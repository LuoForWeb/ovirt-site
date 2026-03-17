<?php
// phpcs:ignoreFile -- 框架类
namespace xphp;

/*********************************************************************************
 * 扩展类库-CURL
 ***********************************************************************************/
class Curl
{
    public function __construct()
    {
        if (!extension_loaded("curl")) {
            throw new \Exception("PHP不支持curl扩展，请检查php.ini配置！");
        }
    }

// 	private $cookie = '/data/cookie.txt'; //cookie保存路径,相对于APP_PATH
    private $timeout = 10;

    /**
     * 统一返回消息
     * @param unknown $result
     * @param unknown $data
     * @param string $errorInfo
     */
    private function result($result, $data, $errorInfo = '')
    {
        if ($result) {
            //成功获取消息并返回
            $data = json_decode($data, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);

            if ($data['msgcode'] >= 0) {
                $info = array(
                    'result' => true,
                    'errorCode' => 0,
                    'errorMsg' => '',
                    'data' => $data
                );
            } else {
                $info = array(
                    'result' => false,
                    'errorCode' => $data['msgcode'],
                    'errorMsg' => $data['msgtxt'],
                    'data' => $data
                );
            }
        } else {
            //获取消息失败
            $info = array(
                'result' => false,
                'errorCode' => 51508,
                'errorMsg' => $errorInfo,
                'data' => $data
            );
        }
        return $info;
    }

