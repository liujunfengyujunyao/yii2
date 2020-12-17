<?php
/*halt*/
    function halt($var)
    {
        echo "<pre>";
        var_dump($var);
        echo "</pre>";
        die;
    }
/* 友好显示var_dump
* @param [type] $var
* @param bool $echo
* @param [type] $label
* @param bool $strict
* @author BinLu <lubb1008@163.com>
 * @Date 2019-12-18
*/
function dump($var, $echo = true, $label = null, $strict = true)
{
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo $output;
        return null;
    } else
        return $output;
}
/**************字符串、数组、处理函数*********************/

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 * @param string $str 要分割的字符串
 * @param string $glue 分割符
 * @return array
 * @author BinLu <lubb1008@163.com>
 */
function str2arr(string $str, $glue = ',')
{
    return explode($glue, $str);
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param array $arr 要连接的数组
 * @param string $glue 分割符
 * @return string
 * @author BinLu <lubb1008@163.com>
 */
function arr2str(array $arr, $glue = ',')
{
    return implode($glue, $arr);
}


/**
 * 获取中文字符串长度
 * @param [type] $str
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-18
 */
function zh_cnLength(string $str)
{
    $strcode = mb_detect_encoding($str, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5"));
    return mb_strlen($str, $strcode);
}

/**
 * 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param int $start 开始位置
 * @param [type] $length 截取长度
 * @param string $charset 编码格式
 * @param bool $suffix 截断显示字符
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function msubstr(string $str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }

    return $suffix && $str != $slice ? $slice . '...' : $slice;
}

/**
 * 方法增强，根据$length自动判断是否应该显示...
 * 字符串截取，支持中文和其他编码
 * 改写自上述方法
 * @param string $str 需要转换的字符串
 * @param int $start 开始位置
 * @param [type] $length 截取长度
 * @param string $charset 编码格式
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function msubstr_hbb($str, $start = 0, $length, $charset = "utf-8")
{
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);

        $slice = join("", array_slice($match[0], $start, $length));
    }
    return (strlen($str) > strlen($slice)) ? $slice . '...' : $slice;
}

/**
 * 对数组进行排序
 * @param [type] $list 查询结果
 * @param [type] $field 排序的字段名
 * @param string $sortby 排序类型 asc正向排序 desc逆向排序 nat自然排序
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function list_sort_by($list, $field, $sortby = 'asc')
{
    if (is_array($list)) {
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc': // 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/**
 * 阿拉伯数字转中文表述，
 * 如101转成一百零一
 * @param [type] $number
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function num2cn($number)
{
    $number = intval($number);
    $capnum = array(
        "零",
        "一",
        "二",
        "三",
        "四",
        "五",
        "六",
        "七",
        "八",
        "九"
    );
    $capdigit = array(
        "",
        "十",
        "百",
        "千",
        "万"
    );

    $data_arr = str_split($number);
    $count = count($data_arr);
    for ($i = 0; $i < $count; $i++) {
        $d = $capnum[$data_arr[$i]];
        $arr[] = $d != '零' ? $d . $capdigit[$count - $i - 1] : $d;
    }
    $cncap = implode("", $arr);

    $cncap = preg_replace("/(零)+/", "0", $cncap); // 合并连续“零”
    $cncap = trim($cncap, '0');
    $cncap = str_replace("0", "零", $cncap); // 合并连续“零”
    $cncap == '一十' && $cncap = '十';
    $cncap == '' && $cncap = '零';
    // echo ( $data.' : '.$cncap.' <br/>' );
    return $cncap;
}

/**
 * 将星期语义化输出
 * @param [type] $number
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function week_name($number = null)
{
    if ($number === null)
        $number = date('w');

    $arr = array(
        "日",
        "一",
        "二",
        "三",
        "四",
        "五",
        "六"
    );

    return '星期' . $arr[$number];
}

/**
 * 日期转换成星期几
 * @param [type] $day
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function day2week($day = null)
{
    $day === null && $day = date('Y-m-d');
    if (empty($day))
        return '';

    $number = date('w', strtotime($day));

    return week_name($number);
}

/**
 * select返回的数组进行整数映射转换
 * @param array $map
 * 映射关系二维数组 array(
 *     	'字段名1'=>array(映射关系数组),
 *     	'字段名2'=>array(映射关系数组),
 *     	......
 * )
 * @return array array(
 *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *      ....
 * )
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function int_to_string(&$data, $map = array('status' => array(0 => '删除', 1 => '正常')))
{
    if ($data === false || $data === null) {
        return $data;
    }
    $data = (array) $data;
    foreach ($data as $key => $row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $data[$key][$col . '_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}

/**
 * 将xml转换为数组
 * @param [type] $xml 需要转化的xm
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-03
 */
function xml_to_array($xml)
{
    $ob = simplexml_load_string($xml);
    $json = json_encode($ob);
    $array = json_decode($json, true);
    return $array;
}

/**
 * 将数组转化成xml
 * @param [type] $data 需要转化的数组
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-03
 */
function data_to_xml($data)
{
    if (is_object($data)) {
        $data = get_object_vars($data);
    }
    $xml = '';
    foreach ($data as $key => $val) {
        if (is_null($val)) {
            $xml .= "<$key/>\n";
        } else {
            if (!is_numeric($key)) {
                $xml .= "<$key>";
            }
            $xml .= (is_array($val) || is_object($val)) ? data_to_xml($val) : $val;
            if (!is_numeric($key)) {
                $xml .= "</$key>";
            }
        }
    }
    return $xml;
}

/**
 * 接收xml数据并转化成数组
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-03
 */
function getRequestBean()
{
    // simplexml_load_string() 函数把 XML 字符串载入对象中。如果失败，则返回 false。
    $bean = simplexml_load_string(file_get_contents('php://input'));
    $request = array();
    foreach ($bean as $key => $value) {
        $request[(string) $key] = (string) $value;
    }
    return $request;
}

//清除所有html标签
function clearHtml($content)
{
    $content = strip_tags($content);
    //转换 &nbsp;&gt;等标签
    $content = htmlspecialchars_decode($content, ENT_QUOTES);
    return $content;
}

/**
 * 获取随机字符串
 * @param int $length 长度
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-03
 */
function generatorString($length = 4)
{
    $char = "0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,J,K,M,N,P,Q,R,S,T,U,V,W,X,Y,Z";
    $list = explode(",", $char);
    $randomString = "";
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $list[rand(0, 32)];
    }
    return $randomString;
}

/**
 * 数组中的值是否存在
 * 数值存在 or 不存在的返回值 ‘’
 * @param array $data
 * @param [type] $key
 * @param string $default
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-03
 */
function isset_value(array $data, $key, $default = "")
{
    if (!empty($data) && isset($data[$key])) {
        return trim($data[$key]);
    } else {
        return $default;
    }
}

/****************************时间相关*******************************/
/**
 * 计算两个日期相隔多少年，多少月，多少天
 * @param [type] $date1
 * @param [type] $date2
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function diffDate(string $date1, string $date2)
{
    $datetime1 = new \DateTime($date1);
    $datetime2 = new \DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    $time['y']         = $interval->format('%Y');
    $time['m']         = $interval->format('%m');
    $time['d']         = $interval->format('%d');
    $time['h']         = $interval->format('%H');
    $time['i']         = $interval->format('%i');
    $time['s']         = $interval->format('%s');
    $time['a']         = $interval->format('%a');    // 两个时间相差总天数
    return $time;
}

/**
 * 将间隔时间语义化输出
 * @param [type] $startTime
 * @param [type] $endTime
 * @param [type] $type
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function diffDateSemantic(string $startTime, string $endTime, array $type = ['y', 'm', 'd'])
{
    $map = [
        'y' => '岁',
        'm' => '个月',
        'd' => '天',
        'h' => '时',
        'i' => '分',
        's' => '秒',
        'a' => '天',
    ];
    $diffDateArr = diffDate($startTime, $endTime);
    $sematicTime = '';
    foreach ($type as $value) {
        if (0 == intval($diffDateArr[$value])) {
            continue;
        }
        $sematicTime .= intval($diffDateArr[$value]) . $map[$value];
    }
    return $sematicTime;
}

/**
 * 将字符串时间转换成时间戳
 * @param string $dateTime
 * @author BinLu <lubb1008@163.com>
 * @Date 2020-01-02
 */
function datetime_string2int(string $dateTime)
{
    return strtotime($dateTime);
}

/**
 * 将int类型的时间格式转换成字符串格式
 * @param int $dateTime 0
 * @param string $type Y-m-d H:m:s
 * @author BinLu <lubb1008@163.com>
 * @Date 2020-01-02
 */
function datetime_int2string(int $dateTime = 0, $type = 'Y-m-d H:i:s')
{
    if (empty($dateTime)) {
        $dateTime = time();
    };
    return date($type, $dateTime);
}

/**
 * 获取时间在一天之内的时间范围
 * 开始时间和结束时间
 * @param int $dateTime
 * @author BinLu <lubb1008@163.com>
 * @Date 2020-01-10
 */
function getDateTimeInDay(int $dateTime = 0)
{
    if (!$dateTime) {
        $dateTime = time();
    }
    $start_time = strtotime(date("Y-m-d", $dateTime));
    return [
        'start' => $dateTime,
        'end' => $start_time + 60 * 60 * 24,
    ];
}

/**
 * 获取当前时间所在周的开始和结束时间
 * @param int $dateTime
 * @author BinLu <lubb1008@163.com>
 * @Date 2020-03-06
 */
function getDateTimeInWeek(int $dateTime = 0)
{
    if (!$dateTime) {
        $dateTime = time();
    }
    $startTime = strtotime(date("Y-m-d H:i:s", mktime(0, 0, 0, date("m", $dateTime), date("d", $dateTime) - date("w", $dateTime) + 1 - 7, date("Y", $dateTime))));
    $endTime = strtotime(date("Y-m-d H:i:s", mktime(23, 59, 59, date("m", $dateTime), date("d", $dateTime) - date("w", $dateTime) + 7 - 7, date("Y", $dateTime))));
    return [
        'start' => $startTime,
        'end' => $endTime,
    ];
}

/**
 * 获取当前时间所在月的开始和结束时间
 * @param int $dateTime
 * @author BinLu <lubb1008@163.com>
 * @Date 2020-03-06
 */
function getDateTimeInMonth(int $dateTime = 0)
{
    if (!$dateTime) {
        $dateTime = time();
    }
    $startTime = mktime(0, 0, 0, date("m", $dateTime), 1, date("Y", $dateTime));
    $endTime = mktime(23, 59, 59, date("m", $dateTime), date("t", $startTime), date("Y", $dateTime));
    return [
        'start' => $startTime,
        'end' => $endTime,
    ];
}

/**
 * 获取当前时间所在季度的开始和结束时间
 * @param int $dateTime
 * @author BinLu <lubb1008@163.com>
 * @Date 2020-03-06
 */
function getDateTimeInQuarter(int $dateTime = 0)
{
    if (!$dateTime) {
        $dateTime = time();
    }
    $season = ceil((date('n', $dateTime)) / 3) - 1; //当月是第几季度
    $startTime = strtotime(date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y'))));
    $endTime = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y'))));
    return [
        'start' => $startTime,
        'end' => $endTime,
    ];
}

/**
 *  获取当前时间所在年的开始和结束时间
 * @param int $dateTime
 * @author BinLu <lubb1008@163.com>
 * @Date 2020-03-06
 */
function getDateTimeInYear(int $dateTime = 0)
{
    if (!$dateTime) {
        $dateTime = time();
    }
    $startTime = strtotime(date(date("Y-01-01 00:00:00", strtotime("$dateTime -1 year"))));
    $endTime = strtotime(date('Y-12-31 23:59:59', strtotime("$dateTime -1 year")));
    return [
        'start' => $startTime,
        'end' => $endTime,
    ];
}


/**
 * 金额转人民币大写字符串
 * @param [type] $num
 * @param bool $isCent 是否分值
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-03
 */
function num2rmb($num, $isCent = true)
{
    if ($isCent) {
        $num = $num / 100;
    }
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角圆拾佰仟万拾佰仟亿";
    $num = round($num, 2);
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "oh,sorry,the number is too long!";
    }

    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '圆'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        $num = $num / 10;
        $num = (int) $num;
        if ($num == 0) {
            break;
        }
    }

    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零圆' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j - 3;
            $slen = $slen - 3;
        }
        $j = $j + 3;
    }
    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    } // if there is a '0' on the end , chop it out
    return $c . "整";
}

