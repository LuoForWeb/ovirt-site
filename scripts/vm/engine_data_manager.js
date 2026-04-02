var EngineDataManager = function(){
    var grid, gridInitFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;

    var initListeners = function(){
		//切换页数保存到cookie
		$('select[name=engine_datatable_length]').on('change', function(){
			pageLength.engine = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});

        $('#engine_datatable').on('click', ' tbody td .row-details', function () {
        	var data = grid.getDataTable().data();
            var nTr = $(this).parents('tr')[0];
            if($(this).hasClass('row-details-open')){
            	//如果是展开的
            	//收起所有展开项
            	$(this).addClass("row-details-close").removeClass("row-details-open");
            	$(this).parent().parent().next().remove();
            	detailsInfo = null;
            }else{
            	//如果是收起的
            	$('tr .details').parent().remove();
            	$('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('tbody tr .details').parents('tr')[0];
            	pageIndex = parseInt($('.pagination-panel-input').val());
            	
            }
            return;
		});
        
      
      //返回虚拟化中心
      $('#returnVcenter').on('click', returnVcenter);
		
    }
    
    var returnVcenter = function(){
    	LOCATION('./content/vm/vcenter_manager.php');
    }
    
    var engineFileDownload = function(){
    	var dataset = this.dataset;
    	var downloadFunction = "downloadEngineFile";
		var href = CONF.AJAXPATH + '?m=' + CONF.M.VCENTER + '&uuid=' + 
		dataset.uuid + '&path=' + encodeURIComponent(dataset.path) + '&size=' + dataset.size + 
		'&name='+ encodeURIComponent(dataset.name) + '&f=' + downloadFunction;
		window.location.href = href;
    }

    var initRow = function(){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var opDiv = $('#engine_datatable tbody > tr').find('td:eq(9)');
		for(var i=0; i<opDiv.length; i++){
			opDownload(opDiv[i], data[i]);
		}
//		addDetailsInfo();
		//下载文件
	      $('.enginefiledownload').off().on('click', engineFileDownload);
	}
	
    //操作
	var opDownload = function(div, data){
		var btn = '<a class="enginefiledownload" title="' + LANG.UI_PUBLIC_DOWNLOAD + 
		'" data-uuid="' + data[11] + '" data-path="' + htmlEncode(data[9]) + '" data-size="' + data[10] + '" data-name="' + htmlEncode(data[5]) +
		'">' + '<i class="viconfont vicon-ge_download"></i></a>';
		$(div).html(btn);
	}
	
	var htmlEncode = function (str) {
		var s = "";
        if(str.length == 0) return "";
        s = str.replace(/&/g,"&amp;");
        s = s.replace(/</g,"&lt;");
        s = s.replace(/>/g,"&gt;");
//        s = s.replace(/ /g,"&nbsp;");
        s = s.replace(/\'/g,"&#39;");
        s = s.replace(/\"/g,"&quot;");
        return s; 
	}
	var htmlDecode = function (str) {
		var s = "";
        if(str.length == 0) return "";
        s = str.replace(/&amp;/g,"&");
        s = s.replace(/&lt;/g,"<");
        s = s.replace(/&gt;/g,">");
        s = s.replace(/&nbsp;/g," ");
        s = s.replace(/&#39;/g,"\'");
        s = s.replace(/&quot;/g,"\"");
        return s; 
	}
    
	//检测刷新的时候是否有展开项目，如果有的话就添加
	var addDetailsInfo = function(){
		var currentPage = parseInt($('.pagination-panel-input').val());
		if(currentPage != pageIndex){
			return true;
		}
		if(detailsInfo){
			var tr = $('tbody tr');
			var data = grid.getDataTable().data();
        	addDetails(tr[detailsIndex], data[detailsIndex]);
			var openTr = $('tbody > tr')[detailsIndex];
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
	}
	
	
	

	//添加详情信息 
	var addDetails = function(nTr, data){
		if(!data){
			return;
		}
    }
    
    var getEngineDetails = function(portList){
		var details = '';

        return details;
    }

    //得到每行的HTML
	var getEachRowHtml = function(key, value){
		var html = "<tr><td>" + key + ":</td><td colspan='10'>";
		html += value;
		html += "</td></tr>";
		return html;
	}
	

    var handleRecords = function () {
		//读取cookie里的保存列表状态
    	var length = "";
    	if($.cookie('pageLength')){
    		var pageList = JSON.parse($.cookie('pageLength'));
    		if(pageList.engine){
        		length = pageList.engine;
        	}
    	}
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 1, 9]
    			}],
    			"order": [
                    [5, "desc"]
                ],
                'pageLength': parseInt(length)
    	};
    	grid = new Datatable();
		var data = {m:CONF.M.VCENTER,f:'getEngineData',p:{}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#engine_datatable"), showDetail:false, checkbox:true, onDataLoad:initRow, dataTable:dataTableOpt});
    	
//    	$("#download").on('click', function(){
//    		downloadEngineData(grid);
//    	});
    	$("#delete").on('click', function(){
    		deleteEngineData(grid);
    	});
    	return;
    }
    
    
//    var downloadEngineData = function(grid){
//    	var select = grid.getSelectedRows();
//		if(!select.length){
//			return tipDownload();
//		}
//		if(select.length > 1){
//			return UIToastr.showInfo(LANG.UI_VCENTER_ENGINE_MANAGE_DOWNLOAD, LANG.UI_VCENTER_ENGINE_MANAGE_DOWNLOAD_TIPS);
//		}
//    }
//
//	
//	var tipDownload = function(){
//		UIToastr.showInfo(LANG.UI_VCENTER_ENGINE_MANAGE_DOWNLOAD, LANG.UI_VCENTER_ENGINE_MANAGE_DOWNLOAD_NO_SELECT_TIPS);
//	}
	
	var tipDelete = function(){
		UIToastr.showInfo(LANG.UI_VCENTER_ENGINE_MANAGE_DELETE, LANG.UI_VCENTER_ENGINE_MANAGE_DELETE_TIPS);
	}
	
    
    
    var deleteEngineData = function(grid){
    	var select = grid.getSelectedRows();
		if(!select.length){
			return tipDelete();
		}
		bootbox.confirm({
            title: LANG.UI_VCENTER_ENGINE_MANAGE_DELETE,
            message: LANG.UI_VCENTER_ENGINE_MANAGE_DELETE_CONFIRM_TIPS,
            callback: debounce(function(r) {
                if(!r) return;
                submitDelete(select, grid);
            }, 300)
        });
    }
    
    var submitDelete = function(select, grid){
        var data = {};
		data.list = select;
		data = JSON.stringify(data);
		var grid = grid;
		
		Metronic.blockUI({target: '#engineContent',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'deleteEngineData',p:data}, function(data){
			Metronic.unblockUI('#engineContent');
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
    	});
    }

    return {
        init: function(){
			handleRecords();
			initListeners();
        }
    }
}();

jQuery(document).ready(function(){
    EngineDataManager.init();
});