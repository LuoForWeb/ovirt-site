<?php
include_once '../../../tpl/permission.php';
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="industry_template" href="./content/platform/industry/template_list.php" >
            <span><?php echo $LANG['WEB_INDUSTRY_TEMPLATE_REPORT'];?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_EDIT'];?></span>
</h3>
<!-- BEGIN FORM-->
<?php
    include_once 'template_config.php';
?>

<!--生成 第二步-->
<div class="row display-none" id="industry_template_step2">
    <div class="portlet box blue-hoki" id="confirm_config">
        <div class="portlet-body form">
            <div id="step2" class="form-body">
                <div class="form-group mt30 col-md-12">
                    <label class="control-label col-md-3 text-right">
                        <span class="required">*</span><?php echo $LANG['UI_REPORT_STRATEGY_NAME']?>
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <input type="text" maxlength="128" class="form-control" id="temp_name"/>
                        </div>
                    </div>
                </div>
                <!--<div class="form-group col-md-12">
                    <label class="control-label col-md-3 mt30 text-right">
                        <span class="required">*</span>病毒查杀
                    </label>
                    <div class="col-md-6 mt30">
                        <div class="input-icon right">
                            <input type="text" maxlength="128" value="20" class="form-control" id="virtus_num"/>
                            <div><span class="help-block ">病毒查杀报告里面展示的数量</span></div>
                        </div>
                    </div>
                </div>-->
                <input type="hidden" value="20"  id="virtus_num"/>

                <div class="form-group col-md-12">
                    <label class="control-label col-md-3 mt30 text-right">
                        <span class="required">*</span><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_FILES_COMPARE']?>
                    </label>
                    <div class="col-md-6 mt30">
                        <div class="input-icon right">
                            <input type="text" maxlength="128" value="20" class="form-control" id="document_num"/>
                            <div><span class="help-block "><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_FILES_COMPARE_TIPS']?></span></div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="control-label col-md-3 mt30 text-right">
                        <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_SCREEN_WATER']?>
                    </label>
                    <div class="col-md-6 mt30">
                        <div class="input-icon right">
                            <input type="checkbox" id="screen_water" class="make-switch"
                                   data-on-color="primary" data-off-color="info" data-size="small"
                                   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            <a class="popovers ml15" data-container="body" data-trigger="hover"
                               data-placement="right"
                               data-content="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_SCREEN_WATER_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="form-group screen_water_div_diy col-md-12 display-none">
                    <label class="control-label col-md-3 text-right">
                        <span class="required">*</span><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_DES_WORD'] ?>
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <input type="text" maxlength="128" class="form-control" id="screen_water_value"/>
                            <div><span class="help-block "><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_DES_WORD_TIPS'] ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="control-label col-md-3 mt30 text-right">
                        <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_WATER'] ?>
                    </label>
                    <div class="col-md-6 mt30">
                        <div class="input-icon right">
                            <input type="checkbox" id="report_water" class="make-switch"
                                   data-on-color="primary" data-off-color="info" data-size="small"
                                   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            <a class="popovers ml15" data-container="body" data-trigger="hover"
                               data-placement="right"
                               data-content="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_WATER_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="form-group report_water_div_style col-md-12 display-none">
                    <label class="control-label col-md-3 text-right">
                        <span class="required">*</span><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_WATER_TYPE'] ?>
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <select  class="form-control" id="report_water_select">
                                <option selected value="0"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_WATER_TYPE_TIMESTAMP'] ?></option>
                                <option value="1"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_WATER_TYPE_DIY'] ?></option>
                                <option value="2"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_WATER_TYPE_DIY_TIME'] ?></option>
                                <option value="3"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_WATER_TYPE_DIY_TIME_USER'] ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group report_water_div_diy col-md-12 display-none">
                    <label class="control-label col-md-3 text-right">
                        <span class="required">*</span><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_DES_WORD'] ?>
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <input type="text" maxlength="128" class="form-control" id="report_water_value"/>
                            <div><span class="help-block "><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_WATER_TYPE_DIY_INFO'] ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="control-label col-md-3 mt30 text-right">
                        <?php echo $LANG['UI_REPORT_EMAIL'] ?>
                    </label>
                    <div class="col-md-6 mt30">
                        <div class="input-icon right">
                            <input type="checkbox" id="email_notice" class="make-switch"
                                   data-on-color="primary" data-off-color="info" data-size="small"
                                   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            <a class="popovers ml15" data-container="body" data-trigger="hover"
                               data-placement="right"
                               data-content="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_EMAIL_NOTICE'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="form-group email_notice_div col-md-12 display-none">
                    <label class="control-label col-md-3 text-right"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_EMAIL'] ?>
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <textarea class="form-control" id="email_notice_value" rows="6" placeholder="<?php echo $LANG['UI_REPORT_EMAIL_ADDRESS'] ?>"></textarea>
                            <div>
                                    <span class="help-block">
                                      <?php echo $LANG['UI_SETTINGS_NOTICE_RECV_EMAIL_TIP2'] ?>
                                    </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="control-label col-md-3 mt30 text-right"><?php echo $LANG['UI_PUBLIC_REMARK']?>
                    </label>
                    <div class="col-md-6 mt30 ">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <textarea class="form-control" id="remark" rows="6" placeholder="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_DES'] ?>"></textarea>
                            <div><span class="help-block"></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions--create" id="bottom-btn">
                <div class="row">
                    <button type="button" id="template_pre" class="btn default button-previous">
                        <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']?>
                    </button>
                    <button type="button" id="template_next" class="btn green-turquoise button-next" style="margin: 0 10px;">
                        <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_PRE']?> <i class="viconfont vicon-xiayibu"></i>
                    </button>
                    <button type="button" id="template_submit" class="btn green-haze button-submit">
                        <?php echo $LANG['UI_PUBLIC_SUBMIT']?> <i class="viconfont vicon-xiayibu"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row portlet box blue-hoki display-none " id="industry_template_pre">
    <div class="portlet-body form">
        <div id="step3" class="form-body">
            <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_REPORT_PRE']?>
        </div>
        <div class=" form-actions--create" id="bottom-btn">
            <div class="row">
                <a href="javascript:;" id="template_pre2" class="btn default button-previous" style="visibility: visible;">
                    <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']?>
                </a>
                <a href="javascript:;" id="template_submit2" class="btn btn-confirm green-haze button-submit">
                    <?php echo $LANG['UI_PUBLIC_SUBMIT']?> <i class="viconfont vicon-xiayibu"></i>
                </a>
            </div>
        </div>
    <div
</div>
<!-- END MODAL -->

<!-- END FORM-->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script>
   // 拿出来初始化
    jQuery(document).ready(function() {
        TemplateConfig.init();
    });
</script>
