<?php
/**
 * QuizGame API -- handles the AJAX requests done by /js/QuizGame.js, such as
 * when the user clicks "Flag this question" etc.
 *
 * @file
 * @ingroup API
 */
class ApiQuizGame extends ApiBase {

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
		$comment = ( isset( $params['comment'] ) ? $params['comment'] : '' ); // reason for flagging (used only in flagItem)
		$id = $params['id']; // quiz ID number
		$key = $params['key']; // MD5 hash of the word 'SALT' and the quiz ID number

		// Check that all of the required parameters are present, and if it
		// ain't so, don't go any further
		if ( $action === null || $id === null || $key === null ) {
			$this->dieUsageMsg( 'missingparam' );
		}

		// Check that the key is correct to make sure that no-one's trying any
		// funny business
		// We cannot check for !$user->isAllowed( 'quizadmin' ) since this
		// function also handles flagging...all the other actions are admin-only,
		// though
		if ( $key != md5( 'SALT' . $id ) ) {
			$this->dieUsage( wfMessage( 'quizgame-ajax-invalid-key' )->text(), 'invalidkey' );
		}

		// ApiBase's getDB() supports only slave connections, lame...
		$dbw = wfGetDB( DB_MASTER );

		switch ( $action ) {
			case 'unprotectItem':
				$dbw->update(
					'quizgame_questions',
					array( 'q_flag' => QuizGameHome::$FLAG_NONE ),
					array( 'q_id' => intval( $id ) ),
					__METHOD__
				);

				$output = wfMessage( 'quizgame-ajax-unprotected' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'unprotect', $id );
				}

				break;
			case 'protectItem':
				$dbw->update(
					'quizgame_questions',
					array( 'q_flag' => QuizGameHome::$FLAG_PROTECT ),
					array( 'q_id' => intval( $id ) ),
					__METHOD__
				);

