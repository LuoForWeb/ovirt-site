/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-28 17:20:30
 * @Description: 业务处理工具函数
 * @version: 1.0
 */

const xssEncode = function (str) {
    if (!str || str == "") return str;
    let des = str.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return des;
}

//顶部菜单定位
//menu: bakandrec, managerment, orch, logalarm
const CTLHORMENU = function (menu) {
    $('.classic-menu-dropdown').removeClass('active');
    $('.mega-menu-dropdown').removeClass('active');
    $('.hor-menu').find('li[data-name=' + menu + ']').addClass('active');
}

/**
 * 统一初始化数字输入框
 * @param dom	保留策略输入框dom对象
 * @param oldeValue	修改初始化初始值
 */
const initReserveSpinner = function (dom, oldeValue = 0) {
    let value = 30;
    if (oldeValue != 0) {
        value = oldeValue;
    }
    dom.spinner({
        value: value,
        step: 5,
        min: 1,
        max: 9999
    });
}

 //---------统一部分策略信息开始--------------
 //----------统一时间策略
 //时间策略传参转换,将以前老的返回参数更新为新版PHP接收的参数格式,部分参数名字有变化
const unifyTimeStrategy = function (data) {
    let info = {};
    //初始化数据
    info.backup_type = 0;
    info.once_start_time = "";
    info.full_backup = {};
    info.incremental_backup = {};
    info.differential_backup = {};
    info.forever_incremental = {};
    if (data == "" || data == undefined || data == null) {
        //如果没数据直接返回空
        return info;
    }
    if (data.type == "oncetime") {
        //一次性备份
        info.backup_type = 2;
        info.once_start_time = data.datetime;
        return info;
    } else {
        //有备份模式的
        info.backup_type = 1;
        //完全备份
        if (data.fullInfo && typeof data.fullInfo === 'object' && Object.keys(data.fullInfo).length !== 0) {
            //获取备份类型
            info.full_backup.strategy_type = data.fullInfo.type;
            info.full_backup.days = data.fullInfo.days;
            info.full_backup.start_time = data.fullInfo.startTime;
            info.full_backup.roll_flag = data.fullInfo.rollFlag;
            info.full_backup.roll_interval = data.fullInfo.rollInterval;
            info.full_backup.roll_end_time = data.fullInfo.endTime;
            info.full_backup.frequency = data.fullInfo.frequency ? data.fullInfo.frequency : "";
            info.full_backup.des = data.fullInfo.des;
        }
        //增量备份
        if (data.incrInfo && typeof data.incrInfo === 'object' && Object.keys(data.incrInfo).length !== 0) {
            //获取备份类型
            info.incremental_backup.strategy_type = data.incrInfo.type;
            info.incremental_backup.days = data.incrInfo.days;
            info.incremental_backup.start_time = data.incrInfo.startTime;
            info.incremental_backup.roll_flag = data.incrInfo.rollFlag;
            info.incremental_backup.roll_interval = data.incrInfo.rollInterval;
            info.incremental_backup.roll_end_time = data.incrInfo.endTime;
            info.incremental_backup.frequency = data.incrInfo.frequency ? data.incrInfo.frequency : "";
            info.incremental_backup.des = data.incrInfo.des;
        }
        //差异备份
        if (data.diffInfo && typeof data.diffInfo === 'object' && Object.keys(data.diffInfo).length !== 0) {
            //获取备份类型
            info.differential_backup.strategy_type = data.diffInfo.type;
            info.differential_backup.days = data.diffInfo.days;
            info.differential_backup.start_time = data.diffInfo.startTime;
            info.differential_backup.roll_flag = data.diffInfo.rollFlag;
            info.differential_backup.roll_interval = data.diffInfo.rollInterval;
            info.differential_backup.roll_end_time = data.diffInfo.endTime;
            info.differential_backup.frequency = data.diffInfo.frequency ? data.diffInfo.frequency : "";
            info.differential_backup.des = data.diffInfo.des;
        }
        //永久增量备份
        if (data.pIncrInfo && typeof data.pIncrInfo === 'object' && Object.keys(data.pIncrInfo).length !== 0) {
            //获取备份类型
            info.forever_incremental.strategy_type = data.pIncrInfo.type;
            info.forever_incremental.days = data.pIncrInfo.days;
            info.forever_incremental.start_time = data.pIncrInfo.startTime;
            info.forever_incremental.roll_flag = data.pIncrInfo.rollFlag;
            info.forever_incremental.roll_interval = data.pIncrInfo.rollInterval;
            info.forever_incremental.roll_end_time = data.pIncrInfo.endTime;
            info.forever_incremental.frequency = data.pIncrInfo.frequency ? data.pIncrInfo.frequency : "";
            info.forever_incremental.des = data.pIncrInfo.des;
        }
        return info
    }
}
 //----------限速策略
 //统一限速策略
