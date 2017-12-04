<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//各种安装操作？
$sql = "show tables";
runquery($sql);

$finish = TRUE;

?>