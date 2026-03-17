<?php

/**
 * 公共函数文件
 * 以  xphp_开头，单词之间用 _ 下划线隔开，单词用小写
 */

/**
 * 用户登录成功获取信息
 * @param string $authtoken token值
 * @return array
 */
function xphp_get_user_info(string $authtoken = ''): array
{

    if ($authtoken) {
        $cache = getCache($authtoken);
    } elseif (getCache('auth_token_')) {
        $cache = getCache(getCache('auth_token_'));
    } else {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        $cache = getCache('auth_token_' . $authorization);
    }

    // 兼容下apikey的方式
    if (empty($cache) && !empty(getCache('auth_apikey_'))) {
        $cache = getCache('auth_token_' . getCache('auth_apikey_'));
    }

    if (empty($cache) && getEnvs('APP_DEBUG')) {
        // 开发模式下 自动获取管理的
        $cache = [
            'userName' => 'admin',
            'userUuid' => 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9',
            'userType' => 3,
            'userLevel' => 1,
            'email' => 'vinchin@vinchin.com',
            'permission' => [],  // 这个应该是左侧的菜单的权限和右边的一个大的功能块 但是不包含细致化的操作
            // 主要是为了获取方法cat的name数组 为了在获取用户权限树的时候判断是否有分配详细操作的权限
            'permissionArr' => [],
            // 用户拥有的具体的方法和类的校验 get=>['路由1','路由2']
            'permissionFunction' => [],
            // page里面所有的方法操作数组  这个之前是因为怕有些接口漏掉，所以权限验证如果路由不在这个里面配置的都可以放过
            'permissionFunctions' => [],
            'permissionVisualScreen' => 100,  // 大屏权限
            'language' => 'zh-cn',
            'tenantuuid' => '',
            'tenantusername' => '',
            'timeout' => time() + 10000
        ];
        $cache['permission'] = [];
        $cache['permissionArr'] = [];
        $cache['permissionFunction'] = [];
        $cache['permissionFunctions'] = [];
    }

    if (empty($cache) || $cache['timeout'] < time()) {
        return [];
    }

    return $cache;
}

/**
 * 用户登录成功设置用户信息
 * @param string $token    token
 * @param array  $userinfo 用户信息
 * @return void
 */
function xphp_set_user_info($token, $userinfo = [])
{

    // 判断下过期时间，如果是0 那么表示永久有效 那么过期时间设置为 2099-12-31
    $timeout = xphp_get_config('special', 'access_token_timeout');
    $timeout = $timeout == 0 ? 4102329600 : (time() + $timeout);
    $userinfo['timeout'] = $timeout;

    setCache('auth_token_' . $token, $userinfo);

    // 同时存一下token的值
    setCache('auth_token_', 'auth_token_' . $token);

    // 同时兼容大屏
    setCache('auth_token_visualscreen_' . $token, time());
}

/**
 * 更新用户信息 适合在接口请求的入口方式更改用户缓存信息
 * @param array $userinfo 用户信息
 * @return void
 * */
function xphp_update_user_info($userinfo = [])
{

    $usr = xphp_get_user_info();
    $newUser = array_merge($usr, $userinfo);

    $authorization = $_SERVER['HTTP_AUTHORIZATION'];
    xphp_set_user_info($authorization, $newUser);
}

/**
 * 用户退出登录
 * @return void
 */
function xphp_user_loginout()
{

    // 1. 启动 session
    session_start();

    // 2. 获取需要保留的数据（在销毁之前获取）
    $user = xphp_get_user_info();
    $permissionVisualScreen = false;
    // 判断是否拥有大屏的权限
    if (!empty($user['permissionVisualScreen']) && $user['permissionVisualScreen'] == 100) {
        $permissionVisualScreen = true;
    }
    // 这里保留语言的Session和用户名
    $language = $user['language'];
    $username = $user['tenantusername'];

    // 3. 清空 $_SESSION 数组中的所有数据
    $_SESSION = [];

    // 4. 获取 session 的 cookie 参数
    $params = session_get_cookie_params();

    // 5. 删除浏览器中的 session cookie（PHPSESSID）
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );

    // 6. 销毁服务器端 session 数据
    session_destroy();

    // 7. 重新启动一个新的会话，用于存储需要保留的数据（非认证信息）
    session_start();
    // 重新生成session id，避免使用旧的session id
    session_regenerate_id(true);

    // 8. 设置需要保留的数据
    $arr = [];
    if ($permissionVisualScreen) {
        $arr['permissionVisualScreen'] = 100;
    }
    $arr['language'] = $language;
    $arr['tenantusername'] = $username;

    // 假设setCaches函数是将数组中的值设置到$_SESSION中
    setCaches($arr);

    // 9. 清除web_ng的缓存目录
    xphp_delete_dir_file(DATA_PATH . '/runtime/cache');
}

/**
* 是否读取老版本的配置
 * 是的话返回 根目录地址
 * 不是的话 返回false
 * @return bool
 */
function xphp_get_oldpath()
{
    if (getEnvs('GET_OLD_CONFIG')) {
        // 读取老框架的配置
        #老框架的主配置目录 /vinchin/web_ng/api/public/
        return dirname(__DIR__, 3) . '/';
    }
    return false;
}

/**
 * 菜单文件读取
 * @param string $name        菜单名称
 * @param bool   $force       是否强制刷新
 * @param array  $permission  用户当前权限菜单
 * @param string $tenantuuid  所属租户
 * @return array
 */
