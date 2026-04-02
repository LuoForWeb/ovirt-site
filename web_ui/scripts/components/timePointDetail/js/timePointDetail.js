/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-06-11 10:49:10
 * @LastEditTime: 2026-03-27 10:42:06
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */
(function ($) {
    // 虚拟机、整机支持病毒扫描
    const MODULE_SUPPORT_VIRUS = [CONF.MODULE_TYPE.VM, CONF.MODULE_TYPE.OS, CONF.MODULE_TYPE.VOL_CDP];
    // 实时模块不支持完整性校验
    const MODULE_NOT_SUPPORT_INTEGRITY = [CONF.MODULE_TYPE.VOL_CDP, CONF.MODULE_TYPE.DB_CDP];
    
    // bd_backup_timepoint中worm_flag值对应枚举描述
    const WORM_FLAG_MAP_DES = [
        LANG.UI_PUBLIC_UNKNOWN, // 未知
        LANG.UI_BACKUP_DATA_POINT_DES_CONFIGED, // 已配置
        LANG.UI_BACKUP_DATA_POINT_DES_NOT_CONFIGED, // 未配置
        LANG.UI_BACKUP_DATA_LABEL_EXPIRED, // 已过期
    ];
    $.fn.initPointDetailDrawer = function (params = {}) {
        const defaults = {
            remote_flag: false,
            data: {},
        };
        const settings = { ...defaults, ...params };
        // 动态添加抽屉dom
        const addPointDrawerDiv = function () {
            return new Promise((resolve, reject) => {
                let html = `<div class="drawer slide pointDetailDrawer" id="pointDetailDrawerContent" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" data-backdrop="pointDetailDrawerContent">
			                    <div class="drawer-content drawer-content-scrollable" role="document">
                                    <div class="drawer-header ">
                                        <button type="button" class="close" data-dismiss="drawer" aria-hidden="true"><i class="viconfont vicon-guanbi"></i></button>
                                        <h4 class="drawer-title"><i class="viconfont vicon-shengchengshijian"></i> ${LANG.UI_BACKUP_DATA_POINT_DETAIL_TITLE}</h4>
                                    </div>
                                    <div class="drawer-body">
                                        <div class="portlet-body">
                                            <div class="detail-content">
                                                <div class="basic-info"></div>
                                                <div class="other-info"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="drawer-footer">
                                        <button type="button" data-dismiss="drawer" class="btn btn-default">${LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_CLOUSE}</button>
                                    </div>
                                </div>
                        </div>`;

                let pointDetailDrawerDiv = $('#pointDetailDrawerContent');
                if (pointDetailDrawerDiv.length > 0) {
                    pointDetailDrawerDiv.remove();
                }

                $('body').append(html);

                // 确保 DOM 更新完成
                requestAnimationFrame(() => {
                    resolve(); // 成功完成，继续下一步
                });
            });
        };

        // 假设这是你传入的 initDetailInfo 方法
        const initDetailInfo = function () {
            const _detailDom = $('#pointDetailDrawerContent');
            if (_detailDom.length == 0) {
                return false;
            }
            getTimePointInfo();
            // 这里写你的初始化逻辑
            // 可以根据 $element 做一些绑定或数据加载
        }
        // 获取时间点信息
        const getTimePointInfo = function () {
            $('.basic-info').empty();
            $('.other-info').empty();
            // 避免多模态框蒙层显示异常情况
            // 1.清除背景蒙层
            let backdrops = $('.drawer-backdrop[data-backdrop="pointDetailDrawerContent"]');
            backdrops.remove();
            // 2.获取抽屉的z-index
            const zIndex = $('#pointDetailDrawerContent').css('z-index');
            $('#pointDetailDrawerContent').drawer('toggle');
            // 3.给新的蒙层添加z-index
            $('.drawer-backdrop[data-backdrop="pointDetailDrawerContent"]').attr('style', `z-index: ${zIndex - 1}`);
            let pointsDetailsBack = function (res) {
                if (!res.success) return false;
                let data = res.data;
                // 基本信息
                initBasicInfo(data);
                // 验证信息
                if (!(data.detail.module_type == CONF.MODULE_TYPE.OS && data.detail.sub_module_type == 0)) {
                    if (data.detail.module_type != CONF.MODULE_TYPE.KUBERNETES) {
                        initVerifyInfo(data.detail);
                    }
                    if (!data.tape_storage_flag) {
                        // 数据完整性信息
                        initIntegrityInfo(data.detail.integrity_info, data.detail.module_type);
                        // worm信息
                        initWormInfo(data.detail.worm_info);
                    }
                    initMergeInfo(data);
                    if (!data.tape_storage_flag) {
                        // 病毒查杀信息
                        initVirusInfo(data.detail, data.detail.module_type);
                    }
                }
            };

            if (!settings.remote_flag) {
                pAjaxRequest({ timepoint_uuid: settings.timepoint_uuid }, `/api/v1/backup_data/points/details`, "GET", pointsDetailsBack, true);
            } else {
                // 基本信息，异地数据只显示基本信息，不显示安全信息
                initBasicInfo(settings.data);
            }
        }
        // 初始化时间点基础信息
        let initBasicInfo = (data) => {
            let { dir_path, detail } = data;
            let path = '';
            let dirPath = '';
            let displayPath = 'flex';
            let timeNameLabel = LANG.UI_BACKUP_DATA_DETAIL_LABEL_TIMEPOINT;
            let timeName = detail.timepoint;
            // 根据不同模块显示不同路径、列表
            switch (detail.module_type) {
                case CONF.MODULE_TYPE.VM:
                case CONF.MODULE_TYPE.PRIVATE_CLOUD:
                    path = `<label>${LANG.UI_CLOUD_PLATFORM_VM_PATH}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span title="${dir_path}">${dir_path}</span>`;
                    break;
                case CONF.MODULE_TYPE.PUBLIC_CLOUD:
                    path = `<label>${LANG.UI_TASK_AWS_INSTANCE_PATH}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span title="${dir_path}">${dir_path}</span>`;
                    break;
                case CONF.MODULE_TYPE.FS:
                    dirPath = JSON.parse(dir_path).join(',');
                    path = `<label>${LANG.UI_FILE_FILE_LIST}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
                    break;
                case CONF.MODULE_TYPE.DB:
                    path = `<label>${LANG.UI_DB_PATH}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span title="${dir_path}">${dir_path}</span>`;
                    break;
                case CONF.MODULE_TYPE.NAS:
                    dirPath = JSON.parse(dir_path).join(',');
                    path = `<label>${LANG.UI_NAS_BACKUP_PATH}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
                    break;
                case CONF.MODULE_TYPE.M365:
                    dirPath = JSON.parse(dir_path).join(',');
                    path = `<label>${LANG.UI_MICROSOFT365_BACKUP_DATA_LIST}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
                    break;
                case CONF.MODULE_TYPE.HADOOP:
                    dirPath = JSON.parse(dir_path).join(',');
                    path = `<label>${LANG.UI_FILE_BAK_PATH}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
                    break;
                case CONF.MODULE_TYPE.OBS:
                    dirPath = JSON.parse(dir_path).join(',');
                    path = `<label>${LANG.UI_OBS_BACKUP_PATH}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
                    break;
                case CONF.MODULE_TYPE.OS:
                    if(dir_path == ''){
                        break;
                    }
                    if (detail.sub_module_type == 1) {
                        $('.other-info').append(`<div class="other-info_line detail-content_title"><span class="decoration me-4"></span> ${LANG.UI_REPORY_BACKUP_ZIYUAN}</div>
                                                <div class="three_tree osDiskTreeDiv" style="display: flex;">
                                                    <label>${LANG.UI_BACKUP_DATA_TABLE_LABEL_PARITION_INFO}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>
                                                    <ul id="diskTree" class="ztree"></ul>
                                                </div>`);
                    } else {
                        dirPath = JSON.parse(dir_path).join(',');
                        path = `<label>${LANG.UI_BACKUP_DATA_TABLE_LABEL_VOLUME_INFO}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
                    }
                    break;
                case CONF.MODULE_TYPE.VOL_CDP:
                    timeNameLabel = LANG.UI_DATA_TYPE_BACKUPSET;
                    timeName = detail.start_timestamp + '  -  ' + detail.end_timestamp;
                    displayPath = 'none';
                    break;
                case CONF.MODULE_TYPE.KUBERNETES:
                    $('.other-info').append(`<div class="other-info_line detail-content_title"><span class="decoration me-4"></span> ${LANG.UI_REPORY_BACKUP_ZIYUAN}</div>
                                            <div class="three_tree osDiskTreeDiv" style="display: flex;">
                                                <label>${LANG.UI_KUBE_CLUSTER_LABEL}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>
                                                <ul id="diskTree" class="ztree"></ul>
                                            </div>`);
                    break;
            }
            let html = `<div class="info-timepoint">
                            <label class="point-info-label">${timeNameLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>${timeName}
                        </div>
                        <div class="info-timepoint_uuid">
                            <label class="point-info-label">${LANG.UI_BACKUP_DATA_DETAIL_LABEL_TIMEPOINT_UUID}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span id="pointUUid">${detail.timepoint_uuid}</span><span id="copyPointUUid" class="clip-icon"> <i class="viconfont vicon-fuzhi"></i></span>
                        </div>
                        <div class="info-storage_uuid">
                            <label class="point-info-label">${LANG.UI_BACKUP_DATA_DETAIL_LABEL_STORAGE_UUID}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span id="storageUUid">${detail.storage_uuid}</span><span id="copyStorageUUid" class="clip-icon"> <i class="viconfont vicon-fuzhi"></i></span>
                        </div>`;
            $('.basic-info').append(html);
            // 区分正常和异常的背景色
            let bgClass = 'detail_bg_normal';
            if (detail.abnormal_chain_flag) {
                bgClass = 'detail_bg_abnormal';
            }
            $('.basic-info').addClass(bgClass);
            // 链异常原因显示
            if (detail.abnormal_chain_flag) {
                let abnormal_reason_html = `<div class="other-info_line detail-content_title"><span class="decoration me-4"></span> ${LANG.UI_BACKUP_DATA_LABEL_ABNORMAL_CHAIN_DESCRIPTION}</div>
                                            <div class="other-info_line">
                                                    <label>${LANG.UI_BACKUP_DATA_LABEL_ABNORMAL_CHAIN_REASON}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>
                                                    <span>${detail.abnormal_chain_des}</span>
                                            </div>`;
                $('.other-info').append(abnormal_reason_html);
            }
            if ((detail.module_type == CONF.MODULE_TYPE.OS && detail.sub_module_type == 1) || detail.module_type == CONF.MODULE_TYPE.KUBERNETES) {
                let zNodes = JSON.parse(dir_path);
                let setting = {
                    check: {
                        enable: true,
                        nocheckInherit: false,
                    },
                    data: {
                        simpleData: {
                            enable: true
                        },
                        key: {
                            title: "dev_name"
                        }
                    },
                    view: {
                        nameIsHTML: true,
                    },
                    callback: {
                    },
                };
                //初始化树对象
                let zTree = $.fn.zTree.init($("#diskTree"), setting, zNodes);
            } else {
                $('.other-info').append(`<div class="other-info_line detail-content_title"><span class="decoration me-4"></span> ${LANG.UI_REPORY_BACKUP_ZIYUAN}</div><div class="other-info_line info-dir_path" style="display:${displayPath}">${path}</div>`);
            }
            // 一键复制
            $('#copyPointUUid').myClipboard({ target: '#pointUUid', });
            $('#copyStorageUUid').myClipboard({ target: '#storageUUid', });
        }
        // 初始化验证结果信息
        let initVerifyInfo = (data) => {
            let { verify_info, verify_flag, last_verify_time } = data;
            let html = ``, ping_status = '', heartbeat_status = '', screen_status = '', displayClass = !verify_flag ? '' : 'display-none';
            if (!verify_flag) {
                // 网络验证
                ping_status = `<span class="label label-sm status-icon ${setVerifyStatusDes(verify_info.ping_status)}">${verify_info.ping_status_des ?? LANG.UI_CLIENT_NOT_VERIFY}</span>`;
                // 心跳严重
                heartbeat_status = `<span class="label label-sm status-icon ${setVerifyStatusDes(verify_info.heartbeat_status)}">${verify_info.heartbeat_status_des ?? LANG.UI_CLIENT_NOT_VERIFY}</span>`;
                // 开机截屏验证
                screen_status = `<span class="label label-sm status-icon ${setVerifyStatusDes(verify_info.screen_status)}">${verify_info.screen_status_des ?? LANG.UI_CLIENT_NOT_VERIFY}</span>`;
            } else {
                last_verify_time = `<span class="label label-sm status-icon label-default">${LANG.UI_CLIENT_NOT_VERIFY}</span>`;
            }
            html += `<div class="other-info_line info_verify">
                        <div class="other-info_line detail-content_title"><span class="decoration me-4"></span> ${LANG.UI_PLATFORM_INDUSTRY_VERIFY_RESULTS}</div>
                        <div class="verify-last_time" style="padding-bottom:12px"><label>${LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_LAST_VERIFY_TIME}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value">${last_verify_time}</span></div>
                        <div class="verify-ping_status ${displayClass}" style="padding-bottom:12px"><label>${LANG.UI_VERIFY_PING_TEST}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value">${ping_status}</span></div>
                        <div class="verify-heartbeat_status ${displayClass}" style="padding-bottom:12px"><label>${LANG.UI_VERIFY_HEARTBEAT}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value">${heartbeat_status}</span></div>
                        <div class="verify-screen_status ${displayClass}"><label>${LANG.UI_VERIFY_SCREEN}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value">${screen_status}</span></div>
                    </div>`;
            $('.other-info').append(html);
        }
        // 初始化完整性校验信息
        let initIntegrityInfo = (data, module_type) => {
            // 判断授权和模块不支持
            if (!CONF.FUNCTIONS.includes('integrity') || MODULE_NOT_SUPPORT_INTEGRITY.includes(module_type)) {
                return;
            }
            let { integrity_check_flag, integrity_check_status, integrity_check_status_des, last_integrity_check_time } = data;
            let value = CONF.POINT_STATUS.UNKNOWN;
            let showClass = '';
            // 校验过才显示时间
            if (integrity_check_flag == 1) {
                switch (integrity_check_status) {
                    case CONF.INTEGRITY_STATUS.NORMAL:
                        value = CONF.POINT_STATUS.NORMAL;
                        break;
                    case CONF.INTEGRITY_STATUS.BROKEN:
                        value = CONF.POINT_STATUS.ABNORMAL;
                        break;
                    case CONF.INTEGRITY_STATUS.NO_CHECK:
                        value = CONF.POINT_STATUS.UNKNOWN;
                        // 未检测不显示上次检测时间
                        showClass = 'display-none';
                        break;
                }
                // 未校验过不显示时间
            } else {
                integrity_check_status_des = LANG.UI_BACKUP_DATA_POINT_DES_NOT_CONFIGED;
                showClass = 'display-none';
            }
            // 没有时间不显示
            if (!last_integrity_check_time) {
                showClass = 'display-none';
            }
            let html = `<div class="other-info_line detail-content_title"><span class="decoration me-4"></span> ${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK}</div>
                        <div class="other-info_line integrity-info">
                            <div class="integrity-status row-info"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_INTEGRITY_STATUS}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>${getStatusLabel(integrity_check_status_des, value)}</div>
                            <div class="integrity-check-time row-info ${showClass}"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_INTEGRITY_LAST_TIME}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value">${last_integrity_check_time}</span></div>
                        </div>`;
            $('.other-info').append(html);
        };
        /**
         * 初始化worm信息
         * @param {object} data 时间点信息
         */
        let initWormInfo = (data) => {
            // 判断授权
            if (!CONF.FUNCTIONS.includes('worm')) {
                return;
            }
            const { worm_flag, worm_expire_left } = data;
            let wormValue = WORM_FLAG_MAP_DES[worm_flag];
            let showClass = '';
            let lableClass = 'label-default';
            // 未配置显示未配置，已配置显示天数
            if (worm_flag == 1) {
                lableClass = 'label-success';
            } else {
                showClass = 'display-none';
            }
            let html = `<div class="other-info_line detail-content_title"><span class="decoration me-4"></span> ${LANG.UI_BACKUP_DATA_DETAIL_ACTION_WORM}</div>
                        <div class="other-info_line worm-info">
                            <div class="worm-status row-info"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_WORM}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value"><span class="label label-sm ${lableClass}">${wormValue}</span></span></div>
                            <div class="worm-days row-info ${showClass}"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_WORM_DATA}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value">${worm_expire_left}</span></div>
                        </div>`;
            $('.other-info').append(html);
        };
        // 初始化病毒扫描信息
        let initVirusInfo = (data, module_type) => {
            // 判断授权
            if (!CONF.FUNCTIONS.includes('virusKill') || !MODULE_SUPPORT_VIRUS.includes(module_type)) {
                return;
            }
            let { virus_scan_status, virus_scan_status_des, virus_list } = data.virus_info
            // 处理状态
            let value = CONF.POINT_STATUS.UNKNOWN;
            let virusTableIdv = `<div class="virus-list row-info">
                                    <label>${LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_SCAN_LIST}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>
                                    <div class="table-container"><table id="pointVirusListTable"></table></div>
                                </div>`;
            switch (virus_scan_status) {
                case CONF.VIRUS_STATUS.NO_SCAN:
                    virusTableIdv = ''
                    value = CONF.POINT_STATUS.UNKNOWN;
                    break;
                case CONF.VIRUS_STATUS.SCANNING:
                    value = CONF.POINT_STATUS.OPERATING;
                    break;
                case CONF.VIRUS_STATUS.SAFE:
                    value = CONF.POINT_STATUS.NORMAL;
                    break;
                case CONF.VIRUS_STATUS.INFECTED:
                case CONF.VIRUS_STATUS.INFECTED_PARTLY_SCAN:
                    value = CONF.POINT_STATUS.ABNORMAL;
                    break;
            }
            let html = `<div class="other-info_line detail-content_title"><span class="decoration me-4"></span> ${LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS}</div>
                        <div class="other-info_line virus-info">
                            <div class="virus-status row-info" style="margin-bottom:12px"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>${getStatusLabel(virus_scan_status_des, value)}</div>
                            ${virusTableIdv}
                        </div>`;
            $('.other-info').append(html);
            // 未扫描不用初始化历史表格
            if (virus_scan_status != CONF.VIRUS_STATUS.NO_SCAN) {
                // 初始化病毒扫描历史记录表格
                $('#pointVirusListTable').initVirusHistoryTable({ timepoint_uuid: data.timepoint_uuid, virus_list: virus_list });
            }
        };
        // 初始化合并状态
        let initMergeInfo = (data) => {
            let html = '';
            let status = data.detail.merge_status;
            let value = CONF.POINT_STATUS.NORMAL;
            // 合并中、依赖点失败、回滚中都是异常
            if ([CONF.MERGE_STATUS.MERGING, CONF.MERGE_STATUS.DEPEND_MERGE_FAILED, CONF.MERGE_STATUS.FAILED, CONF.MERGE_STATUS.ROLL_BACK].includes(status)) {
                value = CONF.POINT_STATUS.ABNORMAL;
            }
            // 只有异常和错误才显示
            if (value != CONF.POINT_STATUS.NORMAL) {
                html = `<div class="other-info_line merge-info">
					<div class="merge-status row-info"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_MERGE_STATUS}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>${getStatusLabel(data.detail.merge_status_des, value)}</div>
				</div>`;
            }
            // <div class="virus-scan-time"><label>${UI_BACKUP_DATA_DETAIL_LABEL_FAILED}</label><span class="label-value">${mergeInfo.fail_reason}</span></div>
            $('.other-info').append(html);
        }
        // 获取时间点相关状态对应的标签显示
        let getStatusLabel = (des, value) => {
            if (!des) {
                return `<span class="label label-sm label-default">${LANG.UI_PUBLIC_UNKNOWN}</span>`
            }
            switch (value) {
                // 未知、无操作
                case CONF.POINT_STATUS.UNKNOWN:
                    return `<span class="label label-sm label-default">${des}</span>`;
                // 操作中
                case CONF.POINT_STATUS.OPERATING:
                    let html = '';
                    let statusArr = des.split(',');
                    statusArr.forEach(item => {
                        html += `<span class="label label-sm label-primary">${item.trim()}</span>`;
                    });
                    return html;
                // 异常
                case CONF.POINT_STATUS.ABNORMAL:
                    return `<span class="label label-sm label-danger">${des}</span>`;
                // 正常
                case CONF.POINT_STATUS.NORMAL:
                    return `<span class="label label-sm label-success">${des}</span>`;
            }
        }
        // 获取验证结果状态对应的class样式
        const setVerifyStatusDes = function (status) {
            let labelClass = "label-info";
            switch (status) {
                case 0:	//未知
                    labelClass = "label-default";
                    break;
                case 1:	//等待
                    labelClass = "label-info";
                    break;
                case 2:	//运行
                    labelClass = "label-success";
                    break;
                case 3:	//跳过
                    labelClass = "label-default";
                    break;
                case 4:	//错误
                    labelClass = "label-danger";
                    break;
                case 5:	//成功
                    labelClass = "label-success";
                    break;
                case 6:	//完成
                    labelClass = "label-success";
                    break;
                case 7:	//异常
                    labelClass = "label-warning";
                    break;
            }
            return labelClass;
        }
        return this.each(function () {
            const $this = $(this);

            // 添加模态框并初始化详情信息
            addPointDrawerDiv()
                .then(() => {
                    initDetailInfo($this); // addPointDrawerDiv 完成后再调用 initDetailInfo
                })
                .catch(err => {
                });
        });
    };
})(jQuery);