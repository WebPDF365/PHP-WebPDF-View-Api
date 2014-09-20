<?php

require_once 'BaseBean.php';
require_once 'SessionInfo.php';

/**
 * Call Get session information API based document ID, return process information class.
 *
 * @link http://api.webpdf365.com @endlink
 *
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class SessionInfoListBean extends BaseBean {
    /**
     * (array(SessionInfo,....)) Session information list
     */
    public $sessionList;
}