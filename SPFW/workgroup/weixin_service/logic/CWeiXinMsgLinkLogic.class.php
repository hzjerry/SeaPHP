<?php
/**
 * 推送消息的链接类消息响应的业务逻辑
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140809
 * @package SPFW.workgroup.weixin_service.logic
 * @final
 */
final class CWeiXinMsgLinkLogic extends CWeiXinMsgLink{
	/**
	 * @return CWeiXinReply
	 * @see CWeiXinResponse::doLogic()
	*/
	public function doLogic(){
		$aRet = new CWeiXinReplyText($this);
		$aRet->setMsg('推送过来的网址：['. $this->_sUrl .']'); //设置回复内容
		return $aRet;
	}
}
?>