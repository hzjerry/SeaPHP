<?php
/**
 * [数据缓存模块]自动加载类库配置区
 *  注意：所有自动加载类必须在框架SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 *  @var array()
 * */
return array
(
'CFileCache'=> //文件缓存类 FileCache
	array('package'=>'extend.cache.lib',	'file'=>'CFileCache.class.php'),
'CMemcache'=> //分布式内存缓存类CMemcache
	array('package'=>'extend.cache.lib',	'file'=>'CMemcache.class.php'),
'ICacheEngine'=> //文件缓存引擎接口
	array('package'=>'extend.cache.lib',	'file'=>'ICacheEngine.php'),
);
?>