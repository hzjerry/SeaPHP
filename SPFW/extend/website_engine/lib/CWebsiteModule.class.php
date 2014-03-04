<?php
/**
 * Website engine业务逻辑基类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130723
 * @package SPFW.extend.website_engine.lib
 * @abstract
 */
abstract class CWebsiteModule
{
	/**
	 * 页面逻辑类包名
	 * @var string
	 * @static
	 */
	static $mPkg = null;
	/**
	 * 需要执行的类方法名
	 * @var string
	 * @static
	 */
	static $msAct = null;
	/**
	 * 页面逻辑类名
	 * @var string
	 * @static
	 */
	static $msClass = null;
	/**
	 * Session会话操作对象
	 * @var CSessionOperat
	 */
	protected $session = null;
	/**
	 * 视图操作对象
	 * @var CWebsiteView
	 */
	protected $view = null;

	/**
	 * 返回默认的启动函数名<br />
	 * 如果在URL路由解析阶段,未得到Act参数,则通过此函数获得默认Act的函数名
	 * @return string 函数名不要包含'()'
	 * @abstract
	 */
	abstract public function getDefaultFunc();

	/**
	 * 设置页面逻辑模块初始化
	 * @param string $sPkg 页面逻辑访问
	 * @param string $sAct 执行函数
	 * @param string $sCtl 控制参数
	 * @param string $sClass 类名称
	 * @param string $sPart 工作区
	 * @return void
	 */
	public function init($sPkg, $sAct, $sCtl, $sClass)
	{
		self::$mPkg = $sPkg;
		self::$msAct = $sAct;
		self::$msClass = $sClass;
		$this->session = new CSessionOperat(CWebsiteEngine::$msPart);
		$this->view = new CWebsiteView();
	}

	/**
	 * 设置COOKIE值<br/>
	 * 注意:在js中访问，key的规则是: 工作区_Key （例如:agent_username）
	 * @param string $sKey 变量名
	 * @param string $sData 保存的数据
	 * @param int $iLiftTime Cookie生命时间(单位秒,默认为永不失效)
	 * @return void
	 */
	protected function setCookie($sKey, $sData, $iLiftTime=0)
	{
		setcookie(CWebsiteEngine::$msPart .'_'. $sKey, $sData, ($iLiftTime == 0)? 0 : time()+$iLiftTime);
	}

	/**
	 * 获取COOKIE值
	 * @param string $sKey 变量名
	 * @return string|null
	 */
	protected function getCookie($sKey)
	{
		if (isset($_COOKIE[CWebsiteEngine::$msPart .'_'. $sKey]))
			return	$_COOKIE[CWebsiteEngine::$msPart .'_'. $sKey];
		else
			return null;
	}

	/**
	 * 删除COOKIE值
	 * @param string $sKey 变量名
	 * @return void
	 */
	protected function delCookie($sKey)
	{
		setcookie(CWebsiteEngine::$msPart .'_'. $sKey, '');
	}

	/**
	 * 发送文件给浏览器(让浏览器出现保存窗口)
	 * $sSendType的类型输入中括号[text/plain]
	 *
	 * @param string $sType [ txt| csv| gif| jpg| png]
	 * @param string $sData 数据内容
	 * @param string $sFile 文件名称(无需扩展名);null时自动生成文件名
	 * @return void
	 */
	protected function sendFile($sType, $sData, $sFile=null)
	{
		static $aTypeList = array('txt'=>'text/plain', 'csv'=>'text/csv', 'gif'=>'image/gif',
			'jpg'=>'image/jpeg', 'png'=>'image/x-png');
		if (!isset($aTypeList[$sType]))
			CErrThrow::throwExit('Err: Can not send file data.', '文件类型不支持,无法发送.');
		//生成文件名
		if (empty($sFile))
			$sFileName = 'file'. date('YmdHis') .'.'. $sType;
		else
			$sFileName .=$sType;

		ob_end_clean();/*清除之前的调试信息*/
		/*文本文件下载头文件*/
		header('Accept-Length:'. strlen($sData));
		header('Content-type:'. $sType);
		header('Content-Disposition: attachment; filename='. $sFileName);
		echo $sData; //送出数据
		exit(0);
	}
}

?>