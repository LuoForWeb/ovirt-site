(function($) {

    $.fn.baseTableConfig = function() {
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
        const TABLE_TOOLBAR_HEIGHT = 48; // table toolbar height
        const TABLE_HEADER_HEIGHT = 40; // table header height
        const TABLE_ROW_HEIGHT = 52; // table row height
        const TABLE_PAGINATION_HEIGHT = 44; // table pagination height
        const TABLE_IN_MODAL_WITHOUT_TOOLBAR_NODATA_HEIGHT = 240; // 在模态框中使用的不带toolbar的无数据时表格高度，计算公式：40(table header height) + 200(nodata td height)
        const TABLE_IN_MODAL_WITH_TOOLBAR_NODATA_HEIGHT = 288; // 在模态框中使用的带toolbar的无数据时表格高度，计算公式：32(toolbar height) + 16(margin-bottom) + 40(table header height) + 200(nodata td height)
        const TABLE_IN_MODAL_WITHOUT_TOOLBAR_WITHOUT_PAGINATION_HASDATA_HEIGHT = 404; // 在模态框中使用的不带toolbar且不带pagination的有数据时表格高度，模态框 modal-body 最大高度500px，计算下来表格超过7行数据就会高度溢出，因而计算公式为：40(table header height) + 7 * 52(table row height)
        const TABLE_IN_MODAL_WITHOUT_TOOLBAR_WITH_PAGINATION_HASDATA_HEIGHT = 448; // 在模态框中使用的不带toolbar且带pagination的有数据时表格高度，模态框 modal-body 最大高度500px，计算下来表格超过7行数据就会高度溢出，因而计算公式为：40(table header height) + 7 * 52(table row height) + 44(table pagination)
        const TABLE_IN_MODAL_WITH_TOOLBAR_WITHOUT_PAGINATION_HASDATA_HEIGHT = 400; // 在模态框中使用的带toolbar且不带pagination的有数据时表格高度，模态框 modal-body 最大高度500px，计算下来表格超过6行数据就会高度溢出，因而计算公式为：32(toolbar height) + 16(margin-bottom) + 40(table header height) + 6 * 52(table row height)
        const TABLE_IN_MODAL_WITH_TOOLBAR_WITH_PAGINATION_HASDATA_HEIGHT = 444; // 在模态框中使用的带toolbar且带pagination的有数据时表格高度，模态框 modal-body 最大高度500px，计算下来表格超过6行数据就会高度溢出，因而计算公式为：32(toolbar height) + 16(margin-bottom) + 40(table header height) + 6 * 52(table row height) + 44(table pagination)
        const TABLE_DEFAULT_TOOLBAR_DATERANGEPICKER_TEXT = readLang('PUBLIC_TABLE_TIME_BEGIN_TO_END'); // 使用表格toolbar-daterangepicker组件时默认文本值
        let currentListSize = 0; // 当前页总条数（不包含未选且被禁用项）
        let currentPageSize = 0; // 当前页总条数（包含未选且被禁用项）
        let rowsLength = 0; // 当前页接口返回的条数
        let $table = $(`#${TABLE_ID}`); // table jquery obj
        let ajaxParams = {
            data: {}, // 请求参数
            url: '', // 接口API
            type: '' // 请求类型
        }; // ajax请求参数
        let settings = {}; // 表格配置项object

        /**
         * 表格高度动态自适应
         * @param pageSize 当前 page list 大小值
         */
        const initTableHeight = (pageSize) => {
            let $tableToolbar = $(`${settings.tableContentWrapper}`).prev();

            if ($tableToolbar.length === 0) {
                // 不带 toolbar 时, table-content-wrapper 高度100%
                $(`${settings.tableContentWrapper}`).css('height', '100%');
            }

            if (!settings.pagination) {
                // 不带 pagination 时, fixed-table-container 高度100%
                $(`${settings.tableContentWrapper} .bootstrap-table .fixed-table-container`).attr('style', 'height: 100% !important');
            }

            // 针对表格tableContentWrapper的父级给定固定高度的场景（如表格上下都有内容的页面），要预先动态调整父级高度，然后再获取 tableContentWrapper 高度
            if (settings.dynamicAdjustParentHeight) {
                if (pageSize < settings.pageSize) { // 切换的pageSize（可来自于pageSize或pageNum的切换）小于设置的pageSize时,为避免留白，将整个表格 tableContentWrapper 高度赋给 parentContaienr
                    let parentContainerHeight = ($tableToolbar.length !== 0 ? TABLE_TOOLBAR_HEIGHT : 0) + TABLE_HEADER_HEIGHT + TABLE_ROW_HEIGHT * pageSize + (settings.pagination ? TABLE_PAGINATION_HEIGHT : 0);
                    $(`${settings.parentContainer}`).css('height', parentContainerHeight);
                } else { // 切换的pageSize（可来自于pageSize或pageNum的切换）大于等于设置的pageSize时，将一开始设置的pageSize（如10页/条）时的原始高度赋给 parentContaienr
                    let parentContainerHeight = ($tableToolbar.length !== 0 ? TABLE_TOOLBAR_HEIGHT : 0) + TABLE_HEADER_HEIGHT + TABLE_ROW_HEIGHT * settings.pageSize + (settings.pagination ? TABLE_PAGINATION_HEIGHT : 0);
                    $(`${settings.parentContainer}`).css('height', parentContainerHeight);
                }
            }

            // 获取 table-content-wrapper class
            const TABLE_CONTENT_WRAPPER = settings.tableContentWrapper;

            // 记录完整展开时表格 pageSize 行的临界高度 critical height,计算公式：tableHeader(40) + tableBody(52 * pageSize) + tablePagination(44)
            const TABLE_CRITICAL_HEIGHT = TABLE_HEADER_HEIGHT + TABLE_ROW_HEIGHT * pageSize + (settings.pagination ? TABLE_PAGINATION_HEIGHT : 0);

            // 获取 table-content-wrapper 的高度
            let tableContentWrapperHeight = $(TABLE_CONTENT_WRAPPER).height();

            // 获取 bootstrap-table 的高度
            let tableHeight = $(`${TABLE_CONTENT_WRAPPER} .bootstrap-table`).height();

            if (tableHeight > tableContentWrapperHeight) {
                $(`${TABLE_CONTENT_WRAPPER} .bootstrap-table`).css('height', `${tableContentWrapperHeight}px`);

                // 调用 resetView() 重置表格高度
                $table.bootstrapTable('resetView');
            } else if (TABLE_CRITICAL_HEIGHT > tableContentWrapperHeight) { // table-content-wrapper 未超过临界高度
                $(`${TABLE_CONTENT_WRAPPER} .bootstrap-table`).css('height', `${tableContentWrapperHeight}px`);

                // 调用 resetView() 重置表格高度
                $table.bootstrapTable('resetView');
            } else { // table-content-wrapper 超过临界高度
                $(`${TABLE_CONTENT_WRAPPER} .bootstrap-table`).css('height', `${TABLE_CRITICAL_HEIGHT}px`);

                // 调用 resetView() 重置表格高度
                $table.bootstrapTable('resetView');
            }
        };

        /**
         * 初始化toolbar buttons 监听
         */
        const initToolbarButtons = () => {
            let $columnFilterDropdownToggle = $(`${settings.buttonsToolbar} .keep-open.btn-group .btn.btn-dropdown-toggle`);
            let $columnFilterDropdownMenu = $(`${settings.buttonsToolbar} .keep-open.btn-group .dropdown-menu`);

            let $exportDropdownToggle = $(`${settings.buttonsToolbar} .export.btn-group .btn.btn-dropdown-toggle`);
            let $exportDropdownMenu = $(`${settings.buttonsToolbar} .export.btn-group .dropdown-menu`);
            let $exportDropdownItem = $(`${settings.buttonsToolbar} .export.btn-group .dropdown-menu .dropdown-item`);

            // 监听 列筛选按钮 click
            $columnFilterDropdownToggle.on('click', () => {
                // 移除上一次打开的 export dropdown-menu
                $exportDropdownToggle.removeClass('show');
                $exportDropdownMenu.removeClass('show');
            });

            // dropdown-menu 阻止事件冒泡，在check时不会关闭dropdown
            $columnFilterDropdownMenu.click(function(e) {
                e.stopPropagation();
            });

            // 监听 表格导出按钮 click
            $exportDropdownToggle.on('click', () => {
                // 移除上一次打开的 column filter dropdown-menu
                $columnFilterDropdownToggle.removeClass('show');
                $columnFilterDropdownMenu.removeClass('show');
            });

            // 监听 表格导出 dropdown menu item click
            $exportDropdownItem.on('click', function(e) {
                e.preventDefault();
                if (!$(this).hasClass('active')) {
                    $exportDropdownItem.removeClass('active');
                    $(this).addClass('active');
                }
            });
        };

        /**
         * 初始化Tooltips
         */
        const initToolTips = () => {
            let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        };

        /**
         * 初始化DropDownMenus
         */
        const initDropDownMenus = () => {
            let $tableTbodyDropdownToggle = $(`#${TABLE_ID} .dropdown-wrapper .btn.dropdown-toggle`);
            let $tableTbodyDropdownMenu = $(`#${TABLE_ID} .dropdown-wrapper .dropdown-menu`);
            let $tableTbodyDropdownItem = $(`#${TABLE_ID} .dropdown-wrapper .dropdown-menu .dropdown-item`);

            // dropdown-menu 阻止事件冒泡，在check时不会关闭dropdown
            $tableTbodyDropdownMenu.click(function(e) {
                e.stopPropagation();
            });

            // 监听dropdown-item click事件，动态添加active样式
            $tableTbodyDropdownItem.on('click', function(e) {
                e.preventDefault();
                if (!$(this).hasClass('active')) {
                    $tableTbodyDropdownItem.removeClass('active');
                    $(this).addClass('active');
                }
            });

            // 监听 dropdown menu hide
            $tableTbodyDropdownToggle.on('hide.bs.dropdown', () => {
                // 清空 dropdown-item active 样式
                $tableTbodyDropdownItem.removeClass('active');
            });
        };

        /**
         * 初始化 btSelectAll checkbox 监听
         */
        const initBtSelectAllCheckBox = () => {
            $(`#${TABLE_ID} .bs-checkbox label input[name=\'btSelectAll\']`).on('change', (e) => {
                if (!e.target.checked) { // 全不选
                    let currentSelectedRows = $table.bootstrapTable('getSelections').length; // 当前选中条数

                    if (currentSelectedRows === 0) {
                        // btSelectAll checkbox 清除半选样式
                        $(`${settings.tableContentWrapper} thead .bs-checkbox label input[name=\'btSelectAll\']`).removeClass('indeterminate');
                    }
                }
            });
        };

        /**
         * 初始化 bt checkbox 监听
         * @param rowsLength 接口返回数据总条数
         */
        const initBtCheckBox = () => {
            if (settings.checkboxHeader) { // 未启用 隐藏 check-all checkbox
                let currentSelectedRows = $table.bootstrapTable('getSelections').length; // 当前选中条数
                let checkedFalseDisabledNums = $table.find('tbody .bs-checkbox label input[type=\'checkbox\']:disabled:not(:checked)').length; // 未选且被禁用checkbox数
                currentListSize = rowsLength - checkedFalseDisabledNums;

                if (currentListSize === 0) { // 全部被禁用时则禁用 btSelectAll checkbox
                    $(`#${TABLE_ID} .bs-checkbox label input[name=\'btSelectAll\']`).prop('disabled', true);
                } else if (currentSelectedRows > 0 && currentSelectedRows < currentListSize) { // 部分被选中时则增加 btSelectAll checkbox 半选样式
                    // btSelectAll checkbox 增加半选样式
                    $(`${settings.tableContentWrapper} thead .bs-checkbox label input[name=\'btSelectAll\']`).addClass('indeterminate');
                }
            }
        };

        /**
         * 初始化 table pagination button dropdown-toggle 监听
         */
        const initTablePaginationDropdown = () => {
            $(`${settings.tableContentWrapper} .fixed-table-pagination .page-list .dropdown-menu .dropdown-item`).on('click', (e) => {
                currentPageSize = parseInt(e.currentTarget.innerText);

                // 重置表格高度
                initTableHeight(currentPageSize);
            });
        };

        /**
         * 模态框中表格高度自适应
         * @param {*} hasData 表格是否有数据
         * @param {*} paramSize 当前表格分页大小
         */
        const handleModalTableHeight = (hasData, paramSize) => {
            if (hasData) { // has data
                let $tableToolbar = $(`${settings.tableContentWrapper}`).prev(); // 获取兄弟元素 table-toolbar
                let modalBodyTableWrapperHeight = 0;

                if ($tableToolbar.length > 0) { // 包含 table toolbar
                    modalBodyTableWrapperHeight = TABLE_TOOLBAR_HEIGHT + TABLE_HEADER_HEIGHT + (rowsLength < paramSize ? rowsLength : paramSize) * TABLE_ROW_HEIGHT + (settings.pagination ? TABLE_PAGINATION_HEIGHT : 0);

                    let tableHeight = settings.pagination ? TABLE_IN_MODAL_WITH_TOOLBAR_WITH_PAGINATION_HASDATA_HEIGHT : TABLE_IN_MODAL_WITH_TOOLBAR_WITHOUT_PAGINATION_HASDATA_HEIGHT;

                    if (modalBodyTableWrapperHeight > tableHeight) { // 带toolbar场景计算得到的 modalBodyTableWrapperHeight 高度超出 modal body 最大容纳6行时的临界高度时
                        $(`${settings.tableContentWrapper}`).parent().css('height', tableHeight + 'px');
                    } else {
                        $(`${settings.tableContentWrapper}`).parent().css('height', modalBodyTableWrapperHeight + 'px');
                    }
                } else { // 不包含 table toolbar
                    modalBodyTableWrapperHeight = TABLE_HEADER_HEIGHT + (rowsLength < paramSize ? rowsLength : paramSize) * TABLE_ROW_HEIGHT + (settings.pagination ? TABLE_PAGINATION_HEIGHT : 0);

                    let tableHeight = settings.pagination ? TABLE_IN_MODAL_WITHOUT_TOOLBAR_WITH_PAGINATION_HASDATA_HEIGHT : TABLE_IN_MODAL_WITHOUT_TOOLBAR_WITHOUT_PAGINATION_HASDATA_HEIGHT;

                    if (modalBodyTableWrapperHeight > tableHeight) { // 不带toolbar场景计算得到的 modalBodyTableWrapperHeight 高度超出 modal body 最大容纳7行时的临界高度时
                        $(`${settings.tableContentWrapper}`).parent().css('height', tableHeight + 'px');
                    } else {
                        $(`${settings.tableContentWrapper}`).parent().css('height', modalBodyTableWrapperHeight + 'px');
                    }
                }
            } else { // no data
                let $tableToolbar = $(`${settings.tableContentWrapper}`).prev(); // 获取兄弟元素 table-toolbar

                if ($tableToolbar.length > 0) { // 包含 table toolbar
                    $(`${settings.tableContentWrapper}`).parent().css('height', TABLE_IN_MODAL_WITH_TOOLBAR_NODATA_HEIGHT);
                } else { // 不包含 table toolbar
                    $(`${settings.tableContentWrapper}`).parent().css('height', TABLE_IN_MODAL_WITHOUT_TOOLBAR_NODATA_HEIGHT);
                }
            }
        };

        /**
         * 处理表格响应数据和初始化各个组件
         * @param response
         */
        const handleResponseData = (response) => {
            if (response.success) {
                if (typeof settings.responseHandler === 'function') {
                    response.data = settings.responseHandler(response);
                }

                if (typeof settings.onLoadSuccess === 'function') {
                    settings.onLoadSuccess(response.data);
                }

                if (response.data && response.data.rows.length > 0) {
                    $table.bootstrapTable('load', response.data);

                    let paramSize = currentPageSize === 0 ? settings.pageSize : currentPageSize;
                    rowsLength = response.data.rows.length;

                    if (settings.tableInModal) {
                        // 处理模态框中的有数据表格高度自适应
                        handleModalTableHeight(true, paramSize);
                    }

                    if (rowsLength < paramSize) { // 返回的数据小于设置的pageSize
                        // 初始化表格高度
                        initTableHeight(rowsLength);
                    } else { // 返回的数据大于等于设置的pageSize
                        // 初始化表格高度
                        initTableHeight(paramSize);
                    }

                    // 清空上一次的 btSelectAll checkbox 禁用样式
                    $(`${settings.tableContentWrapper} thead .bs-checkbox label input[name=\'btSelectAll\']`).prop('disabled', false);

                    // 清空上一次的 btSelectAll checkbox 半选样式
                    $(`${settings.tableContentWrapper} thead .bs-checkbox label input[name=\'btSelectAll\']`).removeClass('indeterminate');
                } else {
                    $table.bootstrapTable('load', { rows: [], total: 0 });

                    if (settings.tableInModal) {
                        // 处理模态框中的无数据表格高度自适应
                        handleModalTableHeight(false);
                    }

                    // 禁用 btSelectAll checkbox
                    $(`#${TABLE_ID} .bs-checkbox label input[name=\'btSelectAll\']`).prop('disabled', true);
                }
            } else {
                toastr.warning(response.message, readLang('PUBLIC_TABLE_GET_DATA'));
            }

            // 初始化 toolbar buttons 监听
            initToolbarButtons();

            // 初始化 Tooltip
            initToolTips();

            // 初始化 DropDownMenu 监听
            initDropDownMenus();

            // 初始化 checkbox 监听
            initBtCheckBox();

            // 初始化 btSelectAll checkbox 监听
            initBtSelectAllCheckBox();

            // 初始化 table pagination button dropdown-toggle 监听
            initTablePaginationDropdown();

            $table.bootstrapTable('hideLoading');
        };

        /**
         * 初始化表格数据
         * @param {*} params
         */
        const initTableData = (params) => {
            $table.bootstrapTable('showLoading');

            // 原生参数自定义处理
            if (typeof settings.queryParams === 'function') {
                params.data = settings.queryParams(params.data);
            }

            // 接口数据请求
            switch (params.type.toUpperCase()) {
            case 'GET':
                axiosGet(params.url, params.data).then(res => {
                    handleResponseData(res);
                });
                break;
            case 'POST':
                axiosPost(params.url, params.data).then(res => {
                    handleResponseData(res);
                });
                break;
            default:
                break;
            }
        };

        /**
         * 获取过滤器组件已选择的数据，便于分页时带上
         */
        const getFilterSelectedData = () => {
            let result = [];

            let dropdownMenuContents = $(`#${settings.filterBtnId}_dropdown_menu .filter-dropdown-menu__content`).children('.checkbox-wrapper');

            for (let i = 0; i < dropdownMenuContents.length; i++) {
                // 获取返回的勾选的数组的每项的key值
                let checkboxLabel = $(dropdownMenuContents[i]).find('.checkbox-wrapper__label');

                let item = { key: $(checkboxLabel).attr('name'), value: [] };

                let checkboxContent = $(dropdownMenuContents[i]).find('.checkbox-wrapper__content').find('.checkbox-wrapper__content__item');

                // 循环遍历checkbox-wrapper__content下的所有item
                for (let j = 0; j < checkboxContent.length; j++) {
                    // 获取已勾选的所有form-check-input
                    let checkedBox = $(checkboxContent[j]).find('.form-check-input:checked');
                    if (checkedBox.length > 0) {
                        // 获取每项勾选的checkbox的id数组
                        item.value.push($(checkedBox).prop('value'));
                    }
                }

                result.push(item);
            }

            // 检查是否每项都一个没选
            if (result.every(i => i.value.length === 0)) {
                result = [];
            }

            return result;
        };

        const ajaxRequest = function(params) {
            const SEARCH_IPT_ID = settings.rightCustomToolbar ? `${settings.rightCustomToolbar}-search-ipt` : 'search-ipt'; // 搜索输入框id值
            let param = {};

            if (settings.search && $(`#${SEARCH_IPT_ID}`).val()) { // 分页时搜索输入框带参数
                param.search = $(`#${SEARCH_IPT_ID}`).val();
            }

            if (settings.dateRangePickerId) { // 分页时toolbar-daterangepicker带参数
                let daterangepickerText = $(`#${settings.dateRangePickerId} .daterangepicker-text`).text();

                if (daterangepickerText && daterangepickerText !== TABLE_DEFAULT_TOOLBAR_DATERANGEPICKER_TEXT) {
                    param.startTime = daterangepickerText.split(' ⇀ ')[0];
                    param.endTime = daterangepickerText.split(' ⇀ ')[1];
                }
            }

            if (settings.filterBtnId) { // 分页时过滤器带参数
                let result = getFilterSelectedData();

                if (result.length > 0) {
                    result.forEach(i => {
                        if (i.value.length > 0) {
                            param[i.key] = i.value;
                        }
                    });
                }
            }

            ajaxParams = {
                data: Object.assign(settings.customParams, params.data, param),
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
            customParams: {}, // 自定义查询参数
            detailView: false, // 是否开启点击查看行详情
            dataType: 'json', // 期望从服务器接收的数据类型
            detailViewIcon: true, // 是否使用展开详情图标
            detailViewByClick: false, // 是否点击行即可展开详情
            data: [],
            fixedColumns: false, // 是否固定列
            headerStyle: headerStyle, // 表头样式
            requestHeader: {}, // 响应表头
            paginationLoop: false, // 是否开启分页循环
            icons: {
                export: 'viconfont vicon-a-Share-threefenxiang3', // 导出图标
                detailOpen: 'viconfont vicon-table-detail-open', // detail open
                detailClose: 'viconfont vicon-table-detail-close', // detail close
                columns: 'viconfont vicon-a-List-checkboxduoxuanliebiao', // 列选择列表图标
                refresh: 'viconfont vicon-biaogeshuaxin' // 刷新图标
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
            rightCustomToolbar: '', // right toolbar wrapper class
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
            tableContentWrapper: '.table-content-wrapper', // table-content-wrapper class
            tableInModal: false, // 是否是模态框中的表格
            dynamicAdjustParentHeight: false, // 是否需要动态调整表格tableContentWrapper的父容器高度（只针对表格tableContentWrapper的父容器为固定高度场景使用，如：表格上下都有内容时，并且要搭配parentContainer参数）
            parentContainer: '', // 表格tableContentWrapper的父容器class

            onCheck: (row, $element) => {
                let currentSelectedRows = $table.bootstrapTable('getSelections').length; // 当前选中条数

                if (currentSelectedRows < currentListSize) {
                    // btSelectAll checkbox 增加半选样式
                    $(`${settings.tableContentWrapper} thead .bs-checkbox label input[name=\'btSelectAll\']`).addClass('indeterminate');
                }

                if (currentSelectedRows === currentListSize) {
                    // btSelectAll checkbox 取消半选样式
                    $(`${settings.tableContentWrapper} thead .bs-checkbox label input[name=\'btSelectAll\']`).removeClass('indeterminate');
                }
            },
            onUncheck: (row, $element) => {
                let currentSelectedRows = $table.bootstrapTable('getSelections').length; // 当前选中条数

                if (currentSelectedRows > 0 && currentSelectedRows < currentListSize) {
                    // btSelectAll checkbox 增加半选样式
                    $(`${settings.tableContentWrapper} thead .bs-checkbox label input[name=\'btSelectAll\']`).addClass('indeterminate');
                }

                if (currentSelectedRows === 0) {
                    // btSelectAll checkbox 取消半选样式
                    $(`${settings.tableContentWrapper} thead .bs-checkbox label input[name=\'btSelectAll\']`).removeClass('indeterminate');
                }
            }
        };

        /**
         * 初始化表格配置
         * @param options 配置项
         */
        const initConfigOptions = (options) => {
            // 保存插件内部的核心回调
            const coreOnCheck = defaults.onCheck;
            const coreOnUncheck = defaults.onUncheck;

            settings = $.extend({}, defaults, options);

            // 获取用户自定义回调
            const userOnCheck = settings.onCheck;
            const userOnUncheck = settings.onUncheck;

            // 合并用户自定义回调和插件核心回调
            settings.onCheck = function(...args) {
                coreOnCheck.apply(this, args);
                if (typeof userOnCheck === 'function' && userOnCheck !== coreOnCheck) {
                    userOnCheck.apply(this, args);
                }
            };

            // 合并用户自定义回调和插件核心回调
            settings.onUncheck = function(...args) {
                coreOnUncheck.apply(this, args);
                if (typeof userOnUncheck === 'function' && userOnUncheck !== coreOnUncheck) {
                    userOnUncheck.apply(this, args);
                }
            };

            $table.bootstrapTable(settings);

            // 获取 right toolbar 作用域
            const $toolbarScope = settings.rightCustomToolbar ?
                $(`.table-toolbar-wrapper__right.${settings.rightCustomToolbar}`) :
                $('.table-toolbar-wrapper__right');

            // 始终移除现有的自定义工具栏元素，以避免在销毁和重新初始化表格时出现陈旧的 DOM 和事件处理程序
            $toolbarScope.find('.toolbar-search-wrapper').remove();
            $toolbarScope.find('.toolbar-advanced-search').remove();

            // 初始化搜索框
            if (settings.search) {
                const SEARCH_IPT_ID = settings.rightCustomToolbar ? `${settings.rightCustomToolbar}-search-ipt` : 'search-ipt'; // 搜索输入框id值
                const SEARCH_IPT_CLEAR_ID = settings.rightCustomToolbar ? `${settings.rightCustomToolbar}-search-clear` : 'search-clear'; // 搜索输入框清空按钮id值
                const SEARCH_BTN_ID = settings.rightCustomToolbar ? `${settings.rightCustomToolbar}-search-btn` : 'search-btn'; // 搜索按钮id值

                const SEARCH_INPUT =
                    '<div class="toolbar-search-wrapper">' +
                        '<input class="form-control form-control-sm search" id="' + SEARCH_IPT_ID + '" name="toolbarSearch" placeholder="' + settings.searchPlaceholder + '">' +
                        '<span class="toolbar-search-wrapper__clear">' +
                            '<i class="viconfont vicon-zujianshanchu" id="' + SEARCH_IPT_CLEAR_ID + '"></i>' +
                        '</span>' +
                        '<button class="btn btn-outline-primary toolbar-search-wrapper__btn" id="' + SEARCH_BTN_ID + '"><i class="viconfont vicon-gaojisousuo1"></i></button>' +
                    '</div>'; // 搜索框HTML

                $toolbarScope.prepend(SEARCH_INPUT);

                const $searchClearBtn = $toolbarScope.find('.toolbar-search-wrapper__clear');

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
                    if (e.target.value) {
                        if ($searchClearBtn.css('display') === 'none') {
                            $searchClearBtn.css({'display': 'inline-flex', 'align-items': 'center'});
                        }
                    } else if ($searchClearBtn.css('display') === 'flex') {
                        $searchClearBtn.css('display', 'none');
                    }
                });

                // 清空监听
                $(`#${SEARCH_IPT_CLEAR_ID}`).on('click', () => {
                    $(`#${SEARCH_IPT_ID}`).val('');

                    // 刷新表格
                    ajaxParams.data.search = '';
                    initTableData(ajaxParams);

                    $searchClearBtn.css('display', 'none');
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

            // 初始化高级搜索框
            if (settings.advancedSearch) {
                const ADVANCED_SEARCH_BUTTON = '<button class="btn btn-secondary-primary toolbar-advanced-search text-overflow-ellipsis ms-12" id="' + settings.advancedSearchId + '">' + readLang('PUBLIC_TIPS_ADVANCED_SEARCH') + '</button>'; // 高级搜索HTML

                // 生成高级搜索dropdown-toggle
                if (settings.search) {
                    $toolbarScope.find('.toolbar-search-wrapper').after(ADVANCED_SEARCH_BUTTON);
                } else {
                    $toolbarScope.prepend(ADVANCED_SEARCH_BUTTON);
                }
            }
        };

        return {
            init: function(options) {
                initConfigOptions(options);

                // 监听窗口变化，表格高度自适应
                window.addEventListener('resize', function() {
                    let paramSize = currentPageSize === 0 ? settings.pageSize : currentPageSize;

                    if (rowsLength < paramSize) { // 返回的数据小于设置的pageSize
                        // 初始化表格高度
                        initTableHeight(rowsLength);
                    } else { // 返回的数据大于等于设置的pageSize
                        // 初始化表格高度
                        initTableHeight(paramSize);
                    }
                });
            }
        };
    };
})(jQuery);