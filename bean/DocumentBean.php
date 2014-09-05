<?php

require_once 'BaseBean.php';

/**
 * Call upload document API, return process information class.
 * 
 * @link http://api.webpdf365.com @endlink
 * 
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class DocumentBean extends BaseBean {
	/**
	 * The ID of the document uploaded
	 */
	public $docId;
	
	/**
	 * File name
	 */
	public $name;
}