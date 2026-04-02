<div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header help_header">
            <h4 class="drawer-title help_title" id="drawer-1-title">
                <i class="viconfont vicon-bangzhuzhongxin help_icon_helpcenter"></i>帮助中心
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close help_icon_close"><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body help_body">
        <!doctype html>
<html>
 
 <body>
  <div >
   <h3 id="集群安装" >集群安装</h3>
   <p ><span >对目标集群创建和执行备份恢复任务前，需要首先在 Kubernetes 集群内安装备份系统对应的客户端，目前有如下三种方式进行安装：</span></p>
   <ul >
    <li >手动安装客户端</li>
    <li >通过 SSH 远程部署方式安装客户端</li>
    <li >通过集群连接配置文件（KubeConfig）方式安装客户端</li>
   </ul>
   <h4 id="一手动安装客户端" >一、手动安装客户端</h4>
   <p ><span >前提条件</span></p>
   <ul >
    <li >Helm 命令行工具存在：要在当前集群的节点上安装 Helm 命令行工具，它是 Kubernetes 的包管理工具，用于管理 Kubernetes 应用程序的部署。</li>
    <li >Helm 仓库地址可访问：确保备份服务器的主机地址能够被当前执行命令的节点正常访问，以便拉取所需的 Helm Chart。</li>
   </ul>
   <p ><span >步骤</span></p>
   <p ><span >1.1 添加 Helm 仓库</span></p>
   <p ><span >首先，添加备份系统的 Helm 仓库并更新仓库信息：</span></p>
   <pre class="code-fence" ><div class="code-fence-highlighter-copy-button" data-fence-content="aGVsbSByZXBvIGFkZCBiYWNrdXAtc3lzdGVtIGh0dHBzOi8vW+Wkh+S7veacjeWKoeWZqOWcsOWdgF06NDQzL2NoYXJ0cy8gLS1pbnNlY3VyZS1za2lwLXRscy12ZXJpZnkgCmhlbG0gc2VhcmNoIHJlcG8gYmFja3VwLXN5c3RlbSAKaGVsbSByZXBvIHVwZGF0ZSBiYWNrdXAtc3lzdGVt">
</div><code ><span ></span><span >helm repo add backup-system https://[备份服务器地址]:443/charts/ --insecure-skip-tls-verify 
</span><span >helm search repo backup-system 
</span><span >helm repo update backup-system 
</span><span ></span></code></pre>
   <p ><span >注意：添加 Helm 仓库时，需将上述命令中的</span> <code >[备份服务器地址]</code> <span >和</span> <code >443</code> <span >替换为实际的备份系统服务器的地址（或域名）和端口（默认443）。</span></p>
   <p ><span >1.2 安装客户端</span></p>
   <p ><span >根据网络模式选择以下命令之一进行安装。</span></p>
   <p ><span >1.2.1 服务端连接客户端模式（</span><code >server.net_model=1</code><span >）</span></p>
   <p ><span >此模式下，服务端主动连接客户端。执行以下命令：</span></p>
   <pre class="code-fence" ><div class="code-fence-highlighter-copy-button" data-fence-content="aGVsbSBpbnN0YWxsIGJhY2t1cC1zeXN0ZW0gYmFja3VwLXN5c3RlbS9jbGllbnQgLW4gYmFja3VwLXN5c3RlbSAtLWNyZWF0ZS1uYW1lc3BhY2UgXAogIC0taW5zZWN1cmUtc2tpcC10bHMtdmVyaWZ5IFwKICAtLXNldCBpbWFnZS5yZXBvPWt1YmUtYWdlbnQgXAogIC0tc2V0IGltYWdlLnJlZ2lzdHJ5PVvlpIfku73mnI3liqHlmajlnLDlnYBdOjUwMDA=">
