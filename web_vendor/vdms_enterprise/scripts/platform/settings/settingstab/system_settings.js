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
        // 设置input属性
        $(elem).attr({
            'accept': '.gif,.jpg,.jpeg,.png',
            'data-maxsize': 20971520 // 20MB
        });

        $(elem).fileupload({
            url: `/api/v1/system/upload`,
            dataType: 'json',
            method: 'POST',
            autoUpload: false, // 关键：禁止自动上传
            headers: {
                'x-api-version': '1.0-rev0',
                'Authorization': window.localStorage.getItem('access_token')
            },
            // 在文件添加时验证
            add: function (e, data) {
                var maxSize = 20 * 1024 * 1024;
                var errors = [];

                $.each(data.files, function(index, file) {
                    // 大小验证
                    if (file.size > maxSize) {
                        errors.push(file.name + ' 超过20MB限制');
                    }

                    // 类型验证
                    var allowedTypes = /\.(gif|jpg|jpeg|png)$/i;
                    if (!allowedTypes.test(file.name)) {
                        errors.push(file.name + ' 格式不支持');
                    }
                });

                if (errors.length > 0) {
                    UIToastr.showError('上传文件', errors.join('<br>'));
                    return false; // 阻止上传
                }

                // 验证通过，显示预览并手动触发上传
                var file = data.files[0];
                var reader = new FileReader();

                reader.onload = function(e) {
                    // 可以在这里显示图片预览
                };
                reader.readAsDataURL(file);

                // 手动提交
                data.submit();
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .bar').css('width', progress + '%');
            },
            done: function (e, data) {
                var result = data.result;
                if (result.code == 0) {
                    UIToastr.showSuccess('上传文件', '上传成功');
                    $(elem + '_url').val(result.data.info);
                    var img = '<a href="'+result.data.info+'" target="_blank"><img src="'+result.data.info+'"></a>';
                    $(elem+'_url_show').html(img);
                    fun(result.data);
                } else {
                    UIToastr.showError('上传文件', result.message);
                }
            },
            fail: function (e, data) {
                UIToastr.showWarning('上传文件', '上传失败');
            }
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