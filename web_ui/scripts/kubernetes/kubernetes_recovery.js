var K8sRecovery = function () {
    var resultData = {};
    var newData;
    var zTreeAgent, zTreeDB = [];
    var searchFlag = false;
    var _timeStamp = '';	//时钟时间戳,全局
    var nodeSelectFlag = false; //自定义节点选择加载标志
    var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
    var _agentName, _agentIP;
    var _agentuuid;
    var dbTypeGlob;//获取数据库类型
    var _archiveValue = 2;
    var _lastDbType;
    var authFun = [];
    var speedList = [];
    var initSpeedFlag = false;
    var agentList = [];
    var _oldPassword = '';

    var initStrategyFlag = false;
    var globalStrategy = [];
    var editFlag = false;
    var defaultStrategy = [];

    var oldNode, networkFlag = false;	//用于比对加载传输网络的节点
    var initConfig = false;
    var namespaceList = [];//存储命名空间列表
    var recoverPVCList = [];
    var timepointStr = "";
    var complete_backup = true; //初始化恢复组件新增了一个参数backup_disable_flag，取值true表示备份没有配置完整性策略，false表示备份配置了

    //K8s备份类型
	 //任务备份类型
	 var K8S_BY_TYPE ={
		UNKNOWN: 0,
		NAMESPACE: 1,
		APPLICATION: 2
	 };
   
    //------
	var defaultConfig = {
        dom: $('#backupStrategyDiv'),
        mode: [1, 2, 3],
		time:false,
		store:false,
		reserve:false,
    };

  


    //初始化备份源树形结构
    var initAgentTree = function(){
        Metronic.blockUI({target: '.agent_tree_div',animate: true,cenrerY: true,});
        var requestClusterZtree = function(d){
            if(d.data == null || d.data == undefined || d.data == "" || d.data.length == 0){
                $("#agent_tree").hide();
                $(".no_search_result").show();
            }else{
                $("#agent_tree").show();
                $(".no_search_result").hide();
            }
            //初始化树
            setTree(d.data);
            Metronic.unblockUI('.agent_tree_div');
        }
        var dataList = {}
        dataList.storage_uuid = "";
        dataList.recoverflag = true;
        dataList.dataflag = false;
        dataList.search_flag = false;
        //初始化为按应用备份
        pAjaxRequest(dataList, "/api/v1/kubernetes/restore_data", "GET", requestClusterZtree,true);
    };

    var initSearchAgentTree = function(){
        //清空表格数据
        $('#data_table_namespace').bootstrapTable('load', []);
        $('#data_table_app').bootstrapTable('load', []);

        // Metronic.blockUI({target: '.agent_tree_div',animate: true,cenrerY: true,});
        var requestSearchClusterZtree = function(d){
            if(d.data == null || d.data == undefined || d.data == "" || d.data.length == 0){
                $("#agent_tree").hide();
                $(".no_search_result").show();
            }else{
                $("#agent_tree").show();
                $(".no_search_result").hide();
            }
            //初始化树
            setTree(d.data);
            // Metronic.unblockUI('.agent_tree_div');
        }
        var dataList = {}
        dataList.storage_uuid = "";
        dataList.recoverflag = true;
        dataList.dataflag = false;
        dataList.search_flag = true;
        dataList.search = {};
        dataList.search.search_keyword = $('#searchVal').val();
        dataList.search.search_forever_flag = "";
        //初始化为按应用备份
        pAjaxRequest(dataList, "/api/v1/kubernetes/restore_data/search", "GET", requestSearchClusterZtree,true);
    };
     /**
     * 添加时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
	  const addPointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.level !== 2 && treeNode.level !== 3) {//不是时间点
            return;
        }
        if ($(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).length) {
            return;
        }
        let sObj = $(`#${treeNode.tId}_span`);
        sObj.after(`<span id="${treeNode.tId}_${treeNode.timepoint_uuid}"><i class='viconfont vicon-Frame11'></i></span>`);
        // 注册点击事件
        $(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).on("click", (ev) => {
            ev.stopPropagation();  // 阻止click事件向上冒泡
            $('.page-content').initPointDetailDrawer({timepoint_uuid: treeNode.timepoint_uuid});
        });
    };

    /**
     * 移除时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
    const removePointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.level !== 2) {//不是时间点
            return;
        }
        $(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).off().remove();
    };


    var setTree = function(zNodes){
        var setting = {
            view:{
                nameIsHTML: true,
                fontCss: function(treeId, treeNode) {
                    let css = {};
		            if (treeNode.point_status == 1) {
		            	css = { color: "#F19F00 " };
		            }
		            if (treeNode.point_status == 3) {
		            	css = { color: "#F1416C " };
		            }
		            return css;
                },
                addHoverDom: addPointTreeHoverDom,
				removeHoverDom: removePointTreeHoverDom,
            },
            check: {
                enable: true,
                nocheckInherit: false,//true 表示 新加入子节点时，自动继承父节点 nocheck = true 的属性。false 表示 新加入子节点时，不继承父节点 nocheck 的属性。
                chkStyle: "radio",
                radioType: "all",
            },
            data: {
                simpleData: {
                    enable: true
                },
                key:{
                    title: "title"
                }
            },
            callback: {
                // beforeClick: beforeClickListener,
                beforeExpand: beforeExpandListener,
                onCheck: onCheckListener,
                onClick: zTreeOnClick,
                // onExpand: beforeClickListener,
            }
        };
        zTreeAgent = $.fn.zTree.init($("#agent_tree"), setting,zNodes);
    };

    //tree勾选事件
    var beforeExpandListener = function (treeId,treeNode){
        nodeRequest(treeId, treeNode);
    }
    //点击事件
    var beforeClickListener = function(treeId,treeNode){
        //获取树对象
        var treeObj = $.fn.zTree.getZTreeObj("agent_tree");
        //查看当前是否展开
        if((treeNode.type == 2 && !havechildren) || (treeNode.type == 3 && !havechildren)){
            if(treeNode.checked){
                treeObj.checkNode(treeNode, false, false,true);
            }else{
                treeObj.checkNode(treeNode, true, false,true);
            }
        }
        //触发勾选事件
        // treeObj.checkNode(treeNode, treeNode.checked, true);
        // nodeRequest(treeId, treeNode);
        // onCheckListener(event, treeId, treeNode);
    }
    //展开事件
    var onCheckListener = function(event, treeId, treeNode){
        //判断当前对象是否有加密
        if(treeNode.encrypted_flag && treeNode.checked){  //如果有密码并且勾选了
            //如果有加密则验证密码
            //包含有数据加码
            bootbox.prompt({ 
                title: LANG.UI_VM_INPUT_DB_ENCRY_PWD, 
                inputType: 'password',
                callback: debounce(function (pwdResult) {
                    if(pwdResult == ""){
                        UIToastr.showWarning(LANG.UI_MACHINE_OS_TIMEPOINT_PWD, LANG.UI_KUBE_ENTER_PASSWORD_TO_CONFIRM);
                        return false;
                    }
                    if(pwdResult == null) {
                        zTreeAgent.checkNode(treeNode, false, false, false);
                        //清空已选择内容
                        $('#data_table_namespace').bootstrapTable('load', []);
                        $('#data_table_app').bootstrapTable('load', []);
                        return;
                    };
                    //获取密码
                    let password = btoa(pwdResult);
                    let requestList = {
                        'timepoint_uuid' : treeNode.timepoint_uuid,
                        'password': password,
                    };
                    let checkFlag = false;
                    //获取密码是否正确
                    var requestData  = function(data){
                        if(data.success){
                            //密码验证成功不提示
                            // $("#"+node.timepoint_uuid+"_password").attr('pass','true');
                            checkFlag = true;
                            // passwordList[treeNode.timepoint_uuid] = password;
                            nodeRequest(treeId, treeNode);
                            UIToastr.showSuccess(LANG.UI_MACHINE_OS_TIMEPOINT_PWD, data.message);
                        }else{
                            //密码验证失败给出失败的提示
                            // $("#"+node.timepoint_uuid+"_password").attr('pass','false');
                            UIToastr.showWarning(LANG.UI_MACHINE_OS_TIMEPOINT_PWD, LANG.UI_KUBE_PASSWORD_VERIFICATION_FAILED);
                        }
                    }
                    //初始化备份源
                    pAjaxRequest(requestList, '/api/v1/jobs/password_check', "GET", requestData, false);
                    if(checkFlag){
                        $(this).modal('hide');
                    }
                    return checkFlag
                },300,false)
            });


        }else{
            nodeRequest(treeId, treeNode);
        }
       
    }

    var zTreeOnClick = function(event, treeId, treeNode) { 
        var treeObj = $.fn.zTree.getZTreeObj(treeId);
		if (treeNode.open) {
			treeObj.expandNode(treeNode, false, false, true,true); // 收拢节点
		} else {
			treeObj.expandNode(treeNode, true, false, true,true); // 展开节点
		}
		//判断当前节点是否被勾选
		if(treeNode.checked){
			//勾选当前节点
			treeObj.checkNode(treeNode, false, true, true); 
		}else{
			//勾选当前节点
			treeObj.checkNode(treeNode, true, true, true); 
		}
    };


    //初始化第二步内容
    // var initSecondPVC = function(){
    //     //恢复到集群
    //     $("#target_cluster").val("");
    //     // $("#namespace_redirect").val(1);
    //     // $(".add-card-button-div").hide();
    //     // $("#coverPvc").bootstrapSwitch('state', true);
    //     // $(".pvc_div").hide();
    //     // $("#card-container").empty();
    // }



    //所有节点相关异步请求事件
    var nodeRequest = function (treeId,treeNode) {

        if (treeNode.children == undefined || treeNode.children.length == 0) {
            havechildren = false;
        } else {
            havechildren = true;
        }
        //获取树对象
        var treeObj = $.fn.zTree.getZTreeObj("agent_tree");


        //获取时间点
        if (treeNode.type == 1 && !havechildren) {
            Metronic.blockUI({target: '.agent_tree_div',animate: true,cenrerY: true,});
            var cluster_uuid = treeNode.cluster_uuid;
            var requestClusterTimepoint = function (d) {
                treeObj.addNodes(treeNode, d.data, false);
                Metronic.unblockUI('.agent_tree_div');
            }
            let dataList = {};
            dataList.storage_uuid = "";
            dataList.task_uuid = treeNode.task_uuid;
            dataList.recoverflag = true;
            dataList.dataflag = false;
            pAjaxRequest(dataList, "/api/v1/kubernetes/restore_data/task/"+treeNode.task_uuid, "GET", requestClusterTimepoint, true);
        }

        //为完备点或增备点时
        if((treeNode.type == 2) || (treeNode.type == 3 && !havechildren)){
            if(treeNode.checked){
                $("#step1tips").hide();
                //当勾选的时候清空第二步内容
                // initSecondPVC();
                //清空表格数据
                $('#data_table_namespace').bootstrapTable('load', []);
                $('#data_table_app').bootstrapTable('load', []);
                //表格展示
                if(treeNode.by_type == 1){
                     //按命名空间备份
                     $("#data_table_namespace_div").show();
                     $("#data_table_app_div").hide();
                }else{
                    //按应用备份
                    $("#data_table_namespace_div").hide();
                    $("#data_table_app_div").show();
                }
                //这里根据类型来判断表格展示类型
                var resource = treeNode.resource;
                //获取app列表
                var app = resource.app;
                //namespace
                var namespace = resource.namespace;
                //获取pvc资源
                var pvcs = resource.pvcs;
               
                
                //装入命名空间列表
                namespaceList = [];
                if(treeNode.by_type == K8S_BY_TYPE.APPLICATION){
                    //按应用备份
                    for (let i=0;i<app.length;i++){
                        namespaceList.push(app[i].namespace);
                    };
                }else{
                    for (let i=0;i<namespace.length;i++){
                        namespaceList.push(namespace[i].namespace);
                    };
                }

                recoverPVCList = [];
                for (var i = 0; i < pvcs.length; i++) {
                    var pvc_each = {};
                    pvc_each.namespace= pvcs[i].namespace;
                    pvc_each.name = pvcs[i].name;
                    pvc_each.size = pvcs[i].size;
                    pvc_each.pvc_storage_class = pvcs[i].pvc_storage_class;
                    pvc_each.ns_name = ""; //先统一赋值从命名 命名空间的值为空
                    recoverPVCList.push(pvc_each);
                }


                //命名空间列表去重
                namespaceList = namespaceList.filter(function(item,index,array){
                    return array.indexOf(item,0) === index;
                });
                //取到所有命名空间后初始化第二步的命名空间重定向
                // initNamespace();
                //获取集群资源
                var cluster = resource.cluster;
                if(cluster.length != 0){
                    cluster[0].pvcs = [];
                    cluster[0].checkbox = true;
                    //集群默认不选中
                    if(cluster[0].name == "CLUSTER"){
                        cluster[0].checkbox = false;
                    }
                    $('#data_table_app').bootstrapTable('append', cluster[0]);
                    $('#data_table_namespace').bootstrapTable('append', cluster[0]);
                }

                
                if(treeNode.by_type == K8S_BY_TYPE.APPLICATION){
                    //按应用备份
                    for (let i=0;i<app.length;i++){
                        //获取当前数据
                        let app_object = app[i];
                        app_object.checkbox = true;
                        //获取该app拥有的pvc资源有哪些
                        app_object.pvcs = getPVCBelongTo(pvcs,treeNode.by_type,app_object.app);
                        $('#data_table_app').bootstrapTable('append', app_object);
                        $('#data_table_app').bootstrapTable('scrollTo', 'bottom')
                        $('#data_table_namespace').bootstrapTable('load', []);
                    }
                }else{
                    for (let j=0;j<namespace.length;j++){
                         //获取当前数据
                         let namespace_object = namespace[j];
                         namespace_object.checkbox = true;
                         //获取该app拥有的pvc资源有哪些
                         namespace_object.pvcs = getPVCBelongTo(pvcs,treeNode.by_type,"",namespace_object.namespace);
                        $('#data_table_namespace').bootstrapTable('append', namespace_object);
                        $('#data_table_namespace').bootstrapTable('scrollTo', 'bottom')
                        $('#data_table_app').bootstrapTable('load', []);
                    }
                }
            }else{
                // if(!havechildren){
                    //清空表格数据
                    $('#data_table_namespace').bootstrapTable('load', []);
                    $('#data_table_app').bootstrapTable('load', []);
                // }

            }

        }
    }

    //获取该命名空间或应用下有哪些pvc资源
    var getPVCBelongTo = function(pvc,by_type,app_name = "",namespace_name = ""){
        var info = [];
        //循环pvc
        for(let i=0;i<pvc.length;i++){
            //获取当前pvc
            let pvc_object = pvc[i];
            //获取app_list
            let app_list = pvc_object.app_list;
            //获取命名空间
            let namespace = pvc_object.namespace;
            if(by_type == 1){
                //按命名空间备份
                if(namespace == namespace_name ){
                    info.push(pvc_object);
                }
            }else{
                //按应用备份
                //如果app_name存在于app_list中
                for (let i=0;i<app_list.length;i++){
                    if(app_list[i].app_name == app_name){
                        info.push(pvc_object);
                    }
                }
            }
        }
        return info;
    }

    //初始化待恢复数据表格
    var initTable = function (){
        //这里初始化2个表格 一个是按命名空间的 一个是按app的

        var pvcToHtml = function(value){
            if(value.pvcs.length == 0){
                return "--";
            }
            //循环value
            var html = ""
            for(let i=0;i<value.pvcs.length;i++){
                let pvcStr = "";
                pvcStr += "namespace: "+value.pvcs[i].namespace+";\n";
                pvcStr += "kind: "+value.pvcs[i].kind+";\n";
                pvcStr += "group: "+value.pvcs[i].group+";\n";
                pvcStr += "version: "+value.pvcs[i].version+";\n";
                html += "<span title='"+pvcStr+"'>"+value.pvcs[i].name+";</span><br>";
            }
            
            return html;
        }

        var onPostBodyFunc = function(by_type){
            function handleRowClick(event){
                event.preventDefault(); // 阻止默认的链接跳转行为
                // 获取 data-index 属性的值
                var dataIndex = $(this).data('index');

                // 使用 getData 方法获取最新的行数据
                if(by_type == 1){
                    //命名空间
                    var rowData = $('#data_table_namespace').bootstrapTable('getData')[dataIndex];
                }else{
                    var rowData = $('#data_table_app').bootstrapTable('getData')[dataIndex];
                }

                // 获取对应的 tr 元素
                var $tr = $(this).closest('tr');

                // 获取所有的 td 元素
                var $tds = $tr.find('td');

                //获取tbody下的 tr 元素
                var $tbodytrs = $(this).parent().parent();

                //改变图标样式,vicon-a-Leftzuo和vicon-a-Upshang轮流切换
                var iconClass = $tds.find('i').attr('class');
                if (iconClass === 'viconfont vicon-a-Leftzuo') {
                    $tds.find('i').attr('class', 'viconfont vicon-a-Upshang');
                } else {
                    $tds.find('i').attr('class', 'viconfont vicon-a-Leftzuo');
                }

                 //在下一次触发时如果$tbodytrs已经添加过内容则已经有内容这改为show和hide事件
                 //获取$tbodytrs的兄弟元素的
                 var $nexttrs = $tbodytrs.next();
                 //获取$nexttrs的类
                 var nexttrsClass = $nexttrs.attr('class');
                 if(nexttrsClass == 'detail-view'){
                    //获取nexttrs是否显示
                    var isShow = $nexttrs.is(':visible');
                    if(isShow){
                        $nexttrs.hide();
                    }else{
                        $nexttrs.show();
                    }
                 }else{
                     //在td元素后面新增一个div元素
                     //这里table的id需要唯一
                     //命名空间 则为  {namespace}_sub_table
                     //应用则为 {namespace}_{app}_{app_type}_sub_table
                     //得到sub的id
                     let tableID = "_sub_table";
                     if(by_type == 1){
                        tableID = rowData.namespace + tableID;
                     }else{
                        tableID = rowData.namespace+"_"+rowData.app+"_"+rowData.app_type + tableID;
                     }
                    $tbodytrs.after(`
                        <tr class="detail-view">
                            <td colspan="12">
                                <table id="${tableID}"></table>
                            </td>
                        </tr>
                        `)

                        init_subTable(tableID,rowData,by_type, dataIndex)

                        //初始化后判断当前行是否已经被勾选
                        if(!$tbodytrs.find('input[type="checkbox"]').prop('checked')){
                            //如果当前行没有被勾选则展开后取消子表格的所有勾选
                            // 等待表格初始化完成后再取消勾选
                            $('#'+tableID).one('post-body.bs.table', function() {
                                $(this).bootstrapTable('uncheckAll');
                            });
                        }
                 }
                
            }
            if(by_type == 1){
                 // 绑定新的点击事件
                $('#data_table_namespace').on('click', 'a[data-index]', handleRowClick);
            }else{
                $('#data_table_app').on('click', 'a[data-index]', handleRowClick);
            }
        }



        //初始化按命名空间的
        var options_namespace = {
            data: {rows:[],total:0},
            showExport: false,
            showColumns: false,
            resizable: false,
            // detailView: true,
            showJumpTo: false,
            pagination:false,
            clickToSelect: false,
            onPostBody: onPostBodyFunc(1),
            columns:[
                {
                    checkbox:true,
                    field: 'checkbox',
                    sortable: false,

                    formatter:function (value, row, index){
                        if(value == true){
                            return true;
                        }else{
                            return false;
                        }
                    }
                },
                {
                    field: 'name',
                    title: LANG.UI_KUBE_NAME,
                    sortable: false,
                    align: 'center',
                    formatter:function (value, row, index){
                        // vicon-a-Upshang
                        return '<a  data-index="' + index + '"><i class="viconfont vicon-a-Leftzuo"></i> ' + value + '</a>';
					}
                },

                {
                    field: 'namespace',
                    title: LANG.UI_STORAGE_TYPE,
                    sortable: false,
                    align: 'center',
                    formatter:function (value, row, index){
                        if(row.type == 5){
                            return LANG.UI_KUBE_CLUSTER_RESOURCES;
                        }else{
                            return LANG.UI_KUBE_NAMESPACE;
                        }
					}
                },
                {
                    field: 'namespace',
                    title: LANG.UI_KUBE_NAMESPACE,
                    sortable: false,
                    align: 'center',
                    // formatter:function (value, row, index){
                    //     if(row.type == 5){
                    //         return '集群资源';
                    //     }else{
                    //         return '命名空间';
                    //     }
					// }
                },
                {
                    field: 'pvcs',
                    title: LANG.UI_KUBE_PVC_PERSISTENT_VOLUME,
                    sortable: false,
                    align: 'center',
                    formatter:function (value, row, index){
                        //得到当前数据
                        return pvcToHtml(row);
					}
                },
            ],

        }
        $('#data_table_namespace').baseTableConfig().init(options_namespace);

        //初始化按app的
        var options_app = {
            data: {rows:[],total:0},
            showExport: false,
            showColumns: false,
            resizable: false,
            // detailView: true,
            pagination:false,
            clickToSelect: false,
            onPostBody:  onPostBodyFunc(2),
            columns:[
                {
                    checkbox:true,
                    field: 'checkbox',
                    sortable: false,
                    formatter:function (value, row, index){
                        if(value == true){
                            return true;
                        }else{
                            return false;
                        }
                    }
                },
                {
                    field: 'app',
                    title: LANG.UI_KUBE_NAME,
                    sortable: false,
                    align: 'center',
                    formatter:function (value, row, index){
                        if(value == ""){
                            value = row.name;
                        }
                        return '<a  data-index="' + index + '"><i class="viconfont vicon-a-Leftzuo"></i> ' + value + '</a>';
					}
                    
                },
                {
                    field: 'app',
                    title: LANG.UI_PUBLIC_NET_BUS_TYPE,
                    sortable: false,
                    align: 'center',
                    formatter:function (value, row, index){
                        if(row.type == 5){
                            return LANG.UI_KUBE_RESOURCE_DETAILS;
                        }else{
                            return LANG.UI_KUBE_APPLICATION;
                        }
					}
                    
                },
                {
                    field: 'namespace',
                    title: LANG.UI_KUBE_NAMESPACE,
                    sortable: false,
                    align: 'center',
                    formatter:function (value, row, index){
                        if(value == ""){
                            value = "--";
                        }
                        return value;
					}
                },
                {
                    field: 'pvcs',
                    title: LANG.UI_KUBE_PVC_PERSISTENT_VOLUME,
                    sortable: false,
                    align: 'center',
                    formatter:function (value, row, index){
                        //得到当前数据
                        return pvcToHtml(row);
					}
                },
            ],

        }
        $('#data_table_app').baseTableConfig().init(options_app);

        //初始化两个表格勾选集群事件
        $("#data_table_app,#data_table_namespace").on('check.bs.table uncheck.bs.table', function (e, row) {
             // 检查事件实际发生的表格是否是当前绑定的表格
            if (e.currentTarget !== e.target) {
                // 事件来自子元素（子表格），忽略
                return;
            }
            if (row && row.id === "CLUSTER") {
                //如果被勾选
                if (row.checkbox) {
                    //如果被勾选
                    UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_KUBE_RECOVER_CLUSTER_RESOURCES_CAUTION);
                }
            }
            let tableID_sub_table = "_sub_table";
             // 获取触发事件的表格 ID
            let table_dom_id = $(this).attr('id');
            
            if (table_dom_id === 'data_table_app') {
                // 这是按应用备份的表格
                tableID_sub_table = row.namespace+"_"+row.app+"_"+row.app_type + tableID_sub_table;
            } else if (table_dom_id === 'data_table_namespace') {
                // 这是按命名空间备份的表格
                tableID_sub_table = row.namespace + tableID_sub_table;
            }
            //查找是否页面中有id为tableID的元素
            var subTable = document.getElementById(tableID_sub_table);
            if(subTable && $('#' + tableID_sub_table).data('bootstrap.table')){
                //如果该元素存在并且是bootstrap-table对象
                if (row.checkbox) {
                    //如果勾选了就把该子表格所有元素全勾选了
                    $('#' + tableID_sub_table).bootstrapTable('checkAll');
                }else{
                    //取消全部勾选
                    $('#' + tableID_sub_table).bootstrapTable('uncheckAll');

                }

            }

            // console.log(row)
        });

    }


    //初始化二级表
    var init_subTable = function (tableID,rowData,by_type,dataIndex) {
        //初始化额外数据
        var extraParams = {};
        Metronic.blockUI({target: '#k8streeDiv',animate: true,cenrerY: true,});
        var options_sub_params = function(){
            var treeObj = $.fn.zTree.getZTreeObj("agent_tree");
            var nodes = treeObj.getCheckedNodes();
            let resultData = {};
            resultData.timepoint_uuid = nodes[0].timepoint_uuid;
            resultData.namespace = rowData.namespace;
            resultData.cluster_uuid = nodes[0].cluster_uuid;
            resultData.app = rowData.app;
            resultData.app_type = rowData.app_type;
            resultData.list_offset = 0;
            resultData.list_limit = 0; //一次性请求300条
            return resultData;
        }
        //初始化按app的
        var options_sub = {
            vin_url: "/api/v1/kubernetes/restore_data/resource",
            vin_method:"GET",
            vin_params: function () {
                return options_sub_params();
            },
            responseHandler: function(res) {
                // 提取额外的参数
                if(res.success){
                    //如果res.data是一个数组并且长度为0
                    // if(Array.isArray(res.data) && res.data.length == 0){
                    //     $("#" + tableID).bootstrapTable('load', []);
                    //     $("#" + tableID).bootstrapTable('hideLoading');
                    // }
                    extraParams = res.data.extraParams;
                    // let result = {
                    //     'rows': res.data.rows,
                    //     'total': res.data.total,
                    // };
                    $("#" + tableID).bootstrapTable('load', res.data.rows);
                    $("#" + tableID).bootstrapTable('hideLoading');
                    // return res.data;
                }else{
                    //如果失败
                    UIToastr.showWarning(LANG.UI_KUBE_GET_RESOURCE_DETAILS, res.message);
                    Metronic.unblockUI('#k8streeDiv');
                }
            },
            showExport: false,
            showColumns: false,
            resizable: false,
            pagination:false,
            checkboxHeader: false,
            columns: [
                {
                    checkbox: true,
                    field: 'checkbox',
                    sortable: false,
                    formatter: function (value, row, index) {
                        if (value == true) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                {
                    field: 'name',
                    title: LANG.UI_KUBE_RESOURCE_NAME+'(name)',
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'kind',
                    title: LANG.UI_KUBE_RESOURCE_TYPE+'(kind)',
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'version',
                    title: LANG.UI_KUBE_RESOURCE_VERSION+'(version)',
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'group',
                    title: LANG.UI_KUBE_RESOURCE_SUBGROUP+'(group)',
                    sortable: false,
                    align: 'center',
                },
                {
                    // field: 'update_time',
                    title: LANG.UI_KUBE_RESOURCE_DETAILS,
                    sortable: false,
                    align: 'center',
                    events: operateEvents,
                    // type: 'operation',
                    formatter: function (value, row, index, field) {
                        var button = '<div class="btn-group  ">';
                        if (index > 4) {
                            button = '<div class="btn-group   dropup">';
                        }
                        button += '<a class="resource_details">'+LANG.UI_KUBE_RESOURCE_DETAILS+'</a></div>'
                        return button;

                    }
                },
            ],

        }
        $("#"+tableID).baseTableConfig().init(options_sub);

        //新增一个表格勾选事件
        // $("#"+tableID).on('check.bs.table', function (e, row) { 
        //     //勾选父级当前行，但不触发父级表的勾选事件
        //     if(by_type == 1){
        //         $('#data_table_namespace').off('check.bs.table')
        //             .bootstrapTable('check', dataIndex)
        //             .on('check.bs.table', arguments.callee);
        //    }else{
        //         $('#data_table_app').off('check.bs.table')
        //             .bootstrapTable('check', dataIndex)
        //             .on('check.bs.table', arguments.callee);
        //    }
            
        // });



        $('#' + tableID).off('post-body.bs.table').on('post-body.bs.table', function () {
            //获取当前表格额外数据
            //获取extraParams的数据
            let list_total = extraParams.list_total;
            let list_offset = extraParams.list_offset;
            let list_size = extraParams.list_size;
            let list_remains = extraParams.list_remains;
            if(list_total == 0){
                let moreHtml = '<tr><td colspan="100" style="text-align: center;">'+LANG.UI_KUBE_NO_DATA_AVAILABLE+'</td></tr>';
                $("#" + tableID).find('tbody').append(moreHtml);

            }
            if(list_remains == 0){
                Metronic.unblockUI('#k8streeDiv');
                return;
            }else{
                // 在表格数据中追加一条加载更多的链接
                let moreHtml = '<tr class="add_more_resource"><td colspan="100" style="text-align: center;"><div><a>'+LANG.UI_KUBE_LOAD_MORE+'('+LANG.UI_KUBE_TOTAL+list_total+LANG.UI_KUBE_REMAINING+list_remains+LANG.UI_KUBE_ITEMS+')</a></div></td></tr>';
                $("#" + tableID).find('tbody').append(moreHtml);
            }
          
            Metronic.unblockUI('#k8streeDiv');
        });
        
        //点击add_more_resource里面的a标签后触发事件
        $("#" + tableID).on('click', '.add_more_resource a', function () {
            Metronic.blockUI({target: '#k8streeDiv',animate: true,cenrerY: true,});
            var requestDetails = function(d){
                //更新extraParams值
                extraParams = d.data.extraParams;
                //移除add_more_resource
                $("#" + tableID).find('.add_more_resource').remove();
                //将获取的数据追加到列表中
                $("#" + tableID).bootstrapTable('append', d.data.rows);
                Metronic.unblockUI('#k8streeDiv');
            }

            let dataList = options_sub_params();
            //需要改变数据中的偏移量和起始偏移量
            dataList.list_offset = parseInt(extraParams.list_total)-parseInt(extraParams.list_remains);
            pAjaxRequest(dataList, "/api/v1/kubernetes/restore_data/resource", "GET", requestDetails,true);

        });
      
    }


    //操作监听事件
    var operateEvents = {
        //资源详情
        'click .resource_details': function (e, value, row, index) {
            Metronic.blockUI({target: '#k8streeDiv',animate: true,cenrerY: true,});
            var requestClusterresourceDetails = function(d){
                if(d.success){
                    //获取主题内容高度
                    var body_height = $("#resourceDetailsDrawer .drawer-body").height();
                    //得到代码框的高度
                    var code_msg_height = body_height-10*2;
                    $("#resourceDetails").css('height',code_msg_height+"px");
                    $("#resourceDetails").val(d.data);
                    $("#resourceDetailsDrawer").drawer('show');
                }else{
                    UIToastr.showWarning(LANG.UI_KUBE_GET_RESOURCE_DETAILS, data.message);
                }
              
                Metronic.unblockUI('#k8streeDiv');
            }

            let dataList = {};
            //获取集群uuid
            dataList.cluster_uuid = row.cluster_uuid;
            //获取时间点
            dataList.timepoint_uuid = row.timepoint_uuid;
            //获取密钥
            dataList.decryption_key = "";
            //获取命名空间
            dataList.namespace = row.namespace;
            //获取资源分组
            dataList.group = row.group;
            //获取资源版本
            dataList.version = row.version;
            //获取资源类型
            dataList.kind = row.kind;
            //获取资源名称
            dataList.name = row.name;
            //获取时间点的节点
            var treeObj = $.fn.zTree.getZTreeObj("agent_tree");
            var nodes = treeObj.getCheckedNodes();
            //初始化第三步传输网络
            dataList.real_node_uuid = nodes[0].real_node_uuid;
            pAjaxRequest(dataList, "/api/v1/kubernetes/restore_data/resource/details", "GET", requestClusterresourceDetails,true);
        },

    };


    //初始化集群选择
    var initCluster = function(){
        var initClusterRequest = function(d){
            //获取所有集群
            var clusterList = d.data.rows;
            let html = '<option value="">'+LANG.UI_RECOVERY_AWS_PLEASE_SELECT_TIPS+'</option>';
            for(let i=0;i<clusterList.length;i++){
                if(clusterList[i].online_flag.online_flag != 1){
                    continue;
                }
                html +='<option value="'+clusterList[i].cluster_uuid+'">'+clusterList[i].cluster_name+'</option>'
            }
            $("#target_cluster").html(html);
        }
        pAjaxRequest({}, "/api/v1/kubernetes/cluster", "GET", initClusterRequest,false);


    }

    var searchAgent = function() {
		var value = $('#searchVal').val(); // 获取输入值并转换为小写
		// 输入验证
		var nodes = zTreeAgent.getNodes();
		if (!nodes || nodes.length == 0) return;
	
		// 隐藏所有节点
		zTreeAgent.hideNodes(nodes);
	
		// 找到匹配的节点集合
		let nodeParamList = zTreeAgent.getNodesByParamFuzzy('name', value);
		//如果没搜索到则给出提示
        if(nodeParamList.length == 0){
            $("#agent_tree").hide();
            $(".no_search_result").show();
        }else{
            $("#agent_tree").show();
            $(".no_search_result").hide();
        }
		// 找到父节点并添加到展示中，但不添加父节点的其他子节点
		var findParent = function(treeObj, node) {
			var pNode = node.getParentNode();
			if (pNode != null) {
				nodeParamList.push(pNode);
				// 隐藏该父节点下除当前节点外的其他子节点
				if (pNode.children && pNode.children.length > 0) {
					for (var i = 0; i < pNode.children.length; i++) {
						if (pNode.children[i] !== node) {
							zTreeAgent.hideNode(pNode.children[i]);
						}
					}
				}
		
				// 继续向上查找
				findParent(treeObj, pNode);
			}
		};
	
		for (var n in nodeParamList) {
			findParent(zTreeAgent, nodeParamList[n]);
		}
		// 排序
		nodeParamList = $.unique(nodeParamList.sort());
		// 展示找到的节点
		zTreeAgent.showNodes(nodeParamList);

	};



    var initListener = function(){
        
        //初始化勾选框的颜色
        $('.control-label input').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green'
        });

        //聚焦输入搜索事件
        // 聚焦时添加红色背景
        $('.search_glass_input').focus(function() {
            $('.search_glass').addClass('search_glass_ing');
            $('.icon-search').addClass('icon-search_ing');
        });

        // 失焦时移除红色背景
        $('.search_glass_input').blur(function() {
            $('.search_glass').removeClass('search_glass_ing');
            $('.icon-search').removeClass('icon-search_ing');
        });

        //搜索可选内容
		$('#searchVal').on('input propertychange',function(){
			searchAgent();
		})

        // //搜索确认事件
        // $('#searchSubmit').on('click',function (){

            
        //     //如果为空则初始化
        //     if($("#searchVal").val() == ""){
        //         initAgentTree();
        //     }else{
        //         initSearchAgentTree();
        //     }

        // })
        // const searchVal = document.getElementById("searchVal");
        // searchVal.addEventListener("keyup", function(event) {
        //     if (event.keyCode === 13) {
        //         if (searchVal.value === "") {
        //             initAgentTree();
        //         } else {
        //             initSearchAgentTree();
        //         }
        //     }
        // });

        //加密传输l开关切换事件
		$("#encrypttransfer").on('switchChange.bootstrapSwitch', function(even,state){
			if(state){
				$(".encrypt_method_div").show();
			}else{
				$(".encrypt_method_div").hide();
			}
		})
        // 恢复集群改变事件
        $("#target_cluster").on('change',function(){
            Metronic.blockUI({target: '#k8srecovercontent',animate: true,cenrerY: true,});
            let cluster_uuid = $(this).val();
            if(cluster_uuid != ""){
                initNamespaceTable();
                initPvcTable();
                // $(".namespace_div").show();
                // $(".pvc_div").show();
            }else{
                $(".namespace_div").hide();
                $(".pvc_div").hide();
                Metronic.unblockUI('#k8srecovercontent');
            }
        })


        $('#Socket-thread').spinner({value: 3, step: 1, min: 1,max: 8});//传输线程
        //判断传输线程权限
		if(CONF.FUNCTIONS.includes('multithread')){
			//如果授权线程
			$(".transfer_threads_div").show();
		}else{
			//默认值为1
			$('#Socket-thread').spinner({value: 1, step: 1, min: 1,max: 8});//传输线程
			$(".transfer_threads_div").hide();
		}


        $('#transfer_threads').on('change',function (){
			if(this.value > 8 || this.value == "" || this.value == 0){
				$('#Socket-thread').spinner('value',3);
				$('#transfer_threads').val(3);
			}
		})
        inintDatatimePicker();

        // 选择恢复方式
		$('#recovertype').on('change',function(){
			if('1' == this.value){
			    // resultData.type_info.strategy = {};
				$('.setOnceTime').hide();
			}else if("4" == this.value){
				$('.setOnceTime').show();
			}
			getTimeDes();
		});
        //保持原命名空间切换事件
        $('#keep_old_namespace').on('switchChange.bootstrapSwitch', function(){
            //切换的时候初始化命名空间详请
            initNamespaceTable();
            initPvcTable();
        });
         //保持原PVC配置切换事件
         $('#keep_old_pvc').on('switchChange.bootstrapSwitch', function(){
            //切换的时候初始化命名空间详请
            initPvcTable()
        });
        // //切换目标命名命名空间的时候也初始化pvc配置
        // $(document).on('select2:select', '.namespace_select', function (e) {
        //     alert(1);
        //     initPvcTable();
        // });

        




        initScriptConfig();
        initJobName();

    }

    var inintDatatimePicker = function () {
        //设置时间
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            //英文独有的
           $(".form_datetime").datetimepicker({
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-mm-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
            startDate: new Date()
           });
        }else{
           $(".form_datetime").datetimepicker({
                 language:  'zh-CN', 
                 autoclose: true,
                 isRTL: Metronic.isRTL(),
                 format: "yyyy-MM-dd hh:ii:ss",
                 pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                 startDate: new Date()
           });
        }
        $('#resetdate').on('click', function () {
            $('#oncetime').val('');
            getTimeDes();
        });
        $('#oncetime').on('change', getTimeDes);
    }

    // 获取时间策略信息描述
	var getTimeDes = function () {
		var des = '';
		des = $('#recovertype').find("option:selected").text();
		if ($('#recovertype').val() == 4) {
			des += '，' + LANG.UI_JOB_TIMING_RECOVER_TIME + "：" + $('#oncetime').val();
		}
		$('.recoveryTimeDes').html(des);
		$('.recoveryTimeDes').prop('title', des);
	}




    var initJobName = function(){
        var requestData  = function(data){
            if(data.success){
                //获得任务名
                $("#jobname").val(data.data);
                
            }else{
                UIToastr.showWarning(LANG.UI_MACHINE_OS_GET_BACKUP_JOB_NAME, data.message);
            }
        }
        pAjaxRequest({}, "/api/v1/kubernetes/jobs_recover_name", "GET", requestData, true);
}






    


    //初始化高级配置
	var initScriptConfig = function(){
        //初始化4个插件
        $(".custom_script_ace").initVinScript({
            class:"custom_script_ace",
            include_list:[3],
            script_details: [
                {
                    "script_method": "2",
                    "script_type": 3,
                    "script_uuid": 0,
                    "script_content": "",
                    "script_name":"",
                }
            ],
            display_tab:"display:none;",
            display_script_method:"display:none;",
            display_script_type:"display:none;",
            display_script_content:"display:none;",
        });
        $(".before_script_ace").initVinScript({
            class:"before_script_ace",
            include_list:[1],
            script_details: [
                {
                    "script_method": "1",
                    "script_type": 1,
                    "script_uuid": 0,
                    "script_content": "",
                    "script_name":"",
                }
            ],
            display_tab:"display:none;",
            display_script_method:"display:none;",
            display_script_type:"display:none;",
            display_script_content:"display:none;",
            display_script_list:"display:none;",
        });
        $(".after_script_success_ace").initVinScript({
            class:"after_script_success_ace",
            include_list:[1],
            script_details: [
                {
                    "script_method": "1",
                    "script_type": 1,
                    "script_uuid": 0,
                    "script_content": "",
                    "script_name":"",
                }
            ],
            display_tab:"display:none;",
            display_script_method:"display:none;",
            display_script_type:"display:none;",
            display_script_content:"display:none;",
            display_script_list:"display:none;",
        });
        $(".after_script_failure_ace").initVinScript({
            class:"after_script_failure_ace",
            include_list:[1],
            script_details: [
                {
                    "script_method": "1",
                    "script_type": 1,
                    "script_uuid": 0,
                    "script_content": "",
                    "script_name":"",
                }
            ],
            display_tab:"display:none;",
            display_script_method:"display:none;",
            display_script_type:"display:none;",
            display_script_content:"display:none;",
            display_script_list:"display:none;",
        });
       

        //初始化hooks事件
        var initHooksShow = function (){
            //得到Hook的值
            var hooktype = $("#hookstype").val();
            switch (hooktype){
                case "0":
                    $(".shDiv").hide();
                    $(".csDiv").hide();
                    $(".custom_div").hide();
                    $(".sh_div").hide();
                    break
                case "SH":
                    $(".shDiv").show();
                    $(".csDiv").hide();
                    $(".custom_div").hide();
                    $(".sh_div").show();
                    break
                case "CUSTOMSCRIPT":
                    $(".shDiv").hide();
                    $(".csDiv").show();
                    $(".custom_div").show();
                    $(".sh_div").hide();
                    break
            }
        }


        //执行容器环境事件
        var initinPodShow = function (){
            //得到执行容器环境的值
            var inPod = $("#inPod_mode").val();
            switch (inPod){
                case "ALL":
                    $(".specified_pod").hide();
                    $(".matched_pod").hide();
                    $(".image_pod").hide();
                    break;
                case "SPECIFIED":
                    $(".specified_pod").show();
                    $(".matched_pod").hide();
                    $(".image_pod").hide();
                    break;
                case "MATCHED":
                    $(".specified_pod").hide();
                    $(".matched_pod").show();
                    $(".image_pod").hide();
                    break;
                case "CUSTOM_IMAGE":
                    $(".specified_pod").hide();
                    $(".matched_pod").hide();
                    $(".image_pod").show();
                    break;
            }
        }
        //初始化hooks脚本
        $("#hookstype").on('change',function (){
            initHooksShow();
        })
        //执行容器事件
        $("#inPod_mode").on('change',function (){
            initinPodShow();
        })
	

	}




    var initDataChangeListeners = function(){
        // initSpeedListeners();
        initHighListeners();
    }
    //加载策略对应描述
    var initStrategyDes = function(){
        // initSpeedStrategyDes();
        initHighStrategyDes();
    }

    var initHighListeners = function(){
        $('#backupThreadNum').on('input propertychange', function(){
            initHighStrategyDes();
        });
        $('.spinner-up').on('click', function(){
            initHighStrategyDes();
        });
        $('.spinner-down').on('click', function(){
            initHighStrategyDes();
        });
    }



        //初始化命名空间
        var initNamespaceTable = function(){
            Metronic.blockUI({target: '#k8srecovercontent',animate: true,cenrerY: true,});
            if(namespaceList.length != 0){
                $(".namespace_div").show();
            }else{
                $(".namespace_div").hide();
            }
            //先检查是否之前有初始化过该表格,如果初始化了则销毁重新初始化
            if ($('#namespace_config').data('bootstrap.table')) {
                $('#namespace_config').bootstrapTable('destroy');
            }


            var frontendData = [];
            var targetNamespaceList = getNamespaceList();
            //需要组装命名空间数据体
            if(namespaceList.length != 0){
                //当存在命名空间的时候需要处理数据
                for(var i=0;i<namespaceList.length;i++){
                    var eachNamespace = {};
                    var namespace = namespaceList[i];
                    eachNamespace.namespace_name = namespace;
                    eachNamespace.target_namespace_name = {};
                    //所有命名空间列表
                    eachNamespace.target_namespace_name.namespace_name_list = targetNamespaceList;
                    //原命名空间
                    eachNamespace.target_namespace_name.namespace = namespace;
                    //新命名空间+时间
                    eachNamespace.target_namespace_name.namespace_name_timeStr = namespace+"-"+timepointStr;
                    //是否保持原命名空间
                    eachNamespace.target_namespace_name.type = $("#keep_old_namespace").get(0).checked;
                    frontendData.push(eachNamespace);
                }
            }
            //初始化命名空间
            var namespaceTable = {
                data: frontendData,  // 直接传入前端数据
                showExport: false,
                showColumns: false,
                resizable: false,
                pagination:false,
                clickToSelect:false,
                onPostBody: function () {
                    $('#namespace_config').find('tbody tr').each(function (index) {
                        var row = $('#namespace_config').bootstrapTable('getData')[index];
                        if (!row) return;
                        //获取select填入值
                        var selectedInput = row.target_namespace_name.namespace_name_timeStr;
                    
                        // 只查找 <select> 元素，并且具有 .select2 类
                        var $select = $(this).find('select.select2');
                        if ($select.length && !$select.data('select2')) {
                            $select.select2({
                                tags: true,
                                minimumResultsForSearch: 1,
                            }).on("select2:open", function () {
                                $(".select2-search__field").attr("placeholder", LANG.UI_KUBE_CUSTOM_NAMESPACE);
                            });
                        }
                        // 添加一个“虚拟”默认项
                        var defaultOptionText = selectedInput; // 比如：'namespace-1-20250507121511'
                        var $defaultOption = new Option(defaultOptionText, defaultOptionText, true, true);

                        $select.append($defaultOption).trigger('change');
                        // 清除默认值，当用户选择其他项时
                        $select.on("select2:select", function (e) {
                            var selectedValue = e.params.data.id;
                            if (selectedValue === defaultOptionText) {
                                return; // 如果还是默认值，不处理
                            }
                            // 用户选择了其他项，移除默认项
                            $select.find("option[value='" + defaultOptionText + "']").remove();
                        });

                    });
                },
                columns: [
                    {
                        field: 'namespace_name',
                        title: LANG.UI_KUBE_ORIGINAL_NAMESPACE,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'target_namespace_name',
                        title: LANG.UI_KUBE_TARGET_NAMESPACE,
                        sortable: false,
                        align: 'center',
                        formatter: function(value,row) {
                           var html = '';
                           //保持原命名空间
                           if(value.type){
                                //打开状态
                                html = value.namespace;
                                return html;
                           }else{
                                //关闭状态
                                //循环
                                for(var i=0;i<value.namespace_name_list.length;i++){
                                    html  += `<option value="${value.namespace_name_list[i]}">
                                                ${value.namespace_name_list[i]}
                                            </option>`
                                }
                                return `<select class="form-control namespace_select select2" style="width:100%;">${html}</select>`;
                           }
                        },
                       
                    },
                ],

            }
            $("#namespace_config").baseTableConfig().init(namespaceTable);
            Metronic.unblockUI('#k8srecovercontent');
        }
        //----------------------------------------------------------------------------------------------
        //初始化pvc配置详请
        var initPvcTable = function(){
            Metronic.blockUI({target: '#k8srecovercontent',animate: true,cenrerY: true,});
            if(recoverPVCList.length != 0){
                $(".pvc_div").show();
            }else{
                $(".pvc_div").hide();
            }
            //先对pvc数据进行处理,将命名空间重命名加入到对应里面去
            let ns_name_list = getNsnameList();
            if (!ns_name_list["valid"]) {
                Metronic.unblockUI('#k8srecovercontent');
                return false; // 返回外层函数
            }
            let ns_name_obj = ns_name_list["ns_rename"];
            //如果对象ns_name_obj的长度为0
            if (Object.keys(ns_name_obj).length != 0){
                //当有值的时候循环对象
                for (var key in ns_name_obj) {
                    if (ns_name_obj.hasOwnProperty(key)) {
                        var value = ns_name_obj[key];
                        //判断key值是否在recoverPVCList中并找到
                        //循环recoverPVCList
                        for(let i=0;i<recoverPVCList.length;i++){
                            if(recoverPVCList[i].namespace == key){
                                //如果找到对应的命名空间则把重命名的命名空间加入到recoverPVCList里面去
                                recoverPVCList[i].ns_name = value;
                            }
                        }
                    }
                }
            }
            //先检查是否之前有初始化过该表格,如果初始化了则销毁重新初始化
            if ($('#pvc_config').data('bootstrap.table')) {
                $('#pvc_config').bootstrapTable('destroy');
            }
            //初始化表格
            var pvc_table = {
                vin_url: "/api/v1/kubernetes/restore_data/pvc",
                vin_method:"POST",
                vin_params: function () {
                    let requestList= {};
                    requestList.cluster_uuid = $("#target_cluster").val();
                    requestList.recoverPVCList = recoverPVCList;
                    return requestList;
                },
                showExport: false,
                showColumns: false,
                resizable: false,
                pagination:false,
                clickToSelect:false,
                onLoadSuccess: function (data) {
                    Metronic.unblockUI('#k8srecovercontent');
                },
                onPostBody: function(){
                     // 确保事件只绑定一次
                    $(document).off('select2:select', '.namespace_select')
                        .on('select2:select', '.namespace_select', function (e) {
                    // console.log("Namespace changed:", $(this).val());
                    initPvcTable();
                    });
                },
                columns: [
                    {
                        checkbox: true,
                        field: 'checkbox',
                        sortable: false,
                        formatter: function (value, row, index) {
                            if (value == true) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    },
                    {
                        field: 'pvc_name',
                        title: LANG.UI_KUBE_PVC_NAME,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'pvc_name_new',
                        title: LANG.UI_KUBE_TARGET_PVC_NAME,
                        sortable: false,
                        align: 'center',
                        formatter: function(value,row) {
                            var html = '';
                            //获取保持原PVC开关
                            var keep_old_pvc =  $("#keep_old_pvc").get(0).checked;
                            if(keep_old_pvc){
                                //打开状态
                                return value.pvc_name;
                            }else{
                                //关闭状态
                                //先判断pvc是否在集群中已经存在
                                var pvc_name = value.pvc_name;
                                var pvc_exists = value.pvc_exists;
                                if(pvc_exists){
                                    //如果存在, 则在原本的pvc上加上时间戳
                                    pvc_name = pvc_name + "-" + timepointStr;
                                }
                                html = `<input type="text" class="form-control pvc_input" value="${pvc_name}" placeholder="${LANG.UI_KUBE_ENTER_TARGET_PVC_NAME}">`
                                return html;
                            }
                           
                         },
                    },
                    {
                        field: 'storage_class',
                        title: LANG.UI_KUBE_STORAGE_CLASS,
                        sortable: false,
                        align: 'center',
                        formatter: function(value,row) {
                            var html = '';
                            //获取保持原PVC开关
                            var keep_old_pvc =  $("#keep_old_pvc").get(0).checked;
                            if(keep_old_pvc){
                                //打开状态
                                var storage_class_exists = value.storage_class_exists;
                                var storage_class = value.storage_class;
                                if(storage_class_exists){
                                    //如果存在相同的
                                    return storage_class;
                                }else{
                                    //如果不存在相同的则还是给出勾选框
                                    html += `<option value="">${LANG.UI_JOB_SELECT}</option>`;
                                    for(var i=0;i<value.storage_class_list.length;i++){
                                        html  += `<option value="${value.storage_class_list[i]}">
                                                    ${value.storage_class_list[i]}
                                                </option>`
                                    }
                                    return `<select class="form-control storage_select select2" style="width:100%;">${html}</select>`;
                                }
                                
                            }else{
                                //关闭状态
                                //判断是否存在
                                var storage_class_exists = value.storage_class_exists;
                                var storage_class = value.storage_class;
                                if(storage_class_exists){
                                    html += `<option value="">${LANG.UI_JOB_SELECT}</option>`;
                                }else{
                                    html += `<option value="" selected>${LANG.UI_JOB_SELECT}</option>`;
                                }
                                for(var i=0;i<value.storage_class_list.length;i++){
                                    //如果存在该值则选中该值
                                    var checked = '';
                                    if(storage_class == value.storage_class_list[i]){
                                        checked = 'selected';
                                    }
                                    html  += `<option ${checked} value="${value.storage_class_list[i]}">
                                                ${value.storage_class_list[i]}
                                            </option>`
                                }
                                return `<select class="form-control storage_select select2" style="width:100%;">${html}</select>`;
                               
                               
                            }
                           
                         },
                    },
                    {
                        field: 'namespace',
                        title: LANG.UI_KUBE_ORIGINAL_NAMESPACE1,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'size',
                        title: LANG.UI_STORAGE_SIZE,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        title: LANG.UI_KUBE_RESET,
                        sortable: false,
                        align: 'center',
                        visible: !($("#keep_old_pvc").get(0).checked),
                        formatter: function (value,row,index,field){
                            var button = '<div class="btn-group">';
                            button +='<div class="btn_operation_vicon">' +
                                '<a class="restore_old"><i class="viconfont vicon-chexiao"></i></a>' +
                            '</div>';
                            button += '</div>';
                            return button;
                        },
                        events:{
                            //重置
                            'click .restore_old': function (e, value, row, index) {
                                //获取pvc名称
                                var pvc_name = row.pvc_name;
                                //获取存储类数据
                                var storage_class_data = row.storage_class;
                                //获取存储类
                                var storage_class_exists = storage_class_data.storage_class_exists;
                                var storage_class = storage_class_data.storage_class;
                                //获取当前行的jquery对象, 找到第index个tr
                                var $tr = $('#pvc_config').find('tbody tr').eq(index);
                                //找到$tr的input对象
                                var $input = $tr.find('.pvc_input');
                                //修改input对象的值为默认值
                                $input.val(pvc_name);
                                //找到$tr的select对象
                                var $select = $tr.find('.storage_select');
                                //先判断是否存在
                                if(storage_class_exists){
                                    //如果存在则修改为默认值
                                    $select.val(storage_class);
                                }else{
                                    //如果不存在则修改为空
                                    $select.val("");
                                }

                            },

                        }
                    },

                ],

            }
            $("#pvc_config").baseTableConfig().init(pvc_table);

            //初始化pvc详请配置

        }
        


    var step1Valid = function(){
        //获取数据
        var treeObj = $.fn.zTree.getZTreeObj("agent_tree");
        var nodes = treeObj.getCheckedNodes();
        if(nodes.length == 0){
            UIToastr.showWarning(LANG.UI_KUBE_SELECT_TIMEPOINT, LANG.UI_KUBE_SELECT_A_TIMEPOINT);
            return false
        }
        //初始化第三步传输网络
        var nodes_uuid = nodes[0].real_node_uuid;
        $("#transferNetworkTree").transferNetwork({node_uuid: nodes_uuid});
        //初始化忽略节点限制
	    initResourceLimit([nodes_uuid]);
        //初始化第二步两个表格
        // initNamespacePvcTable();
        // initPvcTable();
        resultData.recovery_info = {};
        resultData.recovery_info.pvcs = [];
        resultData.recovery_info.resources = [];
        resultData.recovery_info.ns_rename = {};
        //获取源cluster_uuid
        resultData.recovery_info.src_cluster_uuid = nodes[0].cluster_uuid;
        resultData.recovery_info.src_cluster_name = nodes[0].cluster_name;
        //获取源时间点
        resultData.recovery_info.src_timepoint_uuid = nodes[0].timepoint_uuid;
        resultData.recovery_info.src_timepoint_name = nodes[0].name;
        //获取源密码(无)
        resultData.recovery_info.src_timepoint_password = '';

        //时间点转换成时间
        timepointStr = nodes[0].oldname.replace(/[- :]/g, '');

         //获取任务备份类型
         var by_type = nodes[0].by_type
         resultData.recovery_time_type = by_type;
         var resourceData = [];
         //根据选择不同取不同的数据表
         if(by_type == 2){
             resourceData =$('#data_table_app').bootstrapTable('getSelections');
         }else{
             resourceData =$('#data_table_namespace').bootstrapTable('getSelections');
         }
         //获取每一个数据
        for(let i=0;i<resourceData.length;i++){
            var eachData = {};
            //获取命名空间
            eachData.namespace = resourceData[i].namespace;
            //获取app
            eachData.app = resourceData[i].app;
            //获取app_type
            eachData.app_type = resourceData[i].app_type;
            
            //得到表格的id(务必和代码之前的二级表格生成id的方式相同 不然会找错表格)
            let sub_id = "_sub_table";
            if(by_type == 1){
                sub_id = resourceData[i].namespace + sub_id;
            }else{
                sub_id = resourceData[i].namespace+"_"+resourceData[i].app+"_"+resourceData[i].app_type + sub_id;
            }

            //获取元素
            let sub_id_table = document.getElementById(sub_id);
            if(sub_id_table){
                //如果表格存在 false表示未全选
                eachData.all = 2;
                //获取表格的值
                let sub_table_data = $('#'+sub_id).bootstrapTable('getSelections');
                let resourcesList = [];
                for (let j=0;j<sub_table_data.length;j++){
                    let resourcesOne = {};
                    resourcesOne.category = sub_table_data[j].category;
                    resourcesOne.namespace = sub_table_data[j].namespace;
                    resourcesOne.group = sub_table_data[j].group;
                    resourcesOne.version = sub_table_data[j].version;
                    resourcesOne.kind = sub_table_data[j].kind;
                    resourcesOne.name = sub_table_data[j].name;
                    resourcesList.push(resourcesOne);
                }
                eachData.sub_resources = resourcesList;

            }else{
                eachData.all = 1;
                eachData.sub_resources = [];
            }
            resultData.recovery_info.resources.push(eachData)
        }

        //在点击下一步的时候格式化第二步内容
        $("#target_cluster").val("");
        //隐藏两个div
        $(".namespace_div").hide();
        $(".pvc_div").hide();


         //初始化通用策略
	var initCommonStrategy = function(){

        $('#backupStrategyDiv').initBackupStrategy(defaultConfig);
        //初始化完整性检测
        if(CONF.FUNCTIONS.includes('integrity')){
            //获取选中的值
		    var selectedAgent = zTreeAgent.getCheckedNodes(true);
            for(var i = 0; i < selectedAgent.length; i++){
                //获取每个时间点的backup_disable_flag值
                var integrity_check_flag = selectedAgent[i].integrity_check_flag;
                //判断backup_disable_flag是否为1
                if(integrity_check_flag == 1){
                    complete_backup = false;
                    break;
                }
            }
			$('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.KUBE,0,complete_backup);
		}else{
			$("#completeConfig").hide();
		}
		
        //初始化重试策略
		$('#retry_config').retryStrategy();
        //初始化过载保护
    
    }
initCommonStrategy();










        return true;
    }

    var getNamespaceList = function(){
        var cluster_uuid = $("#target_cluster").val();
        var namespaceList = [];
        var requestClusterNameSpace = function(d){
            //获取所有数据
            var data = d.data;
            //循环data
            for(let i=0;i<data.length;i++){
                if(data[i].type ==1){
                    //1为命名空间
                    namespaceList.push(data[i].namespace);
                }
            }
        }
        pAjaxRequest({get_namespace_type: 2}, "/api/v1/kubernetes/cluster/"+cluster_uuid+"/namespaces", "GET", requestClusterNameSpace,false);
        return namespaceList;
    }

    var checkPVCOrNamespace = function(string) {
        // 长度限制：1到253个字符
        if (string.length < 1 || string.length > 253) {
            return false;
        }
    
        // 字符限制：只能使用小写字母、数字以及连接符 `-`
        if (!/^[a-z0-9-]+$/.test(string)) {
            return false;
        }
    
        // 首尾字符限制：不能以连接符 `-` 开头或结束
        if (/^-|-$/.test(string)) {
            return false;
        }
    
        // 连续连接符限制：不能有两个或两个以上的连续连接符
        if (/--/.test(string)) {
            return false;
        }
    
        return true;
    };

    var getNsnameList = function(){
        let ns_rename = {};
        var valid = true; // 标记变量
        var namespace_data = $('#namespace_config').bootstrapTable('getData');
        $('#namespace_config').find('tbody tr').each(function (index) {
            var row = namespace_data[index];
            if (!row) return true;
            //获取原本命名空间
            var namespace_name = row.namespace_name;
            //查找当前行是否含有select标签
            var $select = $(this).find('.namespace_select');
            var target_namespace_name = "";
            if ($select.length > 0) {
                //获取目标命名空间
                target_namespace_name = $select.val();
            }else{
                target_namespace_name = namespace_name;
            }
            if(target_namespace_name == ""){
                UIToastr.showWarning(LANG.UI_KUBE_NAMESPACE_DETAILS, LANG.UI_KUBE_TARGET_NAMESPACE_REQUIRED);
                valid = false; // 设置标记为 false
                return false;
            }
            if(!checkPVCOrNamespace(target_namespace_name)){
                UIToastr.showWarning(LANG.UI_KUBE_NAMESPACE_DETAILS, LANG.UI_KUBE_NAMESPACE_NAME_RULES);
                valid = false; // 设置标记为 false
                return false;
            }
            //这里判断target_namespace_name是否在namespacelist有值,如果有值
            // if(namespacelist.indexOf(target_namespace_name) != -1){
            //     UIToastr.showWarning("命名空间详请", "集群中已经存在或已经选择该命名空间, 请重新选择目标命名空间: "+target_namespace_name);
            //     valid = false; // 设置标记为 false
            //     return false;
            // }else{
            //     //把新的目标命名空间加入到列表中去
            //     namespacelist.push(target_namespace_name);
            // }
            ns_rename[namespace_name] = target_namespace_name;
        });
        return {'valid':valid, 'ns_rename': ns_rename };
    }



    var step2Valid = function(){
        //获取数据
        //获取目标主机uuid
        resultData.recovery_info.dest_cluster_uuid = $("#target_cluster").val();
        if(resultData.recovery_info.dest_cluster_uuid == "" || resultData.recovery_info.dest_cluster_uuid == null){
            UIToastr.showWarning(LANG.UI_KUBE_RESTORE_TO_CLUSTER, LANG.UI_KUBE_SELECT_A_CLUSTER_TO_RESTORE);
            return false;
        }
        //获取该集群的所有命名空间
        // var namespacelist = getNamespaceList();

        //获取命名空间重定向
        resultData.recovery_info.ns_rename = {};
        // var valid = true; // 标记变量
        // var namespace_data = $('#namespace_config').bootstrapTable('getData');
        // $('#namespace_config').find('tbody tr').each(function (index) {
        //     var row = namespace_data[index];
        //     if (!row) return true;
        //     //获取原本命名空间
        //     var namespace_name = row.namespace_name;
        //     //查找当前行是否含有select标签
        //     var $select = $(this).find('.namespace_select');
        //     var target_namespace_name = "";
        //     if ($select.length > 0) {
        //         //获取目标命名空间
        //         target_namespace_name = $select.val();
        //     }else{
        //         target_namespace_name = namespace_name;
        //     }
        //     if(target_namespace_name == ""){
        //         UIToastr.showWarning("命名空间详请", "目标命名空间必填");
        //         valid = false; // 设置标记为 false
        //         return false;
        //     }
        //     if(!checkPVCOrNamespace(target_namespace_name)){
        //         UIToastr.showWarning("命名空间详请", "命名空间名称必须在1到253个字符之间，只能包含小写字母、数字和连接符 `-`，不能以 `-` 开头或结尾，且不能包含连续的 `-`");
        //         valid = false; // 设置标记为 false
        //         return false;
        //     }
        //     //这里判断target_namespace_name是否在namespacelist有值,如果有值
        //     // if(namespacelist.indexOf(target_namespace_name) != -1){
        //     //     UIToastr.showWarning("命名空间详请", "集群中已经存在或已经选择该命名空间, 请重新选择目标命名空间: "+target_namespace_name);
        //     //     valid = false; // 设置标记为 false
        //     //     return false;
        //     // }else{
        //     //     //把新的目标命名空间加入到列表中去
        //     //     namespacelist.push(target_namespace_name);
        //     // }
        //     resultData.recovery_info.ns_rename[namespace_name] = target_namespace_name;
        // });
        let renameData = getNsnameList();
        if (!renameData["valid"]) {
            return false; // 返回外层函数
        }
        resultData.recovery_info.ns_rename = renameData["ns_rename"];
        //获取PVC配置
        resultData.recovery_info.pvcs = [];
        var valid2 = true; // 标记变量
        var pvc_data = $('#pvc_config').bootstrapTable('getData');
        $('#pvc_config').find('tbody tr').each(function (index) {
            var row = pvc_data[index];
            if (!row) return true;
            var checkbox = row.checkbox;
            if(!checkbox){
                //如果没勾选则判断下一个
                return true;
            }
            let pvcOne = {};
            pvcOne.namespace = row.namespace;
            pvcOne.pvc = row.pvc_name;

            var new_pvc = "";
            //查找是否有input
            var $input = $(this).find('.pvc_input');
            if  ($input.length > 0) {
                //获取目标PVC
                new_pvc = $input.val();
            }else{
                new_pvc = row.pvc_name;
            }
            if(new_pvc == ""){
                UIToastr.showWarning(LANG.UI_KUBE_PVC_DETAILS_CONFIGURATION, LANG.UI_KUBE_SELECTED_PVC_NAME_REQUIRED);
                valid2 = false; // 设置标记为 false
                return false;
            }
            if(!checkPVCOrNamespace(new_pvc)){
                UIToastr.showWarning(LANG.UI_KUBE_PVC_DETAILS_CONFIGURATION, LANG.UI_KUBE_PVC_NAME_RULES);
                valid2 = false; // 设置标记为 false
                return false;
            }
            pvcOne.new_pvc = new_pvc;


            //获取存储类
            //查找是否有该select
            var $select = $(this).find('.storage_select');
            var overwrite_sc = "";
            if ($select.length > 0) {
                //获取目标存储类
                overwrite_sc = $select.val();
            }else{
                overwrite_sc = row.storage_class.storage_class;
            }
            if(overwrite_sc == ""){
                UIToastr.showWarning(LANG.UI_KUBE_PVC_DETAILS_CONFIGURATION, LANG.UI_KUBE_SELECTED_STORAGE_CLASS_REQUIRED);
                valid2 = false; // 设置标记为 false
                return false;
            }
            pvcOne.overwrite_sc = overwrite_sc;
            pvcOne.old_storage_class = row.storage_class.storage_class;
            resultData.recovery_info.pvcs.push(pvcOne);
        });
        if (!valid2) {
            return false; // 返回外层函数
        }
        //判断是否资源和pvc都没勾选
        if(resultData.recovery_info.pvcs.length == 0 && resultData.recovery_info.resources.length == 0){
            //都没勾选给出提示
            UIToastr.showWarning(LANG.UI_KUBE_RESOURCE_SELECTION, LANG.UI_KUBE_SELECT_AT_LEAST_ONE_RESOURCE_OR_PVC);
            return false;
        }
        getTimeDes();
        // console.log(resultData);
        return true;
    }

    var step3Valid = function(){
        var result = getTransferStr() & getHigh() & getSafe();
        //获取通用策略----------------
        //恢复方式
        resultData.type_info = {};
        resultData.type_info.strategy = {};
        resultData.type_info.type = parseInt($('#recovertype').val());//恢复方式
		resultData.type_info.strategy.start_time = $('#oncetime').val(); //定时恢复时间
		resultData.type_info.strategy.type = resultData.type_info.type;
		if (resultData.type_info.type == 4 && resultData.type_info.strategy.start_time == '') {//1立即恢复  2定时恢复
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
			return false;
        }


	    resultData.strategyInfo = $('#backupStrategyDiv').getBackupStrategy();
        showAlldes();
        return result;
    }

    // //得到限速策略
    // var getSpeedStr = function(){
    //     data.speed_strategy = speedList;
    //     return true;
    // }

    var getSafe = function(){
        //安全策略配置--------------
        //获取完整性校验配置
        let completion_check = "";
        if(CONF.FUNCTIONS.includes('integrity')){
            completion_check = $('#completeConfig').getCompleteStrategyCovery();
        }
        resultData.safe_config_strategy = safeData("","",completion_check);
       return true;
    }


    //得到传输策略
    var getTransferStr = function(){
        //获取加密传输
        resultData.transfer_strategy = {};
        resultData.transfer_strategy.encrypt = $("#encrypttransfer").is(':checked');
        //获取加密算法
	    resultData.transfer_strategy.encrypt_method = $("#encrypt_method").val();
        //获取传输网络
        let network = $('#transferNetworkTree').transferNetwork('getSelect');
        if(!network){
            return false;
        }
        //获取传输网络
        resultData.transfer_strategy.network = network.network_uuid;
        //获取传输网络池
        resultData.transfer_strategy.network_pool_uuid = network.network_pool_uuid;
        resultData.transfer_strategy.network_name = network.str;
        //线程数
        resultData.thread_num = $("#transfer_threads").val();
        return true;
    }
   

    //存储之前检查是否有修改过密码
    var checkpassword = function(){
        var now_password = $.trim($('#password').val());
        //把现在的密码和读取到的密码做对比
        //先检查旧密码是否存在
        if(_oldPassword != ''){
            if(now_password == _oldPassword){  //没有修改密码
                return atob(now_password);
            }
        }
        return now_password;

    }


    //得到高级配置
    var getHigh = function(){
        resultData.advanced_strategy = {};
        //优先使用集群内快照
        resultData.advanced_strategy.local_snapshot_first = $("#firstSnapshoot").get(0).checked;
        //获取跳过或覆盖资源
        resultData.advanced_strategy.skip_exists = $('input[name="overlay_resource"]:checked').val();
         //重试
	    resultData.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
        if(!resultData.retry_strategy){
            return false;
        }
        //获取过载保护
	    resultData.ignore_resource_limiting_flag = $("#ignore_resource_limiting_flag").is(':checked');
        //获取解除工作负载节点限制
        resultData.advanced_strategy.unset_affinity = {
            'unset_node_affinity': 2,
            'unset_node_selector': 2,
            'unset_node_name': 2,
        };
        // let lnode = $("#lnode").get(0).checked;
        // if(lnode){
        $('input[name="load_node"]:checked').each(function() {
            let value = $(this).val();
            if(value == 'unset_node_affinity'){
                resultData.advanced_strategy.unset_affinity.unset_node_affinity =1;
            }
            if(value == 'unset_node_selector'){
                resultData.advanced_strategy.unset_affinity.unset_node_selector =1;
            }
            if(value == 'unset_node_name'){
                resultData.advanced_strategy.unset_affinity.unset_node_name =1;
            }
        });
        // }
        //Hooks脚本
        //获取hook脚本的选择值
        var hooksType = $("#hookstype").val();
        resultData.advanced_strategy.hooks = {};
        resultData.advanced_strategy.ignore_exception_option = {};
        
        resultData.advanced_strategy.hooks.script_params = {};
        resultData.advanced_strategy.hooks.env = "";
        resultData.advanced_strategy.hooks.run_in_pods = {};
        const invalidWildcard  = /[*?]/;
        switch (hooksType){
            case "0":
                resultData.advanced_strategy.hooks.type = 0;
                break;
            case "SH":
                let inPod_mode = $("#inPod_mode").val();
                resultData.advanced_strategy.hooks.type = 1;
                resultData.advanced_strategy.hooks.run_in_pods.mode = inPod_mode;
                resultData.advanced_strategy.hooks.run_in_pods.params = {};
                switch (inPod_mode){
                    case "ALL":
                        
                        break;
                    case "SPECIFIED":
                        resultData.advanced_strategy.hooks.run_in_pods.params.namespace = $("#specified_namespaces").val();
                        resultData.advanced_strategy.hooks.run_in_pods.params.pod = $("#specified_pod").val();
                        if(resultData.advanced_strategy.hooks.run_in_pods.params.namespace == ""||
                            resultData.advanced_strategy.hooks.run_in_pods.params.pod == ""
                        ){
                            UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_SPECIFIED_POD_NAMESPACE_POD_REQUIRED);
			                return false;
                        }
                        //对这两个值进行正则效验不支持通配符*?
                        
                        if(invalidWildcard.test(resultData.advanced_strategy.hooks.run_in_pods.params.namespace)){
                            UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_SPECIFIED_POD_NO_WILDCARD);
			                return false;
                        }
                        if(invalidWildcard.test(resultData.advanced_strategy.hooks.run_in_pods.params.pod)){
                            UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_SPECIFIED_POD_NO_WILDCARD);
			                return false;
                        }
                        resultData.advanced_strategy.hooks.run_in_pods.params.image = null;
                        resultData.advanced_strategy.hooks.run_in_pods.params.label = null;
                        resultData.advanced_strategy.hooks.run_in_pods.params.container = $("#specified_container").val();
                        break;
                    case "MATCHED":
                        resultData.advanced_strategy.hooks.run_in_pods.params.namespace = $("#matched_namespaces").val();
                        resultData.advanced_strategy.hooks.run_in_pods.params.pod = $("#matched_pod").val();
                        resultData.advanced_strategy.hooks.run_in_pods.params.label = $("#matched_label").val();
                        if(resultData.advanced_strategy.hooks.run_in_pods.params.namespace == ""){
                            UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_MATCHING_NAMESPACE_REQUIRED);
                            return false;
                        }
                        if(resultData.advanced_strategy.hooks.run_in_pods.params.pod == "" && 
                            resultData.advanced_strategy.hooks.run_in_pods.params.label == ""
                        ){
                            UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_MATCHING_POD_OR_LABEL_REQUIRED);
                            return false;
                        }
                        resultData.advanced_strategy.hooks.run_in_pods.params.image = null;
                        resultData.advanced_strategy.hooks.run_in_pods.params.container = null;
                        break;
                    case "CUSTOM_IMAGE":
                        resultData.advanced_strategy.hooks.run_in_pods.params.namespace = $("#image_namespaces").val();
                        resultData.advanced_strategy.hooks.run_in_pods.params.pod = null;
                        resultData.advanced_strategy.hooks.run_in_pods.params.image = $("#image_images").val();

                        if(resultData.advanced_strategy.hooks.run_in_pods.params.namespace == ""||
                            resultData.advanced_strategy.hooks.run_in_pods.params.image == ""
                        ){
                            UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_CUSTOM_IMAGE_REQUIRED);
			                return false;
                        }

                        resultData.advanced_strategy.hooks.run_in_pods.params.label = null;
                        resultData.advanced_strategy.hooks.run_in_pods.params.container = null;
                        break;
                }
                resultData.advanced_strategy.hooks.script_params.before = $("#before_script_ace").getVinScript("before_script_ace");
                if(resultData.advanced_strategy.hooks.script_params.before === false) return false;
                resultData.advanced_strategy.hooks.script_params.after = $("#after_script_success_ace").getVinScript("after_script_success_ace");
                if(resultData.advanced_strategy.hooks.script_params.after === false) return false;
                resultData.advanced_strategy.hooks.script_params.after_failed = $("#after_script_failure_ace").getVinScript("after_script_failure_ace");
                if(resultData.advanced_strategy.hooks.script_params.after_failed === false) return false;
                break;
            case "CUSTOMSCRIPT":
                resultData.advanced_strategy.hooks.type = 2;
                resultData.advanced_strategy.hooks.script_params.custom_script = $("#custom_script_ace").getVinScript("custom_script_ace");
                if(resultData.advanced_strategy.hooks.script_params.custom_script === false) return false;
                resultData.advanced_strategy.hooks.env = $("#cs_env").val();
                break;
        }
        return true;
    }



    var wizardInit = function(){
        if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function(tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
            // set wizard title
//            $('.step-title', $('#k8srecovercontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#k8srecovercontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#k8srecovercontent').find('.button-previous').css('visibility','hidden');
                $('#k8srecovercontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#k8srecovercontent').find('.button-previous').css('visibility','visible');
                $('#k8srecovercontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#k8srecovercontent').find('.button-next').hide();
                $('#k8srecovercontent').find('.button-submit').css('visibility','visible');
            } else {
                $('#k8srecovercontent').find('.button-next').show();
                $('#k8srecovercontent').find('.button-submit').css('visibility','hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#k8srecovercontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch(index){
                    case 1:
                        if(step1Valid() == false){
                            return false;
                        }
                        break;
                    case 2:
                        if(step2Valid() == false){
                            return false;
                        }
                        break;
                    case 3:
                        if(step3Valid() == false){
                            return false;
                        }
                        break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#k8srecovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#k8srecovercontent').find('.button-previous').css('visibility','hidden');
        $('#k8srecovercontent .button-submit').click(submit).css('visibility','hidden');
    };

    var submit = function(){
        if('' == $.trim($("#jobname").val())){
            UIToastr.showWarning(LANG.UI_KUBE_SUBMIT_JOB, LANG.UI_KUBE_RECOVERY_JOB_NAME_REQUIRED);
            return;
        }
        $('.jobnametip').hide();
        let jobName = $.trim($("#jobname").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
        resultData.task_name = $.trim($("#jobname").val());
        resultData.strategygroupuuid = "";
        //TODO提交
        // console.log("最后提交的前端数据(在php还要转一遍):");
        // console.log(resultData);
        Metronic.blockUI({target: '#k8srecovercontent',animate: true,cenrerY: true,});
        var requestBackupJob = function(d){
            Metronic.unblockUI('#k8srecovercontent');
            if(d.success){
                UIToastr.showSuccess(LANG.UI_KUBE_CREATE_CONTAINER_RECOVERY_JOB, d.message);
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }else{
                UIToastr.showWarning(LANG.UI_KUBE_CREATE_CONTAINER_RECOVERY_JOB, d.message);
            }
        }
        pAjaxRequest(resultData, "/api/v1/kubernetes/recovery", "POST", requestBackupJob,true);
    }

    //替换特殊字符
    var clearString = function (s){
        var rs = "";
        for (var i = 0; i < s.length; i++) {
            rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
        }
        return rs;
    }

    //去掉转义
    var escapeJquery = function(srcString){
        // 转义之后的结果
        var escapseResult = srcString.toString();
        // javascript正则表达式中的特殊字符
        var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
            "]", "|", "{", "}"];
        // jquery中的特殊字符,不是正则表达式中的特殊字符
        var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
            ":", ";", "<", ">", ",", "/"];
        for (var i = 0; i < jsSpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp("\\"
                + jsSpecialChars[i], "g"), "\\"
                + jsSpecialChars[i]);
        }
        for (var i = 0; i < jquerySpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                "g"), "\\" + jquerySpecialChars[i]);
        }
        return escapseResult;
    }

//--------------------------------时间策略相关---------------------------------------


    // var inintDatatimePicker = function(){
    //     if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
    //         //英文独有的
    //         $(".form_datetime").datetimepicker({
    //             autoclose: true,
    //             isRTL: Metronic.isRTL(),
    //             format: "yyyy-mm-dd hh:ii:ss",
    //             pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
    //             startDate: new Date()
    //         });
    //     }else{
    //         $(".form_datetime").datetimepicker({
    //             language:  'zh-CN',
    //             autoclose: true,
    //             isRTL: Metronic.isRTL(),
    //             format: "yyyy-MM-dd hh:ii:ss",
    //             pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
    //             startDate: new Date()
    //         });
    //     }
    // }

   

//--------------------------------限速策略相关---------------------------------------
    // var initSpeedTimeStrategy = function(){
    //     var strategy = [];
    //     strategy[0] = {
    //         mode: 1,
    //         strategy_type: 2,
    //         days: [0, 0, 0, 0, 1, 0, 0],
    //         start_time: '23:00:00',
    //         end_time: '23:30:00',
    //     };
    //     //延迟设置,因为这里icheck会默认修改里面的选中事件
    //     $('#speedstrategy').speedstrategy({config: strategy});
    //     initSpeedFlag = true;
    // }

    // var speedModeHandler = function(){
    //     if(this.value == 2){
    //         $('.setSpeedStrategy').hide();
    //     }else{
    //         $('.setSpeedStrategy').show();
    //     }
    // }

    // var initSpeedStrategyDes = function(){
    //     var titleDes = "";
    //     var des = "";
    //     if(speedList.length !=0){
    //         des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
    //     }
    //     for(var i=0;i<speedList.length;i++){
    //         titleDes += speedList[i].des + '. ';
    //     }
    //     var strategyIndex = $('#strategySelect').val();
    //     if(strategyIndex && strategyIndex != "" && editFlag){
    //         var oldDes = globalStrategy[strategyIndex].speedlimit.des;
    //         initStrategyDesStyle($('.speedlimitDes'), titleDes, oldDes);
    //     }else{
    //         $('.speedlimitDes').removeClass('font-green-seagreen');
    //     }
    //     $('.speedlimitDes').html(des);
    //     $('.speedlimitDes').prop('title', titleDes);

    // }
    // //修改颜色
    // var initStrategyDesStyle = function(div, des, oldDes){
    //     if(oldDes != des){
    //         div.addClass('font-green-seagreen');
    //     }else{
    //         div.removeClass('font-green-seagreen');
    //     }
    // }
    // //获取速度单位换算大小
    // var getSpeedUnit = function(){
    //     var type = parseInt($('#unit').val());
    //     var unit;
    //     switch(type){
    //         case 1:
    //             unit = 1024;
    //             break;
    //         case 2:
    //             unit = 1024 * 1024;
    //             break;
    //         case 3:
    //             unit = 1024 * 1024 * 1024;
    //             break;
    //     }

    //     return unit;
    // }
    var getUuid = function() {
        var len = 36;//36长度
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if(len) {
            for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
        } else {
            var r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for(i = 0; i < 36; i++) {
                if(!uuid[i]) {
                    r = 0 | Math.random() * 16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    }
    // var checkTime = function (start,end) {
    //     var startnum = new Date("1970-01-01" + " " + start).getTime();
    //     var endnum = new Date("1970-01-01" + " " + end).getTime();
    //     if(endnum <= startnum && $("#speedModeType").val() == 1) {//按策略限速才判断
    //         UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
    //         return false;
    //     }else {
    //         return true;
    //     }
    // }
    // var checkSimpleForever = function(mode){
    //     for(var i=0;i<speedList.length;i++){
    //         if(mode == speedList[i].mode){
    //             return false;
    //         }
    //     }

    //     return true;
    // }

//------------初始化数字加减器-------------------
    var initSpinner = function(){
        $('#backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 9});
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
    }
//---------------------------------高级策略-------------
    var initHighStrategyDes = function(){
        var des = "";
        des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#backupThreadNum').val();
        $('.heighDes').html(des);
        $('.heighDes').prop('title', des);

    }

    //-----------------统一处理第四步骤显示,不再分开步骤处理显示,这里处理用新的数据格式去处理数据,抛弃以前老版本
    //注意最后面的一些变量有修改,需要页面统一下
    var showAlldes = function(){
        // 恢复方式
        let reservetypeshow = '';
        reservetypeshow = $('.recoveryTimeDes').text();
       

        //--------源备份集群------------
        let sourceBackupClusterDes = "";
        sourceBackupClusterDes = resultData.recovery_info.src_cluster_name;
        //--------备份时间点------------
        let backupTimepointDes = "";
        backupTimepointDes = resultData.recovery_info.src_timepoint_name;
        //--------资源列表------------
        let resourceListDes = "";
        let resource = resultData.recovery_info.resources;
        for(let i=0;i<resource.length;i++){
            let oneDes = "";
            oneDes += resource[i].namespace;
            if(resource[i].app != ""){
                oneDes += "/"+resource[i].app;
            }
            if(resource[i].all == 1){
                oneDes += "/*";
                resourceListDes += oneDes + "\n";
            }else{
                for(let j=0;j<resource[i].sub_resources.length;j++){
                    let oneNextDes = "";
                    oneNextDes = oneDes + "/"+ resource[i].sub_resources[j].name;
                    resourceListDes += oneNextDes + "\n";
                }
            }

        }

        //--------恢复到集群------------
        let targetClusterDes = "";
        targetClusterDes += $("#target_cluster").find("option:selected").text();

        //--------命名空间详请------------
        let redirectNamespaceDes = "";
        redirectNamespaceDes += LANG.UI_KUBE_KEEP_ORIGINAL_NAMESPACE+": "+getSwitchDes($("#keep_old_namespace").get(0).checked) + '<br>';
        if(Object.keys(resultData.recovery_info.ns_rename).length != 0){
            Object.keys(resultData.recovery_info.ns_rename).forEach(function(key) {
                var value = resultData.recovery_info.ns_rename[key];
                redirectNamespaceDes += `${key}->${value}<br>`;
            });
        }else{
            //隐藏
            $(".redirectNamespaceDesShow_div").hide();
        }
        //--------PVC详请------------
        let coverPvcDes = "";
        coverPvcDes = LANG.UI_KUBE_KEEP_PVC_NAMESPACE+": "+getSwitchDes($("#keep_old_pvc").get(0).checked) +  '<br>';
        //--------PVC恢复详情------------
        let pvcDetailsDes = "";
        if(resultData.recovery_info.pvcs.length != 0){
            for(let i=0;i<resultData.recovery_info.pvcs.length;i++){
                pvcDetailsDes += resultData.recovery_info.pvcs[i].pvc+"("+resultData.recovery_info.pvcs[i].old_storage_class+")"+" -> "+
                resultData.recovery_info.pvcs[i].new_pvc+"("+resultData.recovery_info.pvcs[i].overwrite_sc+")"+'<br>';
            }
        }else{
            $(".coverPvcDesShow_div").hide();
            
        }
        coverPvcDes = coverPvcDes + pvcDetailsDes;
        //--------限速策略------------

        let speedDes = "";
		let speedInfo = resultData.strategyInfo.speedlimit.speedInfo;
		//循环speedInfo
		if(speedInfo.length == 0){
			speedDes = LANG.UI_PUBLIC_NOTHING
		}else{
			for(let i = 0; i < speedInfo.length; i++){
				speedDes += speedInfo[i].des+"<br>";
			}
		}
        

        //--------传输策略------------
        let transferDes = "";
        transferDes += LANG.UI_COPY_BACK_ENCRYPT + ": " + getSwitchDes(resultData.transfer_strategy.encrypt)+"<br>";
        if(resultData.transfer_strategy.encrypt){
            transferDes += LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_METHOD+": "
            switch(parseInt(resultData.transfer_strategy.encrypt_method)){
                case 1: 
                    transferDes += "RSA";
                    break;
                case 2: 
                    transferDes += "SM2";
                    break;
                  default:
            }
            transferDes += "<br>";
        }
        //得到传输网络
        transferDes += LANG.UI_K8S_TRANSFER_NET + ": " + resultData.transfer_strategy.network_name +"<br>";
        //线程数
        if(CONF.FUNCTIONS.includes('multithread')){
			transferDes += LANG.UI_KUBE_THREAD_COUNT + ": " +resultData.thread_num + "<br>";
		}
        
        //安全策略----------------
        let safeDes = "";
        //获取安全策略完整性效验
        if(CONF.FUNCTIONS.includes('integrity')){
            let completion_check = $('#completeConfig').getCompleteStrategyCovery();
            safeDes += completion_check.str;
        }
        
       
        
        //--------高级配置------------
        let highStrategyDes = "";
        //优先使用集群内本地快照
        highStrategyDes += LANG.UI_KUBE_USE_LOCAL_SNAPSHOT_FIRST + ": " + getSwitchDes($("#firstSnapshoot").get(0).checked) + "<br>";
        //跳过或覆盖资源
        highStrategyDes += LANG.UI_KUBE_SKIP_OR_OVERWRITE_RESOURCES + ": " + $('input[name="overlay_resource"]:checked')[0].labels[0].innerText + "<br>";
        //解除工作负载节点限制
        let lnodeDes = LANG.UI_KUBE_RELEASE_WORKLOAD_NODE_LIMITATIONS+": <br>";
        let lnodeDesDetails = "";
        $('input[name="load_node"]:checked').each(function() {
            lnodeDesDetails += $(this)[0].labels[0].innerText+"<br>";
        });
        if(lnodeDesDetails == ""){
            lnodeDesDetails = LANG.UI_PUBLIC_NOTHING+"<br>";
        }
        //忽略节点资源限制
        lnodeDesDetails += LANG.UI_NODE_RESOURCE_LIMIT_IGNORE +": "+ getSwitchDes(resultData.ignore_resource_limiting_flag);
        highStrategyDes += lnodeDes+lnodeDesDetails;

        //--------跳过或覆盖资源------------
        //--------解除工作负载节点限制------------
        
        //--------Hook脚本------------
        let hookDes = "";
        hookDes += LANG.UI_K8S_HOOK_SCRIPT + ": " + $("#hookstype").find("option:selected").text() + "<br>";

        //恢复方式
        $('#timeDesShow').html(reservetypeshow);
        //源备份集群
        $("#sourceBackupClusterDesShow").html(sourceBackupClusterDes);
        //备份时间点
        $("#backupTimepointDesShow").html(backupTimepointDes);
        //资源列表
        $("#resourceListDesShow").html(resourceListDes);
        //恢复到集群
        $("#targetClusterDesShow").html(targetClusterDes);
        //命名空间重定向
        $("#redirectNamespaceDesShow").html(redirectNamespaceDes);
        // 覆盖原PVC
        $("#coverPvcDesShow").html(coverPvcDes);
        // //PVC恢复详情
        // $("#pvcDetailsDesShow").html(pvcDetailsDes);
        //限速策略
        $("#speedDesShow").html(speedDes);
        //高级策略
        $("#highStrategyDesShow").html(highStrategyDes);
        //传输策略
        $("#transferDesShow").html(transferDes);
        //跳过或覆盖资源
        // $("#coverResourceDesShow").html(coverResourceDes);
        //忽略异常
        // $("#ignoreAbnormalDesShow").html(ignoreAbnormalDes);
        //解除工作负载节点限制
        // $("#lnodeDesShow").html(lnodeDes);
        //Hook脚本
        $("#hookDesShow").html(hookDes);

        $("#safeDesShow").html(safeDes);

    }
    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function(check){
        if(check){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    return {
        //main function to initiate the module
        init: function () {

            wizardInit(); //初始化步骤插件
            initCluster(); //初始化集群选择
            initAgentTree(); //初始化树形结构
            initListener(); //事件
            
            // inintDatatimePicker();//初始化时间插件
            initTable(); //初始化待恢复数据表格
            initSpinner(); //初始化数字加载器
            
            initDataChangeListeners(); //初始化监听事件
            initStrategyDes(); //初始化策略描述
           
        },

    };

}();

jQuery(document).ready(function() {
    K8sRecovery.init();
});