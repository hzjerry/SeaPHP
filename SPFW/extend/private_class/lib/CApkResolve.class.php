<?php
/**
 * APK包文件，解析类<br/>
 *   备注：仅用于linux环境下，需要远程下载文件。
 * @author JerryLi
 * @see 修改记录:2012/07/03，JerryLi，程序创立
*/
class CApkResolve
{
	/**临时文件存放区目录*/
	private $msTmpPath;
	/**临时文件前缀*/
	const F_PREFIX = 'tmp_apk_';
	/**linux命令:aapt*/
	const CMD_aapt = 'aapt';
	/**linux命令:wget*/
	const CMD_wget = 'wget';
	/**linux命令:ls*/
	const CMD_ls = 'ls';
    /**
     * 构造函数
     * @param string $sTmpFileRootPath 临时文件存放的根目录;以'/'结尾
     */
    public function __construct()
    {
    	$this->msTmpPath = getMAC_ROOT() . getWEB_ROOT() .'download/tmpapk/';
    }

    /**
     * 析构函数
     *
     */
    public function __destruct()
    {
    }

    /**
     * 从网上下载APK<br />
     *   备注：只支持linux
     * @return array('apk'=>'已经下载完成的APK文件地址', 'fsize'=>'文件大小') / null:无效路径或非apk文件
     */
    public function getAPK($sUrl)
    {
    	$aPath = pathinfo($sUrl);
    	if ('apk' == strtolower($aPath['extension']))
    	{
    		$aOut = array();
    		$iRet = 0;

			$oNet = new CNet();
			$iRemote = $oNet->getFileSize($sUrl); //获取远程文件的大小
			unset($oNet);
			if ($iRemote > 0)
			{
				/*检查临时文件是否存在，如果存在则判断是否相同，相同则不下载*/
	    		$sFilePath = $this->msTmpPath . self::F_PREFIX. $aPath['basename']; //文件访问全路径
	    		if (file_exists($sFilePath)) //判断临时文件是否存在
	    		{
	    			$iFilesize = filesize($sFilePath); //获取本地文件大小
	    			if ($iRemote == $iFilesize)
	    				return array('apk'=>$sFilePath, 'fsize'=>$iRemote); //远程文件与本地临时文件相同，不用下载
	    		}

	    		/*检查临时文件存储目录是否存在,不存在则创建*/
	    		if (!file_exists($this->msTmpPath))
	    		{
	    			if (!CFileOperation::creatDir($this->msTmpPath))
	    			{	//临时存储目录创建失败
	    				echo __CLASS__, ' error: Creat file fail. path:', $this->msTmpPath;
						exit();
	    			}
	    		}
	    		/*需要将远程文件下载到本地服务器*/
	    		exec(self::CMD_wget .' '. $sUrl .' -O '. $sFilePath ." -t 1 -c",$aOut,$iRet);
	    		if (0 == $iRet)
	    			return array('apk'=>$sFilePath, 'fsize'=>$iRemote);
	    		else
	    			return null; //文件下载失败
			}
			else
				return null; //远程文件无效
    	}
		else
			return null; //非apk文件
    }

    /**
     *
     * 解析APK文件包，提取数据<br />
     *   备注：只支持linux,下载到系统根目录
     * @return null /<br />
     *  array('lable'=>'应用程序名称', 'package'=>'package', 'sdkVersion'=>'最低支持版本号',
     *  	  'version_code'=>'程序版本代码号', 'version'=>'显示的版本名称',
     *  	  'targetSdkVersion'=>'目标版本号', 'runClass'=>'启动类', 'uses-permission'=>array(权限列表))
     * @example
     * $o = new ApkResolve('./download/');
     * DbugP( $o->resolve('./download/com_google_zxing_client_android.apk') );
     */
    public function resolve($sFilePath, $bDel = false)
    {
    	$aOut = array();
    	$iRet = 0;
    	exec(self::CMD_aapt .' dump badging ' . $sFilePath , $aOut, $iRet);

    	if (0 == $iRet)
    	{
    		$iCnt = count($aOut);
    		$aBuf = array();
    		for($i=0; $i<$iCnt; $i++)
    		{
    			if (preg_match("/^application: ?label='(.*)'/isU", $aOut[$i],$m))
    				$aBuf['lable'] = iconv('UTF-8', 'GBK', $m[1]); //应用程序名称
    			elseif (preg_match("/^package: ?name='([^ ']+)'/is", $aOut[$i],$m))
					$aBuf['package'] = $m[1]; //package
    			elseif (preg_match('/^sdkVersion: ?\'(\d+)\'/is', $aOut[$i],$m))
    				$aBuf['sdkVersion'] = $m[1]; //最低支持版本号
    			elseif (preg_match('/^targetSdkVersion: ?\'(\d+)\'/is', $aOut[$i],$m))
    				$aBuf['targetSdkVersion'] = $m[1]; //目标版本号
    			elseif (preg_match("/^launchable activity name='([^ ']+)'/is", $aOut[$i],$m))
    				$aBuf['runClass'] = $m[1]; //启动类
    			elseif (preg_match("/^uses-permission: ?'(.+)'/is", $aOut[$i],$m))
    				$aBuf['uses-permission'][] = $m[1]; //权限列表

    			if (preg_match('/versionCode=\'(\d+)\'/is', $aOut[$i],$m))
    				$aBuf['version_code'] = $m[1]; //程序版本代码号
    			if (preg_match('/versionName=\'([^\']+)\'/is', $aOut[$i],$m))
    				$aBuf['version'] = $m[1]; //显示的版本名称
    		}
    		unset($aOut);

    		//判断是否删除处理好的临时文件
    		if ($bDel)
    			unlink($sFilePath);
			return $aBuf;
    	}
    	else
    		return null;
    }

    /**
     * 删除目录下的所有下载的临时APK文件<br />
     *   备注：只支持linux,下载到系统根目录
     * @deprecated
     * @return void
     * */
    public function removeTmpFile()
    {
    	$aOut = array();
    	$iRet = 0;
    	$sPath = $this->msTmpPath . self::F_PREFIX .'*';
    	exec(self::CMD_ls .' '. $sPath, $aOut, $iRet);
    	if(0 == $iRet)
    	{
			foreach ($aOut as $sNode)
				unlink($sNode);
			unset($aOut);
    	}
    }
}
?>