<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
if($operation != 'export'){
	cpheader();
}

require_once libfile('function/delete');


$_G['setting']['jobhuntperpage'] = 5;
$page = max(1, $_G['page']);
$start_limit = ($page - 1) * $_G['setting']['jobhuntperpage'];

// $search_condition = array_merge($_GET, $_POST);
// foreach($search_condition as $k => $v) {
// 	if(in_array($k, array('action', 'operation', 'formhash', 'confirmed', 'submit', 'page', 'deletestart', 'allnum', 'includeuc','includepost','current','pertask','lastprocess','deleteitem')) || $v === '') {
// 		unset($search_condition[$k]);
// 	}
// }

shownav('forum', 'kouei_job_title');

if($operation == 'search'){

	if(!submitcheck('submit',1)){
		if($operation == 'search'){
			showsubmenu('kouei_job_title', array(
				array('search', 'koueijob&operation=search', 1)
			));
			showtips('kouei_job_export_tips');
			echo <<<EOT
	<script type="text/javascript" src="static/js/calendar.js"></script>
	<script type="text/JavaScript">
	function page(number) {
		$('koueijobforum').page.value=number;
		$('koueijobforum').searchsubmit.click();
	}
	</script>
EOT;
			showtagheader('div', 'koueijobposts', !$searchsubmit);
			showformheader("koueijob".($operation ? '&operation='.$operation : ''), '', 'koueijobforum');
			showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
			showtableheader();
			showsetting('job_search_time', array('starttime', 'endtime'), array($_GET['starttime'], $_GET['endtime']), 'daterange');
			showsubmit('submit');
			showtablefooter();
			showformfooter();
			showtagfooter('div');
		}
	} else {

		showsubmenu('kouei_job_title');
		showtips('kouei_job_export_tips');
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
			cpmsg($error,"action=koueijob&operation=search");
		}
		$starttime = $starttime ? $starttime : intval($_GET['starttime'] ? $_GET['starttime'] : 0);
		$endtime = $endtime ? $endtime : intval($_GET['endtime'] ? $_GET['endtime'] : 0);
		if(!$starttime || !$endtime){
			$jobcount = C::t('forum_koueitalent_jobhunt')->count_by_displayorder();
			$multipage = multi($jobcount, $_G['setting']['jobhuntperpage'], $page, ADMINSCRIPT."?action=koueijob&operation=search&submit=yes".$urladd);
			$jobresult = C::t('forum_koueitalent_jobhunt')->fetch_by_pagenum($start_limit, $_G['setting']['jobhuntperpage']);

			$uids = array();
			foreach ($jobresult as $key => $value) {
				$uids[$key] = $value['uid'];
			}
			$jobhunts = getjobhunts($jobresult);
		}else{
			if($starttime > $endtime){
				$midtime = $endtime;
				$endtime = $starttime;
				$starttime = $midtime;
			}
			$jobcount = C::t('forum_koueitalent_jobhunt')->count_by_dateline($starttime, $endtime);
			$urladd = '&starttime='.$starttime.'&endtime='.$endtime;
			$multipage = multi($jobcount, $_G['setting']['jobhuntperpage'], $page, ADMINSCRIPT."?action=koueijob&operation=search&submit=yes".$urladd);
			$jobresult = C::t('forum_koueitalent_jobhunt')->fetch_by_dateline_pagenum($starttime, $endtime, $start_limit, $_G['setting']['jobhuntperpage']);
			$jobhunts = getjobhunts($jobresult);
		}
		showformheader("koueijob&operation=clean");
		$condition_str = '&starttime='.$starttime.'&endtime='.$endtime;
		showtableheader(cplang('koueijob_search_result', array('jobcount' => $jobcount)).'<a href="'.ADMINSCRIPT.'?action=koueijob&operation=search" class="act lightlink normal">'.cplang('research').'</a>&nbsp;&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=koueijob&operation=export'.$condition_str.'">'.$lang['members_search_export'].'</a>');
		if($jobcount){
			showsubtitle(array('', 'koueijob_userdata', 'koueijob_name', 'koueijob_birthday', 'koueijob_gender', 'koueijob_edu','koueijob_exp','koueijob_nature','koueijob_pos','koueijob_dateline',''));
			echo $jobhunts;
			showsubmit('deletesubmit', cplang('delete'),($tmpsearch_condition ? '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'jobidarray\');if(this.checked){$(\'deleteallinput\').style.display=\'\';}else{$(\'deleteall\').checked = false;$(\'deleteallinput\').style.display=\'none\';}" class="checkbox">'.cplang('select_all') : ''), ' &nbsp;&nbsp;&nbsp;<span id="deleteallinput" style="display:none"><input id="deleteall" type="checkbox" name="deleteall" class="checkbox">'.cplang('koueijob_search_deleteall', array('jobcount' => $jobcount)).'</span>', $multipage);
			}
		showtablefooter();
		showformfooter();	
	}
} elseif($operation == 'clean') {
	if(submitcheck('deletesubmit',1) && empty($_GET['jobidarray'])){
		cpmsg('kouei_no_find_deljob', 'action=koueijob&operation=search','error');
	}

	if(!empty($_GET['jobidarray'])) {
		$jobids = array();
		$jobids = $_GET['jobidarray'];
		$jobnum = 0;
		foreach($jobids as $key => $jobid) {
				$extra .= '<input type="hidden" name="jobarray[]" value="'.$jobid.'" />';
				$jobnum ++;
		}
	} 
	if(!submitcheck('confirmed')){
		// <br /><label><input type="checkbox" name="includethread" value="1" class="checkbox" />'.$lang['koueijob_delete_thread'].'</label>
		cpmsg('koueijob_delete_confirm',"action=koueijob&operation=clean&submit=yes&confirmed=yes", 'form', array('jobcount' => $jobnum), "$extra",'');
	}else{
		$jobarr = array();
		$jobarr = $_GET['jobarray'];
		$sucnum = 0;
		$delnum = 0;
		foreach ($jobarr as $key => $jobid) {
			$res = C::t('forum_koueitalent_jobhunt')->update_displayorder_by_jobids($jobid);
			if($res){
				$sucnum++;
			}else{
				$delnum++;
			}
		}
		cpmsg('kouei_result_attention',"action=koueijob&operation=search","",array('sucnum' => $sucnum, 'delnum' => $delnum),"",'');
	} 
} elseif ($operation == 'export'){
		$lang = lang('forum/talents');
		$starttime = $_GET['starttime'];
		$endtime = $_GET['endtime'];
		if(!$starttime || !$endtime){
			$result = C::t('forum_koueitalent_jobhunt')->fetch_by_displayorder();
		}else{
			$result = C::t('forum_koueitalent_jobhunt')->fetch_by_dateline_displayorder($starttime,$endtime);
		}
		if($result){
			foreach($result as $key => $profile){
				$exp = C::t('forum_koueitalent_jobhunt_explog')->fetch_explog_by_tid($profile['tid']);
				if(!empty($exp[0])){
					$explog = $exp[0];
				}else{
					$explog = array('job_exp' => '');
				}
				unset($profile['tid']);
				unset($profile['displayorder']);
				unset($profile['dateline']);
				unset($profile['job_mypic']);
				$member = C::t('common_member')->fetch_all_username_by_uid($profile['uid']);
				$member = $member["$profile[uid]"];
				$profile = array_merge(array('username' => $member),$profile) + $explog;
				foreach ($profile as $key => $value) {
					$value = preg_replace('/\s+/', ' ', $value);
					$value = preg_replace('/,/', "$lang[kouei_cut]", $value);
					$detail .= strlen($value) > 11 && is_numeric($value) ? '['.$value.']': $value.",";
				}
				$detail = $detail."\n";
			}

			$title = array('username' => $lang['username'], 'job_id' => $lang['jobid'], 'uid' => $lang['userid'], 
				           'job_realname' => $lang['kouei_jobhunt_realname_no'],'job_DOB' => $lang['kouei_jobhunt_DOB_no'],
				           'job_sex' => $lang['kouei_jobhunt_sex_no'],'job_mar' => $lang['kouei_jobhunt_mar_no'],
				           'job_edu' => $lang['kouei_edu_no'],'job_sch' => $lang['kouei_jobhunt_sch_no'],
				           'job_myqq' => $lang['kouei_jobhunt_qq_no'],'job_phonenum' => $lang['kouei_jobhunt_phone_no'],
				           'job_email' => $lang['kouei_jobhunt_email_no'],'job_expyear' => $lang['kouei_jobhunt_exp_no'],
				           'job_nature' => $lang['kouei_jobhunt_nature_no'],'job_pos' => $lang['kouei_jobhunt_pos_no'],
				           'job_treatment' => $lang['kouei_jobhunt_treatment_no'],'job_workpos' => $lang['kouei_jobhunt_workpos_no'],
				           'job_days' => $lang['kouei_jobhunt_days_no'],'job_self' => $lang['kouei_jobhunt_workexp_2'],'job_exp' => $lang['kouei_jobhunt_workexp_1'],);

			foreach($title as $k => $v) {
			$subject .= ($v ? $v : $k).",";
			}

			$detail = $subject."\n".$detail;
			$filename = date('Ymd', TIMESTAMP). '.csv';
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
			cpmsg('kouei_output_fail','action=koueijob&operation=search','error');
		}
}


function getjobhunts ($jobresult){
	global $_G, $lang;
	if(!empty($jobresult)){
		foreach ($jobresult as $jobhunt) {
		$allmember = C::t('common_member')->fetch_all_username_by_uid($jobhunt['uid']); //此处可能影响性能！！需要注意！！
		$name = $allmember["$jobhunt[uid]"];
		$jobhunts .= showtablerow('', array(), array(
			"<input type=\"checkbox\" name=\"jobidarray[]\" value=\"$jobhunt[job_id]\" class=\"checkbox\">",
			"<a href=\"home.php?mod=space&uid=$jobhunt[uid]\" target=\"_blank\">$name</a>",
			$jobhunt['job_realname'],
			$jobhunt['job_DOB'],
			$jobhunt['job_sex'],
			$jobhunt['job_edu'],
			$jobhunt['job_expyear'],
			$jobhunt['job_nature'],
			$jobhunt['job_pos'],
			date('Y-m-d H:i:s',$jobhunt['dateline']),
			"<a href=\"forum.php?mod=viewthread&tid=$jobhunt[tid]&extra=\" target=\"_blank\" class=\"act\">$lang[view]</a>"
			), TRUE);
		}
	}
	return $jobhunts;
}
?>