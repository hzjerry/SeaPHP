<?php
/**
 * 首页面演示
 * @author JerryLi
 */
class MainHome extends CWebsiteModule{
	/* (non-PHPdoc)
	 * @see CWebsiteModule::defaultFunc()
	*/
	public function getDefaultFunc(){
		return 'home';
	}

	/**
	 * 首页面
	 * @param string $sCtl
	 * @return void
	 */
	public function home($sCtl){
		$this->view->O('user', $this->session->get('logname'));
		$this->view->showPage('child1.tpl');
	}
}
?>