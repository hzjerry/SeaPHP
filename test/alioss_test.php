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
$oss = new CAliOSS(); //oss操作对象
_dbg($oss->deleteAll('eee/ppp/'));
_dbg($oss->isAbleRun());
unset($oss);
_dbg(memory_get_peak_usage(), 'memory');
_dbg(CENV::getRuntime(), 'run time');
?>