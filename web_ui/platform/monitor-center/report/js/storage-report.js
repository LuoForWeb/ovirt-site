/**
 * @description 存储报表配置组件
 * @author      您/团队的名字
 * @date        2026-03-13
 */
(function() {
    // 确保全局组件注册表存在
    if (!window.reportComponents) {
        window.reportComponents = {};
    }

    const storageReportComponent = {
        name: 'storageReport',

        // 1. HTML 模板
        // 将原属于存储报表的表单部分抽离至此
        html: `
            <!-- 资源列表 -->
            <div class="mb-3" id="resource_list_form_group">
                <label class="form-label">资源列表</label>
                <div class="accordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="resource_list_heading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#resource_list_collapse" aria-expanded="false" aria-controls="resource_list_collapse">
                                <span class="badge bg-primary me-2" id="resource_list_count">0</span>个资源已选择
                            </button>
                        </h2>
                        <div id="resource_list_collapse" class="accordion-collapse collapse" aria-labelledby="resource_list_heading">
                            <div class="accordion-body p-0">
                                <table id="resource_list_table"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 节点数据 -->
            <div class="mb-3" id="node_data_form_group">
                <label for="node_data_select2" class="form-label">节点数据</label>
                <select class="form-select" id="node_data_select2" multiple="multiple" data-placeholder="请选择节点数据"></select>
            </div>

            <!-- 定制数据项 -->
            <div class="mb-3" id="customized_data_form_group">
                <label for="customized_data_select2" class="form-label">定制数据项</label>
                <select class="form-select" id="customized_data_select2" multiple="multiple" data-placeholder="请选择定制数据项"></select>
            </div>
        `,

        // 2. 初始化方法
        init: function(container, data) {
            // 渲染HTML
            container.innerHTML = this.html;

            // 初始化各个组件
            this.initResourceListTable(data);
            this.initNodeDataSelect(data);
            this.initCustomizedDataSelect(data);
        },

        // 3. 参数收集方法
        getParams: function() {
            const resourceList = $('#resource_list_table').bootstrapTable('getSelections');
            const nodeData = $('#node_data_select2').val();
            const customizedData = $('#customized_data_select2').val();

            return {
                resourceList: resourceList.map(item => item.id),
                nodeData: nodeData,
                customizedData: customizedData,
            };
        },

        // 4. 校验方法
        isValid: function() {
            // 可以在此添加针对存储报表的特定校验逻辑
            // 例如，检查资源列表是否为空
            const resourceList = $('#resource_list_table').bootstrapTable('getSelections');
            if (resourceList.length === 0) {
                window.bs5Utils.Snack.show('danger', '请至少选择一个资源');
                return false;
            }
            return true;
        },

        // 5. 重置方法
        reset: function() {
            // 容器的innerHTML会在切换时被清空，这里主要是重置可能存在的插件状态
            // 但由于插件都在init时重新创建，所以此方法可能为空
        },

        // 内部辅助方法
        initResourceListTable: function(data) {
            const initialResourceIds = data && data.detail && data.detail.resourceList ? data.detail.resourceList : [];

            $('#resource_list_table').bootstrapTable({
                url: '/api/v2/report/storage/resource', // 假设的API端点
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageList: [10, 25, 50],
                idField: 'id',
                uniqueId: 'id',
                search: true,
                showRefresh: true,
                clickToSelect: true,
                maintainMetaData: true,
                columns: [
                    { checkbox: true },
                    { field: 'name', title: '名称' },
                    { field: 'type', title: '类型' }
                ],
                onPostBody: function() {
                    if (initialResourceIds.length > 0) {
                        $('#resource_list_table').bootstrapTable('checkBy', {
                            field: 'id',
                            values: initialResourceIds
                        });
                    }
                    // 更新已选资源计数徽章
                    const count = $('#resource_list_table').bootstrapTable('getSelections').length;
                    $('#resource_list_count').text(count);
                },
                onCheck: function() { $('#resource_list_count').text($('#resource_list_table').bootstrapTable('getSelections').length); },
                onUncheck: function() { $('#resource_list_count').text($('#resource_list_table').bootstrapTable('getSelections').length); },
                onCheckAll: function() { $('#resource_list_count').text($('#resource_list_table').bootstrapTable('getSelections').length); },
                onUncheckAll: function() { $('#resource_list_count').text($('#resource_list_table').bootstrapTable('getSelections').length); },
            });
        },

        initNodeDataSelect: function(data) {
            const initialNodeData = data && data.detail && data.detail.nodeData ? data.detail.nodeData : [];
            // 假设节点数据也是通过API获取
            $('#node_data_select2').select2({
                theme: 'bootstrap-5',
                // ajax: { ... }
                data: [ /* 示例数据 */ {id: 'cpu', text: 'CPU'}, {id: 'memory', text: '内存'} ]
            }).val(initialNodeData).trigger('change');
        },

        initCustomizedDataSelect: function(data) {
            const initialCustomizedData = data && data.detail && data.detail.customizedData ? data.detail.customizedData : [];
            $('#customized_data_select2').select2({
                theme: 'bootstrap-5',
                data: [ /* 示例数据 */ {id: 'iops', text: 'IOPS'}, {id: 'latency', text: '延迟'} ]
            }).val(initialCustomizedData).trigger('change');
        }
    };

    // 将组件注册到全局
    window.reportComponents.storage = storageReportComponent;

})();