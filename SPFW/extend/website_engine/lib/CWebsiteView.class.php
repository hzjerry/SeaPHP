<?php
/**
 * Website engine 视图操作类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130724
 * @package SPFW.extend.website_engine.lib
 */
class CWebsiteView
{
	/**
	 * 静态缓存根目录
	 * @var string
	 */
	private $msCachePath = null;
	/**
	 * 输出给模板的参数
	 * @var string
	 */
	private $maOutParam = array();
	/**
	 * 构造
	 */
	public function __construct()
	{
		//生成静态缓存目录
		$this->msCachePath = getMAC_ROOT() . getFW_ROOT() .
			str_replace('.', '/', CWebsiteEngine::$msStaticCachePath .'.'. CWebsiteEngine::$msPart) .'/';

		//载入路由参数送到未来的模板中
		$this->O('part', CWebsiteEngine::$msPart);
		$this->O('pkg', CWebsiteEngine::$maRouter[0]);
		$this->O('act', CWebsiteEngine::$maRouter[1]);
		$this->O('ctl', CWebsiteEngine::$maRouter[2]);
		$this->O('webroot', getWEB_ROOT());
		//如果存在session则送出session值
		if (!is_null($aSession = CSessionOperat::getPartData(CWebsiteEngine::$msPart)))
			$this->O('session', $aSession);

// _dbg($this->msCachePath, '$this->sCachePath');
// _dbg(CWebsiteModule::$mPkg, 'CWebsiteModule::$mPkg');
// _dbg(CWebsiteModule::$msAct, 'CWebsiteModule::$msAct');
// _dbg(CWebsiteModule::$msClass, 'CWebsiteModule::$msClass');
// _dbg(CWebsiteEngine::$msPart, 'CWebsiteEngine::$msPart');
// _dbg(CWebsiteEngine::$msStaticCachePath, 'CWebsiteEngine::$msStaticCachePath');
	}

	/**
	 * 载入页面静态缓存<br />
	 * 【注意】缓存被命中，输出结束后，程序会立即终止<br/>
	 * 静态缓存文件名规则:md5(Pkg + Class + Act + Key)
	 * @param string $sKey 缓存哈希关键字
	 * @param int $iLifeTime 缓存生命时间
	 * @return void
	 */
	public function loadStaticCache($sKey=null, $iLifeTime=3600)
	{
		$sFile = md5(CWebsiteModule::$mPkg . CWebsiteModule::$msClass . CWebsiteModule::$msAct . $sKey);
		$sPath = $this->msCachePath .$sFile{0} . $sFile{1} .'/'. $sFile{2}.$sFile{3} .'/'. $sFile{4}.$sFile{5} .'/';
		$FP = $sPath . $sFile;
		if (file_exists($FP) && (time() - filemtime($FP)) < $iLifeTime)
		{	//找到静态缓存,直接输出
 			ob_end_clean(); //清除先前的缓存内容
			$f = fopen($FP,'rb');
			flock($f , LOCK_SH);//共享锁
			$iLoopCnt = filesize($FP) / 4096; //计算需要几次能读完数据
			do
			{	//使用缓存输出4096的目的是,一般浏览器的接收缓存是4096,超过这个数据量就会开始显示内容
				echo fread($f, 4096);
				$iLoopCnt--; //递减一次循环次数
			}while($iLoopCnt > 0);
			flock($f , LOCK_UN);//释放锁
			fclose($f);

			//调试环境下打开缓存命中的提示
			$aBuf = array();
			$aBuf[] = 'Hit static cache.';
			$aBuf[] = 'memory: '. memory_get_peak_usage();
			$aBuf[] = 'runtime: '. CENV::getRuntime();
			_dbg(implode("\t", $aBuf));

			exit(0);
		}
	}

	/**
	 * 生成静态缓存<br/>
	 * 静态缓存文件名规则:md5(Pkg + Class + Act + Key)
	 * @param string $sKey 缓存哈希关键字
	 * @param string $sData 缓存数据
	 * @return void
	 */
	public function makeStaticCache($sKey=null)
	{
		$sFile = md5(CWebsiteModule::$mPkg . CWebsiteModule::$msClass . CWebsiteModule::$msAct . $sKey);
		$sPath = $this->msCachePath .$sFile{0} . $sFile{1} .'/'. $sFile{2}.$sFile{3} .'/'. $sFile{4}.$sFile{5} .'/';

		if (!file_exists($sPath)) //路径不存在时创建缓存
			if (!CFileOperation::creatDir($sPath))
				CErrThrow::throwExit('Error:crete path fail.', '页面静态缓存创建失败,cache目录无权限');

		//文件块写入,写入时锁定文件
		$f = fopen($sPath . $sFile, 'wb');
		flock($f, LOCK_EX);//独占锁
		fwrite($f, ob_get_contents());
		flock($f, LOCK_UN);//解锁
		fclose($f);
	}

