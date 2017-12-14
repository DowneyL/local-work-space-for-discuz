<?php
/**
 *  用来新建一个导航页面，主要解决首页杂乱，内容推荐比较鸡肋的问题
 *  Author: Heng lee
 *  Date: 2017/11/17/ 13:20
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

global $_G;
$action = trim($_GET['action']);
$blocks = array();
$blocks = C::t('forum_kouei_block')->fetch_all_id_name();
/* 首先判断是否设置了标签，后台没有设置任何标签的时候，报错，并跳出。 */
//dd($blocks);
if (empty($blocks)) {
    echo 'No blocks in the database';
    exit;
}

/* 判断用户是否关注了标签，根据结果，执行不同的代码段。 */
$uid = $_G['uid'];
$follow_flag = false;
$me_flag = false;
$follow_ids = C::t('forum_kouei_blockitem')->select($uid);
$lang = lang('forum/navigation');
$filters = array('sort');
if (!empty($follow_ids) && !($action == 'tag')) {
    /**
     * 侧边栏相关数据获取
     */
    /* 获取热门标签的缓存 */
    require_once libfile('function/cache');
    loadcache('sort_block_id');
    $sort_block_ids = $_G['cache']['sort_block_id'];
    $hot_blocks = array();
    $blockss = array();
    foreach ($blocks as $key => $block) {
        $blockss[$block['block_id']] = $block;
    }

    foreach ($sort_block_ids as $sort_block_id) {
        $hot_blocks[] = $blockss[$sort_block_id];
    }
//    dd($hot_blocks);
    $hot_blocks = array_slice($hot_blocks, 0, 10);

    /*
     * 小编推荐
     */
    require_once libfile('function/cache');
    loadcache('kouei_recommend_threads');
    $all_hot_threads = $_G['cache']['kouei_recommend_threads'];
    $hot_threads = array_slice($all_hot_threads, 0, 4);

    if ($action != 'recommend') {
        $recommend_flag = false;
        /* 设置分页 */
        $_G['tpp'] = 20;
        $page = max(1, $_G['page']);
        $start_limit = ($page - 1) * $_G['tpp'];
        $max_count = 500;
        /* 参数会有很多不同，我们需要获取自己的 url 并且去除原本的 orderby 参数 */
        $selfurl = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
        $selfurl = getSelfUrl($filters, $selfurl);

        loadcache('forums');
        /* 查看每个单独导航的时候，获取的数据 */
        if ($_GET['blockid']) {
            $block_id = intval(dhtmlspecialchars($_GET['blockid']));
            $follow_ids = array(
                '0' => array(
                    'block_id' => $block_id
                )
            );
            $block_name = '';
            foreach ($blocks as $block) {
                if ($block['block_id'] == $block_id) {
                    $block_name = $block['block_name'];
                }
            }
        }
        /* 用户获取本周最热，和本月最热的消息 */
        $sorttime = ''; /* 存储查询的时间点 */
        if ($_GET['sort']) {
            $sort = trim($_GET['sort']);
            $now = TIMESTAMP;
            switch ($sort) {
                case 'weekly' :
                    $sorttime = $now - 3600 * 24 * 7;
                    break;
                case 'monthly' :
                    $sorttime = $now - 3600 * 24 * 30;
                    break;
                case 'history' :
                    $sorttime = '';
                    break;
            }
        }
        /* 获取用户关注的帖子数据 */
        $tidarray = array();
        foreach ($follow_ids as $key => $follow) {
            $follow_ids[$key] = $follow['block_id'];
        }
        $itemarray = C::t('forum_kouei_block')->fetch_all_by_block_id($follow_ids);
        $tiditemarray = array_column($itemarray, 'block_item');
        $itemnames = array_column($itemarray, 'block_name', 'block_id');
        foreach ($tiditemarray as $tiditem) {
            $tidsarray[] = explode(',', $tiditem);
        }
        //dd($tidsarray);
        foreach ($tidsarray as $values) {
            foreach ($values as $value) {
                array_push($tidarray, $value);
            }
        }
        //dd($tidarray);
        $threads = $posttables = array();
        $thread_count = C::t('forum_thread')->count_by_kouei_fids($tidarray, $sorttime);
        if (intval($thread_count) > $max_count) {
            $thread_count = $max_count;
        }

        $thread_list = C::t('forum_thread')->fetch_by_kouei_fid($tidarray, 'heats', 'DESC', $start_limit, $_G['tpp'], $sorttime);
        // dd($thread_list);
        foreach ($thread_list as $key => $thread) {
            $threads[$thread['tid']] = $thread;
            // $threads[$thread['tid']]['subject'] = preg_replace('/\s+/','', $thread['subject']);
            $thread['dateline'] = dgmdate($thread['dateline'], 'u', '9999', getglobal('setting/dateformat'));
            $threads[$thread['tid']]['dateline'] = $thread['dateline'];
            $posttables[$thread['posttableid']][] = $thread['tid'];
        }
        // dd($posttables);
        if ($threads) {
            require_once libfile('function/post');
            foreach ($posttables as $tableid => $tids) {
                foreach (C::t('forum_post')->fetch_all_by_tid($tableid, $tids, true, '', 0, 0, 1) as $post) {
                    $threads[$post['tid']]['message'] = messagecutstr(preg_replace('/\s+/', '', $post['message']), 250);
                }
            }
        }
        /* 获取版块的名称 */
        $forum_lists = array();
        foreach ($tidarray as $forum_id) {
            foreach ($_G['cache']['forums'] as $forum) {
                if ($forum['fid'] == $forum_id) {
                    $forum_lists[$forum_id] = $forum['name'];
                }
            }
        }
        if ($_GET['page'] && $_GET['page'] != 1) {
            /**
             * 论坛是 GBK 的，各种转码和转字符都会出现问题
             * 这里之所以用 str_replace 函数，是因为英文双引号 "，会导致 json 的输出，产生问题，
             * 所以此处我们替换为英文的单引号 '
             * 然后再将汉字转字符。传递给前台接收。
             */
            foreach ($threads as $tid => $thread) {
                $threads[$tid]['author'] = urlencode(str_replace("\"", "'", $thread['author']));
                $threads[$tid]['dateline'] = urlencode(str_replace("\"", "'", $thread['dateline']));
                $threads[$tid]['subject'] = urlencode(str_replace("\"", "'", $thread['subject']));
                $threads[$tid]['message'] = urlencode(str_replace("\"", "'", $thread['message']));
                $threads[$tid]['forumname'] = urlencode(str_replace("\"", "'", $forum_lists[$thread['fid']]));
            }
            echo urldecode(json_encode($threads));
        } else {
            include_once template('forum/navigation/navigation');
        }
    } else {
        $recommend_flag = true;
        include_once template('forum/navigation/navigation');
    }
} else {
    if (empty($follow_ids)) {
        $follow_flag = true;
    }

    /* 标签搜素的相关参数 */
    $search_name = '';
    $search_flag = false;
    if (trim($_GET['do']) == 'search' && submitcheck('searchsubmit')) {
        $tagname = trim($_GET['tagname']);
        if ($tagname) {
            $search_name = dhtmlspecialchars($tagname);
            $search_flag = true;
        } else {
            showmessage($lang['search_null'], 'forum.php?mod=navigation&action=tag');
        }
    }
    /* 参数会有很多不同，我们需要获取自己的 url 并且去除原本的 sort 参数 */
    $selfurl = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
    $selfurl = getSelfUrl($filters, $selfurl);
    // 设置标签的分类
    $block_datas = array();
    $block_sort_list = array();
    $tag_order = '';
    trim($_GET['sort']) == 'newest' ? $tag_order = 'block_id' : $tag_order = 'count';
    $datas = C::t('forum_kouei_block')->sort_by_bs_order_id($tag_order, $search_name);
    // 分类导航
    $sortid = '';
    foreach ($datas as $key => $data) {
        $data['order_id'] ? $list_key = $data['order_id'] : $list_key = 0;
        $data['sort_name'] ? $sort_name = $data['sort_name'] : $sort_name = $lang['no_sort'];
        $block_sort_list[$list_key]['sort_name'] = $sort_name;
        $block_sort_list[$list_key]['sort_blocks'][$data['block_id']] = $data;
    }
    krsort($block_sort_list);
    if ($_GET['extra'] && trim($_GET['extra'] == 'my_tag')) {
        // 我的导航
        $block_list =array();
        $me_flag = true;
        foreach ($datas as $data) {
            $block_list[$data['block_id']] = $data;
        }
        foreach ($follow_ids as $bids) {
            $block_datas[$bids['block_id']] = $block_list[$bids['block_id']];
        }
        foreach ($block_datas as $key => $value) {
            $count[$key] = $value['count'];
            $block_id[$key] = $value['block_id'];
        }
        if ($tag_order == 'block_id') {
            array_multisort($block_id, SORT_DESC, $block_datas);
        } else {
            array_multisort($count, SORT_DESC, $block_datas);
        }
    } else {
        if (isset($_GET['sortid']) && intval($_GET['sortid']) !== null) {
            $sortid = intval($_GET['sortid']);
            !empty($block_sort_list[$sortid]) ? $block_datas[0] = $block_sort_list[$sortid] : $block_datas = $block_sort_list;
        } else {
            $block_datas = $block_sort_list;
        }
    }

//    dd($follow_ids);
    if ($_GET['type'] && trim($_GET['type']) == 'follow') {
        $result = array(
            'flag' => 0,
            'code' => 0,
        );

        if ($_POST['follow_block_id'] && $_POST['formhash'] == FORMHASH) {
            $data = array(
                'user_id' => $uid,
                'block_id' => intval(dhtmlspecialchars($_POST['follow_block_id'])),
                'formhash' => $_POST['formhash']
            );

            if (!$data['block_id']) {
                echo json_encode($result);
                exit;
            }

            $haved = C::t('forum_kouei_blockitem')->fetch_by_uid_bid($data['user_id'], $data['block_id']);
            if ($haved == 1) {
                $result['code'] = 4;
                echo json_encode($result);
                exit;
            }

            $followed = C::t('forum_kouei_blockitem')->insert(delByKey($data, 'formhash'));
            if ($followed != 1) {
                echo json_encode($result);
                exit;
            }
            $result['flag'] = 1;
            $result['code'] = 1;
            $result['block_id'] = $data['block_id'];
            echo json_encode($result);
            exit;
        } else {
            echo json_encode($result);
            exit;
        }
    } elseif ($_GET['type'] == 'unfollow') {
        $result = array(
            'flag' => 0,
        );
        if (!$_POST['unfollow_block_id']) {
            echo json_encode($result);
            exit;
        }
        $data = array(
            'user_id' => $uid,
            'block_id' => intval(dhtmlspecialchars($_POST['unfollow_block_id']))
        );
        $unfollowed = C::t('forum_kouei_blockitem')->delete_by_uid_bid($data['user_id'], $data['block_id']);
        if ($unfollowed != 1) {
            echo json_encode($result);
            exit;
        }
        $result['flag'] = 1;
        $result['block_id'] = $data['block_id'];
        echo json_encode($result);
        exit;
    } else {
        include_once template('forum/navigation/tag');
    }
}

function delByKey($arr, $key)
{
    if (!is_array($arr)) {
        return $arr;
    }
    foreach ($arr as $k => $v) {
        if ($k == $key) {
            unset($arr[$k]);
        }
    }
    return $arr;
}

function getSelfUrl ($filters, $url) {
    foreach ($filters as $filter) {
        $url = preg_replace('/[\?&]' . $filter . '=\w+/', '', $url);
    }
    return $url;
}