const unifySpeedStrategy = function (data) {
    let info = [];
    if (!data || data == "" || data == null || data == undefined || data.length == 0) {
        return info;
    }
    for (let i = 0; i < data.length; i++) {
        switch (data[i].type) {
            case 1:
            case 2:
            case 3:
                let limitSpeed = {};
                limitSpeed.speed_type = data[i].type;
                limitSpeed.start_time = data[i].startTime;
                limitSpeed.end_time = data[i].endTime;
                limitSpeed.days = data[i].days;
                limitSpeed.value = data[i].value;
                info.push(limitSpeed);
                break;
            case 4:
                //如果为永久限速
                let foreverSpeed = {};
                foreverSpeed.speed_type = data[i].type;
                foreverSpeed.value = data[i].value;
                info.push(foreverSpeed);
                break;
        }
    }
    return info;
}

/**
 * 获取配置的限速策略
 * @returns {{}}
 */
const getSpeedStrategyInfo = function (){
    let info = {};
    info['level'] = $('#tasklevelselect').val();
    info['type'] = parseInt($('#speedtypeselect').val());
    info['speedInfo'] = [];
    if (info['type'] === 1) {
        // 选择策略
        let selectedRow = $('.strategy-table #table').bootstrapTable('getSelections');
        if (selectedRow.length === 1) {
            info['uuid'] = selectedRow[0].uuid
            info['name'] = selectedRow[0].name
            info['strategy_type'] = selectedRow[0].type
            info['speedInfo'] = [{'des': selectedRow[0].detail}]
        }
    } else {
        // 自定义
        info['speedInfo'] = $('#speedstrategy').getSpeedStrategyConfigFinal();
    }
    return info;
}

 //----------存储策略
const unifyStorageStrategy = function (data) {
    let info = {};
    if (data == "" || data == undefined || data == null || Object.keys(data).length == 0) {
        return info;
    }
    //重复数据删除
    if (typeof data.deduplication !== "undefined" && data.deduplication !== null) {
        info.deduplication_flag = data.deduplication;
    }
    //压缩存储
    if (typeof data.compress !== "undefined" && data.compress !== null) {
        info.compress_flag = data.compress;
    }
    //数据加密
    if (typeof data.encrypt !== "undefined" && data.encrypt !== null) {
        info.encrypt_flag = data.encrypt;
    }
    //自动生成密码
    if (typeof data.password_auto_flag !== "undefined" && data.password_auto_flag !== null) {
        info.auto_create_password_flag = data.password_auto_flag;
    }
    //数据加密密码
    if (typeof data.password !== "undefined" && data.password !== null) {
        info.password = data.password;
    }
    //存储块大小
    if (typeof data.blocksize !== "undefined" && data.blocksize !== null) {
        info.block_size = data.blocksize;
    }
    return info;
}

 //----------保留策略
