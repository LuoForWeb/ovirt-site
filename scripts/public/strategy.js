var Strategy = function () {
	var isInit = false;
	var _module;
	
	//设置成功标志
	var selectSuccess = function(type, panelHeading, activeLi){
		var html = getSelectType(type) + "(" + $.trim(activeLi[0].textContent) + ")";
		panelHeading.find('a').html('<i></i> ' + html);
		var i = panelHeading.find('i');
		i.attr('class', 'fa fa-check font-green-seagreen');
	}
	//取消成功标志
	var selectFailure = function(type, panelHeading){
		var html = getSelectType(type);
		panelHeading.find('a').html('<i></i>' + html);
		var i = panelHeading.find('i');
		i.attr('class', '');
	}
	
	//得到备份类型描述
	var getSelectType = function(type){
		var des = "";
		if("full" == type){
			des = LANG.UI_PUBLIC_BACKUP_FULL;
		}else if("incr" == type){
			des = LANG.UI_PUBLIC_BACKUP_INCREMENT;
		}else if("diff" == type){
			des = LANG.UI_PUBLIC_BACKUP_DIFFRENCE;
		}
		return des;
	}
	
	//得到strategy_each信息
	var getStrategyEach = function(type, tabpane){
		var info = {};
		//type:每天/每周/每月
		info.type = type;
		info.startTime = tabpane.find('.starttime').val();
		info.rollFlag = tabpane.find('.make-switch').get(0).checked;
		info.rollInterval = tabpane.find('.rolltime').val();
		info.endTime = tabpane.find('.endtime').val();
		
		return info;
	}
	
	var getDays = function(type, tabpane){
		var days = [];
		var input;
		if(type == CONF.STRATEGY_TYPE.WEEK){
			input = tabpane.find('.weekcheck').find('input');
		}else if(type == CONF.STRATEGY_TYPE.MONTH){
			input = tabpane.find('.monthcheck').find('input');
		}else{
			return days;
		}
		var flag = false;
		input.each(function(i, d){
			if(d.checked){
				days[i] = 1;
				flag = true;
			}else{
				days[i] = 0;
			}
		});
		
		if(!flag){
			if(type == CONF.STRATEGY_TYPE.WEEK){
				$('.selectweektip').html(LANG.UI_STRATEGY_SELECT_WEEK).show();
			}else if(type == CONF.STRATEGY_TYPE.MONTH){
				$('.selectmonthtip').html(LANG.UI_STRATEGY_SELECT_DATE).show();
			}
		}else{
			$('.selectweektip').hide();
			$('.selectmonthtip').hide();
		}
		
		return days;
	}
	
	//每天
	var daySelect = function(tabpane, type){
		var info = {};
		info = getStrategyEach(CONF.STRATEGY_TYPE.DAY, tabpane);
		//type:完备/增备/差备
		return _module.setBackupInfo(type, info);
	}
	//每周
	var weekSelect = function(tabpane, type){
		var info = {}, days = [];
		info = getStrategyEach(CONF.STRATEGY_TYPE.WEEK, tabpane);
		info.days = getDays(CONF.STRATEGY_TYPE.WEEK, tabpane);
		var flag = false;
		$.each(info.days, function(i, d){
			if(d){
				flag = true;
			}
		});
		if(!flag){
			_module.resetBackupInfo(type);
			return false;
		}
		//type:完备/增备/差备
		return _module.setBackupInfo(type, info);
	}
	//每月
	var monthSelect = function(tabpane, type){
		var info = {}, days = [];
		info = getStrategyEach(CONF.STRATEGY_TYPE.MONTH, tabpane);
		info.days = getDays(CONF.STRATEGY_TYPE.MONTH, tabpane);
		var flag = false;
		$.each(info.days, function(i, d){
			if(d){
				flag = true;
			}
		});
		if(!flag){
			_module.resetBackupInfo(type);
			return false;
		}
		//type:完备/增备/差备
		return _module.setBackupInfo(type, info);
	}
	//全局
	var globalSelect = function(tabpane, type){
		
	}
	
	//每一个策略确定按钮
	var eachButtonClick = function(){
		var tabpane = $(this).closest('.tab-pane');
		var panelDefault = tabpane.closest('.panel-default');
		var panelHeading = panelDefault.children('.panel-heading');
		var activeLi = $(this).closest('.tabbable-line').find('li.active').find('a');
		//完全/增量/差异
		var type = panelDefault.get(0).id;
		var setFlag = false;
		if(tabpane.hasClass('day')){
			setFlag = daySelect(tabpane, type);
		}else if(tabpane.hasClass('week')){
			setFlag = weekSelect(tabpane, type);
		}else if(tabpane.hasClass('month')){
			setFlag = monthSelect(tabpane, type);
		}else if(tabpane.hasClass('global')){
			setFlag = globalSelect(tabpane, type);
		}
		if(setFlag){
			selectSuccess(type, panelHeading, activeLi);
		}else{
			selectFailure(type, panelHeading);
		}
	}
	
	//每一个策略取消按钮
	var cancelButtonClick = function(){
		var tabpane = $(this).closest('.tab-pane');
		var panelDefault = tabpane.closest('.panel-default');
		var panelHeading = panelDefault.children('.panel-heading');
		var type = panelDefault.get(0).id;
		//重置显示
		selectFailure(type, panelHeading);
		//重置数据
		_module.resetBackupInfo(type);
	}
	
	var initEachButtonHandler = function(){
		$('.each-button').on("click", eachButtonClick);
		$('.cancel-button').on("click", cancelButtonClick);
	}
	
	var setFlag = function(flag){
		isInit = flag;
	}
	
	var getFlag = function(){
		return isInit;
	}
	
	var setModule = function(m){
		_module = m;
	}
	
    return {
        //main function to initiate the module
        init: function () {
        	if(getFlag()){
        		//init only once
        		return true;
        	}
        	initEachButtonHandler();
        	setFlag(true);
        },
        
        //初始化模块
        initModule: function(module){
        	setModule(module);
        },
       
    };
}();
jQuery(document).ready(function() {   
	Strategy.init();
});