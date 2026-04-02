<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/hover/hover-min.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<i class="iconfont icon-gongsi"></i> <span id="taskname"><?php echo $LANG['UI_DRILLS_DETAIL_TITLE']?></span>
 <small><i class="iconfont icon-bumen1"></i> <?php echo $LANG['UI_EMERGENCY_GROUP_PLAN']?>: <label id="groupplan"></label></small>
 <small><i class="iconfont icon-bumen"></i> <?php echo $LANG['UI_EMERGENCY_CHILD_PLAN']?>: <label id="childplan"></label></small>
<div class="btn-group pull-right">
	<a href='./content/platform/manoeuvre/task.php' class="ajaxify">
	<button type="button" class="btn btn-sm green-haze">
    <i class="fa fa-arrow-left"></i> <?php echo $LANG['UI_DRILLS_DETAIL_ALL_TASK']?> </button></a>
</div>
</h3>


<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <!-- BEGIN TOP CONTENT-->
    <div class="col-md-12">
        <!-- BEGIN TOP LEFT CONTENT-->
        <div class="col-md-6">
            <input id="task_uuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
            <input id="recovery_mode" value="<?php echo $_GET['mode'];?>" class="display-none"></input>
    		<div class="row">
    			<div class="col-md-6">
    			    <div class="portlet box blue-hoki">
            			<div class="portlet-title">
            				<div class="caption">
            					<i class="fa fa-dashboard"></i><?php echo $LANG['UI_DRILLS_DETAIL_ALL_PROGRESS']?>
            				</div>
            			</div>
            			<div class="portlet-body">
            				<div id="totalprogress" class="height200">
            				</div>
            			</div>
            		</div>
    			</div>
    			<div class="col-md-6">
    			    <div class="portlet box blue-hoki">
            			<div class="portlet-title">
            				<div class="caption">
            					<i class="fa  fa-pie-chart"></i><?php echo $LANG['UI_DRILLS_DETAIL_VM_STATE']?>
            				</div>
            			</div>
            			<div class="portlet-body">
            				<div id="vmsuccess" class="height200">
            				</div>
            			</div>
            		</div>
    			</div>
    		</div>
    		<div class="portlet box blue-hoki">
    			<div class="portlet-title">
    				<div class="caption">
    					<i class="iconfont icon-xinicon02"></i><?php echo $LANG['UI_DRILLS_DETAIL_HOST_MONITOR']?>
    				</div>
    			</div>
    			<div class="portlet-body">
    				<div class="swiper-container swiper-container-host">
                        <div class="swiper-wrapper min-height360" id="hostswiper">
                        </div>
                        <!-- 如果需要分页器 -->
                        <div class="swiper-pagination swiper-pagination-host"></div>
                        
                        <!-- 如果需要导航按钮 -->
<!--                         <div class="swiper-button-prev"></div> -->
<!--                         <div class="swiper-button-next"></div> -->
                        
                        <!-- 如果需要滚动条 -->
<!--                         <div class="swiper-scrollbar"></div> -->
                    </div>
    			</div>
    		</div>
        </div>
        <!-- END TOP LEFT CONTENT-->
        
        <!-- BEGIN TOP RIGHT CONTENT-->
        <div class="col-md-6">
    		<div class="portlet box blue-hoki">
    			<div class="portlet-title">
    				<div class="caption">
    					<i class="fa fa-flag"></i><?php echo $LANG['UI_DRILLS_DETAIL_CURRENT_VM']?>
    				</div>
    			</div>
    			<div class="portlet-body">
    				<div class="row">
    				    <div class="col-md-4 column display-none" id="vmdetailsdiv">
    				        <div class="lh22">
        				        <div class="textellipsis" id="cvmname" title=""></div>
        				        <div><span><i class="fa fa-bolt"></i> <?php echo $LANG['WEB_PLATFORM_DES_INSTANT_RECOVERY']?></span><span class="floatr" id="cvminstant"></span></div>
        				        <div><span><i class="fa fa-play"></i> <?php echo $LANG['UI_VCENTER_MACHINE']?></span><span class="floatr" id="cvmpower"></span></div>
    				        </div>
    				        <div>
    				            <div id="indicatorContainerWrap" class="reladiv marginlb10">
                				    <a id="vmpopovers" class="hvr-float-shadow">
                                        <div id="indicatorContainer" class="reladiv"></div>
                                        <div id="currentvm" class="vmradiain">
                                            <i class="fa fa-desktop"></i>
                                        </div>
                                    </a>
                                </div>
    				        </div>
    				    </div>
    				    <div class="col-md-8 column">
    				        <div class="scroller height200" data-always-visible="1" data-rail-visible="0">
            					<ul class="feeds" id="cvmlogs">
            			        </ul>
            			    </div>
    				    </div>
    				</div>
    			</div>
    		</div>
    		<div class="portlet box blue-hoki">
    			<div class="portlet-title">
    				<div class="caption">
    					<i class="fa fa-desktop"></i><?php echo $LANG['UI_DRILLS_DETAIL_VM_LIST']?>
    				</div>
    			</div>
    			<div class="portlet-body">
    				<!-- Swiper -->
                    <div class="swiper-container swiper-container-vm" style="padding:5px;">
                        <div class="swiper-wrapper min-height360" id="vmswiper">
                        </div>
                        <!-- Add Pagination -->
                        <div class="swiper-pagination swiper-pagination-vm"></div>
                        <!-- Add Arrows -->
