<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['action']=='full'){
	$fid = intval($_GET['fid']);
	$sid = intval($_GET['sid']);
	loadcache('forums');
	$bsorts =  $_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts'];
	$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where classid in(".$bsorts.")");
	while($row = DB::fetch($query)){
		$sortid = intval($_POST['sortid'.$row['classid']]);
		if($sortid>0){
			foreach($_GET['moderate'] as $value){
				$tid = intval($value);
				if($row['classoption']==1){    //多选处理
					$count = DB::result_first("SELECT count(*) FROM ".DB::table('xj_sort_type')." WHERE classid=".$sortid." AND tid=".$tid);
					if($count==0){
						DB::query("insert into ".DB::table('xj_sort_type')."(tid,classid,parentid) values(".$tid.",".$sortid.",".$row['classid'].")");
					}
				}elseif($row['classoption']==0){
					$count = DB::result_first("SELECT count(*) FROM ".DB::table('xj_sort_type')." WHERE parentid=".$row['classid']." AND tid=".$tid);
					if($count>0){
						DB::query("UPDATE ".DB::table('xj_sort_type')." SET classid=".$sortid." WHERE parentid=".$row['classid']." AND tid=".$tid);
					}else{
						DB::query("insert into ".DB::table('xj_sort_type')."(tid,classid,parentid) values(".$tid.",".$sortid.",".$row['classid'].")");
					}
				}
			}
		}elseif($sortid==-1){
			$tids = implode(',',$_GET['moderate']);
			foreach($tids as $key => $value){
				$tids[$key] = intval($value);
			}
			DB::query("DELETE FROM ".DB::table('xj_sort_type')." WHERE parentid=".$row['classid']." AND tid IN(".$tids.")");
		}
	}


	showmessage(lang('plugin/xj_sort', 'xgdjflcg'), 'forum.php?mod=forumdisplay&fid='.$fid.'&sid='.$sid, array(), array('showdialog' => true, 'locationtime' => true));
}else{
	$fid = intval($_GET['fid']);
	$sid = intval($_GET['sid']);
	loadcache('forums');
	$bsorts =  $_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts'];


	$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where classid in(".$bsorts.") ORDER BY classorder");
	$sort = array();
	$item = array();
	while($row = DB::fetch($query)){
		$item['classid'] = $row['classid'];
		$item['classname'] = $row['classname'];
		$item['subclass'] = array();
		$sortquery = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where parent=".$row['classid']." ORDER BY classorder");
		while($sortrow = DB::fetch($sortquery)){
			if(in_array($sortrow['classid'],$mysort)){
				$sortrow['select'] = 'yes';
			}else{
				$sortrow['select'] = 'no';
			}
			$item['subclass'][] = $sortrow;
		}
		$sort[] = $item;
	}
	$selectcount = count($_GET['moderate']);
	$moderate = $_GET['moderate'];
}
include template('xj_sort:batch_sort_form');
?>