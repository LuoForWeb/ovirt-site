<!-- BEGIN FORM-->
<style>
    .safe-div-title{
        height: 36px;
        background: #F7F9FA;
        line-height: 36px;
        text-align: left;
    }
    .safe-div-title .item-label{
        font-size: 12px;
        color: #999999;
    }
    .safe-div-content{
        height: 50px;
        line-height: 34px;
        border-bottom: 1px solid #F1F3F5;
    }

    .safe-div-content .item-label{
        font-weight: 400;
        font-size: 14px;
        color: #666666;
    }
    .padding-l-0{
        padding-left: 0;
    }
    .margin-0-20{
        margin: 0 -24px;
    }
</style>
<form action="#" id="datasafeform" class="form-horizontal">
    <input type="hidden" id="isThreePowers" value="<?php echo $_SESSION['isThreePowers'];?>">
    <div class="form-body-wrapper">
        <div class="form-body hp-100 overflow-visible" style="width: 80%;">
            <div class="form-group safe-div-title">
                <label class="col-md-2 col-md-3_en item-label"></label>
                <label class="col-md-4 item-label"><?php echo $LANG['UI_DATA_LOG_SAFE_TITLE_WEB']?>
                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_DATA_LOG_SAFE_TITLE_WEB_TIPS']?>" data-original-title="" title="">
                        <i class="viconfont vicon-tishi"></i>
                    </a>
                </label>
                <label class="col-md-4 item-label" <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {echo 'style="display:none;"';}?>><?php echo $LANG['UI_DATA_LOG_SAFE_TITLE_BACK']?>
                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_DATA_LOG_SAFE_TITLE_BACK_TIPS']?>" data-original-title="" title="">
                        <i class="viconfont vicon-tishi"></i>
                    </a>
                </label>
            </div>

            <div class="form-group safe-div-content">
                <label class="col-md-2 col-md-3_en item-label text-right"><?php echo $LANG['UI_JOB_HISTORY'] ?></label>
                <label class="col-md-4 col-md-5_en item-label padding-l-0 web-safe-strategy">
                    <div class="col-md-6 padding-l-0">
                        <select class="form-control" name="strategy_type">
                            <option value="3" selected><?php echo $LANG['UI_FILE_PERMANENT'] ?></option>
                            <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                echo '<option value="2">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_DAY_TITLE'] .'</option>';
                                if(!$_SESSION['isThreePowers']){
                                    echo '<option value="1">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_NUM_TITLE'] .'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5 col-md-3_en margin-0-20 show-number display-none">
                        <div class="input-group spinner-group">
                            <input type="text" name="number" style="text-align: left;" value="30" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                <button type="button" class="btn spinner-up default">
                                    <i class="fa fa-angle-up"></i>
                                </button>
                                <button type="button" class="btn spinner-down default">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-md-3_en show-type display-none">
                        <?php echo $LANG['WEB_SYSTEM_AUTH_DAY'] ?>
                    </div>
                </label>

                <div class="col-md-2 item-label form-group-content" <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {echo 'style="display:none;"';}?>>
                    <input type="checkbox" id="history_job_check"  class="make-switch" data-on-color="primary" data-size="small" data-off-color="info"
                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                </div>
            </div>

            <div class="form-group safe-div-content">
                <label class="col-md-2 col-md-3_en item-label text-right"><?php echo $LANG['UI_PLATFORM_JOB_LOG'] ?></label>
                <label class="col-md-4 col-md-5_en item-label padding-l-0 web-safe-strategy">
                    <div class="col-md-6 padding-l-0">
                        <select class="form-control" name="strategy_type">
                            <option value="3" selected><?php echo $LANG['UI_FILE_PERMANENT'] ?></option>
                            <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                echo '<option value="2">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_DAY_TITLE'] .'</option>';
                                if(!$_SESSION['isThreePowers']){
                                    echo '<option value="1">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_NUM_TITLE'] .'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5 col-md-3_en margin-0-20 show-number display-none">
                        <div class="input-group spinner-group">
                            <input type="text" name="number" style="text-align: left;" value="30" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                <button type="button" class="btn spinner-up default">
                                    <i class="fa fa-angle-up"></i>
                                </button>
                                <button type="button" class="btn spinner-down default">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-md-3_en show-type display-none">
                        <?php echo $LANG['WEB_SYSTEM_AUTH_DAY'] ?>
                    </div>
                </label>

                <div class="col-md-2 item-label" <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {echo 'style="display:none;"';}?>>
                    <input type="checkbox" id="job_log_check" class="make-switch" data-on-color="primary"
                           data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                </div>
            </div>

            <div class="form-group safe-div-content">
                <label class="col-md-2 col-md-3_en item-label text-right"><?php echo $LANG['UI_PLATFORM_SYSTEM_LOG']?></label>

                <label class="col-md-4 col-md-5_en item-label padding-l-0 web-safe-strategy">
                    <div class="col-md-6 padding-l-0">
                        <select class="form-control" name="strategy_type">
                            <option value="3" selected><?php echo $LANG['UI_FILE_PERMANENT'] ?></option>
                            <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                echo '<option value="2">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_DAY_TITLE'] .'</option>';
                                if(!$_SESSION['isThreePowers']){
                                    echo '<option value="1">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_NUM_TITLE'] .'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5 col-md-3_en margin-0-20 show-number display-none">
                        <div class="input-group spinner-group">
                            <input type="text" name="number" style="text-align: left;" value="30" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                <button type="button" class="btn spinner-up default">
                                    <i class="fa fa-angle-up"></i>
                                </button>
                                <button type="button" class="btn spinner-down default">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-md-3_en show-type display-none">
                        <?php echo $LANG['WEB_SYSTEM_AUTH_DAY'] ?>
                    </div>
                </label>

                <div class="col-md-2 item-label" <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {echo 'style="display:none;"';}?>>
                    <input type="checkbox" id="system_log_check" class="make-switch" data-on-color="primary"
                           data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                </div>
            </div>

            <div class="form-group safe-div-content">
                <label class="col-md-2 col-md-3_en item-label text-right"><?php echo $LANG['UI_PLATFORM_HA_LOG']?></label>

                <label class="col-md-4 col-md-5_en item-label padding-l-0 web-safe-strategy">
                    <div class="col-md-6 padding-l-0">
                        <select class="form-control" name="strategy_type">
                            <option value="3" selected><?php echo $LANG['UI_FILE_PERMANENT'] ?></option>
                            <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                echo '<option value="2">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_DAY_TITLE'] .'</option>';
                                if(!$_SESSION['isThreePowers']){
                                    echo '<option value="1">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_NUM_TITLE'] .'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5 col-md-3_en margin-0-20 show-number display-none">
                        <div class="input-group spinner-group">
                            <input type="text" name="number" style="text-align: left;" value="30" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                <button type="button" class="btn spinner-up default">
                                    <i class="fa fa-angle-up"></i>
                                </button>
                                <button type="button" class="btn spinner-down default">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-md-3_en show-type display-none">
                        <?php echo $LANG['WEB_SYSTEM_AUTH_DAY'] ?>
                    </div>
                </label>

                <div class="col-md-2 item-label" <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {echo 'style="display:none;"';}?>>
                    <input type="checkbox" id="high_log_check" class="make-switch" data-on-color="primary"
                           data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                </div>
            </div>

            <div class="form-group safe-div-content">
                <label class="col-md-2 col-md-3_en item-label text-right"><?php echo $LANG['UI_PLATFORM_ALARM_TASK']?></label>

                <label class="col-md-4 col-md-5_en item-label padding-l-0 web-safe-strategy">
                    <div class="col-md-6 padding-l-0">
                        <select class="form-control" name="strategy_type">
                            <option value="3" selected><?php echo $LANG['UI_FILE_PERMANENT'] ?></option>
                            <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                echo '<option value="2">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_DAY_TITLE'] .'</option>';
                                if(!$_SESSION['isThreePowers']){
                                    echo '<option value="1">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_NUM_TITLE'] .'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5 col-md-3_en margin-0-20 show-number display-none">
                        <div class="input-group spinner-group">
                            <input type="text" name="number" style="text-align: left;" value="30" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                <button type="button" class="btn spinner-up default">
                                    <i class="fa fa-angle-up"></i>
                                </button>
                                <button type="button" class="btn spinner-down default">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-md-3_en show-type display-none">
                        <?php echo $LANG['WEB_SYSTEM_AUTH_DAY'] ?>
                    </div>
                </label>

                <div class="col-md-2 item-label" <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {echo 'style="display:none;"';}?>>
                    <input type="checkbox" id="job_alarm_check" class="make-switch" data-on-color="primary"
                           data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                </div>
            </div>

            <div class="form-group safe-div-content" style="border-bottom:none;">
                <label class="col-md-2 col-md-3_en item-label text-right"><?php echo $LANG['UI_VISUAL_ALARM_SYSTEM']?></label>

                <label class="col-md-4 col-md-5_en item-label padding-l-0 web-safe-strategy">
                    <div class="col-md-6 padding-l-0">
                        <select class="form-control" name="strategy_type">
                            <option value="3" selected><?php echo $LANG['UI_FILE_PERMANENT'] ?></option>
                            <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                echo '<option value="2">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_DAY_TITLE'] .'</option>';
                                if(!$_SESSION['isThreePowers']){
                                    echo '<option value="1">'. $LANG['UI_GLOBAL_STRATEGY_RESERVE_NUM_TITLE'] .'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5 col-md-3_en margin-0-20 show-number display-none">
                        <div class="input-group spinner-group">
                            <input type="text" name="number" style="text-align: left;" value="30" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                <button type="button" class="btn spinner-up default">
                                    <i class="fa fa-angle-up"></i>
                                </button>
                                <button type="button" class="btn spinner-down default">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-md-3_en show-type display-none">
                        <?php echo $LANG['WEB_SYSTEM_AUTH_DAY'] ?>
                    </div>
                </label>

                <div class="col-md-2 item-label" <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {echo 'style="display:none;"';}?>>
                    <input type="checkbox" id="system_alarm_check" class="make-switch" data-on-color="primary"
                           data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                </div>
            </div>


        </div>
    </div>

    <div class="form-actions flex-items-center justify-content-center">
        <div class="wp-50">
            <label class="control-label min-w-145px col-md-3"></label>
            <div class="col-md-6">
                <button type="button" id="datacancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <?php
                if (in_array('p_data_safe_operate', $_SESSION['permissionArr'])) {
                    echo '<button type="button" id="datasubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] . '</button>';
                }
                ?>
            </div>
        </div>
    </div>
</form>
<!-- END FORM-->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/data_safe.js"></script>