<!--                         <div class="swiper-button-next"></div> -->
<!--                         <div class="swiper-button-prev"></div> -->
                    </div>
			    </div>
    		</div>
        </div>
        <!-- END TOP RIGHT CONTENT-->
        
    </div>
    <!-- END TOP CONTENT-->
    
    <!-- BEGIN BUTTON CONTENT-->
	<div class="col-md-12">
        <!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
				    <li class="active ">
						<a href="#log" data-toggle="tab">
						<i class="viconfont vicon-ge_modify font-green-seagreen"></i> <?php echo $LANG['UI_JOB_RUNING_LOG']?> </a>
					</li>
					<li>
						<a href="#vms" data-toggle="tab">
						<i class="fa fa-desktop font-green-seagreen"></i> <?php echo $LANG['UI_VCENTER_MACHINE_LIST']?> </a>
					</li>
					<li>
						<a href="#history" data-toggle="tab">
						<i class="levelchild viconfont vicon-pt_job_historical_task font-green-seagreen"></i> <?php echo $LANG['UI_JOB_HISTORY']?> </a>
					</li>
					<li>
						<a href="#verify" data-toggle="tab">
						<i class="fa fa-check-circle font-green-seagreen"></i> <?php echo $LANG['UI_DRILLS_DETAIL_BUSINESS_VALIDATE']?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content row margin10 min-height300">
				    <div class="tab-pane active" id="log">
				        <div class="scroller min-height300" data-always-visible="1" data-rail-visible="0">
        					<ul class="feeds" id="runninglog">
        					</ul>
        				</div>
				    </div>
				    
					<div class="tab-pane" id="vms">
        				<div class="table-container margin10">
        					<table class="table table-striped table-bordered table-hover" id="vmstable">
        					<thead>
        					<tr role="row" class="heading">
        						<th width="5%">
        							<?php echo $LANG['UI_PUBLIC_NUMBER']?>
        						</th>
        						<th width="15%">
        							<?php echo $LANG['UI_VCENTER_MACHINE_NAME']?>
        						</th>
        						<th width="15%">
        							<?php echo $LANG['UI_DRILLS_DETAIL_PURPOSE_HOST']?>
        						</th>
        						<th width="30%">
        							<?php echo $LANG['UI_DRILLS_DETAIL_THE_PLAN']?>
        						</th>
        						<th width="15%">
        							<?php echo $LANG['UI_DRILLS_DETAIL_BACKUP_TIMEPOINT']?>
        						</th>
        						<th width="10%">
        							<?php echo $LANG['UI_JOB_TOTAL_SIZE']?>
        						</th>
        						<th width="10%">
        							<?php echo $LANG['UI_DRILLS_DETAIL_STATUS']?>
        						</th>
        						
        					</tr>
        					</thead>
        					<tbody>
        					</tbody>
        					</table>
        				</div>
					</div>
					
					<div class="tab-pane" id="history">
						<div class="table-container margin10">
        					<table class="table table-striped table-bordered table-hover" id="historytable">
        					<thead>
        					<tr role="row" class="heading">
        						<th width="5%">
        							<?php echo $LANG['UI_PUBLIC_NUMBER']?>
        						</th>
        						<th width="12%">
        							 <?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>
        						</th>
        						<th width="8%">
        							 <?php echo $LANG['UI_PUBLIC_STATUS']?>
        						</th>
        						<th width="10%">
        							 <?php echo $LANG['UI_DRILLS_DETAIL_VM_SIZE']?>
        						</th>
        						<th width="10%">
        							<?php echo $LANG['UI_DRILLS_DETAIL_DATA_ALL_SIZE']?>
        						</th>
        						<th width="15%">
        							 <?php echo $LANG['UI_JOB_START_TIME']?>
        						</th>
        						<th width="15%">
        							 <?php echo $LANG['UI_JOB_OVER_TIME']?>
        						</th>
        					</tr>
        					</thead>
        					<tbody>
        					</tbody>
        					</table>
        				</div>
					</div>
					<div class="tab-pane" id="verify">
				         
			            <div class="form-group row">
			                <div class="col-md-3">
            			        <select class="bs-select"  data-show-subtext="true" id="verifytype">
            			            <option  data-icon="fa fa-random" value="1"> <?php echo $LANG['UI_DRILLS_DETAIL_PING_VALIDATE']?></option>
                                    <option  data-icon="fa fa-globe" value="2"> <?php echo $LANG['UI_DRILLS_DETAIL_WEB_SERVER_VALIDATE']?></option>
                                </select>
    			            </div> 
    			            <div class="col-md-5">
                        		<input type="text" maxlength="512" placeholder="<?php echo $LANG['UI_DRILLS_DETAIL_INPUNT_VALIDATE_HOST_IP']?>" class="form-control" id="verifyip"/>
                        	</div>
                        	<div class="col-md-3">
                        		<button type="button" class="btn green-haze" id="verifysubmit"> <?php echo $LANG['UI_DRILLS_DETAIL_SUBMIT']?> </button>
                        	</div>
                        </div>
                        
                        <div class="verifydiv row">
                        </div>
                    
				    </div>
				</div>
			</div>
		</div>
	</div>
	<!-- END BUTTON CONTENT-->
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/echarts.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/radiaindicator/radialIndicator.min.js"></script>
<script type="text/javascript" src="./scripts/platform/manoeuvre/task_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	