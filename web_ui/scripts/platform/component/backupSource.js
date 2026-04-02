
(function($) {
    // 注意:
    // <script type="text/javascript" src="./scripts/platform/component/backupSource.js"></script>
    // 入参={
    //     agent_uuid: "", // 代理uuid
    //     agent_name:"", //代理主机名
    //     check_flag: true, // 勾选状态,勾选还是取消勾选, true为勾选, 默认勾选
    //     relation_flag: true, // 关联选择, true为开启关联, 默认开启关联
    //     show_title : true, // 是否显示标题 true为显示标题, 默认显示标题
    //     show_collapse: false, // 是否折叠, true为展开, 默认不展开
    //     exclude_uuid_list: [] // 排除的uuid列表/值为dev_node_uuid
    // }
    // 初始化
    // $("#testDom").initBackupSource(testData);
    // 事件及方法-监听内部勾选事件
    // 当插件内部有勾选状态的时候可以通过监听该事件获取勾选状态改变的节点信息
    // 方法为$("#testDom").on(testData[i].agent_uuid+"_ztree_id_onCheck", function(event, treeNode,agent_uuid) {})
    // 多个的时候可以通过循环监听
    // for(var i = 0; i < testData.length; i++){
    //     //每棵树的勾选事件监听
    //     $("#testDom").on(testData[i].agent_uuid+"_ztree_id_onCheck", function(event, treeNode,agent_uuid) {
    //         // 这里是你的外部代码
    //         //树的id
    //         console.log("树id:", agent_uuid+"_ztree_id");
    //         //点击的本棵树的节点信息
    //         console.log("勾选状态改变的节点信息:", treeNode);
    //     });
    // }
    // 事件及方法-外部事件勾选插件内部树节点
    // methodVinBackupSource接受两个参数,第一个参数为方法名,其中checkThisNode参数为勾选内部节点事件,第二个为参数值,为一个{}类型
    // 其中第二个参数中dev_uuid为节点的dev_uuid,agent_uuid为需要控制的代理主机的agent_uuid,nowChecked为勾选状态,true为勾选,false为取消勾选
    // 该方法会受插件中勾选关联的影响
    // $("#testDom").methodVinBackupSource('checkThisNode',{
    //     dev_uuid:"ee092eb5-2a9e-4b7b-99e4-29311567d417",
    //     agent_uuid:"11111111111",
    // nowChecked:"true"})
    // 获取数据
    // 1.	获取处理后的数据
    // resultData = $("#testDom").methodVinBackupSource('getData');

    // 2.	获取未处理的数据
    // resultData = $("#testDom").methodVinBackupSource('getAllData');





    // 闭包内的私有变量，用于存储配置
    var privateConfig = {};
    // 初始化返回值框架
    var resultsConfigs = [];
    var ZTREE = {};


    
    

    var onCheckFunction = function(treeId,treeNode,nowChecked=false,agent_uuid) {
        //获取该节点的所有子节点
        function getChildNodes(treeNode) {
          var childNodes = ZTREE[agent_uuid].transformToArray(treeNode);
          var nodes = new Array();
          for(i = 0; i < childNodes.length; i++) {
          nodes[i] = childNodes[i].id;
          }
          return nodes;
          }
      //初始化勾选事件,如果勾选关联打开,则在勾选该节点的时候需要检查该节点的dev_uuid在整个树中是否有相同的dev_uuid,如果存则需要执行相同的勾选或取消勾选操作
      //如果勾选关联开启
      var relation_flag_id = agent_uuid +"_relation_flag_id";
      var switchState = $('#'+relation_flag_id).bootstrapSwitch('state');
        //获取该节点下的所有子节点的id集合
      var childNodesList = getChildNodes(treeNode);
      if (switchState) {
            //先判断该节点是勾选还是未勾选
          
          //循环该节点下的所有子节点id然后获取其dev_uuid
          for(i = 0; i < childNodesList.length; i++) {
              var childNode = ZTREE[agent_uuid].getNodeByParam("id", childNodesList[i]);
              var dev_uuid = childNode.dev_uuid;
              //遍历整颗树的节点
              var nodes = ZTREE[agent_uuid].getNodesByParam("dev_uuid", dev_uuid);
              nodes.forEach(function(node) {
                  ZTREE[agent_uuid].checkNode(node, nowChecked, true);
              });
          }

      }  
  }

 


   $.fn.initBackupSource = function(configs) {
        // 检查传入的configs是否为数组
        if (!Array.isArray(configs)) {
           console.log("Please pass array!");
           return;
        }

        // 定义默认配置
        var defaultConfig = {
            agent_uuid: "", // 代理uuid
            agent_name:"", //代理主机名
            check_flag: true, // 勾选状态,勾选还是取消勾选, true为勾选, 默认勾选
            relation_flag: true, // 关联选择, true为开启关联, 默认开启关联
            show_title : true, // 是否显示标题 true为显示标题, 默认显示标题
            show_collapse: false, // 是否折叠, true为展开, 默认不展开
            exclude_uuid_list: [], // 排除的uuid列表
            blockUI: '',
        };
        resultsConfigs = []; //清空配置

        // 遍历所有配置对象并应用默认值
        configs.forEach(function(config) {
            var settings = $.extend(true, {}, defaultConfig, config); // 深度合并以防止引用共享
            resultsConfigs.push(settings); // 将设置对象添加到结果数组中
        });

        //获取初始化元素的DOM元素
        var $elementDom = $(this);

        //初始化设置树形结构
        var setTree = function(zNodes, config) {
           var beforeExpandListener = function(treeId, treeNode) {
           };
            var onCheckListener = function(e, treeId, treeNode) {
                var nowChecked = treeNode.checked;
                onCheckFunction(treeId,treeNode,nowChecked,config.agent_uuid)
                
                // 触发自定义事件
                $("#" + treeId).trigger(treeId+"_onCheck", [treeNode,config.agent_uuid]);
            };



            var setting = {
                check: {
                    enable: true,
                    nocheckInherit: false,
                },
                data: {
                    simpleData: {
                        enable: true
                    },
                    key: {
                        title: "title"
                    }
                },
                callback: {
                    // beforeClick: beforeClickListener,
                    beforeExpand: beforeExpandListener,
                    onCheck: onCheckListener,
                },
                view: {
                    showTitle: false,
                    nameIsHTML: true,
                }
            };
            var ztree_id_str = config.agent_uuid+"_ztree_id";
            var agent_uuid = config.agent_uuid;
            var ztree_id = $("#"+ztree_id_str);
            ZTREE[agent_uuid] = $.fn.zTree.init(ztree_id, setting, zNodes); //存储实例
            //初始化成功增加一个标识符
            $("a[href='#"+config.agent_uuid+"_collapse_id']").attr('initStatus', 'true');
            //初始化默认全勾选
            // ZTREE[agent_uuid].checkAllNodes(true);
            //查看exclude_uuid_list是有传值,如果有传值则取消部分勾选节点
            if (config.exclude_uuid_list.length > 0) {
                config.exclude_uuid_list.forEach(function(uuid) {
                    var node = ZTREE[agent_uuid].getNodeByParam("id", uuid);
                    if (node) {
                        var dev_uuid = node.dev_uuid;
                        ZTREE[agent_uuid].checkNode(node, false, true);
                         //查看勾选关联是否打开,如果勾选关联打开则对比整颗树是否有节点的dev_uuid是否和这个节点相同,如果相同则执行相同的操作
                         var relation_flag_id = config.agent_uuid +"_relation_flag_id";
                         var switchState = $('#'+relation_flag_id).bootstrapSwitch('state');
                         if (switchState) {
                            var nodes = ZTREE[agent_uuid].getNodesByParam("dev_uuid", dev_uuid);
                            if(nodes){
                                nodes.forEach(function(node) {
                                    ZTREE[agent_uuid].checkNode(node, false, true);
                                });
                            }
                        }
                    }
                });
            }

        };

        //初始化树形结构, 要考虑传值多个的情况
    var initGetZtree = function(config) {
        if(config.blockUI != ""){
            Metronic.blockUI({target:config.blockUI,animate: true});
        }
       
            // 发送后端
            var requestZtree = function(d){
                if(d.success){
                    //获取数据后初始化树形结构
                    //初始化树
                    setTree(d.data, config);
                    if(config.blockUI != ""){
                        Metronic.unblockUI(config.blockUI);
                    }
                    if (config.callback != undefined && config.callback != '') {
                        // 执行回调方法
                        (config.callback)();
                    }
                }else{
                    UIToastr.showInfo(LANG.UI_COMPONENT_GET_DISK_VOLUME,d.message);
                    if(config.blockUI != ""){
                        Metronic.unblockUI(config.blockUI);
                    }
                    if (config.callback != undefined && config.callback != '') {
                        // 执行回调方法
                        (config.callback)();
                    }
                }
                
            }
            pAjaxRequest({'agent_uuid':config.agent_uuid}, "/api/v1/complete_machine_os/disk_tree", "GET", requestZtree,true);
        };

        //初始化DOM元素
        var initSourceDom = function() {
            var htmlAccordion = `
                <div class="add-list">
                    <div class="accordion ChoosePanelBox" role="tablist">
                       
                    </div>
                </div>
            `;

            var generateAccordionPanels = function(config){
                //获取代理名
                var agent_name= config.agent_name;
                //获取勾选关联
                var relation_flag = config.relation_flag;
                var relation_flag_checked = relation_flag ? "checked" : "";
                var relation_flag_id = config.agent_uuid+"_relation_flag_id";
                //初始化collapse的id
                var collapse_id = config.agent_uuid+"_collapse_id";
                //初始化ztree的id
                var ztree_id = config.agent_uuid+"_ztree_id";
                //初始化panel的id
                var panel_id = config.agent_uuid+"_panel_id";
                //是否显示标题
                var show_title = config.show_title ? "" : 'style="display:none"';
                //是否显示线条
                var show_border_title = config.show_title ? "" : 'style="border: none;"';
                //默认展开
                var show_collapse_a = config.show_collapse ? "" : "collapsed";
                var show_collapse_aria = config.show_collapse ? "true" : "false";
                var show_collapse_panel = config.show_collapse ? "collapse in" : "collapse";

                var htmlPanel = `
                    <div class="panel panel-default panel-file" id="${panel_id}" ${show_border_title}>
                        <div class="panel-heading" ${show_title}>
                            <h4 class="panel-title">
                                <a initStatus="false" class="accordion-toggle accordion-toggle-styled popovers ${show_collapse_a}" data-toggle="collapse" data-parent=".ChoosePanelBox" href="#${collapse_id}" aria-expanded="${show_collapse_aria}" aria-controls="${collapse_id}">
                                    <span class="font-green-seagreen">${agent_name}</span>
                                </a>
                            </h4>
                        </div>
                        <div id="${collapse_id}" class="panel-collapse ${show_collapse_panel}" aria-labelledby="headingOne" role="tabpanel" aria-expanded="false">
                            <div class="panel-body" style="padding:16px">
                            <div style="height: 36px;margin-left: 8px;">
                                <label class="control-label">${LANG.UI_COMPONENT_CHECK_ASSOCIATED}</label>
                                <div style="display: inline-block;margin-left: 16px;">
                                    <input type="checkbox" id="${relation_flag_id}" ${relation_flag_checked}
                                            class="make-switch" data-size="small"
                                            data-on-color="primary"
                                            data-off-color="info"
                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                </div>
                                <div style="display: inline-block;margin-left: 3px;">
                                    <a class="popovers" data-container="body"
                                        data-trigger="hover" data-html="true"
                                        data-placement="right"
                                        data-content="${LANG.UI_COMPONENT_CHECK_ASSOCIATED_TIP}">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="form-group">
                                <div id="${ztree_id}"  class="ztree ztree-fa tree-scroll" ></div>
                            </div>
                            </div>
                        </div>
                    </div>`;

                    $(".ChoosePanelBox").append(htmlPanel);

                    //初始化switch插件
                    $('#'+relation_flag_id).bootstrapSwitch();
                    //初始化popovers插件
                    $('.popovers').popover();
                     //初始化获取树形结构数据
                    initGetZtree(config);
            }



             //先检查页面是否有初始化基础DOM
             if (!$('.ChoosePanelBox').length) {
                //当元素不存在的时候初始化基础DOm
                $elementDom.append(htmlAccordion);
            }
            //先循环遍历数组
            for (var i = 0; i < resultsConfigs.length; i++) {
                var config = resultsConfigs[i]; // 获取当前配置对象
                if(config.agent_uuid == "" || config.agent_uuid == undefined || config.agent_uuid == null){
                    //没有agent_uuid则不初始化
                    continue;
                }
                //先查看是勾选状态吗,如果是,则初始化一个panel,如果不是则删除一个panel
                var check_flag = config.check_flag;
                if(!check_flag){
                    var ztree_id = config.agent_uuid+"_ztree_id";
                    var agent_uuid = config.agent_uuid;
                    var panel_id = config.agent_uuid+"_panel_id";
                    //删除panel
                    if ($('#'+panel_id).length) {
                        //如果这个panel存在则删除
                        $('#'+panel_id).remove();
                        //删除ztree实例
                        //先判断是否存有实例
                        if(ZTREE[agent_uuid]){
                            //先获取ztree实例
                            ZTREE[agent_uuid].destroy();
                            //删除ZTREE存储的实例
                            delete ZTREE[agent_uuid];
                        }
                        continue;
                    }
                    continue; //开始下一次循环
                }
                //先检查是否已经初始化了一个一模一样的
                var panel_id = config.agent_uuid+"_panel_id";
                if ($('#'+panel_id).length) {
                    //如果已经初始化则不再初始化
                    // console.log("This node has been initialized. Do not initialize it again")
                    continue;
                }

               //初始化一个panel
               generateAccordionPanels(config);
            }


        };

       var initfunction = function() { 
        //初始化DOM元素
        initSourceDom();
       };

       
       return initfunction();
   };



   //插件方法
   $.fn.methodVinBackupSource = function(method, params = {}) {

        //获取数据(处理后的)
        var getBackupSourceData = function(){
            var resultsDatas = [];
            //获取ZTREE里的所有key值
            for (var key in ZTREE) {
                var eachresultsDatas = {};
                var treeObj = ZTREE[key];
                //获取勾选的磁盘信息------------
                eachresultsDatas.disk_list = [];
                //获取所有勾选的集合
                var checkedNodes_list = treeObj.getCheckedNodes(true);
                //循环获取勾选的磁盘信息
                for(let i = 0; i < checkedNodes_list.length; i++){
                    if(checkedNodes_list[i].first_node != 1){
                        continue;
                    }
                    let eachDevice = {};
                    //获取名称
                    eachDevice.dev_name = checkedNodes_list[i].dev_name;
                    //获取其uuid
                    eachDevice.dev_uuid = checkedNodes_list[i].dev_uuid;
                    eachDevice.dev_type = checkedNodes_list[i].dev_type;
                    //获取唯一id
                    eachDevice.dev_node_uuid =checkedNodes_list[i].dev_node_uuid;
                    eachresultsDatas.disk_list.push(eachDevice);
                }
                //---------------------------
                //获取所有排除的集合
                var checkedNodes = treeObj.getCheckedNodes(false);
                //获取备份是否关联
                var relation_flag_id = key +"_relation_flag_id";
                var switchState = $('#'+relation_flag_id).bootstrapSwitch('state');
                //获取agent_uuid
                eachresultsDatas.agent_uuid = key;
                //获取备份关联
                eachresultsDatas.backup_relation = switchState;
                eachresultsDatas.excluding_devices = [];
                for(let i = 0; i < checkedNodes.length; i++){
                    let eachDevice = {};
                    //获取名称
                    eachDevice.dev_name = checkedNodes[i].dev_name;
                    //获取其uuid
                    eachDevice.dev_uuid = checkedNodes[i].dev_uuid;
                    eachDevice.dev_type = checkedNodes[i].dev_type;
                    //获取唯一id
                    eachDevice.dev_node_uuid = checkedNodes[i].id;
                    // bug#26361修改, 组件返回挂载点, 用于病毒扫描组件排除Windows盘符使用
                    eachDevice.mount_point = checkedNodes[i].mount_point;
                    eachresultsDatas.excluding_devices.push(eachDevice);
                }
                resultsDatas.push(eachresultsDatas);  
            }
            return resultsDatas;
        }


         //获取数据(未处理的)
         var getBackupSourceAllData = function(){
            var resultsDatas = {};
            //获取ZTREE里的所有key值
            for (var key in ZTREE) {
                var treeObj = ZTREE[key];
                //获取所有勾选的集合
                var checkedNodes = treeObj.getCheckedNodes(false);
                resultsDatas[key] = {};
                resultsDatas[key].checkedList = checkedNodes;
                //获取备份是否关联
                var relation_flag_id = key +"_relation_flag_id";
                var switchState = $('#'+relation_flag_id).bootstrapSwitch('state');
                resultsDatas[key].backup_mode = switchState;
            }
            return resultsDatas;
        }

        var getBackupSourceAllNodes = function(){
            var resultsDatas = [];
             //获取ZTREE里的所有key值
             for (var key in ZTREE) {
                var treeObj = ZTREE[key];
                //获取所有勾选的集合
                var allNodes = treeObj.transformToArray(treeObj.getNodes());
                resultsDatas[key] = {};
                resultsDatas[key].allNodes = allNodes;
                //获取备份是否关联
                var relation_flag_id = key +"_relation_flag_id";
                var switchState = $('#'+relation_flag_id).bootstrapSwitch('state');
                resultsDatas[key].backup_mode = switchState;
            }
            return resultsDatas;
        }
        
    
        //勾选事件
        var checkThisNode = function(params){
            //获取dev_uuid
            var dev_uuid = params.dev_uuid; //卷uuid
            //获取agent_uuid
            var agent_uuid = params.agent_uuid; //机器uuid
            //勾选状态
            var nowChecked = params.nowChecked; //勾选状态,默认为false
            if(dev_uuid == undefined || agent_uuid == undefined || dev_uuid == '' || agent_uuid == ''){
                console.log("parameter error")
                return;
            }

            //获取树id
            var treeId = agent_uuid+"_ztree_id";
            //查看是否有当前树的对象
            if(!ZTREE[agent_uuid]){
                //如果没有初始化这棵树则直接退出
                return;
            }
            //获取当前dev_uuid的树集合
            var treeNodes = ZTREE[agent_uuid].getNodesByParam("dev_uuid", dev_uuid,null);
            console.log(treeNodes);
            //循环勾选
            for(var i = 0; i < treeNodes.length; i++){
                //勾选
                onCheckFunction(treeId,treeNodes[i],nowChecked,agent_uuid);
            }

        }
    
    
    
    
    
    
    






        switch (method) {
            case 'getData':
                return getBackupSourceData(params); //处理后的
            case 'getAllData':  
                return getBackupSourceAllData(params); //未处理的
            case 'getBackupSourceAllNodes':  
                return getBackupSourceAllNodes(params); //未处理的
            case 'checkThisNode':
                return checkThisNode(params);
           default:
            console.log("The method is not provided")
            return false;
        } 
   };
}(jQuery));