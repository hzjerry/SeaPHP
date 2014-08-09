<?php
/**
 * 微信的基础信息结构
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinInfoStruct {
	/**
	 * 目的地用户帐号
	 * @var string
	 */
	public $_sToUserName = null;
	/**
	 * 发起者用户帐号
	 * @var string
	 */
	public $_sFromUserName = null;
	/**
	 * 消息创建时间
	 * @var string
	 */
	protected $_sCreateTime = null;
	/**
	 * 消息类型
	 * @var string
	 */
	protected $_sMsgType = null;
}
?>