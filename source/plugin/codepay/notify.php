<?php
require '../../class/class_core.php';
require '../../function/function_forum.php';
if (class_exists('C') && method_exists('C', 'app')) {
    $discuz = C::app();
    $discuz->init();
} elseif (class_exists('discuz_core')) {
    $discuz = &discuz_core::instance();
    $discuz->init();
} else {
    exit('error');
}
include_once DISCUZ_ROOT . './source/plugin/codepay/main.inc.php';

$result = notifyCheck($config['codepay_key']);
$result["ok"] = 0;
if ($result["sign"]) {
    $uid = (int)($_GET['pay_id']);
    $orderid = daddslashes($_GET['param']);
    $inTime = time();
    $submitdate = time() - 60 * 86400;
    $order = DB::fetch_first("SELECT * FROM " . DB::table('forum_order') . " WHERE orderid='{$orderid}' and uid='{$uid}'");
    if ($order) {
        $result["ok"] = 1;
        if ($order['status'] == 1) {
            $rs = DB::query("UPDATE " . DB::table('forum_order') . " SET status='2', buyer='codepay.fateqq.com', confirmdate='{$inTime}' WHERE orderid='{$orderid}' and uid='{$uid}' and status='1'");
            if (!DB::affected_rows()) {
                $result["msg"] = 'data error';
                $result["ok"] = false;
            } else {
                updatemembercount($uid, array($config['cextcredit'] => $order['amount']), 1, 'AFD', $uid);
                updatecreditbyaction($action, $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0);
                DB::query("DELETE FROM " . DB::table('forum_order') . " WHERE submitdate<{$submitdate}");
                notification_add($uid, 'credit', 'addfunds', array(
                    'orderid' => $orderid,
                    'price' => (float)$_GET['money'],
                    'value' => $_G['setting']['extcredits'][$config['cextcredit']]['title'] . ' ' . $order['amount'] . ' ' . $_G['setting']['extcredits'][$config['cextcredit']]['unit']
                ), 1);
            }
        } else {
            $result["msg"] = 'ok';
        }
    } else {
        $result["ok"] = false;
        $result["msg"] = 'no this order';
    }
}
if (!empty($_POST)) exit($result["msg"]);
if ($result["ok"]) {
    dheader('location: /forum.php?mod=misc&action=paysucceed');
} else {
    showmessage($lang['order_handle_error'] . $result["msg"], '/home.php?mod=spacecp&ac=credit');
}