const UnifyReserveStrategy = function (data) {
    let info = {};
    if (data == "" || data == undefined || data == null || Object.keys(data).length == 0) {
        return info;
    }
    //保留类型
    if (typeof data.type !== "undefined" && data.type !== null) {
        info.reserved_type = data.type;
    }
    //保留值
    if (typeof data.value !== "undefined" && data.value !== null) {
        info.value = data.value;
    }
    //自动归档
    if (typeof data.auto_archive_flag !== "undefined" && data.auto_archive_flag !== null) {
        info.auto_archive_flag = data.auto_archive_flag;
    }
    //GFS
    if (typeof data.gfs_strategy_item_list !== "undefined" && data.gfs_strategy_item_list !== null) {
        info.gfs_reserved_strategy = [];
        if (data.gfs_strategy_item_list.length != 0) {
            for (let i = 0; i < data.gfs_strategy_item_list.length; i++) {
                let gfe_each = {};
                gfe_each.gfs_reserved_type = data.gfs_strategy_item_list[i].level1_type;
                gfe_each.gfs_reserved_start = data.gfs_strategy_item_list[i].level2_type;
                gfe_each.gfs_reserved_value = data.gfs_strategy_item_list[i].retention_num;
                info.gfs_reserved_strategy.push(gfe_each);
            }
        }
    }
    return info;
}
//---------统一部分策略信息结束--------------

//处理删除按钮样式切换
const modifyDelStyle = function (tableid, opid) {
	let selectedRow = $('#' + tableid).bootstrapTable('getSelections');
	if (selectedRow.length < 1) {
		$('#' + opid).addClass('exch-forbid-event').removeClass('green-haze');
		$('#' + opid).parent().css({"cursor": "not-allowed"});
		$('#' + opid).css({"color": "#d7d9db"});
	} else {
		$('#' + opid).removeClass('exch-forbid-event').addClass('green-haze');
		$('#' + opid).parent().css({"cursor": "pointer"});
		$('#' + opid).css({"color": "#FFFFFF"});
	}
}

/**
 * 匹配 start 和 end 之间的内容，并替换为指定的内容
 * @param {*} str 要处理的字符串
 * @param {*} start 起始字符
 * @param {*} end 结束字符
 * @param {*} replaceWith 替换字符串
 */
const replaceBetweenStartEnd = (str, start, end, replaceWith) => {
// 使用了非贪婪匹配（*?），以防止匹配到最远的 end 字符
const regex = new RegExp('\\' + start + '([^' + '\\' + end + ']*)' + '\\' + end, 'g');

// 使用 replace() 方法替换匹配到的内容
return str.replace(regex, start + replaceWith + end);
}

//获取源列表名称(文件、nas、hadoop、对象存储用)
    const getSrcListName = function (data) {
    let des = '';
    let num = 0;
    if (data.taskTypeFlag == 1) {//备份
        num = parseInt(data.des_module_type);
    } else {//恢复
        num = parseInt(data.src_sub_module_type);
    }
    switch (parseInt(num)) {
        case 1:   //fs
            des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_COPY_DETAIL_FS_LIST;
            break;
        case 2:   //nas
            des = '<i class="viconfont vicon-nasmanager"></i>' + LANG.UI_COPY_DETAIL_NAS_LIST;
            break;
        case 3:   //hadoop
            des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_HADOOP_CLUSTER_LIST;
            break;
        case 4:   //obs
            des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_PLATFORM_DES_OBS_list;
            break;
    }
    $('#srcList').html(des);
}

const safeData = function (wore, virus_scan, complete) {
	let safe_config_strategy= {}
	if(wore) {
		safe_config_strategy.worm_flag = wore.worm_flag;
		safe_config_strategy.worm_protection_time = wore.worm_protection_time;
	} else {
		safe_config_strategy.worm_flag = 0;
		safe_config_strategy.worm_protection_time = 0
	}
	if(virus_scan) {
		safe_config_strategy.virus_scan_flag = virus_scan.virus_scan_flag;
		safe_config_strategy.virus_scan_config_list = JSON.stringify(virus_scan.virus_scan_config_list);
	} else {
		safe_config_strategy.virus_scan_flag = 0;
		safe_config_strategy.virus_scan_config_list = '';
	}
	if(complete) {
		safe_config_strategy.integrity_check_flag = complete.integrity_check_flag;
		safe_config_strategy.integrity_check_config = complete.integrity_check_config;
	} else {
		safe_config_strategy.integrity_check_flag = 0;
		safe_config_strategy.integrity_check_config = {};
	}
	return safe_config_strategy
}

