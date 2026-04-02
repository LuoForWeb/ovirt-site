var ObsRecover = function () {
    const STORAGE_DEVICE_TYPE = {
        FILE: 1,
        NAS: 2,
        HADOOP: 3,
        OBS: 4
    }; // 存储设备类型
    let ajaxData = {
        pointInfo: {
            agentUUID: '', // 代理端UUID
            ostype: '', // 操作系统类型
            pointUUID: '', // 时间点UUID
            taskUUID: '', // 任务UUID
            fileInfo: []
        },
        recoverInfo: {
            agentUUID: '',
            password: ''
        },
        typeInfo: {
            type: 1,
            high: {
                trasfer: {}
            },
            strategy: {
                start_time: '',
                type: 1
            }
        },
        highInfo: {
            dir_tree_recovery_flag: false,
            same_file_strategy: 1,
            link_file_pass_flag: false,
            permission_operate_flag: false
        },
        storeInfo: {},
        retry_strategy: {},
        safe_strategy: {
            worm_flag: 0, // 是否开启worm防护，默认关闭 
            worm_protection_time: 0, // worm防护保护期限
            virus_scan_flag: 0, // 是否开启病毒防护（对象存储模块不涉及，默认关闭）
            virus_scan_config_list: [], // 病毒防护配置参数（对象存储模块不涉及，默认为空）
            integrity_check_flag: 2, // 是否启用完整性检查
            integrity_check_config: { // 完整性检查参数
                check_strategy: -1, // 完整性检查策略：0 每周，1 每天，2 每次
                full_error_policy: -1, // 备份完备点异常策略：0 停止， 1 重做完备（默认）
                inc_error_policy: -1, // 增备点异常策略：0 停止，1 重做完备，2重做增备（默认）对象存储模块不涉及传-1
                recovery_error_policy: -1 // 恢复异常策略：0 停止（默认），1 继续正常恢复，2 恢复到无网络环境 对象存储备份不涉及传-1
            }
        }
    };

    let initTreeFlag = false; // 切换节点初始化时间点数flag
    let timepointTree, hostTree, hostInitFlag = false, pathTree;
    let vmOldName = [];
    let flagDay = false;
    let filenameFlag = true;
    let _pageSize = 20; // 文件每次请求条数
    let allpointlist;  // 保存一条链的时间点
    let allpointPsw;    //保存一条链的时间点的密码
    let _rBackupFileListDatatable, _recoverFileListDatatable; // 备份的文件列表和恢复文件列表
    let _filelistShow; // 最后一步验证恢复文件列表
    let _sclass = 's';	// 文件类型大小
    let _fileList = [];	// 可选择文件列表
    let _agentName, _agentIP, _pointTaskName, _pointName, _path = '', _pathArr = []; // 代理名称,代理IP,时间点任务名,时间点, 当前文件路径,路径头
    let  _md5Flag = 1, _md5High = '', _md5Low = ''; // md5标志,高八位MD5,第八位MD5,只用于显示更多
    let networkFlag = false; // 是否显示传输网络
    let initSpeedFlag = false;
    let speedList = [];
    let zTreeFile;
    let oldSetting = {};
    let oldTreeNode; // 用于保存搜索前的树
    let share_path = ''; // 共享路径,用于文件恢复到nas
    let req_num = 1000; // 恢复搜索，一次请求的搜索结果条数
    let global_thread_uuid = ''; // 恢复搜索线程uuid
    let current_total_num = 0,current_dir_num = 0,current_file_num = 0; // 搜索到的文件、文件夹、总数
    let srcAllNode; // 搜索之前的所有节点
    let clearNum = 1; // 点击了清除搜索就+1
    let nodeParamList = [];
    let clientTreeNodeParams = [];
    let timer = null;
    let newCount = 1;
    let recoverDeviceBelongOsType = ''; // 恢复目的地设备所属操作系统类型
    let selectedBackupVendor = 0; // 选择的备份时间点所属的对象存储vendor
    let recoverTargetVendor = 0; // 恢复目标所属的对象存储vendor（前提是选中的恢复客户端为对象存储）
    let APPLIANCE_AGENCY_HAS_CONFIGED = false; // 传输代理是否已配置标记
    let _daterangepicker_starttime = "";
	let _daterangepicker_endtime = "";
    let CURRENT_STORAGE_TYPE_IS_TAPE = false; // 标记当前选中的时间点所属的存储设备是否为磁带
    let externalPointUuid = $('#externalPointUuid').val();
	let externalTaskUuid = $('#externalTaskUuid').val();
	let externalItemUuid = $('#externalItemUuid').val();

    //<----------------------------- BEGIN STEP ONE BACKUP POINT ------------------------------------>

    const findParent = function(treeObj, node) {
        timepointTree.expandNode(node, true, false, false);

        if (!node.children){
			nodeParamList.push(node);
			timepointTree.expandNode(node,false,false,false);
		}

		let pNode = node.getParentNode();
		if (pNode != null){
			nodeParamList.push(pNode);
			findParent(timepointTree, pNode);
		}
    }

    const searchByTree = () => {
        timepointTree.hideNodes(timepointTree.transformToArray(timepointTree.getNodes()));
        let startTime = new Date(_daterangepicker_starttime).getTime(); // 开始时间的时间戳
		let endTime = new Date(_daterangepicker_endtime).getTime(); // 结束时间的时间戳
        let matchNodes = []; // 用于存储匹配时间范围的节点

        let nodes = timepointTree.transformToArray(timepointTree.getNodes());

        for (let i = 0; i < nodes.length; i++) {
			let node = nodes[i];
			// 检查 mode 属性是否存在（存在就是时间点），并且值在指定的时间范围内
			if (node.mode && new Date(node.timepoint).getTime() >= startTime && new Date(node.timepoint).getTime() <= endTime) {
				matchNodes.push(node);
			}
		}

        let value = $.trim($('#searchfs').val()); // 搜索关键词
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
        if (!value) {
            nodeParamList = [];
        } else {
            nodeParamList = timepointTree.getNodesByParamFuzzy('name', value);
        }

        // 连接搜索的和所勾选的
		let checkNode =timepointTree.getCheckedNodes();
		nodeParamList =nodeParamList.concat(checkNode);
		let nodeParamList1 = timepointTree.transformToArray(nodeParamList);
		for(let n in nodeParamList1){
			findParent(timepointTree,nodeParamList1[n]);
		}

        // 都不为空取搜索框和时间范围的交集
		if (value != "" && $('#daterangepicker').val() != '') {
			matchNodes = matchNodes.filter(item => nodeParamList.includes(item));
		} else if (value == "" && $('#daterangepicker').val() == '') {//相当于清空搜索
			$('#nosearchtips').hide();
			$('.fs_tree').show();
			timepointTree.showNodes(timepointTree.transformToArray(timepointTree.getNodes()));

			return;
		} else {//有一个为空的取全集
			matchNodes = [...matchNodes, ...nodeParamList];
		}

        if (matchNodes.length == 0) {
			$('.fs_tree').hide();
			$('#nosearchtips').show();
		} else {
			$('.fs_tree').show();
			$("#nosearchtips").hide();
		}

        let arr = new Array();
		for(let i = 0; i < matchNodes.length; i++){
			arr = $.merge(arr,matchNodes[i].getPath());    //找出节点的所有父节点（包括自己）
			if(matchNodes[i].children) {//展开过，有子节点，把子节点加进去
				arr = $.merge(arr,matchNodes[i].children);
			}
		}

        let firstlevel = [];
		let otherlevel = [];
		arr.forEach(item=>{
			if(item.level != 0) {
				otherlevel.push(item);
			}else {
				firstlevel.push(item);
			}
		});

        let newArr = timepointTree.transformToArray(otherlevel);//避免获取到第一层下的其他任务
		newArr = newArr.concat(firstlevel);
		timepointTree.showNodes($.unique(newArr));    //显示所有要求的节点及其路径节点
		//展开显示的所有节点
		newArr.forEach(item => {
			if(item.children) {
				timepointTree.expandNode(item, true);
			}
		});
    }

    /**
	 * 搜索指定时间范围内的备份时间点树
	 * @returns 
	 */
	const searchTimeRange = function () {
		if ($('#daterangepicker').val() == '') {
			searchByTree();
			return;
		}

		let params = {
            data_flag: false, // 是否为备份数据页面标记
            recovery_range: true, // 是否搜索时间范围标记
            startTime: _daterangepicker_starttime,
            endTime: _daterangepicker_endtime,
            storage_uuid: $('#storage_select').val() || ''
        }
		
        Metronic.blockUI({target: '.src-wrap__content', animate: true, cenrerY: true,});
        pAjaxRequest(params, '/api/v1/s3/search_timepoint', 'GET', (result) => {
            if (result.success) {
                if (result.data.length > 0) {
                    $('.fs_tree').show();
			        $("#nosearchtips").hide();

                    let pointNode = result.data;
                    let showNode = [];

                    pointNode.forEach(item => {
						// 在当前树节点中搜索异步请求得到的时间点，如果当前树不存在，加入showNode数组
						let nodeExist = timepointTree.getNodesByParam("id", item.id, null)[0];
						if(nodeExist == null) {
							showNode.push(item);
						}
					});

                    // 把showNode中时间点加到对应父节点下
					if(showNode.length != 0) {
						showNode.forEach(item => {
							// 先把完备点加进去，再放增量差异等，避免先搜增量差异等搜不到父节点
							if(item.type == 3) {
								let parentNode = timepointTree.getNodesByParam("id", item.pId, null)[0];
								if (parentNode){
									timepointTree.addNodes(parentNode, item, true);
								}
							}
						});
						showNode.forEach(item => {
							if(item.type == 4) {//增量差异等
								let parentNode = timepointTree.getNodesByParam("id", item.pId, null)[0];
								if(parentNode) {
									timepointTree.addNodes(parentNode, item, true);
								}
							}
						});
					}

                    searchByTree();
                } else {
                    $('.fs_tree').hide();
			        $('#nosearchtips').show();
                }
            }

            Metronic.unblockUI('.src-wrap__content');
        })
	}

    /**
     * 获取对象备份时间点 refreshFlag 是否重新刷新
     * @param {*} treeId
     * @param {*} treeNode
     * @param {*} refreshFlag
     * @param {*} expendFlag
     */
    const getSyncVcenterInfo = (treeId, treeNode, refreshFlag, expendFlag, chooseFlag = false) => {
        let storage_uuid = $('#storage_select').val() || '';
        let params = {
            job_uuid: treeNode.job_uuid,
            id: treeNode.id,
			refresh: refreshFlag,
            storage_uuid: storage_uuid,
            agent_uuid: treeNode.agent_uuid,
            recover_flag: false,
            data_flag: false
        }

        Metronic.blockUI({target: '.fs_tree',animate: true});
        pAjaxRequest(params, '/api/v1/s3/sync_backup_timepoint', 'GET', (result) => {
            if (result.success) {
                // 给时间点组赋值上所对应的对象存储组 verdor
                let treeData = result.data.map(i => { return { ...i, origin_vendor: parseInt(treeNode.origin_vendor) }});

                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, treeData, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);

                if(expendFlag){
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                }

                // 从备份数据跳转恢复页面，匹配时间点并勾选
                let targetNode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam('id', externalPointUuid);
                if(targetNode && chooseFlag){
                    timepointOnCheck('', treeId, targetNode);
                }

            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_BACKUP_DATA_FAILED, result.message);
            }

            Metronic.unblockUI('.fs_tree');
        });
    }

    /**
     * 节点展开异步添加时间点
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const nodeExpand = (treeId, treeNode) => {
        if(treeNode.children) return true;
        getSyncVcenterInfo(treeId, treeNode, false, false);
    }

    /**
     * 停止搜索
     * @returns
     */
    const stopSearch = () => {
        //停止搜索，开启界面超时检测
		// UIIdleTimeout.start();
		$('.ispinner').css("display","none");
		$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color": "#47b5a0", "color": "white"});
		$('#reco-search-btn').attr("name", "start");
		$('#reco-search-input').prop("disabled", false);
        // Metronic.unblockUI('#recoverFileTree');
		clearNum++;//点击停止，+1,让此次操作不能继续进行
		// 搜索任务创建成功了才发停止的消息给后台
		if (global_thread_uuid === '') return;

        let params = {
            thread_uuid: global_thread_uuid,
            timepoint_uuid: timepointTree.getCheckedNodes(true)[0].timepoint_uuid,
        }

        pAjaxRequest(params, '/api/v1/s3/stop_search', 'post', (result) => {
            if (!result.success) {
                UIToastr.showWarning(result.message);
            }
        })
    }

    /**
     * 计算指定字符在字符串中出现的次数
     * @param {*} str 
     * @param {*} charToSearch 
     * @returns 
     */
    const countCharOccurrences = (str, charToSearch) => {
        // 使用正则表达式和全局搜索（'g' 标志）  
        // 将字符转换为正则表达式时要确保对其进行转义（如果它是特殊字符）  
        const regex = new RegExp(charToSearch.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');  
        let matches = str.match(regex);  
        return matches ? matches.length : 0;  
    }

    /**
     * 获取搜索结果信息
     * @param {*} offset
     * @param {*} thread_uuid
     * @param {*} stopNum
     */
    const getSearchInfo = (offset,thread_uuid,stopNum) => {
        let params = {
            thread_uuid: thread_uuid,
            limit: req_num,
            offset: offset,
            timepoint_uuid: timepointTree.getCheckedNodes(true)[0].timepoint_uuid
        }

        pAjaxRequest(params, '/api/v1/s3/search_info', 'GET', (result) => {
            if (clearNum !== stopNum) return;

            if (result.success) {
                let buckets = 0, dirs = 0, fileNodes = [];

                if (parseInt(result.data.search_finish_flag) === 2) {
                    // 把搜到的节点加在根节点下面
                    if (result.data.filenode && result.data.filenode.length > 0) {
                        // 过滤掉搜索出是桶的那一项节点
                        buckets = result.data.filenode.filter(i => { return (parseInt(i.file_type) === 2 && i.id.endsWith('|'))}).length; // 桶个数
                        fileNodes = result.data.filenode.filter(i => { return !(parseInt(i.file_type) === 2 && i.id.endsWith('|'))}); // 过滤掉桶后的节点数组
                        fileNodes = fileNodes.map(i => {
                            return {
                                ...i,
                                icon: parseInt(i.file_type) === 2 && countCharOccurrences(i.id, '/') === 1 && i.id.endsWith('/') ? './img/s3/cunchutong.svg': i.icon,
                                name: parseInt(i.file_type) === 2 && countCharOccurrences(i.id, '/') === 1 && i.id.endsWith('/') ? i.name.replace('|', '').replace('/', '') : replaceBetweenStartEnd(i.name, '|', '/', '').replace('|', ''), // 桶节点图标换成桶icon，name中去除|
                                isParent: parseInt(i.file_type) === 2 ? true : false,
                                timepoint_uuid: timepointTree.getCheckedNodes(true)[0].timepoint_uuid
                            }
                        });

                        dirs = fileNodes.filter(i => { return parseInt(i.file_type) === 2 }).length;  // 文件夹个数

                        current_total_num += fileNodes.length;
                        current_dir_num += dirs;
                        current_file_num += parseInt(result.data.current_file_num);
                    }

                    $("#recoverFileTree").show();
					$("#seachdes .countnum").html(LANG.UI_FILE_FOUND + current_total_num + LANG.UI_FILE_SEARCH_FILE_NUM + current_file_num + LANG.UI_FILE_SEARCH_FLODER_NUM + current_dir_num + LANG.UI_PUBLIC_STRIP);

                    // 渲染树节点
					zTreeFile.addNodes(null, -1, fileNodes);

					//搜索结果数据过大提示信息
					if (current_total_num > 5000) {
						UIToastr.showWarning(LANG.UI_FILE_RECOVERY_SEARCH, LANG.UI_FILE_RECOVERY_SEARCH_LARGE_DATA_TIPS);
						stopSearch();
						return;
					}

					getSearchInfo(result.data.offset, thread_uuid, stopNum);
                } else {
                    $('.ispinner').css("display", "none");
					$('#reco-search-input').prop("disabled", false);
					$('#reco-search-btn').html(LANG.BILLING_SEARCH).css( {"background-color": "#47b5a0", "color": "white"} );
					$('#reco-search-btn').attr("name", "start");
					// Metronic.unblockUI('#recoverFileTree');
					if (current_total_num === 0) {
						$("#seachdes .countnum").html(LANG.UI_FILE_SEARCH_NO_DATA);
					}else {
						$("#recoverFileTree").show();
					}
                }
            } else {
                // 获取搜索结果失败，自动变成未搜索前的状态并报错
                UIToastr.showWarning(result.title, `${result.message}`);
                
				$('.ispinner').css("display", "none");
				$('#reco-search-input').prop("disabled", false);
				$('#reco-search-btn').html(LANG.BILLING_SEARCH).css( {"background-color": "#47b5a0", "color": "white"} );
				$('#reco-search-btn').attr("name","start");
				// OPREL(d);
            }
        });
    }

    /**
     * 开始搜索
     * @returns
     */
    const startSearch = () => {
        // 动态设置ztree的高度
        $('.recover-source-wrapper__ztree').css('height', 'calc(100% - 60px)');

        let inputname = $('#reco-search-btn').attr("name");

        if (inputname === "start") { // 开始搜索
            let searchval = $.trim($("#reco-search-input").val());
			if (!searchval) return;

            // 先创建搜索消息，创建成功发送获取搜索结果的消息
			let search_timepoint = timepointTree.getCheckedNodes(true)[0].timepoint_uuid;
			let md5_list = [];
			let search_mode = 1; // 1 -> 全文搜索  2 -> 选中部分目录目录搜索

            if (zTreeFile.getCheckedNodes(true).length !== 0) {
				let arr = zTreeFile.getCheckedNodes(true);

				arr.forEach(item => {
					// 根目录不是全选
					if (item.level === 0 && item.check_Child_State !== 2) {
						search_mode = 2;
					}

					// 勾选的是文件夹 或者 文件，且不是根目录
					if (item.check_Child_State === -1 || (item.level !== 0 && item.check_Child_State === 2)) {
						md5_list.push({
							"filepath": item.path,
							"md5_high": item.md5_high,
							"md5_low": item.md5_low
						});
					}
				});

				// 去除全选目录下的子目录
				for (let i = 0; i < md5_list.length; i++) {
					if (md5_list[i+1] != undefined && md5_list[i+1].filepath.indexOf(md5_list[i].filepath) !== -1) {
						md5_list.splice(i+1, 1);
						i--;
					}
				}
			}

            // 删除filepath,只保留MD5
			md5_list.map(item => delete item.filepath);

            // 各种显示样式
			$('#reco-search-input').prop("disabled", true);
			$('#reco-search-btn').attr("name", "stop").attr("disabled", true);
			$('#reco-search-btn').html(LANG.UI_JOB_STOP).css({"background-color": "red", "color": "white"});
			$('.ispinner').css("display", "inline-block");
			$("#seachdes").show();
			$("#seachdes .countnum").html(LANG.UI_FILE_SEARCH_INIT_TIPS);
            $("#recoverFileTree").hide();

            zTreeFile.checkAllNodes(false); // 取消全部勾选
			srcAllNode = [];
			srcAllNode = zTreeFile.transformToArray(zTreeFile.getNodes());
			srcAllNode.map(item=> {
				zTreeFile.removeNode(item);
			});

            let stopNum = clearNum; // 每次点击开始重新赋值，保证这一次能走下去

			let params = {
                search_mode: search_mode,
                md5_list: md5_list,
                path_name: searchval,
                timepoint_uuid: search_timepoint,
                req_total_num: 5000, // 总共请求多少条数据, 0不限制
                module_type: 3,
                sub_module_type: 4
            };

            pAjaxRequest(params, '/api/v1/s3/create_search_job', 'POST', (result) => {
                if (clearNum !== stopNum) return; // 让此次操作不能继续进行
                if (result.success) {
                    //如果在搜索，停止界面超时检测
					// UIIdleTimeout.stop();
					current_total_num = 0;
					current_dir_num = 0;
					current_file_num = 0;
					getSearchInfo(0, result.data.thread_uuid, stopNum)
					global_thread_uuid = result.data.thread_uuid;
					$('#reco-search-btn').attr("disabled",false);
					// Metronic.blockUI({target: '#recoverFileTree',animate: true});
                } else {
                    //获取搜索结果失败，自动变成未搜索前的状态并报错
                    UIToastr.showWarning(result.title, `${result.message}`);

					$('.ispinner').css("display", "none");
					$('#reco-search-input').prop("disabled", false);
					$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color": "#47b5a0", "color": "white"});
					$('#reco-search-btn').attr("name", "start");
					// OPREL(d);
                }
            })
        } else if (inputname === "stop") { // 停止搜索
            stopSearch();
        }
    }

    const handleData = (startIndex, count, treeNode, checkflag) => {
        let arr = treeNode.slice(startIndex, startIndex + count);
        // 截取需要处理的数据
		for (let i = 0; i < arr.length; i++) {
			if (checkflag) {
				zTreeFile.checkNode(arr[i], true, true, false);
			} else {
				zTreeFile.checkNode(arr[i], false, true, false);
			}
		}
		// 如果还有未处理的数据，则继续处理
		if (startIndex + count < treeNode.length) {
			handleData(startIndex + count, count, treeNode, checkflag);
		} else {
			Metronic.unblockUI(".addIt-list");
		}
    }

    /**
     * 一次勾选的条数
     * @param {*} allNodes
     * @returns
     */
    const getSelectNum = (allNodes) => {
        let num = 0;

		if (allNodes.length < 100) {
			num = 1;
		} else if (allNodes.length < 1000) {
			num = 50;
		} else if (allNodes.length < 5000) {
			num = 100;
		} else {
			num = 500;
		}

		return num;
    }

    /**
     * 全选
     * @returns
     */
    const selectAllResult = () => {
        let allNodes = zTreeFile.transformToArray(zTreeFile.getNodes());

		if(allNodes.length === 0) {
			return;
		}

		Metronic.blockUI({target: ".addIt-list",animate: true});
		handleData(0, getSelectNum(allNodes), allNodes, true);
    }

    /**
     * 取消全选
     * @returns
     */
    const cancelSelect = () => {
        Metronic.blockUI({target: ".addIt-list", animate: true});

		let allNodes = zTreeFile.transformToArray(zTreeFile.getCheckedNodes(true));
		if (allNodes.length === 0) {
			Metronic.unblockUI(".addIt-list");
			return;
		}

		handleData(0, getSelectNum(allNodes), allNodes, false);
    }

    /**
     * 清空搜索
     */
    const clearSearchVal = () => {
        // 动态设置ztree的高度
        $('.recover-source-wrapper__ztree').css('height', 'calc(100% - 34px)');
		$("#recoverFileTree").show();
		zTreeFile = $.fn.zTree.init($("#recoverFileTree"), oldSetting, oldTreeNode);
		$('#reco-search-input').val("");
		$("#seachdes").hide();
		$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color": "#47b5a0", "color": "white"});
		$('#reco-search-btn').attr("name","start");
		$('#reco-search-input').prop("disabled",false);
		stopSearch();
    }

    /**
     * 得到整条链
     * @returns
     */
    const getAllPoints = function (treeNode, password) {
        let info = {};
        info.allPoints = [];
        info.pointPasswords = [];
        if(treeNode.type == 4) {//增量差异点
            let pNode = treeNode.getParentNode();
            pNode.children.forEach(item=> {
                info.allPoints.push(item.point_uuid);
                info.allPoints.push(pNode.point_uuid);
                info.pointPasswords.push({
                    'point_uuid':item.point_uuid,
                    'password':password,
                });
                info.pointPasswords.push({
                    'point_uuid':pNode.point_uuid,
                    'password':password,
                });
            });
        }else if(treeNode.type == 3) {//完备点
            if(treeNode.children) {
                treeNode.children.forEach(item=> {
                    info.allPoints.push(item.point_uuid);
                    info.allPoints.push(treeNode.point_uuid);
                    info.pointPasswords.push({
                        'point_uuid':item.point_uuid,
                        'password':password,
                    });
                    info.pointPasswords.push({
                        'point_uuid':treeNode.point_uuid,
                        'password':password,
                    });
                });
            }else {
                info.allPoints.push(treeNode.point_uuid);
                info.pointPasswords.push({
                    'point_uuid':treeNode.point_uuid,
                    'password':password,
                });
            }
        }
        return info;
    }

    /**
     * 恢复文件目录树展开和节点初始化
     * @param treeId
     * @param treeNode
     * @param expandFlag
     */
    const setBakSonTree = (treeId, treeNode, expandFlag) => {
        let params = {
            pid: treeNode.path,
            timepoint_uuid: treeNode.timepoint_uuid,
            root_flag: 0,
            start: 0,
            limit: _pageSize,
            path: treeNode.path,
            md5_flag: treeNode.md5_flag,
            md5_high: treeNode.md5_high,
            md5_low: treeNode.md5_low,
            sclass: _sclass,
            sub_flag: true
        };

        let newParams = deepCloneObject(params);
        
        Metronic.blockUI({target: '.recover-source-wrapper__ztree', animate: true});
        pAjaxRequest(newParams, '/api/v1/s3/recover_dir', 'GET', (result) => {
            if (result.success) {
                let fileList = result.data.filelist;
                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, fileList, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);

                if (expandFlag) {
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                }

                if(treeNode.checked && treeNode.children) {
                    treeNode.children.forEach(item=>{
                        $.fn.zTree.getZTreeObj(treeId).checkNode(item, true);
                    });
                }
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_RECOVER_DIR_DATA_FAILED, result.message);
            }

            Metronic.unblockUI('.recover-source-wrapper__ztree');
        });
    }

    /**
     * 恢复文件目录树节点展开
     * @param treeId
     * @param treeNode
     * @returns {boolean}
     */
    const fileNodeExpand = function (treeId,treeNode) {
        if (treeNode.children) return true;
		setBakSonTree(treeId, treeNode, false);
    }

    /**
     * 加载更多
     * @param {*} treeId
     * @param {*} pNode
     */
    const getMoreTree = (treeId, pNode) => {
        //判断父节点下的子节点是否全选
		let checkeFlag = pNode.getParentNode().check_Child_State === 2 ? true : false;
		let params = {
            pid: pNode.pId,
            timepoint_uuid: pNode.timepoint_uuid,
            root_flag: 0,
            start: pNode.next_index,
            limit: _pageSize,
            path: pNode.pId,
            md5_flag: pNode.md5_flag,
            md5_high: pNode.md5_high,
            md5_low: pNode.md5_low,
            sclass: _sclass,
            sub_flag: true
        };

        Metronic.blockUI({target: '.recover-source-wrapper__ztree',animate: true});
        pAjaxRequest(params, '/api/v1/s3/recover_dir', 'GET', (result) => {
            if (result.success) {
                let fileList = result.data.filelist;
                if (checkeFlag) {
                    fileList = fileList.length > 0 ? fileList.map(item => {
                        return {
                            ...item,
                            checked: true
                        }
                    }) : [];
                }
                $.fn.zTree.getZTreeObj(treeId).removeNode(pNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(pNode.getParentNode(), fileList, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_RECOVERY_LOAD_MORE_FAILED, result.message);
            }

            Metronic.unblockUI('.recover-source-wrapper__ztree');
        });
    }

    /**
     * 恢复文件目录树节点click
     * @param treeId
     * @param treeNode
     */
    const fileNodeClick = function (treeId, treeNode) {
        if (treeNode.more) {//加载更多
			getMoreTree(treeId, treeNode);
			return;
		}
		if (treeNode.isParent) {
			fileNodeExpand(treeId,treeNode);
			zTreeFile.expandNode(treeNode, true);
		}
    }

    /**
     * 设置恢复文件目录树
     * @param fileList
     * @param treeNode
     */
    const setBackupFileTree = (fileList, treeNode) => {
        if (!Array.isArray(fileList)) {
            return;
        }

        //第一次请求目录时加一层父节点 可支持全选
        fileList.push({
			"pId": 0,
			"name": treeNode.agent_name,
			"title": treeNode.agent_name,
			"isParent": true,
			"open": true,
			"icon": treeNode.ostype == "Windows" ? "./img/os/Windows.png" : "./img/s3/cloud-platform.svg", // TODO:区分不同对象存储类型
			"type": "0"
		});

        let setting = {
			check: {
				enable: true,
				nocheckInherit: false,
			},
			data: {
				simpleData: {
					enable: true,
				},
				key: {
					title: "title"
				}
			},
			callback: {
				beforeClick: fileNodeClick,
				// onCheck: fileNodeCheck,
				// beforeCheck:filebeforeCheck,
				beforeExpand: fileNodeExpand,
			},
			view: {
				dblClickExpand: false,
			},
		};

		zTreeFile = $.fn.zTree.init($("#recoverFileTree"), setting, fileList);
		$('#recoverFileTree').show();
		$('.reco-search-group').show();
		oldTreeNode = fileList;
		oldSetting = setting;
    }

    /**
     * 判断某个字符在字符串中出现的次数
     * @param {*} str 
     * @param {*} char 
     */
    const countCharacterOccurrences = (str, char) => {
        let count = 0;  
        for (let i = 0; i < str.length; i++) {  
            if (str[i] === char) {  
                count++;  
            }  
        }

        return count;
    }

    /**
     * 获取恢复文件列表
     * @param {*} treeNode
     */
    const getRecoverDir = (treeNode) => {
        //密码输入正确
		_pathArr = [];
        ajaxData.pointInfo = {
            agentUUID: treeNode.agent_uuid,
            pointUUID: treeNode.point_uuid,
            taskUUID: treeNode.taskuuid,
            nodeuuid: treeNode.nodeuuid,
            ostype: treeNode.os_type
        }

		//用于最后一步显示用
		_agentName = treeNode.agent_name;
		_agentIP = treeNode.agent_ip;
		_pointTaskName = treeNode.task_name;
		_pointName = treeNode.name;
		_fileList = [];

        //		1.初始展示[timepoint_uuid, 1, 0, N, '', 0, '', '']
		// subFlag php那边是否截取路径字符串  初始不截取  第二次以及以后截取
        let params = {
            pid: 0,
            timepoint_uuid: treeNode.point_uuid,
            root_flag: 1,
            start: 0,
            limit: _pageSize,
            path: '',
            md5_flag: 0,
            md5_high: '',
            md5_low: '',
            sclass: _sclass,
            sub_flag: false
        };

        $('#step1tips').hide();
        $('.recover-source-wrapper').show();

        Metronic.blockUI({target: '.recover-source-wrapper__ztree', animate: true});

        pAjaxRequest(params, '/api/v1/s3/recover_dir', 'GET', (result) => {
            if (result.success) {
                let treeData = result.data.filelist.map(i => {
                    return { 
                        ...i, 
                        name: i.name.indexOf('/') === -1 ? replaceBetweenStartEnd(i.path, '|', '/', '').replace('|', '') : replaceBetweenStartEnd(i.name, '|', '/', '').replace('|', ''),
                        title: i.name.indexOf('/') === -1 ? replaceBetweenStartEnd(i.path, '|', '/', '').replace('|', '') : replaceBetweenStartEnd(i.name, '|', '/', '').replace('|', ''),
                        origin_vendor: treeNode.origin_vendor,
                        icon: countCharacterOccurrences(i.path, '/') === 1 && i.path.endsWith('/') ? './img/s3/cunchutong.svg' : i.icon,
                        iconClose: countCharacterOccurrences(i.path, '/') === 1 && i.path.endsWith('/') ? '' : i.iconClose,
                        iconOpen: countCharacterOccurrences(i.path, '/') === 1 && i.path.endsWith('/') ? '' : i.iconOpen
                    }
                });
                
                setBackupFileTree(treeData, treeNode);
            } else {
                $('#step1tips').show();
                $('.recover-source-wrapper').hide();
                UIToastr.showWarning(result.message, '');
            }

            Metronic.unblockUI('.recover-source-wrapper__ztree');
        });
        timepointTree.expandNode(treeNode, true);
    }

    /**
     * 时间点onCheck
     * @param {*} treeId
     * @param {*} treeNode
     */
    const timepointOnCheck = (e, treeId, treeNode) => {
        if (treeNode.chkDisabled) {
            return;
        }
        CURRENT_STORAGE_TYPE_IS_TAPE = treeNode.storage_type === CONF.BD_STORAGE_TYPE.TAPE ? true : false;

        $('#recoverFileTree').hide();
		$('.reco-search-group').hide();
        let allNodes = timepointTree.getCheckedNodes(true);

        for (let i = 0; i < allNodes.length; i++){
			timepointTree.checkNode(allNodes[i], false, false, false);
		}

        timepointTree.checkNode(treeNode, true, false, false);
        // 切换时间点，清空筛选停止搜索
		clearSearchVal();
		global_thread_uuid = '';

        // 是否手动加密
        if ('encrypted_flag' in treeNode && treeNode.encrypted_flag && 'password_auto_flag' in treeNode && !treeNode.password_auto_flag) {
            // 判断是否和之前点击过的是一条链的
            if ($.inArray(treeNode.point_uuid, allpointlist) === -1) {
                bootbox.prompt({
					title: LANG.UI_VM_INPUT_DB_ENCRY_PWD,
					inputType: 'password',
					callback: function (result) {
						//同步ajax校验密码
						if (result == null) return;
						let checkflag = true;
						let params = {
                            timepoint_uuid: treeNode.timepoint_uuid,
                            passwd: btoa($.trim(result))
                        };

						ajaxData.recoverInfo.password = btoa($.trim(result));

                        // 检验对象数据加密密码的正确性
                        pAjaxRequest(params, '/api/v1/s3/check_encrypt', 'post', (result) => {
                            if (result.success) {
                                if (result.data.flag) {
                                    let pointInfo = getAllPoints(treeNode, ajaxData.recoverInfo.password);
									allpointlist = pointInfo['allPoints'];
									allpointPsw = pointInfo['pointPasswords'];
									getRecoverDir(treeNode);
                                } else {
                                    checkflag = false;
                                }
                            } else {
                                UIToastr.showWarning(LANG.UI_OBS_PASSWORD_VERIFY_FAILED, result.message);
                            }
                        });

                        if (!checkflag) return false;
                        return true;
					}
				});
            } else {
                allpointPsw.forEach(item => {
					if(treeNode.timepoint_uuid == item.point_uuid) {
						ajaxData.recoverInfo.password = item.password;
					}
				});

                getRecoverDir(treeNode);
            }
        } else {
            ajaxData.recoverInfo.password = '';
            getRecoverDir(treeNode);
        }
    }

    /**
     * 备份时间点树节点选择
     * @param treeId
     * @param treeNode
     */
    const nodeSelect = (treeId, treeNode) => {
        if (3 !== treeNode.type && 4 !== treeNode.type) {
			timepointTree.expandNode(treeNode, true);
			//异步加载时间点
			nodeExpand(treeId, treeNode);
			return;
		}
		timepointOnCheck(event, treeId, treeNode);
    }

    const addTimepointHoverDom = function (treeId, treeNode) {
        if (treeNode.type !== 3 && treeNode.type !== 4) {//不是时间点
            return;
        }

        if ($(`#${treeNode.tId}_${treeNode.point_uuid}`).length) {
            return;
        }

        let sObj = $(`#${treeNode.tId}_span`);
        sObj.after(`<span id="${treeNode.tId}_${treeNode.point_uuid}"><i class='viconfont vicon-Frame11'></i></span>`);
        // 注册点击事件
        $(`#${treeNode.tId}_${treeNode.point_uuid}`).on("click", (ev) => {
            ev.stopPropagation();  // 阻止click事件向上冒泡
            $('#obs_recover_content').initPointDetailDrawer({timepoint_uuid: treeNode.point_uuid});
        });
    }

    const removeTimepointHoverDom = function (treeId, treeNode) {
        if (treeNode.type !== 3 && treeNode.type !== 4) {//不是时间点
            return;
        }
        $(`#${treeNode.tId}_${treeNode.point_uuid}`).off().remove();
    }

    /**
     * 初始化时间点树
     */
    const initPointTree = function() {
        let params = {
            data_flag: false,
            storage_uuid: $('#storage_select').val() || ''
        }

        Metronic.blockUI({target: '.src-wrap__content', animate: true, cenrerY: true,});
        pAjaxRequest(params, '/api/v1/s3/recover_data_tree', 'GET', (result) => {
            if (result.success) {
                let zNodes = result.data;
                if (zNodes.length === 0){
                    $("#nopointtips").show();
                    $('.fs_tree').hide();
                    $("#agent_point_tree").hide();

                    Metronic.unblockUI('.src-wrap__content');
                    return;
                } else {
                    $("#nopointtips").hide();
                    $('.fs_tree').show();
                    $("#agent_point_tree").show();
                }

                let setting = {
                    check: {
                        enable: true,
                    },
                    data: {
                        simpleData: {
                            enable: true
                        },
                        key: {
                            title: "title"
                        }
                    },
                    callback: {
                        beforeClick: nodeSelect,
                        beforeExpand: nodeExpand,
                        onCheck: timepointOnCheck,
                    },
                    view: {
                        nameIsHTML: true,
                        addHoverDom: addTimepointHoverDom,
                        removeHoverDom: removeTimepointHoverDom
                    }
                }

                timepointTree = $.fn.zTree.init($("#agent_point_tree"), setting, zNodes);
                // 从备份数据跳转恢复页面，展开对象下的时间点
                let targetNode = timepointTree.getNodeByParam('id', externalItemUuid + '_' + externalTaskUuid);
                // 第一次初始化，且有目标节点
                if(targetNode && !initTreeFlag){
                    // 异步获取时间点
                    getSyncVcenterInfo('agent_point_tree',targetNode, true, true, true)
                }
                initTreeFlag = true;
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_BACKUP_TASK_DATA_FAILED, result.message);
            }

            Metronic.unblockUI('.src-wrap__content');
        })
    }

    const storageSelectChange = () => {
        initTreeFlag = false;
		$('#recoverFileTree').hide();
		initPointTree();
		zTreeFile.checkAllNodes(false);
    }

    /**
     * 获取恢复的节点列表
     */
    const initPointShowType = () => {
        pAjaxRequest({}, '/api/v1/storages/type', "GET", (result) => {
			if (result.success) {
				let data = result.data;
				let storageSelect = $('#storage_select');
				storageSelect.empty();

				for (let i = 0; i < data.length; i++) {
					let option = $("<option>").text(data[i].text).val(data[i].storageid);
					storageSelect.append(option);
				}

				initPointTree();
			} else {
				UIToastr.showWarning(LANG.UI_OBS_GET_STORAGE_FAILED, res.message);
			}
		})

        //绑定事件
        $('#storage_select').on('change', storageSelectChange);
    }

    /**
     * 搜索备份时间点树
     */
    const searchFS = () => {
        searchTimeRange();
    }

    const showStep1 = () => {
        // 设置任务名
        if ($.trim($('#jobname').val()) === "") {
            pAjaxRequest({job_name: LANG.UI_OBS_RECOVER_TASK_DEFAULT_NAME}, '/api/v1/s3/default_name', "GET", (result) => {
                if (result.success) {
                    $('#jobname').val(result.data);
                }
            })
        }

        // 设置时间点信息
        $('.srcagentinfo').html(_agentName);
        $('.srctaskname').html(_pointTaskName);
        $('.srctimepoint').html(_pointName);

        // 设置文件列表
        let showStr = '';
        let allCheckedNode = [];

        // 得到所有勾选状态是全选中的节点
        for(let i= 0; i < ajaxData.pointInfo.fileInfo.length; i++){
            //check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
            if (ajaxData.pointInfo.fileInfo[i].type != 0 && ajaxData.pointInfo.fileInfo[i].check_Child_State == 2 || ajaxData.pointInfo.fileInfo[i].check_Child_State == -1) {
                allCheckedNode.push(ajaxData.pointInfo.fileInfo[i]);
            }
        }

        // 过滤掉重复的
        for(let m = 0; m < allCheckedNode.length; m++) {
            if (allCheckedNode[m].btype != 1) {// 磁盘或文件夹
                for(let n = 0; n < allCheckedNode.length; n++) {
                    let str = allCheckedNode[n].pId == null ? '' : allCheckedNode[n].pId;
                    if (str.includes(allCheckedNode[m].path) && allCheckedNode.indexOf(allCheckedNode[n]) != -1) { // 判断全选的文件夹下是否还有文件  有则从数组中删除
                        allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
                        n--;
                    }
                }
            }
        }
        
        // 设置文件显示列表和提交的列表
        ajaxData.pointInfo.fileInfo = [];
        allCheckedNode.forEach(item => {
            selectedBackupVendor = item.origin_vendor;

            ajaxData.pointInfo.fileInfo.push([item.btype, item.path, item.name, item.md5_high, item.md5_low]); // 组装发送给后台的文件信息
            showStr += replaceBetweenStartEnd(item.path, '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '<br>';
        });

        // 判断恢复选择的节点是否是搜索出来的
        if (allCheckedNode[0].search_node != undefined) {
            ajaxData.distinct_flag = 1;
        } else {
            ajaxData.distinct_flag = 2;
        }

        $('#filelist').html(showStr);

        // 初始化线程数描述
        initHighStrategyDes();

        // 恢复到文件客户端且文件客户端tree未被初始化时才调接口默认获取文件客户端树
        if (parseInt($('#deviceSelect').val()) === 1 && $('#host_tree').children().length === 0) {
            initFileClientTree();
        }
    }

    /**
     * 步骤一 选择备份点 校验
     * @returns {boolean}
     */
    const step1Valid = () => {
        // 检查时间点是否勾选
        if (!ajaxData.pointInfo.pointUUID) {
            UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_TIMEPOINT_TITLE, LANG.UI_RECOVERY_FILE_NO_TIMEPOINT_VALUE);
            return false;
        }

        // 检查恢复文件列表是否勾选
        ajaxData.pointInfo.fileInfo = zTreeFile.getCheckedNodes(true);
        if (ajaxData.pointInfo.fileInfo.length === 0) {
            UIToastr.showWarning(LANG.UI_OBS_NO_SELECT_RECOVER_OBJ, LANG.UI_OBS_NO_REC_OBJ_TIPS);
            return false;
        }

        if (CURRENT_STORAGE_TYPE_IS_TAPE) { // 恢复的时间点属于磁带备份任务时，隐藏传输线程和安全策略
            $('.transmit-thread-form-group').hide();
            $('#recoveryThreadNum').val(1);

            $('.safetyLi').hide();
            $('#tab_safety').addClass('display-none');
            $('.confirm-config-safety-form-group').addClass('display-none');
        } else {
            $('.transmit-thread-form-group').show();
            $('#recoveryThreadNum').val(3);

            // 未授权完整性校验隐藏其配置
            if (!CONF.FUNCTIONS.includes('integrity')) {
                $('.safetyLi').hide();
                $('#tab_safety').addClass('display-none');
                $('.confirm-config-safety-form-group').addClass('display-none');
            } else {
                $('.safetyLi').show();
                $('#tab_safety').removeClass('display-none');
                $('.confirm-config-safety-form-group').removeClass('display-none');
            }
        }

        stopSearch(); // 点击下一步停止搜索
        showStep1();

        let integrityCheckFlag = timepointTree.getCheckedNodes(true)[0].integrity_check_flag;
        $('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.FS, 0 , !integrityCheckFlag );

        return true;
    }

    //<----------------------------- END STEP ONE BACKUP POINT -------------------------------------->


    //<----------------------------- BEGIN STEP TWO RECOVER TARGET ---------------------------------->

    /**
     * 步骤二：恢复目标 - 搜索 文件客户端/NAS设备/对象存储/Hadoop 树
     * @param {*} keyword 关键词
     */
    const initHostTree = (keyword) => {
        let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备

        switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
                initFileClientTree(keyword);
                break;
            case STORAGE_DEVICE_TYPE.NAS: // NAS设备
                initNasClientTree(keyword);
                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // Hadoop集群
                initHadoopClientTree(keyword);
                break;
            case STORAGE_DEVICE_TYPE.OBS: // 对象存储
                initObsClientTree(keyword);
                break;
            default:
                break;
        }
    }

    /**
     * 步骤二：恢复目标 - 文件客户端/NAS设备/对象存储/Hadoop 树 节点选择
     * @param treeId
     * @param treeNode
     */
    const hostNodeSelect = (treeId, treeNode) => {
        recoverDeviceBelongOsType = treeNode.os_type;

        if(treeNode.type === 1 || treeNode.type === 'obsGroup') {
			hostTree.expandNode(treeNode);
			return;
		}

		if(treeNode.chkDisabled) return;

		hostTree.checkNode(treeNode, !treeNode.checked, false, true);
    }

    /**
     * 步骤二：恢复目标 - 恢复路径 - 初始化恢复路径树
     * @param {*} treeData
     * @param {*} treeNode
     */
    const setAgentPathTree = (treeData) => {
        newCount = 1;

        let setting = {
            view: {
                addHoverDom: addHoverDom,
                removeHoverDom: removeHoverDom,
                selectedMulti: false
            },
            edit: {
                enable: true,
                drag:false,//禁用拖拽
                editNameSelectAll: true,
                showRenameBtn: showRenameBtn,
                showRemoveBtn: showRemoveBtn,
                removeTitle: LANG.BILLING_DELETE,
                renameTitle: LANG.UI_FILE_EDIT
            },
            check: {
                enable: true,
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            callback: {
                beforeClick: pathNodeSelect,
                onCheck: pathOnCheck,
                beforeExpand: pathNodeExpand,
                beforeEditName: beforeEditName,
                onRemove: onRemove, //移除事件
                onRename: onRename,
            }
        };

        pathTree = $.fn.zTree.init($("#path_tree"), setting, treeData);
    }

    /**
     * 步骤二：恢复目标 - 获取文件客户端下的目录树
     * @param treeNode
     * @returns {boolean}
     */
    const initAgentPathTree = (treeNode) => {

        filenameFlag = true;

        let params = {
            agent_uuid: treeNode.uuid,
            offset: 0,
            limit: _pageSize,
            filename: '',
            dir: '',
            pid: 0,
            startAfter: ''
        }
        Metronic.blockUI({target: '#path_tree',animate: true});

        pAjaxRequest(params, '/api/v1/files/recover_path_tree', 'GET', (result) => {
            if (result.success) {
                setAgentPathTree(result.data);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FILE_FAILED, result.message);
            }

            Metronic.unblockUI('#path_tree');
        });
    }

    /**
     * 步骤二：恢复目标 - 获取NAS设备下的目录树
     * @param treeNode
     */
    const initNasFilePathTree = (treeNode) => {
        filenameFlag = true;
        share_path = treeNode.path;
        
        let params = {
            agent_uuid: treeNode.agent_uuid,
            share_path: treeNode.path,
            offset: 0,
            limit: _pageSize,
            filename: '',
            dir: treeNode.path,
            pid: 0,
            startAfter: ''
        }

        Metronic.blockUI({target: '#path_tree',animate: true});

        pAjaxRequest(params, '/api/v1/nas/recover_path_tree', 'GET', (result) => {
            if (result.success) {
                result.data.push({
                    "id": 0,
                    "name": treeNode.name,
                    "title": "/",
                    "dir_path": "/",
                    "isParent": true,
                    "open": true,
                    "icon": "./img/fs/wenjianjia.png",
                    "iconClose": "./img/fs/wenjianjia.png",
                    "iconOpen": "./img/fs/wenjianjiaopen.png",
                    "type": "0",
                    "path": treeNode.path,
                    "new_dir_create": 2,
                    "parentFlag": true,
                    "noRemoveBtn": true,
                    "noEditBtn": true,
                    "event_type": "nas"
                });

                setAgentPathTree(result.data);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_NAS_FILE_FAILED, result.message);
            }

            Metronic.unblockUI('#path_tree');
        })
    }

    /**
     * 步骤二：恢复目标 - 获取对象存储下的目录树
     * @param treeNode
     */
    const initObsFilePathTree = (treeNode) => {
        filenameFlag = true;
        let params = {
            agent_uuid: treeNode.id,
            offset: 0,
            limit: _pageSize,
            filename: '',
            dir: '',
            pid: 0,
            code_type: 2,
            startAfter: ''
        }

        Metronic.blockUI({target: '#path_tree',animate: true});

        pAjaxRequest(params, '/api/v1/s3/recover_path_tree', 'GET', (result) => {
            if (result.success) {
                if (result.data && result.data.length > 0) {
                    let treeData = result.data.map(item => {
                        return {
                            ...item,
                            name: item.name.split('|')[0],
                            title: item.title.split('|')[0],
                            dir_path: item.dir_path + '/',
                            vendor: treeNode.vendor
                        }
                    });

                    setAgentPathTree(treeData);
                }
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_OBS_FILE_FAILED, result.message);
            }

            Metronic.unblockUI('#path_tree');
        })
    }

    /**
     * 步骤二：恢复目标 - 获取hadoop集群下的目录树
     * @param {*} treeNode 
     */
    const initHdoopFilePathTree = (treeNode) => {
        filenameFlag = true;

        let params = {
            hadoop_cluster_id: treeNode.uuid, 
            limit_count: _pageSize, 
            fetch_root_dir: treeNode.getParentNode() ==  undefined? "/": treeNode.getParentNode().filepath,
            start_after: '', //上一个节点路径
            edit_flag: false
        };

        Metronic.blockUI({target: '#path_tree',animate: true});

        pAjaxRequest(params, '/api/v1/hadoop/recover_path_tree', 'GET', (result) => {
            if (result.success) {
                let treeData = result.data.file_nodes;

                treeData.push({
                    "id": '/',
                    "name": '(/)',
                    "title": "/",
                    "dir_path": "/",
                    "isParent": true,
                    "open": false,
                    "icon": "./img/fs/wenjianjia.png",
                    "iconClose": "./img/fs/wenjianjia.png",
                    "iconOpen": "./img/fs/wenjianjiaopen.png",
                    "type": "0",
                    "path": treeNode.path,
                    "parentFlag": true,
                    "noRemoveBtn": true,
                    "noEditBtn": true,
                    "event_type": "hadoop_cluster",
                    "clusteruuid": treeNode.uuid
                })

                setAgentPathTree(treeData);
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_RECOVER_HADOOP_CLUSTER_DIR_FAILED, result.message);
            }

            Metronic.unblockUI('#path_tree');
        })
    }

    /**
     * 节点传输网络DIV是否显示标记
     * @param agent
     * @returns {boolean}
     */
    const getNetworkFlag = (agent) => {
        for(let i= 0; i < agent.length; i++){
            //客户端连接服务端
            if (agent[i].net_model == 2){
                networkFlag = true;
            }
        }

        return networkFlag;
    }

    /**
     * 初始化节点传输网络列表
     */
    const initNetworkList = () => {
        let data = {
            nodes_uuid: ajaxData.pointInfo.nodeuuid
        }

        pAjaxRequest(data, '/api/v1/nodes/network_card', 'GET', (result) => {
            if (result.success) {
                $('#transferNetwork').empty();
                let networkList = result.data;

                if (networkList && networkList.length > 0) {
                    networkList.forEach(item => {
                        let name = `${item.ip}:${item.port}`;

                        if (item.alias_name) {
                            name += `(${item.alias_name})`;
                        }

                        let option = `<option value="${item.network_uuid}">${name}</option>`;
                        $('#transferNetwork').append(option);
                    });
                }
            }
        })
    }

    const initNetwork = (treeNode) => {
        let nodes = [];
        nodes.push(treeNode);
        networkFlag = false;
        networkFlag = getNetworkFlag(nodes);

        //初始化传输网络
        if(networkFlag){
            $('.transfernetworkDiv').show();
            initNetworkList();
        }else{
            $('.transfernetworkDiv').hide();
        }
    }

    /**
     * 步骤二：恢复目标 - 文件客户端/NAS设备/对象存储/Hadoop 树 节点选中
     * @param e
     * @param treeId
     * @param treeNode
     */
    const hostOnCheck = (e, treeId, treeNode) => {
        if (treeNode.chkDisabled) return;

        if (treeNode.checked) {
            hostTree.checkAllNodes(false);
            hostTree.checkNode(treeNode,true,false,false);

            if (parseInt($('#pathtype').val()) === 2) { // 勾选且手动选择恢复路径则显示 选择路径
                $('#selectPath').show();
                $('#pathtreediv').show();
                // 获取选择客户端的高度后动态计算选择路径的fom-group的高度，以适应分辨率变化
                let clientTreeFormGropHeight = $('.form-group-group-client-tree').height() + 15;
                // 162: (form-group高度 34 + margin-bottom高度 15) * 3 + form-recover-path mb15px
                $('.form-recover-path').css('height', `calc(100% - ${clientTreeFormGropHeight + 162}px)`);
    
                switch (treeNode.event_type) {
                    case 'agent': // 文件客户端
                        initAgentPathTree(treeNode);
                        ajaxData.recoverInfo.cross_platform_transform = 1; // 是否跨平台
                        break;
                    case 'nas': // nas设备
                        initNasFilePathTree(treeNode);
                        ajaxData.recoverInfo.cross_platform_transform = 1; // 是否跨平台
                        break;
                    case 'obs_agent': // 对象存储
                        initObsFilePathTree(treeNode);
                        ajaxData.recoverInfo.cross_platform_transform = 2; // 是否跨平台
                        break;
                    case 'hadoop_cluster': // hadoop
                        initHdoopFilePathTree(treeNode);
                        ajaxData.recoverInfo.cross_platform_transform = 1; // 是否跨平台
                        break;
                    default:
                        break;
                }
            }
        } else { // 未勾选不显示 选择路径
            $('#selectPath').hide();
            $('#pathtreediv').hide();

            if (treeNode.event_type === 'obs_agent') {
				$('#pathtype option[value=1]').show();
			} else {
				$('#pathtype option[value=1]').hide();
			}
        }

        

        // 初始化传输网络
        initNetwork(treeNode);
    }

    /**
     * 步骤二：恢复目标 - 初始化 文件客户端/NAS设备/对象存储/Hadoop 树
     * @param zNodes 树节点数据
     */
    const setHostTree = (zNodes) => {
        let setting = {
            check: {
                enable: true,
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            callback: {
                beforeClick: hostNodeSelect,
                onCheck: hostOnCheck,
            }
        };

        hostTree = $.fn.zTree.init($("#host_tree"), setting, zNodes);
    }

    /**
     * 恢复路径树节点展开
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const pathNodeExpand = function(treeId, treeNode) {
        if (treeNode.children) return true;

        let agentuuid = treeNode.event_type === 'hadoop_cluster' ? treeNode.clusteruuid : treeNode.uuid;
        if (!agentuuid)	return false;

        let dir = treeNode.dir_path;

        if (treeNode.title === "/" && treeNode.event_type === undefined) { // nas新增的根目录
            dir = treeNode.id;
        }

        let params = {};
        let api = '';
        switch (treeNode.event_type) {
            case 'agent':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/files/recover_path_tree';
                break;
            case 'nas':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/nas/recover_path_tree';
                break;
            case 'obs_agent':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/s3/recover_path_tree';
                break;
            case 'hadoop_cluster':
                params = {
                    hadoop_cluster_id: agentuuid,
                    limit_count: _pageSize,
                    fetch_root_dir: dir,
                    code_type: treeNode.code_type,
                    start: 0,
                    start_after: '',
                    edit_flag: false
                }
                api = '/api/v1/hadoop/recover_path_tree';
                break;
            default:
                break;
        }

        Metronic.blockUI({target: '#path_tree', animate: true});
        pAjaxRequest(params, api, 'GET', (result) => {
            if (result.success) {
                let treeData = treeNode.event_type === 'hadoop_cluster' ? result.data.file_nodes : result.data
                pathTree.addNodes(treeNode, treeData, true);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_RECOVERY_GET_SUBDIR_FAILED, result.message);
            }

            Metronic.unblockUI('#path_tree');
        })
    }

    const beforeEditName = function(treeId, treeNode) {
        pathTree.selectNode(treeNode);
        pathTree.editName(treeNode);
        return false;
    }

    /**
     * 恢复路径树添加悬停节点
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const addHoverDom = function(treeId, treeNode) {
		let sObj = $("#" + treeNode.tId + "_span");

		if (treeNode.more || treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length > 0) return;

		if(!filenameFlag) return;

		let addStr = '<span class="button add" id="addBtn_'+ treeNode.tId + '" title="'+ LANG.UI_FILE_NEW_DIR +'" onfocus="this.blur();"></span>';

		sObj.after(addStr);

		let btn = $("#addBtn_"+treeNode.tId);

		// 点击添加节点
		if (btn) btn.bind("click", function(){
			if(treeNode.noRemoveBtn) {//不是新增的节点
				pathNodeExpand(treeId, treeNode);
			}

			if(treeNode.children != undefined && treeNode.children.length != 0) {
				pathTree.addNodes(
					treeNode, 
					-1, 
					{
						id: (treeNode.id + '_' + treeNode.children.length),
						icon: './img/fs/wenjianjia.png',
						iconOpen: "./img/fs/wenjianjiaopen.png", 
						iconClose: "./img/fs/wenjianjia.png",
						title: '', 
						isParent:false, 
						pId: treeNode.id,
						name: 'recovery-destination' + (newCount++),
						isnew: true,
						new_dir_create: CONF.FLAG.SET,
						code_type: treeNode.code_type
					},
					false);
				// let newnode = pathTree.getNodeByParam("id", treeNode.id+'_' + 0, null);
				let newnode = pathTree.getNodeByParam("isnew", true, null);
				beforeEditName(treeId,newnode);
				newnode.isnew = false;
			}else {
				pathTree.addNodes(
					treeNode, 
					-1, 
					{
						id: (treeNode.id + '_' + 0),
						icon: './img/fs/wenjianjia.png',
						iconOpen: "./img/fs/wenjianjiaopen.png", 
						iconClose: "./img/fs/wenjianjia.png", 
						title: '', 
						isParent: false, 
						pId: treeNode.id, 
						name: 'recovery-destination' + (newCount++),
						isnew: true,
						new_dir_create: CONF.FLAG.SET,
						code_type: treeNode.code_type
					},
					false);
				let newnode = pathTree.getNodeByParam("isnew", true, null);
				beforeEditName(treeId,newnode);
				newnode.isnew = false;
			}
			filenameFlag = false;
			return false;
		});
	};

    /**
     * 恢复路径树移除悬停节点
     * @param {*} treeId
     * @param {*} treeNode
     */
    const removeHoverDom = function(treeId, treeNode) {
        $("#addBtn_"+treeNode.tId).unbind().remove();
    }

    /**
     * 是否显示编辑按钮
     * @param {*} treeId
     * @param {*} treeNode
     */
    const showRenameBtn = function(treeId, treeNode) {
        // 获取节点所配置的noEditBtn属性值
        return !(treeNode.noEditBtn !== undefined && treeNode.noEditBtn);
    }

    /**
     * 是否显示删除按钮
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const showRemoveBtn = function(treeId, treeNode) {
        // 获取节点所配置的noRemoveBtn属性值
        return !(treeNode.noRemoveBtn !== undefined && treeNode.noRemoveBtn);
    }

    /**
     * 恢复路径树节点选择
     * @param treeId
     * @param treeNode
     */
    const pathNodeSelect = function(treeId, treeNode) {
        if (treeNode.more) {
            let params = {}

            let api = '';
            switch (treeNode.event_type) {
                case 'agent':
                    params = {
                        agent_uuid: treeNode.uuid,
                        offset: treeNode.next_index,
                        limit: _pageSize,
                        filename: treeNode.search_file_name,
                        dir: treeNode.dir_path,
                        pid: treeNode.pid,
                        start_after: treeNode.search_file_name
                    }
                    api = '/api/v1/files/recover_path_tree';
                    break;
                case 'nas':
                    params = {
                        agent_uuid: treeNode.uuid,
                        offset: treeNode.next_index,
                        limit: _pageSize,
                        filename: treeNode.search_file_name,
                        dir: treeNode.dir_path,
                        pid: treeNode.pid,
                        start_after: treeNode.search_file_name
                    }
                    api = '/api/v1/nas/recover_path_tree';
                    break;
                case 'obs_agent':
                    params = {
                        agent_uuid: treeNode.uuid,
                        offset: treeNode.next_index,
                        limit: _pageSize,
                        filename: treeNode.search_file_name,
                        dir: treeNode.dir_path,
                        pid: treeNode.pid,
                        start_after: treeNode.search_file_name
                    }
                    api = '/api/v1/s3/recover_path_tree';
                    break;
                case 'hadoop_cluster':
                    params = {
                        hadoop_cluster_id: treeNode.uuid,
                        start: treeNode.next_index,
                        limit_count: _pageSize,
                        fetch_root_dir: treeNode.dir_path,
                        start_after: treeNode.next_start
                    }
                    api = '/api/v1/hadoop/recover_path_tree';
                    break;
                default:
                    break;
            }

            Metronic.blockUI({target: '#path_tree',animate: true});
            pAjaxRequest(params, api, 'GET', (result) => {
                if (result.success) {
                    pathTree.removeNode(treeNode);
                    if(treeNode.event_type == "hadoop_cluster"){
						pathTree.addNodes(treeNode.getParentNode(), result.data.file_nodes, true);
					}else{
						pathTree.addNodes(treeNode.getParentNode(), result.data, true);
					}
                } else {
                    UIToastr.showWarning(LANG.UI_HADOOP_RECOVERY_LOAD_MORE_FAILED, result.message);
                }

                Metronic.unblockUI('#path_tree');
            })
        } else {
            pathTree.checkNode(treeNode, !treeNode.checked, false, true);
        }
    }

    /**
     * 恢复路径树节点选中
     * @param e
     * @param id
     * @param node
     */
    const pathOnCheck = function(e, id, node) {
        let allNodes = pathTree.getCheckedNodes(true);

        for(let i = 0; i < allNodes.length; i++){
            pathTree.checkNode(allNodes[i], false, false, false);
        }

        pathTree.checkNode(node, true, false, false);
    }

    /**
     * 恢复路径树节点移除
     * @param event
     * @param treeId
     * @param treeNode
     */
    const onRemove = function(event, treeId, treeNode) {
        // 获取删除节点的父节点
        let parentNode = treeNode.getParentNode();
        // 如果父节点下的子节点不存在
        if (parentNode.children.length === 0) {
            // 修改父节点图标为文件夹样式
            parentNode.isParent = true;
            // parentNode.open = true;
            pathTree.updateNode(parentNode);
        }
    }

    /**
     * 恢复路径树节点重命名
     * @param event
     * @param treeId
     * @param treeNode
     */
    const onRename = function(event, treeId, treeNode) {
        // 选中新增的节点
        let allNodes = pathTree.getCheckedNodes(true);

        for (let i = 0; i < allNodes.length; i++){
            pathTree.checkNode(allNodes[i], false, false, false);
        }

        pathTree.checkNode(treeNode, true, false, false);

        //给新增的节点设置title
        let pnode = treeNode.getParentNode();
        //去掉sharepath最前面的斜杠
		let newpath = share_path.substring(1, share_path.length);
		let parentPath = pnode.dir_path.replace(newpath, '');
		treeNode.title = treeNode.name;
		treeNode.dir_path = parentPath + treeNode.name + '/';
        
        let str = $.trim(treeNode.name);
        if (str.length === 0) {
            UIToastr.showInfo(LANG.UI_NAS_RECOVERY_FILENAME_NO_NULL);
            filenameFlag = false;
            pathTree.editName(treeNode);
            return;
        } else {
            filenameFlag = true;
        }

        // 文件夹名称不能包含特殊符号,不包含  /  :  "  <  >  | \
        let specialchar = ['/', ':','"','<','>','|','\\','?','*'];
        for (let key in specialchar) {
            if (str.indexOf(specialchar[key]) !== -1) {
                UIToastr.showInfo(LANG.UI_FILE_FILENAME_NO_CONTAIN);
                filenameFlag = false;
                pathTree.editName(treeNode);
                return;
            } else {
                filenameFlag = true;
            }
        }
    }

    /**
     * 步骤二：恢复目标 - 恢复路径 - 获取 文件客户端 树
     */
    const initFileClientTree = (keyword = '') => {
        let params = {
            keyword: keyword
        }

        Metronic.blockUI({target: '#host_tree',animate: true});
        pAjaxRequest(params, '/api/v1/files/recover_host_tree', 'GET', (result) => {
            if (result.success) {
                if (result.data.length === 0) {
                    $('#noagenttips').show();
                    $('#host_tree').hide();
                    $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_AGENT);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();

                    // 初始化文件客户端树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_AGENT);

                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FAILED, result.message);
            }

            Metronic.unblockUI('#host_tree');
        })
    }

    /**
     * 步骤二：恢复目标 - 恢复路径 - 获取 NAS 设备树
     */
    const initNasClientTree = (keyword = '') => {
        let params = {
            keyword: keyword,
            timepoint_uuid: ajaxData.pointInfo.pointUUID,
            host_flag: true // 只显示当前勾选的备份数据的节点挂载过的nas设备，防止用户勾选未挂载的设备导致恢复失败
        }

        Metronic.blockUI({target: '#host_tree',animate: true});
        pAjaxRequest(params, '/api/v1/nas/backup_tree', 'GET', (result) => {
            if (result.success) {
                if (result.data.length === 0) {
                    $('#noagenttips').show();
                    $('#host_tree').hide();
                    $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_DEVICE);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();

                    // 初始化NAS设备树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_DEVICE);

                UIToastr.showWarning(LANG.UI_HADOOP_GET_NAS_FAILED, result.message);
            }

            Metronic.unblockUI('#host_tree');
        })
    }

    /**
     * 步骤二：恢复目标 - 恢复路径 - 获取 对象存储 树
     */
    const initObsClientTree = (keyword = '') => {
        let params = {
            keyword: keyword
        }

        Metronic.blockUI({target: '#host_tree',animate: true});
        pAjaxRequest(params, "/api/v1/s3/recover_obs_tree", "GET", function (result) {
            if (result.success) {
                if (result.data.length === 0) {
                    $('#noagenttips').show();
                    $('#host_tree').hide();
                    $('#noagenttips p').html(LANG.UI_OBS_RECOVERY_NO_STORAGE);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();

                    // 如果只有一种对象存储类型时，则默认展开其所有对象存储
                    if (result.data.filter(item => item.type === 'obsGroup').length === 1) {
                        result.data[0].open = true;
                    }

                    // 初始化对象存储树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_OBS_RECOVERY_NO_STORAGE);

                UIToastr.showWarning(LANG.UI_HADOOP_GET_OBS_FAILED, result.message);
            }

            Metronic.unblockUI('#host_tree');
        });
    }

    /**
     * 步骤二：恢复目标 - 恢复路径 - 获取 Hadoop 树
     */
    const initHadoopClientTree = (keyword = '') => {
        let params = {
            keyword: keyword,
            recoverflag:true
        }

        Metronic.blockUI({target: '#host_tree',animate: true});
        pAjaxRequest(params, '/api/v1/hadoop/backup/cluster/ztree', 'GET', (result) => {
            if (result.success) {
                if (result.data.length === 0) {
                    $('#noagenttips').show();
                    $('#host_tree').hide();
                    $('#noagenttips p').html(LANG.UI_OBS_NO_USEFULL_HADOOP);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();

                    // 初始化Hadoop集群树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_OBS_NO_USEFULL_HADOOP);

                UIToastr.showWarning(LANG.UI_OBS_GET_HADOOP_CLUSTER_FAILED, result.message);
            }

            Metronic.unblockUI('#host_tree');
        })
    }

    const loopClientTreeNode = (treeObj, node) => {
        hostTree.expandNode(node, true, false, false);
        if(!node.children){
            clientTreeNodeParams.push(node);
            hostTree.expandNode(node, false, false, false);
        }
        let pNode = node.getParentNode();
        if(pNode != null){
            clientTreeNodeParams.push(pNode);
            loopClientTreeNode(hostTree, pNode);
        }
    }

    /**
     * 步骤二：恢复目标 - 搜索 文件客户端/NAS设备/对象存储/Hadoop
     */
    const searchClientTreeNode = () => {
        // 当前客户端下没有对应资源直接return
        if ($('#noagenttips').css('display') !== 'none') return;
        
        let searchVal = $('#searchHost').val();
        let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备
        let des = '';

        if (!searchVal) {
            initHostTree();
        }

        let nodes = hostTree.getNodes();
        if (!nodes || nodes.length === 0) return;

        let checkNode = hostTree.getCheckedNodes();
        let checkedClientNodes = [];
        
        $.each(checkNode, function (i, v) {
            if (v.type == "obsItem") {
                checkedClientNodes.push(v);
            }
        });

        let allNode = hostTree.transformToArray(hostTree.getNodes());
        
        clientTreeNodeParams = hostTree.getNodesByParamFuzzy('name', searchVal);
        
        if (clientTreeNodeParams.length !== 0) {
            hostTree.hideNodes(allNode);
            $('#noagenttips').hide();
            $('#host_tree').show();
        } else {
            switch (deviceClient) {
                case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
                    des = LANG.UI_FILE_RECOVERY_NO_AGENT;
                    break;
                case STORAGE_DEVICE_TYPE.NAS: // NAS设备
                    des = LANG.UI_FILE_RECOVERY_NO_DEVICE;
                    break;
                case STORAGE_DEVICE_TYPE.HADOOP: // Hadoop集群
                    des = LANG.UI_OBS_NO_USEFULL_HADOOP;
                    break;
                case STORAGE_DEVICE_TYPE.OBS: // 对象存储
                    des = LANG.UI_OBS_RECOVERY_NO_STORAGE;
                    break;
                default:
                    break;
            }

            $('#noagenttips').show();
            $('#host_tree').hide();
            $('#noagenttips p').html(des);
        }

        // 连接搜索的和所勾选的
        clientTreeNodeParams = clientTreeNodeParams.concat(checkedClientNodes);
        let nodeParamList1 = hostTree.transformToArray(clientTreeNodeParams);
        
        for(let n in nodeParamList1){
            loopClientTreeNode(hostTree, nodeParamList1[n]);
        }
        
        hostTree.showNodes(clientTreeNodeParams);
    }

    const step2Valid = () => {
        let agentShowInfo = '', pathShowInfo = '';
        let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备

        // 1.校验
        if (!hostTree){
            $('#noagenttips').show();
            return false;
        }

        let nodes = hostTree.getCheckedNodes();
        if(!nodes.length){
            switch(deviceClient) {
				case STORAGE_DEVICE_TYPE.FILE: // 文件
					UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_REC_HOST_TITLE, LANG.UI_RECOVERY_FILE_NO_REC_HOST_VALUE);
					return false;
				case STORAGE_DEVICE_TYPE.NAS: //NAS
					UIToastr.showWarning(LANG.UI_RECOVERY_HADOOP_NO_REC_NAS_TITLE, LANG.UI_RECOVERY_HADOOP_NO_REC_NAS_VALUE);
					return false;
				case STORAGE_DEVICE_TYPE.HADOOP: // hadoop
					UIToastr.showWarning(LANG.UI_RECOVERY_HADOOP_NO_REC_HADOOP_TITLE, LANG.UI_RECOVERY_HADOOP_NO_REC_HADOOP_VALUE);
					return false;
				case STORAGE_DEVICE_TYPE.OBS: // OBS
					UIToastr.showWarning(LANG.UI_RECOVERY_HADOOP_NO_REC_OBS_TITLE, LANG.UI_RECOVERY_HADOOP_NO_REC_OBS_VALUE);
					return false;
				default:
					return false
			}
        }

        if(!filenameFlag) {
			return false;
		}

        recoverTargetVendor = nodes[0].vendor; // 获取恢复目标对象存储vendor

        // if (selectedBackupVendor === 0 && recoverTargetVendor === 0) { // 只有AWS下的备份时间点且恢复到AWS下的对象存储才有文件权限恢复
        //     $('.obs-permission-recover-form-group').show();
        // } else {
        //     $('.obs-permission-recover-form-group').hide();
        // }

        // 2.ajax data赋值
        ajaxData.recoverInfo.agentUUID = nodes[0].uuid;
        ajaxData.recoverInfo.pathType = parseInt($('#pathtype').val());

        if (ajaxData.recoverInfo.pathType === 1) { // 恢复到原路径
            ajaxData.recoverInfo.path = '';
            ajaxData.recoverInfo.code_type = 2;
            pathShowInfo = LANG.UI_OBS_RECOVERY_FILE_REC_OLD_PATH;
        } else { // 手动选择路径
            if (!pathTree) {
                UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_REC_PATH_TITLE, LANG.UI_RECOVERY_FILE_NO_REC_PATH_VALUE);
                return false;
            }

            let pathNodes = pathTree.getCheckedNodes();

            if (pathNodes.length === 0) {
                UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_REC_PATH_TITLE, LANG.UI_RECOVERY_FILE_NO_REC_PATH_VALUE);
                return false;
            }
            
            let dir_path = pathNodes[0].dir_path;
			if(nodes[0].event_type === "nas") {
                // 去掉sharepath最前面的斜杠
                let newpath = share_path.substring(1, share_path.length);
				dir_path = pathNodes[0].dir_path.replace(newpath, '');
            }

            //选择nas根目录
            if(pathNodes[0].parentFlag !== undefined) {
                pathNodes[0].dir_path = "/";
            }

            ajaxData.recoverInfo.path = dir_path;
            ajaxData.recoverInfo.new_dir_create = pathNodes[0].new_dir_create;
            ajaxData.recoverInfo.code_type = pathNodes[0].code_type;

            // 如果恢复客户端是对象存储则需要处理 |
            if (deviceClient === STORAGE_DEVICE_TYPE.OBS) {
                pathShowInfo = replaceBetweenStartEnd(dir_path, '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            } else {
                pathShowInfo = dir_path;
            }
        }

        // 3.表单确认回显
        switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 恢复到文件
                $('.permission-recover-li').show(); // 权限恢复 tab
                $('.file-permission-recover-form-group').show(); // 权限恢复
                $('.advanced-config-obs-form-group').hide(); // 隐藏对象存储所属form-group项
                $('.transfer-agency-form-group').hide(); // 隐藏传输代理
                $('.applianceselectdiv').hide(); // 隐藏传输代理选择框
                $('.advanced-config-file-form-group').show(); // 显示 文件、NAS和Hadoop所属form-group项
                $('.encrypt-transfer-form-group').show(); // 显示传输加密
                $('.network-retry-times-form-group').show(); // 显示网络重连次数
                $('.network-retry-interval-form-group').show(); // 显示网络重连间隔时间
                $('.confirm-advanced-config-network-retry-times').show(); // 显示确认网络重连次数配置
                $('.confirm-advanced-config-network-retry-interval').show(); // 显示网络重连间隔时间配置

                // 重置传输代理
				$('#appliance_agency_flag').bootstrapSwitch('state', false);

                if ($('#transport_encrypt_flag').get(0).checked) { // 如果已经开启传输加密（先在第三步开启加密传输，再点上一步，再点下一步）
                    $('.encrypt-transfer-method-form-group').show(); // 显示传输加密算法选择框
                } else {
                    // 重置传输加密
                    $('#transport_encrypt_flag').bootstrapSwitch('state', false);
                    $('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框
                }
                
                break;
            case STORAGE_DEVICE_TYPE.NAS: // 恢复到NAS
                $('.advanced-config-obs-form-group').hide(); // 隐藏对象存储所属form-group项
                $('.transfer-agency-form-group').hide(); // 隐藏传输代理
                $('.applianceselectdiv').hide(); // 隐藏传输代理选择框
                $('.network-retry-times-form-group').hide(); // 隐藏网络重连次数
                $('.network-retry-interval-form-group').hide(); // 隐藏网络重连间隔时间
                $('.confirm-advanced-config-network-retry-times').hide(); // 隐藏确认网络重连次数配置
                $('.confirm-advanced-config-network-retry-interval').hide(); // 隐藏网络重连间隔时间配置
                $('.advanced-config-file-form-group').show(); // // 显示 文件、NAS和Hadoop所属form-group项
                $('.permission-recover-li').show(); // 权限恢复 tab
                $('.file-permission-recover-form-group').show(); // 权限恢复

                // 重置传输代理
				$('#appliance_agency_flag').bootstrapSwitch('state', false);

				// 重置传输加密
				$('#transport_encrypt_flag').bootstrapSwitch('state', false);
                $('.encrypt-transfer-form-group').hide(); // 隐藏传输加密
				$('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框

                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // 恢复到Hadoop
                $('.permission-recover-li').show(); // 权限恢复 tab
                $('.file-permission-recover-form-group').show(); // 权限恢复
                $('.advanced-config-obs-form-group').hide(); // 隐藏对象存储所属form-group项
                $('.transfer-agency-form-group').show(); // 显示传输代理
				$('.advanced-config-file-form-group').show(); // 显示 文件、NAS和Hadoop所属form-group项
                $('.network-retry-times-form-group').show(); // 显示网络重连次数
                $('.network-retry-interval-form-group').show(); // 显示网络重连间隔时间
                $('.confirm-advanced-config-network-retry-times').show(); // 显示确认网络重连次数配置
                $('.confirm-advanced-config-network-retry-interval').show(); // 显示网络重连间隔时间配置

                if ($('#appliance_agency_flag').get(0).checked) { // 如果传输代理已经开启（先在第三步开启传输代理，再点上一步，再点下一步）
                    $('.applianceselectdiv').show(); // 显示传输代理选择框
                    $('.encrypt-transfer-form-group').show(); // 显示加密传输

                    if ($('#transport_encrypt_flag').get(0).checked) { // 如果加密传输也已经开启（先在第三步开启加密传输，再点上一步，再点下一步）
				        $('.encrypt-transfer-method-form-group').show(); // 显示传输加密算法选择框
                    } else { // 加密传输未开启
                        // 重置传输加密
                        $('#transport_encrypt_flag').bootstrapSwitch('state', false);
                        $('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框
                    }
                } else {
                    // 重置传输代理
				    $('#appliance_agency_flag').bootstrapSwitch('state', false);

                    $('.applianceselectdiv').hide(); // 隐藏传输代理选择框

                    // 重置传输加密
				    $('#transport_encrypt_flag').bootstrapSwitch('state', false);
                    $('.encrypt-transfer-form-group').hide(); // 隐藏传输加密
				    $('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框
                }
                
                break;
            case STORAGE_DEVICE_TYPE.OBS: // 恢复到对象存储
                $('.permission-recover-li').hide(); // 权限恢复 tab
                $('.file-permission-recover-form-group').hide(); // 权限恢复
                $('.advanced-config-file-form-group').hide(); // 隐藏 文件、NAS和Hadoop所属form-group项
                $('.transfer-agency-form-group').show(); // 显示传输代理
                $('.advanced-config-obs-form-group').show(); // 显示对象存储所属form-group项
                $('.network-retry-times-form-group').show(); // 显示网络重连次数
                $('.network-retry-interval-form-group').show(); // 显示网络重连间隔时间
                $('.confirm-advanced-config-network-retry-times').show(); // 显示确认网络重连次数配置
                $('.confirm-advanced-config-network-retry-interval').show(); // 显示网络重连间隔时间配置

                if ($('#appliance_agency_flag').get(0).checked) { // 如果传输代理已经开启（先在第三步开启传输代理，再点上一步，再点下一步）
                    $('.applianceselectdiv').show(); // 显示传输代理选择框
                    $('.encrypt-transfer-form-group').show(); // 显示加密传输

                    if ($('#transport_encrypt_flag').get(0).checked) { // 如果加密传输也已经开启（先在第三步开启加密传输，再点上一步，再点下一步）
				        $('.encrypt-transfer-method-form-group').show(); // 显示传输加密算法选择框
                    } else { // 加密传输未开启
                        // 重置传输加密
                        $('#transport_encrypt_flag').bootstrapSwitch('state', false);
                        $('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框
                    }
                } else {
                    // 重置传输代理
				    $('#appliance_agency_flag').bootstrapSwitch('state', false);

                    $('.applianceselectdiv').hide(); // 隐藏传输代理选择框

                    // 重置传输加密
				    $('#transport_encrypt_flag').bootstrapSwitch('state', false);
                    $('.encrypt-transfer-form-group').hide(); // 隐藏传输加密
				    $('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框
                }

                let pathType = parseInt($('#pathtype').val());
                if (pathType === 1) { // 恢复到原路径隐藏去除前缀
                    $('.remove-prefix-form-group').hide();
                } else {
                    $('.remove-prefix-form-group').show();
                }

                break;
            default:
                break;
        }

        // 重置恢复方式tabs
        $('#obs_recovery_mode_tabs').find('li.active').removeClass('active');
        $('#obs_recovery_mode_tab_content').find('.tab-pane.obs-recovery-mode-tab-pane.active').removeClass('active');

        // 显示通用策略tab
        $('#obs_recovery_mode_tabs').find(':first').addClass('active');
        $('#obs_recovery_mode_tab_content').find(':first.obs-recovery-mode-tab-pane').addClass('active');

        agentShowInfo = nodes[0].name;
        $('.recover2agent').html(agentShowInfo);
        //恢复路径
        $('.recover2path').html(pathShowInfo);
        $('#recoveryThreadNum').prop('disabled', false); //启用线程数选择

        // 4.步骤三 恢复方式显示时间策略描述
        initTimeStrategyDes();
        initResourceLimit([ajaxData.pointInfo.nodeuuid]);// 高级配置 - 过载保护 显示资源的配置信息

        return true;
    }

    //<----------------------------- END STEP TWO RECOVER TARGET ------------------------------------>


    //<----------------------------- BEGIN STEP THREE RECOVER WAY ----------------------------------->

    const initTimeStrategyDes = () => {
        let des = $('#recovertype').find("option:selected").text();
		
		if ($('#recovertype').val() == 4) {
			des += '，' + LANG.UI_JOB_TIMING_RECOVER_TIME + "：" + $('#oncetime').val();
		}

		$('.recoveryTimeDes').html(des);
		$('.recoveryTimeDes').prop('title', des);
    }

    /**
     * 传输代理 switch change
     */
    const applianceSwitchChange = () => {
        let flag = $('#appliance_agency_flag').get(0).checked;

        if (flag) {
            $('.applianceselectdiv').show();
            $('.encrypt-transfer-form-group').show(); // 显示加密传输
        } else {
            $('.applianceselectdiv').hide();
            $('.encrypt-transfer-form-group').hide(); // 显示加密传输
            $('#transport_encrypt_flag').bootstrapSwitch('state', false);
            $('.encrypt-transfer-method-form-group').hide();
        }
    };

    /**
     * 加密传输 switch change
     */
    const transferEncryptSwitchChange = () => {
        let flag = $('#transport_encrypt_flag').get(0).checked;

        if (flag) {
            $('.encrypt-transfer-method-form-group').show();
        } else {
            $('.encrypt-transfer-method-form-group').hide();
        }
    }

    /**
     * 高级配置 - 重试策略 - 操作异常重试 - 自动重试 change
     */
    const opRetrySwitchChange = () => {
        let flag = $('#op_retry_flag').get(0).checked;

		if(flag) {
			$('.operation-retry-wrap').show();
		} else {
			$('.operation-retry-wrap').hide();
		}
    }

    /**
     * 高级配置 - 重试策略 - 任务重试 - 自动重试 change
     */
    const taskRetrySwitchChange = () => {
        let flag = $('#task_retry_flag').get(0).checked;

		if(flag) {
			$('.task-retry-wrap').show();
		} else {
			$('.task-retry-wrap').hide();
		}
    }

    const getEachStrategy = (strategy) =>{
        let desEach = '';
        desEach += strategy.startTime;
        desEach += LANG.UI_STRATEGY_START + ", ";
        if(strategy.rollFlag){
            desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
        }else{
            desEach += LANG.UI_STRATEGY_ROLL_NO;
        }
        desEach += "<br><br>";

        return desEach;
    }

    const getStrategyDes = (strategy) => {
        //每天12:12:12开始,不滚动
        //每天12:12:12开始,滚动间隔01:11:11,滚动结束时间23:11:11
        //每周1,2,3,4,5,6,
        let des = "";
        if (CONF.STRATEGY_TYPE.DAY == strategy.type){
            des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy);
        }else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
            if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
                des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
            }else{
                des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
            }
        }else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
            des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
        }else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
            des += LANG.UI_STRATEGY_GLOBAL;
        }else{
            des += LANG.UI_PUBLIC_NOTHING + "<br><br>";
        }

        return des;
    }

    /**
     * 获取选择的限速策略
     */
    const getSpeedLimitStrategy = () => {
        let info = {
            level: parseInt($('#tasklevelselect').val()),
            type: parseInt($('#speedtypeselect').val())
        }

        if (info.type === 1) { // 选择全局限速策略
            let selectedRows = $('.strategy-table #table').bootstrapTable('getSelections');

            if (selectedRows.length === 1) {
                info.uuid = selectedRows[0].uuid;
                info.name = selectedRows[0].name;
                info.strategyType = selectedRows[0].type;
                info.speed = [{'des': selectedRows[0].detail}];
            }
        } else { // 自定义策略
            info.speed = $('#speedstrategy').getSpeedStrategyConfigFinal();
            
            // 处理参数名字与接口统一
            if (info.speed.length > 0) {
                info.speed = info.speed.map(item => {
                    return {
                        ...item,
                        start_time: item.startTime,
                        end_time: item.endTime
                    }
                })
            }
        }

        return info;
    }

    const initStrategyDesStyle = (div, des, oldDes) => {
        if (oldDes !== des) {
			div.addClass('font-green-seagreen');
		} else {
			div.removeClass('font-green-seagreen');
		}
    }

    const initHighStrategyDes = function() {
        let des = "";
		des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#recoveryThreadNum').val();
		let strategyIndex = $('#strategySelect').val();

		if (strategyIndex && strategyIndex !== "") {
			let oldDes = globalStrategy[strategyIndex].high.des;
			initStrategyDesStyle($('.recoveryHighDes'), des, oldDes);
		} else {
			$('.recoveryHighDes').removeClass('font-green-seagreen');
		}

		$('.recoveryHighDes').html(des);
		$('.recoveryHighDes').prop('title', des);
    }

    const initHighListeners = () => {
        $('#recoveryThreadNum').on('input propertychange', function() {
			initHighStrategyDes();
		});
		$('.spinner-up').on('click', function() {
			initHighStrategyDes();
		});
		$('.spinner-down').on('click', function() {
			initHighStrategyDes();
		});
    }

    const getSwitchDes = (check) => {
        if(check){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    /**
     * 确认表单配置
     */
    /**
     * 确认表单配置
     * @param {*} completeStrategyDes 完整性校验策略描述
     */
    const confirmRecoverForm = (completeStrategyDes) => {
        let transferHtml = '';
        let networkRetryTimesHtml = '';
        let networkRetryIntervalHtml = '';
        let opRetryHtml = '';
        let opRetryTimesHtml = '';
        let opRetryIntervalHtml = '';
        let taskRetryHtml = '';
        let taskRetryObjetHtml = '';
        let taskRetryTimesHtml = '';
        let taskRetryIntervalHtml = '';
        let overloadProtectionHtml = '';

        let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备
        let pathType = parseInt($('#pathtype').val()); // 手动选择路径 | 原路径

        // 恢复方式
        let showStr1 = '';
        showStr1 = $('.recoveryTimeDes').text();
        $('.reservetypeshow').html(showStr1);

        // 限速策略
        let speedlimitStr = $('.speedlimitDes').prop('title') === '' ? LANG.UI_PUBLIC_NOTHING : $('.speedlimitDes').prop('title');
        $('.speedlimitshow').html(speedlimitStr);

        // 传输策略
        if (CURRENT_STORAGE_TYPE_IS_TAPE) {
            $('.transfershow').html(LANG.UI_PUBLIC_NOTHING + '<br>');
        } else {
            transferHtml += `${LANG.UI_GLOBAL_STRATEGY_THREAD_NUM}：${ajaxData.thread_num}<br>`;
            $('.transfershow').html(transferHtml);
        }

        if (ajaxData.typeInfo.high.trasfer.encrypt) { // 开启加密传输
            transferHtml += LANG.UI_COPY_BACK_ENCRYPT + ": " + LANG.UI_PUBLIC_ON + '<br>';

            let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
            let method = parseInt($('#transferEncryptMethod').val());
            let grade = '';
            switch (method) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                    break;
                default:
                    break;
            }

            transferHtml += encryptedMethodLabel + ": " + grade + "<br>";
        } else {
            transferHtml += LANG.UI_COPY_BACK_ENCRYPT + ": " + LANG.UI_PUBLIC_OFF + '<br>';
        }

        if (deviceClient === STORAGE_DEVICE_TYPE.OBS || deviceClient === STORAGE_DEVICE_TYPE.HADOOP) { // 恢复到对象存储和hadoop才有传输代理
            transferHtml += LANG.UI_PUBLIC_APPLICE + ': ' + getSwitchDes(ajaxData.typeInfo.high.trasfer.appliance_agency_flag) + '<br>';

            let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
            if (ajaxData.typeInfo.high.trasfer.appliance_agency_flag) { // 开启传输代理
                transferHtml += $('.applianceselectlabel').text() + ': ' + applianceNode.name + '<br>';
            }
        }

        switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
                // 传输网络
                if ($('#transferNetwork').val()) {
                    transferHtml += LANG.UI_VOL_CDP_RECOVER_TRANSPORT_NET + ': ' + $('#transferNetwork').find("option:selected").text();
                }

                $('.transfershow').html(transferHtml);
                $('.transferDiv').show();

                if (pathType === 1) { // 原路径恢复
                    $('.dirtreeshow').hide();
                } else {
                    $('.dirtreeshow').html($('.file-skip-dir-tree-label').html() + ": " + getSwitchDes(ajaxData.highInfo.dir_tree_recovery_flag));
                    $('.dirtreeshow').show();
                }

                $('.samenameshow').html($('.file-same-name-label').html() + ": " + $('#file_same_name_handle_type option:selected').text());
                // $('.clearshortcutshow').html($('.clear-shortcut-label').html() + ": " + getSwitchDes(ajaxData.highInfo.link_file_pass_flag));
                // $('.permissionrecoveryshow').html($('.file-permission-recovery-label').html() + ": " + getSwitchDes(ajaxData.highInfo.permission_operate_flag));

                break;
            case STORAGE_DEVICE_TYPE.NAS: // NAS设备
                $('.transferDiv').hide();

                if (pathType === 1) { // 原路径恢复
                    $('.dirtreeshow').hide();
                } else {
                    $('.dirtreeshow').html($('.file-skip-dir-tree-label').html() + ": " + getSwitchDes(ajaxData.highInfo.dir_tree_recovery_flag));
                    $('.dirtreeshow').show();
                }

                $('.samenameshow').html($('.file-same-name-label').html() + ": " + $('#file_same_name_handle_type option:selected').text());
                // $('.clearshortcutshow').html($('.clear-shortcut-label').html() + ": " + getSwitchDes(ajaxData.highInfo.link_file_pass_flag));
                // $('.permissionrecoveryshow').html($('.file-permission-recovery-label').html() + ": " + getSwitchDes(ajaxData.highInfo.permission_operate_flag));

                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // Hadoop
                $('.transfershow').html(transferHtml);
                $('.transferDiv').show();

                if (pathType === 1) { // 原路径恢复
                    $('.dirtreeshow').hide();
                } else {
                    $('.dirtreeshow').html($('.file-skip-dir-tree-label').html() + ": " + getSwitchDes(ajaxData.highInfo.dir_tree_recovery_flag));
                    $('.dirtreeshow').show();
                }

                $('.samenameshow').html($('.file-same-name-label').html() + ": " + $('#file_same_name_handle_type option:selected').text());
                // $('.clearshortcutshow').html($('.clear-shortcut-label').html() + ": " + getSwitchDes(ajaxData.highInfo.link_file_pass_flag));
                // $('.permissionrecoveryshow').hide();
                break;
            case STORAGE_DEVICE_TYPE.OBS: // 对象存储
                $('.transfershow').html(transferHtml);
                $('.transferDiv').show();

                if (pathType === 1) { // 原路径恢复
                    $('.dirtreeshow').hide();
                } else {
                    $('.dirtreeshow').html($('.obs-skip-dir-tree-label').html() + ": " + getSwitchDes(ajaxData.highInfo.dir_tree_recovery_flag));
                    $('.dirtreeshow').show();
                }

                $('.samenameshow').html($('.obs-same-name-label').html() + ": " + $('#obs_same_name_handle_type option:selected').text());
                // $('.permissionrecoveryshow').html($('.obs-permission-recovery-label').html() + ": " + getSwitchDes(ajaxData.highInfo.permission_operate_flag));

                break;
            default:
                break;
        }

        if (ajaxData.safe_strategy.integrity_check_config.recovery_error_policy === 0) { // 中断恢复
            $('.confirm-integrity-check-abnormal-handle').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL}：${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY}`);
        } else if (ajaxData.safe_strategy.integrity_check_config.recovery_error_policy === 1) { // 继续恢复
            $('.confirm-integrity-check-abnormal-handle').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL}：${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY}`);
        } else {
            $('.confirm-integrity-check-abnormal-handle').html(completeStrategyDes);
        }

        networkRetryTimesHtml = `${LANG.UI_RETRY_STRATEGY_NETWORK_TIMES}：${ajaxData.retry_strategy.network_retry_times}`;
        networkRetryIntervalHtml = `${LANG.UI_OBS_NETWORK_RECONNECT_INTERVAL}：${ajaxData.retry_strategy.network_retry_interval}${LANG.UI_PUBLIC_SECOND}`;
        $('.confirm-advanced-config-network-retry-times').html(networkRetryTimesHtml);
        $('.confirm-advanced-config-network-retry-interval').html(networkRetryIntervalHtml);

        opRetryHtml = `${LANG.UI_OBS_OPERATE_ABNORMAL_RETRY}: ${getSwitchDes(ajaxData.retry_strategy.op_retry_flag)}`;
        $('.confirm-advanced-config-op-retry').html(opRetryHtml);
        if (ajaxData.retry_strategy.op_retry_flag) { // 开启了操作异常重试
            $('.confirm-advanced-config-op-retry-times').show();
            $('.confirm-advanced-config-op-retry-interval').show();

            // 确认配置 - 高级配置
            opRetryTimesHtml = `${LANG.UI_OBS_OPERATE_ABNORMAL_RETRY_TIMES}：${ajaxData.retry_strategy.op_retry_times}`;
            opRetryIntervalHtml = `${LANG.UI_OBS_OPERATE_ABNORMAL_RETRY_INERVAL}：${ajaxData.retry_strategy.op_retry_interval}${LANG.UI_PUBLIC_SECOND}`;
            $('.confirm-advanced-config-op-retry-times').html(opRetryTimesHtml);
            $('.confirm-advanced-config-op-retry-interval').html(opRetryIntervalHtml);
        } else {
            $('.confirm-advanced-config-op-retry-times').hide();
            $('.confirm-advanced-config-op-retry-interval').hide();
        }

        taskRetryHtml = `${LANG.UI_OBS_TASK_RETRY}：${getSwitchDes(ajaxData.retry_strategy.task_retry_flag)}`;
        $('.confirm-advanced-config-task-retry').html(taskRetryHtml);
        if (ajaxData.retry_strategy.task_retry_flag) { // 开启了任务重试
            $('.confirm-advanced-config-task-retry-object').show();
            $('.confirm-advanced-config-task-retry-times').show();
            $('.confirm-advanced-config-task-retry-interval').show();

            taskRetryObjetHtml = `${LANG.UI_OBS_RETRY_OBJECT}：${ ajaxData.retry_strategy.task_retry_object === 1 ? LANG.UI_RETRY_STRATEGY_OBJECT_OPTION1 : LANG.UI_RETRY_STRATEGY_OBJECT_OPTION2 }`;
            taskRetryTimesHtml = `${LANG.UI_OBS_TASK_RETRY_TIMES}：${ajaxData.retry_strategy.task_retry_times}`;
            taskRetryIntervalHtml = `${LANG.UI_OBS_TASK_RETRY_INTERVAL}：${ajaxData.retry_strategy.task_retry_interval}${LANG.UI_MICROSOFT365_MINUTE}`;
            $('.confirm-advanced-config-task-retry-object').html(taskRetryObjetHtml);
            $('.confirm-advanced-config-task-retry-times').html(taskRetryTimesHtml);
            $('.confirm-advanced-config-task-retry-interval').html(taskRetryIntervalHtml);
        } else {
            $('.confirm-advanced-config-task-retry-object').hide();
            $('.confirm-advanced-config-task-retry-times').hide();
            $('.confirm-advanced-config-task-retry-interval').hide();
        }

        overloadProtectionHtml = `${$('.overload-protection-label').html()}: ${getSwitchDes(ajaxData.highInfo.ignore_resource_limiting_flag)}`;
        $('.confirm-advanced-config-overload-protection').html(overloadProtectionHtml);
    }

    /**
     * 步骤三 恢复方式校验
     * @returns 
     */
    const step3Valid = () => {
        let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备
        let recoverType = parseInt($('#recovertype').val());
        let start_time =  $('#oncetime').val(); //定时恢复时间
        let encryptMethod = parseInt($('#transferEncryptMethod').val());
        let networkRetryTimes = deviceClient === STORAGE_DEVICE_TYPE.NAS ? '' : parseInt($('#network_retry_times').val()); // 网络重试重连次数
        let networkRetryInterval = deviceClient === STORAGE_DEVICE_TYPE.NAS ? '' : parseInt($('#network_retry_interval').val()); // 网络重试重连间隔时间
        let opRetryTimes = parseInt($('#op_retry_times').val()); // 操作异常重试次数
        let opRetryInterval = parseInt($('#op_retry_interval').val()); // 操作异常重试间隔时间
        let taskRetryTimes = parseInt($('#task_retry_times').val()); // 任务重试次数
        let taskRetryInterval = parseInt($('#task_retry_interval').val()); // 任务重连间隔时间
        let threadNum = CONF.FUNCTIONS.includes('multithread') ? parseInt($('#recoveryThreadNum').val()) : 1; // 多线程未授权时，传输线程默认传1
        let completeConfig = $('#completeConfig').getCompleteStrategyCovery();
        let recovery_error_policy = -1;
        
        if (!CONF.FUNCTIONS.includes('integrity')) { // 未授权完整性校验
            recovery_error_policy = -1;
        } else {
            recovery_error_policy = parseInt(completeConfig.integrity_check_flag === 0 ? -1 : completeConfig.integrity_check_config.recovery_error_policy);
        }

        // 1.校验
        if (recoverType === 4 && !start_time) { // 选了定时恢复但是时间为空
            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
			return false;
        }

        if (threadNum > 32 || threadNum < 1 || !threadNum) { // 校验传输线程
            // 重置为默认值
            $('#recoveryThreadDiv').spinner("value", 3);
            $('.recoveryHighDes').html(LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#recoveryThreadNum').val());
            UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);

            return false;
        }

        APPLIANCE_AGENCY_HAS_CONFIGED = $('#transferAgentTree').transferAgent('validateSelect');
        if ($('#appliance_agency_flag').get(0).checked && !APPLIANCE_AGENCY_HAS_CONFIGED) { // 开启传输代理但传输代理未预先配置时
            UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);

            return false;
        }

        if (deviceClient !== STORAGE_DEVICE_TYPE.NAS && !Number.isInteger(networkRetryTimes)){ // 校验网络重试重连次数(恢复到NAS不做校验)
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_TIMES);
            return false;
        }

        if (deviceClient !== STORAGE_DEVICE_TYPE.NAS && networkRetryTimes < 1) { // 网络重试重连次数小于1(恢复到NAS不做校验)
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
            return false;
        }

        if (deviceClient !== STORAGE_DEVICE_TYPE.NAS && networkRetryTimes > 60) { // 网络重试重连次数大于60(恢复到NAS不做校验)
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_MAX_TIMES);
            return false;
        }

        if (deviceClient !== STORAGE_DEVICE_TYPE.NAS && !Number.isInteger(networkRetryInterval)){ // 校验网络重试重连间隔时间(恢复到NAS不做校验)
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_INTERVAL);
            return false;
        }
        
        if (deviceClient !== STORAGE_DEVICE_TYPE.NAS && networkRetryInterval < 1) { // 网络重试重连间隔时间小于1(恢复到NAS不做校验)
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_MIN_RECONNECT_INTERVAL);
            return false;
        }

        if (deviceClient !== STORAGE_DEVICE_TYPE.NAS && networkRetryInterval > 60) { // 网络重试重连间隔时间大于60(恢复到NAS不做校验)
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_MAX_RECONNECT_INTERVAL);
            return false;
        }

        // Ajax Data 高级配置赋值
        ajaxData.typeInfo.type = recoverType;
        ajaxData.typeInfo.strategy.type = recoverType;
        ajaxData.typeInfo.strategy.start_time = start_time;
        ajaxData.safe_strategy.integrity_check_config.recovery_error_policy = recovery_error_policy; // 完整性校验异常处理方式
        ajaxData.retry_strategy.network_retry_times = networkRetryTimes; // 网络重试次数
        ajaxData.retry_strategy.network_retry_interval = networkRetryInterval; // 网络重试间隔时间
        ajaxData.retry_strategy.op_retry_flag = $('#op_retry_flag').get(0).checked; // 操作异常自动重试
        ajaxData.retry_strategy.task_retry_flag = $('#task_retry_flag').get(0).checked; // 任务重试自动重试

        if (ajaxData.retry_strategy.op_retry_flag) { // 开启了操作异常重试
            if (!Number.isInteger(opRetryTimes)){ // 校验操作异常重试重连次数
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_TIMES);
                return false;
            }
    
            if (opRetryTimes < 1) { // 操作异常重试重连次数小于1
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
                return false;
            }
    
            if (opRetryTimes > 60) { // 操作异常重试重连次数大于60
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_MAX_TIMES);
                return false;
            }

            if (!Number.isInteger(opRetryInterval)){ // 校验操作异常重试重连间隔时间
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_INTERVAL);
                return false;
            }
            
            if (opRetryInterval < 1) { // 操作异常重试重连间隔时间小于1
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_MIN_RECONNECT_INTERVAL);
                return false;
            }
    
            if (opRetryInterval > 60) { // 网络重试重连间隔时间大于60
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_MAX_RECONNECT_INTERVAL);
                return false;
            }

            // Ajax Data 高级配置赋值
            ajaxData.retry_strategy.op_retry_times = opRetryTimes; // 操作异常重试次数
            ajaxData.retry_strategy.op_retry_interval = opRetryInterval; // 操作异常重连间隔时间
        } else {
            // Ajax Data 高级配置赋值
            ajaxData.retry_strategy.op_retry_times = 0; // 操作异常重试次数
            ajaxData.retry_strategy.op_retry_interval = 0; // 操作异常重连间隔时间
        }

        if (ajaxData.retry_strategy.task_retry_flag) { // 开启了任务重试
            if (!Number.isInteger(taskRetryTimes)){ // 校验任务重试重连次数
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_TIMES);
                return false;
            }
    
            if (taskRetryTimes < 1) { // 任务重试重连次数小于1
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
                return false;
            }
    
            if (taskRetryTimes > 3) { // 任务重试重连次数大于3
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_TASK_RETRY_MAX_TIMES);
                return false;
            }

            if (!Number.isInteger(taskRetryInterval)){ // 校验任务重试重连间隔时间
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_INTERVAL);
                return false;
            }
            
            if (taskRetryInterval < 1) { // 任务重试重连间隔时间小于1
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_TASK_RETRY_MIN_INTERVAL);
                return false;
            }
    
            if (taskRetryInterval > 60) { // 任务重试重连间隔时间大于60
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_TASK_RETRY_MAX_INTERVAL);
                return false;
            }

            // Ajax Data 高级配置赋值
            ajaxData.retry_strategy.task_retry_times = taskRetryTimes; // 任务重试重连次数
            ajaxData.retry_strategy.task_retry_interval = taskRetryInterval * 60; // 任务重试重连间隔时间
            ajaxData.retry_strategy.task_retry_object = parseInt($('#task_retry_object').val()); // 任务重连对象 1: 仅重试任务中失败的对象 2: 重试任务中所有的对象
        } else {
            // Ajax Data 高级配置赋值
            ajaxData.retry_strategy.task_retry_times = 0; // 任务重试重连次数
            ajaxData.retry_strategy.task_retry_interval = 0; // 任务重试重连间隔时间
            ajaxData.retry_strategy.task_retry_object = ''; //任务重连对象
        }

        // 2.ajax data 赋值
        ajaxData.speedInfo = getSpeedStrategyInfo();// 限速策略
        ajaxData.thread_num = threadNum; // 传输线程
        let applianceNode = $('#transferAgentTree').transferAgent('getSelect'); // 获取传输代理
        
        switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
                ajaxData.typeInfo.high.trasfer.encrypt = $('#transport_encrypt_flag').get(0).checked; // 是否加密传输
                ajaxData.typeInfo.high.trasfer.encrypt_method = encryptMethod; // 传输加密算法

                if ($('#transferNetwork').val()) { // 传输网络
                    ajaxData.typeInfo.high.trasfer.network = $('#transferNetwork').val();
                }

                ajaxData.highInfo.dir_tree_recovery_flag = $('#file_skip_dir_tree_flag').get(0).checked; // 目录树恢复
                ajaxData.highInfo.same_file_strategy = $('#file_same_name_handle_type').val(); // 同名文件处理

                break;
            case STORAGE_DEVICE_TYPE.NAS: // NAS设备
                ajaxData.highInfo.dir_tree_recovery_flag = $('#file_skip_dir_tree_flag').get(0).checked; // 目录树恢复
                ajaxData.highInfo.same_file_strategy = $('#file_same_name_handle_type').val(); // 同名文件处理
                
                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // Hadoop
                ajaxData.typeInfo.high.trasfer.encrypt = $('#transport_encrypt_flag').get(0).checked; // 是否加密传输
                ajaxData.typeInfo.high.trasfer.encrypt_method = encryptMethod; // 传输加密算法
                ajaxData.typeInfo.high.trasfer.appliance_agency_flag = $('#appliance_agency_flag').get(0).checked; // 是否开启传输代理
                ajaxData.typeInfo.high.trasfer.appliance_uuid = $('#appliance_agency_flag').get(0).checked ? applianceNode.agent_uuid : ''; // 传输代理
                ajaxData.typeInfo.high.appliance_pool_uuid = $('#appliance_agency_flag').get(0).checked ? applianceNode.agent_pool_uuid : ''; // 传输代理资源池uuid

                ajaxData.highInfo.dir_tree_recovery_flag = $('#file_skip_dir_tree_flag').get(0).checked; // 目录树恢复
                ajaxData.highInfo.same_file_strategy = $('#file_same_name_handle_type').val(); // 同名文件处理
                // ajaxData.highInfo.link_file_pass_flag = $('#clear-shortcut').get(0).checked; // 无效快捷方式清理
                // ajaxData.highInfo.permission_operate_flag = $('#file_permission_recovery_flag').get(0).checked; // 文件权限恢复
                ajaxData.recoveryType = 1; // 对齐hadoop 创建恢复任务接口

                break;
            case STORAGE_DEVICE_TYPE.OBS: // 对象存储
                ajaxData.typeInfo.high.trasfer.encrypt = $('#transport_encrypt_flag').get(0).checked; // 是否加密传输
                ajaxData.typeInfo.high.trasfer.encrypt_method = encryptMethod; // 传输加密算法
                ajaxData.typeInfo.high.trasfer.appliance_agency_flag = $('#appliance_agency_flag').get(0).checked; // 是否开启传输代理
                ajaxData.typeInfo.high.trasfer.appliance_uuid = $('#appliance_agency_flag').get(0).checked ? applianceNode.agent_uuid : ''; // 传输代理
                ajaxData.typeInfo.high.appliance_pool_uuid = $('#appliance_agency_flag').get(0).checked ? applianceNode.agent_pool_uuid : ''; // 传输代理资源池uuid
               
                ajaxData.highInfo.dir_tree_recovery_flag = $('#obs_skip_dir_tree_flag').get(0).checked; // 去除前缀
                ajaxData.highInfo.same_file_strategy = $('#obs_same_name_handle_type').val(); // 同名文件处理
                ajaxData.highInfo.permission_operate_flag = $('#obs_permission_recovery_flag').get(0).checked; // 对象权限恢复

                break;
            default:
                break;
        }

        // 过载保护 - 忽略节点资源限制
        ajaxData.highInfo.ignore_resource_limiting_flag = $('#ignore_resource_limiting_flag').get(0).checked;

        // 3.确认表单回显
        confirmRecoverForm(completeConfig.str);

        return true;
    }

    //<----------------------------- END STEP THREE RECOVER WAY ------------------------------------->

    //<----------------------------- BEGIN STEP FOUR CONFIRM CONFIGURATION -------------------------->

    const submit = () => {
        let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备
        let api = '';

        if('' === $.trim($("#jobname").val())){
            $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
            return;
        }
        let jobName = $.trim($("#jobname").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }

        ajaxData.job_name = $.trim($("#jobname").val());

        switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
                api = '/api/v1/files/recover_job';
                break;
            case STORAGE_DEVICE_TYPE.NAS: // NAS设备
                api = '/api/v1/nas/recover_job';
                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // Hadoop
                api = '/api/v1/hadoop/jobs/restore';
                break;
            case STORAGE_DEVICE_TYPE.OBS: // 对象存储
                api = '/api/v1/s3/recover_job';
                break;
            default:
                break;
        }

        Metronic.blockUI({target: '#obs_recover_content',animate: true, cenrerY: true,});
        pAjaxRequest(ajaxData, api, 'POST', (result) => {
            if (result.success) {
                UIToastr.showSuccess(LANG.UI_OBS_CREATE_RECOVER_TASK_SUCCESS);

                LOCATION('./content/platform/jobs/jobs.php', 'task');
            } else {
                UIToastr.showWarning(LANG.UI_OBS_CREATE_RECOVER_TASK_FAILED, result.message);
            }

            Metronic.unblockUI('#obs_recover_content');
        })
    }

    //<----------------------------- END STEP FOUR CONFIRM CONFIGURATION ---------------------------->


    const wizardInit = () => {
        if (!jQuery().bootstrapWizard) {
            return;
        }
        let form = $('#submit_form');
        let error = $('.alert-danger', form);
        let success = $('.alert-success', form);

        const handleTitle = (tab, navigation, index) =>{
            let total = navigation.find('li').length;
            let current = index + 1;
            jQuery('li', $('#obs_recover_content')).removeClass("done");
            let li_list = navigation.find('li');

            for (let i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current === 1) {
                $('#obs_recover_content').find('.button-previous').css('visibility', 'hidden');
                $('#obs_recover_content').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#obs_recover_content').find('.button-previous').css('visibility', 'visible');
                $('#obs_recover_content').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#obs_recover_content').find('.button-next').hide();
                $('#obs_recover_content').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#obs_recover_content').find('.button-next').show();
                $('#obs_recover_content').find('.button-submit').css('visibility', 'hidden');
            }

            Metronic.scrollTo($('.page-title'));
        }

        $('#obs_recover_content').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch(index){
                    case 1:
                        if(step1Valid() === false){
                            return false;
                        }
                        break;
                    case 2:
                        if(step2Valid() === false){
                            return false;
                        }
                        break;
                    case 3:
                        if(step3Valid() === false){
                            return false;
                        }
                        break;
                }

                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                let total = navigation.find('li').length;
                let current = index + 1;
                let $percent = (current / total) * 100;
                $('#obs_recover_content').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#obs_recover_content').find('.button-previous').css('visibility', 'hidden');
        $('#obs_recover_content .button-submit').click(submit).css('visibility', 'hidden');
    }

    /**
     * 初始化传输代理列表
     */
    const initApplianceAgencyOLD = () => {
        pAjaxRequest({offset: 0, limit: 1000, transfer_agent_flag: 1, h_online_status: 1}, 'api/v1/agents', 'GET', (result) => {
             if (result.success) {
                 if (result.data.rows.length > 0) {
                     let data = result.data.rows;
                     let applianceSelect = $('#applianceSelect');
 
                     applianceSelect.empty();
             
                     for (let i = 0; i < data.length; i++){
                         let option = $("<option>").text(data[i].agent_ip).val(data[i].agent_uuid);
                         applianceSelect.append(option);
                     }

                     APPLIANCE_AGENCY_HAS_CONFIGED = true;
                 } else {
                    APPLIANCE_AGENCY_HAS_CONFIGED = false;
                 }
             } else {
                APPLIANCE_AGENCY_HAS_CONFIGED = false;

                UIToastr.showWarning(LANG.UI_OBS_GET_APPLIANCE_AGENCY_FAILED, result.message);
             }
        });
    }

    /**
     * 初始化传输代理
     */
    const initApplianceAgency = () => {
        $('#transferAgentTree').transferAgent();
    }

    /**
	 * 初始化日期选择插件
	 */
	const inintDatatimePicker = function() {
		//初始化日期时间选择控件
		$('#daterangepicker').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),	//默认结束时间
			"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
			"timePicker": true,													//是否显示时间,时分
			"timePicker24Hour": true,											//是否是24小时制
			"alwaysShowCalendars": true,										//是否总是显示日期选择
			"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
			"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function(start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
		});

		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#daterangepicker').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			// _daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
			searchTimeRange();
		});

		$('#daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
			//清除全局变量,然后设置input
			_daterangepicker_starttime = "";
			_daterangepicker_endtime = "";
			// _daterangepicker_range = "";
			$(this).val('');
			searchTimeRange();
		});
		//input右侧的图标事件
		$('.src-wrap__content__daterangepicker i').click(function() {
			$(this).parent().find('input').click();
		});
	}

    const initListener = () => {

        // 初始化时间范围选择器
        inintDatatimePicker();

        $('#tobackup').on('click', function() {
	    	LOCATION('./content/s3/obsbackup.php', 'obs_protect');
		});

        // 开始恢复搜索
		$('#reco-search-btn').on("click", startSearch);

		// 停止搜索
		$('span[name="stop"]').on("click", stopSearch);

        // 点击清除筛选
		$(".clearval").click(function () {
			clearSearchVal();
		});

		// 点击全选
		$(".selectall").click(function () {
			selectAllResult();
		});

		// 点击取消选择
		$(".cancelselect").click(function () {
			cancelSelect();
		});

		// 恢复目标客户端搜索
		$('#searchHost').on('propertychange', searchClientTreeNode).on('input', searchClientTreeNode);;

        // 选择恢复到的类型 1 -> fs, 2 -> nas, 3 -> obs, 4 -> hadoop
		$('#deviceSelect').on('change', function() {
			if (hostTree) {
				hostTree.checkAllNodes(false);
			}

            if (pathTree) {
                pathTree.checkAllNodes(false);
            }

            let $deviceLabel = $(".devicelabel");

            switch (parseInt(this.value)) {
                case STORAGE_DEVICE_TYPE.FILE: // fs
                    $deviceLabel.html(LANG.UI_VOL_CDP_BACKUP_CLIENT_SELECT);
                    $('#pathtype').val('2');
                    $('#pathtype option[value=1]').hide();
                    initFileClientTree();
                    break;
                case STORAGE_DEVICE_TYPE.NAS: // nas
                    $deviceLabel.html(LANG.UI_NAS_DEVICE_SELECT);
                    $('#pathtype').val('2');
                    $('#pathtype option[value=1]').hide();
                    initNasClientTree();
                    break;
                case STORAGE_DEVICE_TYPE.HADOOP: // hadoop
                    $deviceLabel.html(LANG.UI_OBS_SELECT_HADOOP);
                    $('#pathtype').val('2');
                    $('#pathtype option[value=1]').hide();
                    initHadoopClientTree();
                    break;
                case STORAGE_DEVICE_TYPE.OBS: // hadoop
                    $deviceLabel.html(LANG.UI_OBS_SELECT_OBS);
                    $('#pathtype option[value=1]').show();
                    initObsClientTree();
                    break;
                default:
                    break;
            }

			$('#searchHost').val('');
			$('#selectPath').hide();
            $('#pathtreediv').hide();
		});

        // 选择恢复路径
        $('#pathtype').on('change', function() {
			let selectPath = $('#selectPath');
			if ('1' == this.value){
				// 原路径恢复
				UIToastr.showInfo(LANG.UI_OBS_OBJECT_RECOVER, LANG.UI_OBS_OBJECT_RECOVER_TIP);
				selectPath.hide();
                $('#pathtreediv').hide();
			} else if ('2' == this.value){
				// 手动选择路径恢复
				let treenode = hostTree.getCheckedNodes();
                if (treenode.length > 0) {
                    selectPath.show();
                    $('#pathtreediv').show();

                    initObsFilePathTree(treenode[0]);
                } else {
                    selectPath.hide();
                    $('#pathtreediv').hide();
                }
			}
		});

        // 搜索备份时间点树
		$('#searchfs').on('input propertychange',function(){
			searchFS();
		});

        // 初始化传输线程
        $('#recoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});

        // 传输代理switch change
        $('#appliance_agency_flag').on('switchChange.bootstrapSwitch', applianceSwitchChange);

        // 加密传输 switch change
        $('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptSwitchChange);

        // 操作异常重试 - 自动重试 switch change
        $('#op_retry_flag').on('switchChange.bootstrapSwitch', opRetrySwitchChange);
        
        // 任务重试 - 自动重试 switch change
        $('#task_retry_flag').on('switchChange.bootstrapSwitch', taskRetrySwitchChange);

        initHighListeners(); // 线程数量des

        initObsClientTree(); // 初始化对象存储列表

        initApplianceAgency(); // 获取代理传输列表

        // 初始化完整性校验异常处理单选框组
        $('#tab_safety input[type=radio]').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});

        if (CONF.LANGUAGE !== "zh-cn" && CONF.LANGUAGE !== "zh-tw") {
            //英文独有的
            $(".form_datetime").datetimepicker({
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-mm-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        } else {
            $(".form_datetime").datetimepicker({
                language:  'zh-CN',
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-MM-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }

        // 切换时间策略类型
        $('#recovertype').on('change', function() {
            let recoverType = parseInt($('#recovertype').val());

            if (recoverType === 1) {
                $('.setOnceTime').addClass('display-none');
            } else {
                $('.setOnceTime').removeClass('display-none');
            }

			initTimeStrategyDes();
		});

        $('#resetdate').on('click', function () {
			$('#oncetime').val('');
			initTimeStrategyDes();
		});

		$('#oncetime').on('change', initTimeStrategyDes);

        $('#task_retry_flag').bootstrapSwitch('state', false); // 创建备份默认关闭任务自动重试
        $('.task-retry-wrap').hide();
    }

    const initSpinner = () => {
        $('#network_retry_times_spinner').spinner({value: 10, step: 5, min: 1, max: 60}); // 网络重连次数
        $('#network_retry_interval_spinner').spinner({value: 60, step: 5, min: 5, max: 60}); // 网络重连间隔时间
        $('#op_retry_times_spinner').spinner({value: 3, step: 1, min: 1, max: 5}); // 操作重连次数
        $('#op_retry_interval_spinner').spinner({value: 60, step: 5, min: 1, max: 60}); // 操作重连间隔时间
        $('#task_retry_times_spinner').spinner({value: 3, step: 1, min: 1, max: 5}); // 任务重连次数
        $('#task_retry_interval_spinner').spinner({value: 10, step: 5, min: 1, max: 60}); // 任务重连间隔时间
    }

    return {
        init: () => {
            initPointShowType();
            wizardInit();
            initSpinner();
            initListener();
        }
    }
}();

jQuery(document).ready(function() {
    ObsRecover.init();
});