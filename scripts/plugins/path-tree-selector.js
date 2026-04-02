/////////////////////////////////////////
// 渲染文件夹树，依靠于zTree，可以新建、修改、删除、搜索
// @author JackC
// @create 2023-09-08
// @version 0.2
///////////////////////////////////////////
// @author JackC
// @date 2023-11-07
// @version 0.3
// [*] 高度限制
// [*] 回显
// [*] 默认选中
// [*] 选择模式[文件 目录 混合]
// [-] 多语言引入. @rej: 使用外层的LANG即可
///////////////////////////////////////////
// @author JackC
// @date 2023-11-14
// @version 0.4
// [*] 缓存，可以根据标签的ID直接获取到选择器对象
// [-] 实例化一个，可以初始化多个选择框. @rej: 代码逻辑不支持初始化多个
///////////////////////////////////////////
// @author JackC
// @date 2023-12-25
// @version 0.5
// [*] 实现模态框选择
// [*] 实现搜索功能: 完成目录级搜索,全文检索不必要
///////////////////////////////////////////
// @author JackC
// @date 2024-04-23
// @version 0.6
// [*] 搜索按钮点击后自动聚焦
// [*] 搜索按钮输入回车键开始搜索
// [*] 使用web_ng接口显示和搜索
///////////////////////////////////////////

