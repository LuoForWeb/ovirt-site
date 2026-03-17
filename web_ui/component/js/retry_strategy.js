/**
 * 重试策略插件：包括网络重试、操作异常重试、任务重试
 ************使用方法：策略界面增加html如下***********************************
 * <li class="active">lassName="retryLi">
 *      <a href="#retry_config" data-toggle="tab">
 *      <i className="viconfont vicon-zhongshi"></i> 重试策略 </a>
 *  </li>
 *****************************重试策略****************************************
 * <div class="tab-pane" id="retry_config"></div>
 * ****************************js初始化****************************************
 * $('#retry_config').retryStrategy();
 * ps:如果要单独屏蔽一些功能的话，传模块类型进来，如：
 * $('#retry_config').retryStrategy({"module_type': CONF.MODULE_TYPE.KUBERNETES});
 * ****************************js初始化(修改任务)****************************************
 * $('#retry_config').retryStrategy({'retry_strategy': SETTINGS.retry_strategy},'edit');
 * ****************************获取所有值(包括确认配置处显示)显示文字是黑色需要加config_color_class:colorblack参数，是绿色就不用加这个)****************************************
 *  data.retry_strategy = $('#retry_config').retryStrategy({'config_color_class': 'colorblack'} ,'value');
 *  data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
 * ****************************配置处显示html****************************************
 *  <div class="form-group mb0 mt10 retryShow"></div>
 */
