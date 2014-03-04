<?php
/**
 * Xml结构数组与Http的GET请求数据转换<br/>
 *   备注: 严格遵照XML 1.0协议标准，所有属性名称与tag名称都将强制转换为小写，并且用RFC 1738 对 URL 进行编码<br/>
 *   注意: 只能将一层关系的Xml结构数组且不能存在同名兄弟节点结构，转换成Get参数列表，否则返回null<br />
 *   转换协议: key1=val1&key2=val2&key1.tt=val3&key1.bt=val4 ;<br />
 *   协议解释: key后面'.'的内容表示为它的属性，'='后面的内容为key的值，转换时按照Xml结构数组结构
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130423
 * @package SPFW.extend.webservice.lib
 * @see CConverCharset
 * @example
 */
class CXmlArrayConverGET implements IXmlJsonConverArray
{
	/**
	 * 输出格式化开关
	 * @var bool
	 * @access private
	 * */
	private $mbOutputFormat = false;
	/**
	 * 字符编码转换类
	 * @var CConverCharset
	 */
	private $moConv = null;
	/* (non-PHPdoc)
	 * @see IXmlJsonConverArray::__construct()
	 */
	public function __construct($sRoot = 'boot', $sCharset = 'utf-8')
	{
		$this->moConv = new CConverCharset($sCharset);
	}

	/**
	 * @param string $sData http的get结构字符串
	 * @see IXmlJsonConverArray::getArray()
	 * @example
	 *  getArray('name=yy&guest=oo&checksum.value=erasfasefwefasdf&checksum.datetime=20130424171101');
	 */
	public function getArray($sData)
	{
		if (empty($sData) === true)
			return null;
		else
		{
			$aXml = array();
			$aList = explode('&', $sData);
			foreach ($aList as $sNode)
			{
				list($sKey, $sVal) = explode('=', $sNode); //分离键与值
				$iSepar = substr_count($sKey, '.'); //计算键中是否存在'.'
				if ($iSepar == 0)
					$aXml[strtolower($sKey)]['C'] = urldecode($sVal); //只有值
				elseif ($iSepar == 1)
				{	//存在属性
					$iSite = strpos($sKey, '.');
					$aXml[strtolower(substr($sKey, 0, $iSite))]['A'][strtolower(substr($sKey, $iSite + 1))] = rawurldecode($sVal);
				}
			}
			return $aXml;
		}
	}

	/**
	 * 根据传入的XML结构数组得到对应协议的字符串(注意：只转换第一层)
	 * @see IXmlJsonConverArray::getStrPacket()
	 */
	public function getStrPacket($aArr)
	{
		$aRet = array();
		foreach ($aArr as $sKey => $aNode)
		{
			if (isset($aNode['C']))
			{
				if ($this->mbOutputFormat)
					array_push($aRet, strtolower($sKey), '=', $aNode['C'], '&', "\n");
				else
					array_push($aRet, strtolower($sKey), '=', rawurlencode($this->moConv->toTarget($aNode['C'])), '&');
			}
			elseif (isset($aNode['A']))
			{	//存在属性，逐个添加
				foreach ($aNode['A'] as $sArrKey => $sArrVal)
				{
					if ($this->mbOutputFormat)
						array_push($aRet, strtolower($sKey), '.', strtolower($sArrKey), '=', $sArrVal, '&', "\n");
					else
						array_push($aRet, strtolower($sKey), '.', strtolower($sArrKey), '=', rawurlencode($this->moConv->toTarget($sArrVal)), '&');
				}
			}
		}
		if (count($aRet) > 0)
		{
			if ($this->mbOutputFormat) //格式化显示内容时，删除拖尾字符2个
				return substr(implode('', $aRet), 0, -2);
			else
				return substr(implode('', $aRet), 0, -1);
		}
		else
			return null;
	}

	/**
	 * 检查XML结构数组能否被转换<br/>
	 *  备注：只有一层结构的XML数组，以及不能有同名兄弟节点，符合这两条的XML结构数组才能被转换成Get字符串
	 * @param array $aXml XML结构数组
	 * @return bool
	 */
	static public function canConvert($aXml)
	{
		static $aCheck = array('C', 'A'); //常量检查
		foreach ($aXml as $sKey=>$aNode)
		{
			foreach (array_keys($aNode) as $sSubKey)
			{	//检查是否有除属性与值以外的节点
				if (in_array($sSubKey, $aCheck))
					continue;
				else
					return false;
			}
		}
		return true;
	}

	/**
	 * 注意：打开显示格式化后，getStrPacket()的输出只能观看，不能用于传输<br />
	 * @see IXmlJsonConverArray::setShowFormat()
	 */
	public function setShowFormat($bOpen = true)
	{
		$this->mbOutputFormat = $bOpen;
	}


}

?>