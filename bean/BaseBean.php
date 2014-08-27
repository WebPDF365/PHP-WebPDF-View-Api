<?php

/**
 * 调用API,返回处理信息基础类
 * 
 * @link http://api.webpdf365.com @endlink
 * 
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
 class BaseBean {
	/**
	 * int, 错误信息代码，0:成功，其它:发生错误
	 */
	public $error;

	/**
	 * 对应返回的详细信息描述
	 */
	public $msg;
 }