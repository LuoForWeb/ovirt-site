<?php

// +----------------------------------------------------------------------
// | wechat-php-sdk
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方文档: https://www.kancloud.cn/zoujingli/wechat-php-sdk
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/wechat-php-sdk
// +----------------------------------------------------------------------

namespace Wechat;

use Wechat\Lib\Common;
use Wechat\Lib\Tools;

/**
 * 微信粉丝操作SDK
 *
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/06/28 11:20
 */
class WechatWxapp extends Common
{

    /** 获取小程序码 */
    const WXACODE_GET_URL = '/wxa/getwxacode?';
    
    /** 获取小程序码 */
    const WXACODE_UNLIMIT_GET_URL = '/wxa/getwxacodeunlimit?';
    
    /** 获取小程序二维码 */
    const WXQRCODE_GET_URL = '/wxaapp/createwxaqrcode?';
    
    /**
     * 创建二维码
     * @param unknown $scene_id
     * @param number $type:1-小程序码，2-小程序二维码不限制，3-小程序二维码
     * 	$data=[
    		'scene'=>'',	
    		'page'=>'',	
    		'width'=>'',	
    		'auto_color'=>'',	
    		'line_color'=>'',	
    	];
     * @return boolean|boolean|mixed|mixed
     */
    public function getAcode($type = 0,$data=[])
    {
    
    	if (!$this->access_token && !$this->getAccessToken()) {
    		return false;
    	}
    	switch ($type){
    		case 1:
    			$url=self::API_BASE_URL_PREFIX . self::WXACODE_GET_URL . "access_token={$this->access_token}";
    			break;
    		case 2:
    			$url=self::API_BASE_URL_PREFIX . self::WXACODE_UNLIMIT_GET_URL . "access_token={$this->access_token}";
    			break;
    		case 3:
    			$url=self::API_URL_PREFIX . self::WXQRCODE_GET_URL . "access_token={$this->access_token}";
    			break;
    	}
    	 
    	$result = Tools::httpPost($url, Tools::json_encode($data));
    	if ($result) {
    		$json = json_decode($result, true);
    		if($json==null){
    			return $result;
    		}
    		if (empty($json) || !empty($json['errcode'])) {
    			$this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
    			$this->errMsg = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
    			return $this->checkRetry(__FUNCTION__, func_get_args());
    		}
    		return $json;
    	}
    	return false;
    }
}
