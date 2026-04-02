<?php

namespace app\v1\backupmanager\v0\logic;

use app\v1\common\logic\Base;
use app\v1\homepage\v0\logic\homePage;
use app\v1\job\v0\logic\JobInfo;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\logic\Auth;
use app\v1\system\v0\logic\Index;

/**
 * note          容器 脚本管理
 * @author       liushuai@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class backupCenter extends Base
{
    /**
     * 获取备份中心基本信息
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/10/9
     * @return unknown
     */
    public function getBasicInfo($params)
    {
        //获取参数
        //获取IP地址
        $ip = $params['ip'];
        //获取端口号
        $port = $params['port'];
        //获取用户名
        $user_name = $params['user_name'];
        //获取密码
        $password = $params['password'];
        //获取api-key(api-key的验证在request进行)
        // $api_key = $params['api_key'];

        //判断用户名和密码是否匹配
        $result_check = $this->checkPassword($user_name,$password);
        if(!$result_check){
            return array(
                'success' => false,
                'code' => -1,
                'message' => "添加备份中心失败, 用户名或密码错误, 身份验证失败!",
                'data' => array(),
            );
        }
        //第一次要获取很多信息
        //1. 获取备份中心相关基本信息
        $basicInfo = $this->getBackupCenterAllInfo();
        $info = array(
            'success' => true,
            'code' => 0,
            'message' => "添加备份中心成功.",
            'data' => $basicInfo,
        );
        return $info;


    }


    /**
     * 只负责获取备份中心的所有数据,在第一次添加备份中心和自动更新备份中心调取接口时获取
     * @params data_center_uuid
     */
    public function getBackupCenterAllInfo(){
        //获取备份中心指纹信息
        $systemLogic = new Index();
        $thumbprint = $systemLogic->getThumbprintIDNoPermission();
        $info = array(
            'thumbprint' => $thumbprint,
        );
        return $info;
    }






    /**
     * 验证用户名和密码的正确性
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/10/9
     * @return boolean 其中false表示失败
     */
    public function checkPassword($user_name,$password){
        //先查询账号和密码是否为空,若为空则直接返回错误
        if(empty($user_name) || empty($password)){
            return false;
        }
        //根据用户名查询数据库相关记录
        $sql = "select id,user_type from bd_user where user_name = ? and lock_flag = 1 and password = ?";
        $result = $this->dbSelect($sql,array($user_name,$password));
        if(empty($result)){
            //如果没查询出来则返回错误
            return false;
        }
        return true;
    }


    /**
     * 得到所有节点信息
     */
    public function  getAllNodeInfo(){
        $sql = "select node_type, detail, ip, node_uuid, host_name, node_nickname,
                unix_timestamp(register_time) register_time, auto_upgrade_flag from bd_node ";
        $data = $this->dbSelect($sql);
        $nodeInfo = array();
        $NODE = new Node();
        foreach ($data as $d) {
            $nodeDeployStatus = $NODE->getNodeDeployStatus($d['node_uuid']);
            $nodeAllStatus = $NODE->getNodeAllStatus($d['node_uuid']);
            $info = json_decode($d['detail'], true);
            if (!empty($info)) {
                $version = $info['version']['major_version'] . '.' .
                    $info['version']['minor_version'] . '.' . $info['version']['build_number'];
            } else {
                $version = xphp_get_config('app', 'NULLSPACE');
            }
            $nodeInfo[] = array(
                'ip' => $d['ip'],
                'version' => $version,
                'node_deploy' => $nodeDeployStatus,
                'node_status' => $nodeAllStatus['flag'],
                'node_uuid' => $d['node_uuid'],
            );
        }
        return $nodeInfo;

    }

    /**
     * 得到备份中心同步数据
     */
    public function  getBackupCenterInfo(){
        //获取备份中心版本号
        $version = xphp_get_config('app','SYSTEM_INFO')['version'];
        //获取备份中心指纹信息
        $systemLogic = new Index();
        $authHandler = new Auth();
        //获取授权信息
        $auth_info = $authHandler->getSystemAuthInfo();
        //获取节点信息
        $node_info = $this->getAllNodeInfo();
        //获取备份中心授权状态
        $authorized_flag = $systemLogic->getSystemAuthorizationStatus();
        $info = array(
            'version' => $version,
            'auth_info' => json_encode($auth_info),
            'node_info' => $node_info,
            'data_center_info' => $this->getDataCenterInfo(),
            'authorized_flag' => $authorized_flag
        );
        return $info;
    }





//---------------------------------------------------得到同步所有数据--------------------------------------------

    /**
     * 得到集中管理平台应该更新的所有数据
     */
    public function  getAllBasicInfo(){
        $backup_center_info = $this->getBackupCenterInfo();
        $current_job_params = array(
            'search'  =>"",
            'sort' => "create_time",
            'order' => "desc",
            'offset' => 0,
            'limit' => 50,
            'accurateFlag' => false,
            'job_uuid_list' => array(), //默认为空, 如果有该值只会返回该值的一些数据
        );
        // $current_job_info = $this->getCurrentJobInfo($current_job_params);
        // //获取本地文件/usr/share/nginx/vinchin的假数据
        // $file_path = '/usr/share/nginx/vinchin/text.json';
        // $content = file_get_contents($file_path);
        // $rows = json_decode($content,true);
        // $total = count($rows);
        // $current_job_info = [
        //     'rows' => $rows,
        //     'total' => $total,
        // ];


        $info = array(
            'success' => true,
            'code' => 0,
            'message' => "添加备份中心成功.",
            'data' => array(
                'backup_center_info' => $backup_center_info,
                // 'current_job_info' => $current_job_info,
            ),
        );
    return $info;
    }



    //获取当前任务信息
    public function getCurrentJobInfo($current_job_params)
    { 
        $jobHandler = new JobInfo;
        $job_list = $jobHandler->getJobList($current_job_params);
        return $job_list;
    }
//-----------------------------------------------------------------------------------------------------------

    /**
     * 获取备份系统基本信息-用于首页展示
     * @return array $info 备份系统基本信息
     */
    public function getDataCenterInfo()
    {
        //任务数量
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu where  bt.delete_flag = ? and bt.user_uuid = bu.user_uuid and bu.user_uuid = ?";
        $sqlCountParams = array(xphp_get_config('app', 'FLAG')['UNSET'],xphp_get_user_info()['userUuid']);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $homepage = (new homePage());
        //备份数据
        $backupData = $homepage->get_card_basic_task_data_1();
        //客户端数量
        $sql = "select count(agent_uuid) as total from bd_agent where agent_type not in (3, 4, 5)";
        $agentNum = $this->dbSelect($sql, array());
        //nas设备数量
        $nasNum = $homepage->getNasInfo()['nas_num'];
        $info = array(
            'task_num' => $count[0]['total'],//任务数量
            'backup_data' => $backupData['value_size'] . $backupData['value_size_unit'],//备份数据
            'accumulate_time' => $backupData['value_days'],//累计运行时间
            'vm_num' => $homepage->get_card_vm_1([xphp_get_user_info()['userUuid']]),//虚拟机数量
            'agent_num' => $agentNum[0]['total'],//客户端数量
            'nas_num' => $nasNum,//nas设备数量
        );
        return $info;
    }
}
