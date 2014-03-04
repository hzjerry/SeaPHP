<?php
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
		echo "hello world;";
	}
}

?>