<?php
    session_start();
    // include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; 
    $_SESSION['ROOTPATH'] = getcwd() . "/";
    if(empty($_SESSION['permissionVisualScreen'])){
        header('HTTP/1.1 301 Moved Permanently');
        header("Location:/login.php");
        exit;
    }
    
    include_once $_SERVER['DOCUMENT_ROOT'].'/api/load.php';
    $CONF = Xphp::$_config;
    
    
    // $systemHandler = Xphp::instance('SystemHandler');
    // $softwareType = $systemHandler->getSoftwareType();
    
    /**
     * 特殊配置,主要用于第三方OEM版本
     * 所有配置文件均在根目录下special文件夹,包括配置文件,logo,图片等所有信息
     */
    $configFile = $CONF['SPECIAL_DIR'] . $CONF['SPECIAL_CONFIG'];
    if(file_exists($configFile)){
        $content = file_get_contents($configFile);
        $content = json_decode($content, true);
        setSpecialConfigRecursiveVisual($CONF, $content);
    }
    
    function setSpecialConfigRecursiveVisual(&$arrA, $arrB){
        foreach ($arrB as $key => $value){
            if(is_array($value)){
                setSpecialConfigRecursiveVisual($arrA[$key], $value);
            }else{
                $arrA[$key] = $value;
            }
        }
    }
    
    //特殊处理自动登录的情况，适用于文件客户端不用登录可以直接通过特定的链接进入系统首页，跳过WEB系统的登录。
    function clientLogin($agentuuid, $conf){
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
    clientLogin($agentuuid,$CONF['API_MAGIC']);
    session_commit();
    if($_SESSION['language']){
        $userLang = $_SESSION['language'];
    }else{
        $userLang = "zh-cn";
    }
    $LANG = require '../../lang/' . $userLang . '.php';
    
    
    // $settingsHandler = Xphp::instance('SettingsHandler');
    // $visualConf = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['VISUAL']);
    
    // $visualConf = json_decode($visualConf[0]['settings_content'], true);
    // $title = $visualConf['config']['title'];
    
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
<!-- <meta content="width=device-width, initial-scale=1.0" name="viewport"/> -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
<link href="/css/platform/main.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="/assets/global/plugins/swiper/css/swiper.min.css"/>
<link rel="stylesheet" type="text/css" href="/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<?php 
if($_SESSION['language']){
    $userLang = $_SESSION['language'];
}else{
    $userLang = "zh-cn";
}
echo '<link href="/css/platform/lang/' . $userLang . '.css" rel="stylesheet" type="text/css"/>';
?>
<link href="/css/visualscreen/visualscreen.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<!-- 关键权限策略设置 -->
 <meta http-equiv="Permissions-Policy" content="fullscreen=(self https:)">
</head>

<body>
<!-- BEGIN CONTENT -->
<div class="visual" style="background-image: url(../../img/visualscreen/back-pic.png);">
    <!-- 顶部 -->
    <div class="visual-top">
        <div class="time">
            <div class="system-time-box">
                <div class="time-box" >
                    <div style="display: inline-block;width: 3.3rem;" id="hours"></div>
                    <div style="display: inline-block;width: 0.4rem;">:</div>
                    <div style="display: inline-block;width: 3.3rem;" id="minutes"></div>
                    <div style="display: inline-block;width: 0.4rem;">:</div>
                    <div style="display: inline-block;width: 3.3rem;" id="seconds"></div>
                </div>  
                <div class="time-right">
                        <div class="system-time-right">
                            <div class="system-date" id="currentDate"></div>
                        </div>
                        <div class="system-week" id="currentWeek"></div>
                </div>
            </div>
        </div>
        <div class="title-name">
            <img src="../../img/visualscreen/title-name.svg" alt="">
            <p class="system-name"><span id="system-name"></span></p>
        </div>
        <div class="warning">
            <div class="task-warning display-none" id="task-warning">
                <div class="warning-dot display-none"></div>
                <img src="../../img/visualscreen/task-warning.svg" alt="">
                <span class="warning-name"><?php echo $LANG['UI_VISUAL_ALARM_TASK']?></span>
                <span id="taskWarning" class="red-warning warning-number"></span>
            </div>
            <div class="system-warning display-none"  id="system-warning">
                <div class="warning-dot display-none"></div>
                <img src="../../img/visualscreen/system-warning.svg" alt="">
                <span class="warning-name"><?php echo $LANG['UI_VISUAL_ALARM_SYSTEM'];?></span>
                <span id="systemWarning" class="yellow-warning warning-number"></span>
            </div>
            <img id="f11" src="../../img/visualscreen/f11.svg" alt="">
        </div>

    </div>
    <!-- 中部 -->
    <div class="visual-middle">
        <!-- 左边 -->
        <div class="left-box">
            <!-- 当前任务 -->
            <div class="current-task">
                <!-- 标题 -->
                <div class="title-box">
                    <div class="d-flex justify-content-space-between" style="height: 100%;">
                        <div class="node-title-box-left">
                            <img class="title-pic">
                            <span class="zh"><?php echo $LANG['UI_VISUAL_JOB_CURRENT'];?></span>
                            <span class="en screen-notshow_en">CURRENT TASK</span>
                            <img src="../../img/visualscreen/dotdot.svg" style="vertical-align: text-bottom">
                        </div>
                        <div class="node-title-box-right"></div>
                    </div>
                    <div class="title-bottom">
                        <div class="d-flex">
                            <div class="firstline"></div>
                            <div class="lastline"></div>
                        </div>
                        <div class="whiteline screen-taskline_en screen-taskline_cn"></div>
                    </div>
                </div>
                <div style="margin-top:1.875rem;overflow: hidden;position:relative">
                    <!-- 左边顶部+echrts -->
                    <div class="current-task-top-box">
                        <div class="current-task-box">
                            <div style="margin-left:0px" class="current-task-item">
                                <span class="task-title"><?php echo $LANG['UI_VISUAL_NUMBER_CURRENT_TASKS_UNIT'];?></span>
                                <span id="current-num" class="task-title-num"></span>
                            </div>
                        
                            <div class="current-task-item">
                                <span class="task-title"><?php echo $LANG['UI_VISUAL_NUMBER_RUNNING_TASKS_UNIT'];?></span>
                                <span id="running-num" class="task-title-num"></span>
                            </div>
                            <div class="current-task-item">
                                <span class="task-title"><?php echo $LANG['UI_VISUAL_NUMBER_WAITING_TASKS_UNIT'];?></span>
                                <span id="wait-num" class="task-title-num"></span>
                            </div>
                            <div class="current-task-item">
                                <span class="task-title"><?php echo $LANG['UI_VISUAL_NUMBER_STOPPING_TASKS_UNIT'];?></span>
                                <span id="stop-num" class="task-title-num"></span>
                            </div>
                            <div class="current-task-item" style="border-right: none;padding-right:15px">
                                <span class="task-title"><?php echo $LANG['UI_VISUAL_COMPLETE_TASK_TODAY_UNIT'];?></span>
                                <span id="today-complete-num" class="task-title-num"></span>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div style="position:relative;display:flex;width: 11.8rem;height: 11.8rem;margin-right: 2.19rem;">
                                <div id="currentTaskTotalPie" style="width: 11.8rem;height: 11.8rem"></div>
                                <img src="../../img/visualscreen/circle.svg" style="z-index:999;width: 7.75rem;height: 7.75rem;position: absolute;top: 0;left: 0;bottom: 0;right: 0;margin: auto">
                                <div style="z-index:9999;width: 3.75rem;height: 4.75rem;position: absolute;top: 0;left: 0;bottom: 0;right: 0;margin: auto">
                                    <p id="current-most-num" class="current-task-most-num"></p>
                                    <p id="current-most-num-des" class="current-task-most-name"></p>
                                </div>
                            </div>
                            <div class="current-task-middle-box">
                                <!-- 虚拟机 -->
                                <div class="height-task" id="current_vm_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/vm-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #18FEF0 -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #18FEF0"><?php echo $LANG['UI_PLATFORM_VM_VIRTUAL']?></span>
                                            <span id="vm-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- 公有云 -->
                                <div class="height-task" id="current_publicCloud_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/publicCloud-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #2EAD2B -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #2EAD2B"><?php echo $LANG['WEB_PLATFORM_DES_PUBLIC_CLOUD']?></span>
                                            <span id="publicCloud-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- 私有云 -->
                                <div class="height-task" id="current_privateCloud_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/privateCloud-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #18FEF0 -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #18FEF0"><?php echo $LANG['WEB_PLATFORM_DES_PRIVATE_CLOUD']?></span>
                                            <span id="privateCloud-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- 整机 -->
                                <div class="height-task" id="current_completeMachine_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/machine-os-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #FFFFFF -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #FFFFFF"><?php echo $LANG['UI_COMPLETE_MACHINE']?></span>
                                            <span id="completeMachine-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- 卷 -->
                                <div class="height-task" id="current_os_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/os-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #FFFFFF -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #FFFFFF"><?php echo $LANG['WEB_PLATFORM_DES_VOL']?></span>
                                            <span id="os-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- 文件 -->
                                <div class="height-task" id="current_file_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/file-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #06F7A1 -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #06F7A1"><?php echo $LANG['WEB_PLATFORM_DES_FS']?></span>
                                            <span id="file-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- NAS -->
                                <div class="height-task" id="current_nas_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/nas-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #B3F9FE -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #B3F9FE">NAS</span>
                                            <span id="nas-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- OBS -->
                                <div class="height-task" id="current_obs_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/obs-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #FFFFFF -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #FFFFFF"><?php echo $LANG['WEB_PLATFORM_DES_OBS']?></span>
                                            <span id="obs-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- HADOOP -->
                                <div class="height-task" id="current_hadoop_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/hadoop-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #B3F9FE -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #B3F9FE">Hadoop</span>
                                            <span id="hadoop-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- M365 -->
                                <div class="height-task" id="current_office365_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/m365-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #58AAFF -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #58AAFF">M365</span>
                                            <span id="office365-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- K8S -->
                                <div class="height-task" id="current_k8s_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/k8s-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #18FEF0 -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #18FEF0"><?php echo $LANG['WEB_KUBE_BACKUP_KUBE']?></span>
                                            <span id="k8s-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                               
                                <!-- 数据库 -->
                                <div class="height-task" id="current_db_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/db-task.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #4BD7FF -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #4BD7FF"><?php echo $LANG['WEB_PLATFORM_DES_DB']?></span>
                                            <span id="db-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div>
                             
                              
                            
                                <!-- 实时 -->
                                <!-- <div class="height-task" id="current_cdp_tab">
                                    <div class="imgbox">
                                        <div class="back-circle">
                                        </div>
                                        <img src="../../img/visualscreen/cdp.svg">
                                    </div>
                                    <div class="task-gaugename-box" style="background: linear-gradient(270deg, #90E8C5 -50%, rgba(0, 0, 0, 0) 100%);">
                                        <div>
                                            <span class="task-gaugename" style="color: #90E8C5">实时容灾</span>
                                            <span id="cdp-task-num" class="task-gaugename-num">0</span>
                                        </div>
                                    </div>
                                </div> -->
                               
                              

                            </div>
                        </div>
                    </div>
                   
                    <!-- 当前任务表格 -->
                    <div class="current-task-bottom-box">
                        <div class="current-title" >
                            <?php 
                                if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
                                    echo '<p style="width:45%;">'.$LANG['UI_VISUAL_DATA_EXPORT_JOB_NAME'].'</p>
                                    <p style="width:15%;">'.$LANG['UI_VISUAL_TYPE'].'</p>
                                    <p style="width:14%;">'.$LANG['UI_VISUAL_STATUS'].'</p>
                                    <p style="width:15%;text-align:center;">'.$LANG['UI_VISUAL_PROGRESS'].'</p>
                                    <p style="width:11%;">'.$LANG['UI_VISUAL_SPEED'].'</p>';
                                }
                                else{
                                    echo '<p style="width:45%;">'.$LANG['UI_VISUAL_DATA_EXPORT_JOB_NAME'].'</p>
                                    <p style="width:15%;">'.$LANG['UI_VISUAL_STATUS'].'</p>
                                    <p style="width:20%;text-align:center;">'.$LANG['UI_VISUAL_PROGRESS'].'</p>
                                    <p style="width:20%;">'.$LANG['UI_VISUAL_SPEED'].'</p>';
                                }
                            ?>
                        </div>
                        <div id="current-task" style="overflow:hidden;height:19.75rem;">
                            <ul id="current-task-list" class="current-list" style="margin-bottom: 0">
                            </ul>
                        </div>


                    </div>

                </div>

            </div>

        </div>
        <!-- 中间 -->
        <div class="middle-box">
            <div class="module-top-box">
                <div class="imgbox">
                    <i class="viconfont vicon-tingzhi" id="stoptab"></i>
                    <i class="viconfont vicon-qidong display-none" id="starttab"></i>
                </div>
               <!-- 主垂直 Swiper 容器 -->
               <div class="vertical-swiper-container">
                    <!-- 左侧导航控制区域 -->
                    <div class="swiper-controls">
                        <div class="swiper-button-prev-vertical">
                        <svg viewBox="0 0 24 24"><path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/></svg>
                        </div>
                        <div class="swiper-button-next-vertical">
                        <svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                        </div>
                    </div>
                    
                    <!-- 右侧内容区域 -->
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="firsttitle"><?php echo $LANG['UI_PLATFORM_DATA_BACKUP']?></div> 
                            <div class="sub-swiper-container">
                                <div class="swiper sub-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide sub-slide" data-graph="vmgraph" id="swiper_vm"><?php echo $LANG['WEB_PLATFORM_DES_VM']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="privateCloudgraph" id="swiper_privateCloud"><?php echo $LANG['WEB_PLATFORM_DES_PRIVATE_CLOUD']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="publicCloudgraph" id="swiper_publicCloud"><?php echo $LANG['WEB_PLATFORM_DES_PUBLIC_CLOUD']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="machine_os_graph" id="swiper_completeMachine"><?php echo $LANG['UI_COMPLETE_MACHINE']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="osgraph" id="swiper_os"><?php echo $LANG['WEB_PLATFORM_DES_VOL']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="filegraph" id="swiper_file"><?php echo $LANG['WEB_PLATFORM_DES_FS']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="nasgraph" id="swiper_nas">NAS</div> 
                                        <div class="swiper-slide sub-slide" data-graph="obsgraph" id="swiper_obs"><?php echo $LANG['WEB_PLATFORM_DES_OBS']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="hadoopgraph" id="swiper_hadoop">Hadoop</div>
                                        <div class="swiper-slide sub-slide" data-graph="dbgraph" id="swiper_db"><?php echo $LANG['WEB_PLATFORM_DES_DB']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="office365graph" id="swiper_office365">M365</div>
                                        <div class="swiper-slide sub-slide" data-graph="k8sgraph" id="swiper_k8s"><?php echo $LANG['WEB_KUBE_BACKUP_KUBE']?></div>
            
                                    </div>
                                    <!-- 进度条分页器 -->
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="firsttitle"><?php echo $LANG['UI_PLATFORM_CDP_PROTECT']?></div> 
                            <div class="sub-swiper-container">
                                <div class="swiper sub-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide sub-slide" data-graph="cdp_machine_os_graph" id="swiper_cdpCompleteMachine"><?php echo $LANG['UI_COMPLETE_MACHINE']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="cdp_os_graph" id="swiper_cdpOs"><?php echo $LANG['WEB_PLATFORM_DES_VOL']?></div>
                                    </div>
                                    <!-- 进度条分页器 -->
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                        <div class="firsttitle"><?php echo $LANG['UI_PLATFORM_DATA_COPY']?></div> 
                            <div class="sub-swiper-container">
                                <div class="swiper sub-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide sub-slide" data-graph="replicate_machine_os_graph" id="swiper_reCompleteMachine"><?php echo $LANG['UI_COMPLETE_MACHINE']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="replicate_os_graph" id="swiper_reOs"><?php echo $LANG['WEB_PLATFORM_DES_VOL']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="replicate_file_graph" id="swiper_refile"><?php echo $LANG['WEB_PLATFORM_DES_FS']?></div>
                                        <div class="swiper-slide sub-slide" data-graph="replicate_db_graph" id="swiper_redb"><?php echo $LANG['WEB_PLATFORM_DES_DB']?></div>
                                    </div>
                                    <!-- 进度条分页器 -->
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    
                </div>       
            </div>
            <!-- 图形展示区域 -->
            <div class="graph-container">
                <!-- 虚拟化 -->
                <div class="graph-content" id="vmgraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_REPORT_VM_DATA'];?></p>
                            <div><span id="total_vm_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_REPORT_VM_PROTECT'];?></p>
                            <div><span id="protected_vm_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_PLATFORM_VM_DATA'];?></p>
                            <div><span id="vm_backup_data" class="tag-number"></span><span id="vm_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_VM_CUMULATIVE_PROTECT_DATA'];?></p>
                            <div><span id="vm_protect_data" class="tag-number"></span><span id="vm_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_VM_PROTECT_DATA_TODAY'];?></p>
                            <div><span id="vm_protect_data_today" class="tag-number"></span><span id="vm_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper vmdataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/vm-swiper.svg">
                                </div>
                                <div class="moduledes">
                                    <?php echo $LANG['UI_PLATFORM_VM_VIRTUAL'];?>
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <!-- 每个 slide -->
                                <div class="content">
                                    <div class="swiper-slide vm-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="vm_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide vm-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="vm_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide vm-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="vm_copy_task_num">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide vm-archive">
                                        <div>
                                            <?php echo $LANG['UI_ARCHIVE_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="vm_archive_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide vm-archive">
                                        <div>
                                        <?php echo $LANG['UI_VIRTUAL_ARCHIVE_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="vm_archive_point">
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="vmimg">
                        <img src="../../img/visualscreen/vm-circle-inside.svg" style="bottom:8px;" alt="">
                        <img src="../../img/visualscreen/vm-circle-outside.svg" style="width:540px;bottom:-37px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;" alt="">
                        <img src="../../img/visualscreen/vm-big-light.svg" style="bottom:-21px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <img src="../../img/visualscreen/vm-box.svg" style="bottom:0rem;z-index:15;" alt="">
                        <img src="../../img/visualscreen/vm-light.svg" style="bottom:200px;z-index:15;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite;" alt="">
                        <img src="../../img/visualscreen/vm-inverted.svg" style="bottom:1rem;z-index:15" alt="">
                        <div class="vm_item">
                            <div class="vmnodataimg display-none">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/vm.svg" style="bottom:24.4rem;left:20%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/vm.svg" style="bottom:24.4rem;right:20%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/vm.svg" style="bottom: 11.4rem;left:9%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/vm.svg" style="bottom: 11.4rem;right:9%;" alt="">
                            </div>
                            <div class="vmdataimg display-none">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/aio.svg" alt="" id="aio_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/aws.svg" alt="" id="aws_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/citrix.svg" alt="" id="citrix_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/h3c.svg" alt="" id="h3c_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/hbc.svg" alt="" id="hbc_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/huawei.svg" alt="" id="huawei_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/hyperv.svg" alt="" id="hyperv_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/inspur.svg" alt="" id="inspur_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/openstack.svg" alt="" id="openstack_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/oracle.svg" alt="" id="oracle_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/proxmox.svg" alt="" id="proxmox_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/redhat.svg" alt="" id="redhat_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/sangfor.svg" alt="" id="sangfor_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/smartx.svg" alt="" id="smartx_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/vmware.svg" alt="" id="vmware_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/winhong.svg" alt="" id="winhong_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/xcpng.svg" alt="" id="xcpng_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/xhere.svg" alt="" id="xhere_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/xsky.svg" alt="" id="xsky_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/zstack.svg" alt="" id="zstack_item">
                                <img class="vmitem" src="../../img/visualscreen/vm-icon/scp.svg" alt="" id="scp_item">
                            </div>
                            
                        </div>
                    </div>
                </div>
                <!-- 私有云 -->
                <div class="graph-content" id="privateCloudgraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_REPORT_TOTAL_AWS_NUM'];?></p>
                            <div><span id="total_privateCloud_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_PROTECT_AWS'];?></p>
                            <div><span id="protected_privateCloud_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_AWS_BACKUP_DATA'];?></p>
                            <div><span id="privateCloud_backup_data" class="tag-number"></span><span id="privateCloud_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_AWS_PROTECT_DATA_TODAY'];?></p>
                            <div><span id="privateCloud_protect_data_today" class="tag-number"></span><span id="privateCloud_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper privateClouddataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/privateCloud-swiper.svg">
                                </div>
                                <div class="moduledes">
                                    <?php echo $LANG['UI_PLATFORM_PRIVATE_CLOUD'];?>
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <!-- 每个 slide -->
                                <div class="content">
                                    <div class="swiper-slide privateCloud-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="privateCloud_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slideprivateCloud-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="privateCloud_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide privateCloud-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="privateCloud_copy_task_num">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide privateCloud-archive">
                                        <div>
                                            <?php echo $LANG['UI_ARCHIVE_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="privateCloud_archive_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide privateCloud-archive">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_ARCHIVE_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="privateCloud_archive_point">
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vmimg">
                        <img src="../../img/visualscreen/privateCloud-icon/privateCloud-outcircle.svg" style="bottom: -260px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <img src="../../img/visualscreen/privateCloud-icon/privateCloud-circle.svg" style="width: 450.4px;bottom:6px;animation:myfirst 5s infinite;" alt="">
                        <img src="../../img/visualscreen/privateCloud-icon/privateCloud-light.svg" style="bottom:-8px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <img src="../../img/visualscreen/privateCloud-icon/privateCloud-box.svg" style="bottom:73.55px;z-index:15" alt="">
                        <div class="vm_item">
                            <div class="privateCloudnodataimg display-none">
                                <img class="vmitem" src="../../img/visualscreen/privateCloud-icon/openstack.svg" style="bottom: 10.375rem;left:13%" alt="">
                                <img class="vmitem" src="../../img/visualscreen/privateCloud-icon/openstack.svg" style="bottom: 10.375rem;right:13%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/privateCloud-icon/openstack.svg" style="bottom: 25rem;left:calc(50% - 54px)" alt="">
                            </div>
                            <div class="privateClouddataimg display-none">
                                <img class="vmitem" src="../../img/visualscreen/privateCloud-icon/openstack.svg" alt="" id="openstack_item">
                                <img class="vmitem" src="../../img/visualscreen/privateCloud-icon/xsky.svg" alt="" id="xsky_item">
                                <img class="vmitem" src="../../img/visualscreen/privateCloud-icon/zstack.svg" alt="" id="zstack_item">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 公有云 -->
                <div class="graph-content" id="publicCloudgraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_REPORT_TOTAL_AWS_NUM'];?></p>
                            <div><span id="total_publicCloud_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_PROTECT_AWS'];?></p>
                            <div><span id="protected_publicCloud_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_AWS_BACKUP_DATA'];?></p>
                            <div><span id="publicCloud_backup_data" class="tag-number"></span><span id="publicCloud_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_AWS_PROTECT_DATA_TODAY'];?></p>
                            <div><span id="publicCloud_protect_data_today" class="tag-number"></span><span id="publicCloud_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>

                     <!-- 中间区域swiper -->
                     <div class="dataswiper publicClouddataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/publicCloud-swiper.svg">
                                </div>
                                <div class="moduledes">
                                    <?php echo $LANG['UI_PLATFORM_PUBLIC_CLOUD'];?>
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <div class="content">
                                    <!-- 每个 slide -->
                                    <div class="swiper-slide publicCloud-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="publicCloud_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide publicCloud-copy">
                                        <div>
                                        <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="publicCloud_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide publicCloud-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="publicCloud_copy_task_num">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide publicCloud-archive">
                                        <div>
                                            <?php echo $LANG['UI_ARCHIVE_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="publicCloud_archive_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide publicCloud-archive">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_ARCHIVE_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="publicCloud_archive_point">
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="vmimg">
                        <img id="publicCloud" src="../../img/visualscreen/publicCloud-circle.svg" style="width:540px;height:317.6px;bottom:4px;animation:myfirst 5s infinite;" alt="">
                        <img src="../../img/visualscreen/publicCloud-light.svg" style="width:450px;bottom:-25px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <img src="../../img/visualscreen/publicCloud-box.svg" style="width:300px;bottom:3.9rem;z-index:15" alt="">
                        <div class="vm_item">
                            <div class="publicCloudnodataimg display-none">
                                <img class="vmitem" src="../../img/visualscreen/publicCloud-icon/aws.svg" style="bottom:24.4rem;left:20%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/publicCloud-icon/aws.svg" style="bottom:24.4rem;right:20%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/publicCloud-icon/aws.svg" style="bottom: 11.4rem;left:9%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/publicCloud-icon/aws.svg" style="bottom: 11.4rem;right:9%;" alt="">
                            </div>
                            <div class="publicClouddataimg display-none">
                            <img class="vmitem" src="../../img/visualscreen/publicCloud-icon/aws.svg" alt="" id="aws_item">
                            <img class="vmitem" src="../../img/visualscreen/publicCloud-icon/huawei.svg" alt="" id="huawei_item">
                            </div>
                        </div>
                    </div>
                </div>  
                <!-- 整机 -->
                <div class=" graph-content" id="machine_os_graph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_HOSTS_TOTAL_NUMBER'];?></p>
                            <div><span id="total_machine_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HOST'];?></p>
                            <div><span id="protected_machine_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_BACKUP_DATA'] ?></p>
                            <div><span id="machine_os_backup_data" class="tag-number"></span><span id="machine_os_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TOTAL_PROTECT_DATA'] ?></p>
                            <div><span id="machine_os_protect_data" class="tag-number"></span><span id="machine_os_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TODAY_PROTECT_DATA'] ?></p>
                            <div><span id="machine_os_protect_data_today" class="tag-number"></span><span id="machine_os_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper machine-os-dataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/machine-os-swiper.svg">
                                </div>
                                <div class="moduledes">
                                    <?php echo $LANG['UI_COMPLETE_MACHINE'] ?>
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <!-- 每个 slide -->
                               <div class="content">
                                    <div class="swiper-slide machine-os-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="machine_os_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide machine-os-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="machine_os_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide machine-os-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="machine_os_copy_task_num">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide machine-os-archive">
                                        <div>
                                            <?php echo $LANG['UI_ARCHIVE_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="machine_os_archive_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide machine-os-archive">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_ARCHIVE_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="machine_os_archive_point">
                                        
                                        </div>
                                    </div>
                               </div>
                            </div>
                        </div>

                    </div>
                    <div class="vmimg">
                    <img src="../../img/visualscreen/machine-os-icon/back.svg">
                       <img src="../../img/visualscreen/machine-os-icon/box.svg" style="bottom: 76px;" > 
                       <img src="../../img/visualscreen/machine-os-icon/light.svg" style="bottom: 50px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;" > 
                       <img src="../../img/visualscreen/machine-os-icon/circle.svg" style="width:409.6px;bottom: 46px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;" > 
                       <img src="../../img/visualscreen/machine-os-icon/vol-box.svg" style="left:6.5%;bottom: 140px;z-index: 12;" alt=""> 
                       <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="left:6.5%;bottom: 131px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt=""> 
                       <img src="../../img/visualscreen/machine-os-icon/vol-box.svg" style="right:6.5%;bottom: 140px;z-index: 12;" alt=""> 
                       <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="right:6.5%;bottom: 131px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt=""> 

                    </div>


                </div>
                <!-- 卷 -->
                <div class=" graph-content" id="osgraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_HOSTS_TOTAL_NUMBER'];?></p>
                            <div><span id="total_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HOST'];?></p>
                            <div><span id="protected_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_BACKUP_DATA'];?></p>
                            <div><span id="os_backup_data" class="tag-number"></span><span id="os_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TOTAL_PROTECT_DATA'];?></p>
                            <div><span id="os_protect_data" class="tag-number"></span><span id="os_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TODAY_PROTECT_DATA'];?></p>
                            <div><span id="os_protect_data_today" class="tag-number"></span><span id="os_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper os-dataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/os-swiper.svg">
                                </div>
                                <div class="moduledes">
                                    <?php echo $LANG['WEB_PLATFORM_DES_VOL'];?>
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <!-- 每个 slide -->
                               <div class="content">
                                    <div class="swiper-slide os-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="os_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide os-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="os_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide os-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="os_copy_task_num">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide os-archive">
                                        <div>
                                            <?php echo $LANG['UI_ARCHIVE_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="os_archive_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide mos-archive">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_ARCHIVE_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="os_archive_point">
                                        
                                        </div>
                                    </div>
                               </div>
                            </div>
                        </div>
                    </div>
                    <div class="vmimg">
                    <img src="../../img/visualscreen/machine-os-icon/back.svg">
                       <img src="../../img/visualscreen/machine-os-icon/box.svg" style="bottom: 76px;" > 
                       <img src="../../img/visualscreen/machine-os-icon/light.svg" style="bottom: 50px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;" > 
                       <img src="../../img/visualscreen/machine-os-icon/circle.svg" style="width:409.6px;bottom: 46px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;" > 
                       <img src="../../img/visualscreen/os-icon/vol-box.svg" style="left:6.5%;bottom: 140px;z-index: 12;" alt=""> 
                       <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="left:6.5%;bottom: 131px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt=""> 
                       <img src="../../img/visualscreen/os-icon/vol-box.svg" style="right:6.5%;bottom: 140px;z-index: 12;" alt=""> 
                       <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="right:6.5%;bottom: 131px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt=""> 
                    </div>

                </div>
                <!-- 文件 -->
                <div class="graph-content" id="filegraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_HOSTS_TOTAL_NUMBER'];?></p>
                            <div><span id="total_file_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HOST'];?></p>
                            <div><span id="protected_file_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_PLATFORM_FILE_DATA'];?></p>
                            <div><span id="file_backup_data" class="tag-number"></span><span id="file_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_FILE_ACCUMULATE_PROTECT_DATA'];?></p>
                            <div><span id="file_protect_data" class="tag-number"></span><span id="file_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_FILE_PROTECT_DATA_TODAY'];?></p>
                            <div><span id="file_protect_data_today" class="tag-number"></span><span id="file_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper filedataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/file-swiper.svg">
                                </div>
                                <div class="moduledes">
                                        <?php echo $LANG['WEB_PLATFORM_DES_FS'];?>
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <div class="content">
                                    <!-- 每个 slide -->
                                    <div class="swiper-slide file-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="file_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide file-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="file_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide file-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="file_copy_task_num">
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>

                    </div>

                    <div class="vmimg">
                        <img src="../../img/visualscreen/file/file-inside.svg" style="width:445.5px;bottom:5rem;" alt="">
                        <img src="../../img/visualscreen/file/file-light-b.svg" style="width:445.5px;bottom:4.3rem;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                        <img src="../../img/visualscreen/file/file-big.svg" style="width:275.4px;bottom: 6.5rem;z-index: 12;" alt=""> 
                        <img id="file-outside" src="../../img/visualscreen/file/file-outside.svg" style="width:785.7px;bottom:-1.7rem;" alt="">
                        <img src="../../img/visualscreen/file/file-s.svg" style="width: 166.5px;bottom: -3rem;left: 49.4rem;z-index: 14;" alt=""> 
                        <img src="../../img/visualscreen/file/file-light-s.svg" style="animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite;width: 166.5px;bottom: -3.2rem;left: 49.4rem;z-index: 14;" alt=""> 
                        <img class="fileitem" src="../../img/visualscreen/file/file-zip.svg" style="width:98.1px;bottom: 0rem;left: 51.8rem;z-index: 18;" alt=""> 
                        <img src="../../img/visualscreen/file/file-s.svg" style="width: 166.5px;bottom: 16rem;left: 59.4rem;z-index: 12" alt=""> 
                        <img src="../../img/visualscreen/file/file-light-s.svg" style="animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite;width: 166.5px;bottom: 15.8rem;left: 59.4rem;z-index: 12" alt=""> 
                        <img class="fileitem" src="../../img/visualscreen/file/file-p.svg" style="width: 54.9px;bottom: 20rem;left: 63rem;z-index: 14" alt=""> 
                        <img class="fileitem" src="../../img/visualscreen/file/file-w.svg" style="width: 54.9px;bottom: 19rem;left: 61.4rem;z-index: 19" alt=""> 
                        <img class="fileitem" src="../../img/visualscreen/file/file-e.svg" style="width: 54.9px;bottom: 21rem;left: 64.4rem;z-index: 12" alt=""> 
                        <img src="../../img/visualscreen/file/file-s.svg" style="width:166.5px;bottom:8rem;left:3.4rem;z-index: 12" alt=""> 
                        <img src="../../img/visualscreen/file/file-light-s.svg" style="animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite;width:166.5px;bottom:7.8rem;left:3.4rem;z-index: 12" alt=""> 
                        <img class="fileitem" src="../../img/visualscreen/file/file-small.svg" style="width:93.6;bottom: 11rem;left: 5.4rem;z-index: 14;" alt="">
                    </div>

                </div>
                <!-- NAS -->
                <div class="graph-content" id="nasgraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_NAS_TOTAL_DEVICES'];?></p>
                            <div><span id="total_nas_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_NAS_PROTECT_DEVICES'];?></p>
                            <div><span id="protected_nas_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_NAS_BAK_DATA'];?></p>
                            <div><span id="nas_backup_data" class="tag-number"></span><span id="nas_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_NAS_CUMULATIVE_PROTECT_DATA'];?></p>
                            <div><span id="nas_protect_data" class="tag-number"></span><span id="nas_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_NAS_PROTECT_DATA_TODAY'];?></p>
                            <div><span id="nas_protect_data_today" class="tag-number"></span><span id="nas_protect_data_today" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper nasdataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/nas-swiper.svg">
                                </div>
                                <div class="moduledes">
                                    NAS
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <div class="content">
                                    <!-- 每个 slide -->
                                    <div class="swiper-slide nas-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="nas_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide nas-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="nas_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide nas-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="nas_copy_task_num">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="vmimg">
                       <img src="../../img/visualscreen/nas/nas-inside.svg" style="width:27.91rem;bottom:0rem;" alt="">
                       <img src="../../img/visualscreen/nas/nas-light-b.svg" style="width:27.91rem;bottom:-0.6rem;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-light.svg" style="width:26.12rem;height:33.38rem;bottom:-5.2rem;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-b.svg" style="bottom: 2.6rem;z-index: 12;" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-invert.svg" style="bottom: -1rem;z-index: 12;" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-outside.svg" style="width:47.54rem;bottom:-5.7rem;" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-s-light.svg" style="bottom:14.3rem;left:8.4rem;z-index:15" alt="">
                       <img src="../../img/visualscreen/nas/nas-light-s.svg" style="bottom:15.3rem;left:9.6rem;z-index:15;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-s.svg" style="bottom:15.3rem;left:9.6rem;z-index:15" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-s-light.svg" style="bottom:2.3rem;left:3.5%;z-index:15" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-light-s.svg" style="bottom:3.3rem;left:5.5%;z-index:15;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-s.svg" style="bottom:3.3rem;left:5.5%;z-index:15" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-s-light.svg" style="bottom:14.3rem;right:11.5%;z-index:15" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-light-s.svg" style="bottom:15.3rem;right:9.6rem;z-index:15;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-s.svg" style="bottom:15.3rem;right:9.6rem;z-index:15" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-s-light.svg" style="bottom:2.3rem;right:3.7%;z-index:15" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-light-s.svg" style="bottom:4.3rem;right:5.5%;z-index:15;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt=""> 
                       <img src="../../img/visualscreen/nas/nas-s.svg" style="bottom:3.3rem;right:5.5%;z-index:15" alt="">
                    </div>
                </div>
                <!-- 对象存储 -->
                <div class="graph-content" id="obsgraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_OBS_HOSTS_TOTAL_NUMBER'];?></p>
                            <div><span id="total_obs_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_OBS_HOST_PROTECT'];?></p>
                            <div><span id="protected_obs_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_OBS_BACKUP_DATA'];?></p>
                            <div><span id="obs_backup_data" class="tag-number"></span><span id="obs_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_OBS_TOTAL_DATA_SIZE'];?></p>
                            <div><span id="obs_protect_data" class="tag-number"></span><span id="obs_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_OBS_TOTAL_DATA_SIZE_TODAY'];?></p>
                            <div><span id="obs_protect_data_today" class="tag-number"></span><span id="obs_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                        
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper obsdataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/icon/999-blue.svg">
                                </div>
                                <div class="moduledes">
                                    <?php echo $LANG['WEB_PLATFORM_DES_OBS'];?>
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <div class="content">
                                    <!-- 每个 slide -->
                                    <div class="swiper-slide obs-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="obs_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide obs-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="obs_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide obs-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="obs_copy_task_num">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="vmimg">
                        <img src="../../img/visualscreen/publicCloud-circle.svg" style="width:540px;height:317.6px;bottom:4px;animation:myfirst 5s infinite;" alt="">
                        <img src="../../img/visualscreen/publicCloud-light.svg" style="width: 450px;bottom:-25px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <img src="../../img/visualscreen/obs-box.svg" style="width:300px;bottom:3.9rem;z-index:15" alt="">
                        <img src="../../img/visualscreen/hadoop-icon/hadoop-light.svg" style="width:26.12rem;height:33.38rem;bottom:-5.5rem;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <div class="vm_item">
                            <div class="obsdataimg display-none">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/aws.svg" alt="" id="aws_item">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/oss.svg" alt="" id="oss_item">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/oss_en.svg" alt="" id="oss_en_item">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/cos.svg" alt="" id="cos_item">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/cos_en.svg" alt="" id="cos_en_item">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/obs.svg" alt="" id="obs_item">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/ceph.svg" alt="" id="ceph_item">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/wasabi.svg" alt="" id="wasabi_item">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/minio.svg" alt="" id="minio_item">
                                <img class="vmitem" src="../../img/visualscreen/obs-icon/other.svg" alt="" id="other_item">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- hadoop -->
                <div class="graph-content" id="hadoopgraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HADOOP_TOTAL_NUM_CLUSTERS'];?></p>
                            <div><span id="total_hadoop_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_KUBER_CLUSTER_PROTECTED'];?></p>
                            <div><span id="protected_hadoop_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HADOOP_BACKUP_DATA'];?></p>
                            <div><span id="hadoop_backup_data" class="tag-number"></span><span id="hadoop_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_HADOOP_CUMULATIVE_PROTECT_DATA'];?></p>
                            <div><span id="hadoop_protect_data" class="tag-number"></span><span id="hadoop_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HADOOP_PROTECTED_DATA_TODAY'];?></p>
                            <div><span id="hadoop_protect_data_today" class="tag-number"></span><span id="hadoop_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper hadoopdataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/hadoop-swiper.svg">
                                </div>
                                <div class="moduledes">
                                    Hadoop
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <div class="content">
                                    <!-- 每个 slide -->
                                    <div class="swiper-slide hadoop-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="hadoop_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide hadoop-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="hadoop_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide hadoop-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="hadoop_copy_task_num">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vmimg">
                        <img src="../../img/visualscreen/office365/office365-circle.svg" style="width:495.2px;height:278.4px;bottom:-2.2rem;animation:myfirst 5s infinite;" alt="">
                        <img src="../../img/visualscreen/office365/office365-light.svg" style="width:24rem;height:29.25rem;bottom:-2rem;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <img src="../../img/visualscreen/hadoop-box.svg" style="width:20.81rem;bottom:1rem;z-index:15" alt="">
                        <img src="../../img/visualscreen/hadoop-icon/hadoop-light.svg" style="width:26.12rem;height:33.38rem;bottom:-7rem;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <div class="vm_item">
                            <img class="vmitem" src="../../img/visualscreen/hadoop-icon/hadoop.svg" style="bottom:24.4rem;left:20%;" alt="">
                            <img class="vmitem" src="../../img/visualscreen/hadoop-icon/hadoop.svg" style="bottom:24.4rem;right:20%;" alt="">
                            <img class="vmitem" src="../../img/visualscreen/hadoop-icon/hadoop.svg" style="bottom: 11.4rem;left:9%;" alt="">
                            <img class="vmitem" src="../../img/visualscreen/hadoop-icon/hadoop.svg" style="bottom: 11.4rem;right:9%;" alt="">
                     
                        </div>
                    </div>
                </div>
                <!-- M365 -->
                <div class="graph-content" id="office365graph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_PROTECTED_USER'];?></p>
                            <div><span id="protected_user_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_MICROSOFT365_BACKUP_DATA'];?></p>
                            <div><span id="office365_backup_host_data" class="tag-number"></span><span id="office365_backup_host_data_unit" class="tag-unit"></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_MICROSOFT365_TOTAL_DATA_SIZE'];?></p>
                            <div><span id="office365_protect_data" class="tag-number"></span><span id="office365_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_MICROSOFT365_PROTECT_DATA_TODAY'];?></p>
                            <div><span id="office365_protect_data_today" class="tag-number"></span><span id="office365_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper m365dataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/m365-swiper.svg">
                                </div>
                                <div class="moduledes">
                                    Microsoft365
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <div class="content">
                                    <!-- 每个 slide -->
                                    <div class="swiper-slide m365-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="office365_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide m365-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="office365_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide m365-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="office365_copy_task_num">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vmimg">

                        <img id="office365" src="../../img/visualscreen/office365/office365-circle.svg" style="width:496px;height:279.2px;bottom:-2.2rem;animation:myfirst 5s infinite;" alt="">
                        <img src="../../img/visualscreen/office365/office365-light.svg" style="width:24rem;height:29.25rem;bottom:-2rem;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <img src="../../img/visualscreen/office365/office365.svg" style="width:20.81rem;bottom:1rem;z-index:15" alt="">
                        <img src="../../img/visualscreen/office365/office365-shadow.svg" style="width:19.5rem;bottom:-9.4rem;z-index:15" alt="">
                        <div class="vm_item">
                            <img class="vmitem" src="../../img/visualscreen/office365-icon/exchange.svg" style="bottom:24.4rem;left:20%;" alt="">
                            <img class="vmitem" src="../../img/visualscreen/office365-icon/exchange.svg" style="bottom:24.4rem;right:20%;" alt="">
                            <img class="vmitem" src="../../img/visualscreen/office365-icon/exchange.svg" style="bottom: 11.4rem;left:9%;" alt="">
                            <img class="vmitem" src="../../img/visualscreen/office365-icon/exchange.svg" style="bottom: 11.4rem;right:9%;" alt="">
                           
                        </div>
                    </div>
                </div>
                <!-- 数据库 -->
                <div class="graph-content" id="dbgraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_HOSTS_TOTAL_NUMBER'];?></p>
                            <div><span id="total_database_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HOST'];?></p>
                            <div><span id="protected_database_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_PLATFORM_DB_DATA'];?></p>
                            <div><span id="database_backup_data" class="tag-number"></span><span id="database_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_DB_CUMULATIVE_PROTECT_DATA'];?></p>
                            <div><span id="database_protect_data" class="tag-number"></span><span id="database_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_DB_PROTECT_DATA_TODAY'];?></p>
                            <div><span id="database_protect_data_today" class="tag-number"></span><span id="database_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper dbdataswiper display-none">
                        <div class="swiper-container">
                             <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/db-swiper.svg">
                                </div>
                                <div class="moduledes">
                                        <?php echo $LANG['WEB_PLATFORM_DES_DB'];?>
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <div class="content">
                                    <!-- 每个 slide -->
                                    <div class="swiper-slide db-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="database_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide db-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="database_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide db-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="database_copy_task_num">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vmimg">

                        <img src="../../img/visualscreen/database-in-light-b.svg" style="width:23.27rem;bottom:1.6rem;" alt=""> 
                        <img id="database-b" src="../../img/visualscreen/database-out-light-b.svg" style="width:501.6px;height:292px;bottom:-1.7rem;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;" alt=""> 
                        <img src="../../img/visualscreen/database-light-circle.svg" style="width:26.12rem;height:33.38rem;bottom:-1.4rem;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt=""> 
                        <img src="../../img/visualscreen/database-b.svg" style="width:15.77rem;bottom:5rem;z-index:15" alt=""> 
                        <img src="../../img/visualscreen/database-light-b.svg" style="width:8.73rem;bottom:18rem;z-index:15;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt=""> 
                        <img src="../../img/visualscreen/database-invert-b.svg" style="width:15.77rem;bottom:0rem;z-index:15" alt=""> 
                       
                       
                       
                        <img src="../../img/visualscreen/database-in-light-s.svg" style="width:14.99rem;bottom:15.6rem;left:3.6rem;" alt=""> 
                        <img id="database-s1" src="../../img/visualscreen/database-out-light-s.svg" style="width:309.6px;height:528px;bottom:2.9rem;left:1.4rem;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;" alt=""> 
                        <img src="../../img/visualscreen/database-s.svg" style="width:10.16rem;bottom:18rem;left:5.9rem;z-index:15" alt=""> 
                        <img src="../../img/visualscreen/database-light-s.svg" style="width:5.4rem;bottom:26.4rem;left:8.3rem;z-index:15;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <img src="../../img/visualscreen/database-invert-s.svg" style="width:10.16rem;bottom:15.8rem;left:5.9rem;z-index:15" alt=""> 
                        
                        <img src="../../img/visualscreen/database-in-light-s.svg" style="width:14.99rem;bottom:15.6rem;right:3.6rem;" alt=""> 
                        <img id="database-s2" src="../../img/visualscreen/database-out-light-s.svg" style="width:309.6px;height:528px;bottom:2.9rem;right:1.4rem;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;" alt=""> 
                        <img src="../../img/visualscreen/database-s.svg" style="width:10.16rem;bottom:18rem;right:5.9rem;z-index:15;" alt=""> 
                        <img src="../../img/visualscreen/database-light-s.svg" style="width:5.4rem;bottom:26.4rem;right:8.3rem;z-index:15;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt=""> 
                        <img src="../../img/visualscreen/database-invert-s.svg" style="width:10.16rem;bottom:15.8rem;right:5.9rem;z-index:15" alt="">

                        <div class="vm_item">

                            <div class="dbnodataimg display-none">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/database.svg" style="bottom:23.4rem;left:26%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/database.svg" style="bottom:23.4rem;right:26%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/database.svg" style="bottom: 5.4rem;left:10%;" alt="">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/database.svg" style="bottom: 5.4rem;right:10%;" alt="">
                            </div>
                            <div class="dbdataimg display-none">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/DM.svg" alt="" id="DM_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/HIGHGO.svg" alt="" id="HIGHGO_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/KINGBASE.svg" alt="" id="KINGBASE_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/MARIA.svg" alt="" id="MARIA_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/MYSQL.svg" alt="" id="MYSQL_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/ORACLE.svg" alt="" id="ORACLE_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/POSTGRE.svg" alt="" id="POSTGRE_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/SQLSERVER.svg" alt="" id="SQLSERVER_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/UXDB.svg" alt="" id="UXDB_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/OPENGAUSS.svg" alt="" id="OPENGAUSS_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/VASTBASE.svg" alt="" id="VASTBASE_item">
                                <img class="vmitem" src="../../img/visualscreen/database-icon/ANTDB.svg" alt="" id="ANTDB_item">
                            </div>
                            
                        </div>
                    </div>
                </div>
                <!-- k8s -->
                <div class="graph-content" id="k8sgraph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HADOOP_TOTAL_NUM_CLUSTERS'];?></p>
                            <div><span id="total_k8s_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_KUBER_CLUSTER_PROTECTED'];?></p>
                            <div><span id="protected_k8s_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_CLUSTER_BACKUP_DATA'];?></p>
                            <div><span id="k8s_backup_data" class="tag-number"></span><span id="k8s_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_CLUSTER_TOTAL_PROTECT_DATA'];?></p>
                            <div><span id="k8s_protect_data" class="tag-number"></span><span id="k8s_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_CLUSTER_TODAY_PROTECT_DATA'];?></p>
                            <div><span id="k8s_protect_data_today" class="tag-number"></span><span id="k8s_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <!-- 中间区域swiper -->
                    <div class="dataswiper k8sdataswiper display-none">
                        <div class="swiper-container">
                            <div class="swiper-slide">
                                <div>
                                    <img src="../../img/visualscreen/k8s-swiper.svg">
                                </div>
                                <div class="moduledes">
                                    <?php echo $LANG['WEB_KUBE_BACKUP_KUBE'];?>
                                </div>
                            </div>
                            <div class="swiper-wrapper">
                                <div class="content">
                                    <!-- 每个 slide -->
                                    <div class="swiper-slide k8s-copy">
                                        <div>
                                            <?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'];?>
                                        </div>
                                        <div class="swiper-num" id="k8s_copy_data">
                                            
                                        </div>
                                    </div>
                                    <div class="swiper-slide k8s-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_POINT_NUMBER'];?>
                                        </div>
                                        <div class="swiper-num" id="k8s_copy_point">
                                    
                                        </div>
                                    </div>
                                    <div class="swiper-slide k8s-copy">
                                        <div>
                                            <?php echo $LANG['UI_VIRTUAL_COPY_TASK_NUM'];?>
                                        </div>
                                        <div class="swiper-num" id="k8s_copy_task_num">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vmimg">
                        <img src="../../img/visualscreen/k8s-icon/k8s-back.svg" style="bottom: -189px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;z-index:12" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-box.svg" style="bottom: 6.6rem;" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-circle.svg" style="width:580.8px;bottom: 2.7rem;animation:myfirst 5s infinite;" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-light.svg" style="bottom: 50px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-small-box.svg" style="bottom: 54px;left:7.9%" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-small-circle.svg" style="bottom: -1.7px;left: 1%;" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-small-light.svg" style="bottom: 1.3px;left:3.5%;" alt="">

                        <img src="../../img/visualscreen/k8s-icon/k8s-small-box.svg" style="bottom: 54px;right:7.9%" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-small-circle.svg" style="bottom: -1.7px;right: 1%;" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-small-light.svg" style="bottom: 1.3px;right:3.5%;" alt="">

                        <img src="../../img/visualscreen/k8s-icon/k8s-small-box.svg" style="bottom: 377px;left:18.4%" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-small-circle.svg" style="bottom: 319px;left: 11.5%;" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-small-light.svg" style="bottom: 321px;left:14%;" alt="">

                        <img src="../../img/visualscreen/k8s-icon/k8s-small-box.svg" style="bottom: 377px;right:18.4%" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-small-circle.svg" style="bottom: 319px;right: 11.5%;" alt="">
                        <img src="../../img/visualscreen/k8s-icon/k8s-small-light.svg" style="bottom: 321px;right:14%;" alt="">
                    </div>
                </div>
                <!-- 整机连续数据保护  -->
                <div class="graph-content" id="cdp_machine_os_graph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_HOSTS_TOTAL_NUMBER'];?></p>
                            <div><span id="total_cdp_machine_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HOST'];?></p>
                            <div><span id="protected_cdp_machine_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_BACKUP_DATA'];?></p>
                            <div><span id="cdp_machine_os_backup_data" class="tag-number"></span><span id="cdp_machine_os_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TOTAL_PROTECT_DATA'];?></p>
                            <div><span id="cdp_machine_os_protect_data" class="tag-number"></span><span id="cdp_machine_os_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TODAY_PROTECT_DATA'];?></p>
                            <div><span id="cdp_machine_os_protect_data_today" class="tag-number"></span><span id="cdp_machine_os_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <div class="vmimg">
                        <img src="../../img/visualscreen/cdp_machine_os-icon/cdp-machine-os-back.svg" style="animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite"> 
                        <img src="../../img/visualscreen/machine-os-icon/circle.svg" style="width:409.6px;bottom: 46px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;">
                        <img src="../../img/visualscreen/cdp/cdp-m.svg" style="width:197px;bottom:4.75rem;" alt=""> 
                        <img src="../../img/visualscreen/machine-os-icon/light.svg" style="bottom: 50px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;">

                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="width:235px;bottom: 138px;left: 241px;height:40px" alt="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="168" height="1.5" style="position: absolute;bottom: 156px;left: 285px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 10 4;
                                    }
                                </style>
                            </defs>
                            <path id="cdp-machine-os-dot1-line" d="M0,0.75 L168,0.75" class="a"></path>
                        </svg>
                        <img id="cdp-machine-os-dot1" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 143px; left: 275px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%;">
                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="width:235px;bottom: 138px;right: 241px;height:40px" alt="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="168" height="1.5" style="position: absolute;bottom: 156px;right: 285px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 10 4;
                                    }
                                </style>
                            </defs>
                            <path id="cdp-machine-os-dot2-line" d="M0,0.75 L168,0.75" class="a"></path>
                        </svg>
                        <img id="cdp-machine-os-dot2" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 129px; right: 520px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">

                        <img src="../../img/visualscreen/machine-os-icon/box.svg" style="width:134px;bottom: 156px;left:0px">
                        <img src="../../img/visualscreen/machine-os-icon/vol-box.svg" style="width:216px;left:7.5%;bottom: 93px;z-index: 12;" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="width:216px;left:7.5%;bottom: 85px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/box.svg" style="width:134px;bottom: 156px;right:0px">
                        <img src="../../img/visualscreen/machine-os-icon/vol-box.svg" style="width:216px;right:7.5%;bottom: 93px;z-index: 12;" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="width:216px;right:7.5%;bottom: 85px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                    </div>
                </div>
                <!-- 卷连续数据保护 -->
                <div class="graph-content" id="cdp_os_graph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VIRTUAL_HOSTS_TOTAL_NUMBER'];?></p>
                            <div><span id="total_cdp_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HOST'];?></p>
                            <div><span id="protected_cdp_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_BACKUP_DATA'];?></p>
                            <div><span id="cdp_os_backup_data" class="tag-number"></span><span id="cdp_os_backup_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TOTAL_PROTECT_DATA'];?></p>
                            <div><span id="cdp_os_protect_data" class="tag-number"></span><span id="cdp_os_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TODAY_PROTECT_DATA'];?></p>
                            <div><span id="cdp_os_protect_data_today" class="tag-number"></span><span id="cdp_os_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <div class="vmimg">
                        <img src="../../img/visualscreen/cdp_machine_os-icon/cdp-machine-os-back.svg" style="animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite"> 
                        <img src="../../img/visualscreen/machine-os-icon/circle.svg" style="width:409.6px;bottom: 46px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;">
                        <img src="../../img/visualscreen/cdp/cdp-m.svg" style="width:197px;bottom:4.75rem;" alt=""> 
                        <img src="../../img/visualscreen/machine-os-icon/light.svg" style="bottom: 50px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;">

                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="width:235px;bottom: 138px;left: 241px;height:40px" alt="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="168" height="1.5" style="position: absolute;bottom: 156px;left: 285px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 10 4;
                                    }
                                </style>
                            </defs>
                            <path id="cdp-os-dot1-line" d="M0,0.75 L168,0.75" class="a"></path>
                        </svg>
                        <img id="cdp-os-dot1" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 129px; left: 175px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">
                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="bottom: 138px;right: 241px;height:40px" alt="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="168" height="1.5" style="position: absolute;bottom: 156px;right: 285px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 10 4;
                                    }
                                </style>
                            </defs>
                            <path id="cdp-os-dot2-line" d="M0,0.75 L168,0.75" class="a"></path>
                        </svg>
                        <img id="cdp-os-dot2" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 129px; right: 520px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">

                        <img src="../../img/visualscreen/machine-os-icon/box.svg" style="width:134px;bottom: 156px;left:0px">
                        <img src="../../img/visualscreen/os-icon/vol-box.svg" style="width:216px;left:7.5%;bottom: 93px;z-index: 12;" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="width:216px;left:7.5%;bottom: 85px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/box.svg" style="width:134px;bottom: 156px;right:0px">
                        <img src="../../img/visualscreen/os-icon/vol-box.svg" style="width:216px;right:7.5%;bottom: 93px;z-index: 12;" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="width:216px;right:7.5%;bottom: 85px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                    </div>
                </div>
                <!-- 整机复制 -->
                <div class="graph-content" id="replicate_machine_os_graph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HOST_NUM'];?></p>
                            <div><span id="total_replicate_machine_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_COPY_TASK_NUM'];?></p>
                            <div><span id="replicate_machine_os_task_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TOTAL_COPY_DATA'];?></p>
                            <div><span id="replicate_machine_os_protect_data" class="tag-number"></span><span id="replicate_machine_os_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TODAY_COPY_DATA'];?></p>
                            <div><span id="replicate_machine_os_protect_data_today" class="tag-number"></span><span id="replicate_machine_os_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <div class="vmimg">
                        <img src="../../img/visualscreen/cdp_machine_os-icon/cdp-machine-os-back.svg" style="animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite"> 
                        <img src="../../img/visualscreen/machine-os-icon/circle.svg" style="width:409.6px;bottom: 46px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;">
                        <img src="../../img/visualscreen/cdp/cdp-m.svg" style="width:197px;bottom:4.75rem;" alt=""> 
                        <img src="../../img/visualscreen/machine-os-icon/light.svg" style="bottom: 50px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;">
            
                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="width:235px;bottom:138px;left: 241px;height:40px" alt="">
                        <img src="../../img/visualscreen/cdp_machine_os-icon/middle-line.svg" style="bottom: 245px;" alt="">

                        <svg xmlns="http://www.w3.org/2000/svg" width="168" height="1.5" style="position: absolute;bottom: 156px;left: 285px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 10 4;
                                    }
                                </style>
                            </defs>
                            <path id="replicate-machine-os-dot1-line" d="M0,0.75 L168,0.75" class="a"></path>
                        </svg>
                        <img id="replicate-machine-os-dot1" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 129px; left: 185px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">
                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="width:235px;bottom: 138px;right: 241px;height:40px" alt="">

                        <svg xmlns="http://www.w3.org/2000/svg" width="168" height="1.5" style="position: absolute;bottom: 156px;right: 284px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 10 4;
                                    }
                                </style>
                            </defs>
                            <path id="replicate-machine-os-dot2-line" d="M0,0.75 L168,0.75" class="a"></path>
                        </svg>
                        <img id="replicate-machine-os-dot2" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 143px; right: 440px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%;">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:868px;height:312px;bottom:220px">
                            <path id="replicate-machine-os-dot3-line" class="a" d="M45,312 Q 434 -190 823 312"></path>
                        </svg>
                        <img id="replicate-machine-os-dot3" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 503px;left:133px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">
                        <img src="../../img/visualscreen/machine-os-icon/box.svg" style="width:134px;bottom: 156px;left:0px">
                        <img src="../../img/visualscreen/machine-os-icon/vol-box.svg" style="width:216px;left:7.5%;bottom: 93px;z-index: 12;" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="width:216px;left:7.5%;bottom: 85px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/box.svg" style="width:134px;bottom: 156px;right:0px">
                        <img src="../../img/visualscreen/machine-os-icon/vol-box.svg" style="width:216px;right:7.5%;bottom: 93px;z-index: 12;" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="width:216px;right:7.5%;bottom: 85px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                    </div>
                </div>
                <!-- 卷复制 -->
                <div class="graph-content" id="replicate_os_graph">
                <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HOST_NUM'];?></p>
                            <div><span id="total_replicate_os_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_COPY_TASK_NUM'];?></p>
                            <div><span id="replicate_os_task_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TOTAL_COPY_DATA'];?></p>
                            <div><span id="replicate_os_protect_data" class="tag-number"></span><span id="replicate_os_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_HOST_TODAY_COPY_DATA'];?></p>
                            <div><span id="replicate_os_protect_data_today" class="tag-number"></span><span id="replicate_os_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <div class="vmimg">
                        <img src="../../img/visualscreen/cdp_machine_os-icon/cdp-machine-os-back.svg" style="animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite"> 
                        <img src="../../img/visualscreen/machine-os-icon/circle.svg" style="width:409.6px;bottom: 46px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;">
                        <img src="../../img/visualscreen/cdp/cdp-m.svg" style="width:197px;bottom:4.75rem;" alt=""> 
                        <img src="../../img/visualscreen/machine-os-icon/light.svg" style="bottom: 50px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;">
            
                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="width:235px;bottom: 138px;left: 241px;height:40px" alt="">
                        
                       
                        <svg xmlns="http://www.w3.org/2000/svg" width="168" height="1.5" style="position: absolute;bottom: 156px;left: 285px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 10 4;
                                    }
                                </style>
                            </defs>
                            <path id="replicate-os-dot1-line" d="M0,0.75 L168,0.75" class="a"></path>
                        </svg>
                        <img id="replicate-os-dot1" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 129px; left: 185px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">
                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="bottom: 138px;right: 241px;height:40px" alt="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="168" height="1.5" style="position: absolute;bottom: 156px;right: 285px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 10 4;
                                    }
                                </style>
                            </defs>
                            <path id="replicate-os-dot2-line" d="M0,0.75 L168,0.75" class="a"></path>
                        </svg>
                        <img id="replicate-os-dot2" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 129px; right: 520px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">
                        <img src="../../img/visualscreen/machine-os-icon/box.svg" style="width:134px;bottom: 156px;left:0px">
                        <img src="../../img/visualscreen/os-icon/vol-box.svg" style="width:216px;left:7.5%;bottom: 93px;z-index: 12;" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="width:216px;left:7.5%;bottom: 85px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/box.svg" style="width:134px;bottom: 156px;right:0px">
                        <img src="../../img/visualscreen/os-icon/vol-box.svg" style="width:216px;right:7.5%;bottom: 93px;z-index: 12;" alt="">
                        <img src="../../img/visualscreen/machine-os-icon/vol-light.svg" style="width:216px;right:7.5%;bottom: 85px;z-index: 12;animation: visualFade 3s infinite;-webkit-animation: visualFade 3s infinite" alt="">
                    </div>
                </div>
                 <!-- 文件复制 -->
                 <div class="graph-content" id="replicate_file_graph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_PROTECTED_OBJ_NUM'];?></p>
                            <div><span id="total_replicate_file_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_COPY_TASK_NUM'];?></p>
                            <div><span id="replicate_file_task_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_OBJECT_TOTAL_COPY_DATA'];?></p>
                            <div><span id="replicate_file_protect_data" class="tag-number"></span><span id="replicate_file_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_OBJECT_TODAY_COPY_DATA'];?></p>
                            <div><span id="replicate_file_protect_data_today" class="tag-number"></span><span id="replicate_file_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>

                    <div class="vmimg">
                        <img src="../../img/visualscreen/replicate_file/middle-back.svg" style="bottom: 160px;">
                        <img src="../../img/visualscreen/replicate_file/left-light.svg" style="left: 69px;bottom: 69px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;">
                        <img src="../../img/visualscreen/replicate_file/left-file.svg" style="bottom: 160px;left:114px">
                        <img src="../../img/visualscreen/replicate_file/left-cicle.svg" style="width:386.4px;left:49px;bottom:83px;animation:myfirst 5s infinite;">
                        <img src="../../img/visualscreen/replicate_file/left-back.svg" style="left: -68px;bottom: -82px">

                        <img src="../../img/visualscreen/replicate_file/right-light.svg" style="right: 69px;bottom: 69px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;">
                        <img src="../../img/visualscreen/replicate_file/right-file.svg" style="bottom: 160px;right:114px">
                        <img src="../../img/visualscreen/replicate_file/right-cicle.svg" style="width:386.4px;right:46px;bottom:83px;animation:myfirst 5s infinite;">
                        <img src="../../img/visualscreen/replicate_file/right-back.svg" style="right: -68px;bottom: -82px">
                        <img id="replicate-file-img1" src="../../img/visualscreen/replicate_file/middle-file.svg" style="bottom: 194px;left:460px">



                    </div>

                 </div>
                  <!-- 数据库复制 -->
                <div class="graph-content" id="replicate_db_graph">
                    <div class="show-pic-data">
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HOST_NUM'];?></p>
                            <div><span id="total_replicate_db_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_COPY_TASK_NUM'];?></p>
                            <div><span id="replicate_db_task_num" class="tag-number"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_DATABASE_TOTAL_COPY_DATA'];?></p>
                            <div><span id="replicate_db_protect_data" class="tag-number"></span><span id="replicate_db_protect_data_unit" class="tag-unit"></span></div>
                        </div>
                        <div class="vm-pic-data-item">
                            <p class="vm-tag-title"><?php echo $LANG['UI_VISUAL_DATABASE_TODAY_COPY_DATA'];?></p>
                            <div><span id="replicate_db_protect_data_today" class="tag-number"></span><span id="replicate_db_protect_data_today_unit" class="tag-unit"></span></div>
                        </div>
                    </div>
                    <div class="vmimg">
                        <img src="../../img/visualscreen/cdp_machine_os-icon/cdp-machine-os-back.svg" style="animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite"> 
                        <img src="../../img/visualscreen/machine-os-icon/circle.svg" style="width:409.6px;bottom: 48px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;">
                        <img src="../../img/visualscreen/cdp/cdp-m.svg" style="width:197px;bottom:4.75rem;" alt=""> 
                        <img src="../../img/visualscreen/machine-os-icon/light.svg" style="bottom: 50px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;">

                        <img  src="../../img/visualscreen/replicate_db/left-box.svg" style="bottom: 113px;left:33.6px">
                        <img  src="../../img/visualscreen/replicate_db/left-circle.svg" style="width:255.2px;bottom: 85px;left:-8px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;">
                        <img  src="../../img/visualscreen/replicate_db/left-point.svg" style="bottom:257px;left:84.3px">
                        <img  src="../../img/visualscreen/replicate_db/left-point-light.svg" style="bottom: 250px;left:69.3px">
                        <img  src="../../img/visualscreen/replicate_db/left-back.svg" style="bottom: -98px;left:-151.7px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;">

                        <img  src="../../img/visualscreen/replicate_db/right-box.svg" style="bottom: 113px;right:33.6px">
                        <img  src="../../img/visualscreen/replicate_db/right-circle.svg" style="width:255.2px;bottom: 85px;right:-8px;animation:myfirst 5s infinite;-webkit-animation: myfirst 5s infinite;">
                        <img  src="../../img/visualscreen/replicate_db/right-point.svg" style="bottom:257px;right:84.3px">
                        <img  src="../../img/visualscreen/replicate_db/right-point-light.svg" style="bottom: 250px;right:69.3px">
                        <img  src="../../img/visualscreen/replicate_db/right-back.svg" style="bottom: -98px;right:-151.7px;animation: visualFade 4s infinite;-webkit-animation: visualFade 3s infinite;">

                        <img  src="../../img/visualscreen/replicate_db/middle-line.svg" style="bottom: 300.5px;">
                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="bottom: 138px;left: 196px;height:42px;width:282px" alt="">
                        <img src="../../img/visualscreen/cdp/cdp-dot-light-line.svg" style="bottom: 138px;right: 196px;height:42px;width:282px" alt="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="228" height="1.5" style="position:absolute;bottom:158px;left: 227px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 6 4;
                                    }
                                </style>
                            </defs>
                            <path id="repliacte-db-dot1-line" class="a" d="M0,0.75 L228,0.75"></path>
                        </svg>
                        <img id="replicate-db-dot1" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 131px;left:128px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">


                        <svg xmlns="http://www.w3.org/2000/svg" width="230" height="1.5" style="position:absolute;bottom:158px;right: 217px">
                            <defs>
                                <style>
                                    .a {
                                        fill: none;
                                        stroke: #18fef0;
                                        stroke-width: 1.5px;
                                        stroke-dasharray: 6 4;
                                    }
                                </style>
                            </defs>
                            <path id="repliacte-db-dot2-line" class="a" d="M0,0.75 L230,0.75"></path>
                        </svg>
                        <img id="replicate-db-dot2" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 131px;left:626px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:934px ;height:325px;bottom:218px">
                            <path id="repliacte-db-dot3-line" class="a" d="M0,244.8 Q 467 -220 934 235.8"></path>
                        </svg>
                        <img id="replicate-db-dot3" src="../../img/visualscreen/cdp/cdp-dot.svg" alt="" style="width: 1.77rem; height: 1.77rem; position: absolute; bottom: 514px;left:100px; translate: none; rotate: none; scale: none; transform-origin: 50% 50%; transform: translate3d(84.2291px, 0.32813px, 0px);">
                        
                    </div>

                </div>



            </div>
                         
        </div>
        <!-- 右边 -->
        <div class="right-box">
            <!-- 数据统计 -->
            <div class="data-statis">
                <!-- 标题 -->
                <div class="title-box">
                    <div class="d-flex justify-content-space-between" style="height: 100%;">
                        <div class="node-title-box-left">
                            <img class="title-pic">
                            <span class="zh"><?php echo $LANG['UI_VISUAL_DATA_STATISTICS'];?></span>
                            <span class="en screen-notshow_en">DATA STATISTICS</span>
                            <img src="../../img/visualscreen/dotdot.svg" style="vertical-align: text-bottom">
                        </div>
                        <div class="node-title-box-right"></div>
                    </div>
                    <div class="title-bottom">
                        <div class="d-flex">
                            <div class="firstline"></div>
                            <div class="lastline"></div>
                        </div>a
                        <div class="whiteline screen-taskline_en screen-taskline_cn"></div>
                    </div>
                </div>
                <div class="data-box">
                    <div class="data-statistics-top">
                        <div class="data-statistics-top-item backupItem">
                            <div style="width: 4.13rem;height: 100%;position: relative;margin-right: 0.69rem">
                                <img src="../../img/visualscreen/data-data.svg" style="position: absolute;left: 0;right: 0;bottom: 0;top: 0;">
                            </div>
                            <div>
                                <img src="../../img/visualscreen/run-blue.gif" style="width:5.1rem"/>
                                <p class="data-statistics-name"><?php echo $LANG['UI_PLATFORM_DATA_BACKUP'];?></p>
                                <p style="margin: 0;"><span id="total_backup_data" class="data-statistics-num"></span><span id="total_backup_data_unit" class="data-statistics-unit"></span></p>
                            </div>
                        </div>
                        <div class="data-statistics-top-item archiveItem">
                            <div style="width: 4.13rem;height: 100%;position: relative;margin-right: 0.69rem">
                                <img src="../../img/visualscreen/cdp-data.svg" style="position: absolute;left: 0;right: 0;bottom: 0;top: 0;">
                            </div>
                            <div>
                                <img src="../../img/visualscreen/run-green.gif" style="width:5.1rem"/>
                                <p class="data-statistics-name"><?php echo $LANG['UI_PLATFORM_CDP_PROTECT'];?></p>
                                <p style="margin: 0;"><span id="total_cdp_data" class="data-statistics-num"></span><span id="total_cdp_data_unit" class="data-statistics-unit"></span></p>
                            </div>
                        </div>
                        <div class="data-statistics-top-item copyItem">
                            <div style="width: 4.13rem;height: 100%;position: relative;margin-right: 0.69rem">
                                <img src="../../img/visualscreen/copy-data.svg" style="position: absolute;left: 0;right: 0;bottom: 0;top: 0;">
                            </div>
                            <div>
                                <img src="../../img/visualscreen/run-white-new.gif" style="width:5.1rem"/>
                                <p class="data-statistics-name"><?php echo $LANG['UI_PLATFORM_DATA_COPY'];?></p>
                                <p style="margin: 0;"><span id="total_copy_data" class="data-statistics-num"></span><span id="total_copy_data_unit" class="data-statistics-unit"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class='' id="dataStatisticBar" style="height: 279px"></div>
                    <div class='' id="backBarTrendsLine" style="height: 279px"></div>
                </div>


            </div>

        </div>
    </div>
    <!-- 底部 -->
    <div class="visual-bottom">
        <!-- 监控中心 -->
        <div class="monitor-box">
            <!-- 标题 -->
            <div class="title-box">
                <div class="d-flex justify-content-space-between" style="height: 100%;">
                    <div class="node-title-box-left">
                        <img class="title-pic">
                        <span class="zh"><?php echo $LANG['UI_VISUAL_NODE_MONITOR'];?></span>
                        <span class="en screen-notshow_en">NODE MONITORING</span>
                        <img src="../../img/visualscreen/dotdot.svg" style="vertical-align: text-bottom">
                    </div>
                    <div class="node-title-box-right">
                          <!-- 标题导航 -->
                        <div class="swiper-container" id="nodename-swiper">
                        </div>
              
                    </div>
                </div>
                <div class="title-bottom">
                    <div class="d-flex">
                        <div class="firstline"></div>
                        <div class="lastline"></div>
                    </div>
                    <div class="whiteline screen-taskline_en screen-taskline_cn"></div>
                    <!-- <img src="../../img/visualscreen/swiper-trangle.svg" style="position:absolute;bottom:0;right:11.25rem;" alt=""> -->
                </div>
            </div>
            <div class="swiper-contents" id="node-swiper"> 
  

            </div>
        </div>
        <!-- 存储统计 -->
        <div class="store-statistical">
            <!-- 标题 -->
            <div class="title-box">
                <div class="d-flex justify-content-space-between" style="height: 100%;">
                    <div class="node-title-box-left">
                        <img class="title-pic">
                        <span class="zh"><?php echo $LANG['UI_VISUAL_STORAGE_INFO'];?></span>
                        <span class="en screen-notshow_en">STORING STATISTICAL</span>
                        <img src="../../img/visualscreen/dotdot.svg" style="vertical-align: text-bottom">
                    </div>
                </div>
                <div class="title-bottom">
                    <div class="d-flex">
                        <div class="firstline"></div>
                        <div class="lastline"></div>
                    </div>
                    <div class="whiteline screen-taskline_en screen-taskline_cn"></div>
                </div>
            </div>
            <div class="statistical-content">
                <div class="storage-note">
                    <div>
                        <div class="triangle"></div>
                        <span class="storage-note-name"><?php echo $LANG['UI_VISUAL_STORAGE_NUM'];?></span>
                        <span id="storage-num" class="storage-note-num"></span>
                    </div>
                    <div>
                        <div class="triangle"></div>
                        <span class="storage-note-name"><?php echo $LANG['UI_VISUAL_TOTAL_STORAGE'];?></span>
                        <span id="storage-total" class="storage-note-num"></span>
                    </div>
                </div>
                <div id="storageStaticPie" class="storage-static-pie"></div>
                <div class="storage-use">
                    <div style="margin: 1rem 0 0.5rem 1rem">
                        <div style="display:inline-block;line-height:2.95rem;vertical-align:center;width: 0.38rem;height: 0.38rem;background-color: rgba(6, 247, 161, 1);border-radius: 50%"></div>
                        <span class="storage-use-name"><?php echo $LANG['UI_VISUAL_USED'];?></span>
                    </div>
                    <div style="margin-left: 1rem;">
                        <span id="storage-use-num" class="storage-use-num"></span>
                        <span id="storage-use-num-company" class="storage-use-num-company"></span>
                    </div>
                </div>
                <div class="storage-free">
                    <div style="margin: 1rem 0 0.5rem 1rem">
                        <div style="display:inline-block;line-height:2.95rem;vertical-align:center;width: 0.38rem;height: 0.38rem;background-color: rgba(255, 255, 255, 1);border-radius: 50%"></div>
                        <span class="storage-free-name"><?php echo $LANG['UI_VISUAL_NOT_USED'];?></span>
                    </div>
                    <div style="margin-left: 1rem">
                        <span id="storage-free-num" class="storage-free-num"></span>
                        <span id="storage-free-num-company"  class="storage-free-num-company"></span>
                    </div>
                </div>


            </div>

        </div>
        <!-- 存储详情 -->
        <div class="store-detail">
            <!-- 标题 -->
            <div class="title-box">
                <div class="d-flex justify-content-space-between" style="height: 100%;">
                    <div class="node-title-box-left">
                        <img class="title-pic">
                        <span class="zh"><?php echo $LANG['UI_VISUAL_STORAGE_DETAILS'];?></span>
                        <span class="en screen-notshow_en">STORING DETAILS</span>
                        <img src="../../img/visualscreen/dotdot.svg" style="vertical-align: text-bottom">
                    </div>
                </div>
                <div class="title-bottom">
                    <div class="d-flex">
                        <div class="firstline"></div>
                        <div class="lastline"></div>
                    </div>
                    <div class="whiteline screen-taskline_en screen-taskline_cn"></div>
                </div>
                <div class="store-detail-content">
                    <div class="storage-detail-title" >
                        <p style="width:25%;"><?php echo $LANG['UI_VISUAL_STORAGE_NAME'];?></p>
                        <p style="width:20%;"><?php echo $LANG['UI_VISUAL_TYPE'];?></p>
                        <p style="width:45%;"><?php echo $LANG['UI_VISUAL_USE_SPACE'];?></p>
                        <p style="width:10%;text-align: right"><?php echo $LANG['UI_VISUAL_STATUS'];?></p>
                    </div>
                    <div id="storage-detail" class="storage-detail">
                        <ul id="storage-detail-list" class="storage-detail-list">
                        </ul>
                    </div>
                    <div class="bottom-content">
                        
                    </div>
                </div>
            </div>

        </div>

    </div>
    <div>
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="155rem" height="1.21rem"><defs><style>.a,.g,.k,.o{fill:none;}.b{clip-path:url(#a);}.c{clip-path:url(#b);}.d{opacity:0.5;}.e{clip-path:url(#c);}.f{clip-path:url(#d);}.g{stroke:#17d1c8;}.g,.k,.o{stroke-miterlimit:10;}.h{clip-path:url(#e);}.i{clip-path:url(#f);}.j{clip-path:url(#g);}.k{stroke:#1c4b68;stroke-width:6px;}.l{clip-path:url(#h);}.m{clip-path:url(#i);}.n{clip-path:url(#j);}.o{stroke:#18d1c9;}</style><clipPath id="a"><rect class="a" width="2480" height="19.333"/></clipPath><clipPath id="b"><rect class="a" width="2480" height="19.333" transform="translate(0 0)"/></clipPath><clipPath id="c"><rect class="a" width="1171.131" height="19.299" transform="translate(654.569 0.017)"/></clipPath><clipPath id="d"><rect class="a" width="295.414" height="8.833" transform="translate(1507.592 5.25)"/></clipPath><clipPath id="e"><rect class="a" width="295.414" height="8.833" transform="translate(676.994 5.25)"/></clipPath><clipPath id="f"><rect class="a" width="515.278" height="6" transform="translate(982.362 6.667)"/></clipPath><clipPath id="g"><rect class="a" width="515.276" height="6" transform="translate(982.362 6.666)"/></clipPath><clipPath id="h"><rect class="a" width="663.9" height="6.151" transform="translate(1816.1 6.591)"/></clipPath><clipPath id="i"><rect class="a" width="663.901" height="6.151" transform="translate(1816.099 6.591)"/></clipPath><clipPath id="j"><rect class="a" width="663.901" height="6.151" transform="translate(0 6.591)"/></clipPath></defs><g class="b"><g class="c"><g class="d"><g class="e"><g class="f"><line class="g" x1="20.965" y2="18.55" transform="translate(1485.498 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1492.282 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1499.068 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1797.62 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1790.841 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1804.399 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1512.639 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1505.853 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1519.425 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1526.21 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1532.995 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1539.78 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1546.566 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1553.352 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1566.922 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1560.137 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1573.707 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1580.493 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1587.279 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1594.064 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1600.849 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1607.634 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1621.206 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1614.42 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1627.991 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1634.776 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1641.562 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1648.347 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1655.133 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1661.918 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1675.489 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1668.704 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1682.274 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1689.06 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1695.845 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1702.631 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1709.416 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1716.201 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1729.773 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1722.987 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1736.558 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1743.344 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1750.128 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1756.914 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1763.7 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1770.485 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1777.271 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(1784.055 0.392)"/></g><g class="h"><line class="g" x1="20.965" y2="18.55" transform="translate(654.9 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(661.685 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(668.471 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(967.023 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(960.244 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(973.801 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(682.042 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(675.256 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(688.827 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(695.612 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(702.398 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(709.183 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(715.969 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(722.754 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(736.325 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(729.539 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(743.11 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(749.896 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(756.682 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(763.466 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(770.252 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(777.037 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(790.609 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(783.823 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(797.394 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(804.179 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(810.965 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(817.75 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(824.536 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(831.321 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(844.892 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(838.106 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(851.677 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(858.463 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(865.248 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(872.033 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(878.819 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(885.604 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(899.176 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(892.39 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(905.961 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(912.746 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(919.531 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(926.317 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(933.103 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(939.888 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(946.673 0.392)"/><line class="g" x1="20.965" y2="18.55" transform="translate(953.458 0.392)"/></g></g></g><g class="d"><g class="i"><g class="j"><line class="k" x2="515.276" transform="translate(982.362 9.667)"/></g></g></g><g class="d"><g class="l"><g class="m"><line class="k" x2="663.901" transform="translate(1816.099 9.667)"/></g></g></g><g class="d"><g class="n"><g class="n"><line class="k" x2="663.901" transform="translate(0 9.667)"/></g></g></g><g class="d"><g class="c"><line class="o" x2="2480" transform="translate(0 18.833)"/><line class="o" x2="2480" transform="translate(0 0.5)"/></g></g></g></g></svg>
    </div>
</div>

<!-- END CONTENT -->


<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/scripts/conf/config.js" type="text/javascript"></script>
<script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="/scripts/public/public.js" type="text/javascript"></script>
<script src="/lang/<?php 
if($_SESSION['language']){
    $userLang = $_SESSION['language'];
}else{
    $userLang = "zh-cn";
}
echo $userLang; ?>.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript" src="/assets/global/plugins/gsap/gsap.min.js" ></script>
<script type="text/javascript" src="/assets/global/plugins/gsap/MotionPathPlugin.min.js" ></script>
<script src="/assets/global/plugins/echarts/V5.01/echarts.min.js"></script>
<script type="text/javascript" src="/assets/global/plugins/swiper/js/swiper.min.js"></script>

<script type="text/javascript" src="/scripts/plugins/jquery/jquery.browser.js"></script>
<script type="text/javascript" src="/scripts/visualization/noAuth-timeout.js"></script>
<link href="/css/viconfont/iconfont.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="/scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="/scripts/visualscreen/visualscreen.js"></script>

</body>