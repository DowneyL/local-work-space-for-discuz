<?php

/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: misc_stat.php 31889 2012-10-22 03:27:56Z liulanbo $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('CACHE_TIME', 18000);

$op = $_GET['op'];
if(!in_array($op, array('basic', 'trade', 'team', 'trend', 'modworks', 'memberlist', 'forumstat', 'trend', 'salary','actmem'))) {
	$op = 'basic';
}
if(!$_G['group']['allowstatdata'] && $op != 'trend') {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$navtitle = lang('core', 'title_stats_'.$op).' - '.lang('core', 'title_stats');

loadcache('statvars');

if($op == 'basic') {
	$statvars = getstatvars('basic');
	extract($statvars);
	include template('forum/stat_main');
}

//debug 应助分记录统计
elseif($op == 'salary') {
	require_once libfile('function/core');
	// $statvars = getstatvars('salary');
	$page = $_GET['page']?$_GET['page']:1;
	$pagelist = 10;
	$startnum = ($page-1)*$pagelist;
	$total = count(DB::fetch_all("SELECT * FROM ".DB::table('forum_ratelog')." where extcredits = 8"));

	$page_count_num = ceil($total / $pagelist);

	$pagenumarr = array();
	for($i=0; $i<$page_count_num; $i++){
		$pagenumarr[$i] = $i+1;
	}

	if($page-1 >= 0){
		$prevpage = $page-1;
	}else{
		$prevpage = 1;
	}

	if($page+1 <= $page_count_num){
		$nextpage = $page+1;
	}else{
		$nextpage = $page_count_num;
	}

	if(!$_GET['getuid']){
		$hcredit_rows = DB::fetch_all("SELECT * FROM ".DB::table('forum_ratelog')." where extcredits = 8 order by dateline desc limit $startnum,$pagelist");
		$hcredit_query = DB::fetch_all("SELECT * FROM ".DB::table('common_credit_log')." where operation = 'PRC' order by dateline desc");
		$count = count($hcredit_query);
		$change_space = array();
		for ($i=0; $i<$count; $i++){
			if ($hcredit_query[$i]['extcredits8'] != 0){
				array_push($change_space,$hcredit_query[$i]);
			}
		}
		$hcredit_query = $change_space;
		for ($i=0; $i<$count; $i++){
			if($hcredit_rows[$i]){
				$hcredit_rows[$i]['dateline'] = date('Y-m-d  H:i:s',$hcredit_rows[$i]['dateline']);
				$hcredit_rows[$i]['getusername'] = 0;
				$hcredit_rows[$i]['getuid'] = 0; 
				$hcredit_rows[$i]['getuid'] = $hcredit_query[$i+$startnum]['uid'];
	    		$userinfo = getuserbyuid($hcredit_query[$i+$startnum]['uid']);
				$hcredit_rows[$i]['getusername'] = $userinfo['username'];
			}
		}

		$statvars = $hcredit_rows;
	}else{
		$getuid = $_GET['getuid'];
		$hcredit_rows = DB::fetch_all("SELECT * FROM ".DB::table('forum_ratelog')." where extcredits = 8 order by dateline desc");	
		$hcredit_query = DB::fetch_all("SELECT * FROM ".DB::table('common_credit_log')." where operation = 'PRC' order by dateline desc");
		$count = count($hcredit_query);
		$change_space = array();
		for ($i=0; $i<$count; $i++){
			if ($hcredit_query[$i]['extcredits8'] != 0){
				array_push($change_space,$hcredit_query[$i]);
			}
		}

		$hcredit_query = $change_space;
		for ($i=0; $i<$count; $i++){
			if($hcredit_rows[$i]){
				$hcredit_rows[$i]['dateline'] = date('Y-m-d  H:i:s',$hcredit_rows[$i]['dateline']);
				$hcredit_rows[$i]['getusername'] = 0;
				$hcredit_rows[$i]['getuid'] = 0; 
				$hcredit_rows[$i]['getuid'] = $hcredit_query[$i+$startnum]['uid'];
	    		$userinfo = getuserbyuid($hcredit_query[$i+$startnum]['uid']);
				$hcredit_rows[$i]['getusername'] = $userinfo['username'];
			}
		}

		$cou = count($hcredit_rows);
		$change_space_2 = array();
		for ($i=0; $i<$count; $i++){
			if ($hcredit_rows[$i]['getuid'] == $getuid){
				array_push($change_space_2,$hcredit_rows[$i]);
			}
		}
		$hcredit_rows = $change_space_2;
		$statvars = $hcredit_rows;
	}

	$multi = multi($total, $pagelist, $page, 'misc.php?mod=stat&op=salary', $maxpages = 0, $page = 10);

	include template('forum/stat_salary');
}


//debug  活跃人数统计
elseif($op == 'actmem') {

	//分页
	$page = $_GET['page']?$_GET['page']:1;
	$pagelist = 10;
	$startnum = ($page-1)*$pagelist;
	$finishnum = $pagelist;
	$count_last_uids  = 0;
	//设置开始计算和结束计算的时间



	if($_GET['starttime'] && $_GET['finishtime']){

	$starttime_1 = $_GET['starttime'];
	$finishtime_1 = $_GET['finishtime'];
	$starttime = strtotime($starttime_1);
	$finishtime = strtotime($finishtime_1);


	//得到发帖人数的不重复总值以及对应的id数组
	$tid_query = DB::fetch_all("SELECT tid,authorid FROM ".DB::table('forum_thread')." where dateline>=".$starttime." and dateline <=".$finishtime." order by dateline");
	$tids = count($tid_query);

	$hmember_array = array();
	$hmember_uid = array();
	for($i=0; $i<$tids; $i++ ){
		// $hmember_query = DB::fetch_all("SELECT * FROM ".DB::table('forum_threadpartake')." where tid=".$tid_query[$i]['tid']." order by tid");
		// $flag = count($hmember_query);
		// if($flag != 0){
		// 	array_push($hmember_array,$hmember_query);
		// }
		array_push($hmember_uid,$tid_query[$i]['authorid']);
	}
	$forum_uids = array_unique($hmember_uid);
	$count_forum_uids = count($forum_uids);	

	//得到参与回复人数的不重复总值以及对应id数组；
	$reply_query = DB::fetch_all("SELECT uid FROM ".DB::table('forum_threadpartake')." where dateline>=".$starttime." and dateline <=".$finishtime." order by dateline");

	$replys = count($reply_query);
	$reply_uid = array();
	for($j=0; $j<$replys; $j++){
		array_push($reply_uid,$reply_query[$j]['uid']);
	}
	$reply_uids = array_unique($reply_uid);
	$count_reply_uids = count($reply_uids);

	for($i=0; $i<count($hmember_uid); $i++){
	    array_push($reply_uid,$hmember_uid[$i]);
	}

	//得出结果!!
	$last_uids = array_unique($reply_uid);
	$count_last_uids = count($last_uids);
	$get_uids = array_values($last_uids);

	$statvars = array();

	for($i=0; $i<$count_last_uids; $i++){
		$uid = $get_uids[$i];
		$query = DB::fetch_all("SELECT uid,username,regdate FROM ".DB::table('common_member')." where uid=".$uid);
		array_push($statvars,$query[0]);
	}

	//遍历具象化时间
	for($i=0; $i<count($statvars); $i++){
		$statvars[$i]['regdate'] = date('Y-m-d H:i:s',$statvars[$i]['regdate']);
	}

	//分页	
	$total = $count_last_uids;
	$page_count_num = ceil($total / $pagelist);

	$pagenumarr = array();
	for($i=0; $i<$page_count_num; $i++){
		$pagenumarr[$i] = $i+1;
	}

	if($page - 1 >= 0){
		$prevpage = $page-1;
	}else{
		$prevpage = 1;
	}

	if($page + 1 <= $page_count_num){
		$nextpage = $page+1;
	}else{
		$nextpage = $page_count_num;
	}

	$statvars = array_slice($statvars, $startnum, $finishnum);

	//取数据
	}

	include template('forum/stat_actmem');
}


elseif($op == 'trade') {
	$statvars = getstatvars('trade');
	extract($statvars);
	include template('forum/stat_trade');
} elseif($op == 'team') {
	$statvars = getstatvars('team');
	extract($statvars);
	include template('forum/stat_team');
} elseif($op == 'modworks' && $_G['setting']['modworkstatus']) {
	$statvars = getstatvars('modworks');
	extract($statvars);
	if($_GET['exportexcel']) {
		$filename = 'stat_modworks_'.($username ? $username.'_' : '').$starttime.'_'.$endtime.'.csv';
		include template('forum/stat_misc_export');
		$csvstr = ob_get_contents();
		ob_end_clean();
		header('Content-Encoding: none');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Pragma: no-cache');
		header('Expires: 0');
		if($_G['charset'] != 'gbk') {
			$csvstr = diconv($csvstr, $_G['charset'], 'GBK');
		}
		echo $csvstr;
		exit;
	} else {
		include template('forum/stat_misc');
	}
} elseif($op == 'memberlist' && $_G['setting']['memliststatus']) {
	$statvars = getstatvars('memberlist');
	extract($statvars);
	include template('forum/stat_memberlist');
} elseif($op == 'forumstat') {
	$statvars = getstatvars('forumstat');
	extract($statvars);
	include template('forum/stat_misc');
} elseif($op == 'trend') {
	include libfile('misc/stat', 'include');
} else {
	showmessage('undefined_action');
}

function getstatvars($type) {
	global $_G;
	$statvars = & $_G['cache']['statvars'][$type];

	if(!empty($statvars['lastupdated']) && TIMESTAMP - $statvars['lastupdated'] < CACHE_TIME) {
		return $statvars;
	}

	switch($type) {
		//debug
		case 'salary';	

		case 'basic':
		case 'trade':
		case 'onlinetime':
		case 'team':
		case 'modworks':
		case 'memberlist':
		case 'forumstat':
			$statvars = call_user_func('getstatvars_'.$type, ($type == 'forumstat' ? $_GET['fid'] : ''));//getstatvars_forumstat($_GET['fid']);
			break;
	}
	return $statvars;
}

function getstatvars_salary(){
	global $_G;

}


function getstatvars_basic() {
	global $_G;

	$statvars = array();
	$statvars['members'] = C::t('common_member')->count();
	$members_runtime = C::t('common_member')->fetch_runtime();
	@$statvars['membersaddavg'] = round($statvars['members'] / $members_runtime);
	$statvars['memnonpost'] = C::t('common_member_count')->count_by_posts(0);
	$statvars['mempost'] = $statvars['members'] - $statvars['memnonpost'];
	$statvars['admins'] = C::t('common_member')->count_admins();
	$statvars['lastmember'] = C::t('common_member')->count_by_regdate(TIMESTAMP - 86400);
	$statvars['mempostpercent'] = number_format((double)$statvars['mempost'] / $statvars['members'] * 100, 2);

	$bestmember = C::t('forum_post')->fetch_all_top_post_author(0, $_G['timestamp']-86400, 1);
	$bestmember = $bestmember[0];
	$bestmember['author'] = $bestmember['username'];
	$statvars['bestmem'] = $bestmember['author'];
	$statvars['bestmemposts'] = $bestmember['posts'];
	$postsinfo = C::t('forum_post')->fetch_posts(0);
	$statvars['posts'] = $postsinfo['posts'];
	$runtime= $postsinfo['runtime'];

	@$statvars['postsaddavg'] = round($statvars['posts'] / $runtime);

	@$statvars['mempostavg'] = sprintf ("%01.2f", $statvars['posts'] / $statvars['members']);

	$statvars['forums'] = C::t('forum_forum')->fetch_all_fids(0, 'forum', 0, 0, 0, 1);

	$hotforum = C::t('forum_forum')->fetch_all_for_ranklist(1, '', 'posts', 0, 1);
	$statvars['hotforum'] = array('posts' => $hotforum[0]['posts'], 'threads' => $hotforum[0]['threads'], 'fid' => $hotforum[0]['fid'], 'name' => $hotforum[0]['name']);

	$statvars['threads'] = C::t('forum_thread')->count_all_thread();

	$statvars['postsaddtoday'] = C::t('forum_post')->count_by_dateline(0, TIMESTAMP - 86400);

	@$statvars['threadreplyavg'] = sprintf ("%01.2f", ($statvars['posts'] - $statvars['threads']) / $statvars['threads']);

	$statvars['membersaddtoday'] = $statvars['lastmember'];
	@$statvars['activeindex'] = round(($statvars['membersaddavg'] / $statvars['members'] + $statvars['postsaddavg'] / $statvars['posts']) * 1500 + $statvars['threadreplyavg'] * 10 + $statvars['mempostavg'] * 1 + $statvars['mempostpercent'] / 10);

	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);
	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['basic'] = $statvars;
	savecache('statvars', $_G['cache']['statvars']);

	return $statvars;
}

