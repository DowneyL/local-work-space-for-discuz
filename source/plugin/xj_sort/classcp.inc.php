<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp.inc.php 18582 2010-11-29 07:12:59Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}



if($_G['gp_op'] == 'add') {
	$classname = trim($_POST['classname']);
	DB::query("insert into ".DB::table('xj_sort_class')."(classname) values('".$classname."')");
}elseif($_G['gp_op'] == 'del'){
	$classid = intval($_GET['classid']);
	DB::query("delete FROM ".DB::table('xj_sort_class')." where classid=".$classid." or parent=".$classid);
	DB::query("delete FROM ".DB::table('xj_sort_type')." where parentid=".$classid);
}elseif($_GET['op'] == 'saveoption'){
	$arrayclassid = $_GET['classid'];
	foreach($arrayclassid as $value){
		$upclassname = trim($_GET['classname'.$value]);
		DB::query("UPDATE ".DB::table('xj_sort_class')." SET classoption=".intval($_GET['classoption'.$value]).",classorder=".intval($_GET['classorder'.$value]).",classmust=".intval($_GET['classmust'.$value]).",classname='".$upclassname."' WHERE classid=".$value);
	}
}


$ppp = 100;

loadcache('plugin');
$uids = explode(",",$_G['cache']['plugin']['xj_helper']['set_gain_puids']);


showtableheader();
showformheader('plugins&operation=config&do='.$pluginid.'&identifier=xj_sort&pmod=classcp&op=add', 'repeatsubmit');
showsubmit('repeatsubmit', lang('plugin/xj_sort', 'add'), lang('plugin/xj_sort', 'flmc').'&nbsp;<input name="classname" value="" class="txt" />', lang('plugin/xj_sort', 'tjdfl'));
showformfooter();



echo '<tr class="header"><th>'.lang('plugin/xj_sort', 'xssx').'</th><th>'.lang('plugin/xj_sort', 'flmc2').'</th><th>'.lang('plugin/xj_sort', 'flxx').'</th><th>'.lang('plugin/xj_sort', 'cz').'</th></tr>';

$extra = '&srchusername='.$_G['gp_srchusername'].'&usergroup='.$_G['gp_usergroup'];

$srchadd = $_G['gp_srchusername']?"and a.username LIKE '%".$_G['gp_srchusername']."%'":"";
$srchadd = $_G['gp_usergroup']?"and a.groupid=".$_G['gp_usergroup']:"";
$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_sort_class')." where parent=0");
$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where parent=0 order by classorder LIMIT ".(($page - 1) * $ppp).",$ppp");

$i = 0;
echo '<form name="saveoption" method="post" action="admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=xj_sort&pmod=classcp&op=saveoption" id="saveoption">';
while($info = DB::fetch($query)) {
	$i++;
	
	echo '<tr><td><input name="classorder'.$info['classid'].'" value="'.$info['classorder'].'" size="4"/></td><td><input name="classname'.$info['classid'].'" value="'.$info['classname'].'"><input name="classid[]" type="hidden" value="'.$info['classid'].'" /></td><td><input class="radio" type="radio" name="classoption'.$info['classid'].'" value="0" '.($info['classoption']==0?'checked':'').' onclick="showDiv(\'submit'.$info['classid'].'\');">'.lang('plugin/xj_sort', 'dx').' <input class="radio" type="radio" name="classoption'.$info['classid'].'" value="1" '.($info['classoption']==1?'checked':'').'>'.lang('plugin/xj_sort', 'duox').' <input name="classmust'.$info['classid'].'" type="checkbox" id="classmust" value="1" class="checkbox" '.($info['classmust']==1?'checked':'').'/>'.lang('plugin/xj_sort','bxx').'</td>'.
		'<td><a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=xj_sort&pmod=classcp&classid='.$info['classid'].'&op=del">'.lang('plugin/xj_sort', 'del').'</a></td></tr>';
}
echo '<tr><td><input name="submit" type="submit" value="'.lang('plugin/xj_sort', 'save').'" class="btn" /></td></tr></form>';

showtablefooter();
echo multi($count, $ppp, $page, ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=xj_sort&pmod=classcp$extra");
?>