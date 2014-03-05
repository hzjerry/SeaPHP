<?php
/**
 * 登录演示
 * @author JerryLi
 */
class LoginOpt extends CWebsiteModule
{
	/* (non-PHPdoc)
	 * @see CWebsiteModule::defaultFunc()
	*/
	public function getDefaultFunc()
	{
		return 'login';
	}

	/**
	 * 登录
	 * @param string $sCtl
	 * @return void
	 */
	public function login($sCtl)
	{
		$sUName = P('uname');
		$sPWD = P('pwd');

		if ($sUName === 'test' && $sPWD === 'test')
		{	//登录成功
			if (P('rember') === 'true')
			{	//用户选择了记住我的登录信息
				$this->setCookie('rember', 'true');
				$this->setCookie('logname', $sUName);
				$this->setCookie('pwd', $sPWD);
			}
			else
				$this->setCookie('rember', 'false');

			$this->view->showMsg('succ', '登录成功', '登录测试');
		}
		else
		{
			$this->view->loadStaticCache(null, 30); //检查是否命中缓存
			$this->view->showPage('LoginOpt_login.tpl');
			$this->view->makeStaticCache(); //创建静态缓存
		}
	}
}

?>