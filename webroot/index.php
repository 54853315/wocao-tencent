<?php
header("Content-type:text/html;charset=utf-8");
//打开session
session_start();
define('THINK_PATH', '../ThinkPHP/');
define('APP_NAME', 'WoCao');
define('APP_PATH', './WoCao/');
define('APP_DEBUG', true);//开发环境开启调试模式

require THINK_PATH.'ThinkPHP.php';