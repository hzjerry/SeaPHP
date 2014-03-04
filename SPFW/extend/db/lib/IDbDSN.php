<?php
/**
 * 数据库连接配置接口<br/>
 * 返回：数据库链接配置必须继承这个接口
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130501
 * @package SPFW.extend.db.lib
 */
interface IDbDSN
{
	/**
	 * 获取主数据库连接配置(读写库配置)<br />
	 * 返回: 配置信息为一维数组格式为:<br />
	 * array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>'3306')
	 * @return array
	 */
	public function getRW();

	/**
	 * 获取分布式只读数据库连接配置(只读库配置)<br />
	 * <strong>注意</strong>: 如果不存在只读库请这样设置 return null;<br />
	 * 返回: 配置信息为二维数组格式为:<br />
	 * array(<br />
	 * &nbsp;array('host'=>'', 'username'=>'', 'pwd'=>'', 'dbname'=>'', 'port'=>3306), <br />
	 * ...)
	 * @return array | null
	 */
	public function getR();

	/**
	 * 数据库的环境变量配置<br />
	 * 返回: 配置信息为一维数组格式为：<br />
	 * array(<br />'prefix'=>'表前缀', <br />
	 *  'table_upper_lower'=>'强制大小写[upper:强制大写 | lower:强制小写 | intact:保持原样]',<br />
	 *  'db_driver'=>'数据库驱动[mysqli | mysql | sqlite]'<br />
	 *  )
	 */
	public function getEnvironment();
}

?>