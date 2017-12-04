<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_post.php 33848 2013-08-21 06:24:53Z hypowang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

cknewuser();

require_once libfile('class/credit');
require_once libfile('function/post');


//发帖回复前验证手机是否绑定
$filter = $_G['setting']['koueimobilevalidate'];
$filter_arr = explode(',', $filter);
$user_id = $_G['uid'];

$kouei_plugin = $_G['setting']['plugins']['available'];
$count = count($kouei_plugin);

$validate_mobile_flag = false;
$filter_mobile = false;

for ($i = 0; $i < $count; $i++ ) {
	if ( $kouei_plugin[$i] == 'login_mobile' ){
		$validate_mobile_flag = true;
	}
}

for ($j = 0; $j < count($filter_arr); $j++) {
	if ($filter_arr[$j] == $user_id) {
		$filter_mobile = true;
	}
}
//end


$pid = intval(getgpc('pid'));
$sortid = intval(getgpc('sortid'));
$typeid = intval(getgpc('typeid'));
$special = intval(getgpc('special'));

parse_str($_GET['extra'], $_GET['extra']);
$_GET['extra'] = http_build_query($_GET['extra']);

$postinfo = array('subject' => '');
$thread = array('readperm' => '', 'pricedisplay' => '', 'hiddenreplies' => '');

$_G['forum_dtype'] = $_G['forum_checkoption'] = $_G['forum_optionlist'] = $tagarray = $_G['forum_typetemplate'] = array();

if($sortid) {
	require_once libfile('post/threadsorts', 'include');
}

if($_G['forum']['status'] == 3) {
	if(!helper_access::check_module('group')) {
		showmessage('group_status_off');
	}
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid'], 'post');
	if($status == -1) {
		showmessage('forum_not_group', 'index.php');
	} elseif($status == 1) {
		showmessage('forum_group_status_off');
	} elseif($status == 2) {
		showmessage('forum_group_noallowed', "forum.php?mod=group&fid=$_G[fid]");
	} elseif($status == 3) {
		showmessage('forum_group_moderated');
	} elseif($status == 4) {
		if($_G['uid']) {
			showmessage('forum_group_not_groupmember', "", array('fid' => $_G['fid']), array('showmsg' => 1));
		} else {
			showmessage('forum_group_not_groupmember_guest', "", array('fid' => $_G['fid']), array('showmsg' => 1, 'login' => 1));
		}
	} elseif($status == 5) {
		showmessage('forum_group_moderated', "", array('fid' => $_G['fid']), array('showmsg' => 1));
	}
}

if(empty($_GET['action'])) {
	showmessage('undefined_action', NULL);
} elseif($_GET['action'] == 'albumphoto') {
	require libfile('post/albumphoto', 'include');
} elseif(($_G['forum']['simple'] & 1) || $_G['forum']['redirect']) {
	showmessage('forum_disablepost');
}

require_once libfile('function/discuzcode');

$space = array();
space_merge($space, 'field_home');

if($_GET['action'] == 'reply') {
	$addfeedcheck = !empty($space['privacy']['feed']['newreply']) ? 'checked="checked"': '';
} else {
	$addfeedcheck = !empty($space['privacy']['feed']['newthread']) ? 'checked="checked"': '';
}


$navigation = $navtitle = '';

if(!empty($_GET['cedit'])) {
	unset($_G['inajax'], $_GET['infloat'], $_GET['ajaxtarget'], $_GET['handlekey']);
}

