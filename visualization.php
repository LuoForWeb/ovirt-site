<?php
session_start();
$_SESSION['ROOTPATH'] = getcwd() . "/";

if(empty($_SESSION['userUUID'])){
    header('HTTP/1.1 301 Moved Permanently');
    header("Location:./login.php");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'].'/api/load.php';
$CONF = Xphp::$_config;

$systemHandler = Xphp::instance('SystemHandler');
$softwareType = $systemHandler->getSoftwareType();

/**
 * 特殊配置,主要用于第三方OEM版本
 * 所有配置文件均在根目录下special文件夹,包括配置文件,logo,图片等所有信息
 */
$configFile = $CONF['SPECIAL_DIR'] . $CONF['SPECIAL_CONFIG'];
if(file_exists($configFile)){
    $content = file_get_contents($configFile);
    $content = json_decode($content, true);
    setSpecialConfigRecursiveScreen($CONF, $content);
}

function setSpecialConfigRecursiveScreen(&$arrA, $arrB){
    foreach ($arrB as $key => $value){
        if(is_array($value)){
            setSpecialConfigRecursiveScreen($arrA[$key], $value);
        }else{
            $arrA[$key] = $value;
        }
    }
}

//特殊处理自动登录的情况，适用于文件客户端不用登录可以直接通过特定的链接进入系统首页，跳过WEB系统的登录。
function clientLoginScreen($agentuuid, $conf){
    if(!empty($agentuuid)){
        $url = "http://localhost/api/?k=" . $conf . "&m=4&f=fileClientLogin&agentuuid=" . $agentuuid;
        $fileClientLogin = file_get_contents($url);

        //如果通过agentuuid登录成功，把获取的session解码
        if($fileClientLogin){
            $_SESSION = json_decode($fileClientLogin, true);
            $_SESSION['ROOTPATH'] = getcwd() . "/";
        }else{
            header('Location: ./login.php');
        }
    }
}


//根据后台get到的agentuuid得到对应的用户登录到web控制台首页
$agentuuid = $_GET['agentuuid'];
clientLoginScreen($agentuuid,$CONF['API_MAGIC']);
session_commit();
if($_SESSION['language']){
    $userLang = $_SESSION['language'];
}else{
    $userLang = "zh-cn";
}
$LANG = require './lang/' . $userLang . '.php';


$settingsHandler = Xphp::instance('SettingsHandler');
$visualConf = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['VISUAL']);

$visualConf = json_decode($visualConf[0]['settings_content'], true);
$title = $visualConf['config']['title'];

?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <meta charset="utf-8"/>
    <title>
        <?php
        if(file_exists($CONF['SYSTEM_NAME_FILE'])){
            //如果自定义系统名称存在
            echo file_get_contents($CONF['SYSTEM_NAME_FILE']);
        }else{
            //如果自定义系统名称不存在
            echo $CONF['SYSTEM_INFO']['system_name'];
        }
        ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description"/>
    <meta content="vinchin.com" name="author"/>
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/css/gfonts1.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/time-counter/jquery.countdown.timer.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/jcountdown/jcountdown.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
    <!-- END GLOBAL MANDATORY STYLES -->

    <!--Required plugins CSS-->

    <?php
    if($_SESSION['language']){
        $userLang = $_SESSION['language'];
    }else{
        $userLang = "zh-cn";
    }
    echo '<link href="./css/platform/lang/' . $userLang . '.css" rel="stylesheet" type="text/css"/>';
    ?>
    <link href="/css/visualization/visual.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
</head>

<body>

