<?php
/******************************************* 
** 备份数据管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-06-03 下午15:16:44 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
class DataHandler extends OPHandler{
    /**
     * 给备份时间点添加星标
     * 暂时支持一个
     * @param unknown $params
     */
    public function addStar($params){
        $pointUUID = $params['uuid'];
        $this->paramsCheck($pointUUID);
        $msg = array($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_MAKR';
        $msg = json_encode(array('timepoint_uuids'=> $msg));
        return $this->opUnifyMsg($opName, $msg);
    }
    
    /**
     * 备份时间点取消星标
     * 暂时支持一个
     * @param unknown $params
     */
    public function deleteStar($params){
        $pointUUID = $params['uuid'];
        $this->paramsCheck($pointUUID);
        $msg = array($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_UNMARK';
        $msg = json_encode(array('timepoint_uuids'=> $msg));
        return $this->opUnifyMsg($opName, $msg);
    }
    
    public function remarkTimepoint($params){
        $pointUUID = $params['uuid'];
        $remark = $params['remark'];
     	$this->paramsCheck($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_COMMENT';
        $msg = json_encode(array('timepoint_uuid'=> $pointUUID, 'remarks' => $remark));
        return $this->opUnifyMsg($opName, $msg);
    }
    
    /**
     * 统一处理消息
     * @param string $opName    操作名
     * @param json $msg         JSON消息
     * @return array
     */
    private function opUnifyMsg($opName, $msg){
        $mbResult = $this->mbPFMsg($opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
}
?>