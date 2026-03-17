
<?php
    include_once '../../tpl/permission.php';
    $userAllPermission = $_SESSION['permission'];
?>
<link rel="stylesheet" href="../../assets/global/plugins/gridstack/css/gridstack.min.css"/>
<link href="./css/platform/homepage/homepage.css" rel="stylesheet" type="text/css">
<link href="./css/platform/databackup-center.css" rel="stylesheet" type="text/css">
<style>
    .card_titile_module{
        font-weight: bold;
        font-size: 14px;
        color: #4D4D4D;
        line-height: 20px;
        text-align: left;
        font-style: normal;
        text-transform: none;
        margin-top: 15px;
        margin-left: 20px;
    }
    .flex_column_box_each_center{
        margin: auto;
    }
    .protect_module_num{
        font-family: Poppins, Poppins;
        font-weight: 500;
        font-size: 16px;
        color: #2D3748;
        line-height: 24px;
        font-style: normal;
        text-transform: none;
    }
    .protect_module_des{
        font-weight: 400;
        font-size: 12px;
        color: #999999;
        line-height: 20px;
        text-align: left;
        font-style: normal;
        text-transform: none;

    }


    .protected-indicator {
    display: flex;
    align-items: center;
    gap: 5px;
}

.indicator-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #31D395; /* 你可以根据需要调整颜色 */
}

.indicator-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.protected-label {
    font-size: 12px;
    color: #666;
}

.protected-value {
    font-size: 14px;
    font-weight: bold;
    color: #333;
}
.card_img_storage{
    text-align: center;
    background-image: url(../../../img/platform/homepage/chunchu-num-blue.svg);
    background-repeat: no-repeat;
    background-position: center;
    height: 40px;
    width: 40px;
    background-color: #FFFFFF;
    border-radius: 5px !important;
    margin-right: 24px;
    margin-left: 24px;
    box-shadow: 0px 2px 5px 0px rgba(0,0,0,0.05);

   
}
.card_img2_storage{
    text-align: center;
    background-image: url(../../../img/platform/homepage/chunchu-kb.svg);
    background-repeat: no-repeat;
    background-position: center;
    height: 40px;
    width: 40px;
    background-color: #FFFFFF;
    border-radius: 5px !important;
    margin-right: 24px;
    margin-left: 24px;
    box-shadow: 0px 2px 5px 0px rgba(0,0,0,0.05);

}
.grid-stack>.grid-stack-item>.grid-stack-item-content{
    box-shadow: 0px 5px 25px 0px rgba(4, 13, 45, 0.08);
}
.des_line{
    width: 92%;
    margin-left: 4%;
}
.des_percent{
    margin-left: 4%;
}
.centered-content_other{
    padding: 0 30px;
}
.backup_title_des{
    font-family: Microsoft YaHei UI, Microsoft YaHei UI;
    font-weight: bold;
    font-size: 12px;
    color: #4C4C4C;
}
.backup_title_num{
    font-family: Poppins, Poppins;
    font-weight: 600;
    font-size: 12px;
    color: #333333;
}
.backup_detail_des{
    font-family: Microsoft YaHei UI, Microsoft YaHei UI;
    font-weight: 400;
    font-size: 12px;
    color: #797F8A;
}
.backup_detail_num{
    font-family: Poppins, Poppins;
    font-weight: 500;
    font-size: 12px;
    color: #4C5563;
}
.editContainer .recoverBut{
    width: 100%;
    height: 100%;
    border: 0;
    background-color: #50C5FF;
    color: #FFF;
    font-family: Microsoft YaHei;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}
.editContainer .box_icon{
    background-color: rgba(22,177,255,1);
}
.editContainer .editBut{
    background-color: #16B1FF;
}
.editContainer.editContainerHover{
    width: 49px;
}
.editContainer.editContainerHover:hover{
    width: 158px;
}
.editContainer.editContainerHover .box_content{
    width: 108px;
}
</style>

<div class="editContainer">
    <div class="box_icon">
       <div class="icon_editPage"></div>
    </div>

    <div class="box_content">
        <button type="button" class="editBut" id="editBut"><?php echo $LANG['UI_HOMEPAGEPRO_EDIT_HOMEPAGE'] ?></button>
        <button type="button" class="recoverBut" id="recoverBut"><?php echo $LANG['UI_HOMEPAGE_DEFAULT_HOMEPAGE'] ?></button>
        <button type="button" class="saveBut display-none" id="saveBut"><?php echo $LANG['UI_HOMEPAGEPRO_SAVE_HOMEPAGE'] ?></button>
    </div>
</div>
<div class="grid-stack"></div>


