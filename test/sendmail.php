<?php
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require '../SPFW/sea_php_init.php';
$oM = new CMail(); //邮件对象
$aEMailAddress = array('lijian@dns.com.cn');

$sRep = array('{#AgentName#}'=>'小王', '{#logname#}'=>'agent0000', '{#pwd#}'=>'123123');
$sTemp = $oM->getTemplateText('create_agent', $sRep, 'DoNotReply');
if(!$oM->Send($aEMailAddress, 'test mail', $sTemp))
	echo "邮件发送失败.";
else
{
	echo "邮件发送成功";
	_dbg($aEMailAddress, 'address');
}
?>