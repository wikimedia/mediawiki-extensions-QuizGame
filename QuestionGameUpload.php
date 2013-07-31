<?php
/**
 * New version of that fucking AJAX upload form, 1.16-compatible.
 *
 * wpThumbWidth is the width of the thumbnail that will be returned
 * Also, to prevent overwriting uploads of files with popular names i.e.
 * Image.jpg all the uploaded files are prepended with the current timestamp.
 *
 * @file
 * @ingroup SpecialPage
 * @ingroup Upload
 * @author Jack Phoenix <jack@countervandalism.net>
 * @date 26 June 2011
 * @note Based on 1.16 core SpecialUpload.php (GPL-licensed) by Bryan et al.
 * @see http://bugzilla.shoutwiki.com/show_bug.cgi?id=22
 */
class SpecialQuestionGameUpload extends SpecialUpload {
	/**
	 * Constructor: initialise object
	 * Get data POSTed through the form and assign them to the object
	 *
	 * @param $request WebRequest: Data posted.
	 */
	public function __construct( $request = null ) {
		SpecialPage::__construct( 'QuestionGameUpload', 'upload', false );
	}

	/**
	 * apparently you don't need to (re)declare the protected/public class
	 * member variables here, so I removed them.
	 */

	/**
	 * Initialize instance variables from request and create an Upload handler
	 *
	 * What was changed here: $this->mIgnoreWarning is now unconditionally true
	 * and mUpload uses QuizFileUpload instead of UploadBase so that it can add
	 * the timestamp to the filename.
	 *
	 * @param $request WebRequest: The request to extract variables from
	 */
	protected function loadRequest() {
		$this->mRequest = $request = $this->getRequest();
		$this->mSourceType        = $request->getVal( 'wpSourceType', 'file' );
		$this->mUpload            = QuizFileUpload::createFromRequest( $request );
		$this->mUploadClicked     = $request->wasPosted()
			&& ( $request->getCheck( 'wpUpload' )
				|| $request->getCheck( 'wpUploadIgnoreWarning' ) );

		// Guess the desired name from the filename if not provided
		$this->mDesiredDestName   = $request->getText( 'wpDestFile' );
		if( !$this->mDesiredDestName && $request->getFileName( 'wpUploadFile' ) !== null ) {
			$this->mDesiredDestName = $request->getFileName( 'wpUploadFile' );
		}
		$this->mComment           = $request->getText( 'wpUploadDescription' );
		$this->mLicense           = $request->getText( 'wpLicense' );

		$this->mDestWarningAck    = $request->getText( 'wpDestFileWarningAck' );
		$this->mIgnoreWarning     = true;//$request->getCheck( 'wpIgnoreWarning' ) || $request->getCheck( 'wpUploadIgnoreWarning' );
		$this->mWatchthis         = $request->getBool( 'wpWatchthis' ) && $this->getUser()->isLoggedIn();
		$this->mCopyrightStatus   = $request->getText( 'wpUploadCopyStatus' );
		$this->mCopyrightSource   = $request->getText( 'wpUploadSource' );

		$this->mForReUpload       = $request->getBool( 'wpForReUpload' ); // updating a file
		$this->mCancelUpload      = $request->getCheck( 'wpCancelUpload' )
		                         || $request->getCheck( 'wpReUpload' ); // b/w compat

		// If it was posted check for the token (no remote POST'ing with user credentials)
		$token = $request->getVal( 'wpEditToken' );
		if( $this->mSourceType == 'file' && $token == null ) {
			// Skip token check for file uploads as that can't be faked via JS...
			// Some client-side tools don't expect to need to send wpEditToken
			// with their submissions, as that's new in 1.16.
			$this->mTokenOk = true;
		} else {
			$this->mTokenOk = $this->getUser()->matchEditToken( $token );
		}
	}

