<?php
/**
 * 网站引擎用户访问安全验证基类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130724
 * @package SPFW.extend.website_engine.lib
 * @abstract
 */
abstract class CWebsiteSecurity
{
	/**
	 * Session操作对象
	 * @var CSessionOperat
	 */
	protected $session = null;

	/**
	 * 初始化函数
	 * @param string $sPart
	 * @return void
	 */
	public function init($sPart)
	{
		$this->session = new CSessionOperat($sPart);
	}

	/**
	 * 检查访问者权限
	 * @param string $sPkg package包路径
	 * @param string $sAct 方法参数
	 * @return void
	 */
	abstract public function checkAccess($sPkg, $sAct);
}

?>