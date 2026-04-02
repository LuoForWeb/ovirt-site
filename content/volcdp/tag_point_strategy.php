<?php include_once '../../tpl/permission.php';?>
<div id="tagPointLimitModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="iconfont icon-time"></i> <?php echo $LANG['UI_JOB_LABEL_STRATEGY']; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="form-group settagPointStrategy">
				<label class="control-label col-md-2"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY']; ?>
				</label>
				<div class="col-md-10">
					<div class="portlet">
						<div class="portlet-body">
							<div class="panel-group accordion" id="tagpointstrategy">
								
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
		<button type="button"class="btn btn-primary" id="tagpoint_submit"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
	</div>
</div>