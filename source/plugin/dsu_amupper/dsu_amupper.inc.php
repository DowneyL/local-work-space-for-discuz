<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['formhash'] != FORMHASH) {
	showmessage('undefined_action');
}

$this_table['l'] = 'plugin_dsuamupper';
$this_table['c'] = 'plugin_dsuamupper_c';
if($_G['uid']){
	$thisvars = $_G['cache']['plugin']['dsu_amupper'];
	$thisvars['offset'] = $_G['setting']['timeoffset'];
	$thisvars['gids'] = (array)unserialize($thisvars['gids']);
	$thisvars['today'] = dgmdate($_G['timestamp'],'Ymd',$thisvars['offset']);
	$thisvars['uid'] = DB::fetch_first('SELECT * FROM %t WHERE %i', array($this_table['l'], DB::field('uid', $_G['uid'])));
	$this_time = istoday($thisvars['uid']['lasttime']);
	$this_Hs = isH($thisvars['uid']['lasttime']);
	$this_H = $this_Hs['return'];
	$ptjfname = $_G['setting']['extcredits'][$thisvars['ptjf']]['title'];
}

if(!in_array($_G['groupid'],$thisvars['gids'])){
	showmessage('dsu_amupper:ed', '', array(), array('showdialog' => true, 'alert' => 'error', 'closetime' => true));
}

