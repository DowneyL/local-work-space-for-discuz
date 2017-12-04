<?php
/**
 * @return array
 */
function _config()
{
    global $_G;
    if (!isset($_G['cache']['plugin'])) {
        loadcache('plugin');
    }
    $config = $_G['cache']['plugin']['codepay'];
    return $config;
}

/**
 * @param $str
 * @param int $ec_ratio
 * @return array
 */
function getRules($str, $ec_ratio = 0)
{
    if (empty($str)) return array();
    $rules = explode('
', $str);
    $rules_data = array();
    foreach ($rules as $value) {
        $data = explode('|', $value);
        $money = (float)trim($data[0]);
        if ($money > 0) {
            $key = count($data) == 1 ? ($money * round($ec_ratio, 2)) : (int)$data[1];
            if ($key > 0) $rules_data[(string)$key] = $money;
        }
    }
    return $rules_data;
}

/**
 * @param $codepay_key
 * @return array
 */
function notifyCheck($codepay_key)
{
    $result=array();
    if (!empty($_POST))
    {
        foreach($_POST as $key => $data)
        {
            $_GET[$key] = $data;
        }
    }
    ksort($_GET);
    reset($_GET);
    $sign = '';
    foreach ($_GET AS $key => $val) {
        if ($val == '') continue;
        if ($key != 'sign' && $key != 'sign_type') {
            $sign .= "$key=$val&";
        }
    }
    $sign = substr($sign, 0, -1) . $codepay_key;

    if (md5($sign) != $_GET['sign'] || !$_GET['pay_no'] || !$_GET['money']) {
        $result['sign']=false;
        $result['msg']='sign fail';
    }else{
        $result['sign']=true;
        $result['msg']='success';
    }
    return $result;
}