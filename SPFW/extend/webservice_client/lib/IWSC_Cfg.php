<?php
/**
 * WebService Client的API接口连接配置<br/>
 *  备注:每个API连接配置文件都必须实现这个接口
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140702
 * @package SPFW.extend.webservice_client.lib
 */
interface IWSC_Cfg {
	/**
	 * 返回当前API接口配置的连接地址
	 * @return array XML结构数组
	 * @access public
	 */
	public function getUrl();
	/**
	 * 返回当前API接口的配置公钥列表
	 * @return array(array('包名路径'=>'包的公钥'), ...)<br/>
	 * 【注意】当存在多个公钥时，数组的列表顺序需按照 包名路径 长度的升序排序存储。<br/>
	 * 【例如】array('abc'=>'key...', 'abc.def'=>'key...')
	 * @access public
	 */
	public function getPublicKey();
}

?>