var addStrategy = function () {
    // 新建策略消息数据
    let data = { strategyInfo: { time: {}, speedlimit: {}, store: {}, reserve: {} }, strategy_type: 1, strategy_name: '', remark: '' };
    let module = 2;
    let editFlag = false;
    let STRATEGY_TIP = LANG.UI_GLOBAL_STRATEGY_ADD;
    let initDispenseTaskFlag = false; //分发表格初始化标志
    let diffInitFlag = false; //差异表格初始化标志
    const defaultConfig = {
        mode: [1, 2, 3, 9],
        deduplication: true,
        GFS: true,
        strategy_group_flag: true,
    };
    // 通用策略名称
    const StrategyTypes = {
        TIME: LANG.UI_STRATEGY_TIME,
        SPEED: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT,
        STORE: LANG.UI_GLOBAL_STRATEGY_STORE,
        RESERVE: LANG.UI_STRATEGY_RESERVE,
    };
    // 备份策略模块授权类型和字段
    const MODULE_FIXED = {
        2: "vmprotect", // 虚拟机备份
        3: "fileprotect", // 文件
        4: "db_protect", // 数据库
        5: "complete_machine", // 整机
        6: "osbackup", // 卷
        11: "nas_protect", // nas
        14: "office365_protect", // exchange
        17: "awsprotect", // 公有云
        22: "prcloud_protect", // 私有云
        28: "k8s_protect", // kubernetes
        999: "obs_protect", // 对象存储
        998: "hadoop_protect", // hadoop
    }
    // 备份策略模块类型和名称
    const MODULE_NAME = {
        2: LANG.UI_BACKUP_DATA_MODULE_VM, // 虚拟机备份
        3: LANG.UI_BACKUP_DATA_MODULE_FS, // 文件
        4: LANG.UI_BACKUP_DATA_MODULE_DB, // 数据库
        5: LANG.UI_BACKUP_DATA_MODULE_OS, // 整机
        6: LANG.UI_COPY_MODULE_LABEL_REEL_OS, // 卷
        11: LANG.UI_BACKUP_DATA_MODULE_NAS, // nas
        14: LANG.UI_BACKUP_DATA_MODULE_M365, // exchange
        17: LANG.UI_BACKUP_DATA_MODULE_PUBLIC_CLOUD, // 公有云
        22: LANG.UI_BACKUP_DATA_MODULE_PRIVATE_CLOUD, // 私有云
        28: LANG.UI_BACKUP_DATA_MODULE_K8S, // kubernetes
        998: LANG.UI_BACKUP_DATA_MODULE_HADOOP, // hadoop
        999: LANG.UI_BACKUP_DATA_MODULE_OBS, // 对象存储
    }
    // -------------初始化页面--------

    /**
     * 初始化模块权限
     * @param {object} data 修改时传入，用于初始化旧数据
     */
    const initModulePermissions = (data = null) => {
        // 修改获取策略类型
        let strategyType = '';
        if (data) {
            strategyType = data.strategy_type;
        }
        const moduleSelect = $('#strategyType');
        // 清空类型选择框
        moduleSelect.empty();
        const PERMISSION = CONF.PERMISSION;
        let html = '';
        // 根据授权添加模块类型
        for (let key in MODULE_FIXED) {
            if (PERMISSION.includes(MODULE_FIXED[key])) {
                if (strategyType == key) {
                    html += `<option value="${key}" selected>${MODULE_NAME[key]}</option>`;
                } else {
                    html += `<option value="${key}">${MODULE_NAME[key]}</option>`;
                }
            }
        }
        moduleSelect.append(html);
    };
    //初始化策略名称
    const initStrategyName = function () {
        let nameBack = function (res) {
            if (res.data && res.data.name) {
                $('#strategyName').val(res.data.name);
            } else {
                UIToastr.showInfo(res.message);
            }
        }
        pAjaxRequest({}, "/api/v1/strategies/name", "GET", nameBack, true);
    }
    /**
     * 初始化备份策略
     * @param {null|Object[]} data - 备份策略数据数组，每个对象包含备份策略的各种参数
     * @returns {void} 无返回值
     */
    let initStrategy = (data = null) => {
        // 初始化策略类型
        initModulePermissions(data);
        // 初始化根据模块类型配置的变量
        let settings = moduleStrategySettings();
        defaultConfig.permanent = settings.permanent;
        // 更新默认配置并初始化备份策略界面
        defaultConfig.GFS = settings.GFS;
        defaultConfig.mode = settings.mode;
        defaultConfig.deduplication = settings.deduplication;
        defaultConfig.module_type_des = settings.module_type_des;
        defaultConfig.strategy = data;
        defaultConfig.timeFormateFlag = false;
        defaultConfig.reserve_mode_chain_flag = settings.reserve_mode_chain_flag;
        defaultConfig.module_type = module;
        $('#backupStrategy').initBackupStrategy(defaultConfig);
    };
    let initOldData = function () {
        // 策略uuid
        let uuid = $('#strategy_uuid').val();

        let detailsBack = function (res) {
            if (res.success) {
                let data = JSON.parse(res.data.strategyInfo);
                data.strategy_type = res.data.strategy_type;
                old_strategyInfo = JSON.parse(res.data.strategyInfo);
                module = res.data.strategy_type;
                // 策略名称
                $('#strategyName').val(decodeURIComponent(res.data.strategy_name)).prop("disabled", true);
                // 备注
                $('#remark').val(res.data.remark);
                // 策略类型
                $('#strategyType').val(res.data.strategy_type).prop("disabled", true);
                // 初始化默认数据
                initStrategy(data);
            } else {
                UIToastr.showInfo(res.message);
            }
        }
        pAjaxRequest(deepCloneObject({ uuid: uuid }), "/api/v1/strategies/details", "GET", detailsBack, true);
    }

    // 初始化默认数值
    let initDefaultData = function () {
        // 策略uuid
        let uuid = $('#strategy_uuid').val();
        // 通过url是否包含uuid判断是创建还是修改
        if (uuid) {
            editFlag = true;
            STRATEGY_TIP = LANG.UI_GLOBAL_STRATEGY_EDIT;
            initOldData();
        } else {
            initStrategyName();//初始化策略名称
            editFlag = false;
            initStrategy();
        }
    }

    //  ---------页面交互-------------
    //初始化监听事件
    let initListeners = function () {
        //分发修改任务
        $('#dispense_submit').on('click', dispenseJob);
        //切换策略类型
        $('#strategyType').on('change', typeHandler);
        //选择备份策略复选框
        // 取消添加策略
        $('#cancelBtn').on('click', cancel);
        $("#close_modal").on('click', closeModal);
        $('#addsubmit').on('click', submit);
    }
    //关闭drawer
    const closeModal = function () {
        $("#diffDrawer").drawer('hide');
    }
    /**
     * 不同模块存在差异性配置
     * @returns {object}
     */
    const moduleStrategySettings = () => {
        let  mode = [], GFS = false, deduplication = false, permanent = false, module_type_des='', reserve_mode_chain_flag = false;
        switch (module) {
            // 虚拟机模块：完备、增备、差备、永久，显示GFS保留策略，显示重复数据删除
            case CONF.MODULE_TYPE.VM:
            case 17:
            case 22:
                GFS = true;
                mode = [1, 2, 3, 9];
                deduplication = true;
                break;
            // 操作系统：完备、增备、差备、永久， 显示重复数据删除
            case CONF.MODULE_TYPE.OS:
                mode = [1, 2, 3, 9];
                deduplication = true;
                break;
            // M365：完备、增备、永久增量，显示永久保留，不显示数据保留类型
            case CONF.MODULE_TYPE.M365:
                mode = [1, 2, 9];
                permanent = true;
                break;
            // 文件模块：完备、增备、差异、永久
            case CONF.MODULE_TYPE.FS:
            case 998:
            case 999:
            case CONF.MODULE_TYPE.NAS:
                mode = [1, 2, 3, 9];
                break;
            // 数据库：完备、增备、差异、全量，显示重复数据删除
            case CONF.MODULE_TYPE.DB:
                mode = [1, 2, 3, 4, 9];
                deduplication = true;
                module_type_des = 'db';
                break;
            case CONF.MODULE_TYPE.KUBERNETES:
                mode = [1, 2];
                reserve_mode_chain_flag = true
                break;
        }
        return { mode, GFS, deduplication, permanent, module_type_des, reserve_mode_chain_flag };
    }

    // 分发差异抽屉
    let dispenseJob = function () {
        $('#diffDrawer').drawer('hide')
        data.taskuuids = getIdSelectedId('#data_table');
        if (data.taskuuids.length == 0) {
            UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_DISPENSE, LANG.UI_GLOBAL_STRATEGY_DISPENSE_NO_SELECT);
            return false;
        }
        editStrategyConfirm();
    }
    function getIdSelectedId(select) {//select = table id
        return $.map($(select).bootstrapTable('getSelections'), function (row) {
            return row.uuid;
        })
    }

    //切换模块类型
    let typeHandler = function () {
        module = Number($('#strategyType').val());
        data.strategy_type = module;
        let settings = moduleStrategySettings();
        let config = {
            GFS: settings.GFS,
            deduplication: settings.deduplication,
            permanent: settings.permanent,
            mode: settings.mode,
            module_type_des: settings.module_type_des,
            reserve_mode_chain_flag: settings.reserve_mode_chain_flag,
            module_type: module,
        };
        $('#backupStrategy').refreshStrategy(config);
    }

    //取消返回管理界面
    let cancel = function () {
        LOCATION('./content/platform/strategy/global_strategy.php', 'backup_manager');
    }
    // 获取策略差异
    function getDistinctObjects(newObj, oldObj) {
        let diff = { time: {}, speedlimit: {}, store: {}, reserve: {} };
        // 得到差异对比
        function compareObjects(attr) {
            let attributeDiff = {};
            if (!newObj[attr].des && !oldObj[attr].des) {
                return;
            }
            attributeDiff.new = newObj[attr].des ? newObj[attr].des.replaceAll('<br />', '<br>') : '';
            attributeDiff.old = oldObj[attr].des ? oldObj[attr].des.replaceAll('<br />', '<br>') : '';
            // 特殊逻辑处理
            if (attr === 'store') {
                if (attributeDiff.new === attributeDiff.old) {
                    if (!oldObj.store.storeInfo.password_auto_flag && oldObj.store.storeInfo.password !== newObj.store.storeInfo.password) {
                        attributeDiff.new += '<br>' + LANG.UI_GLOBAL_STRATEGY_PASSWORD_CHANGED;
                    } else {
                        attributeDiff = {}; // 无有效差异时，清空对象
                    }
                }
            }
            if (attributeDiff.new && attributeDiff.old) {
                if (attributeDiff.new.replace(/<br>\s*$/, '').replace(/\n$/, '') == attributeDiff.old.replace(/<br>\s*$/, '').replace(/\n$/, '')) {
                    attributeDiff = {};
                }
            }

            // 如果有差异，则添加到diff中，否则不添加
            if (Object.keys(attributeDiff).length > 0) {
                diff[attr] = attributeDiff;
            }
        }

        // 使用通用处理函数来处理每个属性
        compareObjects('time');
        compareObjects('speedlimit');
        compareObjects('store');
        compareObjects('reserve');

        return diff;
    }


    // 初始化分发任务表格
    let iniDataTable = function () {
        let diffDetails = getDistinctObjects(data.strategyInfo, old_strategyInfo);
        let diffName = '';
        if (Object.keys(diffDetails.time).length > 0) {
            diffName += LANG.UI_STRATEGY_TIME + ' ';
        }
        if (Object.keys(diffDetails.speedlimit).length > 0) {
            diffName += LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT + ' ';
        }
        if (Object.keys(diffDetails.store).length > 0) {
            diffName += LANG.UI_GLOBAL_STRATEGY_STORE + ' ';
        }
        if (Object.keys(diffDetails.reserve).length > 0) {
            diffName += LANG.UI_STRATEGY_RESERVE + ' ';
        }
        if (diffName.length == 0) {
            diffName = LANG.UI_PUBLIC_NOTHING;
        }
        let uuid = $('#strategy_uuid').val();
        let options = {
            vin_url: "/api/v1/strategies/jobs",
            vin_method: "POST",
            vin_params: function () {
                let params = {};
                params.strategy_uuid = uuid;
                params.diffName = diffName;
                params.module_type = module;
                return params;
            },
            resizable: false, //可变宽度

            columns: [
                {
                    checkbox: true,
                    sortable: false,
                },
                {
                    field: 'task_name',
                    title: LANG.UI_GLOBAL_STRATEGY_TASK_NAME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'module_type_des',
                    title: LANG.UI_GLOBAL_STRATEGY_MODULE_TYPE,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'create_time',
                    title: LANG.UI_GLOBAL_STRATEGY_EDIT_DATE,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'task_status',
                    title: LANG.UI_PUBLIC_STATUS,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'strategy_diff',
                    title: LANG.UI_GLOBAL_STRATEGY_DIFF,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'diff_info',
                    title: LANG.UI_GLOBAL_STRATEGY_DIFF_DETAIL,
                    sortable: false,
                    align: 'center',
                    events: operateEvents,
                    formatter: function (index, row) {
                        if (row.related) {
                            return '<button class="diff_details btn btn-blue" type="button"><span>' + row.diff_info + '</span></button>'
                        }
                        return '';
                    }
                },]
        };
        // 未初始化过则初始一个新表
        if (!initDispenseTaskFlag) {
            $('#data_table').baseTableConfig().init(options);
            initDispenseTaskFlag = true;
        } else {
            // 如果已经初始化销毁表格再初始化新表
            // 销毁表格
            $('#data_table').bootstrapTable('destroy');
            if (!groupData()) return false;
            $('#data_table').baseTableConfig().init(options);
        }
    }
    // 分发表格监听
    let operateEvents = {
        'click .diff_details': function (e, value, row, index) {
            if (row.strategy_diff == LANG.UI_PUBLIC_NOTHING) {
                UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_DISPENSE, LANG.UI_GLOBAL_STRATEGY_DISPENSE_NO_DIFFERENCE);
            } else {
                $('#diffDrawer').drawer('toggle');
            }
            initStrategyDiffTable();
        }
    }
    function addDiffIfNotEmpty(diffType, diffObject, dataDiff) {
        if (Object.keys(diffObject).length !== 0) {
            dataDiff.push({
                strategy_type: diffType,
                old_info: diffObject.old,
                new_info: diffObject.new,
            });
        }
    }

    // 初始化差异表格
    let initStrategyDiffTable = function (details) {
        // 获取策略修改前后的差异
        let diffInfo = getDistinctObjects(data.strategyInfo, old_strategyInfo)
        let dataDiff = [];
        // 获取每个策略的差异对比
        if (diffInfo && Object.keys(diffInfo).length > 0) {
            addDiffIfNotEmpty(StrategyTypes.TIME, diffInfo.time, dataDiff);
            addDiffIfNotEmpty(StrategyTypes.SPEED, diffInfo.speedlimit, dataDiff);
            addDiffIfNotEmpty(StrategyTypes.STORE, diffInfo.store, dataDiff);
            addDiffIfNotEmpty(StrategyTypes.RESERVE, diffInfo.reserve, dataDiff);
        }
        let options = {
            pagination: false, //页标签
            resizable: false, //可变宽度
            data: dataDiff,

            columns: [
                {
                    field: 'strategy_type',
                    title: LANG.UI_GLOBAL_STRATEGY_NAME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'old_info',
                    title: LANG.UI_GLOBAL_STRATEGY_MODIFY_BEFORE,
                    sortable: false,
                    align: 'center',
                    cellStyle: (value, row, index, field) => {
                        return {
                            css: {
                                "white-space": "normal",
                                "word-wrap": "break-word",
                                "max-width": "36px ",
                            }
                        };
                    },
                    formatter: (value, row, index, field) => {
                        return value;
                    },
                },
                {
                    field: 'new_info',
                    title: LANG.UI_GLOBAL_STRATEGY_MODIFY_AFTER,
                    sortable: false,
                    align: 'center',
                    cellStyle: (value, row, index, field) => {
                        return {
                            css: {
                                "white-space": "normal",
                                "word-wrap": "break-word",
                                "max-width": "36px ",
                            }
                        };
                    },
                    formatter: (value, row, index, field) => {
                        return value;
                    },
                },
            ],
        }

        // 未初始化过则初始一个新表
        if (!diffInitFlag) {
            $('#diffStrategyTable').baseTableConfig().init(options);
            diffInitFlag = true;
        } else {
            // 如果已经初始化销毁表格再初始化新表
            // 销毁表格
            $('#diffStrategyTable').bootstrapTable('destroy');
            $('#diffStrategyTable').baseTableConfig().init(options);
        }
    }
    // 初始化分发模态框
    let initModal = function () {
        $('#strategyDiffDiv').modal({ 'width': '800px' });
        iniDataTable();
    }
    // 确认修改
    let editStrategyConfirm = function () {
        if (!groupData()) return false;
        Metronic.blockUI({ target: '#editModaldiv', animate: true, cenrerY: true, });
        // 修改回调
        let editBack = function (res) {
            Metronic.unblockUI('#editModaldiv');
            // 成功则返回管理页面 
            if (operateResponseList(res)) {
                $('#strategyDiffDiv').modal('hide');
                $('#editModaldiv').modal('hide');
                LOCATION('./content/platform/strategy/global_strategy.php', 'backup_manager');
            } else {
                // 失败
                UIToastr.showInfo(res.message);
            }
        }
        pAjaxRequest(deepCloneObject(data), '/api/v1/strategies', "PUT", editBack, true);
    }
    //询问是否分发策略
    let dispenseCheck = function () {
        bootbox.dialog({
            title: LANG.UI_GLOBAL_STRATEGY_DISPENSE,
            message: LANG.UI_GLOBAL_STRATEGY_DISPENSE_TIPS,
            buttons: {
                // 取消
                cancel: {
                    label: LANG.UI_GLOBAL_STRATEGY_DISPENSE_CANCEL,
                    className: 'btn-default',
                    callback: function () {
                    }
                },
                // 关闭
                noclose: {
                    label: LANG.UI_GLOBAL_STRATEGY_DISPENSE_NO,
                    className: 'btn-primary',
                    callback: function () {
                        editStrategyConfirm();
                    }
                },
                // 确认
                ok: {
                    label: LANG.UI_GLOBAL_STRATEGY_DISPENSE_YES,
                    className: 'btn-primary',
                    callback: function () {
                        initModal();
                    }
                }
            }
        });
    }

    // 添加策略提交
    let submit = function () {
        if (!groupData()) return false;
        Metronic.blockUI({ target: '#backupStrategyContent', animate: true, cenrerY: true, });
        if (editFlag) {
            let nameBack = function (res) {
                Metronic.unblockUI('#backupStrategyContent');
                if (res.success && res.data.task_count > 0) {
                    //如果有备份点信息或任务依赖 进行分发选择
                    dispenseCheck();
                } else {
                    // 没有  删除确认
                    editStrategyConfirm();
                }
            }
            pAjaxRequest({}, `/api/v1/strategies/check/${data.strategy_uuid}`, "GET", nameBack, true);
        } else {
            let addBack = function (res) {
                Metronic.unblockUI('#backupStrategyContent');
                if (operateResponseList(res)) {
                    LOCATION('./content/platform/strategy/global_strategy.php', 'backup_manager');
                } else {
                    UIToastr.showInfo(res.message);
                }
            };
            pAjaxRequest(deepCloneObject(data), "/api/v1/strategies", "POST", addBack, true);
        }
    }

    // 策略消息组合
    let groupData = function () {
        let permission = CONF.PERMISSION;
        // 策略uuid
        if (editFlag) {
            data.strategy_uuid = $('#strategy_uuid').val();
        }
        // 模块
        data.strategy_type = parseInt($('#strategyType').val());
        if (!permission.includes(MODULE_FIXED[data.strategy_type])) {
            if (editFlag) {
                UIToastr.showWarning(STRATEGY_TIP, LANG.UI_GLOBAL_STRATEGY_STRATEGY_TYPE_EMPTY_TIPS_EDIT);
            } else {
                UIToastr.showWarning(STRATEGY_TIP, LANG.UI_GLOBAL_STRATEGY_STRATEGY_TYPE_EMPTY_TIPS);
            }
            return false;
        }
        // 备注
        data.remark = $('#remark').val();
        data.create_time = getNowTime();
        // 策略名称
        let name = $('#strategyName').val().trim();
        // 名称不能输入特殊字符
		if(!customInputValidate('string',name)){
			return false;
		}
        data.strategy_name = name;
        // 备注不能输入特殊字符
		if(!customInputValidate('string',data.remark)){
			return false;
		}
        if (data.strategy_name == '') {
            UIToastr.showWarning(STRATEGY_TIP, LANG.UI_GLOBAL_STRATEGY_STRATEGY_NAME_EMPTY_TIPS);
            return false;
        }
        data.strategyInfo = $('#backupStrategy').getBackupStrategy();
        if (data.strategyInfo === false) {
            return false;
        }
        // 策略组必须配置至少一个策略
        if(!data.strategyInfo.time.timeInfo && !data.strategyInfo.speedlimit.speedInfo && !data.strategyInfo.store.storeInfo && !data.strategyInfo.reserve.reserveInfo){
            UIToastr.showWarning(STRATEGY_TIP, LANG.UI_GLOBAL_STRATEGY_EMPTY_CONFIG_TIPS);
            return false;
        }
        return true;
    }
    let getNowTime = () => {
        // 创建一个Date对象，这将自动设置为当前日期和时间
        let now = new Date();
        // 格式化日期和时间 两位数显示
        const padZero = (num) => num.toString().padStart(2, '0');
        // 月份是从0开始，所以需要加1
        let formattedTime = `${now.getFullYear()}-${padZero(now.getMonth() + 1)}-${padZero(now.getDate())} ${padZero(now.getHours())}:${padZero(now.getMinutes())}:${padZero(now.getSeconds())}`;

        return formattedTime;
    }

    return {
        init: function () {
            initDefaultData();
            initListeners(); //初始化监听事件
        }
    }
}();

$(document).ready(function () {
    addStrategy.init();
});