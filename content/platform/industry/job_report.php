<?php
include_once '../component/ueditor.php';
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/industry/template.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/industry/report.css"/>
<style>

    .page_report {
        min-height: 297mm;
        padding: 20px 24px 30px;
        margin: 10mm auto;
        border: 1px solid #ccc;
        box-sizing: border-box;
        position: relative;
        page-break-after: always;
        background: white;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .page-content_report {
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .page-number_report {
        position: absolute;
        bottom: 2mm;
        right: 7mm;
        font-size: 12pt;
        color: rgb(102, 102, 102);
        text-align: center;
        pointer-events: none; /* 避免干扰点击 */
    }
</style>
<!-- BEGIN PAGE HEADER-->

<!--- 编辑报告 ---->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-config" style="width: 880px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-document-title" style="vertical-align: top">
                <i class="viconfont vicon-a-Group1000003103" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_PLATFORM_INDUSTRY_REPORT_SETTING']?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body form-horizontal">
            <div class="portlet-body">
                <div class="row">
                    <div class="col-md-12" id="industry_report">
                        <!-- BEGIN FORM-->
                        <div class="row" id="industry_report">
                            <div class="item-right-div">
                                <div id="parent_container">
                                    <div id="container" style="overflow-x: hidden" class="hover-scroll-y">
                                        <div class="report-title">
                                            <input type="text" class="form-control input-inline input-title-ch" id="report_title" value="<?php echo $LANG['UI_PLATFORM_INDUSTRY_REPORT_TITLE']?>">
                                            <input type="text" class="form-control input-inline input-title-en item_en" id="report_title2" value="xxx Verification Report">
                                        </div>
                                        <div class="title-line"></div>
                                        <div class="basic-info basic-title1s">
                                            <?php echo $LANG['UI_JOB_BASE_INFO']?>
                                            <span class="item-line item_en"></span>
                                            <span class="item_en span-title-basic">BASIC INFORMATION</span>
                                        </div>
                                        <div class="basic-title-line display-visibility"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <div id="bottom-btn2">
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default" style="margin-left: 12px;"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
                <!--<button type="button" id="preBut" class="btn green-haze btn-confirm">
                    <i class="viconfont vicon-a-Eyesyanjing"></i>
                    预 览
                </button>-->
                <button type="button" id="tempSubmit" class="btn green-haze btn-confirm display-none">
                    <i class="viconfont vicon-a-zancunbaocun"  style="margin-right:4px;"></i><?php echo $LANG['UI_PLATFORM_INDUSTRY_REPORT_TEMP_SAVE']?>
                </button>
                <button type="button" id="makeSubmit" class="btn green-haze btn-confirm display-none">
                    <i class="viconfont vicon-a-Group1000003103"></i>
                    <?php echo $LANG['UI_PLATFORM_INDUSTRY_REPORT_MAKE']?></button>
            </div>
        </div>
    </div>
</div>
<!--- 编辑报告end ---->

<!--更多病毒查杀html-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-virtus" style="width: 800px">
    <div class="drawer-content drawer-content-scrollable" role="virtus">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-virtus-title" style="vertical-align: top">
                <i class="viconfont vicon-a-Frame1000002980" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_PLATFORM_INDUSTRY_REPORT_VIRUS']?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body form-horizontal">
            <div class="portlet-body">
                <div class="row" id="dragle_virtus">
                    <div class="col-md-12 strategy-table" style="padding: 20px;">
                        <!-- Begin: life time stats grey-cararra -->
                        <div class="table-toolbar-wrapper vin_toolbar" id="vin_job_report_virtus_toolbar">
                            <div class="leftTool">
                            </div>
                        </div>
                        <div class="table-container">
                            <table id="virtus-table"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END MODAL -->

<!--更多文件比对html-->
<div id="drawer-document" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static" style="width: 80%;">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i  class="viconfont vicon-a-Notesbiji"></i> <?php echo $LANG['UI_PLATFORM_INDUSTRY_REPORT_FILES_COMPARE']?></h4>
    </div>
    <div class="modal-body">
        <div class="row" id="dragel_document">
            <div class="col-md-12 strategy-table" style="padding: 20px;">
                <!-- Begin: life time stats grey-cararra -->
                <div class="table-toolbar-wrapper vin_toolbar" id="vin_job_report_document_toolbar">
                    <div class="leftTool">
                    </div>
                    <div class="rightTool page-right">
                        <button type="button" title="<?php echo $LANG['UI_LOG_SYSTEM_LOG_DOWNLOAD_NOW']?>" id="downloadlFile" class="btn btn-primary b-btn btn-table brr2 download ml10">
                            <i class="log_download"></i>
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table id="document-table"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END MODAL -->

<!--预览html-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-pre" style="width: 960px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-document-title" style="vertical-align: top">
                <i class="viconfont vicon-a-Group1000003103" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_PLATFORM_INDUSTRY_REPORT_PRE']?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body form-horizontal">
            <div class="portlet-body">
                <div class="row">
                    <div class="col-md-12" id="pre-report" style="padding: 20px;">

                    </div>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default" style="margin-left: 10px;"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
            <button type="button" class="btn green-haze btn-confirm display-none" id="reportApproval" style="width:70px;">
                <i class="viconfont vicon-shenpi"></i>
                <?php echo $LANG['UI_PLATFORM_INDUSTRY_REPORT_APPROVAL']?></button>
            <button type="button" class="btn green-haze btn-confirm display-none" id="downloadReport">
                <i class="viconfont vicon-xiazai"></i>
                <?php echo $LANG['UI_VERIFY_REPORT_DOWNLOAD']?></button>
            <button type="button" class="btn green-haze btn-confirm display-none" id="reportEmail">
                <i class="viconfont vicon-fasongyoujian"></i>
                <?php echo $LANG['UI_VERIFY_REPORT_SEND_TO_EMAIL']?></button>
        </div>
    </div>
</div>
<!-- END MODAL -->

<!-- END FORM-->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<?php
if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/industry/job_report.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
