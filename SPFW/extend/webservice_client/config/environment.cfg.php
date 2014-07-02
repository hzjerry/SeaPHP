<?php
/**
 * WebService Client框架的配置文件
 *
 * @var array
 * */
return array
(
'logic_workgroup'	=> 'workgroup.webservice_client', //api 接口客户端逻辑文件根包位置
'time_calibrate'	=> false, //与服务器自动校准时间（不建议使用，将影响通信性能，除非无法调整本地的时间外）
);
?>