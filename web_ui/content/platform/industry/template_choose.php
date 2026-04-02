<!--选择模板-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="drawer-indust_template_config" style="width: 880px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top">
                <i class="viconfont vicon-hangyeheguijiancha" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING']?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <!-- BEGIN FORM-->
        <?php
            include_once 'template_config.php';
        ?>
    </div>
</div>
<!-- END MODAL -->
<script type="text/javascript" src="./scripts/platform/industry/template_choose.js"></script>
