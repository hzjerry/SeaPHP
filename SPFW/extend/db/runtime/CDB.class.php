<?php
/**
 * 数据库操作对象<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130430
 * @package SPFW.extend.db.runtime
 * @final
 * @example 建立数据库对象
 * <pre>
 * $odb = new CDB('CDbCfgLocalTest'); //CDbCfgLocalTest为数据库连接的DSN配置文件
 * </pre>
 *
 * */
final class CDB extends CExtModule
{
	/**
	 * 数据库驱动类型验证关键字
	 * @var array
	 */
	static public $maDbType = array('mysqli', 'mysql', 'sqlite');
	/**
	 * 表名强制大小写验证关键字
	 * @var array
	 */
	static public $maTableUpperLowerKeys = array('upper', 'lower', 'intact');
	/**
	 * 数据库使用的字符集
	 * @var string
	 */
	static private $msCharset = null;
	/**
	 * 记录SQL的执行日志
	 * @var bool
	 */
	static private $mbWriteLog = false;
	/**
	 * 表对象访问包目录
	 * @var string
	 */
	static private $msTableObjectPath = null;
	/**
	 * SQL出错时打印出错信息
	 * @var bool
	 */
	static private $mbShowError = false;
	/**
	 * SELECT操作的默认库
	 * @var bool
	 */
	static private $mbSelectRW = null;
	/**
	 * 表前缀
	 * @var string | null
	 */
	private $msPrefix = null;
	/**
	 * 表名强制大小写<br />
	 * [upper:强制大写|lower:强制小写|intact:保持原样]
	 * @var string
	 */
	private $msTableUpperLower = 'intact';
	/**
	 * 数据库连接对象
	 * @var CDbDriver
	 */
	private $moDBL = null;
	/**
	 * 数据库操作对象
	 * @var CDbCURD
	 */
	private $moCURD = null;

