<?php

class QuizRecalcStats extends UnlistedSpecialPage {

	/**
	 * Construct the MediaWiki special page
	 */
	public function __construct() {
		parent::__construct( 'QuizRecalcStats' );
	}

	/**
	 * Show the special page
	 *
	 * @param string|null $par Parameter passed to the page
	 */
	public function execute( $par ) {
		$out = $this->getOutput();
		$user = $this->getUser();

		// Only Quiz Administrators should be allowed to access this page
		if ( !$user->isAllowed( 'quizadmin' ) ) {
			throw new ErrorPageError( 'error', 'badaccess-group0' );
		}

		// Show a message if the database is in read-only mode
		$this->checkReadOnly();

		// If user is blocked, s/he doesn't need to access this page
		$block = $user->getBlock();
		if ( $block ) {
			throw new UserBlockedError( $block );
		}

		// Set the correct robot policies, ensure that skins don't render a link to
		// Special:WhatLinksHere on their toolboxes, etc.
		$this->setHeaders();

		// Empty page title for now. Literally better than showing ⧼quizrecalcstats⧽
		// as the page title.
		$out->setPageTitle( '' );

		$dbw = wfGetDB( DB_PRIMARY );
		$res = $dbw->select(
			'user_stats',
			[ 'stats_actor' ],
			[ 'stats_quiz_questions_correct >= stats_quiz_questions_answered' ],
			__METHOD__
		);

		$count = 0;

		// @todo FIXME: SELECT SUM(a_points) ... query below *can* return NULL
		foreach ( $res as $row ) {
			$stats_actor = $dbw->addQuotes( $row->stats_actor );
			$sql = "UPDATE {$dbw->tableName( 'user_stats' )} SET stats_quiz_points = (
				SELECT SUM(a_points) FROM {$dbw->tableName( 'quizgame_answers' )}
				INNER JOIN {$dbw->tableName( 'quizgame_choice' )} ON a_choice_id=choice_id
				WHERE a_actor = {$stats_actor} AND choice_is_correct=1),
				stats_quiz_questions_correct = (
				SELECT COUNT(*) FROM {$dbw->tableName( 'quizgame_answers' )}
				INNER JOIN {$dbw->tableName( 'quizgame_choice' )} ON a_choice_id=choice_id
				WHERE a_actor = {$stats_actor} AND choice_is_correct=1),

				stats_quiz_questions_answered = (
				SELECT COUNT(*) FROM {$dbw->tableName( 'quizgame_answers' )}
				WHERE a_actor = {$stats_actor} )

				WHERE stats_actor = {$stats_actor}";

			$res2 = $dbw->query( $sql, __METHOD__ );
			/*
			// Database::selectField() does not take the last (join conds) arg
			// How typical...
			$quizPoints = $dbw->select(
				[ 'quizgame_answers', 'quizgame_choice' ],
				'SUM(a_points) AS sum',
				[
					'a_actor' => $row->stats_actor,
					'choice_is_correct' => 1
				],
				__METHOD__,
				[ 'LIMIT' => 1 ],
				[
					'quizgame_choice' =>
						[ 'INNER JOIN', 'a_choice_id = choice_id' ]
				]
			);

			$correct = $dbw->select(
				[ 'quizgame_answers', 'quizgame_choice' ],
				[ 'COUNT(*) AS count' ],
				[
					'a_actor' => $row->stats_actor,
					'choice_is_correct' => 1
				],
				__METHOD__,
				[],
				[
					'quizgame_choice' =>
						[ 'INNER JOIN', 'a_choice_id = choice_id' ]
				]
			);

			$answered = $dbw->selectField(
				'quizgame_answers',
				'COUNT(*)',
				[ 'a_actor' => $row->stats_actor ],
				__METHOD__
			);

			$final = $dbw->update(
				'user_stats',
				[
					'stats_quiz_points' => intval( $quizPoints->sum ),
					'stats_quiz_questions_correct' => intval( $correct->count ),
					'stats_quiz_questions_answered' => intval( $answered )
				],
				[ 'stats_actor' => $row->stats_actor ],
				__METHOD__
			);
			*/
			// Update the users % correct
			$dbw->update(
				'user_stats',
				[ 'stats_quiz_questions_correct_percent=stats_quiz_questions_correct/stats_quiz_questions_answered' ],
				[ 'stats_actor' => $row->stats_actor ],
				__METHOD__
			);
			$count++;
		}
		$out->addHTML( "Updated {$count} users" );
	}
}
