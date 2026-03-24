<?php
/*
 * @Author: ChengJiaFu
 * @Date: 2026-03-19 17:35:24
 * @Description: 报表Logic
 * @version: 1.0
 */

namespace app\v2\report\v0\logic;

use app\v2\common\logic\Base;
use app\v2\common\logic\JobInfo as JobInfos;
use xphp\Xpdf;
use app\v2\job\v0\logic\JobInfo;
use app\v2\homepage\v0\logic\homePageInfo;
use app\v2\common\logic\Report as ReportHandler;
use app\v2\vm\v0\logic\VmJobInfo;
use app\v2\resources\v0\logic\Node as NodeHandler;

class Report extends Base
{
    // <----------------------------- BEGIN REPORT TREE TABLE LOGIC ------------------------------------------------------>

    /**
     * 获取报表树
     * @param mixed $params
     * @return array{children: bool, id: mixed, parent: mixed, text: mixed, type: string[]}
     */
    public function getReportTree($params)
    {
        $parentId = $params['id'] ?? '#';
        $search = trim($params['search'] ?? '');
        $treeNodes = [];

        if ($search !== '') { // 搜索报表或文件夹
            return $this->searchReportTreeNodes($search);
        }

        if ($parentId === '#') { // 初始化加载
            // 创建虚拟根节点：所有报表
            $treeNodes[] = [
                'id' => 'root',
                'parent' => '#',
                'text' => '所有报表',
                'type' => 'folder',
                'state' => ['opened' => true]
            ];

            // 查询其子节点（prent_uuid IS NULL 的所有文件夹）
            $sql = "SELECT 
                        g.group_uuid, g.group_name, g.default_template_flag, 
                        (EXISTS (SELECT 1 FROM bd_report_group WHERE parent_uuid = g.group_uuid) OR 
                        EXISTS (SELECT 1 FROM bd_report WHERE group_uuid = g.group_uuid)) as has_children 
                    FROM 
                        bd_report_group g 
                    WHERE 
                        g.parent_uuid IS NULL";
            $rootGroups = $this->dbSelect($sql, []);

            foreach ($rootGroups as $group) {
                $node = [
                    'id' => $group['group_uuid'],
                    'parent' => 'root',
                    'text' => $group['group_name'],
                    'type' => 'folder',
                    'children' => $group['has_children'] === 1
                ];

                // 如果是默认模板文件夹，则默认展开
                if (!empty($group['default_template_flag']) && $group['default_template_flag'] == 1) {
                    $node['state'] = ['opened' => true];
                    $node['templateFlag'] = true;
                } else {
                    $node['templateFlag'] = false;
                }

                $treeNodes[] = $node;
            }

            // 查询根节点下的自定义报表 (group_uuid is null and default_template_flag = 2)
            $reportSql = "SELECT template_uuid, template_name, default_template_flag, template_type, sub_type, sort_order FROM bd_report WHERE (group_uuid IS NULL OR group_uuid = '') AND default_template_flag = 2";
            $rootReports = $this->dbSelect($reportSql, []);

            foreach ($rootReports as $report) {
                $treeNodes[] = [
                    'id' => $report['template_uuid'],
                    'parent' => 'root',
                    'text' => $report['template_name'],
                    'type' => 'file',
                    'templateFlag' => false, // 自定义报表
                    'templateType' => $report['template_type'],
                    'subType' => $report['sub_type'],
                    'sortOrder' => $report['sort_order'],
                    'children' => false
                ];
            }

            return $treeNodes;
        }

        // 查询子文件夹
        $groupSql = "SELECT 
                        g.group_uuid, g.group_name, g.default_template_flag, 
                        (EXISTS (SELECT 1 FROM bd_report_group WHERE parent_uuid = g.group_uuid) OR 
                        EXISTS (SELECT 1 FROM bd_report WHERE group_uuid = g.group_uuid)) as has_children 
                    FROM 
                        bd_report_group g 
                    WHERE 
                        g.parent_uuid = ?";
        $childGroups = $this->dbSelect($groupSql, [$parentId]);

        foreach ($childGroups as $group) {
            $node = [
                'id' => $group['group_uuid'],
                'parent' => $parentId,
                'text' => $group['group_name'],
                'type' => 'folder',
                'templateFlag' => !empty($group['default_template_flag']) && $group['default_template_flag'] === 1,
                'children' => $group['has_children'] === 1
            ];

            $treeNodes[] = $node;
        }

        // 查询子报表
        $reportSql = "SELECT template_uuid, template_name, default_template_flag, template_type, sub_type, sort_order FROM bd_report WHERE group_uuid = ?";
        $reports = $this->dbSelect($reportSql, [$parentId]);

        foreach ($reports as $report) {
            $treeNodes[] = [
                'id' => $report['template_uuid'],
                'parent' => $parentId,
                'text' => $report['template_name'],
                'type' => 'file',
                'templateFlag' => !empty($report['default_template_flag']) && $report['default_template_flag'] === 1,
                'templateType' => $report['template_type'],
                'subType' => $report['sub_type'],
                'sortOrder' => $report['sort_order'],
                'children' => false
            ];
        }

        return $treeNodes;
    }

    /**
     * 搜索报表树节点
     * @param [type] $search
     * @return array
     */
    private function searchReportTreeNodes($search): array
    {
        // 1. 搜索匹配的文件夹
        $groupSql = "SELECT group_uuid FROM bd_report_group WHERE group_name LIKE ?";
        $matchingGroups = $this->dbSelect($groupSql, ['%' . $search . '%']);
        $matchingGroupIds = array_column($matchingGroups, 'group_uuid');

        // 2. 搜索匹配的报表
        $reportSql = "SELECT template_uuid, group_uuid FROM bd_report WHERE template_name LIKE ?";
        $matchingReports = $this->dbSelect($reportSql, ['%' . $search . '%']);
        $matchingReportIds = array_column($matchingReports, 'template_uuid');

        // 2.1 获取匹配文件夹的直接子节点（即如果是文件夹名匹配了搜索关键词，要把它的所有直接子节点，包括子文件夹和子报表一并接入到搜索结果中）
        $childGroupIds = [];
        $childReportIds = [];
        if (!empty($matchingGroupIds)) {
            $placeholders = str_repeat('?,', count($matchingGroupIds) - 1) . '?';

            // 获取子文件夹
            $childGroupSql = "SELECT group_uuid FROM bd_report_group WHERE parent_uuid IN ($placeholders)";
            $childGroupsResult = $this->dbSelect($childGroupSql, array_values($matchingGroupIds));
            $childGroupIds = array_column($childGroupsResult, 'group_uuid');

            // 获取子报表
            $childReportSql = "SELECT template_uuid FROM bd_report WHERE group_uuid IN ($placeholders)";
            $childReportsResult = $this->dbSelect($childReportSql, array_values($matchingGroupIds));
            $childReportIds = array_column($childReportsResult, 'template_uuid');
        }

        // 2.2 如果什么都没匹配到，直接返回空
        if (empty($matchingGroupIds) && empty($matchingReportIds)) {
            // 特殊处理：如果搜索词是“所有”且没有具体匹配项，则返回完整树
            if ($search === '所有') {
                return $this->getCompleteReportTree();
            }

            return [];
        }

        // 3. 迭代查询所有父文件夹ID
        $allAncestorIds = [];
        $reportGroupIds = array_filter(array_column($matchingReports, 'group_uuid'), function ($id) {
            return $id !== null && $id !== '';
        });
        $queue = array_unique(array_merge($matchingGroupIds, $reportGroupIds));
        $processedIds = []; // 记录所有已处理过的ID

        while (!empty($queue)) {
            $params = array_values($queue); // 重置数组索引，避免PDO异常
            $placeholders = str_repeat('?,', count($params) - 1) . '?';

            $parentSql = "SELECT parent_uuid FROM bd_report_group WHERE group_uuid IN ($placeholders) AND parent_uuid IS NOT NULL";
            $parentIdsResult = $this->dbSelect($parentSql, $params);

            $processedIds = array_unique(array_merge($processedIds, $queue));

            if (empty($parentIdsResult)) {
                break;
            }

            $parentUuids = array_unique(array_column($parentIdsResult, 'parent_uuid'));

            // 找出从未处理过的新父节点
            $newParents = array_diff($parentUuids, $processedIds);

            if (empty($newParents)) {
                break;
            }

            $allAncestorIds = array_unique(array_merge($allAncestorIds, $newParents));
            $queue = $newParents;
        }

        // 4. 合并最终的文件夹和报表ID
        $finalGroupIds = array_unique(array_merge($matchingGroupIds, $reportGroupIds, $allAncestorIds, $childGroupIds));
        $finalReportIds = array_unique(array_merge($matchingReportIds, $childReportIds));

        $treeNodes = [];

        // 5. 根据ID获取文件夹详细信息
        if (!empty($finalGroupIds)) {
            $params = array_values($finalGroupIds);
            $placeholders = str_repeat('?,', count($params) - 1) . '?';
            $groupSql = "SELECT group_uuid, group_name, parent_uuid, default_template_flag FROM bd_report_group WHERE group_uuid IN ($placeholders)";
            $groups = $this->dbSelect($groupSql, $params);

            foreach ($groups as $group) {
                $treeNodes[] = [
                    'id' => $group['group_uuid'],
                    'parent' => $group['parent_uuid'] ?? 'root',
                    'text' => $group['group_name'],
                    'type' => 'folder',
                    'templateFlag' => !empty($group['default_template_flag']) && $group['default_template_flag'] == 1,
                    'state' => ['opened' => true]
                ];
            }
        }

        // 6. 根据ID获取报表详细信息
        if (!empty($finalReportIds)) {
            $params = array_values($finalReportIds);
            $placeholders = str_repeat('?,', count($params) - 1) . '?';
            $reportSql = "SELECT template_uuid, template_name, group_uuid, default_template_flag, template_type, sub_type, sort_order FROM bd_report WHERE template_uuid IN ($placeholders)";
            $reports = $this->dbSelect($reportSql, $params);

            foreach ($reports as $report) {
                $treeNodes[] = [
                    'id' => $report['template_uuid'],
                    'parent' => ($report['group_uuid'] !== null && $report['group_uuid'] !== '') ? $report['group_uuid'] : 'root',
                    'text' => $report['template_name'],
                    'type' => 'file',
                    'templateFlag' => !empty($report['default_template_flag']) && $report['default_template_flag'] == 1,
                    'templateType' => $report['template_type'],
                    'subType' => $report['sub_type'],
                    'sortOrder' => $report['sort_order'],
                ];
            }
        }

        // 只有在有结果时才添加根节点
        if (!empty($treeNodes)) {
            array_unshift($treeNodes, [
                'id' => 'root',
                'parent' => '#',
                'text' => '所有报表',
                'type' => 'folder',
                'state' => ['opened' => true]
            ]);
        }

        return $treeNodes;
    }

    /**
     * 获取完整的报表树节点，用于“所有”关键词的特殊搜索场景
     * @return void
     */
    private function getCompleteReportTree(): array
    {
        $treeNodes = [];

        // 1. 添加根节点
        $treeNodes[] = [
            'id' => 'root',
            'parent' => '#',
            'text' => '所有报表',
            'type' => 'folder',
            'state' => ['opened' => true]
        ];

        // 2. 获取所有文件夹
        $allGroupsSql = "SELECT group_uuid, group_name, parent_uuid, default_template_flag FROM bd_report_group";
        $allGroups = $this->dbSelect($allGroupsSql, []);
        foreach ($allGroups as $group) {
            $treeNodes[] = [
                'id' => $group['group_uuid'],
                'parent' => ($group['parent_uuid'] !== null && $group['parent_uuid'] !== '') ? $group['parent_uuid'] : 'root',
                'text' => $group['group_name'],
                'type' => 'folder',
                'templateFlag' => !empty($group['default_template_flag']) && $group['default_template_flag'] == 1,
                'state' => ['opened' => false]
            ];
        }

        // 3. 获取所有报表
        $allReportsSql = "SELECT template_uuid, template_name, group_uuid, default_template_flag, template_type, sub_type, sort_order FROM bd_report";
        $allReports = $this->dbSelect($allReportsSql, []);
        foreach ($allReports as $report) {
            $treeNodes[] = [
                'id' => $report['template_uuid'],
                'parent' => ($report['group_uuid'] !== null && $report['group_uuid'] !== '') ? $report['group_uuid'] : 'root',
                'text' => $report['template_name'],
                'type' => 'file',
                'templateFlag' => !empty($report['default_template_flag']) && $report['default_template_flag'] == 1,
                'templateType' => $report['template_type'],
                'subType' => $report['sub_type'],
                'sortOrder' => $report['sort_order']
            ];
        }

        return $treeNodes;
    }

    /**
     * 获取报表详情
     * @param mixed $params
     */
    public function getReportDetail($params): array
    {
        $templateUuid = $params['uuid'];

        $sql = "SELECT * FROM bd_report WHERE template_uuid = ?";
        $reports = $this->dbSelect($sql, [$templateUuid]);

        $result = [];
        if (empty($reports)) {
            return [];
        }

        $result = [
            'templateUuid' => $reports[0]['template_uuid'],
            'group_uuid' => $reports[0]['group_uuid'],
            'templateName' => $reports[0]['template_name'],
            'description' => $reports[0]['description'],
            'defaultTemplateFlag' => $reports[0]['default_template_flag'],
            'templateType' => $reports[0]['template_type'],
            'subType' => $reports[0]['sub_type'],
            'detail' => json_decode($reports[0]['detail'], true),
        ];

        return $result;
    }