<!-- BEGIN CONTENT -->
<div class="visual">
    <!-- <a style="position:absolute;top: 15px; right:15px;text-decoration:none;cursor:pointer;" id="toFull" ><img style="width:25px;" src="../img/visualization/toFull.png"></a>
    <a style="position:absolute;top: 15px; right:15px;text-decoration:none;cursor:pointer;" class="display-none" id="toNormal" ><img style="width:25px;" src="../img/visualization/toNormal.png"></a> -->
    <img id="visual-center" src="../img/visualization/visual-center.png">
    <div id="visual-title">
        <h3><?php echo $title?></h3>
        <canvas style="position: absolute;top: 50px;right: 260px;" id="myCanvas" width="34" height="34"></canvas>
        <div id="systemTime" >
        </div>
    </div>
    <div id="visual-content">
        <div id="content-left" >
            <div id="vcenter_data" class="dataDiv bgline" >
                <div class="swiper-container" style="overflow:hidden;height: 100%">
                    <div class="swiper-wrapper">
                        <div class="vcenterDiv">
                            <h3 ><span><?php echo $LANG['UI_VCENTER_VC']?></span>
                                <img class="slope" src="../img/visualization/slope.png">
                                <img src="../img/visualization/title-line.png">
                            </h3>
                            <div id="vcenterData" class="data-content">
                            </div>
                            <p id="protect-rote" ></p>
                            <div style="top: 200px;left: 10px;" class="dataDes" id="unprotectDes" >
                            </div>
                            <div style="top: 80px;left: 300px;" class="dataDes" id="protectDes" >
                            </div>
                            <div id="vcenterPieChart" class="visual-chart vcenterChart">
                            </div>
                        </div>
                        <div class="fsHostDiv">
                            <h3 ><span><?php echo $LANG['UI_PLATFORM_TENANT_FILE_AGENT']?></span>
                                <img class="slope" src="../img/visualization/slope.png">
                                <img src="../img/visualization/title-line.png">
                            </h3>
                            <div id="fsData" class="data-content">
                            </div>
                            <p id="fs-rote" ></p>
                            <div style="top: 200px;left: 10px;" class="dataDes" id="fsunprotectDes" >
                            </div>
                            <div style="top: 80px;left: 300px;" class="dataDes" id="fsprotectDes" >
                            </div>
                            <div id="fsPieChart" class="visual-chart">
                            </div>
                        </div>
                        <div class="dbHostDiv">
                            <h3 ><span><?php echo $LANG['UI_PLATFORM_TENANT_DATABASE_AGENT']?></span>
                                <img class="slope" src="../img/visualization/slope.png">
                                <img src="../img/visualization/title-line.png">
                            </h3>
                            <div id="dbData" class="data-content">
                            </div>
                            <p id="db-rote" ></p>
                            <div style="top: 200px;left: 10px;" class="dataDes" id="dbunprotectDes" >
                            </div>
                            <div style="top: 80px;left: 300px;" class="dataDes" id="dbprotectDes" >
                            </div>
                            <div id="dbPieChart" class="visual-chart">
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination" style="text-align: right;padding-right: 20px;"></div>
                </div>
            </div>
            <div id="storage_data" class="dataDiv bgline">
                <h3 ><span><?php echo $LANG['UI_DATACENTER_BACKUP_STORAGE']?></span>
                    <img class="slope" src="../img/visualization/slope.png">
                    <img src="../img/visualization/title-line.png">
                </h3>
                <div id="storageData" class="data-content">
                </div>
                <div style="top: 200px;left: 21px;" class="storagedataDes" id="freeDes" >
                </div>
                <div style="top: 80px;left: 290px;" class="storagedataDes" id="usedDes" >
                </div>
                <div  id="storageChart" class="visual-chart">
                </div>
            </div>
        </div>
        <div id="content-center" >
            <div class="platform-data">
                <h3><?php echo $LANG['UI_DATACENTER_TOTAL_DATA_SIZE']?></h3>
                <p id="all_backup_data"></p>
            </div>
            <div class="right" style="display:flex;">
                <div class="platform-data dbProtectDiv" style="flex: 1;min-width:170px;">
                    <h3><?php echo $LANG['UI_VISUAL_PROTECT_DB_HOST']?></h3>
                    <p id="all_protect_db"></p>
                </div>
                <div class="platform-data fsProtectDiv" style="flex: 1;min-width:170px;">
                    <h3><?php echo $LANG['UI_VISUAL_PROTECT_FS_HOST']?></h3>
                    <p id="all_protect_fs"></p>
                </div>
                <div class="platform-data vmProtectDiv" style="flex: 1;min-width:170px;">
                    <h3><?php echo $LANG['UI_VISUAL_PROTECT_VM']?></h3>
                    <p id="all_protect_num"></p>
                </div>
            </div>
            <div id="runvm-circle" style="position: absolute;top: 112px;left:-45px;">
                <img style="width:70px;"  src="../img/visualization/visual-circle.png">
                <svg  style="display:block;" width="525" height="225"><ellipse cx="525" cy="225" rx="262" ry="112" style="fill: none;"/>
                </svg>
            </div>

            <div id="runvm-circle2" style="display:none;position: absolute;top: 112px;left:-45px;">
            </div>

            <div id="backupDiv">

            </div>
            <div id="recoveryDiv">
            </div>
            <div id="ball8-svg">
                <svg style="display:block;"  width="100%" height="100%"  >
                    <path d="M0 0, L-232 -100, L-350 -48, L-541 -140, L-839 -2, L-541 134 "/>
                </svg>
            </div>

        </div>
        <div id="content-right" >
            <div class="bgline" style="padding-bottom:0.625rem;width:100%;height:27%;float:right;font-size:1rem;color:#00d2ff;">
                <h3 ><span><?php echo $LANG['UI_VISUAL_LATEST_FINISH_JOB']?></span>
                    <img class="slope" src="../img/visualization/slope.png">
                    <img src="../img/visualization/title-line.png">
                </h3>
                <div class="latest-title" >
                    <p style="width:10%;"><?php echo $LANG['UI_VISUAL_NUMBER']?></p>
                    <p style="width:38%;"><?php echo $LANG['UI_VISUAL_JOB_NAME']?></p>
                    <p style="width:20%;"><?php echo $LANG['UI_VISUAL_DATA_SIZE']?></p>
                    <p style="width:32%;"><?php echo $LANG['UI_VISUAL_RESULT_FINISH_TIME']?></p>
                </div>
                <div id="latestJobs" style="overflow:hidden;" class="">
                    <ul id="latestjob-list" class="latest-list">
                    </ul>
                </div>
            </div>
            <div class="bgline" style="padding-bottom:0.625rem;width:100%;height:27%; float:right;font-size:1rem;color:#00d2ff;">
                <h3 ><span><?php echo $LANG['UI_VISUAL_LATEST_BACKUP_VM']?></span>
                    <img class="slope" src="../img/visualization/slope.png">
                    <img src="../img/visualization/title-line.png">
                </h3>
                <div class="latest-title" >
                    <p style="width:10%;"><?php echo $LANG['UI_VISUAL_NUMBER']?></p>
                    <p style="width:36%;"><?php echo $LANG['UI_VISUAL_VM_HOST']?></p>
                    <p style="width:24%;"><?php echo $LANG['UI_VISUAL_RESTORE_POINTS']?></p>
                    <p style="width:30%;"><?php echo $LANG['UI_REPORT_FINISH_TIME']?></p>
                </div>
                <div id="latestVms" style="overflow:hidden;" class="">
                    <ul id="latestvm-list" class="latest-list">
                    </ul>
                </div>
            </div>
            <div class="bgline"  style="width:100%;height:46%; float:right;font-size:1rem;color:#fff;">
                <h3 ><span><?php echo $LANG['UI_VISUAL_DAILY_BACKUP_VM']?></span>
                    <img class="slope" src="../img/visualization/slope.png">
                    <img src="../img/visualization/title-line.png">
                </h3>
                <div id="dailyVmDiv" class="display-none">
                    <p style="color:#fff;position:absolute; top: 50px; left: 25px;font-size:14px;"><?php echo $LANG['UI_VISUAL_NUM_TIME']?></p>
                    <div id="dailyVmChart" style="height:240px;">
                    </div>
                </div>
                <div id="no-dailyVm" class="display-none"><?php echo $LANG['UI_VISUAL_NO_BACKUP_VM']?></div>
            </div>
        </div>
    </div>

    <div id="visual-footer" >
        <div class="bgline"  id="nodeDiv" >
            <h3 ><span><?php echo $LANG['UI_JOB_BACKUP_NODE']?></span>
                <img class="slope" src="../img/visualization/slope.png">
                <img class="line-gif" src="../img/visualization/title-line.gif">
            </h3>
            <div class="netDiv" style="padding-right:5px;">
                <div style="width:50%;display:flex;">
                    <div style="width:35%;">
                        <p style="width:5px;height:5px;border-radius: 5px; background: #1eff00;position:absolute;top:15px;left:15px;"></p>
                        <h3 style="color: #1eff00;"><span class="title-circle"></span><?php echo $LANG['UI_VISUAL_NETWORK_INFLOW']?></h3>
                        <p id="inflow"></p>
                    </div>
                    <div style="width:60%;height:60px;" id="netInflowChart"></div>
                </div>
                <div style="width:50%;display:flex;">
                    <div style="width:5%;position:relative;"><p style="width:5px;height:5px;border-radius: 5px; background: #00ffff;position:absolute;top:15px;left:0;"></p></div>
                    <div style="width:35%;">
                        <h3 style="color: #00ffff;"><span class="title-circle"></span><?php echo $LANG['UI_VISUAL_NETWORK_OUTFLOW']?></h3>
                        <p id="outflow"></p>
                    </div>
                    <div style="width:60%;height:60px;" id="netOutflowChart"></div>
                </div>
            </div>
            <div class="netDiv">
                <div style="width:30%;">
                    <p style="width:5px;height:5px;border-radius: 5px; background: #ff9c00;position:absolute;top:15px;left:15px;"></p>
                    <h3 style="color:#ff9c00;"><span class="title-circle"></span><?php echo $LANG['UI_VISUAL_CPU_SIZE_AND_RATE']?></h3>
                    <p id="cpu-data"></p>
                </div>
                <div id="cpuChart" style="width:72%;height:70px;"></div>
            </div>

            <div class="netDiv">
                <div style="width:30%;">
                    <p style="width:5px;height:5px;border-radius: 5px; background: #ffeb3b;position:absolute;top:15px;left:15px;"></p>
                    <h3 style="color: #ffeb3b;"><span class="title-circle"></span><?php echo $LANG['UI_VISUAL_MEMORY_SIZE_AND_RATE']?></h3>
                    <p id="memory-data"></p>
                </div>
                <div style="width:72%;height:70px;" id="memoryChart"></div>
            </div>
        </div>
        <div class="bgline" id="jobDiv">
            <h3 ><span><?php echo $LANG['UI_VISUAL_CURRENT_JOB']?></span>
                <img class="slope" src="../img/visualization/slope.png">
                <img class="line-gif" src="../img/visualization/title-line.gif">
            </h3>
            <div class="display-none"  id="no-job"><?php echo $LANG['UI_VISUAL_NO_JOB']?></div>
            <div class="display-none"  id="job-wait">
                <div class="bgline" style="width: 24%;height:230px;">
                    <div  style="border-radius:10px;position:relative;width: 148px;height: 127px;margin: 15px auto;padding-top:10px;border: 1px solid #275376; ">
                        <div class="image-border image-border1"></div>
                        <div class="image-border image-border2"></div>
                        <div class="image-border image-border3"></div>
                        <div class="image-border image-border4"></div>

                        <div id="jobPieChart" style="width:90px;height: 80px;margin: 0 auto;"></div>
                        <p id="job-rate" style="text-align:center;"></p>
                    </div>
                    <div id="job-num">
                    </div>
                </div>
                <div style="width: 76%;color: #00ffff;padding-left:33px;">
                    <p id="wait-task"></p>
                    <div style="display: flex;">
                        <div style="border-radius:10px;position:relative;width:28%;border: 1px solid #275376;height: 117px;text-align:center;padding-top:16px;">
                            <div class="image-border image-border1"></div>
                            <div class="image-border image-border2"></div>
                            <div class="image-border image-border3"></div>
                            <div class="image-border image-border4"></div>

                            <img src="../img/visualization/vmgroup.png">
                            <p id="wait-vms"></p>
                        </div>
                        <div style="width:15%;text-align:center;padding-top:40px;">
                            <img src="../img/visualization/arrows.gif">
                        </div>

                        <div style="border-radius:10px;position:relative;width:54%;height: 117px;border:1px solid #275376;padding-left:13px;">
                            <div class="image-border image-border1"></div>
                            <div class="image-border image-border2"></div>
                            <div class="image-border image-border3"></div>
                            <div class="image-border image-border4"></div>

                            <p style="color:#fff;margin-top:10px;"><?php echo $LANG['UI_VISUAL_JOB_COUNT_DOWN']?></p>
                            <div class="timecountdown"></div>
                        </div>

                    </div>
                </div>
            </div>
            <div id="job-run" class="" >
                <div id="myCarousel" class="carousel slide" >

                    <div class="carousel-inner">
                    </div>
                    <!-- 轮播（Carousel）导航 -->
                    <a class="left carousel-control bt-prev" href="#myCarousel" role="button" data-slide="prev">
                        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                    </a>
                    <a class="right carousel-control bt-next" href="#myCarousel" role="button" data-slide="next">
                        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                    </a>
                </div>
            </div>

        </div>
        <div class="bgline" id="dailyStorage" >
            <h3 ><span><?php echo $LANG['UI_VISUAL_DAILY_USE_STORAGE']?></span>
                <img class="slope" src="../img/visualization/slope.png">
                <img src="../img/visualization/title-line.png">
            </h3>
            <div class="display-none" id="dailyStorageDiv" style="width: 100%;height: 100%;">
                <p style="position:absolute; top: 60px; left:22px;font-size: 14px;"><?php echo $LANG['UI_VISUAL_MONTH_DAY']?></p>
                <p style="position:absolute; top: 60px; right:55px;font-size: 14px;"><?php echo $LANG['UI_VISUAL_USED_SIZE']?></p>
                <div id="dailyStorageChart" style="height:83%;width: 100%;margin: 0 auto;padding-left:1rem;"></div>
            </div>
            <div id="no-dailyStorage" class="display-none"><?php echo $LANG['UI_VISUAL_NO_STORAGE_SIZE']?></div>
        </div>
    </div>

