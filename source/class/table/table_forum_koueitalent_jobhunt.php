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

class table_forum_koueitalent_jobhunt extends discuz_table
{
	public function __construct() {
		$this->_table = 'forum_koueitalent_jobhunt';
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
			C::t('forum_koueitalent_jobhunt')->delete_by_tids($tids);
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

		public function fetch_by_pagenum($limit, $num){
		return DB::fetch_all('SELECT * FROM %t where displayorder = 1 ORDER BY dateline DESC LIMIT %d, %d', array($this->_table, $limit, $num), $this->_pk);
	}

	public function fetch_by_displayorder(){
		return DB::fetch_all('SELECT * FROM %t where displayorder = 1 ORDER BY dateline DESC', array($this->_table), $this->_pk);
	}

	public function fetch_by_dateline_pagenum($starttime, $endtime, $limit, $num){
		return DB::fetch_all('SELECT * FROM %t WHERE dateline >= %d and dateline <= %d and displayorder = 1 ORDER BY dateline DESC LIMIT %d, %d', array($this->_table, $starttime, $endtime, $limit, $num), $this->_pk);
	}

	public function fetch_by_dateline_displayorder($starttime, $endtime){
		return DB::fetch_all('SELECT * FROM %t WHERE dateline >= %d and dateline <= %d and displayorder = 1 ORDER BY dateline DESC', array($this->_table, $starttime, $endtime), $this->_pk);
	}
	public function count_by_dateline($starttime, $endtime){
		return DB::result_first('SELECT count(*) FROM %t WHERE dateline >= %d and dateline <= %d and displayorder = 1', array($this->_table, $starttime, $endtime), $this->_pk);
	}

	public function update_displayorder_by_jobids($jobid) {
		return DB::query("UPDATE %t SET displayorder = 0 WHERE job_id = %d", array($this->_table, $jobid), $this->_pk);
	}

	public function count_by_displayorder() {
		$count = (int) DB::result_first("SELECT count(*) FROM %t WHERE displayorder = 1", array($this->_table), $this->_pk);
		return $count;
	}
}

?>