function getstatvars_trade() {
	global $_G;
	$statvars = array();
	$query = C::t('forum_trade')->fetch_all_statvars('tradesum');
	foreach($query as $data) {
		$tradesums[] = $data;
	}
	$statvars['tradesums'] = $tradesums;
	$query = C::t('forum_trade')->fetch_all_statvars('credittradesum');
	foreach($query as $data) {
		$credittradesums[] = $data;
	}
	$statvars['credittradesums'] = $credittradesums;
	$query = C::t('forum_trade')->fetch_all_statvars('totalitems');
	foreach($query as $data) {
		$totalitems[] = $data;
	}
	$statvars['totalitems'] = $totalitems;

	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);
	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['trade'] = $statvars;
	savecache('statvars', $_G['cache']['statvars']);

	return $statvars;
}

function getstatvars_team() {
	global $_G;

	$statvars = array();
	$team = array();

	$forums = $moderators = $members = $fuptemp = array();
	$categories = array(0 => array('fid' => 0, 'fup' => 0, 'type' => 'group', 'name' => $_G['setting']['bbname']));

	$uids = array();
	foreach(C::t('forum_moderator')->fetch_all_no_inherited() as $moderator) {
		$moderators[$moderator['fid']][] = $moderator['uid'];
		$uids[$moderator['uid']] = $moderator['uid'];
	}

	$totaloffdays = $totalol = $totalthismonthol = 0;
	$admins = array();
	$members = C::t('common_member')->fetch_all($uids) + C::t('common_member')->fetch_all_by_adminid(array(1, 2));
	$uids = array_keys($members);
	$onlinetime = $_G['setting']['oltimespan'] ? C::t('common_onlinetime')->fetch_all($uids) : array();
	$member_status = C::t('common_member_status')->fetch_all($uids);
	$member_count = C::t('common_member_count')->fetch_all($uids);
	foreach($members as $uid => $member) {
		$member = array_merge($member, $member_status[$uid], $member_count[$uid], (array)$onlinetime[$uid]);
		$member['thismonthol'] = $member['thismonth'];
		$member['totalol'] = $member['total'];
		if($member['adminid'] == 1 || $member['adminid'] == 2) {
			$admins[] = $member['uid'];
		}

		$member['offdays'] = intval((TIMESTAMP - $member['lastactivity']) / 86400);
		$totaloffdays += $member['offdays'];

		if($_G['setting']['oltimespan']) {
			$member['totalol'] = round($member['totalol'] / 60, 2);
			$member['thismonthol'] = gmdate('Yn', $member['lastactivity']) == gmdate('Yn', TIMESTAMP) ? round($member['thismonthol'] / 60, 2) : 0;
			$totalol += $member['totalol'];
			$totalthismonthol += $member['thismonthol'];
		}

		$members[$member['uid']] = $member;
		$uids[$member['uid']] = $member['uid'];
	}

	$totalthismonthposts = 0;
	foreach(C::t('forum_post')->fetch_all_author_posts_by_dateline(0, $uids, $_G['timestamp']-86400*30) as $post) {
		$members[$post['authorid']]['thismonthposts'] = $post['posts'];
		$totalthismonthposts += $post['posts'];
	}

	$totalmodposts = $totalmodactions = 0;
	if($_G['setting']['modworkstatus']) {
		$starttime = gmdate("Y-m-1", TIMESTAMP + $_G['setting']['timeoffset'] * 3600);
		foreach(C::t('forum_modwork')->fetch_all_user_count_by_dateline($starttime) as $member) {
			$members[$member['uid']]['modactions'] = $member['actioncount'];
			$totalmodactions += $member['actioncount'];
		}
	}

	$query = C::t('forum_forum')->fetch_all_by_status(1, 1);
	foreach($query as $val) {
		$forum = array('fid' => $val['fid'], 'fup' => $val['fup'], 'type' => $val['type'], 'name' => $val['name'], 'inheritedmod' => $val['inheritedmod']);
		$forum['moderators'] = count($moderators[$forum['fid']]);
		switch($forum['type']) {
			case 'group':
				$categories[$forum['fid']] = $forum;
				$forums[$forum['fid']][$forum['fid']] = $forum;
				$catfid = $forum['fid'];
				break;
			case 'forum':
				$forums[$forum['fup']][$forum['fid']] = $forum;
				$fuptemp[$forum['fid']] = $forum['fup'];
				$catfid = $forum['fup'];
				break;
			case 'sub':
				$forums[$fuptemp[$forum['fup']]][$forum['fid']] = $forum;
				$catfid = $fuptemp[$forum['fup']];
				break;
		}
		if(!empty($moderators[$forum['fid']])) {
			$categories[$catfid]['moderating'] = 1;
		}
	}

	foreach($categories as $fid => $category) {
		if(empty($category['moderating'])) {
			unset($categories[$fid]);
		}
	}

	$team = array	(
		'categories' => $categories,
		'forums' => $forums,
		'admins' => $admins,
		'moderators' => $moderators,
		'members' => $members,
		'avgoffdays' => @($totaloffdays / count($members)),
		'avgthismonthposts' => @($totalthismonthposts / count($members)),
		'avgtotalol' => @($totalol / count($members)),
		'avgthismonthol' => @($totalthismonthol / count($members)),
		'avgmodactions' => @($totalmodactions / count($members)),
	);

	loadcache('usergroups');
	if(is_array($team)) {
		foreach($team['members'] as $uid => $member) {
			@$member['thismonthposts'] = intval($member['thismonthposts']);
			@$team['members'][$uid]['offdays'] = $member['offdays'] > $team['avgoffdays'] ? '<b><i>'.$member['offdays'].'</i></b>' : $member['offdays'];
			@$team['members'][$uid]['thismonthposts'] = $member['thismonthposts'] < $team['avgthismonthposts'] / 2 ? '<b><i>'.$member['thismonthposts'].'</i></b>' : $member['thismonthposts'];
			@$team['members'][$uid]['lastactivity'] = dgmdate($member['lastactivity'] + $timeoffset * 3600, 'd');
			@$team['members'][$uid]['thismonthol'] = $member['thismonthol'] < $team['avgthismonthol'] / 2 ? '<b><i>'.$member['thismonthol'].'</i></b>' : $member['thismonthol'];
			@$team['members'][$uid]['totalol'] = $member['totalol'] < $team['avgtotalol'] / 2 ? '<b><i>'.$member['totalol'].'</i></b>' : $member['totalol'];
			@$team['members'][$uid]['modposts'] = $member['modposts'] < $team['avgmodposts'] / 2 ? '<b><i>'.intval($member['modposts']).'</i></b>' : intval($member['modposts']);
			@$team['members'][$uid]['modactions'] = $member['modactions'] < $team['avgmodactions'] / 2 ? '<b><i>'.intval($member['modactions']).'</i></b>' : intval($member['modactions']);
			@$team['members'][$uid]['grouptitle'] = $_G['cache']['usergroups'][$member['adminid']]['grouptitle'];
		}
	}

	$statvars['team'] = $team;
	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);

	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['team'] = $statvars;
	savecache('statvars', $_G['cache']['statvars']);

	return $statvars;
}

