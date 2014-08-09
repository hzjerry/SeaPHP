<?php
/**
 * 推送消息的地理位置类消息响应处理
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinMsgLocation extends CWeiXinMsgBase{
	/**
	 * 维度
	 * @var float
	 */
	protected $_fLocation_X = null;
	/**
	 * 经度
	 * @var float
	 */
	protected $_fLocation_Y = null;
	/**
	 * 缩放大小
	 * @var int
	 */
	protected $_iScale = null;
	/**
	 * 地理位置信息
	 * @var string
	 */
	protected $_sLabel = null;
	/**
	 * 初始化私有参数
	 * @param array $aXml XML结构数组
	 * @see CWeiXinResponse::initParam()
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		$this->_fLocation_X = floatval($aXml['location_x']['C']);
		$this->_fLocation_Y = floatval($aXml['location_y']['C']);
		$this->_iScale = intval($aXml['scale']['C']);
		$this->_sLabel = $aXml['label']['C'];
	}
}
?>