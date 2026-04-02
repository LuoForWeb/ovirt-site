var AboutUS = function(){
    var initBaseInfo = function(){
        pAjaxRequest({}, '/api/v1/system/auth/basic/info', 'get', (res) => {
            if(!res.success){
				return;
			}
            //获取基本信息
			let data =  res.data;
            let status =  data.system_info.status; //系统授权状态 1授权 2 未授权 3过期 4 授权异常 
            if(status == null){
				status = 2;
			}
            //英文版特殊处理
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                if(status == 2 || (status == 1 && data.system_info.trial == 1)){
                    //海外版未授权需要屏蔽售后电话
                    $('.teleitem').hide();
                    //未授权需要将邮箱改成售前邮箱
                    $('#email').text("customer.service@vinchin.com");
                }
                //trial 1是试用 2是正式
                //授权异常以及授权过期按照之前授权显示，例如授权异常/过期前的授权是试用授权则只显示售前邮箱，屏蔽电话，授权过期/异常之前是正式/永久授权则显示售后电话和售后邮箱
                if((status == 3 || status == 4) && data.system_info.trial == 1){
                    //海外版过期需要屏蔽售后电话
                    $('.teleitem').hide();
                     //未授权需要将邮箱改成售前邮箱
                    $('#email').text("customer.service@vinchin.com");
                }
            }
        });
    }
    return {
        //main function to initiate the module
        init: function () {
            initBaseInfo();
        }

    };
}();

jQuery(document).ready(function() {   
	AboutUS.init();
});