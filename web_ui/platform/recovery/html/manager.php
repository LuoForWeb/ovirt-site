<style>
    .module-card{
        width: 25%;
        float: left;
        padding: 20px;
    }
</style>

<div class="vinchin-wrap recover-center-wrap hover-scroll-y" id="recovery_center" style="zoom: 1;">
    <div class="vinchin-wrap__header"><span class="decoration me-8"></span><span class="vinchin-wrap__header__text">备份数据恢复</span></div>
    <div class="vinchin-wrap__group">
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">虚拟化/超融合</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="主流虚拟化或超融合平台的虚拟机备份数据恢复">主流虚拟化或超融合平台的虚拟机备份数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-vm"></i></div>
            </div>
            <div class="lower-region">
                <div class="lower-region__group multiple-items">
                    <button type="button" class="btn route-btn level1" title="">
                        <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="vmRecover.html">
                            <span class="route-btn__text">恢复</span>
                        </a>
                    </button>
                    <button type="button" class="btn route-btn level1" title="">
                        <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="graininess.html?recovery_type=vm&subtype=1">
                            <span class="route-btn__text">细粒度恢复</span>
                        </a>
                    </button>
                </div>
                <div class="lower-region__group multiple-items">
                    <button type="button" class="btn route-btn level1" title="">
                        <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="instantaneous.html?recovery_type=vm&subtype=1">
                            <span class="route-btn__text">瞬时恢复</span>
                        </a>
                    </button>
                    <button type="button" class="btn route-btn level1" title="">
                        <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="platform.html?recovery_type=vm&subtype=1">
                            <span class="route-btn__text">跨平台恢复</span>
                        </a>
                    </button>
                </div>
            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">私有云</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="私有云平台的实例恢复，如OpenStack平台">私有云平台的实例恢复，如OpenStack平台</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-private-cloud"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="vmRecover.html?sub_module_type=2">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="graininess.html?recovery_type=vm&subtype=2">
                        <span class="route-btn__text">细粒度恢复</span>
                    </a>
                </button>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="instantaneous.html?recovery_type=vm&subtype=2">
                        <span class="route-btn__text">瞬时恢复</span>
                    </a>
                </button>
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="platform.html?recovery_type=vm&subtype=2">
                        <span class="route-btn__text">跨平台恢复</span>
                    </a>
                </button>
            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">公有云</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="公有云环境的云主机备份数据恢复">公有云环境的云主机备份数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-plubic-cloud"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="awsRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="graininess.html?recovery_type=vm&subtype=3">
                        <span class="route-btn__text">细粒度恢复</span>
                    </a>
                </button>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="instantaneous.html?recovery_type=vm&subtype=3">
                        <span class="route-btn__text">瞬时恢复</span>
                    </a>
                </button>
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="platform.html?recovery_type=vm&subtype=3">
                        <span class="route-btn__text">跨平台恢复</span>
                    </a>
                </button>
            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">Kubernetes</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="Kubernetes容器平台的备份数据恢复">Kubernetes容器平台的备份数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overciew-k8s"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="kubernetesRecovery.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>

            </div>

        </div>
    </div>
    <div class="vinchin-wrap__group" style="    clear: both;">
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">文件</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="操作系统内的文件恢复，支持Windows、Linux等主流操作系统">操作系统内的文件恢复，支持Windows、Linux等主流操作系统</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-file"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="fileRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>

            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">NAS</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="基于CIFS/NFS协议的NAS设备备份数据恢复">基于CIFS/NFS协议的NAS设备备份数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-nas"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="nasRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>

            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">对象存储</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="S3对象存储恢复，可实现对每个存储桶中海量文件的高级恢复">S3对象存储恢复，可实现对每个存储桶中海量文件的高级恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-obs"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="obsRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>

            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">Hadoop HDFS</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="Hadoop HDFS大数据平台接口级备份数据恢复">Hadoop HDFS大数据平台接口级备份数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-hadoop"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="hadoopRecovery.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>

            </div>
        </div>
    </div>
    <div class="vinchin-wrap__group" style="    clear: both;">
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">数据库</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="主流数据库平台的应用数据恢复">主流数据库平台的应用数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-database"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="dbRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>

            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">Microsoft 365</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="Microsoft 365 Exchange Online &amp; Server的应用数据恢复">Microsoft 365 Exchange Online &amp; Server的应用数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-m365"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="exchangeRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>

            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">整机</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="操作系统整机备份数据恢复">操作系统整机备份数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-complete-machine"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="machineOsRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="graininess.html?recovery_type=os">
                        <span class="route-btn__text">细粒度恢复</span>
                    </a>
                </button>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="instantaneous.html?recovery_type=os">
                        <span class="route-btn__text">瞬时恢复</span>
                    </a>
                </button>
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="platform.html?recovery_type=os">
                        <span class="route-btn__text">跨平台恢复</span>
                    </a>
                </button>
            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">卷</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="操作系统整机卷级备份数据恢复">操作系统整机卷级备份数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-volume"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="osRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>

            </div>
        </div>
    </div>
    <div class="vinchin-wrap__header continuous-recover-group-header" style="    clear: both;">
        <span class="decoration me-8"></span><span class="vinchin-wrap__header__text">实时保护&amp;复制容灾数据恢复</span></div>
    <div class="vinchin-wrap__group">
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">整机</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="整机连续备份数据恢复">整机连续备份数据恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-zhengjishishi"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="cmVolCdpRecovery.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="graininess.html?recovery_type=cdp">
                        <span class="route-btn__text">细粒度恢复</span>
                    </a>
                </button>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="platform.html?recovery_type=cdp">
                        <span class="route-btn__text">跨平台恢复</span>
                    </a>
                </button>
            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">卷</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="整机连续备份数据卷级恢复">整机连续备份数据卷级恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-zhengjidingshi"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="volCdpRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>
            </div>
        </div>
        <div class="vinchin-wrap__group__card module-card">
            <div class="upper-region">
                <div class="upper-region__left">
                    <div class="upper-region__left__title">数据库</div>
                    <div class="upper-region__left__des"><span class="ellipsis-text" title="主流数据库复制数据的恢复">主流数据库复制数据的恢复</span></div>
                </div>
                <div class="upper-region__right"><i class="viconfont vicon-overview-database"></i></div>
            </div>
            <div class="lower-region__group multiple-items">
                <button type="button" class="btn route-btn level1" title="">
                    <a class="recover-center ajaxify " route-id="parent_data_manager" name="recovery" href="dbCdpRecover.html">
                        <span class="route-btn__text">恢复</span>
                    </a>
                </button>
            </div>
        </div>
    </div>
</div>
