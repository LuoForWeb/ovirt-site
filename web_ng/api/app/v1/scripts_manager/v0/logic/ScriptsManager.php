<?php

namespace app\v1\scripts_manager\v0\logic;

use app\v1\common\logic\Base;

/**
 * note          容器 脚本管理
 * @author       liushuai@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ScriptsManager extends Base
{

    // 获取脚本描述
    public function getScriptDes($script_type = 0){
        switch($script_type){
            case 0:
                return xphp_get_lang('WEB_SCRIPT_NULL_SCRIPT');
                break;
            case 1:
                return "shell".xphp_get_lang('WEB_SCRIPT_SCRIPT');
                break;
            case 2:
                return "bat".xphp_get_lang('WEB_SCRIPT_SCRIPT');
                break;
            case 3:
                return "yaml".xphp_get_lang('WEB_SCRIPT_SCRIPT');
                break;
            case 4:
                return "python2".xphp_get_lang('WEB_SCRIPT_SCRIPT');
                break;
            case 5:
                return "python3".xphp_get_lang('WEB_SCRIPT_SCRIPT');
                break;
            case 6:
                return "SQL".xphp_get_lang('WEB_SCRIPT_SCRIPT');
                break;
            case 7:
                return "PowerShell".xphp_get_lang('WEB_SCRIPT_SCRIPT');
                break;
            case 8:
                return "PowerShell".xphp_get_lang('WEB_SCRIPT_SCRIPT');
                break;
            case 9:
                return "PowerShell".xphp_get_lang('WEB_SCRIPT_SCRIPT');
                break;
            default:
                return xphp_get_lang('WEB_SCRIPT_NULL_SCRIPT');
        }

    }






    /**
     * 获取脚本列表
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/4/14
     * @return unknown
     */
    public function getScript($params = [])
    {
        //起始页
        $offset = $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];
        //获取开始时间和结束时间
        $startTime = $params['start_time'] ?? '';
        $endTime = $params['end_time'] ?? ''; 
        $search = $params['search'];
        //获取脚本列表
        $sql = "select id,script_uuid,script_name,script_type,script_content,script_description,
        create_time,update_time,user_uuid from bd_script";
        $sqlCount = "select count(id) as count from bd_script";
        $sqlParams = array();
        $sqlCountParams =  array();
        if(!empty($search)){
            $sql .= " where script_name like '%".$search."%' or script_description like '%".$search."%' ";
            $sqlCount .= " where script_name like '%".$search."%' or script_description like '%".$search."%' ";
        }
        //如果填了修改时间范围查询
        if(!empty($startTime)  && !empty($endTime)){
            if(empty($search)){
                $sql .= " where update_time between ? and  ? ";
                $sqlCount .= " where update_time between ? and  ? ";
            }else{
                $sql .= " and update_time between ? and  ? ";
                $sqlCount .= " and update_time between ? and  ? ";
            }
            $sqlParams = array_merge($sqlParams,array($startTime,$endTime));
            $sqlCountParams = array_merge($sqlCountParams,array($startTime,$endTime));
        }
        if(is_numeric($offset) && !empty($limit)){
            $sql .= " limit ?, ?"; 
            $sqlParams =  array_merge($sqlParams, array($offset, $limit));
        }
        $count = $this->dbSelect($sqlCount,$sqlCountParams);
        $data = $this->dbSelect($sql,$sqlParams);
        $info = array(
            'rows' => array(),
            'total' => $count[0]['count'],
        );
        //数据为空
        if (empty($count)) {
            return $info;
        }
        foreach ($data as $each) {
            $info['rows'][] = array(
                //获取id
                'id' => $each['id'],
                //获取脚本唯一标识
                'script_uuid' => $each['script_uuid'],
                //获取脚本名称
                'script_name' => $each['script_name'],
                //获取脚本功能描述
                'script_description' => $each['script_description'],
                //获取创建时间
                'create_time' => $each['create_time'],
                //获取修改时间
                'update_time' => $each['update_time'],
                //获取创建用户uuid
                'user_uuid' => $each['user_uuid'],
                //获取脚本内容
                'script_content' => $each['script_content'],
                //脚本类型
                'script_type' => $each['script_type'],
                'script_type_des' =>$this->getScriptDes($each['script_type']),
                
            );
        }
        return $info;
    }

    /**
     * 获取单个脚本详情
     * @param unknown $params
     * @author liushuai@vinchin.com
     * @date 2023/4/14
     *
     */
    public function getScriptThis($params)
    {
        $scriptuuid = $params['scripts_uuid'];
        $sql = "select id,script_uuid,script_name,script_type,script_content,script_description,
create_time,update_time,user_uuid from bd_script where script_uuid = ?";
        $result = $this->dbSelect($sql, array($scriptuuid));
        $info = array();
        if (empty($result)) {
            return $info;
        }
        foreach ($result as $each) {
            $info = array(
                //获取id
                'id' => $each['id'],
                //获取脚本唯一标识
                'script_uuid' => $each['script_uuid'],
                //获取脚本名称
                'script_name' => $each['script_name'],
                //获取脚本功能描述
                'script_description' => $each['script_description'],
                //获取创建时间
                'create_time' => $each['create_time'],
                //获取修改时间
                'update_time' => $each['update_time'],
                //获取创建用户uuid
                'user_uuid' => $each['user_uuid'],
                //获取脚本内容
                'script_content' => $each['script_content'],
                //获取脚本类型
                'script_type' =>$each['script_type'],
                'script_type_des' =>$this->getScriptDes($each['script_type']),
            );
        }
        return $info;
    }

    /**
     * 添加单个脚本
     * @param unknown $params
     * @author liushuai@vinchin.com
     * @date 2023/4/14
     */
    public function addScript($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_SCRIPT_ADD_SCRIPT_SUCCESS'),
            'data' => array(),
        );
        //获取脚本名称
        $scriptname = $params['script_name'];
        //查询名称是否有重复的
        $name_sql = "select count(*) as count from bd_script where script_name = ?";
        $name_res = $this->dbSelect($name_sql,array($scriptname));
        $count_name = $name_res[0]['count'];
        if($count_name > 0){
            $resultInfo['success'] = false;
            $resultInfo['message'] = xphp_get_lang('WEB_SCRIPT_SCRIPT_NAME_REPEAT');
            return $resultInfo;
        }
        //获取脚本内容
        $scriptcontent = htmlspecialchars_decode($params['script_content']);
        //获取脚本功能描述
        $scriptdescription = $params['script_description'];
        //获取脚本类型
        $scripttype = $params['script_type'];



