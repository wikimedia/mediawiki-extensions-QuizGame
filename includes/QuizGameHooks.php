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
	 * @param SkinTemplate &$skinTemplate
	 * @param array &$links
	 */
	public static function onSkinTemplateNavigationUniversal( &$skinTemplate, &$links ) {
		global $wgQuizID;

		$user = $skinTemplate->getUser();
		$request = $skinTemplate->getRequest();
		$title = $skinTemplate->getTitle();

		$action = $request->getVal( 'questionGameAction' );
		$quiz = SpecialPage::getTitleFor( 'QuizGameHome' );

		// Add edit tab to content actions for quiz admins
		if (
			$wgQuizID > 0 &&
			$title->isSpecial( 'QuizGameHome' ) &&
			$action != 'createForm' &&
			$user->isAllowed( 'quizadmin' )
		) {
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
			$links['views'][$title->getNamespaceKey()] = [
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

		$db = $updater->getDB();
		$isPostgreSQL = ( $db->getType() === 'postgres' );
		if ( $isPostgreSQL ) {
			$sqlDirectory .= 'postgres/';
		}

		$updater->addExtensionTable( 'quizgame_questions', $sqlDirectory . 'quizgame_questions.sql' );
		$updater->addExtensionTable( 'quizgame_answers', $sqlDirectory . 'quizgame_answers.sql' );
		$updater->addExtensionTable( 'quizgame_choice', $sqlDirectory . 'quizgame_choice.sql' );
		$updater->addExtensionTable( 'quizgame_user_view', $sqlDirectory . 'quizgame_user_view.sql' );

		if ( $isPostgreSQL ) {
			// Don't run the stuff below for Postgres, they're all MySQL/MariaDB-specific
			return;
		}

		$updater->modifyExtensionField( 'quizgame_choice', 'choice_answer_count',
			$sqlDirectory . "patches/patch-add-default-choice_answer_count.sql" );

		// Actor support (T227345)
		if ( !$db->fieldExists( 'quizgame_answers', 'a_actor', __METHOD__ ) ) {
			// 1) add new actor column
			$updater->addExtensionField( 'quizgame_answers', 'a_actor', $sqlDirectory . 'patches/actor/add-a_actor.sql' );

			// 2) add the corresponding index
			$updater->addExtensionIndex( 'quizgame_answers', 'a_actor', $sqlDirectory . 'patches/actor/add-a_actor_index.sql' );

			// 3) populate the columns with correct values
			// PITFALL WARNING! Do NOT change this to $updater->runMaintenance,
			// THEY ARE NOT THE SAME THING and this MUST be using addExtensionUpdate
			// instead for the code to work as desired!
			// HT Skizzerz
			$updater->addExtensionUpdate( [
				'runMaintenance',
				'MigrateOldQuizGameAnswersUserColumnsToActor',
				'../maintenance/migrateOldQuizGameAnswersUserColumnsToActor.php'
			] );

			// 4) drop old columns + indexes
			$updater->dropExtensionField( 'quizgame_answers', 'a_user_name', $sqlDirectory . 'patches/actor/drop-a_user_name.sql' );
			$updater->dropExtensionField( 'quizgame_answers', 'a_user_id', $sqlDirectory . 'patches/actor/drop-a_user_id.sql' );
			$updater->dropExtensionIndex( 'quizgame_answers', 'a_user_id', $sqlDirectory . 'patches/actor/drop-a_user_id_index.sql' );
		}

		if ( !$db->fieldExists( 'quizgame_questions', 'q_actor', __METHOD__ ) ) {
			// 1) add new actor column
			$updater->addExtensionField( 'quizgame_questions', 'q_actor', $sqlDirectory . 'patches/actor/add-q_actor.sql' );

			// 2) add the corresponding index
			$updater->addExtensionIndex( 'quizgame_questions', 'q_actor', $sqlDirectory . 'patches/actor/add-q_actor_index.sql' );

			// 3) populate the columns with correct values
			// PITFALL WARNING! Do NOT change this to $updater->runMaintenance,
			// THEY ARE NOT THE SAME THING and this MUST be using addExtensionUpdate
			// instead for the code to work as desired!
			// HT Skizzerz
			$updater->addExtensionUpdate( [
				'runMaintenance',
				'MigrateOldQuizGameQuestionsUserColumnsToActor',
				'../maintenance/migrateOldQuizGameQuestionsUserColumnsToActor.php'
			] );

			// 4) drop old columns + indexes
			$updater->dropExtensionField( 'quizgame_questions', 'q_user_name', $sqlDirectory . 'patches/actor/drop-q_user_name.sql' );
			$updater->dropExtensionField( 'quizgame_questions', 'q_user_id', $sqlDirectory . 'patches/actor/drop-q_user_id.sql' );
			$updater->dropExtensionIndex( 'quizgame_questions', 'q_user_id', $sqlDirectory . 'patches/actor/drop-q_user_id_index.sql' );
		}

		if ( !$db->fieldExists( 'quizgame_user_view', 'uv_actor', __METHOD__ ) ) {
			// 1) add new actor column
			$updater->addExtensionField( 'quizgame_user_view', 'uv_actor', $sqlDirectory . 'patches/actor/add-uv_actor.sql' );

			// 2) add the corresponding index
			$updater->addExtensionIndex( 'quizgame_user_view', 'uv_actor', $sqlDirectory . 'patches/actor/add-uv_actor_index.sql' );

			// 3) populate the columns with correct values
			// PITFALL WARNING! Do NOT change this to $updater->runMaintenance,
			// THEY ARE NOT THE SAME THING and this MUST be using addExtensionUpdate
			// instead for the code to work as desired!
			// HT Skizzerz
			$updater->addExtensionUpdate( [
				'runMaintenance',
				'MigrateOldQuizGameUserViewUserColumnsToActor',
				'../maintenance/migrateOldQuizGameUserViewUserColumnsToActor.php'
			] );

			// 4) drop old columns + indexes
			$updater->dropExtensionField( 'quizgame_user_view', 'uv_user_name', $sqlDirectory . 'patches/actor/drop-uv_user_name.sql' );
			$updater->dropExtensionField( 'quizgame_user_view', 'uv_user_id', $sqlDirectory . 'patches/actor/drop-uv_user_id.sql' );
			$updater->dropExtensionIndex( 'quizgame_user_view', 'uv_user_id', $sqlDirectory . 'patches/actor/drop-uv_user_id_index.sql' );
		}
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
