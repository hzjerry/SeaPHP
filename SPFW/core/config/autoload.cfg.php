<?php
/*
 * 自动加载类库配置区
 *  注意：所有自动加载类必须在架构SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 * */
return array
(
'CXmlArrayNode'=> //Xml结构数组节点管理类
	array('package'=>'core.lib.base',	'file'=>'CXmlArrayNode.class.php'),

'CENV'=> //系统全局变量管理类
	array('package'=>'core.lib.sys',	'file'=>'CENV.class.php'),
'CConverCharset'=> //字符集编码转换
	array('package'=>'core.lib.sys',	'file'=>'CConverCharset.class.php'),
'IXmlJsonConverArray'=> //接口：Xml与Json转换成Php数组的双向映射处理
	array('package'=>'core.lib.sys',	'file'=>'IXmlJsonConverArray.php'),
'IExtFramework'=> //扩展模块的框架类接口
	array('package'=>'core.lib.sys',	'file'=>'IExtFramework.php'),
'CExtModule'=> //扩展功能模块的抽象类
	array('package'=>'core.lib.sys',	'file'=>'CExtModule.class.php'),
'CExtendManage'=> //Xml结构数组节点管理类
	array('package'=>'core.lib.sys',	'file'=>'CExtendManage.class.php'),
'CSeaPhpWebServiceClient'=> //Sea PHP架构的 Web Service专用客户端
	array('package'=>'core.lib.sys',	'file'=>'CSeaPhpWebServiceClient.class.php'),

'CDate'=> //时间日期处理类
	array('package'=>'core.lib.final',	'file'=>'CDate.class.php'),
'CXmlArrayConver'=> //Xml与php数组转换类
	array('package'=>'core.lib.final',	'file'=>'CXmlArrayConver.class.php'),
'CPostHttp'=> //Http Post处理类 CURL版本
	array('package'=>'core.lib.final',	'file'=>'CPostHttp.class.php'),
'CFileOperation'=> //文件操作类
	array('package'=>'core.lib.final',	'file'=>'CFileOperation.class.php'),
'CLongInt2MicoStr'=> //长整型数值微缩编码类
	array('package'=>'core.lib.final',	'file'=>'CLongInt2MicoStr.class.php'),
'CString'=> //字符串处理类
	array('package'=>'core.lib.final',	'file'=>'CString.class.php'),
'CValCheck'=> //字符串处理类
	array('package'=>'core.lib.final',	'file'=>'CValCheck.class.php'),
'CNet'=> //网络处理类
	array('package'=>'core.lib.final',	'file'=>'CNet.class.php'),
'CErrThrow'=> //网络处理类
	array('package'=>'core.lib.final',	'file'=>'CErrThrow.class.php'),
'CEncryption'=> //加密压缩传输
	array('package'=>'core.lib.final',	'file'=>'CEncryption.class.php'),

/*调试使用，正式平台上删除以下内容*/
'CJsonArrayConver'=> //Json与Php的XML结构数组双向映射处理
	array('package'=>'extend.webservice.lib',	'file'=>'CJsonArrayConver.class.php'),
'CXmlArrayConverGET'=> //Xml结构数组与Http的GET请求数据转换
	array('package'=>'extend.webservice.lib',	'file'=>'CXmlArrayConverGET.class.php'),
);
?>