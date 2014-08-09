<?php
/**
 * 推送消息的基础类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinMsgBase extends CWeiXinResponse{
	/**
	 * 消息id 64位数字
	 * @var string
	 */
	protected $_sMsgId = null;
	/**
	 * 初始化基本参数
	 * @param array $aXml XML结构数组
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		$this->_sMsgId = $aXml['msgid']['C'];
	}
}
?>