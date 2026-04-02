<?php
session_start();
session_commit();
if(!$_SESSION['userUUID']){
    echo "<script>window.location.href = '/'</script>";
    exit("No Permission.");
}
defined('ROOT_PATH') or define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
// 取session的配置和语言包，给页面用
$LANG = $_SESSION['LANG'];
$CONF = $_SESSION['CONF'];

if (empty($CONF)) {
    $CONF = xphp_get_config('app');
    $mouleType = xphp_get_config('module', 'MODULE_TYPE');
    $CONF['MODULE_TYPE'] = $mouleType;

    $tskeType = xphp_get_config('task', 'TASKTYPE');
    $CONF['TASK_TYPE'] = $tskeType;
}

if (empty($LANG)) {
    $user = xphp_get_user_info();
    $LANG = require $_SERVER['DOCUMENT_ROOT'] . '/lang/' . $user['language'] . $CONF['ext'];
}

// xss过滤 只会过滤掉get请求
$_GET = xssFilter($_GET);

function xssFilter($params)
{
    if (is_array($params)) {
        //如果是数组,循环处理每一项
        foreach ($params as $key => $val) {
            //不是字符串或者是json直接返回
            if(!is_string($val) || is_array(json_decode($val, true))) continue;
            $params[$key] = xssFilter($val);
        }
    } else {
        $params = string_remove_xss($params);
        if (PHP_VERSION < '5.4.0') {
            $params = htmlspecialchars($params);
        } else {
            if (!defined('CHARSET') || (strtolower(CHARSET) == 'utf-8')) {
                $charset = 'UTF-8';
            } else {
                $charset = 'ISO-8859-1';
            }
            $params = htmlspecialchars($params, ENT_NOQUOTES, $charset);
        }
    }
    return $params;
}

/**
 * 移除特殊标签
 * @param string $html
 * @return mixed
 */
function string_remove_xss($html)
{
    preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

    $searchs[] = '<';
    $replaces[] = '&lt;';
    $searchs[] = '>';
    $replaces[] = '&gt;';

    $skipkeys = array('onabort','onactivate','onafterprint','onafterupdate','onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
        'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload','onbeforeupdate','onblur','onbounce','oncellchange','onchange',
        'onclick','oncontextmenu','oncontrolselect','oncopy','oncut','ondataavailable','ondatasetchanged','ondatasetcomplete','ondblclick',
        'ondeactivate','ondrag','ondragend','ondragenter','ondragleave','ondragover','ondragstart','ondrop','onerror','onerrorupdate',
        'onfilterchange','onfinish','onfocus','onfocusin','onfocusout','onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete',
        'onload','onlosecapture','onmousedown','onmouseenter','onmouseleave','onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel',
        'onmove','onmoveend','onmovestart','onpaste','onpropertychange','onreadystatechange','onreset','onresize','onresizeend','onresizestart',
        'onrowenter','onrowexit','onrowsdelete','onrowsinserted','onscroll','onselect','onselectionchange','onselectstart','onstart','onstop',
        'onsubmit','onunload','javascript','script','eval','behaviour','expression','style','class');
    $skipstr = implode('|', $skipkeys);
    $allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote';
    
    if ($ms[1]) {
        $ms[1] = array_unique($ms[1]);
        foreach ($ms[1] as $value) {
            $searchs[] = "&lt;".$value."&gt;";
            $value = str_replace(array('\\', '/*'), array('.', '/.'), $value);
            $value = preg_replace(array("/($skipstr)/i"), '.', $value);
            //由于js会传整个html字符串到php,暂时不过滤html标签
            if (!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
                $value = '';
            }
            $replaces[] = empty($value) ? '' : "<" . str_replace('&quot;', '"', $value) . ">";
        }
    } else {
        $html = preg_replace(array("/($skipstr)/i"), '.', $html);
    }

    $html = str_replace($searchs, $replaces, $html);

    return ($html); // 回车转换
}
