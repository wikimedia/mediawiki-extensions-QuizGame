<?php
/**
 * Internationalization file for QuizGame extension.
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

/** English
 * @author Aaron Wright <aaron.wright@gmail.com>
 * @author David Pean <david.pean@gmail.com>
 */
$messages['en'] = array(
	'quizgame-edit' => 'Edit',
	'quizgame-delete' => 'Delete',
	'quizgame-protect' => 'Protect',
	'quizgame-reinstate' => 'Re-instate',
	'quizgame-unprotect' => 'Unprotect',
	'quizgame-admin-panel-title' => 'Admin Panel',
	'quizgame-admin-back' => '&lt; Back to Never Ending Quiz',
	'quizgame-admin-flagged' => 'Flagged Questions',
	'quizgame-admin-protected' => 'Protected Questions',
	'quizgame-admin-permission' => 'You don\'t have permission to edit',
	'quizgame-edit-title' => 'Editing - $1',
	'quizgame-edit-picture-link' => 'Edit Picture',
	'quizgame-flagged-reason' => 'Reason',
	'quizgame-submitted-by' => 'Submitted By',
	'quizgame-question' => 'Question',
	'quizgame-answers' => 'Answers',
	'quizgame-picture' => 'Picture',
	'quizgame-correct-answer-checked' => 'The correct answer is checked.',
	'quizgame-save-page-button' => 'Save Page',
	'quizgame-cancel-button' => 'Cancel',
	'quizgame-login-title' => 'You Must Be Logged in to Create a Quiz',
	'quizgame-login-text' => 'You need to log in, to play the create a quiz!',
	'quizgame-main-page-button' => 'Main Page',
	'quizgame-login-button' => 'Login',
	'quizgame-submit' => 'Submit',
	'quizgame-nomore-questions' => 'No More Questions!',
	'quizgame-ohnoes' => 'There are no more quiz games, please click below to create one!',
	'quizgame-create-button' => 'Create A Quiz!',
	'quizgame-unavailable' => 'Sorry, this question is unavailable!<br /><br />[[Special:QuizGameHome|Click here to try some other questions]]',
	'quizgame-title' => 'Quiz Game!',
	'quizgame-intro' => 'Welcome to the Quiz Game. Here are some rules.',
	'quizgame-introlink' => 'Click here to start',
	// Special:QuizLeaderboard
	'quizgame-leaderboard-scoretitle' => 'Your Score',
	'quizgame-leaderboard-quizpoints' => 'Quiz Points',
	'quizgame-leaderboard-correct' => 'Correct Answers',
	'quizgame-leaderboard-answered' => 'Questions Answered',
	'quizgame-leaderboard-pctcorrect' => 'Percent Correct',
	'quizgame-leaderboard-rank' => 'Overall Rank',
	'quizgame-leaderboard-link' => 'leaderboard',
	'quizgame-leaderboard-most-correct' => 'Quiz Leaderboard - Most Correct Answers',
	'quizgame-leaderboard-highest-percent' => 'Quiz Leaderboard - Highest Percent Correct (min 50 questions)',
	'quizgame-leaderboard-most-points' => 'Quiz Leaderboard - Most Points',
	// Menu in Special:QuizLeaderboard
	'quizgame-leaderboard-order-menu' => 'Order',
	'quizgame-leaderboard-menu-points' => 'Points',
	'quizgame-leaderboard-menu-correct' => 'Total Correct',
	'quizgame-leaderboard-menu-pct' => 'Highest Percentage',
	'quizgame-leaderboard-desc-pct' => '$1%',
	'quizgame-leaderboard-desc-correct' => '$1 correct',
	'quizgame-leaderboard-desc-points' => '$1 points',
	'quizgame-login-or-create-to-climb' => '[[Special:UserLogin|Login]] or [[Special:UserLogin/signup|create an account]] to compete and climb the leaderboard!',
	'quizgame-pct-answered-correct' => '$1% Got This Question Right',
	'quizgame-answered-correctly' => 'You answered correctly',
	'quizgame-skipped' => 'You skipped this question',
	'quizgame-answered-incorrectly' => 'You answered Incorrectly',
	'quizgame-your-answer' => 'Your Answer',
	'quizgame-correct-answer' => 'Correct Answer',
	'quizgame-js-loading' => 'Loading...',
	'quizgame-js-reloading' => 'Re-loading...',
	'quizgame-js-timesup' => "Time's up! You won't earn any points, but try to answer it anyway!",
	'quizgame-js-points' => '$1 points',
	'quizgame-js-seconds' => 'seconds',
	'quizgame-lightbox-pause-quiz' => 'Pause Quiz',
	'quizgame-lightbox-breakdown' => 'View Breakdown For This Question',
	'quizgame-lightbox-breakdown-percent' => '$1% of People who answered this question got it correct',
	'quizgame-lightbox-correct' => 'Nice job, you answered the question right!',
	'quizgame-lightbox-correct-points' => 'You earned $1 points',
	'quizgame-lightbox-incorrect' => 'Sorry, you answered the question wrong!',
	'quizgame-lightbox-incorrect-correct' => 'The correct answer is: $1',
	'quizgame-skip' => 'Skip',
	'quizgame-next' => 'Next Quiz Question',
	'quizgame-times-answered' => '(answered {{PLURAL:$1|once|$1 times}})',
	'quizgame-chose-correct' => 'You chose the correct answer for the previous question! ($1 points)',
	'quizgame-chose-incorrect' => 'You chose the incorrect answer for the previous question! (0 points)',
	'quizgame-flag' => 'Flag',
	'quizgame-prev' => 'prev',
	'quizgame-nav-next' => 'next',
	'quizgame-answered' => 'Answered {{PLURAL:$1|one time|$1 times}}',
	'quizgame-newest' => 'Newest',
	'quizgame-popular' => 'Popular',
	'quizgame-playneverending' => 'Play the Never Ending Quiz',
	'quizgame-viewquizzes-create' => 'Create a Quiz Question',
	'quizgame-viewquizzes-title' => 'View Quiz Questions',
	'quizgame-viewquizzes-title-by-user' => 'View $1\'s Quiz Questions',
	'quizgame-permalink' => 'Permalink',
	'quizgame-create-error-numanswers' => 'You must have at least two answer choices',
	'quizgame-create-error-noquestion' => 'You must write a question',
	'quizgame-create-error-numcorrect' => 'You must have one correct answer',
	'quizgame-create-title' => 'Create a Quiz Question',
	'quizgame-create-message' => 'To add a quiz question to the never ending quiz, write a question and add some answer choices.',
	'quizgame-play-quiz' => 'Play the Never Ending Quiz',
	'quizgame-create-write-question' => 'Write a Question',
	'quizgame-create-write-answers' => 'Write the Answers',
	'quizgame-create-check-correct' => 'Please check the correct answer.',
	'quizgame-create-add-picture' => 'Add a Picture',
	'quizgame-create-edit-picture' => 'Edit Picture',
	'quizgame-create-play' => 'Create and Play!',
	'quizgame-pause-continue' => 'Continue',
	'quizgame-pause-view-leaderboard' => 'View Leaderboard',
	'quizgame-pause-create-question' => 'Create Question',
	'quizgame-last-question' => 'Last Question',
	'quizgame-create-threshold-title' => 'Create Quiz Question',
	'quizgame-create-threshold-reason' => 'Sorry, you cannot create a quiz question until you have at least $1',
	'quizgame-points' => '$1 pts',
	'quizgame-time-ago' => '$1 ago',
	'quizgame-time-days' => '{{PLURAL:$1|one day|$1 days}}',
	'quizgame-time-hours' => '{{PLURAL:$1|one hour|$1 hours}}',
	'quizgame-time-minutes' => '{{PLURAL:$1|one minute|$1 minutes}}',
	'quizgame-time-seconds' => '{{PLURAL:$1|one second|$1 seconds}}',
	// Messages from the AJAX file (these are shown to the end-user)
	'quizgame-ajax-invalid-key' => 'You need a valid key to do that.',
	'quizgame-ajax-unprotected' => 'The question has been un-protected.',
	'quizgame-ajax-protected' => 'The question has been protected.',
	'quizgame-ajax-unflagged' => 'The question has been re-instated.',
	'quizgame-ajax-flagged' => 'The question has been flagged.',
	'quizgame-ajax-deleted' => 'Delete successful!',
	'quizgame-ajax-invalid-option' => 'Invalid AJAX option.',
	'quizgame-ajax-nonnumeric-answer' => 'Answer choice is not numeric.',
	'quizgame-ajax-already-answered' => 'You already answered this question.',
	'quizgame-ajax-invalid-id' => 'There is no question by that ID.',
	// Logs
	'log-name-quiz' => 'Quiz Question log',
	'log-description-quiz' => 'This is a log of quiz question actions.',
	'logentry-quiz-create' => '$1 created [[Special:QuizGameHome/$4|a new quiz question]]',
	'logentry-quiz-delete' => '$1 deleted [[Special:QuizGameHome/$4|quiz question #$4]]',
	'logentry-quiz-flag' => '$1 flagged [[Special:QuizGameHome/$4|quiz question #$4]]',
	'logentry-quiz-protect' => '$1 protected [[Special:QuizGameHome/$4|quiz question #$4]]',
	'logentry-quiz-unflag' => '$1 unflagged [[Special:QuizGameHome/$4|quiz question #$4]]',
	'logentry-quiz-unprotect' => '$1 unprotected [[Special:QuizGameHome/$4|quiz question #$4]]',
	// Category where all images uploaded via Special:QuestionGameUpload will
	// be placed into
	'quizgame-images-category' => 'QuizGame images',
	// For Special:ListGroupRights
	'right-quizadmin' => 'Administrate question games',
);

