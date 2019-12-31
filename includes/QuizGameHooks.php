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
	 * @param SkinTemplate $skinTemplate
	 * @param array $links
	 */
	public static function onSkinTemplateNavigationSpecialPage( &$skinTemplate, &$links ) {
		global $wgQuizID;

		$user = $skinTemplate->getUser();
		$request = $skinTemplate->getRequest();

		$action = $request->getVal( 'questionGameAction' );
		$quiz = SpecialPage::getTitleFor( 'QuizGameHome' );

		// Add edit tab to content actions for quiz admins
		if (
			$wgQuizID > 0 &&
			$action != 'createForm' &&
			$user->isAllowed( 'quizadmin' )
		)
		{
			$selected = false;
			if ( $action == 'editItem' ) {
				$selected = 'selected';
			}
			$links['views']['edit'] = [
				'class' => $selected,
				'text' => $skinTemplate->msg( 'edit' )->plain(),
				// @see https://phabricator.wikimedia.org/T4457
				// @see https://phabricator.wikimedia.org/T4510
				'href' => $quiz->getFullURL( [
					'questionGameAction' => 'editItem',
					'quizGameId' => $wgQuizID
				] ),
			];
		}

		// If editing, make special page go back to quiz question
		if ( $action == 'editItem' ) {
			$links['views'][$skinTemplate->getTitle()->getNamespaceKey()] = [
				'class' => 'selected',
				'text' => $skinTemplate->msg( 'nstab-special' )->plain(),
				'href' => $quiz->getFullURL( [
					'questionGameAction' => 'renderPermalink',
					'permalinkID' => $wgQuizID
				] )
			];
		}
	}

	/**
	 * Expose $wgUserStatsPointValues['quiz_points'] in the page output as a JS
	 * global for QuizGame.js.
	 * I need to rethink this one day...
	 *
	 * @param array $vars Array of pre-existing JS globals
	 */
	public static function onMakeGlobalVariablesScript( $vars ) {
		global $wgUserStatsPointValues;
		$vars['__quiz_js_points_value__'] = ( $wgUserStatsPointValues['quiz_points'] ?? 0 );
	}

	/**
	 * Creates the necessary database tables when the user runs
	 * maintenance/update.php.
	 *
	 * @param DatabaseUpdater $updater
	 */
	public static function onLoadExtensionSchemaUpdates( $updater ) {
		$sqlDirectory = __DIR__ . '/../sql/';
		$updater->addExtensionTable( 'quizgame_questions', $sqlDirectory . 'quizgame_questions.sql' );
		$updater->addExtensionTable( 'quizgame_answers', $sqlDirectory . 'quizgame_answers.sql' );
		$updater->addExtensionTable( 'quizgame_choice', $sqlDirectory . 'quizgame_choice.sql' );
		$updater->addExtensionTable( 'quizgame_user_view', $sqlDirectory . 'quizgame_user_view.sql' );

		$updater->modifyExtensionField( 'quizgame_choice', 'choice_answer_count',
 			$sqlDirectory . "patches/patch-add-default-choice_answer_count.sql" );
	}

	/**
	 * For integration with the Renameuser extension.
	 *
	 * @param RenameuserSQL $renameUserSQL
	 */
	public static function onRenameUserSQL( $renameUserSQL ) {
		$renameUserSQL->tables['quizgame_questions'] = [
			'q_user_name', 'q_user_id'
		];
		$renameUserSQL->tables['quizgame_answers'] = [
			'a_user_name', 'a_user_id'
		];
		// quizgame_choice table has no information related to the user
		$renameUserSQL->tables['quizgame_user_view'] = [
			'uv_user_name', 'uv_user_id'
		];
	}

	/**
	 * If quiz logging is enabled, set up the new log type.
	 */
	public static function onRegisterExtension() {
		global $wgQuizLogs, $wgLogTypes, $wgLogActionsHandlers;
		if ( $wgQuizLogs ) {
			$wgLogTypes[] = 'quiz';
			$wgLogActionsHandlers['quiz/*'] = 'WikitextLogFormatter';
		}
	}
}
