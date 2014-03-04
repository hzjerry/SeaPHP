<?PHP
/**
 * 文件操作类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130428
 * @package SPFW.core.lib.final
 * @final
 * */
final class CFileOperation
{
	/**
	 * 目录过滤（当前目录与上一级目录）
	 * @var array
	 */
	static private $aDirFilter = array('.', '..');
    /**
     * 删除整颗目录树与文件
     * @param string $sPath 目录名称（结尾不要加'/'）
     * @return bool
     * @static
     * @access public
     */
    static public function delDirAndFile($sPath)
    {
        if ( false !== ($handle = opendir($sPath)) )
        {
            while ( false !== ( $item = readdir( $handle ) ) )
            {
                if ( !in_array($item, self::$aDirFilter) )
                {
                	$sSubPath = $sPath .'/'. $item;
                    if ( is_dir( $sSubPath ) ) //进入递归删除目录下的每个文件
                        self::delDirAndFile( $sSubPath );
                    else
						unlink( $sSubPath );//逐个删除文件
                }
            }
            closedir( $handle );
            rmdir( $sPath );//最后将主目录删除
            return true;
        }
        else
        	return false;
    }

    /**
     * 删除目录下所有文件（递归整颗树，不删除目录）
     *
     * @param string $sPath  目录名称(结尾要加'/')
     * @return boolean
     * @static
     * @access public
     */
    static public function delFileUnderDir($sPath)
    {
        if ( false !== ($handle = opendir( $sPath )) )
        {
            while ( false !== ( $item = readdir( $handle ) ) )
            {
                if ( !in_array($item, self::$aDirFilter) )
                {
                	$sSubPath = $sPath .'/'. $item;
                    if ( is_dir( $sSubPath ) )
                        self::delFileUnderDir( $sSubPath );//进入递归删除目录下的每个文件
                    else
                        unlink( $sSubPath );//逐个删除文件
                }
            }
            closedir( $handle );
            return false;
        }
        else
       		return true;
    }

    /**
     * 创建目录<br/>
     *  备注:当路径的目录不存在，则创建整个路径的所有目录
     * @param string $sPath 目录名称
     * @param int $iMode 目录访问属性[0644:所有者可读写，其他人只读]|[0666:仅仅没有可执行权限][0777:最大权限可执行]
     * @return bool true:创建成功/false:无法创建目录
     */
    static public function creatDir($sPath, $iMode=0777)
    {
        if (substr($sPath, -1, 1) == "/")
            $sPath = substr($sPath, 0, -1);
        $aPath = explode("/", $sPath);
        $sPathTmp = '';
        foreach ($aPath as $sNode)
        {
            $sPathTmp .= $sNode ."/"; //逐层目录检查
            if (! file_exists($sPathTmp))//逐级检查目录是否存在
            {
                if (!mkdir($sPathTmp, $iMode))//目录不存在，则创建目录
                    return false;
            }
        }
        return true;
    }

    /**
     * 移动文件到另外一个位置
     *  给定目录路径，如果路径的目录不存在，则创建整个路径的所有目录
     * @param string $sSource 源文件路径
     * @param string $sDest 目标文件路径
     * @return bool true:移动成功/false:移动失败
     * @static
     * @access public
     */
    static public function moveFile($sSource, $sDest)
    {
    	if (file_exists($sSource))
    	{
	        if (copy($sSource, $sDest))
	            if(unlink($sSource))
	                return true;
    	}
        return false;
    }

    /**
     * 文件改名(但保留扩展名不变)
     *
     * @param string $sFPath 文件路径(物理路径)
     * @param string $sFname 原始文件名(包含扩展名)
     * @param string $sNewFName 新文件名(不包含扩展名)
     * @return string | nullnull 改名后的文件名(返回新文件名包含扩展名，不含路径)
     * @static
     * @access public
     */
    static public function renameKeepExt($sFPath, $sFname, $sNewFName)
    {
        $sFPath = (substr($sFPath, -1) == '/')? $sFPath : $sFPath .'/';
        $aTmp = pathinfo($sFname);
        $sExt = $aTmp["extension"]; //获取扩展名
        unset($aTmp);
        if (file_exists($sFPath))
        {
            /*如果存在与新文件名相同的文件，则删除与新文件同名的文件。*/
            if (file_exists($sFPath . $sNewFName .'.'. $sExt))
                unlink($sFPath . $sNewFName .'.'. $sExt);
            /*文件改名*/
            if (rename($sFPath . $sFname, $sFPath . $sNewFName .'.'. $sExt))
                return $sNewFName .'.'. $sExt;
            else
                return null;
        }
        else
            return null;
    }

    /**
     * 文件或目录权限检查函数<br />
     * 返回: 取值范围为{0 <= x <= 15}，每个值表示的含义可由四位二进制数组合推出。
     * 返回值在二进制计数法中，四位由高到低分别代表
     * 可执行rename()函数权限、可对文件追加内容权限、可写入文件权限、可读取文件权限。
     * 0x02 & file_mode_info(...) //目录有写入权限
     *
     * @param  string  $sFilePath   文件路径
     * @return int
     * @access public
     * @static
     */
	static public function file_mode_info($sFilePath)
	{
	    /* 如果不存在，则不可读、不可写、不可改 */
	    if (!file_exists($sFilePath))
	        return false;

	    $mark = 0;
	    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
	    {
	        /* 测试文件 */
	        $test_file = $sFilePath . '/cf_test.txt';
	        /* 如果是目录 */
	        if (is_dir($sFilePath))
	        {
	            /* 检查目录是否可读 */
	            $dir = @opendir($sFilePath);
	            if ($dir === false)
	                return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
	            if (@readdir($dir) !== false)
	                $mark ^= 1; //目录可读 001，目录不可读 000
	            @closedir($dir);
	            /* 检查目录是否可写 */
	            $fp = @fopen($test_file, 'wb');
	            if ($fp === false)
	                return $mark; //如果目录中的文件创建失败，返回不可写。
	            if (@fwrite($fp, 'directory access testing.') !== false)
	                $mark ^= 2; //目录可写可读011，目录可写不可读 010
	            @fclose($fp);
	            @unlink($test_file);
	            /* 检查目录是否可修改 */
	            $fp = @fopen($test_file, 'ab+');
	            if ($fp === false)
	                return $mark;
	            if (@fwrite($fp, "modify test.\r\n") !== false)
	                $mark ^= 4;
	            @fclose($fp);
	            /* 检查目录下是否有执行rename()函数的权限 */
	            if (@rename($test_file, $test_file) !== false)
	                $mark ^= 8;
	            @unlink($test_file);
	        }
	        /* 如果是文件 */
	        elseif (is_file($sFilePath))
	        {
	            /* 以读方式打开 */
	            $fp = @fopen($sFilePath, 'rb');
	            if ($fp)
	                $mark ^= 1; //可读 001
	            @fclose($fp);
	            /* 试着修改文件 */
	            $fp = @fopen($sFilePath, 'ab+');
	            if ($fp && @fwrite($fp, '') !== false)
	                $mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
	            @fclose($fp);
	            /* 检查目录下是否有执行rename()函数的权限 */
	            if (@rename($test_file, $test_file) !== false)
	                $mark ^= 8;
	        }
	    }
	    else
	    {
	        if (@is_readable($sFilePath))
	            $mark ^= 1;
	        if (@is_writable($sFilePath))
	            $mark ^= 14;
	    }
	    return $mark;
	}
}
?>