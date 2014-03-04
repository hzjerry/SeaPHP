<?php
/**
 * Protocol view 的授权访问验证帐号配置<br />
 *  配置的内容是一个二维数组，第二维是帐号信息。请为需要访问接口协议页面的开发人员在此处配置帐号。<br/>
 *  默认访客不需要验证，默认为public权限，只能查看访问权限为public的API接口<br />
 *  其中: purview参数包含3种权限关键字[public:公共访问者权限 | protected:内部开发者权限 | private:私有接口权限(最高权限)]
 *
 * @var array
 * @example
 *  array('uname'=>'登录帐号', 'pwd'=>'123456', 'purview'=>'protected'),
 * */
return array
(
array('uname'=>'develop',	'pwd'=>'123456',	'purview'=>'protected'),
array('uname'=>'admin',		'pwd'=>'123456',	'purview'=>'private'),
);
?>