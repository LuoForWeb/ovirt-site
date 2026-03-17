<!-- BEGIN BODY -->

<?php
// 权限管理,根据用户的权限,显示信息
$permission = $_SESSION['permission'] ?? [];
$currentJobShow = in_array('current_job', $permission) ? "" : "display-none";
$historyJobShow = in_array('history_job', $permission) ? "" : "display-none";
$taskAlarmShow = in_array('task_alarm', $permission) ? "" : "display-none";
$systemAlarmShow = in_array('system_alarm', $permission) ? "" : "display-none";

// 当前任务或历史任务未授权
$surveyataskShow = "display-none";
if (in_array('current_job', $permission) || in_array('history_job', $permission)) {
    $surveyataskShow = "";
}

// 任务告警或系统告警未授权
$surveyalarmShow = "display-none";
if (in_array('task_alarm', $permission) || in_array('system_alarm', $permission)) {
    $surveyalarmShow = "";
}
?>

<?php

function initPages()
{
    //获取所有pages
    $pages = include './api/xphp/conf/page.php';

    $arr = $_SESSION['permission'] ?? [];
    $pages = getTenantPageTop($_SESSION['tenantuuid'], $pages);

    /**
     * 顶部结构
     * 因为和page页面数组的数据结构不一致,所以这儿单独处理这几个菜单按钮,后续增加新的模块需要在这儿的数组中增加
     */
    //监控中心---start
    $getFirstMenu = '';
    $monitoringCenterArray = array('monitor');
    foreach ($monitoringCenterArray as $each) {
        if (in_array($each, $arr)) {
            foreach ($pages as $page) {
                if ($page['name'] == $each) {
                    //生成第一个按钮
                    $getFirstMenu = getFirstMenu($page, $arr);
                }
            }
        }
    }
    //监控中心---end
    //数据保护---start
    $getSecondMenu = '';
    $dataProtectionArray = array('vmprotect', 'awsprotect', 'host_protect', 'nas_protect', 'application_protect', 'k8s_protect', 'vol_cdp_protect', 'dbprotect', 'data_manager');
    foreach ($dataProtectionArray as $each) {
        if (in_array($each, $arr)) {
            //生成第二个按钮
            $getSecondMenu = getSecondMenu($dataProtectionArray, $arr, $pages);
            break;
        }
    }
    //数据保护---end
    //资源管理---start
    $getThirdMenu = '';
    $resourceManageArray = array('tenant', 'resmanagement');
    foreach ($resourceManageArray as $each) {
        if (in_array($each, $arr)) {
            //生成第三个按钮
            $getThirdMenu = getThirdMenu($resourceManageArray, $arr, $pages);
            break;
        }
    }
    //资源管理---end
    //系统管理---start
    $getFourthMenu = '';
    $systemManageArray = array('sysmanagement');
    foreach ($systemManageArray as $each) {
        if (in_array($each, $arr)) {
            foreach ($pages as $page) {
                if ($page['name'] == $each) {
                    //生成第一个按钮
                    $getFourthMenu = getFourthMenu($page, $arr);
                }
            }
        }
    }
    //系统管理---end
    return $getFirstMenu . $getSecondMenu . $getThirdMenu . $getFourthMenu;
}

$allMenus = initPages();

function getTenantPageTop($tenantUUID, $pages)
{
    if (empty($tenantUUID)) {
        return $pages;
    }
    //如果是租户
    foreach ($pages as $key => $value) {
        if ($value['name'] == "vmprotect") {
            //虚拟机保护,修改备份页面
            $child = $value['child'];
            foreach ($child as $ckey => $cvalue) {
                if ($cvalue['name'] == "vmbackup") {
                    $pages[$key]['child'][$ckey]['path'] = "./content/vm/vmbackup.php";
                }
            }
        }
        if ($value['name'] == "sysmanagement") {
            //系统管理,修改租户页面
            $child = $value['child'];
            foreach ($child as $ckey => $cvalue) {
                if ($cvalue['name'] == "tenant_manager") {
                    $pages[$key]['child'][$ckey]['path'] = "/content/platform/tenant/tenant.php?uuid=" . $tenantUUID;
                    $pages[$key]['child'][$ckey]['name'] = "p_tenant_view";
                }
            }
        }
    }

    return $pages;
}

