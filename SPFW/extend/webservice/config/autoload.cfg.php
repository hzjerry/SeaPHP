<?php
/**
 * [WebService框架的]自动加载类库配置区
 *  注意：所有自动加载类必须在框架SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 *  @var array()
 * */
return array
(
'CWebServiceApiLogic'=> //Web Service API Logic base class
	array('package'=>'extend.webservice.lib',	'file'=>'CWebServiceApiLogic.class.php'),
'CXmlArrayConverGET'=> //Xml结构数组与Http的GET请求数据转换
	array('package'=>'extend.webservice.lib',	'file'=>'CXmlArrayConverGET.class.php'),
'CJsonArrayConver'=> //Json与Php的XML结构数组双向映射处理
	array('package'=>'extend.webservice.lib',	'file'=>'CJsonArrayConver.class.php'),
'IProtocolView'=> //WebService的API接口服务协议展示接口
	array('package'=>'extend.webservice.lib',	'file'=>'IProtocolView.php'),
);
?>