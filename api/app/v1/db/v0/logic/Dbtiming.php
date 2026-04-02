<?php

namespace app\v1\db\v0\logic;

use app\v1\common\logic\Base;

class Dbtiming extends Base
{
    public $authKey = '';
    public $authTime = '';

    //定义header
    private $header = array(
        'Content-Type: application/json;charset=utf-8',
    );
    private $url = array(
        'getDBAuth' => '/vc/login',
        'getDBThumbprint' => '/vc/license/request',
        'uploadDBLicense' => '/vc/license/import',
        'getDBLisenceInfo' => '/vc/license/info',
        'getDBStorageInfo' => '/vc/storage/info',
        'addDBStorage' => '/vc/storage/add',
        'getVersion' => '/vc/version'

    );
    private $port = 8089;

    /**
     * 获取数据库定时API认证
     */
    public function getDBAuth()
    {
        $this->writeLog('get Datapp auth!!!!!!!!');
        $url = 'https://' . $_SERVER['SERVER_ADDR'] . ':' . $this->port . $this->url['getDBAuth'];
        $data =  array(
            'name' => "admin",
            'password' => "Admin@3R"
        );
        $result = $this->getHttpData($url, 'POST', $data);
        $result = json_decode($result, true);
        $info = $result['data'];
        $this->authKey = $info['accessToken'];
        $this->header[] = 'Authorization: ' . $info['accessToken'];
        $this->authTime = time() + intval($info['expiresIn']);
    }

    /**
     * 获取数据库定时授权指纹信息
     */
    public function getDBThumbprint()
    {
        $this->writeLog('get Datapp thumbprint!!!!!!!!');
        $url = 'https://' . $_SERVER['SERVER_ADDR'] . ':' . $this->port . $this->url['getDBThumbprint'];
        $result = $this->getHttpData($url, 'GET', array());
        $result = json_decode($result, true);
        $info = $result['data'];
        $key = $info['requestCode'];
        return $key;
    }

    /**
     * 授权数据库定时
     */
    public function uploadDBLicense($key)
    {
        $this->writeLog('upload Datapp license!!!!!!!!');
        $url = 'https://' . $_SERVER['SERVER_ADDR'] . ':' . $this->port . $this->url['uploadDBLicense'];
        $data = array(
            'license' => $key
        );
        $result = $this->getHttpData($url, 'POST', $data);
        return $result;
    }

    /**
     * 获取Datapp授权信息
     */
    public function getDBLisenceInfo()
    {
        $url = 'https://' . $_SERVER['SERVER_ADDR'] . ':' . $this->port . $this->url['getDBLisenceInfo'];
        $result = $this->getHttpData($url, 'GET', array());
        $result = json_decode($result, true);
        return $result['data'];
    }

    /**
     * 获取Datapp存储端id
     */
    public function getDBStorageInfo()
    {
        $this->writeLog('get Datapp storage info!!!!!!!!');
        $url = 'https://' . $_SERVER['SERVER_ADDR'] . ':' . $this->port . $this->url['getDBStorageInfo'];
        $result = $this->getHttpData($url, 'GET', array());
        $result = json_decode($result, true);
        $info = $result['data'];
        return $info;
    }

    /**
     * 添加存储到数据库定时
     */
    public function addDBStorage($id, $path, $size)
    {
        $this->writeLog('add Datapp storage!!!!!!!!');
        $url = 'https://' . $_SERVER['SERVER_ADDR'] . ':' . $this->port . $this->url['addDBStorage'];
        $data = array(
            'id' => $id,
            'path' => $path,
            'limitSpace' => $size
        );
        $result = $this->getHttpData($url, 'POST', $data);
        return  $result;
    }

    /**
     * 获取数据库定时版本
     */
    public function getVersion()
    {
        $this->writeLog('get Datapp version!!!!!!!!');
        $url = 'https://' . $_SERVER['SERVER_ADDR'] . ':' . $this->port . $this->url['getVersion'];
        $result = $this->getHttpData($url, 'GET', array());
        $result = json_decode($result, true);
        $info = $result['data'];
        return $info;
    }

    /**
     * API通信
     * @param string $url
     * @param string $type
     * @param array  $params
     */
    private function getHttpData($url, $type, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header); //设置头信息的地方
        curl_setopt($ch, CURLOPT_HEADER, 0); //不取得返回头信息
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        if ($type == 'POST') {
            $params = json_encode($params, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        $result = curl_exec($ch);

        return $result;
    }
}
