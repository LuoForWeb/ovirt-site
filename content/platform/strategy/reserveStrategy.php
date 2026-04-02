<div class="accordion strategyOne reserve-strategy-form">
    <div class="panel panel-default strategy-panel">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#reserve" aria-expanded="true">
                    <i class="viconfont vicon-baoliucelve font-green-seagreen"></i>
                    <span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
                    <span class="strategyDes reserveDes"></span>
                </a>
            </h4>
        </div>
        <div id="reserve" class="panel-collapse collapse">
            <div class="panel-body">
                <div class="form-group reserveModeDiv">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_GLOBAL_STRATEGY_RESERVE_MODE'] ?>
                    </label>
                    <div class="col-md-4">
                        <select class="form-control select2me input-sm" id="reserveMode">
                            <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT'] ?></option>
                            <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN'] ?></option>
                        </select>
                    </div>
                    <div class="mt5">
                        <a class="popovers reserveModeTips display-none" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_MONGODB_RESERVE_MODE_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_BACKUP_DATA_RESERVE_TYPE'] ?>
                    </label>
                    <div class="col-md-4" style="display: flex;align-items: center;">
                        <select class="form-control select2me input-sm" id="reserveType">
                            <option value="1"><?php echo $LANG['UI_BACKUP_NUM'] ?></option>
                            <option value="2"><?php echo $LANG['UI_BACKUP_DAY'] ?></option>
                        </select>
                    </div>
                    <div class="mt5">
                        <a class="popovers reserveTips1" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_RESERVER_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                        <a class="popovers reserveTips2 display-none" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_PERMANENT_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <div class="form-group reserveNum">
                    <label class="control-label col-md-2"><?php echo $LANG['UI_BACKUP_RESERVE_NUM'] ?>
                    </label>
                    <div class="col-md-2">
                        <div id="spinnerNum">
                            <div class="input-group spinner-group">
                                <input id="spinnerNumInput" class="spinner-input form-control input-sm" maxlength="3">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn spinner-up default input-sm">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="btn spinner-down default input-sm">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group reserveDay display-hide">
                    <label class="control-label col-md-2"><?php echo $LANG['UI_BACKUP_RESERVE_DAY'] ?>
                    </label>
                    <div class="col-md-2">
                        <div id="spinnerDay">
                            <div class="input-group spinner-group">
                                <input id="spinnerDayInput" class="spinner-input form-control" maxlength="3">
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
                    </div>
                </div>

                <div class="form-group GFSdiv">
                    <label
                        class="control-label col-md-2 GFSLable form-group-label"><?php echo $LANG['WEB_VM_GFS_RESERVE_STRATEGY'] ?></label>
                    <div class="col-md-4 form-group-content">
                        <input type="checkbox" id="GFSflag" class="make-switch" data-on-color="primary"
                            data-off-color="info" data-size="small"
                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right"
                            data-html="true" data-content="<?php echo $LANG['WEB_VM_GFS_RESERVE_STRATEGY_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>

                <div id="GFSDiv" class="display-none"></div>
            </div>
        </div>
    </div>
</div>

        <!-- 磁带策略开始 -->
        <div class="accordion strategyOne tape-strategy-form display-hide">
            <div class="panel panel-default strategy-panel">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body"
                            data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne"
                            href="#tape" aria-expanded="true" data-original-title="" title="">
                            <i class="iconfont icon-baoliu font-green-seagreen"></i>
                            <span class="font-green-seagreen"><?php echo $LANG['UI_JOB_STRATEGY'] ?></span>
                            <span class="strategyDes tapeDes"
                                title="<?php echo $LANG['UI_TAPE_USE_GROUP_STRATEGY'] ?>"><?php echo $LANG['UI_TAPE_USE_GROUP_STRATEGY'] ?></span>
                        </a>
                    </h4>
                </div>
                <div id="tape" class="panel-collapse collapse">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <!-- 磁带相关策略 -->
                            <div class="form-group">
                                <label class="control-label col-md-2">
                                    <?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY'] ?>
                                </label>
                                <div class="col-md-4 mt5">
                                    <span class="generate"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-2">
                                    <?php echo $LANG['UI_TAPE_RESERVE_STRATEGY'] ?>
                                </label>
                                <div class="col-md-4 mt5">
                                    <span class="reserve"></span>
                                </div>
                            </div>
                            <!-- 磁带相关策略END -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 磁带策略结束 -->

<script type="text/javascript" src="./scripts/plugins/jquery/jquery.reserveStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.GFSStrategy.js"></script>