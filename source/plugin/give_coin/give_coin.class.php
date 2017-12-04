<?php
if(!defined('IN_DISCUZ')){
	exit('Access Denied');
}

class plugin_give_coin{
	function global_header(){
		global $_G;
		$giveConfig = array();
		$giveConfig = $_G['cache']['plugin']['give_coin'];
		if(intval($giveConfig['status']) == 1 ) {
			if(isset($_POST['regsubmit'])){
				$uid = intval($_G['member']['uid']);
				if($uid){
					$coin_num = intval($giveConfig['coin_num']);
					updatemembercount($uid,array("extcredits4" => $coin_num));
				}
			}
		}
	}
}

class plugin_give_coin_member extends plugin_give_coin{
	function register_input() {
		$lang = lang('plugin/give_coin');
		$bind = "<a href='javascript:void(0);' onClick=\"alert('±ðµãÎÒ');\">".$lang['info']."</a>";
		return $bind;
	}
}
?>