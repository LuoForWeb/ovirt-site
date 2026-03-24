</div>
<!-- END CONTAINER -->

<!-- BEGIN CORE PLUGINS SCRIPTS -->
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.history.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.bundle.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS SCRIPTS -->

<!-- BEGIN GLOBAL PLUGINS SCRIPTS -->
<script src="/assets/global/plugins/jquery-block-ui/jquery.block-ui.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap-toastr/js/toastr.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/select2/js/select2.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/flatpicker/js/flatpicker.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-daterangepicker/js/moment.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-daterangepicker/js/daterangepicker.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-daterangepicker/js/daterangepicker.locales.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/flatpicker/js/zh.js" type="text/javascript"></script>
<script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap-jbvalidator/jbvalidator.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/ztree/js/jquery.ztree.core.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jstree/jstree.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/axios/axios.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-input-spinner/bootstrap-input-spinner.min.js" type="text/javascript"></script>
<!-- END GLOBAL PLUGINS SCRIPTS -->

<!-- BEGIN BOOTSTRAP TABLE -->
<script src="./assets/global/plugins/bootstrap-table/js/tableExport.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-table/js/bootstrap-table.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-table/js/bootstrap-table-zh-CN.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-table/js/bootstrap-table-export.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-table/js/bootstrap-table-resizable.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-table/js/jquery.resizableColumns.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-table/js/bootstrap-table-page-jump-to.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-table/js/bootstrap-table-fixed-columns.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-table/js/bootstrap-table-toolbar.min.js" type="text/javascript"></script>
<!-- END BOOTSTRAP TABLE -->

<script src="/js/config/config.js" type="text/javascript"></script>

<!-- 核心i18n库 -->
<script src="/js/libs/i18n-core.js" defer></script>
<script src="/js/libs/ajax-i18n.js" defer></script>

<!-- BEGIN COMMON SCRIPTS -->
<script src="/assets/global/plugins/jquery-idle-timeout/jquery.idletimeout.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-idle-timeout/jquery.idletimer.js" type="text/javascript"></script>
<script src="/assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="/js/libs/json2.min.js" type="text/javascript"></script>
<script src="/js/config/config.js" type="text/javascript"></script>
<script src="/js/common/event-bus.js" type="text/javascript"></script>
<script src="/js/common/public.js" type="text/javascript"></script>
<script src="/js/common/http.js" type="text/javascript"></script>
<?php
$constant_path = $_SESSION['ROOTPATH'] . 'js/constant/';
$constant_web_path = '/js/constant/';

// 遍历取出 constant 文件夹下的所有常量js
if (is_dir($constant_path)) {
    if ($handle = opendir($constant_path)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && strpos($entry, '.js') !== false) {
                echo '<script src="' . $constant_web_path . $entry . '" type="text/javascript"></script>' . "\n";
            }
        }
        closedir($handle);
    }
}
?>
<script src="/js/common/ui-toastr.js" type="text/javascript"></script>
<script src="/js/common/ui-idletimeout.js" type="text/javascript"></script>
<?php
$utils_path = $_SESSION['ROOTPATH'] . 'js/utils/';
$utils_web_path = '/js/utils/';

// 遍历取出 utils 文件夹下的所有工具js
if (is_dir($utils_path)) {
    if ($handle = opendir($utils_path)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && strpos($entry, '.js') !== false) {
                echo '<script src="' . $utils_web_path . $entry . '" type="text/javascript"></script>' . "\n";
            }
        }
        closedir($handle);
    }
}
?>
<script src="/js/common/init.js" type="text/javascript"></script>
<!-- END COMMON SCRIPTS -->