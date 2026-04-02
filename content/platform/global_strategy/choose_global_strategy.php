<?php
include_once   ROOT_PATH . '/content/platform/public/bs_table.php';
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<style>
    #add-strategy>i{vertical-align: text-top;}
</style>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <!-- Begin: life time stats grey-cararra -->
          
            <div class="portlet-body">
                <div class="row">
                    <div class="col-md-12 strategy-table" style="padding: 0">
                        <!-- Begin: life time stats grey-cararra -->
                        
                        <table id="table"></table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End: life time stats -->
    </div>
</div>

<!-- END PAGE CONTENT-->

<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./scripts/platform/global_strategy/choose_global_strategy.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
