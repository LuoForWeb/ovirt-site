<?php

namespace app\v1\report\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo as JobInfos;
use xphp\Xpdf;
use app\v1\job\v0\logic\JobInfo;
use app\v1\homepage\v0\logic\homePageInfo;
use app\v1\common\logic\Report as ReportHandler;
use app\v1\vm\v0\logic\VmJobInfo;

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
        $templateTypeConfig = xphp_get_config('report', 'TEMPLATE_TYPE');
        $backupSourceTypeConfig = xphp_get_config('report', 'BACKUP_RESOURCE_TYPE');

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
                        break;
                    case $backupSourceTypeConfig['NODE']: // 节点报表模板
                        break;
                    default:
                        break;
                }
                break;
            case $templateTypeConfig['PRODUCTION_RESOURCE']: // 生产资源报表模板
                break;
            case $templateTypeConfig['DATA_PROTECT']: // 数据保护报表模板
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

        $templateTypeConfig = xphp_get_config('report', 'TEMPLATE_TYPE');
        $backupSourceTypeConfig = xphp_get_config('report', 'BACKUP_RESOURCE_TYPE');

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
                        break;
                    case $backupSourceTypeConfig['NODE']: // 节点报表模板
                        break;
                    default:
                        break;
                }
                break;
            case $templateTypeConfig['PRODUCTION_RESOURCE']: // 生产资源报表模板
                break;
            case $templateTypeConfig['DATA_PROTECT']: // 数据保护报表模板
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
            'availabilityForecast' => $params['availabilityForecase'],
            'customFields' => $params['customFields'],
            'path' => $params['path']
        ];
    }

    /**
     * 获取报表列表
     * @param mixed $params
     * @return void
     */
    public function getReportList($params): array
    {
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? 10;
        $sortFields = [
            'create_time' => 'create_time',
            'template_name' => 'template_name',
            'template_type' => 'template_type'
        ];
        $sort = $sortFields[$params['sort']] ?? 'create_time';
        $order = $params['order'] ?? 'desc';
        $search = $params['search'] ?? '';
        $groupUuid = $params['group_uuid'] ?? '';

        $templateTypes = $params['template_type'] ? explode(',', $params['template_type']) : [];

        $queryParams = [];
        $countParams = [];

        $whereClauses = [];
        $countWhereClauses = [];

        if ($groupUuid == '0') { // 获取报表模板下的所有报表
            $childGroupsSql = "SELECT group_uuid FROM bd_report_group WHERE parent_uuid = ?";
            $childGroups = $this->dbSelect($childGroupsSql, ['0']);
            $childGroupUuids = array_column($childGroups, 'group_uuid');

            if (empty($childGroupUuids)) {
                return ['total' => 0, 'rows' => []];
            }

            $placeholders = implode(',', array_fill(0, count($childGroupUuids), '?'));
            $whereClauses[] = "group_uuid IN ({$placeholders})";
            $countWhereClauses[] = "group_uuid IN ({$placeholders})";
            $queryParams = array_merge($queryParams, $childGroupUuids);
            $countParams = array_merge($countParams, $childGroupUuids);
        } else if ($groupUuid !== 'root' && !empty($groupUuid)) { // // 当 groupUuid 为 'root' 或为空时，不添加 group_uuid 过滤条件，查询所有报表
            $whereClauses[] = "group_uuid = ?";
            $countWhereClauses[] = "group_uuid = ?";
            $queryParams[] = $groupUuid;
            $countParams[] = $groupUuid;
        }

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $whereClauses[] = "template_name LIKE ?";
            $countWhereClauses[] = "template_name LIKE ?";
            $queryParams[] = $searchTerm;
            $countParams[] = $searchTerm;
        }

        if (!empty($templateTypes)) {
            $templateTypes = array_map('trim', $templateTypes);
            $placeholders = implode(',', array_fill(0, count($templateTypes), '?'));
            $whereClauses[] = "template_type IN ({$placeholders})";
            $countWhereClauses[] = "template_type IN ({$placeholders})";
            $queryParams = array_merge($queryParams, $templateTypes);
            $countParams = array_merge($countParams, $templateTypes);
        }

        $sql = "SELECT * FROM bd_report";
        $countSql = "SELECT COUNT(*) AS total FROM bd_report";

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
            $countSql .= " WHERE " . implode(' AND ', $countWhereClauses);
        }

        $sql .= " ORDER BY {$sort} {$order} LIMIT {$offset}, {$limit}";
        $totalResult = $this->dbSelect($countSql, $countParams);
        $total = $totalResult[0]['total'] ?? 0;
        $reports = $this->dbSelect($sql, $queryParams);

        $rows = [];
        if (!empty($reports)) {
            foreach ($reports as $report) {
                $rows[] = array(
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
                );
            }
        }

        return ['total' => $total, 'rows' => $rows];
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
            return ['success' => false, 'message' => '缺少group_uuid参数'];
        }

        $this->dbBeginTransaction();

        try {
            $allFoldersToDelete = $this->getDescendantFolders($groupUuid);
            $allFoldersToDelete[] = $groupUuid;

            if (!empty($allFoldersToDelete)) {
                $folderUuidsPlaceholder = implode(',', array_fill(0, count($allFoldersToDelete), '?'));

                $deleteReportsSql = "DELETE FROM bd_report WHERE group_uuid IN ({$folderUuidsPlaceholder})";
                $this->dbExec($deleteReportsSql, $allFoldersToDelete);

                $deleteGroupsSql = "DELETE FROM bd_report_group WHERE group_uuid IN ({$folderUuidsPlaceholder})";
                $this->dbExec($deleteGroupsSql, $allFoldersToDelete);
            }

            $this->dbCommit();

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
    public function deleteReport($params)
    {
        $templateUuids = $params['templateUuids'] ?? null;

        if (empty($templateUuids) || !is_array($templateUuids)) {
            return ['success' => false, 'message' => '缺少templateUuids参数或参数格式不正确'];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($templateUuids), '?'));
            $deleteSql = "DELETE FROM bd_report WHERE template_uuid IN ($placeholders)";
            $this->dbExec($deleteSql, $templateUuids);

            return ['success' => true, 'message' => '删除成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '删除失败: ' . $e->getMessage()];
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
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $storageUuidsParam = $params['storageUuids'];

        $storageUuids = [];
        if (!empty($storageUuidsParam)) {
            $storageUuids = is_array($storageUuidsParam) ? $storageUuidsParam : [$storageUuidsParam];
        } else {
            $storageData = $this->dbSelect("SELECT storage_uuid FROM bd_storage_resource", []);
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
        $pfDes = require APP_PATH . 'v1/description/Pf.php';
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
        $storageType = $params['storage_type'];
        $storageStatus = $params['storage_status'];
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
                'totalCapacity' => v1_calsize($row['total_size'], true),
                'freeCapacity' => v1_calsize($row['free_size'], true),
                'usedCapacity' => v1_calsize($row['used_size'], true),
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
                'totalSize' => v1_calsize($d['capacity'], true),
                'usedSize' => v1_calsize($d['used_space'], true),
                'freeSize' => v1_calsize($d['free_space'], true),
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
        $sql = "
            SELECT 
                COUNT(node_uuid) AS total_nodes,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS online_nodes,
                SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) AS offline_nodes,
                SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) AS missing_nodes
            FROM
                bd_node
        ";

        $data = $this->dbSelect($sql);

        if (!empty($data)) {
            return [
                'total_nodes' => $data[0]['total_nodes'],
                'online_nodes' => $data[0]['online_nodes'],
                'offline_nodes' => $data[0]['offline_nodes'],
                'missing_nodes' => $data[0]['missing_nodes'],
            ];
        }

        return [
            'totalNodes' => 0,
            'onlineNodes' => 0,
            'offlineNodes' => 0,
            'missingNodes' => 0,
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
    public function getNodeLoadTendency($params)
    {

    }

    /**
     * 获取节点数据明细
     * @param mixed $params
     * @return void
     */
    public function getNodeReportList($params)
    {

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
            $searchTerm = '%' . v1_escape_wildcard($params['search']) . '%';
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
                'total_backup_size' => v1_calsize($totalBackupSize, true)
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
                'totalStorageUsage' => v1_calsize($row['totalStorageUsage'], true)
            ];
        }

        return [
            'code' => 0,
            'msg' => $result
        ];
    }
}