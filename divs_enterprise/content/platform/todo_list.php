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
    <span class="curent">待办事项</span>
</h3> -->

<div class="row" style="height: 100%;">
    <div class="col-md-12" style="height: 100%;">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-daiban"></i><?php echo $LANG['UI_PLATFORM_TODO_LIST']?>
                </div>
            </div>
            <div class="portlet-body" style="display: flex;">
                <div class="side-tab"
                     style="height: 100%;width: 15%;border-right: 1px solid #EBEBEB;padding: 0 16px 0 0;">
                    <div class="pending tab-items active">
                        <span><?php echo $LANG['UI_PLATFORM_TODO_LIST_WAIT_APPROVAL']?></span>
                        <span class="count"></span>
                    </div>
                    <div class="waiting tab-items" data-status="9">
                        <span><?php echo $LANG['UI_PLATFORM_TODO_LIST_WAIT_REPORT']?></span>
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


<script type="text/javascript" src="./scripts/platform/todo_list.js"></script>
