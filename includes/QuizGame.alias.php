<?php
/**
 * Aliases for the QuizGame extension.
 *
 * @file
 * @ingroup Extensions
 */

$specialPageAliases = array();

/** English */
$specialPageAliases['en'] = array(
	'QuizGameHome' => array( 'QuizGameHome' ),
	'QuestionGameUpload' => array( 'QuestionGameUpload' ),
	'QuizLeaderboard' => array( 'QuizLeaderboard' ),
	'QuizRecalcStats' => array( 'QuizRecalcStats' ),
	'ViewQuizzes' => array( 'ViewQuizzes' ),
);

/** Finnish (Suomi) */
$specialPageAliases['fi'] = array(
	'QuizGameHome' => array( 'Kysymyspelin kotisivu' ),
	'QuizLeaderboard' => array( 'Kysymyspelin tilastot' ), # "Quiz Game Statistics", since I couldn't find a translation for "leaderboard"
	'ViewQuizzes' => array( 'Katso kysymyksiä' ),
);
