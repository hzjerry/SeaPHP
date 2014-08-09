<?php
/**
 * 推送消息的上报地理位置事件响应处理业务逻辑
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140809
 * @package SPFW.workgroup.weixin_service.logic
 * @final
 */
final class CWeiXinEventMsgLogic extends CWeiXinEventMsg{
	/**
	 * @return CWeiXinReply
	 * @see CWeiXinResponse::doLogic()
	*/
	public function doLogic(){
		$aRet = new CWeiXinReplyText($this);

		if ('subscribe' === $this->_sEvent){//订阅事件
			if (empty($this->_sTicket)){ //普通订阅
				$aRet->setMsg('订阅成功：OPENID:'. $this->_sFromUserName);
			}else{ //二维码扫描订阅事件
				//EventKey:qrscene_为前缀，后面为二维码的参数值
				$aRet->setMsg('二维码扫码订阅成功：OPENID:'. $this->_sFromUserName. ', EventKey:'. $this->_sEventKey);
			}
		}elseif ('unsubscribe' === $this->_sEvent){//取消订阅事件
			//这个操作可以不用回复任何内容
			$aRet->setMsg('退订成功：OPENID:'. $this->_sFromUserName);
		}elseif ('CLICK' === $this->_sEvent){//菜单点击事件
			$aRet->setMsg('点击了菜单的值：'.  $this->_sEventKey .', OPENID:'. $this->_sFromUserName);
		}elseif ('VIEW' === $this->_sEvent){//点击菜单跳转链接时的事件推送
			$aRet->setMsg('点击了菜单的网址跳转：'.  $this->_sEventKey .', OPENID:'. $this->_sFromUserName);
		}elseif ('SCAN' === $this->_sEvent){//已订阅的用户二维码扫描访问事件
			//EventKey：事件KEY值，是一个32位无符号整数，即创建二维码时的二维码scene_id
			$aRet->setMsg('二维码扫码访问：OPENID:'. $this->_sFromUserName. ', EventKey:'. $this->_sEventKey);
		}
		return $aRet;
	}
}
?>