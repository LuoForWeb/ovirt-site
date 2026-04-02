var DbBackup = function () {
	
	var initListener = function(){
//		$('#cancel').on('click', function(){
//			alert('cancel');
//		});
//		
//		$('#submit').on('click', function(){
//			var data = {};
//			var jsonData = JSON.stringify(data);
//			$.post(CONF.AJAXPATH, {m:CONF.M.DB,f:'testMsg',p:jsonData}, function(d){
//				var info = JSON.parse(d);
//	    	});
//		});
		
	}

    return {
        //main function to initiate the module
        init: function () {
        	initListener();
        }

    };

}();


jQuery(document).ready(function() {    
	DbBackup.init();
});