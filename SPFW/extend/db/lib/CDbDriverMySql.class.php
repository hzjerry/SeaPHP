<?php
/**
 * mysql数据库层驱动
 * <li>注意:mysql函数不支持存储过程</li>
 * <li>备注:当配置的只读库不不存在时，将自动连接到主库去读取</li>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20150104
 * @package SPFW.entend.db.lib
 * @see IDbDriver
 * */
class CDbDriverMySql extends CDbDriver{
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
	 * @var resource
	 * @access protected
	 */
	protected $mrRW = null;
	/**
	 * 只读库资源链接
	 * @var resource
	 * @access protected
	 */
	protected $mrR = null;

	/**
	 * 构造<br/>
	 * $aMaster : array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>'3306')<br />
	 * $aSlave : array(array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>3306), ...)
	 * @param string $sCharset 本地字符集
	 * @param array $aMaster 主库连接配置
	 * @param array $aSlave 只读库连接配置
	 */
	function __construct($sCharset, $aMaster, $aSlave=null){
		$this->msDbType = 'MySql';  //指定数据库类型
		$this->msCharset = $sCharset; //设定字符集

		//检查主库配置数组
		if (is_array($aMaster) && !$this->checkConnectCfgArray($aMaster)){
			echo '<br/>Lack the necessary database configuration parameter.<br />',
				 'Must have parameters:', implode(',', self::$maConnKeys);
			exit(0);
		}
		$this->maMaster = $aMaster;

		//检查只读库配置数组
		if (!empty($aSlave) && !$this->checkConnectCfgArray($aSlave)){
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
	public function __destruct(){
		if ($this->mrRW === $this->mrR)
			$this->mrR = null;//两个连接指向同一个地址时，直接删除只读库引用

		if (!is_null($this->mrRW)){
			mysql_close($this->mrRW);
			unset($this->mrRW);
			$this->mrRW = null;
		}
		if (!is_null($this->mrR)){
			if (mysql_close($this->mrR) !== false){
				unset($this->mrR);
				$this->mrR = null;
			}
		}
	}

	/**
	 * 数据库连接自动建立（按需连接）
	 * @param bool $bRW [true:读写库 | false:只读库 | null:系统默认值]
	 * @return mysqli
	 * @access private
	 */
	private function ConnWhenNeeded($bRW = null){
		if (is_null($bRW)) //载入系统默认配置
			$bRW = $this->mbSelectRW;

		if ($bRW){	//读写库连接
			if (empty($this->mrRW)){	//建立连接
				$this->mrRW = $this->connect(
					$this->maMaster['host'],
					$this->maMaster['username'],
					$this->maMaster['pwd'],
					$this->maMaster['dbname'],
					$this->maMaster['port']
				);
				$this->msRW_MD5 = md5(
					$this->maMaster['host'] .
					$this->maMaster['username'] .
					$this->maMaster['pwd'] .
					$this->maMaster['dbname'] .
					$this->msCharset .
					$this->maMaster['port']
				);
			}
			return $this->mrRW;
		}else{	//只读库连接
			if (empty($this->mrR)){	//建立连接
				if (is_array($this->maSlave))
					$aCfg = $this->maSlave;
				else //只读库不存在时，直接连接主库
					$aCfg = $this->maMaster;

				$this->mrR = $this->connect(
					$aCfg['host'],
					$aCfg['username'],
					$aCfg['pwd'],
					$aCfg['dbname'],
					$aCfg['port']
				);
				$this->msR_MD5 = md5(
					$aCfg['host'] .
					$aCfg['username'] .
					$aCfg['pwd'] .
					$aCfg['dbname'] .
					$this->msCharset .
					$aCfg['port']
				);
			}
			return $this->mrR;
		}
	}

	/**
	 * 检查数据库连接配置数组是否有效
	 * @param array $aCfg
	 * @return bool
	 * @access private
	 */
	private function checkConnectCfgArray($aCfg){
		//建立数据库连接
		foreach ($aCfg as $sKey => $sVal){
			if (!in_array($sKey, self::$maConnKeys))
				return false;
		}
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::connect()
	 */
	protected function connect($sHost, $sUserName, $sPwd, $sDataBaseName, $iPort=3306){
		static $aRDbPool = array();
		$sConSre = md5($sHost . $sUserName . $sPwd . $sDataBaseName . $this->msCharset . $iPort); //生成缓存池识别码
		if (array_key_exists($sConSre, $aRDbPool)){	//在缓存池中找到对象
			$rMysql = $aRDbPool[$sConSre]; //取出缓存池中已经存在的链接对象
		}else{	//创建新对象
			$rMysql = @mysql_connect($sHost .':'. $iPort, $sUserName, $sPwd); //这是一个耗时操作
			if (false === $rMysql){
				echo '<br/> Failed to create a database connection.',
					 '<br/> Error msg: ', mysql_error($rMysql);
				exit(0);
				return null;
			}
			//选择数据库
			if (mysql_select_db($sDataBaseName, $rMysql) === false){
				echo '<br />MySql error: not find datebase ', $sDataBaseName ,"\n";
				exit(0);
			}
			//设定默认字符集
			if (mysql_unbuffered_query('SET NAMES \''. $this->msCharset .'\'', $rMysql) === false){
				echo '<br />MySql error: fail loading character set ', $this->msCharset ,"\n";
				exit(0);
			}
			$aRDbPool[$sConSre] = $rMysql; //保存链接对象到缓存池
		}
		return $rMysql;
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::exec()
	 */
	public function exec($sSql, $bGetId = false){
		$odb = $this->ConnWhenNeeded();
		if ($bGetId && (preg_match('/^(insert)\s+\S*/i', $sSql) > 0) ){	//只有insert才需要这个处理
			$this->startTimer();
			$bRet = mysql_unbuffered_query($sSql, $odb);
			$this->addSqlHistory($sSql);
			if ($bRet === false)
				$this->showSqlErr(mysql_errno($odb), mysql_error($odb)); //SQL语句有问题
			else
				return mysql_insert_id($odb);//返回ID
		}else{
			$this->startTimer();
			$bRet = mysql_unbuffered_query($sSql, $odb);
			$this->addSqlHistory($sSql);
			if ($bRet === false)
				$this->showSqlErr(mysql_errno($odb), mysql_error($odb)); //SQL语句有问题
		}
		return mysql_affected_rows($odb); //返回影响条数
	}

	/** 事务处理<br />
	 * 备注:只有InnoDB和BDB存储引擎提供事务安全表<br />
	 * 只支持: insert | update | delete | replace
	 * @see IDbDriver::transaction()
	 */
	public function transaction($aSqls){
		foreach ($aSqls as $sSql){	//逐个检查每条指令，过滤掉不支持事务的指令
			if ((preg_match('/^(insert|update|delete|replace)\s+\S*/i', $sSql) == 0))
				return false;
		}

		$odb = $this->ConnWhenNeeded();
		mysql_query('SET AUTOCOMMIT = 0;', $odb);//关闭自动提交
		foreach ($aSqls as $sSql){
			$this->startTimer();
			$bRet = mysql_unbuffered_query($sSql, $odb);
			$this->addSqlHistory($sSql);
			if (false === $bRet){
				$sErrCode = mysql_errno($odb);
				$sErrMsg = mysql_error($odb);
				mysql_query('ROLLBACK;', $odb); //回滚事务
				mysql_query('SET AUTOCOMMIT = 1;', $odb);//打开自动提交
				$this->showSqlErr($sErrCode, $sErrMsg);
				return false; //执行失败
			}
		}
		mysql_query('COMMIT;', $odb); //指令全部执行成功，提交事务
		mysql_query('SET AUTOCOMMIT = 1;', $odb);//关闭自动提交
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::query()
	 */
	public function query($sSql, $bRW = null){
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return null;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象
		$this->startTimer();
		$oResult = mysql_query($sSql, $odb);
		$this->addSqlHistory($sSql);

		if ($oResult === false){
			$this->showSqlErr(mysql_errno($odb), mysql_error($odb));//SQL语句有问题
		}else{
			if (mysql_num_rows($oResult) > 0){
				$aRet = array();
				while (($row = mysql_fetch_assoc($oResult)) !== false)
					$aRet[] = $row;
				mysql_free_result($oResult); //释放临时数据集
				return $aRet; //输出记录集
			}else
				return null;
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::queryPage()
	*/
	public function queryPage($sSql, $iPage, $iPageSize, $bRW = null){
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
		$oResult = mysql_query($sSql, $odb);
		$this->addSqlHistory($sSql);

		if ($oResult === false){
			$this->showSqlErr(mysql_errno($odb), mysql_error($odb));//SQL语句有问题
		}else{
			if (mysql_num_rows($oResult) > 0){
				$aRet = array();
				while (($row = mysql_fetch_assoc($oResult)) !== false)
					$aRet[] = $row;
				mysql_free_result($oResult); //释放临时数据集
				return $aRet; //输出记录集
			}else
				return null;
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::queryOne()
	 */
	public function queryOne($sSql, $bRW = null){
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return null;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象
		$this->startTimer();
		$sSql .= ' LIMIT 1';
		$oResult = mysql_query($sSql, $odb); //无缓存直接输出
		$this->addSqlHistory($sSql);
		if ($oResult === false){
			$this->showSqlErr(mysql_errno($odb), mysql_error($odb));//SQL语句有问题
		}else{
			if (mysql_num_rows($oResult) > 0){
				$aRow = mysql_fetch_array($oResult, MYSQL_NUM);
				mysql_free_result($oResult); //释放临时数据集
				return $aRow[0];
			}else
				return null;
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::queryFirstRow()
	 */
	public function queryFirstRow($sSql, $bRW = null){
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return null;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象
		$this->startTimer();
		$sSql .= ' LIMIT 1';
		$oResult = mysql_query($sSql, $odb); //无缓存直接输出
		$this->addSqlHistory($sSql);
		if ($oResult === false){
			$this->showSqlErr(mysql_errno($odb), mysql_error($odb));//SQL语句有问题
		}else{
			if (mysql_num_rows($oResult) > 0){
				$aRow = mysql_fetch_array($oResult, MYSQL_ASSOC);
				mysql_free_result($oResult); //释放临时数据集
				return $aRow;
			}else
				return null;
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::queryFirstCol()
	 */
	public function queryFirstCol($sSql, $bRW = null){
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return null;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象

		$this->startTimer();
		$oResult = mysql_query($sSql, $odb); //无缓存直接输出
		$this->addSqlHistory($sSql);
		if ($oResult === false){
			$this->showSqlErr(mysql_errno($odb), mysql_error($odb));//SQL语句有问题
		}else{
			$aRet = array();
			if (mysql_num_rows($oResult) > 0){
				while (($row = mysql_fetch_array($oResult, MYSQL_NUM)) !== false)
					$aRet[] = $row[0];
				mysql_free_result($oResult); //释放临时数据集
				return $aRet;
			}else
				return null;
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::queryRowCallback()
	*/
	public function queryRowCallback($callFunc, $sSql, $bRW = null){
		if (!is_callable($callFunc)){ //$callFunc无效的闭包函数
			$this->showSqlErr('0000', 'queryRowCallback () executable invalid Anonymous functions(or closures)');
		}
		$iRowCnt = 0;
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			return;

		$odb = $this->ConnWhenNeeded($bRW); //载入数据库连接资源对象

		$this->startTimer();
		$oResult = mysql_query($sSql, $odb); //无缓存直接输出
		$this->addSqlHistory($sSql);
		if ($oResult === false)
			$this->showSqlErr(mysql_errno($odb), mysql_error($odb));//SQL语句有问题
		else{ //执行闭包函数
			if (mysql_num_rows($oResult) > 0){
				while (($row = mysql_fetch_array($oResult, MYSQL_NUM)) !== false)
					$callFunc($row);
				mysql_free_result($oResult); //释放临时数据集
			}
		}
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::procedure()
	*/
	public function procedure($sProcName, $aParam, $bOutResult){
		return null;
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::showApiVer()
	 */
	public function showApiVer(){
		$odb = $this->ConnWhenNeeded();
		return 'mysql['. $this->getDbType() .' protocol version:'. mysql_get_proto_info($odb) .']';
	}

	/* (non-PHPdoc)
	 * @see IDbDriver::getDbType()
	 */
	public function getDbType(){
		return $this->msDbType;
	}
}

?>