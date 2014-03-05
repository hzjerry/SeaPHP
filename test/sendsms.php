<?php
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require '../SPFW/sea_php_init.php';
$oM = new CSMS(); //短信对象

$aPhone = array('13646865279');
// $sMsg = str_pad('*****', 68, '12345');
$oM->changeAccount(1); //改变为第二组发送帐号
_dbg($oM->sms()->send($aPhone, '测试短信'));
?>