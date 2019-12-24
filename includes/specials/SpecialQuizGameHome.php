<?php
/**
 * QuizGame extension - interactive question game that uses AJAX
 *
 * @file
 * @ingroup Extensions
 * @author Aaron Wright <aaron.wright@gmail.com>
 * @author Ashish Datta <ashish@setfive.com>
 * @author David Pean <david.pean@gmail.com>
 * @author Jack Phoenix
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:QuizGame Documentation
 */

class QuizGameHome extends UnlistedSpecialPage {

	// quizgame_questions.q_flag used to be an enum() and that sucked, big time
	static $FLAG_NONE = 0;
	static $FLAG_FLAGGED = 1;
	static $FLAG_PROTECT = 2;

	/**
	 * @var string $SALT
	 */
	private $SALT;

	/**
	 * Construct the MediaWiki special page
	 */
	public function __construct() {
		parent::__construct( 'QuizGameHome' );
	}

	/**
	 * Show the special page
	 *
	 * @param string|null $permalink Parameter passed to the page
	 */
	public function execute( $permalink ) {
		$out = $this->getOutput();
		$user = $this->getUser();
		$request = $this->getRequest();

		// Is the database locked? If so, we can't do much since answering a
		// question changes database state...and so does creating a new
		// question
		$this->checkReadOnly();

		// https://phabricator.wikimedia.org/T155405
		// Throws error message when SocialProfile extension is not installed
		if ( !class_exists( 'UserStats' ) ) {
			throw new ErrorPageError( 'quizgame-error-socialprofile-title', 'quizgame-error-socialprofile' );
		}

		// Blocked through Special:Block? No access for you either!
		if ( $user->isBlocked() ) {
			throw new UserBlockedError( $user->getBlock() );
		}

		// If a parameter was passed to the special page, assume that it's the
		// permalink ID and forward the user to the question with that ID
		if ( $permalink ) {
			$out->redirect(
				$this->getPageTitle()->getFullURL(
					"questionGameAction=renderPermalink&permalinkID={$permalink}"
				)
			);
		}

		// Set the correct robot policies, ensure that skins don't render a link to
		// Special:WhatLinksHere on their toolboxes, etc.
		$this->setHeaders();

		// Add CSS & JS
		$out->addModuleStyles( 'ext.quizGame.css' );
		$out->addModules( 'ext.quizGame' );

		// salt at will
		$this->SALT = 'SALT';

		// What we should do depends on the given action parameter
		$action = $request->getVal( 'questionGameAction' );

		switch ( $action ) {
			case 'adminPanel':
				if ( $user->isLoggedIn() && $user->isAllowed( 'quizadmin' ) ) {
					$this->adminPanel();
				} else {
					$this->renderWelcomePage();
				}
				break;
			case 'completeEdit':
				if ( $user->isLoggedIn() && $user->isAllowed( 'quizadmin' ) ) {
					$this->completeEdit();
				} else {
					$this->renderWelcomePage();
				}
				break;
			case 'createForm':
				if ( !$user->isLoggedIn() ) {
					$this->renderLoginPage();
					return;
				}
				$this->renderWelcomePage();
				break;
			case 'createGame':
				$this->createQuizGame();
				break;
			case 'editItem':
				if ( $user->isLoggedIn() && $user->isAllowed( 'quizadmin' ) ) {
					$this->editItem();
				} else {
					$this->renderWelcomePage();
				}
				break;
			case 'launchGame':
				$this->launchGame();
				break;
			case 'renderPermalink':
				$this->launchGame();
				break;
		default:
			$this->launchGame();
			break;
		}
	}

	public function userAnswered( $user_name, $q_id ) {
		$dbr = wfGetDB( DB_REPLICA );
		$s = $dbr->selectRow(
			'quizgame_answers',
			[ 'a_choice_id' ],
			[
				'a_q_id' => intval( $q_id ),
				'a_user_name' => $user_name
			],
			__METHOD__
		);
		if ( $s !== false ) {
			if ( $s->a_choice_id == 0 ) {
				return -1;
			} else {
				return $s->a_choice_id;
			}
		}
		return false;
	}

	public function getAnswerPoints( $user_name, $q_id ) {
		$dbr = wfGetDB( DB_REPLICA );
		$s = $dbr->selectRow(
			'quizgame_answers',
			[ 'a_points' ],
			[
				'a_q_id' => intval( $q_id ),
				'a_user_name' => $user_name
			],
			__METHOD__
		);
		if ( $s !== false ) {
			return $s->a_points;
		}
		return false;
	}

	/**
	 * Get the ID of the next unanswered question for the current user.
	 *
	 * @return int ID of the next unanswered question (or 0)
	 */
	public function getNextQuestion() {
		$dbr = wfGetDB( DB_REPLICA );
		$use_index = $dbr->useIndexClause( 'q_random' );
		$randstr = wfRandom();
		$userName = $this->getUser()->getName();
		$userId = $this->getUser()->getId();

		$q_id = 0;
		$sql = "SELECT q_id FROM {$dbr->tableName( 'quizgame_questions' )} {$use_index} WHERE q_id NOT IN
				(SELECT a_q_id FROM {$dbr->tableName( 'quizgame_answers' )} WHERE a_user_name = {$dbr->addQuotes( $userName )})
				AND q_flag != " . QuizGameHome::$FLAG_FLAGGED . " AND q_user_id <> {$userId} AND q_random > $randstr ORDER by q_random LIMIT 1";
		$res = $dbr->query( $sql, __METHOD__ );
		$row = $dbr->fetchObject( $res );

		if ( $row ) {
			$q_id = $row->q_id;
		}

		if ( $q_id == 0 ) {
			$sql = "SELECT q_id FROM {$dbr->tableName( 'quizgame_questions' )} {$use_index} WHERE q_id NOT IN
					(SELECT a_q_id FROM {$dbr->tableName( 'quizgame_answers' )} WHERE a_user_name = {$dbr->addQuotes( $userName )})
					AND q_flag != " . QuizGameHome::$FLAG_FLAGGED . " AND q_user_id <> {$userId} AND q_random < $randstr ORDER by q_random LIMIT 1";
			$res = $dbr->query( $sql, __METHOD__ );
			$row = $dbr->fetchObject( $res );
			if ( $row ) {
				$q_id = $row->q_id;
			}
		}

		return $q_id;
	}

