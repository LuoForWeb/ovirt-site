<?php
$sysAdminPermission = $_SESSION['isThreePowers'] && $_SESSION['userLevel'] == $CONF['THREE_POWERS_USER']['sysadmin'];
?>
<link rel="stylesheet" href="../../assets/global/plugins/gridstack/css/gridstack.min.css"/>
<link href="./css/platform/homepage/homepage.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />


<div class="editContainer display-none">
    <div class="box_icon">
        <div class="icon_editPage"></div>
    </div>

    <div class="box_content">
        <button type="button" class="editBut" id="editBut"><?php echo $LANG['UI_HOMEPAGEPRO_EDIT_HOMEPAGE'] ?></button>
        <button type="button" class="saveBut display-none" id="saveBut"><?php echo $LANG['UI_HOMEPAGEPRO_SAVE_HOMEPAGE'] ?></button>
    </div>
</div>
<div class="grid-stack"></div>


<div id="divTemp" style="display: none">
    <!---------card1--------->
    <div id="card_basic_task_data_1" id_w="12" id_h="101" id_title="<?php echo $LANG['UI_HOMEPAGEPRO_OVERVIEW_TASK1'] ?>">
        <div id_value="card_basic_task_data_1" class="width_height_100">
            <div class="white_box">
                <div class="img_hello">
                    <div class="close_hello"></div>
                    <div class="hello_content">
                        <div class="text_welcome">welcome!</div>
                        <div class="text_welcome_ext"><?php echo $LANG['UI_HOMEPAGEPRO_WELCOME'] ?></div>
                    </div>
                </div>
                <div class="basic_task">
                    <div class="basic_task_each_start">
                        <div class="title_blue"><span><?php echo $LANG['UI_HOMEPAGEPRO_OVERVIEW'] ?></span></div>
                        <div class="basic_box_all">
                            <div class="basic_box_each_blue">
                                <div class="column_blue_img">
                                    <div class="blue_basic_1"></div>
                                </div>

                                <div class="column_blue_content">
                                    <div><?php echo $LANG['UI_HOMEPAGE_RUNNING_TIME'] ?></div>
                                    <div>
                                        <span class="basic_run_time value_run_time">--</span>
                                        <span class="basic_run_time_unit"><?php echo $LANG['WEB_UTILS_DAY'] ?></span>
                                    </div>
                                </div>

                            </div>
                            <div class="basic_box_each_blue">
                                <div class="column_blue_img">
                                    <div class="blue_basic_2"></div>
                                </div>

                                <div class="column_blue_content">
                                    <div><?php echo $LANG['UI_DATACENTER_TOTAL_DATA_SIZE'] ?></div>
                                    <div>
                                        <span class="basic_run_time value_protect_size">--</span>
                                        <span class="basic_run_time_unit value_protect_size_unit">--</span>
                                    </div>
                                </div>

                            </div>
                            <div class="basic_box_each_blue">
                                <div class="column_blue_img">
                                    <div class="blue_basic_3"></div>
                                </div>

                                <div class="column_blue_content">
                                    <div><?php echo $LANG['UI_PLATFORM_JOB_LOG'] ?></div>
                                    <div>
                                        <span class="basic_run_time value_task_op">--</span>
                                    </div>
                                </div>

                            </div>
                            <div class="basic_box_each_blue">
                                <div class="column_blue_img">
                                    <div class="blue_basic_4"></div>
                                </div>

                                <div class="column_blue_content">
                                    <div><?php echo $LANG['UI_PLATFORM_SYSTEM_LOG'] ?></div>
                                    <div>
                                        <span class="basic_run_time value_system_op">--</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <div class="basic_task_each_end">
                        <div class="title_blue"><span><?php echo $LANG['UI_PUBLIC_JOB'] ?></span></div>
                        <div class="task_box_all">
                            <div class="task_content_box">
                                <div class="trapezoid_box" ></div>
                                <div <?php if ($sysAdminPermission) {
                                    echo "style='visibility:hidden'";
                                } ?>><button id="taskdetail"><?php echo $LANG['UI_JOB_DETAILS'] ?> →</button></div>
                                <div class="task_content_box_value">
                                    <div class="task_content_box_value_box">
                                        <div class="value_current_job">--</div>
                                        <div><?php echo $LANG['UI_PLATFORM_CURRENT_JOB'] ?></div>
                                    </div>
                                    <div class="task_content_box_value_box">
                                        <div class="value_run_job">--</div>
                                        <div><?php echo $LANG['UI_HOMEPAGE_STATUS_RUNNING'] ?></div>
                                    </div>
                                    <div class="task_content_box_value_box">
                                        <div class="value_success_job">--</div>
                                        <div><?php echo $LANG['WEB_PLATFORM_DES_SUCCESSED'] ?></div>
                                    </div>
                                    <div class="task_content_box_value_box">
                                        <div class="value_error_job">--</div>
                                        <div><?php echo $LANG['WEB_PLATFORM_DES_ABNORMAL'] ?></div>
                                    </div>
                                    <div class="task_content_box_value_box">
                                        <div class="value_fail_job">--</div>
                                        <div><?php echo $LANG['WEB_PUBLIC_FAILURE_HOMEPAGE'] ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>,
    <div id="card_storage_all_1" id_w="8" id_h="96" id_title="<?php echo $LANG['UI_PALTFORM_STORAGE'] ?>">
        <div id_value="card_storage_all_1" class="width_height_100">
            <div class="title_comm"><span><?php echo $LANG['UI_PALTFORM_STORAGE'] ?></span><a id="storagedetail"><?php echo $LANG['UI_VERIFY_CHECK_DETAILS'] ?></a></div>
            <div class="storage-box">

                <div>
                    <div class="border-line"></div>
                    <div class="echarts_storage_box">
                        <div class="echarts_storage_blue"></div>
                        <div class="border-line_bottom"></div>
                    </div>
                    <div class="storage_content_blue">
                        <div class="storage_content_blue_box">
                            <div class="value_storage_online">5</div>
                            <div><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></div>
                        </div>
                        <div class="storage_content_blue_box">
                            <div class="value_storage_off">4</div>
                            <div><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></div>
                        </div>
                        <div class="storage_content_blue_box">
                            <div><span class="value_storage_used">54</span><span class="value_storage_used_unit">TB</span></div>
                            <div><?php echo $LANG['UI_HOMEPAGE_USED_STORAGE'] ?></div>
                        </div>
                        <div class="storage_content_blue_box">
                            <div><span class="value_storage_residue">64</span><span class="value_storage_residue_unit">TB</span></div>
                            <div><?php echo $LANG['UI_HOMEPAGE_REMAINING_STORAGE'] ?></div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="storage_select_option">
                        <select class="bootstrap-mutiple-select selectpicker show-tick homepageSelect value_storage_time_day"  data-live-search="false" data-actions-box="false">
                            <option value="3"><?php echo $LANG['UI_HOMEPAGEPRO_PAST_THREE_DAYS'] ?></option>
                            <option value="7"><?php echo $LANG['UI_HOMEPAGEPRO_PAST_SEVEN_DAYS'] ?></option>
                            <option value="30"><?php echo $LANG['UI_HOMEPAGEPRO_PAST_ONE_MONTH'] ?></option>
                        </select>
                    </div>
                    <div class="echarts_storage_7days_box">
                        <div class="echarts_storage_7days">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="card_alarm_all_1" id_w="4" id_h="96" id_title="<?php echo $LANG['UI_PLATFORM_ALARM'] ?>">
        <div id_value="card_alarm_all_1">
            <div class="title_comm">
                <span><?php echo $LANG['UI_PLATFORM_ALARM'] ?></span>
                <div class="btn-group alarm_tab" data-toggle="buttons">
                    <label class="btn btn-default active">
                        <input type="radio" name="alarm_time_day" class="toggle" value="1" checked>24H</label>
                    <label class="btn btn-default">
                        <input type="radio" name="alarm_time_day"  class="toggle" value="7">7D</label>
                    <label class="btn btn-default">
                        <input type="radio" name="alarm_time_day"  class="toggle" value="30">30D</label>
                    <label class="btn btn-default">
                        <input type="radio" name="alarm_time_day"  class="toggle" value="90">90D</label>
                </div>
            </div>
            <div class="alarm_content_box">
                <div>
                    <div class="alarm_content_box_title">
                        <a class="alarm_title_blue" id="taskalarmdetail" <?php if ($sysAdminPermission) {
                            echo "style='visibility:hidden'";
                        } ?>><?php echo $LANG['UI_VERIFY_CHECK_DETAILS'] ?> →</a>
                    </div>
                    <div class="alarm_content_box_value">
                        <div class="value_task_total">100</div>
                        <div><?php echo $LANG['UI_PLATFORM_ALARM_TASK'] ?></div>
                    </div>
                    <div class="alarm_content_box_value_detail">
                        <p><span><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></span><span class="task_alarm_blue_value value_task_total_warning">16</span></p>
                        <div class="alarm_content_box_value_detail_line"></div>
                        <p><span><?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?></span><span class="task_error_blue_value value_task_total_error">11</span></p>
                    </div>
                </div>
                <div>
                    <div class="alarm_content_box_title">
                        <a class="alarm_title_blue" id="systemalarmdetail"><?php echo $LANG['UI_VERIFY_CHECK_DETAILS'] ?> →</a>
                    </div>
                    <div class="alarm_content_box_value">
                        <div class="value_system_total">100</div>
                        <div><?php echo $LANG['UI_VISUAL_ALARM_SYSTEM'] ?></div>
                    </div>
                    <div class="alarm_content_box_value_detail">
                        <p><span><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></span><span class="task_alarm_blue_value value_system_warning">16</span></p>
                        <div class="alarm_content_box_value_detail_line"></div>
                        <p><span><?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?></span><span class="task_error_blue_value value_system_error">11</span></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div id="card_data_protect_all_1" id_w="12" id_h="96" id_title="<?php echo $LANG['UI_HOMEPAGE_DATA_PROTECT'] ?>">
        <div id_value="card_data_protect_all_1">
            <div class="title_comm"><span><?php echo $LANG['UI_HOMEPAGE_DATA_PROTECT'] ?></span></div>
            <div class="data_protect_content_swiper_div">
                <div class="data_protect_content_swiper">
                    <div class="swiper">
                        <div class="swiper-wrapper initSwrapper">

                        </div>
                        <!-- 如果需要导航按钮 -->
                        <div class="swiper-button-prev_blue"></div>
                        <div class="swiper-button-next_blue"></div>

                    </div>



                </div>
            </div>




        </div>
    </div>
    <div id="card_system_alarm_all_1" id_w="12" id_h="96" id_title="<?php echo $LANG['UI_SYSTEM_MONITOR_SYSTEM_MONITOR'] ?>">
        <div id_value="card_system_alarm_all_1">
            <div class="title_select_blue">
                    <span><?php echo $LANG['UI_SYSTEM_MONITOR_SYSTEM_MONITOR'] ?></span>
                    <div  style="float: right;display: inline-block">
                        <select class="bootstrap-mutiple-select selectpicker show-tick homepageSelect homepage_node_select"  data-live-search="false" data-actions-box="false">
                        </select>
                    </div>
                </div>
            <div class="system_alarm_content">
                <div>
                    <div class="system_alarm_title"><div></div><?php echo $LANG['UI_DATACENTER_NETWORK_FLOW'] ?></div>
                    <div class="system_alarm_echarts_network"></div>
                </div>
                <div class="system_alarm_content_root">
                    <div class="root_cpu_echarts_box">
                        <div class="cpu_echarts_blue"></div>
                        <div class="root_echarts_blue"></div>
                    </div>
                    <div class="bps_iops_box">
                        <div>
                            <div class="system_alarm_title"><div></div><?php echo $LANG['UI_HOMEPAGE_MONITOR_BPS'] ?></div>
                            <div class="bps_iops_read"><span><?php echo $LANG['UI_HOMEPAGE_MONITOR_READ'] ?></span><span class="value_bps_read">--</span><span class="value_bps_read_unit">/S</span></div>
                            <div class="bps_iops_write"><span><?php echo $LANG['UI_HOMEPAGE_MONITOR_WRITE'] ?></span><span class="value_bps_write">--</span><span class="value_bps_write_unit">/S</span></div>
                        </div>
                        <div>
                            <div class="system_alarm_title"><div></div><?php echo $LANG['UI_HOMEPAGE_MONITOR_IOPS'] ?></div>
                            <div class="bps_iops_read"><span><?php echo $LANG['UI_HOMEPAGE_MONITOR_READ'] ?></span><span class="value_iops_read">--</span><span><?php echo $LANG['UI_HOMEPAGE_COUNT_SECOND'] ?></span></div>
                            <div class="bps_iops_write"><span><?php echo $LANG['UI_HOMEPAGE_MONITOR_WRITE'] ?></span><span class="value_iops_write">--</span><span><?php echo $LANG['UI_HOMEPAGE_COUNT_SECOND'] ?></span></div>
                        </div>

                    </div>

                </div>
            </div>



        </div>
    </div>
</div>







































<div class="editTool display-none" style="height: 500px;">
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





<script src="./assets/global/plugins/echarts/V5.4.3/echarts.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/gridstack/js/gridstack-all.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/moment.min.js"></script>
<script type="text/javascript" src="./scripts/platform/databackup_center_pro.js"></script>
