<div class="d-flex justify-content-center align-items-center">
    <form class="form needs-validation flex-direction-column wp-50" novalidate>
        <div class="form-group is-required">
            <label class="form-group__label col-md-3">选择备份节点</label>
            <div class="form-group__content col-md-6">
                <select id="nodeSelect" name="nodeUuid" class="form-control select2">

                </select>
            </div>
        </div>

        <div class="form-group is-required">
            <label class="form-group__label col-md-3">选择网卡</label>
            <div class="form-group__content col-md-6">
                <select id="ipSelect" class="form-control select2" name="ipCard">

                </select>
            </div>
        </div>

        <div class="form-group">
            <div class="form-group__label col-md-3">
                ip地址
            </div>
            <div class="form-group__content col-md-6">
                <input type="text" name="ipAddress" class="form-control" placeholder="请输入" value="">
            </div>
        </div>

        <div class="form-group">
            <div class="form-group__label col-md-3">
                前缀
            </div>
            <div class="form-group__content col-md-6">
                <input type="number" name="prefix" class="form-control" placeholder="请输入" value="">
            </div>
        </div>

        <div class="form-group">
            <div class="form-group__label col-md-3">
                子网掩码
            </div>
            <div class="form-group__content col-md-6">
                <input type="text" name="netmask" class="form-control" placeholder="请输入" value="">
            </div>
        </div>

        <div class="form-group">
            <div class="form-group__label col-md-3">
                默认网关
            </div>
            <div class="form-group__content col-md-6">
                <input type="text" name="gateway" class="form-control" placeholder="请输入" value="">
            </div>
        </div>

        <div class="form-group">
            <div class="form-group__label col-md-3">
                DNS服务器
            </div>
            <div class="form-group__content col-md-6">
                <input type="text" name="dns" class="form-control" placeholder="请输入" value="">
            </div>
        </div>

        <div class="form-group pt-8">
            <label class="form-group__label col-md-3"></label>
            <div class="form-group__content col-md-6 d-flex">
                <button type="submit" id="submit_form"
                        class="btn btn-lg btn-primary btn-submit me-16">保存</button>
                <button type="button" id="reset_form"
                        class="btn btn-lg btn-outline-primary btn-reset">重置</button>
            </div>
        </div>
    </form>
</div>


<script type="text/javascript" src="/system/settings/network/js/set-ip.js"></script>