<?php
/**
 * 翼购开发中心
 * ============================================================================
 * * 版权所有 2016 晋江铭翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.mingyie.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: 天机子 $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$lang = lang('plugin/mingyie_alipay');

showtableheader($lang['fangfa']);
echo '<tr><td>'.$lang['help'].'</td></tr>';
echo '<tr><td>'.$lang['helpa'].'</td></tr>';
showtablefooter();
showtableheader($lang['sousuo']);

if(submitcheck('youkasubmit')){
    //搜索结果展示
    showsubtitle(array(
        'id',
        $lang['username'],
        $lang['money'],
        $lang['czjifen'],
        $lang['type'],
        $lang['zhuangt'],
        $lang['jftype'],
        $lang['tjtime'],
        $lang['fktime'],
    ));

    $status = intval($_POST['status']);
    $post_start = strtotime(addslashes($_POST['post_start']));
    $post_end = strtotime(addslashes($_POST['post_end']))+86400;
    $sys_start = strtotime(addslashes($_POST['sys_start']));
    $sys_end = strtotime(addslashes($_POST['sys_end']))+86400;
    //
    $sql = 'select * from '.DB::table('myalipay_pay_log').' where 1 ';
    if($status==1){
        $sql .= ' and status=0 and channel=1 ';
    }elseif($status==2){
        $sql .= ' and status=1 and channel=1 ';
    }
    if($post_start && $post_end){
        $sql .= ' and post_time between '.$post_start.' and '.$post_end;
    }
    if($sys_start && $sys_end){
        $sql .= ' and completiontime between '.$sys_start.' and '.$sys_end;
    }
    $sql .=' order by post_time desc ';
    $query = DB::query($sql);
	
    while($rs = DB::fetch($query)){
        $user = getuserbyuid($rs['uid']);
        $rs['username'] = $user['username'];
        $rs['credit'] = $_G['setting'][extcredits][$rs['description']][title];
        $rs['show_post_time'] = date('Y-m-d H:i:s',$rs['post_time']);
        $rs['show_sys_time'] = $rs['completiontime']?date('Y-m-d H:i:s',$rs['completiontime']):'';
        $rs['channel_name'] = $rs['channel']==1?$lang['chongz']:$lang['goumai'];
        if($rs['channel']==1)
            $rs['show_status'] = $rs['status']==0?$lang['weifk']:$lang['yifuk'];
        
        $result[] = $rs;
    }
    foreach($result as $val){
        showtablerow('',array(),array(
            $val['id'],
            $val['username'],
            $val['price'],
            $val['extcredits'],
            $val['show_status'],
            $val['channel_name'],
            $val['credit'],
            $val['show_post_time'],
            $val['show_sys_time'],
        ));
    }
	
    showtablefooter();
    die;
}


showformheader('plugins&operation=config&do='.$pluginid.'&identifier=mingyie_alipay&pmod=list');

echo '<script type="text/javascript" src="'.$_G['siteurl'].'static/js/calendar.js"></script>
<tr><td colspan="2" class="td27" s="1">'.$lang['dingzhuant'].':</td></tr>
<tr class="noborder" ><td class="vtop rowform">
<select name="status">
    <option value="0" selected>'.$lang['quanbu'].'</option>
    <option value="1" >'.$lang['dengdfk'].'</option>
    <option value="2">'.$lang['payok'].'</option>
    </select>
    </td>
    <td class="vtop tips2" s="1"></td>
    </tr>
<tr><td colspan="2" class="td27" s="1">'.$lang['tjtime'].':</td></tr>
<tr class="noborder" ><td class="vtop rowform">
<input type="text" style="width:100px;" name="post_start" value="" onclick="showcalendar(event, this)">
- <input type="text" style="width:100px;" name="post_end" value="" onclick="showcalendar(event, this)">
</td><td class="vtop tips2" s="1"></td></tr>
<tr><td colspan="2" class="td27" s="1">'.$lang['fktime'].':</td></tr>
<tr class="noborder" ><td class="vtop rowform">
<input type="text" style="width:100px;" name="sys_start" value="" onclick="showcalendar(event, this)">
- <input type="text" style="width:100px;" name="sys_end" value="" onclick="showcalendar(event, this)">
</td><td class="vtop tips2" s="1"></td></tr>
';


showsubmit('youkasubmit',$lang['lijisousuo']);

showformfooter();

showtablefooter();