/**
 * 获取表格过滤器参数
 * @param {*} key 过滤器选项组中指定的field，如 'module_type' 'task_type' 'task_status'
 * @param {*} data 过滤器勾选的数组
 * @returns
 */
// eslint-disable-next-line no-unused-vars
const getTableFilterParams = (key, data) => {
    const item = data.find(i => i.key === key);
    return item ? item : null;
};

// 任务类型常量，键代表包含多个任务类型的module_type，比如：4代表数据库，其value 数组包含多个任务类型，即：数据库备份 数据库恢复 数据库实时恢复 数据库副本
const JOB_TYPE_CONSTANTS = {
    "2": ['52', '53', '54', '55'],
    "4": ['28', '29', '47', '30'],
    "5": ['32', '35', '33', '36', '38', '40', '49']
};

/**
 * 根据权限过滤得到有权限的树形数组，并移除没有权限或子项全被过滤掉的节点
 */
const filterMenuTree = (tree, permissions) => {
    // 递归过滤函数
    const filterNode = (node) => {
        if (node.children && node.children.length > 0) {
            const hasPermission = permissions.includes(node.id);
            if (!hasPermission) return null;

            const filteredChildren = node.children
                .map(filterNode)
                .filter(child => child !== null);

            // 返回新节点时带上标记表示它原本有 children
            return { ...node, children: filteredChildren, hasOriginalChildren: true };
        } else {
            return permissions.includes(node.id) ? { ...node } : null;
        }
    };

    // 第一阶段：递归过滤整棵树
    const partiallyFilteredTree = tree.map(filterNode).filter(node => node !== null);

    // 第二阶段：再次过滤顶层节点，去掉那些原本有 children 但被过滤成空数组的情况
    return partiallyFilteredTree.filter(node => {
        // 如果原本有 children 但过滤后为空数组，则移除
        if (node.hasOriginalChildren && node.children && node.children.length === 0) {
            return false;
        }
        return true;
    });
};

/**
 * 根据勾选的模块过滤最终的任务类型
 * @param {*} jobTypeArray 勾选的任务类型数组
 * @param {*} moduleTypeArr 模块类型集合
 * @param {*} jobTypeConstants 任务类型常量
 * @returns 
 */
const filterJobTypes = (jobTypeArray, moduleTypeArr, jobTypeConstants) => {
    return jobTypeArray.filter(jobType => {
        for (const [key, values] of Object.entries(jobTypeConstants)) {
            if (!moduleTypeArr.has(key) && values.includes(jobType)) {
                return false;
            }
        }
        return true;
    });
}

/**
 * 处理当前任务/历史任务中 业务类型、对象类型、任务类型和任务状态的过滤器组件传参
 * @param {*} filterData 
 */
