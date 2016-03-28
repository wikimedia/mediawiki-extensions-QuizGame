<?php
/**
 * QuizGame extension - interactive question game that uses AJAX
 *
 * @file
 * @ingroup Extensions
 * @author Aaron Wright <aaron.wright@gmail.com>
 * @author Ashish Datta <ashish@setfive.com>
 * @author David Pean <david.pean@gmail.com>
 * @author Jack Phoenix <jack@countervandalism.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:QuizGame Documentation
 */

// Extension credits that will show up on Special:Version
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'QuizGame',
	'version' => '3.3',
	'author' => array( 'Aaron Wright', 'Ashish Datta', 'David Pean', 'Jack Phoenix' ),
	'descriptionmsg' => 'quizgame-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:QuizGame',
);

// ResourceLoader support for MediaWiki 1.17+
$wgResourceModules['ext.quizGame'] = array(
	'scripts' => 'js/QuizGame.js',
	'messages' => array(
		'quizgame-create-error-numanswers', 'quizgame-create-error-noquestion',
		'quizgame-create-error-numcorrect', 'quizgame-js-reloading',
		'quizgame-js-timesup', 'quizgame-js-points',
		'quizgame-pause-continue', 'quizgame-pause-view-leaderboard',
		'quizgame-pause-create-question', 'quizgame-main-page-button',
		'quizgame-js-loading', 'quizgame-lightbox-pause-quiz',
		'quizgame-lightbox-breakdown', 'quizgame-lightbox-breakdown-percent',
		'quizgame-lightbox-correct', 'quizgame-lightbox-incorrect',
		'quizgame-lightbox-correct-points', 'quizgame-lightbox-incorrect-correct',
		'quizgame-create-edit-picture', 'quizgame-edit',
		'quizgame-ajax-nonnumeric-answer', 'quizgame-ajax-already-answered',
		'quizgame-ajax-invalid-id'
	),
	'dependencies' => array(
		'ext.socialprofile.flash',
		'ext.socialprofile.LightBox'
	),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'QuizGame',
	'position' => 'bottom'
);

$wgResourceModules['ext.quizGame.css'] = array(
	'styles' => 'questiongame.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'QuizGame',
	'position' => 'top'
);

// Set up the new special pages
$wgMessagesDirs['QuizGame'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['QuizGameAlias'] = __DIR__ . '/QuestionGame.alias.php';

$wgAutoloadClasses['QuizGameLogFormatter'] = __DIR__ . '/QuizGameLogFormatter.php';
$wgAutoloadClasses['QuizGameHome'] = __DIR__ . '/QuestionGameHome.body.php';
$wgAutoloadClasses['SpecialQuestionGameUpload'] = __DIR__ . '/QuestionGameUpload.php';
$wgAutoloadClasses['QuestionGameUploadForm'] = __DIR__ . '/QuestionGameUpload.php';
$wgAutoloadClasses['QuizLeaderboard'] = __DIR__ . '/QuizLeaderboard.php';
$wgAutoloadClasses['QuizRecalcStats'] = __DIR__ . '/RecalculateStats.php';
$wgAutoloadClasses['ViewQuizzes'] = __DIR__ . '/ViewQuizzes.php';

$wgSpecialPages['QuizGameHome'] = 'QuizGameHome';
$wgSpecialPages['QuestionGameUpload'] = 'SpecialQuestionGameUpload';
$wgSpecialPages['QuizLeaderboard'] = 'QuizLeaderboard';
$wgSpecialPages['QuizRecalcStats'] = 'QuizRecalcStats';
$wgSpecialPages['ViewQuizzes'] = 'ViewQuizzes';

// API modules
$wgAutoloadClasses['ApiQuizGame'] = __DIR__ . '/api/ApiQuizGame.php';
$wgAPIModules['quizgame'] = 'ApiQuizGame';

$wgAutoloadClasses['ApiQuizGameVote'] = __DIR__ . '/api/ApiQuizGameVote.php';
$wgAPIModules['quizgamevote'] = 'ApiQuizGameVote';

// New user right for protecting/deleting/unflagging questions
$wgAvailableRights[] = 'quizadmin';
$wgGroupPermissions['sysop']['quizadmin'] = true;
$wgGroupPermissions['staff']['quizadmin'] = true;

// Should we log quiz creations, deletions, flaggings and unflaggings?
$wgQuizLogs = true;

// If so, set up the new log
// Note: while this may look like as if overriding $wgQuizLogs is impossible,
// this works just as intended; I've tested this.
if( $wgQuizLogs ) {
	$wgLogTypes[] = 'quiz';
	// default log formatter doesn't support wikilinks (?!?) so we have to have
	// our own formatter here :-(
	$wgLogActionsHandlers['quiz/*'] = 'QuizGameLogFormatter';
}

// For example: 'edits' => 5 if you want to require users to have at least 5
// edits before they can create new quiz games.
$wgCreateQuizThresholds = array();

// Hooked functions
$wgAutoloadClasses['QuizGameHooks'] = __DIR__ . '/QuizGameHooks.php';

$wgHooks['SkinTemplateNavigation::SpecialPage'][] = 'QuizGameHooks::addQuizContentActions';
$wgHooks['MakeGlobalVariablesScript'][] = 'QuizGameHooks::addJSGlobals';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'QuizGameHooks::addTables';
$wgHooks['RenameUserSQL'][] = 'QuizGameHooks::onUserRename';