/** Message documentation */
$messages['qqq'] = array(
	'quizgame-edit' => 'Link title',
	'quizgame-delete' => 'Link title',
	'quizgame-protect' => 'Link title',
	'quizgame-reinstate' => 'Link title; reinstating returns a flagged quiz into the pool of available quizzes, allowing users to play it again',
	'quizgame-unprotect' => 'Link title',
	'quizgame-admin-panel-title' => 'Link title',
	'quizgame-admin-back' => 'Link title on the admin panel; clicking on this link takes you back to the question game from the administrative functions',
	'quizgame-admin-flagged' => 'Header title on the admin panel of QuizGame',
	'quizgame-admin-protected' => 'Header title on the admin panel of QuizGame',
	'quizgame-admin-permission' => 'Error message shown if a user without the "quizadmin" user right tries to access the quiz editing form',
	'quizgame-edit-title' => 'Page title shown when editing a quiz; $1 is the title of the quiz',
	'quizgame-edit-picture-link' => 'Link title; the link is rendered via JavaScript. This is shown on Special:QuizGameHome when editing a pre-existing question that has an image.',
	'quizgame-flagged-reason' => 'This message is followed by either the reason why someone flagged a question (on the admin panel, which is viewable only by users with the "quizgame" user right) or an input box where the user can enter a reason why they\'re flagging a question (on Special:QuizGameHome, shown for all users)',
	'quizgame-submitted-by' => 'Title of a box (on Special:QuizGameHome, when viewing a question) which contains the quiz\'s author\'s avatar and some statistics about the author',
	'quizgame-question' => 'Headline on Special:QuizGameHome',
	'quizgame-answers' => 'Headline on Special:QuizGameHome, followed by the choice ballots (<code>&lt;input type="radio"&;gt;</code>)',
	'quizgame-picture' => 'Headline on Special:QuizGameHome',
	'quizgame-correct-answer-checked' => 'Informational message shown when editing a question (as opposed to creating a new question)',
	'quizgame-save-page-button' => 'Button text',
	'quizgame-cancel-button' => 'Button text',
	'quizgame-login-title' => 'Title of the error page when an anonymous user tries to create a quiz',
	'quizgame-login-text' => 'Error message shown to an anonymous user if they try to create a quiz',
	'quizgame-main-page-button' => 'Button text',
	'quizgame-login-button' => 'Button text',
	'quizgame-submit' => 'Button text',
	'quizgame-nomore-questions' => 'Page title when the user has finished answering each and every available quiz. See also the related messages, [[MediaWiki:Quizgame-ohnoes]] and [[MediaWiki:Quizgame-create-button]]',
	'quizgame-ohnoes' => 'Message shown to the user when they\'ve finished answering each and every available quiz. See also the related messages, [[MediaWiki:Quizgame-nomore-questions]] and [[MediaWiki:Quizgame-create-button]].',
	'quizgame-create-button' => 'Button text',
	'quizgame-unavailable' => 'Error message shown when trying to access a quiz that isn\'t available for some reason (via a permalink, i.e. Special:QuizGameHome/36)',
	'quizgame-title' => 'Page title of Special:QuizGameHome, both when starting to play the game as well as when trying to access a nonexistent question',
	'quizgame-intro' => 'Shown on Special:QuizGameHome, when about to start playing the QuizGame. Site administrators can insert site-specific rules into this message. This message is followed by [[MediaWiki:Quizgame-introlink]], which is a link that takes you to a question',
	'quizgame-introlink' => 'Link title; this link that takes you to a question. This is preceded by [[MediaWiki:Quizgame-intro]].',
	'quizgame-leaderboard-scoretitle' => 'Title of the current user\'s personal scorecard on Special:QuizLeaderboard',
	'quizgame-leaderboard-quizpoints' => 'Shown on the current user\'s personal scorecard on Special:QuizLeaderboard; this is followed by (on its own line) the amount of quiz points the user has earned',
	'quizgame-leaderboard-correct' => 'Shown on the current user\'s personal scorecard on Special:QuizLeaderboard; this is followed by (on its own line) the amount of correct answers the current user has',
	'quizgame-leaderboard-answered' => 'Shown on the current user\'s personal scorecard on Special:QuizLeaderboard; this is followed by (on its own line) the total amount of questions the user has answered',
	'quizgame-leaderboard-pctcorrect' => 'Shown on the current user\'s personal scorecard on Special:QuizLeaderboard; this is followed by (on its own line) the percentage of correct answers the user has',
	'quizgame-leaderboard-rank' => 'Shown on the current user\'s personal scorecard on Special:QuizLeaderboard; this is followed by (on its own line) the user\'s overall rank in the quiz leaderboard (when compared to other users of the site)',
	'quizgame-leaderboard-link' => 'Link title of a link taking the user to Special:QuizLeaderboard, the statistics page for questions',
	'quizgame-leaderboard-most-correct' => 'Page title of Special:QuizLeaderboard when viewing the questions that have had the most correct answers by users',
	'quizgame-leaderboard-highest-percent' => 'Page title of Special:QuizLeaderboard when viewing the questions that have the highest percentage of correct answers',
	'quizgame-leaderboard-most-points' => 'Page title of Special:QuizLeaderboard when viewing the list of users who have earned the most points from QuizGame',
	'quizgame-leaderboard-order-menu' => 'Title of a navigation menu on Special:QuizLeaderboard',
	'quizgame-leaderboard-menu-points' => 'Navigation menu link title on Special:QuizLeaderboard',
	'quizgame-leaderboard-menu-correct' => 'Navigation menu link title on Special:QuizLeaderboard',
	'quizgame-leaderboard-menu-pct' => 'Navigation menu link title on Special:QuizLeaderboard',
	'quizgame-leaderboard-desc-pct' => '$1 is a number',
	'quizgame-leaderboard-desc-correct' => '$1 is a number',
	'quizgame-leaderboard-desc-points' => '$1 is a number',
	'quizgame-login-or-create-to-climb' => 'Message shown to anonymous users viewing Special:QuizLeaderboard, prompting them to create an account and join the wiki',
	'quizgame-pct-answered-correct' => '$1 is the percentage of all users who answered the question who managed to pick the correct answer to the question',
	'quizgame-answered-correctly' => 'Shown to the user on quiz pages (Special:QuizGameHome) when viewing a question that they answered correctly',
	'quizgame-skipped' => 'Shown to the user on quiz pages (Special:QuizGameHome) when viewing a question that they chose to skip instead of answering it',
	'quizgame-answered-incorrectly' => 'Shown after answering a question incorrectly',
	'quizgame-your-answer' => 'Shown after answering a question incorrectly',
	'quizgame-correct-answer' => 'Shown after answering a question incorrectly',
	'quizgame-js-loading' => 'This is displayed when loading the page; after loading all the JavaScript etc., the JS takes care of hiding this message',
	'quizgame-js-reloading' => 'Briefly shown via JavaScript after deleting a question before the page is reloaded',
	'quizgame-js-timesup' => 'Shown on quiz pages via JavaScript after the time (30 seconds) has run out and the user hasn\t answered the question yet',
	'quizgame-js-points' => 'Shown via JavaScript; $1 is the amount of points',
	'quizgame-js-seconds' => 'This is preceded by a <code>&lt;span&gt;</code> element which is replaced by JavaScript with the correct amount of remaining time',
	'quizgame-lightbox-pause-quiz' => 'Link title; this is displayed in a lightbox after the user has picked one of the available answer options and the choice was the correct answer',
	'quizgame-lightbox-breakdown' => '"Breakdown" refers to the statistics, i.e. how many people (of the people who answered the question) picked answer option #1, how many picked #2, etc.',
	'quizgame-lightbox-breakdown-percent' => '$1 is the percentage of people who answered the question and got it right. This is displayed in a lightbox after the user has picked one of the available answer options',
	'quizgame-lightbox-correct' => 'This is displayed in a lightbox after the user has picked one of the available answer options and the choice was the correct answer',
	'quizgame-lightbox-correct-points' => '$1 is the amount of points the user earned;  this is displayed in a lightbox after the user has picked one of the available answer options',
	'quizgame-lightbox-incorrect' => 'This is displayed in a lightbox after the user has picked one of the available answer options and their choice was wrong',
	'quizgame-lightbox-incorrect-correct' => '$1 is the correct answer (user-supplied text); this is displayed in a lightbox after the user has picked one of the available answer options',
	'quizgame-skip' => 'Link title',
	'quizgame-next' => 'Link title',
	'quizgame-times-answered' => '$1 is the number of amount of times a question has been answered by different users; this is shown on Special:QuizGameHome after the user has answered a question and they\'ve been taken to a new question',
	'quizgame-chose-correct' => 'Displayed to the user if they picked the correct answer for the question. $1 is the amount of points they got from answering',
	'quizgame-chose-incorrect' => 'Displayed to the user if they picked the wrong answer for the question',
	'quizgame-flag' => 'Link title; flagging a question temporarily removes it from circulation until an admin has reviewed it and either approved it or deleted it',
	'quizgame-prev' => 'Pagination link; the English source text is an abbreviation of the word "previous". Keep this short!',
	'quizgame-nav-next' => 'Pagination link; keep this short!',
	'quizgame-answered' => 'Shown on Special:ViewQuizzes, below each question\'s title',
	'quizgame-newest' => 'Link title on Special:ViewQuizzes',
	'quizgame-popular' => 'Shown to the user on quiz pages (Special:QuizGameHome) when viewing a question that',
	'quizgame-playneverending' => 'Link text; this link is shown on Special:ViewQuizzes',
	'quizgame-viewquizzes-create' => 'Link text; this link is shown on Special:ViewQuizzes',
	'quizgame-viewquizzes-title' => 'The default page title of Special:ViewQuizzes, a special page which allows to view all available quizzes',
	'quizgame-viewquizzes-title-by-user' => 'Page title; $1 is a user name',
	'quizgame-permalink' => 'Link title; similar to the core MediaWiki message [[MediaWiki:Permalink]]',
	'quizgame-create-error-numanswers' => 'Error message shown to the user via JavaScript on the quiz creation form (Special:QuizGameHome) if they have entered only one answer option or no answer options at all and pressed the "{{int:quizgame-create-play}}" button to create a question',
	'quizgame-create-error-noquestion' => 'Error message shown to the user via JavaScript on the quiz creation form (Special:QuizGameHome) if they have forgotten to enter a question and they pressed the "{{int:quizgame-create-play}}" button to create a question',
	'quizgame-create-error-numcorrect' => 'Error message shown to the user via JavaScript on the quiz creation form (Special:QuizGameHome) if they didn\'t check the checkbox indicating which answer is correct and they pressed the "{{int:quizgame-create-play}}" button to create a question',
	'quizgame-create-title' => 'Page title of Special:QuizGameHome when the user is on the quiz creation form',
	'quizgame-create-message' => 'Instructions shown to the user on Special:QuizGameHome when they\'re creating a new quiz question',
	'quizgame-play-quiz' => 'Button text',
	'quizgame-create-write-question' => 'Instructional check shown on Special:QuizGameHome when the user is creating a brand new question',
	'quizgame-create-write-answers' => 'Instructional check shown on Special:QuizGameHome when the user is creating a brand new question',
	'quizgame-create-check-correct' => 'Instructional check shown on Special:QuizGameHome when the user is creating a brand new question',
	'quizgame-create-add-picture' => 'Header title on Special:QuizGameHome; this is followed by the mini-upload form, which allows to upload an image that is shown on the question page',
	'quizgame-create-edit-picture' => 'Link title; shown on Special:QuizGameHome after uploading an image',
	'quizgame-create-play' => 'Button text on the question creation form',
	'quizgame-pause-continue' => 'Link title',
	'quizgame-pause-view-leaderboard' => 'Link title',
	'quizgame-pause-create-question' => 'Link title',
	'quizgame-last-question' => 'Link title; "last" as in "previous"',
	'quizgame-create-threshold-title' => 'Title of Special:QuizGameHome when <code>$wgCreateQuizThresholds</code> is set; used together with the [[MediaWiki:Quizgame-create-threshold-reason]] message',
	'quizgame-create-threshold-reason' => 'Error message informing the user why they cannot create a quiz question when <code>$wgCreateQuizThresholds</code> is set; used together with the [[MediaWiki:Quizgame-create-threshold-title]] message',
	'quizgame-points' => '$1 is the amount of points; "pts" (in the original English message) is an abbreviation of "points"',
	'quizgame-time-ago' => '$1 is either one of the following or a combination of them:
* [[MediaWiki:Quizgame-time-days]]
* [[MediaWiki:Quizgame-time-hours]]
* [[MediaWiki:Quizgame-time-minutes]]
* [[MediaWiki:Quizgame-time-minutes]]

Example output: 4 hours 15 minutes ago',
	'quizgame-ajax-invalid-key' => 'Shown via JavaScript if a user tries to perform administrative actions without a valid key (key in this context is the same as "token")',
	'quizgame-ajax-unprotected' => 'Shown via JavaScript after a question has been unprotected',
	'quizgame-ajax-protected' => 'Shown via JavaScript after a question has been protected.',
	'quizgame-ajax-unflagged' => 'Shown via JavaScript after a question has been reinstated; reinstating returns a flagged quiz into the pool of available quizzes, allowing users to play it again',
	'quizgame-ajax-flagged' => 'Shown via JavaScript after a question has been flagged; flagging marks the question as needing administrative review and temporarily removes it from the pool of available questions',
	'quizgame-ajax-deleted' => 'Shown via JavaScript after successfully deleting a question',
	'quizgame-ajax-invalid-option' => 'Shown via JavaScript if the user somehow tries to access an action that is not recognized by the backend API module',
	'quizgame-ajax-nonnumeric-answer' => 'Shown via JavaScript if the user somehow passes something else than a number as the answer choice to the backend API module',
	'quizgame-ajax-already-answered' => 'Shown via JavaScript if the user tries to answer again to a question they\'ve already answered in the past',
	'quizgame-ajax-invalid-id' => 'Shown via JavaScript if the user tries to access a nonexistent question via the question\'s ID number',
	'log-name-quiz' => 'Name of the log; shown on [[Special:Log]]',
	'log-description-quiz' => 'Description of the quiz log, shown at [[Special:Log/quiz]]',
	'logentry-quiz-create' => 'Log action; $1 is the username, $4 is the question ID number',
	'logentry-quiz-delete' => 'Log action; $1 is the username, $4 is the question ID number',
	'logentry-quiz-flag' => 'Log action; $1 is the username, $4 is the question ID number',
	'logentry-quiz-protect' => 'Log action; $1 is the username, $4 is the question ID number',
	'logentry-quiz-unflag' => 'Log action; $1 is the username, $4 is the question ID number',
	'logentry-quiz-unprotect' => 'Log action; $1 is the username, $4 is the question ID number',
	'quizgame-images-category' => 'Category where all images uploaded via Special:QuestionGameUpload will be placed into',
	'right-quizadmin' => 'Description of the "quizgame" user right, shown on [[Special:ListGroupRights]]',
);

