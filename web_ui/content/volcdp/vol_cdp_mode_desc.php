<?php include_once '../../tpl/permission.php';?>
<div style="width: 750px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
     aria-labelledby="drawer-1-title" aria-hidden="true" id="task_exec_mode_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title">
                <span id="drawer-1-title-1"><i class="viconfont vicon-a-Eyesyanjing"></i></span>
                <span style="vertical-align: top" id="drawer-1-title-2"><?php echo $LANG['UI_BACKUP_MODE']; ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">
            <div class="row tab-pane__row" >
                <div class="margin20  height300 bd1de5">
                    <div class = "volcdp-backupmode-catback-data display-none">
                        <img id = "volcdpcatbackdata" class="volcdp-backupmode-catbackup-data-map">
                    </div>
                    <div class="margin20 height250">
                        <!-- 实时复制 -->
                        <div class="realtimesyncandautotakeover ">
                            <div class="col-md-2">
                                <div class="hosttostandby-host-des">
                                    <img id = "volcdpbackuphostimg">
                                </div>
                                <div class="hosttostandby-host-textdes textalignc">
                                    <label class="fs12"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_SOURCE_HOST']; ?></label>
<!--                                    <span id = "hostIpDesc" class="colorgreen hostip">--><?php //echo $_SERVER['SERVER_ADDR']; ?><!--</span>-->
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="volcdp-data-to-backupserver">
                                    <!-- 主机数据向备机流向示意图 -->
                                    <div class="initial-sync-host-to-server volcdp-host-server-nostatus display-none">  <!--连接无状态，主机到备份服务器-->
                                        <img id = "connectednostate" class="mt10" >
                                    </div>

                                    <div class="initial-sync-host-to-server volcdp-host-server display-none">  <!--初始同步-数据从主机到备份服务器-->
                                        <img id = "volcdpbackupservertostandby" >
                                    </div>

                                    <div class="realtime-sync-host-to-server volcdp-host-to-sever-heart"> <!-- 任务回切心跳包-数据从备份服务器到备机 -->
                                        <img id = "volcdphosttoseverheart" >
                                    </div>

                                    <div class="realtime-sync-host-to-server volcdp-host-to-sever-faild display-none"> <!-- 接管中-主机到备份服务器状态 -->
                                        <img id = "connectednostate" class="mt10">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="hosttostandby-server-des">
                                    <img id = "volcdpbackupserverimg">
                                </div>
                                <div class="hosttostandby-server-textdes min-width150 textalignc ml-10_en">
                                    <label class="fs12"><?php echo $LANG['UI_VOL_CDP_BAK_SERVER']; ?></label>
                                    <span id = "hostIpDesc" class="colorgreen backupserverip"><?php echo $_SERVER['SERVER_ADDR']; ?></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <!-- 备份服务器到备机的数据流向示意图 -->
                                <div class="volcdp-data-to-backupserver">

                                    <div class="initial-sync-host-to-server volcdp-backupserver-to-nostatus display-none">  <!--连接无状态，主机到备份服务器-->
                                        <img id = "connectednostate" class="ml-35 mt10" >
                                    </div>

                                    <div class="volcdp-connected-no-state connected-no-state display-none">  <!--初始同步-备份服务器到备机连通无状态-->
                                        <img id = "connectednostate" class="mt10" >
                                    </div>

                                    <div class="realtime-sync-host-to-server ml-35 volcdp-backupserver-to-standby display-none"> <!--实时同步-数据从主机到备份服务器-->
                                        <img id = "volcdpbackupservertostandby" >
                                    </div>

                                    <div class="realtime-sync-host-to-server volcdp-host-to-sever-heart"> <!-- 任务回切心跳包-数据从备份服务器到备机 -->
                                        <img id = "volcdphosttoseverheart" class="ml-35">
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="hosttostandby-standby-des">
                                    <img id = "volcdpstandbyimg">
                                </div>
                                <div class="hosttostandby-standby-textdes min-width150 ml5 ml-r30_en">
                                    <label class="fs12"><?php echo $LANG['UI_VOL_CDP_STANDBY']; ?></label>
