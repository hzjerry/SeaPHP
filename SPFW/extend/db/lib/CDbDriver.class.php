<?php
/**
 * 数据库驱动抽象类<br/>
 * 备注：数据库驱动必须继承这个类
 * @author Jerryli(hzjerry@gmail.com)
 * @package SPFW.extend.db.lib
 * @abstract
 * @version V0.20130501
 * <li>V0.20140626 修改了microtime()参数，提高效率</li>
 */
abstract class CDbDriver
{
	/**
	 * 数据库类型 [MySql | Qracle | Sqlite]
	 * @var string
	 * @access protected
	 */
	protected $msDbType;

	/**
	 * 数据库连接诶使用的字符集 [utf-8 | gb2312 | gbk]
	 * @var string
	 */
	protected $msCharset;
	/**
	 * 主库连接关键字（用于建立缓存池）
	 * @var string
	 */
	protected $msRW_MD5 = null;
	/**
	 * 只读库连接关键字（用于建立缓存池）
	 * @var string
	 */
	protected $msR_MD5 = null;

	/**
	 * 遇到SQL错误时强制终止，并显示错误提示
	 * @var bool
	 */
	protected $mbErrShow = false;
	/**
	 * SQL执行日志<br />
	 * array((array('sql'=>'', 'time'=>''),...);
	 * @var array
	 */
	private $maSqlLog = array();
	/**
	 * 计时器变量(毫秒)
	 * @var float
	 */
	private $mfTimer=0;
	/**
	 * Select默认操作主库(true:操作主库|false:操作只读库)
	 * @var bool
	 */
	protected $mbSelectRW = true;

	/**
	 * Select默认操作库设定
	 * @param bool $bRW 默认操作主库[true:主库|false:只读库]
	 * @return void
	 * @access public
	 */
	public function setSelectRW($bRW)
	{
		$this->mbSelectRW = $bRW;
	}

	/**
	 * 设置遇到SQL错误时强制终止
	 * @return void
	 * @access public
	 */
	public function setSqlErrShow()
	{
		$this->mbErrShow = true;
	}

	/**
	 * 显示错误提示，并终止程序
	 * @param string $sCode
	 * @param string $sMsg
	 * @return void
	 * @access protected
	 */
	protected function showSqlErr($sCode, $sMsg)
	{
		if ($this->mbErrShow)
		{
			$aTmp = array_pop($this->maSqlLog);
			echo '<blockquote>',
				 '<font face="arial" size="2" color="ff0000">',
				 '<strong>SQL Error: </strong>',
				 '[<font color="000077">', $aTmp['sql'], '</font>]<br />',
				 '<strong>Error msg: </strong> ', '[<font color="000077">code:', $sCode, '<br />', $sMsg, '</font>]',
				 '</font>',
				 '<hr noshade color="dddddd" size="1" /></blockquote>';
			exit(0);
		}
	}

	/**
	 * 获取Sql执行指令的历史记录<br />
	 * 返回:
	 * @return void
	 * @access public
	 */
	public function showSqlHistory()
	{
		echo '<blockquote>';
		echo '<font face="arial" size="2" color="000099"><strong>SQL History:--</strong><ul>';
		foreach ($this->maSqlLog as $aNode)
			echo '<li>', $aNode['time'], ' ms, ', $aNode['sql'] ,'</li>';
		echo '</ul></font>';
		echo '</blockquote><hr noshade color="dddddd" size="1">';
	}

	/**
	 * 返回SQL执行日志记录<br />
	 * 返回:
	 * @return maSqlLog
	 * @access public
	 */
	public function getSqlLog()
	{
		return $this->maSqlLog;
	}

	/**
	 * 计时器开始计时
	 * @return void
	 * @access protected
	 */
	protected function startTimer()
	{
		$this->mfTimer = microtime(true);
	}

	/**
	 * 计时器终止，返回耗时
	 * @return float
	 * @access protected
	 */
	private function endTimer()
	{
		return round( (microtime(true) - $this->mfTimer) * 1000 , 4);
	}

	/**
	 * 记录SQL历史指令<br />
	 * 注意:必须先调用startTimer()初始化计时器，否则无法精确计时
	 * @param string $sSql
	 * @return void
	 * @access protected
	 */
	protected function addSqlHistory($sSql)
	{
		$this->maSqlLog[] = array('sql'=>$sSql, 'time'=>$this->endTimer());
	}

