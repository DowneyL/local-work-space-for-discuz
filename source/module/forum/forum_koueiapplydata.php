<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/core');

if($_POST){
	if(is_array($_POST) && !empty($_POST['reason']) && !empty($_POST['acname']) && !empty($_POST['acname']) && !empty($_POST['applyid']) && !empty($_POST['action'])){
			$reason=$_POST['reason'];
			$acname=$_POST['acname'];
			$applyid=$_POST['applyid'];
			$action=$_POST['action'];

			function uploadKoueiStatvars($applyid,$validate,$action,$acname,$reason){
			  $verifytime = TIMESTAMP;
			  $query_update = DB::query("UPDATE ".DB::table('forum_koueiapply')." set app_validate=".$validate.", verifytime='".$verifytime."' where applyid=".$applyid);
			  $query_insert = DB::query("INSERT INTO ".DB::table('forum_koueiapply_log')."(aid,action,acname,reason,datetime) values('".$applyid."','".$action."','".$acname."','".$reason."','".$verifytime."')");
			  if($query_update && $query_insert){
				  return true;
			  }else{
			  	return false;
			  }
			}

			if($_POST['applyid'] && $_POST['action'] && $_POST['action'] == 'pass'){
			  $applyid = intval($_POST['applyid']);
			  $flag = uploadKoueiStatvars($applyid,1,$action,$acname,$reason);
			  if($flag){
			    echo "<script>alert('Successful');location.href = 'admin.php?action=koueiapply';</script>";
			  }else{
			    echo "<script>alert('Error');location.href = 'admin.php?action=koueiapply';</script>";
			  }
			}

			if($_POST['applyid'] && $_POST['action'] && $_POST['action'] == 'refuse'){
			  $applyid = intval($_POST['applyid']);
			  $flag = uploadKoueiStatvars($applyid,3,$action,$acname,$reason);
			  if($flag){
			    echo "<script>alert('Successful');location.href = 'admin.php?action=koueiapply';</script>";
			  }else{
			    echo "<script>alert('Error');location.href = 'admin.php?action=koueiapply';</script>";
			  }
			}

			if($_POST['applyid'] && $_POST['action'] && $_POST['action'] == 'recover'){
			  $applyid = intval($_POST['applyid']);
			  $flag = uploadKoueiStatvars($applyid,0,$action,$acname,$reason);
			  if($flag){
			    echo "<script>alert('Successful');location.href = 'admin.php?action=koueiapply';</script>";
			  }else{
			    echo "<script>alert('Error');location.href = 'admin.php?action=koueiapply';</script>";
			  }
			}
		}else{
			echo "<script>alert('Error');location.href = 'admin.php?action=koueiapply';</script>";
		}
	}else{
	echo "<script>alert('Warning!Warning!'); location.href = 'forum.php';</script>";
	}

