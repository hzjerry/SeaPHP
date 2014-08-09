<?php
/**
 * 推送消息的语音类消息响应的业务逻辑
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140809
 * @package SPFW.workgroup.weixin_service.logic
 * @final
 */
final class CWeiXinMsgVoiceLogic extends CWeiXinMsgVoice{
	/**
	 * @return CWeiXinReply
	 * @see CWeiXinResponse::doLogic()
	*/
	public function doLogic(){
		$aRet = new CWeiXinReplyMusic($this);
		$aRet->setMsg('疯狂青蛙', '手机铃声', 'http://mp3a.jiuku.com:1234/mp3/184/183203.mp3');
		return $aRet;
	}
}
?>