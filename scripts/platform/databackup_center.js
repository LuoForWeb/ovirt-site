var DataBackupCenter = function () {
    var grid;//声明一个画布对象
    var echarts1,echarts2,echarts3,echarts4,echarts5,echarts6,
        echarts7,echarts8,echarts9,echarts10,echarts11,echarts12,
        echarts13,echarts14,echarts15,echarts16,echarts17,echarts18,
        echarts19,echarts20,echarts21,echarts22,echarts23,echarts24,
        echarts25,echarts26,echarts27,echarts28,echarts29,echarts30;//echarts实例,主要用于调整大小的时候使用
    var CARDLIST = [];
    var timeFlag = true;
    var USER_UUID = "";

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

        grid.on('resizestop', function(Event,GridItemHTMLElement) {
                initSizeEcharts();
            
        });
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
        if(CARDLIST.includes("card_current_job_1")){
            echarts1.resize();
        }
        if(CARDLIST.includes("card_backup_storage_1")){
            echarts2.resize();
        }
        if(CARDLIST.includes("card_copy_storage_pool_1")){
            echarts3.resize();
        }
        if(CARDLIST.includes("card_Archival_storage_1")){
            echarts4.resize();
        }
        if(CARDLIST.includes("card_network_1")){
            echarts5.resize();
        }
        if(CARDLIST.includes("card_vm_1")){
            echarts6.resize();
        }
        if(CARDLIST.includes("card_os_1")){
            echarts7.resize();
        }
        if(CARDLIST.includes("card_file_1")){
            echarts8.resize();
        }
        if(CARDLIST.includes("card_db_1")){
            echarts9.resize();
        }
        if(CARDLIST.includes("card_bps_1")){
            echarts10.resize();
        }
        if(CARDLIST.includes("card_iops_1")){
            echarts11.resize();
        }
        if(CARDLIST.includes("card_storage_7days_1")){
            echarts12.resize();
        }
        if(CARDLIST.includes("card_db_2")){
            echarts13.resize();
            echarts14.resize();
        }
        if(CARDLIST.includes("card_365_2")){
            echarts15.resize();
            echarts16.resize();
        }
        if(CARDLIST.includes("card_task_7days_1")){
            echarts17.resize();
        }
        if(CARDLIST.includes("card_nas_1")){
            echarts18.resize();
        }
        if(CARDLIST.includes("card_volcdp_1")){
            echarts19.resize();
        }
        if(CARDLIST.includes("card_aws_1")){
            echarts20.resize();
        }
        if(CARDLIST.includes("card_hadoop_1")){
            echarts21.resize();
        }
        if(CARDLIST.includes("card_obs_1")){
            echarts22.resize();
        }
        if(CARDLIST.includes("card_storage_1")){
            echarts23.resize();
        }
        if(CARDLIST.includes("card_all_module_backup_data_1")){
            echarts24.resize();
            echarts25.resize();
        }
        if(CARDLIST.includes("card_vol_cdp_protect_1")){
            echarts26.resize();
            echarts27.resize();
            echarts28.resize();
        }
        if(CARDLIST.includes("card_copy_1")){
            echarts29.resize();
            echarts30.resize();
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
                toastr.warning(LANG.UI_HOMEPAGE_GET_CONFIG_FAILED, value.message);
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
                handles: 'se'  //允许在所有方向上调整大小
            },
            disableDrag: false, //允许拖动
            margin: 10, //方块之间的间隔
            cellHeight: 25, //每个方块的高度
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
             //修改样式
             $(".editContainer").removeClass("editContainerHover");
             $(".editContainer .recoverBut").css("display","block");
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
                    toastr.success(value.message+ LANG.UI_REFRESH_5, LANG.UI_HOMEPAGE_SAVE_HOMEPAGE);
                    //刷新当前页面
                    setTimeout(()=>{
                        window.location.reload();
                    },5000);
                }else{
                    //如果获取失败则直接给出提示
                    toastr.warning(value.message, LANG.UI_HOMEPAGE_SAVE_HOMEPAGE);
                }
            }
            //获取所有信息后发送后端
            pAjaxRequest(dataList, "/api/v1/homepage/save_card", "post", requestSyncThis,true);


        });

        // 启用拖动和缩放
        $('#editBut').click(function() {
            //修改样式
            $(".editContainer").addClass("editContainerHover");
            $(".editContainer .editBut").css("display","none");
            $(".editContainer .recoverBut").css("display","none");
            $(".editContainer .saveBut").css("display","block")
            $(".editContainer .box_icon").css("background-color","#41D46A");
            $(".editContainer .icon_editPage").addClass("icon_editPageCom");
            controlCard(true);
        });

        // 恢复默认
        $('#recoverBut').click(function() {
            var dataList ={}
            dataList.user_uuid = USER_UUID;
            //修改样式
            const requestSyncThis = (value) =>{
                if(!value.success){
                    //如果获取失败则直接给出提示
                    toastr.warning(value.message, LANG.UI_HOMEPAGE_DEFALULT_CARD);
                }else{
                    toastr.success(value.message+LANG.UI_REFRESH_5, LANG.UI_HOMEPAGE_DEFALULT_CARD);
                    //刷新当前页面
                    setTimeout(()=>{
                        window.location.reload();
                    },5000);
                }
            }
            //获取所有信息后发送后端
            pAjaxRequest(dataList, "/api/v1/homepage/recover_grid", "post", requestSyncThis,false);
        });


        // 选择关联用户后的卡片更新
        $(document).on('change', '#user_select', () => {
            let userVal = $('#user_select').find('option:selected').val();
            let userName = $('#user_select').find('option:selected').text();
            userName = userVal ? userName : current_userName;
            //这里如果是选择了其他用户,则不能编辑首页
            if(userVal == "" || userVal == undefined || userVal == null){
                $(".editContainer").css("display","block");
            }else{
                $(".editContainer").css("display","none");
            }
            USER_UUID = userVal;
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
            initUser(userVal);
        });

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
        var allInfo = {};
        const requestSyncThis = (value) =>{
            if(!value.success){
                //如果获取失败则直接给出提示
                toastr.warning(LANG.UI_HOMEPAGE_GET_DATA_FAILED, value.message);
            }else{
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
        //这里根据配置信息来确认哪些卡片需要初始化
        if(CARDLIST.includes("card_basic_data")){
            init_card_basic_data(info.card_basic_data);
        }
        if(CARDLIST.includes("card_current_job_1")){
            init_card_current_job_1(info.card_current_job_1);
        }
        if(CARDLIST.includes("card_backup_storage_1")){
            init_card_backup_storage_1(info.card_backup_storage_1);
        }
        if(CARDLIST.includes("card_copy_storage_pool_1")){
            init_card_copy_storage_pool_1(info.card_copy_storage_pool_1);
        }
        if(CARDLIST.includes("card_Archival_storage_1")){
            init_card_Archival_storage_1(info.card_Archival_storage_1);
        }
        if(CARDLIST.includes("card_iops_1")){
            init_card_iops_1(info.card_iops_1);
        }
        if(CARDLIST.includes("card_log_1")){
            init_card_log_1(info.card_log_1);
        }
        if(CARDLIST.includes("card_alarm_1")){
            init_card_alarm_1(info.card_alarm_1);
        }
        if(CARDLIST.includes("card_cpu_1")){
            init_card_cpu_1(info.card_cpu_1);
        }
        if(CARDLIST.includes("card_memory_1")){
            init_card_memory_1(info.card_memory_1);
        }
        if(CARDLIST.includes("card_task_log_1")){
            init_card_task_log_1(info.card_task_log_1);
        }
        if(CARDLIST.includes("card_system_log_1")){
            init_card_system_log_1(info.card_system_log_1);
        }
        if(CARDLIST.includes("card_system_alarm_1")){
            init_card_system_alarm_1(info.card_system_alarm_1);
        }
        if(CARDLIST.includes("card_task_alarm_1")){
            init_card_task_alarm_1(info.card_task_alarm_1);
        }
        if(CARDLIST.includes("card_file_1")){
            init_card_file_1(info.card_file_1);
        }
        if(CARDLIST.includes("card_db_1")){
            init_card_db_1(info.card_db_1);
        }
        if(CARDLIST.includes("card_vm_1")){
            init_card_vm_1(info.card_vm_1);
        }
        if(CARDLIST.includes("card_os_1")){
            init_card_os_1(info.card_os_1);
        }
        if(CARDLIST.includes("card_network_1")){
            init_card_network_1(info.card_network_1);
        }
        if(CARDLIST.includes("card_bps_1")){
            init_card_bps_1(info.card_bps_1);
        }
        if(CARDLIST.includes("card_storage_7days_1")){
            init_card_storage_7days_1(info.card_storage_7days_1);
        }
        if(CARDLIST.includes("card_db_2")){
            init_card_db_2(info.card_db_2);
        }
        if(CARDLIST.includes("card_365_2")){
            init_card_365_2(info.card_365_2);
        }
        if(CARDLIST.includes("card_task_7days_1")){
            init_card_task_7days_1(info.card_task_7days_1);
        }
        if(CARDLIST.includes("card_basic_2")){
            init_card_basic_2(info.card_basic_2);
        }
        if(CARDLIST.includes("card_basic_3")){
            init_card_basic_3(info.card_basic_3);
        }
        if(CARDLIST.includes("card_CDM_1")){
            init_card_CDM_1(info.card_CDM_1);
        }
        if(CARDLIST.includes("card_alarm_2")){
            init_card_alarm_2(info.card_alarm_2);
        }
        if(CARDLIST.includes("card_nas_1")){
            init_card_nas_1(info.card_nas_1);
        }
        if(CARDLIST.includes("card_volcdp_1")){
            init_card_volcdp_1(info.card_volcdp_1);
        }
        if(CARDLIST.includes("card_aws_1")){
            init_card_aws_1(info.card_aws_1);
        }
        if(CARDLIST.includes("card_hadoop_1")){
            init_card_hadoop_1(info.card_hadoop_1);
        }
        if(CARDLIST.includes("card_obs_1")){
            init_card_obs_1(info.card_obs_1);
        }
        if(CARDLIST.includes("card_storage_1")){
            init_card_storage_1(info.card_storage_1);
        }
        if(CARDLIST.includes("card_all_module_backup_data_1")){
            init_card_all_module_backup_data_1(info.card_all_module_backup_data_1);
        }
        if(CARDLIST.includes("card_vol_cdp_protect_1")){
            init_card_vol_cdp_protect_1(info.card_all_module_backup_data_1);
        }
        if(CARDLIST.includes("card_copy_1")){
            init_card_copy_1(info.card_all_module_backup_data_1);
        }

        





        //定时器
        //先清除定时器
        // 定时器管理
        if (timerTask.intervalData_datacenter) {
            clearInterval(timerTask.intervalData_datacenter);
            //删除timerTask里面的定时器
            delete timerTask.intervalData_datacenter;
        }
        //获取当前时间
        if((CARDLIST.includes("card_iops_1") ||
            CARDLIST.includes("card_network_1") ||
            CARDLIST.includes("card_cpu_1") ||
            CARDLIST.includes("card_memory_1") ||
            CARDLIST.includes("card_bps_1")) && timeFlag){
                timeFlag = false;
            //这里需要开始对需要刷新的数据启用定时器
            timerTask.intervalData_datacenter = setInterval(() => {
                if($(".grid-stack").length == 0){
                    clearInterval(timerTask.intervalData_datacenter);
                    return;
                }
                setIntervalFunction();
            }, 3000);
        }


    }

    const setIntervalFunction = () =>{
        var setIntervalList = [];
        if(CARDLIST.includes("card_iops_1")){
            setIntervalList.push("card_iops_1");
        }
        if(CARDLIST.includes("card_network_1")){
            setIntervalList.push("card_network_1");
        }
        if(CARDLIST.includes("card_cpu_1")){
            setIntervalList.push("card_cpu_1");
        }
        if(CARDLIST.includes("card_memory_1")){
            setIntervalList.push("card_memory_1");
        }
        if(CARDLIST.includes("card_bps_1")){
            setIntervalList.push("card_bps_1");
        }

        var dataList = {};
        for (const setIntervalListElement of setIntervalList) {
            dataList[setIntervalListElement] = true;
        }

        const requestSyncThis = (value) =>{
            let info = value.data;
            if(setIntervalList.includes("card_iops_1")){
                init_card_iops_1(info.card_iops_1);
            }
            if(setIntervalList.includes("card_network_1")){
                init_card_network_1(info.card_network_1);
            }
            if(setIntervalList.includes("card_cpu_1")){
                init_card_cpu_1(info.card_cpu_1);
            }
            if(setIntervalList.includes("card_memory_1")){
                init_card_memory_1(info.card_memory_1);
            }
            if(setIntervalList.includes("card_bps_1")){
                init_card_bps_1(info.card_bps_1);
            }
        }
        //获取所有信息后发送后端
        pAjaxRequest(dataList, "/api/v1/homepage/info", "post", requestSyncThis,true);
    }


//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    const init_card_alarm_2 = (info) =>{
        $('[gs-id="card_alarm_2"] .info_value_task_alarm_count').text(info.value_task_alarm_count);
        $('[gs-id="card_alarm_2"] .info_value_task_alarm_level_warning').text(info.value_task_alarm_level_warning);
        $('[gs-id="card_alarm_2"] .info_value_task_alarm_level_error').text(info.value_task_alarm_level_error);
        $('[gs-id="card_alarm_2"] .info_value_system_alarm_count').text(info.value_system_alarm_count);
        $('[gs-id="card_alarm_2"] .info_value_system_alarm_level_warning').text(info.value_system_alarm_level_warning);
        $('[gs-id="card_alarm_2"] .info_value_system_alarm_level_error').text(info.value_system_alarm_level_error);
    }
//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    const init_card_CDM_1 = (info) =>{
        $('[gs-id="card_CDM_1"] .info_laboratoryNum').text(info.laboratoryNum);
        $('[gs-id="card_CDM_1"] .info_verifyNum').text(info.verifyNum);
        $('[gs-id="card_CDM_1"] .info_verifyingNum').text(info.verifyingNum);
        $('[gs-id="card_CDM_1"] .info_verifyTimes').text(info.verifyTimes);
        $('[gs-id="card_CDM_1"] .info_falieTimes').text(info.falieTimes);
    }
