<?php
/**
 * 微信工具类
 * <li>纯静态类，封装了对微信操作的API</li>
 * <li>注意必须使用php5.4+，否则json_encode( ,JSON_UNESCAPED_UNICODE)无法使用</li>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
final class CWeiXinTools{
	static $aSAFE_CHAR = array('\\'=>'\\\\', '/'=>'\\/', '"'=>'\\"', "\t"=>'\\t', "\f"=>'\\f', "\r"=>'\\r', "\n"=>'\\n');
	/**
	 * 获取AccessToken
	 * @param string $sAppid 第三方用户唯一凭证
	 * @param string $sSecret 第三方用户唯一凭证密钥
	 * @return null||array()
	 * <li>('state'=>'true', 'token'=>'', 'exp'=>'') 通信成功</li>
	 * <li>('state'=>false, 'code'=>'', 'msg'=>'') 通信失败</li>
	 * @see http://mp.weixin.qq.com/wiki/index.php?title=%E8%8E%B7%E5%8F%96access_token
	 */
	static public function getAccessToken($sAppid, $sSecret){
		$sUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={@appid}&secret={@secret}';
		$o = new CPostHttp();
		if ($o->getPage(strtr($sUrl, array('{@appid}'=>$sAppid, '{@secret}'=>$sSecret)))){
			$aRet = json_decode($o->getContent(), true);
			unset($o);
			if (isset($aRet['access_token'])){ //成功取得Token
				return array('state'=>true, 'token'=>$aRet['access_token'], 'exp'=>intval($aRet['expires_in']));
			}else{ //遇到错误
				return array('state'=>false, 'code'=>$aRet['errcode'], 'msg'=>intval($aRet['errmsg']));
			}
		}
		return null;
	}
	/**
	 * 发送客服文字消息
	 * @param string $sAccessToken 授权ID
	 * <li>使用CWeiXinTools::getAccessToken()获取</li>
	 * @param string $sOPENID 接收者的sOPENID
	 * @param string $sMsg 文字内容
	 * @return null|array('errcode'=>'0', 'errmsg'=>'ok')
	 * <li>null表示通信失败</li>
	 * <li>errcode:0表示发送成功</li>
	 * <li>errcode:非0 表示有错误,参照:<a href="http://mp.weixin.qq.com/wiki/index.php?title=%E5%85%A8%E5%B1%80%E8%BF%94%E5%9B%9E%E7%A0%81%E8%AF%B4%E6%98%8E">to web</a></li>
	 * @see http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E5%AE%A2%E6%9C%8D%E6%B6%88%E6%81%AF
	 */
	static public function sendServiceTextMsg($sAccessToken, $sOPENID, $sMsg){
		static $sJson = '{"touser":"{@openid}","msgtype":"text","text":{"content":"{@msg}"}}';
		$sUrl = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={@token}';
		$o = new CPostHttp();
		$o->setPostData(strtr($sJson, array('{@openid}'=>$sOPENID, '{@msg}'=>strtr($sMsg, self::$aSAFE_CHAR)))); //设置数据包
		if ($o->post(strtr($sUrl, array('{@token}'=>$sAccessToken)))){
			return json_decode($o->getContent(), true);
			unset($o);
		}else
			return null;
	}
	/**
	 * 发送菜单设置数据包
	 * <li>注意：设置后24小时后生效，如果要立即生效先删除公众号再添加</li>
	 * @param string $sAccessToken 访问授权码
	 * @param array $aMenu 菜单结构体
	 * <li>array('button'=>array(array('type'=>'click', 'name'=>'歌曲', 'key'=>'xxxxxx'),...))</li>
	 * @return null|array('errcode'=>'0', 'errmsg'=>'ok')
	 * <li>null表示通信失败</li>
	 * <li>errcode:0表示发送成功</li>
	 * <li>errcode:非0 表示有错误,参照:<a href="http://mp.weixin.qq.com/wiki/index.php?title=%E5%85%A8%E5%B1%80%E8%BF%94%E5%9B%9E%E7%A0%81%E8%AF%B4%E6%98%8E">to web</a></li>
	 * @see http://mp.weixin.qq.com/wiki/index.php?title=%E8%87%AA%E5%AE%9A%E4%B9%89%E8%8F%9C%E5%8D%95%E5%88%9B%E5%BB%BA%E6%8E%A5%E5%8F%A3
	 * @example<pre>
		$aMenu = array('button'=>array(
			array('type'=>'click', 'name'=>'装备报修', 'key'=>'xxxxxx'),
			array('type'=>'click', 'name'=>'维修员登录', 'key'=>'zzzzz'),
			array('name'=>'工具', 'sub_button'=>array(array('type'=>'view', 'name'=>'搜索', 'url'=>'http://www.soso.com/'))),
			 ));
	 * </pre>
	 */
	static public function sendMenu($sAccessToken, $aMenu){
		$sUrl = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token={@token}';
		$o = new CPostHttp();
		$o->setPostData(json_encode($aMenu, JSON_UNESCAPED_UNICODE)); //设置数据包JSON_UNESCAPED_UNICODE
		if ($o->post(strtr($sUrl, array('{@token}'=>$sAccessToken)))){
			return json_decode($o->getContent(), true);
			unset($o);
		}else
			return null;
	}
	/**
	 * 删除菜单设置
	 * <li>注意：设置后24小时后生效，如果要立即生效先删除公众号再添加</li>
	 * @param string $sAccessToken 访问授权码
	 * @return null|array('errcode'=>'0', 'errmsg'=>'ok')
	 * <li>null表示通信失败</li>
	 * <li>errcode:0表示删除成功</li>
	 * <li>errcode:非0 表示有错误,参照:<a href="http://mp.weixin.qq.com/wiki/index.php?title=%E5%85%A8%E5%B1%80%E8%BF%94%E5%9B%9E%E7%A0%81%E8%AF%B4%E6%98%8E">to web</a></li>
	 */
	static public function removeMenu($sAccessToken){
		$sUrl = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={@token}';
		$o = new CPostHttp();
		if ($o->getPage(strtr($sUrl, array('{@token}'=>$sAccessToken)))){
			return json_decode($o->getContent(), true);
			unset($o);
		}else
			return null;
	}
	/**
	 * 创建二维码Ticket
	 * <li>获得tickid后使用这个链接显示二维码图片：https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=TICKET </li>
	 * @param string $sAccessToken 访问授权码
	 * @param int $iSceneID 场景值ID
	 * <li>临时二维码:为32位非0整型</li>
	 * <li>永久二维码:为1~100000整型</li>
	 * @param int $iExpire 有效期(1-1800秒以内为临时二维码, -1表示永久二维码)
	 * @return null|array('state'=>bool, 'ticket'=>'', 'errcode'=>'...', 'errmsg'=>'...')
	 * <li>state=true表示创建成功，ticket:二维码凭据ID</li>
	 * <li>null表示通信失败</li>
	 * <li>state=false 表示遇到错误 errcode,errmsg参数参照:<a href="http://mp.weixin.qq.com/wiki/index.php?title=%E5%85%A8%E5%B1%80%E8%BF%94%E5%9B%9E%E7%A0%81%E8%AF%B4%E6%98%8E">to web</a></li>
	 */
	static public function createTicket($sAccessToken, $iSceneID, $iExpire=1800){
		$sUrl = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={@token}';
		if ($iExpire > 0){ //临时二维码
			$aParam = array('expire_seconds'=>$iExpire, 'action_name'=>'QR_SCENE',
					'action_info'=>array('scene'=>array('scene_id'=>$iSceneID)));
		}else //永久二维码
			$aParam = array('action_name'=>'QR_SCENE', 'action_info'=>array('scene'=>array('scene_id'=>$iSceneID)));
		$o = new CPostHttp();
		$o->setPostData(json_encode($aParam));
		if ($o->post(strtr($sUrl, array('{@token}'=>$sAccessToken)))){
			$aRet = json_decode($o->getContent(), true);
			unset($o, $aParam);
			if (isset($aRet['ticket'])) //生成成功
				return array('state'=>true, 'ticket'=>$aRet['ticket']);
			else //遇到错误
				return array('state'=>false, 'errcode'=>$aRet['errcode'], 'errmsg'=>$aRet['errmsg']);
		}else
			return null;
	}
}
?>