<?php
include_once '../../tpl/permission.php';
?>
<link rel="stylesheet" type="text/css" href="./css/platform/todo_list.css" />

<div class="portlet box blue-hoki">
    <div class="portlet-title">
        <div class="caption">
            <i class="viconfont vicon-xiaoxituisong"></i>消息
        </div>
    </div>
    <div class="portlet-body p-0">
        <div class="todo-list-wrapper">
            <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                <ul class="nav nav-tabs tabs-left" id="message_list_tabs">
                    <li class="active" data-type="total-report">
                        <a class="tab-item" href="#total_report_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">全部</span>
                            <span class="tab-item__num" id="total_report"></span>
                        </a>
                    </li>
                    <li class="" data-type="awaiting-approval-report">
                        <a class="tab-item" href="#awaiting_approval_report_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">待审批</span>
                            <span class="tab-item__num" id="awaiting_approval_report_total"></span>
                        </a>
                    </li>
                    <li class="" data-type="approving-report">
                        <a class="tab-item" href="#approving_report_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">审批中</span>
                            <span class="tab-item__num" id="approving_report_total"></span>
                        </a>
                    </li>
                    <li class="" data-type="completed-report">
                        <a class="tab-item" href="#completed_report_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">已完成</span>
                            <span class="tab-item__num" id="completed_report_total"></span>
                        </a>
                    </li>
                    <li class="" data-type="rejected-report">
                        <a class="tab-item" href="#rejected_report_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">已驳回</span>
                            <span class="tab-item__num" id="rejected_report_total"></span>
                        </a>
                    </li>
                    <li class="" data-type="withdrawn-report">
                        <a class="tab-item" href="#withdrawn_report_tabpane" data-toggle="tab" aria-expanded="true">
                            <span class="tab-item__text">已撤回</span>
                            <span class="tab-item__num" id="withdrawn_report_total"></span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-10 col-sm-10 col-xs-10 tab-content todo-list-content">
                <div class="tab-pane fade in active" id="total_report_tabpane">
                    <div class="todo-list-cards" id="total_report_cards"></div>
                </div>
                <div class="tab-pane fade in" id="awaiting_approval_report_tabpane">
                    <div class="todo-list-cards" id="awaiting_approval_report_cards"></div>
                </div>
                <div class="tab-pane fade in" id="approving_report_tabpane">
                    <div class="todo-list-cards" id="approving_report_cards"></div>
                </div>
                <div class="tab-pane fade in" id="completed_report_tabpane">
                    <div class="todo-list-cards" id="completed_report_cards"></div>
                </div>
                <div class="tab-pane fade in" id="rejected_report_tabpane">
                    <div class="todo-list-cards" id="rejected_report_cards"></div>
                </div>
                <div class="tab-pane fade in" id="withdrawn_report_tabpane">
                    <div class="todo-list-cards" id="withdrawn_report_cards"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript" src="./scripts/platform/message_center.js"></script>
