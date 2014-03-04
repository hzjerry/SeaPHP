<?php
/**
 * 网络处理类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130602
 * @package SPFW.core.lib.final
 * */
final class CNet
{
	/**
	 * 侦测重定向
	 *  得到最终的页面地址
	 * @param string $sUrl 检测的网址
	 * @return string 最终的url文件地址 / null 地址无效
	 * */
	static public function checkRedir($sUrl)
	{
		$url = parse_url($sUrl);
		$errno = "";
		$errstr = "";
		if(isset($url['host']) &&
				($fp = @fsockopen($url['host'], empty($url['port'])? 80 : $url['port'], $error, $errstr, 3)) !== false
		  )
		{
			fputs($fp,"GET ".(empty($url['path'])?'/':$url['path'])." HTTP/1.1\r\n");
			fputs($fp,"Host: $url[host]\r\n");
			fputs($fp,"Connection: Close\r\n\r\n");
			//下载http头
			$sTmp = '';
			while( ($sTmp .= fgets($fp, 128)) !== false)
			{
				if (strlen($sTmp) < 1024)
				{
					if (strpos($sTmp, "\r\n\r\n") !== false)
						break;
				}
				else
					break;
			}
			fclose($fp);

			if (!empty($sTmp))//整理输出值
			{
				$sTmp = substr($sTmp, 0, strpos($sTmp, "\r\n\r\n"));
				if (preg_match('/HTTP\/\d?\.\d? ?(\d{3}).*/si', $sTmp, $arr))
				{
					if ($arr[1] == '302')
					{	//重定向处理
						if (preg_match('/Location: ?(\S+).*/si', $sTmp, $arr))
							return $this->checkRedir($arr[1]); //递归处理
						else
							return null; //未找到重定向地址
					}
					elseif ($arr[1] == '200')
					{
						unset($sTmp);
						return $sUrl; //找到最终页面地址
					}
					else
						return null; //页面访问失败
				}
				else
					return null; //无效的http协议
			}
			else
				return null; //超时未取到数据
		}
		else
			return null; //URL地址无效
	}

	/**
	 * 获取远程文件的大小
	 * @param string $sUrl 远程文件地址
	 * @return long
	 * @access public
	 * @static
	 * @see 尽可能在checkRedir()之后再调用这个函数
	 * */
	static public function getFileSize($sUrl)
	{
		$url = parse_url($sUrl);
		$errno = "";
		$errstr = "";
		if(isset($url['host']) &&
		   ($fp = @fsockopen($url['host'], empty($url['port'])? 80 : $url['port'], $error, $errstr, 3)) !== false
		  )
		{
			fputs($fp,"GET ".(empty($url['path'])?'/':$url['path'])." HTTP/1.1\r\n");
			fputs($fp,"Host: $url[host]\r\n");
			fputs($fp,"Connection: Close\r\n\r\n");
			//下载http头
			$tmp = '';
			while( ($tmp .= fgets($fp, 128)) !== false)
			{
				if (strlen($tmp) < 1024)
				{
					if(preg_match('/Content-Length: ?(\d+).*/si', $tmp, $arr))
					{
						unset($tmp);
						fclose($fp);
						return intval($arr[1]);
					}
				}
				else
					break;
			}
			unset($tmp);
			fclose($fp);
			return 0;
		}
		else
			return 0;
	}

	/**
	 * 检查URL文件是否有效<br/>
	 * 注意:allow_url_fopen=on<br/>
	 * 返回: string:内容是 Content-Type类型;
	 * @param string $sUrl 需要检查的URL文件地址
	 * @return null | string
	 * @see http://zh.wikipedia.org/wiki/%E5%A4%9A%E7%94%A8%E9%80%94%E4%BA%92%E8%81%AF%E7%B6%B2%E9%83%B5%E4%BB%B6%E6%93%B4%E5%B1%95
	 * */
	static function checkUrlFile($sUrl)
	{
		if (!function_exists('get_headers'))
		{
			echo 'error: not find function get_headers().', "\n",
			'set: allow_url_fopen=on';
			exit();
		}

		if (!is_null($sUrl = self::checkRedir($sUrl)))
		{
			$aRet = get_headers($sUrl, 1);
			if( preg_match('/200/',$aRet[0]) ) //有效访问
				return $aRet['Content-Type'];//找到资源,取出资源类型
			else
				return null;
		}
	}
}
?>