function xphp_get_menu($name = '', $force = false, $permission = [], $tenantuuid = '', $homepage = 'javascript:;')
{

    $version = getXphpVersion();
    $cachekey = 'xphp_menu_' . $name . '_' . $version;

    $cache = xphp_get_cache($cachekey, true);
    if (empty($cache) || $force) {
        // 读取出 menu主配置文件
        $config = getConfig(APP_PATH . $version . '/menu/menu.php');

        $configs = [];
        if (empty($name)) {
            // 读取出所有数据
            foreach ($config as &$item) {
                $item['child'] = getConfig(APP_PATH . $version . '/menu/' . $item['name'] . '.php');
            }
        } else {
            // 读取这个菜单的所有信息
            foreach ($config as $item) {
                if ($item['name'] == $name) {
                    $configs = $item;
                    break;
                }
            }
            $configs['child'] = getConfig(APP_PATH . $version . '/menu/' . $name . '.php');
        }

        $cache = empty($name) ? $config : $configs;

        xphp_set_cache($cachekey, $cache, true);
    }

    if (!empty($permission)) {
        // 用户当前权限菜单
        $cache = xphp_get_in_array($cache, $permission, $tenantuuid, $homepage);
    }
    return $cache ?? [];
}

/**
 * 从多维菜单数组中递归筛选出 name 在指定列表中的项（排除 level=10 的项）
 *
 * @param array  $array      多维菜单数组
 * @param array  $arrayKey   允许的 name 列表
 * @param string $tenantuuid 所属租户
 * @param string $homepage   homepage
 * @return array            筛选后的新数组
 */
function xphp_get_in_array($array = [], $arrayKey = [], $tenantuuid = '', $homepage = '')
{
    $newArray = [];
    foreach ($array as $item) {
        // 跳过 level 为 10 的项
        if (isset($item['level']) && $item['level'] == 10) {
            continue;
        }

        if (!empty($tenantuuid) && in_array($item['name'], ['remote_system', 'cloud_storage', 'vm_overview', 'billing_manager'])) {
          continue;
        }

        // 检查当前项的 name 是否在允许列表中
        if (isset($item['name']) && in_array($item['name'], $arrayKey)) {
            $path = $item['path'] ?? '';
            $name = $item['name'] ?? '';
            if (!empty($tenantuuid) && in_array($item['name'], ['vmprotect', 'tenant_manager'])) {
                if ($item['name'] == 'vmprotect') {
                    // 虚拟机保护,修改备份页面
                    $path = "vmBackup.html";
                }
                if ($item['name'] == 'tenant_manager') {
                    // 系统管理,修改租户页面
                    $path = "/system/tenant/html/index.php?uuid=" . $tenantuuid;
                    $name = 'p_tenant_view';
                }
            }
            if ($item['name'] == 'homepage') {
                $path = $homepage;
            }

            $arr = [
                'name'  => $name,
                'path'  => $path,
                'class' => $item['class'] ?? '',
                'level' => $item['level'] ?? 0,
                'showChild' => $item['showChild'] ?? false,
                'title' => xphp_get_web_lang($item['title'] ?? '', 'common'),
            ];

            // 递归处理子项（保留所有子项，不进行筛选）
            if (!empty($item['child']) && is_array($item['child'])) {
                $arr['child'] = xphp_get_in_array($item['child'], $arrayKey);
            }

            $newArray[] = $arr;
        }
    }
    return $newArray;
}

/**
 * 语言包读取
 * @param string $key    语言包键
 * @param string $module 所在模块
 * @param bool   $force  是否强制刷新
 * @return string|array
 */
function xphp_get_lang($key = '', $module = '', $force = false)
{
    // 表示不是语言包 那么直接返回 key
    if (
        !empty($key) &&
        !(strpos($key, 'WEB_') !== false || strpos($key, 'UI_') !== false || strpos($key, 'API_') !== false)
    ) {
        return $key;
    }

    // 语言包类型
    $langtype = xphp_get_language_type();

    $version = getXphpVersion();
    $cachekey = 'xphp_lang_' . $module . '_' . $key . '_' . $version . '_' . $langtype;

    $cache = cache($cachekey);
    if (empty($cache) || $force) {
        // 缓存所有的语言配置一次
        $keys = 'xphp_lang_list_' . $module . '_' . $version . '_' . $langtype;
        // 强制获取缓存
        $language = cache($keys);
        if (empty($language) || $force) {
            // 读取出系统公共的语言包
            $language = getConfig(LANG_PATH . $langtype . '.php');

            // 合并下当前版本的语言包
            $languages = getConfig(APP_PATH . $version . '/common/lang/' . $langtype . '.php');
            $language = array_merge($language, $languages);

            if (!empty($module)) {
                // 还存在模块的话 那么读取出模块里面的语言配置
                $languages = getConfig(APP_PATH . $version . '/' . $module . '/lang/' . $langtype . '.php');
                $language = array_merge($language, $languages);
            }
            cache($keys, $language, 60);
        }

        $cache = !empty($key) ? ($language[$key] ?? $key) : $language;
        cache($cachekey, $cache);
    }
    return $cache;
}

/**
 * 语言包读取(web页面)
 * @param string $key    语言包键
 * @param string $pre    语言包前缀
 * @param bool   $force  是否强制刷新
 * @return string|array
 */
