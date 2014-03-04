<?php
/**
 * 本系统用于管理接口的协议，在编写每个接口文件时，会将接口的协议保存到接口文件中。<br/>
 *  本程序就是读取接口文件中的信息，给使用者提供如何使用接口的方法。<br/>
 *  依赖: CXmlArrayConver | CExtModule | IExtFramework | CENV::getCharset()
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130423
 * @package SPFW.extend.webservice.runtime
 * @final
 *
 * */
final class CWebServiceProtocolView extends CExtModule implements IExtFramework
{
	/**
	 * ProtocolView 版本号
	 * @var string
	 */
	const VER = '0.20130423';
	/**
	 * 模板文件package访问目录
	 * @var string
	 */
	const TEMPLATE = 'extend.webservice.template_protocol';
	/**
	 * 项目的名称
	 * @var string
	 */
	private $msProjectName = null;
	/**
	 * 根路径
	 * @var string
	 */
	private $msRootName = null;
	/**
	 * 模板的绝对物理位置根路径
	 * @var string
	 */
	private $msMacTemplate = null;
	/**
	 * 模板的网站相对根路径
	 * @var string
	 */
	private $msTemplatePath = null;
	/**
	 * WebService 的入口url地址
	 * @var string
	 */
	private $msWebServiceUrl = null;
	/**
	 * 模板动态数据的Key|Val值存储
	 * @var array
	 */
	private $maStore = array();
	/**
	 * API接口文件的绝对根路径
	 * @var string
	 */
	private $msPackageRoot = null;
	/**
	 * API的业务逻辑文件包的根
	 * @var string
	 */
	private $msPackageWorkgroup = null;
	/**
	 * 用户访问权限 <br/>
	 * 值:[public:公共权限] | [protected:内部开发者权限] | [private:内部私有接口(不对外公开)]
	 * @var string
	 */
	private $msPurview = null;
	/**
	 * xml数组与xml|json数据包转换类
	 * @var IXmlJsonConverArray
	 */
	private $moXmlArray = null;

	/**
	 * 构造函数
	 * @see CExtModule::__construct()
	 */
	function __construct()
	{
		parent::__construct();
		session_start();//本页面会使用到session
		$this->msMacTemplate = getMAC_ROOT() . getFW_ROOT() . strtr(self::TEMPLATE, array('.'=>'/')) .'/';
		$this->msTemplatePath = getFW_ROOT() . strtr(self::TEMPLATE, array('.'=>'/')) .'/';
		$this->setInit(); //初始化页面基本配置信息
	}

	/*
	 * (non-PHPdoc)
	 * @see CExtModule::isAbleRun()
	 */
	public function isAbleRun()
	{
		$aRet = array();
		if (!function_exists('json_encode') || !function_exists('json_decode'))
			$aRet['CWebServiceProtocolView (json_encode|json_decode)'] = false;
		else
			$aRet['CWebServiceProtocolView (json_encode|json_decode)'] = true;

		if (!class_exists('SimpleXMLElement'))
			$aRet['CWebServiceProtocolView (SimpleXMLElement)'] = false;
		else
			$aRet['CWebServiceProtocolView (SimpleXMLElement)'] = true;

		return $aRet;
	}

	/*
	 * (non-PHPdoc)
	 * @see IExtFramework::start()
	 */
	public function start()
	{
		$this->Authorization(); //访问授权
		$this->adapter(); //页面适配控制
	}

	/**
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile()
	{	//加载本框架的自动加载类
		$this->merger2autoload('extend.webservice.config', 'autoload.cfg.php');
	}

	/**
	 * @see CExtModule::setName()
	 */
	protected function setName($sModule = __CLASS__)
	{
		$this->msModule = $sModule;
	}

