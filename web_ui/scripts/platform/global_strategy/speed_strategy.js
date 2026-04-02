
var addGlobalStrategy = function () {
    var initSpeedFlag = false;//初始化限速策略设置
    var strategy_type = 2;
    var speedInfo = []; // 设置的策略信息列表
    var module_type = 0;
    // -------------初始化页面--------

    //  ---------页面交互-------------
    //初始化监听事件
    var initListeners = function () {
        if (!initSpeedFlag) {
            initSpeedTimeStrategy();
        }
        //添加限速策略确定 示范
        // $('#add_submit').on('click', speedSubmit);
    }
    
    // 获取设置的所有策略配置信息
    var speedSubmit = function (){
       let info = $('#speedstrategy').getSpeedStrategyConfigFinal();
        // console.log('最终的配置如下：');
       // console.log(info);
    }

    //初始化限速策略
    var initSpeedTimeStrategy = function () {
        // 这里如果是编辑的话，需要读取
        var strategy = [];
        strategy[0] = {
            mode: 1,
            strategy_type: strategy_type,
            days: [0, 0, 0, 0, 1, 0, 0],
            start_time: '23:00:00',
            end_time: '23:30:00',
            speedInfo: speedInfo,
        };
        $('#speedstrategy').speedstrategy({ config: strategy, module_type: module_type });
        
        initSpeedFlag = true;
    }

    return {
        init: function (options) {
            strategy_type = options.strategy_type != undefined ? options.strategy_type : 2;
            speedInfo = options.speedInfo != undefined ? options.speedInfo : [];
            if (options.initSpeedFlag && options.initSpeedFlag != undefined) {
                initSpeedFlag = false;
            }
            module_type = options.module_type ?? 0;
            // console.log(options)
            initListeners(); //初始化监听事件
        }
    }
}();
