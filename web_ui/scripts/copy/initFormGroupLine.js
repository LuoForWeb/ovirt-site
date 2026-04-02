/*
 * @note: 生成form形式的配置列表
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2024-12-27 17:27:33
 * @LastEditTime: 2026-03-11 15:47:27
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 * 1.开关配置项
        transportPlugin.initSwitch({
            id: 'encryptData', // 开关id，建议与接口所需key一致
            label: LANG.UI_COPY_TRANSPORT_DATA_ENCRYPT_LABEL, // 开关中文名
            tips: LANG.UI_COPY_TRANSPORT_DATA_ENCRYPT_TIPS, // 提示信息，可不传
            defaultFlag: data ? data.bts.encryptData : true, // 开关默认值，可不传
        });
 * 2.单选框配置项
        transportPlugin.initSelect({
            id: 'inc_mode', // 单选框id，建议与接口所需key一致
            label: LANG.UI_COPY_TRANSPORT_INC_MODE, // 单选框中文名
            options: INC_MODE_CONFIG, // 下拉框选项{key:value}，key建议为接口所需字段
            tips: LANG.UI_COPY_TRANSPORT_INC_MODE_TIPS, // 提示信息，可不传
            defaultValue: data ? data.inc_mode : 1, // 下拉框默认值，可不传
        });
 * 3.数字输入框
        transportPlugin.initNumber({
            id: 'threadNum', // 数字输入框id，建议与接口所需key一致
            label: LANG.UI_GLOBAL_STRATEGY_THREAD_NUM, // 数字输入框中文名
            value: data ? data.bts.thread_num : 1, // 默认值，可不传
            min: 1, // 最小值，可不传(默认为1)
            max: 8, // 最大值，可不传(默认为999)
            step: 1, // 步长，可不传(默认为5)
            tips: LANG.UI_COPY_TRANSPORT_THREAD_NUM_TIPS, // 提示信息，可不传
        });
 * 4.开关+下拉框组合配置项
        transportPlugin.initSwitchSelectMixed({
            id: 'compress', // 开关id，建议与接口所需key一致
            labelSwitch: LANG.UI_COPY_BACK_COMPRESS, // 开关中文名
            labelSelect: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE, // 下拉框中文名
            options: COMPRESS_METHOD_CONFIG, // 下拉框选项{key:value}，key建议为接口所需字段
            tips: LANG.UI_COPY_TRANSPORT_COMPRESS_TIPS, // 提示信息，可不传
            defaultFlag: data ? data.bts.compress : true, // 开关默认值，可不传
            defaultValue: data ? data.bts.compress_method : 1, // 下拉框默认值，可不传
        });
 * 
 */
