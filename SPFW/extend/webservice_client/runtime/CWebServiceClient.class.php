<?php
/**
 * Sea PHP WebService专用的Client接口框架<br/>
 * 【注意】:只支持Sea PHP WebService的接口<br/>
 * 接口业务逻辑文件存在workgroup.webservice_client下，可在environment.cfg.php中配置。<br/>
 * 接口业务逻辑文件名与类名的命名方式: pkg +'.'+ class，然后将'.'全部替换成'_'(目的是防止全局空间中的类重名)
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140702
 * @package SPFW.extend.webservice_client.runtime
 * @final
 * @example 对Sea Php的WebService接口调用方式
 * <pre>
 * $o = new CWebServiceClient('CWscCfgTest');
 * $o->openDebug();//打印调试参数
 * $aParam = array(...);
 * _dbg($o->exec('develop.test', 'GET_USER_INFO', $aParam));
 * </pre>
 *
 * */
class CWebServiceClient extends CExtModule{
	/**
	 * 接口实例逻辑文件根目录
	 * @var string
	 */
	private static $_sWorkgroup = null;
	/**
	 * 远程接口的访问地址
	 * @var string
	 */
	private $_sWSC_URL = null;
	/**
	 * 接口的公钥
	 * @var string
	 */
	private $_aPubKeys = null;
	/**
	 * posthttp交互对象
	 * @var CPostHttp
	 */
	private static $_oPost = null;
	/**
	 * XmlArray解析对象
	 * @var CXmlArrayConver
	 */
	private static $_oXmlConv = null;
	/**
	 * 时差校正值
	 * @var int
	 */
	private static $_iCalibrate = null;

	/**
	 * 构造函数<br />
	 * @param string $sCfgName WebService连接配置文件
	 */
	public function __construct($sCfgName){
		parent::__construct();
		$oCfg = import('extend.webservice_client.config', $sCfgName.'.php', $sCfgName);
		if (!empty($oCfg)){
			$this->_sWSC_URL = $oCfg->getUrl();
			$this->_aPubKeys = $oCfg->getPublicKey();
		}else{
			CErrThrow::throwExit('Load cfg fail.', '['. $sCfgName .']does not exist');
		}
		unset($oCfg);
		$oCfg = null;

		if (empty(self::$_oPost)){ //创建通信对象
			self::$_oPost = new CPostHttp();
			self::$_oPost->setPostType('xml');
		}
		if (empty(self::$_oXmlConv)) //创建XmlArray转换对象
			self::$_oXmlConv = new CXmlArrayConver('boot', 'utf-8');

		$this->loagCfg(); //载入全局配置信息
	}

	/**
	 * 析构函数
	 * @see CExtModel::__destruct()
	 */
	function __destruct()
	{
		parent::__destruct(); //必须要调用父类析构函数
	}

	/* !CodeTemplates.overridecomment.nonjd!
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile() {
		$this->merger2autoload('extend.webservice_client.config', 'autoload.cfg.php');
	}

	/* !CodeTemplates.overridecomment.nonjd!
	 * @see CExtModule::setName()
	 */
	protected function setName($sModule = __CLASS__) {
		$this->msModule = $sModule;
	}

	/* !CodeTemplates.overridecomment.nonjd!
	 * @see CExtModule::isAbleRun()
	 */
	public function isAbleRun() {
		$aRet = array();
		if (!function_exists('curl_init'))
			$aRet['CWebServiceClient (curl_init)'] = false;
		else
			$aRet['CWebServiceClient (curl_init)'] = true;
		return $aRet;
	}

	/**
	 * 载入环境配置文件
	 * @return void
	 * @access private
	 */
	private function loagCfg(){
		if (is_null(self::$_sWorkgroup)){//防止多次载入，使用静态变量。
			$aCfg = import('extend.webservice_client.config', 'environment.cfg.php');
			self::$_sWorkgroup = str_replace('.', '/', $aCfg['logic_workgroup']) .'/';//业务逻辑程序目录
			if (null === self::$_iCalibrate && $aCfg['time_calibrate']){ //开启时差校验
				self::$_iCalibrate = $this->getTimedifference();
			}else{
				self::$_iCalibrate = 0;
			}
			unset($aCfg);
		}
	}

