<?php
/**
 * 图文类型回复对象
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
final class CWeiXinReplyNews extends CWeiXinReply{
	/**
	 * 回复的消息内容
	 * <li>array(array('Title'=>'标题', 'Description'=>'消息描述', 'PicUrl'=>'图标', 'Url'=>'跳转地址'),...)</li>
	 * @var string
	 */
	private $_aList = null;
	/**
	 * @see CWeiXinReply::__construct()
	 */
	public function __construct($oReceive){
		parent::__construct($oReceive);
	}
	/**
	 * 增加一个图文项
	 * @param string $sTitle 标题
	 * @param string $sDesc 消息描述
	 * @param string $sPicUrl 图标(大图360*200，小图200*200)
	 * @param string $sUrl 跳转地址(为null则不跳转)
	 */
	public function addNode($sTitle, $sDesc, $sPicUrl, $sUrl=null){
		if (count($this->_aList) < 10)
			$this->_aList[] = array('Title'=>$sTitle, 'Description'=>$sDesc, 'PicUrl'=>$sPicUrl, 'Url'=>$sUrl);
	}
	/**
	 * @see CWeiXinReply::toXml()
	*/
	public function toXml(){
		$aBuf = array();
		$aBuf[] = '<xml>';
		$aBuf[] = ' <ToUserName><![CDATA['. parent::safeS($this->_sToUserName) .']]></ToUserName>';
		$aBuf[] = ' <FromUserName><![CDATA['. parent::safeS($this->_sFromUserName) .']]></FromUserName>';
		$aBuf[] = ' <CreateTime>'. strval(time()) .'</CreateTime>';
		$aBuf[] = ' <MsgType>news</MsgType>';
		$aBuf[] = ' <ArticleCount>'. count($this->_aList) .'</ArticleCount>';
		$aBuf[] = ' <Articles>';
		foreach ($this->_aList as $aNode){
			$aBuf[] = '  <item>';
			if (!empty($aNode['Title']))
				$aBuf[] = '   <Title><![CDATA['. parent::safeS($aNode['Title']) .']]></Title>';
			if (!empty($aNode['Description']))
				$aBuf[] = '   <Description><![CDATA['. parent::safeS($aNode['Description']) .']]></Description>';
			if (!empty($aNode['PicUrl']))
				$aBuf[] = '   <PicUrl><![CDATA['. parent::safeS($aNode['PicUrl']) .']]></PicUrl>';
			if (!empty($aNode['Url']))
				$aBuf[] = '   <Url><![CDATA['. parent::safeS($aNode['Url']) .']]></Url>';
			$aBuf[] = '  </item>';
		}
		$aBuf[] = ' </Articles>';
		$aBuf[] = '</xml>';
		return implode("\n", $aBuf);
	}
}
?>