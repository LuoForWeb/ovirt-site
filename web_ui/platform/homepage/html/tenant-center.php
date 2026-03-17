<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />
<link href="./css/platform/homepage/homepage.css" rel="stylesheet" type="text/css">

<!-- BEGIN PAGE HEADER-->

<!-- END PAGE HEADER-->
<div class="datacenter-content homepage_tenant_content">
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li>
                <div class="data-center-icon"></div>
                <span ><?php echo $LANG['UI_PLATFORM_HOMEPAGE']?></span>
            </li>
        </ul>
    </div>
    <div class="homepage_tenant_page">
        <div class="tenant_all_content">
            <div class="tenant_all_content_left">
                <div class="tenant_basic_info">
                    <div class="tenant_all_content_left_box">
                        <div class="img_backup_data"></div>
                        <div><?php echo $LANG['WEB_TENANT_HOMEPAGE_ACCUMULATE_DATA'] ?></div>
                        <div><span id="tenant_all_backup_size">0</span><span id="tenant_all_backup_size_unit">B</span></span></div>
                    </div>
                    <a class="tenant_all_content_left_box" name="task" id="current_task">
                        <div class="img_current_task"></div>
                            <div><?php echo $LANG['UI_PLATFORM_CURRENT_JOB'] ?></div>
                            <div><span id="tenant_current_job_num">0</span><span class="href-detail">></span></span></div>
                    </a>
                    <a class="tenant_all_content_left_box" name="task" id="history_task">
                        <div class="img_history_task"></div>
                            <div><?php echo $LANG['UI_PLATFORM_HOSTORY_JOB'] ?></div>
                            <div><span id="tenant_history_job_num">0</span><span  class="href-detail">></span></span></div>
                    </a>
                </div>
                <div class="tenant_data_center">
                <div class="tenant_data_center_title"> <div class="data-protection-icon"></div> <?php echo $LANG['UI_HOMEPAGE_DATA_PROTECT'] ?></div>
                    <div class="tenant_data_center_container">  
                        
                        <!-- 更多元素 -->  
                    </div>
                </div>
            </div>
            <div class="tenant_all_content_right">
                <div class="tenant_all_content_right_title">
                    <div><div class="data-protection-icon"></div><?php echo $LANG['WEB_TENANT_HOMEPAGE_RECENT'] ?></div>
                    <div class="backup_days">
                        <select class="form-control  backup_days_select" id="backup_days" data-live-search="false">
                            <option value="3"><?php echo $LANG['UI_HOMEPAGEPRO_PAST_THREE_DAYS'] ?></option>
                            <option value="7"><?php echo $LANG['UI_HOMEPAGEPRO_PAST_SEVEN_DAYS'] ?></option>
                            <option value="30"><?php echo $LANG['UI_HOMEPAGEPRO_PAST_ONE_MONTH'] ?></option>
                        </select>
                    </div>
                </div>
                <div id="backup_chart" style="height: 590px; width:100%;margin-top: 20px;">
                    

                </div>


            </div>
        </div>
    </div>
</div>


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.4.3/echarts.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./scripts/platform/tenant_center.js"></script>
<!-- END PAGE LEVEL PLUGINS -->