	/**
	 * 页面授权处理<br />
	 * 使用GET变量: [login=auth|login=public] [uname] | [pwd]
	 * @return void
	 * @access private
	 */
	private function Authorization()
	{
		if (!is_null($this->G('login')))
		{	//登录验证
			if ($this->G('login') == 'public')
			{	//使用public权限
				$_SESSION[$this->msPackageWorkgroup]['purview'] = 'public';
				$this->msPurview = 'public';
			}
			elseif ($this->G('login') == 'auth')
			{
				$sUname = $this->G('uname');
				$sPwd = $this->G('pwd');
				$Acnt = import('extend.webservice.config', 'auth_protocol.cfg.php'); //载入帐号
				$this->msPurview = 'public'; //设定值
				foreach ($Acnt as $aNode)
				{
					if ($aNode['uname'] == $sUname)
					{	//找到用户名
						if (md5($aNode['pwd']) == $sPwd) //通过验证
							$this->msPurview = $aNode['purview'];
						break;
					}
				}
				$_SESSION[$this->msPackageWorkgroup]['purview'] = $this->msPurview;
			}
		}
		elseif( $this->G('logout') == '1' )
		{	//显示登录界面（重新登录）
			$sView = $this->loadTemplate('main_login.htm');
			$maStore['{@tag_web_path}'] = $this->msTemplatePath; //web根路径
			$maStore['{@tag_my_self}'] = $_SERVER['SCRIPT_NAME']; //当前页面名称
			echo strtr($sView, $maStore);
			flush();
			//清除登录信息
			if (isset($_SESSION[$this->msPackageWorkgroup]['purview']))
				unset($_SESSION[$this->msPackageWorkgroup]['purview']);
			exit(0);
		}
		else
		{
			if (isset($_SESSION[$this->msPackageWorkgroup]['purview']))
				$this->msPurview = $_SESSION[$this->msPackageWorkgroup]['purview'];
			else
				$this->msPurview = 'public'; //默认访客都是public
		}
		//设定访问权限
		$this->maStore['{@tag_purview}'] = $this->msPurview;
	}

	/**
	 * 将字符串转换成安全的html显示代码
	 * @param string $sStr
	 * @return string
	 */
	static public function toHtml($sStr)
	{
		return strtr($sStr, CXmlArrayConver::$msEntities);
	}

	/**
	 * 获取get信息
	 * @param string $sKey GET的Key名称
	 * @return string
	 * @access public
	 * @static
	 */
	static public function G($sKey)
	{
		if (isset($_GET[$sKey]))
			return trim($_GET[$sKey]);
		else
			return null;
	}
	/**
	 * 载入模板文件数据<br/>
	 *  备注:模板文件路径为 self::TEMPLATE
	 * @param string $sFileName 模板文件名称
	 * @return string
	 */
	public function loadTemplate($sFileName)
	{
		if (($hf = fopen($this->msMacTemplate . $sFileName, 'r')) === false)
		{
			echo '<pre>', 'Can not open template:', $this->msMacTemplate, $sFileName, '</pre>';
			exit(0);
		}
		else
		{
			//载入模板文件内容
			$sRet = fread($hf, filesize($this->msMacTemplate . $sFileName));
			unset($hf);
			return $sRet;
		}
	}

	/**
	 * 初始化页面基本配置信息
	 * @return void
	 * @access private
	 */
	private function setInit()
	{
		$this->maStore['{@tag_web_path}'] = $this->msTemplatePath; //web根路径
		$this->maStore['{@tag_year}'] = date('Y');
		//载入配置文件
		$aBuf = import('extend.webservice.config', 'environment.cfg.php');
		$this->msRootName = $aBuf['xml_root_node']; //根节点名称
		$this->msPackageWorkgroup = $aBuf['logic_api_service']; //API逻辑文件根包
		$this->msPackageRoot = getMAC_ROOT() . getFW_ROOT() . strtr($this->msPackageWorkgroup, array('.'=>'/')) .'/'; //获取工作空间的服务包根路径
		$this->msWebServiceUrl = getWEB_ROOT() . $aBuf['web_service_url']; //载入WebService入口url
		$this->msProjectName = $aBuf['protocol_project_name']; //载入项目名称
	}

