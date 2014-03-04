<?php
/**
 * 短信接入商接口约定<br/>
 * 各个接入商的短信API都必须继承这个接口
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130924
 * @package SPFW.extend.sms.lib
 */
interface ISmsSend
{
	/**
	 * 配置帐号
	 * @param string $sUName 账户名
	 * @param string $sPwd 账户密码
	 * @return bool
	 */
	public function setAccount($sUName, $sPwd);
	/**
	 * 群发短信
	 * @param array $aPhone 号码包数组(包的号码数量参见getPhonePackageSize()返回的结果)
	 * @param string $sMsg 短信内容(长度参见个接口)
	 * @param string $sDatetime 定时发送时间,默认null为立即发送(format:YYYY-mm-dd HH:ii:ss)
	 * @return array('state'=>'', 'msg'=>'')<br />
	 * 成功: [state:1(扣费1条)|msg:单短信发送成功]、[state:N(N>1,扣费N条)|msg:长短信发送成功]<br/>
	 * 失败: [state:-1|msg:号码数组超上限]、[state:-2|msg:短信内容超上限]、[state:-3|msg:发送时间无效]、
	 * [state:-4|msg:API服务器未响应]、[state:-5|msg:用户名或密码错误]、[state:-6|msg:含有敏感字符发送错误]、
	 * [state:-7|msg:余额不足]、[state:-50|msg:API返回的其他接口错误信息解释]
	 */
	public function send($aPhone, $sMsg, $sDatetime=null);
	/**
	 * 获得单条短信字数上限(不区分全角与半角)
	 * @return int
	 */
	public function getSingleMsgLength();
	/**
	 * 获得长短信的字数上限(不区分全角与半角)
	 * @return int
	 */
	public function getMaxMsgLength();
	/**
	 * 每次提交的号码包数组上限
	 * @return int
	 */
	public function getPhonePackageSize();
	/**
	 * 剩余的资源(帐号的剩余可用短信条数)
	 * @return array('state'=>'', 'msg'=>'')<br />
	 * 成功：[state:N(N>=0)|msg:短信剩余条数]<br/>
	 * 失败：[state:-4|msg:API服务器未响应]、[state:-5|msg:用户名或密码错误]、
	 * [state:-50|msg:API返回的其他接口错误信息解释]
	 */
	public function restResources();
}

?>