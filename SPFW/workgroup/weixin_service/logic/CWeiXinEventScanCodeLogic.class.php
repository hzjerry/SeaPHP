<?php
/**
 * 菜单扫码识别事件响应处理业务逻辑
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20141013
 * @package SPFW.workgroup.weixin_service.logic
 * @final
 */
final class CWeiXinEventScanCodeLogic extends CWeiXinEventScanCode{
	/**
	 * @return CWeiXinReply
	 * @see CWeiXinResponse::doLogic()
	*/
	public function doLogic(){
		$aRet = new CWeiXinReplyText($this);
		$aRet->setMsg('编码类型:'. $this->_sScanType .
			'; 解码内容：['. $this->_sScanResult .']'.
			'; EventKey：['. $this->_sEventKey .']'
		); //设置回复内容
		return $aRet;
	}
}
?>