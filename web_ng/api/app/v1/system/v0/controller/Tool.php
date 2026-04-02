<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * 系统配置 - 系统工具
 */
class Tool extends AuthBase
{
    /**
     * 获取系统服务列表
     * @return void
     */
    public function getServiceList()
    {
        $this->checkParams('getServices');
        $data = $this->logic()->getServiceList($this->param);
        $this->success('', $data);
    }

    /**
     * 系统服务管理
     * @return json
     */
    public function operateService()
    {

        $this->checkParams('opearate_service');
        $return = $this->logic()->operateService($this->param);
        if ($return['code'] !== 0) {
            return $this->error($return['msg']);
        }
        return $this->success($return['msg']);
    }

    /**
     * 测试网络连接
     * @return json
     */
    public function testConnectTool()
    {

        $this->checkParams('test_connect');
        $return = $this->logic()->testConnectTool($this->param);
        if ($return['code'] !== 0) {
            return $this->error($return['msg']);
        }
        return $this->success($return['msg']);
    }

    /**
     * 上传文件到指定目录
     * @return json
     */
    public function uploadToSystem()
    {

        $return = $this->logic()->uploadToSystem($this->param);
        if ($return['code'] !== 0) {
            return $this->error($return['msg']);
        }
        return $this->success($return['msg']);
    }

    /**
     * 删除临时文件
     * @return json
     */
    public function delTempFile()
    {

        $this->logic()->delTempFile($this->param);
        return $this->success();
    }
}
