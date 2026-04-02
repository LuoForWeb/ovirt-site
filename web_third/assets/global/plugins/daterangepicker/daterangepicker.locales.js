var DateRangePickerLocales = function () {
	var getLocalConfig = function(LANGUAGE){
		//当前只判断是否是中文和英文,是中文就返回中文的内容,是英文就返回英文的内容
		var lang = {
	        format: 'YYYY-MM-DD HH:mm',
	        separator: ' - ',
	        applyLabel: 'Apply',
	        cancelLabel: 'Cancel',
	        fromLabel: 'From',
	        toLabel: 'To',
	        customRangeLabel: 'Custom',
	        daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
	        monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
	        firstDay: 1
	    };
		if("zh-cn" == LANGUAGE){
			lang = {
		        format: 'YYYY-MM-DD HH:mm',
		        separator: ' - ',
		        applyLabel: '确 定',
		        cancelLabel: '取 消',
		        fromLabel: '从',
		        toLabel: '到',
		        customRangeLabel: '自定义',
		        daysOfWeek: ['日', '一', '二', '三', '四', '五','六'],
		        monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
		        firstDay: 1
		    };
		}
		
		return lang;
	};
	
	var getRangesConfig = function(LANGUAGE){
		var ranges = {
	        'Today': [moment().startOf('day'), moment()],
	        'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
	        'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment()],
	        'Last 30 Days': [moment().subtract(29, 'days').startOf('day'), moment()],
	        'This Month': [moment().startOf('month'), moment().endOf('month')],
	        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	    };
		
		if("zh-cn" == LANGUAGE){
			ranges = {
		        '今天': [moment().startOf('day'), moment()],
		        '昨天': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
		        '最近7天': [moment().subtract(6, 'days').startOf('day'), moment()],
		        '最近30天': [moment().subtract(29, 'days').startOf('day'), moment()],
		        '本月': [moment().startOf('month'), moment().endOf('month')],
		        '上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		    };
		}
		
		return ranges;
	};
	
	return {
		getLocalConfig: function (LANGUAGE) {
        	return getLocalConfig(LANGUAGE);
        },
        
        getRangesConfig: function (LANGUAGE) {
        	return getRangesConfig(LANGUAGE);
        },

    };
}();