<?php
/**
 * [WeiXinService框架的]自动加载类库配置区
 *  注意：所有自动加载类必须在框架SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 *  @var array()
 * */
return array
(
'CWeiXinInfoStruct'=> //微信的基础信息结构
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinInfoStruct.class.php'),
'CWeiXinReply'=> //回复处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinReply.class.php'),
'CWeiXinReplyNews'=> //图文类型回复对象
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinReplyNews.class.php'),
'CWeiXinReplyText'=> //文本类型回复对象
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinReplyText.class.php'),
'CWeiXinReplyMusic'=> //音乐类型回复对象
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinReplyMusic.class.php'),
'CWeiXinResponse'=> //对收到Xml请求包响应处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinResponse.class.php'),
'CWeiXinMsgBase'=> //推送消息的基础类
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinMsgBase.class.php'),
'CWeiXinMsgText'=> // 推送消息的文字类消息响应处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinMsgText.class.php'),
'CWeiXinMsgImg'=> // 推送消息的图片类消息响应处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinMsgImg.class.php'),
'CWeiXinMsgVoice'=> // 推送消息的语音类消息响应处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinMsgVoice.class.php'),
'CWeiXinMsgVideo'=> // 推送消息的视频类消息响应处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinMsgVideo.class.php'),
'CWeiXinMsgLocation'=> // 推送消息的地理位置类消息响应处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinMsgLocation.class.php'),
'CWeiXinMsgLink'=> // 推送消息的链接类消息响应处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinMsgLink.class.php'),
'CWeiXinEventBase'=> // 推送事件的基础类
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinEventBase.class.php'),
'CWeiXinEventLocation'=> // 上报地理位置事件响应处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinEventLocation.class.php'),
'CWeiXinEventMsg'=> // 一般信息类事件的响应处理
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinEventMsg.class.php'),
'CWeiXinEventScanCode'=> // 菜单扫码识别事件
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinEventScanCode.class.php'),
'CWeiXinEventLocationSelect'=> // 菜单弹出地理位置选择器的事件推送
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinEventLocationSelect.class.php'),
);
?>