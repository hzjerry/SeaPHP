<?php
/**
 * Cache缓存测试
 * @see SPWF.extend.cache.runtime
 * @author Jerryli(hzjerry@gmail.com)
 * @version V20130513
 * @package SPFW
 */
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require '../SPFW/sea_php_init.php';

$oC = new CCache();
_dbg($oC->isAbleRun(), 'isAbleRun()');
$sKey = 'runtime';
$aRet = $oC->get($sKey);
if (is_null($aRet))
{  //创建缓存
	$aRet = array
	(
		'name'=>'jerryli',
		'datetime'=>date('Y-m-d H:i:s'),
		'content'=>'如果读的时候没有加共享锁，那么其他程序要写的话（不管这个写是加锁还是不加锁）都会立即写成功。如果正好读了一半，然后被其他程序给写了，那么读的后一半就有可能跟前一半对不上（前一半是修改前的，后一半是修改后的）',
	);
	//浮点耗时计算
	for($i=1000000; $i>0; $i--)
		rand(1, 99999999);

	if ($oC->set($sKey, $aRet, 5))
		_dbg($aRet, '缓存创建成功');
	else
		_dbg($aRet, '缓存创建失败');
}
else
	_dbg($aRet, '取出缓存');

$iTmp = $oC->increment('visit', 2);
if (is_null($iTmp))
{
	$oC->set('visit', 1);
	$iTmp = 1;
}
$iTmp = $oC->decrement('visit');
_dbg($iTmp, '增量');
// $oC->del($sKey);
//$oC->gc(); //回收缓存资源

_dbg(memory_get_peak_usage(), 'memory');
_dbg(CENV::getRuntime(), 'run time');
?>