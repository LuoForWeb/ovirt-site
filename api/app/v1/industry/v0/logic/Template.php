<?php

namespace app\v1\industry\v0\logic;

use app\v1\common\logic\Base;

/**
 * note          行业合规模板 逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/31 18:26
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Template extends Base
{
    /**
     * 新建/编辑模板
     * @param array $params 请求参数
     * @return array
     */
    public function updateTemplate(array $params = [])
    {

        $user = xphp_get_user_info();
        // 组装下详情和概要的结构
        $item = $this->makeItem($params['item']);
        $status = xphp_get_config('industry', 'REPORT_STATUS');
        // 公共的数据
        $data = [
            $params['name'],
            $params['remark'],
            $user['userUuid'],
            date(xphp_get_config('special', 'dateformat')),
            $status['ARCHIVED'],
            $params['virtus_num'] ?? 1,
            $params['document_num'],
            json_encode($params['logo']),
            json_encode($params['report_water']),
            json_encode($params['screen_water']),
            json_encode($params['email_notice']),
            $params['item_plan'],
            $params['item_result'],
            json_encode($item['summary']),
            json_encode($item['description']),
            $params['width'],
            json_encode($params['document_custom']),
            $params['approval_uuid'],
            $params['plan_uuid'],
        ];
        // 新建模板
        if (empty($params['templates_uuid'])) {
            $sql = "insert into industry_temp(
                          temp_uuid,`name`,remark,user_uuid,create_time,status,virus_num,
                          document_num,logo,report_water,screen_water,email_notice,
                          item_plan,item_result,summary,description,item_width,document_custom,approval_uuid
                          ,plan_uuid,update_time
                          )values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $data = array_merge([xphp_uuid()], $data, [date(xphp_get_config('special', 'dateformat'))]);

            $result = $this->dbExec($sql, $data);

            $item = [$params['name'], xphp_get_lang('WEB_PLATFORM_INDUSTRY_TEMPLATE_ADD')];
        } else {
            // 编辑模板

            // 权限判断
            $info = $this->dbSelect(
                'select user_uuid from industry_temp where temp_uuid = ?',
                [$params['templates_uuid']]
            );
            $this->checkAuthByUserUuid($info[0]['user_uuid'], '');

            $sql = "UPDATE industry_temp SET
                   `name` = ?, remark = ?, user_uuid = ?, update_time = ?,
                   status = ?, virus_num = ?, document_num = ?,
                   logo = ?, report_water = ?, screen_water = ?,
                   email_notice = ?, item_plan = ?, item_result = ?,
                   summary = ?, description = ?,item_width = ?,document_custom = ?,approval_uuid = ?,plan_uuid = ?
                WHERE temp_uuid = ? ";
            $data = array_merge($data, [$params['templates_uuid']]);

            $result = $this->dbExec($sql, $data);

            $item = [$params['name'], xphp_get_lang('UI_PLATFORM_INDUSTRY_TEMPLATE_EDIT')];
        }

        // 写日志
        $this->systemLog('PT_INDUSTRY_TEMPLATE_OPERATE', $item);

        if ($result) {
            return [
                'code' => 0,
                'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        }
        return [
            'code' => 1,
            'msg' => xphp_get_lang('WEB_PUBLIC_FAILURE')
        ];
    }

    /**
     * 模板详情
     * @param string $uuid    模板uuid
     * @param string $jobUuid 修改任务的时候可能携带的任务uuid
     * @return array
     */
    public function viewTemplate(string $uuid, string $jobUuid = '')
    {
        $data = $this->dbSelect('select * from industry_temp where temp_uuid = ?', [$uuid], \PDO::FETCH_ASSOC);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_TEMPLATE_NOT_EXIST')
            ];
        }

        $info = $data[0];
        if (!empty($jobUuid)) {
            // 需要查询出报告设置的模板信息
            $data = $this->dbSelect(
                'select * from industry_report where temp_uuid = ? and task_uuid = ? limit 1',
                [$uuid, $jobUuid]
            );
            if (!empty($data)) {
                $info = array_merge($info, $data[0]);
                // 以报告里面的为准，不能粗暴的以 array_merge 因为有二维数组里面嵌套二维数组的情况
                $info['summary'] = $data[0]['summary'];
            }
        }
        // 处理下一些结构
        $info['report_water'] = json_decode($info['report_water'], true);
        $info['screen_water'] = json_decode($info['screen_water'], true);
        $info['logo'] = json_decode($info['logo'], true);
        $info['email_notice'] = json_decode($info['email_notice'], true);
        $info['summary'] = json_decode($info['summary'], true);
        $info['description'] = json_decode($info['description'], true);
        $info['document_custom'] = json_decode($info['document_custom'], true);

        // 写日志
        $item = [$info['name'], xphp_get_lang('WEB_PLATFORM_INDUSTRY_TEMPLATE_LOOK')];
        $this->systemLog('PT_INDUSTRY_TEMPLATE_OPERATE', $item);
        return [
            'code' => 0,
            'msg' => $info
        ];
    }

    /**
     * 组合详情和概要的信息 report那边会调用
     * @param array $params 参数数组
     * @return array
     */
    public function makeItem(array $params = [])
    {
        $summary = $description = []; // 初始化详情和概要
        foreach ($params as $item) {
            if ($item['type'] == 2) {
                $description[$item['name']] = [
                    'show' => true,
                    'position' => $item['position'],
                    'uuid' => $item['uuid'] ?? xphp_uuid()
                ];
            } else {
                // 考虑多个的情况
                if (in_array($item['name'], ['item_field', 'item_textarea', 'item_datetime'])) {
                    // 自定义字段、文本和时间控件
                    $summary[$item['name']]['show'] = true;
                    $arr = $summary[$item['name']]['list'] ?? [];
                    $list = [
                        'name' => $item['value']['name'],
                        'en_name' => $item['value']['en_name'] ?? '',
                        'value' => $item['value']['val'] ?? '',
                        'en_value' => $item['value']['en_val'] ?? '',
                        'position' => $item['position'],
                        'uuid' => $item['uuid'] ?? xphp_uuid()
                    ];
                    $summary[$item['name']]['list'] = array_merge($arr, [$list]);
                } else {
                    $summary[$item['name']] = [
                        'show' => true,
                        'position' => $item['position'],
                        'value' => $item['value']['val'],
                        'en_value' => $item['value']['en_val'],
                        'uuid' => $item['uuid'] ?? xphp_uuid()
                    ];
                }
            }
        }
        return [
            'summary' => $summary,
            'description' => $description,
        ];
    }

    /**
     * 获取模板列表
     * @param array $params 参数数组
     * @return array
     */
    public function getTemplateList($params)
    {
        $offset = intval($params['offset']);
        $limit = intval($params['limit']);
        $sort = $params['sort'];
        $order = $params['order'];
        $search = v1_escape_wildcard($params['search']);

        $sql = "select it.temp_uuid, it.name, it.remark, it.user_uuid,it.description,
                        it.create_time, it.status, it.update_time, bu.user_name
                from industry_temp it
                left join bd_user bu on it.user_uuid = bu.user_uuid";
        $sqlCount = "select count(*) as total from industry_temp it";

        $where = '';
        if (!empty($search)) {
            $where .= " where it.name like '%{$search}%'";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users('');
            $sqlNew = " it.user_uuid in ({$userUuidSql}) ";
            $where .= (empty($where) ? ' where ' : ' and ') . $sqlNew;
        }

        $limit = " limit {$offset}, {$limit}";

        if (!empty($sort)) {
            if ($sort == 'num') {
                $sort = 'id';
            }
            $order = " order by it.{$sort} {$order}";
        }

        $data = $this->dbSelect($sql . $where . $order . $limit);
        $count = $this->dbSelect($sqlCount . $where);

        $recode = [];
        foreach ($data as $d) {
            // 判断下当前是否选择了文件比对
            $description = json_decode($d['description'], true);
            $recode[] = array(
                'num' => ++$offset,
                'temp_uuid' => $d['temp_uuid'],
                'name' => $d['name'],
                'remark' => $d['remark'] == '' ? '--' : $d['remark'],
                'user_name' => $d['user_name'],
                'user_uuid' => $d['user_uuid'],
                'create_time' => $d['create_time'],
                'update_time' => $d['update_time'] ?? '--',
                'status' => $d['status'],
                'show_files' => !empty($description['item_document']['show'])
            );
        }

        return [
            'rows' => $recode,
            'total' => $count[0]['total'],
        ];
    }

    /**
     * 删除模板列表
     * @param array $params 参数数组
     * @return boolean
     */
    public function delTemplate($params)
    {
        $uuid = $params['uuids'];
        $uuidList = "('" . implode("','", $uuid) . "')";
        // 权限判断
        $info = $this->dbSelect(
            "select user_uuid from industry_temp where temp_uuid in {$uuidList}"
        );
        $this->checkAuthByUserUuid(implode(',', array_column($info, 'user_uuid')), '');

        // 先判断是否已经被使用了
        $sql = "select temp_uuid from industry_report where temp_uuid in {$uuidList}";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            return false;
        }

        // 写日志
        $sql = "select `name` from industry_temp where temp_uuid in {$uuidList}";
        $temp = $this->dbSelect($sql);
        $item = [implode(',', array_column($temp, 'name')), xphp_get_lang('WEB_PLATFORM_INDUSTRY_TEMPLATE_DEL')];
        $this->systemLog('PT_INDUSTRY_TEMPLATE_OPERATE', $item);

        $sql = "delete from industry_temp where temp_uuid in {$uuidList}";
        return $this->dbExec($sql);
    }

    /**
     * 启用模板列表
     * @param array $params 参数数组
     * @return boolean
     */
    public function unlockTemplate($params)
    {
        $uuid = "('" . implode("','", $params['uuids']) . "')";

        // 权限判断
        $info = $this->dbSelect(
            "select user_uuid from industry_temp where temp_uuid in {$uuid}"
        );
        $this->checkAuthByUserUuid(implode(',', array_column($info, 'user_uuid')), '');

        $flag = xphp_get_config('app', 'FLAG');
        $sql = "update industry_temp set status = ? where temp_uuid in {$uuid}";

        $sqlParams = [$flag['SET']];
        $result = $this->dbExec($sql, $sqlParams);

        // 写日志
        $sql = "select `name` from industry_temp where temp_uuid in {$uuid}";
        $temp = $this->dbSelect($sql);
        $item = [implode(',', array_column($temp, 'name')), xphp_get_lang('WEB_PLATFORM_INDUSTRY_TEMPLATE_ENABLE')];
        $this->systemLog('PT_INDUSTRY_TEMPLATE_OPERATE', $item);
        return $result;
    }

    /**
     * 禁用模板列表
     * @param array $params 参数数组
     * @return boolean
     */
    public function lockTemplate($params)
    {
        $uuid = "('" . implode("','", $params['uuids']) . "')";

        // 权限判断
        $info = $this->dbSelect(
            "select user_uuid from industry_temp where temp_uuid in {$uuid}"
        );
        $this->checkAuthByUserUuid(implode(',', array_column($info, 'user_uuid')), '');

        $flag = xphp_get_config('app', 'FLAG');
        $sql = "update industry_temp set status = ? where temp_uuid in {$uuid}";

        $sqlParams = [$flag['UNSET']];
        $result = $this->dbExec($sql, $sqlParams);
        // 写日志
        $sql = "select `name` from industry_temp where temp_uuid in {$uuid}";
        $temp = $this->dbSelect($sql);
        $item = [implode(',', array_column($temp, 'name')), xphp_get_lang('WEB_PLATFORM_INDUSTRY_TEMPLATE_DISABLED')];
        $this->systemLog('PT_INDUSTRY_TEMPLATE_OPERATE', $item);
        return $result;
    }
}
