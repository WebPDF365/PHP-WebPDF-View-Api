<?php

require_once 'BaseBean.php';

/**
 * 调用上传文档API,返回处理信息类
 * 
 * @link http://api.webpdf365.com @endlink
 * 
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class DocumentBean extends BaseBean {
	/**
	 * A unique string identifying this document.
	 */
	public $docId;
	
	/**
	 * The name of this document.
	 */
	public $name;
}