	/**
	 * Special page entry point
	 *
	 * What was changed here: the setArticleBodyOnly() line below was added,
	 * and some bits of code were entirely removed.
	 */
	public function execute( $par ) {
		// Disable the skin etc.
		$this->getOutput()->setArticleBodyOnly( true );

		// Allow framing so that after uploading an image, we can actually show
		// it to the user :)
		$this->getOutput()->allowClickjacking();

		# Check that uploading is enabled
		if( !UploadBase::isEnabled() ) {
			throw new ErrorPageError( 'uploaddisabled', 'uploaddisabledtext' );
		}

		# Check permissions
		$user = $this->getUser();
		$permissionRequired = UploadBase::isAllowed( $user );
		if( $permissionRequired !== true ) {
			throw new PermissionsError( $permissionRequired );
		}

		# Check blocks
		if( $user->isBlocked() ) {
			throw new UserBlockedError( $user->getBlock() );
		}

		# Check whether we actually want to allow changing stuff
		$this->checkReadOnly();

		$this->loadRequest();

		# Unsave the temporary file in case this was a cancelled upload
		if ( $this->mCancelUpload ) {
			if ( !$this->unsaveUploadedFile() ) {
				# Something went wrong, so unsaveUploadedFile showed a warning
				return;
			}
		}

		# Process upload or show a form
		if ( $this->mTokenOk && !$this->mCancelUpload && ( $this->mUpload && $this->mUploadClicked ) ) {
			$this->processUpload();
		} else {
			$this->showUploadForm( $this->getUploadForm() );
		}

		# Cleanup
		if ( $this->mUpload ) {
			$this->mUpload->cleanupTempFile();
		}
	}

	/**
	 * Get a QuestionGameUploadForm instance with title and text properly set.
	 *
	 * @param $message String: HTML string to add to the form
	 * @param $sessionKey String: session key in case this is a stashed upload
	 * @return QuestionGameUploadForm
	 */
	protected function getUploadForm( $message = '', $sessionKey = '', $hideIgnoreWarning = false ) {
		# Initialize form
		$form = new QuestionGameUploadForm( array(
			'watch' => $this->getWatchCheck(),
			'forreupload' => $this->mForReUpload,
			'sessionkey' => $sessionKey,
			'hideignorewarning' => $hideIgnoreWarning,
			'destwarningack' => (bool)$this->mDestWarningAck,
			'destfile' => $this->mDesiredDestName,
		) );
		$form->setTitle( $this->getTitle() );

		# Check the token, but only if necessary
		if( !$this->mTokenOk && !$this->mCancelUpload
				&& ( $this->mUpload && $this->mUploadClicked ) ) {
			$form->addPreText( $this->msg( 'session_fail_preview' )->parse() );
		}

		# Add upload error message
		$form->addPreText( $message );

		return $form;
	}

	/**
	 * Stashes the upload and shows the main upload form.
	 *
	 * Note: only errors that can be handled by changing the name or
	 * description should be redirected here. It should be assumed that the
	 * file itself is sane and has passed UploadBase::verifyFile. This
	 * essentially means that UploadBase::VERIFICATION_ERROR and
	 * UploadBase::EMPTY_FILE should not be passed here.
	 *
	 * @param $message String: HTML message to be passed to mainUploadForm
	 */
	protected function showRecoverableUploadError( $message ) {
		$sessionKey = $this->mUpload->stashSession();
		$message = '<h2>' . $this->msg( 'uploaderror' )->escaped() . "</h2>\n" .
			'<div class="error">' . $message . "</div>\n";

		$form = $this->getUploadForm( $message, $sessionKey );
		$form->setSubmitText( $this->msg( 'upload-tryagain' )->escaped() );
		$this->showUploadForm( $form );
	}

	/**
	 * Show the upload form with error message, but do not stash the file.
	 *
	 * @param $message String: error message to show
	 */
	protected function showUploadError( $message ) {
		$message = addslashes( $message );
		$message = str_replace( array( "\r\n", "\r", "\n" ), ' ', $message );
		$output = "<script language=\"javascript\">
			/*<![CDATA[*/
				window.parent.QuizGame.uploadError( '{$message}' );
			/*]]>*/</script>";
		$this->showUploadForm( $this->getUploadForm( $output ) );
	}

