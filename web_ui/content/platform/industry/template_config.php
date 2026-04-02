<?php
include_once '../component/ueditor.php';
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/jquery-ui/jquery-ui.css" />
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="./css/platform/industry/template.css"/>
<!-- BEGIN PAGE HEADER-->

<!-- BEGIN FORM-->
<div class="row" id="industry_template">
    <input type="hidden" id="template_uuid" value="<?php echo $_GET['uuid'];?>">
    <input type="hidden" id="is_en_cn" value="1">
    <div class="col-md-12">
        <div class="showItems showItems2 display-none" title="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_EXPEND']?>">
            <i class="viconfont vicon-zhankai"></i>
        </div>
        <div class="col-md-2 item-left-div-d hover-scroll-y" style="overflow-x:hidden;">
            <div id="container-item">
                <div class="showItems" title="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_RETRACT']?>">
                    <i class="viconfont vicon-shouqi"></i>
                </div>
                <div class="item">
                    <div style="margin-bottom:12px;">
                        <span class="item-span"></span>
                        <label class="item-label"><?php echo $LANG['UI_TENANT_COMMON']?></label>
                    </div>
                    <div class="item-gaiyao">
                        <button class="addItem item_logo" data-id="item_logo" style="margin-right:12px;">
                            <i class="viconfont vicon-logo"></i>
                            <span class="span">LOGO</span></button>
                        <button class="addItem item_qrcode" data-id="item_qrcode">
                            <i class="viconfont vicon-erweima"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_QRCODE']?></span></button>
                        <button class="addItem item_sys_code" data-id="item_sys_code" style="margin-right:12px;">
                            <i class="viconfont vicon-zhujiming"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_SYS_NUM']?></span></button>
                        <button class="addItem item_sys_name" data-id="item_sys_name">
                            <i class="viconfont vicon-IPdizhi"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_SYS_NAME']?></span></button>
                        <button class="addItem item_host" data-id="item_host" style="margin-right:12px;">
                            <i class="viconfont vicon-zhujiming"></i>
                            <span class="span"><?php echo $LANG['UI_AGENT_HOST_NAME']?></span></button>
                        <button class="addItem item_ip" data-id="item_ip">
                            <i class="viconfont vicon-IPdizhi"></i>
                            <span class="span"><?php echo $LANG['UI_PUBLIC_IP_ADDRESS']?></span></button>
                        <button class="addItem item_plan" data-id="item_plan" style="margin-right:12px;">
                            <i class="viconfont vicon-fangan"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_PLAN']?></span></button>
                        <button class="addItem item_result" data-id="item_result">
                            <i class="viconfont vicon-baogaoliebiao"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_RESULT']?></span></button>
                        <button class="addItem item_operator" data-id="item_operator" style="margin-right:12px;">
                            <i class="viconfont vicon-caozuorenyuan"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_OPERATOR']?></span></button>
                       <!-- <button class="addItem item_verify_time" data-id="item_verify_time">
                            <i class="viconfont vicon-shengchengshijian"></i>
                            <span class="span"><?php /*echo $LANG['UI_VOL_CDP_GENERATE_TIME']*/?></span></button>-->
                        <button class="addItem item_results" data-id="item_results">
                            <i class="viconfont vicon-jieguo"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_RESULTS']?></span></button>
                        <button class="addItem item_download_time" data-id="item_download_time" style="margin-right:12px;">
                            <i class="viconfont vicon-baogaoxiazaishijian"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_DOWNLOAD_TIME']?></span></button>
                        <button class="addItem item_make_time" data-id="item_make_time">
                            <i class="viconfont vicon-a-Group1000003103"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_MAKE_TIME']?></span></button>
                        <button class="addItem item_operator_sign" data-id="item_operator_sign" style="margin-right:12px;">
                            <i class="viconfont vicon-a-shenheyuanqianzi"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_OPERATOR_SIGN']?></span></button>
                        <button class="addItem item_auditor_sign" data-id="item_auditor_sign">
                            <i class="viconfont vicon-a-shenheyuanqianzi"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_AUDITOR_SIGN']?></span></button>
                        <button class="addItem item_operator_sign_sys" data-id="item_operator_sign_sys" style="margin-right:12px;">
                            <i class="viconfont vicon-a-shenheyuanqianzi"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_OPERATOR_SUS_SIGN']?></span></button>
                        <button class="addItem item_auditor_sign_sys" data-id="item_auditor_sign_sys">
                            <i class="viconfont vicon-a-shenheyuanqianzi"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_AUDITOR_SYS_SIGN']?></span></button>
                        <button class="addItem item_approval" data-id="item_approval" style="margin-right:12px;">
                            <i class="viconfont vicon-shenpiliu"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_APPROVAL']?></span></button>
                    </div>
                </div>
                <div class="item">
                    <div style="margin-bottom:12px;">
                        <span class="item-span"></span>
                        <label class="item-label"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_DIY']?></label>
                    </div>
                    <div class="item-gaiyao">
                        <button class="addItem" data-id="item_field" style="margin-right:12px;">
                            <i class="viconfont vicon-zidingyiziduan"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_DIY_FIELDS']?></span>
                        </button>
                        <button class="addItem" data-id="item_textarea">
                            <i class="viconfont vicon-wenbenkuang"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_DIY_TEXTAREA']?></span>
                        </button>
                        <button class="addItem" data-id="item_datetime" style="margin-right:12px;">
                            <i class="viconfont vicon-shijiankongjian"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_DIY_TIME']?></span></button>
                    </div>
                </div>
                <div class="item">
                    <div style="margin-bottom:12px;">
                        <span class="item-span"></span>
                        <label class="item-label"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_VERIFY']?></label>
                    </div>
                    <div class="item-gaiyao">
                        <!-- <button class="addItem2 item_network" data-id="item_network">
                             <i class="viconfont vicon-wangluoyanzheng"></i>
                             <span class="span">网络结果</span></button>
                         <button class="addItem2 item_ping" data-id="item_ping">
                             <i class="viconfont vicon-xintiaoyanzheng"></i>
                             <span class="span">心跳结果</span></button>-->
                        <button class="addItem2 item_open" data-id="item_open" style="margin-right:12px;">
                            <i class="viconfont vicon-kaijijieguo"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_OPEN']?></span></button>
                        <!--<button class="addItem2 item_virtus" data-id="item_virtus">
                            <i class="viconfont vicon-a-Frame1000002980"></i>
                            <span class="span">病毒查杀</span></button>-->
                        <button class="addItem2 item_integrality" data-id="item_integrality">
                            <i class="viconfont vicon-a-Frame1000003310"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_OPEN_RESULT']?></span></button>
                        <button class="addItem2 item_screen_verify" data-id="item_screen_verify" style="margin-right:12px;">
                            <i class="viconfont vicon-a-Screenshot-onejietu"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_SCREEN_RESULT']?></span></button>
                        <button class="addItem2 item_files" data-id="item_files">
                            <i class="viconfont vicon-a-Notesbiji"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_FILES_RESULT']?></span></button>
                        <button class="addItem2 item_document" data-id="item_document" style="margin-right:12px;">
                            <i class="viconfont vicon-a-Notesbiji"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_FILES_COMPARE']?></span></button>
                        <button class="addItem2 item_screen" data-id="item_screen">
                            <i class="viconfont vicon-jieping"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_SCREEN_COMPARE']?></span></button>
                        <button class="addItem2 item_module" data-id="item_module" style="margin-right:12px;">
                            <i class="viconfont vicon-zhujiming"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_HOST_NAME']?></span></button>
                        <button class="addItem2 item_point" data-id="item_point">
                            <i class="viconfont vicon-shengchengshijian"></i>
                            <span class="span"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_POINT_NAME']?></span></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-10 item-right-div">
            <div id="parent_container" class="hover-scroll-y" style="overflow-x: hidden;">
                <div id="container" class="container-industry-template">
                    <div class="report-title" style="margin-top:0;padding-top:24px;">
                        <input type="text" class="form-control input-inline input-title-ch" style="border: none;" id="report_title" value="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_TITLE']?>">
                        <span class="edit-title"><i class="viconfont vicon-a-Editbianji"></i></span>
                        <input type="text" class="form-control input-inline input-title-en item_en" style="border: none;" id="report_title2" value="Verification template 1">
                    </div>
                    <div class="title-line" style=" width: calc(100% - 40px);"></div>
                    <div class="basic-info basic-title1">
                        <?php echo $LANG['UI_USER_BASE_INFO']?>
                        <span class="item-line item_en"></span>
                        <span class="item_en span-title-basic">BASIC INFORMATION</span>
                    </div>
                    <div class="basic-title-line"></div>
                    <div class="basic-title-line" id="basic_info_line" style="top: 478px;padding-top: 20px;"></div>
                    <!--<div class="draggable-s item-items" id="item_verify_time" data-field="item_verify_time" style="line-height: 30px;width:360px;">
                        <span class="">
                            <i class="viconfont vicon-shengchengshijian" style="display:table-caption;line-height: 10px;margin-right: 4px;"></i>
                            <span class="make-time-ch"><?php /*echo $LANG['UI_VOL_CDP_GENERATE_TIME']*/?></span>
                            <span class="make-time-en item_en">Generation Time</span>
                            <span class="item-line"></span>
                            <span class="make-time-val"><?php /*echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_MAKE_TIME']*/?></span>
                        </span>
                        <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                    </div>-->
                    <div>
                        <!-- 初始元素 -->
                        <div class="draggable-s item-logo item-items" id="item_logo" data-field="item_logo">
                            <span class="margin-r--15">
                                <label class="upload-div" title="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_LOGO_TIPS']?>：120x50 px">
                                    <span class="upload-flag"><i class="viconfont vicon-shangchuan"></i><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_LOGO_UPLOAD']?></span>
                                    <input type="file" accept="image/*" name="files" id="diy_logo_value" style="display:none;">
                                    <input type="hidden" id="diy_logo_value_url">
                                </label>
                            </span>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>

                        <div class="draggable-s item-qrcode item-items" title="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_QRCODE_TIPS']?>" id="item_qrcode" data-field="item_qrcode">
                            <span class="margin-r--15"><img src="./img/platform/industry/qrcode.png"></span>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>

                        <div class="draggable item-1 item-items item-fields" id="item_sys_code" data-field="item_sys_code" style="left:0;top:302px;">
                            <i class="viconfont vicon-tuozhuai show-hover-icon display-none"></i>
                            <label class="item-label-title">
                                <span class="span-title">
                                    <span class="span-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_SYS_NUM']?></span><span class="span-en item_en">System Code</span>
                                 </span>
                                <input type="hidden" class="form-control input-inline input-ch" name="item_field_name" value="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_SYS_NUM']?>">
                                <input type="hidden" class="form-control input-inline item_en input-en" name="item_field_name_en" value="System Code">
                                <br>
                                <input type="text" name="item_field_value" readonly placeholder="生成报告时才能填写" class="form-control input-inline input input-sm input-val">
                            </label>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>

                        <div class="draggable item-1 item-items item-fields" id="item_sys_name" data-field="item_sys_name" style="left: calc(50% - 10px);top:302px;">
                            <i class="viconfont vicon-tuozhuai show-hover-icon display-none"></i>
                            <label class="item-label-title">
                                <span class="span-title">
                                    <span class="span-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_SYS_NAME']?></span><span class="span-en item_en">System Name</span>
                                </span>
                                <input type="hidden" class="form-control input-inline input-ch" name="item_field_name" value="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_SYS_NAME']?>">
                                <input type="hidden" class="form-control input-inline item_en input-en" name="item_field_name_en" value="System Name">
                                <br>
                                <input type="text" name="item_field_value" readonly placeholder="生成报告时才能填写" class="form-control input-inline input input-sm input-val">
                            </label>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>




                        <div class="draggable item-1 item-items item-fields" id="item_host" data-field="item_host" style="left:0;top:302px;">
                            <i class="viconfont vicon-tuozhuai show-hover-icon display-none"></i>
                            <label class="item-label-title">
                                <span class="span-title">
                                    <span class="span-ch"><?php echo $LANG['UI_AGENT_HOST_NAME']?></span><span class="span-en item_en">Host Name</span>
                                 </span>
                                <input type="hidden" class="form-control input-inline input-ch" name="item_field_name" value="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_HOST_NAME']?>">
                                <input type="hidden" class="form-control input-inline item_en input-en" name="item_field_name_en" value="Host Name">
                                <br>
                                <input type="text" name="item_field_value" readonly placeholder="系统自动填充" class="form-control input-inline input input-sm input-val">
                            </label>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>

                        <div class="draggable item-1 item-items item-fields" id="item_ip" data-field="item_ip" style="left: calc(50% - 10px);top:302px;">
                            <i class="viconfont vicon-tuozhuai show-hover-icon display-none"></i>
                            <label class="item-label-title">
                                <span class="span-title">
                                    <span class="span-ch"><?php echo $LANG['UI_PUBLIC_IP_ADDRESS']?></span><span class="span-en item_en">IP Address</span>
                                </span>
                                <input type="hidden" class="form-control input-inline input-ch" name="item_field_name" value="<?php echo $LANG['UI_PUBLIC_IP_ADDRESS']?>">
                                <input type="hidden" class="form-control input-inline item_en input-en" name="item_field_name_en" value="IP Address">
                                <br>
                                <input type="text" name="item_field_value" readonly placeholder="系统自动填充" class="form-control input-inline input input-sm input-val">
                            </label>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>

                        <div class="draggable item-1 item-items item-fields" id="item_operator" data-field="item_operator" style="left: 0;top:388px;">
                            <i class="viconfont vicon-tuozhuai show-hover-icon display-none"></i>
                            <label class="item-label-title">
                                <span class="span-title">
                                    <span class="span-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_OPERATOR']?></span><span class="span-en item_en">Operator</span>
                                </span>
                                <input type="hidden" class="form-control input-inline input-ch" name="item_field_name" value="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_OPERATOR']?>">
                                <input type="hidden" class="form-control input-inline item_en input-en" name="item_field_name_en" value="Operator">
                                <br>
                                <input type="text" name="item_field_value" readonly placeholder="系统自动填充" class="form-control input-inline input input-sm input-val">
                            </label>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>

                        <div class="draggable item-1 item-items item-fields" title="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_AUTO_MAKE_AFTER_VERIFY']?>" id="item_results" data-field="item_results" style="left: calc(50% - 10px);top:388px;">
                            <i class="viconfont vicon-tuozhuai show-hover-icon display-none"></i>
                            <label class="item-label-title">
                                <span class="span-title">
                                    <span class="span-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_VERIFY_RESULT']?></span><span class="span-en item_en">Final Result</span>
                                </span>
                                <input type="hidden" class="form-control input-inline input-ch" name="item_field_name" value="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_VERIFY_RESULT']?>">
                                <input type="hidden" class="form-control input-inline item_en input-en" name="item_field_name_en" value="Final Result">
                                <br>
                                <span class="span-val"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_RESULTS']?></span>
                            </label>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>

                    </div>

                    <div class="draggable item-1 item-plan item-block-width1 item-items" id="item_plan" data-field="item_plan">
                        <i class="viconfont vicon-tuozhuai display-none show-hover-icon"></i>
                        <div class="basic-info basic-title-plan">
                            <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_VERIFY_PLAN']?>
                            <span class="item-line item_en"></span>
                            <span class="item_en span-title-basic">VERFICATION PLAN</span>
                            <button class="btn b-btn item-plan-right" title="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_TOOL_TIPS']?>" type="button">
                                <i class="viconfont vicon-gongjuyincang"></i>
                            </button>
                            <select class="form-control" name="plan_uuid" id="plan_uuid">
                                <option value="0"> <?php echo $LANG['UI_PLATFORM_INDUSTRY_APPROVAL_SELECT']?></option>
                            </select>
                        </div>
                        <textarea id="item_plan_editor"></textarea>
                        <div class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></div>
                    </div>

                    <div class="draggable item-1 item-approval item-block-width1 item-items" id="item_approval" data-field="item_approval">
                        <i class="viconfont vicon-tuozhuai display-none show-hover-icon"></i>
                        <div class="basic-info basic-title-plan">
                            <?php echo $LANG['WEB_INDUSTRY_APPROVAL_LIST']?>
                            <span class="item-line item_en"></span>
                            <span class="item_en span-title-basic">APPROVAL PROCESS</span>
                            <select class="form-control" name="approval_uuid" id="approval_uuid">
                                <option value="0"> <?php echo $LANG['UI_PLATFORM_INDUSTRY_APPROVAL_SELECT']?></option>
                            </select>
                        </div>
                        <div class="img-items">
                            <img src="./img/platform/industry/approval.png">
                        </div>
                        <div class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></div>
                    </div>

                    <div class="draggable item-1 container2-industry-template" id="container2" data-field="item_description">
                        <!-- 这是详情的位置 -->
                        <div class="basic-info basic-title2">
                            <?php echo $LANG['UI_PLATFORM_INDUSTRY_VERIFY_INFO']?>
                            <span class="item-line item_en"></span>
                            <span class="item_en span-title-basic">VERIFICATION INFORMATION</span>
                        </div>

                        <div class="draggable2 item-2 item-items item-verify-items" id="item_module" data-field="item_module">
                            <i class="viconfont vicon-tuozhuai display-none show-hover-icon" style="left:0;top:16px;"></i>
                            <span class="item-verify-items-block">
                                <span class="item-verify-ch"> <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_HOST_NAME']?></span><span class="item_en item-verify-en">Host name</span>
                                <div class="item-verify-val">Host name</div>
                            </span>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>
                        <div class="draggable2 item-2 item-items item-verify-items" id="item_point" data-field="item_point">
                            <i class="viconfont vicon-tuozhuai display-none show-hover-icon" style="left:0;top:16px;"></i>
                             <span class="item-verify-items-block">
                                <span class="item-verify-ch"> <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_POINT_NAME']?></span><span class="item_en item-verify-en">Backup Time Point</span>
                                <div class="item-verify-val"> <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_MAKE_TIME']?></div>
                            </span>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>


                        <div class="draggable2 item-2 item-items item-verify2-items" id="item_integrality" data-field="item_integrality" style="width: 33%;left: 0;">
                            <i class="viconfont vicon-tuozhuai display-none show-hover-icon" style="left:0;top:20px"></i>
                            <span class="item-verify2-items-block">
                                <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_OPEN_RESULT']?></span>
                                <div class="item_en item-verify2-en">Boot Verification Result</div>
                                <div class="item-verify-val"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_RESULTS']?></div>
                            </span>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>
                        <div class="draggable2 item-2 item-items item-verify2-items" id="item_files" data-field="item_files" style="width: 34%;left: 33%;">
                            <i class="viconfont vicon-tuozhuai display-none show-hover-icon" style="left:0;top:20px"></i>
                            <span class="item-verify2-items-block">
                                <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_FILES_RESULT']?></span>
                                <div class="item_en item-verify2-en"> File Comparison Result</div>
                                <div class="item-verify-val"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_RESULTS']?></div>
                            </span>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>
                        <div class="draggable2 item-2 item-items item-verify2-items" id="item_screen_verify" data-field="item_screen_verify" style="width: 33%;left: 67%">
                            <i class="viconfont vicon-tuozhuai display-none show-hover-icon" style="left:0;top:20px"></i>
                            <span class="item-verify2-items-block">
                                <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_SCREEN_RESULT']?></span>
                                <div class="item_en item-verify2-en"> Screenshot Comparison Results</div>
                                <div class="item-verify-val"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_RESULTS']?></div>
                            </span>
                            <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                        </div>



                        <div class="draggable2 item-2 item-open item-block-width2 item-items" id="item_open" data-field="item_open">
                            <i class="viconfont vicon-tuozhuai display-none show-hover-icon"></i>
                            <div class="basic-info basic-title-plan">
                                <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_OPEN']?>
                                <span class="item-line item_en"></span>
                                <span class="item_en span-title-basic">BOOT VERIFICATION</span>
                            </div>
                            <div class="img-items">
                                <img src="./img/platform/industry/open.png">
                            </div>
                            <div class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></div>
                        </div>

                        <div class="draggable2 item-2 item-document item-block-width2 item-items" id="item_document" data-field="item_document">
                            <i class="viconfont vicon-tuozhuai display-none show-hover-icon"></i>
                            <div class="columns columns-right btn-group file-lie">
                                <div class="keep-open btn-group open">
                                    <button class="btn b-btn btn-title btn b-btn btn-title-default dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
                                        <i class="glyphicon icon-columns"></i>
                                    </button>
                                    <ul class="dropdown-menu file-lie-ul" role="menu">
                                        <li class="dropdown-item-marker" role="menuitem">
                                            <label>
                                                <input type="checkbox" id="file_size" checked data-id="file_size" data-checkbox="icheckbox_square-blue" class="icheck">
                                                <span class="file-item-title"><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_SIZE']?></span>
                                            </label>
                                        </li>
                                        <li class="dropdown-item-marker" role="menuitem">
                                            <label>
                                                <input type="checkbox" id="file_position" checked data-id="file_attr" data-checkbox="icheckbox_square-blue" class="icheck">
                                                <span class="file-item-title"><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_ATTR']?></span>
                                            </label>
                                        </li>
                                        <li class="dropdown-item-marker" role="menuitem">
                                            <label>
                                                <input type="checkbox" id="file_create" checked data-id="file_create" data-checkbox="icheckbox_square-blue" class="icheck">
                                                <span class="file-item-title"><?php echo $LANG['UI_PUBLIC_CREATE_TIME']?></span>
                                            </label>
                                        </li>
                                        <li class="dropdown-item-marker" role="menuitem">
                                            <label>
                                                <input type="checkbox" id="file_update" checked data-id="file_modify" data-checkbox="icheckbox_square-blue" class="icheck">
                                                <span class="file-item-title"><?php echo $LANG['UI_DATA_FILE_MODIFY_TIME']?></span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="basic-info basic-title-plan">
                                <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_FILES_COMPARE']?>
                                <span class="item-line item_en"></span>
                                <span class="item_en span-title-basic">FILE COMPARSION</span>
                            </div>
                            <table>
                                <thead>
                                    <tr>

                                        <th class="text-left">
                                            <span class="tr-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_TITLE']?></span>
                                            <span class="item_en tr-en">
                                                </br>Source files & backup files
                                            </span>
                                        </th>
                                        <th width="248" class="text-left verify_method">
                                            <span class="tr-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_METHODS']?></span>
                                            <span class="item_en tr-en">
                                                </br>MD5 Verification value
                                            </span>
                                        </th>
                                        <th style="width:8%;" class="text-left file_size">
                                            <span class="tr-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_SIZE']?></span>
                                            <span class="item_en tr-en">
                                                </br>Files Size
                                            </span>
                                        </th>
                                        <th width="60" class="text-left file_attr">
                                            <span class="tr-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_ATTR']?></span>
                                            <span class="item_en tr-en">
                                                </br>Permission
                                            </span>
                                        </th>
                                        <th width="102" class="text-left file_create">
                                            <span class="tr-ch"><?php echo $LANG['UI_PUBLIC_CREATE_TIME']?></span>
                                            <span class="item_en tr-en">
                                                </br>Create Time
                                            </span>
                                        </th>
                                        <th width="102" class="text-left file_modify">
                                            <span class="tr-ch"><?php echo $LANG['UI_DATA_FILE_MODIFY_TIME']?></span>
                                            <span class="item_en tr-en">
                                                </br>Modify Time
                                            </span>
                                        </th>
                                        <th width="100" class="text-left">
                                            <span class="tr-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_RESULTS']?></span>
                                            <span class="item_en tr-en">
                                                </br>Result
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="file_result_ok">
                                        <td class="text-left">
                                            <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_PRODUCT_PATH']?></span>
                                            <br>
                                            <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_VERIFY_PATH']?></span>
                                        </td>
                                        <td class="text-left">
                                            <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_PRODUCT_VALUE']?></span>
                                            <br>
                                            <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_VERIFY_VALUE']?></span>
                                        </td>
                                        <td class="text-left file_size">
                                            <span>10MB</span>
                                            <br>
                                            <span class="">10MB</span>
                                        </td>
                                        <td class="text-left file_attr">
                                            <span>--<span class="item_en">(-)</span></span>
                                            <br>
                                            <span>--<span class="item_en">(-)</span></span>
                                        </td>
                                        <td class="text-left file_create">
                                            <span>xxxx/xx/xx xx:xx:xx</span>
                                            <br>
                                            <span>xxxx/xx/xx xx:xx:xx</span>
                                        </td>
                                        <td class="text-left file_modify">
                                            <span>xxxx/xx/xx xx:xx:xx</span>
                                            <br>
                                            <span>xxxx/xx/xx xx:xx:xx</span>
                                        </td>
                                        <td class="text-left">
                                            <i class="viconfont vicon-tongguo"></i>
                                            <?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_RESULT_SAME']?>
                                        </td>
                                    </tr>
                                    <tr class="file_result_fail">
                                        <td class="text-left">
                                            <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_PRODUCT_PATH']?></span>
                                            <br>
                                            <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_VERIFY_PATH']?></span>
                                        </td>
                                        <td class="text-left">
                                            <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_PRODUCT_VALUE']?></span>
                                            <br>
                                            <span><?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_VERIFY_VALUE']?></span>
                                        </td>
                                        <td class="text-left file_size">
                                            <span>10MB</span>
                                            <br>
                                            <span class="">10MB</span>
                                        </td>
                                        <td class="text-left file_attr">
                                            <span>--<span class="item_en">(-)</span></span>
                                            <br>
                                            <span>--<span class="item_en">(-)</span></span>
                                        </td>
                                        <td class="text-left file_create">
                                            <span>xxxx/xx/xx xx:xx:xx</span>
                                            <br>
                                            <span>xxxx/xx/xx xx:xx:xx</span>
                                        </td>
                                        <td class="text-left file_modify">
                                            <span>xxxx/xx/xx xx:xx:xx</span>
                                            <br>
                                            <span>xxxx/xx/xx xx:xx:xx</span>
                                        </td>
                                        <td class="text-td">
                                            <i class="viconfont vicon-error-warning-fill"></i>
                                            <?php echo $LANG['UI_PLATFORM_INDUSTRY_FILE_RESULT_NOTSAME']?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></div>
                        </div>

                        <div class="draggable2 item-2 item-screen item-block-width2 item-items" id="item_screen" data-field="item_screen">
                            <i class="viconfont vicon-tuozhuai display-none show-hover-icon"></i>
                            <div class="basic-info basic-title-plan">
                                <?php echo $LANG['UI_PLATFORM_INDUSTRY_SCREEN_COMPARE']?>
                                <span class="item-line item_en"></span>
                                <span class="item_en span-title-basic">SCREENSHOT COMPARSION</span>
                            </div>
                            <div class="img-items">
                                <img src="./img/platform/industry/screen.png">
                            </div>
                            <div class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></div>
                        </div>

                    </div>

                    <div class="draggable item-1 item-result item-block-width1 item-items" id="item_result" data-field="item_result">
                        <i class="viconfont vicon-tuozhuai display-none show-hover-icon"></i>
                        <div class="basic-info basic-title-plan">
                            <?php echo $LANG['UI_PLATFORM_INDUSTRY_VERIFY_RESULT']?>
                            <span class="item-line item_en"></span>
                            <span class="item_en span-title-basic">VERFICATION CONCLUSION</span>
                            <button class="btn b-btn item-plan-right" title=" <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_TOOL_TIPS']?>" type="button">
                                <i class="viconfont vicon-gongjuyincang"></i>
                            </button>
                        </div>
                        <textarea id="item_result_editor"><?php echo $LANG['UI_PLATFORM_INDUSTRY_VERIFY_RESULT_TIPS']?></textarea>
                        <div class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></div>
                    </div>

                    <div class="draggable item-1 item-items item-make_time item-bottom-items" id="item_make_time" data-field="item_make_time">
                        <i class="viconfont vicon-tuozhuai display-none show-hover-icon del-bottom-items"></i>
                        <div class="margin-r-10">
                            <span class="item-verify-bottom-ch"> <?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_MAKE_TIME']?>：</span>
                            <span class="item-verify-bottom-val"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_MAKE_TIME']?></span>
                            <div class="item_en item-verify-bottom-en">Report Generation Time</div>
                        </div>
                        <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                    </div>
                    <div class="draggable item-1 item-download_time item-items item-bottom-items" id="item_download_time" data-field="item_download_time">
                        <i class="viconfont vicon-tuozhuai display-none show-hover-icon del-bottom-items"></i>
                        <div class="margin-r-10">
                            <span class="item-verify-bottom-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_DOWNLOAD_TIME']?>：</span>
                            <span class="item-verify-bottom-val"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_MAKE_TIME']?></span>
                            <div class="item_en item-verify-bottom-en">Report Download Time</div>
                        </div>
                        <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                    </div>
                    <div class="draggable item-1 item-operator_sign item-items item-bottom-items" id="item_operator_sign" data-field="item_operator_sign">
                        <i class="viconfont vicon-tuozhuai display-none show-hover-icon del-bottom-items"></i>
                        <div class="margin-r-10">
                            <span class="item-verify-bottom-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_OPERATOR_SIGN2']?>：</span>
                            <span class="item-verify-bottom-val"></span>
                            <div class="item_en item-verify-bottom-en">Operator(Signature)</div>
                        </div>
                        <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                    </div>
                    <div class="draggable item-1 item-auditor_sign item-items item-bottom-items" id="item_auditor_sign" data-field="item_auditor_sign">
                        <i class="viconfont vicon-tuozhuai display-none show-hover-icon del-bottom-items"></i>
                        <div class="margin-r-10">
                            <span class="item-verify-bottom-ch"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_AUDITOR_SIGN2']?>：</span>
                            <span class="item-verify-bottom-val"></span>
                            <div class="item_en item-verify-bottom-en">Auditor(Signature)</div>
                        </div>
                        <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                    </div>

                </div>
            </div>
            <div class="template-bottom">
                <button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                <button type="button" id="addItems" class="btn green-haze btn-confirm">
                    <?php echo $LANG['UI_PUBLIC_NEXT_STEP']?>
                    <i class="viconfont vicon-xiayibu" style="margin-left:4px;"></i>
                </button>
                <button type="button" id="chooseItems" class="btn green-haze btn-confirm display-none"> <?php echo $LANG['UI_PUBLIC_YES']?></button>
            </div>
        </div>
    </div>
</div>

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
<script type="text/javascript" src="./assets/global/plugins/jquery-ui/jquery-ui.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="./scripts/platform/industry/template_config.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
