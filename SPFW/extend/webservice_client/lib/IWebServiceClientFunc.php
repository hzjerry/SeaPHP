<?php
/**
 * WebService Client的API接口业务实现逻辑接口<br/>
 *  备注:每个API接口业务逻辑必须实现这个接口
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140702
 * @package SPFW.extend.webservice_client.lib
 */
interface IWebServiceClientFunc {
	/**
	 * 入口参数初始化<br/>
	 * 对传入的数组按照xml结构数组规范化后送出
	 * @param array $aParam 调用函数传入的数组
	 * @return array xml结构数组
	 * @access public
	 */
	public function inParamInit($aParam);
	/**
	 * 返回值输出前的规范化处理<br/>
	 * 当接口正常返回时在此函数中将xml结构数组处理转换成业务层规范的数组结构
	 * @param array $aParam 接口返回值的xml结构数组
	 * @return array xml结构数组
	 * @access public
	 */
	public function retParam($aResultParam);
}
?>