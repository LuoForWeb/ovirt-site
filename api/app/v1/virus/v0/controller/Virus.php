<?php

namespace app\v1\virus\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          镜像管理
 * @author       wangru
 * @date         2024/07/15 11:48
 * @version      1.0.0
 */
class Virus extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取病毒库列表
     * @return void
     */
    public function getVirusList()
    {
        $info = $this->logic()->getVirusList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
    /**
     * 获取病毒库厂商
     * @return void
     */
    public function getVirus()
    {
        $info = $this->logic()->getVirus($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 更新病毒
     * @return json
     */
    public function updateVirus()
    {
        $result = $this->logic()->updateVirus($this->param);
        $this->outputHandle($result);
    }


    /**
     * 上傳病毒库
     * @return json
     */
    public function getVirusFile()
    {
        // 此处需要使用$_FILES和$_POST，因为$this->param获取不到os_type
        $result = $this->logic()->getVirusFile($_FILES['files'] ?? null);
        $this->outputHandle($result);
    }

    /**
     * 病毒库操作
     * @return json
     */
    public function applyVirusList()
    {
        $result = $this->logic()->applyVirusList($this->param);
        $this->outputHandle($result);
    }

    /**
     * 上传病毒库授权文件
     * @return void
     */
    public function uploadAuthorizationFile()
    {
        $this->checkParams('upload_authorization_file');
        $result = $this->logic()->uploadAuthorizationFile($this->param);
        $this->outputHandle($result);
    }
}
