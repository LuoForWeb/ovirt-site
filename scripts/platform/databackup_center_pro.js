var DataBackupCenter = function () {
    var grid;//声明一个画布对象
    var echarts1,echarts2,echarts3,echarts4,echarts5,echarts6,
        echarts7,echarts8,echarts9,echarts10,echarts11,echarts12,
        echarts13,echarts14,echarts15,echarts16,echarts17,echarts18,echarts19,echarts20;//echarts实例,主要用于调整大小的时候使用
    var CARDLIST = [];
    var CARDDATA = null; //页面数据
    var intervalData; //定时器
    var data_protect_num = 3; //数据保护模块滑动初始显示多少列
    var mySwiper; //swiper实例

    var current_userName = '';
    _TASKINTERVAL = 5000;		//任务获取延迟时间	5秒钟
    //初始化监听事件
    const initListener = () =>{

        //获取信息
        $("#getGridData").on('click',function(){
            let thislist = downLoadGrid();
            $("#getGridDataValue").text(JSON.stringify(thislist));
        })
        //删除画布的一个内容
        $("#deleteGridData").on('click',function(){
            delteGrid();
        })
        //删除画布的一个内容
        $("#addGridData").on('click',function(){
            addGrid();
        })
        $('#storagedetail').on('click',function(){
            LOCATION('./content/platform/storage/storage_manager.php', 'storage_manager');
        })
        $('#taskdetail').on('click',function(){
            LOCATION('./content/platform/jobs/jobs.php', 'task');
        })
        $('#taskalarmdetail').on('click',function(){
            LOCATION('./content/platform/alarm/alarm.php', 'alarm');
        })
        $('#systemalarmdetail').on('click',function(){
            LOCATION('./content/platform/alarm/alarm.php?tab=1', 'alarm');
        })
        
        //线上可以不运行该函数
        // getTableGrid();



    };

    //统计有哪些卡片用的,线上环境用不上
    const getTableGrid = () =>{
        $(".editTool").show();
        let divs = document.querySelectorAll('div');
        let result = [];

        for (let i = 0; i < divs.length; i++) {
            let id = divs[i].id;
            let id_w = divs[i].getAttribute('id_w');
            let id_h = divs[i].getAttribute('id_h');
            let id_title = divs[i].getAttribute('id_title');
            if (id && id_w && id_h && id_title) {
                result.push({id: id, id_w: id_w, id_h: id_h, id_title:id_title});
            }
        }

        // 创建一个 HTML 表格
        let table = document.createElement('table');
        table.border = "1";
        table.innerHTML = '<tr><th>id_value</th><th>'+ LANG.UI_HOMEPAGEPRO_TITLE +'</th><th>'+ LANG.UI_HOMEPAGEPRO_BEST_WIDTH +'</th><th>'+ LANG.UI_HOMEPAGEPRO_BEST_HEIGHT +'</th></tr>'; // 添加表头

        // 将结果添加到表格中
        for (let i = 0; i < result.length; i++) {
            let row = document.createElement('tr');
            row.innerHTML = `<td>${result[i].id}</td><td>${result[i].id_title}</td><td>${result[i].id_w}</td><td>${result[i].id_h}</td>`;
            table.appendChild(row);
        }
        $("#gridTableList").append(table);
    }

    //获取所有加载的模块的gs_id集合
    const getAllgsDiv = () => {
        let divs = document.querySelectorAll('div');
        let result = [];

        for (let i = 0; i < divs.length; i++) {
            let gs_id = divs[i].getAttribute('gs-id');
            if (gs_id) {
                let divdes = '[gs-id="'+gs_id+'"]'
                result.push(divdes);
            }
        }
        return result;
    }



    const initSizeEcharts = ()=>{
        if(CARDLIST.includes("card_storage_all_1")){
            echarts1.resize();
            echarts2.resize();
        }
        if(CARDLIST.includes("card_system_alarm_all_1")){

                echarts3.resize();
                echarts4.resize();
                echarts5.resize();


        }
    }

    window.onresize = function(event) {
        initSizeEcharts();
    }



    /**
     * 从数据库或其他地方获取首页配置卡片
     */
    const getGridOption = () =>{
        var dataList = {};
        let userVal = $('#user_select').find('option:selected').val();
        if(userVal == undefined || userVal == null){
            userVal = "";
        }
        dataList.userVal = userVal;
        var itemData = [];
        const requestSyncThis = (value) =>{
            if(!value.success){
                //如果获取失败则直接给出提示
                toastr.warning(LANG.UI_HOMEPAGEPRO_GETCONFIG_ERROR, value.message);
            }else{
                itemData = value.data;
                CARDLIST = getCardList(itemData);
            }
        }
        //获取所有信息后发送后端
        pAjaxRequest(dataList, "/api/v1/homepage/grid", "get", requestSyncThis,false);
        return itemData;
    }


    //根据首页配置项获取卡片数组
    const getCardList = (gridItem) =>{
        var List = [];
        for (const gridItemElement of gridItem) {
            List.push(gridItemElement['content'])
        }
        return List;
    }


    //初始化界面画布
    const initGrid = () => {

        var items = getGridOption();
        //结构化数组,转换成自己能用的数组
        $itemList = updateGrid(items);

        //初始化配置项
        var options = {
            resizable: {
                handles: 'none'  //全首页禁用缩放
            },
            disableDrag: true, //全首页禁止拖动
            margin: 10, //方块之间的间隔
            cellHeight: 5, //每个方块的高度
        };
        grid = GridStack.init(options);
        grid.load($itemList);
        if(items.length==0){
            //如果没有数据则直接退出
            return;
        }
        controlCard(false);
        //该函数的主要功能,是因为页面高度还没有渲染完成就执行了后面的初始化操作,导致高度计算有误
        //所以使用setTimeout方法可以执行相同的效果,但是由于setTimeout不太好计算到底需要多少时间完成渲染,所以采用以下方法
        //传入一个选择器数组
        //主要检测是否趋于稳定,即浏览器是否渲染完成
        //每100毫秒检测一次
        //如果该方法影响性能了可用setTimeout方法来执行相同的效果,时间可设置一个满意的时间,或者更改检测频率
        function waitForHeightStability(selectors, callback) {
            // 初始化元素信息
            var elements = selectors.map(function(selector) {
                return {
                    dom: document.querySelector(selector),
                    lastHeight: null,
                    stableCount: 0
                };
            });

            var intervalId = setInterval(function() {
                var allStable = true;

                elements.forEach(function(element) {
                    var currentHeight = element.dom.offsetHeight;
                    if (currentHeight === element.lastHeight) {
                        element.stableCount++;
                    } else {
                        element.lastHeight = currentHeight;
                        element.stableCount = 0;
                    }

                    if (element.stableCount < 3) {
                        allStable = false;
                    }
                });

                if (allStable) {
                    clearInterval(intervalId);
                    callback();
                }
            }, 100);
        }
        //这里写要初始化检测高度的div
        var checkHeighDiv =  getAllgsDiv();

        waitForHeightStability(checkHeighDiv, function() {
            //初始化监听事件
            initListenerCard();

            var allInfo = getHomepageInfo();
            // 在这里执行你的代码
            initCardListener(allInfo);
        });

    }


    const initListenerCard = () =>{
        // 禁用拖动和缩放
        $('#saveBut').click(function() {
            $(".editContainer .editBut").css("display","block")
            $(".editContainer .saveBut").css("display","none")
            $(".editContainer .box_icon").css("background-color","")
            $(".editContainer .icon_editPage").removeClass("icon_editPageCom");
            // 获取所有的网格项目
            controlCard(false);
            //获取数据
            let thislist = downLoadGrid();
            //转换成json
            thislist = JSON.stringify(thislist);
            let dataList = {};
            dataList.cardContent = thislist;
            let userVal = $('#user_select').find('option:selected').val();
            userVal = userVal == undefined ? "" : userVal;
            dataList.userval = userVal;

            const requestSyncThis = (value) =>{
                if(value.success){
                    //如果获取失败则直接给出提示
                    toastr.success(LANG.UI_HOMEPAGEPRO_RESERVE_HOMEPAGE, value.message);
                }else{
                    //如果获取失败则直接给出提示
                    toastr.warning(LANG.UI_HOMEPAGEPRO_RESERVE_HOMEPAGE, value.message);
                }
            }
            //获取所有信息后发送后端
            pAjaxRequest(dataList, "/api/v1/homepage/save_card", "post", requestSyncThis,true);


        });

        // 启用拖动和缩放
        $('#editBut').click(function() {
            $(".editContainer .editBut").css("display","none")
            $(".editContainer .saveBut").css("display","block")
            $(".editContainer .box_icon").css("background-color","#41D46A");
            $(".editContainer .icon_editPage").addClass("icon_editPageCom");
            controlCard(true);
        });


        // 选择关联用户后的卡片更新
        $('#user_select').on('change',()=>{
            let userVal = $('#user_select').find('option:selected').val();
            let userName = $('#user_select').find('option:selected').text();
            userName = userVal ? userName : current_userName;
            //这里如果是选择了其他用户,则不能编辑首页
            if(userVal == "" || userVal == undefined || userVal == null){
                $(".editContainer").css("display","block");
            }else{
                $(".editContainer").css("display","none");
            }
            $('#current_user_name').html(userName);
            //这里获取选定用户的首页
            var items = getGridOption();
            //先移除所有卡片
            grid.removeAll();
            //结构化数组,转换成自己能用的数组
            items = updateGrid(items);
            //插入新的卡片
            items.forEach(item => {grid.addWidget(item);});
            //获取数据
            var allInfo = getHomepageInfo(userVal,userName);
            initCardListener(allInfo);
        });


        //触发顶部hello的关闭按钮
        $('[gs-id="card_basic_task_data_1"] .close_hello').on('click',function(){
            $("#card_basic_task_data_1 .img_hello").css("display","none");
            $("#card_basic_task_data_1 .basic_task").css("margin-top","0");
            //移除卡片
            var chartDom = document.querySelector('[gs-id="card_basic_task_data_1"]');
            grid.removeWidget(chartDom);

            //添加新的卡片
            var addcard = {
                "content":$("#card_basic_task_data_1").html(),
                "x":0,
                "y":0,
                "w":12,
                "id":"card_basic_task_data_1",
                "h":62
            }
            grid.addWidget(addcard);
            init_card_basic_task_data_1(CARDDATA.card_basic_task_data_1);
        })

        //初始化节点信息
        getNodeList();



    }


    //获取节点信息
    const getNodeList = () =>{
        //获取节点信息并初始化节点信息
        const requestSyncThis = (value) =>{
            if(!value.success){
                //如果获取失败则直接给出提示
                toastr.warning(LANG.UI_HOMEPAGEPRO_GET_NODE, value.message);
            }else{
                //先获取数据
                let nodeList = value.data;
                for(let i = 0;i<nodeList.length;i++){
                    // 创建新的option元素
                    var newOption = $("<option></option>").text(nodeList[i].ipDes).val(nodeList[i].node_uuid);
                    // 将新的option添加到select元素中
                    $('[gs-id="card_system_alarm_all_1"] .selectpicker.homepage_node_select').append(newOption);
                }
                //刷新下插件
                $('.selectpicker.homepage_node_select').selectpicker('refresh');
            }
        }
        //获取所有信息后发送后端
        pAjaxRequest({}, "/api/v1/homepage/nodes", "get", requestSyncThis,false);
    }




    //是否启用禁用卡片拖拽效果和禁用效果
    //flag 为布尔类型 true表示启用, false表示禁用
    const controlCard = (flag) =>{
        let bool_flag = !!flag;
// 获取所有的网格项目
        var items = document.querySelectorAll('.grid-stack-item');
        items.forEach(function(item) {
            grid.movable(item, bool_flag);
            grid.resizable(item, bool_flag);
        });
    }




    //获取首页设计
    const getHomepageInfo = (userVal = '',userName = '') =>{
        var dataList = {};
        for (const CARDLISTElement of CARDLIST) {
            dataList[CARDLISTElement] = true;
        }
        dataList.userval = userVal;
        dataList.username = userName;
        //获取告警选择的天数
        dataList.card_alarm_all_1_params = {};
        var selectedAlarmValue = parseInt($('[gs-id="card_alarm_all_1"] input[name="alarm_time_day"]:checked').val());
        dataList.card_alarm_all_1_params.alarm_time_day = selectedAlarmValue;
        //获取选中的节点信息
        dataList.card_system_alarm_all_1_params = {};
        var selectedNode = $('[gs-id="card_system_alarm_all_1"] .selectpicker.homepage_node_select').val();
        dataList.card_system_alarm_all_1_params.selectedNode = selectedNode;
        //获取存储可视化的时间段
        dataList.card_storage_all_1_params = {};
        var recent_days = $('[gs-id="card_storage_all_1"] .selectpicker.value_storage_time_day').val();
        dataList.card_storage_all_1_params.recent_days = parseInt(recent_days);

        var allInfo = {};
        const requestSyncThis = (value) =>{
            if(!value.success){
                //如果获取失败则直接给出提示
                toastr.warning(LANG.UI_HOMEPAGEPRO_GET_DATA_ERROR, value.message);
            }else{
                CARDDATA = value.data;
                allInfo = value.data;
            }
            return allInfo;
        }
        //获取所有信息后发送后端
        pAjaxRequest(dataList, "/api/v1/homepage/info", "post", requestSyncThis,false);
        return allInfo;
    }





    //获取下载布局信息
    const downLoadGrid = () => {
        //获取画布信息
        var saveGridMsg = grid.save();
        var GridList = [];
        //对画布信息转换为自己定义的数组
        for (const saveGridMsgElement of saveGridMsg) {
            //先把html转换成对象
            let contentOption = $(saveGridMsgElement['content']);
            //获取第一个div的class值
            var firstDivClass = contentOption.attr('id_value');
            //把content替换为id_value
            saveGridMsgElement['content'] = firstDivClass;
            saveGridMsgElement['id'] = firstDivClass;
            GridList.push(saveGridMsgElement);
        }
        return GridList;
    }

    //载入转换布局信息
    const updateGrid = (gridOption) => {
        if(gridOption.length == 0){
            return [];
        }
        var itemList = [];
        for (const item of gridOption) {
            //判断是否有该元素
            if($("#"+item['content']).length){
                //获取dom元素
                item['content'] = $("#"+item['content']).html()
                itemList.push(item)
            }else{
                //跳过本次
                continue;
            }
        }
        //返回画布数组
        return itemList;
    }

    //删除一个布局信息
    const delteGrid = () =>{
        //获取内容
        let delete_id = $("#deleteGridValue").val();
        //获取是否存在该内容
        if(delete_id == ""){
            alert(LANG.UI_HOMEPAGEPRO_INPUT_ID_VALUE_INFO);
        }
        //如果找到该元素则删除
        if ($('[gs-id="'+delete_id+'"]').length) {
            //删除对应元素
            grid.removeWidget($('[gs-id="'+delete_id+'"]')[0]);
        }else{
            alert(LANG.UI_HOMEPAGEPRO_NOT_FIND_ELEMENT)
        }
    }

    //添加一个布局信息
    const addGrid = () => {
        //获取id
        var add_id = $("#addGridValue").val();
        //先检查是否画布中已经包含有了
        if ($('[gs-id="'+add_id+'"]').length) {
            alert(LANG.UI_HOMEPAGEPRO_ADD_SAME_ELEMENT);
            return;
        }
        //先查找是否有对应模块
        if($("#"+add_id).length){
            //获取初始化的值
            let w = $("#"+add_id).attr("id_w");
            let h = $("#"+add_id).attr("id_h");
            if(w == undefined){
                w = 1;
            }
            if(h == undefined){
                h = 1;
            }
            let addOption = {
                "id": add_id,
                "w":w,
                "h":h,
                "content":$("#"+add_id).html()
            }
            grid.addWidget(addOption);

            //如果找到了
        }else{
            alert(LANG.UI_HOMEPAGEPRO_NOT_FIND_ELEMENT)
        }

    }



    const initCardListener = (info) =>{
        //概览-任务
        if(CARDLIST.includes("card_basic_task_data_1")){
            init_card_basic_task_data_1(info.card_basic_task_data_1);
        }
        //存储
        if(CARDLIST.includes("card_storage_all_1")){
            init_card_storage_all_1(info.card_storage_all_1);
        }
        //告警
        if(CARDLIST.includes("card_alarm_all_1")){
            init_card_alarm_all_1(info.card_alarm_all_1);
        }
        //数据保护

        if(CARDLIST.includes("card_data_protect_all_1")){
            init_card_data_protect_all_1(info.card_data_protect_all_1);
        }
        //系统监控
        if(CARDLIST.includes("card_system_alarm_all_1")){
            init_card_system_alarm_all_1(info.card_system_alarm_all_1);
        }

        //定时器
        //先清除定时器
        clearInterval(intervalData);
        if(CARDLIST.includes("card_system_alarm_all_1")){
            //这里需要开始对需要刷新的数据启用定时器
            intervalData = setInterval(() => {
                if($(".grid-stack").length == 0){
                    clearInterval(intervalData);
                    return;
                }
                setIntervalFunction();
            }, 2000);
        }



    }

    const setIntervalFunction = () =>{
        var setIntervalList = [];
        var selectedNode = null;
        var dataList = {};
        if(CARDLIST.includes("card_system_alarm_all_1")){
            setIntervalList.push("card_system_alarm_all_1");
            selectedNode = $('[gs-id="card_system_alarm_all_1"] .selectpicker.homepage_node_select').val();
            dataList.card_system_alarm_all_1_params = {};
            dataList.card_system_alarm_all_1_params.selectedNode = selectedNode;
        }
        for (const setIntervalListElement of setIntervalList) {
            dataList[setIntervalListElement] = true;
        }
        const requestSyncThis = (value) =>{
            let info = value.data;
            if(setIntervalList.includes("card_system_alarm_all_1")){
                init_card_system_alarm_all_1(info.card_system_alarm_all_1);
            }

        }
        //获取所有信息后发送后端
        pAjaxRequest(dataList, "/api/v1/homepage/info", "post", requestSyncThis,true);
    }


//---------------------------------初始化各种卡片start-------------------------------------------------------
    const init_card_data_protect_all_1 = (params_data) =>{
        //初始化数据

        const getEveryInfo = (key,value) =>{
            var htmlStr = "";
            if(!key) return '<div style="background: none;"></div>';
            switch (key){
                case "vm_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>`+ LANG.UI_PUBLIC_VM +`</div>
                                    <div class="blue_vm_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.vm_num}</div><div>`+ LANG.UI_HOMEPAGEPRO_VM_NUM +`</div></div>
                                    <div><div>${value.protect_num}</div><div>`+ LANG.UI_CLIENT_PROTECTED +`</div></div>
                                    <div><div>${value.backup_size_des}</div><div>`+ LANG.UI_HOMEPAGEPRO_BACKUP_DATA +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
                case "public_cloud_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>`+ LANG.UI_PUBLIC_PUBLIC_CLOUD +`</div>
                                    <div class="blue_public_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.vm_num}</div><div>`+ LANG.UI_HOMEPAGEPRO_INSTANCE_NUM +`</div></div>
                                    <div><div>${value.protect_num}</div><div>`+ LANG.UI_CLIENT_PROTECTED +`</div></div>
                                    <div><div>${value.backup_size_des}</div><div>`+ LANG.UI_HOMEPAGEPRO_BACKUP_DATA +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
                case "private_cloud_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>`+ LANG.UI_PUBLIC_PRIVATE_CLOUD +`</div>
                                    <div class="blue_private_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.vm_num}</div><div>`+ LANG.UI_HOMEPAGEPRO_INSTANCE_NUM +`</div></div>
                                    <div><div>${value.protect_num}</div><div>`+ LANG.UI_CLIENT_PROTECTED +`</div></div>
                                    <div><div>${value.backup_size_des}</div><div>`+ LANG.UI_HOMEPAGEPRO_BACKUP_DATA +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
                case "nas_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>NAS</div>
                                    <div class="blue_nas_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.protect_num}</div><div>`+ LANG.UI_CLIENT_PROTECTED +`</div></div>
                                    <div><div>${value.backup_size_des}</div><div>`+ LANG.UI_HOMEPAGEPRO_BACKUP_DATA +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
                case "cdp_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>`+ LANG.UI_VISUAL_PLATFORM_DES_CDP +`</div>
                                    <div class="blue_cdp_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.protect_num}</div><div>`+ LANG.UI_CLIENT_PROTECTED +`</div></div>
                                    <div><div>${value.total_backup_size_des}</div><div>`+ LANG.UI_HOMEPAGEPRO_BACKUP_DATA +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
                case "client_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>`+ LANG.UI_PUBLIC_CLIENT +`</div>
                                    <div class="blue_client_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.fs_ackup_size_des}</div><div>`+ LANG.UI_VISUAL_FILE +`</div></div>
                                    <div><div>${value.db_ackup_size_des}</div><div>`+ LANG.UI_VISUAL_DB +`</div></div>
                                    <div><div>${value.os_ackup_size_des}</div><div>`+ LANG.UI_VISUAL_SERVER +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
                case "lab_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>`+ LANG.UI_HOMEPAGEPRO_VIRTUAL_LAB +`</div>
                                    <div class="blue_lab_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.lab_num}</div><div>`+ LANG.UI_HOMEPAGEPRO_VERIFY_TIME +`</div></div>
                                    <div><div>${value.run_num}</div><div>`+ LANG.UI_HOMEPAGEPRO_VERIFYING +`</div></div>
                                    <div><div>${value.faild_num}</div><div>`+ LANG.UI_HOMEPAGEPRO_FAIL_TIME +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
                case "m365_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>M365</div>
                                    <div class="blue_m365_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.protect_num}</div><div>`+ LANG.UI_CLIENT_PROTECTED +`</div></div>
                                    <div><div>${value.backup_size_des}</div><div>`+ LANG.UI_HOMEPAGEPRO_BACKUP_DATA +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
                case "hadoop_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>Hadoop</div>
                                    <div class="blue_hadoop_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.protect_num}</div><div>`+ LANG.UI_CLIENT_PROTECTED +`</div></div>
                                    <div><div>${value.backup_size_des}</div><div>`+ LANG.UI_HOMEPAGEPRO_BACKUP_DATA +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
                case "obs_info":
                    htmlStr = `
                        <div>
                            <div class="swiper_blue_box">
                                <div>
                                    <div>`+ LANG.UI_VISUAL_OBS +`</div>
                                    <div class="blue_obs_img"></div>
                                </div>
                                <div>
                                    <div><div>${value.protect_num}</div><div>`+ LANG.UI_CLIENT_PROTECTED +`</div></div>
                                    <div><div>${value.backup_size_des}</div><div>`+ LANG.UI_HOMEPAGEPRO_BACKUP_DATA +`</div></div>
                                </div>
                            </div>
                        </div>
                    `
                    break;
            }
            return htmlStr;
        }


        const getEachData = (firstKey,firstElement,secondKey,secondElement) =>{
            var eachHtml ='<div class="swiper-slide">'+
                                '<div class="swiper_blue_big_box">'+
                                     getEveryInfo(firstKey,firstElement)+getEveryInfo(secondKey,secondElement)+
                                '</div>'+
                            '</div>';
            return eachHtml;
        }




        var keys = Object.keys(params_data);
        var keysCount = keys.length;
        var htmlTotal = "";
        for (var i = 0; i < keysCount; i += 2) {
            var firstKey = keys[i];
            var firstElement = params_data[firstKey];
            var secondKey = i + 1 < keys.length ? keys[i + 1] : null;
            var secondElement = secondKey ? params_data[secondKey] : null;
            if (secondElement !== null) {
                htmlTotal += getEachData(firstKey,firstElement,secondKey,secondElement);
            } else {
                htmlTotal += getEachData(firstKey,firstElement);
            }
        }
        $('[gs-id="card_data_protect_all_1"] .initSwrapper').append(htmlTotal);







        let newSlidesPerViewNow = 3; //一共显示多少列
        let loopCheck = true; //是否轮播
        if (window.innerWidth <= 1660) {
            newSlidesPerViewNow = 2;
            //如果显示2列,那么一个页面最多4个
            if(keysCount <= 4){
                loopCheck = false;
                //翻页按钮隐藏
                $(".swiper-button-next_blue").hide();
                $(".swiper-button-prev_blue").hide();
            }
        } else {
            newSlidesPerViewNow = 3;
            //如果显示3列,那么一个页面最多6个
            if(keysCount <= 6){
                loopCheck = false;
                //翻页按钮隐藏
                $(".swiper-button-next_blue").hide();
                $(".swiper-button-prev_blue").hide();
            }
        }


        mySwiper = new Swiper ('[gs-id="card_data_protect_all_1"] .swiper', {
            direction: 'horizontal',
            slidesPerView: newSlidesPerViewNow,
            speed: 500,
            loop: loopCheck, // 循环模式选项
            // 如果需要前进后退按钮
            navigation: {
                nextEl: '.swiper-button-next_blue',
                prevEl: '.swiper-button-prev_blue',
            },

        })


        window.addEventListener('resize', function() {
            if (!mySwiper || typeof mySwiper.update !== 'function') {
                // Swiper 还没有初始化，所以什么都不做
                return;
            }
            var newSlidesPerView;
            if (window.innerWidth <= 1660) {
                newSlidesPerView = 2;
            } else {
                newSlidesPerView = 3;
            }

            if (data_protect_num != newSlidesPerView) {
                data_protect_num = newSlidesPerView;
                mySwiper.params.slidesPerView = data_protect_num;
                mySwiper.update();
            }
        });
    }



    const init_card_basic_task_data_1 = (params) =>{
        $('[gs-id="card_basic_task_data_1"] .value_run_time').text(params.value_days);
        $('[gs-id="card_basic_task_data_1"] .value_protect_size').text(params.value_size);
        $('[gs-id="card_basic_task_data_1"] .value_protect_size_unit').text(params.value_size_unit);
        $('[gs-id="card_basic_task_data_1"] .value_task_op').text(params.value_task_log_count);
        $('[gs-id="card_basic_task_data_1"] .value_system_op').text(params.value_system_log_count);
        $('[gs-id="card_basic_task_data_1"] .value_current_job').text(params.task_info.current_total.value_count);
        $('[gs-id="card_basic_task_data_1"] .value_run_job').text(params.task_info.running.value_count);
        $('[gs-id="card_basic_task_data_1"] .value_success_job').text(params.task_info.success.value_count);
        $('[gs-id="card_basic_task_data_1"] .value_error_job').text(params.task_info.abnormal.value_count);
        $('[gs-id="card_basic_task_data_1"] .value_fail_job').text(params.task_info.fail.value_count);
    }

    const init_card_alarm_all_1 = (params) =>{
        const initAlarmCardInfo = (params) =>{
            $('[gs-id="card_alarm_all_1"] .value_task_total').text(params.value_task_alarm_count);
            $('[gs-id="card_alarm_all_1"] .value_task_total_warning').text(params.value_task_alarm_level_warning);
            $('[gs-id="card_alarm_all_1"] .value_task_total_error').text(params.value_task_alarm_level_error);
            $('[gs-id="card_alarm_all_1"] .value_system_total').text(params.value_system_alarm_count);
            $('[gs-id="card_alarm_all_1"] .value_system_warning').text(params.value_system_alarm_level_warning);
            $('[gs-id="card_alarm_all_1"] .value_system_error').text(params.value_system_alarm_level_error);
        }
        initAlarmCardInfo(params);
        $("input[name='alarm_time_day']").change(function(){
            var selectedAlarmValue = parseInt($('[gs-id="card_alarm_all_1"] input[name="alarm_time_day"]:checked').val());
            var dataList = {};
            dataList.card_alarm_all_1 = true;
            dataList.card_alarm_all_1_params = {};
            dataList.card_alarm_all_1_params.alarm_time_day = selectedAlarmValue;
            const requestSyncThis = (value) =>{
                if(!value.success){
                    //如果获取失败则直接给出提示
                    toastr.warning(LANG.UI_HOMEPAGEPRO_GET_DATA_ERROR1, value.message);
                }else{
                    initAlarmCardInfo(value.data.card_alarm_all_1)
                }
            }
            //获取所有信息后发送后端
            pAjaxRequest(dataList, "/api/v1/homepage/info", "post", requestSyncThis,true);
        });
    };


    const init_card_storage_all_1 = (params_data) =>{
        //初始化数据
        $('[gs-id="card_storage_all_1"] .value_storage_online').text(params_data.storageInfo.online_num);
        $('[gs-id="card_storage_all_1"] .value_storage_off').text(params_data.storageInfo.offline_num);
        $('[gs-id="card_storage_all_1"] .value_storage_used').text(params_data.storageInfo.used_size_info.value);
        $('[gs-id="card_storage_all_1"] .value_storage_used_unit').text(params_data.storageInfo.used_size_info.unit);
        $('[gs-id="card_storage_all_1"] .value_storage_residue').text(params_data.storageInfo.free_size_info.value);
        $('[gs-id="card_storage_all_1"] .value_storage_residue_unit').text(params_data.storageInfo.free_size_info.unit);
        const initechart1 = (params_data) =>{
            var chartDom = document.querySelector('[gs-id="card_storage_all_1"] .echarts_storage_blue');
            echarts1 = echarts.init(chartDom);
            //计算使用百分比
            var used_percent = 0;
            if(params_data.storageInfo.used_size_info.intval == 0 || params_data.storageInfo.total_size_info.intval == 0){
                used_percent =0;
            }else{
                used_percent = (params_data.storageInfo.used_size_info.intval/params_data.storageInfo.total_size_info.intval).toFixed(2);
            }

            var option = {
                series: [
                    {
                        name: LANG.UI_VISUAL_STORAGE_TOTAL,
                        type: 'gauge',
                        radius: '90%',
                        startAngle: 90,
                        endAngle: -269.9999,
                        min: 0,     // 最小值
                        max: 100,   // 最大值
                        splitNumber: 10, // 刻度数量
                        axisLine: {
                            show: true,
                            lineStyle: {
                                color: [[used_percent, '#2453D0'], [1, '#E2E7ED']], // 0.7 是已使用的百分比，可以根据实际情况调整
                                width: 30 // 设置圈的宽度为 30px
                            }
                        },
                        splitLine: {
                            show: false,
                        },
                        axisLabel: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        pointer: {
                            show: false
                        },
                        detail: {
                            show: false
                        },
                        // data: [{value: 70, name: '已使用'}] // 已使用的数据
                    }
                ],
                title: {
                    text: [
                        '{a|'+params_data.storageInfo.total_size_info.value+'}{b|'+params_data.storageInfo.total_size_info.unit+'}\n{c|'+LANG.UI_PUBLIC_TOTAL_STORAGE_SIZE+'}'
                    ].join('\n'),
                    left: 'center',
                    top: 'center',
                    textStyle: {
                        rich: {
                            a: {
                                color: '#2D3748',
                                fontSize: 28,
                                fontWeight: 'bold',
                                padding: [5, 2, 5, 10] ,
                                lineHeight: 30
                            },
                            b: {
                                color: '#788799',
                                fontSize: 14,
                                lineHeight: 21
                            },
                            c: {
                                color: '#647080',
                                fontSize: 14,
                                lineHeight: 21
                            }
                        }
                    }
                }
            };

            echarts1.setOption(option);
        }
        const initechart2 = (params_data) =>{
            var chartDom = document.querySelector('[gs-id="card_storage_all_1"] .echarts_storage_7days');
            echarts2 = echarts.init(chartDom);
            //处理数据
            var storageRecentInfo = params_data.storageRecentInfo;
            //初始化数据
            var data_x = [];
            var data_y_backup_data = [];
            var data_y_copy_data = [];
            var data_y_archived_data = [];
            for(let i=0;i<storageRecentInfo.length;i++){
                data_x.push(storageRecentInfo[i].date);
                data_y_backup_data.push(storageRecentInfo[i].backup_data);
                data_y_copy_data.push(storageRecentInfo[i].copy_data+storageRecentInfo[i].archived_data);
                data_y_archived_data.push(storageRecentInfo[i].archived_data);
            }
            //如果大于7则出现区间条
            var dataZoomList = [];
            var bottom_distance = "15%";//距离底部距离
            if(data_x.length>7){
                let zoomList = {
                    type: 'slider',
                    start: 0,
                    end: 50,
                    zoomLock: true,
                    show: true,
                    realtime: true,
                    minValueSpan: 6,
                    maxValueSpan: 6,
                    zoomOnMouseWheel: false, // 禁止鼠标滚轮缩放
                    moveOnMouseMove: false, // 禁止鼠标拖动移动
                    bottom: '15%',
                }
                dataZoomList.push(zoomList)
                bottom_distance ="30%";
            }

            var option;
            option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    },
                    formatter: function(params) {
                        var des = params[0].name + "<br/>";
                            for(var i=0; i<params.length; i++){
                                let dataVal = Number(params[i].value) ? params[i].value : 0;
                                let unit = "B";
                                if(dataVal >= 1024*1024*1024*1024){
                                    dataVal /= 1024*1024*1024*1024;
                                    unit = "TB";
                                } else if(dataVal >= 1024*1024*1024){
                                    dataVal /= 1024*1024*1024;
                                    unit = "GB";
                                } else if(dataVal >= 1024*1024){
                                    dataVal /= 1024*1024;
                                    unit = "MB";
                                } else if(dataVal >= 1024){
                                    dataVal /= 1024;
                                    unit = "KB";
                                }
                                des += params[i].seriesName +": "+ dataVal.toFixed(2) + " " + unit + "<br/>";
                            }
                            return des;
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: bottom_distance,
                    top:'10%',
                    containLabel: true
                },
                xAxis: [
                    {
                        type: 'category',
                        data: data_x,
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLine: {
                            show: false // 隐藏 x 轴线条
                        }
                    }
                ],
                dataZoom:dataZoomList,
                yAxis: [
                    {
                        type: 'value',
                        splitLine: {
                            lineStyle: {
                                type: 'dashed' // 这里将刻度线设置为虚线
                            }
                        },
                        axisLabel: {
                            formatter: function(value, index){
                                value = Number(value);
                                let unit = "B";
                                if(value >= 1024*1024*1024*1024){
                                    value /= 1024*1024*1024*1024;
                                    unit = "TB";
                                } else if(value >= 1024*1024*1024){
                                    value /= 1024*1024*1024;
                                    unit = "GB";
                                } else if(value >= 1024*1024){
                                    value /= 1024*1024;
                                    unit = "MB";
                                } else if(value >= 1024){
                                    value /= 1024;
                                    unit = "KB";
                                }
                                value = value.toFixed(2);
                                return value + unit;
                            },
                        }

                    }
                ],
                series: [
                    {
                        name: LANG.UI_REPORT_STORAGE,
                        type: 'bar',
                        barWidth: 8,
                        itemStyle: {
                            color:"#2453D0",
                            // borderRadius: 50 // 设置柱状图的圆角
                        },
                        data:  data_y_backup_data,
                    },
                    {
                        name: LANG.UI_HOMEPAGEPRO_COPY_ARCHIVE_STORAGE,
                        type: 'bar',
                        barWidth: 8,
                        itemStyle: {
                            color:"#6093FD",
                            // borderRadius: 50 // 设置柱状图的圆角
                        },
                        data: data_y_copy_data,
                    },
                   

                ],

                legend: {
                    bottom: "5%", // 将图例放在最下面
                    icon: 'rect', // 设置图例标记为直角长方形
                    itemWidth: 20, // 设置图例标记的宽度
                    itemHeight: 10, // 设置图例标记的高度
                    itemGap: 28, // 设置图例项之间的间隔为5%
                    padding: [0, 0, 0, 20] // 设置图例文字左边的间距
                }
            };

            echarts2.setOption(option,true);
            // 监听 datazoom 事件
            echarts2.on('datazoom', function (params) {
                // 获取当前的 start 和 end 值
                var startValue = params.start;
                var endValue = params.end;

                // 计算区间大小
                var range = endValue - startValue;

                // 如果区间大小不等于6（对应7个数据点），则重置区间大小
                if (range !== 6) {
                    echarts2.setOption({
                        dataZoom: {
                            start: startValue,
                            end: startValue + 6
                        }
                    });
                }
            });
        }

        //初始化echarts
        initechart1(params_data);
        initechart2(params_data);

        //存储时间段改变事件
        $('[gs-id="card_storage_all_1"] .selectpicker.value_storage_time_day').on('change',function(){
            var recent_days = parseInt($('[gs-id="card_storage_all_1"] .selectpicker.value_storage_time_day').val());
            var dataList = {};
            dataList.card_storage_all_1 = true;
            dataList.card_storage_all_1_params = {};
            dataList.card_storage_all_1_params.recent_days = recent_days;
            const requestSyncThis = (value) =>{
                if(!value.success){
                    //如果获取失败则直接给出提示
                    toastr.warning(LANG.UI_HOMEPAGEPRO_GET_DATA_ERROR1, value.message);
                }else{
                    initechart2(value.data.card_storage_all_1)
                }
            }
            //获取所有信息后发送后端
            pAjaxRequest(dataList, "/api/v1/homepage/info", "post", requestSyncThis,true);
        })


    }


    const init_card_system_alarm_all_1 = (params_data) =>{
        //单位换算
        function formatSizeKB(value) {
            const units = ['KB', 'MB', 'GB', 'TB'];
            let unit = 'KB';
            let index = 0;
            value = parseInt(value);
            while (value >= 1024 && index < units.length - 1) {
                value /= 1024;
                unit = units[++index];
            }
            return {value: Number(value.toFixed(2)), unit: unit};
        }
        //初始化BPS和IOPS
        let value_bps_read = formatSizeKB(params_data.bps.value_read)
        $('[gs-id="card_system_alarm_all_1"] .value_bps_read').text(value_bps_read['value']);
        $('[gs-id="card_system_alarm_all_1"] .value_bps_read_unit').text(value_bps_read['unit']+"/S");
        let value_bps_write = formatSizeKB(params_data.bps.value_write)
        $('[gs-id="card_system_alarm_all_1"] .value_bps_write').text(value_bps_write['value']);
        $('[gs-id="card_system_alarm_all_1"] .value_bps_write_unit').text(value_bps_write['unit']+"/S");

        $('[gs-id="card_system_alarm_all_1"] .value_iops_read').text(params_data.iops.value_read);
        $('[gs-id="card_system_alarm_all_1"] .value_iops_write').text(params_data.iops.value_write);
        //初始化流量图
        const initechart3 = (params_data) =>{
            var chartDom = document.querySelector('[gs-id="card_system_alarm_all_1"] .system_alarm_echarts_network');
            echarts3 = echarts.init(chartDom);
            var option;
            option = {
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        var des = params[0].name + "<br/>";
                        for(var i=0; i<params.length; i++){
                            let dataVal = params[i].value ? params[i].value : "0";
                            if(dataVal >= 1024){
                                des +=  params[i].seriesName +": "+ Math.round(dataVal / 1024) + " MB/s ";
                            }else{
                                des +=  params[i].seriesName +": "+ dataVal + " KB/s ";
                            }
                                des += "<br/>";
                            
                        }
                        return des;
                    }
                },
                legend: {
                    bottom: '5%', // 将图例放在最下面
                    icon: 'rect',
                    itemWidth: 12, // 设置图例标记的宽度
                    itemHeight: 4, // 设置图例标记的高度
                    padding: [0, 0, 0, 36] // 设置图例文字左边的间距
                },
                grid: {
                    left: '0',
                    right: '0',
                    bottom: '12%',
                    top:'5%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: params_data.network.value_x_data,

                },
                yAxis: [
                    {
                        type: 'value',
                        splitLine: {
                            lineStyle: {
                                type: 'dashed' // 这里将刻度线设置为虚线
                            }
                        },
                        axisLabel: {
                            formatter: function(value, index){
                                if(value >= 1024){
                                    return Math.round(value / 1024) + "MB/s";
                                }else{
                                    return value + "KB/s";
                                }
                            },
                        }
                    }
                ],
                series: [
                    {
                        name: params_data.network.value_y_data[0].name,
                        type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#2453D0',
                                width: 2.5
                            }
                        },
                        areaStyle: {
                            normal: {
                                color: new echarts.graphic.LinearGradient(
                                    0,
                                    0,
                                    0,
                                    1,
                                    [
                                        {
                                            offset: 0,
                                            color: 'rgba(36, 83, 208, 0.2)'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255,0.2)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#2453D0',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: params_data.network.value_y_data[0].data
                    },
                    {
                        name: params_data.network.value_y_data[1].name,
                        type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#71F4FE',
                                width: 2.5
                            }
                        },
                        areaStyle: {
                            normal: {
                                color: new echarts.graphic.LinearGradient(
                                    0,
                                    0,
                                    0,
                                    1,
                                    [
                                        {
                                            offset: 0,
                                            color: 'rgba(113, 244, 254, 0.2)'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255,0.2)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#71F4FE',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: params_data.network.value_y_data[1].data
                    }
                ]
            };


            echarts3.setOption(option);
        }

        //初始内存和cpu
        const initechart4 = (params_data) =>{
            var chartDom = document.querySelector('[gs-id="card_system_alarm_all_1"] .cpu_echarts_blue');
            echarts4 = echarts.init(chartDom);
            //需要对百分比处理下换算成小数
            var cpu_data = (params_data.cpu.value_percent / 100).toFixed(2);
            var option = {
                series: [
                    {
                        name: LANG.UI_DATACENTER_CPU_USED,
                        type: 'gauge',
                        radius: '80%',
                        center: ['50%', '50%'], // 设置仪表盘的位置
                        startAngle: 90,
                        endAngle: -269.9999,
                        min: 0,     // 最小值
                        max: 100,   // 最大值
                        splitNumber: 10, // 刻度数量
                        axisLine: {
                            show: true,
                            lineStyle: {
                                color: [[cpu_data, '#2453D0'], [1, '#E2E7ED']], // 0.7 是已使用的百分比，可以根据实际情况调整
                                width: 9 // 设置圈的宽度为 30px
                            }
                        },
                        splitLine: {
                            show: false,
                        },
                        axisLabel: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        pointer: {
                            show: false
                        },
                        detail: {
                            show: false
                        },
                        // data: [{value: 70, name: '已使用'}] // 已使用的数据
                    },
                ],
                title: {
                    text: [
                        '{a|'+params_data.cpu.value_percent+'%}\n{c|'+LANG.UI_DATACENTER_CPU_USED+'}'
                    ].join('\n'),
                    left: 'center',
                    top: 'center',
                    textStyle: {
                        rich: {
                            a: {
                                color: '#2D3748',
                                fontSize: 24,
                                fontWeight: 'bold',
                                padding: [0, 0, 4, 2] ,
                                lineHeight: 30
                            },
                            c: {
                                color: '#647080',
                                fontSize: 14,
                                lineHeight: 21
                            }
                        }
                    }
                }
            };
            echarts4.setOption(option);
        }


        const initechart5 = (params_data) =>{
            var chartDom = document.querySelector('[gs-id="card_system_alarm_all_1"] .root_echarts_blue');
            echarts5 = echarts.init(chartDom);
            var ram_data = (params_data.memory.value_percent / 100).toFixed(2);
            var option = {
                series: [
                    {
                        name: LANG.UI_PUBLIC_MEMORY_RATE,
                        type: 'gauge',
                        radius: '80%',
                        center: ['50%', '50%'], // 设置仪表盘的位置
                        startAngle: 90,
                        endAngle: -269.9999,
                        min: 0,     // 最小值
                        max: 100,   // 最大值
                        splitNumber: 10, // 刻度数量
                        axisLine: {
                            show: true,
                            lineStyle: {
                                color: [[ram_data, '#3873F0'], [1, '#E2E7ED']], // 0.7 是已使用的百分比，可以根据实际情况调整
                                width: 9 // 设置圈的宽度为 30px
                            }
                        },
                        splitLine: {
                            show: false,
                        },
                        axisLabel: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        pointer: {
                            show: false
                        },
                        detail: {
                            show: false
                        },
                        // data: [{value: 70, name: '已使用'}] // 已使用的数据
                    }
                ],

                title: {
                    text: [
                        '{a|'+params_data.memory.value_percent+'%}\n{c|'+ LANG.UI_PUBLIC_MEMORY_RATE +'}'
                    ].join('\n'),
                    left: 'center',
                    top: 'center',
                    textStyle: {
                        rich: {
                            a: {
                                color: '#2D3748',
                                fontSize: 24,
                                fontWeight: 'bold',
                                padding: [0, 0, 4, 2] ,
                                lineHeight: 30
                            },
                            b: {
                                color: '#788799',
                                fontSize: 14,
                                lineHeight: 21
                            },
                            c: {
                                color: '#647080',
                                fontSize: 14,
                                lineHeight: 21
                            }
                        }
                    }
                }
            };
            echarts5.setOption(option);
        }



        //初始化echarts
        initechart3(params_data);
        //初始化echarts
        initechart4(params_data);
        //初始化echarts
        initechart5(params_data);

    }




//---------------------------------初始化各种卡片end-------------------------------------------------------





    const initUser = ()=>{
        // 当前用户相关联的用户
        pAjaxRequest({},'/api/v1/users/auth_lists','GET',(d)=>{
            let rows = d.data.rows;
            current_userName = d.data.currentUser;
            let userselect = document.getElementById('user_select');
            let usernameEle = document.getElementById('username');
            $('#current_user_name').html(current_userName);
            rows.forEach(item => {
                const option = document.createElement('option');
                option.value = item.user_uuid;
                option.text = item.user_name;
                userselect.appendChild(option);
            });
        });

    }

    //-----------------------以下是首页额外要用的-----------------
    //初始化告警
    var initAlarmTips = function(){
        var getAlarmInfo = function(){
            $.post(CONF.AJAXPATH, {m:CONF.M.ALARM,f:'getSurveyNoticeInfo',p:{}}, function(d){
                setAlarmInfo(d);
            })
                .complete(function() {
                    setTimeout(getAlarmInfo, _TASKINTERVAL);});
        }
        getAlarmInfo();

    }
    //更新告警信息
    var updateAlarmTips = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.ALARM,f:'getSurveyNoticeInfo',p:{}}, function(d){
            setAlarmInfo(d);
        });
    }
    /**
     * 设置顶部告警ICON的样式
     * 无告警:	label-info
     * 有警告告警:	label-warning
     * 有错误告警:	label-danger
     */
    var setAlarmLabelIconClour = function(id, data){
        var clourSytel = "label-info";
        if(data.warn > 0){
            clourSytel = "label-warning";
        }
        if(data.error > 0){
            clourSytel = "label-danger";
        }
        $(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);
    }
    //设置告警信息
    var setAlarmInfo = function(d){
        var d = JSON.parse(d);
        $('.badgemark').remove();
        //初始化告警提示
        $('#alarmtask').html(d.alarm.task.error + d.alarm.task.warn);
        $('#alarmsystem').html(d.alarm.system.error + d.alarm.system.warn);

        var total = d.alarm.task.error + d.alarm.task.warn + d.alarm.system.error + d.alarm.system.warn;
        var errorTotal = d.alarm.task.error + d.alarm.system.error;
        var badgeType = "badge-warning";
        var tips = "";
        if(errorTotal > 0){
            badgeType = "badge-danger";
        }
        if(total > 0){
            var tips = '<span class="badge ' + badgeType + ' badgemark">' + total + '</span>';
        }
        $('#alarmtotal').find('span').remove();
        $('#alarmtotal').append(tips);

        setAlarmLabelIconClour(".alarmhreftask", d.alarm.task);
        setAlarmLabelIconClour(".alarmhrefsystem", d.alarm.system);


        //初始化任务提示
        $('#topcurrenttask').html(d.task.current);
        $('#tophistorytask').html(d.task.history);

        //初始化菜单提示
        var storage_manager = $('.page-sidebar-menu').find('a[name=storage_manager]');
        var authorization_module = $('.page-sidebar-menu').find('a[name=authorization_module]');
        var resource_manager = $('.page-sidebar-menu').find('a[name=resmanagement]');
        var system_manager = $('.page-sidebar-menu').find('a[name=sysmanagement]');

        if(d.storage){
            //管理备份存储
            var tips = '<span class="badge badge-warning badgemark line-height-18px w-18px ms-8 p-0">' + d.storage + '</span>';
            storage_manager.append(tips);
            //父级-系统管理
            resource_manager.append(tips);
        }
        if(d.lisence){
            //系统授权
            var tips = '<span class="badge badge-danger badgemark line-height-18px w-18px ms-8 p-0">' + d.lisence + '</span>';
            authorization_module.append(tips);
            //父级-系统管理
            system_manager.append(tips);
        }

    }
    /**
     * 设置顶部告警ICON的样式
     * 无告警:	label-info
     * 有警告告警:	label-warning
     * 有错误告警:	label-danger
     */
    var setAlarmLabelIconClour = function(id, data){
        var clourSytel = "label-info";
        if(data.warn > 0){
            clourSytel = "label-warning";
        }
        if(data.error > 0){
            clourSytel = "label-danger";
        }
        $(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);
    }
    //密码即将过期提示
    var initLoginHistory = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getLoginHistory'}, function(d){
            var data = JSON.parse(d);
            if(data.tips !=""){
                UIToastr.showWarning(LANG.UI_TENANT_HOME_PASSWORD_EXPIRE, data.tips);
            }
        });
    }
    //初始化是否为租户内部,并设置任务窗口事件
    var initUserType = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getUserExtendInfo',p:{}}, function(d){
            var data = JSON.parse(d);
            if(data.tenantuuid == "" && data.useruuid == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
                addManagerListener();
            }else if(data.tenantuuid == ""){
                addOperatorListener();
            }
        });

        $('.taskhrefcurrent').off().on('click', function(){
            LOCATION('./content/platform/jobs/jobs.php', 'task');
        });
        $('.taskhrefhistory').off().on('click', function(){
            LOCATION('./content/platform/jobs/jobs.php?tab=1', 'task');
        });
        $('.alarmhreftask').off().on('click', function(){
            LOCATION('./content/platform/alarm/alarm.php', 'alarm');
        });
        $('.alarmhrefsystem').off().on('click', function(){
            LOCATION('./content/platform/alarm/alarm.php?tab=1', 'alarm', { tabId: 'system_alarm' });
        });

    }
    //添加管理员事件
    var addManagerListener = function(){
        $('#moretaskinfo').on('click', function(){
            CTLHORMENU('bakandrec');
            var url = './content/platform/jobs/history_job.php';
            var urlMark = '?history_job';
            if($('#currenttaskli').hasClass("active")){
                url = './content/platform/jobs/jobs.php';
                urlMark = '?current_job';
            }
            LOCATION(url);
            History.pushState({url:url}, "", urlMark);
        });
        $('.currentjob').off().on('click', function(){
            CTLHORMENU('bakandrec');
        })
    }

    //添加操作员事件
    var addOperatorListener = function(){
        $('#moretaskinfo').on('click', function(){
            CTLHORMENU('bakandrec');
            var url = './content/platform/jobs/history_job.php';
            var urlMark = '?history_job';
            if($('#currenttaskli').hasClass("active")){
                url = './content/platform/jobs/jobs.php';
                urlMark = '?current_job';
            }
            LOCATION(url);
            History.pushState({url:url}, "", urlMark);
        });
        $('.currentjob').off().on('click', function(){
            CTLHORMENU('bakandrec');
        })
    }




    return {
        //main function to initiate the module
        init: function () {
            initGrid();
            initListener() //初始化监听事件
            // initUser();
            initUserType();
            initLoginHistory();
            initAlarmTips();
            const title = document.title;
            History.pushState({url:"./content/platform/databackup_center_pro.php"}, title, "?homepage");
            requestAnimationFrame(() => {
                document.title = title;
            });
        },
        //定义更新告警信息接口,添加虚拟化中心成功,添加存储成功后调用
        updateTopAlarmTips: function(){
            updateAlarmTips();
        },
       
    };

}();

jQuery(document).ready(function() {
    DataBackupCenter.init();
});
