<?php
/**
 * 推送消息的链接类消息响应处理
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinMsgLink extends CWeiXinMsgBase{
	/**
	 * 消息标题
	 * @var string
	 */
	protected $_sTitle = null;
	/**
	 * 消息描述
	 * @var string
	 */
	protected $_sDescription = null;
	/**
	 * 消息链接
	 * @var string
	 */
	protected $_sUrl = null;
	/**
	 * 初始化私有参数
	 * @param array $aXml XML结构数组
	 * @see CWeiXinResponse::initParam()
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		$this->_sTitle = $aXml['title']['C'];
		$this->_sDescription = $aXml['description']['C'];
		$this->_sUrl = $aXml['url']['C'];
	}
}
?>