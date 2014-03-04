<?php
/**
 * Xml结构数组节点管理类<br/>
 * 备注:严格遵照XML 1.0协议标准，所有属性的名称将强制转换为小写
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130416
 * @package SPFW.core.lib.base
 * @example
 * */
class CXmlArrayNode
{
	/**
	 * 将数组的Key转换为小写
	 * @param array $aVal 需要转换的数组
	 * @return array
	 */
	static private function key2Lower(& $aVal)
	{
		$aTemp = array();
		foreach ($aVal as $strKey=>$strValue)
			$aTemp[strtolower($strKey)] = strval($strValue);
		return $aTemp;
	}

	/**
	 * 创建一个记录条的内容数组
	 *
	 * @param string $sContent 节点内容
	 * @param array $aAttrub 节点的属性（必须是一维数组）
	 * @example createNode();//创建一个空节点
	 * 			createNode('0000');//只添加内容
	 *			createNode('0000', array('pro'=>'123',...));//添加内容与属性
	 *			createNode(null, array('pro'=>'123',...));//只添加属性
	 * @return array
	 * @access public
	 * @static
	 */
	static public function createNode($sContent=null, $aAttrub=null)
	{
		if (empty($sContent) && empty($aAttrub))
			return array('C'=>'');

		$aRet = array();
		//判断是否要加入内容
		if (!is_null($sContent))
			$aRet['C'] = strval($sContent);

		//判断是否要加入属性
		if (!empty($aAttrub))
			$aRet['A'] = self::key2Lower($aAttrub);

		return $aRet;
	}

	/**
	 * 创建同名节点（节点只含属性arrtib，不含值content）
	 * @param array $aAttr 属性数组 二维数组:array(array('field1'=>'...', 'field2'=>'...'), ...)
	 * @return array
	 * @access public
	 * @static
	 */
	static public function createSameTagAttrib(& $aAttr)
	{
		$aTemp = array();
		if (count($aAttr) > 1)
		{
			foreach ($aAttr as $aVal)
				$aTemp[]['A'] = self::key2Lower($aVal);
		}
		elseif (count($aAttr) == 1)
			$aTemp['A'] = self::key2Lower($aAttr[0]);

		return $aTemp;
	}

	/**
	 * 判断当前节点是否为同名节点
	 * @param array $aXml 待检查的XML数组节点
	 * @return boolean
	 * @access public
	 * @static
	 */
	static public function isSameTagNode(& $aXml)
	{
		if (isset($aXml[0]))
			return true; //存在同名节点
		else
			return false;//不存在同名节点
	}

	/**
	 * 获取同名兄弟节点类型的属性<br/>
	 *  备注:此函数会丢弃多值节点中的content<br/>
	 *  返回格式: 二维数组:array(array('field1'=>'...', 'field2'=>'...'), ...)
	 * @param array $aXml 待检查的XML数组节点
	 * @return array
	 * @access public
	 * @static
	 */
	static public function getSameTagNode(& $aXml)
	{   //但断是否为多值节点
		$aRet = array();
		if (self::isSameTagNode($aXml))
		{
			foreach ($aXml as $aVal)
				$aRet[] = $aVal['A'];
		}
		else
			$aRet[] = $aXml['A'];

		return $aRet;
	}
}

?>