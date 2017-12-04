<?php
	define('CURSCRIPT','kouei_hlep');
	require './source/class/class_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init();
	$navtitle = '确认提醒';
	$metadescription = '您是否确定求助问题已得到解决，并执行本次操作？';

	$yes = '是';
	$no = '否';
	include template('forum/kouei_hlep');
?>