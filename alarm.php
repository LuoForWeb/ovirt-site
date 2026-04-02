<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <?php
    include_once $_SERVER['DOCUMENT_ROOT'].'/api/load.php';
    $CONF = Xphp::$_config;
    $LANG = require_once './lang/'.$CONF['lang'].$CONF['ext'];
    // 指定下语言包 因为是后台直接读取的
    Xphp::$_lang = $LANG;

    $pfs = require_once APP_PATH.'platform/PFDescription.php';
    // 读取平台操作描述
    Xphp::$_pfdes = $pfs;
    
    // 读取错误码
    Xphp::$_error = require_once API_PATH.'xphp/conf/error.php';

    // 读取虚拟机操作描述
    Xphp::$_vmdes = require_once APP_PATH.'vm/VmDescription.php';

    // 读取数据库操作描述
    Xphp::$_dbdes = require_once APP_PATH.'dbprotect/DbDescription.php';
    
    // 解密参数，如果不对，就提示错误
    $utils = Xphp::instance('utils');
    if (empty($_GET['param'])) {
        echo $LANG['WEB_PUBLIC_OPRATION_UNKNOWN'];die;
    }
    $params = $utils->xphp_decrypt($_GET['param']);

    if (empty($params)) {
        echo $LANG['WEB_PUBLIC_OPRATION_UNKNOWN'];die;
    }

    
    $params = json_decode($params, true);

    if (empty($params['alarmID']) || empty($params['type'])) {
        echo $LANG['WEB_PUBLIC_OPRATION_UNKNOWN'];die;
    }
    // 查询出基本信息，文件信息，日志信息

    $handler = Xphp::instance('AlarmHandler');
    if ($params['type'] == 1) {
        // 系统
        $info = json_decode($handler->getSystemAlarmDetails(['id'=>$params['alarmID']]), true);
    } else {
        //$params['alarmID'] = 95;  // 测试
        $info = json_decode($handler->getTaskAlarmDetails(['id'=>$params['alarmID']]), true);

        if ($info['taskmoduletype'] == 2) {
            // vm
            $handlerf = Xphp::instance('JobHandler');
            $vmlists = json_decode($handlerf->getTaskAlarmDetailsVmInfo(['id'=>$params['alarmID']]), true);
        } elseif ($info['taskmoduletype'] == 3) {
            // fs
            $handlerf = Xphp::instance('FileHandler');
            $files = json_decode($handlerf->getBackupTaskInfo(['alarmid'=>$params['alarmID']]), true);
        } elseif ($info['taskmoduletype'] == 4) { // 659
            //db
            $handlerj = Xphp::instance('JobHandler');
            $dbs = json_decode($handlerj->getTaskAlarmDetailsDbInfo(['id'=>$params['alarmID']]), true);
        }elseif ($info['taskmoduletype'] == 5) { // 148
            //os
            $handlerj = Xphp::instance('JobHandler');
            $os = json_decode($handlerj->getTaskAlarmDetailsOSInfo(['id'=>$params['alarmID']]), true);
        }elseif ($info['taskmoduletype'] == 9) { // 95
            //副本
            $handlerj = Xphp::instance('JobHandler');
            $copys = json_decode($handlerj->getTaskAlarmDetailsCopyInfo(['id'=>$params['alarmID']]), true);
        }elseif ($info['taskmoduletype'] == 11) { // 221
            // nas
            $handlerf = Xphp::instance('FileHandler');
            $files = json_decode($handlerf->getBackupTaskInfo(['alarmid'=>$params['alarmID']]), true);
        }
        //if ($info['taskmoduletype'] != 2) {
            // 获取日志列表
            $logs = json_decode($handler->getTaskAlarmDetailsLogs(['id'=>$params['alarmID'],'type'=>1]), true);
       // }
    }


    /**
     * 特殊配置,主要用于第三方OEM版本
     * 所有配置文件均在根目录下special文件夹,包括配置文件,logo,图片等所有信息
     */
    $configFile = $CONF['SPECIAL_DIR'] . $CONF['SPECIAL_CONFIG'];
    if(file_exists($configFile)){
        $content = file_get_contents($configFile);
        $content = json_decode($content, true);
        setSpecialConfigRecursiveAlarm($CONF, $content);
    }
    function setSpecialConfigRecursiveAlarm(&$arrA, $arrB){
        foreach ($arrB as $key => $value){
            if(is_array($value)){
                setSpecialConfigRecursiveAlarm($arrA[$key], $value);
            }else{
                $arrA[$key] = $value;
            }
        }
    }
    session_start();
    ?>
    <title>
        <?php
        if ($params['type'] == 1) {
            // 系统
            echo $LANG['UI_PLATFORM_ALARM_SYSTEM'];
        } else {
            echo $LANG['UI_PLATFORM_ALARM_TASK'];
        }
        ?>
    </title>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="./assets/global/css/gfonts1.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="./css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
    <link rel="shortcut icon" href="favicon.ico"/>
