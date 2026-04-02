<?php
include_once '../../tpl/permission.php';
$userAllPermission = $_SESSION['permission'];
?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css" />
<link href="../../assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css">
<link href="./css/platform/databackup-center.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./css/platform/todo_list.css" />
<!-- BEGIN PAGE HEADER-->
<!-- <div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <div class="data-center-icon"></div>
            <span ><?php echo $LANG['UI_PLATFORM_HOMEPAGE'] ?></span>
        </li>
    </ul>
</div> -->
<!-- END PAGE HEADER-->
<div class="datacenter-content" style="height: 100%;">
    <div class="pending-tip-div display-none">
        <div class="pending-tip"><i class="viconfont vicon-tishi mr10"></i>您有<span id="pending_count" style="font-weight: bold;padding: 0 5px;">0</span>份验证报告待审批！<a id="go_approve" style="font-weight: 400;font-size: 16px;color: #2A87C8;">前往审批</a></div>
    </div>
    
    <div class="row mb16" style="height: 48.5%;">
        
        <div class="col-md-4 col-sm-4 allInfo device-info-div pr0">
            <div class="shadow-box shadow-box-sm" id="count_div" style="height: 100%;padding: 20px;">
                <div class="message-title" style="padding: 0;">概览<a style="float: right;" id="more_device">查看更多</a>
                </div>
                <div class="device-info mt15"
                    style="width: 100%;display: flex;justify-content: space-around;text-align: center;">
                    <span class="total-content name"><span class="total-val value">0</span><br>设备总个数</span>
                    <span class="plan-yearly name"><span class="plan-val value">0</span><br>本年度验证计划</span>
                    <span class="total-content name"><span class="unverified-val value">0</span><br>未完成验证计划</span>
                </div>
                <div class="mt15" style="height: 75%;">
                    <div class="total-device-div col-md-6" style="width: 48%;height: 100%;">
                        <div id="total-device-chart" style="height: 66%;"></div>
                        <div class="total-device-info"
                            style="height: 34%;padding: 0 12px 27px 12px;display: flex;flex-direction: column;justify-content: space-between;">
                            <div>
                                <span class="info span-text"><span class="circle"
                                        style="background: #2A87C8;margin-bottom: 1.75px;"></span>已备份设备数量</span><span
                                    class="value backed num-text" style="float: right;font: size 16px;"></span>
                            </div>
                            <div>
                                <span class="info span-text"><span class="circle"
                                        style="background: #CCCCCC;margin-bottom: 1.75px;"></span>未备份设备数量</span><span
                                    class="value not-backed num-text" style="float: right;font: size 16px;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="verify-device-div col-md-6" style="width: 48%;height: 100%;float: right">
                        <div id="verify-device-chart" style="height: 66%;"></div>
                        <div class="verify-device-info"
                            style="height: 34%;padding: 0 12px 27px 12px;display: flex;flex-direction: column;justify-content: space-between;">
                            <div>
                                <span class="info span-text"><span class="circle"
                                        style="background: #51CBFF;margin-bottom: 1.75px;"></span>已验证设备数量</span><span
                                    class="value verify num-text" style="float: right;font: size 16px;"></span>
                            </div>
                            <div>
                                <span class="info span-text"><span class="circle"
                                        style="background: #CCCCCC;margin-bottom: 1.75px;"></span>未验证设备数量</span><span
                                    class="value not-verify num-text" style="float: right;font: size 16px;"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 col-sm-4 allInfo client-info-div pr0">
            <span class="message-title" style="position: absolute;z-index: 5;top: 0;left: 39px;">设备验证次数</span>
            <a style="position: absolute;z-index: 5;top: 26px;right: 168px;" id="more_verify">查看更多</a>
            <select name="" class="form-control select2me chart-select" id="bar_select">
                <option class="chart-select-option" value="year">最近一年</option>
                <option class="chart-select-option" value="season">最近一季度</option>
                <option class="chart-select-option" value="week">最近一周</option>
            </select>
            <div class="client-info shadow-box shadow-box-sm" id="client_bar" style="height: 100%;padding: 20px;">
            </div>
        </div>
        
        <div class="col-md-4 col-sm-4 allInfo message-info-div pr0">
            <div class="shadow-box shadow-box-sm" id="message_div" style="height: 100%;">
                <div class="message-title pl20 pr20">待办事项<a style="float: right;" id="more_msg">查看更多</a></div>
                <div class="message-content pl20 pr20" style="height: 85%;"></div>
            </div>
        </div>
    </div>
    
    <div class="row mb16" style="height: 48.5%;">
        <div class="col-md-8 col-sm-8 allInfo report-info-div pr0" style="background: transparent;">
            <select name="" class="form-control select2me chart-select" id="line_select">
                <option class="chart-select-option" value="year">最近一年</option>
                <option class="chart-select-option" value="season">最近一季度</option>
                <option class="chart-select-option" value="week">最近一周</option>
            </select>
            <div class="col-md-12 col-sm-12 pr0 shadow-box shadow-box-sm" style="height: 100%;">
                <div class="col-md-3 pl0 shadow-box-sm" style="width: 28%;height: 100%;position: relative;padding-top: 20px;">
                    <span class="message-title ml5" style="padding: 0;position: absolute;">验证报告统计</span>
                    <div class="pie-div" id="report_pie" style="width: 100%;height: 65%;"></div>
                    <div class="info-div" style="width: 100%;height:30%;padding: 16px 12px;">
                        <div class="flex-div">
                            <div>
                                <span class="circle" style="background: #2A87C8"></span>
                                <span class="span-text"
                                    style="font-size: 14px;display: inline-block;width: 100px;">审批完成报告</span>
                            </div>
                            <div class="flex-div" style="width: 20%;">
                                <span class="complete_percent num-text" style="color: #2A87C8;"></span>
                                <span class="complete_num num-text" style="float: right;"></span>
                            </div>
                        </div>
                        <div class="mt12 flex-div">
                            <div>
                                <span class="circle" style="background: #51CBFF"></span>
                                <span class="span-text"
                                    style="font-size: 14px;display: inline-block;width: 100px;">审批中报告</span>
                            </div>
                            <div class="flex-div" style="width: 20%;">
                                <span class="approving_percent num-text" style="color: #2A87C8;"></span>
                                <span class="approving_num num-text" style="float: right;"></span>
                            </div>
                        </div>
                        <div class="mt12 flex-div">
                            <div>
                                <span class="circle" style="background: #B6E9FF"></span>
                                <span class="span-text"
                                    style="font-size: 14px;display: inline-block;width: 100px;">已驳回报告</span>
                            </div>
                            <div class="flex-div" style="width: 20%;">
                                <span class="reject_percent num-text" style="color: #2A87C8;"></span>
                                <span class="reject_num num-text" style="float: right;"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-9 pr0 report-line-div shadow-box-sm" style="width: 71%;height: 100%;padding-top: 20px;padding-bottom: 20px;">
                    <div class="report-select" style="width: 100%;height:8%"></div>
                    <div class="report-line-info pr20"
                        style="width: 100%;height: 17%;display: flex;justify-content: center;text-align: center;align-items: end;">
                        <span class="name" style="margin-right: 60px;"><span
                                class="total-val value">0</span><br>提交报告总量</span>
                        <span class="name" style="margin-right: 60px;"><span
                                class="success-val value">0</span><br>正常报告总量</span>
                        <span class="name" style="margin-right: 60px;"><span
                                class="abnormal-val value">0</span><br>异常报告总量</span>
                    </div>
                    <div class="col-md-12" id="report_line" style="height: 75%;padding: 0;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-4 allInfo monitor-info-div pr0">
            <div class="shadow-box shadow-box-sm" id="monitor_div" style="height: 100%;padding: 20px;">
                <div class="message-title" style="padding: 0;">系统监控</div>
                <div>
                    <div class="cpu-content" style="margin-top: 16px;position: relative;">
                        <span
                            style="position: absolute;top: 12px;left: 17px;font-weight: 400;font-size: 14px;color: #4D4D4D;">CPU</span>
                        <span
                            style="position: absolute;top: 12px;left: 55%;font-weight: 400;font-size: 14px;color: #4D4D4D;">内存</span>
                        <div class="gmp-chart col-md-6" id="cpuCircle" style="height: 157px;width: 48%;"></div>
                        <div class="gmp-chart col-md-6" id="memeryCircle"
                            style="height: 157px;float: right;width: 48%;"></div>
                    </div>
                </div>

                <div class="storage-content" style="height: 48%;">
                    <div class="gmp-chart col-md-12" id="diskCircle"
                        style="height: 90%;margin-top: 12px;padding:18px 28px 31px 17px">
                        <div class="massage-title" style="font-weight: 400;font-size: 14px;color: #4D4D4D;">存储</div>
                        <div class="storage-warper">
                            <div class="storage-title" style="font-size: 16px;font-weight: 400;color: #333333;">可用容量：<span
                                    class="storage-value num-text" style="font-size: 18px;"></span></div>

                            <div class="ml5" style="position: relative;width: 100%">
                                <div class="progress-line-used"
                                    style="width: 0;height: 12px;background: #2A87C8;position: absolute;z-index: 2;">
                                </div>
                                <div class="progress-line" style="width: 100%;height: 12px;background:#F0F0F0"></div>
                            </div>

                            <div class="storage-content"
                                style="width: 100%;display: flex;justify-content: space-between;color: #999999;font-size: 14px;font-weight: 400;">
                                <div class="free-size">已使用：<span class="value"></span></div>
                                <div class="total-size">总容量：<span class="value"></span></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





<!-- BEGIN PAGE LEVEL PLUGINS -->
<!-- <script src="./scripts/plugins/flexible.js"></script> -->
<script type="text/javascript" src="../../assets/global/plugins/jquery-fontFlex/jQuery.fontFlex.js"></script>
<script src="./assets/global/plugins/echarts/echarts5.5.0.js"></script>
<script src="./assets/global/plugins/counterup/jquery.waypoints.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/moment.min.js"></script>
<script type="text/javascript" src="./scripts/platform/databackup_center_vinchin.js"></script>
<!-- END PAGE LEVEL PLUGINS -->