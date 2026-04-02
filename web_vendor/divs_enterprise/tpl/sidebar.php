<!-- BEGIN CONTAINER -->
<div class="page-container" style="height:95%;">
    <!-- BEGIN SIDEBAR -->
    <div class="page-sidebar-wrapper">
        <div class="page-sidebar navbar-collapse collapse scrollbar">
            <ul class="page-sidebar-menu <?php echo $sidebarClass . $sideMenuClass; ?>" data-keep-expanded="false" data-auto-scroll="false" data-slide-speed="200">
                <li class="sidebar-toggler-wrapper">
                    <div class="sidebar-toggler marginb8" style="margin-right: 0;" title="<?php echo $LANG['UI_PUBLIC_TOOLS_C_OR_E']?>">
                    </div>
                </li>
                <?php
                //组合导航树
                if (!empty($_SESSION['ui_page_temp'])) {
                    $pages = $_SESSION['ui_page_temp'];
                } else {
                    $pages = include './api/xphp/conf/page.php';
                    $_SESSION['ui_page_temp'] = $pages;
                }

                //登录用户权限
                $arr = $_SESSION['permission'] ?? [];

                // 我的待办和报告管理是基础应用，需要这里手动ping上
                $arr = array_merge(
                    $arr,
                    ['homepage', 'p_homepage','system', 'p_system_list', 'p_system_rule', 'todo_list', 'p_todo_list', 'industry_report', 'p_industry_report', 'p_industry_report_list', 'p_industry_report_operation']
                );

                $pages = getTenantPage($_SESSION['tenantuuid'], $pages);

                $nav = '';
                /**
                 * 左侧只展示两层结构
                 */
                foreach ($pages as $page){
                    if(in_array($page['name'], $arr)){
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
                function getTenantPage($tenantUUID, $pages){
                    if(empty($tenantUUID)){
                        return $pages;
                    }
                    //如果是租户
                    foreach ($pages as $key => $value){
                        if($value['name'] == "vmprotect"){
                            //虚拟机保护,修改备份页面
                            $child = $value['child'];
                            foreach ($child as $ckey => $cvalue){
                                if($cvalue['name'] == "vmbackup"){
                                    $pages[$key]['child'][$ckey]['path'] = "./content/vm/vmbackup.php";
                                }
                            }
                        }
                        if($value['name'] == "tenant"){
                            //系统管理,修改租户页面
                            $child = $value['child'];
                            foreach ($child as $ckey => $cvalue){
                                if($cvalue['name'] == "tenant_manager"){
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
                function homePagePath(){
                    if($CONF['SYSTEM_INFO']['enterprise'] == "inspur_enterprise"){
                        //默认新版路径(浪潮sidebar 和web_ui中的就只有这里不一样，因为浪潮首页只有一个，固定死databackup_center_vinchin.php这个页面)
                        $path_str = "./content/platform/databackup_center_vinchin.php";
                        return $path_str;
                    }
                    //先获取版本是哪种
                    $product_type = $_SESSION['product_type'];
                    //GMP默认只跳这个页面
                    $path_str = "./content/platform/databackup_center_vinchin.php";
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
                    if ($_GET['force_route_jump'] == 1 && isset($_GET['module']) && isset($_GET['name'])) {
                        // 通过url设置菜单
                        $checks_name = true;
                        $temp_check_name = base64_decode($_GET['module']);
                        $temp_check_name2 = base64_decode($_GET['name']);
                        $jumpFlag = false;
                        global $pages;
                        foreach ($pages as $page) {
                            if ($jumpFlag) {
                                break;
                            }
                            if ($page['name'] == $temp_check_name) {
                                foreach ($page['child'] as $childPage) {
                                    if ($jumpFlag) {
                                        break;
                                    }
                                    if ($childPage['name'] == $temp_check_name2) {
                                        $jumpFlag = true;
                                    }
                                }
                            }
                        }
                        if (!$jumpFlag) {
                            $checks_name = false;
                            $temp_check_name = '';
                            $temp_check_name2 = '';
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
                function groupLevelOneNav($page, $arr){

                    //需要这个用户具有权限才显示对应菜单
                    // 三权的安全员和审计员不显示首页
                    if(!in_array($page['name'], $arr) || (checkShowActive()[0] && $page['name'] == 'homepage')){
                        //没有权限
                        return "";
                    }
                    global $LANG;

                    //全局观察者不显示页面
                    if($page['name'] == "global_observer" || $page['name'] == "global_read" || $page['name'] == "global_write" ) return "";

                    if ($page['name'] == checkShowActive()[1] && checkShowActive()[0]) {
                        $class="class= 'active open'";
                    } else {
                        $class = '';
                    }

                    // if child is one,do not show and give class ajaxify
                    $ajaxify = '';
                    if (count($page['child']) <= 1 || empty($page['showChild'])) {
                        $ajaxify = 'class="ajaxify"';
                    }

                    $liHeader = "<li {$class}>
    					<a {$ajaxify} href=\"" . $page['path'] . "\" name=\"" . $page['name'] . "\">
    					<i class=\"icontop1 fs20 " . $page['class'] . "\"></i>
    					<span class=\"title\">" . $LANG[$page['title']] . "</span>";

                    if($page['name'] == 'homepage' && !checkShowActive()[0]){
                        $liHeader = "<li class=\"active open\">
    					<a class=\"ajaxify\" name=\"" . $page['name'] . "\" href=\"" . homePagePath() . "\">
    					<i class=\"icontop1 fs20 " . $page['class'] . "\"></i>
    					<span class=\"title\">" . $LANG[$page['title']] . "</span>";
                        $liHeader .= "<span class=\"selected\"></span>";
                    }
                    if($page['showChild'] && !empty($page['child']) && count($page['child']) > 1){
                        // 是否显示下级
                        $isShowNext = true;

                        // 临时存放一下
                        $liHeaderTmp = "<span class=\"selected\"></span>";
                        $liHeaderTmp .= "<span class=\"arrow \"></span></a>";
                        $liHeaderTmp .= "<ul class=\"sub-menu\">";

                        foreach ($page['child'] as $child){
                            if ($child['level'] == 10) {
                                // 不是左侧菜单
                                $isShowNext = false;
                                break;
                            }
                            if(in_array($child['name'], $arr)){
                                $liHeaderTmp .= groupLevelTwoNav($child, $arr);
                            }
                        }

                        if ($isShowNext) {
                            $liHeader .= $liHeaderTmp;
                            $liHeader .= "</ul></li>";
                        } else {
                            $liHeader .= "</a></li>";
                        }
                    }else{
                        $liHeader .= "</a></li>";
                    }

                    return $liHeader;
                }

                /**
                 * 组合第二级导航
                 * @param array $child
                 * @return string
                 */
                function groupLevelTwoNav($child, $arr){
                    global $LANG;
                    if(empty($_SESSION['tenantuuid']) && ($child['name'] == "remote_system" || $child['name'] == "cloud_storage")) return "";
                    // if(!empty($_SESSION['tenantuuid']) && $child['name'] == "vm_overview") return "";
                    //租户内不支持计费管理
                    if(!empty($_SESSION['tenantuuid']) && $child['name'] == "billing_manager"){
                        return "";
                    }
                    $ajaxify = "ajaxify";
                    if($child['showChild'] && !empty($child['child'])){
                        //如果二级还有下一层菜单,二级暂时不设置链接
                        $ajaxify = "";
                    }

                    if ($child['name'] == checkShowActive()[2] && checkShowActive()[0]) {
                        $li = "<li id=sb_".$child['name']."  class=\"level1 active\">";
                    } else {
                        $li = "<li id=sb_".$child['name']."  class=\"level1 \">";
                    }
                    $li .= "<a  class=\"$ajaxify\" name=\"" . $child['name'] . "\" href=\"" . $child['path'] . "\">
    							<i class=\"levelchild " . $child['class'] . "\"></i> 
    							" . $LANG[$child['title']];
                    if($child['showChild'] && !empty($child['child'])){

                        $li .= "<span class=\"selected\"></span>";
                        $li .= "<span class=\"arrow \"></span></a>";
                        $li .= "<ul class=\"sub-menu\">";
                        foreach ($child['child'] as $child){
                            if(in_array($child['name'], $arr)){
                                $li .= groupLevelThreeNav($child);
                            }
                        }
                        $li .= "</ul></li>";
                    }else{
                        $li .= "</a></li>";
                    }


                    return $li;
                }

                /**
                 * 组合第三季导航
                 * @param array $child
                 */
                function groupLevelThreeNav($child){
                    global $LANG;
                    $li = "<li id=sb_".$child['name']."  class=\"level2\">";
                    $li .= "<a  class=\"ajaxify\" name=\"" . $child['name'] . "\" href=\"" . $child['path'] . "\">
    							<i class=\"levelchild " . $child['class'] . "\"></i>
    							" . $LANG[$child['title']] . "</a></li>";
                    return $li;
                }
                ?>
            </ul>
            <ul>

            </ul>
            <!-- END SIDEBAR MENU -->
        </div>

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
                if($agentuuid && $uuid && $type){
                    include './content/fs/fs_job_details.php';
                }elseif ($_SESSION['isThreePowers'] && in_array($_SESSION['userLevel'], [3, 4])) {
                    // 根据授权模式 如果三权模式并且是安全员和审计员的情况，是没有首页的
                    $temp_url = './content/platform/' . ($_SESSION['userLevel'] == 3 ? 'users/users.php' : 'logs/logs.php');
                    // include './content/platform/' . $temp_url;
                }else{
                    //TODO,根据系统管理员,租户管理员,一般用户加载不同页面 tenant_center.php
                    if ($_GET['force_route_jump'] == 1 && isset($_GET['module']) && isset($_GET['name'])) {
                        // url跳转
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
                                            break;
                                        }
                                        foreach ($childPage['child'] as $subChildPage) {
                                            if ($temp_url) {
                                                break;
                                            }
                                            if ($subChildPage['name'] == $subCheckName) {
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
                    } elseif($tenantuuid == "" ){
                        include homePagePath();
                    }else{
                        include './content/platform/tenant_center.php';
                    }
                }
                ?>
            </div>
        </div>
        <!-- BEGIN CONTENT -->
    </div>
    <div id="gmp_homepage_shadow_box" style="position:absolute;z-index:10052;background:rgba(255,255,255, 0.1);width:100%;height:100%;left:0;top:0;">

    </div>
    <!-- END CONTENT -->
    <?php
    if ($temp_url) {
        // 加载下首页的js
        echo '<script type="text/javascript" src="./scripts/platform/databackup_center.js"></script>';
    }
    ?>
    <input id="temp_url" type="hidden" value="<?php echo $temp_url?>" data-force-jump="<?php echo $_GET['force_route_jump'] ?>">
    <script>
        $(function (){
            let temp_url = $('#temp_url').val();
            if (temp_url) {
                if ($('#temp_url').data('force-jump') == 1) {  // 跳转到其他页面，隐藏授权的遮罩
                    $('#gmp_homepage_shadow_box').hide();
                }
                LOCATION(temp_url);
            }
        })

    </script>
