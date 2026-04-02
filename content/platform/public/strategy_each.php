                    <div class="form-group">
						<label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_STARTTIME']?></label>
						<div class="col-md-4">
							<div class="input-group">
								<input type="text" class="form-control timepicker timepicker-24 starttime">
								<span class="input-group-btn">
								<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
								</span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_ROLL_EXEC']?></label>
						<div class="col-md-5">
							<input type="checkbox" class="make-switch rollflag" data-on-color="primary" 
							data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
						</div>
					</div>
					<div class="form-group roll display-hide">
						<label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_ROLL_INTERVAL']?></label>
						<div class="col-md-4">
							<div class="input-group">
								<input type="text" class="form-control timepicker timepicker-24 rolltime">
								<span class="input-group-btn">
								<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
								</span>
							</div>
						</div>
					</div>
					<div class="form-group roll display-hide">
						<label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_OVER_TIME']?></label>
						<div class="col-md-4">
							<div class="input-group">
								<input type="text" class="form-control timepicker timepicker-24 endtime">
								<span class="input-group-btn">
								<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
								</span>
							</div>
						</div>
					</div>
					
					<div class="margintop10">
						<div class="row">
							<div class="col-md-offset-3 col-md-4">
								<button type="button"  class="btn btn-sm  blue-hoki cancel-button"><?php echo $LANG['UI_PUBLIC_NO']?></button>
								<button type="button"  class="btn btn-sm  blue-hoki  each-button"><?php echo $LANG['UI_PUBLIC_YES']?></button>
							</div>
						</div>
					</div>
					<script type="text/javascript" src="./scripts/public/strategy_each.js"></script>