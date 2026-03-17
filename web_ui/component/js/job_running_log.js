/**
 * 任务运行日志插件
 * 调用方式：$('#runninglog').runningLog({
 *   job_uuid: '任务UUID',       // 必选：任务UUID
 *   limit: 50                   // 可选：每批加载数据量（默认50）
 * });
 */
(function($) {
    $.fn.runningLog = function(options) {
        // 默认配置
        const defaultOptions = {
            job_uuid: '',
            limit: 50 // 每批加载的数据量
        };
        // 合并配置
        const opts = $.extend({}, defaultOptions, options);

        // 校验必选参数
        if (!opts.job_uuid) {
            console.error('job_uuid is null');
            return this;
        }

        // 插件作用于日志容器（#runninglog）
        const $logContainer = this.first();
        if (!$logContainer.length) {
            console.error('div is null');
            return this;
        }
        // 全局变量
        let updateInterval = 5000;
        let batchSize = opts.limit; // 每批加载量
        let offset = 0;             // 当前偏移量
        let isLoading = false;      // 防止重复加载
        let autoRefresh = true;     // 是否自动刷新
        let timerTask = window.timerTask || {}; // 兼容原有 timerTask 全局对象
        // 新增：记录是否处于时间范围筛选状态
        let isDateRangeFilter = false;
        // 确保 timerTask 存在
        if (!window.timerTask) window.timerTask = timerTask;
        // 生成日志列表 HTML
        function getLiInfo(d, isAppend) {
            let info = "";
            for (let i = 0; i < d.length; i++) {
                info += '<li class="list-group-item__log"><div class="col1"><div class="cont contdetail"><div class="cont-col1">' +
                    getIcon(d[i].level_value) + '</div><div class="cont-col2"><div class="desc">' + d[i].description + '</div>' +
                    '</div></div></div><div class="col2 logtimecol"><div class="date">' + d[i].op_time + '</div></div></li>';
            }
            if (isAppend) {
                $logContainer.append(info);
            } else {
                $logContainer.html(info);
            }
            if ((d.length === 0 && offset === 0) || (d.length === 0 && isDateRangeFilter)) {
                $logContainer.html('<li><div class="col textalignc">' + LANG.UI_TOOLS_NO_DATA + '</div></li>');
                return;
            }
        }

        // 生成日志级别图标
        function getIcon(level) {
            let icon;
            switch (level) {
                case 1:
                    icon = '<div class="label label-success" style="background-color: transparent;"><i class="viconfont vicon-wancheng1"></i></div>';
                    break;
                case 3:
                    icon = '<div class="label label-danger" style="background-color: transparent;"><i class="viconfont vicon-cuowu"></i></div>';
                    break;
                default:
                    icon = '<div class="label label-warning" style="background-color: transparent;"><i class="viconfont vicon-yichang"></i></div>';
                    break;
            }
            return icon;
        }
        // 移除第一批数据
        function removeFirstBatch() {
            const $items = $logContainer.find('li.list-group-item__log');
            if ($items.length > batchSize) {
                $items.slice(0, batchSize).remove();
                offset -= batchSize;
            }
        }
        // 获取日志数据
        function getLog(isAppend, start_time = "", end_time = "") {
            if (isLoading) return;
            isLoading = true;
            const data = {
                jobs_uuid: opts.job_uuid, // 使用插件传入的 job_uuid
                offset: offset,
                limit: batchSize,          // 使用插件配置的 limit
                start_time: start_time,
                end_time: end_time
            };
            pAjaxRequest(data, "/api/v1/logs/jobs/running/logs", "GET", function(d) {
                // 容器不存在时清除定时器
                if (!$logContainer.length) {
                    clearTimeout(timerTask.JobDetails_logGrid);
                    return;
                }
                if (!isAppend) {
                    offset = batchSize; // 初始加载后设置偏移量
                } else {
                    offset += batchSize; // 追加加载后增加偏移量
                }
                isLoading = false;
                getLiInfo(d.data, isAppend);
            }, true);
        }

        // 自动刷新
        function startAutoRefresh() {
            clearTimeout(timerTask.JobDetails_logGrid);
            // 处于时间范围筛选状态时，不启动下一轮刷新
            if (isDateRangeFilter || !autoRefresh) return;
            timerTask.JobDetails_logGrid = setTimeout(function() {
                const $parentContainer = $logContainer.parent();
                // 仅在顶部时刷新
                if ($parentContainer.scrollTop() <= 0) {
                    offset = 0; // 重置偏移量
                    getLog(false);
                }
                startAutoRefresh(); // 循环启动定时器
            }, updateInterval);
        }
        // 绑定滚动事件
        function bindScrollEvent() {
            const $parentContainer = $logContainer.parent();
            $parentContainer.on('scroll', function() {
                if (isDateRangeFilter) return;
                const scrollTop = $parentContainer.scrollTop();
                const containerHeight = $parentContainer.height();
                const scrollHeight = $parentContainer[0].scrollHeight;
                // 判断是否在顶部（控制自动刷新）
                autoRefresh = scrollTop <= 0;
                // 滚动到底部附近，加载更多
                if (scrollTop + containerHeight >= scrollHeight - 50 && !isLoading) {
                    getLog(true);
                }
                // 滚动到顶部附近，移除旧数据（上翻时）
                if (scrollTop <= 0 && offset > batchSize) {
                    removeFirstBatch();
                    // 滚动到顶部且偏移量不为0，重新加载第一批
                    if (scrollTop <= 10 && offset > batchSize) {
                        offset = 0;
                        getLog(false);
                    }
                }
            });
        }
        function initRunningLogDaterangePicker () {
            let daterangepicker_start_time = "";
            let daterangepicker_end_time = "";
            //初始化日期时间选择控件
		    $('#running_log_daterangepicker_wrapper').daterangepicker({
		    	"autoUpdateInput": false,		 //是否自动填充input
		    	"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
		    	"endDate": moment({hour: 23, minute: 59}),	//默认结束时间
		    	"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
		    	"timePicker": true,													//是否显示时间,时分
		    	"timePicker24Hour": true,											//是否是24小时制
		    	"alwaysShowCalendars": true,										//是否总是显示日期选择
		    	"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		    	"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		    }, function(start, end, label) {
		    });
            //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		    $('#running_log_daterangepicker_wrapper').on('apply.daterangepicker', function(ev, picker) {
		    	//给全局变量赋值,然后设置input
		    	daterangepicker_start_time = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
		    	daterangepicker_end_time = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
		    	$(".running-log-search").html(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
		    	//标记为时间范围筛选状态，清除当前定时器（停止刷新）
		    	isDateRangeFilter = true;
		    	clearTimeout(timerTask.JobDetails_logGrid);
		    	// 加载筛选后的数据（重置偏移量）
		    	offset = 0;
		    	getLog(false,daterangepicker_start_time, daterangepicker_end_time);
		    });

		    $('#running_log_daterangepicker_wrapper').on('cancel.daterangepicker', function(ev, picker) {
		    	//清除全局变量,然后设置input
		    	daterangepicker_start_time = "";
		    	daterangepicker_end_time = "";
		    	$(".running-log-search").html('时间范围');
		    	//取消筛选时，恢复筛选状态，重启自动刷新
		    	isDateRangeFilter = false;
		    	offset = 0;
		    	getLog(false);
		    	startAutoRefresh(); // 恢复自动刷新
		    });

		    //设置时间
		    if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
		    	//英文独有的
		        $(".form_datetime").datetimepicker({
		    	    autoclose: true,
		    	    isRTL: Metronic.isRTL(),
		    	    format: "yyyy-mm-dd hh:ii:ss",
		    	    pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		    	    startDate: new Date()
		        });
	        }else{
		        $(".form_datetime").datetimepicker({
		    	    language:  'zh-CN', 
		    	    autoclose: true,
		    	    isRTL: Metronic.isRTL(),
		    	    format: "yyyy-MM-dd hh:ii:ss",
		    	    pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		    	    startDate: new Date()
		        });
	        }
        }
        const changeTab = function () {
            $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
                if ("#log" == e.target.hash) {
                    autoRefresh = true;
                    startAutoRefresh();
                } else {
                    autoRefresh = false;
                }
            })
        }
        // 插件初始化主流程
        function init() {
            // 初始化加载第一批数据
            getLog(false);
            // 绑定滚动事件
            bindScrollEvent();
            // 启动自动刷新
            startAutoRefresh();
            // 暴露销毁方法（可选）
            $logContainer.data('runningLogDestroy', function() {
                clearTimeout(timerTask.JobDetails_logGrid);
                const $parentContainer = $logContainer.parent(); // 补充定义，避免报错
                $parentContainer.off('scroll'); // 解绑滚动事件
                $logContainer.empty();
                offset = 0;
                isLoading = false;
                autoRefresh = true;
                isDateRangeFilter = false; // 销毁时重置筛选状态
            });
            //初始化时间范围选择器
            initRunningLogDaterangePicker();
            //切换tab切换刷新  tab不是log停止刷新
            changeTab();
        }
        // 执行初始化
        init();
        // 支持链式调用
        return this;
    };
})(jQuery);