var MessageCenter = function () {
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
            initTotalMsg();
        })
    }
   
    const initMsgCount = function () {
        pAjaxRequest({}, '/api/v1/industry/message_summary', 'GET', function (res) {
            $('.tab-items.all .count').text(res.data.total);
            $('.tab-items.pending .count').text(res.data.pending);
            $('.tab-items.approving .count').text(res.data.approving);
            $('.tab-items.archived .count').text(res.data.archived);
            $('.tab-items.rejected .count').text(res.data.rejected);
            $('.tab-items.revoke .count').text(res.data.revoke);
        });
    }

    const initTotalMsg = function () {
        var status = $('.tab-items.active').attr('data-status');
        pAjaxRequest({offset:0, type: 4, status: status}, '/api/v1/industry/report', 'GET', function (res) {
            console.log(res);
            initReportMessage(res.data.rows);
        })
    }

    var initReportMessage = function (data) {
        var html = '';
        for (let i = 0; i < data.length; i++) {
            var content = data[i].approval_list == null ? [] : JSON.parse(data[i].approval_list);
            content = content.slice(1, -1);
            console.log(content);
            var status = data[i].status;
            var statusDes = '';
            var statusClass = '';
            var description = '';
            var statusLabel = '';
            var buttonDes = LANG.UI_PLATFORM_INDUSTRY_REPORT_VIEW_DETAIL;
            var goApproveClass = ''
            const nameArr = content.map(obj => obj.user_name);
            var nameStr = nameArr.join('、');
            var mark = `${LANG.UI_PLATFORM_INDUSTRY_REPORT_WAITTING_APPROVE_USER_PREFIX}${nameStr}${LANG.UI_PLATFORM_INDUSTRY_APPROVE}`
            console.log(nameStr);
            switch (status) {
                case STATUS.PENDING:
                    statusLabel = `label label-sm label-info`;
                    statusClass = `pending-item`;
                    statusDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING;
                    description = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING;
                    buttonDes = LANG.UI_PLATFORM_INDUSTRY_GO_APPROVE;
                    goApproveClass = 'go-approve';
                    break;
                case STATUS.ARCHIVED:
                    statusLabel = `label label-sm label-success`;
                    statusClass = `archived-item`;
                    statusDes = LANG.UI_VISUAL_ALREADY_FINISH;
                    description = LANG.UI_PLATFORM_INDUSTRY_HAS_FINISHED_TIP;
                    mark = `${LANG.UI_PLATFORM_INDUSTRY_USER}${nameStr}${LANG.UI_PLATFORM_INDUSTRY_USER_HAS_FINISHED_TIP}`;
                    break;
                case STATUS.APPROVALING:
                    statusLabel = `label label-sm label-info`;
                    statusClass = `approvaling-item`;
                    statusDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_APPROVING;
                    description = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_IS_APPROVING;
                    
                    mark = `${LANG.UI_PLATFORM_INDUSTRY_USER}${nameStr}${LANG.UI_PLATFORM_INDUSTRY_APPROVAL_IS_APPROVING}`;
                    break;
                case STATUS.REJECTED:
                    statusLabel = `label label-sm label-danger`;
                    statusClass = `reject-item`;
                    statusDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_REJECT;
                    var rejectUser = '';
                    var reason = '';
                    $.each(content, function (k, v){
                        if (v.status == 2) {
                            //流程内部，驳回状态是2
                            rejectUser = v.user_name;
                            if (v.remark != '') {
                                reason = v.remark;
                            } else {
                                reason = '无';
                            }
                        }
                    });
                    mark = `${LANG.UI_PLATFORM_INDUSTRY_USER}${rejectUser}${LANG.UI_PLATFORM_INDUSTRY_APPROVAL_REJECT_REASON}：${reason}`;
                    break;
                case STATUS.REVOKE:
                    statusLabel = `label label-sm label-default`;
                    statusClass = `revoke-item`;
                    statusDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_REVOKED;
                    description = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_REVOKED_TIP;
                    break;
                default:
                    statusLabel = `label label-sm label-default`;
                    statusClass = `revoke-item`;
                    break;
            };
            html += `<div class="item mt8">
                        <div class="item-label"><span class='${statusLabel} ${statusClass}'>${statusDes}</span><span class="approve-time" style="float: right;color:#666666;margin-left:auto;white-space:nowrap">${data[i].create_time}</span></div>
                        <div class="item-info mt8"><div><span class="item-desc">${data[i].name}${description}</span><br><span class="item-mark">${mark}</span></div> <button type="button" class="btn adv_btn brr2 p-lr8 detail-btn ${goApproveClass}">${buttonDes}</button></div>
                    </div>`;
        }
        if(data.length == 0){
            let nodatades = '暂时没有消息';
            let status = parseInt($('.tab-items.active').attr('data-status'));
            switch (status) {
                case STATUS.PENDING: //待审批
                    nodatades = '暂时没有待审批消息';
                    break;
                case STATUS.ARCHIVED: //已完成
                    nodatades = '暂时没有已完成消息';
                    break;
                case STATUS.APPROVALING: //审批中
                    nodatades = '暂时没有审批中消息';
                    break;
                case STATUS.REJECTED: //已驳回
                    nodatades = '暂时没有已驳回消息';
                    break;
                case STATUS.REVOKE: //已撤销
                    nodatades = '暂时没有已撤回消息';
                    break;
                default:
                    nodatades = '暂时没有消息';
            }
            //如果没有消息 就需要加提示信息
            html += `<div class="nodata">
                <img src="./img/platform/table-icon/no-data.svg" alt="">
                <span>${nodatades}</span>
            </div>`;
        }
        $('.msg-list').empty().append(html);
        $(".detail-btn").off().on('click', function () {
            LOCATION('./content/platform/industry/report_list.php', 'industry_report');
        })
    }













    return {
        init: function () {
            addListeners();
            initMsgCount();
            initTotalMsg();
        }
    }
}();

$(document).ready(function () {
    MessageCenter.init();
});