(function($) {
    $.fn.retryStrategy = function(options,method) {
        // 插件的默认设置
        let network_retry_times = 10;
        let network_retry_interval = 60;
        let op_retry_times = 3;
        let op_retry_interval = 60;
        let task_retry_object = 1;
        let task_retry_times = 3;
        let task_retry_interval = 10;
        let op_retry_flag = true;
        let task_retry_flag = false;
        if(method == "edit") {
            network_retry_times = options.retry_strategy.network_retry_times; 
            network_retry_interval = options.retry_strategy.network_retry_interval; 
            op_retry_flag = options.retry_strategy.op_retry_flag;
            task_retry_flag = options.retry_strategy.task_retry_flag;
            if (options.retry_strategy.op_retry_flag) {
                op_retry_times = options.retry_strategy.op_retry_times; 
                op_retry_interval = options.retry_strategy.op_retry_interval; 
            }
            if (options.retry_strategy.task_retry_flag) {
                task_retry_object = options.retry_strategy.task_retry_object; 
                task_retry_times = options.retry_strategy.task_retry_times; 
                task_retry_interval = options.retry_strategy.task_retry_interval / 60; 
            }
        }
        const defaultOptions = {
            'network_retry_times': { value: network_retry_times, step: 5, min: 1, max: 60 }, // 网络重连次数
            'network_retry_interval': { value: network_retry_interval, step: 5, min: 5, max: 60 }, // 网络重连间隔时间（秒）
            'op_retry_flag': op_retry_flag,
            'task_retry_flag': task_retry_flag,
            'op_retry_times': { value: op_retry_times, step: 1, min: 1, max: 5 }, // 操作重连次数
            'op_retry_interval': { value: op_retry_interval, step: 5, min: 5, max: 60 }, // 操作重连间隔时间（秒）
            'task_retry_object': task_retry_object,
            'task_retry_times': {value: task_retry_times, step: 1, min: 1, max: 5 }, // 任务重试次数
            'task_retry_interval': { value: task_retry_interval, step: 5, min: 1, max: 60 }, // 任务重试间隔时间（分钟）
            'config_color_class': '',//如果要让确认配置处文字是黑色，则传此参数为'colorblack'
        };
        // 合并默认的配置项和参数中的配置项
        const settings = $.extend({}, defaultOptions, options);
        //获取开关的值
        const getSwitchDes = (check) => {
            if (check) {
                return LANG.UI_PUBLIC_ON;
            }
            return LANG.UI_PUBLIC_OFF;
        }
        //任务重试切换
        const taskRetryChange = function () {
            if (this.checked) {
                $('.task-retry-wrap').show();
            } else {
                $('.task-retry-wrap').hide();
            }
        }
        //操作重试切换
        const opRetryChange = function () {
            if (this.checked) {
                $('.operation-retry-wrap').show();
            } else {
                $('.operation-retry-wrap').hide();
            }
        }
        const initListener = function ($this) {
            $('#task_retry_flag').on('switchChange.bootstrapSwitch', taskRetryChange);
            $('#op_retry_flag').on('switchChange.bootstrapSwitch', opRetryChange);
            // 初始化spinner
            $this.find('#networkRetryTime').spinner(settings.network_retry_times);
            $this.find('#networkRetryInterval').spinner(settings.network_retry_interval);
            $this.find('#opRetryTime').spinner(settings.op_retry_times);
            $this.find('#opRetryInterval').spinner(settings.op_retry_interval);
            $this.find('#taskRetryTime').spinner(settings.task_retry_times);
            $this.find('#taskRetryInterval').spinner(settings.task_retry_interval);
            $this.find('#task_retry_object').val(settings.task_retry_object);
            $this.find('#op_retry_flag').bootstrapSwitch('state', settings.op_retry_flag); 
            $this.find('#task_retry_flag').bootstrapSwitch('state', settings.task_retry_flag); 
            // 初始化开关
            $this.find('.make-switch').bootstrapSwitch();
            // 初始化提示
            $this.find('.popovers').popover();
        }

        const toHideContent = function () {
            switch (parseInt(settings.module_type)) {
                case CONF.MODULE_TYPE.KUBERNETES: //k8s屏蔽任务重试失败的对象
                    $('#task_retry_object').find("option[value=1]").remove();
                    break;
            }
        }
        
        // 插件的方法
        var methods = {
            init: function() {
                // 初始化插件，插入HTML并设置值
                return this.each(function() {
                    var $this = $(this);
                    // 假设我们要插入的HTML结构
                    var html = `  
                                <div class="retry-strategy-content">
                                    <div class="retry-strategy-content__group retry-group-wrap network-retry-wrap">
                                        <div class="retry-group-wrap__content">
                                            <!-- 网络重连次数 -->
                                            <div class="form-group">
                                                <label
                                                    class="control-label col-md-4 networkRetryTimeLabel">` + LANG.UI_RETRY_STRATEGY_NETWORK_TIMES + `
                                                </label>
                                                <div class="col-md-7">
                                                    <div id="networkRetryTime" class="input-max-width">
                                                        <div class="input-group spinner-group">
                                                            <input  id="network_retry_times" 
                                                                class="spinner-input form-control input-sm"
                                                                maxlength="3">
                                                            <div
                                                                class="spinner-buttons input-group-btn spinner-group-btn">
                                                                <button
                                                                    type="button"
                                                                    class="btn spinner-up default input-sm">
                                                                    <i
                                                                        class="fa fa-angle-up"></i>
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    class="btn spinner-down default input-sm">
                                                                    <i
                                                                        class="fa fa-angle-down"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="help-block ">`+ LANG.UI_RETRY_STRATEGY_NETWORK_TIPS +`</span>
                                                </div>
                                            </div>
                                            <!-- 网络重连间隔时间 -->
                                            <div class="form-group ">
                                                <label
                                                    class="control-label col-md-4 retrytimelabel">`+ LANG.UI_RETRY_STRATEGY_NETWORK_INTERVAL +`
                                                </label>
                                                <div class="col-md-7">
                                                    <div id="networkRetryInterval" class="input-max-width">
                                                        <div class="input-group spinner-group">
                                                            <input  id="network_retry_interval"  class="spinner-input form-control input-sm" maxlength="3">
                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                <button type="button" class="btn spinner-up default input-sm">
                                                                    <i class="fa fa-angle-up"></i>
                                                                </button>
                                                                <button type="button" class="btn spinner-down default input-sm">
                                                                    <i class="fa fa-angle-down"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div><span class="help-block ">`+ LANG.UI_RETRY_STRATEGY_NETWORK_INTERVAL_RANGE_TIPS +`</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="retry-strategy-content__group retry-group-wrap">
                                        <div
                                            class="retry-group-wrap__content">
                                            <!-- 自动重连开关 -->
                                            <div class="form-group">
                                                <label
                                                    class="control-label form-group-label col-md-4">`+ LANG.UI_RETRY_STRATEGY_OP_AUTO_RETRY +`</label>
                                                <div class="col-md-7">
                                                    <div class="mt5">
                                                        <input type="checkbox"
                                                            checked
                                                            id="op_retry_flag"
                                                            data-size="small"
                                                            class="make-switch"
                                                            data-on-color="primary"
                                                            data-off-color="info"
                                                            data-on-text=""
                                                            data-off-text="">
                                                    </div>    
                                                    <div><span class="help-block">`+ LANG.UI_RETRY_STRATEGY_OP_TIPS +`</span></div>
                                                </div>
                                            </div>
                                            <div
                                                class="operation-retry-wrap">
                                                <!-- 操作重试次数 -->
                                                <div class="form-group">
                                                    <label
                                                        class="control-label col-md-4 retrytimelabel">`+ LANG.UI_OBS_OPERATE_ABNORMAL_RETRY_TIMES +`
                                                    </label>
                                                    <div class="col-md-7">
                                                        <div id="opRetryTime" class="input-max-width">
                                                            <div
                                                                class="input-group spinner-group">
                                                                <input
                                                                    
                                                                    id="op_retry_times"
                                                                    
                                                                    class="spinner-input form-control input-sm"
                                                                    maxlength="3">
                                                                <div
                                                                    class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button
                                                                        type="button"
                                                                        class="btn spinner-up default input-sm">
                                                                        <i
                                                                            class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        class="btn spinner-down default input-sm">
                                                                        <i
                                                                            class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div><span class="help-block">`+ LANG.UI_RETRY_STRATEGY_OP_TIMES_TIPS +`</span></div>
                                                    </div>
                                                </div>
                                                <!-- 操作重试间隔时间 -->
                                                <div class="form-group">
                                                    <label
                                                        class="control-label col-md-4 retrytimelabel">`+ LANG.UI_RETRY_STRATEGY__OP_INTERVAL_RANGE +`
                                                    </label>
                                                    <div class="col-md-7">
                                                        <div id="opRetryInterval" class="input-max-width">
                                                            <div
                                                                class="input-group spinner-group">
                                                                <input
                                                                    
                                                                    id="op_retry_interval"
                                                                    
                                                                    class="spinner-input form-control input-sm"
                                                                    maxlength="3">
                                                                <div
                                                                    class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button
                                                                        type="button"
                                                                        class="btn spinner-up default input-sm">
                                                                        <i
                                                                            class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        class="btn spinner-down default input-sm">
                                                                        <i
                                                                            class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div><span class="help-block">`+ LANG.UI_RETRY_STRATEGY_OP_INTERVAL_TIPS +`</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="retry-strategy-content__group retry-group-wrap">
                                        <div
                                            class="retry-group-wrap__content">
                                            <!-- 自动重试开关 -->
                                            <div class="form-group">
                                                <label
                                                    class="control-label form-group-label col-md-4">`+ LANG.UI_RETRY_STRATEGY_TASK +`</label>
                                                <div class="col-md-7">
                                                    <div class="mt5">
                                                        <input type="checkbox"
                                                            id="task_retry_flag"
                                                            data-size="small"
                                                            class="make-switch"
                                                            data-on-color="primary"
                                                            data-off-color="info"
                                                            data-on-text=""
                                                            data-off-text="">
                                                    </div>
                                                    <div><span class="help-block">`+ LANG.UI_RETRY_STRATEGY_TASK_TIPS +`</span></div>
                                                </div>
                                            </div>
                                            <div class="task-retry-wrap display-none">
                                                <!-- 重试对象 -->
                                                <div class="form-group task_retry_object-form">
                                                    <label
                                                        class="control-label col-md-4 form-group-label">`+ LANG.UI_RETRY_STRATEGY_TASK_OBJECT +`
                                                    </label>
                                                    <div class="col-md-5">
                                                        <select
                                                            class="form-control select2me inline-block max-width-245px"
                                                            id="task_retry_object">
                                                            <option
                                                                value="1">
                                                                `+ LANG.UI_RETRY_STRATEGY_OBJECT_OPTION1 +`
                                                            </option>
                                                            <option
                                                                value="2">
                                                                `+ LANG.UI_RETRY_STRATEGY_OBJECT_OPTION2 +`
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- 任务重试次数 -->
                                                <div class="form-group">
                                                    <label
                                                        class="control-label col-md-4 retrytimelabel">`+ LANG.UI_RETRY_STRATEGY_TASK_TIMES +`
                                                    </label>
                                                    <div class="col-md-7">
                                                        <div id="taskRetryTime" class="input-max-width">
                                                            <div
                                                                class="input-group spinner-group">
                                                                <input
                                                                    
                                                                    id="task_retry_times"
                                                                    
                                                                    class="spinner-input form-control input-sm"
                                                                    maxlength="3">
                                                                <div
                                                                    class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button
                                                                        type="button"
                                                                        class="btn spinner-up default input-sm">
                                                                        <i
                                                                            class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        class="btn spinner-down default input-sm">
                                                                        <i
                                                                            class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div><span class="help-block">`+ LANG.UI_RETRY_STRATEGY_TASK_TIMES_TIPS +`</span></div>
                                                    </div>
                                                </div>
                                                <!-- 任务重试间隔时间 -->
                                                <div class="form-group">
                                                    <label
                                                        class="control-label col-md-4 retrytimelabel">`+ LANG.UI_RETRY_STRATEGY_TASK_INTERVAL1 +`
                                                    </label>
                                                    <div class="col-md-7">
                                                        <div id="taskRetryInterval" class="input-max-width">
                                                            <div
                                                                class="input-group spinner-group">
                                                                <input
                                                                    
                                                                    id="task_retry_interval"
                                                                    
                                                                    class="spinner-input form-control input-sm"
                                                                    maxlength="3">
                                                                <div
                                                                    class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button
                                                                        type="button"
                                                                        class="btn spinner-up default input-sm">
                                                                        <i
                                                                            class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        class="btn spinner-down default input-sm">
                                                                        <i
                                                                            class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div><span class="help-block">`+ LANG.UI_RETRY_STRATEGY_TASK_INTERVAL_TIPS +`</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                    $this.html(html);
                    initListener($this);
                    //根据模块类型屏蔽一些显示
                    toHideContent();
                });
            },
            value: function() {//获取值并显示在确认配置界面
                let retry_info = {
                    'network_retry_times': $('#network_retry_times').val().trim(), // 网络重连次数
                    'network_retry_interval': parseInt($('#network_retry_interval').val().trim()), // 网络重连间隔时间
                    'op_retry_flag': $('#op_retry_flag').get(0).checked,
                    'op_retry_times': $('#op_retry_times').val().trim(), // 操作重连次数
                    'op_retry_interval': parseInt($('#op_retry_interval').val().trim()), // 操作重连间隔时间
                    'task_retry_flag': $('#task_retry_flag').get(0).checked,
                    'task_retry_object': $('#task_retry_object').val(),
                    'task_retry_times': $('#task_retry_times').val().trim(), // 任务重试次数
                    'task_retry_interval': parseInt($('#task_retry_interval').val().trim()) * 60 // 任务重试间隔时间
                }
                //最后一步展示
                if (retry_info.network_retry_times == '' || $('#network_retry_interval').val().trim() == '') {
                    UIToastr.showWarning(LANG.UI_STRATEGY_RETRY,LANG.UI_RETRY_STRATEGY_NETWORK_NOT_NULL_TIPS);
                    return false;
                }
                // 网络重连次数1-60
		        if(retry_info.network_retry_times < 1 || retry_info.network_retry_times > 60){
		        	UIToastr.showWarning(LANG.UI_STRATEGY_RETRY,LANG.UI_RETRY_STRATEGY_NETWORK_TIMES_RANGE);
		        	return false;
		        }
                // 网络重连间隔时间
		        if($('#network_retry_interval').val().trim() < 5 || $('#network_retry_interval').val().trim() > 60){
		        	UIToastr.showWarning(LANG.UI_STRATEGY_RETRY,LANG.UI_RETRY_STRATEGY_NETWORK_INTERVAL_RANGE);
		        	return false;
		        }
                let html = `
                    <label class="control-label col-md-3">` + LANG.UI_STRATEGY_RETRY + `:</label>
                    <div class="col-md-9">
                        <div class="form-control-static `+ settings.config_color_class +`">
                            <div class="network_retry_times_show">`+ LANG.UI_RETRY_STRATEGY_NETWORK_TIMES +`：`+ retry_info.network_retry_times +`</div>
                            <div class="network_retry_interval_show">`+ LANG.UI_RETRY_STRATEGY_NETWORK_INTERVAL +`：`+ parseInt(retry_info.network_retry_interval) +`</div>
                            <div class="op_retry_flag_show">`+ LANG.UI_RETRY_STRATEGY_OP +`：`+ getSwitchDes(retry_info.op_retry_flag) +`</div>`;
                if (retry_info.op_retry_flag) {
                    if (retry_info.op_retry_times == '' || $('#op_retry_interval').val().trim() == '') {
                        UIToastr.showWarning(LANG.UI_STRATEGY_RETRY,LANG.UI_RETRY_STRATEGY_OP_NOT_NULL);
                        return false;
                    }
                    // 操作重连次数1-5
		            if(retry_info.op_retry_times < 1 || retry_info.op_retry_times > 5){
		            	UIToastr.showWarning(LANG.UI_STRATEGY_RETRY,LANG.UI_RETRY_STRATEGY_OP_TIMES_RANGE_TIPS);
		            	return false;
		            }
                    // 操作重连间隔时间
		            if($('#op_retry_interval').val().trim() < 5 || $('#op_retry_interval').val().trim() > 60){
		            	UIToastr.showWarning(LANG.UI_STRATEGY_RETRY,LANG.UI_RETRY_STRATEGY_OP_INTERVAL_RANGE_TIPS);
		            	return false;
		            }
                    html += `<div class="op_retry_times_show"> `+ LANG.UI_RETRY_STRATEGY_OP_TIMES +`：`+ retry_info.op_retry_times +`</div>
                             <div class="op_retry_interval_show">`+ LANG.UI_RETRY_STRATEGY__OP_INTERVAL_RANGE +`：`+ parseInt(retry_info.op_retry_interval) +`</div>`;
                } else {
                    retry_info.op_retry_times = 0;
                    retry_info.op_retry_interval = 0;
                }
                html += `<div class="task_retry_flag_show">`+ LANG.UI_RETRY_STRATEGY_TASK +`：`+ getSwitchDes(retry_info.task_retry_flag) +`</div>`;
                if (retry_info.task_retry_flag) {
                    if (retry_info.task_retry_times == '' || $('#task_retry_interval').val().trim() == '') {
                        UIToastr.showWarning(LANG.UI_STRATEGY_RETRY,LANG.UI_RETRY_STRATEGY_TASK_NOT_NULL);
                        return false;
                    }
                    // 任务重试次数1-5
		            if(retry_info.task_retry_times < 1 || retry_info.task_retry_times > 5){
		            	UIToastr.showWarning(LANG.UI_STRATEGY_RETRY,LANG.UI_RETRY_STRATEGY_TASK_TIMES_TIPS1);
		            	return false;
		            }
                    // 任务重试间隔时间
		            if((retry_info.task_retry_interval / 60) < 1 || (retry_info.task_retry_interval / 60) > 60){
		            	UIToastr.showWarning(LANG.UI_STRATEGY_RETRY,LANG.UI_RETRY_STRATEGY_TASK_INTERVAL_RANGE_TIPS);
		            	return false;
		            }
                    html += `<div class="task_retry_object_show">`+ LANG.UI_RETRY_STRATEGY_TASK_OBJECT +`：`+ $('#task_retry_object').find('option:selected').text() +`</div>
                            <div class="task_retry_times_show">`+ LANG.UI_RETRY_STRATEGY_TASK_TIMES +`：`+ retry_info.task_retry_times +`</div>
                            <div class="task_retry_interval_show">`+ LANG.UI_RETRY_STRATEGY_TASK_INTERVAL1 +`：`+ parseInt(retry_info.task_retry_interval) / 60 +`</div>`;
                } else {
                    retry_info.task_retry_object = '';
                    retry_info.task_retry_times = 0;
                    retry_info.task_retry_interval = 0;
                }
                html += `</div>
                    </div>`;
                $('.retryShow').html(html);
                //根据模块类型屏蔽一些显示
                toHideContent();
                return retry_info;
            },
            // edit: function() {
                
            // }
        };
        // 如果调用了方法，则执行该方法
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof options === 'object' || !options) {
            // 如果没有指定方法,则调用init方法
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + options + ' does not exist on jQuery.retryStrategy');
        }
    };
})(jQuery);
