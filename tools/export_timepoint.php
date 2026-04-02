<?php 
require_once '../api/load.php';
require_once './PHPExcel.php';
class Tools extends OPHandler{
    /**
     * 导出信息
     */
    public function exportTimepoint(){
        $endTime = $_GET['endtime'];
        $this->paramsCheck($endTime);
        $sql = "select bbt.timepoint_uuid ,bbt.timepoint, bbt.task_uuid, bbt.task_name, bbt.storage_uuid ,
		                  bsr.storage_nickname, bsr.mount_point,  
		                  bn.ip, bn.node_uuid 
                from bd_backup_timepoint bbt, bd_storage_resource bsr,bd_node bn 
                where bbt.storage_uuid = bsr.storage_uuid and bsr.node_uuid = bn.node_uuid and bbt.timepoint < ?";
        $data = $this->dbSelect($sql, array($endTime));
        
        $allInfo = array();
        
        foreach ($data as $d){
            $info = array(
                'timepoint' => $d['timepoint'],
                'timepoint_uuid' => $d['timepoint_uuid'],
                'task_uuid' => $d['task_uuid'],
                'task_name' => $d['task_name'],
                'storage_uuid' => $d['storage_uuid'],
                'storage_nickname' => $d['storage_nickname'],
                'ip' => $d['ip'],
                'node_uuid' => $d['node_uuid'],
                'fullpath' => "/backup_storage/" . $d['storage_uuid'] . "/vm/" . $d['timepoint_uuid'],
            );
            
            $allInfo[] = $info;
        }
        
        $this->exportExcle($allInfo);
//         var_dump($allInfo);
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
        ->setCellValue('A1', '时间点')
        ->setCellValue('B1', '时间点UUID')
        ->setCellValue('C1', '任务名')
        ->setCellValue('D1', '任务UUID')
        ->setCellValue('E1', '存储名')
        ->setCellValue('F1', '存储UUID')
        ->setCellValue('G1', '节点IP')
        ->setCellValue('H1', '节点UUID')
        ->setCellValue('I1', '全路径');
        
        //添加内容
        foreach ($info as $key => $value){
            $num = $key + 2;
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $num, $value['timepoint'])
            ->setCellValue('B' . $num, $value['timepoint_uuid'])
            ->setCellValue('C' . $num, $value['task_name'])
            ->setCellValue('D' . $num, $value['task_uuid'])
            ->setCellValue('E' . $num, $value['storage_nickname'])
            ->setCellValue('F' . $num, $value['storage_uuid'])
            ->setCellValue('G' . $num, $value['ip'])
            ->setCellValue('H' . $num, $value['node_uuid'])
            ->setCellValue('I' . $num, $value['fullpath']);
        }
        
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('时间点');
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="时间点导出' . date("Y-m-d H:i:s") . '.xls"');
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
$tools->exportTimepoint();

?>