<div id="divTemp" style="display: none">
    <!---------card1--------->
    <div id="card_basic_data" id_w="6" id_h="1" id_title="<?php echo $LANG['UI_HOMEPAGE_OVERVIEW1'] ?>">
        <div id_value="card_basic_data" class="container_four">
            <div class="block_each block_each_first">
                <div class="container_four_each">
                    <div class="container_four_img1"></div>
                </div>
                <div class="container_four_each">
                    <div class="container_four_num"><span class="info_value_days">--</span><strong><?php echo $LANG['WEB_UTILS_DAY'] ?></strong></div>
                    <div class="container_four_des"><?php echo $LANG['UI_HOMEPAGE_RUNNING_TIME'] ?></div>
                </div>
            </div>
            <div class="block_each block_each_second">
                <div class="container_four_each">
                    <div class="container_four_img2"></div>
                </div>
                <div class="container_four_each">
                    <div class="container_four_num"><span class="info_value_size">--</span><strong class="info_value_size_unit">TB</strong></div>
                    <div class="container_four_des"><?php echo $LANG['UI_DATACENTER_TOTAL_DATA_SIZE'] ?></div>
                </div>
            </div>
            <div class="block_each block_each_third">
                <div class="container_four_each">
                    <div class="container_four_img3"></div>
                </div>
                <div class="container_four_each">
                    <div class="container_four_num"><span class="info_value_current_count">--</span></div>
                    <div class="container_four_des"><?php echo $LANG['UI_JOB_CURRENT'] ?></div>
                </div>
            </div>
            <div class="block_each block_each_end">
                <div class="container_four_each">
                    <div class="container_four_img4"></div>
                </div>
                <div class="container_four_each">
                    <div class="container_four_num"><span class="info_value_history_count">--</span></div>
                    <div class="container_four_des"><?php echo $LANG['UI_JOB_HISTORY'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <!---------card2--------->
<!--    <div id="card_current_job_1" id_w="6" id_h="1">-->
<!--        <div id_value="card_current_job_1" style="height: 100%">-->
<!--           <div class="echarts_current_job_1" style="height: 100%"></div>-->
<!--        </div>-->
<!--    </div>-->

    <!---------card2--------->
    <div id="card_current_job_1" id_w="6" id_h="1" id_title="<?php echo $LANG['UI_JOB_CURRENT'] ?>">
        <div id_value="card_current_job_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_PLATFORM_JOB_MONITOR'] ?></h3>
                <div class="flex_row_box" style="padding: 10px">
                    <div style="width: 25%;"><div class="echarts_current_job_1" style="height: 100%"></div></div>
                    <div style="width: 75%;padding-bottom: 10px;" class="flex_row_box">
                        <div class="flex_row_box_each flex_column_box box_content_center">
                            <div>
                                <span class="current_line"></span>
                                <span class="current_value_percent info_running_percent">--</span>
                            </div>
                            <div class="current_des">
                                <?php echo $LANG['UI_HOMEPAGE_STATUS_RUNNING'] ?>
                            </div>
                            <div class="current_value_num info_running_count">
                                --
                            </div>
                        </div>
                        <div class="flex_row_box_each flex_column_box box_content_center">
                            <div>
                                <span class="current_line" style="border-color: #10AEFF;"></span>
                                <span class="current_value_percent info_wait_percent">--</span>
                            </div>
                            <div class="current_des">
                                <?php echo $LANG['WEB_PLATFORM_DES_WAITING'] ?>
                            </div>
                            <div class="current_value_num info_wait_count">
                                --
                            </div>
                        </div>
                        <div class="flex_row_box_each flex_column_box box_content_center">
                        <div>
                                <span class="current_line" style="border-color: #2CCE8B;"></span>
                                <span class="current_value_percent info_success_percent">--</span>
                            </div>
                            <div class="current_des">
                                <?php echo $LANG['WEB_PLATFORM_DES_SUCCESSED'] ?>
                            </div>
                            <div class="current_value_num info_success_count">
                                --
                            </div>
                        </div>
                        <div class="flex_row_box_each flex_column_box box_content_center">
                            <div>
                                <span class="current_line" style="border-color: #FACC15;"></span>
                                <span class="current_value_percent info_abnormal_percent">--</span>
                            </div>
                            <div class="current_des">
                                <?php echo $LANG['WEB_PLATFORM_DES_ABNORMAL'] ?>
                            </div>
                            <div class="current_value_num info_abnormal_count">
                                --
                            </div>
                        </div>
                        <div class="flex_row_box_each flex_column_box box_content_center">
                            <div>
                                <span class="current_line" style="border-color: #F56A6A;"></span>
                                <span class="current_value_percent info_fail_percent">--</span>
                            </div>
                            <div class="current_des">
                                <?php echo $LANG['WEB_PUBLIC_FAILURE_HOMEPAGE'] ?>
                            </div>
                            <div class="current_value_num info_fail_count">
                                --
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!---------card3--------->
    <div id="card_backup_storage_1" id_w="4" id_h="2" id_title="<?php echo $LANG['WEB_HOMEPAGE_BACKUP_STORAGE'] ?>">
        <div id_value="card_backup_storage_1" style="height: 100%">
            <div class="container_storage">
                <div class="upper">
                    <div class="item">
                        <h3 class="card_titile"><?php echo $LANG['WEB_HOMEPAGE_BACKUP_STORAGE'] ?></h3>
                        <div style="height: 100%" class="echarts_backup_storage_1"></div>
                    </div>
                    <div class="item_content">
                        <div class="centered-content">
                            <div class="card_each">
                                <div class="card_img"></div>
                                <div style="width: 70%;">
                                    <div class="card_each_num info_storage_num">--</div>
                                    <div class="card_each_des"><?php echo $LANG['UI_HOMEPAGE_STORAGE_NUM'] ?></div>
                                </div>
                            </div>
                            <div class="card_each">
                                <div class="card_img2"></div>
                                <div style="width: 70%;">
                                    <div class="card_each_num info_storage_size_all">--</div>
                                    <div class="card_each_des"><?php echo $LANG['UI_HOMEPAGE_STORAGE_CAPACITY'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lower">
                    <div class="item">
                        <div class="lower_item_des"><div class="g-dot"></div><span class="info_storage_size_used_percent">--</span></div>
                        <div class="lower_item_num info_storage_size_used">--</div>
                    </div>
                    <div class="item">
                        <div class="lower_item_des"><div class="g-dot" style="background-color: #E9EEF3;"></div><span class="info_storage_size_free_percent">--</span></div>
                        <div class="lower_item_num info_storage_size_free">--</div>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!---------card4--------->
    <div id="card_copy_storage_pool_1" id_w="4" id_h="2" id_title="<?php echo $LANG['UI_HOMEPAGE_COPY_STORAGE'] ?>">
        <div id_value="card_copy_storage_pool_1" style="height: 100%">
            <div class="container_storage">
                <div class="upper upper_other">
                    <div class="item">
                        <h3 class="card_titile card_titile_relative"><?php echo $LANG['WEB_HOMEPAGE_COPY_ARCHIVE_STORAGE'] ?></h3>
                    </div>

                    <div class="centered-content centered-content_other">
                        <div class="card_each">
                            <div class="card_img"></div>
                            <div style="width: 70%;">
                                <div class="card_each_num info_storage_num">--</div>
                                <div class="card_each_des"><?php echo $LANG['UI_HOMEPAGE_STORAGE_NUM'] ?></div>
                            </div>
                        </div>
                        <div class="card_each">
                            <div class="card_img2"></div>
                            <div style="width: 70%;">
                                <div class="card_each_num info_storage_size_all">--</div>
                                <div class="card_each_des"><?php echo $LANG['UI_HOMEPAGE_STORAGE_CAPACITY'] ?></div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="lower lower_other">
                    <div class="echarts_copy_storage_pool_1" style="width: 100%;height: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
    <!---------card5--------->
    <div id="card_Archival_storage_1" id_w="4" id_h="2"  id_title="<?php echo $LANG['WEB_HOMEPAGE_ARCHIVE_STORAGE'] ?>">
        <div id_value="card_Archival_storage_1" style="height: 100%">
            <div class="container_storage">
                <div class="upper upper_other">
                    <div class="item">
                        <h3 class="card_titile card_titile_relative"><?php echo $LANG['WEB_HOMEPAGE_ARCHIVE_STORAGE'] ?></h3>
                    </div>

                    <div class="centered-content centered-content_other">
                        <div class="card_each">
                            <div class="card_img3"></div>
                            <div style="width: 70%;">
                                <div class="card_each_num info_storage_num">--</div>
                                <div class="card_each_des"><?php echo $LANG['UI_HOMEPAGE_BACKUP_STORAGE_NUM'] ?></div>
                            </div>
                        </div>
                        <div class="card_each">
                            <div class="card_img4"></div>
                            <div style="width: 70%;">
                                <div class="card_each_num info_storage_size_all">--</div>
                                <div class="card_each_des"><?php echo $LANG['UI_HOMEPAGE_STORAGE_CAPACITY'] ?></div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="lower lower_other">
                    <div class="echarts_Archival_storage_1" style="width: 100%;height: 100%;"></div>
                </div>
            </div>

        </div>
    </div>
    <!---------card6--------->
    <div id="card_log_1" id_w="3" id_h="1" id_title="<?php echo $LANG['UI_HOMEPAGE_LOG_STATISTICS'] ?>">
        <div id_value="card_log_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_PLATFORM_JOB_LOG'] ?></h3>
                <div class="flex_row_box">
                    <div class="flex_row_box_each log_img_1" style="border-radius: 4px !important;">
                        <div class="log_content_des"><?php echo $LANG['UI_HOMEPAGE_TASK_LOG'] ?></div>
                        <div class="log_content_num info_value_task_log_count">--</div>
                    </div>
                    <div class="flex_row_box_each log_img_2 system_alarm_show" style="border-radius: 4px !important;">
                        <div class="log_content_des"><?php echo $LANG['UI_PLATFORM_SYSTEM_LOG'] ?></div>
                        <div class="log_content_num info_value_system_log_count">--</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card7--------->
    <div id="card_network_1" id_w="6" id_h="2" id_title="<?php echo $LANG['UI_DATACENTER_NETWORK_FLOW'] ?>">
        <div id_value="card_network_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_DATACENTER_NETWORK_FLOW'] ?></h3>
                <div style="height: 100%" class="echarts_network_1"></div>
            </div>
        </div>
    </div>
    <!---------card8--------->
    <div id="card_cpu_1" id_w="3" id_h="1" id_title="CPU">
        <div id_value="card_cpu_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative">CPU</h3>
                <div class="progress_des">
                    <div class="progress_des_content des_percent">
                        <span><strong class="info_value_percent">--</strong><?php echo $LANG['UI_DATACENTER_CPU_RATE'] ?></span>
                    </div>
                    <div class="progress_des_content des_line" style="position: relative;">
                        <div class="progress progress_color_1" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="border-radius: 0px !important;height: 12px;background-color: rgba(16,174,255,0.1);position: relative;overflow: visible;">
                            <div class="progress-bar progress_bar_color_1 info_value_percent_width" style="box-shadow: unset;border-radius: 0px !important;width: 0%;background: linear-gradient( 270deg, #10AEFF 0%, rgba(16,174,255,0.4) 100%);position: relative;">
                                <div style="z-index: 9999;width:18px;height:18px;box-shadow: 0px 0px 5px 0px #10AEFF;background:#FFFFFF;position: absolute;right: -9px;top: 50%;transform: translateY(-50%);border-radius: 18px !important;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card9--------->
    <div id="card_memory_1" id_w="3" id_h="1" id_title="<?php echo $LANG['UI_DRILLS_MEMORY'] ?>">
        <div id_value="card_memory_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_DRILLS_MEMORY'] ?></h3>
                <div class="progress_des">
                    <div class="progress_des_content des_percent">
                        <span><strong class="info_value_percent">--</strong><?php echo $LANG['UI_SYSTEM_MONITOR_RAM_PERCENTAGE'] ?></span>
                    </div>
                    <div class="progress_des_content des_line" style="position: relative;">
                        <div class="progress progress_color_1" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="border-radius: 0px !important;height: 12px;background-color: rgba(45,204,127,0.1);position: relative;overflow: visible;">
                            <div class="progress-bar progress_bar_color_1 info_value_percent_width" style="box-shadow: unset;border-radius: 0px !important;width: 0%;background: linear-gradient( 270deg, #2DCC7F 0%, rgba(45,204,127,0.4) 100%);position: relative;">
                                <div style="z-index: 9999;width:18px;height:18px;box-shadow: 0px 0px 5px 0px #31CF95;background:#FFFFFF;position: absolute;right: -9px;top: 50%;transform: translateY(-50%);border-radius: 18px !important;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!---------card10--------->
    <div id="card_alarm_1" id_w="3" id_h="1" id_title="<?php echo $LANG['UI_HOMEPAGE_ALARM_STATISTICS'] ?>">
        <div id_value="card_alarm_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_ALARM_STATISTICS'] ?></h3>
                <div class="flex_column_box alarm_box" style="height: 100%;margin: 0 20px 20px;">
                    <div class="flex_column_box_each gray_color" style="border-radius: 4px;">
                        <div class="each_alarm"><div class="alarm_img_1"></div></div>
                        <div class="each_alarm"><div><div class="alarm_des"><?php echo $LANG['UI_PLATFORM_ALARM_TASK'] ?></div><div class="alarm_value info_value_task_alarm_count">--</div></div></div>
                        <div class="each_alarm"></div>
                        <div class="each_alarm"><div><div class="alarm_des"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></div><div class="alarm_value info_value_task_alarm_level_warning" style="color: #F19F00;">--</div></div></div>
                        <div class="each_alarm"><div><div class="alarm_des"><?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?></div><div class="alarm_value info_value_task_alarm_level_error" style="color: #F56A6A;">--</div></div></div>
                    </div>
                    <div class="flex_column_box_each gray_color system_alarm_show" style="border-radius: 4px;">
                        <div class="each_alarm"><div class="alarm_img_2"></div></div>
                        <div class="each_alarm"><div><div class="alarm_des"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?></div><div class="alarm_value info_value_system_alarm_count">--</div></div></div>
                        <div class="each_alarm"></div>
                        <div class="each_alarm"><div><div class="alarm_des"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></div><div class="alarm_value info_value_system_alarm_level_warning" style="color: #F19F00;">--</div></div></div>
                        <div class="each_alarm"><div><div class="alarm_des"><?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?></div><div class="alarm_value info_value_system_alarm_level_error" style="color: #F56A6A;">--</div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card11--------->
    <div id="card_vm_1" id_w="3" id_h="2" id_title="<?php echo $LANG['WEB_PLATFORM_DES_VM'] ?>">
        <div id_value="card_vm_1" style="height: 100%">
            <div class="container_storage shadow_gary">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['WEB_PLATFORM_DES_VM'] ?><span class="value_des"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?><strong class="value_num info_size">--</strong></span></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each" style="flex: 2">
                        <div class="echarts_vm_1" style="height: 100%;"></div>
                    </div>
                    <div class="flex_column_box_each flex_column_box des_flex_50">
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_1"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_protectedNum">--</span>
                        </div>
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_2"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_unProtectedNum">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!---------card12--------->
    <div id="card_os_1" id_w="3" id_h="2" id_title="<?php echo $LANG['WEB_PLATFORM_DES_OS'] ?>">
        <div id_value="card_os_1" style="height: 100%">
            <div class="container_storage shadow_gary">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['WEB_PLATFORM_DES_OS'] ?><span class="value_des"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?><strong class="value_num info_size">--</strong></span></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each" style="flex: 2">
                        <div class="echarts_os_1" style="height: 100%;"></div>
                    </div>
                    <div class="flex_column_box_each flex_column_box des_flex_50">
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_1 img_vm_blue"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_protectedNum">--</span>
                        </div>
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_2"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_unProtectedNum">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!---------card13--------->
    <div id="card_file_1" id_w="3" id_h="2" id_title="<?php echo $LANG['UI_FILE_FILE'] ?>">
        <div id_value="card_file_1" style="height: 100%">
            <div class="container_storage shadow_gary">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_FILE_FILE'] ?><span class="value_des"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?><strong class="value_num info_size">--</strong></span></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each" style="flex: 2">
                        <div class="echarts_file_1" style="height: 100%;"></div>
                    </div>
                    <div class="flex_column_box_each flex_column_box des_flex_50">
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_1 img_vm_green"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_protectedNum">--</span>
                        </div>
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_2"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_unProtectedNum">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!---------card14--------->
    <div id="card_db_1" id_w="3" id_h="2" id_title="<?php echo $LANG['UI_HOMEPAGE_DB'] ?>">
        <div id_value="card_db_1" style="height: 100%">
            <div class="container_storage shadow_gary">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_DB'] ?><span class="value_des"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?><strong class="value_num info_size">--</strong></span></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each" style="flex: 2">
                        <div class="echarts_db_1" style="height: 100%;"></div>
                    </div>
                    <div class="flex_column_box_each flex_column_box des_flex_50">
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_1 img_vm_blue_more"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_protectedNum">--</span>
                        </div>
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_2"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_unProtectedNum">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!---------card15--------->
    <div id="card_task_alarm_1" id_w="3" id_h="2" id_title="<?php echo $LANG['UI_PLATFORM_ALARM_TASK'] ?>">
        <div id_value="card_task_alarm_1" style="height: 100%">
            <div class="container_storage line_background">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_PLATFORM_ALARM_TASK'] ?> <a class="alarm_details"><?php echo $LANG['UI_HOMEPAGE_VIEW_ALL'] ?></a></h3>

                <div class="flex_column_box content_all">
                    <div class="content_null">
                        <div class="content_null_img"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!---------card16--------->
    <div id="card_system_alarm_1" id_w="3" id_h="2" id_title="<?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?>">
        <div id_value="card_system_alarm_1" style="height: 100%">
            <div class="container_storage line_background2">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?> <a class="alarm_details"><?php echo $LANG['UI_HOMEPAGE_VIEW_ALL'] ?></a></h3>
                <div class="flex_column_box content_all">
                    <div class="content_null">
                        <div class="content_null_img"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card17--------->
    <div id="card_task_log_1" id_w="3" id_h="2" id_title="<?php echo $LANG['UI_HOMEPAGE_TASK_LOG'] ?>">
        <div id_value="card_task_log_1" style="height: 100%" id_title="<?php echo $LANG['UI_HOMEPAGE_TASK_LOG'] ?>">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_TASK_LOG'] ?> <a class="alarm_details"><?php echo $LANG['UI_HOMEPAGE_VIEW_ALL'] ?></a></h3>

                <div class="flex_column_box content_all">
                    <div class="content_null">
                        <div class="content_null_img"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card18--------->
    <div id="card_system_log_1" id_w="3" id_h="2" id_title="<?php echo $LANG['UI_HOMEPAGE_SYSTEM_LOG'] ?>">
        <div id_value="card_system_log_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_SYSTEM_LOG'] ?> <a class="alarm_details"><?php echo $LANG['UI_HOMEPAGE_VIEW_ALL'] ?></a></h3>

                <div class="flex_column_box content_all">
                    <div class="content_null">
                        <div class="content_null_img"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card19--------->
    <div id="card_bps_1" id_w="6" id_h="2" id_title="<?php echo $LANG['UI_HOMEPAGE_MONITOR_BPS'] ?>">
        <div id_value="card_bps_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_MONITOR_BPS'] ?></h3>
                <div style="height: 100%" class="echarts_bps_1"></div>
            </div>
        </div>
    </div>
    <!---------card20--------->
    <div id="card_iops_1" id_w="6" id_h="2" id_title="<?php echo $LANG['UI_HOMEPAGE_MONITOR_IOPS'] ?>">
        <div id_value="card_iops_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_MONITOR_IOPS'] ?></h3>
                <div style="height: 100%" class="echarts_iops_1"></div>
            </div>
        </div>
    </div>

    <!---------card21--------->


    <!---------card22--------->
    <div id="card_storage_7days_1" id_w="6" id_h="2" id_title="<?php echo $LANG['UI_HOMEPAGE_RECENT_SEVEN_DAY_STORAGE'] ?>">
        <div id_value="card_storage_7days_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_RECENT_SEVEN_DAY_STORAGE'] ?></h3>
                <div style="height: 100%" class="echarts_storage_7days_1"></div>
            </div>
        </div>
    </div>

    <!---------card23--------->


    <!---------card24--------->
    <div id="card_db_2" id_w="3" id_h="2" id_title="<?php echo $LANG['UI_HOMEPAGE_DB_CDP'] ?>">
        <div id_value="card_db_2" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_DB_CDP'] ?></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each flex_row_box">
                        <div class="flex_row_box_each">
                            <div class="echarts_db_2" style="height: 100%"></div>
                        </div>
                        <div class="flex_row_box_each flex_column_box db_position_center">
                            <div>
                                <div class="db_line_display"><span class="circle_12"></span><span class="db_status_des"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span><span class="db_status_value info_productHost_online">--</span></div>
                            </div>
                            <div>
                                <div class="db_line_display"><span class="circle_12_gray"></span><span class="db_status_des"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span><span class="db_status_value info_productHost_offline">--</span></div>
                            </div>
                        </div>
                    </div>
                    <hr class="db_line">
                    <div class="flex_column_box_each flex_row_box">
                        <div class="flex_row_box_each">
                            <div class="echarts_db_3" style="height: 100%"></div>
                        </div>
                        <div class="flex_row_box_each flex_column_box db_position_center">
                            <div>
                                <div class="db_line_display"><span class="circle_12" style="background-color: #22BC7D"></span><span class="db_status_des"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span><span class="db_status_value info_standbyHost_online">--</span></div>
                            </div>
                            <div>
                                <div class="db_line_display"><span class="circle_12_gray"></span><span class="db_status_des"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span><span class="db_status_value info_standbyHost_offline">--</span></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!---------card25--------->
    <div id="card_365_2" id_w="3" id_h="2" id_title="Exchange">
        <div id_value="card_365_2" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative">Exchange</h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each flex_row_box">
                        <div class="flex_row_box_each">
                            <div class="echarts_365_2" style="height: 100%"></div>
                        </div>
                        <div class="flex_row_box_each flex_column_box db_position_center">
                            <div>
                                <div class="db_line_display"><span class="circle_12"></span><span class="db_status_des"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span><span class="db_status_value info_online_online">--</span></div>
                            </div>
                            <div>
                                <div class="db_line_display"><span class="circle_12_gray"></span><span class="db_status_des"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span><span class="db_status_value info_online_offline">--</span></div>
                            </div>
                        </div>
                    </div>
                    <hr class="db_line">
                    <div class="flex_column_box_each flex_row_box">
                        <div class="flex_row_box_each">
                            <div class="echarts_365_3" style="height: 100%"></div>
                        </div>
                        <div class="flex_row_box_each flex_column_box db_position_center">
                            <div>
                                <div class="db_line_display"><span class="circle_12" style="background-color: #22BC7D"></span><span class="db_status_des"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span><span class="db_status_value info_server_online">--</span></div>
                            </div>
                            <div>
                                <div class="db_line_display"><span class="circle_12_gray"></span><span class="db_status_des"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span><span class="db_status_value info_server_offline">--</span></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!---------card26--------->
    <div id="card_basic_2" id_w="6" id_h="1" id_title="<?php echo $LANG['UI_HOMEPAGE_OVERVIEW2'] ?>">
        <div id_value="card_basic_2" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGEPRO_OVERVIEW'] ?></h3>
                <div class="flex_row_box" style="align-items: center;padding: 0px 0px 20px 50px;">
                    <div class="flex_row_box_each flex_column_box">
                        <div class="flex_column_box_each">
                            <div class="basic_img_9"></div>
                        </div>
                        <div class="flex_column_box_each basic_value_1"><span class="info_value_days">--</span><span class="basic_unit_1"><?php echo $LANG['WEB_UTILS_DAY'] ?></span></div>
                        <div class="flex_column_box_each basic_des_1"><?php echo $LANG['UI_HOMEPAGE_RUNNING_TIME'] ?></div>
                    </div>
                    <div class="flex_row_box_each flex_column_box">
                        <div class="flex_column_box_each">
                            <div class="basic_img_12"></div>
                        </div>
                        <div class="flex_column_box_each basic_value_1"><span class="info_value_size">--</span><span class="basic_unit_1 info_value_size_unit">GB</span></div>
                        <div class="flex_column_box_each basic_des_1"><?php echo $LANG['UI_DATACENTER_TOTAL_DATA_SIZE'] ?></div>
                    </div>
                    <div class="flex_row_box_each flex_column_box">
                        <div class="flex_column_box_each">
                            <div class="basic_img_14"></div>
                        </div>
                        <div class="flex_column_box_each basic_value_1 info_value_current_count">--</div>
                        <div class="flex_column_box_each basic_des_1"><?php echo $LANG['UI_HOMEPAGE_CURRENT_JOB_NUM'] ?></div>
                    </div>
                    <div class="flex_row_box_each flex_column_box">
                        <div class="flex_column_box_each">
                            <div class="basic_img_15"></div>
                        </div>
                        <div class="flex_column_box_each basic_value_1 info_value_history_count">--</div>
                        <div class="flex_column_box_each basic_des_1"><?php echo $LANG['UI_HOMEPAGE_HISTORY_JOB_NUM'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card27--------->
    <div id="card_basic_3" id_w="6" id_h="1" id_title="<?php echo $LANG['UI_HOMEPAGE_OVERVIEW3'] ?>">
        <div id_value="card_basic_3" class="container_four">
            <div class="block_each block_each_first basic_color_1">
                <div class="container_four_each">
                    <div class="container_four_img5"></div>
                </div>
                <div class="container_four_each">
                    <div class="container_four_num black_color_1"><span class="info_value_days">--</span></div>
                    <div class="container_four_des black_color_1"><?php echo $LANG['UI_HOMEPAGE_RUNNING_TIME'] ?></div>
                </div>
            </div>
            <div class="block_each block_each_second basic_color_2">
                <div class="container_four_each">
                    <div class="container_four_img6"></div>
                </div>
                <div class="container_four_each">
                    <div class="container_four_num black_color_1"><span class="info_value_size">--</span></div>
                    <div class="container_four_des black_color_1"><?php echo $LANG['UI_DATACENTER_TOTAL_DATA_SIZE'] ?></div>
                </div>
            </div>
            <div class="block_each block_each_third basic_color_3">
                <div class="container_four_each">
                    <div class="container_four_img7"></div>
                </div>
                <div class="container_four_each">
                    <div class="container_four_num black_color_1"><span class="info_value_current_count">--</span></div>
                    <div class="container_four_des black_color_1"><?php echo $LANG['UI_PLATFORM_CURRENT_JOB'] ?></div>
                </div>
            </div>
            <div class="block_each block_each_end basic_color_4">
                <div class="container_four_each">
                    <div class="container_four_img8"></div>
                </div>
                <div class="container_four_each">
                    <div class="container_four_num black_color_1"><span class="info_value_history_count">--</span></div>
                    <div class="container_four_des black_color_1"><?php echo $LANG['UI_PLATFORM_HOSTORY_JOB'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!---------card28--------->
    <div id="card_CDM_1" id_w="5" id_h="1" id_title="CDM">
        <div id_value="card_CDM_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative">CDM</h3>
                <div class="flex_row_box">
                    <div class="flex_row_box_each flex_column_box">
                        <div class="flex_column_box_each"><div class="cdm_img_1"></div></div>
                        <div class="flex_column_box_each cdm_value_1 info_laboratoryNum">--</div>
                        <div class="flex_column_box_each cdm_des_1"><?php echo $LANG['UI_HOMEPAGE_LAB'] ?></div>
                    </div>
                    <div class="flex_row_box_each flex_column_box">
                        <div class="flex_column_box_each"><div class="cdm_img_2"></div></div>
                        <div class="flex_column_box_each cdm_value_1 info_verifyNum">--</div>
                        <div class="flex_column_box_each cdm_des_1"><?php echo $LANG['UI_HOMEPAGE_LAB_NUM'] ?></div>
                    </div>
                    <div class="flex_row_box_each flex_column_box">
                        <div class="flex_column_box_each"><div class="cdm_img_3"></div></div>
                        <div class="flex_column_box_each cdm_value_1 info_verifyingNum">--</div>
                        <div class="flex_column_box_each cdm_des_1"><?php echo $LANG['UI_HOMEPAGE_VERIFYING'] ?></div>
                    </div>
                    <div class="flex_row_box_each flex_column_box">
                        <div class="flex_column_box_each"><div class="cdm_img_4"></div></div>
                        <div class="flex_column_box_each cdm_value_1 info_verifyTimes">--</div>
                        <div class="flex_column_box_each cdm_des_1"><?php echo $LANG['UI_HOMEPAGE_VALID_COUNT'] ?></div>
                    </div>
                    <div class="flex_row_box_each flex_column_box">
                        <div class="flex_column_box_each"><div class="cdm_img_5"></div></div>
                        <div class="flex_column_box_each cdm_value_1 info_falieTimes">--</div>
                        <div class="flex_column_box_each cdm_des_1"><?php echo $LANG['WEB_PUBLIC_FAILURE_HOMEPAGE'] ?></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!---------card29--------->
    <div id="card_task_7days_1" id_w="6" id_h="2" id_title="<?php echo $LANG['UI_HOMEPAGE_RECENT_SEVEN_DAY_TASK'] ?>">
        <div id_value="card_task_7days_1" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_RECENT_SEVEN_DAY_TASK'] ?></h3>
                <div style="height: 100%" class="echarts_task_7days_1"></div>
            </div>
        </div>
    </div>

    <!---------card6--------->
    <div id="card_alarm_2" id_w="3" id_h="1" id_title="<?php echo $LANG['UI_HOMEPAGE_ALARM_STATISTICS'] ?>">
        <div id_value="card_alarm_2" style="height: 100%">
            <div class="container_storage">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_HOMEPAGE_ALARM_STATISTICS'] ?></h3>
                <div class="flex_row_box">
                    <div class="flex_row_box_each flex_row_box alarm_box_gray">
                        <div class="flex_row_box_each flex_column_box">
                            <div class="flex_column_box_each alarm_value info_value_task_alarm_count">--</div>
                            <div class="flex_column_box_each alarm_des"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?></div>
                        </div>
                        <div class="flex_row_box_each flex_column_box">
                            <div class="flex_column_box_each alarm_color_yellow"><span class="circle_12" style="background-color: #F08F1B;"></span><span class="alarm_des_1"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></span><span class="alarm_value_1 info_value_task_alarm_level_warning">--</span></div>
                            <div class="flex_column_box_each alarm_color_red"><span class="circle_12" style="background-color: #E53E3E;"></span><span class="alarm_des_1"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></span><span class="alarm_value_1 info_value_task_alarm_level_error">--</span></div>
                        </div>
                    </div>
                    <div class="flex_row_box_each flex_row_box alarm_box_gray">
                        <div class="flex_row_box_each flex_column_box">
                            <div class="flex_column_box_each alarm_value info_value_system_alarm_count">--</div>
                            <div class="flex_column_box_each alarm_des"><?php echo $LANG['UI_PLATFORM_ALARM_TASK'] ?></div>
                        </div>
                        <div class="flex_row_box_each flex_column_box">
                            <div class="flex_column_box_each alarm_color_yellow"><span class="circle_12" style="background-color: #F08F1B;"></span><span class="alarm_des_1"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></span><span class="alarm_value_1 info_value_system_alarm_level_warning">--</span></div>
                            <div class="flex_column_box_each alarm_color_red"><span class="circle_12" style="background-color: #E53E3E;"></span><span class="alarm_des_1"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></span><span class="alarm_value_1 info_value_system_alarm_level_error">--</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!---------card30--------->
    <div id="card_nas_1" id_w="3" id_h="2" id_title="NAS">
        <div id_value="card_nas_1" style="height: 100%">
            <div class="container_storage shadow_gary">
                <h3 class="card_titile card_titile_relative">NAS<span class="value_des"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?><strong class="value_num info_size">--</strong></span></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each" style="flex: 2">
                        <div class="echarts_nas_1" style="height: 100%;"></div>
                    </div>
                    <div class="flex_column_box_each flex_column_box des_flex_50">
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_1"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_protectedNum">--</span>
                        </div>
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_2"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_unProtectedNum">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card31--------->
    <div id="card_volcdp_1" id_w="3" id_h="2" id_title="VOLCDP">
        <div id_value="card_volcdp_1" style="height: 100%">
            <div class="container_storage shadow_gary">
                <h3 class="card_titile card_titile_relative">Volcdp<span class="value_des"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?><strong class="value_num info_size">--</strong></span></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each" style="flex: 2">
                        <div class="echarts_volcdp_1" style="height: 100%;"></div>
                    </div>
                    <div class="flex_column_box_each flex_column_box des_flex_50">
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_1"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_protectedNum">--</span>
                        </div>
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_2"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_unProtectedNum">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card32--------->
    <div id="card_aws_1" id_w="3" id_h="2" id_title="AWS">
        <div id_value="card_aws_1" style="height: 100%">
            <div class="container_storage shadow_gary">
                <h3 class="card_titile card_titile_relative">AWS<span class="value_des"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?><strong class="value_num info_size">--</strong></span></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each" style="flex: 2">
                        <div class="echarts_aws_1" style="height: 100%;"></div>
                    </div>
                    <div class="flex_column_box_each flex_column_box des_flex_50">
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_1"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_protectedNum">--</span>
                        </div>
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_2"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_unProtectedNum">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card33--------->
    <div id="card_basic_username" id_w="6" id_h="1" id_title="">
        <div id_value="card_basic_username" class="container_four card_username">
            <div class="" style="color: #76767B;">
                <div style="margin-right: 16px;">
                    <span style="font-weight: 400;font-size: 16px;color: #76767B;margin-right:16px;"><?php echo $LANG['UI_HOMEPAGE_NOW_USER'] ?></span>
                    <span id="current_user_name" style="color: #3D3D46;font-size: 16px;font-size: 16px;"></span>
                </div>
            </div>
            <div>
                <select name="" id="user_select" class="user_select  form-control" style="">
                    <option value=""><?php echo $LANG['UI_PUBLIC_SELECT'] ?></option>
                </select>
            </div>
        </div>
    </div>
    <!---------card34--------->
    <div id="card_hadoop_1" id_w="3" id_h="2" id_title="Hadoop">
        <div id_value="card_hadoop_1" style="height: 100%">
            <div class="container_storage shadow_gary">
                <h3 class="card_titile card_titile_relative">Hadoop<span class="value_des"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?><strong class="value_num info_size">--</strong></span></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each" style="flex: 2">
                        <div class="echarts_hadoop_1" style="height: 100%;"></div>
                    </div>
                    <div class="flex_column_box_each flex_column_box des_flex_50">
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_1"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_protectedNum">--</span>
                        </div>
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_2"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_unProtectedNum">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card35--------->
    <div id="card_obs_1" id_w="3" id_h="2" id_title="OBS">
        <div id_value="card_obs_1" style="height: 100%">
            <div class="container_storage shadow_gary">
                <h3 class="card_titile card_titile_relative"><?php echo $LANG['WEB_PLATFORM_DES_OBS'] ?><span class="value_des"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?><strong class="value_num info_size">--</strong></span></h3>
                <div class="flex_column_box" style="height: 100%;">
                    <div class="flex_column_box_each" style="flex: 2">
                        <div class="echarts_obs_1" style="height: 100%;"></div>
                    </div>
                    <div class="flex_column_box_each flex_column_box des_flex_50">
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_1"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_protectedNum">--</span>
                        </div>
                        <div class="flex_column_box_each des_box_50">
                            <div class="img_vm_2"></div>
                            <span class="vm_des_1"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></span>
                            <span class="vm_value_1 info_unProtectedNum">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card36--------->
    <div id="card_storage_1" id_w="4" id_h="2" id_title="<?php echo $LANG['UI_PALTFORM_STORAGE'] ?>">
        <div id_value="card_storage_1" style="height: 100%">
            <div class="container_storage">
                <div class="upper upper_other">
                    <div class="item">
                        <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_PALTFORM_STORAGE'] ?></h3>
                    </div>

                    <div class="centered-content centered-content_other">
                        <div class="card_each" style="border-radius: 4px !important;">
                            <div class="card_img_storage"></div>
                            <div style="width: 70%;">
                                <div class="card_each_num info_storage_num">--</div>
                                <div class="card_each_des" style="color:#76767B"><?php echo $LANG['UI_HOMEPAGE_STORAGE_NUM'] ?></div>
                            </div>
                        </div>
                        <div class="card_each" style="border-radius: 4px !important;">
                            <div class="card_img2_storage"></div>
                            <div style="width: 70%;">
                                <div class="card_each_num info_storage_size_all">--</div>
                                <div class="card_each_des" style="color:#76767B"><?php echo $LANG['UI_HOMEPAGE_REMAINING_STORAGE'] ?></div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="lower lower_other">
                    <div class="echarts_storagel_1" style="width: 100%;height: 100%;margin:0 20px;"></div>
                </div>
            </div>
        </div>
    </div>
    <!---------card37--------->
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
                                    <span class="square square-green backup_title_des"></span>
                                    '. $LANG['UI_HOMEPAGE_CLOUD'] .'
                                </span>
                                <span class="cloud_des backup_title_num"></span>
                            </div>';
                            if (in_array("vmprotect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des"> '. $LANG['UI_PLATFORM_VM_VIRTUAL'] .' </span>
                                            <span class="vmData_des backup_detail_num"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("prcloud_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des">'. $LANG['WEB_PLATFORM_DES_PRIVATE_CLOUD'] .'</span>
                                            <span class="privateData_des backup_detail_num"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("awsprotect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des">'. $LANG['WEB_PLATFORM_DES_PUBLIC_CLOUD'] .'</span>
                                            <span class="publicData_des backup_detail_num"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("k8s_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des">'. $LANG['WEB_KUBE_RECOVERY_CONTAINER'] .'</span>
                                            <span class="k8sData_des backup_detail_num"></span>
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
                                    <span class="square square-blue backup_title_des"></span>
                                    '. $LANG['WEB_PLATFORM_DES_FS'] .'
                                </span>
                                <span class="file_des backup_title_num"></span>
                            </div>';
                            if (in_array("filebackup", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des"> '. $LANG['WEB_PLATFORM_DES_FS'] .' </span>
                                            <span class="fileData_des backup_detail_num"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("nas_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des">NAS</span>
                                            <span class="nasData_des backup_detail_num"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("obs_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des">'. $LANG['WEB_PLATFORM_DES_OBS'] .'</span>
                                            <span class="obsData_des backup_detail_num"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("hadoop_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des">Hadoop</span>
                                            <span class="hadoopData_des backup_detail_num"></span>
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
                                    <span class="square square-file backup_title_des"></span>
                                    '. $LANG['UI_COMPLETE_MACHINE'] .'
                                </span>
                                <span class="machine_des backup_title_num"></span>
                            </div>';
                            if (in_array("complete_machine", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des"> '. $LANG['UI_COMPLETE_MACHINE'] .' </span>
                                            <span class="machineData_des backup_detail_num"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("osbackup", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des">'. $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'] .'</span>
                                            <span class="volData_des backup_detail_num"></span>
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
                                    <span class="square square-db backup_title_des"></span>
                                    '. $LANG['UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_APPLY'] .'
                                </span>
                                <span class="application_des backup_title_num"></span>
                            </div>';
                            if (in_array("db_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des"> '. $LANG['UI_PLATFORM_DB'] .' </span>
                                            <span class="dbData_des backup_detail_num"></span>
                                        </div>
                                    ';
                            }
                            if (in_array("office365_protect", $userAllPermission)) {
                                echo '
                                        <div class="sub-title-content">
                                            <span> ● </span>
                                            <span class="backup_detail_des">M365</span>
                                            <span class="exchangeData_des backup_detail_num"></span>
                                        </div>
                                    ';
                            }
                echo    '</div>';
            }
            ?>
        </div>
    </div>
    <!-- 备份的tooltips -->
    <div id="card_all_module_backup_data_1" id_w="4" id_h="2" id_title="all_module_data">
        <div id_value="card_all_module_backup_data_1" style="height: 100%">
            <div class="container_storage" style="padding-bottom: 20px;">
                <div class="item">
                    <h3 class="card_titile card_titile_relative" style="margin-bottom:0;"><?php echo $LANG['UI_PLATFORM_DATA_BACKUP'] ?></h3>
                </div>
                <div class="flex_row_box" style="padding-bottom: 0px;">
                    <div class="" style="width: 60%;">
                        <div class="flex_row_box" style="padding-bottom: 0;border-right: 1px dashed #E1E1E1;">
                            <div class="" style="width: 30%;">
                                <div class="echarts_protect_circle" style="width: 100%;height: 100%;"></div>
                            </div>
                            <div class="" style="width: 70%;">
                                <div class="flex_row_box" style="padding-bottom: 10px;padding-left: 10;">
                                    <div class="cloud_file_show" style="width: 68%;">
                                        <div class="flex_column_box" style="height: 100%;gap: 12px;">
                                            <div class="flex_column_box flex_column_box_each gray_color cloud_show" style="background: linear-gradient( 270deg, rgba(45,204,127,0) 0%, rgba(45,204,127,0.1) 100%);padding: 0px 16px 16px 16px;">
                                                <div class="item" style="width: 100%;">
                                                    
                                                    <h3 class="card_titile_module card_titile_relative" style="margin-left: 0px;"><span style="width:10px;height:10px;background: #2DCC7F;display:inline-block;margin-right:8px"></span><?php echo $LANG['UI_HOMEPAGE_CLOUD'] ?></h3>
                                                </div>
                                                <div class="flex_row_box" style="width: 100%;padding: 0;align-items: center;">
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_vm_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                            <?php echo $LANG['UI_PLATFORM_VM_VIRTUAL'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_cloud_si_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                            <?php echo $LANG['UI_PLATFORM_PRIVATE_CLOUD'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_cloud_go_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                                <?php echo $LANG['UI_PLATFORM_PUBLIC_CLOUD'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_k8s_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                                <?php echo $LANG['WEB_KUBE_BACKUP_CONTAINER'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex_column_box flex_column_box_each gray_color file_show" style="background: linear-gradient( 90deg, rgba(29,212,224,0.1) 0%, rgba(29,212,224,0) 100%);padding: 0px 16px 16px 16px;">
                                                <div class="item" style="width: 100%;">
                                                    <h3 class="card_titile_module card_titile_relative" style="margin-left: 0px;"><span style="width:10px;height:10px;background: #1DD4E0;display:inline-block;margin-right:8px"></span><?php echo $LANG['WEB_PLATFORM_DES_FS'] ?></h3>
                                                </div>
                                                <div class="flex_row_box" style="width: 100%;padding: 0;align-items: center;">
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_fs_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                            <?php echo $LANG['WEB_PLATFORM_DES_FS'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_nas_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                                NAS
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_obs_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des nowrap_en">
                                                                <?php echo $LANG['WEB_PLATFORM_DES_OBS'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_hadoop_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                                Hadoop
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="machine_application_show" style="width: 32%;">
                                        <div class="flex_column_box" style="height: 100%;gap: 12px;">
                                            <div class="flex_column_box flex_column_box_each gray_color machine_show" style="background: linear-gradient( 270deg, rgba(48,203,203,0) 0%, rgba(48,203,203,0.1) 100%);padding: 0px 16px 16px 16px;">
                                                <div class="item" style="width: 100%;">
                                                    <h3 class="card_titile_module card_titile_relative" style="margin-left: 0px;"><span style="width:10px;height:10px;background: #30CBCB ;display:inline-block;margin-right:8px"></span><?php echo $LANG['UI_PLATFORM_MACHINE_COMPLETE_BACKUP'] ?></h3>
                                                </div>
                                                <div class="flex_row_box" style="width: 100%;padding: 0;align-items: center;">
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_machine_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                            <?php echo $LANG['UI_PLATFORM_MACHINE_COMPLETE_BACKUP'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_os_num" style="font-family: Poppins, Poppins;">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                            <?php echo $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex_column_box flex_column_box_each gray_color application_show" style="background: linear-gradient( 90deg, rgba(76,148,255,0.1) 0%, rgba(76,148,255,0) 100%);padding: 0px 16px 16px 16px;">
                                                <div class="item" style="width: 100%;">
                                                    <h3 class="card_titile_module card_titile_relative" style="margin-left: 0px;"><span style="width:10px;height:10px;background: #4C94FF;display:inline-block;margin-right:8px"></span><?php echo $LANG['UI_REPORT_CDP_YINGYONG'] ?></h3>
                                                </div>
                                                <div class="flex_row_box" style="width: 100%;padding: 0;align-items: center;">
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_db_num">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                            <?php echo $LANG['UI_HOMEPAGE_DB'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex_row_box_each">
                                                        <div class="flex_column_box">
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_m365_num">
                                                                --
                                                            </div>
                                                            <div class="flex_column_box_each flex_column_box_each_center protect_module_des">
                                                                M365
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="" style="width: 40%;height: 96%;">
                        <div class="echarts_protect_line" style="width: 100%;height: 100%;"></div>
                            
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card38----------->
    <div id="card_vol_cdp_protect_1" id_w="4" id_h="2" id_title="<?php echo $LANG['UI_PLATFORM_CDP_PROTECT'] ?>">
        <div id_value="card_vol_cdp_protect_1" style="height: 100%">
            <div class="container_storage">
                <div class="item">
                    <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_PLATFORM_CDP_PROTECT'] ?></h3>
                </div>
                <div class="flex_row_box">
                    <div class="" style="width:40%;">
                        <div class="flex_column_box cdp_show" style="height: 100%;">
                            <div class="flex_column_box_each cdp_show_1">
                                <div class="flex_row_box" style="padding: 10px;">
                                    <div class="flex_row_box_each">
                                        <div class="echarts_vol_cdp_machine_1" style="width: 100%;height: 100%;"></div>
                                    </div>
                                    <div class="flex_row_box_each">
                                        <div class="flex_column_box_each" style="height: 100%;padding: 13px 0px;">
                                            <div class="flex_column_box" style="height: 50%;justify-content: end;margin-bottom: 10px;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 12px; height: 12px; border-radius: 50% !important; background-color: #31D395;"></div>
                                                    <div style="font-size: 14px; color: #7C8999;"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></div>
                                                    <div style="font-size: 20px; font-weight: bold; color: #2D3748;font-family: Poppins, Poppins;" class="protect_module_vol_cdp_machine_num_protect">--</div>
                                                </div>
                                            </div>
                                            <div class="flex_column_box" style="height: 50%;justify-content: start;margin-top: 10px;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 12px; height: 12px; border-radius: 50% !important; background-color:#F0F2F4;"></div>
                                                    <div style="font-size: 14px; color: #7C8999;"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></div>
                                                    <div style="font-size: 20px; font-weight: bold; color: #2D3748;font-family: Poppins, Poppins;" class="protect_module_vol_cdp_machine_num_noProetect">--</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex_column_box_each cdp_show_2">
                                <div class="flex_row_box" style="padding: 10px;">
                                    <div class="flex_row_box_each">
                                        <div class="echarts_vol_cdp_volume_1" style="width: 100%;height: 100%;"></div>
                                    </div>
                                    <div class="flex_row_box_each">
                                        <div class="flex_column_box_each" style="height: 100%;padding: 13px 0px;">
                                            <div class="flex_column_box" style="height: 50%;justify-content: end;margin-bottom: 10px;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 12px; height: 12px; border-radius: 50% !important; background-color: #1DD4E0;"></div>
                                                    <div style="font-size: 14px; color: #7C8999;"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></div>
                                                    <div style="font-size: 20px; font-weight: bold; color: #2D3748;font-family: Poppins, Poppins;" class="protect_module_vol_cdp_os_num_protect">--</div>
                                                </div>
                                            </div>
                                            <div class="flex_column_box" style="height: 50%;justify-content: start;margin-top: 10px;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 12px; height: 12px; border-radius: 50% !important; background-color:#F0F2F4;"></div>
                                                    <div style="font-size: 14px; color: #7C8999;"><?php echo $LANG['UI_HOMEPAGE_NOT_PROTECTED'] ?></div>
                                                    <div style="font-size: 20px; font-weight: bold; color: #2D3748;font-family: Poppins, Poppins;" class="protect_module_vol_cdp_os_num_noProetect">--</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                       
                    </div>
                    <div class="" style="width:60%;height:95%;">
                    <div class="echarts_vol_cdp_1" style="width: 100%;height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---------card39----------->
    <div id="card_copy_1" id_w="4" id_h="2" id_title="<?php echo $LANG['UI_SYSTEM_LICENSE_COPY'] ?>">
        <div id_value="card_copy_1" style="height: 100%">
            <div class="container_storage">
                <div class="item">
                    <h3 class="card_titile card_titile_relative"><?php echo $LANG['UI_SYSTEM_LICENSE_COPY'] ?></h3>
                </div>
                <div class="flex_row_box">
                    <div class="" style="width:40%;">
                        <div class="flex_column_box" style="height: 100%;">
                            <div class="flex_column_box_each">
                                <div class="flex_row_box" style="padding: 10px;">
                                    <div class="flex_row_box_each">
                                        <div class="echarts_copy_num_1" style="width: 100%;height: 100%;"></div>
                                    </div>
                                    <div class="flex_row_box_each">
                                        <div class="flex_column_box_each" style="    height: 100%;padding: 45px 0px;display: flex;flex-direction: column;">
                                                <div class="copy_show_1" style="display: flex; align-items: center; gap: 12px;flex: 1;">
                                                    <div style="width: 12px; height: 12px; background-color: #2DCC7F;"></div>
                                                    <div style="font-weight: 400;font-size: 12px; color: #4D4D4D;width: 42px;"><?php echo $LANG['UI_COMPLETE_MACHINE'] ?></div>
                                                    <div style="font-size: 14px; font-weight: 500; color: #333333;margin-right: 26px;font-family: Poppins, Poppins;margin-right: auto;padding-left: 26px;" class="protect_module_copy_machine_num">--</div>
                                                </div>
                                                <div class="copy_show_2" style="display: flex; align-items: center; gap: 12px;flex: 1;">
                                                    <div style="width: 12px; height: 12px; background-color: #1DD4E0;"></div>
                                                    <div style="font-weight: 400;font-size: 12px; color: #4D4D4D;width: 42px;"><?php echo $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'] ?></div>
                                                    <div style="font-size: 14px; font-weight: 500; color: #333333;margin-right: 26px;margin-right: auto;padding-left: 26px;font-family: Poppins, Poppins;" class="protect_module_copy_os_num">--</div>
                                                </div>
                                                <div class="copy_show_3" style="display: flex; align-items: center; gap: 12px;flex: 1;">
                                                    <div style="width: 12px; height: 12px;  background-color: #30CBCB;"></div>
                                                    <div style="font-weight: 400;font-size: 12px; color: #4D4D4D;width: 42px;"><?php echo $LANG['UI_PLATFORM_FILES'] ?></div>
                                                    <div style="font-size: 14px; font-weight: 500; color: #333333;margin-right: 26px;margin-right: auto;padding-left: 26px;font-family: Poppins, Poppins;" class="protect_module_copy_fs_num">--</div>
                                                </div>
                                                <div class="copy_show_4" style="display: flex; align-items: center; gap: 12px;flex: 1;">
                                                    <div style="width: 12px; height: 12px;  background-color: #4C94FF;"></div>
                                                    <div style="font-weight: 400;font-size: 12px; color: #4D4D4D;width: 42px;"><?php echo $LANG['UI_PLATFORM_DATABASE'] ?></div>
                                                    <div style="font-size: 14px; font-weight: 500; color: #333333;margin-right: 26px;margin-right: auto;padding-left: 26px;font-family: Poppins, Poppins;" class="protect_module_copy_db_num">--</div>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                       
                    </div>
                    <div class="" style="width:60%;">
                        <div class="echarts_copy_capacity_1" style="width: 100%;height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="editTool" style="height: 500px;display: none;">
    <div id="gridTableList"></div>
    <div>
        <input id="deleteGridValue" placeholder="<?php echo $LANG['UI_HOMEPAGEPRO_INPUT_DELETE_ID_VALUE'] ?>">
        <button id="deleteGridData"><?php echo $LANG['UI_HOMEPAGEPRO_DELETE_LAYOUT_INFO'] ?></button>
    </div>
    <div style="margin-top: 20px">
        <input id="addGridValue" placeholder="<?php echo $LANG['UI_HOMEPAGEPRO_INPUT_ADD_ID_VALUE'] ?>">
        <button id="addGridData"><?php echo $LANG['UI_HOMEPAGEPRO_ADD_LAYOUT_INFO'] ?></button>
    </div>
    <div style="margin-top: 20px">
        <button id="getGridData"><?php echo $LANG['UI_HOMEPAGEPRO_GET_LAYOUT_INFO'] ?></button>
        <textarea id="getGridDataValue"></textarea>
    </div>

</div>





<!--<script type="text/javascript" src="./assets/global/plugins/jquery-ui/jquery-ui.min.js"></script>-->
<!--<script type="text/javascript" src="./scripts/platform/databackup_center.js"></script>-->
<script src="./assets/global/plugins/echarts/V5.4.3/echarts.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/gridstack/js/gridstack-all.js"></script>
<script type="text/javascript" src="./assets/global/plugins/moment.min.js"></script>
<script type="text/javascript" src="./scripts/platform/databackup_center.js"></script>
