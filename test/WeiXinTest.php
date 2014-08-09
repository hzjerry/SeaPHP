<?php
header('Content-Type: text/html; charset=UTF-8');
require './SPFW/sea_php_init.php';
/*
 $aTmp = array('f51af5514fb574957a83bb3314047050', '1378104183', '1378684865');
 sort($aTmp, SORT_STRING); //按照协议要求进行排序
 echo 'signature: '. sha1(implode('', $aTmp));
 exit();
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title>微信公众帐号测试器</title>
 </head>

 <body>
  <form method="post" action="">
	文字内容:<input type="text" name="content" value="<?php echo (isset($_POST['content']))? $_POST['content'] : 'this is test content'?>"><br/>
	<input type="submit">
  </form>
<?php
$sSignature = '6d7ed16f388633c05568873fd196ab24823f1dbe';
$sTimestamp = '1378104183';
$sNonce = '1378684865';
$sUrl = 'http://seaphp.fox.cn:8080/WeiXinService.php';
$aSend = array();
if (isset($_POST['content'])){
	$aSend[0] ='
	<xml>
	<ToUserName><![CDATA[gh_911b8934b1da]]></ToUserName>
	<FromUserName><![CDATA[ohEC6jqzhE7Yeb_qaaQ2B0sr9930]]></FromUserName>
	<CreateTime>1348831860</CreateTime>
	<MsgType><![CDATA[text]]></MsgType>
	<Content><![CDATA[{@content}]]></Content>
	<MsgId>1234567890123456</MsgId>
	</xml>
	';
	$aSend[1] ='
	<xml>
	<ToUserName><![CDATA[gh_911b8934b1da]]></ToUserName>
	<FromUserName><![CDATA[ohEC6jqzhE7Yeb_qaaQ2B0sr9930]]></FromUserName>
	<CreateTime>1348831860</CreateTime>
	<MsgType><![CDATA[event]]></MsgType>
	<Event><![CDATA[unsubscribe]]></Event>
	</xml>
	';
	$aSend[2] ='
	<xml>
	<ToUserName><![CDATA[gh_911b8934b1da]]></ToUserName>
	<FromUserName><![CDATA[ohEC6jqzhE7Yeb_qaaQ2B0sr9930]]></FromUserName>
	<CreateTime>1348831860</CreateTime>
	<MsgType><![CDATA[event]]></MsgType>
	<Event><![CDATA[CLICK]]></Event>
	<EventKey><![CDATA[电话]]></EventKey>
	</xml>
	';
	$aSend[3] ='
	<xml>
	<ToUserName><![CDATA[gh_911b8934b1da]]></ToUserName>
	<FromUserName><![CDATA[oxiTvjobkYA_DPofkR16_Y5kZl1I]]></FromUserName>
	<CreateTime>1348831860</CreateTime>
	<MsgType><![CDATA[image]]></MsgType>
	<PicUrl><![CDATA[http://weiplug-dev.oss-cn-hangzhou.aliyuncs.com/FileStore/lijian%40dzs_mobi/scrlty/FC%21VB6j%5D%28Y_%29/background]]></PicUrl>
	<MsgId>1234567890123456</MsgId>
	</xml>
	';
	$aSend[4] ='
	<xml>
	<ToUserName><![CDATA[gh_911b8934b1da]]></ToUserName>
	<FromUserName><![CDATA[oxiTvjobkYA_DPofkR16_Y5kZl1I]]></FromUserName>
	<CreateTime>1348831860</CreateTime>
	<MsgType><![CDATA[event]]></MsgType>
	<Event><![CDATA[LOCATION]]></Event>
	<Latitude>23.137466</Latitude>
	<Longitude>113.352425</Longitude>
	<Precision>119.385040</Precision>
	</xml>
	';
	$oHttp = new CPostHttp();
// 	$oHttp->showDebug();
	$oHttp->setPostData(strtr($aSend[0], array('{@content}'=>$_POST['content'])));
	if ($oHttp->post($sUrl.'?signature='. $sSignature .'&timestamp='. $sTimestamp .'&nonce='.$sNonce))
		echo '<pre>', strtr($oHttp->getContent(), CXmlArrayConver::$msEntities), '</pre>';
}

_dbg(memory_get_peak_usage());
_dbg(CENV::getRuntime());
?>
 </body>
</html>
