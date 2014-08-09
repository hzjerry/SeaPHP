<?php
/**
 * 音乐类型回复对象
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140808
 * @package SPFW.extend.weixin_service.lib
 * @abstract
 */
final class CWeiXinReplyMusic extends CWeiXinReply{
	/**
	 * 音乐标题
	 * @var string
	 */
	private $_sTitle = null;
	/**
	 * 音乐描述
	 * @var string
	 */
	private $_sDescription = null;
	/**
	 * 音乐链接
	 * @var string
	 */
	private $_sMusicURL = null;
	/**
	 * 高质量音乐链接
	 * @var string
	 */
	private $_sHQMusicUrl = null;
	/**
	 * 缩略图的媒体id
	 * @var string
	 */
	private $_sThumbMediaId = null;
	/**
	 * @see CWeiXinReply::__construct()
	 */
	public function __construct($oReceive){
		parent::__construct($oReceive);
	}
	/**
	 * 设置要发送的内容
	 * @param string $sTitle 音乐标题
	 * @param string $Desc 音乐描述
	 * @param string $sURL 音乐链接
	 * @param string $sHUrl 高质量音乐链接(可不填)
	 * @param string $sThumbMediaId 缩略图的媒体id(可不填)
	 */
	public function setMsg($sTitle, $Desc, $sURL, $sHUrl=null, $sThumbMediaId=null){
		$this->_sTitle = $sTitle;
		$this->_sDescription = $Desc;
		$this->_sMusicURL = $sURL;
		$this->_sHQMusicUrl = $sHUrl;
		$this->_sThumbMediaId = $sThumbMediaId;
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
		$aBuf[] = ' <MsgType>music</MsgType>';
		$aBuf[] = ' <Music>';
		$aBuf[] = '  <Title><![CDATA['. parent::safeS($this->_sTitle) .']]></Title>';
		$aBuf[] = '  <Description><![CDATA['. parent::safeS($this->_sDescription) .']]></Description>';
		$aBuf[] = '  <MusicUrl><![CDATA['. parent::safeS($this->_sMusicURL) .']]></MusicUrl>';
		if (empty($this->_sHQMusicUrl)) //没有高音质链接是，默认用普通音质链接
			$aBuf[] = '  <HQMusicUrl><![CDATA['. parent::safeS($this->_sMusicURL) .']]></HQMusicUrl>';
		else
			$aBuf[] = '  <HQMusicUrl><![CDATA['. parent::safeS($this->_sHQMusicUrl) .']]></HQMusicUrl>';
		if (empty($this->_sThumbMediaId))
			$aBuf[] = '  <ThumbMediaId></ThumbMediaId>';
		else
			$aBuf[] = '  <ThumbMediaId><![CDATA['. parent::safeS($this->_sThumbMediaId) .']]></ThumbMediaId>';
		$aBuf[] = ' </Music>';
		$aBuf[] = '</xml>';
		return implode("\n", $aBuf);
	}
}
?>