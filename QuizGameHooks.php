<?php
/**
 * Hooked functions used by the QuizGame extension.
 * All class methods are public and static.
 *
 * @file
 * @ingroup Extensions
 */
class QuizGameHooks {

	/**
	 * Adds an "edit" tab to Special:QuizGameHome.
	 *
	 * @param $skin Skin
	 * @param $content_actions Array
	 * @return Boolean: true
	 */
	public static function addQuizContentActions( &$skinTemplate, &$links ) {
		global $wgUser, $wgRequest, $wgQuizID;

		// Add edit tab to content actions for quiz admins
		if(
			$wgQuizID > 0 &&
			$wgRequest->getVal( 'questionGameAction' ) != 'createForm' &&
			$wgUser->isAllowed( 'quizadmin' )
		)
		{
			$quiz = SpecialPage::getTitleFor( 'QuizGameHome' );
			$selected = false;
			if ( $wgRequest->getVal( 'questionGameAction' ) == 'editItem' ) {
				$selected = 'selected';
			}
			$links['views']['edit'] = array(
				'class' => $selected,
				'text' => wfMessage( 'edit' )->plain(),
				'href' => $quiz->getFullURL( 'questionGameAction=editItem&quizGameId=' . $wgQuizID ), // @bug 2457, 2510
			);
		}

		// If editing, make special page go back to quiz question
		if( $wgRequest->getVal( 'questionGameAction' ) == 'editItem' ) {
			$quiz = SpecialPage::getTitleFor( 'QuizGameHome' );
			$links['views'][$skinTemplate->getTitle()->getNamespaceKey()] = array(
				'class' => 'selected',
				'text' => wfMessage( 'nstab-special' )->plain(),
				'href' => $quiz->getFullURL( 'questionGameAction=renderPermalink&permalinkID=' . $wgQuizID ),
			);
		}

		return true;
	}

	/**
	 * Expose $wgUserStatsPointValues['quiz_points'] in the page output as a JS
	 * global for QuizGame.js.
	 * I need to rethink this one day...
	 *
	 * @param $vars Array: array of pre-existing JS globals
	 * @return Boolean: true
	 */
	public static function addJSGlobals( $vars ) {
		global $wgUserStatsPointValues;
		$vars['__quiz_js_points_value__'] = ( isset( $wgUserStatsPointValues['quiz_points'] ) ? $wgUserStatsPointValues['quiz_points'] : 0 );
		return true;
	}

	/**
	 * Creates the necessary database tables when the user runs
	 * maintenance/update.php.
	 *
	 * @param $updater DatabaseUpdater
	 * @return Boolean: true
	 */
	public static function addTables( $updater ) {
		$dir = dirname( __FILE__ );
		$file = "$dir/quizgame.sql";
		$updater->addExtensionUpdate( array( 'addTable', 'quizgame_questions', $file, true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'quizgame_answers', $file, true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'quizgame_choice', $file, true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'quizgame_user_view', $file, true ) );
		return true;
	}

	/**
	 * For integration with the Renameuser extension.
	 *
	 * @param $renameUserSQL
	 * @return Boolean: true
	 */
	public static function onUserRename( $renameUserSQL ) {
		$renameUserSQL->tables['quizgame_questions'] = array(
			'q_user_name', 'q_user_id'
		);
		$renameUserSQL->tables['quizgame_answers'] = array(
			'a_user_name', 'a_user_id'
		);
		// quizgame_choice table has no information related to the user
		$renameUserSQL->tables['quizgame_user_view'] = array(
			'uv_user_name', 'uv_user_id'
		);
		return true;
	}

	/**
	 * If quiz logging is enabled, set up the new log type.
	 */
	public static function registerExtension() {
		global $wgQuizLogs, $wgLogTypes, $wgLogActionsHandlers;
		if ( $wgQuizLogs ) {
			$wgLogTypes[] = 'quiz';
			// default log formatter doesn't support wikilinks (?!?) so we have to have
			// our own formatter here :-(
			$wgLogActionsHandlers['quiz/*'] = 'QuizGameLogFormatter';
		}
	}
}