    /**
     * 新建报表
     * @param mixed $params
     * @return array{id: string}
     */
    public function createReport($params): array
    {
        $templateTypeConfig = xphp_get_config('report', 'TEMPLATE_TYPE', 'report');
        $backupSourceTypeConfig = xphp_get_config('report', 'BACKUP_RESOURCE_TYPE', 'report');
        $dataProtectTypeConfig = xphp_get_config('report', 'DATA_PROTECT_TYPE', 'report');

        $groupUuid = $params['groupUuid'];
        $templateName = $params['templateName'];
        $description = $params['description'];
        $defaultTemplateFlag = 2; // 非默认模板
        $templateType = $params['templateType'];
        $subType = $params['subType'];
        $noticeFlag = 2; // 默认不发送通知
        $emailNoticeId = null; // 默认不发送邮件通知

        // 计算新的sort_order值
        $sortOrderSql = "SELECT MAX(sort_order) AS max_sort FROM bd_report WHERE group_uuid = ?";
        $sortResult = $this->dbSelect($sortOrderSql, [$groupUuid]);
        $nextSortOrder = ($sortResult[0]['max_sort'] ?? 0) + 1;

        $detail = [];
        switch ($templateType) {
            case $templateTypeConfig['BACKUP_RESOURCE']: // 备份资源报表模板
                switch ($subType) {
                    case $backupSourceTypeConfig['STORAGE']: // 存储报表模板
                        $detail = $this->assembleStorageReportDetails($params['detail']);
                        break;
                    case $backupSourceTypeConfig['TAPE']: // 磁带报表模板
                        $detail = $this->assembleTapeReportDetails($params['detail']);
                        break;
                    case $backupSourceTypeConfig['NODE']: // 节点报表模板
                        $detail = $this->assembleNodeReportDetails($params['detail']);
                        break;
                    default:
                        break;
                }
                break;
            case $templateTypeConfig['PRODUCTION_RESOURCE']: // 生产资源报表模板
                break;
            case $templateTypeConfig['DATA_PROTECT']: // 数据保护报表模板
                switch ($subType) {
                    case $dataProtectTypeConfig['VM']: // 虚拟化保护报表
                    case $dataProtectTypeConfig['PRIVATE_CLOUD']: // 私有云保护报表
                    case $dataProtectTypeConfig['PUBLIC_CLOUD']: // 公有云保护报表
                        $detail = $this->assembleVmReportDetails($params['detail']);
                        break;
                    default:
                        break;
                }
                break;
            case $templateTypeConfig['TASK']: // 任务报表模板
                break;
            case $templateTypeConfig['USER']: // 用户报表模板
                break;
            default:
                break;
        }

        $newTemplateUuid = xphp_uuid();
        $currentTime = date('Y-m-d H:i:s');
        $user = xphp_get_user_info();

        $insertSql = "INSERT INTO 
                        bd_report 
                        (template_uuid, group_uuid, template_name, description, default_template_flag, template_type, 
                        sub_type, detail, sort_order, notice_flag, email_notice_id, create_time, user_uuid, user_name, update_time) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertParams = [
            $newTemplateUuid,
            $groupUuid,
            $templateName,
            $description,
            $defaultTemplateFlag,
            $templateType,
            $subType,
            json_encode($detail),
            $nextSortOrder,
            $noticeFlag,
            $emailNoticeId,
            $currentTime,
            $user['userUuid'],
            $user['userName'],
            $currentTime
        ];

        $this->dbExec($insertSql, $insertParams);

        // 返回新节点的数据给前端，以便更新jstree
        $newTemplateData = [
            'id' => $newTemplateUuid
        ];

        return $newTemplateData;
    }

    /**
     * 更新报表配置
     * @param mixed $params
     * @return void
     */
    public function updateReport($params): array
    {
        $onlyRenameReportName = $params['onlyRenameReportName'] ?? false;

        if ($onlyRenameReportName) {
            $renameSql = "UPDATE bd_report SET template_name = ? WHERE template_uuid = ?";
            $renameParams = [
                $params['templateName'],
                $params['templateUuid']
            ];

            $falg = $this->dbExec($renameSql, $renameParams);

            if (!$falg) {
                return ['success' => false, 'message' => '更新失败'];
            }

            return ['success' => true, 'message' => '更新成功'];
        }

        $templateTypeConfig = xphp_get_config('report', 'TEMPLATE_TYPE', 'report');
        $backupSourceTypeConfig = xphp_get_config('report', 'BACKUP_RESOURCE_TYPE', 'report');
        $dataProtectTypeConfig = xphp_get_config('report', 'DATA_PROTECT_TYPE', 'report');

        $templateUuid = $params['templateUuid'];
        $templateType = $params['templateType'];
        $subType = $params['subType'];
        $templateName = $params['templateName'];
        $description = $params['description'];

        $detail = [];
        switch ($templateType) {
            case $templateTypeConfig['BACKUP_RESOURCE']: // 备份资源报表模板
                switch ($subType) {
                    case $backupSourceTypeConfig['STORAGE']: // 存储报表模板
                        $detail = $this->assembleStorageReportDetails($params['detail']);
                        break;
                    case $backupSourceTypeConfig['TAPE']: // 磁带报表模板
                        $detail = $this->assembleTapeReportDetails($params['detail']);
                        break;
                    case $backupSourceTypeConfig['NODE']: // 节点报表模板
                        $detail = $this->assembleNodeReportDetails($params['detail']);
                        break;
                    default:
                        break;
                }
                break;
            case $templateTypeConfig['PRODUCTION_RESOURCE']: // 生产资源报表模板
                break;
            case $templateTypeConfig['DATA_PROTECT']: // 数据保护报表模板
                switch ($subType) {
                    case $dataProtectTypeConfig['VM']: // 虚拟化保护报表
                    case $dataProtectTypeConfig['PRIVATE_CLOUD']: // 私有云保护报表
                    case $dataProtectTypeConfig['PUBLIC_CLOUD']: // 公有云保护报表
                        $detail = $this->assembleVmReportDetails($params['detail']);
                        break;
                    default:
                        break;
                }
                break;
            case $templateTypeConfig['TASK']: // 任务报表模板
                break;
            case $templateTypeConfig['USER']: // 用户报表模板
                break;
            default:
                break;
        }

        $updateTime = date('Y-m-d H:i:s');
        $user = xphp_get_user_info();

        $updateSql = "UPDATE bd_report SET template_name = ?, description = ?, detail = ?, update_time = ?, user_uuid = ?, user_name = ? WHERE template_uuid = ?";
        $updateParams = [
            $templateName,
            $description,
            json_encode($detail),
            $updateTime,
            $user['userUuid'],
            $user['userName'],
            $templateUuid
        ];

        $result = $this->dbExec($updateSql, $updateParams);

        if ($result) {
            return ['success' => true, 'message' => '更新成功'];
        } else {
            return ['success' => false, 'message' => '更新失败'];
        }
    }

    /**
     * 组装存储报表detail数据
     * @param mixed $params
     * @return void
     */
    private function assembleStorageReportDetails($params): array
    {
        return [
            'storages' => $params['storages'],
            'viewOverview' => $params['viewOverview'],
            'viewUsageTendency' => $params['viewUsageTendency'],
            'timeRangeType' => $params['timeRangeType'],
            'timeRange' => $params['timeRange'],
            'modules' => $params['modules'],
            'availabilityForecast' => $params['availabilityForecast'],
            'customFields' => $params['customFields'],
            'path' => $params['path']
        ];
    }

    /**
     * 组装磁带报表detail数据
     * @param mixed $params
     * @return void
     */
    private function assembleTapeReportDetails($params): array
    {
        return [
            'tapes' => $params['tapes'],
            'viewOverview' => $params['viewOverview'],
            'viewUsageTendency' => $params['viewUsageTendency'],
            'timeRangeType' => $params['timeRangeType'],
            'timeRange' => $params['timeRange'],
            'modules' => $params['modules'],
            'customFields' => $params['customFields'],
            'path' => $params['path']
        ];
    }

    /**
     * 组装节点报表detail数据
     * @param mixed $params
     * @return void
     */
    private function assembleNodeReportDetails($params): array
    {
        return [
            'nodes' => $params['nodes'],
            'viewOverview' => $params['viewOverview'],
            'viewUsageTendency' => $params['viewUsageTendency'],
            'timeRangeType' => $params['timeRangeType'],
            'timeRange' => $params['timeRange'],
            'customFields' => $params['customFields'],
            'path' => $params['path']
        ];
    }

    /**
     * 获取虚拟机报表（虚拟化、私有云、公有云）detail数据
     * @param mixed $params
     * @return array{customFields: mixed, path: mixed, timeRange: mixed, timeRangeType: mixed, viewOverview: mixed, viewUsageTendency: mixed, vms: mixed}
     */
    private function assembleVmReportDetails($params): array
    {
        return [
            'vms' => $params['vms'],
            'viewOverview' => $params['viewOverview'],
            'viewUsageTendency' => $params['viewUsageTendency'],
            'timeRangeType' => $params['timeRangeType'],
            'timeRange' => $params['timeRange'],
            'customFields' => $params['customFields'],
            'path' => $params['path']
        ];
    }

    /**
     * 获取报表列表
     * @param mixed $params
     * @return array{rows: array, total: mixed}
     */
    public function getReportList($params): array
    {
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? 10;
        $sort = $params['sort'] ?? 'create_time';
        $order = $params['order'] ?? 'desc';
        $search = $params['search'] ?? '';
        $groupUuid = $params['group_uuid'] ?? '';
        $templateType = $params['template_type'] ?? '';

        // sort whitelist
        if (!in_array($sort, ['create_time', 'template_name', 'template_type'])) {
            $sort = 'create_time';
        }

        $where = '1=1';
        $queryParams = [];

        if ($groupUuid == '0') { // 获取顶级分组（所有报表）下及其所有子孙分组中的报表
            // 找出所有顶级分组
            $topLevelGroupsSql = "SELECT group_uuid FROM bd_report_group WHERE parent_uuid = ?";
            $topLevelGroups = $this->dbSelect($topLevelGroupsSql, ['0']);
            if (!empty($topLevelGroups)) {
                $startUuids = array_column($topLevelGroups, 'group_uuid');
                $groupUuidsToQuery = $this->getDescendantGroupUuids($startUuids);
                if (!empty($groupUuidsToQuery)) {
                    $placeholders = implode(',', array_fill(0, count($groupUuidsToQuery), '?'));
                    $where .= " AND group_uuid IN ({$placeholders})";
                    $queryParams = array_merge($queryParams, $groupUuidsToQuery);
                } else {
                    // 没有顶级分组下的任何子分组
                    $where .= " AND 1=0";
                }
            } else {
                // 没有顶级分组，则不返回任何报表
                $where .= " AND 1=0";
            }
        } else if (!empty($groupUuid) && $groupUuid != 'root') { // 获取指定分组及其所有子分组中的报表
            // 获取指定分组及其所有子分组
            $groupUuidsToQuery = $this->getDescendantGroupUuids([$groupUuid]);
            if (!empty($groupUuidsToQuery)) {
                $placeholders = implode(',', array_fill(0, count($groupUuidsToQuery), '?'));
                $where .= " AND group_uuid IN ({$placeholders})";
                $queryParams = array_merge($queryParams, $groupUuidsToQuery);
            } else {
                // 指定的分组不存在或没有子分组
                $where .= " AND 1=0";
            }
        }

        if (!empty($search)) {
            $where .= ' AND template_name LIKE ?';
            $queryParams[] = '%' . $search . '%';
        }

        if (!empty($templateType)) {
            $templateTypes = explode(',', $templateType);
            $templateTypes = array_map('trim', $templateTypes);
            $placeholders = implode(',', array_fill(0, count($templateTypes), '?'));
            $where .= " AND template_type IN ({$placeholders})";
            $queryParams = array_merge($queryParams, $templateTypes);
        }

        $countSql = "SELECT COUNT(*) AS total FROM bd_report WHERE {$where}";
        $totalResult = $this->dbSelect($countSql, $queryParams);
        $total = $totalResult[0]['total'] ?? 0;

        $sql = "SELECT * FROM bd_report WHERE {$where} ORDER BY {$sort} {$order} LIMIT {$offset}, {$limit}";
        $reports = $this->dbSelect($sql, $queryParams);

        // 批量获取通知策略信息以避免N+1查询
        $noticeIds = array_filter(array_column($reports, 'email_notice_id'));
        $noticeConfigsMap = [];
        if (!empty($noticeIds)) {
            $uniqueNoticeIds = array_unique($noticeIds);
            $placeholders = implode(',', array_fill(0, count($uniqueNoticeIds), '?'));
            $noticeSql = "SELECT id, email_notice_flag, report_config, receive_email FROM bd_email_notice WHERE id IN ($placeholders)";
            $noticeData = $this->dbSelect($noticeSql, array_values($uniqueNoticeIds));

            foreach ($noticeData as $notice) {
                $noticeConfigsMap[$notice['id']] = [
                    'emailNoticeFlag' => $notice['email_notice_flag'],
                    'reportConfig' => json_decode($notice['report_config'], true),
                    'receiveEmail' => json_decode($notice['receive_email'], true)
                ];
            }
        }

        $formattedReports = [];
        foreach ($reports as $report) {
            $detail = json_decode($report['detail'], true);
            $noticeConfig = new \stdClass(); // 默认为空对象 {}
            if (!empty($report['email_notice_id']) && isset($noticeConfigsMap[$report['email_notice_id']])) {
                $noticeConfig = $noticeConfigsMap[$report['email_notice_id']];
            }

            $formattedReports[] = [
                'templateUuid' => $report['template_uuid'],
                'groupUuid' => $report['group_uuid'],
                'templateName' => $report['template_name'],
                'description' => $report['description'],
                'templateType' => $report['template_type'],
                'subType' => $report['sub_type'],
                'detail' => json_decode($report['detail'], true),
                'sort_order' => $report['sort_order'],
                'noticeFlag' => $report['notice_flag'],
                'emailNoticeId' => $report['email_notice_id'],
                'create_time' => $report['create_time'],
                'defaultTemplateFlag' => $report['default_template_flag'],
                'noticeConfig' => $noticeConfig
            ];
        }

        return [
            'total' => $total,
            'rows' => $formattedReports
        ];
    }

    /**
     * 高性能获取一个或多个起始分组及其所有后代分组的 UUID.
     *
     * @param array $startUuids 起始分组的 UUID 数组.
     * @return array
     */
    private function getDescendantGroupUuids(array $startUuids): array
    {
        if (empty($startUuids)) {
            return [];
        }

        // 1. 一次性获取所有分组，构建邻接表以优化性能
        $allGroupsSql = "SELECT group_uuid, parent_uuid FROM bd_report_group";
        $allGroups = $this->dbSelect($allGroupsSql);
        if (empty($allGroups)) {
            return $startUuids;
        }

        $adj = [];
        // 内存中构建映射关系图，每个父节点ID都执行一个包含其所有子节点ID的数组
        foreach ($allGroups as $group) {
            $adj[$group['parent_uuid']][] = $group['group_uuid'];
        }

        // 2. 使用广度优先搜索（BFS）遍历所有子孙分组
        $allUuids = [];
        $queue = $startUuids;
        $visited = array_fill_keys($startUuids, true);

        while (!empty($queue)) {
            $currentUuid = array_shift($queue);
            $allUuids[] = $currentUuid;

            if (!empty($adj[$currentUuid])) {
                foreach ($adj[$currentUuid] as $childUuid) {
                    if (!isset($visited[$childUuid])) {
                        $queue[] = $childUuid;
                        $visited[$childUuid] = true;
                    }
                }
            }
        }
        return $allUuids;
    }

