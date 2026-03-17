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
    // 获取名为 "show" 的参数的值
    $show = $_GET['show'] ?? '';

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
        if (file_exists($app['SYSTEM_NAME_FILE'])) {
            // 如果自定义系统名称存在
            echo file_get_contents($app['SYSTEM_NAME_FILE']);
        } else {
            // 如果自定义系统名称不存在
            echo $app['SYSTEM_INFO']['system_name'];
        }
        ?>
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="renderer" content="webkit">
    <link href="/assets/global/css/gfonts1.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/css/platform/main.css" rel="stylesheet" type="text/css" />
    <!-- BEGIN THEME STYLES -->
    <link href="/assets/global/css/plugins.css" rel="stylesheet" type="text/css" />
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="/favicon.ico" />
    <link href="/css/platform/standard_login.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="" style="height: 100vh">
    <div class="content">
        <div class="login-content_div">
            <div class="sendSuccessContent" style="display: <?php echo $show == 'send_email' ? 'block' : 'none'; ?>">
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
                        <div class="contentTip">
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
                            <h3 class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></h3>
                            <div class="tips">
                                <div class="email"><img src="/img/platform/login/standard_login/email.svg" alt=""></div>
                                <div class="emailTips">
                                    <?php echo $LANG['UI_EMAIL_SENDSUCCESS'];?>
                                    <?php echo $LANG['UI_EMAIL_TO_EMAILBOX']; ?>
                                </div>
                            </div>
                            <div class="actions">
                                <div class="returnBack"><a href="./forget_pwd.php"><?php echo $LANG['UI_EMAIL_RETURNBACK']?></a></div>
                                <div class="returnLogin"><a href="/login.php"><?php echo $LANG['UI_EMAIL_RETURNLOGIN']?></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="reset-content" style="display: <?php echo $show == 'reset_success' ? 'block' : 'none'; ?>">
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
                        <div class="contentTip">
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
                            <h3 class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></h3>
                            <div class="tips">
                                <div class="success"><img src="/img/platform/login/standard_login/resetPwdSuccess.svg" alt=""></div>
                                <div class="successTips"> <?php echo $LANG['UI_RESETPWDSUCCESS_TIPS']?>
                                    <br><span class="second"></span><?php echo $LANG['UI_RESETPWDSUCCESS_JUMP'];?>
                                </div>
                            </div>
                            <div class="actions">
                                <div class="returnBack"><a href="./forget_pwd.php"><?php echo $LANG['UI_EMAIL_RETURNBACK']?></a></div>
                                <div class="returnLogin"><a href="/login.php"><?php echo $LANG['UI_EMAIL_RETURNLOGIN']?></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<?php
if($lang != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="/assets/global/plugins/jquery-validation/js/localization/messages-'.
        $lang.'.js" type="text/javascript"></script>';
}
?>
<script src="/lang/<?php echo $lang?>.js" type="text/javascript"></script>
<script src="/scripts/platform/reset_pwd_success.js"></script>
</body>
</html>