/**
 * Main JavaScript file for the QuizGame extension.
 *
 * @file
 * @ingroup Extensions
 */

window.QuizGame = {
	continue_timer: '', // has to have an initial value...
	voted: 0,
	// time() JS function from http://phpjs.org/functions/time:562
	// This used to use the __quiz_time__ variable in the past
	current_timestamp: Math.floor( new Date().getTime() / 1000 ),
	current_level: 0,
	levels_array: [30, 19, 9, 0],
	points_array: [30, 20, 10, 0],
	timer: 30,
	count_second: '', // has to have an initial value...
	points: mw.config.get( '__quiz_js_points_value__' ),// 30,
	next_level: 0, // has to have an initial value; introduced by Jack

	deleteById: function( id ) {
		var options = {
			actions: [
				{ label: mw.msg( 'cancel' ) },
				{ label: mw.msg( 'quizgame-delete' ), action: 'accept', flags: ['destructive', 'primary'] }
			]
		};
		OO.ui.confirm( mw.msg( 'quizgame-delete-confirm' ), options ).done( function ( confirmed ) {
			if ( confirmed ) {
				document.getElementById( 'items[' + id + ']' ).style.display = 'none';
				document.getElementById( 'items[' + id + ']' ).style.visibility = 'hidden';

				( new mw.Api() ).postWithToken( 'csrf', {
					action: 'quizgame',
					quizaction: 'deleteItem',
					id: id
				} ).done( function( data ) {
					document.getElementById( 'ajax-messages' ).innerHTML = data.quizgame.output;
				} );
			}
		} );
	},

	unflagById: function( id ) {
		var options = {
			actions: [
				{ label: mw.msg( 'cancel' ) },
				{ label: mw.msg( 'quizgame-unflag' ), action: 'accept', flags: ['progressive', 'primary'] }
			]
		};
		OO.ui.confirm( mw.msg( 'quizgame-unflag-confirm' ), options ).done( function ( confirmed ) {
			if ( confirmed ) {
				document.getElementById( 'items[' + id + ']' ).style.display = 'none';
				document.getElementById( 'items[' + id + ']' ).style.visibility = 'hidden';

				( new mw.Api() ).postWithToken( 'csrf', {
					action: 'quizgame',
					quizaction: 'unflagItem',
					id: id
				} ).done( function( data ) {
					document.getElementById( 'ajax-messages' ).innerHTML = data.quizgame.output;
				} );
			}
		} );
	},

	unprotectById: function( id ) {
		document.getElementById( 'items[' + id + ']' ).style.display = 'none';
		document.getElementById( 'items[' + id + ']' ).style.visibility = 'hidden';

		( new mw.Api() ).postWithToken( 'csrf', {
			action: 'quizgame',
			quizaction: 'unprotectItem',
			id: id
		} ).done( function( data ) {
			document.getElementById( 'ajax-messages' ).innerHTML = data.quizgame.output;
		} );
	},

	protectById: function( id ) {
		( new mw.Api() ).postWithToken( 'csrf', {
			action: 'quizgame',
			quizaction: 'protectItem',
			id: id
		} ).done( function( data ) {
			document.getElementById( 'ajax-messages' ).innerHTML = data.quizgame.output;
		} );
	},

	toggleCheck: function( thisBox ) {
		var nameOfOurCountableVariable;
		// Different loop variable when we're on the welcome page
		if ( jQuery( 'span#this-is-the-welcome-page' ).length > 0 ) {
			nameOfOurCountableVariable = 8 - 1;
		} else {
			nameOfOurCountableVariable = __choices_count__;
		}
		for ( var x = 1; x <= ( nameOfOurCountableVariable ); x++ ) {
			document.getElementById( 'quizgame-isright-' + x ).checked = false;
		}
		thisBox.checked = true;
	},

	uploadError: function( message ) {
		document.getElementById( 'ajax-messages' ).innerHTML = message;
		document.getElementById( 'quizgame-picture' ).innerHTML = '';

		document.getElementById( 'imageUpload-frame' ).src = mw.config.get( 'wgScriptPath' ) +
			'/index.php?title=Special:QuestionGameUpload&wpThumbWidth=80&wpOverwriteFile=true&wpDestFile=' +
			document.getElementById( 'quizGamePicture' ).value;
		document.getElementById( 'quizgame-upload' ).style.display = 'block';
		document.getElementById( 'quizgame-upload' ).style.visibility = 'visible';
	},

	completeImageUpload: function() {
		document.getElementById( 'quizgame-upload' ).style.display = 'none';
		document.getElementById( 'quizgame-upload' ).style.visibility = 'hidden';
		document.getElementById( 'quizgame-picture' ).innerHTML =
			'<img src="' + mw.config.get( 'wgExtensionAssetsPath' ) +
			'/SocialProfile/images/ajax-loader-white.gif" alt="" />';
	},

	uploadComplete: function( imgSrc, imgName, imgDesc ) {
		document.getElementById( 'quizgame-picture' ).innerHTML = imgSrc;

		document.getElementById( 'quizgame-picture' ).firstChild.src =
			document.getElementById( 'quizgame-picture' ).firstChild.src +
			'?' + Math.floor( Math.random() * 100 );

		document.quizGameEditForm.quizGamePicture.value = imgName;

		document.getElementById( 'imageUpload-frame' ).src =
			'/index.php?title=Special:QuestionGameUpload&wpThumbWidth=80&wpOverwriteFile=true&wpDestFile=' +
			imgName;

		document.getElementById( 'quizgame-editpicture-link' ).innerHTML =
			jQuery( 'a' ).prop( 'href', QuizGame.showUpload() ).text( mw.msg( 'quizgame-create-edit-picture' ) );
		document.getElementById( 'quizgame-editpicture-link' ).style.display = 'block';
		document.getElementById( 'quizgame-editpicture-link' ).style.visibility = 'visible';
	},

	showUpload: function() {
		if ( document.getElementById( 'quizgame-editpicture-link' ) ) {
			document.getElementById( 'quizgame-editpicture-link' ).style.display = 'none';
			document.getElementById( 'quizgame-editpicture-link' ).style.visibility = 'hidden';
		}
		if ( document.getElementById( 'quizgame-upload' ) ) {
			document.getElementById( 'quizgame-upload' ).style.display = 'block';
			document.getElementById( 'quizgame-upload' ).style.visibility = 'visible';
		}
	},

	/**
	 * Detects Firefox on Mac by returning boolean true if the current
	 * User-Agent is that.
	 *
	 * @return {boolean}
	 */
	detectMacXFF: function() {
		var userAgent = navigator.userAgent.toLowerCase();
		if ( userAgent.indexOf( 'mac' ) != -1 && userAgent.indexOf( 'firefox' ) != -1 ) {
			return true;
		}
	},

	deleteQuestion: function() {
		var options = {
			actions: [
				{ label: mw.msg( 'cancel' ) },
				{ label: mw.msg( 'quizgame-delete' ), action: 'accept', flags: ['destructive', 'primary'] }
			]
		};
		OO.ui.confirm( mw.msg( 'quizgame-delete-confirm' ), options ).done( function ( confirmed ) {
			if ( confirmed ) {
				var gameId = document.getElementById( 'quizGameId' ).value;
				( new mw.Api() ).postWithToken( 'csrf', {
					action: 'quizgame',
					quizaction: 'deleteItem',
					id: gameId
				} ).done( function( data ) {
					document.getElementById( 'ajax-messages' ).innerHTML = data.quizgame.output + '<br />' + mw.msg( 'quizgame-js-reloading' );
					document.location = mw.config.get( 'wgScriptPath' ) +
						'/index.php?title=Special:QuizGameHome&questionGameAction=launchGame';
				} );
			}
		} );
	},

	showEditMenu: function() {
		document.location = mw.config.get( 'wgServer' ) +
			mw.config.get( 'wgScriptPath' ) +
			'/index.php?title=Special:QuizGameHome&questionGameAction=editItem&quizGameId=' +
			document.getElementById( 'quizGameId' ).value;
	},

	/**
	 * Shows "Flag reason" dialog, and flags a quiz question
	 * for administrator attention and temporarily
	 * removes it from circulation by calling the API.
	 * Once done, the status is reported to the user.
	 * @see https://phabricator.wikimedia.org/T156304
	 */
	flagQuestion: function() {
		var options = {
			actions: [
				{ label: mw.msg( 'cancel' ) },
				{ label: mw.msg( 'quizgame-flag' ), action: 'accept', flags: ['destructive', 'primary'] },
			],
			textInput: { placeholder: mw.msg( 'quizgame-flagged-reason' ) }
		};
		OO.ui.prompt( mw.msg( 'quizgame-flag-confirm' ), options ).done( function ( reason ) {
			if ( reason !== null ) {
				var gameId = document.getElementById( 'quizGameId' ).value;
				( new mw.Api() ).postWithToken( 'csrf', {
					action: 'quizgame',
					quizaction: 'flagItem',
					id: gameId,
					comment: reason
				} ).done( function( data ) {
					document.getElementById( 'ajax-messages' ).innerHTML = data.quizgame.output;
				} );
			}
		} );
	},

	/**
	 * Protects the image used in the quiz game by calling the API and
	 * reporting back to the user.
	 */
	protectImage: function() {
		var gameId = document.getElementById( 'quizGameId' ).value;
		( new mw.Api() ).postWithToken( 'csrf', {
			action: 'quizgame',
			quizaction: 'protectItem',
			id: gameId
		} ).done( function( data ) {
			document.getElementById( 'ajax-messages' ).innerHTML = data.quizgame.output;
		} );
	},

	/**
	 * @see QuizGameHome::launchGame
	 */
	showAnswers: function() {
		document.getElementById( 'loading-answers' ).style.display = 'none';
		document.getElementById( 'loading-answers' ).style.visibility = 'hidden';
		document.getElementById( 'quizgame-answers' ).style.display = 'block';
		document.getElementById( 'quizgame-answers' ).style.visibility = 'visible';
	},

	/**
	 * @param {number} time_viewed
	 */
	countDown: function( time_viewed ) {
		/**
		 * QuizGameHome::launchGame() calls this function no matter what, and
		 * setLevel() below attempts to manipulate div#quiz-points, which
		 * exists only when the question hasn't been created by you and you
		 * haven't answered it yet.
		 *
		 * Likewise, this function attempts to manipulate #time-countdown, and
		 * (un)surprisingly enough, that also fails...
		 */

		if ( time_viewed ) {
			QuizGame.adjustTimer( time_viewed );
		}
		// It doesn't exist if you've already answered it and are a quiz admin
		if ( jQuery( '#quiz-points' ).length > 0 ) {
			QuizGame.setLevel();
		}

		if ( ( QuizGame.timer - QuizGame.next_level ) == 3 ) {
			/* @todo FIXME: get rid of YUI and use jQuery instead
			var options = {
				ease: YAHOO.util.Easing.easeOut,
				seconds: .5,
				maxcount: 4
			};
			new YAHOO.widget.Effects.Pulse( 'quiz-points', options );
			*/
		}

		if ( jQuery( '#time-countdown' ).length > 0 ) {
			document.getElementById( 'time-countdown' ).innerHTML = QuizGame.timer;
		}

		QuizGame.timer--;

		if ( QuizGame.timer == -1 ) {
			if ( jQuery( '#quiz-notime' ).length > 0 ) { // this one's pure paranoia
				document.getElementById( 'quiz-notime' ).innerHTML = mw.msg( 'quizgame-js-timesup' );
			}
			if ( QuizGame.count_second ) {
				clearTimeout( QuizGame.count_second );
			}
		} else {
			QuizGame.count_second = setTimeout( 'QuizGame.countDown(0)', 1000 );
		}
	},

	setLevel: function() {
		for ( var x = 0; x <= QuizGame.levels_array.length - 1; x++ ) {
			if (
				( QuizGame.timer === 0 && x == QuizGame.levels_array.length - 1 ) ||
				( QuizGame.timer <= QuizGame.levels_array[x] && QuizGame.timer > QuizGame.levels_array[x + 1] )
			)
			{
				//document.getElementById( 'quiz-level-' + ( x ) ).className = 'quiz-level-on';
				QuizGame.points = QuizGame.points_array[x];
				document.getElementById( 'quiz-points' ).innerHTML =
					mw.message( 'quizgame-js-points', QuizGame.points ).text();
				QuizGame.next_level = ( ( QuizGame.levels_array[x + 1] ) ? QuizGame.levels_array[x + 1] : 0 );
			} else {
				//document.getElementById( 'quiz-level-' + ( x ) ).className = 'quiz-level-off';
			}
		}
	},

	adjustTimer: function( timeViewed ) {
		var timeDiff = QuizGame.current_timestamp - timeViewed;
		if ( timeDiff > 30 ) {
			QuizGame.timer = 0;
		} else {
			QuizGame.timer = 31 - timeDiff; // give them extra second for page load
		}
		if ( QuizGame.timer > 30 ) {
			QuizGame.timer = 30;
		}
	},

	/**
	 * Go to a quiz when we know its ID (which is used in the permalink)
	 * @param {number} id Quiz ID number
	 */
	goToQuiz: function( id ) {
		window.location = mw.config.get( 'wgServer' ) + mw.config.get( 'wgScriptPath' ) +
			'/index.php?title=Special:QuizGameHome&questionGameAction=launchGame&permalinkID=' + id;
	},

	goToNextQuiz: function() {
		window.location = mw.config.get( 'wgServer' ) + mw.config.get( 'wgScriptPath' ) +
			'/index.php?title=Special:QuizGameHome&questionGameAction=launchGame&lastid=' +
			document.getElementById( 'quizGameId' ).value;
	},

	pauseQuiz: function() {
		if ( document.getElementById( 'lightbox-loader' ) ) {
			document.getElementById( 'lightbox-loader' ).innerHTML = '';
		}
		if ( QuizGame.continue_timer ) {
			clearTimeout( QuizGame.continue_timer );
		}
		document.getElementById( 'quiz-controls' ).innerHTML =
			'<a href="javascript:QuizGame.goToNextQuiz();" class="stop-button">' +
			mw.msg( 'quizgame-pause-continue' ) + '</a> - <a href="' +
			mw.config.get( 'wgScriptPath' ) + '/index.php?title=Special:QuizLeaderboard" class="stop-button">' +
			mw.msg( 'quizgame-pause-view-leaderboard' ) + '</a> - <a href="' +
			mw.config.get( 'wgScriptPath' ) + '/index.php?title=Special:QuizGameHome&questionGameAction=createForm" class="stop-button">' +
			mw.msg( 'quizgame-pause-create-question' ) + '</a><br /><br /><a href="' +
			mw.config.get( 'wgServer' ) + mw.config.get( 'wgScriptPath' ) + '" class="stop-button">' +
			mw.msg( 'quizgame-main-page-button' ) +
			'</a>';
	},

	/**
	 * Get the HTML for the "Loading..." animation.
	 *
	 * @return {string} "Loading" animation HTML
	 */
	getLoader: function() {
		var loader = '';
		loader += '<div id="lightbox-loader">';
		if ( window.isFlashSupported() ) {
			loader += '<embed src="' + mw.config.get( 'wgExtensionAssetsPath' ) +
			'/SocialProfile/images/ajax-loading.swf" quality="high" wmode="transparent" bgcolor="#ffffff"' +
			'pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash"' +
			'type="application/x-shockwave-flash" width="100" height="100"></embed></div>';
		} else {
			loader += '<img src="' + mw.config.get( 'wgExtensionAssetsPath' ) + '/SocialProfile/images/ajax-loader-white.gif" alt="" />';
		}
		return loader;
	},

	/**
	 * Sets the given string as the text for the lightbox.
	 *
	 * @param {string} txt Text to output in the lightbox
	 */
	setLightboxText: function( txt ) {
		var textForLightBox = '', loader = '';
		if ( txt ) {
			textForLightBox = '<br /><br />' + txt;
		}
		if ( !QuizGame.detectMacXFF() ) {
			loader = QuizGame.getLoader();
		} else {
			loader = mw.msg( 'quizgame-js-loading' );
		}
		LightBox.init();
		LightBox.setText( loader + textForLightBox );
	},

	/**
	 * Marks a question as skipped (via the API) and moves onto the next
	 * question.
	 */
	skipQuestion: function() {
		var objLink = {};

		objLink.href = '';
		objLink.title = mw.msg( 'quizgame-js-loading' );

		LightBox.init();
		LightBox.show( objLink );
		QuizGame.setLightboxText( '' );

		var gameId = document.getElementById( 'quizGameId' ).value;
		( new mw.Api() ).postWithToken( 'csrf', {
			action: 'quizgamevote',
			answer: -1,
			id: gameId,
			points: 0
		} ).done( function( data ) {
			QuizGame.goToNextQuiz( document.getElementById( 'quizGameId' ).value );
		} );
	},

	/**
	 * Casts a vote and forwards the user to a new question
	 *
	 * @param {number} id Number (on a range from 1 to 8) of the answer option
	 */
	vote: function( id ) {
		if ( QuizGame.count_second ) {
			clearTimeout( QuizGame.count_second );
		}

		if ( QuizGame.voted == 1 ) {
			return 0;
		}

		QuizGame.voted = 1;

		document.getElementById( 'ajax-messages' ).innerHTML = '';

		var objLink = {};

		objLink.href = '';
		objLink.title = '';

		LightBox.init();
		LightBox.show( objLink );

		var quiz_controls = '<div id="quiz-controls"><a href="javascript:void(0)" onclick="QuizGame.pauseQuiz()" class="stop-button">' +
			mw.msg( 'quizgame-lightbox-pause-quiz' ) + '</a></div>';

		var view_results_button = '<a href="javascript:QuizGame.goToQuiz(' +
			document.getElementById( 'quizGameId' ).value +
			');" class="stop-button">' + mw.msg( 'quizgame-lightbox-breakdown' ) + '</a>';

		var gameId = document.getElementById( 'quizGameId' ).value;

		( new mw.Api() ).postWithToken( 'csrf', {
			action: 'quizgamevote',
			answer: id,
			id: gameId,
			points: QuizGame.points
		} ).done( function( payload ) {
			var text;

			QuizGame.continue_timer = setTimeout( 'QuizGame.goToNextQuiz()', 3000 );

			// An unlikely edge case: someone tries to vote twice for some reason
			if ( payload.error ) {
				QuizGame.setLightboxText(
					'<p class="quizgame-lightbox-wrongtext">' +
					payload.error.info +
					'</p>'
				);
			}

			var percent_right = mw.msg( 'quizgame-lightbox-breakdown-percent', payload.quizgamevote.result.percentRight );
			if ( payload.quizgamevote.result.isRight == 'true' ) {
				text = '<p class="quizgame-lightbox-righttext">' +
					mw.msg( 'quizgame-lightbox-correct' ) + '<br /><br />' +
					mw.msg( 'quizgame-lightbox-correct-points', QuizGame.points ) +
					'</p><br />' + percent_right +
					'<br /><br />' + view_results_button + '<br /><br />' +
					quiz_controls;
			} else {
				text = '<p class="quizgame-lightbox-wrongtext">' +
					mw.msg( 'quizgame-lightbox-incorrect' ) + '<br />' +
					mw.msg( 'quizgame-lightbox-incorrect-correct', payload.quizgamevote.result.rightAnswer ) +
					'</p><br />' + percent_right +
					'<br /><br />' + view_results_button + '<br /><br />' +
					quiz_controls;
			}
			QuizGame.setLightboxText( text );
		} );
	},

	welcomePage_uploadError: function( message ) {
		document.getElementById( 'imageUpload-frame' ).src = mw.config.get( 'wgScriptPath' ) +
			'/index.php?title=Special:QuestionGameUpload&wpThumbWidth=75';
		var uploadElement = document.getElementById( 'quizgame-picture-upload' );
		if ( uploadElement ) {
			uploadElement.style.display = 'block';
			uploadElement.style.visibility = 'visible';
		}
	},

	welcomePage_completeImageUpload: function() {
		var uploadElement = document.getElementById( 'quizgame-picture-upload' );
		if ( uploadElement ) {
			uploadElement.style.display = 'none';
			uploadElement.style.visibility = 'hidden';
		}
		var preview = document.getElementById( 'quizgame-picture-preview' );
		if ( preview ) {
			preview.innerHTML = '<img src="' + mw.config.get( 'wgExtensionAssetsPath' ) +
				'/SocialProfile/images/ajax-loader-white.gif" alt="" />';
		}
	},

	/**
	 * Called on the new quiz creation page after a successful image upload.
	 * This sets the value of the hidden "quizGamePictureName" field as well as
	 * shows a small preview of the uploaded image.
	 *
	 * @param {string} imgSrc The full img tag of the uploaded file
	 * @param {string} imgName Name of the uploaded file
	 * @param {string} imgDesc Unused, consider removing this useless parameter
	 */
	welcomePage_uploadComplete: function( imgSrc, imgName, imgDesc ) {
		var previewElement = document.getElementById( 'quizgame-picture-preview' );
		if ( previewElement ) {
			previewElement.innerHTML = imgSrc;
			// Show the image after reupload (i.e. user uploads an image, then
			// clicks on the "Edit picture" link and uploads a different image)
			// Without these two lines, the "reuploaded" image doesn't show up
			previewElement.style.visibility = 'visible';
			previewElement.style.display = 'block';
		}
		// This line sets the value of the hidden "quizGamePictureName" field
		document.quizGameCreate.quizGamePictureName.value = imgName;
		document.getElementById( 'imageUpload-frame' ).src = mw.config.get( 'wgScriptPath' ) +
			'/index.php?title=Special:QuestionGameUpload&wpThumbWidth=75';
		document.getElementById( 'quizgame-picture-reupload' ).style.display = 'block';
		document.getElementById( 'quizgame-picture-reupload' ).style.visibility = 'visible';
	},

	/**
	 * Creates new answer boxes (up to 8 boxes) and makes
	 * them visible on the welcome page.
	 */
	updateAnswerBoxes: function() {
		for ( var x = 1; x <= ( 8 - 1 ); x++ ) {
			if ( document.getElementById( 'quizgame-answer-' + x ).value ) {
				document.getElementById( 'quizgame-answer-container-' + ( x + 1 ) ).style.display = 'block';
				document.getElementById( 'quizgame-answer-container-' + ( x + 1 ) ).style.visibility = 'visible';
			}
		}
	},

	/**
	 * Called when the user clicks on the submit button on the new quiz creation
	 * form.
	 * Performs some validation and if there is a question, at least two answers
	 * and one correct choice, submits the form.
	 * Otherwise an error message is displayed to the user.
	 */
	startGame: function() {
		var errorText = '',
			answers = 0,
			right = 0;

		for ( var x = 1; x <= 8; x++ ) {
			if ( document.getElementById( 'quizgame-answer-' + x ).value ) {
				answers++;
			}
		}

		if ( answers < 2 ) {
			errorText += mw.msg( 'quizgame-create-error-numanswers' ) + '<p>';
		}
		if ( !document.getElementById( 'quizgame-question' ).value ) {
			errorText += mw.msg( 'quizgame-create-error-noquestion' ) + '<p>';
		}

		for ( x = 1; x <= 8; x++ ) {
			if ( document.getElementById( 'quizgame-isright-' + x ).checked ) {
				right++;
			}
		}

		if ( right != 1 ) {
			errorText += mw.msg( 'quizgame-create-error-numcorrect' ) + '<p>';
		}

		if ( !errorText ) {
			document.getElementById( 'quizGameCreate' ).submit();
		} else {
			document.getElementById( 'quiz-game-errors' ).innerHTML = '<h2>' + errorText + '</h2>';
		}
	},

	showAttachPicture: function() {
		document.getElementById( 'quizgame-picture-preview' ).style.display = 'none';
		document.getElementById( 'quizgame-picture-preview' ).style.visibility = 'hidden';
		document.getElementById( 'quizgame-picture-reupload' ).style.display = 'none';
		document.getElementById( 'quizgame-picture-reupload' ).style.visibility = 'hidden';
		document.getElementById( 'quizgame-picture-upload' ).style.display = 'block';
		document.getElementById( 'quizgame-picture-upload' ).style.visibility = 'visible';
	},

	doHover: function( divID ) {
		document.getElementById( divID ).style.backgroundColor = '#FFFCA9';
	},

	endHover: function( divID ) {
		document.getElementById( divID ).style.backgroundColor = '';
	}
};