	/**
	 * 页面适配控制
	 * @return void
	 * @access private
	 */
	private function adapter()
	{
		$sPkg = self::G('pkg'); //父package包路径
		$sCls = self::G('cls'); //类文件名
		$sCtl = self::G('ctl'); //页面适配控制参数  [protocol]|[helper]|[api_test]
		$sProtocol = self::G('ptl'); //获得协议类型

		$this->setTopNav($sPkg, $sProtocol, $sCls);//设置顶部导航菜单栏

		if (empty($sCtl) || $sCtl == 'protocol' || $sCtl == 'api_test')
		{	//接口协议浏览视图
			$this->maStore['{@tag_left_menu_list}'] = '';
			//协议类型xml|json
			if (empty($sProtocol) || 'json' != $sProtocol)
			{	//json协议设置
				$this->moXmlArray = new CXmlArrayConver($this->msRootName, CENV::getCharset());
				$sProtocol = 'xml';
			}
			else
			{	//xml协议设置
				$this->moXmlArray = new CJsonArrayConver($this->msRootName, CENV::getCharset());
				$sProtocol = 'json';
			}
			$this->moXmlArray->setShowFormat(true); //打开数据包格式化开关

			/*正常的接口协议显示流程*/
			$this->setPackageNav($sPkg, $sProtocol);//设置package导航区内容
			$this->setPackageList($sPkg, $sProtocol);//设置包列表
			$this->setClassList($sPkg, $sProtocol, $sCls);//设置class列表区
			if (empty($sCtl) || $sCtl == 'protocol')
				$this->setProtoclView($sPkg, $sProtocol, $sCls); //设置协议显示内容视图（API协议详细介绍）
			elseif ($sCtl == 'api_test')
				$this->doApi_Test($sPkg, $sProtocol, $sCls); //进行API服务调试
		}
		elseif ($sCtl == 'helper')
		{	//使用向导视图
			$this->maStore['{@tag_top_prompt_area}'] = ''; //不是顶部提示区
			$this->setHelperView(); //设置使用向导视图
		}

		//载入模板执行模板替换
		$sTemplate = self::loadTemplate('frame_main.htm'); //载入主模板框架
		$this->maStore['{@tag_protocol_view_ver}'] = self::VER; //版本号
		$this->maStore['{@tag_project_name}'] = $this->msProjectName; //载入项目名称
		$sTemplate = strtr($sTemplate, $this->maStore); //替换模板中的标签
		echo $sTemplate;
		flush(); //送出缓存
		unset($sTemplate);
	}

	/**
	 * 设置包列表
	 * @param string $sPkg 当前package包路径
	 * @param string $sPtl 协议类型 xml|json
	 * @return void
	 * @access private
	 */
	private function setPackageList($sPkg, $sPtl)
	{
		static $sTemplate = '<li><a href="?pkg={@pkg}&ptl={@ptl}" title="{@pkg}">{@dirname}</a></li>';
		if (empty($sPkg))
			$sDir = $this->msPackageRoot;
		else
			$sDir = $this->msPackageRoot . strtr($sPkg, array('.'=>'/')) .'/';

		if (($aDir = @scandir($sDir)) === false)
		{
			echo '<pre>', 'Can not to open the package directory:', "\n", $sDir,'</pre>';
			exit(0);
		}
		else
		{	//取得目录列表
			unset($aDir['.'], $aDir['..']);
			$bFindit = false; //标记是否找到子目录
			$aOutBuf = array();
			array_push($aOutBuf, '<strong>Package list</strong> :<hr/>', "\n", '<ul>', "\n");
			foreach ($aDir as $sSubDir)
			{
				if ('.' == $sSubDir{0})
					continue; //跳过'.'开头的目录名（排除svn目录）
				elseif (!is_dir($sDir . $sSubDir))
					continue; //跳过非目录
				else
				{	//找到子目录 生成记录
					$bFindit = true;
					$sNextPkg = (empty($sPkg) == true)? $sSubDir : $sPkg .'.'. $sSubDir; //下级父package
					array_push($aOutBuf, strtr($sTemplate,
						array('{@dirname}'=>$sSubDir, '{@pkg}'=>$sNextPkg , '{@ptl}'=>$sPtl)), "\n");
				}
			}
			$aOutBuf[] = '</ul>';
		}
		//判断是否找到子目录
		if ($bFindit)
			$this->maStore['{@tag_left_menu_list}'] .= implode('', $aOutBuf);
		unset($aOutBuf);
	}

