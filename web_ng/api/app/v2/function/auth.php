<?php

/**
 * v2 版本 获取用户权限函数文件
 * 以  v2_auth_ 开头，单词之间用 _ 下划线隔开，单词用小写
 */

/**
 * 获取当前用户是否是超级管理员/三权操作员
 * @param string $userUuid usruuid
 * @return array
 */
function v2_auth_is_admin(string $userUuid = ''): array
{
    $return = [
        'is_admin' => false, // 是否是超级管理员
        'global_observer' => false, // 是否是全局观察者
        'global_read' => false, // 是否是全局观察者只读
        'global_write' => false, // 是否是全局观察者查看并操作
        'is_three_operator' => false, // 是否是三权操作员
    ];
    // 1、首先判断用户的 user_level 是否是1 （超级管理员）
    if (!empty($userUuid)) {
        $userInfo = dbSelect('select user_level from bd_user where user_uuid = ?', [$userUuid]);
        $userHander = new app\v2\user\v0\logic\User();
        $permission = $userHander->pGetUserAllPermission($userUuid, true);
        $user = [
            'userLevel' => $userInfo[0]['user_level'],
            'permission' => $permission
        ];
    } else {
        $user = xphp_get_user_info();
    }

    if ($user['userLevel'] == 1) {
        $return['is_admin'] = true;
        return $return;
    }
    // 2、判断用户是否是全局观察者
    if (in_array('global_observer', $user['permission'])) {
        $return['global_observer'] = true;
        $return['global_read'] = in_array('global_read', $user['permission']);
        $return['global_write'] = in_array('global_write', $user['permission']);
        return $return;
    }

    // 是否是三权操作员
    if ($user['userLevel'] == 5) {
        $return['is_three_operator'] = true;
    }
    return $return;
}

/**
 * 获取当前用户关联管理的用户sql（包括关联管理者和用户组管理员(待定)）
 * @param string $auth     管理用户资源标识
 * @param string $back     返回格式
 *                         array或sql语句
 * @param string $userUuid useruuid
 * @return array|string
 */
function v2_auth_manage_user(string $auth, $back = 'array', $userUuid = '')
{

    if (!empty($userUuid)) {
        $uuid = $userUuid;
    } else {
        $user = xphp_get_user_info();
        $uuid = $user['userUuid'];
    }

    // 根据uuid和auth查询关联的用户uuid集合
    $sql = "select user_uuid from bd_user
                where (manager_uuid = '{$uuid}' and manager_auth like '%,{$auth},%')
                   or user_uuid = '{$uuid}'";
    if ($back == 'sql') {
        $return = '(' . $sql . ')';
    } else {
        $list = dbSelect($sql);
        $return = !empty($list) ? array_column($list, 'user_uuid') : [];
    }

    return $return;
}

/**
 * 判断当前用户是否需要关联权限-查看
 * @return bool
 */
function v2_auth_check(): bool
{
    $checkAuth = v2_auth_is_admin();
    if (
        (empty($checkAuth['is_admin']) && empty($checkAuth['global_observer']))
        || !empty($checkAuth['is_three_operator'])
    ) {
        // 三权模式下的操作员只能查看分配的存储列表
        // 不是超级管理员也不是全局观察者，也只能看到分配的资源
        return true;
    }
    return false;
}

/**
 * 判断当前用户是否需要关联权限-操作
 * @param string $userUuid userUuid
 * @return bool
 */
function v2_auth_checks(string $userUuid = ''): bool
{
    $checkAuth = v2_auth_is_admin($userUuid);
    if (
        (!xphp_three_powers() && empty($checkAuth['is_admin']) && empty($checkAuth['global_write']))
        || (xphp_three_powers() && !empty($checkAuth['is_three_operator']))
    ) {
        // 1.三权操作员只能管理分配的存储列表
        // 2.不是超级管理员也不是全局观察者-查看并操作，也只能管理分配的资源
        return true;
    }
    return false;
}

/**
 * 判断当前用户是否需要关联权限-查看
 * @param string $userUuid userUuid
 * @return bool
 */
function v2_auth_need_check_look(string $userUuid = ''): bool
{
    $checkAuth = v2_auth_is_admin($userUuid);
    if (
        (!xphp_three_powers() && empty($checkAuth['is_admin']) && empty($checkAuth['global_observer']))
        || (xphp_three_powers() && !empty($checkAuth['is_three_operator']))
    ) {
        // 1.不是超级管理员也不是全局观察者，只能看到分配的资源
        // 2.三权操作员只能查看分配的存储列表

        return true;
    }
    return false;
}

