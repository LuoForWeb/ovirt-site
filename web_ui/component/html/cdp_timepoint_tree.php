<div class="alert alert-danger display-none selecttimepointtip">
</div>
<div class="row tab-pane__row">
    <!-- 恢复客户端 -->
    <div class="col-md-3 tab-pane__row__source">
        <div class="form-group src-wrap">
            <div class="src-wrap__title">
                <span>
                    <i class="levelchild viconfont vicon-ge_backup_host"></i> <?php echo $LANG['UI_VOL_CDP_RECOVERY_DATA_SOURCE']; ?>
                </span>
            </div>
            <div class="src-wrap__content">
                <div class="src-wrap__content__search">
                    <select class="bs-select width100p form-control" style="height: 34px" data-show-subtext="true" id="storageselect">
                    </select>
                    <select class="bs-select width100p form-control display-none" style="height: 34px" name="" id="nodeselect">
                    </select>
                </div>
                <div class="src-wrap__content__search">
                    <div class="vm_tree_div">
                        <div class="width100p searchDiv">
                            <input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_SEARCH_CLIENT_KEY_WORD_TIPS']; ?>" class="form-control" id="searchAgent" />
                        </div>
                    </div>
                </div>
                <div class="src-wrap__content__ztree">
                    <div class="two_tree vcenter-tree" style="height: calc(100% - 46px);">
                        <ul id="recovery_agent_tree" class="ztree"></ul>
                    </div>
                    <div class="alert alert-block alert-info fade in display-hide" id="nopointtips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4 class="alert-heading">
                            <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                        </h4>
                        <ol class="alert-ol">
                            <li><?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_NO_AVAILABLE_RECOVERY_DATA'];?></li>
                            <li>
                                <?php echo $LANG['UI_DB_CDP_RECOVER_PROCEED_FIRST'];?>
                                <a id="tobackup" class="alert-link"><?php echo $LANG['UI_VOL_CDP_CREATE_BAK_JOB'];?></a>
                            </li>
                        </ol>
                    </div>
                    <div class="alert alert-block alert-info fade in display-hide" id="novolcdpagent">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4 class="alert-heading">
                            <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                        </h4>
                        <ol class="alert-ol">
                            <li><?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_NO_AVAILABLE_RECOVERY_DATA'];?></li>
                            <li>
                                <?php echo $LANG['UI_DB_CDP_RECOVER_PROCEED_FIRST'];?>
                                <a id="tobackup" class="alert-link"><?php echo $LANG['UI_VOL_CDP_CREATE_BAK_JOB'];?></a>
                            </li>
                        </ol>
                    </div>
                    <div class="alert alert-block alert-info fade in display-hide" id="nosearchtips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ul class="alert-ul">
                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                            <li>
                                <?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="volBackupSet" class="col-md-8 VMList-div display-none">
        <div class="addTitle">
            <span><?php echo $LANG['UI_PLATFORM_CHOOSE_POINT'];?></span>
        </div>
        <div class="addIt-list">
            <!-- 备份时间集 -->
                <div id="show_cdp_timepoint">
                    <?php include_once('vol_cdp_backup_set_info.php'); ?>
                </div>
        </div>
    </div>

    <div id="noCheckTips" class="col-md-8 VMList-div">
        <div class="alert alert-block alert-info fade in" id="step1tips">
            <button type="button" class="close" data-dismiss="alert"></button>
            <ul class="alert-ul">
                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                <li>
                    <?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_PLEASE_SELECT_A_RECOVERY_SOURCE'];?>
                </li>
            </ul>
        </div>
    </div>
</div>
