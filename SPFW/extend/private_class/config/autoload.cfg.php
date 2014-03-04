<?php
/**
 * [数据缓存模块]自动加载类库配置区
 *  注意：所有自动加载类必须在框架SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 *  @var array()
 * */
return array
(
'CApkResolve'=> //APK包文件，解析类(Android项目专用)
	array('package'=>'extend.private_class.lib',	'file'=>'CApkResolve.class.php'),
);
?>