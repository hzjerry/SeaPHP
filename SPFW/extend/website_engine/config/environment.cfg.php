<?php
/**
 * Website Engine框架的配置文件
 *
 * @var array
 * */
return array(
	//网站引擎工作区业务逻辑根目录，里面的每个顶级子目录为一个工作区
	'logic_workgroup'	=> 'workgroup.website_engine.logic',
	//网站引擎静态缓存目录
	'static_cache'		=> 'cache.website_engine.static_cache',
	//smarty模板目录
	'smarty_tpl_workgroup'=> 'workgroup.website_engine.smarty_tpl',
	//smarty模板编译文件与缓存文件目录
	'smarty_cache'		=> 'cache.website_engine.smarty_cache',
	//smarty模板代码定界符
	'smarty_delimiter'	=> array('<%', '%>'),
	//smarty模板的强制编译(注意:在生产环境下,不要开启. 只有在开发环境下才开启)
	'smarty_force_compile'	=> false,
);
?>