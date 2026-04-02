<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $CONF, $LANG;
$vendor = $CONF['SYSTEM_INFO']['vendor'];
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" href="./css/client/application_config.css" type="text/css">

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <?php
    if ($vendor == $CONF['VENDOR_LIST']['gmp']) {  // GMP导航
        echo <<<EOF
            <li>
                <a class="ajaxify" name="clients" href="./content/client/client.php?tab=0&load_old_table_flag=1">
                    <span>{$LANG['UI_EQUIQMENT_MANAGER']}</span>
                </a>
            </li>
            <span>></span>
EOF;
    } else {
        echo <<<EOF
            <li>
                <a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
                    <span>{$LANG['UI_PLATFORM_INFRASTRUCTURE']}</span>
                </a>
            </li>
            <span>></span>
            <li>
                <a class="ajaxify" name="infrastructure" href="./content/client/client.php?tab=0&load_old_table_flag=1">
                    <span>{$LANG['UI_CLIENT_MANAGER']}</span>
                </a>
            </li>
            <span>></span>
EOF;
    }
    ?>
	<span class="curent"><?php echo $LANG['UI_CLIENT_APPLICATION_CONFIG'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height: calc(100% - 22px)">
	<div class="col-md-12 height100p">
		<input id="clientUUID" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
		 <span id="clientIp" class="display-none"><?php echo $_GET['ip'] ?></span>
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" >
			<div class="portlet-title">
				<div class="caption">
					<?php echo $LANG['UI_AGENT_HOST_NAME'] ?>--<span id="clientName"><?php echo $_GET['name'] ?></span>
				</div>
			</div>
			<div class="portlet-body min-height200" id="appconfig">
				<div class="tab-content row margin10">
                    <div class="table-toolbar" style="margin-bottom: 12px">
                        <div class="vin_toolbar mb-0" id="vin_app_toolbar">
                            <div class="leftTool">
                                <div class="customBtn1"></div>
                                <div class="customBtn2"></div>
                            </div>
                            <div class="rightTool">
                                <div class="vin_btnAppToolbar"></div>
                            </div>
                        </div>
                    </div>
					<div class="table-container reset-td-width application-table-wrapper">
                        <table class="table" id="applicationTable"></table>
					</div>
				</div>
			</div>
		</div>
		<!-- END VALIDATION STATES-->
		<!-- BEGIN ADD MODAL -->
		<div id="appModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div id="uuid" class="display-none"></div>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title addAppDiv">
                    <i class="viconfont vicon-danchuangtianjia1"></i>
                    <span class="text"><?php echo $LANG['UI_CLIENT_APP_ADD'] ?></span>
                </h4>
				<h4 class="modal-title display-none editAppDiv"><i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_CLIENT_APP_EDIT'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body form">
					<form action="#" class="form-horizontal" id="submit_form" method="POST">
					<div class="form-wizard">
						<div class="form-body">
							<ul class="nav nav-pills steps">
								<li>
									<a href="#tab1" data-toggle="tab" class="step">
										<span class="number">1 </span>
										<span class="desc">
											<?php echo $LANG['UI_CLIENT_APP_SELECT_RES'] ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
										<span class="number">2 </span>
										<span class="desc">
											<?php echo $LANG['UI_CLIENT_APPLICATION_CONFIG'] ?>
												<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
							</ul>
							<!--<div id="bar" class="progress progress-striped" role="progressbar">
								<div class="progress-bar progress-bar-success">
								</div>
							</div>-->
							<div class="tab-content">
								<div class="tab-pane active" id="tab1">
									<div class="row">
										  <div class="col-md-12">
                                              <div class="form-group">
                                                  <label class="control-label col-md-3 col-md-2_en">
                                                      <span class="required">* </span>
                                                      <?php echo $LANG['UI_CLIENT_APP_TYPE'] ?>
                                                  </label>
                                                  <div class="col-md-6">
                                                      <select class="form-control select2me" id="appType" ></select>
                                                      <div><span class="help-block ">
                                                      </span></div>
                                                  </div>
                                              </div>

                                              <!-- TiDB - 实例集群 - 扫描实例-->
                                              <div class="form-group tidbClusterDiv display-none">
                                                  <label class="control-label col-md-3" for="tidbClusterCheck">
                                                      <span class="required">* </span>
                                                      <?php echo $LANG['UI_DB_CLUSTER_CONNECT']?>
                                                  </label>
                                                  <div class="col-md-4">
                                                      <input type="checkbox" id="tidbClusterCheck" class="make-switch" data-size="small"
                                                        data-on-color="primary" data-off-color="info"
                                                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                      <span class="help-block">
                                                          <?php echo $LANG['UI_DB_ORACLE_CLUSTER_CONNECT_TIPS'] ?>
                                                      </span>
                                                  </div>
                                              </div>

                                              <!-- TiDB - 实例集群 - 选择关联主机 -->
                                              <div class="form-group display-none tidbClusterTreeDiv mt15">
                                                  <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CLUSTER_SELECT_INSTANCE'] ?>
                                                  </label>
                                                  <div class="col-md-8">
                                                      <div class="alert alert-block alert-info fade in display-hide"  id="tidbNoAgent">
                                                          <button type="button" class="close" data-dismiss="alert"></button>
                                                          <ul class="alert-ul">
                                                              <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                              <li>
                                                                  <?php echo $LANG['UI_DB_AUTH_INSTANCE_CLUSTER_TIPS'] ?>
                                                              </li>
                                                          </ul>
                                                      </div>
                                                      <ul id="tidbAgentTree" class="ztree bd1de5  tree_div" style="max-height: 120px; overflow: auto;"></ul>
                                                  </div>
                                              </div>

                                              <!-- TiDB - 实例集群 - 扫描按钮-->
                                              <div class="form-group tidbClusterDiv display-none">
                                                  <div class="col-md-offset-3 col-md-4">
                                                      <button type="button" id="scanTidbInstall" class="btn btn-light-primary">
                                                          <?php echo $LANG['UI_DB_TIDB_INSTALL_START_SCAN'] ?>
                                                      </button>
                                                  </div>
                                              </div>

                                              <!-- 实例列表 -->
                                              <div class="form-group instanceDiv display-none">
                                                  <label class="control-label col-md-3 col-md-2_en">
                                                      <span class="required">* </span>
                                                      <?php echo $LANG['UI_DB_SELECT_INSTANCE_VERIFY'] ?>
                                                  </label>
                                                  <div class="col-md-8  col-md-10_en">
                                                      <div class="table-toolbar">
                                                          <div class="vin_toolbar mb-0" id="vin_instance_toolbar">
                                                              <div class="leftTool">
                                                                  <div class="row">
                                                                      <div class="col-md-12">
                                                                          <button type="button" id="btInstance" class="btn btn-sm green-haze addInstance display-none">
                                                                              <?php echo $LANG['UI_DB_ADD_INSTANCE'] ?>
                                                                          </button>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                              <div class="rightTool">
                                                                  <div class="vin_btnInstanceToolbar"></div>
                                                              </div>
                                                          </div>
                                                      </div>
                                                      <div class="table-container reset-td-width">
                                                          <table class="table" id="instanceTable"></table>
                                                          <div id="selecttips">
                                                              <span class="help-block "></span>
                                                          </div>
                                                      </div>
                                                  </div>
                                              </div>
										  </div>
									</div>
								</div>
								<div class="tab-pane" id="tab2">
									<div class="row">
										<div class="col-md-12 mb15">
											<div class="form-group authItem authtypeDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_DB_IDENTITY_VERIFY_TYPE'] ?>
												</label>
												<div class="col-md-6 d-flex">
													<select class="form-control select2me" id="authtype" name="authtype">
														<option value="1"><?php echo $LANG['WEB_DB_WINDOWS_AUTH'] ?></option>
														<option value="2"><?php echo $LANG['WEB_DB_SQLSERVER_AUTH'] ?></option>
													</select>
                                                    <a class="popovers ml12 pt7" data-container="body" data-trigger="hover"
                                                       data-placement="right" data-content="<?php echo $LANG['UI_DB_IDENTITY_VERIFY_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
												</div>
											</div>
											<div class="form-group authItem oracleAddDiv display-hide">
												<label class="control-label col-md-3">
													<span class="required">* </span>
													<?php echo $LANG['UI_DB_INSTANCE_NAME'] ?>
												</label>
												<div class="col-md-8">
													<div class="input-icon right">
														<input type="text" maxlength="128" class="form-control" id="instancename" />
														<div><span class="help-block ">
														<?php echo $LANG['UI_DB_ORACLE_INSTANCE_NAME_TIPS'] ?>
														</span></div>
													</div>
												</div>
											</div>

											<!-- Oracle身份认证 -->
											<div class="form-group authItem oracleAuthTypeDiv">
												<label class="control-label col-md-3">
													<?php echo $LANG['UI_DB_IDENTITY_VERIFY_TYPE'] ?>
												</label>
												<div class="col-md-6 d-flex">
													<select class="form-control select2me" id="oracleAuthType">
														<option value="2"><?php echo $LANG['WEB_DB_DATABASE_AUTH'] ?></option>
														<option value="1"><?php echo $LANG['WEB_DB_OS_AUTH'] ?></option>
													</select>
                                                    <a class="popovers ml12 pt7" data-container="body" data-trigger="hover" data-html="true"
                                                       data-placement="right" data-content="<?php echo $LANG['UI_DB_IDENTITY_VERIFY_TIPS2'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
												</div>
											</div>
											<div class="userDiv authItem display-hide">
												<div class="form-group installnameDiv">
													<label class="control-label col-md-3 installLabel">
														<span class="required">* </span>
														<?php echo $LANG['UI_DB_INSTALL_USER_NAME'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="installname" />
															<div><span class="help-block installTips">
															<?php echo $LANG['UI_DB_ORACLE_INSTALL_NAME_TIPS'] ?>
															</span></div>
														</div>
													</div>
												</div>
												<div class="form-group installpathDiv display-hide">
													<label class="control-label col-md-3 installpathLabel">
														<span class="required">* </span>
														<?php echo $LANG['UI_CLIENT_APP_DB_BIN_PATH'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<div id="installPath"></div>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_CLIENT_APP_DB_BIN_PATH_OTHER'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>
												<!-- 数据库身份认证 -->
												<div class="form-group instanceAuthDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="authname"/>
															<div><span class="help-block ">
															<?php echo $LANG['UI_DB_INSTANCE_USER_NAME'] ?>
															</span></div>
														</div>
													</div>
												</div>

												<div class="form-group instanceAuthDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
                                                            <i class="sys-password-icon fa fa-eye" id="passwordEyeOn"></i>
                                                            <i class="sys-password-icon fa fa-eye-slash" id="passwordEyeOff"></i>
															<input type="password" autocomplete="off" maxlength="128" class="form-control" id="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
															<div><span class="help-block ">
															<?php echo $LANG['UI_DB_INSTANCE_PASSWORD'] ?>
															</span></div>
														</div>
													</div>
												</div>

												<!-- 操作系统身份认证 -->
												<div class="form-group osAuthDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="osAuthName"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_OS_USER_NAME'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>

												<div class="form-group osAuthDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
                                                            <i class="sys-password-icon fa fa-eye" id="osAuthPasswordEyeOn"></i>
                                                            <i class="sys-password-icon fa fa-eye-slash" id="osAuthPasswordEyeOff"></i>
															<input type="password" autocomplete="off" maxlength="128" class="form-control" id="osAuthPassword"
																   oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_OS_PASSWORD'] ?>
																</span>
															</div>
														</div>
													</div>
												</div><!--//osAuthDiv-->
											</div>

											<!-- MongoDB配置 -->
											<div class="mongodbDiv authItem display-hide">
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">
                                                        <span class="required">* </span>
                                                        <?php echo $LANG['UI_CLIENT_APP_DB_BIN_PATH'] ?>
                                                    </label>
                                                    <div class="col-md-8">
                                                        <div class="input-icon right">
                                                            <div id="mongodbBinPath"></div>
                                                            <div>
																<span class="help-block ">
																	<?php echo $LANG['UI_CLIENT_APP_DB_BIN_PATH_OTHER'] ?>
																</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="mongodbUsername"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_INSTANCE_USER_NAME'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>

												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
                                                            <i class="sys-password-icon fa fa-eye" id="mongodbPasswordEyeOn"></i>
                                                            <i class="sys-password-icon fa fa-eye-slash" id="mongodbPasswordEyeOff"></i>
															<input type="password" autocomplete="off" maxlength="128" class="form-control" id="mongodbPassword"
																   oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_INSTANCE_PASSWORD'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>
											</div><!--//MongoDB配置-->

											<!-- TiDB配置 -->
											<div class="tidbDiv authItem display-hide">
												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="tidbUsername"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_INSTANCE_USER_NAME'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>

												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
                                                            <i class="sys-password-icon fa fa-eye" id="tidbPasswordEyeOn"></i>
                                                            <i class="sys-password-icon fa fa-eye-slash" id="tidbPasswordEyeOff"></i>
															<input type="password" autocomplete="off" maxlength="128" class="form-control" id="tidbPassword"
																   oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_INSTANCE_PASSWORD'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>
											</div><!--//TiDB配置-->

											<!-- Caché/IRIS配置 -->
											<div class="IRISDiv authItem display-hide">
												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="IRISUsername"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_INSTANCE_USER_NAME'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>

												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
                                                            <i class="sys-password-icon fa fa-eye" id="IRISPasswordEyeOn"></i>
                                                            <i class="sys-password-icon fa fa-eye-slash" id="IRISPasswordEyeOff"></i>
															<input type="password" autocomplete="off" maxlength="128" class="form-control" id="IRISPassword"
																   oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_INSTANCE_PASSWORD'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>

												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_DB_TABLE_SPACE'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="IRISTableSpace"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_TABLE_SPACE_TIPS'] ?>
																</span>
															</div>
														</div>
													</div>
												</div><!--//IRIS table space-->
											</div><!--Cache/IRIS配置-->

											<!-- SAP HANA配置 -->
											<div class="sapHanaDiv authItem display-hide">
												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="sapHanaUsername"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_INSTANCE_USER_NAME'] ?>
																</span>
															</div>
														</div>
													</div>
												</div><!--SAP HANA用户名-->

												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
                                                            <i class="sys-password-icon fa fa-eye" id="sapHanaPasswordEyeOn"></i>
                                                            <i class="sys-password-icon fa fa-eye-slash" id="sapHanaPasswordEyeOff"></i>
															<input type="password" autocomplete="off" maxlength="128" class="form-control" id="sapHanaPassword"
																   oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_INSTANCE_PASSWORD'] ?>
																</span>
															</div>
														</div>
													</div>
												</div><!--SAP HANA密码-->
											</div><!--SAP HANA配置-->

											<!-- 集群关联 -->
											<div class="form-group authItem display-none clusterDiv">
												<label class="control-label col-md-3 form-group-label">
													<?php echo $LANG['UI_DB_CLUSTER_CONNECT'] ?>
												</label>
												<div class="col-md-9 form-group-content flex-items-center">
													<input type="checkbox" id="clusterCheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"
														data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
														data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
													<a class="popovers ml12 mb-1" data-container="body"
                                                       data-trigger="hover" data-placement="right"
                                                       data-content="<?php echo $LANG['UI_DB_ORACLE_CLUSTER_CONNECT_TIPS'] ?>" id="oracletip">
													    <i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>

											<!-- 集群别名 -->
											<div class="form-group authItem display-none clusterAliasDiv">
												<label class="control-label col-md-3">
													<span class="required">* </span>
													<?php echo $LANG['UI_DB_CLUSTER_ALIAS'] ?>
												</label>
												<div class="col-md-8">
													<div class="input-icon right">
														<input type="text" maxlength="128" class="form-control" id="clusterAlias"/>
														<div>
															<span class="help-block ">
																<?php echo $LANG['UI_DB_CLUSTER_ALIAS_TIPS'] ?>
															</span>
														</div>
													</div>
												</div>
											</div>

											<!-- 集群服务IP -->
											<div class="form-group authItem display-none clusterServiceIpDiv">
												<label class="control-label col-md-3">
													<span class="required">* </span>
													<?php echo $LANG['UI_DB_CLUSTER_SERVICE_IP'] ?>
												</label>
												<div class="col-md-8">
													<div class="input-icon right">
														<input type="text" maxlength="128" class="form-control" id="clusterServiceIp"/>
														<div>
															<span class="help-block ">
																<?php echo $LANG['UI_DB_CLUSTER_SERVICE_IP_TIPS'] ?>
															</span>
														</div>
													</div>
												</div>
											</div>

											<!-- 集群IP地址 -->
											<div class="form-group authItem display-none clusterIpDiv">
												<label class="control-label col-md-3">
													<span class="required">* </span>
													<?php echo $LANG['UI_DB_CLUSTER_IP_ADDR'] ?>
												</label>
												<div class="col-md-8">
													<div class="input-icon right">
														<input type="text" maxlength="128" class="form-control" id="clusterIp"/>
														<div><span class="help-block ">
															 <?php echo $LANG['UI_DB_CLUSTER_IP_ADDR_TIPS'] ?>
															</span></div>
													</div>
												</div>
											</div>

											<!-- MongoDB —— 选择选择路由节点(Mongos) -->
											<div class="form-group authItem display-none mongodbMongosTreeDiv mt15">
												<label class="control-label col-md-3"><?php echo $LANG['UI_DB_CLUSTER_SELECT_MONGOS'] ?></label>
												<div class="col-md-8">
													<div class="alert alert-block alert-info fade in display-hide"  id="mongodbNoAgent">
														<button type="button" class="close" data-dismiss="alert"></button>
														<ul class="alert-ul">
															<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
															<li>
																<?php echo $LANG['UI_DB_AUTH_INSTANCE_CLUSTER_TIPS'] ?>
															</li>
														</ul>
													</div>
													  <ul id="mongodbMongosTree" class="ztree bd1de5 tree_div" style="min-height: 80px; overflow: auto;"></ul>
												</div>
											</div>

											<!-- 集群 —— 选择关联主机 -->
											<div class="form-group authItem display-none clusterTreeDiv mt15">
												<label class="control-label col-md-3"><?php echo $LANG['UI_DB_CLUSTER_SELECT_INSTANCE'] ?>
												</label>
												<div class="col-md-8">
													<div class="alert alert-block alert-info fade in display-hide"  id="noagent">
														<button type="button" class="close" data-dismiss="alert"></button>
														<ul class="alert-ul">
															<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
															<li>
																<?php echo $LANG['UI_DB_AUTH_INSTANCE_CLUSTER_TIPS'] ?>
															</li>
														</ul>
													</div>
													  <ul id="agent_tree" class="ztree bd1de5  tree_div" style="min-height: 80px; overflow: auto;"></ul>
												</div>
											</div>

											<!-- 实例监听IP -->
											<div class="display-none authItem listenIpDiv">
												<div class="form-group">
													<label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_CLIENT_CONFIG_CLIENT_IP'] ?>
													</label>
													<div class="col-md-9 form-group-content flex-items-center">
														<input type="checkbox" id="listenIpCheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"
														    data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
														    data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
														<a class="popovers ml12 mb-1" data-container="body" data-trigger="hover"
														    data-placement="right" data-content="<?php echo $LANG['UI_CLIENT_CONFIG_CLIENT_IP_TIPS'] ?>">
														    <i class="viconfont vicon-tishi"></i>
														</a>
													</div>
												</div>

												<div class="form-group display-none ipListDiv">
													<label class="control-label col-md-3">

													</label>

													<div class="col-md-8" id="oracleIpList">
													</div>
												</div>

											</div>

											<!-- cnf路径 -->
											<div class="mysqlDiv authItem display-hide">
												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_DB_MYSQL_CNF_PATH'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<div id="cnfPath"></div>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_MYSQL_CNF_PATH_TIPS'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="mysqlname"/>
															<div><span class="help-block ">
															<?php echo $LANG['UI_DB_INSTANCE_USER_NAME'] ?>
															</span></div>
														</div>
													</div>
												</div>

												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
                                                            <i class="sys-password-icon fa fa-eye" id="mysqlPasswordEyeOn"></i>
                                                            <i class="sys-password-icon fa fa-eye-slash" id="mysqlPasswordEyeOff"></i>
															<input type="password" autocomplete="off" maxlength="128" class="form-control"
                                                                   id="mysqlpassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
															<div><span class="help-block ">
															<?php echo $LANG['UI_DB_INSTANCE_PASSWORD'] ?>
															</span></div>
														</div>
													</div>
												</div>
												<div class="form-group">
													<label class="control-label col-md-3">
														<?php echo $LANG['UI_DB_IDENTITY_VERIFY_TYPE'] ?>
													</label>
													<div class="col-md-6 d-flex">
														<select class="form-control select2me" id="mysqlAuthtype" >
															<option value="1"><?php echo $LANG['UI_CLIENT_APP_MYSQL_AUTH_TCP']; ?></option>
															<option value="2"><?php echo $LANG['UI_CLIENT_APP_MYSQL_AUTH_SOCK']; ?></option>
														</select>
                                                        <a class="popovers ml12 pt7" data-container="body" data-trigger="hover"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_CLIENT_APP_MYSQL_AUTH_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
													</div>
												</div>
												<div class="form-group tcpipDiv" id="mysqlIpDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_CLIENT_IP_ADDRESS'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa fa-warning" data-original-title="" style="display: none"></i>
															<input type="text" maxlength="128" class="form-control" id="mysqlIp" />
															<div><span class="help-block ">
															<?php echo $LANG['UI_CLIENT_APP_MYSQL_AUTH_TCP_TIPS1']; ?>
															</span></div>
														</div>
													</div>
												</div>
												<div class="form-group tcpipDiv" id="mysqlPortDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_VECNTER_ADD_VC_IP_PORT'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa fa-warning" data-original-title="" style="display: none"></i>
															<input type="text" maxlength="128" class="form-control" id="port" />
															<div><span class="help-block ">
															<?php echo $LANG['UI_CLIENT_APP_MYSQL_AUTH_TCP_TIPS2']; ?>
															</span></div>
														</div>
													</div>
												</div>

												<div class="form-group display-none sockDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_CLIENT_APP_MYSQL_AUTH_SOCK_TITLE1']; ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="mysqlHost" />
															<div><span class="help-block ">
																<?php echo $LANG['UI_CLIENT_APP_MYSQL_AUTH_SOCK_TIPS1']; ?>
															</span></div>
														</div>
													</div>
												</div>

												<div class="form-group display-none sockDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_CLIENT_APP_MYSQL_AUTH_SOCK_TITLE2']; ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<div id="sockPath"></div>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_CLIENT_APP_MYSQL_AUTH_SOCK_TIPS2']; ?>
																</span>
															</div>
														</div>
													</div>
												</div>

											</div>

											<div class="mysqlListenIpDiv  authItem ">
												<div class="form-group">
													<label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_CLIENT_CONFIG_CLIENT_IP'] ?>
													</label>
													<div class="col-md-9 form-group-content flex-items-center">
														<input type="checkbox" id="mysqlListenIpCheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
														<a class="popovers ml12 mb-1" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_CLIENT_CONFIG_CLIENT_IP_TIPS'] ?>">
															<i class="viconfont vicon-tishi"></i>
														</a>
													</div>
												</div>

												<div class="form-group display-none mysqlIpListDiv">
													<label class="control-label col-md-3">

													</label>

													<div class="col-md-8">
														<div class="input-icon right mb15">
															<i class="fa fa-warning" data-original-title="" style="display: none"></i>
															<input type="text" maxlength="128" class="form-control listenIp"
																   id="mysqlListenIp" value="127.0.0.1" />
														</div>
													</div>
												</div>

											</div>

											<div class="mariaDiv authItem display-hide">
												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_DB_MARIA_CNF_PATH'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<div id="mariaCnfPath"></div>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_DB_MARIA_CNF_PATH_TIPS'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>

												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="marianame"/>
															<div><span class="help-block ">
															<?php echo $LANG['UI_DB_INSTANCE_USER_NAME'] ?>
															</span></div>
														</div>
													</div>
												</div>

												<div class="form-group">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
                                                            <i class="sys-password-icon fa fa-eye" id="mariaPasswordEyeOn"></i>
                                                            <i class="sys-password-icon fa fa-eye-slash" id="mariaPasswordEyeOff"></i>
															<input type="password" autocomplete="off" maxlength="128" class="form-control"
																   id="mariapassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
															<div><span class="help-block ">
															<?php echo $LANG['UI_DB_INSTANCE_PASSWORD'] ?>
															</span></div>
														</div>
													</div>
												</div>

												<div class="form-group">
													<label class="control-label col-md-3">
														<?php echo $LANG['UI_DB_IDENTITY_VERIFY_TYPE'] ?>
													</label>
													<div class="col-md-6 d-flex">
														<select class="form-control select2me" id="mariaAuthtype" >
															<option value="1"><?php echo $LANG['UI_CLIENT_APP_MARIADB_AUTH_TCP']; ?></option>
															<option value="2"><?php echo $LANG['UI_CLIENT_APP_MARIADB_AUTH_SOCK']; ?></option>
														</select>
                                                        <a class="popovers ml12 pt7" data-container="body" data-trigger="hover"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_CLIENT_APP_MARIADB_AUTH_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
													</div>
												</div>
												<div class="form-group mariaTcpipDiv" id="mariaIpDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_CLIENT_IP_ADDRESS'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa fa-warning" data-original-title="" style="display: none"></i>
															<input type="text" maxlength="128" class="form-control" id="mariaIp" />
															<div><span class="help-block ">
																<?php echo $LANG['UI_CLIENT_APP_MARIADB_AUTH_TCP_TIPS1']; ?>
															</span></div>
														</div>
													</div>
												</div>
												<div class="form-group mariaTcpipDiv" id="mariadbPortDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_VECNTER_ADD_VC_IP_PORT'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa fa-warning" data-original-title="" style="display: none"></i>
															<input type="text" maxlength="128" class="form-control" id="mariaport" />
															<div><span class="help-block ">
																<?php echo $LANG['UI_CLIENT_APP_MARIADB_AUTH_TCP_TIPS2']; ?>
															</span></div>
														</div>
													</div>
												</div>

												<div class="form-group display-none mariaSockDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_CLIENT_APP_MARIADB_AUTH_SOCK_TITLE1'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<input type="text" maxlength="128" class="form-control" id="mariaHost" />
															<div><span class="help-block ">
																<?php echo $LANG['UI_CLIENT_APP_MARIADB_AUTH_SOCK_TIPS1'] ?>
															</span></div>
														</div>
													</div>
												</div>

												<div class="form-group display-none mariaSockDiv">
													<label class="control-label col-md-3">
														<span class="required">* </span>
														<?php echo $LANG['UI_CLIENT_APP_MARIADB_AUTH_SOCK_TITLE2'] ?>
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<div id="mariaSockPath"></div>
															<div>
																<span class="help-block ">
																	<?php echo $LANG['UI_CLIENT_APP_MARIADB_AUTH_SOCK_TIPS2'] ?>
																</span>
															</div>
														</div>
													</div>
												</div>

											</div><!--//mariaDiv-->

											<div class="mariaListenIpDiv authItem">
												<div class="form-group">
													<label class="control-label col-md-3 form-group-label">
														<?php echo $LANG['UI_CLIENT_CONFIG_CLIENT_IP'] ?>
													</label>
													<div class="col-md-9 form-group-content flex-items-center">
														<input type="checkbox" id="mariaListenIpCheck" class="make-switch" data-size="small"
															   data-on-color="primary" data-off-color="info"
															   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
															   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
														<a class="popovers ml12 mb-1" data-container="body" data-trigger="hover" data-placement="right"
														   data-content="<?php echo $LANG['UI_CLIENT_CONFIG_CLIENT_IP_TIPS'] ?>">
															<i class="viconfont vicon-tishi"></i>
														</a>
													</div>
												</div>

												<div class="form-group display-none mariaIpListDiv">
													<label class="control-label col-md-3">

													</label>

													<div class="col-md-8">
														<div class="input-icon right mb15">
															<i class="fa fa-warning" data-original-title="" style="display: none"></i>
															<input type="text" maxlength="128" class="form-control listenIp"
																   id="mariaListenIp" value="127.0.0.1" />
														</div>
													</div>
												</div>

											</div><!--//mariaListenIpDiv-->
										</div>
									</div>
								</div><!--//tab2-->
							</div>
						</div>
					</div>
				</form>
				</div>
			</div>

			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn default button-previous" ><?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?></button>
				<button type="button" class="btn green-turquoise button-next " ><?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?></button>
				<button type="button" class="btn green-haze button-submit" id="add_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END MODAL -->
	</div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script src="./scripts/plugins/path-tree-selector.js" type="text/javascript"></script>
<script src="./scripts/client/application_config.js" type="text/javascript"></script>
