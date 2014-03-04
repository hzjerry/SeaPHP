<?php
/**
 * Website Engine的单一入口处理类(工厂模式)<br/>
 *  依赖：SPWF.core.lib.final包的<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130723
 * @package SPFW.extend.website_engine.runtime
 * @final
 * @example
 *	CExtendManage::Run(new CWebsiteEngine('agent')); //agent为工作区
 * */
final class CWebsiteEngine extends CExtModule implements IExtFramework
{
	/**
	 * 工作区名称
	 * @var string
	 * @static
	 */
	static public $msPart = null;
	/**
	 * smarty3的模板目录
	 * @var string
	 * @static
	 */
	static public $msSmartyTplPath = null;
	/**
	 * smarty3的缓存文件目录
	 * @var string
	 * @static
	 */
	static public $msSmartyCachePath = null;
	/**
	 * smarty3模板代码分隔符
	 * @var string
	 * @static
	 */
	static public $maSmartyDelimiter = null;
	/**
	 * smarty3模板的强制编译
	 * @var string
	 * @static
	 */
	static public $maSmartyForceCompile = null;
	/**
	 * 静态缓存目录的包路径
	 * @var string
	 * @static
	 */
	static public $msStaticCachePath = null;
	/**
	 * 页面路由翻书解析数组array('pkg', 'act', 'ctl')
	 * @var array
	 * @static
	 */
	static public $maRouter = null;
	/**
	 * 页面逻辑文件工作区
	 * @var string
	 */
	private $msLogicWorkgroup = null;
	/**
	 * 构造函数
	 * @see CExtModule::__construct()
	 */
	function __construct($msArea)
	{
		parent::__construct(); //必须先运行父类的构造函数
		$this->loadConfig(); //载入配置信息
		if (empty($msArea)) //缺少工作区参数
			CErrThrow::throwExit('CExtModule construct error.', '初始化时缺少工作区参数$msArea,模块无法运行');
		else
			self::$msPart = $msArea; //设定工作区

		//Smarty3模块常量配置
		define('SMARTY_DIR', getMAC_ROOT() .getFW_ROOT() . str_replace('.', '/', 'extend.website_engine.lib.smarty3').'/');

		ob_start();//启动输出缓存
	}

	/**
	 * 析构函数
	 * @see CExtModel::__destruct()
	 */
	function __destruct()
	{
		parent::__destruct(); //必须要调用父类析构函数
	}

	/*
	 * (non-PHPdoc)
	 * @see CExtModule::isAbleRun()
	 */
	public function isAbleRun()
	{
		$aRet = array();
		/*日志目录能否写入*/
		$sPath = getMAC_ROOT(). getFW_ROOT() . strtr(self::$msStaticCachePath, array('.'=>'/'));
		if (!file_exists($sPath))
		{	//创建静态缓存目录
			if (CFileOperation::creatDir($sPath))
				$aRet['CWebsiteEngine static_cache can be witten'] = true;
			else
				$aRet['CWebsiteEngine static_cache can be witten'] = false;
		}
		else
		{	//检查静态缓存目录是否有可写权限
			if (0x02 & CFileOperation::file_mode_info($sPath))
				$aRet['CWebsiteEngine static_cache can be witten'] = true;
			else
				$aRet['CWebsiteEngine static_cache can be witten'] = false;
		}

		return $aRet;
	}

	/*
	 * (non-PHPdoc)
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile()
	{	//加载本框架的自动加载类
		$this->merger2autoload('extend.website_engine.config', 'autoload.cfg.php');
		//装载Website Engine专用静态函数库
		import('extend.website_engine.lib', 'static_function.php');
	}

	/*
	 * (non-PHPdoc)
	 * @see CExtModule::setName()
	 */
	protected function setName($sModule=__CLASS__)
	{
		$this->msModule = $sModule;
	}

	/**
	 * 载入环境配置
	 * @return void
	 */
	private function loadConfig()
	{
		$aCfg = import('extend.website_engine.config', 'environment.cfg.php');
		//TODO:载入配置信息
		$this->msLogicWorkgroup = $aCfg['logic_workgroup'];
		self::$msStaticCachePath = $aCfg['static_cache'];
		self::$msSmartyTplPath = $aCfg['smarty_tpl_workgroup'];
		self::$msSmartyCachePath = $aCfg['smarty_cache'];
		self::$maSmartyDelimiter = $aCfg['smarty_delimiter'];
		self::$maSmartyForceCompile = $aCfg['smarty_force_compile'];
		unset($aCfg);
	}

