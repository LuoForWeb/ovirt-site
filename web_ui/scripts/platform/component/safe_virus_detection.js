(function ($) {
	/**
	 * // 安全配置
	 * {
	 * 	"safe_config_strategy": {
	 * 		"worm_flag": 1,          // 是否启用worm保护
	 * 		"worm_protection_time": 99,  // worm保护天数
	 * 		"virus_scan_flag": 1,      // 是否启用病毒查杀
	 * 		"virus_scan_config_list":[
	 * 		{
	 * 			"strategy_type": 1,  // 策略类型： 1 - 验证策略(备份或验证任务)， 2 - 未扫描策略， 3 - 健康策略， 4 - 已感染策略
	 * 			"recover_policy": 1,     // 恢复策略： 1 - 直接恢复，2 - 杀除后恢复， 3 - 执行扫描， 4 - 直接接管
	 * 			"interrupt_policy": 1,    // 扫描中断策略： 1 - 扫出病毒后停止(验证任务停止检测，恢复任务停止恢复, 接管任务停止接管)， 2 - 扫出病毒后恢复到无网络环境， 3 - 扫出病毒后杀除并恢复，4 - 扫出病毒后接管到无网络环境， 5 - 杀除后接管
	 * 			"scan_strategy": 1,      // 扫描策略： 1 - 仅扫描（默认）， 2 - 扫描并删除可疑文件， 3 - 扫描并查杀可疑文件，失败则删除， 4 - 扫描并查杀可疑文件，失败则跳过(interrupt_policy=3/5时置位)
	 * 			"all_timepoints_flag": 0,   // 是否扫描任务关联的所有未扫描备份点
	 * 		    "max_num_of_one_detection": 0,		// 新增，单个备份对象最大检测数量
	 * 			"skip_application_group_flag": 0  // 是否跳过应用组
	 * 			"_scan_entire_system_flag":1,		// 默认1-扫描全盘，此时specific_scan_target_map为空，2-扫描特定目标
	 * 			"virus_thread_num":2				// 新增，扫描线程
	 * 			"specific_scan_target_map":{
	 *             "1": ["path1", "path2", "xxx"],		// 包含路径
	 *             "2": ["path3", "path4", "xxx"],		// 排除路径
	 *             "3": [".txt", ".doc", "xxx"],			// 包含后缀名
	 *             "4": [".tmp", ".log", "xxx"]			// 排除后缀名
	 * 			}
	 * 		}],
	 * 		"integrity_check_flag": 1,    // 是否启用完整性检查
	 * 		"integrity_check_config": {    // 没有完整性检查策略则此字段值为null
	 * 			"check_strategy": 0,    // 完整性检查策略： 0 -每周（默认） 1 - 每天， 2 - 每次
	 * 			"full_error_policy": 0,   // 备份完备点异常策略：0 – 停止， 1 – 重做完备（默认）
	 * 			"inc_error_policy": 0,   // 增备点异常策略：0 – 停止 ,1 – 重做完备, 2 - 重做增备（默认）
	 * 			"recovery_error_policy": 0   // 恢复异常策略：0 – 停止(默认), 1 – 继续正常恢复, 2 – 恢复到无网络环境
	 * 		}
	 * 	}
	 * }
	 */
	//此界面有三个方法，备份时候的病毒检测，恢复时候的病毒检测，病毒检测的应用
	$.fn.virusDefine = {
		scan_entire_system_flag: {
			full_disk: 1, // 全盘扫描
			specify_path: 2, // 指定路径扫描
		},
		specific_scan_target_type: {
			include_path: 1, // 包含路径
			exclude_path: 2, // 包含后缀
			include_suffix: 3, // 排除路径
			exclude_suffix: 4, // 排除后缀
		},
		timepoint_process_mode: {
			immediate_recovery: 1,  // 直接恢复
			remove_virus_recovery: 2,  // 杀毒后恢复
			execute_scan: 3,  // 执行扫描/重新扫描
		},
		interrupt_policy: {
			interrupt_recovery: 1,  // 扫描异常后中断恢复
			recovery_no_net: 2,  // 扫描异常后恢复到无网络环境
			remove_virus_recovery: 3,  // 扫描异常后杀毒后恢复
			takeover_no_net: 4,  // 扫出病毒后接管到无网络环境
			remove_virus_takeover: 5,  // 杀除后接管
		},
		strategy_type: {
			backup_or_apply: 1,  // 备份或验证
			no_scan: 2,  // 未扫描
			healthy: 3, // 健康
			reflected: 4, // 已感染
		},
		scan_strategy_type: {
			only_scan: 1,  // 仅扫描
			scan_delete: 2,  // 扫描并删除可疑文件
			scan_kill_fail_delete: 3,  // 扫描并查杀可疑文件，失败则删除
			scan_kill_fail_skip: 4,  // 扫描并查杀可疑文件，失败则跳过
		},
		virus_scan_status: {
			no_scan: 0, // 未扫描
			scanning: 1, // 扫描中
			safe: 2, // 健康
			infected: 3, // 已感染
			infected_but_unfinished: 4, // 已感染但未完成扫描
		}
	};

	/**
	 * 获取病毒检测策略描述
	 * @param virusStrategy
	 */
	const getVirusStrategyDes = virusStrategy => {
		let des = ``;
		// 扫描引擎
		des += `${LANG.UI_SAFE_VIRUS_ENGINE}: ${virusStrategy.virus_lib_type_name}<br>`;
		// 扫描线程
		des += `${LANG.UI_SAFE_SCAN_THREAD}: ${virusStrategy.virus_thread_num}<br>`;
		// 扫描对象
		if (parseInt(virusStrategy.scan_entire_system_flag) === $.fn.virusDefine.scan_entire_system_flag.full_disk) {
			des += `${LANG.UI_SAFE_SCAN_ENTIRE}: ${LANG.UI_SAFE_SCAN_ENTIRE_FULL_DISK}<br>`;
		} else {
			des += `${LANG.UI_SAFE_SCAN_ENTIRE}: ${LANG.UI_SAFE_SCAN_ENTIRE_SPECIFY_PATH}<br>`;
		}
		// 扫描/排除配置
		if (
			parseInt(virusStrategy.scan_entire_system_flag) === $.fn.virusDefine.scan_entire_system_flag.specify_path &&
			typeof virusStrategy.specific_scan_target_map === 'object' &&
			Object.keys(virusStrategy.specific_scan_target_map).length
		) {
			let includePath = [];
			let includeSuffix = [];
			let excludePath = [];
			let excludeSuffix = [];
			for (let targetType in virusStrategy.specific_scan_target_map) {
				const targetItems = virusStrategy.specific_scan_target_map[targetType];
				targetType = parseInt(targetType);
				for (const targetItem of targetItems) {
					switch (targetType) {
						case $.fn.virusDefine.specific_scan_target_type.include_path:
							includePath.push(`${targetItem}`);
							break;
						case $.fn.virusDefine.specific_scan_target_type.exclude_path:
							excludePath.push(targetItem);
							break;
						case $.fn.virusDefine.specific_scan_target_type.include_suffix:
							includeSuffix.push(targetItem);
							break;
						case $.fn.virusDefine.specific_scan_target_type.exclude_suffix:
							excludeSuffix.push(targetItem);
							break;
					}
				}
			}

			if (includePath.length) {
				des += `${LANG.UI_SAFE_INCLUDE_PATH}: ` + includePath.map(v => v).join('; ') + '<br>';
			}
			if (includeSuffix.length) {
				des += `${LANG.UI_SAFE_INCLUDE_SUFFIX}: ` + includeSuffix.map(v => '*.' + v).join('; ') + '<br>';
			}
			if (excludePath.length) {
				des += `${LANG.UI_SAFE_EXCLUDE_PATH}: ` + excludePath.map(v => v).join('; ') + '<br>';
			}
			if (excludeSuffix.length) {
				des += `${LANG.UI_SAFE_EXCLUDE_SUFFIX}: ` + excludeSuffix.map(v => '*.' + v).join('; ') + '<br>';
			}
		}
		return des;
	};

	/**
	 * 获取用于备份的病毒检测描述
	 * @param virusConfig
	 */
	const getBackupVirusConfigDes = virusConfig => {
		let des = ``;
		if (virusConfig.virus_scan_flag) {  // 开启病毒检测
			des = `${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK}: ${LANG.UI_PUBLIC_ON}<br>`;
			if (!Array.isArray(virusConfig.virus_scan_config_list) || !virusConfig.virus_scan_config_list.length) {
				des += `${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK_EMPTY}<br>`;
			} else {
				let virusScanConfigInfo = virusConfig.virus_scan_config_list[0];
				// 当检测到首个恶意软件或病毒后停止检测
				if (virusScanConfigInfo.interrupt_policy) {
					des += `${LANG.UI_SAFE_SEARCH_FIRST_VIRUS_STOP}<br>`;
				}
				// 检查所有未检测备份点
				if (virusScanConfigInfo.all_timepoints_flag) {
					des += `${LANG.UI_SAFE_SEARCH_ALL_BACKUP}<br>`;
					des += `${LANG.UI_SAFE_MAX_NUM_OF_ONE_DETECTION}: ${virusScanConfigInfo.max_num_of_one_detection}<br>`;
				}
				des += getVirusStrategyDes(virusScanConfigInfo);
			}
		} else {
			des = `${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK}: ${LANG.UI_PUBLIC_OFF}<br>`;
		}
		return des;
	};

	/**
	 * 获取用于恢复的病毒检测描述
	 * @param virusConfig
	 */
	const getRecoveryVirusConfigDes = virusConfig => {
		let des = ``;
		if (!Array.isArray(virusConfig.virus_scan_config_list) || !virusConfig.virus_scan_config_list.length) {
			des += `${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK_EMPTY}<br>`;
		} else {
			des += `<span>${LANG.UI_MACHINE_OS_VIRUS_SCAN_STRATEGY}: <br>`;
			for (const virusScanConfigInfo of virusConfig.virus_scan_config_list) {
				// 恢复策略类型【未扫描 健康 已感染】
				let strategyType = parseInt(virusScanConfigInfo.strategy_type);
				// 备份点处理方式
				let recoverPolicy = parseInt(virusScanConfigInfo.recover_policy);
				// 扫描策略
				let interruptPolicy = parseInt(virusScanConfigInfo.interrupt_policy);
				switch (strategyType) {
					case $.fn.virusDefine.strategy_type.no_scan:  // 未扫描
						des += `<span style="color: #f19f00">${LANG.UI_SAFE_BACKUP_TIMEPOINT_VIRUS_STATUS}: ${LANG.UI_SAFE_NO_SCAN}</span><br>`;
						// 备份点处理方式
						if (recoverPolicy === $.fn.virusDefine.timepoint_process_mode.execute_scan) {  // 执行扫描
							des += `<span style="display: block; word-break: break-all" class="break-word_en">${LANG.UI_SAFE_TIMEPOINT_PROCESS_MODE}: ${LANG.UI_SAFE_TAKE_SCAN}<br>`;
							// 扫描策略
							if (interruptPolicy === $.fn.virusDefine.interrupt_policy.interrupt_recovery) {  // 扫描异常后中断恢复
								des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_SCAN_ABNORMAL_STOP}<br>`;
							} else if (interruptPolicy === $.fn.virusDefine.interrupt_policy.recovery_no_net) {  // 扫描异常后恢复到无网络环境
								des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_SCAN_ABNORMAL_RECOVER_NO_WEB}<br>`;
							} else {  // 扫描异常后杀毒后恢复
								des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_SCAN_ABNORMAL_VIRUS_STOP}<br>`;
							}
							des += getVirusStrategyDes(virusScanConfigInfo);
						} else {  // 直接恢复
							des += `<span style="display: block; word-break: break-all" class="break-word_en">${LANG.UI_SAFE_TIMEPOINT_PROCESS_MODE}: ${LANG.UI_RECOVERY_ZHIJIE}<br>`;
						}
						des += `</span>`;
						break;
					case $.fn.virusDefine.strategy_type.healthy:  // 健康
						des += `<span style="color: #0fbf98">${LANG.UI_SAFE_BACKUP_TIMEPOINT_VIRUS_STATUS}: ${LANG.UI_SAFE_HEALTHY}</span><br>`;
						// 备份点处理方式
						if (recoverPolicy === $.fn.virusDefine.timepoint_process_mode.execute_scan) {  // 重新扫描
							des += `<span style="display: block; word-break: break-all" class="break-word_en">${LANG.UI_SAFE_TIMEPOINT_PROCESS_MODE}: ${LANG.UI_RECOVERY_RESCAN}<br>`;
							// 扫描策略
							if (interruptPolicy === $.fn.virusDefine.interrupt_policy.interrupt_recovery) {  // 检测到病毒后，中断恢复
								des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_SCAN_VIRUS_STOP_RECOVER}<br>`;
							} else if (interruptPolicy === $.fn.virusDefine.interrupt_policy.recovery_no_net) {  // 检测到病毒后，恢复到无网络环境
								des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_SCAN_VIRUS_RECOVER_NO_INTER}<br>`;
							} else {  // 检测到病毒后，杀毒后恢复
								des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_SCAN_VIRUS_VIRUS_RECOVER}<br>`;
							}
							des += getVirusStrategyDes(virusScanConfigInfo);
						} else {  // 直接恢复
							des += `<span style="display: block; word-break: break-all" class="break-word_en">${LANG.UI_SAFE_TIMEPOINT_PROCESS_MODE}: ${LANG.UI_RECOVERY_ZHIJIE}<br>`;
						}
						des += `</span>`;
						break;
					default:  // 已感染
						des += `<span style="color: #f1416c">${LANG.UI_SAFE_BACKUP_TIMEPOINT_VIRUS_STATUS}: ${LANG.UI_SAFE_REFLECTED}</span><br>`;
						// 备份点处理方式
						if (recoverPolicy === $.fn.virusDefine.timepoint_process_mode.execute_scan) {  // 重新扫描
							des += `<span style="display: block; word-break: break-all" class="break-word_en">${LANG.UI_SAFE_TIMEPOINT_PROCESS_MODE}: ${LANG.UI_RECOVERY_RESCAN}<br>`;
							// 扫描策略
							if (interruptPolicy === $.fn.virusDefine.interrupt_policy.interrupt_recovery) {  // 检测到病毒后，中断恢复
								des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_SCAN_VIRUS_STOP_RECOVER}<br>`;
							} else if (interruptPolicy === $.fn.virusDefine.interrupt_policy.recovery_no_net) {  // 检测到病毒后，恢复到无网络环境
								des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_SCAN_VIRUS_RECOVER_NO_INTER}<br>`;
							} else {  // 检测到病毒后，杀毒后恢复
								des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_SCAN_VIRUS_VIRUS_RECOVER}<br>`;
							}
							des += getVirusStrategyDes(virusScanConfigInfo);
						} else if (recoverPolicy === $.fn.virusDefine.timepoint_process_mode.remove_virus_recovery) {  // 杀毒后恢复
							des += `<span style="display: block; word-break: break-all" class="break-word_en">${LANG.UI_SAFE_TIMEPOINT_PROCESS_MODE}: ${LANG.UI_RECOVERY_VIRUS_COVERY}<br>`;
						} else {  // 直接恢复
							des += `<span style="display: block; word-break: break-all" class="break-word_en">${LANG.UI_SAFE_TIMEPOINT_PROCESS_MODE}: ${LANG.UI_RECOVERY_ZHIJIE}<br>`;
						}
						des += `</span>`;
						break;
				}
			}
		}
		return des;
	};

	/**
	 * 获取用于验证的病毒检测描述
	 * @param virusConfig
	 */
	const getApplyVirusConfigDes = virusConfig => {
		let des = ``;
		if (virusConfig.virus_scan_flag) {  // 开启病毒检测
			des = `${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK}: ${LANG.UI_PUBLIC_ON}<br>`;
			if (!Array.isArray(virusConfig.virus_scan_config_list) || !virusConfig.virus_scan_config_list.length) {
				des += `${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK_EMPTY}<br>`;
			} else {
				let virusScanConfigInfo = virusConfig.virus_scan_config_list[0];
				// 当检测到首个恶意软件或病毒后停止检测
				if (virusScanConfigInfo.interrupt_policy) {
					des += `${LANG.UI_SAFE_SEARCH_FIRST_VIRUS_STOP}<br>`;
				}
				// 跳过对应用组关联备份点的检测
				if (virusScanConfigInfo.skip_application_group_flag) {
					des += `${LANG.UI_SAFE_SKIP_GROUP_BACKUP_CHECK}<br>`;
				}
				des += getVirusStrategyDes(virusScanConfigInfo);
			}
		} else {
			des = `${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK}: ${LANG.UI_PUBLIC_OFF}<br>`;
		}
		return des;
	};

	/**
	 * 获取用于接管的病毒检测描述
	 * @param virusConfig
	 */
	const getTakeoverVirusConfigDes = virusConfig => {
		let des = ``;
		if (!Array.isArray(virusConfig.virus_scan_config_list) || !virusConfig.virus_scan_config_list.length) {
			des += `${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK_EMPTY}<br>`;
		} else {
			let virusScanConfigInfo = virusConfig.virus_scan_config_list[0];
			// 接管处理方式
			if (parseInt(virusScanConfigInfo.recover_policy) === CONF.RECOVER_POLICY.SCAN) {  // 执行扫描
				des += `${LANG.UI_SAFE_TAKEOVER_SETTING}: ${LANG.UI_SAFE_TAKE_SCAN}<br>`;
				// 扫描策略
				let interruptPolicy = parseInt(virusScanConfigInfo.interrupt_policy);
				switch (interruptPolicy) {
					case CONF.INTERRUPT.STOP:
						des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_VIRUS_SCAN_ABNORNAL_STOP}<br>`;
						break;
					case CONF.INTERRUPT.KILL_TAKE:
						des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_VIRUS_SCAN_ABNORNAL_VIRUS_TAKEOVER}<br>`;
						break;
					default:
						des += `${LANG.UI_SAFE_SCAN_STRATEGY}: ${LANG.UI_SAFE_VIRUS_SCAN_ABNORNAL_TAKEOVER_NO_INTER}<br>`;
						break;
				}
				des += getVirusStrategyDes(virusScanConfigInfo);
			} else {  // 直接接管
				des += `${LANG.UI_SAFE_TAKEOVER_SETTING}: ${LANG.UI_SAFE_DECTION_TAKEOVER}<br>`;
			}
		}
		return des;
	};

	/**
	 * 获取病毒检测的描述
	 * @param virusConfig
	 * @param useMode string 使用模式，backup-备份，recovery-恢复，apply-验证，takeover接管
	 */
	$.fn.getVirusConfigDes = (virusConfig, useMode = 'backup') => {
		switch (useMode) {
			case 'backup':
				return getBackupVirusConfigDes(virusConfig);
			case 'recovery':
				return getRecoveryVirusConfigDes(virusConfig);
			case 'apply':
				return getApplyVirusConfigDes(virusConfig);
			case 'takeover':
				return getTakeoverVirusConfigDes(virusConfig);
			default:
				return '';
		}
	};

	//////////////////// 开始-病毒扫描策略 ////////////////////

	/**
	 * 获取扫描/排除配置的排除对象
	 */
	const getIncludeExcludeConfigExcludeEntire = id => {
		return `
		<div class="tab-pane" id="${id}_exclude-entire" role="tabpanel">
			<div class="form-group virus-include-exclude-item">
				<div class="title">${LANG.UI_SAFE_INCLUDE_EXCLUDE_PATH}</div>
				<div class="path" id="${id}_excludePath"></div>
				<button type="button" class="btn btn-light-primary add-btn" id="${id}_addExcludePathBtn">${LANG.UI_SAFE_INCLUDE_EXCLUDE_ADD_PATH}</button>
			</div>
			<div class="form-group virus-include-exclude-item">
				<div class="title">${LANG.UI_SAFE_INCLUDE_EXCLUDE_SUFFIX}</div>
				<div class="path">
					<input id="${id}_excludeSuffix" type="text" maxlength="128" class="form-control" />
				</div>
				<button type="button" class="btn btn-light-primary add-btn" id="${id}_addExcludeSuffixBtn">${LANG.UI_SAFE_INCLUDE_EXCLUDE_ADD_SUFFIX}</button>
			</div>
		</div>
		`;
	};

	/**
	 * 获取扫描/排除配置的扫描对象
	 */
	const getIncludeExcludeConfigIncludeEntire = id => {
		return `
		<div class="tab-pane active" id="${id}_include-entire" role="tabpanel">
			<div class="form-group virus-include-exclude-item">
				<div class="title">${LANG.UI_SAFE_INCLUDE_EXCLUDE_PATH}</div>
				<div class="path" id="${id}_includePath"></div>
				<button type="button" class="btn btn-light-primary add-btn" id="${id}_addIncludePathBtn">${LANG.UI_SAFE_INCLUDE_EXCLUDE_ADD_PATH}</button>
			</div>
			<div class="form-group virus-include-exclude-item">
				<div class="title">${LANG.UI_SAFE_INCLUDE_EXCLUDE_SUFFIX}</div>
				<div class="path">
					<input id="${id}_includeSuffix" type="text" maxlength="20" class="form-control" />
				</div>
				<button type="button" class="btn btn-light-primary add-btn" id="${id}_addIncludeSuffixBtn">${LANG.UI_SAFE_INCLUDE_EXCLUDE_ADD_SUFFIX}</button>
			</div>
		</div>
		`;
	};

	/**
	 * 获取扫描/排除配置
	 */
	const getIncludeExcludeConfig = (id, leftWidth = 2) => {
		return `
		<div class="form-group virusScanSpecifyItem">
			<div class="col-md-offset-${leftWidth} col-md-${12 - leftWidth}">
				<div class="accordion virusIncludeExcludeStrategy">
					<div class="panel panel-default strategy-panel">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top"
									data-toggle="collapse" data-parent=".virusIncludeExcludeStrategy" href="#${id}_virus-include-exclude-strategy" aria-expanded="true">
									<i class="levelchild viconfont vicon-saomiao font-green-seagreen"></i>
									<span class="font-green-seagreen">${LANG.UI_SAFE_INCLUDE_EXCLUDE_TITLE}</span>
								</a>
							</h4>
						</div>
						<div id="${id}_virus-include-exclude-strategy" class="panel-collapse collapse in ">
							<div class="panel-body virusIncludeExcludeCollapse">
								<div class="col-md-3 virusIncludeExcludeCollapse_nav-left">
									<ul class="nav flex-column" role="tablist" aria-orientation="vertical">
										<li class="nav-item active" style="margin-bottom: 16px;">
											<a class="nav-link" data-toggle="tab" href="#${id}_include-entire" role="tab" aria-selected="true">${LANG.UI_SAFE_INCLUDE_ENTIRE}</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-toggle="tab" href="#${id}_exclude-entire" role="tab" aria-selected="false">${LANG.UI_SAFE_EXCLUDE_ENTIRE}</a>
										</li>
									</ul>
								</div>
								<div class="tab-content col-md-9 virusIncludeExcludeCollapse_nav-right">
									<!-- 扫描对象 -->
									${getIncludeExcludeConfigIncludeEntire(id)}

									<!-- 排除对象 -->
									${getIncludeExcludeConfigExcludeEntire(id)}
								</div>
								<!-- 提示信息 -->
								<div class="alert alert-block alert-info fade in col-md-12" style="display: block!important; margin: 16px 0 0 0;">
									<button type="button" class="close" data-dismiss="alert"></button>
									<ul class="alert-ul">
										<strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
										<li style="line-height: 1.42857143">${LANG.UI_SAFE_INCLUDE_EXCLUDE_TIPS}</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group virusScanSpecifyItem" id="${id}_include-result__wrapper">
			<label class="col-md-${leftWidth} control-label" for="${id}_include-result">${LANG.UI_SAFE_INCLUDE_TARGET}</label>
			<div class="col-md-${12 - leftWidth}">
				<ul class="virus-include-exclude-result" id="${id}_include-result"></ul>
			</div>
		</div>
		<div class="form-group virusScanSpecifyItem" id="${id}_exclude-result__wrapper">
			<label class="col-md-${leftWidth} control-label" for="${id}_exclude-result">${LANG.UI_SAFE_EXCLUDE_TARGET}</label>
			<div class="col-md-${12 - leftWidth}">
				<ul class="virus-include-exclude-result" id="${id}_exclude-result"></ul>
			</div>
		</div>
		`;
	};

	/**
	 * 获取扫描引擎
	 */
	const getVirusScanStrategyScanEngine = (id, leftWidth = 2) => {
		return `
		<div class="form-group virusScanEngineDiv">
			<label class="col-md-${leftWidth} control-label pt7" for="${id}_scan-engine">${LANG.UI_SAFE_VIRUS_ENGINE}</label>
			<div class="col-md-7">
				<select id="${id}_scan-engine" class="form-control"></select>
			</div>
		</div>
		`;
	};

	/**
	 * 获取扫描线程
	 */
	const getVirusScanStrategyScanThread = (id, leftWidth = 2) => {
		return `
		<div class="form-group">
			<label class="col-md-${leftWidth} control-label pt7" for="${id}_scan-thread">${LANG.UI_SAFE_SCAN_THREAD}</label>
			<div class="col-md-7">
				<div id="${id}_scan-thread-spinner">
					<div class="input-group spinner-group">
						<input id="${id}_scan-thread" class="spinner-input form-control">
						<div class="spinner-buttons input-group-btn spinner-group-btn">
							<button type="button" class="btn spinner-up default">
								<i class="fa fa-angle-up"></i>
							</button>
							<button type="button" class="btn spinner-down default">
								<i class="fa fa-angle-down"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		`;
	};

	/**
	 * 获取扫描对象
	 */
	const getVirusScanStrategyScanEntire = (id, leftWidth = 2) => {
		return `
		<div class="form-group">
			<label class="col-md-${leftWidth} pt7 control-label" for="${id}_scan-entire">${LANG.UI_SAFE_SCAN_ENTIRE}</label>
			<div class="col-md-7">
				<select id="${id}_scan-entire" class="form-control">
					<option value="${$.fn.virusDefine.scan_entire_system_flag.full_disk}">${LANG.UI_SAFE_SCAN_ENTIRE_FULL_DISK}</option>
					<option value="${$.fn.virusDefine.scan_entire_system_flag.specify_path}">${LANG.UI_SAFE_SCAN_ENTIRE_SPECIFY_PATH}</option>
				</select>
			</div>
		</div>
		`;
	};

	/**
	 * 获取病毒扫描策略
	 */
	const getVirusScanStrategy = id => {
		return `
		<div class="accordion virusStrategy">
			<div class="panel panel-default strategy-panel">
				<div class="panel-heading">
				<h4 class="panel-title">
					<a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top"
						data-toggle="collapse" data-parent=".virusStrategy" href="#${id}_virus-scan-strategy" aria-expanded="true">
						<i class="levelchild viconfont vicon-xiaoyancelve font-green-seagreen"></i>
						<span class="font-green-seagreen">${LANG.UI_SAFE_VIRUS_SCAN_STRATEGY}</span>
					</a>
				</h4>
				</div>
				<div id="${id}_virus-scan-strategy" class="virus-scan-strategy__collapse panel-collapse collapse in ">
					<div class="panel-body">
						<!-- 扫描引擎 -->
						${getVirusScanStrategyScanEngine(id)}
						<!-- 扫描线程 -->
						${getVirusScanStrategyScanThread(id)}
						<!-- 扫描对象 -->
						${getVirusScanStrategyScanEntire(id)}
						<!-- 扫描/排除配置 -->
						${getIncludeExcludeConfig(id)}
					</div>
				</div>
			</div>
		</div>
		`;
	};

	/**
	 * 标准化扫描策略
	 */
	const formalizationScanStrategy = (scanStrategy) => {
		if (typeof scanStrategy === 'undefined' || !scanStrategy) {
			scanStrategy = {};
		}
		return {
			virus_lib_type: typeof scanStrategy.virus_lib_type !== 'undefined' ? parseInt(scanStrategy.virus_lib_type) : 0,
			virus_thread_num: typeof scanStrategy.virus_thread_num !== 'undefined' ? parseInt(scanStrategy.virus_thread_num) : 2,
			scan_entire_system_flag: typeof scanStrategy.scan_entire_system_flag !== 'undefined' ? scanStrategy.scan_entire_system_flag : $.fn.virusDefine.scan_entire_system_flag.full_disk,
			specific_scan_target_map: typeof scanStrategy.specific_scan_target_map !== 'undefined' ? scanStrategy.specific_scan_target_map : {},
			agent_uuid: typeof scanStrategy.agent_uuid !== 'undefined' ? scanStrategy.agent_uuid : '',
			show_scan_engine_flag: true,  // 是否显示病毒扫描引擎
			virus_scan_status: typeof scanStrategy.virus_scan_status !== 'undefined' ? scanStrategy.virus_scan_status : $.fn.virusDefine.virus_scan_status.safe,  // 病毒扫描状态
			os_type: typeof scanStrategy.os_type !== 'undefined' ? scanStrategy.os_type : undefined,  // 操作系统类型
			max_num_of_one_detection: typeof scanStrategy.max_num_of_one_detection !== 'undefined'? parseInt(scanStrategy.max_num_of_one_detection) : 1,
			exclude_mount_point_list: typeof scanStrategy.exclude_mount_point_list !== 'undefined' ? scanStrategy.exclude_mount_point_list : [],  // 排除挂载点列表
		};
	};

	/**
	 * 初始化病毒引擎
	 */
	const initVirusEngine = (id, scanStrategy) => {
		// 扫描引擎
		if (scanStrategy.show_scan_engine_flag) {
			$(`#${id}.virusScanEngineDiv`).show();
			Metronic.blockUI({ target: `#${id}`, animate: true });
			pAjaxRequest({ offset: 0, limit: 200 }, `/api/v1/virus`, `GET`, res => {
				Metronic.unblockUI(`#${id}`);
				if (!res.success) {
					return;
				}
				let options = ``;
				for (const row of res.data.rows) {
					let selected = '';
					if (parseInt(row.apply_status) === 1) {
						selected = 'selected';
					}
					options += `<option value="${row.type}" ${selected}>${row.name}</option>`;
				}
				$(`#${id}_scan-engine`).html(options);
			}, false);
		} else {
			$(`#${id} .virusScanEngineDiv`).hide();
		}
	};

	/**
	 * 设置结果是否显示
	 */
	const setIncludeExcludeResultVisible = id => {
		if ($(`#${id}_scan-entire`).val() == $.fn.virusDefine.scan_entire_system_flag.full_disk) {
			$(`#${id}_include-result__wrapper`).hide();
			$(`#${id}_exclude-result__wrapper`).hide();
		} else {
			if ($(`#${id}_include-result`).children().length) {
				$(`#${id}_include-result__wrapper`).show();
			} else {
				$(`#${id}_include-result__wrapper`).hide();
			}
			if ($(`#${id}_exclude-result`).children().length) {
				$(`#${id}_exclude-result__wrapper`).show();
			} else {
				$(`#${id}_exclude-result__wrapper`).hide();
			}
		}
	};

	/**
	 * 判断添加的扫描、排除是否重复
	 * @param id
	 * @param type
	 * @param value
	 * @param message
	 */
	const judgeIncludeExcludeRepeat = (id, type, value, message) => {
		let { includeExcludeResult, includeExcludeResultStr } = getIncludeExcludeResult(id);
		if (!Array.isArray(includeExcludeResult[type])) {
			return true;
		}
		if (includeExcludeResult[type].includes(value)) {
			UIToastr.showWarning(LANG.UI_SAFE_INCLUDE_EXCLUDE_TITLE, message);
			return false;
		}
		return true;
	};

	/**
	 * 初始化病毒扫描策略监听
	 */
	const initVirusScanStrategyListener = (id, scanStrategy) => {
		// 初始化扫描引擎
		initVirusEngine(id, scanStrategy);
		// 扫描线程
		$(`#${id}_scan-thread-spinner`).spinner({ value: 2, step: 1, min: 1, max: 12 });
		$(`#${id}_scan-thread`).on('change', function () {
			let value = this.value;
			if (isNaN(value) || !value) {
				$(`#${id}_scan-thread-spinner`).spinner('value', 2);
			} else {
				value = parseInt(value);
				if (value < 1) {
					$(`#${id}_scan-thread-spinner`).spinner('value', 1);
				} else if (value > 12) {
					$(`#${id}_scan-thread-spinner`).spinner('value', 12);
				}
			}
		});
		// 扫描对象
		$(`#${id}_scan-entire`).on('change', function () {
			if (parseInt(this.value) === 1) {  // 全盘扫描
				$(`#${id} .virusScanSpecifyItem`).hide();
			} else { // 扫描指定目标
				$(`#${id} .virusScanSpecifyItem`).show();
			}
			setIncludeExcludeResultVisible(id);
		});
		// 扫描路径
		let includePathSelector = new $.fn.PathTreeSelector.cls(scanStrategy.agent_uuid, { operateFlag: false });
		includePathSelector.init({
			target_id: `${id}_includePath`,
			select_mode: 3,
			select_only: false,
			os_type: scanStrategy.os_type,
			exclude_mount_point_list: scanStrategy.exclude_mount_point_list,  // 排除挂载点列表
		});
		$(`#${id}_addIncludePathBtn`).on('click', () => {
			let includePath = $.fn.PathTreeSelector.getCheckPath(`${id}_includePath`);
			if (!includePath) {
				return;
			}
			if (scanStrategy.os_type === 'Windows') {
				if (!checkPath(includePath, scanStrategy.os_type)) {
					UIToastr.showWarning(LANG.UI_SAFE_INCLUDE_PATH, LANG.UI_SAFE_INCLUDE_PATH_WINDOWS_TIPS);
					return;
				}
			} else if (scanStrategy.os_type === 'Linux') {
				if (!checkPath(includePath, scanStrategy.os_type)) {
					UIToastr.showWarning(LANG.UI_SAFE_INCLUDE_PATH, LANG.UI_SAFE_INCLUDE_PATH_LINUX_TIPS);
					return;
				}
			} else if (!checkPath(includePath, scanStrategy.os_type) && !checkPath(includePath, scanStrategy.os_type)) {
				UIToastr.showWarning(LANG.UI_SAFE_INCLUDE_PATH, LANG.UI_SAFE_INCLUDE_PATH_TIPS);
				return;
			}
			if (!judgeIncludeExcludeRepeat(id, $.fn.virusDefine.specific_scan_target_type.exclude_path, includePath, LANG.UI_SAFE_INCLUDE_PATH_ALREADY_IN_EXCLUDE_PATH)) {
				return;
			}
			addIncludeExcludeResult(id, 'include', 'path', includePath);
			$.fn.PathTreeSelector.clearPath(`${id}_includePath`);
		});
		// 扫描后缀
		$(`#${id}_addIncludeSuffixBtn`).on('click', () => {
			let includeSuffix = $(`#${id}_includeSuffix`).val();
			if (!includeSuffix) {
				return;
			}
			if (!judgeIncludeExcludeRepeat(id, $.fn.virusDefine.specific_scan_target_type.exclude_suffix, includeSuffix, LANG.UI_SAFE_INCLUDE_SUFFIX_ALREADY_IN_EXCLUDE_SUFFIX)) {
				return;
			}
			addIncludeExcludeResult(id, 'include', 'suffix', includeSuffix);
			$(`#${id}_includeSuffix`).val(``)
		});
		// 排除路径
		let excludePathSelector = new $.fn.PathTreeSelector.cls(scanStrategy.agent_uuid, { operateFlag: false });
		excludePathSelector.init({
			target_id: `${id}_excludePath`,
			select_mode: 3,
			select_only: false,
			os_type: scanStrategy.os_type,
			exclude_mount_point_list: scanStrategy.exclude_mount_point_list,  // 排除挂载点列表
		});
		$(`#${id}_addExcludePathBtn`).on('click', () => {
			let excludePath = $.fn.PathTreeSelector.getCheckPath(`${id}_excludePath`);
			if (!excludePath) {
				return;
			}
			if (scanStrategy.os_type === 'Windows') {
				if (!checkPath(excludePath, scanStrategy.os_type)) {
					UIToastr.showWarning(LANG.UI_SAFE_INCLUDE_PATH, LANG.UI_SAFE_EXCLUDE_PATH_WINDOWS_TIPS);
					return;
				}
			} else if (scanStrategy.os_type === 'Linux') {
				if (!checkPath(excludePath, scanStrategy.os_type)) {
					UIToastr.showWarning(LANG.UI_SAFE_INCLUDE_PATH, LANG.UI_SAFE_EXCLUDE_PATH_LINUX_TIPS);
					return;
				}
			} else if (!checkPath(excludePath, scanStrategy.os_type) && !checkPath(excludePath, scanStrategy.os_type)) {
				UIToastr.showWarning(LANG.UI_SAFE_INCLUDE_PATH, LANG.UI_SAFE_EXCLUDE_PATH_TIPS);
				return;
			}
			if (!judgeIncludeExcludeRepeat(id, $.fn.virusDefine.specific_scan_target_type.include_path, excludePath, LANG.UI_SAFE_EXCLUDE_PATH_ALREADY_IN_INCLUDE_PATH)) {
				return;
			}
			addIncludeExcludeResult(id, 'exclude', 'path', excludePath);
			$.fn.PathTreeSelector.clearPath(`${id}_excludePath`);
		});
		// 排除后缀
		$(`#${id}_addExcludeSuffixBtn`).on('click', () => {
			let excludeSuffix = $(`#${id}_excludeSuffix`).val();
			if (!excludeSuffix) {
				return;
			}
			if (!judgeIncludeExcludeRepeat(id, $.fn.virusDefine.specific_scan_target_type.include_suffix, excludeSuffix, LANG.UI_SAFE_EXCLUDE_SUFFIX_ALREADY_IN_INCLUDE_SUFFIX)) {
				return;
			}
			addIncludeExcludeResult(id, 'exclude', 'suffix', excludeSuffix);
			$(`#${id}_excludeSuffix`).val(``)
		});
		// 扫描目标
		$(`#${id}_include-result`).on('click', '.vicon-cuowu', function () {
			$(this).parent('.virus-include-exclude-result__item').remove();
			setIncludeExcludeResultVisible(id);
		});
		// 排除目标
		$(`#${id}_exclude-result`).on('click', '.vicon-cuowu', function () {
			$(this).parent('.virus-include-exclude-result__item').remove();
			setIncludeExcludeResultVisible(id);
		});
	};

	/**
	 * 设置病毒扫描策略默认值
	 */
	const setVirusScanStrategyDefaultValue = (id, scanStrategy) => {
		// 扫描线程
		if (scanStrategy.virus_lib_type !== 0) {
			$(`#${id}_scan-engine`).val(scanStrategy.virus_lib_type);
		}
		// 扫描线程
		$(`#${id}_scan-thread-spinner`).spinner('value', scanStrategy.virus_thread_num);
		// 扫描对象
		$(`#${id}_scan-entire`).val(scanStrategy.scan_entire_system_flag).trigger('change');
		// 扫描目标
		$(`#${id}_include-result`).html(``);
		$(`#${id}_exclude-result`).html(``);
		for (let targetType in scanStrategy.specific_scan_target_map) {
			const targetItems = scanStrategy.specific_scan_target_map[targetType];
			targetType = parseInt(targetType);
			for (const targetItem of targetItems) {
				switch (targetType) {
					case $.fn.virusDefine.specific_scan_target_type.include_path:
						if (scanStrategy.os_type === 'Windows') {
							if (!checkPath(targetItem, scanStrategy.os_type)) {
								break;
							}
						} else if (scanStrategy.os_type === 'Linux') {
							if (!checkPath(targetItem, scanStrategy.os_type)) {
								break;
							}
						} else if (!checkPath(targetItem, scanStrategy.os_type) && !checkPath(targetItem, scanStrategy.os_type)) {
							break
						}
						addIncludeExcludeResult(id, 'include', 'path', targetItem);
						break;
					case $.fn.virusDefine.specific_scan_target_type.exclude_path:
						if (scanStrategy.os_type === 'Windows') {
							if (!checkPath(targetItem, scanStrategy.os_type)) {
								break;
							}
						} else if (scanStrategy.os_type === 'Linux') {
							if (!checkPath(targetItem, scanStrategy.os_type)) {
								break;
							}
						} else if (!checkPath(targetItem, scanStrategy.os_type) && !checkPath(targetItem, scanStrategy.os_type)) {
							break
						}
						addIncludeExcludeResult(id, 'exclude', 'path', targetItem);
						break;
					case $.fn.virusDefine.specific_scan_target_type.include_suffix:
						addIncludeExcludeResult(id, 'include', 'suffix', targetItem);
						break;
					case $.fn.virusDefine.specific_scan_target_type.exclude_suffix:
						addIncludeExcludeResult(id, 'exclude', 'suffix', targetItem);
						break;
				}
			}
		}
		setIncludeExcludeResultVisible(id);
	};

	/**
	 * 添加扫描/排除结果
	 */
	const addIncludeExcludeResult = (id, type = 'include', category = 'path', result) => {
		let prefixTitle = LANG.UI_SAFE_INCLUDE_EXCLUDE_PATH;
		let showName = result;
		if (category === 'suffix') {
			prefixTitle = LANG.UI_SAFE_INCLUDE_EXCLUDE_SUFFIX;
			showName = `*.${result}`;
		}
		if (type === 'include') {
			let includeItems = $(`#${id}_include-result`).children();
			for (const includeItem of includeItems) {  // 去除重复项
				if ($(includeItem).data('type') == category && $(includeItem).data('value') == result) {
					return;
				}
			}
			$(`#${id}_include-result`).append(`
			<li class="virus-include-exclude-result__item" data-type="${category}" data-value="${result}">
				<span class="text ${category}" title="${showName}">${prefixTitle}: ${showName}</span>
				<i class="viconfont vicon-cuowu"></i>
			</li>
			`);
		} else {
			let excludeItems = $(`#${id}_exclude-result`).children();
			for (const excludeItem of excludeItems) {  // 去除重复项
				if ($(excludeItem).data('type') == category && $(excludeItem).data('value') == result) {
					return;
				}
			}
			$(`#${id}_exclude-result`).append(`
			<li class="virus-include-exclude-result__item" data-type="${category}" data-value="${result}">
				<span class="text ${category}" title="${showName}">${prefixTitle}: ${showName}</span>
				<i class="viconfont vicon-cuowu"></i>
			</li>
			`);
		}
		setIncludeExcludeResultVisible(id);
	};

	/**
	 * 获取扫描/排除结果
	 */
	const getIncludeExcludeResult = (id) => {
		let includeExcludeResult = {};
		let includeExcludeResultStr = '';
		let includePath = [];
		let includeSuffix = [];
		let excludePath = [];
		let excludeSuffix = [];
		let includeItems = $(`#${id}_include-result`).children();
		let excludeItems = $(`#${id}_exclude-result`).children();
		for (const includeItem of includeItems) {
			if ($(includeItem).data('type') === 'path') {
				includePath.push($(includeItem).data('value').toString());
			} else if ($(includeItem).data('type') === 'suffix') {
				includeSuffix.push($(includeItem).data('value').toString());
			}
		}
		for (const excludeItem of excludeItems) {
			if ($(excludeItem).data('type') === 'path') {
				excludePath.push($(excludeItem).data('value').toString());
			} else if ($(excludeItem).data('type') === 'suffix') {
				excludeSuffix.push($(excludeItem).data('value').toString());
			}
		}
		if (includePath.length) {
			includeExcludeResultStr += `${LANG.UI_SAFE_INCLUDE_PATH}: <br>` + includePath.join('<br>') + '<br>';
			includeExcludeResult[$.fn.virusDefine.specific_scan_target_type.include_path] = includePath;
		}
		if (includeSuffix.length) {
			includeExcludeResultStr += `${LANG.UI_SAFE_INCLUDE_SUFFIX}: <br>` + includeSuffix.map(v => '*.' + v).join('<br>') + '<br>';
			includeExcludeResult[$.fn.virusDefine.specific_scan_target_type.include_suffix] = includeSuffix;
		}
		if (excludePath.length) {
			includeExcludeResultStr += `${LANG.UI_SAFE_EXCLUDE_PATH}: <br>` + excludePath.join('<br>') + '<br>';
			includeExcludeResult[$.fn.virusDefine.specific_scan_target_type.exclude_path] = excludePath;
		}
		if (excludeSuffix.length) {
			includeExcludeResultStr += `${LANG.UI_SAFE_EXCLUDE_SUFFIX}: <br>` + excludeSuffix.map(v => '*.' + v).join('<br>') + '<br>';
			includeExcludeResult[$.fn.virusDefine.specific_scan_target_type.exclude_suffix] = excludeSuffix;
		}
		return {
			includeExcludeResult,
			includeExcludeResultStr,
		}
	};

	/**
	 * 获取病毒扫描策略
	 */
	const getVirusScanStrategyResult = (id) => {
		let virusScanThread = parseInt($(`#${id}_scan-thread`).val());
		let virusScanEntireSystem = parseInt($(`#${id}_scan-entire`).val());
		let virusScanEngine = parseInt($(`#${id}_scan-engine`).val());
		let virusScanEngineName = $(`#${id}_scan-engine option:selected`).text().trim();
		let specificScanTargetMap = {};
		let virusScanStrategyStr = '';
		// 扫描线程
		virusScanStrategyStr += $(`label[for="${id}_scan-thread"]`).html() + ': ' + virusScanThread + '<br>';
		// 扫描对象
		virusScanStrategyStr += $(`label[for="${id}_scan-entire"]`).html() + ':' + $(`#${id}_scan-entire option:selected`).html() + '<br>';
		if (virusScanEntireSystem == $.fn.virusDefine.scan_entire_system_flag.specify_path) {
			let { includeExcludeResult, includeExcludeResultStr } = getIncludeExcludeResult(id);
			specificScanTargetMap = includeExcludeResult;
			virusScanStrategyStr += includeExcludeResultStr;
		}
		return {
			virusScanThread,
			virusScanEntireSystem,
			virusScanEngine,  // 扫描引擎
			virusScanEngineName,
			specificScanTargetMap,
			virusScanStrategyStr,
		};
	};

	/**
	 * 校验病毒扫描策略
	 * @param id
	 * @param virusScanEntireSystem
	 * @param specificScanTargetMap
	 * @param prefixMessage
	 * @returns {boolean}
	 */
	const validateVirusScanStrategy = (id, virusScanEntireSystem, specificScanTargetMap, prefixMessage = '') => {
		/**
		 * 选择 “扫描指定目标”时：
		 * 1、扫描对象不能为空（扫描路径、扫描对象至少要有一个），提示“未配置任何扫描对象”
		 * 2、扫描路径的输入框有指时，需要提示“当前存在未添加的扫描路径”，并阻止进入下一步
		 * 3、排除路径的输入框有值时，需要提示“当前存在未添加的排除路径”，并阻止进入下一步
		 */
		if (virusScanEntireSystem === $.fn.virusDefine.scan_entire_system_flag.specify_path) {  // 扫描指定目标
			let configuredScanEntireFlag = false;
			if (
				Array.isArray(specificScanTargetMap[$.fn.virusDefine.specific_scan_target_type.include_path]) &&
				specificScanTargetMap[$.fn.virusDefine.specific_scan_target_type.include_path].length > 0
			) {  // 扫描路径
				configuredScanEntireFlag = true;
			}
			if (
				Array.isArray(specificScanTargetMap[$.fn.virusDefine.specific_scan_target_type.include_suffix]) &&
				specificScanTargetMap[$.fn.virusDefine.specific_scan_target_type.include_suffix].length > 0
			) {  // 扫描后缀
				configuredScanEntireFlag = true;
			}
			if (!configuredScanEntireFlag) {  // 1、扫描对象不能为空（扫描路径、扫描对象至少要有一个），提示“未配置任何扫描对象”
				UIToastr.showWarning(LANG.UI_SAFE_STRATEGY_VIRUS_CHECK, prefixMessage + LANG.UI_SAFE_INCLUDE_ENTIRE_EMPTY);
				return false
			}

			// 2、扫描路径的输入框有值时，需要提示“当前存在未添加的扫描路径”，并阻止进入下一步
			let includePath = $.fn.PathTreeSelector.getCheckPath(`${id}_includePath`);
			if (includePath) {
				UIToastr.showWarning(LANG.UI_SAFE_STRATEGY_VIRUS_CHECK, prefixMessage + LANG.UI_SAFE_INCLUDE_PATH_NOT_ADD_ITEM);
				return false;
			}

			// 3、排除路径的输入框有值时，需要提示“当前存在未添加的排除路径”，并阻止进入下一步
			let excludePath = $.fn.PathTreeSelector.getCheckPath(`${id}_excludePath`);
			if (excludePath) {
				UIToastr.showWarning(LANG.UI_SAFE_STRATEGY_VIRUS_CHECK, prefixMessage + LANG.UI_SAFE_EXCLUDE_PATH_NOT_ADD_ITEM);
				return false;
			}
		}
		return true;
	}

	//////////////////// 结束-病毒扫描策略 ////////////////////

	//备份
	//先在html界面定义一个<div id="wormConfig"></div>
	// 定义病毒检测插件,接受默认值，percent代表label占的百分比，flag代表的是是否开启病毒检测，interrupt_policy代表是否选中 当检测到首个恶意软件或病毒后停止检测，all_timepoints_flag代表是否选中检查所有未检测备份点，案例$('#wormConfig').virusDetectionBackup('col-md-3',true,true,true)
	//返回值是对象，第一个值是1或者0，第二个值，第三个值也是1或0，都代表是否选中，使用var a = $('#wormConfig').getVirusDetectionBackup()
	/**
	 * 使用该组件需要引入
	 * <link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css" />
	 * <script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
	 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
	 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
	 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
	 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
	 */
	/**
	 * 备份病毒检测
	 * @param percent 占比
	 * @param flag 病毒检测开关
	 * @param interrupt_policy 当检测到首个恶意软件或病毒后停止检测
	 * @param all_timepoints_flag 检查所有未检测备份点
	 * @param scanStrategy 扫描策略
	 * @returns {*}
	 */
	$.fn.virusDetectionBackup = function (percent = 'col-md-3', flag = false, interrupt_policy = true, all_timepoints_flag = false, scanStrategy = {
		virus_lib_type: 0,
		virus_thread_num: 2,
		scan_entire_system_flag: 1,
		specific_scan_target_map: {},
		agent_uuid: '',
		exclude_mount_point_list: [],
		os_type: undefined,
		max_num_of_one_detection: 1,
	}) {
		let id = $(this).attr('id');
		const MAX_MAX_NUM_OF_ONE_DETECTION = 100;
		const MIN_MAX_NUM_OF_ONE_DETECTION = 1;
		if (!flag) {
			interrupt_policy = true;
			all_timepoints_flag = false;
		}

		/**
		 * 获取页面内容
		 */
		const getContent = () => {
			return `
			<!-- 病毒扫描开关 -->
			<div class="form-group">
				<label class="control-label ${percent} form-group-label">${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK}</label>
				<div class="col-md-3 form-group-content flex-items-center">
					<input type="checkbox" id="${id}_check" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text=${LANG.UI_FILE_ENABLE} data-off-text=${LANG.UI_FILE_DISABLE}>
					<a class="popovers ml12 mb-5" data-container="body" data-trigger="hover" data-placement="right" data-content="${LANG.UI_SAFE_STRATEGY_BACKUP_POINT_CHECK_VIRUS}">
						<i class="viconfont vicon-tishi"></i>
					</a>
				</div>
			</div>
			<!-- 病毒扫描选项 -->
			<div class="form-group virusBackupSelect">
				<label class="control-label ${percent} form-group-label"></label>
				<div class="col-md-8">
					<div class="virus-item">
						<input type="checkbox" id="${id}_check1"  value="1" name="virus" />
						<label for="${id}_check1">${LANG.UI_SAFE_SEARCH_FIRST_VIRUS_STOP}</label>
					</div>
					<div class="virus-item">
						<input type="checkbox" id="${id}_check2"  value="2" name="virus" />
						<label for="${id}_check2">${LANG.UI_SAFE_SEARCH_ALL_BACKUP}</label>
						<!-- 单个备份对象最大检测数量 -->
						<div class="maxNumOfOneDetectionNumDiv">
							<div class="labelWrapper">${LANG.UI_SAFE_MAX_NUM_OF_ONE_DETECTION}</div>
							<div class="spinnerWrapper">
								<div id="${id}_maxNumOfOneDetectionSpinner">
									<div class="input-group spinner-group">
										<input id="${id}_maxNumOfOneDetection" onkeyup="value=value.replace(/[^\\d]/g,'')" class="spinner-input form-control">
										<div class="spinner-buttons input-group-btn spinner-group-btn">
											<button type="button" class="btn spinner-up default">
												<i class="fa fa-angle-up"></i>
											</button>
											<button type="button" class="btn spinner-down default">
												<i class="fa fa-angle-down"></i>
											</button>
										</div>
									</div>
								</div>
<!--								<div class="help-block">${LANG.UI_SAFE_MAX_NUM_OF_ONE_DETECTION}</div>-->
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- 病毒扫描策略 -->
			<div class="form-group virusBackupSelect">
				<label class="control-label form-group-label ${percent}"></label>
				<div class="col-md-8">${getVirusScanStrategy(id)}</div>
			</div>
			`;
		};

		/**
		 * 单个备份对象最大检测数量变化了
		 */
		const maxNumOfOneDetectionChange = () => {
			let $maxNumOfOneDetection = $(`#${id}_maxNumOfOneDetection`);
			let value = parseInt($maxNumOfOneDetection.val());
			if (!$maxNumOfOneDetection.val() || value < MIN_MAX_NUM_OF_ONE_DETECTION) {
				value = MIN_MAX_NUM_OF_ONE_DETECTION;
			} else if (value > MAX_MAX_NUM_OF_ONE_DETECTION) {
				value = MAX_MAX_NUM_OF_ONE_DETECTION;
			}
			$(`#${id}_maxNumOfOneDetectionSpinner`).spinner('value', value);
		};

		/**
		 * 初始化事件监听器
		 */
		const initEventListener = () => {
			// 病毒检测开关
			$(`#${id}_check`).on('switchChange.bootstrapSwitch', function () {
				if (this.checked) {
					$(`#${id} .virusBackupSelect`).show();
				} else {
					$(`#${id} .virusBackupSelect`).hide();
				}
				$(`#${id}_check2`).trigger('change');
			});
			$(`#${id}_check2`).on('change', function () {
				if (this.checked) {
					$('.maxNumOfOneDetectionNumDiv').show();
				} else {
					$('.maxNumOfOneDetectionNumDiv').hide();
				}
			});
			// spinner
			$(`#${id}_maxNumOfOneDetectionSpinner`).spinner({ value: scanStrategy.max_num_of_one_detection, step: 1, min: MIN_MAX_NUM_OF_ONE_DETECTION, max: MAX_MAX_NUM_OF_ONE_DETECTION });
			$(`#${id}_maxNumOfOneDetection`).on('change', maxNumOfOneDetectionChange);
			$(`#${id}_maxNumOfOneDetectionSpinner button`).on('click', maxNumOfOneDetectionChange);
			// popovers
			$(`#${id} a.popovers`).popover();
			// 病毒扫描策略
			initVirusScanStrategyListener(id, scanStrategy);
		};

		/**
		 * 设置默认值
		 */
		const setDefaultValue = function () {
			// 病毒检测开关
			$(`#${id}_check`).bootstrapSwitch('state', !!flag).trigger('switchChange.bootstrapSwitch');
			// 当检测到首个恶意软件或病毒后停止检测
			if (interrupt_policy) {
				let checkbox = $(`#${id}_check1`);
				checkbox.prop('checked', true);
				checkbox.parent('span').addClass('checked');
			}
			// 检查所有未检测备份点
			if (all_timepoints_flag) {
				let $checkbox = $(`#${id}_check2`);
				$checkbox.prop('checked', true);
				$checkbox.parent('span').addClass('checked');
				$checkbox.trigger('change');
				$(`#${id}_maxNumOfOneDetection`).val(scanStrategy.max_num_of_one_detection).trigger('change');
			} else {
				$(`#${id}_maxNumOfOneDetection`).val(1).trigger('change');
			}
			// 设置病毒扫描策略默认值
			setVirusScanStrategyDefaultValue(id, scanStrategy);
		};

		$(`#${id}`).html(getContent());
		scanStrategy = formalizationScanStrategy(scanStrategy);
		initEventListener();
		setDefaultValue();
	};


	$.fn.getVirusDetectionBackup = function () {
		let id = $(this).attr('id');
		let virusCheckFlag = $(`#${id}_check`).get(0).checked;
		let virus_scan_config_list = [];

		if (virusCheckFlag) {
			let check1 = $(`#${id}_check1`).get(0).checked ? 1 : 0;
			let check2 = $(`#${id}_check2`).get(0).checked ? 1 : 0;
			// 病毒扫描策略
			let { virusScanThread, virusScanEntireSystem, virusScanEngine, virusScanEngineName, specificScanTargetMap, virusScanStrategyStr } = getVirusScanStrategyResult(id);

			let validateResult = validateVirusScanStrategy(id, virusScanEntireSystem, specificScanTargetMap);
			if (!validateResult) {
				return false;
			}
			virus_scan_config_list.push({
				strategy_type: 1,
				recover_policy: -1,
				interrupt_policy: check1,
				scan_strategy: 1,
				all_timepoints_flag: check2,
				max_num_of_one_detection: check2 ? parseInt($(`#${id}_maxNumOfOneDetection`).val()) : 0,
				skip_application_group_flag: -1,
				virus_thread_num: virusScanThread,  // 扫描线程
				scan_entire_system_flag: virusScanEntireSystem,  // 扫描对象
				virus_lib_type: virusScanEngine,  // 扫描引擎
				virus_lib_type_name: virusScanEngineName,  // 扫描引擎名称
				specific_scan_target_map: specificScanTargetMap,  // 扫描目标/排除目标
			});
		}
		return {
			virus_scan_flag: virusCheckFlag ? 1 : 0,
			virus_scan_config_list: virus_scan_config_list,
			str: $.fn.getVirusConfigDes({
				virus_scan_flag: virusCheckFlag ? 1 : 0,
				virus_scan_config_list: virus_scan_config_list,
			}, 'backup'),
		};
	};


	//先在html界面定义一个<div id="wormConfig"></div>
	// 定义病毒检测插件,接受默认值，percent代表label占的百分比，flag代表的是是否开启病毒检测，isApply代表是否有应用配置，interrupt_policy代表是否选中当检测到首个恶意软件或病毒后停止检测，skip_application_group_flag代表是否选中跳过对应用组关联备份点的检测
	// ，使用方式$('#wormConfig').virusDetectionApply('col-md-3',false,false, true, true)
	//返回值是对象，都返回1或者0，使用var a = $('#wormConfig').getVirusDetectionApply()
	//使用该组件需要引入./css/platform/component/safe_virus.css
	/**
	 *
	 * @param flag switch
	 * @param selectValue
	 * @returns {*}
	 */

	$.fn.virusDetectionApply = function (percent = 'col-md-3', flag = false, isApply = false, interrupt_policy = true, skip_application_group_flag = true, scanStrategy = {
		virus_lib_type: 0,
		virus_thread_num: 2,
		scan_entire_system_flag: 1,
		specific_scan_target_map: {},
		agent_uuid: '',
	}) {
		let id = $(this).attr('id');

		if (!flag) {
			interrupt_policy = true;
			skip_application_group_flag = true;
		}

		/**
		 * 获取内容
		 */
		const getContent = () => {
			return `
			<!-- 病毒检测 -->
			<div class="form-group">
				<label class="control-label ${percent} form-group-label" for="${id}_check">
					${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK}
				</label>
				<div class="col-md-7 form-group-content flex-items-center">
					<input type="checkbox" id="${id}_check" ${flag ? 'checked' : ''} class="make-switch" data-on-color="primary"
						data-size="small" data-off-color="info">
					<a class="popovers ml12 mb-5" data-container="body" data-trigger="hover" data-placement="right"
						data-content=${LANG.UI_SAFE_STRATEGY_BACKUP_POINT_CHECK_VIRUS}>
						<i class="viconfont vicon-tishi"></i>
					</a>
				</div>
			</div>
			<!-- 病毒扫描选项 -->
			<div class="form-group virusApplySelect">
				<label class="control-label ${percent} form-group-label"></label>
				<div class="col-md-7">
					<div class="virus-item">
						<input type="checkbox" id="${id}_check1"  value="1" name="virus" />
						<label for="${id}_check1">${LANG.UI_SAFE_SEARCH_FIRST_VIRUS_STOP}</label>
					</div>
					<div class="virus-item">
						<input type="checkbox" id="${id}_check2"  value="2" name="virus" />
						<label for="${id}_check2">${LANG.UI_SAFE_SKIP_GROUP_BACKUP_CHECK}</label>
					</div>
				</div>
			</div>
			<!-- 病毒扫描策略 -->
			<div class="form-group virusApplySelect">
				<label class="control-label form-group-label col-md-3"></label>
				<div class="col-md-9">${getVirusScanStrategy(id)}</div>
			</div>
			`;
		};

		/**
		 * 初始化事件监听器
		 */
		const initEventListener = () => {
			// 病毒检测开关
			$(`#${id}_check`).on('switchChange.bootstrapSwitch', function () {
				if (this.checked) {
					$(`#${id} .virusApplySelect`).show();
				} else {
					$(`#${id} .virusApplySelect`).hide();
				}
			});
			// popovers
			$(`#${id} a.popovers`).popover();
			// 病毒扫描策略
			initVirusScanStrategyListener(id, scanStrategy);
		};

		/**
		 * 设置默认值
		 */
		const setDefaultValue = function () {
			// 病毒检测开关
			$(`#${id}_check`).bootstrapSwitch('state', !!flag).trigger('switchChange.bootstrapSwitch');
			// 当检测到首个恶意软件或病毒后停止检测
			if (interrupt_policy) {
				let checkbox = $(`#${id}_check1`);
				checkbox.prop('checked', true);
				checkbox.parent('span').addClass('checked');
			}
			// 跳过对应用组关联备份点的检测
			if (!isApply) {
				$(`#${id}_check2`).parents('.virus-item').hide();
			} else {
				$(`#${id}_check2`).parents('.virus-item').show();
				if (skip_application_group_flag) {
					let checkbox = $(`#${id}_check2`);
					checkbox.prop('checked', true);
					checkbox.parent('span').addClass('checked');
				}
			}
			// 设置病毒扫描策略默认值
			setVirusScanStrategyDefaultValue(id, scanStrategy);
		};

		$(`#${id}`).html(getContent());
		scanStrategy = formalizationScanStrategy(scanStrategy);
		initEventListener();
		setDefaultValue();
	};
	$.fn.getVirusDetectionApply = function () {
		let id = $(this).attr('id');
		let virusCheckFlag = $(`#${id}_check`).get(0).checked;
		let virus_scan_config_list = [];

		if (virusCheckFlag) {
			let check1 = $(`#${id}_check1`).get(0).checked ? 1 : 0
			let check2 = $(`#${id}_check2`).get(0).checked ? 1 : 0
			// 病毒扫描策略
			let { virusScanThread, virusScanEntireSystem, virusScanEngine, virusScanEngineName, specificScanTargetMap, virusScanStrategyStr } = getVirusScanStrategyResult(id);

			let validateResult = validateVirusScanStrategy(id, virusScanEntireSystem, specificScanTargetMap);
			if (!validateResult) {
				return false;
			}
			virus_scan_config_list.push({
				strategy_type: 1,
				recover_policy: -1,
				interrupt_policy: check1,
				scan_strategy: 1,
				all_timepoints_flag: -1,
				max_num_of_one_detection: 0,
				skip_application_group_flag: check2,
				virus_thread_num: virusScanThread,  // 扫描线程
				scan_entire_system_flag: virusScanEntireSystem,  // 扫描对象
				virus_lib_type: virusScanEngine,  // 扫描引擎
				virus_lib_type_name: virusScanEngineName,  // 扫描引擎名称
				specific_scan_target_map: specificScanTargetMap,  // 扫描目标/排除目标
			});
		}
		return {
			virus_scan_flag: virusCheckFlag ? 1 : 0,
			virus_scan_config_list: virus_scan_config_list,
			str: $.fn.getVirusConfigDes({
				virus_scan_flag: virusCheckFlag ? 1 : 0,
				virus_scan_config_list: virus_scan_config_list,
			}, 'apply'),
		};
	};



	//先在html界面定义一个<div id="wormConfig"></div>
	//定义病毒检测插件,接受默认值，isNoScan代表传递过来的备份源是否有未扫描的，isHeathy代表传递过来的备份源是否是健康的，
	// isInfect代表传递过来的备份源是否有已感染的，使用方式$('#wormConfig').virusDetectionCover(false,false,false)，
	//isFlagCover代表整机定时没有恢复到无网络环境 如果是整机定时就传true，
	//返回值是一个对象数组，如[{strategy_type:1;recover_policy:1;interrupt_policy:1}]代表只选择了未扫描策略，并且每个都选中了第一项，使用var a = $('#wormConfig').getVirusDetectionCover()
	//使用该组件需要引入./css/platform/component/safe_virus.css和 ./scripts/public/initComponents.js
	/**
	 *
	 * @param flag switch
	 * @param selectValue
	 * @returns {*}
	 */

	$.fn.virusDetectionCover = function (isNoScan = true, isHeathy = true, isInfect = true, isFlagCover = false, scanStrategy = {
		no_scan: {
			virus_lib_type: 0,
			virus_thread_num: 2,
			scan_entire_system_flag: 1,
			specific_scan_target_map: {},
			agent_uuid: '',
			virus_scan_status: $.fn.virusDefine.virus_scan_status.no_scan,
		},
		healthy: {
			virus_lib_type: 0,
			virus_thread_num: 2,
			scan_entire_system_flag: 1,
			specific_scan_target_map: {},
			agent_uuid: '',
			virus_scan_status: $.fn.virusDefine.virus_scan_status.safe,
		},
		reflected: {
			virus_lib_type: 0,
			virus_thread_num: 2,
			scan_entire_system_flag: 1,
			specific_scan_target_map: {},
			agent_uuid: '',
			virus_scan_status: $.fn.virusDefine.virus_scan_status.infected,
		}
	}) {
		let id = $(this).attr('id');

		/**
		 * 获取已感染策略的html
		 */
		const getReflectedHtml = () => {
			return `
			<div class="tab-pane" id="${id}_reflected" role="tabpanel">
				<!-- 备份点处理方式 -->
				<div class="form-group">
					<label class="control-label col-md-3 form-group-label safe-label pl0_en">${LANG.UI_MACHINE_OS_POINT_HANDLE_METHOD}</label>
					<div class="col-md-9">
						<div class="radio-group" id="${id}_reflected_select">
							<label class="radio-group__item with-svg me-20 w-122px_en" value="1">
								${LANG.UI_RECOVERY_ZHIJIE}
							</label>
							<label class="radio-group__item with-svg me-20 w-122px_en" value="3">
								${LANG.UI_RECOVERY_RESCAN}
							</label>
							<label class="radio-group__item with-svg active w-122px_en" value="2">
								${LANG.UI_RECOVERY_VIRUS_COVERY}
							</label>
						</div>
					</div>
				</div>
				<!-- 扫描策略 -->
				<div class="form-group display-none reflectedVirus">
					<label class="control-label col-md-3 form-group-label" for="${id}_reflectedVirus">${LANG.UI_SAFE_SCAN_STRATEGY}</label>
					<div class="col-md-7">
						<select id="${id}_reflectedVirus" class="form-control">
							<option value="1">${LANG.UI_SAFE_SCAN_VIRUS_STOP_RECOVER}</option>
							<option value="3">${LANG.UI_SAFE_SCAN_VIRUS_VIRUS_RECOVER}</option>
							<option value="2" class="${id}_recoveryToNoNetworkEnv"">${LANG.UI_SAFE_SCAN_VIRUS_RECOVER_NO_INTER}</option>
						</select>
					</div>
				</div>
				<!-- 病毒扫描策略 -->
				<div id="${id}_reflected_virus_strategy" class="virusStrategy">
					<!-- 扫描引擎 -->
					${getVirusScanStrategyScanEngine(id + '_reflected_virus_strategy', 3)}
					<!-- 扫描线程 -->
					${getVirusScanStrategyScanThread(id + '_reflected_virus_strategy', 3)}
					<!-- 扫描对象 -->
					${getVirusScanStrategyScanEntire(id + '_reflected_virus_strategy', 3)}
					<!-- 扫描/排除配置 -->
					${getIncludeExcludeConfig(id + '_reflected_virus_strategy', 3)}
				</div>
			</div>
			`;
		};

		/**
		 * 获取健康策略的html
		 */
		const getHealthyHtml = () => {
			return `
			<div class="tab-pane" id="${id}_healthy" role="tabpanel">
				<!-- 备份点处理方式 -->
				<div class="form-group">
					<label class="control-label col-md-3 form-group-label safe-label pl0_en">${LANG.UI_MACHINE_OS_POINT_HANDLE_METHOD}</label>
					<div class="col-md-9">
						<div class="radio-group" id="${id}_healthy_select">
							<label class="radio-group__item with-svg me-20 active" value="1">
								${LANG.UI_RECOVERY_ZHIJIE}
							</label>
							<label class="radio-group__item with-svg me-20" value="3">
								${LANG.UI_RECOVERY_RESCAN}
							</label>
						</div>
					</div>
				</div>
				<!-- 扫描策略 -->
				<div class="form-group display-none healthVirus">
					<label class="control-label col-md-3 form-group-label" for="${id}_healthyVirus">${LANG.UI_SAFE_SCAN_STRATEGY}</label>
					<div class="col-md-7">
						<select id="${id}_healthyVirus" class="form-control">
							<option value="1">${LANG.UI_SAFE_SCAN_VIRUS_STOP_RECOVER}</option>
							<option value="3">${LANG.UI_SAFE_SCAN_VIRUS_VIRUS_RECOVER}</option>
							<option value="2" class="${id}_recoveryToNoNetworkEnv"">${LANG.UI_SAFE_SCAN_VIRUS_RECOVER_NO_INTER}</option>
						</select>
					</div>
				</div>
				<!-- 病毒扫描策略 -->
				<div id="${id}_healthy_virus_strategy" class="virusStrategy">
					<!-- 扫描引擎 -->
					${getVirusScanStrategyScanEngine(id + '_healthy_virus_strategy', 3)}
					<!-- 扫描线程 -->
					${getVirusScanStrategyScanThread(id + '_healthy_virus_strategy', 3)}
					<!-- 扫描对象 -->
					${getVirusScanStrategyScanEntire(id + '_healthy_virus_strategy', 3)}
					<!-- 扫描/排除配置 -->
					${getIncludeExcludeConfig(id + '_healthy_virus_strategy', 3)}
				</div>
			</div>
			`;
		};

		/**
		 * 获取未扫描策略的html
		 */
		const getNoScanHtml = () => {
			return `
			<div class="tab-pane" id="${id}_noScan" role="tabpanel">
				<!-- 备份点处理方式 -->
				<div class="form-group">
					<label class="control-label col-md-3 form-group-label safe-label pl0_en">${LANG.UI_MACHINE_OS_POINT_HANDLE_METHOD}</label>
					<div class="col-md-9">
						<div class="radio-group" id="${id}_noScan_select">
							<label class="radio-group__item with-svg me-20 active" value="1">
								${LANG.UI_RECOVERY_ZHIJIE}
							</label>
							<label class="radio-group__item with-svg me-20" value="3">
								${LANG.UI_SAFE_TAKE_SCAN}
							</label>
						</div>
					</div>
				</div>
				<!-- 扫描策略 -->
				<div class="form-group display-none noScanBackup">
					<label class="control-label col-md-3 form-group-label" for="${id}_noScanVirus">${LANG.UI_SAFE_SCAN_STRATEGY}</label>
					<div class="col-md-7">
						<select id="${id}_noScanVirus" class="form-control">
							<option value="1">${LANG.UI_SAFE_SCAN_ABNORMAL_STOP}</option>
							<option value="3">${LANG.UI_SAFE_SCAN_ABNORMAL_VIRUS_STOP}</option>
							<option value="2" class="${id}_recoveryToNoNetworkEnv"">${LANG.UI_SAFE_SCAN_ABNORMAL_RECOVER_NO_WEB}</option>
						</select>
					</div>
				</div>
				<!-- 病毒扫描策略 -->
				<div id="${id}_no_scan_virus_strategy" class="virusStrategy">
					<!-- 扫描引擎 -->
					${getVirusScanStrategyScanEngine(id + '_no_scan_virus_strategy', 3)}
					<!-- 扫描线程 -->
					${getVirusScanStrategyScanThread(id + '_no_scan_virus_strategy', 3)}
					<!-- 扫描对象 -->
					${getVirusScanStrategyScanEntire(id + '_no_scan_virus_strategy', 3)}
					<!-- 扫描/排除配置 -->
					${getIncludeExcludeConfig(id + '_no_scan_virus_strategy', 3)}
				</div>
			</div>
			`;
		};

		/**
		 * 获取内容
		 */
		const getContent = () => {
			return `
			<div class="htmlStrategy timepointVirusScanStrategy">
				<div class="strategyHead">  
					<div class="strategyPoint"></div>
					<div class="strategyText">
						${LANG.UI_MACHINE_OS_VIRUS_SCAN_STRATEGY}
						<span class="virus-descript"> ${LANG.UI_SAFE_BACKUP_DIFF_VIRUS}</span>
					</div>
				</div>
				<div class="bodyVirus">
					<!-- 顶部提示信息 -->
					<div class="alert alert-block alert-danger fade in" id="${id}_topTips__wrapper">
						<button type="button" class="close" data-dismiss="alert"></button>
						<i class="fa fa-warning"></i>
						<span id="${id}_topTips"></span>
					</div>
					<!-- 病毒扫描主体 -->
					<div class="row d-flex">
						<!-- 左侧导航栏 -->
						<div class="col-md-3 timepointVirusScanStrategy__left" style="border-right: 1px solid #EBEBEB">
							<ul class="nav flex-column" role="tablist" aria-orientation="vertical">
								<!-- 未扫描 -->
								<li class="nav-item" id="${id}_noScanTitle">
									<a class="nav-link nav-text" data-toggle="tab" href="#${id}_noScan" role="tab" aria-expanded="false">  
										<i class="viconfont vicon-a-Scanning-twosaomiao1" style="color: #F19F00"></i>
										${LANG.UI_SAFE_NO_SCAN_BACKUP_POINT}
									</a>
								</li>
								<!-- 健康 -->	
								<li class="nav-item" id="${id}_healthyTitle">
									<a class="nav-link nav-text"   data-toggle="tab" href="#${id}_healthy" role="tab" aria-expanded="false">
										<i class="viconfont vicon-a-Check-correctduigou" style="color: #0FBF98"></i>
										${LANG.UI_SAFE_HEALTH_BACKUP_POINT}
									</a>
								</li>
								<!-- 已感染 -->
								<li class="nav-item" id="${id}_reflectedTitle">
									<a class="nav-link nav-text" data-toggle="tab" href="#${id}_reflected" role="tab" aria-expanded="false">
										<i class="viconfont vicon-a-Cautionbaocuo" style="color: #F1416C"></i>
										${LANG.UI_SAFE_REFECT_BACKUP_POINT}
									</a>
								</li>
							</ul>
						</div>
						<!-- Tab panes -->
						<div class="col-md-9 tab-content timepointVirusScanStrategy__right">
							${getNoScanHtml()}
							${getHealthyHtml()}
							${getReflectedHtml()}
						</div>
					</div>
				</div>
			</div>
			`;
		};

		/**
		 * 初始化按钮样式的单选框组
		 */
		const initRadioButtons = () => {
			const LEFT_AND_RIGHT_PADDING_WIDTH = 40; // 左右总padding宽度
			const LEFT_AND_RIGHT_BORDER_WIDTH = 2; // 左右总border宽度
			let radioButtonElList = [].slice.call(document.querySelectorAll(`#${id} .radio-group`));

			radioButtonElList.forEach(el => {
				let max_width = 0;
				$(el).find('.radio-group__item:not(.with-svg)').each(function () {
					// 获取每一组 radio-group 中 radio-group__item 的完整宽度（自身宽度 + 左右padding + 左右border）
					let fullWidth = $(this).width() + LEFT_AND_RIGHT_PADDING_WIDTH + LEFT_AND_RIGHT_BORDER_WIDTH;
					if (fullWidth > max_width) {
						max_width = fullWidth;
					}
				});
				$(el).find('.radio-group__item:not(.with-svg)').each(function () {
					// 将每一组 radio-group 中 radio-group__item 的最大宽度设置为其余项的宽度
					$(this).width(max_width - LEFT_AND_RIGHT_PADDING_WIDTH - LEFT_AND_RIGHT_BORDER_WIDTH);
				});

				$(el).on('click', '.radio-group__item', function (e) {
					let oldValue = $(el).find('.radio-group__item.active').attr("value");
					// 移除上一个的active样式
					$(el).find('.radio-group__item.active').removeClass('active');

					// 触发 change 事件
					let newValue = $(this).attr("value");
					if (oldValue !== newValue) {
						$(el).trigger('change', [oldValue, newValue]);
					}

					let radioButtonItemList = [].slice.call(el.querySelectorAll('.radio-group__item'));

					radioButtonItemList.forEach(i => {
						let value = $(i).attr("value");
						let currentValue = $(e.target).attr("value");

						if (value === currentValue) {
							$(i).addClass('active');
						}
					});
				});
			});
		};

		/**
		 * 初始化事件监听器
		 */
		const initEventListener = () => {
			// 注册备份点恢复方式事件
			initRadioButtons();
			$(`#${id}_noScan_select`).on('change', (e, oldVal, newVal) => {
				let number = newVal
				if (number == 1) {
					$(`#${id} .noScanBackup`).hide();
					$(`#${id}_no_scan_virus_strategy`).hide();
				} else {
					$(`#${id} .noScanBackup`).show();
					$(`#${id}_no_scan_virus_strategy`).show();
				}
			});
			$(`#${id}_healthy_select`).on('change', (e, oldVal, newVal) => {
				let number = newVal
				if (number == 1) {
					$(`#${id} .healthVirus`).hide();
					$(`#${id}_healthy_virus_strategy`).hide();
				} else {
					$(`#${id} .healthVirus`).show();
					$(`#${id}_healthy_virus_strategy`).show();
				}
			});
			$(`#${id}_reflected_select`).on('change', (e, oldVal, newVal) => {
				let number = newVal
				if (number == 1 || number == 2) {
					$(`#${id} .reflectedVirus`).hide();
					$(`#${id}_reflected_virus_strategy`).hide();
				} else {
					$(`#${id} .reflectedVirus`).show();
					$(`#${id}_reflected_virus_strategy`).show();
				}
			});
			// popovers
			$(`#${id} a.popovers`).popover();
			// 病毒扫描策略
			initVirusScanStrategyListener(id + '_no_scan_virus_strategy', scanStrategy.no_scan);
			initVirusScanStrategyListener(id + '_healthy_virus_strategy', scanStrategy.healthy);
			initVirusScanStrategyListener(id + '_reflected_virus_strategy', scanStrategy.reflected);
		};

		/**
		 * 设置默认显示的tab
		 */
		const setDefaultShowTab = () => {
			if (isNoScan) {
				$(`#${id}_noScanTitle`).addClass('active');
				$(`a[href="#${id}_noScan"]`).prop('aria-expanded', 'true');
				$(`#${id}_noScan`).addClass('active');
			} else if (isHeathy) {
				$(`#${id}_healthyTitle`).addClass('active');
				$(`a[href="#${id}_healthy"]`).prop('aria-expanded', 'true');
				$(`#${id}_healthy`).addClass('active');
			} else if (isInfect) {
				$(`#${id}_reflectedTitle`).addClass('active');
				$(`a[href="#${id}_reflected"]`).prop('aria-expanded', 'true');
				$(`#${id}_reflected`).addClass('active');
			}
		};

		/**
		 * 设置默认值
		 */
		const setDefaultValue = () => {
			if (!isNoScan) {
				$(`#${id}_noScanTitle`).addClass('disabled');
			}
			if (!isHeathy) {
				$(`#${id}_healthyTitle`).addClass('disabled');
			}
			if (!isInfect) {
				$(`#${id}_reflectedTitle`).addClass('disabled');
			}
			if (isFlagCover) {
				$(`.${id}_recoveryToNoNetworkEnv`).hide();
			}
			if (isHeathy && !isNoScan && !isInfect) {
				$(`#${id}_topTips__wrapper`).hide();
			} else {
				$(`#${id}_topTips__wrapper`).show();
				// 设置提示信息
				if (isNoScan && !isInfect) {
					$(`#${id}_topTips`).text(LANG.UI_SAFE_CHOOSE_BACKUP_VIRUS_SCAN_RECOVER_VIRUS);
				} else if (!isNoScan && isInfect) {
					$(`#${id}_topTips`).text(LANG.UI_SAFE_CHOOSE_REFECT_BACKUP_VIRUS_SCAN_RECOVER_VIRUS);
				} else {
					$(`#${id}_topTips`).text(LANG.UI_SAFE_CHOOSE_REFECT_AND_NO_SCAN_BACKUP_VIRUS_SCAN_RECOVER_VIRUS);
				}
			}
			// 填充默认值
			// 病毒扫描策略
			setVirusScanStrategyDefaultValue(id + '_no_scan_virus_strategy', scanStrategy.no_scan);
			setVirusScanStrategyDefaultValue(id + '_healthy_virus_strategy', scanStrategy.healthy);
			setVirusScanStrategyDefaultValue(id + '_reflected_virus_strategy', scanStrategy.reflected);
			$(`#${id}_noScan_select`).trigger('change', [0, 1]);
			$(`#${id}_healthy_select`).trigger('change', [0, 1]);
			$(`#${id}_reflected_select`).trigger('change', [0, 2]);
			if (isInfect) {  // 已感染
				if (parseInt(scanStrategy.reflected.virus_scan_status) === $.fn.virusDefine.virus_scan_status.infected_but_unfinished) {  // 已感染但未完成
					$(`#${id}_reflected_select label[value="2"]`).hide();
				}
			}
		};

		$(`#${id}`).html(getContent());
		scanStrategy.no_scan = formalizationScanStrategy(scanStrategy.no_scan);
		scanStrategy.healthy = formalizationScanStrategy(scanStrategy.healthy);
		scanStrategy.reflected = formalizationScanStrategy(scanStrategy.reflected);
		initEventListener();
		setDefaultShowTab();
		setDefaultValue();
	};

	$.fn.getVirusDetectionCover = function () {
		let id = $(this).attr('id');
		let virus_scan_config_list = [];
		let noScanTitle = $(`#${id}_noScanTitle`);
		if (!noScanTitle.hasClass('disabled')) {
			let selectBackup = parseInt($(`#${id}_noScan_select`).find('.radio-group__item.active').attr("value"));
			let interruptPolicy = parseInt($(`#${id}_noScanVirus`).val() || -1);
			var virusScanThread = 0;
			var virusScanEntireSystem = 0;
			var virusScanEngine = 0;
			var virusScanEngineName = '';
			var specificScanTargetMap = {};
			let scanStrategyType = $.fn.virusDefine.scan_strategy_type.only_scan;
			if (selectBackup === $.fn.virusDefine.timepoint_process_mode.execute_scan) {  // 执行/重新扫描
				var { virusScanThread, virusScanEntireSystem, virusScanEngine, virusScanEngineName, specificScanTargetMap, virusScanStrategyStr } = getVirusScanStrategyResult(id + '_no_scan_virus_strategy');

				let validateResult = validateVirusScanStrategy(id + '_no_scan_virus_strategy', virusScanEntireSystem, specificScanTargetMap, LANG.UI_SAFE_NO_SCAN_BACKUP_POINT + ', ');
				if (!validateResult) {
					return false;
				}
				if (interruptPolicy === $.fn.virusDefine.interrupt_policy.remove_virus_recovery) {  // 扫描异常后杀毒后恢复，扫描策略为 4-扫描并查杀可疑文件，失败则跳过
					scanStrategyType = $.fn.virusDefine.scan_strategy_type.scan_kill_fail_skip;
				}
			} else if (selectBackup === $.fn.virusDefine.timepoint_process_mode.immediate_recovery) {  // 直接恢复
				scanStrategyType = -1;
				interruptPolicy = -1;
			}
			virus_scan_config_list.push({
				strategy_type: $.fn.virusDefine.strategy_type.no_scan,
				recover_policy: selectBackup,
				interrupt_policy: interruptPolicy,
				scan_strategy: scanStrategyType,
				all_timepoints_flag: -1,
				max_num_of_one_detection: 0,
				skip_application_group_flag: -1,
				virus_thread_num: virusScanThread,  // 扫描线程
				scan_entire_system_flag: virusScanEntireSystem,  // 扫描对象
				virus_lib_type: virusScanEngine,  // 扫描引擎
				virus_lib_type_name: virusScanEngineName,  // 扫描引擎名称
				specific_scan_target_map: specificScanTargetMap,  // 扫描目标/排除目标
			});
		}
		let healthTitle = $(`#${id}_healthyTitle`)
		if (!healthTitle.hasClass('disabled')) {
			let selectBackup = parseInt($(`#${id}_healthy_select`).find('.radio-group__item.active').attr("value"));
			let interruptPolicy = parseInt($(`#${id}_healthyVirus`).val() || -1);
			var virusScanThread = 0;
			var virusScanEntireSystem = 0;
			var virusScanEngine = 0;
			var virusScanEngineName = '';
			var specificScanTargetMap = {};
			let scanStrategyType = $.fn.virusDefine.scan_strategy_type.only_scan;
			if (selectBackup === $.fn.virusDefine.timepoint_process_mode.execute_scan) {  // 执行/重新扫描
				var { virusScanThread, virusScanEntireSystem, virusScanEngine, virusScanEngineName, specificScanTargetMap, virusScanStrategyStr } = getVirusScanStrategyResult(id + '_healthy_virus_strategy');

				let validateResult = validateVirusScanStrategy(id + '_healthy_virus_strategy', virusScanEntireSystem, specificScanTargetMap, LANG.UI_SAFE_HEALTH_BACKUP_POINT + ', ');
				if (!validateResult) {
					return false;
				}
				if (interruptPolicy === $.fn.virusDefine.interrupt_policy.remove_virus_recovery) {  // 扫描异常后杀毒后恢复，扫描策略为 4-扫描并查杀可疑文件，失败则跳过
					scanStrategyType = $.fn.virusDefine.scan_strategy_type.scan_kill_fail_skip;
				}
			} else if (selectBackup === $.fn.virusDefine.timepoint_process_mode.immediate_recovery) {  // 直接恢复
				scanStrategyType = -1;
				interruptPolicy = -1;
			}
			virus_scan_config_list.push({
				strategy_type: $.fn.virusDefine.strategy_type.healthy,
				recover_policy: Number(selectBackup),
				interrupt_policy: interruptPolicy,
				scan_strategy: scanStrategyType,
				all_timepoints_flag: -1,
				max_num_of_one_detection: 0,
				skip_application_group_flag: -1,
				virus_thread_num: virusScanThread,  // 扫描线程
				scan_entire_system_flag: virusScanEntireSystem,  // 扫描对象
				virus_lib_type: virusScanEngine,  // 扫描引擎
				virus_lib_type_name: virusScanEngineName,  // 扫描引擎名称
				specific_scan_target_map: specificScanTargetMap,  // 扫描目标/排除目标
			});
		}
		let infectTitle = $(`#${id}_reflectedTitle`)
		if (!infectTitle.hasClass('disabled')) {
			let selectBackup = parseInt($(`#${id}_reflected_select`).find('.radio-group__item.active').attr("value"));
			let interruptPolicy = parseInt($(`#${id}_reflectedVirus`).val() || -1);
			var virusScanThread = 0;
			var virusScanEntireSystem = 0;
			var virusScanEngine = 0;
			var virusScanEngineName = '';
			var specificScanTargetMap = {};
			let scanStrategyType = $.fn.virusDefine.scan_strategy_type.only_scan;
			if (selectBackup === $.fn.virusDefine.timepoint_process_mode.execute_scan) {  // 执行/重新扫描
				var { virusScanThread, virusScanEntireSystem, virusScanEngine, virusScanEngineName, specificScanTargetMap, virusScanStrategyStr } = getVirusScanStrategyResult(id + '_reflected_virus_strategy');

				let validateResult = validateVirusScanStrategy(id + '_reflected_virus_strategy', virusScanEntireSystem, specificScanTargetMap, LANG.UI_SAFE_REFECT_BACKUP_POINT + ', ');
				if (!validateResult) {
					return false;
				}
				if (interruptPolicy === $.fn.virusDefine.interrupt_policy.remove_virus_recovery) {  // 扫描异常后杀毒后恢复，扫描策略为 4-扫描并查杀可疑文件，失败则跳过
					scanStrategyType = $.fn.virusDefine.scan_strategy_type.scan_kill_fail_skip;
				}
			} else if (selectBackup === $.fn.virusDefine.timepoint_process_mode.immediate_recovery) {  // 直接恢复
				scanStrategyType = -1;
				interruptPolicy = -1;
			}
			virus_scan_config_list.push({
				strategy_type: $.fn.virusDefine.strategy_type.reflected,
				recover_policy: Number(selectBackup),
				interrupt_policy: interruptPolicy,
				scan_strategy: scanStrategyType,
				all_timepoints_flag: -1,
				max_num_of_one_detection: 0,
				skip_application_group_flag: -1,
				virus_thread_num: virusScanThread,  // 扫描线程
				scan_entire_system_flag: virusScanEntireSystem,  // 扫描对象
				virus_lib_type: virusScanEngine,  // 扫描引擎
				virus_lib_type_name: virusScanEngineName,  // 扫描引擎名称
				specific_scan_target_map: specificScanTargetMap,  // 扫描目标/排除目标
			});
		}
		return {
			virus_scan_flag: 1,
			virus_scan_config_list,
			str: $.fn.getVirusConfigDes({
				virus_scan_flag: 1,
				virus_scan_config_list: virus_scan_config_list,
			}, 'recovery'),
		};
	};

	//1.先在html界面定义一个<div id="example"></div>
	// RECOVER_POLICY: {
	//     DIRECT_COVER: 1, //直接恢复
	//         VIRUS: 2, //杀毒后恢复
	//         SCAN: 3, //执行扫描
	//         DIRECT_TAKE: 4,//直接接管
	// }
	// INTERRUPT: {
	//     STOP: 1,//扫出病毒后停止(验证任务停止检测，恢复任务停止恢复)
	//         NO_INTER: 2,//扫出病毒后恢复到无网络环境
	//         KILL_COVER: 3,//扫出病毒后杀除并恢复
	//         TAKE_ONINTER: 4,//扫出病毒后接管到无网络环境
	//         KILL_TAKE: 5,//杀除后接管
	// }
	//2.使用方式$('#example').takeAppOver('col-md-3',CONF.RECOVER_POLICY.SCAN, CONF.INTERRUPT.STOP),这里传参是传一个类名，代表label占几分的类，通常是3份或者4份，takeover代表接管处理方式选择的是第几项，virus代表病毒扫描异常处理选择的是第几项，
	//3.返回值是对象，包含两个值，virus_scan_config_list是选择的每个值str是选中的文字描述
	// 使用var a = $('#example').getTakeAppOver()
	//4.使用该组件需要引入./css/platform/component/safe_virus.css 和 ./scripts/public/initComponents.js
	/**
	 *
	 * @param flag switch
	 * @param selectValue
	 * @returns {*}
	 */
	$.fn.takeAppOver = function (percent = 'col-md-3', takeover = CONF.RECOVER_POLICY.DIRECT_TAKE, virus = CONF.INTERRUPT.STOP, scanStrategy = {
		virus_lib_type: 0,
		virus_thread_num: 2,
		scan_entire_system_flag: 1,
		specific_scan_target_map: {},
		agent_uuid: '',
	}) {
		let id = $(this).attr('id');

		const getAccordionHtml = () => {
			return `
			<!-- 接管处理方式 -->
			<div class="form-group" style="display: flex;align-items: center">
				<label class="control-label col-md-3 form-group-label" style="padding-top: 0px">${LANG.UI_SAFE_TAKEOVER_SETTING}</label>
				<div class="col-md-7 col-md-9_en">
					<div class="radio-group" id="${id}_takeover_select">
						<label class="radio-group__item with-svg me-20" value=${CONF.RECOVER_POLICY.DIRECT_TAKE}>
							${LANG.UI_SAFE_DECTION_TAKEOVER}
						</label>
						<label class="radio-group__item with-svg me-20 mr-0_en" value=${CONF.RECOVER_POLICY.SCAN}>
							${LANG.UI_SAFE_TAKE_SCAN}
						</label>
					</div>
				</div>
			</div>
			<!-- 扫描策略 -->
			<div class="form-group" id="${id}_scan_strategy__wrapper" style="display: flex;align-items: center">
				<label class="control-label col-md-3 form-group-label" style="padding-top: 0px" for="${id}_scan_strategy">${LANG.UI_SAFE_SCAN_STRATEGY}</label>
				<div class="col-md-7">
					<select id="${id}_scan_strategy" class="form-control">
						<option value=${CONF.INTERRUPT.STOP}>${LANG.UI_SAFE_VIRUS_SCAN_ABNORNAL_STOP}</option>
						<option value=${CONF.INTERRUPT.KILL_TAKE}>${LANG.UI_SAFE_VIRUS_SCAN_ABNORNAL_VIRUS_TAKEOVER}</option>
						<option value=${CONF.INTERRUPT.TAKE_ONINTER}>${LANG.UI_SAFE_VIRUS_SCAN_ABNORNAL_TAKEOVER_NO_INTER}</option>
					</select>
				</div>
			</div>
			<!-- 病毒扫描策略 -->
			<div id="${id}_virus_strategy" class="virusStrategy">
				<!-- 扫描引擎 -->
				${getVirusScanStrategyScanEngine(id, 3)}
				<!-- 扫描线程 -->
				${getVirusScanStrategyScanThread(id, 3)}
				<!-- 扫描对象 -->
				${getVirusScanStrategyScanEntire(id, 3)}
				<!-- 扫描/排除配置 -->
				${getIncludeExcludeConfig(id, 3)}
			</div>
			`;
		};

		/**
		 * 获取内容
		 */
		const getContent = () => {
			return `
			<div class="form-group">
				<label class="control-label ${percent} form-group-label">${LANG.UI_SAFE_STRATEGY_VIRUS_CHECK}</label>
				<div class="form-group-content">
					<div class="form-group completeSelect">
						<label class="control-label form-group-label"></label>
						<div class="${percent = 'col-md-3' ? 'col-md-9' : 'col-md-8'}">
							<div class="accordion virusTakeoverStrategy">
								<div class="panel panel-default strategy-panel">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a class="accordion-toggle accordion-toggle-styled popovers" data-container="body"
												data-trigger="hover" data-placement="top" data-toggle="collapse"
												data-parent=".virusTakeoverStrategy" href="#${id}_takeOverSet" aria-expanded="true">
												<i class="levelchild viconfont vicon-pt_setting_email_notification font-green-seagreen"></i>
												<span class="font-green-seagreen">${LANG.UI_GLOBAL_STRATEGY_MESSAGE}</span>
												<span class="splitText"></span>
											</a>
										</h4>
									</div>
									<div id="${id}_takeOverSet" class="panel-collapse collapse in ">
										<div class="panel-body">${getAccordionHtml()}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			`;
		};

		/**
		 * 初始化按钮样式的单选框组
		 */
		const initRadioButtons = () => {
			const LEFT_AND_RIGHT_PADDING_WIDTH = 40; // 左右总padding宽度
			const LEFT_AND_RIGHT_BORDER_WIDTH = 2; // 左右总border宽度
			let radioButtonElList = [].slice.call(document.querySelectorAll(`#${id} .radio-group`));

			radioButtonElList.forEach(el => {
				let max_width = 0;
				$(el).find('.radio-group__item:not(.with-svg)').each(function () {
					// 获取每一组 radio-group 中 radio-group__item 的完整宽度（自身宽度 + 左右padding + 左右border）
					let fullWidth = $(this).width() + LEFT_AND_RIGHT_PADDING_WIDTH + LEFT_AND_RIGHT_BORDER_WIDTH;
					if (fullWidth > max_width) {
						max_width = fullWidth;
					}
				});
				$(el).find('.radio-group__item:not(.with-svg)').each(function () {
					// 将每一组 radio-group 中 radio-group__item 的最大宽度设置为其余项的宽度
					$(this).width(max_width - LEFT_AND_RIGHT_PADDING_WIDTH - LEFT_AND_RIGHT_BORDER_WIDTH);
				});

				$(el).on('click', '.radio-group__item', function (e) {
					let oldValue = $(el).find('.radio-group__item.active').attr("value");
					// 移除上一个的active样式
					$(el).find('.radio-group__item.active').removeClass('active');

					// 触发 change 事件
					let newValue = $(this).attr("value");
					if (oldValue !== newValue) {
						$(el).trigger('change', [oldValue, newValue]);
					}

					let radioButtonItemList = [].slice.call(el.querySelectorAll('.radio-group__item'));

					radioButtonItemList.forEach(i => {
						let value = $(i).attr("value");
						let currentValue = $(e.target).attr("value");

						if (value === currentValue) {
							$(i).addClass('active');
						}
					});
				});
			});
		};

		/**
		 * 初始化事件监听器
		 */
		const initEventListener = () => {
			initRadioButtons();
			$(`#${id}_takeover_select`).on('change', (e, oldVal, newVal) => {
				let number = newVal
				if (number == CONF.RECOVER_POLICY.SCAN) {
					$(`#${id}_scan_strategy__wrapper`).show();
					$(`#${id}_virus_strategy`).show();
				} else {
					$(`#${id}_scan_strategy__wrapper`).hide();
					$(`#${id}_virus_strategy`).hide();
				}
			});
			// popovers
			$(`#${id} a.popovers`).popover();
			// 病毒扫描策略
			initVirusScanStrategyListener(id, scanStrategy);
		};

		/**
		 * 设置默认值
		 */
		const setDefaultValue = () => {
			// 接管处理方式
			$(`#${id}_takeover_select label.radio-group__item`).removeClass('active');
			$(`#${id}_takeover_select label.radio-group__item[value=${takeover}]`).addClass('active');
			$(`#${id}_takeover_select`).trigger('change', [0, takeover]);
			// 扫描策略
			$(`#${id}_scan_strategy`).val(virus);
			// 病毒扫描策略
			setVirusScanStrategyDefaultValue(id, scanStrategy);
		};

		$(`#${id}`).html(getContent());
		scanStrategy = formalizationScanStrategy(scanStrategy);
		initEventListener();
		setDefaultValue();
	};

	$.fn.getTakeAppOver = function () {
		let id = $(this).attr('id');
		let $takeoverSelect = $(`#${id}_takeover_select`);
		let takeoverStrategy = CONF.RECOVER_POLICY.DIRECT_TAKE;  // 默认直接接管
		let interruptPolicy = -1;
		if ($takeoverSelect.length) {
			takeoverStrategy = parseInt($takeoverSelect.find('.radio-group__item.active').attr("value"));
			interruptPolicy = parseInt($(`#${id}_scan_strategy`).val() || -1);
		}
		let virusScanThread = 0;
		let virusScanEntireSystem = 0;
		let virusScanEngine = 0;
		var virusScanEngineName = '';
		let specificScanTargetMap = {};
		let scanStrategyType = $.fn.virusDefine.scan_strategy_type.only_scan;
		if (takeoverStrategy === CONF.RECOVER_POLICY.SCAN) {
			let virusScanStrategyResult = getVirusScanStrategyResult(id);
			virusScanThread = virusScanStrategyResult.virusScanThread;
			virusScanEntireSystem = virusScanStrategyResult.virusScanEntireSystem;
			virusScanEngine = virusScanStrategyResult.virusScanEngine;
			virusScanEngineName = virusScanStrategyResult.virusScanEngineName;
			specificScanTargetMap = virusScanStrategyResult.specificScanTargetMap;

			let validateResult = validateVirusScanStrategy(id, virusScanEntireSystem, specificScanTargetMap);
			if (!validateResult) {
				return false;
			}
			if (interruptPolicy === $.fn.virusDefine.interrupt_policy.remove_virus_takeover) {  // 杀除并接管的扫描策略为 4 - 扫描并查杀可疑文件，失败则跳过
				scanStrategyType = $.fn.virusDefine.scan_strategy_type.scan_kill_fail_skip;
			}
		} else if (takeoverStrategy === CONF.RECOVER_POLICY.DIRECT_TAKE) {  // 直接接管，中断策略和扫描策略都为-1
			scanStrategyType = -1;
			interruptPolicy = -1
		}
		let virus_scan_config_list = [{
			strategy_type: 0,
			recover_policy: takeoverStrategy,
			interrupt_policy: interruptPolicy,
			scan_strategy: scanStrategyType,
			all_timepoints_flag: 0,
			max_num_of_one_detection: 0,
			skip_application_group_flag: 0,
			virus_thread_num: virusScanThread,  // 扫描线程
			scan_entire_system_flag: virusScanEntireSystem,  // 扫描对象
			virus_lib_type: virusScanEngine,  // 扫描引擎
			virus_lib_type_name: virusScanEngineName,  // 扫描引擎名称
			specific_scan_target_map: specificScanTargetMap,  // 扫描目标/排除目标
		}]

		return {
			virus_scan_flag: 1,
			virus_scan_config_list,
			str: $.fn.getVirusConfigDes({
				virus_scan_flag: 1,
				virus_scan_config_list: virus_scan_config_list,
			}, 'takeover'),
		};
	};

})(jQuery);
