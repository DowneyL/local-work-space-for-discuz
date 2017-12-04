<?php
/*
 * 主页：http://addon.discuz.com/?@ailab
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_viewlog extends discuz_table{
	public function __construct() {

		$this->_table = 'viewlog';
		$this->_pk    = 'logid';
		$this->_pre_cache_key = 'viewlog_';

		parent::__construct();
	}
	public function count(){
		$count = DB::result_first('SELECT COUNT(*) FROM %t', array($this->_table));
		return $count;
	}
	
	public function fetch_all_by_range($start,$end) {
		return DB::fetch_all('SELECT * FROM %t ORDER BY logid DESC LIMIT %d,%d', array($this->_table,$start,$end), $this->_pk);
	}
	
	public function fetch_logid_by_tid_uid($tid,$uid){
		return DB::result_first('SELECT logid FROM %t where tid=%d and uid=%d ORDER BY logid DESC', array($this->_table,$tid,$uid), $this->_pk);
	}
	
	public function fetch_all_by_tid($tid,$num=0) {
		if($num) return DB::fetch_all('SELECT * FROM %t where tid=%d ORDER BY dateline DESC LIMIT %d', array($this->_table,$tid,$num), $this->_pk);
		else return DB::fetch_all('SELECT * FROM %t where tid=%d ORDER BY logid DESC', array($this->_table,$tid), $this->_pk);
	}

	public function update_by_logid($newData,$logid){
		DB::update($this->_table,$newData,array('logid'=>$logid));
	}
	
	public function delete_by_time($detime){
		DB::query("delete from %t where dateline<%d",array($this->_table,$detime), $this->_pk);
	}
	
	public function drop() {
		return DB::query('DROP TABLE IF EXISTS %t',array($this->_table));
	}
}

?>