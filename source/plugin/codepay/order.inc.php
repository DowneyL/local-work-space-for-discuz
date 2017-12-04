<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

include_once DISCUZ_ROOT.'./source/plugin/codepay/main.inc.php';
$pay_bank = unserialize($config['type']);
$pay_banks = array();
foreach ($pay_bank as $key => $value) {
    if($value=='wechat'){
        $pay_banks['wechat'] = 3;
    }else if($value=='qqpay'){
        $pay_banks['qqpay'] = 2;
    }else{
        $pay_banks['alipay'] = 1;
    }
}
if($_GET['mobile']){
    include template('codepay:mobile');
    exit();
}

?>