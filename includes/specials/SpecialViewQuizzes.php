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
	 * @param string|null $par Parameter passed to the page
	 */
	public function execute( $par ) {
		global $wgUploadPath;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$title = $this->getPageTitle();

		// Set the correct robot policies, ensure that skins don't render a link to
		// Special:WhatLinksHere on their toolboxes, etc.
		$this->setHeaders();

		// Add CSS & JS
		$out->addModuleStyles( 'ext.quizGame.css' );
		$out->addModules( 'ext.quizGame' );

		// Page either most or newest for everyone
		$type = $request->getVal( 'type' ) ?: 'newest';
		$order = $type === 'most' ? 'q_answer_count' : 'q_date';

		// Pagination
		$per_page = 20;
		$page = $request->getInt( 'page', 1 );

		$limit = $per_page;
		$limitvalue = 0; // OFFSET for SQL queries

		// @phan-suppress-next-line PhanSuspiciousValueComparison
		if ( $limit > 0 && $page ) {
			$limitvalue = $page * $limit - ( $limit );
		}

		$linkRenderer = $this->getLinkRenderer();
		$quizGameHome = SpecialPage::getTitleFor( 'QuizGameHome' );
		$output = '<div class="view-quizzes-top-links">' .
			$linkRenderer->makeLink(
				$quizGameHome,
				$this->msg( 'quizgame-playneverending' )->text(),
				[],
				[ 'questionGameAction' => 'launchGame' ]
			) . ' - ' .
			$linkRenderer->makeLink(
				$quizGameHome,
				$this->msg( 'quizgame-viewquizzes-create' )->text(),
				[],
				[ 'questionGameAction' => 'createForm' ]
			) . '<br /><br />
		</div>

		<div class="view-quizzes-navigation">
			<h2>' . $this->msg( 'quizgame-leaderboard-order-menu' )->escaped() . '</h2>';

		$dbr = wfGetDB( DB_MASTER );

		$where = [];
		$where[] = 'q_flag <> ' . QuizGameHome::$FLAG_FLAGGED;

		// Display only a user's most or newest
		$user = $request->getVal( 'user' );
		$linkQueryParameters = [];
		if ( $user ) {
			$u = User::newFromName( $user );
			if ( $u && $u instanceof User ) {
				$where['q_actor'] = $u->getActorId();
			}
			$linkQueryParameters['user'] = $user;
		}

		if ( $type == 'newest' ) {
			$linkQueryParameters['type'] = 'most';
			$output .= '<p><b>' . $this->msg( 'quizgame-newest' )->escaped() . '</b></p>
				<p>' . $linkRenderer->makeLink(
					$title,
					$this->msg( 'quizgame-popular' )->text(),
					[],
					$linkQueryParameters
				) . '</p>';
		} else {
			$linkQueryParameters['type'] = 'newest';
			$output .= '<p>' . $linkRenderer->makeLink(
				$title,
				$this->msg( 'quizgame-newest' )->text(),
				[],
				$linkQueryParameters
			) . '</p>
				<p><b>' . $this->msg( 'quizgame-popular' )->escaped() . '</b></p>';
		}

		$output .= '</div>';

		if ( $user ) {
			$out->setPageTitle( $this->msg( 'quizgame-viewquizzes-title-by-user', $user )->parse() );
		} else {
			$out->setPageTitle( $this->msg( 'quizgame-viewquizzes-title' )->text() );
		}

		$res = $dbr->select(
			'quizgame_questions',
			[
				'q_id', 'q_actor', 'q_text',
				'q_date', 'q_picture', 'q_answer_count'
			],
			$where,
			__METHOD__,
			[
				'ORDER BY' => "$order DESC",
				'LIMIT' => $limit,
				'OFFSET' => $limitvalue
			]
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
			$creator = User::newFromActorId( $row->q_actor );
			if ( !$creator || !$creator instanceof User ) {
				continue;
			}
			$safeUserName = htmlspecialchars( $creator->getName(), ENT_QUOTES );
			$avatar = new wAvatar( $creator->getId(), 'm' );
			$quiz_title = htmlspecialchars( $row->q_text );
			$quiz_date = (int)wfTimestamp( TS_UNIX, $row->q_date );
			$quiz_answers = $row->q_answer_count;
			$quiz_id = $row->q_id;
			$row_id = "quizz-row-{$x}";

			$url = htmlspecialchars( $quizGameHome->getFullURL( [
				'questionGameAction' => 'renderPermalink',
				'permalinkID' => $quiz_id
			] ) );
			if ( ( $x < $total ) && ( $x % $per_page != 0 ) ) {
				$output .= "<div class=\"view-quizzes-row\" id=\"{$row_id}\" onclick=\"window.location='" . $url . '\'">';
			} else {
				$output .= "<div class=\"view-quizzes-row-bottom\" id=\"{$row_id}\" onclick=\"window.location='" . $url . '\'">';
			}

			$output .= "<div class=\"view-quizzes-number\">{$x}.</div>
				<div class=\"view-quizzes-user-image\">{$avatar->getAvatarURL()}</div>
				<div class=\"view-quizzes-user-name\">{$safeUserName}</div>
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

		if ( $numofpages > 1 ) {
			$output .= '<div class="view-quizzes-page-nav">';
			if ( $page > 1 ) {
				$linkQueryParameters['type'] = 'most';
				$linkQueryParameters['page'] = ( $page - 1 );
				$output .= $linkRenderer->makeLink(
					$title,
					$this->msg( 'quizgame-prev' )->text(),
					[],
					$linkQueryParameters
				) . $this->msg( 'word-separator' )->escaped();
			}

			if ( ( $total % $per_page ) != 0 ) {
				$numofpages++;
			}
			if ( $numofpages >= 9 && $page < $total ) {
				$numofpages = 9 + $page;
			}
			if ( $numofpages >= ( $total / $per_page ) ) {
				$numofpages = ( $total / $per_page ) + 1;
			}

			for ( $i = 1; $i <= $numofpages; $i++ ) {
				if ( $i == $page ) {
					$output .= ( $i . ' ' );
				} else {
					$linkQueryParameters['type'] = 'most';
					$linkQueryParameters['page'] = $i;
					$output .= $linkRenderer->makeLink(
						$title,
						(string)$i,
						[],
						$linkQueryParameters
					) . $this->msg( 'word-separator' )->escaped();
				}
			}

			if ( ( $total - ( $per_page * $page ) ) > 0 ) {
				$linkQueryParameters['type'] = 'most';
				$linkQueryParameters['page'] = ( $page + 1 );
				$output .= $this->msg( 'word-separator' )->escaped() .
					$linkRenderer->makeLink(
						$title,
						$this->msg( 'quizgame-nav-next' )->text(),
						[],
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
		$dif = [];
		$dif['w'] = intval( $totalDays / 7 );
		$dif['d'] = $totalDays;
		$dif['h'] = $h = intval( $totalSecs / ( 60 * 60 ) );
		$dif['m'] = $m = intval( ( $totalSecs - ( $h * 60 * 60 ) ) / 60 );
		$dif['s'] = $totalSecs - ( $h * 60 * 60 ) - ( $m * 60 );

		return $dif;
	}

	static function getTimeOffset( $time, $timeabrv, $timename ) {
		$timeStr = '';
		if ( $time[$timeabrv] > 0 ) {
			$timeStr = wfMessage( "quizgame-time-{$timename}", $time[$timeabrv] )->parse();
		}
		if ( $timeStr ) {
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
		if ( $timeStr < 2 ) {
			$timeStr .= $timeStrH;
			$timeStr .= $timeStrM;
			if ( !$timeStr ) {
				$timeStr .= $timeStrS;
			}
		}
		if ( !$timeStr ) {
			$timeStr = wfMessage( 'quizgame-time-seconds', 1 )->parse();
		}
		return $timeStr;
	}

}
