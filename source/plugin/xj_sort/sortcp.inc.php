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
	$classid = intval($_POST['classid']);
	$classname = trim($_POST['classname']);
	DB::query("insert into ".DB::table('xj_sort_class')."(classname,parent) values('".$classname."',".$classid.")");
}elseif($_G['gp_op'] == 'del'){
	$classid = intval($_GET['delid']);
	DB::query("delete FROM ".DB::table('xj_sort_class')." where classid=".$classid);
	DB::query("delete FROM ".DB::table('xj_sort_type')." where classid=".$classid);
}elseif($_GET['op'] == 'saveoption'){
	$arrayclassid = $_GET['classid'];
	foreach($arrayclassid as $value){
		$upclassname = trim($_GET['classname'.$value]);
		DB::query("UPDATE ".DB::table('xj_sort_class')." SET classorder=".intval($_GET['classorder'.$value]).",classname='".$upclassname."' WHERE classid=".$value);
	}
}


$ppp = 100;

loadcache('plugin');
$uids = explode(",",$_G['cache']['plugin']['xj_helper']['set_gain_puids']);

//
$jumpmenu = '<script type="text/javascript">function MM_jumpMenu(targ,selObj,restore){ eval(targ+".location=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=xj_sort&pmod=sortcp&bclassid="+selObj.options[selObj.selectedIndex].value+"\'");  if (restore) selObj.selectedIndex=0;}</script>';


//获取大类
$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where parent=0");
$i = 1;
$classid = 0 ;
while($class = DB::fetch($query)) {
	if($i == 1){
		$classid = $class['classid'];
		$i++;
	}
	$classselect = $classselect.'<option value="'.$class['classid'].'" '.($_GET['bclassid']==$class['classid']?'selected':'').'>'.$class['classname'].'</option>';
}


showtableheader();
showformheader('plugins&operation=config&do='.$pluginid.'&identifier=xj_sort&pmod=sortcp&op=add', 'repeatsubmit');
showsubmit('repeatsubmit', lang('plugin/xj_sort', 'add'), $jumpmenu.lang('plugin/xj_sort', 'dlxz').'<select name="classid" id="class" class="select" onchange="MM_jumpMenu(\'self\',this,0)">'.$classselect.'</select>'.lang('plugin/xj_sort', 'flmc').'&nbsp;<input name="classname" value="" class="txt" />', lang('plugin/xj_sort', 'tjxfl'));
showformfooter();



echo '<tr class="header"><th>'.lang('plugin/xj_sort', 'xssx').'</th><th>'.lang('plugin/xj_sort', 'flmc2').'</th><th>'.lang('plugin/xj_sort', 'cz').'</th></tr>';

$extra = '&srchusername='.$_G['gp_srchusername'].'&usergroup='.$_G['gp_usergroup'];

$srchadd = $_G['gp_srchusername']?"and a.username LIKE '%".$_G['gp_srchusername']."%'":"";
$srchadd = $_G['gp_usergroup']?"and a.groupid=".$_G['gp_usergroup']:"";
$bclassid = $_GET['bclassid'];
if(empty($bclassid)){
	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_sort_class')." where parent=$classid");
	$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where parent=$classid order by classorder LIMIT ".(($page - 1) * $ppp).",$ppp");
}else{
	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_sort_class')." where parent=".$bclassid);
	$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where parent=".$bclassid." order by classorder LIMIT ".(($page - 1) * $ppp).",$ppp");
}

$i = 0;
echo '<form name="saveoption" method="post" action="admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=xj_sort&pmod=sortcp&op=saveoption&bclassid='.$_GET['bclassid'].'" id="saveoption">';
while($info = DB::fetch($query)) {
	$i++;
	echo '<tr><td><input name="classid[]" type="hidden" value="'.$info['classid'].'" /><input name="classorder'.$info['classid'].'" value="'.$info['classorder'].'" size="4"/></td><td><input name="classname'.$info['classid'].'" value="'.$info['classname'].'"></td>'.
		'<td><a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=xj_sort&pmod=sortcp&delid='.$info['classid'].'&op=del">'.lang('plugin/xj_sort', 'del').'</a></td></tr>';
}
echo '<tr><td><input name="submit" type="submit" value="'.lang('plugin/xj_sort', 'save').'" class="btn" /></td></tr></form>';
showtablefooter();
echo multi($count, $ppp, $page, ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=xj_sort&pmod=classcp$extra");
?>