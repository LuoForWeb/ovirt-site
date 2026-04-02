<?php

namespace app\v1\system\v0\controller;
use app\v1\common\controller\AuthBase;
/**
 * note          系统授权 controller
 * @author       lilingyu@vinchin.com
 * @date         2024/7/18 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Auth extends AuthBase
{
    // 获取系统授权的基本信息
    public function getSystemLisenceInfo()
    {
        $info =  $this->logic()->getSystemLisenceInfo();
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
    // 获取系统指纹信息(权限验证)
    public function getThumbprint()
    {
        $info =  $this->logic()->getThumbprint();
        if($info['success'] == true){
            return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
        }
    }
    // 获取系统指纹信息文件
    public function getThumbprintFile()
    {
        $info =  $this->logic()->getThumbprintFile();
         return $info;
    }
    //上传授权文件
    public function uploadLicense()
    {
        $info =  $this->logic()->uploadLicense();
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    //获取系统授权信息(新页面)
    public function getSystemAuthInfo(){
        $info =  $this->logic()->getSystemAuthInfo();
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
    * 获取授权的一些基本信息
     * @return json
     */
    public function getLicenceInfo()
    {
         return $this->success('', $this->logic()->getLicenceInfo($this->param));
    }
}
