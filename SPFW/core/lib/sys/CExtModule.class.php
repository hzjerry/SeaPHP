<?php
defined('SEA_PHP_RUNTIME') or exit('sea php framework initialization step is not valid.'); //功能:让框架必须按顺序加载
/**
 * 扩展功能模块的抽象类<br/>
 *   所有扩展类必须继承此类
 * @author Jerryli(hzjerry@gmail.com)
 * @abstract
 * @package SPFW.core.lib.sys
 * */
abstract class CExtModule
{
	/**
	 * 扩展模块的系统运行日志目录
	 * @var string
	 */
	const sRUN_LOG_PATH = 'log/core/extend/';
	/**
	 * 开始时间计数器
	 * @var float
	 */
	private $fBeginTime = 0;
	/**
	 * 开始时间
	 * @var string
	 */
	private $sBeginDatetime = null;
	/**
	 * 扩展模块的名称
	 * @var String
	 */
	protected $msModule = null;
	/**
	 * 运行日志数据
	 * @var array
	 */
	protected $maLogData = null;

	/**
	 * 构造函数
	 * @example
	 * <pre>
	 * //子类在构造函数中必须加入如下内容
	 * function __construct()
	 * {
	 *     parent::__construct(); //必须先运行父类的构造函数
	 * }
	 * </pre>
	 * */
	function __construct()
	{
		//TODO 需要完善日志（应该记录一些什么内容？）
// 		list($usec, $sec) = explode(' ', microtime());
// 		$this->fBeginTime = floatval($usec) + floatval($sec); //启动时间保存
// 		$this->sBeginDatetime = date('Y-m-d H:i:s'); //记录开始时间
		//上面的代码暂时不用

		$this->setName(); //设置模块的名称
		$this->autoloadProfile(); //载入自动加载类配置
	}

	/**
	 * 析构函数
	 * */
	function __destruct()
	{
		$this->saveRunLog(); //保存运行时日志
	}

	/**
	 * 外部组件的动态加载类配置文件合并函数<br/>
	 *   备注：载入的配置文件内结构必须如下所示<br/>
	 *   return array('类名'=>array('package'=>'core.lib.final', 'fail'=>'CDate.class.php'), ...);<br/>
	 *   样例请参照: SPFW/core/config/autoload.cfg.php
	 *
	 * @author Jerryli(hzjerry@gmail.com)
	 * @param string $sPackage 包路径
	 * @param string $sFilePath 文件名
	 * @return void
	 * @global SEA_PHP_FW_AUTOLOAD
	 * @access protected
	 */
	protected function merger2autoload($sPackage, $sFilePath)
	{
		static $sAUTOLOAD_VAR = 'SEA_PHP_FW_AUTOLOAD';
		$aTmp = import($sPackage, $sFilePath);
		if (isset($GLOBALS[$sAUTOLOAD_VAR]))
		{	//将新加入的配置信息合并Key重复则丢弃新加入的这个Key值
			$aAutoLoadKey = array_keys($GLOBALS[$sAUTOLOAD_VAR]);
			foreach ($aTmp as $sKey => $Val)
			{
				if (in_array($sKey, $aAutoLoadKey))
					continue;
				else
					$GLOBALS[$sAUTOLOAD_VAR][$sKey] = $Val;
			}
			unset($aAutoLoadKey);
		}
		else //数组为空，则直接赋值
			$GLOBALS[$sAUTOLOAD_VAR] = $aTmp;
	}

	/**
	 * 设定需要保存的日志内容<br />
	 * 返回：一维护数组；如果返回的是数组会以每项为一行，写入log.core.extend.[$sModule]目录每天生成一个.php文件
	 * @param array $aArr 日志记录(一维数组)array('log1', 'log2',...)
	 * @return array | null
	 * @access protected
	 * @abstract
	 * */
	protected function setSaveLogData($aArr=null)
	{
		$this->maLogData = $aArr;
	}

	/**
	 * 保存运行日志<br />
	 * 备注：如果运行结束时，有日志保存需求输出，则自动保存日志<br />
	 * 注意：日志文件会以.php文件方式保存，并在首行加入<?exit();?>，以防止通过Web方式在浏览器端被打开或下载
	 * @return void
	 * @access private
	 */
	private function saveRunLog()
	{
		if (empty($this->maLogData) || !is_array($this->maLogData))
			return ;
		elseif (empty($this->msModule))
			return ;

		$sPath = getMAC_ROOT() . getFW_ROOT() . self::sRUN_LOG_PATH . $this->msModule .'/'. date('Ym') .'/';
		if (!file_exists($sPath))
		{	//日志目录不存在,创建
			if (!CFileOperation::creatDir($sPath))
			{
				echo 'Expansion module running log creation failed.';
				exit(0);
			}
		}

		//写入日志文件
		$sFile = 'log_'. date('ymd') .'.php';
		if (!file_exists($sPath . $sFile))
		{	//新建文件
			if (($hf = fopen($sPath . $sFile, 'wb')) !== false)
				$this->maLogData = array_merge(array('<?php exit(0);?>'), $this->maLogData);
			else
			{
				echo 'Unable to create log file[', $sPath, $sFile ,'].',
				"\n", dbg::toString(dbg::TRACE());
				exit(0);
			}
		}
		else
		{	//追加方式写入文件
			if (($hf = fopen($sPath . $sFile, 'ab')) === false)
			{	//无法创建日志文件
				echo 'Unable to create log file[', $sPath, $sFile ,'].',
				"\n", dbg::toString(dbg::TRACE());
				exit(0);
			}
		}
		if (!fwrite($hf, implode("\n", $this->maLogData) ."\n"))
		{	//日志写入失败
			echo 'Log file is written to the failure.[', $sPath, $sFile ,'].',
			"\n", dbg::toString(dbg::TRACE());
			exit(0);
		}
		fclose($hf); //日志成功写入
	}

	/**
	 * 自动加载配置文件<br/>
	 *  调用基类中的merger2autoload()方法可将需要合并到自动加载类的配置文件合并到内核
	 * @return void
	 * @access protected
	 * @abstract
	 * @example<pre>
	 * protected function autoloadProfile()
	 * {
	 *     $this->merger2autoload('extend.webservice.config', 'autoload.cfg.php');
	 * }
	 * </pre>
	 * */
	abstract protected function autoloadProfile();

	/**
	 * 设置当前模块的名称
	 *  用于生成模块访问日志文件用
	 * @param string $sModule 当前模块的类名
	 * @return void
	 * @access protected
	 * @abstract
	 * @example<pre>
	 * protected function setName($sModule=__CLASS__)
	 * {
	 *     $this->msModule = $sModule;
	 * }
	 * </pre>
	 * */
	abstract protected function setName($sModule=__CLASS__);

	/**
	 *  检查当前的系统环境是否能够运行本模块或框架<br />
	 *  返回:array('key'=>bool,...); key用于显示检测报告的名称,值为是否能运行
	 * @return array
	 * @access public
	 * @abstract
	 * */
	abstract public function isAbleRun();
}
?>