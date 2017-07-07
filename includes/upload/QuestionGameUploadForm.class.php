<?php

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
