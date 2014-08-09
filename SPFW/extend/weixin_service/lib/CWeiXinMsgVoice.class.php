<?php
/**
 * 推送消息的语音类消息响应处理
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinMsgVoice extends CWeiXinMsgBase{
	/**
	 * 语音消息媒体id
	 * <li>可以调用多媒体文件下载接口拉取数据</li>
	 * @var string
	 */
	protected $_sMediaId = null;
	/**
	 * 语音格式
	 * <li>amr, speex</li>
	 * @var string
	 */
	protected $_sFormat = null;
	/**
	 * 语音识别结果
	 * <li>UTF8编码字符集</li>
	 * @var null|string
	 */
	protected $_sRecognition = null;
	/**
	 * 初始化私有参数
	 * @param array $aXml XML结构数组
	 * @see CWeiXinResponse::initParam()
	 */
	public function initParam(& $aXml){
		parent::initParam($aXml);
		$this->_sMediaId = $aXml['mediaid']['C'];
		$this->_sFormat = $aXml['format']['C'];
		if (isset($aXml['recognition']))
			$this->_sRecognition = $aXml['recognition']['C'];
	}
}
?>