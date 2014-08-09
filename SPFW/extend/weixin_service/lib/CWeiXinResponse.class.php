<?php
/**
 * 对收到Xml请求包响应处理
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
abstract class CWeiXinResponse extends CWeiXinInfoStruct{
	/**
	 * 初始化基本参数
	 * @param array $aXml XML结构数组
	 */
	public function initParam(& $aXml){
		$this->_sCreateTime = $aXml['createtime']['C'];
		$this->_sMsgType = $aXml['msgtype']['C'];
		$this->_sToUserName = $aXml['tousername']['C'];
		$this->_sFromUserName = $aXml['fromusername']['C'];
	}
	/**
	 * 执行业务逻辑
	 * @return CWeiXinReply
	 */
	abstract public function doLogic();
}
?>