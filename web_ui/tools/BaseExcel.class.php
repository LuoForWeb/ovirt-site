<?php

require_once ROOT_PATH . 'tools/PHPExcel.php';

class BaseExcel
{
    /**
     * 导出数据
     * @param string $title 表格的名称
     * @param array $header 表头,[name => 姓名, ip => IP地址, create_time => 创建时间]
     * @param array $data 表的数据[[name => JackC, ip => 192.168.1.1, create_time => 2023-08-23 11:23:01]]
     * @param array $relation 列与数据的关系，即各个列的位置和宽度[name => [col_name => A, width => 20]]
     * @return void
     * @throws Exception
     */
    public function export(string $title, array $header, array $data, array $relation)
    {
        $objPHPExcel = new PHPExcel();

        // 设置表格的属性
        $objPHPExcel->getProperties()
            ->setCreator("vinchin")
            ->setLastModifiedBy("vinchin")
            ->setTitle("Office 2007 XLSX Document")
            ->setSubject("Office 2007 XLSX Document")
            ->setDescription("Office 2007 XLSX")
            ->setKeywords("office 2007")
            ->setCategory("");

        // 设置活跃的表格，设为第一个
        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
        // 表格的文件名
        $objPHPExcel->getActiveSheet()->setTitle($title);
        // 设置表头
        foreach ($relation as $key => $colInfo) {
            $activeSheet->setCellValue($colInfo['col_name'] . '1', $header[$key]);
            // 表头加粗
            $activeSheet->getStyle($colInfo['col_name'] . '1')->getFont()->setBold(true);
            // 设置列宽度
            $activeSheet->getColumnDimension($colInfo['col_name'])->setWidth($colInfo['width']);
        }

        // 添加内容
        foreach ($data as $index => $row) {
            $rowIndex = $index + 2;
            foreach ($relation as $key => $colInfo) {
                $activeSheet->setCellValue($colInfo['col_name'] . $rowIndex, $row[$key]);
            }
        }

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $title . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}

return new BaseExcel();