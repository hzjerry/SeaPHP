<?php
/**
 * 错误处理类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130413
 * @package SPFW.core.lib.final
 * */
final class CErrThrow
{
	/**
	 * 抛出系统错误信息,并终止程序运行<br />
	 * 注意：必须在系统奔溃时使用，否则函数打印出的函数堆栈信息有安全隐患
	 * @param string $sErrCode 错误代号
	 * @param string $sMsg 错误提示信息
	 * @return null;
	 * @static
	 * @access public
	 */
	static public function throwExit($sErrCode, $sMsg)
	{
		echo '<div style="border: 1px solid #007aff;background: #e8eef1;">',
			 '<strong>Error Code:</strong>&nbsp;',$sErrCode,
			 '<br /><strong>Error Message:</strong>&nbsp;', $sMsg,
			 '<hr style="height:1px;border:none;border-top:1px dashed #0066CC;" />',
			 '<strong>Function stack trace:</strong><br />',
			 implode("\n<br />", dbg::TRACE()),
			 '</div>';
		exit(0); //终止程序
	}
}

?>