</div><code ><span ></span><span >helm install backup-system backup-system/client -n backup-system --create-namespace \
</span><span >  --insecure-skip-tls-verify \
</span><span >  --set image.repo=kube-agent \
</span><span >  --set image.registry=[备份服务器地址]:5000
</span><span ></span></code></pre>
   <p ><span >1.2.2 客户端连接服务端模式（</span><code >server.net_model=2</code><span >）</span></p>
   <p ><span >此模式下，客户端主动连接服务端。执行以下命令：</span></p>
   <pre class="code-fence" ><div class="code-fence-highlighter-copy-button" data-fence-content="aGVsbSBpbnN0YWxsIGJhY2t1cC1zeXN0ZW0gYmFja3VwLXN5c3RlbS9jbGllbnQgLW4gYmFja3VwLXN5c3RlbSAtLWNyZWF0ZS1uYW1lc3BhY2UgXAogIC0taW5zZWN1cmUtc2tpcC10bHMtdmVyaWZ5IFwKICAtLXNldCBzZXJ2ZXIubmV0X21vZGVsPTIgXAogIC0tc2V0IHNlcnZlci5ob3N0PVvlpIfku73mnI3liqHlmajlnLDlnYBdIFwKICAtLXNldCBpbWFnZS5yZXBvPWt1YmUtYWdlbnQgXAogIC0tc2V0IGltYWdlLnJlZ2lzdHJ5PVvlpIfku73mnI3liqHlmajlnLDlnYBdOjUwMDA=">
</div><code ><span ></span><span >helm install backup-system backup-system/client -n backup-system --create-namespace \
</span><span >  --insecure-skip-tls-verify \
</span><span >  --set server.net_model=2 \
</span><span >  --set server.host=[备份服务器地址] \
</span><span >  --set image.repo=kube-agent \
</span><span >  --set image.registry=[备份服务器地址]:5000
</span><span ></span></code></pre>
   <p ><span >注：上述命令以 Docker 镜像仓库在备份服务器上为例，备份服务器已内置 Docker 镜像仓库以供 kubernetes 拉取客户端镜像。</span></p>
   <p ><span >1.2.3 helm 命令安装客户端参数表</span></p>
   <table border="1" style="border-color: #E6E6E6;">
    <thead>
     <tr >
      <th >参数</th>
      <th >说明</th>
      <th >必填</th>
      <th >默认值</th>
     </tr>
    </thead>
    <tbody>
     <tr >
      <td >server.net_model</td>
      <td >服务端主动连接模式(1:服务端连接客户端，2:客户端连接服务端)</td>
      <td >非必填</td>
      <td >1</td>
     </tr>
     <tr class="intellij-row-even" >
      <td >server.host</td>
      <td >客户端连接备份服务器的地址</td>
      <td >非必填</td>
      <td >127.0.0.1</td>
     </tr>
     <tr >
      <td >server.port</td>
      <td >客户端连接备份服务的监听端口</td>
      <td >非必填</td>
      <td >22710</td>
     </tr>
     <tr class="intellij-row-even" >
      <td >server.listen</td>
      <td >服务端连接客户端时，客户端在集群内监听的端口，集群内每个节点都会监听该指定端口，供备份系统服务端连接</td>
      <td >非必填</td>
      <td >23100</td>
     </tr>
     <tr >
      <td >limit.cpu</td>
      <td >客户端CPU限制</td>
      <td >非必填</td>
      <td >0</td>
     </tr>
     <tr class="intellij-row-even" >
      <td >limit.memory</td>
      <td >客户端内存限制</td>
      <td >非必填</td>
      <td >0</td>
     </tr>
     <tr >
      <td >image.registry</td>
      <td >拉取客户端的镜像仓库地址（不指定则使用容器运行时默认镜像仓库，可能会导致客户端镜像拉取失败）</td>
      <td >非必填</td>
      <td ></td>
     </tr>
     <tr class="intellij-row-even" >
      <td >image.repo</td>
      <td >拉取客户端的镜像名称（[VENDOR]部分根据实际拉取位置替换或者去除）</td>
      <td >非必填</td>
      <td >[VENDOR]/kube-agent</td>
     </tr>
     <tr >
      <td >image.tag</td>
      <td >拉取客户端的镜像版本</td>
      <td >非必填</td>
      <td >1.1.0</td>
     </tr>
     <tr class="intellij-row-even" >
      <td >-n backup-system</td>
      <td >安装客户端到指定的命名空间，比如 backup-system</td>
      <td >必填</td>
      <td ></td>
     </tr>
    </tbody>
   </table>
   <p ><span >1.3 注册集群到备份系统</span></p>
   <ul >
    <li >客户端连接服务端模式：客户端启动后会主动连接命令行中配置的备份服务器地址，自动完成集群信息的注册。</li>
    <li >服务端连接客户端模式：需在备份系统管理界面的“添加集群”功能页面中，手动填写主节点的 IP 地址和客户端监听端口，以完成集群的手动添加注册。</li>
   </ul>
   <h4 id="二通过-ssh-远程部署方式安装客户端" >二、通过 SSH 远程部署方式安装客户端</h4>
   <p ><span >前提条件</span></p>
   <ul >
    <li >SSH 用户权限：进行远程连接部署的 SSH 用户必须具有 root 权限才能在目标节点上执行安装命令。</li>
    <li >网络互通：确保客户端和服务端之间网络能够正常通信。</li>
    <li >端口未占用：若使用服务端连接客户端模式，要保证 Kubernetes 集群内所有节点未占用指定的监听端口。</li>
   </ul>
   <p ><span >步骤</span></p>
   <ol >
    <li >在备份系统控制台，依次点击“资源管理” - “基础设施” - “Kubernetes 集群”，进入管理界面，添加 Kubernetes 集群时选择“通过 SSH 远程部署”。</li>
    <li >输入集群节点的 SSH 连接信息，如 IP 地址、用户名、密码。</li>
    <li >备份系统会自动在目标节点上执行安装命令并部署客户端。</li>
   </ol>
   <h4 id="三通过集群连接配置文件kubeconfig方式安装客户端" >三、通过集群连接配置文件（KubeConfig）方式安装客户端</h4>
   <p ><span >前提条件</span></p>
   <ul >
    <li >获取 <code >KubeConfig</code> 文件：确保已获取目标集群的 <code >KubeConfig</code> 文件，该文件包含了连接 Kubernetes 集群所需的认证信息。</li>
    <li >网络访问：确保备份服务器能够访问目标集群的 API 服务器。</li>
   </ul>
   <p ><span >步骤</span></p>
   <ol >
    <li >在备份系统管理界面选择“通过集群连接配置文件方式”。</li>
    <li >上传 <code >KubeConfig</code> 文件（支持 <code >.yaml</code> 或 <code >.txt</code> 格式），也可直接粘贴文件内容到文本输入框。</li>
    <li >备份系统会使用自带的 <code >helm</code> 命令工具连接您的 Kubernetes 集群并远程安装客户端到集群中。</li>
   </ol>
   <p ><span >通常在 Kubernetes 主节点的</span> <code >~/.kube/config</code> <span >文件中可以找到该文件。执行以下命令查看内容：</span></p>
   <pre class="code-fence" ><div class="code-fence-highlighter-copy-button" data-fence-content="Y2F0IH4vLmt1YmUvY29uZmln">
