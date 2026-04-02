<div class="accordion strategyOne">
    <div class="panel panel-default strategy-panel">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover"
                    data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupTime"
                    aria-expanded="true">
                    <i class="viconfont vicon-shijian2 font-green-seagreen"></i>
                    <span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
                    <span class="strategyDes backupTimeDes"></span>
                </a>
            </h4>
        </div>
        <div id="backupTime" class="panel-collapse collapse in">
            <div class="panel-body">
                <div class="col-md-12">
                    <!-- 任务分布 -->
                    <div class="form-group">
                        <label
                            class="control-label col-md-2 form-group-label"><?php echo $LANG['UI_PUBLIC_TASK_CROWD'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="taskCrowd mb15" id="backupCrowd"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2"><?php echo $LANG['UI_BACKUP_TYPE'] ?>
                        </label>
                        <div class="col-md-3">
                            <select class="form-control select2me input-sm" id="backuptype">
                                <option value="strategy"><?php echo $LANG['UI_BACKUP_AS_STRATEGY'] ?></option>
                                <option value="oncetime"><?php echo $LANG['UI_BACKUP_AS_ONCETIME'] ?></option>
                                <option value="manual"><?php echo $LANG['UI_BACKUP_AS_MANUAL'] ?></option>
                            </select>
                        </div>

                    </div>

                    <div class="form-group setStrategy">
                        <label class="control-label col-md-2 form-group-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_BACKUP_SET_STRATEGY'] ?>
                        </label>
                        <div class="col-md-10 form-group-content">
                            <div class="input-group form-group-boxes" id="strategymode">
                                <label class="full" style="padding-right: 20px;"><input type="checkbox" id="fullBackup"
                                        data-checkbox="icheckbox_square-blue" data-mode="1"
                                        class="icheck"><?php echo $LANG['UI_BACKUP_FULL'] ?></label>
                                <label class="incr" style="padding-right: 20px;"><input type="checkbox" id="incrBackup"
                                        data-checkbox="icheckbox_square-blue" data-mode="2"
                                        class="icheck"><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></label>
                                <label class="diff" style="padding-right: 20px;"><input type="checkbox" id="diffBackup"
                                        data-checkbox="icheckbox_square-blue" data-mode="3"
                                        class="icheck"><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></label>
                                <label class="pIncr" style="padding-right: 20px;"><input type="checkbox"
                                        id="pincrBackup" data-checkbox="icheckbox_square-blue" data-mode="9"
                                        class="icheck"><?php echo $LANG['UI_BACKUP_PERMANENT_INCREMENT'] ?></label>
                                <label class="dbLog display-none" style="padding-right: 20px;"><input type="checkbox"
                                        id="logBackup" data-checkbox="icheckbox_square-blue" data-mode="4"
                                        class="icheck"><?php echo $LANG['UI_BACKUP_ARCHIVE_LOG_BACKUP'] ?></label>
                                <label class="autoLog display-none" style="padding-right: 20px;">
                                    <input type="checkbox" id="autoLogBackup" data-checkbox="icheckbox_square-blue" data-mode="10" class="icheck"><?php echo $LANG['UI_DB_BACKUP_LOG'] ?>
                                    </label>
                                <!-- 时间策略提示 -->
                                <!-- 完备 增备 差异 永久 -->
                                <a class="popovers time-strategy-tips all-strategy-tips" data-container="body"
                                    data-trigger="hover" data-placement="right" data-html="true" data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_INCR_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_DIFF_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_PINCR_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_THREE_TOOLTIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 完备 增备 永久-->
                                <a class="popovers time-strategy-tips no-diff-tips display-none" data-container="body"
                                    data-trigger="hover" data-placement="right" data-html="true" data-content="
                                    <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                        $LANG['UI_STRATEGY_INCR_TOOLTIPS'] . "<br><br>" .
                                        $LANG['UI_STRATEGY_PINCR_TOOLTIPS'] . "<br><br>" ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 完备 增备 差异 -->
                                <a class="popovers time-strategy-tips no-pIncr-tips display-none" data-container="body"
                                    data-trigger="hover" data-placement="right" data-html="true" data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_INCR_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_DIFF_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_THREE_TOOLTIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 完备 增备 -->
                                <a class="popovers time-strategy-tips no-full-pIncr-tips display-none"
                                    data-container="body" data-trigger="hover" data-placement="right" data-html="true"
                                    data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_INCR_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_THREE_TOOLTIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 数据库提示 -->
                                <!-- 完备 增备 差异 日志 -->
                                <a class="popovers time-strategy-tips db-tips display-none" data-container="body"
                                    data-trigger="hover" data-placement="right" data-html="true" data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_INCR_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_DIFF_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_LOG_TOOLTIPS'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 完备 增备 日志 -->
                                <a class="popovers time-strategy-tips db-no-diff-tips display-none"
                                    data-container="body" data-trigger="hover" data-placement="right" data-html="true"
                                    data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_INCR_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_LOG_TOOLTIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 完备 差异 日志 -->
                                <a class="popovers time-strategy-tips db-no-incr-tips display-none"
                                    data-container="body" data-trigger="hover" data-placement="right" data-html="true"
                                    data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_DIFF_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_LOG_TOOLTIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 完备 增量 差异 永久 日志 -->
                                <a class="popovers time-strategy-tips db-all-tips display-none" data-container="body"
                                    data-trigger="hover" data-placement="right" data-html="true" data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_INCR_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_DIFF_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_PINCR_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_LOG_TOOLTIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- TIDB的tips, 日志备份有特殊提示, 完备 增量 差异 日志 -->
                                <a class="popovers time-strategy-tips db-tidb-tips display-none" data-container="body"
                                    data-trigger="hover" data-placement="right" data-html="true" data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_LOG_TOOLTIPS_TIDB']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 完备 日志 -->
                                <a class="popovers time-strategy-tips db-log-tips display-none" data-container="body"
                                    data-trigger="hover" data-placement="right" data-html="true" data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_LOG_TOOLTIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 完备 增备 差异 -->
                                <!-- SAP HANA 没有日志备份 -->
                                <a class="popovers time-strategy-tips db-no-log-tips display-none" data-container="body"
                                   data-trigger="hover" data-placement="right" data-html="true" data-content="
                                <?php echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_INCR_TOOLTIPS'] . "<br><br>" .
                                    $LANG['UI_STRATEGY_DIFF_TOOLTIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                                <!-- 永久 -->
                                <a class="popovers time-strategy-tips pIncr-tips display-none" data-container="body"
                                    data-trigger="hover" data-placement="right" data-html="true" data-content="
                                <?php echo $LANG['UI_STRATEGY_PINCR_TOOLTIPS'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-offset-2 col-md-10">
                            <div class="portlet">
                                <div class="portlet-body">
                                    <div class="panel-group accordion" id="backupTimestrategy">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group display-hide setOnceTime">
                        <div class="onceTime-content">
                            <label class="control-label col-md-2">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_BACKUP_SET_TIME'] ?>
                            </label>
                            <div class="onceTime-content-input">
                                <div class="input-group date form_datetime">
                                    <input type="text" size="16" readonly id="oncetime" class="form-control input-sm"
                                        style="width: 160px;">
                                    <span class="input-group-btn">
                                        <button class="btn default input-sm" id="resetdate" type="button"><i
                                                class="fa fa-times"></i></button>
                                        <button class="btn default date-set input-sm" type="button"><i
                                                class="viconfont vicon-ge_calendar"></i></button>
                                    </span>
                                </div>
                            </div>
                            <div class="onceTime-content-tips">
                                <a class="popovers " data-container="body" data-trigger="hover" data-placement="right"
                                    data-content="<?php echo $LANG['UI_PLATFORM_STRATEGY_DELTASK_ONETIME'] ?>"
                                    data-original-title="" title="">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>