//组合第一个菜单-监控中心
function getFirstMenu($page, $arr)
{
    $ulHtml = '<li class="menu-dropdown classic-menu-dropdown">';
    $ulHtml .= '<a data-hover="megamenu-dropdown" data-close-others="true" data-toggle="dropdown" href="javascript:;" class="hover-initialized" aria-expanded="false">
                        ' . $LANG['UI_PLATFORM_MONITOR_CENTER'] . ' <i class="fa fa-angle-down"></i>
                    </a>';
    $ulHtml .= '<ul class="dropdown-menu pull-left">';
    $ulHtml .= getEachMenu($page, $arr);
    $ulHtml .= '</ul>';
    $ulHtml .= '</li>';
    return $ulHtml;
}

//组合第二个菜单-数据保护
function getSecondMenu($ArrayExist, $arr, $pages)
{
    $ulHtml = '<li class="menu-dropdown classic-menu-dropdown">';
    $ulHtml .= '<a data-hover="megamenu-dropdown" data-close-others="true" data-toggle="dropdown" href="javascript:;" class="hover-initialized" aria-expanded="false">
                            ' . $LANG['UI_HOMEPAGE_DATA_PROTECT'] . ' <i class="fa fa-angle-down"></i>
                        </a>';
    $ulHtml .= '<ul class="dropdown-menu pull-left">';
    foreach ($ArrayExist as $each) {
        if (in_array($each, $arr)) {
            foreach ($pages as $page) {
                if ($page['name'] == $each) {
                    //生成第一个按钮
                    $ulHtml .= getEachBigModuleMenu($page, $arr);
                }
            }
        }

    }
    $ulHtml .= '</ul>';
    $ulHtml .= '</li>';
    return $ulHtml;
}

//组合第三个菜单-资源管理
function getThirdMenu($ArrayExist, $arr, $pages)
{
    $ulHtml = '<li class="menu-dropdown classic-menu-dropdown">';
    $ulHtml .= '<a data-hover="megamenu-dropdown" data-close-others="true" data-toggle="dropdown" href="javascript:;" class="hover-initialized" aria-expanded="false">
                                ' . $LANG['UI_PLATFORM_RESOURCE_MANAGER'] . ' <i class="fa fa-angle-down"></i>
                            </a>';
    $ulHtml .= '<ul class="dropdown-menu pull-left">';
    foreach ($ArrayExist as $each) {
        if (in_array($each, $arr)) {
            foreach ($pages as $page) {
                if ($page['name'] == $each) {
                    //生成第一个按钮
                    $ulHtml .= getEachBigModuleMenu($page, $arr);
                }
            }
        }

    }
    $ulHtml .= '</ul>';
    $ulHtml .= '</li>';
    return $ulHtml;
}

//组合第一个菜单-系统管理
function getFourthMenu($page, $arr)
{
    global $LANG; //使用全局变量
    $ulHtml = '<li class="menu-dropdown classic-menu-dropdown">';
    $ulHtml .= '<a data-hover="megamenu-dropdown" data-close-others="true" data-toggle="dropdown" href="javascript:;" class="hover-initialized" aria-expanded="false">
                            ' . $LANG['UI_PLATFORM_SYSTEM_MANAGER'] . ' <i class="fa fa-angle-down"></i>
                        </a>';
    $ulHtml .= '<ul class="dropdown-menu pull-left">';
    $ulHtml .= getEachMenu($page, $arr);
    $ulHtml .= '</ul>';
    $ulHtml .= '</li>';
    return $ulHtml;
}

