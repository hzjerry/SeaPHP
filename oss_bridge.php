<?php
/**
 * 阿里OSS内部转发网桥
 * @see SPWF.extend.webservice.runtime CWebService
 * @author Jerryli(hzjerry@gmail.com)
 * @version V20131015
 * @package SPFW
 */
header('Content-Type: text/html; charset=UTF-8');
require './SPFW/sea_php_init.php';
//如果get参数必须存在u参数
CExtendManage::Run(new CAliOssInternalBridge());
?>