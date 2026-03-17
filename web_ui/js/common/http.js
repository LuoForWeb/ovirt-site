/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-27 17:06:09
 * @Description: 二次封装的axios
 * @version: 1.0
 */
let REQUEST_URL = '', REQUEST_PARAMS = {}, CSRF_TOKEN = '', AUTH_TOKEN = '';

const service = axios.create({
    baseURL: '/api/v2/',
    timeout: 300000
});

service.defaults.retry = 3; // 超时重连次数
service.defaults.retryDelay = 1000; // 超时重连间隔时间

// 统一请求拦截
service.interceptors.request.use(config => {
    config.headers = {
        'Authorization': AUTH_TOKEN ? AUTH_TOKEN : window.localStorage.getItem('access_token'),
        'X-Csrf-Token': CSRF_TOKEN ? CSRF_TOKEN : window.localStorage.getItem('csrf_token'),
        'x-api-version': '1.0-rev0'
    };

    switch (config.method) {
    case 'get':
        if (!config.params) {
            config.params = {};
        }
        break;
    case 'post':
        if (config.isUpload) {
            config.headers.contentType = 'multipart/form-data'; // 根据参数是否启用form-data方式
        } else {
            config.headers.contentType = 'application/json';
        }
        break;
    default:
        break;
    }

    return config;
}, error => {
    return Promise.reject(error);
});

/**
 * 统一处理 error
 * @param status 状态码
 */
const errorHandle = (status) => {

    switch (status) {
    case 401: // 401: 未登录状态，跳转登录页
        break;
    case 403: // 403 token过期 清除token并跳转登录页 localStorage.removeItem('token');
        break;
    case 404: // 404 请求不存在
        break;
    default:
        break;
    }
};

// 统一响应拦截
service.interceptors.response.use(
    // 请求成功
    res => {
        if (res.data.success && res.data.code === 0) {
            // 设置接口认证信息
            // eslint-disable-next-line no-underscore-dangle
            CSRF_TOKEN = res.headers.__token__;
            window.localStorage.setItem('csrf_token', CSRF_TOKEN);

            // 认证成功后保存用户登录信息到localstorage
            if (REQUEST_URL === 'login' && res.data.message === 1) {
                // 设置身份认证信息
                AUTH_TOKEN = res.data.data.access_token;
                window.localStorage.setItem('access_token', AUTH_TOKEN);
                window.localStorage.setItem('username', REQUEST_PARAMS.username);
                window.localStorage.setItem('password', REQUEST_PARAMS.password);
            }
        }

        // 授权超时需要重新认证
        if (!res.data.success && res.data.code === 910086) {
            let username = window.localStorage.getItem('username');
            let password = window.localStorage.getItem('password');

            // eslint-disable-next-line no-use-before-define, new-cap, no-unused-vars
            axiosPost('login', {username: username, password: password}).then(result => {
                // console.log(result, '重新登录');
            });
        }

        if (!res.data.success && res.data.code === 910087) {
            let config = res.config;

            if (!config || !config.retry) {
                return Promise.reject('token验证失败');
            }

            config.retryCount = config.retryCount || 0;

            if (config.retryCount >= config.retry) {
                // Reject with the error
                return Promise.reject(`超过最大重连限制次数${config.retryCount}次`);
            }

            config.retryCount += 1;

            let backoff = new Promise(function(resolve) {
                setTimeout(function() {
                    resolve();
                }, config.retryDelay || 1);
            });

            return backoff.then(function() {
                return service(config);
            });
        }

        return res;
    },

    // 请求失败
    error => {
        const { response } = error;

        if (response) {
            // 查询升级包状态和升级日志接口的错误特殊处理：后台在替换PHP文件和UI文件时会不可避免的服务器出错，此时要保证接口正常轮询，因此这两个接口如果出错要能继续请求
            if (CONF.UPDATE_API_ERROR_CODES.indexOf(response.status) > -1 && (response.request.responseURL.includes(CONF.KEEP_REQUEST_URL.GET_UPGRADE_STATUS) || response.request.responseURL.includes(CONF.KEEP_REQUEST_URL.GET_UPGRADE_LOG))) {
                let config = response.config;

                let backoff = new Promise(function(resolve) {
                    setTimeout(function() {
                        resolve();
                    }, config.retryDelay || 1);
                });

                return backoff.then(function() {
                    return service(config);
                });
            }

            errorHandle(response.status, response.data.message);

            return Promise.reject(response);
        }
    }
);

