<div class="drawer-content drawer-content-scrollable" role="document">
    <div class="drawer-header help_header">
        <h4 class="drawer-title help_title" id="drawer-1-title">
            <i class="viconfont vicon-bangzhuzhongxin help_icon_helpcenter"></i>Help Center
            <span data-dismiss="drawer" aria-label="Close" class="drawer-close help_icon_close"><i class="viconfont vicon-guanbi"></i></span>
        </h4>

    </div>
    <div class="drawer-body help_body">
        <h4>Add Cluster</h4>
        <hr>
        <p>This document provides detailed instructions on how to add a Kubernetes cluster to the Vinchin backup system. There are three ways to add a cluster, all of which ultimately require installing an agent client on the target host.</p>
        <h1>1. Manual Addition Method</h1>
        <p>Ensure that the Helm client is installed on the cluster's master node.</p>
        <p>Ensure that the backup server's Helm repository address is accessible.</p>
        <hr>
        <h3>1.1 First, add Vinchin's Helm repository and update the repository information:</h3>
        <textarea class="code_msg" disabled style="height: 150px;"><?php echo 'shell
helm repo add vinchin https://backup-server-ip/charts/ --insecure-skip-tls-verify
helm search repo vinchin
helm repo update vinchin' ?></textarea>
        <p>- Replace `https://backup-server-ip/charts/` with the actual backup server Helm repository address</p>

        <h3>1.2 Install Client Agent</h3>
        <p>Select one of the following commands based on the network mode.</p>
        <p>In this mode, the server actively connects to the client. Execute the following command:</p>
        <textarea class="code_msg" disabled style="height: 150px;"><?php echo 'shell
helm install vinchin vinchin/client -n vinchin --create-namespace \
  --set image.pullPolicy=Always \
  --insecure-skip-tls-verify \
  --set server.port=23100 \
  --set server.net_model=1' ?></textarea>
        <p>- `--set server.port=23100`: The port on which the client listens within the cluster for the server to connect.</p>
        <p>- `--set server.net_model=1`: Specifies the network mode as server connecting to client.</p>
        <h3>1.3 Client Connects to Server Mode</h3>
        <p>In this mode, the client actively connects to the server. Execute the following command:</p>
        <textarea class="code_msg" disabled style="height: 150px;"><?php echo 'helm install vinchin vinchin/client -n vinchin --create-namespace \
  --set server.host=backup-server-ip \
  --set server.port=22710 \
  --set image.pullPolicy=Always \
  --insecure-skip-tls-verify \
  --set server.net_model=2' ?></textarea>
        <p>- `--set server.host=backup-server-ip`: The IP address of the server.</p>
        <p>- `--set server.port=22710`: The port on which the server listens for the client to connect.</p>
        <p>- `--set server.net_model=2`: Specifies the network mode as client connecting to server.</p>
        <h3>1.4 Add Cluster Information</h3>
        <p>- If using **Client Connects to Server Mode**, the client agent will automatically register the cluster information after starting.</p>
        <p>- If using **Server Connects to Client Mode**, manually enter the master node's IP address and client listening port in the "Add Cluster" function page of the Vinchin management interface.</p>

        <h1>2. Remote Deployment via SSH</h1>
        <p>Ensure that the SSH user has root privileges.</p>
        <p>Ensure that the client and server networks are interconnected.</p>
        <p>If using server connects to client mode, ensure that no nodes in the cluster are using the specified listening port.</p>
        <hr>
        <h3>2.1 In the Vinchin management interface, select "Remote Deployment via SSH".</h3>
        <h3>2.2 Enter the SSH connection information of the cluster node (such as IP address, username, password, or key).</h3>
        <h3>2.3 Vinchin will automatically execute the installation command on the target node and deploy the client agent.</h3>
        <h3>2.4 After deployment, check the status of the client agent using the following command:</h3>
        <textarea class="code_msg" disabled style="height: 150px;"><?php echo 'shell
kubectl get pods -n vinchin' ?></textarea>
        <p>When all Pods are in the `Running` state, it indicates that the client agent has been successfully deployed and started.</p>

        <h1>3. Remote Deployment via Cluster Connection Configuration File</h1>
        <p>Ensure that you have obtained the `kubeconfig` file of the target cluster.</p>
        <p>Ensure that the backup server can access the API server of the target cluster.</p>
        <hr>
        <h3>3.1 In the Vinchin management interface, select "Remote Deployment via Cluster Connection Configuration File".</h3>
        <h3>3.2 Upload the `kubeconfig` file (supports `.yaml` or `.txt` format), or directly paste the file content.</h3>
        <h3>3.3 Vinchin will use Helm commands to remotely install the client agent.</h3>
        <h3>3.4 The `kubeconfig` file is usually located in the `~/.kube/config` file on the Kubernetes master node. Use the following command to view the content:</h3>
        <textarea class="code_msg" disabled style="height: 150px;"><?php echo 'shell
cat ~/.kube/config' ?></textarea>
        <p>If the `kubeconfig` file is not found, please refer to the Kubernetes official documentation to generate or obtain it.</p>

        <h1>4. Manual Uninstallation of Client Agent</h1>
        <h3>4.1 If you need to uninstall the client agent in the cluster, execute the following command:</h3>
        <textarea class="code_msg" disabled style="height: 150px;"><?php echo 'shell
helm uninstall vinchin -n vinchin' ?></textarea>
        <h3>4.2 Use the following command to monitor the uninstallation progress in real-time:</h3>
        <textarea class="code_msg" disabled style="height: 150px;"><?php echo 'shell
watch -n 1 kubectl get pods -n vinchin' ?></textarea>
        <p>When there are no running Pods in the target namespace, it indicates that the uninstallation is successful.</p>

        <h1>5. Notes</h1>
        <h3>5.1 If you choose **Server Connects to Client Mode**, ensure that the port on which the client listens is not occupied.</h3>
        <h3>5.2 If you choose **Client Connects to Server Mode**, ensure that the client can access the server's IP and port.</h3>
        <h3>5.3 Installation and uninstallation operations require sufficient Kubernetes cluster permissions.</h3>
        <h3>5.4 Ensure that the client and server networks are interconnected to avoid connection failures due to network issues.</h3>
        <h3>5.5 Ensure that the Helm repository address is correct and that the backup server provides stable service.</h3>
        <p>Through the above steps, you can successfully add a Kubernetes cluster to the Vinchin backup system and ensure that the client agent is running normally. If you encounter any issues during the operation, please refer to the relevant logs or contact technical support.</p>

    </div>
    <div class="drawer-footer">
        <!--            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit">Confirm</button>-->
        <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default">Close</button>
    </div>
</div>