	/**
	 * 设置package导航区内容
	 * @param string $sPkg 当前package包路径
	 * @param string $sPtl 协议类型 xml|json
	 * @return void
	 * @access private
	 */
	private function setPackageNav($sPkg, $sPtl)
	{
		static $sTemplate = '<a href="?ctl=protocol&pkg={@pkg_path}&ptl={@ptl}">{@pkg}</a>';
		//package导航条的处理
		$aNav = array();
		$aNav[] = 'Package:&nbsp;'; //压入前缀提示符
		if (empty($sPkg))
			$aNav[] = '(请选择package路径)';
		else
		{
			$aPkg = explode('.', $sPkg);
			$sLastVal = ''; //保存最后一个值
			$aHistory = array();//历史路劲信息
			foreach ($aPkg as $sVal)
			{
				$aHistory[] = $sVal;//访问路径处理
				//输出模板
				array_push($aNav, strtr($sTemplate,
					array('{@pkg_path}'=>implode('.', $aHistory) ,'{@pkg}'=>$sVal, '{@ptl}'=>$sPtl)));
				$aNav[] = '<span class="nav_separator"></span>';
				$sLastVal = $sVal;
			}
			unset($aHistory);
			//弹出存储区最后一个节点，重新加入一个不带链接的package尾节点
			array_pop($aNav);
			array_pop($aNav);
			$aNav[] = $sLastVal;
		}
		$this->maStore['{@tag_top_prompt_area}'] = implode('', $aNav);
		unset($aNav);
	}

	/**
	 * 设置class列表区
	 * @param string $sPkg 当前package包路径
	 * @param string $sPtl 协议类型 xml|json
	 * @param string $sCls 类文件名
	 * @return void
	 * @access private
	 */
	private function setClassList($sPkg, $sPtl, $sCls)
	{
		static $sTemplate = '<li{@select}><a href="?ctl=protocol&pkg={@pkg}&ptl={@ptl}&cls={@classname}" title="{@classname}">{@classname}</a></li>';
		if (empty($sPkg))
			$sPath = $this->msPackageRoot;
		else
			$sPath = $this->msPackageRoot . strtr($sPkg, array('.'=>'/'));

		if (($aDir = scandir($sPath)) !== false)
		{
			$bFindIt = false;
			$aOutBuf = array();
			array_push($aOutBuf, '<strong>Class list</strong> :<hr/>', "\n",'<ul>', "\n");
			foreach ($aDir as $sVal)
			{
				if(substr($sVal, -4) == '.php')
				{	//找到PHP文件
					$bFindIt = true; //找到文件
					$sClassName = substr($sVal, 0, -4);
					array_push($aOutBuf, strtr($sTemplate,
						array('{@classname}'=>$sClassName,
							  '{@select}'=>($sCls == $sClassName)? ' class="selected"' : '',
							  '{@pkg}'=>$sPkg ,
							  '{@ptl}'=>$sPtl)), "\n");
				}
			}
			array_push($aOutBuf, "\n", '</ul>', "\n");
			if ($bFindIt)
				$this->maStore['{@tag_left_menu_list}'] .= implode('', $aOutBuf);
			unset($aOutBuf);
		}
	}

	/**
	 * 设置顶部导航菜单栏
	 * @param string $sPkg 当前package包路径
	 * @param string $sPtl 协议类型 xml|json
	 * @param string $sCls 类文件名
	 * @return void
	 * @access private
	 */
	private function setTopNav($sPkg, $sPtl, $sCls)
	{
		static $sTemplate = '<li class="{@select}"><a href="{@link}">{@menu_name}</a></li>';
		$aOutBuf = array();
		//接口根
		$aOutBuf[] = strtr($sTemplate,
				array('{@select}'=>'', '{@link}'=>$_SERVER['SCRIPT_NAME'].'?ctl=protocol&ptl=xml', '{@menu_name}'=>'接口根'));
		//XML协议
		$aOutBuf[] = strtr($sTemplate,
				array('{@select}'=>($sPtl == 'xml')? 'active' : '',
					  '{@link}'=>'?ctl=protocol&pkg='. $sPkg .'&ptl=xml'.'&cls='. $sCls,
					  '{@menu_name}'=>'XML协议'));
		//JSON协议
		$aOutBuf[] = strtr($sTemplate,
				array('{@select}'=>($sPtl == 'json')? 'active' : '',
					  '{@link}'=>'?ctl=protocol&pkg='. $sPkg .'&ptl=json'.'&cls='. $sCls,
					  '{@menu_name}'=>'JSON协议'));
		//使用介绍
		$aOutBuf[] = strtr($sTemplate, array('{@select}'=>'', '{@link}'=>'?ctl=helper&view=general', '{@menu_name}'=>'使用介绍'));
		//输出数据
		$this->maStore['{@tag_top_nav}'] = implode('', $aOutBuf);
		unset($aOutBuf);
	}

