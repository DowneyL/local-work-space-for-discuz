<?php
if(!defined('IN_DISCUZ')) {
exit('Access Denied');
}

class plugin_xj_sort{
	function forumdisplay_modlayer(){
		$fid = intval($_GET['fid']);
		$sid = intval($_GET['sid']);
		include template('xj_sort:batch_sort');
		return $return;
	}




	function forumdisplay_threadtype_extra(){
		global $_G;
		$return = '';
		$fid = intval($_GET['fid']);
		$sid = $_GET['sid'];
		$bsorts =  $_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts'];

		if(!empty($bsorts)){
			if(!empty($_GET['filter'])){
				$filter = $filter.'&filter='.$_GET['filter'];
			}
			if(!empty($_GET['typeid'])){
				$filter = $filter.'&typeid='.$_GET['typeid'];
			}
			if(!empty($_GET['sortid'])){
				$filter = $filter.'&sortid='.$_GET['sortid'];
			}
			if($sid){
				$sids = explode('|',$sid);
				$sidlist = unserialize($_G['cookie']['sidlist']);//获得COOKIE缓存的，选择的分类		
				$sidlist[$sids[0]] = $sids[1];  //获得COOKIE缓存的，选择的分类
			}	

			
			$sorthtml = '<div class="bm bml pbn" style="padding:10px;margin-bottom:0px;"><div style="clear:both;line-height:25px;font-weight:bold;">'.lang('plugin/xj_sort','tzfl').': <a href="forum.php?mod=forumdisplay&fid='.$fid.'">'.lang('plugin/xj_sort','qb').'</a></div>';
			$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where classid in(".$bsorts.") ORDER BY classorder");
			while($row = DB::fetch($query)){
				$sorthtml = $sorthtml.'<div style="clear:both;border-top:1px #CCCCCC dashed;line-height:30px;">';
				$sorthtml = $sorthtml.'<a href="javascript:;" style="font-weight:bold;">'.$row['classname'].'</a> : ';
				$sortquery = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where parent=".$row['classid']." ORDER BY classorder");
				while($sortrow = DB::fetch($sortquery)){
					$forumlink = in_array('forum_forumdisplay', $_G['setting']['rewritestatus']) ? rewriteoutput('forum_forumdisplay', 1, '', $fid, $page) : 'forum.php?mod=forumdisplay&fid='.$fid.'&page='.$page;
					if(in_array($sortrow['classid'],$sidlist)){
						$sorthtml = $sorthtml.'<a href="'.$forumlink.'&sid='.$row['classid'].'|'.$sortrow['classid'].$filter.'" style="font-weight:bold;">'.$sortrow['classname'].'</a> ';
					}else{
						$sorthtml = $sorthtml.'<a href="'.$forumlink.'&sid='.$row['classid'].'|'.$sortrow['classid'].$filter.'">'.$sortrow['classname'].'</a> ';
					}
				}
				
				
				
				$sorthtml = $sorthtml.'</div>';
			}
			$sorthtml = $sorthtml.'</div>';
		}
		//$sorthtml = $sorthtml.'<li><a href="forum.php?mod=forumdisplay&amp;fid=2&amp;filter=typeid&amp;typeid=1">动作片</a></li>';
		if($sorthtml){
			$return = $sorthtml.'<br>';
		}

		return $return;	
	}
	function post_top(){
		global $_G;
		$fid = intval($_GET['fid']);
		$tid = intval($_GET['tid']);
		loadcache('forums');

		
		loadcache('plugin');
		$bsorts =  $_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts'];
		$vars = $_G['cache']['plugin']['xj_sort'];
		$fid_str = $vars['kouei_fid_can']; 
		$fid_arr = explode(',',$fid_str);

		if(!empty($bsorts)){
			if(!empty($tid)){
				$query = DB::query("SELECT * FROM ".DB::table('xj_sort_type')." where tid=".$tid);
				$mysort = array();
				while($row = DB::fetch($query)){
					$mysort[] = $row['classid'];
				}
			}
		
			$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where classid in(".$bsorts.") ORDER BY classorder");
			$sort = array();
			$item = array();


			while($row = DB::fetch($query)){
				$item['classid'] = $row['classid'];
				$item['classname'] = $row['classname'];
				$item['classoption'] = $row['classoption'];
				$item['subclass'] = array();
				$sortquery = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where parent=".$row['classid']." ORDER BY classorder");
				while($sortrow = DB::fetch($sortquery)){
					if(in_array($sortrow['classid'],$mysort)){
						$sortrow['select'] = 'yes';
					}else{
						$sortrow['select'] = 'no';
					}
					$item['subclass'][] = $sortrow;
				}
				$sort[] = $item;
			}

				if(!in_array($fid,$fid_arr)){
					include template('xj_sort:post_sort');
				}else{
					include template('xj_sort:post_sort_1');
				}
		}

		return $return;
	}

