<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE TITLE-->
<link href="./assets/global/css/components.min.css" id="style_components" rel="stylesheet" type="text/css"/>
<h3 class="page-title">
<?php echo $LANG['UI_DRILLS_REPORT_DETAIL']?> <small><?php echo $LANG['UI_DRILLS_RECOVER_REPORT_DETAIL']?></small>
</h3>
<!-- END PAGE TITLE-->
<input id="report_uuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
<!-- END PAGE HEADER-->
<div class="row widget-row">
    <div class="col-md-3">
        <!-- BEGIN WIDGET THUMB -->
        <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
            <h4 class="widget-thumb-heading"><?php echo $LANG['UI_DRILLS_RESULT']?></h4>
            <div class="widget-thumb-wrap">
                <i class="widget-thumb-icon bg-blue fa fa-flag"></i>
                <div class="widget-thumb-body">
                    <span class="widget-thumb-subtitle"><?php echo $LANG['UI_PUBLIC_STATUS']?></span>
                    <span class="widget-thumb-body-stat tresult"></span>
                </div>
            </div>
        </div>
        <!-- END WIDGET THUMB -->
    </div>
    <div class="col-md-3">
        <!-- BEGIN WIDGET THUMB -->
        <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
            <h4 class="widget-thumb-heading"><?php echo $LANG['UI_DRILLS_HOST']?></h4>
            <div class="widget-thumb-wrap">
                <i class="widget-thumb-icon bg-green iconfont icon-xinicon02"></i>
                <div class="widget-thumb-body">
                    <span class="widget-thumb-subtitle"><?php echo $LANG['UI_DRILLS_NUMBER']?></span>
                    <span class="widget-thumb-body-stat thosts" data-counter="counterup" data-value=""></span>
                </div>
            </div>
        </div>
        <!-- END WIDGET THUMB -->
    </div>
    <div class="col-md-3">
        <!-- BEGIN WIDGET THUMB -->
        <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
            <h4 class="widget-thumb-heading"><?php echo $LANG['UI_DRILLS_VM']?></h4>
            <div class="widget-thumb-wrap">
                <i class="widget-thumb-icon bg-red fa fa-desktop"></i>
                <div class="widget-thumb-body">
                    <span class="widget-thumb-subtitle"><?php echo $LANG['UI_DRILLS_NUMBER']?></span>
                    <span class="widget-thumb-body-stat tvms" data-counter="counterup" data-value=""></span>
                </div>
            </div>
        </div>
        <!-- END WIDGET THUMB -->
    </div>
    <div class="col-md-3">
        <!-- BEGIN WIDGET THUMB -->
        <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 bordered">
            <h4 class="widget-thumb-heading"><?php echo $LANG['UI_DRILLS_ENVIRONMENT_TIME']?></h4>
            <div class="widget-thumb-wrap">
                <i class="widget-thumb-icon bg-purple fa fa-clock-o"></i>
                <div class="widget-thumb-body">
                    <span class="widget-thumb-subtitle"><?php echo $LANG['UI_DRILLS_SECOND']?></span>
                    <span class="widget-thumb-body-stat ttimes" data-counter="counterup" data-value=""></span>
                </div>
            </div>
        </div>
        <!-- END WIDGET THUMB -->
    </div>
    
</div>


<div class="row">
    <div class="col-md-6">
        <!-- BEGIN WIDGET THUMB -->
        <div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
            <h4 class="widget-thumb-heading"><?php echo $LANG['UI_DRILLS_VM_SOURCE_OCCUPY']?></h4>
            <div id="vmresource" class="height400">
			</div>
        </div>
        <!-- END WIDGET THUMB -->
    </div>
    <div class="col-md-6">
        <!-- BEGIN WIDGET THUMB -->
        <div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
            <h4 class="widget-thumb-heading"><?php echo $LANG['UI_DRILLS_VM_COUNT']?></h4>
            <div id="vmsuccess" class="height400">
			</div>
        </div>
        <!-- END WIDGET THUMB -->
    </div>
</div>
                    
<div class="row">
	<div class="col-xs-12">
		<table class="table table-striped table-hover tablevalignm">
		<thead>
		<tr>
			<th>
				 #
			</th>
			<th>
				<?php echo $LANG['UI_VCENTER_HOST_NAME']?>
			</th>
			<th>
				<?php echo $LANG['UI_PUBLIC_IP_ADDRESS']?>
			</th>
			<th class="hidden-480">
				<?php echo $LANG['UI_DRILLS_PRODUCT_NETWORK']?>
			</th>
			<th class="hidden-480">
				<?php echo $LANG['UI_DRILLS_ISOLATED_NETWORK']?>
			</th>
		</tr>
		</thead>
		<tbody id="hosttbody">
		</tbody>
		</table>
	</div>
</div>
                    
