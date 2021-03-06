<?php
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

$operation = $_GET['operation'];
// 包含后台的头文件，并且展示顶部的小导航栏  论坛 >> 论坛小导航 [+]
cpheader();
shownav('forum', 'kouei_navigation_title');

if ($operation == 'base') {
    // 展示论坛小导航的导航菜单
    /**
     * showsubmenu()
     * 参数一：大菜单的标题
     * 参数二：小菜单的个数
     * 参数二中每个数组：每个菜单的具体信息
     * 参数二中每个数组中的索引1：小菜单的名称，在语言包 lang_admincp 下。
     * 参数二中每个数组中的索引2：跳转地址
     * 参数二中每个数组中的索引3：判断是否为当前选中的菜单
     */
    showsubmenu('kouei_navigation_title', array(
        array('view', 'koueinavigation&operation=base', '1'),
        array('add', 'koueinavigation&operation=create'),
        array('modify', "koueinavigation&operation=modify"),
        array('kouei_nav_recommend', "koueinavigation&operation=recommend"),
        array('kouei_block_log', "koueinavigation&operation=log"),
        array('other', "koueinavigation&operation=other"),
    ));

    // 展示提示语，参数是语言包 lang_admincp 下的对应项
    showtips('kouei_navigation_base_tips');

    // 展示我们的标签内容, 当然可以删除和修改
    // 先做分页, 查到总数和数据
    $_G['setting']['blockperpage'] = 20;
    $page = max(1, $_G['page']);
    $start_limit = ($page - 1) * $_G['setting']['blockperpage'];

    $blocks = array();
    $block_count = C::t('forum_kouei_block')->count();
    $blocks = C::t('forum_kouei_block')->fetch_by_pagenum($start_limit, $_G['setting']['blockperpage']);
    $multipage = multi($block_count, $_G['setting']['blockperpage'], $page, ADMINSCRIPT . "?action=koueinavigation&operation=base");

    showformheader("koueinavigation&operation=clean");
    showtableheader(cplang('kouei_navigation_count', array('block_count' => $block_count)));
    if ($block_count) {
        showsubtitle(array('', 'kouei_nav_id', 'kouei_nav_name', 'kouei_nav_item', 'kouei_sort_type', ''));
        echo getBlocks($blocks);
        showsubmit('deletesubmit', cplang('delete'), '', '', $multipage);
    }
    showformfooter();
    /*
    dd($blocks_count);
    dd($blocks);
    */

} elseif ($operation == 'create') {
    showsubmenu('kouei_navigation_title', array(
        array('view', 'koueinavigation&operation=base'),
        array('add', 'koueinavigation&operation=create', '1'),
        array('modify', "koueinavigation&operation=modify"),
        array('kouei_nav_recommend', "koueinavigation&operation=recommend"),
        array('kouei_block_log', "koueinavigation&operation=log"),
        array('other', "koueinavigation&operation=other"),
    ));
    showtips('kouei_navigation_add_tips');
    /**
     * 下面这一段代码，做的事情，简单描述一下。
     * 创建一个 id 为 koueinavigation 的 div 标签，其中，包含了一个 method 为 post 的表格，提交到
     * 'koueinavigation&operation=store' 这个 uri 上。
     * form 表单中，有一个 table ，table 中则生成，一个 name 为 block_name 的 text 文本框，和一个
     * name 为 block_item 的 textarea 文本框。
     */
    $lang = lang('admincp');
    showtagheader('div', 'koueinavigation', true);
    showformheader('koueinavigation&operation=store');
    showtableheader();
    showsetting('kouei_navigation_block_name', 'block_name', '', 'text', '', 0, $lang['block_name_comment']);
    showsetting('kouei_navigation_block_item', 'block_item', '', 'textarea', '', 0, $lang['block_item_comment']);
    $sorts = C::t('forum_kouei_blocksort')->fetch_all_id_name();
    if (!empty($sorts)) {
        $sort_list = "<select name=\"sort_id\">\n<option value='0' selected='selected'>$lang[block_sort]</option>";
        foreach ($sorts as $sort) {
            $sort_list .= "<option value=\"$sort[sort_id]\">$sort[sort_name]</option>\n";
        }
        $sort_list .= '</select>';
        showsetting('kouei_navigation_sort', '', '', $sort_list, '', 0, $lang['block_sort_commend']);
    }
    showsubmit('submit');
    showtablefooter();
    showformfooter();
    showtagfooter('div');

} elseif ($operation == 'store') {
    $block_data = getBlockData($_GET['block_name'], $_GET['block_item'], $_GET['sort_id']);
    // 插入数据库。
    $result = C::t('forum_kouei_block')->insert($block_data);
    $result ? cpmsg('create_block_success', 'action=koueinavigation&operation=base', 'succeed') : cpmsg_error('unknow_error');

} elseif ($operation == 'clean') {
    // 删除导航
    // 删除导航的同时，我们也要删除用户关注过的导航的数据
    // 所以这个日后开发
    $block_id = dhtmlspecialchars($_GET['blockidarray']);
    $detele_blocks = C::t('forum_kouei_block')->delete_by_bids($block_id);
    $delete_blockitems = C::t('forum_kouei_blockitem')->delete_by_bids($block_id);
    cpmsg(cplang('kouei_block_clean', array('delete_blocks' => $detele_blocks, 'delete_blockitems' => $delete_blockitems)), 'action=koueinavigation&operation=base', 'succeed');

} elseif ($operation == 'modify') {
    $blockid = intval($_GET['blockid']);
    if ($blockid) {
        showsubmenu('kouei_navigation_title', array(
            array('view', 'koueinavigation&operation=base'),
            array('add', 'koueinavigation&operation=create'),
            array('modify', "koueinavigation&operation=modify", '1'),
            array('kouei_nav_recommend', "koueinavigation&operation=recommend"),
            array('kouei_block_log', "koueinavigation&operation=log"),
            array('other', "koueinavigation&operation=other"),
        ));
        showtips('kouei_navigation_modify_tips');

        $block = C::t('forum_kouei_block')->fetch_all($blockid);
//        dd($block);
        $lang = lang('admincp');
        showtagheader('div', 'koueinavigation', true);
        showformheader('koueinavigation&operation=update');
        showtableheader();
        showsetting('kouei_navigation_block_id', 'block_id', $blockid, 'text', 'readonly');
        showsetting('kouei_navigation_block_name', 'block_name', $block[$blockid]['block_name'], 'text', '', 0, $lang['block_name_comment']);
        showsetting('kouei_navigation_block_item', 'block_item', $block[$blockid]['block_item'], 'textarea', '', 0, $lang['block_item_comment']);
        $sorts = C::t('forum_kouei_blocksort')->fetch_all_id_name();
        if (!empty($sorts)) {
            $sort_list = "<select name=\"sort_id\">\n<option value='0'>$lang[block_sort]</option>";
            foreach ($sorts as $sort) {
                if ($sort['sort_id'] != $block[$blockid]['sort_id']) {
                    $sort_list .= "<option value=\"$sort[sort_id]\">$sort[sort_name]</option>\n";
                } else {
                    $sort_list .= "<option value=\"$sort[sort_id]\" selected='selected'>$sort[sort_name]</option>\n";
                }
            }
            $sort_list .= '</select>';
            showsetting('kouei_navigation_sort', '', '', $sort_list, '', 0, $lang['block_sort_commend']);
        }
        showsubmit('submit');
        showtablefooter();
        showformfooter();
        showtagfooter('div');
    } else {
        cpmsg_error('nothing_modify', 'action=koueinavigation&operation=base');
    }
} elseif ($operation == 'update') {
    $blockid = intval($_GET['block_id']);
    // dd($blockid);
    if ($blockid) {
        $block_data = getBlockData($_GET['block_name'], $_GET['block_item'], $_GET['sort_id']);
        $result = C::t('forum_kouei_block')->update($blockid, $block_data);
        $result ? cpmsg('update_block_success', 'action=koueinavigation&operation=base', 'succeed') : cpmsg_error('unknow_error');
    } else {
        cpmsg_error('unknow_error');
    }
} else if ($operation == 'recommend') {
    showsubmenu('kouei_navigation_title', array(
        array('view', 'koueinavigation&operation=base'),
        array('add', 'koueinavigation&operation=create'),
        array('modify', "koueinavigation&operation=modify"),
        array('kouei_nav_recommend', "koueinavigation&operation=recommend", '1'),
        array('kouei_block_log', "koueinavigation&operation=log"),
        array('other', "koueinavigation&operation=other"),
    ));

    global $_G;
    $cache_tids = '';
    loadcache('kouei_recommend_threads');
    if ($_G['cache']['kouei_recommend_threads']) {
        $cache_threads = $_G['cache']['kouei_recommend_threads'];
        foreach ($cache_threads as $tid => $data) {
            $cache_tids .= $tid . ',';
        }
        $cache_tids = substr($cache_tids, 0, strlen($cache_tids) - 1);
    }
    showformheader('koueinavigation&operation=recommend&submit=yes');
    showtableheader();
    showsetting('kouei_nav_recommend', 'recommend_tids', $cache_tids, 'textarea', '', 0, $lang['kouei_recommend_tips']);
    showsubmit('submit');
    showtablefooter();
    showformfooter();
    if (!empty($_POST) && $_GET['submit']) {
        $tids = dhtmlspecialchars(trim($_POST['recommend_tids']));
        // 处理对应版块的提交内容，只允许提交有效内容。
        $tids_arr = array();
        // 数组唯一，并重写索引，因为 array_unique() 不改写键名。
        $tids_arr = array_values(array_unique(explode(',', $tids)));
        /* 
        dd($block_item_arr);
        exit;
        */
        $bia_len = count($tids_arr);

        if ($bia_len == 1) {
            $recommend_tid = intval($tids_arr['0']);
            $recommend_tid === 0 ? cpmsg_error('kouei_navigation_type_error', 'action=koueinavigation&operation=recommend') : $block_item = (string)$block_item;
        } else {
            $handle = '';
            for ($i = 0; $i < $bia_len; $i++) {
                $tids_arr[$i] = intval($tids_arr[$i]);
                if ($tids_arr[$i] == 0) {
                    cpmsg_error('kouei_navigation_type_error', 'action=koueinavigation&operation=recommend');
                }
            }
        }
        $thread_list = C::t('forum_thread')->fetch_all_by_tid($tids_arr);
        $threads = array();
        $posttables = array();
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
        require_once libfile('function/cache');

        // 按输入的顺序，，对小编推荐进行排序。
        $threads_change_keys = array();
        foreach ($tids_arr as $value) {
            $threads_change_keys[$value] = $threads[$value];
        }
        $result = savecache('kouei_recommend_threads', $threads_change_keys);
    }
} else if ($operation == 'other') {
    $setting = C::t('common_setting')->fetch_all(null);

    showsubmenu('kouei_navigation_title', array(
        array('view', 'koueinavigation&operation=base'),
        array('add', 'koueinavigation&operation=create'),
        array('modify', "koueinavigation&operation=modify"),
        array('kouei_nav_recommend', "koueinavigation&operation=recommend"),
        array('kouei_block_log', "koueinavigation&operation=log"),
        array('other', "koueinavigation&operation=other", '1'),
    ));
    showformheader('setting&edit=yes');
    showtableheader();
    showsetting('kouei_adv', 'settingnew[kouei_adv]', $setting['kouei_adv'], 'radio');
    showsetting('kouei_help_fid', 'settingnew[kouei_helpfid]', $setting['kouei_helpfid'], 'text', '', 0, $lang['kouei_help_fid_explain']);
    showsetting('kouei_adv_link', 'settingnew[kouei_adv_link]', $setting['kouei_adv_link'], 'text', '', 0, $lang['kouei_adv_link_explain']);
    showsetting('kouei_adv_img', 'settingnew[kouei_adv_img]', $setting['kouei_adv_img'], 'text', '', 0, $lang['kouei_adv_img_explain']);
    showsetting('kouei_push_adv_link', 'settingnew[kouei_push_adv_link]', $setting['kouei_push_adv_link'], 'text', '', 0, $lang['kouei_push_adv_link_explain']);
    showsubmit('settingsubmit');
    showtablefooter();
    showformfooter();
} else if ($operation == 'log') {
    showsubmenu('kouei_navigation_title', array(
        array('view', 'koueinavigation&operation=base'),
        array('add', 'koueinavigation&operation=create'),
        array('modify', "koueinavigation&operation=modify"),
        array('kouei_nav_recommend', "koueinavigation&operation=recommend"),
        array('kouei_block_log', "koueinavigation&operation=log", '1'),
        array('other', "koueinavigation&operation=other"),
    ));
    showtips('kouei_navigation_log_tips');
//    $_G['tpp'] = 2;
    $page = max(1, $_G['page']);
    $start_limit = ($page - 1) * $_G['tpp'];
    if (!isset($_GET['userid'])) {
        $logs_count = count(C::t('forum_kouei_blockitem')->select_log_by_uid());
        $all_block_logs = C::t('forum_kouei_blockitem')->select_log_by_uid($start_limit, $_G['tpp']);
        $multipage = multi($logs_count, $_G['tpp'], $page, ADMINSCRIPT . "?action=koueinavigation&operation=log");
        showtableheader(cplang('kouei_navigation_log_count', array('logs_count' => $logs_count)), 'kouei-short-width');
        if ($logs_count) {
            showsubtitle(array('', 'kouei_user_id', 'kouei_user_name', ''));
            echo getLogs($all_block_logs);
            showsubmit('', '', '', '', $multipage);
        }
    } else {
        $user_id = dintval(trim($_GET['userid']));
        $block_logs_count = count(C::t('forum_kouei_blockitem')->select_block_log_by_uid($user_id));
        $block_logs = C::t('forum_kouei_blockitem')->select_block_log_by_uid($user_id, $start_limit, $_G['tpp']);
        $multipage = multi($block_logs_count, $_G['tpp'], $page, ADMINSCRIPT . "?action=koueinavigation&operation=log&userid=$user_id");
        showtableheader(cplang('kouei_navigation_count', array('block_count' => $block_logs_count)), 'kouei-short-width');
        if ($block_logs_count) {
            showsubtitle(array('', 'kouei_nav_id', 'kouei_nav_name', ''));
            echo getBlockLogs($block_logs);
            showsubmit('', '', '', '', $multipage);
        }
    }
}

