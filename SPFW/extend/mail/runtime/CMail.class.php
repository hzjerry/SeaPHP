<?php
/**
 * 邮件操作对象<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130430
 * @package SPFW.extend.db.runtime
 * @final
 * @example 建立数据库对象
 * <pre>
 * $oM = new CMail(); //邮件对象
 * if(!$oM->Send(array('hzjerry@gmail.com'), 'test mail', 'this is phpMailer send mail'))
 *     echo "邮件发送失败."; //发送失败时会把错误日志写入log.core.extend.CMail
 * else
 *     echo "邮件发送成功";
 * </pre>
 *
 * */
final class CMail extends CExtModule
{
	/**
	 * smtp发件服务器地址
	 * @var string
	 */
	static $msMailHost = null;
	/**
	 * 发件人账户的用户名(smtp)
	 * @var string
	 */
	static $msAccountName = null;
	/**
	 * 发件人账户的密码
	 * @var string
	 */
	static $msAccountPwd = null;
	/**
	 * 发件人邮件地址(一般与发件人账户名相同)
	 * @var string
	 */
	static $msFromAddr = null;
	/**
	 * 发件人名字
	 * @var string
	 */
	static $msFromName = null;
	/**
	 * 默认监控地址(抄送地址)
	 * @var string
	 */
	static $msMonitorAddr = null;
	/**
	 * 邮件模板目录(包路径)
	 * @var string
	 */
	static $msTemplate = null;
	/**
	 * 邮件发送错误日志(用于记录错误日志信息到日志文件)
	 * @var string
	 */
	private $maErrLog = array();
	/* (non-PHPdoc)
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile()
	{
		$this->merger2autoload('extend.mail.config', 'autoload.cfg.php');
	}

	/* (non-PHPdoc)
	 * @see CExtModule::setName()
	 */
	protected function setName($sModule = __CLASS__)
	{
		$this->msModule = $sModule;
	}

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		parent::__construct();
		$this->loagCfg(); //载入全局配置信息
	}

	/* (non-PHPdoc)
	 * @see CExtModule::__destruct()
	 */
	public function __destruct()
	{
		if (count($this->maErrLog) > 0)
		{	//邮件发送存在错误,保存错误日志
			$this->setSaveLogData($this->maErrLog);
			unset($this->maErrLog);
		}

		parent::__destruct();
	}

	/* (non-PHPdoc)
	 * @see CExtModule::isAbleRun()
	 */
	public function isAbleRun()
	{
		$aRet = array();
		if (version_compare(PHP_VERSION, '5.0.0', '<'))
			$aRet[__CLASS__.' (phpMailer Pequires PHP5+ operating environment)'] = false;
		else
			$aRet[__CLASS__.' (phpMailer Pequires PHP5+ operating environment)'] = true;
		return $aRet;
	}

	/**
	 * 载入环境配置文件
	 * @return void
	 * @access private
	 */
	private function loagCfg()
	{	//防止多次载入，使用静态变量。
		if (is_null(self::$msMailHost))
		{
			$aCfg = import('extend.mail.config', 'environment.cfg.php');
			self::$msMailHost = $aCfg['mail_host'];
			self::$msAccountName = $aCfg['account_name'];
			self::$msAccountPwd = $aCfg['account_pwd'];
			self::$msFromAddr = $aCfg['from_address'];
			self::$msFromName = $aCfg['from_name'];
			self::$msMonitorAddr = $aCfg['monitor_addr'];
			self::$msTemplate = $aCfg['template'];
			unset($aCfg);
		}
	}

	/**
	 * 发送邮件
	 * @access public
	 * @param array $aAddr 收件人地址数组
	 * @param string $sTitle 邮件标题
	 * @param string $sBody 邮件内容
	 * @param string $sType 邮件类型[text | html]
	 * @return boolean
	 */
	public function send($aAddr, $sTitle, $sBody, $sType='text')
	{
		//邮箱发送方配置
		$oM = new PHPMailer(); //建立邮件发送类
		$oM->CharSet = "UTF-8"; // 设置编码
		$oM->IsSMTP(); // 使用SMTP方式发送
		$oM->SMTPAuth = true; // 启用SMTP验证功能
		$oM->Host = self::$msMailHost; // 企业邮局域名
		$oM->Username = self::$msAccountName; // 邮局用户名(请填写完整的email地址)
		$oM->Password = self::$msAccountPwd; // 邮局密码
		$oM->From = self::$msFromAddr; //邮件发送者email地址
		$oM->FromName = self::$msFromName; //发件人名字
		//监控邮箱(密件抄送)
		if (!$oM->AddBCC(self::$msMonitorAddr, 'Monitor'))
			$oM->AddAddress(self::$msMonitorAddr); //密件抄送失败,直接显式发送

		//发送的邮件信息设置
		if ('html' == $sType)
		{
			$oM->IsHTML(true); //html邮件
			$oM->AltBody = 'This is the HTML-formatted e-mail.';
		}
		else
			$oM->IsHTML(false); //文本邮件
		$oM->Subject = $sTitle; //邮件标题
		$oM->Body = $sBody; //邮件标题
		$oM->Priority = 1; //急件
		//设置收件人地址列表
		foreach ($aAddr as $sAddr)
			$oM->AddAddress($sAddr);

		if (!$oM->Send())
		{	//邮件发送失败,将失败信息保存到日志
			$this->maErrLog[] = '----'. date('Y-m-d H:i:s') .'----';
			$this->maErrLog[] = 'err msg: '. $oM->ErrorInfo;
			$this->maErrLog[] = 'mail to: '. $aAddr[0];
			$this->maErrLog[] = 'mail title: '. $sTitle;
			$this->maErrLog[] = 'mail body: start-body('. mb_strlen($sBody) .'):';
			$this->maErrLog[] = $sBody;
			$this->maErrLog[] = 'end-body;'. "\n";
			unset($oM);
			return false;
		}
		else
		{
			unset($oM);
			return true;
		}
	}

	/**
	 * 获取文本模板内容<br/>
	 * 备注:模板文件保存路径请参见environment.cfg.php中的配置目录
	 * @param string $sTemplateName 模板名称(无需后缀,默认后缀.tpl)
	 * @param array $aReplace 模板替换数组 array('key'=>'val', ...)
	 * @param string $sSignatureName 签名文件名称(无需后缀,默认后缀.tpl)
	 * @return string|null
	 */
	public function getTemplateText($sTemplateName, $aReplace, $sSignatureName=null)
	{
		$sTextPath = getMAC_ROOT() . getFW_ROOT() . str_replace('.', '/', self::$msTemplate) .'/text/';
		$sSigPath = getMAC_ROOT() . getFW_ROOT() . str_replace('.', '/', self::$msTemplate) .'/signature/';
		if (file_exists($sTextPath . $sTemplateName .'.tpl'))
		{
			$sData = file_get_contents($sTextPath . $sTemplateName .'.tpl') ."\n"; //取出模板内容
			//如果存在签名模板,取出
			if (!is_null($sSignatureName) && file_exists($sSigPath . $sSignatureName .'.tpl'))
				$sData .= file_get_contents($sSigPath . $sSignatureName .'.tpl');
			return strtr($sData, $aReplace);//模板数组替换并输出
		}
		else
			return null;
	}

	/**
	 * 获取HTML模板内容<br/>
	 * 备注:模板文件保存路径请参见environment.cfg.php中的配置目录
	 * @param string $sTemplateName 模板名称(无需后缀,默认后缀.tpl)
	 * @param array $aReplace 模板替换数组 array('key'=>'val', ...)
	 * @return string|null
	 */
	public function getTemplateHtml($sTemplateName, $aReplace)
	{
		$sPath = getMAC_ROOT() . getFW_ROOT() . str_replace('.', '/', self::$msTemplate) .'/html/';
		if (file_exists($sPath . $sTemplateName .'.tpl'))
			return strtr(file_get_contents($sPath . $sTemplateName .'.tpl'), $aReplace);//模板数组替换并输出
		else
			return null;
	}
}

?>