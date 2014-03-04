<?php
/* Prompt：
 *     SeaPhp架构Web根，如果在二级目录下，则需要在入口文件中加入这句定义，并修改其根目录值
 *     注意：前后都必须加上'/'
 * Code:
 *     define('SEA_PHP_ROOT', '/SeaPhp/');
 * */


/* Prompt：
 *     SeaPhp架构的根目录的名称，如果修改了架构根目录的名称，则需要在入口文件中加入这句定义，并修改其根目录值
 *     注意：前后都不要出现'/'
 * Code:
 *     define('SEA_PHP_SPFW_ROOT', 'SPFW');
 * */


/* Prompt：
 *     SeaPhp架构的本机访问绝对路径，如果使用命令行方式执行(非Web代理方式执行)，则需要加入这句定义，并修改本机的绝对根路径
 *     注意：末尾不要带 '/'
 * Code:
 *     define('SEA_PHP_MACHINE_ROOT', 'E:/webroot');
 * */



/**
 * sea php 架构初始化 (请不要修改本文件)
 *
 * @author Jerryli(hzjerry@gmail.com)
 * @version V20130409
 * @package SPFW
 * @global SEA_PHP_FW_DIR_MACHINE_ROOT, SEA_PHP_FW_DIR_WEB_ROOT, SEA_PHP_FW_DIR_SPFW_ROOT
 */

define('SEA_PHP_INIT', true); //功能:tag
if (defined('SEA_PHP_MACHINE_ROOT'))
	$GLOBALS['SEA_PHP_FW_DIR_MACHINE_ROOT'] = SEA_PHP_MACHINE_ROOT;
else
	$GLOBALS['SEA_PHP_FW_DIR_MACHINE_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
/*---*/
if (defined('SEA_PHP_ROOT'))
{
	$sWebRoot = SEA_PHP_ROOT;
	if ($sWebRoot{0} !== '/' && $sWebRoot{0} !== '\\')
		$sWebRoot = '/'. $sWebRoot;
	if (!strrchr($sWebRoot, '/') && !strrchr($sWebRoot, '\\'))
		$sWebRoot .= '/';
	$GLOBALS['SEA_PHP_FW_DIR_WEB_ROOT'] = $sWebRoot;
	unset($sWebRoot);
}
else
	$GLOBALS['SEA_PHP_FW_DIR_WEB_ROOT'] = '/';
/*---*/
if (defined('SEA_PHP_SPFW_ROOT'))
	$GLOBALS['SEA_PHP_FW_DIR_SPFW_ROOT'] = SEA_PHP_SPFW_ROOT;
else
	$GLOBALS['SEA_PHP_FW_DIR_SPFW_ROOT'] = 'SPFW';

/*加载运行时*/
require_once($GLOBALS['SEA_PHP_FW_DIR_MACHINE_ROOT'] .
			 $GLOBALS['SEA_PHP_FW_DIR_WEB_ROOT'] .
			 $GLOBALS['SEA_PHP_FW_DIR_SPFW_ROOT'] .
			 '/core/runtime/runtime.php');
?>