///普通奖励积分的计算与是否连续签到的判断
if($this_time == -1 || $thisvars['sz']){
	$cons = $thisvars['uid']['cons']=="" ? 0 : $thisvars['uid']['cons'] + 1 ;
	$addup = $thisvars['uid']['addup'] + 1 ;
}elseif($this_time == 0){
	$cons = $thisvars['uid']['cons'];
	$addup = $thisvars['uid']['addup'];
}else{
	$cons = 0;
	$addup = $thisvars['uid']['addup'] + 1 ;
}
if($this_time <> 0){
	if(dsucheckformulacredits($thisvars['ptgs'])){
		$amu_formula = str_replace("leiji",$addup,$thisvars['ptgs']);
		$amu_formula = str_replace("lianxu",$cons,$amu_formula);
		@eval("\$pt = $amu_formula;");
		$pt = empty($thisvars['ptmax']) ? intval($pt) : intval(min($pt, $thisvars['ptmax']));
		$amu_formula_n = str_replace("leiji",$addup + 1,$thisvars['ptgs']);
		$amu_formula_n = str_replace("lianxu",$cons + 1,$amu_formula_n);
		@eval("\$pt_n = $amu_formula_n;");
		$pt_n = empty($thisvars['ptmax']) ? intval($pt_n) : intval(min($pt_n, $thisvars['ptmax']));
		//echo $amu_formula;
	}else{
		$pt = $pt_n = 1;
	}


	$tsarr = DB::fetch_all("SELECT * FROM %t WHERE id>%d LIMIT %d", array('plugin_dsuamupperc', '-1', '100'), 'id');
	$data_f2a =dstripslashes($tsarr);
	$next_old='';
	if($tsarr && $thisvars['ms'] == 3){
		foreach ($data_f2a as $id => $result){
			if(($_G['groupid'] == $result['usergid']|| $result['usergid'] <= '0') && $cons == $result['days']){
				$teshu[$id] = $result;
				$tsmsg[] = lang('plugin/dsu_amupper','tsmsg',array('days'=>$result['days'], 'title'=>$_G['setting']['extcredits'][$result['extcredits']]['title'], 'reward'=>$result['reward']));
			}
			$next = $result['days'] - $cons;
			if(($_G['groupid'] == $result['usergid']|| $result['usergid'] <= '0') && $next > 0 && ($next < $next_old || empty($next_old)) ){
				$next_msg = lang('plugin/dsu_amupper','next_msg',array('cons'=>$result['days'], 'next'=>$next, 'ptjfname'=>$_G['setting']['extcredits'][$result['extcredits']]['title'], 'pt'=>$result['reward']));
				$next_old = $next;
			}
		}
	}

	if($tsarr && $thisvars['ms'] == 4){
		
		foreach ($data_f2a as $id => $result){
			$yushu = $cons % $result['days'];
			if(($_G['groupid'] == $result['usergid']|| $result['usergid'] <= '0') && $yushu == 0 && $cons > 0){
				$teshu[$id] = $result;
				$tsmsg[] = lang('plugin/dsu_amupper','tsmsg',array('days'=>$result['days'], 'title'=>$_G['setting']['extcredits'][$result['extcredits']]['title'], 'reward'=>$result['reward']));
			}
			$next = $result['days'] - ($cons % $result['days']);
			$cons_next = $cons + $next;

			if(($_G['groupid'] == $result['usergid']|| $result['usergid'] <= '0') && $next > 0 && ($next < $next_old || empty($next_old))){
				$next_msg = lang('plugin/dsu_amupper','next_msg',array('cons'=>$cons_next, 'next'=>$next, 'ptjfname'=>$_G['setting']['extcredits'][$result['extcredits']]['title'], 'pt'=>$result['reward']));
				$next_old = $next;
			}
		}
	}


	$ptmsg = lang('plugin/dsu_amupper','ptmsg',array('addup'=>$addup, 'cons'=>$cons, 'ptjfname'=>$ptjfname, 'pt'=>$pt, 'pt_n'=>$pt_n));


	if(file_exists(DISCUZ_ROOT.'./data/tid_amupper.lock')) {
		showmessage('dsu_amupper:wrong', '', array(), array('showdialog' => true, 'alert' => 'error', 'closetime' => true));
	}else{
		$jiangliba = 0;
		if( $this_time < 0 && $thisvars['uid']['time'] <> dgmdate($_G['timestamp'],'Ymd', $_G['setting']['timeoffset'])){
			switch ($thisvars['ms']){
				case 1:
					//关闭插件
				break;
				
				case 2:
					//无特殊奖励
					$return_msg = $ptmsg;
				break;

				case 3:
					//特殊奖励(N)
					if($tsmsg){$tsmsg = implode('，', $tsmsg).'. ';}
					$return_msg = $ptmsg.$tsmsg.$next_msg;
				break;

				case 4:
					//特殊奖励(Y)
					if($tsmsg){$tsmsg = implode('，', $tsmsg).'. ';}
					$return_msg = $ptmsg.$tsmsg.$next_msg;
				break;
			}

			if($thisvars['ft']){	
				$subject =  str_replace("time",dgmdate($_G['timestamp'],'Y-m-d',$thisvars['offset']),$thisvars['bt']);
				$today = dgmdate($_G['timestamp'],'Ymd',$thisvars['offset']);

				$arr = DB::fetch_first('SELECT allow FROM %t WHERE time=%d', array($this_table['l'], $today));
				$thistid = DB::fetch_first('SELECT * FROM %t WHERE %i', array('forum_thread', DB::field('tid', $arr['allow'])));

				if($arr['allow'] && $thistid['displayorder'] <> -1 && $thistid['closed'] == 1 ){
					$arr['pid'] = addnewpid($thisvars['ft'],$arr['allow'],$subject,$return_msg);
				}elseif($arr['allow'] && $thistid['displayorder'] == -1 && $thistid['closed'] == 1){
					$id = addnewtid($thisvars['ft'], $subject, $return_msg, $thisvars['ztfn']);
					$arr['pid'] = $id['pid'];$arr['allow'] = $id['tid'];
					DB::query('UPDATE %t SET allow = %d WHERE time = %d', array($this_table['l'], $arr['allow'], $today));
				}elseif(!$arr['allow'] || !$thistid){
					$id = addnewtid($thisvars['ft'], $subject, $return_msg, $thisvars['ztfn']);
					$arr['pid'] = $id['pid'];$arr['allow'] = $id['tid'];
					DB::query('UPDATE %t SET allow = %d WHERE time = %d', array($this_table['l'], $arr['allow'], $today));
				}
				
				if($arr['allow'] && $arr['pid']){
					$intosql = array(
						'uid'=>intval($_G['uid']),
						'uname'=>dhtmlspecialchars("'".addslashes($_G['username'])."'"),
						'addup'=>intval($addup),
						'cons'=>intval($cons),
						'lasttime'=>intval($_G['timestamp']),
						'time'=>intval($today),
						'allow'=>intval($arr['allow']),
					);
					DB::query('REPLACE INTO '.DB::table($this_table['l']).'(uid, uname, addup, cons, lasttime, time, allow) VALUES ('.implode(',', $intosql).')');
					$jiangliba = 1;
					
				}
				
			}else{
				$intosql = array(
					'uid'=>intval($_G['uid']),
					'uname'=>dhtmlspecialchars("'".addslashes($_G['username'])."'"),
					'addup'=>intval($addup),
					'cons'=>intval($cons),
					'lasttime'=>intval($_G['timestamp']),
					'time'=>intval($today),
					'allow'=>intval($arr['allow']),
				);
				DB::query('REPLACE INTO '.DB::table($this_table['l']).'(uid, uname, addup, cons, lasttime, time, allow) VALUES ('.implode(',', $intosql).')');
				$jiangliba = 1;
				
			}
			
			if($jiangliba == 1){
				switch ($thisvars['ms']){
					case 1:
						//关闭插件
					break;
					
					case 2:
						//无特殊奖励
						updatemembercount($_G['uid'], array("extcredits{$thisvars['ptjf']}" => $pt), true,'',0);
					break;

					case 3:
						//特殊奖励(N)
						if(is_array($teshu)){
							foreach ($teshu as $id => $result){
								updatemembercount($_G['uid'], array("extcredits{$result['extcredits']}" => $result['reward']), true,'',0);
							}
						}
						updatemembercount($_G['uid'], array("extcredits{$thisvars['ptjf']}" => $pt), true,'',0);
					break;

					case 4:
						//特殊奖励(Y)
						if(is_array($teshu)){
							foreach ($teshu as $id => $result){
								updatemembercount($_G['uid'], array("extcredits{$result['extcredits']}" => $result['reward']), true,'',0);
							}
						}
						updatemembercount($_G['uid'], array("extcredits{$thisvars['ptjf']}" => $pt), true,'',0);
					break;
				}
			}

		}
	}
}elseif($this_time == 0 && $this_H){
	$Hreward = rand($this_Hs['minreward'],$this_Hs['maxreward']);
	$return_msg = lang('plugin/dsu_amupper','Hmsg',array('ptjfname'=>$ptjfname, 'pt'=>$Hreward));
	DB::query('UPDATE %t SET lasttime = %d WHERE uid = %d', array($this_table['l'], $_G['timestamp'], $_G['uid']));
	updatemembercount($_G['uid'], array("extcredits{$thisvars['ptjf']}" => $Hreward), true,'',0);
}

