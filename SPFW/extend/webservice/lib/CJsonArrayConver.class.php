<?php
/**
 * Json与Php的XML结构数组双向映射处理<br/>
 *   备注:严格遵照XML 1.0协议标准，所有属性名称与tag名称都将强制转换为小写,强制字符集编码为utf-8
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130419
 * @package SPFW.entend.webservice.lib
 * @see CConverCharset
 * @example
 *  $oXml = new CJsonArrayConver();
 *  $aRet = $oXml->getArray($data);
 *  _dbg(strtr($oXml->getXML($aRet), CXmlArrayConver::$msEntities));
 * */
class CJsonArrayConver implements IXmlJsonConverArray
{
	/**
	 * xml输出时格式化用的前缀符号
	 * @var string
	 */
	const PREFIX = "\t";
	/**
	 * JSON中待转换的实体内容
	 * @var string
	 */
	static public $msEntities =
		array('"' => '\\"', '\\'=>'\\\\', '/'=>'\\/', /*"\b"=>'//b'(这个不对应该是"\b"=chr(0x08)，无法输入),*/
			  "\f"=>'\\f', "\n"=>'\\n', "\r"=>'\\r', "\t"=>'\\t');
	/**
	 * 字符集转换对象
	 * @var CConverCharset
	 * @access private
	 * */
	private $moConvt = null;
	/**
	 * 输出格式化开关
	 * @var bool
	 * @access private
	 * */
	private $mbOutputFormat = false;
	/**
	 * XML根节点
	 * @var string
	 * @access private
	 * */
	private $msRootNode = null;

	/**
	 * 构造函数
	 * @param string $sRoot('boot') 根节点名称
	 * @param string $sCharset 必须使用utf-8(不得使用其他字符集)
	 * @see IXmlJsonConverArray::__construct()
	 */
	public function __construct($sRoot = 'boot', $sCharset = 'utf-8')
	{
		if (function_exists('json_encode') && function_exists('json_decode'))
		{
			$this->msRootNode = $sRoot;
			$this->msCharset = 'utf-8';
			$this->moConvt = new CConverCharset($this->msCharset);
		}
		else
		{
    		echo '<br/>['. __CLASS__. ']:json system function json_encode or json_decode does not exist.';
    		echo '<br/>PHP5.2 and above can use.';
    		exit();
		}
	}

	/**
	 * 根据传入的Json字符串得到，XML结果数组<br/>
	 * @see IXmlJsonConverArray::getArray()
	 */
	public function getArray($sData)
	{
		//Remove UTF-8 BOM if present, json_decode() does not like it.
		if(substr($sData, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF))
			$sData = substr($sData, 3);

		$aJson = json_decode($sData, true);
		unset($sData);
		if (is_null($aJson))
		{
_dbg(json_last_error());
			return null;
		}
		else
		{	/*将字符集转换成本地字符集*/
			if ($this->moConvt->differenceCharset())
				$this->toLocalCharset($aJson);
			return $aJson[$this->msRootNode];
		}
	}

	/**
	 * 根据传入的数组得到Json结构的字符协议串包<br/>
	 * @see IXmlJsonConverArray::getStrPacket()
	 */
	public function getStrPacket($aArr)
	{
		if($this->mbOutputFormat)
		{	//可显示格式
			$aTmp = array();
			array_push($aTmp, '{"{@root}":', "\n", '{@data}', "\n}");
			$aTmp = implode('', $aTmp);

			$sRet = $this->array2json($aArr);
			return strtr($aTmp, array('{@root}'=>$this->msRootNode ,'{@data}'=>"{\n". substr($sRet['val'], 0, -2) ."\n}"));
		}
		else
		{	//传输格式
			$aTmp = '{"{@root}":{@data}}';
			return strtr($aTmp, array('{@root}'=>$this->msRootNode ,'{@data}'=>json_encode(self::toLowerArrayKey($aArr))));
		}
	}

