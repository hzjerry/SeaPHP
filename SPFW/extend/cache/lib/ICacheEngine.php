<?php
/**
 * 文件缓存引擎接口<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130513
 * @package SPFW.extend.cache.lib
 */
interface ICacheEngine
{
	/**
	 * 删除指定的缓存键值
	 *
	 * @param string $sKey 缓存键值
	 * @return bool
	 */
	public function del($sKey);

	/**
	 * 读取缓存<br />
	 * 返回: 缓存内容,字符串或数组；缓存为空或过期返回null
	 * @param string $sKey 缓存键值(无需做md5())
	 * @return string | array | null
	 * @access public
	 */
	public function get($sKey);

	/**
	 * 写入缓存
	 *
	 * @param string $sKey 缓存键值
	 * @param mixed $mVal 需要保存的对象
	 * @param int $iExpire 失效时间
	 * @return bool
	 * @access public
	 */
	public function set($sKey, $mVal, $iExpire=null);

	/**
	 * 指定键值增量<br />
	 * 返回: null表示键值未创建，先调用set()方法创建键值
	 * @param string $sKey 缓存键值
	 * @param int $iVal 增量值(必须&gt;0,默认为1)
	 * @return int | null
	 */
	public function increment($sKey, $iVal=1);

	/**
	 * 指定键值减量<br />
	 * 返回: null表示键值未创建，先调用set()方法创建键值
	 * @param string $sKey 缓存键值
	 * @param int $iVal 减量值(必须&gt;0,默认为1)
	 * @return int | null
	 */
	public function decrement($sKey, $iVal=1);

	/**
	 * 回收所有缓存资源<br />
	 * 注意:即使缓存失效时间未到，缓存数据也将强行回收。
	 * @return bool
	 */
	public function gc();
}

?>