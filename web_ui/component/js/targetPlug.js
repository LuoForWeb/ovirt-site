
(function($) {
    // 提前在html页面引入

    // <script type="text/javascript" src="/assets/global/plugins/select2/select2-4.1.0/js/select2.min.js"></script>

    // <link href="/assets/global/plugins/select2/select2-4.1.0/css/select2.min.css" rel="stylesheet" type="text/css"/>

    // <link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />

    // <script type="text/javascript" src="./scripts/platform/component/targetPlug.js"></script>


    //插件参数定义
    // 入参定义 = {
    //     以下的值如果不传就是给的该默认值,有些值如果和默认值相同可以不传那个键名
    //     type:0   //必须值 1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管
    //     origin_task_uuid: "", //源任务uuid(实时,定时可不传或为空)
    //     origin_agent_uuid: "", //源代理主机uuid(实时,定时可不传或为空)
    //     origin_timepoint_uuid: "", //源时间点uuid或时间点(实时,定时)
    //     target_agent_uuid: "", //目标代理主机uuid(目标端主机)
    //     target_visible_flag: false,  //控制恢复目标的列是否显示 true显示,false不显示
    //     recover_type: true, //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
    //     target_auto_flag: false, //是否显示恢复目标选择框的自动分配选项
    //     target_input_flag:false, //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
    //     target_input_min: -1, //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
    //     origin_data_array:[], //前端源信息,当origin_data_flag为true时使用这个数据
    //     nocheck: false, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
    //     forced_relationship: true, //是否强制关联勾选.默认为强制关联勾选
    //     init_nochecked:['5b431cb4-470d-b6b4-50c0-75a065449e03','3a05f8eb-4765-f653-10ab-2593964da393'], //修改使用,不适用可以不传这个键,初始化不勾选的节点,dev_node_uuid的集合
    //     init_selected:{ //修改使用,不使用可以不传这个键,初始化勾选的节点右边选中的值
    //                 "b65e05ab-b261-45b4-137e-9161ab549a0f": {//源左边节点的dev_node_uuid
    //                     "selected_type": "selected", //类型分为selected和manual, selected为选中的项的value值, manual为自定义输入
    //                     "selected_value": "b65e05ab-b261-45b4-137e-9161ab549a0f" //selected为option的value值,manual为自定义输入的值
    //                     },
    //                 }
    //     loading:'', //为加载的动画,传入".loadClass"或者"#loadId",默认为空值,不传不会有加载动画
    //     selected_disabled: false, //是否禁用选择框,true为禁用,false为不禁用
    //     first_column_name:"源磁盘名", //第一列名称
    //     second_column_name:"大小", //第二列名称
    //     third_column_name:"恢复目标", //第三列名称
    // };


    // 使用ztree插件的方法获取所有数据或者已勾选数据
    // 其中tree的id的值如果origin_timepoint_uuid不为空则id为:origin_timepoint_uuid +"_tableId"
    // 如果origin_timepoint_uuid为空则使用target_agent_uuid,其中id为origin_agent_uuid +"_tableId"
    // 自由获取整棵树
    // var treeObj = $.fn.zTree.getZTreeObj("你的treeid");
    // console.log(treeObj.getNodes());
    // console.log(treeObj.transformToArray(treeObj.getNodes()))


    // 获取已组装数据
    // 1.	获取所有数据的集合
    // var hhh = $("#testDiv").getTargetData('getAllData');
    //         console.log(hhh);
    // 2.	获取单个的数据
    // 其中第二个参数(例如1234)表示你的origin_timepoint_uuid或者origin_agent_uuid
    // 其中如果有origin_timepoint_uuid则只传入origin_timepoint_uuid的值,没有则传入origin_agent_uuid的值
    // var hhh = $("#testDiv").getTargetData('getEachData',"1234");
    //         console.log(hhh);







    $.fn.initTargetPlug = function(config) {
         //检查传参合规性
        //  if(config.origin_timepoint_uuid == undefined || config.origin_timepoint_uuid == null || config.origin_timepoint_uuid == ""){
        //      console.error('The time point cannot be empty.');
        //      return;
        //  }
        //  if(config.target_agent_uuid == undefined || config.target_agent_uuid == null || config.target_agent_uuid == ""){
        //      console.error('The target agent host cannot be empty.');
        //      return;
        //  }
          // 设置默认配置
          var settings = $.extend({
            type: 0, //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管,7实时回切
            origin_task_uuid: "", //源任务uuid(实时,定时可不传或为空)
            origin_agent_uuid: "", //源代理主机uuid(实时,定时可不传或为空)
            origin_timepoint_uuid: "", //源时间点uuid或时间点(实时,定时)
            target_agent_uuid: "", //目标代理主机uuid(目标端主机)
            target_visible_flag: false,  //控制恢复目标的列是否显示 true显示,false不显示
            origin_os_type: "Windows", //源的操作系统类型,默认windows, 接收Windows和Linux两个值
            target_os_type: "Linux",  //目标机操作系统类型 windows还是linux, 只有type为3和6在使用,其他type可不传
            recover_type: true, //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
            target_auto_flag: false, //是否显示恢复目标选择框的自动分配选项
            target_input_flag:false, //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
            target_input_min: -1, //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
            origin_data_array:[], //前端源信息,当origin_data_flag为true时使用这个数据
            nocheck: false, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
            forced_relationship: true, //是否强制关联勾选.默认为强制关联勾选
            selected_disabled: false, //是否禁用勾选,true为禁用勾选,false为不禁用勾选
            init_nochecked:[],
            match_dev_uuid_list:[], //包含的数据,只有实时回切在用
            init_selected:{},
            loading:'',
            callback:'',
            first_column_name:LANG.UI_MACHINE_OS_SOURCE_DISK_NAME,
            second_column_name:LANG.UI_MACHINE_OS_SIZE,
            third_column_name:LANG.UI_MACHINE_OS_RESTORE_TARGET,
         }, config);
 
         //获取初始化元素的DOM元素
         var $elementDom = $(this);
 
         var tableId = settings.origin_timepoint_uuid == "" ? settings.origin_agent_uuid : settings.origin_timepoint_uuid;
         tableId  =  tableId +"_tableId";

 
         //定义一个树形对象
         var treeObj;

         //type为3或者6的时候需要存储获取到的所有挂载信息
         var init_mount_point_list= [];
         var write_mount_point_list= [];




         //组装目标卷数据
         var transformTargetData = function(devices) {
             var resultInfo = {
                 disk_info:[],
                 disk_info_select:"", //磁盘信息勾选框html
                 volume_info:[],
                 volume_info_select:"", //卷信息勾选框html
             };
 
             for (var i = 0; i < devices.length; i++) {
                //这里的recovery_show_flag表示,如果是目标是分区的话, recovery_show_flag为2的就不显示在分区下面了

                 var device = devices[i];
                 if(device.first_node == 1){
                    if(device.recovery_show_flag == 1){
                        resultInfo.volume_info.push(device);
                    }
                    resultInfo.disk_info.push(device);
                 }else if(device.end_node == 1){
                    if(device.recovery_show_flag == 2) continue;
                     resultInfo.volume_info.push(device);
                 }
             }
             
 
             //组装数据成select的html代码
             function packagesSelectHtml(info) {
                 //创建一个select标签
                 var html = '<select selectId class="select3" style="width:100%;" name="target_name"><option value="0">'+LANG.UI_JOB_SELECT+'</option>';
                 if(settings.type == 3){
                    //为3或者6的时候没有请选择
                    html = '<select selectId class="select3" style="width:100%;" name="target_name">';
                 }
                 if (settings.instant_flag != undefined && settings.instant_flag) {
                     html = '<select selectId class="select3" style="width:100%;" name="target_name"><option value="0">'+LANG.UI_PLATFORM_INDUSTRY_INPUT+'</option>';
                 }
                 
                 if(settings.target_auto_flag){
                    html += '<option value="2" selected>'+LANG.UI_COMPONENT_AUTO_ALLOCATE+'</option>';
                 }
                 
                 for (var i = 0; i < info.length; i++) {
                     var device = info[i];
                     var name = device.dev_name;
                    //  var recovery_show_flag = device.recovery_show_flag;
                    //  if(recovery_show_flag == 2){
                    //     continue;
                    //  }
                    if(device.mount_point != ""){
                        name += '(' + device.mount_point + ')'
                    }
                    if(device.removable_flag == 1){
                        name += '(' +LANG.UI_OS_REMOVE_DEVICE + ')'
                    }
                    name +='('+LANG.UI_COMPONENT_TOTAL_CAPACITY+': ' + formatBytes(device.total_size) + ')';
                    //  if(device.first_node == 1){
                    //      name += '(' + device.extra_name + ')';
                    //  }
                    var disbaled = "";
                    if(settings.selected_disabled){
                        disbaled = "disabled";
                    }
                     html += '<option '+disbaled+' value_is_system_disk_flag="'+device.is_system_disk_flag+'" value_struct_type="'+device.struct_type+'" value_total="'+device.total_size+'" value_dev_uuid="'+device.dev_uuid+'" value="' + device.dev_node_uuid + '">' + name + '</option>';
                 }
                 html +='</select>';
                 return html;
             }
 
             resultInfo.disk_info_select = packagesSelectHtml(resultInfo.disk_info);
             resultInfo.volume_info_select = packagesSelectHtml(resultInfo.volume_info);
             return resultInfo;
         };
 
 
         var onCheckFunction = function(treeId,treeNode,nowChecked=false,agent_uuid) {
             //获取该节点的所有子节点
             function getChildNodes(treeNode) {
                 var childNodes = treeObj.transformToArray(treeNode);
                 var nodes = new Array();
                 for(i = 0; i < childNodes.length; i++) {
                 nodes[i] = childNodes[i].id;
                 }
                 return nodes;
             }
             //初始化勾选事件,如果勾选关联打开,则在勾选该节点的时候需要检查该节点的dev_uuid在整个树中是否有相同的dev_uuid,如果存则需要执行相同的勾选或取消勾选操作
             //如果勾选关联开启
             var switchState = settings.forced_relationship;
                 //获取该节点下的所有子节点的id集合
             var childNodesList = getChildNodes(treeNode);
             if (switchState) {
                     //先判断该节点是勾选还是未勾选
                 
                 //循环该节点下的所有子节点id然后获取其dev_uuid
                 for(i = 0; i < childNodesList.length; i++) {
                     var childNode = treeObj.getNodeByParam("id", childNodesList[i]);
                     var dev_uuid = childNode.dev_uuid;
                     //遍历整颗树的节点
                     var nodes = treeObj.getNodesByParam("dev_uuid", dev_uuid);
                     nodes.forEach(function(node) {
                         treeObj.checkNode(node, nowChecked, true);
                     });
                 }
         
             }  
         }
 
 
         //初始化Dom元素
         var initTableDom = function(data) {
             var origin_data = data.origin_data;
             var target_data = data.target_data;
             var target_data_info = transformTargetData(target_data);
             //将数据转换成表格数据
 
             var onCheckListener = function(e, treeId, treeNode) {
                 var nowChecked = treeNode.checked;
                 onCheckFunction(treeId,treeNode,nowChecked,config.agent_uuid)
             };
 
             var setting = {
                 view: {
                     showLine: false,
                     showIcon: false,
                     showTitle: false,
                     addDiyDom: addDiyDom,
                     nameIsHTML: true
                 },
                 check: {
                         enable: true
                     },
                 callback: {
                     onCheck: onCheckListener,
                 },
                 data: {
                     simpleData: {
                         enable: true
                     }
                 }
             };
             /**
              * 自定义DOM节点
              */
             function addDiyDom(treeId, treeNode) {
                 var spaceWidth = 26;
                 var liObj = $("#" + treeNode.tId);
                 var aObj = $("#" + treeNode.tId + "_a");
                 var switchObj = $("#" + treeNode.tId + "_switch");
                 var icoObj = $("#" + treeNode.tId + "_ico");
                 var spanObj = $("#" + treeNode.tId + "_span");
                 var corpCatDisplay = settings.target_visible_flag  ? "" : "display:none;";
                 var corpCatWidth = settings.target_visible_flag  ? "" : "width:50%";
                 spanObj.prepend ('<i class="levelchild viconfont '+treeNode.iconClass+'"></i>');
                 if(settings.nocheck){
                    aObj.before('<span style="display:inline-block;width:15px;height:15px;margin: 0 13px;"> </span>')
                 }
                 aObj.attr('dev_node_uuid', treeNode.dev_node_uuid);
                 aObj.append('<div class="diy swich" style="width:30%;'+corpCatWidth+'"></div>');
                 var div = $(liObj).find('div').eq(0);
                 switchObj.remove();
                 spanObj.remove();
                 icoObj.remove();
                 div.append(switchObj);
                 div.append(spanObj);
                 var spaceStr = "<span style='height:1px;display: inline-block;width:" + (spaceWidth * treeNode.level) + "px'></span>";
                 switchObj.before(spaceStr);
                 
                 var editStr = '';
                 editStr += '<div class="diy" style="width:30%;'+corpCatWidth+'">' +formatBytes(treeNode.total_size) + '</div>';
                 // var corpCat = '<div title="' + treeNode.CORP_CAT + '">' + treeNode.CORP_CAT + '</div>';
                 var corpCat = "";
                 if(settings.recover_type){//如果是整机恢复
                     if(treeNode.first_node == 1){ //如果是磁盘, 则显示恢复目标
                         corpCat = target_data_info.disk_info_select;
                     }
                 }else{//如果是卷恢复
                     if(treeNode.end_node == 1){ //如果是最后一个节点
                         corpCat = target_data_info.volume_info_select;
                     }
                 }

                 if (treeNode.show_amount != undefined && treeNode.show_amount) {
                     if(treeNode.pId == 0){
                         // 第一层默认都是磁盘
                         corpCat = target_data_info.disk_info_select;
                     } else {
                         corpCat = target_data_info.volume_info_select;
                     }
                 }
                 editStr += '<div class="diy selecteTarget" style="width:40%; '+corpCatDisplay+'">' + corpCat + '</div>';
             
                 aObj.append(editStr);


                 //初始化select
                function initSelectListener(){
                    //获取当前节点的dev_node_uuid
                    var dev_node_uuid = treeNode.dev_node_uuid;
                    //获取选择数据
                    var selectValueList = settings.init_selected[dev_node_uuid];
                    if(selectValueList == undefined|| selectValueList == null){
                        return;
                    }
                    //获取已经选择类型
                    var selected_type = selectValueList['selected_type'];
                    var $select = aObj.find(".select3");
                     //获取值
                     var selected_value = selectValueList['selected_value'];
                     if(selected_type == 'selected'){
                        //获取aObj下面的select对象
                        //检查是否有select是否有这个值
                        aObj.find(".select3").val(selected_value)
                     }else{
                        aObj.find(".select3").append('<option selected value="'+selected_value+'">'+selected_value+'</option>')
                     }
                     //更新select2插件
                     $select.trigger('change');
                     return;
                }
              
                //如果settings.init_selected对象为空{}
                if(Object.keys(settings.init_selected).length != 0){
                    initSelectListener();
                }

                 //获取选中的值并添加到初始化中
                 if(corpCat != ""){
                    //获取选择框对象
                    var selectObj = aObj.find(".select3");
                    //获取其选中的值option对象
                    // 获取当前选中的 <option> 元素
                    var $selectedOption = selectObj.find('option:selected');

                    //获取选中值
                    var selectedValue = {};
                    //获取选中的值的dev_node_uuid
                    // 获取选中的值的 dev_node_uuid
                    selectedValue.dev_node_uuid =  selectObj.val();
                    selectedValue.dev_uuid = $selectedOption.attr('value_dev_uuid') ?? "";
                    selectedValue.total_size = $selectedOption.attr('value_total') ?? "";
                    selectedValue.struct_type = $selectedOption.attr('value_struct_type') ?? "";
                    selectedValue.name = $selectedOption.text();
                    treeNode.selectedInfo = selectedValue;
                 }
                 
             }
 
             //获取部分数据存储到html的属性中
             var baseDatas = {
                origin_task_uuid: settings.origin_task_uuid, 
                origin_agent_uuid: settings.origin_agent_uuid, 
                origin_timepoint_uuid: settings.origin_timepoint_uuid, 
                target_agent_uuid: settings.target_agent_uuid,
                recover_type: settings.recover_type, 
                target_visible_flag: settings.target_visible_flag,
                type: settings.type,
             };
             //把baseDatas转换成json
             var baseDatasJson = JSON.stringify(baseDatas);

             $elementDom.html('<ul id="'+tableId+'" data-datas="' + encodeURIComponent(baseDatasJson) +'" class="recoverTargetZtree ztree"></ul>');

             
           
             //初始化树
             treeObj = $.fn.zTree.init($("#"+tableId), setting, origin_data);
             
             //初始化成功后对树进行操作
            function nocheckedListener(){
                var init_nochecked = settings.init_nochecked;
                if(init_nochecked.length == 0){
                    return;
                }
                for(var i = 0; i < init_nochecked.length; i++){
                    var node = treeObj.getNodeByParam("dev_node_uuid", init_nochecked[i]);
                    //判断是否找到,没找到就下一个
                    if(node.length == 0){
                        continue;
                    }
                    treeObj.checkNode(node, false, false);
                }



                
            }

            function setChkDisabledListener(){
                //只有当 recover_type为true的时候才进行下面操作
                // 瞬时恢复也不需要操作
                if(!Boolean(settings.recover_type) || settings.instant_flag){
                    return;
                }
                //这儿对树进行统一操作
                //获取当前树结构
                var tree = treeObj.getNodes();
                //循环每个节点
                for( var i = 0; i < tree.length; i++){
                    var node = tree[i];
                    //判断当前是否是系统所在磁盘
                    if(node.is_system_disk_flag == 1){ 
                        //如果是系统所在磁盘则禁用当前节点及该节点下面的子节点
                        treeObj.setChkDisabled(node, true, false, true);

                    }
                }
            }
            nocheckedListener();
            setChkDisabledListener();
           
           
            function formatState (state) {
                if (!state.id ){
                    return state.text;
                }
                 //获取stats的类
                 //判断是否有element和element.className属性
                 var thisclass = "";
                 var thistext = state.text;
                 if(state.element && state.element.className){
                    thisclass = state.element.className;
                    thistext +="("+LANG.UI_COMPONENT_SELECTED+")";
                }
                var $state = $('<span class="'+thisclass+'">' + thistext + '</span>');
                return $state;
              };
            $(".select3").select2({ 
                tags: settings.target_input_flag, 
                minimumResultsForSearch:settings.target_input_min,
                // templateResult: formatState
            });
            // 自定义搜索框的样式
            $(".select3").on("select2:open", function () {
                var $searchField = $(".select2-search__field");
                $searchField.attr("placeholder", LANG.UI_COMPONENT_CUSTOM_PATH);
            });
            
            var corpCatDisplay = settings.target_visible_flag  ? "" : "display:none;";
            var corpCatWidth = settings.target_visible_flag  ? "" : "width:50%";
             //添加表头
             var li_head = ' <li class="head"><a><div class="diy" style="width:40px;"></div>'+
             '<div class="diy" style="width:30%;'+corpCatWidth+'">'+settings.first_column_name+'</div>'+
             '<div class="diy" style="width:30%;'+corpCatWidth+'">'+settings.second_column_name+'</div>'+
             '<div class="diy" style="width:40%; '+corpCatDisplay+' ">'+settings.third_column_name+'</div>' ;
             var rows = $("#"+tableId).find('li');
             if (rows.length > 0) {
                 rows.eq(0).before(li_head)
             } else {
                 $("#"+tableId).append(li_head);
                 $("#"+tableId).append('<li ><div style="text-align: center;line-height: 30px;" >'+LANG.UI_COMPONENT_NO_DATA+'</div></li>')
             }
 
              // 初始化搜索框事件
             // 初始化所有具有特定name属性的<select>元素
             var $selects = $("#"+tableId).find('select[name="target_name"]');
             $(".select2-search__field").attr("placeholder",LANG.UI_COMPONENT_CUSTOMIZE)            

             function updateDisabledOptions() {  
                 // 重置所有非零值和非自动选择选项的禁用状态  
                //  $selects.find('option').filter(function() {  
                //      return $(this).val() !== '0' || $(this).val() !== '2';  
                //  }).prop('disabled', false);  
                //  $selects.find('option').filter(function() {  
                //      return $(this).val() !== '0' && $(this).val() !== '2' ;  
                //  }).css('color', "");  
                 // 遍历所有选择框，并禁用其他选择框中已选择的相同非零值选项  
                 $selects.each(function() {  
                     var $this = $(this);  
                     var selectedValue = $this.val();  
                     if (selectedValue !== '0' && selectedValue !== '2') {
                        //先按照常规禁用
                        // $selects.not(this).find('option[value="' + selectedValue + '"]').attr('disabled', true);
                        $selects.not(this).find('option[value="' + selectedValue + '"]').addClass('selectedGreen');
                         
                        
                     }  
                 }); 
             }  

            //强制关联同步选择  
            function updateSameOption(dev_node_uuid, selectedValue){
                //获取整个ztree
                var treeObj = $.fn.zTree.getZTreeObj(tableId);
                //找到dev_node_uuid的节点
                var node = treeObj.getNodeByParam("dev_node_uuid", dev_node_uuid);
                //获取该节点的dev_uuid
                var dev_uuid = node.dev_uuid;
                //遍历树结构找到所有dev_uuid的集合
                var nodes = treeObj.getNodesByParam("dev_uuid", dev_uuid);
                nodes.forEach(function(node) {
                    //获取a标签的id
                    var aObj = $("#" + node.tId + "_a");
                    //获取aObj下面的name为target_name的select对象
                    var selectObj = aObj.find('select[name="target_name"]');
                    //选中selectObj的值为selectedValue
                    selectObj.val(selectedValue).trigger('change.select2', { silent: true });

                    // 获取当前元素的上一级的上一级元素的 id
                    var parentId = selectObj.parent().parent().attr('dev_node_uuid');
                    var selectedValueNode = {};
                    var selectedOption = selectObj.find('option:selected');
                    selectedValueNode.dev_node_uuid =  selectObj.val();
                    selectedValueNode.dev_uuid = selectedOption.attr('value_dev_uuid') ?? "";
                    selectedValueNode.total_size = selectedOption.attr('value_total') ?? "";
                    selectedValueNode.struct_type = selectedOption.attr('value_struct_type') ?? "";
                    selectedValueNode.name = selectedOption.text();
                    //把selectedValue值放在treeObj节点里面
                    //找到对应的节点
                    var nodesSelect = treeObj.getNodesByParam("id", parentId, null);
                    nodesSelect[0].selectedInfo = selectedValueNode;
                    //因为update会影响重构的一些属性,所以这里名字要把图标加上更新一下
                    nodesSelect[0].name = `<i class="levelchild viconfont ${nodesSelect[0].iconClass}"></i>${nodesSelect[0].dev_name}`
                    updateDisabledOptions(selectObj.val());
                })
            }

            function getSelectValueList (){
                var select_val_list = [];
                $selects.each(function() {  
                    var $this = $(this);  
                    var selectedValue = $this.val();  
                    if (selectedValue != '0' && selectedValue != '2') {
                        select_val_list.push(selectedValue);
                    }
                }); 
                return select_val_list;

            }


            function exist_same_select(this_dev_node_uuid) {
                // 获取所有的选中的值的 dev_node_uuid
                let dev_node_uuids = [];
                let skip_first_occurrence = true; // 标志变量，用于跳过第一次出现的 this_dev_node_uuid
                $selects.each(function() {  
                    var $this = $(this);  
                    var selectedValue = $this.val();  
                    if (selectedValue != '0' && selectedValue != '2') {
                        if (selectedValue == this_dev_node_uuid) {
                            if (skip_first_occurrence) {
                                skip_first_occurrence = false; // 设置为 false，表示第一次出现已经跳过了
                                return; // 跳过本次循环
                            }
                        }
                        dev_node_uuids.push(selectedValue);
                    }
                }); 
                return dev_node_uuids;
            }

            /**
             * 优化路径数组 - 去除被其他路径包含的子路径
             * @param {string[]} pathArray 原始路径数组
             * @returns {string[]} 优化后的路径数组（只保留最根路径）
             */
            function optimizePathArray(pathArray) {
                // 先标准化所有路径（统一斜杠，去掉末尾斜杠）
                const normalizedPaths = pathArray.map(p => 
                    p.replace(/\\/g, '/').replace(/\/+$/, '')
                );
                
                // 按路径长度升序排序（短的路径在前）
                const sortedPaths = [...normalizedPaths].sort((a, b) => a.length - b.length);
                
                const result = [];
                
                outer: for (let i = 0; i < sortedPaths.length; i++) {
                    const currentPath = sortedPaths[i];
                    
                    // 检查是否已被结果集中的某个路径包含
                    for (const existingPath of result) {
                        if (currentPath === existingPath || 
                            currentPath.startsWith(existingPath + '/')) {
                            // 当前路径是已有路径的子路径，跳过
                            continue outer;
                        }
                    }
                    
                    // 不是任何已有路径的子路径，添加到结果集
                    result.push(currentPath);
                }
                
                // 返回原始格式（保持原始斜杠风格）
                return result.map(p => {
                    const original = pathArray.find(op => 
                        op.replace(/\\/g, '/').replace(/\/+$/, '') === p
                    );
                    return original || p;
                });
            }
            /**
             * 统计目标路径作为前缀被路径数组匹配的次数
             * @param {string[]} pathArray 路径数组
             * @param {string} targetPath 要检查的目标路径
             * @returns {number} 匹配次数
             */
            function countMatchingParentPaths(pathArray, targetPath) {
                // 标准化目标路径：统一斜杠，去掉末尾斜杠
                const normalizedTarget = targetPath.replace(/\\/g, '/').replace(/\/+$/, '');
                let count = 0;

                for (const path of pathArray) {
                    // 标准化当前路径
                    const normalizedPath = path.replace(/\\/g, '/').replace(/\/+$/, '');
                    
                    // 检查当前路径是否以目标路径开头
                    // 并且后面紧跟斜杠或已到字符串末尾（确保是完整前缀匹配）
                    if (normalizedPath.startsWith(normalizedTarget) && 
                        (normalizedPath.length === normalizedTarget.length || 
                        normalizedPath[normalizedTarget.length] === '/')) {
                        count++;
                    }
                }

                return count;
            }

                /**
         * 标准化路径（统一格式并提取根路径）
         * @param {string} path 原始路径
         * @returns {string} 标准化后的根路径
         */
        function normalizeToRootPath(path) {
            // 1. 统一使用正斜杠
            // 2. 移除末尾斜杠
            // 3. 提取最顶层路径
            const normalized = path.replace(/\\/g, '/').replace(/\/+$/, '');
            
            // 提取根路径逻辑：
            // - Linux路径: /usr/local → /usr
            // - Windows路径: C:/Program Files → C:
            // - 网络路径: //server/share → //server/share
            const parts = normalized.split('/');
            
            if (normalized.startsWith('//')) {
                // 处理网络路径 (//server/share)
                return parts.slice(0, 3).join('/');
            } else if (/^[A-Za-z]:/.test(parts[0])) {
                // 处理Windows路径 (C:)
                return parts[0];
            } else {
                // 处理Linux路径 (/usr)
                return parts.length > 1 ? `/${parts[1]}` : normalized;
            }
        }



                    /**
             * 验证路径是否符合指定操作系统的格式
             * @param {string} path 要验证的路径
             * @param {'windows'|'linux'} osType 操作系统类型
             * @returns {boolean} 是否匹配对应系统的路径格式
             */
            function validatePathFormat(path, osType) {
                if (osType === 'Windows') {
                    // Windows路径正则：
                    // 1. 盘符路径：C:\ 或 C:\path
                    // 2. 网络路径：\\server\share
                    // 3. 允许正斜杠/反斜杠混合（Windows实际都支持）
                    const winRegex = /^(?:[a-zA-Z]:[\\/]|\\\\[^\\/?:*"<>|\r\n]+\\[^\\/?:*"<>|\r\n]+)/;
                    return winRegex.test(path);
                } else if (osType === 'Linux') {
                    // Linux路径正则：
                    // 1. 必须以/开头
                    // 2. 不允许包含Windows特殊字符 <>:"|\?*
                    const linuxRegex = /^\/[^<>:"|?*]*$/;
                    return linuxRegex.test(path);
                }
                return false;
            }



         
             // 监听选择框的变化  
             $selects.on('change', function() {
                // 获取当前元素的上一级的上一级元素的 id
                var parentId = $(this).parent().parent().attr('dev_node_uuid');
                //获取当前选中的option的dev_node_uuid
                var this_dev_node_uuid = $(this).find('option:selected').val();
                //这里type为3单独处理
                if(settings.type == 3){
                    //这里获取的this_dev_node_uuid为手动输入或者自动选择
                    //其中自动选择的值为2
                    if(this_dev_node_uuid == 2){
                       //如果为2自动选择则不管
                    }else{
                        //先验证输入格式是否正确
                        //如果是操作系统
                        var valid_result = validatePathFormat(this_dev_node_uuid, settings.target_os_type)
                        if(!valid_result){
                             //如果大于等于2个,则表明当前值是一个重复值
                             UIToastr.showWarning(LANG.UI_MACHINE_OS_TARGET_CONFIG, LANG.UI_MACHINE_OS_INPUT_RIGHT_PATH);
                             // 阻止默认行为，恢复之前的选中值
                             $(this).val(2).trigger('change.select2', { silent: true });
                             return;
                        }
                        //如果不为2则是用户自定义输入的值
                        //获取所有选中的值
                        var selectList = getSelectValueList();
                        //将用户输入的值存入write_mount_point_list中,每次切换的时候都会更新下这个值
                        write_mount_point_list = selectList;
                        //合并两个数组write_mount_point_list和init_mount_point_list
                        var new_list = write_mount_point_list.concat(init_mount_point_list);
                        //先对数组去重
                        // var new_list_unique = optimizePathArray(new_list);
                        //标准化获取的路径
                        var new_list_unique_standard = normalizeToRootPath(this_dev_node_uuid);
                        // 再用优化后的数组进行匹配计数
                        const count = countMatchingParentPaths(new_list, new_list_unique_standard);
                        if(count >= 2){
                            //如果大于等于2个,则表明当前值是一个重复值
                            UIToastr.showWarning(LANG.UI_COMPONENT_RESTORE_TARGET, LANG.UI_MACHINE_OS_UNMOUNT_TIP);
                            // 阻止默认行为，恢复之前的选中值
                            $(this).val(2).trigger('change.select2', { silent: true });
                            return;
                        }

                        //这里判断是什么操作类型
                        // var target_os_type = settings.target_os_type

                        
                    }
                }
                //type为4的时候 不能选择系统磁盘  7回切目标磁盘不能为系统所在磁盘
                if(settings.type == 4 || settings.type == 7){
                    //获取当前选中的磁盘类型
                    var is_system_disk_flag = $(this).find('option:selected').attr('value_is_system_disk_flag');
                    if(is_system_disk_flag == 1){
                        UIToastr.showWarning(LANG.UI_COMPONENT_RESTORE_TARGET, LANG.UI_MACHINE_OS_IS_NOT_SYSTEM_DISK);
                        // 阻止默认行为，恢复之前的选中值
                        $(this).val(0).trigger('change.select2', { silent: true });
                        return;
                    }
                }

                 //找到该节点
                var nodes = treeObj.getNodesByParam("dev_node_uuid", parentId);
                if(nodes.length != 0){
                    //获取当前左边源值的大小
                    var oringin_total_size = nodes[0].total_size;
                    //获取当前选中的目标大小
                    var target_value_total = $(this).find('option:selected').attr('value_total');
                    //比较两个值的大小
                    if(oringin_total_size > target_value_total){
                        UIToastr.showWarning(LANG.UI_COMPONENT_RESTORE_TARGET, LANG.UI_COMPONENT_TARGET_CAPACITY_ERROR);
                        // 阻止默认行为，恢复之前的选中值
                        $(this).val(0).trigger('change.select2', { silent: true });
                        return;
                    }
                }
                 //获取选中值
                //  var selectedValue = {};
                 //获取选中的option的class
                 var thisclass = $(this).find('option:selected').attr('class');
               
                 //获取所有已选择的值
                 let dev_node_uuids = exist_same_select(this_dev_node_uuid);
                 //如果选中值和已选值相同则直接返回
                 if(dev_node_uuids.includes(this_dev_node_uuid)){
                    UIToastr.showWarning(LANG.UI_COMPONENT_RESTORE_TARGET, LANG.UI_COMPONENT_DISK_ALREADY_SELECTED);
                    // 阻止默认行为，恢复之前的选中值
                    $(this).val(0).trigger('change.select2', { silent: true });
                    return;
                 }



                //  if(thisclass == "selectedGreen"){
                //     UIToastr.showWarning("恢复目标", "该磁盘已选择,请勿重复选择");
                //     // 阻止默认行为，恢复之前的选中值
                //     $(this).val(0).trigger('change.select2', { silent: true });
                //     return;
                //  }
                
                 //获取选中的值的dev_node_uuid
                 // 获取选中的值的 dev_node_uuid
                //  var selectedOption = $(this).find('option:selected');
                //  selectedValue.dev_node_uuid =  $(this).val();
                //  selectedValue.dev_uuid = selectedOption.attr('value_dev_uuid') ?? "";
                //  selectedValue.total_size = selectedOption.attr('value_total') ?? "";
                //  selectedValue.struct_type = selectedOption.attr('value_struct_type') ?? "";
                //  selectedValue.name = selectedOption.text();
                //  //把selectedValue值放在treeObj节点里面
                //  //找到对应的节点
                //  var nodes = treeObj.getNodesByParam("id", parentId, null);
                //  nodes[0].selectedInfo = selectedValue;
                //  //因为update会影响重构的一些属性,所以这里名字要把图标加上更新一下
                //  nodes[0].name = `<i class="levelchild viconfont ${nodes[0].iconClass}"></i>${nodes[0].dev_name}`
                 updateSameOption(parentId,$(this).val());
                //  treeObj.updateNode(nodes[0]);
                //  updateDisabledOptions($(this).val()); 
                
                 
                 
                 
             });  
          

             
             
 
 
 
 
 
 
   
 
 
 
 
 
 
 
 
 
                         
             
                
         }
 
 
 
         var initData = function() {
             //根据实时定时来判断调用哪一个接口
             //如果为定时恢复，则调用定时恢复接口
             var dataList = settings;
             if(settings.loading != ""){
                Metronic.blockUI({target:$(settings.loading),animate: true});
             }
             var requestOriginFun = function(datas) {
                 if(datas.success){
                    if(dataList.type == 3 || dataList.type == 4){
                        //如果是用前端传过来的数据
                        datas.data.origin_data = settings.origin_data_array;
                    }
                    //为3的时候需要存储一下挂载磁盘信息,防止用户重复选择
                    if(dataList.type == 3){
                        init_mount_point_list = datas.data.mount_point_list;
                    }
                     initTableDom(datas.data);
                     if(settings.loading != ""){
                        Metronic.unblockUI(settings.loading);
                     }
                     if (settings.callback != '') {
                         // 执行回调方法
                         (settings.callback)();
                     }
                 }else{
 
                 }
 
             };
             pAjaxRequest(dataList, "/api/v1/complete_machine_os/get_recover_target_info", "post", requestOriginFun,true);
 
         };
 
         
 
         //容量单位转换, decimals (可选): 输出结果的小数位数，默认为2。
         function formatBytes(bytes, decimals = 2) {
             bytes = parseInt(bytes);
             if (bytes === 0) return '0 Bytes';
         
             const k = 1024;
             const dm = decimals < 0 ? 0 : decimals;
             const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
         
             const i = Math.floor(Math.log(bytes) / Math.log(k));
         
             return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
         }
 
        
 
       
 
 
 
         var initfunction = function() { 
             initData(); //初始化数据
 
             
         };
 
        
        return initfunction();
    };



    //插件方法
   $.fn.getTargetData = function(method, params = "", isInstantMachine = false, isIgnoreSys = false) {
        // 获取数据(处理后的)
        var getTargetDataAllFunc = function() {
            var resultDatas = [];
            // 获取所有id的后缀为_tableId的id集合
            function getElementsWithSuffix(suffix) {
                
                // 获取页面上所有的元素
                const allElements = document.querySelectorAll('*');
                // 存储符合条件的元素
                const elementsWithSuffix = [];

                // 遍历所有元素
                allElements.forEach(element => {
                    // 获取元素的id
                    const id = element.id;
                    // 检查id是否以指定后缀结尾
                    if (id.endsWith(suffix)) {
                        elementsWithSuffix.push(element);
                    }
                });

                return elementsWithSuffix;
            }

            // 使用示例
            const suffix = '_tableId';
            const elements = getElementsWithSuffix(suffix);
           // 打印结果
            elements.forEach(element => {
                let element_val = getZtreeDataFunc(element.id, isInstantMachine, isIgnoreSys);
                if(!element_val){
                    resultDatas = false;
                    return;
                }
                resultDatas.push(element_val);
            });
            return resultDatas;
        }



        var getZtreeDataFunc = function(eachID, isInstantMachine = false, isIgnoreSys = false) {
            //初始化数据
            var eachdatas = {};
            //先判断是否存在这个id
            if (!$('#'+eachID).length) {
                UIToastr.showWarning(LANG.UI_MACHINE_OS_TARGET_CONFIG, LANG.UI_MACHINE_OS_NOT_INIT);
                return false;
            }
            //获取这个eachID中存储的data-datas值
            var data_datas = $('#'+eachID).attr('data-datas');
            // 解码并解析 JSON 数据
            var storedData = decodeURIComponent(data_datas);
            storedData = JSON.parse(storedData);
            //获取所有基本数据
            eachdatas.origin_task_uuid = storedData.origin_task_uuid;
            eachdatas.origin_agent_uuid = storedData.origin_agent_uuid;
            eachdatas.origin_timepoint_uuid = storedData.origin_timepoint_uuid;
            eachdatas.target_agent_uuid = storedData.target_agent_uuid;
            eachdatas.recover_type = storedData.recover_type;
            eachdatas.target_strategy = []; 
            eachdatas.exclude_dev = [];
            eachdatas.disk_list = [];
            //获取ztree对象
            var treeObj = $.fn.zTree.getZTreeObj(eachID);
            var nodes = treeObj.transformToArray(treeObj.getNodes());

            //获取所有磁盘信息----------
            //获取勾选的磁盘信息------------
           
            //获取所有勾选的集合
            var checkedNodes_list = treeObj.getCheckedNodes(true);
            //循环获取磁盘信息
            for(let i = 0; i < checkedNodes_list.length; i++){
                if(checkedNodes_list[i].first_node != 1){
                    continue;
                }
                let eachDevice = {};
                //获取名称
                eachDevice.dev_name = checkedNodes_list[i].dev_name;
                //获取其uuid
                eachDevice.dev_uuid = checkedNodes_list[i].dev_uuid;
                //获取唯一id
                eachDevice.dev_node_uuid =checkedNodes_list[i].dev_node_uuid;
                eachdatas.disk_list.push(eachDevice);
            }
            //获取所有磁盘信息----------
            //循环所有节点数据 
            for(var j = 0; j < nodes.length; j++){
                //获取卷配置
                let each_recovery_strategy = {};
                //获取排除配置
                let each_exclude_recovery_dev = {};
                //获取是否勾选
                let checked = nodes[j].checked;
               
                //获取磁盘属性
                let first_node = nodes[j].first_node;
                //获取是否是最后一层属性
                let end_node = nodes[j].end_node;
                //如果是整机恢复
                if((storedData.recover_type && checked && first_node == 1) || 
                (!storedData.recover_type && checked && end_node == 1) || isInstantMachine){
                    // 获取恢复目标配置信息
                    let selectedInfo = nodes[j].selectedInfo;
                    if (isInstantMachine) {
                        if (selectedInfo == undefined) {
                            continue;
                        }
                        selectedInfo.dev_node_uuid = nodes[j].dev_node_uuid;
                    }
                    //获取其选择的值
                    let dev_node_uuid = selectedInfo != undefined ? selectedInfo.dev_node_uuid : 0;
                    if(dev_node_uuid == 0 && storedData.target_visible_flag && !isInstantMachine){
                        UIToastr.showWarning(LANG.UI_COMPONENT_RESTORE_TARGET, LANG.UI_COMPONENT_SOURCE_DISK_CONFIG_ERROR);
                        eachdatas =  false;
                        break;
                    }
                    if (isInstantMachine) {
                        each_recovery_strategy.source_fs_uuid = nodes[j].fs_uuid;
                    }
                    each_recovery_strategy.source_dev_struct_type = nodes[j].struct_type;
                    each_recovery_strategy.source_dev_uuid = nodes[j].dev_uuid;
                    each_recovery_strategy.main_pid = nodes[j].main_pid != undefined ? nodes[j].main_pid : '';
                    each_recovery_strategy.source_name = nodes[j].name;
                    each_recovery_strategy.source_dev_node_uuid = nodes[j].dev_node_uuid;
                    each_recovery_strategy.source_is_system_disk_flag = nodes[j].is_system_disk_flag;

                    each_recovery_strategy.destination_dev_struct_type = selectedInfo.struct_type;
                    each_recovery_strategy.destination_dev_uuid = selectedInfo.dev_uuid;
                    each_recovery_strategy.destination_dev_node_uuid = selectedInfo.dev_node_uuid;
                    each_recovery_strategy.destination_name = selectedInfo.name;

                    eachdatas.target_strategy.push(each_recovery_strategy);

                }else if(!checked){
                    each_exclude_recovery_dev.dev_uuid = nodes[j].dev_uuid;
                    each_exclude_recovery_dev.dev_node_uuid = nodes[j].dev_node_uuid;
                    each_exclude_recovery_dev.dev_name = nodes[j].dev_name;
                    eachdatas.exclude_dev.push(each_exclude_recovery_dev);
                }
            }
            if(!eachdatas){
                return false;
            }
            //type为4 则不检测是否数据源是否含有系统磁盘
            if(storedData.type != 4 && storedData.type != 7 && storedData.type != 2 && !isIgnoreSys){
                //这里如果目标是磁盘层, 则左边勾选的源必须要有系统磁盘在,没有系统磁盘或没有备份系统磁盘都不能通过
                let hasSystemDisk = false; //false表示没有系统磁盘
                if(storedData.recover_type){
                    for(let i = 0; i < eachdatas.target_strategy.length; i++){
                        if(eachdatas.target_strategy[i].source_is_system_disk_flag == 1){
                            hasSystemDisk = true;
                            break;
                        }
                    }
                    if(!hasSystemDisk){
                        UIToastr.showWarning(LANG.UI_COMPONENT_RESTORE_TARGET, LANG.UI_MACHINE_OS_IS_NOT_SYSTEM_DISK_TIP);
                        eachdatas = false;
                    }
                }

            }
            
            if(!eachdatas){
                return false;
            }
           
           
            if(eachdatas.target_strategy.length == 0){
                UIToastr.showWarning(LANG.UI_COMPONENT_RESTORE_TARGET, LANG.UI_COMPONENT_AT_LEAST_ONE_SELECTED);
                eachdatas =  false;
            }
           return eachdatas;
        }


        var getTargetDataFunc = function(eachID, isInstantMachine, isIgnoreSys) {
            eachID = eachID+'_tableId';
            return getZtreeDataFunc(eachID, isInstantMachine, isIgnoreSys);
        }






      switch (method) {
            case 'getAllData':
                return getTargetDataAllFunc(); //所有处理后的集合
            case 'getEachData':
                return getTargetDataFunc(params, isInstantMachine, isIgnoreSys); //处理后的
           default:
            console.log("The method is not provided")
            return false;
        } 
    };
 
 
 
 }(jQuery));