<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/core');

if($_POST){
	if(is_array($_POST) && !empty($_POST['username']) && !empty($_POST['age']) && !empty($_POST['job']) && !empty($_POST['modname']) && !empty($_POST['phonenum']) && !empty($_POST['myqq']) && !empty($_POST['special']) && !empty($_POST['reason']) && !empty($_POST['measure']) && $_POST['formhash'] == formhash()){
		$query_validate = DB::query("SELECT * FROM ".DB::table('forum_koueiapply')." where app_username='".$_POST['username']."'");
		$row = DB::num_rows($query_validate);
		if($row == 0){
			$app_username = $_POST['username'];
			if($_POST['sex'] == 'man'){
				$app_sex = 1;
			}else if($_POST['sex'] == 'woman'){
				$app_sex = 0;
			}else{
				$app_sex = 1;
			}
			$app_age = $_POST['age'];
			$app_job = $_POST['job'];
			$app_modname = $_POST['modname'];
			$app_phonenum = $_POST['phonenum'];
			$app_myqq = $_POST['myqq'];
			$app_special = $_POST['special'];
			$app_reason = $_POST['reason'];
			$app_measure = $_POST['measure'];
			$applytime = TIMESTAMP;
			$query = DB::query("INSERT INTO ".DB::table('forum_koueiapply')."(app_username,app_sex,app_age,app_job,app_mouldname,app_phonenum,app_myqq,app_special,app_reason,app_measure,applytime) values('".$app_username."','".$app_sex."','".$app_age."','".$app_job."','".$app_modname."','".$app_phonenum."','".$app_myqq."','".$app_special."','".$app_reason."','".$app_measure."','".$applytime."')");

			if($query){
				showmessage('kouei_apply_success','forum.php',array(),array('refreshtime' => '3'));
			}else{
				showmessage('kouei_apply_failed','forum.php',array(),array('refreshtime' => '3'));
			}
		}else{
			showmessage('kouei_apply_error','forum.php',array(),array('refreshtime' => '3'));
		}
	}else{
		showmessage('kouei_apply_failed');
	}
}else{
	showmessage('kouei_apply_refuse','forum.php',array());
}

include template('forum/kouei_apply');
?>