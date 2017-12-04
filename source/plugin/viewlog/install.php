<?php
/*
 * 主页：http://addon.discuz.com/?@ailab
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
 
if(!defined('IN_DISCUZ')) { 
	exit('Access Denied');
}
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS `pre_viewlog` (
  `logid` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `ip` char(15) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL default '0', 
  PRIMARY KEY  (`logid`)
) ENGINE=MyISAM;
EOF;
runquery($sql);
$finish = TRUE;

?>