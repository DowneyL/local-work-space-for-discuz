<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$plugin_xj_sort_type = DB::table('xj_sort_type');
$plugin_xj_sort_class = DB::table('xj_sort_class');

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS $plugin_xj_sort_type (
  `tid` int(8) NOT NULL,
  `classid` mediumint(4) NOT NULL,
  `parentid` mediumint(4) NOT NULL,
  KEY `tid` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

CREATE TABLE IF NOT EXISTS $plugin_xj_sort_class (
  `classid` mediumint(4) NOT NULL auto_increment,
  `parent` mediumint(4) NOT NULL,
  `classname` varchar(80) NOT NULL,
  `classoption` smallint(2) NOT NULL,
  `classorder` mediumint(4) NOT NULL,
  `classmust` smallint(1) NOT NULL,
  PRIMARY KEY  (`classid`),
  KEY `classorder` (`classorder`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=gbk AUTO_INCREMENT=1 ;

EOF;

runquery($sql);

$finish = TRUE;

?>