function xphp_get_web_lang($key = '', $pre = '', $force = false)
{
    // 表示不是语言包 那么直接返回 key
    if (!empty($key) && !(str_contains($key, 'WEB_PAGE_'))) {
        return $key;
    }

    // 语言包类型
    $langtype = xphp_get_language_type();

    $version = getXphpVersion();
    $cachekey = 'xphp_web_lang_' . $pre . '_' . $key . '_' . $version . '_' . $langtype;

    $cache = cache($cachekey);
    if (empty($cache) || $force) {
        // 缓存所有的语言配置一次
        $keys = 'xphp_web_lang_list_' . $pre . '_' . $version . '_' . $langtype;
        $pres = xphp_get_config('lang', 'webPre');
        $pre = $pres[$pre] ?? '';
        // 强制获取缓存
        $language = cache($keys);
        if (empty($language) || $force) {
            // 读取出系统公共的语言包
            $language = getConfig(LANG_PATH . $langtype . '.php');

            // 合并下当前版本的语言包
            $languages = getConfig(APP_PATH . $version . '/common/lang/' . $langtype . '.php');
            $language = array_merge($language, $languages);

            if (!empty($pre)) {
                // 筛选出 key 以 'WEB_PAGE_COMMON_MENUS_' 开头的项
                $language = array_filter(
                    $language,
                    function ($key) use ($pre) {
                        return str_starts_with($key, $pre);
                    },
                    ARRAY_FILTER_USE_KEY // 告诉 array_filter 回调函数接收的是 key
                );
            }

            cache($keys, $language, 60);
        }

        $cache = !empty($key) ? ($language[$key] ?? $key) : $language;
        cache($cachekey, $cache);
    }
    return $cache;
}

/**
* 语言包类型获取 根据当前登录的用户
 * @return string
 */
function xphp_get_language_type()
{

    $user = xphp_get_user_info();
    if (empty($user)) {
        // 语言包类型读取配置文件里面的
        $langtype = xphp_get_config('app', 'lang');
    } else {
        $langtype = $user['language'];
    }
    // 都没取到 那么就默认中文
    return $langtype ?? 'zh-cn';
}

/**
 * 配置文件读取
 * @param string $name   名称
 * @param string $key    键
 * @param string $module 模块名称
 * @param bool   $force  是否强制刷新
 * @return array|mixed
 */
function xphp_get_config(string $name, $key = '', $module = '', $force = false)
{

    $version = getXphpVersion();
    $cachekey = 'xphp_config_' . $name . '_' . $key . '_' . $module . '_' . $version;

    $cache = cache($cachekey);
    if (empty($cache) || $force) {
        // 这里对所有的配置文件进行一个强制的缓存
        $keys = 'xphp_config_lists_' . $name . '_' . $module . '_' . $version;
        // 强制获取缓存
        $config = cache($keys);
        if (empty($config) || $force) {
            $oldpath = xphp_get_oldpath();
            if (in_array($name, ['error', 'log_system', 'log_task']) && $oldpath) {
                // 读取老框架的配置错误码
                $langtype = xphp_get_language_type();
                // 读取老框架的配置
                // phpcs:ignore
                Xphp::$_lang = getConfig($oldpath . 'lang/' . $langtype . '.php');
                $config = getConfig($oldpath . 'api/xphp/conf/' . $name . '.php');
            } else {
                // 读取出系统公共的配置文件
                $config = getConfig(CONF_PATH . $name . '.php');

                // 合并下当前版本的配置
                $versionconfig = APP_PATH . $version . '/config/' . $name . '.php';
                $config = array_merge($config, getConfig($versionconfig));

                // 如果存在 module 那么在模块里面找
                if (!empty($module)) {
                    $moduleconfig = APP_PATH . $version . '/' . $module . '/config/' . $name . '.php';
                    $config = array_merge($config, getConfig($moduleconfig));
                }
            }
            // 缓存60秒，为了处理一个请求里面重复查询所有配置问题
            cache($keys, $config, 60);
        }

        if (empty($config) && !empty($key) && $name != 'app') {
            // 其它配置文件未找到 那么就读取app里面的试试
            return xphp_get_config('app', $key, $force);
        }

        // 如果是 app 的配置文件，还需要读取出特别的配置并组合下 ...
        if ($name == 'app' && !in_array($key, ['SPECIAL_DIR', 'SPECIAL_CONFIG'])) {
            $configFile = xphp_get_config('app', 'SPECIAL_DIR') . xphp_get_config('app', 'SPECIAL_CONFIG');
            if (file_exists($configFile)) {
                $content = file_get_contents($configFile);
                $content = json_decode($content, true);
                xphp_recursive($config, $content);
            }
        }

        $cache = !empty($key) ? ($config[$key] ?? $key) : $config;
        cache($cachekey, $cache);
    }
    return $cache;
}

/**
* 递归的比较数组并替换
 * @param array $arrA 名称
 * @param array $arrB 键
 * @return void
 */
function xphp_recursive(array &$arrA, array $arrB)
{
    foreach ($arrB as $key => $value) {
        if (is_array($value)) {
            // 如果 $arrA[$key] 不存在或不是数组，则初始化为一个空数组
            if (!isset($arrA[$key]) || !is_array($arrA[$key])) {
                $arrA[$key] = [];
            }
            // 递归调用
            xphp_recursive($arrA[$key], $value);
        } else {
            $arrA[$key] = $value;
        }
    }
}

/**
 * 描述文件读取
 * @param string $name   名称
 * @param string $key    键
 * @param string $module 模块名称
 * @param bool   $force  是否强制刷新
 * @return array|mixed
 */
