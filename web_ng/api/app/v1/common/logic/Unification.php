<?php

namespace app\v1\common\logic;

/**
 * note          备份恢复统一处理类 logic
 * @author       liushuai@vinchin.com
 * @date         2023/4/18 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Unification extends Base
{
    
    /**
     * 统一时间策略
     * @author liushuai@vinchin.com
     * @date 2023/4/18
     * @param 的部分参数(前端接收参数)
     *   time_strategy 时间策略
     *   backup_type 备份策略类型 1:按策略备份 2:一次性备份
     *   once_start_time 一次性备份时间
     *   full_backup 完全备份
     *   incremental_backup 增量备份
     *   differential_backup 差异备份
     *   forever_incremental 永久增量
     *   
     *   {strategy_type 策略类型 1:每天 2:每周 3:每月 4:一次性备份
     *   days 备份日期
     *   start_time 开始时间
     *   roll_flag 滚动执行标志
     *   roll_interval 滚动间隔
     *   roll_end_time 结束时间
     *   week_frequency 可选配置，备份频率设置间隔多少周，选择每周策略才能配置，默认每周无间隔}
     *$strategy_group_uuid(目前无)
     *   
     *
     *@return
     * "time_strategy_list":[
        {
            "strategy_type":4,  //策略类型 对应前端strategy_type
            "mode":1,  //完全1/增量2/差异3/日志4/标签7  根据枚举对应,这儿的枚举前端没有 根据前端的
                                                                    完备差备等特殊字段有无数据来判断 可能比左边列举的多 config里面的'BACKUP_MODE'
            "days":"",  //备份日期 对应前端days
            "start_time":"2023-04-27 16:10:50",  //开始时间  对应前端once_start_time或者start_time
            "roll_flag":2, //滚动标志  对应前端roll_flag
            "roll_interval":0, //滚动间隔 对应前端滚动间隔
            "roll_end_time":"", //滚动结束时间  对应前端roll_end_time
            "global_id":0,  //全局策略(目前无)
            "strategy_group_uuid":null //策略组uuid(目前无)
            }
     *   
     *   
     */
    public function unifyTimeStrategy($params,$strategy_group_uuid = ""){
        //如果为空值 则初始化返回参数后直接返回
        if(empty($params)){
            $dataList = $this->initOnceTime($params);
            return $dataList;
        }
        //备份策略类型
        $backup_type = $params['backup_type'];
        //获取策略备份模式
        $modeList = xphp_get_config('task')['BACKUP_MODE'];
        
        if($backup_type == 2){
            //如果为一次性备份
            $dataList = $this->initOnceTime($params);
            return $dataList;
        }else if($backup_type == 1){
            //初始化数据
            $dataList = array();
            //完全备份
            if(!empty($params['full_backup'])){
                $dataList[] =  $this->initTimeStrategy($modeList['FULL'], $params['full_backup']);
            }
            //增量备份
            if(!empty($params['incremental_backup'])){
                $dataList[] =  $this->initTimeStrategy($modeList['INCREMENTAL'], $params['incremental_backup']);
            }
            //差异备份
            if(!empty($params['differential_backup'])){
                $dataList[] =  $this->initTimeStrategy($modeList['DIFFERENTIAL'], $params['differential_backup']);
            }
            //永久增量
            if(!empty($params['forever_incremental'])){
                $dataList[] =  $this->initTimeStrategy($modeList['INCREMENTAL'], $params['forever_incremental']);
            }
            
        }
        return $dataList;
    }
    
    
    /**
     * 一次性备份处理或初始化
     * @author liushuai@vinchin.com
     * @date 2023/4/18
     * @param unknown $params
     */
    private function initOnceTime($params,$strategy_group_uuid = ""){
        //获取开始时间
        $start_time = "";
        if(!empty($params['once_start_time'])){
            $start_time = $params['once_start_time'];
        }
        
        //组装数据
        $info = array(
            'strategy_type' => 4,
            'mode' => 1, //一次性备份都为完备
            'days' => "",
            'start_time' => $start_time,
            'roll_flag' => 2,
            'roll_interval' => 0,
            'roll_end_time' => "",
            'global_id' => 0,
            'strategy_group_uuid' => "",
        );
        return $info;
        
    }
    
    
    /**
     * 初始化时间策略内容
     * @author liushuai@vinchin.com
     * @date 2023/4/18
     * @param unknown $mode mode
     * @param unknown $params
     * @param string $strategy_group_uuid
     */
    private  function initTimeStrategy($mode,$params,$strategy_group_uuid = ""){
        //获取策略类型
        $strategyList = xphp_get_config('task')['STRATEGY_TYPE'];
        //获取策略类型
        $strategy_type = intval($params['strategy_type']);
        //mode
        $mode = intval($mode);
        
        //获取滚动间隔
        $frequency = "";
        if(!empty($params['frequency'])){
            $frequency = $params['frequency'];
        }
        //获取备份日期
        //备份日期为日期和滚动间隔的拼接  例如:1100100s9
        $days = implode("", $params['days']) . $frequency;
        
        if($strategyList['EVERY_DAY'] == $strategy_type){
            //每天备份
            $days = "1111111";
        }
        //获取开始时间
        $start_time = $params['start_time'];
        //获取滚动标志
        $roll_flag = $params['roll_flag'] ? 1:2;
        //获取滚动间隔
        $roll_interval = $params['roll_interval'];
        //获取滚动结束时间
        $roll_end_time = $params['roll_end_time'];
        //global_id(无,已弃用)
        $global_id = 0;
        //获取策略组uuid(无)(目前只有虚拟机有)
        $strategy_group_uuid = "'";
        //组装数据
        $info = array(
            'strategy_type' => $strategy_type,
            'mode' => $mode, 
            'days' => $days,
            'start_time' => $start_time,
            'roll_flag' => $roll_flag,
            'roll_interval' => $roll_interval,
            'roll_end_time' => $roll_end_time,
            'global_id' => $global_id,
            'strategy_group_uuid' => $strategy_group_uuid,
        );
        return $info;
    }
    
    
    
    //---------限速策略
    
    /**
     *  统一限速策略
     * @author liushuai@vinchin.com
     * @date 2023/4/19
     * @param 前端所传参数:
       [{
            "speed_type":2, //1:每天 2:每周 3:每月 4:永久限速
            "start_time":"23:00:00", //限速开始时间
            "end_time":"23:30:00", //限速结束时间
            "days":[1, 0, 1, 0, 0, 1, 0], //限速日期
            "value":10485760 //限速值
        }],
        $strategy_group_uuid 策略组uuid 未使用传值为空或不传

     * 或者永久限速
     *  {
            "speed_type":4,
            "value":15728640
        }

     * @return 后端所需参数(老版本需要这些值,有可能有些字段已弃用,未核对,暂时按照以前的结构向后端传)
     * [{
            "strategy_uuid":"d80b45b97efe85e4ccd3c92b4cc58a14b78f", //uuid 前端js生成的一个唯一uuid
            "strategy_name":"", //策略名字, 一直为空
            "strategy_type":4, //和前端speed_type对应 //1:每天 2:每周 3:每月 4:永久限速
            "start_time":"",//限速开始时间
            "end_time":"",//限速结束时间
            "days":"",//限速日期
            "speed_limited_value":10485760,//限速值
            "extra_info":"", //额外信息,一直为空
            "remark":"", //策略组要用到用作对比数据是否改变,其他模块目前没用到,可传空
            "strategy_group_uuid":null //策略组uuid 目前为空(只有虚拟机有)
        }]
     * 
     * 
     * 
     */
    public function unifySpeedStrategy($params,$strategy_group_uuid = ""){
        //初始化返回数据
        $dataList = array();
        //如果参数为空,则返回参数
        if(empty($params)){
            $dataList[] = $this->initForeverSpeed($params);
            return $dataList;
        }
        
        //循环拼装结构
        foreach ($params as $each){
            //获取限速类型
            $strategy_type = $params['speed_type'];
            switch ($strategy_type){
                case 1://每天
                case 2://每周
                case 3://每月
                    $dataList[] = $this->initSpeedStrategy($each);
                    break;
                case 4://永久限速
                    $dataList[] = $this->initForeverSpeed($each);
                    break;
            }
        }
        return $dataList;
    }
    
    
    
    /**
     * 永久限速或初始化数据
     * @author liushuai@vinchin.com
     * @date 2023/4/18
     * @param unknown $params
     */
    private function initForeverSpeed($params,$strategy_group_uuid = ""){
        //初始化值为1024
        $value = 1024;
        if(!empty($params['value'])){
            $value = intval($params['value']);
        }
        //组装数据
        $info = array(
            'strategy_uuid' => "",
            'strategy_name' => "",
            'strategy_type' => 4,
            'start_time' => "",
            'end_time' => "",
            'days' => "",
            'speed_limited_value' => $value,
            'extra_info' => "",
            'remark' => "",
            'strategy_group_uuid' => "",
        );
        return $info;
        
    }
    
    
    
    
    /**
     * 初始化限速策略内容
     * @author liushuai@vinchin.com
     * @date 2023/4/18
     * @param unknown $params
     */
    private function initSpeedStrategy($params,$strategy_group_uuid = ""){
        //获取策略uuid
        $strategy_uuid = "";
        //获取策略名称
        $strategy_name = "";
        //获取策略类型
        $strategy_type = intval($params['speed_type']);
        //获取策略开始时间
        $start_time = v1_formart_time($params['start_time']);
        //获取策略结束时间
        $end_time = v1_formart_time($params['end_time']);
        //获取天数
        $days = implode("", $params['days']);
        //获取限速大小
        $speed_limited_value = intval($params['value']);
        //获取额外信息
        $extra_info = "";
        //获取备注信息
        $remark = "";
        //获取策略组uuid
        $strategy_group_uuid = "";
        //组装数据
        $info = array(
            'strategy_uuid' => $strategy_uuid,
            'strategy_name' => $strategy_name,
            'strategy_type' => $strategy_type,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'days' => $days,
            'speed_limited_value' => $speed_limited_value,
            'extra_info' => $extra_info,
            'remark' => $remark,
            'strategy_group_uuid' => $strategy_group_uuid,
        );
        return $info;
        
    }
    
    
    //----存储策略-----
    
    /**
     * 统一存储策略
     * @author liushuai@vinchin.com
     * @date 2023/4/19
     * @param unknown $params
     *  {
            "compress_flag": false,
            "deduplication_flag": true,
            "block_size": 256
            "encrypt_flag"
            "password"
        }

     * @param string $strategy_group_uuid
     * @return {
        "encrypt_flag":2, //数据加密
        "compress_flag":1, //压缩传输
        "deduplication_flag":2, //重复数据删除
        "block_size":1048576,  //存储块大小
        "strategy_group_uuid":null, //策略组uuid
        "password_auto_flag":1, //自动生成密码
        "password":"" //密码
    }
     */
    public function unifyStorageStrategy($params,$strategy_group_uuid = ""){
        $dataList = array();
        //获取数据
        //获取数据加密
        $encrypt_flag = 2;
        if(!empty($params['encrypt_flag'])){
            $encrypt_flag = $params['encrypt_flag'] ? 1:2;
        }
        //获取压缩传输
        $compress_flag = 2;
        if(!empty($params['compress_flag'])){
            $compress_flag = $params['compress_flag'] ? 1:2;
        }
        //获取重复数据删除
        $deduplication_flag = 2;
        if(!empty($params['deduplication_flag'])){
            $deduplication_flag = $params['deduplication_flag'] ? 1:2;
        }
        //获取数据块大小
        $block_size = 0;
        if(!empty($params['block_size'])){
            $block_size = intval($params['block_size']);
        }
        //获取策略组uuid(目前没用)
        $strategy_group_uuid = "";
        //获取自动生成密码
        $password_auto_flag = 2;
        if(!empty($params['auto_create_password_flag'])){
            $password_auto_flag = $params['auto_create_password_flag'] ? 1:2;
        }
        //获取密码
        $password = 2;
        if(!empty($params['password'])){
            $password = $params['password'];
        }
        
        //组装数据
        $dataList = array(
            "encrypt_flag" => $encrypt_flag,
            "compress_flag" => $compress_flag,
            "deduplication_flag" => $deduplication_flag,
            "block_size" => $block_size * 1024,
            "strategy_group_uuid" => $strategy_group_uuid,
            "password_auto_flag" => $password_auto_flag,
            "password" => $password,
        );
        return $dataList;
    }
    
    
    //-------保留策略-----
    
    /**
     * 统一保留策略
     * @author liushuai@vinchin.com
     * @date 2023/4/19
     * @param unknown $params
     * {
             "reserved_type": 1,
             "value": 46,
	         "gfs_reserved_flag ": true,
             "gfs_reserved_strategy": [
		      {
		          "gfs_reserved_type": 3,
                  "gfs_reserved_start": 6,
                  "gfs_reserved_value ": 3,
		
                },
                    ],
        }
     * @param string $strategy_group_uuid
     * @return :保留策略：{
        "strategy_type":1,
        "number":30,
        "auto_archive_flag":2,
        "strategy_group_uuid":""
    }
    GFS策略：{
            "level1_type":1,
            "level2_type":7,
            "retention_num":5
        }
     */
    public function unifyReserveStrategy($params,$strategy_group_uuid = ""){
        $dataList = array(
            'reserveStrategy' => array(),
            'gfs' => array(),
        );
        //获取保留方式
        $strategy_type = 1;
        if(!empty($params['reserved_type'])){
            $strategy_type = intval($params['reserved_type']);
        }
        
        //获取保留个数
        $number = 1;
        if(!empty($params['value'])){
            $number = intval($params['value']);
        }
        
        //获取自动归档
        $auto_archive_flag = 2;
        if(!empty($params['auto_archive_flag'])){
            $auto_archive_flag = $params['auto_archive_flag'];
        }
        //获取策略组uuid
        $strategy_group_uuid = "";
        
        //获取GFS保留策略
        $gfsList = array();
        if(!empty($params['gfs_reserved_strategy'])){
            foreach ($params['gfs_reserved_strategy'] as $each){
                $gfsList[] = array(
                    //获取保留类型
                    'level1_type' => $each['gfs_reserved_type'],
                    //获取开始时间
                    'level2_type' => $each['gfs_reserved_start'],
                    //获取gfs保留个数
                    'retention_num' => $each['gfs_reserved_value'],
                    
                );
            }
        }
        
        //组装数据
        $dataList = array(
            'reserveStrategy' =>array(
                'strategy_type' => $strategy_type,
                'strategy_mode' => $params['strategyMode'] ? $params['strategyMode'] : 0,
                'number' => $number,
                'auto_archive_flag' => $auto_archive_flag,
                'strategy_group_uuid' => $strategy_group_uuid,
            ),
            'gfs' => $gfsList,
        );
        
        return $dataList;
        
    }
    
    
    
    
    
    
    
}
