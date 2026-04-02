<?php
include_once '../../tpl/permission.php';
?>
<link rel="stylesheet" type="text/css" href="./css/platform/todo_list.css" />

<div class="portlet box blue-hoki">
    <div class="portlet-title">
        <div class="caption">
            <i class="viconfont vicon-daiban"></i><?php echo $LANG['UI_PLATFORM_TODO_LIST'] ?>
        </div>
    </div>
    <div class="portlet-body p-0">
        <div class="todo-list-wrapper">
            <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                <ul class="nav nav-tabs tabs-left" id="todo_list_tabs">
                    <li class="active" data-type="awaiting-approval-report">
                        <a class="tab-item" href="#awaiting_approval_report_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">待审批报告</span>
                            <span class="tab-item__num" id="awaiting_approval_report_total"></span>
                        </a>
                    </li>
                    <li class="" data-type="awaiting-initiate-report">
                        <a class="tab-item" href="#awaiting_initiate_report_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">待发起报告</span>
                            <span class="tab-item__num" id="awaiting_initiate_report_total"></span>
                        </a>
                    </li>
                    <li class="" data-type="awaiting-approval-plan">
                        <a class="tab-item" href="#awaiting_approval_plan_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">待审批方案</span>
                            <span class="tab-item__num" id="awaiting_approval_plan_total"></span>
                        </a>
                    </li>
                    <li class="" data-type="awaiting-initiate-plan">
                        <a class="tab-item" href="#awaiting_initiate_plan_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">待发起方案</span>
                            <span class="tab-item__num" id="awaiting_initiate_plan_total"></span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-10 col-sm-10 col-xs-10 tab-content todo-list-content">
                <div class="tab-pane fade in active" id="awaiting_approval_report_tabpane">
                    <div class="todo-list-cards" id="awaiting_approval_report_cards"></div>
                </div>
                <div class="tab-pane fade in" id="awaiting_initiate_report_tabpane">
                    <div class="todo-list-cards" id="awaiting_initiate_report_cards"></div>
                </div>
                <div class="tab-pane fade in" id="awaiting_approval_plan_tabpane">
                    <div class="todo-list-cards" id="awaiting_approval_plan_cards"></div>
                </div>
                <div class="tab-pane fade in" id="awaiting_initiate_plan_tabpane">
                    <div class="todo-list-cards" id="awaiting_initiate_plan_cards"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="./scripts/platform/todo_list.js"></script>
