<?php
/**
 * WebService的Service单一入口处理类<br/>
 *  依赖：SPWF.core.lib.final包的CConverCharset类,CENV类<br/>
 *  checksum算法: md5(package + class + timestemp(10) + package_pwd)
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130413
 * @package SPFW.extend.webservice.runtime
 * @final
 * @example 协议入口的基本参数
 * <pre>
 * &lt;boot&gt;
 * &nbsp;&nbsp;&lt;package&gt;访问包路径(无大小写限制)&lt;/package&gt;
 * &nbsp;&nbsp;&lt;class&gt;执行的类名(约定必须用大写)&lt;/class&gt;
 * &nbsp;&nbsp;&lt;checksum value='md5(32)' timestemp='YYYYMMDDHHIISS'/&gt;
 * &lt;/boot&gt;
 * </pre>
 *
 * */
final class CWebService extends CExtModule implements IExtFramework
{
	/**
	 * 开放包的包路径（这个包路径下的API服务接口，不需要做checksum验证）
	 * @var string
	 */
	const PUBLIC_PACKAGE = 'system.public';
	/**
	 * XmlArray输入数据容器（保存从POST得到的数据解析结果）
	 * @var array
	 */
	private $maInXML = null;
	/**
	 * XmlArray输出数据容器（保存即将发送的数据）
	 * @var array
	 */
	private $maOutXML = null;
	/**
	 * 日志记录 标志
	 * @var bool
	 */
	private $mbWriteLog = false;
	/**
	 * 日志中记录Xml结构数组信息 标志
	 * @var bool
	 */
	private $mbWriteArrayLog = false;
	/**
	 * 输出数据格式化 标志
	 * @var bool
	 */
	private $mbResultFormat = false;
	/**
	 * Xml使用的字符集
	 * @var string
	 */
	private $msXmlCharset = null;
	/**
	 * 根节点名称
	 * @var string
	 */
	public $msRootNode = null;
	/**
	 * 返回值标签的名称
	 * @var string
	 */
	public $msResultNode = null;
	/**
	 * 日志文件目录
	 * @var string
	 */
	private $msLogPath = null;
	/**
	 * 逻辑文件日志
	 * @var string
	 */
	private $msLogicPath = null;
	/**
	 * 是否做checksum校验检查
	 * @var bool
	 */
	private $mbDoChecksum = false;
	/**
	 * 得到的post数据
	 * @var string
	 */
	private $msPostData = null;
	/**
	 * 送出的返回数据
	 * @var string
	 */
	private $msResultData = null;
	/**
	 * 系统级状态值定义
	 * @var array()
	 */
	static public $maResultStatus = array
	(
		'999'=>'There is no post data.(不存在post数据)',
		'901'=>'Received protocol packets can not be resolved.(收到的协议包无法解析)',
		'910'=>'Invalid input.(无效输入)',
		'911'=>'Missing package and class node value.(缺少package与class节点值)',
		'912'=>'checksum value attribute node does not exist.(checksum节点的value属性不存在)',
		'913'=>'package, class can not be empty.(缺少package或class节点)',
		'914'=>'The checksum validation did not pass.(checksum校验未通过)',
		'915'=>'The package access password is not set Web Service.(Web Service 中未设置这个package访问密码)',
		'916'=>'API interface class not found.(api接口服务类未找到)',
		'917'=>'API interface services no output result set.(api接口服务无输出结果集)',
		'918'=>'checksum verification fails, the server exceeds plus or minus one hour time difference.(checksum校验未通过,与服务器时差超出正负1小时)',
		'919'=>'checksum unix_timestamp attribute node does not exist.(checksum节点的unix_timestamp属性不存在)',
		'920'=>'checksum node does not exist.(checksum节点不存在)',
	);
	/**
	 * 通信协议选择<br/>
	 *  值:[xml|json]
	 * @var string
	 */
	public $msProtocol = 'xml';
	/**
	 * XML结构数组协议转换包对象
	 * @var IXmlJsonConverArray
	 */
	public $moXmlArray = null;
	/**
	 * 从解析包中获得的package信息
	 * @var string
	 */
	private $msPackage = null;
	/**
	 * 从解析包中获得的class信息
	 * @var string
	 */
	private $msClass = null;

