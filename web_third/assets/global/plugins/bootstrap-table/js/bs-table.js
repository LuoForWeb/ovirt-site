(function ($) {
    $.fn.baseTableConfig = function () {
        var table_id = this.prop('id');
        var pageSessionRecord = JSON.parse(sessionStorage.getItem('' + table_id + '_pageRecord'));
        var totalCount = 0;
        var selectCount = 0;
        var checkIndex;
        var filterFlag = true;
        var toolbarId;

        // var opCode;
        var addListeners = function () {
            //过滤提交
            $('#vin_history_toolbar #filterSubmit').off('click').on('click', function () {
                filterFlag = true;
            });

            // 过滤全选和反选
            $('#vin_history_toolbar #selectAll').on('click', function (e) {
                filterFlag = false;
            });

            $('#vin_current_toolbar #selectNone').on('click', function () {
                filterFlag = false;
            });

            //每次点击checkbox时停止刷新
            $('#vin_current_toolbar #filterDiv .filter-content input[type=checkbox]').on('click', function () {
                filterFlag = false;
            });

            // 搜索时存入session
            // $('.customSearch').on('keyup', function () {
            //     sessionStorage.removeItem("search");
            //     sessionStorage.setItem("search", $('.customSearch').val());
            //     $('#'+table_id+'').bootstrapTable('refresh');
            // })

            var displayFlag = false;
            $('#filterCancel').on('click', function (e) {
                $('#filters').removeClass('show');
                $('.filters').css('background-color', '#fff');
                displayFlag = false;
            });

            //输入框清除按钮
            $('.customSearch').on('focus', function () {
                $('.clear').addClass('show');
                $('.search input').removeAttr('placeholder');
            });

            $('.customSearch').on('blur', function () {
                if ($('.customSearch').val() == '') {
                    $('.clear').removeClass('show');
                    $('.search input').attr('placeholder', '按任务名搜索');
                };
            });

            $('.clear').on('click', function () {
                $('.customSearch').val('');
                $('.clear').removeClass('show');
                sessionStorage.removeItem("search");
                $('.search input').attr('placeholder', '按任务名搜索');
                $('#' + table_id + '').bootstrapTable('resetSearch');
            });

            // 改变表格高度
            // var changeHeightFlag = false
            // $('.change_height button').on('click', function () {
            //     if (changeHeightFlag == false) {
            //         changeHeightFlag = true;
            //         $('.table>tbody>tr>td').css({
            //             'padding-top': '10.25px',
            //             'padding-bottom': '10.25px'
            //         })
            //         $('#change-height button i').addClass('icon-auto-height2');
            //     } else if (changeHeightFlag == true) {
            //         changeHeightFlag = false
            //         $('.table>tbody>tr>td').css({
            //             'padding-top': '4.25px',
            //             'padding-bottom': '4.25px'
            //         })
            //         $('#change-height button i').removeClass('icon-auto-height2');
            //     }
            // });


            // $('.filters').on('click', function () {
            //     if (displayFlag == false) {
            //         $('#filters').addClass('show');
            //         $('.filters').css('background-color', 'rgba(15,191,152,0.1)');
            //         displayFlag = true;
            //     } else if (displayFlag == true) {
            //         $('#filters').removeClass('show');
            //         $('.filters').css('background-color', '#fff')
            //         displayFlag = false;
            //     }
            // });

            //点击过滤菜单外关闭过滤菜单
            $(document).on('click', function (e) {
                if ($(e.target).closest('#test1').length > 0) {

                } else {
                    // 关闭弹框
                    $('#filters').removeClass('show');
                    $('.filters').css('background-color', '#fff');
                    displayFlag = false;
                };

                if ($(e.target).closest('#addList').length > 0) {

                } else {
                    $(".addTaskList").hide();
                    $(".subDiv").hide();
                }
            });

            $(".addTask").on("click", function (e) {
                $(".addTaskList").show();
            })

            $('.addTask').on('click', function () {
                $('#filters').removeClass('show');
                $('.filters').css('background-color', '#fff');
                displayFlag = false;
            });
        }

        var checkRecord = function () {
            var checkArr = [];
            $.each(checkIndex, function (index) {
                checkArr.push(checkIndex[index].job_uuid);
            });
            $('#' + table_id + '').bootstrapTable('checkBy', {
                field: 'job_uuid',
                values: checkArr
            })
        }

        function initTableHeight() {
            //拿到父窗口的高度
            var height;
            var panelH = window.innerHeight;
            //拿到提示框高度
            if ($(document).find('.alert-info').length >= 1) {
                var tipHeight = $('.alert-info').height();
                height = panelH - 351 - tipHeight;
            } else {
                height = panelH - 351;
            }
            //计算表格container该设置的高度
            var container = $("#currentjobdiv .fixed-table-body").css({
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
                    // 遍历用户配置中的数据
                    aUserConfHideCols.forEach(function (sField) {
                        // 通过hideColumn方法隐藏列
                        $('#' + tid + '').bootstrapTable('hideColumn', sField);
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
                $('#' + table_id + '').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                lastIndex.splice(0, 1);
            }

            var html = [];
            $.each(row, function (key, value) {
                html.push('<p>' + key + ': ' + value + '</p>');
            })

            return html.join('');
        };

        //点击任务名超链接事件
        var operateEvents = {
            'click .detail-Page': function (e, value, row, index) {
                window.location.replace("");
                e.preventDefault();
            },
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
                }
            };
        };

        //当模块类型和任务类型同时选择时处理任务类型
        var switchParams = function (module, job_type) {
            var new_job_type = [];
            $.each(module, function (index, value) {
                switch (value) {
                    case "2": //VM
                        $.each(job_type, function (k, v) {
                            if (v == 1) { //虚拟机备份
                                new_job_type.push(1);
                            } else if (v == 2) {
                                new_job_type.push(2);
                            } else {
                                new_job_type.push(v);
                            }
                        })
                        break;
                    case "3": //文件
                        $.each(job_type, function (k, v) {
                            if (v == 1) {
                                new_job_type.push(1);
                            } else if (v == 2) {
                                new_job_type.push(2);
                            } else if (v == 17) {
                                new_job_type.push(26);
                            } else if (v == 19) {
                                new_job_type.push(27);
                            } else {
                                new_job_type.push(v);
                            }
                        })
                        break;
                    case "4": //数据库
                        $.each(job_type, function (k, v) {
                            if (v == 1) {
                                new_job_type.push(28);
                            } else if (v == 2) {
                                new_job_type.push(29);
                            } else if (v == 17) {
                                new_job_type.push(30);
                            } else if (v == 19) {
                                new_job_type.push(31);
                            } else {
                                new_job_type.push(v);
                            }
                        })
                        break;
                    case "5": //OS
                        $.each(job_type, function (k, v) {
                            if (v == 1) {
                                new_job_type.push(35);
                            } else if (v == 2) {
                                new_job_type.push(36);
                            } else if (v == 17) {
                                new_job_type.push(38);
                            } else if (v == 19) {
                                new_job_type.push(39);
                            } else {
                                new_job_type.push(v);
                            }
                        })
                        break;
                    case "11": //NAS
                        $.each(job_type, function (k, v) {
                            if (v == 1) {
                                new_job_type.push(1);
                            } else if (v == 2) {
                                new_job_type.push(2);
                            } else if (v == 17) {
                                new_job_type.push(44);
                            } else if (v == 19) {
                                new_job_type.push(45);
                            } else {
                                new_job_type.push(v);
                            }
                        })
                        break;
                    case "10": //卷CDP
                        $.each(job_type, function (k, v) {
                            if (v == 1) {
                                new_job_type.push(32);
                            } else if (v == 2) {
                                new_job_type.push(33);
                            } else if (v == 17) {
                                new_job_type.push(34);
                            } else {
                                new_job_type.push(v);
                            }
                        })
                        break;
                    default:
                        new_job_type.push(job_type);
                        break;
                }
            });
            return new_job_type;
        }

        //当模块类型未选中时处理任务类型
        var switchJobType = function (job_type) {
            var new_job_type = [];
            $.each(job_type, function (k, v) {
                switch (v) {
                    case '1'://备份
                    new_job_type.push(1,28,32,35);
                        break;
                    case '2'://恢复
                    new_job_type.push(2,29,33,36);
                        break;
                    case '17'://副本
                    new_job_type.push(17,30,38);
                        break;
                    case '19'://归档
                    new_job_type.push(19,40);
                        break;
                    default:
                    new_job_type.push(job_type);
                        break;
                }
            })
            
            return new_job_type;
        }

        return {

            init: function (options) {
                //web_ui请求参数
                var queryParams = function (params) {
                    var info = {};
                    var finalInfo = {};
                    var module = [];
                    $.each(params, function (k, v) {
                        info[k] = v;
                    })
                    //过滤参数组合
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
                            }else{
                                filters.job_type = switchJobType(filters.job_type);
                            }
                        }
                    })

                    var filtersInfo = $.each(filters, function (k, v) {
                        filters[k] = v.join();

                        if (filters[k] === '') {
                            filters[k] = undefined;
                        }
                    });

                    var vin_params = options.vin_params();
                        
                    var finalInfo = $.extend(info, filtersInfo, vin_params);
                    //精确搜索时offset置为0
                    var accurateFlag = vin_params.accurateFlag;
                    if (accurateFlag == true) {
                        finalInfo.offset = 0;
                    }

                    if (options.dateTimePicker) {
                        var start_time = $('#' + options.dateTimePicker.id + '').attr('start_time');
                        var end_time = $('#' + options.dateTimePicker.id + '').attr('end_time');
                        finalInfo.start_time = start_time;
                        finalInfo.end_time = end_time
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
                        var module = [];

                        $.each(params.data, function(k,v){
                            info[k] = v;
                        })

                        //过滤参数组合
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
                                }else{
                                    filters.job_type = switchJobType(filters.job_type);
                                }
                            }
                        })

                        if (filterFlag == false) {
                            var filtersInfo = {};
                            $.each(JSON.parse(sessionStorage.getItem('' + table_id + '_filters')), function (k, v) {
                                filtersInfo[k] = v.join();
                            })
                        } else {
                            var filtersInfo = $.each(filters, function (k, v) {
                                filters[k] = v.join();

                                if (filters[k] === '') {
                                    filters[k] = undefined;
                                }
                            });
                        }

                                                
                        if (options.vin_params) {
                            var vin_params = options.vin_params();
                        }
                        
                        var finalInfo = $.extend(info, filtersInfo, vin_params);
                        
                        if (options.vin_params) {
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
                            if (finalInfo[k] == null || finalInfo[k] == "") {
                                if (k == "offset") {} else {
                                    finalInfo[k] = undefined;
                                }
                            }
                        })

                        // var data;
                        pAjaxRequest(finalInfo, options.vin_url, options.vin_method, options.responseHandler || function (res) {
                            var res = {
                                "total": res.data.total,
                                "rows": res.data.rows,
                            }
                            // data = res;
                            //必须为bootstrap-table的ajax传入的params的success传入一个对象，空的即可，以便ajax可以正确使用
                            params.success({});
                            $('#' + table_id + '').bootstrapTable('load', res);
                            $('#' + table_id + '').bootstrapTable('hideLoading');
                            // return res;
                        }, async = true);

                    } else {
                        //必须为bootstrap-table的ajax传入的params的success传入一个对象，空的即可，以便ajax可以正确使用
                        params.success({});
                    };
                };


                //根据模块类型得到任务详情的页面地址
                var getDetailsUrl = function (module, taskType) {
                    var url = '';
                    if (2 == module) {
                        //虚拟机
                        url = './content/vm/vm_job_details.php';
                        if (7 == taskType) {
                            //瞬时恢复
                            url = "./content/vm/vm_instant_job_details.php";
                        }
                        if (8 == taskType) {
                            //迁移
                            url = "./content/vm/vm_motion_job_details.php";
                        }
                        if (6 == taskType) {
                            //细粒度恢复
                            url = "./content/vm/vm_grain_job_details.php";
                        }

                        //数据验证
                        if (37 == taskType) {
                            url = "./content/platform/dataverification/verification_job_details.php";
                        }
                    } else if (3 == module) {
                        //文件
                        url = './content/fs/fs_job_details.php';
                    } else if (11 == module) {
                        //nas
                        url = './content/nas/nas_job_details.php';
                    } else if (4 == module) {
                        //数据库
                        //			url = 'javascript:;';
                        url = './content/dbprotect/db_job_details.php';
                    } else if (9 == module) {
                        //副本 虚拟机|文件|NAS
                        if (taskType == 17 || taskType == 18 ||
                            taskType == 26 || taskType == 27 ||
                            taskType == 30 || taskType == 31 ||
                            taskType == 38 || taskType == 39 ||
                            taskType == 44 || taskType == 45) {
                            url = './content/vm/vm_copy_job_details.php';
                        }
                        //归档
                        if (taskType == 19 || taskType == 20) {
                            url = './content/platform/archive/archive_job_details.php';
                        }
                    } else if (10 == module) {
                        url = './content/volcdp/cdp_job_details.php';
                    } else if (10000 == module) {
                        //数据库CDP
                        if (21 == taskType) {
                            //实时备份
                            url = "./content/db/db_cdp_job_details.php";
                        }
                        if (22 == taskType) {
                            //数据恢复
                            url = "./content/db/db_recovery_job_details.php";
                        }
                    } else if (10001 == module) {
                        //文件CDP
                        if (24 == taskType) {
                            //实时备份
                            url = "./content/fs/fs_cdp_job_details.php";
                        }
                        if (25 == taskType) {
                            //数据恢复
                            url = "./content/fs/fs_recovery_job_details.php";
                        }
                    } else if (5 == module) {
                        //操作系统
                        url = "./content/os/os_job_details.php";
                    }
                    return url;
                }
                var DEF_OPTIONS = {
                    toolbarId: options.toolbarId ? options.toolbarId : '.vin_toolbar',
                    buttonsToolbar: '.vin_btnToolbar',
                    classes: "table  table-hover table-borderless", //表的类名
                    cache: false, //是否开启缓存
                    search: true, //是否开启搜索
                    searchSelector: '.customSearch',
                    showJumpTo: true,
                    searchAlign: 'left',
                    method: 'get', //请求方式	
                    sortable: true, //是否开启排序
                    // onCheck: function (res) {
                    //     var selectedRow = $('#' + table_id + '').bootstrapTable("getSelections");
                    //     checkIndex = selectedRow;
                    //     $('#' + table_id + ' thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
                    //     if (selectedRow.length == 0) {
                    //         $('.fixed-table-pagination .pull-left .pagination-info span').html('');
                    //     } else if (selectedRow.length > 0) {
                    //         $('.fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>选中' + selectedRow.length + '');
                    //         $('.batch-start').addClass('batch_start_active');
                    //         $('.batch-start i').addClass('icon-start');
                    //         $('.batch-start span').addClass('bgb');
                    //         $('.batch-stop').addClass('batch_stop_active');
                    //         $('.batch-stop i').addClass('icon-stop');
                    //         $('.batch-stop span').addClass('bgb');
                    //         $('.batch-delete').addClass('batch_delete_active');
                    //         $('.batch-delete i').addClass('icon-delete');
                    //         $('.batch-delete span').addClass('bgb');
                    //         if (table_id == 'history_table') {
                    //             $('#delete_historyjob').removeClass('icon-gray-delete');
                    //             $('#delete_historyjob').addClass('icon-white-delete');
                    //             $('#delete_historyjob').css('background-color', '#0FBF98');
                    //         }
                    //     }
                    // },
                    // onUncheck: function () {
                    //     var selectedRow = $('#' + table_id + '').bootstrapTable("getSelections");
                    //     checkIndex = selectedRow;

                    //     if (selectedRow.length == 0) {
                    //         $('#' + table_id + ' thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
                    //         $('.fixed-table-pagination .pull-left .pagination-info span').html('');
                    //         $('.batch-start').removeClass('batch_start_active');
                    //         $('.batch-start i').removeClass('icon-start');
                    //         $('.batch-start span').removeClass('bgb');

                    //         $('.batch-stop').removeClass('batch_stop_active');
                    //         $('.batch-stop i').removeClass('icon-stop');
                    //         $('.batch-stop span').removeClass('bgb');

                    //         $('.batch-delete').removeClass('batch_delete_active');
                    //         $('.batch-delete i').removeClass('icon-delete');
                    //         $('.batch-delete span').removeClass('bgb');
                    //         if (table_id == 'history_table') {
                    //             $('#delete_historyjob').removeClass('icon-white-delete');
                    //             $('#delete_historyjob').addClass('icon-gray-delete');
                    //             $('#delete_historyjob').css('background-color', '#F4F4F5');
                    //         }
                    //     } else if (selectedRow.length > 0) {
                    //         $('#' + table_id + ' thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
                    //         $('.fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>选中' + selectedRow.length + '');
                    //     };
                    // },
                    // onUncheckAll: function () {
                    //     var selectedRow = $('#' + table_id + '').bootstrapTable("getSelections");
                    //     checkIndex = selectedRow;

                    //     $('#' + table_id + ' thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
                    //     $('.fixed-table-pagination .pull-left .pagination-info span').html('');
                    //     $('.batch-start').removeClass('batch_start_active');
                    //     $('.batch-start i').removeClass('icon-start');
                    //     $('.batch-start span').removeClass('bgb');

                    //     $('.batch-stop').removeClass('batch_stop_active');
                    //     $('.batch-stop i').removeClass('icon-stop');
                    //     $('.batch-stop span').removeClass('bgb');

                    //     $('.batch-delete').removeClass('batch_delete_active');
                    //     $('.batch-delete i').removeClass('icon-delete');
                    //     $('.batch-delete span').removeClass('bgb');
                    //     if (table_id == 'history_table') {
                    //         $('#delete_historyjob').removeClass('icon-white-delete');
                    //         $('#delete_historyjob').addClass('icon-gray-delete');
                    //         $('#delete_historyjob').css('background-color', '#F4F4F5');
                    //     }
                    // },
                    // onCheckAll: function () {
                    //     var selectedRow = $('#' + table_id + '').bootstrapTable("getSelections");
                    //     checkIndex = selectedRow;
                    //     $('#' + table_id + ' thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checked.svg')");
                    //     // var selectedRow = $('#' + table_id + '').bootstrapTable("getSelections");
                    //     $('.fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>选中' + selectedRow.length + '');
                    //     if ($('#table').find('.no-records-found').length > 0) {

                    //     } else {
                    //         $('.batch-start').addClass('batch_start_active');
                    //         $('.batch-start i').addClass('icon-start');
                    //         $('.batch-start span').addClass('bgb');
                    //         $('.batch-stop').addClass('batch_stop_active');
                    //         $('.batch-stop i').addClass('icon-stop');
                    //         $('.batch-stop span').addClass('bgb');
                    //         $('.batch-delete').addClass('batch_delete_active');
                    //         $('.batch-delete i').addClass('icon-delete');
                    //         $('.batch-delete span').addClass('bgb');
                    //         if (table_id == 'history_table') {
                    //             $('#delete_historyjob').removeClass('icon-gray-delete');
                    //             $('#delete_historyjob').addClass('icon-white-delete');
                    //             $('#delete_historyjob').css('background-color', '#0FBF98');
                    //         }
                    //     }
                    // },
                    loadingFontSize: '13px',
                    loadingTemplate: function (loadingMessage) {
                        return '<span class="loading-wrap">' +
                            '<span class="loading-text">' +
                            loadingMessage +
                            '</span>' +
                            '<span class="animation-wrap"><span class="animation-dot"></span></span>' +
                            '</span>'
                    },
                    onLoadSuccess: function () {
                        loadSuccess(table_id, options);
                        if ($('#' + table_id + '').find('.no-records-found').length > 0) {
                            $('#' + table_id + ' thead .bs-checkbox input[type=checkbox]').prop('disable', true);
                        }
                    },
                    //onResetView: initTableHeight,
                    headerStyle: headerStyle,
                    pagination: true, //是否开启分页
                    showExport: true, //是否开启导出按钮
                    silent: true,
                    showColumns: true, //是否开启列选择按钮
                    showLoading: false,
                    buttonsPrefix: 'b-btn', //所有按钮样式类名，在最前方加
                    buttonsClass: 'toolbtn',
                    icons: {
                        export: 'icon-export', //按钮工具的图标类，在最后加
                        detailOpen: 'detail-open',
                        detailClose: 'detail-close',
                        columns: 'icon-columns',
                    },
                    pageList: [5, 10, 25, 50], //每页的记录数组
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
                    trimOnSearch: true, //	true设置为修剪搜索字段中的空格
                    pageNumber: pageSessionRecord != null ? pageSessionRecord.offset + 1 : 1, //	默认起始页
                    resizable: true,
                    onColumnSwitch: function (field, checked) {
                        if (options.hideColumns) {
                            columnSwitch(table_id, options.toolbarId);
                        }
                    },

                    onPreBody: function () {
                        // var sessionSearch = sessionStorage.getItem('search');
                        // $('.customSearch').val(sessionSearch);

                        if (sessionStorage.getItem('dateTime')) {
                            $('#' + options.dateTimePicker.id + '').html('<i class="icon-time"></i>' + sessionStorage.getItem('dateTime') + '');
                        } else if (sessionStorage.getItem('dateTime') == 'null') {
                            $('#' + options.dateTimePicker.id + '').html('<i class="icon-time"></i>' + LANG.UI_PUBLIC_START_TIME + '-' + LANG.UI_PUBLIC_END_TIME + '');
                        }
                    },
                    onPageChange: function (number, size) {
                        var pageRecord = {
                            offset: number - 1,
                            limit: size
                        }
                        sessionStorage.setItem('' + table_id + '_pageRecord', JSON.stringify(pageRecord));
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
                            v.formatter = function (value) {
                                if (value == 2) {}
                                switch (value) {
                                    case 2:
                                        return '<span>虚拟机</span>';
                                    case 3:
                                        return '<span>' + LANG.UI_VISUAL_FILE + '</span>';
                                    case 4:
                                        return '<span>' + LANG.UI_VISUAL_DB + '</span>';
                                    case 5:
                                        return '<span>' + LANG.UI_PLATFORM_DES_OS + '</span>';
                                    case 6:
                                        return '<span>VDDT_SERVER</span>';
                                    case 7:
                                        return '<span>VDDT_CLIENT</span>';
                                    case 8:
                                        return '<span>副本备份</span>';
                                    case 9:
                                        return '<span>副本</span>';
                                    case 10:
                                        return '<span>卷CDP</span>';
                                    case 11:
                                        return '<span>NAS</span>';
                                    case 12:
                                        return '<span>EXCHANGE</span>';

                                    default:
                                        break;
                                }
                            };
                            v.cellStyle = cellStyle;
                            break;
                        case 'label':
                            // 状态转义
                            v.formatter = function (value) {
                                switch (value) {
                                    case CONF.TASK_STATUS.WAITTING:
                                        return '<span class="label label-sm label-info status-icon">' + LANG.UI_PUBLIC_WAIT + '</span>';
                                    case CONF.TASK_STATUS.STOPPING:
                                        return '<span class="label label-sm label-info status-icon">' + LANG.UI_PUBLIC_STOPPING + '</span>';

                                    case CONF.TASK_STATUS.PREPARING:
                                        return '<span class="label label-sm label-info status-icon">' + LANG.UI_PUBLIC_READYING + '</span>';

                                    case CONF.TASK_STATUS.RUNNING:
                                        return '<span class="label label-sm label-success status-icon">' + LANG.UI_PUBLIC_RUNNING + '</span>';

                                    case CONF.TASK_STATUS.PAUSED:
                                        return '<span class="label label-sm label-success status-icon">' + LANG.UI_JOB_PAUSE + '</span>';

                                    case CONF.TASK_STATUS.SUCCESSED:
                                        return '<span class="label label-sm label-success status-icon">' + LANG.UI_VISUAL_SUCCESS + '</span>';
                                    case CONF.TASK_STATUS.STARTING:
                                        return '<span class="label label-sm label-success status-icon">' + LANG.UI_PUBLIC_STARTING + '</span>';

                                    case CONF.TASK_STATUS.STOPPED:
                                        return '<span class="label label-sm label-default status-icon">' + LANG.UI_JOB_STOP + '</span>';
                                    case CONF.TASK_STATUS.ABNORMAL:
                                        return '<span class="label label-sm label-warning status-icon">' + LANG.UI_NODE_ABNORMAL + '</span>';
                                    case CONF.TASK_STATUS.NETWORK_FAULT:
                                        return '<span class="label label-sm label-danger status-icon">' + LANG.UI_PUBLIC_NETWORK_ERROR + '</span>';

                                    case CONF.TASK_STATUS.ERROR:
                                        return '<span class="label label-sm label-danger status-icon">' + LANG.UI_PUBLIC_ERROR + '</span>';
                                    default:
                                        // return '<span class="label label-sm label-info status-icon">准备中</span>';
                                        break;
                                }

                                if (value == 1) {} else {}
                            };
                            break;
                        case 'href':
                            // 添加超链接
                            v.formatter = function (value, row, index, field) {
                                // console.log(row);
                                var url = getDetailsUrl(row.module_type_value, row.job_type_value);
                                if (row.module_type_value == CONF.MODULE_TYPE.FS) {
                                    var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                                        '" class="ajaxify" name="task" title = ' + value + '>' + value + '</a>';
                                } else if (row.module_type_value == CONF.MODULE_TYPE.VM && row.job_type == LANG.UI_INSTANT_NAME) {
                                    var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                                        '" class="ajaxify" name="task" title = ' + value + '>' + value + '</a>';
                                } else if (row.module_type_value == CONF.MODULE_TYPE.COPY) {
                                    var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                                        '" class="ajaxify" name="task" title = ' + value + '>' + value + '</a>';
                                } else {
                                    var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                                        '" class="ajaxify" name="task" title = ' + value + '>' + value + '</a>';
                                }
                                return nameStr;
                            }
                            v.cellStyle = cellStyle;
                            v.events = operateEvents;
                            break;
                        case 'operation':
                            // 操作按钮格式化
                            v.formatter = function (value, row, index, field) {
                                var opCode = row.op_list;
                                var uuid = row.job_uuid;

                                var button = '<div class="btn-group">';
                                if (index > 5) {
                                    button = '<div class="btn-group dropup">';
                                }

                                button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                                    'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                                    '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                                    '</button>' +
                                    '<ul class="dropdown-menu min-width100" role="menu" id=' + uuid + '>';

                                if (row.job_type_value == 28) {
                                    if (row.db_type == CONF.DB_TYPE.SQLSERVER) {
                                        // opCode.splice(2, 1);
                                        opCode = opCode.slice(0, 2).concat(opCode.slice(3, 8));
                                    } else if (row.db_type == CONF.DB_TYPE.MYSQL || row.db_type == CONF.DB_TYPE.MARIA) {
                                        // opCode = opCode.splice(3, 1);
                                        opCode = opCode.slice(0, 3).concat(opCode.slice(4, 8));
                                    } else if (row.db_type == CONF.DB_TYPE.POSTGRE || row.db_type == CONF.DB_TYPE.ANTDB || row.db_type == CONF.DB_TYPE.KINGBASE ||
                                        row.db_type == CONF.DB_TYPE.UXDB || row.db_type == CONF.DB_TYPE.HIGHGO || row.db_type == CONF.DB_TYPE.OPENGAUSS || row.db_type == CONF.DB_TYPE.VASTBASE) {
                                        // opCode = opCode.splice(2, 2);
                                        opCode = opCode.slice(0, 2).concat(opCode.slice(4, 8));
                                    }
                                }

                                $.each(opCode, function (i, d) {
                                    switch (d) {
                                        case 1:
                                            button += '<li class="start"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START + '</a></li>';
                                            break;
                                        case 2:
                                            button += '<li class="stop"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
                                            break;
                                        case 3:
                                            if ($.inArray('global_observer', CONF.PERMISSION) != -1) break;
                                            button += '<li class="divider"></li><li class="edit"><a href="javascript:;"><i class="viconfont vicon-ge_modify"></i> ' + LANG.UI_JOB_MODIFY + '</a></li>';
                                            break;
                                        case 4:
                                            button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_JOB_DELETE + '</a></li>';
                                            break;
                                        case 5:
                                            button += '<li class="pause"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_PAUSE + '</a></li>';
                                            break;
                                        case 6:
                                            // if (options.resModule.subModule == CONF.VM_TYPE.INSPURVVDK) break;
                                            button += '<li class="startDiff"><a href="javascript:;" ><i class="viconfont vicon-ge_differentia_backup"></i> ' + LANG.UI_JOB_START_DIFFRENCE + '</a></li>';
                                            break;
                                        case 7:
                                            button += '<li class="startIncr"><a href="javascript:;" ><i class="viconfont vicon-ge_increment"></i> ' + LANG.UI_JOB_START_INCREMENT + '</a></li>';
                                            break;
                                        case 8:
                                            button += '<li class="startStra"><a href="javascript:;" ><i class="viconfont vicon-ge_time_point"></i> ' + LANG.UI_JOB_START_STRATEGY + '</a></li>';
                                            break;
                                        case 9:
                                            //全局观察者不能迁移
                                            if (4 == CONF.SOFTWARE || $.inArray('global_observer', CONF.PERMISSION) != -1) break;
                                            button += '<li class="motion"><a href="javascript:;" ><i class="viconfont vicon-ge_migration"></i> ' + LANG.UI_MOTION_NAME + '</a></li>';
                                            break;
                                        case 10:
                                            button += '<li class="start"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START_FULL + '</a></li>';
                                            break;
                                        case 11:
                                            if ($.inArray('global_observer', CONF.PERMISSION) != -1) break;
                                            button += '<li class="takeover"><a href="javascript:;" ><i class="viconfont vicon-vol_cdp_takeover"></i> ' + LANG.UI_JOB_START_TAKEOVER + '</a></li>';
                                            break;
                                        case 12:
                                            if ($.inArray('global_observer', CONF.PERMISSION) != -1) break;
                                            button += '<li class="stoptakeover"><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_TAKEOVER + '</a></li>';
                                            break;
                                        case 13:
                                            if (row.job_type_value == 28 && (row.db_type == CONF.DB_TYPE.ORACLE || row.db_type == CONF.DB_TYPE.DM ||
                                                    row.db_type == CONF.DB_TYPE.POSTGRE || row.db_type == CONF.DB_TYPE.KINGBASE ||
                                                    row.db_type == CONF.DB_TYPE.UXDB || row.db_type == CONF.DB_TYPE.HIGHGO || row.db_type == CONF.DB_TYPE.OPENGAUSS || row.db_type == CONF.DB_TYPE.VASTBASE)) {
                                                button += '<li class="startLog"><a href="javascript:;" ><i class="viconfont vicon-ge_running_log"></i> ' + LANG.UI_JOB_START_ARCHIVE_LOG_BACKUP + '</a></li>';
                                            } else {
                                                button += '<li class="startLog"><a href="javascript:;" ><i class="viconfont vicon-ge_log"></i> ' + LANG.UI_JOB_START_LOG_BACKUP + '</a></li>';
                                            }
                                            break;
                                        case 14:
                                            button += '<li class="startfailback"><a href="javascript:;" ><i class="viconfont vicon-ge_cutback"></i>' + LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK + ' </a></li>';
                                            break;
                                            //				case 15:
                                            //					button += '<li class="stopfailback"><a href="javascript:;" ><i class="fa fa-square"></i>停止接管12</a></li>'; 
                                            //					break;
                                        case 16:
                                            button += '<li class="createlable"><a href="javascript:;"><i class="viconfont vicon-ge_sign"></i> ' + LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABEL + ' </a></li>';
                                            break;
                                        case 41:
                                            button += '<li class="stopfailback"><a href="javascript:;"><i class="viconfont vicon-ge_cutback"></i> ' + LANG.UI_JOB_STOP_DBCDP_FAILBACK + ' </a></li>';
                                            break;
                                    }
                                });
                                button += '</ul></div>';
                                return button;
                            }
                            break;
                        default:
                            if (v.formatter == undefined) {
                                v.formatter = function (value, row, index, field) {
                                    return '<span title = "' + value + '">' + value + '</span>'
                                }
                            }
                            v.cellStyle = cellStyle;
                            break;
                    }
                    v.type = undefined;
                });


                // 搜索框
                if (options.searchInput === true && $('' + options.toolbarId + '').find('' + options.searchSelector + '').length < 1) {
                    toolbarId = options.toolbarId;
                    $('' + options.toolbarId + ' .leftTool').append(`<div class='customBtn1'></div>
                    <div class="search input-group mr6">
                    <input class="` + options.searchClass + ` customSearch" autocomplete="off" type="text" placeholder="按任务名搜索">
                    <div class="position0" style="width:auto;height:34px">
                        <button class="b-btn clear hide position0"><i class="icon-close-small"></i></button>
                    </div>
                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                        <button class="b-btn search-btn"><i class="icon-search"></i></button>
                    </div>
                </div>
                <div class='customBtn2'></div>`);
                }

                //新建任务按钮
                if (options.addTaskBtn === true && $('' + options.toolbarId + '').find('#addList').length < 1) {
                    $('' + options.toolbarId + ' .leftTool').append(`<div class="btn-group" id="addList">
                    <label>
                        <button class="dropdown-toggle btn-font flex_center addTask p-lr8" id="addTask" aria-haspopup="true" aria-expanded="false" style="width:118px;height:34px;border:0px">
                            <i class="addNewTask"></i>
                            <span>新建</span>
                        </button>
                        <ul class="dropdown-menu addTaskList" data-stopPropagation="true">
                            <li class="vm_protected"  data-stopPropagation="true"><span>` + LANG.UI_SETTING_VIRTUAL_MACHINE_PROTECTION + `</span><i class = "icon-rightArrow"></i><div class="subDiv">
                            <ul> 
                                <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                <li class="vmInstantRecovery ">瞬时恢复任务</li>
                                <li class="vmGrainRecover">细粒度恢复任务</li>
                            </ul>
                            </div></li>
                            <li class="db_protect" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_SETTING_DATABASE_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                            <ul> 
                                <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                            </ul>
                            </div></li>
                            <li class="os_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_SETTING_OS_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                            <ul> 
                                <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                            </ul></div></li>
                            <li class="real_time_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_SETTING_VOL_CDP_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                            <ul> 
                                <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                <li class="takeover">` + LANG.UI_VOL_CDP_TAKEOVER_TASK + `</li>
                            </ul></div></li>
                            <li class="fs_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_SETTING_FILE_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                            <ul> 
                                <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                            </ul></div></li>
                            <li class="nas_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_SETTING_NAS_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                            <ul> 
                                <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                            </ul></div></li>
                        </ul>
                    </label>
                </div>`)
                }

                // 过滤器处理
                if (options.filterOption && $('' + options.toolbarId + '').find('#test1').length < 1) {
                    var html = `<div class="btn-group" id="test1">
						<button id="filterBtn" class="dropdown-toggle filters bgw btn-font flex_center p-lr8" data-toggle="filters" style="width:auto;height:34px;border:0px;">
							<svg width="16" height="16" style="margin-right: 4px;" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<g id="Filter (&#231;&#173;&#155;&#233;&#128;&#137;)">
									<path id="Vector" d="M2 3L6.8 8.60593V12.8148L9.2 14V8.60593L14 3H2Z" stroke="#0FBF98" stroke-width="1.33333" stroke-linejoin="round" />
								</g>
							</svg>
							<span>
								过滤器(无)
							</span>
						</button>
						<ul class="dropdown-menu hide" id="filters" aria-haspopup="true" aria-expanded="false" style="position:absolute;top:22px;z-index:9999">

							<div class="content" id="filterDiv" style="width:auto;height: auto; min-height:290px; display:flex;flex-direction:column;">

								<div class="" style="width: inherit;height: 42px;border-bottom: 1px solid #F1F3F5;padding:12px 16px 12px 16px">
									<div style="display:flex;align-items:center;justify-content:space-between;">
										<span style="display:inline-block;font-size: 14px;font-weight: 400;color: #1D1E26;line-height: 16px;">过滤选项</span>
										<div>
											<label for="selectAll">
												<a id="selectAll" style="font-size: 12px;">` + LANG.UI_JOB_FILTER_SELECTALL + `</a>
											</label>
											<span style="color: #E4E4E4;margin:0 4px 0 4px">|</span>
											<label for="selectNone">
												<a id="selectNone" style="font-size: 12px;">` + LANG.UI_JOB_FILTER_SELECTNONE + `</a>
											</label>
										</div>
									</div>
								</div>

								<div class="filter-content" style="width: inherit;height: auto;">
									<div id="filter-content-div" style="padding:8px 10px 8px 10px;display:flex;justify-content:space-between;align-items:center;">

										<div id="job_status" class="m-lr6"></div>

                                        <div id="module_type" class="mr15"></div>

                                        <div id="job_type" class="mr12"></div>

									</div>
							    </div>
                            <div class="first-line" style="width: inherit;height: 56px;border-top: 1px solid #F1F3F5;display:flex;justify-content:flex-end">
									<button id="filterCancel" class="btn btn-sm"><span>` + LANG.UI_PUBLIC_CANCEL + `</span></button>
									<button id='filterSubmit' class="btn btn-sm" type="submit" style="border: 0;"><span>` + LANG.UI_PUBLIC_CONFIRM + `</span></button>
							</div>
						</ul>
					</div>`

                    $('' + options.toolbarId + ' .leftTool').append(html);
                    options.filterOption = $.each(options.filterOption, function (k, v) {
                        switch (v) {
                            case 'job_status':
                                if (table_id == 'history_table') {
                                    $('' + options.toolbarId + ' #job_status').append(`
                                    <div>
                                        <ul>
                                            <span style="color: #393C4D; font-size: 13px;">` + LANG.UI_VISUAL_RESULT + `</span>
                                            <li><label for="his_success"><input type="checkbox" id="his_success" value="1"><span class="label-success">` + LANG.UI_VISUAL_SUCCESS + `</span></label></li>
                                            <li><label for="his_wait"><input type="checkbox" id="his_wait" value="2"><span class="label-info">` + LANG.UI_VISUAL_SUSPEND + `</span></label></li>
                                            <li><label for="his_abnormal"><input type="checkbox" id="his_abnormal" value="3"><span class="label-warning">` + LANG.UI_VISUAL_NODE_ABNORMAL + `</span></label></li>
                                            <li><label for="his_error"><input type="checkbox" id="his_error" value="4"><span class="label-danger">` + LANG.UI_VISUAL_FAIL + `</span></label></li>
                                        </ul>
                                    </div>`)
                                } else {
                                    $('' + options.toolbarId + ' #job_status').append(`
                                    <div>
                                        <ul>
                                            <span style="color: #393C4D; font-size: 13px;">` + LANG.UI_PUBLIC_STATUS + `</span>
                                            <li><label for="` + table_id + `-success"><input type="checkbox" id="` + table_id + `-success" value="17"><span class="label-success">` + LANG.UI_VISUAL_SUCCESS + `</span></label></li>
                                            <li><label for="` + table_id + `-wait"><input type="checkbox" id="` + table_id + `-wait" value="1"><span class="label-info">` + LANG.UI_VISUAL_WAIT + `</span></label></li>
                                            <li><label for="` + table_id + `-error"><input type="checkbox" id="` + table_id + `-error" value="8"><span class="label-danger">` + LANG.UI_VISUAL_ERROR + `</span></label></li>
                                            <li><label for="` + table_id + `-stop"><input type="checkbox" id="` + table_id + `-stop" value="4"><span class="label-default">` + LANG.UI_VISUAL_STOP + `</span></label></li>
                                            <li><label for="` + table_id + `-abnormal"><input type="checkbox" id="` + table_id + `-abnormal" value="7"><span class="label-warning">` + LANG.UI_VISUAL_NODE_ABNORMAL + `</span></label></li>
                                        </ul>
                                    </div>`)
                                }
                                break;
                            case 'module_type':
                                $('' + options.toolbarId + ' #module_type').append(`
						<div  class="mid">
                            <ul>
                                <span style="color: #393C4D; font-size: 13px;">` + LANG.UI_SEARCH_MODE_TYPE + `</span>
                                <li>
                                    <label for="` + table_id + `-os_backup"><input type="checkbox" id="` + table_id + `-os_backup" value="5"><span>` + LANG.UI_VISUAL_OS + `</span></label>
                                </li>
                                <li>
                                    <label for="` + table_id + `-nas_backup"><input type="checkbox" id="` + table_id + `-nas_backup" value="11"><span>` + LANG.UI_VISUAL_NAS + `</span></label>
                                </li>
                                <li>
                                    <label for="` + table_id + `-vm_backup"><input type="checkbox" id="` + table_id + `-vm_backup" value="2"><span>` + LANG.UI_VISUAL_VM + `</span></label>
                                </li>
                                <li>
                                    <label for="` + table_id + `-db_backup"><input type="checkbox" id="` + table_id + `-db_backup" value="4"><span>` + LANG.UI_VISUAL_DB + `</span></label>
                                </li>
                                <li>
                                    <label for="` + table_id + `-fs_backup"><input type="checkbox" id="` + table_id + `-fs_backup" value="3"><span>` + LANG.UI_VISUAL_FILE + `</span></label>
                                </li>
                                <li>
                                <label for="` + table_id + `-vol_cdp_backup"><input type="checkbox" id="` + table_id + `-vol_cdp_backup" value="10"><span>` + LANG.UI_PLATFORM_DES_CDP + `</span></label>
                            </li>
                            </ul>
						</div>`)
                                break;
                            case 'job_type':
                                $('' + options.toolbarId + ' #job_type').append(`
						<div  class="mid">
                            <ul>
                                <span style="color: #393C4D; font-size: 13px;white-space: nowrap;">` + LANG.UI_SEARCH_TASK_TYPE + `</span>
                                <li>
                                    <label for="` + table_id + `-backup"><input type="checkbox" id="` + table_id + `-backup" value="1"><span>` + LANG.UI_PUBLIC_BACKUP + ` </span></label>
                                </li>
                                <li>
                                    <label for="` + table_id + `-recovery"><input type="checkbox" id="` + table_id + `-recovery" value="2"><span>` + LANG.UI_PUBLIC_RECOVERY + ` </span></label>
                                </li>
                                <li>
                                    <label for="` + table_id + `-archive"><input type="checkbox" id="` + table_id + `-archive" value="19"><span>` + LANG.UI_ARCHIVE + ` </span></label>
                                </li>
                                <li>
                                    <label for="` + table_id + `-takeover"><input type="checkbox" id="` + table_id + `-takeover" value="34"><span>` + LANG.UI_VISUAL_TAKEOVER + ` </span></label>
                                </li>
                                <li>
                                    <label for="` + table_id + `-copy"><input type="checkbox" id="` + table_id + `-copy" value="17"><span>副本 </span></label>
                                </li>
                            </ul>
						</div>`)
                                break;
                            case 'data_type':
                                $('' + options.toolbarId + ' #filter-content-div').append(`
                            <div id="data_type" class="mr12">
                                <div  class="mid">
                                    <ul>
                                    <span style="color: #393C4D; font-size: 13px;white-space: nowrap;">` + LANG.UI_JOB_DATA_TYPE + `</span>
                                    <li>
                                        <label for="` + table_id + `-backup"><input type="checkbox" id="` + table_id + `-full" value="1"><span>` + LANG.UI_DATA_TYPE_FULL + ` </span></label>
                                    </li>
                                    <li>
                                        <label for="` + table_id + `-recovery"><input type="checkbox" id="` + table_id + `-incriment" value="2"><span>` + LANG.UI_DATA_TYPE_INCR + ` </span></label>
                                    </li>
                                    <li>
                                        <label for="` + table_id + `-archive"><input type="checkbox" id="` + table_id + `-diffrence" value="3"><span>` + LANG.UI_DATA_TYPE_DIFF + ` </span></label>
                                    </li>
                                    <li>
                                        <label for="` + table_id + `-takeover"><input type="checkbox" id="` + table_id + `-backupset" value="7"><span>` + LANG.UI_DATA_TYPE_BACKUPSET + ` </span></label>
                                    </li>
                                    </ul>
                                </div>
                             </div>`)
                                break;
                            default:
                                break;
                        }
                    })
                }

                //时间选择器
                if (options.dateTimePicker != {} && options.dateTimePicker != undefined && $('' + options.toolbarId + '').find('.timePlan').length < 1) {
                    $('' + options.toolbarId + ' .leftTool').append(`<div class="m-lr6" style="width:auto;height:34px;display:flex;justify-content:center;align-items:center">
                    <button class="btn dropdown-toggle timePlan p-lr8" id="` + options.dateTimePicker.id + `" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;display:flex;justify-content:center;align-items:center ;border:0px">
                        <i class="icon-time"></i>
                        开始时间-结束时间
                    </button>
                </div>`)
                }

                //改变高度
                if (options.changeHeightBtn === true && $('' + options.toolbarId + '').find('#change-height').length < 1) {
                    $('' + options.toolbarId + ' .rightTool').prepend(`<div id="change-height" ">
					<button class="btn change_height"  title = "改变高度">
						<i class="icon-auto-height1"></i>
					</button>
				</div>`)
                }

                //高级搜索
                if (options.advanceSearch != {} && options.advanceSearch != undefined && $('' + options.toolbarId + '').find('#advanced-search').length < 1) {
                    $('' + options.toolbarId + ' .rightTool').prepend(`
                    <div class="customBtn3"></div>
                <div id="advanced-search" class="brr2 mr6" style="width: auto;height: 32px">
					<button class="btn adv_btn brr2 p-lr8" id="advanced-search-btn">
						<i class="adv-search"></i>
						<span style="color: #FFFFFF;">高级搜索</span>
					</button>
                    <div class="advanced_list display-none">
                        <div class="list_header"><span>搜索条件展示</span><a id="clear_adv">清除筛选</a></div>
                        <div id="list_content"></div>
                    </div>
				</div>
                <!-- BEGIN MODAL -->
                    <div id="` + options.advanceSearch.module + `JobModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-backdrop="static">
                        <div class="modal-header bs-modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title bs-modal-title"><i class="adv-search-modal"></i> 高级搜索
                            </h4>
                        </div>
                        <div class="modal-body bs-modal-body">
                            <div class="portlet-body">

                                <div class="list-option">
                                    <div class="row">
                                        <!-- 任务名 -->
                                        <label class="control-label col-md-4">` + LANG.UI_SEARCH_TASK_NAME + `
                                        </label>
                                        <div class="col-md-5">
                                            <input style="width:364px; height:32px;" id="` + options.advanceSearch.module + `_taskName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                                        </div>
                                    </div>
                                </div>

                            <div class="list-option">
                                <div class="row">
                                    <!-- 按用户 -->
                                    <label class="control-label col-md-4">` + LANG.UI_STORAGE_DETAIL_USERNAME + `
                                    </label>
                                    <div class="col-md-5">
                                    <input style="width:364px; height:32px;" id="` + options.advanceSearch.module + `_userName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                                    </div>
                                </div>
                            </div>

                         <div class="list-option">
                            <div class="row">
                                <!-- 按主机名/IP/别名 -->
                                <label class="control-label col-md-4">` + LANG.UI_BACKUP_FILE_HOSTNAME + `
                                </label>
                                <div class="col-md-5">
                                <input style="width:364px; height:32px;" id="` + options.advanceSearch.module + `_hostName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                                </div>
                            </div>
                        </div>

                        <div class="list-option">
                            <div class="row">
                                <!-- 按虚拟机名/IP/别名 -->
                                <label class="control-label col-md-4">` + LANG.UI_JOB_VM_NAME + `
                                </label>
                                <div class="col-md-5">
                                <input style="width:364px; height:32px;" id="` + options.advanceSearch.module + `_vmName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                                </div>
                            </div>
                        </div>

                                <div class="list-option">
                                    <div class="row">
                                        <!-- 任务类型 -->
                                        <label class="control-label col-md-4">` + LANG.UI_SEARCH_TASK_TYPE + `
                                        </label>
                                        <div class="col-md-5">
                                            <select class="form-control select2me" id="` + options.advanceSearch.module + `_tasktype" style="width: 364px;">
                                                <option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
                                                <option value="1">` + LANG.UI_PUBLIC_BACKUP + `</option>
                                                <option value="2">` + LANG.UI_PUBLIC_RECOVERY + `</option>
                                            </select>
                                            <select class="form-control select2me display-none" id="vmTasktype">
									<option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
									<option value="1">` + LANG.UI_PUBLIC_BACKUP + `</option>
									<option value="2">` + LANG.UI_PUBLIC_RECOVERY + `</option>
									<option value="7">` + LANG.UI_INSTANT_NAME + `</option>
									<option value="8">` + LANG.UI_MOTION_NAME + `</option>
									<option value="6">` + LANG.UI_RECOVERY_GRAIN + `</option>
                                    <option value="37">` + LANG.UI_JOB_DATA_VERTIFY + `</option>
								</select>
								<select class="form-control select2me display-none" id="fsTasktype">
									<option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
									<option value="1">` + LANG.UI_PUBLIC_BACKUP + `</option>
									<option value="2">` + LANG.UI_PUBLIC_RECOVERY + `</option>
								</select>
								<select class="form-control select2me display-none" id="dbTasktype">
									<option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
									<option value="28">` + LANG.UI_PUBLIC_BACKUP + `</option>
									<option value="29">` + LANG.UI_PUBLIC_RECOVERY + `</option>
								</select>
								<select class="form-control select2me display-none" id="copyTasktype">
									<option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
									<option value="17">` + LANG.UI_VISUAL_DRILLS_VM_COPY + `</option>
									<option value="18">` + LANG.UI_VISUAL_DRILLS_VM_COPY_CALLBACK + `</option>
									<option value="26">` + LANG.UI_VISUAL_FILE_COPY + `</option>
									<option value="27">` + LANG.UI_VISUAL_FILE_COPY_CALLBACK + `</option>
									<option value="30">` + LANG.UI_VIRTUAL_DB_COPY + `</option>
									<option value="31">` + LANG.UI_VIRTUAL_DB_COPY_BACK + `</option>
									<option value="19">` + LANG.UI_ARCHIVE + `</option>
									<option value="20">` + LANG.UI_ARCHIVE_CALLBACK + `</option>
								</select>
								<select class="form-control select2me display-none" id="dbCDPTaskType">
									<option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
									<option value="21">` + LANG.UI_PUBLIC_CDP + `</option>
									<option value="22">` + LANG.UI_PUBLIC_RECOVERY + `</option>
								</select>
								<select class="form-control select2me display-none" id="fileCDPTaskType">
									<option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
									<option value="24"><?php echo $LANG['WEB_PLATFORM_DES_REAL_TIME_SYN']?></option>
								</select>
								<select class="form-control select2me display-none" id="osTaskType">
									<option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
									<option value="35">` + LANG.UI_PUBLIC_BACKUP + `</option>
									<option value="36">` + LANG.UI_PUBLIC_RECOVERY + `</option>
								</select>
								<select class="form-control select2me display-none" id="nasTaskType">
									<option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
									<option value="1">` + LANG.UI_PUBLIC_BACKUP + `</option>
									<option value="2">` + LANG.UI_PUBLIC_RECOVERY + `</option>
								</select>
								<select class="form-control select2me display-none" id="volCdpTaskType">
									<option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
									<option value="32">` + LANG.UI_PUBLIC_BACKUP + `</option>
									<option value="33">` + LANG.UI_PUBLIC_RECOVERY + `</option>
									<option value="34">` + LANG.UI_PUBLIC_TAKEOVER + `</option>
								</select>

                                        </div>
                                    </div>
                                </div>

                                <div class="list-option">
                                    <div class="row">
                                        <!-- 模块类型 -->
                                        <label class="control-label col-md-4">` + LANG.UI_SEARCH_MODE_TYPE + `
                                        </label>
                                        <div class="col-md-5">
                                            <select class="form-control select2me" id="` + options.advanceSearch.module + `_moduletype" style="width: 364px;">
                                                <option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
                                                <option value="11">` + LANG.UI_VISUAL_NAS + `</option>
                                                <option value="2">` + LANG.UI_PUBLIC_VM + `</option>
                                                <option value="3">` + LANG.UI_VISUAL_FILE + `</option>
                                                <option value="4">` + LANG.UI_VISUAL_DB + `</option>
                                                <option value="5">` + LANG.UI_PLATFORM_DES_OS + `</option>
                                                <option value="9">` + LANG.UI_VISUAL_COPY + `/` + LANG.UI_VISUAL_ARCHIVE_CHART + `</option>
                                                <option value="10">` + LANG.UI_PLATFORM_DES_CDP + `</option>
                                                <option value="10000">` + LANG.UI_JOB_DB_CDP + `</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-option">
                                    <div class="row">
                                        <!-- 所在节点 -->
                                        <label class="control-label col-md-4">` + LANG.UI_PUBLIC_STORAGE_IN_NODE + `
                                        </label>
                                        <div class="col-md-5">
                                            <select class="form-control select2me" id="` + options.advanceSearch.module + `_node" style="width: 364px;">
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-option">
                                    <div class="row">
                                        <!-- 所在存储 -->
                                        <label class="control-label col-md-4">` + LANG.UI_BACKUP_FILE_STORAGE + `
                                        </label>
                                        <div class="col-md-5">
                                            <select class="form-control select2me" id="` + options.advanceSearch.module + `_storage" style="width: 364px;">
                                            </select>
                                        </div>
                                    </div>
                                </div>

                    <div class="list-option">
    					<div class="row">
							<!-- 虚拟化类型 -->
							<div id="vmtypeDiv" style = "display:none">
								<label class="control-label col-md-4">` + LANG.UI_VM_SETTING_V2_HYPER_TYPE + `
								</label>
								<div class="col-md-5">
									<select class="form-control select2me" id="vm_hypervisor" name="vmtype">
									</select>
								</div>
							</div>

                            <!-- 数据库类型 -->
							<div id="dbtypeDiv" style = "display:none">
								<label class="control-label col-md-4">` + LANG.UI_DB_TYPE + `
								</label>
								<div class="col-md-5">
									<select class="form-control select2me" id="` + options.advanceSearch.module + `dbtype" name="dbtype">
                                    <option value="0">` + LANG.UI_PUBLIC_ALL + `</option>
                                    <option value="1">SQL Server</option>
                                    <option value="2">Oracle</option>
                                    <option value="3">MySQL</option>
                                    <option value="4">DM</option>
                                    <option value="5">PostgreSQL</option>		
                                    <option value="6">KingbaseES</option>
                                    <option value="7">UXDB</option>			
                                    <option value="8">Highgo DB</option>
                                    <option value="9">MariaDB</option>					
                                    <option value="10">openGauss</option>
                                    <option value="11">Vastbase</option>
									</select>
								</div>
							</div>
    					</div>
    				</div>

                            </div>
                        </div>

                        <!-- modal-footer -->
                        <div class="modal-footer bs-modal-footer">
                            <button type="button" data-dismiss="modal" class="btn bs_btn_default">取消</button>
                            <button type="button" class="btn bs_btn_success" id="` + options.advanceSearch.module + `_search_submit">确定</button>
                        </div>
                    </div>
                    <!-- END MODAL -->`)
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
                            <span class="ml5">` + LANG.UI_VISUAL_STOP + `</span>
                        </div>
    
                        <div class="batch-delete mr12" id="batch_delete">
                            <i class="icon-delete-disable"></i>
                            <span class="ml5">` + LANG.UI_JOB_DELETE + `</span>
                        </div>
                    </div>
                </div>`)
                }


                var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range
                var initDatetimePicker = function () {
                    //初始化日期时间选择控件
                    $('#' + options.dateTimePicker.id + '').daterangepicker({
                        "autoUpdateInput": false, //是否自动填充input
                        "startDate": moment().subtract(6, 'days').startOf('day'), //默认开始时间
                        "endDate": moment({
                            hour: 23,
                            minute: 59
                        }), //默认结束时间
                        "maxDate": moment({
                            hour: 23,
                            minute: 59
                        }), //最大可用时间
                        "timePicker": true, //是否显示时间,时分
                        "timePicker24Hour": true, //是否是24小时制
                        "alwaysShowCalendars": true, //是否总是显示日期选择
                        "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE), //根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
                        "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE), //根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
                    }, function (start, end, label) {
                        //			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
                    });

                    //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
                    $('#' + options.dateTimePicker.id + '').on('apply.daterangepicker', function (ev, picker) {
                        //给全局变量赋值,然后设置input
                        _daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
                        _daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
                        _daterangepicker_range = picker.chosenLabel;
                        $(this).attr("start_time", picker.startDate.format('YYYY-MM-DD HH:mm'));
                        $(this).attr("end_time", picker.endDate.format('YYYY-MM-DD HH:mm'));

                        $(this).html('<i class="icon-time"></i>' + picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
                        sessionStorage.setItem('dateTime', picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
                        $('#' + table_id + '').bootstrapTable(('refresh'));
                    });

                    $('#' + options.dateTimePicker.id + '').on('cancel.daterangepicker', function (ev, picker) {
                        //清除全局变量,然后设置input
                        _daterangepicker_starttime = "";
                        _daterangepicker_endtime = "";
                        _daterangepicker_range = "";
                        $(this).attr('start_time', '');
                        $(this).attr('end_time', '');
                        $(this).html('<i class="icon-time"></i>开始时间-结束时间');
                        sessionStorage.setItem('dateTime', $(this).val());
                        $('#' + table_id + '').bootstrapTable(('refresh'));
                    });
                }

                $(' #' + table_id + ' ').bootstrapTable(options);
                addListeners(); //初始化监听事件
                if (options.dateTimePicker != {} && options.dateTimePicker != undefined) {
                    initDatetimePicker(); //初始化日期选择
                };
            }

        };
    }
})(jQuery)