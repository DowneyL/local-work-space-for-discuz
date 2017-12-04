<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
global $_G;
include_once DISCUZ_ROOT.'./source/plugin/codepay/util.func.php';

$lang = lang('plugin/codepay');

$config = _config();

if (empty($config['cextcredit'])) {
    showmessage($lang['credits_addfunds'] . $lang['no_set'], 'home.php?mod=spacecp&ac=credit');
}

$ec_ratio = $config['codepay_ratio'];


$rules_data = getRules($config['rule'], $ec_ratio);