<?php
header('Content-type:text/html; charset=utf-8');
$str=<<<HTML
<br /> <br /> <br />文<br />字<br /><br />   
<br />   
<br /><br /><br />
HTML;
echo preg_replace('/<(\/?br.*?)>/si','',$str);