	/**
	 * Get information about an individual question.
	 *
	 * @param int $questionId Question ID
	 * @param int $skipId If defined, the question ID (q_id) must *not* be this
	 * @return array
	 */
	public function getQuestion( $questionId, $skipId = 0 ) {
		$userName = $this->getUser()->getName();
		$dbr = wfGetDB( DB_REPLICA );
		$where = [];
		$where['q_id'] = intval( $questionId );
		if ( $skipId > 0 ) {
			$where[] = "q_id <> {$skipId}";
		}
		$res = $dbr->select(
			'quizgame_questions',
			[
				'q_id', 'q_user_id', 'q_user_name', 'q_text', 'q_flag',
				'q_answer_count', 'q_answer_correct_count', 'q_picture',
				'q_date'
			],
			$where,
			__METHOD__,
			[ 'LIMIT' => 1 ]
		);

		$row = $dbr->fetchObject( $res );
		$quiz = [];

		if ( $row ) {
			$quiz['text'] = $row->q_text;
			$quiz['image'] = $row->q_picture;
			$quiz['user_name'] = $row->q_user_name;
			$quiz['user_id'] = $row->q_user_id;
			$quiz['answer_count'] = $row->q_answer_count;
			$quiz['id'] = $row->q_id;
			$quiz['status'] = $row->q_flag;

			if ( $row->q_answer_count > 0 ) {
				$correct_percent = str_replace( '.0', '', number_format( $row->q_answer_correct_count / $row->q_answer_count * 100, 1 ) );
			} else {
				$correct_percent = 0;
			}

			$quiz['correct_percent'] = $correct_percent;
			$quiz['user_answer'] = $this->userAnswered( $userName, $row->q_id );

			if ( $quiz['user_answer'] ) {
				$quiz['points'] = $this->getAnswerPoints( $userName, $questionId );
			}

			$choices = $this->getQuestionChoices( $questionId, $row->q_answer_count );
			foreach ( $choices as $choice ) {
				if ( $choice['is_correct'] ) {
					$quiz['correct_answer'] = $choice['id'];
				}
			}

			$quiz['choices'] = $choices;
		}

		return $quiz;
	}

	/**
	 * Get the answer options for a question when we know its ID number.
	 *
	 * @param int $questionId Question ID
	 * @param int $question_answer_count Amount of answers on the question
	 * @return array[]
	 */
	public function getQuestionChoices( $questionId, $question_answer_count = 0 ) {
		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select(
			'quizgame_choice',
			[
				'choice_id', 'choice_text', 'choice_order',
				'choice_answer_count', 'choice_is_correct'
			],
			[ 'choice_q_id' => intval( $questionId ) ],
			__METHOD__,
			[ 'ORDER BY' => 'choice_order' ]
		);

		$choices = [];
		foreach ( $res as $row ) {
			if ( $question_answer_count ) {
				$percent = str_replace( '.0', '', number_format( $row->choice_answer_count / $question_answer_count * 100, 1 ) );
			} else {
				$percent = 0;
			}

			$choices[] = [
				'id' => $row->choice_id,
				'text' => $row->choice_text,
				'is_correct' => $row->choice_is_correct,
				'answers' => $row->choice_answer_count,
				'percent' => $percent
			];
		}

		return $choices;
	}

	function adminPanel() {
		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select(
			'quizgame_questions',
			[ 'q_id', 'q_text', 'q_flag', 'q_picture', 'q_comment' ],
			[
				// I'd like to know the ideal way of doing this.
				// Database::makeList() has a tendency of making the world
				// explode (see my notes on VideoHooks::onVideoDelete to find
				// out what I mean)
				'q_flag = ' . QuizGameHome::$FLAG_FLAGGED . ' OR q_flag = ' .
					QuizGameHome::$FLAG_PROTECT
			],
			__METHOD__
		);

		// Define variables to avoid E_NOTICEs
		$flaggedQuestions = '';
		$protectedQuestions = '';

		foreach ( $res as $row ) {
			$options = '<ul>';
			$choices = $this->getQuestionChoices( $row->q_id );
			foreach ( $choices as $choice ) {
				$options .= '<li>' . $choice['text'] . ' ' .
					( ( $choice['is_correct'] == 1 ) ? ' — ' . $this->msg( 'quizgame-correct-answer' )->text() : '' ) .
					'</li>';
			}
			$options .= '</ul>';

			$thumbnail = '';
			if ( strlen( $row->q_picture ) > 0 ) {
				$image = wfFindFile( $row->q_picture );
				// You know why this check is here, just grep for the function
				// name (I'm too lazy to copypaste it here for the third time).
				if ( is_object( $image ) ) {
					$thumb = $image->transform( [ 'width' => 80, 'height' => 0 ] );
					$thumbnail = $thumb->toHtml();
				}
			}

			$buttons = $this->getLinkRenderer()->makeLink(
				$this->getPageTitle(),
				$this->msg( 'quizgame-edit' )->text(),
				[],
				[
					'questionGameAction' => 'editItem',
					'quizGameId' => $row->q_id
				]
			) . " -
					<a class=\"delete-by-id\" href=\"#\" data-quiz-id=\"{$row->q_id}\">" .
					$this->msg( 'quizgame-delete' )->text() . '</a> - ';

			if ( $row->q_flag == QuizGameHome::$FLAG_FLAGGED ) {
				$buttons .= "<a class=\"protect-by-id\" href=\"#\" data-quiz-id=\"{$row->q_id}\">" .
					$this->msg( 'quizgame-protect' )->text() . "</a>
						 - <a class=\"unflag-by-id\" href=\"#\" data-quiz-id=\"{$row->q_id}\">" .
						 $this->msg( 'quizgame-unflag' )->text() . '</a>';
			} else {
				$buttons .= "<a class=\"unprotect-by-id\" href=\"#\" data-quiz-id=\"{$row->q_id}\">" .
					$this->msg( 'quizgame-unprotect' )->text() . '</a>';
			}

			if ( $row->q_flag == QuizGameHome::$FLAG_FLAGGED ) {
				$reason = '';
				if ( $row->q_comment != '' ) {
					$reason = "<div class=\"quizgame-flagged-answers\" id=\"quizgame-flagged-reason-{$row->q_id}\">
						<b>" . $this->msg( 'quizgame-flagged-reason' )->text() . "</b>: {$row->q_comment}
					</div><p>";
				}

				$flaggedQuestions .= "<div class=\"quizgame-flagged-item\" id=\"items[{$row->q_id}]\">

				<h3>{$row->q_text}</h3>

				<div class=\"quizgame-flagged-picture\" id=\"quizgame-flagged-picture-{$row->q_id}\">
					{$thumbnail}
				</div>

				<div class=\"quizgame-flagged-answers\" id=\"quizgame-flagged-answers-{$row->q_id}\">
					{$options}
				</div>
				{$reason}
				<div class=\"quizgame-flagged-buttons\" id=\"quizgame-flagged-buttons\">
					{$buttons}
				</div>

			</div>";
			} else {
				$protectedQuestions .= "<div class=\"quizgame-protected-item\" id=\"items[{$row->q_id}]\">

			   	<h3>{$row->q_text}</h3>

				<div class=\"quizgame-flagged-picture\" id=\"quizgame-flagged-picture-{$row->q_id}\">
					{$thumbnail}
				</div>

				<div class=\"quizgame-flagged-answers\" id=\"quizgame-flagged-answers-{$row->q_id}\">
					{$options}
				</div>

				<div class=\"quizgame-flagged-buttons\" id=\"quizgame-flagged-buttons\">
					{$buttons}
				</div>

			</div>";
			}
		}

		$this->getOutput()->setPageTitle( $this->msg( 'quizgame-admin-panel-title' )->text() );

		$output = '<div class="quizgame-admin" id="quizgame-admin">

				<div class="ajax-messages" id="ajax-messages" style="margin:0px 0px 15px 0px;"></div>

				<div class="quizgame-admin-top-links">
					<a href="' . htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=launchGame' ) ) . '">' .
						$this->msg( 'quizgame-admin-back' )->text() . '</a>
				</div>

				<h1>' . $this->msg( 'quizgame-admin-flagged' )->text() . "</h1>
				{$flaggedQuestions}

				<h1>" . $this->msg( 'quizgame-admin-protected' )->text() . "</h1>
				{$protectedQuestions}

			</div>";

		$this->getOutput()->addHTML( $output );
	}

