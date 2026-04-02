<?php

namespace app\v1\db\v0\logic;

use app\v1\common\logic\Base;
use app\v1\db\v0\logic\DbRpc;

class DbCdp extends Base
{
    /**
     * 获取授权信息
     * @return array
     */
    public function getLicenseCenterAuthorInfo()
    {
        $hostip = $_SERVER['SERVER_ADDR'];
        $operate = '获取网络获取授权配置';
        $data = array();
        $result = (new DbRpc())->getLicenseCenterAuthorInfo($hostip, $data);
        $data = $this->checkRPCMsg($operate, $result);
        return $data;
    }

    /**
     * 统一检查RPC消息,如果失败直接返回并退出
     * @param string $operate 操作名称
     * @param array  $result  RPC返回结果
     * @return array
     */
    public function checkRPCMsg($operate, $result)
    {
        //单独处理部分错误,不提示的.
        //1.-93001 停止任务成功,但是报错-93001, 不处理
        //2.-19150 启动任务,任务已经在运行了,报错,不处理
        $excludeError = array(
            -93001, -19150
        );
        if (in_array($result['errorCode'], $excludeError)) {
            $result['result'] = true;
            return $result;
        }
        if (!$result['result']) {
            //如果失败
            //不是vinchin_enterprise,是false的话直接return空
            $systemInfoEnterprise = xphp_get_config('app', 'SYSTEM_INFO')['enterprise'];
            $enterprise = xphp_get_config('app', 'ENTERPRISE');
            $vinchinVersions = array(
                $enterprise['vinchin_standard'],
                $enterprise['vinchin_enterprise'],
                $enterprise['vinchin_advance_enterprise'],
            );
            if (!in_array($systemInfoEnterprise, $vinchinVersions)) {
                return '';
            }
            echo $this->muOpResult(false, $operate, $result['errorMsg'], 'warning', $result['errorCode']);
            exit();
        }

        $msgData = $result['data'];
        $msgCode = intval($msgData['msgcode']);
        //这里需要去解析错误消息TODO
        $msgTxt = $msgData['msgtxt'];
        if ($msgCode < 0) {
            //如果rpc返回 操作失败
            echo $this->muOpResult(false, $operate, $msgTxt, 'warning', $msgCode);
            exit();
        }
        return $msgData['data'];
    }

    /**
     * 获取环境码
     * @param unknown $params
     */
    public function getEnvStr(){
        $hostip = $_SERVER['SERVER_ADDR'];
        $operate = "获取备份系统环境码";
        $data = array();
        $result = (new DbRpc())->getEnvStr($hostip, $data);
        $data = $this->checkRPCMsg($operate, $result);
        //适配返回空字符串的情况
        if(empty($data)){
            return "";
        }
        return $data['envstr'];
    }

    /**
     * 上传license文件
     */
    public function upLicenseFile($licenseStr){
        $hostip = $_SERVER['SERVER_ADDR'];
        $rpc = new DbRpc();
        $data = array(
            'licenseStr' => $licenseStr
        );
        $result = $rpc->upLicenseFile($hostip, $data);
        if(!$result['result']){
            //授权失败,直接失败结果
            return $result;
        }
        //授权成功,立即设置成授权中心
        
        //配置本地授权许可列表性质
        $data = array(
            'tablemode' => 1
        );
        $result = $rpc->setLocAuthorTableMode($hostip, $data);
        
        //激活当前授权配置立即生效
        $data = array(
            'syscode' => $syscode,
        );
        $result = $rpc->actLicenseCfgValid($hostip, $data);
        
        $operate = "设置授权中心";
        $data = array(
            'licport' => '7999'
        );
        $result = $rpc->setLicenseCenter($hostip, $data);
        return $result;
    }
}
    
    