(function () {
    let pathTreeCache = {};

    /**
     * @typedef {Object} RequestType
     * @property {String}   url         请求的url
     * @property {String}   method      请求的方法，默认为GET
     * @property {String}   contentType 默认为application/json; charset=UTF-8
     * @property {Object}   req_data    请求的数据
     */

    /**
     * 文件树
     * @constructor
     * @param {String} [agentUuid] 客户端uuid,请求的参数
     *
     * @param {Object}   [options]                 操作选项
     * @param {String}   [options.newFolderPrefix] 新目录的前缀
     * @param {String}   [options.newFolderIcon]   新目录的图标
     * @param {String}   [options.newFolderOpen]   新目录打开状态的图标
     * @param {String}   [options.newFolderClose]  新目录关闭状态的图标
     * @param {String}   [options.newFileIcon]   新文件的图标
     * @param {String}   [options.newFileOpen]   新文件打开状态的图标
     * @param {String}   [options.newFileClose]  新文件关闭状态的图标
     * @param {Boolean}  [options.operateFlag]     是否允许新建、修改、删除
     * @param {Boolean}  [options.searchFlag]      是否允许搜索
     * @param {String}   [options.pickType]        选择的模式, [plain modal drawer], drawer未实现
     * @param {Function} [options.onChange]        选择改变的回调,参数【选择的路径，传入的标签ID，选择的ztree节点】
     * @param {Function} [options.onCancel]        模块框取消回调,无参数
     * @param {Function} [options.onSubmit]        模态狂确定回调,参数【选择的路径，传入的标签ID】
     *
     * @param {Object}  [options.plain]               plain方式显示配置
     * @param {String}  [options.plain.treeMaxHeight] 树的最大高度
     *
     * @param {RequestType|Object}  [options.request] 请求配置
     * @param {RequestType|Object}  [options.search]  搜索配置
     * @property blur
     */
    let PathTreeSelector = function (agentUuid = null, options = {}) {
        if (!options) {
            options = {};
        }
        this.agentUuid = agentUuid ? agentUuid : null;
        this.options = this.getOptions(options);
        this.request = this.getRequestConfig(options.request);
        this.plainOption = this.getPlainOption(options.plain);
        this.searchRequest = this.getSearchRequest(options.search);

        // 新建文件夹
        this.newPathCount = 1;
        this.fileNameFlag = true;
        this.lastCheck = null;
        this.excludeMountPointList = [];  // 排除挂载点列表
    }

    /**
     * 获取请求配置
     * @param request
     * @returns {Object}
     */
    PathTreeSelector.prototype.getRequestConfig = function (request) {
        return {
            url: request?.url ? request.url : `/api/v1/agents/${this.agentUuid}/disk_files`,
            method: 'GET',
            contentType: request?.contentType ? request.contentType : 'application/json; charset=UTF-8',
            req_data: {
                search_index: 0,
                limit_count: 100,
                dir_path: '',
                search_file_name: '',
                select_mode: 3,
                code_type: 2,
                selected_path: '',
                parent_path: '',
            },
        };
    }

    /**
     * 获取配置
     * @param options
     * @returns {Object}
     */
    PathTreeSelector.prototype.getOptions = function (options) {
        if (!options) {
            options = {};
        }
        return {
            newFolderPrefix: options.newFolderPrefix ? options.newFolderPrefix : 'new folder',
            newFolderIcon: options.newFolderIcon ? options.newFolderIcon : './img/fs/wenjianjia.png',
            newFolderOpen: options.newFolderOpen ? options.newFolderOpen : './img/fs/wenjianjiaopen.png',
            newFolderClose: options.newFolderClose ? options.newFolderClose : './img/fs/wenjianjia.png',
            newFileIcon: options.newFileIcon ? options.newFileIcon : './img/fs/wenjian.png',
            newFileOpen: options.newFileOpen ? options.newFileOpen : './img/fs/wenjian.png',
            newFileClose: options.newFileClose ? options.newFileClose : './img/fs/wenjian.png',
            operateFlag: options.operateFlag !== undefined ? !!options.operateFlag : true,
            searchFlag: options.searchFlag !== undefined ? !!options.searchFlag : true,
            pickType: options.pickType ? options.pickType : 'modal',  // 选择方式[plain modal drawer], drawer未实现
            onChange: options.onChange ? options.onChange : null,
            onCancel: options.onCancel ? options.onCancel : null,
            onSubmit: options.onSubmit ? options.onSubmit : null,
        };
    }

    /**
     * 获取plain配置
     * @param plainOption
     * @returns {Object}
     */
    PathTreeSelector.prototype.getPlainOption = function (plainOption) {
        if (!plainOption) {
            plainOption = {};
        }
        return {
            treeMaxHeight: plainOption.treeMaxHeight ? plainOption.treeMaxHeight : '200px',
        }
    }

    /**
     * 获取搜索的请求配置
     * @param searchRequest
     * @returns {{p: any, method: (*|string), f: (*|string), m: (*|number), contentType: (*|string), url: (*|string)}}
     */
    PathTreeSelector.prototype.getSearchRequest = function (searchRequest) {
        if (!searchRequest) {
            searchRequest = {};
        }
        return {
            url: searchRequest?.url ? searchRequest.url : `/api/v1/agents/${this.agentUuid}/disk_files/search`,
            method: 'GET',
            contentType: searchRequest?.contentType ? searchRequest.contentType : 'application/json; charset=UTF-8',
            req_data: {
                search_index: 0,
                limit_count: 1000,
                dir_path: '',
                search_file_name: '',
                select_mode: 3,
                code_type: 2,
                selected_path: '',
                parent_path: '',
                search_value: '',
            },
        };
    }

    PathTreeSelector.prototype.setInitOption = function (targetId, selectMode = 3, selectedPath = '', disabled = false, osType = 'Linux') {
        // 初始化标签值
        this.selectOnly = true;
        if (typeof targetId === 'object') {
            this.topTargetId = targetId.target_id;
            this.topTarget = '#' + targetId.target_id;
            this.osType = typeof targetId.os_type === 'undefined' ? 'Linux' : targetId.os_type;
            this.selectedPath = typeof targetId.selected_path === 'undefined' ? '' : targetId.selected_path;
            this.request.req_data.select_mode = typeof targetId.select_mode === 'undefined' ? 3 : targetId.select_mode;
            this.disabled = typeof targetId.disabled === 'undefined' ? false : targetId.disabled;
            this.selectOnly = typeof targetId.select_only === 'undefined' ? true : targetId.select_only;
            this.lastSelectPath = this.selectedPath;
            this.excludeMountPointList = [];
            if (Array.isArray(targetId.exclude_mount_point_list) && targetId.exclude_mount_point_list.length > 0) {  // 排除挂载点列表
                for (const excludeMountPoint of targetId.exclude_mount_point_list) {
                    if (!excludeMountPoint) {
                        continue;
                    }
                    this.excludeMountPointList.push(excludeMountPoint.replaceAll('\\', '/'));  // 路径统一替换为/
                }
                // 去重
                this.excludeMountPointList = [...new Set(this.excludeMountPointList)];
            }
        } else {
            this.topTargetId = targetId;
            this.topTarget = '#' + targetId;
            this.osType = osType;
            this.selectedPath = selectedPath;
            this.request.req_data.select_mode = selectMode;
            this.disabled = disabled;
            this.lastSelectPath = selectedPath;
        }
        this.metronicTarget = this.topTarget;
    }

    /**
     * @typedef {Object} PathTreeOption
     * @property {String} target_id 目标节点的标签ID
     * @property {Number} [select_mode=3] 选择方式[1选择文件 2选择目录 3混合选择]
     * @property {String} [selected_path=''] 选择的路径
     * @property {Boolean} [disabled=false] 禁止选择
     * @property {String} [os_type='Linux'] 操作系统类别
     * @property {Boolean} [select_only=true] 是否只能选择路径
     */
    /**
     * 初始化参数
     * @lends PathTreeSelector.prototype
     * @param {String|PathTreeOption} targetId 目标节点的标签ID
     * @param {Number} selectMode 选择方式[1选择文件 2选择目录 3混合选择]
     * @param {String} selectedPath 选择的路径
     * @param {Boolean} disabled 禁止选择
     * @param {String} osType 操作系统类别
     */
    PathTreeSelector.prototype.init = function (targetId, selectMode = 3, selectedPath = '', disabled = false, osType = 'Linux') {
        // 初始化zTree设置
        this.initZTreeSetting();
        this.setInitOption(targetId, selectMode, selectedPath, disabled, osType);
        // 清空内容
        $(this.topTarget).html('');

        // 初始化文件树
        if ('plain' === this.options.pickType) {
            this.showPlainPick();
        } else if ('modal' === this.options.pickType) {
            this.showModalPick();
        } else {
            $(this.topTarget).html('Not support pick type');
            return;
        }

        pathTreeCache[this.topTargetId] = this;
    }

    /**
     * 显示文件选择树
     */
    PathTreeSelector.prototype.showPlainPick = function () {
        this.fileNameFlag = true;
        this.newPathCount = 1;
        let context = '';
        let readonly = 'readonly';
        if (!this.selectOnly) {
            readonly = '';
        }
        context += `<div id="${this.topTargetId}_inputGroup" class="input-group" style="width: 100%; margin-bottom: 0">` +
            `<input type="text" class="form-control popovers" value="${this.selectedPath}" ${readonly} data-container="body"
                data-trigger="hover" spellcheck="false" data-placement="top" data-content="${this.selectedPath}" style="color: #333" id="${this.topTargetId}_input" />` +
            `<span class="input-group-btn" id="${this.topTargetId}_inputGroup_btn">` +
            `<div class="btn green-haze selector-submit ${this.disabled ? 'disabled' : ''}"><i class="fa fa-chevron-down"></i> ${LANG.UI_CLIENT_APP_SELECT_PATH}</div>` +
            `</span>` +
            `</div>`;
        context += `<ul id="${this.topTargetId}_zTree" class="ztree no-scroll-bar" style="max-height: ${this.plainOption.treeMaxHeight}; overflow-y: auto"></ul>`;
        $(this.topTarget).append($(context));
        this.popoverInputTag = $(`${this.topTarget}_inputGroup` + ` input.popovers`);
        /**
         * @type {jQuery|*|{fadeIn: Function, scrollTop: Function|Number}}
         */
        this.zTreeTag = $(`${this.topTarget}_zTree`);
        this.selectBtnTag = $(`${this.topTarget}_inputGroup` + ` div.selector-submit`);
        if (!this.agentUuid) {  // 如果没有输入客户端UUID, 则不显示
            $(`#${this.topTargetId}_inputGroup_btn`).hide();
        }

        this.popoverInputTag.popover();
        this.zTreeTag.hide();
        if (!this.disabled) {
            this.selectBtnTag.on('click', () => {  // 注册点击事件
                this.clickSelectBtn();
            });
        }
    }

    /**
     * 点击选择路径的按钮
     */
    PathTreeSelector.prototype.clickSelectBtn = function () {
        let iconTag = $(`${this.topTarget}_inputGroup` + ` div.selector-submit i.fa`);
        if (iconTag.hasClass('fa-chevron-down')) {  // 显示树
            this.selectBtnTag.html(`<i class="fa fa-chevron-up"></i> ${LANG.UI_CLIENT_APP_FINISH_SELECT_PATH}`);
            this.zTreeTag.fadeIn();
            this.initZTree().then(() => {
                let checkNode = this.getCurrentCheckNode();
                if (checkNode) {
                    setTimeout(() => {
                        // 设置选中的位置
                        this.zTreeTag.scrollTop($('#' + checkNode.tId).get(0).offsetTop - 50);
                    }, 200);  // 需要等待dom加载
                }
            });
        } else {  // 隐藏树
            this.selectBtnTag.html(`<i class="fa fa-chevron-down"></i> ${LANG.UI_CLIENT_APP_SELECT_PATH}`);
            this.zTreeTag.hide();
        }
    }

    /**
     * 以模态框弹窗显示
     */
    PathTreeSelector.prototype.showModalPick = function () {
        this.initModal();
        let readonly = 'readonly';
        if (!this.selectOnly) {
            readonly = '';
        }
        let context = `
            <div id="${this.topTargetId}_inputGroup" class="input-group" style="width: 100%;">
                <input type="text" class="form-control popovers" value="${this.selectedPath}" ${readonly} data-container="body"
                    data-trigger="hover" spellcheck="false" data-placement="top" data-content="${this.selectedPath}" style="color: #333; border-color: #E9EBEB;"
                    id="${this.topTargetId}_input" />
                <span class="input-group-btn" id="${this.topTargetId}_inputGroup_btn">
                    <div class="btn btn-default search-submit popovers" data-container="body" data-trigger="hover"
                        data-placement="top" data-content="${LANG.UI_PATH_TREE_BROWSER_FILE}"
                        style="display: flex; width: 40px; height: 34px; background-color: #fff; border: 1px solid #E9EBEB; border-radius: 0 4px 4px 0 !important; padding: 8px 12px;">
                        <img src="./img/platform/folder-16x17.png" style="width: 16px; height: 16px;" />
                    </div>
                </span>
            </div>`;
        $(this.topTarget).append($(context));
        this.popoverInputTag = $(`${this.topTarget}_inputGroup` + ` input.popovers`);
        this.selectBtnTag = $(`${this.topTarget}_inputGroup` + ` div.search-submit`);
        this.metronicTarget = this.topTarget + '-select-modal div.modal-body';
        if (!this.agentUuid) {  // 如果没有输入客户端UUID, 则不显示
            $(`#${this.topTargetId}_inputGroup_btn`).hide();
        }

        $(`${this.topTarget}_inputGroup` + ' .popovers').popover();
        if (!this.disabled) {
            this.selectBtnTag.on('click', () => {
                this.clickShowModalBtn().then();
            });
        }
        let self = this;
        $(`#${this.topTargetId}_input`).off().on('change input', function () {
            self.lastSelectPath = $(this).val();
            $(`#${self.topTargetId}-showback`).val(self.lastSelectPath);
            if (self.options.onChange) {
                self.options.onChange(self.lastSelectPath, self.topTargetId, null);
            }
        });
    }

    /**
     * 初始化模态框
     */
    PathTreeSelector.prototype.initModal = function () {
        let modalTag = $(this.topTarget + '-select-modal');
        if (modalTag) {
            modalTag.remove();
        }
        let readonly = 'readonly';
        if (!this.selectOnly) {
            readonly = '';
        }
        let modal = `
		<div id="${this.topTargetId}-select-modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-backdrop="static">
			<div class="modal-header">
				<h4 class="modal-title">
				<i class="iconfont icon-fsdata"></i>
				<span>${LANG.UI_PATH_TREE_MODAL_TITLE}</span>
				</h4>
			</div>
			<div class="modal-body">
				<div class="input-group ${this.options.searchFlag ? 'display-none' : 'display-none'}" style="width: 100%;">
					<input type="text" spellcheck="false" class="form-control" id="${this.topTargetId}-search-value" />
                    <span class="input-group-btn" id="${this.topTargetId}-search-btn">
                        <div class="btn btn-success">
                        <i class="fa fa-search"></i>
                            ${LANG.UI_PATH_TREE_SEARCH}
                        </div>
                    </span>
				</div>
			    <div class="input-group mb10 ${this.options.searchFlag ? '' : ''}" style="width: 100%;border: 1px solid #e0e0e0">
                    <ul id="${this.topTargetId}-ztree" class="ztree" style="height: ${this.options.searchFlag ? '284px' : '284px'}; overflow-y: auto"></ul>
			    </div>
			    <div class="input-group" style="width: 100%;">
			        <label class="col-md-3 control-label tal select-file-title_en" style="text-align: left">${LANG.UI_PATH_TREE_SELECTED_FILE}</label>
			        <div class="col-md-9 pr0 pl0 select-file-content_en">
					    <input type="text" spellcheck="false" class="form-control" ${readonly} id="${this.topTargetId}-showback" value="${this.selectedPath}"
					         data-container="body" data-trigger="hover" data-placement="top" data-content="${this.selectedPath}"
					         style="color: #333" />
			        </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default btn-cancel">${LANG.UI_PUBLIC_CANCEL}</button>
				<button type="button" class="btn btn-primary btn-submit">${LANG.UI_PUBLIC_CONFIRM}</button>
			</div>
		</div>`;
        $('body').append($(modal));
        $(this.topTarget + '-showback').popover();
        this.zTreeTag = $(this.topTarget + '-ztree');
        $(this.topTarget + '-select-modal button.btn-submit').on('click', () => {
            let selectedPath = this.getCurrentCheckPath();
            if (!selectedPath) {
                UIToastr.showWarning(LANG.UI_PATH_TREE_SELECT_FILE_TITLE, LANG.UI_PATH_TREE_SELECT_FILE_TIP1);
                return;
            }
            if (this.options.onSubmit) {
                if (!this.options.onSubmit(selectedPath, this.topTargetId)) {
                    return;
                }
            }
            this.lastSelectPath = selectedPath;
            this.popoverInputTag.attr('data-content', this.lastSelectPath).val(this.lastSelectPath);
            $(this.topTarget + '-select-modal').modal('hide');
        });
        $(this.topTarget + '-select-modal button.btn-cancel').on('click', () => {
            this.popoverInputTag.attr('data-content', this.lastSelectPath).val(this.lastSelectPath);
            $(this.topTarget + '-select-modal').modal('hide');
        });
        if (this.options.searchFlag) {
            $(this.topTarget + '-search-btn').on('click', () => {
                let value = $(this.topTarget + '-search-value').val();
                if (!value) {
                    UIToastr.showInfo(LANG.UI_PATH_TREE_SELECT_FILE_TITLE, LANG.UI_PATH_TREE_SELECT_FILE_TIP2);
                    return;
                }
                console.log(value);
                // NOTE: 全盘扫描暂不实现，原因: 没必要，扫描的代价太大
            });
        }
        let self = this;
        $(`#${this.topTargetId}-showback`).off().on('change', function () {
            self.lastSelectPath = $(this).val();
        });
    }

    /**
     * 点击了显示模态框按钮
     * @returns {Promise<void>}
     */
    PathTreeSelector.prototype.clickShowModalBtn = async function () {
        $(this.topTarget + '-select-modal').modal({width: '760px', height: '360px'});
        await this.initZTree();
        let checkNode = this.getCurrentCheckNode();
        if (checkNode) {
            setTimeout(() => {
                // 设置选中的位置
                this.zTreeTag.scrollTop($('#' + checkNode.tId).get(0).offsetTop - 50);
            }, 200);  // 需要等待dom加载
        } else if (this.lastSelectPath) {
            let treeNodes = this.getAllTreeNode();
            /**
             * @type {{title: String, tId: String}}
             */
            let treeNode = {};
            for (treeNode of treeNodes) {
                if (treeNode.title === this.lastSelectPath) {
                    break;
                }
            }
            if (treeNode) {
                this.tree.checkNode(treeNode, true, false, false);
                setTimeout(() => {
                    // 设置选中的位置
                    this.zTreeTag.scrollTop($('#' + treeNode.tId).get(0).offsetTop - 50);
                }, 200);  // 需要等待dom加载
            }
        }
    }

    /**
     * 构建树节点
     * @param row
     * @param data
     * @return {Object}
     */
    PathTreeSelector.prototype.buildTreeNode = function (row, data) {
        let icon = this.options.newFileIcon;
        let iconOpen = this.options.newFileOpen;
        let iconClose = this.options.newFileClose;
        let isParent = false;
        let filetype = parseInt(row.filetype);
        if (filetype !== 1) {
            icon = this.options.newFolderIcon;
            iconOpen = this.options.newFolderOpen;
            iconClose = this.options.newFolderClose;
            isParent = true;
        }
        return {
            id: row.filepath,
            pId: row.parent_path,
            name: row.filename,
            title: row.filepath,
            icon,
            iconOpen,
            iconClose,
            isParent,
            open: row.open,
            checked: row.checked,
            filetype,
            agent_uuid: row.agent_uuid,
            search_file_name: data.search_file_name,
            current_next_index: parseInt(data.current_next_index),
            eventtype: 'file',
            is_new: false,
        };
    };

    /**
     * 获取更多节点
     * @param data
     * @return {Object}
     */
    PathTreeSelector.prototype.getMoreNode = function (data) {
        return {
            id: LANG.UI_CLIENT_PATH_TREE_MORE_TITLE,
            pId: 0,
            name: LANG.UI_CLIENT_PATH_TREE_MORE,
            title: LANG.UI_CLIENT_PATH_TREE_MORE_TITLE,
            icon: this.options.newFolderIcon,
            iconOpen: this.options.newFolderOpen,
            iconClose: this.options.newFolderClose,
            isParent: false,
            open: false,
            checked: false,
            chkDisabled: true,
            dir_path: data.dir_path,
            search_file_name: data.search_file_name,
            current_next_index: parseInt(data.current_next_index),
            eventtype: 'more',
            is_new: false,
        };
    };

    /**
     * 根据排除目录判断文件路径是否显示
     * @param filepath
     * @returns {boolean}
     */
    PathTreeSelector.prototype.judgeFilepathIsShowByExcludeMountPointList = function (filepath) {
        if(this.osType !== 'Windows') {  // 仅对Windows处理
            return true;
        }
        if (this.excludeMountPointList.length === 0) {  // 排除挂载点列表为空，默认显示
            return true;
        }
        return !this.excludeMountPointList.includes(filepath);
    }

    /**
     * 初始化zTree
     */
    PathTreeSelector.prototype.initZTree = async function () {
        return new Promise((resolve, reject) => {
            this.request.req_data.selected_path = this.selectedPath;
            if (this.zTreeTag.hasClass('initZTreeFlag')) {
                resolve();
                return;
            }
            Metronic.blockUI({target: this.metronicTarget, animate: true});
            pAjaxRequest(this.request.req_data, this.request.url, this.request.method, res => {
                Metronic.unblockUI(this.metronicTarget);
                this.request.req_data.selected_path = '';
                this.zTreeTag.addClass('initZTreeFlag');
                if (!res.success) {
                    UIToastr.showWarning(LANG.UI_PATH_TREE_SELECT_FILE_TITLE, res.message);
                    reject();
                    return;
                }
                let nodes = [];
                for (const row of res.data.rows) {
                    if (!this.judgeFilepathIsShowByExcludeMountPointList(row.filepath)) {
                        continue;
                    }
                    nodes.push(this.buildTreeNode(row, res.data));
                }
                if (res.data.more_flag) {
                    nodes.push(this.getMoreNode(res.data));
                }
                this.tree = $.fn.zTree.init(this.zTreeTag, this.zTreeSettings, nodes);
                resolve();
            });
        });
    }

    /**
     * 全盘扫描
     */
    PathTreeSelector.prototype.searchValue = function () {
        let value = $(`#${this.topTargetId} input`).val();
        if (!value) {
            return;
        }
        let checkNode = this.getCurrentCheckNode();
        console.log(checkNode);
        // NOTE: 实现搜索功能
        /**
         * 1. 创建后台搜索线程
         * 2. 搜索相应文件内容
         */
    }

    /////////////// zTree配置开始 ///////////////

    /**
     * 初始化zTree设置
     */
    PathTreeSelector.prototype.initZTreeSetting = function () {
        this.zTreeSettings = {
            view: {
                addHoverDom: (treeId, treeNode) => {
                    this.addHoverDom(treeId, treeNode);
                },
                removeHoverDom: (treeId, treeNode) => {
                    this.removeHoverDom(treeId, treeNode);
                },
                selectedMulti: false,
                nameIsHTML: true,
            },
            edit: {
                enable: true,
                editNameSelectAll: true,
                showRenameBtn: this.showRenameBtn,
                showRemoveBtn: this.showRemoveBtn,
                removeTitle: LANG.UI_PUBLIC_DELETE,
                renameTitle: LANG.UI_FILE_EDIT,

            },
            check: {
                enable: true,
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            callback: {
                beforeClick: (treeId, treeNode) => {
                    this.pathNodeSelect(treeId, treeNode);
                },
                onCheck: (e, id, node) => {
                    this.pathOnCheck(e, id, node);
                },
                beforeExpand: (treeId, treeNode) => {
                    this.pathNodeExpand(treeId, treeNode);
                },
                beforeEditName: (treeId, treeNode) => {
                    this.beforeEditName(treeId, treeNode);
                },
                onRemove: (event, treeId, treeNode) => {
                    this.onRemove(event, treeId, treeNode);
                },
                onRename: (event, treeId, treeNode) => {
                    this.onRename(event, treeId, treeNode);
                },
            }
        };
        /**
         * @property {Function|Object|null}
         */
        this.tree = null;
    }

    /**
     * 鼠标移入操作
     * @lends PathTreeSelector.prototype
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.addHoverDom = function (treeId, treeNode) {
        let searchInputDivTag = "#searchInputDiv_" + treeNode.tId;
        if ($(searchInputDivTag).length) {  // 处于搜索状态
            return;
        }
        this.addAddHoverBtn(treeId, treeNode);
        this.addSearchHoverBtn(treeId, treeNode);
    }

    /**
     * 添加 添加图标
     * @param treeId
     * @param treeNode
     * @returns {boolean}
     */
    PathTreeSelector.prototype.addAddHoverBtn = function (treeId, treeNode) {
        if (!this.options.operateFlag) {
            return false;
        }
        if (treeNode.eventtype === 'more') {
            return false;
        }
        let fileType = parseInt(treeNode.filetype);
        if (1 === fileType) {
            return false;
        }
        let sObj = $("#" + treeNode.tId + "_span");
        let editBtnTag = "#addBtn_" + treeNode.tId;
        if (treeNode.more || treeNode.editNameFlag || $(editBtnTag).length > 0) {
            return false;
        }
        if (!this.fileNameFlag) {
            return false;
        }
        let addStr = `<span class="button add" id="addBtn_${treeNode.tId}" title="${LANG.UI_FILE_NEW_DIR}" onfocus="this.blur();"></span>`;
        sObj.after(addStr);
        let btnNode = $(editBtnTag);
        // 点击添加节点
        if (!btnNode) {
            return false;
        }
        btnNode.on("click", () => {
            this.clickAddHoverBtn(treeId, treeNode);
            return false;
        });
    }

    /**
     * 点击了添加文件夹按钮
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.clickAddHoverBtn = function (treeId, treeNode) {
        if (!treeNode.is_new) {//不是新增的节点
            this.pathNodeExpand(treeId, treeNode);
        }
        let nodeId = treeNode.id + '_' + 0;
        if (treeNode.children !== undefined && treeNode.children.length) {
            nodeId = treeNode.id + '_' + treeNode.children.length;
        }
        this.tree.addNodes(treeNode, -1, {
            id: nodeId,
            icon: this.options.newFolderIcon,
            iconOpen: this.options.newFolderOpen,
            iconClose: this.options.newFolderClose,
            title: '',
            isParent: false,
            pId: treeNode.id,
            name: this.options.newFolderPrefix + this.newPathCount++,
            is_new: true,
            code_type: 2,
            eventtype: 'file',
            is_new_status: true,
        }, false);
        let newNode = this.tree.getNodeByParam("is_new_status", true, null);
        this.beforeEditName(treeId, newNode);
        newNode.is_new_status = false;
        this.fileNameFlag = false;
    }

    /**
     * 添加搜索图标
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.addSearchHoverBtn = function (treeId, treeNode) {
        let fileType = parseInt(treeNode.type);
        let newDirCreate = !!treeNode.is_new;
        if (!this.options.searchFlag || newDirCreate || 1 === fileType || treeNode.more) {
            return;
        }
        if (treeNode.eventtype === 'more') {
            return false;
        }
        let searchStr = `
        <span id="searchBtn_${treeNode.tId}"
            style="width: 18px;height: 18px; color: #8c8c8c; background-color: #f5f5f5;
                display: inline-block; margin-left: 2px; position: relative; top: 2px">
            <i class="fa fa-search" style="font-family: FontAwesome,serif; position: relative; top: -2px;"></i>
        </span>`;
        let sObj = $("#" + treeNode.tId + "_span");
        let searchBtnTag = "#searchBtn_" + treeNode.tId;
        if ($(searchBtnTag).length > 0) {
            return;
        }
        sObj.after(searchStr);
        $(searchBtnTag).on('click', () => {
            this.clickSearchHoverBtn(treeId, treeNode, sObj);
        }).hover(() => {
            $(searchBtnTag).css({color: '#0fbf98', 'background-color': '#e7f7f3'});
        }, () => {
            $(searchBtnTag).css({color: '#8c8c8c', 'background-color': '#f5f5f5'});
        });
    }

    /**
     * 点击了鼠标悬停的搜索按钮
     * @param treeId
     * @param treeNode
     * @param sObj
     */
    PathTreeSelector.prototype.clickSearchHoverBtn = function (treeId, treeNode, sObj) {
        // 移除悬停按钮
        this.removeHoverDom(treeId, treeNode);
        if (treeNode.search_status === undefined) {
            treeNode.search_status = 1;
            this.tree.updateNode(treeNode);
        }
        if (treeNode.search_value === undefined) {
            treeNode.search_value = '';
            this.tree.updateNode(treeNode);
        }
        let searchStatus = parseInt(treeNode.search_status);
        // 移除其他搜索框
        $('div.searchInputDivCls, span.searchErrorCls').off().remove();
        let lastSearch = treeNode.search_value ? treeNode.search_value : '';
        let searchInput = `
            <div style="display: inline-block;" class="form-group searchInputDivCls" id="searchInputDiv_${treeNode.tId}">
                <div style="display: flex; margin-top: 1px">
                    <input id="searchInput_${treeNode.tId}" spellcheck="false" class="form-control" style="height: 21px; padding: 0 10px"
                        value="${lastSearch}" autocomplete="off" />
                    <span class="input-group-btn" style="width: 42px; margin-left: 4px; display: flex; margin-top: 2px">
                        <div class="btn btn-success" style="position: relative; top: -2px; width: 21px; height: 21px;"
                            id="searchInputSearchBtn_${treeNode.tId}" title="${LANG.UI_PATH_TREE_SEARCH_START}">
                            <i class="fa fa-search" style="font-family: FontAwesome,serif; padding-top: 3px"></i>
                        </div>
                        <div class="btn btn-danger" style="position: relative; top: -2px; width: 21px; height: 21px;"
                            id="searchInputCloseBtn_${treeNode.tId}" title="${LANG.UI_PATH_TREE_SEARCH_EXIT}">
                            <i class="fa fa-close" style="font-family: FontAwesome,serif; padding-top: 3px"></i>
                        </div>
                        <div class="ispinner display-none" id="searchInputLoading_${treeNode.tId}"
                            title="${LANG.UI_PATH_TREE_SEARCH_RUNNING}">
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                        </div>
                        <div class="btn btn-danger display-none" style="position: relative; top: -2px; width: 21px; height: 21px;"
                            id="searchInputStopBtn_${treeNode.tId}" title="${LANG.UI_PATH_TREE_SEARCH_STOP}">
                            <i class="fa fa-stop" style="font-family: FontAwesome,serif; padding-top: 3px"></i>
                        </div>
                    </span>
                </div>
                <span id="searchInputWidth_${treeNode.tId}" class="display-none"></span>
            </div>`
        let searchError = `<span id="searchError_${treeNode.tId}" class="searchErrorCls" style="color: red"></span>`;
        sObj.after(searchError);
        sObj.after(searchInput);
        $('#searchInputDiv_' + treeNode.tId).on('click', ev => {
            ev.preventDefault();
            return false;
        });
        $('#searchInput_' + treeNode.tId).on('click', () => {
            $('#searchInput_' + treeNode.tId).focus();
        }).on('input change', function () {
            // 获取输入框中文本内容的实际宽度
            let inputWidth = $('#searchInputWidth_' + treeNode.tId).html($(this).val()).width();

            if (inputWidth > $(this).width()) {
                if (inputWidth > 300) {  // 最大值300px
                    return;
                }
                $(this).css('width', inputWidth + 'px');
            } else if (inputWidth < $(this).width()) {
                $(this).css('width', ''); // 清除宽度样式，使其回到默认宽度

                if (inputWidth > $(this).width()) {
                    $(this).css('width', inputWidth + 'px');
                }
            }
        }).focus().keydown(ev => {
            if (ev.key === 'Enter' || ev.keyCode === 13) {
                this.clickInnerSelectBtn(treeId, treeNode);
            }
        });
        $('#searchInputSearchBtn_' + treeNode.tId).on('click', () => {
            this.clickInnerSelectBtn(treeId, treeNode);
        });
        $('#searchInputCloseBtn_' + treeNode.tId).on('click', () => {
            $('#searchInputDiv_' + treeNode.tId).off().remove();
            $('#searchError_' + treeNode.tId).off().remove();
            if (1 !== treeNode.search_status) {
                let checkNode = this.getCurrentCheckNode();
                if (checkNode && checkNode.getParentNode() === treeNode) {
                    this.tree.checkNode(treeNode, false, false, true);
                }
                this.tree.removeChildNodes(treeNode);
                treeNode.isParent = true;
                treeNode.search_value = '';
                this.tree.updateNode(treeNode);
                this.tree.expandNode(treeNode, false);
            }
            treeNode.search_status = 1;
            this.tree.updateNode(treeNode);
        });
        $('#searchInputStopBtn_' + treeNode.tId).on('click', () => {
            this.outInnerSearch(treeId, treeNode);
            treeNode.search_status = 1;
            this.tree.updateNode(treeNode);
        });
        if (2 === searchStatus) {
            this.inInnerSearch(treeId, treeNode);
        } else {
            this.outInnerSearch(treeId, treeNode);
        }
    }

    /**
     * 设置开始搜索的图标
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.inInnerSearch = function (treeId, treeNode) {
        $('#searchInputStopBtn_' + treeNode.tId).show();
        $('#searchInputLoading_' + treeNode.tId).show();
        $('#searchInputSearchBtn_' + treeNode.tId).hide();
        $('#searchInputCloseBtn_' + treeNode.tId).hide();
        $('#searchInput_' + treeNode.tId).attr('readonly', 'readonly').attr('title', LANG.UI_PATH_TREE_SEARCH_RUNNING);
        $('#searchError_' + treeNode.tId).html('');
    }

    /**
     * 设置结束搜索的图标
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.outInnerSearch = function (treeId, treeNode) {
        $('#searchInputStopBtn_' + treeNode.tId).hide();
        $('#searchInputLoading_' + treeNode.tId).hide();
        $('#searchInputSearchBtn_' + treeNode.tId).show();
        $('#searchInputCloseBtn_' + treeNode.tId).show();
        $('#searchInput_' + treeNode.tId).removeAttr('readonly').removeAttr('title');
    }

    /**
     * 点击了内部的搜索按钮,进行检索目录下对应的文件或目录
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.clickInnerSelectBtn = function (treeId, treeNode) {
        let searchInputTag = $('#searchInput_' + treeNode.tId);
        let value = searchInputTag.val();
        if (!value) {
            UIToastr.showInfo(LANG.UI_PATH_TREE_SELECT_FILE_TITLE, LANG.UI_PATH_TREE_SELECT_FILE_TIP2);
            return;
        }
        let dir = treeNode.title;
        if (treeNode.title === "/" && treeNode.eventtype !== undefined) {
            dir = treeNode.id
        }
        this.searchRequest.req_data.search_index = 0;
        this.searchRequest.req_data.dir_path = dir;
        this.searchRequest.req_data.parent_path = treeNode.id;
        this.searchRequest.req_data.search_value = value;
        this.searchRequest.req_data.select_mode = this.request.req_data.select_mode;
        // 隐藏关闭和搜索按钮
        this.inInnerSearch(treeId, treeNode);

        // 移除所有的子节点
        let checkNode = this.getCurrentCheckNode();
        if (checkNode && checkNode.getParentNode() === treeNode) {
            this.tree.checkNode(treeNode, false, true, true);
        }
        this.tree.removeChildNodes(treeNode);
        treeNode.search_status = 2;
        treeNode.isParent = true;
        treeNode.search_value = value;
        this.tree.updateNode(treeNode);
        this.tree.expandNode(treeNode, true);

        // 搜索
        this.doInnerSearch(treeId, treeNode);
    }

    /**
     * 搜索文件
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.doInnerSearch = function (treeId, treeNode) {
        pAjaxRequest(this.searchRequest.req_data, this.searchRequest.url, this.searchRequest.method, res => {
            if (!res.success) {
                $('#searchError_' + treeNode.tId).html(LANG.UI_PATH_TREE_SEARCH_FAILED + res.message);
                this.outInnerSearch(treeId, treeNode);
                treeNode.search_status = 4;
                this.tree.updateNode(treeNode);
                return;
            }
            let nodes = [];
            for (const row of res.data.rows) {
                if (!this.judgeFilepathIsShowByExcludeMountPointList(row.filepath)) {
                    continue;
                }
                nodes.push(this.buildTreeNode(row, res.data));
            }
            this.tree.addNodes(treeNode, nodes, true);
            if (!res.data.more_flag) {
                this.outInnerSearch(treeId, treeNode);
                treeNode.search_status = 4;
                this.tree.updateNode(treeNode);
                return;
            }
            this.searchRequest.req_data.search_index = parseInt(res.data.current_next_index);
            this.searchRequest.req_data.search_file_name = res.data.search_file_name;
            this.doInnerSearch(treeId, treeNode);
        });
    }

    /**
     * 鼠标移出操作
     * @lends PathTreeSelector.prototype
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.removeHoverDom = function (treeId, treeNode) {
        $("#addBtn_" + treeNode.tId).off().remove();
        $("#searchBtn_" + treeNode.tId).off().remove();
    }

    /**
     * 是否显示编辑按钮
     * @lends PathTreeSelector.prototype
     * @param treeId
     * @param {{eventtype: String}} treeNode
     * @param {{is_new: Boolean}} treeNode
     * @return {boolean}
     */
    PathTreeSelector.prototype.showRenameBtn = function (treeId, treeNode) {
        if (treeNode.eventtype === 'more') {
            return false;
        }
        return !!treeNode.is_new;
    }

    /**
     * 是否显示删除按钮
     * @lends PathTreeSelector.prototype
     * @param treeId
     * @param {{eventtype: String}} treeNode
     * @param {{is_new: Boolean}} treeNode
     * @return {boolean}
     */
    PathTreeSelector.prototype.showRemoveBtn = function (treeId, treeNode) {
        if (treeNode.eventtype === 'more') {
            return false;
        }
        return !!treeNode.is_new;
    }

    /**
     * 节点点击
     * @lends PathTreeSelector.prototype
     * @param treeId
     * @param {Object} treeNode
     * @param {Boolean} treeNode.more
     * @param {String} treeNode.next_index
     * @param {String} treeNode.dir_path
     * @param {String} treeNode.pId
     * @param {Function} treeNode.getParentNode
     * @param {Boolean} treeNode.checked
     * @param {String} treeNode.search_file_name
     * @param {String} treeNode.eventtype
     * @param {String} treeNode.current_next_index
     */
    PathTreeSelector.prototype.pathNodeSelect = function (treeId, treeNode) {
        if (treeNode.eventtype === 'more') {
            Metronic.blockUI({target: this.metronicTarget, animate: true});
            this.request.req_data.search_index = parseInt(treeNode.current_next_index);
            this.request.req_data.dir_path = treeNode.dir_path;
            this.request.req_data.search_file_name = treeNode.search_file_name;
            this.request.req_data.selected_path = '';
            this.request.req_data.parent_path = treeNode.pId;
            pAjaxRequest(this.request.req_data, this.request.url, this.request.method, res => {
                Metronic.unblockUI(this.metronicTarget);
                if (!res.success) {
                    UIToastr.showWarning(LANG.UI_PATH_TREE_SELECT_FILE_TITLE, res.message);
                    return;
                }
                let nodes = [];
                for (const row of res.data.rows) {
                    if (!this.judgeFilepathIsShowByExcludeMountPointList(row.filepath)) {
                        continue;
                    }
                    nodes.push(this.buildTreeNode(row, res.data));
                }
                if (res.data.more_flag) {
                    nodes.push(this.getMoreNode(res.data));
                }
                this.tree.removeNode(treeNode);
                this.tree.addNodes(treeNode.getParentNode(), nodes, true);
            });
        } else {
            this.tree.checkNode(treeNode, !treeNode.checked, false, true);
        }
    }

    /**
     * 格式化文件路径
     * @param path
     * @return {*|string}
     */
    PathTreeSelector.prototype.formatFilePath = function (path) {
        if (!path) {
            return '';
        }
        if(this.osType !== 'Windows') {
            return path;
        }
        return path.replaceAll(/\//g, '\\');
    }

    /**
     * 节点选中
     * @lends PathTreeSelector.prototype
     * @param e
     * @param id
     * @param treeNode
     */
    PathTreeSelector.prototype.pathOnCheck = function (e, id, treeNode) {
        let checked = treeNode.checked;
        //单选
        let allNodes = this.tree.getCheckedNodes(true);
        for (let i = 0; i < allNodes.length; i++) {
            this.tree.checkNode(allNodes[i], false, false, false);
        }

        if (!checked) {
            if ('plain' === this.options.pickType) {
                this.popoverInputTag.attr('data-content', '').val('');
            }
            $(this.topTarget + '-showback').attr('data-content', '').val('');
            return
        }

        let fileType = parseInt(treeNode.filetype);
        if (1 === this.request.req_data.select_mode && 1 !== fileType) {  // 选择文件
            this.tree.checkNode(treeNode, false, false, false);
            if ('plain' === this.options.pickType) {
                this.popoverInputTag.attr('data-content', '').val('');
            }
            $(this.topTarget + '-showback').attr('data-content', '').val('');
            UIToastr.showInfo(LANG.UI_PATH_TREE_SELECT_FILE_TITLE, LANG.UI_CLIENT_APP_SELECT_PATH_TIPS1);
            if (this.lastCheck !== null && this.options.onChange) {
                this.options.onChange(null, this.topTargetId, treeNode);
            }
            this.lastCheck = null;
            return;
        }
        this.tree.checkNode(treeNode, true, false, false);
        if ('plain' === this.options.pickType) {
            this.popoverInputTag.attr('data-content', treeNode.title).val(treeNode.title);
        }
        $(this.topTarget + '-showback').attr('data-content', this.formatFilePath(treeNode.title)).val(this.formatFilePath(treeNode.title));
        if (this.lastCheck !== treeNode.title && this.options.onChange) {
            this.options.onChange(treeNode.title, this.topTargetId, treeNode)
        }
        this.lastCheck = treeNode.title;
    };

    /**
     * 展开节点
     * @lends PathTreeSelector.prototype
     * @param treeId
     * @param {{eventtype: String, children: Array|null, title: String, id: String, code_type: String, pId: String}} treeNode
     * @return {boolean}
     */
    PathTreeSelector.prototype.pathNodeExpand = function (treeId, treeNode) {
        if (treeNode.children) {
            return true;
        }
        let dir = treeNode.title;
        if (treeNode.title === "/" && !treeNode.pId) {
            dir = treeNode.id
        }
        Metronic.blockUI({target: this.metronicTarget, animate: true});
        this.request.req_data.search_index = 0;
        this.request.req_data.dir_path = dir;
        this.request.req_data.search_file_name = '';
        this.request.req_data.selected_path = '';
        this.request.req_data.parent_path = treeNode.id;
        pAjaxRequest(this.request.req_data, this.request.url, this.request.method, res => {
            Metronic.unblockUI(this.metronicTarget);
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_PATH_TREE_SELECT_FILE_TITLE, res.message);
                return;
            }
            let nodes = [];
            for (const row of res.data.rows) {
                if (!this.judgeFilepathIsShowByExcludeMountPointList(row.filepath)) {
                    continue;
                }
                nodes.push(this.buildTreeNode(row, res.data));
            }
            if (res.data.more_flag) {
                nodes.push(this.getMoreNode(res.data));
            }
            this.tree.addNodes(treeNode, nodes, true);
        });
    }

    /**
     * 在修改名字之前
     * @lends PathTreeSelector.prototype
     * @param treeId
     * @param treeNode
     * @return {boolean}
     */
    PathTreeSelector.prototype.beforeEditName = function (treeId, treeNode) {
        this.tree.selectNode(treeNode);
        this.tree.editName(treeNode);
        return false;
    }

    /**
     * 删除节点
     * @lends PathTreeSelector.prototype
     * @param event
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.onRemove = function (event, treeId, treeNode) {
        //获取删除节点的父节点
        let parentNode = treeNode.getParentNode();
        //如果父节点下的子节点不存在
        if (!parentNode.children.length) {
            //修改父节点图标为文件夹样式
            parentNode.isParent = true;
            // parentNode.open = true;
            this.tree.updateNode(parentNode);
        }
    }

    /**
     * 改完名字之后
     * @lends PathTreeSelector.prototype
     * @param event
     * @param treeId
     * @param treeNode
     */
    PathTreeSelector.prototype.onRename = function (event, treeId, treeNode) {
        //给新增的节点设置title
        let pNode = treeNode.getParentNode();
        treeNode.title = pNode.title + treeNode.name + '/';
        let str = $.trim(treeNode.name);
        if (!str.length) {
            UIToastr.showInfo(LANG.UI_PATH_TREE_NEW_FOLDER_TITLE, LANG.UI_NAS_RECOVERY_FILENAME_NO_NULL);
            this.fileNameFlag = false;
            this.tree.editName(treeNode);
            return;
        } else {
            this.fileNameFlag = true;
        }
        if (this.osType === 'Windows') {
            //文件夹名称不能包含特殊符号,不包含  /  :  "  <  >  | \
            let specialChar = ['/', ':', '"', '<', '>', '|', '\\', '?', '*'];
            for (let key in specialChar) {
                if (str.indexOf(specialChar[key]) !== -1) {
                    UIToastr.showInfo(LANG.UI_PATH_TREE_NEW_FOLDER_TITLE, LANG.UI_FILE_FILENAME_NO_CONTAIN);
                    this.fileNameFlag = false;
                    this.tree.editName(treeNode);
                    return;
                } else {
                    this.fileNameFlag = true;
                }
            }
        }
    }

    /**
     * 获取所有的节点
     * @returns {[]}
     */
    PathTreeSelector.prototype.getAllTreeNode = function () {
        if (!this.tree) {
            return [];
        }
        return this.tree.transformToArray(this.tree.getNodes());
    }

    /**
     * @lends PathTreeSelector.prototype
     * @param checked
     * @return {*}
     */
    PathTreeSelector.prototype.getCheckedNodes = function (checked = true) {
        if (!this.tree) {
            return [];
        }
        return this.tree.getCheckedNodes(checked);
    }

    /**
     * 获取当前选中的节点
     * @return {*|null}
     */
    PathTreeSelector.prototype.getCurrentCheckNode = function () {
        let nodes = this.getCheckedNodes(true);
        if (!nodes.length) {
            return null;
        }
        return nodes.pop();
    }

    /**
     * 获取当前选中的路径
     * @return {*}
     */
    PathTreeSelector.prototype.getCurrentCheckPath = function () {
        if ('plain' === this.options.pickType) {
            return $(`#${this.topTargetId}_input`).val();
        } else if ('modal' === this.options.pickType) {
            return $(`#${this.topTargetId}-showback`).val();
        }
        return '';
    }

    /////////////// zTree配置结束 ///////////////

    // NOTE: 有新的需求可以增加方法

    $.fn.PathTreeSelector = {
        cls: PathTreeSelector,
        getPathTreeSelector: function (targetId) {
            if (pathTreeCache[targetId] !== undefined) {
                return pathTreeCache[targetId];
            }
            return null;
        },
        getCheckPath: function (targetId) {
            let obj = this.getPathTreeSelector(targetId);
            if (!obj) {
                return '';
            }
            return obj.getCurrentCheckPath();
        },
        getCheckNode: function (targetId) {
            let obj = this.getPathTreeSelector(targetId);
            if (!obj) {
                return null;
            }
            return obj.getCurrentCheckNode();
        },
        clearPath: function (targetId) {
            let obj = this.getPathTreeSelector(targetId);
            if (!obj) {
                return;
            }
            $(`#${obj.topTargetId}-showback`).val(``);
            $(`#${obj.topTargetId}_input`).val(``);
            if (obj.tree) {
                obj.tree.checkAllNodes(false);
            }
        }
    };
})();
