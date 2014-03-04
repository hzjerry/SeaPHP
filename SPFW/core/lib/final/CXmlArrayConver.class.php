<?php
/**
 * Xml与Php数组的双向映射处理<br/>
 *   备注:严格遵照XML 1.0协议标准，所有属性名称与tag名称都将强制转换为小写
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130413
 * @package SPFW.core.lib.final
 * @see CConverCharset
 * @example
 *  $oXml = new CXmlArrayConver();
 *  $aRet = $oXml->getArray($data);
 *  _dbg(strtr($oXml->getStrPacket($aRet), CXmlArrayConver::$msEntities));
 * */
final class CXmlArrayConver implements IXmlJsonConverArray
{
	/**
	 * xml输出时格式化用的前缀符号
	 * @var string
	 */
	const PREFIX = '    ';
	/**
	 * XML中待转换的实体内容
	 * @var string
	 */
	static public $msEntities = array( "&" => "&amp;", "<" => "&lt;", ">" => "&gt;", "'" => "&apos;", '"' => "&quot;" );
	/**
	 * XML根节点
	 * @var string
	 * @access private
	 * */
	private $msRootNode = null;
	/**
	 * XML字符集
	 * @var string
	 * @access private
	 * */
	private $msCharset = null;
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
	 * 构造函数
	 * @param string $sRoot('boot') 根节点名称
	 * @param string $sCharset xml使用的字符集
	 * @see IXmlJsonConverArray::__construct()
	 */
	function __construct($sRoot = 'boot', $sCharset='utf-8')
	{
		static $sPHP_VER = PHP_VERSION;
		if (intval($sPHP_VER{0}) < 5)
		{
			echo 'PHP version is too low, there is no SimpleXMLElement class.';
			exit(0);
		}
		else
		{
			$this->msRootNode = $sRoot;
			$this->msCharset = $sCharset;
			$this->moConvt = new CConverCharset($sCharset);
		}
	}

	/**
	 * 析构函数
	 */
	function __destruct()
	{
		unset($this->moConvt);
	}

	/**
	 * 将字符集编码成XML所支持的字符集
	 * @param string $sStr 数据
	 * @return string
	 */
	private function encodeCharset(& $sStr)
	{
		return strtr($this->moConvt->toTarget(trim($sStr)), self::$msEntities);
	}

	/**
	 * 将从XML中提取出来的字符集转换成本地字符集
	 * @param string $sStr 数据
	 * @return string
	 */
	private function decodeCharset(& $sStr)
	{
		static $sREntities = null;
		if (empty($sREntities))
			$sREntities = array_flip(self::$msEntities);

		return strtr($this->moConvt->toLocal(trim($sStr)), $sREntities);
	}

	/**
	 * 控制输出内容
	 * @param string $sStr 数据
	 * @return string
	 */
	private function O($sStr)
	{
		if ($this->mbOutputFormat)
			return $sStr;
		else
			return '';
	}

	/**
	 * 根据传入的XML得到XML数组
	 * @param string $sXML XML数据包
	 * @return array | null
	 * @see IXmlJsonConverArray::getArray()
	 */
	public function getArray($sXML)
	{
		$moXml = @simplexml_load_string($sXML);
		if ($moXml === false)
			return null;
		else
		{
			$sRet = $this->xml2array($moXml);
			unset($moXml);
			return $sRet;
		}
	}

	/**
	 * 根据传入的数组得到XML结构的字符协议串包
	 * @param array $aArr XML结构数组
	 * @return string
	 * @see IXmlJsonConverArray::getStrPacket()
	 */
	public function getStrPacket($aArr)
	{
		static $sBase = null;
		if (empty($sBase))
		{
			$aTmp = array();
			$aTmp[] = '<?xml version=\'1.0\' encoding=\'{@charset}\'?>';
			$aTmp[] = $this->O("\n");
			$aTmp[] = '<{@root}>';
			$aTmp[] = $this->O("\n");
			$aTmp[] = '{@data}</{@root}>';
			$sBase = implode('', $aTmp);
			unset($aTmp);
		}
		$aXml = $this->array2xml($aArr);
		$sXml = strtr($sBase,
					array('{@charset}'=>$this->msCharset,
						  '{@root}'=>$this->msRootNode,
						  '{@data}'=>$aXml['val']
						 )
				     );
		unset($aXml);
		return str_replace(">\n\n", ">\n", $sXml);
	}

