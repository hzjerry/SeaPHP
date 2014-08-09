<?php
/**
 * Wei Xin Service 启动入口
 * @see SPWF.extend.weixin_service.runtime CWeiXinHelper.class.php
 * @author Jerryli(hzjerry@gmail.com)
 * @version V20140807
 * @package SPFW
 */
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require './SPFW/sea_php_init.php';
CExtendManage::Run(new CWeiXinHelper());
?>