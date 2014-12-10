<?php
/*
 * sea php 系统静态全局函数(请不要修改这个文件)
 *
 * @author Jerryli(hzjerry@gmail.com)
 * @package SPFW.core.runtime
 * @version V20130415
 * <li>V20140626 修改了microtime()函数，提高运行效率</li>
 * <li>V20140928 修改了$GLOBALS['SEA_PHP_FW_VAR_HOST']全局变量，如果以命令行方式运行SEA_PHP_FW_VAR_HOST=null</li>
 * <li>V20141210 对使用反向代理时通过HTTP_X_FORWARDED_FOR获取客户IP的方式做了优化。</li>
 */
defined('SEA_PHP_INIT') or exit('sea php framework initialization step is not valid.'); //功能:让框架必须按顺序加载
define('SEA_PHP_RUNTIME', true);  //功能:tag

//记录架构运行的开始时间
$GLOBALS['SEA_PHP_FW_VAR_BEGIN_TIMESTEMP'] = microtime(true); //启动时间保存

//加载基本静态函数
require_once($GLOBALS['SEA_PHP_FW_DIR_MACHINE_ROOT'] .
		$GLOBALS['SEA_PHP_FW_DIR_WEB_ROOT'] .
		$GLOBALS['SEA_PHP_FW_DIR_SPFW_ROOT'] .
		'/core/runtime/static_function.php');

//加载基本类
import('core.lib.final', 'dbg.class.php'); //调试信息输出类

//环境配置处理
$GLOBALS['SEA_PHP_FW_VAR_ENV'] = import('core.config', 'environment.cfg.php');
if ($GLOBALS['SEA_PHP_FW_VAR_ENV']['output_buffering'] === true) //页面缓冲初始化
	ob_start();
if (!empty($GLOBALS['SEA_PHP_FW_VAR_ENV']['timezone'])) //时间区域初始化
	date_default_timezone_set('PRC');

/*获取访问者IP;例:60.186.90.130; $GLOBALS['FW_VAR']['IP']*/
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ //存在反向代理时的IP获取
	$aTmp = explode(', ', $_SERVER["HTTP_X_FORWARDED_FOR"], 2);
	$GLOBALS['SEA_PHP_FW_VAR_IP'] = $aTmp[0]; //第一个IP为客户IP
}elseif (isset($_SERVER["HTTP_CLIENT_IP"]))
	$GLOBALS['SEA_PHP_FW_VAR_IP'] = $_SERVER["HTTP_CLIENT_IP"];
elseif (isset($_SERVER["REMOTE_ADDR"]))
	$GLOBALS['SEA_PHP_FW_VAR_IP'] = $_SERVER["REMOTE_ADDR"];
else
	$GLOBALS['SEA_PHP_FW_VAR_IP'] = "0.0.0.0";

/*服务器主机头名称;例:www.fox.cn或www.fox.cn:8080*/
if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['SERVER_PORT'])){
	$GLOBALS['SEA_PHP_FW_VAR_HOST'] =
		($_SERVER['SERVER_PORT']=='80')? $_SERVER['SERVER_NAME'] :
			$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
}else{
	$GLOBALS['SEA_PHP_FW_VAR_HOST'] = null;
}

//autoload动态加载类处理
$aAutoload = import('core.config', 'autoload.cfg.php');//载入架构的内核动态加载类配置表
$aEF = import('core.config', 'extend_framework.cfg.php');//载入动态加载的，扩展框架入口类配置表
if (!empty($aEF) && count($aEF) > 0)
	$GLOBALS['SEA_PHP_FW_AUTOLOAD'] = array_merge($aAutoload, $aEF); //将配置文件合并
else //内核加载类配置表
	$GLOBALS['SEA_PHP_FW_AUTOLOAD'] = $aAutoload;
unset($aAutoload, $aEF);
/**
 * 动态加载类核心处理函数
 * @global SEA_PHP_FW_AUTOLOAD
 * */
function seaphpAutoload($sClassName){
	$aCfg = $GLOBALS['SEA_PHP_FW_AUTOLOAD'];
	if (array_key_exists($sClassName, $aCfg)){	//找到需要加载的类，开始加载
		import($aCfg[$sClassName]['package'], $aCfg[$sClassName]['file']);
		if (!(class_exists($sClassName) || interface_exists($sClassName))){
			echo '<pre>Access to autoload, beacuse not find [', $sClassName ,'] class or interface.', "\n";
			foreach (dbg::TRACE() as $sNode)
				echo $sNode, "\n";
			echo '</pre>';
			exit(0);
		}
	}elseif (count(spl_autoload_functions()) == 1){ //当不存在其他加载类时，类不存在则强制终止，并给出错误信息
		//自动加载类为发现配置文件
		echo '<pre>Failed to autoload, class(', $sClassName, ') configuration file not found', "\n";
		foreach (dbg::TRACE() as $sNode)
			echo $sNode, "\n";
		echo '</pre>';
		exit(0);
	}
}
spl_autoload_register('seaphpAutoload'); //注册自动加载类
?>