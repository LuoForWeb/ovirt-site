<?php
include_once '../../tpl/permission.php';
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
$authfun = $_SESSION['authfun'];
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <?php
		if ($_GET['orcPlan']) {
			$url = './content/platform/orchestration/orchestration_details.php?uuid=' . $_GET['orcPlan'];
            echo "<a href=\"$url\" class='ajaxify' name='task'><span>" .  $LANG['UI_JOB_TASK_ORCHESTRATION'] . "</span></a>";
		} else {
            echo '<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php"><span>' .  $LANG['UI_PLATFORM_CURRENT_JOB'] . '</span></a>';
		}
        ?>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS']?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail" id="fileCopyJobDetail">
    <div class="col-md-12 job-detail__halftop">
        <div class="col-md-8 job-detail__halftop__charts">
            <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
            <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
            <!-- BEGIN DYNAMIC CHART PORTLET-->
            <div class="portlet-charts">
                <div class="portlet-charts__title">
                    <div class="caption">
                        <i class="viconfont vicon-ge_task_flow"></i> <?php echo $LANG['UI_JOB_FLOW'] ?>
                    </div>
                </div>
                <div class="portlet-charts__body">
                    <div class="portlet-charts__body__speedchart">
                        <div id="speedcharthover"></div>
                        <div id="speedchart"></div>
                    </div>
                </div>
            </div>
            <!-- END DYNAMIC CHART PORTLET-->
        </div>

        <div class="col-md-4 job-detail__halftop__navtabs">
            <!-- BEGIN PORTLET-->
            <div class="portlet">
                <div class="portlet-body">
                    <!--BEGIN TABS-->
                        <div class="portlet-title init_tab_title">
							<i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
						</div>
						<div class="tab-content" style="padding:16px;">
							<div class="tab-pane active">
                                <div class="portlet-body">
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_PUBLIC_OPERATION'] ?>:
                                        </div>
                                        <div class="col-md-8 col-operate value">
                                        <div class="btn-group  vmStartDiv dropdown-wrapper">
                                                <button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle"
                                                        data-toggle="dropdown" data-hover="dropdown" data-delay="1000"
                                                        data-close-others="true">
                                                    <?php echo $LANG['UI_PUBLIC_OPERATION'] ?>
                                                    <i class="fa fa-angle-down"></i>
                                                </button>
                                                <ul class="dropdown-menu" role="menu" id="fileCopyOpList">
                                                    <li class="startFileCopy"><button class="btn dropdown-menu__item me-0" type="button"><i
                                                                    class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_FILE_COPY'] ?>
                                                        </button></li>
                                                    <li class="startCompare"><button class="btn dropdown-menu__item me-0" type="button"><i
                                                                    class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_FILE_COPY_COMPARE'] ?>
                                                        </button></li>
                                                    <li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i
                                                                    class="viconfont vicon-ge_suspend-copy me-4"></i><?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?>
                                                        </button></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_TASK_REPORT_TASK_NAME'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="taskName"
                                            style="word-break:break-word; max-width:300px;">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="taskType">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="status">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_TASK_STAGE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="task_stage">
                                        </div>
                                    </div>
                                    <div class="row static-info total-size-div">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_FILE_JOB_ACQUIRED_CAPACITY'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="totalSize">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_ALREADY_COMPLETED_SIZE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="currentSize">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_START_TIME'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="startTime">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_INTERVAL_TIME'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="intervalTime">
                                        </div>
                                    </div>

                                    <!-- MORE CONFIG -->
                                    <div class="row static-info flex-items-center">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_MORE'] ?>:
                                        </div>
                                        <div class="col-md-8 value">
                                            <a id="details_more" class="green-haze" data-toggle="drawer" data-target="#obs_more_detail_drawer" href="javascript:void(0)" style="color: #0FBF98;">
												<?php echo $LANG['BILLING_VIEW_DETAILS'] ?>                       
											</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!--END TABS-->
                </div>
            </div>
            <!-- END PORTLET-->
        </div>

    </div>
    <div class="col-md-12 job-detail__halfbottom">
        <!-- BEGIN TAB PORTLET-->
        <div class="job-detail__halfbottom__portlet">
            <div class="portlet-title">
                <ul class="nav nav-tabs">
                    <li class="active ">
                        <a href="#log" data-toggle="tab">
                            <i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
                    </li>
                    <li>
                        <a href="#filecopyLi" data-toggle="tab">
                            <i class="viconfont vicon-fuzhiliebiao"></i> <?php echo $LANG['UI_FILE_COPY_LIST'] ?></a>
                    </li>
                    <li id="historyli">
                        <a href="#history" data-toggle="tab">
                            <i class="viconfont vicon-histroytask"></i> <?php echo $LANG['UI_JOB_HISTORY'] ?> </a>
                    </li>
                    <li>
                        <a href="#compareLi" data-toggle="tab">
                            <i class="viconfont vicon-duibi"></i><?php echo $LANG['UI_FILE_COPY_COMPARE_RESULT'] ?></a>
                    </li>
                </ul>
            </div>
            <div class="portlet-body">
                <div class="tab-content">
                    <div class="tab-pane active running-log-pane" id="log">
                        <div class="time-range-wrapper">
                            <div id="running_log_daterangepicker_wrapper">
                                <i class="viconfont vicon-shijiankongjian"></i>
                                <span class="running-log-search"><?php echo $LANG['UI_TOOLS_TABLE_START_END_TIME'] ?></span>
                            </div>
                        </div>
                        <div class="running-log-content">
                            <ul id="runninglog"></ul>
                        </div>
                    </div>

                    <div class="tab-pane" id="filecopyLi">
                        <div class="table-container">
                            <table class="table table-hover table-borderless" id="filecopyTable">
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="history">
                        <div class="table-container">
                            <table class="table table-hover table-borderless" id="historytable">
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="compareLi">
                        <div class="table-container">
                            <table class="table table-hover table-borderless" id="compareTable">

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END TAB PORTLET-->

    </div>
    <!-- drawer开始 -->
    <div class="drawer slide aws-drawer-width_en" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="compareDrawer-title" aria-hidden="true" id="compareDrawer">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title" id="drawer-1-title">
                    <i class="viconfont vicon-jieguo mr8"></i><?php echo $LANG['UI_FILE_COPY_COMPARE_RESULT'] ?>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <div class="config-detail-wrap__group strategy-group">
                    <div class="strategy-group__form">
                        <div class="strategy-group__form__item">
				        	<div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_SRC'] ?>
				        	</div>
                            <div class="strategy-group__form__item__value col-md-8 file-copy-src"></div>
				        </div>
                        <div class="strategy-group__form__item">
				        	<div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_DES'] ?>
				        	</div>
                            <div class="strategy-group__form__item__value col-md-8 file-copy-des"></div>
				        </div>
                        <div class="strategy-group__form__item">
				        	<div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_COMPARE_TOTAL'] ?>
				        	</div>
                            <div class="strategy-group__form__item__value col-md-8 diff-total"></div>
				        </div>
                        <div class="strategy-group__form__item">
				        	<div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_ONLY_SHOW_DIFF'] ?>
				        	</div>
                            <div class="strategy-group__form__item__value col-md-2">
                                <input type="checkbox" id="dif_load_flag" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            </div>
				        </div>
                        <div class="strategy-group__form__item">
				        	<div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_DIFF_FILE']?>
				        	</div>
                            <div class="strategy-group__form__item__value col-md-2">
                                <input type="checkbox" id="dif_copy_force_flag" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            </div>
                            <a class="popovers ml15 mt2" data-container="body" data-trigger="hover"
                               data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_DIFF_FILE_TIPS']?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
				        </div>
                    </div>
                </div>
                
                <div class="diff-file-tree-content">
                    <ul id="diffFileTree" class="ztree"></ul>
                </div>
                <div class="alert alert-block alert-info fade in mt15">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
                    <ol class = "alert-ol">
                        <li>
                            <?php echo $LANG['UI_FILE_COPY_COMPARE_TIPS1'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_FILE_COPY_COMPARE_TIPS2'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_FILE_COPY_COMPARE_TIPS3'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_FILE_COPY_COMPARE_TIPS4'] ?>
                        </li>
                    </ol>
                </div>
            </div>
            <div class="drawer-footer">
                <button type="button" class="btn btn-primary" id="copyResult"><?php echo $LANG['UI_FILE_COPY_TO_COPY'] ?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
            </div>
        </div>
    </div>
    <!-- drawer结束 -->
</div>
<!-- END PAGE CONTENT-->
<!-- BEGIN TASK DETAIL DRAWER -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px"
    aria-labelledby="obs_detail_drawer_title" aria-hidden="true" id="obs_more_detail_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <div class="drawer-title" id="obs_detail_drawer_title"
                style="display:flex;justify-content:space-between;align-items:center">
                <div class="drawer-title-left">
                    <i class="viconfont vicon-tenant-detail mr10"></i>
                    <span><?php echo $LANG['UI_MORE_DETAIL'] ?></span>
                </div>
                <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                    <i class="viconfont vicon-guanbi"></i>
                </div>
            </div>
        </div>

        <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
        <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
            <!-- BEGIN COMMON STRATEGY GROUP -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-celve1 me-4"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
                </div>
                <!-- START TIME STRATEGY -->
				<div class="strategy-group__title timeStragegyDiv">
                    <span class="decoration me-8"></span>
					<span class="strategy-group__title__text"><?php echo $LANG['WEB_COMMON_TIME_STRATEGY'] ?></span>
                </div>
				<div class="strategy-group__form timeStragegyDiv">
					<!-- 创建/修改时间 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_CREATE_TIME'] ?>
						</div>
                        <div id="createTime" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 下次开始时间 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?>
						</div>
                        <div id="nextTime" class="strategy-group__form__item__value col-md-8"></div>
					</div>
                     <!-- 复制间隔 -->
                     <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                        <?php echo $LANG['UI_FILE_COPY_INTERVAL'] ?></div>
                        <div id="copyInterval" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 首次复制启动时间 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                        <?php echo $LANG['UI_FILE_COPY_FIRST_START_TIME'] ?></div>
                        <div id="firstCopyTime" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
				</div>
				<!-- END TIME STRATEGY -->
                <!-- BEGIN SPEED LIMIT STRATEGY -->
                <div class="strategy-group__title speed-limit-div">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                </div>
                <div class="strategy-group__form mb-20 speed-limit-div">
                    <!-- 限速策略 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></div>
                        <div id="speed_limit_backup" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务等级 -->
                    <div class="strategy-group__form__item task-priority-form-item-backup">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_TASK_PRIORITY'] ?></div>
                        <div id="task_priority_backup" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END SPEED LIMIT STRATEGY -->
                <!-- BEGIN NODE -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_TARGET_NODE'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_TARGET_NODE'] ?></div>
                        <div id="backup_node" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END NODE -->
            </div>
            <!-- END COMMON STRATEGY GROUP -->

            <!-- BEGIN TRANSMIT STRATEGY GROUP -->
            <div class="config-detail-wrap__group strategy-group transfer-strategy-show">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-a-chuanshu me-4"></i><span
                        class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 压缩传输 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                        <?php echo $LANG['UI_DB_CDP_BACKUP_COMPRESSED_TRANSMISSION'] ?></div>
                        <div id="transfer_compress" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 加密传输 -->
                    <div class="strategy-group__form__item transfer-encrypt-div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></div>
                        <div id="transfer_encrypt" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 加密算法 -->
                    <div class="strategy-group__form__item display-none transfer-encrypt-method-form-item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
                        <div id="transfer_encrypt_method" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
            </div>
            <!-- END TRANSMIT STRATEGY GROUP -->

            <!-- BEGIN ADVANCED CONFIG GROUP -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-gaojipeizhi me-4"></i><span
                        class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></span>
                </div>

				<!-- BEGIN COPY STRATEGY GROUP -->
				<div class="copy-strategy-show">
					<div class="strategy-group__title">
                	    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DB_CDP_SYNC_STRATEGY'] ?></span>
                	</div>
            	    <div class="strategy-group__form">
            	        <!-- 快照 -->
            	        <div class="strategy-group__form__item silentsnapshotcheck-form-item">
            	            <div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></div>
            	            <div id="silentsnapshotcheck" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
                        <!-- 文件权限复制 -->
                        <div class="strategy-group__form__item permission-form-item ">
            	            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_PERMISSION'] ?></div>
            	            <div id="permission_flag" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
                        <!-- 复制勾选目录 -->
                        <div class="strategy-group__form__item sync-self-form-item ">
            	            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_SELECTED_DIR'] ?></div>
            	            <div id="sync_self_flag" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
            	    </div>
            	</div>
            	<!-- END COPY STRATEGY GROUP -->
				<!-- BEGIN THREAD CONFIG GROUP -->
                <div class="">
                    <div class="strategy-group__title">
                        <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_FILE_COPY_THREAD_CONFIG'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                    <?php 
				        if($authfun['multithread']) {
				        	echo '
                                <!-- 传输线程 -->
                                <div class="strategy-group__form__item transfer-thread-form-item ">
                                    <div class="strategy-group__form__item__label col-md-4">
                                        '. $LANG['WEB_KUBE_RECOVERY_TRANSFER_THREADS'] .'</div>
                                    <div id="transfer_thread" class="strategy-group__form__item__value col-md-8"></div>
                                </div>
                            ';
                        }
                    ?> 
                        <!-- 扫描线程 -->
                        <div class="strategy-group__form__item scan-thread-form-item ">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_BAK_SCAN_THREAD'] ?> </div>
                            <div id="scan_thread" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 扫描文件速度 -->
                        <div class="strategy-group__form__item scan-speed-form-item ">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_BAK_SCAN_FILE'] ?> </div>
                            <div id="scan_speed" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
            	<!-- END THREAD CONFIG GROUP -->
				<!-- BEGIN ABNORMAL GROUP -->
				<div class="">
					<div class="strategy-group__title">
                	    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_WARNING'] ?></span>
                	</div>
            	    <div class="strategy-group__form">
            	        <!-- 跳过文件告警智能判断 -->
            	        <div class="strategy-group__form__item passfilealarm-form-item ">
            	            <div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_JUDGE'] ?></div>
            	            <div id="passfilealarmcheck" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
						<!-- 跳过文件告警个数 -->
            	        <div class="strategy-group__form__item pass-des-form-item ">
            	            <div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_NUM'] ?></div>
            	            <div id="passfilenum" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
						<!-- 跳过文件告警比例 -->
            	        <div class="strategy-group__form__item pass-des-form-item ">
            	            <div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_RATIO'] ?></div>
            	            <div id="warningpercent" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
                         <!-- 同名文件处理 -->
            	        <div class="strategy-group__form__item file-same-name-form-item ">
            	            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE'] ?></div>
            	            <div id="file_same_name_handle" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
                        <!-- 校验方式 -->
            	        <div class="strategy-group__form__item recover-way-form-item ">
            	            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_PARITY'] ?></div>
            	            <div id="file_check_mode" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
                        <!-- 校验算法 -->
            	        <div class="strategy-group__form__item recover-way-form-item file_verification_algorithm-form-item">
            	            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_VERIFICATION_ALGORITHM'] ?></div>
            	            <div id="file_verification_algorithm" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
                        <!-- 覆盖规则 -->
            	        <div class="strategy-group__form__item recover-way-form-item">
            	            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_FILE_COPY_RULE'] ?></div>
            	            <div id="sync_rule" class="strategy-group__form__item__value col-md-8"></div>
            	        </div>
            	    </div>
                    <!-- BEGIN WILDCARD GROUP -->
				    <div class="">
				    	<div class="strategy-group__title">
                    	    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_FILE_WILDCARD_FILTER'] ?></span>
                    	</div>
            	        <div class="strategy-group__form">
            	            <!-- 通配符 -->
            	            <div class="strategy-group__form__item wildcard-form-item">
            	                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_FILE_WILDCARD_FILTER'] ?></div>
            	                <div id="wildcard" class="strategy-group__form__item__value col-md-8"></div>
            	            </div>
            	        </div>
            	    </div>
            	    <!-- END WILDCARD GROUP -->
                    <!-- BEGIN TIME GROUP -->
				    <div class="">
				    	<div class="strategy-group__title">
                    	    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_PLATFORM_SET_TIME_FILTER'] ?></span>
                    	</div>
            	        <div class="strategy-group__form">
            	            <!-- 通配符 -->
            	            <div class="strategy-group__form__item time-range-form-item">
            	                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_PLATFORM_SET_TIME_FILTER'] ?></div>
            	                <div id="time_range" class="strategy-group__form__item__value col-md-8"></div>
            	            </div>
            	        </div>
            	    </div>
            	    <!-- END TIME GROUP -->
            	</div>
            	<!-- END ABNORMAL GROUP -->
                <!-- BEGIN RETRY STRATEGY -->
                <div class="">
                    <div class="strategy-group__title">
                        <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- 网络重连次数 -->
                        <div class="strategy-group__form__item network_retry_times_show">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?></div>
                            <div id="network_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 网络重连间隔时间 -->
                        <div class="strategy-group__form__item network_retry_interval_show">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?></div>
                            <div id="network_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 操作异常自动重试 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?></div>
                            <div id="op_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 操作异常重连次数 -->
                        <div class="strategy-group__form__item op-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?></div>
                            <div id="op_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 操作异常重连间隔时间 -->
                        <div class="strategy-group__form__item op-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?></div>
                            <div id="op_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务自动重试 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?></div>
                            <div id="task_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务重连对象 -->
                        <!-- <div class="strategy-group__form__item task-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?></div>
                            <div id="task_retry_object" class="strategy-group__form__item__value col-md-8"></div>
                        </div> -->
                        <!-- 任务重连次数 -->
                        <div class="strategy-group__form__item task-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?></div>
                            <div id="task_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务重连间隔时间 -->
                        <div class="strategy-group__form__item task-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?></div>
                            <div id="task_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
                <!-- END RETRY STRATEGY -->
                <!-- BEGIN OVERLOAD GROUP -->
                <div class="">
                    <div class="strategy-group__title">
                        <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- 忽略节点资源限制 -->
                        <div class="strategy-group__form__item passfilealarm-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></div>
                            <div id="ignoreResourceLimit" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
                <!-- END OVERLOAD GROUP -->
            </div>
            <!-- END ADVANCED CONFIG GROUP -->
        </div>
        <!-- END BACKUP TASK DETAIL DRAWER BODY -->

        <div class="drawer-footer">
            <button type="button" class="btn green-haze" data-dismiss="drawer"
                aria-label="Close"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
        </div>
    </div>
