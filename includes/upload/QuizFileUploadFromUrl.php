<?php
/**
 * Like QuizFileUpload but for uploads from a URL...
 */
class QuizFileUploadFromUrl extends UploadFromUrl {
	/**
	 * Create a form of UploadBase depending on wpSourceType and initializes it
	 */
	public static function createFromRequest( &$request, $type = null ) {
		$handler = new self;
		$handler->initializeFromRequest( $request );
		return $handler;
	}

	function initializeFromRequest( &$request ) {
		$desiredDestName = $request->getText( 'wpDestFile' );
		if ( !$desiredDestName ) {
			$desiredDestName = $request->getText( 'wpUploadFileURL' );
			// Trim down the user-supplied target URL a bit so that
			// https://upload.wikimedia.org/wikipedia/commons/b/b5/1dayoldkitten.JPG
			// becomes "1dayoldkitten.JPG" rather than
			// "https---upload.wikimedia.org-wikipedia-commons-b-b5-1dayoldkitten.JPG"
			// because that's quite a mouthful of a file name...
			if ( preg_match( '/\//', $desiredDestName ) ) {
				$arr = explode( '/', $desiredDestName );
				$desiredDestName = end( $arr );
			}
		}
		$this->initialize(
			// This is one of the reasons why this class exists: to prefix the file name w/ the current time
			time() . '-' . $desiredDestName,
			trim( $request->getVal( 'wpUploadFileURL' ) )
		);
	}

	public function doStashFile( User $user = null ) {
		return parent::doStashFile( $user );
	}
}
