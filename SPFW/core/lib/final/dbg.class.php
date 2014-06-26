<?php
/**
 * 调试信息打印类
 * @author Jerryli(hzjerry@gmail.com)
 * @package SPFW.core.lib.final
 * @final
 * */
final class dbg
{
	const mTEMPLATE_GENERAL = '<pre style="clear:both;">{@val}</pre>';
	const mTEMPLATE_TITLE = '<pre style="clear:both;">{@title}:{@val}</pre>';

	/**
	 * 打印一个对象到字符串
	 * @param mixed $obj 需要打印的对象
	 * @param bool $bCode true:代码级别/false:显示级别
	 * @return string
	 * @static
	 * @access public
	 * */
	static public function toString($obj, $bCode=false)
	{
		$sBuf = '';
		if (is_bool($obj))//判断是否为bool类型
			$sBuf = ($obj) ? 'bool(True)' : 'bool(False)';
		elseif (is_null($obj))//判断是否为空
			$sBuf ='is null';
		elseif (is_int($obj))//判断是否为整形
			$sBuf =  'int('. $obj .')';
		elseif (is_float($obj))//判断是否为浮点
			$sBuf =  'float('. $obj.')';
		elseif (is_string($obj))//判断是否为字符串
			$sBuf ='char['. strlen($obj) .']:('. $obj .')';
		elseif (is_array($obj))//判断是否为数组
			$sBuf = ($bCode) ? var_export($obj, true) : print_r($obj, true);
		elseif (is_resource($obj))//是否为资源类型
			$sBuf = 'resource:'. print_r($obj, true);
		elseif (is_object($obj))//判断是否为对象
		{
			$aBuf = array();
			$aBuf[] = 'class '. get_class($obj) ."\n{\n";
			$aTmp = get_class_vars(get_class($obj)); //取出成员变量
			if (count($aTmp) > 0)
			{
				foreach($aTmp as $name => $val)
					$aBuf[] = '    var $'. $name .' = '. var_export($val, true) .";\n";
				$aBuf[] = "\n";
			}
			$aTmp = get_class_methods($obj);//取出成员函数
			if (count($aTmp) > 0)
			{
				foreach($aTmp as $func)
					$aBuf[] = '    function '. $func ."(...);\n";
			}
			$aBuf[] = '}';
			$sBuf = join('', $aBuf);
		}
		else
			$sBuf ='内容不可识别(不应该出现这个)';

		return strtr($sBuf, array("\n\n"=>"\n", '        '=>"\t"));
	}

	/**
	 * 获取追溯信息 程序的运行栈
	 * @return array()
	 * @static
	 * @access public
	 * */
	static public function TRACE()
	{
		$aOutBuf = array();
		$aTrace = debug_backtrace();
		$iIndex = 0;
		foreach($aTrace as $aTraceInfo)
		{
			$aTmp = array();
			if (!isset($aTraceInfo['file']) || empty($aTraceInfo['file']) )
				continue;
			elseif ($iIndex == 0)
			{
				$iIndex++;
				continue;
			}

			$aTmp[] = '['. $iIndex .']';
			$aTmp[] = '[Line:'. $aTraceInfo['line'] .']';
			if (isset($aTraceInfo['class']))
				$aTmp[] = '['. $aTraceInfo['class'] . $aTraceInfo['type'] . $aTraceInfo['function'] .'()]';
			elseif (isset($aTraceInfo['function']))
				$aTmp[] = '['. $aTraceInfo['function'] .'()]';

			$aTmp[] = 'file:'. $aTraceInfo['file'];
			$aOutBuf[] = join('', $aTmp);

			unset($aTmp);
			$iIndex++;
		}
		return $aOutBuf;
	}

	/**
	 * 打印一个对象（以echo方式输出）
	 *   备注:输出内容相对美观，但看不出数组中值的类型
	 * @param mixed $obj 需要打印的对象
	 * @param string $sTitle 标题
	 * @return void
	 * @static
	 * @access public
	 * */
	static public function D($obj, $sTitle=null)
	{
		if (is_null($sTitle))
			echo strtr(self::mTEMPLATE_GENERAL, array('{@val}'=>self::toString($obj)));
		else
			echo strtr(self::mTEMPLATE_TITLE, array('{@val}'=>self::toString($obj), '{@title}'=>$sTitle));
	}

	/**
	 * 打印一个对象（以echo方式输出）<br/>
	 *  备注:输出内容以代码级别显示(针对打印的数组的显示可直接放到代码中运行)
	 * @param mixed $obj 需要打印的对象
	 * @param string $sTitle 标题
	 * @return void
	 * @static
	 * @access public
	 * */
	static public function DC($obj, $sTitle=null)
	{
		if (is_null($sTitle))
			echo strtr(self::mTEMPLATE_GENERAL, array('{@val}'=>self::toString($obj, true)));
		else
			echo strtr(self::mTEMPLATE_TITLE, array('{@val}'=>self::toString($obj, true), '{@title}'=>$sTitle));
	}

	/**
	 * 打印一个对象并终止程序（以echo方式输出）
	 * @param mixed $obj 需要打印的对象
	 * @param string $sTitle 标题
	 * @return void
	 * @static
	 * @access public
	 * */
	static public function DE($obj, $sTitle=null)
	{
		self::D($obj, $sTitle);
		exit(0);
	}

	/**
	 * 打印一个对象追加到文件(日志写入失败时会终止程序)
	 * @param mixed $obj 需要打印的对象
	 * @param string $sTitle 标题
	 * @param string $sFilePath 文件绝对路径(null时保存在当前目录下，每天生成一个日志文件)
	 * @return void
	 * @static
	 * @access public
	 * */
	static public function DF($obj, $sTitle=null, $sFilePath=null)
	{
		$bErr = false; //是否遇到错误
		if(file_exists($sFilePath) && is_writable($sFilePath))
		{	//文件存在且可写入
			if (($hf = @fopen($sFilePath, 'ab')) !== false)
			{
				if (!fwrite($hf, self::toString($obj) ."\n"))
					$bErr = true;
				fclose($hf);
			}
			else
				$bErr = true;
		}
		elseif (is_null($sFilePath))
		{	//默认日志文件
			$sFilePath = getcwd() .'/DEBUG['. date('Ymd') .'].log';
			if (($hf = @fopen($sFilePath, 'ab')) !== false)
			{
				if (!fwrite($hf, self::toString($obj) ."\n"))
					$bErr = true;
				fclose($hf);
			}
			else
				$bErr = true;
		}
		//若遇到错误，终止程序
		if($bErr)
		{	//日志文件写入失败
			echo '<pre>error: The log file is written to fail.',
				 "\n", 'file path: ', $sFilePath, "\nStack info:\n";
			foreach(self::TRACE() as $sNode)
				echo $sNode ."\n";
			echo '</pre>';
			exit(0);
		}
	}
}
?>