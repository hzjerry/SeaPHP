<?php
class getAllList extends CWebsiteModule
{
	function __construct()
	{
		echo __CLASS__ .'::'. __FUNCTION__ ."():i'm here:";
	}

	/* (non-PHPdoc)
	 * @see CWebsiteModule::defaultFunc()
	 */
	public function getDefaultFunc()
	{
		return 'deluser';
	}

	public function deluser($sCtl)
	{
		$this->view->loadStaticCache($sCtl, 5);
		if (is_null($sBuf = $this->session->get('user.list')))
			$this->session->set('user.list', 'ttete'.date('Y-m-d H:i:s'));
		else
			echo "<br/>Session:". $this->session->get('user.list');

		echo '<br/>e:'. G('e');
		echo '<br/>b:'. G('b');
// 		for ($i=0; $i<10000; $i++)
			echo date('Y-m-d H:i:s');
		echo "<br/>function:". __FUNCTION__.'()';

		$this->view->O('user', 'jerryli');
		$this->view->showPage('child1.tpl');

		$this->view->makeStaticCache($sCtl);
	}
}
?>