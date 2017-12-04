<?php
if(!defined('IN_DISCUZ')){
	exit('Access Denied');
}

class plugin_kouei_help{

}

class plugin_kouei_help_forum extends plugin_kouei_help{
	function  viewthread_useraction(){
		global $_G;
		loadcache('plugin');
		loadcache('forum');
		lang('plugin/kouei_help');
		$view_uid = intval($_G['uid']);
		$author_uid = intval($_G['thread']['authorid']);
		$help_fid = intval($_G['forum']['fid']);
		$tid = intval($_G['tid']);

		$vars=$_G['cache']['plugin']['kouei_help'];
		$vars['oof'] = intval($vars['oof']);

		$stamp_id = $vars['stamp_id'];
		$icon_id = $vars['icon_id'];

		$fid_str = $vars['fid_can'];
		$uid_str = $vars['uid_can'];

		$fid_arr = explode(',',$fid_str);
		$uid_arr = explode(',',$uid_str);

		$flag = in_array($help_fid,$fid_arr) && $view_uid == $author_uid  && $vars['oof'] == 1;

		$flag_banzhu = in_array($help_fid,$fid_arr) && in_array($view_uid,$uid_arr) && $vars['oof'] == 1;
		if( $flag || $flag_banzhu) {
			$op = $_GET['kouei_op']?$_GET['kouei_op']:0;
			if( $op == 1){
				$stamp = $stamp_id;
				$icon = $icon_id;
				$query = DB::query("UPDATE ".DB::table('forum_thread')." SET stamp =".$stamp.",icon =".$icon." where tid=".$tid);
			}

			$query_check = DB::query("SELECT * FROM ".DB::table('forum_thread')." where tid=".$tid);
			$mysort = array();
			while($row = DB::fetch($query_check)){
				$mysort[0] = $row['stamp'];
				$mysort[1] = $row['icon'];
			}
			$stamp = intval($mysort[0]);
			$icon = intval($mysort[1]);

			$url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]; 
			include template('kouei_help:forum_post_button');
		}
		return $return;
	}	
}
?>