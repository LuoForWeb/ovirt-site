var FileSyncRecycleBin = function () {
   
    //事件监听
    var initListener = function () {
        $('#deletePathData').on('click',delData);
        $('#clearData').on('click',clearData);
        $('#restorePathData').on('click',restorePathData);
        $('#searchBtn').on('click',startPathSearch);
        $('#stopBtn').on('click',stopPathSearch);
        
    }

    //删除回收站目录
    var delData = function() {
        var nodes = zPathTree.getCheckedNodes(true);
        nodes = filterFile(nodes);
        var deleteList = [];
        for(var i = 0; i < nodes.length; i++) {
            deleteList.push({
                "file_path": nodes[i].file_path,             //需要删除的文件或目录
                "file_type": nodes[i].file_type,              //文件类型
                "code_type": nodes[i].code_type,                  //编码类型
                "recycle_bin_uuid":nodes[i].recycle_bin_uuid,    //回收站的uuid
            });
        }
        pAjaxRequest({"delete_list" : deleteList}, "/api/v1/filecopy/collection_path", "DELETE", function (result) {
            operateResponseList(result,result.message)
        });

    }

    //清空回收站数据
    var clearData = function() {
        var nodes = zTree.getCheckedNodes(true);
        if (nodes.length == 0) {
            UIToastr.showWarning(LANG.UI_FILE_COPY_RECYCLE_BIN_CLEAR,LANG.UI_FILE_COPY_RECYCLE_BIN_PLEASE_SELECT);
            return;
        }
        nodes = filterFile(nodes);
        var deleteList = [];
        for(var i = 0; i < nodes.length; i++) {
            deleteList.push({
                "recycle_bin_uuid":nodes[i].id,    //回收站的id
            });
        }
        pAjaxRequest({"delete_list" : deleteList}, "/api/v1/filecopy/collection_path", "DELETE", function (result) {
            operateResponseList(result,result.message)
        });

    }

    //还原回收站数据
    var restorePathData = function() {
        var nodes = zPathTree.getCheckedNodes(true);
        if (nodes.length == 0) {
            UIToastr.showWarning(LANG.UI_FILE_COPY_RESTORE_DATA,LANG.UI_FILE_COPY_RESTORE_PLEASE_SELECT);
            return;
        }
        nodes = filterFile(nodes);
        var deleteList = [];
        for(var i = 0; i < nodes.length; i++) {
            deleteList.push({
                "file_path": nodes[i].file_path,             //需要删除的文件或目录
                "file_type": nodes[i].file_type,              //文件类型
                "code_type": nodes[i].code_type,                  //编码类型
                "recycle_bin_uuid":nodes[i].recycle_bin_uuid,    //回收站的uuid
            });
        }
        //看到时候调用恢复接口还是写新的
        pAjaxRequest({"delete_list" : deleteList}, "", "POST", function (result) {
            operateResponseList(result,result.message)
        });

    }

    //开始搜索
    var startPathSearch = function() {

        if ($('#recycle_bin_path').val().trim() == "") {
            UIToastr.showWarning(LANG.UI_FILE_COPY_SEARCH_FILE,LANG.UI_FILE_COPY_PLEASE_INPUT);
            return;
        }
        $('#searchBtn').hide();
        $('#stopBtn').show();
        return;
        //看到时候调用恢复接口还是写新的
        pAjaxRequest({}, "", "POST", function (result) {
            operateResponseList(result,result.message)
        });

    }
    //停止搜索
    var stopPathSearch = function() {
        $('#searchBtn').show();
        $('#stopBtn').hide();
        return;
     
        //看到时候调用恢复接口还是写新的
        pAjaxRequest({}, "", "POST", function (result) {
            operateResponseList(result,result.message)
        });

    }
    

    //过滤文件
    var filterFile = function (allfileNodes) {
        var allCheckedNode = [];
        //得到所有勾选状态是全选中的节点
        for(var i=0; i<allfileNodes.length; i++){
            //check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
            if(allfileNodes[i].check_Child_State == 2 || allfileNodes[i].check_Child_State == -1) {
                allCheckedNode.push(allfileNodes[i]);
            }
        }
        //过滤掉重复的
        for(var m = 0;m<allCheckedNode.length;m++) {
            if(allCheckedNode[m].file_type != 1) {//磁盘或文件夹
                for(var n = 0;n<allCheckedNode.length;n++) {
                    var str = allCheckedNode[n].pId==null ?'':allCheckedNode[n].pId;
                    if(str.includes(allCheckedNode[m].file_path) && allCheckedNode.indexOf(allCheckedNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
                        allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
                        n--;
                    }
                }
            }
        }
        return allCheckedNode;
    }
       

   


    //初始化回收站路径树
    var initPathTree = function (params) {
        Metronic.blockUI({target: '.file-path-content',animate: true});
        pAjaxRequest({}, "/api/v1/filecopy/collection_path", "GET", function (result) {
            if (result.success) {
                $('.file-path-div').show();
                $("#rightTips").hide();
            } else {
                $('.file-path-div').hide();
                $("#rightTips").show();
                return;
            }
            Metronic.unblockUI('.file-path-content');
            var setting = {
                check: {
                    enable: true,
                    nocheckInherit: false
                },
                view: {
                    showTitle: true,
                    nameIsHTML: true
                },
                data: {
                    simpleData: {
                        enable: true
                    },
                    key:{
                        title: "name"
                    },
                },
                callback: {
                    onCheck: onPathCheck,
                    beforeClick: onPathClick,
                }
            };
            zPathTree = $.fn.zTree.init($("#filePath"), setting, result.data);
        });
    }

    var onPathCheck = function(e, treeId, treeNode) {
        modifyDelStyle();
    }

    var onPathClick = function(treeId, treeNode) {
        
    }

     //处理删除按钮样式切换
     var modifyDelStyle = function () {
        var nodes = zPathTree.getCheckedNodes(true);
	    if (nodes.length < 1) {
	    	$('#deletePathData').addClass('exch-forbid-event').removeClass('green-haze');
	    	$('#deletePathData').parent().css({"cursor": "not-allowed"});
	    } else {
	    	$('#deletePathData').removeClass('exch-forbid-event').addClass('green-haze');
	    	$('#deletePathData').parent().css({"cursor": "pointer"});
	    }
    }


    //初始化回收站树
    var initTree = function () {
        var params = {};
        Metronic.blockUI({target: '.recycle-bin-tree-div',animate: true});
        pAjaxRequest(params, "/api/v1/filecopy/collection_task", "GET", function (result) {
            Metronic.unblockUI('.recycle-bin-tree-div');
            if (result.success) {
                $('#recycleBin').show();
                $("#nopointtips").hide();
            } else {
                $('#recycleBin').hide();
                $("#nopointtips").show();
                return;
            }
            var setting = {
                check: {
                    enable: true,
                    nocheckInherit: false
                },
                view: {
                    showTitle: true,
                    nameIsHTML: true
                },
                data: {
                    simpleData: {
                        enable: true
                    },
                    key:{
                        title: "name"
                    },
                },
                callback: {
                    onCheck: onCheck,
                    beforeClick: onClick,
                }
            };
            zTree = $.fn.zTree.init($("#recycleBin"), setting, result.data);
        });
    }

    var onCheck = function (e, treeId, treeNode) {
        onClick(e, treeId, treeNode);
    }
    var onClick = function (treeId, treeNode) {
        if (treeNode.level !== 2) {
            zTree.expandNode(treeNode, true, true);
            return;
        }
        initPathTree();
    }

   
   
    



    return {
        //main function to initiate the module
        init: function () {
            initListener();
            initTree();
        }

    };

}();

jQuery(document).ready(function () {
    FileSyncRecycleBin.init();
});