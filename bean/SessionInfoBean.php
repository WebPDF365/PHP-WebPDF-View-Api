<?php

require_once 'SessionInfo.php';

/**
 * Call Get session information API based on session ID, return process information class.
 *
 * @link http://api.webpdf365.com @endlink
 *
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class SessionInfoBean extends SessionInfo {
    /**
     * (int) Error information code, 0: succeed; other: error
     */
    public $error;
}