function Cascader(options) {
    // 默认配置
    options = $.extend({
        container: null,
        data: [],
        selectFn: function () {},
        placeholder: "搜索或点击下拉选择",
        value: "",
        clearable: false //  是否启用清除功能
    }, options);

    // 如果提供了父容器，则挂载
    if (options.container) {
        this.container = $(options.container);
    }

    // 数据源
    this.data = options.data;
    // 选择后的回调函数
    this.selectFn = options.selectFn;
    this.placeholder = options.placeholder;
    this.options = options; // 存储 options

    // dom
    this.$el = undefined;
    // 是否加载初始化数据
    this.initData = false;
    // 是否加载初始化搜索数据
    this.initSearchData = false;
    // 搜索数据label列表
    this.searchLabelArr = [];
    // 搜索数据value列表
    this.searchValueArr = [];
    this.searActiveArr = [];
    // 当前层级
    this.level = 0;
    // 当前数组
    this.curArr = [];
    // 当前选择值
    this.result = { value: "", label: "" };
    // 初始值
    this.value = options.value;

    // 调用初始化方法
    this.init();
}

Cascader.prototype.init = function () {
    this.$el = $('<div class="ui-cascader">' +
                    '<div class="ui-cascader-input">' +
                        '<span class="arrow"></span>' +
                        '<input autocomplete="off" class="searchtxt" type="text" placeholder="' + this.placeholder + '" />' +
                        '<span class="clear-btn hide-clear-btn"><i class="icon-close-small"></i></span>' + // 添加清除按钮
                    '</div>' +
                '</div>');

    if (this.container) {
        this.container.html("");
        this.container.append(this.$el);
    }

    this.input = this.$el.find(".ui-cascader-input");
    this.inputDom = this.input.find("input");
    this.arrow = this.input.find(".arrow");
    this.clearBtn = this.input.find(".clear-btn"); // 获取清除按钮

    // 显示清除按钮的逻辑
    if (this.options.clearable) {
        this.inputDom.on("input", function () {
            if (this.inputDom.val().length > 0) {
                this.clearBtn.removeClass('hide-clear-btn');
            } else {
                this.clearBtn.addClass('hide-clear-btn');
            }
        }.bind(this));

        this.inputDom.on("blur", function () {
            if (this.inputDom.val().length > 0) {
                this.clearBtn.removeClass('hide-clear-btn');
            } else {
                this.clearBtn.addClass('hide-clear-btn');
            }
        }.bind(this));

        // 绑定清除按钮点击事件
        this.clearBtn.on("click", function () {
            this.inputDom.val(""); // 清空输入框内容
            this.result = { value: "", label: "" }; // 重置结果
            this.popClose(); // 关闭下拉菜单
            this.selectFn([]); // 调用选择回调函数，传递空数组
            this.clearBtn.addClass('hide-clear-btn'); // 隐藏清除按钮

            // 重置级联下拉状态
            this.resetCascader();
        }.bind(this));

        // 绑定鼠标悬停事件
        this.input.on("mouseenter", function () {
            if (this.inputDom.val().length > 0) {
                this.clearBtn.removeClass('hide-clear-btn');
            }
        }.bind(this));

        this.input.on("mouseleave", function () {
            if (this.inputDom.val().length > 0) {
                this.clearBtn.addClass('hide-clear-btn');
            }
        }.bind(this));
    }


    this.list = $('<div class="ui-cascader ui-cascader-dropdown panel hid"></div>');
    this.searchedList = $('<div class="ui-cascader ui-cascader-dropdown searchedlist hid"></div>');

    $(document.body).append(this.list);
    $(document.body).append(this.searchedList);

    // input点击 弹出
    this.input.on("click", function () {
        if (this.list.hasClass("hid")) {
            if (!this.initData) {
                this.createUl(this.data);
                this.initData = true;
            }
            if (!this.list.parents("body").length) {
                $(document.body).append(this.list);
                this.list.delegate("li.ui-cascader-menu-item", "click", function (e) {
                    var parent_index = $(e.target).parent().index();
                    var value = $(e.target).attr("data-value");
                    $(e.target).addClass("on").siblings().removeClass("on");
                    this.level = parent_index;
                    this.getArray(this.data, value);
                }.bind(this));
            }
            this.popOpen();
        }
    }.bind(this));

    // input中输入内容时，搜索
    this.inputDom.on("input", function (e) {
        const searchKey = e.target.value.trim();
        if (!this.initSearchData) {
            this.createSearchArr(this.data);
            this.initSearchData = true;
        }
        if (searchKey) {
            if (!this.searchedList.parents("body").length) {
                $(document.body).append(this.searchedList);
                this.searchedList.delegate("li.ui-cascader-menu-item", "click", function (e) {
                    let searStr = $(e.target).text().split("/");
                    let litxt = this.list.find(".ui-cascader-menu").eq(0);
                    let len = searStr.length;

                    litxt.nextAll().remove();

                    this.createSearchdlist(this.data, searStr[0]);

                    for (var i = 1; i < len - 1; i++) {
                        this.createSearchdlist(this.searActiveArr, searStr[i]);
                    }
                    this.list.find(".ui-cascader-menu").each(function (i, item) {
                        var jqElArr = $(item).find("li");

                        this.highlighting(jqElArr, searStr[i]);
                        if (i == len - 1) {
                            this.getValue();
                            this.popClose();
                        }
                    }.bind(this));
                }.bind(this));
            }
            this.popClose();
            this.createSearchBox(searchKey);
        } else {
            this.popClose();
            if (this.list.hasClass("hid")) {
                if (!this.initData) {
                    this.createUl(this.data);
                    this.initData = true;
                }
                this.popOpen();
            }
        }
    }.bind(this));

    // input失去焦点时
    this.inputDom.on("blur", function (e) {
        const val = e.target.value;
        // 如果input中的内容，和选中的结果不一致，则还原为选中的值
        if (val !== this.result.label) {
            this.inputDom.val(this.result.label);

            if (!this.result.label) { // 未选中任何值隐藏 clearBtn
                this.clearBtn.addClass('hide-clear-btn');
            } else {
                this.clearBtn.removeClass('hide-clear-btn');
            }
        }
        // 收起查询
    }.bind(this));

    this.list.delegate("li.ui-cascader-menu-item", "click", function (e) {
        var parent_index = $(e.target).parent().index();
        var value = $(e.target).attr("data-value");
        $(e.target).addClass("on").siblings().removeClass("on");
        this.level = parent_index;
        this.getArray(this.data, value);
    }.bind(this));

    this.searchedList.delegate("li.ui-cascader-menu-item", "click", function (e) {
        let searStr = $(e.target).text().split("/");
        let litxt = this.list.find(".ui-cascader-menu").eq(0);
        let len = searStr.length;

        litxt.nextAll().remove();

        this.createSearchdlist(this.data, searStr[0]);

        for (var i = 1; i < len - 1; i++) {
            this.createSearchdlist(this.searActiveArr, searStr[i]);
        }
        this.list.find(".ui-cascader-menu").each(function (i, item) {
            var jqElArr = $(item).find("li");

            this.highlighting(jqElArr, searStr[i]);
            if (i == len - 1) {
                this.getValue();
                this.popClose();
            }
        }.bind(this));
    }.bind(this));

    $("html").on("click", this.htmlClickHandler.bind(this));

    this.setInitVal();

    return this.$el;
};

