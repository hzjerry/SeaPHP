<?php
/**
 * [Mail]自动加载类库配置区
 *  注意：所有自动加载类必须在框架SPFW目录内，package是相对包路径，file是文件名
 *  将会保存在$GLOBALS['SEA_PHP_FW_AUTOLOAD']中，由runtime.php加载
 *  @var array()
 * */
return array
(
'PHPMailer'=> //PHPMailer
	array('package'=>'extend.mail.lib',	'file'=>'class.phpmailer.php'),
'SMTP'=> //SMTP PHPMailer的依赖类
	array('package'=>'extend.mail.lib',	'file'=>'class.smtp.php'),
'html2text'=> //Converts HTML to formatted plain text
	array('package'=>'extend.mail.lib',	'file'=>'class.html2text.php'),
);
?>