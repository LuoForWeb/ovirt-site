<div class="alert alert-danger display-none selecttimepointtip">
</div>
<div class="row tab-pane__row">
    <div class="col-md-4 tab-pane__row__source">
        <div class="form-group src-wrap">
            <div class="src-wrap__title">
                <span><?php echo $LANG['UI_PUBLIC_SELECT']?><?php echo $LANG['UI_RECOVERY_BACKUP_POINT_LOWERCASE'] ?></span>
            </div>
            <div class="src-wrap__content">
                <div class="src-wrap__content__search">
                    <select class="bs-select width100p form-control" style="height: 34px" data-show-subtext="true" id="storageselect">
                    </select>
                    <select class="bs-select width100p form-control display-none" style="height: 34px" data-show-subtext="true" id="nodeselect">
                    </select>
                    <div class="vm_tree_div">
                        <div class="input-icon width100p">
                            <input type="text" maxlength="128" placeholder="<?php echo $LANG['WEB_OS_SEARCH_NAME'] ?>" class="form-control" id="searchname" autocomplete="off" />
                        </div>
                    </div>
                </div>
                <div class="src-wrap__content__ztree" style="height: calc(100% - 92px)">
                    <div class="two_tree vcenter-tree">
                        <ul id="pointtypetree" class="ztree bd1de5 tree_div ztree-fa"></ul>
                        <ul id="vmtypetree" class="ztree bd1de5 tree_div display-hide"></ul>
                    </div>
                    <div class="alert alert-block alert-info fade in display-hide" id="nopointtips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ul class="alert-ul">
                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                            <li>
                                <strong><?php echo $LANG['UI_RECOVERY_NO_VM_TITLE'] ?></strong>
                                <a id="tobackup"><small><?php
                                        if ($_GET['recovery_type'] == 'os') {
                                           echo $LANG['UI_RECOVERY_MACHINE_NO_TIMEPOINT_TIPS'];
                                        } elseif ($_GET['subtype'] == 1) {
                                            echo $LANG['UI_RECOVERY_NO_VM_TIPS'];
                                        } elseif ($_GET['subtype'] == 2) {
                                            echo $LANG['UI_RECOVERY_PRIVATE_CLOUD_NO_VM_TIPS'];
                                        } else {
                                            echo $LANG['UI_RECOVERY_AWS_NO_TIMEPOINT_TIPS'];
                                        } ?></small></a>
                            </li>
                        </ul>
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
    <div class="col-md-8 VMList-div">
        <div class="addTitle">
            <span><?php echo $LANG['UI_RECOVERY_ALREADY_SELECT_POINT'] ?></span>
        </div>
        <div class="addIt-list">
            <ul class="feeds addVMList" id="showGroupList">
            </ul>
            <ul class="feeds addVMList" id="cbrTimeGroupList">
            </ul>
        </div>
    </div>
</div>
<input type="hidden" id="vm_ponint_subtype" value="<?php echo $_GET['subtype'];?>"/>
<link rel="stylesheet" href="./scripts/components/timePointDetail/css/timePointDetail.css"></link>
<script src="./scripts/components/timePointDetail/js/timePointDetail.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/clipboard/clipboard.min.js"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