	/**
	 * 检查用户是否有具备Api的可访问权限
	 * @param string $sApiAccess
	 * @return bool
	 * @access private
	 */
	private function checkAuth($sApiAccess)
	{
		if ('protected' == $sApiAccess)
		{	//开发者权限
			if ($this->msPurview == 'public')
				return false;
		}
		elseif ('private' == $sApiAccess)
		{	//私有接口
			if (in_array($this->msPurview, array('public', 'protected')))
				return false;
		}
		return true;
	}
	/**
	 * 设置协议显示内容视图（API协议详细介绍）
	 * @param string $sPkg 当前package包路径
	 * @param string $sPtl 协议类型 xml|json
	 * @param string $sCls 类文件名
	 * @return void
	 * @access private
	 */
	private function setProtoclView($sPkg, $sPtl, $sCls)
	{
		static $sRestrict = '{抱歉！您所在的权限组无权访问此内容}'; //访问权限不够的提示

		if (empty($sCls)) //提示选择左侧的Class
			$sView = $this->loadTemplate('frame_first.htm'); //载入视图文件
		else
		{
			$sView = $this->loadTemplate('frame_protocol.htm'); //载入视图文件
			$aTagReplace['{@tag_package_path}'] = $sPkg;
			$aTagReplace['{@tag_class_name}'] = $sCls;
			$aTagReplace['{@tag_protocol_type}'] = $sPtl;

			if (empty($sPkg))
				$sPackage = $this->msPackageWorkgroup;
			else
				$sPackage = $this->msPackageWorkgroup .'.'. $sPkg;

			$oAPI = import($sPackage, $sCls .'.php', $sCls); //载入API接口文件
			if ($oAPI instanceof IProtocolView)
			{
				if ($oAPI->getAccess() == 'public')
					$aTagReplace['{@tag_access}'] = '<span style="color:#23B129">public(开放的公众接口)</span>';
				elseif ($oAPI->getAccess() == 'protected') //内部开发者权限接口
					$aTagReplace['{@tag_access}'] = '<span style="color:#007AFF">protected(内部开发者的私有接口，开发者权限可查看)</span>';
				else //剩下的private以及为定义的都属于私有接口，
					$aTagReplace['{@tag_access}'] = '<span style="color:#E00728">private(内部私有接口，不对外公开)</span>';

				/*package包介绍，读取包路径下的package_explain.txt文件内容*/
				$sFile = $this->msPackageRoot . strtr($sPkg, array('.'=>'/')) .'/package_explain.txt';
				if (file_exists($sFile))
					$aTagReplace['{@tag_package_explain}'] = file_get_contents($sFile); //载入包介绍
				else
					$aTagReplace['{@tag_package_explain}'] = '{未定义包介绍； 请在包目录下创建 package_explain.txt 文件，写入包介绍信息}';
				unset($sFile);

				/*判断访问者权限，没有权限的访问者不能查看受限内容*/
				if ($this->checkAuth($oAPI->getAccess()))
				{
					$aTagReplace['{@tag_class_explain}'] = nl2br(self::toHtml($oAPI->getClassExplain())); //载入类介绍
					$aTagReplace['{@tag_use_explain}'] = nl2br(self::toHtml($oAPI->getUseExplain())); //载入接口使用介绍
					//输出入口协议
					$aXml = $oAPI->getInProtocol(); //获取入口协议的XML结构数组
					/*加入package|class*/
					$aXml = array_merge(array('package'=>array('C'=>$sPkg), 'class'=>array('C'=>$sCls)), $aXml);
					/*加入checksum*/
					if ($sPkg != CWebService::PUBLIC_PACKAGE) //开放包路径中的API接口无checksum验证节点
						$aXml = array_merge($aXml, array('checksum'=>
									array('A'=>array('value'=>'校验码md5(32位)',
													 'unix_timestamp'=>'Unix新纪元(格林威治时间1970年1月1日00:00:00)到当前时间的秒数'))));
					//输出XML或JSON出口协议
					$aTagReplace['{@tag_in_protocol_view}'] = self::toHtml($this->moXmlArray->getStrPacket($aXml));
					//输出Get格式的出口协议
					$oGet = new CXmlArrayConverGET();
					$oGet->setShowFormat(true);
					if (CXmlArrayConverGET::canConvert($aXml))
						$aTagReplace['{@tag_in_get_protocol_view}'] = nl2br(self::toHtml($oGet->getStrPacket($aXml)));
					else
						$aTagReplace['{@tag_in_get_protocol_view}'] =
							'<div style="color:#E00728">当前入口协议不支持get方式访问</div>';
					unset($oGet);

					//输出XML或JSON出口协议
					$aXml = $oAPI->getOutProtocol(); //获取入口协议的XML结构数组
					$aXml = array_merge($aXml, array('result'=>array('A'=>array('value'=>'状态代码', 'msg'=>'状态代码解释'))) );
					$aTagReplace['{@tag_out_protocol_view}'] = self::toHtml($this->moXmlArray->getStrPacket($aXml));
					unset($aXml);

					//获取API接口的状态码返回值
					$aBuf = array();
					foreach ($oAPI->getResultStateList() as $sCode => $sVal)
						array_push($aBuf, $sCode, ' => ', $sVal, "\n");
					$aTagReplace['{@tag_result_api_state_list}'] = nl2br(self::toHtml(implode('', $aBuf)));
					unset($aBuf);

					//获取WebService框架的系统状态值
					$aBuf = array();
					foreach (CWebService::$maResultStatus as $sCode => $sVal)
						array_push($aBuf, $sCode, ' => ', $sVal, "\n");
					$aTagReplace['{@tag_result_system_state_list}'] = nl2br(self::toHtml(implode('', $aBuf)));
					unset($aBuf);

					//输出更新记录
					$aBuf = array();
					foreach ($oAPI->getUpdaueLog() as $aNode)
						array_push($aBuf, '<p>', $aNode['date'], ' : [', $aNode['author'], '] ',  self::toHtml($aNode['memo']), '</p>');
					$aTagReplace['{@tag_update_log}'] = implode('', $aBuf);
					unset($aBuf);

					//输出手工调试接口
					if (CXmlArrayConverGET::canConvert($oAPI->getInProtocol()))
					{
						$aBuf = array();
						$aBuf[] ='<button id="btn_api_test" class="action red">';
						$aBuf[] ='<span class="label">手工测试API接口</span></button>';
						array_push($aBuf, "\n", '<script>', "\n");
						array_push($aBuf, '$(function(){', "\n");
						array_push($aBuf, '$("#btn_api_test").click(function(){', "\n");
						array_push($aBuf, 'location.href =\'', $_SERVER['SCRIPT_NAME']);
						array_push($aBuf, '?ctl=api_test&ptl=xml&pkg=', $sPkg ,'&cls=', $sCls ,'\';', "\n");
						array_push($aBuf, '});', "\n");
						array_push($aBuf, '});', "\n", '</script>');
						$aTagReplace['{@tag_test_api_button}'] = implode('', $aBuf);
					}
					else
					{	//当前的入口协议不支持手工调试
						$aTagReplace['{@tag_test_api_button}'] =
							'<div style="color:#E00728">当前API接口不支持手工调试，请使用客户端程序的方式进行调试</div>';
					}
				}
				else
				{	//权限不够，显示无权访问
					$aTagReplace['{@tag_class_explain}'] = $sRestrict;
					$aTagReplace['{@tag_use_explain}'] = $sRestrict;
					$aTagReplace['{@tag_in_protocol_view}'] = $sRestrict;
					$aTagReplace['{@tag_out_protocol_view}'] = $sRestrict;
					$aTagReplace['{@tag_result_api_state_list}'] = $sRestrict;
					$aTagReplace['{@tag_result_system_state_list}'] = $sRestrict;
					$aTagReplace['{@tag_update_log}'] = $sRestrict;
					$aTagReplace['{@tag_test_api_button}'] = $sRestrict;
					$aTagReplace['{@tag_in_get_protocol_view}'] = $sRestrict;
				}
			}
			else
			{
				echo 'error:<br />', "\n",
					 'API service class must inherited IProtocolView interface.<br>', "\n",
					 'package: ', $sPkg , ', class:', $sCls;
				exit(0);
			}

			$sView = strtr($sView, $aTagReplace);
			unset($aTagReplace);
		}
		$this->maStore['{@tag_content_view}'] = $sView;
		unset($sView);
	}