const handleUpdateFilterParams = (filterData) => {

    let checkedFilterParams = { module_type: [], sub_module_type: [], job_type: [], job_status: [], storage_location: [], dev_type: [] };
    let moduleTypeArr = [], subModuleTypeArr = [], storageLocationArr = [], devTypeArr = [];

    if (filterData.length > 0) {
        const timingProtectData = filterData.find(i => i.key === "timing_data_protect")?.value || []; // 定时保护勾选数据
        const realTimeProtectData = filterData.find(i => i.key === "real_time_data_protect")?.value || []; // 实时保护勾选数据
        const dataCopyData = filterData.find(i => i.key === "data_copy")?.value || []; // 数据复制勾选数据
        const taskTypeData = filterData.find(i => i.key === "task_type")?.value || []; // 任务类型勾选数据
        const taskStatusData = filterData.find(i => i.key === "task_status")?.value || []; // 任务状态勾选数据
        
        // 勾选了定时保护或实时保护或数据复制时，处理 module_type 和 sub_module_type 
        if (!timingProtectData.length && !realTimeProtectData.length && !dataCopyData.length) {
            checkedFilterParams = { module_type: [], sub_module_type: [], job_type: [], job_status: [], storage_location: [], dev_type: [] };
        } else {
            if (timingProtectData.length > 0) { // 勾选了定时保护列的数据
                timingProtectData.forEach(item => {
                    const itemData = item.split('-');

                    // 定时备份模块不含子模块的模块，sub_module_type默认给0，所以length总是2
                    moduleTypeArr.push(itemData[0]);
                    subModuleTypeArr.push(itemData[1]);
                });
            }

            if (realTimeProtectData.length > 0) { // 勾选了实时保护列的数据，split分隔后，第一个元素表示 module_type,第二个元素表示storage_location（1：备份 3：复制），第三个元素表示 dev_type（1：卷 2：磁盘）
                realTimeProtectData.forEach(item => {
                    const itemData = item.split('-');

                    moduleTypeArr.push(itemData[0]);
                    storageLocationArr.push(itemData[1]);
                    devTypeArr.push(itemData[2]);
                });
            }

            if (dataCopyData.length > 0) { // 勾选了数据复制列的数据
                dataCopyData.forEach(item => {
                    const itemData = item.split('-');

                    if (itemData.length === 2) { // 勾选的文件复制或数据库复制
                        moduleTypeArr.push(itemData[0]);
                        subModuleTypeArr.push(itemData[1]);
                    } else { // 勾选的整机或卷时：split分隔后，第一个元素表示 module_type,第二个元素表示sub_module_type（默认给0），第三个元素表示 dev_type（1：卷 2：磁盘）
                        moduleTypeArr.push(itemData[0]);
                        storageLocationArr.push(itemData[1]);
                        devTypeArr.push(itemData[2]);
                    }
                });
            }

            checkedFilterParams = { 
                module_type: moduleTypeArr, 
                sub_module_type: subModuleTypeArr, 
                job_type: [], 
                job_status: [], 
                storage_location: storageLocationArr, 
                dev_type: devTypeArr 
            };
        }
        
        // 勾选了任务类型数据
        if (taskTypeData.length > 0) {
            checkedFilterParams.job_type = taskTypeData.join(',').split(',');
            
            // 过滤任务类型
            if (moduleTypeArr.size > 0) {
                checkedFilterParams.job_type = filterJobTypes(
                    checkedFilterParams.job_type,
                    moduleTypeArr,
                    JOB_TYPE_CONSTANTS
                );
            }
        }

        // 勾选了任务状态数据
        if (taskStatusData.length > 0) {
            checkedFilterParams.job_status = taskStatusData;
        }
    } else {
        checkedFilterParams = {
            module_type: [],
            sub_module_type: [],
            job_type: [],
            job_status: [],
            storage_location: [],
            dev_type: []
        }
    }

    return checkedFilterParams;
}

/**
 * 获取资源限制描述
 */
const getResourceLimitDesHtml = (timeType, timeList) => {
    const WEEK_DES_MAP = {
        1: LANG.UI_PUBLIC_NUMBER_ONE,
        2: LANG.UI_PUBLIC_NUMBER_TWO,
        3: LANG.UI_PUBLIC_NUMBER_THREE,
        4: LANG.UI_PUBLIC_NUMBER_FOUR,
        5: LANG.UI_PUBLIC_NUMBER_FIVE,
        6: LANG.UI_PUBLIC_NUMBER_SIX,
        7: LANG.UI_PUBLIC_NUMBER_SEVEN,
    }

    let des = '';
    switch (timeType) {
        case 1: // 每天
            des = timeList.map(item => { 
                return `
                        ${LANG.UI_STRATEGY_DAY}${item.prohibit_start_time}${LANG.UI_STRATEGY_START}，
                        ${item.prohibit_end_time}${LANG.UI_STRATEGY_END};`}).join('<br>');
            break;
        case 2: // 每周
            des = timeList.map(item => { 
                return `
                        ${LANG.UI_STRATEGY_WEEK}
                        ${item.days.map((v, i) => v === 1 ? WEEK_DES_MAP[i + 1] : null).filter(day => day !== null).join('，')}
                        ${item.prohibit_start_time}${LANG.UI_STRATEGY_START}，
                        ${item.prohibit_end_time}${LANG.UI_STRATEGY_END}
                        ;` }).join('<br>');
            break;
        case 3: // 每月
            des = timeList.map(item => { 
                return `
                        ${LANG.UI_STRATEGY_MONTH}
                        ${item.days.map((v, i) => v === 1 ? i + 1 : null).filter(day => day !== null).join(',')}
                        ${item.prohibit_start_time}${LANG.UI_STRATEGY_START}，
                        ${item.prohibit_end_time}${LANG.UI_STRATEGY_END}
                        ;` }).join('<br>');
            break;
        case 4: // 自定义
            des = timeList.map(item => { 
                return `
                        ${item.prohibit_start_time}${LANG.UI_STRATEGY_START}，
                        ${item.prohibit_end_time}${LANG.UI_STRATEGY_END};` }).join('<br>');
        default:
            des = '--';
            break;
    }

    return des;
}

