<?php
    include_once '../../tpl/permission.php';
    $userAllPermission = $_SESSION['permission'];
?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<link href="../../assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css">
<link href="./css/platform/databackup-center.css" rel="stylesheet" type="text/css">
<div class="datacenter-content">
    <?php
    if ($_SESSION['userLevel'] != 2) {//不是sysadmin登录
        echo '<div class="row mb16">
        <!-- 系统信息汇总开始 -->
        <div class="col-md-6 col-sm-6 allInfo pr0">
            <div class="col-md-3 col-sm-3 cardbox pl0 littleScreen-pr">
                <div class="card">
                    <div class="viconfont vicon-a-Stopwatchmiaobiao"></div>
                    <div class="serverRunTime data"></div>
                    <span class="datades">'.$LANG['UI_HOMEPAGE_RUNNING_TIME'].'</span>
                    <div class="bgimg"></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-3 cardbox pl0 littleScreen-pr">
                <div class="card">
                    <div class="viconfont vicon-a-Protectbaohu2"></div>
                    <div class="serverProtectData data"></div>
                    <span class="datades">'.$LANG['UI_DATACENTER_TOTAL_DATA_SIZE'].'</span>
                    <div class="bgimg"></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-3 cardbox pl0 littleScreen-pr" id="curtask">
                <div class="card">
                    <div class="viconfont vicon-a-OrderIcon"></div>
                    <div class="serverCurrentTask data"></div>
                    <span class="datades">'.$LANG['UI_JOB_CURRENT'].'</span>
                    <div class="bgimg"></div>';
        //未授权任务模块不显示跳转
        if(in_array("current_job", $userAllPermission)){
            echo '<div class="arrow"></div>';
        }
        echo '
                </div>
            </div>';
        //历史任务
        echo '<div class="col-md-3 col-sm-3 cardbox pl0 littleScreen-pr" id="histask">
                            <div class="card">
                                <div class="viconfont vicon-Icon"></div>
                                <div class="serverHisTask data"></div>
                                <span class="datades"></span>
                                <div class="bgimg"></div>';
        //未授权任务模块不显示跳转
        if(in_array("history_job", $userAllPermission)){
            echo '<div class="arrow"></div>';
        }
        echo '
                            </div>
                        </div>';
        //碳排放
        echo '<div class="col-md-3 col-sm-3 cardbox pl0 littleScreen-pr display-none" id="carbonMonitor">
                            <div class="card">
                                <div class="viconfont vicon-carbon"></div>
                                <div class="carbonMonitorNum data"></div>
                                <span class="datades">'.$LANG['UI_HOMEPAGE_CARBON_NUM'].'</span>
                                <div class="bgimg"></div>
                                <div class="arrow"></div>';
        echo '
                            </div>
                        </div>
                </div>

        <!-- 系统信息汇总结束 -->
        <!-- 任务状态开始 -->
        <div class="col-md-6 col-sm-6 pl0 taskboxdiv">
            <div class="taskbox">
                <div class="data-top">
                    <div class="taskStatusTitle">'.$LANG['UI_PUBLIC_JOB'].'</div>
                    <div class="greyline"></div>
                </div>
                <div class="taskStatus">
                    <div class="eachStatus">
                        <div calss="each-status-content">
                            <span>'. $LANG['UI_HOMEPAGE_STATUS_RUNNING'] .'</span>
                            <div class="runNum data"></div>
                        </div> 
                    </div>
                    <div class="eachStatus">
                        <div calss="each-status-content">
                            <span>'.$LANG['WEB_PLATFORM_DES_WAITING'].'</span>
                            <div class="waitNum data"></div>
                        </div> 
                    </div>
                    <div class="eachStatus">
                        <div calss="each-status-content">
                            <span>'.$LANG['WEB_PLATFORM_DES_STOP'].'</span>
                            <div class="stopNum data"></div>
                        </div> 
                    </div>
                    <div class="eachStatus">
                        <div calss="each-status-content">
                            <span>'. $LANG['WEB_PLATFORM_DES_ABNORMAL'].'</span>
                            <div class="abnormalNum data"></div>
                        </div> 
                    </div>
                    <div class="eachStatus">
                        <div calss="each-status-content">
                            <span>'. $LANG['WEB_PUBLIC_FAILURE_HOMEPAGE'].'</span>
                            <div class="failNum data"></div>
                        </div> 
                    </div>
                </div>
            </div>

        </div>
        <!-- 任务状态结束 -->
    </div>';
    }
    ?>

    <!-- 数据保护开始 -->
    <div class="row mb16">
        <div class="col-md-8 dataprotectdiv">
            <div class="dataprotect">
                <div class="data-top">
                    <span class="taskStatusTitle"><?php echo $LANG['UI_HOMEPAGE_DATA_PROTECT']?></span>
                    <div class="greyline"></div>
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <?php
                            if(in_array("copy", $userAllPermission)){
                                echo '<li class="copy" role="presentation"><a href="#copy_pane" aria-controls="copy" role="tab" data-toggle="tab" id="copy_tab">' . $LANG['UI_PLATFORM_DATA_COPY']. '</a></li>';
                            }
                        ?>
                        <?php
                            if(in_array("vol_cdp_protect", $userAllPermission)){
                                echo '<li class="vol_cdp_protect" role="presentation"><a href="#vol_cdp_protect_pane" aria-controls="vol_cdp_protect" role="tab" data-toggle="tab" id="vol_cdp_protect_tab">' . $LANG['UI_PLATFORM_CDP_PROTECT'] . '</a></li>';
                            }
                        ?>
                        <?php
                            if(in_array("backup", $userAllPermission)){
                                echo '<li class="backup" role="presentation"><a href="#backup_pane" aria-controls="backup" role="tab" data-toggle="tab" id="backup_tab"> '. $LANG['UI_PLATFORM_DATA_BACKUP'] .'</a></li>';
                            }
                        ?>
                        <span class="dataSlider"></span>
                    </ul>
                </div>
                <!-- Tab panes -->
                <div class="tab-content" style="height: calc(100% - 48px);">
                    <!-- 数据备份 -->
                    <div role="tabpanel" class="tab-pane each-protect-pane" id="backup_pane">
                        <div class="col-md-7 col-xs-7 backup-letf_pane auth-system">
                            <div id="taskPie"></div>
                            <div class = "content-form">
                                <dl class="each-module cloud hidden">
                                    <dt>
                                        <span class="cloud">
                                            <span class="square"></span><?php echo $LANG['UI_HOMEPAGE_CLOUD']?>
                                        </span>
                                        <span class="number"></span>
                                    </dt>
                                    <?php
                                        if(in_array("vmprotect", $userAllPermission)){
                                            echo '
                                            <dd class="vmprotect">
                                                <span>
                                                    <span class="circle"></span>'. $LANG['UI_PLATFORM_VM_VIRTUAL'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                    <?php
                                        if(in_array("prcloud_protect", $userAllPermission)){
                                            echo '
                                            <dd class="prcloud_protect">
                                                <span>
                                                    <span class="circle"></span>'. $LANG['WEB_PLATFORM_DES_PRIVATE_CLOUD'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                    <?php
                                        if(in_array("awsprotect", $userAllPermission)){
                                            echo '
                                            <dd class="awsprotect">
                                                <span>
                                                    <span class="circle"></span>'. $LANG['WEB_PLATFORM_DES_PUBLIC_CLOUD'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                    <?php
                                        if(in_array("k8s_protect", $userAllPermission)){
                                            echo '
                                            <dd class="k8s_protect">
                                                <span>
                                                    <span class="circle"></span>'. $LANG['UI_ARCHIVE_CLOUD_CONTAIN'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                </dl>
                                <dl class="each-module file hidden">
                                    <dt>
                                        <span class="file">
                                            <span class="square"></span><?php echo $LANG['UI_FILE_FILE']?>
                                        </span>
                                        <span class="number"></span>
                                    </dt>
                                    <?php
                                        if(in_array("filebackup", $userAllPermission)){
                                            echo '
                                            <dd class="filebackup">
                                                <span>
                                                    <span class="circle"></span>'. $LANG['UI_FILE_FILE'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                    <?php
                                        if(in_array("nas_protect", $userAllPermission)){
                                            echo '
                                            <dd class="nas_protect">
                                                <span>
                                                    <span class="circle"></span>NAS
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                    <?php
                                        if(in_array("obs_protect", $userAllPermission)){
                                            echo '
                                            <dd class="obs_protect">
                                                <span>
                                                    <span class="circle"></span>'. $LANG['WEB_PLATFORM_DES_OBS'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                     <?php
                                        if(in_array("hadoop_protect", $userAllPermission)){
                                            echo '
                                            <dd class="hadoop_protect">
                                                <span>
                                                    <span class="circle"></span>Hadoop
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                </dl>
                                <dl class="each-module machine hidden">
                                    <dt>
                                        <span class="machine">
                                            <span class="square"></span><?php echo $LANG['UI_COMPLETE_MACHINE']?>
                                        </span>
                                        <span class="number"></span>
                                    </dt>
                                    <?php
                                        if(in_array("complete_machine", $userAllPermission)){
                                            echo '
                                            <dd class="complete_machine">
                                                <span>
                                                    <span class="circle"></span>'. $LANG['UI_COMPLETE_MACHINE'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                    <?php
                                        if(in_array("osbackup", $userAllPermission)){
                                            echo '
                                            <dd class="osbackup">
                                                <span>
                                                    <span class="circle"></span>'. $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                </dl>
                                <dl class="each-module application hidden">
                                    <dt>
                                        <span class="application">
                                            <span class="square"></span><?php echo $LANG['UI_REPORT_CDP_YINGYONG']?>
                                        </span>
                                        <span class="number"></span>
                                    </dt>
                                    <?php
                                        if(in_array("db_protect", $userAllPermission)){
                                            echo '
                                            <dd class="db_protect">
                                                <span>
                                                    <span class="circle"></span>'. $LANG['BILLING_DB'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                    <?php
                                        if(in_array("office365_protect", $userAllPermission)){
                                            echo '
                                            <dd class="office365_protect">
                                                <span>
                                                    <span class="circle"></span>M365
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                </dl>
                            </div>
                        </div>
                        <div class="col-md-7 col-xs-7 backup-letf_pane no-auth-system">
                            <img src="/assets/global/img/no-data.svg" class="no-data-img">
                            <span><?php echo $LANG['WEB_ERROR_BD_SYSTEM_NOT_AUTH_ERROR']?><span class="addAuth"><?php echo $LANG['UI_HOMEPAGE_TO_AUTH']?></span></span>
                            <!-- 避免没有div页面报错 -->
                            <!-- <div id="taskPie" class="display-none"></div> -->
                        </div>  
                        <div class="col-md-5 col-xs-5 backup-right_pane">
                            <div id="taskBarChart"></div>
                        </div>
                    </div>
                     <!-- 连续数据保护 -->
                    <div role="tabpanel" class="tab-pane each-protect-pane" id="vol_cdp_protect_pane">
                        <div class="col-md-5 col-xs-5 backup-letf_pane">
                            <div id="volCdpPie"></div>
                            <div class = "content-form vol_cdp">
                                <dl class="each-module vol_cdp">
                                    <?php
                                        if(in_array("complete_cdp_backup", $userAllPermission)){
                                            echo '
                                            <dd class="complete_cdp_backup">
                                                <span>
                                                    <span class="square square-green"></span>'. $LANG['UI_COMPLETE_MACHINE'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                        if(in_array("vol_cdp_backup", $userAllPermission)){
                                            echo '
                                            <dd class="vol_cdp_backup">
                                                <span>
                                                    <span class="square square-blue"></span>'. $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                </dl>
                            </div>
                        </div>
                        <div class="col-md-7 col-xs-7 backup-right_pane">
                            <div id="volCdpChart"></div>
                        </div>
                    </div>
                    <!-- 数据复制 -->
                    <div role="tabpanel" class="tab-pane each-protect-pane" id="copy_pane">
                        <div class="col-md-5 col-xs-5 backup-letf_pane">
                            <div id="copyPie"></div>
                            <div class = "content-form copy-class">
                                <dl class="each-module copy-class">
                                     <?php
                                        if(in_array("machine_copy", $userAllPermission)){
                                            echo '
                                            <dd class="machine_copy">
                                                <span>
                                                    <span class="square square-green"></span>'. $LANG['UI_COMPLETE_MACHINE'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                        if(in_array("vol_cdp_copy", $userAllPermission)){
                                            echo '
                                            <dd class="vol_cdp_copy">
                                                <span>
                                                    <span class="square square-blue"></span>'. $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                        if(in_array("file_copy_protect", $userAllPermission)){
                                            echo '
                                            <dd class="file_copy">
                                                <span>
                                                    <span class="square square-file"></span>'. $LANG['UI_PLATFORM_FILES'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                        if(in_array("dbcdpcopy", $userAllPermission)){
                                            echo '
                                            <dd class="dbcdpcopy">
                                                <span>
                                                    <span class="square square-db"></span>'. $LANG['UI_PLATFORM_DATABASE'] .'
                                                </span>
                                                <span class="number"></span>
                                            </dd>
                                            ';
                                        }
                                    ?>
                                </dl>
                            </div>
                        </div>
                        <div class="col-md-7 col-xs-7 backup-right_pane">
                            <div id="copyChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-4 pl0 storagecenterdiv">
            <div class="dataprotect storagecenter">
                <div class="data-top">
                    <span class="taskStatusTitle"><?php echo $LANG['UI_PALTFORM_STORAGE']?></span>
                    <div class="greyline"></div>
                </div>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="bakStorage">
                        <!-- 备份 -->
                        <div class="col-md-4 col-sm-4 storage-circle-content">
                            <div class="box">
                                <div id="capacityCircle"></div>
                            </div>
                        </div>
                        <div class="col-md-7 col-sm-7 pl0 storage-des-content">
                            <div class="top">
                                <div class="top-each">
                                    <span><?php echo $LANG['UI_STORAGE_TOTAL_SIZE']?></span>
                                    <span class="storageCap"></span>
                                    <span class="storageCapUnit">TB</span>
                                </div>
                                <div class="storage-top-right top-each">
                                    <span><?php echo $LANG['UI_DATACENTER_STORAGE_NUM']?></span>
                                    <span class="storageNum"></span>
                                    <span><?php echo $LANG['BILLING_UNIT_ONE']?></span>
                                </div>
                            </div>
                            <div class="bottom">
                                <div class="bottom-each">
                                    <div>
                                        <span class="squre"></span><?php echo $LANG['UI_HOMEPAGE_USED_STORAGE']?>
                                    </div>
                                    <div>
                                        <span class="squre grey"></span><?php echo $LANG['UI_HOMEPAGE_REMAINING_STORAGE']?>
                                    </div>
                                </div>
                                <div class="bottom-each">
                                    <div class="usedPercent"></div>
                                    <div class="remainPercent"></div>
                                </div>
                                <div class="bottom-each">
                                    <div>
                                        <span class="usedNum"></span>
                                        <span class="usedUnit"></span>
                                    </div>
                                    <div>
                                        <span class="remianNum "></span>
                                        <span class="remianUnit"></span>
                                    </div>
                                </div>
                            </div>
                            <div id="storageTips" class="display-none"><?php echo $LANG['UI_HOMEPAGE_STORAGE_NO_ENOUGH']?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 数据保护结束 -->
    <!-- 系统监控开始 -->
    <div class="systemMonitor">
        <div class="data-top">
            <div class="taskStatusTitle"><?php echo $LANG['UI_SYSTEM_MONITOR_SYSTEM_MONITOR']?></div>
            <div class="greyline"></div>
            <div class="">
                <select class="form-control select2me" name="standbyhost" id="node_uuid"></select>
                <i class="fa fa-angle-down"></i>
            </div>
        </div>
        <div class="sysmoni-bottom">
            <div class="col-md-2 col-sm-2 pl0 littleScreen-pr cpu-memerydiv">
                <div class="cpu-memery">
                    <div class="col-md-12 col-sm-6 padding0 cpu-memery-half">
                        <div class="cpu">
                            <div class="col-md-7 col-sm-7 padding0">
                                <div class="littleLabel">CPU</div>
                                <div class="img img1"></div>
                                <span><?php echo $LANG['UI_DATACENTER_CPU_RATE']?></span>
                            </div>
                            <div class="col-md-2 cpu-memery-sapce col-sm-2 padding0"></div>
                            <div class="col-md-3 cpu-memery-w col-sm-3 padding0">
                                <div id="cpuCircle"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-6 padding0 cpu-memery-half">
                        <div class="memery">
                            <div class="col-md-7 col-sm-7 padding0 ">
                                <div class="littleLabel"><?php echo $LANG['UI_DRILLS_MEMORY']?></div>
                                <div class="img img2"></div>
                                <span><?php echo $LANG['UI_DATACENTER_MEMORY_RATE']?></span>
                            </div>
                            <div class="col-md-2 col-sm-2 cpu-memery-sapce padding0"></div>
                            <div class="col-md-3 col-sm-3 cpu-memery-w padding0">
                                <div id="memeryCircle"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 pl0 littleScreen-pr networkTrafficdiv">
                <div class="networkTraffic">
                    <div id="networkTrafficChart"></div>
                </div>
            </div>
            <div class="speeddiv">
                <div class="col-md-2 col-sm-2 pl0 littleScreen-pr bpsdiv">
                    <div class="bps">
                        <div class="littleLabel"><?php echo $LANG['UI_HOMEPAGE_MONITOR_BPS']?></div>
                        <div class="top">
                            <div class="blueLine"></div>
                            <p><?php echo $LANG['UI_HOMEPAGE_MONITOR_READ']?>：</p><span class="bpsread"></span>
                        </div>
                        <div class="bottom">
                            <div class="greenLine"></div>
                            <p><?php echo $LANG['UI_HOMEPAGE_MONITOR_WRITE']?>：</p><span class="bpswrite"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-2 pl0 littleScreen-pr iopsdiv">
                    <div class="iops">
                        <div class="littleLabel"><?php echo $LANG['UI_HOMEPAGE_MONITOR_IOPS']?></div>
                        <div class="top">
                            <div class="blueLine"></div>
                            <p><?php echo $LANG['UI_HOMEPAGE_MONITOR_READ']?>：</p><span class="iopsread"></span>
                        </div>
                        <div class="bottom">
                            <div class="greenLine"></div>
                            <p><?php echo $LANG['UI_HOMEPAGE_MONITOR_WRITE']?>：</p><span class="iopswrite"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 系统监控结束 -->
    <div class="height24"></div>
    <!-- 备份的tooltips -->
    <div class="back-bar-tooltip-content display-none">
        <div class="top-title">
            <?php echo $LANG['UI_HOMEPAGE_PROTECTED_DATA']?>
        </div>
        <div class="modules-container">
            <?php
             if (in_array("vmprotect", $userAllPermission) || in_array("prcloud_protect", $userAllPermission)
                || in_array("awsprotect", $userAllPermission) || in_array("k8s_protect", $userAllPermission)) {
                echo '
                        <div class="large-title-content">
                            <div class="group-content">
                                <span>
                                    <span class="square square-green"></span>
                                    '. $LANG['UI_HOMEPAGE_CLOUD'] .'
                                </span>
                                <span class="cloud_des"></span>
                            </div>';
                            if (in_array("vmprotect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span> '. $LANG['UI_PLATFORM_VM_VIRTUAL'] .' </span>
                                            <span class="vmData_des"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("prcloud_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span>'. $LANG['WEB_PLATFORM_DES_PRIVATE_CLOUD'] .'</span>
                                            <span class="privateData_des"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("awsprotect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span>'. $LANG['WEB_PLATFORM_DES_PUBLIC_CLOUD'] .'</span>
                                            <span class="publicData_des"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("k8s_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span>'. $LANG['WEB_KUBE_RECOVERY_CONTAINER'] .'</span>
                                            <span class="k8sData_des"></span>
                                        </div>
                                    ';
                            }
                echo    '</div>';
            }
            ?>
            <?php
             if (in_array("filebackup", $userAllPermission) || in_array("nas_protect", $userAllPermission)
                || in_array("obs_protect", $userAllPermission) || in_array("hadoop_protect", $userAllPermission)) {
                echo '
                        <div class="large-title-content">
                            <div class="group-content">
                                <span>
                                    <span class="square square-blue"></span>
                                    '. $LANG['WEB_PLATFORM_DES_FS'] .'
                                </span>
                                <span class="file_des"></span>
                            </div>';
                            if (in_array("filebackup", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span> '. $LANG['WEB_PLATFORM_DES_FS'] .' </span>
                                            <span class="fileData_des"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("nas_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span>NAS</span>
                                            <span class="nasData_des"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("obs_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span>'. $LANG['WEB_PLATFORM_DES_OBS'] .'</span>
                                            <span class="obsData_des"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("hadoop_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span>Hadoop</span>
                                            <span class="hadoopData_des"></span>
                                        </div>
                                    ';
                            }
                echo    '</div>';
            }
            ?>
            <?php
             if (in_array("complete_machine", $userAllPermission) || in_array("osbackup", $userAllPermission)) {
                echo '
                        <div class="large-title-content">
                            <div class="group-content">
                                <span>
                                    <span class="square square-file"></span>
                                    '. $LANG['UI_COMPLETE_MACHINE'] .'
                                </span>
                                <span class="machine_des"></span>
                            </div>';
                            if (in_array("complete_machine", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span> '. $LANG['UI_COMPLETE_MACHINE'] .' </span>
                                            <span class="machineData_des"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("osbackup", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span>'. $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'] .'</span>
                                            <span class="volData_des"></span>
                                        </div>
                                    ';
                            }
                echo    '</div>';
            }
            ?>
            <?php
             if (in_array("db_protect", $userAllPermission) || in_array("office365_protect", $userAllPermission)) {
                echo '
                        <div class="large-title-content">
                            <div class="group-content">
                                <span>
                                    <span class="square square-db"></span>
                                    '. $LANG['UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_APPLY'] .'
                                </span>
                                <span class="application_des"></span>
                            </div>';
                            if (in_array("db_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span> '. $LANG['UI_PLATFORM_DB'] .' </span>
                                            <span class="dbData_des"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("office365_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span>M365</span>
                                            <span class="exchangeData_des"></span>
                                        </div>
                                    ';
                            }
                echo    '</div>';
            }
            ?>
        </div>
    </div>
    <!-- 备份的tooltips -->
    <!-- 实时的tooltips -->
    <div class="cdp-bar-tooltip-content display-none">
        <div class="top-title">
            <?php echo $LANG['UI_HOMEPAGE_PROTECTED_DATA']?>
        </div>
        <div class="modules-container">
            <?php
                 if (in_array("complete_cdp_backup", $userAllPermission)) {
                    echo '
                            <div class="large-title-content">
                                <div class="group-content">
                                    <span>
                                        <span class="square square-green"></span>
                                        '. $LANG['UI_COMPLETE_MACHINE'] .'
                                    </span>
                                    <span class="complete_cdp_backup_data_des"></span>
                                </div>
                            </div>
                        ';
                }
                if (in_array("vol_cdp_backup", $userAllPermission)) {
                    echo '
                            <div class="large-title-content">
                                <div class="group-content">
                                    <span>
                                        <span class="square square-blue"></span>
                                        '. $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'] .'
                                    </span>
                                    <span class="vol_cdp_backup_data_des"></span>
                                </div>
                            </div>
                        ';
                }
            ?>
        </div>
    </div>
    <!-- 实时的tooltips -->
    <!-- 复制的tooltips -->
    <div class="copy-bar-tooltip-content display-none">
        <div class="top-title">
            <?php echo $LANG['UI_HOMEPAGE_PROTECTED_DATA']?>
        </div>
        <div class="modules-container">
            <?php
                 if (in_array("machine_copy", $userAllPermission)) {
                    echo '
                            <div class="large-title-content">
                                <div class="group-content">
                                    <span>
                                        <span class="square square-green"></span>
                                        '. $LANG['UI_COMPLETE_MACHINE'] .'
                                    </span>
                                    <span class="machine_copy_data_des"></span>
                                </div>
                            </div>
                        ';
                }
                if (in_array("vol_cdp_copy", $userAllPermission)) {
                    echo '
                            <div class="large-title-content">
                                <div class="group-content">
                                    <span>
                                        <span class="square square-blue"></span>
                                        '. $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'] .'
                                    </span>
                                    <span class="vol_cdp_copy_data_des"></span>
                                </div>
                            </div>
                        ';
                }
                if (in_array("file_copy_protect", $userAllPermission)) {
                    echo '
                            <div class="large-title-content">
                                <div class="group-content">
                                    <span>
                                        <span class="square square-file"></span>
                                        '. $LANG['WEB_PLATFORM_DES_FS'] .'
                                    </span>
                                    <span class="file_copy_data_des"></span>
                                </div>
                            </div>
                        ';
                }
                if (in_array("dbcdpcopy", $userAllPermission)) {
                    echo '
                            <div class="large-title-content">
                                <div class="group-content">
                                    <span>
                                        <span class="square square-db"></span>
                                        '. $LANG['UI_PLATFORM_DB'] .'
                                    </span>
                                    <span class="dbcdpcopy_data_des"></span>
                                </div>
                            </div>
                        ';
                }
            ?>
        </div>
    </div>
    <!-- 复制的tooltips -->
</div>





<!-- BEGIN PAGE LEVEL PLUGINS -->
<!-- <script src="./scripts/plugins/flexible.js"></script> -->
<script type="text/javascript" src="../../assets/global/plugins/jquery-fontFlex/jQuery.fontFlex.js" ></script>
<script src="./assets/global/plugins/echarts/V5.4.3/echarts.min.js"></script>
<script src="./assets/global/plugins/counterup/jquery.waypoints.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/moment.min.js"></script>
<script type="text/javascript" src="./scripts/platform/databackup_center_vinchin.js"></script>

<!-- END PAGE LEVEL PLUGINS -->