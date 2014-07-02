<?php
/**
 * [WebServiceClient框架的]自动加载类库配置区
 *  注意：所有自动加载类必须在框架SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 *  @var array()
 * */
return array
(
'IWSC_Cfg'=> //接口连接配置的接口
	array('package'=>'extend.webservice_client.lib',	'file'=>'IWSC_Cfg.php'),
'IWebServiceClientFunc'=> //接口客户端的实现逻辑接口
	array('package'=>'extend.webservice_client.lib',	'file'=>'IWebServiceClientFunc.php'),
);
?>