/**
 * 判断当前用户是否需要关联权限-操作
 * @param string $userUuid useruuid
 * @return bool
 */
function v2_auth_need_operation(string $userUuid = ''): bool
{
    $checkAuth = v2_auth_is_admin($userUuid);
    if (
        (!xphp_three_powers() && empty($checkAuth['is_admin']) && empty($checkAuth['global_write']))
        || (xphp_three_powers() && !empty($checkAuth['is_three_operator']))
    ) {
        // 1.三权操作员只能管理分配的存储列表
        // 2.不是超级管理员也不是全局观察者-查看并操作，也只能管理分配的资源
        return true;
    }
    return false;
}

/**
 * 获取当前用户可拥有的资源sql(可分配的资源判断)
 * @param int    $souceType 分配的资源类型标识 详见resource的 RESOURCE_TYPE 枚举
 * @param string $field     如果带入了主表的主键，那么就会直接返回一个完整的where条件，不需要主表再拼一次
 * @param string $auth      权限标识，详见 user的 USER_AUTH 枚举
 * @param int    $type      类型 1查看 2操作
 * @param string $userUuid  useruuid
 * @return string
 */
function v2_auth_get_source_by_type(
    int $souceType,
    string $field = '',
    string $auth = 'resmanagement',
    int $type = 1,
    string $userUuid = ''
) {

    $source = $type == 1 ? '_look' : '_operate';
    // 关联管理用户判断 存储资源 - 查看?操作
    $authUser = v2_auth_manage_user($auth . $source, 'sql', $userUuid);

    // 需要查询出用户所用户的资源
    $authsLogic = new \app\v2\resources\v0\logic\Index();
    return ($authsLogic)->pGetUserAllResourceSql(
        $authUser,
        $souceType,
        'sql',
        $field
    );
}

/**
 * 操作存储权限判断
 * @param string $sourceUuid 资源uuid
 * @param string $auth       权限标识，详见 user的 USER_AUTH 枚举
 * @param int    $souceType  分配的资源类型标识 详见resource的 RESOURCE_TYPE 枚举
 * @param string $userUuid   useruuid
 * @return bool|string
 */
function v2_auth_check_operate(string $sourceUuid, string $auth, int $souceType, string $userUuid = ''): bool
{

    $resourceUuid = v2_auth_get_source_by_type($souceType, '', $auth, 2, $userUuid);
    $idArr = explode(',', $sourceUuid);
    $idArr = array_filter($idArr);
    if (count($idArr) == 1) {
        $sql = "SELECT CASE WHEN '{$idArr[0]}' IN (
                        {$resourceUuid}
                    ) THEN 1 ELSE 0 END AS result";
    } else {
        // 多个
        $subSql = '';
        $i = 0;
        foreach ($idArr as $item) {
            if ($i == 0) {
                $subSql = "SELECT '{$item}' AS uuid ";
            } else {
                $subSql .= "UNION ALL SELECT '{$item}' ";
            }
            $i++;
        }
        $sql = "WITH target_users AS (
                            {$resourceUuid}
                        ),
                        check_list AS (
                            {$subSql}
                        )
                        SELECT 
                            CASE WHEN COUNT(t.uuid) = COUNT(*) THEN 1 ELSE 0 END AS result
                        FROM check_list cl
                        LEFT JOIN target_users t ON cl.uuid = t.uuid";
    }

    $check = dbSelect($sql);

    if (empty($check) || $check[0]['result'] != 1) {
        return false;
    }

    return true;
}

/**
 * 获取当前用户可管理的用户uuid sql
 * @param string $auth     权限标识，详见
 *                         user的 USER_AUTH 枚举
 * @param int    $type     类型
 *                         1查看
 *                         2操作
 * @param string $userUuid useruuid
 * @return string
 */
function v2_auth_get_users(string $auth, int $type = 1, string $userUuid = ''): string
{

    $source = $type == 1 ? '_look' : '_operate';
    // 关联管理用户判断 存储资源 - 查看?操作
    return v2_auth_manage_user($auth . $source, 'sql', $userUuid);
}
