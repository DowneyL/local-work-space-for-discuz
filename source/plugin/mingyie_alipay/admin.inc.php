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
 if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$lang = lang('plugin/mingyie_alipay');

$my_conf = $_G['cache']['plugin']['mingyie_alipay'];

if(empty($my_conf['credit_type'])){
    showmessage($lang['czjfcs'], 'home.php?mod=spacecp&ac=credit');
}

$ec_ratio = $my_conf['ratio'];//�һ�����

//֧����ʽ����
$pay_bank = unserialize($my_conf['type']);
$pay_banks = array();
foreach($pay_bank as $b){
    $data = array();
    $data['id'] = $b;
    $data['lang'] = $lang[$b];
    $pay_banks[] = $data;
}
$ec_mincredits = $my_conf['mincredits'];//��С��ֵ���
$ec_maxcredits = $my_conf['maxcredits'];//����ֵ���
$api_type = unserialize($my_conf['type']);//֧����ʽ��Ϣ


if(submitcheck('addfundssubmit')) {
	$pay_type = $_POST;
	$amount = intval($pay_type['addfundamount']);
	if(empty($amount)) {
		showmessage('memcp_credits_addfunds_msg_incorrect', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
	}
	
	if($ec_mincredits && $amount < $ec_mincredits) {
		showmessage($lang['zuixiaocz'], '', array('mincredits' => $ec_mincredits), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
	}
	
	if($ec_maxcredits && $amount > $ec_maxcredits) {
		showmessage($lang['zuifacz'], '', array('mincredits' => $ec_maxcredits), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
	}
	
	if(empty($pay_type['paytype'])) {
		showmessage($lang['paffangz'], '', array('mincredits' => $ec_maxcredits), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
	}
	
	$price = round(($amount * $ec_ratio * 100) / 100, 2);//��ֵ���û���
	//price
	//extcredits
	$ordeosn = date('Ymd').uniqid();//���ɶ�����
	$post_time = time();
	
	//�������ݲ������ݿ�
	$sql = 'insert into '.DB::table('myalipay_pay_log').'(uid,price,extcredits,orderid,post_time,status,description,channel,credit_type)values';
	$sql .= "({$_G['uid']},'{$amount}','{$price}','{$ordeosn}','$post_time',0,".$my_conf['credit_type'].",1,'".$pay_type['paytype']."')";
	DB::query($sql);
	
	//���붩������
	DB::query("INSERT INTO ".DB::table('forum_order')." (orderid, status, uid, amount, price, submitdate) VALUES ('{$ordeosn}', '1', '$_G[uid]', '{$price}', '$amount', '$_G[timestamp]')");
	
	$money = $amount * 100;
	$return_url= $_G['siteurl'].'source/plugin/mingyie_alipay/payok.php';//֪ͨ��ַ
	$sign = md5(md5($money.$return_url.$ordeosn.'1').$my_conf['key']);
	
	if($pay_type['paytype'] == 'Alipay'){
		//���ֵ֧��������֧����ת֧��
		
		$url = "https://www.mingyie.com/api/pay/?order_sn=".$ordeosn."&money=".$money."&return_url=".$return_url."&user_id=".$my_conf['user_id']."&type=".$pay_type['paytype']."&wappc=1&notify_url=".$return_url."&sign=".$sign;
		
		dheader('location: '.$url.'');
		exit;
	}
	
	if($pay_type['paytype'] == 'Weixin'){
		
		
		//���ֵ΢��ɨ��֧��
		$param = array (
		'type' => 'Weixin',//֧������
		'money' => $money,//֧������Է�Ϊ��λ
		'return_url' => $return_url,//�ص���ַ(֧���ɹ������ת���ĵ�ַ)
		'notify_url' => $return_url,//�첽֪ͨ��ַ(֧���ɹ������ĵ�ַ����POST����������)
		'order_sn' => $ordeosn,//�̻�������
		'wappc' => '1',//1ΪPC2Ϊ�ֻ�
		'user_id' => $my_conf['user_id'],//���������Ļ�ԱID
		'sign'=>$sign,//ǩ��
		);
		
		$url = 'https://www.mingyie.com/api/pay/';
		$rets = request_post($url,$param);
		
		$chaxun= $_G['siteurl'].'source/plugin/mingyie_alipay/chaxun.php';//������ѯ��ַ
		
		$def_urla = '<html><head>
    <title>΢��ɨ��֧��</title>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <meta name="decorator" content="template_footer" />
    <link rel="stylesheet" type="text/css" href="/source/plugin/mingyie_alipay/template/weixinpay.css" />
  </head><body><div class="wx_header">
        <div class="wx_logo">
            <img src="/source/plugin/mingyie_alipay/template/wxlogo_pay.png" alt="΢��֧����־" title="΢��֧��"></div>
    </div>
			<div align="center" id="qrcode"></div>

			<div class="weixin">
			    <div class="weixin2">
			        <b class="wx_box_corner left pngFix"></b><b class="wx_box_corner right pngFix"></b>
			        <div class="wx_box pngFix">
			            <div class="wx_box_area">
			                <div class="pay_box qr_default">
			                    <div class="area_bd">
			                    <span id="qr_box" class="wx_img_wrapper">
			 
			                        '.$rets.'
			                    
			                    
			                    </span>
			                    
			                        <div class="msg_default_box"><i class="icon_wx pngFix"></i>
			                            <p>
			                                ��ʹ��΢��ɨ��<br />
			                                                                                                        ��ά�������֧��
			                            </p>
			                        </div>
			                    
			                        <div class="msg_box"><i class="icon_wx pngFix"></i>
			                            <p><strong>ɨ��ɹ�</strong>�����ֻ�ȷ��֧��</p>
			                        </div>
			                    </div>
			                </div>
			            </div>

				<div class="wx_hd">
				    <div class="wx_hd_img icon_wx"></div>
				</div>
				<div class="wx_money"><span>��</span>'.$amount.'Ԫ</div>
				<div class="wx_pay">
				    <p><span class="wx_left">֧��������</span><span class="wx_right">'.$ordeosn.'</span></p>
				</div>
				
				<div class="wx_kf">
				    <div class="wx_kf_img icon_wx"></div>
				    <div class="wx_kf_wz">
				        <p>����ʱ�䣺8:00-24:00</p>
				    </div>
				</div>
			            <div class="wx_hd">
			                <div class="wx_hd_img icon_wx"></div>
			            </div>  
			        </div>
			    </div>
			</div>
			
<script type="text/javascript">
$(document).ready(function () {
            setInterval("ajaxstatus()", 3000);    
        });
//$("#HidOrderid").val()  Ϊ��������
        function ajaxstatus() {
                $.ajax({
                    url: "'.$chaxun.'",
                    type: "POST",
					dataType: "json",
					data: {
					    order_sn: "'.$ordeosn.'",
					},
                    success: function (data) {
						
						if(data){
						
						    alert("֧���ɹ�");location.href="/home.php?mod=spacecp&ac=credit&op=base";
						}
						
                    },
                    error: function (jqXHR) {
                        alert(jqXHR.responseText); 
                    }
                });
            
      
        } 
	
</script>
</body></html>';
		
		
		echo $def_urla;
		exit;
		
		
	}
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

