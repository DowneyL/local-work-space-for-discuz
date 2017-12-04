<?php
/*
 * 主页：http://addon.discuz.com/?@ailab
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
 
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
require_once libfile('function/misc');
loadcache('plugin');
$users=array();
$ips=array();
$pagenum=18;
$page=max(1,intval($_GET['page']));
$count=C::t('#viewlog#viewlog')->count();
showtableheader(lang('plugin/viewlog','title').lang('plugin/viewlog','title_tips'));
showsubtitle(array(
	lang('plugin/viewlog','subject'),
	lang('plugin/viewlog','username'),
	lang('plugin/viewlog','dateline'),
	lang('plugin/viewlog','ipdata'),
));
$list=C::t('#viewlog#viewlog')->fetch_all_by_range(($page-1)*$pagenum,$pagenum);
$threads=array();
foreach($list as $data) {
	userList($data['uid']);
	ipStrs($data['ip']);
	if(!isset($threads[$data['tid']])) $threads[$data['tid']]=C::t('forum_thread')->fetch($data['tid']);
	showtablerow('', array('class="td_k"', 'class="td_k"', 'class="td_l"'), array(
		'<a href="forum.php?mod=viewthread&tid='.$threads[$data['tid']]['tid'].'" target="_blank">'.$threads[$data['tid']]['subject'].'</a>',
		'<a href="home.php?mod=space&uid='.$data['uid'].'" target="_blank">'.$users[$data['uid']].'</a>',
		date('Y-m-d H:i:s',$data['dateline']),
		$ips[md5($data['ip'])],
	));
}
showtablefooter();
echo multi($count,$pagenum,$page,ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=viewlog&pmod=data");
function userList($uid){
	global $users;
	if(!isset($users[$uid])){
		$info=C::t('common_member')->fetch_all_username_by_uid(array($uid));
		$users[$uid]=$info[$uid];
	}
}
function ipStrs($ip){
	global $ips;
	if(!isset($ips[md5($ip)])){
		$myadd=convertip($ip);
		$myadd=str_replace(' ','',str_replace('-','',$myadd));
		$ips[md5($ip)]=$myadd;
	}
}
?>