if($return_msg){
	if(defined('VIP_INITED')) vip::hooks('sign');
	dsetcookie('dsu_amuppered', $_G['uid'], 3600);
	dsetcookie('dsu_amupper', 0, 1);
	if($this_Hs['ok']){
		$return_msg = $return_msg.lang('plugin/dsu_amupper','Hendmsg',array());
	}
	if($arr['allow'] && $arr['pid']){
		$url = "forum.php?mod=redirect&goto=findpost&ptid={$arr['allow']}&pid={$arr['pid']}";
		if($thisvars['autogo'] && empty($_GET['nojump'])){
			showmessage($return_msg, $url, array(),array('showmsg' => 1,  'showdialog' => 1, 'alert' => 'right', 'locationtime' => 3));
		}else{
			showmessage($return_msg, $url, array(),array('showmsg' => 1,  'showdialog' => 1, 'alert' => 'right', 'closetime' => 3));
		}
		
	}else{
		showmessage($return_msg, '', array(), array('showmsg' => 1, 'showdialog' => 1, 'alert' => 'right', 'closetime' => 3));
	}
}else{
	dsetcookie('dsu_amuppered', $_G['uid'], 600);
	dsetcookie('dsu_amupper', 0, 1);
	if($this_Hs['ok']){
		showmessage( lang('plugin/dsu_amupper','Hed',array('max'=>$this_Hs['max'])), '', array(), array('showmsg' => 1, 'alert' => 'error', 'showdialog' => 1));
	}else{
		showmessage('dsu_amupper:ed', '', array(), array('showmsg' => 1, 'alert' => 'error', 'showdialog' => 1));
	}
	
}


///自定义函数区
function istoday($time){
	global $_G;	
	$time = empty($time) ? 0 : $time ;
	$today = dgmdate($_G['timestamp'],'Ymd', $_G['setting']['timeoffset']);
	$yesterday = dgmdate($_G['timestamp']-3600*24,'Ymd',$_G['setting']['timeoffset']);
	$lastday = dgmdate($time,'Ymd',$_G['setting']['timeoffset']);
	$days = $lastday - $today;
	if($lastday == $yesterday){$days = -1;}
	return $days ;
}


