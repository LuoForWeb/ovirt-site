<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;
use app\v1\opcode\NodeOpcode;

/**
 * note          存储管理处理类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/6 11:32
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Storage extends AuthBase
{
    /**
     * 添加生产存储
     * @return json
     */
    public function addLunStorage()
    {
        $op = xphp_get_lang('UI_LUN_STORAGE_SEND_ADD_MSG');
        // 逻辑层转发
        $return = $this->logic()->addLunStorage($this->param);

        return $this->muOpResult($return['result'], $op, '', '', $return['errorCode']);
    }

    /**
     * 检查存储启动器
     * @return json
     */
    public function checkInitiator()
    {
        $op = xphp_get_lang('UI_LUN_STORAGE_SEND_CHECK_INITATOR_MSG');
        // 逻辑层转发
        $return = $this->logic()->checkInitiator($this->param);

        if (empty($return)) {
            return $this->success('', ['title' => $op]);
        }
        return $this->error(implode(',', $return), [$return]);
    }

    /**
     * 修改生产存储
     * @return json
     */
    public function editLunStorage()
    {
        // 逻辑层转发
        $return = $this->logic()->editLunStorage($this->param);
        return $this->success('', $return);
    }

    /**
     * 获取生产存储列表
     * @return json
     */
    public function getLunStorageList()
    {
        // 逻辑层转发
        $return = $this->logic()->getLunStorageList($this->param);
        return $this->success('', $return);
    }

    /**
     * 获取LUN存储列表
     * @return json
     */
    public function getLunList()
    {
        // 批量
        // $this->checkParams('startBatch');

        // 逻辑层转发
        $return = $this->logic()->getLunList($this->param);
        return $this->success('', $return);

        // return $this->output($return);
    }

    /**
     * 获取LUN快照列表
     * @return json
     */
    public function getLunSnap()
    {
        // 批量
        // $this->checkParams('startBatch');

        // 逻辑层转发
        $return = $this->logic()->getLunSnap($this->param);
        return $this->success('', $return);

        // return $this->output($return);
    }

    /**
     * 同步生产存储
     * @return json
     */
    public function syncLunStorage()
    {
        // 批量
        // $this->checkParams('startBatch');

        // 逻辑层转发
        $return = $this->logic()->syncLunStorage($this->param);
        return $this->success('', $return);

        // return $this->output($return);
    }

    /**
     * 得到当前所有节点中存储最大的那个节点存储返回
     * @return json
     */
    public function getMaxStorage()
    {
        $info = $this->logic()->getMaxStorage();
        $this->success('', $info);
    }

    /**
     * 获取存储设备列表 / 详情
     * @return json
     */
    public function getStorageInfo()
    {

        // 根据参数判断是获取列表还是详情
        if (!empty($this->param['storages_uuid'])) { // 详情
            // 参数验证
            $this->checkParams('storages_detail');
            $records = $this->logic()->getStorageDetail($this->param['storages_uuid']);

            if (!$records) {
                $this->error();
            }

            $this->success('', $records);
        }
        // 列表
        // 参数验证
        $this->checkParams('storages_list');
        $records = $this->logic()->getStorageList($this->param);

        $this->success('', $records);
    }

    /**
     * 得到存储设备自动导入时间点时间
     * @return json
     */
    public function getImportRefreshTime()
    {

        $records = $this->logic()->getImportRefreshTime($this->param);

        $this->success('', $records);
    }

    /**
     * 设置存储设备自动导入时间点时间
     * @return json
     */
    public function setImportRefreshTime()
    {

        // 参数验证
        $this->checkParams('auto_time');
        $records = $this->logic()->setImportRefreshTime($this->param);

        if (!$records) {
            $this->error();
        }
        $this->success();
    }

    /**
     * 添加存储设备1,2,3,4,5,6,7
     * @return json
     */
    public function addNewStorage()
    {

        // 参数验证
        $this->checkParams('add_storages' . ($this->param['storage_type'] > 4 ? $this->param['storage_type'] : ''));
        $records = $this->logic()->addNewStorage($this->param);
        if ($records['code'] != 0) {
            $this->error($records['msg']);
        }
        return $this->success($records['msg']);
    }

    /**
     * 添加存储设备之nas系列11
     * @return json
     */
    public function addNasStorage()
    {

        // 参数验证
        $this->checkParams('add_storages' . $this->param['storage_type']);
        $records = $this->logic()->addNasStorage($this->param);
        if ($records['code'] != 0) {
            $this->error($records['msg']);
        }
        return $this->success($records['msg']);
    }

    /**
     * 添加存储设备之华为cbr12
     * @return json
     */
    public function addHuaweiCBRStorage()
    {

        // 参数验证
        $this->checkParams('add_storages' . $this->param['storage_type']);
        $records = $this->logic()->addHuaweiCBRStorage($this->param);
        if ($records['code'] != 0) {
            $this->error($records['msg']);
        }
        return $this->success($records['msg']);
    }

    /**
     * 添加存储设备之异地备份8
     * @return json
     */
    public function addCopyStorage()
    {

        // 参数验证
        $this->checkParams('add_storages' . $this->param['storage_type']);
        $records = $this->logic()->addCopyStorage($this->param);
        if ($records['code'] != 0) {
            $this->error($records['msg']);
        }
        return $this->success($records['msg']);
    }

    /**
     * 添加存储设备之云存储9
     * @return json
     */
    public function addCloudStorage()
    {

        // 参数验证
        $this->checkParams('add_storages' . $this->param['storage_type']);
        $records = $this->logic()->addCloudStorage($this->param);
        if ($records['code'] != 0) {
            $this->error($records['msg']);
        }
        return $this->success($records['msg']);
    }

    /**
     * 添加存储设备之并行文件13
     * @return json
     */
    public function addParallelStorage()
    {

        // 参数验证
        $this->checkParams('add_storages' . $this->param['storage_type']);
        $records = $this->logic()->addParallelStorage($this->param);
        if ($records['code'] != 0) {
            $this->error($records['msg']);
        }
        return $this->success($records['msg']);
    }

    /**
     * 修改存储设备
     * @return json
     */
    public function editStorageInfo()
    {

        // 参数验证
        $this->checkParams('edit_storages');
        $records = $this->logic()->editStorageInfo($this->param);
        if (!$records[0]) {
            $this->error($records[1]);
        }
        $this->success($records[1]);
    }

    /**
     * 删除存储设备 检测
     * @return json
     */
    public function checkStorageInfo()
    {

        // 批量
        $this->checkParams('batchdel');

        // logic处理逻辑
        $result = $this->logic()->checkStorageInfo($this->param['uuids']);

        $msg = xphp_get_lang('WEB_NODE_OP_DELETE');
        return $result ? $this->success($msg) : $this->error($msg);
    }

    /**
     * 删除存储设备 确定
     * @return json
     */
    public function delStorageInfo()
    {

        // 批量
        $this->checkParams('batchdel');

        // logic处理逻辑
        $result = $this->logic()->delStorageInfo($this->param['uuids']);

        $msg = xphp_get_lang('WEB_NODE_OP_DELETE');
        return $result ? $this->success($msg) : $this->error($msg);
    }

    /**
     * 导入数据管理列表 / 获取备份数据信息详情
     * @return json
     */
    public function getImportData()
    {
        // 根据参数判断是获取列表还是详情
        if (!empty($this->param['data_uuid'])) { // 详情
            // 参数验证
            $this->checkParams('data_detail', '', ['storages_uuid' => $this->param['data_uuid']]);
            $records = $this->logic()->getImportDataDetail($this->param['data_uuid']);

            if (!$records) {
                $this->error();
            }

            $this->success('', $records);
        }
        // 列表
        // 参数验证
        $this->checkParams('data_list');
        $records = $this->logic()->getImportDataList($this->param);

        $this->success('', $records);
    }

    /**
     * 分配导入数据到其他用户
     * @return json
     */
    public function distributeDataToUser()
    {
        // 参数验证
        $this->checkParams('distribute');
        $records = $this->logic()->distributeDataToUser($this->param);

        if (!$records) {
            $this->error();
        }

        $this->success($records);
    }

    /**
     * 获取添加存储的表格数据
     * @return json
     */
    public function getAddStorageTable()
    {
        // 参数验证
        $this->checkParams('storages_table');
        $records = $this->logic()->getAddStorageTable($this->param);


        $this->success('', $records);
    }

    /**
     * 获取存储WWN信息
     * @return json
     */
    public function getWwnNum()
    {
        // 参数验证
        $this->checkParams('storages_wwn');
        $records = $this->logic()->getWwnNum($this->param);

        $this->success('', $records);
    }

    /**
     * 获取iscsi名称
     * @return json
     */
    public function getIscsiName()
    {
        // 参数验证
        $this->checkParams('storages_iscsi');
        $records = $this->logic()->getIscsiName($this->param);

        if (!$records) {
            $this->error();
        }

        $this->success('success', $records);
    }

    /**
     * 获取IQN对应的LUN信息
     * @return json
     */
    public function getIscsiLunTable()
    {
        // 参数验证
        $this->checkParams('storages_lun');
        $records = $this->logic()->getIscsiLunTable($this->param);

        $this->success('', $records);
    }

    /**
     * 进行chap认证信息填写后再次扫描-第一次认证
     * @return json
     */
    public function getIscsiChapList1()
    {
        // 参数验证
        $this->checkParams('storages_chap_auth');
        $records = $this->logic()->getIscsiChapList1($this->param);

        if (!$records) {
            $this->error();
        }

        $this->success('success', $records);
    }

    /**
     * 进行chap门户认证信息填写后再次扫描-第二次认证
     * @return json
     */
    public function getIscsiChapList()
    {
        // 参数验证
        $this->checkParams('storages_chap_auth');
        $records = $this->logic()->getIscsiChapList($this->param);

        if (!$records) {
            $this->error();
        }

        $this->success('success', $records);
    }

    /**
     * 获取云存储的云服务商列表
     * @return json
     */
    public function getVendor()
    {

        $records = $this->logic()->getVendors($this->param);

        $this->success('', $records);
    }

    /**
     * 获取云存储bucket folder列表
     * @return json
     */
    public function getBucketFolder()
    {
        // 参数验证
        $this->checkParams('storages_bucket');
        $records = $this->logic()->getBucketFolder($this->param);

        $this->success('', $records);
    }

    /**
     * 获取CBR区域
     * @return json
     */
    public function getCBRArea()
    {
        // 参数验证
        $this->checkParams('storages_cbr');
        $records = $this->logic()->getCBRArea($this->param);

        $this->success('', $records);
    }

    /**
     * 根据存储类型获取新的存储名称
     * @return json
     */
    public function getNewStorageName()
    {
        // 参数验证
        $this->checkParams('storages_name');
        $records = $this->logic()->getNewStorageName($this->param);

        $this->success('', $records);
    }

    /**
     * 获取云存储region
     * @return json
     */
    public function getCloudRegion()
    {
        // 参数验证
        $this->checkParams('storages_region');
        $records = $this->logic()->getCloudRegion($this->param);


        $this->success('', $records);
    }

    /**
     * @description: 获取异地备份系统存储列表
     * @return json
     */
    public function getRemoteStorages()
    {
        $info = $this->logic()->getRemoteStorages($this->param);
        return $this->success('', $info);
    }

    /**
     * 添加lan-free存储
     * @return json
     */
    public function addLanfreeStorage()
    {
        // 参数验证
        $this->checkParams('storages_lanfree' . $this->param['storagetype']);
        $chk = $this->logic()->addLanfreeStorage($this->param);
        if (empty($chk[0])) {
            $this->error($chk[1]);
        }
        $this->success();
    }

    /**
     * 获取存储列表
     * @return void
     */
    public function getStorageRecoveryList()
    {
        $info = $this->logic()->getStorageRecoveryList($this->param);
        return $this->success('', $info);
    }

    /**
     * 手动同步
     * @return json
     */
    public function syncStorageInfo()
    {
        $info = $this->logic()->syncStorageInfo($this->param);
        if ($info['code'] == 0) {
            return $this->success($info['msg']);
        }
        return $this->error($info['msg']);
    }
}
