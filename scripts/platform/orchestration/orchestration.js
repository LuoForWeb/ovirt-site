/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 任务编排创建、修改任务相关操作
 * @Date: 2024-03-18 14:08:32
 * @LastEditTime: 2026-03-09 09:31:03
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */
var Orchestration = function () {
    let dataIndex = 1; // 阶段index
    let planList = []; // 存阶段序号
    let queryParams = { task_type: 1 }; // 获取任务列表参数
    let displayFlag = false; // 过滤器
    let totalCount = 0; // 过滤器总条数
    let selectCount = 0; // 勾选个数
    let tableInitFlag = false; // 任务列表初始化标志
    let addTaskIndex; //添加任务的section
    let task_numbers = 0; //总任务数量
    let editFlag = false; // 修改任务标志
    let originData = []; //修改任务的初始信息
    let verifyData = {}; // 创建修改消息
    let ORCHESTRATION_TIP = LANG.UI_JOB_TASK_ORCHESTRATION_ADD; // 修改或者创建的提示标题
    let selectIds = []; // 已选择的任务uuid，用于任务添加排除
    const WARN_TASK_NUM = 10;
    const BACKUP_MODE = [1, 2, 3, 4];
    const MODE_DISPLAY = ['', '', '', 'display-none', 'display-none']
    const BACKUP_TYPE = [CONF.TASK_TYPE.BACKUP, CONF.TASK_TYPE.OS_BACKUP, CONF.TASK_TYPE.NAS_BACKUP, CONF.TASK_TYPE.DB_BACKUP];
    // 无增量备份
    const DB_NO_INCR = [1, 5, 6, 7, 8, 10, 11, 12];
    // 无差异备份
    const DB_NO_DIFF = [3, 5, 6, 7, 8, 9, 10, 11, 12];
    // 驱动时间类型
    const EVENT_TYPE = {
        1: LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_SUCCESS_ALL,
        2: LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_SUCCESS_ANY,
        3: LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_SUCCESS_FEW,
        4: LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_FAILLE_ALL,
        5: LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_FAILLE_ANY,
        6: LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_FAILLE_FEW
    }
    // 触发事件类型
    const EVENT_MODE = {
        ALL_SUCCESS: 1,
        ANY_SUCCESS: 2,
        FEW_SUCCESS: 3,
        ALL_FAILURE: 4,
        ANY_FAILURE: 5,
        FEW_FAILURE: 6
    }
    // 模块授权字段->类型值
    const MODULE_PERMISSION = {
        "vmprotect": CONF.MODULE_TYPE.VM, // 虚拟机备份
        "prcloud_protect": CONF.MODULE_TYPE.VM, // 私有云
        "awsprotect": CONF.MODULE_TYPE.VM, // 公有云
        "k8s_protect": CONF.MODULE_TYPE.KUBERNETES,
        "fileprotect": CONF.MODULE_TYPE.FS, // 文件
        "nas_protect": CONF.MODULE_TYPE.NAS, // nas
        "obs_protect": CONF.MODULE_TYPE.FS, // 对象存储
        "hadoop_protect": CONF.MODULE_TYPE.FS, // hadoop
        "db_protect": CONF.MODULE_TYPE.DB, // 数据库
        "office365_protect": CONF.MODULE_TYPE.M365, // exchange
        "complete_machine": CONF.MODULE_TYPE.OS, // 整机
        "osbackup": CONF.MODULE_TYPE.OS, // 卷
    };
    // 授权字段->名称
    const MODULE_NAME = {
        'vmprotect': LANG.UI_BACKUP_DATA_MODULE_VM,// 虚拟机
        'prcloud_protect': LANG.UI_PUBLIC_PRIVATE_CLOUD,// 私有云
        'awsprotect': LANG.UI_PUBLIC_PUBLIC_CLOUD, // 公有云
        'k8s_protect': LANG.UI_BACKUP_DATA_MODULE_K8S, // kubernetes
        'fileprotect': LANG.UI_VISUAL_FILE, // 文件
        'nas_protect': LANG.UI_BACKUP_DATA_MODULE_NAS, // nas
        'obs_protect': LANG.UI_BACKUP_DATA_MODULE_OBS, // 对象存储
        'hadoop_protect': LANG.UI_BACKUP_DATA_MODULE_HADOOP, // hadoop
        'db_protect': LANG.UI_BACKUP_DATA_MODULE_DB, // 数据库
        'office365_protect': LANG.UI_BACKUP_DATA_MODULE_M365, // exchange
        'complete_machine': LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS, // 整机
        'osbackup': LANG.UI_COPY_MODULE_LABEL_REEL_OS, // 卷
    };
    // 虚拟机子模块  授权字段->类型值
    const VM_SUB_MODULE = {
        'vmprotect': CONF.VM_SUB_MODULE.VM,
        'prcloud_protect': CONF.VM_SUB_MODULE.PRIVATE_CLOUD,
        'awsprotect': CONF.VM_SUB_MODULE.PUBLIC_CLOUD,
    }
    // 文件子模块  授权字段->类型值
    const FS_SUB_MODULE = {
        'fileprotect': CONF.SUBMODULE_TYPE.FS,
        'nas_protect': CONF.SUBMODULE_TYPE.NAS,
        'obs_protect': CONF.SUBMODULE_TYPE.OBS,
        'hadoop_protect': CONF.SUBMODULE_TYPE.HADOOP,
    }
    // 状态列表id
    const STATUS_LIST = {
        WAIT: 1,
        RUNNING: 2,
        STOPPED: 4,
        STOPPING: 5,
        ABNORMAL: 7,
        ERROR: 8,
        SUCCESS: 17,
    };
    // 状态对应class
    const STATUS_CLASS = {
        WAIT: 'info',
        RUNNING: 'success',
        STOPPED: 'default',
        STOPPING: 'info',
        ABNORMAL: 'warning',
        ERROR: 'danger',
        SUCCESS: 'success',
    };
    // 状态描述文字
    const STATUS_DES = {
        WAIT: LANG.UI_VISUAL_WAIT,
        RUNNING: LANG.UI_PUBLIC_RUNNING,
        STOPPED: LANG.UI_VISUAL_STOP,
        STOPPING: LANG.UI_VISUAL_STOPPING,
        ABNORMAL: LANG.UI_VISUAL_NODE_ABNORMAL,
        ERROR: LANG.UI_VISUAL_ERROR,
        SUCCESS: LANG.UI_VISUAL_SUCCESS,
    }
    const MODULE_IMG = {
        2: 'vicon-module_vm',
        3: 'vicon-module_file',
        4: 'vicon-module_db',
        5: 'vicon-module_os-copy',
        10: 'vicon-module_vol-cdp',
        11: 'vicon-module_nas',
        16: 'vicon-module_vm',
        22: 'vicon-module_vm',
        26: 'vicon-module_file',
        14: 'vicon-a-Group1000002961',
        999: 'vicon-a-Group1000002958',
        998: 'vicon-a-Group1000002959',
    }
    const COPY_IMG = 'vicon-module_copy';
    const DATA_VERIFY = 'vicon-a-Group1000002960';

    // 各个备份模式列表 mode -> 名称
    const MODE_LIST = {
        1: LANG.UI_PUBLIC_BACKUP_FULL, // 完全备份
        2: LANG.UI_PUBLIC_BACKUP_INCREMENT, // 增量备份
        3: LANG.UI_PUBLIC_BACKUP_DIFFRENCE, // 差异备份
        4: LANG.UI_JOB_TASK_ORCHESTRATION_ARCHIVE_LOG_BACKUP, // 日志备份
        // 9: LANG.UI_PUBLIC_PERMANENT_INCREMENT, // 永久增量
    }
    // 初始化名称
    let initDefaultNames = () => {
        let nameBack = (res) => {
            if (res.success) {
                $('#planName').val(res.data.name);
            }
        };
        pAjaxRequest({}, "/api/v1/orchestration/plan/name", "GET", nameBack, true);
    };
    // 初始化数据
    let init = () => {
        $(".popovers").popover();
        // 通过url是否包含plan_uuid判断是创建还是修改
        if ($('#plan_uuid').val()) {
            editFlag = true;
            ORCHESTRATION_TIP = LANG.UI_JOB_TASK_ORCHESTRATION_MODIFY;
        } else {
            editFlag = false;
        };
        initData();
        // 修改编排计划
        if (editFlag) {
            let plan_uuid = $('#plan_uuid').val();
            let infoBack = (res) => {
                originData = res.data;
                $('#planName').val(originData.plan_nickname);
                // 初始化阶段信息
                setSectionInfo(originData);
                // 初始化时间策略
                initStrategy(originData);
            };
            pAjaxRequest({ plan_uuid: plan_uuid }, "/api/v1/orchestration/details", "GET", infoBack, true);
        }
    };
    let initData = () => {
        $('#spinnerTime').spinner({ value: 30, step: 5, min: 1, max: 999 });
        var el = document.getElementById('itemList1');
        // 初始化第一个阶段任务列表为可拖动框
        var sortable = new Sortable(el, {
            group: 'shared', //可跨区域拖动
            animation: 150,
            onRemove: function (item) { // 拖动时清除相关事件触发的选择
                let uuid = $(item.item).data('uuid');
                $('#time' + uuid).remove(); // 去掉时间策略的选项
                $('#task' + uuid).remove(); // 去掉驱动任务的选项
                $(item.to).find('.empty_tips-div').remove(); // 删除多余的提示
                let toId = $(item.to).attr('id').replace('itemList', '');
                changeTotal(1);
                changeTotal(toId);
            },
        });
        // 初始化时间策略
        initStrategy();
        // 初始化默认名称

        if (!editFlag) {
            initDefaultNames();
        }

        $('#backupMode').empty().append(getModeButton(BACKUP_MODE, MODE_LIST));
        $('.icheck').iCheck({ checkboxClass: 'icheckbox_square-blue' });

        //选择备份策略复选框
        $('#backupMode').find('.icheck').on('ifClicked', timeModeClick);
    };
    // 组合时间策略类型勾选框
    const getModeButton = (mode, list) => {
        // 根据传入的mode生成复选框
        let html = "";
        mode.forEach((element, index) => {
            html += `<label style="padding-right: 20px;" class="backup_mode${element} ${MODE_DISPLAY[index]}"><input type="checkbox" data-checkbox="icheckbox_square-blue" data-mode="${element}" class="icheck">${list[element]}</label>`
        });
        return html;
    }

    // 事件
    let listeners = () => {
        // 添加阶段
        $('#addSection').on('click', () => { addPlanSection(true) });
        // 按任务类型选择任务
        $('#taskType').on('change', function () {
            let taskType = parseInt($(this).val());
            queryParams.task_type = taskType;
            $('#dataTable').bootstrapTable('refresh', {
                query: deepCloneObject(queryParams)
            });
            // 备份任务显示备份模式
            if (taskType == 1) {
                $('.backup-mode-group').show();
                $('#backupMode').empty().append(getModeButton(BACKUP_MODE, MODE_LIST));

                //选择备份策略复选框
                $('#backupMode').find('.icheck').on('ifClicked', timeModeClick);
            } else {
                $('.backup-mode-group').hide();
            }
            $('#backupMode .icheck').iCheck({ checkboxClass: 'icheckbox_square-blue' });
        });
        // 添加任务抽屉
        $(`#addTaskBtn`).on('click', function () {
            addTaskIndex = parseInt($(this).data('index'));
            const zIndex = $('#addTaskDrawer').css('z-index');
            $('#addTaskDrawer').drawer('toggle');
            // 3.给新的蒙层添加z-index
            $('.drawer-backdrop').attr('style', `z-index: ${zIndex - 1}`);
            initTaskTable(); // 初始化任务列表
        });
        // 确认添加任务
        $('.btn-addTask').on('click', addTaskItem);
        // 配置时间策略
        $('#timeSelect').on('change', setTimeStrategy);
        // 返回页面
        $('#cancelBtn').on('click', () => {
            LOCATION('./content/platform/jobs/jobs.php?tab=2', 'task');
        });
        // 创建编排计划
        $('#addSubmit').on('click', submit);

        //点击过滤菜单外关闭过滤菜单
        $(document).on('click', function (e) {
            if ($(e.target).closest('#filterWrapper').length > 0) {
            } else {
                // 关闭弹框
                $('#orchestrationTaskToolbar #taskFilters').removeClass('show');
                $('#orchestrationTaskToolbar #filterButton').removeClass('filter-hover');
                $('#orchestrationTaskToolbar #filterButton').removeClass('filter-active');
                displayFlag = false;
            };
        });
    };

    /**
     * 添加一个新的计划部分
     * @param {boolean} scrollFlag - 控制是否滚动到新添加的部分，默认为false
     * 无返回值
     */
    const addPlanSection = (scrollFlag = false) => {
        // 增加 dataIndex 和更新 planList，以记录新的计划部分
        dataIndex++;
        planList.push(planList[planList.length - 1] + 1); // 记录阶段数量
        newSection(dataIndex); // 动态添加新的 section

        // 对 DOM 进行操作，添加新的部分并处理滚动
        const el = document.querySelector('.section-wrapper');
        if (el) {
            // 如果存在 .section-wrapper 元素，进行滚动操作（如果需要）
            if (scrollFlag) {
                // 使用 requestAnimationFrame 优化性能，平滑滚动到新添加的部分
                requestAnimationFrame(() => {
                    el.scrollLeft += el.clientWidth;
                });
            }
        }
    };

    /**
    * 初始化时间选择器。
    * 该函数为指定的元素初始化一个时间选择器，允许用户以指定的格式选择时间。
    * 
    * @param {string} id - 用于构建时间选择器元素ID的字符串。该ID将被附加到预定义的字符串上以唯一标识时间选择器。
    * 
    * 注意：此函数依赖于jQuery和timepicker插件。
    */
    let initTimepicker = (id) => {
        // 为指定ID的元素初始化时间选择器，配置时间格式、默认时间、自动关闭、分钟步进、显示秒数和是否显示上午/下午标记
        $(`#eventAccordion${id} .interval-time`).timepicker({
            timeFormat: 'HH:mm:ss', // 时间格式：小时:分钟:秒
            defaultTime: '00:30:00', // 默认时间设置为00:30:00
            autoclose: true, // 选择时间后自动关闭时间选择器
            minuteStep: 5, // 分钟步进值为5分钟
            showSeconds: true, // 显示秒数
            showMeridian: false, // 不显示上午/下午标记
        });
    }
    /**
     * 初始化select选择器
     * @param {string} id - 用于拼接select元素id的字符串
     * 该函数没有返回值
     */
    let initSelectPicker = (id) => {
        // 使用jQuery选择器初始化selectpicker，设置多选框的文本和行为
        $(`#multiple_select${id}`).selectpicker({
            noneSelectedText: LANG.BILLING_PLEASE_SELECT, // 未选择时的文本
            deselectAllText: LANG.BILLING_DESELECT_ALL, // 取消全选时的文本
            selectAllText: LANG.BILLING_SELECT_ALL, // 全选时的文本
            liveSearchPlaceholder: LANG.BILLING_SEARCH, // 搜索框的占位符文本
        });
    }
    /**
    * 初始化可排序列表。
    * @param {string} id - 列表的唯一标识符后缀。
    * 
    * 该函数创建一个Sortable实例，使指定的列表项可以进行排序，并在移除项时动态更新相关的UI元素。
    */
    let initSortable = (id) => {
        // 获取列表项的容器
        let itemWrapper = $(`#itemList${id}`);
        // 初始化Sortable实例
        var sortable = new Sortable(itemWrapper[0], {
            group: 'shared', // 设置为共享组，允许跨列表移动项
            animation: 150, // 移动动画的持续时间
            onRemove: function (item) {
                // 获取移除项的数据属性
                let timeDes = $(item.item).data('des');
                let timeId = $(item.item).data('strategy');
                let uuid = $(item.item).data('uuid');
                // 移除对应的DOM元素
                $('#task' + uuid).remove();
                // 如果移除的项被移动到“itemList1”容器中，动态添加一个选项到select元素
                if ($(item.to).attr('id') == 'itemList1') {
                    let strategySelect = $('#strategySelect');
                    strategySelect.append(`<option value="${timeId}" id="time${uuid}">${timeDes}</option>`);
                }
                $(item.to).find('.empty_tips-div').remove(); // 删除多余的提示
                let toId = $(item.to).attr('id').replace('itemList', '');
                changeTotal(id);
                changeTotal(toId);
            },
        });
    }
    let changeTotal = (id) => {
        let itemWrapper = $(`#itemList${id}`);
        let totalWarn = $(`#totalWarn${id}`);
        let totalTask = itemWrapper.find('.card-item').length;
        let totalDiv = totalWarn.find('.total');
        totalDiv.html(totalTask)
        if (totalTask <= WARN_TASK_NUM) {
            totalWarn.find('.warn-tips').hide();
        } else {
            totalWarn.find('.warn-tips').show();
        }
    }
    let initICheck = (id) => {
        $(`#eventMode${id} .icheck`).iCheck({ checkboxClass: 'icheckbox_square-blue' });
    }
    // 获取触发事件选项
    let getOptions = () => {
        let html = '';
        for (let key in EVENT_TYPE) {
            if (EVENT_TYPE.hasOwnProperty(key)) {
                html += `<option value="${key}">${EVENT_TYPE[key]}</option>`
            }
        }
        return html;
    }
    // 创建新阶段
    let newSection = (id) => {
        // 个位数 加0 补足两位数
        let number = id < 10 ? '0' + id : id;
        let content = `<div id="next-vector${id}"><div class="next-vector"></div></div>
                <div class="section-card" id="sectionCard${id}">
                <div class="plan-section">
                    <div class="form-group section-header">
                        <div class="header-add col-md-12">
                            <div class="card-button col-md-10">
                                <button type="button" id="addTaskBtn${id}" class="btn green-haze btn-add" data-index="${id}"><i class="viconfont vicon-ge_add_task mr5"></i>` + LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS + `</button>
                            </div>
                            <div class="card-index col-md-2"><span>${number}</span></div>
                        </div>
                    </div>
                    <!-- 触发条件 -->
                    <div class="section-event">
                        <div class="form-group">
                            <div class="form-group-content col-md-12 eventMode_en" id="eventMode${id}">
                                <div style="display: flex;align-items: center;width: 100%;" class="eventmode_en user-width60_en">
                                    <label class="event-label">` + LANG.UI_JOB_TASK_ORCHESTRATION_DRIVE_LABEL + `</label>
                                    <!-- 事件触发 -->
                                    <label class="drive-label"><input type="checkbox" id="eventDrive" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck">` + LANG.UI_JOB_TASK_ORCHESTRATION_DRIVE_EVENT + `</label>
                                    <!-- 时间触发 -->
                                    <label class="drive-label"><input type="checkbox" id="timeDrive" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck">` + LANG.UI_JOB_TASK_ORCHESTRATION_DRIVE_TIME + `</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group drive-time-wrapper" id="eventAccordion${id}">
                            <div class="form-group event-panel display-none col-md-12" data-mode="2">
                                <div style="display: flex;align-items: center;width: 100%;">
                                    <label class="event-label padTop7p">` + LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_LIST + `</label>
                                    <select class="bs-select form-control eventCheck" id="eventCheck${id}" style="flex:1;">${getOptions()}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group drive-event-wrapper display-none padTop7p taskList" id="taskList${id}">
                                <div class="form-group-content col-md-12">
                                    <div style="display: flex;align-items: center;width: 100%;">
                                        <label class="event-label">` + LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_SELECT_SOME + `</label>
                                        <select class="form-control select2me selectpicker show-tick ignore multiple_select" multiple data-live-search="true" data-actions-box="true" id="multiple_select${id}">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group event-panel display-none col-md-12" data-mode="1">
                                <div style="display: flex;align-items: center;width: 100%;">
                                    <label class="event-label padTop7p">` + LANG.UI_JOB_TASK_ORCHESTRATION_TIME_INTERVAL + `</label>
                                    <div class="input-group" style="flex:1;">
                                        <input type="text" value="00:30:00" class="form-control timepicker timepicker-24 interval-time">
                                        <span class="input-group-btn">
                                            <button class="btn default btn-time" type="button">
                                            <i class="viconfont vicon-beifenshijiandian"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <div class="form-group-content col-md-12">
                                <div style="display: flex;align-items: center;width: 100%;">
                                    <label class="event-label">` + LANG.UI_JOB_ORCHESTRATION_SECTION_REMARK + `</label>
                                    <div class="input-group" style="flex:1"><input type="text" class="form-control plan-remarks" maxlength="64"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group section-body">
                        <div class="card-items-list"  id="itemList${id}">                       
                            <div class="empty_tips-div">` + LANG.UI_JOB_TASK_ORCHESTRATION_ADD_TASK_TIPS + `...</div>
                        </div>
                    </div>
                    <div class="task_total-div" id ="totalWarn${id}"><span>${LANG.UI_JOB_ORCHESTRATION_TASK_NUM}: </span><span class="total">0</span><span class="warn-tips display-none">   ` + LANG.UI_JOB_TASK_ORCHESTRATION_WARN_TIPS + `</span></div>
                </div>
                <div id="delSection${id}" class="delSection" data-index="${id}">×</div>
            </div>`;
        let element = document.querySelector('.add-section-before');
        element.insertAdjacentHTML('beforebegin', content);

        // 初始化页面
        // 初始化多选选项框
        initSelectPicker(id);
        // 初始化时间选择器
        initTimepicker(id);
        // 初始化icheck勾选框
        initICheck(id);
        // 初始化拖拽框
        initSortable(id);

        // 绑定事件
        // 时间选择器点击展开
        $(`#eventAccordion${id} .input-group`).on('click', (e) => {
            e.preventDefault();
            $(`#eventAccordion${id} .interval-time`).timepicker('showWidget');
        });
        $(`#eventAccordion${id} .interval-time`).on('blur', function () {
            let val = $(this).val();
            if (val == "" || val == null) {
                $(this).val("0:30:00");
            };
        })
        // 清除依赖任务删除的错误提示类
        $(`.drive-event-wrapper`).on('click', () => {
            $(`#taskList${id}`).removeClass('has-error');
        })
        // 修改section序号
        modifyNum();
        // 添加任务
        $(`#addTaskBtn${id}`).on('click', function () {
            addTaskIndex = parseInt($(this).data('index'));
            // 避免多模态框蒙层显示异常情况
            // 1.清除背景蒙层
            // 2.获取抽屉的z-index
            const zIndex = $('#addTaskDrawer').css('z-index');
            $('#addTaskDrawer').drawer('toggle');
            // 3.给新的蒙层添加z-index
            $('.drawer-backdrop').attr('style', `z-index: ${zIndex - 1}`);
            initTaskTable();
        });
        // 初始化勾选框
        $(`#eventMode${id} .icheck`).iCheck({ checkboxClass: 'icheckbox_square-blue' });
        // 初始化间隔时间
        $(`#spinnerTime${id}`).spinner({ value: 30, step: 5, min: 1, max: 999 });
        // 触发时间选择
        $(`#eventMode${id}`).find('.icheck').on('ifClicked', function (event) {
            let mode = parseInt($(this).data('mode'));
            if (event.target.checked) {
                //如果是取消选中
                $(`#eventAccordion${id}`).find('.event-panel[data-mode=' + mode + ']').hide();
            } else {
                //如果是选中
                $(`#eventAccordion${id}`).find('.event-panel[data-mode=' + mode + ']').show();
            }
            switch (mode) {
                case 2:
                    if (event.target.checked) {
                        $(`#taskList${id}`).hide();
                    } else {
                        let value = parseInt($(`#eventCheck${id}`).val());
                        // 某些成功和某些失败展示驱动任务选择
                        if (value == EVENT_MODE.FEW_FAILURE || value == EVENT_MODE.FEW_SUCCESS) {
                            $(`#taskList${id}`).show();
                        } else {
                            $(`#taskList${id}`).hide();
                        };
                    }
                    break;
            }
        });
        //成功失败事件
        $(`#eventCheck${id}`).on('change', function () {
            let value = parseInt($(this).val());
            // 某些成功和某些失败展示驱动任务选择
            if (value == 3 || value == 6) {
                $(`#taskList${id}`).show();
            } else {
                $(`#taskList${id}`).hide();
            };
        })
        // 删除阶段
        $(`#delSection${id}`).on('click', () => {
            $(`#next-vector${id}`).remove(); // 移除箭头指示
            $(`#sectionCard${id}`).remove(); // 移除阶段框
            modifyNum(); // 删除节点时，修改阶段的序号
        })
        //初始化任务选择列表
        $(`#taskList${id} .show-tick`).on('click', () => {
            let select = $(`#multiple_select${id}`);
            select.empty(); // 清空任务驱动选择框
            // 重新根据上一个阶段初始化，驱动任务选择列表
            let itemList = document.querySelectorAll(`#itemList${id - 1} .card-item-wrapper`);
            itemList.forEach((item) => {
                let taskId = $(item).data('uuid');
                let name = $(item).data('name');
                // 增加option
                select.append(`<option value="${taskId}" id="task${taskId}">${name}</option>`);
            });
            // 刷新多选插件
            select.selectpicker('refresh');
        });
        if (editFlag) {
            $('.empty_tips-div').remove();
        }
    };
    // 删除或增加修改阶段序号
    let modifyNum = () => {
        // 修改section序号
        let numDom = document.querySelectorAll('.card-index span');
        numDom.forEach(function (d, i) {
            i = i + 1;
            let num = i.toString().padStart(2, '0'); // 更高效地添加前导0
            d.innerHTML = num; // 直接使用原生JavaScript操作DOM
        });
    };
    // 初始化任务列表
    let initTaskTable = () => {
        queryParams.editFlag = editFlag;
        queryParams.plan_uuid = $('#plan_uuid').val() ?? '';
        let options = {
            vin_url: '/api/v1/orchestration/task',
            vin_method: 'POST',
            vin_params: function () {
                return queryParams;
            },
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            resizable: true, //可变宽度
            sortName: 'create_time', // 默认排序
            sortOrder: 'desc',
            uniqueId: 'task_uuid', //
            dateTimePicker: {
                id: 'dateRangePickerOrchestration'
            }, //时间选择器// 勾选框联动效果
            toolbarId: '#orchestrationTaskToolbar',
            searchInput: true, //搜索框
            placeholder: LANG.UI_JOB_SEARCH_JOB_NAME,
            searchClass: 'search_task-input', //搜索框类名
            searchSelector: '.search_task-input', //表格选择使用该搜索框
            customTool: {
                afterInput: `<div class="btn-group" id="filterWrapper">
                                <button id="filterButton" class="btn-font btn-title p-lr8">
                                    <i class="viconfont vicon-shaixuan"></i>
                                    <span>${LANG.UI_JOB_TASK_ORCHESTRATION_FILTER_NONE}</span>
                                </button>
                                <ul class="dropdown-menu" id="taskFilters" aria-haspopup="true">
                                    <div class="content" id="filterDiv">
                                        <div class="" style="width: inherit;height: 42px;border-bottom: 1px solid #F1F3F5;padding:12px 16px 12px 16px">
                                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                                <span style="display:inline-block;font-size: 14px;font-weight: 400;color: #1D1E26;line-height: 16px;">${LANG.UI_JOB_FILTER_OPTIONS}</span>
                                                <div>
                                                    <label for="selectAll">
                                                        <a id="selectAll" style="font-size: 12px;">${LANG.UI_JOB_FILTER_SELECTALL}</a>
                                                    </label>
                                                    <span style="color: #E4E4E4;margin:0 4px 0 4px">|</span>
                                                    <label for="selectNone">
                                                        <a id="selectNone" style="font-size: 12px;">${LANG.UI_JOB_FILTER_CLEAR_NEW}</a>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="filter-content" style="width: inherit;height: auto;">
                                        </div>
                                        <div class="first-line" style="width: inherit;height: 56px;border-top: 1px solid #F1F3F5;display:flex;justify-content:flex-end">
                                            <button id="filterCancel" class="btn btn-sm filtertable_btn_en"><span>${LANG.UI_PUBLIC_CANCEL}</span></button>
                                            <button id='filterSubmit' class="btn btn-sm filtertable_btn_en" type="submit" style="border: 0;"><span>${LANG.UI_PUBLIC_CONFIRM}</span></button>
                                        </div>
                                </ul>
                            </div>`,
            },
            onCheck: () => {
                // 单行勾选
                let selectedRow = $('#dataTable').bootstrapTable("getSelections");
                showBackupMode(selectedRow);
            },
            onUncheck: () => {
                // 取消勾选单行
                let selectedRow = $('#dataTable').bootstrapTable("getSelections");
                showBackupMode(selectedRow);
            },
            onUncheckAll: () => {
                let selectedRow = $('#dataTable').bootstrapTable("getSelections");
                showBackupMode(selectedRow);
            },
            onCheckAll: () => {
                let selectedRow = $('#dataTable').bootstrapTable("getSelections");
                showBackupMode(selectedRow);
            },
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                    formatter: (value, row, index) => {
                        // 如果任务已经被添加，则不能再被添加
                        if (row.task_orchestration_plan_flag || selectIds.includes(row.task_uuid)) {
                            return {
                                disabled: true,
                            }
                        }
                    },
                },
                {
                    field: 'task_name',
                    title: LANG.UI_GLOBAL_STRATEGY_TASK_NAME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'module_type',
                    title: LANG.UI_GLOBAL_STRATEGY_MODULE_TYPE,
                    sortable: false,
                    align: 'center',
                    formatter: (index, row) => {
                        return row.module_type_des;
                    }
                },
                {
                    field: 'task_type_des',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'create_time',
                    title: LANG.UI_GLOBAL_STRATEGY_UPDATE_TIME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    sortable: true,
                    align: 'center',
                    formatter: (index, row) => {
                        switch (row.status) {
                            case CONF.TASK_STATUS.WAITTING:
                                return '<span class="label label-sm label-info label-info_en">' + LANG.UI_PUBLIC_WAIT + '</span>';
                            case CONF.TASK_STATUS.STOPPING:
                                return '<span class="label label-sm label-info label-info_en">' + LANG.UI_PUBLIC_STOPPING + '</span>';

                            case CONF.TASK_STATUS.PREPARING:
                                return '<span class="label label-sm label-info label-info_en">' + LANG.UI_PUBLIC_READYING + '</span>';

                            case CONF.TASK_STATUS.RUNNING:
                                return '<span class="label label-sm label-success label-success_en">' + LANG.UI_PUBLIC_RUNNING + '</span>';
                            case CONF.TASK_STATUS.FINISHED:
                                return '<span class="label label-sm label-success label-success_en">' + LANG.UI_VISUAL_ALREADY_FINISH + '</span>';
                            case CONF.TASK_STATUS.PAUSED:
                                return '<span class="label label-sm label-success label-success_en">' + LANG.UI_JOB_PAUSE + '</span>';

                            case CONF.TASK_STATUS.SUCCESSED:
                                return '<span class="label label-sm label-success label-success_en">' + LANG.UI_PUBLIC_SUCCESS + '</span>';
                            case CONF.TASK_STATUS.STARTING:
                                return '<span class="label label-sm label-success label-success_en">' + LANG.UI_PUBLIC_STARTING + '</span>';

                            case CONF.TASK_STATUS.STOPPED:
                                return '<span class="label label-sm label-default label-default_en">' + LANG.UI_VISUAL_STOP + '</span>';
                            case CONF.TASK_STATUS.ABNORMAL:
                                return '<span class="label label-sm label-warning label-warning_en">' + LANG.UI_NODE_ABNORMAL + '</span>';
                            case CONF.TASK_STATUS.NETWORK_FAULT:
                                return '<span class="label label-sm label-danger label-danger_en" style="width:auto; min-width:40px">' + LANG.UI_PUBLIC_NETWORK_ERROR + '</span>';
                            case CONF.TASK_STATUS.CREATING:
                                return '<span class="label label-sm label-default label-default_en">' + LANG.UI_PUBLIC_CREATING + '</span>';
                            case CONF.TASK_STATUS.PENDING:
                                return '<span class="label label-sm label-warning label-warning_en">' + LANG.UI_PUBLIC_PENDING + '</span>';
                            case CONF.TASK_STATUS.ERROR:
                                return '<span class="label label-sm label-danger label-danger_en" style="width:auto; min-width:40px">' + LANG.UI_PUBLIC_FAILED + '</span>';
                            default:
                                // return '<span class="label label-sm label-info  ">准备中</span>';
                                break;
                        }
                    }
                },
            ]
        }
        if (!tableInitFlag) {
            selectIds = getTaskUuids();
            $('#dataTable').baseTableConfig().init(options);
            tableInitFlag = true;
        } else {
            selectIds = getTaskUuids();
            queryParams.task_type = parseInt($('#taskType').val());
            $('#dataTable').bootstrapTable('refresh', {
                query: queryParams
            });
        }
        // 点击日期，关闭筛选框
        $('#dateRangePickerOrchestration').on('click', () => {
            $('#orchestrationTaskToolbar #filterButton').removeClass('filter-active');
            $('#orchestrationTaskToolbar #taskFilters').removeClass('show');
        });
        $('#orchestrationTaskToolbar #filterButton').on('click', showFilter);
        // 过滤全选
        $('#orchestrationTaskToolbar #selectAll').on('click', filterAll);
        // 过滤反选
        $('#orchestrationTaskToolbar #selectNone').on('click', filterNone);
        // 过滤取消
        $('#filterCancel').on('click', (event) => {
            $('#taskFilters').removeClass('show');
            $('#filterButton').css('background-color', '#fff');
            displayFlag = false;
            event.preventDefault();
        });
        // 过滤确认
        $('#filterSubmit').off('click').on('click', (event) => {
            getFilterParams();
            $('#dataTable').bootstrapTable('refresh', {
                query: queryParams
            });
            // 关闭弹框
            $('#orchestrationTaskToolbar #taskFilters').removeClass('show');
            $('#orchestrationTaskToolbar #filterButton').removeClass('filter-hover');
            $('#orchestrationTaskToolbar #filterButton').removeClass('filter-active');
            event.preventDefault();
        });
        // 表格搜索监听事件
        $('#orchestrationTaskToolbar .search-btn').on('click',()=>{
            queryParams.search = $('.search_task-input').val();
            $('#dataTable').bootstrapTable('refresh');
        })
        $('#orchestrationTaskToolbar .clear').on('click',()=>{
            queryParams.search = '';
            $('.search_task-input').val('')
            $('#dataTable').bootstrapTable('refresh');
        })
        $('.search_task-input').keypress(function(event){
            if(event.which == 13){
                queryParams.search = $('.search_task-input').val();
            }
        })
        addFilterContent();
        initTaskTableHeight();
    };
    // 显示过滤器
    const showFilter = (event) => {
        const $taskFilters = $('#orchestrationTaskToolbar #taskFilters');
        const $filterButton = $('#orchestrationTaskToolbar #filterButton');
        // 使用`===`进行严格比较
        if (displayFlag === false) {
            // 添加类
            $taskFilters.addClass('show');
            $filterButton.addClass('filter-active');
            displayFlag = true;
        } else {
            // 移除类
            $taskFilters.removeClass('show');
            $filterButton.removeClass('filter-active');
            displayFlag = false;
        }
        event.preventDefault();
    };
    // 过滤器全选
    let filterAll = () => {
        filterFlag = false;
        // 使用querySelectorAll和forEach
        document.querySelectorAll('#orchestrationTaskToolbar #filterDiv .filter-content input[type=checkbox]').forEach((checkbox) => {
            checkbox.checked = true; // 直接设置复选框的选中状态
            totalCount++; // 计算选中复选框的数量
        });

        // 更新UI，文本的更新
        let filterButtonText = LANG.UI_JOB_FILTER + '(' + totalCount + '/' + totalCount + ')';
        document.querySelector('#orchestrationTaskToolbar #filterButton span').textContent = filterButtonText;

        // 重置selectCount和totalCount
        selectCount = 0;
        totalCount = 0;
    };
    // 过滤器反选
    let filterNone = () => {
        filterFlag = false;
        $('#orchestrationTaskToolbar #filterDiv .filter-content input[type=checkbox]').each(() => {
            $('#orchestrationTaskToolbar #filterDiv .filter-content input[type=checkbox]').prop("checked", false);
        });
        $('#orchestrationTaskToolbar #filterButton span').text(LANG.UI_JOB_TASK_ORCHESTRATION_FILTER_NONE);
        selectCount = 0;
        totalCount = 0;
    };
    // 修改过滤选择个数
    let filterOne = () => {
        $('#orchestrationTaskToolbar #filterDiv .filter-content input[type=checkbox]').each(function () {
            totalCount += 1;
            if ($(this).prop("checked")) {
                selectCount += 1
            }
        });
        if (selectCount > 0) {
            $('#orchestrationTaskToolbar #filterButton span').text(LANG.UI_JOB_FILTER + '(' + selectCount + '/' + totalCount + ')');
        } else {
            $('#orchestrationTaskToolbar #filterButton span').text(LANG.UI_JOB_TASK_ORCHESTRATION_FILTER_NONE);
        }
        totalCount = 0;
        selectCount = 0;
    };
    // 获取过滤器参数
    let getFilterParams = () => {
        // 勾选状态
        let jobStatus = [];
        let moduleType = [];
        let subModuleType = [];
        $('#orchestrationTaskToolbar #filterDiv #job_status input[type=checkbox]').each(function () {
            if ($(this).prop("checked")) {
                jobStatus.push(parseInt($(this).val()));
            }
        });
        queryParams.status = jobStatus;
        // 勾选模块
        $('#orchestrationTaskToolbar #filterDiv #module_type input[type=checkbox]').each(function () {
            let value = parseInt($(this).val());
            let sub_module = parseInt($(this).data('sub_module'));
            if ($(this).prop("checked")) {
                moduleType.push(value);
                subModuleType.push(sub_module);
            }
        });
        queryParams.module_type = moduleType;
        queryParams.sub_module_type = subModuleType;
        queryParams.task_type = parseInt($('#taskType').val());
    };
    // 添加过滤内容
    let addFilterContent = () => {
        let filterContent = `
                <div id="filter-content-div" style="padding:8px 10px 8px 10px;display:flex;justify-content:space-between;align-items:flex-start;">
                    <div id="job_status" class="m-lr6">
                        <div>
                            <span style="color: #393C4D; font-size: 13px;">` + LANG.UI_PUBLIC_STATUS + `</span>
                            <ul style="max-height: 200px;display: flex;flex-wrap: wrap;width: max-content;">${getStatusOptions()}</ul>
                        </div>
                    </div>
                    <div id="module_type" class="mr15">
                        <div  class="mid">
                            <span style="color: #393C4D; font-size: 13px;">` + LANG.UI_SEARCH_MODE_TYPE + `</span>
                            <ul style="max-height: 200px;display: flex;flex-wrap: wrap;width: max-content;">${getPermissionModule()}</ul>
                        </div>
                    </div>
                </div>`;

        $('#orchestrationTaskToolbar .filter-content').html(filterContent);
        //每次点击checkbox遍历选中的数量设置ui
        $('#orchestrationTaskToolbar #filterDiv .filter-content input[type=checkbox]').on('click', filterOne);
    };
    let getPermissionModule = () => {
        let permission = CONF.PERMISSION;
        let html = '';
        for (const [key, value] of Object.entries(MODULE_PERMISSION)) {
            if (permission.includes(key)) {
                let sub_type = 0;
                // 获取虚拟机子模块类型
                if (Object.keys(VM_SUB_MODULE).includes(key)) {
                    sub_type = VM_SUB_MODULE[key];
                }
                // 获取文件子模块类型
                if (Object.keys(FS_SUB_MODULE).includes(key)) {
                    sub_type = FS_SUB_MODULE[key];
                }
                if (key == 'complete_machine') {
                    sub_type = 1;
                }
                html += `<li><label for="${key}"><input type="checkbox" id="${key}" value="${value}" data-sub_module="${sub_type}"><span>${MODULE_NAME[key]}</span></label></li>`
            }
        }
        return html;
    }
    let getStatusOptions = () => {
        let html = '';
        for (let key in STATUS_LIST) {
            if (STATUS_LIST.hasOwnProperty(key)) {
                html += `<li><label><input type="checkbox" value="${STATUS_LIST[key]}"><span class="label label-sm label-${STATUS_CLASS[key]} label-${STATUS_CLASS[key]}_en">${STATUS_DES[key]}</span></label></li>`
            }
        }
        return html;
    };
    //获取选中项
    let getSelectedRow = (select) => {//select = table id
        return $.map($(select).bootstrapTable('getSelections'), function (row) {
            return row;
        })
    };

    //获取选中项id
    let getSelectedId = (select) => {//select = table id
        return $.map($(select).bootstrapTable('getSelections'), function (row) {
            return row.task_uuid;
        });
    }
    let addTaskItem = () => {
        let taskType = $('#taskType').val();
        let selectedRow = getSelectedRow('#dataTable');
        let ids = getSelectedId('#dataTable');
        let itemWrapper = $(`#itemList${addTaskIndex}`);
        let totalWarn = $(`#totalWarn${addTaskIndex}`);
        let mode = parseInt($('#backupMode').find('.checked .icheck').data('mode'), 10);
        let strategySelect = $('#strategySelect');
        $('.time_empty-option').remove();
        if (isNaN(mode) && taskType == 1) {
            UIToastr.showWarning(LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS, LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS_EMPTY_MODE);
            return false;
        }
        if (selectedRow.length == 0) {
            UIToastr.showWarning(LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS, LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS_EMPTY_TIPS);
            return false;
        }
        for (let i = 0; i < ids.length; i++) {
            if (taskAddedCheck(ids[i])) {
                UIToastr.showWarning(LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS, LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS_REPEAT);
                return false;
            };
        };
        for (let i = 0; i < selectedRow.length; i++) {
            let item = selectedRow[i];
            let module = item.module_type;
            let icon = getModuleIcon(module, item.task_type); // 获取图标
            let itemName = getItemName(item, mode, taskType); // 获取项名称
            let strategy = item.time_strategy;
            let strategyDes = getTimeStrategy(strategy);
            if (item.occupation_flag && !editFlag) {
                UIToastr.showWarning(LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS, LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS_OCCUPATION);
                return false;
            }
            // 复制任务backup_mode等于任务类型
            if(item.module_type == CONF.MODULE_TYPE.FILE_COPY){
                mode = item.task_type;
            }
            let content = createCardItemHTML(item, icon, itemName, strategyDes, mode);
            itemWrapper.append(content);
            $('#delTask' + item.task_uuid).on('click', () => {
                $('#' + item.task_uuid).remove();
                $('#time' + item.task_uuid).remove();
                let totalTask = itemWrapper.find('.card-item').length;
                let totalDiv = totalWarn.find('.total');
                totalDiv.html(totalTask)
                if (totalTask <= WARN_TASK_NUM) {
                    totalWarn.find('.warn-tips').hide();
                }
                // 检查复用策略数量，为零则提示
                strategyEmptyOp();
            });
            // 阶段1任务需要显示时间策略
            if (addTaskIndex == 1 && strategyDes.time) {
                const maxDisplayLength = document.querySelector('#planName').offsetWidth / 20;
                const des = strategyDes.time.length > maxDisplayLength
                    ? strategyDes.time.substring(0, maxDisplayLength) + '...'
                    : strategyDes.time;
                strategySelect.append(`<option value="${item.strategy_id}" title="${strategyDes.time}(${item.task_name})" id="time${item.task_uuid}">${des}(${item.task_name})</option>`);
            };
        }
        let totalTask = itemWrapper.find('.card-item').length;
        let totalDiv = totalWarn.find('.total');
        totalDiv.html(totalTask)
        if (totalTask > WARN_TASK_NUM) {
            totalWarn.find('.warn-tips').show();
        }
        strategyEmptyOp();
        $("#addTaskDrawer").drawer('hide');
        itemWrapper.find('.empty_tips-div').remove();
    };

    function strategyEmptyOp() {
        // 检查复用策略数量，为零则提示
        let strategySelect = $('#strategySelect');
        let optionCount = strategySelect.find('option').length;
        if (optionCount == 0) {
            strategySelect.append(`<option class="time_empty-option">${LANG.UI_JOB_TASK_ORCHESTRATION_NO_STRATEGY}</option>`);
        } else {
            return true;
        }
    }

    /**
     * 根据模块类型获取对应的图标路径。
     * 
     * @param {string} module 模块类型，取值来自CONF.MODULE_TYPE枚举。
     * @returns {string} 返回对应模块类型的图标路径。
     */
    function getModuleIcon(module, taskType) {
        console.log(module);
        // 副本任务类型
        if (taskType == 17) {
            // 如果任务类型为副本，返回固定的图片路径
            return COPY_IMG;
        }
        if (taskType == 37) {
            return DATA_VERIFY
        }
        // 根据不同的模块类型返回相应的图标路径
        return MODULE_IMG[module]
    }
    /**
     * 根据任务类型和模式获取任务名称。
     * 
     * @param {Object} item - 任务对象，必须包含任务名称（task_name）。
     * @param {number} mode - 任务模式，影响返回的任务名称前缀。
     * @returns {string|boolean} 返回任务名称字符串或在特定条件下返回false。
     */
    function getItemName(item, mode, taskType = 1) {
        // 任务类型为备份时的详细处理逻辑
        if (BACKUP_TYPE.includes(parseInt(taskType))) {
            if (mode == 1) {
                // 全量备份模式
                return LANG.UI_JOB_TASK_ORCHESTRATION_BACKUP_MODE_FULL + item.task_name;
            } else if (mode == 2) {
                // 增量备份模式
                return LANG.UI_JOB_TASK_ORCHESTRATION_BACKUP_MODE_INCR + item.task_name;
            } else if (mode == 3) {
                // 差异备份模式
                return LANG.UI_JOB_TASK_ORCHESTRATION_BACKUP_MODE_DIFF + item.task_name;
            } else if (mode == 4) {
                // 归档日志备份模式
                return LANG.UI_JOB_TASK_ORCHESTRATION_BACKUP_MODE_LOG + item.task_name;
            } else if (mode == 9) {
                // 永久增量备份模式
                return LANG.UI_JOB_TASK_ORCHESTRATION_BACKUP_MODE_PINCR + item.task_name;
            }
        } else {
            // 默认情况下，返回任务对象中的任务名称
            return item.task_name;
        }
    }
    /**
     * 创建一个表示卡片项的HTML字符串
     * @param {Object} item - 包含卡片项信息的对象
     * @param {string} icon - 卡片项的图标URL
     * @param {string} itemName - 卡片项的名称
     * @param {Object} strategyDes - 包含策略描述信息的对象
     * @param {string} mode - 模式标识，用于指定卡片项的行为或外观
     * @returns {string} - 表示卡片项的HTML字符串
     */
    function createCardItemHTML(item, icon, itemName, strategyDes, mode) {
        // 创建卡片项的HTML内容
        let content = `<div class="card-item-wrapper" id="${item.task_uuid}" data-uuid="${item.task_uuid}" data-mode="${mode}" data-name="${item.task_name}" data-strategy="${item.strategy_id}" data-des="${strategyDes.time}">
        <div class="card-item">
            <i class="item-icon-div viconfont ${icon}"></i>
            <span class="item-name" title="${itemName}">${itemName}</span>
            <i class="viconfont vicon-guanbi item-del" id="delTask${item.task_uuid}"></i>
        </div>
        </div>`
        return content;
    }
    /**
     * 处理时间模式点击事件的函数。
     * 当用户点击不同的备份模式（完备、增量、差异）时，该函数将根据用户的选中状态执行相应的操作，
     * 如取消其他选项的选中状态等。
     * @param {object} event - 事件对象，包含了触发该函数的事件的详细信息。
     */
    let timeModeClick = function (event) {
        var mode = parseInt($(this).data('mode'), 10);
        // 检查mode是否为NaN，是则提前退出函数
        if (isNaN(mode)) {
            return;
        }

        // 根据点击的备份模式执行不同的逻辑
        switch (mode) {
            case CONF.TIME_STRATEGY_MODE.FULL: // 备份模式为完备
                // 如果点击的是取消按钮，则同时取消增量和差异备份的选中状态
                if (!event.target.checked) {
                    executeUncheck([2, 3, 4, 9]);
                }
                break;
            case CONF.TIME_STRATEGY_MODE.INCR: // 备份模式为增量
                // 如果点击的是取消按钮，则选中完备备份，同时取消差异和永久增量备份的选中状态
                if (!event.target.checked) {
                    executeUncheck([1, 3, 4, 9]);
                }
                break;
            case CONF.TIME_STRATEGY_MODE.DIFF: // 备份模式为差异
                // 如果点击的是取消按钮，则选中完备备份，同时取消增量和永久增量备份的选中状态
                if (!event.target.checked) {
                    executeUncheck([1, 2, 4, 9]);
                }
                break;
            case CONF.TIME_STRATEGY_MODE.DB_LOG: // 备份模式为差异
                // 如果点击的是取消按钮，则选中完备备份，同时取消增量和永久增量备份的选中状态
                if (!event.target.checked) {
                    executeUncheck([1, 2, 3, 9]);
                }
                break;
            case CONF.TIME_STRATEGY_MODE.PER_INCR: // 备份模式为差异
                // 如果点击的是取消按钮，则选中完备备份，同时取消增量和永久增量备份的选中状态
                if (!event.target.checked) {
                    executeUncheck([1, 2, 3, 4]);
                }
                break;
        }
    };

    /**
     * 该函数用于在指定的备份模式元素下，取消勾选指定模式的复选框。
     * @param {array} mode - 需要取消勾选的第一种模式。
     * @param {string} backupModeElement - 备份模式元素的选择器，用于定位到包含各种模式复选框的父元素。
     */

    let executeUncheck = function (modeList) {
        for (const mode of modeList) {
            // 取消勾选指定模式的复选框
            $(`#backupMode [data-mode=${mode}]`).iCheck('uncheck');
        }
    };
    //初始化时间策略
    const initStrategy = function (strategy = null) {
        const strategyConfig = [{
            mode: 0,
            strategy_type: 2,
            days: [0, 0, 0, 0, 1, 0, 0],
            start_time: '23:00:00',
            roll_flag: false,
            roll_interval: '01:00:00',
            roll_end_time: '23:59:59',
            frequency: '',
        }];
        const strategyIds = getStrategyIds();
        if (strategy != null) {
            if (strategy.time_strategy_id != 0 && strategy.time_strategy.length != 0) {
                // 任务的策略
                if (strategy.time_strategy_id && strategyIds.includes(strategy.time_strategy_id)) {
                    $('#timeSelect').val(1);
                    $('#strategySelect').val(strategy.time_strategy_id);
                    $('.strategy-div').show();
                    $('.strategy-time').hide();
                } else {
                    $('#timeSelect').val('custom');
                    $('.strategy-time').show();
                    const time = strategy.time_strategy[0];
                    strategyConfig[0] = {
                        mode: 0,
                        strategy_type: time.type,
                        days: time.days,
                        start_time: time.startTime,
                        roll_flag: time.rollFlag,
                        roll_interval: time.rollInterval,
                        roll_end_time: time.endTime,
                        frequency: time.frequency,
                    };
                }
            }
        }
        //延迟设置,因为这里icheck会默认修改里面的选中事件
        $('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: strategyConfig, display: [] });
    }
    /**
     * 设置时间策略函数
     * @param {Object} event - 触发事件的对象，预期包含当前目标的值（value）
     * 无返回值
     */
    const setTimeStrategy = function (event) {
        const strategyId = event.currentTarget.value;
        // 检查 strategyId 是否为字符串类型
        if (typeof strategyId !== 'string') {
            return;
        }

        // 定义策略ID与对应操作的映射，以动态调整页面展示
        const strategyActions = {
            'custom': () => $('.strategy-time').show() && $('.strategy-div').hide(),
            'manual': () => $('.strategy-time').hide() && $('.strategy-div').hide(),
            '1': () => $('.strategy-time').hide() && $('.strategy-div').show()
        };

        // 检查并执行映射中对应的策略操作
        if (strategyActions[strategyId]) {
            strategyActions[strategyId]();
        }
        // 检查复用策略数量，为零则提示
        strategyEmptyOp();
    };
    // 提交
    let submit = () => {
        if (!groupData()) {
            return false;
        }
        Metronic.blockUI({ target: '#orchestrationContent', animate: true });
        let createBack = (res) => {
            Metronic.unblockUI('#orchestrationContent');
            operateResponseList(res);
            if (res.success) {
                LOCATION('./content/platform/jobs/jobs.php?tab=2', 'task');
            };
        }
        let param = {};
        param.task_orchestration_plan = verifyData;
        pAjaxRequest(deepCloneObject(param), '/api/v1/orchestration', 'POST', createBack, true);
    };
    // 组装创建修改消息
    let groupData = () => {
        task_numbers = 0;
        // 名称
        verifyData.plan_name = $('#planName').val().trim();
        if (verifyData.plan_name == '') {
            UIToastr.showWarning(ORCHESTRATION_TIP, LANG.UI_JOB_TASK_ORCHESTRATION_NAME_EMPTY);
            return false;
        }
        verifyData.plan_uuid = $('#plan_uuid').val();
        // 阶段
        let sectionCard = document.querySelectorAll('.section-card');
        verifyData.section_list = [];
        for (let index = 0; index < sectionCard.length - 1; index++) {
            let startType = determineStartType(index, sectionCard); //触发事件 1按时间触发 2按事件触发 3事件+时间
            if (!startType && index > 0) {
                UIToastr.showWarning(ORCHESTRATION_TIP, LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_TYPE_EMPTY);
                return false;
            }
            let event_type = startType === 2 || startType === 3 ? $(sectionCard[index]).find('.eventCheck').val() : 0;
            let executionInterval = calculateExecutionInterval(sectionCard[index], startType);
            let driveTaskUuids = startType === 2 || startType === 3 ? $(sectionCard[index]).find('.multiple_select').val() : [];
            if ((startType == 2 || startType == 3) && (event_type == 3 || event_type == 6)) {
                if (!validateDriveTaskUuids(driveTaskUuids, index)) {
                    return false;
                }
                // 检查驱动任务是否在上一阶段中 不在则增加提示和红色区域
                if (!isTaskExists(verifyData.section_list[index - 1], driveTaskUuids)) {
                    $(sectionCard[index]).find(`.drive-event-wrapper`).addClass('has-error');
                    $(sectionCard[index]).find(`.multiple_select`).val('')
                    return false;
                };
            }
            // 有空的任务阶段不能创建计划
            if (getTaskList(sectionCard[index]) == false) {
                return false;
            }
            verifyData.section_list[index] = {
                'section_uuid': '',
                'start_type': startType ?? '',
                'execution_interval': index == 0 ? 0 : parseInt(executionInterval),
                'event_type': index == 0 ? 0 : parseInt(event_type),
                'drive_task_uuids': driveTaskUuids,
                'task_list': getTaskList(sectionCard[index]),
                'details': JSON.stringify({ remarks: $(sectionCard[index]).find('.plan-remarks').val() }),
            }
        }
        // 编排计划总任务数量
        verifyData.tasks_number = getTaskUuids().length;
        // 时间策略id
        verifyData.time_strategy_id = 0;
        verifyData.time_strategy = [];
        verifyData.details = '';
        let timeSelect = $('#timeSelect').val();
        if (timeSelect == 'custom') {
            verifyData.time_strategy_id = 0;
            let timeStrategy = $('#backupTimestrategy').getStrategyConfig();
            verifyData.time_strategy = timeStrategy.comInfo;
        } else if (parseInt(timeSelect) == 1) {
            let timeMode = $('#strategySelect').val();
            verifyData.time_strategy_id = parseInt(timeMode);
        }
        verifyData.details = '';
        return true;
    };
    /**
     * 根据指定索引和章节卡片信息，确定开始类型。
     * @param {number} index - 章节卡片的索引。
     * @param {Array} sectionCard - 包含多个章节卡片的数组。
     * @returns {number} startType - 返回开始类型的编码。0代表无开始类型，1代表仅时间开始，2代表仅事件开始，3代表时间和事件都开始。
     */
    const determineStartType = (index, sectionCard) => {
        let startType = 0; // 默认开始类型为0
        // 获取第一个和第二个复选框的选中状态
        let eventCheck = $($(sectionCard[index]).find('.icheck')[0]).prop('checked');
        let timeCheck = $($(sectionCard[index]).find('.icheck')[1]).prop('checked');

        // 根据复选框的选中状态设置开始类型
        if (timeCheck && eventCheck) {
            startType = 3; // 时间和事件都选中
        } else if (timeCheck) {
            startType = 1; // 仅时间选中
        } else if (eventCheck) {
            startType = 2; // 仅事件选中
        }

        return startType;
    };
    /**
     * 计算执行间隔时间
     * @param {HTMLElement} sectionCard - 包含时间输入的section卡片元素
     * @param {number} startType - 启动类型，决定是否处理时间字符串
     * @returns {number} 返回计算得到的执行间隔时间，单位为秒；如果不需要计算或计算失败，则返回0。
     */
    const calculateExecutionInterval = (sectionCard, startType) => {
        // 当启动类型为1或3时，处理时间字符串
        if (startType === 1 || startType === 3) {
            // 从sectionCard中找到时间输入框，并获取其值
            const time_str = $($(sectionCard).find('.interval-time')).val();
            if (time_str) {
                // 将时间字符串按':'分割为小时、分钟和秒，并转换为数字
                const [hours, minutes, seconds] = time_str.split(':');
                // 计算总秒数并返回
                return parseInt(hours) * 3600 + parseInt(minutes) * 60 + parseInt(seconds);
            }
        }
        // 如果不需要处理时间字符串或处理失败，返回0
        return 0;
    };
    /**
     * 验证驱动任务UUIDs的有效性。
     * 
     * @param {Array} driveTaskUuids - 驱动任务的UUID数组。
     * @param {number} index - 当前验证任务在数组中的索引位置（此参数在函数体中未使用，可能用于后续扩展）。
     * @returns {boolean} 如果驱动任务UUIDs有效（非空），返回true；否则返回false。
     */
    const validateDriveTaskUuids = (driveTaskUuids, index) => {
        // 当驱动任务UUID数组为空时，显示警告信息并返回false
        if (driveTaskUuids.length === 0) {
            UIToastr.showWarning(ORCHESTRATION_TIP, LANG.UI_JOB_TASK_ORCHESTRATION_DRIVE_JOBS_EMPTY);
            return false;
        }
        return true;
    };
    /**
     * 检查给定的 driveTask 是否存在于 taskList 中。
     * 
     * @param {Object} taskList - 一个包含 task_list 属性的对象，task_list 应该是一个数组。
     * @param {Array} driveTask - 一个包含 task_uuid 的数组，用于检查是否存在于 taskList 中。
     * @returns {Boolean} 如果 driveTask 中的所有任务都存在于 taskList 中，则返回 true；否则返回 false。
     */
    let isTaskExists = (taskList, driveTask) => {
        // 检查 taskList 是否为有效对象和数组
        if (!taskList || typeof taskList !== 'object' || !Array.isArray(taskList.task_list)) {
            return false; // 如果不满足条件，返回 false
        }

        // 使用 Set 数据结构优化任务查找
        let taskSet = new Set(taskList.task_list.map(task => task.task_uuid));

        // 检查 driveTask 是否为数组
        if (!Array.isArray(driveTask)) {
            return false; // 如果不满足条件，返回 false
        }

        // 遍历 driveTask，检查每个任务是否存在于 taskSet 中
        for (let i = 0; i < driveTask.length; i++) {
            if (!taskSet.has(driveTask[i])) {
                UIToastr.showWarning(ORCHESTRATION_TIP, LANG.UI_JOB_TASK_ORCHESTRATION_DRIVE_JOBS_DELETE);
                return false; // 如果有任一任务不存在于 taskSet 中，返回 false
            }
        }
        return true; // 所有任务都存在于 taskSet 中，返回 true
    };

    /**
     * 获取卡片元素中的任务列表。
     * @param {HTMLElement} cardElement - 用于查找任务列表的卡片元素。
     * @returns {Array} 返回一个包含任务信息的数组，每个任务包含uuid、mode和details。
     * @throws {Error} 如果提供的卡片元素无效，抛出错误。
     */
    const getTaskList = (cardElement) => {
        if (!cardElement) {
            UIToastr.showWarning(ORCHESTRATION_TIP, LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS_EMPTY);
            return false; // 返回一个空数组以保持函数返回类型的一致性
        }

        const list = [];
        // 查询所有卡片项包装器
        const cardItemWrappers = cardElement.querySelectorAll('.card-item-wrapper');

        // 如果没有找到卡片项，显示警告并返回空数组
        if (cardItemWrappers.length === 0) {
            UIToastr.showWarning(ORCHESTRATION_TIP, LANG.UI_JOB_TASK_ORCHESTRATION_ADD_JOBS_EMPTY);
            return false;
        }

        // 遍历每个卡片项，并收集任务信息
        cardItemWrappers.forEach((listItem) => {
            task_numbers++;
            const uuid = listItem.dataset.uuid;
            const mode = listItem.dataset.mode;

            // 将任务信息添加到列表中
            list.push({
                'task_uuid': uuid,
                'backup_mode': mode ?? 1,
                'details': '',
            });
        });

        return list;
    };

    var getTimeStrategy = (msg) => {
        let timeInfo = { time: '' };
        if (!msg) {
            return timeInfo;
        }
        for (let i = 0; i < msg.length; i++) {
            let strategy = msg[i];
            let des = "";
            if (CONF.STRATEGY_TYPE.DAY == strategy.type) {
                des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy);
            } else if (CONF.STRATEGY_TYPE.WEEK == strategy.type) {
                des += getStrategyFrequency(strategy);
                if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                    des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
                } else {
                    des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
                }
            } else if (CONF.STRATEGY_TYPE.MONTH == strategy.type) {
                des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
            } else if (CONF.STRATEGY_TYPE.GLOBAL == strategy.type) {
                des += strategy.startTime;
            } else {
                des += LANG.UI_PUBLIC_NOTHING;
            }
            timeInfo.time += des;
        }
        return timeInfo;
    }
    //得到备份间隔描述
    let getStrategyFrequency = (strategy) => {
        let frequency = "";
        let frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
        for (let i = 1; i <= 20; i++) {
            if (strategy.frequency == "s" + i) {
                if (i == 1) {
                    frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
                }
                else {
                    frequency = frequencyLang.replace('x', i) + ",";
                }
            }
        }
        return frequency;
    }
    //获取每周显示日期
    let getStrategyWeek = (days) => {
        let desDays = '';
        $.each(days, (i, d) => {
            if (1 == d) {
                desDays += CONF.WEEK[i] + ", ";
            }
        });
        return desDays;
    }
    let getStrategyDays = (days) => {
        let desDays = '';
        $.each(days, function (i, d) {
            if (1 == d) {
                let day = i + 1;
                desDays += day + ", ";
            }
        });
        return desDays;
    }
    let getEachStrategy = (strategy) => {
        let desEach = '';
        desEach += strategy.startTime;
        //如果是英文版 需要加空格
        if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
            desEach += " "; //策略开始时间
        }
        desEach += LANG.UI_STRATEGY_START + ", ";
        if (strategy.rollFlag) {
            if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
            } else {
                desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + strategy.endTime + LANG.UI_STRATEGY_END;
            }

        } else {
            desEach += LANG.UI_STRATEGY_ROLL_NO;
        }
        desEach += ",";
        return desEach;
    }
    // ---------------修改编排计划--------------

    let setSectionInfo = (data) => {
        let sectionList = data.section_list;
        let strategySelect = $('#strategySelect');
        // 初始化阶段信息
        sectionList.forEach((el, index, arr) => {
            // 阶段二开始需要动态创建section
            if (index > 0) {
                addPlanSection();
            }
            let itemWrapper = $(`#itemList${index + 1}`);
            let taskList = el.task_list;
            for (let i = 0; i < taskList.length; i++) {
                // 初始化任务列表
                let taskType = taskList[i].info.task_type;
                let strategyId = taskList[i].info.strategy_id;
                let strategy = taskList[i].info.time_strategy;
                let taskId = taskList[i].task_uuid;
                let mode = taskList[i].backup_mode; //备份类型
                let strategyDes = getTimeStrategy(strategy); //时间策略描述
                let module = taskList[i].info.module_type;
                // 图标信息
                let icon = getModuleIcon(module, taskType);
                let itemName = getItemName(taskList[i].info, mode, taskType)
                let content = createCardItemHTML(taskList[i].info, icon, itemName, strategyDes, mode);
                let details = JSON.parse(el.details);
                $(`#sectionCard${index + 1}`).find('.plan-remarks').val(details.remarks);
                // 动态添加任务列
                itemWrapper.append(content);
                $('#delTask' + taskId).on('click', () => {
                    $('#' + taskId).remove();
                    $('#time' + taskId).remove();
                    // 检查复用策略数量，为零则提示
                    let totalTask = itemWrapper.find('.card-item').length;
                    let totalWarn = $(`#totalWarn${index + 1}`);
                    let totalDiv = totalWarn.find('.total');
                    totalDiv.html(totalTask)
                    if (totalTask <= WARN_TASK_NUM) {
                        totalWarn.find('.warn-tips').hide();
                    }
                    strategyEmptyOp();
                });
                // 阶段1任务需要显示时间策略
                if (index == 0 && strategyDes.time) {
                    let taskName = taskList[i].info.task_name;
                    const maxDisplayLength = document.querySelector('#planName').offsetWidth / 20;
                    const des = strategyDes.time.length > maxDisplayLength
                        ? strategyDes.time.substring(0, maxDisplayLength) + '...'
                        : strategyDes.time;
                    strategySelect.append(`<option value="${strategyId}" title="${strategyDes.time}(${taskName})" id="time${taskId}">${des}(${taskName})</option>`);
                };
            };
            // 初始化触发事件信息
            if (el.start_type == 1) {//按时间
                $($(`#eventMode${index + 1}`).find('.icheck[data-mode=1]')).iCheck('check');
                $(`#eventAccordion${index + 1}`).find('.interval-time').val(formatSecondsAsHHMMSS(el.execution_interval));
                $(`#eventAccordion${index + 1}`).find('.event-panel[data-mode=1]').show();
            } else if (el.start_type == 2) {//按事件
                $($(`#eventMode${index + 1}`).find('.icheck[data-mode=2]')).iCheck('check');
                $(`#eventCheck${index + 1}`).val(el.event_type);
                $(`#eventAccordion${index + 1}`).find('.event-panel[data-mode=2]').show();
            } else if (el.start_type == 3) {//时间+事件
                $($(`#eventMode${index + 1}`).find('.icheck')).iCheck('check');
                $(`#eventAccordion${index + 1}`).find('.interval-time').val(formatSecondsAsHHMMSS(el.execution_interval));
                $(`#eventCheck${index + 1}`).val(el.event_type);
                $(`#eventAccordion${index + 1}`).find('.event-panel').show();
            };
            //初始化任务选择列表
            let select = $(`#multiple_select${index + 1}`);
            select.empty();
            let itemList = document.querySelectorAll(`#itemList${index} .card-item-wrapper`);
            itemList.forEach((item) => {
                let taskId = $(item).data('uuid');
                let name = $(item).data('name');
                select.append(`<option value="${taskId}" id="task${taskId}">${name}</option>`);
            });
            if (el.drive_task_uuid != '[]') {
                select.val(JSON.parse(el.drive_task_uuid)).selectpicker('refresh');
                $(`#taskList${index + 1}`).show();
            }
            let totalWarn = $(`#totalWarn${index + 1}`);
            totalWarn.find('.total').html(taskList.length);
            if (taskList.length > WARN_TASK_NUM) {
                totalWarn.find('.warn-tips').show();
            }
            if (editFlag) {
                $('.empty_tips-div').remove();
            }
            select.selectpicker('refresh');
        });
    };
    /**
     * 将总秒数格式化为hh:mm:ss的字符串格式
     * @param {number} totalSeconds 总秒数
     * @returns {string} 格式化后的時間字符串
     */
    function formatSecondsAsHHMMSS(totalSeconds) {
        // 计算小时数
        const hours = Math.floor(totalSeconds / 3600);
        // 计算分钟数
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        // 计算剩余秒数
        const seconds = totalSeconds % 60;
        // 使用字符串模板和padStart来确保数字始终是两位数的格式
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    // 获取所有的时间策略id
    let getStrategyIds = () => {
        let selectElement = document.getElementById('strategySelect');
        // 获取所有的options
        let options = selectElement.options;
        // 创建一个数组来存储所有的values
        let values = [];
        for (let i = 0; i < options.length; i++) {
            // 将每个option的value添加到values数组中
            values.push(parseInt(options[i].value));
        }
        return values;
    };

    // 调整高度
    let initTaskTableHeight = () => {
        //拿到父窗口的高度
        var panelH = window.innerHeight;
        height = panelH - 571;
        $("#addTaskDrawer .fixed-table-body").css({
            "height": "auto",
            "min-height": "300px",
        });
    }
    // 检查任务是否被添加过
    let taskAddedCheck = (id) => {
        let taskItem = $('.card-item-wrapper');
        // 没有添加的任务直接返回false
        if (taskItem.length == 0) {
            return false;
        };
        let list = getTaskUuids();
        // uuid和当前添加的所有任务重复
        if (list.includes(id)) {
            return true;
        }
        return false;
    }
    /**
     * 获取页面上所有任务项的UUID。
     * 该函数没有参数。
     * @return {Array} 返回一个包含所有任务项UUID的数组。
     */
    const getTaskUuids = () => {
        // 使用const声明，因为list变量在赋值后不再被修改
        const taskUuids = [];
        // 优化DOM元素查询，使用.map()进行转换
        const taskItems = $('.card-item-wrapper');

        // 检查taskItems是否为空，避免边界条件问题
        if (taskItems.length === 0) {
            // 如果没有找到任务项，可以记录日志，或者根据实际需求进行其他处理
            return taskUuids; // 返回空数组
        }

        // 使用.map()提取UUID，比for循环更简洁高效
        const uuids = taskItems.map((i, el) => {
            // 通过try-catch捕获可能的异常，提高代码健壮性
            try {
                return $(el).data('uuid');
            } catch (error) {
                return []; // 在异常情况下返回null，或根据需要进行其他处理
            }
        }).get(); // .get()将jQuery对象转换为普通数组

        // 过滤可能的null值，只保留有效的UUID
        taskUuids.push(...uuids.filter(uuid => uuid !== null));

        // 返回包含所有UUID的数组
        return taskUuids;
    };
    /**
    * 根据传入的模块信息数组，更新备份模式相关UI元素的显示状态和选中状态。
    *
    * @param {Object[]} rows - 包含模块信息的对象数组，每个对象应具有module_type和sub_module_type属性。
    */
    let showBackupMode = (rows) => {

        /**
         * 隐藏指定CSS类的元素，并取消选中指定数据模式的输入框。
         *
         * @param {string[]} classes - 要隐藏的CSS类名列表。
         * @param {...number} dataModes - 要取消选中的数据模式列表。
         */
        const hideClassAndUncheck = (classes, ...dataModes) => {
            classes.forEach((cls) => {
                $(`.backup_mode${cls}`).hide();
            });
            BACKUP_MODE.forEach((mode) => {
                if (!classes.includes(mode)) {
                    $(`.backup_mode${mode}`).show();
                }
            });
            dataModes.forEach((mode) => {
                $('#backupMode').find(`input[data-mode=${mode}]`).iCheck('uncheck');
            });
        };

        // 初始化隐藏和取消勾选的集合
        let hideClasses = [];
        let uncheckDataModes = [];

        // 遍历模块信息数组，根据模块类型和子模块类型收集需要隐藏的类和取消选中的数据模式
        rows.forEach((row) => {
            switch (row.module_type) {
                case CONF.MODULE_TYPE.FS:
                case CONF.MODULE_TYPE.NAS:
                    hideClasses.push(...[4, 9]);
                    uncheckDataModes.push(4, 9);
                    break;
                case CONF.MODULE_TYPE.VM:
                case CONF.MODULE_TYPE.OS:
                    hideClasses.push(4);
                    uncheckDataModes.push(4);
                    break;
                case CONF.MODULE_TYPE.M365:
                    hideClasses.push(...[3, 4]);
                    uncheckDataModes.push(4, 3);
                    break;
                case CONF.MODULE_TYPE.DB:
                    hideClasses.push(9);
                    uncheckDataModes.push(9);
                    if (DB_NO_INCR.includes(row.sub_type)) {
                        hideClasses.push(2);
                        uncheckDataModes.push(2);
                    }
                    if (DB_NO_DIFF.includes(row.sub_type)) {
                        hideClasses.push(3);
                        uncheckDataModes.push(3);
                    }
                    break;
                default:
                    hideClasses.push(...[4, 9]);
                    break;
            }
        });
        if (rows.length == 0) {
            hideClasses.push(...[4, 9]);
            uncheckDataModes.push(4, 9);
        }
        // 执行隐藏和取消勾选操作
        hideClassAndUncheck(hideClasses, ...uncheckDataModes);
    };

    return {
        init: function () {
            listeners();
            init();
        }
    };
}();
jQuery(document).ready(function () {
    Orchestration.init();
});