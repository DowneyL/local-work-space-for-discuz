<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = lang('forum/talents');
$actiontitle = $lang['kouei_talents_select_title'];
$recruitinfo = $lang['kouei_recruit_title'];
$formhash = FORMHASH;
include template('forum/forum_kouei_recruit');
?>