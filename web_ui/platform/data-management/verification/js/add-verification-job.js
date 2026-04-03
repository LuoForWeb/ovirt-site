var addVerificationJob = (() => {
    let data = {srcInfo:{}, backupInfo:{}, highInfo:{}};
    let STEP_DIRECTION = ''; // 记录步骤导航栏stepDirection值
    let CURRENT_STEP_INDEX = 0; // 记录步骤导航栏currentStepIndex值
    let editFlag =false;

    // <------------------------------ BEIGIN STEP 1 zTree ------------------------------------>



    // <------------------------------ END STEP 1 zTree ---------------------------------------->


    // <------------------------------ BEIGIN STEP 3 TIME STRATEGY ----------------------------->


    


    // <------------------------------ END STEP 3 TIME STRATEGY --------------------------------->


    // <------------------------------ BEIGIN STEP 3 SPEED LIMIT STRATEGY ----------------------->

    
   

   

    // <------------------------------- END STEP 3 SPEED LIMIT STRATEGY ------------------------------->


    // <------------------------------- BEGIN STEP 3 STORAGE STRATEGY --------------------------------->

    

    // <------------------------------- END STEP 3 STORAGE STRATEGY ----------------------------------->

    // <------------------------------- BEGIN STEP 3 RESERVE STRATEGY --------------------------------->

    

    // <------------------------------- END STEP 3 RESERVE STRATEGY ---------------------------------->
    /**
     * 初始化监听事件
     */
    const initListener = () => {
        initResizable('tree-wrapper','table-wrapper','resizer1');
        initResizable('storage-wrapper','node-wrapper','resizer2');
    };


   

    /**
     * 备份源校验
     * @returns true
     */
    const step1Valid = (callback) => {
       
        return true;
    };

    /**
     * 备份目的地校验
     * @returns true
     */
    const step2Valid = () => {
        return true;
    };

   
    /**
     * 备份策略校验
     * @returns true
     */
    const step3Valid = () => {
    
        return true;
    };

    /**
     * 初始化步骤导航栏
     */
    const initWizard = () => {
        $('.backup-form').smartWizard({
            selected: 0,
            autoAdjustHeight: true,
            enableUrlHash: false,
            // transition: {
            //     animation: 'fade',
            //     speed: '400'
            // },
            toolbar: {
                position: 'bottom', // none|top|bottom|both
                showNextButton: true, // show/hide a Next button
                showPreviousButton: true, // show/hide a Previous button
                extraHtml: '<button class="btn btn-primary sw-btn-submit sw-btn" type="button" id="btn-finish" >提交<i class="viconfont vicon-xiayibu"></i></button>', // Extra html to show on toolbar
               
            },
            lang: {
                next: '下一步',
                previous: '上一步'
            }
        });
        
        $('.sw-btn-prev').html('<i class="viconfont vicon-shangyibu"></i>上一步').css('visibility', 'hidden');
        $('.sw-btn-next').html('下一步<i class="viconfont vicon-xiayibu"></i>');
        $('.sw-btn-submit').css('visibility', 'hidden');

        // 初始化监听 下一步 按钮click事件
        $('.sw-btn-next').on('click', () => {

            if (STEP_DIRECTION === 'forward') {
                switch (CURRENT_STEP_INDEX) {
                    case 0:
                        break;
                    case 1:
                        break;
                    case 2:


                        break;
                    default:
                        break;
                }
            }
        });


        $('.backup-form').on('leaveStep', function(e, anchorObject, currentStepIndex, nextStepIndex, stepDirection, callback) {

            STEP_DIRECTION = stepDirection;
            CURRENT_STEP_INDEX = currentStepIndex;
            let total = $('.nav-stepper').find('li').length;//总共的步骤数
            let current = nextStepIndex + 1;      //当前步骤
            //如果第一步 上一步按钮隐藏
            if (current == 1) {
                $('.backup-form').find('.sw-btn-prev').css('visibility', 'hidden');
                $('.backup-form').find('.sw-btn-next').addClass('next-btn-margin-left');
            } else {
                $('.backup-form').find('.sw-btn-prev').css('visibility', 'visible');
                $('.backup-form').find('.sw-btn-next').removeClass('next-btn-margin-left');
            }

            //处理最后一步
            if (current >= total) {
                $('.backup-form').find('.sw-btn-next').hide();
                $('.backup-form').find('.sw-btn-submit').css('visibility', 'visible');
            } else {
                $('.backup-form').find('.sw-btn-next').show();
                if (editFlag){
                    $('.backup-form .sw-btn-submit').css('visibility', 'visible');
                }else{
                    $('.backup-form .sw-btn-submit').css('visibility', 'hidden');
                }
            }

            if (stepDirection === 'forward') {
                switch (currentStepIndex) {
                    case 0:
                        // Step One 校验
                        if (step2Valid()) {
                            return true;
                        }
                        return false;
                        break;
                    case 1:
                        // Step Two 校验
                        if (step2Valid()) {
                            return true;
                        }
                        break;
                    case 2:
                        // Step Three 校验
                        if (step3Valid()) {
                            return true;
                        }
                        break;
                    default:
                        break;
                }
            }else if (stepDirection === 'backward') {
                
                return true;
            }

            return false;
        });

        if (editFlag){
            $('.backup-form .sw-btn-submit').click(submit).css('visibility', 'visible');
        }else{
            $('.backup-form .sw-btn-submit').click(submit).css('visibility', 'hidden');
        }

    }

    var initObjectTree = () => {
       const treeNode = [
            {
                'id':1,
                'pId':0,
                'name':'VMware vSphere',
                'title':'VMware vSphere',
                'nocheck':false,
                'iconSkin':'vm_vmware',
                'open':false,
            },
            {
                'id':'bc65f17d-e1e6-4456-a73f-d60a6cdf0aba',
                'pId':1,
                'name':'192.168.1.130',
                'title':'192.168.1.130',
                'isParent':false,
                'nocheck':false,
                'iconSkin':'vm_vmware_vcenter',
                'open':false,
            },
            {
                'id':15,
                'pId':0,
                'name':'OpenStack',
                'title':'OpenStack',
                'nocheck':false,
                'iconSkin':'vm_openstack_kvm',
                'open':false,
            },
            {
                'id':'a9294801-094e-1f3e-6e95-4953df286cd3',
                'pId':15,
                'name':'http:\/\/192.168.64.39(\u6d4b\u8bd5\u7ec4)',
                'title':'http:\/\/192.168.64.39(\u6d4b\u8bd5\u7ec4)',
                'isParent':false,
                'nocheck':false,
                'iconSkin':'vm_openstack_kvm_vcenter',
                'open':false,
            },
            {
                'id':'a9294801-094e-1f3e-6e95-4953df286cd6',
                'pId':15,
                'name':'http:\/\/192.168.64.36(\u6d4b\u8bd5\u7ec4)',
                'title':'http:\/\/192.168.64.36(\u6d4b\u8bd5\u7ec4)',
                'isParent':false,
                'nocheck':false,
                'iconSkin':'vm_openstack_kvm_vcenter',
                'open':false,
            }
        ];

        let setting = {
            check: {
                enable: true,
                nocheckInherit: false,
                chkboxType: {
                    'Y': 'ps',
                    'N': 'ps'
                }
            },
            data: {
                simpleData: {
                    enable: true,
                    idKey: 'id',
                    pIdKey: 'pId',
                    rootPId: 0
                },
                key: {
                    title: 'title'
                }
            },
            view: {
                fontCss: getFontCss
            }
        };

        let zTree = $.fn.zTree.init($('#vm-tree'), setting, treeNode); 

    }
	
    var getFontCss = function(treeId, treeNode) {
        var css = {color:'#333', 'font-weight':'normal'};
        if (treeNode.inbackup) {
            css = {color:'green', 'font-weight':'bold'};
        }
        if (treeNode.highlight) {
            //搜索使用的样式
            css = {color:'#A60000', 'font-weight':'bold'};
        }
        return css;
    }

     var initStorageTree = () => {
       const treeNode = [
            {
                'id':1,
                'pId':0,
                'name':'存储资源池',
                'title':'存储资源池',
                'nocheck':false,
                'iconSkin':'',
                'open':true,
            },
            {
                'id':'bc65f17d-e1e6-4456-a73f-d60a6cdf0aba',
                'pId':1,
                'name':'云存储1',
                'title':'云存储1',
                'isParent':false,
                'nocheck':false,
                'iconSkin':'',
                'open':false,
            },
            {
                'id':'ca65f17d-e1e6-4456-a73f-d60a6cdf0aba',
                'pId':1,
                'name':'本地目录1',
                'title':'本地目录1',
                'isParent':false,
                'nocheck':false,
                'iconSkin':'',
                'open':false,
            },
            {
                'id':15,
                'pId':0,
                'name':'存储设备',
                'title':'存储设备',
                'nocheck':false,
                'iconSkin':'',
                'open':false,
            },
            {
                'id':'a9294801-094e-1f3e-6e95-4953df286cd3',
                'pId':15,
                'name':'云存储1',
                'title':'云存储1',
                'isParent':false,
                'nocheck':false,
                'iconSkin':'vm_openstack_kvm_vcenter',
                'open':false,
            },
            {
                'id':'a9294801-094e-1f3e-6e95-4953df286cd6',
                'pId':15,
                'name':'本地目录1',
                'title':'本地目录1',
                'isParent':false,
                'nocheck':false,
                'iconSkin':'',
                'open':false,
            }
        ];

        let setting = {
            check: {
                enable: true,
                nocheckInherit: false,
                chkboxType: {
                    'Y': 'ps',
                    'N': 'ps'
                }
            },
            data: {
                simpleData: {
                    enable: true,
                    idKey: 'id',
                    pIdKey: 'pId',
                    rootPId: 0
                },
                key: {
                    title: 'title'
                }
            },
            view: {
                fontCss: getFontCss
            }
        };

        let zTree = $.fn.zTree.init($('#storage-tree'), setting, treeNode); 

    }

     var initNodeTree = () => {
      const treeNode = [
            {
                'id':1,
                'pId':0,
                'name':'计算资源池',
                'title':'计算资源池',
                'nocheck':false,
                'iconSkin':'',
                'open':true,
            },
            {
                'id':'bc65f17d-e1e6-4456-a73f-d60a6cdf0aba',
                'pId':1,
                'name':'localhost.localdomain(192.168.28.101)',
                'title':'localhost.localdomain(192.168.28.101)',
                'isParent':false,
                'nocheck':false,
                'iconSkin':'',
                'open':false,
            },
            {
                'id':'ca65f17d-e1e6-4456-a73f-d60a6cdf0aba',
                'pId':1,
                'name':'localhost.localdomain(192.168.28.102)',
                'title':'localhost.localdomain(192.168.28.102)',
                'isParent':false,
                'nocheck':false,
                'iconSkin':'',
                'open':false,
            },
            
        ];

        let setting = {
            check: {
                enable: true,
                nocheckInherit: false,
                chkboxType: {
                    'Y': 'ps',
                    'N': 'ps'
                }
            },
            data: {
                simpleData: {
                    enable: true,
                    idKey: 'id',
                    pIdKey: 'pId',
                    rootPId: 0
                },
                key: {
                    title: 'title'
                }
            },
            view: {
                fontCss: getFontCss
            }
        };

        let zTree = $.fn.zTree.init($('#node-tree'), setting, treeNode); 

    }

    const initDataTable = () => {
        let list = [];
        for(var i=0; i<20; i++) {
            var info = {
                object_name: "虚拟机" + i,
                object_type: "虚拟机",
                dir_path: "123123412312",
                vm_num: 1
            };
            list.push(info);
        }
        let tableOptions = {
            data: list,
            pagination: true, // 启用分页功能（如果有需要）
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                    formatter: function stateFormatter(value, row, index) {

                        if (index === 2) {
                            return {
                                disabled: true
                            };
                        }
                        if (index === 3) {
                            return {
                                checked: true
                            };
                        }
                        if (index === 5) {
                            return {
                                disabled: true,
                                checked: true
                            };
                        }
                        return value;
                    }
                },
                {
                    field: 'object_name',
                    title: '对象名称',
                    sortable: true,
                },
        
                {
                    field: 'object_type',
                    title: '对象类型'
                },
                {
                    field: 'dir_path',
                    title: '对象路径'
                },
                {
                    field: 'vm_num',
                    title: '虚拟机数量'
                },
                {
                    field: '',
                    title: '操作',
                },
               
             
            ]
        };

        $('#common_table').baseTableConfig().init(tableOptions);

    };


    var submit = () => {
        
    }

    return {
        init: () => {
            initListener();
            initWizard();
            initObjectTree();
            initStorageTree();
            initNodeTree();
            initDataTable();
        }
    };
})();

jQuery(document).ready(() => {
    addVerificationJob.init();
});