//        var_dump("开始2");
        //生成脚本uuid
        $scriptuuid = xphp_uuid();
        //创建时间
        $createtime = date('Y-m-d H:i:s');
        //更新时间
        $updatetime = date('Y-m-d H:i:s');
        //用户uuid
        $useruuid = xphp_get_user_info()['userUuid'];
//        var_dump($useruuid);
//        var_dump("这是");
//        return;
        $sql = "insert into bd_script(script_uuid,script_name,
script_type,script_content,script_description,create_time,update_time,
user_uuid) values(?,?,?,?,?,?,?,?)";
        $sqlparams = array($scriptuuid,$scriptname,$scripttype,
            $scriptcontent,$scriptdescription,$createtime,$updatetime,
            $useruuid
        );
        $result = $this->dbExec($sql, $sqlparams);
        if(!$result){
            $resultInfo['success'] = false;
            $resultInfo['message'] = xphp_get_lang('WEB_SCRIPT_ADD_SCRIPT_FAILURE');
            return $resultInfo;
        }
        return $resultInfo;
    }


    /**
     * 修改单个集群
     * @param unknown $params
     * @author liushuai@vinchin.com
     * @date 2023/4/14
     */
    public function editScriptThis($params)
    {

        //获取脚本uuid
        $scripuuid = $params['script_uuid'];
        //获取脚本名称
        $scriptname = $params['script_name'];
        //获取脚本内容
        $scriptcontent = htmlspecialchars_decode($params['script_content']);
        //获取脚本类型
        $scripttype = $params['script_type'];
        //获取脚本功能描述
        $scriptdescription = $params['script_description'];
        //更新时间
        $updatetime = date('Y-m-d H:i:s');

        $sql = "update bd_script set script_type = ?, script_name = ?,script_content = ?, script_description = ?,update_time = ? where script_uuid = ?";
        $sqlparams = array($scripttype, $scriptname,$scriptcontent,$scriptdescription,$updatetime,$scripuuid);
        $result = $this->dbExec($sql, $sqlparams);
        return $result;
    }





    /**
     * 删除单个或多个集群
     * @param unknown $params
     * @author liushuai@vinchin.com
     * @date 2023/4/14
     */
    public function deleteScript($params)
    {
        $scriptuuidlist = $params['script_uuid'];
        $stringlist = implode("','", $scriptuuidlist);
        //执行SQL语句
        $sql = "delete from bd_script where script_uuid in ('".$stringlist."')";
        $result = $this->dbExec($sql);
        return $result;
    }
}