//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    const init_card_basic_3 = (info) =>{
        $('[gs-id="card_basic_3"] .info_value_days').text(info.value_days+LANG.UI_PUBLIC_UNIT_DAY);
        $('[gs-id="card_basic_3"] .info_value_size').text(info.value_size+info.value_size_unit);
        $('[gs-id="card_basic_3"] .info_value_current_count').text(info.value_current_count);
        $('[gs-id="card_basic_3"] .info_value_history_count').text(info.value_history_count);
    }
//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    const init_card_basic_2 = (info) =>{
        $('[gs-id="card_basic_2"] .info_value_days').text(info.value_days);
        $('[gs-id="card_basic_2"] .info_value_size').text(info.value_size);
        $('[gs-id="card_basic_2"] .info_value_size_unit').text(info.value_size_unit);
        $('[gs-id="card_basic_2"] .info_value_current_count').text(info.value_current_count);
        $('[gs-id="card_basic_2"] .info_value_history_count').text(info.value_history_count);
    }
//---------------------------------初始化卡片card_task_log_1-------------------------------------------------------
    const init_card_task_alarm_1 = (info) =>{
        if(info == ""|| info == null || info == undefined || info.length == 0){
            htmlContent = '<div class="content_null">\n' +
                '                        <div class="content_null_img"></div>\n' +
                '                    </div>'
            $('[gs-id="card_task_alarm_1"] .content_all').html(htmlContent);
            return;
        }
        var htmlContent = "";
        for (let i = 0; i < info.length; i++) {
            let colorLevelDes = '';
            switch (info[i].system_alarm_status_int){
                case 0:
                    break;
                case 1:
                    colorLevelDes = 'badge-success';
                    break;
                case 2:
                    colorLevelDes = 'badge-warning';
                    break;
                case 3:
                    colorLevelDes = 'badge-danger';
                    break;
            }
            let colorResponseDes = info[i].system_alarm_response_status_int == 2 ? "badge-default" : "badge-success";


            htmlContent += `
                <div class="content_each">
                    <div class="inline_div">
                        <div class="color_status ${colorLevelDes}"></div>
                        <span class="alarm_content">${info[i].system_alarm_description}</span>
                    </div>
                    <div class="alarm_time">
                        <span>${info[i].system_alarm_time}</span>
                        <span class="badge ${colorLevelDes} badge-sm alarm_status_10">${info[i].system_alarm_status_des}</span>
                        <span class="badge ${colorResponseDes} badge-sm alarm_status">${info[i].system_alarm_response_status_des}</span>
                    </div>
                </div>
            `;
        }
        $('[gs-id="card_task_alarm_1"] .content_all').html(htmlContent);
    }
//---------------------------------初始化卡片card_task_log_1-------------------------------------------------------
    const init_card_system_alarm_1 = (info) =>{
        if(info == ""|| info == null || info == undefined || info.length == 0){
            htmlContent = '<div class="content_null">\n' +
                '                        <div class="content_null_img"></div>\n' +
                '                    </div>'
            $('[gs-id="card_system_alarm_1"] .content_all').html(htmlContent);
            return;
        }
        var htmlContent = "";
        for (let i = 0; i < info.length; i++) {
            let colorDes = '';
            switch (info[i].system_alarm_status_int){
                case 0:
                    break;
                case 1:
                    colorDes = 'badge-success';
                    break;
                case 2:
                    colorDes = 'badge-warning';
                    break;
                case 3:
                    colorDes = 'badge-danger';
                    break;
            }
            let colorResponseDes = info[i].system_alarm_response_status_int == 2 ? "badge-default" : "badge-success";
            htmlContent += `
                <div class="content_each">
                    <div class="inline_div">
                        <div class="color_status ${colorDes}"></div>
                        <span class="alarm_content">${info[i].system_alarm_description}</span>
                    </div>
                    <div class="alarm_time">
                        <span>${info[i].system_alarm_time}</span>
                        <span class="badge ${colorDes} badge-sm alarm_status_10">${info[i].system_alarm_status_des}</span>
                        <span class="badge ${colorResponseDes} badge-sm alarm_status">${info[i].system_alarm_response_status_des}</span>
                    </div>
                </div>
            `;
        }
        $('[gs-id="card_system_alarm_1"] .content_all').html(htmlContent);
    }

//---------------------------------初始化卡片card_task_log_1-------------------------------------------------------
    const init_card_system_log_1 = (info) =>{
        if(info == ""|| info == null || info == undefined || info.length == 0){
            htmlContent = '<div class="content_null">\n' +
                '                        <div class="content_null_img"></div>\n' +
                '                    </div>'
            $('[gs-id="card_system_log_1"] .content_all').html(htmlContent);
            return;
        }
        var htmlContent = "";
        for (let i = 0; i < info.length; i++) {
            let colorDes = '';
            switch (info[i].system_alarm_status_int){
                case 0:
                    break;
                case 1:
                    colorDes = 'badge-success';
                    break;
                case 2:
                    colorDes = 'badge-warning';
                    break;
                case 3:
                    colorDes = 'badge-danger';
                    break;
            }
            htmlContent += `
                <div class="content_each">
                    <div class="inline_div">
                        <div class="color_status ${colorDes}"></div>
                        <span class="alarm_content">${info[i].system_alarm_description}</span>
                    </div>
                    <div class="alarm_time">
                        <span>${info[i].system_alarm_time}</span>
                        <span class="badge ${colorDes} badge-sm alarm_status_10">${info[i].system_alarm_status_des}</span>
                    </div>
                </div>
            `;
        }
        $('[gs-id="card_system_log_1"] .content_all').html(htmlContent);
    }
//---------------------------------初始化卡片card_task_log_1-------------------------------------------------------
    const init_card_task_log_1 = (info) =>{
        if(info == ""|| info == null || info == undefined || info.length == 0){
            htmlContent = '<div class="content_null">\n' +
                '                        <div class="content_null_img"></div>\n' +
                '                    </div>'
            $('[gs-id="card_task_log_1"] .content_all').html(htmlContent);
            return;
        }
        var htmlContent = "";
        for (let i = 0; i < info.length; i++) {
            let colorDes = '';
            switch (info[i].system_alarm_status_int){
                case 0:
                    break;
                case 1:
                    colorDes = 'badge-success';
                    break;
                case 2:
                    colorDes = 'badge-warning';
                    break;
                case 3:
                    colorDes = 'badge-danger';
                    break;
            }
            htmlContent += `
                <div class="content_each">
                    <div class="inline_div">
                        <div class="color_status ${colorDes}"></div>
                        <span class="alarm_content">${info[i].system_alarm_description}</span>
                    </div>
                    <div class="alarm_time">
                        <span>${info[i].system_alarm_time}</span>
                        <span class="badge ${colorDes} badge-sm alarm_status_10">${info[i].system_alarm_status_des}</span>
                    </div>
                </div>
            `;
        }
        $('[gs-id="card_task_log_1"] .content_all').html(htmlContent);

    }
//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    const init_card_memory_1 = (info) =>{
        // 保留两位小数
        const percentValue = parseFloat(info.value_percent).toFixed(2);
        $('[gs-id="card_memory_1"] .info_value_percent').text(percentValue+"%");
        $('[gs-id="card_memory_1"] .info_value_percent_width').css('width',percentValue+"%");

    }
//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    const init_card_cpu_1 = (info) =>{
         // 保留两位小数
        const percentValue = parseFloat(info.value_percent).toFixed(2);
        $('[gs-id="card_cpu_1"] .info_value_percent').text(percentValue+"%");
        $('[gs-id="card_cpu_1"] .info_value_percent_width').css('width',percentValue+"%");

    }
//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    const init_card_alarm_1 = (info) =>{
        
        if(!CONF.PERMISSION.includes('system_alarm')){
            $(".system_alarm_show").hide();
        }
        $('[gs-id="card_alarm_1"] .info_value_task_alarm_count').text(info.value_task_alarm_count);
        $('[gs-id="card_alarm_1"] .info_value_task_alarm_level_warning').text(info.value_task_alarm_level_warning);
        $('[gs-id="card_alarm_1"] .info_value_task_alarm_level_error').text(info.value_task_alarm_level_error);
        $('[gs-id="card_alarm_1"] .info_value_system_alarm_count').text(info.value_system_alarm_count);
        $('[gs-id="card_alarm_1"] .info_value_system_alarm_level_warning').text(info.value_system_alarm_level_warning);
        $('[gs-id="card_alarm_1"] .info_value_system_alarm_level_error').text(info.value_system_alarm_level_error);
    }
//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    const init_card_log_1 = (info) =>{
        $('[gs-id="card_log_1"] .info_value_task_log_count').text(info.value_task_log_count);
        $('[gs-id="card_log_1"] .info_value_system_log_count').text(info.value_system_log_count);
    }
//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    const init_card_basic_data = (info) =>{
        $('[gs-id="card_basic_data"] .info_value_days').text(info.value_days);
        $('[gs-id="card_basic_data"] .info_value_size').text(info.value_size);
        $('[gs-id="card_basic_data"] .info_value_size_unit').text(info.value_size_unit);
        $('[gs-id="card_basic_data"] .info_value_current_count').text(info.value_current_count);
        $('[gs-id="card_basic_data"] .info_value_history_count').text(info.value_history_count);
    }
//---------------------------------初始化卡片card_current_job_1-------------------------------------------------------
    //初始化卡片
    const init_card_current_job_1 = (info) => {
        var chartDom = document.querySelector('[gs-id="card_current_job_1"] .echarts_current_job_1');
        echarts1 = echarts.init(chartDom);

        let mapping = {
            "running": { "name": LANG.UI_PUBLIC_RUNNING, "color": "#20CCDD" },
            "wait": { "name": LANG.UI_PUBLIC_WAIT, "color": "#10AEFF" },
            "success": { "name": LANG.UI_VISUAL_SUCCESS, "color": "#2CCE8B" },
            "abnormal": { "name": LANG.UI_VISUAL_NODE_ABNORMAL, "color": "#FACC15" },
            "fail": { "name": LANG.UI_VISUAL_FAIL, "color": "#F56A6A" }
        };

        let result = [];

        for (let key in info) {
            if (info.hasOwnProperty(key) && (key != "all" && key != "current_total")) {
                result.push({
                    value: info[key].value_count,
                    name: mapping[key].name,
                    itemStyle: { color: mapping[key].color }
                });
            }
        }

        //初始化页面数据
        $('[gs-id="card_current_job_1"] .info_running_percent').text(info.running.value_percent+"%");
        $('[gs-id="card_current_job_1"] .info_running_count').text(info.running.value_count);
        $('[gs-id="card_current_job_1"] .info_wait_percent').text(info.wait.value_percent+"%");
        $('[gs-id="card_current_job_1"] .info_wait_count').text(info.wait.value_count);
        $('[gs-id="card_current_job_1"] .info_success_percent').text(info.success.value_percent+"%");
        $('[gs-id="card_current_job_1"] .info_success_count').text(info.success.value_count);
        $('[gs-id="card_current_job_1"] .info_abnormal_percent').text(info.abnormal.value_percent+"%");
        $('[gs-id="card_current_job_1"] .info_abnormal_count').text(info.abnormal.value_count);
        $('[gs-id="card_current_job_1"] .info_fail_percent').text(info.fail.value_percent+"%");
        $('[gs-id="card_current_job_1"] .info_fail_count').text(info.fail.value_count);
        let totalNum = parseInt(info.running.value_count+info.wait.value_count+info.success.value_count+info.abnormal.value_count+info.fail.value_count);

        var option;
        option = {
            tooltip: {
                trigger: 'item',
                confine: true,
                formatter: '{a} <br/>{b} : {c} ({d}%)'
            },
            series: [
                {
                    name: LANG.UI_HOMEPAGEPRO_TASK,
                    type: 'pie',
                    radius: ['75%', '95%'],
                    center: ['50%', '50%'],
                    label: {
                        show: true,
                        position: 'center',
                        formatter: function (params) {
                            return ['{a|' + totalNum + '}', '{b|'+ LANG.UI_HOMEPAGEPRO_TASK_NUM +'}'].join('\n');
                        },
                        rich: {
                            a: {
                                color: '#2D3748',
                                fontSize: 28,
                                lineHeight: 33
                            },
                            b: {
                                color: '#647080',
                                fontSize: 14,
                                lineHeight: 20
                            }
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: result,
                }
            ]
        };
        echarts1.setOption(option);
    };
//---------------------------------card_backup_storage_1-------------------------------------------------------
    const init_card_backup_storage_1 = (info) =>{
        const initechart2 = () =>{
            var chartDom = document.querySelector('[gs-id="card_backup_storage_1"] .echarts_backup_storage_1');
            echarts2 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_backup_storage_1"] .info_storage_num').text(info.value_storage_num);
            $('[gs-id="card_backup_storage_1"] .info_storage_size_all').text(info.value_storage_size_all+info.value_storage_size_all_unit);
            let percent_used = (info.value_storage_size_used_int / info.value_storage_size_all_int * 100).toFixed(2);
            let percent_free = (info.value_storage_size_free_int / info.value_storage_size_all_int * 100).toFixed(2);
            if(info.value_storage_size_all_int == 0){
                percent_used =  0;
                percent_free = 0;
            }
            $('[gs-id="card_backup_storage_1"] .info_storage_size_used_percent').text(LANG.UI_HOMEPAGE_USED_STORAGE+"("+percent_used+"%)");
            $('[gs-id="card_backup_storage_1"] .info_storage_size_free_percent').text(LANG.UI_HOMEPAGE_REMAIN_STORAGE+"("+percent_free+"%)");
            $('[gs-id="card_backup_storage_1"] .info_storage_size_used').text(info.value_storage_size_used+info.value_storage_size_used_unit);
            $('[gs-id="card_backup_storage_1"] .info_storage_size_free').text(info.value_storage_size_free+info.value_storage_size_free_unit);

            var option;
            option = {
                tooltip: {
                    trigger: 'item',
                    confine: true,
                    formatter: function(params) {
                        let unit = params.name === LANG.UI_HOMEPAGE_USED_STORAGE ? info.value_storage_size_used_unit : info.value_storage_size_free_unit;
                        let value = params.name === LANG.UI_HOMEPAGE_USED_STORAGE ? info.value_storage_size_used : info.value_storage_size_free;
                        return params.seriesName + '<br>' + params.name + ': ' + value + unit + ' (' + params.percent + '%)';
                    }
                },

                series: [
                    {
                        name: LANG.UI_REPORT_STORAGE,
                        type: 'pie',
                        radius: ['50%', '70%'],
                        center: ['50%', '55%'],
                        label: {
                            show: true,
                            position: 'center',
                            formatter: function (params) {
                                var total = info.value_storage_size_all+info.value_storage_size_all_unit;
                                return ['{a|' + total + '}', '{b|'+LANG.UI_VISUAL_STORAGE_TOTAL +'}'].join('\n');
                            },
                            rich: {
                                a: {
                                    color: '#2D3748',
                                    fontSize: 28,
                                    lineHeight: 33
                                },
                                b: {
                                    color: '#647080',
                                    fontSize: 14,
                                    lineHeight: 20
                                }
                            }
                        },
                        hoverAnimation: false,//禁止鼠标事件
                        data: [
                            { value: info.value_storage_size_used_int, name: LANG.UI_HOMEPAGE_USED_STORAGE,itemStyle: {color: '#31D395', emphasis: { color: '#31D395' }}},
                            { value: info.value_storage_size_free_int, name: LANG.UI_HOMEPAGE_REMAIN_STORAGE,itemStyle: { color: '#E9EEF3', emphasis: { color: '#E9EEF3' } }},
                        ]
                    }
                ]
            };
            echarts2.setOption(option);
        }
        //初始化echarts
        initechart2();
    }


    //---------------------------------card_copy_storage_pool_1-------------------------------------------------------
    const init_card_copy_storage_pool_1 = (info) =>{
        const initechart3 = () =>{
            var chartDom = document.querySelector('[gs-id="card_copy_storage_pool_1"] .echarts_copy_storage_pool_1');
            echarts3 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_copy_storage_pool_1"] .info_storage_num').text(info.value_storage_num);
            $('[gs-id="card_copy_storage_pool_1"] .info_storage_size_all').text(info.value_storage_size_all+info.value_storage_size_all_unit);

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
                    bottom: '15%',
                    top:'10%',
                    containLabel: true
                },
                xAxis: [
                    {
                        type: 'category',
                        data: info.card_storage_7days_1.value_x_data,
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLine: {
                            show: false // 隐藏 x 轴线条
                        }
                    }
                ],
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
                        name: LANG.UI_HOMEPAGEPRO_COPY_ARCHIVE_STORAGE,
                        type: 'bar',
                        barWidth: '25%',
                        itemStyle: {
                            color: '#2CCE8B',
                            borderRadius: 50 // 设置柱状图的圆角
                        },
                        data: info.card_storage_7days_1.value_y_data.copy_size.data,
                    },

                ],
                legend: {
                    bottom: "5%", // 将图例放在最下面
                    icon: 'circle',
                    padding: [0, 0, 0, 20] // 设置图例文字左边的间距
                }
            };
            echarts3.setOption(option);
        }
        //初始化echarts
        initechart3();
    }