/**
 * @param $params
 * 传入你数据库查询到的数组参数
 * @return string
 * 返回数据展示的表单 html 字符串
 */
function getBlocks($params)
{
    global $_G, $lang;
    $blocks = '';
    $sorts = C::t('forum_kouei_blocksort')->fetch_all_id_name();
    $sorts_list = array_column($sorts, 'sort_name', 'sort_id');
    if (!empty($params)) {
        $sort_name = '';
        foreach ($params as $param) {
            if ($param['sort_id'] != 0) {
                $sort_name = $sorts_list[$param['sort_id']];
            } else {
                $sort_name = $lang['no_sort'];
            }
            $blocks .= showtablerow('', array(), array(
                "<input type=\"checkbox\" name=\"blockidarray[]\" value=\"$param[block_id]\" class=\"checkbox\">",
                $param['block_id'],
                $param['block_name'],
                $param['block_item'],
                $sort_name,
                "<a href='?action=koueinavigation&operation=modify&blockid=" . $param['block_id'] . "'>$lang[edit]</a>"
            ), TRUE);
        }
    }
    return $blocks;
}

function getLogs($params)
{
    global $_G, $lang;
    $logs = '';
    if (!empty($params)) {
        foreach ($params as $param) {
            $logs .= showtablerow('', array(), array(
                '',
                $param['user_id'],
                $param['username'],
                "<a href='?action=koueinavigation&operation=log&userid=" . $param['user_id'] . "'>$lang[view]</a>"
            ), TRUE);
        }
    }
    return $logs;
}