Cascader.prototype.popOpen = function (el) {
    // 修改箭头样式
    this.arrow.addClass("on");

    const windowHeight = $(window).height(); // 屏幕高度
    const comHeight = this.$el.height(); // 组件高度（input)
    const comTop = this.input.offset().top + 5; // 组件（input）到顶部到距离（包括滚动条滚动到部分）
    const comLeft = this.input.offset().left;
    const scrollBarHeight = $(document).scrollTop(); // 滚动条到高度（即页面滚动进去到距离）
    let height = 0; // 弹出框到高度

    const zIndex = this.getMaxZIndex();

    if (el) {
        height = $(el).height(); // 弹出框的高度
        if (windowHeight - (comTop - scrollBarHeight + comHeight + height) < 10) {
            $(el).css("top", `${comTop - height - 5}px`);
            $(el).css("left", `${comLeft}px`);
        } else {
            $(el).css("top", `${comTop + comHeight}px`);
            $(el).css("left", `${comLeft}px`);
        }
        $(el).css("zIndex", zIndex + 1);
        el.removeClass("hid");
    } else {
        height = this.list.height(); // 弹出框的高度
        if (windowHeight - (comTop - scrollBarHeight + comHeight + height) < 10) {
            this.list.css("top", `-${comTop - height - 5}px`);
            this.list.css("left", `${comLeft}px`);
        } else {
            this.list.css("top", `${comTop + comHeight}px`);
            this.list.css("left", `${comLeft}px`);
        }
        this.list.css("zIndex", zIndex + 1);
        this.list.removeClass("hid");
    }
};