//---------------------------------card_Archival_storage_1-------------------------------------------------------
    const init_card_Archival_storage_1 = (info) =>{
        const initechart4 = () =>{
            var chartDom = document.querySelector('[gs-id="card_Archival_storage_1"] .echarts_Archival_storage_1');
            echarts4 = echarts.init(chartDom);
            //初始化页面数据
            $('[gs-id="card_Archival_storage_1"] .info_storage_num').text(info.value_storage_num);
            $('[gs-id="card_Archival_storage_1"] .info_storage_size_all').text(info.value_storage_size_all+info.value_storage_size_all_unit);

            var option;
            option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    top:'10%',
                    containLabel: true
                },
                xAxis: [
                    {
                        type: 'category',
                        data: info.card_storage_7days_1.value_x_data,
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLine: {
                            show: false // 隐藏 x 轴线条
                        }
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        splitLine: {
                            lineStyle: {
                                type: 'dashed' // 这里将刻度线设置为虚线
                            }
                        }
                    }
                ],
                series: [
                    {
                        name: LANG.UI_HOMEPAGE_ARCHIVAL_STORAGE,
                        type: 'bar',
                        barWidth: '25%',
                        itemStyle: {
                            color: '#10AEFF',
                            borderRadius: 50 // 设置柱状图的圆角
                        },
                        data: info.card_storage_7days_1.value_y_data.copy_size.data,
                    },
                ],
                legend: {
                    bottom: "5%", // 将图例放在最下面
                    icon: 'circle',
                    padding: [0, 0, 0, 20] // 设置图例文字左边的间距
                }
            };
            echarts4.setOption(option);
        }
        //初始化echarts
        initechart4();
    }
//---------------------------------card_network_1-------------------------------------------------------
    const init_card_network_1 = (info) =>{
        const initechart5 = () =>{
            var chartDom = document.querySelector('[gs-id="card_network_1"] .echarts_network_1');
            echarts5 = echarts.init(chartDom);

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
                    icon: 'circle',
                    padding: [0, 0, 0, 20] // 设置图例文字左边的间距
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    top:'10%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: info.value_x_data
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
                        name: info.value_y_data[0].name,
                        type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#20C99A',
                                width: 2
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
                                            color: '#02e3a33c'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#20C99A',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[0].data
                    },
                    {
                        name: info.value_y_data[1].name,
                        type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#10AEFF',
                                width: 2
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
                                            color: '#1eb0f94e'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#10AEFF',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[1].data
                    }
                ]
            };
            echarts5.setOption(option);
        }
        //初始化echarts
        initechart5();
    }

//---------------------------------card_vm_1-------------------------------------------------------
    const init_card_vm_1 = (info) =>{
        const initechart6 = () =>{
            var chartDom = document.querySelector('[gs-id="card_vm_1"] .echarts_vm_1');
            echarts6 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_vm_1"] .info_size').text(info.backupData.des);
            $('[gs-id="card_vm_1"] .info_protectedNum').text(info.protectedNum);
            $('[gs-id="card_vm_1"] .info_unProtectedNum').text(parseInt(info.totalNum)-parseInt(info.protectedNum));
            let protected_percent = (parseInt(info.protectedNum) / parseInt(info.totalNum) * 100).toFixed(2);

            var option = {
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',  // 大一圈的白色圆
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                // borderWidth: 20,
                                color: [[1, 'rgba(255, 255, 255, 1)']],  // 白色
                                width: 1000,  // 宽度设为100
                                // shadowBlur: 80,  // 阴影模糊大小
                                // shadowColor: 'rgba(226, 236, 249, 0.50)',  // 阴影颜色
                                // shadowOffsetX: 10,  // 阴影水平偏移量
                                // shadowOffsetY: 20,  // 阴影水平偏移量
                            },
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        }
                    },
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius:'80%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width:18,
                            itemStyle: {
                                borderWidth: 18,  // 运动轨迹宽度
                                borderColor: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),  // 已使用的渐变色
                                shadowBlur: 30,  // 阴影模糊大小
                                shadowColor: 'rgba(135, 145, 233, 0.30)',  // 阴影颜色
                                shadowOffsetX: 8,  // 阴影水平偏移量
                                shadowOffsetY: 12,  // 阴影水平偏移量
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, 'rgba(239, 251, 245, 1)']],  // 运动轨迹颜色
                                width: 20  // 进度条宽度
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 20
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [
                            {
                                value: protected_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return [
                                    '{a|'+info.totalNum+'}\n',  // 第一行数字
                                    '{b|'+ LANG.UI_SETTING_VM_NUM +'}'  // 第二行文字
                                ].join('');
                            },
                            rich: {
                                a: {
                                    fontSize: 28,
                                    color: '#2D3748'
                                },
                                b: {
                                    fontSize: 14,
                                    color: '#647080'
                                }
                            },
                        },
                    }
                ]
            };
            echarts6.setOption(option);
        }
        //初始化echarts
        initechart6();
    }