    /**
     * 创建报表子文件夹
     * @param mixed $params
     * @return array{id: string}
     */
    public function createReportGroup($params)
    {
        $parentUuid = $params['parent_uuid'] ?? null;
        $groupName = $params['group_name'] ?? 'New Folder';

        // 如果父节点是虚拟根 'root'，则在数据库中存为 NULL
        $dbParentUuid = ($parentUuid === 'root') ? null : $parentUuid;

        // 计算新节点的排序值
        $sortSql = "SELECT MAX(sort_order) as max_sort FROM bd_report_group WHERE parent_uuid " . ($dbParentUuid === null ? "IS NULL" : "= ?");
        $sortParams = ($dbParentUuid === null) ? [] : [$dbParentUuid];
        $sortResult = $this->dbSelect($sortSql, $sortParams);
        $nextSortOrder = ($sortResult[0]['max_sort'] ?? 0) + 1;

        // 插入新值
        $newGroupUuid = xphp_uuid();
        $currentTime = date('Y-m-d H:i:s');

        $insertSql = "INSERT INTO bd_report_group (group_uuid, parent_uuid, group_name, default_template_flag, sort_order, create_time, update_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertParams = [
            $newGroupUuid,
            $dbParentUuid,
            $groupName,
            2, // 2 代表用户创建的文件夹
            $nextSortOrder,
            $currentTime,
            $currentTime
        ];

        $this->dbExec($insertSql, $insertParams);

        // 返回新节点的数据给前端，以便更新jstree
        $newGroupData = [
            'id' => $newGroupUuid
        ];

