<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
include_once DISCUZ_ROOT . './source/plugin/codepay/main.inc.php';
if (!$_G['uid']) showmessage('not login');
$type = (int)$_POST['type'];
$money = (float)$_POST['money'];
$need = (int)$_POST['addfundamount'];

if (empty($config['codepay_id']) || empty($config['codepay_key'])) showmessage($lang['no_id'], '/admin.php?frames=yes&action=plugins');

if ($type < 1) $type = 1;

if ($need < 1) $need = 1;

if ($need < (int)$config['codepay_mincredits'] || ($need > 0 && $config['codepay_maxcredits'] && $need > $config['codepay_maxcredits'])) {
    showmessage('codepay:amount_invalid', '', array('codepay_maxcredits' => $config['codepay_maxcredits'], 'codepay_mincredits' => $config['codepay_mincredits']));
}
$pay_bank = unserialize($config['type']);
foreach ($pay_bank as $key => $value) {
    if($value=='wechat'){
        $pay_banks['3'] = 3;
    }else if($value=='qqpay'){
        $pay_banks['2'] = 2;
    }else{
        $pay_banks['1'] = 1;
    }
}
if (!$pay_banks[(string)$type]) showmessage($lang['pay_channelname'] . $lang['not_on'], 'home.php?mod=spacecp&ac=credit');

$needMoney = (float)$rules_data[(string)$need];

if (!$config['money_on'] && $needMoney <= 0 && $need > 0) showmessage($lang['not_money_error'], 'home.php?mod=spacecp&ac=credit');

if ($needMoney <= 0) $needMoney = round(ceil(($need / $ec_ratio) * 100) / 100, 2);

if ($needMoney <= 0) showmessage('not money', 'home.php?mod=spacecp&ac=credit');

$orderid = date('Ymd') . uniqid();


$creatTime = time();

DB::query("INSERT INTO " . DB::table('forum_order') . " (orderid, status, uid, amount, price, submitdate) VALUES ('{$orderid}', '1', '{$_G['uid']}', '{$need}', '{$needMoney}', '{$creatTime}')");
if (!DB::affected_rows()) showmessage($lang['order_err']);
$config['act'] = (int)$config['act'] == 1 ? 0 : 1;
$return_url = $_G['siteurl'] . 'source/plugin/codepay/notify.php';
$parameter = array(
    "id" => (int)trim($config['codepay_id']),
    "type" => $type,
    "price" => $needMoney,
    "pay_id" => $_G['uid'],
    "param" => $orderid,
    "act" => $config['act'],
    "outTime" => 360,
    "debug" => 1,
    "page" => 1,
    "chart" => CHARSET,
    "return_url" => $return_url,
    "notify_url" => $return_url
);

ksort($parameter);
reset($parameter);

$param = '';
$sign = '';

foreach ($parameter AS $key => $val) {
    if ($val == '') continue;
    $param .= "$key=" . urlencode($val) . "&";
    $sign .= "$key=$val&";
}

$param = substr($param, 0, -1);
$sign = substr($sign, 0, -1) . $config['codepay_key'];
$codepay_frame_url = 'http://codepay.fateqq.com:52888/creat_order/?' . $param . '&sign=' . md5($sign);


if($_GET['mobile']){
    include template('codepay:pay');

}else{
    echo '<iframe src="' . $codepay_frame_url . '" width="100%" height="100%" frameborder="no" border="0" scrolling="no" allowtransparency="yes"></iframe>';
}
exit(0);
?>