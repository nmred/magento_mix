<?php
define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); 

$configFile = 'application';

// 判断开发线上环境
$configFile .= '.ini';

// 执行入口文件
try {
	$app  = new Yaf_Application(APP_PATH . "/conf/$configFile");
	$app->bootstrap()
		->run();
} catch (\Exception $e) {
	var_dump($e);
	$data = array(
		'status' => 1000,
		'data'   => array(),
		'msg'    => 'Bad Request',		
	);
	header('Content-Type:application/json; charset=utf-8');
	echo json_encode($data);
}
