/*
 * @Author: ChengJiaFu
 * @Date: 2025-07-25 11:43:19
 * @Description: jobs
 * @version: 1.0
 */
var Jobs = (() => {
    const initListeners = () => {
        // 监听tabs change
        $('#job_navs').on('click', function(event) {
            if (event.target.tagName === 'A') { // 检查点击的元素是否是一个 <a> 标签
                // 获取父元素 li.nav-item
                let clickedItem = event.target.parentElement;
                let currentTabName = clickedItem.getAttribute('data-type');

                switch (currentTabName) {
                    case 'current': // 当前任务
                        if (timerTask.ORCHESTRATION_JOB_TIMER) { // 如果编排任务定时器存在则清除
                            clearTimeout(timerTask.ORCHESTRATION_JOB_TIMER);
                        }

                        window.$emit('updateCurrentJobPage', {}); // 触发更新当前任务tab页
                        break;
                    case 'history': // 历史任务
                        if (timerTask.CURRENT_JOB_TIMER) { // 如果当前任务定时器存在则清除
                            clearTimeout(timerTask.CURRENT_JOB_TIMER);
                        }

                        if (timerTask.ORCHESTRATION_JOB_TIMER) { // 如果编排任务定时器存在则清除
                            clearTimeout(timerTask.ORCHESTRATION_JOB_TIMER);
                        }

                        window.$emit('updateHistoryJobPage', {}); // 触发更新历史任务tab页
                        break;
                    case 'orchestration': // 编排任务
                        if (timerTask.CURRENT_JOB_TIMER) { // 如果当前任务定时器存在则清除
                            clearTimeout(timerTask.CURRENT_JOB_TIMER);
                        }

                        window.$emit('updateOrchestrationJobPage', {}); // 触发更新任务编排tab页
                        break;
                    default:
                        break;
                }
            }
        });

        // 监听首页的任务点击
        let route = History.getState();
        let tabId = route.data.tabId || '';

        if (tabId) {
            // 移除上一个 active tab
            $('#job_navs').find('.nav-item.active').removeClass('active');
            $('#jobs_tab_content').find('.tab-pane.active').removeClass('active');

            switch (tabId) {
                case 'currentLi':
                    $('#currentLi').addClass('active');
                    $('#current_job_tabpane').addClass('active');

                    window.$emit('updateCurrentJobPage', {}); // 触发更新当前任务tab页

                    break;
                case 'historyLi':
                    $('#historyLi').addClass('active');
                    $('#history_job_tabpane').addClass('active');

                    window.$emit('updateHistoryJobPage', {}); // 触发更新历史任务tab页

                    break;
                case 'jobOrchestrationLi':
                    $('#jobOrchestrationLi').addClass('active');
                    $('#orchestration_job_tabpane').addClass('active');

                    window.$emit('updateOrchestrationJobPage', {}); // 触发更新任务编排tab页

                    break;
                default:
                    break;
            }
        }
    }
    return {
        init: () => {
            initListeners();
        }
    }
})();

jQuery(document).ready(() => {
    Jobs.init();
});