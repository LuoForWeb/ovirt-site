/**
 * jQuery Form Validator 组件
 * 提供灵活的表单验证和输入限制功能
 */
(function ($, LANG) {
    // 默认错误消息
    const defaultMessages = {
        required: LANG.UI_PUBLIC_FIELD_REQUIRED,
        email: LANG.UI_VALIDATE_EMAIL,
        ipv4: LANG.UI_VALIDATE_IPV4,
        ipv6: LANG.UI_VALIDATE_IPV6,
        netmask_v4: LANG.UI_VALIDATE_IPV4_NETMASK,
        netmask_v6: LANG.UI_VALIDATE_IPV4_SUBNET_PREFIX_LENGTH,
        gateway_v4: LANG.UI_VALIDATE_GATEWAY,
        gateway_v6: LANG.UI_VALIDATE_GATEWAY,
        minlength: LANG.UI_VALIDATE_MIN_LENGTH,
        maxlength: LANG.UI_VALIDATE_MAX_LENGTH,
        rangelength: LANG.UI_VALIDATE_RANGE_LENGTH,
        min: LANG.UI_VALIDATE_MIN_NUMBER,
        max: LANG.UI_VALIDATE_MAX_NUMBER,
        range: LANG.UI_VALIDATE_RANGE_NUMBER,
        no_whitespace: LANG.UI_VALIDATE_LIMIT_NO_WHITE_SPACE,
        no_chinese: LANG.UI_VALIDATE_LIMIT_NO_CHINESE,
        no_special_chars: LANG.UI_VALIDATE_LIMIT_NO_SPECIAL_CHARS,
        digits_only: LANG.UI_VALIDATE_LIMIT_ONLY_DIGITS,
        letters_only: LANG.UI_VALIDATE_LIMIT_ONLY_LETTERS,
    };

    // 默认配置
    const defaultOptions = {
        validationPrefix: 'validate-', // 验证前缀
        restrictionPrefix: 'restrict-', // 限制前缀
        errorClass: 'has-error', // 错误类名
        successClass: 'has-success', // 成功类名
        errorIcon: '<i class="validation-icon" data-toggle="tooltip" data-placement="top" data-original-title=""></i>', // 错误图标
        messages: {}, // 自定义错误消息
        onSuccess: function (element) {
        }, // 验证成功回调
        onError: function (element, message) {
        }, // 验证失败回调
        onValidated: function (isValid) {
        }, // 整体验证完成回调
        onRestriction: function (element, value) {
        } // 输入限制回调
    };

    // 验证方法集合
    const validators = {
        // 必填验证
        required: function (value, element) {
            if (element.type === 'checkbox') {
                return element.checked;
            } else if (element.type === 'radio') {
                return $('input[name="' + element.name + '"]:checked').length > 0;
            }
            return $.trim(value).length > 0;
        },

        // 邮箱验证
        email: function (value) {
            const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            return regex.test(value);
        },

        // IPv4地址验证
        ipv4: function (value) {
            const regex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            return regex.test(value);
        },

        // IPv6地址验证
        ipv6: function (value) {
            const regex = /^(([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]+|::(ffff(:0{1,4})?:)?((25[0-5]|(2[0-4]|1?[0-9])?[0-9])\.){3}(25[0-5]|(2[0-4]|1?[0-9])?[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1?[0-9])?[0-9])\.){3}(25[0-5]|(2[0-4]|1?[0-9])?[0-9]))$/;
            return regex.test(value);
        },

        // IPv4子网掩码验证
        netmask_v4: function (value) {
            // 1. 检查 CIDR 格式（如 24）
            const cidrPattern = /^([0-9]|[12][0-9]|3[0-2])$/;
            if (cidrPattern.test(value)) {
                return true;
            }

            // 2. 检查点分十进制格式（如 255.255.192.0）
            const decimalMaskPattern = /^(255|254|252|248|240|224|192|128|0)\.(255|254|252|248|240|224|192|128|0)\.(255|254|252|248|240|224|192|128|0)\.(255|254|252|248|240|224|192|128|0)$/;
            if (!decimalMaskPattern.test(value)) {
                return false;
            }

            // 3. 确保从左到右非递增（掩码的 1 必须连续）
            const octets = value.split('.').map(Number);
            for (let i = 1; i < 4; i++) {
                if (octets[i] > octets[i - 1]) {
                    return false; // 后面的数字不能比前面的大
                }
            }

            // 4. 检查掩码的二进制形式是否有效（连续的 1 后接连续的 0）
            let binaryStr = '';
            for (const octet of octets) {
                binaryStr += octet.toString(2).padStart(8, '0');
            }
            return /^1*0*$/.test(binaryStr);
        },

        // IPv6前缀长度验证
        netmask_v6: function (value) {
            // Validate CIDR notation
            const cidrPattern = /^([0-9]|[1-9]\d|1[0-1]\d|12[0-8])$/;
            if (cidrPattern.test(value)) {
                return true;
            }
            return this.ipv6(value);
        },

        // 默认网关验证（与IP验证相同）
        gateway_v4: function (value) {
            if (!value.length) {
                return true;
            }
            return this.ipv4(value);
        },
        gateway_v6: function (value) {
            if (!value.length) {
                return true;
            }
            return this.ipv6(value);
        },

        // 最小长度验证
        minlength: function (value, param) {
            return value.length >= param;
        },

        // 最大长度验证
        maxlength: function (value, param) {
            return value.length <= param;
        },

        // 长度范围验证
        rangelength: function (value, param) {
            const length = value.length;
            return length >= param[0] && length <= param[1];
        },

        // 最小值验证
        min: function (value, param) {
            const num = parseFloat(value);
            return !isNaN(num) && num >= param;
        },

        // 最大值验证
        max: function (value, param) {
            const num = parseFloat(value);
            return !isNaN(num) && num <= param;
        },

        // 数值范围验证
        range: function (value, param) {
            const num = parseFloat(value);
            return !isNaN(num) && num >= param[0] && num <= param[1];
        }
    };

    // 输入限制方法集合
    const restrictions = {
        // 不允许空白字符
        no_whitespace: {
            validate: function (value) {
                return !/\s/.test(value);
            },
            process: function (value) {
                return value.replace(/\s/g, '');
            }
        },

        // 不允许中文
        no_chinese: {
            validate: function (value) {
                return !/[\u4e00-\u9fa5]/.test(value);
            },
            process: function (value) {
                return value.replace(/[\u4e00-\u9fa5]/g, '');
            }
        },

        // 不允许特殊字符
        no_special_chars: {
            validate: function (value) {
                return /^[a-zA-Z0-9]+$/.test(value);
            },
            process: function (value) {
                return value.replace(/[^a-zA-Z0-9]/g, '');
            }
        },

        // 只允许数字
        digits_only: {
            validate: function (value) {
                return /^\d+$/.test(value);
            },
            process: function (value) {
                return value.replace(/\D/g, '');
            }
        },

        // 只允许字母
        letters_only: {
            validate: function (value) {
                return /^[a-zA-Z]+$/.test(value);
            },
            process: function (value) {
                return value.replace(/[^a-zA-Z]/g, '');
            }
        },

        // 最大值限制
        max: {
            validate: function (value, param) {
                const num = parseFloat(value);
                return isNaN(num) || num <= param;
            },
            process: function (value, param) {
                const num = parseFloat(value);
                return isNaN(num) ? '' : Math.min(num, param).toString();
            }
        },

        // 最小值限制
        min: {
            validate: function (value, param) {
                const num = parseFloat(value);
                return isNaN(num) || num >= param;
            },
            process: function (value, param) {
                const num = parseFloat(value);
                return isNaN(num) ? '' : Math.max(num, param).toString();
            }
        },

        // 最大长度限制
        maxlength: {
            validate: function (value, param) {
                return value.length <= param;
            },
            process: function (value, param) {
                return value.substring(0, param);
            }
        }
    };

    // 添加验证方法
    $.fn.addFormValidator = function (name, method, message) {
        validators[name] = method;
        if (message) {
            defaultMessages[name] = message;
        }
        return this;
    };

    // 添加输入限制
    $.fn.addFormRestriction = function (name, method, message) {
        // 如果传入的是完整的限制对象（包含validate和process方法）
        if (typeof method === 'object' && method.validate && method.process) {
            restrictions[name] = method;
        } else if (typeof method === 'function') {  // 如果只传入验证函数，自动创建process方法（默认不处理）
            restrictions[name] = {
                validate: method,
                process: function (value) {
                    return value;
                }
            };
        } else if (typeof method === 'object') {  // 如果传入的是包含validate和process的对象（但没有直接检查属性）
            restrictions[name] = {
                validate: method.validate || function () {
                    return true;
                },
                process: method.process || function (value) {
                    return value;
                }
            };
        }

        // 设置自定义错误消息
        if (message) {
            defaultMessages[name] = message;
        }

        return this;
    };

    // 添加静态验证方法到jQuery对象上
    $.formValidateElement = function(element, options) {
        const settings = $.extend({}, defaultOptions, options || {});
        const messages = $.extend({}, defaultMessages, settings.messages || {});

        // 验证单个元素
        function validateElement(el) {
            const $el = $(el);
            let isValid = true;
            let errorMessage = '';

            // 获取所有验证规则
            const validationRules = getRules(el, settings.validationPrefix, validators);
            if (Object.keys(validationRules).length === 0) {
                return true;
            }

            // 执行验证规则
            Object.keys(validationRules).forEach(ruleName => {
                if (!isValid) return;

                const param = validationRules[ruleName];
                const value = $el.is('input, textarea, select') ? $el.val() : $el.text();

                if (!validators[ruleName].call(validators, value, param, el)) {
                    isValid = false;
                    errorMessage = formatMessage(messages[ruleName], param);
                }
            });

            // 更新UI
            updateElementUI($el, isValid, errorMessage, validationRules);

            return isValid;
        }

        // 获取规则
        function getRules(element, prefix, ruleSet) {
            const rules = {};

            // 直接获取元素的 attributes
            $.each(element.attributes, function () {
                if (this.name.startsWith('data-' + prefix)) {
                    const fullName = this.name.substring(5); // 去掉 "data-" 前缀
                    const ruleName = fullName.substring(prefix.length);

                    // 只添加存在的规则
                    if (ruleSet[ruleName]) {
                        let param = this.value;

                        // 处理数组参数
                        if (param && param.startsWith('[') && param.endsWith(']')) {
                            try {
                                param = JSON.parse(param);
                            } catch (e) {
                                param = param.slice(1, -1).split(',');
                            }
                        }

                        rules[ruleName] = param === '' ? true : param;
                    }
                }
            });

            return rules;
        }

        // 格式化消息
        function formatMessage(message, param) {
            if (!message) return '';

            if (Array.isArray(param)) {
                param.forEach((p, i) => {
                    message = message.replace(new RegExp('\\{' + i + '\\}', 'g'), p);
                });
            } else if (param !== true) {
                message = message.replace('{0}', param);
            }

            return message;
        }

        // 更新元素UI状态
        function updateElementUI($element, isValid, errorMessage, validationRules) {
            // 移除之前的图标
            $element.siblings('.validation-icon').remove();

            // 添加或移除类
            let formGroup = $element.closest('.form-group');
            formGroup.removeClass(settings.errorClass + ' ' + settings.successClass);
            formGroup.addClass(isValid ? settings.successClass : settings.errorClass);

            // 添加验证图标
            if ($element.is('input, textarea, select')) {
                let icon = $element.parent('.input-icon').children('i').show();
                const value = $element.val();
                if (!isValid) {
                    icon.removeClass('fa-check').addClass("fa-warning");
                    icon.attr("data-original-title", errorMessage).tooltip({'container': 'body'}).show();
                } else {
                    icon.removeClass('fa-warning').addClass("fa-check");
                    icon.attr("data-original-title", '').tooltip({'container': 'body'}).show();
                    if (!value.length && typeof validationRules['required'] === 'undefined') {
                        formGroup.removeClass(settings.errorClass + ' ' + settings.successClass);
                        icon.hide();
                    }
                }
            }

            // 调用回调
            if (isValid) {
                settings.onSuccess($element[0]);
            } else {
                settings.onError($element[0], errorMessage);
            }
        }

        return validateElement(element);
    };

    // 主验证方法
    $.fn.formValidate = function (options) {
        const settings = $.extend({}, defaultOptions, options);
        const messages = $.extend({}, defaultMessages, settings.messages);

        // 验证单个元素
        function validateElement(element) {
            return $.formValidateElement(element, {
                validationPrefix: settings.validationPrefix,
                restrictionPrefix: settings.restrictionPrefix,
                messages: messages
            });
        }

        // 处理输入限制
        function processRestrictions(element, value) {
            const $element = $(element);
            let newValue = value;
            let processed = false;

            // 获取所有限制规则
            const restrictionRules = getRules(element, settings.restrictionPrefix, restrictions);

            // 执行限制规则
            Object.keys(restrictionRules).forEach(ruleName => {
                const restriction = restrictions[ruleName];
                const param = restrictionRules[ruleName];

                if (!restriction.validate.call(restrictions, newValue, param, element)) {
                    newValue = restriction.process.call(restrictions, newValue, param, element);
                    processed = true;
                }
            });

            if (processed && newValue !== value) {
                $element.val(newValue);
                settings.onRestriction(element, newValue);
            }

            return newValue;
        }

        // 获取规则
        function getRules(element, prefix, ruleSet) {
            const rules = {};

            // 直接获取元素的 attributes
            $.each(element.attributes, function () {
                if (this.name.startsWith('data-' + prefix)) {
                    const fullName = this.name.substring(5); // 去掉 "data-" 前缀
                    const ruleName = fullName.substring(prefix.length);

                    // 只添加存在的规则
                    if (ruleSet[ruleName]) {
                        let param = this.value;

                        // 处理数组参数
                        if (param && param.startsWith('[') && param.endsWith(']')) {
                            try {
                                param = JSON.parse(param);
                            } catch (e) {
                                param = param.slice(1, -1).split(',');
                            }
                        }

                        rules[ruleName] = param === '' ? true : param;
                    }
                }
            });

            return rules;
        }

        // 绑定事件
        // 输入限制处理
        this.on('input', 'input, textarea', function () {
            const $this = $(this);
            const value = $this.val();
            const newValue = processRestrictions(this, value);

            if (newValue !== value) {
                $this.val(newValue);
            }
        });

        // 实时验证
        this.on('change blur', 'input, textarea, select', function () {
            validateElement(this);
        });

        // 验证单个元素
        this.validateElement = function (element) {
            return validateElement(element);
        };

        // 验证所有元素
        this.validateAll = function () {
            let allValid = true;
            const elements = this.find('input, textarea, select');

            elements.each(function () {
                const isValid = validateElement(this);
                if (!isValid) {
                    allValid = false;
                }
            });

            settings.onValidated(allValid);
            return allValid;
        };

        return this;
    };
})(jQuery, LANG);