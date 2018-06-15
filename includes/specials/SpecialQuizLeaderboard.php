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

		$out->addModuleStyles( 'ext.quizGame.css' );

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
			case 'points':
				$out->setPageTitle( $this->msg( 'quizgame-leaderboard-most-points' )->text() );
				$field = 'stats_quiz_points';
				break;
		}

		$dbr = wfGetDB( DB_MASTER );
		$whereConds[] = 'stats_user_id <> 0'; // Exclude anonymous users
		$res = $dbr->select(
			'user_stats',
			[
				'stats_user_id', 'stats_user_name', 'stats_quiz_points',
				'stats_quiz_questions_correct',
				'stats_quiz_questions_correct_percent'
			],
			$whereConds,
			__METHOD__,
			[ 'ORDER BY' => "{$field} DESC", 'LIMIT' => 50, 'OFFSET' => 0 ]
		);

		$quizgame_title = SpecialPage::getTitleFor( 'QuizGameHome' );

		$output = '<div class="quiz-leaderboard-nav">';

		if ( $user->isLoggedIn() ) {
			$stats = new UserStats( $user->getId(), $user->getName() );
			$stats_data = $stats->getUserStats();

			// Get users rank
			$quiz_rank = 0;
			$s = $dbr->selectRow(
				'user_stats',
				[ 'COUNT(*) AS count' ],
				[ 'stats_quiz_points > ' . $stats_data['quiz_points'] ],
				__METHOD__
			);
			if ( $s !== false ) {
				$quiz_rank = $s->count + 1;
			}
			$avatar = new wAvatar( $user->getId(), 'm' );

			$formattedTotalPoints = $lang->formatNum( $stats_data['quiz_points'] );
			$formattedCorrectAnswers = $lang->formatNum( $stats_data['quiz_correct'] );
			$formattedAnswers = $lang->formatNum( $stats_data['quiz_answered'] );
			// Display the current user's scorecard
			$output .= "<div class=\"user-rank-lb\">
				<h2>{$avatar->getAvatarURL()} " . $this->msg( 'quizgame-leaderboard-scoretitle' )->text() . '</h2>

					<p><b>' . $this->msg( 'quizgame-leaderboard-quizpoints' )->text() . "</b></p>
					<p class=\"user-rank-points\">{$formattedTotalPoints}</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-correct' )->text() . "</b></p>
					<p>{$formattedCorrectAnswers}</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-answered' )->text() . "</b></p>
					<p>{$formattedAnswers}</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-pctcorrect' )->text() . "</b></p>
					<p>{$stats_data['quiz_correct_percent']}%</p>
					<div class=\"visualClear\"></div>

					<p><b>" . $this->msg( 'quizgame-leaderboard-rank' )->text() . "</b></p>
					<p>{$quiz_rank}</p>
					<div class=\"visualClear\"></div>

				</div>";
		}

		// Build the "Order" navigation menu
		$menu = [
			$this->msg( 'quizgame-leaderboard-menu-points' )->text() => 'points',
			$this->msg( 'quizgame-leaderboard-menu-correct' )->text() => 'correct',
			$this->msg( 'quizgame-leaderboard-menu-pct' )->text() => 'percentage'
		];

		$output .= '<h1>' . $this->msg( 'quizgame-leaderboard-order-menu' )->text() . '</h1>';

		foreach ( $menu as $title => $qs ) {
			if ( $input != $qs ) {
				$output .= "<p><a href=\"{$this->getPageTitle()->getFullURL()}/{$qs}\">{$title}</a><p>";
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
		    $user_name = $row->stats_user_name;
		    $user_title = Title::makeTitle( NS_USER, $row->stats_user_name );
		    $avatar = new wAvatar( $row->stats_user_id, 'm' );
			$user_name_short = $lang->truncateForVisual( $user_name, 18 );

		    $output .= "<div class=\"top-fan-row\">
		 		   <span class=\"top-fan-num\">{$x}.</span>
				   <span class=\"top-fan\">{$avatar->getAvatarURL()}
				   <a href=\"" . $user_title->getFullURL() . '">' . $user_name_short . '</a>
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
