<?php

use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;

class QuestionGameUploadForm extends UploadForm {
	protected $mWatch;
	protected $mForReUpload;
	protected $mSessionKey;
	protected $mHideIgnoreWarning;
	protected $mDestWarningAck;
	protected $mDestFile;

	protected $mSourceIds;

	public function __construct( $options = [] ) {
		$this->mWatch = !empty( $options['watch'] );
		$this->mForReUpload = !empty( $options['forreupload'] );
		$this->mSessionKey = $options['sessionkey'] ?? '';
		$this->mHideIgnoreWarning = !empty( $options['hideignorewarning'] );
		$this->mDestWarningAck = !empty( $options['destwarningack'] );

		$this->mDestFile = $options['destfile'] ?? '';

		$sourceDescriptor = $this->getSourceSection();
		$descriptor = $sourceDescriptor
			+ $this->getDescriptionSection()
			+ $this->getOptionsSection();

		HTMLForm::__construct( $descriptor, $this->getContext(), 'upload' );

		# Set some form properties
		$this->setSubmitText( $this->msg( 'uploadbtn' )->text() );
		$this->setSubmitName( 'wpUpload' );
		$this->setSubmitTooltip( 'upload' );
		$this->setId( 'mw-upload-form' );

		# Build a list of IDs for JavaScript insertion
		$this->mSourceIds = [];
		foreach ( $sourceDescriptor as $key => $field ) {
			if ( !empty( $field['id'] ) ) {
				$this->mSourceIds[] = $field['id'];
			}
		}
	}

	public function displayForm( $submitResult ) {
		parent::displayForm( $submitResult );
		$this->getContext()->getOutput()->setPreventClickjacking( false );
	}

	/**
	 * Wrap the form innards in an actual <form> element
	 * This is here because HTMLForm's default wrapForm() is so stupid that it
	 * doesn't let us add the onsubmit attribute...oh yeah, and because using
	 * $wgOut->addInlineScript in that addUploadJS() function doesn't work,
	 * either
	 *
	 * @param string $html HTML contents to wrap.
	 * @return string Wrapped HTML.
	 */
	public function wrapForm( $html ) {
		# Include a <fieldset> wrapper for style, if requested.
		if ( $this->mWrapperLegend !== false ) {
			$html = Html::openElement( 'fieldset' ) . "\n" .
				Html::element( 'legend', [], $this->mWrapperLegend ) . "\n" .
				$html . "\n" .
				Html::closeElement( 'fieldset' ) . "\n";
		}
		# Use multipart/form-data
		$encType = $this->mUseMultipart
			? 'multipart/form-data'
			: 'application/x-www-form-urlencoded';
		# Attributes
		$attribs = [
			'action'  => $this->getTitle()->getFullURL(),
			'method'  => 'post',
			'class'   => 'visualClear',
			'enctype' => $encType,
			'onsubmit' => 'submitForm()', // added
			'id' => 'upload', // added
			'name' => 'upload' // added
		];

		// fucking newlines...
		return "<script type=\"text/javascript\">
	function submitForm() {
		var valueToCheck = document.getElementById( 'wpUploadFile' ).value;
		if ( valueToCheck != '' ) {
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
			if ( !( document.getElementById( 'wpSourceTypeurl' ) && document.getElementById( 'wpSourceTypeurl' ).checked ) ) {
				// textError method is gone and I can't find it anywhere...
				alert( '" . str_replace( "\n", ' ', wfMessage( 'emptyfile' )->escaped() ) . "' );
				return false;
			} else {
				// wpSourceTypeurl *is* set, so we must be trying to use the upload-by-URL feature
				if ( window.parent.document.getElementById( 'old_correct' ) ) {
					window.parent.QuizGame.completeImageUpload();
				} else {
					// This is what we want to call when we're creating a brand
					// new quiz
					window.parent.QuizGame.welcomePage_completeImageUpload();
				}
			}
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
			return [
				'wpSessionKey' => [
					'type' => 'hidden',
					'default' => $this->mSessionKey,
				],
				'wpSourceType' => [
					'type' => 'hidden',
					'default' => 'Stash',
				],
			];
		}

		$canUploadByUrl = UploadFromUrl::isEnabled() && $this->getUser()->isAllowed( 'upload_by_url' );
		$radio = $canUploadByUrl;
		$selectedSourceType = strtolower( $this->getRequest()->getText( 'wpSourceType', 'File' ) );

		$descriptor = [];
		$descriptor['UploadFile'] = [
			'class' => UploadSourceField::class,
			'section' => 'source',
			'type' => 'file',
			'id' => 'wpUploadFile',
			'label-message' => 'sourcefilename',
			'upload-type' => 'File',
			'radio' => &$radio,
			// help removed, we don't need any tl,dr on this mini-upload form
			'checked' => $selectedSourceType == 'file',
		];
		if ( $canUploadByUrl ) {
			$descriptor['UploadFileURL'] = [
				'class' => UploadSourceField::class,
				'section' => 'source',
				'id' => 'wpUploadFileURL',
				'radio-id' => 'wpSourceTypeurl',
				'label-message' => 'sourceurl',
				'upload-type' => 'url',
				'radio' => &$radio,
				'checked' => $selectedSourceType == 'url',
			];
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
		$descriptor = [
			'DestFile' => [
				'type' => 'hidden',
				'id' => 'wpDestFile',
				'size' => 60,
				'default' => $this->mDestFile,
				# FIXME: hack to work around poor handling of the 'default' option in HTMLForm
				'nodata' => strval( $this->mDestFile ) !== '',
				'readonly' => true // users do not need to change the file name; normally this is true only when reuploading
			]
		];

		global $wgUseCopyrightUpload;
		if ( $wgUseCopyrightUpload ) {
			$descriptor['UploadCopyStatus'] = [
				'type' => 'text',
				'section' => 'description',
				'id' => 'wpUploadCopyStatus',
				'label-message' => 'filestatus',
			];
			$descriptor['UploadSource'] = [
				'type' => 'text',
				'section' => 'description',
				'id' => 'wpUploadSource',
				'label-message' => 'filesource',
			];
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
		$descriptor = [];

		$descriptor['wpDestFileWarningAck'] = [
			'type' => 'hidden',
			'id' => 'wpDestFileWarningAck',
			'default' => $this->mDestWarningAck ? '1' : '',
		];

		if ( $this->mForReUpload ) {
			$descriptor['wpForReUpload'] = [
				'type' => 'hidden',
				'id' => 'wpForReUpload',
				'default' => '1',
			];
		}

		return $descriptor;
	}

	protected function addUploadJS() {
		// Intentionally empty
	}
}