	/**
	 * 将数组转换成JSON字符串
	 * @param array $aArray  保存需要转换成XML的数据数据包
	 * @param int $iLevel 递归的时返回当前层级
	 * @return string
	 */
	private function array2json(& $aArray, $iLevel=1)
	{
		if (empty($aArray))
			return null;
		else
		{
			if (array_key_exists('C',$aArray))
				return array('val'=>$this->encodeCharset($aArray['C']), 'type'=>'c');//遇到叶子将节点，直接返回其content
		}

		$aBuf = array();
		foreach ($aArray as $sTag => & $aVal)
		{
			if ('A' == $sTag)//不处理这个Tag
				continue;
			elseif (isset($aVal[0]))
			{	//探测到当前层有同名tag节点
				$aLeaf = array();
				array_push($aLeaf, str_repeat(self::PREFIX, $iLevel), self::S(strtolower($sTag)),
						   ":\n", str_repeat(self::PREFIX, $iLevel), '['); //构建tag头
				foreach ($aVal as & $aNode)
				{
					array_push($aLeaf, "{\n");
					//如果存在属性则构建属性
					if (array_key_exists('A', $aNode))
					{
						$aLeaf[] = str_repeat(self::PREFIX, $iLevel+1);
						array_push($aLeaf, '"A":{', $this->getAttribStr($aNode['A']), "},\n");
					}

					$aSubNode = $this->array2json($aNode, $iLevel+1); //递归
					if (!empty($aSubNode))
					{
						if ($aSubNode['type'] == 'c')
						{
							$aLeaf[] = str_repeat(self::PREFIX, $iLevel+1);
							array_push($aLeaf, '"C":', $aSubNode['val'], "\n");
						}
						else
						{
							if (substr($aSubNode['val'], -2) == ",\n")
								array_push($aLeaf, substr($aSubNode['val'],0,  -2), "\n");//去除拖尾','
							else
								array_push($aLeaf, "\n", $aSubNode['val'], "\n");//加入嵌套内容
						}
					}
					unset($sSubNode);
					//删除拖尾','
					$sTmp = implode('', $aLeaf);
					unset($aLeaf);
					$aLeaf = array();
					if (substr($sTmp, -2) == ",\n")
						$aLeaf[] = substr($sTmp, 0, -2) ."\n";
					else
						$aLeaf[] = $sTmp;
					unset($sTmp);
					array_push($aLeaf, str_repeat(self::PREFIX, $iLevel), "},\n", str_repeat(self::PREFIX, $iLevel));
				}
				array_push($aBuf, substr(implode('', $aLeaf), 0, -($iLevel+2)), "],\n");
				unset($aLeaf);
			}
			else
			{	//处理同层兄弟节点
				$aLeaf = array();
				array_push($aLeaf, str_repeat(self::PREFIX, $iLevel), self::S(strtolower($sTag)),
						   ":\n", str_repeat(self::PREFIX, $iLevel), "{\n"); //构建tag头
				//如果存在属性则构建属性
				if (array_key_exists('A', $aVal))
				{
					$aLeaf[] = str_repeat(self::PREFIX, $iLevel+1);
					array_push($aLeaf, '"A":{', $this->getAttribStr($aVal['A']), "},\n");
				}

				$aSubNode = $this->array2json($aVal, $iLevel+1); //递归
				if (!empty($aSubNode))
				{
					if ($aSubNode['type'] == 'c')
					{
						$aLeaf[] = str_repeat(self::PREFIX, $iLevel+1);
						array_push($aLeaf, '"C":', $aSubNode['val']);
					}
					else
					{
						if (substr($aSubNode['val'], -1) == "\n")
							array_push($aLeaf, $aSubNode['val']);
						else
							array_push($aLeaf, $aSubNode['val'], "\n");
					}
				}
				unset($sSubNode);
				//删除拖尾','
				$sTmp = implode('', $aLeaf);
				if (substr($sTmp, -2) == ",\n")
					$aBuf[] = substr($sTmp, 0, -2) ."\n";
				else
					$aBuf[] = $sTmp;
				unset($aLeaf, $sTmp);
				array_push($aBuf, "\n", str_repeat(self::PREFIX, $iLevel), "},\n");
			}
		}
		if (count($aBuf) == 0)
			return null; //节点中没有内容
		else
			return array('val'=>implode('', $aBuf), 'type'=>'a');
	}

	/* (non-PHPdoc)
	 * @see IXmlJsonConverArray::setShowFormat()
	 */
	public function setShowFormat($bOpen = true)
	{
		$this->mbOutputFormat = $bOpen;
	}

	/**
	 * 把数组内容转换为本地字符集
	 * @param array $aArr 待转换的数组
	 * @return void
	 * @access private
	 */
	private function toLocalCharset(& $aArr)
	{
		static $SysTag = array('A','C');
		//判断输入内容是否有效
		if (!is_array($aArr))
			return;
		//开始遍历本层的每个节点
		foreach ($aArr as $Key => & $Val)
		{
			if (is_array($Val) || is_object($Val)) //如果是数组或对象则继续递归
				$this->toLocalCharset($Val);
			else //找到非数组节点，表示这个为值
			{
				if (is_string($Val)) //只对UTF-8内容进行转换
					$Val = $this->moConvt->toLocal($Val);

				/*强制将Key转换为小写*/
				if (!in_array($Key, $SysTag))
				{
					$aData[strtolower($Key)] = $Val;
					unset($aData[$Key]);
				}
			}
		}
	}

	/**
	 * 字符串内容封装
	 * @param string $sStr 待转换的字符串
	 * @return string 输出内容会自动加上双引号
	 * @access private
	 * @static
	 */
	static private function S($sStr)
	{
		return '"'. trim($sStr) .'"';
	}

	/**
	 * 将字符集编码成JSON所支持的字符集
	 * @param string $sStr 数据
	 * @return string
	 * @access private
	 */
	private function encodeCharset(& $sStr)
	{

		if (is_string($sStr) && $this->moConvt->differenceCharset())
		{
			$sRet = $this->moConvt->toTarget(trim($sStr));
			$sRet = strtr($sRet, self::$msEntities);
			return '"'. $sRet  .'"';
		}
		elseif (is_int($sStr) || is_float($sStr))
		{
			return $sStr;
		}
		elseif (is_bool($sStr))
		{
			return ($sStr)? 'true' : 'false';
		}
	}

	/**
	 * 输出属性字符串
	 * @param array $aArr 待转换的字符串
	 * @return string 输出内容会自动加上双引号
	 * @access private
	 */
	private function getAttribStr($aArr)
	{
		$aBuf = array();
		foreach ($aArr as $sKey => $sVal)
			array_push($aBuf, self::S($sKey), ':', $this->encodeCharset($sVal), ', ');
		return substr(trim(implode('', $aBuf)), 0, -1);
	}

	/**
	 * 将数组的Key，转换为小写
	 * @param array $aVal 需要转换的数组
	 * @return array
	 * @access private
	 * @static
	 */
	static private function toLowerArrayKey(& $aVal)
	{
		$aBuf = array();
		foreach ($aVal as $sKey => $sVal)
			$aBuf[strtolower($sKey)] = $sVal;
		return $aBuf;
	}
}

?>