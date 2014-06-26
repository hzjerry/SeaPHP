<?php
defined('SEA_PHP_RUNTIME') or exit('sea php framework initialization step is not valid.'); //功能:让框架必须按顺序加载
/**
 * 系统全局变量管理类
 * @author Jerryli(hzjerry@gmail.com)
 *
 * @package SPFW.core.lib.sys
 * @global SEA_PHP_FW_VAR_ENV, SEA_PHP_FW_VAR_BEGIN_TIMESTEMP
 * @version
 * <li>V20140626 修改了修改了getRuntime函数，提高运行效率</li>
 * */
class CENV
{

	/**
	 * 获取系统字符集
	 * @return string
	 * @static
	 * @access public
	 * @global SEA_PHP_FW_VAR_BEGIN_TIMESTEMP
	 */
	static public function getCharset()
	{
		return $GLOBALS['SEA_PHP_FW_VAR_ENV']['charset'];
	}

	/**
	 * 获取系统运行时间
	 *
	 * @return float ms
	 * @static
	 * @access public
	 * @global SEA_PHP_FW_VAR_BEGIN_TIMESTEMP
	 */
	static public function getRuntime()
	{
		return (microtime(true) - $GLOBALS['SEA_PHP_FW_VAR_BEGIN_TIMESTEMP']) * 1000;
	}

	/**
	 * 获取访问者IP
	 *
	 * @return string
	 * @static
	 * @access public
	 * @global SEA_PHP_FW_VAR_IP
	 */
	static public function getIP()
	{
		return $GLOBALS['SEA_PHP_FW_VAR_IP'];
	}

	/**
	 * 获取主机头<br/>
	 *  例:www.fox.cn或www.fox.cn:8080
	 * @return string
	 * @static
	 * @access public
	 * @global SEA_PHP_FW_VAR_HOST
	 */
	static public function getHost()
	{
		return $GLOBALS['SEA_PHP_FW_VAR_HOST'];
	}
}

?>