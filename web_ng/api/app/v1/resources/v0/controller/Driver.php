<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;

class Driver extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取驱动信息 & 获取驱动详情
     * @return void
     */
    public function getDrivers()
    {
        if (!isset($this->param['drivers_uuid'])) {
            // 获取驱动列表
            $this->checkParams('get_driver_list');
            $result = $this->logic()->getDriverList($this->param);
        } else {
            // 获取驱动详情
            $this->checkParams('get_driver_detail');
            $result = $this->logic()->getDriverDetail($this->param['drivers_uuid']);
        }

        $this->outputHandle($result);
    }

    /**
     * 批量删除驱动
     * @return void
     */
    public function deleteDrivers()
    {
        $this->checkParams('delete_drivers');
        $result = $this->logic()->deleteDrivers($this->param['driver_uuid_list']);
        $this->outputHandle($result);
    }

    /**
     * 上传驱动
     * @return void
     */
    public function uploadDriver()
    {
        // 此处需要使用$_FILES和$_POST，因为$this->param获取不到os_type
        $result = $this->logic()->uploadDriver($_FILES['files'] ?? null, intval($_POST['os_type']));
        $this->outputHandle($result);
    }

    /**
     * 添加驱动
     * @return void
     */
    public function addDriver()
    {
        $this->checkParams('add_driver');
        $result = $this->logic()->addDriver($this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取代理驱动安装列表
     * @return void
     */
    public function getAgentInstallDriverList()
    {
        $this->checkParams('get_agent_install_driver_list');
        $result = $this->logic()->getAgentInstallDriverList($this->param['agent_uuid_list']);
        $this->outputHandle($result);
    }

    /**
     * 安装代理驱动
     * @return void
     */
    public function installAgentDriver()
    {
        $this->checkParams('install_agent_driver');
        $result = $this->logic()->installAgentDriver($this->param['agent_uuid_list']);
        $this->outputHandle($result);
    }

    /**
     * 批量检查操作系统驱动
     * @return void
     */
    public function checkOsDrivers()
    {
        $this->checkParams('check_os_drivers');
        $result = $this->logic()->checkOsDrivers(
            $this->param['check_os_list'],
            $this->param['driver_usage'] ?? xphp_get_config('driver', 'DRIVER_CHECK_USAGE', 'resources')['RECOVERY']
        );
        $this->outputHandle($result);
    }
}