if($_GET['action'] == 'edit' || $_GET['action'] == 'reply') {

	$thread = C::t('forum_thread')->fetch($_G['tid']);
	if(!$_G['forum_auditstatuson'] && !($thread['displayorder']>=0 || (in_array($thread['displayorder'], array(-4, -2)) && $thread['authorid']==$_G['uid']))) {
		$thread = array();
	}
	if(!empty($thread)) {

		if($thread['readperm'] && $thread['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $thread['authorid'] != $_G['uid']) {
			showmessage('thread_nopermission', NULL, array('readperm' => $thread['readperm']), array('login' => 1));
		}

		$_G['fid'] = $thread['fid'];
		$special = $thread['special'];

	} else {
		showmessage('thread_nonexistence');
	}

	if($thread['closed'] == 1 && !$_G['forum']['ismoderator']) {
		showmessage('post_thread_closed');
	}
}

if($_G['forum']['status'] == 3) {
	$returnurl = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].(!empty($_GET['extra']) ? '&action=list&'.preg_replace("/^(&)*/", '', $_GET['extra']) : '').'#groupnav';
	$nav = get_groupnav($_G['forum']);
	$navigation = ' <em>&rsaquo;</em> <a href="group.php">'.$_G['setting']['navs'][3]['navname'].'</a> '.$nav['nav'];
} else {
	loadcache('forums');
	$returnurl = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].(!empty($_GET['extra']) ? '&'.preg_replace("/^(&)*/", '', $_GET['extra']) : '');
	$navigation = ' <em>&rsaquo;</em> <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a>';

	if($_G['forum']['type'] == 'sub') {
		$fup = $_G['cache']['forums'][$_G['forum']['fup']]['fup'];
		$t_link = $_G['cache']['forums'][$fup]['type'] == 'group' ? 'forum.php?gid='.$fup : 'forum.php?mod=forumdisplay&fid='.$fup;
		$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['cache']['forums'][$fup]['name']).'</a>';
	}

	if($_G['forum']['fup']) {
		$fup = $_G['forum']['fup'];
		$t_link = $_G['cache']['forums'][$fup]['type'] == 'group' ? 'forum.php?gid='.$fup : 'forum.php?mod=forumdisplay&fid='.$fup;
		$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['cache']['forums'][$fup]['name']).'</a>';
	}

	$t_link = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].($_GET['extra'] && !IS_ROBOT ? '&'.$_GET['extra'] : '');
	$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['forum']['name']).'</a>';

	unset($t_link, $t_name);
}

periodscheck('postbanperiods');

if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
	showmessage('forum_passwd', "forum.php?mod=forumdisplay&fid=$_G[fid]");
}

if(empty($_G['forum']['allowview'])) {
	if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
		showmessagenoperm('viewperm', $_G['fid']);
	}
} elseif($_G['forum']['allowview'] == -1) {
	showmessage('forum_access_view_disallow');
}

formulaperm($_G['forum']['formulaperm']);

if(!$_G['adminid'] && $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 60) && TIMESTAMP - $_G['member']['regdate'] < $_G['setting']['newbiespan'] * 60) {
	showmessage('post_newbie_span', '', array('newbiespan' => $_G['setting']['newbiespan']));
}

$special = $special > 0 && $special < 7 || $special == 127 ? intval($special) : 0;

$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
$_G['group']['allowpostattach'] = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
$_G['forum']['allowpostimage'] = isset($_G['forum']['allowpostimage']) ? $_G['forum']['allowpostimage'] : '';
$_G['group']['allowpostimage'] = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));
$_G['group']['attachextensions'] = $_G['forum']['attachextensions'] ? $_G['forum']['attachextensions'] : $_G['group']['attachextensions'];
require_once libfile('function/upload');
$swfconfig = getuploadconfig($_G['uid'], $_G['fid']);
$imgexts = str_replace(array(';', '*.'), array(', ', ''), $swfconfig['imageexts']['ext']);
$allowuploadnum = $allowuploadtoday = TRUE;
if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
	if($_G['group']['maxattachnum']) {
		$allowuploadnum = $_G['group']['maxattachnum'] - getuserprofile('todayattachs');
		$allowuploadnum = $allowuploadnum < 0 ? 0 : $allowuploadnum;
		if(!$allowuploadnum) {
			$allowuploadtoday = false;
		}
	}
	if($_G['group']['maxsizeperday']) {
		$allowuploadsize = $_G['group']['maxsizeperday'] - getuserprofile('todayattachsize');
		$allowuploadsize = $allowuploadsize < 0 ? 0 : $allowuploadsize;
		if(!$allowuploadsize) {
			$allowuploadtoday = false;
		}
		$allowuploadsize = $allowuploadsize / 1048576 >= 1 ? round(($allowuploadsize / 1048576), 1).'MB' : round(($allowuploadsize / 1024)).'KB';
	}
}
$allowpostimg = $_G['group']['allowpostimage'] && $imgexts;
$enctype = ($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) ? 'enctype="multipart/form-data"' : '';
$maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1).'MB' : round(($_G['group']['maxattachsize'] / 1024)).'KB';