	function forumdisplay_thread_output(){
		global $_G;
		$fid = $_GET['fid'];
		$bsorts =  $_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts'];
		
		
		//过滤输出防止和其它插件冲突
		$return=array();
		$threadlist = $_G['forum_threadlist'];
		foreach($threadlist as $key => $value){
			$return[] = '';
		}
		//end
		
		
		if($_G['cache']['plugin']['xj_sort']['title_show']==1 and !empty($bsorts)){
			if(empty($_GET['sid'])){
				$fid = intval($_GET['fid']);
				if(!empty($_GET['filter'])){
					$filter = $filter.'&filter='.$_GET['filter'];
				}
				if(!empty($_GET['typeid'])){
					$filter = $filter.'&typeid='.$_GET['typeid'];
				}
				if(!empty($_GET['sortid'])){
					$filter = $filter.'&sortid='.$_GET['sortid'];
				}
				$threadlist = $_G['forum_threadlist'];
				$sortlist = array();
				foreach($threadlist as $key => $value){
					$query = DB::query("SELECT * FROM ".DB::table(xj_sort_type)." A,".DB::table(xj_sort_class)." B WHERE tid='{$value['tid']}' and A.classid=B.classid");
					$sort = '';
					if($query){
						while($value = DB::fetch($query)) {
							$sort = $sort.'<em>[<a href="forum.php?mod=forumdisplay&fid='.$fid.'&sid='.$value['parentid'].'|'.$value['classid'].$filter.'">'.$value['classname'].'</a>]</em>';
						}
					}
					$sortlist[] = $sort;
				}
				$return = $sortlist;
			}
		}
		return $return;
	}
	
}

