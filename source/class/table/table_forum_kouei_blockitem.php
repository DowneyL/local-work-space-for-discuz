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


	public function select_log_by_uid($limit = null, $num = null) {
		$limitsql = '';
		if ($limit !== null && $num !== null) {
			$limitsql = ' LIMIT %d, %d';
		}
		return DB::fetch_all("SELECT t1.user_id, t2.username FROM %t AS t1
		LEFT JOIN ".DB::table('common_member')." AS t2 ON t1.user_id = t2.uid
		GROUP BY user_id ASC".$limitsql, array($this->_table, $limit, $num));
	}

	public function select_block_log_by_uid($userid,$limit = null, $num = null) {
		$limitsql = '';
		if ($limit !== null && $num !== null) {
			$limitsql = ' LIMIT %d, %d';
		}
		return DB::fetch_all("SELECT t1.block_id, t2.block_name FROM %t AS t1
		LEFT JOIN ".DB::table('forum_kouei_block')." AS t2 ON t1.block_id = t2.block_id
		WHERE user_id=%s".$limitsql, array($this->_table, $userid, $limit, $num));
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