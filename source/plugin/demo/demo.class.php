<?php  
  
if(!defined('IN_DISCUZ')) {  
    exit('Access Denied');  
}  
  
  
class plugin_demo {  
  
    function __construct(){  
          
    }  
    //全局钩子  
    function common(){  
        global $_G;  
        if($_G['uid']){  
            //经验值加1点  
        }  
    }  
  
    function global_footer(){  
    	return '<b>Hello Plugin!</b>';
    }  
      
}  