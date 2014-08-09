<?php
/**
 * 推送消息的文字类消息响应的业务逻辑
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140809
 * @package SPFW.workgroup.weixin_service.logic
 * @final
 */
final class CWeiXinMsgTextLogic extends CWeiXinMsgText{
	/**
	 * @return CWeiXinReply
	 * @see CWeiXinResponse::doLogic()
	*/
	public function doLogic(){
		$aRet = new CWeiXinReplyText($this);
		$aRet->setMsg('欢迎您的访问。['. $this->_sContent .']'); //设置回复内容
		return $aRet;
	}
}
?>