<?php
/*
 * Website Engine 专用静态全局函数
 *
 * @author Jerryli(hzjerry@gmail.com)
 * @package SPFW.extend.website_engine.lib
 * @version V20130724
 */

/**
 * 获取GET参数
 * @param string $sKey 关键字
 * @return string|null
 */
function G($sKey)
{
	if (get_magic_quotes_gpc()) //如果php.ini开启了输入转义(magic_quotes_gpc=On)，需要反转处理
		return (isset($_GET[$sKey]))? stripslashes(trim($_GET[$sKey])) : null;
	else
		return (isset($_GET[$sKey]))? trim($_GET[$sKey]) : null;
}

/**
 * 获取POST参数
 * @param string $sKey 关键字
 * @return string|null
 */
function P($sKey)
{
	if (get_magic_quotes_gpc()) //如果php.ini开启了输入转义(magic_quotes_gpc=On)，需要反转处理
		return (isset($_POST[$sKey]))? stripslashes(trim($_POST[$sKey])) : null;
	else
		return (isset($_POST[$sKey]))? trim($_POST[$sKey]) : null;
}

/**
 * 获取客户Post过来的信息，如果不存在则取Get的信息
 * @param string $sKey 关键字
 * @return string|null
 */
function PG($sKey)
{
	if (isset($_POST[$sKey]))
		return P($sKey);
	elseif (isset($_GET[$sKey]))
		return G($sKey);
	else
		return null;
}

/**
 * 重定向浏览器
 * @param string $url 要重定向的 url
 * @return void
 */
function redirect($url)
{
	if (headers_sent())
	{	//headers已经送出，不能再次送出
		echo 'redirect to <a href="'. $url .'">click jump</a>',
			'<script language="javascript">',
			'document.location.href="'. $url .'";',
			'</script>';
	}
	else
		header('Location: ' .$url);
	exit(0);
}
?>