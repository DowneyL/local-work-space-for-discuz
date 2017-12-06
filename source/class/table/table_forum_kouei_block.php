<?php 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_kouei_block extends discuz_table
{
	public function __construct() {
		$this->_table = 'forum_kouei_block';
		$this->_pk = 'block_id';

		parent::__construct();
	}

	public function fetch_by_pagenum($limit, $num){
		return DB::fetch_all('SELECT * FROM %t ORDER BY block_id DESC LIMIT %d, %d', array($this->_table, $limit, $num));
	}

	public function fetch_all_id_name(){
		return DB::fetch_all('SELECT block_id, block_name FROM %t ORDER BY block_id DESC', array($this->_table));
	}

	public function fetch_all_by_block_id($blockids, $start = 0, $limit = 0, $tableid = 0) {
		$blockids = dintval($blockids, true);
		if($blockids) {
			return DB::fetch_all("SELECT * FROM %t WHERE block_id IN(%n) ".DB::limit($start, $limit), array($this->_table, (array)$blockids));
		}
		return array();
	}

	public function delete_by_bids($blockids) {
		if(is_array($blockids)) {
			$wheresql = DB::field('block_id', $blockids);
		} else {
			return 0;
		}
		return DB::query("DELETE FROM %t WHERE $wheresql", array($this->_table, $blockids));
	}

	public function update_by_sids($sortids) {
		if(is_array($sortids)) {
			$wheresql = DB::field('sort_id', $sortids);
		} else {
			return 0;
		}
		return DB::query("UPDATE %t SET sort_id = 0 WHERE $wheresql", array($this->_table, $sortids));
	}
}
?>