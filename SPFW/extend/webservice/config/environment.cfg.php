<?php
/**
 * WebService框架的配置文件
 *
 * @var array
 * */
return array
(
'write_log'			=> true, //是否记录日志文件
'write_log_array'	=> false, //是否记录数组日志，受write_log值的影响(将xml|json数据转换成Xml结构数组记录在日志中)(注:会产生大量冗余数据，非调试状态不要开启)
'format_result_data'=> false, //是否送回格式化的字符串数据包(送出的数据更美观，但会增加传输数据量，建议正式上线后设为false)
'do_checksum'		=> true, //是否做checksum校验检查
'xml_charset'		=> 'utf-8', //系统字符集
'xml_root_node'		=> 'boot', //xml根节点名称(非必须时请不要修改这个值)
'result_note_name'	=> 'result', //状态值节点名称
'log_path'			=> 'log.extend.webservice', //日志文件保存包路径
'logic_api_service'	=> 'workgroup.webservice.package', //api逻辑服务文件所在根包位置
/*protocol view部分的配置信息*/
'protocol_project_name'	=> 'Sea PHP', //protocol view协议显示页面中的版本信息（可根据需要修改）
'web_service_url'	=> 'WebService.php', //webservice的入口地址（以当前网站项目根为起始位置,即:getWEB_ROOT()为前缀）
);
?>