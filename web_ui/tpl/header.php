<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>
        <?php
        if (file_exists($CONF['SYSTEM_NAME_FILE'])) {
            //如果自定义系统名称存在
            echo file_get_contents($CONF['SYSTEM_NAME_FILE']);
        } else {
            //如果自定义系统名称不存在
            echo $CONF['SYSTEM_INFO']['system_name'];
        }
        ?>
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description" />
    <meta content="vinchin.com" name="author" />
    <meta name="renderer" content="webkit">

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-toastr/css/toastr.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/flatpicker/css/flatpickr.min.css" rel="stylesheet" type="text/css" />
    <link href="./assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="./assets/global/plugins/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" type="text/css" />
    <link href="./assets/global/plugins/bootstrap-daterangepicker/css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/ztree/css/metroStyle/metroStyle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/jstree/themes/default/style.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/jquery-smartWizard/css/smart_wizard.min.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BEGIN BOOTSTRAP TABLE STYLES -->
    <link href="./assets/global/plugins/bootstrap-table/css/jquery.resizableColumns.css" rel="stylesheet" type="text/css">
    <link href="./assets/global/plugins/bootstrap-table/css/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
    <link href="./assets/global/plugins/bootstrap-table/css/bootstrap-table-page-jump-to.min.css" rel="stylesheet" type="text/css" />
    <link href="./assets/global/plugins/bootstrap-table/css/bootstrap-table-fixed-columns.min.css" rel="stylesheet" type="text/css" />
    <!-- END BOOTSTRAP TABLE STYLES -->

    <!-- BEGIN SYSTEM FRAMEWORK STYLES -->
    <link href="/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css" />
    <link href="/assets/admin/layout/css/component.css" rel="stylesheet" type="text/css" />
    <!-- END SYSTEM FRAMEWORK STYLES -->

    <!-- BEGIN COMMON STYLES -->
    <link href="/css/viconfont/iconfont.css" rel="stylesheet" type="text/css" />
    <link href="/css/main.css" rel="stylesheet" type="text/css" />
    <link href="/css/common.css" rel="stylesheet" type="text/css" />
    <!-- END COMMON STYLES -->

    <!-- END LOCAL STYLES -->
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
    <?php
    echo '<link href="/css/lang/' . $_SESSION['language'] . '.css" rel="stylesheet" type="text/css"/>';
    ?>
</head>
<!-- END HEAD -->