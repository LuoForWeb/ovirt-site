<?php include_once '../../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="/system/settings/network/css/index.css" />

<div class="vinchin-wrapper breadcrumb-wrapper">
    <nav class="breadcrumb-wrapper__nav" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="ajaxify" name="setting_manager" href="settingManager.html" data-i18n="BTN_SAVE">
                    <span>系统配置</span>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">网络配置</li>
        </ol>
    </nav>
    <div class="breadcrumb-wrapper__title">
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-lg">
            <li class="nav-item">
                <a href="#ipdiv" data-bs-toggle="tab" class="nav-link active" id="ipdiv-block" data-i18n="BTN_SAVE">
                    <i class="levelchild viconfont vicon-pt_setting_ip_address"></i>IP地址
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#dnsdiv" id="dnsdiv-block" data-i18n="BTN_SAVE">
                    <i class="levelchild viconfont vicon-pt_setting_dns"></i>域名解析
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#nicteamingdiv" id="nicteamingdiv-block" data-i18n="BTN_SAVE">
                    <i class=" levelchild viconfont vicon-pt_setting_netcard"></i>网卡聚合
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#cardbridgediv" id="cardbridgediv-block" data-i18n="BTN_SAVE">
                    <i class=" levelchild viconfont vicon-card_bridge"></i>网卡桥接
                </a>
            </li>
        </ul>
    </div>
    <div class="breadcrumb-wrapper__content tab-content hover-scroll" id="myTabContent">
        <div class="tab-pane fade show active" id="ipdiv" role="tabpanel">
            <?php
            include_once './set-ip.php';
            ?>
        </div>
        <div class="tab-pane fade" id="dnsdiv" role="tabpanel">
            <?php
            include_once './system-dns.php';
            ?>
        </div>
        <div class="tab-pane fade" id="nicteamingdiv" role="tabpanel">
            <?php
            include_once './nic-teaming.php';
            ?>
        </div>
        <div class="tab-pane fade" id="cardbridgediv" role="tabpanel">
            <?php
            include_once './card-bridge.php';
            ?>
        </div>
    </div>
</div>



<!-- BEGIN PAGE LEVEL PLUGINS -->
<input type="hidden" id="tabHref" value="<?php echo $_GET['tab'];?>">
<script type="text/javascript" src="/system/settings/network/js/index.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
