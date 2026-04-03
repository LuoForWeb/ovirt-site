<?php include_once '../../../tpl/permission.php'; ?>
<link type="text/css" rel="stylesheet" href="./plugins/table/css/table.css" />
<div class="vinchin-wrapper display-hide">
    <div class="vinchin-wrapper__title">
        <div class="caption">
            <span class="caption-box">
                <span class="caption-box__text"  data-i18n="">新建备份任务</span>
            </span>
        </div>
    </div>
    <div class="vinchin-wrapper__content">
        <form  class="form backup-form needs-validation" novalidate>
            <ul class="nav nav-stepper">
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-center" href="#step-1">
                        <div class="item-wrapper">
                            <div class="item-wrapper__icon">
                                <span class="num">1</span>
                            </div>
                            <div class="item-wrapper__label">
                                <h3 class="wizard-title">
                                    备份源
                                    <i class="viconfont vicon-gou"></i>
                                </h3>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-center" href="#step-2">
                        <div class="item-wrapper">
                            <div class="item-wrapper__icon">
                                <span class="num">2</span>
                            </div>
                            <div class="item-wrapper__label">
                                    <h3 class="wizard-title">
                                    备份目的地
                                    <i class="viconfont vicon-gou"></i>
                                </h3>
                            
                            </div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-center" href="#step-3">
                        <div class="item-wrapper">
                            <div class="item-wrapper__icon">
                                <span class="num">3</span>
                            </div>
                            <div class="item-wrapper__label">
                                <h3 class="wizard-title">
                                    备份策略
                                    <i class="viconfont vicon-gou"></i>
                                </h3>
                            
                            </div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-center " href="#step-4">
                        <div class="item-wrapper">
                            <div class="item-wrapper__icon">
                                <span class="num">4</span>
                            </div>
                            <div class="item-wrapper__label">
                                <h3 class="wizard-title">
                                    确认配置
                                    <i class="viconfont vicon-gou"></i>
                                </h3>
                            
                            </div>
                        </div>
                    </a>
                </li>
            </ul>


            <div class="tab-content backup-content">
                <div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
                    <div class="row hp-100">                        
                        <div class="tree-wrapper " id="tree-wrapper">
                            <div class="tree-wrapper__toolbar">
                                <label class="tree-wrapper__title wp-100">选择备份源</label>
                                <div class="row">
                                    <div class="tree-wrapper__filter">
                                            <select class="form-control select2 wp-100" data-placeholder="请选择一项">
                                                <option></option>
                                                <option value="1">VMware vSphere</option>
                                                <option value="2">Openstack</option>
                                            </select>
                                    </div>
                                    <button type="button" class="btn btn-icon  btn-icon-secondary-primary">
                                        <i class="viconfont vicon-shaixuan"></i>
                                    </button>
                                </div>

                                <div class="search input-group mr12 tree-wrapper__search">
                                    <input class="customSearch" autocomplete="off" type="text"  maxlength="64" placeholder="对象名">
                                    <div class="position0" >
                                        <button type="button" class="b-btn lab_tableclear clear position0 hide"><i class="icon-close-small"></i></button>
                                    </div>
                                    <div class="positionL0" >
                                        <button type="button" class="b-btn search-btn"><i class="icon-search"></i></button>
                                    </div>
                                </div>


                            </div>
                            <div class="tree-wrapper__tree hover-scroll-y">
                                <div class="tree-wrapper__tree__content">
                                    <ul id="vm-tree" class="ztree bd1de5"></ul>
                                </div>
                            </div>
                        </div>

                        <div class="resizer" id="resizer1">
                            <div class="resizer-line"></div>
                        </div>

                        <div class="table-wrapper" id="table-wrapper">
                        
                            <div class="table-toolbar-wrapper">
                            <!-- BEGIN LEFT TOOLBAR -->
                                <div class="table-toolbar-wrapper__left">
                                    <div class="table-toolbar-wrapper__title">
                                        <label>已选备份源</label>
                                        <span>(共选中105台虚拟机)</span>
                                    </div>
                                    <button type="button" class="btn btn-icon  btn-icon-secondary-primary">
                                        <i class="viconfont vicon-shaixuan"></i>
                                    </button>
                                </div>
                                <!-- END LEFT TOOLBAR -->

                                <!-- BEGIN RIGHT TOOLBAR -->
                                <div class="table-toolbar-wrapper__right">
                                    <div class="toolbar-buttons-wrapper">

                                    </div>
                                </div>
                                <!-- END RIGHT TOOLBAR -->
                            </div>

                            <div class="table-content-wrapper">
                                <table id="common_table"></table>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
                    <div class="row hp-100">
                        <div class="tree-wrapper" id="storage-wrapper">
                            <div class="tree-wrapper__toolbar">
                                <label class="tree-wrapper__title wp-100">选择目标存储</label>
                                <div class="row">
                                    <div class="tree-wrapper__filter">
                                    </div>     
                                </div>

                                <div class="tree-wrapper__search">
                                   
                                </div>


                            </div>
                            <div class="tree-wrapper__tree hover-scroll-y">
                                <div class="tree-wrapper__tree__content">
                                    <ul id="storage-tree" class="ztree bd1de5"></ul>
                                </div>
                            </div>
                        </div>

                        <div class="resizer" id="resizer2">
                            <div class="resizer-line"></div>
                        </div>

                        <div class="tree-wrapper" id="node-wrapper">
                            <div class="tree-wrapper__toolbar">
                                <label class="tree-wrapper__title wp-100">选择目标节点</label>
                                <div class="row">
                                    <div class="tree-wrapper__filter">
                                    </div>     
                                </div>

                                <div class="tree-wrapper__search">
                                   
                                </div>


                            </div>
                            <div class="tree-wrapper__tree hover-scroll-y">
                                <div class="tree-wrapper__tree__content">
                                    <ul id="node-tree" class="ztree bd1de5"></ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
                    <div class="tabs-vertical-wrapper hp-100">
                        <ul class="nav nav-tabs-vertical">
                            <li class="nav-item mt-20">
                                <a class="nav-link active" data-bs-toggle="tab" href="#common-tab">通用策略</a>
                            </li>
                            <li class="nav-item ">
                                <a class="nav-link" data-bs-toggle="tab" href="#transfer-tab">传输策略</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#script-tab">脚本配置</a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#advanced-tab">高级配置</a>
                            </li>
                        </ul>
                        <div class="tab-content p-20" >
                            <div class="tab-pane fade active show" id="common-tab" role="tabpanel">

                            </div>
                            <div class="tab-pane fade" id="transfer-tab" role="tabpanel">

                            </div>
                            <div class="tab-pane fade" id="script" role="tabpanel">

                            </div>
                               <div class="tab-pane fade" id="advanced-tab" role="tabpanel">
                        
                               </div>
                        </div>
                    
                    </div>

                </div>
                <div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-4">
                    <div class="tab-pane__body">
                        <div class="tab-pane__body__form">
                            
                            <div class="confirm-content">
                                <h3 class="confirm-content__title">任务名</h3>           
                                <div class="confirm-content__body">
                                    <div class="form-group">
                                        <label class="confirm-content__label">任务名</label>
                                        <div class="confirm-content__value input-icon">
                                            <i class="fa"></i>
                                            <input type="text" class="form-control" maxlenth="64" value="VMware vSphere备份6" >
                                            <span class="help-block ">您可以修改默认的任务名</span>
                                        </div>
                                    </div>
                                    
                                </div>                        
                            </div>

                            <div class="confirm-content">
                                <h3 class="confirm-content__title">备份源</h3>
                                <div class="confirm-content__body">
                                    <div class="form-group">
                                        <label class="confirm-content__label">备份源</label>
                                        <div class="confirm-content__value">
                                            VMware vSphere备份192.168.3.20/Datacenter
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="confirm-content__label">自动加入备份</label>
                                        <div class="confirm-content__value">
                                            <span class="badge badge-default">关闭</span>
                                        </div>
                                    </div>
                                </div>         
                            </div>

                            <div class="confirm-content">
                                <h3 class="confirm-content__title">目的地</h3>
                                <div class="confirm-content__body">
                                     <div class="form-group">
                                        <label class="confirm-content__label">目标存储</label>
                                        <div class="confirm-content__value">
                                            本地磁盘1(192.168.45.200, 本地磁盘, 总容量: 149.92 GB, 可用空间: 91.25 GB)
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="confirm-content__label">目标节点</label>
                                        <div class="confirm-content__value">
                                            localhost.localdomain(192.168.45.200)
                                        </div>
                                    </div>
                                
                                </div>
                            </div>

                            <div class="confirm-content strategy-content">
                               
                                <div class="confirm-content__strategy">
                                    <h3 class="confirm-content__title">备份策略</h3>
                                     <div class="confirm-content__body"> 
                                        <div class="form-group strategy-group__title">                                   
                                            <span class="decoration me-4"></span>
                                            <span class="strategy-group__title__text">时间策略</span>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">备份方式</label>
                                            <div class="confirm-content__value">
                                                按策略备份
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">时间策略</label>
                                            <div class="confirm-content__value">
                                                完全备份 (每周5, 18:13:00 开始, 不滚动),
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">完全备份补偿</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                </div>  
                                
                                <div class="confirm-content__strategy">
                                     <h3 class="confirm-content__title"></h3>
                                     <div class="confirm-content__body">
                                        
                                        <div class="form-group strategy-group__title">                                   
                                            <span class="decoration me-4"></span>
                                            <span class="strategy-group__title__text">存储策略</span>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">重复数据删除</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">压缩存储</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-success">开启</span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">压缩等级</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-default">极速压缩</span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">数据加密</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>

                                <div class="confirm-content__strategy">
                                    <h3 class="confirm-content__title"></h3>
                                     <div class="confirm-content__body">
                                        <div class="form-group strategy-group__title">                                   
                                            <span class="decoration me-4"></span>
                                            <span class="strategy-group__title__text">传输策略</span>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">传输模式</label>
                                            <div class="confirm-content__value">
                                                NBD传输
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">传输压缩</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">异步传输</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">传输线程</label>
                                            <div class="confirm-content__value">
                                                3
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">代理</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                </div>

                                <div class="confirm-content__strategy">
                                    <h3 class="confirm-content__title"></h3>
                                     <div class="confirm-content__body">            
                                        <div class="form-group strategy-group__title">                                   
                                            <span class="decoration me-4"></span>
                                            <span class="strategy-group__title__text">保留策略</span>
                                        
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">保留类型</label>
                                            <div class="confirm-content__value">
                                                按备份点保留
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">保留方式</label>
                                            <div class="confirm-content__value">
                                                按备份点保留
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">保留值</label>
                                            <div class="confirm-content__value">
                                                30
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">GFS保留策略</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                </div>

                                <div class="confirm-content__strategy">
                                    <h3 class="confirm-content__title"></h3>
                                     <div class="confirm-content__body">
                                        <div class="form-group strategy-group__title">                                   
                                            <span class="decoration me-4"></span>
                                            <span class="strategy-group__title__text">高级配置</span>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">快照模式</label>
                                            <div class="confirm-content__value">
                                                串行
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">提前创建快照</label>
                                            <div class="confirm-content__value">
                                            <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">静默快照</label>
                                            <div class="confirm-content__value">
                                            <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">增量模式</label>
                                            <div class="confirm-content__value">
                                                CBT
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">深度有效提取</label>
                                            <div class="confirm-content__value">
                                            <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="confirm-content__label">忽略节点资源限制</label>
                                            <div class="confirm-content__value">
                                                <span class="badge badge-default">关闭</span>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>



<script type="text/javascript" src="./assets/global/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-smartWizard/js/jquery.smartWizard.min.js"></script>

<script type="text/javascript" src="./plugins/table/js/table.js" ></script>
<script type="text/javascript" src="./platform/data-management/verification/js/add-verification-job.js"></script>