</div><code ><span ></span><span >cat ~/.kube/config 
</span><span ></span></code></pre>
   <p ><span >通常一个完整可用的</span> <code >~/.kube/config</code> <span >文件应该包含如下类似字段：</span></p>
   <pre class="code-fence" ><div class="code-fence-highlighter-copy-button" data-fence-content="YXBpVmVyc2lvbjogdjEKY2x1c3RlcnM6Ci0gY2x1c3RlcjoKICAgIGNlcnRpZmljYXRlLWF1dGhvcml0eS1kYXRhOiAqKioKICAgIHNlcnZlcjogaHR0cHM6Ly8xOTIuMTY4LjEuMTo2NDQzCiAgbmFtZToga3ViZXJuZXRlcwpjb250ZXh0czoKLSBjb250ZXh0OgogICAgY2x1c3Rlcjoga3ViZXJuZXRlcwogICAgdXNlcjoga3ViZXJuZXRlcy1hZG1pbgogIG5hbWU6IGt1YmVybmV0ZXMtYWRtaW5Aa3ViZXJuZXRlcwpjdXJyZW50LWNvbnRleHQ6IGt1YmVybmV0ZXMtYWRtaW5Aa3ViZXJuZXRlcwpraW5kOiBDb25maWcKcHJlZmVyZW5jZXM6IHt9CnVzZXJzOgotIG5hbWU6IGt1YmVybmV0ZXMtYWRtaW4KICB1c2VyOgogICAgY2xpZW50LWNlcnRpZmljYXRlLWRhdGE6ICoqKgogICAgY2xpZW50LWtleS1kYXRhOiAqKio=">
