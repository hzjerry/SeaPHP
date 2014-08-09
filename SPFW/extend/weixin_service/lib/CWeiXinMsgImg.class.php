<?php
/**
 * 推送消息的图片类消息响应处理
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinMsgImg extends CWeiXinMsgBase{
	/**
	 * 收到的图片链接
	 * @var string
	 */
	protected $_sPicUrl = null;
	/**
	 * 图片消息媒体id
	 * <li>可以调用多媒体文件下载接口拉取数据</li>
	 * @var string
	 */
	protected $_sMediaId = null;
	/**
	 * 初始化私有参数
	 * @param array $aXml XML结构数组
	 * @see CWeiXinResponse::initParam()
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		$this->_sPicUrl = $aXml['picurl']['C'];
		if (isset($aXml['mediaid'])) //有可能不存在mediaid ?
			$this->_sMediaId = $aXml['mediaid']['C'];
	}
}
?>