/**
 * 分页的序号
 * @param [type] $page_current
 * @param [type] $page_size
 * @param [type] $page_total
 * @param [type] $key_index
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-23
 */
function page_xh($page_current, $page_size, $page_total, $key_index)
{
    $xh = ($page_size) * ($page_current - 1) + ($key_index + 1);
    return  str_pad($xh, strlen($page_total), '0', STR_PAD_LEFT);
}

/**************************验证相关*********************************/
/**
 * 正则验证手机号码
 */
function validateMobile($mobile)
{
    if (empty($mobile) || !is_numeric($mobile)) {
        return false;
    }
    // $regular = '#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#';
    $regular = '#^1(0|1|2|3|4|5|6|7|8|9)\d{9}$#'; //1开头的11位数
    return preg_match($regular, $mobile) ? true : false;
}

/**
 * 验证固定电话
 */
function validateTelphone($str)
{
    if (empty($str)) {
        return true;
    }
    return preg_match('/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/', trim($str));
}
/**
 * 验证qq号码
 */
function validateQQ($str)
{
    if (empty($str)) {
        return true;
    }

    return preg_match('/^[1-9]\d{4,12}$/', trim($str));
}

/**
 * 验证邮政编码
 */
function validateZipCode($str)
{
    if (empty($str)) {
        return true;
    }

    return preg_match('/^[1-9]\d{5}$/', trim($str));
}
/**
 * 验证ip
 */
