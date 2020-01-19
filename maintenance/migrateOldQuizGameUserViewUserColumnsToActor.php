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
class MigrateOldQuizGameUserViewUserColumnsToActor extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();
		// @codingStandardsIgnoreLine
		$this->addDescription( 'Migrates data from old _user_id/_user_name columns in the quizgame_user_view table to the new actor column.' );
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
	protected function updateSkippedMessage() {
		return 'quizgame_user_view has already been migrated to use the actor column.';
	}

	/**
	 * Do the actual work.
	 *
	 * @return bool True to log the update as done
	 */
	protected function doDBUpdates() {
		$dbw = $this->getDB( DB_MASTER );

		if ( !$dbw->fieldExists( 'quizgame_user_view', 'uv_user_id', __METHOD__ ) ) {
			return true;
		}

		$dbw->query(
			// @codingStandardsIgnoreLine
			"UPDATE {$dbw->tableName( 'quizgame_user_view' )} SET uv_actor=(SELECT actor_id FROM {$dbw->tableName( 'actor' )} WHERE actor_name=uv_user_name AND actor_user=uv_user_id)",
			__METHOD__
		);

		return true;
	}
}

$maintClass = MigrateOldQuizGameUserViewUserColumnsToActor::class;
require_once RUN_MAINTENANCE_IF_MAIN;
