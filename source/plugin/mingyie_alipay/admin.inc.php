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
 if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$lang = lang('plugin/mingyie_alipay');

$my_conf = $_G['cache']['plugin']['mingyie_alipay'];

if(empty($my_conf['credit_type'])){
    showmessage($lang['czjfcs'], 'home.php?mod=spacecp&ac=credit');
}

$ec_ratio = $my_conf['ratio'];//兑换比例

//支付方式处理
$pay_bank = unserialize($my_conf['type']);
$pay_banks = array();
foreach($pay_bank as $b){
    $data = array();
    $data['id'] = $b;
    $data['lang'] = $lang[$b];
    $pay_banks[] = $data;
}
$ec_mincredits = $my_conf['mincredits'];//最小充值金额
$ec_maxcredits = $my_conf['maxcredits'];//最大充值金额
$api_type = unserialize($my_conf['type']);//支付方式信息


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
	
	$price = round(($amount * $ec_ratio * 100) / 100, 2);//充值所得积分
	//price
	//extcredits
	$ordeosn = date('Ymd').uniqid();//生成订单号
	$post_time = time();
	
	//生成数据插入数据库
	$sql = 'insert into '.DB::table('myalipay_pay_log').'(uid,price,extcredits,orderid,post_time,status,description,channel,credit_type)values';
	$sql .= "({$_G['uid']},'{$amount}','{$price}','{$ordeosn}','$post_time',0,".$my_conf['credit_type'].",1,'".$pay_type['paytype']."')";
	DB::query($sql);
	
	//插入订单数据
	DB::query("INSERT INTO ".DB::table('forum_order')." (orderid, status, uid, amount, price, submitdate) VALUES ('{$ordeosn}', '1', '$_G[uid]', '{$price}', '$amount', '$_G[timestamp]')");
	
	$money = $amount * 100;
	$return_url= $_G['siteurl'].'source/plugin/mingyie_alipay/payok.php';//通知地址
	$sign = md5(md5($money.$return_url.$ordeosn.'1').$my_conf['key']);
	
	if($pay_type['paytype'] == 'Alipay'){
		//如果值支付宝生成支付跳转支付
		
		$url = "https://www.mingyie.com/api/pay/?order_sn=".$ordeosn."&money=".$money."&return_url=".$return_url."&user_id=".$my_conf['user_id']."&type=".$pay_type['paytype']."&wappc=1&notify_url=".$return_url."&sign=".$sign;
		
		dheader('location: '.$url.'');
		exit;
	}
	
	if($pay_type['paytype'] == 'Weixin'){
		
		
		//如果值微信扫码支付
		$param = array (
		'type' => 'Weixin',//支付别名
		'money' => $money,//支付金额以分为单位
		'return_url' => $return_url,//回调地址(支付成功后会跳转到改地址)
		'notify_url' => $return_url,//异步通知地址(支付成功后会向改地址发送POST处理结果请求)
		'order_sn' => $ordeosn,//商户订单号
		'wappc' => '1',//1为PC2为手机
		'user_id' => $my_conf['user_id'],//铭翼开发中心会员ID
		'sign'=>$sign,//签名
		);
		
		$url = 'https://www.mingyie.com/api/pay/';
		$rets = request_post($url,$param);
		
		$chaxun= $_G['siteurl'].'source/plugin/mingyie_alipay/chaxun.php';//订单查询地址
		
		$def_urla = '<html><head>
    <title>微信扫码支付</title>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <meta name="decorator" content="template_footer" />
    <link rel="stylesheet" type="text/css" href="/source/plugin/mingyie_alipay/template/weixinpay.css" />
  </head><body><div class="wx_header">
        <div class="wx_logo">
            <img src="/source/plugin/mingyie_alipay/template/wxlogo_pay.png" alt="微信支付标志" title="微信支付"></div>
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
			                                请使用微信扫描<br />
			                                                                                                        二维码以完成支付
			                            </p>
			                        </div>
			                    
			                        <div class="msg_box"><i class="icon_wx pngFix"></i>
			                            <p><strong>扫描成功</strong>请在手机确认支付</p>
			                        </div>
			                    </div>
			                </div>
			            </div>

				<div class="wx_hd">
				    <div class="wx_hd_img icon_wx"></div>
				</div>
				<div class="wx_money"><span>￥</span>'.$amount.'元</div>
				<div class="wx_pay">
				    <p><span class="wx_left">支付订单号</span><span class="wx_right">'.$ordeosn.'</span></p>
				</div>
				
				<div class="wx_kf">
				    <div class="wx_kf_img icon_wx"></div>
				    <div class="wx_kf_wz">
				        <p>工作时间：8:00-24:00</p>
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
//$("#HidOrderid").val()  为订单号码
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
						
						    alert("支付成功");location.href="/home.php?mod=spacecp&ac=credit&op=base";
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

