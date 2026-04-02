(function ($) {
    $.fn.baseTableCacheConfig = function () {
        var table_id = 'current_pending_table';
        var pageSessionRecord = JSON.parse(sessionStorage.getItem('' + table_id + '_pageRecord'));
        var filterFlag = false;
        var scrollPosition = 0;
        var data = {}; //导出全部专用参数
        let cache = {}; // 缓存已加载的数据
        let currentPage = 1; // 当前页码
        let pageLimit = 10; // 默认每页数据
        let currentOrder = []; // 当前页面的顺序
        let lastSearchParams = {}; // 用于保存上一次的查询参数

        var addListeners = function (options) {
            //监听导出全部数据
            $('' + options.toolbarId + ' .exportAll').off().on('click', () => {
                data.offset = 0;
                //导出参数不需要limit
                data.limit = undefined;
                if (options.vin_url) { //web_ng适配
                    $.ajax({
                        url: options.exportAllConfig.url,
                        beforeSend: function (XMLHttpRequest) {
                            //设置headers
                            // XMLHttpRequest.setRequestHeader("X-Csrf-Token", _Token); //接口认证
                            XMLHttpRequest.setRequestHeader("x-api-version", "1.0-rev0"); //当前接口版本
                            // XMLHttpRequest.setRequestHeader("Authorization", _AuthToken); //身份认证
                        },
                        method: 'POST',
                        data: data,
                        xhrFields: {
                            responseType: 'blob',
                        },
                        success: (response) => {
                            let currentDate = new Date();
                            let currentDateTime = currentDate.toLocaleString();
                            let url = URL.createObjectURL(response);
                            let a = document.createElement('a');
                            a.href = url;
                            a.download = '' + options.exportAllConfig.fileName + '(' + currentDateTime + ').xlsx';
                            a.click();
                            URL.revokeObjectURL(url);
                        }
                    });
                } else {
                    $.ajax({
                        url: options.url,
                        method: 'POST',
                        data: {
                            m: options.exportAllConfig.module,
                            f: options.exportAllConfig.funName || "", //需要在options里面添加导出全部的配置，默认为空不传
                            p: JSON.stringify(data),
                        },
                        xhrFields: {
                            responseType: 'blob',
                        },
                        success: (response) => {
                            let currentDate = new Date();
                            let currentDateTime = currentDate.toLocaleString();
                            let url = URL.createObjectURL(response);
                            let a = document.createElement('a');
                            a.href = url;
                            a.download = '' + options.exportAllConfig.fileName + '(' + currentDateTime + ').xlsx';
                            a.click();
                            URL.revokeObjectURL(url);
                        }
                    });
                }
            })

            var displayFlag = false;

            $(options.toolbarId).off().on('click', '.' + table_id + 'clear', function () {
                $('#' + table_id + '').val('');
                $('.' + table_id + 'clear').removeClass('show');
                sessionStorage.removeItem("search");
                $('.search input').attr('placeholder', options.placeholder);
                $('#' + table_id + '').bootstrapTable('resetSearch');
            })

            //输入框清除按钮
            $('.' + options.searchClass).on('focus', function () {
                $('.' + table_id + 'clear').addClass('show');
                $('.search input').removeAttr('placeholder');
            });

            $('.' + options.searchClass).on('blur', function () {
                if ($('.' + options.searchClass).val() == '') {
                    $('.' + table_id + 'clear').removeClass('show');
                    $('.search input').attr('placeholder', options.placeholder);
                };
            });

            $('' + options.toolbarId + ' .clear').off('click').on('click', function () {
                $(options.searchSelector).val('');
                $('' + options.toolbarId + ' .clear').removeClass('show');
                $('.search input').val('');
                $('.search input').attr('placeholder', options.placeholder);
            });

            // 保存排序按钮
            $('#sortSubmit').off('click').on('click', function () {
                if (currentOrder.length == 0) {
                    // 未进行任何操作
                    $('#current_pending_task_drawer').drawer('hide');
                    return;
                }

                // 收集所有 job_uuid
                let allChangeIds = [];
                for (let page in cache) {
                    if (!cache.hasOwnProperty(page)) continue;
                    var allChangeId = [];
                    const rows = cache[page].rows || [];
                    for (let j = 0; j < rows.length; j++) {
                        allChangeId.push(rows[j].job_uuid);
                    }
                    allChangeIds.push({page: page, ids: allChangeId});
                }
                // console.log('排序ID列表:', allChangeIds); // 可以调试用
                Metronic.blockUI({target: '#current_pending_task_drawer', animate: true});
                pAjaxRequest({ids: allChangeIds}, `/api/v1/jobs/pending`, 'PUT', res => {
                    Metronic.unblockUI('#current_pending_task_drawer');
                    if (!res.success) {
                        UIToastr.showWarning(res.title, res.message);
                        return;
                    }
                    $('#current_pending_task_drawer').drawer('hide');
                    UIToastr.showSuccess(res.title, res.message);
                });
            })
        }

        function initTableHeight(options) {
            //拿到父窗口的高度
            var height;
            var panelH = window.innerHeight;
            //如果是模态框内部
            if ($(`#${table_id}`).closest('.modal-body').length > 0) {
                //有数据显示分页
                if ($(`#${table_id}`).parent().parent().next('.fixed-table-pagination').is(':visible')) {
                    var modalHeight = $(`#${table_id}`).closest('.modal-body').height();
                    $(`#${table_id}`).parent().css({
                        "height": modalHeight - 106
                    });
                } else {
                    //无数据隐藏分页
                    $(`#${table_id}`).parent().css({
                        "height": 200
                    });
                }
                return;
            }
            //拿到提示框高度
            if ($(document).find('.alert-info').length >= 1) {
                var tipHeight = $('.alert-info').outerHeight();
                //数字从左到右依次为顶层导航栏，page-content，portlet-title，portlet-body，vin_toolbar，thead的高度
                height = panelH - 46 - 40 - 20 - 40 - 46 - 40 - tipHeight;
            } else {
                height = panelH - 46 - 40 - 20 - 40 - 46 - 40;
            }

            if ($('.breadcrumb').length > 0) {
                height -= 38;
            }
            //减去分页栏的高度
            height -= 52;

            //如果有高级搜索就会显示搜索项，这时需要再多搜索项的高度（主要为日志告警页面）
            if ($(options.searchDiv).is(':visible')) {
                height -= $(options.searchDiv).outerHeight();
            }

            //计算表格container该设置的高度
            $(`#${table_id}`).parent().css({
                "height": height
            });
        }

        //本地缓存定义
        var oStorage = {
            _Storage: window.localStorage,
            // 是否支持缓存
            isSupportStorage: function () {
                return this._Storage != undefined ? true : false;
            },
            // 是否有缓存
            hasItem: function (item) {
                return this._Storage.hasOwnProperty(item);
            },
            // 获取缓存key-value形式
            getItem: function (item) {
                // 将json字符串转成对象或数组
                return JSON.parse(this._Storage.getItem(item));
            },
            // 设置缓存key-value形式
            setItem: function (item, val) {
                // 将对象或数组转成json字符串
                return this._Storage.setItem(item, JSON.stringify(val));
            }
        };

        // 表格载入成功时执行的函数
        function loadSuccess(tid, options) {
            // 第一次载入时没有缓存，默认隐藏的列存入缓存中
            if (!(oStorage.isSupportStorage() && oStorage.hasItem(tid + "_BsTable")) && options.hideColumns != undefined) {
                var aTmpConfig = options.hideColumns.split(',');
                oStorage.setItem(tid + "_BsTable", aTmpConfig);
            }

            // 从本地缓存中获取用户配置
            if (options.hideColumns != undefined) {
                var aUserConfHideCols = oStorage.getItem(tid + "_BsTable");
                if (aUserConfHideCols != "") {
                    // 遍历用户配置中的数据，为了避免以前用过的环境在修改列的字段后，之前存入的字段名不匹配导致报错，显示不出任务列表，使用try...catch语句捕获异常抛出
                    aUserConfHideCols.forEach(function (sField) {
                        // 通过hideColumn方法隐藏列
                        try {
                            $('#' + tid + '').bootstrapTable('hideColumn', sField);
                        } catch (e) {
                            console.log('Unimportant exceptions');
                        }
                    });
                };
            }
        }

        // 初始化拖拽
        function initDragAndDrop() {
            const tbody = document.querySelector('#' + table_id + ' tbody');
            Sortable.create(tbody, {
                animation: 150,
                onEnd: function (evt) {
                    console.log('Row moved:', evt.oldIndex, 'to', evt.newIndex);
                    updateCurrentOrder();
                }
            });
        }

        // 更新排序
        function updateCurrentOrder() {
            currentOrder = [];
            let currentPageRows = [];
            let data = cache[currentPage];
            $('#' + table_id + ' tbody tr').each(function () {
                const id = $(this).data('uniqueid'); // 获取每行的唯一标识符
                currentOrder.push(id);

                // 根据ID从原始数据中找到对应的行数据
                const item = data.rows.find(item => item.job_uuid === id);
                if (item) {
                    currentPageRows.push(item);
                }
            });
            // 更新缓存中的当前页数据
            cache[currentPage] = {
                rows: currentPageRows,
                total: cache[currentPage].total
            };
            console.log('Current order:', currentOrder);
        }

        // 置顶操作
        function updateDataWithNewOrder(data, newOrder) {
            // Step 1: 找出要置顶的 item
            const topItemUuid = newOrder[0];
            const topItem = data.rows.find(item => item.job_uuid === topItemUuid);
            if (!topItem) return data; // 如果找不到对应数据，不处理

            // Step 2: 如果当前是第一页，只需要把这个 item 移动到最前面
            if (currentPage === 1) {
                let newData = [topItem];

                // 添加其余元素（除了这个 item）
                for (let i = 0; i < data.rows.length; i++) {
                    if (data.rows[i].job_uuid !== topItemUuid) {
                        newData.push(data.rows[i]);
                    }
                }

                cache[1] = { rows: newData, total: data.total };
                return cache[currentPage];
            }

            // Step 3: 非第一页，需要进行跨页操作
            // 1. 先从当前页删除这个 item
            let currentPageRows = [...cache[currentPage].rows]; // 当前页的数据数组
            currentPageRows = currentPageRows.filter(i => i.job_uuid !== topItemUuid); // 删除目标项
            cache[currentPage].rows = currentPageRows;

            // 2. 开始逐层向前置换，将每个页面的最后一条向前推
            let currentsPage = currentPage; // 记录当前操作的页码，针对有些页码没加载出来的
            for (let p = currentPage; p > 1; p--) {
                let prevPage = p - 1;
                if (cache[prevPage] != undefined && cache[prevPage].rows != undefined) {
                    // 获取前一页的最后一条
                    let prevLastItem = cache[prevPage].rows[cache[prevPage].rows.length - 1];

                    // 插入当前页的顶部
                    cache[currentsPage].rows.unshift(prevLastItem);

                    // 删除前一页的最后一条
                    cache[prevPage].rows.pop();
                    currentsPage --;
                }
            }

            // 3. 最后把 moveItem 插入到第一页的最前面
            cache[1].rows.unshift(topItem);

            // 返回刷新用的数据
            return cache[currentPage];
        }

        // 切换列时执行的函数
        function columnSwitch(tid, toolbarId) {
            // tid表示表格id，必须独一为二
            var aTmpConfig = [];
            // 找到特定id下的所有input框，遍历，i表示索引，v表示元素
            $("" + toolbarId + " .keep-open .dropdown-menu").find("input").each(function (k, v) {
                // 判断未勾选的列，获得字段属性存入定义的数组中
                if (this.checked === false) {
                    aTmpConfig.push($(v).attr("data-field"));
                };
            });

            // 通过setItem方法，以key-value形式存入缓存中
            oStorage.setItem(tid + "_BsTable", aTmpConfig);
        }

        //详情页模板格式化
        var lastIndex = [-1, -1];
        var detailFormatter1 = function (index, row, options) {
            if (index != lastIndex[1]) {
                lastIndex.push(index);
                $('#' + table_id + '').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                lastIndex.splice(0, 1);
            }
            return '<p><span style="margin-right: 20px">'+LANG.UI_PLATFORM_PENDING_JOB_MSG+'：</span>'+row.pending_msg+'</p>';
        };

        //文本溢出显示省略号
        var cellStyle = function (value, row, index, field) {
            return {
                css: {
                    "white-space": "nowrap",
                    "text-overflow": "ellipsis",
                    "overflow": "hidden",
                    "max-width": "36px ",
                }
            };
        };

        //表头样式
        var headerStyle = function (column) {
            return {
                css: {
                    'font-family': 'Microsoft YaHei-Regular, Microsoft YaHei',
                    'font-size': '14px',
                    'position': 'sticky',
                    'top': 0,
                    'background-color': '#F7F9FA',
                    'z-index': 999,
                    'color': '#393C4D',
                    "white-space": "nowrap",
                    "text-overflow": "ellipsis",
                    "overflow": "hidden",
                    "max-width": "36px",
                    "font-weight": 400
                }
            };
        };

        //当模块类型未选中时处理任务类型
        var switchJobType = function (job_type) {
            var new_job_type = [];
            $.each(job_type, function (k, v) {
                switch (v) {
                    case '1': //备份
                        new_job_type.push(1, 28, 32, 35);
                        break;
                    case '2': //恢复
                        new_job_type.push(2, 29, 33, 36);
                        break;
                    case '17': //副本
                        new_job_type.push(17, 30, 38);
                        break;
                    case '19': //归档
                        new_job_type.push(19, 40);
                        break;
                    case '8': //迁移
                        new_job_type.push(CONF.TASK_TYPE.VM_INSTANT_RECOVERY_MOTION, CONF.TASK_TYPE.VM_CDP_INSTANT_RECOVERY_MOTION, CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION);
                        break;
                    case '7': //瞬时恢复
                        new_job_type.push(CONF.TASK_TYPE.OS_INSTANT_RECOVERY, CONF.TASK_TYPE.INSTANT_RECOVERY);
                        break;
                    default:
                        new_job_type.push(job_type);
                        break;
                }
            })

            return new_job_type;
        }

        function shouldClearCache(newParams) {
            // 排除 offset 和 limit 参数后进行比较
            const keysToCompare = Object.keys(newParams).filter(key => key !== 'offset' && key !== 'limit');
            for (let key of keysToCompare) {
                if (lastSearchParams[key] !== newParams[key]) {
                    return true; // 如果有任意一个参数不同，则返回true，表示需要清空缓存
                }
            }
            return false;
        }

        function updateSearchParams(newParams) {
            // 更新最后一次使用的搜索参数
            lastSearchParams = { ...newParams };
        }

        return {
            init: function (options) {
                currentPage = pageSessionRecord != null ? pageSessionRecord.offset + 1 : 1; // 当前页码
                pageLimit = pageSessionRecord ? pageSessionRecord.limit : 10; // 每页数量
                //新的ajax接口
                var ajaxRequest = function (params) {
                    if (options.vin_url) {
                        var info = {};
                        var finalInfo = {};
                        var module = [];
                        $('#' + table_id + '').bootstrapTable('showLoading');

                        $.each(params.data, function (k, v) {
                            info[k] = v;
                        })

                        ///过滤参数组合
                        var filters = {};
                        $.each(options.filterOption, function (index, value) {
                            filters[value] = [];
                            $('' + options.toolbarId + ' #' + value + ' input:checkbox:checked').each(function () {
                                filters[value].push($(this).attr('value'));
                            })
                            //特别处理任务类型转换
                            if (value == 'module_type') {
                                module = filters.module_type;
                            }
                            if (value == 'job_type') {
                                //特别处理任务类型转换
                                if (filters.module_type != '') {
                                    filters.job_type = switchParams(module, filters.job_type);
                                } else {
                                    filters.job_type = switchJobType(filters.job_type);
                                }
                            }
                        })

                        if (filterFlag == false) {
                            var filtersInfo = {};
                            $.each(JSON.parse(sessionStorage.getItem('' + table_id + '_filters')), function (k, v) {
                                if (k == 'module_type') {
                                    var moduleType = [];
                                    var subModuleType = [];
                                    $.each(v, function (index, value) {
                                        var splitModule = value.split('-');
                                        if (splitModule.length > 1) {
                                            moduleType.push(splitModule[0]);
                                            subModuleType.push(splitModule[1]);
                                        } else {
                                            moduleType.push(splitModule[0]);
                                        }
                                    })
                                    filtersInfo[k] = moduleType.join();
                                    filtersInfo['sub_module_type'] = subModuleType.join();
                                } else {
                                    if (k == 'job_type') {
                                        //特别处理任务类型转换
                                        if (filtersInfo.module_type != '' && filtersInfo.module_type != undefined) {
                                            filtersInfo[k] = switchParams(filtersInfo['module_type'].split(','), v).join();
                                        } else {
                                            filtersInfo[k] = switchJobType(v).join();
                                        }
                                    } else {
                                        filtersInfo[k] = v.join();
                                    }
                                }
                                if (filtersInfo[k] == '') {
                                    filtersInfo[k] = undefined;
                                }
                                if (filtersInfo['sub_module_type'] == '') {
                                    filtersInfo['sub_module_type'] = undefined;
                                }
                            })
                        } else {
                            var filtersInfo = {};
                            $.each(filters, function (k, v) {
                                if (k == 'module_type') {
                                    var moduleType = [];
                                    var subModuleType = [];
                                    $.each(v, function (index, value) {
                                        var splitModule = value.split('-');
                                        if (splitModule.length > 1) {
                                            moduleType.push(splitModule[0]);
                                            subModuleType.push(splitModule[1]);
                                        } else {
                                            moduleType.push(splitModule[0]);
                                        }
                                    })
                                    filtersInfo[k] = moduleType.join();
                                    filtersInfo['sub_module_type'] = subModuleType.join();
                                } else {
                                    filtersInfo[k] = v.join();
                                }
                                if (filtersInfo[k] == '') {
                                    filtersInfo[k] = undefined;
                                }
                                if (filtersInfo['sub_module_type'] == '') {
                                    filtersInfo['sub_module_type'] = undefined;
                                }
                            });
                        }

                        if (options.vin_params) {
                            var vin_params = options.vin_params();
                        }
                        var finalInfo = $.extend({}, info, vin_params, filtersInfo);
                        finalInfo.sort = info.sort;
                        finalInfo.order = info.order;

                        finalInfo.offset = isNaN(finalInfo.offset) ? 0 : finalInfo.offset;

                        if (options.vin_params && vin_params.accurateFlag) {
                            //精确搜索时offset置为0
                            var accurateFlag = vin_params.accurateFlag;
                            if (accurateFlag == true) {
                                finalInfo.offset = 0;
                            }
                        }


                        if (options.dateTimePicker) {
                            var start_time = $('#' + options.dateTimePicker.id + '').attr('start_time');
                            var end_time = $('#' + options.dateTimePicker.id + '').attr('end_time');
                            finalInfo.start_time = start_time;
                            finalInfo.end_time = end_time
                        }

                        $.each(finalInfo, function (k, v) {
                            if (finalInfo[k] == null || finalInfo[k] === "") {
                                if (k == "offset" || k == "search") {} else {
                                    finalInfo[k] = undefined;
                                }
                            }
                        })
                        // 这里需要判断下当前的请求参数，是否和上次一致，除了offset和limit之外
                        // 如果不一致，需要清空缓存
                        if (shouldClearCache(finalInfo) || pageLimit != finalInfo.limit) {
                            cache = {}; // 清空缓存
                            currentOrder = []; // 清空保存的排序
                        }

                        // 更新最新的请求参数
                        updateSearchParams(finalInfo);
                        if (cache[currentPage] != undefined && cache[currentPage].total != undefined) {
                            params.success({});
                            $('#' + table_id + '').bootstrapTable('load', cache[currentPage]);
                            $('#' + table_id + '').bootstrapTable('hideLoading');
                        } else {
                            // var data;
                            pAjaxRequest(finalInfo, options.vin_url, options.vin_method, options.responseHandler || function (res) {
                                if (res.success) {
                                    var res = {
                                        "total": res.data.total,
                                        "rows": res.data.rows,
                                    }

                                    //必须为bootstrap-table的ajax传入的params的success传入一个对象，空的即可，以便ajax可以正确使用
                                    params.success({});
                                    cache[currentPage] = res;
                                    $('#' + table_id + '').bootstrapTable('load', res);
                                    // 初始化Popover
                                    let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="popover"]'));
                                    popoverTriggerList.map((popoverTriggerEl) => {
                                        $(popoverTriggerEl).popover()
                                    });
                                    $('#' + table_id + '').bootstrapTable('hideLoading');
                                } else {
                                    operateResponseList(res);
                                    params.error({});
                                }
                            }, true);
                        }
                    } else {
                        //必须为bootstrap-table的ajax传入的params的success传入一个对象，空的即可，以便ajax可以正确使用
                        params.success({});
                    }
                };
                //任务的操作事件
                var operates = {
                    'click .move-top-btn': function (event, value, row, index) {
                        updateCurrentOrder();
                        const itemId = row.job_uuid;
                        const updatedOrder = [itemId].concat(currentOrder.filter(id => id !== itemId));
                        cache[currentPage] = updateDataWithNewOrder(cache[currentPage], updatedOrder);
                        $('#' + table_id).bootstrapTable('load', cache[currentPage]);
                    },
                }
                var DEF_OPTIONS = {
                    toolbarId: options.toolbarId ? options.toolbarId : '.vin_toolbar',
                    buttonsToolbar: '.vin_pending_btnToolbar',
                    classes: "table  table-hover table-borderless", //表的类名
                    cache: false, //是否开启缓存
                    search: true, //是否开启搜索
                    searchSelector: '.' + options.searchClass,
                    showJumpTo: true,
                    searchAlign: 'left',
                    method: 'get', //请求方式
                    paginationLoop: false,
                    searchOnEnterKey: true,
                    sortable: true, //是否开启排序
                    loadingFontSize: '13px',
                    loadingTemplate: function (loadingMessage) {
                        return '<span class="loading-wrap">' +
                            '<span class="loading-text">' +
                            loadingMessage +
                            '</span>' +
                            '<span class="animation-wrap"><span class="animation-dot"></span></span>' +
                            '</span>'
                    },
                    onRefresh: function (params) {
                        if (cache[currentPage]) {
                            $('#' + table_id + '').bootstrapTable('load', cache[currentPage]);
                        }
                    },
                    onLoadSuccess: function (data) {
                        loadSuccess(table_id, options);
                        initDragAndDrop();
                        if ($('#' + table_id + '').find('.no-records-found').length > 0) {
                            $('#' + table_id + ' thead .bs-checkbox input[type=checkbox]').prop('disable', true);
                        }
                        if (options.LoadSuccess) {
                            options.LoadSuccess();
                        }

                        $(options.tableArea + ' .fixed-table-body').off().on('scroll', function () {
                            scrollPosition = $(options.tableArea + ' .fixed-table-body').scrollTop();
                        });
                    },
                    onPostBody: function () {
                        $(options.tableArea + ' .fixed-table-body').scrollTop(scrollPosition);
                        if (options.PostBody != undefined) {
                            options.PostBody();
                        }
                        if (options.fullPage) {
                            initTableHeight(options);
                        }
                        if (options.oneHundredPercentHeight) {
                            $(options.tableArea + ' .fixed-table-body').css('height', '100%');
                        }
                    },
                    //bootstrap-table的重排序列事件，columns参数表示排序后的列的顺序数组，afterOption表示排序后的配置项
                    onReorderColumn: function (columns, afterOption) {
                        if (options.reorderableColumns) {
                            refreshReorderColsArr = columns;
                            columnReordered(table_id, columns, afterOption);
                        }
                    },
                    // exportTypes: ['json', 'xml', 'png', 'csv', 'txt', 'sql', 'doc', 'excel', 'xlsx', 'pdf'], //所有可导出类型默认 ['json', 'xml', 'csv', 'txt', 'sql', 'excel']
                    // exportDataType: 'all',
                    exportOptions: {
                        fileName: function () {
                            let currentDate = new Date();
                            let currentDateTime = currentDate.toLocaleString();
                            if (options.exportAllConfig) {
                                //导出全部时文件名自定义
                                return options.exportAllConfig.fileName + '(' + currentDateTime + ')';
                            } else if (options.fileName) {
                                //待传入自定义默认导出的文件名
                                return options.fileName;
                            } else {
                                return 'tableExport' + '(' + currentDateTime + ')';
                            }
                        },
                    },
                    // onResetView: function () {
                    //     $('#' + table_id).bootstrapTable('resetView');
                    // },
                    headerStyle: headerStyle,
                    pagination: true, //是否开启分页
                    showExport: true, //是否开启导出按钮
                    silent: true,
                    showColumns: true, //是否开启列选择按钮
                    showLoading: false,
                    buttonsPrefix: 'btn b-btn btn-title', //所有按钮样式类名，在最前方加
                    // buttonsClass: 'table',
                    icons: {
                        export: 'viconfont vicon-a-Share-threefenxiang3', //按钮工具的图标类，在最后加
                        detailOpen: 'detail-open',
                        detailClose: 'detail-close',
                        columns: 'icon-columns',
                    },
                    pageList: options.pageList ? options.pageList : [5, 10, 25, 50], //每页的记录数组
                    smartDisplay: false,
                    detailView: false, //是否开启点击查看详情页
                    detailFormatter: detailFormatter1, //详情页模板格式化
                    minimumCountColumns: 2, //最少显示列数
                    clickToSelect: true, //点击选中行
                    queryParamsType: 'limit', //参数格式为limit可获取RESTFul类型参数‘limit, offset, search, sort, order’
                    sidePagination: "server", //	分页方式，client和server
                    pageSize: pageSessionRecord ? pageSessionRecord.limit : 10, //默认每页条数
                    dataType: "json", //	数据类型
                    strictSearch: false, //	严格搜索
                    trimOnSearch: false, //	true设置为修剪搜索字段中的空格
                    pageNumber: pageSessionRecord != null ? pageSessionRecord.offset + 1 : 1, //	默认起始页
                    resizable: true,
                    columns: [ //列定义
                        {
                            field: 'job_name', //字段名
                            title: LANG.UI_SEARCH_TASK_NAME,
                            sortable: false, //默认可排序，禁用排序才写此项
                        },
                        /*{
                            field: 'business_type',
                            title: LANG.UI_BUSINESS_TYPE,
                            sortable: false, //默认可排序，禁用排序才写此项
                            formatter: function (index, row) {
                                return `<span title="${row.business_type.text}">${row.business_type.text}</span>`;
                            }
                        },*/
                        {
                            field: 'module_type_value',
                            title: LANG.UI_SEARCH_OBJ_TYPE,
                            sortable: false, //默认可排序，禁用排序才写此项
                            formatter: function (index, row) {
                                return `<span title="${row.module_type}">${row.module_type}</span>`;
                            }
                        },
                        {
                            field: 'job_type_value',
                            title: LANG.UI_SEARCH_TASK_TYPE,
                            sortable: false, //默认可排序，禁用排序才写此项
                            formatter: function (index, row) {
                                if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //vol_cdp验证任务特殊处理
                                    return `<span title="${LANG.UI_VOL_CDP_VERIFY_DESC}">${LANG.UI_VOL_CDP_VERIFY_DESC}</span>`;
                                } else {
                                    return `<span title="${row.job_type}">${row.job_type}</span>`;
                                }
                            }
                        },
                        {
                            field: 'create_time',
                            title: LANG.UI_TAPE_PENDING_TIME,
                            sortable: false, //默认可排序，禁用排序才写此项
                        },
                        {
                            field: 'current_stage_value',
                            title: LANG.UI_PLATFORM_JOB_STAGE,
                            sortable: false, //默认可排序，禁用排序才写此项
                        },
                        {
                            field: 'job_status_value',
                            title: LANG.UI_PUBLIC_STATUS,
                            // 所有的type: "label",都是在bs-table.js中定义的formatter
                            type: "label",
                            sortable: false, //默认可排序，禁用排序才写此项
                        },
                        {
                            field: 'duration_time',
                            title: LANG.UI_PUBLIC_CONTINUE_RUN_TIME,
                            sortable: false, //默认可排序，禁用排序才写此项
                        },
                        {
                            title: LANG.UI_PUBLIC_OPERATION,
                            sortable: false,
                            clickToSelect: false, //不可通过点击行选中
                            // 所有的type: "xxx",都是在bs-table.js中定义的formatter
                            type: "operation",
                            width: "125px",
                            events: operates, //单元点击事件
                            forceHide: true,
                        }
                    ],
                    onColumnSwitch: function (field, checked) {
                        if (options.hideColumns) {
                            columnSwitch(table_id, options.toolbarId);
                        }
                        if (options.columnsSwitch) {
                            options.columnsSwitch();
                        }
                    },
                    onPreBody: function () {
                        // 解决跳转页输入框只能输入数字
                        $(".page-jump-to input[type='number']").attr("onkeyup", "this.value=this.value.replace(/\D/g,'')");
                        $(".page-jump-to input[type='number']").attr("onafterpaste", "this.value=this.value.replace(/\D/g,'')");
                    },
                    onPageChange: function (number, size) {
                        var pageRecord = {
                            offset: number - 1,
                            limit: size
                        }
                        currentPage = number;
                        $('#'+table_id).bootstrapTable('refresh', {query: pageRecord});
                        sessionStorage.setItem('' + table_id + '_pageRecord', JSON.stringify(pageRecord));
                    }
                }

                var web_ng_options = {
                    ajax: ajaxRequest,
                }
                // 合并参数
                options = $.extend(true, DEF_OPTIONS, web_ng_options, options);

                //重排序列的读取
                var aUserConfReorderColumns = oStorage.getItem(table_id + "_ColReorder");
                if (options.reorderableColumns && aUserConfReorderColumns) {
                    var aUserConfReorderColumns = oStorage.getItem(table_id + "_ColReorder");
                    // 根据 aUserConfReorderColumns 数组的顺序对 options的columns 按照field字段进行排序
                    var sortedArray = options.columns.sort((a, b) => {
                        const indexA = aUserConfReorderColumns.indexOf(a.field);
                        const indexB = aUserConfReorderColumns.indexOf(b.field);

                        // 如果 a.field 在 aUserConfReorderColumns 中的位置小于 b.field 的位置，则 a 应在 b 之前
                        if (indexA < indexB) return -1;
                        // 反之，则 b 应在 a 之前
                        if (indexA > indexB) return 1;
                        // 如果两者在 aUserConfReorderColumns 中的位置相同，或者任一不在 aUserConfReorderColumns 中，则保持原顺序
                        return 0;
                    });
                    options.columns = sortedArray;
                }

                //列处理
                options.columns = $.each(options.columns, function (k, v) {
                    v.align = 'left';

                    if (v.sortable === false) {
                        // return;
                    } else if (v.sortable == undefined) {
                        v.sortable = true
                    };

                    switch (v.type) {
                        case 'label':
                            // 状态转义,多处使用
                            v.formatter = function (value) {
                                switch (value) {
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
                                        return '<span class="label label-sm label-success label-success_en ">' + LANG.UI_PUBLIC_STARTING + '</span>';

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
                                    case CONF.TASK_STATUS.DELETING:
                                        return '<span class="label label-sm label-info label-info_en">' + LANG.UI_NODE_STATUS_DELETING + '</span>';
                                    case CONF.TASK_STATUS.CLEANING:
                                        return '<span class="label label-sm label-warning label-warning_en">' + LANG.UI_NODE_STATUS_CLEANING + '</span>';
                                    default:
                                        // return '<span class="label label-sm label-info  ">准备中</span>';
                                        break;
                                }

                            };
                            break;
                        case 'operation':
                            // 操作按钮格式化, 任务和GMP使用
                            v.formatter = function (value, row, index, field) {
                                var uuid = row.job_uuid;
                                var button = '<button class="btn btn-table move-top-btn change_sort" title="'+LANG.UI_PLATFORM_PENDING_JOB_TIPS+'" data-id="' + uuid + '"><i class="viconfont vicon-a-To-topqudingbu"></i></button>';
                                return button;
                            }
                            break;
                        default:
                            if (v.formatter == undefined) {
                                //如果没有自定义formatter
                                v.formatter = function (value, row, index, field) {
                                    return `<span title = "${value}">${value}</span>`;
                                }
                                //只有在formatter没有定义时才给到默认的样式，以供拖拽显示 '...'
                                if (v.cellStyle == undefined) {
                                    v.cellStyle = cellStyle;
                                }
                            } else {
                                //如果自定义了，且为button
                                if (v.opButton) {
                                    //预留opButton操作空间
                                } else {
                                    //如果自定义了，且不为button
                                    if (v.cellStyle == undefined) {
                                        v.cellStyle = cellStyle;
                                    }
                                }
                            }
                            break;
                    }
                    v.type = undefined;
                });

                //改变高度
                if (options.changeHeightBtn === true && $('' + options.toolbarId + '').find('#change-pending-height').length < 1) {
                    $('' + options.toolbarId + ' .rightTool').prepend(`<div id="change-pending-height" ">
					<button class="btn btn-primary btn-table change_height" title = "` + LANG.UI_TOOLS_TABLE_CHANGE_HEIGHT +`">
						<i class="icon-auto-height1"></i>
					</button>
				</div>`)
                }

                //自定义工具栏
                var customToolFlag = false;
                if (options.customTool) {
                    if (options.customTool.beforeInput && $('' + options.toolbarId + ' .customBtn1').html() == 0) {
                        $('' + options.toolbarId + ' .customBtn1').append(options.customTool.beforeInput);
                    }
                    if (options.customTool.afterInput && $('' + options.toolbarId + ' .customBtn2').html() == 0) {
                        $('' + options.toolbarId + ' .customBtn2').append(options.customTool.afterInput);
                    }
                    if (options.customTool.beforeAdvance && $('' + options.toolbarId + ' .customBtn3').html() == 0) {
                        $('' + options.toolbarId + ' .customBtn3').append(options.customTool.beforeAdvance);
                    }
                    if (options.customTool.afterAdvance && $('' + options.toolbarId + ' .customBtn4').html() == 0) {
                        $('' + options.toolbarId + ' .customBtn4').append(options.customTool.afterAdvance);
                    }
                    customToolFlag = true;
                } else {
                    customToolFlag = false;
                }

                $(' #' + table_id + ' ').bootstrapTable(options);

                if (options.exportAllConfig && $('' + options.toolbarId + ' .export').find('.exportAll').length < 1) {
                    //导出按钮新增导出所有选项
                    $('' + options.toolbarId + ' .export .dropdown-menu').append('<li role="menuitem" class="exportAll" data-type="all"><a href="javascript:void(0)">' + LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL + '</a></li>');
                }

                //导出按钮修改中文title
                $('' + options.toolbarId + ' .export button').prop('title', LANG.UI_TOOLS_TABLE_EXPORT_DATA);

                addListeners(options); //初始化监听事件
            }
        };
    }
})(jQuery)
