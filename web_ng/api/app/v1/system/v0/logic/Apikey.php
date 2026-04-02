<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcodePrivate;
use xphp\Curl;
use xphp\Email;

/**
 * note          生成apikey管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/14 14:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Apikey extends Base
{


    /**
     * 生成apikey
     * @param array $params 数据
     * @return void
     */
    public function createApikey($params = [])
    {
        //获取当前用户信息
        $userInfo = xphp_get_user_info();
        $sql = "select password from bd_user where user_uuid = ? ";
        $data = $this->dbSelect($sql,array($userInfo['userUuid']));
        //生成apikey
        $apikey = md5('VINCHIN'.'='.$userInfo['userName'].'='.$data[0]['password'].'='.time());
        //apikey存入数据库
        $sql = "insert bd_apikey (apikey,create_time,host,remark) values (?,?,?,?)";
        $result = $this->dbQuery($sql,array($apikey,date('Y-m-d H:i:s'), '', ''));
        if ($result) {
            $this->systemLog('SYSTEM_CREATE_APIKEY_SUCCESS');
            return [
                'msg' => "",
                'data' => [
                    'apikey' => $apikey
                ]
            ];
        } else {
            return false;
        }
    }

    /**
     * 获取apikey列表
     * @param array $params 数据
     * @return object  apikey列表对象
     */
    public function getApikeyList($params = [])
    {
        $sql = "select id, apikey, unix_timestamp(create_time) create_time, lock_flag from bd_apikey ";
        $sqlCount = "select count(id) as total from bd_apikey ";
        $sqlParams = $sqlCountParams = [];
        if ((!empty($params['sort']) && in_array(strtolower($params['order']), ['asc', 'desc']))) {
            // 排序
            $sql .= " order by " . $params['sort'] . ' ' . $params['order'];
        }
        $sql .= ' limit ? , ? ';
        $sqlParams = array_merge($sqlParams, array($params['offset'], $params['limit']));

        $datas = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $records = $rows = [];

        $num = intval($count[0]['total']);

        foreach ($datas as $d) {

            $rows[] = array(
                'id' => intval($d['id']),
                'apikey' => $d['apikey'],
                'create_time' => date('Y-m-d H:i:s', intval($d['create_time'])),
                'lock_flag' => intval($d['lock_flag']),
                'lock_flag_des' => xphp_get_desc('Pf', 'LOCK_FLAG_DES')[intval($d['lock_flag'])]
            );
        }

        $records['rows'] = $rows;
        $records['total'] = $num;

        return $records;
    }

    /**
     * 删除apikey
     * @param array $params 数据
     * @return boolean
     */
    public function deleteApikey($params = [])
    {
        //
        if (!empty($params['apikey_ids'])) {
            $ids = '(' . implode(',', $params['apikey_ids']) . ')';
            // 批量
            $result = $this->dbExec(
                "delete from bd_apikey where id in " . $ids,
                []
            );
        } else {
            $result = $this->dbExec(
                "delete from bd_apikey where id = ? ",
                [$params['apikey_uuid']]
            );
        }

        return $result;
    }

    /**
     * 禁用apikey
     * @param array $params 数据
     * @return boolean
     */
    public function lockApikey($params = [])
    {
        //
        if (!empty($params['apikey_ids'])) {
            $ids = '(' . implode(',', $params['apikey_ids']) . ')';
            // 批量
            $result = $this->dbExec(
                "update bd_apikey set lock_flag = ? where id in " . $ids,
                [xphp_get_config('app')['FLAG']['UNSET']]
            );
        } else {
            $result = $this->dbExec(
                "update bd_apikey set lock_flag = ? where id = ? ",
                [xphp_get_config('app')['FLAG']['UNSET'], $params['apikey_uuid']]
            );
        }

        return $result;
    }

    /**
     * 启用apikey
     * @param array $params 数据
     * @return boolean
     */
    public function unlockApikey($params = [])
    {
        //
        if (!empty($params['apikey_ids'])) {
            $ids = '(' . implode(',', $params['apikey_ids']) . ')';
            // 批量
            $result = $this->dbExec(
                "update bd_apikey set lock_flag = ? where id in " . $ids,
                [xphp_get_config('app')['FLAG']['SET']]
            );
        } else {
            $result = $this->dbExec(
                "update bd_apikey set lock_flag = ? where id = ? ",
                [xphp_get_config('app')['FLAG']['SET'], $params['apikey_uuid']]
            );
        }

        return $result;
    }


}