//组装每一个大的模块,通用几个按钮
function getEachBigModuleMenu($page, $arr)
{
    //判断权限
    if (!in_array($page['name'], $arr)) {
        return;
    }
    global $LANG; //使用全局变量
    $liHtml = '';

    //第一层
    if (isset($page['child']) && $page['level'] == 0) {
        $liHtml .= '<li class="dropdown-submenu">';
        $title = $page['title'];
        $title = $LANG[$title];
        $liHtml .= '<a class="" name="' . $page['name'] . '" href="' . $page['path'] . '">
                        <i class="levelchild ' . $page['class'] . '"></i>
                        ' . $title . '</a>';
        $liHtml .= '<ul class="dropdown-menu pull-left">';
        foreach ($page['child'] as $eachChild) {
            $liHtml .= getEachBigModuleMenu($eachChild, $arr);
        }
        $liHtml .= '</ul>';
        $liHtml .= '</li>';
        return $liHtml;
    }

    //第二层
    if ($page['level'] == 1 && !isset($page['showChild'])) {
        $liHtml .= '<li class="">';
        $title = $page['title'];
        $title = $LANG[$title];
        $liHtml .= '<a class="ajaxify" name="' . $page['name'] . '" href="' . $page['path'] . '">
                        <i class="levelchild ' . $page['class'] . '"></i>
                        ' . $title . '</a>';
        $liHtml .= '</li>';
        return $liHtml;
    }
    //第二层
    if (isset($page['child']) && $page['level'] == 1 && isset($page['showChild']) && $page['showChild']) {
        $liHtml .= '<li class="dropdown-submenu">';
        $title = $page['title'];
        $title = $LANG[$title];
        $liHtml .= '<a class="" name="' . $page['name'] . '" href="' . $page['path'] . '">
                        <i class="levelchild ' . $page['class'] . '"></i>
                        ' . $title . '</a>';
        $liHtml .= '<ul class="dropdown-menu pull-left">';
        foreach ($page['child'] as $eachChild) {
            $liHtml .= getEachBigModuleMenu($eachChild, $arr);
        }
        $liHtml .= '</ul>';
        $liHtml .= '</li>';
        return $liHtml;
    }
    //第三层
    if ($page['level'] == 2 && !isset($page['showChild'])) {
        $liHtml .= '<li class="">';
        $title = $page['title'];
        $title = $LANG[$title];
        $liHtml .= '<a class="ajaxify" name="' . $page['name'] . '" href="' . $page['path'] . '">
                        <i class="levelchild ' . $page['class'] . '"></i>
                        ' . $title . '</a>';
        $liHtml .= '</li>';
        return $liHtml;
    }
}

//获取整个目录结构,递归函数,单个结构
function getEachMenu($page, $arr)
{
    global $LANG; //使用全局变量
    $liHtml = '';
    //获取监控中心下第一个层级
    if (!empty($page['child'])) {
        $childList = $page['child'];
        foreach ($childList as $each) {
            if (in_array($each['name'], $arr) && $each['level'] == 1) {
                $title = $each['title'];
                $title = $LANG[$title];
                $liHtml .= '<li class="">';
                $liHtml .= '<a class="ajaxify" name="' . $each['name'] . '" href="' . $each['path'] . '">
                                    <i class="levelchild ' . $each['class'] . '"></i>
                                    ' . $title . '</a>';
                $liHtml .= ' </li>';
                $liHtml .= getEachMenu($childList, $arr);
            }
        }
    }
    return $liHtml;
}

?>

