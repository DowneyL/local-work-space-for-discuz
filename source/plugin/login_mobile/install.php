<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}





$sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('mobile_login_connection') . "` (" .
  "`phone` bigint(20) unsigned NOT NULL," .
  "`username` char(15) NOT NULL DEFAULT ''," .
  "`addtime` int(10) unsigned NOT NULL DEFAULT '0',".
  "PRIMARY KEY (`phone`)," .
  "UNIQUE KEY (`username`)".
") ENGINE=InnoDB DEFAULT CHARSET=utf8";
runquery($sql);




$sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('mobile_login_seccode') . "` (" .
  "`phone` bigint(20) unsigned NOT NULL COMMENT '手机号',".
  "`seccode` varchar(4) NOT NULL DEFAULT '0000' COMMENT '验证码',".
  "`expire` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间戳',".
  "PRIMARY KEY (`phone`)".
") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手机验证码表';";
runquery($sql);





$sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('mobile_login_sms') . "` (" .
  "`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,".
  "`phone` bigint(20) unsigned NOT NULL DEFAULT '0',".
  "`msg` varchar(256) NOT NULL DEFAULT '',".
  "`sendtime` int(10) unsigned NOT NULL DEFAULT '0',".
  "PRIMARY KEY (`id`)".
") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信发送记录';";
runquery($sql);

$finish = TRUE;
?>
