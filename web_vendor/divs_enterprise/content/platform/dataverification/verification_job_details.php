<?php
include_once '../../../tpl/permission.php';
include_once '../component/ueditor.php';
?>

<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/verification_job_details.css"/>
<!-- END PAGE LEVEL STYLES -->

<div class="row job-details" id="jobDetail">
    <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
    <!-- BEGIN PAGE HEADER-->
    <div class="header">
        <li>
            <a class="ajaxify" name="verification_job" href=" ./content/platform/jobs/verify_jobs.php">
                <span class="icons-back"><i class="viconfont vicon-fanhui"></i></span>
                <span class="txt-back"><?php echo $LANG['UI_STORAGE_RETURNBACK']?></span>
            </a>
        </li>
        <span class="back-line"></span>
        <span class="curent"><?php echo $LANG['UI_TASK_REPORT_TASK_NAME']?>：<span id="taskName"></span></span>
        <span id="status">--</span>
        <span class="header-right" id="open_job_detail"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_LOOK_MORE']?></span>
    </div>
    <div class="col-md-12 main-contents">
        <div class="main-job-client">
            <div class="hover-scroll-y clients-list">
                <div class="client-header">
                    <div class="lefts">
                        <!--<label>
                            <input id="batchChoose" type="checkbox" class="input-check">
                        </label>-->
                    </div>
                    <div class="search input-group searchs">
                        <input class="customSearch" id="search_job" autocomplete="off" type="text" maxlength="64" placeholder="按名称搜索">
                        <button class="b-btn search-btn" id="search_action"><i class="icon-search"></i></button>
                    </div>
                </div>
                <div class="client-body">

                    <!--<div class="body-check">
                        <label>
                          <input name="batchChoose[]" type="checkbox" class="input-check">
                        </label>
                        <div class="searchs list-active">
                            <label class="client-title">设备名称设备名称</label>
                            <label class="client-status client-status-default">状态</label>
                        </div>
                    </div>

                    <div class="body-check">
                        <div class="checker">
                            <span><input name="batchChoose" type="checkbox" class="input-check"></span>
                        </div>
                        <div class="searchs">
                            <label class="client-title">设备名称设备名称</label>
                            <label class="client-status client-status-blue">待验证</label>
                        </div>
                    </div>

                    <div class="body-check">
                        <div class="checker">
                            <span><input name="batchChoose" type="checkbox" class="input-check"></span>
                        </div>
                        <div class="searchs">
                            <label class="client-title">设备名称设备名称</label>
                            <label class="client-status client-status-blues">验证完成</label>
                        </div>
                    </div>

                    <div class="body-check">
                        <div class="checker">
                            <span><input name="batchChoose" type="checkbox" class="input-check"></span>
                        </div>
                        <div class="searchs">
                            <label class="client-title">设备名称设备名称</label>
                            <label class="client-status client-status-gereen">报告完成</label>
                        </div>
                    </div>

                    <div class="body-check">
                        <div class="checker">
                            <span><input name="batchChoose" type="checkbox" class="input-check"></span>
                        </div>
                        <div class="searchs">
                            <label class="client-title">设备名称设备名称</label>
                            <label class="client-status client-status-yellow">报告异常</label>
                        </div>
                    </div>-->

                </div>
            </div>

            <div >
                <div class="batch-btn batch-disabled">批量启动</div>
            </div>

        </div>
        <div class="main-job-content">
            <div class="content-verify-msg">
                <div class="tit"></div>
                <div class="titles-desc"><?php echo $LANG['UI_PLATFORM_INDUSTRY_VERIFY_INFO']?></div>

                <label class="label open_machine" id="show_final_item_cofnig" style="margin-left:20px;">
                    <span><?php echo $LANG['UI_VM_MACHINE_MORE']?></span><i class="viconfont vicon-a-Leftzuo"></i></label>

                <div class="ritht-icon" id="item_operate_job">
                    <i class="viconfont vicon-qidong "></i>
                </div>
                <div class="content-block-div">
                    <div class="block-items"><label class="items-t"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_HOST_NAME']?></label><label class="items-c" id="item_host_name_job">--</label></div>
                    <div class="block-items"><label class="items-t"><?php echo $LANG['UI_AGENT_HOST_NAME']?></label><label class="items-c" id="item_agent_name_job">--</label></div>
                    <div class="block-items"><label class="items-t"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_OPERATOR']?></label><label class="items-c" id="user_name_job">--</label></div>
                    <div class="block-items"><label class="items-t"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_CLIENT_NUM']?></label><label class="items-c" id="item_agent_uuid_job">--</label></div>
                    <div class="block-items"><label class="items-t"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_CLIENT_IP']?></label><label class="items-c" id="item_ip_job">----</label></div>
                    <div class="block-items"><label class="items-t"><?php echo $LANG['UI_VOL_CDP_VERIF_TIME_POINT']?></label><label class="items-c" id="item_timestamp_job">----</label></div>
                </div>
            </div>
            <div class="content-verify-stege">
                <div class="tit"></div>
                <div class="titles-desc"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_STAGE_STATUS']?></div>
                <div class="stege-conetnt">

                    <div class="stege-item">
                        <div id="stage_status1" class="stage-icon">
                            <img src="./img/platform/industry/loading.png" style="display: none;">
                            <i class="viconfont vicon-huanjinggoujian "></i>
                        </div>
                        <div class="stage-txt"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_BUILD_VIR']?></div>
                    </div>
                    <div class="stege-item">
                        <div id="stage_status1_line" class="stage-line"></div>
                    </div>

                    <div class="stege-item">
                        <div id="stage_status2" class="stage-icon">
                            <img src="./img/platform/industry/loading.png" style="display: none;">
                            <i class="viconfont vicon-kaijiyanzheng "></i>
                        </div>
                        <div class="stage-txt"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_OPEN']?></div>
                    </div>
                    <div class="stege-item">
                        <div id="stage_status2_line" class="stage-line"></div>
                    </div>

                    <div class="stege-item">
                        <div id="stage_status3" class="stage-icon">
                            <img src="./img/platform/industry/loading.png" style="display: none;">
                            <i class="viconfont vicon-wenjianyanzheng "></i>
                        </div>
                        <div class="stage-txt"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_FILE_VERIFY']?></div>
                    </div>
                    <div class="stege-item">
                        <div id="stage_status3_line" class="stage-line"></div>
                    </div>

                    <div class="stege-item">
                        <div id="stage_status4" class="stage-icon">
                            <img src="./img/platform/industry/loading.png" style="display: none;">
                            <i class="viconfont vicon-jiepingyanzheng "></i>
                        </div>
                        <div class="stage-txt"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_SCREEN_VERIFYS']?></div>
                    </div>
                    <div class="stege-item">
                        <div id="stage_status4_line" class="stage-line"></div>
                    </div>

                    <div class="stege-item">
                        <div id="stage_status5" class="stage-icon"><i class="viconfont vicon-yanzhengwancheng "></i></div>
                        <div class="stage-txt"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_VERIFY_FINAL']?></div>
                    </div>

                </div>
            </div>
            <div class="content-verify-plan">
                <div class="tit"></div>
                <div class="titles-desc"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_VERIFY_PLAN']?></div>
                <label class="label open_machine" id="show_final_item_plan" style="margin-left:20px;">
                    <span><?php echo $LANG['WEB_PLATFORM_GMP_JOB_LOOK_PLAN']?></span><i class="viconfont vicon-a-Leftzuo"></i></label>
            </div>
            <div class="content-verify-result">
                <div class="tit"></div>
                <div class="titles-desc"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_VERIFY_RESULT']?></div>
                <label class="label open_machine" id="show_final_item_result" style="float: right;padding-right: 0;margin-right: 0;">
                    <span><?php echo $LANG['UI_VM_MACHINE_MORE']?></span><i class="viconfont vicon-a-Leftzuo"></i>
                </label>
                <div class="result-div">
                    <div class="result-div-l">
                        <div class="result-div-tit"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_OPEN']?></div>
                        <div class="result-div-content">
                            <img id="item_open_url" src="/img/platform/open_job.png">
                            <div class="result-content-des">
                                <div class="result-content-item">
                                    <label><?php echo $LANG['WEB_VM_POWER_ON']?></label>
                                    <label class="content-item-label" id="item_open_flag">
                                        <img src="/img/platform/industry/success.svg">
                                        <?php echo $LANG['UI_GMP_REPORT_APPROVE_RESULT_PASS']?>
                                    </label>
                                </div>
                                <div class="result-content-item">
                                    <label><?php echo $LANG['UI_NETWORK']?></label>
                                    <label class="content-item-label" id="item_network_flag">
                                        <img src="/img/platform/industry/success.svg">
                                        <?php echo $LANG['UI_GMP_REPORT_APPROVE_RESULT_PASS']?>
                                    </label>
                                </div>
                                <div class="result-content-item">
                                    <label><?php echo $LANG['UI_PLATFORM_HEART_BEAT']?></label>
                                    <label class="content-item-label" id="item_heartbeat_flag">
                                        <img src="/img/platform/industry/fail.svg">
                                        <?php echo $LANG['WEB_PLATFORM_DES_ABNORMAL']?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="result-div-m">
                        <div class="result-div-tit"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_FILE_COMPARE_NUM']?></div>
                        <div class="result-div-desc">
                            <label class="label-desc"><?php echo $LANG['UI_HOMEPAGE_TOTAL_NUM']?><span class="span-desc" id="file_total_num">--</span></label>
                            <label class="label-desc" style="float: right;"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_COMPARES']?><span class="span-desc" id="file_total_compare_num">--</span></label>
                        </div>
                        <div class="result-div-con">
                            <div class="result-div-con-item"><span class="same-circle"></span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_RESULT_SAME']?><span class="right-con" id="file_total_normal_num">--</span></div>
                            <div class="result-div-con-item"><span class="error-circle"></span><?php echo $LANG['WEB_PLATFORM_DES_ABNORMAL']?><span class="right-con" id="file_total_error_num">--</span></div>
                        </div>
                    </div>
                    <div class="result-div-r">
                        <div class="result-div-tit"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_SCREEN_COMPARE_NUM']?></div>
                        <div class="result-div-desc">
                            <label class="label-desc"><?php echo $LANG['UI_HOMEPAGE_TOTAL_NUM']?><span class="span-desc" id="screen_total_num">--</span></label>
                            <label class="label-desc" style="float: right;"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_COMPARES']?><span class="span-desc" id="screen_total_compare_num">--</span></label>
                        </div>
                        <div class="result-div-con">
                            <div class="result-div-con-item"><span class="same-circle"></span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_RESULT_SAME']?><span class="right-con" id="screen_total_normal_num">--</span></div>
                            <div class="result-div-con-item"><span class="error-circle"></span><?php echo $LANG['WEB_PLATFORM_DES_ABNORMAL']?><span class="right-con" id="screen_total_error_num">--</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-verify-results" id="item_result_job">
                <div class="tit"></div>
                <div class="titles-desc"><?php echo $LANG['UI_PLATFORM_INDUSTRY_VERIFY_RESULT']?></div>
                <label class="label open_machine item-result-right" title="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_TOOL_TIPS']?>">
                    <i class="viconfont vicon-gongjuyincang"></i>
                </label>

                <div style="clear: both;" id="item_result_editor_parent">
                    <textarea id="item_result_job_editor" placeholder="<?php echo $LANG['WEB_PLATFORM_GMP_JOB_INPUT_RESULT']?>"></textarea>
                </div>
                <!-- <button type="button" id="save_result" class="btn default" style="float: right;margin-top: 6px;"><?php /*echo $LANG['UI_VCENTER_REFRESH_TIME_SAVE']*/?></button>-->
            </div>

            <div class="verify-time-block">
                <div class="verify-time-item"><span class="time-title"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_VERIFY_DATE']?>：</span><span class="time-desc" id="verify_time">----</span></div>
                <div class="verify-time-item"><span class="time-title"><?php echo $LANG['UI_STRATEGY_STARTTIME']?>：</span><span class="time-desc" id="start_time">----</span></div>
                <div class="verify-time-item"><span class="time-title"><?php echo $LANG['UI_REPORT_FINISH_TIME']?>：</span><span class="time-desc" id="end_time">----</span></div>
                <div class="verify-time-item"><span class="time-title"><?php echo $LANG['UI_JOB_INTERVAL_TIME']?>：</span><span class="time-desc" id="interval_time">--</span></div>
            </div>
            <div class="main-bottom-button">
                <button type="button" id="preReport" class="btn default" style="margin-right:12px;"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_PRE_REPORT']?></button>
                <button type="button" id="submitReport" class="btn green-haze"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_SUBMIT_REPORT']?></button>
            </div>
        </div>
        <div class="main-job-right">
            <div class="right-contents">
                <div class="block-item">
                    <div class="tit"></div>
                    <div class="titles-desc"><?php echo $LANG['UI_PLATFORM_INDUSTRY_SCREEN_COMPARE']?></div>
                    <div class="titles-link">
                        <label class="label open_machine open_verify_machine">
                            <span><?php echo $LANG['WEB_PLATFORM_GMP_JOB_OPEN_VERIFY_MACHINE']?></span><i class="viconfont vicon-a-Leftzuo"></i></label>
                        <label class="label open_machine open_prod_machine display-none">
                            <span><?php echo $LANG['WEB_PLATFORM_GMP_JOB_OPEN_PRODUCT_MACHINE']?></span><i class="viconfont vicon-a-Leftzuo"></i></label>
                    </div>
                </div>
                <div class="block-item2">
                    <button type="button" id="allScreen_job" class="btn green-haze"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_SCREENS']?></button>
                    <button type="button" id="productScreen_job" class="btn default"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_SCREENS_PRODUCT']?></button>
                    <button type="button" id="verifyScreen_job" class="btn default"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_SCREENS_VERIFY']?></button>
                </div>
                <div class="block-item-bottom">

                    <!-- <div class="screen-block screen-item-pic-job">
                         <div class="show-screen-item-shadow-box display-none">
                             <label class="icon-del screen-time-job">
                                 <i class="viconfont vicon-zujianshanchu"></i>
                             </label>
                             <label class="icon-max">
                                 <i class="viconfont vicon-a-Zoom-infangda"></i>
                             </label>
                             <div class="shadow-box">
                             </div>
                         </div>
                         <div class="screen-item">
                             <div class="screen-time-job">
                                 <input type="text" class="screen-time-value" value="第1组截屏" style="border: none;">
                             </div>
                             <div class="products product_flag">
                                 <div class="div-img">
                                     <img src="/img/platform/screen_job.png">
                                 </div>
                                 <div class="titles">
                                     <label class="labels-l">生产环境截屏<i class="viconfont vicon-zujianshanchu "></i></label>
                                     <label class="labels-r">xxxx-xx-xx</label>
                                 </div>
                             </div>
                             <div class="products verify_flag">
                                 <div class="div-img">
                                     <img src="/img/platform/screen_job.png">
                                 </div>
                                 <div class="titles">
                                     <label class="labels-l">验证环境截屏<i class="viconfont vicon-zujianshanchu "></i></label>
                                     <label class="labels-r">xxxx-xx-xx</label>
                                 </div>
                             </div>
                         </div>
                         <div class="screen-desc" >
                             <div class="result">比对结果：
                                 <label><input type="radio" name="eca4699477628b85e8ae98b8bb5b4a5d194c" value="1"><span>一致</span></label>
                                 <label><input type="radio" name="eca4699477628b85e8ae98b8bb5b4a5d194c" value="2"><span>不一致</span></label>
                                 <i class="viconfont vicon-baocun"></i>
                             </div>
                             <textarea cols="50" rows="2" placeholder="请输入截图描述备注信息"></textarea>
                         </div>
                     </div>-->

                    <div class="screen-bottom">
                        <!--<a href="###"> < </a>
                        <a href="###">1</a>
                        <a href="###">2</a>
                        <a href="###">3</a>
                        <a href="###">4</a>
                        <a href="###">5</a>
                        <a href="###">6</a>
                        <a href="###">7</a>
                        <a href="###">8</a>
                        <a href="###">...</a>
                        <a href="###">100</a>
                        <a href="###"> > </a>-->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--全屏展示-->
    <div class="full-screen-block-job display-none" id="full-screen-block">
        <div class="close-btn">
            <i class="viconfont vicon-tuichuquanping"></i><div class="text-title"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_EXIT']?></div>
        </div>
        <div class="content-div-job">
            <div class="title" id="title_screen">--</div>
            <div class="contnt-img">
                <img id="product_img" src="/img/platform/screen_job.png">
                <div class="title-img" id="product_img_title">--</div>
                <img id="verify_img" src="/img/platform/screen_job.png">
                <div class="title-img" id="verify_img_title">--</div>
            </div>
        </div>
    </div>

    <!--打开生产、验证主机-->
    <div class="full-screen-block-job display-none" id="full-machine-block">
        <div class="close-btn close-btn2">
            <i class="viconfont vicon-tuichuquanping"></i><div class="text-title"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_EXIT']?></div>
        </div>
        <div class="title-btn-block">
            <label class="in-active"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_VERIFY_VIR']?></label>
        </div>
        <div class="content-div-job content-div2" style="height:80%;border-radius:0 !important;">
            <div class="contnt-url" id="show_iframe_url">

            </div>
        </div>
    </div>

    <!--更多信息抽屉-->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
         id="drawer-item_cofnig" style="width: 800px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title">
                    <i class="viconfont vicon-xiangqing mr8"></i> <span id="objectname"></span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <div>
                    <div class="tit"></div>
                    <div class="titles-desc"><?php echo $LANG['UI_VM_MACHINE_BASIC_INFO'] ?></div>
                </div>

                <div class="row static-info"  style="clear: both;">
                    <div class="col-md-4 name">
                        <?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT_POINT'] ?>
                    </div>
                    <div class="col-md-8 value" id="timepoint">
                    </div>
                </div>
                <div class="row static-info"  style="clear: both;display: none;">
                    <div class="col-md-4 name">
                        <?php echo $LANG['WEB_PLATFORM_GMP_JOB_MAX_POINT_NUM'] ?>
                    </div>
                    <div class="col-md-8 value" id="max_timepoint_verify">
                    </div>
                </div>

                <div class="row static-info hypervisorDiv display-hide">
                    <div class="col-md-4 name">
                        <?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT_POINT'] ?>
                    </div>
                    <div class="col-md-8 value" id="hypervisor">
                    </div>
                </div>

                <div class="row static-info timepointDiv display-hide">
                    <div class="col-md-4 name">
                        <?php echo $LANG['UI_VERIFY_TIMEPOINT_INFO'] ?>
                    </div>
                    <div class="col-md-8 value" id="timepointInfo">
                    </div>
                </div>


                <div class="marin-top44">
                    <div class="tit"></div>
                    <div class="titles-desc"><?php echo $LANG['UI_DB_AGENT_CONIFG'] ?></div>
                </div>

                <div class="row static-info" style="clear: both;">
                    <div class="col-md-4 name">
                        <?php echo $LANG['UI_VERIFY_CPU_NUM'] ?>
                    </div>
                    <div class="col-md-8 value" id="cpuNum">

                    </div>
                </div>
                <div class="row static-info">
                    <div class="col-md-4 name">
                        <?php echo $LANG['UI_VERIFY_CORE_NUM'] ?>
                    </div>
                    <div class="col-md-8 value" id="coreNum">

                    </div>
                </div>
                <div class="row static-info">
                    <div class="col-md-4 name">
                        <?php echo $LANG['UI_VERIFY_MEMORY_SIZE'] ?>
                    </div>
                    <div class="col-md-8 value" id="memorySize">

                    </div>
                </div>
                <div class="row static-info">
                    <div class="col-md-4 name">
                        <?php echo $LANG['UI_VERIFY_OS_TYPE'] ?>
                    </div>
                    <div class="col-md-8 value" id="osType">

                    </div>
                </div>
                <div class="row static-info">
                    <div class="col-md-4 name">
                        <?php echo $LANG['UI_VIRTUAL_MACHINE_MAXIMUM_BOOT_WAITING_TIME'] ?>
                    </div>
                    <div class="col-md-8 value" id="maxBootTime">

                    </div>
                </div>
                <div class="row static-info">
                    <div class="col-md-4 name">
                        <?php echo $LANG['WEB_PLATFORM_GMP_JOB_SCREEN_VERIFY'] ?>
                    </div>
                    <div class="col-md-8 value" id="screenFlag">

                    </div>
                </div>

                <div class="marin-top44">
                    <div class="tit"></div>
                    <div class="titles-desc"> <?php echo $LANG['UI_SYSTEM_MONITOR_NET_MSG'] ?></div>
                </div>

                <div class="row static-info" style="clear: both;">
                    <table id="networkTable"></table>
                </div>

            </div>
            <div class="drawer-footer">
                <button type="button" class="btn green-haze" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
            </div>
        </div>
    </div>


