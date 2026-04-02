<?php 
require_once '../api/load.php';
require_once './PHPExcel.php';
class Tools extends OPHandler{
    /**
     * 导出信息
     */
    public function exportVM(){
        $sql = "select task_uuid, task_name, strategy_id, create_time, user_uuid, node_uuid, storage_uuid
        from bd_task where module_type = 2 and task_type = 1 and delete_flag = 2";
        $data = $this->dbSelect($sql, array());
        
        $allInfo = array();
        
        foreach ($data as $d){
            $vminfo = $this->getTaskAllVMInfo($d['task_uuid']);
            $username = $this->getUsername($d['user_uuid']);
            $nodename = $this->getNodename($d['node_uuid']);
            $storageinfo = $this->getStoragename($d['storage_uuid']);
            $timeStrategy = $this->getStrategyInfo($d['strategy_id']);
            $reservedStrategy = $this->getReservedInfo($d['strategy_id']);
            foreach ($vminfo as $vm){
                $info = array(
                    'task_name' => $d['task_name'],
                    'vm_name' => $vm['vm_name'],
                    'dir_path' => $vm['dir_path'],
                    'nodename' => $nodename,
                    'storagename' => $storageinfo['name'],
                    'storagetotal' => $storageinfo['total'],
                    'storageuse' => $storageinfo['use'],
                    'storagefree' => $storageinfo['free'],
                    'full' => $timeStrategy['full'],
                    'inc' => $timeStrategy['inc'],
                    'diff' => $timeStrategy['diff'],
                    'reserved' => $reservedStrategy,
                    'username' => $username,
                    'create_time' => $d['create_time'],
                );
                
                $allInfo[] = $info;
            }
        }
        
        $this->exportExcle($allInfo);
//         var_dump($allInfo);
    }
    
    /**
     * 得到任务的所有虚拟机信息
     * @param unknown $taskuuid
     */
    private function getTaskAllVMInfo($taskuuid){
        $sql = "select vcenter_uuid, vm_uuid, vm_name, dir_path from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        return $data;
    }
    
    /**
     * 获取用户名
     * @param unknown $useruuid
     */
    private function getUsername($useruuid){
        $sql = "select user_name from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        return $data[0]['user_name'];
    }
    
    /**
     * 获取节点名
     * @param unknown $nodeuuid
     */
    private function getNodename($nodeuuid){
        $sql = "select host_name, node_nickname, ip from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $name = $data[0]['host_name'] . "(" . $data[0]['ip'] . ")";
        if(!empty($data[0]['node_nickname'])){
            $name = $data[0]['node_nickname'] . "(" . $data[0]['ip'] . ")";
        }
        return $name;
    }
    
    /**
     * 获取存储名
     * @param unknown $storageuuid
     */
    private function getStoragename($storageuuid){
        $sql = "select storage_nickname, total_size, free_size from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $utils = Xphp::instance('Utils');
        $info = array(
            'name' => $data[0]['storage_nickname'],
            'total' => $utils->calSize($data[0]['total_size']),
            'use' => $utils->calSize($data[0]['total_size'] - $data[0]['free_size']),
            'free' => $utils->calSize($data[0]['free_size']),
        );
        return $info;
    }
    
    /**
     * 获取时间策略信息
     */
    private function getStrategyInfo($strategyid){
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, 
                roll_end_time from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info = array(
            'full' => '',
            'inc' => '',
            'diff' => ''
        );
        foreach ($data as $d){
            $des = $this->getStrategyType($d['strategy_type']) . $this->getStrategyDays($d['days']) . $this->getStrategyInterval($d);
            if($d['mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                $info['full'] = $des;
            }
            if($d['mode'] == Xphp::$_config['BACKUP_MODE']['INCREMENTAL']){
                $info['inc'] = $des;
            }
            if($d['mode'] == Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']){
                $info['diff'] = $des;
            }
        }
        return $info;
    }
    
    /**
     * 获取保留策略信息
     */
    private function getReservedInfo($strategyid){
        $sql = "select strategy_type, number from bd_reserved_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        if($data[0]['strategy_type'] == "2"){
            $des = "按天数保留,保留值为 " . $data[0]['number'];
        }else{
            $des = "按个数保留,保留值为 " . $data[0]['number'];
        }
        return $des;
    }
    
