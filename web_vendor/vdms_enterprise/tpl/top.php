<!-- BEGIN BODY -->
<!-- DOC: Apply "page-header-fixed-mobile" and "page-footer-fixed-mobile" class to body element to force fixed header or footer in mobile devices -->
<!-- DOC: Apply "page-sidebar-closed" class to the body and "page-sidebar-menu-closed" class to the sidebar menu element to hide the sidebar by default -->
<!-- DOC: Apply "page-sidebar-hide" class to the body to make the sidebar completely hidden on toggle -->
<!-- DOC: Apply "page-sidebar-closed-hide-logo" class to the body element to make the logo hidden on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-hide" class to body element to completely hide the sidebar on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-fixed" class to have fixed sidebar -->
<!-- DOC: Apply "page-footer-fixed" class to the body element to have fixed footer -->
<!-- DOC: Apply "page-sidebar-reversed" class to put the sidebar on the right side -->
<!-- DOC: Apply "page-full-width" class to the body element to have full width page without the sidebar menu -->
<?php
$sidebarClass = "";
if("1" == $_COOKIE['sidebar_closed']){
    //如果默认是收起的,添加body收起类
    $sidebarClass = "page-sidebar-closed";
    $sideMenuClass = " page-sidebar-menu-closed";
}
?>

<?php
//权限管理,根据用户的权限,显示信息
$permission = $_SESSION['permission'] ?? [];
$currentJobShow = in_array('current_job', $permission) ? "" : "none";
$historyJobShow = in_array('history_job', $permission) ? "" : "none";
$verifyJobShow = in_array('verification_job', $permission) ? "" : "none";
$historyverifyJobShow = in_array('verify_history_job', $permission) ? "" : "none";
$taskAlarmShow = in_array('task_alarm', $permission) ? "" : "none";
$systemAlarmShow = in_array('system_alarm', $permission) ? "" : "none";

//如果都没有
$surveyataskShow = "none";
if(in_array('current_job', $permission) || in_array('history_job', $permission)){
    $surveyataskShow = "";
}

$surveyalarmShow = "none";
if(in_array('task_alarm', $permission) || in_array('system_alarm', $permission)){
    $surveyalarmShow = "";
}
?>

<?php

function initPages(){
    //获取所有pages
    $pages = include './api/xphp/conf/page.php';

    $arr =$_SESSION['permission'] ?? [];


    $pages = getTenantPageTop($_SESSION['tenantuuid'], $pages);

    /**
     * 顶部结构
     * 因为和page页面数组的数据结构不一致,所以这儿单独处理这几个菜单按钮,后续增加新的模块需要在这儿的数组中增加
     */
    //监控中心---start
    $getFirstMenu = '';
    $monitoringCenterArray = array('monitor');
    foreach ($monitoringCenterArray as $each){
        if(in_array($each, $arr)){
            foreach ($pages as $page){
                if($page['name'] == $each){
                    //生成第一个按钮
                    $getFirstMenu = getFirstMenu($page, $arr);
                }
            }
        }
    }
    //监控中心---end
    //数据保护---start
    $getSecondMenu = '';
    $dataProtectionArray = array('vmprotect','awsprotect','host_protect','nas_protect','application_protect','k8s_protect','vol_cdp_protect','dbprotect','data_manager');
    foreach ($dataProtectionArray as $each){
        if(in_array($each, $arr)){
            //生成第二个按钮
            $getSecondMenu = getSecondMenu($dataProtectionArray, $arr, $pages);
            break;
        }
    }
    //数据保护---end
    //资源管理---start
    $getThirdMenu = '';
    $resourceManageArray = array('tenant','resmanagement');
    foreach ($resourceManageArray as $each){
        if(in_array($each, $arr)){
            //生成第三个按钮
            $getThirdMenu = getThirdMenu($resourceManageArray, $arr, $pages);
            break;
        }
    }
    //资源管理---end
    //系统管理---start
    $getFourthMenu = '';
    $systemManageArray = array('sysmanagement');
    foreach ($systemManageArray as $each){
        if(in_array($each, $arr)){
            foreach ($pages as $page){
                if($page['name'] == $each){
                    //生成第一个按钮
                    $getFourthMenu = getFourthMenu($page, $arr);
                }
            }
        }
    }
    //系统管理---end
    return $getFirstMenu.$getSecondMenu.$getThirdMenu.$getFourthMenu;
}