	/**
	 * 使用向导的内容视图
	 * @return void
	 * @access private
	 */
	private function setHelperView()
	{
		$this->maStore['{@tag_top_prompt_area}'] = ''; //不使用顶部提示区

		$aTags = array();
		//初始化菜单项目
		$aMenu = array(array('name'=>'基本使用介绍', 'tag'=>'general'),
					   array('name'=>'Json的特殊结构介绍', 'tag'=>'json'));
		$aBuf[] = '<strong>使用介绍</strong> :<ul>';
		foreach ($aMenu as $aNode)
		{	//生成菜单
			array_push($aBuf, '<li',
				(isset($_GET['view']) && $_GET['view'] == $aNode['tag'])? ' class="selected"':'',
				'><a href="?ctl=helper&view=', $aNode['tag'], '">',
				$aNode['name'], '</a></li>');
		}
		$aBuf[] = '</ul>';

		$this->maStore['{@tag_left_menu_list}'] = implode('', $aBuf);//菜单初始化
		unset($aBuf);

		//内容区初始化
		$aTags = array();
		if (isset($_GET['view']) && $_GET['view'] == 'general')
		{
			$aView = self::loadTemplate('frame_helper.htm'); //载入使用帮助模板
			$aTags['{@tag_enter_url}'] = 'http://'. CENV::getHost() . $this->msWebServiceUrl .'?protocol_type=xml';
			$aTags['{@tag_project_name}'] = $this->msProjectName;
		}
		else
		{
			$aView = self::loadTemplate('frame_helper_json.htm'); //载入使用帮助模板
		}
		$this->maStore['{@tag_content_view}'] = strtr($aView, $aTags);
		unset($aTags);
	}