	/**
	 * 输出参数给模板
	 * @param string $sKey
	 * @param mixed $Data
	 * @return void
	 */
	public function O($sKey, $Data)
	{
		$this->maOutParam[$sKey] = $Data;
	}

	/**
	 * 抛出状态提示信息页面,并结束程序<br/>
	 * @param string $sTypeFlg 信息提示类型 [succ:操作成功 | fail:操作失败 | warn:警告/注意]
	 * @param string $sMsg 提示信息内容
	 * @param string $sTitle 提示信息页标题
	 * @param string $sRedirect 页面重定向地址
	 * @param int $iAutoRedirectTime 自动重定向时间(单位:秒)
	 * @return void
	 */
	public function showMsg($sTypeFlg, $sMsg, $sTitle=null, $sRedirect=null, $iAutoRedirectTime=6)
	{
		static $aFlg = array('succ', 'fail', 'warn');
		$this->O('TypeFlg', (in_array($sTypeFlg, $aFlg)? $sTypeFlg : 'empty')); //非指定标志输出'empty'
		$this->O('Title', 	(empty($sTitle))? '信息提示':$sTitle); //页面标题
		$this->O('Msg', 	$sMsg); //提示信息内容主体
		$this->O('Redirect',$sRedirect); //页面跳转地址
		$this->O('JumpTime',$iAutoRedirectTime); //页面自动跳转时间
		$this->showPage('showMsg.tpl');
		exit(0);
	}

	/**
	 * 显示Smaty模板页面
	 * @param string $sTpl 模板文件
	 */
	public function showPage($sTpl)
	{
		//smart3模板目录(带工作区)
		$sTplDir = getMAC_ROOT() . getFW_ROOT() .
			str_replace('.', '/', CWebsiteEngine::$msSmartyTplPath .'.'. CWebsiteEngine::$msPart) .'/';
		//smart3模板编译文件
		$sCompDir = getMAC_ROOT() . getFW_ROOT() .
			str_replace('.', '/', CWebsiteEngine::$msSmartyCachePath) .'/templates_c/'. CWebsiteEngine::$msPart .'/';
		//smart3模板缓存文件目录
		$sCacheDir = getMAC_ROOT() . getFW_ROOT() .
			str_replace('.', '/', CWebsiteEngine::$msSmartyCachePath) .'/cache/'. CWebsiteEngine::$msPart .'/';

		$oSmt = new Smarty(); //构造模板对象
		$oSmt->caching = false; //不要打开这个选项(永远不用smarty的缓存)
		$oSmt->cache_lifetime = 120; //缓存文件生命期
		$oSmt->debugging = false; //关闭调试环境(调试时直接把调试数据打印在页面上)
		$oSmt->force_compile = CWebsiteEngine::$maSmartyForceCompile; //强迫编译
		$oSmt->left_delimiter = CWebsiteEngine::$maSmartyDelimiter[0]; //左定界符
		$oSmt->right_delimiter = CWebsiteEngine::$maSmartyDelimiter[1];//右定界符
		//设置模板文件目录
		if (file_exists($sTplDir))
			$oSmt->setTemplateDir($sTplDir);
		else
			CErrThrow::throwExit('Error: Smarty not read and write permissions', 'Smarty的模板文件目录不存在: '. $sTplDir);

		//设置模板编译文件存放目录
		if (!file_exists($sCompDir)) //编译目录不存在直接创建
			if (!CFileOperation::creatDir($sCompDir))
				CErrThrow::throwExit('Error: Smarty not read and write permissions', 'Smarty的templates_c无读写权限: '.$sCompDir);
		$oSmt->setCompileDir($sCompDir);

		//设置模板编译文件存放目录
		if ($oSmt->caching && !file_exists($sCacheDir)) //编译目录不存在直接创建
			if (!CFileOperation::creatDir($sCacheDir))
			CErrThrow::throwExit('Error: Smarty not read and write permissions', 'Smarty的cache无读写权限: '.$sCacheDir);
		$oSmt->setCacheDir($sCacheDir);
		$oSmt->use_sub_dirs = true; //允许使用子目录

		//输出变量到模板
		foreach ($this->maOutParam as $aKey=>$Data)
			$oSmt->assign($aKey, $Data);

		//输出模板信息
		$oSmt->display($sTpl);

		//打印传到模板的参数
_dbg($this->maOutParam, '<hr/>Out Smarty Param');
		//释放模板对象
		unset($oSmt);
		unset($this->maOutParam);
	}
}

?>