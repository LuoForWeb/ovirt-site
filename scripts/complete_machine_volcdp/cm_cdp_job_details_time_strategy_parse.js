
/**
 * 解析策略：备份任务为标签策略，恢复任务为任务启动策略
 */
var   parseStartStrategy = function(timeStrategyInfo,taskType){
    let timeStrategy = getTimeStrategy(timeStrategyInfo);
    if(timeStrategy==""){
        timeStrategy = LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE;
    }
    switch(taskType){
        case CONF.TASK_TYPE.VOL_CDP_BACKUP:
        case CONF.TASK_TYPE.VOL_CDP_REPLICATION:
            $('#tagdes').html(timeStrategy);
            let arr = timeStrategy.split('<br>')
            let str = ''
            for(let i = 0; i<arr.length;i++){
                str=str+arr[i]+`\n`
            }
            $('#tagdes').prop('title', str);
            break;
        case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
            $('#recoverStartStrategy').html(timeStrategy);
            break;
    }
}

//得到时间策略描述信息
var getTimeStrategy = function(msg){
    let timeInfo = LANG.UI_PUBLIC_NOTHING;
    if(!msg){
        return timeInfo;
    }
    timeInfo = "";
    for(let i=0; i<msg.length; i++){
        let strategy = msg[i];
        let des = "";
        if(CONF.STRATEGY_TYPE.DAY == strategy.type){
            des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy); 
        }else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
            des += getStrategyFrequency(strategy);
            if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
                des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
            }else{
                des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
            }
        }else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
            des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
        }else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
            des += strategy.startTime; 
        }else{
            des += LANG.UI_PUBLIC_NOTHING;
        }
        timeInfo += des;
    }
    return timeInfo;
}

var  getStrategyDays = function(days){
    let desDays = '';
    $.each(days, function(i,d){
        if(1 == d){
            let day = i+1;
            if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
                desDays += day + ", ";
            }else{
                desDays += "Day" + day + ", ";
            }
        
        }
    });
    return desDays;
}

//获取每周显示日期
var getStrategyWeek = function(days){
    let desDays = '';
    $.each(days, function(i,d){
        if(1 == d){
            desDays += CONF.WEEK[i] + ", ";
        }
    });
    return desDays;
}

var getEachStrategy = function(strategy){
    let desEach = '';
    desEach += strategy.startTime;
    //如果是英文版 需要加空格
    if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
        desEach += " "; //策略开始时间
    }
    desEach += LANG.UI_STRATEGY_START + ", ";
    if(strategy.rollFlag){
        desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
    }else{
        desEach += LANG.UI_STRATEGY_ROLL_NO;
    }
    desEach += "<br>";
    return desEach;
}

//得到备份间隔描述
var getStrategyFrequency = function(strategy){
    var frequency = "";
    var frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
    for(var i=1;i<=52;i++){
        if(strategy.frequency == "s" +i){
            if(i == 1){
                frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
            }
            else{
                frequency = frequencyLang.replace('x', i) + ",";
            }
        }
    }
    return frequency;
}