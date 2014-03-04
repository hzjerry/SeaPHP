<?php
/**
 * WebService的API接口服务协议展示接口<br/>
 *  备注:每个API接口类必须要继承这个接口，并实现里面所有方法，这样在查看接口协议的介绍页面时才能出现相应的信息。
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130422
 * @package SPFW.extend.webservice.lib
 */
interface IProtocolView
{
	/**
	 * 返回当前API接口类的介绍
	 * @return string
	 * @access public
	 */
	public function getClassExplain();

	/**
	 * 返回当前API类的使用介绍
	 * @return string
	 * @access public
	 */
	public function getUseExplain();

	/**
	 * 返回接口公开程度<br/>
	 *   备注：返回的访问权限内容为3个级别<br/>[public:公共权限] | [protected:内部开发者权限] | [private:私有保密接口]<br/>
	 *   与之对因的访问权限为[public:无需帐号密码] | [protected:帐号develop + 密码] | [private:帐号admin + 密码]
	 * @return string
	 * @access public
	 */
	public function getAccess();

	/**
	 * 返回入口协议<br/>
	 *   备注：返回为Xml结构数组<br/>
	 *   变量长度定义建议值: [min:最小(值|长度)] | [max:最大(值|长度)] | [fixed:固定长度] | [val:起始值~N]
	 * @return array
	 * @access public
	 */
	public function getInProtocol();

	/**
	 * 返回出口协议<br/>
	 *   备注：返回为Xml结构数组<br/>
	 *   变量长度定义建议值: [max:最大长度] | [fixed:固定长度] | [min:最小长度]
	 * @return array
	 * @access public
	 */
	public function getOutProtocol();

	/**
	 * 返回更新日志<br/>
	 *   备注：返回数组结构(二维数组)：<br/>
	 *   array(array('date'=>'YYYY-MM-DD', 'author'=>'修改人', 'memo'=>'修改内容'), ...)
	 * @return array
	 * @access public
	 * @example
	 * <pre>
	 * public function getUpdaueLog()
	 * {
	 *     return array(array('date'=>'2013-06-02', 'author'=>'jerryli', 'memo'=>'接口创建'),
	 *                  array('date'=>'2013-07-11', 'author'=>'jerryli', 'memo'=>'增加了访问权限的控制'));
	 * }
	 * </pre>
	 */
	public function getUpdaueLog();
}

?>