<?php  
  
if(!defined('IN_DISCUZ')) {  
    exit('Access Denied');  
}  
  
  
class plugin_demo {  
  
    function __construct(){  
          
    }  
    //ȫ�ֹ���  
    function common(){  
        global $_G;  
        if($_G['uid']){  
            //����ֵ��1��  
        }  
    }  
  
    function global_footer(){  
    	return '<b>Hello Plugin!</b>';
    }  
      
}  