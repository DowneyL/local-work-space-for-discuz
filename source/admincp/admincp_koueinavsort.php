<?php
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
$operation = $_GET['operation'];
// 包含后台的头文件，并且展示顶部的小导航栏  论坛 >> 论坛小导航 [+]
cpheader();
shownav('forum', 'kouei_navsort_title');
if ($operation == 'base') {
    showsubmenu('kouei_navsort_title', array(
        array('view', 'koueinavsort&operation=base', '1'),
        array('add', 'koueinavsort&operation=create'),
        array('modify', "koueinavsort&operation=modify"),
    ));
    showtips('kouei_navsort_base_tips');
    global $_G;
    $page = max(1, $_G['page']);
    $start_limit = ($page - 1) * $_G['tpp'];

    $sort_count = C::t('forum_kouei_blocksort')->count();
    $sorts = C::t('forum_kouei_blocksort')->fetch_by_pagenum($start_limit, $_G['tpp']);
    $multipage = multi($sort_count, $_G['tpp'], $page, ADMINSCRIPT . "?action=koueinavsort&operation=base");

    showformheader("koueinavsort&operation=clean");
    showtableheader(cplang('kouei_navsort_count', array('sort_count' => $sort_count)));
    if ($sort_count) {
        showsubtitle(array('', 'kouei_navsort_order_id', 'kouei_navsort_id', 'kouei_navsort_name', ''));
        echo getSorts($sorts);
        showsubmit('deletesubmit', cplang('delete'), '', '', $multipage);
    }
    showformfooter();

} elseif ($operation == 'create') {
    showsubmenu('kouei_navsort_title', array(
        array('view', 'koueinavsort&operation=base'),
        array('add', 'koueinavsort&operation=create', '1'),
        array('modify', "koueinavsort&operation=modify"),
    ));
    showtips('kouei_navsort_add_tips');
    $lang = lang('admincp');
    showtagheader('div', 'koueinavsort', true);
    showformheader('koueinavsort&operation=store');
    showtableheader();
    showsetting('kouei_navsort_order_id', 'order_id', '', 'text', '', 0, $lang['order_id_comment']);
    showsetting('kouei_navsort_sort_name', 'sort_name', '', 'text', '', 0, $lang['sort_name_comment']);
    showsubmit('submit');
    showtablefooter();
    showformfooter();
    showtagfooter('div');

} elseif ($operation == 'store'){
    if ($_POST['order_id'] && $_POST['sort_name']) {
        $sort_data = array(
            'order_id' => intval($_POST['order_id']),
            'sort_name' => dhtmlspecialchars(trim($_POST['sort_name'])),
        );
        if (!is_numeric($sort_data['order_id']) || $sort_data['order_id'] == 0 || strlen($sort_data['sort_name']) > 10) {
            cpmsg_error('kouei_navigation_type_error', 'action=koueinavsort&operation=create');
        }
        $result = C::t('forum_kouei_blocksort')->insert($sort_data);
        $result ? cpmsg('create_block_success', 'action=koueinavsort&operation=base', 'succeed') : cpmsg_error('unknow_error');
    }
} elseif ($operation == 'modify') {
    $sortid = intval($_GET['sortid']);
    if (!$sortid || $sortid == 0) {
        cpmsg_error('nothing_modify', 'action=koueinavsort&operation=base');
    }
    showsubmenu('kouei_navsort_title', array(
        array('view', 'koueinavsort&operation=base'),
        array('add', 'koueinavsort&operation=create'),
        array('modify', "koueinavsort&operation=modify", '1'),
    ));
    showtips('kouei_navsort_modify_tips');

    $sort = C::t('forum_kouei_blocksort')->fetch_all($sortid);
//    dd($sort);
    $lang = lang('admincp');
    showtagheader('div', 'koueinavsort', true);
    showformheader('koueinavsort&operation=update');
    showtableheader();
    showsetting('kouei_navsort_id', 'sort_id', $sortid, 'text', 'readonly');
    showsetting('kouei_navsort_order_id', 'order_id', $sort[$sortid]['order_id'], 'text', '', 0, $lang['order_id_comment']);
    showsetting('kouei_navsort_sort_name', 'sort_name', $sort[$sortid]['sort_name'], 'text', '', 0, $lang['sort_name_comment']);
    showsubmit('submit');
    showtablefooter();
    showformfooter();
    showtagfooter('div');
    
} elseif ($operation == 'update') {
    $sortid = intval($_POST['sort_id']);
    if (!$sortid || $sortid == 0) {
        cpmsg_error('unknow_error');
    }
    $sort_data = array(
        'order_id' => intval($_POST['order_id']),
        'sort_name' => dhtmlspecialchars(trim($_POST['sort_name'])),
    );
    if (!is_numeric($sort_data['order_id']) || $sort_data['order_id'] == 0 || strlen($sort_data['sort_name']) > 10) {
        cpmsg_error('kouei_navigation_type_error', 'action=koueinavsort&operation=create');
    }
    $result = C::t('forum_kouei_blocksort')->update($sortid, $sort_data);
    $result ? cpmsg('update_block_success', 'action=koueinavsort&operation=base', 'succeed') : cpmsg_error('unknow_error');

} elseif ($operation == 'clean') {
    if (empty($_GET['sortidarray'])) {
        cpmsg_error('unknow_error');
    }
    $sort_ids = dhtmlspecialchars($_GET['sortidarray']);
    $detele_sorts = C::t('forum_kouei_blocksort')->delete_by_sids($sort_ids);
    $update_blocks = C::t('forum_kouei_block')-> update_by_sids($sort_ids);
    cpmsg(cplang('kouei_sort_clean', array('delete_sorts' => $detele_sorts, 'update_blocks' => $update_blocks)), 'action=koueinavsort&operation=base', 'succeed');
}

function getSorts ($params) {
    global $_G, $lang;
    $sorts = '';
    if (!empty($params)) {
        foreach ($params as $param) {
            $sorts .= showtablerow('', array(), array(
                "<input type=\"checkbox\" name=\"sortidarray[]\" value=\"$param[sort_id]\" class=\"checkbox\">",
                $param['order_id'],
                $param['sort_id'],
                $param['sort_name'],
                "<a href='?action=koueinavsort&operation=modify&sortid=" . $param['sort_id'] . "'>$lang[edit]</a>"
            ), TRUE);
        }
    }
    return $sorts;
}

?>