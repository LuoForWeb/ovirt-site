/**
 * 备份任务 - 安全策略 - 备份后立即验证
 *
 * 调用方法：
 * 引用js：<script type="text/javascript" src="./scripts/platform/component/safe_surebackup.js"></script>
 * 在安全策略div下构建容器div，如：<div id="sureBackupConfig"></div>
 * 创建备份任务初始化：$('#sureBackupConfig').initSureBackupConfig();
 * 修改备份任务初始化：
 * $('#sureBackupConfig').initSureBackupConfig({
 *     colClass: 'col-md-3', // label宽度的css类
 *     sureBackupFlag: true, // 默认是否开启
 *     timepointRange: 1, // 时间点范围
 *     threadNum: 1, // 同时启动验证数量
 * });
 * 获取数据：$('#sureBackupConfig').getSureBackupData();
 * 获取确认配置处的描述：$('#sureBackupConfig').getSureBackupDes();
 */
(function($) {
    const TIMEPOINT_RANGE_UNKNOW = 0;
    const TIMEPOINT_RANGE_CURRENT = 1; // 当前验证时间点
    const TIMEPOINT_RANGE_ALL_UNVERIFY = 2; // 所有未验证时间点

    let create_surebackup_after_backup = false;
    let surebackup_timepoint_range = TIMEPOINT_RANGE_UNKNOW;
    let surebackup_thread_num = 0;

    //得到开关的结果描述   开启/关闭
    const getSwitchDes = function (check) {
        if (check) {
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    /**
     * 初始化验证策略
     * @param {Object} params
     */
    $.fn.initSureBackupConfig = function (params = {}) {
        // 容器div的ID
        let id = $(this).attr('id');
        // label宽度的css类
        let colClass = params.colClass ?? 'col-md-3';
        // 默认是否开启
        let sureBackupFlag = params.sureBackupFlag ?? false;
        // 时间点范围
        let timepointRange = params.timepointRange ? parseInt(params.timepointRange) : TIMEPOINT_RANGE_CURRENT;
        // 同时启动验证数量
        let threadNum = params.threadNum ?? 1;

        $(`#${id}`).html(`
            <!-- 备份后立即验证 开关 -->
            <div class="form-group">
                <label class="control-label ${colClass}  form-group-label">${LANG.UI_SUREBACKUP_AFTER_BACKUP}</label>
                <div class="col-md-3 form-group-content">
                    <input type="checkbox" id="${id}_surebackup_check" ${sureBackupFlag ? 'checked' : ''} class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text=${LANG.UI_FILE_ENABLE} data-off-text=${LANG.UI_FILE_DISABLE}>
                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="${LANG.UI_SUREBACKUP_AFTER_BACKUP_TIPS}">
                        <i class="viconfont vicon-tishi"></i>
                    </a>
                </div>
            </div>
            <!-- 验证时间点范围 -->
            <div class="form-group surebackup-div display-none">
                <label class="control-label ${colClass} form-group-label">${LANG.UI_SUREBACKUP_AFTER_BACKUP_TIMEPOINT_RANGE}</label>
                <div class="col-md-3">
                    <div class="range-item radio-container">
                        <input type="radio" id="${id}_range_current" value=${TIMEPOINT_RANGE_CURRENT} name="${id}_range" ${TIMEPOINT_RANGE_CURRENT === timepointRange ? 'checked' : ''}/>
                        <label for="${id}_range_current">${LANG.UI_SUREBACKUP_AFTER_BACKUP_TIMEPOINT_RANGE_CURRENT}</label>
                    </div>
                    <div class="range-item radio-container">
                        <input type="radio" id="${id}_range_all"  value=${TIMEPOINT_RANGE_ALL_UNVERIFY} name="${id}_range" ${TIMEPOINT_RANGE_ALL_UNVERIFY === timepointRange ? 'checked' : ''}/>
                        <label for="${id}_range_all">${LANG.UI_SUREBACKUP_AFTER_BACKUP_TIMEPOINT_RANGE_ALL_UNVERIFY}</label>
                    </div>
                </div>
            </div>
            <!-- 同时启动验证数量 -->
            <div class="form-group surebackup-div display-none">
                <label class="control-label ${colClass} form-group-label">${LANG.UI_SUREBACKUP_AFTER_BACKUP_THREAD_NUM}</label>
                <div class="col-md-3 form-group-content">
                    <div class="input-group spinner-group" style="justify-content: left">
                        <input type="text" id="${id}_thread_num" maxlength="2" onkeyup="value=value.replace(/[^\\d]/g,'')" class="spinner-input form-control input-sm" style="width: 150px" value="${threadNum}">
                        <div class="spinner-buttons input-group-btn spinner-group-btn" style="left: 128px">
                            <button type="button" class="input-sm btn spinner-up default" style="margin-left: -1px">
                                <i class="fa fa-angle-up"></i>
                            </button>
                            <button type="button" class="input-sm btn spinner-down default" style="border-right: none;border-bottom: none">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        const init = function () {
            // 初始化
            $(`#${id}_surebackup_check`).bootstrapSwitch('state', sureBackupFlag);
            if (sureBackupFlag) {
                $('.surebackup-div').show();
            } else {
                $('.surebackup-div').hide();
            }
            $(`input[name=${id}_range]`).iCheck({
                radioClass: 'iradio_square-blue'
            });

            // 添加事件
            $(`#${id}_surebackup_check`).on('switchChange.bootstrapSwitch',function() {
                if (this.checked) {
                    $('.surebackup-div').show();
                } else {
                    $('.surebackup-div').hide();
                }
            });
            $('.surebackup-div .spinner-up').on('click', function() {
                let inputSelector = `#${id}_thread_num`;
                // 获取当前值，加 1，然后设置回去
                let currentValue = parseInt($(inputSelector).val(), 10) || 1; // 确保值为数字，如果不是则默认为 1
                $(inputSelector).val(currentValue + 1);
            })
            $('.surebackup-div .spinner-down').on('click', function() {
                let inputSelector = `#${id}_thread_num`;
                // 获取当前值，加 1，然后设置回去
                let currentValue = parseInt($(inputSelector).val(), 10) || 1; // 确保值为数字，如果不是则默认为 1
                if( currentValue > 1 ) {
                    $(inputSelector).val(currentValue - 1);
                }
            })
        }
        init();
    }

    /**
     * 获取数据
     */
    $.fn.getSureBackupData = function () {
        let id = $(this).attr('id');
        create_surebackup_after_backup = $(`#${id}_surebackup_check`).bootstrapSwitch('state');
        if (create_surebackup_after_backup) {
            surebackup_timepoint_range = $(`input[name=${id}_range]:checked`).val();
            surebackup_thread_num = $(`#${id}_thread_num`).val();
            if (typeof surebackup_timepoint_range === 'undefined') {
                UIToastr.showWarning(LANG.UI_SUREBACKUP_AFTER_BACKUP, LANG.UI_SUREBACKUP_AFTER_BACKUP_TIMEPOINT_RANGE_TIPS);
                return false;
            }
            if (surebackup_thread_num === '') {
                UIToastr.showWarning(LANG.UI_SUREBACKUP_AFTER_BACKUP, LANG.UI_SUREBACKUP_AFTER_BACKUP_THREAD_NUM_TIPS);
                return false;
            }
            surebackup_timepoint_range = parseInt(surebackup_timepoint_range);
            surebackup_thread_num = parseInt(surebackup_thread_num);
        }

        return {
            create_surebackup_after_backup: create_surebackup_after_backup,
            surebackup_timepoint_range: surebackup_timepoint_range,
            surebackup_thread_num: surebackup_thread_num
        }
    }

    /**
     * 获取描述
     */
    $.fn.getSureBackupDes = function () {
        let str = LANG.UI_SUREBACKUP_AFTER_BACKUP + ': ' + getSwitchDes(create_surebackup_after_backup);
        if (create_surebackup_after_backup) {
            str += "<br>" + LANG.UI_SUREBACKUP_AFTER_BACKUP_TIMEPOINT_RANGE + ': ';
            switch (surebackup_timepoint_range) {
                case TIMEPOINT_RANGE_CURRENT:
                    str += LANG.UI_SUREBACKUP_AFTER_BACKUP_TIMEPOINT_RANGE_CURRENT;
                    break;
                case TIMEPOINT_RANGE_ALL_UNVERIFY:
                    str += LANG.UI_SUREBACKUP_AFTER_BACKUP_TIMEPOINT_RANGE_ALL_UNVERIFY;
                    break;
                default:
                    break;
            }
            str += "<br>" + LANG.UI_SUREBACKUP_AFTER_BACKUP_THREAD_NUM + ': ' + surebackup_thread_num;
        }
        return str;
    }
})(jQuery)