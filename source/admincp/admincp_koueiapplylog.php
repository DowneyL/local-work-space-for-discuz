<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$limit = 10;
$page = $_GET['page'] ? intval($_GET['page']) : 1;
$start = intval(($page-1)*$limit);

$query = DB::query("SELECT * FROM ".DB::table('forum_koueiapply_log')." order by lid desc limit ".$start.",".$limit);
 if($query){
    $statvars = array();
    while($row = DB::fetch($query)){
      $row['datetime'] = date('Y-m-d H:i:s',$row['datetime']);
      array_push($statvars,$row);
    }
}
include template('forum/koueiapply_log');