    /**
     * 得到滚动信息
     * @param unknown $d
     */
    private function getStrategyInterval($d){
        $des = $d['start_time'] . "开始,";
        if($d['roll_flag'] == "2"){
            $des .= "不滚动";
        }else{
            $des .= "滚动间隔" . $d['roll_interval'];
            $des .= ",结束时间" . $d['roll_end_time'];
        }
        return $des;
    }
    
    /**
     * 得到策略的类型
     * @param unknown $type
     */
    private function getStrategyType($type){
        $des = "";
        $type = intval($type);
        switch ($type){
            case Xphp::$_config['STRATEGY_TYPE']['EVERY_DAY']:
                $des = "每天";
                break;
            case Xphp::$_config['STRATEGY_TYPE']['EVERY_WEEK']:
                $des = "每周";
                break;
            case Xphp::$_config['STRATEGY_TYPE']['EVERY_MONTH']:
                $des = "每月";
                break;
            case Xphp::$_config['STRATEGY_TYPE']['ONCE']:
                $des = "一次性";
                break;
        }
        return $des;
    }
    
    /**
     * 得到策略的天
     * @param unknown $days
     * @return string
     */
    private function getStrategyDays($days){
        $desDays = '';
        foreach ($days as $key => $value){
            if(1 == $value){
                $desDays .= ($key + 1) . ", ";
            }
        }
        return $desDays;
    }
    
    /**
     * 导出到excel
     * @param unknown $info
     */
    private function exportExcle($info){
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("vinchin")
        ->setLastModifiedBy("vinchin")
        ->setTitle("Office 2007 XLSX Document")
        ->setSubject("Office 2007 XLSX Document")
        ->setDescription("Office 2007 XLSX")
        ->setKeywords("office 2007")
        ->setCategory("");
        
        //设置表头
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '任务名')
        ->setCellValue('B1', '虚拟机名')
        ->setCellValue('C1', '虚拟机路径')
        ->setCellValue('D1', '备份节点')
        ->setCellValue('E1', '备份存储')
        ->setCellValue('F1', '存储总容量')
        ->setCellValue('G1', '存储使用量')
        ->setCellValue('H1', '存储可用容量')
        ->setCellValue('I1', '完全策略')
        ->setCellValue('J1', '增量策略')
        ->setCellValue('K1', '差异策略')
        ->setCellValue('L1', '保留策略')
        ->setCellValue('M1', '用户')
        ->setCellValue('N1', '创建/修改时间');
        
        //添加内容
        foreach ($info as $key => $value){
            $num = $key + 2;
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, $value['task_name'])
            ->setCellValue('B' . $num, $value['vm_name'])
            ->setCellValue('C' . $num, $value['dir_path'])
            ->setCellValue('D' . $num, $value['nodename'])
            ->setCellValue('E' . $num, $value['storagename'])
            ->setCellValue('F' . $num, $value['storagetotal'])
            ->setCellValue('G' . $num, $value['storageuse'])
            ->setCellValue('H' . $num, $value['storagefree'])
            ->setCellValue('I' . $num, $value['full'])
            ->setCellValue('J' . $num, $value['inc'])
            ->setCellValue('K' . $num, $value['diff'])
            ->setCellValue('L' . $num, $value['reserved'])
            ->setCellValue('M' . $num, $value['username'])
            ->setCellValue('N' . $num, $value['create_time']);
        }
        
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('已备份虚拟机');
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="任务导出' . date("Y-m-d H:i:s") . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
}
$tools = new Tools();
$tools->exportVM();

?>