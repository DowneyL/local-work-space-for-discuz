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


loadcache('forums');
$fid = intval($_GET['fid']);
if($fid==0){
	foreach($_G['cache']['forums'] as $key => $value){
		if($value['type']=='forum'){
			$fid=$key;
			break;
		}
	}
}

loadcache('plugin');
if($_GET['op'] == 'save') {
	$pluginvarid = DB::result_first("SELECT pluginvarid FROM ".DB::table('common_pluginvar')." where pluginid=$pluginid and variable='sorts'");
	$pluginvars = array();
	$pluginvars[$pluginvarid][$fid]=implode(',',$_POST['sorts']);
	set_pluginsetting($pluginvars);
	updatecache(array('forums'));//¸üÐÂ»º´æ
	$_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts'] = $pluginvars[$pluginvarid][$fid];
}




require_once libfile('function/forumlist');
$forumlist = forumselect(FALSE, 0, $fid, TRUE);
$jumpmenu = '<script type="text/javascript">function MM_jumpMenu(targ,selObj,restore){ eval(targ+".location=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=xj_sort&pmod=forumcp&fid="+selObj.options[selObj.selectedIndex].value+"\'");  if (restore) selObj.selectedIndex=0;}</script>';


showtableheader();


echo $jumpmenu.lang('plugin/xj_sort','bkxz').'<select name="classid" id="class" class="select" onchange="MM_jumpMenu(\'self\',this,0)">'.$forumlist.'</select>';
$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where parent=0");
$sort =array();
while($item = DB::fetch($query)){
	$value = array();
	$value[] = $item['classid'];
	$value[] = $item['classname'];
	$sort[] = $value;
}
//echo explode(',',$_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts']);
$sortselect = explode(',',$_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts']);

showformheader('plugins&operation=config&do='.$pluginid.'&identifier=xj_sort&pmod=forumcp&op=save&fid='.$fid,'','savesort');
showsetting(lang('plugin/xj_sort','xzkydfl'), array('sorts', $sort), $sortselect, 'mcheckbox');
showsubmit('savesort',lang('plugin/xj_sort','save'));
showformfooter();



showtablefooter();

?>