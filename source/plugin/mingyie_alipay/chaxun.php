<?php
/**
 * ����������
 * ============================================================================
 * * ��Ȩ���� 2016 ������������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.mingyie.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Author: ����� $
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
  'order_sn' => $post['order_sn'],//�̻�������
  'user_id' => $my_conf['user_id'],//���������Ļ�ԱID
  'sign'=>$sign,//ǩ��
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
        $ch = curl_init();//��ʼ��curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//ץȡָ����ҳ
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // ����֤֤����Դ�ļ��
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // ��֤���м��SSL�����㷨�Ƿ����
        curl_setopt($ch, CURLOPT_HEADER, 0);//����header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//Ҫ����Ϊ�ַ������������Ļ��
        curl_setopt($ch, CURLOPT_POST, 1);//post�ύ��ʽ
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//����curl
        curl_close($ch);
        
        return $data;
}