function xphp_get_desc(string $name, string $key, $module = '', $force = false)
{
    $version = getXphpVersion();

    $cachekey = 'xphp_desc_' . $name . '_' . $key . '_' . $version;

    $cache = cache($cachekey);
    if (empty($cache) || $force) {
        // 对所有的描述文件配置进行一个短暂缓存,避免同一个接口多次请求
        $keys = 'xphp_desc_list_' . $name . '_' . $module  . '_' . $version;
        // 强制获取缓存
        $config = cache($keys);
        if (empty($config) || $force) {
            // 读取出系统公共的描述文件
            $config = getConfig(APP_PATH . 'description/' . $name . '.php');

            // 合并下当前版本的配置
            $versionconfig = APP_PATH . $version . '/description/' . $name . '.php';
            $config = array_merge($config, getConfig($versionconfig));

            if (!empty($module)) {
                // 获取模块下的描述文件
                $moduleconfig = APP_PATH . $version . '/' . $module . '/description/' . $name . '.php';
                $config = array_merge($config, getConfig($moduleconfig));
            }
            cache($keys, $config, 60);
        }


        $cache = $config[$key] ?? $key;

        // 处理下语言包翻译
        if (is_array($cache)) {
            $lang = xphp_get_lang();
            foreach ($cache as &$item) {
                if (strpos($item, 'WEB_') !== false || strpos($item, 'UI_') !== false) {
                    // 表示是语言包
                    if (strpos(trim($item), ' ') !== false) {
                        // 这里有个特殊的需要兼容  (语言包) . ' 其它说明 ' . (语言包). ' 其它说明2' 这种格式的
                        $items = explode(' ', $item);
                        $item2s = [];
                        foreach ($items as $item2) {
                            if (strpos($item2, 'WEB_') !== false || strpos($item2, 'UI_') !== false) {
                                $item2s[] = $lang[$item2];
                            } else {
                                $item2s[] = $item2;
                            }
                        }
                        $item = implode(' ', $item2s);
                    } else {
                        $item = $lang[$item];
                    }
                }
            }
        }

        cache($cachekey, $cache);
    }
    return $cache;
}

/**
 * AES 加密 和 js 加解密通用
 * @param string $data 加密串
 * @return string
 */
function xphp_encrypt_js($data = '')
{
    // 加密
    $aes = openssl_encrypt(
        $data,
        'AES-256-CBC',
        getEnvs('PARAM_CRYPT_KEY'),
        OPENSSL_RAW_DATA,
        getEnvs('PARAM_CRYPT_IV')
    );
    return bin2hex($aes);
}

/**
 * AES 解密 和 js 加解密通用
 * @param string $data 解密串
 * @return string
 */
function xphp_decrypt_js($data = '')
{
    // 解密
    $mes = hex2bin($data);
    return openssl_decrypt(
        $mes,
        'AES-256-CBC',
        getEnvs('PARAM_CRYPT_KEY'),
        OPENSSL_RAW_DATA,
        getEnvs('PARAM_CRYPT_IV')
    );
}

/**
 * 过滤编辑器xss
 * @param $data 过滤前字符串
 * @return string
 */
function xphp_xss_remove($data)
{
    if ($data === '' || $data === (string)((int)$data)) {
        return $data;
    }

    /*$check = false;
    // 使用正则表达式匹配 <xxx> 单标签，其中 xxx 是纯英文字母开头的任意字符串
    $data = preg_replace_callback('/<([a-zA-Z][\w.]*)\s*\/?>/u', function ($matches) use (&$check) {
        $check = true;
        // 转义匹配到的标签内容 去除两边的括号
        return htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8');
    }, $data);

    if ($check) {
        return $data;
    }

    if (preg_match('/<([a-zA-Z][\w.]*)\s*\/?/u', $data)) {
        // 以 <x* 开始的参数值不进行过滤
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }*/

    $cleanxssconfig = HTMLPurifier_Config::createDefault();
    $cleanxssconfig->set('Core.Encoding', 'UTF-8');
    // 保留的标签
    $cleanxssconfig->set(
        'HTML.Allowed',
        'div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]'
    );
    $cleanxssconfig->set(
        'CSS.AllowedProperties',
        'font,font-size,font-weight,font-style,font-family,
        text-decoration,padding-left,color,background-color,text-align'
    );
    $cleanxssconfig->set('HTML.TargetBlank', true);
    $cleanxssconfig->set('Cache.DefinitionImpl', null);

    $cleanxssobj = new HTMLPurifier($cleanxssconfig);
    return $cleanxssobj->purify(html_entity_decode($data));
}

/**
 * uuid生成器
 * @return string
 */
function xphp_uuid(): string
{
    if (function_exists('com_create_guid')) {
        $uuid = com_create_guid();
    } else {
        mt_srand((int)((double)microtime() * 10000));
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
            . chr(125);// "}"
    }
    return strtolower(substr($uuid, 1, -1));
}

/**
 * 系统加密方法
 *
 * @param string $data   要加密的字符串
 * @param string $key    加密密钥
 * @param int    $expire 过期时间 单位 秒
 * @return string
 */
