<?php
/*
 * 主页：http://addon.discuz.com/?@ailab
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_viewlog {

}

class plugin_viewlog_forum extends plugin_viewlog{
	function viewthread_postbottom_output(){
		global $_G;
		loadcache('plugin');
		lang('plugin/viewlog');

		$vars=$_G['cache']['plugin']['viewlog'];
		$vars['delog']=intval($vars['delog']);
		$vars['detime']=intval($vars['detime']);
		$vars['banauthor']=intval($vars['banauthor']);
		$vars['avatorsize']=intval($vars['avatorsize']);
		if($vars['delog']&&$vars['detime']){
			$detime=TIMESTAMP-$vars['detime']*86400;	
			C::t('#viewlog#viewlog')->delete_by_time($detime);
		}else{
			C::t('#viewlog#viewlog')->delete_by_time(TIMESTAMP-intval(M_PI)*86400);	
		}
		$title=trim($vars['title']);
		if($_G['uid']){
			$logid=C::t('#viewlog#viewlog')->fetch_logid_by_tid_uid($_G['tid'],$_G['uid']);
			if(!$logid){
				$newData=array(
					'uid'=>$_G['uid'],
					'tid'=>$_G['tid'],
					'ip'=>$_G['clientip'],
					'dateline'=>TIMESTAMP, 
				);
				C::t('#viewlog#viewlog')->insert($newData);
			}else{
				$review=intval($vars['review']);
				if($review==1){//1=更新为最新一次浏览 2=只记录第一次浏览
					$newData=array(
						'uid'=>$_G['uid'],
						'tid'=>$_G['tid'],
						'ip'=>$_G['clientip'],
						'dateline'=>TIMESTAMP, 
					);
					C::t('#viewlog#viewlog')->update_by_logid($newData,$logid);
				}
			}
		}

		if($vars['avatorsize'] && $vars['avatorsize'] >= 34 && $vars['avatorsize'] <=48){
			$len = $vars['avatorsize'];
		}elseif($vars['avatorsize'] && $vars['avatorsize'] < 34){
			$len = 34;
		}elseif($vars['avatorsize'] && $vars['avatorsize'] > 48){
			$len = 48;
		}else{
			$len = 34;
		}

		$userList=C::t('#viewlog#viewlog')->fetch_all_by_tid($_G['tid'],intval($vars['num']));
		foreach($userList as $k=>$v){
			$uids[]=$v['uid'];
			$userList[$k]['avatar'] = avatar($v['uid'],'big',true);
			$userList[$k]['dateline']=date('Y-m-d H:i:s',$v['dateline']);
		}

		//二次开发代码
		if(in_array($_G['thread']['authorid'],$uids) && $vars['banauthor']){
			$count = count($uids)-1;
		}else{
			$count = count($uids);
		}

		if($vars['banauthor']&&count($uids)==1&&$uids[0]==$_G['thread']['authorid']){//只有一个楼主的情况
			return '';
		}
		$users=C::t('common_member')->fetch_all_username_by_uid($uids);

		
		include template('viewlog:list');
		if($_G['forum_firstpid']) return array($return,'');
		else return array();
	}
}


?>