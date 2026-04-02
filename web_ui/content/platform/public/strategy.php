<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<div class="portlet">
	<div class="portlet-body">
		<div class="tabbable-line">
			<ul class="nav nav-tabs ">
				<li class="active">
					<a href="<?php echo "#" . $strategyType . "1"?>" data-toggle="tab">
					<?php echo $LANG['UI_STRATEGY_DAY']?> </a>
				</li>
				<li>
					<a href="<?php echo "#" . $strategyType . "2"?>" data-toggle="tab">
					<?php echo $LANG['UI_STRATEGY_WEEK']?> </a>
				</li>
				<li>
					<a href="<?php echo "#" . $strategyType . "3"?>" data-toggle="tab">
					<?php echo $LANG['UI_STRATEGY_MONTH']?> </a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active day" id="<?php echo $strategyType . "1"?>">
					<?php include $_SESSION['ROOTPATH']."content/platform/public/strategy_each.php"?>
				</div>
				<div class="tab-pane week" id="<?php echo $strategyType . "2"?>">
				    <div class="alert alert-danger display-none selectweektip">
    				</div>
				    <div class="form-group">
						<label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_EVERY_WEEK']?></label>
						<div class="col-md-9">
							<div class="input-group weekcheck">
							    <row>
							        <div>
							            <label><input type="checkbox" class="icheck"> <?php echo $LANG['UI_STRATEGY_MONDAY']?> </label>
    									<label><input type="checkbox" class="icheck"> <?php echo $LANG['UI_STRATEGY_TUESDAY']?> </label>
    									<label><input type="checkbox" class="icheck"> <?php echo $LANG['UI_STRATEGY_WEDNESDAY']?> </label>
    									<label><input type="checkbox" class="icheck"> <?php echo $LANG['UI_STRATEGY_THURSDAY']?> </label>
    								</div>
							    </row>
							    <row>
							        <div>
							            <label><input type="checkbox" class="icheck"> <?php echo $LANG['UI_STRATEGY_FRIDAY']?> </label>
    									<label><input type="checkbox" class="icheck"> <?php echo $LANG['UI_STRATEGY_SATURDAY']?> </label>
    									<label><input type="checkbox" class="icheck"> <?php echo $LANG['UI_STRATEGY_SUNDAY']?> </label>
    								</div>
							    </row>
							</div>
						</div>
					</div>
					<?php include $_SESSION['ROOTPATH']."content/platform/public/strategy_each.php"?>
				</div>
				<div class="tab-pane month" id="<?php echo $strategyType . "3"?>">
				    <div class="alert alert-danger display-none selectmonthtip">
    				</div>
				    <div class="form-group">
						<label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_EVERY_MONTH']?></label>
						<div class="col-md-9">
							<div class="input-group monthcheck">
					            <label class="label50"><input type="checkbox" class="icheck"> 1 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 2 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 3 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 4 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 5 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 6 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 7 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 8 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 9 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 10 </label>
					            <label class="label50"><input type="checkbox" class="icheck"> 11 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 12 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 13 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 14 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 15 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 16 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 17 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 18 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 19 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 20 </label>
					            <label class="label50"><input type="checkbox" class="icheck"> 21 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 22 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 23 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 24 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 25 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 26 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 27 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 28 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 29 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 30 </label>
								<label class="label50"><input type="checkbox" class="icheck"> 31 </label>
							</div>
						</div>
					</div>
					<?php include $_SESSION['ROOTPATH']."content/platform/public/strategy_each.php"?>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
