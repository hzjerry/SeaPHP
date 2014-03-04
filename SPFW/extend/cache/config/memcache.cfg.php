<?php
/**
 * Memcache服务连接配置
 * 当主Memcache连接不上时，自动连接备用Memcache
 *
 * @var array
 * */
return array
(
	array/*主Memcache连接信息*/
	(
		'host'		=> 'localhost', //memcache服务机地址
		'port'		=> 11211, //端口号
		'timeout'	=> 5 //超时秒数
	),
	array/*备用Memcache连接信息*/
	(
		'host'		=> 'localhost', //memcache服务机地址
		'port'		=> 11211, //端口号
		'timeout'	=> 5 //超时秒数
	),
);
?>