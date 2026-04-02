<?php include_once '../../../../tpl/permission.php'; ?>

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
		</a>
	</li>
	<span>></span>
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_br.php">
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_BAK_REC'] ?></span>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_RC_ONCEBAK'] ?></span>
</h3>
<div class="row row-manager">
	<div class="col-md-12 col-manager">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-opbak"></i><?php echo $LANG['UI_PLATFORM_RC_ONCEBAK'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="oncebakform" class="form-horizontal">
					<div class="form-body-wrapper">
						<div class="form-body wp-50 hp-100 overflow-visible width80p_en">
							<div class="form-group" id="configdiv">
								<label class="control-label col-md-3"><?php echo $LANG['UI_BR_ONCEBAK_SELECT_BU'] ?>
								</label>
								<div class="col-md-8">
									<ul id="baktree" class="ztree bd1de5 tree_div "
										style="height: 400px;overflow: auto">
									</ul>
									<div><span class="help-block ">
											<?php echo $LANG['UI_BR_ONCEBAK_SELECT_CONTENT'] ?>
										</span></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3">
								</label>
								<div class="col-md-8">
									<div class="alert alert-block alert-info fade in">
										<button type="button" class="close" data-dismiss="alert"></button>
										<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
										</h4>
										<ol class="alert-ol">
											<li>
												<?php echo $LANG['UI_BR_ONCEBAK_SELECT_CONTENT_TIP_ONE'] ?>
											</li>
											<li>
												<?php echo $LANG['UI_BR_ONCEBAK_SELECT_CONTENT_TIP_TWO'] ?>
											</li>
										</ol>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-actions flex-items-center justify-content-center">
						<div class="wp-50">
							<label class="control-label col-md-3"></label>
							<div class="col-md-8">
								<button type="button" id="oncebakcancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
								<?php
								if (in_array("rc_oncebak", $_SESSION['permissionArr'])) {
									echo '<button type="button" id="oncebaksubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] . '</button>';
								}
								?>
							</div>
						</div>
					</div>
				</form>
				<!-- END FORM-->
			</div>
		</div>
	</div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_br_oncebak.js"></script>