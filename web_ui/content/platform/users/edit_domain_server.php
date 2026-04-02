<?php include_once '../../../tpl/permission.php';?>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="safety" href="./content/platform/users/safety_manager.php">
            <?php echo $LANG['UI_PLATFORM_SAFETY']?>
        </a>
    </li>
    <span>></span>
    <li>
        <a class="ajaxify" name="safety" href="./content/platform/users/domain_server.php">
            <?php echo $LANG['UI_PLATFORM_SAFETY_DOMAIN']?>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_DOMAIN_SERVER_MODIFY']?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	<input id="domainuuid" value="<?php  echo $_GET['domainuuid'];?>" class="display-none"></input>
	   <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
	<!-- BEGIN VALIDATION STATES-->
	<div class="portlet box blue-hoki" id="editdomainContent">
		<div class="portlet-title">
			<div class="caption" style="color: #575962;">
				<?php echo $LANG['UI_DOMAIN_SERVER_MANAGE']?>
			</div>
		</div>
	<div class="portlet-body form">
	<!-- BEGIN FORM-->
	<form action="#" id="form_editDomainServer" class="form-horizontal">
	<div class="form-body">

    	<div class="form-group mt15">
			<label class="control-label col-md-3"><?php echo $LANG['UI_DOMAIN_SERVER_TYPE']?><span class="required">
    			* </span>
			</label>
			<div class="col-md-4">
				<div class="input-icon right">
					<i class="fa"></i>
    					<select class="form-control select2me" id="domaintype" name="domaintype" >
    						<option value="1"><?php echo $LANG['UI_DOMAIN_SERVER_TYPE1']?></option>
    				    <!-- <option value="2">Apple目录服务</option>
    						<option value="3">Oracle目录</option>
    						<option value="4">OpenLDAP</option>  -->
    					</select>
					<div><span class="help-block ">
					<?php echo $LANG['UI_DOMAIN_SERVER_TYPE_TIPS']?>
					</span></div>
				</div>
        	</div>
    	</div>

        <div class="form-group" >
            <label class="control-label col-md-3"><?php echo $LANG['UI_DOMAIN_PROTOCL']?><span class="required">
    			* </span>
            </label>
            <div class="col-md-4">
                <div class="input-icon right">
                    <i class="fa"></i>
                    <select class="form-control select2me" id="protocol" name="protocol" >
                        <option value="2">LDAP</option>
                        <option value="1">LDAPS</option>
                    </select>
                    <div><span class="help-block ">
					<?php echo $LANG['UI_DOMAIN_PROTOCL_TIPS']?>
					</span></div>
                </div>
            </div>
        </div>
        <div class="form-group certificateDiv display-hide">
            <label class="control-label col-md-3"><?php echo $LANG['UI_DOMAIN_VERIFY_CA_CERT']?>
            </label>
            <div class="col-md-8">
                <input type="checkbox" id="certificate_check" class="make-switch" data-on-color="primary"
                       data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
            </div>
        </div>

    	<div class="form-group">
			<label class="control-label col-md-3"><?php echo $LANG['UI_DOMAIN_SERVER_NAME']?><span class="required">
			* </span>
			</label>
			<div class="col-md-4">
				<div class="input-icon right">
					<i class="fa"></i>
					<input id="domain" type="text" maxlength="128" class="form-control" name="domain" placeholder="abc.com"/>
					<div><span class="help-block ">
					</span></div>
				</div>
    		</div>
		</div>
        <div class="form-group ">
            <label class="control-label col-md-3"><?php echo $LANG['UI_CLIENT_IP_ADDRESS']?><span class="required">
			* </span>
            </label>
            <div class="col-md-4">
                <div class="input-icon right">
                    <i class="fa"></i>
                    <input id="ip" value="" type="text" maxlength="128" class="form-control" name="ip"/>
                    <div><span class="help-block ">
                            <?php echo $LANG['UI_DOMAIN_IP_TIPS']?>
					</span></div>
                </div>
            </div>
        </div>

        <div class="form-group ">
            <label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_PORT']?><span class="required">
			* </span>
            </label>
            <div class="col-md-4">
                <div class="input-icon right">
                    <i class="fa"></i>
                    <input id="port" value="389" type="text" maxlength="128" class="form-control" name="port"/>
                    <div><span class="help-block ">
					<?php echo $LANG['UI_DOMAIN_PORT_TIPS']?>
					</span></div>
                </div>
            </div>
        </div>

    	<div class="form-group">
			<label class="control-label col-md-3"><?php echo $LANG['UI_DOMAIN_SERVER_USER_NAME']?><span class="required">
			* </span>
			</label>
			<div class="col-md-4">
				<div class="input-icon right">
					<i class="fa"></i>
					<input id="userName" type="text" maxlength="128" class="form-control" name="username"/>
					<div><span class="help-block ">
					<?php echo $LANG['UI_DOMAIN_SERVER_USER_NAME_TIPS']?>
					</span></div>
				</div>
    		</div>
		</div>

    	<div class="form-group">
			<label class="control-label col-md-3"><?php echo $LANG['UI_DOMAIN_SERVER_PASSWORD']?><span class="required">
			* </span>
			</label>
			<div class="col-md-4">
				<div class="input-icon right">
					<i class="fa"></i>
					<input id="password" type="password" autocomplete="off" maxlength="128" class="form-control" name="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
					<div><span class="help-block ">
					<?php echo $LANG['UI_DOMAIN_SERVER_PASSWORD_TIPS']?>
					</span></div>
				</div>
    		</div>
		</div>

		<!-- <div class="form-group <?php if(!empty($_SESSION['tenantuuid'])){ echo "display-none";}?>">
			<label class="control-label col-md-3"><?php echo $LANG['UI_DOMAIN_SERVER_RELATED_TENANT']?>
			</label>
			<div class="col-md-4">
				<div class="input-icon right">
					<i class="fa"></i>
    					<select class="form-control select2me" id="tenant" name="tenant" >
    					</select>
					<div><span class="help-block ">
					<?php echo $LANG['UI_DOMAIN_SERVER_TYPE_TIPS']?>
					</span></div>
				</div>
        	</div>
    	</div> -->

		</div>
		<div class="form-actions pt50">
			<div class="row">
				<div class="col-md-offset-3 col-md-4">
					<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_CANCEL']?></button>
					<button type="button" id="addsubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_CONFIRM']?></button>
				</div>
			</div>
		</div>

	</form>
	</div>
	</div>
	</div>
</div>


<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./scripts/platform/users/edit_domain_server.js" type="text/javascript"></script>