	/**
	 * 构造<br/>
	 * $aMaster : array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>'3306')<br />
	 * $aSlave : array(array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>3306), ...)
	 * @param string $sCharset 本地字符集 [utf8 | gb2312 | gbk]
	 * @param array $aMaster 主库连接配置
	 * @param array $aSlave 只读库连接配置
	 * @abstract
	 */
	abstract function __construct($sCharset, $aMaster, $aSlave);

	/**
	 * 数据库连接<br />
	 *  备注：请自行解决链接缓存池的问题，以防止建立多个数据库连接<br/>
	 *  请在实现类的构造函数中调用，不提供外部访问<br/>
	 *  返回: 数据库连接的资源或链接对象
	 * @param string $sHost 主机地址
	 * @param string $sUserName 用户名
	 * @param string $sPwd 密码
	 * @param string $sDataBaseName 数据库名
	 * @param int $iPort 端口号(3306)
	 * @return mixed
	 * @access protected
	 * @abstract
	 */
	abstract protected function connect($sHost, $sUserName, $sPwd, $sDataBaseName, $iPort=3306);

	/**
	 * 执行无返回记录集的SQL指令<br />
	 *  返回：影响的记录条数 | AUTO_INCREMENT的值<br />
	 *  不支持: select
	 * @param string $sSql SQL指令
	 * @param bool $bGetId [true:返回Insert后的自增ID]|[false:返回SQL影响的记录条数]
	 * @return int | null
	 * @access public
	 * @abstract
	 */
	abstract public function exec($sSql, $bGetId=false);

	/**
	 * 执行事务指令<br />
	 *  当整个指令集度执行成功后，才会提交，否则回滚。<br/>
	 *  只支持: insert | update | delete
	 * @param array $aSqls 指令集(一维数组)
	 * @return bool
	 * @access public
	 * @abstract
	 */
	abstract public function transaction($aSqls);

	/**
	 * 查询指令<br />
	 * 返回: 以二维数组格式，返回整个记录集<br />
	 * array(array('field1'=>'value', ...), ...)
	 * @param string $sSql SQL指令
	 * @param bool $bRW [true:读写库 | false:只读库 | null:系统默认值详见setSelectRW()]
	 * @return array | null
	 * @access public
	 * @abstract
	 * @see CDbDriver::setSelectRW()
	 */
	abstract public function query($sSql, $bRW = null);

	/**
	 * 查询指令(高性能查询)<br />
	 * 返回: 只返回第一条记录的第一个字段值<br />
	 * @param string $sSql SQL指令
	 * @param bool $bRW [true:读写库 | false:只读库 | null:系统默认值详见setSelectRW()]
	 * @return string | null
	 * @access public
	 * @abstract
	 * @see CDbDriver::setSelectRW()
	 */
	abstract public function queryOne($sSql, $bRW = null);

	/**
	 * 查询指令(高性能查询)<br />
	 * 返回: 只返回第一条记录的所有字段(一维数组)<br />
	 * array('field1'=>'value', ...)
	 * @param string $sSql SQL指令
	 * @param bool $bRW [true:读写库 | false:只读库 | null:系统默认值详见setSelectRW()]
	 * @return array
	 * @access public
	 * @abstract
	 * @see CDbDriver::setSelectRW()
	 */
	abstract public function queryFirstRow($sSql, $bRW = null);

	/**
	 * 查询指令(高性能查询)<br />
	 * 返回: 只返回第一列(一维数组)<br />
	 * array(value1, value2, ...)
	 * @param string $sSql SQL指令
	 * @param bool $bRW [true:读写库 | false:只读库 | null:系统默认值详见setSelectRW()]
	 * @return array
	 * @access public
	 * @abstract
	 * @see CDbDriver::setSelectRW()
	 */
	abstract public function queryFirstCol($sSql, $bRW = null);

	/**
	 * 查询指令(高性能查询)<br />
	 * 返回: 以二维数组格式，返回整个记录集<br />
	 * array(array('field1'=>'value',...),...)
	 * @param string $sSql SQL指令
	 * @param int $iPage 页码(&gt;=1)
	 * @param int $iPageSize 页大小
	 * @param bool $bRW [true:读写库 | false:只读库 | null:系统默认值详见setSelectRW()]
	 * @return array
	 * @access public
	 * @abstract
	 * @see CDbDriver::setSelectRW()
	 */
	abstract public function queryPage($sSql, $iPage, $iPageSize, $bRW = null);

	/**
	 * 返回数据库API的版本号
	 * @return string
	 * @access public
	 * @abstract
	 */
	abstract public function showApiVer();

	/**
	 * 获取数据库类型
	 * @return string
	 * @abstract
	 */
	public function getDbType()
	{
		return $this->msDbType;
	}
}

?>