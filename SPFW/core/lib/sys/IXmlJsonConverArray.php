<?php
/**
 * Xml与Json转换成Php数组的双向映射处理（Xml数组结构为基础）
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130415
 * @package SPFW.core.lib.sys
 */
interface IXmlJsonConverArray
{
	/**
	 * 构造函数
	 * @param string $sRoot('boot') 根节点名称
	 * @param string $sCharset 协议使用的字符集
	 */
	function __construct($sRoot = 'boot', $sCharset='utf-8');
	/**
	 * 根据传入的字符串包得到XML结构数组
	 * @param string $sData 字符串数据包
	 * @return array | null
	 */
	public function getArray($sData);

	/**
	 * 根据传入的XML结构数组得到对应协议的字符串
	 * @param array $aArr XML结构数组
	 * @return string
	 */
	public function getStrPacket($aArr);

	/**
	 * 设置对应协议数据包输出时进行显示格式化<br />
	 *  备注:用于getStrPacket()时，输出更美观，便于阅读，但是会增加传输数据量
	 *  @param boolean $bOpen true:打开显示模式
	 *  @return void
	 *  @access public
	 */
	public function setShowFormat($bOpen=true);
}

?>