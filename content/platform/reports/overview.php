<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css" />

<link href="./css/platform/report.css" rel="stylesheet" type="text/css"/>

<div class="report-overview">
    <div class="report-overview__header">
        <div class="report-overview__header__title">
            <i class="levelchild viconfont vicon-pt_report_shortage_report me-8"></i>
            <span class="report-overview__header__title__text"><?php echo $LANG['UI_REPORT_OVERVIEW'] ?></span>
        </div>
        <button class="btn green-haze <?php if (!in_array('p_vm_report_export', $_SESSION['permissionArr'])) {
            echo 'display-none';
        } ?>" type="button" id="report_overview_export" >
            <span class="indicator-label">
                <i class="viconfont vicon-a-Share-threefenxiang3 me-2"></i>
                <?php echo $LANG['UI_EXPORT_REPORT'] ?>
            </span>
            <span class="indicator-progress">
                <span class="spinner-border spinner-border-sm me-4"></span><?php echo $LANG['UI_EXPORTING_REPORT'] ?>
            </span>
        </button>
    </div>
    <div class="report-overview__content hover-scroll-y">
        <!-- BEGIN PRODUCT RESOURCE SECTION -->
        <div class="report-section productive-resource-section mb-20 
            <?php if (
                !in_array('global_observer', $_SESSION['permission']) &&
                !array_intersect(
                    ['client', 'vmprotect', 'nas_protect', 'vol_cdp_protect', 'exchange_organization', 'obs_protect', 'hadoop_protect', 'file_copy_protect', 'k8s_protect', 'db_protect', 'dbcdpcopy'],
                    $_SESSION['permission']
                )
            ) {
                echo 'display-none';
            } ?>">
            <div class="report-section__header">
                <span class="decoration"></span>
                <span class="report-section__header__title"><?php echo $LANG['UI_REPORT_ZHILIAO'] ?></span>
            </div>
            <div class="report-section__content">
                <div class="d-flex mb-20">
                    <!-- BEIGIN CLIENT OVERVIEW CARD -->
                    <div class="report-card client-card me-20 <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('client', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="report-card__header"><?php echo $LANG['UI_MOTION_DESTINATION'] ?></div>
                        <div class="client-card__echarts">
                            <div class="client-card__echarts__chart" id="agent_echarts"></div>
                        </div>
                        <div class="client-card__footer">
                            <div class="client-card__footer__item status-box">
                                <div class="status-box__top">
                                    <span class="dot dot--success me-4"></span>
                                    <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                </div>
                                <div class="status-box__bootom">
                                    <span class="dot"></span>
                                    <span id="onlineNumber" class="status-box__bootom__num"></span>
                                </div>
                            </div>
                            <div class="client-card__footer__item status-box">
                                <div class="status-box__top">
                                    <span class="dot dot--secondary me-4"></span>
                                    <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                </div>
                                <div class="status-box__bootom">
                                    <span class="dot"></span>
                                    <span id="offlineNumber" class="status-box__bootom__num"></span>
                                </div>
                            </div>
                            <div class="client-card__footer__item status-box">
                                <div class="status-box__top">
                                    <span class="dot dot--primary me-4"></span>
                                    <span class="status"><?php echo $LANG['UI_REPORT_PROTECTING'] ?></span>
                                </div>
                                <div class="status-box__bootom">
                                    <span class="dot"></span>
                                    <span id="protectedNumber" class="status-box__bootom__num"></span>
                                </div>
                            </div>
                            <div class="client-card__footer__item status-box">
                                <div class="status-box__top">
                                    <span class="dot dot--info me-4"></span>
                                    <span class="status"><?php echo $LANG['UI_VM_REPORT_NOIN_BACKUP'] ?></span>
                                </div>
                                <div class="status-box__bootom">
                                    <span class="dot"></span>
                                    <span id="unprotectedNumber" class="status-box__bootom__num"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END CLIENT OVERVIEW CARD -->

                    <!-- BEIGIN VM OVERVIEW CARD -->
                    <div class="report-card vm-card <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('vmprotect', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="report-card__header"><?php echo $LANG['WEB_PLATFORM_DES_VM'] ?></div>
                        <div class="vm-card__content">
                            <!-- BEGIN VM OVERVIEW CARD -->
                            <div class="virtual-overview-card vm-overview-card me-20 <?php
                            if (!in_array('global_observer', $_SESSION['permission']) && !in_array('vmprotect', $_SESSION['permission'])) {
                                echo ' display-none';
                            }
                            ?>">
                                <div class="virtual-overview-card__header">
                                    <div class="virtual-overview-card__header__box virtual-wrap">
                                        <div class="virtual-wrap__img">
                                            <img src="./img/platform/report/vcenter.svg" />
                                        </div>
                                        <div class="virtual-wrap__info">
                                            <span id="vm_platform" class="virtual-wrap__info__num"></span>
                                            <span class="virtual-wrap__info__des"><?php echo $LANG['UI_HOMEPAGE_VM_PLATFORM'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="virtual-overview-card__content">
                                    <div id="vcenter_echarts" class="vcenter-chart"></div>
                                    <div class="virtual-chart-info flex-direction-row">
                                        <div class="status-box mb-4 me-20">
                                            <div class="status-box__top">
                                                <span class="dot dot--success me-4"></span>
                                                <span class="status"><?php echo $LANG['UI_REPORT_PROTECTING'] ?></span>
                                            </div>
                                            <div class="status-box__bootom">
                                                <span class="dot"></span>
                                                <span id="vm_protected" class="status-box__bootom__num"></span>
                                            </div>
                                        </div>
                                        <div class="status-box">
                                            <div class="status-box__top">
                                                <span class="dot dot--info me-4"></span>
                                                <span class="status"><?php echo $LANG['UI_VM_REPORT_NOIN_BACKUP'] ?></span>
                                            </div>
                                            <div class="status-box__bootom">
                                                <span class="dot"></span>
                                                <span id="vm_unprotected" class="status-box__bootom__num"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END VM OVERVIEW CARD -->

                            <!-- BEGIN PUBLIC CLOUD CARD -->
                            <div class="virtual-overview-card vm-pubpri-overview-card me-20 <?php
                            if (!in_array('global_observer', $_SESSION['permission']) && !in_array('awsprotect', $_SESSION['permission'])) {
                                echo ' display-none';
                            }
                            ?>">
                                <div class="virtual-overview-card__header">
                                    <div class="virtual-overview-card__header__box virtual-wrap">
                                        <div class="virtual-wrap__img">
                                            <img src="./img/platform/report/cloud.svg" />
                                        </div>
                                        <div class="virtual-wrap__info">
                                            <span id="public_cloud" class="virtual-wrap__info__num"></span>
                                            <span class="virtual-wrap__info__des"><?php echo $LANG['UI_PLATFORM_VM_CLOUD_PLATFORM'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="virtual-overview-card__content">
                                    <div id="public_echarts" class="vcenter-chart"></div>
                                    <div class="virtual-chart-info flex-direction-row">
                                        <div class="status-box mb-4 me-20">
                                            <div class="status-box__top">
                                                <span class="dot dot--success me-4"></span>
                                                <span class="status"><?php echo $LANG['UI_REPORT_PROTECTING'] ?></span>
                                            </div>
                                            <div class="status-box__bootom">
                                                <span class="dot"></span>
                                                <span id="protected_public_cloud" class="status-box__bootom__num"></span>
                                            </div>
                                        </div>
                                        <div class="status-box">
                                            <div class="status-box__top">
                                                <span class="dot dot--info me-4"></span>
                                                <span class="status"><?php echo $LANG['UI_VM_REPORT_NOIN_BACKUP'] ?></span>
                                            </div>
                                            <div class="status-box__bootom">
                                                <span class="dot"></span>
                                                <span id="unprotected_public_cloud" class="status-box__bootom__num"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END PUBLIC CLOUD CARD -->

                            <!-- BEGIN PRIVATE CLOUD CARD -->
                            <div class="virtual-overview-card vm-pubpri-overview-card <?php
                            if (!in_array('global_observer', $_SESSION['permission']) && !in_array('prcloud_protect', $_SESSION['permission'])) {
                                echo ' display-none';
                            }
                            ?>">
                                <div class="virtual-overview-card__header">
                                    <div class="virtual-overview-card__header__box virtual-wrap">
                                        <div class="virtual-wrap__img">
                                            <img src="./img/platform/report/cloudAgent.svg" />
                                        </div>
                                        <div class="virtual-wrap__info">
                                            <span id="private_cloud" class="virtual-wrap__info__num"></span>
                                            <span class="virtual-wrap__info__des"><?php echo $LANG['UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="virtual-overview-card__content">
                                    <div id="private_echarts" class="vcenter-chart"></div>
                                    <div class="virtual-chart-info flex-direction-row">
                                        <div class="status-box mb-4 me-20">
                                            <div class="status-box__top">
                                                <span class="dot dot--success me-4"></span>
                                                <span class="status"><?php echo $LANG['UI_REPORT_PROTECTING'] ?></span>
                                            </div>
                                            <div class="status-box__bootom">
                                                <span class="dot"></span>
                                                <span id="protected_private_cloud" class="status-box__bootom__num"></span>
                                            </div>
                                        </div>
                                        <div class="status-box">
                                            <div class="status-box__top">
                                                <span class="dot dot--info me-4"></span>
                                                <span class="status"><?php echo $LANG['UI_VM_REPORT_NOIN_BACKUP'] ?></span>
                                            </div>
                                            <div class="status-box__bootom">
                                                <span class="dot"></span>
                                                <span id="unprotected_private_cloud" class="status-box__bootom__num"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END PRIVATE CLOUD CARD -->
                        </div>
                    </div>
                    <!-- END VM OVERVIEW CARD -->
                </div>
                
                <div class="d-flex mb-20">
                    <!-- BEIGIN NAS OVERVIEW CARD -->
                    <div class="equipment-card nas-card me-20 <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('nas_protect', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="equipment-card__header"><?php echo $LANG['UI_PLATFORM_NAS_DEVICE'] ?></div>
                        <div class="equipment-card__content svg-wrapper">
                            <div class="svg-wrapper__img"><img src="./img/platform/report/nas.svg" /></div>
                            <div class="svg-wrapper__info">
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['UI_PUBLIC_EQUIPMENT_NUMS'] ?></span>
                                    <span id="nas_device" class="svg-wrapper__info__item__num"></span>
                                </div>
                                <div class="separator-vertical"></div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                    <span id="nas_online" class="svg-wrapper__info__item__num text--online"></span>
                                </div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                    <span id="nas_offline" class="svg-wrapper__info__item__num text--offline"></span>
                                </div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="equipment-card__footer data-wrapper">
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                                    <span id="nas_protect" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PUBLIC_JOB'] ?></span>
                                    <span id="nas_task" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET'] ?></span>
                                    <span id="nas_backup" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END NAS OVERVIEW CARD -->

                    <!-- BEIGIN VOLCDP OVERVIEW CARD -->
                    <div class="equipment-card volcdp-card me-20 <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('vol_cdp_protect', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="equipment-card__header"><?php echo $LANG['UI_REPORT_CDP_PROTECT'] ?></div>
                        <div class="equipment-card__content svg-wrapper">
                            <div class="svg-wrapper__img"><img src="./img/platform/report/cdp.svg" /></div>
                                <div class="svg-wrapper__info">
                                    <div class="svg-wrapper__info__item wp-100">
                                        <span class="svg-wrapper__info__item__title"><?php echo $LANG['UI_PUBLIC_EQUIPMENT_NUMS'] ?></span>
                                        <span id="cdp_device" class="svg-wrapper__info__item__num"></span>
                                    </div>
                                </div>
                            </div>
                        <div class="separator"></div>
                        <div class="equipment-card__footer data-wrapper">
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PUBLIC_JOB'] ?></span>
                                    <span id="cdp_task" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_VOL_CDP_BAK_DATA'] ?></span>
                                    <span id="cdp_backup_set" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PLATFORM_BACKUPDATA'] ?></span>
                                    <span id="cdp_backup" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END VOLCDP OVERVIEW CARD -->

                    <!-- BEIGIN EXCHANGE OVERVIEW CARD -->
                    <div class="equipment-card exchange-card <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('exchange_organization', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="equipment-card__header"><?php echo $LANG['WEB_PLATFORM_DES_M365'] ?></div>
                        <div class="equipment-card__content svg-wrapper">
                            <div class="svg-wrapper__img"><img src="./img/platform/report/module-exchange.svg" /></div>
                            <div class="svg-wrapper__info">
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['UI_REPORT_ORGANIZATION'] ?></span>
                                    <span id="app_device" class="svg-wrapper__info__item__num"></span>
                                </div>
                                <div class="separator-vertical"></div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                    <span id="app_online" class="svg-wrapper__info__item__num text--online"></span>
                                </div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                    <span id="app_offline" class="svg-wrapper__info__item__num text--offline"></span>
                                </div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="equipment-card__footer data-wrapper">
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                                    <span id="app_protected" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PUBLIC_JOB'] ?></span>
                                    <span id="app_task" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_COPY_SOURCE_BACKUP_DATA'] ?></span>
                                    <span id="app_backup" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END EXCHANGE OVERVIEW CARD -->
                </div>

                <div class="d-flex mb-20">
                    <!-- BEGIN OBS OVERVIEW CARD -->
                    <div class="equipment-card obs-card me-20 <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('obs_protect', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="equipment-card__header"><?php echo $LANG['UI_PLATFORM_OBS'] ?></div>
                        <div class="equipment-card__content obs-wrapper">
                            <div id="obs_echart" class="obs-wrapper__chart me-36"></div>
                            <div class="virtual-chart-info">
                                <div class="status-box mb-4">
                                    <div class="status-box__top">
                                        <span class="dot dot--success me-4"></span>
                                        <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                    </div>
                                    <div class="status-box__bootom">
                                        <span class="dot"></span>
                                        <span id="obs_online" class="status-box__bootom__num"></span>
                                    </div>
                                </div>
                                <div class="status-box">
                                    <div class="status-box__top">
                                        <span class="dot dot--info me-4"></span>
                                        <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                    </div>
                                    <div class="status-box__bootom">
                                        <span class="dot"></span>
                                        <span id="obs_offline" class="status-box__bootom__num"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END OBS OVERVIEW CARD -->

                    <!-- BEGIN HADOOP OVERVIEW CARD -->
                    <div class="equipment-card hadoop-card me-20 <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('hadoop_protect', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="equipment-card__header"><?php echo $LANG['UI_HOMEPAGE_PROTECTED_HADOOP_TOTAL_NUM'] ?></div>
                        <div class="equipment-card__content svg-wrapper">
                            <div class="svg-wrapper__img"><img src="./img/platform/report/hadoop.svg" /></div>
                            <div class="svg-wrapper__info">
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['UI_CLUSTER_NUMBERS'] ?></span>
                                    <span id="hadoop_device" class="svg-wrapper__info__item__num"></span>
                                </div>
                                <div class="separator-vertical"></div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                    <span id="hadoop_online" class="svg-wrapper__info__item__num text--online"></span>
                                </div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                    <span id="hadoop_offline" class="svg-wrapper__info__item__num text--offline"></span>
                                </div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="equipment-card__footer data-wrapper">
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                                    <span id="hadoop_auth" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PUBLIC_JOB'] ?></span>
                                    <span id="hadoop_task" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_COPY_SOURCE_BACKUP_DATA'] ?></span>
                                    <span id="hadoop_backup" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END HADOOP OVERVIEW CARD -->

                    <!-- BEGIN FILE COPY OVERVIEW CARD -->
                    <div class="equipment-card filecopy-card <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('file_copy_protect', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="equipment-card__header"><?php echo $LANG['UI_FILE_COPY'] ?></div>
                        <div class="equipment-card__content echarts-wrapper">
                            <div class="echarts-wrapper__item me-20">
                                <div id="copy_obj_chart" class="echarts-wrapper__item__chart me-10"></div>
                                <div class="virtual-chart-info">
                                    <div class="status-box mb-4">
                                        <div class="status-box__top">
                                            <span class="dot dot--primary me-4"></span>
                                            <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                        </div>
                                        <div class="status-box__bootom">
                                            <span class="dot"></span>
                                            <span id="filecopy_source_client_online" class="status-box__bootom__num"></span>
                                        </div>
                                    </div>
                                    <div class="status-box">
                                        <div class="status-box__top">
                                            <span class="dot dot--info me-4"></span>
                                            <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                        </div>
                                        <div class="status-box__bootom">
                                            <span class="dot"></span>
                                            <span id="filecopy_source_client_offline" class="status-box__bootom__num"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="echarts-wrapper__item">
                                <div id="copy_target_chart" class="echarts-wrapper__item__chart me-10"></div>
                                <div class="virtual-chart-info">
                                    <div class="status-box mb-4">
                                        <div class="status-box__top">
                                            <span class="dot dot--success me-4"></span>
                                            <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                        </div>
                                        <div class="status-box__bootom">
                                            <span class="dot"></span>
                                            <span id="filecopy_target_client_online" class="status-box__bootom__num"></span>
                                        </div>
                                    </div>
                                    <div class="status-box">
                                        <div class="status-box__top">
                                            <span class="dot dot--info me-4"></span>
                                            <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                        </div>
                                        <div class="status-box__bootom">
                                            <span class="dot"></span>
                                            <span id="filecopy_target_client_offline" class="status-box__bootom__num"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="equipment-card__footer data-wrapper">
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                                    <span id="filecopy_protected" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PUBLIC_JOB'] ?></span>
                                    <span id="filecopy_tasks" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_FILE_COPY_DATA'] ?></span>
                                    <span id="filecopy_copy_data" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END FILE COPY OVERVIEW CARD -->
                </div>

                <div class="d-flex">
                    <!-- BEIGIN K8S OVERVIEW CARD -->
                    <div class="equipment-card k8s-card me-20 <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('k8s_protect', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="equipment-card__header">kubernets</div>
                        <div class="equipment-card__content svg-wrapper">
                            <div class="svg-wrapper__img"><img src="./img/platform/report/kubernets.svg" /></div>
                            <div class="svg-wrapper__info">
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['UI_CLUSTER_NUMBERS'] ?></span>
                                    <span id="kubernets_total" class="svg-wrapper__info__item__num"></span>
                                </div>
                                <div class="separator-vertical"></div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                    <span id="kubernets_online" class="svg-wrapper__info__item__num text--online"></span>
                                </div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                    <span id="kubernets_offline" class="svg-wrapper__info__item__num text--offline"></span>
                                </div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="equipment-card__footer data-wrapper">
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                                    <span id="kubernets_protected" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PUBLIC_JOB'] ?></span>
                                    <span id="kubernets_tasks" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_COPY_SOURCE_BACKUP_DATA'] ?></span>
                                    <span id="kubernets_backup_data" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END K8S OVERVIEW CARD -->

                    <!-- BEGIN DB PROTECT OVERVIEW CARD -->
                    <div class="equipment-card dbprotect-card me-20 <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('db_protect', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="equipment-card__header"><?php echo $LANG['UI_PLATFORM_DB_PROTECT'] ?></div>
                        <div class="equipment-card__content svg-wrapper">
                            <div class="svg-wrapper__img"><img src="./img/platform/report/module-db-protect.svg" /></div>
                            <div class="svg-wrapper__info">
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_PLATFORM_DES_INSTANCE'] ?></span>
                                    <span id="db_total" class="svg-wrapper__info__item__num"></span>
                                </div>
                                <div class="separator-vertical"></div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                    <span id="db_online" class="svg-wrapper__info__item__num text--online"></span>
                                </div>
                                <div class="svg-wrapper__info__item">
                                    <span class="svg-wrapper__info__item__title"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                    <span id="db_offline" class="svg-wrapper__info__item__num text--offline"></span>
                                </div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="equipment-card__footer data-wrapper">
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                                    <span id="db_protected" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PUBLIC_JOB'] ?></span>
                                    <span id="db_tasks" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_COPY_SOURCE_BACKUP_DATA'] ?></span>
                                    <span id="db_backup_data" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END DB PROTECT OVERVIEW CARD -->

                    <!-- BEGIN DB COPY OVERVIEW CARD -->
                    <div class="equipment-card dbcopy-card <?php if (!in_array('global_observer', $_SESSION['permission']) && !in_array('dbcdpcopy', $_SESSION['permission'])) {
                        echo ' display-none';
                    } ?>">
                        <div class="equipment-card__header"><?php echo $LANG['UI_DB_CDP_COPY'] ?></div>
                        <div class="equipment-card__content echarts-wrapper">
                            <div class="echarts-wrapper__item me-20">
                                <div id="dbcopy_host_chart" class="echarts-wrapper__item__chart me-10"></div>
                                <div class="virtual-chart-info">
                                    <div class="status-box mb-4">
                                        <div class="status-box__top">
                                            <span class="dot dot--primary me-4"></span>
                                            <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                        </div>
                                        <div class="status-box__bootom">
                                            <span class="dot"></span>
                                            <span id="dbcopy_host_online" class="status-box__bootom__num"></span>
                                        </div>
                                    </div>
                                    <div class="status-box">
                                        <div class="status-box__top">
                                            <span class="dot dot--info me-4"></span>
                                            <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                        </div>
                                        <div class="status-box__bootom">
                                            <span class="dot"></span>
                                            <span id="dbcopy_host_offline" class="status-box__bootom__num"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="echarts-wrapper__item">
                                <div id="dbcopy_backup_host_chart" class="echarts-wrapper__item__chart me-10"></div>
                                <div class="virtual-chart-info">
                                    <div class="status-box mb-4">
                                        <div class="status-box__top">
                                            <span class="dot dot--success me-4"></span>
                                            <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></span>
                                        </div>
                                        <div class="status-box__bootom">
                                            <span class="dot"></span>
                                            <span id="dbcopy_backup_host_online" class="status-box__bootom__num"></span>
                                        </div>
                                    </div>
                                    <div class="status-box">
                                        <div class="status-box__top">
                                            <span class="dot dot--info me-4"></span>
                                            <span class="status"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></span>
                                        </div>
                                        <div class="status-box__bootom">
                                            <span class="dot"></span>
                                            <span id="dbcopy_backup_host_offline" class="status-box__bootom__num"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="equipment-card__footer data-wrapper">
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_HOMEPAGE_PROTECTED'] ?></span>
                                    <span id="dbcopy_protected" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_PUBLIC_JOB'] ?></span>
                                    <span id="dbcopy_tasks" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                            <div class="data-wrapper__item">
                                <div class="data-wrapper__item__right">
                                    <span class="data-wrapper__item__right__title"><?php echo $LANG['UI_FILE_COPY_DATA'] ?></span>
                                    <span id="dbcopy_copy_data" class="data-wrapper__item__right__num"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END DB COPY OVERVIEW CARD -->
                </div>
            </div>
        </div>
        <!-- END PRODUCT RESOURCE SECTION -->
        
        <!-- BEGIN BACKUP RESOURCE SECTION -->
        <div class="report-section backup-resource-section mb-20">
            <div class="report-section__header">
                <span class="decoration"></span>
                <span class="report-section__header__title"><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
            </div>
            <div class="report-section__content">
                <div class="d-flex">
                    <!-- BEGEIN NODE OVERVIEW CARD -->
                    <div class="report-card node-card me-20">
                        <div class="report-card__header main-node">
                            <span class="main-node__label"><?php echo $LANG['UI_STORAGE_REMOTE_NODE_MASTER'] ?>: </span>
                            <span id="mainNode" class="main-node__value"></span>
                        </div>
                        <div class="node-card__content">
                            <div class="node-card__content__img">
                                <img src="./img/platform/report/backup.svg" />
                            </div>
                            <div class="node-card__content__info">
                                <div class="node-data-wrap">
                                    <div class="node-data-wrap__item">
                                        <span class="node-data-wrap__item__title">
                                            <span class="dot dot--info me-8"></span>
                                            <span class="node-data-wrap__item__title__text"><?php echo $LANG['UI_REPORT_CDP_RUN_DAYS'] ?></span>
                                        </span>
                                        <span class="node-data-wrap__item__data">
                                            <span class="dot me-4"></span>
                                            <span id="run_time" class="node-data-wrap__item__data__text"></span>
                                        </span>
                                    </div>
                                    <div class="node-data-wrap__item">
                                        <span class="node-data-wrap__item__title">
                                            <span class="dot dot--info me-8"></span>
                                            <span class="node-data-wrap__item__title__text"><?php echo $LANG['UI_REPORT_CDP_PROTECT_DATA'] ?></span>
                                        </span>
                                        <span class="node-data-wrap__item__data">
                                            <span class="dot me-4"></span>
                                            <span id="node_protected" class="node-data-wrap__item__data__text"></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="node-status-wrap">
                                    <div class="node-status-wrap__item">
                                        <img src="./img/platform/report/Connection-point.svg" />
                                    </div>
                                    <div class="node-status-wrap__item">
                                        <span class="node-status-wrap__item__title"><?php echo $LANG['UI_PALTFORM_CHILD_NODE'] ?></span>
                                        <span id="child_node" class="node-status-wrap__item__num"></span>
                                    </div>
                                    <div class="separator-vertical"></div>
                                    <div class="node-status-wrap__item">
                                        <span class="node-status-wrap__item__title"><?php echo $LANG['UI_REPORT_NODE_NORMAL'] ?></span>
                                        <span id="normal_node" class="node-status-wrap__item__num text--online"></span>
                                    </div>
                                    <div class="node-status-wrap__item">
                                        <span class="node-status-wrap__item__title"><?php echo $LANG['WEB_PLATFORM_DES_ABNORMAL'] ?></span>
                                        <span id="abnormal_node" class="node-status-wrap__item__num text--abnormal"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END NODE OVERVIEW CARD -->

                    <!-- BEGIN STORAGE OVERVIEW CARD -->
                    <div class="report-card storage-card">
                        <div class="report-card__header"><?php echo $LANG['UI_PALTFORM_STORAGE'] ?></div>
                        <div class="storage-card__content">
                            <div id="storage_echarts" class="storage-echarts"></div>
                            <div class="storage-wrapper">
                                <div class="storage-wrapper__boxes">
                                    <div class="storage-wrapper__boxes__item storage-box">
                                        <div class="storage-box__img"><img src="./img/platform/report/copy.svg" /></div>
                                        <div class="storage-box__info">
                                            <span id="copy_storage" class="storage-box__info__data"></span>
                                            <span class="storage-box__info__title"><?php echo $LANG['UI_HOMEPAGE_COPY_STORAGE_NUM'] ?></span>
                                        </div>
                                    </div>
                                    <div class="storage-wrapper__boxes__item storage-box">
                                        <div class="storage-box__img"><img src="./img/platform/report/used.svg" /></div>
                                        <div class="storage-box__info">
                                            <span id="used_size" class="storage-box__info__data"></span>
                                            <span class="storage-box__info__title"><?php echo $LANG['UI_HOMEPAGE_COPY_USED_STORAGE'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="storage-wrapper__data">
                                    <div class="storage-wrapper__data__item storage-capacity-wrap">
                                        <span class="storage-capacity-wrap__title">
                                            <span class="dot dot--success me-16"></span>
                                            <span class="description"><?php echo $LANG['UI_HOMEPAGE_TOTAL_USED_STORAGE'] ?>（</span>
                                            <span id="used_size_precent" class="capacity"></span>
                                            <span class="capacity">）</span>
                                        </span>
                                        <span class="storage-capacity-wrap__num">
                                            <span class="dot me-16"></span>
                                            <span id="used_size_number" class="nums"></span>
                                        </span>
                                    </div>
                                    <div class="storage-wrapper__data__item storage-capacity-wrap">
                                        <span class="storage-capacity-wrap__title">
                                            <span class="dot dot--info me-16"></span>
                                            <span class="description"><?php echo $LANG['UI_HOMEPAGE_TOTAL_UNSED_STORAGE'] ?>（</span>
                                            <span id="unused_size_precent" class="capacity"></span>
                                            <span class="capacity">）</span>
                                        </span>
                                        <span class="storage-capacity-wrap__num">
                                            <span class="dot me-16"></span>
                                            <span id="unused_size_number" class="nums"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END STORAGE OVERVIEW CARD -->
                </div>
            </div>
        </div>
        <!-- END BACKUP RESOURCE SECTION -->

        <div class="d-flex">
            <!-- BEGIN RUNNING TENDENCY SECTION -->
            <div class="report-section running-section me-20">
                <div class="report-section__header">
                    <span class="decoration"></span>
                    <span class="report-section__header__title"><?php echo $LANG['UI_REPORT_TASK_DENCY'] ?></span>
                </div>
                <div class="report-section__content">
                    <div class="tendency-toolbar">
                        <span class="tendency-summarize">
                            <?php echo $LANG['BILLING_TOTAL'] ?>
                            <span id="task_days" class="days"></span><?php echo $LANG['UI_REPORT_TIAO'] ?>
                            <span id="task_backup_data" class="backup-data"></span><?php echo $LANG['UI_REPORT_TASK_COUNT'] ?>
                            <span id="task_nums" class="tasks"></span><?php echo $LANG['UI_REPORT_SYSTEM_TASK'] ?>
                        </span>
                        <button class="dropdown-toggle toolbar-timepicker" id="task_running_tendency_daterangepicker" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="time"><?php echo $LANG['UI_TOOLS_TABLE_START_END_TIME'] ?></span>
                            <i class="viconfont vicon-ge_calendar ms-8"></i>
                        </button>
                    </div>
                    <div class="tendency-content running-tendency-content">
                        <div id="task_running_tendency_echart" class="tendency-content__echart"></div>
                        <div class="tendency-content__footer mark-point">
                            <div class="mark-point__item">
                                <span class="dot dot--success me-12"></span>
                                <span class="mark-point__item__desc"><?php echo $LANG['UI_COPY_SOURCE_BACKUP_DATA'] ?></span>
                            </div>
                            <div class="mark-point__item">
                                <span class="dot dot--primary me-12"></span>
                                <span class="mark-point__item__desc"><?php echo $LANG['UI_STORAGE_DELETE_TASK_COUNT'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="running-tendency-nodata display-none">
                        <div class="running-tendency-nodata__svg">
                            <img src="./img/platform/report/nodata.svg" />
                        </div>
                        <div class="running-tendency-nodata__info"><?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?></div>
                    </div>
                </div>
            </div>
            <!-- END RUNNING TENDENCY SECTION -->

            <!-- BEGIN ALARM SECTION -->
            <div class="report-section alarm-section">
                <div class="report-section__header">
                    <span class="decoration"></span>
                    <span class="report-section__header__title"><?php echo $LANG['WEB_NODE_NAS_OP_WARNING'] ?></span>
                </div>
                <div class="report-section__content">
                    <div class="tendency-toolbar">
                        <span class="tendency-summarize">
                            <?php echo $LANG['BILLING_TOTAL'] ?>
                            <span id="alarm_days" class="days"></span><?php echo $LANG['UI_REPORT_TIAO'] ?>
                            <span id="alarm_task_nums" class="backup-data"></span><?php echo $LANG['UI_REPORT_TASK_ALARM'] ?>
                            <span id="alarm_system_nums" class="tasks"></span><?php echo $LANG['UI_REPORT_SYSTEM_ALARM'] ?>
                        </span>
                        <button class="dropdown-toggle toolbar-timepicker" id="system_alarm_daterangepicker" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="time"><?php echo $LANG['UI_TOOLS_TABLE_START_END_TIME'] ?></span>
                            <i class="viconfont vicon-ge_calendar ms-8"></i>
                        </button>
                    </div>
                    <div class="tendency-content alarm-content">
                        <div id="alarm_echart" class="tendency-content__echart"></div>
                        <div class="tendency-content__footer mark-point">
                            <div class="mark-point__item">
                                <span class="dot dot--primary me-12"></span>
                                <span class="mark-point__item__desc"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?></span>
                            </div>
                            <div class="mark-point__item">
                                <span class="dot dot--warning me-12"></span>
                                <span class="mark-point__item__desc"><?php echo $LANG['UI_PLATFORM_ALARM_TASK'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="alarm-nodata display-none">
                        <div class="alarm-nodata__svg">
                            <img src="./img/platform/report/nodata.svg" />
                        </div>
                        <div class="alarm-nodata__info"><?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?></div>
                    </div>
                </div>
            </div>
            <!-- END ALARM SECTION -->
        </div>
    </div>
</div>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/echarts/V5.4.3/echarts.min.js"></script>
<script type="text/javascript" src="./scripts/platform/reports/overview.js"></script>