/** Finnish (Suomi)
 * @author Jack Phoenix <jack@countervandalism.net>
 */
$messages['fi'] = array(
	'quizgame-edit' => 'Muokkaa',
	'quizgame-delete' => 'Poista',
	'quizgame-protect' => 'Suojaa',
	'quizgame-reinstate' => 'Palauta kiertoon',
	'quizgame-unprotect' => 'Poista suojaus',
	'quizgame-admin-panel-title' => 'Ylläpitäjän paneeli',
	'quizgame-admin-back' => '&lt; Takaisin loppumattomaan tietovisaan',
	'quizgame-admin-flagged' => 'Merkityt kysymykset',
	'quizgame-admin-protected' => 'Suojatut kysymykset',
	'quizgame-admin-permission' => 'Sinulla ei ole oikeutta muokata',
	'quizgame-edit-title' => 'Muokataan - $1',
	'quizgame-edit-picture-link' => 'Muokkaa kuvaa',
	'quizgame-flagged-reason' => 'Syy',
	'quizgame-submitted-by' => 'Lähettänyt',
	'quizgame-question' => 'Kysymys',
	'quizgame-answers' => 'Vastaukset',
	'quizgame-picture' => 'Kuva',
	'quizgame-correct-answer-checked' => 'Oikea vastaus on merkitty.',
	'quizgame-save-page-button' => 'Tallenna sivu',
	'quizgame-cancel-button' => 'Peruuta',
	'quizgame-login-title' => 'Sinun tulee olla kirjautunut sisään luodaksesi kysymyksen',
	'quizgame-login-text' => 'Sinun tulee olla sisäänkirjautunut voidaksesi luoda kysymyksen!',
	'quizgame-main-page-button' => 'Etusivu',
	'quizgame-login-button' => 'Kirjaudu sisään',
	'quizgame-nomore-questions' => 'Ei enempää kysymyksiä!',
	'quizgame-ohnoes' => 'Tietovisakysymyksiä ei ole enempää, napsauta painiketta alapuolella luodaksesi uuden kysymyksen!',
	'quizgame-create-button' => 'Luo kysymys!',
	'quizgame-unavailable' => 'Pahoittelut, tämä kysymys ei ole saatavilla!<br /><br />[[Special:QuizGameHome|Napsauta tästä kokeillaksesi muita kysymyksiä]]',
	'quizgame-title' => 'Kysymyspeli!',
	'quizgame-intro' => 'Tervetuloa kysymyspeliin. Tässä on joitakin sääntöjä.',
	'quizgame-introlink' => 'Napsauta tästä aloittaaksesi',
	'quizgame-leaderboard-scoretitle' => 'Pisteesi',
	'quizgame-leaderboard-quizpoints' => 'Kysymyspisteet',
	'quizgame-leaderboard-correct' => 'Oikeat vastaukset',
	'quizgame-leaderboard-answered' => 'Vastattuja kysymyksiä',
	'quizgame-leaderboard-pctcorrect' => 'Prosenttia oikein',
	'quizgame-leaderboard-rank' => 'Lopullinen sijoitus',
	'quizgame-leaderboard-link' => 'pistetilasto',
	'quizgame-leaderboard-most-correct' => 'Kysymyspelin pistetilasto - Eniten oikeita vastauksia',
	'quizgame-leaderboard-highest-percent' => 'Kysymyspelin pistetilasto - prosentuaalisesti eniten oikein (väh. 50 kysymystä)',
	'quizgame-leaderboard-most-points' => 'Kysymyspelin pistetilasto - Eniten pisteitä',
	'quizgame-leaderboard-order-menu' => 'Järjestys',
	'quizgame-leaderboard-menu-points' => 'Pistettä',
	'quizgame-leaderboard-menu-correct' => 'Yhteensä oikein',
	'quizgame-leaderboard-menu-pct' => 'Korkein prosenttimäärä',
	'quizgame-leaderboard-desc-correct' => '$1 oikein',
	'quizgame-leaderboard-desc-points' => '$1 pistettä',
	'quizgame-login-or-create-to-climb' => '[[Special:UserLogin|Kirjaudu sisään]] tai [[Special:UserLogin/signup|luo käyttäjätunnus]] kilpaillaksesi ja kiivetäksesi pistetilastossa!',
	'quizgame-pct-answered-correct' => '$1% vastaajista sai tämän kysymyksen oikein',
	'quizgame-answered-correctly' => 'Vastasit oikein',
	'quizgame-skipped' => 'Ohitit tämän kysymyksen',
	'quizgame-answered-incorrectly' => 'Vastasit väärin',
	'quizgame-your-answer' => 'Vastauksesi',
	'quizgame-correct-answer' => 'Oikea vastaus',
	'quizgame-js-loading' => 'Ladataan...',
	'quizgame-js-reloading' => 'Uudelleenladataan...',
	'quizgame-js-timesup' => 'Aika loppui! Et ansaitse pisteitä, mutta yritä vastata siihen kuitenkin!',
	'quizgame-js-points' => '$1 pistettä',
	'quizgame-js-seconds' => 'sekuntia',
	'quizgame-lightbox-pause-quiz' => 'Keskeytä kysymyspeli',
	'quizgame-lightbox-breakdown' => 'Katso erittely tälle kysymykselle',
	'quizgame-lightbox-breakdown-percent' => '$1% tähän kysymykseen vastanneista ihmisistä vastasi oikein',
	'quizgame-lightbox-correct' => 'Hyvää työtä, vastasit kysymykseen oikein!',
	'quizgame-lightbox-correct-points' => 'Ansaitsit $1 pistettä',
	'quizgame-lightbox-incorrect' => 'Pahoittelut, vastasit kysymykseen väärin!',
	'quizgame-lightbox-incorrect-correct' => 'Oikea vastaus on: $1',
	'quizgame-skip' => 'Ohita',
	'quizgame-next' => 'Seuraava kysymys',
	'quizgame-times-answered' => '(vastattu {{PLURAL:$1|yhden kerran|$1 kertaa}})',
	'quizgame-chose-correct' => 'Valitsit oikean vastauksen edelliseen kysymykseen! ($1 pistettä)',
	'quizgame-chose-incorrect' => 'Valitsit väärän vastauksen edelliseen kysymykseen! (0 pistettä)',
	'quizgame-flag' => 'Merkitse',
	'quizgame-prev' => 'edell.',
	'quizgame-nav-next' => 'seur.',
	'quizgame-answered' => 'Vastattu {{PLURAL:$1|yhden kerran|$1 kertaa}}',
	'quizgame-newest' => 'Uusimmat',
	'quizgame-popular' => 'Suositut',
	'quizgame-playneverending' => 'Pelaa loputonta tietovisaa',
	'quizgame-viewquizzes-create' => 'Luo tietovisakysymys',
	'quizgame-viewquizzes-title' => 'Katso kysymyspelin kysymyksiä',
	'quizgame-viewquizzes-title-by-user' => 'Katso käyttäjän $1 kysymykset',
	'quizgame-permalink' => 'Ikilinkki',
	'quizgame-create-error-numanswers' => 'Sinulla täytyy olla vähintäänkin kaksi vastausvaihtoehtoa',
	'quizgame-create-error-noquestion' => 'Sinun täytyy kirjoittaa kysymys',
	'quizgame-create-error-numcorrect' => 'Sinulla täytyy olla vähintäänkin yksi oikea vastaus',
	'quizgame-create-title' => 'Luo kysymys',
	'quizgame-create-message' => 'Kirjoita kysymys ja lisää joitakin vastausvaihtoehtoja lisätäksesi kysymyksen loppumattomaan tietovisaan.',
	'quizgame-play-quiz' => 'Pelaa loputonta tietovisaa',
	'quizgame-create-write-question' => 'Kirjoita kysymys',
	'quizgame-create-write-answers' => 'Kirjoita vastaukset',
	'quizgame-create-check-correct' => 'Rastita oikea vastausvaihtoehto.',
	'quizgame-create-add-picture' => 'Lisää kuva',
	'quizgame-create-edit-picture' => 'Muokkaa kuvaa',
	'quizgame-create-play' => 'Luo ja pelaa!',
	'quizgame-pause-continue' => 'Jatka',
	'quizgame-pause-view-leaderboard' => 'Katso pistetilasto',
	'quizgame-pause-create-question' => 'Luo kysymys',
	'quizgame-last-question' => 'Viimeinen kysymys',
	'quizgame-create-threshold-title' => 'Luo tietovisakysymys',
	'quizgame-create-threshold-reason' => 'Pahoittelut, et voi luoda tietovisakysymystä ennen kuin sinulla on ainakin $1',
	'quizgame-points' => '$1 pistettä',
	'quizgame-time-ago' => '$1 sitten',
	'quizgame-time-days' => '{{PLURAL:$1|päivä|$1 päivää}}',
	'quizgame-time-hours' => '{{PLURAL:$1|tunti|$1 tuntia}}',
	'quizgame-time-minutes' => '{{PLURAL:$1|minuutti|$1 minuuttia}}',
	'quizgame-time-seconds' => '{{PLURAL:$1|sekunti|$1 sekuntia}}',
	'quizgame-ajax-invalid-key' => 'Tarvitset kelvollisen avaimen tehdäksesi tuon.',
	'quizgame-ajax-unprotected' => 'Kysymyksen suojaus purettiin onnistuneesti.',
	'quizgame-ajax-protected' => 'Kysymys suojattiin onnistuneesti.',
	'quizgame-ajax-unflagged' => 'Kysymys palautettiin onnistuneesti.',
	'quizgame-ajax-flagged' => 'Kysymys merkittiin onnistuneesti.',
	'quizgame-ajax-deleted' => 'Poisto onnistui!',
	'quizgame-ajax-invalid-option' => 'Virheellinen AJAX-vaihtoehto.',
	'quizgame-ajax-nonnumeric-answer' => 'Vastausvaihtoehto ei ole numeerinen.',
	'quizgame-ajax-already-answered' => 'Olet jo vastannut tähän kysymykseen.',
	'quizgame-ajax-invalid-id' => 'Kyseisellä tunnistenumerolla ei ole kysymystä.',
	'log-name-quiz' => 'Kysymysloki',
	'log-description-quiz' => 'Tämä on loki tietovisakysymyksiin kohdistuneista toiminnoista.',
	'logentry-quiz-create' => '$1 loi [[Special:QuizGameHome/$4|uuden tietovisakysymyksen]]',
	'logentry-quiz-delete' => '$1 poisti [[Special:QuizGameHome/$4|tietovisakysymyksen #$4]]',
	'logentry-quiz-flag' => '$1 merkitsi [[Special:QuizGameHome/$4|tietovisakysymyksen #$4]]',
	'logentry-quiz-protect' => '$1 suojasi [[Special:QuizGameHome/$4|tietovisakysymyksen #$4]]',
	'logentry-quiz-unflag' => '$1 poisti merkinnän [[Special:QuizGameHome/$4|tietovisakysymykseltä #$4]]',
	'logentry-quiz-unprotect' => '$1 poisti suojauksen [[Special:QuizGameHome/$4|tietovisakysymykseltä #$4]]',
	'quizgame-images-category' => 'Kysymyspelin kuvat',
	'right-quizadmin' => 'Ylläpitää kysymyspelejä',
);

