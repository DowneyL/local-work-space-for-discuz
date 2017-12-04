<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
if($operation != 'export'){
	cpheader();
}
require_once libfile('function/delete');


$_G['setting']['recruitperpage'] = 5;
$page = max(1, $_G['page']);
$start_limit = ($page - 1) * $_G['setting']['recruitperpage'];

// $search_condition = array_merge($_GET, $_POST);
// foreach($search_condition as $k => $v) {
// 	if(in_array($k, array('action', 'operation', 'formhash', 'confirmed', 'submit', 'page', 'deletestart', 'allnum', 'includeuc','includepost','current','pertask','lastprocess','deleteitem')) || $v === '') {
// 		unset($search_condition[$k]);
// 	}
// }
shownav('forum', 'kouei_rec_title');

if($operation == 'search'){

	if(!submitcheck('submit',1)){
		if($operation == 'search'){
			showsubmenu('kouei_rec_title', array(
				array('search', 'koueirec&operation=search', 1)
			));
			showtips('kouei_rec_export_tips');
			echo <<<EOT
	<script type="text/javascript" src="static/js/calendar.js"></script>
	<script type="text/JavaScript">
	function page(number) {
		$('koueirecforum').page.value=number;
		$('koueirecforum').searchsubmit.click();
	}
	</script>
EOT;
			showtagheader('div', 'koueirecposts', !$searchsubmit);
			showformheader("koueirec".($operation ? '&operation='.$operation : ''), '', 'koueirecforum');
			showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
			showtableheader();
			showsetting('rec_search_time', array('starttime', 'endtime'), array($_GET['starttime'], $_GET['endtime']), 'daterange');
			showsubmit('submit');
			showtablefooter();
			showformfooter();
			showtagfooter('div');
		}
	} else {

		showsubmenu('kouei_rec_title');
		showtips('kouei_rec_export_tips');
		if(!empty($_GET['starttime'])) {
			$starttime = strtotime($_GET['starttime']);
		}
		if($_G['adminid'] == 1 && !empty($_GET['endtime']) && $_GET['endtime'] != dgmdate(TIMESTAMP, 'Y-n-j')) {
			$endtime = strtotime($_GET['endtime']);
		} else {
			$endtime = TIMESTAMP;
		}
		if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
			$error = 'prune_mod_range_illegal';
		}
		if($error){
			cpmsg($error,"action=koueirec&operation=search");
		}
		$starttime = $starttime ? $starttime : intval($_GET['starttime'] ? $_GET['starttime'] : 0);
		$endtime = $endtime ? $endtime : intval($_GET['endtime'] ? $_GET['endtime'] : 0);
		if(!$starttime || !$endtime){
			$reccount = C::t('forum_koueitalent_recruit')->count_by_displayorder();
			$multipage = multi($reccount, $_G['setting']['recruitperpage'], $page, ADMINSCRIPT."?action=koueirec&operation=search&submit=yes".$urladd);
			$recresult = C::t('forum_koueitalent_recruit')->fetch_by_pagenum($start_limit, $_G['setting']['recruitperpage']);

			$uids = array();
			foreach ($recresult as $key => $value) {
				$uids[$key] = $value['uid'];
			}
			$recruits = getRecruits($recresult);
		}else{
			if($starttime > $endtime){
				$midtime = $endtime;
				$endtime = $starttime;
				$starttime = $midtime;
			}
			$reccount = C::t('forum_koueitalent_recruit')->count_by_dateline($starttime, $endtime);
			$urladd = '&starttime='.$starttime.'&endtime='.$endtime;
			$multipage = multi($reccount, $_G['setting']['recruitperpage'], $page, ADMINSCRIPT."?action=koueirec&operation=search&submit=yes".$urladd);
			$recresult = C::t('forum_koueitalent_recruit')->fetch_by_dateline_pagenum($starttime, $endtime, $start_limit, $_G['setting']['recruitperpage']);
			$recruits = getRecruits($recresult);
		}
		showformheader("koueirec&operation=clean");
		$condition_str = '&starttime='.$starttime.'&endtime='.$endtime;
		showtableheader(cplang('koueirec_search_result', array('reccount' => $reccount)).'<a href="'.ADMINSCRIPT.'?action=koueirec&operation=search" class="act lightlink normal">'.cplang('research').'</a>&nbsp;&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=koueirec&operation=export'.$condition_str.'">'.$lang['members_search_export'].'</a>');
		if($reccount){
			showsubtitle(array('', 'koueirec_userdata', 'koueirec_compname', 'koueirec_compphone', 'koeuirec_pic', ''));
			echo $recruits;
			showsubmit('deletesubmit', cplang('delete'),($tmpsearch_condition ? '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'recidarray\');if(this.checked){$(\'deleteallinput\').style.display=\'\';}else{$(\'deleteall\').checked = false;$(\'deleteallinput\').style.display=\'none\';}" class="checkbox">'.cplang('select_all') : ''), ' &nbsp;&nbsp;&nbsp;<span id="deleteallinput" style="display:none"><input id="deleteall" type="checkbox" name="deleteall" class="checkbox">'.cplang('koueirec_search_deleteall', array('reccount' => $reccount)).'</span>', $multipage);
		}
		showtablefooter();
		showformfooter();
	}
} elseif($operation == 'clean') {
	if(submitcheck('deletesubmit',1) && empty($_GET['recidarray'])){
		cpmsg('kouei_no_find_delrec', 'action=koueirec&operation=search','error');
	}

	if(!empty($_GET['recidarray'])) {
		$recids = array();
		$recids = $_GET['recidarray'];
		$recnum = 0;
		foreach($recids as $key => $recid) {
			$extra .= '<input type="hidden" name="recarray[]" value="'.$recid.'" />';
			$recnum ++;
		}
	}
	if(!submitcheck('confirmed')){
		// <br /><label><input type="checkbox" name="includethread" value="1" class="checkbox" />'.$lang['koueirec_delete_thread'].'</label>
		cpmsg('koueirec_delete_confirm',"action=koueirec&operation=clean&submit=yes&confirmed=yes", 'form', array('reccount' => $recnum), "$extra",'');
	}else{
		$recarr = array();
		$recarr = $_GET['recarray'];
		$sucnum = 0;
		$delnum = 0;
		foreach ($recarr as $key => $recid) {
			$res = C::t('forum_koueitalent_recruit')->update_displayorder_by_recids($recid);
			if($res){
				$sucnum++;
			}else{
				$delnum++;
			}
		}
		cpmsg('kouei_result_attention',"action=koueirec&operation=search","",array('sucnum' => $sucnum, 'delnum' => $delnum),"",'');
	}
} elseif ($operation == 'export'){
	$lang = lang('forum/talents');
	$starttime = $_GET['starttime'];
	$endtime = $_GET['endtime'];
	if(!$starttime || !$endtime){
		$result = C::t('forum_koueitalent_recruit')->fetch_by_displayorder();
	}else{
		$result = C::t('forum_koueitalent_recruit')->fetch_by_dateline_displayorder($starttime,$endtime);
	}
	if($result){
		foreach($result as $key => $profile){
			unset($profile['tid']);
			unset($profile['displayorder']);
			unset($profile['dateline']);
			unset($profile['rec_mypic']);
			$member = C::t('common_member')->fetch_all_username_by_uid($profile['uid']);
			$member = $member["$profile[uid]"];
			$profile = array_merge(array('username' => $member),$profile);
			foreach ($profile as $key => $value) {
				$value = preg_replace('/\s+/', ' ', $value);
				$value = preg_replace('/,/', "$lang[kouei_cut]", $value);
				$detail .= strlen($value) > 11 && is_numeric($value) ? '['.$value.'],': $value.","; //"新增一个逗号，做分割"
//					$detail .= $value.",";
			}
			$detail = $detail."\n";
		}
		$title = array('username' => $lang['username'], 'rec_id' => $lang['recid'], 'uid' => $lang['userid'], 'rec_infos' => $lang['kouei_recruit_jobinfo'], 'rec_describe' => $lang['kouei_recruit_describe_no'],
			'rec_compname' => $lang['kouei_recruit_company_no'], 'rec_compplace' => $lang['kouei_recruit_workpla_no'],'rec_contact' => $lang['kouei_recruit_contact_no'], 'rec_compphone' => $lang['kouei_recruit_conn_no']);

		foreach($title as $k => $v) {
			$subject .= ($v ? $v : $k).",";
		}
//			echo "<pre>";
//			print_r($detail);
//			echo "</pre>";
		$detail = $subject."\n".$detail;
		$filename = date('Ymd', TIMESTAMP). '.csv';
//			exit();
		ob_end_clean();
		header('Content-Encoding: none');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Pragma: no-cache');
		header('Expires: 0');
		if($_G['charset'] != 'gbk'){
			$detail = diconv($detail, $_G['charset'], 'GBK');
		}
		echo $detail;
		exit();
	}else{
		cpheader();
		cpmsg('kouei_output_fail','action=koueirec&operation=search','error');
	}
}



function getRecruits ($recresult){
	global $_G, $lang;
	$recruits = '';
	if(!empty($recresult)){
		foreach ($recresult as $recruit) {
			$allmember = C::t('common_member')->fetch_all_username_by_uid($recruit['uid']); //此处可能影响性能！！需要注意！！
			$name = $allmember["$recruit[uid]"];
			$recruits .= showtablerow('', array(), array(
				"<input type=\"checkbox\" name=\"recidarray[]\" value=\"$recruit[rec_id]\" class=\"checkbox\">",
				"<a href=\"home.php?mod=space&uid=$recruit[uid]\" target=\"_blank\">$name</a>",
				$recruit['rec_compname'],
				$recruit['rec_compphone'],
				"<a href=\"\data\attachment\album\\$recruit[rec_mypic]\">$lang[logs_click2view]</a>",
				"<a href=\"forum.php?mod=viewthread&tid=$recruit[tid]&extra=\" target=\"_blank\" class=\"act\">$lang[view]</a>"
			), TRUE);
		}
	}
	return $recruits;
}
?>