Cascader.prototype.popClose = function () {
    if (!this.list.hasClass("hid")) {
        this.list.addClass("hid");
    }

    if (!this.searchedList.hasClass("hid")) {
        this.searchedList.addClass("hid");
    }
    this.arrow.removeClass("on");
};

Cascader.prototype.resetCascader = function () {
    // 重置所有与级联下拉相关的状态变量
    this.initData = false;
    this.initSearchData = false;
    this.searchLabelArr = [];
    this.searchValueArr = [];
    this.searActiveArr = [];
    this.level = 0;
    this.curArr = [];
    this.result = { value: "", label: "" };
    this.value = "";

    // 清空搜索结果列表
    this.searchedList.empty();

    // 清空下拉列表
    this.list.empty();

    // 重置输入框内容
    this.inputDom.val("");

    // 重置箭头样式
    this.arrow.removeClass("on");

    // 隐藏清除按钮
    this.clearBtn.addClass('hide-clear-btn');
};

Cascader.prototype.getArray = function (data, value) {
    for (var i in data) {
        if (data[i].value == value) {
            this.curArr = data[i].children;
            this.createEl();
            break;
        } else {
            this.getArray(data[i].children, value);
        }
    }
};

Cascader.prototype.selJsonToStr = function (arr) {
    let labelArr = [];
    let valueArr = [];
    $.each(arr, function (i, data) {
        labelArr.push(data.label);
        valueArr.push(data.value);
    });
    return {
        label: labelArr.join(" / "),
        value: valueArr.join("."),
    };
};

Cascader.prototype.getValue = function (isClick) {
    isClick = isClick === undefined ? true : isClick;
    let selJson = []; /* 最终选项数组 */
    this.list.find("li.on").each(function (i, data) {
        var label = $(this).attr("data-label"),
            value = $(this).attr("data-value");
        selJson.push({ value: value, label: label });
    });
    var { label, value } = this.selJsonToStr(selJson);

    this.inputDom.val(label);
    this.result = {
        label,
        value,
    };
    if (isClick) {
        this.selectFn(selJson);
        this.list.remove();
        this.searchedList.remove();
    }
};

Cascader.prototype.createUl = function (data) {
    if (!data) {
        return;
    }
    let arr = data;
    let liArr = [];

    let ul = $('<ul class="ui-cascader-menu"></ul>');
    $.each(arr, function (i, data) {
        let menuItemHtml = '';

        if (data.children) {
            menuItemHtml = `<li data-label="${data.label}" data-value="${data.value}" class="ui-cascader-menu-item lastchild">${data.label}<i class="viconfont vicon-gengduo"></i></li>`;
        } else {
            menuItemHtml = `<li data-label="${data.label}" data-value="${data.value}" class="ui-cascader-menu-item">${data.label}</li>`;
        }

        liArr.push(menuItemHtml);
    });
    ul.append(liArr.join(""));
    this.list.append(ul);
};

Cascader.prototype.createEl = function () {
    if (this.curArr) {
        /*  点击非最后一个子级 */
        this.list.find(".ui-cascader-menu").eq(this.level).nextAll().remove();
        this.createUl(this.curArr);
        this.popOpen();
    } else {
        /* 点击最后一个子级 */
        this.list.find(".ui-cascader-menu").eq(this.level).nextAll().remove();

        this.getValue();
        this.popClose();
    }
};

// 获取当前页面上所有z-index
Cascader.prototype.getMaxZIndex = function () {
    var maxZ = Math.max.apply(
        null,
        $.map($("body *"), function (e, n) {
            if ($(e).css("position") != "static")
                return parseInt($(e).css("z-index")) || 0;
        })
    );
    return maxZ;
};