				$output = wfMessage( 'quizgame-ajax-protected' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'protect', $id, $comment );
				}

				break;
			case 'unflagItem':
				// Fix stats of those who answered the flagged question
				/*
				$sql = "UPDATE user_stats SET stats_quiz_questions_answered=stats_quiz_questions_answered+1
				WHERE stats_user_id IN (SELECT a_user_id FROM quizgame_answers WHERE a_q_id = {$id})";
				$res = $dbr->query( $sql, __METHOD__ );

				// Fix Stats of those who answered the flagged question correctly
				$sql = "UPDATE user_stats SET stats_quiz_questions_correct=stats_quiz_questions_correct+1
				WHERE stats_user_id IN (SELECT a_user_id FROM quizgame_answers INNER JOIN quizgame_choice ON a_choice_id=choice_id WHERE a_q_id = {$id} AND choice_is_correct=1 )";
				$res = $dbr->query( $sql, __METHOD__ );

				// Update everyone's percentage who answered that question
				$sql = "UPDATE user_stats SET stats_quiz_questions_correct_percent=stats_quiz_questions_correct /stats_quiz_questions_answered
				WHERE stats_user_id IN (SELECT a_user_id FROM quizgame_answers WHERE a_q_id = {$id} )";
				$res = $dbr->query( $sql, __METHOD__ );
				*/

				$dbw->update(
					'quizgame_questions',
					array( 'q_flag' => QuizGameHome::$FLAG_NONE, 'q_comment' => '' ),
					array( 'q_id' => intval( $id ) ),
					__METHOD__
				);

				$output = wfMessage( 'quizgame-ajax-unflagged' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'unflag', $id );
				}

				break;
			case 'flagItem':
				/*
				// Fix stats of those who answered the flagged question
				$sql = "UPDATE user_stats SET stats_quiz_questions_answered=stats_quiz_questions_answered-1
				WHERE stats_user_id IN (SELECT a_user_id FROM quizgame_answers WHERE a_q_id = {$id} )";
				$res = $dbr->query( $sql, __METHOD__ );

				// Fix stats of those who answered the flagged question correctly
				$sql = "UPDATE user_stats SET stats_quiz_questions_correct=stats_quiz_questions_correct-1
				WHERE stats_user_id IN (SELECT a_user_id FROM quizgame_answers INNER JOIN quizgame_choice ON a_choice_id=choice_id WHERE a_q_id = {$id} AND choice_is_correct=1 )";
				$res = $dbr->query( $sql, __METHOD__ );

				// Update everyone's percentage who answered that question
				$sql = "UPDATE user_stats SET stats_quiz_questions_correct_percent=stats_quiz_questions_correct /stats_quiz_questions_answered
				WHERE stats_user_id IN (SELECT a_user_id FROM quizgame_answers WHERE a_q_id = {$id} )";
				$res = $dbr->query( $sql, __METHOD__ );
				*/

				$dbw->update(
					'quizgame_questions',
					array( 'q_flag' => QuizGameHome::$FLAG_FLAGGED, 'q_comment' => $comment ),
					array( 'q_id' => intval( $id ) ),
					__METHOD__
				);

				$dbw->update(
					'quizgame_questions',
					array( 'q_flag' => QuizGameHome::$FLAG_FLAGGED ),
					array( 'q_id' => intval( $id ) ),
					__METHOD__
				);

				$output = wfMessage( 'quizgame-ajax-flagged' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'flag', $id, $comment );
				}

				break;
			case 'deleteItem':
				$res = $dbw->select(
					array( 'quizgame_answers', 'quizgame_choice' ),
					array( 'a_user_id', 'a_points', 'choice_is_correct' ),
					array( 'a_q_id' => intval( $id ) ),
					__METHOD__,
					'',
					array( 'quizgame_choice' => array( 'LEFT JOIN', 'choice_id = a_choice_id' ) )
				);

				foreach ( $res as $row ) {
					if ( $row->choice_is_correct == 1 ) {
						$percentage = 'stats_quiz_questions_correct_percent = (stats_quiz_questions_correct - 1)/(stats_quiz_questions_answered - 1)';
					} else {
						$percentage = 'stats_quiz_questions_correct_percent = (stats_quiz_questions_correct)/(stats_quiz_questions_answered - 1)';
					}

					// Update everyone who answered this question
					$dbw->update(
						'user_stats',
						/* SET */array(
							$percentage,
							'stats_quiz_questions_answered = stats_quiz_questions_answered - 1',
						),
						/* WHERE */array(
							'stats_user_id' => $row->a_user_id
						),
						__METHOD__
					);

					// Update everyone who answered this question correct
					if ( $row->choice_is_correct == 1 ) {
						$dbw->update(
							'user_stats',
							/* SET */array(
								'stats_quiz_questions_correct = stats_quiz_questions_correct - 1',
								'stats_quiz_points = stats_quiz_points-' . $row->a_points
							),
							/* WHERE */array(
								'stats_user_id' => $row->a_user_id
							),
							__METHOD__
						);
					}

					global $wgMemc;
					$key = $wgMemc->makeKey( 'user', 'stats', $row->a_user_id );
					$wgMemc->delete( $key );
				}

				$dbw->delete(
					'quizgame_answers',
					array( 'a_q_id' => intval( $id ) ),
					__METHOD__
				);

				$dbw->delete(
					'quizgame_choice',
					array( 'choice_q_id' => intval( $id ) ),
					__METHOD__
				);

				$dbw->delete(
					'quizgame_questions',
					array( 'q_id' => intval( $id ) ),
					__METHOD__
				);

				$output = wfMessage( 'quizgame-ajax-deleted' )->text();

				// Add a log entry if quiz logging is enabled
				if ( $wgQuizLogs ) {
					$this->createLogEntry( 'delete', $id );
				}

				break;
			default:
				$output = wfMessage( 'quizgame-ajax-invalid-option' )->text();
				break;
		} // switch() loop end

		// This is shown to the user via AJAX.
		$data = array( 'output' => $output );

		// Dear API, Y U NO MAKE SENSE?
		// The following, which is also used in the voting API, does NOT work:
		//ApiResult::setContent( $data, '' );
		// But this one, similar to what /includes/api/ApiBlock.php does, works:
		$this->getResult()->addValue( null, $this->getModuleName(), $data );
	}

	/**
	 * Log what was done to a particular quiz by whom at what time.
	 *
	 * @param $what String: action to log (delete, flag, protect, unflag or unprotect)
	 * @param $id Integer: ID of the affected quiz game
	 * @param $comment String: user-supplied comment; used only when flagging quizzes
	 */
	private function createLogEntry( $what, $id, $comment = '' ) {
		$logEntry = new ManualLogEntry( 'quiz', $what );
		$logEntry->setPerformer( $this->getUser() );
		$logEntry->setTarget( SpecialPage::getTitleFor( 'QuestionGameHome', $id ) );
		// Flagging reason, if any
		if ( !empty( $comment ) ) {
			$logEntry->setComment( $comment );
		}
		$logEntry->setParameters( array(
			'4::quizid' => $id
		) );

		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 * @return String: the description string for this module
	 */
	public function getDescription() {
		return 'Question Game API for administrative actions';
	}

	public function getAllowedParams() {
		return array(
			'comment' => array(
				ApiBase::PARAM_TYPE => 'string'
			),
			'id' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'key' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'quizaction' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			)
		);
	}

	/**
	 * Get the human-readable descriptions of all the parameters that this
	 * module accepts/requires.
	 *
	 * @deprecated since MediaWiki core 1.25
	 * @return Array
	 */
	public function getParamDescription() {
		return array(
			'comment' => 'Reason for flagging (used only in flagItem)',
			'id' => 'Quiz ID number',
			'key' => 'MD5 hash of the salt and the quiz ID number',
			'quizaction' => 'What to do + the word "Item", i.e. deleteItem'
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=quizgame&quizaction=flagItem&comment=Inappropriate%20question&id=30&key=ThisObviouslyIsntARealKey',
			'api.php?action=quizgame&quizaction=deleteItem&id=30&key=YetAnotherExampleKey',
		);
	}

	public function getExamplesMessages() {
		return array(
			'action=quizgame&quizaction=flagItem&comment=Inappropriate%20question&id=30&key=ThisObviouslyIsntARealKey' => 'apihelp-quizgame-example-1',
			'action=quizgame&quizaction=deleteItem&id=30&key=YetAnotherExampleKey' => 'apihelp-quizgame-example-2',
		);
	}
}
