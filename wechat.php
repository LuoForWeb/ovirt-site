<?php

/**
 * 获取用户openid
 */
include_once $_SERVER['DOCUMENT_ROOT'].'/api/load.php';

$CONF = Xphp::$_config;
$LANG = require_once './lang/'.$CONF['lang'].$CONF['ext'];

session_start();
$utils = Xphp::instance('Utils');
$that = Xphp::instance('OPHandler');
$op = $_GET['op'] ?? 'diaplay';

if (!empty($_GET['param'])) {
    // 解密参数
    $params = $utils->xphp_short_decrypt($_GET['param']);

    if (empty($params)) {
        echo backHtml('/img/platform/alarm/icon_error.png', $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_TIMEOUT_TITLE'], $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_TIMEOUT_CONTENT']);
        die;
    }

    $params = json_decode($params, true);
    if (empty($params['userUuid']) || empty($params['template_url']) || empty($params['appid']) ||empty($params['appsecret'])) {
        echo backHtml('/img/platform/alarm/icon_error.png', $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_TIMEOUT_TITLE'], $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_TIMEOUT_CONTENT']);
        die;
    }
    $userUuid = $params['userUuid'];
    $template_url = $params['template_url'];
    $appid = $params['appid'];
    $appsecret = $params['appsecret'];
    $_SESSION['userUuid'] = $userUuid;
    $_SESSION['template_url'] = $template_url;
    $_SESSION['appid'] = $appid;
    $_SESSION['appsecret'] = $appsecret;
}
$code = $_GET['code'];
$openid = $_GET['_openid'];

$appid = $_SESSION['appid'];
$appsecret = $_SESSION['appsecret'];
$userUuid = $_SESSION['userUuid'];
$template_url = $_SESSION['template_url'];

if (empty($appid) || empty($appsecret) || empty($userUuid)) {
    echo backHtml('/img/platform/alarm/icon_error.png', $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_TIMEOUT_TITLE'], $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_TIMEOUT_CONTENT']);
    die;
}

if (!$openid && $op != 'auth') {
    $op = 'bind';
}

if ($op == 'display') {
    // 绑定信息 $openid
    // 存储获取到的openid到表中
    // ... bd_system_settings  settings_type 为 SYSTEM_WECHAT_OPENID（10）
    $sql = "select * from bd_system_settings where settings_type = 10";
    $wechats = $that->dbSelect($sql);
    if (!empty($_GET['mode']) && $_GET['mode'] == 1) {
        $openid = json_decode($utils->xphp_short_decrypt($_GET['data']), true);
    } else {
        $openid = $_SESSION['user_temp'];
    }

    if(!empty($wechats)){
        $wechatContent = json_decode($wechats[0]['settings_content'],true);
        // 需要判断下本次的appid是否和之前的一致，不一致就删除openid，一致就追加更新
        if ($wechatContent['appid'] != $appid) {
            $wechatContent['openid'] = [];
        }
        // 判断下是否存在里面，存在就不需要追加
        if (!empty($wechatContent['openid'])) {
            $opeid_arr = array_column($wechatContent['openid'], 'openid');
            if (!in_array($openid['openid'], $opeid_arr)) {
                $wechatContent['openid'][] = $openid;
                // 保存更新数据
                $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = 10";
                $that->dbExec($sql, [json_encode($wechatContent), date('Y-m-d H:i:s')]);
            }
        } else {
            $wechatContent['openid'][] = $openid;
            // 保存更新数据
            $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = 10";
            $that->dbExec($sql, [json_encode($wechatContent), date('Y-m-d H:i:s')]);
        }
    } else {
        // 插入数据
        $sql = "INSERT INTO `bd_system_settings` (`settings_type`, `settings_content`, `modify_time`, `user_uuid`) VALUES (10, ?, ?, ?)";
        $settings_content = [
            'appid' => $appid,
            'appsecret' => $appsecret,
            'openid'    => [$openid],
            'wechatFlag'    => false
        ];
        $that->dbExec($sql, [json_encode($settings_content), date('Y-m-d H:i:s'),  $userUuid ?? '']);
    }

    // 输出内容
    echo backHtml('/img/platform/alarm/icon_success.png', $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_SUCCESS_TITLE'], $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_SUCCESS_CONTENT']);

    /* sleep(1);
    // 刷新输出缓冲
    flush();
    ob_flush();
    header('Location: '. $template_url);*/
}

if ($op == 'bind') {
    // 获取openid
    header('Location: ' . $template_url . '/wechat.php?op=auth');
}

if ($op == 'auth') {
    // 授权
    $weChat = $utils->wechat ( 'Oauth', ['appid'=>$appid, 'appsecret'=>$appsecret]);

    if ($code) {
        $accessToken = $weChat->getOauthAccessToken ();
        if ($accessToken) {
            $token = $accessToken['access_token'];
            $openid = $accessToken ['openid'];
            // 获取用户的信息
            $user_info = $weChat->getOauthUserInfo($token, $openid);
            $_SESSION ['user_temp'] = [
                'openid' => $openid,
                'nickname' => $user_info['nickname'],// 昵称
                'headimgurl' => $user_info['headimgurl'], // 头像
            ];
            header('Location: ' . $template_url . '/wechat.php?op=display&_openid=' . $openid);
        } else {
            echo backHtml('/img/platform/alarm/icon_error.png', $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_FAIL_TITLE'], $LANG['UI_SETTINGS_NOTICE_WECHAT_AUTH_FAIL_CONTENT']);
        }
    } else {
        $url = $weChat->getOauthRedirect ( $template_url . '/wechat.php?op=auth', '', 'snsapi_userinfo' );
        header('Location: ' . $url);
    }
}

function backHtml($icon, $title, $content) {
    return '<div style="margin-top: 100px;">
        <img src="'.$icon.'" style="
    display: block;
    width: 44%;
    margin-left: 28%;
">
        <div style="
    text-align: center;
    height: 22px;
    font-size: 50px;
    font-weight: bold;
    color: #333333;
    line-height: 22px;
    margin-top: 46px;
">'.$title.'</div>
        <div style="
    width: 59%;
    height: 118px;
    font-size: 40px;
    font-weight: 400;
    color: #999999;
    line-height: 48px;
    margin: 68px auto;
    text-align: center;
">'.$content.'</div>
    </div>';
}