	/**
	 * 设置XML输出时进行显示格式化<br />
	 *  备注:用于getStrPacket()时，输出更美观，便于阅读，但是会增加传输数据量
	 *  @param boolean $bOpen true:打开显示模式
	 *  @return void
	 *  @access public
	 *  @see IXmlJsonConverArray::setShowFormat()
	 */
	public function setShowFormat($bOpen=true)
	{
		if ($bOpen === true)
			$this->mbOutputFormat = true;
		else
			$this->mbOutputFormat = false;
	}

	/**
	 * XML对象转换为数组<br/>
	 *
     *   注意:所有tag名，会强制被转换为小写
     * 解析的数组规则:
     * 规则分为：枝干，叶子
     * [枝干]:节点称为枝干，枝干构成了XML文档的结构，其表现形式为$key；
     *       即:<boot><t1><t1_1 ts='1'>123</t1_1&gt</t1></boot>
     *       他的枝干为:['t1']['t1_1'],
     *       其叶子数据访问方式: ['t1']['t1_1']['C']
     *       其叶子属性访问方式: ['t1']['t1_1']['A']['ts']
     *   同时，枝干也可能存在同层的同名枝干，如:<boot><t1><t1_1>123</t1_1><t1_1>456</t1_1></t1></boot>
     *       他的枝干为:['t1']['t1_1']=array();是一个数组形式保存，访问时可以为：
     *       content:['t1']['t1_1'][0]['C'] and content:['t1']['t1_1'][1]['C']
     *   对于同层多级的枝干，还有一种情况，就是有可能他的父枝干带attrib的情况，
     *       如:<boot><t1 a='we'><t1_1>123</t1_1><t1_1>456</t1_1></t1></boot>
     *       此时访问t1的a属性的写法为：['t1']['A']['a']，属性与枝干存在于同一个层级
     *       此时访问tl.t1_1的内容写法为:['t1']['t1_1']['C']
     *
     *
     * [叶子]:所有的最终数据都是叶子，叶子分为value与attrib，分别用value:C,attrib:A表示，C/A区分大小写。
     *       例如:[info]=>array('C'=>'Mr.C', 'A'=>array('t1'=>'123', 't2'=>'456'))
     *
     * @access private
	 */
	private function xml2array(SimpleXMLElement $xml,$attributesKey=null,$childrenKey=null,$valueKey=null)
	{
		if($childrenKey && !is_string($childrenKey))
			$childrenKey = '@children';
		if($attributesKey && !is_string($attributesKey))
			$attributesKey = '@attributes';
		if($valueKey && !is_string($valueKey))
			$valueKey = '@values';

		$aRet = array(); //保存用于返回的数组
		$_content = trim((string)$xml);
		if (!empty($_content))
		{	//此处为叶子节点的处理
// 			$name = $xml->getName();
			if($valueKey)
				$aRet[$valueKey] = $_content; //正常情况下不应该跑到这里
			else
				$aRet = array('C'=>$this->decodeCharset($_content));
		}

		$children = array();
		$bFirst = true;
		foreach($xml->children() as $sElementName => $oChild)
		{
			$sElementName = strtolower($sElementName);//强制枝干名称转换为小写
			$aValue = self::xml2array($oChild, $attributesKey, $childrenKey,$valueKey);
			if(isset($children[$sElementName]))
			{
				if(is_array($children[$sElementName]))
				{
					if($bFirst)
					{
						$temp = $children[$sElementName];
						unset($children[$sElementName]);
						$children[$sElementName][] = $temp;
						$bFirst=false;
					}
					$children[$sElementName][] = $aValue;
				}else
					$children[$sElementName] = array($children[$sElementName],$aValue);
			}
			else
				$children[$sElementName] = $aValue;
		}
		if($children)
		{
			if($childrenKey){
				$aRet[$childrenKey] = $children;
			}
			else{$aRet = array_merge($aRet,$children);
			}
		}

		//节点的属性处理
		$attributes = array();
		foreach($xml->attributes() as $sKey=>$sValue)
			$attributes[$sKey] = $this->decodeCharset($sValue);//属性内容编码强制转换

		if($attributes)
		{
			if($attributesKey)
				$aRet[$attributesKey] = $attributes;
			else
			{
				if (empty($aRet))
					$aRet = array('A'=>$attributes);
				elseif (is_array($aRet))
					$aRet = array_merge($aRet, array('A'=>$attributes));
				else //值内容强制转换
					$aRet = array('C'=>$this->decodeCharset($aRet), 'A'=>$attributes);
			}
		}
		return $aRet;
	}

