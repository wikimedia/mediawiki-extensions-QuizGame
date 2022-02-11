<?php

class QuizLeaderboard extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'QuizLeaderboard' );
	}

	/**
	 * Show the special page
	 *
	 * @param string|null $input Parameter passed to the page
	 */
	public function execute( $input ) {
		$lang = $this->getLanguage();
		$out = $this->getOutput();
		$user = $this->getUser();

		if ( !$input ) {
			$input = 'points';
		}

		// Set the correct robot policies, ensure that skins don't render a link to
		// Special:WhatLinksHere on their toolboxes, etc.
		$this->setHeaders();

		$out->addModuleStyles( [
			'ext.quizGame.css',
			'ext.quizGame.leaderboard.css'
		] );

		$whereConds = [];

		switch ( $input ) {
			case 'correct':
				$out->setPageTitle( $this->msg( 'quizgame-leaderboard-most-correct' )->text() );
				$field = 'stats_quiz_questions_correct';
				break;
			case 'percentage':
				$out->setPageTitle( $this->msg( 'quizgame-leaderboard-highest-percent' )->text() );
				$field = 'stats_quiz_questions_correct_percent';
				$whereConds[] = 'stats_quiz_questions_answered >= 50';
				break;
			default:
				$out->setPageTitle( $this->msg( 'quizgame-leaderboard-most-points' )->text() );
				$field = 'stats_quiz_points';
		}

		$dbr = wfGetDB( DB_PRIMARY );
		$whereConds[] = 'stats_actor IS NOT NULL'; // Exclude anonymous users
		$res = $dbr->select(
			'user_stats',
			[
				'stats_actor', 'stats_quiz_points',
				'stats_quiz_questions_correct',
				'stats_quiz_questions_correct_percent'
			],
			$whereConds,
			__METHOD__,
			[ 'ORDER BY' => "{$field} DESC", 'LIMIT' => 50, 'OFFSET' => 0 ]
		);

		$quizgame_title = SpecialPage::getTitleFor( 'QuizGameHome' );

		$output = '<div class="quiz-leaderboard-nav">';

		if ( $user->isRegistered() ) {
			$stats = new UserStats( $user->getId(), $user->getName() );
			$stats_data = $stats->getUserStats();

			// Get user's rank
			$count = $dbr->selectRowCount(
				'user_stats',
				'*',
				[ 'stats_quiz_points > ' . (int)$stats_data['quiz_points'] ],
				__METHOD__
			);
			$quiz_rank = $count + 1;
			$avatar = new wAvatar( $user->getId(), 'm' );

			$formattedTotalPoints = htmlspecialchars( $lang->formatNum( (int)$stats_data['quiz_points'] ) );
			$formattedCorrectAnswers = htmlspecialchars( $lang->formatNum( (int)$stats_data['quiz_correct'] ) );
			$formattedAnswers = htmlspecialchars( $lang->formatNum( (int)$stats_data['quiz_answered'] ) );
			// Display the current user's scorecard
			$output .= "<div class=\"user-rank-lb\">
				<h2>{$avatar->getAvatarURL()} " . $this->msg( 'quizgame-leaderboard-scoretitle' )->escaped() . '</h2>

					<p><b>' . $this->msg( 'quizgame-leaderboard-quizpoints' )->escaped() . "</b></p>
					<p class=\"user-rank-points\">{$formattedTotalPoints}</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-correct' )->escaped() . "</b></p>
					<p>{$formattedCorrectAnswers}</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-answered' )->escaped() . "</b></p>
					<p>{$formattedAnswers}</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-pctcorrect' )->escaped() . "</b></p>
					<p>{$stats_data['quiz_correct_percent']}%</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-rank' )->escaped() . "</b></p>
					<p>{$quiz_rank}</p>
					<div class=\"visualClear\"></div>

				</div>";
		}

		// Build the "Order" navigation menu
		$menu = [
			$this->msg( 'quizgame-leaderboard-menu-points' )->escaped() => 'points',
			$this->msg( 'quizgame-leaderboard-menu-correct' )->escaped() => 'correct',
			$this->msg( 'quizgame-leaderboard-menu-pct' )->escaped() => 'percentage'
		];

		$output .= '<h1>' . $this->msg( 'quizgame-leaderboard-order-menu' )->escaped() . '</h1>';

		$pt = $this->getPageTitle();
		foreach ( $menu as $title => $qs ) {
			if ( $input != $qs ) {
				$escapedURL = htmlspecialchars( $pt->getFullURL(), ENT_QUOTES );
				$output .= "<p><a href=\"{$escapedURL}/{$qs}\">{$title}</a><p>";
			} else {
				$output .= "<p><b>{$title}</b></p>";
			}
		}

		$output .= '</div>';

		$output .= '<div class="quiz-leaderboard-top-links">' .
			$this->getLinkRenderer()->makeLink(
				$quizgame_title,
				$this->msg( 'quizgame-admin-back' )->text(),
				[],
				[ 'questionGameAction' => 'launchGame' ]
			) . '</div>';

		$x = 1;
		$output .= '<div class="top-users">';

		foreach ( $res as $row ) {
			$actor = User::newFromActorId( $row->stats_actor );
			if ( !$actor || !$actor instanceof User ) {
				continue;
			}

			if ( empty( $row->$field ) ) {
				continue;
			}

			$avatar = new wAvatar( $actor->getId(), 'm' );
			$user_name_short = $lang->truncateForVisual( $actor->getName(), 18 );

			$output .= "<div class=\"top-fan-row\">
				   <span class=\"top-fan-num\">{$x}.</span>
				   <span class=\"top-fan\">{$avatar->getAvatarURL()}
				   <a href=\"" . htmlspecialchars( $actor->getUserPage()->getFullURL(), ENT_QUOTES ) . '">' .
					   htmlspecialchars( $user_name_short, ENT_QUOTES ) . '</a>
				</span>';

			switch ( $input ) {
				case 'correct':
					$stat = $this->msg( 'quizgame-leaderboard-desc-correct', $lang->formatNum( $row->$field ) )->parse();
					break;
				case 'percentage':
					$stat = $this->msg( 'quizgame-leaderboard-desc-pct', $lang->formatNum( $row->$field * 100 ) )->parse();
					break;
				case 'points':
					$stat = $this->msg( 'quizgame-leaderboard-desc-points', $lang->formatNum( $row->$field ) )->parse();
					break;
				default:
					$stat = '';
			}

			$output .= "<span class=\"top-fan-points\"><b>{$stat}</b></span>";
			$output .= '<div class="visualClear"></div>';
			$output .= '</div>';
			$x++;
		}

		$output .= '</div><div class="visualClear"></div>';

		$out->addHTML( $output );
	}
}
