(function ($) {
    // CHECK_TIME: {
    // 	DAY: 1,//每天一次
    // 		WEEK: 0, //每周一次
    // 		EVERY: 2,//每次备份
    // },
    // FULL_ABNORAL: {//完全备份点异常
    // 	REFULL: 1,//将整链标记为损坏，并重做完备
    // 		STOPBACKUP: 0,//中止备份
    // },
    // OTHER_ABNORAL: {
    // 	REFULL: 1,//重做完备（将对应差异点/增量点标记为损坏）
    // 		REINC: 2,//重做增量（删除无效差异点/增量点，并基于最新有效点做目标端增量）
    // 		STOPBACKUP: 0,//中止备份
    // }
    /**
     * 使用说明
     * 1、在html界面定义一个dom元素，如: <div id="completeDetectionBackup"></div>
     * 2、初始化组件 $('#wormConfig').completeDetectionBackup('col-md-3', true, true, CONF.CHECK_TIME.WEEK, CONF.FULL_ABNORAL.REFULL, CONF.OTHER_ABNORAL.REFULL)
     * 3、获取配置值 var a = $('#wormConfig').getCompleteDetectionBackup()
     *
     * 初始化参数说明
     * precent: 文本的宽度
     * flag: 是否开启完整性校验
     * module_type: 模块类型
     * reIncFlag: 差异/增量备份点异常是否有重做增量选项
     * checkDay: 校验周期
     * full: 完全备份点异常
     * verification: 差异/增量备份点异常
     *
     * 返回值说明
     * {
     *     integrity_check_flag: 完整性校验开关值
     *     integrity_check_config: {
     *         check_strategy: 校验周期
     *         full_error_policy: 完全备份点异常
     *         inc_error_policy: 差异/增量备份点异常
     *         recovery_error_policy: 固定值-1
     *     }
     *     str: 描述信息
     * }
     *
     * @param flag switch
     * @param selectValue
     * @returns {*}
     */
    $.fn.completeDetectionBackup = function (precent = 'col-md-3', flag = true, module_type = 2, reIncFlag = true, checkDay = CONF.CHECK_TIME.EVERY, full = CONF.FULL_ABNORAL.REFULL, verification = CONF.OTHER_ABNORAL.REFULL) {
        let id = $(this).attr('id');
        flag = true;  // 默认开启完整性校验
        checkDay = CONF.CHECK_TIME.EVERY;  // 默认每次备份

        $(`#${id}`).html(`
                            <div class="form-group display-none">
                               <label class="control-label ${precent}  form-group-label">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK}</label>
                               <div class="col-md-3 form-group-content flex-items-center">
                                  <input type="checkbox" id="${id}_check" ${flag ? 'checked' : ''} class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text=${LANG.UI_FILE_ENABLE} data-off-text=${LANG.UI_FILE_DISABLE}>
                                  <a class="popovers ml12 mb-5" data-container="body" data-trigger="hover" data-placement="right" data-content="${LANG.WEB_OS_TRANSFER_ENCRY_TIPS}">
                                     <i class="viconfont vicon-tishi"></i>
                                     </a>
                               </div>
                            </div>
                            <div class="form-group completeSelect ${flag ? '' : 'display-none'}">
                               <label class="control-label ${precent} form-group-label">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK}</label>
                               <div class="${precent = 'col-md-3' ? 'col-md-9' : 'col-md-8'}">
                                  <div class="accordion strategyOne">
                                     <div class="panel panel-default strategy-panel">
                                         <div class="panel-heading">
                                            <h4 class="panel-title">
                                               <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupTim" aria-expanded="true">
                                                  <i class="levelchild viconfont vicon-pt_setting_email_notification font-green-seagreen"></i>
                                                  <span class="font-green-seagreen">${LANG.UI_STRATEGY_VERTIFY}</span>
                                                  <span class="splitText"></span>
                                               </a>
                                            </h4>
                                         </div>
                                         <div id="backupTim" class="panel-collapse collapse in recoverytimecollapseview">
                                             <div class="panel-body" style="">
                                                <div class="form-group display-none">
                                                   <label class="control-label col-md-3 form-group-label">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD}</label>
                                                   <div class="col-md-9">
                                                      <div class="virus-item">
                                                          <input type="radio" id="check_week" value=${CONF.CHECK_TIME.WEEK} name="checkDay" />
                                                          <label for="check_week">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_WEEK}</label>
                                                      </div>
                                                      <div class="virus-item">
                                                           <input type="radio" id="check_day"  value=${CONF.CHECK_TIME.DAY} name="checkDay" />
                                                           <label for="check_day">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_DAY}</label>
                                                      </div>
                                                      <div class="virus-item">
                                                           <input type="radio" id="check_every"  value=${CONF.CHECK_TIME.EVERY} name="checkDay" />
                                                           <label for="check_every">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_EVERY_TIME}</label>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="form-group ">
                                                   <label class="control-label col-md-3 form-group-label fullTimepointErrorPolicyLabel">${module_type == 2 || module_type == 5 || module_type == 10 ? LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_FULL_ERROR_POLICY : LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_ABNORMAL}</label>
                                                   <div class="col-md-9">
                                                      <div class="virus-item">
                                                          <input type="radio" id="full_restart" value=${CONF.FULL_ABNORAL.REFULL} name="full" />
                                                          <label for="full_restart">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE2}</label>
                                                      </div>
                                                      <div class="virus-item">
                                                           <input type="radio" id="full_end"  value=${CONF.FULL_ABNORAL.STOPBACKUP} name="full" />
                                                           <label for="full_end">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE1}</label>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="form-group ${module_type == 2 || module_type == 5 || module_type == 10 ? '' : 'display-none'}" id="other">
                                                   <label class="control-label col-md-3 form-group-label">${LANG.UI_SAFE_STRATEGY_INTEGRITY_ADN_DIFF_CHECK_BACKUP_POINT_ABNORMAL}</label>
                                                   <div class="col-md-9">
                                                      <div class="virus-item">
                                                          <input type="radio" id="${id}_verification_full" value=${CONF.OTHER_ABNORAL.REFULL} name="verification" />
                                                          <label for="${id}_verification_full">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_INC_ERROR_POLICY_OPTION_REDO_FULL}</label>
                                                      </div>
                                                      <div class="virus-item ${reIncFlag ? '' : 'display-none'}">
                                                           <input type="radio" id="${id}_verification_inc" value=${CONF.OTHER_ABNORAL.REINC} name="verification" />
                                                           <label for="${id}_verification_inc" >${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_INC_ERROR_POLICY_OPTION_REDO_INCR}</label>
                                                      </div>
                                                      <div class="virus-item">
                                                           <input type="radio" id="${id}_verification_end"  value=${CONF.OTHER_ABNORAL.STOPBACKUP} name="verification" />
                                                           <label for="${id}_verification_end">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE3}</label>
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                         </div>
                                     </div>
                                  </div>
                               </div>
                            </div>
		`);
        let str1 = '';
        let str2 = '';
        let str3 = '';
        var inputValue = function () {
            let aa = `#${id}_check`
            $(aa).bootstrapSwitch();
            setRadioButtons()
            splitJoin(str1, str2, str3);
        }

        function setRadioButtons() {
            var radios1 = $(`#${id} input[name="checkDay"]`);
            for (var i = 0; i < radios1.length; i++) {
                if (radios1[i].value == checkDay) {
                    radios1[i].checked = true;
                    var htmlContent = $('label[for="' + radios1[i].id + '"]').html();
                    str1 += htmlContent;
                    break;
                }
            }
            var radios2 = $(`#${id} input[name="full"]`);
            for (var i = 0; i < radios2.length; i++) {
                if (radios2[i].value == full) {
                    radios2[i].checked = true;
                    var htmlContent = $('label[for="' + radios2[i].id + '"]').html();
                    str2 += htmlContent;
                    break;
                }
            }
            var radios3 = $(`#${id} input[name="verification"]`);
            if (module_type == 2 || module_type == 5 || module_type == 10) {
                for (var i = 0; i < radios3.length; i++) {
                    if (radios3[i].value == verification) {
                        radios3[i].checked = true;
                        var htmlContent = $('label[for="' + radios3[i].id + '"]').html();
                        str3 += htmlContent;

                        break;
                    }
                }
            } else {
                str3 = '';
            }

        }

        $(`#${id}_check`).on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                $('.completeSelect').show();
            } else {
                $('.completeSelect').hide();
            }
        });
        $(`#${id} input[type="radio"]`).change(function () {
            var label = $('label[for="' + this.id + '"]');
            var htmlContent = label.html();

            switch (this.name) {
                case 'checkDay':
                    str1 = htmlContent;
                    break;
                case 'full':
                    str2 = htmlContent;
                    break;
                case 'verification':
                    str3 = htmlContent;
                    break;
            }
            splitJoin(str1, str2, str3);
        });

        const splitJoin = function (str1, str2, str3) {
            let strList = [str2];
            if (module_type == 2 || module_type == 5 || module_type == 10) {
                strList.push(str3);
            }
            $(`#${id} .splitText`).attr('title', strList.join('; ')).text(strList.join('; '));
        }
        inputValue()
        $(`#${id} .popovers`).popover();
    };

    /**
     * 获取完整备份策略描述
     * @param {*} integrityCheckFlag 完整性校验是否开启
     * @param {*} integrityCheckConfig 完整性校验配置
     * @param {*} isFullTimepointTitle 是否显示完备点异常标题【true显示完备点标题 false显示备份点标题】
     * @param {*} showIncrErrorPolicy 是否显示增量点异常策略
     * @param {*} excludeTitleFlag 是否排除标题
     * @param {*} wrapFlag 是否包裹br标签
     */
    $.fn.getCompleteDetectionBackupDes = (
        {
            integrityCheckFlag,
            integrityCheckConfig,
            isFullTimepointTitle = true,
            showIncrErrorPolicy = true,
            excludeTitleFlag = false,
            wrapFlag = true,
        }
    ) => {
        let desList = [];
        let des = ``;
        if (!excludeTitleFlag) {
            des = `${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK}: `;
        }
        if (integrityCheckFlag) {
            let _des = ``;
            if (isFullTimepointTitle) {
                _des += `${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_FULL_ERROR_POLICY}: `;
            } else {
                _des += `${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_ABNORMAL}: `;
            }
            // 完备点异常
            switch (integrityCheckConfig.full_error_policy) {
                case CONF.FULL_ABNORAL.REFULL:
                    _des += `${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE2}`;
                    break;
                default:
                    _des += `${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE1}`;
                    break;
            }
            desList.push(_des);
            // 增量点异常
            if (showIncrErrorPolicy) {
                _des = `${LANG.UI_SAFE_STRATEGY_INTEGRITY_ADN_DIFF_CHECK_BACKUP_POINT_ABNORMAL}: `;
                switch (integrityCheckConfig.inc_error_policy) {
                    case CONF.OTHER_ABNORAL.REFULL:
                        _des += `${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_INC_ERROR_POLICY_OPTION_REDO_FULL}`;
                        break;
                    case CONF.OTHER_ABNORAL.REINC:
                        _des += `${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_INC_ERROR_POLICY_OPTION_REDO_INCR}`;
                        break;
                    default:
                        _des += `${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE3}`;
                        break;
                }
                desList.push(_des);
            }
        } else {
            des = `${LANG.UI_PUBLIC_OFF}`;
        }
        if (wrapFlag) {
            return des + desList.join('<br>');
        } else {
            return des + desList.join('; ');
        }
    };

    /**
     * 获取完整性校验策略
     * @returns {Object}
     */
    $.fn.getCompleteDetectionBackup = function () {
        let id = $(this).attr('id');
        let integrityCheckConfig = {};
        let integrityCheckFlag = $(`#${id}_check`).get(0).checked ? 1 : 0;
        let isFullTimepointTitle = $('.fullTimepointErrorPolicyLabel').text().trim() === LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_FULL_ERROR_POLICY;
        let showIncrErrorPolicy = false;
        if (integrityCheckFlag) {
            let checkStrategy = $('input[name="checkDay"]:checked').val() || 0;
            let $fullErrorPolicy = $('input[name="full"]:checked');
            let $incrErrorPolicyWrapper = $('#other');
            let $incrErrorPolicy = $('input[name="verification"]:checked');
            let incErrorPolicy = -1
            if ($incrErrorPolicyWrapper.css('display') !== 'none') {
                incErrorPolicy = $incrErrorPolicy.val() || 2;
                showIncrErrorPolicy = true;
            }
            integrityCheckConfig.check_strategy = Number(checkStrategy)
            integrityCheckConfig.full_error_policy = Number($fullErrorPolicy.val() || 1)
            integrityCheckConfig.inc_error_policy = Number(incErrorPolicy)
            integrityCheckConfig.recovery_error_policy = -1
        }
        return {
            integrity_check_flag: integrityCheckFlag,
            integrity_check_config: integrityCheckConfig,
            str: $('.splitText').text(),
            des: $.fn.getCompleteDetectionBackupDes({
                integrityCheckFlag,
                integrityCheckConfig,
                isFullTimepointTitle,
                showIncrErrorPolicy,
                wrapFlag: false,
            }),
        };
    };

    /**
     * 使用说明
     * 1、在html界面定义一个dom元素，如: <div id="completeDetectionApply"></div>
     * 2、初始化组件 $('#completeDetectionApply').completeStrategyApply('col-md-3')
     * 3、获取配置值 var a = $('#completeDetectionApply').getCompleteStrategyApply()
     *
     * 初始化参数说明
     * precent: 文本的宽度
     * integrity_check_flag: 是否开启完整性校验
     *
     * 返回值说明
     * {
     *     integrity_check_flag: 完整性校验开关值
     *     integrity_check_config: {
     *         check_strategy: 固定值0
     *         full_error_policy: 固定值1
     *         inc_error_policy: 固定值2
     *         recovery_error_policy: 固定值0
     *     }
     * }
     *
     * @param precent number
     * @returns {*}
     */

    $.fn.completeStrategyApply = function (precent = 'col-md-3', integrity_check_flag = true) {
        let id = $(this).attr('id');
        $(`#${id}`).html(`
		<div class="form-group">
           <label class="control-label ${precent} form-group-label">${LANG.UI_VM_INTEGRITY_VERIFY}</label>
           <div class="col-md-3 form-group-content flex-items-center">
               <input type="checkbox" id="${id}_check" ${integrity_check_flag ? 'checked' : ''} class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text=${LANG.UI_FILE_ENABLE} data-off-text=${LANG.UI_FILE_DISABLE}>
               <a class="popovers ml12 mb-5" data-container="body" data-trigger="hover" data-placement="right" data-content=${LANG.UI_SAFE_STRATEGY_BACKUP_POINT_CHECK}>
                   <i class="viconfont vicon-tishi"></i>
               </a>
           </div>
        </div>
		`)
        var inputValue = function () {
            let aa = `#${id}_check`
            $(aa).bootstrapSwitch();
        }
        inputValue()
        $('.popovers').popover();
    }

    $.fn.getCompleteStrategyApply = function () {
        let id = $(this).attr('id');
        let selectValue = {};
        selectValue.check_strategy = 0
        selectValue.full_error_policy = 1
        selectValue.inc_error_policy = 2
        selectValue.recovery_error_policy = 0
        return {
            integrity_check_flag: $(`#${id}_check`).get(0).checked ? 1 : 0,
            integrity_check_config: selectValue,
        };
    }


    //1.先在html界面定义一个<div id="wormConfig"></div>
    //2.使用方式：在js初始化时写上$('#wormConfig').completeStrategyCovery(CONF.MODULE_TYPE.VM),module_type为模块类型，只有虚拟机的时候才有恢复到无网络环境
    //3.返回值是一个对象，只有一个数值，如{recovery_error_policy: 1}代表选择的是第二项，使用方法 var a = $('#wormConfig').getCompleteStrategyCovery()
    //4.使用该组件需要引入./css/platform/component/safe_virus.css
    /**
     * 使用说明
     * 1、在html界面定义一个dom元素，如: <div id="integrityCheckRecoveryStrategy"></div>
     * 2、引入样式表 <link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css" />
     * 3、初始化组件 $('#integrityCheckRecoveryStrategy').completeStrategyCovery(CONF.MODULE_TYPE.VM)
     * 4、获取配置值 var a = $('#integrityCheckRecoveryStrategy').getCompleteStrategyCovery()
     *
     * 初始化参数说明
     * module_type: 模块类型，只有虚拟化才显示恢复到无网络环境选项
     * value: 完整性校验异常处理的值[0中断恢复 1继续恢复 2恢复到无网络环境]
     * backup_disable_flag: 备份任务是否禁用了完整性校验，true禁用，false启用
     * hide_recovery_to_no_network: 是否隐藏恢复到无网络环境选项，true隐藏，false不隐藏
     *
     * 返回值说明
     * {
     *     integrity_check_flag: 固定为1
     *     integrity_check_config: {
     *         check_strategy: 固定为-1
     *         full_error_policy: 固定为-1
     *         inc_error_policy: 固定为-1
     *         recovery_error_policy: 完整性校验异常处理
     *     }
     *     str: 描述信息
     * }
     *
     * @param module_type
     * @param value
     * @returns {*}
     */

    $.fn.completeStrategyCovery = function (module_type, value = 0, backup_disable_flag = false, hide_recovery_to_no_network = false) {
        let id = $(this).attr('id');
        value = parseInt(value);

        if (parseInt(module_type) !== CONF.MODULE_TYPE.VM) {
            hide_recovery_to_no_network = true;
        }

        const getContent = () => {
            return `
			<div class="htmlComplete">
		   		<div class="completeHead">
		   		   <div class="completePoint"></div>
		   		   <div class="completeText">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_STRATEGY}</div>
           		</div>
           		<div class="bodyComplete">
			    	<div class="complete-item__wrapper complete__wrapper">
			    		<div class="bodyText">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL}</div>
			    		<div class="virus-item">
							<label for="${id}_interrupt_recovery">
								<input type="radio" id="${id}_interrupt_recovery" value="0" name="abnormal" />
								<span class="virus-text">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY}</span>
							</label>
           		    	</div>
           		    	<div class="virus-item strategyItem">
           		    		<label for="${id}_continue_recovery">
           		    			<input type="radio" id="${id}_continue_recovery" value="1" name="abnormal" />
           		    			<span class="virus-text">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY}</span>
							</label>
           		   		</div>
           		    	<div class="virus-item" id="${id}_recover_to_no_network_item">
							<label for="${id}_recover_to_no_netowrk">
								<input type="radio" id="${id}_recover_to_no_netowrk" value="2" name="abnormal" />
								<span class="virus-text">${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_RECOVERY_TO_NO_NETWORK}</span>
							</label>
           		    	</div>
					</div>
           			<div id="${id}BackupDisableDiv" class="complete__wrapper">
           				<div class="alert alert-block alert-info fade in m0">
							<button type="button" class="close" data-dismiss="alert"></button>
							<strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
							<ul class="alert-ul">
								<li>${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_TASK_DISABLED_TIPS}</li>
							</ul>
						</div>
					</div>
           		</div>
        	</div>	
			`;
        };

        const setValue = () => {
            var $radioItems = $(`#${id} input[name="abnormal"]`);
            for (var i = 0; i < $radioItems.length; i++) {
                if (parseInt($radioItems[i].value) == value) {
                    $radioItems[i].checked = true;
                }
            }
            if (backup_disable_flag) {
                $(`#${id}BackupDisableDiv`).show();
                $(`#${id} input[name="abnormal"]`).attr('disabled', 'disabled').removeAttr('checked');
            } else {
                $(`#${id}BackupDisableDiv`).remove();
            }
            if (hide_recovery_to_no_network) {
                $(`#${id}_recover_to_no_network_item`).hide();
            } else {
                $(`#${id}_recover_to_no_network_item`).show();
            }
        };

        const initEventListener = () => {
            $(`#${id}BackupDisableDiv button.close`).on('click', () => {
                $(`#${id}BackupDisableDiv`).remove();
            });
            if (backup_disable_flag) {
                // $(`#${id} .complete-item__wrapper .virus-item label`).off().on('click', (e) => {
                // 	e.preventDefault();
                // });
            }
        };

        $(`#${id}`).html(getContent()).data('backup-disable', backup_disable_flag);
        setValue()
        initEventListener();
    }
    $.fn.getCompleteStrategyCovery = function () {
        let id = $(this).attr('id');
        let $selected = $(`#${id} input[name="abnormal"]:checked`);
        let integrityCheckFlag = 1;
        let integrityCheckConfig = {
            check_strategy: -1,
            full_error_policy: -1,
            inc_error_policy: -1,
            recovery_error_policy: Number($selected.val()) || 0,
        };
        let str = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_STRATEGY + ':<br>';
        str += LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL + ': ' + $(`#${id} label[for="${$selected.attr('id')}"] span.virus-text`).html();
        if ($(this).data('backup-disable')) {  // 经过确认，如果备份未开启完整性校验，恢复消息结构不论传什么都可以
            integrityCheckFlag = 0;
            integrityCheckConfig.recovery_error_policy = 0;
            str = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_STRATEGY + ': ' + LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_RECOVERY_UNSET;
        }
        return {
            integrity_check_flag: integrityCheckFlag,
            integrity_check_config: integrityCheckConfig,
            str,
        };
    }

})(jQuery);
