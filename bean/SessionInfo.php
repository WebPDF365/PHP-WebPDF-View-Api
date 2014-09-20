<?php

/**
 * Session information class.
 * 
 * @link http://api.webpdf365.com @endlink
 * 
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class SessionInfo {
    /**
     * Session ID
     */
    public $sessionId;

    /**
     * Document ID
     */
    public $docId;

    /**
     * (long) Unit: minute; interval time from create to expiry
     */
    public $expiry;

    /**
     * (long) Session expiry date
     */
    public $expiryDate;

    /**
     * Session create date
     */
    public $createDate;

    /**
     * (boolean) Whether sessionId is long-term valid, default is false
     */
    public $infinite;
}