	/**
	 * 构造函数
	 * @see CExtModule::__construct()
	 */
	function __construct()
	{
		parent::__construct(); //必须先运行父类的构造函数
		$this->loadConfig(); //载入配置信息
		if (isset($_GET['format_auto']) && $_GET['format_auto'] == 'true')
			$this->mbResultFormat = true; //内部隐含参数(不对外公开)，由ProtocolView传入，显示格式化的返回信息
		if (isset($_GET['protocol_type']) && $_GET['protocol_type'] == 'json')
			$this->setProtocol('json'); //设定协议
		else
			$this->setProtocol('xml'); //设定协议

	}

	/**
	 * 析构函数
	 * @see CExtModel::__destruct()
	 */
	function __destruct()
	{
		unset($this->moXmlArray);
		if (!is_null($this->maInXML))
			unset($this->maInXML);
		if (!is_null($this->maOutXML))
			unset($this->maOutXML);
		parent::__destruct(); //必须要调用父类析构函数
	}

	/*
	 * (non-PHPdoc)
	 * @see CExtModule::isAbleRun()
	 */
	public function isAbleRun()
	{
		$aRet = array();
		if (!function_exists('json_encode') || !function_exists('json_decode'))
			$aRet['CWebService (json_encode|json_decode)'] = false;
		else
			$aRet['CWebService (json_encode|json_decode)'] = true;

		if (!class_exists('SimpleXMLElement'))
			$aRet['CWebService (SimpleXMLElement)'] = false;
		else
			$aRet['CWebService (SimpleXMLElement)'] = true;

		/*日志目录能否写入*/
		$sPath = getMAC_ROOT(). getFW_ROOT() . strtr($this->msLogPath, array('.'=>'/'));
		if ((0x02 & CFileOperation::file_mode_info($sPath)) === 0)
			$aRet['CWebService (Write permissions: '. $this->msLogPath .')'] = false;
		else
			$aRet['CWebService (Write permissions: '. $this->msLogPath .')'] = true;
		return $aRet;
	}

	/*
	 * (non-PHPdoc)
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile()
	{	//加载本框架的自动加载类
		$this->merger2autoload('extend.webservice.config', 'autoload.cfg.php');
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
		$aCfg = import('extend.webservice.config', 'environment.cfg.php');
		$this->mbWriteLog = $aCfg['write_log'];
		$this->mbWriteArrayLog = $aCfg['write_log_array'];
		$this->msXmlCharset = strtolower($aCfg['xml_charset']);
		$this->msRootNode = strtolower($aCfg['xml_root_node']);
		$this->msLogPath = $aCfg['log_path'];
		$this->msLogicPath = $aCfg['logic_api_service'];
		$this->msResultNode = strtolower($aCfg['result_note_name']);
		$this->mbResultFormat = $aCfg['format_result_data'];
		$this->mbDoChecksum = $aCfg['do_checksum'];
		unset($aCfg);
	}

	/*
	 * (non-PHPdoc)
	 * 运行入口
	 * @see IExtFramework::start()
	*/
	public function start()
	{
		$this->entrance(); //入口参数检查
		$this->checkSecurity(); //安全访问检查
		$this->adapter(); //启动适配器
		$this->writeLog(); //记录运行日志
	}

	/**
	 * 设定传输时使用的通信协议类型
	 * @param string $sType 协议[ xml | json ]
	 * @return void
	 * @access public
	 */
	public function setProtocol($sType)
	{
		if ('json' == $sType)
		{
			$this->msProtocol = 'json';
			// TODO JSON数组解析处理
			$this->moXmlArray = new CJsonArrayConver($this->msRootNode, $this->msXmlCharset);
		}
		else
		{
			$this->msProtocol = 'xml';
			$this->moXmlArray = new CXmlArrayConver($this->msRootNode, $this->msXmlCharset);
		}

		if ($this->mbResultFormat) //判断是否要打开送回的数据进行格式化
			$this->moXmlArray->setShowFormat(true);
	}

