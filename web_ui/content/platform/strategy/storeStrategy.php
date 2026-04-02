<div class="accordion strategyOne store-strategy-form">
    <div class="panel panel-default strategy-panel">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#store" aria-expanded="true">
                    <i class="viconfont vicon-cunchu font-green-seagreen"></i>
                    <span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
                    <span class="strategyDes storeDes"></span>
                </a>
            </h4>
        </div>
        <div id="store" class="panel-collapse collapse ">
            <div class="panel-body">
                <!-- 重复数据删除 -->
                <div class="form-group deduplicationDiv">
                    <label class="control-label col-md-2 deduplicationLabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_DEDUPLICATION'] ?></label>
                    <div class="col-md-4">
                        <input type="checkbox" id="deduplicationCheck" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DEDUPLICATION_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <!-- 压缩存储 -->
                <div class="form-group">
                    <label class="control-label col-md-2 compressLabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?></label>
                    <div class="col-md-4">
                        <input type="checkbox" id="compressCheck" checked class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_COMPRESS_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <!-- 压缩等级选择 -->
                <div class="form-group compressGradeDiv">
                    <label class="control-label col-md-2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
                    <div class="col-md-4">
                        <select class="form-control select2me input-sm" id="compressGrade">
                            <option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
                            <option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
                            <option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
                            <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
                        </select>
                    </div>
                </div>
                <!-- 数据加密 -->
                <div class="form-group">
                    <label class="control-label col-md-2 encryptStorageLabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?></label>
                    <div class="col-md-4">
                        <input type="checkbox" id="encryptStorageCheck" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DATE_ENCRY_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <!-- 存储加密 -->
                <div class="form-group storage-encrypt-div display-none">
                    <label class="control-label col-md-2 storage-encrypt-label"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                    <div class="col-md-4">
                        <select class="form-control select2me input-sm" id="storageEncryptMethod">
                            <?php
                            foreach ($CONF['STORE_ENCRYPT_METHOD'] as $index => $encryptMethod) {
                                    //英文版屏蔽SM加密
                                    if ((($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") || $CONF['SYSTEM_INFO']['enterprise'] == 'vinchin_enterprise_en') && $index == 2) {
                                }else{
                                    echo '<option value="' . $index . '">' . $encryptMethod . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <!-- 自动生成密码 -->
                <div class="form-group display-none passwordModeDiv">
                    <label class="control-label col-md-2 passwordAutoLabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?></label>
                    <div class="col-md-4">
                        <input type="checkbox" id="passwordAutocheck" class="make-switch" checked data-on-color="primary" data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>
                <!-- 密码 -->
                <div class="form-group display-none passwordDiv">
                    <label class="control-label col-md-2 passwordlabel"><?php echo $LANG['UI_BACKUP_PASSWORD'] ?></label>
                    <div class="col-md-4"  style="position: relative;">
                        <input type="password" autocomplete="off" maxlength="128" class="form-control input-sm" id="password" placeholder="" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                        <button type="button" class="btn btn-link show-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                            <i class="viconfont vicon-a-lujing8232"></i>
                        </button>
                    </div>
                </div>
                <!-- 确认密码 -->
                <div class="form-group display-none passwordDiv">
                    <label class="control-label col-md-2 repasswordlabel"><?php echo $LANG['UI_BACKUP_PASSWORD_CONFIRM'] ?></label>
                    <div class="col-md-4"  style="position: relative;">
                        <input type="password" autocomplete="off" maxlength="128" class="form-control input-sm" id="repassword" placeholder="" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                        <button type="button" class="btn btn-link show-rePassword-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                            <i class="viconfont vicon-a-lujing8232"></i>
                        </button>
                    </div>
                    <div class="col-md-4 display-none passwordTips" style="padding: 10px 0 0 0;color: #F3565D;"><?php echo $LANG['UI_BACKUP_PASSWORD_CONFIRM_ERROR'] ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.storeStrategy.js"></script>