(function ($) {
    $.fn.initFormGroupLine = function (options) {
        // 默认插件配置（暂无、扩展使用）
        const defaultOptions = {};
        const settings = $.extend(defaultOptions, options);
        const _dom = $(this);
        /**
         * 生成提示html
         * @param {string} tips 
         * @returns 
         */
        const getTipsHtml = (tips) => {
            return `<a class="popovers ml12" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="${tips}">
                        <i class="viconfont vicon-tishi"></i>
                    </a>`;
        }
        /**
         * 控制页面元素显示隐藏
         * @param {object} dom 
         * @param {boolean} show 
         * @returns 
         */
        const domShowHandler = function (dom, show) {
            // 根据show的值控制dom显示隐藏
            if (dom.length) {
                if (show) {
                    dom.show();
                } else {
                    dom.hide();
                }
            } else {
                return false;
            }
        };

        /**
         * 开关配置项
         * @param {*} params 
         * @param {string} id 开关的唯一id，建议与接口所需key一致
         * @param {string} label 配置项中文名
         * @param {string} tips 提示信息，可不传
         * @param {boolean} defaultFlag 开关默认值，可不传
         */
        const switchGroup = (params) => {
            const { id, label, tips, defaultFlag = true } = params;
            let labelHtml = '', tipsHtml = '';
            // 配置项描述
            if (label) {
                labelHtml = `<label class="control-label col-md-3 ${id}-switch-label">${label}</label>`
            };
            // 配置项提示
            if (tips) {
                tipsHtml = getTipsHtml(tips);
            };
            // 页面增加配置项内容
            _dom.append(`<div class="custom-form-group custom-switch-div">
                            <div class="form-group ${id}-switch-div custom-switch-div">
                                ${labelHtml}
                                <div class="col-md-4">
                                    <input type="checkbox" id="${id}" data-size="small" checked class="make-switch" data-on-color="primary" data-off-color="info" data-on-text="${LANG.UI_PUBLIC_ON_ONE}" data-off-text="${LANG.UI_PUBLIC_OFF_ONE}" ${defaultFlag ? 'checked' : ''}>
                                    ${tipsHtml}
                                </div>
                            </div>
                        </div>`);
            // 初始化开关插件
            _dom.find(`#${id}`).bootstrapSwitch();
            // 初始化默认状态
            _dom.find(`#${id}`).bootstrapSwitch('state', defaultFlag);
            // 初始化提示信息插件
            _dom.find('.popovers').popover();
        }
        /**
         * 选择框配置项
         * @param {*} params 
         * @param {string} id 选择框的唯一id，建议与接口所需key一致
         * @param {object} options 下拉框选项{key:value}，key建议为接口所需字段
         * @param {string} tips 提示信息，可不传
         * @param {string || number} defaultValue 下拉框默认值，可不传
         */
        const selectGroup = (params) => {
            const { id, label, options, tips, defaultValue } = params;
            let labelHtml = '', tipsHtml = '', optionsHtml = '', modeTips = '';
            // 配置项描述
            if (label) {
                labelHtml = `<label class="control-label ${id}-select-label col-md-3">${label}</label>`
            };
            // 配置项提示信息
            if (tips) {
                tipsHtml = getTipsHtml(tips);
            };
            // 下拉框选项option
            for (let key in options) {
                optionsHtml += `<option value="${key}" ${key == defaultValue ? 'selected' : ''}>${options[key]}</option>`
            }
            // #28339 取消永久增量+镜像副本上云组合 提示信息不能选择镜像副本
            if(id == 'copy_mode'){
                modeTips = `<div class="form-group copy-mode-tip display-none" style="margin-top: -15px;">
							<label class="col-md-3"></label>
							<div class="col-md-8">
								<label class="help-block">${LANG.UI_COPY_TIPS_MIRROR_MODE_UNSUPPORT}</label>
							</div>
						</div>`;
            }
            // 动态添加内容
            _dom.append(`<div class="custom-form-group custom-select-div">
                            <div class="form-group ${id}-select-div custom-select-div">
                                ${labelHtml}
                                <div class="col-md-4">
                                    <select class="form-control select2me input-sm" id="${id}">${optionsHtml}</select>
                                </div>
                                ${tipsHtml}
                            </div>
                            ${modeTips}
                        </div>`);
            // 初始化提示插件
            _dom.find('.popovers').popover();
        }
        /**
         * 数字输入框配置项
         * @param {*} params 
         * @param {string} id 数字输入框的唯一id，建议与接口所需key一致
         * @param {number} value 默认值，可不传(默认为1)
         * @param {number} min 最小值，可不传（默认为1）
         * @param {number} max 最大值，可不传（默认为999）
         * @param {number} step 步长，可不传（默认为5）
         * @param {string} tips 提示信息，可不传
         */
        const numberGroup = (params) => {
            const { id, label, value = 1, min = 1, max = 999, step = 5, tips } = params;
            let labelHtml = '', tipsHtml = '';
            // 配置项描述
            if (label) {
                labelHtml = `<label class="control-label ${id}-number-label col-md-3">${label}</label>`
            };
            // 配置项提示信息
            if (tips) {
                tipsHtml = getTipsHtml(tips);
            };
            // 动态增加内容
            _dom.append(`<div class="custom-form-group custom-number-div">
                            <div class="form-group ${id}-number-div">
                                ${labelHtml}
                                <div class="col-md-4">
                                    <div class="input-group spinner-group">
                                        <input type="number" id="${id}" class="spinner-input form-control input-sm" max="${max}" min="${min}" onchange="this.value = (this.value > ${max}) ? ${max} : (this.value < ${min}) ? ${min} : this.value">
                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                            <button type="button" class="btn spinner-up default input-sm">
                                                <i class="fa fa-angle-up"></i>
                                            </button>
                                            <button type="button" class="btn spinner-down default input-sm">
                                                <i class="fa fa-angle-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    ${tipsHtml}
                                </div>
                            </div>
                        </div>`);
            // 初始化数字框插件
            $(`.${id}-number-div .spinner-group`).spinner({ value: value, step: step, min: min, max: max });
            // 初始化提示信息插件
            _dom.find('.popovers').popover();
        }
        /**
         * 开关+下拉框配置项
         * @param {*} params 
         * @param {string} id 开关+下拉框的唯一id，建议与接口所需key一致
         * @param {string} labelSwitch 开关描述，可不传
         * @param {string} labelSelect 下拉框描述，可不传
         * @param {object} options 下拉框选项{key:value}，key建议为接口所需字段
         * @param {string} tips 提示信息，可不传
         * @param {boolean} defaultFlag 开关默认值，可不传
         * @param {number} defaultValue 下拉框默认值，可不传
         */
        const switchSelectMixed = (params) => {
            const { id, labelSwitch, labelSelect, options, tips, defaultFlag = true, defaultValue = 1 } = params;
            let labelSwitchHtml = '', labelSelectHtml = '', tipsHtml = '', optionsHtml = '';
            // 开关配置项描述
            if (labelSwitch) {
                labelSwitchHtml = `<label class="control-label ${id}-switch-label col-md-3">${labelSwitch}</label>`
            };
            // 下拉框配置项描述
            if (labelSelect) {
                labelSelectHtml = `<label class="control-label ${id}-select-label col-md-3">${labelSelect}</label>`
            };
            // 下拉框选项option
            for (let key in options) {
                optionsHtml += `<option value="${key}" ${key == defaultValue ? 'selected' : ''}>${options[key]}</option>`
            };
            // 配置项提示信息
            if (tips) {
                tipsHtml = getTipsHtml(tips);
            };
            // 动态增加内容
            _dom.append(`<div class="custom-form-group custom-switch-select-div  custom-form-group__${id}">
                            <div class="form-group ${id}-switch-div">
                                ${labelSwitchHtml}
                                <div class="col-md-4">
                                    <input type="checkbox" id="${id}" data-size="small" checked class="make-switch" data-on-color="primary" data-off-color="info" data-on-text="${LANG.UI_PUBLIC_ON_ONE}" data-off-text="${LANG.UI_PUBLIC_OFF_ONE}" ${defaultFlag ? 'checked' : ''}>
                                    ${tipsHtml}
                                </div>
                            </div>
                            <div class="form-group custom-form-group ${id}-select-div" style=" ${defaultFlag ? '' : 'display: none'};">
                                ${labelSelectHtml}
                                <div class="col-md-4">
                                    <select class="form-control select2me input-sm" id="${id}_method">${optionsHtml}</select>
                                </div>
                            </div>
                        </div>`);
            // 初始化开关插件
            _dom.find(`#${id}`).bootstrapSwitch();
            // 初始化开关状态
            _dom.find(`#${id}`).bootstrapSwitch('state', defaultFlag)
            // 开关状态监听，控制下拉框显示
            _dom.find(`#${id}`).on('switchChange.bootstrapSwitch', function () {
                const methodDiv = $(`.${id}-select-div`);
                domShowHandler(methodDiv, this.checked)
            });
            // 初始化提示信息插件
            _dom.find('.popovers').popover();
        }
        /**
         * 单选按钮配置项
         * @param {*} params 
         * @param {string} id 单选按钮的唯一id，建议与接口所需key一致
         * @param {string} label 单选按钮描述，可不传
         * @param {object} options 单选按钮选项{key:value}，key建议为接口所需字段
         * @param {string} tips 提示信息，可不传
         * @param {number} defaultValue 单选按钮默认值，可不传
         * @param {*} params 
         */
        const radioGroup = (params) => {
            const { id, label, options, tips, defaultValue = 1 } = params;
            let labelHtml = '', tipsHtml = '', optionsHtml = '';
            // 单选按钮标签
            if (label) {
                labelHtml = `<label class="control-label ${id}-radio-label col-md-3">${label}</label>`
            };
            // 单选按钮提示
            if (tips) {
                tipsHtml = getTipsHtml(tips);
            }
            // 单选按钮配置项
            if (options) {
                for (let key in options) {
                    optionsHtml += `<label class="mr15">
                                        <input type="radio" data-checkbox="iradio_square-blue" data-mode="${key}" class="icheck" data-id="${id}" value="${key}" ${key == defaultValue ? 'checked' : ''} data-des="${options[key]}">
                                        ${options[key]}
                                    </label>`
                }
            }
            // 动态增加内容
            _dom.append(`<div class="custom-form-group custom-radio-div">
                            <div class="form-group ${id}-radio-div">
                                ${labelHtml}
                                <div class="col-md-4" style="height:34px;line-height:34px">
                                    ${optionsHtml}
                                    ${tipsHtml}
                                </div>
                            </div>
                        </div>`);
            // 初始化提示信息插件
            _dom.find('.popovers').popover();
            // 初始化单选按钮插件
            _dom.find('.icheck').iCheck({ radioClass: 'iradio_square-blue' });
            // 单选事件
            _dom.find(`.${id}-radio-div .icheck`).on('ifClicked', function () {
                _dom.find(`.${id}-radio-div .icheck`).iCheck('uncheck');
                $(this).iCheck('check');
            });
        }
        /**
         * 得到开关的结果描述   开启/关闭
         * @param {boolean} check 
         * @returns 
         */
        const getSwitchDes = function (check) {
            if (check) {
                return LANG.UI_PUBLIC_ON;
            }
            return LANG.UI_PUBLIC_OFF;
        }
        /**
         * 得到开关配置信息
         * @param {*} groupLine 
         * @returns 
         */
        const getSwitchConfig = (groupLine) => {
            // 配置项id
            let switchId = $(groupLine).find('input').attr('id');
            // 勾选状态
            let checked = $(`#${switchId}`).get(0).checked;
            // 描述信息
            let switchDes = $(`.${switchId}-switch-label`).html();
            // 生成描述信息
            showGroupConfigDes(`${switchDes}: ${getSwitchDes(checked)}<br>`);
            // 返回配置信息
            return { [switchId]: checked };
        }
        /**
         * 得到下拉框配置信息
         * @param {*} groupLine 
         * @returns 
         */
        const getSelectConfig = (groupLine, params) => {
            // 配置项id
            let selectId = $(groupLine).find('select').attr('id');
            // 选择配置值
            let value = parseInt($(`#${selectId}`).val());
            // 配置项描述
            let selectDes = $(`.${selectId}-select-label`).html();
            // 选项描述
            let selectMethodDes = $(`#${selectId} option:selected`).text();
            // 生成描述信息
            if (params.showIncModeFlag) {
                showGroupConfigDes(`${selectDes}: ${selectMethodDes}<br>`);
            }
            // 返回配置值信息
            return { [selectId]: value };
        }
        /**
         * 得到数字配置信息
         * @param {*} groupLine 
         * @returns 
         */
        const getNumberConfig = (groupLine, params) => {
            // 配置项id
            let numberId = $(groupLine).find('input').attr('id');
            // 配置项取值
            let value = parseInt($(`#${numberId}`).val());
            // 配置项描述
            let label = $(`.${numberId}-number-label`).html();
            // 判断输入的值是否为数字，不是则给出提示
            if (isNaN(value)) {
                UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_COPY_TRANSPORT_NUMBER_NAN_TIPS + label);
            }
            // 生成描述信息
            let numberDes = $(`#${numberId}`).val();
            if (numberId == 'threadNum' && !CONF.FUNCTIONS.includes('multithread')) {
            } else {
                showGroupConfigDes(`${label}: ${numberDes}<br>`);
            }
            // 返回配置信息
            return { [numberId]: value };
        }
        /**
         * 得到开关+下拉框配置信息
         * @param {*} groupLine 
         * @returns 
         */
        const getSwitchSelectConfig = (groupLine, params) => {
            // 开关id
            let flagId = $(groupLine).find('input').attr('id');
            // 勾选状态
            let checked = $(`#${flagId}`).get(0).checked;
            // 选择框id
            let methodId = $(groupLine).find('select').attr('id');
            // 选择项
            let value = parseInt($(`#${methodId}`).val());
            // 开关描述
            let switchDes = $(`.${flagId}-switch-label`).html();
            if(flagId == 'compress'){
                if(!params.showCompressFlag){
                    return { [flagId]: checked, [methodId]: value };
                }
            }
            showGroupConfigDes(`${switchDes}: ${getSwitchDes(checked)}<br>`);
            // 状态为开启时，显示下拉框配置信息
            if (checked) {
                let selectDes = $(`.${flagId}-select-label`).html();
                let selectMethodDes = $(`#${methodId} option:selected`).text();
                showGroupConfigDes(`${selectDes}: ${selectMethodDes}<br>`);
            }
            // 返回配置信息
            return { [flagId]: checked, [methodId]: value };
        }
        /**
         * 得到单选配置信息
         * @param {*} groupLine 
         * @returns 
         */
        const getRadioConfig = (groupLine) => {
            // 单选按钮id
            let radioId = $(groupLine).find('input:checked').data('id');
            // 单选按钮值
            let radioCheckVal = parseInt($(groupLine).find('input:checked').val());
            // 开关描述
            let switchDes = $(`.${radioId}-radio-label`).html();
            // 状态描述
            let checkValDes = $(groupLine).find('input:checked').data('des')
            // 显示配置信息描述
            showGroupConfigDes(`${switchDes}: ${checkValDes}<br>`);
            // 返回配置信息
            return { [radioId]: radioCheckVal };
        }
        /**
         * 显示配置信息
         * @param {*} des 
         */
        const showGroupConfigDes = (des) => {
            if (!settings.showDesDom) {
                return false;
            }
            settings.showDesDom.append(des)
        }
        /**
         * 获取配置项信息
         * @returns 
         */
        const getFormGroupConfig = (params) => {
            let data = {};
            const groupLines = _dom.find('.custom-form-group');
            groupLines.each((index, groupLine) => {
                let groupLineData = {};
                // 获取开关配置信息
                if ($(groupLine).hasClass('custom-switch-div')) {
                    groupLineData = getSwitchConfig(groupLine);
                }
                // 获取选择框配置信息
                if ($(groupLine).hasClass('custom-select-div')) {
                    groupLineData = getSelectConfig(groupLine, params);
                }
                // 获取数字输入框配置信息
                if ($(groupLine).hasClass('custom-number-div')) {
                    groupLineData = getNumberConfig(groupLine, params);
                }
                // 获取开关+下拉框配置信息
                if ($(groupLine).hasClass('custom-switch-select-div')) {
                    groupLineData = getSwitchSelectConfig(groupLine, params);
                }
                // 获取单选组合配置信息
                if ($(groupLine).hasClass('custom-radio-div')) {
                    groupLineData = getRadioConfig(groupLine);
                }
                // 返回配置信息
                data = { ...data, ...groupLineData };
            })
            return data;
        }
        return this.each(function () {
            const privateMethod = {
                initSwitch: switchGroup,
                initSelect: selectGroup,
                initNumber: numberGroup,
                initRadio: radioGroup,
                initSwitchSelectMixed: switchSelectMixed,
                getFormGroupConfig: getFormGroupConfig,
            }
            // 暴露获取副本源信息方法
            $(this).data('myFormGroup', privateMethod);
        });
    }
})(jQuery)