	/**
	 * 返回属性字符串（专用于array2xml()函数内）
	 * @param array() $aArr xml的属性数组
	 * @return string
	 */
	private function getAttribStr($aArr)
	{
		$aBuf = array();
		foreach ($aArr as $sKey => $sVal)
			array_push($aBuf, $sKey, '=\'', $this->encodeCharset($sVal), '\' ');
		return trim(join('', $aBuf));
	}

	/**
	 * 将数组转换成XML
	 * @param array $aArray  保存需要转换成XML的数据数据包
	 * @param int $iLevel 递归的时返回当前层级
	 * @return string
	 */
	private function array2xml(& $aArray, $iLevel=1)
	{
		if (empty($aArray))
			return null;
		else
		{
			if (array_key_exists('C',$aArray))
				return array('val'=>$this->encodeCharset($aArray['C']),
						     'type'=>'c'
						    );//遇到叶子将节点，直接返回其content
		}

		$aBuf = array();
		foreach ($aArray as $sTag => & $aVal)
		{
			if ('A' == $sTag)//不处理这个Tag
				continue;
			elseif (isset($aVal[0]))
			{	//探测到当前层有同名tag节点
				$aLeaf = array();
				foreach ($aVal as & $aNode)
				{
					array_push($aLeaf, $this->O(str_repeat(self::PREFIX, $iLevel)), '<', strtolower($sTag)); //构建tag头
					//如果存在属性则构建属性
					if (array_key_exists('A', $aNode))
						$aLeaf[] = ' '. $this->getAttribStr($aNode['A']);

					$aSubNode = $this->array2xml($aNode, $iLevel+1);
					if (empty($aSubNode))
						array_push($aLeaf, '/>', $this->O("\n"));//空节点
					else
					{
						$aLeaf[] = '>';
						if ($aSubNode['type'] == 'c')
							$aLeaf[] = $aSubNode['val'];
						else
						{
							array_push($aLeaf, $this->O("\n"), $aSubNode['val'], $this->O("\n"));//关闭节点
							$aLeaf[] = $this->O(str_repeat(self::PREFIX, $iLevel));
						}
						array_push($aLeaf, '</', strtolower($sTag), '>', $this->O("\n"));//关闭节点
					}
					unset($sSubNode);
				}
				$aBuf[] = implode('', $aLeaf);
				unset($aLeaf);
			}
			else
			{	//处理同层兄弟节点
				$aLeaf = array();
				array_push($aLeaf, $this->O(str_repeat(self::PREFIX, $iLevel)), '<', strtolower($sTag)); //构建tag头
				//如果存在属性则构建属性
				if (array_key_exists('A', $aVal))
					$aLeaf[] = ' '. $this->getAttribStr($aVal['A']);

				$aSubNode = $this->array2xml($aVal, $iLevel+1); //递归
				if (empty($aSubNode))
					array_push($aLeaf, '/>', $this->O("\n"));//空节点
				else
				{
					$aLeaf[] = '>';
					if ($aSubNode['type'] == 'c')
						$aLeaf[] = $aSubNode['val'];
					else
					{
						array_push($aLeaf, $this->O("\n"), $aSubNode['val']);//关闭节点
						$aLeaf[] = $this->O(str_repeat(self::PREFIX, $iLevel));
					}
					array_push($aLeaf, '</', strtolower($sTag), '>', $this->O("\n"));//关闭节点
				}
				unset($sSubNode);

				$aBuf[] = implode('', $aLeaf);
				unset($aLeaf);
			}
		}
		if (count($aBuf) == 0)
			return null; //节点中没有内容
		else
			return array('val'=>implode('', $aBuf), 'type'=>'a');
	}
}
?>