<?php
/**
 * mysqli数据库层驱动<br/>
 * 备注:必须php5.2.9以上<br />
 * 备注:当配置的只读库不不存在时，将自动连接到主库去读取
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130419
 * @package SPFW.entend.db.lib
 * @see IDbDriver
 * @example
 * */
class CDbDriverMySqli extends CDbDriver
{
	/**
	 * 连接配置关键字列表(检查配置连接完整性用)
	 * @var array
	 */
	static public $maConnKeys = array('host', 'username', 'pwd', 'dbname', 'port');
	/**
	 * 主库连接配置<br />
	 * array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>'3306')
	 * @var array
	 */
	private $maMaster =  null;
	/**
	 * 只读库连接配置<br />
	 * array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>'3306')
	 * @var array
	 */
	private $maSlave =  null;
	/**
	 * 读写库资源链接
	 * @var mysqli
	 * @access protected
	 */
	protected $mmRW = null;
	/**
	 * 只读库资源链接
	 * @var mysqli
	 * @access protected
	 */
	protected $mmR = null;

	/**
	 * 构造<br/>
	 * $aMaster : array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>'3306')<br />
	 * $aSlave : array(array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>3306), ...)
	 * @param string $sCharset 本地字符集
	 * @param array $aMaster 主库连接配置
	 * @param array $aSlave 只读库连接配置
	 */
	function __construct($sCharset, $aMaster, $aSlave=null)
	{
		if (version_compare(phpversion(), '5.2.9', '<'))
		{	//当前版本不够运行
			echo '<br/>mysqli running version must be &lt;= 5.2.9.';
			exit(0);
		}
		elseif (!class_exists('mysqli'))
		{	//mysqli未安装
			echo '<br/>mysqli is not installed.';
			exit(0);
		}
		$this->msDbType = 'MySql';  //指定数据库类型
		$this->msCharset = $sCharset; //设定字符集

		//检查主库配置数组
		if (is_array($aMaster) && !$this->checkConnectCfgArray($aMaster))
		{
			echo '<br/>Lack the necessary database configuration parameter.<br />',
				 'Must have parameters:', implode(',', self::$maConnKeys);
			exit(0);
		}
		$this->maMaster = $aMaster;

		//检查只读库配置数组
		if (!empty($aSlave) && !$this->checkConnectCfgArray($aSlave))
		{
			echo '<br/>Lack the necessary database configuration parameter.<br />',
				 'Must have parameters:', implode(',', self::$maConnKeys);
			exit(0);
		}
		if (empty($aSlave))
			$this->maSlave = null;
		else
			$this->maSlave = $aSlave;
	}

	/**
	 * 析构函数
	 */
	public function __destruct()
	{
		if ($this->mmR == $this->mmRW)
			$this->mmR = null; //两个连接指向同一个地址时，直接删除只读库引用

		if (!is_null($this->mmRW))
		{
			$this->mmRW->close();
			unset($this->mmRW);
			$this->mmRW = null;
		}
		if (!is_null($this->mmR))
		{
			$this->mmR->close();
			unset($this->mmR);
			$this->mmR = null;
		}
	}

	/**
	 * 数据库连接自动建立（按需连接）
	 * @param bool $bRW [true:读写库 | false:只读库 | null:系统默认值]
	 * @return mysqli
	 * @access private
	 */
	private function ConnWhenNeeded($bRW = null)
	{
		if (is_null($bRW)) //载入系统默认配置
			$bRW = $this->mbSelectRW;

		if ($bRW)
		{	//读写库连接
			if (empty($this->mmRW))
			{	//建立连接
				$this->mmRW = $this->connect
				(
					$this->maMaster['host'],
					$this->maMaster['username'],
					$this->maMaster['pwd'],
					$this->maMaster['dbname'],
					$this->maMaster['port']
				);
				$this->msRW_MD5 = md5
				(
					$this->maMaster['host'] .
					$this->maMaster['username'] .
					$this->maMaster['pwd'] .
					$this->maMaster['dbname'] .
					$this->msCharset .
					$this->maMaster['port']
				);
			}
			return $this->mmRW;
		}
		else
		{	//只读库连接
			if (empty($this->mmR))
			{	//建立连接
				if (is_array($this->maSlave))
					$aCfg = $this->maSlave;
				else //只读库不存在时，直接连接主库
					$aCfg = $this->maMaster;

				$this->mmR = $this->connect
				(
					$aCfg['host'],
					$aCfg['username'],
					$aCfg['pwd'],
					$aCfg['dbname'],
					$aCfg['port']
				);
				$this->msR_MD5 = md5
				(
					$aCfg['host'] .
					$aCfg['username'] .
					$aCfg['pwd'] .
					$aCfg['dbname'] .
					$this->msCharset .
					$aCfg['port']
				);
			}
			return $this->mmR;
		}
	}

