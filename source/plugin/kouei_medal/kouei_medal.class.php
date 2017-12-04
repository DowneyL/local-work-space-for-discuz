<?php
  
if(!defined('IN_DISCUZ')) {  
    exit('Access Denied');  
}  
  
class plugin_kouei_medal {  
  	function global_header(){
  	global $_G;
  	$medalConfig = array();
  	$medalConfig = $_G['cache']['plugin']['kouei_medal'];
    $password = $medalConfig['medal_password'];
    $start_id = $medalConfig['medal_start'];
    $finish_id = $medalConfig['medal_finish'];
    if($_GET['kouei_medal_flag'] && $_GET['kouei_medal_flag'] == $password){
      if(intval($medalConfig['kouei_switch']) == 1){
          if(intval($medalConfig['kouei_year']) == 1){
             $query = DB::query("SELECT * FROM ".DB::table('common_member_field_forum')." order by uid");
             $mysort = array();
             while($row = DB::fetch($query)){
                $mysort[] = $row;
             }
            for($i=0; $i<count($mysort); $i++){
             $medad = $mysort[$i]['medals'];
             $uid = $mysort[$i]['uid'];
               if(!preg_match("/\t/",$medad)){
                  if($medad>=$start_id && $medad<=$finish_id){
                    $medad+=1;
                    $query = DB::query("update ".DB::table('common_member_field_forum')." set medals =".$medad." where uid=".$uid);
                  }
               }else{
                  $medad = explode("\t",$medad);
                  for($j=0; $j<count($medad); $j++){
                    if($medad[$j]>=$start_id &&$medad[$j]<=$finish_id){
                      $medad[$j]+=1;
                    }
                  }
                  $medad = implode("\t",$medad);
                  C::t('common_member_field_forum')->update($uid,array('medals' => $medad));
              }
            }
          }
        }
      }
  	}
}