//---------------------------------card_os_1-------------------------------------------------------
    const init_card_os_1 = (info) =>{
        const initechart7 = () =>{
            var chartDom = document.querySelector('[gs-id="card_os_1"] .echarts_os_1');
            echarts7 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_os_1"] .info_size').text(info.backupData.des);
            $('[gs-id="card_os_1"] .info_protectedNum').text(info.protectedNum);
            $('[gs-id="card_os_1"] .info_unProtectedNum').text(parseInt(info.totalNum)-parseInt(info.protectedNum));
            let protected_percent = (parseInt(info.protectedNum) / parseInt(info.totalNum) * 100).toFixed(2);

            var option = {
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',  // 大一圈的白色圆
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                // borderWidth: 20,
                                color: [[1, 'rgba(255, 255, 255, 1)']],  // 白色
                                width: 1000,  // 宽度设为100
                                // shadowBlur: 80,  // 阴影模糊大小
                                // shadowColor: 'rgba(226, 236, 249, 0.50)',  // 阴影颜色
                                // shadowOffsetX: 10,  // 阴影水平偏移量
                                // shadowOffsetY: 20,  // 阴影水平偏移量
                            },
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        }
                    },
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius:'80%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width:18,
                            itemStyle: {
                                borderWidth: 18,  // 运动轨迹宽度
                                borderColor: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 144, 199, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(29,212,224, 1)'
                                }]),
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 144, 199, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(29,212,224, 1)'
                                }]),  // 已使用的渐变色
                                shadowBlur: 30,  // 阴影模糊大小
                                shadowColor: 'rgba(135, 145, 233, 0.30)',  // 阴影颜色
                                shadowOffsetX: 8,  // 阴影水平偏移量
                                shadowOffsetY: 12,  // 阴影水平偏移量
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, 'rgba(239,247,251, 1)']],  // 运动轨迹颜色
                                width: 20  // 进度条宽度
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 20
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [
                            {
                                value: protected_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return [
                                    '{a|'+info.totalNum+'}\n',  // 第一行数字
                                    '{b|'+ LANG.UI_HOMEPAGEPRO_OS_NUM +'}'  // 第二行文字
                                ].join('');
                            },
                            rich: {
                                a: {
                                    fontSize: 28,
                                    color: '#2D3748'
                                },
                                b: {
                                    fontSize: 14,
                                    color: '#647080'
                                }
                            },
                        },
                    }
                ]
            };
            echarts7.setOption(option);
        }
        //初始化echarts
        initechart7();
    }
    //---------------------------------card_file_1-------------------------------------------------------
    const init_card_file_1 = (info) =>{
        const initechart8 = () =>{
            var chartDom = document.querySelector('[gs-id="card_file_1"] .echarts_file_1');
            echarts8 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_file_1"] .info_size').text(info.backupData.des);
            $('[gs-id="card_file_1"] .info_protectedNum').text(info.protectedNum);
            $('[gs-id="card_file_1"] .info_unProtectedNum').text(parseInt(info.totalNum)-parseInt(info.protectedNum));
            let protected_percent = (parseInt(info.protectedNum) / parseInt(info.totalNum) * 100).toFixed(2);

            var option = {
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',  // 大一圈的白色圆
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                // borderWidth: 20,
                                color: [[1, 'rgba(255, 255, 255, 1)']],  // 白色
                                width: 1000,  // 宽度设为100
                                // shadowBlur: 80,  // 阴影模糊大小
                                // shadowColor: 'rgba(226, 236, 249, 0.50)',  // 阴影颜色
                                // shadowOffsetX: 10,  // 阴影水平偏移量
                                // shadowOffsetY: 20,  // 阴影水平偏移量
                            },
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        }
                    },
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius:'80%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width:18,
                            itemStyle: {
                                borderWidth: 18,  // 运动轨迹宽度
                                borderColor: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(16, 167, 158, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(29, 215, 181, 1)'
                                }]),
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(16, 167, 158, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(29, 215, 181, 1)'
                                }]),  // 已使用的渐变色
                                shadowBlur: 30,  // 阴影模糊大小
                                shadowColor: 'rgba(135, 145, 233, 0.30)',  // 阴影颜色
                                shadowOffsetX: 8,  // 阴影水平偏移量
                                shadowOffsetY: 12,  // 阴影水平偏移量
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, 'rgba(239, 251, 245, 1)']],  // 运动轨迹颜色
                                width: 20  // 进度条宽度
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 20
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [
                            {
                                value: protected_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return [
                                    '{a|'+info.totalNum+'}\n',  // 第一行数字
                                    '{b|'+ LANG.UI_HOMEPAGEPRO_FILE_AGENT_NUM +'}'  // 第二行文字
                                ].join('');
                            },
                            rich: {
                                a: {
                                    fontSize: 28,
                                    color: '#2D3748'
                                },
                                b: {
                                    fontSize: 14,
                                    color: '#647080'
                                }
                            },
                        },
                    }
                ]
            };
            echarts8.setOption(option);
        }
        //初始化echarts
        initechart8();
    }
    //---------------------------------card_db_1-------------------------------------------------------
    const init_card_db_1 = (info) =>{
        const initechart9 = () =>{
            var chartDom = document.querySelector('[gs-id="card_db_1"] .echarts_db_1');
            echarts9 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_db_1"] .info_size').text(info.backupData.des);
            $('[gs-id="card_db_1"] .info_protectedNum').text(info.protectedNum);
            $('[gs-id="card_db_1"] .info_unProtectedNum').text(parseInt(info.totalNum)-parseInt(info.protectedNum));
            let protected_percent = (parseInt(info.protectedNum) / parseInt(info.totalNum) * 100).toFixed(2);


            var option = {
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',  // 大一圈的白色圆
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                // borderWidth: 20,
                                color: [[1, 'rgba(255, 255, 255, 1)']],  // 白色
                                width: 1000,  // 宽度设为100
                                // shadowBlur: 80,  // 阴影模糊大小
                                // shadowColor: 'rgba(226, 236, 249, 0.50)',  // 阴影颜色
                                // shadowOffsetX: 10,  // 阴影水平偏移量
                                // shadowOffsetY: 20,  // 阴影水平偏移量
                            },
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        }
                    },
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius:'80%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width:18,
                            itemStyle: {
                                borderWidth: 18,  // 运动轨迹宽度
                                borderColor: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 63, 199, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(50, 146, 234, 1)'
                                }]),
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 63, 199, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(50, 146, 234, 1)'
                                }]),  // 已使用的渐变色
                                shadowBlur: 30,  // 阴影模糊大小
                                shadowColor: 'rgba(135, 145, 233, 0.30)',  // 阴影颜色
                                shadowOffsetX: 8,  // 阴影水平偏移量
                                shadowOffsetY: 12,  // 阴影水平偏移量
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, 'rgba(239, 244, 251, 1)']],  // 运动轨迹颜色
                                width: 20  // 进度条宽度
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 20
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [
                            {
                                value: protected_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return [
                                    '{a|'+info.totalNum+'}\n',  // 第一行数字
                                    '{b|'+ LANG.UI_HOMEPAGEPRO_DB_AGENT_NUM +'}'  // 第二行文字
                                ].join('');
                            },
                            rich: {
                                a: {
                                    fontSize: 28,
                                    color: '#2D3748'
                                },
                                b: {
                                    fontSize: 14,
                                    color: '#647080'
                                }
                            },
                        },
                    }
                ]
            };
            echarts9.setOption(option);
        }
        //初始化echarts
        initechart9();
    }

    //---------------------------------card_bps_1-------------------------------------------------------
    const init_card_bps_1 = (info) =>{
        const initechart10 = () =>{
            var chartDom = document.querySelector('[gs-id="card_bps_1"] .echarts_bps_1');
            echarts10 = echarts.init(chartDom);
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
                    icon: 'circle',
                    padding: [0, 0, 0, 20] // 设置图例文字左边的间距
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    top:'10%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: info.value_x_data
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
                        name: info.value_y_data[0].name,
                        type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#20C99A',
                                width: 2
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
                                            color: '#02e3a33c'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#20C99A',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[0].data
                    },
                    {
                        name: info.value_y_data[1].name,
                        type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#10AEFF',
                                width: 2
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
                                            color: '#1eb0f94e'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#10AEFF',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[1].data
                    }
                ]
            };
            echarts10.setOption(option);
        }
        //初始化echarts
        initechart10();
    }
    //---------------------------------card_iops_1-------------------------------------------------------
    const init_card_iops_1 = (info) =>{
        const initechart11 = () =>{
            var chartDom = document.querySelector('[gs-id="card_iops_1"] .echarts_iops_1');
            echarts11 = echarts.init(chartDom);



            var option;
            option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    bottom: '5%', // 将图例放在最下面
                    icon: 'circle',
                    padding: [0, 0, 0, 20] // 设置图例文字左边的间距
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    top:'10%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: info.value_x_data,
                },
                yAxis: [
                    {
                        type: 'value',
                        splitLine: {
                            lineStyle: {
                                type: 'dashed' // 这里将刻度线设置为虚线
                            }
                        }
                    }
                ],
                series: [
                    {
                        name: info.value_y_data[0].name,
                        type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#20C99A',
                                width: 2
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
                                            color: '#02e3a33c'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#20C99A',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[0].data
                    },
                    {
                        name: info.value_y_data[1].name,
                        type: 'line',
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#10AEFF',
                                width: 2
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
                                            color: '#1eb0f94e'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#10AEFF',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[1].data
                    }
                ]
            };
            echarts11.setOption(option);
        }
        //初始化echarts
        initechart11();
    }

    //---------------------------------card_task_7days_1-------------------------------------------------------
    const init_card_storage_7days_1 = (info) =>{
        const initechart12 = () =>{
            var chartDom = document.querySelector('[gs-id="card_storage_7days_1"] .echarts_storage_7days_1');
            echarts12 = echarts.init(chartDom);
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
                    bottom: '15%',
                    top:'10%',
                    containLabel: true
                },
                xAxis: [
                    {
                        type: 'category',
                        data: info.value_x_data,
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLine: {
                            show: false // 隐藏 x 轴线条
                        }
                    }
                ],
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
                        name: info.value_y_data.backup_size.name,
                        type: 'bar',
                        barWidth: '15%',
                        itemStyle: {
                            borderRadius: 50 // 设置柱状图的圆角
                        },
                        data: info.value_y_data.backup_size.data,
                    },
                    {
                        name: info.value_y_data.copy_size.name,
                        type: 'bar',
                        barWidth: '15%',
                        itemStyle: {
                            borderRadius: 50 // 设置柱状图的圆角
                        },
                        data: info.value_y_data.copy_size.data,
                    },
                ],

                legend: {
                    bottom: "5%", // 将图例放在最下面
                    icon: 'circle',
                    padding: [0, 0, 0, 20] // 设置图例文字左边的间距
                }
            };
            echarts12.setOption(option);
        }
        //初始化echarts
        initechart12();
    }

    //---------------------------------card_db_2-------------------------------------------------------
    const init_card_db_2 = (info) =>{
        //初始化页面数据
        $('[gs-id="card_db_2"] .info_productHost_online').text(info.productHost.online);
        $('[gs-id="card_db_2"] .info_productHost_offline').text(info.productHost.offline);
        $('[gs-id="card_db_2"] .info_standbyHost_online').text(info.standbyHost.online);
        $('[gs-id="card_db_2"] .info_standbyHost_offline').text(info.standbyHost.offline);
        const initechart13 = () =>{
            var chartDom = document.querySelector('[gs-id="card_db_2"] .echarts_db_2');
            echarts13 = echarts.init(chartDom);
            var option = {
                title: {
                    text: LANG.UI_BACKUP_FILE_PRODUCTHOST,
                    left: 'center',  // 水平居中
                    top: 'center',  // 垂直居中
                    textStyle: {
                        color: '#4C5563',  // 文字颜色
                        fontSize: 14,  // 文字大小
                        align: 'center'  // 文字对齐方式
                    }
                },
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',
                        center: ['50%', '50%'],
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        detail: {
                            color: 'black',
                            show: false
                        },
                        progress: {
                            show: true,
                            overlap: false,
                            roundCap: true,
                            clip: false,
                            itemStyle: {
                                color: '#28B4F0'
                            }
                        },
                        axisLine: {
                            lineStyle: {
                                width: 18
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 10
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [{value:info.productHost.online}],
                    }
                ]
            };
            echarts13.setOption(option);
        }
        const initechart14 = () =>{
            var chartDom = document.querySelector('[gs-id="card_db_2"] .echarts_db_3');
            echarts14 = echarts.init(chartDom);
            var option = {
                title: {
                    text: LANG.UI_BACKUP_FILE_BAKHOST,
                    left: 'center',  // 水平居中
                    top: 'center',  // 垂直居中
                    textStyle: {
                        color: '#4C5563',  // 文字颜色
                        fontSize: 14,  // 文字大小
                        align: 'center'  // 文字对齐方式
                    }
                },
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',
                        center: ['50%', '50%'],
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        detail: {
                            color: 'black',
                            show: false
                        },
                        progress: {
                            show: true,
                            overlap: false,
                            roundCap: true,
                            clip: false,
                            itemStyle: {
                                color: '#22BC7D'
                            }
                        },
                        axisLine: {
                            lineStyle: {
                                width: 18
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 10
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [{value:info.standbyHost.online}],
                    }
                ]
            };
            echarts14.setOption(option);
        }
        //初始化echarts
        initechart13();
        initechart14();
    }
    //---------------------------------card_365_2-------------------------------------------------------
    const init_card_365_2 = (info) =>{

        //初始化页面数据
        $('[gs-id="card_365_2"] .info_online_online').text(info.online.online);
        $('[gs-id="card_365_2"] .info_online_offline').text(info.online.offline);
        $('[gs-id="card_365_2"] .info_server_online').text(info.server.online);
        $('[gs-id="card_365_2"] .info_server_offline').text(info.server.offline);

        const initechart15 = () =>{
            var chartDom = document.querySelector('[gs-id="card_365_2"] .echarts_365_2');
            echarts15 = echarts.init(chartDom);
            var option = {
                title: {
                    text: 'online',
                    left: 'center',  // 水平居中
                    top: 'center',  // 垂直居中
                    textStyle: {
                        color: '#4C5563',  // 文字颜色
                        fontSize: 14,  // 文字大小
                        align: 'center'  // 文字对齐方式
                    }
                },
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',
                        center: ['50%', '50%'],
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        detail: {
                            color: 'black',
                            show: false
                        },
                        progress: {
                            show: true,
                            overlap: false,
                            roundCap: true,
                            clip: false,
                            itemStyle: {
                                color: '#28B4F0'
                            }
                        },
                        axisLine: {
                            lineStyle: {
                                width: 18
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 10
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [{value:info.online.online}],
                    }
                ]
            };
            echarts15.setOption(option);
        }
        const initechart16 = () =>{
            var chartDom = document.querySelector('[gs-id="card_365_2"] .echarts_365_3');
            echarts16 = echarts.init(chartDom);
            var option = {
                title: {
                    text: 'server',
                    left: 'center',  // 水平居中
                    top: 'center',  // 垂直居中
                    textStyle: {
                        color: '#4C5563',  // 文字颜色
                        fontSize: 14,  // 文字大小
                        align: 'center'  // 文字对齐方式
                    }
                },
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',
                        center: ['50%', '50%'],
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        detail: {
                            color: 'black',
                            show: false
                        },
                        progress: {
                            show: true,
                            overlap: false,
                            roundCap: true,
                            clip: false,
                            itemStyle: {
                                color: '#22BC7D'
                            }
                        },
                        axisLine: {
                            lineStyle: {
                                width: 18
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 10
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [{value:info.server.online}],
                    }
                ]
            };
            echarts16.setOption(option);
        }
        //初始化echarts
        initechart15();
        initechart16();
    }

    //---------------------------------card_task_7days_1-------------------------------------------------------
    const init_card_task_7days_1 = (info) =>{
        const initechart17 = () =>{
            var chartDom = document.querySelector('[gs-id="card_task_7days_1"] .echarts_task_7days_1');
            echarts17 = echarts.init(chartDom);
            var option;

            option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    bottom: '5%', // 将图例放在最下面
                    icon: 'circle',
                    padding: [0, 0, 0, 20] // 设置图例文字左边的间距
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    top:'10%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: info.value_x_data
                },
                yAxis: [
                    {
                        type: 'value',
                        splitLine: {
                            lineStyle: {
                                type: 'dashed' // 这里将刻度线设置为虚线
                            }
                        }
                    }
                ],
                series: [
                    {
                        name: info.value_y_data[0].name,
                        type: 'line',
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#20C99A',
                                width: 2
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
                                            color: '#02e3a33c'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#20C99A',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[0].data
                    },
                    {
                        name: info.value_y_data[1].name,
                        type: 'line',
                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#2860F0',
                                width: 2
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
                                            color: '#2860F0'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#2860F0',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[1].data
                    },
                    {
                        name: info.value_y_data[2].name,
                        type: 'line',

                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#10AEFF',
                                width: 2
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
                                            color: '#1eb0f94e'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#10AEFF',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[2].data
                    },
                    {
                        name: info.value_y_data[3].name,
                        type: 'line',

                        symbol: 'circle',
                        symbolSize: 5,
                        showSymbol: false,
                        lineStyle: {
                            normal: {
                                color: '#9972EF',
                                width: 2
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
                                            color: '#1eb0f94e'
                                        },
                                        {
                                            offset: 1,
                                            color: 'rgba(255, 255, 255, 0.3)'
                                        }
                                    ],
                                    false
                                ),
                                shadowColor: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#9972EF',
                                borderColor: 'rgba(221, 220, 107, .1)',
                                borderWidth: 12
                            }
                        },
                        data: info.value_y_data[3].data
                    }
                ]
            };
            echarts17.setOption(option);
        }
        //初始化echarts
        initechart17();
    }

    //---------------------------------card_nas_1-------------------------------------------------------
    const init_card_nas_1 = (info) =>{
        const initechart18 = () =>{
            var chartDom = document.querySelector('[gs-id="card_nas_1"] .echarts_nas_1');
            echarts18 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_nas_1"] .info_size').text(info.backupData.des);
            $('[gs-id="card_nas_1"] .info_protectedNum').text(info.protectedNum);
            $('[gs-id="card_nas_1"] .info_unProtectedNum').text(parseInt(info.totalNum)-parseInt(info.protectedNum));
            let protected_percent = (parseInt(info.protectedNum) / parseInt(info.totalNum) * 100).toFixed(2);

            var option = {
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',  // 大一圈的白色圆
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                // borderWidth: 20,
                                color: [[1, 'rgba(255, 255, 255, 1)']],  // 白色
                                width: 1000,  // 宽度设为100
                                // shadowBlur: 80,  // 阴影模糊大小
                                // shadowColor: 'rgba(226, 236, 249, 0.50)',  // 阴影颜色
                                // shadowOffsetX: 10,  // 阴影水平偏移量
                                // shadowOffsetY: 20,  // 阴影水平偏移量
                            },
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        }
                    },
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius:'80%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width:18,
                            itemStyle: {
                                borderWidth: 18,  // 运动轨迹宽度
                                borderColor: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),  // 已使用的渐变色
                                shadowBlur: 30,  // 阴影模糊大小
                                shadowColor: 'rgba(135, 145, 233, 0.30)',  // 阴影颜色
                                shadowOffsetX: 8,  // 阴影水平偏移量
                                shadowOffsetY: 12,  // 阴影水平偏移量
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, 'rgba(239, 251, 245, 1)']],  // 运动轨迹颜色
                                width: 20  // 进度条宽度
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 20
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [
                            {
                                value: protected_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return [
                                    '{a|'+info.totalNum+'}\n',  // 第一行数字
                                    '{b|'+ LANG.UI_HOMEPAGEPRO_NAS_AGENT_NUM +'}'  // 第二行文字
                                ].join('');
                            },
                            rich: {
                                a: {
                                    fontSize: 28,
                                    color: '#2D3748'
                                },
                                b: {
                                    fontSize: 14,
                                    color: '#647080'
                                }
                            },
                        },
                    }
                ]
            };
            echarts18.setOption(option);
        }
        //初始化echarts
        initechart18();
    }

    //---------------------------------card_volcdp_1-------------------------------------------------------
    const init_card_volcdp_1 = (info) =>{
        const initechart19 = () =>{
            var chartDom = document.querySelector('[gs-id="card_volcdp_1"] .echarts_volcdp_1');
            echarts19 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_volcdp_1"] .info_size').text(info.backupData.des);
            $('[gs-id="card_volcdp_1"] .info_protectedNum').text(info.protectedNum);
            $('[gs-id="card_volcdp_1"] .info_unProtectedNum').text(parseInt(info.totalNum)-parseInt(info.protectedNum));
            let protected_percent = (parseInt(info.protectedNum) / parseInt(info.totalNum) * 100).toFixed(2);

            var option = {
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',  // 大一圈的白色圆
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                // borderWidth: 20,
                                color: [[1, 'rgba(255, 255, 255, 1)']],  // 白色
                                width: 1000,  // 宽度设为100
                                // shadowBlur: 80,  // 阴影模糊大小
                                // shadowColor: 'rgba(226, 236, 249, 0.50)',  // 阴影颜色
                                // shadowOffsetX: 10,  // 阴影水平偏移量
                                // shadowOffsetY: 20,  // 阴影水平偏移量
                            },
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        }
                    },
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius:'80%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width:18,
                            itemStyle: {
                                borderWidth: 18,  // 运动轨迹宽度
                                borderColor: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),  // 已使用的渐变色
                                shadowBlur: 30,  // 阴影模糊大小
                                shadowColor: 'rgba(135, 145, 233, 0.30)',  // 阴影颜色
                                shadowOffsetX: 8,  // 阴影水平偏移量
                                shadowOffsetY: 12,  // 阴影水平偏移量
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, 'rgba(239, 251, 245, 1)']],  // 运动轨迹颜色
                                width: 20  // 进度条宽度
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 20
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [
                            {
                                value: protected_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return [
                                    '{a|'+info.totalNum+'}\n',  // 第一行数字
                                    '{b|'+ LANG.UI_HOMEPAGEPRO_CDP_AGENT_NUM+'}'  // 第二行文字
                                ].join('');
                            },
                            rich: {
                                a: {
                                    fontSize: 28,
                                    color: '#2D3748'
                                },
                                b: {
                                    fontSize: 14,
                                    color: '#647080'
                                }
                            },
                        },
                    }
                ]
            };
            echarts19.setOption(option);
        }
        //初始化echarts
        initechart19();
    }

    //---------------------------------card_aws_1-------------------------------------------------------
    const init_card_aws_1 = (info) =>{
        const initechart20 = () =>{
            var chartDom = document.querySelector('[gs-id="card_aws_1"] .echarts_aws_1');
            echarts20 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_aws_1"] .info_size').text(info.backupData.des);
            $('[gs-id="card_aws_1"] .info_protectedNum').text(info.protectedNum);
            $('[gs-id="card_aws_1"] .info_unProtectedNum').text(parseInt(info.totalNum)-parseInt(info.protectedNum));
            let protected_percent = (parseInt(info.protectedNum) / parseInt(info.totalNum) * 100).toFixed(2);

            var option = {
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',  // 大一圈的白色圆
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                // borderWidth: 20,
                                color: [[1, 'rgba(255, 255, 255, 1)']],  // 白色
                                width: 1000,  // 宽度设为100
                                // shadowBlur: 80,  // 阴影模糊大小
                                // shadowColor: 'rgba(226, 236, 249, 0.50)',  // 阴影颜色
                                // shadowOffsetX: 10,  // 阴影水平偏移量
                                // shadowOffsetY: 20,  // 阴影水平偏移量
                            },
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        }
                    },
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius:'80%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width:18,
                            itemStyle: {
                                borderWidth: 18,  // 运动轨迹宽度
                                borderColor: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),  // 已使用的渐变色
                                shadowBlur: 30,  // 阴影模糊大小
                                shadowColor: 'rgba(135, 145, 233, 0.30)',  // 阴影颜色
                                shadowOffsetX: 8,  // 阴影水平偏移量
                                shadowOffsetY: 12,  // 阴影水平偏移量
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, 'rgba(239, 251, 245, 1)']],  // 运动轨迹颜色
                                width: 20  // 进度条宽度
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 20
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [
                            {
                                value: protected_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return [
                                    '{a|'+info.totalNum+'}\n',  // 第一行数字
                                    '{b|'+LANG.UI_HOMEPAGE_AWS_HOST_TOTAL+'}'  // 第二行文字
                                ].join('');
                            },
                            rich: {
                                a: {
                                    fontSize: 28,
                                    color: '#2D3748'
                                },
                                b: {
                                    fontSize: 14,
                                    color: '#647080'
                                }
                            },
                        },
                    }
                ]
            };
            echarts20.setOption(option);
        }
        //初始化echarts
        initechart20();
    }
    //---------------------------------card_hadoop_1------------------------------------------------------
    const init_card_hadoop_1 = (info) => {
        const initechart21 = () =>{
            var chartDom = document.querySelector('[gs-id="card_hadoop_1"] .echarts_hadoop_1');
            echarts21 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_hadoop_1"] .info_size').text(info.backupData.des);
            $('[gs-id="card_hadoop_1"] .info_protectedNum').text(info.protectedNum);
            $('[gs-id="card_hadoop_1"] .info_unProtectedNum').text(parseInt(info.totalNum)-parseInt(info.protectedNum));
            let protected_percent = (parseInt(info.protectedNum) / parseInt(info.totalNum) * 100).toFixed(2);
            var option = {
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',  // 大一圈的白色圆
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                // borderWidth: 20,
                                color: [[1, 'rgba(255, 255, 255, 1)']],  // 白色
                                width: 1000,  // 宽度设为100
                                // shadowBlur: 80,  // 阴影模糊大小
                                // shadowColor: 'rgba(226, 236, 249, 0.50)',  // 阴影颜色
                                // shadowOffsetX: 10,  // 阴影水平偏移量
                                // shadowOffsetY: 20,  // 阴影水平偏移量
                            },
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        }
                    },
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius:'80%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width:18,
                            itemStyle: {
                                borderWidth: 18,  // 运动轨迹宽度
                                borderColor: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),  // 已使用的渐变色
                                shadowBlur: 30,  // 阴影模糊大小
                                shadowColor: 'rgba(135, 145, 233, 0.30)',  // 阴影颜色
                                shadowOffsetX: 8,  // 阴影水平偏移量
                                shadowOffsetY: 12,  // 阴影水平偏移量
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, 'rgba(239, 251, 245, 1)']],  // 运动轨迹颜色
                                width: 20  // 进度条宽度
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 20
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [
                            {
                                value: protected_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return [
                                    '{a|'+info.totalNum+'}\n',  // 第一行数字
                                    '{b|'+LANG.UI_HOMEPAGE_HADOOP_CLUSTER_TOTAL+'}'  // 第二行文字
                                ].join('');
                            },
                            rich: {
                                a: {
                                    fontSize: 28,
                                    color: '#2D3748'
                                },
                                b: {
                                    fontSize: 14,
                                    color: '#647080'
                                }
                            },
                        },
                    }
                ]
            };
            echarts21.setOption(option);
        }
        //初始化echarts
        initechart21();
    }
    //---------------------------------card_obs_1------------------------------------------------------
    const init_card_obs_1 = (info) => {
        const initechart22 = () =>{
            var chartDom = document.querySelector('[gs-id="card_obs_1"] .echarts_obs_1');
            echarts22 = echarts.init(chartDom);

            //初始化页面数据
            $('[gs-id="card_obs_1"] .info_size').text(info.backupData.des);
            $('[gs-id="card_obs_1"] .info_protectedNum').text(info.protectedNum);
            $('[gs-id="card_obs_1"] .info_unProtectedNum').text(parseInt(info.totalNum)-parseInt(info.protectedNum));
            let protected_percent = (parseInt(info.protectedNum) / parseInt(info.totalNum) * 100).toFixed(2);
            var option = {
                series: [
                    {
                        type: 'gauge',
                        radius: '90%',  // 大一圈的白色圆
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        axisLine: {
                            lineStyle: {
                                // borderWidth: 20,
                                color: [[1, 'rgba(255, 255, 255, 1)']],  // 白色
                                width: 1000,  // 宽度设为100
                                // shadowBlur: 80,  // 阴影模糊大小
                                // shadowColor: 'rgba(226, 236, 249, 0.50)',  // 阴影颜色
                                // shadowOffsetX: 10,  // 阴影水平偏移量
                                // shadowOffsetY: 20,  // 阴影水平偏移量
                            },
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        detail: {
                            show: false
                        }
                    },
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius:'80%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width:18,
                            itemStyle: {
                                borderWidth: 18,  // 运动轨迹宽度
                                borderColor: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgba(48, 192, 145, 1)'
                                }, {
                                    offset: 1,
                                    color: 'rgba(49, 224, 140, 1)'
                                }]),  // 已使用的渐变色
                                shadowBlur: 30,  // 阴影模糊大小
                                shadowColor: 'rgba(135, 145, 233, 0.30)',  // 阴影颜色
                                shadowOffsetX: 8,  // 阴影水平偏移量
                                shadowOffsetY: 12,  // 阴影水平偏移量
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, 'rgba(239, 251, 245, 1)']],  // 运动轨迹颜色
                                width: 20  // 进度条宽度
                            }
                        },
                        splitLine: {
                            show: false,
                            distance: 0,
                            length: 20
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false,
                            distance: 50
                        },
                        data: [
                            {
                                value: protected_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return [
                                    '{a|'+info.totalNum+'}\n',  // 第一行数字
                                    '{b|'+LANG.UI_HOMEPAGE_OBS_TOTAL+'}'  // 第二行文字
                                ].join('');
                            },
                            rich: {
                                a: {
                                    fontSize: 28,
                                    color: '#2D3748'
                                },
                                b: {
                                    fontSize: 14,
                                    color: '#647080'
                                }
                            },
                        },
                    }
                ]
            };
            echarts22.setOption(option);
        }
        //初始化echarts
        initechart22();
    }

    //------------------------------card_storage_1---------------------------------------------------



    const init_card_storage_1 = (info) => {
        const initechart23 = () => {
            var chartDom = document.querySelector('[gs-id="card_storage_1"] .echarts_storagel_1');
            echarts23 = echarts.init(chartDom);
            // 初始化页面数据
            $('[gs-id="card_storage_1"] .info_storage_num').text(info.value_storage_num);
            $('[gs-id="card_storage_1"] .info_storage_size_all').text(info.value_storage_size_free + info.value_storage_size_free_unit);
    
            var option;
            option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    },
                    formatter: function(params) {
                        var des = params[0].name + "<br/>";
                        for (var i = 0; i < params.length; i++) {
                            let dataVal = Number(params[i].value) ? params[i].value : 0;
                            let unit = "B";
                            if (dataVal >= 1024 * 1024 * 1024 * 1024) {
                                dataVal /= 1024 * 1024 * 1024 * 1024;
                                unit = "TB";
                            } else if (dataVal >= 1024 * 1024 * 1024) {
                                dataVal /= 1024 * 1024 * 1024;
                                unit = "GB";
                            } else if (dataVal >= 1024 * 1024) {
                                dataVal /= 1024 * 1024;
                                unit = "MB";
                            } else if (dataVal >= 1024) {
                                dataVal /= 1024;
                                unit = "KB";
                            }
                            // 添加图例色块和加粗数值显示
                            des += '<span style="display:inline-block;margin-right:5px;margin-bottom:2px;width:16px;height:4px;background-color:#2DCC7F"></span><span style="color: #797F8A;font-size: 14px;font-weight: 400;margin-right:22px;">' + LANG.UI_REPORT_STORAGE_USAGE + "</span><b style='font-weight: 500;font-size: 14px;color: #4C5563;'>" + dataVal.toFixed(2) + " " + unit + "</b><br/>";
                        }
                        return des;
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    top: '10%',
                    containLabel: true
                },
                xAxis: [{
                    type: 'category',
                    data: info.value_7_days_storage_size.value_x_data,
                    axisTick: {
                        alignWithLabel: true
                    },
                    axisLine: {
                        show: false // 隐藏 x 轴线条
                    },
                    axisLabel: {
                        color: '#B5B5C3' // 设置x轴文字颜色
                    }
                }],
                yAxis: [{
                    type: 'value',
                    splitLine: {
                        lineStyle: {
                            type: 'dashed' // 这里将刻度线设置为虚线
                        }
                    },
                    axisLabel: {
                        formatter: function(value, index) {
                            value = Number(value);
                            let unit = "B";
                            if (value >= 1024 * 1024 * 1024 * 1024) {
                                value /= 1024 * 1024 * 1024 * 1024;
                                unit = "TB";
                            } else if (value >= 1024 * 1024 * 1024) {
                                value /= 1024 * 1024 * 1024;
                                unit = "GB";
                            } else if (value >= 1024 * 1024) {
                                value /= 1024 * 1024;
                                unit = "MB";
                            } else if (value >= 1024) {
                                value /= 1024;
                                unit = "KB";
                            }
                            value = value.toFixed(2);
                            return value + unit;
                        },
                        color: '#B5B5C3' // 设置y轴文字颜色
                    }
                }],
                series: [{
                    name: '{a|'+LANG.UI_HOMEPAGE_EVERY_DAY+'}',
                    type: 'bar',
                    barWidth: 16, // 设置柱状图宽度为14px
                    itemStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0,
                                color: 'rgba(45, 204, 127, 0.5)' // 顶部：#2DCC7F，透明度0.5
                            },
                            {
                                offset: 1,
                                color: 'rgba(45, 204, 127, 1)' // 底部：#2DCC7F，透明度1
                            }
                        ]),
                    },
                    data: info.value_7_days_storage_size.value_y_data.all_size.data,
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 0
                        }
                    },
                    markPoint: {
                        symbol: 'rect', // 使用矩形标记
                        symbolSize: [20, 4], // 设置标记宽度为16px，高度为2px
                        symbolOffset: [0, -1], // 向上偏移1px，使其位于柱状图顶部
                        itemStyle: {
                            color: '#2DCC7F' // 标记
                        },
                        emphasis: {
                            disabled: true // 禁用鼠标悬停效果
                        },
                        data: info.value_7_days_storage_size.value_y_data.all_size.data.map((value, index) => ({
                            xAxis: index, // 对应柱子的横坐标
                            yAxis: value // 对应柱子的值
                        }))
                    }
                }],
                legend: {
                    bottom: "5%", // 将图例放在最下面
                    icon: 'rect', // 改为矩形图标
                    itemWidth: 16, // 设置图例宽度为16px
                    itemHeight: 4, // 设置图例高度为2px
                    padding: [0, 0, 0, 20], // 设置图例文字左边的间距
                    textStyle: {
                        rich: {
                            a: {
                                color: '#404040',
                            }
                        }
                    }
                }
            };
            echarts23.setOption(option);
        }
        // 初始化echarts
        initechart23();
    }

    //------------------------------card_all_module_backup_data_1----------------------------------
    //公共处理函数
            var fillMissingDates = function (data,num = 7) {
            // 获取当前日期
            const today = new Date();
            num = num - 1;
            // 获取最近七天的日期字符串数组（格式为MM-DD）
            const lastSevenDays = [];
            for (let i = num; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(today.getDate() - i);
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                lastSevenDays.push(`${month}-${day}`);
            }
         
            // 创建一个映射，用于快速查找日期对应的size
            const dateToSize = {};
            data.forEach(item => {
                dateToSize[item.date] = item.size;
            });
         
            // 初始化结果数组，全部设为0
            const result = new Array(7).fill(0);
         
            // 填充存在的size
            lastSevenDays.forEach((date, index) => {
                if (dateToSize[date] !== undefined) {
                    result[index] = parseInt(dateToSize[date]);
                }
            });
         
            return result;
        }

        const findMaxSumData = function (rawData,num) {
            // 每一列的和
            const columnSums = new Array(num).fill(0);
            // 累加到相应的列
            rawData.forEach(arr => {
                for (let i = 0; i < num; i++) {
                    columnSums[i] += arr[i];
                }
            });
            // 找到列和的最大值
            let maxSum = Math.max(...columnSums);
            maxSum = maxSum != 0 ? maxSum : 1;
            return maxSum;
        }
        const fillDataDes = function (fixedDate,data) { 
            for (const module in data) {
                const moduleData = data[module];
                // // 跳过空数组
                // if (Array.isArray(moduleData) && moduleData.length === 0) continue;
                let divClassName = module + '_des';
                let $divs = $(`.${divClassName}`);
                //获取当前的top-title
                // 如果没有找到对应的div，跳过
                if ($divs.length === 0) continue;
                $divs.each(function() {
                    const $div = $(this);
                    // 在模块数据中查找匹配日期的项
                    const dataItem = moduleData.find(item => item.date === fixedDate);
                    if (dataItem) {
                        // 找到匹配项，设置des内容
                        $div.text(dataItem.des);
                    } else {
                        // 未找到匹配项，设置为0B
                        $div.text('0B');
                    }
                });
            }
        }
        var getLastSevenDaysPure = function (num) {
            const dates = [];
            const today = new Date();
            for (let i = 0; i < num; i++) {
              const date = new Date(today);
              date.setDate(today.getDate() - i);
              const month = String(date.getMonth() + 1).padStart(2, '0');
              const day = String(date.getDate()).padStart(2, '0');
              dates.unshift(`${month}-${day}`);
            }
            return dates;
        }
        // 辅助函数将十六进制颜色转换为RGBA
        var hexToRgba = function(hex, alpha) {
            // 移除 # 符号
            hex = hex.replace('#', '');
            
            // 解析 RGB 值
            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
    
    
    const init_card_all_module_backup_data_1 = (info) => {
        //统一处理权限显示
        var permission_show = function(info){
            //判断是否含有云
            if(!info.protect_num.cloud.show){
                $(".cloud_show").css("display", "none")
            }
            //是否有文件
            if(!info.protect_num.file.show){
                $(".file_show").css("display", "none")
            }
            //是否有云和文件
            if(!info.protect_num.file.show && !info.protect_num.cloud.show){
                $(".cloud_file_show").css("display", "none")
            }
            //是否有整机
            if(!info.protect_num.machine.show){
                $(".machine_show").css("display", "none")
            }
            //是否有应用
            if(!info.protect_num.application.show){
                $(".application_show").css("display", "none")
            }
            //是否有整机和应用
            if(!info.protect_num.machine.show && !info.protect_num.application.show){
                $(".machine_application_show").css("display", "none")
            }
            //云下面 
            if(!info.protect_num.cloud.module.vmprotect.show){
                //找到第1个虚拟化
                $(".cloud_show").find(".flex_row_box_each").eq(0).css("display", "none")
            }
            if(!info.protect_num.cloud.module.prcloud_protect.show){
                //找到第2个私有云
                $(".cloud_show").find(".flex_row_box_each").eq(1).css("display", "none")
            }
            if(!info.protect_num.cloud.module.awsprotect.show){
                //找到第3个公有云
                $(".cloud_show").find(".flex_row_box_each").eq(2).css("display", "none")
            }
            if(!info.protect_num.cloud.module.k8s_protect.show){
                //找到第4个容器
                $(".cloud_show").find(".flex_row_box_each").eq(3).css("display", "none")
            }

            //文件下面
            if(!info.protect_num.file.module.filebackup.show){
                //找到第1个文件
                $(".file_show").find(".flex_row_box_each").eq(0).css("display", "none")
            }
            if(!info.protect_num.file.module.nas_protect.show){
                //找到第2个nas
                $(".file_show").find(".flex_row_box_each").eq(1).css("display", "none")
            }
            if(!info.protect_num.file.module.obs_protect.show){
                //找到第3个对象存储
                $(".file_show").find(".flex_row_box_each").eq(2).css("display", "none")
            }
            if(!info.protect_num.file.module.hadoop_protect.show){
                //找到第4个hadoop
                $(".file_show").find(".flex_row_box_each").eq(3).css("display", "none")
            }

            //整机下面
            if(!info.protect_num.machine.module.complete_machine.show){
                //找到第1个整机
                $(".machine_show").find(".flex_row_box_each").eq(0).css("display", "none")
            }
            if(!info.protect_num.machine.module.osbackup.show){
                //找到第2个卷
                $(".machine_show").find(".flex_row_box_each").eq(1).css("display", "none")
            }
            //应用下面
            if(!info.protect_num.application.module.db_protect.show){
                //找到第1个数据库
                $(".application_show").find(".flex_row_box_each").eq(0).css("display", "none")
            }
            if(!info.protect_num.application.module.office365_protect.show){
                //找到第1个数据库
                $(".application_show").find(".flex_row_box_each").eq(1).css("display", "none")
            }
        };
        permission_show(info);
        
        const initechart24 = (info) => {
            var chartDom = document.querySelector('[gs-id="card_all_module_backup_data_1"] .echarts_protect_circle');
            echarts24 = echarts.init(chartDom);
            // 初始化页面数据
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_vm_num').text(info.protect_num.cloud.module.vmprotect.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_cloud_si_num').text(info.protect_num.cloud.module.prcloud_protect.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_cloud_go_num').text(info.protect_num.cloud.module.awsprotect.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_k8s_num').text(info.protect_num.cloud.module.k8s_protect.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_fs_num').text(info.protect_num.file.module.filebackup.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_nas_num').text(info.protect_num.file.module.nas_protect.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_obs_num').text(info.protect_num.file.module.obs_protect.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_hadoop_num').text(info.protect_num.file.module.hadoop_protect.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_machine_num').text(info.protect_num.machine.module.complete_machine.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_os_num').text(info.protect_num.machine.module.osbackup.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_db_num').text(info.protect_num.application.module.db_protect.protected);
            $('[gs-id="card_all_module_backup_data_1"] .protect_module_m365_num').text(info.protect_num.application.module.office365_protect.protected);

            let totalMouduleProtectedNum = {};
            const topModules = ["cloud", "file", "machine", "application"];
            topModules.forEach(module => {
                let totalProtected = 0;
                const modules = info.protect_num[module].module;
                // 遍历子模块
                for (const subModule in modules) {
                    if (modules.hasOwnProperty(subModule)) {
                        totalProtected += modules[subModule].protected;
                    }
                }
                totalMouduleProtectedNum[module] = totalProtected;
            });
            let totalNum = (info.protect_num.cloud.show ? totalMouduleProtectedNum.cloud : 0) +
            (info.protect_num.file.show ? totalMouduleProtectedNum.file : 0) +
            (info.protect_num.application.show ? totalMouduleProtectedNum.application : 0) +
            (info.protect_num.machine.show? totalMouduleProtectedNum.machine : 0);
            totalNum = parseInt(totalNum);

            var option;
            option = {
                tooltip: {
                    show: false,
                },
                legend: {
                    show: false,
                },
                color:["#2DCC7F", "#1DD4E0", "#30CBCB", "#4C94FF"],
                series: [
                  {
                    // 背景圆 - 红色大圆
                    type: 'pie',
                    radius: ['0%', '90%'], // 调整半径范围，确保背景圆更大
                    hoverAnimation: false,
                    itemStyle: {
                        color: '#FFFFFF', // 背景圆颜色
                        borderColor: 'transparent',
                        borderWidth: 0,
                        shadowBlur: 30,        // 对应box-shadow的blur值(60px)
                        shadowColor: 'rgba(226, 236, 249, 1)', // 对应box-shadow的颜色
                        // shadowOffsetX: 5,      // 对应box-shadow的水平偏移(0px)
                        // shadowOffsetY: 5      // 对应box-shadow的垂直偏移(10px)
                    },
                    // label: {
                    //   show: false
                    // },
                    labelLine: {
                      show: false
                    },
                    data: [
                      { value: 1, name: '' }
                    ],
                    z: 0 // 确保在底层
                  },
                  {
                    name: LANG.UI_HOMEPAGE_PROTECTED_DEVICE,
                    type: 'pie',
                    radius: ['59%', '75%'], // 调整主饼图半径，使其在背景圆内部
                    avoidLabelOverlap: false,
                    hoverAnimation: false,
                    itemStyle: {
                      borderRadius: 0,
                      borderColor: '#fff',
                      borderWidth: 2.3
                    },
                    label: {
                        show: true,
                        position: 'center',
                        formatter: function (params) {
                            return ['{a|' + totalNum + '}', '{b|'+ LANG.UI_HOMEPAGE_PROTECTED_DEVICE +'}'].join('\n');
                        },
                        rich: {
                            a: {
                                color: '#2D3748',
                                fontSize: 28,
                                lineHeight: 33
                            },
                            b: {
                                color: '#647080',
                                fontSize: 14,
                                lineHeight: 20
                            }
                        }
                    },
                    labelLine: {
                      show: false
                    },
                    data: [
                        ...(info.protect_num.file.show ? [{ value: totalMouduleProtectedNum.file, name: LANG.UI_FILE_FILE}] : []),
                        ...(info.protect_num.cloud.show ? [{ value: totalMouduleProtectedNum.cloud, name: LANG.UI_HOMEPAGE_CLOUD}] : []),
                        ...(info.protect_num.machine.show ? [{ value: totalMouduleProtectedNum.machine, name: LANG.UI_BACKUP_DATA_MODULE_OS}] : []),
                        ...(info.protect_num.application.show ? [{ value: totalMouduleProtectedNum.application, name: LANG.UI_KUBE_APPLICATION}] : []),
                    ],
                    z: 1 // 确保在上层
                  }
                ]
              };
            echarts24.setOption(option);
        }
        // 初始化echarts
        initechart24(info);
        //----

        const initechart25 = (info) => {
            var chartDom = document.querySelector('[gs-id="card_all_module_backup_data_1"] .echarts_protect_line');
            echarts25 = echarts.init(chartDom);
            // 初始化页面数据
            //四个大模块七天的数据
            const cloud = fillMissingDates(info.protect_capacity.cloud);
            const file = fillMissingDates(info.protect_capacity.file);
            const machine = fillMissingDates(info.protect_capacity.machine);
            const application = fillMissingDates(info.protect_capacity.application);
            let rawData = [//y轴数据
                cloud, //云
                file, //文件
                machine, //整机
                application, //应用
            ];
            let dateNum = 7;//x轴显示几天的数据
            const windowWidth = window.innerWidth;
            if (windowWidth > 1365 && windowWidth < 1600) {
                rawData = rawData.map(arr => arr.slice(-4));
                dateNum = 4;
            }
            let maxNum = findMaxSumData(rawData,dateNum);
            let interval = maxNum / 5;
            const totalData = [];
            for (let i = 0; i < rawData[0].length; ++i) {
                let sum = 0;
                for (let j = 0; j < rawData.length; ++j) {
                    sum += rawData[j][i];
                }
                totalData.push(sum);
            }
            //处理rawData里面的几个数据是否都有
            let rawDataHasData = [];
            let colors = [];
            let titles = [];
            if(info.protect_num.cloud.show){
                rawDataHasData.push(rawData[0]);
                colors.push("#2DCC7F");
                titles.push(LANG.UI_HOMEPAGE_CLOUD);
            }
            if(info.protect_num.file.show){
                rawDataHasData.push(rawData[1]);
                colors.push("#1DD4E0");
                titles.push(LANG.UI_FILE_FILE);
            }
            if(info.protect_num.machine.show){
                rawDataHasData.push(rawData[2]);
                colors.push("#30CBCB");
                titles.push(LANG.UI_BACKUP_DATA_MODULE_OS);
            }
            if(info.protect_num.application.show){
                rawDataHasData.push(rawData[3]);
                colors.push("#4C94FF");
                titles.push(LANG.UI_KUBE_APPLICATION);
            }
            const series =titles.map((name, sid) => {
                return {
                    name,
                    type: 'line',
                    symbol: 'circle',
                    symbolSize: 6,
                    showSymbol: true,
                    lineStyle: {
                        width: 2,
                        color: colors[sid]
                    },
                    itemStyle: {
                        color: colors[sid],
                        borderWidth: 2
                    },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {
                                offset: 0,
                                color: hexToRgba(colors[sid], 0.1)
                            },
                            {
                                offset: 1,
                                color: 'rgba(255, 255, 255,0.1)'
                            }
                        ])
                    },
                    data: rawDataHasData[sid].map((d, did) =>
                        totalData[did] <= 0 ? 0 : d 
                      )
                };
            });
            var option;
            option = {
                title: {
                    text: LANG.UI_HOMEPAGE_PROTECT_DATA, 
                    left: 'left',
                    top: 'top',
                    textStyle: {
                        color: '#3D3D46',
                        fontSize: 14, 
                        fontWeight: '400'
                    },
                    padding: 0 
                },
                color: ["#20C99A"],
                // backgroundColor: 'pink',
                tooltip: {
                    trigger: "axis",
                    axisPointer: {
                        type: "shadow"
                    },
                    appendToBody: true,
                    zlevel: 1000,
                    z: 1000,
                    className: 'data-bar-tooltip display-none', // 自定义类名
                    formatter: function (a) {
                        fillDataDes(a[0].name,info.protect_capacity);
                        return $('.back-bar-tooltip-content').html();
                    }
                },
                legend: {
                    icon: "square",
                    show:true,
                    right:0,
                    top: 20,
                    itemWidth: 10,
                    itemHeight:10,
                    itemGap: 16, // 设置图例间距
                    textStyle: {
                        color: "#333333",
                        fontSize: 12,
                        rich: {
                            a: {
                                verticalAlign: 'middle',
                            },
                        },
                        padding:[0,-2,-2,1],
                    },
                },
                grid: {
                    left: "5",
                    top: "55",
                    right: "0",
                    bottom: "0",
                    containLabel: true,
                },
                xAxis: [
                    {
                        type: "category",
                        data: getLastSevenDaysPure(dateNum),
                        z: 10,
                        axisLabel: {
                            textStyle: {
                                color: "#B5B5C3",
                                fontSize: "12"
                            },
                            interval: 0,
                        },
                        axisLine: {
                            show: true,
                            lineStyle: {
                                color: "#F1F1F5" ,
                                width: 1
                            }
                        },
                        axisTick: {
                            show: false
                        },
                    }
                ],
                yAxis: [
                    {
                        type: "value",
                        min: 0,
                        max: maxNum,
                        interval: interval,
                        axisLabel: {
                            textStyle: {
                                color: "#B5B5C3",
                                fontSize: "12"
                            },
                            formatter: function(value, index){
                                if(value >= 1024 && value <= 1024*1024){
                                    return Math.round(value * 10 / 1024) / 10 + "KB";
                                }else if(value >= 1024*1024 && value <= 1024*1024*1024){
                                    return Math.round(value * 10 / (1024*1024)) / 10 + "MB";
                                }else if(value > 1024 *1024*1024 && value <= 1024 * 1024 * 1024*1024){
                                    return Math.round(value * 10 /(1024 * 1024 * 1024) ) / 10 + "GB";
                                }else if(value > 1024*1024*1024*1024){
                                    return Math.round(value * 10 /(1024 *1024 * 1024 * 1024) ) / 10 + "TB";
                                }
                                else{
                                    return value + "B";
                                }
                            }
                        },
                        axisLine: {
                            lineStyle: {
                                show:false
                            }
                        },
                        splitLine: {
                            lineStyle: {
                                color: "#F1F1F5",
                                type: 'dashed',
                            }
                        },
                    }
                ],
                series
            };
            echarts25.setOption(option);
        }
        // 初始化echarts
        initechart25(info);
    
    }

    //--------------------------------card_vol_cdp_protect_1-------------------------------------------
    const init_card_vol_cdp_protect_1 = (info) => {
        //初始化显示
        var permission_show = function(info){
            //是否有实时
            if(!info.protect_num.vol_cdp_protect.show){
                $('[gs-id="card_vol_cdp_protect_1"]').css("display", "none")
            }
            //是否有整机
            if(!info.protect_num.vol_cdp_protect.module.complete_cdp_backup.show){
                //找到第1个整机
                $(".cdp_show_1").css("display", "none")
            }
            if(!info.protect_num.vol_cdp_protect.module.vol_cdp_backup.show){
                //找到第2个卷
                $(".cdp_show_2").css("display", "none")
            }
        }
        permission_show(info)




        const initechart26 = () => {
            var chartDom = document.querySelector('[gs-id="card_vol_cdp_protect_1"] .echarts_vol_cdp_machine_1');
            echarts26 = echarts.init(chartDom);
           
            //整机未保护的为
            let vol_cdp_noProtect_num = parseInt(info.protect_num.vol_cdp_protect.module.complete_cdp_backup.total) - parseInt(info.protect_num.vol_cdp_protect.module.complete_cdp_backup.protected);
            //卷未保护的为
            let vol_cdp_os_noProtect_data = parseInt(info.protect_num.vol_cdp_protect.module.vol_cdp_backup.total) - parseInt(info.protect_num.vol_cdp_protect.module.vol_cdp_backup.protected);
            // 初始化页面数据
            $('[gs-id="card_vol_cdp_protect_1"] .protect_module_vol_cdp_machine_num_protect').text(info.protect_num.vol_cdp_protect.module.complete_cdp_backup.protected);
            $('[gs-id="card_vol_cdp_protect_1"] .protect_module_vol_cdp_machine_num_noProetect').text(vol_cdp_noProtect_num);
            $('[gs-id="card_vol_cdp_protect_1"] .protect_module_vol_cdp_os_num_protect').text(info.protect_num.vol_cdp_protect.module.vol_cdp_backup.protected);
            $('[gs-id="card_vol_cdp_protect_1"] .protect_module_vol_cdp_os_num_noProetect').text(vol_cdp_os_noProtect_data);

            //整机和卷百分比
            let complete_cdp_backup_percent = Math.round(info.protect_num.vol_cdp_protect.module.complete_cdp_backup.protected / info.protect_num.vol_cdp_protect.module.complete_cdp_backup.total);
            

            var option = {
                series: [
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius: '90%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width: 8,
                            // 关键：roundCap 属性实现圆角效果
                            roundCap: true,
                            itemStyle: {
                                color: {
                                    type: 'linear',
                                    x: 0,
                                    y: 0,
                                    x2: 0,
                                    y2: 1,
                                    colorStops: [{
                                        offset: 1,
                                        color: '#2DCC7F'
                                    }]
                                }
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, '#F0F2F4']],  // 背景轨道颜色
                                width: 8
                            }
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        data: [
                            {
                                value: complete_cdp_backup_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return '{b|'+LANG.UI_BACKUP_DATA_MODULE_OS+'}';
                            },
                            rich: {
                                b: {
                                    fontSize: 14,
                                    color: '#4C5563',
                                    lineHeight: 20
                                }
                            },
                        },
                    }
                ]
            };
            echarts26.setOption(option);
        }
        

        const initechart27 = () => {
            var chartDom = document.querySelector('[gs-id="card_vol_cdp_protect_1"] .echarts_vol_cdp_volume_1');
            echarts27 = echarts.init(chartDom);
            let vol_cdp_backup_percent = Math.round(info.protect_num.vol_cdp_protect.module.vol_cdp_backup.protected / info.protect_num.vol_cdp_protect.module.vol_cdp_backup.total);
            // 初始化页面数据
            var option = {
                series: [
                    {
                        type: 'gauge',
                        startAngle: 90,
                        radius: '90%',
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            width: 8,
                            // 关键：roundCap 属性实现圆角效果
                            roundCap: true,
                            itemStyle: {
                                color: {
                                    type: 'linear',
                                    x: 0,
                                    y: 0,
                                    x2: 0,
                                    y2: 1,
                                    colorStops: [{
                                        offset: 1,
                                        color: '#1DD4E0'
                                    }]
                                }
                            },
                        },
                        axisLine: {
                            lineStyle: {
                                color: [[1, '#F0F2F4']],  // 背景轨道颜色
                                width: 8
                            }
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        data: [
                            {
                                value: vol_cdp_backup_percent,
                            },
                        ],
                        detail: {
                            show: true,
                            offsetCenter: [0, 0],  // 居中显示
                            formatter: function() {
                                return '{b|'+LANG.UI_COPY_MODULE_LABEL_REEL_OS+'}';
                            },
                            rich: {
                                b: {
                                    fontSize: 14,
                                    color: '#4C5563',
                                    lineHeight: 20
                                }
                            },
                        },
                    }
                ]
            };
            echarts27.setOption(option);
        }

        const initechart28 = (info) => {
            var chartDom = document.querySelector('[gs-id="card_vol_cdp_protect_1"] .echarts_vol_cdp_1');
            echarts28 = echarts.init(chartDom);
            // 初始化页面数据
            var option;
            const complete_cdp_backup_data = fillMissingDates(info.protect_capacity.complete_cdp_backup_data, 7);
            const vol_cdp_backup_data = fillMissingDates(info.protect_capacity.vol_cdp_backup_data, 7);
            let rawData = [
                complete_cdp_backup_data, //整机实时
                vol_cdp_backup_data, //卷实时
            ];
            let dateNum = 7;//x轴显示几天的数据
            const windowWidth = window.innerWidth;
            if (windowWidth > 1365 && windowWidth < 1600) {
                rawData = rawData.map(arr => arr.slice(-7));
                dateNum = 7;
            }
            let maxNum = findMaxSumData(rawData, dateNum);
            let interval = maxNum / 5;
            const totalData = [];
            for (let i = 0; i < rawData[0].length; ++i) {
                let sum = 0;
                for (let j = 0; j < rawData.length; ++j) {
                    sum += rawData[j][i];
                }
                totalData.push(sum);
            }

            //循环处理权限问题
             //处理rawData里面的几个数据是否都有
             let rawDataHasData = [];
             let colors = [];
             let titles = [];
             if(info.protect_num.vol_cdp_protect.module.complete_cdp_backup.show){
                 rawDataHasData.push(totalData[0]);
                 colors.push("#5CDEB8");
                 titles.push(LANG.UI_BACKUP_DATA_MODULE_OS);
             }
             if(info.protect_num.vol_cdp_protect.module.vol_cdp_backup.show){
                 rawDataHasData.push(totalData[1]);
                 colors.push("#62CBF8");
                 titles.push(LANG.UI_VOL_CDP_RECOVER_VOL);
             }

            const series = titles.map((name, sid) => {
                return {
                    name,
                    type: 'bar',
                    barWidth: '10px',
                    itemStyle: {
                        // 柱子颜色 - 从顶部原始颜色渐变到底部白色，并设置顶部圆角
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {
                                offset: 0,
                                color: colors[sid] // 顶部：使用原始颜色
                            },
                            {
                                offset: 1,
                                color: hexToRgba(colors[sid], 0.3) // 底部：白色
                            }
                        ]),
                        borderRadius: [8, 8, 0, 0] // 设置顶部圆角 [上左, 上右, 下右, 下左]
                    },
                    label: {
                        show: false,
                    },
                    data: rawData[sid].map((d, did) =>
                        rawDataHasData[did] <= 0 ? 0 : d 
                      )
                };
            });
            option = {
                title: {
                    text: LANG.UI_HOMEPAGE_PROTECT_DATA,
                    left: 'left',
                    top: 'top',
                    textStyle: {
                        color: '#3D3D46',
                        fontSize: 14,
                        fontWeight: '400'
                    },
                    padding: 0
                },
                color: ["#20C99A"],
                tooltip: {
                    trigger: "axis",
                    axisPointer: {
                        type: "shadow"
                    },
                    formatter: function(params) {
                        // 获取默认的 tooltip 内容，然后包装在自定义样式中
                        let header = '<div style="font-size: 12px;color:#718096;margin-bottom: 8px;">' + params[0].name + '</div>';
                        let content = '<table style="width: 100%;border-collapse: collapse;">';
                        
                        params.forEach(param => {
                            content += `
                                <tr>
                                    <td style="text-align: left; padding: 2px 0;">
                                        ${param.marker} 
                                        <span style="font-size: 14px;color: #797F8A;margin-right: 51px;">${param.seriesName}</span>
                                    </td>
                                    <td style="text-align: right; padding: 2px 0;">
                                        <span style="font-family: Poppins, Poppins;font-size: 14px;color: #4C5563;font-weight: 500;">${param.value || 0}</span>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        content += '</table>';
                        return header + content;
                    }
                },
                legend: {
                    icon: "square",
                    show: true,
                    right: 0,
                    top: 20,
                    itemWidth: 10,
                    itemHeight: 10,
                    itemGap: 16,
                    textStyle: {
                        color: "#333333",
                        fontSize: "12",
                        padding: [0, -2, -2, 1],
                    },
                },
                grid: {
                    left: "5",
                    top: "55",
                    right: "0",
                    bottom: "0",
                    containLabel: true
                },
                xAxis: [
                    {
                        type: "category",
                        data: getLastSevenDaysPure(dateNum),
                        z: 10,
                        axisLabel: {
                            textStyle: {
                                color: "#B5B5C3",
                                fontSize: "12"
                            }
                        },
                        axisLine: {
                            show: true,
                            lineStyle: {
                                color: "#F1F1F5",
                            }
                        },
                        axisTick: {
                            show: false
                        },
                    }
                ],
                yAxis: [
                    {
                        type: "value",
                        min: 0,
                        max: maxNum,
                        interval: interval,
                        axisLabel: {
                            textStyle: {
                                color: "#B5B5C3",
                                fontSize: "12"
                            },
                            formatter: function(value, index){
                                if(value >= 1024 && value <= 1024*1024){
                                    return Math.round(value * 10 / 1024) / 10 + "KB";
                                }else if(value >= 1024*1024 && value <= 1024*1024*1024){
                                    return Math.round(value * 10 / (1024*1024)) / 10 + "MB";
                                }else if(value > 1024 *1024*1024 && value <= 1024 * 1024 * 1024*1024){
                                    return Math.round(value * 10 /(1024 * 1024 * 1024) ) / 10 + "GB";
                                }else if(value > 1024*1024*1024*1024){
                                    return Math.round(value * 10 /(1024 *1024 * 1024 * 1024) ) / 10 + "TB";
                                }
                                else{
                                    return value + "B";
                                }
                            }
                        },
                        axisLine: {
                            lineStyle: {
                                show:false
                            }
                        },
                        splitLine: {
                            lineStyle: {
                                color: "#F1F1F5",
                                type: 'dashed',
                            }
                        },
                    }
                ],
                series
            };
            echarts28.setOption(option);
        }
        // 初始化echarts
        initechart26(info);
        // 初始化echarts
        initechart27(info);
        // 初始化echarts
        initechart28(info);
    }


    //--------------------------------card_copy_1-------------------------------------------
    const init_card_copy_1 = (info) => {
        const initechart29 = () => {
            var chartDom = document.querySelector('[gs-id="card_copy_1"] .echarts_copy_num_1');
            echarts29 = echarts.init(chartDom);

            //确认是否显示
            var colors = [];
            var raduis = ['90%', '75%', '60%', '45%'];
            var values = [];
            // var labels = ['整机', '卷', '数据库', '文件'];

            if(info.protect_num.copy.module.machine_copy.show){
                colors.push("#2DCC7F");
                values.push(Math.round(info.protect_num.copy.module.machine_copy.protected / info.protect_num.copy.module.machine_copy.total));
            }else{
                $(".copy_show_1").hide();
            }
            if(info.protect_num.copy.module.vol_cdp_copy.show){
                colors.push("#1DD4E0");
                values.push(Math.round(info.protect_num.copy.module.vol_cdp_copy.protected / info.protect_num.copy.module.vol_cdp_copy.total));
            }else{
                $(".copy_show_2").hide();
            }
            if(info.protect_num.copy.module.dbcdpcopy.show){
                colors.push("#4C94FF");
                values.push(Math.round(info.protect_num.copy.module.dbcdpcopy.protected / info.protect_num.copy.module.dbcdpcopy.total));
            }else{
                $(".copy_show_4").hide();
            }
            if(info.protect_num.copy.module.file_copy.show){
                colors.push("#30CBCB");
                values.push(Math.round(info.protect_num.copy.module.file_copy.protected / info.protect_num.copy.module.file_copy.total));
            }else{
                $(".copy_show_3").hide();
            }
            //初始化页面数据
            $('[gs-id="card_copy_1"] .protect_module_copy_machine_num').text(info.protect_num.copy.module.machine_copy.protected+"/"+info.protect_num.copy.module.machine_copy.total);
            $('[gs-id="card_copy_1"] .protect_module_copy_os_num').text(info.protect_num.copy.module.vol_cdp_copy.protected+"/"+info.protect_num.copy.module.vol_cdp_copy.total);
            $('[gs-id="card_copy_1"] .protect_module_copy_fs_num').text(info.protect_num.copy.module.file_copy.protected+"/"+info.protect_num.copy.module.file_copy.total);
            $('[gs-id="card_copy_1"] .protect_module_copy_db_num').text(info.protect_num.copy.module.dbcdpcopy.protected+"/"+info.protect_num.copy.module.dbcdpcopy.total);
            
            var option = {
                series: []
            };
            
            for (var i = 0; i < values.length; i++) {
                var series_each = {
                    type: 'gauge',
                    startAngle: 90,
                    radius: raduis[i],
                    endAngle: -270,
                    pointer: {
                        show: false
                    },
                    progress: {
                        show: true,
                        width: 6,
                        roundCap: true,
                        itemStyle: {
                            color: {
                                type: 'linear',
                                x: 0,
                                y: 0,
                                x2: 0,
                                y2: 1,
                                colorStops: [{
                                    offset: 1,
                                    color: colors[i]
                                }]
                            },
                        },
                    },
                    axisLine: {
                        lineStyle: {
                            color: [[1, '#F0F2F4']],
                            width: 6
                        }
                    },
                    splitLine: {
                        show: false
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        show: false
                    },
                    data: [
                        {
                            value: values[i],
                        },
                    ],
                    detail: {
                        show: false,
                    },
                }
                option.series.push(series_each);
            }
    
            echarts29.setOption(option);
        }



        const initechart30 = (info) => {
            var chartDom = document.querySelector('[gs-id="card_copy_1"] .echarts_copy_capacity_1');
            echarts30 = echarts.init(chartDom);
            var option;
            const machine_copy_data = fillMissingDates(info.protect_capacity.machine_copy_data, 7);
            const vol_cdp_copy_data = fillMissingDates(info.protect_capacity.vol_cdp_backup_data, 7);
            const file_copy_data = fillMissingDates(info.protect_capacity.file_copy_data, 7);
            const dbcdpcopy_data = fillMissingDates(info.protect_capacity.dbcdpcopy_data, 7);
            let rawData = [
                machine_copy_data, //整机复制
                vol_cdp_copy_data, //卷复制
                file_copy_data, //文件复制
                dbcdpcopy_data, //数据库复制
            ];
            let dateNum = 7;//x轴显示几天的数据
            const windowWidth = window.innerWidth;
            if (windowWidth > 1365 && windowWidth < 1600) {
                rawData = rawData.map(arr => arr.slice(-7));
                dateNum = 7;
            }
            let maxNum = findMaxSumData(rawData,dateNum);
            let interval = maxNum / 5;
            const totalData = [];
            for (let i = 0; i < rawData.length; ++i) {
                let sum = 0;
                for (let j = 0; j < rawData[i].length; ++j) {
                    sum += rawData[i][j];
                }
                totalData.push(sum);
            }
            //循环处理权限问题
            //处理rawData里面的几个数据是否都有
            let rawDataHasData = [];
            let colors = [];
            let titles = [];

            if(info.protect_num.copy.module.machine_copy.show){
                rawDataHasData.push(totalData[0]);
                colors.push("#2DCC7F");
                titles.push(LANG.UI_BACKUP_DATA_MODULE_OS);
            }
            if(info.protect_num.copy.module.vol_cdp_copy.show){
                rawDataHasData.push(totalData[1]);
                colors.push("#1DD4E0");
                titles.push(LANG.UI_VOL_CDP_RECOVER_VOL);
            }
            if(info.protect_num.copy.module.file_copy.show){
                rawDataHasData.push(totalData[2]);
                colors.push("#30CBCB");
                titles.push(LANG.UI_FILE_FILE);
            }
            if(info.protect_num.copy.module.dbcdpcopy.show){
                rawDataHasData.push(totalData[3]);
                colors.push("#4C94FF");
                titles.push(LANG.UI_VISUAL_DB);
            }



            const series = titles.map((name, sid) => {
            return {
                name,
                type: 'bar',
                stack: 'total',
                barWidth: '8px',
                itemStyle: {
                // 柱子颜色
                normal: {
                    color: colors[sid], // 使用颜色数组中的颜色
                    borderColor: 'transparent',
                    borderWidth: 1.2,
                }
                },
                label: {
                show: false,
                },
                data: rawData[sid].map((d, did) =>
                    rawDataHasData[sid] <= 0 ? 0 : d 
                  )
            };
            });
            option = {
                title: {
                    text: LANG.UI_HOMEPAGE_PROTECT_DATA, 
                    left: 'left',
                    top: 'top',
                    textStyle: {
                        color: '#3D3D46',
                        fontSize: 14, 
                        fontWeight: '400'
                    },
                    padding: 0 
                },
                color: ["#20C99A"],
                tooltip: {
                    trigger: "axis",
                    axisPointer: {
                        type: "shadow"
                    },
                    formatter: function(params) {
                        // 获取默认的 tooltip 内容，然后包装在自定义样式中
                        let header = '<div style="font-size: 12px;color:#718096;margin-bottom: 8px;">' + params[0].name + '</div>';
                        let content = '<table style="width: 100%;border-collapse: collapse;">';
                        
                        params.forEach(param => {
                            function getValueUnit(value){
                                if(value >= 1024 && value <= 1024*1024){
                                    return Math.round(value * 10 / 1024) / 10 + "KB";
                                }else if(value >= 1024*1024 && value <= 1024*1024*1024){
                                    return Math.round(value * 10 / (1024*1024)) / 10 + "MB";
                                }else if(value > 1024 *1024*1024 && value <= 1024 * 1024 * 1024*1024){
                                    return Math.round(value * 10 /(1024 * 1024 * 1024) ) / 10 + "GB";
                                }else if(value > 1024*1024*1024*1024){
                                    return Math.round(value * 10 /(1024 *1024 * 1024 * 1024) ) / 10 + "TB";
                                }
                                else{
                                    return value + "B";
                                }
                            }
                    
                            let valueUnit = getValueUnit(param.value);  // 这里正确使用 param.value
                            content += `
                                <tr>
                                    <td style="text-align: left; padding: 2px 0;">
                                        ${param.marker} 
                                        <span style="font-size: 14px;color: #797F8A;margin-right: 51px;">${param.seriesName}</span>
                                    </td>
                                    <td style="text-align: right; padding: 2px 0;">
                                        <span style="font-family: Poppins, Poppins;font-size: 14px;color: #4C5563;font-weight: 500;">${valueUnit || 0}</span>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        content += '</table>';
                        return header + content;
                    }
                },
                legend: {
                    icon: "square",
                    show: true,
                    right: 0,
                    top: 20,
                    itemWidth: 10,
                    itemHeight: 10,
                    itemGap: 16,
                    textStyle: {
                        color: "#333333",
                        fontSize: "12",
                        rich: {
                            a: {
                                verticalAlign: 'middle',
                            },
                        },
                        padding: [0, -2, -2, 1],
                    }
                },
                grid: {
                    left: "5",
                    top: "55",
                    right: "0",
                    bottom: "0",
                    containLabel: true
                },
                yAxis: [
                    {
                        type: "category",
                        data: getLastSevenDaysPure(dateNum),
                        z: 10,
                        axisLabel: {
                            textStyle: {
                                color: "#B5B5C3",
                                fontSize: "12"
                            },
                        },
                        axisLine: {
                            show: true,
                            lineStyle: {
                                color: "#F1F1F5" 
                            }
                        },
                        axisTick: {
                            show: false
                        },
                    }
                ],
                xAxis: [
                    {
                        type: "value",
                        min: 0,
                        max: maxNum,
                        interval: interval,
                        axisLabel: {
                            textStyle: {
                                color: "#7C7D8B",
                                fontSize: "12"
                            },
                            formatter: function(value, index){
                                if(value >= 1024 && value <= 1024*1024){
                                    return Math.round(value * 10 / 1024) / 10 + "KB";
                                }else if(value >= 1024*1024 && value <= 1024*1024*1024){
                                    return Math.round(value * 10 / (1024*1024)) / 10 + "MB";
                                }else if(value > 1024 *1024*1024 && value <= 1024 * 1024 * 1024*1024){
                                    return Math.round(value * 10 /(1024 * 1024 * 1024) ) / 10 + "GB";
                                }else if(value > 1024*1024*1024*1024){
                                    return Math.round(value * 10 /(1024 *1024 * 1024 * 1024) ) / 10 + "TB";
                                }
                                else{
                                    return value + "B";
                                }
                            }
                        },
                        axisLine: {
                            lineStyle: {
                                show:false
                            }
                        },
                        splitLine: {
                            lineStyle: {
                                color: "#F1F1F5"
                            }
                        }
                    }
                ],
                series,
            };
            echarts30.setOption(option);
        }


        // 初始化echarts
        initechart29(info);
         // 初始化echarts
         initechart30(info);
    }



