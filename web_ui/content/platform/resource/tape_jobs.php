<div class="tape-task-wrapper">
    <div class="vin_toolbar" id="vin_tape_job_toolbar">
        <div class="leftTool"></div>
        <div class="rightTool">
            <div class=" vin_btnToolbar3"></div>
        </div>
    </div>
    <div class="table-container">
        <table id="tape_job_table"></table>
    </div>
    <div class="alert alert-block alert-info fade in h-50px mt-10 mb-0" id="marktips">
        <button type="button" class="close" data-dismiss="alert"></button>
        <ul class="alert-ul">
            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
            <li>
                <?php echo $LANG['UI_JOB_CURRENT_NAME_TIPS'] ?>
            </li>
        </ul>
    </div>
</div>

<script src="./scripts/platform/resource/tape_jobs.js"></script>
