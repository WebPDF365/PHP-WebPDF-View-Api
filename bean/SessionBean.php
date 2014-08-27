<?php

require_once 'BaseBean.php';
require_once 'ViewUrl.php';

/**
 * 调用阅读文档API,返回处理信息类
 * 
 * @link http://api.webpdf365.com @endlink
 * 
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class SessionBean extends BaseBean {
	/**
	 * 新创建的session Id
	 */
	public $sessionId;
	
	/**
	 * Long :Session Id过期日期
	 */
	public $expiryDate;
	
	/**
	 * ViewUrl
	 */
	public $urls;

	/**
	 * boolean: SessionId 是否长期有效的标志…
	 */
	public $infinite;
}