<?php
/**
 * 数据缓存操作类<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130513
 * @package SPFW.extend.cache.runtime
 */
class CCache extends CExtModule
{
	/**
	 * 缓存处理对象
	 * @var ICacheEngine
	 */
	private static $moCache = null;
	/**
	 * 当前使用的缓存类型(memcache | filecache)
	 * @var string
	 */
	private static $msCacheType = null;

	/**
	 * 构造函数
	 */
	function __construct()
	{
		parent::__construct();
		$this->loadCfg();
	}

	/**
	 * 析构函数
	 */
	function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * 载入配置文件
	 */
	private function loadCfg()
	{
		$aCfg = import('extend.cache.config', 'environment.cfg.php');
		if ('filecache' === $aCfg['cache_type'])
			self::$moCache = new CFileCache();
		elseif ('memcache' === $aCfg['cache_type'])
			self::$moCache = new CMemcache();
		else
		{
			echo '<br />Cache: cache_type invalid in environment.cfg.php.',
				 '<br />cache_type:', $aCfg['cache_type'];
			exit(0);
		}
		self::$msCacheType = $aCfg['cache_type'];
		unset($aCfg);
	}

	/* (non-PHPdoc)
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile()
	{
		$this->merger2autoload('extend.cache.config', 'autoload.cfg.php');
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
		$aRet = array();
		if (class_exists('Memcache'))
			$aRet['CMemcache (Memcache)'] = true;
		else
			$aRet['CMemcache (Memcache)'] = false;

		$sPath = getMAC_ROOT(). getFW_ROOT() . strtr(CFileCache::msCACHE_PACKAGE, array('.'=>'/'));
		if (!CFileOperation::creatDir($sPath)) //创建缓存目录
			$aRet['CFileCache (Write permissions: '. $sPath .')'] = false;
		if ((0x02 & CFileOperation::file_mode_info($sPath)) === 0)
			$aRet['CFileCache (Write permissions: '. $sPath .')'] = false;
		else
			$aRet['CFileCache (Write permissions: '. $sPath .')'] = true;
		return $aRet;
	}

	/**
	 * 删除指定的缓存键值
	 *
	 * @param string $sKey 缓存键值
	 * @return bool
	 */
	public function del($sKey)
	{
		return self::$moCache->del($sKey);
	}

	/**
	 * 读取缓存<br />
	 * 返回: 缓存内容,字符串或数组；缓存为空或过期返回null
	 * @param string $sKey 缓存键值(无需做md5())
	 * @return string | null
	 * @access public
	 */
	public function get($sKey)
	{
		return self::$moCache->get($sKey);
	}

	/**
	 * 写入缓存
	 *
	 * @param string $sKey 缓存键值
	 * @param mixed $mVal 需要保存的对象
	 * @param int $iExpire 失效时间
	 * @return bool
	 * @access public
	 */
	public function set($sKey, $mVal, $iExpire = null)
	{
		return self::$moCache->set($sKey, $mVal, $iExpire);
	}

	/**
	 * 指定键值增量<br />
	 * 返回: null表示键值未创建，先调用set()方法创建键值
	 * @param string $sKey 缓存键值
	 * @param int $iVal 增量值(必须&gt;0,默认为1)
	 * @return int | null
	 */
	public function increment($sKey, $iVal = 1)
	{
		if (($mRet = self::$moCache->increment($sKey, $iVal)) === false)
			return null;
		else
			return $mRet;
	}

	/**
	 * 指定键值减量<br />
	 * 返回: null表示键值未创建，先调用set()方法创建键值
	 * @param string $sKey 缓存键值
	 * @param int $iVal 减量值(必须&gt;0,默认为1)
	 * @return int | null
	 */
	public function decrement($sKey, $iVal = 1)
	{
		if (($mRet = self::$moCache->decrement($sKey, $iVal)) === false)
			return null;
		else
			return $mRet;
	}

	/**
	 * 回收所有缓存资源
	 * @return bool
	 */
	public function gc()
	{
		return self::$moCache->gc();
	}
}

?>