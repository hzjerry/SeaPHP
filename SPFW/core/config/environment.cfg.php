<?php
/*
 * 系统内核的环境变量配置
 * 将会保存在$GLOBALS['SEA_PHP_FW_VAR_ENV']中，由runtime.php加载
 *
 * */
return array
(
'output_buffering'	=> true, //页面缓冲是否打开(即在架构初始化时执行ob_start()，打开输出缓冲)
'show_debug_info'	=> true, //_dbg(),_dbge()两个调试打印函数的显示输出 开关
'charset'			=> 'utf-8', //系统字符集
'timezone'			=> 'RPC', //时间区域初始化(=null表示使用系统默认设置)
);
?>