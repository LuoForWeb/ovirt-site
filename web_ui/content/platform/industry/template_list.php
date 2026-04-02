<?php
include_once '../../../tpl/permission.php';
include_once  '../public/bs_table.php';
?>

<!-- BEGIN PAGE HEADER-->
<div class="row" style="height: 100%;">
    <div class="col-md-12" style="height: 100%;">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki" id='authdiv'>
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-a-File-editing-onebianjiwenjian1"></i><?php echo $LANG['WEB_INDUSTRY_TEMPLATE_REPORT'];?>
                </div>
            </div>
            <div class="portlet-body">
                <!-- <div class="tab-content row margin10"> -->
                <div class="tab-pane" id="template_list_div">
                    <!-- BEGIN PAGE CONTENT-->
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Begin: life time stats grey-cararra -->
                            <div class="portlet-body m-10">
                                <div class="vin_toolbar" id="vin_template_toolbar">
                                    <div class="leftTool">
                                    </div>

                                    <div class="rightTool">
                                        <div class="vin_btnToolbar">
                                        </div>
                                    </div>
                                </div>
                                <table id="template_table"></table>
                                <!-- <div class="alert alert-block alert-info fade in" id="marktips">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <ul class="alert-ul">
                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                        <li>
                                            <?php echo $LANG['UI_JOB_CURRENT_NAME_TIPS'] ?>
                                        </li>
                                    </ul>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- </div> -->
            </div>
        </div>
        <!-- END TAB PORTLET-->
    </div>
</div>
<!-- END PAGE CONTENT-->

<script src="./scripts/platform/industry/template_list.js"></script>

<!--引入报告配置-->
<?php require_once 'job_report.php';?>
