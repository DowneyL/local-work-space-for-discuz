<?php 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_kouei_blockitem extends discuz_table
{
	public function __construct() {
		$this->_table = 'forum_kouei_blockitem';
		$this->_pk = '';

		parent::__construct();
	}

	public function select($userid = 0, $blockid = 0, $idtype = '', $orderfield = '', $ordertype = 'DESC', $limit = 0, $count = 0, $blockidglue = '=', $returnnum = 0) {
		$data = self::make_where($userid, $blockid, $idtype, $blockidglue);
		$ordersql = $limitsql = '';
		if($orderfield) {
			$ordersql = ' ORDER BY '.DB::order($orderfield, $ordertype);
		}
		if($limit) {
			$limitsql = DB::limit($limit, $count);
		}
		if($data) {
			if($returnnum) {
				return DB::result_first('SELECT count(*) FROM %t WHERE '.$data['where'], $data['data']);
			}
			return DB::fetch_all('SELECT block_id FROM %t WHERE '.$data['where'].$ordersql.$limitsql, $data['data']);
		} else {
			return false;
		}
	}

	public function fetch_by_uid_bid($userid, $blockid) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE user_id=%d AND block_id=%s", array($this->_table, $userid, $blockid));
	}	

	public function delete_by_uid_bid($userid, $blockid) {
		return DB::query("DELETE FROM %t WHERE user_id=%d AND block_id=%s", array($this->_table, $userid, $blockid));
	}

	public function delete_by_bids($blockids) {
		if(is_array($blockids)) {
			$wheresql = DB::field('block_id', $blockids);
		} else {
			return 0;
		}
		return DB::query("DELETE FROM %t WHERE $wheresql", array($this->_table, $blockids));
	}

	public function sort_by_bid () {
		return DB::fetch_all("SELECT block_id,count(*) count FROM %t GROUP BY block_id ORDER BY count DESC", array($this->_table));
	}

	private function make_where($userid = 0, $blockid = 0, $idtype = '', $blockidglue = '=') {
		$wheresql = ' 1';
		$data = array();
		$data['data'][] = $this->_table;
		if($userid) {
			$wheresql .= !is_array($userid) ? " AND user_id=%d" : " AND user_id IN (%n)";
			$data['data'][] = $userid;
		}
		if($blockid) {
			$wheresql .= !is_array($blockid) ? " AND ".DB::field('block_id', $blockid, $blockidglue) : " AND ".DB::field('block_id', $blockid);
		}
		if($idtype) {
			$wheresql .= " AND idtype=%s";
			$data['data'][] = $idtype;
		}
		if($wheresql == ' 1') {
			return false;
		}
		$data['where'] = $wheresql;
		return $data;
	}
}

?>