	/**
	 * 进行api手动调试
	 * @param string $sPkg 当前package包路径
	 * @param string $sPtl 协议类型 xml|json
	 * @param string $sCls 类文件名
	 * @return void
	 * @access private
	 */
	private function doApi_Test($sPkg, $sPtl, $sCls)
	{
		if (empty($sPkg))
			$sPackage = $this->msPackageWorkgroup;
		else
			$sPackage = $this->msPackageWorkgroup .'.'. $sPkg;

		$oAPI = import($sPackage, $sCls .'.php', $sCls); //载入API接口文件
		if (!$this->checkAuth($oAPI->getAccess()))
		{	//无解接口访问权限
			Header('Location: http://'. CENV::getHost() . $_SERVER['SCRIPT_NAME']);
			exit(0);
		}

		if ($oAPI instanceof IProtocolView)
		{
			$aInP = $oAPI->getInProtocol(); //取出入口协议的Xml结构数组
			$oJson = new CJsonArrayConver($this->msRootName, CENV::getCharset());
			if (CXmlArrayConverGET::canConvert($aInP)) //检查能否使用Get方式
			{	//入口协议可通过Get方式提交
				$aTags['{@tag_in_protocol_json}'] = $oJson->getStrPacket($aInP); //得到入口协议的json字符串
				$aTags['{@tag_root_name}'] = $this->msRootName; //送出根节点的名称
				unset($oJson);

				$sView = self::loadTemplate('frame_api_test.htm'); //载入API测试模板
			}
			else
			{
				//TODO 入口参数无法用get表示的处理
				$sView = '<div class="helpMsg">当前API接口不支持手工调试，请使用客户端程序的方式进行调试</div>';
			}
			$aTags['{@tag_enter_url}'] = 'http://'. CENV::getHost() . $this->msWebServiceUrl;
			$aTags['{@tag_package}'] = $sPkg;
			$aTags['{@tag_class}'] = $sCls;
			$aTags['{@tag_web_path}'] = $this->msTemplatePath; //web根路径


			$this->maStore['{@tag_content_view}'] = strtr($sView, $aTags);
		}
		else
		{
			echo 'error:<br />', "\n",
			'API service class must inherited IProtocolView interface.<br>', "\n",
			'package: ', $sPkg , ', class:', $sCls;
			exit(0);
		}
	}
}
?>