/**
 * 统一处理请求参数
 * @param {*} params PARAMS
 * @param {*} isGet 是否为GET请求
 * @returns {*&{sign: 加密后str, timestamp: number}}
 */
const requestParamsHandler = (params, isGet) => {
    // eslint-disable-next-line no-undef
    let newParams = recursiveCloneRequestData(params);

    let timestamp = new Date().getTime(); // 获取当前操作时间戳
    newParams.timestamp = timestamp;
    // eslint-disable-next-line no-undef
    let signParams = recursiveCloneSignData(newParams, isGet);

    let len = 0, keyList = [];

    $.each(signParams, function(key) {
        keyList[len] = key;
        len++;
    });

    keyList.sort();
    let list = {};

    $.each(keyList, function(i, key) {
        list[key] = signParams[key];
    });

    // sign加密消息
    // eslint-disable-next-line no-undef
    let sign = signEncrypt(JSON.stringify(list));

    // eslint-disable-next-line no-undef
    return { ...newParams, timestamp: timestamp, sign: sign };
};

/**
 * 二次封装 axios get 请求
 * @param url url
 * @param params params
 * @returns {Promise<unknown>}
 * @constructor
 */
// eslint-disable-next-line no-unused-vars
const axiosGet = function(url, params) {
    REQUEST_PARAMS = requestParamsHandler(params, true);
    REQUEST_URL = url;

    return new Promise((resolve, reject) => {
        service.get(url, {
            params: REQUEST_PARAMS
        }).then(res => {
            resolve(res.data);
        }).catch(err =>{
            reject(err.data);
        });
    });
};

/**
 * 二次封装 axios post 请求
 * @param url url
 * @param params params
 * @returns {Promise<unknown>}
 * @constructor
 */
const axiosPost = function(url, params) {
    REQUEST_PARAMS = requestParamsHandler(params, false);
    REQUEST_URL = url;

    return new Promise((resolve, reject) => {
        service.post(REQUEST_URL, REQUEST_PARAMS)
            .then(res => {
                resolve(res.data);
            })
            .catch(err =>{
                reject(err.data);
            });
    });
};

/**
 * 二次封装 axios put 请求
 * @param url url
 * @param params params
 * @returns {Promise<unknown>}
 * @constructor
 */
// eslint-disable-next-line no-unused-vars
const axiosPut = function(url, params) {
    REQUEST_PARAMS = requestParamsHandler(params, false);
    REQUEST_URL = url;

    return new Promise((resolve, reject) => {
        service.put(REQUEST_URL, REQUEST_PARAMS)
            .then(res => {
                resolve(res.data);
            })
            .catch(err =>{
                reject(err.data);
            });
    });
};

/**
 * 二次封装 axios delete 请求
 * @param url url
 * @param params params
 * @returns {Promise<unknown>}
 * @constructor
 */
// eslint-disable-next-line no-unused-vars
const axiosDelete = function(url, params) {
    REQUEST_PARAMS = requestParamsHandler(params, false);
    REQUEST_URL = url;

    return new Promise((resolve, reject) => {
        service.delete(REQUEST_URL, {data: REQUEST_PARAMS})
            .then(res => {
                resolve(res.data);
            })
            .catch(err =>{
                reject(err.data);
            });
    });
};

/**
 * 二次封装 axios patch 请求
 * @param {*} url url
 * @param {*} params params
 * @returns
 */
// eslint-disable-next-line no-unused-vars
const axiosPatch = function(url, params) {
    REQUEST_PARAMS = requestParamsHandler(params, false);
    REQUEST_URL = url;

    return new Promise((resolve, reject) => {
        service.patch(REQUEST_URL, {data: REQUEST_PARAMS})
            .then(res => {
                resolve(res.data);
            })
            .catch(err =>{
                reject(err.data);
            });
    });
};
