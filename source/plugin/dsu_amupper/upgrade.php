<?php
/**
此破解程序由星期六源码【xlqzm.com】提供，更多商业源码请登录星期六源码官网
官方网站:xlqzm.com
更多商业插件：http://xqlzm.com/forum-113-1.html
更多商业模板：http://xqlzm.com/forum-112-1.html
更多商业源码：http://xqlzm.com/forum-141-1.html
**/
$sql = <<<EOF
CREATE TABLE  pre_plugin_dsuamupper (
`uid` MEDIUMINT( 8 ) UNSIGNED NOT NULL ,
`uname` CHAR( 15 ) CHARACTER SET gbk COLLATE gbk_chinese_ci NOT NULL ,
`addup` MEDIUMINT( 10 ) UNSIGNED NOT NULL ,
`cons` MEDIUMINT( 8 ) UNSIGNED NOT NULL ,
`lasttime` INT( 10 ) UNSIGNED NOT NULL ,
`time` INT( 10 ) UNSIGNED NOT NULL ,
`allow` MEDIUMINT( 8 ) UNSIGNED NOT NULL ,
UNIQUE (
`uid`
)
) ENGINE = MYISAM ;

CREATE TABLE  pre_plugin_dsuampperc (
`id` MEDIUMINT( 8 ) UNSIGNED NOT NULL ,
`days` MEDIUMINT( 8 ) UNSIGNED NOT NULL ,
`usergid` SMALLINT( 6 ) UNSIGNED NOT NULL ,
`extcredits` TINYINT( 3 ) UNSIGNED NOT NULL ,
`reward` MEDIUMINT( 8 ) UNSIGNED NOT NULL
UNIQUE (
`id`
)
) ENGINE = MYISAM ;

EOF;








if(file_exists(DISCUZ_ROOT.'./data/plugindata/dsu_amupper.lang.php')) {	
	unlink(DISCUZ_ROOT.'./data/plugindata/dsu_amupper.lang.php');
} 


$amuppertable = DB::table('plugin_dsuamupper');
$query = DB::query("SHOW TABLES LIKE '$amuppertable'");
$amuppertable_exist = 0;
if(DB::num_rows($query) > 0){
	$amuppertable_exist = 1;
}else{
	runquery($sql);
}


	$finish = TRUE;



?>