//------------------------------------------------------------------------------------------------------------
    const initUser = (userVal)=>{
        // 当前用户相关联的用户
        pAjaxRequest({},'/api/v1/users/auth_lists','GET',(d)=>{
            let rows = d.data.rows;
            current_userName = d.data.currentUser;
            let userselect = document.getElementById('user_select');
            let usernameEle = document.getElementById('username');
            USER_UUID = d.data.currentUserUuid;
            $('#current_user_name').html(current_userName);
            if(rows.length == 0){
                $('#user_select').hide();
                return;
            }
            rows.forEach(item => {
                const option = document.createElement('option');
                option.value = item.user_uuid;
                option.text = item.user_name;
                if(userVal && userVal == option.value){
                    option.selected = true;
                    USER_UUID = option.value;
                    $('#current_user_name').html(option.text);
                }
                userselect.appendChild(option);
            });
        });

    }
    //----------------------------------------
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
             // 增加 blinking 动画
            $('.top-menu .nav .dropdown-notification .dropdown-toggle').addClass('has-data');
        } else {
            // 移除 blinking 动画
            $('.top-menu .nav .dropdown-notification .dropdown-toggle').removeClass('has-data');
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
    // 如果有类似这样的代码，需要添加防重复逻辑
window.addEventListener('popstate', function(event) {
    // 添加防重复逻辑
    if (event.state && event.state.initialized) {
        return; // 已经初始化过，避免重复执行
    }
    // 你的页面初始化代码
});
   
    return {
        //main function to initiate the module
        init: function () {
            
            initGrid();
            initListener() //初始化监听事件
            initUser();
            initUserType();
            initLoginHistory();
            initAlarmTips();
            
            // 综合检查：URL 和状态
            const title = document.title;
            // 使用 replaceState 替代 pushState，避免添加新的历史记录
            History.pushState({url:"./content/platform/databackup_center.php",routeName: 'homepage'}, title, "?homepage");
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