$_G['group']['maxprice'] = isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]) ? $_G['group']['maxprice'] : 0;

$extra = !empty($_GET['extra']) ? rawurlencode($_GET['extra']) : '';
$notifycheck = empty($emailnotify) ? '' : 'checked="checked"';
$stickcheck = empty($sticktopic) ? '' : 'checked="checked"';
$digestcheck = empty($addtodigest) ? '' : 'checked="checked"';

$subject = isset($_GET['subject']) ? dhtmlspecialchars(censor(trim($_GET['subject']))) : '';
$subject = !empty($subject) ? str_replace("\t", ' ', $subject) : $subject;
$message = isset($_GET['message']) ? censor($_GET['message']) : '';
$polloptions = isset($polloptions) ? censor(trim($polloptions)) : '';
$readperm = isset($_GET['readperm']) ? intval($_GET['readperm']) : 0;
$price = isset($_GET['price']) ? intval($_GET['price']) : 0;

if(empty($bbcodeoff) && !$_G['group']['allowhidecode'] && !empty($message) && preg_match("/\[hide=?\d*\].*?\[\/hide\]/is", preg_replace("/(\[code\](.+?)\[\/code\])/is", ' ', $message))) {
	showmessage('post_hide_nopermission');
}


$urloffcheck = $usesigcheck = $smileyoffcheck = $codeoffcheck = $htmloncheck = $emailcheck = '';

list($seccodecheck, $secqaacheck) = seccheck('post', $_GET['action']);

$_G['group']['allowpostpoll'] = $_G['group']['allowpost'] && $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);
$_G['group']['allowposttrade'] = $_G['group']['allowpost'] && $_G['group']['allowposttrade'] && ($_G['forum']['allowpostspecial'] & 2);
$_G['group']['allowpostreward'] = $_G['group']['allowpost'] && $_G['group']['allowpostreward'] && ($_G['forum']['allowpostspecial'] & 4);
$_G['group']['allowpostactivity'] = $_G['group']['allowpost'] && $_G['group']['allowpostactivity'] && ($_G['forum']['allowpostspecial'] & 8);
$_G['group']['allowpostdebate'] = $_G['group']['allowpost'] && $_G['group']['allowpostdebate'] && ($_G['forum']['allowpostspecial'] & 16);
$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'] ? 'checked="checked"' : '';
$ordertypecheck = !empty($thread['tid']) && getstatus($thread['status'], 4) ? 'checked="checked"' : '';
$imgcontentcheck = !empty($thread['tid']) && getstatus($thread['status'], 15) ? 'checked="checked"' : '';
$specialextra = !empty($_GET['specialextra']) ? $_GET['specialextra'] : '';
$_G['forum']['threadplugin'] = dunserialize($_G['forum']['threadplugin']);

if($specialextra && $_G['group']['allowpost'] && $_G['setting']['threadplugins'] &&
	(!array_key_exists($specialextra, $_G['setting']['threadplugins']) ||
	!@in_array($specialextra, is_array($_G['forum']['threadplugin']) ? $_G['forum']['threadplugin'] : dunserialize($_G['forum']['threadplugin'])) ||
	!@in_array($specialextra, $_G['group']['allowthreadplugin']))) {
	$specialextra = '';
}
if($special == 3 && !isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']])) {
	showmessage('reward_credits_closed');
}
$_G['group']['allowanonymous'] = $_G['forum']['allowanonymous'] || $_G['group']['allowanonymous'] ? 1 : 0;

if($_GET['action'] == 'newthread' && $_G['forum']['allowspecialonly'] && !$special) {
	if($_G['group']['allowpostpoll']) {
		$special = 1;
	} elseif($_G['group']['allowposttrade']) {
		$special = 2;
	} elseif($_G['group']['allowpostreward']) {
		$special = 3;
	} elseif($_G['group']['allowpostactivity']) {
		$special = 4;
	} elseif($_G['group']['allowpostdebate']) {
		$special = 5;
	} elseif($_G['group']['allowpost'] && $_G['setting']['threadplugins'] && $_G['group']['allowthreadplugin']) {
		if(empty($_GET['specialextra'])) {
			foreach($_G['forum']['threadplugin'] as $tpid) {
				if(array_key_exists($tpid, $_G['setting']['threadplugins']) && @in_array($tpid, $_G['group']['allowthreadplugin'])){
					$specialextra=$tpid;
					break;
				}
			}
		}
		$threadpluginary = array_intersect($_G['forum']['threadplugin'], $_G['group']['allowthreadplugin']);
		$specialextra = in_array($specialextra, $threadpluginary) ? $specialextra : '';
	}

	if(!$special && !$specialextra) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}


}

