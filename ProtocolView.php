<?php
/**
 * Web Service的协议显示页面入口
 * @see SPWF.extend.webservice.runtime CWebServiceProtocolView
 * @author Jerryli(hzjerry@gmail.com)
 * @version V20130422
 * @package SPFW
 */
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require './SPFW/sea_php_init.php';

CExtendManage::Run(new CWebServiceProtocolView());

_dbg(memory_get_peak_usage(), 'memory');
_dbg(CENV::getRuntime(), 'run time');
?>