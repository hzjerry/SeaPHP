<?php
/**
 * 文件缓存类 FileCache<br/>
 * 依赖:CFileOperation类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130503
 * @package SPFW.extend.cache.lib
 * @see CFileOperation::delDirAndFile()
 * @see CFileOperation::creatDir()
 * @example
 * <pre>
 * $oFC = new CFileCache(); //创建文件缓存类
 * $sKey = 'ab_123'; //缓存键值
 * $data = $oFC->get($sKey); //取得缓存
 * if(is_null($data))
 * &nbsp;&nbsp;$oFC->set($sKey, array('name'=>'ttt', 'datetime'=>date('Y-m-d H:i:s')), 10); //缓存不存在创建缓存
 * print_r($data);
 * </pre>
 */
final class CFileCache implements ICacheEngine
{
	/**
	 * 默认缓存失效时间(1小时)
	 * @var int
	 */
	const miEXPIRE = 3600;
	/**
	 * 缓存目录包位置
	 * @var string
	 */
	const msCACHE_PACKAGE  = 'cache.filecache'; //文件级缓存保存包位置
	/**
	 * 缓存文件绝对根路径
	 * @var string
	 * @static
	 */
	private static $msCachePath = null;

	/**
	 * 构造函数
	 */
	function __construct()
	{
		$this->loagCfg();
	}

	/**
	 * 载入基本配置
	 * @return void
	*/
	public function loagCfg()
	{
		if (is_null(self::$msCachePath))
			self::$msCachePath = getMAC_ROOT() . getFW_ROOT() . strtr(self::msCACHE_PACKAGE, array('.'=>'/')) .'/';
	}

	/* (non-PHPdoc)
	 * @see CCacheEngine::get()
	 */
	public function get($sKey)
	{
		if(empty($sKey))
			return false;

		$sFile  = self::getFileName($sKey);
		if(!file_exists($sFile))
			return null;
		else
		{
			$handle = fopen($sFile,'rb');
			flock($handle , LOCK_SH);//共享锁
			if (intval(fgets($handle)) > time())//检查时间戳
			{	//未失效期，取出数据
				$sData = fread($handle, filesize($sFile));
				flock($handle , LOCK_UN);//释放锁
				fclose($handle);
				return unserialize($sData);
			}
			else
			{	//已经失效期
				flock($handle , LOCK_UN);//释放锁
				fclose($handle);
				unlink($sFile); //删除失效文件
				return null;
			}
		}
	}

	/* (non-PHPdoc)
	 * @see CCacheEngine::increment()
	*/
	public function increment($sKey, $iVal = 1)
	{
		if(empty($sKey))
			return null;

		$sFile  = self::getFileName($sKey);
		if(!file_exists($sFile))
			return null; //键值不存在
		else
		{
			$handle = fopen($sFile,'r+b');
			flock($handle , LOCK_EX);//独占锁
			$iFileSize = filesize($sFile); //获取文件大小
			rewind($handle); //移动到文件头
			$iTimestemp = intval(fgets($handle)); //取出时间戳
			if ($iTimestemp > time())//检查时间戳
			{	//未失效期，取出数据
				$sData = fread($handle, $iFileSize);//取出缓存值
				/*将缓存取出后，立即写回*/
				$iCnt = intval(unserialize($sData)) + $iVal; //缓存值增量
				$sTmp = $iTimestemp ."\n". serialize($iCnt); //生成缓存值
				rewind($handle); //移动到文件头
				fwrite($handle, str_pad($sTmp, $iFileSize, ' ')); //写回文件，如果长度不够则末尾补空格
				flock($handle , LOCK_UN);//释放锁
				fclose($handle); //立即关闭file io
				return $iCnt;
			}
			else
			{	//已经失效期
				flock($handle , LOCK_UN);//释放锁
				fclose($handle);
// 				unlink($sFile); //删除失效文件
				return null;
			}
		}
	}