if(!$sortid && !$specialextra) {
	$postspecialcheck[$special] = ' class="a"';
}

$editorid = 'e';
$_G['setting']['editoroptions'] = str_pad(decbin($_G['setting']['editoroptions']), 3, 0, STR_PAD_LEFT);
$editormode = $_G['setting']['editoroptions']{0};
$allowswitcheditor = $_G['setting']['editoroptions']{1};
$editor = array(
	'editormode' => $editormode,
	'allowswitcheditor' => $allowswitcheditor,
	'allowhtml' => $_G['forum']['allowhtml'],
	'allowsmilies' => $_G['forum']['allowsmilies'],
	'allowbbcode' => $_G['forum']['allowbbcode'],
	'allowimgcode' => $_G['forum']['allowimgcode'],
	'allowresize' => 1,
	'allowchecklength' => 1,
	'allowtopicreset' => 1,
	'textarea' => 'message',
	'simplemode' => !isset($_G['cookie']['editormode_'.$editorid]) ? !$_G['setting']['editoroptions']{2} : $_G['cookie']['editormode_'.$editorid],
);
if($specialextra) {
	$special = 127;
}

if($_GET['action'] == 'newthread') {
	$policykey = 'post';
} elseif($_GET['action'] == 'reply') {
	$policykey = 'reply';
} else {
	$policykey = '';
}
if($policykey) {

	$postcredits = $_G['forum'][$policykey.'credits'] ? $_G['forum'][$policykey.'credits'] : $_G['setting']['creditspolicy'][$policykey];

}

$albumlist = array();
if(helper_access::check_module('album') && $_G['group']['allowupload'] && $_G['uid']) {
	$query = C::t('home_album')->fetch_all_by_uid($_G['uid'], 'updatetime');
	foreach($query as $value) {
		if($value['picnum']) {
			$albumlist[] = $value;
		}
	}
}

$posturl = "action=$_GET[action]&fid=$_G[fid]".
	(!empty($_G['tid']) ? "&tid=$_G[tid]" : '').
	(!empty($pid) ? "&pid=$pid" : '').
	(!empty($special) ? "&special=$special" : '').
	(!empty($sortid) ? "&sortid=$sortid" : '').
	(!empty($typeid) ? "&typeid=$typeid" : '').
	(!empty($_GET['firstpid']) ? "&firstpid=$firstpid" : '').
	(!empty($_GET['addtrade']) ? "&addtrade=$addtrade" : '');

if($_GET['action'] == 'reply') {
	check_allow_action('allowreply');
} else {
	check_allow_action('allowpost');
}

