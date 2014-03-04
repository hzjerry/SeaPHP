<?php
/**
 * 系统私有类自动加载模块(即与架构框架无关的类)<br/>
 * 本区域中的lib目录中的文件类,均为用户为项目自定义的私有类,需要使用私有类时用这个模块进行加载.<br/>
 *  依赖: CXmlArrayConver | CExtModule | IExtFramework | CENV::getCharset()
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130626
 * @package SPFW.extend.private_class.runtime
 * @example
 *  CExtendManage::Run(new CPrivateClas()); 加载私有类到动态加载列表
 */
class CPrivateClas extends CExtModule implements IExtFramework
{
	/**
	 * 构造函数
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * 析构函数
	 */
	function __destruct()
	{
		parent::__destruct();
	}

	/* (non-PHPdoc)
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile()
	{
		$this->merger2autoload('extend.private_class.config', 'autoload.cfg.php');
	}

	/* (non-PHPdoc)
	 * @see CExtModule::setName()
	 */
	protected function setName($sModule = __CLASS__)
	{
		$this->msModule = $sModule;
	}

	/* (non-PHPdoc)
	 * @see CExtModule::isAbleRun()
	 */
	public function isAbleRun()
	{
		return null;
	}

	/* (non-PHPdoc)
	 * @see IExtFramework::start()
	 */
	public function start()
	{
		//模块加载时,完成动态类配置文件的配置
	}

}

?>