function isH($time){
	global $_G;	
	include_once 'source/plugin/dsu_amupper/config.php';
	if($pperconfig['max'] && $pperconfig['minreward'] && $pperconfig['maxreward']){
		$nt1 = dgmdate($_G['timestamp'],'i',$_G['setting']['timeoffset']);
		$nt2 = dgmdate($_G['timestamp'],'s',$_G['setting']['timeoffset']);
		$nt = $nt1*60 + $nt2;
		$Htime =$_G['timestamp'] - $nt;	
		$Hnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_dsuamupper')." WHERE lasttime >= '{$Htime}'");
		$time = empty($time) ? 0 : $time ;
		$nowtime = dgmdate($_G['timestamp'],'H',$_G['setting']['timeoffset']);
		$last = dgmdate($time,'H',$_G['setting']['timeoffset']);
		$H = $pperconfig;
		$H['ok'] = 1;
		$H['return'] = $last + 1 > $nowtime  ? 0 : 1;
		$H['return'] = $Hnum < $pperconfig['max'] ? $H['return'] : 0;
	}
	return $H;
}

function dsucheckformulsyntax($formula, $operators, $tokens) {
	$var = implode('|', $tokens);
	$operator = implode('', $operators);

	$operator = str_replace(
		array('+', '-', '*', '/', '(', ')', '{', '}', '\''),
		array('\+', '\-', '\*', '\/', '\(', '\)', '\{', '\}', '\\\''),
		$operator
	);

	if(!empty($formula)) {
		if(!preg_match("/^([$operator\.\d\(\)]|(($var)([$operator\(\)]|$)+))+$/", $formula) || !is_null(eval(preg_replace("/($var)/", "\$\\1", $formula).';'))){
			return false;
		}
	}
	return true;
}

function dsucheckformulacredits($formula) {
	return dsucheckformulsyntax(
		$formula,
		array('+', '-', '*', '/', ' '),
		array('lianxu', 'leiji')
	);
}

function addnewtid($fid,$subject,$message,$typeid=0){
	global $_G;
	if($_G['uid'] && $fid && $subject && $message){
		require_once libfile('function/forum');
		DB::insert('forum_thread', array(
			'fid'=>$fid,
			'author'=>$_G['username'],
			'authorid'=>$_G['uid'],
			'subject' =>$subject,
			'typeid' => $typeid,
			'dateline' => $_G['timestamp'],
			'lastpost' => $_G['timestamp'],
			'lastposter'=>$_G['username'],
			'closed'=>1));

		$id['tid'] = $tid = DB::insert_id();

		if($tid){
			$message = '[b]'.$_G['username'].'[/b],'.$message;
			$id['pid']=insertpost(array(
				'fid'=>$fid,
				'tid' => $tid,
				'first'=>'1',
				'author'=>$_G['username'],
				'authorid'=>$_G['uid'],
				'subject'=>$subject,
				'dateline'=>$_G['timestamp'],
				'message'=>$message,
				'useip'=>$_G['clientip']));
			$lastpost = "$tid\t".addslashes($subject)."\t$_G[timestamp]\t".addslashes($_G['username']);
			DB::query('UPDATE '.DB::table('forum_forum')." SET threads=threads+'1', todayposts=todayposts+1, lastpost='{$lastpost}' WHERE fid=".$fid);
		}
	}
	
	return $id;
}

function addnewpid($fid,$tid,$subject='',$message){
	global $_G;
	if($_G['uid'] && $fid && $tid && $message){
		require_once libfile('function/forum');
		$message = '[b]    '.$_G['username'].'[/b],'.$message;
		$pid=insertpost(array(
			'fid'=>$fid,
			'tid'=>$tid,
			'first'=>'0',
			'author'=>$_G['username'],
			'authorid'=>$_G['uid'],
			'subject'=>'',
			'dateline'=>$_G['timestamp'],
			'message'=>$message,
			'useip'=>$_G['clientip']));
		if($pid){
			DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='".addslashes($_G['username'])."', lastpost='$_G[timestamp]', replies=replies+1 WHERE tid='$tid' AND fid='$fid'", 'UNBUFFERED');
			$lastpost = "$tid\t".addslashes($subject)."\t$_G[timestamp]\t".addslashes($_G['username']);
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', posts=posts+1, todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
			return $pid;
		}
	}
}
?>


