<link rel="stylesheet" type="text/css" href="/component/css/style.css" />
<div class="panel-body" id="storagemount_component_body">

    <div class="col-md-12 paddding-l-r-0">
        <div class="form-group">
            <label class="control-label col-md-3">
                <span class="required">* </span>
                <span class="show_domain_ip_point"><?php echo $LANG['UI_PLATFORM_MOUNT_POINT_OR_IP'];?></span>
                <span class="show_domain_ip_point1 display-none"><?php echo $LANG['UI_PLATFORM_MOUNT_POINT_OR_IP1'];?></span>
                <span class="show_domain_ip_point2 display-none"><?php echo $LANG['UI_PLATFORM_MOUNT_POINT_OR_IP2'];?></span>
            </label>
            <div class="col-md-6 auto-change-scale">
                <div class="selectipdiv">
                    <select class="form-control serveripaddr">
                    </select>
                    <div>
                        <span class="help-block show-diy"><?php echo $LANG['UI_INSTANT_VM_TARGET']?>
                            <a class="diyserverip"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY']?></a>
                        </span>
                        <span class="help-block display-none not-show-diy">
                            <span class="normal_tips_"> <?php echo $LANG['UI_INSTANT_VM_TARGETS']?></span>
                            <span class="normal_tips2_ display-none"> <?php echo $LANG['UI_INSTANT_VM_TARGETS2']?></span>
                        </span>
                    </div>
                </div>
                <div class="input-icon right mb15 display-none inputipdiv">
                    <i class="fa"></i>
                    <input type="text" maxlength="128" class="form-control ip_domain"/>
                    <div>
                        <span class="help-block "><?php echo $LANG['UI_INSTANT_VM_TARGET2']?>
                            <a class="selectserverip"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY2']?></a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 mt-20 paddding-l-r-0 grain_job_detail_" style="margin-bottom: 5px;">
        <div class="form-group">
            <label class="control-label col-md-3">
                <span class="required">* </span>
                <?php echo $LANG['UI_PLATFORM_MOUNT_PROTOCOL'];?>
            </label>
            <div class="col-md-6 auto-change-scale">
                <div class="input-group width-100">
                    <select class="form-control width-100-15 amount_service">
                    </select>
                    <span class="input-group-btn amount_limit">
                        <button class="btn btn-service" type="button"><?php echo $LANG['UI_PLATFORM_MOUNT_CONFIGURE'];?></button>
                    </span>
                </div>
                <div>
                    <span class="help-block max-height-300 show-item-service-ip display-none">

                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

<!--配置访问地址 start-->
<div id="storagemount_component_modal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="levelchild viconfont vicon-ziyuangeli"></i> <?php echo $LANG['UI_PLATFORM_MOUNT_CONFIGURE'];?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="">
            <div class="list-option">
                <div class="row">
                    <!-- 限制模式 -->
                    <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_MOUNT_LIMIT_MODE'];?></label>
                    <div class="col-md-7">
                        <select class="form-control select2me storagemount_component_limit_mode">
                            <option value="0"><?php echo $LANG['UI_PLATFORM_MOUNT_LIMIT_MODE_NO'];?></option>
                            <option value="1"><?php echo $LANG['UI_PLATFORM_MOUNT_LIMIT_MODE_IP'];?></option>
                        </select>
                    </div>
                    <div class="col-md-1" style="line-height: 34px;margin-left: -30px;">
                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_PLATFORM_MOUNT_LIMIT_MODE_IP_TIPS'];?>" data-original-title="" title="">
                            <i class="viconfont vicon-tishi"></i>
                        </a></div>
                </div>
            </div>

            <div class="show-limit-ip display-none">
                <span class="limit-ip-item">
                    <div class="list-option">
                        <div class="row">
                            <!-- ip -->
                            <label class="control-label col-md-3">IP</label>
                            <div class="col-md-7">
                                <div class="input-group width-100">
                                    <div class="item-service-ip margin-0">
                                        <input type="text" name="limit_ip[]" value="">
                                        <button type="button"><i class="viconfont vicon-a-Reduce-onejianshao"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </span>

                <div class="list-option">
                    <div class="row">
                        <!-- ip -->
                        <label class="control-label col-md-3"></label>
                        <div class="col-md-7">
                            <div class="service-add-limit-ip">
                                <span><i class="viconfont vicon-zhediekuanganniu"></i><?php echo $LANG['UI_PLATFORM_MOUNT_LIMIT_MODE_ADD_IP'];?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
        <button type="button" class="btn btn-primary storagemount_component_modal_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
    </div>
</div>
<!--配置访问地址 end-->
<script type="text/javascript" src="/component/js/storage_mount.js"></script>
<script>

    // 初始化挂载协议组件demo，
    // 必须有 node_uuid参数
    // 可选 data 编辑的时候 内容格式和读取的保持一致
    // 可选 protocol 自定义协议列表 [{'name':'NFS', 'value': 1},{'name':'iSCSI','value': 2}]
    // 可选 class 设置默认的右边的class，控制长度显示
    /*var params = {
        'node_uuid': '5efa0bde-11b6-4c97-802a-4e39833a4f72',
        'data': {'ip_domain':'192.168.28.14', 'protocol':1, 'limit_ip':'192.168.28.15,192.168.24.14'},
        'protocol':[{'name':'NFS', 'value': 1}],
        'class': 'col-md-8'
    }
    storageMount.init(params);*/

    // 获取配置信息 示例
   // var info = storageMount.getAmountInfo();
  //  console.log('mount_info', info);
</script>