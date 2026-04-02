<?php
//获取请求URL地址,后面会根据URL地址判断左侧菜单
$RequestUrl = $_SERVER['REQUEST_URI'];
?>

<!-- BEGIN CONTAINER -->
<div class="page-container">
    <!-- BEGIN SIDEBAR -->
    <div class="page-sidebar navbar-collapse collapse scrollbar">
        <!-- BEGIN SIDEBAR MENU -->
        
        <ul class="page-sidebar-menu <?php echo $sidebarClass . $sideMenuClass; ?>" data-keep-expanded="false" data-auto-scroll="false" data-slide-speed="200">
            <?php
            //组合导航树
            $pages = include './api/xphp/conf/page.php';

            //登录用户权限
            $arr = $_SESSION['permission'] ?? [];

            $pages = getTenantPage($_SESSION['tenantuuid'], $pages);

            $nav = '';

            /**
             * 左侧只展示两层结构
             */
            foreach ($pages as $page) {
                if (in_array($page['name'], $arr)) {
                    //如果第一层具有权限,生成此第一层菜单
                    $nav .= groupLevelOneNav($page, $arr);
                }

            }

            echo $nav;

            /**
             * 得到租户的导航页
             * @param unknown $tenantUUID
             * @param unknown $pages
             * @return unknown
             */
            function getTenantPage($tenantUUID, $pages)
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
                    if ($value['name'] == "tenant") {
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

            /**
             * 获取首页路径
             * 在\api\app\platform\PlatformHandler.class.php里面有同名函数,要一并修改
             */
            function homePagePath()
            {
                global $CONF;
                if ($CONF['SYSTEM_INFO']['enterprise'] == "inspur_enterprise") {
                    //默认新版路径(浪潮sidebar 和web_ui中的就只有这里不一样，因为浪潮首页只有一个，固定死databackup_center_vinchin.php这个页面)
                    $path_str = "./content/platform/databackup_center_vinchin.php";
                    return $path_str;
                }
                //先获取版本是哪种
                $product_type = $_SESSION['product_type'];
                //默认新版路径
                $path_str = "./content/platform/databackup_center.php";
                switch ($product_type) {
                    //专业版
                    case 'professional':
                        //如果是专业版: 老版路径
                        $path_str = './content/platform/databackup_center_vinchin.php';
                        //如果不是三权分立,则不处理
                        if (!$_SESSION['isThreePowers']) {
                            //以下不是三权时
                            if (in_array('global_observer', $_SESSION['permission'])) {
                                // 如果有全局观察者权限，那么也默认和admin一样的
                                break;
                            }
                            switch ($_SESSION['userLevel']) {
                                //admin
                                case 1:
                                    break;
                                default:
                                    $path_str = './content/platform/databackup_center.php';
                                    break;
                            }
                            break;
                        }
                        //以下是三权时
                        switch ($_SESSION['userLevel']) {
                            //老版首页
                            case 2:
                                $path_str = './content/platform/databackup_center_vinchin.php';
                                break;
                            //新版首页(个人首页)
                            case 5:
                                $path_str = './content/platform/databackup_center.php';
                                break;
                            //没有首页
                            case 3:
                            case 4:
                                $path_str = 'javascript:;';
                                break;
                        }
                        break;
                    //基础版: 显示新版首页
                    case 'basic':
                        break;
                    //白牌版: 显示新版首页
                    case 'special':
                        break;
                    //项目版: 显示新版首页
                    case 'project':
                        $path_str = './content/platform/databackup_center_pro.php';
                        //以下是三权时
                        switch ($_SESSION['userLevel']) {
                            case 2:
                            case 5:
                                $path_str = './content/platform/databackup_center_pro.php';
                                break;
                            //没有首页
                            case 3:
                            case 4:
                                $path_str = 'javascript:;';
                                break;
                        }
                        break;
                    //标准版
                    case 'standard':
                        $path_str = './content/platform/databackup_center_standard.php';
                        break;
                }
                return $path_str;
            }

            /**
             * 判断显示 active
             */
            function checkShowActive()
            {
                // 这里判断下默认选中那个菜单
                $checks_name = false;
                if ($_SESSION['isThreePowers'] && in_array($_SESSION['userLevel'], [3, 4])) {
                    // 根据授权模式 如果三权模式并且是安全员和审计员的情况，是没有首页的
                    $checks_name = true;
                    if ($_SESSION['userLevel'] == 3) {
                        $temp_check_name = 'sysmanagement';
                        $temp_check_name2 = 'safety';
                    } else {
                        $temp_check_name = 'monitor';
                        $temp_check_name2 = 'log';
                    }
                }
                return [
                    $checks_name,
                    $temp_check_name ?? '',
                    $temp_check_name2 ?? ''
                ];
            }

            /**
             * 组合第一级导航
             * @param unknown $pageInfo
             * @param unknown $ini_arr
             * @param unknown $arr
             * @param unknown $k
             * @return string
             */
            function groupLevelOneNav($page, $arr)
            {

                //需要这个用户具有权限才显示对应菜单
                // 三权的安全员和审计员不显示首页
                if (!in_array($page['name'], $arr) || (checkShowActive()[0] && $page['name'] == 'homepage')) {
                    //没有权限
                    return "";
                }
                global $LANG;

                //租户内暂时不支持副本容灾和数据归档
                /*if(!empty($_SESSION['tenantuuid']) && ($page['name'] == "datacopy" || $page['name'] == "data_archive")){
                    return "";
                }*/


                //全局观察者不显示页面
                if ($page['name'] == "global_observer" || $page['name'] == "global_read" || $page['name'] == "global_write")
                    return "";

                if ($page['name'] == checkShowActive()[1] && checkShowActive()[0]) {
                    $class = "class= 'level0 active open'";
                } else {
                    $class = "class= 'level0'";
                }

                $liHeader = "<li {$class}>
                    <a href=\"" . $page['path'] . "\" name=\"" . $page['name'] . "\" class=\"" . 'menu-level1' . "\">
                        <i class=\"icontop1 me-8 " . $page['class'] . "\"></i>
                    <span class=\"title\">" . $LANG[$page['title']] . "</span>";

                if ($page['name'] == 'homepage' && !checkShowActive()[0]) {
                    $liHeader = "<li class=\"level0 active open\">
                    <a class=\"ajaxify menu-level1\" name=\"" . $page['name'] . "\" href=\"" . homePagePath() . "\">
                        <i class=\"icontop1 me-8 " . $page['class'] . "\"></i>
                    <span class=\"title\">" . $LANG[$page['title']] . "</span>";
                    $liHeader .= "<span class=\"selected\"></span>";
                }
                if (!empty($page['child'])) {
                    // 是否显示下级
                    $isShowNext = true;

                    // 临时存放一下
                    $liHeaderTmp = "<span class=\"selected\"></span>";
                    $liHeaderTmp .= "<span class=\"arrow \"></span></a>";
                    $liHeaderTmp .= "<ul class=\"sub-menu sub-menu-level1\">";

                    foreach ($page['child'] as $child) {
                        if ($child['level'] == 10) {
                            // 不是左侧菜单
                            $isShowNext = false;
                            break;
                        }
                        if (in_array($child['name'], $arr)) {
                            $liHeaderTmp .= groupLevelTwoNav($child, $arr);
                        }
                    }

                    if ($isShowNext) {
                        $liHeader .= $liHeaderTmp;
                        $liHeader .= "</ul></li>";
                    } else {
                        $liHeader .= "</a></li>";
                    }
                } else {
                    $liHeader .= "</a></li>";
                }

                return $liHeader;
            }

            /**
             * 组合第二级导航
             * @param array $child
             * @return string
             */
            function groupLevelTwoNav($child, $arr)
            {
                global $LANG;
                if (empty($_SESSION['tenantuuid']) && ($child['name'] == "remote_system" || $child['name'] == "cloud_storage"))
                    return "";
                // if(!empty($_SESSION['tenantuuid']) && $child['name'] == "vm_overview") return "";
                //租户内不支持计费管理
                if (!empty($_SESSION['tenantuuid']) && $child['name'] == "billing_manager") {
                    return "";
                }
                $ajaxify = "ajaxify";
                if ($child['showChild'] && !empty($child['child'])) {
                    //如果二级还有下一层菜单,二级暂时不设置链接
                    $ajaxify = "";
                }

                if ($child['name'] == checkShowActive()[2] && checkShowActive()[0]) {
                    $li = "<li id=sb_" . $child['name'] . "  class=\"level1 active\">";
                } else {
                    $li = "<li id=sb_" . $child['name'] . "  class=\"level1 \">";
                }
                // 这里需要判断四组整机 -磁盘和卷的情况，如果只授权了一个，那就显示整机
                // 第一组 备份下的 complete_machine osbackup
                // 第二组 连续数据保护下的 complete_cdp_backup vol_cdp_backup
                // 第三组 复制下的 machine_copy vol_cdp_copy
                // 第四组 备份数据管理 下的 vol_cdp_complete_takeover vol_cdp_takeover 显示为接管
                $page1 = ['complete_machine', 'osbackup'];
                $page2 = ['complete_cdp_backup', 'vol_cdp_backup'];
                $page3 = ['machine_copy', 'vol_cdp_copy'];
                $page4 = ['vol_cdp_complete_takeover', 'vol_cdp_takeover'];
                if (
                    (in_array($child['name'], $page1) && count(array_intersect($_SESSION['permission'], $page1)) == 1)
                    || (in_array($child['name'], $page2) && count(array_intersect($_SESSION['permission'], $page2)) == 1)
                    || (in_array($child['name'], $page3) && count(array_intersect($_SESSION['permission'], $page3)) == 1)
                ) {
                    $child['title'] = 'UI_COMPLETE_MACHINE';
                } else if ((in_array($child['name'], $page4) && count(array_intersect($_SESSION['permission'], $page4)) == 1)) {
                    $child['title'] = 'UI_PLATFORM_VOL_CDP_TAKEOVER';
                }

                if ($child['path'] == 'javascript:;') {
                    $li .= "<a  class=\"$ajaxify menu-level2\" name=\"" . $child['name'] . "\" href=\"" . $child['path'] . "\">
                            <i class=\"levelchild " . $child['class'] . "\"></i>
                            <span class='title ms-4'>" . $LANG[$child['title']] . '</span>';
                } else {
                    $li .= "<a  class=\"$ajaxify menu-level2\" name=\"" . $child['name'] . "\" href=\"" . $child['path'] . "\">
                            <i class=\"levelchild menu-level1-i me-8 " . $child['class'] . "\"></i><span class='title-text menu-level1-text'> 
                            " . $LANG[$child['title']] . '</span>';
                }

                if ($child['showChild'] && !empty($child['child'])) {

                    $li .= "<span class=\"selected\"></span>";
                    $li .= "<span class=\"arrow \"></span></a>";
                    $li .= "<ul class=\"sub-menu sub-menu-level2\">";
                    foreach ($child['child'] as $child) {
                        if (in_array($child['name'], $arr)) {
                            $li .= groupLevelThreeNav($child);
                        }
                    }
                    $li .= "</ul></li>";
                } else {
                    $li .= "</a></li>";
                }


                return $li;
            }

            /**
             * 组合第三季导航
             * @param array $child
             */
            function groupLevelThreeNav($child)
            {
                global $LANG;
                $li = "<li id=sb_" . $child['name'] . "  class=\"level2\">";

                $li .= "<a  class=\"ajaxify menu-level3\" name=\"" . $child['name'] . "\" href=\"" . $child['path'] . "\">
                            <i class=\"levelchild menu-level2-i me-8 " . $child['class'] . "\"></i><span class='title-text menu-level2-text'>
                            " . $LANG[$child['title']] . "</span></a></li>";

                return $li;

            }

            ?>
        </ul>
        <div class="sidebar-toggle-wrapper">
            <div class="sidebar-toggle" title="<?php echo $LANG['UI_FOLD'] ?>" id="sidebar_toggle">
                <i class="viconfont vicon-menu-fold"></i>
                <i class="viconfont vicon-menu-unfold display-none"></i>
            </div>
        </div>
        <!-- END SIDEBAR MENU -->
    </div>

    <!-- END SIDEBAR -->
    <script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <!-- BEGIN CONTENT -->
    <div class="page-content-wrapper overflowAuto" style="height:100%;">
        <div class="page-content">
            <!-- END STYLE CUSTOMIZER -->
            <div class="page-content-body" style="height:100%;">
                <!-- HERE WILL BE LOADED AN AJAX CONTENT -->
                <?php
                //为了处理文件客户端任务列表详情跳转，需要三个参数：task_uuid,task_type,agent_uuid
                $uuid = $_GET['uuid'];
                $type = $_GET['type'];
                $agentuuid = $_GET['agentuuid'];
                $tenantuuid = (new \app\v1\user\v0\logic\User())->getUserPermission();
                $temp_url = '';
                $tempNavigation = '';
                if ($agentuuid && $uuid && $type) {
                    include './content/fs/fs_job_details.php';
                } elseif ($_SESSION['isThreePowers'] && in_array($_SESSION['userLevel'], [3, 4])) {
                    // 根据授权模式 如果三权模式并且是安全员和审计员的情况，是没有首页的
                    $temp_url = './content/platform/' . ($_SESSION['userLevel'] == 3 ? 'users/users.php' : 'logs/logs.php');
                    // include './content/platform/' . $temp_url;
                } else {
                    //TODO,根据系统管理员,租户管理员,一般用户加载不同页面 tenant_center.php
                    if ($_GET['force_route_jump'] == 1 && isset($_GET['module']) && isset($_GET['name'])) {
                        $checks_name = true;
                        $temp_check_name = base64_decode($_GET['module']);
                        $temp_check_name2 = base64_decode($_GET['name']);
                        $subCheckName = '';
                        if ($_GET['sub_name']) {
                            $subCheckName = base64_decode($_GET['sub_name']);
                        }
                        foreach ($pages as $page) {
                            if ($temp_url) {
                                break;
                            }
                            if ($page['name'] == $temp_check_name) {
                                foreach ($page['child'] as $childPage) {
                                    if ($temp_url) {
                                        break;
                                    }
                                    if ($childPage['name'] == $temp_check_name2) {
                                        if (!$subCheckName) {
                                            $temp_url = $childPage['path'];
                                            $tempNavigation = $temp_check_name2;
                                            break;
                                        }
                                        foreach ($childPage['child'] as $subChildPage) {
                                            if ($temp_url) {
                                                break;
                                            }
                                            if ($subChildPage['name'] == $subCheckName) {
                                                $tempNavigation = $temp_check_name2;
                                                $temp_url = $subChildPage['path'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if (!$temp_url) {  // 未查到对应的路径
                            include homePagePath();
                        }
                        if (isset($_GET['tab'])) {
                            $temp_url .= "?tab=" . $_GET['tab'];
                        }
                    } elseif ($tenantuuid == "") {
                        include homePagePath();
                    } else {
                        include './content/platform/tenant_center.php';
                    }
                }

                ?>
            </div>
        </div>
        <!-- BEGIN CONTENT -->
    </div>
    <!-- END CONTENT -->
    <?php
    if ($temp_url) {
        // 加载下首页的js
        echo '<script type="text/javascript" src="./scripts/platform/databackup_center.js"></script>';
    }
    ?>
    <input id="temp_url" type="hidden" data-navigation="<?php echo $tempNavigation ?>" value="<?php echo $temp_url ?>">
    <script>
    $(function() {
        let temp_url = $('#temp_url').val();
        let tempNavigation = $('#temp_url').data('navigation');
        if (temp_url) {
            waitSystemInit().then(() => {
                LOCATION(temp_url, tempNavigation);
            });
        }
    })
    </script>