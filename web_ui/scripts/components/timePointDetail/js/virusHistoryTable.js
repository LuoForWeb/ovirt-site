/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-06-12 11:02:47
 * @LastEditTime: 2025-07-15 10:13:07
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */
(function ($) {
    $.fn.initVirusHistoryTable = function (params) {
        const defaults = {};
        const settings = { ...defaults, ...params };
        // 当前页面位置
        const _dom = $(this);
        // 初始化历史记录表格
        const initTable = function () {
            let options = {
                vin_url: "/api/v1/backup_data/virus_history",
                vin_method: "GET",
                vin_params: function () {
                    return { timepoint_uuid: settings.timepoint_uuid, virus_list: settings.virus_list };
                },
                showColumns: true,
                sortName: 'start_time',
                sortOrder: 'desc',
                pagination: false, //分页
                pageList: [5, 10, 25, 50], //每页数量
                detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
                detailFormatter: virus_detail, //详情展开
                columns: [
                    {
                        field: 'start_time',
                        title: LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_SCAN_START_TIME,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'end_time',
                        title: LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_SCAN_END_TIME,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'total_detect_file_num',
                        title: LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_SCAN_FILES,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'skip_file_num',
                        title: LANG.UI_FILE_COUNT_PASS,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'virus_file_num',
                        title: LANG.UI_BACKUP_DATA_VIRUS_DETECT_INFECTED_NUM,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'status',
                        title: LANG.UI_PUBLIC_STATUS,
                        sortable: true,
                        align: 'center',
                        formatter: function (value, row, index) {
                            let des = LANG.UI_PUBLIC_UNKNOWN;
                            let type = 0;
                            switch (row.status) {
                                case CONF.VIRUS_HISTORY_STATUS.DETECT_STATUS_NONE:
                                    des = LANG.UI_PUBLIC_UNKNOWN;
                                    break;
                                case CONF.VIRUS_HISTORY_STATUS.DETECT_STATUS_RUNNING:
                                    des = LANG.UI_BACKUP_DATA_POINT_OPERATION_STATE_SCANNING;
                                    type = 1;
                                    break;
                                case CONF.VIRUS_HISTORY_STATUS.DETECT_STATUS_INTERRUPTED:
                                    des = LANG.UI_BACKUP_DATA_VIRUS_DETECT_INTERRUPTED;
                                    type = 0;
                                    break;
                                case CONF.VIRUS_HISTORY_STATUS.DETECT_STATUS_COMPLETED:
                                    des = LANG.UI_BACKUP_DATA_VIRUS_DETECT_COMPLETED;
                                    type = 3;
                                    break;
                                case CONF.VIRUS_HISTORY_STATUS.DETECT_STATUS_FAILED:
                                    des = LANG.UI_BACKUP_DATA_VIRUS_DETECT_FAILED;
                                    type = 2;
                                    break;
                            }
                            return getStatusLabel(des, type);
                        }
                    },

                ]
            }
            _dom.baseTableConfig().init(options);
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
                case CONF.POINT_STATUS.OPERATING:
                    return `<span class="label label-sm label-info">${des}</span>`;
                // 异常
                case CONF.POINT_STATUS.ABNORMAL:
                    return `<span class="label label-sm label-danger">${des}</span>`;
                // 正常
                case CONF.POINT_STATUS.NORMAL:
                    return `<span class="label label-sm label-success">${des}</span>`;
            }
        }
        // 初始化展开详情
        let virus_detail = function (index, row, element) {
            let { detect_uuid, virus_list, timepoint_uuid, detect_config } = row;
            if (detect_uuid) {
                detect_config = JSON.parse(detect_config);
                let strategy_type = LANG.UI_SAFE_SEARCH_FIRST_VIRUS_STOP;
                if (detect_config.direct_stop_flag == '2') {
                    strategy_type = LANG.UI_SAFE_SEARCH_ALL_BACKUP;
                }
                let detect_path = '';
                if (detect_config.scan_entire_system_flag !== '1') {
                    try {
                        if (detect_config.specific_scan_target_map) {
                            detect_path = LANG.UI_SAFE_INCLUDE_PATH + ': ' + detect_config.specific_scan_target_map[1].join(';');
                        }
                    } catch (e) {
                        detect_path = '';
                    }
                }
                let html = `<div class="virus_detail-line virus_detail-config" style="display: flex;padding: 12px 0;">
                                <label class="virus_detail-label virus-config_label" style="font-size: 12px;color: #4d4d4d;width:100px">${LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_CONFIG_DETAIL}</label><br>
                                <span class="virus_detail-content virus-config_text">
                                    <span>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS}: ${LANG.UI_PUBLIC_ON}</span><br>
                                    <span>${strategy_type}</span><br>
                                    <span>${LANG.UI_SAFE_SCAN_THREAD}: ${detect_config.thread_num} </span><br>
                                    <span>${LANG.UI_SAFE_SCAN_ENTIRE}: ${detect_config.scan_entire_system_flag == '1' ? LANG.UI_SAFE_SCAN_ENTIRE_FULL_DISK : LANG.UI_SAFE_SCAN_ENTIRE_SPECIFY_PATH}</span><br>
                                    <span>${detect_path}</span>
                                </span>
                            </div>`;
                $(element).append(html);
            }
            Metronic.blockUI({ target: element, animate: true });
            // 获取文件感染列表
            let virusListBack = function (res) {
                Metronic.unblockUI(element);
                if (res.success) {
                    let virusList = res.data.result_list;
                    let list = '';
                    for (let i = 0; i < virusList.length; i++) {
                        list += virusList[i].file_path + '<br>';
                    }
                    let html = `<div class="virus_detail-info">
                                    <div class="virus_detail-line virus_detail-infect-path">
                                        <label class="virus_detail-label virus-config_label">${LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_INFECT_FILE_LIST}</label>
                                        <span class="virus_detail-content virus-config_text virus-infected_list">${list}</span>
                                    </div>
                                </div>`;

                    $(element).append(html);
                }
            };
            pAjaxRequest({ detect_uuid: detect_uuid, result_file_path: virus_list, timepoint_uuid: timepoint_uuid }, `/api/v1/backup_data/points/virus_list`, "GET", virusListBack, true);
        }
        return this.each(function () {
            initTable();
        });
    }
})(jQuery)