var reportSearch = function () {
    return function (){
        var search_class = 'report-pending-search'; // 标记是哪个地方来的搜索
        var level = 0;
        var searchParams;
        let reportDom = ReportList;

        const addListeners = function () {
            // 获取所有模板列表
            initVerifyTemplate();

            //弹出高级搜索模态框
            $('#searchAll'+level).on('click', function(){
                $('#searchmodal'+level).modal({'width':'800px', 'height':'260px'});
            });
            //高级搜索发送请求到服务端
            $('#serach_submit'+level).on('click', function(){
                $('.'+search_class).val('');
                $('#searchDiv'+ level+' .searchContent' +level).text('');
                var p = {};
                p.report_title = $('#report_title'+level).val();
                p.report_task = $('#report_task'+level).val();
                p.report_agent = $('#report_agent'+level).val();
                p.report_template = $('#report_template'+level).val();
                searchParams = p;
                //添加搜索条件显示
                addSearchContent(p);
                $('#searchmodal' +level).modal('hide');

                reportDom.refresh({'queryParams':searchParams});
            });
        }

        let initVerifyTemplate = function(){
            let params = {};
            params.offset = 0;
            params.limit = 100;
            pAjaxRequest(params, "/api/v1/industry/templates", "GET", function (result) {
                let list = result.data.rows;
                let template = $('#report_template' + level);
                template.empty();
                let option = $("<option>").text(LANG.UI_JOB_SELECT).val('');
                template.append(option)
                for(let i=0; i<list.length; i++){
                    let option = $("<option>").text(list[i].name).val(list[i].temp_uuid);
                    template.append(option);
                }
                template.selectpicker('refresh');
            });
        }

        //显示搜索内容
        var addSearchContent = function(p){
            var info = "";
            $('.searchContent'+level).text('');

            if(p.report_title != ''){
                info += '<span id="report_title_val'+level +'" title="' + $('#report_title' + level).val() + '"> '+ LANG.UI_PLATFORM_INDUSTRY_REPORT_NAME +':' +
                    ' <i>' + $('#report_title' + level).val() + '</i><em>X</em></span>';
            }
            if(p.report_task != ''){
                info += '<span id="report_task_val'+level +'" title="' + $('#report_task' + level).val() + '"> '+ LANG.UI_PLATFORM_ASSOCIA_TASK +':' +
                    ' <i>' + $('#report_task' + level).val() + '</i><em>X</em></span>';
            }
            if(p.report_agent != ''){
                info += '<span id="report_agent_val'+level +'" title="' + $('#report_agent' + level).val() + '"> '+ LANG.UI_PLATFORM_INDUSTRY_HOST_NAMES +':' +
                    ' <i>' + $('#report_agent' + level).val() + '</i><em>X</em></span>';
            }

            if(p.report_template != ''){
                info += '<span id="report_template_val'+level +'" title="' + $('#report_template'+level).find("option:selected").text() + '"> '+ LANG.UI_PLATFORM_INDUSTRY_REPORT_TEMPLATE +':' +
                    ' <i>' + $('#report_template'+level).find("option:selected").text() + '</i><em>X</em></span>';
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

                if (id == 'report_title_val'+level) {
                    searchParams['report_title'] = '';
                    $('#report_title'+level).val('');
                } else if (id == 'report_task_val'+level) {
                    searchParams['report_task'] = '';
                    $('#report_task'+level).val('');
                } else if (id == 'report_agent_val'+level) {
                    searchParams['report_agent'] = '';
                    $('#report_agent'+level).val('');
                } else if (id == 'report_template_val'+level) {
                    searchParams['report_template'] = '';
                    $('#report_template'+level).val('');
                    $('#report_template'+level).selectpicker('refresh');
                }
                $(this).parent().remove();
                reportDom.refresh({'queryParams':searchParams});
            });
            $('#searchDiv'+level+' .clearSearch'+level).on('click',function(){
                $('#searchDiv'+level+' .searchContent'+level).text('');
                $('#searchDiv'+level).hide();
                searchParams = {};
                $('#report_title'+level).val('');
                $('#report_task'+level).val('');
                $('#report_agent'+level).val('');
                $('#report_template'+level).val('');
                $('#report_template'+level).selectpicker('refresh');
                reportDom.refresh({'queryParams':searchParams});
            });

            //如果没搜索条件，先隐藏div
            if(!info){
                $('#searchDiv'+level).hide();
            }
        }

        return {
            init: function (options = {}) {
                if (options.type == undefined) {
                    search_class = 'report-pending-search';
                } else {
                    search_class = options.type;
                }
                if (options.level != undefined) {
                    level = options.level;
                }
                if (level == 0) {
                    reportDom = ReportList;
                } else if (level == 1) {
                    reportDom = ArchivedReport;
                } else {
                    reportDom = ShareReportList;
                }
                addListeners();
            },
        };
    }
}();
