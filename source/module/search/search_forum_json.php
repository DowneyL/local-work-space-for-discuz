<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_forum.php 33198 2013-05-06 09:23:45Z jeffjzhang
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

//调试用的函数
function dd($params)
{
    if (is_array($params)) {
        echo "<pre>";
        print_r($params);
        echo "</pre>";
    } else {
        echo $params . "<br />";
    }
}

function bre($params)
{
    echo $params . "<br />";
}

define('NOROBOT', TRUE);

if (!$_G['setting']['search']['forum']['status']) {
    showmessage('search_forum_closed'); // 判断是否开启论坛帖子搜索功能
}

if (!$_G['adminid'] && !($_G['group']['allowsearch'] & 2)) {
    // 判断用户组权限
    showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['forum']['searchctrl'] = intval($_G['setting']['search']['forum']['searchctrl']);


require_once libfile('function/forumlist');
require_once libfile('function/forum');
require_once libfile('function/post');
loadcache(array('forums', 'posttable_info'));
$srchmod = 2;

$cachelife_time = 300;        // Life span for cache of searching in specified range of time
$cachelife_text = 3600;        // Life span for cache of text searching

$srchtype = empty($_GET['srchtype']) ? '' : trim($_GET['srchtype']);
$searchid = isset($_GET['searchid']) ? intval($_GET['searchid']) : 0;

$seltableid = intval($_GET['seltableid']);

if ($srchtype != 'title' && $srchtype != 'fulltext') {
    $srchtype = '';
}

// echo $srchtype . "<br/>";	// 设置搜索类型
// echo $searchid . "<br/>";	// searchid 搜索内容缓存id

//$content = iconv("utf-8","gb2312",$content);

$srchtxt = iconv("utf-8", "gb2312", urldecode(trim($_GET['srchtxt']))); // 对 ajax 提交过来的中文内容进行转码。
$srchuid = intval($_GET['srchuid']);
$srchuname = isset($_GET['srchuname']) ? trim(str_replace('|', '', $_GET['srchuname'])) : '';;
$srchfrom = intval($_GET['srchfrom']);
$before = intval($_GET['before']);
$srchfid = $_GET['srchfid'];
$srhfid = intval($_GET['srhfid']);


$keyword = isset($srchtxt) ? dhtmlspecialchars(trim($srchtxt)) : '';

// 尝试打印这些搜索信息

/*
bre($srchtxt);
bre($srchuid);
bre($srchuname);
bre($srchfrom);
bre($before);
bre($srchfid);
bre($srhfid);
*/

if (!submitcheck('searchsubmit', 1)) {
    if ($_GET['adv']) {
        include template('search/forum_adv');
    } else {
        include template('search/forum');
    }

} else {

    /**
     * $orderby 为排序方式
     * dateline 按发帖时间
     * replies 按回复数
     * views 按查看数
     * lastpost 按最后回复时间
     */
    // $orderby = in_array($_GET['orderby'], array('dateline', 'replies', 'views')) ? $_GET['orderby'] : 'lastpost';    //设置排序方式
    $orderby = 'dateline';
    /**
     * $ascdesc 为升降序
     * desc 为降序
     * asc  为升序
     */
    $ascdesc = isset($_GET['ascdesc']) && $_GET['ascdesc'] == 'asc' ? 'asc' : 'desc';    //设置升降序

//	 dd($orderby);
//	 dd($ascdesc);
//	 var_dump(!empty($searchid));		// false

    //设置查找方式
    if ($_G['group']['allowsearch'] & 32 && $srchtype == 'fulltext') {
        periodscheck('searchbanperiods');
    } elseif ($srchtype != 'title') {
        $srchtype = 'title';
    }

    // var_dump(!empty($srchfid));

    // 设置查找范围
    $forumsarray = array();
    if (!empty($srchfid)) {
        foreach ((is_array($srchfid) ? $srchfid : explode('_', $srchfid)) as $forum) {
            if ($forum = intval(trim($forum))) {
                $forumsarray[] = $forum;
            }
        }
    }

    // dd($forumsarray);

    $fids = $comma = '';
    foreach ($_G['cache']['forums'] as $fid => $forum) {
        if ($forum['status'] && $forum['type'] != 'group' && (!$forum['viewperm'] && $_G['group']['readaccess']) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
            if (!$forumsarray || in_array($fid, $forumsarray)) {
                $fids .= "$comma'$fid'";
                $comma = ',';
            }
        }
    }
//    dd($_G['cache']['forums']);

    $fids = '2';
//		 dd($fids); // 获取所有搜索版块的 id
//		 var_dump($_G['setting']['threadplugins'] && $specialplugin);

    //这个是干啥的，还不知道。 (特殊查找？)
    if ($_G['setting']['threadplugins'] && $specialplugin) {
        $specialpluginstr = implode("','", $specialplugin);
        $special[] = 127;
    } else {
        $specialpluginstr = '';
    }

    $special = $_GET['special'];
    $specials = $special ? implode(',', $special) : '';
    $srchfilter = in_array($_GET['srchfilter'], array('all', 'digest', 'top')) ? $_GET['srchfilter'] : 'all';

    $searchstring = 'forum|' . $srchtype . '|' . base64_encode($srchtxt) . '|' . intval($srchuid) . '|' . $srchuname . '|' . addslashes($fids) . '|' . intval($srchfrom) . '|' . intval($before) . '|' . $srchfilter . '|' . $specials . '|' . $specialpluginstr . '|' . $seltableid;
    $searchindex = array('id' => 0, 'dateline' => '0');

    /*
    dd($special);
    dd($specials);
    dd($srchfilter);
    dd($searchstring); //这个貌似是关键？一个搜索字符串
    dd($searchindex);
    */

    $digestltd = $srchfilter == 'digest' ? "t.digest>'0' AND" : '';
    $topltd = $srchfilter == 'top' ? "AND t.displayorder>'0'" : "AND t.displayorder>='0'";

    // dd($digestltd);		// 搜索判断条件
    // dd($topltd);	// 搜索判断条件
    // var_dump(!empty($srchfrom) && empty($srchtxt) && empty($srchuid) && empty($srchuname));	//false

    if (!empty($srchfrom) && empty($srchtxt) && empty($srchuid) && empty($srchuname)) {

        $searchfrom = $before ? '<=' : '>=';
        $searchfrom .= TIMESTAMP - $srchfrom;
        $sqlsrch = "FROM " . DB::table('forum_thread') . " t WHERE $digestltd t.fid IN ($fids) $topltd AND t.lastpost$searchfrom";
        $expiration = TIMESTAMP + $cachelife_time;
        $keywords = '';

    } else {

        $sqlsrch = $srchtype == 'fulltext' ?
            "FROM " . DB::table(getposttable($seltableid)) . " p, " . DB::table('forum_thread') . " t WHERE $digestltd t.fid IN ($fids) $topltd AND p.tid=t.tid AND p.invisible='0'" :
            "FROM " . DB::table('forum_thread') . " t WHERE $digestltd t.fid IN ($fids) $topltd";

        // dd($sqlsrch);	// 生成搜索的 sql 语句。

        if ($srchuname) {
            $srchuid = array_keys(C::t('common_member')->fetch_all_by_like_username($srchuname, 0, 50));
            if (!$srchuid) {
                $sqlsrch .= ' AND 0';
            }
        }/* elseif($srchuid) {
						$srchuid = "'$srchuid'";
					}*/


        if ($srchtxt) {
            $srcharr = $srchtype == 'fulltext' ? searchkey($keyword, "(p.message LIKE '%{text}%' OR p.subject LIKE '%{text}%')", true) : searchkey($keyword, "t.subject LIKE '%{text}%'", true);
            $srchtxt = $srcharr[0];
            $sqlsrch .= $srcharr[1];
        }

        // dd($sqlsrch); 	// 一步步更新 sql 语句


        if ($srchuid) {
            $sqlsrch .= ' AND ' . ($srchtype == 'fulltext' ? 'p' : 't') . '.authorid IN (' . dimplode((array)$srchuid) . ')';
        }

        //dd($sqlsrch); 	// 一步步更新 sql 语句


        if (!empty($srchfrom)) {
            $searchfrom = ($before ? '<=' : '>=') . (TIMESTAMP - $srchfrom);
            $sqlsrch .= " AND t.lastpost$searchfrom";
        }

        // dd($sqlsrch); 	// 一步步更新 sql 语句

        if (!empty($specials)) {
            $sqlsrch .= " AND special IN (" . dimplode($special) . ")";
        }

        // dd($sqlsrch); 	// 一步步更新 sql 语句

        $keywords = str_replace('%', '+', $srchtxt);
        $expiration = TIMESTAMP + $cachelife_text;

        // dd($keywords);	// 搜索的关键词
        // dd($expiration); 	// 搜索在哪个事件范围内？
    }

    $num = $ids = 0;
    $_G['setting']['search']['forum']['maxsearchresults'] = $_G['setting']['search']['forum']['maxsearchresults'] ? intval($_G['setting']['search']['forum']['maxsearchresults']) : 500;
    $query = DB::query("SELECT " . ($srchtype == 'fulltext' ? 'DISTINCT' : '') . " t.tid, t.closed, t.author, t.authorid $sqlsrch ORDER BY tid DESC LIMIT " . $_G['setting']['search']['forum']['maxsearchresults']);

    // dd("SELECT ".($srchtype == 'fulltext' ? 'DISTINCT' : '')." t.tid, t.closed, t.author, t.authorid $sqlsrch ORDER BY tid DESC LIMIT ".$_G['setting']['search']['forum']['maxsearchresults']);  // 最终执行的查询语句

    while ($thread = DB::fetch($query)) {
        $ids .= ',' . $thread['tid'];
        $num++;
    }

    DB::free_result($query);

    $srch_arr = array(
        'srchmod' => $srchmod,
        'keywords' => $keywords,
        'searchstring' => $searchstring,
        'useip' => $_G['clientip'],
        'uid' => $_G['uid'],
        'dateline' => $_G['timestamp'],
        'expiration' => $expiration,
        'num' => $num,
        'ids' => $ids
    );

    /*
    dd($srch_arr);
    */

    require_once libfile('function/misc');

    $_G['tpp'] = 10; // 设置搜索的个数
    $page = max(1, intval($_GET['page']));
    $start_limit = ($page - 1) * $_G['tpp'];

    //dd($page);	// 应该是当前页数
    //dd($start_limit);	// 排序

    $keyword = dhtmlspecialchars($srch_arr['keywords']);
    $keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

    $index = array();
    $index['keywords'] = rawurlencode($srch_arr['keywords']);
    $searchtring = explode('|', $srch_arr['searchstring']);
    $index['searchtype'] = $searchstring[0];
    $searchstring[2] = base64_decode($searchstring[2]);
    $srchuname = $searchstring[3];
    $modfid = 0;

    /*
    dd($index['keywords']);		// 处理过的关键词
    dd($searchstring);		// 处理 searchstring 获取所需数据
    dd($index['searchtype']);	// 获取 search type
    dd($searchstring[2]);	 // 也是 keywords ??
    dd($srchuname);			// 要查询的用户相关
    */

    if ($keyword) {
        $modkeyword = str_replace(' ', ',', $keyword);
        $fids = explode(',', str_replace('\'', '', $searchstring[5]));
        if (count($fids) == 1 && in_array($_G['adminid'], array(1, 2, 3))) {
            $modfid = $fids[0];
            if ($_G['adminid'] == 3 && !C::t('forum_moderator')->fetch_uid_by_fid_uid($modfid, $_G['uid'])) {
                $modfid = 0;
            }
        }
    }

    $threadlist = $posttables = array();
    foreach (C::t('forum_thread')->fetch_all_by_tid_fid_displayorder(explode(',', $srch_arr['ids']), null, 0, $orderby, $start_limit, $_G['tpp'], '>=', $ascdesc) as $thread) {
        // 不设置高亮
//			$thread['subject'] = bat_highlight($thread['subject'], $keyword);
        $thread['realtid'] = $thread['isgroup'] == 1 ? $thread['closed'] : $thread['tid'];
        $threadlist[$thread['tid']] = procthread($thread, 'dt');
        $posttables[$thread['posttableid']][] = $thread['tid'];
    }
//
//		echo "<pre>";
//		print_r($posttables);
//		echo "</pre>";
//
		if($threadlist) {
			foreach($posttables as $tableid => $tids) {
				foreach(C::t('forum_post')->fetch_all_by_tid($tableid, $tids, true, '', 0, 0, 1) as $post) {
					$threadlist[$post['tid']]['message'] = messagecutstr($post['message'], 200);
				}
			}

		}

//		echo "<pre>";
//		print_r($threadlist);
//		echo "</pre>";

    /*
     * 搜索页面的翻页显示内容，重写multi函数
     * echo $index['num']."<br/>"; 搜索出来的内容个数
     * echo $_G['tpp']."<br/>"; 每页显示的个数
     * echo $page."<br/>"; 当前页数
     */
    $multipage = multi($srch_arr['num'], $_G['tpp'], $page, "search.php?mod=forum&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

    $url_forward = 'search.php?mod=forum&' . $_SERVER['QUERY_STRING'];

    $fulltextchecked = $searchstring[1] == 'fulltext' ? 'checked="checked"' : '';

//		$thread['subject'] = urlencode($thread['subject']);
    //输出

//    dd($threadlist);
    $thread_arr = array();
    $keywords = array('subject', 'author', 'views', 'message', 'replies');
    foreach ($threadlist as $tid => $values) {
        foreach ($values as $key => $value) {
            if (in_array($key, $keywords)) {
                $thread_arr[$tid][$key] = urlencode(preg_replace('/\s+/','', $value));
            }
        }
    }
//		dd($thread_arr);
//		dd($threadlist);
    $thread_json = json_encode($thread_arr);
    echo urldecode($thread_json);
}

?>