    /**
     * CURL-get方式获取数据
     * @param string $url URL
     * @param string $proxy 是否代理
     * @param int $timeout 请求时间
     * @param array $header header信息
     */
    public function get($url, $data, $header = null, $timeout = 10, $proxy = null)
    {
        if (!$url) return false;
        $ssl = stripos($url, 'https://') === 0 ? true : false;
        $curl = curl_init();
        if (!is_null($proxy)) curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        }
        $cookie_file = $this->get_cookie_file();
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file); //连接结束后保存cookie信息的文件。
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);//包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HOSTNAME']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数。
// 		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if (is_array($header))
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置请求的Header

        $content = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        if ($curl_errno > 0) {
            $error = sprintf("%s", curl_error($curl));
            curl_close($curl);
// 			throw new Exception($error);
            return $this->result(false, array(), $error);
        }
        curl_close($curl);
        return $this->result(true, $content);
    }

    /**
     * CURL-post方式获取数据
     * @param string $url URL
     * @param array $data POST数据
     * @param string $proxy 是否代理
     * @param int $timeout 请求时间
     * @param array $header header信息
     */
    public function post($url, $data, $header = null, $timeout = 10, $proxy = null)
    {
        if (!$url) return false;
// 		if ($data) {
// 			$data = http_build_query($data);
// 		}
        $ssl = stripos($url, 'https://') === 0 ? true : false;
        $curl = curl_init();
        if (!is_null($proxy)) curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        }
        $cookie_file = $this->get_cookie_file();
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file); //连接结束后保存cookie信息的文件。
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);//包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HOSTNAME']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_POST, true); //发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//Post提交的数据包
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数。
        if (is_array($header))
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置请求的Header

        $content = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        if ($curl_errno > 0) {
            $error = sprintf("%s", curl_error($curl));
            curl_close($curl);
// 			throw new Exception($error);
            return $this->result(false, array(), $error);
        }
        curl_close($curl);
        return $this->result(true, $content);
    }

    /**
     * CURL-post方式获取数据 5.0.8之前的，5.0.8之后的适配了数据库CDP，不适合了。
     * @param string $url URL
     * @param array $data POST数据
     * @param string $proxy 是否代理
     * @param int $timeout 请求时间
     * @param array $header header信息
     */
    public function oldPost($url, $data, $proxy = null, $timeout = 10, $header = null)
    {
        if (!$url) return false;
        if ($data) {
            $data = http_build_query($data);
        }
        $ssl = stripos($url, 'https://') === 0 ? true : false;
        $curl = curl_init();
        if (!is_null($proxy)) curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        }
        $cookie_file = $this->get_cookie_file();
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file); //连接结束后保存cookie信息的文件。
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);//包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_POST, true); //发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//Post提交的数据包
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数。
        if (is_array($header))
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置请求的Header

        $content = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        if ($curl_errno > 0) {
            $error = sprintf("curl error=%s, errno=%d.", curl_error($curl), $curl_errno);
            curl_close($curl);
            return false;
            throw new Exception($error);
        }
        curl_close($curl);
        return $content;
    }

    /**
     * CURL-put方式获取数据
     * @param string $url URL
     * @param array $data POST数据
     * @param string $proxy 是否代理
     * @param int $timeout 请求时间
     * @param array $header header信息
     */
    public function put($url, $data, $header = null, $timeout = 10, $proxy = null)
    {
        if (!$url) return false;
// 		if ($data) {
// 			$data = http_build_query($data);
// 		}
        $ssl = stripos($url, 'https://') === 0 ? true : false;
        $curl = curl_init();
        if (!is_null($proxy)) curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        }
        $cookie_file = $this->get_cookie_file();
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file); //连接结束后保存cookie信息的文件。
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);//包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HOSTNAME']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数
        $data = (is_array($data)) ? http_build_query($data) : $data;
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($data)));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if (is_array($header))
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置请求的Header

        $content = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        if ($curl_errno > 0) {
            $error = sprintf("%s", curl_error($curl));
            curl_close($curl);
// 			throw new Exception($error);
            return $this->result(false, array(), $error);
        }
        curl_close($curl);
        return $this->result(true, $content);
    }

    /**
     * CURL-DEL方式获取数据
     * @param string $url URL
     * @param array $data POST数据
     * @param string $proxy 是否代理
     * @param int $timeout 请求时间
     * @param array $header header信息
     */
    public function del($url, $data, $header = null, $timeout = 10, $proxy = null)
    {
        if (!$url) return false;
// 		if ($data) {
// 			$data = http_build_query($data);
// 		}
        $ssl = stripos($url, 'https://') === 0 ? true : false;
        $curl = curl_init();
        if (!is_null($proxy)) curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        }
        $cookie_file = $this->get_cookie_file();
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file); //连接结束后保存cookie信息的文件。
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);//包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HOSTNAME']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数
        $data = (is_array($data)) ? http_build_query($data) : $data;
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DEL');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($data)));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if (is_array($header))
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置请求的Header

        $content = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        if ($curl_errno > 0) {
            $error = sprintf("%s", curl_error($curl));
            curl_close($curl);
// 			throw new Exception($error);
            return $this->result(false, array(), $error);
        }
        curl_close($curl);
        return $this->result(true, $content);
    }

    /**
     * 自定义执行curl函数，作为默认get/post/put/del方法的补充
     * @param string $url 请求的URL的完整地址如:http://www.initphp.com/
     * @param array|string $data post/put/del提交的数据，可以是query string或php数组
     * @param array $options 自定义参数，格式与curl_setopt_array要求一致
     * @return string
     * @throws Exception
     * @todo 下一步准备用exec封装get/post/put/del方法，减少代码冗余
     */
    public function exec($url, $data = array(), array $options = array())
    {
        $curl = curl_init();
        if ($url) $options[CURLOPT_URL] = $url; //请求的URL，完整地址
        if ($data) {//post/put/del请求的数据设置
            $options[CURLOPT_POSTFIELDS] = is_array($data) ? http_build_query($data) : strval($data);
            !isset($options[CURLOPT_CUSTOMREQUEST]) && $options[CURLOPT_POST] = true;
        }
        //默认选项
        !isset($options[CURLOPT_HEADER]) && $options[CURLOPT_HEADER] = 0;//启用时会将头文件的信息作为数据流输出。
        !isset($options[CURLOPT_FOLLOWLOCATION]) && $options[CURLOPT_FOLLOWLOCATION] = 1;//启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        !isset($options[CURLOPT_RETURNTRANSFER]) && $options[CURLOPT_RETURNTRANSFER] = 1;//文件流形式
        !isset($options[CURLOPT_TIMEOUT]) && $options[CURLOPT_TIMEOUT] = $this->get_timeout();//curl执行超时时间
        !isset($options[CURLOPT_COOKIEJAR]) && $options[CURLOPT_COOKIEJAR] = $this->get_cookie_file();//连接结束后保存cookie信息的文件。
        !isset($options[CURLOPT_COOKIEFILE]) && $options[CURLOPT_COOKIEFILE] = $this->get_cookie_file();//包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
        !isset($options[CURLOPT_USERAGENT]) && $options[CURLOPT_USERAGENT] = $_SERVER['HOSTNAME'];//在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt_array($curl, $options);
        //执行并返回
        $content = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        if ($curl_errno > 0) {
            $error = sprintf("%s", curl_error($curl));
            curl_close($curl);
// 			throw new Exception($error);
            return $this->result(false, array(), $error);
        }
        curl_close($curl);
        return $this->result(true, $content);
    }

    /**
     * CURL-综合方式获取数据
     * @param string $url URL
     * @param array $data POST数据
     * @param string $proxy 是否代理
     * @param int $timeout 请求时间
     * @param array $header header信息
     */
    public function curl($url, $data, $method = 'GET', $header = null, $timeout = 10, $proxy = null)
    {
        if (!$url) return false;

        $ssl = stripos($url, 'https://') === 0 ? true : false;
        $curl = curl_init();
        if (!is_null($proxy)) curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        }
        $cookie_file = $this->get_cookie_file();
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file); //连接结束后保存cookie信息的文件。
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);//包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HOSTNAME']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数
        $data = (is_array($data)) ? http_build_query($data) : $data;
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($data)));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if (is_array($header))
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置请求的Header

        $content = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        if ($curl_errno > 0) {
            $error = sprintf("%s", curl_error($curl));
            curl_close($curl);
            return $error;
        }
        curl_close($curl);
        return $content;
    }


    /**
     * post以json的格式请求
     * @param string $url 请求url
     * @param array  $data 请求参数，key=>val
     * @param array  $header 请求头，格式是一维数组，组装好了的header头
     * @param int    $timeout 超时时间，单位s
     * @param string $proxy 代理
     **/
    public function post_json($url, $data, $header = null, $timeout = 10, $proxy = null)
    {
        if (!$url) return false;

        $ssl = stripos($url, 'https://') === 0 ? true : false;
        $curl = curl_init();

        if (!is_null($proxy)) {
            curl_setopt($curl, CURLOPT_PROXY, $proxy);
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        }

        $cookie_file = $this->get_cookie_file();
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_HOST']); // 建议用 HTTP_HOST 而非 HOSTNAME
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, true);

        $data = json_encode($data, JSON_UNESCAPED_UNICODE); // 保持中文可读

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        // 处理 header
        if (is_array($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        $content = curl_exec($curl);
        $curl_errno = curl_errno($curl);

        if ($curl_errno > 0) {
            $error = curl_error($curl);
            curl_close($curl);
            return $this->result(false, array(), $error);
        }

        curl_close($curl);
        return $this->result(true, $content);
    }

    /**
     * 获取COOKIE存放文件的地址
     */
    private function get_cookie_file()
    {
        return "";
// 		return APP_PATH . $this->cookie;
    }

    /**
     * 获取curl超时时间
     * @return int
     */
    private function get_timeout()
    {
        return $this->timeout;
    }
}
