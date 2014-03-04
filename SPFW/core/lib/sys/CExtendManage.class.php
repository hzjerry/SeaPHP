<?php
/**
 * 扩展功能模块管理器类<br/>
 *   备注:此类管理extend.下的所有可直接作为单一入口可运行的框架类，由他来执行框架类的启动。
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130413
 * @package SPFW.core.lib.sys
 * @example
 * CExtendManage::Run(new CWebService()); //运行WebService服务
 * CExtendManage::Run(new CWebServiceProtocolView()); //运行WebService的协议介绍服务
 * */
final class CExtendManage
{
	/**
	 * 运行框架类模块对象(调用后模块直接运行)<br/>
	 * 这个方法调用的类都在core.config.extend_framework.cfg.php中注册过的系统公共框架
	 * @param IExtFramework $ief 扩展框架类(必须继承IExtFramework接口)
	 * @return void
	 * @example
	 *  CExtendManage::Run(new CWebServiceProtocolView());
	 */
	static public function Run(IExtFramework & $ief)
	{
		$ief->start();//运行框架
		unset($ief); //运行结束，释放资源
	}

	/**
	 * 用户自定义的扩展框架(调用后模块直接运行)<br/>
	 * （无需在core.config.extend_framework.cfg.php中注册扩展框架入口）
	 * @param string $sModuleName 模块名<br/>
	 * (即SPWF.extend.[模块]这个模块的目录名; 如:SPWF.extend.db时,$sModuleName='db')
	 * @param string $sRunTimeClass 运行时类名<br/>
	 * (即:SPWF.extend.[模块].runtime 下需要执行的类名；类文件名格式: 类名+'.class.php')
	 * @return void
	 * @example
	 *  CExtendManage::runUserExt('website_engine', 'CWebsiteEngine');
	 */
	static public function runUserExt($sModuleName, $sRunTimeClass)
	{
		$oClass = import('extend.'. $sModuleName .'.runtime', $sRunTimeClass .'.class.php', $sRunTimeClass);
		if (!is_null($oClass))
			self::Run($oClass);
		else
			CErrThrow::throwExit('CExtendManage::Frameword load fail.',
				'Not find class ['. $sRunTimeClass .'] at the [extend/'. $sModuleName .'/runtime/'. $sRunTimeClass .'.class.php]');
	}
}

?>