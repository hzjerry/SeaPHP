<?php
/**
 * 推送消息的文字类消息响应处理
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinMsgText extends CWeiXinMsgBase{
	/**
	 * 收到的消息
	 * @var string
	 */
	protected $_sContent = null;
	/**
	 * 初始化私有参数
	 * @param array $aXml XML结构数组
	 * @see CWeiXinResponse::initParam()
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		$this->_sContent = $aXml['content']['C'];
	}
}
?>