	/* (non-PHPdoc)
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile()
	{
		$this->merger2autoload('extend.db.config', 'autoload.cfg.php');
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
	public function __construct($sDSN)
	{
		parent::__construct();
		$this->loagCfg(); //载入全局配置信息
		//载入数据库连接配置对象
		$oDSN = import('extend.db.dsn', $sDSN .'.php', $sDSN);
		$aCfg = $oDSN->getEnvironment();
		if (!empty($aCfg['prefix']))
			$this->msPrefix = trim($aCfg['prefix']);
		/*检查表名强制大小写字符设置*/
		$sTmp = strtolower($aCfg['table_upper_lower']);
		if (!empty($sTmp) && in_array($sTmp, self::$maTableUpperLowerKeys))
			$this->msTableUpperLower = $sTmp;
		else
		{	//配置设置不正确
			echo '<br />', $sDSN, '->getEnvironment(): table_upper_lower must be upper or lower or intact';
			exit(0);
		}
		/*数据库驱动设置*/
		if (in_array($aCfg['db_driver'], self::$maDbType))
		{
			$aMaster = $oDSN->getRW(); //载入主库配置
			$aSlave = $oDSN->getR(); //载入只读库配置
			if (!empty($aSlave))
			{
				if (count($aSlave) == 1)
					$aSlave = $aSlave[0]; //只存在一个只读库，直接取出
				else //存在多个只读库，随机选择一个
					$aSlave = $aSlave[rand(0, count($aSlave)-1)];
			}
			if ('mysqli' == $aCfg['db_driver']) //创建数据库连接对象
				$this->moDBL = new CDbDriverMySqli(self::$msCharset, $aMaster, $aSlave);
			elseif ('mysql' == $aCfg['db_driver']) //创建数据库连接对象
				$this->moDBL = new CDbDriverMySql(self::$msCharset, $aMaster, $aSlave);

			$this->moDBL->setSelectRW(self::$mbSelectRW);//设置默认SELECT操作库
			if (self::$mbShowError) //设置遇到SQL错误时，是否打印错误信息
				$this->moDBL->setSqlErrShow();

			//建立数据库操作对象
			$this->moCURD = new CDbCURD($this->moDBL, $this->msPrefix, $this->msTableUpperLower);
			unset($aMaster, $aSlave);
		}
		else
		{	//配置设置不正确
			echo '<br />', $sDSN, '->getEnvironment(): db_driver must be ', implode(' or ', self::$maDbType);
			exit(0);
		}
		unset($aCfg);
	}

	/* (non-PHPdoc)
	 * @see CExtModule::__destruct()
	 */
	public function __destruct()
	{
		if (self::$mbWriteLog)
		{	//是否记录日志，由environment.cfg.php中的write_log控制
			$aBuf = array();
			foreach ($this->moDBL->getSqlLog() as $aNode)
				$aBuf[] = implode('', array($aNode['time'], ' ms,', "\t", $aNode['sql']));
			if (count($aBuf) > 0)
				$this->setSaveLogData($aBuf); //输出SQL日志
			unset($aBuf);
		}

		if (!is_null($this->moDBL))
			unset($this->moDBL);
		if (!is_null($this->moCURD))
			unset($this->moCURD);
		$this->moDBL = null;
		$this->moCURD = null;

		parent::__destruct();
	}

	/* (non-PHPdoc)
	 * @see CExtModule::isAbleRun()
	 */
	public function isAbleRun()
	{
		$aRet = array();
		if (!function_exists('mysql_connect'))
			$aRet['CDB (mysql_connect)'] = false;
		else
			$aRet['CDB (mysql_connect)'] = true;

		if (!class_exists('mysqli'))
			$aRet['CDB (mysqli)'] = false;
		else
			$aRet['CDB (mysqli)'] = true;
		return $aRet;
	}

	/**
	 * 载入环境配置文件
	 * @return void
	 * @access private
	 */
	private function loagCfg()
	{	//防止多次载入，使用静态变量。
		if (is_null(self::$msCharset))
		{
			$aCfg = import('extend.db.config', 'environment.cfg.php');
			self::$msCharset = $aCfg['charset'];
			self::$mbWriteLog = $aCfg['write_log'];
			self::$mbShowError = $aCfg['show_error'];
			self::$msTableObjectPath = $aCfg['table_object_path'];
			self::$mbSelectRW = (isset($aCfg['select_default']) && $aCfg['select_default'] == 'r')? false : true;
			unset($aCfg);
		}
	}

	/**
	 * 返回数据库操作对象
	 * @return CDbCURD
	 */
	public function db()
	{
		return $this->moCURD;
	}


	/**
	 * 返回表对象(以表为业务逻辑对象)<br />
	 * 注意: 表对象存放于workgroup.db.table_object（可在environment.cfg.php中的table_object_path参数配置）
	 * @param string $sTableName 表名
	 * @param string $sProject 项目名称(默认无需设置)<br />
	 * $sProject: 如在本框架内存在多个项目，可通过设置这个值来区分不同项目的表对象
	 * @return CDbTable
	 */
	public function tableObj($sTableName, $sProject=null)
	{
		//检查表对象类文件是否存在
		$sPath = getMAC_ROOT() . getFW_ROOT() . strtr(self::$msTableObjectPath, array('.'=>'/')) .'/';
		if (empty($sProject))
			$sClassName = strtoupper($sTableName);
		else
			$sClassName = strtoupper(self::$msTableObjectPath .'_'. $sTableName);
		if (file_exists($sPath . $sClassName .'.php'))//找到表对象
			$oTable = import(self::$msTableObjectPath, $sClassName .'.php', $sClassName); //获得表对象
		else
			$oTable = new CDbTable(); //公共表对象

		$oTable->init($this->moDBL, $this->msPrefix, $this->msTableUpperLower, $sTableName); //初始化表对象
		return $oTable;
	}
}

?>