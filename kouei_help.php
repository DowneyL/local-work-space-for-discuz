<?php
	define('CURSCRIPT','kouei_hlep');
	require './source/class/class_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init();
	$navtitle = 'ȷ������';
	$metadescription = '���Ƿ�ȷ�����������ѵõ��������ִ�б��β�����';

	$yes = '��';
	$no = '��';
	include template('forum/kouei_hlep');
?>