

var addeditor,editeditor;
var Scripts_manager = function () {
    var changeHeightFlag = false;
    var queryParams = {};
    var Script_Type = ["txt",".sh",".bat",".yaml",".py",".py",".sql",".ps1",".psm1",".psd1"];



    //初始化数据
    var initDataTable = function () {
        var options = {
            toolbarId: '#vin_kubernetes_script_toolbar',
            vin_url:"/api/v1/scripts",
            vin_method:"GET",
            vin_params:function(){
                let searchval = $('#searchVal').val();
                if(searchval == null || searchval == ""){
                    return {};
                }else{
                    return {'search':searchval}
                }
               
            },
            searchInput: false,
            pagination: true,
            pageList:[5,10,25,50],
            changeHeightBtn: true, //改变高度按钮
            showJumpTo: true,
            resizable: true,
            onResetView:function initTableHeight() {
                //拿到父窗口的高度
                var height;
                var panelH = window.innerHeight;
        
                height = panelH -255;
        
                $("#kuberneteScriptDiv .fixed-table-body").css({
                    "height": height
                });
            },
            onPostBody:function (){
                btnDisplayClass();
                $('[data-toggle="tooltip"]').tooltip();
            },
            columns:[
                    {
                        checkbox:true,
                        sortable: false,
            },
            //         {
            //             field: 'id',
            //             title: '编号',
            //             sortable: true,
            //             align: 'left',
            // },
                    {
                        field: 'script_name',
                        title: LANG.WEB_SCRIPT_SCRIPT_NAME,
                        sortable: true,
                        align: 'left',
                },
                {
                    field: 'script_description',
                    title: LANG.WEB_SCRIPT_SCRIPT_DESCRIPTION,
                    sortable: true,
                    align: 'left',
                },
                {
                            field: 'script_type_des',
                            title: LANG.WEB_SCRIPT_SCRIPT_TYPE,
                            sortable: true,
                            align: 'left',
                },
                    
                {
                    field: 'update_time',
                    title: LANG.WEB_SCRIPT_UPDATE_TIME,
                    sortable: true,
                    align: 'left',
                },
                {
                    // field: 'update_time',
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: true,
                    align: 'left',
                    events: operateEvents,
                    // type: 'operation',
                    formatter: function (value,row,index,field){
                        var button = '<div class="btn-group">';
                        button +='<div class="btn_operation_vicon">'
                        if ($.inArray('p_scripts_manager_edit', CONF.PERMISSION_ARR) !== -1){
                            button += '<a class="editScript"><i class="viconfont vicon-a-Editbianji"></i></a>';
                        }
                        if ($.inArray('p_scripts_manager_delete', CONF.PERMISSION_ARR) !== -1){
                            button += '<a class="deleteScript"><i class="viconfont vicon-a-Deleteshanchu1"></i></a>';
                        }
                            button += '<a class="downloadScript"><i class="viconfont vicon-xiazai"></i></a>'
                        '</div>';
                        button += '</div>';
                        return button;
                    }
                },


                ],


        }
        $('#script_table').baseTableConfig().init(options);
    }

    //清空输入的内容
    var clear_script = function(){
        //清空脚本名称
        $("#script_name").val("");
        //清空功能描述
        $("#script_description").val("");
        //请空脚本类型
        $("#script_type").val(0);
        //清空脚本内容
        addeditor.setValue("");
    }
   

    //添加脚本
    var addScript = function (){
        var dataList = {};
        //获取脚本名称
        dataList.script_name = $('#script_name').val();
        //获取脚本描述
        dataList.script_description = $('#script_description').val();
        //获取脚本类型
        dataList.script_type = $('#script_type').val();
        //获取脚本内容
        dataList.script_content =addeditor.getValue();
        if(dataList.script_name == "" || dataList.script_type == 0 || dataList.script_content == ""){
            UIToastr.showInfo(LANG.WEB_SCRIPT_ADD_SCRIPT, LANG.WEB_SCRIPT_SCRIPT_CONTENT_REQUIRED);
            return;
        }
        var requestAddScript = function(d){
           if(d.success){
               //添加成功
               UIToastr.showSuccess(LANG.WEB_SCRIPT_ADD_SCRIPT,d.message);
               $('#script_table').bootstrapTable('refresh');
               $('#addScriptmodal').modal('hide');
               clear_script();
           }else{
               UIToastr.showInfo(LANG.WEB_SCRIPT_ADD_SCRIPT,d.message);
           }
        }
        // 发送后端
        pAjaxRequest(dataList, "/api/v1/scripts", "POST", requestAddScript,false);

    }

    //修改脚本
    var editScript = function (){
        var dataList = {};
        //获取脚本名称
        dataList.script_name = $('#edit_script_name').val();
        //获取脚本描述
        dataList.script_description = $('#edit_script_description').val();
        //获取脚本类型
        dataList.script_type = $('#edit_script_type').val();
        //获取脚本内容
        dataList.script_content = editeditor.getValue();
        //获取uuid
        dataList.script_uuid = $('#edit_script_uuid').val();
        if(dataList.script_name == "" || dataList.script_type == 0){
            UIToastr.showInfo(LANG.WEB_SCRIPT_EDIT_SCRIPT, LANG.WEB_SCRIPT_FILL_NAME_AND_TYPE);
            return;
        }

        var requestAddScript = function(d){
            if(d.success){
                //添加成功
                UIToastr.showSuccess(LANG.WEB_SCRIPT_EDIT_SCRIPT,d.message);
                $('#script_table').bootstrapTable('refresh', {
                    query: queryParams
                });
                $('#editScriptmodal').modal('hide');
            }else{
                UIToastr.showInfo(LANG.WEB_SCRIPT_EDIT_SCRIPT,d.message);
            }

        }
        // 发送后端
        pAjaxRequest(dataList, "/api/v1/scripts/"+dataList.script_uuid, "POST", requestAddScript,false);
    }

    //根据是否有勾选添加样式
    var btnDisplayClass = function(){
        $("input[name='btSelectItem'], input[name='btSelectAll']").on('change',function (){
            var selectedRow = $('#script_table').bootstrapTable("getSelections");
            if(selectedRow.length != 0){
                //如果有勾选 则改变图标颜色
                $(".grey_box_btn").addClass('grey_box_btn_hover');
            }else{
                $(".grey_box_btn").removeClass('grey_box_btn_hover');
            }
        })
    }

    //改变表格高度
    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#script_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'padding-bottom': '10.25px'
            })
            $('#vin_kubernetes_script_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#script_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#vin_kubernetes_script_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }

    //批量删除勾选
    var deleteSelect = function(){
        bootbox.confirm({
            title: LANG.WEB_SCRIPT_DELETE_SCRIPT,
            message:LANG.WEB_SCRIPT_DELETE_SOME_CONFIRM,
            buttons: {
                confirm: {
                    label: LANG.UI_PUBLIC_CONFIRM,
                    className: 'btn-primary'
                },
                cancel: {
                    label: LANG.UI_PUBLIC_CANCEL,
                    className: 'btn-default'
                }
            },
            callback: debounce(function (result) {
                if (result) {
                    Metronic.blockUI({target: $("#kuberneteScriptDiv"),animate: true});
                    //获取勾选
                    var selectedRow = $('#script_table').bootstrapTable("getSelections");
                    let dataList = {};
                    dataList.script_uuid = [];
                    for(let i=0;i<selectedRow.length;i++){
                        dataList.script_uuid.push(selectedRow[i].script_uuid);
                    }
                    var requestDeleteScript = function (d){
                        Metronic.unblockUI($("#kuberneteScriptDiv"));
                        // alert(d.message);
                        if(d.success){
                            //删除成功
                            UIToastr.showSuccess(LANG.WEB_SCRIPT_DELETE_SCRIPT,d.message);
                            //$('#script_table').bootstrapTable('refresh');
                            //更新表格数据
                            $('#script_table').bootstrapTable('refresh', {
                                query: queryParams
                            });
                        }else{
                            UIToastr.showInfo(LANG.WEB_SCRIPT_DELETE_SCRIPT,d.message);
                        }
                    }
                    //发送后端接口
                    pAjaxRequest(dataList, "/api/v1/scripts", "DELETE", requestDeleteScript,true);
                } else {
                    // 用户点击了“取消”按钮
                    // 这里可以添加取消后的处理逻辑，如果不需要处理，可以留空
                }
            },300)
            
        });
    }
    //初始化脚本插件
    var initEditor = function(){
        
        //初始化添加脚本插件
        addeditor = ace.edit("script_content");
        addeditor.session.setUseWrapMode(true);
        addeditor.setShowPrintMargin(false);

        //初始化修改脚本插件
        editeditor = ace.edit("edit_script_content");
        editeditor.session.setUseWrapMode(true);
        editeditor.setShowPrintMargin(false);



    }


    //初始化缩放
    var initZoom = function(){
         // handle portlet fullscreen
         $('.script_div').on('click', '.portlet > .portlet-title .vicon-a-apikeydaima', function(e) {
            e.preventDefault();
            var portlet = $(this).closest(".portlet");
            if (portlet.hasClass('portlet-fullscreen')) {
                $(this).removeClass('on');
                portlet.removeClass('portlet-fullscreen');
                $('.script_msg').css({
                    'height': '150px', // 设置元素高度为窗口高度
                    'overflow-y': 'auto' // 内容超出时显示垂直滚动条
                });
                $('body').removeClass('page-portlet-fullscreen');
            } else {
                var height = Metronic.getViewPort().height -
                    portlet.children('.portlet-title').outerHeight() -
                    parseInt(portlet.children('.portlet-body').css('padding-top')) -
                    parseInt(portlet.children('.portlet-body').css('padding-bottom'));
                $(this).addClass('on');
                var titleHeight = $('.script_div .caption').outerHeight(true)+50; // 获取窗口可视高度
                    $('.script_msg').css({
                        'height': `calc(100vh - ${titleHeight}px)`, // 设置元素高度为窗口高度
                        'overflow-y': 'auto' // 内容超出时显示垂直滚动条
                    });
                portlet.addClass('portlet-fullscreen');
                $('body').addClass('page-portlet-fullscreen');
            }
        });
    }

    //初始化上传插件
    var initFileInput = function(){
        //初始化添加的文件上传
        $('#chooseFile').on('click', function(e) {
            e.preventDefault(); // Prevent default link behavior
            $('#hiddenFileInput').trigger('click'); // Trigger hidden input
        });
        $('#hiddenFileInput').on('change', function(e) {
            var file = e.target.files[0];
            if (!file) {
                return;
            }
            // Check file type based on the accept attribute
        //[".sh",".bat",".yaml",".py",".py",".sql",".ps1",".psm1",".psd1"]
            if (!Script_Type.includes('.' + file.name.split('.').pop())) {
                alert(LANG.WEB_SCRIPT_SELECT_FILE_TYPES);
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                let fileContent = e.target.result; // Store file content in variable
                addeditor.setValue(fileContent);
                
            };
            reader.readAsText(file);
        });


        //初始化修改的文件上传
        $('#chooseFileEdit').on('click', function(e) {
            e.preventDefault(); // Prevent default link behavior
            $('#hiddenFileInputEdit').trigger('click'); // Trigger hidden input
        });
        $('#hiddenFileInputEdit').on('change', function(e) {
            var file = e.target.files[0];
            if (!file) {
                return;
            }
            // Check file type based on the accept attribute
            if (!Script_Type.includes('.' + file.name.split('.').pop())) {
                alert(LANG.WEB_SCRIPT_SELECT_FILE_TYPES);
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                let fileContent = e.target.result; // Store file content in variable
                editeditor.setValue(fileContent);
                
            };
            reader.readAsText(file);
        });
    }


    //检测脚本关键词高危行为
    var checkDangerKeyword = function(script_content){
        // 定义高危关键词列表
        const highRiskKeywords = [
            // SQL Keywords
            'drop', 'delete', 'truncate', 'alter', 'update', 'insert', 'select *', 'union', 
            '--', ';', '/*', '*/',
            
            // Shell (Bash) Commands
            'rm -rf', 'cp', 'mv', 'chown', 'chmod 777', 'sudo', 'system', 'exec', '|', '&', ';',
            
            // Windows Batch (BAT) Commands
            'del /s', 'erase', 'move', 'copy', 'net use', 'net share', 'net user', 'format', 'shutdown', 'start',
            
            // YAML Script Keywords (for execution contexts like Ansible)
            'command:', 'shell:', 'script:', 'delegate_to:', 'become:', 'become_user:',
            
            // Python Script Dangerous Functions
            'os.system', 'subprocess.call', 'subprocess.run', 'eval', 'exec', 'globals', 'locals', '__import__',
            
            // Linux Commands
            'chattr +i', 'dd if=/dev/zero of=/dev/sdX', 'iptables -F', 'killall', 'reboot', 'poweroff','rm'
        ];

        // 记录发现的高危关键词
        let foundKeywords = [];

        // 遍历关键词列表，检查是否出现在输入的SQL字符串中
        highRiskKeywords.forEach(keyword => {
            if (script_content.toLowerCase().includes(keyword.toLowerCase())) {
                foundKeywords.push(keyword);
            }
        });

        // 返回发现的所有高危关键词
        return foundKeywords;

    }

   //监听事件
    var initListeners = function(){
        initEditor();
        initZoom();
        initFileInput();


        //添加脚本
        $('#addScript').on('click',function(){
            $('#addScriptmodal').modal({'width':'800px', 'height':'512px'});
        })
        //确认添加脚本
        $('#current_script_submit').on('click',function(){
           addScript();
        })
        //确认修改
        $('#edit_script_submit').on('click',function(){
            editScript();
        })

        // 改变表格高度
        $('#vin_kubernetes_script_toolbar .change_height').on('click', change_height);
        
        //批量删除事件
        $("#deleteSelect").on('click',function(){
            deleteSelect();
        })

        //自定义搜索输入框监听
        $('.scriptCustomSearch').on('focus', function () {
            $('.clear').addClass('show');
            $('.search input').removeAttr('placeholder');
        });

        $('.scriptCustomSearch').on('blur', function () {
            if ($('.scriptCustomSearch').val() == '') {
                $('.clear').removeClass('show');
                $('.search input').attr('placeholder', LANG.WEB_SCRIPT_SEARCH);
            };
        });

        $('.clear').on('click', function () {
            $('.scriptCustomSearch').val('');
            $('.clear').removeClass('show');
            sessionStorage.removeItem("search");
            $('.search input').attr('placeholder', LANG.WEB_SCRIPT_SEARCH);
            $('#script_table').bootstrapTable('resetSearch');
        });  

        $('#cancel_edit_script').on('click',function(){
            editeditor.setValue('');
        })

        //搜索事件
        $('#searchSubmit').on('click',function(){
            let searchval = $('#searchVal').val();
            if(searchval == null || searchval == ""){
                return;
            }
            $('#script_table').bootstrapTable('refresh', {
                query: queryParams
            });
        })

        //取消新增脚本清空内容
        $('#clear_script').on('click',function(){
            clear_script();
        })
    }


    //操作监听事件
    var operateEvents = {
        //修改事件
        'click .editScript': function (e, value, row, index) {
            //获取uuid
            var script_uuid = row.script_uuid;
            //获取名字
            var script_name = row.script_name;
            //获取脚本类型
            var script_type = row.script_type;
            //获取描述
            var script_description = row.script_description;
            //获取内容
            var script_content = row.script_content;
            //直接在html中加入
            $('#edit_script_uuid').val(script_uuid);
            $('#edit_script_name').val(script_name);
            $('#edit_script_description').val(script_description);
            $('#edit_script_type').val(script_type);
            editeditor.setValue(script_content);
            $('#editScriptmodal').modal({'width':'800px', 'height':'512px'});
        },
        //删除事件
        'click .deleteScript': function (e, value, row, index) {
            bootbox.confirm({
                title: LANG.WEB_SCRIPT_DELETE_SCRIPT,
                message: LANG.WEB_SCRIPT_DELETE_CONFIRM,
                buttons: {
                    confirm: {
                        label: LANG.UI_PUBLIC_CONFIRM,
                        className: 'btn-primary'
                    },
                    cancel: {
                        label: LANG.UI_PUBLIC_CANCEL,
                        className: 'btn-default'
                    }
                },
                callback: debounce(function (result) {
                    if (result) {
                        //获取uuid
                        var dataList = {};
                        dataList.script_uuid = [];
                        dataList.script_uuid.push(row.script_uuid);
                        // 发送后端
                        var requestAddScript = function(d){
                            if(d.success){
                                //删除成功
                                UIToastr.showSuccess(LANG.WEB_SCRIPT_DELETE_SCRIPT,d.message);
                                $('#script_table').bootstrapTable('refresh', {
                                    query: queryParams
                                });
                            }else{
                                UIToastr.showInfo(LANG.WEB_SCRIPT_DELETE_SCRIPT,d.message);
                            }
                        }
                        pAjaxRequest(dataList, "/api/v1/scripts", "DELETE", requestAddScript,false);
                    } else {
                        // 用户点击了“取消”按钮
                        // 这里可以添加取消后的处理逻辑，如果不需要处理，可以留空
                    }
                },300)
            });


           
        },

        //下载脚本
        'click .downloadScript': function (e, value, row, index) {
            //获取脚本类型
            var script_type = row.script_type;
            //根据脚本类型获取后缀名
            var suffix = Script_Type[script_type];
            //获取脚本内容
            var script_content = row.script_content;
            //下载导出文件
             // 创建 Blob 对象
            var blob = new Blob([script_content], { type: 'text/plain' });

            // 创建一个 URL 对象
            var url = URL.createObjectURL(blob);

            // 创建一个 <a> 元素
            var a = document.createElement('a');
            a.href = url;
            a.download = 'script' + suffix; // 设置下载的文件名

            // 将 <a> 元素添加到 DOM 中
            document.body.appendChild(a);

            // 触发点击事件
            a.click();

            // 移除 <a> 元素
            document.body.removeChild(a);

            // 释放 URL 对象
            URL.revokeObjectURL(url);





        },





    };

    return{
        init: function () {
            initDataTable();
            initListeners();
            
        }
    };
}();
jQuery(document).ready(function () {
    Scripts_manager.init();
});