	/**
	 * Completes an edit of a question
	 * Updates the SQL and then forwards to the permalink
	 */
	function completeEdit() {
		$request = $this->getRequest();

		$id = $request->getInt( 'quizGameId' );

		// Only Quiz Administrators can perform this operation.
		if ( !$this->getUser()->isAllowed( 'quizadmin' ) ) {
			$this->getOutput()->addHTML( $this->msg( 'quizgame-admin-permission' )->text() );
			return;
		}

		$question = $request->getVal( 'quizgame-question' );
		$choices_count = $request->getInt( 'choices_count' );
		$old_correct_id = $request->getInt( 'old_correct' );

		$picture = $request->getVal( 'quizGamePicture' );

		// Updated quiz choices
		$dbw = wfGetDB( DB_MASTER );
		for ( $x = 1; $x <= $choices_count; $x++ ) {
			if ( $request->getVal( "quizgame-answer-{$x}" ) ) {
				if ( $request->getVal( "quizgame-isright-{$x}" ) == 'on' ) {
					$is_correct = 1;
				} else {
					$is_correct = 0;
				}

				$dbw->update(
					'quizgame_choice',
					[
						'choice_text' => $request->getVal( "quizgame-answer-{$x}" ),
						'choice_is_correct' => $is_correct
					],
					[ 'choice_q_id' => $id, 'choice_order' => $x ],
					__METHOD__
				);
			}
		}

		$dbw->update(
			'quizgame_questions',
			[ 'q_text' => $question, 'q_picture' => $picture ],
			[ 'q_id' => $id ],
			__METHOD__
		);

		// Get new correct answer to see if it's different, and if so,
		// we have to update any user stats
		$s = $dbw->selectRow(
			'quizgame_choice',
			[ 'choice_id' ],
			[ 'choice_q_id' => $id, 'choice_is_correct' => 1 ],
			__METHOD__
		);
		if ( $s !== false ) {
			$new_correct_id = $s->choice_id;
		}

		// Ruh roh rorge...we have to fix stats
		if ( $new_correct_id != $old_correct_id ) {
			// Those who had the old answer ID correct need their total to be decremented
			$selectOld = $dbw->buildSelectSubquery(
				'quizgame_answers',
				'a_user_id',
				[ 'a_choice_id' => $old_correct_id ],
				__METHOD__
			);
			$dbw->update(
				'user_stats',
				[ 'stats_quiz_questions_correct=stats_quiz_questions_correct-1' ],
				[ "stats_user_id IN $selectOld" ],
				__METHOD__
			);

			// Those who had the new answer ID correct need their total to be increased
			$selectNew = $dbw->buildSelectSubquery(
				'quizgame_answers',
				'a_user_id',
				[ 'a_choice_id' => $new_correct_id ],
				__METHOD__
			);
			$dbw->update(
				'user_stats',
				[ 'stats_quiz_questions_correct=stats_quiz_questions_correct+1' ],
				[ "stats_user_id IN $selectNew" ],
				__METHOD__
			);

			// Finally, we need to adjust everyone's %'s who have been affected by this switch
			$selectBoth = $dbw->buildSelectSubquery(
				'quizgame_answers',
				'a_user_id',
				[ 'a_choice_id' => [ $old_correct_id, $new_correct_id ] ],
				__METHOD__
			);
			$dbw->update(
				'user_stats',
				[ 'stats_quiz_questions_correct_percent=stats_quiz_questions_correct /stats_quiz_questions_answered' ],
				[ "stats_user_id IN $selectBoth" ],
				__METHOD__
			);

			// Also, we need to adjust the question table and fix how many answered it correctly
			/*
			$howMany = $dbw->selectField(
				'quizgame_answers',
				'COUNT(*)',
				[ 'a_choice_id' => $new_correct_id ],
				__METHOD__
			);
			$res = $dbw->update(
				'quizgame_questions',
				[ 'q_answer_correct_count' => intval( $howMany ) ],
				[ 'q_id' => $id ],
				__METHOD__
			);
			*/
			$count = $dbw->buildSelectSubquery(
				'quizgame_answers',
				'COUNT(*)',
				[ 'a_choice_id' => $new_correct_id ],
				__METHOD__
			);
			$dbw->update(
				'quizgame_questions',
				[ "q_answer_correct_count = $count" ],
				[ 'q_id' => $id ],
				__METHOD__
			);
		}

		header( 'Location: ' . $this->getPageTitle()->getFullURL( "renderPermalink&permalinkID={$id}" ) );
	}

