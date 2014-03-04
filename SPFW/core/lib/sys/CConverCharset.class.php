<?php
defined('SEA_PHP_RUNTIME') or exit('sea php framework initialization step is not valid.'); //功能:让框架必须按顺序加载
/**
 * 字符集编码转换<br />
 * 依赖:CENV
 * @author Jerryli(hzjerry@gmail.com)
 * @package SPFW.core.lib.sys
 * */
class CConverCharset
{
	/**
	 * @var string | 本地字符集
	 * @access public
	 * @global SEA_PHP_FW_VAR_ENV
	 * */
	static public $msLocal = null;
	/**
	 * @var string | 目标字符集
	 * @access public
	 * */
	public $msTarget = null;

	/**
	 * 构造函数
	 * @param string $sTargetCharset 目标字符集(默认为本地字符集)
	 * @see CENV::getCharset()
	 * */
	function __construct($sTargetCharset = null)
	{
		if (is_null(self::$msLocal))
			self::$msLocal = CENV::getCharset(); //获取本地字符集编码
		$this->msTarget = (empty($sTargetCharset))? self::$msLocal : strtolower($sTargetCharset);
	}

	/**
	 * 判断字符串是否为UTF-8格式
	 *
	 * @param string $sStr
	 * @access public
	 * @return boolean
	 * @static
	 */
	static public function isUTF8($sStr)
	{
		static $sPreg =
		'%^(?:
		[\x09\x0A\x0D\x20-\x7E] # ASCII
		| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
		| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
		| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
		| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
		| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
		)*$%xs';
		return preg_match($sPreg, $sStr);
	}

	/**
	 * 转换成本地字符集
	 * @param string $sStr 待转换的字符集
	 * @return string
	 * @access public
	 * */
	public function toLocal($sStr)
	{
		if (self::$msLocal == $this->msTarget)
			return $sStr;
		else
			return iconv($this->msTarget, self::$msLocal, $sStr);
	}

	/**
	 * 转换成目标字符集
	 * @param string $sStr 待转换的字符集
	 * @return string
	 * @access public
	 * */
	public function toTarget($sStr)
	{
		if (self::$msLocal == $this->msTarget)
			return $sStr;
		else
			return iconv(self::$msLocal, $this->msTarget, $sStr);
	}

	/**
	 * 检查本地字符集与目标字符集设置是否有差异
	 * @return boolean
	 * @access public
	 * */
	public function differenceCharset()
	{
		return ($this->msTarget == self::$msLocal);
	}
}

?>