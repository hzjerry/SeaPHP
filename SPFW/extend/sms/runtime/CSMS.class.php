<?php
/**
 * 短信群发接口模块<br/>
 * 已介入的群发接口公司
 * 1、http://youe.smsadmin.cn/
 * 2、http://bizsms.cn/
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130817
 * @package SPFW.extend.db.runtime
 * @final
 * @example sms操作
 * <pre>
 * $oM = new CSMS(); //sms操作对象
 * _dbg($oM->sms()->send(array('13000000000'), '短信发送'))
 * </pre>
 *
 * */
final class CSMS extends CExtModule
{
	/**
	 * 账户用户名
	 * @var string
	 */
	static $msAccount = null;
	/**
	 * 帐户密码
	 * @var string
	 */
	static $msPwd = null;
	/**
	 * 接入的API模块类
	 * @var string
	 */
	static $msApiClass = null;
	/**
	 * 帐号配置信息数组(保存多帐号的配置信息，0表示第一个配置)
	 * @var array
	 */
	static $maSmsAccount = null;
	/**
	 * 短信发送操作对象
	 * @var ISmsSend
	 */
	private $moSendAPI = null;

	/* (non-PHPdoc)
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile()
	{
		$this->merger2autoload('extend.sms.config', 'autoload.cfg.php');
	}

	/* (non-PHPdoc)
	 * @see CExtModule::setName()
	 */
	protected function setName($sModule = __CLASS__)
	{
		$this->msModule = $sModule;
	}

	/**
	 * 构造函数<br />
	 * @param string $sDSN 数据库连接配置（配置文件必须存放于extend.db.dsn下，文件名与类名相同）
	 */
	public function __construct()
	{
		parent::__construct();
		$this->loagCfg(); //载入全局配置信息
		//创建SMS操作对象
		$this->moSendAPI = import('extend.sms.lib', self::$msApiClass .'.class.php', self::$msApiClass);
		$this->moSendAPI->setAccount(self::$msAccount, self::$msPwd);//配置帐号
	}

	/* (non-PHPdoc)
	 * @see CExtModule::__destruct()
	 */
	public function __destruct()
	{
		unset($this->moSendAPI);//释放对象
		parent::__destruct();
	}

	/* (non-PHPdoc)
	 * @see CExtModule::isAbleRun()
	 */
	public function isAbleRun()
	{
		$aRet = array();
		$aRet[__CLASS__. ' ( mb_strlen check ok)'] = function_exists('mb_strlen');
		return $aRet;
	}

	/**
	 * 载入环境配置文件
	 * @return void
	 * @access private
	 */
	private function loagCfg()
	{
		$aCfg = import('extend.sms.config', 'environment.cfg.php');
		self::$maSmsAccount = $aCfg['sms_account']; //取出短信接口配置帐号
		//设置初始值
		self::$msApiClass = self::$maSmsAccount[0]['api_class'];
		self::$msAccount = self::$maSmsAccount[0]['account'];
		self::$msPwd = self::$maSmsAccount[0]['pwd'];
		unset($aCfg);
	}

	/**
	 * 切换短信发送帐号
	 * @param int $iIdx 帐号配置索引（参见:./config/environment.cgf.php）
	 */
	public function changeAccount($iIdx=0)
	{
		if (isset(self::$maSmsAccount[$iIdx]))
		{
			if (self::$msApiClass !== self::$maSmsAccount[$iIdx]['api_class'])
			{	//重新创建帐号对象
				self::$msApiClass = self::$maSmsAccount[$iIdx]['api_class'];
				unset($this->moSendAPI);
				$this->moSendAPI = import('extend.sms.lib', self::$msApiClass .'.class.php', self::$msApiClass);
				if (is_null($this->moSendAPI))
					CErrThrow::throwExit('sms error', '群发类对象:'. self::$msApiClass .'不存在');
			}
		}
		else //配置数组索引无效
			CErrThrow::throwExit('sms error', '短信配置账户不存在，请检查environment.cgf.php');

		self::$msAccount = self::$maSmsAccount[$iIdx]['account'];
		self::$msPwd = self::$maSmsAccount[$iIdx]['pwd'];
		$this->moSendAPI->setAccount(self::$msAccount, self::$msPwd);//配置帐号
	}

	/**
	 * 返回短信接口对象的操作引用
	 * @return ISmsSend
	 */
	public function sms()
	{
		return $this->moSendAPI;
	}
}

?>