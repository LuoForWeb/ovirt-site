var UIToastr = function () {

    return {
        //main function to initiate the module
        init: function () {
        	toastr.options = {
        		"closeButton": true,
				"debug": false,
				"preventDuplicates": true, //重复内容的提示框只出现一次，无论提示框是打开还是关闭
				"preventOpenDuplicates": true,  //重复内容的提示框在开启时只出现一个 如果当前的提示框已经打开，不会多开。直到提示框关闭后，才可再开
//				"positionClass": "toast-top-full-width",
				"positionClass": "toast-top-center",
				"onclick": null,
//				"showDuration": "100000",
//				  "hideDuration": "100000",
//				  "timeOut": "5000000",
//				  "extendedTimeOut": "1000000",
            };
        },
        
        //show success msg
    	showSuccess: function(title, message){
    		toastr.clear();
    		toastr["success"](message, title);
    	},
    	
    	//show  info msg
    	showInfo: function(title, message){
    		toastr.clear();
    		toastr["info"](message, title);
    	},
    	
    	//show warning msg
    	showWarning: function(title, message){
    		toastr.clear();
    		toastr["warning"](message, title);
    	},
    	
    	//show error msg
    	showError: function(title, message){
    		toastr.clear();
    		toastr["error"](message, title);
    	},
    };
}();