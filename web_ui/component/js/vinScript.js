


// 定义插件
(function($) {
    // 提前引入插件
    // <script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>

    // <script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>


    //参数定义
    // {
    //     class: config.class, //需要初始化的class,必须值
    //     script_details: [
    //         {
    //             "script_method": "0", //脚本执行方式
    //             "script_type": 0,   //脚本类型
    //             "script_uuid": "", //脚本库的uuid,这个是如果获取脚本库的文件的话 这是脚本库里面的脚本uuid
    //             "script_content": "", //脚本内容
    //             "script_name":"", //脚本名称
    //             "exec_interval": 30,  //脚本执行间隔.可不传该键
    //             "trigger_fail_num": 1, //脚本执行次数, 可不传该键
    //         },
    //     ],
    //     include_list:[1,2,3,4,5,6,7,8,9], //可以支持的脚本类型,默认全部支持9个类型
    //     以下内容都接收display:none和display:block两个值,用于那个行是否显示以下是默认值,为空表示使用css自己的属性
    //     display_tab:"",  
    //     display_script_method:"",
    //     display_script_type:"",
    //     display_script_content:"",
    //     display_script_list:"",
    //     display_script_name:"",
    //     display_exec_interval:"display:none",
    //     display_trigger_fail_num:"display:none",
        

    //     //以下不需要传入键,插件内部使用的
    //     include_list_suffix: [],
    //     include_list_display:[],
    // }

    // 获取数据
    // var config = $('#testDom').getVinScript(yourClassName);
    


     // 闭包内的私有变量，用于存储配置
     var privateConfig = {
     };
     // ace实例化
     var aceEditor = {};


    $.fn.initVinScript = function(config) {
        // var allowTabSwitch = false; //提供一个检测是否保存成功的检测
        // 验证class是否已提供
        if (!config || !config.class) {
            console.error('initVinScript requires a class to be specified.');
            return this;
        }
        //初始值包含所有
        var init_include_list = [1,2,3,4,5,6,7,8,9];
        var init_include_list_suffix = [".sh",".bat",".yaml",".py",".py",".sql",".ps1",".psm1",".psd1"];
        // 设置默认配置
        var settings = $.extend({
            class: config.class, //需要初始化的class
            script_details: [
                {
                    "script_method": "0",
                    "script_type": 0,
                    "script_uuid": "",
                    "script_content": "",
                    "script_name":"",
                    "exec_interval": 30,
                    "trigger_fail_num": 1,
                },
            ],
            include_list:[1,2,4,5,7],
            display_tab:"",
            display_script_method:"",
            display_script_type:"",
            display_script_content:"",
            display_script_list:"",
            display_script_name:"",
            display_exec_interval:"display:none",
            display_trigger_fail_num:"display:none",
            

            include_list_suffix: [],
            include_list_display:[],
        }, config);

       //重新封装include_list,如果1包含在include_list中则把1的位置改为"display:none",否则为""
        var scriptInclude = function(include_list){
            //初始化所有类型的枚举和上面对应
            var init_script_list_map = Array.from(init_include_list);

            for(var i=0;i<init_include_list.length;i++){
                 if(include_list.indexOf(init_include_list[i]) != -1){
                    init_script_list_map[i] = "";
                 }else{
                    init_script_list_map[i] = "display:none";
                 }
             }
             return init_script_list_map;
        }

        //初始化p排除值
        settings.include_list_display = scriptInclude(settings.include_list);
       var scriptIncludeSuffix = function(include_list){
        //初始化所有类型的枚举和上面对应
        var init_include_list_suffix_map = Array.from(init_include_list_suffix);
        for(var i=0;i<init_include_list.length;i++){
            if(include_list.indexOf(init_include_list[i]) != -1){
                
            }else{
                //删除指定元素
                //获取元素名称
                var this_suffix = init_include_list_suffix[i];
                var index = init_include_list_suffix_map.indexOf(this_suffix);  
                if (index !== -1) {  
                    init_include_list_suffix_map.splice(index, 1);  
                } 
            }
        }
        return init_include_list_suffix_map;
    }
        //初始化p排除值
    settings.include_list_suffix = scriptIncludeSuffix(settings.include_list);
        



       


        //将script_details转换成我要使用的数据格式
        var scriptDetailsToArray = function(scriptDetails) {
            if(scriptDetails == [
                
            ] ) return;
            var result = {};
            for (var i = 0; i < scriptDetails.length; i++) {
                var scriptDetail = scriptDetails[i];
                //获取每个key值
                var keyName = "script_tab_"+i;
                result[keyName] = scriptDetail;
            }
            return result;
        }
        //初始化返回值框架
        privateConfig[settings.class] = scriptDetailsToArray(settings.script_details);

//--------------------------

        //初始化脚本库
        var initScriptManager = function() {
             // 发送后端
             var requestScript = function(d){
                if(d.success){
                   let script_list = d.data;
                   script_list = script_list.rows;
                   // 将script_list赋值为其rows属性
                    // 如果script_list为空，添加一个默认的请选择的选项
                    if(script_list.length === 0) {
                        let defaultOption = $('<option>', {
                            value: 0,
                            text: LANG.UI_JOB_SELECT
                        });
                        $("#"+settings.class+'_script_list').append(defaultOption);
                    } else {
                        // 否则，先添加一个默认的请选择的选项，然后再添加其他的选项
                        let defaultOption = $('<option>', {
                            value: 0,
                            text: LANG.UI_JOB_SELECT
                        });
                        $("#"+settings.class+'_script_list').append(defaultOption);

                        $.each(script_list, function(i, item) {
                            let option = $('<option>', {
                                value: item.script_uuid,
                                text: item.script_name
                            });
                            $("#"+settings.class+'_script_list').append(option);
                        });

                        //初始化脚本库
                         $("#"+settings.class+'_script_list').val(privateConfig[settings.class]['script_tab_0'].script_uuid ?? 0);
                    }
                  
                }else{
                    UIToastr.showInfo(LANG.UI_SCRIPT_GET_SCRIPT,d.message);
                }
            }
            pAjaxRequest({}, "/api/v1/scripts", "get", requestScript,false);
        };

        //初始化部分监听事件
        var initListeners = function() {
            // 监听脚本类型变化
            $("#"+settings.class+'_script_method').change(function() {
                //当切换脚本源的时候，清空脚本类型
                $("#"+settings.class+'_script_type').val(0);
                //清空脚本库
                $("#"+settings.class+'_script_list').val(0);
                //清空脚本内容
                aceEditor[settings.class].setValue("");
                if(this.value == 1){
                    $("."+settings.class+'_script_list_div').show();
                    $("."+settings.class+'_script_type_div').hide();
                }else{
                    $("."+settings.class+'_script_list_div').hide();
                    $("."+settings.class+'_script_type_div').show();
                }
            });


            //监听脚本库的变化
            $("#"+settings.class+'_script_list').change(function() {
                if(this.value != 0){
                    //获取脚本内容
                    let script_uuid = this.value;
                    //获取已经选中的lable
                    let clickedValue =$('label[name="'+settings.class+'_script_tab"]').filter('.active').attr('value');
                    let requestScript = function(d){
                        if(d.success){
                            let script_content = d.data.script_content;
                            aceEditor[settings.class].setValue(script_content);
                            //给定返回值的脚本类型
                            if( privateConfig[settings.class][clickedValue] == undefined){
                                privateConfig[settings.class][clickedValue] = {};
                            }
                            //设置脚本名称
                            $("#"+settings.class+'_script_name').val(d.data.script_name);
                            privateConfig[settings.class][clickedValue]['script_type'] = parseInt(d.data.script_type);
                            privateConfig[settings.class][clickedValue]['script_name'] = parseInt(d.data.script_name);
                        }else{
                            UIToastr.showInfo(LANG.UI_SCRIPT_GET_SCRIPT,d.message);
                        }
                    }
                    pAjaxRequest({'scripts_uuid':script_uuid}, "/api/v1/scripts", "get", requestScript,true);
                }
            });

            //监听脚本类型变化
            $("#"+settings.class+'_script_type').change(function(){
                //获取已经选中的lable
                let clickedValue =$('label[name="'+settings.class+'_script_tab"]').filter('.active').attr('value');
                if(privateConfig[settings.class][clickedValue] == undefined){
                    privateConfig[settings.class][clickedValue] = {};
                }
                privateConfig[settings.class][clickedValue].script_type = parseInt(this.value);
            });

            //实时监测脚本名称
            $("#"+settings.class+'_script_name').bind('input propertychange', function() { 
                //获取脚本名称
                let script_name = $(this).val();
                 //脚本名称规则,不能有特殊字符,长度不能超过128个字符-------------
                 let script_name_reg = /^[^\s\\\/:*?"<>|]{1,128}$/;
                //对名称进行正则匹配
                if(!script_name_reg.test(script_name)){
                    UIToastr.showWarning(LANG.WEB_SCRIPT_SCRIPT_NAME,LANG.UI_SCRIPT_NAME_VALID);
                }
            });


        };


        //初始化脚本框架 ,该初始化依赖于ace editor 必须在使用该插件之前引入进去
        var initScriptContent = function() {
             //初始化添加脚本插件
             aceEditor[settings.class] = ace.edit(settings.class+'_script_content');
             aceEditor[settings.class].session.setUseWrapMode(true);
             aceEditor[settings.class].setShowPrintMargin(false);
            //初始化脚本框架成功后检测是否有内容以及类型
            //初始化内容
            aceEditor[settings.class].setValue(privateConfig[settings.class]['script_tab_0'].script_content ?? "");
            //初始化脚本源
            $("#"+settings.class+'_script_method').val(parseInt(privateConfig[settings.class]['script_tab_0'].script_method));
            $("#"+settings.class+'_script_name').val(privateConfig[settings.class]['script_tab_0'].script_name);
            //切换脚本库
            if(parseInt(privateConfig[settings.class]['script_tab_0'].script_method) == 1){
                    $("."+settings.class+'_script_list_div').show();
                    $("."+settings.class+'_script_type_div').hide();
            }else{
                    $("."+settings.class+'_script_list_div').hide();
                    $("."+settings.class+'_script_type_div').show();
            }
            //初始化脚本类型
            $("#"+settings.class+'_script_type').val(parseInt(privateConfig[settings.class]['script_tab_0'].script_type));
            

            
            //初始化label,遍历privateConfig内容,key值作为label内容初始化
            var scriptDiv =   $("."+settings.class+'_script_tab');
            for(let key in privateConfig[settings.class]){
                //获取数字
                var lastNum = key.split('_tab_')[1];
                if(lastNum == 0) continue;
                //在底部添加一个tab
                var tab = `<label class="radio-group__item me-20" name="${settings.class}_script_tab" 
                value="script_tab_${lastNum}"><span>${LANG.UI_SCRIPT_SCRIPT}-${lastNum}</span><i class="viconfont vicon-a-Close-oneguanbi" title="${LANG.UI_PUBLIC_OFF}" 
                style="position: absolute;top: -8px;right: -9px;color: #AAB3C0;background-color: white;border-radius: 50%;"></i></label>`;
                scriptDiv.append(tab);
            }
          
          
        }


        var initDomTree = function() {
            //把include_list_suffix数组抓换成字符串,逗号隔开
            var suffix = settings.include_list_suffix.join(",");


            let htmlDom = `
                <div class="form_div" style="margin-top: 10px;${settings.display_tab}">
                    <!-- 添加脚本 -->
                    <label class="script_form_title">${LANG.UI_SCRIPT_MULTIPLE_SCRIPTS}
                    </label>
                    <div class="script_form_content">
                        <div class="radio-group ${settings.class}_script_tab">
                            <label class="radio-group__item me-20  active" name="${settings.class}_script_tab" value="script_tab_0"><span class="radio-group__item_text">${LANG.UI_SCRIPT_SCRIPT}-0<span></label>
                            <label class="radio-group__item_button me-20 " id="${settings.class}_add_tab" 
                                value="script_tab_1000"><i class="viconfont vicon-zhediekuanganniu1" 
                                style="position: relative;top: -1px;left: -7px;color: #AAB3C0;background-color: white;border-radius: 50%;"></i><span>${LANG.UI_SCRIPT_ADD_SCRIPT}</span></label>
                        </div>
                    </div>
                </div>
                <div class="form_div" style="${settings.display_exec_interval}">
                    <label class="script_form_title">${LANG.UI_SCRIPT_EXECUTION_INTERVAL}</label>
                    <div class="script_form_content">
                        <div id="${settings.class}_exec_interval_spinner">
                            <div class="input-group spinner-group">
                                <input type="text" id="${settings.class}_exec_interval" style="text-align: left;" class="input-sm spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="input-sm btn spinner-up default">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="input-sm btn spinner-down default">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form_div" style="${settings.display_trigger_fail_num}">
                    <label class="script_form_title">${LANG.UI_SCRIPT_CONSECUTIVE_FAILURES}</label>
                    <div class="script_form_content">
                        <div id="${settings.class}_trigger_fail_num_spinner">
                            <div class="input-group spinner-group">
                                <input type="text" id="${settings.class}_trigger_fail_num" style="text-align: left;" class="input-sm spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="input-sm btn spinner-up default">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="input-sm btn spinner-down default">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form_div" style="${settings.display_script_method}">
                    <!-- 脚本名称 -->
                    <label class="script_form_title">${LANG.UI_SCRIPT_SCRIPT_SOURCE}
                    </label>
                    <div class="script_form_content">
                        <select class="form-control select2me" id="${settings.class}_script_method" style="width: 100%;">
                                <option value="0">${LANG.UI_SCRIPT_MANUAL_INPUT}</option>
                                <option value="1">${LANG.UI_SCRIPT_SELECT_SCRIPT}</option>
                        </select>
                    </div>
                </div>

                




                <div class="form_div ${settings.class}_script_type_div" style="${settings.display_script_type}">
                    <!-- 脚本名称 -->
                    <label class="script_form_title">${LANG.UI_SCRIPT_SCRIPT_TYPE}
                    </label>
                    <div class="script_form_content">
                        <select class="form-control select2me" id="${settings.class}_script_type" style="width: 100%;">
                        <option value="0">${LANG.UI_JOB_SELECT}</option>
                            <option value="1" style="${settings.include_list_display[0]}">shell ${LANG.UI_SCRIPT_SCRIPT}(.sh)</option>
                            <option value="2" style="${settings.include_list_display[1]}">bat ${LANG.UI_SCRIPT_SCRIPT}(.bat)</option>
                            <option value="3" style="${settings.include_list_display[2]}">yaml ${LANG.UI_SCRIPT_SCRIPT}(.yaml)</option>
                            <option value="4" style="${settings.include_list_display[3]}">python2 ${LANG.UI_SCRIPT_SCRIPT}(.py)</option>
                            <option value="5" style="${settings.include_list_display[4]}">python3 ${LANG.UI_SCRIPT_SCRIPT}(.py)</option>
                            <option value="6" style="${settings.include_list_display[5]}">SQL ${LANG.UI_SCRIPT_SCRIPT}(.sql)</option>
                            <option value="7" style="${settings.include_list_display[6]}">PowerShell ${LANG.UI_SCRIPT_SCRIPT}(.ps1)</option>
                            <option value="8" style="${settings.include_list_display[7]}">PowerShell ${LANG.UI_SCRIPT_SCRIPT}(.psm1)</option>
                            <option value="9" style="${settings.include_list_display[8]}">PowerShell ${LANG.UI_SCRIPT_SCRIPT}(.psd1)</option>
                        </select>
                    </div>
                </div>

                <div class="form_div ${settings.class}_script_list_div"  style="display: none;">
                    <!-- 脚本库名称 -->
                    <label class="script_form_title">${LANG.UI_SCRIPT_SCRIPT_LIBRARY}
                    </label>
                    <div class="script_form_content">
                        <select class="form-control" id="${settings.class}_script_list" style="width: 100%;">
                            
                                   
                        </select>
                    </div>
                </div>

                <div class="form_div" style="${settings.display_script_name}">
                    <!-- 脚本名称 -->
                    <label class="script_form_title">${LANG.UI_SCRIPT_SCRIPT_NAME}
                    </label>
                    <div class="script_form_content">
                        <div class="input-icon right">
                            <input class="form-control" placeholder="${LANG.UI_SCRIPT_ENTER_SCRIPT_NAME}" id="${settings.class}_script_name" style="width: 100%;"/>
                        </div>
                    </div>
                    
                </div>

               


                <div class="form_div script_div">
                
                    <label class="script_form_title">
                    </label>
                    <div class="script_form_content">
                        <div class="portlet">
                            <div class="portlet-title line" style="border-bottom:unset;">
                                <div class="caption" style="width: 100%;color: #333333;font-weight: 400;font-size: 14px;background: #F5F5F5;padding-bottom: 10px;">
                                    <span style="margin-left:16px">${LANG.UI_SCRIPT_CONTENT}</span>
                                    <div class="tools" style="width: 20%;float: right;text-align: right;padding-right: 6px;">
                                        <a type="file" href="" id="${settings.class}_chooseFileEdit" data-original-title="${LANG.UI_SCRIPT_LOAD_LOCAL_CODE}" title="${LANG.UI_SCRIPT_LOAD_LOCAL_CODE}" >
                                            <i class="viconfont vicon-shangchuan"></i>
                                        </a>
                                        <input type="file" id="${settings.class}_hiddenFileInputEdit" accept="${suffix}" style="display: none;" />
                                        <a href="" class="viconfont vicon-a-apikeydaima" id="${settings.class}_hiddenVicon" data-original-title="${LANG.UI_SCRIPT_ZOOM}" title="${LANG.UI_SCRIPT_ZOOM}">
                                        </a>
                                    </div>
                                    
                                </div>
                                
                                <div class="" style="border: solid 1px #F5F5F5;">
                                    <!-- 脚本内容 -->
                                    <div id="${settings.class}_script_content" class="script_msg" style="height:150px;width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
                <div class="form_div" style="display: none;">
                    <button class="btn btn-light-primary me-2" style="float: right;" id="${settings.class}_save_script" type="button">${LANG.UI_SCRIPT_SAVE}</button>
                </div>
                
                `;
                $("."+settings.class).empty().append(htmlDom);
        }

        //初始化上传以及缩放监听事件
        var initZoomUpload = function() {
            // 初始化缩放事件
            $('.script_div').on('click', '.portlet > .portlet-title #'+settings.class+'_hiddenVicon', function(e) {
                e.preventDefault();
                var portlet = $(this).closest(".portlet");
                if (portlet.hasClass('portlet-fullscreen')) {
                    $(this).removeClass('on');
                    portlet.removeClass('portlet-fullscreen');
                    $("#"+settings.class+"_script_content").css({
                        'height': '150px', // 设置元素高度为窗口高度
                        'overflow-y': 'auto' // 内容超出时显示垂直滚动条
                    });
                    $('body').removeClass('page-portlet-fullscreen');
                } else {
                   
                    $(this).addClass('on');
                    var titleHeight = $('.script_div .caption').outerHeight(true)+50; // 获取窗口可视高度
                    $("#"+settings.class+"_script_content").css({
                            'height': `calc(100vh - ${titleHeight}px)`, // 设置元素高度为窗口高度
                            'overflow-y': 'auto' // 内容超出时显示垂直滚动条
                        });
                    portlet.addClass('portlet-fullscreen');
                    $('body').addClass('page-portlet-fullscreen');
                }
            });

            //初始化spinner插件
            $('#'+settings.class+'_exec_interval_spinner').spinner({value: 30, step: 1, min: 1,max: 99999});//执行间隔
            $('#'+settings.class+'_trigger_fail_num_spinner').spinner({value: 1, step: 1, min: 1,max: 999});//执行次数


            //初始化添加的文件上传
            $("#"+settings.class+'_chooseFileEdit').on('click', function(e) {
                e.preventDefault(); // Prevent default link behavior
                $("#"+settings.class+'_hiddenFileInputEdit').trigger('click'); // Trigger hidden input
            });
            $("#"+settings.class+'_hiddenFileInputEdit').on('change', function(e) {
                var file = e.target.files[0];
                if (!file) {
                    return;
                }
                // Check file type based on the accept attribute
                if (!settings.include_list_suffix.includes('.' + file.name.split('.').pop())) {
                    UIToastr.showWarning(LANG.UI_SCRIPT_ADD_SCRIPT, LANG.UI_SCRIPT_SELECT_SPECIFIED_FILE);
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e) {
                    let fileContent = e.target.result; // Store file content in variable
                    aceEditor[settings.class].setValue(fileContent);
                    
                };
                reader.readAsText(file);
            });


           function checkThisData(script_name,scriptType, scriptContent, clickedValue){
                let result = {
                    "title" : '',
                    "check"  : true,
                };
                //脚本类型不能为空---------------
                if(scriptType == 0 || scriptType == null || scriptType == undefined||  scriptType == ""){
                    result.check = false;
                    result.title = LANG.UI_SCRIPT_TYPE_IS_NULL;
                    return result;
                }
                //脚本名称不能为空----------------
                if(script_name == ''){
                    result.title  = LANG.UI_SCRIPT_NAME_IS_NULL;
                    result.check  = false;
                    return result;
                }
                //脚本内容不能为空-----------------
                if(scriptContent == ''){
                    result.title  = LANG.UI_SCRIPT_CONTENT_IS_NULL;
                    result.check  = false;
                    return result;
                }
                //脚本名称规则,不能有特殊字符,长度不能超过128个字符-------------
                let script_name_reg = /^[^\s\\\/:*?"<>|]{1,128}$/;
                // let script_name_reg = /^[a-zA-Z0-9_]{1,128}$/;
                if(!script_name_reg.test(script_name)){
                    result.title  = LANG.UI_SCRIPT_NAME_VALID;
                    result.check  = false;
                    return result;
                }
                //脚本名称不能重复
                var scriptDiv =   $("."+settings.class+'_script_tab');
                var tabs = scriptDiv.find('label[name="'+settings.class+'_script_tab"]');
                //获取已保存的对象
                var AllData = privateConfig[settings.class];
                //循环获取所有的script_name
                //判断AllData是否是一个对象并且长度不为0
                if (typeof AllData === 'object' && AllData !== null && Object.keys(AllData).length > 0) {
                     //如果是对象并且有值
                     //循环该对象
                     for (var key in AllData) {
                        //获取每一个对象
                        var item = AllData[key];
                        if(key == clickedValue){
                            continue;
                        }
                        //获取当前对象的名称
                        var script_name_save_each = item.script_name;
                        if(script_name == script_name_save_each){
                            result.title  = LANG.UI_SCRIPT_NAME_IS_EXIST;
                            result.check  = false;
                            return result;
                        }
                     }
                } else {
                    //如果不是一个对象则不验证
                }
                
               
                return result;
           } 
           

            //初始化保存事件
            $("#"+settings.class+'_save_script').on('click', function(e) {
                Metronic.blockUI({target:$("."+settings.class),animate: true});
                //获取已经选中的lable
                let $label = $('label[name="'+settings.class+'_script_tab"]').filter('.active');
                let clickedValue = $label.attr('value');
                //获取脚本方式
                let scriptmethod = $("#"+settings.class+'_script_method').val();
                //获取名称
                let script_name = $("#"+settings.class+'_script_name').val();
                //获取间隔
                let exec_interval = $("#"+settings.class+'_exec_interval').val();
                //获取次数
                let trigger_fail_num = $("#"+settings.class+'_trigger_fail_num').val();
                //获取当前脚本类型
                let  scriptType = 0;
                if(scriptmethod == 0){
                    scriptType = $("#"+settings.class+'_script_type').val();
                }else{
                    if(privateConfig[settings.class][clickedValue] == undefined){
                        scriptType = 0;
                    }else{
                        scriptType = privateConfig[settings.class][clickedValue]['script_type']
                    }
                    
                }
                script_uuid = $("#"+settings.class+'_script_list').val();
                //获取当前脚本内容
                let scriptContent = aceEditor[settings.class].getValue();
                Metronic.unblockUI("."+settings.class);
               
                
                let resultCheck = checkThisData(script_name,scriptType, scriptContent, clickedValue);
                if(!resultCheck['check']){
                    //如果有save则去掉
                    if($label.find('.save_tab').length != 0) {
                        $label.find('.save_tab').remove();
                    }
                    
                    if($label.find('.error_tab').length == 0) {
                        $label.append('<i class="viconfont vicon-a-Check-onexiaoyan-1 error_tab"></i>');
                    }
                    //更新title内容
                    $label.find('.error_tab').attr('title',resultCheck['title']);
                }else{
                    if(script_name != ""){
                        //名字不为空的时候
                        //获取$label下面的span标签
                        let $span = $label.find('span.radio-group__item_text');
                        //把span标签内容设置为该name
                        $span.text(script_name);
                        //添加title
                        $label.attr('title', script_name);
                    }
                    
                    //给$label里面添加一个i标签
                    //先检查下面是否有class为vicon-Frame-15的i标签
                    if($label.find('.save_tab').length == 0) {
                        if($label.find('.error_tab').length != 0){
                            //移除该对象
                            $label.find('.error_tab').remove();
                        }
                        $label.append('<i class="viconfont vicon-a-Check-onexiaoyan1 save_tab"></i>');
                    }

                }

               
                

                
               



              
                
                
                privateConfig[settings.class][clickedValue] = {
                    'script_method': scriptmethod,
                    'script_type' : scriptType,
                    'script_uuid' : script_uuid,
                    'script_content': scriptContent,
                    'script_name': script_name,
                    'exec_interval': exec_interval,
                    'trigger_fail_num': trigger_fail_num
                }
                // allowTabSwitch = true;
               
                // UIToastr.showSuccess("保存脚本", "保存成功");
            });
            //初始化添加脚本事件
            $("#"+settings.class+'_add_tab').on('click', function(e) {
                //初始化一次保存事件
                $("#"+settings.class+'_save_script').trigger('click');
                var scriptDiv =   $("."+settings.class+'_script_tab');
                //获取scriptDiv下面的所有tab
                var tabs = scriptDiv.find('label[name="'+settings.class+'_script_tab"]');
                //获取最后一个tabs的value
                var lastValue = tabs.last().attr('value');

               
                //如果检测通过了才可以添加脚本
                let checkAllSave = true; //检测所有的tab是否已经保存成功
                tabs.each(function(index,element){
                    //获取当前tab的对象
                    var thisTab = $(element);
                    //获取当前对象下是否含有i标签
                    if(thisTab.find('.save_tab').length > 0){
                        //如果含有i标签
                    }else{
                        if(thisTab.find('.save_tab').length == 0 && thisTab.find('.error_tab').length == 0){
                            thisTab.append('<i title="'+LANG.UI_SCRIPT_IS_NOT_COMPLETE+'" class="viconfont vicon-a-Check-onexiaoyan-1 error_tab"></i>');
                        }
                        //如果不含有
                        checkAllSave = false;
                        return false;
                    }
                })




                if(checkAllSave){
                    var lastNum = lastValue.split('_tab_')[1];
                        //获取下一个tab的value
                        var nextValue = parseInt(lastNum)+1;
                        //在底部添加一个tab
                        var tab = `<label class="radio-group__item me-20" name="${settings.class}_script_tab" 
                        value="script_tab_${nextValue}"><span class="radio-group__item_text">${LANG.UI_SCRIPT_SCRIPT}-${nextValue}</span><i class="viconfont vicon-a-Close-oneguanbi" title="${LANG.UI_PUBLIC_OFF}" 
                        style="position: absolute;top: -8px;right: -9px;color: #AAB3C0;background-color: white;border-radius: 50%;"></i></label>`;
                        //在当前点击对象的前一个增加一个tab
                        $(this).before(tab);
                }else{
                    //查找html中所有的error_tab
                        var errorTabs = scriptDiv.find('.error_tab').attr('title');
                        console.log(errorTabs);
                        UIToastr.showWarning(LANG.UI_SCRIPT_ADD_SCRIPT, errorTabs);
                }

               
               
            });

            //选择每个_script_tab下面的i标签点击事件
            $("."+settings.class+'_script_tab').on('click', 'i.vicon-a-Close-oneguanbi', function(e) {
                e.stopPropagation(); // 阻止事件冒泡
                //获取当前点击的i标签的父标签的value
                var value = $(this).parent().attr('value');
                //获取当前label是否是激活active状态
                var activeValue = $(this).parent().hasClass('active');
                //删除value对应的tab
                $(this).parent().remove();
                //同时删除对应的数据
                delete privateConfig[settings.class][value];
                //如果active存在则把最后一个label设置为active状态
                if(activeValue){
                    //获取最后一个label
                    var lastLabel =$('label[name="'+settings.class+'_script_tab"]').last();
                    //设置最后一个label为active状态
                    lastLabel.addClass('active');
                    //触发最后一个label的点击事件
                    // lastLabel.trigger('click');

                    //获取当前点击的i标签的父标签的value
                    var value = lastLabel.attr('value');
                    //获取当前点击的i标签的父标签的name
                    // var name = $(this).attr('name');
                    //获取已存储的数据
                    var data = privateConfig[settings.class][value];
                    if(data == undefined || data == null || data == '' || data == []){
                        //没有找到数据的时候则初始化所有内容
                        $("."+settings.class+'_script_list_div').hide();
                        $("."+settings.class+'_script_type_div').show();
                        //将脚本源初始化为手动输入
                        $("#"+settings.class+'_script_method').val(0);
                        //将脚本类型初始化为请选择
                        $("#"+settings.class+'_script_type').val(0);
                        //填写脚本名称
                        $("#"+settings.class+'_script_name').val("");
                        //间隔
                        $("#"+settings.class+'_exec_interval_spinner').spinner('value', 30);
                        //次数
                        $("#"+settings.class+'_trigger_fail_num_spinner').spinner('value',1);
                        //将脚本内容初始为空字符串
                        aceEditor[settings.class].setValue('');
                        return;
                    }
                    //如果有数据则初始化内容
                    //填写脚本源
                    $("#"+settings.class+'_script_method').val(data['script_method'] ?? 0);
                    if(data['script_method'] == 1){
                        $("."+settings.class+'_script_list_div').show();
                        $("."+settings.class+'_script_type_div').hide();
                    }else{
                        $("."+settings.class+'_script_list_div').hide();
                        $("."+settings.class+'_script_type_div').show();
                    }
                    //填写脚本类型
                    $("#"+settings.class+'_script_type').val(data['script_type'] ?? 0);
                    //填写脚本名称
                    $("#"+settings.class+'_script_name').val(data['script_name'] ?? "");
                    //填写脚本内容
                    aceEditor[settings.class].setValue(data['script_content'] ?? "");
                    //间隔
                    $("#"+settings.class+'_exec_interval_spinner').spinner('value', parseInt(data['exec_interval'])?? 30);
                    //次数
                    $("#"+settings.class+'_trigger_fail_num_spinner').spinner('value', parseInt(data['trigger_fail_num'])?? 1);





                }
                

                
            })

             //选择每个tab点击事件
             $("."+settings.class+'_script_tab').on('click','label', function(e) {
                //这里判断如果点击的当前value值为"script_tab_1000", 则不执行后续内容
                if($(this).attr('value') == "script_tab_1000"){
                    return;
                }
                //触发保存事件
                //触发保存按钮事件
                $("#"+settings.class+'_save_script').trigger('click');
                //获取当前点击的i标签的父标签的value
                var value = $(this).attr('value');
                //获取当前点击的i标签的父标签的name
                var name = $(this).attr('name');
                //获取已存储的数据
                var data = privateConfig[settings.class][value];
                if(data == undefined || data == null || data == '' || data == []){
                    //没有找到数据的时候则初始化所有内容
                    $("."+settings.class+'_script_list_div').hide();
                    $("."+settings.class+'_script_type_div').show();
                    //将脚本源初始化为手动输入
                    $("#"+settings.class+'_script_method').val(0);
                    //将脚本类型初始化为请选择
                    $("#"+settings.class+'_script_type').val(0);
                    //填写脚本名称
                    $("#"+settings.class+'_script_name').val("");
                    //间隔
                    $("#"+settings.class+'_exec_interval_spinner').spinner('value', 30);
                    //次数
                    $("#"+settings.class+'_trigger_fail_num_spinner').spinner('value',1);
                    //将脚本内容初始为空字符串
                    aceEditor[settings.class].setValue('');
                    return;
                }
                //如果有数据则初始化内容
                //填写脚本源
                $("#"+settings.class+'_script_method').val(data['script_method'] ?? 0);
                if(data['script_method'] == 1){
                    $("."+settings.class+'_script_list_div').show();
                    $("."+settings.class+'_script_type_div').hide();
                }else{
                    $("."+settings.class+'_script_list_div').hide();
                    $("."+settings.class+'_script_type_div').show();
                }
                //填写脚本类型
                $("#"+settings.class+'_script_type').val(data['script_type'] ?? 0);
                //填写脚本名称
                $("#"+settings.class+'_script_name').val(data['script_name'] ?? "");
                //填写脚本内容
                aceEditor[settings.class].setValue(data['script_content'] ?? "");
                //间隔
                $("#"+settings.class+'_exec_interval_spinner').spinner('value', parseInt(data['exec_interval'])?? 30);
                //次数
                $("#"+settings.class+'_trigger_fail_num_spinner').spinner('value', parseInt(data['trigger_fail_num'])?? 1);



            })


            /**
             * 初始化按钮样式的单选框组
             */
            const initRadioButtons = () => {
                // const LEFT_AND_RIGHT_PADDING_WIDTH = 40; // 左右总padding宽度
                // const LEFT_AND_RIGHT_BORDER_WIDTH = 2; // 左右总border宽度
                const className = settings.class + '_script_tab';
                let selector = `.radio-group[class*="${className}"]`;
                let radioButtonElList = [].slice.call(document.querySelectorAll(selector));
                // let radioButtonElList = [].slice.call(document.querySelectorAll('.radio-group'+"."+settings.class+"_script_tab"));

                radioButtonElList.forEach(el => {
                   
                    $(el).on('click', '.radio-group__item', function(e) {
                        let oldValue = $(el).find('.radio-group__item.active').attr("value");
                        // 触发 change 事件
                        let newValue = $(this).attr("value");
                       
                        
                        if (oldValue !== newValue) {
                             // 移除上一个的active样式
                            $(el).find('.radio-group__item.active').removeClass('active');
                            $(el).trigger('change', [oldValue, newValue]);
                        }

                        let radioButtonItemList = [].slice.call(el.querySelectorAll('.radio-group__item'));

                        radioButtonItemList.forEach(i => {
                            let value = $(i).attr("value");
                            let currentValue = $(e.target).closest('[value]').attr('value');

                            if (value === currentValue) {
                                $(i).addClass('active');
                            }
                        });
                    });
                })
            };
            initRadioButtons();

        };









        var initfunction = function() {
            //初始化基本DOM元素
            initDomTree();
            //初始化上传以及缩放监听事件
            initZoomUpload();
            //初始化脚本框架及内容
            initScriptContent();
            //初始化部分监听事件
            initListeners();
            //初始化脚本库选择框
            initScriptManager();
            
            
            
        };

        
        return initfunction();
    };

    // 获取配置或基于配置的结果
    $.fn.getVinScript = function(className) {
        //获取脚本类型
        if(privateConfig[className] == undefined){
            return {};
        }
        //触发一次保存事件
        $("#"+className+'_save_script').trigger('click');
        //检测
         //获取scriptDiv下面的所有tab
         var scriptDiv =   $("."+className+'_script_tab');
         var tabs = scriptDiv.find('label[name="'+className+'_script_tab"]');
          //获取名称
        let scriptName = $("#"+className+'_script_name').val();
        let scriptContent = aceEditor[className].getValue()
           //如果检测通过了才可以添加脚本
        let checkAllSave = true; //检测所有的tab是否已经保存成功
         if(tabs.length == 1 &&  tabs.first().attr('value') == 'script_tab_0' && (scriptName == '' && scriptContent == '')){
            //如果只有初始化的一个并且名称为空则不做检测
            //删除当前tabs下的错误
            $(tabs[0]).find('.error_tab').remove();
         }else{
            
            tabs.each(function(index,element){
                //获取当前tab的对象
                var thisTab = $(element);
                //获取当前对象下是否含有i标签
                if(thisTab.find('.save_tab').length > 0){
                    //如果含有i标签
                }else{
                    if(thisTab.find('.save_tab').length == 0 && thisTab.find('.error_tab').length == 0){
                        thisTab.append('<i title="'+LANG.UI_SCRIPT_IS_NOT_COMPLETE+'" class="viconfont vicon-a-Check-onexiaoyan-1 error_tab"></i>');
                    }
                    //如果不含有
                    checkAllSave = false;
                    return false;
                }
            })

         }
        
         if(!checkAllSave){
            var errorTabs = $('.error_tab').attr('title');
            console.log(errorTabs);
            UIToastr.showWarning(LANG.UI_SCRIPT_ADD_SCRIPT, errorTabs);
            return false;
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
                if(script_content == "" || script_content == null){
                    return foundKeywords;
                }

                // 遍历关键词列表，检查是否出现在输入的SQL字符串中
                highRiskKeywords.forEach(keyword => {
                    if (script_content.toLowerCase().includes(keyword.toLowerCase())) {
                        foundKeywords.push(keyword);
                    }
                });

                // 返回发现的所有高危关键词
                return foundKeywords;

            }

        let result = [];
        let script_result = privateConfig[className];
        let script_content_words = [];
        for(let key in script_result){
            result.push(script_result[key]);
            script_content_words.push.apply(script_content_words,checkDangerKeyword(script_result[key].script_content))
        }
    
        if(script_content_words.length > 0){
            //把数组组合成字符串
            let script_content_words_str = script_content_words.join(",");
            UIToastr.showWarning(LANG.UI_SCRIPT_ADD_SCRIPT, LANG.UI_SCRIPT_CHECK_WORD + script_content_words_str);
        }
        // 过滤掉 script_content 为空的项
        result = result.filter(item => item.script_content !== "");

        // 检查 script_name 是否有重复值
        let scriptNames = new Set();
        let duplicateNames = [];
        for (let item of result) {
            if (scriptNames.has(item.script_name)) {
                duplicateNames.push(item.script_name);
            } else {
                scriptNames.add(item.script_name);
            }
        }
        return  result;
    };
}(jQuery));