/**
 * 过滤掉对象中的空数组
 * @param {*} obj 
 * @returns 
 */
const filterEmptyArrayProperties = (obj) => {
    return Object.keys(obj).reduce((acc, key) => {
        if (!(Array.isArray(obj[key]) && obj[key].length === 0)) {
            acc[key] = obj[key];
        }
        return acc;
    }, {});
};

/**
 * 生成tooltip中带标题的文案内容
 * @param {*} textArr JSON文本数组，[{title: 'xxx', description: 'xxx'}]
 * @returns 
 */
const getWithTitleCopyWrite = (textArr) => {
    if (!Array.isArray(textArr) || textArr.length === 0) return;

    let html = `<div class="tooltips-with-title-wrap">`;

    for (let i = 0; i < textArr.length; i++) {
        html += `<div class="tooltips-with-title-wrap__item">
                    <span class="title">${textArr[i].title}${CONF.LANGUAGE === 'zh-cn' ? '：' : ':'}</span>
                    ${textArr[i].description}
                </div>`;

        if (i !== textArr.length - 1) { // 最后一个不加分割线
            html += `<div class="separator--dashed my-8"></div>`;
        }
    }

    return html;
}

/**
 * 生成tooltip中带序号的文案内容
 * @param {*} textArr 字符串文本数组，['XXX', 'XXXX']
 * @returns 
 */
const getWithSerialNumbersCopyWrite = (textArr) => {
    if (!Array.isArray(textArr) || textArr.length === 0) return;

    let html = `<ol class="tooltips-with-numbers-wrap">`;

    for (let i = 0; i < textArr.length; i++) {
        html += `<li class="tooltips-with-numbers-wrap__item">${textArr[i]}</li>`;
    }

    html += `</ol>`;

    return html;
}

/**
 * 获取备份节点资源限制参数
 * 用于各模块备份页面-高级配置-过载保护处显示
 */
