<style>
    .nav-tab-radios{
        height: 285px;
    }

</style>
<div id="speed" class="panel-collapse collapse ">
    <div class="panel-body">

        <div class="form-group">
            <label class="control-label col-md-2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPES'] ?>
            </label>
            <div class="col-md-6">
                <div class="">
                    <select class="bs-select dataBorder form-control" style="height:34px;width:200px" data-show-subtext="true" id="speedtypeselect">
                        <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_AUTO'] ?></option>
                        <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_CHOOSE_NAME'] ?></option>
                    </select>
                    <a class="popovers ml15"  data-container="body" data-trigger="hover" data-placement="right"  data-html="true"
                       data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_CHOOSE_TIPS'] ?>" data-original-title="" title="" style="float: left;margin-top: -25px;margin-left: 215px !important;">
                        <i class="viconfont vicon-tishi"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="form-group" id="task_type_global_speed_strategy" style="display: none;">
            <label class="control-label col-md-2"><?php echo $LANG['UI_GLOBAL_STRATEGY_TASK_LEVEL'] ?>
            </label>
            <div class="col-md-6">
                <div class="">
                    <select class="bs-select dataBorder form-control" style="height:34px;width: 200px" data-show-subtext="true" id="tasklevelselect">'
                        <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_TASK_LEVEL_NORMAL'] ?></option>
                        <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_TASK_LEVEL_FIRST'] ?></option>
                        <option value="3"><?php echo $LANG['UI_GLOBAL_STRATEGY_TASK_LEVEL_HIGH'] ?></option>
                    </select>
                    <a class="popovers ml15"  data-container="body" data-trigger="hover" data-placement="right"
                       data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_TASK_LEVEL_TIPS'] ?>" data-original-title="" title="" style="float: left;margin-top: -25px;margin-left: 215px !important;">
                        <i class="viconfont vicon-tishi"></i>
                    </a>
                </div>
            </div>
        </div>

        <div id="show_type_1" style="display: none;">
            <div class="form-group">
                <label class="control-label col-md-2"><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?>
                </label>
                <div class="col-md-10" style="margin-left:-15px;">
                    <?php include_once(ROOT_PATH . '/content/platform/global_strategy/choose_global_strategy.php'); ?>
                    <!--       选择全局限速策略-->
                </div>
            </div>
        </div>

        <div id="show_type_2">
            <?php include_once(ROOT_PATH . '/content/platform/global_strategy/speedLimitStrategy.php'); ?>
            <!--       全局限速策略-->
        </div>

    </div>
</div>

<script>
    $('#speedtypeselect').on('change', function () {

        //获取所有文本内容
        var value = $('#speedtypeselect').val();

        if (value == 1) {
            $('#show_type_1').show();
            $('#show_type_2').hide();
            $('#task_type_global_speed_strategy').show();
        } else {
            $('#show_type_2').show();
            $('#show_type_1').hide();
            // 不显示任务级别
            $('#task_type_global_speed_strategy').hide();
        }
    });
    $(function(){
        // 初始化设置策略并且携带初始数据
        let speedInfo = [];
        addGlobalStrategy.init({'strategy_type':2,speedInfo:speedInfo,initSpeedFlag:1});
        // 获取限速策略配置
        let info = $('#speedstrategy').getSpeedStrategyConfigFinal();

    });

    // 获取设置的所有策略配置信息
    var speedSubmit = function (){
        let info = '';
        var level = $('#tasklevelselect').val();
        var value = $('#speedtypeselect').val();
        if (value == 1) {
            // 选择策略
            var selectedRow = $('.strategy-table #table').bootstrapTable('getSelections');
            if (selectedRow.length == 1) {
                info = selectedRow[0].uuid
            }
        } else {
            // 自定义
            info = $('#speedstrategy').getSpeedStrategyConfigFinal();
        }

         // console.log('最终的配置如下：type:' + value + ',level:' + level);
        // console.log(info);
    }
</script>