	/* (non-PHPdoc)
	 * @see CCacheEngine::decrement()
	*/
	public function decrement($sKey, $iVal = 1)
	{
		if(empty($sKey))
			return null;

		$sFile  = self::getFileName($sKey);
		if(!file_exists($sFile))
			return null; //键值不存在
		else
		{
					$handle = fopen($sFile,'r+b');
			flock($handle , LOCK_EX);//独占锁
			$iFileSize = filesize($sFile); //获取文件大小
			rewind($handle); //移动到文件头
			$iTimestemp = intval(fgets($handle)); //取出时间戳
			if ($iTimestemp > time())//检查时间戳
			{	//未失效期，取出数据
				$sData = fread($handle, $iFileSize);//取出缓存值
				/*将缓存取出后，立即写回*/
				$iCnt = intval(unserialize($sData)) - $iVal; //缓存值增量
				$sTmp = $iTimestemp ."\n". serialize($iCnt); //生成缓存值
				rewind($handle); //移动到文件头
				fwrite($handle, str_pad($sTmp, $iFileSize, ' ')); //写回文件，如果长度不够则末尾补空格
				flock($handle , LOCK_UN);//释放锁
				fclose($handle); //立即关闭file io
				return $iCnt;
			}
			else
			{	//已经失效期
				flock($handle , LOCK_UN);//释放锁
				fclose($handle);
// 				unlink($sFile); //删除失效文件
				return null;
			}
		}
	}

	/* (non-PHPdoc)
	 * @see CCacheEngine::set()
	 */
	public function set($sKey, $mVal, $iExpire=null)
	{
		if(empty($sKey))
			return false;

		$sFile = self::getFileName($sKey);
		if (!file_exists(dirname($sFile)))
			if (!self::is_mkdir(dirname($sFile)))
				return false;

		$aBuf = array();
		$aBuf[] = time() + ((empty($iExpire)) ? self::miEXPIRE : intval($iExpire));
		$aBuf[] = serialize($mVal);
		/*写入文件操作*/
		$handle = fopen($sFile,'wb');
		flock($handle , LOCK_EX);//独占锁
		fwrite($handle, implode("\n", $aBuf));
		flock($handle , LOCK_UN);//解锁
		fclose($handle);
		return true;
	}

	/* (non-PHPdoc)
	 * @see CCacheEngine::del()
	 */
	public function del($sKey)
	{
		if(empty($sKey))
			return false;
		else
		{
			@unlink(self::getFileName($sKey));
			return true;
		}
	}

	/**
	 * 获取缓存文件全路径<br />
	 * 返回: 缓存文件全路径<br />
	 * $sKey的值会被转换成md5(),并分解为3级目录进行访问
	 * @param string $sKey 缓存键
	 * @return string
	 * @access protected
	 */
	private static function getFileName($sKey)
	{
		if(empty($sKey))
			return false;

		$key_md5 = md5($sKey);
		$aFileName = array();
		$aFileName[]  = rtrim(self::$msCachePath,'/');
		$aFileName[]  = $key_md5{0} . $key_md5{1};
		$aFileName[]  = $key_md5{2} . $key_md5{3};
		$aFileName[]  = $key_md5{4} . $key_md5{5};
		$aFileName[]  = $key_md5;
		return implode('/', $aFileName);
	}

	/**
	 * 创建目录<br />
	 *
	 * @param string $sDir
	 * @return bool
	 */
	private static function is_mkdir($sDir='')
	{
		if(empty($sDir))
			return false;
		/*如果无法创建缓存目录，让系统直接抛出错误提示*/
		return CFileOperation::creatDir($sDir);
	}

	/* (non-PHPdoc)
	 * @see CCacheEngine::gc()
	 */
	public function gc()
	{
		return CFileOperation::delDirAndFile(substr(self::$msCachePath, 0, -1));
	}

}
?>