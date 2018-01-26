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

	public function fetch_all_block_id(){
	    return DB::fetch_all('SELECT block_id FROM %t', array($this->_table));
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

	public function sort_by_bs_order_id($order = '', $block = '') {
		$order ? $ordersql = ' ORDER BY '. $order .' DESC' : $ordersql = '';
		$block ? $wheresql = " WHERE block_name LIKE %s" : $wheresql = '';
		if ($block) {
			return DB::fetch_all("SELECT block.*, blocksort.sort_name, blocksort.order_id, t2.count FROM %t AS block 
				   LEFT JOIN ".DB::table('forum_kouei_blocksort')." AS blocksort ON block.sort_id = blocksort.sort_id
				   LEFT JOIN (select block_id, count(*) as count from ".DB::table('forum_kouei_blockitem')." 
				   group by block_id) t2 on block.block_id = t2.block_id".$wheresql.$ordersql, array($this->_table, '%'.$block.'%'));
		}
		return DB::fetch_all("SELECT block.*, blocksort.sort_name, blocksort.order_id, t2.count FROM %t AS block 
			   LEFT JOIN ".DB::table('forum_kouei_blocksort')." AS blocksort ON block.sort_id = blocksort.sort_id
			   LEFT JOIN (select block_id, count(*) as count from ".DB::table('forum_kouei_blockitem')." 
			   group by block_id) t2 on block.block_id = t2.block_id".$wheresql.$ordersql, array($this->_table));
	}
}
?>