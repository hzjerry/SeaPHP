<?php
/**
 * 数据库模块的配置文件
 *
 * @var array
 * */
return array
(
'write_log'		=> true, //是否记录SQL执行日志[只有在进行性能检查时才打开，平时请不要开启这个选项](log.core.extend.CDB)
'show_error'	=> true, //遇到错误时打印错误信息
'select_default'=> 'rw', //SELECT的默认操作库[rw:操作读写主库|r:操作只读从库]
'charset'		=> 'utf8', //数据库使用的本地字符集(注意:是utf8不是utf-8)
'table_object_path'	=> 'workgroup.db.table_object', //数据库表对象
);
?>