<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


function getKoueiTableContent($stat){
	if(!empty($stat)){
	$lang = lang('forum/talents');
	$message_talents = '';
	$message_talents = "<table border='1px'>".
		   "<tr><td colspan='2' align='center'><strong>".$lang['kouei_jobhunt_message']."</strong></td></tr>".
		   "<tr><td width='100px'>".$lang['kouei_jobhunt_realname_no']."</td><td width='300px'>".$stat['username']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_DOB_no']."</td><td>".$stat['barthdate']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_sex_no']."</td><td>".$stat['gender']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_mar_no']."</td><td>".$stat['wedding']."</td></tr>".
		   "<tr><td>".$lang['kouei_edu_no']."</td><td>".$stat['school_info']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_sch_no']."</td><td>".$stat['endschool']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_qq_no']."</td><td>".$stat['qqnum']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_phone_no']."</td><td>".$stat['phonenum']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_email_no']."</td><td>".$stat['emails']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_exp_no']."</td><td>".$stat['workexperience']."</td></tr>".
		   "<tr><td colspan='2' align='center'><strong>".$lang['kouei_jobhunt_want']."</strong></td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_nature_no']."</td><td>".$stat['workxz']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_pos_no']."</td><td>".$stat['qwposition']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_treatment_no']."</td><td>".$stat['daiyu']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_workpos_no']."</td><td>".$stat['workplace']."</td></tr>".
		   "<tr><td>".$lang['kouei_jobhunt_days_no']."</td><td>".$stat['dgsj']."</td></tr>";
	}
	return $message_talents;
}

function getKoueiTableExp($stat,$infos_str){
if(!empty($stat)){
	$lang = lang('forum/talents');
	$message = '';
	$message = "<tr><td colspan='2' align='center'><strong>".$lang['kouei_jobhunt_workexp_1']."</strong></td></tr>".
			   "<tr><td>".$lang['kouei_jobhunt_workexp_1']."</td><td>".$infos_str."</td></tr>";
}
	return $message;
}

function getKoueiTableSelf($stat){
if(!empty($stat)){
	$lang = lang('forum/talents');
	$message = '';
	$message = "<tr><td colspan='2' align='center'><strong>".$lang['kouei_jobhunt_workexp_2']."</strong></td></tr>".
			   "<tr><td>".$lang['kouei_jobhunt_workexp_2']."</td><td>".$stat['selfeval']."</td></tr>";	
}
	return $message;
}

function getKoueiTableFoot(){
	$message = '';
	$message = "</table>";
	return $message;
}

?>