<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\Log;

class WBList extends Base
{

    /**
     * 获取黑白名单列表
     * @param $params
     * @return array
     */
    public function getWBList($params = [])
    {
        if(!empty($params['list_uuid'])){
            //获取某个list的详细信息
            $listUuid = $params['list_uuid'];
            $sql = "select start_ip,end_ip,start_time,end_time,description from bd_access_restrict where uuid = ?";
            $data = $this->dbSelect($sql,array($listUuid));
            $records = array();
            $records = array(
                'start_ip' => $data[0]['start_ip'],
                'end_ip' => $data[0]['end_ip'],
                'start_time' => $data[0]['start_time'],
                'end_time' => $data[0]['end_time'],
                'description' => $data[0]['description'],
                'permanent_access_flag' => v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['UNSET']),
            );
            if(!!$data[0]['start_time']){
                // 开启永久时效
                $records['permanent_access_flag'] = v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['UNSET']);
            }else{
                $records['permanent_access_flag'] = v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['SET']);
            }
            return $records;

        }else {
            $listType = $params['list_type'];
            $sql = "select *,COUNT(id) OVER () as total from bd_access_restrict where list_type = ? ";
            $sqlParams = array($listType);
            if($params['search']){
                $sql .= "and (start_ip like '%" .$params['search'] ."%' or end_ip like '%" . $params['search'] ."%')";
            }
            $checkAuth = v1_auth_is_admin();
            if(v1_auth_need_check_look()){
                // 三权模式下的操作员只能查看分配的存储列表
                // 不是超级管理员也不是全局观察者，也只能看到分配的资源
                $userUuidSql = v1_auth_get_users('');
                $sqlNew = " and bd_access_restrict.create_user_uuid in ({$userUuidSql}) ";
                $sql .= $sqlNew;
            }
            $data = $this->dbSelect($sql, $sqlParams);
            $records = $rows = [];
            $num = intval($data[0]['total']);
            $i = 0;
            foreach ($data as $item) {
                $i++;
                $rows[] = array(
                    'list_uuid' => $item['uuid'],
                    'num' => $params['offset'] + $i,
                    'ip_address' => $item['start_ip'] == $item['end_ip'] ? $item['start_ip'] : $item['start_ip'] . '-' . $item['end_ip'],
                    'list_type' => $item['list_type'],
                    'valid_time' => $item['start_time'] ? $item['start_time'] . '-' . $item['end_time'] : xphp_get_lang('WEB_SYSTEM_PERMANENT'),
                    'description' => $item['description'],
                    'lock_flag' => v1_parse_flag_to_bool($item['lock_flag']),
                    'create_user_name' => $item['create_user_name'],
                    'create_user_uuid' => $item['create_user_uuid'],
                    'op_list' => array(
                        'START' => 1,       //启用
                        'FORBID' => 2,      //禁用
                        'EDIT' => 3,        //修改
                        'DELETE' => 4,     //删除
                    ),);
            };
            $records['rows'] = $rows;
            $records['total'] = $num;
            return $records;
        }
    }



    /**
     * 添加黑白名单
     * @param $params
     * @return array
     */
    public function addWBList($params = [])
    {
        // 获取当前用户信息
        $userInfo = xphp_get_user_info();
        $createUserUuid = $userInfo['userUuid'];
        $createUserName = $userInfo['userName'];
        //生成一个UUID
        $listUUID = xphp_uuid();
        //从js获取list_uuid、list_type、start_ip、end_ip、list_type、start_time、end_time、description
        $startIP =  $params['start_ip'];
        $endIP =  $params['end_ip'];
        $listType = $params['list_type'];
        // 永久时效
        if(!$params['permanent_access_flag']){
            $timeRange = $params['time_range'];
            $dateRangeArray = explode(" - ",$timeRange);
            $startTime = $dateRangeArray[0];
            $endTime = $dateRangeArray[1];
        }else{
            $startTime = null;
            $endTime = null;
        }
        $permanentAccessFlag = v1_parse_bool_to_flag($params['permanent_access_flag']);

        $description = $params['description'];
        $lockFlag = xphp_get_config('app')['FLAG']['SET'];


        // 更新数据库
        $sql = "insert bd_access_restrict (uuid, start_ip, end_ip, list_type, start_time, end_time, description, lock_flag, create_user_name, create_user_uuid, permanent_access_flag) 
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array($listUUID, $startIP, $endIP, $listType, $startTime, $endTime, $description, $lockFlag, $createUserName, $createUserUuid, $permanentAccessFlag);
        $this->dbBeginTransaction();    // 开始事务
        $result = $this->dbQuery($sql, $sqlParams);

        if($result){
            $this->dbCommit();
//            $this->systemLog('SYSTEM_USER_ADD_SUCCESS', array($params['username']));   写入日志
            return [
                'msg' => xphp_get_lang(xphp_get_lang('WEB_SYSTEM_ADD_LIST_SUCCESS')),
            ];
        }
        else{
            $this->dbRollBack();
            return [
                'msg' => xphp_get_lang(xphp_get_lang('WEB_SYSTEM_ADD_LIST_FAIL')),
            ];
        }
    }

    /**
     * 修改名单(时效/备注)
     */
    public function editWBList($params = [])
    {
        $startIp = $params['start_ip'];
        $endIp = $params['end_ip'];
        $listUuid = $params['list_uuid'];
        // 永久时效
        if(!$params['permanent_access_flag']){
            $timeRange = $params['time_range'];
            $dateRangeArray = explode(" - ",$timeRange);
            $startTime = $dateRangeArray[0];
            $endTime = $dateRangeArray[1];
        }else{
            $startTime = null;
            $endTime = null;
        }
        $permanentAccessFlag = v1_parse_bool_to_flag($params['permanent_access_flag']);
        $description = $params['description'];
        $sql = "update bd_access_restrict set start_ip = ?, end_ip = ?, start_time = ?, end_time = ?, description = ?,permanent_access_flag = ? where uuid = ?";
        $result = $this->dbExec($sql,array($startIp,$endIp,$startTime,$endTime,$description,$permanentAccessFlag,$listUuid));
        return $result;
    }

    /**
     * 删除名单
     * @return bool
     */
    public function deleteWBlist($params = [])
    {
        if(!empty($params['list_uuids'])){
            // 批量
            foreach ($params['list_uuids'] as &$value) {
                $value = "'" . $value . "'";
            }
            $list_uuids = '(' . implode(',', $params['list_uuids']) . ')';
            $result = $this->dbExec("delete from bd_access_restrict where uuid in " . $list_uuids, []);
        }else{
            $result = $this->dbExec("delete from bd_access_restrict where uuid = ?", [$params['list_uuid']]);
        }
        return $result;
    }

    /**
     * 锁定名单
     * @return bool
     */
    public function lockWBList($params = [])
    {
        if(!empty($params['list_uuids'])){
            // 批量
            foreach ($params['list_uuids'] as &$value) {
                $value = "'" . $value . "'";
            }
            $list_uuids = '(' . implode(',', $params['list_uuids']) . ')';
            $result = $this->dbExec("update bd_access_restrict set lock_flag = ? where uuid in " . $list_uuids, [xphp_get_config('app')['FLAG']['UNSET']]);
        }else{
            $result = $this->dbExec("update bd_access_restrict set lock_flag = ? where uuid = ? ",[xphp_get_config('app')['FLAG']['UNSET'], $params['list_uuid']]);
        }
        return array(
            'result' => $result
        );
    }

    /**
     * 启用名单
     * @return bool
     */
    public function unlockWBList($params = [])
    {
        if(!empty($params['list_uuids'])){
            // 批量
            foreach ($params['list_uuids'] as &$value) {
                $value = "'" . $value . "'";
            }
            $list_uuids = '(' . implode(',', $params['list_uuids']) . ')';
            $result = $this->dbExec("update bd_access_restrict set lock_flag = ? where uuid in " . $list_uuids, [xphp_get_config('app')['FLAG']['SET']]);
        }else{
            $result = $this->dbExec("update bd_access_restrict set lock_flag = ? where uuid = ? " , [xphp_get_config('app')['FLAG']['SET'], $params['list_uuid']]);
        }
        return array(
            'result' => $result
        );
    }

    /**
     * 添加IP时候确保没重复
     */
    public function compareList($params = [])
    {
        $startIP =  $params['start_ip'];
        $endIP =  $params['end_ip'];
        $modifyUuid = $params['modify_uuid'];
        if (trim($startIP) == trim($endIP)){
            $sql = "select start_ip from bd_access_restrict where start_ip = end_ip";
            if(!empty($modifyUuid)){
                $sql .= " and uuid != '" . $modifyUuid . "'";
            }
            $data = $this->dbSelect($sql);
            foreach ($data as $d){
                if(trim($d['start_ip']) == trim($startIP)){
                    return  false;
                }
            }
        }else {
            $sql = "select start_ip, end_ip from bd_access_restrict ";
            if(!empty($modifyUuid)){
                $sql .= " and uuid != '" . $modifyUuid . "'";
            }
            $data = $this->dbSelect($sql);
            foreach ($data as $d){
                if(trim($d['start_ip']) == trim($startIP) && trim($d['end_ip']) == trim($endIP)){
                    return  false;
                }
            }
        }

        return true;
    }

}