<?php

namespace app\v1\industry\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          行业合规报告相关控制器
 * @author       wanggongxi@vinchin.com
 * @date         2024/8/6 10:31
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Report extends AuthBase
{
    /**
     * 报告详情
     * @return json
     */
    public function viewReport()
    {
        // 需要兼容下如果是任务那边过来的配置
        if (!empty($this->param['job_uuid']) && !empty($this->param['agent_uuid'])) {
            $return = $this->logic()->viewReport($this->param);
            if ($return['code'] == 0) {
                return $this->success('', $return['msg']);
            }
        } elseif (!empty($this->param['report_uuid'])) {
            // 参数验证
            $this->checkParams('view');
            $return = $this->logic()->viewReport($this->param['report_uuid']);
            if ($return['code'] == 0) {
                return $this->success('', $return['msg']);
            }
        } else {
            // 参数验证
            // $this->checkParams('list');
            $return = $this->logic()->getReportList($this->param);
            return $this->success('', $return);
        }

        return $this->error($return['msg']);
    }

    /**
     * 审批报告
     * @return json
     */
    public function approveReport()
    {
        // 参数验证
        $this->checkParams('view');

        $return = $this->logic()->approveReport($this->param);
        if ($return['code'] == 0) {
            return $this->success(xphp_get_lang('UI_GMP_REPORT_APPROVE_RESULT_SUCCESS'), $return);
        }

        return $this->error($return['msg']);
    }

    /**
     * 更改审批流审批
     * @return json
     */
    public function changeApproval()
    {
        // 参数验证
        $this->checkParams('view');

        $return = $this->logic()->changeApproval($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }

        return $this->error($return['msg']);
    }

    /**
     * 更改审批人
     * @return json
     */
    public function changeApproveUser()
    {
        // 参数验证
        $this->checkParams('view');

        $return = $this->logic()->changeApproveUser($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }

        return $this->error($return['msg']);
    }

    /**
     * 撤销审批流
     * @return json
     */
    public function cancelApproval()
    {
        // 参数验证
        $this->checkParams('view');

        $return = $this->logic()->cancelApproval($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }

        return $this->error($return['msg']);
    }

    /**
     * 删除审批报告
     * @return json
     */
    public function delReport()
    {
        // 参数验证
        $this->checkParams('uuids');

        $return = $this->logic()->delReport($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }

        return $this->error($return['msg']);
    }

    /**
     * 创建任务生成报告相关信息
     * @return json
     */
    public function jobReport()
    {
        // 参数验证
        // $this->checkParams('make');
        $return = $this->logic()->jobReport($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 生成报告
     * @return json
     */
    public function makeReport()
    {
        // 参数验证
        // $this->checkParams('make');
        $return = $this->logic()->makeReport($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 保存报告
     * @return json
     */
    public function saveReport()
    {
        // 参数验证
        $this->checkParams('save');
        $return = $this->logic()->saveReport($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 一键截屏
     * @return json
     */
    public function allScrren()
    {
        // 参数验证
        $this->checkParams('view');
        $return = $this->logic()->allScrren($this->param['report_uuid']);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 生产截屏
     * @return json
     */
    public function produceScrren()
    {
        // 参数验证
        $this->checkParams('view');
        $return = $this->logic()->produceScrren($this->param['report_uuid']);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 验证截屏
     * @return json
     */
    public function verifyScrren()
    {
        // 参数验证
        $this->checkParams('view');
        $return = $this->logic()->verifyScrren($this->param['report_uuid']);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 病毒查杀列表
     * @return json
     */
    public function virtusLists()
    {

        // 参数验证
        $this->checkParams('lists');
        $return = $this->logic()->virtusLists($this->param);
        return $this->success('', $return);
    }

    /**
     * 文件比对列表
     * @return json
     */
    public function documentLists()
    {
        // 参数验证
        $this->checkParams('lists');
        $return = $this->logic()->documentLists($this->param);
        return $this->success('', $return);
    }

    /**
     * 获取比对文件下载url
     * @return json
     */
    public function downLoadFileUrl()
    {
        // 参数验证
        $this->checkParams('view');
        $return = $this->logic()->downLoadFileUrl($this->param['report_uuid']);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 获取下载url
     * @return json
     */
    public function downLoadUrl()
    {
        // 参数验证
        $this->checkParams('view');
        $return = $this->logic()->downLoadUrl($this->param['report_uuid']);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 下载url
     * @return json
     */
    public function downLoad()
    {
        // 参数验证
        $return = $this->logic()->downLoad($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 发送报告到邮箱 此时接收人只有当前登录用户的邮箱
     * @return json
     */
    public function sendEmail()
    {
        // 参数验证
        $this->checkParams('view');
        $return = $this->logic()->sendEmail($this->param['report_uuid']);
        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 分享/抄送
     * @return json
     */
    public function shareReport()
    {
        $return = $this->logic()->shareReport($this->param);
        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 评论报告
     * @return json
     */
    public function remarkReport()
    {
        $return = $this->logic()->remarkReport($this->param);
        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 获取评论列表
     * @return json
     */
    public function getReportCommentList()
    {
        $return = $this->logic()->getReportCommentList($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }
        return $this->error($return['msg']);
    }

    /**
     * 获取用户独立密码
     * @return json
     */
    public function getCustomPwd()
    {
        $return = $this->logic()->getCustomPwd($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }
        return $this->error($return['msg']);
    }

    /**
     * 报告审批时获取所有的用户或者用户组
     * @return json
     */
    public function getShareUsers()
    {
        $return = $this->logic()->getShareUsers($this->param);
        return $this->success('', $return);
    }
}
