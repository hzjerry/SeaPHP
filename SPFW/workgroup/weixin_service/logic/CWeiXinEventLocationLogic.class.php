<?php
/**
 * 推送消息的上报地理位置事件响应处理业务逻辑
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140809
 * @package SPFW.workgroup.weixin_service.logic
 * @final
 */
final class CWeiXinEventLocationLogic extends CWeiXinEventLocation{
	/**
	 * @return CWeiXinReply
	 * @see CWeiXinResponse::doLogic()
	*/
	public function doLogic(){
		$aRet = new CWeiXinReplyText($this);
		$aRet->setMsg('你的坐标是：Lat:'. $this->_fLatitude .' Long:'. $this->_fLongitude);
		return $aRet;
	}
}
?>