	/**
	 * api服务入口处理
	 * @return boolean
	 * @access private
	 */
	private function entrance()
	{
		/*判断是否取到了get内容*/
		$sGET = (empty($_SERVER['QUERY_STRING']))? null : $_SERVER['QUERY_STRING'];
		if (empty($sGET))
			$sGET = null;
		else
		{
			$oXmlGet = new CXmlArrayConverGET($this->msRootNode, $this->msXmlCharset);
			$this->maInXML = $oXmlGet->getArray($sGET);
			if (!isset($this->maInXML['package']['C']) && !isset($this->maInXML['class']['C']))
			{	//不存在适配节点，表示Get上没有取到数据包
				unset($this->maInXML);
				$this->maInXML = null;
			}
			else
			{
				if (isset($this->maInXML['protocol_type']))//移除系统专用的节点名称
					unset($this->maInXML['protocol_type']);
				$this->msPostData = $sGET; //记录收到的QUERY_STRING数据
// 				$this->msPostData = $this->moXmlArray->getStrPacket($this->maInXML); //将收到的数据翻译成对应的协议用于保存
			}
		}

		/*如果GET数据未取到数据时，则检查是否取到POST的数据*/
		if (empty($this->maInXML))
		{	//Get没有取到数据时，使用POST的信息
			$this->msPostData = file_get_contents("php://input");
			if (empty($this->msPostData))
				$this->throwState('910'); //未收到数据
			$this->maInXML = $this->moXmlArray->getArray($this->msPostData);
		}

		if(empty($this->maInXML))
			$this->throwState('901');//数据包解析失败
		elseif (!isset($this->maInXML['package']['C']) || !isset($this->maInXML['class']['C']))
			$this->throwState('911'); //缺少package与class节点值
		elseif ($this->maInXML['package']['C'] != self::PUBLIC_PACKAGE) //system.public下的所有节点，不做checksum检查
		{
			if ($this->mbDoChecksum)
			{
				if (!isset($this->maInXML['checksum']))
					$this->throwState('920'); //缺少checksum节点
				elseif (!isset($this->maInXML['checksum']['A']['value']))
					$this->throwState('912'); //缺少checksum节点的value属性内容
				elseif (!isset($this->maInXML['checksum']['A']['unix_timestamp']))
					$this->throwState('919'); //缺少checksum节点的unix_timestamp属性内容
			}
		}

		//取出适配路由信息
		$this->msPackage = $this->maInXML['package']['C'];
		$this->msClass = $this->maInXML['class']['C'];
		unset($this->maInXML['package'], $this->maInXML['class']); //释放节点资源
	}

	/**
	 * 业务逻辑适配器
	 * @return void
	 * @access private
	 */
	private function adapter()
	{
		//生成接口文件的访问路径
		$sRootPackage = 'workgroup.webservice.package.'. $this->msPackage;
		$sPath = getMAC_ROOT() .getFW_ROOT() . str_replace('.', '/', $sRootPackage) .'/';
		if (file_exists($sPath . $this->msClass .'.php'))
		{
			$this->maOutXML = $this->doLogic(import($sRootPackage, $this->msClass .'.php', $this->msClass));
			if (empty($this->maOutXML))
				$this->throwState('917'); //没有输出结果集
			else
				$this->output($this->maOutXML); //送出结果集
		}
		else
			$this->throwState('916'); //未找到逻辑类
	}

	/**
	 * 执行API的业务逻辑
	 * @param CWebServiceApiLogic $oLogic 需要执行的逻辑对象
	 * @return array 送出的XML数组对象
	 * @access private
	 */
	private function doLogic(CWebServiceApiLogic $oLogic)
	{
		$oLogic->run($this->maInXML);
		return $oLogic->getResult();
	}