<body class="page-header-fixed overflow-hidden page-quick-sidebar-over-content">
    <!-- BEGIN PAGE HEADER -->
    <div class="page-header">
        <nav class="navbar navbar-expand-sm navbar-fixed-top page-header-nav">
            <!-- BEGIN LOGO -->
            <div class="page-header-nav__logo">
                <a href="<?php echo $CONF['REMOTE']['website'] ?>" target="_blank">
                    <?php
                    if ($CONF['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType) { // 免费版
                        echo '<img src="./img/platform/logo.svg" alt="" class="logo-default logosizefree">';
                    } else {
                        $theme_skin = $_SESSION['theme_skin'];

                        if (file_exists($CONF['SPECIAL_DIR'] . "logo.png")) {
                            $logo = "./special/logo.png";
                        } else {
                            $logo = "./img/platform/logo.svg";

                            if (!empty($theme_skin)) {
                                switch ($theme_skin) {
                                    case 'blueSkin':
                                        $logo = "./img/themeSkin/logo-white.png";
                                        break;
                                    case 'blueProSkin':
                                    case 'whiteBlueSkin':
                                        $logo = "./img/themeSkin/logo-blue.png";
                                        break;
                                    case 'standardSkin':
                                        $logo = './img/themeSkin/logo-standard.png';
                                        break;
                                    default:
                                        $logo = "./img/platform/logo.svg";
                                        break;
                                }
                            }
                        }

                        echo '<img src="' . $logo . '" alt="" class="logo-default logosize">';
                    }
                    ?>
                </a>
                <div class="sidebar-toggle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="折叠" id="sidebar-toggle">
                    <i class="viconfont vicon-menu-fold"></i>
                    <i class="viconfont vicon-menu-unfold display-none"></i>
                </div>
            </div>
            <!-- END LOGO -->

            <!-- BEGIN CUSTOM PRODUCT NAME -->
            <span class="product-name text-overflow-ellipsis <?php echo $_SESSION['softwareNameDiy'] ? '' : 'display-none' ?>">
                <?php echo $_SESSION['softwareNameDiy'] ?>
            </span>
            <!-- END CUSTOM PRODUCT NAME -->
            
            <!-- BEGIN TOP NAVIGATION MENU -->
            <div class="page-header-nav__menu">
                <div class="page-header-nav__menu__system-time">
                    <span class="system-time__label">
                        系统时间
                    </span>
                    <span class="system-item__content">2026-01-27 10:30:00</span>
                </div>

                <div class="separator-vertical"></div>

                <!-- BEGIN TASK DROPDOWN -->
                <div class="page-header-nav__menu__dropdown">
                    <div class="dropdown-toggle task-img" data-bs-toggle="dropdown" data-bs-hover="dropdown">
                        <i class="viconfont vicon-a-View-listxiangqingliebiao1"></i>
                    </div>

                    <div class="dropdown-menu nav-dropdown-menu">
                        <a class="dropdown-item nav-dropdown-menu-href current-task-href">
                            <div class="nav-dropdown-menu-href__label">
                                <div class="nav-dropdown-menu-href__label__img">
                                    <i class="viconfont vicon-pt_job_current_task"></i>
                                </div>
                                <span class="nav-dropdown-menu-href__label__text">当前任务</span>
                            </div>
                            <span class="nav-dropdown-menu-href__value">7</span>
                        </a>
                        <a class="dropdown-item nav-dropdown-menu-href history-task-href">
                            <div class="nav-dropdown-menu-href__label">
                                <div class="nav-dropdown-menu-href__label__img">
                                    <i class="viconfont vicon-pt_job_historical_task"></i>
                                </div>
                                <span class="nav-dropdown-menu-href__label__text">历史任务</span>
                            </div>
                            <span class="nav-dropdown-menu-href__value system-warning-text">258</span>
                        </a>
                    </div>
                </div>
                <!-- END TASK DROPDOWN -->

                <!-- BEGIN ALARM DROPDOWN -->
                <div class="page-header-nav__menu__dropdown">
                    <div class="dropdown-toggle" data-bs-toggle="dropdown" data-bs-hover="dropdown">
                        <i class="viconfont vicon-a-Remindtixing"></i>
                    </div>
                    
                    <div class="dropdown-menu nav-dropdown-menu">
                        <a class="dropdown-item nav-dropdown-menu-href task-warning-href">
                            <div class="nav-dropdown-menu-href__label">
                                <div class="nav-dropdown-menu-href__label__img">
                                    <i class="viconfont vicon-pt_alarm_task_alarms"></i>
                                </div>
                                <span class="nav-dropdown-menu-href__label__text">任务告警</span>
                            </div>
                            <span class="nav-dropdown-menu-href__value task-warning-text">48</span>
                        </a>
                        <a class="dropdown-item nav-dropdown-menu-href system-warning-href">
                            <div class="nav-dropdown-menu-href__label">
                                <div class="nav-dropdown-menu-href__label__img system-warning-img">
                                    <i class="viconfont viconfont vicon-pt_alarm_system_alarm"></i>
                                </div>
                                <span class="nav-dropdown-menu-href__label__text">系统告警</span>
                            </div>
                            <span class="nav-dropdown-menu-href__value system-warning-text">78</span>
                        </a>
                    </div>
                </div>
                <!-- END ALARM DROPDOWN -->

                <!-- BEGIN FULL SCREEN -->
                <div class="page-header-nav__menu__dropdown">
                    <a href="/visualscreen" title="可视化大屏" class="dropdown-toggle" target="_blank">
                        <i class="viconfont vicon-a-Computerdiannao"></i>
                    </a>
                </div>
                <!-- END FULL SCREEN -->

                <div class="page-header-nav__menu__admin">
                    <div class="dropdown-toggle admin-dropdown" data-bs-toggle="dropdown" data-bs-hover="dropdown">
                        <span class="admin-dropdown__label">
                            <i class="viconfont viconfont vicon-a-Peoplerenyuan"></i>
                        </span>
                        <span class="admin-dropdown__content">
                            admin
                        </span>
                    </div>
                    <div class="dropdown-menu admin-menu">
                        <div class="dropdown-item admin-menu-wrapper">
                            <div class="admin-menu-wrapper__header">
                                <div class="admin-menu-wrapper__header__left admin-info">
                                    <div class="admin-info__left">
                                        <i class="viconfont vicon-user-3-fill"></i>
                                    </div>
                                    <div class="admin-info__right">
                                        <div class="admin-info__right__username">
                                            
                                        </div>
                                        <div class="admin-info__right__auth auth--normal">
                                            <span class="auth-text auth-text--normal"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="admin-menu-wrapper__header__right remain-days">
                                    <span class="remain-days__label">
                                        <?php echo $LANG['SYSTEM_AUTH_REMAIN_DAYS'] ?>
                                    </span>
                                    <span class="remain-days__value"></span>
                                </div>
                            </div>
                            <div class="admin-menu-wrapper__content">
                                <a class="admin-menu-wrapper__content__item item-info personal-information">
                                    <div class="item-info__img">
                                        <i class="viconfont vicon-a-Mewode"></i>
                                    </div>
                                    <div class="item-info__text">
                                        <span><?php echo $LANG['PUBLIC_MENU_PERSONAL_INFO'] ?></span>
                                    </div>
                                </a>
                                <a class="admin-menu-wrapper__content__item item-info modify-password">
                                    <div class="item-info__img">
                                        <i class="viconfont vicon-edit-two"></i>
                                    </div>
                                    <div class="item-info__text">
                                        <span><?php echo $LANG['PUBLIC_MENU_MODIFY_PASSWD'] ?></span>
                                    </div>
                                </a>
                                <a class="admin-menu-wrapper__content__item item-info"
                                    href="<?php echo $CONF['REMOTE']['aboutus']; ?>" target="_blank">
                                    <div class="item-info__img">
                                        <i class="viconfont vicon-flag"></i>
                                    </div>
                                    <div class="item-info__text">
                                        <span><?php echo $LANG['PUBLIC_MENU_ABOUT_US'] ?></span>
                                    </div>
                                </a>
                            </div>
                            <div class="admin-menu-wrapper__footer">
                                <div class="logout-wrapper">
                                    <i class="viconfont vicon-logout"></i>
                                    <a href="/account/login/html/loginout.php"><span
                                            class="logout-wrapper__text"><?php echo $LANG['PUBLIC_MENU_SAFE_EXIT'] ?></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TOP NAVIGATION MENU -->
        </nav>
    </div>
    <!-- END PAGE HEADER -->