	/**
	 * Shows the edit panel for a single question
	 */
	function editItem() {
		global $wgExtensionAssetsPath, $wgQuizID;

		$lang = $this->getLanguage();
		$out = $this->getOutput();
		$request = $this->getRequest();

		$id = $request->getInt( 'quizGameId' );

		$wgQuizID = $id;

		$question = $this->getQuestion( $id );

		$out->setPageTitle( $this->msg( 'quizgame-edit-title', $question['text'] )->parse() );

		$user_name = $question['user_name'];
		$user_id = $question['user_id'];
		$user_title = Title::makeTitle( NS_USER, $question['user_name'] );
		$avatar = new wAvatar( $user_id, 'l' );
		$stats = new UserStats( $user_id, $user_name );
		$stats_data = $stats->getUserStats();

		$uploadPage = SpecialPage::getTitleFor( 'QuestionGameUpload' );

		if ( strlen( $question['image'] ) > 0 ) {
			$image = wfFindFile( $question['image'] );
			$thumbtag = '';
			// If a file that is still being used on a quiz game is
			// independently deleted from the quiz game, poor users will
			// stumble upon nasty fatals without this check here.
			if ( is_object( $image ) ) {
				$thumb = $image->transform( [ 'width' => 80 ] );
				$thumbtag = $thumb->toHtml();
			}

			$pictag = '<div id="quizgame-picture" class="quizgame-picture">' . $thumbtag . '</div>
					<p id="quizgame-editpicture-link"><!-- jQuery injects a link here --></p>
					<div id="quizgame-upload" class="quizgame-upload" style="display:none">
						<iframe id="imageUpload-frame" class="imageUpload-frame" width="650" scrolling="no" frameborder="0" src="' .
							htmlspecialchars( $uploadPage->getFullURL( wfArrayToCGI( [
								'wpThumbWidth' => '80',
								'wpOverwriteFile' => 'true',
								'wpDestFile' => $question['image']
							] ) ) ) . '">
						</iframe>
					</div>';

		} else {
			$pictag = '<div id="quizgame-picture" class="quizgame-picture"></div>
					<div id="quizgame-editpicture-link"></div>

					<div id="quizgame-upload" class="quizgame-upload">
						<iframe id="imageUpload-frame" class="imageUpload-frame" width="650" scrolling="no" frameborder="0" src="' .
							htmlspecialchars( $uploadPage->getFullURL( wfArrayToCGI( [
								'wpThumbWidth' => '80'
							] ) ) ) . '">
						</iframe>
					</div>';
		}

		$x = 1;
		$choices_count = count( $question['choices'] );
		$quizOptions = '';
		foreach ( $question['choices'] as $choice ) {
			if ( $choice['is_correct'] ) {
				$old_correct = $choice['id'];
			}
			$quizOptions .= "<div id=\"quizgame-answer-container-{$x}\" class=\"quizgame-answer\">
							<span class=\"quizgame-answer-number\">{$x}.</span>
							<input name=\"quizgame-answer-{$x}\" id=\"quizgame-answer-{$x}\" type=\"text\" value=\"" .
								htmlspecialchars( $choice['text'], ENT_QUOTES ) . "\" size=\"32\" />
							<input type=\"checkbox\" id=\"quizgame-isright-{$x}\" " .
								( ( $choice['is_correct'] ) ? 'checked="checked"' : '' ) .
								" name=\"quizgame-isright-{$x}\">
						</div>";

			$x++;
		}

		// As nice as it'd be to move this variable to a function that is
		// hooked to MakeGlobalVariablesScript, it's impossible. So don't even
		// bother trying, mmmkay?
		$output = "<div class=\"quizgame-edit-container\" id=\"quizgame-edit-container\">

				<script type=\"text/javascript\">
				var __choices_count__ = {$choices_count};
				</script>";

		global $wgRightsText;
		if ( $wgRightsText ) {
			$copywarnMsg = 'copyrightwarning';
			$copywarnMsgParams = [
				'[[' . $this->msg( 'copyrightpage' )->inContentLanguage()->plain() . ']]',
				$wgRightsText
			];
		} else {
			$copywarnMsg = 'copyrightwarning2';
			$copywarnMsgParams = [
				'[[' . $this->msg( 'copyrightpage' )->inContentLanguage()->plain() . ']]'
			];
		}

		$formattedVoteCount = $lang->formatNum( $stats_data['votes'] );
		$formattedEditCount = $lang->formatNum( $stats_data['edits'] );
		$formattedCommentCount = $lang->formatNum( $stats_data['comments'] );
		$pictureStuff = '';
		// Show the picture stuff only if we can upload files (T155448)
		if ( UploadBase::isEnabled() ) {
			$pictureStuff = '<h1>' . $this->msg( 'quizgame-picture' )->text() . "</h1>
				<div class=\"quizgame-edit-picture\" id=\"quizgame-edit-picture\">
					{$pictag}
				</div>

				<input id=\"quizGamePicture\" name=\"quizGamePicture\" type=\"hidden\" value=\"{$question['image']}\" />";
		}
		$output .= "
				<div class=\"quizgame-edit-question\" id=\"quizgame-edit-question\">
					<form name=\"quizGameEditForm\" id=\"quizGameEditForm\" method=\"post\" action=\"" .
						htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=completeEdit' ) ) .
						'">

						<div class="credit-box" id="creditBox">
							<h1>' . $this->msg( 'quizgame-submitted-by' )->text() . "</h1>

							<div id=\"submitted-by-image\" class=\"submitted-by-image\">
								<a href=\"{$user_title->getFullURL()}\">
									{$avatar->getAvatarURL()}
								</a>
							</div>

							<div id=\"submitted-by-user\" class=\"submitted-by-user\">
								<div id=\"submitted-by-user-text\">
									<a href=\"{$user_title->getFullURL()}\">{$user_name}</a>
								</div>
								<ul>
									<li id=\"userstats-votes\">
										<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/voteIcon.gif\" alt=\"\" />
										{$formattedVoteCount}
									</li>
									<li id=\"userstats-edits\">
										<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/editIcon.gif\" alt=\"\" />
										{$formattedEditCount}
									</li>
									<li id=\"userstats-comments\">
										<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/commentsIcon.gif\" alt=\"\" />
										{$formattedCommentCount}
									</li>
								</ul>
							</div>
							<div class=\"visualClear\"></div>
						</div>

						<div class=\"ajax-messages\" id=\"ajax-messages\" style=\"margin:20px 0px 15px 0px;\"></div>

						<h1>" . $this->msg( 'quizgame-question' )->text() . "</h1>
						<input name=\"quizgame-question\" id=\"quizgame-question\" type=\"text\" value=\"" .
							htmlspecialchars( $question['text'], ENT_QUOTES ) . "\" size=\"64\" />
						<h1>" . $this->msg( 'quizgame-answers' )->text() . "</h1>
						<div style=\"margin:10px 0px;\">" . $this->msg( 'quizgame-correct-answer-checked' )->text() . "</div>
						{$quizOptions}
						{$pictureStuff}

						<input id=\"quizGameId\" name=\"quizGameId\" type=\"hidden\" value=\"{$question['id']}\" />
						<input name=\"choices_count\" type=\"hidden\" value=\"{$choices_count}\" />
						<input id=\"old_correct\" name=\"old_correct\" type=\"hidden\" value=\"{$old_correct}\" />
					</form>
				</div>

				<div class=\"quizgame-copyright-warning\">" .
					$this->msg( $copywarnMsg )->params( $copywarnMsgParams )->parse() .
				"</div>

				<div class=\"quizgame-edit-buttons\" id=\"quizgame-edit-buttons\">
					<input type=\"button\" class=\"site-button\" value=\"" . $this->msg( 'quizgame-save-page-button' )->text() . "\" onclick=\"javascript:document.quizGameEditForm.submit()\"/>
					<input type=\"button\" class=\"site-button\" value=\"" . $this->msg( 'quizgame-cancel-button' )->text() . "\" onclick=\"javascript:document.location='" .
						htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=launchGame' ) ) . '\'" />
				</div>
			</div>';

		$out->addHTML( $output );
	}