</head>
<link rel="stylesheet" href="./css/platform/alarm.css">
<body>
<div class="content">
    <div class="main_top">
        <div class="div_item"> <img src="/img/platform/alarm/icon_l.png"></div>
        <div class="div_item"><img src="/img/platform/alarm/bg_r.png" class="imgs"></div>
    </div>
    <ul class="nav top_nav">
        <?php
         if (
                 $params['type'] == 1 || (
                         $params['type'] == 2 && ($info['alarmlevelflag'] == 1 || !in_array($info['taskmoduletype'], [2,3,4,5,9,11]))
                 )
         ) {
             echo '<li class="active">
            <a href="#home" class="nav_def nav_def_1 click">'.$LANG['UI_ALARM_BASE_INFO'].'
                <div class="nav_bot"></div>
            </a>
        </li>';
         } else {
             echo '<li class="active">
            <a href="#home" class="nav_def click">'.$LANG['UI_ALARM_BASE_INFO'].' 
                <div class="nav_bot"></div>
            </a>
        </li>';
             $second_title = '';
             $third_title = $LANG['UI_ALARM_LOG_INFO'];

             if ($info['taskmoduletype'] == 2) {
                 // vm
                 $second_title = $LANG['UI_JOB_VM_INFO'];
                 // 虚拟机 主机信息显示为虚拟机信息
                 if(in_array($info['tasktypeindex'], [17, 18, 19, 20])){
                     $second_title =  $LANG['UI_JOB_VM_INFO'];
                 }
                 echo '<li><a href="#menu1" class="nav_def">'.$second_title.'</a></li>
                        <li><a href="#menu2" class="nav_def">'.$third_title.'</a></li>';
             } elseif ($info['taskmoduletype'] == 3) {
                 // FS
                 $second_title = $LANG['UI_JOB_FILE_INFO'];
                 echo '<li><a href="#menu1" class="nav_def">'.$second_title.'</a></li>
                        <li><a href="#menu2" class="nav_def">'.$third_title.'</a></li>';
             } elseif ($info['taskmoduletype'] == 4) {
                 // DB
                 $second_title = $LANG['UI_DB_INFO'];
                 echo '<li><a href="#menu1" class="nav_def">'.$second_title.'</a></li>
                        <li><a href="#menu2" class="nav_def">'.$third_title.'</a></li>';
             } elseif ($info['taskmoduletype'] == 5) {
                 // OS
                 $second_title = $LANG['UI_VIRTUAL_LAB_HOST_INFO'];
                 echo '<li><a href="#menu1" class="nav_def">'.$second_title.'</a></li>
                        <li><a href="#menu2" class="nav_def">'.$third_title.'</a></li>';
             } elseif ($info['taskmoduletype'] == 9) {
                 // 副本
                 $second_title = $LANG['UI_VIRTUAL_LAB_HOST_INFO'];
                 echo '<li><a href="#menu1" class="nav_def">'.$second_title.'</a></li>
                        <li><a href="#menu2" class="nav_def">'.$third_title.'</a></li>';
             } elseif ($info['taskmoduletype'] == 11) {
                 // NAS
                 $second_title = $LANG['UI_JOB_FILE_INFO'];
                 echo '<li><a href="#menu1" class="nav_def">'.$second_title.'</a></li>
                        <li><a href="#menu2" class="nav_def">'.$third_title.'</a></li>';
             }
             
         }
         ?>

    </ul>
    <div class="tab-content">
        <div id="home" class="tab-pane contents fade in active">
            <div class="main_content">
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_ALARM_ID']?></span>
                    <span class="item_right"><?php echo $info['alarmid'];?></span>
                </div>
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_ALARM_LEVLE']?></span>
                    <?php
                        if ($info['alarmlevelflag'] == 2) {
                            echo ' <span class="item_right item_warning"><span class="item_circle_warn"></span>'.$info['alarmlevel'].'</span>';
                        } elseif ($info['alarmlevelflag'] == 3) {
                            echo ' <span class="item_right item_error"><span class="item_circle_red"></span>'.$info['alarmlevel'].'</span>';
                        } else {
                            echo ' <span class="item_right item_general"><span class="item_circle_general"></span>'.$info['alarmlevel'].'</span>';
                        }
                    ?>
                </div>
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_ALARM_CONTENT']?></span>
                    <span class="item_right"><?php echo $info['alarmcontent'];?></span>
                </div>
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_ALARM_TIME']?></span>
                    <span class="item_right"><?php echo $info['alarmtime'];?></span>
                </div>
            </div>

            <div class="main_content main_content2">
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_ALARM_RESPONSE_FLAG']?></span>
                    <?php
                        if ($info['alarmsolvedflag'] == 1) {
                            echo '<span class="item_right item_res">'.$info['alarmsolveddes'].'</span>';
                        } else {
                            echo '<span class="item_right item_resno">'.$info['alarmsolveddes'].'</span>';
                        }
                    ?>
                </div>
                <div class="item_content" <?php if (empty($info['alarmsolveduser'])) echo 'style="display:none;"';?>>
                    <span class="item_left"><?php echo $LANG['UI_ALARM_RESPONSE_USER']?></span>
                    <span class="item_right"><?php echo $info['alarmsolveduser'];?></span>
                </div>
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_ALARM_RESPONSE_TIME']?></span>
                    <span class="item_right"><?php echo $info['alarmsolvedtime'];?></span>
                </div>
            </div>
            
            <div class="main_content main_content3" <?php if ($params['type'] == 1) echo 'style="display:none;"';?>>
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_JOB_RNAME']?></span>
                    <span class="item_right"><?php echo $info['taskname'];?></span>
                </div>
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_PUBLIC_MODULE_TYPE']?></span>
                    <span class="item_right"><?php echo $info['taskmodule'];?></span>
                </div>
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_PUBLIC_TASK_TYPE']?></span>
                    <span class="item_right"><?php echo $info['tasktype'];?></span>
                </div>
            </div>


            <div class="main_content main_content4" <?php if ($params['type'] == 1) echo 'style="display:none;"';?>>
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['WEB_PLATFORM_DES_NODE']?></span>
                    <span class="item_right"><?php echo $info['tasknode'];?></span>
                </div>
                <div class="item_content" <?php if (in_array($info['tasktypeindex'], [2, 29])) echo 'style="display:none;"';?>>
                    <span class="item_left"><?php echo $LANG['UI_PALTFORM_STORAGE']?></span>
                    <span class="item_right"><?php echo $info['taskstorage'];?></span>
                </div>
            </div>

            <div class="main_content main_content5">
                <div class="item_content">
                    <span class="item_left"><?php echo $LANG['UI_SETTINGS_NOTICE_TYPE_DES']?></span>
                    <span class="item_right"><?php echo $info['sendtypes'];?></span>
                </div>
            </div>
        </div>
        
        <div id="menu1" class="tab-pane fade">
            <?php
            if ($info['taskmoduletype'] == 2) {
                if (!empty($vmlists['data'])) {
                    foreach ($vmlists['data'] as $item) {
                        echo '<div class="main_content main_content_sec">
                <div class="item_content ">
                    <span class="item_left">'.$LANG['UI_VCENTER_MACHINE_NAME'].'</span>
                    <span class="item_right">'.$item[1] . '</span>
                </div>
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_PUBLIC_STATUS'].'</span>';
                        if ($item[3] == 6) {
                            echo ' <span class="item_right item_warning"><span class="item_circle_warn"></span>'.$item[2].'</span>';
                        } elseif ($item[3] == 4) {
                            echo ' <span class="item_right item_error"><span class="item_circle_red"></span>'.$item[2].'</span>';
                        } else {
                            echo ' <span class="item_right item_general"><span class="item_circle_general"></span>'.$item[2].'</span>';
                        }
                        echo '</div>
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_PUBLIC_DESCRIPTION'].'</span>
                    <span class="item_right">'. $item[4] .'</span>
                </div>
            </div>';
                    }
                } else {
                    echo '<div class="noresult"><img src="/img/platform/alarm/noresult.png"><div class="text">'. $LANG['WEB_PLATFORM_DC_NO_DATA'].'</div></div>';
                }
            }

            ?>
            <?php
            if (in_array($info['taskmoduletype'], [3, 11])){
                if (!empty($files)) {
                    foreach ($files as $item) {
                        echo '<div class="main_content">
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_JOB_FILE_SRC_HOST_NAME'].'</span>
                    <span class="item_right">'. ($info['taskmoduletype'] == 3 ? $item['agentName'] : $item['agentIP']) . '('. $item['agentIP'] .')</span>
                </div>

                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_BACKUP_FILE_LIST'].'</span>
                    <span class="item_right">'. implode('</br>', $item['file_list']) .'</span>
                </div>
            </div>';
                    }
                }else {
                    echo '<div class="noresult"><img src="/img/platform/alarm/noresult.png"><div class="text">'. $LANG['WEB_PLATFORM_DC_NO_DATA'].'</div></div>';
                }
            }
            ?>

            <?php
            if ($info['taskmoduletype'] == 4) {
                if (!empty($dbs['data'])) {
                    foreach ($dbs['data'] as $item) {
                        echo '<div class="main_content">
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_DB_DATABASE_NAME'].'</span>
                    <span class="item_right">'.$item[1] . '</span>
                </div>
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_PUBLIC_STATUS'].'</span>';

                        if ($item[3] == 1) {
                            echo ' <span class="item_right item_warning"><span class="item_circle_warn"></span>'.$item[2].'</span>';
                        } elseif ($item[3] == 4) {
                            echo ' <span class="item_right item_error"><span class="item_circle_red"></span>'.$item[2].'</span>';
                        } else {
                            echo ' <span class="item_right item_general"><span class="item_circle_general"></span>'.$item[2].'</span>';
                        }

                        echo '</div>
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_PUBLIC_DESCRIPTION'].'</span>
                    <span class="item_right">'. $item[4] .'</span>
                </div>
            </div>';
                    }
                } else {
                    echo '<div class="noresult"><img src="/img/platform/alarm/noresult.png"><div  class="text">'. $LANG['WEB_PLATFORM_DC_NO_DATA'].'</div></div>';
                }
            }

            ?>

            <?php
            if ($info['taskmoduletype'] == 5) {
                if (!empty($os['data'])) {
                    foreach ($os['data'] as $item) {
                        echo '<div class="main_content main_content_sec">
                <div class="item_content ">
                    <span class="item_left">'.$LANG['UI_AGENT_HOST_NAME'].'</span>
                    <span class="item_right">'.$item[1] . '</span>
                </div>
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_PUBLIC_STATUS'].'</span>';
                        if ($item[3] == 6) {
                            echo ' <span class="item_right item_warning"><span class="item_circle_warn"></span>'.$item[2].'</span>';
                        } elseif ($item[3] == 8) {
                            echo ' <span class="item_right item_error"><span class="item_circle_red"></span>'.$item[2].'</span>';
                        } else {
                            echo ' <span class="item_right item_general"><span class="item_circle_general"></span>'.$item[2].'</span>';
                        }
                        echo '</div>
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_PUBLIC_DESCRIPTION'].'</span>
                    <span class="item_right">'. $item[4] .'</span>
                </div>
            </div>';
                    }
                } else {
                    echo '<div class="noresult"><img src="/img/platform/alarm/noresult.png"><div class="text">'. $LANG['WEB_PLATFORM_DC_NO_DATA'].'</div></div>';
                }
            }

            ?>

            <?php
            if ($info['taskmoduletype'] == 9) {
                if (!empty($copys['data'])) {
                    foreach ($copys['data'] as $item) {
                        echo '<div class="main_content main_content_sec">
                <div class="item_content ">
                    <span class="item_left">'.$LANG['UI_AGENT_HOST_NAME'].'</span>
                    <span class="item_right">'.$item[1] . '</span>
                </div>
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_PUBLIC_STATUS'].'</span>';
                        if ($item[3] == 4) {
                            echo ' <span class="item_right item_warning"><span class="item_circle_warn"></span>'.$item[2].'</span>';
                        } elseif ($item[3] == 2) {
                            echo ' <span class="item_right item_error"><span class="item_circle_red"></span>'.$item[2].'</span>';
                        } else {
                            echo ' <span class="item_right item_general"><span class="item_circle_general"></span>'.$item[2].'</span>';
                        }
                        echo '</div>
                <div class="item_content">
                    <span class="item_left">'.$LANG['UI_PUBLIC_DESCRIPTION'].'</span>
                    <span class="item_right">'. $item[4] .'</span>
                </div>
            </div>';
                    }
                } else {
                    echo '<div class="noresult"><img src="/img/platform/alarm/noresult.png"><div class="text">'. $LANG['WEB_PLATFORM_DC_NO_DATA'].'</div></div>';
                }
            }

            ?>

        </div>
        <div id="menu2" class="tab-pane fade">
            <div class="main_content_log">
                <div class="item_content_log"><?php 
                    if (!empty($logs)) {echo ($logs['ext']['tasklog']) ?: '<div class="noresult"><img src="/img/platform/alarm/noresult.png"><div class="text">'. $LANG['WEB_PLATFORM_DC_NO_DATA'].'</div></div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        
    </div>
    
</div>


<script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>

<?php
if($CONF['lang'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-'.
        $CONF['lang'].'.js" type="text/javascript"></script>';
}
?>

<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="./lang/<?php echo $CONF['lang']?>.js" type="text/javascript"></script>
<script src="./scripts/conf/config.js" type="text/javascript"></script>
<script src="./scripts/public/public.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="./scripts/platform/alarm.js" type="text/javascript"></script>
<script>
    $(document).ready(function(){
        $(".top_nav a").click(function(){
            $(this).tab('show');
            $('.top_nav li a').removeClass('click');
            $('.top_nav li a .nav_bot').remove();
            $(this).addClass('click');
            $(this).append(' <div class="nav_bot"></div>');
        });
    });


    $(document).ready(function() {
        var tabContainer = $('.content');
        var length = 1;
        // 计算个数
        length = $('.top_nav').children().length;
        var currentIndex = 1,temIndex;

        tabContainer.on('touchstart', function(e) {
            var touchStartX = e.originalEvent.touches[0].clientX;
            tabContainer.on('touchmove', function(e) {
                var touchMoveX = e.originalEvent.touches[0].clientX;
                var distance = touchMoveX - touchStartX;
                temIndex = 1;
                if (distance > 50 && currentIndex > 1) {
                    // 向左滑动
                    //currentIndex--;
                    temIndex --;
                } else if (distance < -50 && currentIndex < length) {
                    // 向右滑动
                   // currentIndex++;
                    temIndex ++;
                }
            });

            tabContainer.on('touchend', function() {
                tabContainer.off('touchmove');
                tabContainer.off('touchend');
                // 这里进行tab显示与隐藏
                if (temIndex != 1) {
                    if (temIndex > 1) {
                        // 表示有改变
                        currentIndex = currentIndex + 1;
                    } else{
                        currentIndex = currentIndex - 1;
                    }
                    var that = $('.top_nav li').eq(currentIndex-1).find('a');
                    that.tab('show');
                    $('.top_nav li a').removeClass('click');
                    $('.top_nav li a .nav_bot').remove();
                    that.addClass('click');
                    that.append(' <div class="nav_bot"></div>');
                }
            });
        });
    });
</script>
</body>
</html>