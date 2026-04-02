<style>
    .item-title{
        margin-left: 2px;
        font-weight: 400;
        font-size: 14px;
        color: #333333;
        line-height: 22px;
        text-align: left;
        font-style: normal;
        text-transform: none;
    }
    .i{
        font-size: 18px;
        line-height: 24px;
    }
</style>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="role_add_edit_drawer" style="width: 800px">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top;padding-left:0;font-weight:400;font-size:16px;">
                <i class="viconfont vicon-ge_add_task role_drawer_title_i i"></i>
                <span style="vertical-align: top" id="role_drawer_title"><?php echo $LANG['UI_ROLE_ADD'];?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi i"></i></span>
            </h4>
        </div>
        <!-- BEGIN FORM-->
        <div class="drawer-body form-horizontal">
            <div class="portlet-body form" style="padding: 0 !important;">
                <div class="form-group" style="margin-top: 15px">
                    <label class="control-label col-md-2">
                        <span class="required">* </span><?php echo $LANG['UI_ROLE_NAME']?>
                    </label>
                    <div class="col-md-9">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input type="text" maxlength="64" class="form-control" id="role_name" name="rolename"/>
                            <div><span class="help-block ">
                                <?php echo $LANG['UI_ROLE_NAME_TIPS']?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="div_global_auth">
                    <label class="control-label col-md-2" style="margin-top:-6px;"><?php echo $LANG['UI_PLATFORM_GLOBAL_OBSERBER']?></label>
                    <div class="col-md-9">
                        <input type="checkbox"
                               id="choose_global_flag"
                               class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info"
                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <a class="popovers ml15" data-container="body"
                           data-trigger="hover" data-html="true"
                           data-placement="right"
                           data-content="<?php echo $LANG['UI_PLATFORM_GLOBAL_TIPS']?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                        <span class="help-block show-role-used" style="color: red;"><?php echo $LANG['UI_PLATFORM_GLOBAL_TIPS2']?></span>
                    </div>
                </div>

                <div class="form-group display-none" id="show_global_auth" style="margin-top:-10px;margin-bottom: 2px;">
                    <label class="control-label col-md-2"></label>
                    <div class="col-md-9">
                        <div class="input-group" style="margin-top: 4px">
                            <span style="padding-right:20px;" id="global_read">
                            <label class="backupDiv">
                                <input type="radio" class="icheck-verify-cycle" name="global_auth" value="global_read" >
                                 <span class="item-title"><?php echo $LANG['UI_PLATFORM_GLOBAL_READ']?></span>
                            </label>
                            </span>
                            <span id="global_write">
                                <label class="backupDiv">
                                    <input type="radio" checked class="icheck-verify-cycle" name="global_auth" value="global_write" >
                                    <span class="item-title"><?php echo $LANG['UI_PLATFORM_GLOBAL_WRITE']?></span>
                                </label>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-2">
                        <span class="required"> * </span><?php echo $LANG['UI_ROLE_PERMISSION']?>
                    </label>
                    <div class="col-md-9">
                        <div class="min-height250 mt10">
                            <ul id="permissionTree" class="ztree bd1de5  vcenter-tree ztree-fa"></ul>
                            <div><span class="help-block ">
                                <?php echo $LANG['UI_ROLE_PERMISSION_TIPS']?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="drawer-footer">
            <div id="bottom-btn">
                <button type="button" id="roleSubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default" style="margin-right:0;"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
            </div>
        </div>
    </div>
</div>
<!-- END MODAL -->
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/users/role_drawer.js"></script>