	/**
	 * 打开调试模式<br/>
	 * 通信时会将数据直接打印到屏幕
	 * @return void
	 * @access public
	 */
	public function openDebug()
	{
		self::$_oPost->showDebug();
		self::$_oPost->showError();
		self::$_oXmlConv->setShowFormat(true);
	}

	/**
	 * 执行接口指令
	 * @param string $sPkg 接口的包路径
	 * @param string $sCls 接口的执行类
	 * @param array $aParam 接口参数
	 * @return array | null 返回null表示接口工作不正常
	 * @access public
	 */
	public function exec($sPkg, $sCls, $aParam){
		//根据$sPkg与$sCls定位业务逻辑文件
		$sClass = str_replace('.', '_', $sPkg .'.'. $sCls);
		if (!class_exists($sClass, false)){ //类未加载，加载类
			$sFilePath = getMAC_ROOT() . getFW_ROOT() . self::$_sWorkgroup . $sClass .'.php';
			if(file_exists($sFilePath))
				require_once $sFilePath; //加载类文件
			else{
				CErrThrow::throwExit('file not find.', '['. $sFilePath .']does not exist');
			}
		}
		//实例化接口
		$oLogic = new $sClass();
		if (null !== $aParam)
			$aParam = $oLogic->inParamInit($aParam);//获取提交的xml结构数组
		else
			$aParam = null;
		//协议头构造
		$iPkgLen = strlen($sPkg);//包名长度
		$sPKey = null;//公钥的值
		foreach($this->_aPubKeys as $sPKName => $sKeyVal){
			if ($sPkg === $sPKName){
				$sPKey = $sKeyVal;
				break;
			}else{
				$iPKeyLen = strlen($sPKName);//待比较的包名称长度
				if ($iPKeyLen < $iPkgLen && substr($sPkg, 0, $iPKeyLen) === $sPKName)
					$sPKey = $sKeyVal; //找到一级匹配路径
			}
		}
		if (empty($sPKey)){ //未发现可用公钥，运行终止
			CErrThrow::throwExit('key not find.', 'The public key is not found effective');
		}
		$sTimestemp = strval(time()+self::$_iCalibrate);//计算时间戳
		$sChecksum = array('value'=>md5($sPkg . $sCls . $sTimestemp . $sPKey), 'unix_timestamp'=>$sTimestemp);
		$aXml = array(
			'package'=>array('C'=>$sPkg),
			'class'=>array('C'=>$sCls),
			'checksum'=>CXmlArrayNode::createNode(null, $sChecksum)
		);
		if (!empty($aParam)) //合并数组
			$aXml = array_merge($aXml, $aParam);
		//提交数据到接口
		self::$_oPost->setPostData(self::$_oXmlConv->getStrPacket($aXml));
		unset($aXml);
		if (self::$_oPost->post($this->_sWSC_URL)){
			//解析成功送回XML结构数组进行返回处理
			$aRet = $oLogic->retParam(self::$_oXmlConv->getArray(self::$_oPost->getContent()));
			unset($oLogic);
			$oLogic = null;
			return $aRet;
		}else{ //接口工作不正常
			unset($oLogic);
			$oLogic = null;
		}
	}

	/**
	 * 矫正时差(与WebServie的公共接口校对时差)<br />
	 * 返回:正数表示比Server快，负数表示比Server慢;
	 * @return int
	 * @access private
	 */
	private function getTimedifference(){
		$aXml = array(
			'package'	=>array('C'=>'system.public'),
			'class'		=>array('C'=>'GET_TIME_DIFFERENCE'),
			'datetime'	=>array('C'=>date('Y-m-d H:i:s')),
		);
		self::$_oPost->setPostData(self::$_oXmlConv->getStrPacket($aXml));
		unset($aXml);
		if (self::$_oPost->post($this->_sWSC_URL)){ //取得矫正值
			$aParam = self::$_oXmlConv->getArray(self::$_oPost->getContent());
			if (isset($aParam['result']['A']['value']) && $aParam['result']['A']['value'] === '0000')
				return intval($aParam['deviation']['C']);
		}else{ //获取失败
			return 0;
		}
	}
}
?>