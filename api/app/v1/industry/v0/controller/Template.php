<?php

namespace app\v1\industry\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          行业合规模板控制器
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/31 18:26
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Template extends AuthBase
{
    /**
    * 新建/编辑模板
     * @return json
     */
    public function updateTemplate()
    {
        // 参数验证
        $this->checkParams('update');
        $return = $this->logic()->updateTemplate($this->param);
        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 获取模板列表
     * @return json
     */
    public function getTemplateList()
    {
        if (!empty($this->param['templates_uuid'])) {
            // 模板详情
            // 参数验证
            $this->checkParams('view');
            $return = $this->logic()->viewTemplate($this->param['templates_uuid'], $this->param['job_uuid'] ?? '');
            if ($return['code'] == 0) {
                return $this->success('', $return['msg']);
            }
            return $this->error($return['msg']);
        }
        // 逻辑层转发
        $result = $this->logic()->getTemplateList($this->param);

        if ($result) {
            return $this->success('', $result, 200);
        }
        return $this->error('', $result, $result['errorCode']);
    }

    /**
     * 删除模板列表
     * @return json
     */
    public function delTemplate()
    {
        // 逻辑层转发
        $result = $this->logic()->delTemplate($this->param);

        if ($result) {
            return $this->success('', $result);
        }
        return $this->error(xphp_get_lang('UI_PLATFORM_INDUSTRY_TEMPLATE_IS_USED'));
    }

    /**
     * 启用模板列表
     * @return json
     */
    public function unlockTemplate()
    {
        // 逻辑层转发
        $result = $this->logic()->unlockTemplate($this->param);

        if ($result) {
            return $this->success('', $result, 200);
        }
        return $this->error('', $result, $result['errorCode']);
    }

    /**
     * 禁用模板列表
     * @return json
     */
    public function lockTemplate()
    {
        // 逻辑层转发
        $result = $this->logic()->lockTemplate($this->param);

        if ($result) {
            return $this->success('', $result, 200);
        }
        return $this->error('', $result, $result['errorCode']);
    }
}
