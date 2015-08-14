<?php

class ViewQuizzes extends UnlistedSpecialPage {

	/**
	 * Construct the MediaWiki special page
	 */
	public function __construct() {
		parent::__construct( 'ViewQuizzes' );
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		global $wgUploadPath;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$title = $this->getPageTitle();

		// Add CSS & JS
		$out->addModuleStyles( 'ext.quizGame.css' );
		$out->addModules( 'ext.quizGame' );

		// Page either most or newest for everyone
		$type = $request->getVal( 'type' );
		if( !$type ) {
			$type = 'newest';
		}
		if( $type == 'newest' ) {
			$order = 'q_date';
		}
		if( $type == 'most' ) {
			$order = 'q_answer_count';
		}

		// Pagination
		$per_page = 20;
		$page = $request->getInt( 'page', 1 );

		$limit = $per_page;
		$limitvalue = 0; // OFFSET for SQL queries

		if ( $limit > 0 && $page ) {
			$limitvalue = $page * $limit - ( $limit );
		}

		$quizGameHome = SpecialPage::getTitleFor( 'QuizGameHome' );
		$output = '<div class="view-quizzes-top-links">' .
			Linker::link(
				$quizGameHome,
				$this->msg( 'quizgame-playneverending' )->text(),
				array(),
				array( 'questionGameAction' => 'launchGame' )
			) . ' - ' .
			Linker::link(
				$quizGameHome,
				$this->msg( 'quizgame-viewquizzes-create' )->text(),
				array(),
				array( 'questionGameAction' => 'createForm' )
			) . '<br /><br />
		</div>

		<div class="view-quizzes-navigation">
			<h2>' . $this->msg( 'quizgame-leaderboard-order-menu' )->text() . '</h2>';

		$dbr = wfGetDB( DB_MASTER );

		$where = array();
		$where[] = 'q_flag <> ' . QUIZGAME_FLAG_FLAGGED;

		// Display only a user's most or newest
		$user = $request->getVal( 'user' );
		$linkQueryParameters = array();
		if ( $user ) {
			$where['q_user_name'] = $user;
			$linkQueryParameters['user'] = $user;
		}

		if( $type == 'newest' ) {
			$linkQueryParameters['type'] = 'most';
			$output .= '<p><b>' . $this->msg( 'quizgame-newest' )->text() . '</b></p>
				<p>' . Linker::link(
					$title,
					$this->msg( 'quizgame-popular' )->text(),
					array(),
					$linkQueryParameters
				) . '</p>';
		} else {
			$linkQueryParameters['type'] = 'newest';
			$output .= '<p>' . Linker::link(
				$title,
				$this->msg( 'quizgame-newest' )->text(),
				array(),
				$linkQueryParameters
			) . '</p>
				<p><b>' . $this->msg( 'quizgame-popular' )->text() . '</b></p>';
		}

		$output .= '</div>';

		if ( $user ) {
			$out->setPageTitle( $this->msg( 'quizgame-viewquizzes-title-by-user', $user )->parse() );
		} else {
			$out->setPageTitle( $this->msg( 'quizgame-viewquizzes-title' )->text() );
		}

		$res = $dbr->select(
			'quizgame_questions',
			array(
				'q_id', 'q_user_id', 'q_user_name', 'q_text',
				'UNIX_TIMESTAMP(q_date) AS quiz_date', 'q_picture',
				'q_answer_count'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => "$order DESC",
				'LIMIT' => $limit,
				'OFFSET' => $limitvalue
			)
		);

		$res_total = $dbr->select(
			'quizgame_questions',
			'COUNT(*) AS total_quizzes',
			$where,
			__METHOD__
		);
		$row_total = $dbr->fetchObject( $res_total );
		$total = $row_total->total_quizzes;

		$output .= '<div class="view-quizzes">';

		$x = ( ( $page - 1 ) * $per_page ) + 1;

