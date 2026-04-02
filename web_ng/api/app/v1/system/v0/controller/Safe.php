<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          系统安全配置 controller
 * @author       wanggongxi@vinchin.com
 * @date         2023/12/19 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Safe extends AuthBase
{
    /**
    * 获取数据安全配置
     * @return json
     */
    public function getDatas()
    {
        $return = $this->logic()->getDatas();
        $this->success('', $return);
    }

    /**
     * 保存数据安全配置
     * @return json
     */
    public function setDatas()
    {

        $return = $this->logic()->setDatas($this->param);
        return $return ? $this->success(xphp_get_lang('WEB_PLATFORM_SUBMIT_SUCCESS')) : $this->error();
    }

    /**
     * 获取账户安全配置
     * @return json
     */
    public function getAccounts()
    {
        $return = $this->logic()->getAccounts();
        $this->success('', $return);
    }

    /**
     * 保存账户安全配置
     * @return json
     */
    public function setAccounts()
    {
        $this->checkParams('set_accounts');
        $return = $this->logic()->setAccounts($this->param);
        if ($return['code'] !== 0) {
            return $this->error($return['msg']);
        }
        return $this->success($return['msg']);
    }

    /**
     * 获取存储安全配置
     * @return json
     */
    public function getStorages()
    {
        $return = $this->logic()->getStorages();
        $this->success('', $return);
    }

    /**
     * 保存存储安全配置
     * @return json
     */
    public function setStorages()
    {
        $this->checkParams('set_storages');
        $return = $this->logic()->setStorages($this->param);
        if ($return['code'] !== 0) {
            return $this->error($return['msg']);
        }
        return $this->success($return['msg']);
    }

    /**
     * 获取系统安全配置
     * @return json
     */
    public function getOsSafe()
    {
        $return = $this->logic()->getOsSafe();
        $this->success('', $return);
    }

    /**
     * 保存系统安全配置
     * @return json
     */
    public function setOsSafe()
    {
        $this->checkParams('set_os');
        $return = $this->logic()->setOsSafe($this->param);
        if ($return['code'] !== 0) {
            return $this->error($return['msg']);
        }
        return $this->success($return['msg']);
    }

    /**
     * 关机/重启
     * @return json
     */
    public function powerSubmit()
    {
        $this->checkParams('power');
        $return = $this->logic()->powerSubmit($this->param);
        if ($return['code'] !== 0) {
            return $this->error($return['msg']);
        }
        return $this->success($return['msg'], $return['data'] ?? []);
    }
}
