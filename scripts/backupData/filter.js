/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 过滤器插件
 * @Date: 2024-10-16 10:25:52
 * @LastEditTime: 2026-03-19 11:13:25
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */
/**
 * 过滤器插件
 * @param {Object} MODULE_FIXED - 定时模块模块类型->授权字段
 * @param {Object} MODULE_REAL - 实时模块模块类型->授权字段
 * @param {Object} MODULE_NAME - 模块值->模块名称
 * @param {Object} TASK_TYPE - 任务类型值->任务类型名称
 * @param {Object} BACKUP_TASK_TYPE - 备份任务类型分组->各模块备份任务类型值
 * @param {Object} POINT_OPERATION_STATE - 时间点操作类型值->操作类型名称
 * @param {Object} setting - 插件配置选项
 * @param {boolean|jQuery} options.showModule - 是否显示模块类型筛选
 * @param {boolean|jQuery} options.showTaskType - 是否显示任务类型筛选
 * @param {boolean|jQuery} options.showTaskStatus - 是否显示状态筛选
 */
(function ($) {
    // 任务类型
    const TASK_TYPE = {
        1: LANG.UI_VISUAL_BACKUP, // 备份
        2: LANG.UI_BACKUP_DATA_LABEL_SERVICE_REPLICATION, // 复制
        17: LANG.UI_VISUAL_COPY, // 副本
        19: LANG.UI_VISUAL_ARCHIVE_CHART // 归档
    };
    // 备份类型值
    const BACKUP_TASK_TYPE = [CONF.TASK_TYPE.BACKUP, CONF.TASK_TYPE.DB_CDP_BACKUP, CONF.TASK_TYPE.DB_BACKUP, CONF.TASK_TYPE.VOL_CDP_BACKUP, CONF.TASK_TYPE.OS_BACKUP, CONF.TASK_TYPE.NAS_BACKUP, CONF.TASK_TYPE.KUBE_BACKUP];
    // 时间点状态
    const POINT_STATUS = {
        1: LANG.UI_JOB_CROWD_NORMAL, // 正常
        2: LANG.UI_NODE_ABNORMAL // 异常
    }
    // 时间点操作状态
    const POINT_OPERATION_STATE = {
        1: LANG.UI_BACKUP_DATA_POINT_OPERATION_STATE_MERGING, // 合并中
        2: LANG.UI_BACKUP_DATA_POINT_OPERATION_STATE_DELETING, // 扫描中
        4: LANG.UI_BACKUP_DATA_POINT_OPERATION_STATE_SCANNING, // 删除中
        8: LANG.UI_BACKUP_DATA_POINT_OPERATION_STATE_CHECKING, // 检测中
    }
    $.fn.myFilter = function (options = {}) {
        const defaults = {
            showModule: true,
            showTaskType: true,
            showTaskStatus: false,
            showRealModule: true,
            showFixedModule: true,
            showAbnormal: false,
        };
        const settings = { ...defaults, ...options };
        const dom = $(this);
        let select_all_flag = false;
        let PERMISSION = CONF.PERMISSION;
        // 全局观察者只有查看权限未获得模块授权，需要显示所有模块类型
        if (CONF.PERMISSION.includes('global_observer')) {
            PERMISSION = CONF.GLOBAL_OBSERVER_CONFIG;
        }
        let show_backup_flag = SERVICE_BACKUP.some(module => PERMISSION.includes(module.id)); // 是否显示备份模块
        let show_cdp_flag = SERVICE_CDP.some(module => PERMISSION.includes(module.id)); // 是否显示cdp模块
        let show_replication_flag = SERVICE_REPLICATION.some(module => PERMISSION.includes(module.id) && module.id !== 'file_copy_protect'); // 是否显示数据复制模块
        let show_copy_flag = PERMISSION.includes('copy_protect'); // 是否显示副本任务类型
        let show_archive_flag = PERMISSION.includes('archive_new'); // 是否显示归档任务类型
        // ----------------------初始化html
        // 根据授权生成模块列表
        const getOptions = (list) => {
            let html = '';
            for (const element of list) {
                if (PERMISSION.includes(element.id)) {
                    let type_info = ``;
                    if (element.type == CONF.MODULE_TYPE.VOL_CDP) {
                        type_info = `data-storage_location="${element.storage_location}" data-dev_type="${element.dev_type}" data-task_type="${element.task_type}"`;
                    } else {
                        type_info = `data-task_type="${element.task_type}"`;
                    }
                    html += `<li><label><input type="checkbox" data-module_type="${element.type}" data-sub_module_type="${element.sub_type}" ${type_info}><span>${element.name}</span></label></li>`
                }
            }
            return html;
        };
        /**
         * 生成模块选项
         * @param {string} des 描述
         * @param {array} list 模块类型
         * @returns 
         */
        const getModuleOptions = (des, list) => {
            return `<div class="m-lr6 filter-module_type">
                        <div style="width: max-content;">
                            <span>${des}</span>
                            <ul class="filter-list">
                                ${getOptions(list)}
                            </ul>
                        </div>
                    </div>`
        };
        // 生成任务类型选项
        const getTaskOption = (list) => {
            let html = ``;
            for (const key of Object.keys(list)) {
                // 备份模块和实时备份模块都没授权，隐藏备份类型
                if(!show_backup_flag && !show_cdp_flag && key == 1){
                    continue;
                }
                // 备份模块没有授权，隐藏副本类型
                if(!show_copy_flag && key == 17){
                    continue;
                }
                // 复制模块没有授权，隐藏复制类型
                if(!show_replication_flag && key == 2){
                    continue;
                }
                // 归档模块没有授权，隐藏归档类型
                if(!show_archive_flag && key == 19){
                    continue;
                }
                html += `<li><label><input type="checkbox" value="${key}"><span>${list[key]}</span></label></li>`;
            }
            return `<div class="m-lr6 taskType">
                            <div>
                                <ul class="filter-list">
                                    <span>${LANG.UI_SEARCH_TASK_TYPE}</span>
                                    ${html}
                                </ul>
                            </div>
                        </div>`;
        };
        const getPointStatus = () => {
            return `<div id="task-filter-div">
                            <div class="m-lr6">
                                <div>
                                    <span>${LANG.UI_PUBLIC_STATUS}</span>
                                    <ul id="status" class="filter-list">
                                        ${getStatusOptions('status', POINT_STATUS)}
                                        ${getStatusOptions('opStatus', POINT_OPERATION_STATE)}
                                    </ul>
                                </div>
                            </div>
                        </div>`;

        }
        // 获取状态列表
        const getStatusOptions = (str, list) => {
            let html = '';
            for (const key of Object.keys(list)) {
                html += `<li class="${str}"><label><input type="checkbox" value="${key}"><span>${list[key]}</span></label></li>`;
            }
            return html;
        }
        const getFilterContent = () => {
            // 检查定时模块权限
            let html = `<div class="filter-div-wrapper">`;
            // 根据授权显示备份模块
            if (show_backup_flag && settings.showModule) {
                html += getModuleOptions(LANG.UI_BACKUP_DATA_LABEL_SERVICE_BACKUP, SERVICE_BACKUP);
            }
            // 根据授权显示连续数据保护模块
            if (show_cdp_flag && settings.showModule) {
                html += getModuleOptions(LANG.UI_REPORY_CDP, SERVICE_CDP);
            }
            // 根据授权显示连续数据保护模块
            if (show_replication_flag && settings.showModule) {
                let service_replication = SERVICE_REPLICATION.filter(item => item.id !== 'file_copy_protect');
                html += getModuleOptions(LANG.UI_BACKUP_DATA_LABEL_SERVICE_REPLICATION, service_replication);
            }
            // 生成任务类型过滤项
            if (settings.showTaskType) {
                html += getTaskOption(TASK_TYPE);
            }
            if (settings.showTaskStatus) {
                html += getPointStatus()
            }
            html += `</div>`;
            if (settings.showAbnormal) {
                html += `<div class="abnormal-chian-wrapper">
                            <span title="${LANG.UI_BACKUP_DATA_LABEL_ABNORMAL_CHAIN_TIPS}">${LANG.UI_BACKUP_DATA_LABEL_ABNORMAL_CHAIN}</span><input type="checkbox" id = "abnormal_chain_flag">
                        </div>`;
            }
            return html;
        };
        // 初始化过滤器插件
        const init = ($el) => {
            let html = `
                    <button class="btn-font btn-title p-lr8 btn-whitespace filterButton" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
						<i class="viconfont vicon-shaixuan"></i>
						<span>${LANG.UI_JOB_TASK_ORCHESTRATION_FILTER_NONE}</span>
					</button>
					<ul class="dropdown-menu filter-menu">
						<div class="content filterDiv" style="min-width: 200px;height: 450px;">
							<div class="filter-menu-title">
								<div class="filter-menu-head">
									<span class="filter-head-label">${LANG.UI_JOB_FILTER_OPTIONS}</span>
								<div>
									<label for="selectAll">
										<a class="selectAll">${LANG.UI_JOB_FILTER_SELECTALL}</a>
									</label>
									<span class="filter-head-gap">|</span>
									<label for="selectNone">
										<a class="selectNone">${LANG.UI_JOB_FILTER_CLEAR_NEW}</a>
									</label>
								</div>
							</div>
						</div>

						<div class="filter-content" style="height:350px;overflow:scroll">${getFilterContent()}</div>
						<div class="first-line">
							<button class="btn btn-sm filterCancel"><span>${LANG.UI_PUBLIC_CANCEL}</span></button>
							<button class="btn btn-sm filterSubmit" type="submit"><span>${LANG.UI_PUBLIC_CONFIRM}</span></button>
						</div>
					</ul>`
            $el.html(html);
        };
        // ----------------------初始化html
        // 获取过滤器参数
        const getFilter = function () {
            let taskType = [];
            let moduleType = [];
            let subModuleType = [];
            let status = [];
            let operationState = [];
            let param = { task_type: [] };
            // 筛选任务类型
            if (settings.showTaskType) {
                dom.find(`.filterDiv .taskType input[type=checkbox]`).each(function () {
                    let value = parseInt($(this).val());
                    if ($(this).prop("checked")) {
                        // 备份对应多个任务值，需要单独判断添加
                        if (value == 1) {
                            taskType.push(...BACKUP_TASK_TYPE);
                        } else if (value == 2) {
                            taskType.push(...[CONF.TASK_TYPE.VOL_CDP_REPLICATION, CONF.TASK_TYPE.FILE_COPY]);
                        } else if (value == 17) {
                            taskType.push(...[CONF.TASK_TYPE.BACKUP_COPY, CONF.TASK_TYPE.BACKUP_COPY_FETCH]);
                        } else if (value == 19) {
                            taskType.push(...[CONF.TASK_TYPE.ARCHIVE], CONF.TASK_TYPE.ARCHIVE_FETCH);
                        } else {
                            taskType.push(value);
                        }
                    }
                });
            }
            // 筛选模块类型
            if (settings.showModule) {
                dom.find(`.filterDiv .filter-module_type input[type=checkbox]`).each(function () {
                    let value = parseInt($(this).data('module_type'));
                    let sub_module = parseInt($(this).data('sub_module_type'));
                    if ($(this).prop("checked")) {
                        moduleType.push(value);
                        subModuleType.push(sub_module);
                        if (moduleType == CONF.MODULE_TYPE.VOL_CDP) {
                            param.storage_location = $(this).attr('data-storage_location');
                            param.dev_type = $(this).attr('data-dev_type');
                            param.task_type.concat($(this).attr('data-task_type'));
                        }
                    }
                });
            }
            // 筛选时间点状态
            if (settings.showTaskStatus) {
                dom.find(`.filterDiv .status input[type=checkbox]`).each(function () {
                    let value = parseInt($(this).val());
                    if ($(this).prop("checked")) {
                        status.push(value);
                    }
                });
                // 筛选操作状态
                dom.find(`.filterDiv .opStatus input[type=checkbox]`).each(function () {
                    let value = parseInt($(this).val());
                    if ($(this).prop("checked")) {
                        operationState.push(value);
                    }
                });
            }
            param.abnormal_chain_flag = dom.find(`.filterDiv #abnormal_chain_flag`).prop("checked");
            param.module_type = moduleType ?? [];
            param.sub_module_type = subModuleType ?? [];
            param.task_type.push(...taskType);
            param.status = status ?? [];
            param.operation_status = operationState ?? [];
            // 计算勾选数量
            let { selectCount, totalCount } = getSelectNum();
            // 判断是否是全选
            if (selectCount == totalCount) {
                select_all_flag = true;
                return {
                    select_all_flag: true,
                    module_type: [],
                    sub_module_type: [],
                    task_type: [],
                    status: [],
                    operation_status: [],
                };
            } else {
                select_all_flag = false;
            }
            param.select_all_flag = select_all_flag;
            showSelectNum();
            return param;
        };
        // 计算勾选数量
        const getSelectNum = () => {
            let selectCount = 0;
            let totalCount = 0;
            dom.find(` .filterDiv .filter-content .filter-list input[type=checkbox]`).each(function () {
                totalCount += 1;
                if ($(this).prop("checked")) {
                    selectCount += 1
                }
            });
            return { selectCount, totalCount }
        }
        // 显示勾选的过滤条件个数
        const showSelectNum = () => {
            let { selectCount, totalCount } = getSelectNum();
            let abnormal_chain_flag = dom.find(`.filterDiv #abnormal_chain_flag`).prop("checked");
            // 修改过滤条件描述
            if (abnormal_chain_flag) {
                dom.find(`.filterButton span`).html(LANG.UI_JOB_FILTER + `<span class="abnormal-chain-color"> (${LANG.UI_BACKUP_DATA_LABEL_ABNORMAL_CHAIN})</span>`);
                return true;
            }
            // 修改过滤条件描述
            if (selectCount > 0) {
                dom.find(`.filterButton span`).text(LANG.UI_JOB_FILTER + '(' + selectCount + '/' + totalCount + ')');
            } else {
                dom.find(`.filterButton span`).text(LANG.UI_JOB_FILTER + '(' + LANG.UI_JOB_FILTER_SELECTNONE + ')');
            }
        }
        // 过滤器点击事件
        const listener = () => {
            // 过滤器显示隐藏
            dom.find('.filterButton').on('click', showItemFilter);
            dom.find('.filterCancel').on('click', showItemFilter);
            dom.find('.filterSubmit').on('click', showItemFilter);
            // 全选和全不选
            dom.find('.selectAll').on('click', () => filterAll(true));
            dom.find('.selectNone').on('click', () => filterAll(false));
            //每次点击checkbox遍历选中的数量设置ui
            dom.find('.filterDiv .filter-content input[type=checkbox]').on('click', showSelectNum)
        };
        // 显示过滤器
        const showItemFilter = (event) => {
            const menu = dom.find('.filter-menu');
            const btn = dom.find('.filterButton');
            // 使用`===`进行严格比较
            if (menu.hasClass('show')) {
                // 添加类
                menu.removeClass('show');
                btn.removeClass('filter-active');
            } else {
                // 移除类
                menu.addClass('show');
                btn.addClass('filter-active');
            }
            event.preventDefault();
        };
        // 全选
        const filterAll = (flag) => {
            dom.find(`.filterDiv .filter-content input[type=checkbox]`).each(function () {
                $(`.filter-content .filter-list input[type=checkbox]`).prop("checked", flag);
            });
            select_all_flag = flag;
            // 修改显示数量
            showSelectNum();
        }
        return this.each(function () {
            const $this = $(this);
            const privateMethod = {
                getFilter: getFilter
            }
            // 将获取过滤参数暴露给外部使用
            $this.data('myFilter', privateMethod);
            // 初始化方法
            init($this);
            listener();
        });
    };
})(jQuery);