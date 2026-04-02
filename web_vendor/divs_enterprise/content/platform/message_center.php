<?php
include_once '../../tpl/permission.php';
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<!-- END PAGE LEVEL STYLES -->

<!-- <h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="homepage" href="./content/platform/databackup_center_vinchin.php">
            <span><?php echo $LANG['UI_HOMEPAGE_PAGE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent">消息</span>
</h3> -->

<div class="row" style="height: 100%;">
    <div class="col-md-12" style="height: 100%;">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-xiaoxituisong"></i>消息
                </div>
            </div>
            <div class="portlet-body" style="display: flex;">
                <div class="side-tab"
                    style="height: 100%;width: 15%;border-right: 1px solid #EBEBEB;padding: 0 16px 0 0;">
                    <div class="all tab-items active">
                        <span>全部</span>
                        <span class="count"></span>
                    </div>
                    <div class="pending tab-items" data-status="0">
                        <span>待审批</span>
                        <span class="count"></span>
                    </div>
                    <div class="approving tab-items" data-status="2">
                        <span>审批中</span>
                        <span class="count"></span>
                    </div>
                    <div class="archived tab-items" data-status="1">
                        <span>已完成</span>
                        <span class="count"></span>
                    </div>
                    <div class="rejected tab-items" data-status="3">
                        <span>已驳回</span>
                        <span class="count"></span>
                    </div>
                    <div class="revoke tab-items" data-status="4">
                        <span>已撤回</span>
                        <span class="count"></span>
                    </div>
                </div>
                <div class="content-right" style="width: 100%;padding: 10px 10px 10px 20px;overflow:auto">
                    <div class="msg-list" style="width: 100%;height: 98%"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript" src="./scripts/platform/message_center.js"></script>
