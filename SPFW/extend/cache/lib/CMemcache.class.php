<?php
/**
 * 分布式内存缓存类CMemcache<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130503
 * @package SPFW.extend.cache.lib
 * @link http://www.php.net/manual/zh/class.memcache.php
 * @example
 * <pre>
 * $oFC = new CMemcache('./tmp/'); //创建文件缓存类
 * $sKey = 'ab_123'; //缓存键值
 * $data = $oFC->get($sKey); //取得缓存
 * if(is_null($data))
 * &nbsp;&nbsp;$oFC->set($sKey, array('name'=>'ttt', 'datetime'=>date('Y-m-d H:i:s')), 10); //缓存不存在创建缓存
 * print_r($data);
 * </pre>
 */
class CMemcache implements ICacheEngine
{
	/**
	 * Memcache缓存对象
	 * @var Memcache
	 */
	private static $moMC = null;

	/**
	 * 构造函数
	 */
	function __construct()
	{
		if (is_null(self::$moMC))
		{
			if (!class_exists('Memcache'))
			{
				echo '<br />Not find class Memcache. <br/>Please install php memcache plugin.';
				exit(0);
			}
			else
			{
				self::$moMC = new Memcache;
				$this->loadCfg();
			}
		}
	}

	/**
	 * 析构函数
	 */
	function __destruct()
	{
		self::$moMC->close();
		self::$moMC = null;
	}

	/**
	 * 载入配置
	 * @return void
	 */
	protected function loadCfg()
	{
		$aCfg = import('extend.cache.config', 'memcache.cfg.php');
		if (count($aCfg) >= 1)
		{
			foreach ($aCfg as $aNode)
			{
				if (self::$moMC->connect($aNode['host'], $aNode['port'], $aNode['timeout']))
					return; //连接建立
			}
			/*如果运行到这儿表示memcache服务器连接不上*/
			echo '<br />memcache connect fail.';
			exit(0);
		}
		else
		{
			echo '<br />memcache config load fail.';
			exit(0);
		}
	}

	/* (non-PHPdoc)
	 * @see ICacheEngine::del()
	 */
	public function del($sKey)
	{
		return self::$moMC->delete($sKey);
	}

	/* (non-PHPdoc)
	 * @see ICacheEngine::get()
	 */
	public function get($sKey)
	{
		if (($mVal = self::$moMC->get($sKey)) === false)
			return null;
		else
			return $mVal;
	}

	/* (non-PHPdoc)
	 * @see ICacheEngine::set()
	 */
	public function set($sKey, $mVal, $iExpire = null)
	{
		$compress = is_bool($mVal) || is_int($mVal) || is_float($mVal) ? false : MEMCACHE_COMPRESSED;
		return self::$moMC->set($sKey, $mVal, $compress, $iExpire);
	}

	/* (non-PHPdoc)
	 * @see ICacheEngine::increment()
	 */
	public function increment($sKey, $iVal = 1)
	{
		if (($mRet = self::$moMC->increment($sKey, $iVal)) === false)
			return null;
		else
			return $mRet;
	}

	/* (non-PHPdoc)
	 * @see ICacheEngine::decrement()
	 */
	public function decrement($sKey, $iVal = 1)
	{
		if (($mRet = self::$moMC->decrement($sKey, $iVal)) === false)
			return null;
		else
			return $mRet;
	}

	/* (non-PHPdoc)
	 * @see ICacheEngine::gc()
	 */
	public function gc()
	{
		return self::$moMC->flush();
	}
}

?>