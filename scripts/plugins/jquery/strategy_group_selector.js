/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-04-10 15:55:59
 * @LastEditTime: 2025-04-10 16:43:45
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 * 配置项：
 * {
 *      module_type: CONF.MODULE_TYPE.NAS, // 模块类型
 *      defaultConfig: defaultConfig, // 初始化通用策略的配置
 *      backupStrategyDom: '#backupStrategyDiv' // 通用策略的dom
 * }
 * 使用：
 * $('.strategy-group-select-wrapper').strategyGroupSelector({module_type: CONF.MODULE_TYPE.NAS, defaultConfig: defaultConfig, backupStrategyDom: '#backupStrategyDiv'})
 */
(function ($) {
    $.fn.strategyGroupSelector = function (options) {
        // 当前页面位置
        const _dom = $(this);
        // 暂存所有本模块策略组信息
        const globalStrategy = [];
        // 初始化策略配置
        const defaultConfig = options.defaultConfig;
        // 初始化选择下拉框
        const initSelectorDom = function () {
            _dom.empty();
            let html = `<div class="form-group">
                            <div class="control-label col-md-1">${LANG.UI_GLOBAL_STRATEGY_SELECT_STRATEGY}</div>
                            <div class="col-md-4">
                                <select class="form-control select2me inline-block" id="strategySelect">
                                </select>
                                <a class="popovers ml15" data-container="body" data-trigger="hover"
                                    data-placement="right" data-content="${LANG.UI_GLOBAL_STRATEGY_SELECT_STRATEGY_TIPS}">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>`;
            _dom.append(html);
            // 初始化提示
            _dom.find('.popovers').popover();
        }
        // 获取策略组列表
        const initStrategyGroupOptions = function () {
            function initStrategyList(res) {
                if (!res.success) return;
                if (res.data.length > 0) {
                    // 清空策略列表，再插入新的策略列表
                    let data = res.data;
                    let strategyselect = _dom.find('#strategySelect');
                    strategyselect.empty();
                    for (var i = 0; i < data.length; i++) {
                        var option = '<option  value="' + data[i].uuid + '">' + data[i].text + '</option>';
                        globalStrategy[data[i].uuid] = data[i].strategy;
                        strategyselect.append(option);
                    }
                    // 初始化下拉框样式
                    _dom.find('#strategySelect').searchableSelect();
                    // 选择策略组监听
                    _dom.find('.searchable-select-item').on('click', strategySelectHandler);
                }

            }
            pAjaxRequest({ type: options.module_type }, '/api/v1/strategies/select', "GET", initStrategyList, true);
        }
        // 应用策略组
        const strategySelectHandler = function () {
                let index = _dom.find('#strategySelect option:selected').val();
                if(!index){
                    return;
                }
                let strategy = globalStrategy[index];
                defaultConfig.strategy = strategy;
                defaultConfig.timeFormateFlag = false;
                if(options.backupStrategyDom){
                    $(options.backupStrategyDom).initBackupStrategy(defaultConfig);
                }
        }
        const init = function () {
            initSelectorDom();
            initStrategyGroupOptions();
        }
        return this.each(function () {
            init();
        })
    }
})(jQuery)