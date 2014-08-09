<?php
/*
 * 自动加载类库 扩展框架的入口类文件配置
 *  注意：所有自动加载类必须在架构SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 * */
return array
(
'CWebService'=> //WebService服务框架
	array('package'=>'extend.webservice.runtime',	'file'=>'CWebService.class.php'),
'CWebServiceProtocolView'=> //WebService协议显示模块
	array('package'=>'extend.webservice.runtime',	'file'=>'CWebServiceProtocolView.class.php'),
'CDB'=> //数据库操作对象
	array('package'=>'extend.db.runtime',	'file'=>'CDB.class.php'),
'CCache'=> //数据缓存操作类
	array('package'=>'extend.cache.runtime',	'file'=>'CCache.class.php'),
'CPrivateClas'=> //系统私有类自动加载模块
	array('package'=>'extend.private_class.runtime',	'file'=>'CPrivateClas.class.php'),
'CWebsiteEngine'=> //网站引擎框架
	array('package'=>'extend.website_engine.runtime',	'file'=>'CWebsiteEngine.class.php'),
'CMail'=> //邮件操作作对象
	array('package'=>'extend.mail.runtime',	'file'=>'CMail.class.php'),
'CAliOSS'=> //阿里OSS云存储操作模块
	array('package'=>'extend.alioss.runtime',	'file'=>'CAliOSS.class.php'),
'CAliOssInternalBridge'=> //阿里OSS云存内网转发网桥
	array('package'=>'extend.alioss.runtime',	'file'=>'CAliOssInternalBridge.class.php'),
'CSMS'=> //短信发送模块
	array('package'=>'extend.sms.runtime',	'file'=>'CSMS.class.php'),
'CWebServiceClient'=> //SeaPHP WebService 专用的Client 操作对象
	array('package'=>'extend.webservice_client.runtime',	'file'=>'CWebServiceClient.class.php'),
'CWeiXinHelper'=> //微信向导服务类(专用于处理微信的请求响应)
	array('package'=>'extend.weixin_service.runtime',	'file'=>'CWeiXinHelper.class.php'),
'CWeiXinTools'=> //微信工具类
	array('package'=>'extend.weixin_service.lib',	'file'=>'CWeiXinTools.class.php'),
);
?>