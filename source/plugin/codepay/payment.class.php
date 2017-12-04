<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
global $_G;

class plugin_codepay {
	function global_usernav_extra3() {	
		global $_G;
		$config = $_G['cache']['plugin']['codepay'];
		if(!$_G['uid'])return;
        return strlen($config['linktitle'])?'<a href="home.php?mod=spacecp&ac=plugin&id=codepay:order&opp=payment&op=credit" style="color:'.$config['linkcolor'].'; "><strong>'.$config['linktitle'].'</strong></a><span class="pipe">|</span>':'';

	}
}
class plugin_codepay_home extends plugin_codepay {
	function spacecp_credit_extra_output() {
		global $_G;
		$config = $_G['cache']['plugin']['codepay'];
        return '&nbsp; <a href="home.php?mod=spacecp&ac=plugin&id=codepay:order&opp=payment&op=credit" class="xi2">'.$config['linktitle'].'&raquo;</a>';

	}
}
?>