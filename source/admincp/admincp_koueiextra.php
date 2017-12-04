<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
cpheader();
$setting = C::t('common_setting')->fetch_all(null);
//echo "<pre>";
//print_r($setting);
//echo "</pre>";
//showtips('kouei_rec_export_tips');
if($_GET['operation'] == 'base'){
showtips('kouei_extra_tips');
showtagheader('div', 'koueiextra', !$searchsubmit);
showformheader("koueiextra".'&operation=koueimobilevalidate', '', 'cpform');
showtableheader();
showsetting('kouei_mobile_validate', 'settingnew[koueimobilevalidate]', $setting['koueimobilevalidate'], 'textarea');
showsubmit('submit');
showtablefooter();
showformfooter();
showtagfooter('div');
}

if($_GET['operation'] == 'koueimobilevalidate'){
    if (!empty($_POST['settingnew']['koueimobilevalidate'])) {
        require_once libfile('function/cache');
        $koueimobilevalidate = $_POST['settingnew']['koueimobilevalidate'];
        $result = C::t('common_setting')->update('koueimobilevalidate', $koueimobilevalidate);
        updatecache('koueimobilevalidate');
        if ($result) {
            cpmsg('kouei_set_mobile_validate_success', 'action=koueiextra&operation=base', 'succeed');
        } else {
            cpmsg('kouei_set_mobile_validate_error', 'action=koueiextra&operation=base', 'error');
        }
    }
}