function validateIp($str)
{
    if (empty($str))
        return true;

    if (!preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $str)) {
        return false;
    }

    $ip_array = explode('.', $str);

    //真实的ip地址每个数字不能大于255（0-255）
    return ($ip_array[0] <= 255 && $ip_array[1] <= 255 && $ip_array[2] <= 255 && $ip_array[3] <= 255) ? true : false;
}
/**
 * 验证身份证(中国)
 */
function validateChineseIdCard($str)
{
    $str = trim($str);
    if (empty($str))
        return true;

    if (preg_match("/^([0-9]{15}|[0-9]{17}[0-9a-z])$/i", $str))
        return true;
    else
        return false;
}

/**
 * 验证网址
 */
function validateUrl($str)
{
    if (empty($str))
        return true;

    return preg_match('#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', $str) ? true : false;
}

/***************************IP相关***********************************/
/**
 * 随机Ip
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-03
 */
function randomIp()
{
    $ip_long = array(
        array('607649792', '608174079'), //36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
    );
    $rand_key = mt_rand(0, 9);
    $ip       = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    return $ip;
}


/**
 * 获得用户的真实IP地址
 * @author BinLu <lubb1008@163.com>
 * @Date 2019-12-03
 */
function getRealIp()
{
    static $realip = NULL;
    if ($realip !== NULL) {
        return $realip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr as $ip) {
                $ip = trim($ip);
                if ($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $realip;
}

/**
 * 取出数组指定key
 * @param $data
 * @param $field
 * @return array
 */
function filterField($data, $field)
{
    $filterData = array();
    foreach ($field as $k => $v) {
        if (isset($data[$v])) {
            $filterData[$v] = is_string($data[$v]) ? trim($data[$v]) : $data[$v];
        }
    }
    return $filterData;
}
/**
 * 返回给前端的数据结构
 */
function success($data = [])
{
    // if (!$data) {
    //     $data = ['success' => 1];
    // }
    return json_encode(['result' => 0, 'message' => 'Request successful', 'data' => $data],JSON_UNESCAPED_UNICODE);
}
function json($data = [])
{
    // if (!$data) {
    //     $data = ['success' => 1];
    // }
    return json_encode(['result' => 0, 'message' => 'Request successful', 'data' => $data],JSON_UNESCAPED_UNICODE);
}

/**
 * 给前端返回异常
 * @author whui
 */
function fail($msg, $code = 1, $data = [])
{
    if (!$data) {
        $data = ['fail' => $msg];
    }
    return json_encode(['result' => $code, 'message' => $msg, 'data' => $data],JSON_UNESCAPED_UNICODE);
}

function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}
