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
header("Content-type: text/html; charset=gbk");
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
$lang = lang('plugin/mingyie_alipay');
loadcache('plugin');
$my_conf = $_G['cache']['plugin']['mingyie_alipay'];

$sign = md5(md5($my_conf['user_id'].$post['order_sn']).$my_conf['key']);

$data = array (
  'order_sn' => $post['order_sn'],//商户订单号
  'user_id' => $my_conf['user_id'],//铭翼开发中心会员ID
  'sign'=>$sign,//签名
);
$url = 'https://www.mingyie.com/api/pay/chaxun/';

$json = request_post($url,$data);
$rest = json_decode($json,true);
if($rest['code'] == 10106){
	
	echo 1;
	exit;;
}


function request_post($url,$param) {
        if (empty($url) || empty($param)) {
            return false;
        }
        
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        
        return $data;
}