function getstatvars_modworks() {
	global $_G;
	$statvars = array();

	$before = $_GET['before'];
	$before = (isset($before) && $before > 0 && $before <=  $_G['setting']['maxmodworksmonths']) ? intval($before) : 0 ;

	$modworks_starttime = $_GET['modworks_starttime'];
	$modworks_endtime = $_GET['modworks_endtime'];

	list($now['year'], $now['month'], $now['day']) = explode("-", dgmdate(TIMESTAMP, 'Y-n-j'));

	$monthlinks = array();
	$uid = !empty($_GET['uid']) ? $_GET['uid'] : 0;
	for($i = 0; $i <= $_G['setting']['maxmodworksmonths']; $i++) {
		$month = date("Y-m", mktime(0, 0, 0, $now['month'] - $i, 1, $now['year']));
		if($i != $before) {
			$monthlinks[$i] = "<li><a href=\"misc.php?mod=stat&op=modworks&before=$i&uid=$uid\" hidefocus=\"true\">$month</a></li>";
		} else {
			if(!isset($_GET['before']) && $modworks_starttime && $modworks_endtime) {
				$starttime = dgmdate(strtotime($modworks_starttime), 'Y-m-d');
				$endtime = dgmdate(strtotime($modworks_endtime), 'Y-m-d');
				$monthlinks[$i] = "<li><a href=\"misc.php?mod=stat&op=modworks&before=$i&uid=$uid\" hidefocus=\"true\">$month</a></li>";
			} else {
				$starttime = $month.'-01';
				$endtime = date("Y-m-01", mktime(0, 0, 0, $now['month'] - $i + 1 , 1, $now['year']));
				$monthlinks[$i] = "<li class=\"xw1 a\"><a href=\"misc.php?mod=stat&op=modworks&before=$i&uid=$uid\" hidefocus=\"true\">$month</a></li>";
			}
		}
	}
	$statvars['monthlinks'] = $monthlinks;

	$expiretime = date('Y-m', mktime(0, 0, 0, $now['month'] - $_G['setting']['maxmodworksmonths'] - 1, 1, $now['year']));

	$mergeactions = array('OPN' => 'CLS', 'ECL' => 'CLS', 'UEC' => 'CLS', 'EOP' => 'CLS', 'UEO' => 'CLS',
		'UDG' => 'DIG', 'EDI' =>'DIG', 'UED' => 'DIG', 'UST' => 'STK', 'EST' => 'STK',	'UES' => 'STK',
		'DLP' => 'DEL',	'PRN' => 'DEL',	'UDL' => 'DEL',	'UHL' => 'HLT',	'EHL' => 'HLT',	'UEH' => 'HLT',
		'SPL' => 'MRG', 'ABL' => 'EDT', 'RBL' => 'EDT');

	if($uid) {

		$uid = $_GET['uid'];
		$member = getuserbyuid($uid, 1);
		if(!$member || $member['adminid'] == 0) {
			showmessage('member_not_found');
		}

		$modactions = $totalactions = array();
		$starttime_dateline = strtotime($starttime);
		$endtime_dateline = strtotime($endtime);
		$endtime_dateline = $endtime_dateline > TIMESTAMP ? TIMESTAMP : $endtime_dateline;
		while($starttime_dateline <= $endtime_dateline) {
			$modactions[dgmdate($starttime_dateline, 'Y-m-d')] = array();
			$starttime_dateline += 86400;
		}

		foreach(C::t('forum_modwork')->fetch_all_by_uid_dateline($uid, $starttime, $endtime) as $data) {
			if(isset($mergeactions[$data['modaction']])) {
				$data['modaction'] = $mergeactions[$data['modaction']];
			}
			$modactions[$data['dateline']]['total'] += $data['count'];
			$modactions[$data['dateline']][$data['modaction']]['count'] += $data['count'];
			$modactions[$data['dateline']][$data['modaction']]['posts'] += $data['posts'];
			$totalactions[$data['modaction']]['count'] += $data['count'];
			$totalactions[$data['modaction']]['posts'] += $data['posts'];
			$totalactions['total'] += $data['count'];
		}
		$statvars['modactions'] = $modactions;
		$statvars['totalactions'] = $totalactions;
		$statvars['username'] = $member['username'];

	} else {

		$members = $total = array();
		$uids = $totalmodactions = 0;

		$members = C::t('common_member')->fetch_all_by_adminid(array(1, 2, 3));


		foreach(C::t('forum_modwork')->fetch_all_user_count_posts_by_uid_dateline(array_keys($members), $starttime, $endtime) as $data) {
			if(isset($mergeactions[$data['modaction']])) {
				$data['modaction'] = $mergeactions[$data['modaction']];
			}
			$members[$data['uid']]['total'] += $data['count'];
			$totalmodactioncount += $data['count'];

			$members[$data['uid']][$data['modaction']]['count'] += $data['count'];
			$members[$data['uid']][$data['modaction']]['posts'] += $data['posts'];

			$total[$data['modaction']]['count'] += $data['count'];
			$total[$data['modaction']]['posts'] += $data['posts'];
			$total['total'] += $data['count'];

		}

		$avgmodactioncount = @($totalmodactioncount / count($members));
		foreach($members as $id => $member) {
			$members[$id]['totalactions'] = intval($members[$id]['totalactions']);
			$members[$id]['username'] = ($members[$id]['total'] < $avgmodactioncount / 2) ? ('<b><i>'.$members[$id]['username'].'</i></b>') : ($members[$id]['username']);
		}

		if(!empty($before)) {
			C::t('forum_modwork')->delete_by_dateline($expiretime.'-01');
		} else {
			$members['thismonth'] = $starttime;
			$members['lastupdate'] = TIMESTAMP;
			unset($members['lastupdate'], $members['thismonth']);
		}
		$statvars['members'] = $members;
		$statvars['total'] = $total;
	}
	$modactioncode = lang('forum/modaction');

	$bgarray = array();
	foreach($modactioncode as $key => $val) {
		if(isset($mergeactions[$key])) {
			unset($modactioncode[$key]);
		}
	}

	$statvars['modactioncode'] = $modactioncode;
	$tdcols = count($modactioncode) + 1;
	$tdwidth = floor(90 / ($tdcols - 1)).'%';
	$statvars['tdwidth'] = $tdwidth;
	$statvars['uid'] = $uid;
	$statvars['starttime'] = $starttime;
	$statvars['endtime'] = $endtime;
	return $statvars;
}

