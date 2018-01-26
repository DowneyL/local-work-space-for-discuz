<?php
require 'inc.php';
require_once libfile('function/discuzcode');

$token = $_POST['token'];
$result = WmApiLib::check_token($token);

$tid = $_POST['tid'];
if (empty($tid) || !is_numeric($tid)) {
    WmApiError::display_result('param_error', '');
    exit;
}

$sql_where = ' where invisible>=0 ';
$sql_where = $sql_where . ' and tid=' . $tid;


$page_size = $_GET['page_size'];
if (!empty($page_size)) {
    if (!is_numeric($page_size)) {
        WmApiError::display_result('param_error', '');
        exit;
    }
} else {
    $page_size = 5;
}

$page_index = $_GET['page_index'];
if (!empty($page_index)) {
    if (!is_numeric($page_index)) {
        WmApiError::display_result('param_error', '');
        exit;
    }
} else {
    $page_index = 0;
}

$sql_limit = ' order by dateline limit ' . ($page_index * $page_size) . ', ' . $page_size;

$resp_data = array();
$post_list = array();
$forum_post_data = DB::fetch_all("SELECT * FROM " . DB::table('forum_post') . $sql_where . $sql_limit);
foreach ($forum_post_data as &$value) {
    // 修改字段
    $value['create_time'] = date('Y-m-d', $value['dateline']);
    $value['author_avatar'] = WmApiLib::get_user_avatar($value['authorid']);

    if ($value['first'] != 1) {
        array_push($post_list, $value);
    }
}

$resp_data['post_list'] = $post_list;

$thread_data = DB::fetch_first("SELECT * FROM " . DB::table('forum_thread') . ' where tid=' . $tid);
// 修改字段
$thread_data['create_time'] = date('Y-m-d', $thread_data['dateline']);
$thread_data['author_avatar'] = WmApiLib::get_user_avatar($thread_data['authorid']);

$first_post_data = DB::fetch_first("SELECT * FROM " . DB::table('forum_post') . " where first=1 and tid=" . $tid);
$pid = $first_post_data['pid'];

#$thread_data['message'] = $first_post_data['message'];
$thread_data['message'] = discuzcode($first_post_data['message'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);


// 获取版块
$forum_forum_data = DB::fetch_first("SELECT * FROM " . DB::table('forum_forum') . " WHERE status=1 and fid=" . $thread_data['fid']);
if ($forum_forum_data) {
    $thread_data['fid_name'] = $forum_forum_data['name'];
}

if ($thread_data['attachment'] == 2) {
    $image_list = array();
    // 获取图片
    if ($thread_data['attachment'] == 2) {
        $forum_attachment = DB::fetch_all("SELECT * FROM " . DB::table('forum_attachment_' . ($tid % 10)) . " where pid=" . $pid);
        foreach ($forum_attachment as &$image_item) {
            $image_url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . dirname($_SERVER['PHP_SELF']) . '/get_image.php?file_url=' . $image_item['attachment'];
            array_push($image_list, $image_url);
        }
        $thread_data['image_list'] = $image_list;
    }
}

$resp_data['thread_data'] = $thread_data;

if ($_POST['new_reader'] == 1) {
    // 更新阅读数
    DB::query("update " . DB::table('forum_thread') . ' set views=views+1 where tid=' . $tid);
}

WmApiError::display_result('ok', $resp_data);
