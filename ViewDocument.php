<?php

/**
 * Class ViewDocument
 * Simple object for handling Webpdf View Documents.
 * This class is only useful when combined with the ViewApi class.
 * 
 * @see ViewApi
 * 
 * @link http://api.webpdf365.com @endlink
 * 
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class ViewDocument {
	/**
	 * The name of this document.
	 */
	public $fileName;
	
	/**
	 * URL of the document you want to upload.
	 */
	public $fileUrl;
	
	/**
	 * Internal path of the document you want to upload.
	 */
	public $filePath;
	
	/**
	 * Byte Data of the document you want to upload.
	 */
	public $fileData = null;
	
	/**
	 * Creates a new WebPDF View Document.
	 *
	 * @params array $params
	 *  Array of document properties to set.
	 */
	public function __construct(array $params = array()) {
		foreach ($params as $key => $val) {
		  $this->{$key} = $val;
		}
	}
}