        return $newGroupData;
    }

    /**
     * 修改子文件夹名
     * @param mixed $params
     * @return array|array{id: mixed}
     */
    public function updateReportGroup($params)
    {
        $groupUuid = $params['group_uuid'] ?? null;
        $groupName = $params['group_name'] ?? null;

        if (empty($groupUuid)) {
            return [];
        }

        $updateSql = "UPDATE bd_report_group SET group_name = ?, update_time = ? WHERE group_uuid = ?";
        $updateParams = [
            $groupName,
            date('Y-m-d H:i:s'),
            $groupUuid
        ];

        $result = $this->dbExec($updateSql, $updateParams);

        if ($result) {
            return ['success' => true, 'message' => '更新成功'];
        } else {
            return ['success' => false, 'message' => '更新失败'];
        }
    }

    /**
     * 删除自定义报表文件夹
     * @param mixed $params
     * @return void
     */
    public function deleteReportGroup($params): array
    {
        $groupUuid = $params['group_uuid'] ?? null;

        if (empty($groupUuid)) {
            return ['success' => false, 'message' => '未指定任何报表文件夹'];
        }

        $this->dbBeginTransaction();
        try {
            // 1. 递归获取所有待删除的文件夹UUID（包括自身）
            $allFoldersToDelete = $this->getDescendantFolders($groupUuid);
            $allFoldersToDelete[] = $groupUuid;

            if (!empty($allFoldersToDelete)) {
                $folderUuidsPlaceholder = implode(',', array_fill(0, count($allFoldersToDelete), '?'));

                // 2. 根据文件夹UUID，查询出所有待删除报表关联的 email_notice_id
                $selectReportsSql = "SELECT email_notice_id FROM bd_report WHERE group_uuid IN ($folderUuidsPlaceholder)";
                $reportsInFolders = $this->dbSelect($selectReportsSql, $allFoldersToDelete);

                $noticeIdsToDelete = [];
                foreach ($reportsInFolders as $report) {
                    if (!empty($report['email_notice_id'])) {
                        $noticeIdsToDelete[] = $report['email_notice_id'];
                    }
                }

                // 3. 如果有关联的通知配置，则从 bd_email_notice 表中删除它们
                if (!empty($noticeIdsToDelete)) {
                    $uniqueNoticeIds = array_unique($noticeIdsToDelete);
                    $noticePlaceholders = implode(',', array_fill(0, count($uniqueNoticeIds), '?'));
                    $deleteNoticeSql = "DELETE FROM bd_email_notice WHERE id IN ($noticePlaceholders)";
                    $this->dbExec($deleteNoticeSql, array_values($uniqueNoticeIds));
                }

                // 4. 从 bd_report 表中删除这些文件夹下的所有报表
                $deleteReportSql = "DELETE FROM bd_report WHERE group_uuid IN ($folderUuidsPlaceholder)";
                $this->dbExec($deleteReportSql, $allFoldersToDelete);

                // 5. 从 bd_report_group 表中删除所有相关文件夹
                $deleteGroupSql = "DELETE FROM bd_report_group WHERE group_uuid IN ($folderUuidsPlaceholder)";
                $this->dbExec($deleteGroupSql, $allFoldersToDelete);
            }

            $this->dbCommit(); // 提交事务
            return ['success' => true, 'message' => '删除成功'];
        } catch (\Exception $e) {
            $this->dbRollBack();
            return ['success' => false, 'message' => '删除失败: ' . $e->getMessage()];
        }
    }

    /**
     * 递归获取所有子文件夹id
     * @param mixed $parentUuid
     * @return void
     */
    private function getDescendantFolders($parentUuid): array
    {
        $descendants = [];
        $childrenSql = "SELECT group_uuid FROM bd_report_group WHERE parent_uuid = ?";
        $children = $this->dbSelect($childrenSql, [$parentUuid]);

        foreach ($children as $child) {
            $descendants[] = $child['group_uuid'];
            $descendants = array_merge($descendants, $this->getDescendantFolders($child['group_uuid']));
        }

        return $descendants;
    }

    /**
     * 删除报表
     * @param mixed $params
     * @return array{message: string, success: bool}
     */
    public function deleteReport($params): array
    {
        $templateUuids = explode(',', $params['templateUuids'] ?? null);

        if (empty($templateUuids)) {
            return ['success' => false, 'message' => '未指定任务报表'];
        }

        try {
            // 1. 根据 templateUuids 查询出所有需要删除的 email_notice_id
            $placeholders = implode(',', array_fill(0, count($templateUuids), '?'));
            $selectSql = "SELECT email_notice_id FROM bd_report WHERE template_uuid IN ($placeholders)";
            $reportsToDelete = $this->dbSelect($selectSql, $templateUuids);

            $noticeIdsToDelete = [];
            foreach ($reportsToDelete as $report) {
                if (!empty($report['email_notice_id'])) {
                    $noticeIdsToDelete[] = $report['email_notice_id'];
                }
            }

            // 2. 如果有关联的通知配置，则从 bd_email_notice 表中删除它们
            if (!empty($noticeIdsToDelete)) {
                // 去重，以防多个报表共享同一个通知配置（虽然不太可能，但作为防御性措施）
                $uniqueNoticeIds = array_unique($noticeIdsToDelete);
                $noticePlaceholders = implode(',', array_fill(0, count($uniqueNoticeIds), '?'));
                $deleteNoticeSql = "DELETE FROM bd_email_notice WHERE id IN ($noticePlaceholders)";
                $this->dbExec($deleteNoticeSql, array_values($uniqueNoticeIds));
            }

            // 3. 从 bd_report 表中删除报表本身
            $deleteReportSql = "DELETE FROM bd_report WHERE template_uuid IN ($placeholders)";
            $this->dbExec($deleteReportSql, $templateUuids);

            return ['success' => true, 'message' => '报表删除成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '报表删除失败: ' . $e->getMessage()];
        }
    }

    /**
     * 获取自定义报表路径树
     * @return void
     */
    public function getCustomReportFolderTree($params): array
    {
        $parentId = $params['id'] ?? '#';
        $treeNodes = [];

        if ($parentId === '#') { // 初始化加载
            // 创建虚拟根节点：所有报表
            $treeNodes[] = [
                'id' => 'root',
                'parent' => '#',
                'text' => '所有报表',
                'type' => 'folder',
                'state' => ['opened' => true]
            ];

            // 查询其子节点（parent_uuid IS NULL 的所有自定义文件夹）
            $sql = "SELECT 
                        g.group_uuid, g.group_name,
                        (EXISTS ( SELECT 1 FROM bd_report_group WHERE parent_uuid = g.group_uuid AND default_template_flag = 2 )) AS has_children 
                    FROM 
                        bd_report_group g 
                    WHERE
                        g.parent_uuid IS NULL AND g.default_template_flag = 2";

            $rootGroups = $this->dbSelect($sql, []);

            foreach ($rootGroups as $group) {
                $node = [
                    'id' => $group['group_uuid'],
                    'parent' => 'root',
                    'text' => $group['group_name'],
                    'type' => 'folder',
                    'children' => $group['has_children'] === 1
                ];

                $treeNodes[] = $node;
            }

            return $treeNodes;
        }

        // 查询子文件夹
        $groupSql = "SELECT 
                        g.group_uuid, g.group_name, 
                        (EXISTS (SELECT 1 FROM bd_report_group WHERE parent_uuid = g.group_uuid AND default_template_flag = 2)) as has_children 
                    FROM 
                        bd_report_group g 
                    WHERE 
                        g.parent_uuid = ? AND g.default_template_flag = 2";
        $childGroups = $this->dbSelect($groupSql, [$parentId]);

        foreach ($childGroups as $group) {
            $node = [
                'id' => $group['group_uuid'],
                'parent' => $parentId,
                'text' => $group['group_name'],
                'type' => 'folder',
                'children' => $group['has_children'] === 1
            ];

            $treeNodes[] = $node;
        }

        return $treeNodes;
    }

    // <----------------------------- END REPORT TREE TABLE LOGIC ------------------------------------------------------>

    /**
     * 获取存储概览数据
     * @return array{freeSpace: int, offlineCount: int, onlineCount: int, storagePoolOvervew: array{centralized_pool_count: int, cloud_pool_count: int, nas_pool_count: int|array{centralized_pool_count: mixed, cloud_pool_count: mixed, nas_pool_count: mixed}, storageTotal: int, topStorageUsage: array, totalSpace: int, usedSpace: int, utilizationRate: int}|array{freeSpace: string, offlineCount: mixed, onlineCount: mixed, storagePoolOvervew: array{centralized_pool_count: int, cloud_pool_count: int, nas_pool_count: int}|array{centralized_pool_count: mixed, cloud_pool_count: mixed, nas_pool_count: mixed}, storageTotal: mixed, topStorageUsage: array, totalSpace: string, usedSpace: string, utilizationRate: float|int}}
     */
    public function getStorageOverview()
    {
        $storagestatus = xphp_get_config('resource', 'STORAGE_STATUS');
        $storagestype = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $flag = xphp_get_config('app', 'FLAG');

        // 子查询，用于根据 bd_module_server 表计算节点的真实在线状态
        // 节点在线的条件是：在 bd_module_server 中有记录，且所有模块的 online_flag 均为 1
        // 按 node_uuid 进行分组后，MIN() 找出 online_flag 这一列的最小值（如果所有模块的 online_flag 都是 1，那么 MIN(online_flag) 的结果自然就是 1；只要有任何一个模块的 online_flag 是 0，那么 MIN(online_flag) 的结果就必然是 0）
        $nodeStatusSubquery = "
            SELECT
                node_uuid,
                MIN(online_flag) as is_online
            FROM
                bd_module_server
            GROUP BY
                node_uuid
        ";

        // COALESCE 函数会接受一个或多个参数，并返回参数列表中的第一个非 NULL 值
        // 情况一：ns.is_online 有值。如果 LEFT JOIN 成功匹配，ns.is_online 的值会是 1 (在线) 或 0 (离线)。因为 1 或 0 都不是 NULL，所以 COALESCE 函数会直接返回这个值 (1 或 0)
        // 情况二：ns.is_online 是 NULL。如果 LEFT JOIN 未能匹配（即节点在 bd_module_server 中无记录），ns.is_online 就是 NULL。此时 COALESCE 函数会检查它的下一个参数，发现是 0，并且 0 不是 NULL，于是函数就返回 0。
        $sql = "
            SELECT
                COUNT(bsr.storage_uuid) AS storage_total,
                SUM(bsr.total_size) AS total_space,
                SUM(bsr.free_size) AS free_space,
                SUM(bsr.total_size - bsr.free_size) AS used_space,
                SUM(CASE
                    -- 如果节点状态为离线 (is_online 为 0 或 NULL)，并且不是云存储，则存储计为离线
                    WHEN (COALESCE(ns.is_online, 0) = 0 AND bsr.storage_type != {$storagestype['CLOUD']}) THEN 1
                    -- 或者存储本身的状态就是离线
                    WHEN bsr.status = {$storagestatus['OFFLINE']} THEN 1
                    ELSE 0
                END) AS offline_count,
                SUM(CASE
                    -- 存储在线的条件：节点在线（或为云存储） 且 存储状态为在线 且 已挂载
                    WHEN (COALESCE(ns.is_online, 0) = 1 OR bsr.storage_type = {$storagestype['CLOUD']})
                        AND bsr.status = {$storagestatus['ONLINE']}
                        AND bsr.mount_flag = {$flag['SET']}
                    THEN 1
                    ELSE 0
                END) AS online_count
            FROM
                bd_storage_resource AS bsr
            LEFT JOIN
                ({$nodeStatusSubquery}) AS ns ON bsr.node_uuid = ns.node_uuid
        ";

        $data = $this->dbSelect($sql, []);

        $storagePoolOverviewSql = "SELECT
                                        SUM(CASE WHEN storage_pool_type = 1 THEN 1 ELSE 0 END) AS centralized_pool_count,
                                        SUM(CASE WHEN storage_pool_type = 2 THEN 1 ELSE 0 END) AS nas_pool_count,
                                        SUM(CASE WHEN storage_pool_type = 3 THEN 1 ELSE 0 END) AS cloud_pool_count
                                    FROM
                                        bd_storage_resource_pool";

        $storagePoolOverviewData = $this->dbSelect($storagePoolOverviewSql, []);

        if (!empty($storagePoolOverviewData)) {
            $storagePoolOverview = array(
                'centralized_pool_count' => $storagePoolOverviewData[0]['centralized_pool_count'],
                'nas_pool_count' => $storagePoolOverviewData[0]['nas_pool_count'],
                'cloud_pool_count' => $storagePoolOverviewData[0]['cloud_pool_count']
            );
        } else {
            $storagePoolOverview = array(
                'centralized_pool_count' => 0,
                'nas_pool_count' => 0,
                'cloud_pool_count' => 0
            );
        }

        $topStorageUsage = $this->getStorageUsageTopN(5);

        if (!empty($data)) {
            $result = array(
                'storageTotal' => $data[0]['storage_total'],
                'totalSpace' => $data[0]['total_space'],
                'freeSpace' => $data[0]['free_space'],
                'usedSpace' => $data[0]['used_space'],
                'offlineCount' => $data[0]['offline_count'],
                'onlineCount' => $data[0]['online_count'],
                'utilizationRate' => !empty($data[0]['total_space']) ? round($data[0]['used_space'] / $data[0]['total_space'] * 100, 1) : 0,
                'storagePoolOvervew' => $storagePoolOverview,
                'topStorageUsageList' => $topStorageUsage
            );
        } else {
            $result = [
                'storageTotal' => 0,
                'totalSpace' => 0,
                'freeSpace' => 0,
                'usedSpace' => 0,
                'offlineCount' => 0,
                'onlineCount' => 0,
                'utilizationRate' => 0,
                'storagePoolOvervew' => $storagePoolOverview,
                'topStorageUsageList' => $topStorageUsage
            ];
        }

        return $result;
    }

    /**
     * 获取Top N 存储容量排行的存储设备信息
     * @param [type] $limit
     * @return void
     */
    private function getStorageUsageTopN($limit): array
    {
        $sql = "SELECT 
                    storage_nickname, total_size, free_size, (( total_size - free_size ) / total_size ) * 100 AS usage_rate 
                FROM
                    bd_storage_resource 
                WHERE
                    total_size > 0 
                ORDER BY
                    usage_rate DESC
                    LIMIT ?";

        $data = $this->dbSelect($sql, [$limit]);

        $result = [];

        if (!empty($data)) {
            foreach ($data as $row) {
                $result[] = array(
                    'name' => $row['storage_nickname'],
                    'value' => round($row['usage_rate'], 2)
                );
            }
        }

        // ECharts的条形图通常需要数据倒序排列才能正向显示
        return array_reverse($result);
    }

    /**
     * 获取存储报表近期使用趋势数据
     * @param mixed $params
     * @return void
     */
    public function getStorageUsageTendency($params): array
    {
        $storageTypeCfg = xphp_get_config('storage', 'BD_STORAGE_TYPE');
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $storageUuidsParam = $params['storageUuids'];
        $tapeStorgeFlag = $params['tapeStorgeFlag'] ?? false;

        $storageUuids = [];
        if (!empty($storageUuidsParam)) {
            $storageUuids = is_array($storageUuidsParam) ? $storageUuidsParam : [$storageUuidsParam];
        } else {
            if ($tapeStorgeFlag) {
                $storageData = $this->dbSelect("SELECT storage_uuid FROM bd_storage_resource WHERE storage_type = ?", [$storageTypeCfg['TAPE']]);
            } else {
                $storageData = $this->dbSelect("SELECT storage_uuid FROM bd_storage_resource", []);
            }

            foreach ($storageData as $row) {
                $storageUuids[] = $row['storage_uuid'];
            }
        }

        $result = array();

        if (empty($storageUuids)) {
            return [
                'legend' => [],
                'xAxis' => [],
                'series' => [],
            ];
        }

        $placeholders = implode(',', array_fill(0, count($storageUuids), '?'));

        $sql = "SELECT 
                    SUM(total_size) AS total, 
                    DATE(timepoint) AS date,
                    module_type,
                    sub_module_type
                FROM bd_backup_timepoint 
                WHERE storage_uuid IN ($placeholders) AND timepoint >= ? AND timepoint < DATE_ADD(?, INTERVAL 1 DAY)
                GROUP BY date, module_type, sub_module_type
                ORDER BY date ASC";

        $sqlParams = array_merge($storageUuids, [$startTime, $endTime]);
        $queryResult = $this->dbSelect($sql, $sqlParams);

        $seriesData = [];
        $legendData = [];

        foreach ($queryResult as $row) {
            $moduleName = $this->getModuleName($row['module_type'], $row['sub_module_type']);

            if (!in_array($moduleName, $legendData)) {
                $legendData[] = $moduleName;
            }
            if (!isset($seriesData[$moduleName])) {
                $seriesData[$moduleName] = [];
            }
            if (!isset($seriesData[$moduleName][$row['date']])) {
                $seriesData[$moduleName][$row['date']] = 0;
            }
            $seriesData[$moduleName][$row['date']] += (int) $row['total'];
        }

        $xAxisData = [];
        $current = strtotime($startTime);
        $end = strtotime($endTime);
        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $xAxisData[] = $date;
            $current = strtotime('+1 day', $current);
        }

        $finalSeries = [];
        $finalLegend = array_values($legendData);
        foreach ($legendData as $lg) {
            if (!in_array($lg, $finalLegend)) {
                $finalLegend[] = $lg;
            }
        }

        foreach ($finalLegend as $moduleName) {
            $dataPoints = [];
            foreach ($xAxisData as $date) {
                $dataPoints[] = isset($seriesData[$moduleName][$date]) ? $seriesData[$moduleName][$date] : 0;
            }
            $finalSeries[] = [
                'name' => $moduleName,
                'type' => 'line',
                'stack' => '总量',
                'smooth' => true,
                'data' => $dataPoints,
            ];
        }

        $result = [
            'legend' => $finalLegend,
            'xAxis' => $xAxisData,
            'series' => $finalSeries,
        ];

        return $result;
    }

    /**
     * 获取存储容量可用性预测
     * @param mixed $params 存储uuid数组
     * @return void
     */
    public function getStorageAvailabilityForecast($params): array
    {
        $storageUuids = $params['storage_uuids'];

        // 1.获取当前所有选定存储的聚合容量
        $queryParams = [];
        $whereClause = 'WHERE status = 1'; // 只考虑在线存储
        if (!empty($storageUuids)) {
            $placeholders = implode(',', array_fill(0, count($storageUuids), '?'));
            $whereClause .= " AND storage_uuid IN ($placeholders)";
            $queryParams = $storageUuids;
        }

        $currentCapacitySql = "SELECT SUM(total_size) AS total_capacity, SUM(free_size) AS free_capacity FROM bd_storage_resource $whereClause";
        $currentCapacityResult = $this->dbSelect($currentCapacitySql, $queryParams);

        // 如果没有数据或总容量为0，则无法预测
        if (empty($currentCapacityResult) || !$currentCapacityResult[0]['total_capacity']) {
            return [];
        }

        $totalCapacity = $currentCapacityResult[0]['total_capacity'];
        $freeCapacity = $currentCapacityResult[0]['free_capacity'];
        $usedCapacity = $totalCapacity - $freeCapacity;

        // 2.获取聚合后的历史存储使用数据（最近90天）
        $historyParams = [];
        // 基础查询条件，只查最近90天的数据
        $historyWhereClause = 'WHERE h.record_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';

        if (!empty($storageUuids)) {
            $placeholders = implode(',', array_fill(0, count($storageUuids), '?'));
            $historyWhereClause .= " AND h.storage_uuid IN ($placeholders)";
            $historyParams = $storageUuids;
        }

        $historySql = "SELECT h.record_date, SUM(h.used_size) AS daily_used_size FROM bd_storage_usage_history h $historyWhereClause GROUP BY h.record_date ORDER BY h.record_date ASC";
        $historyResult = $this->dbSelect($historySql, $historyParams);

        // 如果历史数据点少于2个，无法进行线性回归
        if (count($historyResult) < 2) {
            return [
                'remainingDays' => '∞', // 数据不足，无法预测
                'fullDate' => 'N/A',
                'totalCapacity' => $totalCapacity,
                'warningThreshold' => $totalCapacity * 0.8,
                'dateAxis' => [],
                'historicalData' => [],
                'predictedData' => []
            ];
        }

        // 3. 计算每日平均使用率 (线性回归)
        $dailyUsageRate = $this->calculateDailyUsageRate($historyResult);

        // 当使用率 <= 0 时，认为存储使用不再增长，预测线应为水平，避免出现负值
        if ($dailyUsageRate <= 0) {
            $dailyUsageRate = 0;
        }

        // 4. 预测并格式化返回数据
        $remainingDays = ($dailyUsageRate > 0) ? floor($freeCapacity / $dailyUsageRate) : '∞';
        $fullDate = ($remainingDays !== '∞') ? date('Y-m-d', strtotime("+{$remainingDays} days")) : 'N/A';

        $dateAxis = [];
        $historicalData = [];
        foreach ($historyResult as $row) {
            $dateAxis[] = $row['record_date'];
            $historicalData[] = $row['daily_used_size'];
        }

        // 生成预测数据
        $predictedData = [];
        $lastHistoricalDate = end($historyResult)['record_date'];
        $lastHistoricalValue = end($historicalData);

        // 预测未来30天或直到容量满
        $predictionDays = 30;
        if ($remainingDays !== '∞' && $remainingDays < 30) {
            $predictionDays = (int) $remainingDays + 2; // 多显示两天以确保图表能画出充满的点
        }

        for ($i = 1; $i <= $predictionDays; $i++) {
            $predictedDate = date('Y-m-d', strtotime("$lastHistoricalDate +$i days"));
            $predictedValue = $lastHistoricalValue + ($dailyUsageRate * $i);

            $dateAxis[] = $predictedDate;
            // 预测值不能超过总容量
            if ($predictedValue > $totalCapacity) {
                $predictedData[] = $totalCapacity;
                break; // 容量已满，停止预测
            }
            $predictedData[] = $predictedValue;
        }

        return [
            'totalCapacity' => $totalCapacity,
            'warningThreshold' => $totalCapacity * 0.8, // 80% 预警线
            'remainingDays' => $remainingDays,
            'fullDate' => $fullDate,
            'dateAxis' => $dateAxis,
            'historicalData' => $historicalData,
            'predictedData' => $predictedData,
        ];
    }

    /**
     * 使用最小二乘法计算每日存储使用率的线性回归斜率
     *
     * @param array $data 包含 'record_date' 和 'daily_used_size' 的历史数据
     * @return float 每日平均使用率 (斜率 m)
     */
    private function calculateDailyUsageRate($data)
    {
        $n = count($data);
        if ($n < 2) {
            return 0; // 数据点不足
        }

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        // 将日期转换为相对天数索引 (x)，以第一个数据点为第0天
        $firstDate = new \DateTime($data[0]['record_date']);

        foreach ($data as $row) {
            $currentDate = new \DateTime($row['record_date']);
            $x = $firstDate->diff($currentDate)->days;
            $y = (float) $row['daily_used_size'];

            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        // 最小二乘法斜率公式: m = (n * Σ(xy) - Σx * Σy) / (n * Σ(x^2) - (Σx)^2)
        $denominator = ($n * $sumX2) - ($sumX * $sumX);

        if ($denominator == 0) {
            return 0; // 避免除以零，通常发生在所有x值都相同时
        }

        $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;

        return $slope;
    }

    /**
     * 获取存储报表数据
     * @param $params params
     * @return array
     */
    public function getStorageList($params)
    {
        $pfDes = require APP_PATH . 'v2/description/Pf.php';
        $allstorageStatusDes = $pfDes['STORAGESTATUS'];
        $storageTypeDes = $pfDes['STORAGETYPE'];
        $sortFields = [
            'type' => 'bs.storage_type',
            'total_capacity' => 'bs.total_size',
            'used_capacity' => 'used_size',
            'available_capacity' => 'bs.free_size',
            'status' => 'bs.status',
            'use_mode' => 'bs.use_mode', //存储用途
            'node_status' => 'bm.online_flag',
        ];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'bs.total_size';
        $order = $params['order'] ?: 'desc';
        $search = $params['search'];
        $storageType = $params['storageType'];
        $storageStatus = $params['storageStatus'];
        $storageUse = $params['storage_use'];

        $fromAndJoins = "
            FROM 
                bd_storage_resource bs
                LEFT JOIN 
                bd_node bn ON bn.node_uuid = bs.node_uuid
                LEFT JOIN 
                (SELECT online_flag, node_uuid FROM bd_module_server GROUP BY node_uuid) AS bm ON bm.node_uuid = bs.node_uuid
        ";

        $whereConditions = ['1 = 1'];
        $sqlParams = [];

        // 按名字搜索
        if (!empty($search)) {
            $whereConditions[] = 'bs.storage_nickname LIKE ?';
            $sqlParams[] = '%' . $search . '%';
        }

        // 模块类型筛选
        if (!empty($storageType)) {
            $placeholders = implode(',', array_fill(0, count($storageType), '?'));
            $whereConditions[] = "bs.storage_type IN ($placeholders)";
            $sqlParams = array_merge($sqlParams, $storageType);
        }

        // 存储状态
        if (!empty($storageStatus)) {
            $placeholders = implode(',', array_fill(0, count($storageStatus), '?'));
            $whereConditions[] = "bs.status IN ($placeholders)";
            $sqlParams = array_merge($sqlParams, $storageStatus);
        }

        // 存储用涂
        if (!empty($storageUse)) {
            $placeholders = implode(',', array_fill(0, count($storageUse), '?'));
            $whereConditions[] = "bs.use_mode IN ($placeholders)";
            $sqlParams = array_merge($sqlParams, $storageUse);
        }

        $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);

        $sqlCount = "SELECT count(*) AS total" . $fromAndJoins . $whereClause;
        $sql = "SELECT 
                    bs.storage_id, bs.storage_uuid,bs.storage_nickname, bs.storage_type, bs.total_size, bs.free_size, bs.use_mode,
                    (bs.total_size-bs.free_size) as used_size, bs.status, bn.node_nickname, bm.online_flag,
                    bn.ip
                " . $fromAndJoins . $whereClause . " ORDER BY $sort $order LIMIT $offset, $limit ";

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlParams);
        $reportHandler = new ReportHandler();

        $rows = [];
        foreach ($data as &$row) {
            $flag = (empty($row['online_flag']) || $row['online_flag'] == xphp_get_config('app', 'FLAG')['UNSET']) ? 2 : 1;

            $rows[] = array(
                'id' => $row['storage_id'],
                'storageUuid' => $row['storage_uuid'],
                'name' => $row['storage_nickname'],
                'type' => xphp_get_lang($storageTypeDes[$row['storage_type']]),
                'totalCapacity' => v2_calsize($row['total_size'], true),
                'freeCapacity' => v2_calsize($row['free_size'], true),
                'usedCapacity' => v2_calsize($row['used_size'], true),
                'storageStatus' => $row['status'],
                'storageStatusDes' => xphp_get_lang($allstorageStatusDes[$row['status']]),
                'addTime' => '--',
                'useModel' => $reportHandler->getUsemodeDes($row['use_mode']),
                'node' => $row['node_nickname'] ? $row['node_nickname'] : $row['ip'],
                'nodeStatus' => $flag
            );
        }

        return array(
            'total' => $count[0]['total'] ?? 0,
            'rows' => $rows
        );
    }

    /**
     * 根据 moduleType 和 subModuleType 获取对应的中文对象名称
     *
     * @param string|int|array $moduleType    模块类型（多个用逗号分隔）
     * @param string|int|array $subModuleType 模块类型（多个用逗号分隔）
     * @param int              $devType       设备类型（用于 VOL_CDP 类型区分卷/整机）
     * @return string 匹配的对象名称，多个用逗号连接
     */
    private function getModuleName($moduleType, $subModuleType = '', $devType = 0)
    {
        $moduleArr = xphp_get_config('module', 'MODULE_TYPE');
        if ($moduleType == $moduleArr['VOL_CDP']) {
            // 是实时，那么根据 devType 来判断
            return $devType == 1 ?
                xphp_get_lang('UI_PLATFORM_CDP_REEL_BACKUP') : xphp_get_lang('UI_PLATFORM_CDP_COMPLETE_BACKUP');
        } elseif ($moduleType == $moduleArr['DB']) {  // 数据库模块不需要判断sub_module_type
            return xphp_get_lang('UI_PLATFORM_DATABASE');
        }

        $moduleArrs = explode(',', trim($moduleType, ','));
        $subModuleArrs = explode(',', trim($subModuleType, ','));

        // 如果 moduleArrs 为空，直接返回空
        if (empty($moduleArrs)) {
            return '';
        }

        // 根据module_type和sub_module_type返回对应的对象信息
        $objectArr = [
            '2-0' => ('UI_PLATFORM_VM_VIRTUAL'), // 虚拟化
            '2-1' => ('UI_PLATFORM_VM_VIRTUAL'), // 虚拟化
            '2-2' => ('UI_PLATFORM_PRIVATE_CLOUD'), // 私有云
            '2-3' => ('UI_PLATFORM_PUBLIC_CLOUD'), // 公有云
            '5-0' => ('UI_PLATFORM_MACHINE_REEL_BACKUP'), // 定时卷（数据库中submodule_type存的0）
            '5-1' => ('UI_PLATFORM_CDP_COMPLETE_BACKUP'), // 定时整机
            '5-2' => ('UI_PLATFORM_MACHINE_REEL_BACKUP'), // 定时卷（兼容前端过滤筛选）
            '11-0' => ('UI_PLATFORM_NAS'),// 文件系列 nas
            '11-2' => ('UI_PLATFORM_NAS'),// 文件系列 nas
            '3-1' => ('UI_PLATFORM_FILES'),// 文件系列 文件
            '3-3' => ('UI_PLATFORM_HADOOP_HDFS'),// 文件系列 HADOOP
            '3-4' => ('UI_PLATFORM_OBS_STORAGE'),// 文件系列 OBS
            '4-0' => ('UI_PLATFORM_DATABASE'), // 数据库
            '14-0' => ('UI_PLATFORM_MICROSOFT365'), // Microsoft 365
            '14-1' => ('UI_PLATFORM_MICROSOFT365'), // Microsoft 365
            '28-0' => ('UI_PLATFORM_K8S'), // 容器
            '26-0' => ('UI_PLATFORM_FILES'), // 文件复制 文件
            '12-0' => ('UI_PLATFORM_DATABASE'), // 数据库复制 数据库
            '10-0' => ('UI_PLATFORM_CDP_COMPLETE_BACKUP'), // 实时 需要根据 dev_type 区分，备份类型 1卷 2整机
            '10000-0' => ('UI_PLATFORM_DATABASE'), // 友商数据库
        ];

        $result = [];
        foreach ($moduleArrs as $key => $item) {
            $subKey = !isset($subModuleArrs[$key]) ? 0 : $subModuleArrs[$key];
            $items = $item . '-' . $subKey;
            if (isset($objectArr[$items])) {
                $result[] = xphp_get_lang($objectArr[$items]);
            }
        }

        // 去重 + 合并成字符串
        $result = array_unique($result);
        return implode(',', $result);
    }

    /**
     * 获取磁带库、驱动器、磁带、装载率等统计信息
     */
    public function getTapeOverview(): array
    {
        // 获取磁带库总数
        $libraryCountSql = "SELECT count(*) as total FROM bd_tape_library";
        $libraryCount = $this->dbSelect($libraryCountSql);
        $totalLibraries = $libraryCount[0]['total'] ?? 0;

        // 获取驱动器总数
        $driverCountSql = "SELECT count(*) as total FROM bd_tape_driver";
        $driverCount = $this->dbSelect($driverCountSql);
        $totalDrivers = $driverCount[0]['total'] ?? 0;

        // 获取磁带总数
        $tapeStatsSql = "
            SELECT
                COUNT(*) AS total_tapes,
                SUM(capacity) AS total_space,
                SUM(free_space) AS free_space,
                SUM(used_space) AS used_space,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS used_tapes,
                SUM(CASE WHEN status != 2 THEN 1 ELSE 0 END) AS online_tapes,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS offline_tapes
            FROM
                bd_tape_carriage
        ";
        $tapeStats = $this->dbSelect($tapeStatsSql);

        $totalTapes = $tapeStats[0]['total_tapes'] ?? 0;
        $usedTapes = $tapeStats[0]['used_tapes'] ?? 0;
        $onlineTapes = $tapeStats[0]['online_tapes'] ?? 0;
        $offlineTapes = $tapeStats[0]['offline_tapes'] ?? 0;
        $totalSpace = $tapeStats[0]['total_space'] ?? 0;
        $freeSpace = $tapeStats[0]['free_space'] ?? 0;
        $usedSpace = $tapeStats[0]['used_space'] ?? 0;

        // 获取装载率
        $loadingRate = 0;
        if ($totalTapes > 0) {
            $loadingRate = round(($usedTapes / $totalTapes) * 100, 2);
        }

        return [
            'totalLibraries' => $totalLibraries,
            'totalDrivers' => $totalDrivers,
            'usedTapes' => (int) $usedTapes,
            'onlineTapes' => (int) $onlineTapes,
            'offlineTapes' => (int) $offlineTapes,
            'loadingRate' => $loadingRate,
            'totalSpace' => $totalSpace,
            'freeSpace' => $freeSpace,
            'usedSpace' => $usedSpace,
        ];
    }

    /**
     * 获取磁带组列表
     * @param mixed $params
     * @return void
     */
    public function getTapeGroup($params): array
    {
        $search = $params['search'];
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? 10;
        $sortFields = [
            'name' => 'btg.name',
            'status' => 'bsr.status',
            'tape_count' => 'tape_count',
            'total_capacity' => 'total_capacity',
        ];
        $sort = $sortFields[$params['sort']] ?? 'btg.id';
        $order = $params['order'] ?? 'asc';

        $fromAndJoins = "
            FROM
                bd_tape_group btg
            LEFT JOIN
                bd_tape_carriage btc ON btg.group_uuid = btc.group_uuid
            LEFT JOIN
                bd_storage_resource bsr ON btg.group_uuid = bsr.storage_uuid
        ";

        $whereConditions = ['1 = 1'];
        $sqlParams = [];

        if (!empty($search)) {
            $whereConditions[] = 'btg.name LIKE ?';
            $sqlParams[] = '%' . $search . '%';
        }

        $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);

        $groupBy = " GROUP BY btg.id, btg.group_uuid, btg.name, bsr.status";

        $sqlCount = "SELECT COUNT(*) as total FROM (SELECT btg.id " . $fromAndJoins . $whereClause . $groupBy . ") as count_subquery";
        $countResult = $this->dbSelect($sqlCount, $sqlParams);
        $total = $countResult[0]['total'] ?? 0;

        $sql = "
            SELECT
                btg.id,
                btg.group_uuid,
                btg.name,
                bsr.status,
                COUNT(btc.id) AS tape_count,
                SUM(btc.capacity) AS total_capacity,
                SUM(btc.free_space) AS available_capacity
            " . $fromAndJoins . $whereClause . "
            " . $groupBy . "
            ORDER BY
                $sort $order
            LIMIT
                $offset, $limit
        ";
        $data = $this->dbSelect($sql, $sqlParams);

        $rows = [];
        foreach ($data as $d) {
            $rows[] = [
                'id' => $d['id'],
                'groupUuid' => $d['group_uuid'],
                'name' => $d['name'],
                'status' => $d['status'],
                'tapeCount' => $d['tape_count'],
                'totalCapacity' => v2_calsize($d['total_capacity'], true),
                'availableCapacity' => v2_calsize($d['available_capacity'], true),
            ];
        }

        return [
            'rows' => $rows,
            'total' => $total
        ];
    }

    /**
     * 
     *  获取磁带备份数据明细
     * @param [type] $params
     * @return void
     */
    public function getTapeReportList($params): array
    {
        $search = $params['search'];
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? 10;
        $sortFields = [
            'name' => 'btc.name, btc.serial_number',
            'serialNumber' => 'btc.serial_number',
            'type' => 'btc.type',
            'capacity' => 'btc.capacity',
            'status' => 'btc.status',
            'usedSize' => 'btc.used_space',
            'freeSize' => 'btc.free_space',
        ];
        $sort = $sortFields[$params['sort']] ?? 'btc.id';
        $order = $params['order'] ?: 'asc';

        $fromAndJoins = "
            FROM 
                bd_tape_carriage btc
                LEFT JOIN bd_tape_group btg ON btc.group_uuid = btg.group_uuid
                LEFT JOIN bd_tape_backup_set btbs ON btc.backup_set_uuid = btbs.backup_set_uuid
                LEFT JOIN bd_tape_driver btd ON btc.lib_name = btd.lib_name
        ";

        $whereConditions = ['1 = 1'];
        $sqlParams = [];

        // 按名字搜索
        if (!empty($search)) {
            $whereConditions[] = 'btc.serial_number LIKE ?';
            $sqlParams[] = '%' . $search . '%';
        }

        // 按磁带状态搜索
        if (!empty($params['status'])) {
            $placeholders = implode(',', array_fill(0, count($params['status']), '?'));
            $whereConditions[] = 'btc.status IN (' . $placeholders . ')';
            $sqlParams = array_merge($sqlParams, $params['status']);
        }

        $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);

        $groupBy = ' GROUP BY btc.id';

        $sqlCount = "SELECT 
                        COUNT(*) AS total 
                    FROM 
                        (
                            SELECT
                                btc.id 
                            {$fromAndJoins} 
                            {$whereClause}
                            {$groupBy}
                        ) AS count_subquery";

        $sql = "SELECT 
                    btc.id, btc.name, btc.lib_name, btc.serial_number, btc.capacity,
                    btc.used_space, btc.free_space, btc.status, btc.type, btc.slot_number, btc.group_uuid,
                    btc.backup_set_uuid, btc.description, btc.detail,
                    btg.name AS group_name,
                    btbs.name AS backup_set_name, 
                    btd.name AS driver_name, btd.driver_path  
                " . $fromAndJoins . $whereClause . $groupBy . " ORDER BY $sort $order LIMIT $offset, $limit ";

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlParams);

        $rows = [];
        foreach ($data as $d) {
            $rows[] = array(
                'id' => $d['id'],
                'tapeName' => empty($d['name']) ? $d['serial_number'] : $d['name'],
                'libName' => $d['lib_name'],
                'serialNumber' => $d['serial_number'],
                'totalSize' => v2_calsize($d['capacity'], true),
                'usedSize' => v2_calsize($d['used_space'], true),
                'freeSize' => v2_calsize($d['free_space'], true),
                'tapeStatus' => $d['status'],
                'tapeType' => $d['type'],
                'slotNumber' => $d['slot_number'],
                'groupUuid' => empty($d['group_uuid']) ? '--' : $d['group_uuid'],
                'groupName' => empty($d['group_name']) ? '--' : $d['group_name'],
                'backupSetUuid' => empty($d['backup_set_uuid']) ? '--' : $d['backup_set_uuid'],
                'backupSetName' => empty($d['backup_set_name']) ? '--' : $d['backup_set_name'],
                'driverName' => empty($d['driver_name']) ? '--' : $d['driver_name'],
                'driverPath' => empty($d['driver_path']) ? '--' : $d['driver_path'],
                'description' => empty($d['description']) ? '--' : $d['description'],
                'detail' => empty($d['detail']) ? '--' : $d['detail'],
            );
        }

        return [
            'rows' => $rows,
            'total' => $count[0]['total']
        ];
        ;
    }

    /**
     * 获取节点概览
     * @return void
     */
    public function getNodeOverview(): array
    {
        $allNodeStatus = xphp_get_config('node', 'NODE_OPERATE_STATUS', 'resources');
        $abnormalStatusCode = $allNodeStatus['ABNORMAL'] ?? 4; // 异常状态码
        $offlineNodeStatusCode = $allNodeStatus['OFFLINE']; // 节点“强制离线”状态码
        $moduleOnlineFlag = xphp_get_config('app', 'FLAG')['SET']; // 模块“在线”标志

        // 使用原生SQL查询以获得最佳性能，避免N+1问题
        $sql = "
        SELECT
            COUNT(n.node_uuid) AS total_nodes,
            SUM(IF(n.status = {$abnormalStatusCode}, 1, 0)) AS abnormal_nodes,
            SUM(
                IF(
                    -- 条件1: 必须部署了模块 (module_count > 0)
                    IFNULL(gm.module_count, 0) > 0
                    -- 条件2: 节点自身状态不能是“强制离线”
                    AND n.status != {$offlineNodeStatusCode}
                    -- 条件3: 所有已部署模块都必须在线 (offline_module_count = 0)
                    AND IFNULL(gm.offline_module_count, 0) = 0,
                    1,
                    0
                )
            ) AS online_nodes_count
        FROM
            bd_node n
        LEFT JOIN
            (
                SELECT
                    node_uuid,
                    COUNT(*) AS module_count,
                    SUM(IF(online_flag != {$moduleOnlineFlag}, 1, 0)) AS offline_module_count
                FROM
                    bd_module_server
                GROUP BY
                    node_uuid
            ) AS gm ON n.node_uuid = gm.node_uuid
        ";

        $overviewData = $this->dbSelect($sql);
        if (empty($overviewData) || !isset($overviewData[0])) {
            // 如果查询失败或没有数据，返回空概览
            return [
                'totalNodes' => 0,
                'onlineNodes' => 0,
                'offlineNodes' => 0,
                'abnormalNodes' => 0,
            ];
        }

        $stats = $overviewData[0];
        // 计算离线节点数
        $offline_nodes_count = (int) $stats['total_nodes'] - (int) $stats['online_nodes_count'];

        return [
            'totalNodes' => (int) $stats['total_nodes'],
            'onlineNodes' => (int) $stats['online_nodes_count'],
            'offlineNodes' => $offline_nodes_count,
            'abnormalNodes' => (int) $stats['abnormal_nodes'] ?? 0,
        ];
    }

    /**
     * 节点使用率排行
     * @param mixed $limit
     * @return void
     */
    public function getTapeUsageTopN($limit): array
    {
        $cpuRankql = "
            SELECT
                CONCAT(bn.host_name, ' (', bn.ip, ')') AS host_name,
                bnm.cpu_usage_rate 
            FROM
                bd_node_monitor bnm
                LEFT JOIN bd_node bn ON bnm.node_uuid = bn.node_uuid 
            ORDER BY
                cpu_usage_rate DESC 
                LIMIT ?
        ";

        $memoryRankSql = "
            SELECT
                CONCAT(bn.host_name, ' (', bn.ip, ')') AS host_name,
                bnm.memory_usage_rate 
            FROM
                bd_node_monitor bnm
                LEFT JOIN bd_node bn ON bnm.node_uuid = bn.node_uuid 
            ORDER BY
                memory_usage_rate DESC 
                LIMIT ?
        ";

        $cpuRankData = $this->dbSelect($cpuRankql, [$limit]);
        $memoryRankData = $this->dbSelect($memoryRankSql, [$limit]);

        return [
            'cpuRank' => $cpuRankData,
            'memoryRank' => $memoryRankData,
        ];
    }

    /**
     * 获取各节点近期备份趋势
     * @param [type] $params
     * @return void
     */
    public function getNodeBackupTendency($params): array
    {
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $nodeUuidsParam = $params['node_uuids'];

        $nodeUuids = [];
        if (!empty($nodeUuidsParam)) {
            $nodeUuids = is_array($nodeUuidsParam) ? $nodeUuidsParam : [$nodeUuidsParam];
        } else {
            $nodeData = $this->dbSelect("SELECT node_uuid FROM bd_node", []);
            foreach ($nodeData as $row) {
                $nodeUuids[] = $row['node_uuid'];
            }
        }

        $result = array();
        if (empty($nodeUuids)) {
            return [
                'legend' => [],
                'xAxis' => [],
                'series' => [],
            ];
        }

        $placeholders = implode(',', array_fill(0, count($nodeUuids), '?'));

        $sql = "SELECT
                    bbt.real_node_uuid,
                    CONCAT(bn.host_name, bn.ip) AS node_name,
                    SUM(bbt.total_size) AS total,
                    DATE(bbt.timepoint) AS date
                FROM
                    bd_backup_timepoint AS bbt
                LEFT JOIN
                    bd_node AS bn ON bbt.real_node_uuid = bn.node_uuid
                WHERE
                    bbt.real_node_uuid IN ($placeholders)
                    AND bbt.timepoint >= ?
                    AND bbt.timepoint < DATE_ADD(?, INTERVAL 1 DAY)
                GROUP BY
                    bbt.real_node_uuid, node_name, date
                ORDER BY
                    bbt.real_node_uuid, date ASC";

        $sqlParams = array_merge($nodeUuids, [$startTime, $endTime]);

        $queryResultData = $this->dbSelect($sql, $sqlParams);

        $seriesByName = [];
        $xAxisData = [];
        $legendData = [];

        if (!empty($queryResultData)) {
            foreach ($queryResultData as $item) {
                $nodeName = $item['node_name'];
                $date = $item['date'];
                $total = $item['total'];

                // 为新节点初始化数组
                if (!isset($seriesByName[$nodeName])) {
                    $seriesByName[$nodeName] = [];
                    $legendData[] = $nodeName;
                }

                $seriesByName[$nodeName][$date] = $total;
                $xAxisData[] = $date;
            }

            $seriesData = [];
            foreach ($seriesByName as $name => $dateValues) {
                $dataPoints = [];
                // 确保每个系列在X轴的每个日期上都有一个数据点
                foreach ($xAxisData as $date) {
                    $dataPoints[] = $dateValues[$date] ?? 0;
                }

                $seriesData[] = [
                    'name' => $name,
                    'type' => 'line',
                    'stack' => 'Total',
                    'areaStyle' => new \stdClass(), // 启用区域填充
                    'emphasis' => [
                        'focus' => 'series'
                    ],
                    'data' => $dataPoints
                ];
            }
        }

        return [
            'legend' => $legendData,
            'xAxis' => $xAxisData,
            'series' => $seriesData,
        ];
    }

    /**
     * 获取节点负载趋势
     * @param mixed $params
     * @return void
     */
    public function getNodeLoadTendency($params): array
    {
        // 1. 处理输入参数
        $startTime = $params['startTime'] ?? date('Y-m-d', strtotime('-1 month'));
        $endTime = $params['endTime'] ?? date('Y-m-d');
        $nodeUuids = $params['nodeUuids'] ?? [];
        $loadMinuteType = $params['loadMinuteType'] ?? 1;

        // 动态选择负载字段
        $loadField = 'system_load_1';
        if ($loadMinuteType == 2) {
            $loadField = 'system_load_5';
        } elseif ($loadMinuteType == 3) {
            $loadField = 'system_load_15';
        }

        // 2. 如果未传入nodeUuids，则获取所有节点
        if (empty($nodeUuids)) {
            $allNodesForUuid = $this->dbSelect("SELECT node_uuid FROM bd_node");
            $nodeUuids = array_column($allNodesForUuid, 'node_uuid');
        }

        if (empty($nodeUuids)) {
            // 如果没有任何节点，返回一个空的ECharts结构
            return ['legend' => ['data' => []], 'xAxis' => ['data' => []], 'series' => []];
        }

        // 3. 获取节点详细信息 (hostname, type) 用于图例和样式
        $placeholders = implode(',', array_fill(0, count($nodeUuids), '?'));
        $nodeInfoSql = "SELECT node_uuid, host_name, node_type FROM bd_node WHERE node_uuid IN ($placeholders)";
        $nodeInfoList = $this->dbSelect($nodeInfoSql, $nodeUuids);
        $nodeInfoMap = array_column($nodeInfoList, null, 'node_uuid');

        // 4.查询监控数据
        $startTimestamp = strtotime($startTime . ' 00:00:00');
        $endTimestamp = strtotime($endTime . ' 23:59:59');
        $duration = $endTimestamp - $startTimestamp;

        // 目标数据点数量，可以根据需要调整，150是一个比较均衡的值
        $targetPoints = 150;
        // 最小采样间隔（秒），例如监控数据是每分钟一条，则最小间隔不应小于60秒
        $minInterval = 60;

        // 计算每个时间窗口的宽度（秒），并确保不小于最小间隔
        $interval = max(floor($duration / $targetPoints), $minInterval);

        $monitorSql = "SELECT
            node_uuid,
            AVG({$loadField}) as load_value,
            DATE_FORMAT(FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(monitor_time) / {$interval}) * {$interval}), '%Y-%m-%d %H:%i') as monitor_time
            FROM 
                bd_system_monitor_m
            WHERE 
                node_uuid IN ($placeholders)
                AND monitor_time BETWEEN ? AND ?
            GROUP BY
                node_uuid,
                FLOOR(UNIX_TIMESTAMP(monitor_time) / {$interval})
            ORDER BY 
                monitor_time ASC";

        $monitorParams = array_merge($nodeUuids, [date('Y-m-d H:i:s', $startTimestamp), date('Y-m-d H:i:s', $endTimestamp)]);
        $monitorData = $this->dbSelect($monitorSql, $monitorParams);

        // 5. 数据重组与格式化为ECharts结构
        $legendData = [];
        $xAxisData = [];
        $seriesMap = [];

        foreach ($nodeInfoMap as $uuid => $info) {
            $legendData[] = $info['host_name'];
            $seriesMap[$uuid] = [
                'name' => $info['host_name'],
                'type' => 'line',
                'smooth' => true,
                'data' => [],
            ];
            if ($info['node_type'] == 1) {
                $seriesMap[$uuid]['areaStyle'] = ['opacity' => 0.1];
                $seriesMap[$uuid]['markPoint'] = ['data' => [['type' => 'max', 'name' => '峰值']]];
            }
        }

        if (empty($monitorData)) {
            return [
                'legend' => ['data' => $legendData],
                'xAxis' => ['data' => []],
                'yAxis' => ['type' => 'value', 'name' => '负载指数'],
                'series' => array_values($seriesMap)
            ];
        }

        $xAxisData = array_unique(array_column($monitorData, 'monitor_time'));
        sort($xAxisData);

        $timeIndexMap = array_flip($xAxisData);
        $timeCount = count($xAxisData);

        foreach ($seriesMap as $uuid => &$series) {
            $series['data'] = array_fill(0, $timeCount, null);
        }
        unset($series);

        foreach ($monitorData as $record) {
            $uuid = $record['node_uuid'];
            $time = $record['monitor_time'];
            $value = $record['load_value'];
            if (isset($timeIndexMap[$time]) && isset($seriesMap[$uuid])) {
                $index = $timeIndexMap[$time];
                $seriesMap[$uuid]['data'][$index] = round($value, 2); // 保留两位小数
            }
        }

        // 6. 组装最终的ECharts Option对象
        $echartsOption = [
            'legend' => [
                'data' => $legendData,
                'type' => 'scroll', // 当图例过多时可以滚动
                'bottom' => 0
            ],
            'xAxis' => [
                'type' => 'category',
                'boundaryGap' => false,
                'data' => $xAxisData
            ],
            'yAxis' => [
                'type' => 'value',
                'name' => '负载指数'
            ],
            'series' => array_values($seriesMap) // 转换为索引数组
        ];

        return $echartsOption;
    }

    /**
     * 获取节点数据明细
     * @param mixed $params
     * @return void
     */
    public function getNodeReportList($params): array
    {
        $nodeHandler = new NodeHandler();
        $result = $nodeHandler->getNodes($params);

        return $result;
    }

    /**
     * 获取生产资源 - 虚拟化报表概览数据
     * @param mixed $queryBackupDataFlag
     * @return array{backup_data: int, backup_number: int, vm_platform_total: int, vm_protected_total: mixed, vm_total: mixed, vm_unprotected_total: float|int}
     */
    public function getVmOverview(): array
    {
        return $this->getVmwareOverview('virtualization');
    }

    /**
     * 获取生产资源 - 私有云报表概览数据
     * @return array
     */
    public function getPrivateVmOverview(): array
    {
        return $this->getVmwareOverview('private');
    }

    /**
     * 获取生产资源 - 公有云报表概览数据
     * @return array
     */
    public function getPublicVmOverview(): array
    {
        return $this->getVmwareOverview('public');
    }

    /**
     * 获取生产资源 - 虚拟化、私有云、公有云报表概览数据
     * @param string $type
     * @return array
     */
    private function getVmwareOverview(string $type): array
    {
        $vmGroups = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        $hypervisorTypes = [];
        $operator = 'IN';

        switch ($type) {
            case 'private':
                $hypervisorTypes = $vmGroups['openstack'];
                break;
            case 'public':
                $hypervisorTypes = $vmGroups['publiccloud'];
                break;
            case 'virtualization':
                $hypervisorTypes = array_merge($vmGroups['publiccloud'], $vmGroups['openstack']);
                $operator = 'NOT IN';
                break;
            default:
                return [];
        }

        $vmFilterClause = '0=1';
        $platformFilterClause = '0=1';
        $params = [];
        $platformParams = [];

        if (!empty($hypervisorTypes)) {
            $placeholders = implode(',', array_fill(0, count($hypervisorTypes), '?'));
            $vmFilterClause = "vv.hypervisor_type {$operator} ($placeholders)";
            $params = $hypervisorTypes;
            $platformFilterClause = "hypervisor_type {$operator} ($placeholders)";
            $platformParams = $hypervisorTypes;
        } elseif ($operator === 'NOT IN') {
            $vmFilterClause = '1=1';
            $platformFilterClause = '1=1';
        }

        $sql = "SELECT
                    (SELECT COUNT(vm.vm_uuid) FROM vm_machine vm INNER JOIN vm_vcenter vv ON vv.vcenter_uuid = vm.vcenter_uuid WHERE {$vmFilterClause}) as vm_total,
                    (SELECT COUNT(vcenter_uuid) FROM vm_vcenter WHERE {$platformFilterClause}) as vm_platform_total,
                    (SELECT COUNT(DISTINCT hypervisor_type) FROM vm_vcenter WHERE {$platformFilterClause}) as hypervisor_type_total,
                    (SELECT COUNT(DISTINCT vml.vm_uuid)

                         FROM vm_machine_list vml
                         INNER JOIN vm_vcenter vv ON vv.vcenter_uuid = vml.vcenter_uuid
                         INNER JOIN bd_task bt ON bt.task_uuid = vml.task_uuid
                         WHERE {$vmFilterClause} AND bt.task_type = 1) as vm_protected_total
                ";

        $queryParams = array_merge($params, $platformParams, $platformParams, $params);
        $overviewData = $this->dbSelect($sql, $queryParams);

        $hypervisorTypeTotal = $overviewData[0]['hypervisor_type_total'] ?? 0;
        $vmTotalNumber = $overviewData[0]['vm_total'] ?? 0;
        $vmPlatformNumberCount = $overviewData[0]['vm_platform_total'] ?? 0;
        $protectedVmTotal = $overviewData[0]['vm_protected_total'] ?? 0;
        $unprotectedVmTotal = $vmTotalNumber - $protectedVmTotal;

        $backupData = 0;
        $backupNumber = 0;

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $reportHandler = new ReportHandler();

        if (!empty($hypervisorTypes) || $operator === 'NOT IN') {
            $backupVmsParams = array_merge($params, [$moduleType]);
            $backupVmsSql = "SELECT vml.vm_uuid FROM bd_backup_timepoint bbt
                    INNER JOIN vm_machine_list vml ON bbt.task_uuid = vml.task_uuid
                    INNER JOIN vm_vcenter vv ON vml.vcenter_uuid = vv.vcenter_uuid
                    WHERE {$vmFilterClause} AND bbt.module_type = ?
                    GROUP BY vml.vm_uuid";
            $vmsWithBackup = $this->dbSelect($backupVmsSql, $backupVmsParams);

            if (!empty($vmsWithBackup)) {
                $historyDataKey = 'vm_overview_data'; // default
                if ($type === 'public') {
                    $historyDataKey = 'public_cloud_overview_data';
                } elseif ($type === 'private') {
                    $historyDataKey = 'private_cloud_overview_data';
                }

                $allVmHistoryData = $reportHandler->getAllVmHistoryData($historyDataKey);
                foreach ($vmsWithBackup as $d) {
                    if (empty($d['vm_uuid']))
                        continue;
                    $vmRunningInfo = $reportHandler->getVmRunningInfo($allVmHistoryData, $d['vm_uuid']);
                    $backupData += $vmRunningInfo['backup_data'];
                }
            }

            $backupNumParams = array_merge($params, [$moduleType]);
            $backupNumSql = "SELECT COUNT(bht.history_task_uuid) as total
                FROM bd_history_task bht
                INNER JOIN vm_machine_list vml on bht.task_uuid = vml.task_uuid
                INNER JOIN vm_vcenter vv on vml.vcenter_uuid = vv.vcenter_uuid 
                WHERE {$vmFilterClause} AND bht.module_type = ?";
            $backupNumData = $this->dbSelect($backupNumSql, $backupNumParams);
            $backupNumber = $backupNumData[0]['total'] ?? 0;
        }

        return [
            'hypervisorTypeTotal' => $hypervisorTypeTotal,
            'vmPlatformTotal' => $vmPlatformNumberCount,
            'vmTotal' => $vmTotalNumber,
            'vmProtectedTotal' => $protectedVmTotal,
            'vmUnprotectedTotal' => $unprotectedVmTotal,
            'backupNumber' => $backupNumber,
            'backupData' => $backupData,
        ];
    }

    /**
     * 获取各个虚拟化近期的备份趋势
     * @param mixed $params
     * @return array{legend: array, series: array, xAxis: array}
     */
    public function getVmwareBackupTrend($params)
    {
        // 获取所有虚拟化类型
        $vmJobInfo = new VmJobInfo();
        $hypervisorTypes = $vmJobInfo->getAllHypervisorType(['cloud_flag' => false]);
        $hypervisorTypeMap = array_column($hypervisorTypes, 'text', 'value');
        $hypervisorTypeValues = array_keys($hypervisorTypeMap);

        // 从参数中获取时间范围，默认为近一个月
        $startTimeStr = $params['start_time'] ?? date('Y-m-d 00:00:00', strtotime('-29 days'));
        $endTimeStr = $params['end_time'] ?? date('Y-m-d 23:59:59');

        // 生成近一月的日期
        $dates = [];
        $current = strtotime($startTimeStr);
        $end = strtotime($endTimeStr);
        while ($current <= $end) {
            $dates[date('Y-m-d', $current)] = date('m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        $tbDivisor = 1024 * 1024 * 1024 * 1024;

        // 计算初始备份量：计算出指定过去时间范围内（如30天前），每个虚拟化平台已经存在的总备份数据大小和总对象大小，作为图表的起始基准值
        $initialTotals = [];
        if (!empty($hypervisorTypeValues)) {
            $initialTotalsQueryParams = [$startTimeStr];
            $placeholders = implode(',', array_fill(0, count($hypervisorTypeValues), '?'));
            $initialToralSql = "
                SELECT
                    bht.submodule_type AS hypervisor_type,
                    DATE(bht.finish_time) AS backup_date,
                    SUM(bho.write_size) AS total_write_size,
                    SUM(bho.total_size) AS total_data_size
                FROM
                    bd_history_object bho
                    JOIN bd_history_task bht ON bho.history_uuid = bht.history_uuid 
                WHERE 
                    bht.finish_time < ? 
                    AND bht.submodule_type IN ($placeholders) 
                    AND bht.task_type = 1 
                    AND bht.module_type = 2 
                GROUP BY
                    hypervisor_type,
	                backup_date";

            $initialTotalsQueryParams = array_merge($initialTotalsQueryParams, $hypervisorTypeValues);
            $initialTotalsResult = $this->dbSelect($initialToralSql, $initialTotalsQueryParams);

            foreach ($initialTotalsResult as $row) {
                $initialTotals[$row['hypervisor_type']] = [
                    'total_write_size' => $row['total_write_size'] / $tbDivisor,
                    'total_data_size' => $row['total_data_size'] / $tbDivisor
                ];
            }
        }

        // 计算最近一月的日增长数据
        $dailyDataMap = [];
        if (!empty($hypervisorTypeValues)) {
            $dailyIncrementsQueryParams = [$startTimeStr, $endTimeStr];
            $placeholders = implode(',', array_fill(0, count($hypervisorTypeValues), '?'));

            $dailyIncrementsSql = "
                SELECT 
                    bht.submodule_type AS hypervisor_type,
                    DATE(bht.finish_time) AS backup_date,
                    SUM(bho.write_size) AS total_write_size,
                    SUM(bho.total_size) AS total_data_size
                FROM
                    bd_history_object bho
                    JOIN bd_history_task bht ON bho.history_uuid = bht.history_uuid 
                WHERE 
                    bht.finish_time >= ? 
                    AND bht.submodule_type IN ($placeholders) 
                    AND bht.task_type = 1 
                    AND bht.module_type = 2 
                GROUP BY
                    hypervisor_type,
	                backup_date";

            $dailyIncrementsQueryParams = array_merge($dailyIncrementsQueryParams, $hypervisorTypeValues);
            $dailyIncrementsResult = $this->dbSelect($dailyIncrementsSql, $dailyIncrementsQueryParams);

            foreach ($dailyIncrementsResult as $row) {
                $dailyDataMap[$row['hypervisor_type']][$row['backup_date']] = [
                    'total_write_size' => $row['total_write_size'] / $tbDivisor,
                    'total_data_size' => $row['total_data_size'] / $tbDivisor
                ];
            }
        }

        // Combine and process data
        $seriesData = [];
        foreach ($hypervisorTypeMap as $typeValue => $typeName) {
            $seriesData[$typeValue] = [
                'name' => $typeName,
                'data' => [],
                'cumulativeWritten' => $initialTotals[$typeValue]['total_write_size'] ?? 0,
                'cumulativeData' => $initialTotals[$typeValue]['total_data_size'] ?? 0,
            ];
        }

        $finalSeries = [];
        foreach ($seriesData as $typeValue => &$series) {
            foreach ($dates as $dateYmd => $dateMd) {
                if (isset($dailyDataMap[$typeValue][$dateYmd])) {
                    $series['cumulativeWritten'] += $dailyDataMap[$typeValue][$dateYmd]['total_write_size'];
                    $series['cumulativeData'] += $dailyDataMap[$typeValue][$dateYmd]['total_data_size'];
                }
                $series['data'][] = [
                    'value' => round($series['cumulativeWritten'], 2),
                    'totalObjectSize' => round($series['cumulativeData'], 2)
                ];
            }
            $finalSeries[] = [
                'name' => $series['name'],
                'data' => $series['data']
            ];
        }

        return [
            'legend' => array_values($hypervisorTypeMap),
            'xAxis' => array_values($dates),
            'series' => $finalSeries
        ];
    }

    /**
     * 获取虚拟化数据明细
     *
     * @param [type] $params
     * @return array
     */
    public function getVmwareDetailList($params): array
    {
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sortFields = [
            'vcenter_ip' => 'vc.vcenter_ip',
            'nickname' => 'vc.nickname',
            'register_time' => 'vc.register_time'
        ];
        $sortField = $sortFields[$params['sort']] ?? 'vc.register_time';
        $sortOrder = $params['order'] ?: 'desc';

        $where = ['vc.online_flag = 1'];
        $queryParams = [];

        // 根据平台类型筛选（虚拟化、公有云、私有云）
        if (!empty($params('platform'))) {
            $platformTypes = $this->getHypervisorTypesByPlatform($params['platform']);

            if (!empty($platformTypes)) {
                $placeholders = implode(',', array_fill(0, count($platformTypes), '?'));
                $where[] = "vc.hypervisor_type IN ($placeholders)";
                $queryParams = array_merge($queryParams, $platformTypes);
            } else {
                return ['data' => [], 'total' => 0];
            }
        }

        // 根据搜索条件筛选
        if (!empty($params['search'])) {
            $searchTerm = '%' . v2_escape_wildcard($params['search']) . '%';
            $where[] = "(vc.vcenter_ip LIKE ? OR vc.nickname LIKE ?)";
            $queryParams[] = $searchTerm;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $countSql = "SELECT COUNT(vc.vcenter_id) AS total FROM vm_center vc ($whereClause)";
        $total = $this->dbSelect($countSql, $queryParams);

        if ($total == 0) {
            return ['data' => [], 'total' => 0];
        }

        $orderBy = "ORDER BY {$sortField} {$sortOrder}";

        $sql = "
            SELECT
                vc.vcenter_id,
                vc.vcenter_uuid,
                vc.vcenter_ip,
                vc.nickname,
                vc.hypervisor_type,
                vc.version,
                (SELECT COUNT(1) FROM vm_machine vm WHERE vm.vcenter_uuid = vc.vcenter_uuid) as vm_total,
                (SELECT COUNT(DISTINCT vml.vm_uuid) FROM vm_machine_list vml INNER JOIN bd_task bt ON vml.task_uuid = bt.task_uuid WHERE vml.vcenter_uuid = vc.vcenter_uuid AND bt.task_type = 1) as vm_protected_total,
                (SELECT SUM(bho.total_size) FROM bd_history_object bho WHERE bho.parent_uuid = vc.vcenter_uuid) as total_space,
                (SELECT SUM(bho.valid_size) FROM bd_history_object bho WHERE bho.parent_uuid = vc.vcenter_uuid) as total_written
            FROM
                vm_vcenter vc
            {$whereClause}
            {$orderBy}
            LIMIT {$offset}, {$limit}
        ";

        $list = $this->dbSelect($sql, $queryParams);

        // 获取虚拟化类型名称映射
        $hypervisorTypes = (new VmJobInfo())->getAllHypervisorType(['cloud_flag' => false]);
        $hypervisorTypeMap = array_column($hypervisorTypes, 'text', 'value');

        $result = [];
        foreach ($list as $item) {
            $vmTotal = (int) $item['vm_total'];
            $protectedVmTotal = (int) $item['vm_protected_total'];
            $result[] = [
                'vcenterIp' => $item['vcenter_ip'],
                'nickname' => $item['nickname'],
                'hypervisorType' => $hypervisorTypeMap[$item['hypervisor_type']] ?? $item['hypervisor_type'],
                'version' => $item['version'],
                'vmTotal' => $vmTotal,
                'vmProtectedTotal' => $protectedVmTotal,
                'vmUnprotectedTotal' => $vmTotal - $protectedVmTotal,
                'totalSpace' => (double) ($item['total_space'] ?? 0),
                'totalWritten' => (double) ($item['total_written'] ?? 0),
            ];
        }

        return ['data' => $result, 'total' => (int) $total];
    }

    /**
     * 根据平台获取虚拟化类型
     * @param string $platform
     */
    private function getHypervisorTypesByPlatform(string $platform)
    {
        $allTypes = (new VmJobInfo())->getAllHypervisorType(['cloud_flag' => false]);
        $typeMap = [];
        foreach ($allTypes as $type) {
            $typeMap[$type['platform']][] = $type['value'];
        }

        return $typeMap[$platform] ?? [];
    }

    /**
     * 获取各虚拟化平台下的虚拟机树（支持 jsTree 懒加载）
     * @param array $params 可能包含 id, vcenterUuid, module_type 等参数
     * @return array
     * @throws \Exception
     */
    public function getVirtualMachineTree($params): array
    {
        $parentId = (isset($params['id']) && $params['id'] !== '#') ? $params['id'] : null;
        $vcenterUuid = $params['vcenterUuid'] ?? null;

        $vmConfig = xphp_get_config('vm');
        $hypervisorGroups = $vmConfig['VMHYPERVISORGROUP'];
        $publicCloudPlatforms = $hypervisorGroups['publiccloud'];
        $privateCloudPlatforms = $hypervisorGroups['privatecloud'];
        $allCloudPlatforms = array_merge($publicCloudPlatforms, $privateCloudPlatforms);
        $hypervisorNames = $vmConfig['VMHYPERVISORDES'];

        $treeData = [];

        // 初始加载 (id is null, from '#') - 只返回第一层：平台类型
        if (is_null($parentId)) {
            $vcenterPlatformType = $params['vcenterPlatformType'] ?? 'virtualization';
            $platformsToShow = [];

            switch ($vcenterPlatformType) {
                case 'public':
                    $platformsToShow = $publicCloudPlatforms;
                    break;
                case 'private':
                    $platformsToShow = $privateCloudPlatforms;
                    break;
                default: // 'virtualization'
                    // 从数据库中获取所有实际存在的平台类型
                    $sql = "SELECT DISTINCT hypervisor_type FROM vm_vcenter";
                    $typesData = $this->dbSelect($sql);
                    $allTypesInDB = array_column($typesData, 'hypervisor_type');
                    // 筛选出不属于云平台的类型
                    $platformsToShow = array_diff($allTypesInDB, $allCloudPlatforms);
                    break;
            }

            foreach ($platformsToShow as $type) {
                // 确保该平台类型有对应的名称
                if (isset($hypervisorNames[$type])) {
                    $treeData[] = [
                        'id' => $type,
                        'parent' => '#',
                        'text' => $hypervisorNames[$type],
                        'children' => true, // 告诉 jstree 这个节点可以展开
                        'state' => ['checkbox_disabled' => true], // 设置为不可勾选
                    ];
                }
            }
        }

        // 第二层加载：父节点是平台类型 (e.g., 'vmware', 'hyperv')
        // 通过检查 $parentId 是否在我们的平台名称列表里来判断
        else if (in_array($parentId, array_keys($hypervisorNames)) && !filter_var($parentId, FILTER_VALIDATE_URL) && strpos($parentId, '-') === false) {
            $sql = "SELECT vcenter_id, vcenter_ip, vcenter_uuid, nickname, hypervisor_type FROM vm_vcenter WHERE hypervisor_type = ?";
            $vcenters = $this->dbSelect($sql, [$parentId]);

            foreach ($vcenters as $vcenter) {
                $treeData[] = [
                    'id' => $vcenter['vcenter_uuid'],
                    'parent' => $vcenter['hypervisor_type'],
                    'text' => $vcenter['nickname'] ? $vcenter['nickname'] : $vcenter['vcenter_ip'],
                    'a_attr' => ['title' => $vcenter['nickname'] . ' (' . $vcenter['vcenter_ip'] . ')'],
                    'children' => true, // vCenter 节点也可以展开
                    'data' => ['vcenterUuid' => $vcenter['vcenter_uuid']], // 将 vcenter_uuid 放入 data 属性，供前端发请求时使用
                ];
            }
        }

        // 从第三层开始，vcenter_uuid 是必需的
        else {
            if (!$vcenterUuid) {
                // 如果前端没有传来 vcenter_uuid，无法继续查询，返回空
                return [];
            }

            // 第三层及以后加载：父节点是 UUID，实现分页加载
            $page = isset($params['page']) ? (int) $params['page'] : 1;
            $limit = 100;
            $offset = ($page - 1) * $limit;

            $queryParams = [$parentId, $vcenterUuid];
            $whereClause = "parent_uuid = ? AND vcenter_uuid = ? AND display_mode = 1";

            // 计算总数
            $countSql = "SELECT COUNT(*) as total FROM vm_tree WHERE {$whereClause}";
            $totalResult = $this->dbSelect($countSql, $queryParams);
            $total = $totalResult[0]['total'] ?? 0;

            if ($total > 0) {
                // 获取 hypervisor type 以确定排序规则
                $vcenterInfo = $this->dbSelect("SELECT hypervisor_type FROM vm_vcenter WHERE vcenter_uuid = ?", [$vcenterUuid]);
                $hypervisor = $vcenterInfo[0]['hypervisor_type'] ?? null;

                $vmwareGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP')['vmware'] ?? [];
                $isVmware = in_array(intval($hypervisor), $vmwareGroup);

                if ($isVmware) {
                    // VMware 环境下，使用 gbk 排序
                    $orderBy = "ORDER BY type, convert(name USING gbk) COLLATE gbk_chinese_ci";
                } else {
                    // 其他环境，使用默认排序
                    $orderBy = "ORDER BY type, name";
                }

                // 分页查询 vm_tree
                $dataSql = "SELECT uuid, name, type, parent_uuid FROM vm_tree WHERE {$whereClause} {$orderBy}, name LIMIT {$offset}, {$limit}";
                $children = $this->dbSelect($dataSql, $queryParams);

                foreach ($children as $child) {
                    $isVm = ($child['type'] === $vmConfig['VM_TREE_TYPE']['VM']);
                    $treeData[] = [
                        'id' => $child['uuid'],
                        'parent' => $child['parent_uuid'],
                        'text' => $child['name'],
                        'type' => $isVm ? 'file' : 'folder',
                        'children' => !$isVm, // 文件夹可以展开，虚拟机是叶子节点
                        'a_attr' => ['title' => $child['name']],
                        'data' => ['vcenterUuid' => $vcenterUuid]
                    ];
                }
            }

            if ($total > ($page * $limit)) {
                $treeData[] = [
                    'id' => 'load-more_' . $parentId . '_page_' . ($page + 1),
                    'parent' => $parentId,
                    'text' => '加载更多...',
                    'type' => 'file',
                    'children' => false,
                    'state' => ['checkbox_disabled' => true],
                    'li_attr' => [
                        'class' => 'load-more-node',
                        'data-page' => $page + 1,
                        'data-parent-id' => $parentId,
                        'data-vcenter-uuid' => $vcenterUuid
                    ]
                ];
            }
        }

        return $treeData;
    }

    /**
     * 获取运行趋势数据（供第三方接口调用 - DBS项目获取虚拟机近十四天运行趋势数据）
     * @param mixed $params
     * @return void
     */
    public function getReportVmBackupdata($params): array
    {
        $moduleType = $params['module_type'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];

        $sql = "SELECT
            DATE_FORMAT(finish_time, '%Y-%m-%d') AS every_day, 
            total_object_write_size, 
            details, 
            module_type, 
            error_code, 
            task_type, 
            task_uuid
        FROM bd_history_task 
        WHERE task_uuid IS NOT NULL 
        AND error_code = 0 
        AND module_type =  ?";
        $sql .= " AND finish_time >= '$startTime' AND finish_time <= '$endTime'";

        $data = $this->dbSelect($sql, array($moduleType));

        $dataArray = array();
        foreach ($data as $item) {
            $day = $item['every_day'];
            $details = json_decode($item['details'], true);
            $vmsDetails = $details['vms_details'] ?? [];

            foreach ($vmsDetails as $vmDetail) {
                $vmUuid = $vmDetail['vm_uuid'];
                $vmName = $vmDetail['vm_name']; // 从 vmsDetails 中获取 vm_name
                $key = json_encode(['vmUuid' => $vmUuid, 'vmName' => $vmName]); // 使用 JSON 编码

                if (!isset($dataArray[$key])) {
                    $dataArray[$key] = [];
                }

                $dataArray[$key][$day]['total_backup_size'] += $vmDetail['vm_valid_size'];
            }
        }

        $resultArray = [];
        foreach ($dataArray as $key => $days) {
            $vmInfo = json_decode($key, true); // 解码 JSON 字符串
            $vmUuid = $vmInfo['vmUuid'];
            $vmName = $vmInfo['vmName'];

            $totalBackupSize = 0;

            foreach ($days as $dayData) {
                $totalBackupSize += $dayData['total_backup_size'];
            }

            $resultArray[] = [
                'vm_uuid' => $vmUuid,
                'vm_name' => $vmName,
                'total_backup_size' => v2_calsize($totalBackupSize, true)
            ];
        }

        return [
            'code' => 0,
            'msg' => $resultArray
        ];
    }

    /**
     * 获取虚拟机有效占用备份存储空间大小
     * @param mixed $params
     * @return void
     */
    public function getReportVmStorageUsage($params): array
    {
        $moduleType = $params['module_type'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $taskType = $params['task_type'];

        if (!empty($taskType)) {
            $sql = "SELECT 
                    vbt.vm_uuid, 
                    vbt.vm_name, 
                    SUM(bbt.write_size) AS totalStorageUsage
                FROM 
                    bd_backup_timepoint bbt
                JOIN 
                    vm_backup_timepoint vbt ON bbt.timepoint_uuid = vbt.timepoint_uuid
                WHERE 
                    bbt.module_type = ? 
                    AND bbt.task_type = ? 
                    AND bbt.timepoint BETWEEN ? AND ?
                GROUP BY 
                    vbt.vm_uuid, vbt.vm_name";

            $sqlParams = [$moduleType, $taskType, $startTime, $endTime];
        } else {
            $sql = "SELECT 
                    vbt.vm_uuid, 
                    vbt.vm_name, 
                    SUM(bbt.write_size) AS totalStorageUsage
                FROM 
                    bd_backup_timepoint bbt
                JOIN 
                    vm_backup_timepoint vbt ON bbt.timepoint_uuid = vbt.timepoint_uuid
                WHERE 
                    bbt.module_type = ? 
                    AND bbt.timepoint BETWEEN ? AND ?
                GROUP BY 
                    vbt.vm_uuid, vbt.vm_name";

            $sqlParams = [$moduleType, $startTime, $endTime];
        }

        $data = $this->dbSelect($sql, $sqlParams);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'vm_uuid' => $row['vm_uuid'],
                'vm_name' => $row['vm_name'],
                'totalStorageUsage' => v2_calsize($row['totalStorageUsage'], true)
            ];
        }

        return [
            'code' => 0,
            'msg' => $result
        ];
    }

    /**
     * 配置报表通知
     * @param mixed $params
     * @return string
     */
    public function configureReportNotice($params): array
    {
        $templateUuids = json_decode($params['templateUuids'], true) ?: [];
        $noticeFlag = $params['emailNotifyFlag'];

        if (empty($templateUuids)) {
            return ['success' => false, 'message' => '未指定报表'];
        }

        // 开启事务，确保操作的原子性
        $this->dbBeginTransaction();

        try {
            if ($noticeFlag) {
                // 逻辑分支 A：开启或更新通知配置
                $notifyContentTypeCfg = xphp_get_config('report', 'NOTIFY_CONTENT_TYPE', 'report');
                $exportDetailTypeCfg = xphp_get_config('report', 'EXPORT_DETAIL_TYPE', 'report');
                $singleTaskObjectTypeCfg = xphp_get_config('report', 'SINGLE_TASK_OBJECT_TYPE', 'report');

                // 1. 准备通知配置的 JSON 数据
                $reportConfigData = [];
                $recEmails = $params['recEmails'];
                $noticeContentTypes = json_decode($params['noticeContentTypes'], true);
                $reportConfigData['timeStrategy'] = $params['timeStrategy'];
                $reportConfigData['noticeContentTypes'] = $params['noticeContentTypes'];

                // 获取通知明细内容
                if (in_array($notifyContentTypeCfg['DETAIL'], $noticeContentTypes)) {
                    $detailExportRange = $params['detailExportRange'];
                    $reportConfigData['detailExportRange'] = $detailExportRange;
                    if ($detailExportRange == $exportDetailTypeCfg['CUSTOM']) {
                        $reportConfigData['exportNums'] = $params['exportNums'];
                    }
                }

                // 获取单任务对象内容
                $singleObjectDetailFlag = $params['singleObjectDetailFlag'];
                $reportConfigData['singleObjectDetailFlag'] = $singleObjectDetailFlag;
                if ($singleObjectDetailFlag) {
                    $singleObjectTypes = json_decode($params['singleObjectTypes'], true);
                    $reportConfigData['singleObjectTypes'] = $params['singleObjectTypes'];
                    if (in_array($singleTaskObjectTypeCfg['HISTORY_RUN_RECORD'], $singleObjectTypes)) {
                        $historyRunRecordExportRange = $params['historyRunRecordExportRange'];
                        $reportConfigData['historyRunRecordExportRange'] = $historyRunRecordExportRange;
                        if ($historyRunRecordExportRange == $exportDetailTypeCfg['CUSTOM']) {
                            $reportConfigData['exportHistoryNums'] = $params['exportHistoryNums'];
                        }
                    }
                }

                // 附件格式
                $reportConfigData['attachmentFormats'] = $params['attachmentFormats'];
                $reportConfigJson = json_encode($reportConfigData);

                // 2. 遍历所有报表 UUID
                foreach ($templateUuids as $uuid) {
                    // 2.1 查询报表现有的 email_notice_id
                    $reportSql = "SELECT email_notice_id FROM bd_report WHERE template_uuid = ?";
                    $reportInfo = $this->dbSelect($reportSql, [$uuid]);
                    $emailNoticeId = $reportInfo[0]['email_notice_id'] ?? null;

                    if (empty($emailNoticeId)) {
                        // 2.2 新增：如果 email_notice_id 为空，则插入新记录
                        $emailNoticeFlag = 1;
                        $emailNoticeType = 2; // 2代表来自报表的邮件配置
                        $insertSql = "INSERT INTO bd_email_notice (email_notice_flag, report_config, receive_email, email_notice_type) VALUES (?, ?, ?, ?)";
                        $this->dbExec($insertSql, [$emailNoticeFlag, $reportConfigJson, $recEmails, $emailNoticeType]);

                        $newNoticeId = $this->dbLastInsertId();

                        // 2.3 更新 bd_report 表，关联新的 email_notice_id
                        $updateReportSql = "UPDATE bd_report SET email_notice_id = ?, notice_flag = 1 WHERE template_uuid = ?";
                        $this->dbExec($updateReportSql, [$newNoticeId, $uuid]);
                    } else {
                        // 2.4 更新：如果 email_notice_id 已存在，则更新现有记录
                        $updateNoticeSql = "UPDATE bd_email_notice SET report_config = ?, receive_email = ? WHERE id = ?";
                        $this->dbExec($updateNoticeSql, [$reportConfigJson, $recEmails, $emailNoticeId]);

                        // 确保报表的通知标记为开启
                        $updateReportSql = "UPDATE bd_report SET notice_flag = 1 WHERE template_uuid = ?";
                        $this->dbExec($updateReportSql, [$uuid]);
                    }
                }
            } else {
                // 逻辑分支 B：关闭通知配置
                foreach ($templateUuids as $uuid) {
                    // 1. 查询报表现有的 email_notice_id
                    $reportSql = "SELECT email_notice_id FROM bd_report WHERE template_uuid = ?";
                    $reportInfo = $this->dbSelect($reportSql, [$uuid]);
                    $emailNoticeId = $reportInfo[0]['email_notice_id'] ?? null;

                    // 2. 如果存在通知配置，则删除
                    if (!empty($emailNoticeId)) {
                        $deleteSql = "DELETE FROM bd_email_notice WHERE id = ?";
                        $this->dbExec($deleteSql, [$emailNoticeId]);
                    }

                    // 3. 更新报表状态为“关闭通知”，并清空关联 ID
                    $updateReportSql = "UPDATE bd_report SET notice_flag = 2, email_notice_id = NULL WHERE template_uuid = ?";
                    $this->dbExec($updateReportSql, [$uuid]);
                }
            }

            // 提交事务
            $this->dbCommit();

            return ['success' => true, 'message' => '通知配置成功'];
        } catch (\Exception $e) {
            // 如果任何步骤出错，回滚事务
            $this->dbRollBack();
            // 可以选择性地记录错误日志
            // xphp_log($e->getMessage(), 'error');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}