$allMenus = initPages();


function getTenantPageTop($tenantUUID, $pages){
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
        if($value['name'] == "sysmanagement"){
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







//组合第一个菜单-监控中心
function getFirstMenu($page, $arr){
    $ulHtml = '<li class="menu-dropdown classic-menu-dropdown">';
    $ulHtml .= '<a data-hover="megamenu-dropdown" data-close-others="true" data-toggle="dropdown" href="javascript:;" class="hover-initialized" aria-expanded="false">
                        '.$LANG['UI_PLATFORM_MONITOR_CENTER'].' <i class="fa fa-angle-down"></i>
                    </a>';
    $ulHtml .= '<ul class="dropdown-menu pull-left">';
    $ulHtml .= getEachMenu($page, $arr);
    $ulHtml .= '</ul>';
    $ulHtml .= '</li>';
    return $ulHtml;
}

//组合第二个菜单-数据保护
function getSecondMenu($ArrayExist, $arr, $pages){
    $ulHtml = '<li class="menu-dropdown classic-menu-dropdown">';
    $ulHtml .= '<a data-hover="megamenu-dropdown" data-close-others="true" data-toggle="dropdown" href="javascript:;" class="hover-initialized" aria-expanded="false">
                            '.$LANG['UI_HOMEPAGE_DATA_PROTECT'].' <i class="fa fa-angle-down"></i>
                        </a>';
    $ulHtml .= '<ul class="dropdown-menu pull-left">';
    foreach ($ArrayExist as $each){
        if(in_array($each, $arr)){
            foreach ($pages as $page){
                if($page['name'] == $each){
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
function getThirdMenu($ArrayExist, $arr, $pages){
    $ulHtml = '<li class="menu-dropdown classic-menu-dropdown">';
    $ulHtml .= '<a data-hover="megamenu-dropdown" data-close-others="true" data-toggle="dropdown" href="javascript:;" class="hover-initialized" aria-expanded="false">
                                '.$LANG['UI_PLATFORM_RESOURCE_MANAGER'].' <i class="fa fa-angle-down"></i>
                            </a>';
    $ulHtml .= '<ul class="dropdown-menu pull-left">';
    foreach ($ArrayExist as $each){
        if(in_array($each, $arr)){
            foreach ($pages as $page){
                if($page['name'] == $each){
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
function getFourthMenu($page, $arr){
    $ulHtml = '<li class="menu-dropdown classic-menu-dropdown">';
    $ulHtml .= '<a data-hover="megamenu-dropdown" data-close-others="true" data-toggle="dropdown" href="javascript:;" class="hover-initialized" aria-expanded="false">
                            '.$LANG['UI_PLATFORM_SYSTEM_MANAGER'].' <i class="fa fa-angle-down"></i>
                        </a>';
    $ulHtml .= '<ul class="dropdown-menu pull-left">';
    $ulHtml .= getEachMenu($page, $arr);
    $ulHtml .= '</ul>';
    $ulHtml .= '</li>';
    return $ulHtml;
}



//组装每一个大的模块,通用几个按钮
function getEachBigModuleMenu($page, $arr){
    //判断权限
    if(!in_array($page['name'], $arr)){
        return;
    }
    global $LANG; //使用全局变量
    $liHtml = '';

    //第一层
    if(isset($page['child']) && $page['level'] == 0){
        $liHtml .= '<li class="dropdown-submenu">';
        $title = $page['title'];
        $title = $LANG[$title];
        $liHtml .= '<a class="" name="'.$page['name'].'" href="'.$page['path'].'">
                        <i class="levelchild '.$page['class'].'"></i>
                        '.$title.'</a>';
        $liHtml .= '<ul class="dropdown-menu pull-left">';
        foreach ($page['child'] as $eachChild){
            $liHtml .= getEachBigModuleMenu($eachChild,$arr);
        }
        $liHtml .= '</ul>';
        $liHtml .= '</li>';
        return $liHtml;
    }

    //第二层
    if($page['level'] == 1 && !isset($page['showChild'])){
        $liHtml .= '<li class="">';
        $title = $page['title'];
        $title = $LANG[$title];
        $liHtml .= '<a class="ajaxify" name="'.$page['name'].'" href="'.$page['path'].'">
                        <i class="levelchild '.$page['class'].'"></i>
                        '.$title.'</a>';
        $liHtml .= '</li>';
        return $liHtml;
    }
    //第二层
    if(isset($page['child']) && $page['level'] == 1 && isset($page['showChild']) && $page['showChild']){
        $liHtml .= '<li class="dropdown-submenu">';
        $title = $page['title'];
        $title = $LANG[$title];
        $liHtml .= '<a class="" name="'.$page['name'].'" href="'.$page['path'].'">
                        <i class="levelchild '.$page['class'].'"></i>
                        '.$title.'</a>';
        $liHtml .= '<ul class="dropdown-menu pull-left">';
        foreach ($page['child'] as $eachChild){
            $liHtml .= getEachBigModuleMenu($eachChild,$arr);
        }
        $liHtml .= '</ul>';
        $liHtml .= '</li>';
        return $liHtml;
    }
    //第三层
    if($page['level'] == 2 && !isset($page['showChild'])){
        $liHtml .= '<li class="">';
        $title = $page['title'];
        $title = $LANG[$title];
        $liHtml .= '<a class="ajaxify" name="'.$page['name'].'" href="'.$page['path'].'">
                        <i class="levelchild '.$page['class'].'"></i>
                        '.$title.'</a>';
        $liHtml .= '</li>';
        return $liHtml;
    }
}


//获取整个目录结构,递归函数,单个结构
function getEachMenu($page, $arr){
    global $LANG; //使用全局变量
    $liHtml = '';
    //获取监控中心下第一个层级
    if(!empty($page['child'])){
        $childList = $page['child'];
        foreach ($childList as $each){
            if(in_array($each['name'], $arr) && $each['level'] == 1){
                $title = $each['title'];
                $title = $LANG[$title];
                $liHtml .= '<li class="">';
                $liHtml .= '<a class="ajaxify" name="'.$each['name'].'" href="'.$each['path'].'">
                                    <i class="levelchild '.$each['class'].'"></i>
                                    '.$title.'</a>';
                $liHtml .= ' </li>';
                $liHtml .= getEachMenu($childList,$arr);
            }
        }
    }
    return $liHtml;
}


?>









<body class="page-header-fixed overflowy-hide   page-quick-sidebar-over-content <?php echo $sidebarClass; ?>">
<!-- BEGIN HEADER -->
<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner">
        <!-- BEGIN LOGO -->
        <div class="page-logo" style="width: auto;">
            <a href="<?php echo $CONF['REMOTE']['website']?>" target="_black">
                <?php
                if($CONF['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType){
                    //如果是免费版,使用专用logo
                    echo '<img src="./img/platform/logo-free.png" alt="" class="logo-default logosizefree">';
                }else{
                    //如果是其他版本
                    $theme_skin = $_SESSION['theme_skin'];

                    if(file_exists($CONF['SPECIAL_DIR'] . "logo.png")){
                        $logo = "./special/logo.png";
                    }else{
                        $logo = "./img/platform/logo.png";
                        if(!empty($theme_skin)){
                            switch($theme_skin){
                                case 'blueSkin':
                                    $logo = "./img/themeSkin/logo-white.png";
                                    break;
                                case 'blueProSkin':
                                case 'whiteBlueSkin':
                                    $logo = "./img/themeSkin/logo-blue.png";
                                    break;
                                default:
                                    $logo = "./img/platform/logo.png";
                                    break;
                            }
                        }
                    }

                    echo '<img src="./img/platform/logo-divs.png" alt="" class="logo-default logosize">';

                }
                ?>
            </a>
            <div class="custom-sys-name-div"><div class="custom-sys-name"></div></div>
            <div class="menu-toggler sidebar-toggler hide">
                <!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
            </div>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
        </a>
        <!--组合顶部导航栏-----开始-->
        <div class="hor-menu hor-menu-light display-none">
            <ul class="nav navbar-nav">
                <li class="active">
                    <a href="index.html"><?php echo $LANG['UI_HOMEPAGE_PAGE']?></a>
                </li>
                <?php echo $allMenus;?>
            </ul>
        </div>
        <!--组合顶部导航栏-----结尾-->





        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <!-- 		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> -->
        <!-- 		</a> -->
        <!-- END RESPONSIVE MENU TOGGLER -->
        <!-- BEGIN TOP NAVIGATION MENU -->
        <div class="top-menu">
            <ul class="nav navbar-nav pull-right">
                <li class="dropdown dropdown-extended dropdown-notification dropdown-time">
                    <span class="spantitle"><?php echo $LANG['UI_HOMEPAGE_SYSTEM_TIME'] ?></span>
                    <span class="systemTimeTop" id="systemTimeTop" ></span>
        
                </li>
                <!-- BEGIN NOTIFICATION DROPDOWN -->
                <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                <li class="dropdown dropdown-extended dropdown-notification" id="surveyatask"  style="display: <?php echo $surveyataskShow?>">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" style="height: 46px">
                        <i class="viconfont vicon-task" style="font-size: 20px"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <ul class="dropdown-menu-list" data-handle-color="#637283">
                                <li style="display: <?php echo $currentJobShow?>">
                                    <a href="javascript:;" class="taskhrefcurrent">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="levelchild viconfont vicon-pt_job_current_task"></i>
									</span>
									<span class="bold" id="topcurrenttask"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_TASK_CURRENT']?> </span>
                                    </a>
                                </li>
                                <li style="display: <?php echo $historyJobShow?>">
                                    <a href="javascript:;" class="taskhrefhistory">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="levelchild viconfont vicon-pt_job_historical_task"></i>
									</span>
									<span class="bold" id="tophistorytask"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_TASK_HISTORY']?> </span>
                                    </a>
                                </li>
                                <li style="display: <?php echo $verifyJobShow?>">
                                    <a href="javascript:;" class="verifytaskhrefcurrent">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="levelchild viconfont vicon-pt_job_current_task"></i>
									</span>
									<span class="bold" id="topverifycurtask"> </span> 个验证任务</span>
                                    </a>
                                </li>
                                <li style="display: <?php echo $historyverifyJobShow?>">
                                    <a href="javascript:;" class="verifytaskhrefhistory">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="levelchild viconfont vicon-pt_job_historical_task"></i>
									</span>
									<span class="bold" id="topverifyhistask"> </span> 个历史验证任务</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="dropdown dropdown-extended dropdown-notification" id="surveyalarm" style="display: <?php echo $surveyalarmShow?>">
                    <a href="#" class="dropdown-toggle"  data-toggle="dropdown" data-hover="dropdown" data-close-others="true" id="alarmtotal">
                        <i class="icon-bell"></i>
                        <span class="badge badge-danger display-hide" id="alarmtotal">
				    </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <ul class="dropdown-menu-list " data-handle-color="#637283">
                                <li style="display: <?php echo $taskAlarmShow?>">
                                    <a href="javascript:;" class="alarmhreftask">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="levelchild viconfont vicon-pt_alarm_task_alarms  "></i>
									</span>
									<span class="bold" id="alarmtask"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_ERROR']?> </span>
                                    </a>
                                </li>
                                <li style="display: <?php echo $systemAlarmShow?>">
                                    <a href="javascript:;" class="alarmhrefsystem">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="levelchild viconfont vicon-pt_alarm_system_alarm "></i>
									</span>
									<span class="bold" id="alarmsystem"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_WARNNING']?> </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- <?php

                if(empty($_SESSION['tenantuuid'])){
                    $authfun = $_SESSION['authfun'];
                    $hide = "";
                    //检查当前用户是否属于Master组
                    $masterFlag = (new \app\v1\user\v0\logic\User())->pCheckUserIsMaster($_SESSION['userUUID']);
                    if((!$authfun['visualization'] || !$masterFlag) && !in_array('p_visual_screen', $_SESSION['permissionArr'] ?? [])){
                        $hide =  "display: none";
                    }
                    echo '<li class="dropdown dropdown-extended dropdown-notification" id="visualscreen" style="'.$hide.'">
					       <a title="'.$LANG['UI_VISUAL_SCREEN'].'" href="/visualscreen" class= "dropdown-toggle visualization" target="_blank"  data-hover="dropdown"  ><i class="icon-screen-desktop"></i></a></li>';
                }
                ?> -->
                <!-- END NOTIFICATION DROPDOWN -->
                <!-- BEGIN USER LOGIN DROPDOWN -->
                <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                <li class="dropdown dropdown-user">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <i class="icon-user"></i>
                        <span class="username username-hide-on-mobile" id="username">
					<?php echo $_SESSION['tenantusername']?>
					</span>
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-default">
                        <li>
                            <a href="./content/platform/users/userinfo.php" name="userinfo" class="ajaxify">
                                <i class="icon-user"></i> <?php echo $LANG['UI_USER_SELF_INFO']?> </a>
                        </li>
                        <?php
                        if($_SESSION['userType'] !== 2){
                            /*echo '<li>
                            <a href="./content/platform/users/edit_password.php" name="edit_password" class="ajaxify">
                                <i class="levelchild viconfont vicon-ge_modify"></i> '.$LANG['UI_USER_MODIFY_PASS'].' </a>
                        </li>';*/
                        }
                        ?>
                        <li>
                            <a href="javascript:void(0)" onclick="loginOut()">
                                <i class="viconfont vicon-ge_lock"></i> <?php echo $LANG['UI_USER_LOCK_SCREEN']?> </a>
                        </li>


                        <li class="divider">
                        </li>

                        <?php
                        //如果是云祺的版本，显示帮助按钮，对应帮助文档
                        // 						if(in_array($CONF['SYSTEM_INFO']['enterprise'], $CONF['ENTERPRISE']) && $CONF['SYSTEM_INFO']['enterprise'] != 'vinchin_standard'){
                        // 						    echo   '<li>
                        //             						    <a href="./help/help_doc.pdf" target="_blank">
                        //             						    <i class="icon-doc"></i> ' . $LANG['UI_USER_HELP'] . '</a>
                        //             						</li>';
                        // 						}
                        if(in_array($CONF['SYSTEM_INFO']['enterprise'], $CONF['ENTERPRISE']) && $CONF['SYSTEM_INFO']['enterprise'] != 'vinchin_standard'){
                            echo   '<li>
            							<a href="./content/platform/users/aboutus.php" name="aboutus" class="ajaxify">
            							<i class="icon-flag"></i> '. $LANG['UI_USER_ABOUT_US'].' </a>
                                    </li>';
                        }

                        ?>
                        <!-- <li>
						    <a href="<?php echo $CONF['REMOTE']['website']?>" target="_blank">
						    <i class="icon-flag"></i> <?php echo $LANG['UI_USER_ABOUT_US']?></a>
						</li> -->
                        <!--
						<li>
							<a href="./content/platform/users/feedback.php" name="feedback" class="ajaxify">
							<i class="icon-emoticon-smile"></i> <?php echo $LANG['UI_FEEDBACK_TITLE']?> </a>
						</li>
						 -->
                        <!--<li>
							    <a href="<?php
                        echo $CONF['REMOTE']['website'];
                        ?>" target="_blank">
							    <i class="icon-doc"></i> <?php echo $LANG['UI_USER_HELP']?></a>
							</li> -->

                        <li class="divider">
                        </li>

                        <li>
                            <a href="javascript:void(0)" onclick="loginOut(1)">
                                <i class="icon-key"></i> <?php echo $LANG['UI_USER_LOGIN_OUT']?> </a>
                        </li>

                    </ul>
                </li>
                <!-- END USER LOGIN DROPDOWN -->
            </ul>
        </div>
        <!-- END TOP NAVIGATION MENU -->
    </div>
    <!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<div class="clearfix">
</div>
