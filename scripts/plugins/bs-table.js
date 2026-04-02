(function ($) {
    $.fn.baseTableConfig = function () {
        const TABLE_ID = this.prop('id'); // 表格 id
        const headerStyle = function () {
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
        let scrollPosition = 0;

        function initTableHeight(options) {
            //拿到父窗口的高度
            var height;
            var panelH = window.innerHeight;
            //如果是模态框内部
            if ($(`#${TABLE_ID}`).closest('.modal-body').length > 0) {
                //有数据显示分页
                if ($(`#${TABLE_ID}`).parent().parent().next('.fixed-table-pagination').is(':visible')) {
                    var modalHeight = $(`#${TABLE_ID}`).closest('.modal-body').height();
                    $(`#${TABLE_ID}`).parent().css({
                        "height": modalHeight - 106
                    });
                } else {
                    //无数据隐藏分页
                    $(`#${TABLE_ID}`).parent().css({
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
            $(`#${TABLE_ID}`).parent().css({
                "height": height
            });
        }

        //本地缓存定义
        const oStorage = {
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
        };

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
                $('#' + TABLE_ID + '').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                lastIndex.splice(0, 1);
            }

            var html = [];
            $.each(row, function (key, value) {
                html.push('<p>' + key + ': ' + value + '</p>');
            })

            return html.join('');
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

        /**
         * 初始化导出菜单
         * @param {*} options 
         */
        const initExportMenu = (options) => {
            const $toolbar = $(`${options.toolbarId}`);
            const $exportMenu = $toolbar.find('.export .dropdown-menu');
            const exportSettings = options.exportSettings;

            if (!exportSettings) { // 没有配置导出项时使用表格默认导出项
                return;
            }

            // 筛选内置的导出项，而不是清空它们
            const $builtInItems = $exportMenu.find('li[data-type]');

            // 如果定义了 showBuiltIn，则根据它来筛选
            if (exportSettings.showBuiltIn !== undefined && Array.isArray(exportSettings.showBuiltIn)) {
                $builtInItems.each(function() {
                    const $item = $(this);
                    const type = $item.data('type');
                    if (exportSettings.showBuiltIn.indexOf(type) > -1) {
                        $item.show();
                    } else {
                        $item.hide();
                    }
                });
            } else {
                // 如果没有定义 showBuiltIn，则默认全部显示
                $builtInItems.show();
            }

            // 移除之前添加的自定义导出项，防止重复
            $exportMenu.find('.custom-export-item').remove();

            // 添加自定义导出项
            if (exportSettings.custom && Array.isArray(exportSettings.custom) && exportSettings.custom.length > 0) {
                exportSettings.custom.forEach(item => {
                    const itemClass = item.class || '';
                    const label = item.label || 'Custom Export';
                    const customItem = `<li class="custom-export-item ${itemClass}" data-file-name="${item.fileName}""><a href="javascript:void(0)">${label}</a></li>`;
                    $exportMenu.append(customItem);
                });
            }
        };

        const addListeners = function (options) {

            $(options.toolbarId).off().on('click', '.' + TABLE_ID + 'clear', function () {
                $('#' + TABLE_ID + '').val('');
                $('.' + TABLE_ID + 'clear').removeClass('show');
                sessionStorage.removeItem("search");
                $(`${options.toolbarId} .search input`).attr('placeholder', options.placeholder);
                $('#' + TABLE_ID + '').bootstrapTable('resetSearch');
            });

            $(`${options.toolbarId} .search .customSearch`).on('focus', () => {
                if ($(`${options.toolbarId} .search .customSearch`).val()) {
                    $(`${options.toolbarId} .search .clear`).removeClass('hide');
                }
            });

            $(`${options.toolbarId} .search .customSearch`).on('input', () => {
                if ($(`${options.toolbarId} .search .customSearch`).val()) {
                    $(`${options.toolbarId} .search .clear`).removeClass('hide');
                } else {
                    $(`${options.toolbarId} .search .clear`).addClass('hide');
                }
            });

            $(`${options.toolbarId} .search .clear`).on('click', () => {
                $(`${options.toolbarId} .search .clear`).addClass('hide');
            });
        }

        return {
            init: function (options) {
                //web_ui请求参数
                var queryParams = function (params) {
                    var info = {};
                    var finalInfo = {};

                    $.each(params, function (k, v) {
                        info[k] = v;
                    })

                    var vin_params = options.vin_params();

                    var finalInfo = $.extend(info, vin_params);
                    //精确搜索时offset置为0
                    var accurateFlag = vin_params.accurateFlag;
                    if (accurateFlag == true) {
                        finalInfo.offset = 0;
                    }

                    var paramsInfo = JSON.stringify(finalInfo);
                    return {
                        m: options.M,
                        f: options.F,
                        p: paramsInfo,
                    };
                };

                //新的ajax接口
                var ajaxRequest = function (params) {
                    if (options.vin_url) {
                        var info = {};
                        var finalInfo = {};
                        $('#' + TABLE_ID + '').bootstrapTable('showLoading');

                        $.each(params.data, function (k, v) {
                            info[k] = v;
                        })

                        if (options.vin_params) {
                            var vin_params = options.vin_params();
                        }
                        var finalInfo = $.extend({}, info, vin_params);

                        if (options.dateRangePickerId) { // 表格toolbar日期选择器id
                            let daterangepickerText = $(`#${options.dateRangePickerId} .daterangepicker-text`).text();

                            if (daterangepickerText && daterangepickerText !== LANG.UI_DATERANGEPICKER_NO_TIME) {
                                finalInfo.start_time = daterangepickerText.split(' - ')[0];
                                finalInfo.end_time = daterangepickerText.split(' - ')[1];
                            }
                        }

                        finalInfo.sort = info.sort;
                        finalInfo.order = info.order;

                        finalInfo.offset = isNaN(finalInfo.offset) ? 0 : finalInfo.offset;

                        if (finalInfo.job_name) {
                            finalInfo.search = ''; //精确搜索时
                        }

                        if (options.vin_params && vin_params.accurateFlag) {
                            //精确搜索时offset置为0
                            var accurateFlag = vin_params.accurateFlag;
                            if (accurateFlag == true) {
                                finalInfo.offset = 0;
                            }
                        }

                        $.each(finalInfo, function (k, v) {
                            if (finalInfo[k] == null || finalInfo[k] === "") {
                                if (k == "offset" || k == "search") {} else {
                                    finalInfo[k] = undefined;
                                }
                            }
                        })

                        // var data;
                        pAjaxRequest(finalInfo, options.vin_url, options.vin_method, options.responseHandler || function (res) {
                            if (res.success) {
                                var res = {
                                    "total": res.data.total,
                                    "rows": res.data.rows,
                                }
                                // 如果出现有数据，但是查询返回没有列表，则说明当前页超过最大页数
                                if (res.total != 0 && res.rows.length == 0) {
                                    // 重定向到第一页
                                    $('#' + TABLE_ID + '').bootstrapTable('selectPage', 1);
                                    return;
                                }
                                //必须为bootstrap-table的ajax传入的params的success传入一个对象，空的即可，以便ajax可以正确使用
                                params.success({});
                                $('#' + TABLE_ID + '').bootstrapTable('load', res);
                                // 初始化Popover
                                let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="popover"]'));
                                popoverTriggerList.map((popoverTriggerEl) => {
                                    $(popoverTriggerEl).popover()
                                });

                                // 总条数小于5条就隐藏分页器
                                if (res.total === 0 || res.total < 5) {
                                    $(`#${TABLE_ID}`).parent().parent().next('.fixed-table-pagination').hide();
                                } else {
                                    $(`#${TABLE_ID}`).parent().parent().next('.fixed-table-pagination').show();
                                }

                                // 将 total 存到实例的data属性中，以便表格列切换隐藏显示时用于判断是否显示分页
                                $(`#${TABLE_ID}`).data('total', res.total);

                                $('#' + TABLE_ID + '').bootstrapTable('hideLoading');
                            } else {
                                operateResponseList(res);
                                params.error({});
                            }
                        }, async = true);

                    } else {
                        //必须为bootstrap-table的ajax传入的params的success传入一个对象，空的即可，以便ajax可以正确使用
                        params.success({});
                    };
                };

                var DEF_OPTIONS = {
                    toolbarId: options.toolbarId ? options.toolbarId : '.vin_toolbar',
                    buttonsToolbar: '.vin_btnToolbar',
                    classes: "table  table-hover table-borderless", //表的类名
                    cache: false, //是否开启缓存
                    search: true, //是否开启搜索
                    paginationLoop: false,
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
                    onLoadSuccess: function (data) {
                        loadSuccess(TABLE_ID, options);

                        initExportMenu(options);

                        if ($('#' + TABLE_ID + '').find('.no-records-found').length > 0) {
                            $('#' + TABLE_ID + ' thead .bs-checkbox input[type=checkbox]').prop('disable', true);
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

                        $('[data-toggle="tooltip"]').tooltip();
                    },
                    //bootstrap-table的重排序列事件，columns参数表示排序后的列的顺序数组，afterOption表示排序后的配置项
                    onReorderColumn: function (columns, afterOption) {
                        if (options.reorderableColumns) {
                            refreshReorderColsArr = columns;
                            columnReordered(TABLE_ID, columns, afterOption);
                        }
                    },
                    exportOptions: {
                        fileName: function () {
                            let currentDate = new Date();
                            let currentDateTime = currentDate.toLocaleString();
                            if (options.fileName) {
                                //待传入自定义默认导出的文件名
                                return options.fileName;
                            } else {
                                return 'tableExport' + '(' + currentDateTime + ')';
                            }
                        },
                    },
                    // onResetView: function () {
                    //     $('#' + TABLE_ID).bootstrapTable('resetView');
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
                    pageList: options.pageList ? options.pageList : [5, 10, 25, 50, 100, 200], //每页的记录数组
                    smartDisplay: false,
                    detailView: false, //是否开启点击查看详情页
                    detailFormatter: detailFormatter1, //详情页模板格式化
                    minimumCountColumns: 2, //最少显示列数
                    clickToSelect: true, //点击选中行
                    queryParamsType: 'limit', //参数格式为limit可获取RESTFul类型参数‘limit, offset, search, sort, order’
                    sidePagination: "server", //	分页方式，client和server
                    pageSize: 10, //默认每页条数
                    dataType: "json", //	数据类型
                    strictSearch: false, //	严格搜索
                    trimOnSearch: false, //	true设置为修剪搜索字段中的空格
                    pageNumber: 1, // 默认起始页
                    resizable: true,
                    onColumnSwitch: function (field, checked) {
                        // 监听表格列筛选隐藏显示，判断是否显示分页
                        let totalCount = $(`#${TABLE_ID}`).data('total');

                        // 总条数小于5条就隐藏分页器
                        if (totalCount === 0 || totalCount < 5) {
                            $(`#${TABLE_ID}`).parent().parent().next('.fixed-table-pagination').hide();
                        } else {
                            $(`#${TABLE_ID}`).parent().parent().next('.fixed-table-pagination').show();
                        }

                        if (options.hideColumns) {
                            columnSwitch(TABLE_ID, options.toolbarId);
                        }
                        
                        if (options.columnsSwitch) {
                            options.columnsSwitch();
                        }
                    },
                    onPreBody: function () {
                        // 解决跳转页输入框只能输入数字
                        $(".page-jump-to input[type='number']").attr("onkeyup", "this.value=this.value.replace(/\D/g,'')");
                        $(".page-jump-to input[type='number']").attr("onafterpaste", "this.value=this.value.replace(/\D/g,'')");
                    }
                }

                //新旧api适配
                var web_ui_options = {
                    url: options.url,
                    method: options.method,
                    queryParams: queryParams,
                    contentType: "application/x-www-form-urlencoded", //http请求头
                }

                var web_ng_options = {
                    ajax: ajaxRequest,
                }
                // 合并参数
                if (options.vin_url) {
                    options = $.extend(true, DEF_OPTIONS, web_ng_options, options);
                } else {
                    options = $.extend(true, DEF_OPTIONS, web_ui_options, options);
                }

                //重排序列的读取
                var aUserConfReorderColumns = oStorage.getItem(TABLE_ID + "_ColReorder");
                if (options.reorderableColumns && aUserConfReorderColumns) {
                    var aUserConfReorderColumns = oStorage.getItem(TABLE_ID + "_ColReorder");
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
                        case 'module':
                            // 模块转义
                            v.formatter = function (value, row) {
                                switch (value) {
                                    case 2:
                                        if (2 == row.sub_module_type_value) {
                                            return '<span>' + LANG.UI_PUBLIC_PRIVATE_CLOUD + '</span>';
                                        }
                                        if (3 == row.sub_module_type_value) {
                                            return '<span>' + LANG.UI_PUBLIC_PUBLIC_CLOUD + '</span>';
                                        }
                                        return '<span>' + LANG.UI_PUBLIC_VM + '</span>';
                                    case 3:
                                        if (row.sub_module_type_value == 1) {
                                            return '<span>' + LANG.UI_VISUAL_FILE + '</span>';
                                        }
                                        if (row.sub_module_type_value == 2) {
                                            return '<span>' + LANG.UI_VISUAL_NAS + '</span>';
                                        }
                                        if (row.sub_module_type_value == 3) {
                                            return '<span>' + LANG.UI_VISUAL_HADOOP + '</span>';
                                        }
                                        if (row.sub_module_type_value == 4) {
                                            return '<span> ' + LANG.UI_VISUAL_OBS + ' </span>';
                                        }
                                        return '<span>' + LANG.UI_VISUAL_FILE + '</span>';
                                    case 4:
                                        return '<span>' + LANG.UI_VISUAL_DB + '</span>';
                                    case 5:
                                        if (1 == row.sub_module_type_value) {
                                            return '<span>' + LANG.UI_JOB_TYPE_MACHINE_OS + '</span>';
                                        }else{
                                            return '<span>' + LANG.UI_PLATFORM_DES_OS + '</span>';
                                        }

                                    case 6:
                                        return '<span>VDDT_SERVER</span>';
                                    case 7:
                                        return '<span>VDDT_CLIENT</span>';
                                    case 8:
                                        return '<span>' + LANG.UI_VISUAL_COPY_BACKUP + '</span>';
                                    case 9:
                                        return '<span>' +LANG.UI_VISUAL_COPY_OR_ARCHIVE_NEW+ '</span>';
                                    case 10:
                                        return '<span>' + LANG.UI_VISUAL_SERVER_CDP + '</span>';
                                    case 11:
                                        return '<span>' + LANG.UI_VISUAL_NAS+ '</span>';
                                    case 12:
                                        return '<span>' + LANG.UI_VIRTUAL_DATABASE_REAL_TIME+ '</span>';
                                    case 14:
                                        return '<span>Microsoft365</span>';
                                    case 17:
                                        return '<span>' + LANG.UI_PLATFORM_DES_AWS + '</span>';
                                    case 26:
                                        return '<span>' + LANG.UI_FILE_COPY_DES + '</span>';
                                    case 28:
                                        return '<span>' + LANG.UI_BACKUP_DATA_MODULE_K8S + '</span>';
                                    case 30:
                                        return '<span>' + LANG.UI_JOB_DATA_VERTIFY + '</span>';
                                    case 10000:
                                        return '<span>' + LANG.UI_VIRTUAL_DATABASE_REAL_TIME + '</span>';
                                    default:
                                        break;
                                }
                            };
                            v.cellStyle = cellStyle;
                            break;
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
                                        return '<span class="label label-sm label-info label-info_en">' + LANG.UI_NODE_STATUS_CLEANING + '</span>';
                                    default:
                                        // return '<span class="label label-sm label-info  ">准备中</span>';
                                        break;
                                }

                            };
                            break;
                        case 'operation':
                            // 操作按钮格式化, 任务和GMP使用
                            v.formatter = function (value, row, index, field) {
                                var opCode = row.op_list;
                                var uuid = row.job_uuid;

                                let task_type_value = row.job_type_value;
                                var button = '<div class="btn-group dropdown-wrapper">';
                                if (index > 5) {
                                    button = '<div class="btn-group dropup dropdown-wrapper">';
                                }

                                button += `<button type="button" id="dropdown_operate_${uuid}" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                                                ${LANG.UI_PUBLIC_OPERATION} <i class="fa fa-angle-down"></i>
                                            </button>`;

                                if ($.inArray(13, opCode) != -1) {
                                    button += '<ul class="dropdown-menu" role="menu" id=' + uuid + '>';
                                } else if ($.inArray(17, opCode) != -1) {
                                    button += '<ul class="dropdown-menu" role="menu" id=' + uuid + '>';
                                } else {
                                    button += '<ul class="dropdown-menu" role="menu" id=' + uuid + '>';
                                }

                                $.each(opCode, function (i, d) {
                                    switch (d) {
                                        case CONF.TASK_CONTROL.START: // 启动任务
                                            if ([17, 26, 30, 38, 44].includes(row.job_type_value)) {
                                                if (row.copy_mode == 1) {
                                                    button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START_MIRROR_COPY + '</button></li>';
                                                }
                                            } else if (CONF.VENDOR == CONF.VENDOR_LIST.gmp && row.job_type_value == CONF.TASK_TYPE.SURE_BACKUP) {
                                                // gmp
                                                button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_PLATFORM_TEMPLATE_START_VERIFY + '</button></li>';
                                            } else {
                                                var des = LANG.UI_JOB_START;
                                                if(row.job_type_value == 37){
                                                    des = LANG.UI_VERIFY_START;
                                                }
                                                button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + des + '</button></li>';
                                            }
                                            break;
                                        case CONF.TASK_CONTROL.STOP: // 停止
                                            button += '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.MODIFY: // 修改
                                            button += '<div class="separator"></div><li class="edit"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_JOB_MODIFY + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.DELETE: // 删除任务
                                            button += '<li class="delete"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_delete me-4"></i> ' + LANG.UI_JOB_DELETE + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.PAUSE: // 暂停
                                            button += '<li class="pause"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_PAUSE + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.START_DIFF: // 启动差异
                                            // if (options.resModule.subModule == CONF.VM_TYPE.INSPURVVDK) break;
                                            button += '<li class="startDiff"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_differentia_backup me-4"></i> ' + LANG.UI_JOB_START_DIFFRENCE + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.START_INCR: // 启动增量
                                            if (task_type_value == 17) {
                                                if (row.copy_mode == 2) {
                                                    button += '<li class="startIncr"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_increment me-4"></i> ' + LANG.UI_JOB_START_INCREASE_COPY + '</button></li>';
                                                }
                                            } else {
                                                button += '<li class="startIncr"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_increment me-4"></i> ' + LANG.UI_JOB_START_INCREMENT + '</button></li>';
                                            }
                                            break;
                                        case CONF.TASK_CONTROL.START_STRATEGY: // 启动策略
                                            button += '<li class="startStra"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_time_point me-4"></i> ' + LANG.UI_JOB_START_STRATEGY + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.MIGRATION: // 迁移
                                            if (4 == CONF.SOFTWARE) break;
                                            button += '<li class="motion"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_migration me-4"></i> ' + LANG.UI_MOTION_NAME + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.START_FULL: // 启动完备
                                            if (task_type_value == 17) {
                                                if (row.copy_mode == 2) {
                                                    button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> '+ LANG.UI_JOB_START_FULL_COPY +'</button></li>';
                                                }
                                            } else {
                                                button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START_FULL + '</button></li>';
                                            }
                                            break;
                                        case CONF.TASK_CONTROL.START_TAKEOVER: // 启动接管
                                            if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //vol_cdp验证任务
                                                button += '<li class="takeover"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-vol_cdp_takeover me-4"></i> ' + LANG.UI_JOB_START_VERIF + '</button></li>';
                                            } else {
                                                button += '<li class="takeover"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-vol_cdp_takeover me-4"></i> ' + LANG.UI_JOB_START_TAKEOVER + '</button></li>';
                                            }
                                            break;
                                        case CONF.TASK_CONTROL.STOP_TAKEOVER: // 停止接管
                                            if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //vol_cdp验证任务
                                                button += '<li class="stoptakeover"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_stop_over me-4"></i> ' + LANG.UI_JOB_STOP_VERIF + '</button></li>';
                                            } else {
                                                button += '<li class="stoptakeover"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_stop_over me-4"></i> ' + LANG.UI_JOB_STOP_TAKEOVER + '</button></li>';
                                            }
                                            break;
                                        case CONF.TASK_CONTROL.LOG_BACKUP: // 日志备份
                                            if (row.job_type_value == 28 && (row.db_type == CONF.DB_TYPE.ORACLE || row.db_type == CONF.DB_TYPE.DM ||
                                                row.db_type == CONF.DB_TYPE.POSTGRE || row.db_type == CONF.DB_TYPE.KINGBASE ||
                                                row.db_type == CONF.DB_TYPE.UXDB || row.db_type == CONF.DB_TYPE.HIGHGO || row.db_type == CONF.DB_TYPE.OPENGAUSS ||
                                                row.db_type == CONF.DB_TYPE.VASTBASE || row.db_type == CONF.DB_TYPE.ANTDB)) {
                                                button += '<li class="startLog"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_running_log me-4"></i> ' + LANG.UI_JOB_START_ARCHIVE_LOG_BACKUP + '</button></li>';
                                            } else {
                                                button += '<li class="startLog"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_log me-4"></i> ' + LANG.UI_JOB_START_LOG_BACKUP + '</button></li>';
                                            }
                                            break;
                                        case CONF.TASK_CONTROL.START_FAILBACK: // 启动回切
                                            if (row.takeover_agent_role != CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //vol_cdp验证任务
                                                button += '<li class="startfailback"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_cutback me-4"></i> ' + LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK + ' </button></li>';
                                            }
                                            break;
                                        //				case 15:
                                        //					button += '<li class="stopfailback"><a class="btn dropdown-menu__item me-0" type="button" ><i class="fa fa-square"></i>停止接管12</a></li>';
                                        //					break;
                                        case CONF.TASK_CONTROL.CONTINUE: // 创建标签
                                            button += '<li class="createlable"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_sign me-4"></i> ' + LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABEL + ' </button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.AUTO_TAKEOVER: // 自动接管
                                            if (row.auto_takeover_flag == 1) { // 配置了自动接管
                                                if (row.auto_takeover_enable_flag == 1) { // 启用过自动接管则显示 禁用自动接管 按钮
                                                    button += '<li class="stopautotakeover"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_stop_over me-4"></i> ' + LANG.UI_VOL_CDP_STOP_AUTO_TAKEOVER + ' </button></li>';
                                                } else if (row.auto_takeover_enable_flag == 2) { // 未启用自动接管则显示 启用自动接管按钮
                                                    button += '<li class="startautotakeover"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-vol_cdp_takeover me-4"></i> ' + LANG.UI_VOL_CDP_START_AUTO_TAKEOVER + ' </button></li>';
                                                }
                                            }
                                            break;
                                        case 41:
                                            button += '<li class="stopfailback"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_cutback me-4"></i> ' + LANG.UI_JOB_STOP_DBCDP_FAILBACK + ' </button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.START_COPY: // 启动复制
                                            button += '<li class="startcopy"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START_COPY + ' </button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.START_COMPARE: // 启动对比
                                            button += '<li class="startcompare"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START_COMPARE + ' </button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.FORCE_DELETE: // 强制删除
                                            button += '<li class="deleteforce"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_delete me-4"></i> ' + LANG.UI_JOB_FORCE_DELETE + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.FORCE_STOP: // 强制停止
                                            button += '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.STOP_MOTION: // 停止迁移
                                            button += '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_STOP_MOTION + '</button></li>';
                                            break;
                                        case CONF.TASK_CONTROL.FINISH_MOTION: // 完成迁移
                                            button += '<li class="finishMotion"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-qianyichenggong me-4"></i> ' + LANG.UI_PLATFORM_RECOVERY_JOB_STOP_INSTANT_OPERATION + '</button></li>';
                                            break;
                                    }
                                });
                                button += '</ul></div>';
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

                // 搜索框
                if (options.searchInput === true && $('' + options.toolbarId + '').find('' + options.searchSelector + '').length < 1) {
                    toolbarId = options.toolbarId;
                    $('' + options.toolbarId + ' .leftTool').append(`
                    <div class='customBtn1'></div>
                    <div class="search input-group mr12">
                        <input class="` + options.searchClass + ` customSearch" autocomplete="off" type="text" style="padding-right:32px" maxlength="64" placeholder="` + (options.placeholder || "") + `">
                        <div class="position0" style="width:auto;height:34px">
                            <button class="b-btn ` + TABLE_ID + `clear clear hide position0"><i class="icon-close-small"></i></button>
                        </div>
                        <div class="positionL0" style="width:auto;height:34px;">
                            <button class="b-btn search-btn"><i class="icon-search"></i></button>
                        </div>
                    </div>
                    <div class='customBtn2'></div>`);
                }

                //改变高度
                if (options.changeHeightBtn === true && $('' + options.toolbarId + '').find('#change-height').length < 1) {
                    $('' + options.toolbarId + ' .rightTool').prepend(`<div id="change-height" ">
					<button class="btn btn-primary btn-table change_height" data-toggle="tooltip" data-placement="bottom" data-trigger=\"hover\" title = "` + LANG.UI_TOOLS_TABLE_CHANGE_HEIGHT +`">
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

                //批量操作
                if (options.batchOperation === true && $(document).find('.batchOperation-left').length < 1) {
                    $('' + options.toolbarId + '').after(`<div class="batchOperation" style="width: 386px;height: 22px;margin-bottom:8px">
                    <div class="batchOperation-left" style="display: flex;height: 22px">
                        <div class="batch-start mr12" id="batch_start">
                            <i class="icon-start-disable"></i>
                            <span class="ml5">` + LANG.UI_JOB_START_STRATEGY + `</span>
                        </div>
    
                        <div class="batch-stop mr12" id="batch_stop">
                            <i class="icon-stop-disable"></i>
                            <span class="ml5">` + LANG.UI_JOB_STOP + `</span>
                        </div>
    
                        <div class="batch-delete mr12" id="batch_delete">
                            <i class="icon-delete-disable"></i>
                            <span class="ml5">` + LANG.UI_JOB_DELETE + `</span>
                        </div>
                    </div>
                </div>`)
                }

                $(' #' + TABLE_ID + ' ').bootstrapTable(options);

                //导出按钮修改中文title
                // $('' + options.toolbarId + ' .export button').prop('title', LANG.UI_TOOLS_TABLE_EXPORT_DATA);

                addListeners(options); //初始化监听事件
            }
        };
    }
})(jQuery)