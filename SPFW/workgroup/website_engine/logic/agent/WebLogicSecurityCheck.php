<?php
/**
 * 网站引擎 工作区的用户授权访问检查<br/>
 * 备注：每个工作区必须要有一个这样的文件，由Website Engine负责调用。
 *      在此文件中可对$sPkg, $sAct两个传入的参数进行验证。可通过$this->session对sess进行访问操作。
 *      如果用户未获得授权（自己设定规则），可通过redirect($url)全局函数跳转到登录页面
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140305
 * @package SPFW.workgroup.website_engine.工作区
 * @final
 */
final class WebLogicSecurityCheck extends CWebsiteSecurity{
	/**
	 * 公开的无需登录验证的逻辑方法
	 * @var array
	 */
	static $maPublicList = array('LoginOpt', 'ajax.LoginOrReg');
	/* (non-PHPdoc)
	 * @see CWebsiteSecurity::checkAccess()
	 */
	public function checkAccess($sPkg, $sAct){
		//TODO:此处编写用户授权访问的业务逻辑
		if (!in_array($sPkg, self::$maPublicList)){ //不对LoginOpt与ajax.LoginOrReg任何做安全检查
			if (is_null($this->session->get('login_state'))){
				echo '用户未授权:',
				'<br/><strong>Pkg:</strong>', $sPkg,
				'<br/><strong>Act:</strong>', $sAct,
				'<br/>用户是否授权操作可修改这个类文件:'.__DIR__.'\\'. __CLASS__ .'.php',
				'<br/>';
			}
		}
	}
}
?>