</div>

<!-- END CONTENT -->


<script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/scripts/conf/config.js" type="text/javascript"></script>
<script src="/lang/<?php
if($_SESSION['language']){
    $userLang = $_SESSION['language'];
}else{
    $userLang = "zh-cn";
}
echo $userLang; ?>.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<!-- IMPORTANT! Load jquery-ui.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>

<!--Required plugins js-->
<script type="text/javascript" src="/assets/global/plugins/anime/anime.min.js" ></script>
<script src="./assets/global/plugins/echarts/echarts.min.js"></script>
<script type="text/javascript" src="/assets/global/plugins/g2/g2.min.js"></script>
<script type="text/javascript" src="/assets/global/plugins/jquery-fontFlex/jQuery.fontFlex.js" ></script>
<script type="text/javascript" src="/assets/global/plugins/time-counter/jquery.timeout.interval.idle.js" ></script>
<script type="text/javascript" src="/assets/global/plugins/time-counter/jquery.countdown.counter.js" ></script>
<script type="text/javascript" src="/assets/global/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"></script>
<script type="text/javascript" src="/assets/global/plugins/bootstrap-modal/js/bootstrap-modal.js"></script>
<script src="/assets/global/plugins/jquery-idle-timeout/jquery.idletimeout.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-idle-timeout/jquery.idletimer.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jcountdown/jquery.jcountdown.min.js"></script>
<script src="./assets/global/plugins/counterup/jquery.waypoints.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>

<script type="text/javascript" src="/scripts/plugins/jquery/jquery.browser.js"></script>
<script type="text/javascript" src="/scripts/visualization/noAuth-timeout.js"></script>
<script type="text/javascript" src="/scripts/visualization/visualization.js"></script>

</body>