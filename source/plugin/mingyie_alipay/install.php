<?php
/**
 * 翼购开发中心
 * ============================================================================
 * * 版权所有 2016 晋江铭翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.mingyie.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: 天机子 $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF
DROP TABLE IF EXISTS `cdb_myalipay_pay_log`;
CREATE TABLE `cdb_myalipay_pay_log` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `uid` int(8) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `extcredits` int(8) NOT NULL,
  `orderid` varchar(22) NOT NULL,
  `post_time` varchar(10) NOT NULL,
  `status` int(4) NOT NULL,
  `description` int(2) NOT NULL,
  `sysorderid` varchar(40) NOT NULL,
  `completiontime` varchar(10) NOT NULL,
  `channel` int(1) DEFAULT '1',
  `credit_type` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

EOF;

runquery($sql);

$finish = TRUE;

?>
