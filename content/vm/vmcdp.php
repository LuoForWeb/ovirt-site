<?php include_once '../../tpl/permission.php';?>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<?php session_start();session_commit();?>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_BACKUP_VM_BAK']?> <small><?php echo $LANG['UI_BACKUP_VM_BAK_DESCRIPTION']?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	    <input id="s_vmuuid" value="<?php echo $_GET['vmuuid'];?>" class="display-none"></input>
	    <input id="s_vcenteruuid" value="<?php echo $_GET['vcenteruuid'];?>" class="display-none"></input>
	    <input id="s_showtype" value="<?php echo $_GET['showtype'];?>" class="display-none"></input>
	    <input id="s_hypervisor" value="<?php echo $_GET['hypervisor'];?>" class="display-none"></input>
		<div class="portlet box blue-hoki" id="vmbackupcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i> <?php echo $LANG['UI_BACKUP_NEW_TASK']?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" class="form-horizontal" id="submit_form" method="POST">
					<div class="form-wizard">
						<div class="form-body">
							<ul class="nav nav-pills nav-justified steps">
								<li>
									<a href="#tab1" data-toggle="tab" class="step">
									<span class="number">
									1 </span>
									<span class="desc">
									<i class="fa fa-check"></i> <?php echo $LANG['UI_BACKUP_SOURCE']?> </span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
									<span class="number">
									2 </span>
									<span class="desc">
									<i class="fa fa-check"></i> <?php echo $LANG['UI_BACKUP_TYPE']?> </span>
									</a>
								</li>
								<li>
									<a href="#tab3" data-toggle="tab" class="step active">
									<span class="number">
									3 </span>
									<span class="desc">
									<i class="fa fa-check"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG']?> </span>
									</a>
								</li>
								<li>
									<a href="#tab4" data-toggle="tab" class="step">
									<span class="number">
									4 </span>
									<span class="desc">
									<i class="fa fa-check"></i> <?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG']?></span>
									</a>
								</li>
							</ul>
							<div id="bar" class="progress progress-striped" role="progressbar">
								<div class="progress-bar progress-bar-success">
								</div>
							</div>
							<div class="tab-content bakuptab">
								<div class="tab-pane active" id="tab1">
									<h4 class="block"> <?php echo $LANG['UI_BACKUP_SELECT_VM_TITLE']?> </h4>
									<div class="alert alert-danger display-none selectvmtip">
    								</div>
									<div class="row">
                                      <div class="col-md-7">
                                        <div class="form-group">
    										<label class="control-label col-md-4"> <?php echo $LANG['UI_BACKUP_SELECT_VM']?><span class="required">
    										* </span>
    										</label>
    										<div class="col-md-8 ">
    										      <div class="vm_tree_div ">
    										        <select class="bs-select" data-show-subtext="true" id="vmshowtype">
                                                        <option data-icon="hostcluster icon-default" value="1"><?php echo $LANG['UI_VCENTER_SHOW_HOST_CLUSTER']?></option>
                                                        <option data-icon="vmtmp icon-default" value="2"><?php echo $LANG['UI_VCENTER_SHOW_VM_TEMPLATE']?></option>
                                                        <option data-icon="hostvm icon-default" value="3"><?php echo $LANG['UI_VCENTER_SHOW_HOST_VM']?></option>
                                                    </select>
                                                    <div class="input-icon right floatr">
                    									<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD']?>" class="form-control display-hide" id="searchvm"/>
                    								</div>
    										      </div>
    										      
    										      
    										    <div class="mt10 min-height250">
    										        <div class="three_tree" >
    										          <ul id="vm_tree" class="ztree bd1de5  tree_div display-hide"></ul>
    										          <ul id="vm_tree_hc" class="ztree bd1de5  tree_div " ></ul>
    										          <ul id="vm_tree_vt" class="ztree bd1de5  tree_div display-hide" ></ul>
    										        </div>
        											<div class="alert alert-block alert-info fade in display-hide"  id="nodatatips">
                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
                                                        <ol class = "alert-ol">
															<li>
																<?php echo $LANG['UI_BACKUP_NO_VM_DATA_TITLE']?>
                                                            </li>
                                                            <li>
                                                                <a id="toaddvcenter"><small><?php echo $LANG['UI_BACKUP_NO_VM_DATA_TIPS1']?></small></a>
                                                            </li>
                                                            <li>
                                                                <small><?php echo $LANG['UI_BACKUP_NO_VM_DATA_TIPS2']?></small>
                                                            </li>
                                                        </ol>
                        							</div>
    										    </div>
    										</div>
    									</div>
                                      </div>
                                      <div class="col-md-4 VMList-div">                
                                           <div class="addTitle">
                                             <span><?php echo $LANG['UI_BACKUP_ADD_VM']?></span>
                                           </div>  
                                                                                                                         
                                      <div class="addIt-list">
                                          <ul class="feeds addVMList" id="addHCList">                                                                                                                                                                                                                                                         
                                          </ul>
                                          
                                          <ul class="feeds addVMList" id="addVTList">                                                                                                                                                                                                                                                         
                                          </ul>
                                          
                                          <ul class="feeds addVMList" id="addHVList">                                                                                                                                                                                                                                                         
                                          </ul>
                                        </div>
                                      </div>
                                    </div>
									
								</div>
								<div class="tab-pane" id="tab2">
									<div class="row">
									   <div class="col-md-3">
									       <h4 class="block"><?php echo $LANG['UI_VM_SET_START_METHOD']?></h4>
									   </div>
									   <div class="col-md-9 ptb20">
									       <div class="label label-sm label-success"><small><?php echo $LANG['UI_STRATEGY_SERVER_TIME']?>:  </small><span id="servertime">----</span></div>
									   </div>
									</div>
									<div class="alert alert-danger display-none settimetip">
    								</div>
									<div class="row">
                                      <div class="col-md-9 min-height300">
                                        <div class="form-group">
    										<label class="control-label col-md-3"><?php echo $LANG['UI_VM_START_METHOD']?> <span class="required">
    										* </span>
    										</label>
    										<div class="col-md-5">
    											<select class="form-control select2me" id="backuptype">
    												<option value="1"><?php echo $LANG['UI_VM_MANUAL_START']?></option>
    												<option value="2"><?php echo $LANG['UI_VM_TIMING_START']?></option>
    											</select>
    										</div>
    									</div>
    									
    									<div class="form-group display-hide setOnceTime">
    										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SET_TIME']?> <span class="required">
    										* </span>
    										</label>
    										<div class="col-md-5">
    											<div class="input-group date form_datetime">
    												<input type="text" size="16" readonly id="oncetime" class="form-control">
    												<span class="input-group-btn">
    												<button class="btn default" id="resetdate" type="button"><i class="fa fa-times"></i></button>
    												<button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
    												</span>
    											</div>
    										</div>
    									</div>
    									
                                      </div>
                                    </div>
									
								</div>
								<div class="tab-pane" id="tab3">
									<h4 class="block"><?php echo $LANG['UI_BACKUP_SET_HIGH_STRATEGY']?></h4>
									<div class="alert alert-danger display-none setstrategytip">
    								</div>
    								<div class="row">
    								    <div class="tabbable-custom pdlr15 col-md-offset-1 col-md-9">
            								<ul class="nav nav-tabs ">
            									<li class="active">
            										<a href="#tab_node" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_DATA_DES_TIPS']?>" 
                        										   data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
            										<i class="icon-pointer"></i> <?php echo $LANG['UI_BACKUP_DATA_DES']?> </a>
            									</li>
            									<li class="">
            										<a href="#tab_store" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_STORAGE_TIPS']?>" 
                        										   data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
            										<i class="glyphicon glyphicon-hdd"></i> <?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY']?> </a>
            									</li>
            									<li class="">
            										<a href="#tab_hightransfer" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_TIPS']?>" 
                        										   data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
            										<i class="fa fa-exchange"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']?> </a>
            									</li>
            									<li class="">
            										<a href="#tab_reserve" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_DATA_RESERVE_TYPE_TIPS']?>" 
                        										   data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
            										<i class="glyphicon glyphicon-trash"></i> <?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY']?> </a>
            									</li>
            									<li class="showBackupmode">
            										<a href="#tab_backupmode" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_MODE_TIPS']?>" 
                        										   data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
            										<i class="viconfont vicon-ge_advanced"></i> <?php echo $LANG['UI_BACKUP_MODE']?> </a>
            									</li>
            								</ul>
            								<div class="tab-content min-height300">
            									<div class="tab-pane active" id="tab_node">
            									     <div class="panel-body">
            											<div class="col-md-9 ">
                                    					    <div class="form-group">
                                        						<label class="control-label col-md-3 autonodelabel"><?php echo $LANG['UI_BACKUP_AUTO_NODE']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="autonodecheck" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['WEB_PLATFORM_PUBLIC_YES']?>" 
                                        							data-off-text="<?php echo $LANG['WEB_PLATFORM_PUBLIC_NO']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_AUTO_NODE_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					<div class="form-group display-none diynodediv">
                                        						<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_BACKUP_DIY_NODE']?></label>
                                        						<div class="col-md-9">
                                        							<select class="form-control select2me" id="selectnode">
                                    								</select>
                                        						</div>
                                        					</div>
                                        					<div class="form-group display-none autostoragediv">
                                        						<label class="control-label col-md-3 autostoragelabel"><?php echo $LANG['UI_BACKUP_AUTO_STORAGE']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="autostoragecheck" checked class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['WEB_PLATFORM_PUBLIC_YES']?>" 
                                        							data-off-text="<?php echo $LANG['WEB_PLATFORM_PUBLIC_NO']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_AUTO_STORAGE_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					<div class="form-group display-none diystoragediv">
                                        						<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_BACKUP_DIY_STORAGE']?></label>
                                        						<div class="col-md-9">
                                        							<select class="form-control select2me" id="selectstorage">
                                    								</select>
                                        						</div>
                                        					</div>
                                        				</div>
            										</div>
            									</div>
            									<div class="tab-pane" id="tab_store">
            									    <div class="panel-body" style="overflow-y:auto;">
            										     <div class="col-md-9 ">
                                    					    <div class="form-group">
                                        						<label class="control-label col-md-3 deduplicationlabel"><?php echo $LANG['UI_BACKUP_DEDUPLICATION']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="deduplicationcheck"   class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DEDUPLICATION_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					<div class="form-group">
                                        						<label class="control-label col-md-3 compresslabel"><?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="compresscheck" checked class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_COMPRESS_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					<div class="form-group blocksizediv">
                                        						<label class="control-label col-md-3 blocksizelabel"><?php echo $LANG['UI_BACKUP_BLOCK_SIZE']?></label>
                                        						<div class="col-md-4">
                                        							<select class="form-control select2me" id="blocksize">
                                    									<option value="64" selected = "selected">64 KB</option>
                                    									<option value="128">128 KB</option>
                                    									<option value="256">256 KB</option>
                                    									<option value="512">512 KB</option>
                                    									<option value="1024">1024 KB</option>
                                    									<option value="2048">2048 KB</option>
                                    								</select>
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_BLOCK_SIZE_TIPS']?> ">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					<div class="form-group display-hide">
                                        						<label class="control-label col-md-3 encryptlabel"><?php echo $LANG['UI_BACKUP_ENCRYPTION_STORAGE']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="encryptcheck" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        					</div>
                                        					<div class="form-group display-hide">
                                        						<label class="control-label col-md-3 validlabel"><?php echo $LANG['UI_BACKUP_VALID_DATA']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="validcheck" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        					</div>
                                        				</div>
            										</div>
            									</div>
            									<div class="tab-pane" id="tab_hightransfer">
            									     <div class="panel-body" style="overflow-y:auto;">
            										     <div class="col-md-9 ">
            										        <div class="form-group display-hide encrypttransferdiv">
                                        						<label class="control-label col-md-3 encrypttransferlabel"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="encrypttransfer"  class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        					</div>
                                        					<div class="form-group transportdiv">
                                        						<label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']?></label>
                                        						<div class="col-md-4">
                                        							<select class="form-control select2me" id="transport_mode">
                                    									<option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']?></option>
                                    									<option value="nbdssl"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBDSSL']?></option>
                                    									<option value="san"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN']?></option> 
                                    									<option value="hotadd"><?php echo $LANG['UI_BACKUP_TRANSPORT_HOT']?></option> 
                                    								</select>
                                        						</div>
                                        						<div class="col-md-2 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_SELECT_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					
                                        					<!-- 华为传输模式 -->
                                        					<div class="form-group huaweitransportdiv display-none">
                                        						<label class="control-label col-md-3 huaweitransferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']?></label>
                                        						<div class="col-md-4">
                                        							<select class="form-control select2me" id="huaweitransport_mode">
                                    									<option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']?></option>
                                    									<option value="nbdssl"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBDSSL']?></option>
                                    									<option value="san"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN']?></option> 
                                    								</select>
                                        						</div>
                                        						<div class="col-md-2 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_HUAWEI_SELECT_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					
                                        					<!-- XenServer传输模式 -->
                                        					<div class="form-group xentransdiv display-none">
                                        						<label class="control-label col-md-3 xentranslabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']?></label>
                                        						<div class="col-md-4">
                                        							<select class="form-control select2me" id="xentransmode">
                                    									<option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']?></option>
                                    									<option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN']?></option>
                                    									<option value="3"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD_XENSERVER']?></option>
                                    									<option value="4"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD_SAN_XENSERVER']?></option> 
                                    								</select>
                                        						</div>
                                        						<div class="col-md-2 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_XENSERVER_SELECT_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					
                                        					<!-- 类XenServer传输模式 -->
                                        					<div class="form-group leixentransdiv display-none">
                                        						<label class="control-label col-md-3 leixentranslabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']?></label>
                                        						<div class="col-md-4">
                                        							<select class="form-control select2me" id="leixentransmode">
                                    									<option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']?></option>
                                    									<option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN']?></option>
                                    								</select>
                                        						</div>
                                        						<div class="col-md-2 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_LEIXEN_SELECT_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					
                                        				</div>
            										</div>
            									</div>
            									<div class="tab-pane" id="tab_reserve">
            									    <div class="panel-body">
            										     <div class="col-md-9 ">
                                    					    <div class="alert alert-danger display-none setreservtip"></div>
                                                            <div class="form-group">
                        										<label class="control-label col-md-3">
                        										<?php echo $LANG['UI_BACKUP_DATA_RESERVE_TYPE']?> <span class="required">
                        										* </span>
                        										</label>
                        										<div class="col-md-4">
                        											<select class="form-control select2me" id="reservetype">
                        												<option value="2"><?php echo $LANG['UI_BACKUP_DAY']?></option>
                        												<option value="1"><?php echo $LANG['UI_BACKUP_NUM']?></option>
                        											</select>
                        										</div>
                        										<div class="col-md-2 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_RESERVER_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                        									</div>
                        									<div class="form-group display-hide reserveNum">
                        										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_NUM']?> <span class="required">
                        										* </span>
                        										</label>
                        										<div class="col-md-9">
                    												<div id="spinnerNum">
                        												<div class="input-group" style="width:150px;">
                        													<input type="text" id="spinnerNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="3">
                        													<div class="spinner-buttons input-group-btn">
                        														<button type="button" class="btn spinner-up default">
                        														<i class="fa fa-angle-up"></i>
                        														</button>
                        														<button type="button" class="btn spinner-down default">
                        														<i class="fa fa-angle-down"></i>
                        														</button>
                        													</div>
                        												</div>
                        											</div>
                        										</div>
                        									</div>
                        									<div class="form-group reserveDay">
                        										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_DAY']?> <span class="required">
                        										* </span>
                        										</label>
                        										<div class="col-md-9">
                    												<div id="spinnerDay">
                        												<div class="input-group" style="width:150px;">
                        													<input type="text" id="spinnerDayInput" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="3" >
                        													<div class="spinner-buttons input-group-btn">
                        														<button type="button" class="btn spinner-up default">
                        														<i class="fa fa-angle-up"></i>
                        														</button>
                        														<button type="button" class="btn spinner-down default">
                        														<i class="fa fa-angle-down"></i>
                        														</button>
                        													</div>
                        												</div>
                        											</div>
                        										</div>
                        									</div>
                        								</div>
            										</div>
            									</div>
            									<div class="tab-pane" id="tab_backupmode">
            									    <div class="panel-body" style="overflow-y:auto;">
            										     <div class="col-md-9 ">
            										     	<div class="form-group">
                        										<label class="control-label col-md-3 snapshotTypelabel">
                        										<?php echo $LANG['UI_BACKUP_SNAPSHOT_MODE']?> 
                        										</label>
                        										<div class="col-md-4">
                        											<select class="form-control" id="snapshottype">
                        												<option value="1"><?php echo $LANG['UI_BACKUP_SNAPSHOT_MODE_SERIAL']?></option>
                        												<option value="2"><?php echo $LANG['UI_BACKUP_SNAPSHOT_MODE_PARALLEL']?></option>
                        											</select>
                        										</div>
                        										<div class="col-md-2 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_SNAPSHOT_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                        									</div>
                        									
                        									<div class="form-group" id="incmodeldiv">
                        										<label class="control-label col-md-3 incmodellabel">
                        										<?php echo $LANG['UI_BACKUP_INCMODE']?>
                        										</label>
                        										<div class="col-md-4">
                        											<select class="form-control" id="incmodel">
                        												<option value="2"><?php echo $LANG['UI_BACKUP_INCMODE_HIGH_SPEED']?></option>
                        												<option value="3"><?php echo $LANG['UI_BACKUP_INCMODE_CBT']?></option>
                        												<option value="1"><?php echo $LANG['UI_BACKUP_INCMODE_GENNERAL']?></option>
                        											</select>
                        										</div>
                        										<div class="col-md-2 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_INC_MODE_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                        									</div>
            										     	
                                    					    <div class="form-group" id="backupmodediv">
                                        						<label class="control-label col-md-3 snapshotlabel"><?php echo $LANG['UI_BACKUP_MODE_HIGH_SPEED']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="snapshotcheck" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" 
                                									   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_SPEED_TIPS']?>">
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					<div class="form-group snapshotdiv">
                                        						<label class="control-label col-md-3 silentsnapshotlabel"><?php echo $LANG['UI_BACKUP_MODE_HIGH_SILENTSNAPSHOT']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="silentsnapshotcheck"  class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" 
                            										      data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_SILENTSNAPSHOT_TIPS']?>" >
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					
                                        					<div class="form-group cbtdiv">
                                        						<label class="control-label col-md-3 cbtlabel"><?php echo $LANG['UI_BACKUP_CBT']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="cbtcheck"  class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" 
                            										      data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_CBT_TIPS']?>" >
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                        									
                        									<div class="form-group parsefsDiv">
                                        						<label class="control-label col-md-3 parsefslabel"><?php echo $LANG['UI_BACKUP_VCBT']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="parsefscheck"  class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" 
                            										      data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_VCBT_TIPS']?>" >
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					
                                        					<div class="form-group swapdiv display-none" style="margin-left: 50px;">
                                        						<label class="control-label col-md-3 swaplabel"><?php echo $LANG['UI_BACKUP_SWAP']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="swapcheck"  class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" 
                            										      data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_SWAP_TIPS']?>" >
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					
                                        					<div class="form-group deletefilediv display-none" style="margin-left: 50px;">
                                        						<label class="control-label col-md-3 deletefilelabel"><?php echo $LANG['UI_BACKUP_DELETEFILE']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="deletefilecheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" 
                            										      data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_DELFILE_TIPS']?>" >
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        					
                                        					<div class="form-group gapdiv display-none" style="margin-left: 50px;">
                                        						<label class="control-label col-md-3 gaplabel"><?php echo $LANG['UI_BACKUP_GAP']?></label>
                                        						<div class="col-md-4">
                                        							<input type="checkbox" id="gapcheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" 
                                        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                                        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                        						</div>
                                        						<div class="col-md-1 mt10">
                            										<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" 
                            										      data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_GAP_TIPS']?>" >
        						                                       <i class="fa fa-info-circle fa-lg"></i>
        						                                    </a>
                        										</div>
                                        					</div>
                                        				</div>
            										</div>
            									</div>
            								</div>
            							</div>
    								</div>
								</div>
								<div class="tab-pane" id="tab4">
									<h3 class="block"><?php echo $LANG['UI_BACKUP_CONFIRM_SETTING']?></h3>
									<div class="alert alert-danger display-none jobnametip">
    								</div>
									<div class="form-group mb0">
										<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']?>:</label>
										<div class="col-md-4">
            								<div class="input-icon right">
            									<i class="fa"></i>
            									<input type="text" maxlength="64" class="form-control" id="jobname"/>
            									<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']?></span>
            								</div>
            							</div>
									</div>
									<h4 class="form-section"></h4>
									<div class="form-group mb0">
										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SOURCE']?>:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen vmshow">
											</p>
										</div>
									</div>
									<h4 class="form-section"></h4>
									<div class="form-group mb0">
										<label class="control-label col-md-3"><?php echo $LANG['UI_VM_START_METHOD']?>:</label>
										<div class="col-md-4">
											<p class="form-control-static colorgreen backuptypeshow">
											</p>
										</div>
									</div>
									<h4 class="form-section"></h4>
									
									<div class="form-group mb0 ">
										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_DATA_DES']?>:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen nodeinfoshow">
											</p>
										</div>
									</div>
									<div class="form-group mb0 ">
										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY']?>:</label>
										<div class="col-md-4">
											<p class="form-control-static colorgreen storageinfoshow">
											</p>
										</div>
									</div>
									<div class="form-group mb0 " id="transportmodeshowdiv">
										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']?>:</label>
										<div class="col-md-4">
											<p class="form-control-static colorgreen transportinfoshow">
											</p>
										</div>
									</div>
									<div class="form-group mb0">
										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY']?>:</label>
										<div class="col-md-4">
											<p class="form-control-static colorgreen reservetypeshow">
											</p>
										</div>
									</div>
									<div class="form-group mb0" id="backupmodeshowdiv">
										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_MODE']?>:</label>
										<div class="col-md-6">
											<div class="form-control-static colorgreen backupmodeshow1">
											</div>
											<div class="form-control-static colorgreen backupmodeshow2">
											</div>
											<div class="form-control-static colorgreen cbtmodeshow">
											</div>
											<div class="form-control-static colorgreen incmodeshow">
											</div>
											<div class="form-control-static colorgreen snapshotmodeshow">
											</div><br>
											<div class="form-control-static colorgreen parsefsmodeshow">
											</div>
											<div class="form-control-static colorgreen swapmodeshow">
											</div>
											<div class="form-control-static colorgreen deletefilemodeshow">
											</div>
											<div class="form-control-static colorgreen gapmodeshow">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-actions">
							<div class="row">
								<div class="col-md-offset-6 col-md-6">
									<a href="javascript:;" class="btn default button-previous">
									<i class="m-icon-swapleft"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']?> </a>
									<a href="javascript:;" class="btn default button-next">
									<?php echo $LANG['UI_PUBLIC_NEXT_STEP']?> <i class="m-icon-swapright m-icon-white"></i>
									</a>
									<a href="javascript:;" class="btn green button-submit">
									<?php echo $LANG['UI_PUBLIC_SUBMIT']?> <i class="m-icon-swapright m-icon-white"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
			
			
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/vm/vmcdp.js" type="text/javascript"></script>
