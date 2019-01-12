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
		$points = $params['points'];

		// Check that all of the required parameters are present, and if it
		// ain't so, don't go any further
		if ( $answer === null || $id === null || $points === null ) {
			$this->dieWithError( 'apierror-missingparam', 'missingparam' );
		}

		if ( !is_numeric( $answer ) ) {
			$this->dieWithError( 'quizgame-ajax-nonnumeric-answer', 'nonnumericanswer' );
		}

		$dbw = wfGetDB( DB_MASTER );

		// Check if they already answered
		$s = $dbw->selectRow(
			'quizgame_answers',
			[ 'a_choice_id' ],
			[ 'a_q_id' => intval( $id ), 'a_user_name' => $user->getName() ],
			__METHOD__
		);

		if ( $s !== false ) {
			$this->dieWithError( 'quizgame-ajax-already-answered', 'alreadyanswered' );
		}

		// Add answer by user
		$dbw->insert(
			'quizgame_answers',
			[
				'a_q_id' => intval( $id ),
				'a_user_id' => $user->getId(),
				'a_user_name' => $user->getName(),
				'a_choice_id' => $answer,
				'a_points' => $points,
				'a_date' => date( 'Y-m-d H:i:s' )
			],
			__METHOD__
		);

		// If the question is being skipped, stop here
		if ( $answer == -1 ) {
			return 'ok';
		}

		// Clear out anti-cheating table
		$dbw->delete(
			'quizgame_user_view',
			[ 'uv_user_id' => $user->getId(), 'uv_q_id' => intval( $id ) ],
			__METHOD__
		);

		// Update answer picked
		$dbw->update(
			'quizgame_choice',
			[ 'choice_answer_count = choice_answer_count + 1' ],
			[ 'choice_id' => $answer ],
			__METHOD__
		);

		// Update question answered
		$dbw->update(
			'quizgame_questions',
			[ 'q_answer_count = q_answer_count + 1' ],
			[ 'q_id' => intval( $id ) ],
			__METHOD__
		);

		// Add to stats how many quizzes the user has answered
		$stats = new UserStatsTrack( $user->getId(), $user->getName() );
		$stats->incStatField( 'quiz_answered' );

		// Check if the answer was right
		$s = $dbw->selectRow(
			'quizgame_questions',
			[ 'q_answer_count' ],
			[ 'q_id' => intval( $id ) ],
			__METHOD__
		);
		if ( $s !== false ) {
			$answer_count = $s->q_answer_count;
		}

		// Check if the answer was right
		$s = $dbw->selectRow(
			'quizgame_choice',
			[ 'choice_id', 'choice_text', 'choice_answer_count' ],
			[ 'choice_q_id' => intval( $id ), 'choice_is_correct' => 1 ],
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
			$data = [
				'isRight' => $isRight,
				'rightAnswer' => addslashes( $s->choice_text ), // @todo FIXME/CHECKME: addslashes() still needed?
				'percentRight' => $percent
			];
			if ( defined( 'ApiResult::META_CONTENT' ) ) {
				// Why?
				ApiResult::setContentValue( $data, 'content', '' );
			} else {
				ApiResult::setContent( $data, '' );
			}

			if ( $s->choice_id == $answer ) {
				// Update question answered correctly for entire question
				$dbw->update(
					'quizgame_questions',
					[ 'q_answer_correct_count = q_answer_correct_count+1' ],
					[ 'q_id' => $id ],
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
				[ 'stats_quiz_questions_correct_percent = stats_quiz_questions_correct/stats_quiz_questions_answered' ],
				[ 'stats_user_id' => $user->getId() ],
				__METHOD__
			);

			$this->getResult()->addValue( null, $this->getModuleName(),
				[ 'result' => $data ]
			);
		} else {
			$this->dieWithError( 'quizgame-ajax-invalid-id', 'nosuchquestion' );
		}
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 * @return string The description string for this module
	 */
	public function getDescription() {
		return 'Question Game API for voting';
	}

	public function needsToken() {
		return 'csrf';
	}

	public function isWriteMode() {
		return true;
	}

	public function getAllowedParams() {
		return [
			'answer' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			],
			'id' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			],
			'points' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
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
			'answer' => 'Numeric answer ID',
			'id' => 'Quiz ID number',
			'points' => 'How many points are given out for the correct answer'
		];
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return [
			'api.php?action=quizgamevote&answer=3&id=245'
		];
	}

	public function getExamplesMessages() {
		return [
			'action=quizgamevote&answer=3&id=245' => 'apihelp-quizgamevote-example-1'
		];
	}
}
