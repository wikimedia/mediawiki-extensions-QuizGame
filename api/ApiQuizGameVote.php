<?php
/**
 * QuizGame voting API
 *
 * @file
 * @ingroup API
 */
class ApiQuizGameVote extends ApiBase {

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
		$params = $this->extractRequestParams();
		$user = $this->getUser();

		$answer = $params['answer']; // numeric answer ID
		$id = $params['id']; // quiz ID number
		$key = $params['key']; // MD5 hash of the salt and the quiz ID number
		$points = $params['points'];

		// Check that all of the required parameters are present, and if it
		// ain't so, don't go any further
		if ( $answer === null || $id === null || $key === null || $points === null ) {
			$this->dieUsageMsg( 'missingparam' );
		}

		// Check that the key is correct to make sure that no-one's trying any
		// funny business
		if ( $key != md5( 'SALT' . $id ) ) {
			$this->dieUsage( wfMessage( 'quizgame-ajax-invalid-key' )->text(), 'invalidkey' );
		}

		if ( !is_numeric( $answer ) ) {
			$this->dieUsage( wfMessage( 'quizgame-ajax-nonnumeric-answer' )->text(), 'nonnumericanswer' );
		}

		$dbw = wfGetDB( DB_MASTER );

		// Check if they already answered
		$s = $dbw->selectRow(
			'quizgame_answers',
			array( 'a_choice_id' ),
			array( 'a_q_id' => intval( $id ), 'a_user_name' => $user->getName() ),
			__METHOD__
		);

		if ( $s !== false ) {
			$this->dieUsage( wfMessage( 'quizgame-ajax-already-answered' )->text(), 'alreadyanswered' );
		}

		// Add answer by user
		$dbw->insert(
			'quizgame_answers',
			array(
				'a_q_id' => intval( $id ),
				'a_user_id' => $user->getId(),
				'a_user_name' => $user->getName(),
				'a_choice_id' => $answer,
				'a_points' => $points,
				'a_date' => date( 'Y-m-d H:i:s' )
			),
			__METHOD__
		);

		// If the question is being skipped, stop here
		if ( $answer == -1 ) {
			return 'ok';
		}

		// Clear out anti-cheating table
		$dbw->delete(
			'quizgame_user_view',
			array( 'uv_user_id' => $user->getId(), 'uv_q_id' => intval( $id ) ),
			__METHOD__
		);
		$dbw->commit();

		// Update answer picked
		$dbw->update(
			'quizgame_choice',
			array( 'choice_answer_count = choice_answer_count + 1' ),
			array( 'choice_id' => $answer ),
			__METHOD__
		);

		// Update question answered
		$dbw->update(
			'quizgame_questions',
			array( 'q_answer_count = q_answer_count + 1' ),
			array( 'q_id' => intval( $id ) ),
			__METHOD__
		);

		// Add to stats how many quizzes the user has answered
		$stats = new UserStatsTrack( $user->getId(), $user->getName() );
		$stats->incStatField( 'quiz_answered' );

		// Check if the answer was right
		$s = $dbw->selectRow(
			'quizgame_questions',
			array( 'q_answer_count' ),
			array( 'q_id' => intval( $id ) ),
			__METHOD__
		);
		if ( $s !== false ) {
			$answer_count = $s->q_answer_count;
		}

		// Check if the answer was right
		$s = $dbw->selectRow(
			'quizgame_choice',
			array( 'choice_id', 'choice_text', 'choice_answer_count' ),
			array( 'choice_q_id' => intval( $id ), 'choice_is_correct' => 1 ),
			__METHOD__
		);

		if ( $s !== false ) {
			if ( $answer_count ) {
				$formattedNumber = number_format( $s->choice_answer_count / $answer_count * 100, 1 );
				$percent = str_replace( '.0', '', $formattedNumber );
			} else {
				$percent = 0;
			}

			$isRight = ( ( $s->choice_id == $answer ) ? 'true' : 'false' );
			$data = array(
				'isRight' => $isRight,
				'rightAnswer' => addslashes( $s->choice_text ), // @todo FIXME/CHECKME: addslashes() still needed?
				'percentRight' => $percent
			);
			ApiResult::setContent( $data, '' );

			if ( $s->choice_id == $answer ) {
				// Update question answered correctly for entire question
				$dbw->update(
					'quizgame_questions',
					array( 'q_answer_correct_count = q_answer_correct_count+1' ),
					array( 'q_id' => $id ),
					__METHOD__
				);

				// Add to stats how many quizzes the user has answered correctly
				$stats->incStatField( 'quiz_correct' );

				// Add to point total
				if ( !$user->isBlocked() && is_numeric( $points ) ) {
					$stats->incStatField( 'quiz_points', $points );
				}
			}

			// Update the users % correct
			$dbw->update(
				'user_stats',
				array( 'stats_quiz_questions_correct_percent = stats_quiz_questions_correct/stats_quiz_questions_answered' ),
				array( 'stats_user_id' => $user->getId() ),
				__METHOD__
			);

			$this->getResult()->addValue( null, $this->getModuleName(),
				array( 'result' => $data )
			);
		} else {
			$this->dieUsage( wfMessage( 'quizgame-ajax-invalid-id' )->text(), 'nosuchquestion' );
		}
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 * @return String: the description string for this module
	 */
	public function getDescription() {
		return 'Question Game API for voting';
	}

	public function getAllowedParams() {
		return array(
			'answer' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'id' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'key' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'points' => array(
				ApiBase::PARAM_TYPE => 'integer',
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
			'answer' => 'Numeric answer ID',
			'id' => 'Quiz ID number',
			'key' => 'MD5 hash of the salt and the quiz ID number',
			'points' => 'How many points are given out for the correct answer'
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=quizgamevote&answer=3&id=245&key=ThisObviouslyIsntARealKey'
		);
	}

	public function getExamplesMessages() {
		return array(
			'action=quizgamevote&answer=3&id=245&key=ThisObviouslyIsntARealKey' => 'apihelp-quizgamevote-example-1'
		);
	}
}
