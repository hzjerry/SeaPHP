<?php
/**
 * 微信向导服务类(专用于处理微信的请求响应)
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140807
 * @package SPFW.extend.weixin_service.runtime
 * @final
 *
 */
final class CWeiXinHelper extends CExtModule implements IExtFramework{
	/**
	 * 逻辑服务类包路径
	 * @var string
	 */
	private $_sLogicPkg = null;
	/**
	 * 是否记录日志
	 * @var boolean
	 */
	private $_bWriteLog = false;
	/**
	 * 日志缓存
	 * @var array()
	 */
	private $_aLogBuf = array();
	/**
	 * 首次绑定用的授权Token
	 * @var string
	 */
	private $_sVerifyToken = null;
	/**
	 * 构造函数
	 * @see CExtModule::__construct()
	 */
	function __construct(){
		parent::__construct(); //必须先运行父类的构造函数
		$this->loadConfig(); //载入配置信息
		ob_start();//启动输出缓存捕获
	}
	/**
	 * 析构函数
	 * @see CExtModel::__destruct()
	 */
	function __destruct(){
		if ($this->_bWriteLog)
			$this->setSaveLogData($this->_aLogBuf); //输出日志

		parent::__destruct(); //必须要调用父类析构函数
	}
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile() {
		$this->merger2autoload('extend.weixin_service.config', 'autoload.cfg.php');
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
		return array();
	}
	/**
	 * 载入环境配置
	 * @return void
	 */
	private function loadConfig()
	{
		$aCfg = import('extend.weixin_service.config', 'environment.cfg.php');
		$this->_sLogicPkg = $aCfg['logic_workgroup'];
		$this->_bWriteLog = $aCfg['write_log'];
		$this->_sVerifyToken = $aCfg['verify_token'];
		unset($aCfg);
	}
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see IExtFramework::start()
	 */
	public function start() {
		$this->visitVerify(); //访问验证检查（对于非法访问，到这儿就会终止运行）

		$sRecData = file_get_contents("php://input");
		if (!empty($sRecData)){
			//解析xml
			$oXml = new CXmlArrayConver();
			if(null !== ($aXml = $oXml->getArray($sRecData))){
				unset($oXml);
				//保存收到数据
				$this->_aLogBuf[] = '==========>';
				$this->_aLogBuf[] = 'ip: '. CENV::getIP();
				$this->_aLogBuf[] = 'in: '. date('Y-m-d H:i:s');
				$this->_aLogBuf[] = 'receive data('. strlen($sRecData) .' byte):';
				$this->_aLogBuf[] = $sRecData;unset($sRecData);
				$this->adapterLogic($aXml);
			}else //无效的XML结构
				CErrThrow::throwExit('000003', 'Invalid XML structure, denial of service.');

		}else //不存在POST数据
			CErrThrow::throwExit('000002', 'No POST data, denial of service.');
	}
	/**
	 * 微信访问验证（含首次绑定验证）
	 * @return void
	 */
	public function visitVerify(){
		if (isset($_GET['signature'])){
			$signature = $_GET['signature'];
			$timestamp = $_GET['timestamp'];
			$nonce = $_GET['nonce'];
			$sDomain = (isset($_GET['e']))? $_GET['e'] : '';
			//验证规则 md5($sDomain . $this->_sVerifyToken)
			$aTmp = array(md5($sDomain . $this->_sVerifyToken), $timestamp, $nonce);
			sort($aTmp, SORT_STRING); //按照协议要求进行排序
			if (sha1(implode('', $aTmp)) === $_GET['signature']){//验证通过
				if (isset($_GET['echostr'])){
					echo $_GET['echostr'];//首次网址接入验证通过
					exit(0); //终止程序
				}
			}else //非微信的访问请求，直接拒绝
				CErrThrow::throwExit('000000', 'weixin bind verify fail. Denial of Service');
		}else //没有授权验证请求，拒绝提供服务
			CErrThrow::throwExit('000001', 'Warning: you have no right to use the Service.');
	}
	/**
	 * 逻辑适配
	 * @param array $sXml XML结构数组
	 */
	public function adapterLogic(& $aXml){
		$sMsgType = $aXml['msgtype']['C'];
		$sLogicClass = null;
		if ('text' === $sMsgType){//文本消息
			$sLogicClass = 'CWeiXinMsgTextLogic';
		}elseif ('image' === $sMsgType){//图片消息
			$sLogicClass = 'CWeiXinMsgImgLogic';
		}elseif ('voice' === $sMsgType){//语音消息
			$sLogicClass = 'CWeiXinMsgVoiceLogic';
		}elseif ('video' === $sMsgType){//视频消息
			$sLogicClass = 'CWeiXinMsgVideoLogic';
		}elseif ('location' === $sMsgType){//地理位置消息
			$sLogicClass = 'CWeiXinMsgLocationLogic';
		}elseif ('link' === $sMsgType){//链接消息
			$sLogicClass = 'CWeiXinMsgLinkLogic';
		}elseif ('event' === $sMsgType){//事件
			$sEvent = $aXml['event']['C'];
			if ('LOCATION' === $sEvent){//上报地理位置事件
				$sLogicClass = 'CWeiXinEventLocationLogic';
			}elseif (in_array($sEvent, array('subscribe', 'unsubscribe', 'SCAN', 'CLICK', 'VIEW'))){//一般信息事件
				$sLogicClass = 'CWeiXinEventMsgLogic';
			}elseif (in_array($sEvent, array('scancode_push', 'scancode_waitmsg'))){//菜单的扫码识别事件
				$sLogicClass = 'CWeiXinEventScanCodeLogic';
			}elseif (in_array($sEvent, array('location_select'))){//菜单弹出地理位置选择器的事件推送
				$sLogicClass = 'CWeiXinEventLocationSelectLogic';
			}
		}
		if (null !== $sLogicClass){ //命中适配类的处理
			$oResponse = import($this->_sLogicPkg, $sLogicClass.'.class.php', $sLogicClass);//载入业务逻辑类
			$oResponse->initParam($aXml);//初始化参数
			unset($aXml);
			if (null !==($oReply = $oResponse->doLogic())){ //输出响应
				$sXml = $oReply->toXml(); //取出响应内容
				echo $sXml;
				ob_end_flush(); //送出缓存
				$this->_aLogBuf[] = 'response data('. strlen($sXml) .' byte):';
				$this->_aLogBuf[] = $sXml;
				unset($sXml);
			}else{ //没有数据输出
				$this->_aLogBuf[] = 'response data(0 byte)';
			}
		}else{ //未命中适配类，不返回任何内容
			$this->_aLogBuf[] = 'Misses adapter class.';
		}
		$this->_aLogBuf[] = 'runtime: '. round(CENV::getRuntime(), 4) .' ms';
		$this->_aLogBuf[] = 'memory peak usage: '. ceil(memory_get_peak_usage()/1000) .' kb';
	}
}
?>