	/**
	 * Present a "log in" message
	 */
	function renderLoginPage() {
		$this->getOutput()->setPageTitle( $this->msg( 'quizgame-login-title' )->text() );

		$output = $this->msg( 'quizgame-login-text' )->text() . '<p>';
		$output .= '<div>
			<input type="button" class="site-button" value="' .
				$this->msg( 'quizgame-main-page-button' )->text() . '" onclick="window.location=\'' .
				htmlspecialchars( Title::newMainPage()->getFullURL() ) . '\'" />
			<input type="button" class="site-button" value="' .
			$this->msg( 'quizgame-login-button' )->text() . '" onclick="window.location=\'' .
			htmlspecialchars( SpecialPage::getTitleFor( 'Userlogin' )->getFullURL() ) . '\'" />
		</div>';
		$this->getOutput()->addHTML( $output );
	}

	/**
	 * Present "No more quizzes" message to the user
	 */
	function renderQuizOver() {
		$this->getOutput()->setPageTitle( $this->msg( 'quizgame-nomore-questions' )->text() );

		$output = $this->msg( 'quizgame-ohnoes' )->parse();
		$output .= '<div>
		<input type="button" class="site-button" value="' .
			$this->msg( 'quizgame-create-button' )->text() . '" onclick="window.location=\'' .
			htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=createForm' ) ) .
			'\'"/>

		</div>';
		$this->getOutput()->addHTML( $output );
		return '';
	}

	/**
	 * Renders the "permalink is not available" error message.
	 */
	function renderPermalinkError() {
		$this->getOutput()->setPageTitle( $this->msg( 'quizgame-title' )->text() );
		$this->getOutput()->addWikiMsg( 'quizgame-unavailable' );
	}

	function renderStart() {
		$this->getOutput()->setPageTitle( $this->msg( 'quizgame-title' )->text() );
		$url = htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=launchGame' ) );
		$output = $this->msg( 'quizgame-intro' )->parse() . '  <a href="' . $url . '">' .
			$this->msg( 'quizgame-introlink' )->text() . '</a>';
		$this->getOutput()->addHTML( $output );
	}