function xphp_encrpt($data, $key = 'vinchin', $expire = 0): string
{
    $key = md5($key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time() : 0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace(array (
        '+',
        '/',
        '='
    ), array (
        '-',
        '_',
        ''
    ), base64_encode($str));
}

/**
 * 系统解密方法
 *
 * @param string $data 要解密的字符串 （必须是aes_encrpt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string
 */
function xphp_decrypt($data, $key = 'vinchin'): string
{
    $key = md5($key);
    $data = str_replace(array (
        '-',
        '_'
    ), array (
        '+',
        '/'
    ), $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);

    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 系统加密方法
 *
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @return string
 */
function xphp_short_encrypt(string $data, $key = 'vinchin'): string
{
    // 使用 SHA-256 代替 MD5，SHA-256 更安全
    $key = hash('sha256', $key, true);

    // 使用 XOR 加密
    $encrypted = '';
    $len = strlen($data);
    $keyLen = strlen($key);

    for ($i = 0; $i < $len; $i++) {
        $encrypted .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
    }

    // 使用 URL-safe 的 Base64 编码
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($encrypted));
}

/**
 * 系统解密方法
 *
 * @param string $encryptedData 加密后的字符串
 * @param string $key           加密密钥
 * @return string
 */
function xphp_short_decrypt(string $encryptedData, $key = 'vinchin'): string
{
    // 使用 SHA-256 代替 MD5，SHA-256 更安全
    $key = hash('sha256', $key, true);

    // 将 URL-safe 的 Base64 编码转换为标准 Base64 编码并解码
    $encryptedData = base64_decode(str_replace(['-', '_'], ['+', '/'], $encryptedData));

    // 使用 XOR 解密
    $decrypted = '';
    $len = strlen($encryptedData);
    $keyLen = strlen($key);

    for ($i = 0; $i < $len; $i++) {
        $decrypted .= chr(ord($encryptedData[$i]) ^ ord($key[$i % $keyLen]));
    }

    return $decrypted;
}

/**
 * 判断是否是三权模式
 * @return bool
 */
function xphp_three_powers(): bool
{
    // return true; // 测试使用

    $cachekey = 'xphp_three_powers_';
    $cache = cache($cachekey);
    if (empty($cache)) {
        $sql = "select extension from bd_license";
        $data = dbSelect($sql, array());
        if (!empty($data)) {
            $data = json_decode(v1_decrypt($data[0]['extension']), true);
            if (!empty($data)) {
                $pagelist = $data['p']; // 授权页面
                $authFun = $data['f']; // 授权功能
                // 三权模式标识 threepowers 是定义在授权系统里面的授权功能里面
                if (!empty($authFun['threepowers'])) {
                    cache($cachekey, 1, 60);
                    return true;
                }
            }
        }
        cache($cachekey, 2, 60);
        return false;
    }

    return $cache == 1;
}

/**
* 校验当前登录用户是否是属于三权用户级别
 * @param string $useruuid 用户uuid
 * @return bool
 */
function xphp_check_three_user(string $useruuid): bool
{
    $cacheKey = 'xphp_check_three_user_cache_' . $useruuid;
    $cache = cache($cacheKey);

    if (empty($cache)) {
        $count = 0;
        $cache = 2;
        // 根据用户uuid去 mt_user_role 表查询是否有关联的角色uuid
        $role = dbSelect("select role_uuid from mt_user_role where user_uuid = ?", [$useruuid]);
        if (!empty($role)) {
            $roleArr = array_column($role, 'role_uuid');
            $count = count(array_intersect(
                $roleArr,
                array_values(array_filter(xphp_get_config('three_powers', 'INIT_ROLE_LIST')))
            ));
        }

        if (
            (xphp_three_powers() && $count) || (!xphp_three_powers() && !$count)
        ) {
            $cache = 1;
        }

        cache($cacheKey, $cache, 600);
    }
    return $cache == 1;
}

/**
* 根据用户uuid和权限标识返回管理的用户uuid集合
 * @param string $uuid 用户uuid
 * @param string $auth 权限标识
 * @return array
 */
function xphp_get_manager_uuid(string $uuid, string $auth = ''): array
{

    if (!empty($auth)) {
        // 根据uuid和auth查询关联的用户uuid集合
        $sql = "select user_uuid from bd_user where manager_uuid = ? and manager_auth like ?";
        $list = dbSelect($sql, [$uuid, '%,' . $auth . ',%']);
        $return = !empty($list) ? array_column($list, 'user_uuid') : [];
    } else {
        // 根据uuid查询关联的用户uuid集合
        $sql = "select user_uuid,manager_auth from bd_user where manager_uuid = ?";
        $list = dbSelect($sql, [$uuid]);
        // 组装下 权限 => 用户id集合
        $return = [];
        if (!empty($list)) {
            // 把所有的操作权限都汇总
            $allAuth = array_column($list, 'manager_auth');
            $allAuths = [];
            foreach ($allAuth as $items) {
                $allAuths = array_merge($allAuths, explode(',', $items));
            }
            $allAuths = array_unique(array_filter($allAuths)); // 去重去空
            // 组装存在的
            foreach ($list as $item) {
                $userAuth = array_filter(explode(',', $item['manager_auth']));
                $sameArray = array_intersect($allAuths, $userAuth);
                if (!empty($sameArray)) {
                    foreach ($sameArray as $item2) {
                        $return[$item2][] = $item['user_uuid'];
                    }
                }
            }
        }
    }

    return $return;
}

/**
* 判断用户是否有操作关联用户的权限
 * @param string $authcode 权限码
 * @param array  $useruuid 被操作的用户uuid集合
 * @return boolean
 */
function xphp_check_operate(string $authcode, array $useruuid = []): bool
{
    // 1 先判断操作的数据是否有不属于自身的
    // 2 有自身的，在判断是否有关联用户的操作权限
    $user = xphp_get_user_info();
    if ($user['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
        // 管理员可以操作
        return true;
    }

    $userArr = array_unique(array_filter($useruuid));
    if (count($userArr) > 1 || (count($userArr) == 1 && $userArr[0] != $user['userUuid'])) {
        // 表示有不属于自己的数据 那么需要判断是否越权
        // 获取拥有当前模块操作的所有用户集合
        $userArrs = array_merge($user['authUser'][$authcode] ?? [], [$user['userUuid']]);
        if (count(array_intersect($userArrs, $userArr)) < count($userArr)) {
            return false; // 没有权限
        }
    }
    return true;
}

/**
 * 删除文件夹内所有文件及子文件夹，但不删除文件夹本身
 * @param string $path 文件夹路径
 * @return boolean
 */
function xphp_delete_dir_file(string $path): bool
{
    $result = false;
    if (is_dir($path)) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if (($item != '.') && ($item != '..')) {
                    $fullPath = "$path/$item";
                    if (is_dir($fullPath)) {
                        xphp_delete_dir_file($fullPath); // 递归删除子文件夹内容
                        rmdir($fullPath); // 删除空子文件夹
                    } else {
                        unlink($fullPath); // 删除文件
                    }
                }
            }
            closedir($handle);
            $result = true;
        }
    }
    return $result;
}

