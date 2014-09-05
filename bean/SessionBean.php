<?php

require_once 'BaseBean.php';
require_once 'ViewUrl.php';

/**
 * Call viewing document API, return process information class.
 * 
 * @link http://api.webpdf365.com @endlink
 * 
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class SessionBean extends BaseBean {
	/**
	 * New session ID
	 */
	public $sessionId;
	
	/**
	 * (Long) Session ID expiry date
	 */
	public $expiryDate;
	
	/**
	 * ViewUrl
	 */
	public $urls;

	/**
	 * (boolean) Whether sessionId is long-term valid, default is false
	 */
	public $infinite;
}