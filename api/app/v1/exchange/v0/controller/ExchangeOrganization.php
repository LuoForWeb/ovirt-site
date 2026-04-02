<?php

namespace app\v1\exchange\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          office365（exchange） -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeOrganization extends AuthBase
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取组织信息
     * @return object 组织相关信息
     */
    public function getOrganizationInfo()
    {
        $data = $this->logic()->getOrganizationInfo($this->param);
        if (!empty($data)) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_ORGANIZATION_INFO_SUCCESS'), $data);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_GET_ORGANIZATION_INFO_SUCCESS'), $data);
        }
    }

    /**
     * 添加组织
     * exchange online和exchange server添加参数不同
     * @return object 组织相关信息
     */
    public function addOrganization()
    {
        //获取接收到的参数
        $param = $this->param;
        //添加方式：1：exchangeonline，2：exchangeserver
        if ($param['type'] == 1) {
            $param['online_info']['app_secret'] = base64_decode($param['online_info']['app_secret']);
            $param['online_info']['app_cert_info']['cert_password'] = base64_decode($param['online_info']['app_cert_info']['cert_password']);
            $param['online_info']['auto_create_azure_ad_app_info']['app_name'] = urldecode(base64_decode($param['online_info']['auto_create_azure_ad_app_info']['app_name']));
            $msg = $param['online_info'];
        } elseif ($param['type'] == 2) {
            $param['server_info']['password'] = base64_decode(xphp_decrypt_js($param['server_info']['password']));
            $msg = $param['server_info'];
        }
        $result = $this->logic()->addOrganization($msg, $param['type'], $param['add_op_id']);
        return $this->success(xphp_get_lang('WEB_M365_SERVER_ADD_ORGANIZATION_SUCCESS'), $result);
    }

    /**
     * 修改组织
     * @return object 组织相关信息
     */
    public function editOrganization()
    {
        //获取接收到的参数
        $param = $this->param;
        //如果只修改了别名
        if ($param['nicknameFlag']) {
            $result = $this->logic()->editOrganizationNickname($param['nickname'], $param['organization_uuid']);
            return $this->success('', $result);
        }
        //添加方式：1：exchangeonline，2：exchangeserver
        if ($param['type'] == 1) {
            $param['online_info']['app_secret'] = $param['online_info']['app_secret'] == ""  ? v1_pt_pass_decrypt(base64_decode($param['all_password']['app_secret'])) : base64_decode($param['online_info']['app_secret']);
            $param['online_info']['app_cert_info']['cert_password'] = $param['online_info']['app_cert_info']['cert_password'] == ""  ? v1_pt_pass_decrypt(base64_decode($param['all_password']['cert_password'])) : base64_decode($param['online_info']['app_cert_info']['cert_password']);
            $param['online_info']['auto_create_azure_ad_app_info']['app_name'] = urldecode(base64_decode($param['online_info']['auto_create_azure_ad_app_info']['app_name']));
            $param['online_info']['nickname'] = $param['nickname'];
            $msg = $param['online_info'];
        } elseif ($param['type'] == 2) {
            $param['server_info']['password'] = $param['server_info']['password'] == "" ? v1_pt_pass_decrypt(base64_decode($param['all_password']['password'])) : base64_decode(xphp_decrypt_js($param['server_info']['password']));
            $param['server_info']['nickname'] = $param['nickname'];
            $msg = $param['server_info'];
        }
        $msg['organization_uuid'] = $param['organization_uuid'];
        $result = $this->logic()->editOrganization($msg, $param['type']);
        return $this->success('', $result);
    }

    /**
     * 删除组织
     * @return object 组织相关信息
     */
    public function deleteOrganization()
    {
        $result = $this->logic()->deleteOrganization($this->param);
        if ($result['result']) {
            return $this->success(xphp_get_lang('WEB_M365_COMMON_OP_CODE_DELETE_ORGANIZATION'), $result);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_COMMON_OP_CODE_DELETE_ORGANIZATION'), $result);
        }
    }

    /**
     * 获取用于身份验证的代码
     * 判断登录是否成功
     * @return string 身份验证的代码
     */
    public function getVertifyCode()
    {
        $result = $this->logic()->getVertifyCode($this->param);
        if ($result['op_status'] != 3) {
            return $this->success('', $result);
        } else {
            $error = include '/usr/share/nginx/vinchin/web_ng/api/app/v1/config/error.php';
            $des = xphp_get_lang('WEB_OPHANDLER_ERROR_CODE') . ': #' . $result['error_code'] . ',';
            $des .= xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ': ' . xphp_get_lang($error['errorCodeDes'][$error['errorCode'][$result['error_code']]]);
            return $this->error($des, $result);
        }
    }

    /**
     * 获取Microsoft365组织自动刷新间隔时间
     * @return string 自动刷新间隔时间
     */
    public function getRefreshTime()
    {
        $result = $this->logic()->getRefreshTime();
        return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_ORAGNIZATION_AUTO_REFRESH_TIME'), $result);
    }

    /**
     * 更新Microsoft365组织自动刷新间隔时间
     * @return string 更新结果
     */
    public function editRefreshTime()
    {
        $this->checkParams('refresh_time_rule');
        $result = $this->logic()->editRefreshTime($this->param);
        return $this->success(xphp_get_lang('WEB_M365_SERVER_UPDATE_ORAGNIZATION_AUTO_REFRESH_TIME'), $result);
    }

    /**
     * 获取exchange server用于客户端关联的客户端
     * @return array 客户端信息
     */
    public function getServerAgent()
    {
        $result = $this->logic()->getServerAgent();
        return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_CLIENT_INFO'), $result);
    }

    /**
     * 同步
     * @return object 同步结果
     */
    public function syncOrganization()
    {
        $result = $this->logic()->syncOrganization($this->param);
        if ($result['result']) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_SYNC_ORAGNIZATION_SUCCESS'), $result);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_SYNC_ORAGNIZATION_ERROR'), $result);
        }
    }
}
