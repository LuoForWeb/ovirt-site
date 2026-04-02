var TodoList = function () {
    const STATUS = {
        PENDING: 0,
        ARCHIVED: 1,
        APPROVALING: 2,
        REJECTED: 3,
        REVOKE: 4,
        GENERATED: 9
    };
    const addListeners = function () {
        $('.tab-items').on('click', function (e) {
            $('.tab-items').removeClass('active');
            $(this).addClass('active');
            initTodoReport();
        })
    }

    const initMsgCount = function () {
        pAjaxRequest({}, '/api/v1/industry/message_summary', 'GET', function (res) {
            $('.tab-items.waiting .count').text(res.data.waiting);
        });
    }

    const initTodoReport = function () {
        var status = $('.tab-items.active').attr('data-status');
        pAjaxRequest({offset:0, type: 4, status: status}, '/api/v1/industry/report', 'GET', function (res) {
            if (status == 9) {
                initWaitingGenerateReportMessage(res.data.rows);
            } else {
                initTodoReportMessage(res.data.rows);
            }
        })
    }

    var initTodoReportMessage = function (data) {
        var html = '';
        var count = 0; //待我审批的数量计算放到这里查全部的，然后根据approval_flag确定是否是我审批的
        for (let i = 0; i < data.length; i++) {
            var approvalFlag = data[i].approval_flag;
            if (approvalFlag) {
                count++;
                var content = data[i].approval_list == null ? [] : JSON.parse(data[i].approval_list);
                content = content.slice(1, -1);
                console.log(content);
                var status = data[i].status;
                var statusDes = '';
                var statusClass = '';
                var description = '';
                var statusLabel = '';

                statusLabel = `label label-sm label-info`;
                statusClass = `pending-item`;
                statusDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING_ME;
                description = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING_ME;

                const nameArr = content.map(obj => obj.user_name);
                var nameStr = nameArr.join('、');
                var mark = `${LANG.UI_PLATFORM_INDUSTRY_REPORT_WAITTING_APPROVE_USER_PREFIX}${nameStr}${LANG.UI_PLATFORM_INDUSTRY_APPROVE}`;

                html += `<div class="item mt8">
                            <div class="item-label"><span class='${statusLabel} ${statusClass}'>${statusDes}</span><span class="approve-time" style="float: right;color:#666666;margin-left:auto;white-space:nowrap">${data[i].create_time}</span></div>
                            <div class="item-info mt8"><div><span class="item-desc">${data[i].name}${description}</span><br><span class="item-mark">${mark}</span></div> <button type="button" class="btn adv_btn brr2 p-lr8 detail-btn">${LANG.UI_PLATFORM_INDUSTRY_GO_APPROVE}</button></div>
                        </div>`;
            }
        }
        $('.tab-items.pending .count').text(count);
        $('.msg-list').empty().append(html);
        $(".detail-btn").off().on('click', function () {
            LOCATION('./content/platform/industry/report_list.php', 'industry_report');
        })
    }

    var initWaitingGenerateReportMessage = function (data) {
        var html = '';
        for (let i = 0; i < data.length; i++) {
            var status = data[i].status;
            var statusDes = '';
            var statusClass = '';
            var description = '';
            var statusLabel = '';
            var mark = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING_START_REPORT;
            statusDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING_START_REPORT_STATUS;
            description = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING_START_REPORT;

            statusLabel = `label label-sm label-default`;
            statusClass = `revoke-item`; //样式类

            html += `<div class="item mt8">
                            <div class="item-label"><span class='${statusLabel} ${statusClass}'>${statusDes}</span><span class="approve-time" style="float: right;color:#666666;margin-left:auto;white-space:nowrap">${data[i].create_time}</span></div>
                            <div class="item-info mt8"><div><span class="item-desc">${data[i].name}${description}</span><br><span class="item-mark">${mark}</span></div> <button type="button" data-uuid="${data[i].job_uuid}" class="btn adv_btn brr2 p-lr8 detail-btn">${LANG.UI_PLATFORM_INDUSTRY_GO_START_REPORT}</button></div>
                        </div>`;
        }
        $('.msg-list').empty().append(html);
        $(".detail-btn").off().on('click', function () {
            // jump verifition_job
            var uuid = $(this).attr('data-uuid');
            var url = './content/platform/dataverification/verification_job_details.php?type=37&uuid=' + uuid;
            LOCATION(url, 'verification_job');
        })
    }


    return {
        init: function () {
            addListeners();
            initMsgCount();
            initTodoReport();
        }
    }
}();

$(document).ready(function () {
    TodoList.init();
});