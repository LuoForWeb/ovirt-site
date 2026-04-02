var ShareTask = function () {
    //当前文件路径, 目录是否完成 true未完成, 下次开始, 路径数组,时间点UUID,缓存文件目录,搜索
    $('#graininess_job').on('click', function () {
        let uuid = $("#data_uuid").val()
        var url = './content/platform/recovery/graininess_job.php?uuid=' + uuid
        LOCATION(url,'task');
    });


    return {
        //main function to initiate the module
        init: function () {
        }
    };
}();

jQuery(document).ready(function() {
    ShareTask.init();
});