</div>
<!-- END PAGE CONTENT-->



<!--方案抽屉-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="drawer-item_plan_editor" style="width: 1000px;">
    <div class="drawer-content drawer-content-scrollable" role="document" id="item_plan_job">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top">
                <i class="viconfont vicon-hangyeheguijiancha" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['WEB_PLATFORM_GMP_JOB_PLAN_DETAIL']?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">
            <!-- BEGIN FORM-->
            <div class="row" style="padding:0 20px;">
                <div id="item_plan_editor_parent">
                    <textarea id="item_plan_job_editor" placeholder="<?php echo $LANG['WEB_PLATFORM_GMP_JOB_PLAN_INPUT']?>"></textarea>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <div class="item-result-right plan-btn" title="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_TOOL_TIPS']?>">
                <i class="viconfont vicon-gongjuyincang"></i>
            </div>
            <button type="button" class="btn green-haze" id="save_plan"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
            <button type="button" class="btn default" style="margin-right:12px;" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?></button>

        </div>
    </div>
</div>

<!--任务详情信息抽屉-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="drawer-job_detail_config" style="width: 800px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-body">
            <div>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
                <div class="portlet box blue-hoki" style="border: none;">
                    <div class="portlet-title" style="padding-left:0;">
                        <ul class="nav nav-tabs floatl">
                            <li class="active">
                                <a href="#detail_div" data-toggle="tab">
                                    <i class="viconfont vicon-xiangqing"></i> <?php echo $LANG['UI_PLATFORM_JOB_DETAILS']?>
                                </a>
                            </li>
                            <li>
                                <a href="#log_div" data-toggle="tab">
                                    <i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG']?>
                                </a>
                            </li>
                            <li>
                                <a href="#history_div" data-toggle="tab">
                                    <i class="viconfont vicon-histroytask"></i> <?php echo $LANG['UI_PLATFORM_HOSTORY_JOB']?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body form">
                        <div class="tab-content" style="padding:20px 0;">
                            <div class="tab-pane active" id="detail_div">
                                <div class="details_more_box">
                                    <div class="details_more_head">
                                        <div class="row">
                                            <i class="viconfont vicon-gaiyao1 mr8"></i><?php echo $LANG['UI_JOB_SUMMARY'];?>
                                        </div>
                                    </div>
                                    <div class="details_more_content">
                                        <div class="details_more_content_body">
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_JOB_RNAME'] ?>:
                                                </div>
                                                <div class="col-md-8 value" id="taskName2" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_PUBLIC_TASK_TYPE'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="taskType">
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_JOB_START_TIME'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="startTime">
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_JOB_INTERVAL_TIME'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="intervalTime">
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_VERIFY_TYPE'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="verifyType">
                                                </div>
                                            </div>

                                        </div>
                                        <hr class="details_more_hr">
                                    </div>
                                </div>

                                <div class="details_more_box">
                                    <div class="details_more_head">
                                        <div class="row">
                                            <i class="viconfont vicon-celve1 mr8"></i><?php echo $LANG['UI_JOB_STRATEGY'];?>
                                        </div>
                                    </div>
                                    <div class="details_more_content">
                                        <div class="details_more_content_body">
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_JOB_CREATE_TIME'] ?>:
                                                </div>
                                                <div class="col-md-8 value" id="createTime" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_JOB_NEXT_START_TIME'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="nextTime">
                                                </div>
                                            </div>

                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_VM_MACHINE_BOOT_MODE'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="timeStrategy">
                                                </div>
                                            </div>

                                        </div>
                                        <hr class="details_more_hr">
                                    </div>
                                </div>

                                <div class="details_more_box">
                                    <div class="details_more_head">
                                        <div class="row">
                                            <i class="viconfont vicon-ge_advanced mr8"></i><?php echo $LANG['UI_JOB_HIGH'];?>
                                        </div>
                                    </div>
                                    <div class="details_more_content">
                                        <div class="details_more_content_body">
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_PLATFORM_VIRTUAL_LAB'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="labname">
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_VERIFY_BACKUP_SERVER_IP'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="backup_server_ip">
                                                </div>
                                            </div>
                                            <div class="row static-info display-none">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_VERIFY_MANAGE_HOST_NUM_MEANWHILE'] ?>:
                                                </div>
                                                <div class="col-md-8 value" id="deal_vm_num">
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_VERIFY_FILE_THREAD_NUM'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="doc_compare_thread">
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'];?>:
                                                </div>
                                                <div class="col-md-8 value" id="ignore_resource_limit">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane" id="log_div">
                                <ul class="feeds" id="runninglog">
                                </ul>
                            </div>

                            <div class="tab-pane" id="history_div">
                                <div class="table-container">
                                    <table id="historyTable"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn green-haze" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
        </div>
    </div>
</div>

<!-- 截屏报告分析 -->
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/industry/job_report.php';?>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/platform/dataverification/verification_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
