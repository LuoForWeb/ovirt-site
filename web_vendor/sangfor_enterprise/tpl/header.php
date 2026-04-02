<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>
<?php 
    if(file_exists($CONF['SYSTEM_NAME_FILE'])){
        //如果自定义系统名称存在
        echo file_get_contents($CONF['SYSTEM_NAME_FILE']);
    }else{
        //如果自定义系统名称不存在
        echo $CONF['SYSTEM_INFO']['system_name'];
    }
?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta content="" name="description"/>
<meta content="vinchin.com" name="author"/>
<meta name="renderer" content="webkit">
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="./assets/global/css/gfonts1.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="./assets/global/plugins/bootstrap-toastr/toastr.min.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="./assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="./assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link id="style_color" href="./assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
<link href="./assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<link href="./css/iconfont/iconfont.css" rel="stylesheet" type="text/css"/>
<link href="./css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
<link href="./css/platform/main.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css"/>
<link href="./css/vm/virtualizationIcon.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-drawer/css/bootstrap-drawer.min.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="./assets/global/plugins/dropzone/css/dropzone.min.css" />
<link rel="stylesheet" href="./assets/global/plugins/nouislider/css/nouislider.min.css" />
<?php 
echo '<link href="./css/platform/lang/' . $_SESSION['language'] . '.css" rel="stylesheet" type="text/css"/>';
?>
<link href="./css/platform/revision.css" rel="stylesheet" type="text/css"/>
<link href="./css/platform/special.css" rel="stylesheet" type="text/css"/>
<link href="../css/vincomponent/css/vincomponent.css" rel="stylesheet" type="text/css" />
<!-- END THEME STYLES -->
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico"/>
<!-- 树形结构-->
<link rel="stylesheet" type="text/css" href="../scripts/plugins/ztree/css/metroStyle/metroStyle.css"/>
<!--表格-->
<link rel="stylesheet" type="text/css"href="../scripts/plugins/bootstrap-table/bootstrap-table.css" />

<!--主题颜色-->
</head>
<!-- END HEAD -->