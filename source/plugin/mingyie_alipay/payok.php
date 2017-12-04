<?php
/**
 * 翼购开发中心
 * ============================================================================
 * * 版权所有 2016 晋江铭翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.mingyie.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: 天机子 $
 */

require '../../class/class_core.php';
require '../../function/function_forum.php';
$has_c = false;
if(class_exists('C') && method_exists('C','app')){
    $discuz = C::app();
    $discuz->init();
    $has_c = true;
}elseif(class_exists('discuz_core')){
    $discuz = & discuz_core::instance();
    $discuz->init();

}else{
    die();
}

if(!$_POST){
	
	dheader('location: /home.php?mod=spacecp&ac=credit&op=base');
	exit;
}

$post = $_POST;
$lang = lang('plugin/mingyi_alipay');
loadcache('plugin');
$my_conf = $_G['cache']['plugin']['mingyi_alipay'];

$order = DB::fetch_first("SELECT * FROM ".DB::table('myalipay_pay_log')." WHERE orderid='".$post['order_sn']."'");


if($post['money'] !== $order['price']){
	exit($lang['moneybf']);
	
}

if($order['status']){
	exit($lang['orderzf']);
}

//验证签名是否正确
$signa = md5(md5($order['orderid'].$order['price'].$post['trade_no'].$my_conf['user_id']).$my_conf['key']);


if($post['sign'] !== $signa){
	exit($lang['qianmerror']);
}

//$post['trade_status']返回值为10000表示支付成功
if($post['trade_status'] == 10000){
	$completiontime=time();
	$IsSuccess=false;
	
	$IsSuccess=DB::query("update ".DB::table('myalipay_pay_log')." set status=1,sysorderid='".$post['trade_no']."',completiontime='".$completiontime."' WHERE orderid='".$order['orderid']."' AND status!=1 limit 1");
    
	if($IsSuccess){
		DB::query("UPDATE ".DB::table('forum_order')." SET status='2', buyer='mingyicom', confirmdate='$_G[timestamp]' WHERE orderid='".$order['orderid']."'");
		updatemembercount($order['uid'], array($order['description'] => $order['extcredits']), 1, 'AFD', $order['uid']);
		updatecreditbyaction($action, $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0);
		DB::query("DELETE FROM ".DB::table('forum_order')." WHERE submitdate<'$_G[timestamp]'-60*86400");
		$submitdate = dgmdate($order['submitdate']);
		$confirmdate = dgmdate(TIMESTAMP);
		
	}
	
}else{
	exit($lang['weizhicw']);
}