/**
 *  获取 系统安全里面的数据安全的一些配置信息
 * @param int $type 某一项的配置  1 历史任务 2任务日志 3系统日志 4高可用日志 5任务告警 6系统告警
 * @return array 数组[类型, 数量] 类型1按个数 2按天数， 3永久保留
 */
function xphp_get_system_data_safe(int $type = 1): array
{
    // 获取 bd_system_settings 表里面 settings_type 为 SYSTEM_CONFIG_RECORDS（12） 的配置内容
    $sql = "select settings_content from bd_system_settings where settings_type = ?";

    $data = dbSelect($sql, [xphp_get_config('app', 'SETTINGS_CONF')['SYSTEM_CONFIG_RECORDS']]);

    if (!empty($data)) {
        // 处理下数据格式，因为存储的是json数组
        $content = json_decode($data[0]['settings_content'], true);

        $model = xphp_three_powers() ? 'three' : 'normal';
        if (empty($content[$model])) {
            return [3, 0];
        }
        $data = $content[$model][$type] ?? [];
        if (
            empty($data) ||
            $data['strategy_status'] == xphp_get_config('app', 'FLAG')['UNSET'] ||
            $data['strategy_type'] ==
            xphp_get_config('system', 'RESERVED_TYPE')['SYSTEM_RESERVED_STRATEGY_TYPE_PERMANENT']
        ) {
            return [3, 0]; // 永久
        } else {
            // 类型和数量 1按个数 2按天数
            // 默认30
            return [$data['strategy_type'], empty($data['number']) ? 30 : $data['number']];
        }
    }
    // 默认永久
    return [3, 0];
}

/**
 *  获取 文件夹下面的所有文件的列表
 * @param string $directory        根目录
 * @param string $excludeDirectory 不需要查找的目录
 * @param string $extension        查找的文件后缀
 * @return array
 */
function xphp_get_files(string $directory, $excludeDirectory = [], $extension = 'php'): array
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            function ($current, $key, $iterator) use ($excludeDirectory) {
                $currentPath = $current->getRealPath();
                foreach ($excludeDirectory as $excludeDir) {
                    if (strpos($currentPath, $excludeDir) !== false) {
                        return false;
                    }
                }
                return true;
            }
        ),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $phpFiles = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && (($extension && $file->getExtension() === $extension) || empty($extension))) {
            $phpFiles[] = $file->getPathname();
        }
    }
    return $phpFiles;
}

/**
 * 二维码生成类
 * @param string  $url     url地址
 * @param boolean $outfile 是否保存为图片
 * @return string|base64
 */
function xphp_qrcode($url = '', $outfile = false)
{

    include_once(API_PATH . 'extend/phpqrcode/phpqrcode.php');
    if ($outfile) {
        $path = DATA_PATH . '/qrcode/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        QRcode::png($url, $path . $outfile);
        return './web_ng/api/data/qrcode/' . $outfile;
    }
    return QRcode::png($url);
}

/**
 * 载入微信类
 * @param string $type  类名
 * @param array  $cache 配置信息
 * @return object
 */
function xphp_wechat($type = '', $cache = [])
{
    $options = array (
        'appid' => $cache ['appid'], // 填写高级调用功能的app id
        'appsecret' => $cache ['appsecret'], // 填写高级调用功能的密钥
    );

    include_once(API_PATH . 'extend/wechat-php-sdk/include.php');
    $wechat = &\Wechat\Loader::get_instance($type, $options);
    return $wechat;
}

/**
 * 发送模板消息
 *
 * @param array  $openid     接收用户openid数组
 * @param string $templateid 模板id
 * @param array  $info       模板信息
 * @param string $url        跳转url
 * @param array  $config     微信配置
 * @param int    $wechatmode 发送模式 1 系统中转 2自定义
 * @return bool
 */
function xphp_send_wechat_template($openid = [], $templateid = '', $info = [], $url = '', $config = [], $wechatmode = 1)
{
    if (!$templateid || empty($openid)) {
        return false;
    }
    if (empty($config)) {
        $config = xphp_get_config('notice', 'WECHAT_CONFIG');
    }

    $return = false;
    $wechat = xphp_wechat('Receive', $config);
    // 批量发送消息
    foreach ($openid as $item) {
        $data = [];
        $data ['touser'] = $item;
        $data ['template_id'] = $templateid;
        $data ['data'] = $info;
        $data ['url'] = $url;
        if ($wechatmode == 1) {
            // 中转服务
            $return = xphp_send_wechat_transfer($data, $config);
        } else {
            $return = $wechat->sendTemplateMessage($data);
        }
    }
    return $return;
}