<!-- BEGIN : LISTS -->
<div class="row">
    <div class="col-lg-12">
        <div class="portlet light portlet-fit bordered">
            <div class="portlet-body" id="planlist">
                <div class="mt-element-list">
                    <div class="mt-list-head list-todo blue-hoki">
                        <div class="list-head-title-container">
                            <h3 class="list-title"><?php echo $LANG['UI_EMERGENCY_ALL_PLAN']?></h3>
                            <div class="list-head-count">
                                <div class="list-head-count-item">
                                    <i class="iconfont icon-bumen1"></i> <?php echo $LANG['UI_EMERGENCY_GROUP_PLAN']?>: 15</div>
                                <div class="list-head-count-item">
                                    <i class="iconfont icon-bumen"></i> <?php echo $LANG['UI_EMERGENCY_CHILD_PLAN']?>: 34</div>
                                <div class="list-head-count-item">
                                    <i class="fa fa-desktop"></i> <?php echo $LANG['UI_VCENTER_MACHINE']?>: 34</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-list-container list-todo">
                        <div class="list-todo-line red"></div>
                        <ul>
                            <li class="mt-list-item">
                                <div class="list-todo-icon bg-white font-blue-steel">
                                    <i class="iconfont icon-bumen1"></i> <?php echo $LANG['UI_EMERGENCY_GROUP_PLAN']?>1
                                </div>
                                <div class="list-todo-item blue-steel">
                                    <a class="list-toggle-container font-white" data-toggle="collapse" href="#task-1-2" aria-expanded="false">
                                        <div class="list-toggle done uppercase">
                                            <div class="list-toggle-title bold">
                                                <i class="iconfont icon-bumen"></i> <?php echo $LANG['UI_EMERGENCY_CHILD_PLAN']?>1</div>
                                            <div class="badge badge-default pull-right bold">3</div>
                                        </div>
                                    </a>
                                    <div class="task-list panel-collapse collapse in" id="task-1-2">
                                        <ul>
                                            <li class="task-list-item done">
                                                <div class="task-icon icon30">
                                                     <i class="fa fa-desktop font-green-seagreen"></i>
                                                </div>
                                                <div class="task-status icon30">
                                                     <i class="fa fa-check font-green-seagreen"></i>
                                                </div>
                                                <div class="task-content">
                                                    <h4 class="bold">
                                                        Red Hat Enterprise Linux 6 64-bit
                                                    </h4>
                                                    <p><?php echo $LANG['UI_DRILLS_CPU']?>: 2/2<br><?php echo $LANG['UI_DRILLS_MEMORY']?>: 2048MB<br><?php echo $LANG['UI_DRILLS_SIZE']?>: 2TB<br><?php echo $LANG['UI_RECOVERY_BACKUP_POINT']?>: 2016-12-12 12:15:13<br><?php echo $LANG['UI_DRILLS_DETAIL_PURPOSE_HOST']?>: localhost.localhost(192.168.1.21)</p>
                                                </div>
                                            </li>
                                            <li class="task-list-item done">
                                                <div class="task-icon icon30">
                                                     <i class="fa fa-desktop font-green-seagreen"></i>
                                                </div>
                                                <div class="task-status icon30">
                                                     <i class="fa fa-close font-red"></i>
                                                </div>
                                                <div class="task-content">
                                                    <h4 class="bold">
                                                        Red Hat Enterprise Linux 6 64-bit2
                                                    </h4>
                                                    <p><?php echo $LANG['UI_DRILLS_CPU']?>: 2/2<br><?php echo $LANG['UI_DRILLS_MEMORY']?>: 2048MB<br><?php echo $LANG['UI_DRILLS_SIZE']?>: 2TB<br><?php echo $LANG['UI_RECOVERY_BACKUP_POINT']?>: 2016-12-12 12:15:13<br><?php echo $LANG['UI_DRILLS_DETAIL_PURPOSE_HOST']?>: localhost.localhost(192.168.1.21)</p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="list-todo-item blue-steel">
                                    <a class="list-toggle-container font-white" data-toggle="collapse" href="#task-1-3" aria-expanded="false">
                                        <div class="list-toggle done uppercase">
                                            <div class="list-toggle-title bold"><?php echo $LANG['UI_EMERGENCY_CHILD_PLAN']?>2</div>
                                            <div class="badge badge-default pull-right bold">3</div>
                                        </div>
                                    </a>
                                    <div class="task-list panel-collapse collapse in" id="task-1-3">
                                        <ul>
                                            <li class="task-list-item done">
                                                <div class="task-icon icon30">
                                                     <i class="fa fa-desktop font-green-seagreen"></i>
                                                </div>
                                                <div class="task-status icon30">
                                                     <i class="fa fa-check font-green-seagreen"></i>
                                                </div>
                                                <div class="task-content">
                                                    <h4 class="bold">
                                                        Red Hat Enterprise Linux 6 64-bit
                                                    </h4>
                                                    <p><?php echo $LANG['UI_DRILLS_CPU']?>: 2/2<br><?php echo $LANG['UI_DRILLS_MEMORY']?>: 2048MB<br><?php echo $LANG['UI_DRILLS_SIZE']?>: 2TB<br><?php echo $LANG['UI_RECOVERY_BACKUP_POINT']?>: 2016-12-12 12:15:13<br><?php echo $LANG['UI_DRILLS_DETAIL_PURPOSE_HOST']?>: localhost.localhost(192.168.1.21)</p>
                                                </div>
                                            </li>
                                            <li class="task-list-item done">
                                                <div class="task-icon icon30">
                                                     <i class="fa fa-desktop font-green-seagreen"></i>
                                                </div>
                                                <div class="task-status icon30">
                                                     <i class="fa fa-close font-red"></i>
                                                </div>
                                                <div class="task-content">
                                                    <h4 class="bold">
                                                        Red Hat Enterprise Linux 6 64-bit2
                                                    </h4>
                                                    <p><?php echo $LANG['UI_DRILLS_CPU']?>: 2/2<br><?php echo $LANG['UI_DRILLS_MEMORY']?>: 2048MB<br><?php echo $LANG['UI_DRILLS_SIZE']?>: 2TB<br><?php echo $LANG['UI_RECOVERY_BACKUP_POINT']?>: 2016-12-12 12:15:13<br><?php echo $LANG['UI_DRILLS_DETAIL_PURPOSE_HOST']?>: localhost.localhost(192.168.1.21)</p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- END : LISTS -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/echarts.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/counterup/jquery.waypoints.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
<script src="./scripts/plugins/jquery/jquery.planList.js" type="text/javascript"></script>
<script src="./scripts/platform/manoeuvre/report_details.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->	