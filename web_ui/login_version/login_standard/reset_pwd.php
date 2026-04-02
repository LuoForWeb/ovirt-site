<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    include_once $_SERVER['DOCUMENT_ROOT'].'/web_ng/api/public/load.php';
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $app = xphp_get_config('app');
    if($_SESSION['language'] != null){
        $LANG = require $rootPath .'/lang/'.$_SESSION['language'].$app['ext'];
        $lang = $_SESSION['language'];
    }else{
        $LANG = require $rootPath .'/lang/'.$app['lang'].$app['ext'];
        $lang = $app['lang'];
    }
    $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
    /**
     * 特殊配置,主要用于第三方OEM版本
     * 所有配置文件均在根目录下special文件夹,包括配置文件,logo,图片等所有信息
     */
    $configFile = $app['SPECIAL_DIR'] . $app['SPECIAL_CONFIG'];
    if(file_exists($configFile)){
        $content = file_get_contents($configFile);
        $content = json_decode($content, true);
        setSpecialConfigRecursiveLogin($app, $content);
    }
    function setSpecialConfigRecursiveLogin(&$arrA, $arrB){
        foreach ($arrB as $key => $value){
            if(is_array($value)){
                setSpecialConfigRecursiveLogin($arrA[$key], $value);
            }else{
                $arrA[$key] = $value;
            }
        }
    }

    ?>
    <title>
        <?php
        session_start();
        if (file_exists($app['SYSTEM_NAME_FILE'])) {
            // 如果自定义系统名称存在
            echo file_get_contents($app['SYSTEM_NAME_FILE']);
        } else {
            // 如果自定义系统名称不存在
            echo $app['SYSTEM_INFO']['system_name'];
        }
        //  提示信息
        $tips=$LANG['UI_RESETPWD_TIPS'];
        //        获取接收到邮件的时间
        $urlInfo=$_GET['key'];
        setcookie('resetPwdToken', $urlInfo, [
            'expires' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => [],
        ]);
        $token = v1_decrypt($urlInfo);
        $token=json_decode($token,true);
        $_SESSION['time']=$token['time']; //发送邮件的时间
        //        加载此页面的时间
        $_SESSION['nowTime']=time(); //进入页面的时间
        $_SESSION['timeout']=xphp_get_config('email','EMAIL')['sendTimeOut'];
        //        获取用户名
        $username=$token['username'];
        ?>
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="renderer" content="webkit">
    <link href="/assets/global/css/gfonts1.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link href="/css/platform/standard_login.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="" style="height: 100vh">
    <div class="content">
        <div class="login-content_div">
            <div class="backupImg">
                <?php
                if($lang == "zh-cn" || $lang == "zh-tw"){
                    echo '<img src="/img/platform/login/standard_login/backup.svg" alt="">';
                }
                else{
                    echo '<img src="/img/platform/login/standard_login/backup_en.svg" alt="">';
                }
                ?>
            </div>
            <div class="login">
                <div class="logo">
                    <a href="<?php echo $app['REMOTE']['website']?>" target="_black">
                        <?php
                        if($app['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType){
                            //如果是免费版,使用专用logo
                            echo '<img src="./img/platform/logo-free.png" alt="" class="logo-default loginlogosizefree">';
                        }else{
                            //如果是其他版本
                            if(file_exists($app['SPECIAL_DIR'] . "logo.png")){
                                $logo = "/special/logo.png";
                            }else{
                                $logo = "/img/platform/login/standard_login/vinchin_logo.svg";
                            }
                            echo '<img src="' . $logo . '" alt="" class="logo-default loginlogosize">';
                        }
                        ?>
                    </a>
                </div>
                <div class="">
                    <h3 class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></h3>
                    <div class="tip">
                        <?php echo $tips?>
                    </div>
                    <div class="form-actions">
                        <a href="/login.php">
                            <button id="returnLogin" class="btn display-none">
                                <?php echo $LANG['UI_FORGETPWD_RETURNLOGIN'];?>
                            </button>
                        </a>
                    </div>
                    <div style="display: none" class="sessionTime"><?php echo $_SESSION['time'] ?></div>
                    <div style="display: none" class="sessionNowTime"><?php echo $_SESSION['nowTime']?></div>
                    <div style="display: none" class=sessionTimeOut><?php echo $_SESSION['timeout']?></div>
                    <form class="resetpwd-form" method="post">
                        <div class="form-group">
                            <input type="text"  class="form-control" name="username" maxlength="64" readonly="readonly" autocomplete="off" value="<?php echo $username ?>">
                        </div>
                        <div class="form-group">
                            <div class="input-icon form-input form-password">
                                <i class="eye">
                                    <img src="/img/platform/login/standard_login/eye.svg" alt="">
                                </i>
                                <i class="eye-close">
                                    <img src="/img/platform/login/standard_login/eye-close.svg" alt="">
                                </i>
                                <i id="capslock-warning" class="capslock-icon" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>">
                                    <img src="/img/platform/login/standard_login/capslock.svg" alt="">
                                </i>
                                <input type="password" autocomplete="off" placeholder="<?php echo $LANG['UI_RESETPWD_ENTER_NEWPASSWORD'];?>"  id="password" class="form-control focus-control toggle-password" name="password" autocomplete="off" maxlength="32" oncopy="return false" oncut="return false" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-icon form-input form-repassword">
                                <i class="eye">
                                    <img src="/img/platform/login/standard_login/eye.svg" alt="">
                                </i>
                                <i class="eye-close">
                                    <img src="/img/platform/login/standard_login/eye-close.svg" alt="">
                                </i>
                                <i id="re-capslock-warning" class="capslock-icon" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>">
                                    <img src="/img/platform/login/standard_login/capslock.svg" alt="">
                                </i>
                                <input type="password" autocomplete="off" placeholder="<?php echo $LANG['UI_RESETPWD_REENTER_NEWPASSWORD']; ?>"  id="repassWord" class="form-control focus-control toggle-repassword" name="resetPassWord" autocomplete="off" maxlength="32" onpaste="return false" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn" id="resetBtn"><?php echo $LANG['UI_RESETPWD_YES']; ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright">
        <?php echo $app['SYSTEM_INFO']['copyright'] . " &copy; " . $app['SYSTEM_INFO']['years'] . " ".
            $app['SYSTEM_INFO']['company'] . $app['SYSTEM_INFO']['version'];
        ?>
        <p>
            <?php echo $app['SYSTEM_INFO']['recommend'];?>
        </p>
    </div>
</div>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<?php
if($lang != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="/assets/global/plugins/jquery-validation/js/localization/messages-'.
        $lang.'.js" type="text/javascript"></script>';
}
?>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="/lang/<?php echo $lang?>.js" type="text/javascript"></script>
<script src="/scripts/conf/config.js" type="text/javascript"></script>
<script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="/scripts/public/public.js" type="text/javascript"></script>
<script src="/scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="/scripts/libs/base64.min.js" type="text/javascript"></script>
<script src="/scripts/libs/md5.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="/scripts/platform/reset_pwd.js"></script>
</body>
</html>