/** Dutch (Nederlands)
 * @author Mark van Alphen
 * @author Mitchel Corstjens
 */
$messages['nl'] = array(
	'quizgame-edit' => 'Bewerk',
	'quizgame-delete' => 'Verwijder',
	'quizgame-protect' => 'Beveilig',
	'quizgame-reinstate' => 'Opnieuw',
	'quizgame-unprotect' => 'Beveiliging opheffen',
	'quizgame-admin-panel-title' => 'Beheerderspaneel',
	'quizgame-admin-back' => '&lt; Terug naar nooit eindigende quiz',
	'quizgame-admin-flagged' => 'Gemarkeerde vragen',
	'quizgame-admin-protected' => 'Beveiligde vragen',
	'quizgame-admin-permission' => 'Je hebt geen permissie om te bewerken',
	'quizgame-edit-title' => 'Aan het bewerken - $1',
	'quizgame-edit-picture-link' => 'Bewerk afbeelding',
	'quizgame-flagged-reason' => 'Reden',
	'quizgame-submitted-by' => 'Geplaatst door',
	'quizgame-question' => 'Vraag',
	'quizgame-answers' => 'Antwoorden',
	'quizgame-picture' => 'Afbeelding',
	'quizgame-correct-answer-checked' => 'Het correcte antwoord is aangevinkt.',
	'quizgame-save-page-button' => 'Pagina opslaan',
	'quizgame-cancel-button' => 'Annuleer',
	'quizgame-login-title' => 'Je moet ingelogd zijn om een quiz te maken',
	'quizgame-login-text' => 'Je moet inloggen, om maak een quiz te spelen!',
	'quizgame-main-page-button' => 'Hoofdpagina',
	'quizgame-login-button' => 'inloggen',
	'quizgame-submit' => 'Voeg toe',
	'quizgame-nomore-questions' => 'Geen vragen meer!',
	'quizgame-ohnoes' => 'Er zijn geen quiz spellen meer, klik a.u.b. hieronder om er een te maken!',
	'quizgame-create-button' => 'Maak een quiz!',
	'quizgame-unavailable' => 'Sorry, deze vraag is niet beschikbaar!<br />[[Special:QuizGameHome|Klik hier om andere vragen te proberen]]',
	'quizgame-title' => 'Quiz spel!',
	'quizgame-intro' => 'Welkom bij het quiz spel. Hier zijn wat regels.',
	'quizgame-introlink' => 'Klik hier om te beginnen',
	'quizgame-leaderboard-scoretitle' => 'Jouw score',
	'quizgame-leaderboard-quizpoints' => 'Quiz punten',
	'quizgame-leaderboard-correct' => 'Correcte antwoorden',
	'quizgame-leaderboard-answered' => 'Vragen beantwoord',
	'quizgame-leaderboard-pctcorrect' => 'Procent correct',
	'quizgame-leaderboard-rank' => 'Algemene rank',
	'quizgame-leaderboard-link' => 'topscores',
	'quizgame-leaderboard-most-correct' => 'Quiz topscores - meeste correcte antwoorden',
	'quizgame-leaderboard-highest-percent' => 'Quiz topscores - hoogste percentage correct (minimaal 50 vragen)',
	'quizgame-leaderboard-most-points' => 'Quiz topscores - meeste punten',
	'quizgame-leaderboard-order-menu' => 'Volgorde',
	'quizgame-leaderboard-menu-points' => 'Punten',
	'quizgame-leaderboard-menu-correct' => 'Totaal correct',
	'quizgame-leaderboard-menu-pct' => 'Hoogste percentage',
	'quizgame-leaderboard-desc-correct' => '$1 correct',
	'quizgame-leaderboard-desc-points' => '$1 punten',
	'quizgame-login-or-create-to-climb' => '[[Special:UserLogin|Inloggen]] of [[Special:UserLogin/signup|maak een account]] om te strijden en naar boven te klimmen op de topscores!',
	'quizgame-pct-answered-correct' => '$1% had deze vraag juist',
	'quizgame-answered-correctly' => 'Je antwoordde correct',
	'quizgame-skipped' => 'Je hebt deze vraag overgeslagen',
	'quizgame-answered-incorrectly' => 'Je antwoordde incorrect',
	'quizgame-your-answer' => 'Jouw antwoord',
	'quizgame-correct-answer' => 'Correcte antwoord',
	'quizgame-js-loading' => 'Laden...',
	'quizgame-js-reloading' => 'Herladen...',
	'quizgame-js-timesup' => 'Tijd is om! Je krijgt geen punten meer, maar probeer hem toch te beantwoorden!',
	'quizgame-js-points' => '$1 punten',
	'quizgame-js-seconds' => 'seconden',
	'quizgame-lightbox-pause-quiz' => 'Pauzeer quiz',
	'quizgame-lightbox-breakdown' => 'Bekijk breakdown voor deze vraag',
	'quizgame-lightbox-breakdown-percent' => '$1% van de mensen die de vraag beantwoord hebben hadden hem goed',
	'quizgame-lightbox-correct' => 'Goed gedaan, je beantwoorde de vraag juist!',
	'quizgame-lightbox-correct-points' => 'Je verdiende $1 punten',
	'quizgame-lightbox-incorrect' => 'Sorry, je beantwoorde de vraag onjuist!',
	'quizgame-lightbox-incorrect-correct' => 'Het correcte antwoord is: $1',
	'quizgame-skip' => 'Sla over',
	'quizgame-next' => 'Volgende quiz vraag',
	'quizgame-times-answered' => '({{PLURAL:$1|een keer|$1 keer}} beantwoord)',
	'quizgame-chose-correct' => 'Je koos het correcte antwoord op de vorige vraag! ($1 punten)',
	'quizgame-chose-incorrect' => 'Je koos het incorrecte antwoord op de vorige vraag! (0 punten)',
	'quizgame-flag' => 'Markeer',
	'quizgame-prev' => 'vorige',
	'quizgame-nav-next' => 'volgende',
	'quizgame-answered' => '{{PLURAL:$1|een keer bantwoord|$1 keren beantwoord}}',
	'quizgame-newest' => 'Nieuwste',
	'quizgame-popular' => 'Populair',
	'quizgame-playneverending' => 'Speel de nooit eindigende quiz',
	'quizgame-viewquizzes-create' => 'Maak een quiz vraag',
	'quizgame-viewquizzes-title' => 'Bekijk quiz vragen',
	'quizgame-viewquizzes-title-by-user' => 'Bekijk $1\'s quiz vragen',
	'quizgame-permalink' => 'Permalink',
	'quizgame-create-error-numanswers' => 'Je moet tenminste twee antwoord keuzes hebben',
	'quizgame-create-error-noquestion' => 'Je moet een vraag schrijven',
	'quizgame-create-error-numcorrect' => 'Je moet één correcte vraag hebben',
	'quizgame-create-title' => 'Maak een quiz vraag',
	'quizgame-create-message' => 'Om een quiz vraag toe te voegen aan de quiz die nooit eindigt, schrijf een vraag en voeg wat antwoord keuzes toe.',
	'quizgame-play-quiz' => 'Speel de nooit eindigende quiz',
	'quizgame-create-write-question' => 'Schrijf een vraag',
	'quizgame-create-write-answers' => 'Schrijf de antwoorden',
	'quizgame-create-check-correct' => 'Vink a.u.b. correcte antwoord aan.',
	'quizgame-create-add-picture' => 'Voeg een afbeelding toe',
	'quizgame-create-edit-picture' => 'Bewerk afbeelding',
	'quizgame-create-play' => 'Maak en speel!',
	'quizgame-pause-continue' => 'Ga verder',
	'quizgame-pause-view-leaderboard' => 'Bekijk topscores',
	'quizgame-pause-create-question' => 'Maak vraag',
	'quizgame-last-question' => 'Laatste vraag',
	'quizgame-create-threshold-title' => 'Maak quiz vraag',
	'quizgame-create-threshold-reason' => 'Sorry, je kan geen quiz vraag maken tot je tenminste $1 hebt',
	'quizgame-points' => '$1 punten',
	'quizgame-time-ago' => '$1 geleden',
	'quizgame-time-days' => '{{PLURAL:$1|een dag|$1 dagen}}',
	'quizgame-time-hours' => '{{PLURAL:$1|een uur|$1 uren}}',
	'quizgame-time-minutes' => '{{PLURAL:$1|een minuut|$1 minuten}}',
	'quizgame-time-seconds' => '{{PLURAL:$1|een seconde|$1 seconden}}',
	'log-name-quiz' => 'Quiz vraag logboek',
	'log-description-quiz' => 'Dit is een logboek van quiz vraag acties.',
	'logentry-quiz-create' => '$1 gecreëerd [[Special:QuizGameHome/$4|een nieuwe quizvraag]]',
	'logentry-quiz-delete' => '$1 verwijderd [[Special:QuizGameHome/$4|quizvraag #$4]]',
	'logentry-quiz-flag' => '$1 gemarkeerd [[Special:QuizGameHome/$4|quizvraag #$4]]',
	'logentry-quiz-protect' => '$1 beschermde [[Special:QuizGameHome/$4|quizvraag #$4]]',
	'logentry-quiz-unflag' => '$1 ongemarkeerde [[Special:QuizGameHome/$4|quizvraag #$4]]',
	'logentry-quiz-unprotect' => '$1 onbeschermde [[Special:QuizGameHome/$4|quizvraag #$4]]',
	'quizgame-ajax-invalid-key' => 'Er is een geldige sleutel nodig om dat te doen.',
	'quizgame-ajax-unprotected' => 'De vraag is onbeschermd.',
	'quizgame-ajax-protected' => 'De vraag is beschermd.',
	'quizgame-ajax-unflagged' => 'De vraag is niet meer gemarkeerd.',
	'quizgame-ajax-flagged' => 'De vraag is gemarkeerd.',
	'quizgame-ajax-deleted' => 'Verwijderen succesvol',
	'quizgame-ajax-invalid-option' => 'Ongeldige AJAX optie.',
	'quizgame-images-category' => 'QuizGame afbeeldingen',
	'right-quizadmin' => 'Administreer quiz vragen',
);