<!--                                    <span id = "hostIpDesc" class="colorgreen standbyip">192.168.1.102</span>-->
                                </div>
                            </div>
                        </div>
                        <!-- 实时同步，未开启自动接管 -->
                        <div class="realtimesyncmap display-none">
                            <div class="col-md-4 textalignc">
                                <div class="volcdpbackuphostdes">
                                    <img id = "volcdpbackuphostimg">
                                </div>
                                <div class="volcdpbackuphosttextdes textalignc">
                                    <label class="fs12"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_SOURCE_HOST']; ?></label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <img id="volcdphosttobackupserver-connectednostate">
                                <img id = "volcdphosttobackupserver" class="volcdp-host-to-backupserver display-none">
                            </div>

                            <div class="col-md-4">
                                <div class="volcdpbackuphostdes">
                                    <img id = "volcdpbackupserverimg">
                                </div>
                                <div class="volcdpbackuphosttextdes min-width200 min-width150_en ml-70_en">
                                    <label class="fs12"><?php echo $LANG['UI_VOL_CDP_BAK_SERVER']; ?></label> 
                                    <span id = "hostIpDesc" class="colorgreen backupserverip"><?php echo $_SERVER['SERVER_ADDR']; ?></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="margin20 height180 mt30">
                    <div class="volcdp-backupmode-view-cuttingline">
                        <span class="volcdp-backupmode-overview-text"><?php echo $LANG['UI_VOL_CDP_BACKUP_EXECUTION_MODE_DESC'] ?></span>
                    </div>

                    <div class="height150 volcdp-backupmode-reail-time-sync-desc mt15 ">
                        <!--实时同步未开启自动接管描述 -->
                        <div class="realtimesyncoverview display-none">
                            <span> <?php echo $LANG['UI_VOL_CDP_REAL_SYNCS_MODE_DESC']; ?> </span>
                            <div class=""><?php echo $LANG['UI_VOL_CDP_MODE_DESC'];?></div>
                            <div class="mt15" >
                                <a class="taskstageherf initSyncHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['UI_VOL_CDP_INIT_SYNC']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REAL_TIME_TAKEOVER_INIT_SYNC_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf initSyncHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_REAL_TIME_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REAL_TIME_NO_TAKEOVER_INIT_SYNC_DESC'] ?></span>
                                </a>
                            </div>
                        </div>

                        <!-- 实时同步开启自动接管描述 add-->
                        <div class="realTimeSyncTakeoverView display-none">
                            <!-- 实时同步+ 自动接管 -->
                            <span id = "realTimeSyncAndAutotakeover">
                                <?php echo $LANG['UI_VOL_CDP_REAL_SYNC_OR_REAL_REPLI'] ;?>
                                <div class=""><?php echo $LANG['UI_VOL_CDP_MODE_DESC'];?></div>
                            </span>

                            <div class="mt15" >
                                <a class="taskstageherf initSyncHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['UI_VOL_CDP_INIT_SYNC'] ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REAL_TIME_TAKEOVER_INIT_SYNC_DESC']?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf initSyncHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_REAL_TIME_SYN'];?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_BACKUP_REAL_TIME_SYNC'];?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf takeoverStartingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_TAKEOVER_STARTING'];?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_PLATFORM_DES_TAKEOVER_STARTING_DESC'];?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf takingoverHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_IN_TAKEOVER']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_PLATFORM_DES_IN_TAKEOVER_DESC']; ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf taskBackcuttingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_BACK_INITIAL_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_PLATFORM_DES_BACK_INITIAL_SYN_DESC']; ?> </span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf taskBackcuttingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_BACK_TIME_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_PLATFORM_DES_BACK_SYN_DESC']; ?> </span>
                                </a>
                            </div>
                        </div>

                        <!-- 主备复制未开启自动接管描述 add-->
                        <div class="realReplicationNotakeoverView display-none">
                            <div class="real-replication-mode-desc">
                                <span><?php echo $LANG['UI_VOL_CDP_REAL_REPLICATION_MODE_DESC']; ?></span>
                                <div class=""><?php echo $LANG['UI_VOL_CDP_MODE_DESC'];?></div>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf realTimeRepliModeInitSyncherf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['UI_VOL_CDP_INIT_SYNC']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_INIT_SYSNC_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf realTimeRepliRealTtimeSyncherf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_REAL_TIME_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_SYSNC_DESC'] ?></span>
                                </a>
                            </div>
                        </div>


                        <!-- 主备复制开启自动接管描述 add-->
                        <div class="realReplicationTakeoverView display-none">
                            <div class="real-replication-mode-desc">
                                <span><?php echo $LANG['UI_VOL_CDP_REAL_COPY_AND_AUTO_TAKEOVER_DESC']; ?></span>
                                <div class=""><?php echo $LANG['UI_VOL_CDP_MODE_DESC'];?></div>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf initSyncHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['UI_VOL_CDP_INIT_SYNC']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_INIT_SYNC_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf realTtimeSyncHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_REAL_TIME_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_SYSNC_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf takeoverStartingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_TAKEOVER_STARTING']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_TAKEOVER_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf takingoverHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_IN_TAKEOVER']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_PLATFORM_DES_IN_TAKEOVER_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf taskBackcuttingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_BACK_INITIAL_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_BACK_INITIAL_SYN_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf taskBackcuttingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_BACK_TIME_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_BACK_TIME_SYN_DESC'] ?></span>
                                </a>
                            </div>
                        </div>

                        <!-- 实时备份+主备复制未开启自动接管描述 add-->
                        <div class="realtimereplicationoverview display-none">
                            <div class="real-replication-mode-desc">
                                <span><?php echo $LANG['UI_VOL_CDP_REAL_SYNC_AND_REAL_REPLI_MODE_DESC']; ?></span>
                                <div class=""><?php echo $LANG['UI_VOL_CDP_MODE_DESC'];?></div>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf realTimeRepliModeInitSyncherf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['UI_VOL_CDP_INIT_SYNC']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_INIT_SYNC_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf realTimeRepliRealTtimeSyncherf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_REAL_TIME_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_SYN_DESC'] ?></span>
                                </a>
                            </div>
                        </div>

                        <!-- 实时备份+主备复制开启自动接管描述 add-->
                        <div class="autotakeoververview display-none">
                            <div id="realTimeCopyAndBackupOrAutotakeover">
                                <span><?php echo $LANG['UI_VOL_CDP_REAL_COPY_AND_BACKUP_AUTO_TAKEOVER_DESC']; ?></span>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf initSyncHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['UI_VOL_CDP_INIT_SYNC']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_INIT_SYNC_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf realTtimeSyncHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_REAL_TIME_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_SYN_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf takeoverStartingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_TAKEOVER_STARTING']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_REAL_TIME_TAKEOVER_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf takeoverStartingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_IN_TAKEOVER']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_PLATFORM_DES_IN_TAKEOVER_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf taskBackcuttingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_BACK_INITIAL_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_VOL_CDP_REPLICATION_TAKEOVER_INITIAL_SYN_DESC'] ?></span>
                                </a>
                            </div>
                            <div class="mt15" >
                                <a class="taskstageherf taskBackcuttingHerf">
                                    <span class="taskstagetitiletext"><?php echo $LANG['WEB_PLATFORM_DES_BACK_TIME_SYN']; ?></span>:
                                    <span class="taskstagecontenttext"><?php echo $LANG['UI_PLATFORM_DES_BACK_SYN_DESC'] ?></span>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>