// 如果点击的不是组件范围，则关闭组件
Cascader.prototype.htmlClickHandler = function (e) {
    if (this.list.hasClass("hid") && this.searchedList.hasClass("hid")) return;

    var cascader = $(e.target).parents(".ui-cascader");
    if (cascader.size() == 0) {
        this.popClose();
    }
};

// 生成拉平的搜索结果
Cascader.prototype.createSearchArr = function (data, label) {
    for (var i in data) {
        if (!label) {
            label = "";
        }
        let str = label + "" + data[i].label + "/";
        if (data[i].children) {
            this.curArr = data[i].children;
            this.createSearchArr(this.curArr, str);
        } else {
            str = str.substring(0, str.lastIndexOf("/"));
            this.searchLabelArr.push(str.toLocaleLowerCase());
            this.searchValueArr.push(data[i].value);
        }
    }
};

// 生成搜索结果dom
Cascader.prototype.createSearchBox = function (label) {
    var liArr = [];
    let ul = $('<ul class="ui-cascader-menu"></ul>');
    $.each(this.searchLabelArr, (i, data) => {
        if (data.indexOf(label.toLocaleLowerCase()) != -1) {
            const value = this.searchValueArr[i];
            liArr.push(
                `<li data-value="${value}" class="ui-cascader-menu-item lastchild" title="${data}">${data}</li>`
            );
        }
    });

    if (liArr.length != 0) {
        this.searchedList.empty();

        ul.append(liArr.join(""));
        this.searchedList.append(ul);
        this.popOpen(this.searchedList);
    } else {
        this.searchedList.empty();
        ul.append('<li class="nosearch">无匹配数据</li>');
        this.searchedList.append(ul);
        this.popOpen(this.searchedList);
    }
};

Cascader.prototype.createSearchdlist = function (data, label) {
    for (var i in data) {
        if (data[i].label.toLocaleLowerCase() == label.toLocaleLowerCase()) {
            this.searActiveArr = data[i].children;
        }
    }
    this.createUl(this.searActiveArr);
};

Cascader.prototype.highlighting = function (jqElArr, highStr) {
    jqElArr.each((i, item) => {
        var curtxt = $(item).attr("data-label").toLocaleLowerCase();
        if (highStr.toLocaleLowerCase() == curtxt) {
            var selectedItem = $(item);
            $(item).addClass("on").siblings().removeClass("on");
            this.scrollToOpened(selectedItem);
        }
    });
};

Cascader.prototype.scrollToOpened = function (selectedItem) {
    var listUl = selectedItem.parents("ul");
    if (selectedItem.size() > 0) {
        var scrollTop = listUl.scrollTop(),
            top = selectedItem.position().top + scrollTop;
        if (scrollTop < top) {
            top = top - (listUl.height() - selectedItem.height()) / 2;
            listUl.scrollTop(top);
        }
    }
};

Cascader.prototype.getLabelByVal = function (val, data) {
    return data.find((currentLv) => currentLv.value === val);
};

// 如果传入初值，则设置初始状态
Cascader.prototype.setInitVal = function () {
    if (this.value) {
        let valArr = this.value.split("/");
        let ret = [];
        let searStr = [];
        for (i = 0; i < valArr.length; i++) {
            if (ret.length) {
                const temp = this.getLabelByVal(
                    valArr[i],
                    ret[ret.length - 1].children
                );
                ret.push(temp);
                searStr.push(temp.label);
            } else {
                const temp = this.getLabelByVal(valArr[i], this.data);
                ret.push(temp);
                searStr.push(temp.label);
            }
        }
        let litxt = this.list.find(".ui-cascader-menu").eq(0);
        let len = searStr.length;

        litxt.nextAll().remove();

        if (!this.initData) {
            this.createUl(this.data);
            this.initData = true;
        }

        this.createSearchdlist(this.data, searStr[0]);

        for (var i = 1; i < len - 1; i++) {
            this.createSearchdlist(this.searActiveArr, searStr[i]);
        }
        this.list.find(".ui-cascader-menu").each((i, item) => {
            var jqElArr = $(item).find("li");

            this.highlighting(jqElArr, searStr[i]);
            if (i == len - 1) {
                this.getValue(false);
                this.popClose();
            }
        });
    }
};

Cascader.prototype.getResult = function () {
    return this.result;
};