</div>
<!-- END TASK DETAIL DRAWER -->

<!-- 跳过文件详情drawer开始 -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px" aria-labelledby="obs_detail_drawer_title" aria-hidden="true" id="passFileDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <div class="drawer-title" id="obs_detail_drawer_title"
                style="display:flex;justify-content:space-between;align-items:center">
                <div class="drawer-title-left">
                    <i class="viconfont vicon-tenant-detail mr10"></i>
                    <span><?php echo $LANG['UI_FILE_SKIP_FILE_DETAILS'] ?></span>
                </div>
                <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                    <i class="viconfont vicon-guanbi"></i>
                </div>
            </div>
        </div>
        <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
        <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
            <!-- 跳过文件详情 -->
            <div class="config-detail-wrap__group strategy-group">
                <!-- 小标题 -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['UI_FILE_SKIP_FILE_STATISTICS'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 跳过文件个数 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_FILE_NUM'] ?>
                        </div>
                        <div id="file_skip_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过文件比例 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_FILE_RATIO'] ?>
                        </div>
                        <div id="file_skip_radio" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过目录个数 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_DIRECTORY_NUM'] ?>
                        </div>
                        <div id="dir_skip_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过目录比例 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_DIRECTORY_RATIO'] ?>
                        </div>
                        <div id="dir_skip_radio" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过文件告警个数 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_NUM'] ?>
                        </div>
                        <div id="file_skip_alarm_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过文件告警比例 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_RATIO'] ?>
                        </div>
                        <div id="file_skip_alarm_radio" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END SPEED LIMIT STRATEGY -->
            </div>
            <!-- 跳过文件详情 -->

            <!-- 具体跳过原因 -->
            <div class="config-detail-wrap__group strategy-group">
                <!-- 小标题 -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['UI_FILE_SKIP_REASON_DETAILS'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 被占用文件个数 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                        <?php echo $LANG['UI_FILE_OCCUPIED_FILE_NUM'] ?></div>
                        <div id="file_occupied_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 被删除文件个数 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_DELETE_FILE_NUM'] ?></div>
                        <div id="file_delete_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 无权限文件个数 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_NO_PERMISSION_FILE_NUM'] ?></div>
                        <div id="no_permission_file_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                     <!-- 无权限目录个数 -->
                     <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_NO_PERMISSION_DIRECTORY_NUM'] ?></div>
                        <div id="no_permission_dir_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                     <!-- 被删除目录个数 -->
                     <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_DELETE_DIRECTORY_NUM'] ?></div>
                        <div id="dir_delete_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                     <!-- 其他个数 -->
                     <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_OTHER_NUM'] ?></div>
                        <div id="other_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
            </div>
            <!-- 具体跳过原因 -->
        </div>
        <!-- END BACKUP TASK DETAIL DRAWER BODY -->

        <div class="drawer-footer">
            <button type="button" class="btn btn-primary" id="downloadDrawer" style="width: 128px;"><i class="viconfont vicon-baogaoxiazaishijian"></i><?php echo $LANG['UI_FILE_DOWNLOAD_SKIP_FILE'] ?></button>
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
		
        </div>
    </div>
</div>
<!-- 跳过文件详情drawer结束 -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
 <script type="text/javascript" src="./scripts/filecopy/file_copy_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->