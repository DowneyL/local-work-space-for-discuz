<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$plugin_xj_sort_type = DB::table('xj_sort_type');
$plugin_xj_sort_class = DB::table('xj_sort_class');




if($_GET['fromversion']=='1.1'){
$sql = <<<EOF
ALTER TABLE $plugin_xj_sort_class ADD `classoption` smallint(2) NOT NULL;
ALTER TABLE $plugin_xj_sort_class ADD `classorder` mediumint(4) NOT NULL;
ALTER TABLE $plugin_xj_sort_class ADD `classmust` smallint(1) NOT NULL;
EOF;
}

if($_GET['fromversion']=='1.2'){
$sql = <<<EOF
ALTER TABLE $plugin_xj_sort_class ADD `classorder` mediumint(4) NOT NULL;
ALTER TABLE $plugin_xj_sort_class ADD `classmust` smallint(1) NOT NULL;
EOF;
}




runquery($sql);

$finish = TRUE;

?>