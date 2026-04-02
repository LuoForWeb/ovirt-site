var Lock = function () {
	
	var addListners = function(){
		$("#login").on("click",function(){
			var psd = $('#psd').val();
			var username = $('#username').text();
			if(!psd){
				$('.alert-danger').show();
				return;
			}
			submit(username, psd);
		})
		$('#psd').keypress(function (e) {
            if (e.which == 13) {
            	submit($('#username').text(), $('#psd').val());
                return false;
            }
        });
	}
	var submit = function(username, password){
		var password = hex_md5(password);
		var data = JSON.stringify({username:username,password:password});
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'login',p:data}, loginResult);
	}
	
	var loginResult = function(data){
		var loginResult = JSON.parse(data);
		var tips = '';
		if(1 == loginResult){
			//login success
			window.location = './';
			return;
		}else if(2 == loginResult){
			tips = LANG.UI_LOGIN_ERROR_TIPS;
		}else if(3 == loginResult){
			tips = LANG.UI_LOGIN_USER_LOCKED_TIPS;
		}else if(5 == loginResult){
			tips = LANG.UI_LOGIN_USER_LOGIN_OVERTIME;
		} else{
			tips = LANG.UI_PUBLIC_UNKNOWN_ERROR;
		}
		$('.alert-danger span').html(tips).show();
		$('.alert-danger').show();
		$('#psd').val('');
	}

	//如果session已清除,需要重新登录
	var checkSessionExist = function(){
		var lang = $('.systemLang').val();
		if(lang == ""){
			window.location.href="/login.php";
		}
	}
    return {
        //main function to initiate the module
        init: function () {
             $.backstretch([
		        "../../assets/admin/pages/media/bg/1.jpg",
    		    "../../assets/admin/pages/media/bg/2.jpg",
    		    "../../assets/admin/pages/media/bg/3.jpg",
    		    "../../assets/admin/pages/media/bg/4.jpg"
		        ], {
		          fade: 2000,
		          duration: 8000
		      });
			 addListners();
			 checkSessionExist();
        }
    };
}();

jQuery(document).ready(function() {    
    Metronic.init(); // init metronic core components
	Layout.init(); // init current layout
    Lock.init();
});