<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          系统配置之系统通知
 * @author       wanggongxi@vinchin.com
 * @date         2024/3/29 17:23
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Notice extends AuthBase
{
    /**
     * 获取邮件通知配置
     * @return json
     */
    public function getEmailConf()
    {
        $records = $this->logic()->getEmailConf($this->param);

        $this->success('', $records);
    }

    /**
     * 配置邮件通知
     * @return json
     */
    public function setEmailConf()
    {
        // 参数验证
        //$this->checkParams('init');

        $result = $this->logic()->setEmailConf($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 测试发送邮件通知
     * @return json
     */
    public function sendEmailTest()
    {
        // 参数验证
        $this->checkParams('testemail');

        $result = $this->logic()->sendEmailTest($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg'], $result['info'] ?? []);
    }

    /**
     * 保存邮件SMTP配置信息
     * @return json
     */
    public function setEmailSmtp()
    {
        // 参数验证
        $this->checkParams('smtp');

        $result = $this->logic()->setEmailSmtp($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 获取短信通知配置
     * @return json
     */
    public function getSmsConf()
    {
        $records = $this->logic()->getSmsConf($this->param);

        $this->success('', $records);
    }

    /**
     * 配置短信通知
     * @return json
     */
    public function setSmsConf()
    {
        // 参数验证
        //$this->checkParams('init');

        $result = $this->logic()->setSmsConf($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 测试短信猫发送
     * @return json
     */
    public function sendCatSmsTest()
    {
        // 参数验证
        $this->checkParams('sms');

        $result = $this->logic()->sendCatSmsTest($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 测试互联网短信发送
     * @return json
     */
    public function sendSmsTest()
    {
        // 参数验证
        $this->checkParams('sendsms');

        $result = $this->logic()->sendSmsTest($this->param);

        $this->success($result[0] . xphp_get_lang('WEB_SYSTEM_SETTING_SEND_SMS_SUCCESS'), ['value' => $result[1]]);
    }

    /**
     * 获取微信通知配置
     * @return json
     */
    public function getWechatConf()
    {
        $records = $this->logic()->getWechatConf($this->param);

        $this->success('', $records);
    }

    /**
     * 配置微信通知
     * @return json
     */
    public function setWechatConf()
    {
        $result = $this->logic()->setWechatConf($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 获取微信通知的二维码
     * @return json
     */
    public function getWechatQrcode()
    {
        $records = $this->logic()->getWechatQrcode($this->param);

        $this->success('', $records);
    }

    /**
     * 发送测试微信通知
     * @return json
     */
    public function sendWechatTest()
    {
        $this->param['test_notice'] = 1;
        $result = $this->logic()->sendWechatSet($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 保存微信通知配置
     * @return json
     */
    public function updateWechatConf()
    {

        $result = $this->logic()->sendWechatSet($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 获取微信通知用户列表
     * @return json
     */
    public function getWechatUser()
    {
        $records = $this->logic()->getWechatUser($this->param);

        $this->success('', $records);
    }

    /**
     * 删除微信授权用户
     * @return json
     */
    public function delWechatUser()
    {
        $result = $this->logic()->delWechatUser($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 获取企业微信通知配置
     * @return json
     */
    public function getWecomConf()
    {
        $records = $this->logic()->getWecomConf($this->param);

        $this->success('', $records);
    }

    /**
     * 配置企业微信通知
     * @return json
     */
    public function setWecomConf()
    {
        $result = $this->logic()->setWecomConf($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 发送企业微信测试通知
     * @return json
     */
    public function sendWecomTest()
    {
        $this->param['test_notice'] = 1; // 表明是测试
        $result = $this->logic()->sendWecomSet($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 保存企业微信通知配置
     * @return json
     */
    public function updateWecomConf()
    {
        $result = $this->logic()->sendWecomSet($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 发送邮件通知
     * @return json
     */
    public function sendEmail()
    {
        $result = $this->logic()->sendEmail($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 发送短信通知
     * @return json
     */
    public function sendSms()
    {
        $result = $this->logic()->sendSms($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }

    /**
     * 发送微信通知
     * @return json
     */
    public function sendWechat()
    {
        $result = $this->logic()->sendWechat($this->param);

        if ($result) {
            $this->success(xphp_get_lang('WEB_UTILS_SEND_INFO_SUCCESS'));
        }
        $this->error(xphp_get_lang('WEB_UTILS_SEND_INFO_ERROR'));
    }

    /**
     * 发送企业微信通知
     * @return json
     */
    public function sendWecom()
    {
        $result = $this->logic()->sendWecom($this->param);

        if ($result['code'] == 0) {
            $this->success($result['msg']);
        }
        $this->error($result['msg']);
    }
}