	/*
	 * (non-PHPdoc)
	 * 运行入口
	 * @see IExtFramework::start()
	*/
	public function start()
	{
		$sPkg = null;
		$sAct = null;
		$sCtl = null;
		//TODO:入口
		//分离路由信息
		if (is_null(self::$maRouter = self::resolveURL()))
		{
			//没有操作参数,载入工作区的默认package信息
			$aCfg = import('extend.website_engine.config', 'entrance.cfg.php');
			if (isset($aCfg[self::$msPart]))
				$sPkg = $aCfg[self::$msPart]; //取出默认package
			else
			{	//工作区配置信息不存在(给出错误提示)
				CErrThrow::throwExit('CExtModule start error.',
				'工作区['. self::$msPart .']默认入口信息不存在，请检查[extend.website_engine.config\entrance.cfg.php 配置文件');
			}
		}
		else
		{	//含路由信息,逐位分解到对应的变量
			switch(count(self::$maRouter))
			{
				case 1: list($sPkg) = self::$maRouter; break;
				case 2: list($sPkg, $sAct) = self::$maRouter; break;
				default:list($sPkg, $sAct, $sCtl) = self::$maRouter;
			}
		}
		//重新将整理好的数据填入路由信息表
		self::$maRouter = array($sPkg, $sAct, $sCtl);

// _dbg($sPkg .'-'. $sAct .'-'. $sCtl, 'Router');

		/******运行访问安全检查类******/
		$oTmp = import($this->msLogicWorkgroup .'.'. self::$msPart, 'WebLogicSecurityCheck.php', 'WebLogicSecurityCheck');
		if (is_null($oTmp)) //页面逻辑安全检查类不存在
		{
			CErrThrow::throwExit(__CLASS__. 'Fail: page logic WebLogicSecurityCheck class not find.',
			'页面逻辑的用户安全检查类不存在,请检查:<br/>'. $this->msLogicWorkgroup .'.'. self::$msPart .
			'下的WebLogicSecurityCheck.php文件是否存在且类名正确编写.');
		}
		else
		{
			$this->checkAccess($oTmp, $sPkg, $sAct);
			unset($oTmp);
		}

		/******运行页面逻辑******/
		$sPath = getMAC_ROOT() . getFW_ROOT() .
				 str_replace('.', '/', $this->msLogicWorkgroup .'.'. self::$msPart .'.'.$sPkg) .'.php';
		if (file_exists($sPath))
		{	//将页面逻辑类文件名与package路劲进行分离
			if (false !== strrpos($sPkg, '.'))
			{
				$sPkgPath = $this->msLogicWorkgroup .'.'. self::$msPart .'.'. substr($sPkg, 0, strrpos($sPkg, '.'));
				$aFile = substr($sPkg, strrpos($sPkg, '.')+1);
			}
			else
			{	//$sPkg中不存在'.'，表示此$sPkg为文件名，不是路径
				$sPkgPath = $this->msLogicWorkgroup .'.'. self::$msPart;
				$aFile = $sPkg;
				$sPkg = null; //清除包路径(此时$sPkg为文件名，不是路径)
			}
// _dbg($sPkgPath, '$sPkgPath');
// _dbg($aFile, '$aFile');
			$oTmp = import($sPkgPath, $aFile .'.php', $aFile);
			if (is_null($oTmp))
			{	 //页面逻辑类不存在（可能是类名与文件名不同引起）
				CErrThrow::throwExit(__CLASS__. ' page logic create fail',
				'页面逻辑类创建失败，请核对类名是否与类文件名完全相同。');
			}
			else
			{	//执行页面逻辑
				$this->runLogic($oTmp, $sAct, $sCtl, $sPkgPath, $aFile);
				unset($oTmp);
			}
		}
		else
		{	//对应的页面逻辑类文件不存在,直接重定向到404页面
			ob_end_clean(); //清空输出缓存中的内容;
			header('HTTP/1.1 404 Not Found');
			exit();
		}
// _dbg($sPath);
	}

	/**
	 * 解析URL入口地址<br/>
	 * 返回:正常情况返回数据,其参数最多为3个,数组参数的顺序为 array('package', 'act', 'ctl')
	 * @return NULL|array:
	 */
	static private function resolveURL()
	{
		if (strpos($_SERVER['QUERY_STRING'], '&') > 0)
			/*包含Get数据的时候的分离方式*/
			$sRouter = substr($_SERVER['QUERY_STRING'], 0, strpos($_SERVER['QUERY_STRING'], '&'));
		else
			/*不包含Get数据的时候的分离方式*/
			$sRouter = $_SERVER['QUERY_STRING'];

		if (empty($sRouter))
			return null;
		else
		{
			if (!empty($_GET))
				array_shift($_GET);//移除GET中的第一项
			return explode('-', $sRouter);
		}
	}

	/**
	 * 运行页面逻辑类方法
	 * @param CWebsiteModule $oCWM 页面逻辑类对象
	 * @param string $sAct 需要执行的类方法
	 * @param string $sCtl 传入的控制参数
	 * @param string $sPkg 包路径
	 * @param string $sClass 类名
	 * @return null
	 */
	private function runLogic(CWebsiteModule $oCWM, $sAct, $sCtl, $sPkg, $sClass)
	{
		if (empty($sAct))//没有传入Act参数,从页面业务逻辑类中直接获取默认Act方法
		{
			$sAct = $oCWM->getDefaultFunc(); //取得默认Act
			self::$maRouter[1] = $sAct; //将默认Act保存到路由信息中
		}


		if (method_exists($oCWM, $sAct)) //检查类中的指定Act方法是否存在
		{
			$oCWM->init($sPkg, $sAct, $sCtl, $sClass); //设置参数
			$oCWM->$sAct($sCtl); //执行指定方法
		}
		else
		{
			//对应的页面逻辑类中指定的方法不存在,直接重定向到404页面
			ob_end_clean(); //清空输出缓存中的内容;
			header('HTTP/1.1 404 Not Found');
			exit();
		}
	}

	/**
	 * 访问授权检查
	 * @param CWebsiteSecurity $oCWS
	 * @param string $sPkg
	 * @param string $sAct
	 */
	private function checkAccess(CWebsiteSecurity $oCWS, $sPkg, $sAct)
	{
		$oCWS->init(self::$msPart);
		$oCWS->checkAccess($sPkg, $sAct);
	}
}
?>