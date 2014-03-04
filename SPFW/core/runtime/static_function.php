<?php
/*
 * sea php 系统静态全局函数(请不要修改这个文件)
 *
 * @author Jerryli(hzjerry@gmail.com)
 * @package SPFW.core.runtime
 * @version V20130409
 */
defined('SEA_PHP_RUNTIME') or exit('sea php framework initialization step is not valid.'); //功能:让框架必须按顺序加载

/**
 * 返回项目的绝对根路径
 *
 * @return string 架构的所在目录的绝对根路径（即项目机器路径）<br/>
 * 返回值中，末尾不带'/'需要自己添加
 * @global SEA_PHP_FW_DIR_MACHINE_ROOT
 */
function getMAC_ROOT()
{
	static $sPath = null;
	if (is_null($sPath))
	{	//初始化根路径
		$sPath = $GLOBALS['SEA_PHP_FW_DIR_MACHINE_ROOT'];
	}
	return $sPath;
}

/**
 * 返回当前项目的相对根目录
 *
 * @return string 网站项目的相对根路径（即Web的'/'起始位置）
 * @global SEA_PHP_FW_DIR_WEB_ROOT
 */
function getWEB_ROOT()
{
	static $sPath = null;
	if (is_null($sPath))
	{	//初始化根路径
		$sPath = $GLOBALS['SEA_PHP_FW_DIR_WEB_ROOT'];
	}
	return $sPath;
}

/**
 * 返回架构的相对根目录
 *
 * @return string 框架的所在目录的绝对路径（即以SPFW目录内为起始位置）
 * @global SEA_PHP_FW_DIR_WEB_ROOT, SEA_PHP_FW_DIR_SPFW_ROOT
 */
function getFW_ROOT()
{
	static $sPath = null;
	if (is_null($sPath))
	{	//初始化根路径
		$sPath = $GLOBALS['SEA_PHP_FW_DIR_WEB_ROOT'] . $GLOBALS['SEA_PHP_FW_DIR_SPFW_ROOT'] .'/';
	}
	return $sPath;
}

/**
 * 文件导入含函数
 *   备注：以框架根目录SPFW为起始地址
 *   1、如果被包含的文件有return 则函数直接返回这个值
 *   2、如果包含的为类文件，且使用了$sRunClass参数，则函数会new这个类并返回类对象
 *
 * @author Jerryli(hzjerry@gmail.com)
 * @param string $sPackage 类Java的包地址，以SPFW目录为根 （例:core.lib.sys）
 * @param string $sFilename 需要包含的文件名称
 * @param string $sRunClass 包含后需要执行的类名
 * @return mixed|null
 * @global SEA_PHP_FW_DIR_MACHINE_ROOT, SEA_PHP_FW_DIR_WEB_ROOT, SEA_PHP_FW_DIR_SPFW_ROOT
 */
function import($sPackage, $sFilename, $sRunClass=null)
{
	$sLoadFilePath = getMAC_ROOT() . getFW_ROOT() . str_replace('.', '/', $sPackage) .'/' . $sFilename;
	/*不做文件是否存在的检查，遇到错误让系统直接报错*/
	if (empty($sRunClass))
		return require $sLoadFilePath; //只加载文件操作(require效率高于require_once)
	else
	{	//加载类并返回新建的类对象
		if (class_exists($sRunClass, false)) //判断类名是否已经加载
			return new $sRunClass();//找到类创建对象
		else
		{	//类未加载，加载文件
			if (file_exists($sLoadFilePath))
			{
				require_once $sLoadFilePath; //加载类文件
				if (class_exists($sRunClass, false))
					return new $sRunClass();//找到类创建对象
				else
					return null; //文件加载成功但未找到类
			}
			else //类文件不存在
				return null;
		}
	}
}

/**
 * 打印变量内容（调试函数）
 *  可通过:core.config中的environment.cfg.php中的show_debug_info来控制是否启用
 * @param mixed  $obj 需要打印的对象
 * @param string $sTitle 需要打印的标题
 * @return void
 * @global SEA_PHP_FW_VAR_ENV
 */
function _dbg($obj, $sTitle=null)
{
	if ($GLOBALS['SEA_PHP_FW_VAR_ENV']['show_debug_info'] === true)
		dbg::D($obj, $sTitle);
}

/**
 * 打印变量内容并终止程序（调试函数）
 *
 * @param string $obj 需要打印的对象
 * @return void
 * @global SEA_PHP_FW_VAR_ENV
 */
function _dbge($obj)
{
	if ($GLOBALS['SEA_PHP_FW_VAR_ENV']['show_debug_info'] === true)
		dbg::DE($obj);
}
?>