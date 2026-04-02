<!-- 虚拟机任务详情 - 传输策略 -->
<div class="strategy-group__form">
    <div class="backup_style">
        <!-- 传输模式 -->
        <div class="strategy-group__form__item transportModediv">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>
            </div>
            <div id="transportMode" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
        <!-- 代理资源池 -->
        <div class="strategy-group__form__item agentPoolDiv display-none">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_AGENT_POOL'] ?>
            </div>
            <div id="agentPool" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
        <!-- 代理 -->
        <div class="strategy-group__form__item applianceDiv display-none">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?>
            </div>
            <div id="appliance" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
        <!-- 加密传输 -->
        <div class="strategy-group__form__item transportEncryptdiv">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>
            </div>
            <div id="transportEncrypt" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
        <!-- 数据传输网段 -->
        <div class="strategy-group__form__item ipSegmentDiv display-none">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_VM_DATA_TRANSFER_NET_SEGMENT'] ?>
            </div>
            <div id="ipSegment" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
        <!-- 备份节点IP -->
        <div class="strategy-group__form__item baknodeIpDiv display-none">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_BACKUP_NODE_TRANSFER_IP'] ?>
            </div>
            <div id="baknodeIp" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
    </div>
    <div class="recoveryDiv">
        <!-- 恢复任务传输模式 -->
        <div class="strategy-group__form__item recoveryTransportDiv display-none">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>
            </div>
            <div id="recoveryTransport" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
        <!-- 恢复代理资源池 -->
        <div class="strategy-group__form__item agentPoolRecDiv display-none">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_AGENT_POOL'] ?>
            </div>
            <div id="agentPoolRec" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
        <!-- 恢复任务代理 -->
        <div class="strategy-group__form__item applianceRecDiv display-none">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?>
            </div>
            <div id="applianceRec" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
        <!-- 恢复任务加密传输 -->
        <div class="strategy-group__form__item recoveryEncryptdiv">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>
            </div>
            <div id="recoveryEncrypt" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
        <!-- 恢复任务数据传输网段 -->
        <div class="strategy-group__form__item reIpSegmentDiv display-none">
            <div class="strategy-group__form__item__label col-md-4">
                <?php echo $LANG['UI_VM_DATA_TRANSFER_NET_SEGMENT'] ?>
            </div>
            <div id="reIpSegment" class="strategy-group__form__item__value col-md-8">
            </div>
        </div>
    </div>

    <!-- 传输线程 -->
    <div class="strategy-group__form__item threadCountDiv">
        <div class="strategy-group__form__item__label col-md-4">
            <?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
        </div>
        <div id="threadCount" class="strategy-group__form__item__value col-md-8">
        </div>
    </div>
    <!-- 传输压缩 -->
    <div class="strategy-group__form__item transferCompressDiv display-none">
        <div class="strategy-group__form__item__label col-md-4">
            <?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS'] ?>
        </div>
        <div id="transferCompress" class="strategy-group__form__item__value col-md-8">
        </div>
    </div>
    <!-- 异步传输 -->
    <div class="strategy-group__form__item asyncTransferDiv display-none">
        <div class="strategy-group__form__item__label col-md-4">
            <?php echo $LANG['UI_BACKUP_ASYNC_TRANSFER'] ?>
        </div>
        <div id="asyncTransfer" class="strategy-group__form__item__value col-md-8">
        </div>
    </div>
    <!-- 并行传输 -->
    <div class="parallelTransferDiv">
        <div class="strategy-group__title">
            <span class="decoration me-4"></span><span
                class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_PARALLEL_TRANSFER'] ?></span>
        </div>
        <div class="strategy-group__form">
            <div class="strategy-group__form__item ptVmCountDiv">
                <div class="strategy-group__form__item__label col-md-4">
                    <?php echo $LANG['UI_BACKUP_PARALLEL_TRANSFER_VM_COUNT'] ?>
                </div>
                <div id="ptVmCount" class="strategy-group__form__item__value col-md-8">
                </div>
            </div>
            <div class="strategy-group__form__item">
                <div class="strategy-group__form__item__label col-md-4">
                    <?php echo $LANG['UI_BACKUP_SINGLE_VM_PARALLEL_DISK_TRANSFER_COUNT'] ?>
                </div>
                <div id="ptDiskCount" class="strategy-group__form__item__value col-md-8">
                </div>
            </div>
            <div class="strategy-group__form__item">
                <div class="strategy-group__form__item__label col-md-4">
                    <?php echo $LANG['UI_BACKUP_VM_SINGLE_DISK_PARALLEL_TRANSFER_COUNT'] ?>
                </div>
                <div id="ptSegmentCount" class="strategy-group__form__item__value col-md-8">
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="./scripts/platform/component/vm_job_transfer.js"></script>