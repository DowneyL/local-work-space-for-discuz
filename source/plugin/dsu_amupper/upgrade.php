<?php
/**
���ƽ������������Դ�롾xlqzm.com���ṩ��������ҵԴ�����¼������Դ�����
�ٷ���վ:xlqzm.com
������ҵ�����http://xqlzm.com/forum-113-1.html
������ҵģ�壺http://xqlzm.com/forum-112-1.html
������ҵԴ�룺http://xqlzm.com/forum-141-1.html
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