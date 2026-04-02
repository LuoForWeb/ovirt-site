var planSearch = function () {
    return function (){
        var search_class = 'plan-data-search'; // 标记是哪个地方来的搜索
        var level = 0;
        var searchParams;
        let reportDom = planList();

        const addListeners = function () {
            // 获取所有审批流列表
            initVerifyApproval();

            //弹出高级搜索模态框
            $('#searchAll'+level).on('click', function(){
                $('#searchmodal'+level).modal({'width':'800px', 'height':'260px'});
            });
            //高级搜索发送请求到服务端
            $('#serach_submit'+level).on('click', function(){
                $('.'+search_class).val('');
                $('#searchDiv'+ level+' .searchContent' +level).text('');
                var p = {};
                p.plan_title = $('#plan_title'+level).val();
                p.plan_num = $('#plan_num'+level).val();
                p.plan_version = $('#plan_version'+level).val();
                p.plan_approval = $('#plan_approval'+level).val();
                searchParams = p;
                //添加搜索条件显示
                addSearchContent(p);
                $('#searchmodal' +level).modal('hide');

                reportDom.refresh({'queryParams':searchParams,level:level});
            });
        }

        let initVerifyApproval = function(){
            let params = {};
            params.offset = 0;
            params.limit = 100;
            pAjaxRequest(params, "/api/v1/approvals/list", "GET", function (result) {
                let list = result.data.rows;
                let approval = $('#plan_approval' + level);
                approval.empty();
                let option = $("<option>").text(LANG.UI_JOB_SELECT).val('');
                approval.append(option)
                for(let i=0; i<list.length; i++){
                    let option = $("<option>").text(list[i].name).val(list[i].approval_uuid);
                    approval.append(option);
                }
                approval.selectpicker('refresh');
            });
        }

        //显示搜索内容
        var addSearchContent = function(p){
            var info = "";
            $('.searchContent'+level).text('');

            if(p.plan_title != ''){
                info += '<span id="plan_title_val'+level +'" title="' + $('#plan_title' + level).val() + '"> '+ LANG.UI_STORAGE_NAME +':' +
                    ' <i>' + $('#plan_title' + level).val() + '</i><em>X</em></span>';
            }
            if(p.plan_num != ''){
                info += '<span id="plan_num_val'+level +'" title="' + $('#plan_num' + level).val() + '"> '+ LANG.UI_REPORT_NUMBER +':' +
                    ' <i>' + $('#plan_num' + level).val() + '</i><em>X</em></span>';
            }
            if(p.plan_version != ''){
                info += '<span id="plan_version_val'+level +'" title="' + $('#plan_version' + level).val() + '"> '+ LANG.UI_NODE_VERSION +':' +
                    ' <i>' + $('#plan_version' + level).val() + '</i><em>X</em></span>';
            }

            if(p.plan_approval != ''){
                info += '<span id="plan_approval_val'+level +'" title="' + $('#plan_approval'+level).find("option:selected").text() + '"> '+ LANG.UI_PLATFORM_INDUSTRY_APPROVAL +':' +
                    ' <i>' + $('#plan_approval'+level).find("option:selected").text() + '</i><em>X</em></span>';
            }

            $('.searchContent'+level).append(info);
            $('#searchDiv'+level).show();

            $('#searchDiv'+level+' .searchContent'+level+' em').on('click', function(){
                var searchContent = $('.searchContent'+level);
                if(searchContent[0].children.length == 0){
                    $('#searchDiv'+level).hide();
                }
                var parent  = $(this).parent();
                var id = parent[0].id;

                if (id == 'plan_title_val'+level) {
                    searchParams['plan_title'] = '';
                    $('#plan_title'+level).val('');
                } else if (id == 'plan_num_val'+level) {
                    searchParams['plan_num'] = '';
                    $('#plan_num'+level).val('');
                } else if (id == 'plan_version_val'+level) {
                    searchParams['plan_version'] = '';
                    $('#plan_version'+level).val('');
                } else if (id == 'plan_approval_val'+level) {
                    searchParams['plan_approval'] = '';
                    $('#plan_approval'+level).val('');
                    $('#plan_approval'+level).selectpicker('refresh');
                }
                $(this).parent().remove();
                reportDom.refresh({'queryParams':searchParams,level:level});
            });
            $('#searchDiv'+level+' .clearSearch'+level).on('click',function(){
                $('#searchDiv'+level+' .searchContent'+level).text('');
                $('#searchDiv'+level).hide();
                let toolbarId = '#vin_plan_function_toolbar';
                let searchClass = 'plan-function-search';
                if (level == 0) {
                    // 数据验证
                    toolbarId = '#vin_plan_data_toolbar';
                    searchClass = 'plan-data-search';
                }
                $(toolbarId + ' .'+searchClass).val();
                searchParams = {};
                $('#plan_title'+level).val('');
                $('#plan_num'+level).val('');
                $('#plan_version'+level).val('');
                $('#plan_approval'+level).val('');
                $('#plan_approval'+level).selectpicker('refresh');
                reportDom.refresh({'queryParams':searchParams,level:level});
            });

            //如果没搜索条件，先隐藏div
            if(!info){
                $('#searchDiv'+level).hide();
            }
        }

        return {
            init: function (options = {}) {
                if (options.type == undefined) {
                    search_class = 'plan-data-search';
                } else {
                    search_class = options.type;
                }
                if (options.level != undefined) {
                    level = options.level;
                }
                reportDom = planList();
                addListeners();
            },
        };
    }
}();
