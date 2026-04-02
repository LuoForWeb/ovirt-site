var SystemSettings = function () {
    function addListeners () {
        // 初始化上传按钮
        initUploadLogo();

        $('#submit').on('click', submit);

        $('.close-btn').on('click', closeUpload);

        $('.cancel-btn').on('click', ()=>{
            LOCATION('./content/platform/settings/setting_manager.php', 'setting_manager');
        })
    }
    // 封装上传图片事件
    function uploadImage(elem, fun){
        $(elem).fileupload({
            url: `/api/v1/system/upload`,
            dataType: 'json',
            acceptFileTypes: 'image',
            method: 'POST',
            headers: {
                'x-api-version': '1.0-rev0',
                'Authorization': window.localStorage.getItem('access_token')
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .bar').css('width', progress + '%');
            },
            done: function (e, data) {
                var result = data.result;
               if (result.code == 0) {
                   UIToastr.showSuccess('上传文件', '上传成功');
                   // 上传成功
                    $(elem + '_url').val(result.data.info);
                   var img = '<a href="'+result.data.info+'" target="_blank"><img src="'+result.data.info+'"></a>';
                    $(elem+'_url_show').html(img);
                    // 回调函数
                   fun(result.data);
               } else {
                   UIToastr.showError('上传文件', result.message);
                   return false;
               }
            },
            start: function (e) {
            },
            stop: function (e) {
            },
            fail: function (e, data) {
                UIToastr.showWarning('上传文件', '上传失败');
                return false;
            },
        });
    }

    function initUploadLogo(){
        // 初始化上传按钮
        uploadImage('#diy_logo_value', function (data){
            var img = ' <img class="upload-img upload-flag" style="width:150px;height:50px" src="'+data.info+'">';
            $('#item_logo .upload-div').find('.upload-flag').replaceWith(img)
            $('#item_logo .upload-div').attr('title', '点击替换');
            $('#item_logo .upload-div').css({
                'background': '#fff'
            });
        });
    }

    function submit () {
        var data = {};
        var sysName = $('input[name="custom_name"]').val();
        data.logo = '';
        if ($('.upload-flag').attr('src') != undefined) {
            data.logo = $('#diy_logo_value_url').val();
        }
        data.sys_name = sysName;
        pAjaxRequest(data, '/api/v1/system/setting', 'POST', function (res) {
            console.log(res);
            var op = '保存系统配置';
            operateResponseList(res, op);
        })
    }

    function closeUpload () {
        var html = `<span class="upload-flag"><i class="viconfont vicon-shangchuan"></i>上传logo</span>`;
        $('#item_logo .upload-div').find('.upload-flag').replaceWith(html);
        $('#diy_logo_value_url').val('');
    }

    function initOldConfig () {
        pAjaxRequest({}, '/api/v1/system/setting', 'GET', function (res) {
            $('input[name="custom_name"]').val(res.data.sys_name);
            if (res.data.logo) {
                var url = res.data.logo;
                var img = ' <img class="upload-img upload-flag" style="width:150px;height:50px" src="'+url+'">';
                $('#item_logo .upload-div').find('.upload-flag').replaceWith(img);
                $('#item_logo .upload-div').attr('title', '点击替换');
                $('#item_logo .upload-div').css({
                    'background': '#fff'
                });
                $('#diy_logo_value_url').val(url);
                $('.custom-logo').show();
            }
        })
    }

    return {
        init: function () {
            initOldConfig();
            addListeners();
        }
    }
}();

$(document).ready(function () {
    SystemSettings.init();
});