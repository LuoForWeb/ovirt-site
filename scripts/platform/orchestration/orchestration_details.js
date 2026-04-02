var OrchestrationDetails = function () {
    let paginationOpenFlag = false; // 记录分页展开情况
    let initGridFlag = false; // 记录历史表格初始化情况
    let changeHeightFlag = false; // 记录高度改变情况
    let isDragging = false; // 拖拽标志
    let startX, startScrollLeft; //鼠标拖拽位置和滚动距离
    const BACKUP_TYPE = [CONF.TASK_TYPE.BACKUP, CONF.TASK_TYPE.OS_BACKUP, CONF.TASK_TYPE.NAS_BACKUP, CONF.TASK_TYPE.DB_BACKUP];
    // 模块类型图片
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

    let initData = () => {
        let init = () => {
            let planId = $('#plan_uuid').val();
            let infoBack = (res) => {
                let data = res.data;
                initDetails(data);
                initSectionList(data.section_list);
            };
            pAjaxRequest({ plan_uuid: planId }, "/api/v1/orchestration/details", "GET", infoBack, true);
            if (timerTask.orchestrationDetailsInfo) {
                clearTimeout(timerTask.orchestrationDetailsInfo);
            }
            timerTask.orchestrationDetailsInfo = setTimeout(init, 5000);
        }
        init();
    };
    // 初始化概要信息
    let initDetails = (data) => {
        $('.col-plan_nickname').html(data.plan_nickname); //名称
        let stateName = '';
        switch (data.plan_state) {
            case CONF.ORCHESTRATION_PLAN_STATUS.WAITING: // 等待
                stateName = '<span class="label label-sm label-info label-info_en">' + LANG.UI_VISUAL_WAIT + '</span>';
                break;
            case CONF.ORCHESTRATION_PLAN_STATUS.RUNNING: // 运行中
                stateName = '<span class="label label-sm label-success label-success_en">' + LANG.UI_VISUAL_RUNNING + '</span>';
                break;
            case CONF.ORCHESTRATION_PLAN_STATUS.PAUSED: // 暂停
                stateName = '<span class="label label-sm label-default label-default_en">' + LANG.UI_VISUAL_PAUSE + '</span>';
                break;
            case CONF.ORCHESTRATION_PLAN_STATUS.STOPPED: // 停止
                stateName = '<span class="label label-sm label-default label-default_en">' + LANG.UI_VISUAL_STOP + '</span>';
                break;
            case CONF.ORCHESTRATION_PLAN_STATUS.ERROR: // 错误
                stateName = '<span class="label label-sm label-danger label-danger_en">' + LANG.UI_PUBLIC_ERROR + '</span>';
                break;
        };
        setBtnStatus(data);
        $('.col-plan_state').html(stateName); //状态
        if (data.time_strategy_id == -1) {
            $('.col-time_strategy').html(LANG.UI_BACKUP_MANUAL);
        } else {
            $('.col-time_strategy').html(getTimeStrategy(data.time_strategy).time);
        }
        $('.col-task_numbers').html(data.tasks_number + LANG.UI_PUBLIC_NUM); //任务数量
        $('.col-execute_count').html(data.execute_count + LANG.UI_PUBLIC_UNIT_COUNT); //执行次数
        $('.col-next_time').html(data.next_start_time); //下次执行时间
    };
    // 初始化阶段
    let initSectionList = (sections) => {
        $('.section-wrapper').empty();
        sections.forEach((item, index) => {
            let id = index + 1;
            let number = id < 10 ? '0' + id : id; // 不足两位数 补0
            let content = '';
            let event = getEventDes(item);
            let taskContent = getTask(item);
            // 阶段状态
            let state = item.section_state;
            let stateClass = '';
            let stateDes = '';
            switch (state) {
                case CONF.ORCHESTRATION_SECTION_STATUS.WAITING:
                    stateClass = 'waiting';
                    stateDes = LANG.UI_PUBLIC_WAIT;
                    break;
                case CONF.ORCHESTRATION_SECTION_STATUS.RUNNING:
                    stateClass = 'running';
                    stateDes = LANG.UI_JOB_TASK_ORCHESTRATION_SECTION_STATUS_RUNNING;
                    break;
                case CONF.ORCHESTRATION_SECTION_STATUS.FINISHED:
                    stateClass = 'success';
                    stateDes = LANG.UI_FILE_COMPLETED;
                    break;
                case CONF.ORCHESTRATION_SECTION_STATUS.STOPPED:
                    stateClass = 'stopped';
                    stateDes = LANG.UI_VISUAL_STOP;
                    break;
            };

            // 第一个阶段前面没有箭头
            if (id > 1) {
                if (state == CONF.ORCHESTRATION_SECTION_STATUS.RUNNING || state == CONF.ORCHESTRATION_SECTION_STATUS.FINISHED) {
                    content += `<div id="next-vector${id}"><div class="next-vector"></div></div>`
                } else {
                    content += `<div id="next-vector${id}"><div class="next-vector  next-vector-grey"></div></div>`
                }
            }
            content += `
                    <div class="section-card card-state-${stateClass}" id="sectionCard${id}">
                    <div class="plan-section">
                        <div class="form-group section-header" id="sectionHeader">
                            <div class="header-add col-md-12">
                                <div class="card-state col-md-10">
                                    <div class="state-${stateClass}">${stateDes}</div>
                                </div>
                                <div class="card-index col-md-2 ${stateClass}"><span>${number}</span></div>
                            </div>
                        </div>
                        <!-- 触发条件 -->
                        ${event}
                        <div class="form-group section-body">
                            <div class="card-items-list"  id="itemList${id}">${taskContent}</div>
                        </div>
                    </div>
                </div>`;
            $('.section-wrapper').append(content);
            // 初始化勾选框
            $(`#eventMode${id} .icheck`).iCheck({ checkboxClass: 'icheckbox_square-blue' });
        });
    };
    // 触发事件
    let getEventDes = (item) => {
        let timeDes = '<div class="event-driveEvent">' + LANG.UI_JOB_TASK_ORCHESTRATION_DRIVE_LABEL + '：';
        let startType = item.start_type; //触发事件 1按时间触发 2按事件触发 3事件+时间
        let startDes = '';
        let endDes = '';
        let triggerDes = '<div class="event-trigger">' + LANG.UI_JOB_TASK_ORCHESTRATION_SECTION_TRIGGER_STATUS + '：';
        if (startType == 1) {
            timeDes += LANG.UI_JOB_TASK_ORCHESTRATION_TIME_INTERVAL + formatSecondsAsHHMMSS(item.execution_interval) + '</div>';
        }
        if (startType == 2) {
            timeDes += getEvent(item.event_type, item.driveDes) + '</div>';
        }
        if (startType == 3) {
            timeDes += LANG.UI_JOB_TASK_ORCHESTRATION_TIME_INTERVAL + formatSecondsAsHHMMSS(item.execution_interval) + "，" + getEvent(item.event_type, item.driveDes) + '</div>';
        }
        if (startType == 0) {
            timeDes = '';
        }
        // 开始时间
        if (item.start_time != '0000-00-00 00:00:00') {
            startDes = '<div class="event-startTime">' + LANG.UI_PUBLIC_START_TIME + '：' + item.start_time + '</div>';
        }
        // 结束时间
        if (item.end_time != '0000-00-00 00:00:00') {
            endDes = '<div class="event-endTime">' + LANG.UI_PUBLIC_END_TIME + '：' + item.end_time + '</div>';
        }
        // 触发状态
        if (item.trigger_flag) {
            triggerDes += LANG.UI_JOB_TASK_ORCHESTRATION_SECTION_TRIGGER_STATUS_YES + '</div>';
        } else {
            triggerDes += LANG.UI_JOB_TASK_ORCHESTRATION_SECTION_TRIGGER_STATUS_NO + '</div>';
        }
        let details = JSON.parse(item.details);
        let remarks = `<div title="${details.remarks}"  style="white-space: nowrap;text-overflow: ellipsis;overflow: hidden;">${LANG.UI_JOB_ORCHESTRATION_SECTION_REMARK}：${details.remarks}</div>`;

        return `<div class="section-event">${timeDes}${startDes}${endDes}${triggerDes}${remarks}</div>`;
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
        return ` ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
    // 事件触发信息
    let getEvent = (type, driveDes = '') => {
        let des = '';
        switch (type) {
            case 1: // 全部成功
                des = LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_SUCCESS_ALL;
                break;
            case 2: // 任意成功
                des = LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_SUCCESS_ANY;
                break;
            case 3: // 某些成功
                des = driveDes + LANG.UI_VISUAL_SUCCESS;
                break;
            case 4: // 全部失败
                des = LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_FAILLE_ALL;
                break;
            case 5: // 任意失败
                des = LANG.UI_JOB_TASK_ORCHESTRATION_EVENT_FAILLE_ANY;
                break;
            case 6: // 某些失败
                des = driveDes + LANG.UI_VISUAL_FAIL;
                break;
        }
        return des;
    };
    // 初始化任务item
    let getTask = (item) => {
        let content = '';
        let taskList = item.task_list;
        let sectionState = item.section_state;
        for (let i = 0; i < taskList.length; i++) {
            let task = taskList[i];
            let info = task.info;
            if (info.length == 0) {
                return '';
            }
            let strategyDes = getTimeStrategy(info.time_strategy); //时间策略描述
            // 图标信息
            let icon = MODULE_IMG[info.module_type];
            if (info.task_type == 17) {
                icon = COPY_IMG;
            }
            if (info.task_type == 37) {
                icon = DATA_VERIFY;
            }
            let stateLabel = getTaskState(task);
            let taskUrl = getTaskUrl(task);
            let progress = getTaskProgress(task);
            content += `
                <div class="card-item-wrapper" id="${task.task_uuid}" data-uuid="${task.task_uuid}" data-mode="${task.backup_mode}" data-name="${info.task_name}" data-strategy="${info.strategy_id}" data-des="${strategyDes.time}">
                    <div class="card-item">
                        <i class="item-icon-div viconfont ${icon}"></i>
                        ${taskUrl}
                        ${progress}
                        ${stateLabel}
                    </div>
                </div>`;

        }
        return content;
    };

    // 获取时间策略信息
    var getTimeStrategy = (msg) => {
        let timeInfo = { time: LANG.UI_PUBLIC_NOTHING };
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

            timeInfo.time = des;
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
    // 获取每天
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
    // 获取每个策略
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
        desEach += "<br>";
        return desEach;
    }
    // 完成状态和运行状态阶段的任务显示任务状态
    let getTaskState = (task) => {
        let state = task.info.task_status;
        // 任务状态
        switch (state) {
            case CONF.TASK_STATUS.WAITTING:
                return '<span class="label label-sm label-info label-info_en item-status">' + LANG.UI_PUBLIC_WAIT + '</span>';
            case CONF.TASK_STATUS.STOPPING:
                return '<span class="label label-sm label-info label-info_en item-status">' + LANG.UI_PUBLIC_STOPPING + '</span>';

            case CONF.TASK_STATUS.PREPARING:
                return '<span class="label label-sm label-info label-info_en item-status">' + LANG.UI_PUBLIC_READYING + '</span>';

            case CONF.TASK_STATUS.RUNNING:
                return '<span class="label label-sm label-success label-success_en item-status">' + LANG.UI_PUBLIC_RUNNING + '</span>';
            case CONF.TASK_STATUS.FINISHED:
                return '<span class="label label-sm label-success label-success_en item-status">' + LANG.UI_VISUAL_ALREADY_FINISH + '</span>';
            case CONF.TASK_STATUS.PAUSED:
                return '<span class="label label-sm label-success label-success_en item-status">' + LANG.UI_JOB_PAUSE + '</span>';

            case CONF.TASK_STATUS.SUCCESSED:
                return '<span class="label label-sm label-success label-success_en item-status">' + LANG.UI_PUBLIC_SUCCESS + '</span>';
            case CONF.TASK_STATUS.STARTING:
                return '<span class="label label-sm label-success label-success_en item-status">' + LANG.UI_PUBLIC_STARTING + '</span>';

            case CONF.TASK_STATUS.STOPPED:
                return '<span class="label label-sm label-default label-default_en item-status">' + LANG.UI_VISUAL_STOP + '</span>';
            case CONF.TASK_STATUS.ABNORMAL:
                return '<span class="label label-sm label-warning label-warning_en item-status">' + LANG.UI_NODE_ABNORMAL + '</span>';
            case CONF.TASK_STATUS.NETWORK_FAULT:
                return '<span class="label label-sm label-danger label-danger_en item-status" style="width:auto; min-width:40px">' + LANG.UI_PUBLIC_NETWORK_ERROR + '</span>';
            case CONF.TASK_STATUS.CREATING:
                return '<span class="label label-sm label-default label-default_en item-status">' + LANG.UI_PUBLIC_CREATING + '</span>';
            case CONF.TASK_STATUS.PENDING:
                return '<span class="label label-sm label-warning label-warning_en item-status">' + LANG.UI_PUBLIC_PENDING + '</span>';
            case CONF.TASK_STATUS.ERROR:
                return '<span class="label label-sm label-danger label-danger_en item-status" style="width:auto; min-width:40px">' + LANG.UI_PUBLIC_FAILED + '</span>';
            default:
                // return '<span class="label label-sm label-info  ">准备中</span>';
                break;
        }
    };
    function getTaskProgress(task) {
        let progress = task.info.progress;
        return '<span class="task-progress">' + progress + '</span>';
    };
    // 任务名可点击跳转详情页面
    let getTaskUrl = (task) => {
        let taskType = task.info.task_type;
        let taskName = getItemName(task.info, task.backup_mode);
        let taskId = task.task_uuid;
        let moduleType = task.info.module_type;
        let subModuleType = task.info.sub_module_type;
        let planId = $('#plan_uuid').val();
        // 组装url
        let nameStr = '';
        let url = getDetailsUrl(moduleType, taskType, subModuleType);
        // 数据验证
        if(taskType == 37){
            url = './content/platform/dataverification/verification_job_details.php';
        }
        nameStr = '<a href="' + url + '?type=' + taskType + '&uuid=' + taskId + '&orcPlan=' + planId + '" class="ajaxify item-name" name="task" title = "' + taskName + '">' + taskName + '</a>';
        if (taskType == CONF.TASK_TYPE.BACKUP_COPY || taskType == CONF.TASK_TYPE.BACKUP_COPY_FETCH || taskType == CONF.TASK_TYPE.ARCHIVE || taskType == CONF.TASK_TYPE.BACKUP_COPY_FETCH) {
            nameStr = '<a href=./content/copy/copy_job_details.php?type=' + taskType + '&module=' + moduleType + '&subType=' + subModuleType + '&uuid=' + taskId + '&orcPlan=' + planId + '" class="ajaxify item-name" name="task" title = "' + taskName + '">' + taskName + '</a>';
        }
        return nameStr;
    };

    /**
     * 根据任务类型和模式获取任务名称。
     * 
     * @param {Object} item - 任务对象，必须包含任务名称（task_name）。
     * @param {number} mode - 任务模式，影响返回的任务名称前缀。
     * @returns {string|boolean} 返回任务名称字符串或在特定条件下返回false。
     */
    function getItemName(item, mode) {
        let taskType = item.task_type; // 获取任务类型，转换为数字

        // 任务类型为备份时的详细处理逻辑
        if (BACKUP_TYPE.includes(taskType)) {
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
    //根据模块类型得到任务详情的页面地址
    var getDetailsUrl = (module, taskType, hypervisorType) => {
        let url = '';
        // 虚拟机
        switch (module) {
            case CONF.MODULE_TYPE.VM: // 2 虚拟机
                if (3 != hypervisorType) {
                    //虚拟机
                    url = './content/vm/vm_job_details.php';
                    if (CONF.TASK_TYPE.VM_INSTANT_RECOVERY == taskType) {
                        //瞬时恢复
                        url = "./content/vm/vm_instant_job_details.php";
                    }
                    if (CONF.TASK_TYPE.VM_INSTANT_RECOVERY_MOTION == taskType) {
                        //迁移
                        url = "./content/vm/vm_motion_job_details.php";
                    }
                    if (CONF.TASK_TYPE.VM_FILE_RECOVERY == taskType) {
                        //细粒度恢复
                        url = "./content/vm/vm_grain_job_details.php";
                    }

                    //数据验证
                    if (CONF.TASK_TYPE.SURE_BACKUP == taskType) {
                        url = "./content/platform/dataverification/verification_job_details.php";
                    }
                } else {
                    //AWS
                    url = './content/aws/aws_job_details.php';
                    if (CONF.TASK_TYPE.VM_FILE_RECOVERY == taskType) {
                        //细粒度恢复 todo 暂时用vm的
                        url = "./content/vm/vm_grain_job_details.php";
                    }
                }
                //CBR同步
                if (CONF.TASK_TYPE.VM_HUAWEI_CBR_SYNC == taskType) {
                    url = "./content/cbr/cbr_job_details.php";
                }
                break;
            case CONF.MODULE_TYPE.FS: // 3 文件
                url = './content/fs/fs_job_details.php';
                break;
            case CONF.MODULE_TYPE.DB: // 4 数据库
                url = './content/dbprotect/db_job_details.php';
                break;
            case CONF.MODULE_TYPE.OS: // 5 操作系统
                //操作系统
                if (CONF.TASK_TYPE.OS_INSTANT_RECOVERY == taskType) {
                    url = "./content/os/os_instant_job_details.php";
                } else if (CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION == taskType) {
                    //迁移
                    url = "./content/os/os_motion_job_details.php";
                } else {
                    url = "./content/os/os_job_details.php";
                }
                break;
            case CONF.MODULE_TYPE.KUBERNETES: // 28 k8s
                url = './content/kubernetes/kubernetes_job_details.php';
                break;
            case CONF.MODULE_TYPE.NAS: // 11 nas
                url = './content/nas/nas_job_details.php';
                break;
            case '30': // 12 数据库cdp
                url = "./content/platform/dataverification/verification_job_details.php";
                break;
            case CONF.MODULE_TYPE.M365: // 14 exchange
                url = './content/exchange/exchange_job_details.php';
                break;
            case CONF.MODULE_TYPE.FILE_COPY: // 26 文件复制
                url = './content/filecopy/file_copy_job_details.php';
                break;
        }
        return url;
    };
    let initListeners = () => {
        // 滑轮滚动阶段
        $('.section-wrapper').on('wheel', function (e) {
            let el = document.querySelector('.section-wrapper')
            e.preventDefault();
            // 根据滚动的方向改变 scrollLeft 的值
            // 正值向左滚动，负值向右滚动
            el.scrollLeft += e.originalEvent.deltaY;
        });
        $('.start').on('click', () => {
            $('.event-startTime').remove();
            $('.event-endTime').remove();
            let plan_uuid = $('#plan_uuid').val();
            pAjaxRequest(deepCloneObject({ plan_uuid: plan_uuid, op_mode: 2 }), `/api/v1/orchestration/operate`, "GET", (res) => { operateResponseList(res) }, true);
        });
        $('.stop').on('click', () => {
            let plan_uuid = $('#plan_uuid').val();
            let uuid = []
            uuid.push(plan_uuid);
            pAjaxRequest(deepCloneObject({ plan_uuid: uuid, op_mode: 3 }), `/api/v1/orchestration/operate`, "GET", (res) => { operateResponseList(res) }, true);
        });
    };
    //根据任务状态设置按钮权限
    let setBtnStatus = (data) => {
        let planState = data.plan_state;
        let planId = $('#plan_uuid').val();
        switch (planState) {
            case CONF.ORCHESTRATION_PLAN_STATUS.WAITING: // 等待状态 禁用启动策略，修改，删除
                addForbidButton(planId, 'start', true);
                addForbidButton(planId, 'stop', true);
                break;
            case CONF.ORCHESTRATION_PLAN_STATUS.RUNNING: // 运行状态 禁用启动策略，启动，修改，删除
                addForbidButton(planId, 'start', false);
                addForbidButton(planId, 'stop', true);
                break;
            case CONF.ORCHESTRATION_PLAN_STATUS.STOPPED: // 停止状态 禁用停止
                addForbidButton(planId, 'stop', false);
                addForbidButton(planId, 'start', true);
                break;
            case CONF.ORCHESTRATION_PLAN_STATUS.ERROR: // 错误状态 禁用启动策略、修改、删除、停止
                addForbidButton(planId, 'start', true);
                break;
        }
    }

    //添加禁止点击的按钮样式
    let addForbidButton = (uuid, option, available) => {
        if (!available) {
            // 不能点击，降低透明度
            $('#' + uuid + ' .' + option + ' .btn').prop('disabled', true);
        }
    }
    let initHistory = () => {
        let planId = $('#plan_uuid').val();
        // 判断分页展开状态
        if ($('#history .page-list .dropdown').hasClass('open')) {
            paginationOpenFlag = true;
        } else {
            paginationOpenFlag = false;
        }
        let options = {
            vin_url: "/api/v1/orchestration/history",
            vin_method: "GET",
            vin_params: function () {
                return {
                    plan_uuid: planId,
                };
            },
            tableArea: '#history',
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            sortName: 'time_point',
            sortOrder: 'desc',
            resizable: true, //可变宽度
            toolbarId: '#historyToolbar',
            buttonsToolbar: '#historyToolbar .vin_btnToolbar',
            sortName: 'end_time',
            sortOrder: 'desc',
            changeHeightBtn: true, //改变高度按钮
            onRefresh: () => {
                $("#historyTable").bootstrapTable('hideLoading');
            },
            PostBody: () => {
                // 展开分页选择
                if (paginationOpenFlag) {
                    $('#history .page-list .dropdown').addClass('open');
                }
            },
            columns: [
                {
                    field: 'plan_nickname',
                    title: LANG.UI_JOB_ORCHESTRATION_PLAN_NAME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'task_number',
                    title: LANG.UI_JOB_ORCHESTRATION_TASK_NUM,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'start_time',
                    title: LANG.UI_PUBLIC_START_TIME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'end_time',
                    title: LANG.UI_PUBLIC_END_TIME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'error_code',
                    title: LANG.UI_JOB_TASK_ORCHESTRATION_LABEL_RESULT,
                    sortable: false,
                    align: 'center',
                },

            ]
        }

        let init = function () {
            if (!initGridFlag) {
                $('#historyTable').baseTableConfig().init(options);
                initGridFlag = true;
            } else {
                $('#historyTable').bootstrapTable('refresh');
            }
            // 改变表格高度
            $('#historyToolbar .change_height').on('click', changeHeight);
        }

        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            e.target // newly activated tab
            e.relatedTarget // previous active tab
            if ("#history" == e.target.hash) {
                init();
            }
        })
    };

    // 改变表格高度
    var changeHeight = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#historyTable>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            })
            $('#historyToolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#historyTable>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#historyToolbar .change_height i').removeClass('icon-auto-height2');
        }
    }
    return {
        init: function () {
            initData();
            initHistory();
            initListeners();
        }
    };
}();
jQuery(document).ready(function () {
    OrchestrationDetails.init();
});