function getstatvars_memberlist() {
	global $_G;
	$statvars = array();
	$srchmem = $_GET['srchmem'];
	$page = $_G['setting']['membermaxpages'] && isset($_GET['page']) && $_GET['page'] > $_G['setting']['membermaxpages'] ? 1 : $_GET['page'];
	if(empty($page)) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $_G['setting']['memberperpage'];
	$statvars['memberlist'] = C::t('common_member')->fetch_all_stat_memberlist($srchmem, $_GET['order'], $_GET['asc'] ? 'ASC' : 'DESC', $start_limit, $_G['setting']['memberperpage']);
	$num = !empty($srchmem) ?  C::t('common_member')->count_by_like_username($srchmem) :  C::t('common_member')->count();
	$multipage = multi($num, $_G['setting']['memberperpage'], $page, 'misc.php?mod=stat&op=memberlist&srchmem='.rawurlencode($srchmem).'&order='.rawurlencode($_GET['order']).'&asc='.rawurlencode($_GET['asc']), $_G['setting']['membermaxpages']);
	$statvars['multipage'] = $multipage;

	return $statvars;
}

function getstatvars_forumstat($fid) {
	global $_G;
	$xml = "<chart>\n";
	$statvars = array();
	$monthdays = array('31', '29', '31', '30', '31', '30', '31', '31', '30', '31', '30', '31');
	if(!$fid) {
		$query = C::t('forum_forum')->fetch_all_fids();
		$forums = array();
		foreach($query as $val) {
			$forums[] = array('fid' => $val['fid'], 'type' => $val['type'], 'name' => $val['name'], 'posts' => $val['posts']);
		}
		$statvars['forums'] = $forums;
	} else {
		$foruminfo = C::t('forum_forum')->fetch($fid);
		$statvars['foruminfo'] = array('fid' => $foruminfo['fid'], 'name' => $foruminfo['name'], 'posts' => $foruminfo['posts'], 'threads' => $foruminfo['threads'], 'todayposts' => $foruminfo['todayposts']);

		$current_date = $end_date = date('Y-m-d');
		$current_month = $end_month = date('Y-m');
		$current_month_start = $end_month_start = $current_month . '-01';
		if($_GET['month']) {
			$end_month = trim($_GET['month']);
			$month = substr($end_month, strpos($end_month, '-') + 1);
			$end_date = $end_month . '-' . $monthdays[$month - 1];
			$end_month_start = $end_month . '-' . '01';
		}
		$statvars['month'] = $end_month;
		$logs = array();
		$xml .= "<xaxis>\n";
		$xmlvalue = '';
		$xaxisindex = 0;
		foreach(C::t('forum_statlog')->fetch_all_by_logdate($end_month_start, $end_date, $fid) as $log) {
			$logs[] = $log;
			list($yyyy, $mm, $dd) = explode('-', $log['logdate']);
			$xaxisindex++;
			$xml .= "<value xid=\"{$xaxisindex}\">{$mm}{$dd}</value>\n";
			$xmlvalue .= "<value xid=\"{$xaxisindex}\">{$log['value']}</value>\n";
		}
		$xml .= "</xaxis>\n";
		$xml .= "<graphs>\n";
		$xml .= "<graph gid=\"0\" title=\"".diconv(lang('spacecp', 'do_stat_post_number'), CHARSET, 'UTF-8')."\">\n";
		$xml .= $xmlvalue;
		$xml .= "</graph>\n";
		$xml .= "</graphs>\n";
		$xml .= "</chart>\n";
		if($_GET['xml']) {
			@header("Expires: -1");
			@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
			@header("Pragma: no-cache");
			@header("Content-type: application/xml; charset=utf-8");
			echo $xml;
			exit;
		}
		$statvars['logs'] = $logs;

		$mindate = C::t('forum_statlog')->fetch_min_logdate_by_fid($fid);
		list($minyear, $minmonth, $minday) = explode('-', $mindate);
		$minmonth = $minyear . '-' . $minmonth;
		$month = $minmonth;
		$monthlist = array();
		while(datecompare($month, $current_month) <= 0) {
			$monthlist[] = $month;
			$month = getnextmonth($month);
		}
		$statvars['monthlist'] = $monthlist;

		$monthposts = array();
		foreach(C::t('forum_statlog')->fetch_all_by_fid_type($fid) as $data) {
			list($year, $month, $day) = explode('-', $data['logdate']);
			if(isset($monthposts[$year.'-'.$month])) {
				$monthposts[$year.'-'.$month] += $data['value'];
			} else {
				$monthposts[$year.'-'.$month] = $data['value'];
			}
		}
		$statvars['monthposts'] = $monthposts;
	}
	$statvars['statuspara'] = "path=&settings_file=data/stat_setting.xml&data_file=".urlencode("misc.php?mod=stat&op=forumstat&fid=$fid&month={$_GET['month']}&xml=1");
	return $statvars;
}

function datecompare($date1, $date2) {
	$year1 = $month1 = $day1 = 1;
	$year2 = $month2 = $day2 = 1;
	list($year1, $month1, $day1) = explode('-', $date1);
	list($year2, $month2, $day2) = explode('-', $date2);

	return mktime(0, 0, 0, $month1, $day1, $year1) - mktime(0, 0, 0, $month2, $day2, $year2);
}

function getnextmonth($monthdate) {
	list($year, $month) = explode('-', $monthdate);
	$month = $month + 1;
	if($month > 12) {
		$month = 1;
		$year = $year + 1;
	}
	$month = sprintf("%02d", $month);
	return $year . '-' . $month;
}