		foreach ( $res as $row ) {
			$user_create = $row->q_user_name;
			$user_id = $row->q_user_id;
			$avatar = new wAvatar( $user_id, 'm' );
			$quiz_title = $row->q_text;
			$quiz_date = $row->quiz_date;
			$quiz_answers = $row->q_answer_count;
			$quiz_id = $row->q_id;
			$row_id = "quizz-row-{$x}";

			$url = htmlspecialchars( $quizGameHome->getFullURL( array(
				'questionGameAction' => 'renderPermalink',
				'permalinkID' => $quiz_id
			) ) );
			// Hover support is done in /js/QuizGame.js
			if ( ( $x < $total ) && ( $x % $per_page != 0 ) ) {
				$output .= "<div class=\"view-quizzes-row\" id=\"{$row_id}\" onclick=\"window.location='" . $url . '\'">';
			} else {
				$output .= "<div class=\"view-quizzes-row-bottom\" id=\"{$row_id}\" onclick=\"window.location='" . $url . '\'">';
			}

			$output .= "<div class=\"view-quizzes-number\">{$x}.</div>
				<div class=\"view-quizzes-user-image\">{$avatar->getAvatarURL()}</div>
				<div class=\"view-quizzes-user-name\">{$user_create}</div>
				<div class=\"view-quizzes-text\">
					<p><b><u>{$quiz_title}</u></b></p>
					<p class=\"view-quizzes-num-answers\">" .
						$this->msg( 'quizgame-answered', $quiz_answers )->parse() . '</p>
					<p class="view-quizzes-time">(' .
						$this->msg( 'quizgame-time-ago', self::getTimeAgo( $quiz_date ) )->parse() .
					')</p>
				</div>
				<div class="visualClear"></div>
			</div>';

			$x++;
		}

		$output .= '</div>
		<div class="visualClear"></div>';

		$numofpages = $total / $per_page;

		if( $numofpages > 1 ) {
			$output .= '<div class="view-quizzes-page-nav">';
			if( $page > 1 ) {
				$linkQueryParameters['type'] = 'most';
				$linkQueryParameters['page'] = ( $page - 1 );
				$output .= Linker::link(
					$title,
					$this->msg( 'quizgame-prev' )->text(),
					array(),
					$linkQueryParameters
				) . $this->msg( 'word-separator' )->text();
			}

			if( ( $total % $per_page ) != 0 ) {
				$numofpages++;
			}
			if( $numofpages >= 9 && $page < $total ) {
				$numofpages = 9 + $page;
			}
			if( $numofpages >= ( $total / $per_page ) ) {
				$numofpages = ( $total / $per_page ) + 1;
			}

			for( $i = 1; $i <= $numofpages; $i++ ) {
				if( $i == $page ) {
					$output .= ( $i . ' ' );
				} else {
					$linkQueryParameters['type'] = 'most';
					$linkQueryParameters['page'] = $i;
					$output .= Linker::link(
						$title,
						$i,
						array(),
						$linkQueryParameters
					) . $this->msg( 'word-separator' )->text();
				}
			}

			if( ( $total - ( $per_page * $page ) ) > 0 ) {
				$linkQueryParameters['type'] = 'most';
				$linkQueryParameters['page'] = ( $page + 1 );
				$output .= $this->msg( 'word-separator' )->text() .
					Linker::link(
						$title,
						$this->msg( 'quizgame-nav-next' )->text(),
						array(),
						$linkQueryParameters
					);
			}
			$output .= '</div>';
		}

		$out->addHTML( $output );
	}

	/**
	 * The following three functions are borrowed
	 * from includes/wikia/GlobalFunctionsNY.php
	 */
	static function dateDiff( $date1, $date2 ) {
		$dtDiff = $date1 - $date2;

		$totalDays = intval( $dtDiff / ( 24 * 60 * 60 ) );
		$totalSecs = $dtDiff - ( $totalDays * 24 * 60 * 60 );
		$dif['w'] = intval( $totalDays / 7 );
		$dif['d'] = $totalDays;
		$dif['h'] = $h = intval( $totalSecs / ( 60 * 60 ) );
		$dif['m'] = $m = intval( ( $totalSecs - ( $h * 60 * 60 ) ) / 60 );
		$dif['s'] = $totalSecs - ( $h * 60 * 60 ) - ( $m * 60 );

		return $dif;
	}

	static function getTimeOffset( $time, $timeabrv, $timename ) {
		$timeStr = '';
		if( $time[$timeabrv] > 0 ) {
			$timeStr = wfMessage( "quizgame-time-{$timename}", $time[$timeabrv] )->parse();
		}
		if( $timeStr ) {
			$timeStr .= ' ';
		}
		return $timeStr;
	}

	static function getTimeAgo( $time ) {
		$timeArray = self::dateDiff( time(), $time );
		$timeStr = '';
		$timeStrD = self::getTimeOffset( $timeArray, 'd', 'days' );
		$timeStrH = self::getTimeOffset( $timeArray, 'h', 'hours' );
		$timeStrM = self::getTimeOffset( $timeArray, 'm', 'minutes' );
		$timeStrS = self::getTimeOffset( $timeArray, 's', 'seconds' );
		$timeStr = $timeStrD;
		if( $timeStr < 2 ) {
			$timeStr .= $timeStrH;
			$timeStr .= $timeStrM;
			if( !$timeStr ) {
				$timeStr .= $timeStrS;
			}
		}
		if( !$timeStr ) {
			$timeStr = wfMessage( 'quizgame-time-seconds', 1 )->parse();
		}
		return $timeStr;
	}

}