<?php
/**
 * 文本类型回复对象
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
final class CWeiXinReplyText extends CWeiXinReply{
	/**
	 * 回复的消息内容
	 * @var string
	 */
	private $_sContent = null;
	/**
	 * @param CWeiXinInfoStruct $oReceive
	 * @see CWeiXinReply::__construct()
	*/
	public function __construct($oReceive){
		parent::__construct($oReceive);
	}
	/**
	 * 设置要发送的内容
	 * @param string $sMsg
	 */
	public function setMsg($sMsg){
		$this->_sContent = $sMsg;
	}
	/**
	 * @see CWeiXinReply::toXml()
	*/
	public function toXml(){
		$aBuf = array();
		$aBuf[] = '<xml>';
		$aBuf[] = ' <ToUserName><![CDATA['. parent::safeS($this->_sToUserName) .']]></ToUserName>';
		$aBuf[] = ' <FromUserName><![CDATA['. parent::safeS($this->_sFromUserName) .']]></FromUserName>';
		$aBuf[] = ' <CreateTime>'. strval(time()) .'</CreateTime>';
		$aBuf[] = ' <MsgType>text</MsgType>';
		$aBuf[] = ' <Content><![CDATA['. $this->_sContent .']]></Content>';
		$aBuf[] = '</xml>';
		return implode("\n", $aBuf);
	}
}
?>