	/**
	 * 输出结果集
	 * @param array $aXml XML结构数组
	 * @return void
	 * @access private
	 */
	private function output(& $aXml)
	{
		//配合文件头的内核框架的runtime.php中开启的ob_start()，阻止调试信息的干扰
		ob_end_clean(); //清除输出缓存数据
		header('Content-Type: application/'. $this->msProtocol .'; charset='. $this->msXmlCharset);
		$this->msResultData = $this->moXmlArray->getStrPacket($aXml); //存储需要发送的数据包
		echo $this->msResultData;
		$this->writeLog();//记录访问日志
		exit();
	}

	/**
	 * 访问安全检查（检查checksum值的有效性）<br/>
	 *  checksum算法: md5(package+class+timestemp(10)+package_pwd)<br/>
	 *  package: system.public这个包目录中的所有接口，不进行checksum验证，系统的公开接口
	 * @return boolean
	 * @access private
	 */
	private function checkSecurity()
	{
		if (!$this->mbDoChecksum)
			return true; //跳过安全检查,不做处理

		if (empty($this->msPackage) || empty($this->msClass))
			$this->throwState('913');
		elseif ($this->msPackage == self::PUBLIC_PACKAGE)
			return true; //系统公开接口，不进行安全验证
		else
		{	//开始对checksum进行校验
			$aPwds = import('extend.webservice.config', 'package_access_pwd.cfg.php'); //载入包密码验证配置
			$iPackageLen = strlen($this->msPackage); //取出访问包长度
			$sPackagePwd = null;
			foreach ($aPwds as $sKey=>$sPwd)
			{	//从包的访问密码配置文件中能找出对应的访问密码
				$iKey = strlen($sKey); //获取密码验证用的包名长度
				if ($iKey > $iPackageLen)
					continue;
				elseif (substr($this->msPackage, 0, $iKey) == $sKey)
				{	//找到匹配的package密码
					$sPackagePwd = $sPwd;
					break;
				}
			}
			unset($aPwds);
			if (empty($sPackagePwd)) //未找到访问密码
				$this->throwState('915');

			//系统会生成 正负一小时的验证码，做最多3次校验
			$sCheckSum = $this->maInXML['checksum']['A']['value']; //取出checksum
			$iUnixTimestamp = intval($this->maInXML['checksum']['A']['unix_timestamp']); //取出unix_timestamp
			if ($sCheckSum == md5($this->msPackage . $this->msClass . $iUnixTimestamp . $sPackagePwd))
			{	//校验第一步通过
				if (CValCheck::CK(time()-$iUnixTimestamp, array('range'=>array(-3600, 3600))) ) //允许1小时误差
					return true; //校验通过
				else
					$this->throwState('918'); //误差超出正负1小时
			}
			else
				$this->throwState('914');//校验失败抛出错误提示
		}
	}

	/**
	 * 抛出状态值，并且终止服务
	 * @param string $sCode 状态码
	 * @return void
	 */
	private function throwState($sCode)
	{
		$aXml = array();
		$aXml['server']['A'] = array('unix_timestamp'=>time(), 'datetime'=>date('Y-m-d H:i:s'), 'runtime'=>CENV::getRuntime());
		if (array_key_exists($sCode, self::$maResultStatus))
			$aXml['result']['A'] = array('value'=>$sCode, 'msg'=>self::$maResultStatus[$sCode]);
		else
			$aXml['result']['A'] = array('value'=>$sCode, 'msg'=>'Undefined error code(未定义的错误代码)');
		$this->output($aXml);
	}