if($special == 4) {
	$_G['setting']['activityfield'] = $_G['setting']['activityfield'] ? dunserialize($_G['setting']['activityfield']) : array();
}
if(helper_access::check_module('album') && $_G['group']['allowupload'] && $_G['setting']['albumcategorystat'] && !empty($_G['cache']['albumcategory'])) {
	require_once libfile('function/portalcp');
}
$navtitle = lang('core', 'title_'.$_GET['action'].'_post');
if($_GET['action'] == 'newthread' || $_GET['action'] == 'newtrade') {

//人才供求版块二次开发代码开始
	if($_G['fid'] == 68){
		if($_GET['do'] == 'recruit'){
			$formhash = FORMHASH;
			if(!empty($_POST) && $_POST['formhash'] == $formhash){
				$lang = lang('forum/talents');
				require_once libfile('function/home');
				require_once libfile('function/core');
				$stat = array();
				$stat = $_POST;
				$stat = dhtmlspecialchars($stat);
				if(!empty($stat['infos']) && !empty($stat['compname']) && !empty($stat['describe']) && !empty($stat['compplace']) && !empty($stat['contact']) && !empty($stat['sel-contact']) && !empty($stat['compphone']) && !empty($_FILES['bslicense'])){
					$ass = pic_upload($_FILES['bslicense']);
					if(!empty($ass)){
						$infos_arr = $stat['infos'];
						$infos_arr = kouei_get_new_arr($infos_arr);
						$infos_size = count($infos_arr);
						for ($i=0; $i < $infos_size; $i++) {
							if ($infos_size == 1) {
								$infos_str .= $lang['kouei_recruit_pos'].' : '.$infos_arr[$i]['job'].'<br />'.
									$lang['kouei_recruit_request'].' : '.$infos_arr[$i]['request'].'<br />'.
									$lang['kouei_recruit_salary_no'].' : '.$infos_arr[$i]['welfare'].'<br /><br />';
							}else{
								$infos_str .= $lang['kouei_recruit_pos'].($i+1).' : '.$infos_arr[$i]['job'].'<br />'.
									$lang['kouei_recruit_request'].' : '.$infos_arr[$i]['request'].'<br />'.
									$lang['kouei_recruit_salary_no'].' : '.$infos_arr[$i]['welfare'].'<br /><br />';
							}
						}

						$infos_str = dhtmlspecialchars($infos_str);
						if($stat['sel-contact'] == 'male'){
							$stat['contact'] .= $lang['kouei_jobhunt_male'];
						}else{
							$stat['contact'] .= $lang['kouei_jobhunt_female'];
						}
						$message_talents = "<table border='1px'>".
							"<tr><td colspan='2' align='center'><strong>".$lang['kouei_recruit_jobinfo']."</strong></td></tr>".
							"<tr><td width='100px'>".$lang['kouei_recruit_jobinfo']."</td><td width='300px'>".$infos_str."</td></tr>".
							"<tr><td colspan='2' align='center'><strong>".$lang['kouei_recruit_cominfo']."</strong></td></tr>".
							"<tr><td>".$lang['kouei_recruit_company_no']."</td><td>".$stat['compname']."</td></tr>".
							"<tr><td>".$lang['kouei_recruit_describe_no']."</td><td>".$stat['describe']."</td></tr>".
							"<tr><td>".$lang['kouei_recruit_workpla_no']."</td><td>".$stat['compplace']."</td></tr>".
							"<tr><td>".$lang['kouei_recruit_contact_no']."</td><td>".$stat['contact']."</td></tr>".
							"<tr><td>".$lang['kouei_recruit_conn_no']."</td><td>".$stat['compphone']."</td></tr>".
							"</table>";
					}else{
						showmessage('attachment_nonexistence');
					}
				}else{
					showmessage('kouei_talents_form_check');
				}
			}else{
				showmessage('kouei_talents_error');
			}
		}elseif($_GET['do'] == 'jobhunt'){
			$formhash = FORMHASH;
			if(!empty($_POST) && $_POST['formhash'] == $formhash){
				$lang = lang('forum/talents');
				require_once libfile('function/home');
				require_once libfile('function/core');
				require_once libfile('function/talents');
				$stat = array();
				$stat = $_POST;
				$stat = dhtmlspecialchars($stat);
				if(!empty($stat['username']) && !empty($stat['barthdate']) && !empty($stat['gender']) && !empty($stat['wedding']) && !empty($stat['school_info']) && !empty($stat['endschool']) && !empty($stat['qqnum']) && !empty($stat['phonenum']) && !empty($stat['emails']) && !empty($stat['workexperience']) && !empty($stat['workxz']) && !empty($stat['qwposition']) && !empty($stat['daiyu']) && !empty($stat['workplace']) && !is_null($stat['dgsj'])){

					if($stat['dgsj'] == 0){
						$stat['dgsj'] = $lang['kouei_rightnow'];
					}else{
						$stat['dgsj'] .= $lang['kouei_days'];
					}
					$infos_str = '';
					if(!empty($stat['infos'])){
						$infos = array();
						$infos = $stat['infos'];
						$infos = kouei_get_new_arr($infos);
						$infos_size = count($infos);
						for($i = 0; $i < $infos_size; $i++){
							$infos_str .= ($i+1).". ". $lang['kouei_talents_at'].$infos[$i]['start'].$lang['kouei_talents_start'].$lang['kouei_talents_at2'].$infos[$i]['compname'].$lang['kouei_talents_use'].$infos[$i]['zhiwei'].$lang['kouei_talents_use2'].$infos[$i]['end'].$lang['kouei_talents_salary'].$infos[$i]['salary'].$lang['kouei_money']."<br />";
						}
						$infos_str = nl2br($infos_str);
					}
					if(!(bool)trim($stat['selfeval'])){
						$stat['selfeval'] = $lang['kouei_no_selfeval'];
						if($infos_str){
							$message_talents = getKoueiTableContent($stat).getKoueiTableExp($stat,$infos_str).getKoueiTableFoot();
						}else{
							$message_talents = getKoueiTableContent($stat).getKoueiTableFoot();
						}
					}else{
						if($infos_str){
							$message_talents = getKoueiTableContent($stat).getKoueiTableExp($stat,$infos_str).getKoueiTableSelf($stat).getKoueiTableFoot();
						}else{
							$message_talents = getKoueiTableContent($stat).getKoueiTableSelf($stat).getKoueiTableFoot();
						}
					}


				}else{
					showmessage('kouei_talents_form_check');
				}
			}else{
				showmessage('kouei_talents_error');
			}
		}
	}
//人才供求版块二次开发代码结尾
	loadcache('groupreadaccess');
	$navtitle .= ' - '.$_G['forum']['name'];

	//发帖前验证手机是否绑定
	if ($validate_mobile_flag && !$filter_mobile) {
		$username = $_G["username"];
		require_once DISCUZ_ROOT.'source/plugin/login_mobile/table/table_mobile_login_connection.php';
		$phone = C::t("#login_mobile#mobile_login_connection")->getPhone($username);

		if( $phone ){
			require_once libfile('post/newthread', 'include');
		} else {
			showmessage('kouei_validate_mobile_register', 'home.php?mod=spacecp&ac=plugin&id=login_mobile:home_binding');
		}
	} else {
		require_once libfile('post/newthread', 'include');
	}
	//end

} elseif($_GET['action'] == 'reply') {
	$navtitle .= ' - '.$thread['subject'].' - '.$_G['forum']['name'];

	//回复前验证手机是否绑定
	if ($validate_mobile_flag && !$filter_mobile) {
		$username = $_G["username"];
		require_once DISCUZ_ROOT.'source/plugin/login_mobile/table/table_mobile_login_connection.php';
		$phone = C::t("#login_mobile#mobile_login_connection")->getPhone($username);

		if( $phone ){
			require_once libfile('post/newreply', 'include');
		} else {
			showmessage('kouei_validate_mobile_register_extra');
		}
	} else {
		require_once libfile('post/newreply', 'include');
	}
	//end
	
} elseif($_GET['action'] == 'edit') {
	loadcache('groupreadaccess');
	$navtitle .= ' - '.$thread['subject'].' - '.$_G['forum']['name'];
	require_once libfile('post/editpost', 'include');
} elseif($_GET['action'] == 'talents') {
	require_once libfile('post/talents', 'include');
} elseif($_GET['action'] == 'recruit') {
	require_once libfile('post/recruit', 'include');
} elseif($_GET['action'] == 'jobhunt') {
	require_once libfile('post/jobhunt', 'include');
}

function check_allow_action($action = 'allowpost') {
	global $_G;
	if(isset($_G['forum'][$action]) && $_G['forum'][$action] == -1) {
		showmessage('forum_access_disallow');
	}
}
function recent_use_tag() {
	$tagarray = $stringarray = array();
	$string = '';
	$i = 0;
	$query = C::t('common_tagitem')->select(0, 0, 'tid', 'itemid', 'DESC', 10);
	foreach($query as $result) {
		if($i > 4) {
			break;
		}
		if($tagarray[$result['tagid']] == '') {
			$i++;
		}
		$tagarray[$result['tagid']] = 1;
	}
	if($tagarray) {
		$query = C::t('common_tag')->fetch_all(array_keys($tagarray));
		foreach($query as $result) {
			$tagarray[$result[tagid]] = $result['tagname'];
		}
	}
	return $tagarray;
}

function kouei_get_new_arr ($arr){
	$i = 0;
	foreach ($arr as $key => $value) {
		$newarr[$i] = $value;
		$i++;
	}
	return $newarr;
}

?>