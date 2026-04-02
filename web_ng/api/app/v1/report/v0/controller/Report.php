<?php

namespace app\v1\report\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          报表
 * @author       liushuai@vinchin.com
 * @date         2023/10/24 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Report extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取任务报表列
     * @return void
     */
    public function getTemplateList()
    {
        $info = $this->logic()->getTemplateList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
    /**
     * 获取任务报表详情
     * @return void
     */
    public function getTemplateDetail()
    {
        $info = $this->logic()->getTemplateDetail($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
    /**
     * 获取客户端报表模板的列表
     * @return json
     */
    public function getAgenteDetails()
    {
        $info = $this->logic()->getAgenteDetails($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取虚拟机报表模板的列表
     * @return json
     */
    public function getVmReportList()
    {
        $info = $this->logic()->getVmReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取公有云报表模板的列表
     * @return json
     */
    public function getPublicReportList()
    {
        $info = $this->logic()->getPublicReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取私有云报表模板的列表
     * @return json
     */
    public function getPrivateReportList()
    {
        $info = $this->logic()->getPrivateReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取虚拟机报表模板的列表
     * @return json
     */
    public function getStorageReportList()
    {
        $info = $this->logic()->getStorageReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取任务报表模板的列表
     * @return json
     */
    public function getTaskReportList()
    {
        $info = $this->logic()->getTaskReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取实时容灾报表模板的列表
     * @return json
     */
    public function getVolReportList()
    {
        $info = $this->logic()->getVolReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取nas报表模板的列表
     * @return json
     */
    public function getNasReportList()
    {
        $info = $this->logic()->getNasReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取应用保护报表模板的列表
     * @return json
     */
    public function getAppReportList()
    {
        $info = $this->logic()->getAppReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
    /**
     * 获取对象存储报表模板的列表
     * @return json
     */
    public function getObReporList()
    {
        $info = $this->logic()->getObReporList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
    /**
     * 获取hadoop报表模板的列表
     * @return json
     */
    public function getHadoopReportList()
    {
        $info = $this->logic()->getHadoopReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 
     * 获取文件复制数据明细
     * @return void
     */
    public function getFilecopyReportList()
    {
        $info = $this->logic()->getFilecopyReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取K8S数据明细
     * @return void
     */
    public function getK8sReportList()
    {
        $info = $this->logic()->getK8sReportList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 删除资源
     * @return json
     */
    public function deleteReportStrategy()
    {
        //        $this->checkParams('recourse_delete');
        $result = $this->logic()->deleteReportStrategy($this->param);
        return $result;
    }

    /**
     * 添加模板
     * @return json
     */
    public function addOrModifyTemplate()
    {
        $result = $this->logic()->addOrModifyTemplate($this->param);
        return $result;
    }

    /**
     * 删除模板
     * @return json
     */
    public function deleteTemplate()
    {
        $result = $this->logic()->deleteTemplate($this->param);
        return $result;
    }

    /**
     * 获取模板概览
     * @return json
     */
    public function getReportTemplateOverview()
    {
        $info = $this->logic()->getReportTemplateOverview($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取任务概览
     * @return json
     */
    public function getAgentOverview()
    {
        $info = $this->logic()->getAgentOverview();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取虚拟机概览
     * @return json
     */
    public function getVmOverview()
    {
        $info = $this->logic()->getVmOverview();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取公有云
     * @return json
     */
    public function getPublicCloud()
    {
        $info = $this->logic()->getPublicCloud();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取nas概览
     * @return json
     */
    public function getNasOverview()
    {
        $info = $this->logic()->getNasOverview();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取实时容灾概览
     * @return json
     */
    public function getCdpOverview()
    {
        $info = $this->logic()->getCdpOverview();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取实时容灾概览
     * @return json
     */
    public function getAppOverview()
    {
        $info = $this->logic()->getAppOverview();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取实时容灾概览
     * @return json
     */
    public function getNodeInfo()
    {
        $info = $this->logic()->getNodeInfo();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取实时容灾概览
     * @return json
     */
    public function getStorageOverview()
    {
        $info = $this->logic()->getStorageOverview();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取概览页任务运行趋势
     * @return json
     */
    public function getTaskOverviewTendency()
    {

        $info = $this->logic()->getTaskOverviewTendency($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取任务运行趋势
     * @return json
     */
    public function getStragyTendency()
    {

        $info = $this->logic()->getStragyTendency($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取概览页任务运行趋势
     * @return json
     */
    public function getAlarmNumber()
    {

        $info = $this->logic()->getAlarmNumber($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    //获取系统-邮件通知开关是否开启
    public function getEmail()
    {

        $info = $this->logic()->getEmail($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
    //获取个人信息邮箱信息
    public function getAddressInfo()
    {

        $info = $this->logic()->getAddressInfo($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
    public function getReportNoticeInfo()
    {
        $info = $this->logic()->getReportNoticeInfo();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
    /**
     * 下载url
     * @return json
     */
    public function reportDownLoadUrl()
    {
        // 参数验证
        $return = $this->logic()->reportDownLoadUrl($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }
    /**
     * 下载url
     * @return json
     */
    public function reportDownLoad()
    {
        $return = $this->logic()->reportDownLoad($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 获取运行趋势数据（供第三方接口调用 - DBS项目获取虚拟机近十四天运行趋势数据）
     * @return void
     */
    public function getReportVmBackupdata()
    {
        $return = $this->logic()->getReportVmBackupdata($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 获取虚拟机报表存储用途
     */
    public function getReportVmStorageUsage()
    {
        $return = $this->logic()->getReportVmStorageUsage($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 导出全部客户端报表数据
     * @return void
     */
    public function exportAllClientReportData()
    {
        $record = $this->logic()->exportAllClientReportData($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }
}
