<?php
/**
 * 菜单扫码识别事件 'scancode_push', 'scancode_waitmsg'
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20141013
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinEventScanCode extends CWeiXinEventBase{
	/**
	 * 条码类型
	 * <li>二维码:qrcode|一维条码:barcode</li>
	 * @var string
	 */
	protected $_sScanType = null;
	/**
	 * 二维码对应的字符串信息
	 * @var string
	 */
	protected $_sScanResult = null;
	/**
	 * 初始化私有参数
	 * @param array $aXml XML结构数组
	 * @see CWeiXinResponse::initParam()
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		if (isset($aXml['scancodeinfo'])){
		$this->_sScanType = $aXml['scancodeinfo']['scantype']['C'];
		$this->_sScanResult = $aXml['scancodeinfo']['scanresult']['C'];
		}
	}
}
?>