class plugin_xj_sort_forum extends plugin_xj_sort{
	//主题列表页输出
	function forumdisplay_xj_sort_output($a) {
		$sid = $_GET['sid'];
		$fid = intval($_GET['fid']);
		if(!empty($sid)){
			global $_G,$filterarr,$tableid,$start,$indexadd,$multipage,$separatepos;
			
			$_G['forum_threadlist'] = array();
			$page = $_G['page'];
			if($sid>0){
				$sqlsid = "&sid=".$sid;
				$sids = explode('|',$sid);
				$sidlist = unserialize($_G['cookie']['sidlist']);
				$sidlist[$sids[0]] = $sids[1];
				$sidnum = count($sidlist); //检索分类数
				dsetcookie('sidlist', serialize($sidlist), 0);
				$sidlist=implode(",",$sidlist);
			}
			
			
			$query = DB::query("SELECT count(t.tid) FROM ".DB::table(get_table_name2($tableid))." t,".DB::table('xj_sort_type')." s where t.tid=s.tid and t.`fid`='$fid' and s.classid IN(".$sidlist.") AND t.`displayorder` IN(0,1,2,3,4) group by t.tid HAVING count(t.tid) = $sidnum");
			$_G['forum_threadcount'] = DB::num_rows($query);
			//echo "SELECT count(*) FROM ".DB::table(get_table_name2($tableid))." t,".DB::table('xj_sort_type')." s where t.tid=s.tid and t.`fid`='$fid' and s.classid IN(".$sidlist.") AND t.`displayorder` IN(0,1,2,3,4) group by t.tid HAVING count(t.tid) = $sidnum ORDER BY lastpost DESC";
			//$s = mysql_query("SELECT count(*) FROM ".DB::table(get_table_name2($tableid))." t WHERE t.tid=some(SELECT s.tid FROM ".DB::table('xj_sort_type')." s WHERE s.classid in(".$sidlist.") GROUP BY s.tid HAVING count(s.tid)=$sidnum) AND t.fid=$fid AND t.displayorder IN(0,1,2,3,4)");
			//$_G['forum_threadcount'] = mysql_result($s, 0);

			
			
			if(@ceil($_G['forum_threadcount']/$_G['tpp']) < $page) {
				$page = 1;
			}
			$start_limit = ($page - 1) * $_G['tpp'];
			$separatepos = 0;
			
			$multipage = multi($_G['forum_threadcount'], $_G['tpp'], $page, "forum.php?mod=forumdisplay&fid=$_G[fid]".$sqlsid.$forumdisplayadd['page'].($multiadd ? '&'.implode('&', $multiadd) : '')."$multipage_archive", $_G['setting']['threadmaxpages']);
			
			
			$threadlist = DB::fetch_all("SELECT * FROM ".DB::table(get_table_name2($tableid))." t,".DB::table('xj_sort_type')." s where t.tid=s.tid and t.fid=$fid and s.classid in(".$sidlist.") AND t.`displayorder` IN(0,1,2,3,4) group by t.tid HAVING count(t.tid) = $sidnum ORDER BY lastpost DESC LIMIT ".$start_limit.",".$_G['tpp']);
			//$threadlist = DB::fetch_all("SELECT * FROM ".DB::table(get_table_name2($tableid))." t WHERE t.tid=some(SELECT s.tid FROM ".DB::table('xj_sort_type')." s WHERE s.classid in(".$sidlist.") GROUP BY s.tid HAVING count(s.tid)=$sidnum) AND t.fid=$fid AND t.displayorder IN(0,1,2,3,4) ORDER BY lastpost DESC LIMIT ".$start_limit.",".$_G['tpp']);
			
			while(list($key)=each($threadlist)){  
				$threadlist[$key]['dateline'] = dgmdate($threadlist[$key]['dateline'], 'u', '9999', getglobal('setting/dateformat'));
				$threadlist[$key]['lastpost'] = dgmdate($threadlist[$key]['lastpost'], 'u');
				
				if($_G['forum']['picstyle'] && empty($_G['cookie']['forumdefstyle'])) {
					if($threadlist[$key]['fid'] != $_G['fid'] && empty($threadlist[$key]['cover'])) {
						continue;
					}
					$threadlist[$key]['coverpath'] = getthreadcover($threadlist[$key]['tid'], $threadlist[$key]['cover']);
					$threadlist[$key]['cover'] = abs($threadlist[$key]['cover']);
				}
				
				
				if($_G['forum']['status'] != 3 && ($threadlist[$key]['closed'] || ($_G['forum']['autoclose'] && $threadlist[$key]['fid'] == $_G['fid'] && TIMESTAMP - $threadlist[$key][$closedby] > $_G['forum']['autoclose']))) {
					if($threadlist[$key]['isgroup'] == 1) {
						$threadlist[$key]['folder'] = 'common';
						$grouptids[] = $threadlist[$key]['closed'];
					} else {
						if($threadlist[$key]['closed'] > 1) {
							$threadlist[$key]['moved'] = $threadlist[$key]['tid'];
							$threadlist[$key]['replies'] = '-';
							$threadlist[$key]['views'] = '-';
						}
						$threadlist[$key]['folder'] = 'lock';
					}
				} elseif($_G['forum']['status'] == 3 && $threadlist[$key]['closed'] == 1) {
					$threadlist[$key]['folder'] = 'lock';
				} else {
					$threadlist[$key]['folder'] = 'common';
					$threadlist[$key]['weeknew'] = TIMESTAMP - 604800 <= $threadlist[$key]['dbdateline'];
					if($threadlist[$key]['replies'] > $threadlist[$key]['views']) {
						$threadlist[$key]['views'] = $threadlist[$key]['replies'];
					}
					if($_G['setting']['heatthread']['iconlevels']) {
						foreach($_G['setting']['heatthread']['iconlevels'] as $k => $i) {
							if($threadlist[$key]['heats'] > $i) {
								$threadlist[$key]['heatlevel'] = $k + 1;
								break;
							}
						}
					}
				}
			}
			
			
			//处理贴标标题前面显示分类的过程
			//$sortlist = $_G['setting']['pluginhooks']['forumdisplay_thread']; //获取forumdisplay_thread嵌入点的内容
			if($_G['cache']['plugin']['xj_sort']['title_show']==1){
				$sortlist = array();
				foreach($threadlist as $key => $value){
					$query = DB::query("SELECT * FROM ".DB::table(xj_sort_type)." A,".DB::table(xj_sort_class)." B WHERE tid='{$value['tid']}' and A.classid=B.classid");
					$sort = '';
					if($query){
						while($value = DB::fetch($query)) {
							$sort = $sort.'<em>[<a href="forum.php?mod=forumdisplay&fid='.$fid.'&sid='.$value['parentid'].'|'.$value['classid'].$filter.'">'.$value['classname'].'</a>]</em>';
						}
					}
					$sortlist[] = $sort;
				}
				$_G['setting']['pluginhooks']['forumdisplay_thread'] = $sortlist;
			}
			
			$_G['forum_threadlist'] = $threadlist;
		}else{
			dsetcookie('sidlist', '');//清除分类选择的COOKIE
		}
	}
	
