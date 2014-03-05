<?php
// http://seaphp.fox.cn:8080/clientest.php
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require '../SPFW/sea_php_init.php';

$o = new CSeaPhpWebServiceClient('http://seaphp.fox.cn:8080/webservice.php', 'www.seaphp.org$develop*~`!#%^&');
$o->addNode('username', 'jerryli');
$o->addNode('info', null, array('sex'=>'男', 'weight'=>'51', 'birthday'=>'1980-10-16'));
$o->addNode('home.builder', 'guest', array('sex'=>'男', 'weight'=>'51', 'birthday'=>'1980-10-16'));
$aTmp = array(array('name'=>'王海', 'id'=>'00001'), array('name'=>'王菲', 'id'=>'00002'));
$o->addSameTagAttrib('row', $aTmp);
// _dbg($o->getXmlArray());
$o->openDebug();
_dbg($o->exec('develop.test', 'GET_USER_INFO'));
_dbg(memory_get_peak_usage());
_dbg(CENV::getRuntime());
?>