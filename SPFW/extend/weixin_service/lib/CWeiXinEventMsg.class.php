<?php
/**
 * 一般信息类事件的响应处理
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinEventMsg extends CWeiXinEventBase{
	/**
	 * 事件KEY值
	 * @var string
	 */
	protected $_sEventKey = null;
	/**
	 * 二维码的ticket
	 * <li>id可用来换取二维码图片</li>
	 * @var string
	 */
	protected $_sTicket = null;
	/**
	 * 初始化私有参数
	 * @param array $aXml XML结构数组
	 * @see CWeiXinResponse::initParam()
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		if (isset($aXml['eventkey']))
			$this->_sEventKey = $aXml['eventkey']['C'];
		if (isset($aXml['ticket']))
			$this->_sTicket = $aXml['ticket']['C'];
	}
}
?>