	//发贴处理
	function post_showimg_dzx_message($a) {
		global $_G;
		$fid = intval($_GET['fid']);
		loadcache('forums');
		$bsorts =  $_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts'];
		
		if(!empty($bsorts)){
			if($a['param']['0'] == 'post_newthread_succeed') {   //新贴处理
				$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where classid in(".$bsorts.")");
				while($row = DB::fetch($query)){
					if($row['classoption']==1){    //多选处理
						foreach($_GET['sortid'.$row['classid']] as $value){
							$sortid = intval($value);
							DB::query("insert into ".DB::table('xj_sort_type')."(tid,classid,parentid) values(".$a['param'][2]['tid'].",".$sortid.",".$row['classid'].")");
						}
					}elseif($row['classoption']==0){   //单选处理
						$sortid = intval($_POST['sortid'.$row['classid']]);
						if($sortid>0){
							DB::query("insert into ".DB::table('xj_sort_type')."(tid,classid,parentid) values(".$a['param'][2]['tid'].",".$sortid.",".$row['classid'].")");
						}
					}
				}
			}elseif($a['param']['0'] == 'post_edit_succeed'){   //编辑贴子处理
				$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where classid in(".$bsorts.")");
				while($row = DB::fetch($query)){
					if($row['classoption']==0){  
						$sortid = intval($_POST['sortid'.$row['classid']]);
						if($sortid>0){
							$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_sort_type')." where tid=".$a['param'][2]['tid']." and parentid=".$row['classid']);
							if($count==0){
								DB::query("insert into ".DB::table('xj_sort_type')."(tid,classid,parentid) values(".$a['param'][2]['tid'].",".$sortid.",".$row['classid'].")");
							}else{
								DB::query("update ".DB::table('xj_sort_type')." set classid=".$sortid." where tid=".$a['param'][2]['tid']." and parentid=".$row['classid']);
							}
						}elseif($sortid==0){
							DB::query("DELETE FROM ".DB::table('xj_sort_type')." WHERE tid=".$a['param'][2]['tid']." AND parentid=".$row['classid']);
						}
					}elseif($row['classoption']==1){
						$sortids = $_GET['sortid'.$row['classid']];  //多选类ID数组
						$sortquery = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where parent=".$row['classid']);
						while($sortrow = DB::fetch($sortquery)){
							if(in_array($sortrow['classid'],$sortids)){
								$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('xj_sort_type')." where tid=".$a['param'][2]['tid']." and classid=".$sortrow['classid']);
								if($count==0){
									DB::query("insert into ".DB::table('xj_sort_type')."(tid,classid,parentid) values(".$a['param'][2]['tid'].",".$sortrow['classid'].",".$row['classid'].")");
								}
							}else{
								DB::query("DELETE FROM ".DB::table('xj_sort_type')." WHERE tid=".$a['param'][2]['tid']." AND classid=".$sortrow['classid']);
							}
						}
					}			
				}
			}
		}
	}
	
	function post_newthread() {
		global $_G;
		if($_GET['topicsubmit']=='yes'){
			$fid = intval($_GET['fid']);
			$tid = intval($_GET['tid']);
			loadcache('forums');
			$bsorts =  $_G['cache']['forums'][$fid]['plugin']['xj_sort']['sorts'];
			if(!empty($bsorts)){
				$query = DB::query("SELECT * FROM ".DB::table('xj_sort_class')." where classid in(".$bsorts.") and classmust=1 ORDER BY classorder");
				$errormess = '';
				while($row = DB::fetch($query)){
					if($_GET['sortid'.$row['classid']]==0){
						$errormess = $errormess.$row['classname'].' ';
					}
				}
				if($errormess){
					showmessage(lang('plugin/xj_sort','qxz').' '.$errormess.lang('plugin/xj_sort','fl'));
				}
			}
		}
		return;	
	}


}

function get_table_name2($tableid = 0){
		$tableid = intval($tableid);
		return $tableid ? "forum_thread_$tableid" : 'forum_thread';
}




?>
