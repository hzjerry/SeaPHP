<?php
/**
 * 推送事件的基础类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinEventBase extends CWeiXinResponse{
	/**
	 * 事件类型
	 * <li>subscribe : 订阅事件</li>
	 * <li>unsubscribe : 取消订阅事件</li>
	 * <li>CLICK : 菜单点击事件</li>
	 * <li>VIEW : 点击菜单跳转链接时的事件推送</li>
	 * <li>SCAN : 用户已关注时的二维码扫描事件推送</li>
	 * <li>LOCATION : 上报地理位置事件</li>
	 * @var string
	 */
	protected $_sEvent = null;
	/**
	 * 事件KEY值
	 * @var string
	 */
	protected $_sEventKey = null;
	/**
	 * 初始化基本参数
	 * @param array $aXml XML结构数组
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		$this->_sEvent = $aXml['event']['C'];
		if (isset($aXml['eventkey']))
			$this->_sEventKey = $aXml['eventkey']['C'];
	}
}
?>