/**
 * 中转服务
 * @param array $data   发送的数据包
 * @param array $config 微信配置
 * @return boolean
 */
function xphp_send_wechat_transfer(array $data, array $config): bool
{
    $mbResult = (new \xphp\db\Op())->mbPFMsg('PT_LICENSE_OP_QUERY_THUMBPRINT', '', true);
    $datas = [
        'data' => $data,
        'config' => $config,
        'thumb' => $mbResult['msg']['thumbprint'] ?? '',
        'x-api-version' => '1.0-rev0',
        'op' => 'wechat'
    ];
    $url = xphp_get_config('notice', 'WECHAT_TRANSFER_URL');
    $url = $url . '/api/v1/system/wechat_transfer?' . http_build_query($datas);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datas));
    // 设置 POST 数据为 JSON 格式
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datas));
    // 设置 Content-Type 头
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($datas))
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // 忽略 SSL 证书验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    curl_close($ch);

    $return = json_decode($response, true);

    return $return['errcode'] == 0;
}

/**
 * 发送企业微信通知
 * @param array $config  配置
 * @param array $message 信息
 * @param int   $istest  是否测试
 * @return bool
 */
function xphp_send_wework_api($config = [], $message = [], $istest = 0): bool
{

    if (empty($config)) {
        /*$config = [
            // 企业的id，在管理端->"我的企业" 可以看到
            'CORP_ID'               => 'ww37ae916bc52a725d',
            // 某个自建应用的id及secret, 在管理端 -> 企业应用 -> 自建应用, 点进相应应用可以看到
            'APP_ID'                => 1000002,
            'APP_SECRET'            => 'T0cToNVS4SPQM4wKhnTEWOrriy9zLdXorS_WpuxdYoU',
        ];*/
        return false;
        die('please put config.');
    }

    $sessionkey = md5(json_encode($config));
    $cache = xphp_get_cache($sessionkey);
    if (!empty($cache)) {
        $sessionvalue = json_decode($cache, true);
    }
    if (!empty($sessionvalue) && $sessionvalue['expires_in'] + $sessionvalue['cache_time'] > time()) {
        // 存在缓存并且未过期
        $accesstoken = $sessionvalue['access_token'];
    } else {
        // 获取 accesstoken
        $corpId = $config['CORP_ID'];  // 企业ID
        $corpSecret = $config['APP_SECRET'];  // 应用的凭证密钥

        $accessTokenUrl = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$corpId}&corpsecret={$corpSecret}";

        $response = file_get_contents($accessTokenUrl);
        $result = json_decode($response, true);
        if ($result['access_token']) {
            // 缓存下token
            $result['cache_time'] = time();
            xphp_set_cache($sessionkey, json_encode($result));

            $accesstoken = $result['access_token'];
        } else {
            return false;
            die('Failed to get access_token.');
        }
    }

    if ($istest) {
        // 只是获取accesstoken 测试
        return true;
    }

    $agentId = $config['APP_ID'];  // 应用ID

    $sendMessageUrl = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$accesstoken}";

    $message = [
        'touser' => '@all',
        'msgtype' => 'textcard',
        'agentid' => $agentId,
        'textcard' => array(
            'title' => $message['title'] ?? xphp_get_lang('WEB_UTILS_NOTICE'),
            'description' => $message['content'] ?? xphp_get_lang('WEB_UTILS_CONTENT'),
            'url' => $message['url'] ?? 'https://www.vinchin.com',  // 在卡片底部显示的链接
            'btntxt' => xphp_get_lang('WEB_UTILS_DETAILS'),  // 在卡片底部显示的按钮文字
        )
    ];

    $data = json_encode($message);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sendMessageUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if ($result['errcode'] === 0) {
        return true;
       // echo Xphp::$lang['WEB_UTILS_SEND_INFO_SUCCESS'];
    } else {
        // file_put_contents(ROOT_PATH . 'log.txt', $result['errmsg'] . date('Y-m-d H:i') . PHP_EOL, FILE_APPEND);
        return false;
       // echo Xphp::$lang['WEB_UTILS_SEND_INFO_ERROR'] . $result['errmsg'];
    }
}

/**
 * 生成随机验证码
 * @param int $length 验证码长度
 * @return string
 */
function xphp_random_code($length = 4)
{
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        // 生成一个随机字符
        $char = chr(mt_rand(48, 57)); // 数字0-9
        if (mt_rand(0, 1) === 1) {
            $char = chr(mt_rand(65, 90)); // 大写字母A-Z
        }
        $code .= $char;
    }
    return $code;
}

/**
* 获取缓存 原则，先判断是否存在memchached服务，存在，则取，否则就取 session的缓存
 * @param string  $key  缓存的key
 * @param boolean $flag 是否共享
 * @return string|array|number|*
 */
function xphp_get_cache(string $key, $flag = false)
{
    $memcahced = setMemcached();
    if (empty($memcahced)) {
        // 未能初始化memchached 使用默认的缓存
        return getCache($key);
    }
    // 因为memchached是共享的，所以需要区分客户端来源
    $authkey = $flag ? '' : md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']) . '_';
    //return $memcahced->get($authkey . $key);
    if (!class_exists(xphp\Memcacheds::class)) {
        include_once XPHP_PATH . 'libs/Memcacheds.php';
    }
    $cache = new \xphp\Memcacheds($memcahced);
    return $cache->get($authkey . $key);
}

