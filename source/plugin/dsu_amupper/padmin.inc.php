<?php
/**
此破解程序由星期六源码【xlqzm.com】提供，更多商业源码请登录星期六源码官网
官方网站:xlqzm.com
更多商业插件：http://xqlzm.com/forum-113-1.html
更多商业模板：http://xqlzm.com/forum-112-1.html
更多商业源码：http://xqlzm.com/forum-141-1.html
**/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['formhash'] != FORMHASH) {
	showmessage('undefined_action');
}

$padminid = $_G['cache']['plugin']['dsu_amupper']['adminid'];

if($_G['uid'] == $padminid && $padminid){
	$_GET['uid'] = intval($_GET['uid']);
	if(empty($_GET['uid'])){
		include template('dsu_amupper:padmin');
	}else{
		$puid = DB::fetch_first('SELECT * FROM %t WHERE %i', array('plugin_dsuamupper', DB::field('uid', $_GET['uid'])));
		$_GET['cons'] = intval($_GET['cons']);
		if($_GET['ppersubmit'] && $puid && $_GET['cons']){
			$intosql = array(
				'uid'=>intval($puid['uid']),
				'uname'=>dhtmlspecialchars("'".addslashes($puid['uname'])."'"),
				'addup'=>intval($puid['addup']),
				'cons'=>intval($_GET['cons']),
				'lasttime'=>intval($puid['lasttime']),
				'time'=>intval($puid['time']),
				'allow'=>intval($puid['allow']),
			);
			DB::query('REPLACE INTO '.DB::table('plugin_dsuamupper').'(uid, uname, addup, cons, lasttime, time, allow) VALUES ('.implode(',', $intosql).')');
			showmessage('dsu_amupper:postok', '', array(), array('showmsg' => true,'alert' => 'right', 'closetime' =>3));
		}
		include template('dsu_amupper:padmin');
	}
}else{
	showmessage('to_login', 'member.php?mod=logging&action=login', array(), array('showmsg' => true, 'login' => 1));
}

?>