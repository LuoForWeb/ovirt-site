<?php

namespace app\v1\report\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo as JobInfos;
use xphp\Images;
use xphp\Xpdf;
use app\v1\job\v0\logic\JobInfo;
use app\v1\homepage\v0\logic\homePageInfo;
use Mpdf\Tag\Em;
use app\v1\common\logic\Report as ReportHandler;
use app\v1\resources\v0\logic\Index as ResourceHandler;

class Report extends Base
{

    // <----------------------------- BEGIN CUSTOM REPORT TEMPLATE ------------------------------------------->

    /**
     * 获取报表策略列表   
     * @param array $params 数组 
     * @return array 报表策略列表
     */
    public function getTemplateList($params)
    {

        $sortFields = [
            'id' => 'brt.id',
            'template_name' => 'brt.template_name',
            'template_type' => 'brt.template_type',
            'create_time' => 'brt.create_time',
            'user_uuid' => 'brt.user_uuid',
            'email_notice_id' => 'brt.email_notice_id',
        ];

        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $search = $params['search'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $sort = $sortFields[$params['sort']] ?? 'create_time';
        $order = $params['order'] ?: 'desc';

        $sql = "SELECT distinct 
                    brt.template_name, brt.template_uuid, brt.template_type, brt.create_time, brt.user_name, brt.user_uuid,
                    brt.email_notice_id, brt.description, brt.detail,ben.email_notice_type, ben.receive_email, ben.report_config 
                FROM 
                    bd_report_template brt
                    LEFT JOIN bd_email_notice ben ON ben.id = brt.email_notice_id
                WHERE 
                    1=1";
        $sqlCount = "select count(brt.id) as total from bd_report_template brt WHERE 1=1 ";
        $sqlParams = array();
        $sqlCountParams = array();

        // 按名字搜索
        if ($this->checkEmpty($search)) {
            $sql .= ' and brt.template_name like ? ';
            $sqlCount .= 'and brt.template_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }

        if ($this->checkEmpty($startTime)) {
            $sql .= " and brt.create_time >= '$startTime' and brt.create_time <=  '$endTime'";
            $sqlCount .= "and brt.create_time >= '$startTime' and brt.create_time <=  '$endTime'";
        }

        // 获取当前用户创建的报表
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " and brt.user_uuid in ({$userUuidSql}) ";
            $sqlCount .= " and brt.user_uuid in ({$userUuidSql}) ";
        }

        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $rows = array_map(function ($row) {
            return [
                'id' => $row['id'],
                'template_name' => $row['template_name'],
                'template_type' => $row['template_type'],
                'create_time' => $row['create_time'],
                'user_name' => $row['user_name'],
                'email_notice_id' => $row['email_notice_id'],
                'description' => $row['description'],
                'detail' => $row['detail'],
                'template_uuid' => $row['template_uuid'],
                'email_notice_type' => $row['email_notice_type'],
                'receive_email' => $row['receive_email'],
                'report_config' => $row['report_config'],
                'user_uuid' => $row['user_uuid']
            ];
        }, $data);

        return array(
            'total' => $count[0]['total'],
            'rows' => $rows
        );
    }

    /**
     * 添加或修改报表模板
     * @param array $params 数组
     * @return array 数组
     */
    public function addOrModifyTemplate(array $params)
    {
        $reportHandler = new ReportHandler();
        $templatetype = $params['templateType'];
        if ($templatetype == '1') { // 虚拟化
            $overviewKeys = ['vm_platform', 'vm_number', 'protected_number', 'backup_number', 'backup_data'];
            $customFieldKeys = [
                'vm_name',
                'vm_ip',
                'ip',
                'vm_type',
                'online',
                'protect_status',
                'task',
                'last_backup_time',
                'backup_number',
                'backup_data',
                'total_object_size',
                'total_object_transport_size',
                'total_object_write_size',
                'storage_nickname'
            ];
        }

        if ($templatetype == '2') { // 客户端
            $overviewKeys = ['host_number', 'online_host', 'offline_host', 'protected_client', 'unprotected_number', 'backup_data'];
            $customFieldKeys = [
                'name',
                'ip',
                'os_type',
                'module_type',
                'user',
                'add_time',
                'protect_status',
                'task',
                'full_backup_number',
                'incre_backup_number',
                'dif_backup_number',
                'online',
                'total_object_write_size',
                'total_object_valid_size',
                'backup_data',
            ];
        }

        if ($templatetype == '3') { // CDP
            $overviewKeys = ['host_number', 'protected_number', 'unprotected_number', 'task_number', 'backup_set_number', 'backup_data'];
            $customFieldKeys = [
                'host_name',
                'ip',
                'os_type',
                'protect',
                'protect_app',
                'task',
                'add_time',
                'backup_set',
                'backup_status',
                'backup_data',
                'user',
                'last_backup_time',
                'auto_takeover',
                'storage_nickname'
            ];
        }

        if ($templatetype == '4') { // NAS
            $overviewKeys = ['device_number', 'online_number', 'offline_number', 'protected_number', 'unprotected_number', 'backup_data'];
            $customFieldKeys = [
                'device_name',
                'ip',
                'shared_path',
                'device_type',
                'add_time',
                'status',
                'auth_status',
                'protect_status',
                'backup_data',
                'task'
            ];
        }

        if ($templatetype == '5') { // 存储
            $overviewKeys = ['storage_device', 'online_number', 'offline_number', 'copy_data', 'archived_data', 'backup_data'];
            $customFieldKeys = [
                'name',
                'type',
                'total_capacity',
                'available_capacity',
                'used_capacity',
                'storage_status',
                'add_time',
                'use_model',
                'node',
                'node_status'
            ];
        }

        if ($templatetype == '6') { // 任务
            $overviewKeys = ['task_num', 'stop_task', 'success_task', 'abnormal_task', 'failed_task'];
            $customFieldKeys = [
                'task_name',
                'task_type',
                'module_type',
                'task_status',
                'speed',
                'progress',
                'create_time',
                'last_start_time',
                'uptime_time',
                'finish_time',
                'next_run_time',
                'total_object_size',
                'total_object_write_size',
                'total_object_transport_size',
                'storage_nickname',
                'create_user'
            ];
        }

        if ($templatetype == '7') { // M365
            $overviewKeys = ['app_number', 'app_online', 'app_offline', 'app_protected', 'backup_data'];
            $customFieldKeys = [
                'task_name',
                'organization_name',
                'start_time',
                'finish_time',
                'total_object_size',
                'total_object_valid_size',
                'total_object_transport_size',
                'total_object_write_size',
                'task_status',
                'user'
            ];
        }

        if ($templatetype == '8') { // 公有云
            $overviewKeys = ['vm_platform', 'vm_number', 'protected_number', 'backup_number', 'backup_data'];
            $customFieldKeys = [
                'vm_name',
                'ip',
                'vm_ip',
                'vm_type',
                'online',
                'protect_status',
                'task',
                'last_backup_time',
                'backup_number',
                'backup_data',
                'total_object_size',
                'total_object_transport_size',
                'total_object_write_size',
                'storage_nickname'
            ];
        }

        if ($templatetype == '9') { // 私有云
            $overviewKeys = ['vm_platform', 'vm_number', 'protected_number', 'backup_number', 'backup_data'];
            $customFieldKeys = [
                'vm_name',
                'ip',
                'vm_ip',
                'vm_type',
                'online',
                'protect_status',
                'task',
                'last_backup_time',
                'backup_number',
                'backup_data',
                'total_object_size',
                'total_object_transport_size',
                'total_object_write_size',
                'storage_nickname'
            ];
        }

        if ($templatetype == '10') { // 对象存储
            $overviewKeys = ['ob_total', 'ob_normal', 'ob_offline'];
            $customFieldKeys = [
                'vendor',
                'endpoint_override',
                'nickname',
                'obs_create_time',
                'status',
                'creator',
                'owner'
            ];
        }

        if ($templatetype == '11') { // Hadoop
            $overviewKeys = ['hadoop_total', 'hadoop_auth', 'hadoop_online', 'hadoop_offline'];
            $customFieldKeys = [
                'cluster_name',
                'node_number',
                'add_time',
                'auth_status',
                'online_flag'
            ];
        }

        if ($templatetype == '12') { // 文件复制
            $overviewKeys = ['file_copy_object', 'file_copy_target', 'file_copy_protected', 'task_total', 'copy_data'];
            $customFieldKeys = [
                'task_name',
                'source_object',
                'target_object',
                'start_time',
                'finish_time',
                'total_object_size',
                'total_object_valid_size',
                'total_object_transport_size',
                'total_object_write_size',
                'task_status',
                'user'
            ];
        }

        if ($templatetype == '13') { // K8S
            $overviewKeys = ['k8s_total', 'k8s_online', 'k8s_offline', 'k8s_protected', 'k8s_unprotected', 'backup_data'];
            $customFieldKeys = [
                'task_name',
                'start_time',
                'finish_time',
                'total_object_size',
                'total_object_valid_size',
                'total_object_transport_size',
                'total_object_write_size'
            ];
        }

        if ($params['templateUuid']) { // 修改模版
            $reportHandler->modifyCustomReportTemplate($params, $overviewKeys, $customFieldKeys);
        } else { // 创建模版
            $reportHandler->addCustomReportTemplate($params, $overviewKeys, $customFieldKeys);
        }
    }

    /**
     * 删除资源组
     * @param array $params 数组
     * @return array 数组
     */
    public function deleteTemplate($params)
    {
        if (!empty($params['template_uuid'])) {
            foreach ($params['template_uuid'] as $item) {
                $result[] = "'" . $item . "'";
            }
        }
        $list = implode(', ', $result);
        $this->dbBeginTransaction();
        $sqlId = "select email_notice_id from bd_report_template where template_uuid in ($list)";
        $sqlIdParams = array();
        $dataId = $this->dbSelect($sqlId, $sqlIdParams);
        if (!empty($dataId)) {
            foreach ($dataId as $item) {
                $idsArray[] = $item['email_notice_id'];
            }
            $idList = implode(', ', $idsArray);
            $sql1 = "delete from bd_email_notice where id in ($idList)";
            $result1 = $this->dbExec($sql1, []);
        } else {
            $result1 = true;
        }

        $sql = "delete from bd_report_template where template_uuid in ($list)";
        $result = $result1 && $this->dbExec($sql, []);
        //删除资源组返回结果到界面
        if ($result) {
            $this->dbCommit();
            return $this->muOpResult(true, xphp_get_lang('UI_REPORT_DELETE'));
        } else {
            $this->dbRollBack();
            return $this->muOpResult(false, xphp_get_lang('UI_REPORT_DELETE'));
        }
    }

    // <----------------------------- END CUSTOM REPORT TEMPLATE --------------------------------------------->



    // <----------------------------- BEGIN CUSTOM REPORT OVERVIEW ------------------------------------------->

    /**
     * 获取模板概览数据
     * @param array $params 数组
     * @return array 数组
     */
    public function getReportTemplateOverview($params, $emailId = '', $fromCliProcessFlag = false)
    {
        $module = $params['overview_uuid'];
        $userUUID = '';
        if ($fromCliProcessFlag) { // 如果是来自cli进程调用则需要通过邮件id获取user_uuid
            $sql = "SELECT user_uuid from bd_report_template WHERE email_notice_id = ?";
            $userUuidData = $this->dbSelect($sql, [$emailId]);
            $userUUID = $userUuidData[0]['user_uuid'];
        }

        if ($module == '1') {
            $overviewList = $this->getVmOverview($userUUID);
        }

        if ($module == '2') {
            $overviewList = $this->getAgentOverview($userUUID);
        }

        if ($module == '3') {
            $overviewList = $this->getCdpOverview($userUUID);
        }

        if ($module == '4') {
            $overviewList = $this->getNasOverview($userUUID);
        }

        if ($module == '5') {
            $overviewList = $this->getStorageOverview($userUUID);
        }

        if ($module == '6') {
            $overviewList = $this->getTaskOverview($userUUID);
        }

        if ($module == '7') {
            $overviewList = $this->getAppOverview($userUUID);
        }

        if ($module == '8') {
            $overviewList = $this->getPublicOverview($userUUID);
        }

        if ($module == '9') {
            $overviewList = $this->getPrivateOverview($userUUID);
        }

        if ($module == '10') {
            $overviewList = $this->getDbCopyOverview($userUUID);
        }

        if ($module == '34') {
            $overviewList = $this->getObsOverview($userUUID);
        }

        if ($module == '11') {
            $overviewList = $this->getHadoopOverview($userUUID);
        }

        if ($module == '26') {
            $overviewList = $this->getFileCopyOverview($userUUID);
        }

        if ($module == '28') {
            $overviewList = $this->getKubernetsOverview($userUUID);
        }

        if ($module == '29') {
            $overviewList = $this->getDbProtectOverview($userUUID);
        }

        return array(
            'overviewList' => $overviewList,
        );
    }

    /**
     * 获取节点信息
     * @return void
     */
    public function getNodeInfo()
    {
        $reportHandler = new ReportHandler();
        $sql = "select ip,DATEDIFF(NOW(), register_time) AS run_time from bd_node where node_type = 1";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $runTime = $data[0]['run_time'];
        $ip = $data[0]['ip'];
        $nodeNumberSql = "select node_uuid from bd_node  where node_type != 1";
        $nodeNumberData = $this->dbSelect($nodeNumberSql);
        $nodeTotalNumber = count($nodeNumberData); //
        $abnormalNumber = 0;
        $normalNumber = 0;
        foreach ($nodeNumberData as $d) {
            $nodeAllStatus = $reportHandler->getNodeAllStatus($d['node_uuid']);
            if ($nodeAllStatus['flag']) {
                $normalNumber += 1;
            } else {
                $abnormalNumber += 1;
            }
        }
        $backsql = "select module_type,total_object_completed_size,start_time,submodule_type,task_type 
                    from bd_history_task bht";
        $backdata = $this->dbSelect($backsql);
        $backupDataValue = 0;
        if (!empty($backdata)) {
            foreach ($backdata as $d) {
                $backupDataValue += $d['total_object_completed_size'];
            }
        }
        $appOverviewData = array(
            'master_ip' => $ip,
            'run_rime' => $runTime,
            'node_number' => $nodeTotalNumber,
            'normal_number' => $normalNumber,
            'abnormal_number' => $abnormalNumber,
            'backup_data' => $backupDataValue
        );
        return $appOverviewData;
    }

    /**
     * 获取虚拟机报表概览
     *
     * @param boolean $queryBackupDataFlag 是否查询备份数据
     * @return void
     */
    public function getVmOverview($userUUID = ''): array
    {
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $privateCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $cloudTypesStr = implode("','", $privateCloudTypes);
        $vmNumberSql = "SELECT 
                            COUNT( vm.vm_uuid ) total 
                        FROM 
                            vm_machine vm
	                    INNER JOIN 
                            vm_vcenter vv ON vv.vcenter_uuid = vm.vcenter_uuid 
                        WHERE 
                            vv.hypervisor_type NOT IN ('" . $publicCloudTypesStr . "') AND vv.hypervisor_type NOT IN ('" . $cloudTypesStr . "')";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['VM'], 'vm.vm_uuid');
            $vmNumberSql .= " AND ({$resourceUuidSql}) ";
        }
        $vmNumberData = $this->dbSelect($vmNumberSql);
        $vmTotalNumber = $vmNumberData[0]['total']; //系统虚拟机总个数

        $vmPlatformNumberSql = "select * from vm_vcenter where hypervisor_type not in ('" . $cloudTypesStr . "') and hypervisor_type not in ('" . $publicCloudTypesStr . "')";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $vmPlatformNumberSql .= " AND user_uuid IN ({$userUuidSql}) ";
        }
        $vmPlatformNumberData = $this->dbSelect($vmPlatformNumberSql);
        $vmPlatformNumberCount = count($vmPlatformNumberData);  //虚拟化平台总数

