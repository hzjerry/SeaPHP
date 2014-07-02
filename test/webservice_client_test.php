<?php
// http://seaphp.fox.cn:8080/test/webservice_client_test.php
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require '../SPFW/sea_php_init.php';

$o = new CWebServiceClient('CWscCfgTest');
$o->openDebug();//打印调试参数
$aParam = array(
	'name'=>'jerryli',
	'userid'=>'12312312313',
	'mother'=>'王小姐',
	'father'=>'李先生',
	'brother'=>'陈兄弟',
);

_dbg($o->exec('develop.test', 'GET_USER_INFO', $aParam));
?>