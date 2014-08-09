<?php
/**
 * 回复处理
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinReply extends CWeiXinInfoStruct{
	/**
	 * 返回Xml结构包
	 * @return string
	 */
	abstract public function toXml();
	/**
	 * 构造函数
	 * @param CWeiXinInfoStruct $oReceive
	 */
	public function __construct($oReceive){
		$this->_sToUserName = $oReceive->_sFromUserName; //消息接收者
		$this->_sFromUserName = $oReceive->_sToUserName; //消息发送者
	}
	/**
	 * 安全的字符串处理
	 * @static
	 * @param string $sS 待处理的字符串
	 * @return string
	 */
	static public function safeS($sS){
		return strtr($sS, array('<![CDATA['=>'< ! [ CDATA [', ']]>'=>'] ] >'));
	}
}
?>