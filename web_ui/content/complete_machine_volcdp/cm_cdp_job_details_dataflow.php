<style>
    .textonline{
        position:relative;
        left:50%;
        transform:translateX(-50%);
        width:268px;
        text-align:center;
        margin-top: 15px;
        line-height:20px;
        font-size: 12px;
        z-index: 99;
        word-break: break-all;
    }
    .agentcolor {
        color: #B8C3CD;
    }
    .ipcolor{
        color: #7E8299;
    }
    .sourceTobackup,.backupTotarget{
        position:absolute;
        left:50%;
        top:50%;
        transform:translate(-50%,-50%);
    }
    .speed{
        font-size: 12px;
        color: #88A1B9;
    }
    .sourceToBackupServerSpeed,.backupServerTotargetSpeed{
        position:absolute;
        left:40%;
        top: -50%;
    }
    .failbackSpeed{
        position:absolute;
        left:48%;
        top:-35%;
    }
    #standbyHostRemoteControl {
        text-decoration: none;
    }

    @media (min-width: 1920px) {
        .chrome{
            position: absolute;
            left: 16.2rem;
            top: -4rem;
            width: 620px;
        }
        .data_flow{
            width: 700px;
        }
    }
    @media (min-width: 1440px) and  (max-width: 1919px) {
        .chrome{
            position: absolute;
            left: 6.5rem;
            top: -3.5rem;
            width: 565px;
        }
    }

    @media (min-width: 1910px) {
        .edge{
            position: absolute;
            left: 17.2rem;
            top: -4rem;
            width: 620px;
        }
    }
    @media (max-width: 1440px) {
        .edge{
            position: absolute;
            left: 5.5rem;
            top: -4.2rem;
            width: 620px;
        }
        .data_flow{
            width: 630px;
        }
        .sourceToBackupServerStatus,.backupServerTotargetStatus{
            width: 188px !important;
        }
        .sourceToBackupServerStatus_backup,.backupServerTotargetStatus_recovery{
            width: 425px !important;
        }
        .textonline{
            width: 110px;
        }
        .textonline_takeover{
            width: 120px;
        }
    }
</style>
<div class="tab-pane active" id="tab_cm_task_map" style="overflow: auto;">
    <div class="portlet-body">
        <div class="tab-content">
            <div class="tab-pane active">
                <div class="portlet-no-bgcolor col-md-12" style="position:relative;top: 50%;transform: translateY(-30%);">
                    <div class="portlet-body margin0auto data_flow">
                        <div class="row data_flow_content" style=""></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="./scripts/complete_machine_volcdp/cm_cdp_job_details_dataflow.js"></script>