<?php
/**
 * Web Service启动入口
 * @see SPWF.extend.webservice.runtime CWebService
 * @author Jerryli(hzjerry@gmail.com)
 * @version V20130422
 * @package SPFW
 */
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require './SPFW/sea_php_init.php';
//如果get参数存在protocol_type=json，则使用json协议，否则默认使用xml协议
CExtendManage::Run(new CWebService());
?>