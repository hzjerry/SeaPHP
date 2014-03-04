<?php
/**
 * [Website Engine框架的]自动加载类库配置区
 *  注意：所有自动加载类必须在框架SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 *  @var array()
 * */
return array
(
'CWebsiteModule'=> //网站引擎的页面逻辑基类
	array('package'=>'extend.website_engine.lib',	'file'=>'CWebsiteModule.class.php'),
'CSessionOperat'=> //Session操作对象
	array('package'=>'extend.website_engine.lib',	'file'=>'CSessionOperat.class.php'),
'CWebsiteSecurity'=> //网站引擎用户访问安全验证基类
	array('package'=>'extend.website_engine.lib',	'file'=>'CWebsiteSecurity.class.php'),
'CWebsiteView'=> //Website engine 视图操作类
	array('package'=>'extend.website_engine.lib',	'file'=>'CWebsiteView.class.php'),
'Smarty'=> //Website engine 视图操作类
	array('package'=>'extend.website_engine.lib.smarty3',	'file'=>'Smarty.class.php'),
);
?>