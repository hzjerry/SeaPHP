<?php
/**
 * Qrcode生成实例程序
 * @author Jerryli(hzjerry@gmail.com)
 * @version V20150106
 * @package SPFW
 */
define('SEA_PHP_ROOT', '/');
require './SPFW/sea_php_init.php';
CExtendManage::Run(new CPrivateClas());

/*入口参数*/
$iErrLevel = isset($_GET['e'])?intval($_GET['e']):0;//容错级别[0,1,2,3]
$sData = isset($_GET['c'])?$_GET['c']:null;//编码的内容

/*输入验证*/
if (empty($sData)){
	echo 'The lack of the necessary production parameters';
	exit(0);
}
/**
 *
 * param[0]: 编码内容
 * param[1]: 生成缓存文件名(默认为false)
 * param[2]: 容错级别(默认为:QR_ECLEVEL_L) QR_ECLEVEL_L | QR_ECLEVEL_M | QR_ECLEVEL_Q | QR_ECLEVEL_H
 * param[3]: 每个点的像数位(默认为:3)
 * param[4]: 外边框像数(默认为:1)
 * param[5]: 是否保存到文件(默认为:false:不保存直接显示)
 */
QRcode::png($sData, false, $iErrLevel, 4);
?>