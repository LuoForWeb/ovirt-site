var Settings_System_Tool = function(){
	var grid = $('#serviceTable');
	var gridInitFlag = false;
	var _SERVERNODE = "";

	var addListeners = function(){
		$('#toolSelect').selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });


		$('#servicenodeSelect').on('change', function(){
			var name = $.trim($('.service_searchinput').val());
			//节点uuid
			var nodeuuid = $('#servicenodeSelect').val();
			grid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						limit: queryParams.limit,
						offset: queryParams.offset,
						node_uuid: nodeuuid,
						search_value: name
					};
				}
			});
		});

		$('#toolSelect').on('change', toolHandler);
	}

	var toolHandler = function(){
		if(this.value == "telnet" || this.value == "telnet6"){
			$('#pingContent').hide();
			$('#telnetContent').show();
			$('.portDiv').show();
			if(_SERVERNODE !=""){
				$('#toolnodeSelect').val(_SERVERNODE);
			}
			$('#toolnodeSelect').prop('disabled', true);
			$('#toolnodeSelect').selectpicker('refresh');
		}else{
			$('#telnetContent').hide();
			$('#pingContent').show();
			$('.portDiv').hide();
			$('#toolnodeSelect').prop('disabled', false);
			$('#toolnodeSelect').selectpicker('refresh');
		}
	}

	var testHandler = function(){
		var data ={};
		data.ip = $('#testIP').val();
		data.node_uuid = $('#toolnodeSelect').val();
		data.tool_type = $('#toolSelect').val();
		data.port = $('#testport').val();
		Metronic.blockUI({target:'#networkTool', animate: true});
		pAjaxRequest(data, '/api/v1/system/tools/connect', 'POST', function (res) {
			Metronic.unblockUI('#networkTool');
			var title = res.message
			if (res.code == 0) {
				UIToastr.showSuccess(LANG.UI_SETTINGS_TEST_NETWORK_CONNECT, title + ' ' + LANG.UI_PUBLIC_SUCCESS);
			} else {
				UIToastr.showWarning(LANG.UI_SETTINGS_TEST_NETWORK_CONNECT, title + ' ' + LANG.UI_DATACENTER_FAILURE);
			}
		})
	}


	var initSelectNode = function(){
		pAjaxRequest({offset:0,limit:100}, "/api/v1/nodes/", "GET", function (result) {
			var data = result.data.rows;

			var servicenodeSelect = $('#servicenodeSelect');
			var toolnodeSelect = $('#toolnodeSelect');
			if(0 == data.length){
				servicenodeSelect.prop('disabled', true);
				toolnodeSelect.prop('disabled', true);
				return;
			}
			servicenodeSelect.empty();
			toolnodeSelect.empty();
			for(var i=0; i<data.length; i++){
				var name = data[i].host_name + '('+ data[i].ip +')';
				var option1 = $("<option>").text(name).val(data[i].node_uuid);
				var option2 = $("<option>").text(name).val(data[i].node_uuid);
				servicenodeSelect.append(option1);
				toolnodeSelect.append(option2);
			}
			_SERVERNODE = data[0].node_uuid;

			initServiceTable();
		});
	}

	var startService = function(row){
		opJob(row, 1);
	}


	var stopService = function(row){
		opJob(row, 2);
	}

	var restartService = function(row){
		opJob(row, 3);
	}


	var opJob = function(row, operate){
		var params = {};
		params.operate = operate;
		params.name = row.name.split(".")[0];
		params.node_uuid = $('#servicenodeSelect').val();

		Metronic.blockUI({target: '#serviceManage',animate: true});
		pAjaxRequest(params, '/api/v1/system/tools/service', 'POST', function (res) {
			Metronic.unblockUI('#serviceManage');
			if (operateResponseList(res, LANG.UI_SETTING_SERVICE)){
				grid.bootstrapTable('refresh');
			}
		})
	}

	var initServiceTable = function(){
		var updateInterval = 30000;

		var initGrid = function () {
			var node_uuid = $('#servicenodeSelect').val();
			var name = $.trim($('.service_searchinput').val());
			if (!gridInitFlag) {
				let options = {
					toolbarId: '#vin_service_toolbar',
					buttonsToolbar: '#vin_service_toolbar .vin_btnToolbar',
					vin_url: '/api/v1/system/services',
					vin_method: 'GET',
					queryParamsType: 'limit',
					queryParams: function (p) {
						return {
							limit: p.limit,
							offset: p.offset,
							node_uuid: node_uuid,
							search_value: $('.serviceSearch').val()
						}
					},
					searchInput: true, //搜索框
					searchClass: 'serviceSearch', //自定义的搜索框类名
					searchSelector: '.serviceSearch', //选择使用自定义搜索框
					searchOnEnterKey: true,
					placeholder: LANG.UI_SETTINGS_SERVICE_SEARCH_BY_NAME, //搜索框的placeholder
					pagination: true,
					sidePagination: 'server',
					pageNumber: 1,
					pageSize: 10,
					pageList: [10, 20, 50, 100],
					paginationLoop: false,
					lineHeight: '60px',
					sortable: false,
					resizable: true,
					onRefresh: function (params) {
						grid.bootstrapTable('hideLoading');
					},
					PostBody: function () {
						$('#vin_service_toolbar .search-btn').off().on('click', function () {
							grid.bootstrapTable('refresh');
						})
						$('#serviceTable th[data-field="name"]').css('width','50%');
						$('#serviceTable th[data-field="status_des"]').css('width','15%');
						$('#serviceTable th[data-field="operations"]').css('width','20%');
					},
					columns: [
						{
							field: 'no',
							title: LANG.UI_PUBLIC_TABLE_ID,
							width: '100px'
						},
						{
							field: 'name',
							title: LANG.UI_SETTINGS_SERVICE_NAME,
						},
						{
							field: 'status_des',
							title: LANG.UI_PUBLIC_STATUS,
							sortable: true,
							formatter: function (value, row) {
								if (0 === row.status) {
									return '<span class="label label-sm label-success">' + value + '</span>';
								} else {
									return '<span class="label label-sm label-default">' + value + '</span>';
								}
							}
						},
						{
							field: 'operations',
							title: LANG.UI_PUBLIC_OPERATION,
							clickToSelect: false,
							events: {
								'click .start': function (event, value, row, index) {
									startService(row);
								},
								'click .stop': function (event, value, row, index) {
									stopService(row);
								},
								'click .restart': function (event, value, row, index) {
									restartService(row);
								},
							},
							formatter: function (value, data, index) {
								if ($.inArray('p_setting_manager_operate', CONF.PERMISSION_ARR) === -1) {
									return '--';
								}
								let button = '<div class="btn-group positionabs" style="margin-top: -13px;">';
								if (index > 4) {
									button = '<div class="btn-group positionabs dropup" style="margin-top: -13px;">';
								}
								button += '<button type="button" class="btn btn-primary btn-sm   dropdown-toggle" data-toggle="dropdown" ' +
									'data-hover="dropdown" data-delay="1000" data-close-others="true" style="width: auto;">' +
									'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
									'</button>' +
									'<ul class="dropdown-menu min-width100" role="menu" id="'+ data.name +'">';
								$.each(value, function (i, d) {
									switch(d){
										case 1:
											button += '<li class="start"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_SETTINGS_SERVICE_START + '</a></li>';
											break;
										case 2:
											button += '<li class="stop"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
											break;
										case 3:
											button += '<li class="restart"><a href="javascript:;"><i class="viconfont vicon-ge_refresh"></i> ' + LANG.UI_SETTINGS_SERVICE_RESTART + '</a></li>';
											break;
									}
								});
								button += '</ul></div>';
								return button;
							},
						}
					]
				};
				grid.baseTableConfig().init(options);
				gridInitFlag = true;
			} else {
				grid.bootstrapTable('refreshOptions', {
					queryParams: function (queryParams) {
						return {
							limit: queryParams.limit,
							offset: queryParams.offset,
							node_uuid: node_uuid,
							search_value: $('.serviceSearch').val()
						};
					}
				});
			}
			timerTask.System_Service = setTimeout(initGrid, updateInterval);
		}
		initGrid();
	}

	var handleValidation = function() {
        var toolform = $('#toolform');

        toolform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	toolip: {
            		required: true,
            		ipv4: true
            	},
            	toolport: {
            		requirds: true,
                    numberPort: true,
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-test').removeClass("has-success").addClass('has-error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change done by hightlight

            },

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-test').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {

            }

        });

        //IP验证格式
        $.validator.addMethod("ipv4", function(value, element) {
        	var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
    		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
    		//http|https
    		var domainHTTP = this.optional( element ) || /^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
    		var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
    		return domain || ipv4 || domainHTTP || ipv4HTTP;
        }, LANG.UI_SETTING_INPUT_IP);

		//端口验证格式
		$.validator.addMethod("numberPort", function(value, element) {
			return this.optional(element) ||  /^([0-9]|[1-9]\d|[1-9]\d{2}|[1-9]\d{3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/i.test(value);
		}, LANG.UI_SETTING_INPUT_PORT);

		//端口必须验证
		$.validator.addMethod("requirds", function(value, element) {
			if (
				($('#toolSelect').val() == 'telnet' || $('#toolSelect').val() == 'telnet6')
				&& $('#testport').val() == '') {
				return false;
			}
			return true;
		}, LANG.UI_PUBLIC_EMPTY_TIPS);



        $("#testTool").click(function(){
        	if (toolform.validate().form()) {
        		testHandler();
            }
        });


	};

	return {
		init: function () {
			initSelectNode();
			addListeners();
			handleValidation();
		}
	}

}();
var Settings_System_RemoteControl = function(){
	var addListeners = function(){
		$('#WebSSH').on('click',function(){
			var domain = document.domain;
			var domin_ssh ="https://"+domain+"/ssh/host/127.0.0.1";
			window.open(domin_ssh);
		});
	};

	//初始化web uploader
	var initWebUploader = function(){
    	//开始上传按钮
        var $btn = $('#ctlBtn');
        //文件信息显示区域
//        var $list = $('#thelist');
        //当前状态
        var state = 'pending';
        //文件名
        var $input_filename = $('#input_filename');
        //上传进度提示信息
        var $hint_content = $('.hint_content');
        //进度百分比文字
        var $state_upload = $('#state_upload');
        //进度条
        var $uploadProgress = $("#uploadProgress");
        //从队列中删除
        var $del_name =$("#bnt_del_name");
        //限制单个大小
        var fileSingleSizeLimit_val = 1*1024*1024*1024;
        //限制单个文件类型
        var extensions_val = 'tar.gz,tar,zip,rar';
        //初始化Web Uploader
        var uploader = WebUploader.create({
            // swf文件路径
            swf: './assets/global/plugins/web-uploader/Uploader.swf',
            // 文件接收服务端。
            server: '/api/v1/system/tools/uploads?x-api-version=1.0-rev0',
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '#picker',
            fileNumLimit: 1,
            fileSingleSizeLimit: fileSingleSizeLimit_val,//限制大小1G，单文件
            chunked: true, //分片上传大文件
			chunkSize: 5 * 1024 * 1024,//分片上传分片大小5M
			chunkRetry: 3,//如果某个分片由于网络问题出错，允许自动重传多少次
            resize: false,//不压缩
            accept: {
 			   title: 'upgrade file',
 			   extensions: extensions_val,
 			   mimeTypes: ''
 			},
        });

        //当文件被添加之前调用(选择文件前调用)
        uploader.on('beforeFileQueued',function(file){
        	var name_suf = file.ext;
        	var file_size = file.size;
        	if(extensions_val.search(name_suf)==-1 || name_suf == ""){
        		UIToastr.showWarning(LANG.UI_SETTINGS_UPLOAD_TITLE,LANG.UI_SETTINGS_UPLOAD_ERROR_NAME);
        	}
        	if(file_size>=fileSingleSizeLimit_val){
        		UIToastr.showWarning(LANG.UI_SETTINGS_UPLOAD_TITLE,LANG.UI_SETTINGS_UPLOAD_ERROR_SIZE);
        	}


        });

        // 当有文件被添加进队列的时候（选择文件后调用）
        uploader.on( 'fileQueued', function( file ) {
        	$del_name.css("display","block");
        	$input_filename.val(file.name);
        	$state_upload.text(LANG.UI_SETTINGS_UPLOAD_WAITING);
        });

        // 文件上传过程中创建进度条实时显示。
        uploader.on( 'uploadProgress', function( file, percentage ) {
        	$state_upload.text(LANG.UI_SETTINGS_UPLOAD_UPLOADING+ '（' + parseInt(percentage * 100) + '%）');
        	$uploadProgress.css("width",parseInt(percentage * 100)+'%');

        });

        // 文件上传成功后会调用
        uploader.on( 'uploadSuccess', function( file ) {
        	$state_upload.text(LANG.UI_SETTINGS_UPLOAD_SUCCESS);
        });

        // 文件上传失败后会调用
        uploader.on( 'uploadError', function( file ) {
        	$state_upload.text(LANG.UI_SETTINGS_UPLOAD_ERROR);
        });

        // 文件上传完毕后会调用（不管成功还是失败）
        uploader.on( 'uploadComplete', function( file ) {
        });

        // all事件（所有的事件触发都会响应到）
        uploader.on( 'all', function( type ) {
            if ( type === 'startUpload' ) {
                state = 'uploading';
            } else if ( type === 'stopUpload' ) {
                state = 'paused';
            } else if ( type === 'uploadFinished' ) {
                state = 'done';
            }

            if ( state === 'uploading' ) {
                $btn.text(LANG.UI_SETTINGS_UPLOAD_STOP);
            } else {
                $btn.text(LANG.UI_SETTINGS_UPLOAD_START);
            }
        });
        //当文件被移除队列时候触发
        uploader.on('reset',function(file){
			pAjaxRequest({}, '/api/v1/system/tools/uploads', 'DELETE', function (res) {
			})
        });


        // 开始上传按钮点击事件响应
        $btn.on( 'click', function() {
        	var name_suf = $input_filename.val();
        	var ext_exit = name_suf.indexOf(".") != -1;
        	if(!ext_exit){
        		UIToastr.showWarning(LANG.UI_SETTINGS_UPLOAD_TITLE,LANG.UI_SETTINGS_UPLOAD_ERROR_NAME);
        		return;
        	}

        	if($input_filename.val()==''){
        		return;
        	}else{
        		$hint_content.css("display","block");
                if ( state === 'uploading' ) {
                    uploader.stop(true);
                } else {
                    uploader.upload();
                }
        	}

        });

        //删除单个文件从上传队列中
        $del_name.on('click',function(){
        	$del_name.css("display","none");
        	uploader.reset();
        	$input_filename.val('');
        	$hint_content.css("display","none");
        	$uploadProgress.css("width",0);
        });


    };

	return {
		init: function () {
			addListeners();
			initWebUploader();
		}
	}
}()

jQuery(document).ready(function(){
	Settings_System_Tool.init();
	Settings_System_RemoteControl.init();
})