	/**
	 * Do the upload.
	 * Checks are made in SpecialQuestionGameUpload::execute()
	 *
	 * What was changed here: $wgContLang was added as a global,
	 * one hook and the post-upload redirect were removed in favor of the code
	 * below the $this->mUploadSuccessful = true; line
	 */
	protected function processUpload() {
		global $wgContLang;

		// Fetch the file if required
		$status = $this->mUpload->fetchFile();
		if( !$status->isOK() ) {
			$this->showUploadError(
				$this->getUploadForm( $this->getOutput()->parse( $status->getWikiText() ) )
			);
			return;
		}

		// Upload verification
		$details = $this->mUpload->verifyUpload();
		if ( $details['status'] != UploadBase::OK ) {
			$this->processVerificationError( $details );
			return;
		}

		// Verify permissions for this title
		$permErrors = $this->mUpload->verifyTitlePermissions( $this->getUser() );
		if( $permErrors !== true ) {
			$code = array_shift( $permErrors[0] );
			$this->showRecoverableUploadError( $this->msg( $code, $permErrors[0] )->parse() );
			return;
		}

		$this->mLocalFile = $this->mUpload->getLocalFile();

		// Check warnings if necessary
		if( !$this->mIgnoreWarning ) {
			$warnings = $this->mUpload->checkWarnings();
			if( $this->showUploadWarning( $warnings ) ) {
				return;
			}
		}

		// add *all* images uploaded via this form into the category defined in
		// MediaWiki:Quiz-images-category
		// yes, I know that this is ugly as hell but Jedi can live with it and
		// so can I. It's not my fault that loadRequest() is a useless piece of
		// crap.
		$localizedNS = $wgContLang->getNsText( NS_CATEGORY );
		$categoriesText = '[[' . $localizedNS . ':' . wfMessage( 'quizgame-images-category' )->inContentLanguage()->plain() . ']]';

		// Get the page text if this is not a reupload
		//if( !$this->mForReUpload ) {
			$pageText = self::getInitialPageText(
				//$this->mComment, // text to be inserted on the file page
				$categoriesText,
				$this->mLicense,
				$this->mCopyrightStatus,
				$this->mCopyrightSource
			);
		//} else {
			//$pageText = false;
		//}

		$status = $this->mUpload->performUpload(
			$categoriesText,//$this->mComment, // upload summary (shown on RecentChanges etc.)
			$pageText, // text inserted on the page
			$this->mWatchthis, $this->getUser()
		);

		if ( !$status->isGood() ) {
			$this->showUploadError( $this->getOutput()->parse( $status->getWikiText() ) );
			return;
		}

		// Success, redirect to description page
		$this->mUploadSuccessful = true;

		$this->getOutput()->setArticleBodyOnly( true );
		$this->getOutput()->clearHTML();

		$thumbWidth = $this->getRequest()->getInt( 'wpThumbWidth', 75 );

		// The old version below, which initially used $this->mDesiredDestName
		// instead of that getTitle() caused plenty o' fatals...the new version
		// seems to be OK...I think.
		//$img = wfFindFile( $this->mUpload->getTitle() );
		$img = $this->mLocalFile;

		if ( !$img ) {
			// This should NOT be happening...the transform() call below
			// will cause a fatal error if $img is not an object
			error_log(
				'QuizGame/MiniAjaxUpload FATAL! $this->mUpload is: ' .
				print_r( $this->mUpload, true )
			);
		}

		$thumb = $img->transform( array( 'width' => $thumbWidth ) );
		$img_tag = $thumb->toHtml();
		$slashedImgTag = addslashes( $img_tag );

		// $this->mDesiredDestName doesn't include the timestamp so we can't
		// use it as the second param to the JS function...
		// To explain this fucked up logic: here we pass the image name to the
		// uploadComplete JS function (see QuizGame.js), and that function sets
		// the value of the hidden <input> with the ID and name
		// "quizGamePictureName" to the image's name.
		// QuizGameHome::createQuizGame() uses WebRequest to get
		// the value of quizGamePictureName and inserts that into the database.
		// If we don't pass the correct (timestamped) image name here, we will
		// end up with fatals that are pretty damn tricky to fix.
		$imgName = $img->getTitle()->getDBkey();
		echo "<script language=\"javascript\">
			/*<![CDATA[*/
			// This element only exists when we're editing a pre-existing quiz
			var oldCorrect = window.parent.document.getElementById( 'old_correct' );
			if ( oldCorrect ) {
				window.parent.QuizGame.uploadComplete(\"{$slashedImgTag}\", \"{$imgName}\", '');
			} else {
				// This is what we want to call when we're creating a brand
				// new quiz
				window.parent.QuizGame.welcomePage_uploadComplete(\"{$slashedImgTag}\", \"{$imgName}\", '');
			}
			/*]]>*/</script>";
	}
}

class QuestionGameUploadForm extends UploadForm {
	protected $mWatch;
	protected $mForReUpload;
	protected $mSessionKey;
	protected $mHideIgnoreWarning;
	protected $mDestWarningAck;
	protected $mDestFile;

	protected $mSourceIds;

	public function __construct( $options = array() ) {
		$this->mWatch = !empty( $options['watch'] );
		$this->mForReUpload = !empty( $options['forreupload'] );
		$this->mSessionKey = isset( $options['sessionkey'] )
				? $options['sessionkey'] : '';
		$this->mHideIgnoreWarning = !empty( $options['hideignorewarning'] );
		$this->mDestWarningAck = !empty( $options['destwarningack'] );

		$this->mDestFile = isset( $options['destfile'] ) ? $options['destfile'] : '';

		$sourceDescriptor = $this->getSourceSection();
		$descriptor = $sourceDescriptor
			+ $this->getDescriptionSection()
			+ $this->getOptionsSection();

		HTMLForm::__construct( $descriptor, 'upload' );

		# Set some form properties
		$this->setSubmitText( $this->msg( 'uploadbtn' )->text() );
		$this->setSubmitName( 'wpUpload' );
		$this->setSubmitTooltip( 'upload' );
		$this->setId( 'mw-upload-form' );

		# Build a list of IDs for JavaScript insertion
		$this->mSourceIds = array();
		foreach ( $sourceDescriptor as $key => $field ) {
			if ( !empty( $field['id'] ) ) {
				$this->mSourceIds[] = $field['id'];
			}
		}
	}

	function displayForm( $submitResult ) {
		global $wgOut;
		parent::displayForm( $submitResult );
		if ( method_exists( $wgOut, 'allowClickjacking' ) ) {
			$wgOut->allowClickjacking();
		}
	}

	/**
	 * Wrap the form innards in an actual <form> element
	 * This is here because HTMLForm's default wrapForm() is so stupid that it
	 * doesn't let us add the onsubmit attribute...oh yeah, and because using
	 * $wgOut->addInlineScript in that addUploadJS() function doesn't work,
	 * either
	 *
	 * @param $html String: HTML contents to wrap.
	 * @return String: wrapped HTML.
	 */
	function wrapForm( $html ) {
		# Include a <fieldset> wrapper for style, if requested.
		if ( $this->mWrapperLegend !== false ) {
			$html = Xml::fieldset( $this->mWrapperLegend, $html );
		}
		# Use multipart/form-data
		$encType = $this->mUseMultipart
			? 'multipart/form-data'
			: 'application/x-www-form-urlencoded';
		# Attributes
		$attribs = array(
			'action'  => $this->getTitle()->getFullURL(),
			'method'  => 'post',
			'class'   => 'visualClear',
			'enctype' => $encType,
			'onsubmit' => 'submitForm()', // added
			'id' => 'upload', // added
			'name' => 'upload' // added
		);

		// fucking newlines...
		return "<script type=\"text/javascript\">
	function submitForm() {
		var valueToCheck = document.getElementById( 'wpUploadFile' ).value;
		if( valueToCheck != '' ) {
			// This value is set only when we're editing a pre-existing quiz
			if ( window.parent.document.getElementById( 'old_correct' ) ) {
				window.parent.QuizGame.completeImageUpload();
			} else {
				// This is what we want to call when we're creating a brand
				// new quiz
				window.parent.QuizGame.welcomePage_completeImageUpload();
			}
			return true;
		} else {
			// textError method is gone and I can't find it anywhere...
			alert( '" . str_replace( "\n", ' ', wfMessage( 'emptyfile' )->plain() ) . "' );
			return false;
		}
	}
</script>\n" . Html::rawElement( 'form', $attribs, $html );
	}

	/**
	 * Get the descriptor of the fieldset that contains the file source
	 * selection. The section is 'source'
	 *
	 * @return array Descriptor array
	 */
	protected function getSourceSection() {
		if ( $this->mSessionKey ) {
			return array(
				'wpSessionKey' => array(
					'type' => 'hidden',
					'default' => $this->mSessionKey,
				),
				'wpSourceType' => array(
					'type' => 'hidden',
					'default' => 'Stash',
				),
			);
		}

		$canUploadByUrl = UploadFromUrl::isEnabled() && $this->getUser()->isAllowed( 'upload_by_url' );
		$radio = $canUploadByUrl;
		$selectedSourceType = strtolower( $this->getRequest()->getText( 'wpSourceType', 'File' ) );

		$descriptor = array();
		$descriptor['UploadFile'] = array(
			'class' => 'UploadSourceField',
			'section' => 'source',
			'type' => 'file',
			'id' => 'wpUploadFile',
			'label-message' => 'sourcefilename',
			'upload-type' => 'File',
			'radio' => &$radio,
			// help removed, we don't need any tl,dr on this mini-upload form
			'checked' => $selectedSourceType == 'file',
		);
		if ( $canUploadByUrl ) {
			$descriptor['UploadFileURL'] = array(
				'class' => 'UploadSourceField',
				'section' => 'source',
				'id' => 'wpUploadFileURL',
				'label-message' => 'sourceurl',
				'upload-type' => 'url',
				'radio' => &$radio,
				'checked' => $selectedSourceType == 'url',
			);
		}

		return $descriptor;
	}

	/**
	 * Get the descriptor of the fieldset that contains the file description
	 * input. The section is 'description'
	 *
	 * @return array Descriptor array
	 */
	protected function getDescriptionSection() {
		$descriptor = array(
			'DestFile' => array(
				'type' => 'hidden',
				'id' => 'wpDestFile',
				'size' => 60,
				'default' => $this->mDestFile,
				# FIXME: hack to work around poor handling of the 'default' option in HTMLForm
				'nodata' => strval( $this->mDestFile ) !== '',
				'readonly' => true // users do not need to change the file name; normally this is true only when reuploading
			)
		);

		global $wgUseCopyrightUpload;
		if ( $wgUseCopyrightUpload ) {
			$descriptor['UploadCopyStatus'] = array(
				'type' => 'text',
				'section' => 'description',
				'id' => 'wpUploadCopyStatus',
				'label-message' => 'filestatus',
			);
			$descriptor['UploadSource'] = array(
				'type' => 'text',
				'section' => 'description',
				'id' => 'wpUploadSource',
				'label-message' => 'filesource',
			);
		}

		return $descriptor;
	}

	/**
	 * Get the descriptor of the fieldset that contains the upload options,
	 * such as "watch this file". The section is 'options'
	 *
	 * @return array Descriptor array
	 */
	protected function getOptionsSection() {
		$descriptor = array();

		$descriptor['wpDestFileWarningAck'] = array(
			'type' => 'hidden',
			'id' => 'wpDestFileWarningAck',
			'default' => $this->mDestWarningAck ? '1' : '',
		);

		if ( $this->mForReUpload ) {
			$descriptor['wpForReUpload'] = array(
				'type' => 'hidden',
				'id' => 'wpForReUpload',
				'default' => '1',
			);
		}

		return $descriptor;
	}

	/**
	 * Add the upload JS and show the form.
	 */
	public function show() {
		HTMLForm::show();
	}

	/**
	 * Empty function; submission is handled elsewhere.
	 *
	 * @return bool false
	 */
	function trySubmit() {
		return false;
	}
}

/**
 * Quick helper class for SpecialQuestionGameUpload::loadRequest; this prefixes
 * the filename with the timestamp. Yes, another class is needed for it. *sigh*
 */
class QuizFileUpload extends UploadFromFile {
	/**
	 * Create a form of UploadBase depending on wpSourceType and initializes it
	 */
	public static function createFromRequest( &$request, $type = null ) {
		$handler = new self;
		$handler->initializeFromRequest( $request );
		return $handler;
	}

	function initializeFromRequest( &$request ) {
		$upload = $request->getUpload( 'wpUploadFile' );

		$desiredDestName = $request->getText( 'wpDestFile' );
		if ( !$desiredDestName ) {
			$desiredDestName = $request->getFileName( 'wpUploadFile' );
		}
		$desiredDestName = time() . '-' . $desiredDestName;

		$this->initialize( $desiredDestName, $upload );
	}
}