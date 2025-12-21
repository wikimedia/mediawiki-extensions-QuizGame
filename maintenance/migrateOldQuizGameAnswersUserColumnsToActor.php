<?php
/**
 * @file
 * @ingroup Maintenance
 */
$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Run automatically with update.php
 *
 * @since January 2020
 */
class MigrateOldQuizGameAnswersUserColumnsToActor extends MediaWiki\Maintenance\LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Migrates data from old _user_id/_user_name columns in the quizgame_answers table ' .
			' to the new actor column.' );
	}

	/**
	 * Get the update key name to go in the update log table
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return __CLASS__;
	}

	/**
	 * Message to show that the update was done already and was just skipped
	 *
	 * @return string
	 */
	public function updateSkippedMessage() {
		return 'quizgame_answers has already been migrated to use the actor column.';
	}

	/**
	 * Do the actual work.
	 *
	 * @return bool True to log the update as done
	 */
	protected function doDBUpdates() {
		$dbw = $this->getDB( DB_PRIMARY );

		if ( !$dbw->fieldExists( 'quizgame_answers', 'a_user_id', __METHOD__ ) ) {
			return true;
		}

		$dbw->query(
			"UPDATE {$dbw->tableName( 'quizgame_answers' )} " .
			"SET a_actor=(SELECT actor_id FROM {$dbw->tableName( 'actor' )} WHERE actor_name=a_user_name AND actor_user=a_user_id)",
			__METHOD__
		);

		return true;
	}
}

$maintClass = MigrateOldQuizGameAnswersUserColumnsToActor::class;
require_once RUN_MAINTENANCE_IF_MAIN;