	/**
	 * 写日志<br/>
	 *  备注:日志目录 $this->msLogPath ./package/[yyyymm]/[dd]_class.php
	 * @return void
	 * @access private
	 */
	private function writeLog()
	{
		if (!$this->mbWriteLog)
			return ; //不写入日志

		$sPath = getMAC_ROOT() . getFW_ROOT() . strtr($this->msLogPath, array('.'=>'/')) .'/';
		//检查日志目录是否存在
		if (!file_exists($sPath))
		{
			if (!CFileOperation::creatDir($sPath)) //尝试创建日志目录，失败时报错
			{
				echo 'Log file directory does not exist.[', $sPath ,'].',
				"\n", dbg::toString(dbg::TRACE());
				exit(0);
			}
		}

		//开始建立日志内容
		$aBuf = array();
		$aBuf[] = str_pad('', 15, '#') ."\n";
		array_push($aBuf, 'in time: ', date('Y-m-d H:i:s'), "\n");
		array_push($aBuf, 'run time: ', round(CENV::getRuntime(), 4), ' ms', "\n");
		array_push($aBuf, 'memory peak usage: ', ceil(memory_get_peak_usage()/1000), "kb\n");
		array_push($aBuf, 'ip: ', CENV::getIP(), "\n");

		if (!empty($this->msPackage) && !empty($this->msClass) )
		{	//有效访问的处理
			$sPath .= $this->msPackage .'/'. date('Ym') .'/';
			CFileOperation::creatDir($sPath); //检查日志目录，如果不存在则创建
			$sFile = date('d') .'_'. $this->msClass .'.php'; //生成日志文件名
			//日志内容
			array_push($aBuf, 'adapter: ', $this->msPackage, '->', $this->msClass, "\n");
			if (isset($_SERVER['HTTP_REFERER']))
				array_push($aBuf, 'url: ', $_SERVER['HTTP_REFERER'], "\n");
			array_push($aBuf, 'protocol type: ', $this->msProtocol, "\n");
			array_push($aBuf, '[input data: ', strlen($this->msPostData),' byte]----', "\n", $this->msPostData, "\n\n");
			array_push($aBuf, '[output data: ', strlen($this->msResultData),' byte]----', "\n", $this->msResultData, "\n\n");
			if ($this->mbWriteArrayLog === true)
			{	//记录Xml结构数组信息
				array_push($aBuf, '[input array]', dbg::toString($this->maInXML), "\n");
				array_push($aBuf, '[output array]', dbg::toString($this->maOutXML), "\n");
			}
			$aBuf[] = "\n\n";
		}
		else
		{	//无效访问的处理
			$sFile = 'root_'. date('Ymd') .'.php'; //生成文件名
			if (isset($_SERVER['HTTP_REFERER']))
				array_push($aBuf, 'url: ', $_SERVER['HTTP_REFERER'], "\n");
			if (!empty($this->msPostData) || !empty($this->msPostData))
				array_push($aBuf, '[', $this->msProtocol, ']', "\n");
			if (!empty($this->msPostData))
				array_push($aBuf, '[input data: ', strlen($this->msPostData),' byte]----', "\n", $this->msPostData, "\n");
			if (!empty($this->msPostData))
				array_push($aBuf, '[output data: ', strlen($this->msResultData),' byte]----', "\n", $this->msResultData, "\n");
			$aBuf[] = "\n\n";
		}

		if (!file_exists($sPath . $sFile)) //文件不存在时，加入防止显示的头
			$aBuf = array_merge(array('<?php exit(0);?>', "\n"), $aBuf);

		if (($hf = fopen($sPath . $sFile, 'ab')) !== false)
		{
			if (!fwrite($hf, implode('', $aBuf)))
			{	//日志写入失败
				echo 'Log file is written to the failure.[', $sPath, $sFile ,'].',
					 "\n", dbg::toString(dbg::TRACE());
				exit(0);
			}
			fclose($hf); //日志成功写入
		}
		else
		{	//无法创建日志文件
			echo 'Unable to create log file[', $sPath, $sFile ,'].',
				 "\n", dbg::toString(dbg::TRACE());
			exit(0);
		}
	}

	/**
	 * 时差校正，返回加上时差的时间
	 * @param int $iNum 增减的秒数
	 * @return timestemp
	 * @static
	 * @access private
	 */
	static private function TimeDeviation($iNum=0)
	{
		$iS     = intval(date('s')) + intval($iNum);
		$itemp  = mktime(date('H'),date('i'),$iS,date('m'),date('d'),date('Y'));
		return date("YmdHis",$itemp);
	}
}
?>