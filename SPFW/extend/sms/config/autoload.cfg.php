<?php
/**
 * [短信发送模块]自动加载类库配置区
 *  注意：所有自动加载类必须在框架SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 *  @var array()
 * */
return array
(
'ISmsSend'=> //短信发送接口类
	array('package'=>'extend.sms.lib',	'file'=>'ISmsSend.class.php'),
);
?>