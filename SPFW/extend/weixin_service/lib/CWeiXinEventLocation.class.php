<?php
/**
 * 上报地理位置事件响应处理
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinEventLocation extends CWeiXinEventBase{
	/**
	 * 地理位置纬度
	 * @var float
	 */
	protected $_fLatitude = null;
	/**
	 * 地理位置经度
	 * @var float
	 */
	protected $_fLongitude = null;
	/**
	 * 地理位置精度
	 * @var float
	 */
	protected $_fPrecision = null;
	/**
	 * 初始化私有参数
	 * @param array $aXml XML结构数组
	 * @see CWeiXinResponse::initParam()
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		$this->_fLatitude = floatval($aXml['latitude']['C']);
		$this->_fLongitude = floatval($aXml['longitude']['C']);
		$this->_fPrecision = floatval($aXml['precision']['C']);
	}
}
?>