	/**
	 * Main function to render a quiz game.
	 * Also handles rendering a permalink.
	 */
	function launchGame() {
		global $wgExtensionAssetsPath;

		$lang = $this->getLanguage();
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		$on_load = 'QuizGame.showAnswers();';

		// controls the maximum length of the previous game bar graphs
		$dbr = wfGetDB( DB_MASTER );

		$permalinkID = $request->getInt( 'permalinkID' );
		$lastid = $request->getInt( 'lastid' );
		$skipid = $request->getInt( 'skipid' );

		$isFixedlink = false;
		$permalinkOptions = -1;
		$backButton = '';
		$editMenu = '';
		$editLinks = '';

		// Logged in user's stats
		$stats = new UserStats( $user->getId(), $user->getName() );
		$current_user_stats = $stats->getUserStats();
		if ( !$current_user_stats['quiz_points'] ) {
			$current_user_stats['quiz_points'] = 0;
		}

		// Get users rank
		$quiz_rank = 0;
		$s = $dbr->selectRow(
			'user_stats',
			[ 'COUNT(*) AS count' ],
			[ 'stats_quiz_points > ' . $current_user_stats['quiz_points'] ],
			__METHOD__
		);
		if ( $s !== false ) {
			$quiz_rank = $s->count + 1;
		}

		// This is assuming that lastId and permalinkId
		// are mutually exclusive
		if ( $permalinkID ) {
			$question = $this->getQuestion( $permalinkID );
			if ( !$question ) {
				$this->renderPermalinkError();
				return '';
			}
		} else {
			$question = $this->getQuestion( $this->getNextQuestion(), $skipid );
			if ( !$question ) {
				$this->renderQuizOver();
				return '';
			}
		}

		global $wgQuizID;
		$wgQuizID = $question['id'];

		$timestampedViewed = 0;
		if ( $user->getName() != $question['user_name'] ) {
			// check to see if the user already had viewed this question
			global $wgMemc;
			$key = $wgMemc->makeKey( 'quizgame-user-view', $user->getId(), $question['id'] );
			$data = $wgMemc->get( $key );
			if ( $data > 0 ) {
				$timestampedViewed = $data;
			} else {
				// mark that they viewed for first time
				$wgMemc->set( $key, time() );
			}
			$on_load .= "QuizGame.countDown({$timestampedViewed});";
		}

		if ( $lastid ) {
			$prev_question = $this->getQuestion( $lastid );
		}

		// @note This is an extremely filthy hack...but it seems to be working?
		$out->addScript( '<script type="text/javascript">(window.RLQ = window.RLQ || []).push(function () {
			jQuery( function() {
				mw.loader.using( \'ext.quizGame\', function() { ' . $on_load . " } );
			} );
		} );</script>\n" );

		$gameid = $question['id'];

		$out->setPageTitle( $question['text'] );

		if ( strlen( $question['image'] ) > 0 ) {
			$image = wfFindFile( $question['image'] );
			$imageThumb = '';
			$imgWidth = 0;
			// If a file that is still being used on a quiz game is
			// independently deleted from the quiz game, poor users will
			// stumble upon nasty fatals without this check here.
			if ( is_object( $image ) ) {
				$imageThumb = $image->createThumb( 160 );
				$imageThumb .= '?' . time();
				if ( $image->getWidth() >= 160 ) {
					$imgWidth = 160;
				} else {
					$imgWidth = $image->getWidth();
				}
			}
			$imageTag = '<div id="quizgame-picture" class="quizgame-picture">
				<img src="' . $imageThumb . '" width="' . $imgWidth . '"></div>';
		} else {
			$imageTag = '';
		}

		$user_name = $question['user_name'];
		$user_title = Title::makeTitle( NS_USER, $user_name );
		$id = $question['user_id'];
		$avatar = new wAvatar( $id, 'l' );
		$stats = new UserStats( $id, $user_name );
		$stats_data = $stats->getUserStats();

		$user_answer = $this->userAnswered( $user->getName(), $gameid );

		global $wgUseEditButtonFloat;
		if (
			( $user->getId() == $question['user_id'] ||
			( $user_answer && $user->isLoggedIn() && $user->isAllowed( 'quizadmin' ) ) ||
			$user->isAllowed( 'quizadmin' ) ) && ( $wgUseEditButtonFloat == true )
		) {
			$editMenu = "
				<div class=\"edit-menu-quiz-game\">
					<div class=\"edit-button-quiz-game\">
						<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/editIcon.gif\" alt=\"\" />
						<!-- jQuery inserts an edit link here -->
					</div>
				</div>";

			$editLinks = $this->getLinkRenderer()->makeLink(
				$this->getPageTitle(),
				$this->msg( 'quizgame-admin-panel-title' )->text(),
				[],
				[ 'questionGameAction' => 'adminPanel' ]
			) . ' -
				<a class="protect-image-link" href="#">' . $this->msg( 'quizgame-protect' )->text() . '</a> - ' .
				'<a class="delete-quiz-link" href="#">' . $this->msg( 'quizgame-delete' )->text() . '</a> -';
		}

		// For registered users, display their personal scorecard; for anons,
		// encourage them to join the site to play quizzes.
		if ( $user->isLoggedIn() ) {
			$leaderboard_title = SpecialPage::getTitleFor( 'QuizLeaderboard' );
			$formattedQuizPoints = $lang->formatNum( $current_user_stats['quiz_points'] );
			$formattedCorrectAnswers = $lang->formatNum( $current_user_stats['quiz_correct'] );
			$formattedTotalAnswers = $lang->formatNum( $current_user_stats['quiz_answered'] );
			$stats_box = '<div class="user-rank">
					<h2>' . $this->msg( 'quizgame-leaderboard-scoretitle' )->text() . '</h2>

					<p><b>' . $this->msg( 'quizgame-leaderboard-quizpoints' )->text() . "</b></p>
					<p class=\"user-rank-points\">{$formattedQuizPoints}</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-correct' )->text() . "</b></p>
					<p>{$formattedCorrectAnswers}</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-answered' )->text() . "</b></p>
					<p>{$formattedTotalAnswers}</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-pctcorrect' )->text() . "</b></p>
					<p>{$current_user_stats['quiz_correct_percent']}%</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-rank' )->text() . "</b></p>
					<p>{$quiz_rank} <span class=\"user-rank-link\">
						<a href=\"{$leaderboard_title->getFullURL()}\">(" . $this->msg( 'quizgame-leaderboard-link' )->text() . ")</a>
					</span></p>
					<div class=\"visualClear\"></div>

				</div>";
		} else {
			$stats_box = '<div class="user-rank">
				<h2>' . $this->msg( 'quizgame-leaderboard-scoretitle' )->text() . '</h2>'
					. $this->msg( 'quizgame-login-or-create-to-climb' )->parse() .
			'</div>';
		}

		$answers = '';
		if ( $user_answer ) {
			$answers .= '<div class="answer-percent-correct">' .
				$this->msg( 'quizgame-pct-answered-correct', $question['correct_percent'] )->text() . '</div>';
			if ( $user_answer == $question['correct_answer'] ) {
				$answers .= '<div class="answer-message-correct">' .
					$this->msg( 'quizgame-answered-correctly' )->text() . '</div>';
			} else {
				if ( $user_answer == -1 ) {
					$answers .= '<div class="answer-message-incorrect">' .
						$this->msg( 'quizgame-skipped' )->text() . '</div>';
				} else {
					$answers .= '<div class="answer-message-incorrect">' .
						$this->msg( 'quizgame-answered-incorrectly' )->text() .
						'</div>';
				}
			}
		}

		// User hasn't answered yet, so display the quiz options with the
		// ability to play the question
		if ( !$user_answer && $user->getName() != $question['user_name'] ) {
			$answers .= '<ul>';
			$x = 1;
			foreach ( $question['choices'] as $choice ) {
				$answers .= "<li id=\"{$x}\"><a class=\"quiz-vote-link\" data-choice-id=\"{$choice['id']}\" href=\"#\">{$choice['text']}</a></li>";
				$x++;
			}
			$answers .= '</ul>';
		} else {
			// User has answered, so display the right answer, and how many
			// people picked what
			$x = 1;
			foreach ( $question['choices'] as $choice ) {
				$bar_width = floor( 220 * ( $choice['percent'] / 100 ) );
				if ( $choice['is_correct'] == 1 ) {
					$barColor = 'green';
					$barColorNumber = '3';
				} else {
					$barColor = 'red';
					$barColorNumber = '2';
				}
				$incorrectMsg = $correctMsg = '';
				if ( $user_answer == $choice['id'] && $question['correct_answer'] != $choice['id'] ) {
					$incorrectMsg = '- <span class="answer-message-incorrect">' .
						$this->msg( 'quizgame-your-answer' )->text() . '</span>';
				}
				if ( $question['correct_answer'] == $choice['id'] ) {
					$correctMsg = '- <span class="answer-message-correct">' .
						$this->msg( 'quizgame-correct-answer' )->text() . '</span>';
				}
				$answers .= "<div id=\"{$x}\" class=\"answer-choice\">{$choice['text']}" .
						$incorrectMsg . $correctMsg .
					'</div>';
				$answers .= "<div class=\"one-answer-bar answer-" . $barColor . "\">
						<img border=\"0\" style=\"width:{$bar_width}px;\" class=\"one-answer-width\" src=\"{$wgExtensionAssetsPath}/SocialProfile/images/vote-bar-" . $barColorNumber . ".gif\"/>
						<span class=\"answer-percent\">{$choice['percent']}%</span>
					</div>";
				$x++;
			}
		}

		$output = "
		<div id=\"quizgame-container\" class=\"quizgame-container\">
			{$editMenu}";

		$output .= '<div class="quizgame-left">';

		if ( !$user_answer && $user->getName() != $question['user_name'] ) {
			global $wgUserStatsPointValues;
			$quizPoints = ( $wgUserStatsPointValues['quiz_points'] ?? 0 );
			$output .= '<div class="time-box">
					<div class="quiz-countdown">
						<span id="time-countdown">-</span> ' . $this->msg( 'quizgame-js-seconds' )->text() .
					'</div>

					<div class="quiz-points" id="quiz-points">' .
						$this->msg( 'quizgame-points', $quizPoints )->parse() .
					'</div>
					<div class="quiz-notime" id="quiz-notime"></div>
					</div>';
		}

		$output .= '<div class="ajax-messages" id="ajax-messages"></div>';

		$output .= "

					{$imageTag}

					<div id=\"loading-answers\">" . $this->msg( 'quizgame-js-loading' )->text() . "</div>
					<div id=\"quizgame-answers\" style=\"display:none;\" class=\"quizgame-answers\">
						{$answers}
					</div>

					<form name=\"quizGameForm\" id=\"quizGameForm\">
						<input id=\"quizGameId\" name=\"quizGameId\" type=\"hidden\" value=\"{$gameid}\" />
					</form>

					<div class=\"navigation-buttons\">
						{$backButton}";

		if ( !$user_answer && $user->getName() != $question['user_name'] ) {
			$output .= '<a class="skip-question-link" href="javascript:void(0);">' .
				$this->msg( 'quizgame-skip' )->text() . '</a>';
		} else {
			$output .= '<a href="' . htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=launchGame' ) ) . '">' .
				$this->msg( 'quizgame-next' )->text() . '</a>';
		}
		$output .= '</div>';

		if ( !empty( $prev_question['id'] ) && !empty( $prev_question['user_answer'] ) ) {
			$output .= '<div id="answer-stats" class="answer-stats" style="display:block">

							<div class="last-game">

							<div class="last-question-heading">'
								. $this->msg( 'quizgame-last-question' )->text() . ' - <a href="' .
								htmlspecialchars( $this->getPageTitle()->getFullURL( "questionGameAction=renderPermalink&permalinkID={$prev_question['id']}" ) ) .
								"\">{$prev_question['text']}</a>
								<div class=\"last-question-count\">" .
									$this->msg( 'quizgame-times-answered', $prev_question['answer_count'] )->parse() .
								'</div>
							</div>';

			$your_answer_status = '';
			if ( $prev_question['id'] && $prev_question['user_answer'] ) {
				// Get the choice text of what the user picked (and show how many points they got)
				foreach ( $prev_question['choices'] as $choice ) {
					if ( $choice['id'] == $prev_question['user_answer'] ) {
						$your_answer = $choice['text'];
						if ( $choice['is_correct'] == 1 ) {
							$your_answer_status = '<div class="answer-status-correct">' .
								$this->msg( 'quizgame-chose-correct', $prev_question['points'] )->parse() .
							'</div>';
						} else {
							$your_answer_status = '<div class="answer-status-incorrect">' .
								$this->msg( 'quizgame-chose-incorrect' )->text() .
							'</div>';
						}
					}
				}
			}

			$output .= "<div class=\"user-answer-status\">
							{$your_answer_status}
						</div>";

			foreach ( $prev_question['choices'] as $choice ) {
				$bar_width = floor( 460 * ( $choice['percent'] / 100 ) );
				if ( $choice['is_correct'] == 1 ) {
					$answerClass = 'correct';
					$answerColor = 'green';
					$answerColorNumber = '3';
				} else {
					$answerClass = 'incorrect';
					$answerColor = 'red';
					$answerColorNumber = '2';
				}
				$output .= "<div class=\"answer-bar answer-bar-one\" style=\"display:block\">
						<div class=\"one-answer small-answer-" . $answerClass . "\">{$choice['text']}</div>
						<span class=\"one-answer-bar answer-" . $answerColor . "\">
							<img border=\"0\" style=\"width:{$bar_width}px; height: 11px;\" class=\"one-answer-width\" src=\"{$wgExtensionAssetsPath}/SocialProfile/images/vote-bar-" . $answerColorNumber . ".gif\"/>
							<span class=\"one-answer-percent answer-percent\">{$choice['percent']}%</span>
						</span>
					</div>";
			}

			$output .= '</div>
							</div>';
		}

		$formattedVoteCount = $lang->formatNum( $stats_data['votes'] );
		$formattedEditCount = $lang->formatNum( $stats_data['edits'] );
		$formattedCommentCount = $lang->formatNum( $stats_data['comments'] );
		$output .= "</div>

				<div class=\"quizgame-right\">

					<div class=\"create-link\">
						<img border=\"0\" src=\"{$wgExtensionAssetsPath}/SocialProfile/images/addIcon.gif\" alt=\"\" />
						<a href=\"" . htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=createForm' ) ) . '">'
							. $this->msg( 'quizgame-create-title' )->text() .
						"</a>
					</div>
					<div class=\"credit-box\" id=\"creditBox\">
						<h1>" . $this->msg( 'quizgame-submitted-by' )->text() . "</h1>

						<div id=\"submitted-by-image\" class=\"submitted-by-image\">
							<a href=\"{$user_title->getFullURL()}\">
								{$avatar->getAvatarURL()}
							</a>
						</div>

						<div id=\"submitted-by-user\" class=\"submitted-by-user\">
							<div id=\"submitted-by-user-text\">
								<a href=\"" . htmlspecialchars( Title::makeTitle( NS_USER, $user_name )->getFullURL() ) . "\">{$user_name}</a>
							</div>
							<ul>
								<li id=\"userstats-votes\">
									<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/voteIcon.gif\" alt=\"\" />
									{$formattedVoteCount}
								</li>
								<li id=\"userstats-edits\">
									<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/editIcon.gif\" alt=\"\" />
									{$formattedEditCount}
								</li>
								<li id=\"userstats-comments\">
									<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/commentsIcon.gif\" alt=\"\" />
									{$formattedCommentCount}
								</li>
							</ul>
						</div>
						<div class=\"visualClear\"></div>
						{$stats_box}
					</div>
						<div class=\"bottom-links\" id=\"utility-buttons\">
							<a class=\"flag-quiz-link\" href=\"#\">" .
								$this->msg( 'quizgame-flag' )->text() . '</a> - ';

		// Protect & delete links for quiz administrators
		if ( $user->isAllowed( 'quizadmin' ) ) {
			$output .= '<a href="' . htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=adminPanel' ) ) . '">'
					. $this->msg( 'quizgame-admin-panel-title' )->text() .
				'</a> - <a class="protect-image-link" href="#">' .
					$this->msg( 'quizgame-protect' )->text() . '</a> - ' .
				'<a class="delete-quiz-link" href="#">' .
					$this->msg( 'quizgame-delete' )->text() . '</a> - ';
		}

		$output .= "<a href=\"javascript:document.location='" .
			htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=renderPermalink' ) ) .
			"&permalinkID=' + document.getElementById( 'quizGameId' ).value\">" .
				$this->msg( 'quizgame-permalink' )->text() . "</a>
					</div>
				</div>
			</div>

			<div class=\"visualClear\"></div>
			<div class=\"hiddendiv\" style=\"display:none\">
				<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/overlay.png\" alt=\"\" />
			</div>";

		$out->addHTML( $output );
	}

