<?php
/**
 * Aliases for the QuizGame extension.
 *
 * @file
 * @ingroup Extensions
 */

$specialPageAliases = [];

/** English */
$specialPageAliases['en'] = [
	'QuizGameHome' => [ 'QuizGameHome' ],
	'QuestionGameUpload' => [ 'QuestionGameUpload' ],
	'QuizLeaderboard' => [ 'QuizLeaderboard' ],
	'QuizRecalcStats' => [ 'QuizRecalcStats' ],
	'ViewQuizzes' => [ 'ViewQuizzes' ],
];

/** Finnish (suomi) */
$specialPageAliases['fi'] = [
	'QuizGameHome' => [ 'Kysymyspelin kotisivu' ],
	'QuizLeaderboard' => [ 'Kysymyspelin tilastot' ], # "Quiz Game Statistics", since I couldn't find a translation for "leaderboard"
	'ViewQuizzes' => [ 'Katso kysymyksiä' ],
];
