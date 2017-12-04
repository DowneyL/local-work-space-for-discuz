<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search.php 34131 2013-10-17 03:54:09Z andyzheng $
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'test');

require './source/class/class_core.php';

$array = array(
    "name" => "liheng",
    "sex" => "nan",
);

$json = json_encode($_POST);

echo $json;

?>
