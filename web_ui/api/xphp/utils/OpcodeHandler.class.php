<?php
class OpcodeHandler{
    //opcode level 子类初始化
    protected $opcodeLevel;
    //opcode type 子类初始化
    protected $opcodeType;
    //opcode 子类初始化
    protected $opcode;
    //操作描述   子类初始化
    protected $opDes;
    
    /**
     * 根据操作名字(和后台操作名一一对应)获得opcodeInfo   socket消息包头使用
     * @param string $opName
     * @return Ambigous <multitype:, multitype:mixed NULL >
     */
    public function getOpcodeInfo($opName){
        $opcodeInfo = array();
        foreach ($this->opcode as $optype){
            if(array_search($opName, $optype)){
                $opcodeInfo = array(
                    "level" => $this->opcodeLevel,
                    "type" => array_search(array_search($optype, $this->opcode), $this->opcodeType),
                    "code" => array_search($opName, $optype)
                );
                break;
            }
        }
        return $opcodeInfo;
    }
    
    /**
     * 根据操作名字得到操作描述
     * @param string $opName
     * @return string
     */
    public function getOpcodeDes($opName){
        $opcodeDes = Xphp::$_lang['WEB_PUBLIC_OPRATION_UNKNOWN'];
        if(array_key_exists($opName, $this->opDes)){
            $opcodeDes = $this->opDes[$opName];
        }
        return $opcodeDes;
    }
}

?>