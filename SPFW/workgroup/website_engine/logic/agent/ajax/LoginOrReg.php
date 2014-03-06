<?php
/**
 * aJax处理接口
 * @author JerryLi
 *
 */
class LoginOrReg extends CWebsiteModule
{
	/**
	 * 构造函数
	 */
	function __construct()
	{
		$GLOBALS['SEA_PHP_FW_VAR_ENV']['show_debug_info'] = false;//强制关闭调试
	}

	/* (non-PHPdoc)
	 * @see CWebsiteModule::defaultFunc()
	*/
	public function getDefaultFunc()
	{
		return 'loginCheck';
	}

	/**
	 * 检查代理登录信息是否有效
	 * @param string $sCtl
	 * @return void
	 */
	public function loginAgentCheck($sCtl)
	{
		$sUName = G('uname');
		$sPwd =  G('pwd');
		if ($sUName === 'test' && $sPwd === 'test')
			echo json_encode(array('login_state'=>true));
		else
			echo json_encode(array('login_state'=>false));

	}
}
?>