	// Function that inserts questions into the database
	function createQuizGame() {
		global $wgMemc, $wgQuizLogs;

		$request = $this->getRequest();
		$user = $this->getUser();

		$max_answers = 8;

		if ( !$user->matchEditToken( $request->getVal( 'wpEditToken' ) ) ) {
			header( 'Location: ' . $this->getPageTitle()->getFullURL() );
			return;
		}

		$question = $request->getText( 'quizgame-question' );
		$imageName = $request->getText( 'quizGamePictureName' );

		// Add quiz question
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			'quizgame_questions',
			[
				'q_user_id' => $user->getId(),
				'q_user_name' => $user->getName(),
				'q_text' => strip_tags( $question ), // make sure nobody inserts malicious code
				'q_picture' => $imageName,
				'q_date' => date( 'Y-m-d H:i:s' ),
				'q_random' => wfRandom()
			],
			__METHOD__
		);
		$questionId = $dbw->insertId();

		// Add Quiz Choices
		for ( $x = 1; $x <= $max_answers; $x++ ) {
			if ( $request->getVal( "quizgame-answer-{$x}" ) ) {
				if ( $request->getVal( "quizgame-isright-{$x}" ) == 'on' ) {
					$is_correct = 1;
				} else {
					$is_correct = 0;
				}
				$dbw->insert(
					'quizgame_choice',
					[
						'choice_q_id' => $questionId,
						'choice_text' => strip_tags( $request->getVal( "quizgame-answer-{$x}" ) ), // make sure nobody inserts malicious code
						'choice_order' => $x,
						'choice_is_correct' => $is_correct
					],
					__METHOD__
				);
			}
		}

