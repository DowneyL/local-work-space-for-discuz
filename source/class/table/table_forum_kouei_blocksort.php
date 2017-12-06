<?php 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_kouei_blocksort extends discuz_table
{
	public function __construct() {
		$this->_table = 'forum_kouei_blocksort';
		$this->_pk = 'sort_id';

		parent::__construct();
	}

	public function fetch_by_pagenum($limit, $num){
		return DB::fetch_all('SELECT * FROM %t ORDER BY order_id ASC LIMIT %d, %d', array($this->_table, $limit, $num));
	}

	public function fetch_all_id_name(){
		return DB::fetch_all('SELECT sort_id, sort_name FROM %t ORDER BY order_id ASC', array($this->_table));
	}

	public function delete_by_sids($sortids) {
		if(is_array($sortids)) {
			$wheresql = DB::field('sort_id', $sortids);
		} else {
			return 0;
		}
		return DB::query("DELETE FROM %t WHERE $wheresql", array($this->_table, $sortids));
	}
}

?>