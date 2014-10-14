<?php
/**
 * 菜单弹出地理位置选择器的事件推送
 * <li>location_select 将会调起发送位置功能，菜单的响应用户收不到，在用户发送位置之后，会再推送一个地理位置消息功能给用户</li>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20141014
 * @package SPFW.workgroup.weixin_service.logic
 * @final
 */
final class CWeiXinEventLocationSelectLogic extends CWeiXinEventLocationSelect{
	/**
	 * @return CWeiXinReply
	 * @see CWeiXinResponse::doLogic()
	*/
	public function doLogic(){
		$aRet = new CWeiXinReplyText($this);
		$aRet->setMsg('你的坐标是：Lat:'. $this->_fLocation_Y .
			'; Long:'. $this->_fLocation_X .
			'; 地标名称:'. $this->_sLabel .
			'; POI:'. $this->_sPoiname
		);
		return $aRet;
	}
}
?>