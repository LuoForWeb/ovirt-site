var MessageCenter = function () {
    const STATUS = {
        PENDING: 0,
        ARCHIVED: 1,
        APPROVALING: 2,
        REJECTED: 3,
        REVOKE: 4,
        GENERATED: 9
    };
   
    const initMsgCount = function () {
        pAjaxRequest({}, '/api/v1/industry/message_summary', 'GET', function (res) {
            $('#total_report').html(res.data.total || 0);
            $('#awaiting_approval_report_total').html(res.data.pending || 0);
            $('#approving_report_total').html(res.data.approving || 0);
            $('#completed_report_total').html(res.data.archived || 0);
            $('#rejected_report_total').html(res.data.rejected || 0);
            $('#withdrawn_report_total').html(res.data.revoke || 0);

            getAllReports();
        });
    }

    /**
     * 渲染消息列表
     * @param {*} arraylist 报告列表
     * @param {*} reportType 报告类型（待审批、待发起）
     * @returns 
     */
    const renderReportList = (arraylist, reportType) => {
        if (!Array.isArray(arraylist) || arraylist.length === 0) {
            let nodataDes = '';
            switch (reportType) {
                    case 'total-report': // 全部
                        nodataDes = '暂无消息';
                        break;
                    case 'awaiting-approval-report': // 待审批
                        nodataDes = '暂无待审批消息';
                        break;
                    case 'approving-report': // 审批中
                        nodataDes = '暂无审批中消息';
                        break;
                    case 'completed-report': // 已完成
                        nodataDes = '暂无已完成消息';
                        break;
                    case 'rejected-report': // 已驳回
                        nodataDes = '暂无已驳回消息';
                        break;
                    case 'withdrawn-report': // 已撤回
                        nodataDes = '暂无已撤回消息';
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
            let approvers = [];
            let approvalList = [];
            if (item.approval_list) {
                // approval_list 掐头去尾 获取审批人列表
                approvalList = JSON.parse(item.approval_list);
                approvers = approvalList.slice(1, -1).map(i => i.user_name);
            }
            let status = item.status;
            let badge = '';
            let time = '';
            let typeDes = '';
            let description = '';
            let moveDes = '';
            let routeBtnClass = 'detail-btn';
            let uuid = '';

            switch(status) {
                case STATUS.PENDING: // 待审批
                    badge = `<span class="badge badge-warning">待审批</span>`;
                    time = item.create_time;
                    typeDes = '待审批';
                    description = `待用户${approvers.join('、')}审批`;
                    moveDes = '前往审批';
                    uuid = item.uuid;
                    break;
                case STATUS.APPROVALING: // 审批中
                    badge = `<span class="badge badge-primary">审批中</span>`;
                    time = item.create_time;
                    typeDes = '审批中';
                    description = `用户${approvers.join('、')}正在审批中`;
                    moveDes = LANG.UI_PLATFORM_INDUSTRY_REPORT_VIEW_DETAIL;
                    uuid = item.uuid;
                    break;
                case STATUS.ARCHIVED: // 已完成
                    badge = `<span class="badge badge-primary">已完成</span>`;
                    time = item.create_time;
                    typeDes = '已完成';
                    description = `用户${approvers.join('、')}已完成审批`;
                    moveDes = LANG.UI_PLATFORM_INDUSTRY_REPORT_VIEW_DETAIL;
                    uuid = item.uuid;
                    break;
                
                case STATUS.REJECTED: // 已驳回
                    badge = `<span class="badge badge-secondary">已驳回</span>`;
                    time = item.create_time;
                    typeDes = '已驳回';

                    let rejectUser = '';
                    let reason = '';
                    approvalList.slice(1, -1).forEach(i => {
                        if (i.status == 2) { //流程内部，驳回状态是2
                            rejectUser = i.user_name;
                            reason = i.reason || '无';
                        }
                    })
                    description = `用户${rejectUser}已驳回审批，原因：${reason}`;

                    moveDes = LANG.UI_PLATFORM_INDUSTRY_REPORT_VIEW_DETAIL;
                    uuid = item.uuid;
                    break;
                case STATUS.REVOKE: // 已撤回
                    badge = `<span class="badge badge-secondary">已撤销</span>`;
                    time = item.create_time;
                    typeDes = '已撤销';
                    description = `用户${approvers.join('、')}撤销审批`;
                    moveDes = LANG.UI_PLATFORM_INDUSTRY_REPORT_VIEW_DETAIL;
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

    const getAllReports = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({status: 99}, '/api/v1/industry/report', 'GET', (res) => {
            try {
                let reports = res.data.rows || [];
                $('#total_report_cards').empty();
                $('#total_report_cards').html(renderReportList(reports, 'total-report'));
                
                $(".detail-btn").off().on('click', function () {
                    LOCATION('./content/platform/industry/report_list.php', 'industry_report');
                });
            } catch (error) {
                console.log(error, 'error');
                UIToastr.showWarning('获取消息列表总数据失败');
            } finally {
                Metronic.unblockUI('.todo-list-content');
            }
        });
    }

    const getPendingReports = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({status: 0}, '/api/v1/industry/report', 'GET', (res) => {
            try {
                let reports = res.data.rows || [];
                $('#awaiting_approval_report_cards').empty();
                $('#awaiting_approval_report_cards').html(renderReportList(reports, 'awaiting-approval-report'));

                $(".detail-btn").off().on('click', function () {
                    LOCATION('./content/platform/industry/report_list.php', 'industry_report');
                });
            } catch (error) {
                UIToastr.showWarning('获取待审批消息数据失败');
            } finally {
                Metronic.unblockUI('.todo-list-content');
            }
        });
    }

    const getApprovingReports = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({status: 2}, '/api/v1/industry/report', 'GET', (res) => {
            try {
                let reports = res.data.rows || [];
                $('#approving_report_cards').empty();
                $('#approving_report_cards').html(renderReportList(reports, 'approving-report'));

                $(".detail-btn").off().on('click', function () {
                    LOCATION('./content/platform/industry/report_list.php', 'industry_report');
                });
            } catch (error) {
                UIToastr.showWarning('获取审批中消息数据失败');
            } finally {
                Metronic.unblockUI('.todo-list-content');
            }
        });
    }

    const getApprovedReports = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({status: 1}, '/api/v1/industry/report', 'GET', (res) => {
            try {
                let reports = res.data.rows || [];
                $('#completed_report_cards').empty();
                $('#completed_report_cards').html(renderReportList(reports, 'completed-report'));

                $(".detail-btn").off().on('click', function () {
                    LOCATION('./content/platform/industry/report_list.php', 'industry_report');
                });
            } catch (error) {
                UIToastr.showWarning('获取已完成消息数据失败');
            } finally {
                Metronic.unblockUI('.todo-list-content');
            }
        });
    }

    const getRejectedReports = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({status: 3}, '/api/v1/industry/report', 'GET', (res) => {
            try {
                let reports = res.data.rows || [];
                $('#rejected_report_cards').empty();
                $('#rejected_report_cards').html(renderReportList(reports, 'rejected-report'));

                $(".detail-btn").off().on('click', function () {
                    LOCATION('./content/platform/industry/report_list.php', 'industry_report');
                });
            } catch (error) {
                UIToastr.showWarning('获取已驳回消息数据失败');
            } finally {
                Metronic.unblockUI('.todo-list-content');
            }
        });
    }

    const getWithdrawnReports = () => {
        Metronic.blockUI({ target: '.todo-list-content', animate: true });

        pAjaxRequest({status: 4}, '/api/v1/industry/report', 'GET', (res) => {
            try {
                let reports = res.data.rows || [];
                $('#withdrawn_report_cards').empty();
                $('#withdrawn_report_cards').html(renderReportList(reports, 'withdrawn-report'));

                $(".detail-btn").off().on('click', function () {
                    LOCATION('./content/platform/industry/report_list.php', 'industry_report');
                });
            } catch (error) {
                UIToastr.showWarning('获取已撤回消息数据失败');
            } finally {
                Metronic.unblockUI('.todo-list-content');
            }
        });
    }

    const addListeners = function () {
        $('#message_list_tabs').on('click', function(event) {
            if (event.target.tagName === 'SPAN' || event.target.tagName === 'A') {
                // 获取父元素 li.nav-item
                const clickedItem = event.target.closest('li');
                if (!clickedItem || !clickedItem.hasAttribute('data-type')) {
                    return;
                }

                const currentTabName = clickedItem.getAttribute('data-type');

                switch (currentTabName) {
                    case 'total-report': // 全部
                        getAllReports();
                        break;
                    case 'awaiting-approval-report': // 待审批
                        getPendingReports();
                        break;
                    case 'approving-report': // 审批中
                        getApprovingReports();
                        break;
                    case 'completed-report': // 已完成
                        getApprovedReports();
                        break;
                    case 'rejected-report': // 已驳回
                        getRejectedReports();
                        break;
                    case 'withdrawn-report': // 已撤回
                        getWithdrawnReports();
                        break;
                    default:
                        break;
                }
            }
        });
    }

    return {
        init: function () {
            addListeners();
            initMsgCount();
        }
    }
}();

$(document).ready(function () {
    MessageCenter.init();
});