		// Update social statistics
		$stats = new UserStatsTrack( $user->getId(), $user->getName() );
		$stats->incStatField( 'quiz_created' );

		// Add a log entry if quiz logging is enabled
		if( $wgQuizLogs ) {
			$logEntry = new ManualLogEntry( 'quiz', 'create' );
			$logEntry->setPerformer( $user );
			$logEntry->setTarget( $this->getPageTitle( (string)$questionId ) );
			$logEntry->setParameters( [
				'4::quizid' => $questionId
			] );

			$logId = $logEntry->insert();
			$logEntry->publish( $logId );
		}

		// Delete memcached key
		$key = $wgMemc->makeKey( 'user', 'profile', 'quiz', $user->getId() );
		$wgMemc->delete( $key );

		// Redirect the user
		header( 'Location: ' . $this->getPageTitle()->getFullURL( "questionGameAction=renderPermalink&permalinkID={$questionId}" ) );
	}

	function renderWelcomePage() {
		global $wgCreateQuizThresholds;

		$out = $this->getOutput();
		$user = $this->getUser();

		// No access for blocked users
		if ( $user->isBlocked() ) {
			throw new UserBlockedError( $user->getBlock() );
		}

		/**
		 * Create Quiz Thresholds based on User Stats
		 */
		if ( is_array( $wgCreateQuizThresholds ) && count( $wgCreateQuizThresholds ) > 0 ) {
			$can_create = true;

			$stats = new UserStats( $user->getId(), $user->getName() );
			$stats_data = $stats->getUserStats();

			$threshold_reason = '';
			foreach ( $wgCreateQuizThresholds as $field => $threshold ) {
				if ( $stats_data[$field] < $threshold ) {
					$can_create = false;
					$threshold_reason .= ( ( $threshold_reason ) ? ', ' : '' ) . "$threshold $field";
				}
			}

			if ( $can_create == false ) {
				$out->setPageTitle( $this->msg( 'quizgame-create-threshold-title' )->text() );
				$out->addHTML( $this->msg( 'quizgame-create-threshold-reason', $threshold_reason )->parse() );
				return '';
			}
		}

		$chain = time();
		$max_answers = 8;

		$out->setPageTitle( $this->msg( 'quizgame-create-title' )->text() );

		$output = '<div id="quiz-container" class="quiz-container">

			<div class="create-message">
				<p>' . $this->msg( 'quizgame-create-message' )->parse() . '</p>
				<p><input class="site-button" type="button" onclick="document.location=\'' .
					htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=launchGame' ) ) .
					'\'" value="' . $this->msg( 'quizgame-play-quiz' )->text() . '" /></p>
			</div>

			<div class="quizgame-create-form" id="quizgame-create-form">
				<form id="quizGameCreate" name="quizGameCreate" method="post" action="' .
					htmlspecialchars( $this->getPageTitle()->getFullURL( 'questionGameAction=createGame' ) ) . '">
				<div id="quiz-game-errors" style="color:red"></div>

				<h1>' . $this->msg( 'quizgame-create-write-question' )->text() . '</h1>
				<input name="quizgame-question" id="quizgame-question" type="text" value="" size="64" />
				<h1 class="write-answer">' . $this->msg( 'quizgame-create-write-answers' )->text() . '</h1>
				<span style="margin-top:10px;">' . $this->msg( 'quizgame-create-check-correct' )->text() . '</span>
				<span style="display:none;" id="this-is-the-welcome-page"></span>';
		// the span#this-is-the-welcome-page element is an epic hack for JS
		// because I can't think of a better way to detect where we are and JS
		// needs to know if it's on the welcome page or not because there is a
		// code block nearly identical to the below one elsewhere in this file,
		// the only big difference being the hook handlers

		for ( $x = 1; $x <= $max_answers; $x++ ) {
			$output .= "<div id=\"quizgame-answer-container-{$x}\" class=\"quizgame-answer\"" .
				( ( $x > 2 ) ? ' style="display:none;"' : '' ) . ">
				<span class=\"quizgame-answer-number\">{$x}.</span>
				<input name=\"quizgame-answer-{$x}\" id=\"quizgame-answer-{$x}\" type=\"text\" value=\"\" size=\"32\" />
				<input type=\"checkbox\" id=\"quizgame-isright-{$x}\" name=\"quizgame-isright-{$x}\">
			</div>";
		}

		$output .= '<input id="quizGamePictureName" name="quizGamePictureName" type="hidden" value="" />
				<input id="chain" name="chain" type="hidden" value="' . $chain . '" />' .
				Html::hidden( 'wpEditToken', $user->getEditToken() ) .

			'</form>';

		// Show the picture stuff only if we can upload files (T155448)
		if ( UploadBase::isEnabled() ) {
			$output .= '<h1 style="margin-top:20px">' .
				$this->msg( 'quizgame-create-add-picture' )->text() . '</h1>
			<div id="quizgame-picture-upload">
				<div id="real-form">
					<iframe id="imageUpload-frame" class="imageUpload-frame" src="' .
						htmlspecialchars( SpecialPage::getTitleFor( 'QuestionGameUpload' )->getFullURL( 'wpThumbWidth=75' ) ) . '">
					</iframe>
				</div>
			</div>
			<div id="quizgame-picture-preview" class="quizgame-picture-preview"></div>
			<!-- jQuery injects the link element into the next node, the p element -->
			<p id="quizgame-picture-reupload" style="display:none">
			</p>';
		}
		$output .= '</div>

			<div id="startButton" class="startButton">
				<input type="button" class="site-button" value="' . $this->msg( 'quizgame-create-play' )->text() . '" />
			</div>

			</div>';

		$out->addHTML( $output );
	}
}
