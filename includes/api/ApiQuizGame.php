<?php
/**
 * QuizGame API -- handles the AJAX requests done by /js/QuizGame.js, such as
 * when the user clicks "Flag this question" etc.
 *
 * @file
 * @ingroup API
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use Wikimedia\ParamValidator\ParamValidator;

class ApiQuizGame extends MediaWiki\Api\ApiBase {

	/**
	 * Constructor
	 */
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName );
	}

	/**
	 * Main function
	 */
	public function execute() {
		global $wgQuizLogs;

		$params = $this->extractRequestParams();
		$user = $this->getUser();

		$action = $params['quizaction']; // what to do + the word "Item", i.e. deleteItem
		$comment = ( $params['comment'] ?? '' ); // reason for flagging (used only in flagItem)
		$id = $params['id']; // quiz ID number

		// ApiBase's getDB() supports only slave connections, lame...
		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY );

		// Fail early if the user is sitewide blocked.
		// (This snippet copied from MW core /includes/api/ApiTag.php)
		$block = $user->getBlock();
		if ( $block && $block->isSitewide() ) {
			$this->dieBlocked( $block );
		}

		// Allow non-quizadmins to use the flagging feature but require quizadmin
		// rights for all other stuff
		if ( $action !== 'flagItem' && !$user->isAllowed( 'quizadmin' ) ) {
			$this->dieWithError( 'badaccess-group0' );
		}

		switch ( $action ) {
			case 'unprotectItem':
				$dbw->update(
					'quizgame_questions',
					[ 'q_flag' => QuizGameHome::$FLAG_NONE ],
					[ 'q_id' => intval( $id ) ],
					__METHOD__
				);

				$output = $this->msg( 'quizgame-ajax-unprotected' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'unprotect', $id );
				}

				break;
			case 'protectItem':
				$dbw->update(
					'quizgame_questions',
					[ 'q_flag' => QuizGameHome::$FLAG_PROTECT ],
					[ 'q_id' => intval( $id ) ],
					__METHOD__
				);

				$output = $this->msg( 'quizgame-ajax-protected' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'protect', $id, $comment );
				}

				break;
			case 'unflagItem':
				// Fix stats of those who answered the flagged question
				/*
				$sql = "UPDATE user_stats SET stats_quiz_questions_answered=stats_quiz_questions_answered+1
				WHERE stats_actor IN (SELECT a_actor FROM quizgame_answers WHERE a_q_id = {$id})";
				$res = $dbr->query( $sql, __METHOD__ );

				// Fix Stats of those who answered the flagged question correctly
				$sql = "UPDATE user_stats SET stats_quiz_questions_correct=stats_quiz_questions_correct+1
				WHERE stats_actor IN (SELECT a_actor FROM quizgame_answers INNER JOIN quizgame_choice ON a_choice_id=choice_id WHERE a_q_id = {$id} AND choice_is_correct=1 )";
				$res = $dbr->query( $sql, __METHOD__ );

				// Update everyone's percentage who answered that question
				$sql = "UPDATE user_stats SET stats_quiz_questions_correct_percent=stats_quiz_questions_correct /stats_quiz_questions_answered
				WHERE stats_actor IN (SELECT a_actor FROM quizgame_answers WHERE a_q_id = {$id} )";
				$res = $dbr->query( $sql, __METHOD__ );
				*/

				$dbw->update(
					'quizgame_questions',
					[ 'q_flag' => QuizGameHome::$FLAG_NONE, 'q_comment' => '' ],
					[ 'q_id' => intval( $id ) ],
					__METHOD__
				);

				$output = $this->msg( 'quizgame-ajax-unflagged' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'unflag', $id );
				}

				break;
			case 'flagItem':
				/*
				// Fix stats of those who answered the flagged question
				$sql = "UPDATE user_stats SET stats_quiz_questions_answered=stats_quiz_questions_answered-1
				WHERE stats_actor IN (SELECT a_actor FROM quizgame_answers WHERE a_q_id = {$id} )";
				$res = $dbr->query( $sql, __METHOD__ );

				// Fix stats of those who answered the flagged question correctly
				$sql = "UPDATE user_stats SET stats_quiz_questions_correct=stats_quiz_questions_correct-1
				WHERE stats_actor IN (SELECT a_actor FROM quizgame_answers INNER JOIN quizgame_choice ON a_choice_id=choice_id WHERE a_q_id = {$id} AND choice_is_correct=1 )";
				$res = $dbr->query( $sql, __METHOD__ );

				// Update everyone's percentage who answered that question
				$sql = "UPDATE user_stats SET stats_quiz_questions_correct_percent=stats_quiz_questions_correct /stats_quiz_questions_answered
				WHERE stats_actor IN (SELECT a_actor FROM quizgame_answers WHERE a_q_id = {$id} )";
				$res = $dbr->query( $sql, __METHOD__ );
				*/

				$dbw->update(
					'quizgame_questions',
					[ 'q_flag' => QuizGameHome::$FLAG_FLAGGED, 'q_comment' => $comment ],
					[ 'q_id' => intval( $id ) ],
					__METHOD__
				);

				$dbw->update(
					'quizgame_questions',
					[ 'q_flag' => QuizGameHome::$FLAG_FLAGGED ],
					[ 'q_id' => intval( $id ) ],
					__METHOD__
				);

				$output = $this->msg( 'quizgame-ajax-flagged' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'flag', $id, $comment );
				}

				break;
			case 'deleteItem':
				$res = $dbw->select(
					[ 'quizgame_answers', 'quizgame_choice' ],
					[ 'a_actor', 'a_points', 'choice_is_correct' ],
					[ 'a_q_id' => intval( $id ) ],
					__METHOD__,
					'',
					[ 'quizgame_choice' => [ 'LEFT JOIN', 'choice_id = a_choice_id' ] ]
				);
				$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

				foreach ( $res as $row ) {
					if ( $row->choice_is_correct == 1 ) {
						$percentage = 'stats_quiz_questions_correct_percent = (stats_quiz_questions_correct - 1)/(stats_quiz_questions_answered - 1)';
					} else {
						$percentage = 'stats_quiz_questions_correct_percent = (stats_quiz_questions_correct)/(stats_quiz_questions_answered - 1)';
					}

					// Update everyone who answered this question
					$dbw->update(
						'user_stats',
						/* SET */[
							$percentage,
							'stats_quiz_questions_answered = stats_quiz_questions_answered - 1',
						],
						/* WHERE */[
							'stats_actor' => $row->a_actor
						],
						__METHOD__
					);

					// Update everyone who answered this question correct
					if ( $row->choice_is_correct == 1 ) {
						$dbw->update(
							'user_stats',
							/* SET */[
								'stats_quiz_questions_correct = stats_quiz_questions_correct - 1',
								'stats_quiz_points = stats_quiz_points - ' . (int)$row->a_points
							],
							/* WHERE */[
								'stats_actor' => $row->a_actor
							],
							__METHOD__
						);
					}

					$key = $cache->makeKey( 'user', 'stats', 'actor_id', $row->a_actor );
					$cache->delete( $key );
				}

				$dbw->delete(
					'quizgame_answers',
					[ 'a_q_id' => intval( $id ) ],
					__METHOD__
				);

				$dbw->delete(
					'quizgame_choice',
					[ 'choice_q_id' => intval( $id ) ],
					__METHOD__
				);

				$dbw->delete(
					'quizgame_questions',
					[ 'q_id' => intval( $id ) ],
					__METHOD__
				);

				$output = $this->msg( 'quizgame-ajax-deleted' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'delete', $id );
				}

				break;
			default:
				$output = $this->msg( 'quizgame-ajax-invalid-option' )->text();
				break;
		} // switch() loop end

		// This is shown to the user via AJAX.
		$data = [ 'output' => $output ];

		$this->getResult()->addValue( null, $this->getModuleName(), $data );
	}

	/**
	 * Log what was done to a particular quiz by whom at what time.
	 *
	 * @param string $what Action to log (delete, flag, protect, unflag or unprotect)
	 * @param int $id ID of the affected quiz game
	 * @param string $comment User-supplied comment; used only when flagging quizzes
	 */
	private function createLogEntry( $what, $id, $comment = '' ) {
		$logEntry = new ManualLogEntry( 'quiz', $what );
		$logEntry->setPerformer( $this->getUser() );
		$logEntry->setTarget( SpecialPage::getTitleFor( 'QuestionGameHome', (string)$id ) );
		// Flagging reason, if any
		if ( $comment ) {
			$logEntry->setComment( $comment );
		}
		$logEntry->setParameters( [
			'4::quizid' => $id
		] );

		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 * @return string The description string for this module
	 */
	public function getDescription() {
		return 'Question Game API for administrative actions';
	}

	public function needsToken() {
		return 'csrf';
	}

	public function isWriteMode() {
		return true;
	}

	public function getAllowedParams() {
		return [
			'comment' => [
				ParamValidator::PARAM_TYPE => 'string'
			],
			'id' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true
			],
			'quizaction' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			]
		];
	}

	/**
	 * Get the human-readable descriptions of all the parameters that this
	 * module accepts/requires.
	 *
	 * @deprecated since MediaWiki core 1.25
	 * @return array
	 */
	public function getParamDescription() {
		return [
			'comment' => 'Reason for flagging (used only in flagItem)',
			'id' => 'Quiz ID number',
			'quizaction' => 'What to do + the word "Item", i.e. deleteItem'
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return [
			'api.php?action=quizgame&quizaction=flagItem&comment=Inappropriate%20question&id=30',
			'api.php?action=quizgame&quizaction=deleteItem&id=30',
		];
	}

	public function getExamplesMessages() {
		return [
			'action=quizgame&quizaction=flagItem&comment=Inappropriate%20question&id=30' => 'apihelp-quizgame-example-1',
			'action=quizgame&quizaction=deleteItem&id=30' => 'apihelp-quizgame-example-2',
		];
	}
}
