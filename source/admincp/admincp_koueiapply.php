<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$sql = <<<EOF

create table if not exists pre_forum_koueiapply (
  `applyid` int(8) not null auto_increment,
  `app_username` varchar(40) not null,
  `app_sex` tinyint(1) not null,
  `app_age` smallint(2) not null,
  `app_job` varchar(50) not null,
  `app_mouldname` varchar(50) not null,
  `app_phonenum` varchar(20) not null,
  `app_myqq` varchar(20) not null,
  `app_special` mediumtext not null,
  `app_reason` mediumtext not null,
  `app_measure` mediumtext not null,
  `app_validate` tinyint(3) not null,
  `applytime` int(10) not null,
  `verifytime` int(10) not null,
  primary key (`applyid`)
) engine=myisam default charset=gbk;

create table if not exists pre_forum_koueiapply_log (
  `lid` int(8) not null auto_increment,
  `aid` int(8) not null,
  `acname` varchar(30) not null,
  `action` varchar(20) not null,
  `reason` mediumtext not null,
  `datetime` int(10) not null,
  primary key (`lid`)
) engine=myisam default charset=gbk;

EOF;


$limit = 20;
$page = $_GET['page'] ? intval($_GET['page']) : 1;
$start = intval(($page-1)*$limit);

function getRowsAndPages($app_validate,$limit){
$query_all = DB::query("SELECT * FROM ".DB::table('forum_koueiapply')." where app_validate=".$app_validate );
$num_rows = DB::num_rows($query_all);
$page_all = ceil($num_rows/$limit);
$rowp = array();
$rowp['num_rows'] = $num_rows;
$rowp['page_all'] = $page_all;
return $rowp;
}

function getKoueiStatvars($app_validate,$start,$limit){


  $query = DB::query("SELECT * FROM ".DB::table('forum_koueiapply')." where app_validate=".$app_validate." order by applytime desc limit ".$start.",".$limit);
  if($query){
    $statvars = array();
    while($row = DB::fetch($query)){
      $row['applytime'] = date('Y-m-d H:i:s',$row['applytime']);
      if($row['verifytime']){
        $row['verifytime'] = date('Y-m-d H:i:s',$row['verifytime']);
      }
      array_push($statvars,$row);
    }
  } 
  return $statvars;
}


if($_GET['koueimod'] && $_GET['koueimod'] == 'validate'){
  $statvars = getKoueiStatvars(1,$start,$limit);
  $rowp = getRowsAndPages(1,$limit);
}else if($_GET['koueimod'] && $_GET['koueimod'] == 'apprefuse'){
  $statvars = getKoueiStatvars(3,$start,$limit);
  $rowp = getRowsAndPages(3,$limit);
}else{
  $statvars = getKoueiStatvars(0,$start,$limit);
  $rowp = getRowsAndPages(0,$limit);
}

include template('forum/koueiapply');
?>