jQuery( function() {
	// Code specific to Special:QuizGameHome
	if ( mw.config.get( 'wgCanonicalSpecialPageName' ) == 'QuizGameHome' ) {
		// When editing a quiz game that has an image
		jQuery( 'p#quizgame-editpicture-link' ).append(
			jQuery( '<a>' )
				.attr( 'href', '#' )
				.on( 'click', function() { QuizGame.showUpload(); } )
				.text( mw.msg( 'quizgame-edit-picture-link' ) )
		);

		// "Edit" link
		jQuery( 'div.edit-button-quiz-game' ).append(
			jQuery( '<a>' )
				.attr( 'href', '#' )
				.on( 'click', function() { QuizGame.showEditMenu(); } )
				.text( mw.msg( 'quizgame-edit' ) )
		);

		// Voting links
		jQuery( 'a.quiz-vote-link' ).each( function( index ) {
			jQuery( this ).on( 'click', function() {
				QuizGame.vote( jQuery( this ).data( 'choice-id' ) );
			} );
		} );

		// "Skip this question" link
		jQuery( 'a.skip-question-link' ).on( 'click', function() {
			QuizGame.skipQuestion();
		} );

		// Various admin panel links all over the place
		jQuery( 'a.flag-quiz-link' )
			.attr( 'href', '#' )
			.on( 'click', function() { QuizGame.flagQuestion(); } );
		jQuery( 'a.protect-image-link' )
			.attr( 'href', '#' )
			.on( 'click', function() { QuizGame.protectImage(); } );
		jQuery( 'a.delete-quiz-link' )
			.attr( 'href', '#' )
			.on( 'click', function() { QuizGame.deleteQuestion(); } );
		jQuery( 'a.delete-by-id' )
			.attr( 'href', '#' )
			.on( 'click', function() { QuizGame.deleteById( jQuery( this ).data( 'quiz-id' ) ); } );
		jQuery( 'a.protect-by-id' )
			.attr( 'href', '#' )
			.on( 'click', function() { QuizGame.protectById( jQuery( this ).data( 'quiz-id' ) ); } );
		jQuery( 'a.unflag-by-id' )
			.attr( 'href', '#' )
			.on( 'click', function() { QuizGame.unflagById( jQuery( this ).data( 'quiz-id' ) ); } );
		jQuery( 'a.unprotect-by-id' )
			.attr( 'href', '#' )
			.on( 'click', function() { QuizGame.unprotectById( jQuery( this ).data( 'quiz-id' ) ); } );

		// Answer boxes on the quiz creation view (welcome page)
		if ( jQuery( 'span#this-is-the-welcome-page' ).length > 0 ) {
			jQuery( 'input[id^="quizgame-answer-"]' ).on( {
				'input': function() {
					// Mobile (Android) support
					// @see https://mathiasbynens.be/notes/oninput
					// @todo FIXME: jumpy, but better than not showing the boxes 3-8 at all
					jQuery( this ).off( 'keyup' );
					QuizGame.updateAnswerBoxes();
				},
				'keyup': function() {
					QuizGame.updateAnswerBoxes();
				}
			} );
		}
		jQuery( 'input[type="checkbox"]' ).on( 'click', function() {
			QuizGame.toggleCheck( this );
		} );

		// "Edit Picture" link
		jQuery( 'p#quizgame-picture-reupload' ).append(
			jQuery( '<a>' )
				.attr( 'href', '#' )
				.on( 'click', function( event ) { event.preventDefault(); QuizGame.showAttachPicture(); } )
				.text( mw.msg( 'quizgame-create-edit-picture' ) )
		);

		// Handler for the "Create and Play!" button
		jQuery( 'div#startButton > input[class="site-button"]' ).on( 'click', function() {
			QuizGame.startGame();
		} );
	}

	// Code specific to Special:ViewQuizzes
	if ( mw.config.get( 'wgCanonicalSpecialPageName' ) == 'ViewQuizzes' ) {
		jQuery( 'div.view-quizzes-row, div.view-quizzes-row-bottom' ).each( function( index ) {
			jQuery( this ).on( {
				/* this has to stay on the PHP code for the time being...
				'click': function() {
				},
				*/
				'mouseout': function() {
					QuizGame.endHover( jQuery( this ).attr( 'id' ) );
				},
				'mouseover': function() {
					QuizGame.doHover( jQuery( this ).attr( 'id' ) );
				}
			} );
		} );
	}
} );