function getBlockLogs($params)
{
    global $_G, $lang;
    $blocklogs = '';
    if (!empty($params)) {
        foreach ($params as $param) {
            $blocklogs .= showtablerow('', array(), array(
                '',
                $param['block_id'],
                $param['block_name'],
                '',
            ), TRUE);
        }
    }
    return $blocklogs;
}

/**
 * @param $name
 * 传递过来的 'block_name' 参数
 * @param $item
 * 传递过来的 'block_item' 参数
 * @return array
 * 返回一个参数处理过后的数组，直接进行插入或者升级。
 */
function getBlockData($name, $item, $sort)
{
    $block_name = '';
    $block_item = '';
    $sort_id = '';
    if (!$name || !$item || intval($sort) == 0) {
        cpmsg_error('kouei_navigation_store_error_empty', 'action=koueinavigation&operation=create');
    } else {
        $block_name = dhtmlspecialchars(trim($name));
        $block_item = dhtmlspecialchars(trim($item));
        $sort_id = intval($sort);

        // 判断提交内容长度，根据你设计的数据库进行改动。
        $block_name_len = strlen($block_name);
        $block_item_len = strlen($block_item);
        if ($block_name_len > 40 || $block_item_len > 255) {
            cpmsg_error('kouei_navigation_too_long', 'action=koueinavigation&operation=create');
        }

        // 处理对应版块的提交内容，只允许提交有效内容。
        $block_item_arr = array();
        // 数组唯一，并重写索引，因为 array_unique() 不该写键名。
        $block_item_arr = array_values(array_unique(explode(',', $block_item)));
        /*
        dd($block_item_arr);
        exit;
        */
        $bia_len = count($block_item_arr);

        if ($bia_len == 1) {
            $block_item = intval($block_item_arr['0']);
            $block_item === 0 ? cpmsg_error('kouei_navigation_type_error', 'action=koueinavigation&operation=create') : $block_item = (string)$block_item;
        } else {
            $handle = '';
            for ($i = 0; $i < $bia_len; $i++) {
                $block_item_arr[$i] = intval($block_item_arr[$i]);
                if ($block_item_arr[$i] == 0) {
                    cpmsg_error('kouei_navigation_type_error', 'action=koueinavigation&operation=create');
                }
                $handle .= $block_item_arr[$i] . ',';
            }
            $block_item = substr($handle, 0, strlen($handle) - 1);
        }

        // 数据至此处理完成，准备插入数据库。
        /*
        dd($block_name);
        dd($block_item);
        */
        return array(
            'block_name' => $block_name,
            'block_item' => $block_item,
            'sort_id' => $sort_id,
        );
    }
}