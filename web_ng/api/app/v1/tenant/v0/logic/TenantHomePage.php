<?php

namespace app\v1\tenant\v0\logic;

use app\v1\common\logic\Base;
use app\v1\homepage\v0\logic\homePage;
use app\v1\resources\v0\logic\Index as ResourceIndex;
use app\v1\tenant\v0\logic\Index as LogicIndex;

/**
 * note          租户管理首页
 * @author      liushuai@vinchin.com
 * @date         2024/4/16 16:20
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class TenantHomePage extends Base
{

     /**
     * 获取租户首页
     * @param unknown $params
     */
    public function getHomePageInfo($params){
        $tenantUuid = $params['tenant_uuid'];
        $adminUuid = $params['user_uuid'];
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_TENANT_GET_HOMEPAGE_DATA'),
            'data' => array(),
        );
        $info = array(
            'user_type' => 'user', //租户类型 管理员为admin,租户下其他用户为user
            'current_task' => 0,
            'history_task' => 0,
            'backup_total_size' => 0,
            'backup_total_size_unit' => 'kb',
            'module_info' => array(
                'vm' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'db' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'public_cloud' => array( //公有云实例
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'os' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'fs' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'nas' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'm365' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'exchange_online' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'hadoop' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'obs' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'kubernetes' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'os_copy' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'fs_copy' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'nas_copy' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
                'db_copy' => array(
                    'flag' => false, //是否授权该模块
                    'auth_used' => 0, //使用数量
                    'auth_total' => -1, //授权数量,-1表示无限制
                    'backup_size_str' => '--', //备份数据容量+单位
                    'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
                    'backup_size_int' => 0, //备份数据大小,转换后保留两位
                    'bakcup_size_unit' => '--', //备份数据单位
                ),
            ),
            'backup_chart' => array(),


        );

        //获取最近天数
        $recently_days = $params['backup_days'];

        //获取当前用户uuid
        $user_uuid = $adminUuid ? $adminUuid : xphp_get_user_info()['userUuid'];
        //获取当前租户uuid
        $tenant_uuid = $tenantUuid ? $tenantUuid : xphp_get_user_info()['tenantuuid'];
        //判断当前用户类型是一般用户还是租户管理员
        $sql_check_admin = "select count(id) as total from bd_tenant where admin_uuid = ?";
        $result_check_admin = $this->dbSelect($sql_check_admin,array($user_uuid));
        $result_check_total = $result_check_admin[0]['total'];
        if(!empty($tenant_uuid)){
            if($result_check_total == 0){
                $info['user_type'] = 'user';
            }else{
                $info['user_type'] = 'admin';
            }
        }
        //获取当前任务数
        $homepageHandler = new homePage;
        //获取基本信息
        $basicInfo = $homepageHandler->get_card_basic_data($user_uuid);
        $info['current_task'] = $basicInfo['value_current_count'];
        $info['history_task'] = $basicInfo['value_history_count'];
        $info['backup_total_size'] = $basicInfo['value_size'];
        $info['backup_total_size_unit'] = $basicInfo['value_size_unit'];

        //获取授权信息
        $permission = xphp_get_user_info()['permission'];
        //获取当前租户是按照什么方式进行授权的,按数量还是按容量
        $tenantHandler = new Index;
        $authCommon = $tenantHandler->pGetTenantSettings($tenant_uuid);
        
        $auth_way = intval($authCommon['auth_way']);
        //获取可用数量集合


        //获取虚拟机是否有其授权
        $info['module_info']['vm']['flag'] = (in_array('vmprotect', $permission) && in_array('vcenter_manager', $permission)) ? true : false;
        if($info['module_info']['vm']['flag']){
        $info['module_info']['vm'] = $this->getVmInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取数据库授权
        $info['module_info']['db']['flag'] = in_array('db_protect', $permission) ? true : false;
        if($info['module_info']['db']['flag']){
        $info['module_info']['db'] = $this->getDbInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取公有云授权
        $info['module_info']['public_cloud']['flag'] = (in_array('awsprotect', $permission) && in_array('cloud_platform', $permission)) ? true : false;
        if($info['module_info']['public_cloud']['flag']){
        $info['module_info']['public_cloud'] = $this->getpublicCloudInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取私有云授权
        $info['module_info']['private_cloud']['flag'] = in_array('cloud_platform_private', $permission) ? true : false;
        if($info['module_info']['private_cloud']['flag']){
            $info['module_info']['private_cloud'] = $this->getprivateCloudInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取操作系统授权
        $info['module_info']['os']['flag'] = (in_array('osbackup', $permission)||in_array('complete_machine', $permission)) ? true : false;
        if($info['module_info']['os']['flag']){
        $info['module_info']['os'] = $this->getOsInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取文件授权
        $info['module_info']['fs']['flag'] = in_array('fileprotect', $permission) ? true : false;
        if($info['module_info']['fs']['flag']){
            $info['module_info']['fs'] = $this->getFsInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取Nas授权
        $info['module_info']['nas']['flag'] = (in_array('nas_protect', $permission) && in_array('nasmanager', $permission)) ? true : false;
        if($info['module_info']['nas']['flag']){
            $info['module_info']['nas'] = $this->getNasInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取m365授权
        $info['module_info']['m365']['flag'] = (in_array('office365_protect', $permission) && in_array('exchange_organization', $permission)) ? true : false;
        if($info['module_info']['m365']['flag']){
            $info['module_info']['m365'] = $this->getm365Info($user_uuid,$authCommon,$tenant_uuid);
        }
        //获取m365授权
        $info['module_info']['exchange_online']['flag'] = (in_array('office365_protect', $permission) && in_array('exchange_organization', $permission)) ? true : false;
        if($info['module_info']['exchange_online']['flag']){
            $info['module_info']['exchange_online'] = $this->get_exchange_online_Info($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取hadoop授权
        $info['module_info']['hadoop']['flag'] = (in_array('hadoop_protect', $permission) && in_array('hadoop_cluster', $permission)) ? true : false;
        if($info['module_info']['hadoop']['flag']){
            $info['module_info']['hadoop'] = $this->getHadoopInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取obs授权
        $info['module_info']['obs']['flag'] = (in_array('obs_protect', $permission) && in_array('obsmanager', $permission)) ? true : false;
        if($info['module_info']['obs']['flag']){
            $info['module_info']['obs'] = $this->getObsInfo($user_uuid,$authCommon,$tenant_uuid);
        }

      
        //获取k8s授权
        $info['module_info']['kubernetes']['flag'] = in_array('k8s_protect', $permission) ? true : false;
        if($info['module_info']['kubernetes']['flag']){
            $info['module_info']['kubernetes'] = $this->getKubernetesInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取整机复制
        $info['module_info']['os_copy']['flag'] = (in_array('machine_copy', $permission) || in_array('vol_cdp_copy', $permission)) ? true : false;
        if($info['module_info']['os_copy']['flag']){
            $info['module_info']['os_copy'] = $this->getOSCopyInfo($user_uuid,$authCommon,$tenant_uuid);
        }

        //获取文件复制
        $info['module_info']['fs_copy']['flag'] = in_array('file_copy_protect', $permission) ? true : false;
        if($info['module_info']['fs_copy']['flag']){
            $info['module_info']['fs_copy'] = $this->getFSCopyInfo($user_uuid,$authCommon,$tenant_uuid);
        }

         //获取nas复制
         $info['module_info']['nas_copy']['flag'] = in_array('file_copy_protect', $permission) ? true : false;
         if($info['module_info']['nas_copy']['flag']){
             $info['module_info']['nas_copy'] = $this->getFSCopyInfo($user_uuid,$authCommon,$tenant_uuid);
         }

        //获取数据库复制
        $info['module_info']['db_copy']['flag'] = in_array('dbcdpcopy', $permission) ? true : false;
        if($info['module_info']['db_copy']['flag']){
            $info['module_info']['db_copy'] = $this->getDBCopyInfo($user_uuid,$authCommon,$tenant_uuid);
        }




        //近期数据
        
        $info['backup_chart'] = $homepageHandler->getTimepointWriteSize($recently_days);





          
        $resultInfo['data'] = $info;
        return $resultInfo;
    }



    //获取授权数量或容量
    public function getAuthDataTotalAndUsed($module,$tenant_uuid, $subType = ''){
        // $module = xphp_get_config('module')['MODULE_TYPE']['DB'];
        $auth_tenant_data = (new Tenant())->checkTenantAuth($module,array(),"",true,$tenant_uuid,$subType);
       
        $tenantHandler = new Index;
        $config = $tenantHandler->pGetTenantSettings($tenant_uuid);
        $info['auth_used'] = $auth_tenant_data['used'];
        $info['auth_type'] = $auth_tenant_data['auth_type']; //模块的auth_way, 1表示数量, 2表示容量
        // $config = $config['auth_way'];// 多租户授权方式, 和系统授权方式保持一致
        $tenanHandler = new Tenant;
        $tenantData = $tenanHandler->getTenantUserList(array('tenant_uuid'=>$tenant_uuid));
        //获取所有租户的容量
        $ALL_tenant_backup_quota = $tenantData['rows'];
        $quota_total = 0;
        foreach ($ALL_tenant_backup_quota as $value) {
            if($value['tenant_uuid'] == $tenant_uuid){
                $quota_total = $value['total_backup_data'];  
                $quota_used = $value['used_backup_data'];
            }
        }
        if($auth_tenant_data['auth_type'] == 2){ //1为数量, 2为容量
            $info['auth_used'] = v1_calsize($auth_tenant_data['used'],true);
            $info['auth_total'] = $quota_total;
        }else{
            switch($module){
                case xphp_get_config('module')['MODULE_TYPE']['VM']:
                    if($subType == xphp_get_config('module', 'VM_SUB_MODULE')['VM']){
                        $info['auth_total'] = $config['vm_num'] ? intval($config['vm_num']) : 0;
                    }else if($subType == xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']){
                        $info['auth_total'] = $config['aws_num'] ? intval($config['aws_num']) : 0;
                    }else if($subType == xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD']){
                        $info['auth_total'] = $config['ops_num'] ? intval($config['ops_num']) : 0;
                    }
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['FS']:
                    $info['auth_total'] = $config['file_num'] ? intval($config['file_num']) : 0;
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['DB']:
                    $info['auth_total'] = $config['db_num'] ? intval($config['db_num']) : 0;
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['OS']:
                    $info['auth_total'] = $config['os_num'] ? intval($config['os_num']) : 0;
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['NAS']:
                    $info['auth_total'] = $config['nas_num'] ? intval($config['nas_num']) : 0;
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['M365']:
                    if($subType == 2){
                        $info['auth_total'] = $config['m365_online_num'] ? intval($config['m365_online_num']) : 0;
                    }else if($subType == 1){
                        $info['auth_total'] = $config['m365_num'] ? intval($config['m365_num']) : 0;
                    }
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['OBS']:
                    $info['auth_total'] = $config['obs_num'] ? intval($config['obs_num']) : 0;
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['HADOOP']:
                    $info['auth_total'] = $config['hadoop_num'] ? intval($config['hadoop_num']) : 0;
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['KUBERNETES']:
                    $info['auth_total'] = $config['k8s_num'] ? intval($config['k8s_num']) : 0;
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['FILE_COPY']:
                    if($subType == xphp_get_config('module', 'SUBMODULE_TYPE')['FS']){
                        $info['auth_total'] = $config['filecopy_num'] ? intval($config['filecopy_num']) : 0;
                    }else if($subType == xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']){
                        $info['auth_total'] = $config['nascopy_num'] ? intval($config['nascopy_num']) : 0;
                    }
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['DB_CDP']:
                    $info['auth_total'] = $config['dbcdpcopy_num'] ? intval($config['dbcdpcopy_num']) : 0;
                    break;
                case xphp_get_config('module')['MODULE_TYPE']['VOL_CDP']:
                    $info['auth_total'] = $config['machinecopy_num'] ? intval($config['machinecopy_num']) : 0;
                    break;
               default:
                $info['auth_total'] = 0;
            }
        }
        return $info;
    }

   



    /**
     * 获取虚拟机数据
     */
    public function getVmInfo($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );


        $module = xphp_get_config('module')['MODULE_TYPE']['VM'];
        $subType = xphp_get_config('module', 'VM_SUB_MODULE')['VM'];
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.user_uuid = ? and bbt.task_type = ? and bbt.copy_flag = ? and vbt.hypervisor_type not in ('".$cloudDes."')";
        $data = $this->dbSelect($sql, array($user_uuid,xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }

    



    /**
     * 获取公有云数据
     */
    public function getPublicCloudInfo($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );

        $module = xphp_get_config('module')['MODULE_TYPE']['VM'];
        $subType = xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD'];
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
       
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.user_uuid = ? and bbt.task_type = ? and bbt.copy_flag = ? and vbt.hypervisor_type in ('".$cloudDes."')";
        $data = $this->dbSelect($sql, array($user_uuid, xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }

     /**
     * 获取数据库数据
     */
    public function getDbInfo($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );
        $module = xphp_get_config('module')['MODULE_TYPE']['DB'];
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid));
        //数据库备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, db_backup_timepoint dbt where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($user_uuid, xphp_get_config('module')['MODULE_TYPE']['DB'], xphp_get_config('task')['TASKTYPE']['DB_BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $dbBackupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($dbBackupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $dbBackupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }


    /**
     * 获取操作系统数据
     */
    public function getOsInfo($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );
        $module = xphp_get_config('module')['MODULE_TYPE']['OS'];
        $subType = "";
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, os_backup_timepoint obt where bbt.timepoint_uuid = obt.timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($user_uuid, xphp_get_config('module')['MODULE_TYPE']['OS'], xphp_get_config('task')['TASKTYPE']['OS_BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $osBackupSize = intval($data[0]['backup_size']);
        $osBackupSizeDes = v1_calsize_to_value_and_unit($osBackupSize,true);
        $info['backup_size_str'] = $osBackupSizeDes['value'].$osBackupSizeDes['unit'];
        $info['backup_size'] = $osBackupSize;
        $info['backup_size_int'] = $osBackupSizeDes['value'];
        $info['bakcup_size_unit'] = $osBackupSizeDes['unit'];

        return $info;
    }


    /**
     * 获取文件数据
     */
    public function getFsInfo($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );
        $module = xphp_get_config('module')['MODULE_TYPE']['FS'];
        $subType = "";
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.sub_module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($user_uuid, xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('module','SUBMODULE_TYPE')['FS'], xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $fsBackupSize = intval($data[0]['backup_size']);
        $fsBackupSizeDes = v1_calsize_to_value_and_unit($fsBackupSize,true);
        $info['backup_size_str'] = $fsBackupSizeDes['value'].$fsBackupSizeDes['unit'];
        $info['backup_size'] = $fsBackupSize;
        $info['backup_size_int'] = $fsBackupSizeDes['value'];
        $info['bakcup_size_unit'] = $fsBackupSizeDes['unit'];
        return $info;
    }



     /**
     * 获取Nas数据
     */
    public function getNasInfo($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );
        $module = xphp_get_config('module')['MODULE_TYPE']['NAS'];
        $subType = "";
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($user_uuid, xphp_get_config('module')['MODULE_TYPE']['NAS'], xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $BackupSize = intval($data[0]['backup_size']);
        $BackupSizeDes = v1_calsize_to_value_and_unit($BackupSize,true);
        $info['backup_size_str'] = $BackupSizeDes['value'].$BackupSizeDes['unit'];
        $info['backup_size'] = $BackupSize;
        $info['backup_size_int'] = $BackupSizeDes['value'];
        $info['bakcup_size_unit'] = $BackupSizeDes['unit'];
        return $info;
    }



    /**
     * 获取m365数据
     */
    public function getm365Info($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );
        $module = xphp_get_config('module')['MODULE_TYPE']['M365'];
        $subType = 1;
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, m365_backup_timepoint mbt where bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($user_uuid, xphp_get_config('module')['MODULE_TYPE']['M365'], xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $BackupSize = intval($data[0]['backup_size']);
        $BackupSizeDes = v1_calsize_to_value_and_unit($BackupSize,true);
        $info['backup_size_str'] = $BackupSizeDes['value'].$BackupSizeDes['unit'];
        $info['backup_size'] = $BackupSize;
        $info['backup_size_int'] = $BackupSizeDes['value'];
        $info['bakcup_size_unit'] = $BackupSizeDes['unit'];
        return $info;
    }



    public function get_exchange_online_Info($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );
        $module = xphp_get_config('module')['MODULE_TYPE']['M365'];
        $subType = 2;
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, m365_backup_timepoint mbt where bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($user_uuid, xphp_get_config('module')['MODULE_TYPE']['M365'], xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $BackupSize = intval($data[0]['backup_size']);
        $BackupSizeDes = v1_calsize_to_value_and_unit($BackupSize,true);
        $info['backup_size_str'] = $BackupSizeDes['value'].$BackupSizeDes['unit'];
        $info['backup_size'] = $BackupSize;
        $info['backup_size_int'] = $BackupSizeDes['value'];
        $info['bakcup_size_unit'] = $BackupSizeDes['unit'];
        return $info;
    }

    /**
     * 获取m365数据
     */
    public function getHadoopInfo($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );
        $module = xphp_get_config('module')['MODULE_TYPE']['HADOOP'];
        $subType = "";
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, fs_backup_timepoint fbt where
        bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? 
        and bbt.sub_module_type = ?";
        $data = $this->dbSelect($sql, array($user_uuid, xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('task')['TASKTYPE']['BACKUP'],
            xphp_get_config('app')['FLAG']['UNSET'],xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }

    /**
     * 获取m365数据
     */
    public function getObsInfo($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );
        $module = xphp_get_config('module')['MODULE_TYPE']['OBS'];
        $subType = "";
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, fs_backup_timepoint fbt where
        bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? 
        and bbt.sub_module_type = ?";
        $data = $this->dbSelect($sql, array($user_uuid, xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('task')['TASKTYPE']['BACKUP'],
            xphp_get_config('app')['FLAG']['UNSET'],xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }

    /**
     * 获取私有云数据
     */
    public function getprivateCloudInfo($user_uuid,$authCommon,$tenant_uuid){
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => '--', //备份数据单位
        );

        $module = xphp_get_config('module')['MODULE_TYPE']['VM'];
        $subType = xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.user_uuid = ? and bbt.task_type = ? and bbt.copy_flag = ? and vbt.hypervisor_type in ('".$cloudDes."')";
        $data = $this->dbSelect($sql, array($user_uuid,xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }


    //获取kubernetes
    public function getKubernetesInfo($user_uuid,$authCommon,$tenant_uuid) { 
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => 'B', //备份数据单位
        );
        //可用数量
        $module = xphp_get_config('module')['MODULE_TYPE']['KUBERNETES'];
        $subType = "";
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt where bbt.user_uuid = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($user_uuid,xphp_get_config('task')['TASKTYPE']['KUBE_BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }

    // 获取整机复制备份信息
    public function getOSCopyInfo($user_uuid,$authCommon,$tenant_uuid) { 
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => 'B', //备份数据单位
        );
        //可用数量
        $module = xphp_get_config('module')['MODULE_TYPE']['VOL_CDP'];
        $subType = "";
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(total_object_valid_size) as backup_size from bd_history_task where task_type = ? and module_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('task')['TASKTYPE']['VOL_CDP_REPLICATION'], xphp_get_config('module')['MODULE_TYPE']['VOL_CDP']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }

    // 获取文件复制备份信息
    public function getFSCopyInfo($user_uuid,$authCommon,$tenant_uuid) { 
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => 'B', //备份数据单位
        );
        //可用数量
        $module = xphp_get_config('module')['MODULE_TYPE']['FILE_COPY'];
        $subType = xphp_get_config('module', 'SUBMODULE_TYPE')['FS'];
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(total_object_valid_size) as backup_size from bd_history_task where task_type = ? and module_type = ? and submodule_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('task')['TASKTYPE']['FILE_COPY'], xphp_get_config('module')['MODULE_TYPE']['FILE_COPY'], xphp_get_config('module', 'SUBMODULE_TYPE')['FS']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }

     // 获取文件复制备份信息
     public function getNASCopyInfo($user_uuid,$authCommon,$tenant_uuid) { 
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => 'B', //备份数据单位
        );
        //可用数量
        $module = xphp_get_config('module')['MODULE_TYPE']['FILE_COPY'];
        $subType = xphp_get_config('module', 'SUBMODULE_TYPE')['NAS'];
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(total_object_valid_size) as backup_size from bd_history_task where task_type = ? and module_type = ? and submodule_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('task')['TASKTYPE']['FILE_COPY'], xphp_get_config('module')['MODULE_TYPE']['FILE_COPY'], xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }

    // 获取数据库复制备份信息
    public function getDBCopyInfo($user_uuid,$authCommon,$tenant_uuid) { 
        $info = array(
            'flag' => true,
            'auth_used' => 0,
            'auth_total' => 0,
            'backup_size_str' => '--', //备份数据容量+单位
            'backup_size' => 0, //备份数据大小,未转换大小的容量,单位k
            'backup_size_int' => 0, //备份数据大小,转换后保留两位
            'bakcup_size_unit' => 'B', //备份数据单位
        );
        //可用数量
        $module = xphp_get_config('module')['MODULE_TYPE']['DB_CDP'];
        $subType = "";
        $info = array_merge($info, $this->getAuthDataTotalAndUsed($module,$tenant_uuid,$subType));
        //备份数据
        $sql = "select sum(total_object_valid_size) as backup_size from bd_history_task where task_type = ? and module_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('task')['TASKTYPE']['CDP_DB_BACKUP'], xphp_get_config('module')['MODULE_TYPE']['DB_CDP']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize_to_value_and_unit($backupSize,true);
        $info['backup_size_str'] = $backupSizeDes['value'].$backupSizeDes['unit'];
        $info['backup_size'] = $backupSize;
        $info['backup_size_int'] = $backupSizeDes['value'];
        $info['bakcup_size_unit'] = $backupSizeDes['unit'];
        return $info;
    }



    







    
}
