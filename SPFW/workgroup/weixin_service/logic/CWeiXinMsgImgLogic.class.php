<?php
/**
 * 推送消息的图片类消息响应的业务逻辑
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140809
 * @package SPFW.workgroup.weixin_service.logic
 * @final
 */
final class CWeiXinMsgImgLogic extends CWeiXinMsgImg{
	/**
	 * @return CWeiXinReply
	 * @see CWeiXinResponse::doLogic()
	*/
	public function doLogic(){
		$aRet = new CWeiXinReplyNews($this);
		$aRet->addNode('网站首页', '内容介绍', 'http://img.dns.com.cn/common/logo.gif');
		$aRet->addNode('功能强劲', '全能MYDNS功能', 'http://img.dns.com.cn/domain/ymzc_02.jpg', 'http://blog.aliyun.com/1483');
		$aRet->addNode('全面安全保障', '域名锁定功能', 'http://img.dns.com.cn/domain/ymzc_03.jpg', 'http://help.aliyun.com/');
		$aRet->addNode('一站式服务', '域名到期可以自动续费', 'http://img.dns.com.cn/domain/ymzc_04.jpg', 'http://promotion.aliyun.com/');
		$aRet->addNode('随时候命', '7*24小时为您服务', 'http://img.dns.com.cn/domain/ymzc_05.jpg','http://www.open-open.com/');
		return $aRet;
	}
}
?>