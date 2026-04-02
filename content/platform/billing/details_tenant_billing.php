<?php include_once '../../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
	<?php
	       if(empty($_SESSION['tenantuuid'])){
	           echo '<a style="color: #20c99a;" class="ajaxify" name="billing_manager" href="./content/platform/billing/billing_manager.php">
                    		<i class="iconfont icon-feiyong" ></i>
                    		'.$LANG['BILLING_MANAGER'].' -
                    	</a>
                    	<a style="color: #20c99a;" class="ajaxify" name="details_billing" href="./content/platform/billing/details_billing.php?billing_uuid='.$_GET['billing_uuid'].'" id="details_billing">
                    		<i class="fa fa-list-alt" ></i>
                    		'.$LANG['BILLING_DETAILS'].' 
                    </a>';
	       }else{
	           echo '<a style="color: #20c99a;" class="ajaxify" id="companyName" name="p_tenant_view" href="./content/platform/tenant/tenant.php?uuid='.$_SESSION['tenantuuid'].'">
                    		<i class="iconfont icon-zuhu" ></i>
                    		 
                    	</a>';
	       }
	?>
	<span style="color: #575962;">-<?php echo $LANG['BILLING_DETAILS_TENANT_TITLE']?></span>
</h3>

<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<input value="<?php echo $_GET['tenant_uuid'];?>" id="tenant_uuid" style="display: none;">
    <div class="col-md-12">
      <div class="portlet box blue-hoki" id='authdiv'>
		<div class="portlet-title">
			<div class="caption" id="billing_name">
				<?php echo $LANG['BILLING_DETAILS_TENANT_TITLE']?>
			</div>
			
		</div>

		<div class="portlet-body">
		
    		<div class="table-toolbar">
    				<div class="row">
    					<div class="col-md-12">
    						<h3>*<?php echo $LANG['BILLING_YOUR_MSG']?></h3>
    						<div class="col-md-12">
        						<p><?php echo $LANG['BILLING_BUY_WAY']?>:  <span id="update_pay"></span></p>
        						<p><?php echo $LANG['BILLING_START_TIME']?>:  <span id="update_start_time"></span></p>
        						<p><?php echo $LANG['BILLING_END_TIME']?>:  <span id="update_end_time"></span></p>
        						<p><?php echo $LANG['BILLING_USE_DAY']?>:  <span id="update_day"></span></p>
        						<p><?php echo $LANG['BILLING_USE_CAPACITY']?>:  <span id="update_capacity"></span></p>
        						<p><?php echo $LANG['BILLING_USE_NUM']?>:  <span id="update_num"></span></p>
        						<p><?php echo $LANG['BILLING_TOTAL']?>:  <span id="update_billing"></span></p>
    						</div>
    					</div>
    				</div>
    			</div>
			
			
    			<div class="table-toolbar">
    				<div class="row">
    					<div class="col-md-12">
    						<h3>*<?php echo $LANG['BILLING_DETAILS']?></h3>
        					<div class="col-md-12">
        						<div class="btn-group">
        							<button type="button" id="exportExcel" class="btn btn-sm green-haze">
        							<i class="viconfont vicon-ge_add_task"></i><?php echo $LANG['BILLING_EXPORT']?>
        							</button>
        						</div>
        					</div>
    						
    						<div class="col-md-12">
        						<div class="table-container" style="margin-top: 20px;">
                        			<table class="table table-striped table-bordered table-hover lh2" id="billing_details_table">
                            			<thead>
                                			<tr role="row" class="heading">
                                    			
                                    			<th width="20%">
                                    				<?php echo $LANG['BILLING_START_TIME']?>	
                                    			</th>
                                    			<th width="20%">
                                    				<?php echo $LANG['BILLING_END_TIME']?>
                                    			</th>
                                    			<th width="10%" id="use_type">
                                    				--
                                    			</th>
                                    			<th width="10%">
                                    				<?php echo $LANG['BILLING_UNIT_COST']?>	
                                    			</th>
                                    			<th width="10%">
                                    				<?php echo $LANG['BILLING_TOTAL']?>
                                    			</th>
                                			</tr>
                            			</thead>
                        			</table>
                    			</div>
    						
    						</div>
    					</div>
    				</div>
    			</div>
    			<?php   switch ($_SESSION['language']){
    			    case "zh-cn":
    			        include_once './lang/DESbilling-zh-cn.php';
    			        break;
    			    case "zh-tw":
    			        include_once './lang/DESbilling-zh-tw.php';
    			        break;
    			    
    			}?>
		</div>
       </div> 
    </div>
</div>

           
            
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/billing/details_tenant_billing.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	