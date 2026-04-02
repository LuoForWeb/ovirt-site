<style>
    #show_completeMachineRecovery .driver-check__wrapper .driver-config__label{
        padding-right: 15px;
    }
    #show_completeMachineRecovery .driver-check__wrapper .driverCheckBtnDiv .driver-config__content{
        padding-left: 15px;
    }
    #show_completeMachineRecovery .driver-check-content__wrapper {
        margin-left: 0px;
        padding-left: 0px;
        padding-right: 0;
    }
    .script_form_title{
        width: 12%;
    }
    .script_form_content{
        width: 87%;
    }
    #show_completeMachineRecovery .select-special .selection{
        height: 34px;
        display: inline-block;
        width: 100%;
        line-height: 34px;
    }
    #show_completeMachineRecovery .select-special .select2-container{
        height: 34px;
        display: inline-block;
        width: 100% !important;
        line-height: 34px;
    }
    #show_completeMachineRecovery .select-special .select2-selection{
        height: 34px;
        display: inline-block;
        width: 100% !important;
        line-height: 34px;
        border: 1px solid #e6e6e6;
        border-radius: 4px !important;
    }
    .select2-dropdown,.select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #e6e6e6;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{
        background-color: #F0F0F0;
        color: #333;
    }
    .recover_radio input[type="radio"]{
        margin: 0;
        margin-right: 8px;
        outline: none;
        appearance: none;
        width: 16px;
        height: 16px;
        border-radius: 50% !important;
        border: solid 1px #999999;
        box-sizing: border-box;
        vertical-align: middle;
    }
    .recover_radio input[type="radio"]:checked, .virus-item input[type="radio"]:active, .virus-item input[type="radio"]:active:checked{
        background-image: url(../../../img/platform/vinblue.svg) !important;
        background-position: -57px -12px !important;
        border: 0 !important;

    }
    div[class*='icheckbox_'], div[class*='iradio_']{
        top: 2px !important;
    }
</style>
<!-- 目标机配置-->
<div class="form-group">
    <label class="control-label col-md-3">
        <span class="required">* </span>
        <?php echo $LANG['UI_MACHINE_OS_TARGET_HOST_CONFIG'];?>
    </label>
    <div class="col-md-7">
        <div class="add-list">
            <div class="accordion targetBox" role="tablist">


            </div>
        </div>

    </div>
</div>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./scripts/platform/component/transfer_network.js" type="text/javascript"></script>
<script src="./scripts/platform/recovery/client_recovery.js" type="text/javascript"></script>
