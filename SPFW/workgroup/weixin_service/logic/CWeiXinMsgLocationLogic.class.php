<?php
/**
 * 推送消息的地理位置类消息响应的业务逻辑
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140809
 * @package SPFW.workgroup.weixin_service.logic
 * @final
 */
final class CWeiXinMsgLocationLogic extends CWeiXinMsgLocation{
	/**
	 * @return CWeiXinReply
	 * @see CWeiXinResponse::doLogic()
	*/
	public function doLogic(){
		$aRet = new CWeiXinReplyText($this);
		$aRet->setMsg('您的坐标是Lat:'. $this->_fLocation_X .', Long:'. $this->_fLocation_Y); //设置回复内容
		return $aRet;
	}
}
?>