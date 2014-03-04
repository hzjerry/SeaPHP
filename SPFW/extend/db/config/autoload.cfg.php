<?php
/**
 * [数据库模块]自动加载类库配置区
 *  注意：所有自动加载类必须在框架SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 *  @var array()
 * */
return array
(
'CDbDriver'=> //数据库驱动接口
	array('package'=>'extend.db.lib',	'file'=>'CDbDriver.class.php'),
'CDbDriverMySqli'=> //mysqli数据库层驱动
	array('package'=>'extend.db.lib',	'file'=>'CDbDriverMySqli.class.php'),
'CDbDriverMySql'=> //mysql数据库层驱动
	array('package'=>'extend.db.lib',	'file'=>'CDbDriverMySql.class.php'),
'CDbCURD'=> //数据库操作层（CURD创建更新读取删除）
	array('package'=>'extend.db.lib',	'file'=>'CDbCURD.class.php'),
'CDbTable'=> //数据库表对象操作基类
	array('package'=>'extend.db.lib',	'file'=>'CDbTable.class.php'),
'IDbDSN'=> //数据库连接配置接口
	array('package'=>'extend.db.lib',	'file'=>'IDbDSN.php'),
);
?>