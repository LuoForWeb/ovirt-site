<?php
//获取请求URL地址,后面会根据URL地址判断左侧菜单
$RequestUrl = $_SERVER['REQUEST_URI'];
if ($RequestUrl == "/" || $RequestUrl == '/?homepage') {
	//如果是/,也默认成index.html
	$RequestUrl = "index.html";
} else {
	//这里要处理两种形式，1."/index.html", 2."/index.html?homepage"
	$strposInt = strpos($RequestUrl, "?");
	if ($strposInt > 0) {
		$RequestUrl = substr($RequestUrl, 1, $strposInt - 1);
	} else {
		$RequestUrl = substr($RequestUrl, 1);
	}
}
// var_dump($RequestUrl);
?>

<!-- BEGIN CONTAINER -->
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<div class="page-sidebar-wrapper <?php if ($RequestUrl == "index.html")
		echo "displaynone" ?>">
			<div class="page-sidebar navbar-collapse collapse scrollbar">
						<ul class="page-sidebar-menu <?php echo $sidebarClass . $sideMenuClass; ?>" data-keep-expanded="false" data-auto-scroll="false" data-slide-speed="200">
				<?php

				//组合导航树
				$pages = include './api/xphp/conf/page.php';

				//根据不同URL地址,即顶部不同的选择,展示不同的左侧菜单,首页不处理,已经做了隐藏显示
				if ($RequestUrl != "index.html") {
					$newPages = array();
					$showPageArr = array();
					if ($RequestUrl == "monitor.html" || $RequestUrl == "alarm.html" || $RequestUrl == "monihis.html" || $RequestUrl == "monitask.html" || $RequestUrl == "monisystem.html") {
						//监控中心
						$showPageArr = array("monitor");
					} elseif ($RequestUrl == "dataprotect.html") {
						//数据保护(虚拟机保护,主机保护,实时容灾保护,NAS保护,数据库实时备份,备份数据,数据归档,对象存储保护，hadoop保护)
						// $showPageArr = array("vmprotect", "awsprotect", "host_protect", "vol_cdp_protect", "nas_protect", "dbprotect", "application_protect", "data_verification", "data_archive_cd", "data_manager", "obs_protect", "hadoop_protect");
						//备份 实时 复制 数据管理
						$showPageArr = array("backup","vol_cdp_protect","copy","data_manager");
					} elseif ($RequestUrl == "resmanagement.html" || $RequestUrl == "resmanagementclient.html" || $RequestUrl == "resmanagementvcenter.html") {
						//资源管理
						$showPageArr = array("resmanagement");
					} elseif ($RequestUrl == "sysmanagement.html") {
						//系统管理(系统管理,租户管理)
						$showPageArr = array("sysmanagement");
					}

					//过滤掉不需要的,只要展示的
					foreach ($pages as $pageOne) {
						if (in_array($pageOne['name'], $showPageArr)) {
							$newPages[] = $pageOne;
						}
					}
					$pages = $newPages;
				}
				//登录用户权限
				$arr = $_SESSION['permission'];
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
						if ($value['name'] == "sysmanagement" || $value['name'] == "tenant") {
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
					if (!in_array($page['name'], $arr)) {
						//没有权限
						return "";
					}
					global $LANG;

					//租户内暂时不支持副本容灾和数据归档
					// if (!empty($_SESSION['tenantuuid']) && ($page['name'] == "datacopy" || $page['name'] == "data_archive")) {
					// 	return "";
					// }

					//全局观察者不显示页面
					if ($page['name'] == "global_observer" || $page['name'] == "global_read" || $page['name'] == "global_write")
						return "";

					$liHeader = "<li class= 'level0'>
    					<a href=\"" . $page['path'] . "\" name=\"" . $page['name'] . "\" class=\"" . 'menu-level1' . "\">
    					<i class=\"icontop1 me-8 " . $page['class'] . "\"></i>
    					<span class=\"title ms-4\">" . $LANG[$page['title']] . "</span>";

					if ($page['name'] == 'homepage') {
						$liHeader = "<li class=\"level0 active open\">
    					<a class=\"ajaxify menu-level1\" name=\"" . $page['name'] . "\" href=\"" . $page['path'] . "\">
    					<i class=\"icontop1 me-8 " . $page['class'] . "\"></i>
    					<span class=\"title ms-4\">" . $LANG[$page['title']] . "</span>";
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
					if (!empty($_SESSION['tenantuuid']) && $child['name'] == "vm_overview")
						return "";
					$ajaxify = "ajaxify";
					if ($child['showChild'] && !empty($child['child'])) {
						//如果二级还有下一层菜单,二级暂时不设置链接
						$ajaxify = "";
					}

					$li = "<li id=sb_" . $child['name'] . "  class=\"level1 \">";

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
						$li .= "<ul class=\"sub-menu  sub-menu-level2\">";
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
								<i class=\"levelchild menu-level2-i me-4 " . $child['class'] . "\"></i><span class='title-text menu-level2-text'>
								" . $LANG[$child['title']] . "</span></a></li>";

					return $li;

				}

				?>
			</ul>
			<ul>
			
			</ul>
			<!-- END SIDEBAR MENU -->
		</div>
		
	</div>
	
	
	<?php
	// 	return;
	?>
	<!-- END SIDEBAR -->
	<script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper overflowAuto sangfor-background" <?php if ($RequestUrl == "index.html")
		echo "style='margin-left: 0'" ?>>
					<div class="page-content sangfor-bgc">
									<!-- END STYLE CUSTOMIZER -->
									<div class="page-content-body" style="height:100%;">
										<!-- HERE WILL BE LOADED AN AJAX CONTENT -->
									<?php
	$tenantuuid = (new \app\v1\user\v0\logic\User())->getUserPermission();

	if ($RequestUrl == "index.html") {
		//首页加载
		//TODO,根据系统管理员,租户管理员,一般用户加载不同页面 tenant_center.php
		if ($tenantuuid == "") {
			include './content/platform/databackup_center.php';
		} else {
			include './content/platform/tenant_center.php';
		}
	} else {
		// //其他页面加载
		// if($RequestUrl == "monitor.html"){
		//     //监控中心-当前任务
		//     // include './content/platform/jobs/jobs.php';
		// }elseif ($RequestUrl == "dataprotect.html"){
		//     //数据保护-这里要处理不同授权情况,取page中第一个菜单的第一个选项,第二层和第三层有点差异
		//     $includeUrl = $pages[0]['child'][0]['path'];                    //第二层,比如虚拟机的概览
		//     if($includeUrl == "javascript:;"){
		//         $includeUrl = $pages[0]['child'][0]['child'][0]['path'];    //第三层,比如文件保护的备份
		//     }
		//     include $includeUrl;
		// }elseif ($RequestUrl == "resmanagement.html"){
		//     //资源管理-存储设备
		//     include './content/platform/storage/storage_manager.php';
		// }elseif ($RequestUrl == "sysmanagement.html"){
		//     //系统管理-系统配置
		//     include './content/platform/settings/setting_manager.php';
		// }elseif ($RequestUrl == "alarm.html"){
		//     //监控中心-告警
		// 	include './content/platform/alarm/alarm.php';
		// }elseif ($RequestUrl == "node_manager.html"){
		//     //资源管理-备份节点
		//     include './content/platform/node/node_manager.php';
		// }
	}


	?>
			</div>
		</div>
		<!-- BEGIN CONTENT -->
	</div>
	<!-- END CONTENT -->
	
	