        $protectedVmNumberSql = "SELECT 
                            COUNT(DISTINCT vml.vm_uuid ) total 
                        FROM 
                            vm_machine_list vml
	                        INNER JOIN vm_vcenter vv ON vv.vcenter_uuid = vml.vcenter_uuid 
                            INNER JOIN bd_task bt ON bt.task_uuid = vml.task_uuid
                        WHERE 
                            vv.hypervisor_type NOT IN ('" . $publicCloudTypesStr . "') AND vv.hypervisor_type NOT IN ('" . $cloudTypesStr . "')
                            AND bt.task_type = 1 
                            AND vml.vm_uuid IN (
                            SELECT
                                vm.vm_uuid 
                            FROM
                                vm_machine vm
                                INNER JOIN vm_vcenter vv2 ON vm.vcenter_uuid = vv2.vcenter_uuid 
                            WHERE
                                vv2.hypervisor_type NOT IN ('" . $publicCloudTypesStr . "') AND vv2.hypervisor_type NOT IN ('" . $cloudTypesStr . "')  
                            )";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['VM'], 'vml.vm_uuid');
            $protectedVmNumberSql .= " AND ({$resourceUuidSql}) ";
        }
        $protectedVmNumberData = $this->dbSelect($protectedVmNumberSql);
        $protectedVmTotal = $protectedVmNumberData[0]['total'];  //受保护的虚拟机总数
        $unprotectedVmTotal = $vmTotalNumber - $protectedVmTotal;  //未保护虚拟机个数

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];

        $sql = "SELECT vml.vm_uuid FROM bd_backup_timepoint bbt
                LEFT JOIN vm_machine_list vml ON bbt.task_uuid = vml.task_uuid
                LEFT JOIN vm_vcenter vv ON vml.vcenter_uuid = vv.vcenter_uuid WHERE  
                vml.vm_uuid IS NOT NULL AND module_type = ?";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND bbt.user_uuid IN ({$userUuidSql}) ";
        }
        $sql .= " GROUP BY vm_uuid";
        $data = $this->dbSelect($sql, array($moduleType));

        $backupData = 0;
        $backupNumber = 0;
        $reportHandler = new ReportHandler();

        $allVmHistoryData = $reportHandler->getAllVmHistoryData('vm_overview_data');

        if (!empty($data)) {
            foreach ($data as $d) {
                $vmRunningInfo = $reportHandler->getVmRunningInfo($allVmHistoryData, $d['vm_uuid']);
                $backupData += $vmRunningInfo['backup_data'];
            }
        }

        $backupNumber = $reportHandler->getModuleBackupNum($moduleType, $userUUID);

        $vmOverviewData = array(
            'vm_platform_total' => $vmPlatformNumberCount,
            'vm_total' => $vmTotalNumber,
            'vm_protected_total' => $protectedVmTotal,
            'vm_unprotected_total' => $unprotectedVmTotal,
            'backup_number' => $backupNumber,
            'backup_data' => $backupData,
        );

        return $vmOverviewData;
    }

    /**
     * 获取私有云概览数据
     * @param mixed $queryBackupDataFlag 是否查询备份数据
     * @return 
     */
    public function getPrivateOverview($userUUID = '')
    {

        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $vmNumberSql = "select * from vm_machine vm left join vm_vcenter vv on vm.vcenter_uuid = vv.vcenter_uuid where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['PRIVATE_CLOUD'], 'vm.vm_uuid');
            $vmNumberSql .= " AND ({$resourceUuidSql}) ";
        }
        $vmNumberData = $this->dbSelect($vmNumberSql);
        $vmTotalNumber = count($vmNumberData); //公有云虚拟机总个数

        $cloudNumberSql = "select * from vm_vcenter where hypervisor_type in ('" . $publicCloudTypesStr . "')";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $cloudNumberSql .= " AND user_uuid IN ({$userUuidSql}) ";
        }
        $cloudNumberData = $this->dbSelect($cloudNumberSql);
        $cloudTotalNumber = count($cloudNumberData); //公有云平台

        $protectedVmNumberSql = "select distinct vm_uuid from vm_machine_list vml left join vm_vcenter vv on vml.vcenter_uuid = vv.vcenter_uuid where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $protectedVmNumberSql .= " AND user_uuid IN ({$userUuidSql}) ";
        }
        $protectedVmNumberData = $this->dbSelect($protectedVmNumberSql);
        $protectedVmTotal = count($protectedVmNumberData);  //受保护的虚拟机总数
        $unprotectedVmTotal = $vmTotalNumber - $protectedVmTotal;  //未保护虚拟机个数

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];

        $sql = "SELECT vml.vm_uuid FROM bd_backup_timepoint bbt
                LEFT JOIN vm_machine_list vml ON bbt.task_uuid = vml.task_uuid
                LEFT JOIN vm_vcenter vv ON vml.vcenter_uuid = vv.vcenter_uuid WHERE vv.hypervisor_type IN ('" . $publicCloudTypesStr . "')
                AND module_type = ?";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND bbt.user_uuid IN ({$userUuidSql}) ";
        }
        $sql .= " GROUP BY vm_uuid";
        $data = $this->dbSelect($sql, array($moduleType));

        $backupData = 0;
        $backupNumber = 0;
        $reportHandler = new ReportHandler();

        $allVmHistoryData = $reportHandler->getAllVmHistoryData('public_cloud_overview_data');

        if (!empty($data)) {
            foreach ($data as $d) {
                $vmRunningInfo = $reportHandler->getVmRunningInfo($allVmHistoryData, $d['vm_uuid']);
                $backupData += $vmRunningInfo['backup_data'];
            }
        }

        $sqlCount = "select * from bd_history_task bht
                left join vm_machine_list vml on bht.task_uuid = vml.task_uuid
                left join vm_vcenter vv on vml.vcenter_uuid = vv.vcenter_uuid where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')
                and module_type = ?";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sqlCount .= " AND bht.user_uuid IN ({$userUuidSql}) ";
        }
        $dataCount = $this->dbSelect($sqlCount, array($moduleType));
        $backupNumber = count($dataCount);  //备份次数

        $vmOverviewData = array(
            'vm_number' => $vmTotalNumber,
            'protected_number' => $protectedVmTotal,
            'vm_platform' => $cloudTotalNumber,
            'backup_number' => $backupNumber,
            'backup_data' => $backupData,
            'unprotected_number' => $unprotectedVmTotal,
        );
        return $vmOverviewData;
    }

    /**
     * 获取公有云概览数据
     * @param mixed $queryBackupDataFlag 是否查询备份数据
     * @return 
     */
    public function getPublicOverview($userUUID = '')
    {
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $vmNumberSql = "select * from vm_machine vm left join vm_vcenter vv on vm.vcenter_uuid = vv.vcenter_uuid where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['PUBLIC_CLOUD'], 'vm.vm_uuid');
            $vmNumberSql .= " AND ({$resourceUuidSql}) ";
        }
        $vmNumberData = $this->dbSelect($vmNumberSql);
        $vmTotalNumber = count($vmNumberData); //公有云虚拟机总个数
        $cloudNumberSql = "select * from vm_vcenter where hypervisor_type in ('" . $publicCloudTypesStr . "')";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $cloudNumberSql .= " AND user_uuid IN ({$userUuidSql}) ";
        }
        $cloudNumberData = $this->dbSelect($cloudNumberSql);
        $cloudTotalNumber = count($cloudNumberData); //公有云平台
        $protectedVmNumberSql = "select distinct vm_uuid from vm_machine_list vml left join vm_vcenter vv on vml.vcenter_uuid = vv.vcenter_uuid where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['PUBLIC_CLOUD'], 'vml.vm_uuid');
            $protectedVmNumberSql .= " AND ({$resourceUuidSql}) ";
        }
        $protectedVmNumberData = $this->dbSelect($protectedVmNumberSql);
        $protectedVmTotal = count($protectedVmNumberData);  //受保护的虚拟机总数
        $unprotectedVmTotal = $vmTotalNumber - $protectedVmTotal;  //未保护虚拟机个数

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];

        $sql = "SELECT vml.vm_uuid FROM bd_backup_timepoint bbt
                LEFT JOIN vm_machine_list vml ON bbt.task_uuid = vml.task_uuid
                LEFT JOIN vm_vcenter vv ON vml.vcenter_uuid = vv.vcenter_uuid WHERE vv.hypervisor_type in ('" . $publicCloudTypesStr . "')
                and module_type = ?";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND bbt.user_uuid IN ({$userUuidSql}) ";
        }
        $sql .= " GROUP BY vm_uuid";
        $data = $this->dbSelect($sql, array($moduleType));

        $backupData = 0;
        $backupNumber = 0;
        $reportHandler = new ReportHandler();

        $allVmHistoryData = $reportHandler->getAllVmHistoryData('public_cloud_overview_data');

        if (!empty($data)) {
            foreach ($data as $d) {
                $vmRunningInfo = $reportHandler->getVmRunningInfo($allVmHistoryData, $d['vm_uuid']);
                $backupData += $vmRunningInfo['backup_data'];
            }
        }

        $sqlCount = "select * from bd_history_task bht
                left join vm_machine_list vml on bht.task_uuid = vml.task_uuid
                left join vm_vcenter vv on vml.vcenter_uuid = vv.vcenter_uuid where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')
                and module_type = ?";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sqlCount .= " AND bht.user_uuid IN ({$userUuidSql}) ";
        }
        $dataCount = $this->dbSelect($sqlCount, array($moduleType));
        $backupNumber = count($dataCount);  //备份次数

        $vmOverviewData = array(
            'vm_number' => $vmTotalNumber,
            'protected_number' => $protectedVmTotal,
            'vm_platform' => $cloudTotalNumber,
            'backup_number' => $backupNumber,
            'backup_data' => $backupData,
            'unprotected_number' => $unprotectedVmTotal,
        );
        return $vmOverviewData;
    }

    /**
     * 获取公有云平台报表概览
     * @return object  概览数据
     */
    public function getPublicCloud()
    {
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $privateCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'];

        // 获取公有云平台总数
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $cloudNumberSql = "select distinct vv.vcenter_id from vm_vcenter vv LEFT JOIN vm_machine vm ON vv.vcenter_uuid = vm.vcenter_uuid where hypervisor_type in ('" . $publicCloudTypesStr . "')";
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['PUBLIC_CLOUD'], 'vm.vm_uuid');
            $cloudNumberSql .= " AND ({$resourceUuidSql}) ";
        }

        $cloudNumberData = $this->dbSelect($cloudNumberSql);
        $publicCloudTotal = count($cloudNumberData); //公有云平台总数

        // 获取私有云平台总数
        $privateCloudTypesStr = implode("','", $privateCloudTypes);
        $cloudClientNumberSql = "select distinct vv.vcenter_id from vm_vcenter vv LEFT JOIN vm_machine vm ON vv.vcenter_uuid = vm.vcenter_uuid where hypervisor_type in ('" . $privateCloudTypesStr . "')";
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['PRIVATE_CLOUD'], 'vm.vm_uuid');
            $cloudClientNumberSql .= " AND ({$resourceUuidSql}) ";
        }
        $cloudClientNumberData = $this->dbSelect($cloudClientNumberSql);
        $privateCloudTotal = count($cloudClientNumberData);//私有云平台总数

        // 获取公有云平台下的虚拟机总数
        $publicCloudVmNumberSql = "SELECT
                                        COUNT(vm.vm_uuid) total
                                    FROM
                                        vm_machine vm
                                        INNER JOIN vm_vcenter vv ON vv.vcenter_uuid = vm.vcenter_uuid 
                                    WHERE
                                        hypervisor_type IN ('" . $publicCloudTypesStr . "')";

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['PUBLIC_CLOUD'], 'vm.vm_uuid');
            $publicCloudVmNumberSql .= " AND ({$resourceUuidSql}) ";
        }

        $publicCloudVmNumberData = $this->dbSelect($publicCloudVmNumberSql);
        $publicCloudVmTotal = $publicCloudVmNumberData[0]['total'];

        // 获取公有云平台下受保护的虚拟机数(查出的虚拟机实例为保证不是脏数据需要再做一次过滤)
        $protectedPublicCloudVmNumberSql = "SELECT
                                                COUNT( DISTINCT vml.vm_uuid ) total 
                                            FROM
                                                vm_machine_list vml
                                                INNER JOIN vm_vcenter vv ON vv.vcenter_uuid = vml.vcenter_uuid
                                                INNER JOIN bd_task bt ON bt.task_uuid = vml.task_uuid 
                                            WHERE
                                                vv.hypervisor_type IN ('" . $publicCloudTypesStr . "') 
                                                AND bt.task_type = 1 
                                                AND vml.vm_uuid IN (
                                                SELECT
                                                    vm.vm_uuid 
                                                FROM
                                                    vm_machine vm
                                                    INNER JOIN vm_vcenter vv2 ON vm.vcenter_uuid = vv2.vcenter_uuid 
                                                WHERE
                                                vv2.hypervisor_type IN ('" . $publicCloudTypesStr . "')  
                                                )";
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['PUBLIC_CLOUD'], 'vml.vm_uuid');
            $protectedPublicCloudVmNumberSql .= " AND ({$resourceUuidSql}) ";
        }
        $protectedPublicCloudVmData = $this->dbSelect($protectedPublicCloudVmNumberSql);
        $protectedPublicCloudVmTotal = $protectedPublicCloudVmData[0]['total'];

        // 获取私有云平台下的虚拟机总数
        $privateCloudVmNumberSql = "SELECT
                                        COUNT( vm.vm_uuid ) total 
                                    FROM
                                        vm_machine vm
                                        INNER JOIN vm_vcenter vv ON vv.vcenter_uuid = vm.vcenter_uuid 
                                    WHERE
                                        hypervisor_type IN ('" . $privateCloudTypesStr . "')";

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['PRIVATE_CLOUD'], 'vm.vm_uuid');
            $privateCloudVmNumberSql .= " AND ({$resourceUuidSql}) ";
        }
        $privateCloudVmNumberData = $this->dbSelect($privateCloudVmNumberSql);
        $privateCloudVmTotal = $privateCloudVmNumberData[0]['total'];

        // 获取私有云平台下受保护的虚拟机数（查出的虚拟机实例为保证不是脏数据需要再做一次过滤）
        $protectedPrivateCloudVmNumberSql = "SELECT
                                                COUNT( DISTINCT vml.vm_uuid ) total 
                                            FROM
                                                vm_machine_list vml
                                                INNER JOIN vm_vcenter vv ON vv.vcenter_uuid = vml.vcenter_uuid
                                                INNER JOIN bd_task bt ON bt.task_uuid = vml.task_uuid 
                                            WHERE
                                                vv.hypervisor_type IN ('" . $privateCloudTypesStr . "') 
                                                AND bt.task_type = 1 
                                                AND vml.vm_uuid IN (
                                                SELECT
                                                    vm.vm_uuid 
                                                FROM
                                                    vm_machine vm
                                                    INNER JOIN vm_vcenter vv2 ON vm.vcenter_uuid = vv2.vcenter_uuid 
                                                WHERE
                                                vv2.hypervisor_type IN ('" . $privateCloudTypesStr . "')  
                                                )";

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['PRIVATE_CLOUD'], 'vml.vm_uuid');
            $protectedPrivateCloudVmNumberSql .= " AND ({$resourceUuidSql}) ";
        }
        $protectedPrivateCloudVmData = $this->dbSelect($protectedPrivateCloudVmNumberSql);
        $protectedPrivateCloudVmTotal = $protectedPrivateCloudVmData[0]['total'];

        $cloudOverviewData = array(
            'public_cloud_total' => $publicCloudTotal,
            'public_cloud_vm_total' => $publicCloudVmTotal,
            'public_cloud_vm_protected_total' => $protectedPublicCloudVmTotal,
            'public_cloud_vm_unprotected_total' => $publicCloudVmTotal - $protectedPublicCloudVmTotal,
            'private_cloud_total' => $privateCloudTotal,
            'private_cloud_vm_total' => $privateCloudVmTotal,
            'private_cloud_vm_protected_total' => $protectedPrivateCloudVmTotal,
            'private_cloud_vm_unprotected_total' => $privateCloudVmTotal - $protectedPrivateCloudVmTotal,
        );

        return $cloudOverviewData;
    }

    /**
     * 获取主机概览数据
     * @return object  概览数据
     */
    public function getAgentOverview($userUUID = ''): array
    {
        $sql = "SELECT online_flag FROM bd_agent WHERE agent_type NOT IN (3, 4)";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }

        $data = $this->dbSelect($sql);
        $totalClientNumber = count($data);
        $onlineClientNumber = 0;
        $offlineClientNumber = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                if ($d['online_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                    $onlineClientNumber += 1;
                } else {
                    $offlineClientNumber += 1;
                }
            }
        }
        $sqlBackup = "select sum(total_size) as total, user_uuid from bd_backup_timepoint where module_type != 2";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sqlBackup .= " and user_uuid in ({$userUuidSql}) ";
        }

        $dataBackup = $this->dbSelect($sqlBackup);
        $sqlCdpBackup = "select sum(backup_file_size + log_file_total_size) as total from cdp_vol_backup_vol_set";
        $dataCdpBackup = $this->dbSelect($sqlCdpBackup);
        $reportHandler = new ReportHandler();
        $protectedClient = $reportHandler->getTaskAgentNumber($userUUID);

        $agentOverviewData = array(
            'host_number' => $totalClientNumber,
            'online_host' => $onlineClientNumber,
            'offline_host' => $offlineClientNumber,
            'backup_data' => $dataBackup[0]['total'] + $dataCdpBackup[0]['total'],
            'protected_client' => $protectedClient,
            'unprotected_client' => $totalClientNumber - $protectedClient,
        );

        return $agentOverviewData;
    }

    /** 
     * 获取实时容灾概览数据 
     * @return array  概览数据
     */
    public function getCdpOverview($userUUID = '')
    {
        $sql = "select distinct master_agent_uuid from cdp_vol_task";
        $data = $this->dbSelect($sql);
        $protectedClientTotal = count($data);

        $allAgentSql = "select agent_uuid from bd_agent WHERE agent_type = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'agent_uuid');
            $allAgentSql .= " AND ($resourceUuidSql) ";
        }
        $allAgentData = $this->dbSelect($allAgentSql);
        $unprotectedClientTotal = count($allAgentData) - $protectedClientTotal;
        if ($unprotectedClientTotal < 0) {
            $unprotectedClientTotal = 0;
        }
        $backupData = 0;
        $volSetSql = "select backup_agent_id,backup_file_size,log_file_total_size from cdp_vol_backup_vol_set";
        $backupSetIdArray = array();
        $volData = $this->dbSelect($volSetSql);
        if (!empty($volData)) {
            foreach ($volData as $d) {
                $backupData += $d['backup_file_size'] + $d['log_file_total_size'];
                $backupSetIdArray[] = $d['backup_agent_id'];
            }
        }
        $hostNumber = $protectedClientTotal + $unprotectedClientTotal;
        $backupSetNumber = count($backupSetIdArray);
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        $reportHandler = new ReportHandler();
        $taskNumber = $reportHandler->getModuleTaskNumber($moduleType, $userUUID);
        $cdpOverviewData = array(
            'host_number' => count($allAgentData),
            'protected_total' => $protectedClientTotal,
            'unprotected_total' => $unprotectedClientTotal,
            'backup_data' => $backupData,
            'backup_set_number' => $backupSetNumber,
            'task_number' => $taskNumber
        );

        return $cdpOverviewData;
    }

    /**
     * 获取nas概览数据
     * @return array  概览数据
     */
    public function getNasOverview($userUUID = '')
    {
        $sql = "SELECT nas_status FROM nas_storage_resource WHERE 1 = 1";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['NAS'], 'nas_uuid');
            $sql .= " AND ({$resourceUuidSql}) ";
        }

        $data = $this->dbSelect($sql);
        $nasDeviceDum = count($data);
        $onlineNumber = 0;
        $offlineNumber = 0;
        $protectedNasDevice = 0;
        $unprotectedNasDevice = 0;

        if (!empty($data)) {
            foreach ($data as $d) {
                $nasStatus = $d['nas_status'];
                if ($nasStatus == 1) {
                    $onlineNumber += 1;
                }
            }
        }
        $offlineNumber = $nasDeviceDum - $onlineNumber;
        $nasTaskSql = "SELECT DISTINCT
                            nt.nas_uuid 
                        FROM
                            nas_task nt
                            LEFT JOIN bd_task bt ON bt.task_uuid = nt.task_uuid 
                        WHERE
                            1 = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['NAS'], 'nt.nas_uuid');
            $nasTaskSql .= " AND ({$resourceUuidSql}) ";
        }
        $nasTaskData = $this->dbSelect($nasTaskSql);
        $protectedNasDevice = count($nasTaskData);

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['NAS'];
        $reportHandler = new ReportHandler();
        $backupData = $reportHandler->getModuleBackupData($moduleType, $userUUID);
        $unprotectedNasDevice = $nasDeviceDum - $protectedNasDevice;  //未保护NAS设备总数
        $taskNumber = $reportHandler->getModuleTaskNumber($moduleType, $userUUID);

        $nasOverviewData = array(
            'device_number' => $nasDeviceDum,
            'online_number' => $onlineNumber,
            'offline_number' => $offlineNumber,
            'backup_data' => $backupData,
            'protected_number' => $protectedNasDevice,
            'unprotected_number' => $unprotectedNasDevice,
            'nas_task' => $taskNumber,
        );
        return $nasOverviewData;
    }

    /**
     * 获取存储概览数据
     * @return array  概览数据
     */
    public function getStorageOverview($userUUID = '')
    {
        // 通过 bd_storage_resource 获取 存储总数、在线存储和离线存储
        $sql = "SELECT status,total_size,free_size,storage_uuid FROM bd_storage_resource WHERE storage_type NOT IN (14) AND lan_free_flag = 2";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'],
                'storage_uuid'
            );
            $sql .= " AND ({$resourceUuidSql}) ";
        }
        $data = $this->dbSelect($sql);
        $totalStorageDevices = count($data);
        $onlineNumber = 0;
        $offlineNumber = 0;
        $usedStorage = 0;
        $unusedStorage = 0;
        $totalStorageSize = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                $totalStorageSize += $d['total_size'];
                $usedStorage += $d['total_size'] - $d['free_size'];
                $unusedStorage += $d['free_size'];
                if ($d['status'] == xphp_get_config('resource', 'STORAGE_STATUS')['ONLINE']) {
                    $onlineNumber += 1;
                } else {
                    $offlineNumber += 1;
                }
            }
        }

        // 通过 bd_backup_timepoint 获取 备份数据、副本数据和归档数据
        $backupSql = "SELECT total_object_completed_size FROM bd_history_task WHERE task_type IN (1, 28, 56)";
        $backupData = $this->dbSelect($backupSql);
        $backupCount = 0;
        foreach ($backupData as $i) {
            $backupCount += $i['total_object_completed_size'];
        }

        $copySql = "SELECT total_object_completed_size FROM bd_history_task WHERE task_type = 17";
        $copyData = $this->dbSelect($copySql);
        $copyCount = 0;
        foreach ($copyData as $i) {
            $copyCount += $i['total_object_completed_size'];
        }

        $archivedSql = "SELECT total_object_completed_size FROM bd_history_task WHERE task_type = 19";
        $archivedData = $this->dbSelect($archivedSql);
        $archivedCount = 0;
        foreach ($archivedData as $i) {
            $archivedCount += $i['total_object_completed_size'];
        }

        // 获取副本存储个数和副本存储已用容量
        $copyStorageSql = "SELECT 
                                storage_uuid, 
                                SUM(write_size) AS total_write_size
                            FROM 
                                bd_backup_timepoint
                            WHERE 
                                task_type = 17
                            GROUP BY 
                                storage_uuid";
        $copyStorageData = $this->dbSelect($copyStorageSql);
        $copyStorageNum = count($copyStorageData);
        $usedCopyStorage = 0;

        if (!empty($copyStorageData)) {
            foreach ($copyStorageData as $i) {
                $usedCopyStorage += $i['total_write_size'];
            }
        }

        $storageOverviewData = array(
            'storage_device' => $totalStorageDevices,
            'online_device' => $onlineNumber,
            'offline_device' => $offlineNumber,
            'backup_data' => $backupCount,
            'copy_data' => $copyCount,
            'archived_data' => $archivedCount,
            'used_storage' => $usedStorage,
            'unused_storage' => $unusedStorage,
            'total_storage_size' => $totalStorageSize,
            'copy_storage_num' => $copyStorageNum,
            'used_copy_storage' => $usedCopyStorage,
        );

        return $storageOverviewData;
    }

    /**
     * 获取任务概览数据
     * @return void
     */
    public function getTaskOverview($userUUID = ''): array
    {
        $userVal = empty($userUUID) ? xphp_get_user_info()['userUuid'] : $userUUID;
        $sql = "select * from bd_task where delete_flag != ?";
        $taskData = $this->dbSelect($sql, array(xphp_get_config('app', 'FLAG')['SET']));
        $totalTasks = count($taskData);
        $hisSql = "select error_code from bd_history_task";
        $hisData = $this->dbSelect($hisSql);
        $runTaskNumber = count($hisData);

        $sqlAlarm = "select distinct count(task_alarm_id) as total from bd_task_alarm where user_uuid = ? and alarm_level = ? and solved_flag = ?";
        //历史异常  level 2
        $countAbnormal = $this->dbSelect($sqlAlarm, array($userVal, 2, 2));
        //历史失败  level 3
        $countFail = $this->dbSelect($sqlAlarm, array($userVal, 3, 2));
        //成功从bd_history_task中取
        $sqlhis = "select count(id) as total from bd_history_task where user_uuid = ? and error_code = ?";
        //历史成功
        $countSuccess = $this->dbSelect($sqlhis, array($userVal, 0));
        //历史中止
        $countStop = $this->dbSelect($sqlhis, array($userVal, 2));
        //当前成功任务
        $sqltaskSuccess = "select count(id) as total from bd_task where user_uuid = ? and task_status = ?";
        $taskSuccess = $this->dbSelect($sqltaskSuccess, array($userVal, 17));
        //当前异常任务
        $sqltaskAbnormal = "select count(id) as total from bd_task where user_uuid = ? and task_status = ?";
        $taskAbnormal = $this->dbSelect($sqltaskAbnormal, array($userVal, 7));
        //当前错误任务
        $sqltaskFail = "select count(id) as total from bd_task where user_uuid = ? and task_status = ?";
        $taskFail = $this->dbSelect($sqltaskFail, array($userVal, 8));
        //当前停止任务
        $sqltaskStop = "select count(id) as total from bd_task where user_uuid = ? and task_status = ?";
        $taskStop = $this->dbSelect($sqltaskStop, array($userVal, 4));
        $taskOverviewData = array(
            'task_num' => $totalTasks, //当前任务个数
            'success_task' => $taskSuccess[0]['total'],
            'abnormal_task' => $taskAbnormal[0]['total'],
            'failed_task' => $taskFail[0]['total'],
            'stop_task' => $taskStop[0]['total'],

            'history_num' => $runTaskNumber,  //已运行任务个数
            'success_history' => $countSuccess[0]['total'],
            'abnormal_history' => $countAbnormal[0]['total'],
            'failed_history' => $countFail[0]['total'],
            'stop_history' => $countStop[0]['total']
        );

        return $taskOverviewData;
    }

    /**
     * 获取应用保护概览数据
     * @return object  概览数据
     */
    public function getAppOverview($userUUID = '')
    {
        $userInfo = xphp_get_user_info();
        $userUuid = $userInfo['userUuid'];
        $sql = "select count(distinct mo.organization_uuid) as total from m365_organization mo left join mt_user_tenant mut on mo.user_uuid = mut.user_uuid where 1 = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['M365_EXCHANGE'], 'mo.organization_uuid');
            $sql .= " AND ({$resourceUuidSql}) ";
        }
        $data = $this->dbSelect($sql);

        $onlineSql = "select count(distinct mo.organization_uuid) as total from m365_organization mo left join mt_user_tenant mut on mo.user_uuid = mut.user_uuid where mo.online_flag = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['M365_EXCHANGE'], 'mo.organization_uuid');
            $onlineSql .= " AND ({$resourceUuidSql}) ";
        }

        $onlineData = $this->dbSelect($onlineSql);

        //受保护的组织
        $sqlProtect = "select count(distinct mol.organization_uuid) as total from m365_object_list mol, bd_task bt left join mt_user_tenant mut on bt.user_uuid = mut.user_uuid where bt.task_uuid = mol.task_uuid and bt.task_type = ?";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['M365_EXCHANGE'], 'mol.organization_uuid');
            $sqlProtect .= " AND ({$resourceUuidSql}) ";
        }

        $dataProtect = $this->dbSelect($sqlProtect, array(xphp_get_config('task', 'TASKTYPE')['BACKUP']));

        $backsql = "select sum(total_size)  as total from bd_backup_timepoint where module_type = 14 AND task_type != 17";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $backsql .= " and user_uuid in ({$userUuidSql}) ";
        }
        $backdata = $this->dbSelect($backsql);

        $reportHandler = new ReportHandler();
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['M365'];
        $taskNumber = $reportHandler->getModuleTaskNumber($moduleType, $userUUID);

        $info = array(
            'app_number' => $data[0]['total'],
            'app_online' => $onlineData[0]['total'],
            'app_offline' => $data[0]['total'] - $onlineData[0]['total'],
            'app_protected' => intval($dataProtect[0]['total']),
            'backup_data' => intval($backdata[0]['total']),
            'app_task' => $taskNumber
        );

        return $info;
    }

    /**
     * 获取数据库复制报表数据
     * @return void
     */
    public function getDbCopyOverview($userUUID = ''): array
    {
        $reportHandler = new ReportHandler();
        $flag = xphp_get_config('app', 'FLAG');
        $module = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        //主机在线离线数
        $sql = "SELECT
                    online_flag
                FROM
                    bd_agent ba 
                WHERE
                    ba.agent_uuid IN ( SELECT DISTINCT baa.agent_uuid FROM bd_agent_app baa ) ";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'ba.agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }
        $data = $this->dbSelect($sql);
        $hostOnline = 0;
        $hostOffline = 0;

        foreach ($data as $d) {
            if (intval($d['online_flag']) == $flag['SET']) {
                $hostOnline++;
            } else {
                $hostOffline++;
            }
        }

        $bakOnlineSql = "SELECT
                        CDDT.target_agent_uuid,
                    CASE
                            WHEN BAA.cluster_flag = 1 THEN
                            ( SELECT COUNT(*) FROM bd_agent_app BAA2 WHERE BAA2.cluster_uuid = BAA.cluster_uuid ) 
                            WHEN BAA.cluster_flag IS NOT NULL 
                            AND BAA.cluster_flag != 1 THEN
                                1 ELSE 0 
                                END AS count 
                        FROM
                            cdp_db_dr_task CDDT
                            INNER JOIN bd_agent_app BAA ON CDDT.target_agent_uuid = BAA.agent_uuid
                            INNER JOIN bd_agent BA ON CDDT.target_agent_uuid = BA.agent_uuid 
                        WHERE
                            BA.online_flag = 1";
        $bakOfflineSql = "SELECT
                        CDDT.target_agent_uuid,
                    CASE
                            WHEN BAA.cluster_flag = 1 THEN
                            ( SELECT COUNT(*) FROM bd_agent_app BAA2 WHERE BAA2.cluster_uuid = BAA.cluster_uuid ) 
                            WHEN BAA.cluster_flag IS NOT NULL 
                            AND BAA.cluster_flag != 1 THEN
                                1 ELSE 0 
                                END AS count 
                        FROM
                            cdp_db_dr_task CDDT
                            INNER JOIN bd_agent_app BAA ON CDDT.target_agent_uuid = BAA.agent_uuid
                            INNER JOIN bd_agent BA ON CDDT.target_agent_uuid = BA.agent_uuid 
                        WHERE
                            BA.online_flag = 2";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'BA.agent_uuid');
            $bakOnlineSql .= " AND ($resourceUuidSql) ";
            $bakOfflineSql .= " AND ($resourceUuidSql) ";
        }

        // 备份主机在线离线数
        $sqlProtectBakOnline = "SELECT
                            COALESCE(SUM(count), 0) AS count 
                        FROM
                            ({$bakOnlineSql}) subquery;";
        $sqlProtectBakOffline = "SELECT
                            COALESCE(SUM(count), 0) AS count 
                        FROM
                            ({$bakOfflineSql}) subquery;";

        $bakOnline = $this->dbSelect($sqlProtectBakOnline);
        $bakOffline = $this->dbSelect($sqlProtectBakOffline);

        // 受保护主机：正在运行中的数据库复制任务源端客户端个数（需统计集群下所有节点）
        $subQuerySql = "SELECT
                            BT.agent_uuid,
                        CASE
                                WHEN BAA.cluster_flag = 1 THEN
                                ( SELECT COUNT(*) FROM bd_agent_app WHERE cluster_uuid = BAA.cluster_uuid ) 
                                WHEN BAA.cluster_flag != 1 THEN
                                1 ELSE 0 
                            END AS count 
                        FROM
                            bd_task BT
                            INNER JOIN bd_agent_app BAA ON BT.agent_uuid = BAA.agent_uuid 
                        WHERE
                            BT.module_type = 10 
                            AND BT.task_type = 46 
                        AND BT.task_status = 2";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'BAA.agent_uuid');
            $subQuerySql .= " AND ($resourceUuidSql) ";
        }

        $sqlProtectedNum = "SELECT
                                COALESCE(SUM(count), 0) AS count 
                            FROM
                                ({$subQuerySql}) AS subquery;";

        $protectedNum = $this->dbSelect($sqlProtectedNum);

        $info = array(
            'hostOnline' => $hostOnline,
            'hostOffline' => $hostOffline,
            'totalHost' => $hostOnline + $hostOffline,
            'bakOnline' => $bakOnline[0]['count'],
            'bakOffline' => $bakOffline[0]['count'],
            'totalBackupHost' => $bakOnline[0]['count'] + $bakOffline[0]['count'],
            'protectedNum' => $protectedNum[0]['count'],
            'taskNum' => $reportHandler->pGetTaskNum($module, null, $userUUID),
            'totalCopyData' => $reportHandler->getDbCopyTotalData()
        );

        return $info;
    }

    /**
     * 获取对象存储报表概览
     * @return object  概览数据
     */
    public function getObsOverview($userUUID = '')
    {
        $numberSql = "select * from obs_resource where 1= 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['OBS'], 'obs_uuid');
            $numberSql .= " AND ($resourceUuidSql) ";
        }
        $numberData = $this->dbSelect($numberSql);
        $totalNumber = count($numberData); //对象存储

        $normalSql = "select * from obs_resource where status = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['OBS'], 'obs_uuid');
            $normalSql .= " AND ($resourceUuidSql) ";
        }
        $normalData = $this->dbSelect($normalSql);
        $normalNumber = count($normalData); //状态正常

        $overviewData = array(
            'obs_total' => $totalNumber,
            'obs_normal' => $normalNumber,
            'obs_offline' => $totalNumber - $normalNumber,
        );
        return $overviewData;
    }

    /**
     * 获取Hadoop数量
     * @return object  概览数据
     */
    public function getHadoopOverview($userUUID = ''): array
    {
        $numberSql = "select * from hadoop_cluster where 1 = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['HADOOP'], 'hadoop_cluster_uuid');
            $numberSql .= " AND ($resourceUuidSql) ";
        }
        $numberData = $this->dbSelect($numberSql);
        $totalNumber = count($numberData); //对象存储

        $onlineSql = "select * from hadoop_cluster where status IN (1,2)";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['HADOOP'], 'hadoop_cluster_uuid');
            $onlineSql .= " AND ($resourceUuidSql) ";
        }

        $onlineData = $this->dbSelect($onlineSql);
        $onlineNumber = count($onlineData); //在线

        $offlineSql = "select * from hadoop_cluster where status = 3";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['HADOOP'], 'hadoop_cluster_uuid');
            $offlineSql .= " AND ($resourceUuidSql) ";
        }
        $offlineData = $this->dbSelect($offlineSql);
        $offlineNumber = count($offlineData); //离线

        $authSql = "select * from hadoop_cluster where authorization = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['HADOOP'], 'hadoop_cluster_uuid');
            $authSql .= " AND ($resourceUuidSql) ";
        }
        $authData = $this->dbSelect($authSql);
        $authNumber = count($authData); //授权

        $hadoopData = "select  sum(write_size) as total from bd_backup_timepoint where sub_module_type = 3 and module_type = 3 and task_type = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $hadoopData .= " AND user_uuid IN ({$userUuidSql}) ";
        }
        $allData = $this->dbSelect($hadoopData);//备份数据
        $taskNumberSql = "select * from bd_task where sub_module_type = 3 and module_type = 3 and task_type = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $taskNumberSql .= " AND user_uuid IN ({$userUuidSql}) ";
        }
        $taskNumber = $this->dbSelect($taskNumberSql);
        $totalTask = count($taskNumber); //对象存储
        $overviewData = array(
            'hadoop_total' => $totalNumber,
            'hadoop_online' => $onlineNumber,
            'hadoop_offline' => $offlineNumber,
            'hadoop_auth' => $authNumber,
            'backupData' => $allData[0]['total'] ? $allData[0]['total'] : 0,
            'task' => $totalTask
        );

        return $overviewData;
    }

    /**
     * 获取文件复制报表数据 
     * @return void
     */
    public function getFileCopyOverview($userUUID = ''): array
    {
        $reportHandler = new ReportHandler();
        $userUuid = xphp_get_user_info()['userUuid'];

        // 客户端主机
        $hostInfo = $reportHandler->getFileClientInfo($userUUID);
        // nas设备
        $nasInfo = $reportHandler->getNasClientInfo($userUUID);

        $online = $hostInfo['online'] + $nasInfo['online'];
        $offline = $hostInfo['offline'] + $nasInfo['offline'];
        $totalClient = $hostInfo['total'] + $nasInfo['total'];

        //  获取文件复制受保护对象
        $homePageInfo = new homePageInfo();
        $showFlagInfo = $homePageInfo->getPageAuth();
        $copyInfo = $this->getFilecopyInfo(
            $showFlagInfo['file_copy_protect'],
            xphp_get_config('module', 'MODULE_TYPE')['FILE_COPY'],
            xphp_get_config('task', 'TASKTYPE')['FILE_COPY'],
            empty($userUUID) ? $userUuid : $userUUID
        );

        // 获取文件复制总数据
        $filecopyDataSql = "SELECT sum( total_object_write_size ) AS total, user_uuid FROM bd_history_task WHERE module_type = 26 AND task_type = 62";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $filecopyDataSql .= " AND user_uuid IN ({$userUuidSql}) ";
        }

        $filecopyData = $this->dbSelect($filecopyDataSql);

        $info = array(
            'online' => $online,
            'offline' => $offline,
            'totalClient' => $totalClient,
            'protectedNum' => $copyInfo['protected'],
            'taskNum' => $reportHandler->pGetTaskNum(xphp_get_config('module', 'MODULE_TYPE')['FILE_COPY'], null, $userUUID),
            'totalCopyData' => $filecopyData[0]['total']
        );

        return $info;
    }

    /**
     * 文件复制信息
     *@param boolean $showFlag 模块是否授权显示
     * @param int $taskType 任务类型
     * @param $userUuid 用户uuid
     * @return []
     */
    private function getFilecopyInfo($showFlag, $module, $taskType, $userUuid)
    {
        $flag = xphp_get_config('app')['FLAG'];
        $userInfo = xphp_get_user_info();
        $sqlProtect2 = '';
        $sqlParams = array();
        // userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look($userUuid)) {
            $sqlProtect2 .= " AND bt.user_uuid = ?";
            $sqlParams = array($userUuid, $userUuid);
        }

        // 不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlProtect2 .= " AND mut.tenant_uuid IS NULL ";
        }
        // 受保护的文件复制对象数量
        $sqlProtectSrc = "SELECT
                            COUNT( DISTINCT uuid ) AS total 
                        FROM
                            (
                            SELECT
                                source_uuid AS uuid 
                            FROM
                                sync_task_path_list stpl
                                JOIN bd_task bt ON bt.task_uuid = stpl.task_uuid
                                LEFT JOIN mt_user_tenant mut ON bt.user_uuid = mut.user_uuid 
                            WHERE
                                bt.delete_flag = {$flag['UNSET']} 
                                AND bt.module_type = {$module} 
                                AND bt.task_type = {$taskType} 
                                AND stpl.source_type != 2 " . $sqlProtect2;
        $sqlProtectNassrc = "UNION
                            SELECT DISTINCT ip AS uuid FROM nas_storage_resource WHERE nas_uuid IN 
                            (SELECT source_uuid AS nas_uuid FROM sync_task_path_list stpl JOIN bd_task bt ON bt.task_uuid = stpl.task_uuid
                            LEFT JOIN mt_user_tenant mut ON bt.user_uuid = mut.user_uuid WHERE stpl.source_type = 2 " . $sqlProtect2 . ") 
                            ) AS combined_uuids;";
        $dataProtect = $this->dbSelect($sqlProtectSrc . $sqlProtectNassrc, $sqlParams);
        $info = array(
            'show' => $showFlag,
            'protected' => intval($dataProtect[0]['total']),
            'total' => $this->getHostNums($userUuid) + $this->getNasNums($userUuid),
        );
        return $info;
    }

    /**
     * 获取主机总数
     * @return []
     */
    private function getHostNums($userUuid)
    {
        $userInfo = xphp_get_user_info();
        $sqlParams = array();
        $sql = "select count(distinct ba.agent_uuid) as total from bd_agent ba left join mt_user_tenant mut on ba.user_uuid = mut.user_uuid where ba.agent_type not in (3, 4, 5)";

        if (v1_auth_need_check_look($userUuid)) {//userLevel是1 2 3就全部显示
            $resourceHandler = new ResourceHandler();
            $uuid = $resourceHandler->pGetUserAllResource($userUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $agentUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " and ba.agent_uuid in ($agentUuidsIn)";
            }
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return $data[0]['total'];
    }

    /**
     * 获取NAS设备总数
     * @return []
     */
    private function getNasNums($userUuid)
    {
        $userInfo = xphp_get_user_info();
        $sql = "select nsr.nas_uuid from nas_storage_resource nsr left join mt_user_tenant mut on nsr.user_uuid = mut.user_uuid ";
        $sqlContact = " where ";

        if (v1_auth_need_check_look($userUuid)) {//userLevel是1 2 3就全部显示
            $resourceHandler = new ResourceHandler();
            $uuid = $resourceHandler->pGetUserAllResource($userUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['NAS']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $nasUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= $sqlContact . " nsr.nas_uuid in ($nasUuidsIn)";
                $sqlContact = " and";
            }
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= $sqlContact . " mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql);
        $total = count($data);
        return $total;
    }

    /**
     * 获取K8s报表数据
     * @return void
     */
    public function getKubernetsOverview($userUUID = ''): array
    {
        $reportHandler = new ReportHandler();
        $user = xphp_get_user_info();
        $flag = xphp_get_config('app', 'FLAG');
        $module = xphp_get_config('module', 'MODULE_TYPE')['KUBERNETES'];
        $sql = "select online_flag from kube_cluster where 1 = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['K8S'], 'cluster_uuid');
            $sql .= " AND ({$resourceUuidSql}) ";
        }
        $data = $this->dbSelect($sql);
        // 受保护的k8s集群
        $sqlProtect = "select count(distinct kt.cluster_uuid) as total from bd_task bt,kube_task kt where bt.task_uuid = kt.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ?";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['K8S'], 'kt.cluster_uuid');
            $sqlProtect .= " AND ({$resourceUuidSql}) ";
        }
        $dataProtect = $this->dbSelect(
            $sqlProtect,
            array(
                $flag['UNSET'],
                $module,
                xphp_get_config('task', 'TASKTYPE')['KUBE_BACKUP']
            )
        );
        $online = 0;
        $offline = 0;
        foreach ($data as $d) {
            if (intval($d['online_flag']) == $flag['SET']) {
                $online++;
            } else {
                $offline++;
            }
        }

        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $online + $offline,
            'protectedNum' => $dataProtect[0]['total'],
            'taskNum' => $reportHandler->pGetTaskNum($module, null, $userUUID),
            'backupDataSize' => $reportHandler->pGetProtectData($module, null, $userUUID)
        );

        return $info;
    }

    /**
     * 获取数据库报表概览数据
     * @return void
     */
    public function getDbProtectOverview($userUUID = ''): array
    {
        $reportHandler = new ReportHandler();
        $user = xphp_get_user_info();
        $module = xphp_get_config('module', 'MODULE_TYPE')['DB'];
        $flag = xphp_get_config('app', 'FLAG');

        // 查询 bd_agent_app 与 bd_agent 的关联数据
        $sql = "SELECT 
                baa.agent_uuid,
                baa.cluster_uuid,
                ba.online_flag
            FROM 
                bd_agent_app baa
            INNER JOIN 
                bd_agent ba ON baa.agent_uuid = ba.agent_uuid
            WHERE 
                user_uuid = ?";

        $data = $this->dbSelect($sql, array($user['userUuid']));

        $instanceMap = [];
        $onlineInstances = [];

        foreach ($data as $row) {
            $agentUuid = $row['agent_uuid'];
            $clusterUuid = $row['cluster_uuid'];
            $onlineFlag = $row['online_flag'];

            if (empty($clusterUuid)) {
                // 单实例
                $instanceMap[$agentUuid] = true;
                if ($onlineFlag == 1) {
                    $onlineInstances[$agentUuid] = true;
                }
            } else {
                // 集群实例
                if (!isset($instanceMap[$clusterUuid])) {
                    $instanceMap[$clusterUuid] = [];
                }

                $instanceMap[$clusterUuid][$agentUuid] = true;

                if ($onlineFlag == 1 && !isset($onlineInstances[$clusterUuid])) {
                    $onlineInstances[$clusterUuid] = true; // 只要有一个节点在线，整个集群就算在线
                }
            }
        }

        $totalInstances = count($instanceMap);
        $totalOnlineInstances = count($onlineInstances);

        // 受保护的数据库
        $sqlProtect = "SELECT 
                            count(distinct dl.agent_uuid) AS total 
                        FROM 
                            bd_task bt,db_list dl 
                        WHERE 
                            bt.task_uuid = dl.task_uuid AND bt.delete_flag = ? AND bt.module_type = ? AND bt.task_type = ? and bt.user_uuid = ?";

        $dataProtect = $this->dbSelect(
            $sqlProtect,
            array(
                $flag['UNSET'],
                $module,
                xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'],
                $user['userUuid']
            )
        );

        return [
            'total_instances' => $totalInstances,
            'online_instances' => $totalOnlineInstances,
            'offline_instances' => $totalInstances - $totalOnlineInstances,
            'protected_num' => $dataProtect[0]['total'],
            'task_num' => $reportHandler->pGetTaskNum($module, null, $userUUID),
            'backup_data' => $reportHandler->pGetProtectData($module, null, $userUUID)
        ];
    }

    /**
     * 获取概览页的任务运行趋势
     * @param $moduleType moduleType
     * @param $reportType reportType
     * @return array
     */
    public function getTaskOverviewTendency($params)
    {
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];

        $sql = "SELECT SUM(total_object_completed_size) AS total,finish_time, COUNT(DISTINCT task_name) AS task_count
                FROM
	              ( SELECT DATE_FORMAT( finish_time, '%Y-%m-%d' ) AS finish_time,total_object_completed_size,task_name FROM bd_history_task ORDER BY finish_time ) AS bsa 
                GROUP BY finish_time";
        $data = $this->dbSelect($sql);
        $dataArray = array();
        foreach ($data as $item) {
            $dataArray[] = array(
                'finishTime' => $item['finish_time'],
                'taskCount' => $item['task_count'],
                'total' => $item['total']
            );
        }

        $startTimestamp = strtotime(date('Y-m-d', strtotime($startTime))); // 2024-01-01
        $endTimestamp = strtotime(date('Y-m-d', strtotime($endTime))); // 2024-02-23

        $reportArray = [];

        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('Y-m-d', $i);

            $matchingItem = array_filter($dataArray, function ($item) use ($day) {
                return $item['finishTime'] === $day;
            });

            $matchingItem = array_values($matchingItem);

            if (!empty($matchingItem)) {
                $reportArray[] = array(
                    'finishTime' => $day,
                    'taskCount' => $matchingItem[0]['taskCount'],
                    'total' => $matchingItem[0]['total']
                );
            } else {
                $reportArray[] = array(
                    'finishTime' => $day,
                    'taskCount' => 0,
                    'total' => 0
                );
            }
        }

        return $reportArray;
    }

    // <----------------------------- END CUSTOM REPORT OVERVIEW --------------------------------------------->


    // <----------------------------- BEGIN CUSTOM REPORT RUNNING TENDENCY ----------------------------------->

    /**
     * 获取任务运行趋势数据
     * @return void
     */
    public function getStragyTendency($params): array
    {
        $moduleReportTypeArr = xphp_get_config('report', 'MODULE_REPORT');
        switch ($params['type']) {
            case $moduleReportTypeArr['TASK']: // 任务
                $tendencyList = $this->getTaskRunningTendency($params['type'], $params['timeInterval'], $params['statusType']);
                break;
            case $moduleReportTypeArr['STORAGE']: // 存储
                $tendencyList = $this->getTaskRunningTendency($params['type'], $params['timeInterval'], $params['statusType']);
                break;
            case $moduleReportTypeArr['CLIENT']: // 客户端
                $tendencyList = $this->getAgentTendency($params['timeInterval']);
                break;
            case $moduleReportTypeArr['NAS']: // NAS
                $tendencyList = $this->getTaskRunningTendency($params['type'], $params['timeInterval'], $params['statusType']);
                break;
            case $moduleReportTypeArr['VM']: // 虚拟机
                $tendencyList = $this->getTaskRunningTendency($params['type'], $params['timeInterval'], $params['statusType']);
                break;
            case $moduleReportTypeArr['VOL_CDP']: // 实时容灾
                $tendencyList = $this->getCdpTendency($params['timeInterval']);
                break;
            case $moduleReportTypeArr['PUBLIC_CLOUD']: // 共有云
                $tendencyList = $this->getPublicTendency($params['timeInterval']);
                break;
            case $moduleReportTypeArr['PRIVATE_CLOUD']: // 私有云
                $tendencyList = $this->getPrivateTendency($params['timeInterval']);
                break;
            case $moduleReportTypeArr['OBS']: // 对象存储
                $tendencyList = $this->getFsSubmoduleTendency($params['timeInterval'], xphp_get_config('module', 'MODULE_TYPE')['FS'], xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']);
                break;
            case $moduleReportTypeArr['HADOOP']: // 对象存储
                $tendencyList = $this->getFsSubmoduleTendency($params['timeInterval'], xphp_get_config('module', 'MODULE_TYPE')['FS'], xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
                break;
            case $moduleReportTypeArr['FILECOPY']: // 文件复制
                $tendencyList = $this->getTaskRunningTendency($params['type'], $params['timeInterval'], $params['statusType']);
                break;
            case $moduleReportTypeArr['M365']: // Microsoft 365
                $tendencyList = $this->getTaskRunningTendency($params['type'], $params['timeInterval'], $params['statusType']);
                break;
            case $moduleReportTypeArr['K8S']: // K8S
                $tendencyList = $this->getTaskRunningTendency($params['type'], $params['timeInterval'], $params['statusType']);
                break;
            default:
                break;
        }

        return $tendencyList;
    }

    /**
     * 获取任务近期运行数据
     * @param mixed $reportType 报表类型
     * @param mixed $timeInterval 时间间隔类型（1：最近一天 2：最近3天 3：最近一周 4：最近一个月）
     * @param mixed $statusType 状态类型（0：全部 1：成功 2：失败）
     * @return void
     */
    public function getTaskRunningTendency($reportType, $timeInterval, $statusType): array
    {
        $timeIntervalType = xphp_get_config('report', 'TIME_INTERVAL');
        $nowTime = date('Y-m-d');

        switch (intval(($timeInterval))) {
            case $timeIntervalType['LAST_DAY']:  //最近1天
                $interval = 0;
                break;
            case $timeIntervalType['LAST_THREE_DAYS']:  //最近3天
                $interval = 2;
                break;
            case $timeIntervalType['LAST_WEEK']:  //最近一周
                $interval = 6;
                break;
            case $timeIntervalType['RECENT_FORTEEN_DAYS']: // 最近14天
                $interval = 13;
                break;
            case $timeIntervalType['LAST_MONTH']:  //最近一个月
                $interval = 29;
                break;
            default:
                break;
        }

        $reducedTime = date('Y-m-d', strtotime($nowTime . ' - ' . $interval . 'days'));
        $moduleReportTypeArr = xphp_get_config('report', 'MODULE_REPORT');

        $sql = "SELECT 
                  SUM(total_object_completed_size) AS total,every_day
                FROM 
                   ( SELECT DATE_FORMAT( finish_time, '%Y-%m-%d' ) AS every_day, total_object_completed_size, module_type, error_code, task_type, task_uuid  
	                FROM bd_history_task) bht WHERE bht.task_uuid IS NOT NULL ";

        if ($reportType == $moduleReportTypeArr['TASK']) { // 任务报表
            switch ($statusType) {
                case '2': // 成功
                    $sql .= " AND bht.error_code = 0 ";
                    break;
                case '3': // 失败
                    $sql .= " AND bht.error_code != 0 ";
                    break;
                default:
                    break;
            }
        }

        if ($reportType == $moduleReportTypeArr['VM']) { // 虚拟机报表
            switch ($statusType) {
                case '1': // 全部
                    $sql .= " AND bht.module_type = 2 ";
                    break;
                case '2': // 成功
                    $sql .= " AND bht.error_code = 0 AND bht.module_type = 2 ";
                    break;
                case '3': // 失败
                    $sql .= " AND bht.error_code != 0 AND bht.module_type = 2 ";
                    break;
                default:
                    break;
            }
        }

        if ($reportType == $moduleReportTypeArr['STORAGE']) { // 存储报表
            switch ($statusType) {
                case '1': // 备份
                    $sql .= " AND bht.task_type IN (1, 28, 56) ";
                    break;
                case '2': // 副本
                    $sql .= " AND bht.task_type = 17 ";
                    break;
                case '3': // 归档
                    $sql .= " AND bht.task_type = 19 ";
                    break;
                default:
                    break;
            }
        }

        if ($reportType == $moduleReportTypeArr['NAS']) { // NAS报表
            switch ($statusType) {
                case '1': // 全部
                    $sql .= " AND bht.module_type = 11 ";
                    break;
                case '2': // 成功
                    $sql .= " AND bht.error_code = 0 AND bht.module_type = 11 ";
                    break;
                case '3': // 失败
                    $sql .= " AND bht.error_code != 0 AND bht.module_type = 11 ";
                    break;
                default:
                    break;
            }
        }

        if ($reportType == $moduleReportTypeArr['FILECOPY']) { // 文件复制报表
            switch ($statusType) {
                case '1': // 全部
                    $sql .= " AND bht.module_type = 26 AND bht.task_type = 62 ";
                    break;
                case '2': // 成功
                    $sql .= " AND bht.error_code = 0 AND bht.module_type = 26 AND bht.task_type = 62 ";
                    break;
                case '3': // 失败
                    $sql .= " AND bht.error_code != 0 AND bht.module_type = 11 AND bht.task_type = 62 ";
                    break;
                default:
                    break;
            }
        }

        if ($reportType == $moduleReportTypeArr['M365']) { // Microsoft 365报表
            switch ($statusType) {
                case '1': // 全部
                    $sql .= " AND bht.module_type = 14 ";
                    break;
                case '2': // 成功
                    $sql .= " AND bht.error_code = 0 AND bht.module_type = 14 ";
                    break;
                case '3': // 失败
                    $sql .= " AND bht.error_code != 0 AND bht.module_type = 14 ";
                    break;
                default:
                    break;
            }
        }

        if ($reportType == $moduleReportTypeArr['K8S']) { // K8S报表
            switch ($statusType) {
                case '1': // 全部
                    $sql .= " AND bht.module_type = 28 ";
                    break;
                case '2': // 成功
                    $sql .= " AND bht.error_code = 0 AND bht.module_type = 28 ";
                    break;
                case '3': // 失败
                    $sql .= " AND bht.error_code != 0 AND bht.module_type = 28 ";
                    break;
                default:
                    break;
            }
        }

        $sql .= " GROUP BY every_day";

        $data = $this->dbSelect($sql);
        $dataArray = array();
        foreach ($data as $item) {
            $dataArray[$item['every_day']] = $item['total'];
        }

        $startTimestamp = strtotime(date('Y-m-d', strtotime($reducedTime)));
        $endTimestamp = strtotime(date('Y-m-d', strtotime($nowTime)));
        $reportArray = [];

        // 每天递增86400秒（即一天）
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('Y-m-d', $i);
            if (isset($dataArray[$day])) {
                $reportArray[] = [
                    'value' => $dataArray[$day],
                    'time' => $day,
                ];
            } else {
                $reportArray[] = [
                    'value' => 0,
                    'time' => $day,
                ];
            }
        }

        return $reportArray;
    }

    /**
     * 获取客户端报表任务运行数据
     * @param mixed $timeInterval
     * @return void
     */
    public function getAgentTendency($timeInterval): array
    {
        $reportHandler = new ReportHandler();
        $timeIntervalType = xphp_get_config('report', 'TIME_INTERVAL');
        $nowTime = date('Y-m-d');

        switch (intval(($timeInterval))) {
            case $timeIntervalType['LAST_DAY']:  //最近1天
                $interval = 0;
                break;
            case $timeIntervalType['LAST_THREE_DAYS']:  //最近3天
                $interval = 2;
                break;
            case $timeIntervalType['LAST_WEEK']:  //最近一周
                $interval = 6;
                break;
            case $timeIntervalType['LAST_MONTH']:  //最近一个月
                $interval = 29;
                break;
        }

        $reducedTime = date('Y-m-d', strtotime($nowTime . ' - ' . $interval . 'days'));

        $startTimestamp = strtotime(date('Y-m-d', strtotime($reducedTime)));
        $endTimestamp = strtotime(date('Y-m-d', strtotime($nowTime)));

        // 数据库查询
        $sql = "SELECT sum(bsa.total_size) as total, alarm_day, source 
        FROM (
            SELECT DATE_FORMAT(bbt.timepoint, '%m-%d') AS alarm_day, bbt.total_size, 'db' AS source 
            FROM bd_backup_timepoint bbt JOIN db_backup_timepoint dt ON bbt.timepoint_uuid = dt.timepoint_uuid
            UNION ALL
            SELECT DATE_FORMAT(bbt.timepoint, '%m-%d') AS alarm_day, bbt.total_size, 'file' AS source 
            FROM bd_backup_timepoint bbt JOIN fs_backup_timepoint ft ON bbt.timepoint_uuid = ft.fs_timepoint_uuid
            UNION ALL
            SELECT DATE_FORMAT(bbt.timepoint, '%m-%d') AS alarm_day, bbt.total_size, 'os' AS source 
            FROM bd_backup_timepoint bbt JOIN os_backup_timepoint ot ON bbt.timepoint_uuid = ot.timepoint_uuid
            UNION ALL
            SELECT DATE_FORMAT(bbt.timepoint, '%m-%d') AS alarm_day, cvbcs.backup_file_size + cvbcs.log_file_total_size AS total_size, 'cdp' AS source 
            FROM bd_backup_timepoint bbt JOIN cdp_vol_backup_agent cvba ON bbt.timepoint_uuid = cvba.timepoint_uuid 
            JOIN cdp_vol_backup_vol_set cvbcs ON cvba.id = cvbcs.backup_agent_id
        ) AS bsa 
            GROUP BY alarm_day, source";

        // 执行查询
        $data = $this->dbSelect($sql);

        // 将数据按来源分类
        $groupedData = $reportHandler->groupBySourceAndDay($data);

        $ret = [
            'db' => [],
            'file' => [],
            'os' => [],
            'cdp' => [],
        ];

        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('m-d', $i);
            foreach (['db', 'file', 'os', 'cdp'] as $source) {
                $value = isset($groupedData[$source][$day]) ? $groupedData[$source][$day] : 0;
                $ret[$source][$day] = [
                    'value' => $value,
                    'time' => $day,
                ];
            }
        }

        // 计算总和
        $sumData = [];
        $sumData = [];
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('m-d', $i);
            $sumData[] = [
                'time' => $day,
                'value' => $ret['cdp'][$day]['value'] + $ret['db'][$day]['value'] + $ret['file'][$day]['value'] + $ret['os'][$day]['value']
            ];
        }

        return $sumData;
    }

    /**
     * 获取实时容灾报表任务运行数据
     * @param mixed $timeInterval
     * @return void
     */
    public function getCdpTendency($timeInterval): array
    {
        $timeIntervalType = xphp_get_config('report', 'TIME_INTERVAL');
        $nowTime = date('Y-m-d');

        switch (intval(($timeInterval))) {
            case $timeIntervalType['LAST_DAY']:  //最近1天
                $interval = 0;
                break;
            case $timeIntervalType['LAST_THREE_DAYS']:  //最近3天
                $interval = 2;
                break;
            case $timeIntervalType['LAST_WEEK']:  //最近一周
                $interval = 6;
                break;
            case $timeIntervalType['LAST_MONTH']:  //最近一个月
                $interval = 29;
                break;
        }

        $reducedTime = date('Y-m-d', strtotime($nowTime . ' - ' . $interval . 'days'));

        $startTimestamp = strtotime(date('Y-m-d', strtotime($reducedTime)));
        $endTimestamp = strtotime(date('Y-m-d', strtotime($nowTime)));

        $volcdpSql = "SELECT SUM(bsa.backup_file_size + bsa.log_file_total_size) as total,every_day
                      FROM
                         ( SELECT DATE_FORMAT( end_timestamp, '%m-%d' ) AS every_day,backup_file_size,log_file_total_size
	                       from bd_backup_timepoint bt,cdp_vol_backup_agent cvba,cdp_vol_backup_vol_set cvbcs
	                       where bt.timepoint_uuid = cvba.timepoint_uuid and cvba.id = cvbcs.backup_agent_id) as bsa 
                      GROUP BY every_day";
        $data = $this->dbSelect($volcdpSql);

        foreach ($data as $item) {
            $volCdp[$item['every_day']] = $item['total'];
        }

        $ret = [];
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('m-d', $i);
            if (isset($volCdp[$day])) {
                $ret[] = [
                    'value' => $volCdp[$day],
                    'time' => $day,
                ];
            } else {
                $ret[] = [
                    'value' => 0,
                    'time' => $day,
                ];
            }
        }
        return $ret;
    }

    /**
     * 获取公有云最近运行趋势
     * @param $moduleType moduleType
     * @param $reportType reportType
     * @return array
     */
    public function getPublicTendency($timeInterval)
    {
        $timeIntervalType = xphp_get_config('report', 'TIME_INTERVAL');
        $nowTime = date('Y-m-d');

        switch (intval(($timeInterval))) {
            case $timeIntervalType['LAST_DAY']:  //最近1天
                $interval = 0;
                break;
            case $timeIntervalType['LAST_THREE_DAYS']:  //最近3天
                $interval = 2;
                break;
            case $timeIntervalType['LAST_WEEK']:  //最近一周
                $interval = 6;
                break;
            case $timeIntervalType['LAST_MONTH']:  //最近一个月
                $interval = 29;
                break;
        }

        $reducedTime = date('Y-m-d', strtotime($nowTime . ' - ' . $interval . 'days'));

        $startTimestamp = strtotime(date('Y-m-d', strtotime($reducedTime)));
        $endTimestamp = strtotime(date('Y-m-d', strtotime($nowTime)));

        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);

        $sql = "SELECT 
                  SUM(total_size) AS total,every_day
              FROM 
                   ( SELECT DATE_FORMAT( timepoint, '%m-%d' ) AS every_day,total_size,module_type,task_uuid
	                FROM bd_backup_timepoint) bt
              left join vm_machine_list vml on bt.task_uuid = vml.task_uuid
              left join vm_vcenter vv on vml.vcenter_uuid = vv.vcenter_uuid where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')
              AND module_type = ? 
              GROUP BY every_day ";

        $data = $this->dbSelect($sql, array(xphp_get_config('module', 'MODULE_TYPE')['VM']));

        foreach ($data as $item) {
            $arr[$item['every_day']] = $item['total'];
        }

        $ret = [];
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('m-d', $i);
            if (isset($arr[$day])) {
                $ret[] = [
                    'value' => $arr[$day],
                    'time' => $day,
                ];
            } else {
                $ret[] = [
                    'value' => 0,
                    'time' => $day,
                ];
            }
        }
        return $ret;
    }

    /**
     * 获取私有云最近运行趋势
     * @param $moduleType moduleType
     * @param $reportType reportType
     * @return array
     */
    public function getPrivateTendency($timeInterval)
    {

        $timeIntervalType = xphp_get_config('report', 'TIME_INTERVAL');
        $nowTime = date('Y-m-d');

        switch (intval(($timeInterval))) {
            case $timeIntervalType['LAST_DAY']:  //最近1天
                $interval = 0;
                break;
            case $timeIntervalType['LAST_THREE_DAYS']:  //最近3天
                $interval = 2;
                break;
            case $timeIntervalType['LAST_WEEK']:  //最近一周
                $interval = 6;
                break;
            case $timeIntervalType['LAST_MONTH']:  //最近一个月
                $interval = 29;
                break;
        }

        $reducedTime = date('Y-m-d', strtotime($nowTime . ' - ' . $interval . 'days'));

        $startTimestamp = strtotime(date('Y-m-d', strtotime($reducedTime)));
        $endTimestamp = strtotime(date('Y-m-d', strtotime($nowTime)));

        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);

        $sql = "SELECT 
                  SUM(total_size) AS total,every_day
              FROM 
                   ( SELECT DATE_FORMAT( timepoint, '%m-%d' ) AS every_day,total_size,module_type,task_uuid
	                FROM bd_backup_timepoint) bt
              left join vm_machine_list vml on bt.task_uuid = vml.task_uuid
              left join vm_vcenter vv on vml.vcenter_uuid = vv.vcenter_uuid where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')
              AND module_type = ? 
              GROUP BY every_day ";

        $data = $this->dbSelect($sql, array(xphp_get_config('module', 'MODULE_TYPE')['VM']));

        foreach ($data as $item) {
            $arr[$item['every_day']] = $item['total'];
        }

        $ret = [];
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('m-d', $i);
            if (isset($arr[$day])) {
                $ret[] = [
                    'value' => $arr[$day],
                    'time' => $day,
                ];
            } else {
                $ret[] = [
                    'value' => 0,
                    'time' => $day,
                ];
            }
        }
        return $ret;
    }

    /**
     * 获取文件子模块（对象存储、hadoop）最近运行趋势
     * @param mixed $timeInterval
     * @param mixed $moduleType
     * @param mixed $subModuleType
     * @return array<array{time: string, value: int|array{time: string, value: mixed}>}
     */
    public function getFsSubmoduleTendency($timeInterval, $moduleType, $subModuleType)
    {

        $timeIntervalType = xphp_get_config('report', 'TIME_INTERVAL');
        $nowTime = date('Y-m-d');

        switch (intval(($timeInterval))) {
            case $timeIntervalType['LAST_DAY']:  //最近1天
                $interval = 0;
                break;
            case $timeIntervalType['LAST_THREE_DAYS']:  //最近3天
                $interval = 2;
                break;
            case $timeIntervalType['LAST_WEEK']:  //最近一周
                $interval = 6;
                break;
            case $timeIntervalType['LAST_MONTH']:  //最近一个月
                $interval = 29;
                break;
        }

        $reducedTime = date('Y-m-d', strtotime($nowTime . ' - ' . $interval . 'days'));

        $startTimestamp = strtotime(date('Y-m-d', strtotime($reducedTime)));
        $endTimestamp = strtotime(date('Y-m-d', strtotime($nowTime)));

        $sql = "SELECT 
                  SUM(total_size) AS total,every_day
              FROM 
                   ( SELECT DATE_FORMAT( timepoint, '%m-%d' ) AS every_day,total_size,module_type,sub_module_type
	                FROM bd_backup_timepoint) bt
              where sub_module_type = ?
              AND module_type = ? 
              GROUP BY every_day ";

        $data = $this->dbSelect($sql, array($subModuleType, $moduleType));
        foreach ($data as $item) {
            $arr[$item['every_day']] = $item['total'];
        }

        $ret = [];
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('m-d', $i);
            if (isset($arr[$day])) {
                $ret[] = [
                    'value' => $arr[$day],
                    'time' => $day,
                ];
            } else {
                $ret[] = [
                    'value' => 0,
                    'time' => $day,
                ];
            }
        }

        return $ret;
    }

    // <----------------------------- END CUSTOM REPORT RUNNING TENDENCY ------------------------------------->



    // <------------------------------ BEGIN CUSTOM REPORT DETAIL --------------------------------------------->

    /**
     * 获取虚拟机报表数据详情
     * @param $params params
     * @return void
     */
    public function getVmReportList($params): array
    {
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $params['sort'] ?? 'vv.vcenter_ip';
        $order = $params['order'] ?: 'desc';
        $searchName = $params['search'];    //模块匹配IP或主机名
        $startTime = $params['start_time'];//过滤开始时间
        $endTime = $params['end_time'];//过滤结束时间
        $vmType = $params['vm_type'];  //虚拟机类型
        $vmStatus = $params['vm_status']; // 虚拟机在线状态
        $protectStatus = $params['protect_status'];  //备份状态 1.保护中，未保护

        $keys = md5($offset . '-' . $limit . '-' . $sort . '-' . $order . '-' . $searchName . '-' . $vmType . '-' . $vmStatus . '-' . $protectStatus);
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP']; //备份
        $vmConfig = xphp_get_config('vendor', 'VMHYPERVISORTYPE');

        $sql = "select distinct vv.vcenter_ip,vm.vm_uuid,vm.vm_name,vv.hypervisor_type,vm.power_state,bu.user_name,vt.detail as vm_detail,
                IF (vml.task_uuid IS NOT NULL, 1, 0) AS protect_status
                from vm_machine vm
                LEFT JOIN vm_vcenter vv ON vv.vcenter_uuid = vm.vcenter_uuid 
                LEFT JOIN vm_machine_list vml ON vml.vm_uuid = vm.vm_uuid
                LEFT JOIN vm_tree vt ON vt.uuid = vm.vm_uuid
                LEFT JOIN bd_user bu ON vv.user_uuid = bu.user_uuid
                where vv.hypervisor_type not in ('" . $publicCloudTypesStr . "') and vv.hypervisor_type not in ('" . $publicCloudTypesStr . "')  ";
        if (!empty($vmType)) {  // 主机状态
            $vmType = implode(', ', $vmType);
            $sql .= ' and vv.hypervisor_type in  (' . $vmType . ') ';
        }

        // 虚拟机状态
        if (!empty($vmStatus)) {
            $vmStatus = implode(', ', $vmStatus);
            $sql .= ' and vm.power_state in  (' . $vmStatus . ') ';
        }

        // 备份状态
        if (!empty($protectStatus)) {
            $protectStatus = implode(', ', $protectStatus);

            switch ($protectStatus) {
                case '0':
                    $sql .= ' AND vml.task_uuid IS NULL ';
                    break;
                case '1':
                    $sql .= ' AND vml.task_uuid IS NOT NULL ';
                    break;
                default:
                    break;
            }
        }
        if ($this->checkEmpty($searchName)) {
            $sql .= ' and vm.vm_name like "%' . $searchName . '%" ';
        }

        $countSql = $sql;
        $countData = $this->dbSelect($countSql);
        $isExport = $params['isExport'];
        if (!$isExport) {
            $sql .= " ORDER BY vm.vm_name  ";
        }
        $data = $this->dbSelect($sql);
        $vmReportArray = array();
        $reportHandler = new ReportHandler();
        if (!empty($data)) {
            $vmHypervisorGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');
            $vmHypervisorType = xphp_get_config('vm', 'VMHYPERVISORTYPE');

            $allVmHistoryData = $reportHandler->getAllVmHistoryData($keys);

            foreach ($data as $d) {
                //获取虚拟机IP
                if (!empty($d['vm_detail'])) {
                    $detail = json_decode($d['vm_detail'], true);
                    if (
                        in_array(intval($d['hypervisor_type']), $vmHypervisorGroup['openstack']) ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_SANGFOR_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_VMWARE'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_HYPERV'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_H3C_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_WINHONG_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_SMARTX_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_XFUSION_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_H3C_CAS_CVD']
                    ) {
                        $network = $detail['network_list'];
                        $vmIp = '';
                        $ipList = array();
                        if (!empty($network)) {
                            foreach ($network as $net) {
                                $list = $net['ip_list'];
                                foreach ($list as $ip) {
                                    $ipList[] = $ip;
                                    $vmIp .= $ip . PHP_EOL;
                                }
                            }
                        }
                    }
                }
                $vmUuid = $d['vm_uuid'];
                $vmName = $d['vm_name'];
                $vcenterIp = $d['vcenter_ip'];
                $online = $d['power_state'];
                $vmType = $d['hypervisor_type'];
                $backupStatus = $d['protect_status'];
                $storageNickname = $reportHandler->getStorageNickName($d['vm_uuid']);
                $vmRunningInfo = $reportHandler->getVmRunningInfo($allVmHistoryData, $vmUuid);
                $backupStatusInfo = $reportHandler->getVmProtectionState($vmUuid);
                $vmReportArray[] = array(
                    'ip' => $vcenterIp,
                    'vm_name' => $vmName,
                    'vm_ip' => $vmIp ? $vmIp : '--',
                    'vm_type' => $vmConfig[$vmType],
                    'online' => $online,
                    'protect_status' => $backupStatus,
                    'last_backup_time' => $vmRunningInfo['last_backup_time'],
                    'task' => $backupStatusInfo,
                    'backup_number' => $vmRunningInfo['backup_count'],
                    'total_object_size' => $vmRunningInfo['total_object_size'],
                    'total_object_transport_size' => $vmRunningInfo['total_object_transport_size'],
                    'total_object_write_size' => $vmRunningInfo['total_object_write_size'],
                    'backup_data' => $vmRunningInfo['backup_data'],
                    'storage_nickname' => $storageNickname ? $storageNickname : '--',
                );
            }
        }
        $vminfo = v1_array_sort($vmReportArray, $sort, $order, $offset, $limit);  //排序
        $row = v1_array_sort($vmReportArray, $sort, $order, $offset, -1);  //排序
        return array(
            'total' => count($countData),
            'rows' => $vminfo,
            'row' => $row
        );
    }

    /**
     * 获取公有云报表数据详情
     * @param $params params
     * @return void
     */
    public function getPublicReportList($params)
    {
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $params['sort'] ?? 'vv.vcenter_ip';
        $order = $params['order'] ?: 'desc';
        $searchName = $params['search'];    //模块匹配IP或主机名
        $startTime = $params['start_time'];//过滤开始时间
        $endTime = $params['end_time'];//过滤结束时间
        $vmType = $params['vm_type'];  //虚拟机类型
        $vmStatus = $params['vm_status']; // 虚拟机在线状态
        $protectStatus = $params['protect_status'];  //备份状态 1.保护中，未保护
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP']; //备份
        $vmConfig = xphp_get_config('vendor', 'PUBLICCLOUDHYPERVISORTYPE');
        //$tenant = $params['tenant']; //虚拟化租户，备份租户
        $sql = "select distinct vv.vcenter_ip,vm.vm_uuid,vm.vm_name,vv.hypervisor_type,vm.power_state,bu.user_name,vt.detail as vm_detail,
                IF (vml.task_uuid IS NOT NULL, 1, 0) AS protect_status
                from vm_machine vm
                LEFT JOIN vm_vcenter vv ON vv.vcenter_uuid = vm.vcenter_uuid 
                LEFT JOIN vm_machine_list vml ON vml.vm_uuid = vm.vm_uuid
                LEFT JOIN vm_tree vt ON vt.uuid = vm.vm_uuid
                LEFT JOIN bd_user bu ON vv.user_uuid = bu.user_uuid
                where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')  ";

        // 虚拟机状态
        if (!empty($vmStatus)) {
            $vmStatus = implode(', ', $vmStatus);
            $sql .= ' and vm.power_state in  (' . $vmStatus . ') ';
        }

        if (!empty($vmType)) {  // 主机状态
            $vmType = implode(', ', $vmType);
            $sql .= ' and vv.hypervisor_type in  (' . $vmType . ') ';
        }

        // 备份状态
        if (!empty($protectStatus)) {
            $protectStatus = implode(', ', $protectStatus);

            switch ($protectStatus) {
                case '0':
                    $sql .= ' AND vml.task_uuid IS NULL ';
                    break;
                case '1':
                    $sql .= ' AND vml.task_uuid IS NOT NULL ';
                    break;
                default:
                    break;
            }
        }

        if ($this->checkEmpty($searchName)) {
            $sql .= ' and vm.vm_name like "%' . $searchName . '%" ';
        }

        $countSql = $sql;
        $countData = $this->dbSelect($countSql);
        $isExport = $params['isExport'];
        if (!$isExport) {
            $sql .= " ORDER BY vm.vm_name ";
        }
        $data = $this->dbSelect($sql);
        $vmReportArray = array();
        $reportHandler = new ReportHandler();
        if (!empty($data)) {
            $vmHypervisorGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');
            $vmHypervisorType = xphp_get_config('vm', 'VMHYPERVISORTYPE');

            $keys = md5($offset . '-' . $limit . '-' . $sort . '-' . $order . '-' . $searchName . '-' . $vmType . '-' . $vmStatus . '-' . $protectStatus);
            $allVmHistoryData = $reportHandler->getAllVmHistoryData($keys);

            foreach ($data as $d) {
                //获取虚拟机IP
                if (!empty($d['vm_detail'])) {
                    $detail = json_decode($d['vm_detail'], true);
                    if (
                        in_array(intval($d['hypervisor_type']), $vmHypervisorGroup['openstack']) ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_SANGFOR_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_VMWARE'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_HYPERV'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_H3C_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_WINHONG_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_SMARTX_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_XFUSION_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_H3C_CAS_CVD']
                    ) {
                        $network = $detail['network_list'];
                        $vmIp = '';
                        $ipList = array();
                        if (!empty($network)) {
                            foreach ($network as $net) {
                                $list = $net['ip_list'];
                                foreach ($list as $ip) {
                                    $ipList[] = $ip;
                                    $vmIp .= $ip . PHP_EOL;
                                }
                            }
                        }
                    }
                }
                $vmUuid = $d['vm_uuid'];
                $vmName = $d['vm_name'];
                $vcenterIp = $d['vcenter_ip'];
                $online = $d['power_state'];
                $vmType = $d['hypervisor_type'];
                $backupStatus = $d['protect_status'];
                $storageNickname = $reportHandler->getStorageNickName($d['vm_uuid']);
                $vmRunningInfo = $reportHandler->getVmRunningInfo($allVmHistoryData, $vmUuid);
                $backupStatusInfo = $reportHandler->getVmProtectionState($vmUuid);
                $vmReportArray[] = array(
                    'ip' => $vcenterIp,
                    'vm_name' => $vmName,
                    'vm_ip' => $vmIp ? $vmIp : '--',
                    'vm_type' => $vmConfig[$vmType],
                    'online' => $online,
                    'protect_status' => $backupStatus,
                    'last_backup_time' => $vmRunningInfo['last_backup_time'],
                    'task' => $backupStatusInfo,
                    'backup_number' => $vmRunningInfo['backup_count'],
                    'total_object_size' => $vmRunningInfo['total_object_size'],
                    'total_object_transport_size' => $vmRunningInfo['total_object_transport_size'],
                    'total_object_write_size' => $vmRunningInfo['total_object_write_size'],
                    'backup_data' => $vmRunningInfo['backup_data'],
                    'storage_nickname' => $storageNickname ? $storageNickname : '--',
                );
            }
        }
        $vminfo = v1_array_sort($vmReportArray, $sort, $order, $offset, $limit);  //排序
        $row = v1_array_sort($vmReportArray, $sort, $order, $offset, -1);  //排序
        return array(
            'total' => count($countData),
            'rows' => $vminfo,
            'row' => $row
        );
    }

    /**
     * 获取私有云报表数据详情
     * @param $params params
     * @return void
     */
    public function getPrivateReportList($params)
    {
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $sort = $params['sort'] ?? 'vv.vcenter_ip';
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $order = $params['order'] ?: 'desc';
        $searchName = $params['search'];    //模块匹配IP或主机名
        $startTime = $params['start_time'];//过滤开始时间
        $endTime = $params['end_time'];//过滤结束时间
        $vmType = $params['vm_type'];  //虚拟机类型
        $vmStatus = $params['vm_status']; // 虚拟机在线状态
        $protectStatus = $params['protect_status'];  //备份状态 1.保护中，未保护
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP']; //备份
        $vmConfig = xphp_get_config('vendor', 'CLOUDHYPERVISORTYPE');
        //$tenant = $params['tenant']; //虚拟化租户，备份租户
        $sql = "select distinct vv.vcenter_ip,vm.vm_uuid,vm.vm_name,vv.hypervisor_type,vm.power_state,bu.user_name,vt.detail as vm_detail,
                IF (vml.task_uuid IS NOT NULL, 1, 0) AS protect_status
                from vm_machine vm
                LEFT JOIN vm_vcenter vv ON vv.vcenter_uuid = vm.vcenter_uuid 
                LEFT JOIN vm_machine_list vml ON vml.vm_uuid = vm.vm_uuid
                LEFT JOIN vm_tree vt ON vt.uuid = vm.vm_uuid
                LEFT JOIN bd_user bu ON vv.user_uuid = bu.user_uuid
                where vv.hypervisor_type in ('" . $publicCloudTypesStr . "')  ";

        // 虚拟机状态
        if (!empty($vmStatus)) {
            $vmStatus = implode(', ', $vmStatus);
            $sql .= ' and vm.power_state in  (' . $vmStatus . ') ';
        }

        if (!empty($vmType)) {  // 主机状态
            $vmType = implode(', ', $vmType);
            $sql .= ' and vv.hypervisor_type in  (' . $vmType . ') ';
        }

        // 备份状态
        if (!empty($protectStatus)) {
            $protectStatus = implode(', ', $protectStatus);

            switch ($protectStatus) {
                case '0':
                    $sql .= ' AND vml.task_uuid IS NULL ';
                    break;
                case '1':
                    $sql .= ' AND vml.task_uuid IS NOT NULL ';
                    break;
                default:
                    break;
            }
        }

        if ($this->checkEmpty($searchName)) {
            $sql .= ' and vm.vm_name like "%' . $searchName . '%" ';
        }

        $countSql = $sql;
        $countData = $this->dbSelect($countSql);
        $isExport = $params['isExport'];
        if (!$isExport) {
            $sql .= " ORDER BY vm.vm_name ";
        }
        $data = $this->dbSelect($sql);
        $vmReportArray = array();
        $reportHandler = new ReportHandler();
        if (!empty($data)) {
            $vmHypervisorGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');
            $vmHypervisorType = xphp_get_config('vm', 'VMHYPERVISORTYPE');

            $keys = md5($offset . '-' . $limit . '-' . $sort . '-' . $order . '-' . $searchName . '-' . $vmType . '-' . $vmStatus . '-' . $protectStatus);
            $allVmHistoryData = $reportHandler->getAllVmHistoryData($keys);

            foreach ($data as $d) {
                //获取虚拟机IP
                if (!empty($d['vm_detail'])) {
                    $detail = json_decode($d['vm_detail'], true);
                    if (
                        in_array(intval($d['hypervisor_type']), $vmHypervisorGroup['openstack']) ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_SANGFOR_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_VMWARE'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_HYPERV'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_H3C_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_WINHONG_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_SMARTX_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_XFUSION_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM'] ||
                        intval($d['hypervisor_type']) == $vmHypervisorType['VM_HYPERVISOR_TYPE_H3C_CAS_CVD']
                    ) {
                        $network = $detail['network_list'];
                        $vmIp = '';
                        $ipList = array();
                        if (!empty($network)) {
                            foreach ($network as $net) {
                                $list = $net['ip_list'];
                                foreach ($list as $ip) {
                                    $ipList[] = $ip;
                                    $vmIp .= $ip . PHP_EOL;
                                }
                            }
                        }
                    }
                }
                $vmUuid = $d['vm_uuid'];
                $vmName = $d['vm_name'];
                $vcenterIp = $d['vcenter_ip'];
                $online = $d['power_state'];
                $vmType = $d['hypervisor_type'];
                $backupStatus = $d['protect_status'];
                $storageNickname = $reportHandler->getStorageNickName($d['vm_uuid']);
                $vmRunningInfo = $reportHandler->getVmRunningInfo($allVmHistoryData, $vmUuid);
                $backupStatusInfo = $reportHandler->getVmProtectionState($vmUuid);
                $vmReportArray[] = array(
                    'ip' => $vcenterIp,
                    'vm_name' => $vmName,
                    'vm_ip' => $vmIp ? $vmIp : '--',
                    'vm_type' => $vmConfig[$vmType],
                    'online' => $online,
                    'protect_status' => $backupStatus,
                    'last_backup_time' => $vmRunningInfo['last_backup_time'],
                    'task' => $backupStatusInfo,
                    'backup_number' => $vmRunningInfo['backup_count'],
                    'total_object_size' => $vmRunningInfo['total_object_size'],
                    'total_object_transport_size' => $vmRunningInfo['total_object_transport_size'],
                    'total_object_write_size' => $vmRunningInfo['total_object_write_size'],
                    'backup_data' => $vmRunningInfo['backup_data'],
                    'storage_nickname' => $storageNickname ? $storageNickname : '--',
                );
            }
        }
        $vminfo = v1_array_sort($vmReportArray, $sort, $order, $offset, $limit);  //排序
        $row = v1_array_sort($vmReportArray, $sort, $order, $offset, -1);  //排序
        return array(
            'total' => count($countData),
            'rows' => $vminfo,
            'row' => $row
        );
    }

    /**
     * 导出客户端报表全部明细数据
     * @param mixed $params
     * @return void
     */
    public function exportAllClientReportData($params)
    {
        $exportData = $this->getAgenteDetails($params, true)['rows'];
        $title = xphp_get_lang('UI_CLIENT_REPORT_DATA_DETAIL');

        $header = [
            'name' => xphp_get_lang('UI_AGENT_HOST_NAME'),
            'hostname' => xphp_get_lang('UI_CLIENT_REPORT_IP'),
            'os_type' => xphp_get_lang('UI_CLIENT_REPORT_OS'),
            'module_type_des' => xphp_get_lang('UI_PUBLIC_MODULE_TYPE'),
            'user' => xphp_get_lang('UI_CLIENT_REPORT_OWNER'),
            'plugin_deploy_status' => xphp_get_lang('UI_REPORT_ONLINE_STATUS'),
            'add_time' => xphp_get_lang('UI_JOB_CREATE_TIME'),
            'full_backup_number' => xphp_get_lang('UI_REPORT_FULL_BACKUP'),
            'incre_backup_number' => xphp_get_lang('UI_REPORT_INCRE_BACKUP'),
            'dif_backup_number' => xphp_get_lang('UI_REPORT_DIF_BACKUP'),
            'online' => xphp_get_lang('UI_VM_PROTECT_STATUS'),
            'task' => xphp_get_lang('UI_BACKUP_REPORT_TASK'),
            'backup_data' => xphp_get_lang('UI_PLATFORM_VOL_CDP_BACKUPSET'),
        ];

        $exportData = array_map(function ($row) {
            return [
                'name' => $row['name'],
                'hostname' => $row['hostname'],
                'os_type' => $row['os_type'],
                'module_type_des' => $row['module_type_des'],
                'user' => $row['user'],
                'plugin_deploy_status' => $row['plugin_deploy_status'],
                'add_time' => $row['add_time'],
                'full_backup_number' => $row['full_backup_number'],
                'incre_backup_number' => $row['incre_backup_number'],
                'dif_backup_number' => $row['dif_backup_number'],
                'online' => $row['online'],
                'task' => $row['task'],
                'backup_data' => $row['backup_data']
            ];
        }, $exportData);

        $relation = [
            'name' => ['col_name' => 'A', 'width' => 25],
            'hostname' => ['col_name' => 'B', 'width' => 15],
            'os_type' => ['col_name' => 'C', 'width' => 15],
            'module_type_des' => ['col_name' => 'D', 'width' => 20],
            'user' => ['col_name' => 'E', 'width' => 20],
            'plugin_deploy_status' => ['col_name' => 'F', 'width' => 20],
            'add_time' => ['col_name' => 'G', 'width' => 15],
            'full_backup_number' => ['col_name' => 'H', 'width' => 15],
            'incre_backup_number' => ['col_name' => 'I', 'width' => 15],
            'dif_backup_number' => ['col_name' => 'J', 'width' => 15],
            'online' => ['col_name' => 'K', 'width' => 15],
            'task' => ['col_name' => 'K', 'width' => 15],
            'backup_data' => ['col_name' => 'K', 'width' => 15],
        ];

        v1_base_export($title, $header, $exportData, $relation);
    }

    /**
     * 获取客户端详细数据
     * @param array $params 数组
     * @return array 数组
     */
    public function getAgenteDetails($params, $isExportAll = false)
    {
        $sortFields = [
            'ip' => 'ip',
            'os_version' => 'os_version',
            'add_time' => 'register_time',
            'protect_status' => 'protect_status',
            'online' => 'online_flag',
        ];

        $search = $params['search'];
        $osType = !empty($params['os_type']) ? explode(',', $params['os_type']) : [];
        $protectStatus = (isset($params['protect_status']) && $params['protect_status'] !== '') ? explode(',', $params['protect_status']) : [];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $online = !empty($params['online']) ? explode(',', $params['online']) : [];
        $pluginDeployStatus = !empty($params['plugin_deploy_status']) ? explode(',', $params['plugin_deploy_status']) : [];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'ip';
        $order = $params['order'] ?: 'desc';

        $sql = "SELECT DISTINCT
            ba.hostname,
            ba.agent_name,
            ba.ip,
            ba.os_type,
            ba.os_version,
            bu.user_name,
            ba.register_time,
            ba.online_flag,
        IF ( bl.task_names IS NOT NULL, 1, 0 ) AS protect_status,
            ba.plugin_deploy_status,
            ba.agent_uuid,
            task_names,
            module_type,
	        sub_module_type
        FROM
            bd_agent ba
            LEFT JOIN bd_user bu ON ba.user_uuid = bu.user_uuid
            LEFT JOIN (
            SELECT
                group_concat( bt.task_name SEPARATOR ', ' ) AS task_names,
                bl.agent_uuid,
                bl.task_uuid,
                bt.module_type as module_type,
	            bt.sub_module_type as sub_module_type	
            FROM
                bd_task_agent_list bl
                INNER JOIN bd_task bt ON bl.task_uuid = bt.task_uuid
            GROUP BY
                bl.agent_uuid 
            ) bl ON bl.agent_uuid = ba.agent_uuid 
        WHERE
            ba.agent_type NOT IN (3,4) ";

        $sqlParams = array();

        if ($this->checkEmpty($startTime)) {
            $sql .= " and ba.register_time >= '$startTime' and ba.register_time <=  '$endTime'";
        }

        // 按名字搜索
        if ($this->checkEmpty($search) && $search !== '/') {
            $sql .= ' and (ba.hostname like ? or ba.ip like ?) ';
            $sqlParams = array_merge($sqlParams, ['%' . $search . '%', '%' . $search . '%']);
        }

        // 对象类型
        $moduleType = $params['module_type'] ? explode(',', $params['module_type']) : [];
        $submoduleType = $params['sub_module_type'] ? explode(',', $params['sub_module_type']) : [];
        $devType = $params['dev_type'] ? explode(',', $params['dev_type']) : [];

        // 操作系统类型
        if (!empty($osType)) {
            $osTypeList = implode("', '", $osType);
            $sql .= ' AND ba.os_type IN ' . "('" . $osTypeList . "') ";
        }

        // 备份状态
        if (!empty($protectStatus)) {
            $protectStatus = implode(', ', $protectStatus);
            switch ($protectStatus) {
                case '0':
                    $sql .= ' AND task_names IS NULL ';
                    break;
                case '1':
                    $sql .= ' AND task_names IS NOT NULL ';
                    break;
                default:
                    break;
            }
        }

        // 主机状态
        if (!empty($online)) {
            $onlineList = implode(', ', $online);
            $pluginDeployStatusList = implode(', ', $pluginDeployStatus);
            $sql .= " AND ba.online_flag IN ($onlineList) AND ba.plugin_deploy_status IN ($pluginDeployStatusList) ";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'ba.agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }

        $sql .= " GROUP BY ba.agent_uuid";

        // 修正：先不分页，获取所有匹配基本条件的客户端
        $allAgents = $this->dbSelect($sql, $sqlParams);

        // 如果按模块类型筛选，则预先过滤掉没有任务的客户端
        if (!empty($moduleType)) {
            $allAgents = array_filter($allAgents, function ($item) {
                return $item['task_names'] !== null && $item['task_names'] !== '';
            });
        }

        // 修正：在PHP中对所有结果进行完整的模块类型过滤
        $finalAgents = [];
        $reportHandler = new ReportHandler();
        if (!empty($moduleType)) {
            foreach ($allAgents as $agent) {
                $moduleSizes = $reportHandler->getClientBackupSizeByModule($agent['agent_uuid'], $moduleType, $submoduleType, $devType);
                $filterModuleSizes = array_filter($moduleSizes, function ($item) {
                    return $item['module_type'] != 0;
                });

                // 只有当客户端包含指定模块类型的任务时，才将其保留
                if (!empty($filterModuleSizes)) {
                    $agent['__module_sizes'] = $filterModuleSizes; // 缓存结果，避免重复查询
                    $finalAgents[] = $agent;
                }
            }
        } else {
            // 如果不按模块筛选，则保留所有客户端，但仍需获取其模块数据
            foreach ($allAgents as $agent) {
                $moduleSizes = $reportHandler->getClientBackupSizeByModule($agent['agent_uuid'], [], [], []);
                $agent['__module_sizes'] = $moduleSizes;
                $finalAgents[] = $agent;
            }
        }

        // 修正：总数是完整过滤后数组的大小
        $count = count($finalAgents);

        // 修正：在PHP数组上进行排序
        if ($sort && !empty($finalAgents)) {
            $sortOrder = ($order === 'desc') ? SORT_DESC : SORT_ASC;
            $sortColumn = array_column($finalAgents, $sort);
            array_multisort($sortColumn, $sortOrder, $finalAgents);
        }

        // 修正：在PHP数组上进行分页
        if (!$isExportAll) {
            $paginatedAgents = array_slice($finalAgents, $offset, $limit);
        } else {
            $paginatedAgents = $finalAgents;
        }

        $rows = [];
        foreach ($paginatedAgents as $row) {
            // 使用之前缓存的模块数据
            $moduleSizes = $row['__module_sizes'];
            unset($row['__module_sizes']);

            $fullPoint = $reportHandler->getFullPoint($row['agent_uuid'], $row['module_type']);
            $incrementPoint = $reportHandler->getIncrementPoint($row['agent_uuid'], $row['module_type']);
            $diffrencePoint = $reportHandler->getDiffrencePoint($row['agent_uuid'], $row['module_type']);
            $archivedLogPoint = $reportHandler->getArchivedLogPoint($row['agent_uuid'], $row['module_type']);

            $totalObjectWriteSize = array_sum(array_column($moduleSizes, 'total_object_write_size'));
            $totalObjectValidSize = array_sum(array_column($moduleSizes, 'total_object_valid_size'));
            $totalObjectSize = array_sum(array_column($moduleSizes, 'total_object_size'));

            $fields = ['module_type', 'sub_module_type', 'dev_type', 'module_type_des'];
            $result = array_fill_keys($fields, []);

            foreach ($moduleSizes as $item) {
                foreach ($fields as $field) {
                    if (isset($item[$field])) {
                        $result[$field][] = $item[$field];
                    }
                }
            }

            // 将每个字段的值用逗号拼接成字符串
            foreach ($fields as $field) {
                $result[$field] = implode(',', $result[$field]);
            }

            $rows[] = array(
                'name' => $row['agent_name'],
                'hostname' => $row['hostname'] ? $row['hostname'] : '--',
                'ip' => $row['ip'],
                'os_type' => $row['os_type'],
                'os_version' => $row['os_version'],
                'add_time' => $row['register_time'],
                'plugin_deploy_status' => $row['plugin_deploy_status'],
                'user' => $this->getClientUuidName($row['agent_uuid'], $row['user_name']),
                'protect_status' => $row['protect_status'],
                'task' => $row['task_names'] ? $row['task_names'] : '--',
                'full_backup_number' => $fullPoint ?: 0,
                'incre_backup_number' => $incrementPoint ?: 0,
                'dif_backup_number' => $diffrencePoint ?: 0,
                'archived_log_number' => $archivedLogPoint ?: 0,
                'online' => $row['online_flag'],
                'module_type' => explode(',', $result['module_type']),
                'sub_module_type' => explode(',', $result['sub_module_type']),
                'dev_type' => explode(',', $result['dev_type']),
                'module_type_des' => $result['module_type_des'] ? $result['module_type_des'] : '--',
                'total_object_write_size' => v1_calsize($totalObjectWriteSize, true),
                'total_object_valid_size' => v1_calsize($totalObjectValidSize, true),
                'backup_data' => v1_calsize($totalObjectSize, true),
            );
        }

        return ['total' => $count, 'rows' => $rows];
    }

    /**
     * 根据客户端 uuid 获取当前的使用者是谁 只查询分配的资源/组
     * @param string $agentUuid 客户端uuid
     * @param string $userName  拥有着名称
     * @return string
     */
    private function getClientUuidName(string $agentUuid, string $userName = ''): string
    {
        $sqlParams = [$agentUuid];
        // 先 再分配的资源和资源组去查询
        // 适配租户，租户有租户管理员和租户，都要显示
        $sql2 = "select user_uuid from mt_user_resource where resource_type IN (2, 10) and resource_uuid = ?";
        $array = $this->dbSelect($sql2, $sqlParams);
        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type In (2, 10) ";
            $array = $this->dbSelect($sql, $sqlParams);
        }

        if (is_array($array) && $array) {
            $userUuids = "'" . implode("', '", array_column($array, 'user_uuid')) . "'";
            $sql = "select user_name from bd_user where user_uuid IN ($userUuids)";
            $data = $this->dbSelect($sql);
            if (!is_array($data)) {
                $data = [];
            }
            $userName = implode(',', array_column($data, 'user_name'));
        }

        return $userName;
    }

    /**
     * 获取存储报表数据
     * @param $params params
     * @return array
     */
    public function getStorageReportList($params)
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

        $sql = "SELECT 
                    bs.storage_nickname, bs.storage_type, bs.total_size, bs.free_size, bs.use_mode,
                    (bs.total_size-bs.free_size) as used_size, bs.status, bn.node_nickname, bm.online_flag,
                    bn.ip
                FROM 
                    bd_storage_resource bs
                    LEFT JOIN 
                    bd_node bn ON bn.node_uuid = bs.node_uuid
                    LEFT JOIN 
                    (SELECT online_flag, node_uuid FROM bd_module_server GROUP BY node_uuid) AS bm ON bm.node_uuid = bs.node_uuid
                WHERE 
                    1 = 1
                ";

        $sqlCount = "SELECT count(*) AS total
                     FROM 
                        bd_storage_resource bs
                        LEFT JOIN bd_node bn ON bn.node_uuid =bs.node_uuid
                        LEFT JOIN 
                        (select online_flag, node_uuid from bd_module_server GROUP BY node_uuid) AS bm ON bm.node_uuid = bs.node_uuid
                    WHERE 
                        1 = 1
                    ";
        $sqlParams = array();
        $sqlCountParams = array();
        // 按名字搜索
        if (!empty($search)) {
            $sql .= ' and  bs.storage_nickname like ? ';
            $sqlCount .= ' and  bs.storage_nickname like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }

        // 模块类型筛选
        if (!empty($storageType)) {
            $storageType = implode(', ', $storageType);
            $sql .= " and bs.storage_type in ($storageType) ";
            $sqlCount .= " and bs.storage_type in ($storageType)";
        }

        // 存储状态
        if (!empty($storageStatus)) {
            $storageStatus = implode(', ', $storageStatus);
            $sql .= " and bs.status in ($storageStatus) ";
            $sqlCount .= " and bs.status in ($storageStatus) ";
        }
        // 存储用涂
        if (!empty($storageUse)) {
            $storageUse = implode(', ', $storageUse);
            $sql .= " and bs.use_mode in ($storageUse) ";
            $sqlCount .= " and bs.use_mode in ($storageUse) ";
        }
        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $reportHandler = new ReportHandler();

        $rows = [];
        foreach ($data as &$row) {
            $flag = 1;
            if ($row['online_flag'] == xphp_get_config('app', 'FLAG')['UNSET']) {
                $flag = 2;
            }
            if (empty($row['online_flag'])) {
                $flag = 2;
            }

            $rows[] = array(
                'name' => $row['storage_nickname'],
                'type' => xphp_get_lang($storageTypeDes[$row['storage_type']]),
                'total_capacity' => v1_calsize($row['total_size'], true),
                'available_capacity' => v1_calsize($row['free_size'], true),
                'used_capacity' => v1_calsize($row['used_size'], true),
                'storage_status' => xphp_get_lang($allstorageStatusDes[$row['status']]),
                'add_time' => '--',
                'use_model' => $reportHandler->getUsemodeDes($row['use_mode']),
                'node' => $row['node_nickname'] ? $row['node_nickname'] : $row['ip'],
                'node_status' => $flag
            );
        }

        return array(
            'total' => $count[0]['total'],
            'rows' => $rows
        );
    }

    /**
     * 获取组织信息
     * @param array $params 参数
     * @return array 组织相关信息
     */
    public function getAppReportList($params = [])
    {
        $jobInfo = new JobInfo();
        $reportHandler = new ReportHandler();
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $search = $params['search'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $errorCode = $params['task_status'] ? explode(',', trim($params['task_status'])) : '';
        $errorCodeSql = !empty($errorCode) ? $reportHandler->getJobStatusSql($errorCode) : '';

        $m365ModuleType = xphp_get_config('module', 'MODULE_TYPE')['M365'];

        $sql = "SELECT distinct bht.id, bht.task_name, bht.module_type, bht.submodule_type,
                    bht.error_code, bht.user_uuid, bht.user_name,
                    bht.details, bht.total_object_size, bht.total_object_valid_size, bht.total_object_transport_size,
                    bht.total_object_completed_size, bht.total_object_write_size,
                    unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time,
                    bht.user_name, bht.task_uuid, bht.history_uuid
                FROM
                    bd_history_task bht
                WHERE
                    bht.module_type = ?";

        $sqlCount = "SELECT count(bht.id) as total FROM bd_history_task bht WHERE bht.module_type = ?";

        $sqlParams = [$m365ModuleType];
        $sqlCountParams = [$m365ModuleType];

        // 按任务名搜索
        if ($this->checkEmpty($search)) {
            $sql .= ' AND bht.task_name like ? ';
            $sqlCount .= ' AND bht.task_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }

        // 按时间搜索
        if ($this->checkEmpty($startTime) && $this->checkEmpty($endTime)) {
            $sql .= " AND bht.start_time >= '$startTime' AND bht.start_time <=  '$endTime'";
            $sqlCount .= " AND bht.start_time >= '$startTime' AND bht.start_time <=  '$endTime'";
        }

        // 按任务状态搜索
        if (!empty($errorCodeSql)) {
            $sql .= " AND bht.error_code $errorCodeSql";
            $sqlCount .= " AND bht.error_code $errorCodeSql";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND bht.user_uuid IN ({$userUuidSql}) ";
            $sqlCount .= " AND bht.user_uuid IN ({$userUuidSql}) ";
        }

        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        $total = intval($count[0]['total']);

        // 表头排序
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'task_name' => 'bht.task_name',
                'start_time' => 'bht.start_time',
                'finish_time' => 'bht.finish_time',
                'total_object_size' => 'bht.total_object_size',
                'total_object_valid_size' => 'bht.total_object_valid_size',
                'total_object_transport_size' => 'bht.total_object_transport_size',
                'total_object_write_size' => 'bht.total_object_write_size',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' bht.start_time desc' : ($sortArr[$params['sort']] . ' ' . $sortType . '');
            $sql .= " order by " . $sort;
        }

        $sql .= ' limit ? , ? ';
        $sqlParams = array_merge($sqlParams, array($offset, $limit));

        $data = $this->dbSelect($sql, $sqlParams);

        $records = array();

        if (!empty($data)) {
            foreach ($data as $d) {
                $details = json_decode($d['details'], true);

                $records[] = array(
                    'task_name' => $d['task_name'],
                    'organization_name' => $details['organization_name'],
                    'start_time' => $this->parseDate($d['start_time']),
                    'finish_time' => $this->parseDate($d['finish_time']),
                    'total_object_size' => $d['total_object_size'],
                    'total_object_valid_size' => $d['total_object_valid_size'],
                    'total_object_transport_size' => $d['total_object_transport_size'],
                    'total_object_write_size' => $d['total_object_write_size'],
                    'task_status' => $d['error_code'],  // 任务状态
                    'task_status_des' => $jobInfo->getHistoryJobResultDes($d['error_code']),
                    'user' => $d['user_name']
                );
            }
        }

        return ['rows' => $records, 'total' => $total];
    }

    /**
     * 获取任务报表数据
     * @param $params params
     * @return array
     */
    public function getTaskReportList($params)
    {
        $pfDes = require APP_PATH . 'v1/description/Pf.php';
        $allTaskTypeDes = $pfDes['TASKTYPEDES'];
        $allModuleTypeDes = $pfDes['MODULE_TYPE_DES'];
        $JobInfo = new JobInfos();
        $reportHandler = new ReportHandler();
        $sortFields = [
            'task_type' => 'bt.task_type',
            'module_type' => 'bt.module_type',
            'create_time' => 'bt.create_time',
            'finish_time' => 'bri.finish_time',
            'backup_data' => 'bri.total_object_size',
            'last_start_time' => 'bri.start_time',
            'task_status' => 'bt.task_status',
        ];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'bt.create_time';
        $order = $params['order'] ?: 'desc';
        $search = $params['search'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $subModuleType = $params['sub_module_type'];
        $moduleType = $params['module_type'];
        $job_status = $params['job_status'];
        $taskType = $params['job_type'];

        $sql = "SELECT 
                 bt.task_uuid, 
                 bt.task_name, 
                 bt.module_type, 
                 bt.task_type, 
                 bt.create_time, 
                 bt.task_status, 
                 bt.sub_module_type,
                 MAX(bri.start_time) AS start_time, 
                 MAX( bri.finish_time ) AS finish_time,
                 unix_timestamp( MAX(bri.start_time) ) start_times,
                 SUM(bri.total_object_size) AS total_object_size,
                 SUM(bri.total_object_write_size) AS total_object_write_size, 
                 SUM(bri.total_object_transport_size) AS total_object_transport_size,
                 bu.user_name
             FROM bd_task bt
             LEFT JOIN bd_history_task bri ON bt.task_uuid = bri.task_uuid
             LEFT JOIN sr_sure_backup ssb ON bt.task_uuid = ssb.task_uuid 
             LEFT JOIN bd_user bu ON bu.user_uuid = bt.user_uuid
             WHERE 1 = 1";
        $sqlParams = array();
        $sqlCountParams = array();
        // 按名字搜索

        if ($this->checkEmpty($search)) {
            $sql .= ' and bt.task_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }

        if ($this->checkEmpty($startTime)) {
            $sql .= " and bt.create_time >= '$startTime' and bt.create_time <=  '$endTime'";
        }

        if (!empty($moduleType)) {
            $sql .= " and bt.module_type in ($moduleType) ";
        }

        if (!empty($subModuleType)) {
            $sql .= " and bt.sub_module_type in ($subModuleType) ";
        }

        if (!empty($taskType)) {
            $sql .= " and bt.task_type in ($taskType) ";
        }
        if (!empty($job_status)) {
            $sql .= " and bt.task_status in ($job_status) ";
        }

        $sql .= " GROUP BY bt.task_uuid";

        $sqlCount = $sql;

        $isExport = $params['isExport'];
        if (!$isExport) {
            $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        }

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $count = count($count);

        $rows = [];

        foreach ($data as $row) {
            $ptDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
            $lastStart = $reportHandler->getLastStart($row['task_uuid']);
            $storageName = $reportHandler->getStorageName($row['task_uuid']);
            $task_status = $reportHandler->getStatus($row['task_uuid']);
            $sub_module_type = $row['submodule_type'];
            $jobStatus = $ptDes[$task_status];

            $rows[] = array(
                'task_uuid' => $row['task_uuid'],
                'task_name' => $row['task_name'],
                'task_type' => xphp_get_lang($allTaskTypeDes[$row['task_type']]),
                'sub_module_type_value' => $row['sub_module_type'],
                'module_type' => $row['module_type'],
                'task_status' => $row['task_status'],
                'speed' => '--',
                'progress' => '--',
                'create_time' => $row['create_time'] ? $row['create_time'] : '----',
                'last_start_time' => $row['start_time'] ? $row['start_time'] : '----',
                'uptime_time' => $JobInfo->getTimeInterval($row['start_times'], $row['task_status']),
                'finish_time' => $row['finish_time'] ? $row['finish_time'] : '----',
                'next_run_time' => $reportHandler->getJobNextstarttime($row['task_uuid'], $jobStatus), // 下次运行时间,
                'total_object_size' => !empty($row['total_object_size']) ? v1_calsize($row['total_object_size'], true) : '0',
                'total_object_write_size' => !empty($row['total_object_write_size']) ? v1_calsize($row['total_object_write_size'], true) : '0',
                'total_object_transport_size' => !empty($row['total_object_transport_size']) ? v1_calsize($row['total_object_transport_size'], true) : '0',
                'storage_nickname' => $storageName,
                'create_user' => $row['user_name']
            );
        }

        return ['total' => $count, 'rows' => $rows];
    }

    /**
     * 获取实时容灾备份数据
     * @param array $params 数组
     * @return array 数组
     */
    public function getVolReportList($params)
    {
        $reportHandler = new ReportHandler();
        $sortFields = [
            'add_time' => 'task_create_time',
            'protect' => 'protect_status',
            'protect_app' => 'app_status',
            'last_backup_time' => 'start_time',
            'backup_status' => 'task_status'
        ];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'start_time';
        $order = $params['order'] ?: 'desc';
        $searchName = $params['search'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $protectStatus = $params['protect_status'];
        $jobStatus = $params['task_status'];

        $sql = "WITH RankedRecords AS (  
                SELECT   
                    master_agent_uuid, 
                    master_agent_detail,
                    IF (bt.task_uuid IS NOT NULL, 1, 0) AS protect_status,
                    IF (cvta.app_uuid IS NOT NULL, 1, 0) AS app_status,
                    bt.task_name,
                    bt.task_status,
                    storage_nickname,
                    task_create_time,
                    bri.start_time,
                    bu.user_name,
                    bt.task_uuid,  
                    ROW_NUMBER() OVER (PARTITION BY master_agent_uuid ORDER BY protect_status DESC) AS rn
                FROM 
                    cdp_vol_backup_agent cvba
                    INNER JOIN bd_backup_timepoint bbt ON bbt.timepoint_uuid = cvba.timepoint_uuid
                    LEFT JOIN bd_task bt ON bt.task_uuid = bbt.task_uuid
                    LEFT JOIN cdp_vol_task_takeover_app cvta ON cvta.task_uuid = bt.task_uuid
                    LEFT JOIN bd_user bu ON bu.user_uuid = bbt.user_uuid
                    LEFT JOIN bd_storage_resource bsr ON bsr.storage_uuid = bbt.storage_uuid
                    LEFT JOIN bd_running_info bri ON bt.task_uuid = bri.task_uuid 
                WHERE 1=1";
        $sqlCount = "WITH RankedRecords AS (  
                    SELECT   
                        master_agent_uuid, 
                        master_agent_detail,
                        IF (bt.task_uuid IS NOT NULL, 1, 0) AS protect_status,
                        IF (cvta.app_uuid IS NOT NULL, 1, 0) AS app_status,
                        bt.task_name,
                        bt.task_status,
                        storage_nickname,
                        task_create_time,
                        bri.start_time,
                        bu.user_name,
                        bt.task_uuid,  
                        ROW_NUMBER() OVER (PARTITION BY master_agent_uuid ORDER BY protect_status DESC) AS rn
                    FROM 
                        cdp_vol_backup_agent cvba
                        INNER JOIN bd_backup_timepoint bbt ON bbt.timepoint_uuid = cvba.timepoint_uuid
                        LEFT JOIN bd_task bt ON bt.task_uuid = bbt.task_uuid
                        LEFT JOIN cdp_vol_task_takeover_app cvta ON cvta.task_uuid = bt.task_uuid
                        LEFT JOIN bd_user bu ON bu.user_uuid = bbt.user_uuid
                        LEFT JOIN bd_storage_resource bsr ON bsr.storage_uuid = bbt.storage_uuid
                        LEFT JOIN bd_running_info bri ON bt.task_uuid = bri.task_uuid 
                    WHERE 1=1";
        $sqlParams = array();
        $sqlCountParams = array();

        // 按名字搜索
        if ($this->checkEmpty($searchName)) {
            $sql .= ' AND bt.task_name LIKE ? ';
            $sqlCount .= ' AND bt.task_name LIKE ? ';
            $sqlParams[] = '%' . $searchName . '%';
            $sqlCountParams[] = '%' . $searchName . '%';
        }

        // 备份状态
        if (!empty($protectStatus) && count($protectStatus) == 1) {
            $protectStatus = implode(', ', $protectStatus);

            switch ($protectStatus) {
                case '0':
                    $sql .= ' AND bt.task_uuid IS NULL ';
                    $sqlCount .= ' AND bt.task_uuid IS NULL ';
                    break;
                case '1':
                    $sql .= ' AND bt.task_uuid IS NOT NULL ';
                    $sqlCount .= ' AND bt.task_uuid IS NOT NULL ';
                    break;
                default:
                    break;
            }
        }

        // 任务状态
        if (!empty($jobStatus)) {
            $jobStatus = implode(', ', $jobStatus);
            $sql .= " AND bt.task_status IN ($jobStatus) ";
            $sqlCount .= " AND bt.task_status IN ($jobStatus) ";
        }

        // 时间范围
        if ($this->checkEmpty($startTime)) {
            $sql .= " AND task_create_time >= ? AND task_create_time <= ? ";
            $sqlCount .= " AND task_create_time >= ? AND task_create_time <= ? ";
            $sqlParams[] = $startTime;
            $sqlParams[] = $endTime;
            $sqlCountParams[] = $startTime;
            $sqlCountParams[] = $endTime;
        }

        $isExport = $params['isExport'];
        if (!$isExport) {
            $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        }

        $sql .= " ) SELECT * FROM RankedRecords WHERE rn = 1";
        $sqlCount .= " ) SELECT COUNT(*) AS total FROM RankedRecords WHERE rn = 1";

        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $data = $this->dbSelect($sql, $sqlParams);

        $count = $dataCount[0]['total'];
        $rowsInfo = array();
        if ($count > 0) {
            foreach ($data as $d) {
                $details = json_decode($d['master_agent_detail'], true);
                $agentIp = $details['ip'];
                $hostName = $details['agent_name'];
                $osType = $details['os_type'];
                $protectStatus = $d['protect_status'];
                $appStatus = $d['app_status'];
                $taskName = $d['task_name'];
                $taskUuid = $d['task_uuid'];
                $userName = $d['user_name'];
                $startTime = $d['task_create_time'];
                $backupData = $d['total_object_size'];
                $lastBackupTime = $d['start_time'];
                $backupStatus = $d['task_status'];
                $masterAgentUuid = $d['master_agent_uuid'];
                $rowsInfo[] = array(
                    'ip' => $agentIp ? $agentIp : '--',
                    'host_name' => $hostName ? $hostName : '--',
                    'os_type' => $osType,
                    'user' => $userName,
                    'add_time' => $startTime,
                    'protect' => $protectStatus,
                    'protect_app' => $appStatus,
                    'task' => $taskName ? $taskName : '--',
                    'last_backup_time' => $lastBackupTime ? $lastBackupTime : '--',
                    'backup_set' => $reportHandler->getHostBackupSetInfo($masterAgentUuid),
                    'backup_status' => $backupStatus,
                    'backup_data' => v1_calsize($reportHandler->getHostBackupData($masterAgentUuid), true),
                    'storage_nickname' => $d['storage_nickname'],
                    'auto_takeover' => $reportHandler->getAutoTakeover($taskUuid)
                );
            }
        }
        return array(
            'total' => $count,
            'rows' => $rowsInfo
        );
    }

    /**
     * 获取nas报表数据
     * @param $params params
     * @return array
     */
    public function getNasReportList($params)
    {
        $sortFields = [
            'ip' => 'a.ip',
            'nas_type' => 'a.nas_type',
            'nas_create_time' => 'a.nas_create_time',
            'backup_status' => 'a.backup_status',
            'total_object_completed_size' => 'a.total_object_completed_size',
            'nas_status' => 'a.nas_status',
        ];
        $search = $params['search'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $deviceType = $params['device_type'];
        $protectStatus = $params['protect_status'];
        $online = $params['status'];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'ip';
        $order = $params['order'] ?: 'desc';

        $sql = "SELECT
                    nsr.ip,
                    nsr.nas_nickname,
                    nsr.share_path,
                    nsr.nas_type,
                    nsr.nas_uuid,
                    nsr.nas_status,
                    nsr.nas_create_time,
                    nsr.authorization_status,
                    SUM(bbt.total_size) total_size,
                IF
                    ( nt.task_uuid IS NOT NULL, 1, 0 ) AS backup_status,
                    bbt.task_name 
                FROM
                    nas_storage_resource nsr
                    LEFT JOIN nas_task nt ON nt.nas_uuid = nsr.nas_uuid
                    LEFT JOIN bd_backup_timepoint bbt ON bbt.task_uuid = nt.task_uuid 
                WHERE
                    1 = 1 ";

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['NAS'], 'nsr.nas_uuid');
            $sql .= " AND ({$resourceUuidSql}) ";
        }

        $sqlParams = array();
        $sqlCountParams = array();
        if ($this->checkEmpty($search)) {
            $sql .= ' and  a.ip like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }
        // 类型
        if (!empty($deviceType)) {
            $type = implode(', ', $deviceType);
            $sql .= " and a.nas_type in ($type) ";
        }

        // 备份状态
        if (!empty($protectStatus)) {
            $protectStatus = implode(', ', $protectStatus);
            $sql .= " and a.backup_status in ($protectStatus) ";
        }
        if (!empty($online)) {
            $hostStatus = implode(', ', $online);
            $sql .= " and a.nas_status in ($hostStatus) ";
        }
        // 备份时间
        if ($this->checkEmpty($startTime)) {
            $sql .= " and nsr.nas_create_time >= '$startTime'";
        }
        // 备份时间
        if ($this->checkEmpty($endTime)) {
            $sql .= " and nsr.nas_create_time <=  '$endTime'";
        }

        $sql .= " GROUP BY nsr.nas_uuid";

        $sqlCount = $sql;
        $isExport = $params['isExport'];
        if (!$isExport) {
            $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        }
        $countData = $this->dbSelect($sqlCount, $sqlCountParams);
        $count = count($countData);
        $data = $this->dbSelect($sql, $sqlParams);
        $rows = [];
        $rows = array_map(function ($row) {
            return [
                'ip' => $row['ip'],
                'device_name' => $row['nas_nickname'],
                'shared_path' => $row['share_path'],
                'device_type' => $row['nas_type'],
                'add_time' => $row['nas_create_time'],
                'status' => $row['nas_status'],
                'auth_status' => $row['authorization_status'],
                'protect_status' => $row['backup_status'],
                'task' => $row['task_name'] ? $row['task_name'] : '--',
                'backup_data' => $row['total_size'] ? v1_calsize($row['total_size'], true) : 0

            ];
        }, $data);
        return array(
            'total' => $count,
            'rows' => $rows
        );
    }

    /**
     * 获取对象存储详情数据
     * @param array $params
     * @return array{rows: array, total: int}
     */
    public function getObReporList(array $params): array
    {
        $reportHandler = new ReportHandler();
        $search = !empty($params['search']) ? v1_escape_wildcard($params['search']) : '';

        $sql = 'SELECT 
                    obs.id, obs.obs_uuid, obs.user_uuid, obs.obs_nickname, obs.obs_create_time, obs.vendor, obs.access_key_id, obs.access_key_secret, obs.endpoint_override, obs.ssl_verify_flag, obs.status, obs.detail, obs.proxy_uuid,
                    bu.user_name as bu_username  
                FROM 
                    obs_resource obs
                LEFT JOIN 
                    bd_user bu on bu.user_uuid = obs.user_uuid';

        $sqlcount = 'select count(obs_uuid) as total from obs_resource obs';

        $where = " WHERE obs.id is not null ";

        if (!empty($search)) {
            $where .= " AND obs.obs_nickname LIKE '%" . $search . "%' OR obs.endpoint_override LIKE '%" . $search . "%'";
        }
        if (!empty($params['vendor'])) {
            $vendor = implode(', ', $params['vendor']);

            $where .= " AND obs.vendor in ($vendor)";
        }

        if (isset($params['status']) && $params['status'] !== '') {
            $status = implode(', ', $params['status']);
            $where .= " AND obs.status in ($status)";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['OBS'], 'obs.obs_uuid');
            $where .= " AND ({$resourceUuidSql}) ";
        }

        //拼接搜索条件
        $sql .= $where;
        $sqlcount .= $where;
        $count = $this->dbSelect($sqlcount);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        $total = intval($count[0]['total']);
        // 表头排序
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'obs_uuid' => 'obs_uuid',
                'obs_nickname' => 'obs_nickname',
                'vendor' => 'vendor',
                'obs_create_time' => 'obs_create_time',
                'access_key_id' => 'access_key_id',
                'access_key_secret' => 'access_key_secret',
                'endpoint_override' => 'endpoint_override'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', id desc');
            $sql .= " order by " . $sort;
        }

        $sql .= ' limit ? , ? ';
        $sqlParams = array($params['offset'], $params['limit']);

        $data = $this->dbSelect($sql, $sqlParams);

        $records = [];
        $verdorArr = [
            0 => xphp_get_lang('UI_OBS_VENDOR_AWS'),
            1 => xphp_get_lang('UI_OBS_VENDOR_OSS'),
            2 => xphp_get_lang('UI_OBS_VENDOR_COS'),
            3 => xphp_get_lang('UI_OBS_VENDOR'),
            4 => 'Ceph',
            5 => 'Wasabi',
            6 => 'Minio',
            7 => xphp_get_lang('UI_OBS_VENDOR_AZURE'),
            8 => xphp_get_lang('UI_OBS_VENDOR_OTHER')
        ];

        $user = xphp_get_user_info();
        foreach ($data as $d) {
            $appliance_agency = '';
            if (!empty($d['proxy_uuid'])) {
                $appliance_agency = $reportHandler->getApplianceAgency($d['proxy_uuid']);
            }

            // 拥有者
            $owner = $reportHandler->getObsUUIDName($d['obs_uuid'], $d['bu_username'] ?? '--');
            $arr = explode(',', $owner);
            if ('admin' == $owner && $user['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                foreach ($arr as $index => $name) {
                    if ($name == 'admin') {
                        $arr[$index] = 'sysadmin';
                    }
                }
                $owner = implode(',', $arr);
            }

            // 创建者
            $creator = $d['bu_username'] ?? '--';
            if ('admin' == $creator && $user['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                $creator = 'sysadmin';
            }

            // 判断创建者等不等于当前用户,等于当前用户才能操作
            $opFlag = true;
            if ($d['user_uuid'] != $user['userUuid']) {
                $opFlag = false;
            }

            $records[] = [
                'id' => $d['id'],
                'obs_uuid' => $d['obs_uuid'],
                'nickname' => $d['obs_nickname'],
                'obs_create_time' => $d['obs_create_time'],
                'vendor' => $d['vendor'],
                'vendorDesc' => $verdorArr[$d['vendor']],
                'access_key_id' => $d['access_key_id'],
                'endpoint_override' => $d['endpoint_override'],
                'ssl_verify_flag' => $d['ssl_verify_flag'],
                'status' => $d['status'],
                'detail' => $d['detail'],
                'appliance_uuid' => $d['proxy_uuid'],
                'appliance_agency' => $appliance_agency,
                'creator' => $creator,
                'owner' => $owner,
                'op_flag' => $opFlag, // 当前用户是否可以操作该资源
            ];
        }

        return ['rows' => $records, 'total' => $total];
    }

    /**
     * 获取hadoop备份数据详情
     * @param mixed $params
     * @return array[]|array{rows: array, total: mixed}
     */
    public function getHadoopReportList($params)
    {
        //起始页
        $offset = $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];
        //排序字段
        $sort = $params['sort'];
        //排序方式
        $order = $params['order'];
        //获取搜索值
        $keyword = $params['keyword'];

        $sql = "select  hn.namenode_ip, hn.rest_api_port, hn.namenode_username, hn.verification_type, hn.status as node_status, hn.ssl_verify_flag, hn.version as node_version,
        temp_hc.hadoop_cluster_uuid,  temp_hc.hadoop_cluster_name as cluster_name, temp_hc.addition_time as add_time, temp_hc.status as online_flag, temp_hc.authorization  as auth_status, temp_hc.proxy_uuid, temp_hc.refresh_time, temp_hc.version,
        temp_hc.agent_name, temp_hc.ip, temp_hc.bu_username, temp_hc.user_uuid 
        from hadoop_namenode as hn
        INNER join ( select hc.hadoop_cluster_uuid, hc.hadoop_cluster_name, hc.proxy_uuid, hc.addition_time, hc.status, hc.authorization, hc.refresh_time, hc.version, hc.user_uuid, ba.agent_name, ba.ip, bu.user_name as bu_username from hadoop_cluster hc left join bd_agent ba on hc.proxy_uuid = ba.agent_uuid 
        left join bd_user bu on hc.user_uuid = bu.user_uuid";

        $sqlcount = "select count(hc.id) as count from hadoop_cluster as hc";
        $sqlparams = array();
        $sqlcountparams = array();
        $sqlcontxt = ' where ';
        if (v1_auth_need_check_look()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['HADOOP'],
                'hc.hadoop_cluster_uuid'
            );
            $sql .= " where  ({$resourceUuidSql})";
            $sqlcount .= " where ({$resourceUuidSql})";
            $sqlcontxt = " and ";
        }

        //关键字
        if (!empty($keyword)) {
            $sql .= $sqlcontxt . " hc.hadoop_cluster_name like '%" . $keyword . "%' ";
            $sqlcount .= $sqlcontxt . " hc.hadoop_cluster_name like '%" . $keyword . "%' ";
            $sqlcontxt = " and ";
        }
        //如果有开始时间和结束时间 则添加时间查询
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            //后面拼接and
            $sql .= $sqlcontxt . " hc.addition_time between ? and ? ";
            $sqlparams = array_merge($sqlparams, array($params['start_time'], $params['end_time']));
            $sqlcount .= $sqlcontxt . " hc.addition_time between ? and ? ";
            $sqlcountparams = array_merge($sqlcountparams, array($params['start_time'], $params['end_time']));
            $sqlcontxt = " and ";
        }
        //如果有集群状态
        if (!empty($params['status'])) {
            //后面拼接and
            //如果是在线状态 
            if ($params['status'] == 'online') {
                $sql .= $sqlcontxt . " hc.status in (1,2) ";
                $sqlcount .= $sqlcontxt . " hc.status in (1,2) ";
            } else {
                $sql .= $sqlcontxt . " hc.status = ? ";
                $sqlparams = array_merge($sqlparams, array($params['status']));
                $sqlcount .= $sqlcontxt . " hc.status = ? ";
                $sqlcountparams = array_merge($sqlcountparams, array($params['status']));
            }
        }
        if (!empty($limit)) {
            $sql .= " limit ?, ?";
            $sqlparams = array_merge($sqlparams, array($offset, $limit));
        }
        $sql .= ") as temp_hc on temp_hc.hadoop_cluster_uuid  =  hn.hadoop_cluster_uuid";
        //排序
        if (!empty($sort) && !empty($order)) {
            if ($sort != 'node_number') {
                $sql .= " order by " . $sort . " " . $order;
            } else {
                $sqlnew = "select hc.hadoop_cluster_uuid,count(hn.id) as node_count 
                from hadoop_cluster hc 
                left join hadoop_namenode hn on hn.hadoop_cluster_uuid = hc.hadoop_cluster_uuid 
                group by hc.hadoop_cluster_uuid
                order by node_count " . $order;
                ;
                $result = $this->dbSelect($sqlnew);
                $uuidarr = [];
                foreach ($result as $each) {
                    $uuidarr[] = "'" . $each['hadoop_cluster_uuid'] . "'";
                }
                ;
                $str = implode(',', $uuidarr);
                $sql .= " order by field(temp_hc.hadoop_cluster_uuid,$str)";
            }
        } else {
            //默认按照时间倒序进行排序
            $sql .= " order by addition_time desc";
        }
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $count = $this->dbSelect($sqlcount, $sqlcountparams);
        $info = array(
            'rows' => array(),
            'total' => $count[0]['count'],
            'authinfo' => $this->getClusterAuthInfo()
        );
        //如果数据为空
        if (empty($result)) {
            return $info;
        }
        $clusteridlist = [];
        $user = xphp_get_user_info();
        //循环处理
        foreach ($result as $each) {
            if (!in_array($each['hadoop_cluster_uuid'], $clusteridlist)) {
                //拥有者
                $owner = $this->getUUIDName($each['hadoop_cluster_uuid'], $each['bu_username'] ?? '--');
                $arr = explode(',', $owner);
                if ('admin' == $owner && $user['isThreePowers']) {
                    // 三权模式下，admin显示为sysadmin
                    foreach ($arr as $index => $name) {
                        if ($name == 'admin') {
                            $arr[$index] = 'sysadmin';
                        }
                    }
                    $owner = implode(',', $arr);
                }
                //创建者
                $creator = $each['bu_username'] ?? '--';
                if ('admin' == $creator && $user['isThreePowers']) {
                    // 三权模式下，admin显示为sysadmin
                    $creator = 'sysadmin';
                }
                // 租户内判断创建者等不等于当前用户,等于当前用户才能操作
                $opFlag = true;
                if ($user['tenantuuid'] && $each['user_uuid'] != $user['userUuid']) {
                    $opFlag = false;
                }
                //如果是不存在的集群
                $node_list = array();
                $node_list[] = array(
                    "namenode_ip" => $each['namenode_ip'],
                    "username" => $each['namenode_username'],
                    "rest_api" => $each['rest_api_port'],
                    "verify_type" => $each['verification_type'],
                    "online_status" => $each['node_status'],
                    "ssl_verify_flag" => $each['ssl_verify_flag'],
                    "version" => $each['node_version'] != null && $each['node_version'] != "" ? $each['node_version'] : "--",
                );
                $info['rows'][] = array(
                    "cluster_name" => $each['cluster_name'],
                    "cluster_uuid" => $each['hadoop_cluster_uuid'],
                    "node_number" => 1,
                    "add_time" => $each['add_time'],
                    "auth_status" => $each['auth_status'],
                    "online_flag" => $each['online_flag'],
                    "appliance_uuid" => $each['proxy_uuid'],
                    "appliance_des" => $each['agent_name'] . '(' . $each['ip'] . ')',
                    "refresh_time" => $each['refresh_time'],
                    "version" => $each['version'] != null && $each['version'] != "" ? $each['version'] : "--",
                    "node_info" => $node_list,
                    'op_flag' => $opFlag, // 当前用户是否可以操作该资源
                    'creator' => $creator,
                    'owner' => $owner,
                );
                $clusteridlist[] = $each['hadoop_cluster_uuid'];
                ;
            } else {
                for ($index = 0; $index < count($info['rows']); $index++) {
                    //如果是已经存在集群，则找出集群，新增节点信息
                    if ($each['hadoop_cluster_uuid'] == $info['rows'][$index]['cluster_uuid']) {
                        $node_info = array(
                            "namenode_ip" => $each['namenode_ip'],
                            "username" => $each['namenode_username'],
                            "rest_api" => $each['rest_api_port'],
                            "verify_type" => $each['verification_type'],
                            "online_status" => $each['node_status'],
                            "ssl_verify_flag" => $each['ssl_verify_flag'],
                            "version" => $each['node_version'] != null && $each['node_version'] != "" ? $each['node_version'] : "--",
                        );
                        $info['rows'][$index]['node_info'][] = $node_info;
                        $info['rows'][$index]['node_number']++;
                        break;
                    }
                }
            }
        }
        return $info;
    }

    /**
     * 得到hadoop授权信息
     * @param unknown $params
     * @return unknown
     */
    private function getClusterAuthInfo()
    {
        $systemHandler = new \app\v1\system\v0\logic\Index();
        $hadoopInfo = $systemHandler->getOneModuleLisenceInfo('hadoop');
        $licenseInfo = $systemHandler->getSystemLicenseType();
        $info = array(
            'hadoop' => $hadoopInfo,
            'licensetype' => $licenseInfo['licensetype']
        );
        return $info;
    }

    /**
     * 根据hadoop集群uuid获取当前的使用者是谁
     *
     * @param string $clusterUUID hadoop集群uuid
     * @param string $userName 拥有者
     * @return string
     */
    private function getUUIDName(string $clusterUUID, string $userName = ''): string
    {
        $sqlParams = [$clusterUUID];
        // 先 再分配的资源和资源组去查询
        $sql = "select user_uuid from mt_user_resource where resource_type = 60 and resource_uuid = ? limit 1";
        $array = $this->dbSelect($sql, $sqlParams);

        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 60";
            $array = $this->dbSelect($sql, $sqlParams);
        }

        if ($array) {
            $sql = "select user_name from bd_user where user_uuid in ('" . implode("','", array_column($array, 'user_uuid')) . "')";
            $data = $this->dbSelect($sql);
            $userName = implode(',', array_column($data, 'user_name'));
        }

        return $userName;
    }

    /**
     * 获取文件复制数据明细
     * @return void
     */
    public function getFilecopyReportList($params): array
    {
        $jobInfo = new JobInfo();
        $reportHandler = new ReportHandler();
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $search = $params['search'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $errorCode = $params['task_status'] ? explode(',', trim($params['task_status'])) : '';

        $errorCodeSql = !empty($errorCode) ? $reportHandler->getJobStatusSql($errorCode) : '';

        $filecopyModuleType = xphp_get_config('module', 'MODULE_TYPE')['FILE_COPY'];
        $filecopyTaskType = xphp_get_config('task', 'TASKTYPE')['FILE_COPY'];

        $sql = "SELECT distinct bht.id, bht.task_name, bht.module_type, bht.submodule_type,
                    bht.error_code, 
                    bht.details, bht.total_object_size, bht.total_object_valid_size, bht.total_object_transport_size,
                    bht.total_object_completed_size, bht.total_object_write_size,
                    unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time,
                    bht.user_name,bht.user_uuid, bht.task_uuid, bht.history_uuid
                FROM
                    bd_history_task bht
                WHERE
                    bht.module_type = ? AND bht.task_type = ?";

        $sqlCount = "SELECT count(bht.id) as total FROM bd_history_task bht WHERE bht.module_type = ? AND bht.task_type = ?";

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND bht.user_uuid IN ({$userUuidSql}) ";
            $sqlCount .= " AND bht.user_uuid IN ({$userUuidSql}) ";
        }

        $sqlParams = [$filecopyModuleType, $filecopyTaskType];
        $sqlCountParams = [$filecopyModuleType, $filecopyTaskType];

        // 按任务名搜索
        if ($this->checkEmpty($search)) {
            $sql .= ' AND bht.task_name like ? ';
            $sqlCount .= ' AND bht.task_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }

        // 按时间搜索
        if ($this->checkEmpty($startTime) && $this->checkEmpty($endTime)) {
            $sql .= " AND bht.start_time >= '$startTime' AND bht.start_time <=  '$endTime'";
            $sqlCount .= " AND bht.start_time >= '$startTime' AND bht.start_time <=  '$endTime'";
        }

        // 按任务状态搜索
        if (!empty($errorCodeSql)) {
            $sql .= " AND bht.error_code $errorCodeSql";
            $sqlCount .= " AND bht.error_code $errorCodeSql";
        }

        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        $total = intval($count[0]['total']);

        // 表头排序
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'task_name' => 'bht.task_name',
                'start_time' => 'bht.start_time',
                'finish_time' => 'bht.finish_time',
                'total_object_size' => 'bht.total_object_size',
                'total_object_valid_size' => 'bht.total_object_valid_size',
                'total_object_transport_size' => 'bht.total_object_transport_size',
                'total_object_write_size' => 'bht.total_object_write_size',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' bht.start_time desc' : ($sortArr[$params['sort']] . ' ' . $sortType . '');
            $sql .= " order by " . $sort;
        }

        $sql .= ' limit ? , ? ';
        $sqlParams = array_merge($sqlParams, array($offset, $limit));

        $data = $this->dbSelect($sql, $sqlParams);

        $records = array();

        if (!empty($data)) {
            foreach ($data as $d) {
                $filecopyDetails = $reportHandler->getFilecopyTaskDetails(json_decode($d['details'], true));

                $records[] = array(
                    'task_name' => $d['task_name'],
                    'source_object' => $filecopyDetails['source_name'],
                    'target_object' => $filecopyDetails['target_name'],
                    'start_time' => $this->parseDate($d['start_time']),
                    'finish_time' => $this->parseDate($d['finish_time']),
                    'total_object_size' => $d['total_object_size'],
                    'total_object_valid_size' => $d['total_object_valid_size'],
                    'total_object_transport_size' => $d['total_object_transport_size'],
                    'total_object_write_size' => $d['total_object_write_size'],
                    'task_status' => $d['error_code'],  // 任务状态
                    'task_status_des' => $jobInfo->getHistoryJobResultDes($d['error_code']),
                    'user' => $d['user_name']
                );
            }
        }

        return ['rows' => $records, 'total' => $total];
    }

    /**
     * 获取K8S报表数据明细
     * @param mixed $params
     * @return void
     */
    public function getK8sReportList($params): array
    {
        $jobInfo = new JobInfo();
        $reportHandler = new ReportHandler();
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $search = $params['search'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $errorCode = $params['task_status'] ? explode(',', trim($params['task_status'])) : '';

        $errorCodeSql = !empty($errorCode) ? $reportHandler->getJobStatusSql($errorCode) : '';

        $k8sModuleType = xphp_get_config('module', 'MODULE_TYPE')['KUBERNETES'];
        $k8sBackupTaskType = xphp_get_config('task', 'TASKTYPE')['KUBE_BACKUP'];

        $sql = "SELECT distinct bht.id, bht.task_name, bht.module_type, bht.submodule_type,
                    bht.error_code, 
                    bht.total_object_size, bht.total_object_valid_size, bht.total_object_transport_size,
                    bht.total_object_completed_size, bht.total_object_write_size,
                    unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time,
                    bht.user_name, bht.task_uuid, bht.history_uuid
                FROM
                    bd_history_task bht
                WHERE
                    bht.module_type = ? AND bht.task_type = ?";

        $sqlCount = "SELECT count(bht.id) as total FROM bd_history_task bht WHERE bht.module_type = ? AND bht.task_type = ?";

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND bht.user_uuid IN ({$userUuidSql}) ";
            $sqlCount .= " AND bht.user_uuid IN ({$userUuidSql}) ";
        }

        $sqlParams = [$k8sModuleType, $k8sBackupTaskType];
        $sqlCountParams = [$k8sModuleType, $k8sBackupTaskType];

        // 按任务名搜索
        if ($this->checkEmpty($search)) {
            $sql .= ' AND bht.task_name like ? ';
            $sqlCount .= ' AND bht.task_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }

        // 按时间搜索
        if ($this->checkEmpty($startTime) && $this->checkEmpty($endTime)) {
            $sql .= " AND bht.start_time >= '$startTime' AND bht.start_time <=  '$endTime'";
            $sqlCount .= " AND bht.start_time >= '$startTime' AND bht.start_time <=  '$endTime'";
        }

        // 按任务状态搜索
        if (!empty($errorCodeSql)) {
            $sql .= " AND bht.error_code $errorCodeSql";
            $sqlCount .= " AND bht.error_code $errorCodeSql";
        }

        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        $total = intval($count[0]['total']);

        // 表头排序
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'task_name' => 'bht.task_name',
                'start_time' => 'bht.start_time',
                'finish_time' => 'bht.finish_time',
                'total_object_size' => 'bht.total_object_size',
                'total_object_valid_size' => 'bht.total_object_valid_size',
                'total_object_transport_size' => 'bht.total_object_transport_size',
                'total_object_write_size' => 'bht.total_object_write_size',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' bht.start_time desc' : ($sortArr[$params['sort']] . ' ' . $sortType . '');
            $sql .= " order by " . $sort;
        }

        $sql .= ' limit ? , ? ';
        $sqlParams = array_merge($sqlParams, array($offset, $limit));

        $data = $this->dbSelect($sql, $sqlParams);

        $records = array();

        if (!empty($data)) {
            foreach ($data as $d) {
                $records[] = array(
                    'task_name' => $d['task_name'],
                    'start_time' => $this->parseDate($d['start_time']),
                    'finish_time' => $this->parseDate($d['finish_time']),
                    'total_object_size' => $d['total_object_size'],
                    'total_object_valid_size' => $d['total_object_valid_size'],
                    'total_object_transport_size' => $d['total_object_transport_size'],
                    'total_object_write_size' => $d['total_object_write_size'],
                    'task_status' => $d['error_code'],  // 任务状态
                    'task_status_des' => $jobInfo->getHistoryJobResultDes($d['error_code']),
                    'user' => $d['user_name']
                );
            }
        }

        return ['rows' => $records, 'total' => $total];
    }

    // <----------------------------- END CUSTOM REPORT DETAIL --------------------------------------------->


    // <----------------------------- BEGIN EMAIL NOTICE --------------------------------------------->

    /**
     * 获取报表邮件信息
     * @param [type] $reportType 报表模块类型
     * @param [type] $id email_notice_id
     * @param [type] $noticeType 通知类型 1：日报 2：周报 3：月报 4：年报
     * @return void
     */
    public function getReportNoticeInfo($reportType, $id, $noticeType): array
    {
        $reportHandler = new ReportHandler();
        $nodeInfo = $this->getNodeInfo();
        $params = array();
        $params['start_time'] = date('Y-m-d H:i:s', strtotime('-7 days'));
        $params['end_time'] = date('Y-m-d H:i:s');
        $alarmInfo = $this->getAlarmInfo($noticeType);

        $noticeTypeDes = '';
        switch ($noticeType) {
            case 1:
                $noticeTypeDes = xphp_get_lang('UI_REPORT_DAILY');
                break;
            case 2:
                $noticeTypeDes = xphp_get_lang('UI_REPORT_WEEKLY');
                break;
            case 3:
                $noticeTypeDes = xphp_get_lang('UI_REPORT_MONTHLY');
                break;
            case 4:
                $noticeTypeDes = xphp_get_lang('UI_REPORT_ANNALS');
                break;
            default:
                break;
        }

        $nameInfo = $reportHandler->getReportDetail($id, $noticeTypeDes);

        $url = 'https://' . $_SERVER['SERVER_ADDR'] . '/report.php?';

        switch ($reportType) {
            case 1:  //组合虚拟机模块报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '1';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-vm-report-oem.html');
                } elseif (xphp_get_config('app', 'lang') == 'en-us') {
                    $message = file_get_contents(DATA_PATH . 'email/email-vm-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-vm-report.html');
                }

                $emailContent = $reportHandler->vmEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_REPORT_VM');
                break;
            case 2:  //组合客户端报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '2';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-client-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-client-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-client-report.html');
                }

                $emailContent = $reportHandler->clientEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_CLIENT_REPORT');
                break;
            case 3:  //组合实时保护报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '3';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-cdp-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-cdp-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-cdp-report.html');
                }

                $emailContent = $reportHandler->cdpEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_REPORT_CDP_REPORT');
                break;
            case 4:  //组合NAS设备报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '4';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-nas-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-nas-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-nas-report.html');
                }

                $emailContent = $reportHandler->nasEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_REPORT_NAS_REPORT');
                break;
            case 5:  //组合存储报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '5';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-storage-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-storage-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-storage-report.html');
                }

                $emailContent = $reportHandler->storageEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_REPORT_STORAGE');
                break;
            case 6:  //组合任务报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '6';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-job-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-job-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-job-report.html');
                }

                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_PLATFORM_JOB_REPORT');

                $emailContent = $reportHandler->taskEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                break;
            case 7:  //组合M365模块报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '7';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-app-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-app-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-app-report.html');
                }

                $emailContent = $reportHandler->appEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_REPORT_APP');
                break;
            case 8:  //组合公有云模块报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '8';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-public-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-public-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-public-report.html');
                }

                $emailContent = $reportHandler->publicEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_REPORT_PUBLIC_REPORT');
                break;
            case 9:  //组合私有云模块报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '9';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-public-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-public-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-public-report.html');
                }

                $emailContent = $reportHandler->publicEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_REPORT_PRIVATE_REPORT');
                break;
            case 10:  //组合对象存储模块报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '34';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-ob-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-ob-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-ob-report.html');
                }

                $emailContent = $reportHandler->obEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_PLATFORM_OBS_REPORT');
                break;
            case 11:  // 组合hadoop模块报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '11';
                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
                    $message = file_get_contents(DATA_PATH . 'email/email-hadoop-report-oem.html');
                } else if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-hadoop-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-hadoop-report.html');
                }

                $emailContent = $reportHandler->hadoopEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_PLATFORM_HADOOP_REPORT');
                break;
            case 12: // 组合文件复制报表相关日报html，并返回
                $params = array();
                $params['overview_uuid'] = '26';

                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-filecopy-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-filecopy-report.html');
                }
                $emailContent = $reportHandler->filecopyEmailContent($detailInfo['overviewList'], $message, $alarmInfo, $nodeInfo, $nameInfo);

                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_FILE_COPY_REPORT');
                break;
            case 13: // 组合Kubernets报表相关日报html
                $params = array();
                $params['overview_uuid'] = '28';

                $detailInfo = $this->getReportTemplateOverview($params, $id, true);

                if (xphp_get_config('app', 'lang') == "en-us") {
                    $message = file_get_contents(DATA_PATH . 'email/email-kubernetes-report-en.html');
                } else {
                    $message = file_get_contents(DATA_PATH . 'email/email-kubernetes-report.html');
                }

                $emailContent = $reportHandler->k8sEmailContent($detailInfo['overviewList'], $message, $alarmInfo, $nodeInfo, $nameInfo);
                $emailTitle = '【' . $noticeTypeDes . '】' . xphp_get_lang('UI_KUBERNETES_REPORT');

                break;
            default:
                break;
        }
        return array(
            'title' => $emailTitle,
            'content' => $emailContent
        );
    }

    // <----------------------------- END EMAIL NOTICE --------------------------------------------->

    /**
     * 获取自定义报表配置详情
     * @param mixed $params
     * @return array{detail: mixed}
     */
    public function getTemplateDetail($params)
    {
        $uuid = $params['detail_uuid'];
        $sql = "select detail from bd_report_template 
                where template_uuid = '$uuid'";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        return json_decode($data[0]['detail'], true);
    }

    /**
     * 获取概览页告警次数
     * @param $moduleType moduleType
     * @param $reportType reportType
     * @return array
     */
    public function getAlarmNumber($params)
    {
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];

        $systemAlarmSql = "SELECT
                count(*) AS total,
                alarm_day 
            FROM
                ( SELECT DATE_FORMAT( alarm_time, '%Y-%m-%d' ) AS alarm_day FROM bd_system_alarm WHERE alarm_level != 1 ORDER BY alarm_time ) AS bsa 
            GROUP BY
                alarm_day";
        $systemAlarmData = $this->dbSelect($systemAlarmSql);

        $taskAlarmSql = "SELECT
                count(*) AS total,
                alarm_day 
            FROM
                ( SELECT DATE_FORMAT( alarm_time, '%Y-%m-%d' ) AS alarm_day FROM bd_task_alarm WHERE alarm_level != 1 ORDER BY alarm_time ) AS bsa 
            GROUP BY
                alarm_day";
        $taskAlarmData = $this->dbSelect($taskAlarmSql);

        $startTimestamp = strtotime(date('Y-m-d', strtotime($startTime)));
        $endTimestamp = strtotime(date('Y-m-d', strtotime($endTime)));

        $systemAlarm = [];
        $taskAlarm = [];

        foreach ($systemAlarmData as $item) {
            $systemAlarm[] = array(
                'alarm_day' => $item['alarm_day'],
                'total' => $item['total']
            );
        }

        foreach ($taskAlarmData as $item) {
            $taskAlarm[] = array(
                'alarm_day' => $item['alarm_day'],
                'total' => $item['total']
            );
        }

        $record = array("systemAlarm" => [], "taskAlarm" => []);
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('Y-m-d', $i);

            $matchingSystemAlarmItem = array_filter($systemAlarm, function ($item) use ($day) {
                return $item['alarm_day'] === $day;
            });

            $matchingTaskAlarmItem = array_filter($taskAlarm, function ($item) use ($day) {
                return $item['alarm_day'] === $day;
            });

            $matchingSystemAlarmItem = array_values($matchingSystemAlarmItem);
            $matchingTaskAlarmItem = array_values($matchingTaskAlarmItem);

            if (!empty($matchingSystemAlarmItem)) {
                $record['systemAlarm'][] = array(
                    'time' => $day,
                    'value' => $matchingSystemAlarmItem[0]['total']
                );
            } else {
                $record['systemAlarm'][] = array(
                    'time' => $day,
                    'value' => 0
                );
            }

            if (!empty($matchingTaskAlarmItem)) {
                $record['taskAlarm'][] = array(
                    'time' => $day,
                    'value' => $matchingTaskAlarmItem[0]['total']
                );
            } else {
                $record['taskAlarm'][] = array(
                    'time' => $day,
                    'value' => 0
                );
            }
        }

        return $record;
    }

    /**
     * 获取告警信息（用于邮件内容显示）
     * @param mixed $noticeType
     * @return array{noticeTypeDes: string, systemAlarm: mixed, taskAlarm: mixed}
     */
    private function getAlarmInfo($noticeType): array
    {
        $days = 0;
        $noticeTypeDes = '';
        switch ($noticeType) {
            case 1: // 近一日
                $days = 1;
                $noticeTypeDes = '一天';
                break;
            case 2: // 近一周
                $days = 7;
                $noticeTypeDes = '一周';
                break;
            case 3: // 近一月
                $days = 30;
                $noticeTypeDes = '一个月';
                break;
            default:
                $days = 7; // 默认一周
                break;
        }

        $nowTime = date('Y-m-d');
        $reducedTime = date('Y-m-d', strtotime($nowTime . ' - ' . ($days - 1) . ' days'));
        $nowTimeStr = $nowTime . ' 23:59:59';
        $reducedTimeStr = $reducedTime . ' 00:00:00';

        $taskAlarmSql = "select count(*) as taskAlarm from bd_task_alarm 
                    WHERE alarm_time BETWEEN ? AND ? AND alarm_level != 1";
        $taskAlarmParams = array($reducedTimeStr, $nowTimeStr);
        $taskCount = $this->dbSelect($taskAlarmSql, $taskAlarmParams);

        $systemAlarmSql = "select count(*) as systemAlarm from bd_system_alarm 
                           WHERE alarm_time BETWEEN ? AND ? AND alarm_level != 1";
        $systemAlarmParams = array($reducedTimeStr, $nowTimeStr);
        $systemCount = $this->dbSelect($systemAlarmSql, $systemAlarmParams);

        return array(
            'task_alarm_number' => $taskCount[0]['taskAlarm'] ?? 0,
            'system_alarm_number' => $systemCount[0]['systemAlarm'] ?? 0,
            'notice_type_des' => $noticeTypeDes
        );
    }

    public function getEmail()
    {
        $sql = "select email_notice_flag from bd_email_notice LIMIT 1";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        return array(
            'emailFlage' => $data[0]['email_notice_flag']
        );
    }

    public function getAddressInfo()
    {
        $sql = "select receive_email from bd_email_notice where id = 1";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        return array(
            'addressInfo' => $data[0]['receive_email']
        );
    }

    /**
     * 下载报告
     * @param array $param 参数
     * @return array|stream
     */
    public function reportDownLoadUrl(array $param)
    {

        $token = token();
        $fileCounter = 0;
        $data = $param['data'];
        $dir = dirname(__DIR__, 7) . '/';
        // 正则替换所有的相对位置为绝对路径
        $data = preg_replace_callback(
            '/<img\s+[^>]*?src="([^"]*)"/i',
            function ($matches) use ($dir, &$fileCounter) {
                $src = $matches[1];
                if (preg_match('/^data:\S+/', $src, $base64Matches)) {
                    // 移除 data:image/png;base64, 部分，只保留 Base64 编码的实际内容
                    $base64Image = str_replace('data:image/png;base64,', '', $base64Matches[0]);
                    $base64Image = str_replace(' ', '+', $base64Image); // 替换空格为加号（如果有的话）
                    // 解码 Base64 编码的字符串为二进制数据
                    $imageData = base64_decode($base64Image);
                    $path = DATA_PATH . '/report/pdf/';
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $fileName = $fileCounter . '.png';
                    $fileCounter++;
                    // 构建完整的文件路径
                    $filePath = $path . $fileName;
                    // 将二进制数据写入文件
                    file_put_contents($filePath, $imageData);
                    return '<img src="' . $filePath . '"';
                }
                // 检查src是否已经是绝对路径（以http://或https://开头）
                else if (preg_match('/^https?:\/\//i', $src)) {
                    $src = explode('web_ng/', $src);
                    return '<img src="' . $dir . 'web_ng/' . $src[1] . '"'; // 如果是绝对路径，则保持不变
                } else {
                    // 如果是相对路径，则添加基础URL
                    return '<img src="' . $dir . '/' . ltrim($src, './') . '"';
                }
            },
            $data
        );
        $titleText = xphp_get_lang('UI_REPORT_REPORT_OVERVIEW');
        $style = '
        <link rel="stylesheet" type="text/css" href="' . $dir . '/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
        <link rel="stylesheet" type="text/css" href="' . $dir . '/assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css"/>
        <link rel="stylesheet" type="text/css" href="' . $dir . '/assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css"/>
        <link rel="stylesheet" type="text/css" href="' . $dir . '/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
        <link rel="stylesheet" type="text/css" href="' . $dir . '/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
        <link rel="stylesheet" type="text/css" href="' . $dir . '/assets/global/plugins/daterangepicker/daterangepicker.css"/>
        <link href="' . $dir . '/assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
        <link href="' . $dir . '/assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />';
        $html = '
        <!DOCTYPE html>  
        <html>  
        <head>  
            <title>' . $titleText . '</title>  
            ' . $style . ' 
        </head>  
        <body>  
           ' . $data . '
        </body>  
        </html>';
        $path = DATA_PATH . '/report/pdf/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if (!file_put_contents($path . 'report.txt', $html)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_REPORT_SAVE_FAIL')
            ];
        }
        // 输出下载的URL
        $reportUuid = $param['downname'];
        xphp_set_cache($token, true, true);
        $url = '/api/v1/report/overview/download?token=' . $token . '&downname=' . $reportUuid . '&x-api-version=1.0-rev0';
        $item = ['', xphp_get_lang('UI_VERIFY_REPORT_DOWNLOAD')];
        $this->systemLog('PT_INDUSTRY_REPORT_OPERATE', $item);
        return [
            'code' => 0,
            'msg' => [
                'url' => $url
            ]
        ];
    }

    /**
     * 下载报告
     * @param array $param 参数
     * @return array|stream
     */
    public function reportDownLoad(array $param)
    {
        if (empty($param['token']) || empty(xphp_get_cache($param['token'], true))) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_SETTINGS_UPDATE_PARAMS_ERROR')
            ];
        }
        $reportUuid = $param['downname'];
        $title = '';
        switch ($reportUuid) {
            case 'overview':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_REPORT_OVERVIEW');
                break;
            case 'task':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_TASK_REPORT');
                break;
            case 'app':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_APP_REPORT');
                break;
            case 'cdp':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_CDP_REPORT');
                break;
            case 'client':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_CLIENT_REPORT');
                break;
            case 'hadoop':
                $title = xphp_get_lang('UI_PLATFORM_HADOOP_REPORT');
                break;
            case 'nas':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_NAS_REPORT');
                break;
            case 'ob':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_OB_REPORT');
                break;
            case 'public':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_PRIVATE_REPORT');
                break;
            case 'private':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_PUBLIC_REPORT');
                break;
            case 'storage':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_STORAGE_REPORT');
                break;
            case 'vm':
                $title = xphp_get_lang('UI_REPORT_VINCHIN_VM_REPORT');
                break;
            default:
                break;
        }
        xphp_set_cache($param['token'], false, true);
        $data = $this->getOverviewHtml();
        $downname = $title . '.pdf';
        return (new Xpdf())->makePdf($data['msg']['html'], 0, md5($reportUuid), $downname, $data['msg']['water'], 1, $title);
    }

    /**
     * 获取转换后的html
     * @param string
     * @return string
     */
    public function getOverviewHtml()
    {
        $url = DATA_PATH . '/report/pdf/report.txt';
        $html = file_get_contents($url);
        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $waters = $company_email['company_name'];
        return [
            'code' => 0,
            'msg' => [
                'html' => $html,
                'water' => $waters ?? '',
            ]
        ];
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