(function($) {

    $.fn.baseTableConfig = function(id) {
        const TABLE_ID = this.prop('id'); // table id
        const headerStyle = function() {
            return {
                css: {
                    'font-family': 'Microsoft YaHei UI, Microsoft YaHei',
                    'font-size': '12px',
                    'font-weight': 400,
                    'line-height': '22px',
                    'top': 0,
                    'background-color': '#F9FAFB',
                    'z-index': 999,
                    'color': '#718096',
                    'white-space': 'nowrap',
                    'text-overflow': 'ellipsis',
                    'overflow': 'hidden',
                    'height': '40px',
                    'border-bottom': 0
                }
            };
        };
        let $table = $(`#${TABLE_ID}`); // table jquery obj
        let ajaxParams = {
            data: {}, // 请求参数
            url: '', // 接口API
            type: '' // 请求类型
        }; // ajax请求参数
        let settings = {};

        /**
         * 有数据表格高度动态自适应
         */
        const initTableHeight = () => {
            // 获取 table-content-wrapper class
            const TABLE_CONTENT_WRAPPER = settings.tableContentWrapper;

            // 记录完整展开时表格十行的临界高度 critical height,计算公式：tableHeader(40) + tableBody(52*10) + tablePagination(44)
            const TABLE_CRITICAL_HEIGHT = 604;

            // 获取 table-content-wrapper 的高度
            let tableContentWrapperHeight = $(TABLE_CONTENT_WRAPPER).height();

            // 获取 bootstrap-table 的高度
            let tableHeight = $(`${TABLE_CONTENT_WRAPPER} .bootstrap-table`).height();

            if (tableHeight > tableContentWrapperHeight) {
                $(`${TABLE_CONTENT_WRAPPER} .bootstrap-table`).css('height', `${tableContentWrapperHeight}px`);

                // 调用 resetView() 重置表格高度
                $table.bootstrapTable('resetView', { height: tableContentWrapperHeight });
            } else if (TABLE_CRITICAL_HEIGHT > tableContentWrapperHeight) { // table-content-wrapper 未超过临界高度
                $(`${TABLE_CONTENT_WRAPPER} .bootstrap-table`).css('height', `${tableContentWrapperHeight}px`);

                // 调用 resetView() 重置表格高度
                $table.bootstrapTable('resetView', { height: tableContentWrapperHeight });
            } else { // table-content-wrapper 超过临界高度
                $(`${TABLE_CONTENT_WRAPPER} .bootstrap-table`).css('height', `${TABLE_CRITICAL_HEIGHT}px`);

                // 调用 resetView() 重置表格高度
                $table.bootstrapTable('resetView', { height: TABLE_CRITICAL_HEIGHT });
            }
        };

        /**
         * 初始化表格数据
         * @param {*} params
         */
        const initTableData = (params) => {
            $table.bootstrapTable('showLoading');

            pAjaxRequest(params.data, params.url, params.type, (res) => {
                if (res.success) {
                    $table.bootstrapTable('load', res.data || { rows: [], total: 0 });
                } else {
                    toastr.warning(res.message, '获取表格数据失败');
                }

                // 初始化有数据时的表格高度
                initTableHeight();

                // 初始化tooltip
                let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // dropdown-menu 阻止事件冒泡，在check时不会关闭dropdown
                $('.dropdown-menu').click(function(e) {
                    e.stopPropagation();
                });

                // 监听dropdown-item click事件，动态添加active样式
                $('.dropdown-item').on('click', function(e) {
                    e.preventDefault();
                    if (!$(this).hasClass('active')) {
                        $('.dropdown-item').removeClass('active');
                        $(this).addClass('active');
                    }
                });

                $table.bootstrapTable('hideLoading');
            });
        };

        const ajaxRequest = function(params) {
            ajaxParams = {
                data: params.data,
                url: params.url,
                type: params.type
            };

            initTableData(ajaxParams);
        };

        let defaults = {
            ajax: ajaxRequest,
            advancedSearch: false, // 是否启用高级搜索
            advancedSearchId: 'advanced_search', // 高级搜索 button dropdown-toggle id
            buttonsClass: 'dropdown-toggle', // 自定义按钮class
            buttonsPrefix: 'btn', // 自定义按钮前缀class
            buttonsToolbar: '.toolbar-buttons-wrapper', // 自定义按钮工具栏class
            cache: false, // 是否开启缓存
            checkboxHeader: true, // 是否不隐藏表头 选中所有/取消选中所有 checkbox
            classes: 'table table-hover', // 设置表格class，可选值：table-bordered table-hover table-striped table-dark table-sm table-borderles. 默认：table table-bordered table-hover
            clickToSelect: true, // 是否点击行就选中checkbox
            detailView: false, // 是否开启点击查看行详情
            dataType: 'json', // 期望从服务器接收的数据类型
            detailViewIcon: true, // 是否使用展开详情图标
            detailViewByClick: false, // 是否点击行即可展开详情
            data: [],
            fixedColumns: false, // 是否固定列
            headerStyle: headerStyle, // 表头样式
            icons: {
                export: 'viconfont vicon-table-toolbar-export', // 导出图标
                detailOpen: 'viconfont vicon-table-detail-open', // detail open
                detailClose: 'viconfont vicon-table-detail-close', // detail close
                columns: 'viconfont vicon-table-toolbar-columns', // 列选择列表图标
                refresh: 'viconfont vicon-table-toolbar-refresh' // 刷新图标
            },
            loadingFontSize: '12px', // 表格加载中loading字体大小
            loadingTemplate: function() {
                return '<div class="blockui-message">' +
                    '<span class="spinner-border spinner-primary"></span>' +
                    'Loading...' +
                    '</div>';
            }, //  loading template
            method: 'get',
            minimumCountColumns: 1, // 最少显示列数
            pagination: true, // 是否开启分页
            pageList: [5, 10, 25, 50], // 每页的记录数组
            queryParamsType: 'limit', // 参数格式为limit可获取RESTFul类型参数‘limit, offset, search, sort, order’
            pageSize: 10, // 默认每页条数
            pageNumber: 1, //	默认起始页
            paginationPreText: '<i class="viconfont vicon-table-pagination-left"></i>',
            paginationNextText: '<i class="viconfont vicon-table-pagination-right"></i>',
            resizable: false, // 是否开启移动列宽
            rightToolbarClass: '', // right toolbar wrapper class
            search: false, // 是否启用搜索
            searchable: false, // 是否启用向后端发送搜索参数
            searchHighlight: true, // 是否高亮搜索结果
            searchOnEnterKey: true, // 是否启用Enter键来搜索
            searchSelector: '.toolbar-search-input', // 自定义搜索
            searchPlaceholder: '', // 自定义搜索placeholder
            searchText: '', // 初始化搜索输入框赋的值
            showColumnsSearch: false, // 是否开启表格列搜索（在列数很多时可设为true）
            showColumnsToggleAll: false, // 是否开启表格列全选(默认第一列永远被选中)
            showToggle: false, // 是否使用卡片视图
            showSearchButton: false, // 是否启用 搜索输入框的 搜索按钮
            showJumpTo: true,
            sortable: true,
            showExport: false, // 是否开启导出按钮
            silent: true,
            showColumns: false, // 是否开启列选择按钮
            showLoading: false, // 是否使用loading
            smartDisplay: false, // 是否启用智能地显示分页或卡片视图
            sidePagination: 'server', // 分页方式，client和server
            strictSearch: false, //	严格搜索
            showFullscreen: false, // 是否显示全屏展示按钮
            showHeader:true, // 是否展示表格头
            showPaginationSwitch: false, // 是否启用 展示/隐藏 分页的按钮
            showRefresh: true, // 是否启用 刷新表格 的按钮
            singleSelect: false, // 是否只允许选择一行
            trimOnSearch: true, //	true设置为修剪搜索字段中的空格
            sortClass: '', // 使用sortClass选项设置要排序的td元素的类名
            tableContentWrapper: '.table-content-wrapper' // table-content-wrapper class
        };

        /**
         * 初始化表格配置
         * @param options 配置项
         */
        const initConfigOptions = (options) => {
            settings = $.extend({}, defaults, options);

            $table.bootstrapTable(settings);

            // 获取$toolbarSearchWrapper对象
            let $toolbarSearchWrapper = settings.rightToolbarClass ?
                $(`.table-toolbar-wrapper__right.${settings.rightToolbarClass} .toolbar-search-wrapper`) :
                $('.table-toolbar-wrapper__right .toolbar-search-wrapper');

            // 初始化搜索框
            if (settings.search && $toolbarSearchWrapper.length === 0) {
                const SEARCH_IPT_ID = settings.rightToolbarClass ? `${settings.rightToolbarClass}-search-ipt` : 'search-ipt'; // 搜索输入框id值
                const SEARCH_IPT_CLEAR_ID = settings.rightToolbarClass ? `${settings.rightToolbarClass}-search-clear` : 'search-clear'; // 搜索输入框清空按钮id值
                const SEARCH_BTN_ID = settings.rightToolbarClass ? `${settings.rightToolbarClass}-search-btn` : 'search-btn'; // 搜索按钮id值

                const SEARCH_INPUT =
                    '<div class="toolbar-search-wrapper">' +
                    '<input class="form-control form-control-sm search" id="' + SEARCH_IPT_ID + '" name="toolbarSearch" placeholder="' + settings.searchPlaceholder + '">' +
                    '<span class="toolbar-search-wrapper__clear">' +
                    '<i class="viconfont vicon-qingchu" id="' + SEARCH_IPT_CLEAR_ID + '"></i>' +
                    '</span>' +
                    '<button class="btn btn-outline-primary toolbar-search-wrapper__btn" id="' + SEARCH_BTN_ID + '"><i class="viconfont vicon-a-Searchsousuo"></i></button>' +
                    '</div>'; // 搜索框HTML

                if (settings.rightToolbarClass) {
                    $(`.table-toolbar-wrapper__right.${settings.rightToolbarClass}`).prepend(SEARCH_INPUT);
                } else {
                    $('.table-toolbar-wrapper__right').prepend(SEARCH_INPUT);
                }

                // 搜索click监听
                $(`#${SEARCH_BTN_ID}`).on('click', () => {
                    let searchVal = $(`#${SEARCH_IPT_ID}`).val();

                    if (searchVal) {
                        ajaxParams.data.search = searchVal;

                        // 获取搜索结果
                        initTableData(ajaxParams);
                    }
                });

                // 搜索input监听
                $(`#${SEARCH_IPT_ID}`).on('input', (e) => {
                    let displayVal = settings.rightToolbarClass ?
                        $(`.table-toolbar-wrapper__right.${settings.rightToolbarClass} .toolbar-search-wrapper__clear`).css('display') :
                        $('.table-toolbar-wrapper__right .toolbar-search-wrapper__clear').css('display');

                    if (e.target.value) {
                        if (displayVal === 'none') {
                            if (settings.rightToolbarClass) {
                                $(`.table-toolbar-wrapper__right.${settings.rightToolbarClass} .toolbar-search-wrapper__clear`).css({'display': 'inline-flex', 'align-items': 'center'});
                            } else {
                                $('.table-toolbar-wrapper__right .toolbar-search-wrapper__clear').css({'display': 'inline-flex', 'align-items': 'center'});
                            }
                        }
                    } else if (displayVal === 'flex') {
                        if (settings.rightToolbarClass) {
                            $(`.table-toolbar-wrapper__right.${settings.rightToolbarClass} .toolbar-search-wrapper__clear`).css('display', 'none');
                        } else {
                            $('.table-toolbar-wrapper__right .toolbar-search-wrapper__clear').css('display', 'none');
                        }
                    }
                });

                // 清空监听
                $(`#${SEARCH_IPT_CLEAR_ID}`).on('click', () => {
                    $(`#${SEARCH_IPT_ID}`).val('');

                    // 刷新表格
                    ajaxParams.data.search = '';
                    initTableData(ajaxParams);

                    if (settings.rightToolbarClass) {
                        $(`.table-toolbar-wrapper__right.${settings.rightToolbarClass} .toolbar-search-wrapper__clear`).css('display', 'none');
                    } else {
                        $('.table-toolbar-wrapper__right .toolbar-search-wrapper__clear').css('display', 'none');
                    }
                });

                // 搜索 EnterKey down 监听
                $(`#${SEARCH_IPT_ID}`).on('keydown', (e) => {
                    if (e.target.id === SEARCH_IPT_ID && e.key === 'Enter') {
                        let searchVal = $(`#${SEARCH_IPT_ID}`).val();

                        if (searchVal) {
                            ajaxParams.data.search = searchVal;

                            // 获取搜索结果
                            initTableData(ajaxParams);
                        }
                    }
                });
            }

            // 获取$toolbarAdvancedSearch对象
            let $toolbarAdvancedSearch = settings.rightToolbarClass ?
                $(`.table-toolbar-wrapper__right.${settings.rightToolbarClass} .toolbar-advanced-search`) :
                $('.table-toolbar-wrapper__right .toolbar-advanced-search');

            // 初始化高级搜索框
            if (settings.advancedSearch && $toolbarAdvancedSearch.length === 0) {
                const ADVANCED_SEARCH_BUTTON = '<button class="btn btn-secondary-primary toolbar-advanced-search text-overflow-ellipsis ms-12" id="' + settings.advancedSearchId + '">高级搜索</button>'; // 高级搜索HTML

                // 生成高级搜索dropdown-toggle
                if (settings.search) {
                    if (settings.rightToolbarClass) {
                        $(`.table-toolbar-wrapper__right.${settings.rightToolbarClass} .toolbar-search-wrapper`).after(ADVANCED_SEARCH_BUTTON);
                    } else {
                        $('.table-toolbar-wrapper__right .toolbar-search-wrapper').after(ADVANCED_SEARCH_BUTTON);
                    }
                } else if (settings.rightToolbarClass) {
                    $(`.table-toolbar-wrapper__right.${settings.rightToolbarClass}`).prepend(ADVANCED_SEARCH_BUTTON);
                } else {
                    $('.table-toolbar-wrapper__right').prepend(ADVANCED_SEARCH_BUTTON);
                }
            }
        };

        return {
            init: function(options) {
                initConfigOptions(options);

                // 监听高级搜索派发的数据
                window.$on('advancedSearchEvent', (params) => {
                    if (params.url === ajaxParams.url) {
                        ajaxParams.data = Object.assign(ajaxParams.data, params.advancedParams);

                        // 获取搜索结果
                        initTableData(ajaxParams);
                    }

                });

                // 监听窗口变化，表格高度自适应
                window.addEventListener('resize', function() {
                    initTableHeight();
                });

            }
        };
    };
})(jQuery);