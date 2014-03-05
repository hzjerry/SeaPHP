<?php
/**
 * db测试
 * @author Jerryli(hzjerry@gmail.com)
 * @version V20130501
 * @package SPFW
 */
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require '../SPFW/sea_php_init.php';

CExtendManage::Run(new CWebsiteEngine('agent'));

_dbg($_GET, 'GET');
_dbg(memory_get_peak_usage(), 'memory');
_dbg(CENV::getRuntime(), 'run time');
?>