</div><code ><span ></span><span >apiVersion: v1
</span><span >clusters:
</span><span >- cluster:
</span><span >    certificate-authority-data: ***
</span><span >    server: https://192.168.1.1:6443
</span><span >  name: kubernetes
</span><span >contexts:
</span><span >- context:
</span><span >    cluster: kubernetes
</span><span >    user: kubernetes-admin
</span><span >  name: kubernetes-admin@kubernetes
</span><span >current-context: kubernetes-admin@kubernetes
</span><span >kind: Config
</span><span >preferences: {}
</span><span >users:
</span><span >- name: kubernetes-admin
</span><span >  user:
</span><span >    client-certificate-data: ***
</span><span >    client-key-data: ***
</span><span ></span></code></pre>
   <p ><span >在部署时，将使用上述文件中的</span> <code >server</code> <span >字段作为 API 服务器的地址进行连接部署，您可以修改为能实际在备份服务端上能访问的 API 服务器地址。</span></p>
   <p ><span >若未找到该文件，请参考 Kubernetes 官方文档中的步骤进行生成或获取。</span></p>
   <h3 id="检查部署状态" >检查部署状态</h3>
   <p ><span >部署完成后，可通过以下命令检查客户端的状态：</span></p>
   <pre class="code-fence" ><div class="code-fence-highlighter-copy-button" data-fence-content="a3ViZWN0bCBnZXQgcG9kcyAtbiBiYWNrdXAtc3lzdGVt">
</div><code ><span ></span><span >kubectl get pods -n backup-system 
</span><span ></span></code></pre>
   <p ><span >当列出所有 Pod 的状态均为</span> <code >Running</code> <span >时，表明客户端已成功部署并启动。</span></p>
   <p ><span >注：</span><code >-n 命名空间</code> <span >参数传入客户端实际安装到的命名空间值。</span></p>
   <h3 id="卸载客户端" >卸载客户端</h3>
   <p ><span >在使用SSH和KubeConfig方式远程安装客户端的情况下，在备份系统管理界面删除集群时将尝试自动删除客户端。</span></p>
   <p ><span >如果需要手动卸载集群内的客户端，可以执行以下命令：</span></p>
   <pre class="code-fence" ><div class="code-fence-highlighter-copy-button" data-fence-content="aGVsbSB1bmluc3RhbGwgYmFja3VwLXN5c3RlbSAtbiBiYWNrdXAtc3lzdGVt">
</div><code ><span ></span><span >helm uninstall backup-system -n backup-system 
</span><span ></span></code></pre>
   <p ><span >使用以下命令实时监控卸载进度：</span></p>
   <pre class="code-fence" ><div class="code-fence-highlighter-copy-button" data-fence-content="d2F0Y2ggLW4gMSBrdWJlY3RsIGdldCBwb2RzIC1uIGJhY2t1cC1zeXN0ZW0=">
</div><code ><span ></span><span >watch -n 1 kubectl get pods -n backup-system 
</span><span ></span></code></pre>
   <p ><span >当目标命名空间下没有运行的 Pod 时，表示卸载成功。</span></p>
   <h3 id="注意事项" >注意事项</h3>
   <ol >
    <li >网络模式选择
     <ul >
      <li >服务端连接客户端模式：要确保集群内全部客户端监听的端口未被其它程序占用，若存在被占用的情况，可在安装时调整客户端监听端口（默认监听端口为 23100）。</li>
      <li >客户端连接服务端模式：要保证客户端能够正常连接服务端的 IP 和端口，可用 <code >ping</code>, <code >telnet</code> 等命令测试连通性。</li>
     </ul></li>
    <li >权限要求：客户端的安装和卸载操作时需要具有 Kubernetes 集群API访问权限，否则可能会操作失败。</li>
    <li >网络连通性：确保客户端和服务端之间的特定连接方向的网络畅通，避免因网络问题导致客户端注册失败，影响集群的添加和备份恢复功能的使用。</li>
    <li >通过 SSH 远程部署方式安装客户端和通过集群连接配置文件方式安装客户端方式，将直接从备份服务器上拉取内置的 Helm Charts 和 Docker 客户端镜像。</li>
   </ol>
   <p ><span >通过以上步骤，您可以成功将 Kubernetes 集群添加到备份系统中，并确保集群中客户端正常运行。如果在操作过程中遇到问题，请参考相关日志或联系技术支持。</span></p>
  </div>
 </body>
</html>
            
          


        </div>
        <div class="drawer-footer">
            <!--            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit">确 定</button>-->
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default">关 闭</button>
        </div>
    </div>