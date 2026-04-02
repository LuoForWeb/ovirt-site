<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          系统管理类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:10
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends AuthBase
{
    /**
     * 获取系统授权类型
     * @return false|string
     */
    public function getSystemLicenseType()
    {
        $info = $this->logic()->getSystemLicenseType();

        return json_encode($info);
    }

    /**
     * 得到系统授权信息
     * (!!!!!注意)Vmhandler checkTaskLegal有调用
     * @return string
     */
    public function getSystemLisenceInfo()
    {
        $systemStatus = $this->logic()->getSystemAuthorizationStatus();
        $systemStatusDes = $this->logic()->getSystemAuthorizationDes($systemStatus);
        //调用新的获取授权信息方法
        return $this->logic()->getAuthorizedInfoV2(intval($systemStatus), $systemStatusDes);
    }

    /**
     * 下载指纹信息
     * @return string
     */
    public function getThumbprint()
    {
        //调用新的获取授权信息方法
        return $this->logic()->getThumbprint();
    }

    /**
     * 授权文件
     * @return string
     */
    public function uploadLisence()
    {
        $data = $_POST;
        $info = $this->logic()->uploadLisence($data);
        //调用新的获取授权信息方法
        if ($info['success']) {
            return $this->success($info['message'], $info['data']);
        } else {
            return $this->error($info['message'], $info['data']);
        }
    }

    /**
     * 获取license的extension解密后的数据
     * (!!!!!注意)后台有调用
     * @return string
     */
    public function getDecryptLisence()
    {
        $return = $this->logic()->getDecryptLisence($this->param);
        if ($return['code'] != 0) {
            $this->error($return['msg']);
        }
        $this->success($return['msg'], $return['data']);
    }

    /**
     * 生成文件下载的路径
     * @return void
     */
    public function generateDownloadFilepath()
    {
        $this->checkParams('generate_download_filepath');
        $ret = $this->logic()->generateDownloadFilepath($this->param);
        $this->outputHandle($ret);
    }

    /**
     * 下载文件
     * @return void
     */
    public function downloadFile()
    {
        $this->checkParams('download_file');
        $this->logic()->downloadFile($this->param['p']);
    }

    /**
     * 获取虚拟机授权的基本信息
     * @return void
     */
    public function getVMLicenseInfo()
    {
        $data = $this->logic()->getVMLicenseInfo();
        $this->success('', $data);
    }

    /**
     * 获取系统的基本信息
     * @return void
     */
    public function getConfig()
    {
        $data = $this->logic()->getConfig();
        $this->success('', $data);
    }

    /**
     * 获取恢复权限配置信息
     * @return void
     */
    public function getRecoverPermission()
    {
        $data = $this->logic()->getRecoverPermission();
        $this->success('', $data);
    }

    /**
     * 批量检查IP是否可达
     * @return void
     */
    public function batchCheckIpReachable()
    {
        $this->checkParams('batch_check_ip_reachable');
        $ret = $this->logic()->batchCheckIpReachable($this->param);
        $this->outputHandle($ret);
    }

    /**
     * 发送消息(后台使用)
     * @return void
     */
    public function sendMessages()
    {
        $this->checkParams('send_messages');
        $ret = $this->logic()->sendMessages($this->param);
        $this->outputHandle($ret);
    }
}
