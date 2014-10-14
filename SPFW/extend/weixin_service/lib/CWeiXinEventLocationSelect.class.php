<?php
/**
 * 菜单弹出地理位置选择器的事件推送
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20141013
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinEventLocationSelect extends CWeiXinEventBase{
	/**
	 * X坐标信息(维度)
	 * @var float
	 */
	protected $_fLocation_X = null;
	/**
	 * Y坐标信息(经度)
	 * @var float
	 */
	protected $_fLocation_Y = null;
	/**
	 * 精度
	 * @var int
	 */
	protected $_iScale = null;
	/**
	 * 地理位置的字符串信息
	 * @var int
	 */
	protected $_sLabel = null;
	/**
	 * 朋友圈POI的名字
	 * @var int
	 */
	protected $_sPoiname = null;
	/**
	 * 初始化私有参数
	 * @param array $aXml XML结构数组
	 * @see CWeiXinResponse::initParam()
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		if (isset($aXml['sendlocationinfo'])){
			$this->_fLocation_X = doubleval($aXml['sendlocationinfo']['location_x']['C']);
			$this->_fLocation_Y = doubleval($aXml['sendlocationinfo']['location_y']['C']);
			$this->_iScale = intval($aXml['sendlocationinfo']['scale']['C']);
			$this->_sLabel = $aXml['sendlocationinfo']['label']['C'];
			$this->_sPoiname = $aXml['sendlocationinfo']['poiname']['C'];
		}
	}
}
?>