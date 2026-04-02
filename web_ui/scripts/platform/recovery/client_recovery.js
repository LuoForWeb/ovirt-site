var clientRecoverConfig = function () {
    var point; // 初始化声明时间点
    var jobType = 1;// 任务类型 默认是跨平台恢复 2瞬时恢复 3迁移
    var taskUuid = ''; // 瞬时恢复的任务uuid
    var pointType = 1; // 时间点来源类型 默认无代理 2操作系统（有代理）,3实时
    var driverCheck = []; // 驱动检测结果存储
    var passCheck = []; // 密码验证结果存储
    var isPlatformType = []; // 判断是否异构平台并且未驱动检测通过
    var isSupportOs = []; // 判断是否不支持替换驱动
    // 主机网卡信息 cdp用
    let source_netcard_conf = [];
    // 目标机网卡信息 cdp用
    let target_netcard_conf = [];

    //初始化目标机配置内容dom元素
    var initTargetDom = function(timepointInfo, iplist){
        var ul,dul;
        if (jobType == 2) {
            // 瞬时恢复
            ul = `
                <li  class="active">
                    <a href="#${timepointInfo.timepoint_uuid}_tab_1_2" data-toggle="tab" aria-expanded="true">
                        <i class="viconfont vicon-a-zancunbaocun"></i> ${LANG.UI_PLATFORM_RECOVERY_TARGET_CONFIG} </a>
                </li>
                <li>
                    <a href="#${timepointInfo.timepoint_uuid}_tab_1_3" data-toggle="tab" aria-expanded="false">
                        <i class="viconfont vicon-ziyuanxiangqing"></i> ${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}</a>
                </li>
                `;
            dul = `
                    <div class="tab-pane active" id="${timepointInfo.timepoint_uuid}_tab_1_2">
                        <div class="portlet-body">
                            <div id="${timepointInfo.timepoint_uuid}_recover_target_id"></div>
                        </div>
                    </div>
                    <div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_1_3">
                        <div class="portlet-body">
                            <div class="row" style="margin: 0;">
                                <div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">
                                    <ul class="nav nav-tabs tabs-left">
                                        <li class="active"><a href="#${timepointInfo.timepoint_uuid}_tab_0_1" data-toggle="tab" aria-expanded="true">${LANG.UI_PLATFORM_RECOVERY_SCRIPTS_BEFORE}</a></li>
                                        <li class=""><a href="#${timepointInfo.timepoint_uuid}_tab_0_2" data-toggle="tab" aria-expanded="true">${LANG.UI_PLATFORM_RECOVERY_SCRIPTS_AFTER}</a></li>
                                        <li class=""><a href="#${timepointInfo.timepoint_uuid}_tab_0_3" data-toggle="tab" aria-expanded="true">${LANG.UI_PLATFORM_RECOVERY_SCRIPTS_STOP_BEFORE}</a></li>
                                        <li class=""><a href="#${timepointInfo.timepoint_uuid}_tab_0_4" data-toggle="tab" aria-expanded="true">${LANG.UI_PLATFORM_RECOVERY_SCRIPTS_STOP_AFTER}</a></li>
                                    </ul>
                                </div>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="${timepointInfo.timepoint_uuid}_tab_0_1" data-num="0">
                                            <div class="${timepointInfo.timepoint_uuid}_script_recover_before"></div>
                                        </div>
                                        <div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_0_2" data-num="0">
                                            <div class="${timepointInfo.timepoint_uuid}_script_recover_after"></div>
                                        </div>
                                        <div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_0_3" data-num="0">
                                            <div class="${timepointInfo.timepoint_uuid}_script_stop_before"></div>
                                        </div>
                                        <div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_0_4" data-num="0">
                                            <div class="${timepointInfo.timepoint_uuid}_script_stop_after"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
        } else if (jobType == 3){
            // 迁移任务
            ul = `
                <li class="active">
                    <a href="#${timepointInfo.timepoint_uuid}_tab_1_1" data-toggle="tab" aria-expanded="true">
                        <i class="viconfont vicon-a-zancunbaocun"></i> ${LANG.UI_PLATFORM_RECOVERY_TARGET_CONFIG} </a>
                </li>
            <li>
                    <a href="#${timepointInfo.timepoint_uuid}_tab_1_2" data-toggle="tab" aria-expanded="false">
                        <i class="viconfont vicon-wangluo"></i> ${LANG.UI_VOL_CDP_SETTING_NETWORK} </a>
                </li>
                    <li>
                    <a href="#${timepointInfo.timepoint_uuid}_tab_1_3" data-toggle="tab" aria-expanded="false">
                        <i class="viconfont vicon-ziyuanxiangqing"></i> ${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}</a>
                </li> `;
            dul = `<div class="tab-pane active" id="${timepointInfo.timepoint_uuid}_tab_1_1">
                        <div class="portlet-body">
                            <div id="${timepointInfo.timepoint_uuid}_recover_target_id"></div>
                        </div>
                    </div>
                   <div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_1_2">
                        <div class="portlet-body">
                            <div id="${timepointInfo.timepoint_uuid}_ipconfig_id"></div>
                        </div>
                    </div>
                   <div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_1_3">
                        <div class="portlet-body">
                            <div class="row" style="margin: 0;">
                                <div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">
                                    <ul class="nav nav-tabs tabs-left">
                                        <li class="active"><a href="#${timepointInfo.timepoint_uuid}_tab_0_1" data-toggle="tab" aria-expanded="true">${LANG.UI_PLATFORM_RECOVERY_SCRIPT_RECOVERY}</a></li>
                                    </ul>
                                </div>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="${timepointInfo.timepoint_uuid}_tab_0_1" data-num="0">
                                            <div class="${timepointInfo.timepoint_uuid}_script_recover_after"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> `;
        } else {
            var active = 'active';
            var uls = '',duls = '';
            // 跨平台恢复
            ul = uls + `<li class="${active}">
                        <a href="#${timepointInfo.timepoint_uuid}_tab_1_1" data-toggle="tab" aria-expanded="true">
                            <i class="viconfont vicon-a-zancunbaocun"></i> ${LANG.UI_PLATFORM_RECOVERY_TARGET_CONFIG} </a>
                    </li>
                    <li>
                        <a href="#${timepointInfo.timepoint_uuid}_tab_1_2" data-toggle="tab" aria-expanded="false">
                            <i class="viconfont vicon-wangluo"></i> ${LANG.UI_VM_SETTING_V2_NETWORK} </a>
                    </li>
                    <li>
                        <a href="#${timepointInfo.timepoint_uuid}_tab_1_3" data-toggle="tab" aria-expanded="false">
                            <i class="viconfont vicon-gaojipeizhi"></i> ${LANG.UI_PUBLIC_ADVANCED_CONFIG} </a>
                    </li>
                    <li>
                        <a href="#${timepointInfo.timepoint_uuid}_tab_1_4" data-toggle="tab" aria-expanded="false">
                            <i class="viconfont vicon-ziyuanxiangqing"></i> ${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}</a>
                    </li>`;
            dul = duls + `<div class="tab-pane ${active}" id="${timepointInfo.timepoint_uuid}_tab_1_1">
                        <div class="portlet-body">
                            <div id="${timepointInfo.timepoint_uuid}_recover_target_id"></div>
                        </div>
                    </div>

                    <div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_1_2">
                        <div class="portlet-body">
                            <div id="${timepointInfo.timepoint_uuid}_ipconfig_id"></div>
                        </div>
                    </div>

                    <div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_1_3">
                        <div class="portlet-body">
                            <div class="form-group">
                                <label class="control-label col-md-3">${LANG.UI_VM_SETTING_V2_BOOT_TYPE}
                                </label>
                                <div class="col-md-6">
                                    <select class="form-control" disabled id="${timepointInfo.timepoint_uuid}_boot_mode_id">
                                        <option value="1" selected>BIOS</option>
                                        <option value="2">EFI</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 form-group-label">${LANG.UI_VM_RESET_HOST_NAME}</label>
                                <div class="col-md-3 form-group-content">
                                    <input type="checkbox" id="${timepointInfo.timepoint_uuid}_rename_check" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                </div>
                            </div>
                            <div class="form-group reconnect-times-wrapper display-none">
                                <label class="control-label col-md-3 reconnect-times-Label">${LANG.UI_CLIENT_HOST_NAME}</label>
                                <div class="col-md-6">
                                    <input type="text" id="${timepointInfo.timepoint_uuid}_rename_id" class="form-control input-sm" maxlength="64">
                                </div>
                            </div>
                         
                            <div class="form-group transfernetworkDiv">
                                <label class="control-label col-md-3 transfernetworklabel">${LANG.UI_NODE_NETWORK_TRANSFER}</label>
                                    <div class="col-md-6 form-group-content flex-items-center">
                                        <ul class="ztree" id="${timepointInfo.timepoint_uuid}_transferNetworkTree"></ul>
                                        <span class="ms-8">
                                             <i class="viconfont vicon-tishi popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="${LANG.UI_PLATFORM_RECOVERY_NETWORK_CONNECT}"></i>
                                        </span>
                                    </div>
                           
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_1_4">
                        <div class="portlet-body">
                            <div class="row" style="margin: 0;">
                                <div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">
                                    <ul class="nav nav-tabs tabs-left">
                                        <li class="active"><a href="#${timepointInfo.timepoint_uuid}_tab_0_1" data-toggle="tab" aria-expanded="true">${LANG.UI_PLATFORM_RECOVERY_SCRIPTS_AFTER}</a></li>
                                        
                                    </ul>
                                </div>
                                <div class="col-md-9 col-sm-9 col-xs-9">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="${timepointInfo.timepoint_uuid}_tab_0_1" data-num="0">
                                            <div class="${timepointInfo.timepoint_uuid}_script_recover_after"></div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
        }

        var DomHtml = `<div class="panel panel-default panel-file" id="${timepointInfo.timepoint_uuid}_target_panel_id">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a class="accordion-toggle accordion-toggle-styled popovers" data-toggle="collapse" data-parent=".targetBox" href="#${timepointInfo.timepoint_uuid}_target_collapse_id" aria-expanded="true" aria-controls="${timepointInfo.timepoint_uuid}_target_collapse_id">
										<span class="font-green-seagreen">${timepointInfo.host_name}</span>
										<span class="strategyDes storeDes">${LANG.UI_RECOVERY_TIMEPOINT}: ${timepointInfo.timepoint_name}</span>
									</a>
								</h4>
							</div>
							<div id="${timepointInfo.timepoint_uuid}_target_collapse_id" class="panel-collapse collapse in" aria-labelledby="headingOne" role="tabpanel" aria-expanded="false">
								<div class="panel-body" style="padding:16px">
								
                                    <div class="form-group show_job_type1_start display-none">
                                        <label class="control-label col-md-3_en" style="float: left;width:155px;">
                                            <span class="required">* </span>
                                            `+ LANG.UI_MACHINE_OS_BOOT_MEDIA +`
                                            </label>
                                        <div class="col-md-4 col-md-6_en form-group-content flex-items-center">
                                            <div class="radio-container">
                                                <input type="radio" name="${timepointInfo.timepoint_uuid}_boot_media" value="1" id="${timepointInfo.timepoint_uuid}_boot_media_manual" checked>
                                                <label for="${timepointInfo.timepoint_uuid}_boot_media_manual" style="margin-right:16px;">${LANG.UI_MACHINE_OS_MANUAL_DEPLOY}</label>
                                                <input type="radio" name="${timepointInfo.timepoint_uuid}_boot_media" value="2" id="${timepointInfo.timepoint_uuid}_boot_media_auto">
                                                <label for="${timepointInfo.timepoint_uuid}_boot_media_auto">${LANG.UI_MACHINE_OS_AUTO_DEPLOY}</label>
                                            </div>
                                            <span class="ms-8">
                                             <i class="viconfont vicon-tishi popovers"  data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="${LANG.UI_MACHINE_OS_DEPLOY_DES}"></i>
                                        </span>
                                        </div>
                                       
                                    </div>
                                    
									<div class="form-group">
										<label class="control-label col-md-3_en" style="float: left;width:155px;">
											<span class="required">* </span>
											${LANG.UI_MACHINE_OS_RECOVERY_TARGET}
										</label>
										<div class="col-md-6 select-special">
										<select class="form-control select2me ipselect" id="${timepointInfo.timepoint_uuid}_ipselect">
											<option value="0">${LANG.UI_JOB_SELECT}</option>
										</select>
										</div>
										<button class="btn btn-light-primary me-2 linkChecked" status="false" status_agent_uuid="" type="button" id="${timepointInfo.timepoint_uuid}_linkChecked">${LANG.UI_DRIVER_CHECK_BTN}</button>
									</div>
							
									<div class="form-group">
											<div id="${timepointInfo.timepoint_uuid}_driverCheck"></div>
									</div>
									<div id="driverCheckFailContinueOs_${timepointInfo.timepoint_uuid}" class="display-none">
                                        <div class="form-group">
                                            <div class="col-md-12 driver-config__content">
                                                <label style="float:left;line-height:32px;margin-right:12px;">${LANG.UI_PLATFORM_RECOVERY_DRIVER_NO_CONTINUE_TIPS}：</label>
                                                <div class="input-group " id="continue_driver_fail" style="margin-top: 4px;float: left;">
                                                    <label>
                                                        <input type="checkbox" id="continueCheckOs_${timepointInfo.timepoint_uuid}" style="margin-right:5px;" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck">${LANG.UI_PUBLIC_YES}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<div class="form-group display-none" id="${timepointInfo.timepoint_uuid}_sourceConfigDiv" style="margin-bottom: 10px;">
										<div class="portlet-body">
											<div class="tabbable tabbable-custom">
												<ul class="nav nav-tabs">
													${ul}
												</ul>
												<div class="tab-content" style="padding: 12px;">
													${dul}
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>`;


        $(".targetBox").append(DomHtml);


        $("#"+timepointInfo.timepoint_uuid+"_rename_check").bootstrapSwitch('onSwitchChange', function (e, data) {
            if(data){
                //开
                $("#"+timepointInfo.timepoint_uuid+"_rename_id").parent().parent().show();
            }else{
                //关
                $("#"+timepointInfo.timepoint_uuid+"_rename_id").parent().parent().hide();
            }
        });

        if (jobType == 1) {
            // 处理下引导模式的值
            var system_boot_type = timepointInfo.system_boot_type == undefined ? 1 : timepointInfo.system_boot_type;
            $('#'+timepointInfo.timepoint_uuid+'_boot_mode_id').val(system_boot_type);
        }


        if (jobType == 1 && pointType != 3) {
            // 跨平台，并且不是实时的点
            $('.show_job_type1_start').show();
            //初始化单选框
            $('input[name="'+timepointInfo.timepoint_uuid+'_boot_media"]').iCheck({
                radioClass: 'iradio_square-blue'
            });

            var changeSelectFunc = function(showType){
                //清空
                $("#"+timepointInfo.timepoint_uuid+"_ipselect").empty().off();
                let ipOption = `<option value="">`+ LANG.UI_MACHINE_OS_PLEASE_SELECT +`</option>`
                for (let i = 0; i < iplist.length; i++) {
                    let ipInfo = iplist[i];
                    //是否是离线状态
                    let online_flag = ipInfo.online_flag;
                    //是否是在任务中状态
                    let in_task = ipInfo.in_task;
                    //回去任务中名字
                    let task_name = ipInfo.task_name;
                    if(ipInfo.agent_type == showType){
                        //如果是离线状态不可选
                        ipOption += `<option value_task_name="${task_name}" agent_type="${ipInfo.agent_type}" os_type="${ipInfo.os_type}" net_model="${ipInfo.net_model}" value_online="${online_flag}" value_in_task="${in_task}" value="${ipInfo.agent_uuid}">${ipInfo.agent_name}</option>`;
                    }
                }
                $("#"+timepointInfo.timepoint_uuid+"_ipselect").append(ipOption);
                $("#"+timepointInfo.timepoint_uuid+"_ipselect").select2({
                    placeholder: LANG.UI_JOB_SELECT,
                    language: {
                        noResults: function() {
                            return LANG.UI_RECOVERY_CLIENT_SERARCH_TIPS; // 自定义提示文本
                        },
                        // 搜索框的提示文字（新增这个配置）
                        searching: function() {
                            return LANG.UI_MICROSOFT365_INPUT_KEYWORD_TIPS; // 您的自定义提示文本
                        }
                    }
                    //allowClear: true // 允许清空选择
                }).on('select2:open', function() {
                    // 使用 this 找到当前 select2 实例对应的 dropdown
                    const $dropdown = $(this).data('select2').$dropdown;
                    // 定位到当前下拉中的搜索框
                    const $searchField = $dropdown.find('.select2-search__field');

                    // 获取搜索框并设置placeholder
                    $searchField.attr('placeholder', LANG.UI_MICROSOFT365_INPUT_KEYWORD_TIPS);

                    // 延迟聚焦（等待 DOM 更新）
                    setTimeout(function () {
                        if ($searchField.length) {
                            const nativeInput = $searchField[0]; // 获取原生 DOM 元素
                            nativeInput.focus();
                        }
                    }, 200); // 可根据需要调整延迟时间
                });

                //切换后需要重新检测
                $("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr('status','false');

                //选择恢复目的后初始化选择事件
                //监听所有class为ipselect的change事件
                $("#"+timepointInfo.timepoint_uuid+"_ipselect").on('change', () => {
                    //选择后改变检测状态
                    //获取恢复目的地的值
                    let ipselectValue = $("#"+timepointInfo.timepoint_uuid+"_ipselect").val();

                    //获取离线状态
                    let online_flag = $("#"+timepointInfo.timepoint_uuid+"_ipselect").find("option:selected").attr('value_online');
                    //获取任务中状态
                    let in_task = $("#"+timepointInfo.timepoint_uuid+"_ipselect").find("option:selected").attr('value_in_task');
                    //获取任务名称
                    let task_name = $("#"+timepointInfo.timepoint_uuid+"_ipselect").find("option:selected").attr('value_task_name');
                    let is_self = $("#"+timepointInfo.timepoint_uuid+"_ipselect").find("option:selected").attr('is_self');
                    var check = true;
                    //如果是离线状态或任务中则禁止选择并给出提示(注意该值非布尔类型)
                    if(online_flag == "false"){
                        UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET, LANG.UI_MACHINE_OS_CANNOT_SELECT_OFFLINE_HOST);
                        check = false;
                    }
                    if(check && in_task == "true"){
                        UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET, LANG.UI_MACHINE_OS_CANNOT_SELECT_HOST_IN_TASK+"'"+task_name+"'"+LANG.UI_MACHINE_OS_TASK);
                        check = false;
                    }

                    if (check && is_self == 1) {
                        UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET, LANG.UI_RECOVERY_SOURCE_HOST_TIPS);
                        check = false;
                    }

                    if (check && (ipselectValue == '' || ipselectValue == 0)) {
                        check = false;
                    }

                    // 清空属性
                    $("#"+timepointInfo.timepoint_uuid+"_sourceConfigDiv").hide();
                    $("#"+timepointInfo.timepoint_uuid+"_recover_target_id").html("");
                    $("#"+timepointInfo.timepoint_uuid+"_driverCheck").html("");
                    $("#driverCheckFailContinueOs_"+timepointInfo.timepoint_uuid).hide();
                    //获取检测里面的属性status_agent_uuid的值
                    $("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr("status_agent_uuid");
                    $("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr("status","false");
                    //当切换选项后就取消勾选i标签
                    $("#"+timepointInfo.timepoint_uuid+"_linkChecked").find("i").remove();

                    if (!check) {
                        //取消选择
                        $("#"+timepointInfo.timepoint_uuid+"_ipselect").select2("val", "0");
                        return;
                    }

                    //获取检测里面的属性status_agent_uuid的值
                    let statusAgentUuid = $("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr("status_agent_uuid");

                    //当ipselect事件改变后获取所有.ipselect选中的ip然后禁用
                    // 获取所有 class 为 ipselect 的选择框
                    var ipSelects = $('.ipselect');
                    var selectValueList = [];

                    // 遍历每个选择框,使用for的方式
                    for (var i = 0; i < ipSelects.length; i++) {
                        // 获取当前选择框的值
                        var selectValue = $(ipSelects[i]).val();
                        // 将值添加到数组中
                        selectValueList.push(selectValue);
                    }
                    //遍历所有.ipselect选择框
                    ipSelects.each(function(){
                        //获取当前选中的值
                        var selectValue = $(this).val();
                        //获取排除当前选中值的所有option对象集合
                        var optionList = $(this).find('option').not($(this).find('option[value="'+selectValue+'"]'));
                        //遍历optionList
                        optionList.each(function(){
                            //获取当前option的值
                            var optionValue = $(this).val();
                            if(selectValueList.includes(optionValue) && optionValue != ""){
                                $(this).attr('disabled',true);
                            }else{
                                $(this).attr('disabled',false);
                            }
                        })
                    })
                })

                //初始化poppvers
                $(".targetBox").find('.popovers').popover();
                //初始化checkbox
                $('.make-switch').bootstrapSwitch();
            }

            // 添加切换事件
            $('input[name="'+timepointInfo.timepoint_uuid+'_boot_media"]').on('ifChecked ', function(event) {
                // 获取当前选中的单选框的值
                var selectedValue = $(this).val();
                if(selectedValue ==2){
                    //切换自动部署的时候需要输入密码
                    bootbox.prompt({
                        title: '<span style="color:red;">'+LANG.UI_MACHINE_OS_AUTO_DEPLOY_WARN_TIP_1+', <strong>'+LANG.UI_MACHINE_OS_AUTO_DEPLOY_WARN_TIP_2+'</strong>, '+LANG.UI_MACHINE_OS_AUTO_DEPLOY_WARN_TIP_3+'. </span>'+LANG.UI_MACHINE_OS_AUTO_DEPLOY_WARN_TIP_4,
                        inputType: 'password',
                        callback: debounce(function (pwdResult) {
                            if(pwdResult == ""){
                                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM);
                                return false;
                            }
                            if(pwdResult == null) {
                                // 如果用户取消输入密码，恢复之前选中的单选按钮状态
                                $('input[name="' + timepointInfo.timepoint_uuid + '_boot_media"][value="' + 1 + '"]').iCheck('check');
                                changeSelectFunc(2)
                                return;
                            };
                            //获取密码
                            let password = hex_md5(pwdResult);
                            let requestList = {};
                            let getPassword = "";
                            //获取密码是否正确
                            var requestData  = function(data){
                                if(data.success){
                                    getPassword = data.data.password;
                                }
                            }
                            //初始化备份源
                            pAjaxRequest(requestList, '/api/v1/users/password', "GET", requestData, false);
                            if(getPassword == password){
                                UIToastr.showSuccess(LANG.UI_PUBLIC_TIPS, LANG.UI_MACHINE_OS_PASSWORD_VERIFY_SUCCESS);
                                changeSelectFunc(1)
                                $(this).modal('hide');
                                return true;
                            }else{
                                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_MACHINE_OS_PASSWORD_VERIFY_FAILED2);
                                changeSelectFunc(2)
                                return false;
                            }
                        },300,false)
                    });
                }else{
                    changeSelectFunc(2)
                }
            });
        }

        // 如果是瞬时恢复任务，需要判断时间点的操作系统类型，
        var os_type = '';
        if (jobType == 2) {
            if (timepointInfo.os_type != undefined) {
                var osType = parseInt(timepointInfo.os_type);
                if (osType == 2) {
                    os_type = 'Linux';
                } else if (osType == 1 || osType == 3) {
                    os_type = 'Windows';
                } else {
                    os_type = getPointOstype(timepointInfo.timepoint_uuid);
                }
            } else {
                // 请求后台接口获取操作系统类型
                os_type = getPointOstype(timepointInfo.timepoint_uuid);
            }
            if (os_type == '') {
                // 给个默认的，不然页面都不显示了
                os_type = 'Linux';
            }
        }

        //初始化恢复目的地
        //循环iplist的数据添加option
        for (let i = 0; i < iplist.length; i++) {
            let ipInfo = iplist[i];
            // 根据任务类型来控制哪些显示，并且根据 agent_type 来控制是否需要驱动检测
            // 暂时全部取消 测试
            if (jobType == 1 && !(ipInfo.agent_type == 2)) {
                // 跨平台恢复只能到引导镜像、默认手动部署
                continue;
            }
            if (jobType == 2 && !(ipInfo.agent_type == 1)) {
                // 瞬时恢复到任意在线客户端
                continue;
            }

            // 迁移可以到客户端和livecd，
            if (jobType == 3 && timepointInfo.is_sys == false && !(ipInfo.agent_type == 1)) {
                // 如果瞬时恢复的点不包含系统盘，那么只能迁移到客户端
                continue;
            }

            if (jobType == 2 && os_type != ipInfo.os_type) {
                // 瞬时恢复只能到同操作系统
                continue;
            }
            //是否是离线状态
            let online_flag = ipInfo.online_flag;
            //是否是在任务中状态
            let in_task = ipInfo.in_task;
            //回去任务中名字
            let task_name = ipInfo.task_name;
            let is_self = 2;
            let self = '';
            if (jobType == 2 && pointType == 2 && timepointInfo.agent_uuid == ipInfo.agent_uuid) {
                // 瞬时恢复增加原主机标识
                is_self = 1;
                self = '(' + LANG.UI_RECOVERY_SOURCE_HOST_NAME + ')';
            }

            let ipOption = `<option value="${ipInfo.agent_uuid}" value_task_name="${task_name}" value_online="${online_flag}"
                            value_in_task="${in_task}" agent_type="${ipInfo.agent_type}" os_type="${ipInfo.os_type}"
                             net_model="${ipInfo.net_model}" is_self="${is_self}">${ipInfo.agent_name}${self}</option>`;
            $("#"+timepointInfo.timepoint_uuid+"_ipselect").append(ipOption);
        }
        $("#"+timepointInfo.timepoint_uuid+"_ipselect").select2({
            placeholder: LANG.UI_JOB_SELECT,
            language: {
                noResults: function() {
                    return LANG.UI_RECOVERY_CLIENT_SERARCH_TIPS; // 自定义提示文本
                },
                // 搜索框的提示文字（新增这个配置）
                searching: function() {
                    return LANG.UI_MICROSOFT365_INPUT_KEYWORD_TIPS; // 您的自定义提示文本
                }
            }
            //allowClear: true // 允许清空选择
        }).on('select2:open', function() {
            // 使用 this 找到当前 select2 实例对应的 dropdown
            const $dropdown = $(this).data('select2').$dropdown;
            // 定位到当前下拉中的搜索框
            const $searchField = $dropdown.find('.select2-search__field');

            // 获取搜索框并设置placeholder
            $searchField.attr('placeholder', LANG.UI_MICROSOFT365_INPUT_KEYWORD_TIPS);

            // 延迟聚焦（等待 DOM 更新）
            setTimeout(function () {
                if ($searchField.length) {
                    const nativeInput = $searchField[0]; // 获取原生 DOM 元素
                    nativeInput.focus();
                }
            }, 200); // 可根据需要调整延迟时间
        });

        //选择恢复目的后初始化选择事件
        //监听所有class为ipselect的change事件
        $("#"+timepointInfo.timepoint_uuid+"_ipselect").on('change', () => {
            //选择后改变检测状态
            //获取恢复目的地的值
            let ipselectValue = $("#"+timepointInfo.timepoint_uuid+"_ipselect").val();

            //获取离线状态
            let online_flag = $("#"+timepointInfo.timepoint_uuid+"_ipselect").find("option:selected").attr('value_online');
            //获取任务中状态
            let in_task = $("#"+timepointInfo.timepoint_uuid+"_ipselect").find("option:selected").attr('value_in_task');
            //获取任务名称
            let task_name = $("#"+timepointInfo.timepoint_uuid+"_ipselect").find("option:selected").attr('value_task_name');
            let is_self = $("#"+timepointInfo.timepoint_uuid+"_ipselect").find("option:selected").attr('is_self');
            var check = true;
            //如果是离线状态或任务中则禁止选择并给出提示(注意该值非布尔类型)
            if(online_flag == "false"){
                UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET, LANG.UI_MACHINE_OS_CANNOT_SELECT_OFFLINE_HOST);
                check = false;
            }
            if(check && in_task == "true"){
                UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET, LANG.UI_MACHINE_OS_CANNOT_SELECT_HOST_IN_TASK+"'"+task_name+"'"+LANG.UI_MACHINE_OS_TASK);
                check = false;
            }

            if (check && is_self == 1) {
                UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET, LANG.UI_RECOVERY_SOURCE_HOST_TIPS);
                check = false;
            }

            if (check && (ipselectValue == '' || ipselectValue == 0)) {
                check = false;
            }

            // 清空属性
            $("#"+timepointInfo.timepoint_uuid+"_sourceConfigDiv").hide();
            $("#"+timepointInfo.timepoint_uuid+"_recover_target_id").html("");
            $("#"+timepointInfo.timepoint_uuid+"_driverCheck").html("");
            $("#driverCheckFailContinueOs_"+timepointInfo.timepoint_uuid).hide();
            //获取检测里面的属性status_agent_uuid的值
            $("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr("status_agent_uuid");
            $("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr("status","false");
            //当切换选项后就取消勾选i标签
            $("#"+timepointInfo.timepoint_uuid+"_linkChecked").find("i").remove();

            if (!check) {
                //取消选择
                $("#"+timepointInfo.timepoint_uuid+"_ipselect").select2("val", "0");
                return;
            }

            //获取检测里面的属性status_agent_uuid的值
            let statusAgentUuid = $("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr("status_agent_uuid");

            //当ipselect事件改变后获取所有.ipselect选中的ip然后禁用
            // 获取所有 class 为 ipselect 的选择框
            var ipSelects = $('.ipselect');
            var selectValueList = [];

            // 遍历每个选择框,使用for的方式
            for (var i = 0; i < ipSelects.length; i++) {
                // 获取当前选择框的值
                var selectValue = $(ipSelects[i]).val();
                // 将值添加到数组中
                selectValueList.push(selectValue);
            }
            //遍历所有.ipselect选择框
            ipSelects.each(function(){
                //获取当前选中的值
                var selectValue = $(this).val();
                //获取排除当前选中值的所有option对象集合
                var optionList = $(this).find('option').not($(this).find('option[value="'+selectValue+'"]'));
                //遍历optionList
                optionList.each(function(){
                    //获取当前option的值
                    var optionValue = $(this).val();
                    if(selectValueList.includes(optionValue) && optionValue != ""){
                        $(this).attr('disabled',true);
                    }else{
                        $(this).attr('disabled',false);
                    }
                })
            })
        })

        //初始化poppvers
        $(".targetBox").find('.popovers').popover();
        //初始化checkbox
        $('.make-switch').bootstrapSwitch();

        $("#"+timepointInfo.timepoint_uuid+"_linkChecked").on('click', () => {
            //获取恢复目的地的值
            let agent_uuid = $("#"+timepointInfo.timepoint_uuid+"_ipselect").val();
            if(agent_uuid == "0"){
                $("#"+timepointInfo.timepoint_uuid+"_sourceConfigDiv").hide();
                $("#"+timepointInfo.timepoint_uuid+"_recover_target_id").html("");
                UIToastr.showWarning(LANG.UI_PLATFORM_RECOVERY_DESTINATION, LANG.UI_TENANT_SELECT_RECOVERY_DES);
                return;
            }
            Metronic.blockUI({target: '#submit_form',animate: true, cenrerY: true});
            // 刷新客户端
            pAjaxRequest({},'/api/v1/agents/'+agent_uuid+'/refresh','POST',(d)=>{
                Metronic.unblockUI('#submit_form');
                if (pointType == 3) {
                    // cdp 需要初始化备机网络
                    loadStandbyNetworkInfo(agent_uuid);
                }
                var agent_type = $('#'+timepointInfo.timepoint_uuid+'_ipselect option:selected').attr('agent_type');
                var os_type = $('#'+timepointInfo.timepoint_uuid+'_ipselect option:selected').attr('os_type');
                // 跨平台恢复需要和迁移到livecd需要驱动检测
                if (jobType == 1 || (jobType == 3 && agent_type == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS)) {
                    var moduleTypeArr = {
                        1:CONF.MODULE_TYPE.VM,
                        2:CONF.MODULE_TYPE.OS,
                        3:CONF.MODULE_TYPE.VOL_CDP,
                    };
                    var sorce_list = [{
                        name: timepointInfo.host_name,
                        timepoint_uuid: timepointInfo.timepoint_uuid,
                        module_type: moduleTypeArr[pointType], // VM
                    }];
                    var target_info = {
                        target_type: $.fn.driverCheck.DRIVER_TARGET_TYPE.CLIENT, // VM
                        client_info: {
                            agent_uuid: agent_uuid,
                        }
                    };
                    //初始化检测插件
                    $.fn.driverCheck.init($("#"+timepointInfo.timepoint_uuid+"_driverCheck"), {
                        width: {
                            config_label: '',
                            config_content: 'col-md-12',
                        },
                        getCheckObject: () => {
                            return {
                                source_list:sorce_list,
                                target_info:target_info
                            }
                        },
                        afterCheck:function (result, data){
                            var timepoint_uuid = timepointInfo.timepoint_uuid;
                            // 隐藏是否继续恢复
                            $('#driverCheckFailContinueOs_'+timepoint_uuid).hide();
                            isPlatformType[timepoint_uuid] = false;
                            isSupportOs[timepoint_uuid] = false;
                            // 根据返回结果为true的时候显示下面的虚拟机信息
                            if (result) {
                                driverCheck[timepoint_uuid] = data;
                                showAgentConfig(timepoint_uuid, timepointInfo.agent_uuid, timepointInfo.encrypted_flag, timepointInfo.node_uuid, os_type);
                            } else {
                                // 还有个操作系统版本不支持添加驱动的情况
                                if (data[0].driver_check_status == 8) {
                                    isSupportOs[timepoint_uuid] = true;
                                    driverCheck[timepoint_uuid] = data;
                                    showAgentConfig(timepoint_uuid, timepointInfo.agent_uuid, timepointInfo.encrypted_flag, timepointInfo.node_uuid, os_type);
                                } else {
                                    // 有个特殊处理，如果是异构平台，用户可决定是否继续恢复
                                    for(var k in data) {
                                        if (data[k].platform_type == 2) {
                                            isPlatformType[timepoint_uuid] = true;
                                        } else {
                                            isPlatformType[timepoint_uuid] = false;
                                        }
                                    }
                                    if (isPlatformType[timepoint_uuid]) {
                                        // 显示是否继续恢复
                                        $('#driverCheckFailContinueOs_'+timepoint_uuid).show();
                                        driverCheck[timepoint_uuid] = data;
                                        showAgentConfig(timepoint_uuid, timepointInfo.agent_uuid, timepointInfo.encrypted_flag, timepointInfo.node_uuid, os_type);
                                    } else {
                                        $("#"+timepoint_uuid+"_sourceConfigDiv").hide();
                                        $("#"+timepoint_uuid+"_recover_target_id").html("");
                                        driverCheck[timepoint_uuid] = [];
                                    }
                                }
                            }
                            // console.log('result', result)
                            // console.log('data', data)
                        }
                    });

                    //初始化内容后初始化驱动检测插件
                    $.fn.driverCheck.check($("#"+timepointInfo.timepoint_uuid+"_driverCheck"));
                } else {
                    //  console.log('timepointInfo', timepointInfo)
                    // 直接显示客户端机器配置信息
                    showAgentConfig(timepointInfo.timepoint_uuid, timepointInfo.agent_uuid, timepointInfo.encrypted_flag, timepointInfo.node_uuid, os_type);
                }
            });
        });
    }

    /**
     * 获取操作系统类型
     *
     * */
    var getPointOstype = function (timepoint_uuid){
        var params = {
            agent_uuid: '',
            timepoint_uuid: timepoint_uuid
        };
        var os_type = '';
        pAjaxRequest(params, "/api/v1/recovery/timepoint_config", "GET", function (d) {
            if (d.success) {
                var ostype = parseInt(d.data.os_type);
                if (ostype == 2) {
                    os_type = 'Windows';
                } else if (ostype < 5 && ostype > 0) {
                    os_type = 'Linux';
                }
                return os_type;
            }
        }, false);
    }

    /**
     * 获取备机网卡信息
     */
    let loadStandbyNetworkInfo = (_recoveryTargetUuid) => {
        let data = {'agent_uuid': _recoveryTargetUuid};
        Metronic.blockUI({target: '#completeMachineVolcdprecoverycontent',animate: true, cenrerY: true,});
        pAjaxRequest(data,'/api/v1/complete_machine_volcdp/standby/network_info','GET',(d)=>{
            Metronic.unblockUI('#completeMachineVolcdprecoverycontent');
            target_netcard_conf = d.data;
            target_netcard_conf = target_netcard_conf.map(row => {
                return {
                    ...row,
                    value: row.mac_address,
                }
            })
        },false);
    }

    /**
     * 刷新客户端
     */
    let refreshClient = (agentUuid) => {
        pAjaxRequest({},'/api/v1/agents/'+agentUuid+'/refresh','POST',(d)=>{
            return true;
            // return d.success;
        },false);
    }

    // 初始化目标配置
    function showAgentConfig(timepoint_uuid, origin_agent_uuid, encrypted_flag = false, real_node_uuid, os_type = ''){
        //添加属性
        let agent_uuid = $("#"+timepoint_uuid+"_ipselect").val();
        var agent_type = $('#'+timepoint_uuid+'_ipselect option:selected').attr('agent_type');
        // Metronic.blockUI({target: '#'+timepoint_uuid+'_target_panel_id',animate: true});
        //初始化恢复源
        $("#"+timepoint_uuid+"_recover_target_id").html('');
        var type = 2; // 默认虚拟机
        if (pointType == 2) {
            // 操作系统
            type = 1;
        } else if (pointType == 3){
            // 实时
            type = 6;
        }

        // 整机到整机的瞬时恢复，可以配置挂载路径
        var target_visible_flag = true;
        if (jobType == 2 && !(pointType == 2 && os_type == 'Linux')) {
            target_visible_flag = false;
        }
        $("#"+timepoint_uuid+"_recover_target_id").initTargetPlug({
            type: type, //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管
            origin_task_uuid: "", //源任务uuid(实时,定时可不传或为空)
            origin_agent_uuid: origin_agent_uuid, //源代理主机uuid(实时,定时可不传或为空)
            origin_timepoint_uuid: timepoint_uuid, //源时间点uuid或时间点(实时,定时)
            target_agent_uuid: agent_uuid, //目标代理主机uuid(目标端主机)
            target_visible_flag: target_visible_flag,  //控制恢复目标的列是否显示 true显示,false不显示
            recover_type: !(jobType == 2 && pointType == 2 && os_type == 'Linux'), //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
            target_auto_flag: false, //是否显示恢复目标选择框的自动分配选项
            target_input_flag: jobType == 2 && pointType == 2 && os_type == 'Linux', //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
            target_input_min: jobType == 2 && pointType == 2 && os_type == 'Linux' ? 0 : -1, //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
            origin_data_array:[], //前端源信息,当origin_data_flag为true时使用这个数据
            //nocheck: jobType == 2, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
            nocheck: false, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
            forced_relationship: true,
            chkDisabled: jobType == 3, // 迁移不允许排除磁盘
            first_column_name: LANG.UI_PLATFORM_RECOVERY_SOURCE_DISK,
            second_column_name: LANG.UI_PUBLIC_TOTAL_SIZE,
            third_column_name: jobType != 2 ? LANG.UI_RECOVERY_GOAL : LANG.UI_RECOVERY_GUAZAI_PATH,
            is_instant_machine: jobType == 2 && pointType == 2 && os_type == 'Linux', // 是否是整机到整机的瞬时恢复任务
            task_uuid: taskUuid, // 瞬时恢复的任务uuid
            instant_flag: jobType == 2, // 瞬时恢复的任务标记
            motion_flag: jobType == 3 && agent_type == 1, // 迁移到在线客户端的任务标记
            loading: '#'+timepoint_uuid+'_target_collapse_id'
        });


        if (jobType != 2) {
            // 如果不是瞬时恢复 需要初始化网络
            // 1 如果是跨平台恢复还需要初始化 网络配置插件，迁移则只是显示网络列表
            initNetworkList("#"+timepoint_uuid+"_ipconfig_id", agent_uuid, timepoint_uuid);
        }

        //初始化传输网络信息
        $("#"+timepoint_uuid+"_transferNetworkTree").transferNetwork({node_uuid: real_node_uuid});

        //初始化脚本插件
        if (jobType == 2) {
            // 瞬时恢复有四个脚本
            $('.'+timepoint_uuid+"_script_recover_before").html('');
            $('.'+timepoint_uuid+"_script_recover_before").initVinScript({class: timepoint_uuid+"_script_recover_before"});

            $('.'+timepoint_uuid+"_script_recover_after").html('');
            $('.'+timepoint_uuid+"_script_recover_after").initVinScript({class: timepoint_uuid+"_script_recover_after"});

            $('.'+timepoint_uuid+"_script_stop_before").html('');
            $('.'+timepoint_uuid+"_script_stop_before").initVinScript({class: timepoint_uuid+"_script_stop_before"});

            $('.'+timepoint_uuid+"_script_stop_after").html('');
            $('.'+timepoint_uuid+"_script_stop_after").initVinScript({class: timepoint_uuid+"_script_stop_after"});
        } else if (jobType == 1 || jobType == 3) {
            // 跨平台恢复 或 迁移
            $('.'+timepoint_uuid+"_script_recover_after").html('');
            $('.'+timepoint_uuid+"_script_recover_after").initVinScript({class: timepoint_uuid+"_script_recover_after"});
        }

        //当检测通过后修改检测的状态
        //获取其状态属性
        $("#"+timepoint_uuid+"_linkChecked").attr("status", "true");
        var iTag = '<i class="levelchild viconfont vicon-a-Checkxiaoyan" style="margin-right: 5px;"></i>';
        $("#"+timepoint_uuid+"_linkChecked").html(iTag + $("#"+timepoint_uuid+"_linkChecked").text());

        //添加属性
        $("#"+timepoint_uuid+"_linkChecked").attr("status_agent_uuid", agent_uuid);

        //显示配置项
        $("#"+timepoint_uuid+"_sourceConfigDiv").show();

        if (jobType == 3) {
            checkBlockUIRemoval(timepoint_uuid);
        }
    }

    function checkBlockUIRemoval(timepoint_uuid) {
        var doms = $('#' + timepoint_uuid + '_target_collapse_id');
        var timeout = 10000; // 最大等待时间 10 秒
        var startTime = Date.now();

        var checks = setInterval(function () {
            if (doms.find('.blockUI').length === 0) {
                disableCheckboxes("#" + timepoint_uuid + "_recover_target_id li", timepoint_uuid + '_tableId');
                clearInterval(checks);
            } else if (Date.now() - startTime > timeout) {
                console.warn("Timeout: .blockUI was not removed within the expected time.");
                clearInterval(checks);
            }
        }, 500);
    }

    function disableCheckboxes(selector, tableId) {
        var treeObj = $.fn.zTree.getZTreeObj(tableId);
        var nodes = treeObj.getNodes();
        for (var i=0; i < nodes.length; i++) {
            treeObj.setChkDisabled(nodes[i], true);
        }
        return;
        $(selector).find('.checkbox_true_full').css({
            'pointer-events': 'none',
            'opacity': '0.5',
        });
    }

    //获取所有可用恢复目的地主机IP地址等等
    var getOptionIp = function(){
        Metronic.blockUI({target:$('.waitInitDiv'),animate: true});

        var requestData  = function(data){
            if(data.success){
                initTargetConfig(data.data);
                Metronic.unblockUI('.waitInitDiv');
            }else{
                UIToastr.showWarning(LANG.UI_PLATFORM_RECOVERY_DESTINATION, data.message);
            }
        }
        //初始化备份源
        pAjaxRequest({}, '/api/v1/complete_machine_os/target_ip', "GET", requestData, true);
    }

    //初始化目标机配置
    var initTargetConfig = function(iplist){
        //获取勾选的时间点主机
        var selectedAgent = point.point_info
        //先清空内容
        $(".targetBox").html("");
        //根据勾选的时间点初始化第二步内容
        for(var i = 0; i < selectedAgent.length; i++){
            let timepointInfo = {};
            //获取时间点uuid
            timepointInfo.timepoint_uuid = selectedAgent[i].timepoint_uuid;
            //获取时间点名称
            timepointInfo.timepoint_name = selectedAgent[i].name;
            //获取时间点主机名
            timepointInfo.host_name = selectedAgent[i].host_name;
            //获取时间点主机uuid
            timepointInfo.agent_uuid = selectedAgent[i].uuid;
            //获取操作系统类型
            timepointInfo.os_type = selectedAgent[i].os_type;
            timepointInfo.node_uuid = selectedAgent[i].node_uuid;
            // 引导模式
            timepointInfo.system_boot_type = selectedAgent[i].system_boot_type;

            //是否加密
            timepointInfo.encrypted_flag = selectedAgent[i].encrypted_flag;
            // 是否包含系统盘
            timepointInfo.is_sys = selectedAgent[i].is_sys != undefined ? selectedAgent[i].is_sys : true;

            initTargetDom(timepointInfo, iplist, i);
        }
    }

    // 初始化目标机配置
    function initClient(){
        //初始化点击事件
        getOptionIp();
        // 源机网卡信息
        loadAgentNetworkInfo();
    }

    /**
     * 获取主机网卡信息
     */
    let loadAgentNetworkInfo =  () => {
        let data = {};
        data.agent_uuid = point.agent_uuid;
        data.task_uuid = point.task_uuid;
        pAjaxRequest(data,'/api/v1/complete_machine_volcdp/agent/network_info','GET',(d)=>{
            source_netcard_conf = d.data;
        },false)
    }

    // 加载宿主机的所有网卡列表
    var initNetworkList = function (id, agentUuid, timepoint){
        // 1，先根据agentUuid获取网卡列表，
        // 2.根据任务类型是否加载网络配置
        var dom = timepoint + '_sourceConfigDiv';
        Metronic.blockUI({target: '#'+dom,animate: true});
        pAjaxRequest({agent_uuid: agentUuid}, '/api/v1/complete_machine_os/agent_network', 'GET', function (d) {
            Metronic.unblockUI('#'+dom);
            if (d.success) {
                initNetwork(d.data, id, agentUuid, timepoint);
            } else {
                operateResponseList(d);
            }
        }, true)
    }

    var initNetwork = function (network, id, agentUuid, timepoint){
        //获取数据的各个值
        let agent_uuid = network.agent_uuid;
        var agent_name = network.agent_name;
        var hostname = network.hostname;
        var os_type = network.os_type;
        var agent_type = network.agent_type;
        var ip = network.ip;
        var nic_list = network.nic_list;

        //填写主机名
        $("#"+timepoint+"_rename_id").val(hostname)
        //遍历nic_list数组
        // 初始化网络配置插件结构体
        var networkInit = [];
        if (pointType == 3) {
            // cdp
            nic_list = source_netcard_conf;
        }
        //遍历nic_list数组
        for (var i = 0; i < nic_list.length; i++) {
            var eachNetwork = {};

            eachNetwork.checked = true;
            eachNetwork.chk_disabled = (jobType == 3);
            eachNetwork.net_uuid = getUuid();
            eachNetwork.show_config_type_flag = true;
            eachNetwork.net_name = nic_list[i]['name'];
            eachNetwork.net_mac = nic_list[i]['mac_address'];
            eachNetwork.mac_address = nic_list[i]['mac_address'];
            eachNetwork.show_advance_flag = (jobType == 1);
            eachNetwork.change_net_flag = false;
            eachNetwork.network_list = target_netcard_conf;
            if (pointType == 3) {
                // cdp实时隐藏 ipv6
                eachNetwork.show_ip_switch_nav_tabs_flag = false;
            }

            eachNetwork.ipv6_set = {
                config_type: $.fn.NetworkConfig.CONFIG_TYPE_ENUM.auto,
                ip_set: [],
                dns1: '',
                dns2: ''
            };
            eachNetwork.ipv4_set = {
                config_type: $.fn.NetworkConfig.CONFIG_TYPE_ENUM.auto,
                ip_set: [],
                dns1: nic_list[i]['dns'] != undefined ? nic_list[i]['dns'] : '',
                dns2: nic_list[i]['dns2'] != undefined ? nic_list[i]['dns2'] : ''
            };

            //获取ip类型
            let ip_set = nic_list[i]['ip_set'];
            for (var j = 0; j < ip_set.length; j++) {
                let eachIp_set = {};
                eachIp_set.ip = ip_set[j].ip_addr;
                eachIp_set.netmask = ip_set[j].netmask;
                if(ip_set[j].ip_type == 2){
                    // ipv6
                    eachNetwork.ipv6_set.gateway = nic_list[i]['ipv6_gateway_address'] != undefined ? nic_list[i]['ipv6_gateway_address'] : '';
                    eachNetwork.ipv6_set.ip_set.push(eachIp_set);
                } else {
                    eachNetwork.ipv4_set.gateway = nic_list[i]['gateway_address'];
                    eachNetwork.ipv4_set.ip_set.push(eachIp_set);
                }
            }
            networkInit.push(eachNetwork);
        }

        //console.log('networkInit', networkInit)
        if (pointType == 3) {
            // cdp
            $.fn.NetworkConfig.init($("#"+timepoint+"_ipconfig_id"), {
                custom_vm_net_list: networkInit,
                height: {
                    // table_height: '400px',
                },
                show_advance_flag: true,  // 是否显示高级
            }, $.fn.NetworkConfig.USAGE_TYPE_ENUM.CUSTOM_VM);
        } else {
            $.fn.NetworkConfig.init($("#"+timepoint+"_ipconfig_id"), {
                client_net_list: networkInit,
                height: {
                    table_height: '400px',
                },
                show_advance_flag: true,
            },  $.fn.NetworkConfig.USAGE_TYPE_ENUM.CLIENT);
        }

        //高级配置事件
        $('#show_completeMachineRecovery .rowhighconfig').on('click', function(){
            //显示/隐藏高级配置行
            $(this).closest("tr").next().toggleClass('displaynone');
            //设置展开收起图标
            $(this).find('span').toggleClass('glyphicon-plus');
            $(this).find('span').toggleClass('glyphicon-minus');
        });
    }

    var getUuid = function (len = 0) {
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if (len > 0) {
            for (i = 0; i < len; i++) {
                uuid[i] = chars[0 | Math.random() * radix];
            }
        } else {
            var r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for (i = 0; i < 36; i++) {
                if (!uuid[i]) {
                    r = 0 | Math.random() * 16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    }

    // ip校验
    var checkIp = function (netDatas){
        let ipV4List = [];
        let ipV6List = [];
        var check = true;
        var msg;
        for (var j in netDatas) {
            var netData = netDatas[j];
            if (!check || !netData.checked || !netData.change_net_flag) {
                break;
            }
            // 检测手动配置的ipv4
            if (2 === netData.ipv4_set.config_type) {
                for (let i in netData.ipv4_set.ip_set) {
                    // 检测ip
                    if ('' === netData.ipv4_set.ip_set[i].ip || !ipV4V6(netData.ipv4_set.ip_set[i].ip)) {
                        msg = LANG.UI_SETTING_INPUT_IP;
                        check = false;
                        break;
                    }
                    if (ipV4List.includes(netData.ipv4_set.ip_set[i].ip)) {
                        msg = LANG.UI_SETTING_INPUT_IP_REPEAT;
                        check = false;
                        break;
                    }
                    // 记录已有ip防止重复
                    ipV4List.push(netData.ipv4_set.ip_set[i].ip);

                    // 检测子网掩码
                    if (!netData.ipv4_set.ip_set[i].netmask) {
                        msg = LANG.UI_SETTING_INPUT_NETMASK;
                        check = false;
                        break;
                    }
                }
                // 检测网关 迁移不强制输入网关
                if ('' === netData.ipv4_set.gateway && jobType != 3) {
                    msg = LANG.UI_SETTING_INPUT_GATEWAY;
                    check = false;
                    break;
                }
                // 检测DNS
                if (netData.ipv4_set.dns1 && !ipV4V6(netData.ipv4_set.dns1) || netData.ipv4_set.dns2 && !ipV4V6(netData.ipv4_set.dns2)) {
                    msg = LANG.UI_SETTING_INPUT_DNS;
                    check = false;
                    break;
                }
            }
            // 检测手动配置的ipv6
            if (2 === netData.ipv6_set.config_type) {
                for (let i in netData.ipv6_set.ip_set) {
                    // 检测ip
                    if ('' === netData.ipv6_set.ip_set[i].ip || !ipV4V6(netData.ipv6_set.ip_set[i].ip)) {
                        msg = LANG.UI_SETTING_INPUT_IP;
                        check = false;
                        break;
                    }
                    if (ipV6List.includes(netData.ipv6_set.ip_set[i].ip)) {
                        msg = LANG.UI_SETTING_INPUT_IP_REPEAT;
                        check = false;
                        break;
                    }
                    // 记录已有ip防止重复
                    ipV6List.push(netData.ipv6_set.ip_set[i].ip);

                    // 检测子网掩码
                    if (!netData.ipv6_set.ip_set[i].netmask) {
                        msg = LANG.UI_SETTING_INPUT_NETMASK;
                        check = false;
                        break;
                    }
                }
                // 检测网关 迁移不强制输入网关
                if (jobType != 3 && '' === netData.ipv6_set.gateway) {
                    msg = LANG.UI_SETTING_INPUT_GATEWAY;
                    check = false;
                    break;
                }
                // 检测DNS
                if (netData.ipv6_set.dns1 && !ipV4V6(netData.ipv6_set.dns1) || netData.ipv6_set.dns2 && !ipV4V6(netData.ipv6_set.dns2)) {
                    msg = LANG.UI_SETTING_INPUT_DNS;
                    check = false;
                    break;
                }
            }
        }

        if (!check) {
            UIToastr.showWarning(LANG.UI_VM_SETTING_V2_NETWORK, msg);
        }
        return check;
    }

    return {
        init: function (options = {}) {
            if (options.point != undefined) {
                point = options.point;
            } else {
                point = RecoverTimepoint.getInfo();
            }

            if (options.jobType != undefined) {
                jobType = options.jobType;
            }

            if (options.pointType != undefined) {
                pointType = options.pointType;
            }

            if (options.taskUuid != undefined) {
                taskUuid = options.taskUuid;
            }
            initClient();		//初始化时间点对应的整机配置
        },
        getInfo: function (){
            // 对外提供获取目标机配置信息
            //获取勾选的时间点主机
            var selectedAgent = point.point_info
            var recover_info = [];
            var check = true;
            var cdp = {};
            //根据勾选的时间点初始化第二步内容
            for(var i = 0; i < selectedAgent.length; i++){
                var agentInfo = {};
                //获取时间点uuid
                var pointUuid = selectedAgent[i].timepoint_uuid;
                agentInfo.point_name = selectedAgent[i].host_name;
                if ($('#'+pointUuid +'_recover_target_id').html() == '') {
                    check = false;
                    UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_DESTINATION_SUCCESS);
                    break;
                    return false;
                }

                // 处理下驱动检测结果
                if (driverCheck[pointUuid] != undefined) {
                    agentInfo.driver_info = driverCheck[pointUuid][0]['driver_hw_id_map'];
                    cdp = {
                        'cross_platform_flag': driverCheck[pointUuid][0]['diff_platform_type'],
                        'cross_platform_conf': driverCheck[pointUuid][0]['driver_hw_id_map'],
                    };
                    if (isPlatformType[pointUuid] == true) {
                        // 标记是否异构平台未检测通过
                        if($('#continueCheckOs_' + pointUuid).is(':checked')){
                            // 选择继续
                            agentInfo.continue_driver = true; // 继续恢复 等后台给出新的消息结构字段
                        } else {
                            // 如果页面的 checkbox的值不是继续恢复，那么就阻止并给出提示
                            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_DRIVER_NO_CONTINUE_TIPS);
                            check = false;
                        }
                    }
                    // 不支持替换驱动
                    if (isSupportOs[pointUuid] == true) {
                        cdp = {
                            'cross_platform_flag': false,
                            'cross_platform_conf': '',
                        };
                        UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_DRIVER_CHECK_RESULT_NONSUPPORT_OS_INSTALL_DRIVER);
                    }
                } else {
                    agentInfo.driver_info = '';
                }
                agentInfo.recovery_timepoint_uuid = pointUuid;
                agentInfo.recovery_timepoint_pwd = '';

                if (jobType == 1) {
                    // 恢复任务 有个传输网络的通用配置
                    //获取传输网络
                    let network = $("#"+pointUuid+"_transferNetworkTree").transferNetwork('getSelect');
                    if(network == false){
                        check = false;
                        UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_NODE_NETWORK_TRANSFER_EMPTY);
                        break;
                        return false;
                    }
                    agentInfo.network_uuid = network.network_uuid;
                    agentInfo.network_pool_uuid = network.network_pool_uuid;
                    agentInfo.network_name = network.name;
                } else {
                    agentInfo.network_uuid = '';
                }

                // 获取目标设备配置
                var treeObj = $.fn.zTree.getZTreeObj(pointUuid +'_tableId');
                var recover_target_id = treeObj.getNodes();
                if (jobType === 1) {
                    // 跨平台恢复 需要判断恢复目标设备是否全部选了
                    for (var k in recover_target_id) {
                        if (recover_target_id[k].selectedInfo == undefined || recover_target_id[k].selectedInfo.length <= 0) {
                            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_DESTINATION_REQUIRE);
                            check = false;
                            break;
                        }
                    }
                    if (!check) {
                        return false;
                    }
                    // cdp
                    if (pointType == 3) {
                        let changeData = $('#'+ pointUuid +'_recover_target_id').getTargetData('getEachData',pointUuid);
                        var relation_set = [];
                        for(let i=0;i<changeData.target_strategy.length;i++){
                            let relation_set_list = {};
                            relation_set_list.dev_uuid = changeData.target_strategy[i].source_dev_uuid;
                            relation_set_list.target_dev_uuid = changeData.target_strategy[i].destination_dev_uuid;
                            relation_set_list.backup_time = $('.selecttimepoint').val();
                            relation_set.push(relation_set_list);
                        }
                        cdp.relation_set = relation_set;
                    }
                }
                //agentInfo.recover_target_id = recover_target_id;

                //获取目标卷配置------------------------------
                // 是否是整机到整机的瞬时恢复任务
                var os_type = $('#'+pointUuid+'_ipselect option:selected').attr('os_type');
                var agent_type = $('#'+pointUuid+'_ipselect option:selected').attr('agent_type');
                agentInfo.agent_type = agent_type;
                agentInfo.os_type = os_type;
                var is_instant_machine = jobType == 2 && pointType == 2 && os_type == 'Linux';
                var eachTargetData = $("#"+pointUuid+"_recover_target_id").getTargetData('getEachData', pointUuid, is_instant_machine, jobType != 1);
                agentInfo.recovery_strategy = eachTargetData.target_strategy;
                agentInfo.exclude_recovery_dev = eachTargetData.exclude_dev;
                agentInfo.mount_config = {
                    timepoint_uuid: pointUuid,
                    mount_info: JSON.stringify([]),
                    inst_rev_devices: JSON.stringify([]), // 选择的磁盘 dev_uuid
                };
                // 获取选中的磁盘uuid
                var inst_rev_devices = [];
                if (jobType == 2) {
                    for (k in recover_target_id) {
                        if (recover_target_id[k].checked) {
                            // 判断下是否全部选中，因为磁盘不允许排除卷
                            //check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
                            inst_rev_devices.push(recover_target_id[k].dev_uuid);
                        }
                    }
                    if (check && inst_rev_devices.length <= 0) {
                        check = false;
                        // UIToastr.showError(LANG.UI_PLATFORM_RECOVERY_TARGET_CONFIG, LANG.UI_PLATFORM_RECOVERY_TARGET_CONFIG_CHOOSE_ONE);
                    }
                    // 将 mount_info 转换为 JSON 字符串
                    agentInfo.mount_config.inst_rev_devices = JSON.stringify(inst_rev_devices);

                    if (is_instant_machine) {
                        // 需要循环处理下挂载路径的、
                        var mount_info = [];
                        for (k in agentInfo.recovery_strategy) {
                            var mnt_point = agentInfo.recovery_strategy[k].destination_name;
                            if (mnt_point != '' && mnt_point != LANG.UI_PLATFORM_INDUSTRY_INPUT && $.inArray(agentInfo.recovery_strategy[k].main_pid, inst_rev_devices) !== -1) {
                                // 如果有自定义路径
                                // 正则匹配必须是类似 /开头的nginx的文件路径
                                const regex = /^\/(?!$)(?:[^/]+\/?)+$/;
                                if (regex.test(mnt_point)) {
                                    mount_info.push({
                                        dev_uuid: agentInfo.recovery_strategy[k].source_dev_uuid,
                                        fs_uuid: agentInfo.recovery_strategy[k].source_fs_uuid,
                                        mnt_point: mnt_point
                                    });
                                } else {
                                    UIToastr.showWarning(LANG.UI_RECOVERY_GUAZAI_PATH, LANG.UI_PLATFORM_RECOVERY_MOUNT_PATH_RULE_TIPS);
                                    check = false;
                                    break;
                                }
                            }
                        }
                        // 将 mount_info 转换为 JSON 字符串
                        agentInfo.mount_config.mount_info = JSON.stringify(mount_info);
                    }
                }


                if (jobType != 2) {
                    // 获取网络配置
                    var network_list = $.fn.NetworkConfig.getData($("#"+pointUuid+"_ipconfig_id"));
                    if (!checkIp(network_list)) {
                        check = false;
                        break;
                    }
                    var network_lists = [];
                    for (var k in network_list) {
                        if (network_list[k].checked) {
                            network_lists.push(network_list[k]);
                        }
                    }
                    if (network_lists.length <= 0) {
                        UIToastr.showWarning(LANG.UI_VM_SETTING_V2_NETWORK, LANG.UI_NODE_NETWORK_POOL_LIST_EMPTY);
                        check = false;
                        break;
                    }
                    agentInfo.network_config = network_list;
                    if (pointType == 3) {
                        // cdp
                        cdp.restore_ip_change_flag = network_list[0].change_net_flag;
                        cdp.restore_business_ip_map = [];
                        for (var i in network_list) {
                            if (network_list[i].checked) {
                                // 选中网卡
                                let interfacesList = {};
                                interfacesList.uuid = getUuid();  // 随机生成uuid
                                interfacesList.model_type = "";
                                interfacesList.source_nic_name = network_list[i].net_name;   // 源虚拟机网卡名称
                                interfacesList.source_nic_mac = network_list[i].net_mac;     // 源虚拟机网卡地址
                                interfacesList.name = network_list[i].network_name;          // 目标
                                interfacesList.mac_address = network_list[i].network;        // 目标网卡地址

                                // ipv4
                                interfacesList.DNS1 = network_list[i].ipv4_set.dns1;
                                interfacesList.DNS2 = network_list[i].ipv4_set.dns2;
                                interfacesList.gateway_address = network_list[i].ipv4_set.gateway;    // 目标网关地址
                                // ipv6
                                interfacesList.ipv6_DNS1 = network_list[i].ipv6_set.dns1;
                                interfacesList.ipv6_DNS2 = network_list[i].ipv6_set.dns2;
                                interfacesList.ipv6_gateway_address = network_list[i].ipv6_set.gateway;    // 目标网关地址

                                interfacesList.ip_set = [];
                                // ipv4
                                for(let j=0;j<network_list[i].ipv4_set.ip_set.length;j++){
                                    interfacesList.ip_set.push(
                                        {
                                            "ip_addr":network_list[i].ipv4_set.ip_set[j].ip,
                                            "ip_type":1,
                                            "netmask":network_list[i].ipv4_set.ip_set[j].netmask
                                        }
                                    )
                                }

                                // ipv6
                                for(let j=0;j<network_list[i].ipv6_set.ip_set.length;j++){
                                    interfacesList.ip_set.push(
                                        {
                                            "ip_addr":network_list[i].ipv6_set.ip_set[j].ip,
                                            "ip_type":2,
                                            "netmask":network_list[i].ipv6_set.ip_set[j].netmask
                                        }
                                    )
                                }
                                cdp.restore_business_ip_map.push(interfacesList);
                            }
                        }
                    }
                }
                else {
                    agentInfo.network_config = [];
                }

                // 获取脚本配置
                agentInfo.after_recovery_script_info = [];// 公共的，给个默认的
                if (jobType == 2) {
                    // 瞬时恢复脚本
                    agentInfo.script_info = {
                        timepoint_uuid: pointUuid,
                        before_run_scripts: $('.'+pointUuid+"_script_recover_before").getVinScript(pointUuid+"_script_recover_before"),
                        after_start_success_scripts: $('.'+pointUuid+"_script_recover_after").getVinScript(pointUuid+"_script_recover_after"),
                        before_stop_scripts: $('.'+pointUuid+"_script_stop_before").getVinScript(pointUuid+"_script_stop_before"),
                        after_stop_scripts: $('.'+pointUuid+"_script_stop_after").getVinScript(pointUuid+"_script_stop_after"),
                    };
                    if (
                        !agentInfo.script_info.before_run_scripts ||
                        !agentInfo.script_info.after_start_success_scripts ||
                        !agentInfo.script_info.before_stop_scripts ||
                        !agentInfo.script_info.after_stop_scripts
                    ) {
                        check = false;
                    }
                } else if (jobType == 1 || jobType == 3) {
                    agentInfo.after_recovery_script_info = $('.'+pointUuid+"_script_recover_after").getVinScript(pointUuid+"_script_recover_after");
                    if (!agentInfo.after_recovery_script_info) {
                        check = false;
                    }
                }
                agentInfo.grub_method = 0;
                agentInfo.rename_host = 0; // "1:rename;  others:Don't",
                agentInfo.new_host_name = '';
                if (jobType == 1) {
                    // 跨平台恢复
                    // 获取高级配置
                    agentInfo.grub_method = parseInt($('#'+pointUuid +'_boot_mode_id').val()); // 引导模式 "0:not reset; 1:bios;  2:EFI",
                    agentInfo.rename_host =  $('#'+pointUuid +'_rename_check').get(0).checked; // 重置主机名
                    if (agentInfo.rename_host) {
                        agentInfo.new_host_name =  $('#'+pointUuid +'_rename_id').val(); // 主机名
                    }
                    if (pointType == 3) {
                        cdp.host_reset_name = '';
                        // cdp 实时
                        if (agentInfo.rename_host) {
                            cdp.host_reset_name = agentInfo.new_host_name;
                        }
                    }
                }
                // 返回选择的宿主机
                agentInfo.agent_uuid = $('#'+pointUuid +'_ipselect').val();
                agentInfo.destination_agent_uuid = agentInfo.agent_uuid;
                // 返回选择的宿主机的显示名称
                agentInfo.agent_name = $('#'+pointUuid +'_ipselect option:checked').text();
                agentInfo.os_name = agentInfo.agent_name;
                agentInfo.net_model = $('#'+pointUuid+'_ipselect option:selected').attr('net_model');
                agentInfo.cdp = cdp;
                recover_info.push(agentInfo);
            }
            if (!check) {
                return false;
            }
            // console.log('recover_info', recover_info);
            return recover_info;
        }
    };
}();