/**
 * 设置缓存 原则，先判断是否存在memchached服务
 * @param string  $key    缓存的key
 * @param  $value  缓存的值
 * @param boolean $flag   是否共享
 * @param int     $expire 过期时间，对memcached才有效
 * @return boolean
 */
function xphp_set_cache(string $key, $value, $flag = false, $expire = 3600)
{
    $memcahced = setMemcached();
    if (empty($memcahced)) {
        // 未能初始化memchached 使用默认的缓存
        return setCache($key, $value);
    }
    // 因为memchached是共享的，所以需要区分客户端来源
    $authkey = $flag ? '' : md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']) . '_';
    //return $memcahced->set($authkey . $key, $value, $expire);
    if (!class_exists(xphp\Memcacheds::class)) {
        include_once XPHP_PATH . 'libs/Memcacheds.php';
    }
    $cache = new \xphp\Memcacheds($memcahced);

    return $cache->set($authkey . $key, $value, $expire);
}

/**
 * 比较两个时间，计算持续时间
 * @param int $timestampStart 开始时间时间戳
 * @param int $timestampEnd   结束时间时间戳
 * @return string
 */
function xphp_format_duration(int $timestampStart, int $timestampEnd): string
{
    // 创建 DateTime 对象
    $start = new DateTime("@$timestampStart");
    $end = new DateTime("@$timestampEnd");

    // 计算间隔
    $interval = $start->diff($end);

    // 获取天、小时、分钟和秒
    $days = $interval->days;
    $hours = $interval->h;
    $minutes = $interval->i;
    $seconds = $interval->s;

    // 如果结束时间早于开始时间，调整输出
    if ($timestampEnd < $timestampStart) {
        $days = -$days;
        $hours = -$hours;
        $minutes = -$minutes;
        $seconds = -$seconds;
    }
    $day = xphp_get_lang('WEB_UTILS_DAY');
    $hour = xphp_get_lang('WEB_UTILS_HOUR');
    $minute = xphp_get_lang('WEB_UTILS_MINUTE');
    $second = xphp_get_lang('WEB_UTILS_SECOND');
    // 返回格式化的字符串
    return sprintf("%d{$day}%02d{$hour}%02d{$minute}%02d{$second}", $days, $hours, $minutes, $seconds);
}

/**
* 提供一个以秒为单位的时间戳，返回 xx小时xx分xx秒
 * @param int $seconds 秒
 * @return string
 */
function xphp_formatSeconds(int $seconds)
{
    $hours = intdiv($seconds, 3600);
    $remainingSeconds = $seconds % 3600;
    $minutes = intdiv($remainingSeconds, 60);
    $secs = $remainingSeconds % 60;

    $result = '';
    if ($hours > 0) {
        $result .= $hours . xphp_get_lang('WEB_UTILS_HOUR');
    }
    if ($minutes > 0 || $hours > 0) {
        $result .= $minutes . xphp_get_lang('WEB_UTILS_MINUTE');
    }
    $result .= $secs . xphp_get_lang('WEB_UTILS_SECOND');

    return $result;
}

/**
 * 根据传递的 echarts 配置，动态使用 Node 生成对应的图片
 * @param array  $options echarts 的配置项
 * @param string $output  保存文件名（不含后缀），为空则自动生成
 * @return array ['code' => 0 成功, 'msg' => 文件路径 或 错误信息]
 */
function xphp_makepicByEchars(array $options, string $output = ''): array
{
    if (empty($output)) {
        $output = 'chart_' . bin2hex(random_bytes(8)); // 更安全的唯一ID
    }

    $vendor = xphp_get_config('app', 'SYSTEM_INFO')['vendor'];
    $basePath =  "/usr/share/nginx/{$vendor}/echarts-render";
    if (!is_dir($basePath)) {
        if (!mkdir($basePath, 0755, true) && !is_dir($basePath)) {
            return ['code' => -1, 'msg' => "can not make dir : {$basePath}"];
        }
    }

    $savePath = DATA_PATH . 'echarts-pic/';
    if (!is_dir($savePath)) {
        if (!mkdir($savePath, 0755, true) && !is_dir($savePath)) {
            return ['code' => -1, 'msg' => "can not make dir : {$savePath}"];
        }
    }
    $configFile = $savePath . "{$output}.json";
    $imgFile    = $basePath . "{$output}.png";

    // 保存配置
    try {
        $json = json_encode($options, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        file_put_contents($configFile, $json);
    } catch (\Exception $e) {
        return ['code' => -2, 'msg' => 'JSON code fail: ' . $e->getMessage()];
    }

    // 检查 Node 脚本是否存在
    $scripts = $basePath . '/render-echart.js';
    if (!file_exists($scripts)) {
        unlink($configFile);
        return ['code' => -3, 'msg' => 'scripts missed : ' . $scripts];
    }

    // 执行命令
    $cmd = sprintf(
        'cd %s && /usr/bin/node %s %s %s 2>&1',
        escapeshellarg(__DIR__),
        escapeshellarg($scripts),
        escapeshellarg($configFile),
        escapeshellarg($imgFile)
    );

    exec($cmd, $execOutput, $returnCode);

    // 清理临时文件
    @unlink($configFile);

    if ($returnCode === 0 && file_exists($imgFile)) {
        return ['code' => 0, 'msg' => $imgFile];
    } else {
        $errorMsg = implode("\n", $execOutput) ?: 'exec fail（please check Node vir or sudo auth）';
        return ['code' => $returnCode, 'msg' => $errorMsg];
    }
}
