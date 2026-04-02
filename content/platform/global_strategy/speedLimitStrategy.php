<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<style>
    .monthcheck .label60 div{
        margin-right: -5px;
    }
    .weekcheck label>div{margin-right: -5px;}
</style>

    <div class="form-group setSpeedStrategy">
        <label class="control-label col-md-2"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY'] ?>
        </label>
        <div class="col-md-10">
            <div class="portlet">
                <div class="portlet-body">
                    <div class="panel-group accordion" id="speedstrategy">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group" id="speedItemSet" style="display:none;margin-top:-15px;">
        <label class="control-label col-md-2"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY_MESSAGE'] ?>
        </label>
        <div class="col-md-10">
            <ul id="speedList" style="padding-left:0;max-height:155px;overflow-y:scroll;">
    
            </ul>
        </div>
    </div>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script src="./scripts/platform/global_strategy/speed_strategy.js" type="text/javascript"></script>
