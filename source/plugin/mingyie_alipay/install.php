<?php
/**
 * ����������
 * ============================================================================
 * * ��Ȩ���� 2016 ������������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.mingyie.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Author: ����� $
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
