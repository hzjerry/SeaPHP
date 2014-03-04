<?php
/**
 * 网站引擎Session操作对象<br/>
 * 本类通过$sPart分区方式来管理Session,可以让同一个域下的不同的工作区共享Session,而互不会造成冲突
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130724
 * @package SPFW.extend.website_engine.lib
 */
class CSessionOperat
{
	/**
	 * 分区名称
	 * @var string
	 */
	protected $msPart = null;
	/**
	 * 分区关键字
	 * @var string
	 */
	protected $msPartKey = null;

	/**
	 * 构造<br/>
	 * 设定Session的所属分区
	 * @param string $sPart
	 */
	function __construct($sPart)
	{
		$this->msPart = $sPart; //设置分区名称
		$this->msPartKey = __CLASS__.'_'.$this->msPart; //生成分区关键字

		/*如果Session未启用,则启动*/
		if (!isset($_SESSION))
			session_start();
	}

	/**
	 * 返回当前Session的所属分区名称
	 * @return string
	 */
	public function getPart()
	{
		return $this->msPart;
	}

	/**
	 * 保存Session数据
	 * @param string $sKey 哈希关键字
	 * @param mixed $aData 需要保存的数据
	 * @return void
	 */
	public function set($sKey, $aData)
	{
		$_SESSION[$this->msPartKey][$sKey] = $aData;
	}

	/**
	 * 获取Session数据
	 * @param string $sKey 哈希关键字
	 * @return mixed|null
	 */
	public function get($sKey)
	{
		if (isset($_SESSION[$this->msPartKey][$sKey]))
			return $_SESSION[$this->msPartKey][$sKey];
		else
			return null;
	}

	/**
	 * 移除Session数据
	 * @param string $sKey 哈希关键字
	 * @return void
	 */
	public function del($sKey)
	{
		if (isset($_SESSION[$this->msPartKey][$sKey]))
			unset($_SESSION[$this->msPartKey][$sKey]);
	}

	/**
	 * 清除分区中的Session数据
	 * @return void
	 */
	public function destroy()
	{
		if (isset($_SESSION[$this->msPartKey]))
			unset($_SESSION[$this->msPartKey]);
	}

	/**
	 * 取出某个分区内的所有Session数据
	 * @param string $sPart 分区名称
	 * @return array|null:
	 */
	static function getPartData($sPart)
	{
		$sPartKey = __CLASS__.'_'.$sPart; //分区Key
		if (array_key_exists($sPartKey, $_SESSION))
			return $_SESSION[$sPartKey];
		else
			return null;
	}
}
?>