const initResourceLimit = (node_uuid_list) => {
	const WEEK_DES_MAP = {
		1: LANG.UI_PUBLIC_NUMBER_ONE,
		2: LANG.UI_PUBLIC_NUMBER_TWO,
		3: LANG.UI_PUBLIC_NUMBER_THREE,
		4: LANG.UI_PUBLIC_NUMBER_FOUR,
		5: LANG.UI_PUBLIC_NUMBER_FIVE,
		6: LANG.UI_PUBLIC_NUMBER_SIX,
		7: LANG.UI_PUBLIC_NUMBER_SEVEN,
	}
	vinchinUI.blockUI({target: '#resourceLimitModal', animate: true});
	let options = {
		pagination: true,
		pageList: [5, 10, 25, 50],
		detailView: false,
		resizable: false, //可变宽度
		vin_params:function(){
			return {node_uuid_list: node_uuid_list};
		},
		vin_url: "/api/v1/nodes/resources_limit/batch",
		vin_method: "GET",
		onPostBody: (data) => {
			$('#nodeLimitTable th[data-field="max_task_running_num"]').css('width', '25%');
			$('#nodeLimitTable th[data-field="time"]').css('width', '42%');
			if (data.length <= 5) {
				$('.node-limit-form .fixed-table-pagination').hide();
			} else {
				$('.node-limit-form .fixed-table-pagination').show();
			}

		},
		columns: [
			{
				field: 'name',
				title: LANG.UI_NODE_REMOTE_NODE_NAME,
				sortable: false,
				align: 'center',
				formatter: function (value, data, row) {
					if (!data.node_config_flag || !data.init_flag) {
						return '--';
					}
					let name = (data.node_nickname != "" ? data.node_nickname : data.host_name) + `(` + data.ip + `)`;
					return `<span title="`+ name +`">`+ name +`</span>`;
				}
		},
			{
				field: 'max_task_running_num',
				title: LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT,
				sortable: false,
				align: 'center',
				formatter: function (value, data, row) {
					if (!data.node_config_flag || !data.init_flag) {
						return '--';
					}
					return data.max_task_running_num;
				}
		},
			{
				field: 'time',
				title: LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD,
				sortable: false,
				align: 'center',
				formatter: function (value, data, row) {
					let des = '';
					if (!data.node_config_flag || !data.init_flag) {
						return '--';
					}
					let timeType = data.prohibit_time_type;
					let timeList = data.prohibit_time_info;
					switch (timeType) {
						case 1: // 每天
							des = timeList.map(item => {
								return `
										${LANG.UI_STRATEGY_DAY}${item.start_time}${LANG.UI_STRATEGY_START}，
										${item.end_time}${LANG.UI_STRATEGY_END}
										;`}).join('');
							break;
						case 2: // 每周
							des = timeList.map(item => {
								return `
										${LANG.UI_STRATEGY_WEEK}
										${item.days.map((v, i) => v === 1 ? WEEK_DES_MAP[i + 1] : null).filter(day => day !== null).join('，')}
										${item.start_time}${LANG.UI_STRATEGY_START}，
										${item.end_time}${LANG.UI_STRATEGY_END}
										;` }).join('');
							break;
						case 3: // 每月
							des = timeList.map(item => {
								return `
										${LANG.UI_STRATEGY_MONTH}
										${item.days.map((v, i) => v === 1 ? i + 1 : null).filter(day => day !== null).join(',')}
										${item.start_time}${LANG.UI_STRATEGY_START}，
										${item.end_time}${LANG.UI_STRATEGY_END}
										;` }).join('');
							break;
						case 4: // 自定义
							des = timeList.map(item => {
								return `
										${item.start_time}${LANG.UI_STRATEGY_START}，
										${item.end_time}${LANG.UI_STRATEGY_END};` }).join('');
							break;
						default:
							break;
					}
					let title = des.replace(/\s+/g, ' ').replace(/;/g, '\n');
					if (des.replace(/\s+/g, '') == "") {
						return `--`;
					}
					return `<span title = "`+ title +`">` + des + `</span>`;
				}
		},
		],
	}
	$('#nodeLimitTable').bootstrapTable('destroy').baseTableConfig().init(options);
};

/**
 * 递归遍历树形数组，根据提供的值数组为匹配的节点设置  checked: true
 * @param {*} treeData - 要遍历的树形数组
 * @param {*} valuesToCkeck - 需要设置为 checked 的 value 值的数组
 * @returns 返回一个带有 checked 标记的新树形数组
 */
const setCheckedByValues = (treeData, valuesToCkeck) => {
    const valuesSet = new Set(valuesToCkeck);

    const traverse = (nodes) => {
        if (!Array.isArray(nodes)) {
            return nodes;
        }

        return nodes.map(node => {
            // 创建一个节点对象的浅拷贝
            const newNode = { ...node };

            // 检查当前节点的 value 是否在 Set 中
            if (valuesSet.has(newNode.value)) {
                newNode.checked = true;
            }

            // 如果有子节点，则递归遍历，并用返回的新子节点数组替换
            if (newNode.children && newNode.children.length > 0) {
                newNode.children = traverse(newNode.children);
            }

            return newNode;
        });
    };

    return traverse(treeData);
};