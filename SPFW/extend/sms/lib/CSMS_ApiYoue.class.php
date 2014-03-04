<?php
/**
 * 悠逸公司的短信API接入类<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130924
 * @package SPFW.extend.sms.lib
 * @link http://youe.smsadmin.cn/
 */
class CSMS_ApiYoue implements ISmsSend
{
	/**
	 * 单条短信的字数
	 * @var int
	 */
	const SINGLE_MSG_SIZE = 67;
	/**
	 * 长短信的字数
	 * @var int
	 */
	const LONG_MSG_SIZE = 250;
	/**
	 * 每个发送包，最大的发送号码数量
	 * @var int
	 */
	const SEND_MAX_NUM = 300;
	/**
	 * 群发API接口地址
	 * @var string
	 */
	const SEND_API_URL = 'http://www.smsadmin.cn/smsmarketing/wwwroot/api/post_send/';
	/**
	 * 资源剩余数查询API地址
	 * @var string
	 */
	const RESOURCE_API_URL = 'http://www.smsadmin.cn/smsmarketing/wwwroot/api/user_info/';
	/**
	 * POSTHTTP对象
	 * @var CPostHttp
	 */
	private $moPost = null;
	/**
	 * 账户名
	 * @var string
	 */
	private $msAccount = null;
	/**
	 * 账户密码
	 * @var string
	 */
	private $msPwd = null;

	/* !CodeTemplates.overridecomment.nonjd!
	 * @see ISmsSend::setAccount()
	 */
	public function setAccount($sUName, $sPwd)
	{
		$this->msAccount = $sUName;
		$this->msPwd = $sPwd;
	}

	/* !CodeTemplates.overridecomment.nonjd!
	 * @see ISmsSend::sendMsg()
	 */
	public function send($aPhone, $sMsg, $sDatetime=null)
	{
		$iSendNum = 0;
		if (count($aPhone) > self::SEND_MAX_NUM)
			return array('state'=>-1, 'msg'=>'号码数组超上限');
		elseif (mb_strlen($sMsg, CENV::getCharset()) > self::LONG_MSG_SIZE)
			return array('state'=>-2, 'msg'=>'短信内容超上限');
		elseif (!empty($sDatetime) && CValCheck::CK($sDatetime, array('datetime')))
			return array('state'=>-3, 'msg'=>'发送时间无效');
		elseif (mb_strlen($sMsg, CENV::getCharset()) <= self::SINGLE_MSG_SIZE)
			$iSendNum = 1;//单条短信的处理
		elseif (mb_strlen($sMsg, CENV::getCharset()) <= self::LONG_MSG_SIZE)
			$iSendNum = ceil(mb_strlen($sMsg, CENV::getCharset())/self::SINGLE_MSG_SIZE);//长短信的处理
		//正常发送流程
		if (is_null($this->moPost))
			$this->moPost = new CPostHttp();
		$this->moPost->addField('uid', $this->msAccount);
		$this->moPost->addField('pwd', $this->msPwd);
		$this->moPost->addField('mobile', implode(';', $aPhone));
		$this->moPost->addField('msg', iconv(CENV::getCharset(), 'GB2312', $sMsg));
		$this->moPost->post(self::SEND_API_URL);
		if (200 !== $this->moPost->getResponseStatus())
			return array('state'=>-4, 'msg'=>'API服务器未响应');
		else
		{	//收到正常回复，正常处理流程
			$sBuf = $this->moPost->getContent();
			$cState = substr($sBuf, 0, 1);
			if ('0' === $cState)
				return array('state'=>$iSendNum, 'msg'=>'发送成功');
			elseif ('1' === $cState)
				return array('state'=>-5, 'msg'=>'用户名或密码错误');
			elseif ('6' === $cState)
				return array('state'=>-6, 'msg'=>'含有敏感字符发送错误');
			elseif ('2' === $cState)
				return array('state'=>-7, 'msg'=>'余额不足');
			else
				return array('state'=>-50, 'msg'=>iconv('GB2312', CENV::getCharset(), $sBuf));
		}
	}
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see ISmsSend::getMsgLength()
	 */
	public function getSingleMsgLength()
	{
		return self::SINGLE_MSG_SIZE;
	}
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see ISmsSend::getPhonePackageSize()
	 */
	public function getPhonePackageSize()
	{
		return self::SEND_MAX_NUM;
	}
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see ISmsSend::getMaxMsgLength()
	 */
	public function getMaxMsgLength()
	{
		return self::LONG_MSG_SIZE;
	}
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see ISmsSend::restResources()
	 */
	public function restResources()
	{
		if (is_null($this->moPost))
			$this->moPost = new CPostHttp();
		$sParam = '?uid='. urldecode($this->msAccount) .'&pwd='.urldecode($this->msPwd);
		$this->moPost->post(self::RESOURCE_API_URL . $sParam);
		if (200 !== $this->moPost->getResponseStatus())
			return array('state'=>-4, 'msg'=>'API服务器未响应');
		else
		{	//收到正常回复，正常处理流程
			$sBuf = $this->moPost->getContent();
			$cState = substr($sBuf, 0, 1);
			if ('1' === $cState)
				return array('state'=>-5, 'msg'=>'用户名或密码错误');
			else
			{
				$aBuf = split('<br>', iconv('GB2312', CENV::getCharset(), $sBuf));
				if (count($aBuf) == 2)
				{
					$aTmp = split('=', $aBuf[1]);
					if (isset($aTmp[1]) && !CValCheck::CK(trim($aTmp[1]), array('isint')))
						return array('state'=>intval($aTmp[1]), 'msg'=>'剩余短信'); //正常获得数据
				}
			}
			//未知错误
			return array('state'=>-50, 'msg'=>iconv('GB2312', CENV::getCharset(), $sBuf));
		}
	}
}
?>