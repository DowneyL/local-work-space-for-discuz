<?php  
if(!defined('IN_DISCUZ')) {  
    exit('Access Denied');  
}  
  
  
//各种反安装操作，恢复安装时的修改  
$sql = "show tables";  
runquery($sql);  
  
  
$finish = TRUE;  
  
?>  