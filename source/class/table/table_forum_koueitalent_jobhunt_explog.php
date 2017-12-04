<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_order.php 29009 2012-03-22 07:37:36Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_koueitalent_jobhunt_explog extends discuz_table
{
	public function __construct() {
		$this->_table = 'forum_koueitalent_jobhunt_explog';
		$this->_pk = 'rid';

		parent::__construct();
	}

	public function fetch($tid, $tableid = 0) {
		$tid = intval($tid);
		$data = array();
		if($tid && ($data = $this->fetch_cache($tid)) === false) {
			$parameter = array($this->_table, $tid);
			$data = DB::fetch_first("SELECT * FROM %t WHERE tid=%d", $parameter);
			if(!empty($data)) $this->store_cache($tid, $data, $this->_cache_ttl);
		}
		return $data;
	}

	public function delete_by_tid($tids, $unbuffered = false, $tableid = 0, $limit = 0) {
		$tids = dintval($tids, true);
		if($tids) {
			$this->clear_cache($tids);
			C::t('forum_koueitalent_jobhunt_explog')->delete_by_tids($tids);
			return DB::delete($this->_table, DB::field('tid', $tids), $limit, $unbuffered);
		}
		return !$unbuffered ? 0 : false;
	}
	public function delete($tids, $unbuffered = false, $tableid = 0, $limit = 0) {
		return $this->delete_by_tid($tids, $unbuffered, $tableid, $limit);
	}

	public function delete_by_tids($tids) {
		return DB::delete($this->_table, DB::field('tid', $tids));
	}

	public function fetch_explog_by_tid($tid){
		return DB::fetch_all('SELECT job_exp FROM %t WHERE tid = %d', array($this->_table, $tid), $this->_pk);
	}
}

?>