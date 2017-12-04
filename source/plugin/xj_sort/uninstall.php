<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$plugin_xj_sort_type = DB::table('xj_sort_type');
$plugin_xj_sort_class = DB::table('xj_sort_class');

$sql = <<<EOF

DROP TABLE $plugin_xj_sort_type;
DROP TABLE $plugin_xj_sort_class;

EOF;

runquery($sql);

$finish = TRUE;

?>