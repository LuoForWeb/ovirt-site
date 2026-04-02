/*
 * @Author: ChengJiaFu
 * @Date: 2025-09-15 09:57:02
 * @Description: 待审批报告列表
 * @version: 1.0
 */
var TodoList = function () {
    const STATUS = {
        PENDING: 0,
        ARCHIVED: 1,
        APPROVALING: 2,
        REJECTED: 3,
        REVOKE: 4,
        GENERATED: 9
    };

    /**
     * 渲染报告列表
     * @param {*} arraylist 报告列表
     * @param {*} reportType 报告类型（待审批、待发起）
     * @returns 
     */
    const renderReportList = (arraylist, reportType) => {
        if (!Array.isArray(arraylist) || arraylist.length === 0) {
            let nodataDes = '';
            switch (reportType) {
                case 'awaiting-approval-report': // 待审批报告
                    nodataDes = '暂无待审批报告';
                    break;
                case 'awaiting-initiate-report': // 待发起报告
                    nodataDes = '暂无待发起报告';
                    break;
                case 'awaiting-approval-plan': // 待审批方案
                    nodataDes = '暂无待审批方案';
                    break;
                case 'awaiting-initiate-plan': // 待发起方案
                    nodataDes = '暂无待发起方案';
                    break;
                default:
                    break;
            }

            const noDataHtml = '<div class="nodata-wrap">' + 
                                    '<img src="./img/platform/table-icon/no-data.svg" alt=""></img>' + 
                                    '<span class="nodata-wrap__text">' + nodataDes + '</span>' + 
                                '</div>';
            return noDataHtml;
        };

        let resultHtml = '';
        arraylist.forEach(item => {
            let badge = '';
            let time = '';
            let typeDes = '';
            let description = '';
            let moveDes = '';
            let routeBtnClass = '';
            let uuid = '';

            switch(reportType) {
                case 'awaiting-approval-report': // 待审批报告
                    badge = `<span class="badge badge-warning">待我审批</span>`;
                    time = item.report_time;
                    typeDes = '待审批';

                    // approval_list 掐头去尾 获取审批人列表
                    let approvers = item.approval_list.slice(1, -1).map(i => i.user_name);
                    description = `待用户${approvers.join('、')}审批`;
                    moveDes = '前往审批';
                    routeBtnClass = 'awaiting-approval-report-btn';
                    uuid = item.job_uuid;
                    break;
                case 'awaiting-initiate-report': // 待发起报告
                    badge = `<span class="badge badge-secondary">待发起</span>`;
                    time = item.create_time;
                    typeDes = '待发起报告';
                    description = '待发起报告';
                    moveDes = '前往发起';
                    routeBtnClass = 'awaiting-initiate-report-btn';
                    uuid = item.job_uuid;
                    break;
                case 'awaiting-approval-plan': // 待审批方案
                    badge = `<span class="badge badge-secondary">待审批</span>`;
                    time = item.update_time;
                    typeDes = '待审批方案';
                    description = '待审批方案';
                    moveDes = '前审批';
                    routeBtnClass = 'awaiting-approval-plan-btn';
                    uuid = item.uuid;
                    break;
                case 'awaiting-initiate-plan': // 待发起方案
                    badge = `<span class="badge badge-secondary">待发起</span>`;
                    time = item.update_time;
                    typeDes = '待发起方案';
                    description = '待发起方案';
                    moveDes = '前发起';
                    routeBtnClass = 'awaiting-initiate-plan-btn';
                    uuid = item.uuid;
                    break;
                default:
                    break;
            }

            resultHtml += `
                <div class="todo-list-cards__item card-wrapper">
                    <div class="card-wrapper__header">
                        ${badge}
                        <span class="time">${time}</span>
                    </div>
                    <div class="card-wrapper__content">
                        <div class="card-wrapper__content__info">
                            <span class="title">${item.name}${typeDes}</span>
                            <span class="description">${description}</span>
                        </div>
                        <button class="btn btn-outline-primary ${routeBtnClass}" data-uuid="${uuid}">${moveDes}</button>
                    </div>
                </div>`;
        });

        return resultHtml;
    }

    /**
     * 获取待审批列表
     */
    const initAwaitingApprovalReport = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({ offset:0, type: 3 }, '/api/v1/industry/report', 'GET', function (res) {
            try {
                if (res.success) {
                    let awaitingApprovalList = res.data.rows.length > 0 ? res.data.rows.filter(i => i.approval_flag).map(i => { return { ...i,approval_list: JSON.parse(i.approval_list) }}) : [];

                    $('#awaiting_approval_report_cards').empty();
                    $('#awaiting_approval_report_total').html(awaitingApprovalList.length);
                    $('#awaiting_approval_report_cards').html(renderReportList(awaitingApprovalList, 'awaiting-approval-report'));

                    $('.awaiting-approval-report-btn').on('click', () => {
                        LOCATION('./content/platform/industry/report_list.php', 'industry_report');
                    });
                } else {
                    UIToastr.showWarning('获取待审批报告数据失败');
                }
            } catch (error) {
                UIToastr.showWarning('获取待审批报告数据失败');
            } finally {
                Metronic.unblockUI('.todo-list-content');
            }
        });
    }

    /**
     * 获取待发起列表
     */
    const initAwaitingInitiateReport = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({ offset:0, type: 4 }, '/api/v1/industry/report', 'GET', function (res) {
            try {
                if (res.success) {
                    let awaitingInitiateList = res.data.rows;
                    $('#awaiting_initiate_report_cards').empty();
                    $('#awaiting_initiate_report_total').html(res.data.total || 0);
                    $('#awaiting_initiate_report_cards').html(renderReportList(awaitingInitiateList, 'awaiting-initiate-report'));

                    $('.awaiting-initiate-report-btn').on('click', function() {
                        let uuid = $(this).attr('data-uuid');
                        LOCATION(`./content/platform/dataverification/verification_job_details.php?type=37&uuid=${uuid}`, 'verification_job');
                    });
                } else {
                    UIToastr.showWarning('获取待发起报告数据失败');
                }
            } catch (error) {
                UIToastr.showWarning('获取待发起报告数据失败');
            } finally {
               Metronic.unblockUI('.todo-list-content'); 
            }
        });
    }

    /**
     * 获取待审批方案
     */
    const initAwaitingApprovalPlan = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({ offset:0, type: 4 }, '/api/v1/industry/plan', 'GET', function (res) {
            try {
                if (res.success) {
                    let awaitingApprovalList = res.data.rows;
                    $('#awaiting_approval_plan_cards').empty();
                    $('#awaiting_approval_plan_total').html(res.data.total || 0);
                    $('#awaiting_approval_plan_cards').html(renderReportList(awaitingApprovalList, 'awaiting-approval-plan'));

                    $('.awaiting-approval-plan-btn').on('click', function() {
                        let uuid = $(this).attr('data-uuid');
                        LOCATION(`./content/platform/industry/plan_list.php?uuid=${uuid}`, 'industry_plan');
                    });
                } else {
                    UIToastr.showWarning('获取待审批方案数据失败');
                }
            } catch (error) {
                UIToastr.showWarning('获取待审批方案数据失败');
            } finally {
                Metronic.unblockUI('.todo-list-content');
            }
        });
    }

    /**
     * 获取待发起方案
     */
    const initAwaitingInitiatePlan = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({ offset:0, type: 5 }, '/api/v1/industry/plan', 'GET', function (res) {
            try {
                if (res.success) {
                    let awaitingApprovalList = res.data.rows;
                    $('#awaiting_initiate_plan_cards').empty();
                    $('#awaiting_initiate_plan_total').html(res.data.total || 0);
                    $('#awaiting_initiate_plan_cards').html(renderReportList(awaitingApprovalList, 'awaiting-initiate-plan'));

                    $('.awaiting-initiate-plan-btn').on('click', function() {
                        let uuid = $(this).attr('data-uuid');
                        LOCATION(`./content/platform/industry/plan_list.php?uuid=${uuid}`, 'industry_plan');
                    });
                } else {
                    UIToastr.showWarning('获取待审批方案数据失败');
                }
            } catch (error) {
                UIToastr.showWarning('获取待审批方案数据失败');
            } finally {
                Metronic.unblockUI('.todo-list-content');
            }
        });
    }

    return {
        init: function () {
            initAwaitingApprovalReport();
            initAwaitingInitiateReport();
            initAwaitingApprovalPlan();
            initAwaitingInitiatePlan();
        }
    }
}();

$(document).ready(function () {
    TodoList.init();
});