	/**
	 * 检查数据库连接配置数组是否有效
	 * @param array $aCfg
	 * @return bool
	 * @access private
	 */
	private function checkConnectCfgArray($aCfg)
	{
		//建立数据库连接
		foreach ($aCfg as $sKey => $sVal)
		{
			if (!in_array($sKey, self::$maConnKeys))
				return false;
		}
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::connect()
	 */
	protected function connect($sHost, $sUserName, $sPwd, $sDataBaseName, $iPort=3306)
	{
		static $aRDbPool = array();
		$sConSre = md5($sHost . $sUserName . $sPwd . $sDataBaseName . $this->msCharset . $iPort); //生成缓存池识别码
		if (array_key_exists($sConSre, $aRDbPool))
		{	//在缓存池中找到对象
			$oMysqli = $aRDbPool[$sConSre]; //取出缓存池中已经存在的链接对象
		}
		else
		{	//创建新对象
			$oMysqli = new mysqli($sHost, $sUserName, $sPwd, $sDataBaseName, $iPort); //这是一个耗时操作
			if ($oMysqli->connect_error)
			{
				echo '<br/> Failed to create a database connection.',
					 '<br/> Error msg: ', $oMysqli->connect_error;
				exit(0);
				return null;
			}
			//设定默认字符集
			if (!$oMysqli->set_charset($this->msCharset))
			{
				echo '<br />MySql error: fail loading character set ', $this->msCharset ,"\n";
				exit(0);
			}
			$aRDbPool[$sConSre] = $oMysqli; //保存链接对象到缓存池
		}
		return $oMysqli;
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::exec()
	 */
	public function exec($sSql, $bGetId = false)
	{
		$odb = $this->ConnWhenNeeded();
		if ($bGetId && (preg_match('/^(insert)\s+\S*/i', $sSql) > 0) )
		{	//只有insert才需要这个处理
			$this->startTimer();
			$results = $odb->query($sSql, MYSQLI_STORE_RESULT);
			$this->addSqlHistory($sSql);
			if ($results === false)
				$this->showSqlErr($odb->errno, $odb->error); //SQL语句有问题
			else
				return $odb->insert_id; //返回ID
		}
		else
		{
			$this->startTimer();
			$bRet = $odb->query($sSql, MYSQLI_USE_RESULT);
			$this->addSqlHistory($sSql);
			if ($bRet === false)
				$this->showSqlErr($odb->errno, $odb->error); //SQL语句有问题
		}
		return $odb->affected_rows; //返回影响的记录条数
	}

	/** 事务处理<br />
	 * 备注:只有InnoDB和BDB存储引擎提供事务安全表<br />
	 * 只支持: insert | update | delete | replace
	 * @see IDbDriver::transaction()
	 */
	public function transaction($aSqls)
	{
		foreach ($aSqls as $sSql)
		{	//逐个检查每条指令，过滤掉不支持事务的指令
			if ((preg_match('/^(insert|update|delete|replace)\s+\S*/i', $sSql) == 0))
				return false;
		}

		$odb = $this->ConnWhenNeeded();
		$odb->autocommit(false);//关闭自动提交
		foreach ($aSqls as $sSql)
		{
			$this->startTimer();
			$bRet = $odb->query($sSql, MYSQLI_USE_RESULT);
			$this->addSqlHistory($sSql);
			if (false === $bRet)
			{
				$sErrCode = $odb->errno;
				$sErrMsg = $odb->error;
				$odb->rollback(); //回滚事务
				$this->showSqlErr($sErrCode, $sErrMsg);
				return false; //执行失败
			}
		}
		$odb->commit(); //指令全部执行成功，提交事务
		$odb->autocommit(true);//关闭自动提交
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::query()
	 */
	public function query($sSql, $bRW = null)
	{
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return null;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象

		$this->startTimer();
		$results = $odb->query($sSql, MYSQLI_STORE_RESULT);
		$this->addSqlHistory($sSql);
		if ($results === false)
		{	//SQL语句有问题
			$this->showSqlErr($odb->errno, $odb->error);
		}
		else
		{
			$aRet = array();
			while(!is_null($aRow = $results->fetch_array(MYSQLI_ASSOC)))
				$aRet[] = $aRow;
			$results->free(); //释放对象
			unset($results);
			if (count($aRet) == 0)
				return null; //未找到记录集
			else
				return $aRet; //输出记录集
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::queryPage()
	*/
	public function queryPage($sSql, $iPage, $iPageSize, $bRW = null)
	{
		static $sTemplate = ' LIMIT {@start}, {@cnt}';

		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return null;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象

		//初始化输入参数
		$iPage = (intval($iPage) <= 0)? 1 : $iPage;
		$iPageSize = (intval($iPageSize) <= 0)? 1 : $iPageSize;
		$iRowStart = ($iPage - 1) * $iPageSize + 1;//计算页面起始记录位置
		/*追加LIMIT子句*/
		$sSql .= strtr($sTemplate, array('{@start}'=>$iRowStart - 1, '{@cnt}'=>$iPageSize));

		$this->startTimer();
		$results = $odb->query($sSql, MYSQLI_STORE_RESULT);
		$this->addSqlHistory($sSql);
		if ($results === false)
		{	//SQL语句有问题
			$this->showSqlErr($odb->errno, $odb->error);
		}
		else
		{
			$aRet = array();
			while(!is_null($aRow = $results->fetch_array(MYSQLI_ASSOC)))
				$aRet[] = $aRow;
			$results->free(); //释放对象
			unset($results);
			if (count($aRet) == 0)
				return null; //未找到记录集
			else
				return $aRet; //输出记录集
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::queryOne()
	 */
	public function queryOne($sSql, $bRW = null)
	{
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return null;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象
		$this->startTimer();
		$sSql .= ' LIMIT 1';
		$results = $odb->query($sSql, MYSQLI_USE_RESULT); //无缓存直接输出
		$this->addSqlHistory($sSql);
		if ($results === false)
		{	//SQL语句有问题
			$this->showSqlErr($odb->errno, $odb->error);
		}
		else
		{
			$aRow = $results->fetch_array(MYSQLI_NUM);
			$results->free(); //释放对象
			if (is_null($aRow))
				return null;
			else
				return $aRow[0];
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::queryFirstRow()
	 */
	public function queryFirstRow($sSql, $bRW = null)
	{
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return null;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象
		$this->startTimer();
		$sSql .= ' LIMIT 1';
		$results = $odb->query($sSql, MYSQLI_USE_RESULT); //无缓存直接输出
		$this->addSqlHistory($sSql);
		if ($results === false)
			$this->showSqlErr($odb->errno, $odb->error);//SQL语句有问题
		else
		{
			$aRow = $results->fetch_array(MYSQLI_ASSOC);
			$results->free(); //释放对象
			if (is_null($aRow))
				return null;
			else
				return $aRow;
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::queryFirstCol()
	 */
	public function queryFirstCol($sSql, $bRW = null)
	{
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return null;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象

		$this->startTimer();
		$results = $odb->query($sSql, MYSQLI_USE_RESULT); //无缓存直接输出
		$this->addSqlHistory($sSql);
		if ($results === false)
			$this->showSqlErr($odb->errno, $odb->error);//SQL语句有问题
		else
		{
			$aRet = array();
			while(!is_null($aRow = $results->fetch_array(MYSQLI_NUM)))
				$aRet[] = $aRow[0];
			$results->free(); //释放对象
			if (count($aRet) == 0)
				return null;
			else
				return $aRet;
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::showApiVer()
	 */
	public function showApiVer()
	{
		$odb = $this->ConnWhenNeeded();
		return 'mysqli['. $this->getDbType() .' protocol version: '. $odb->protocol_version .']';
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::getDbType()
	 */
	public function getDbType()
	{
		return $this->msDbType;
	}
}

?>