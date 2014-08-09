<?php
/**
 * Website Engine框架的配置文件
 *
 * @var array
 * */
return array
(
	//微信服务的业务逻辑类目录
	'logic_workgroup'	=> 'workgroup.weixin_service.logic',
	//是否记录输入输出的日志
	'write_log'			=> true,
	//微信绑定是的授权验证Token(也可以自己改写bindVerify函数，不用这个参数)
	'verify_token'		=> 'f51af5514fb574957a83bb3314047050',
);
?>