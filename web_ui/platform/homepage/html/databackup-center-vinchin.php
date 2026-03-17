<!-- 主内容 -->
<div class="container">
    <div class="login-container">
        <!-- Logo和标题 -->
        <div class="text-center mb-4">
            <i class="bi bi-cloud-fill text-primary" style="font-size: 3rem;"></i>
            <h1 class="h3 mt-2" data-i18n="WEB_PLATFORM_TOKEN_ERROR">云管理平台</h1>
            <p class="text-muted" data-i18n="WEB_PAGE_COMMON_MENU_PLATFORM_HOMEPAGE">安全、高效、可靠的企业级解决方案</p>
        </div>

        <!-- 登录表单 -->
        <form id="loginForm">
            <div class="mb-3">
                <label for="username" class="form-label" data-i18n="LABEL_USERNAME">
                    <i class="bi bi-person"></i> 用户名
                </label>
                <input type="text"
                       class="form-control"
                       id="username"
                       required
                       data-i18n-placeholder="PLACEHOLDER_USERNAME"
                       placeholder="请输入用户名">
                <div class="form-text" data-i18n="HINT_USERNAME">
                    请输入您的用户名
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label" data-i18n="LABEL_PASSWORD">
                    <i class="bi bi-lock"></i> 密码
                </label>
                <input type="password"
                       class="form-control"
                       id="password"
                       required
                       data-i18n-placeholder="PLACEHOLDER_PASSWORD"
                       placeholder="请输入密码">
                <div class="form-text" data-i18n="HINT_PASSWORD">
                    请输入6-20位密码
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember" data-i18n="LABEL_REMEMBER">
                    记住登录状态
                </label>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" data-i18n="BUTTON_LOGIN">
                    <i class="bi bi-box-arrow-in-right"></i> 登录
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()" data-i18n="BUTTON_RESET">
                    <i class="bi bi-arrow-clockwise"></i> 重置
                </button>
            </div>

            <div class="mt-3 text-center">
                <a href="#" class="text-decoration-none" data-i18n="LINK_FORGOT">忘记密码？</a>
                <span class="mx-2">|</span>
                <a href="#" class="text-decoration-none" data-i18n="LINK_REGISTER">注册新账户</a>
            </div>
        </form>

        <!-- 统计信息 -->
        <div class="mt-4 pt-3 border-top">
            <div class="row text-center">
                <div class="col">
                    <h5 data-i18n="STATS_USERS">用户总数</h5>
                    <h3 class="text-primary">1,248</h3>
                </div>
                <div class="col">
                    <h5 data-i18n="STATS_ONLINE">在线用户</h5>
                    <h3 class="text-success">156</h3>
                </div>
            </div>
        </div>

        <!-- 温馨提示 -->
        <div class="alert alert-info mt-4" role="alert">
            <i class="bi bi-info-circle"></i>
            <span data-i18n="TIPS_SECURITY">请妥善保管您的登录凭证，建议定期更换密码。</span>
        </div>

        <!-- 页脚 -->
        <div class="mt-4 text-center text-muted">
            <small data-i18n="COPYRIGHT">© 2024 云管理平台. 保留所有权利.</small>
            <div class="mt-1">
                <a href="#" class="text-muted text-decoration-none me-2" data-i18n="LINK_PRIVACY">隐私政策</a>
                <a href="#" class="text-muted text-decoration-none" data-i18n="LINK_TERMS">服务条款</a>
            </div>
        </div>
    </div>
</div>
<?php
$ss = '23';
?>
<script src="/platform/homepage/js/databackup_center_vinchin.js"></script>
