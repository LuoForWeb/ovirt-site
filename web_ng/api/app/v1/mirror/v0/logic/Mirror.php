<?php

namespace app\v1\mirror\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\ToolOpcode;

class Mirror extends Base
{

    private $toolOpcode;

    public function __construct()
    {
        parent::__construct();
        $this->toolOpcode = new ToolOpcode();
    }



    /**
     * 获取镜像管理
     * @param array $params 数组
     * @return array 镜像管理列表
     */
    public function getMirrorList($params)
    {
        $sortFields = [
            'create_date' => 'create_date',
            'expire_date' => 'expire_date',
            'status' => 'status',
            'os_type' => 'os_type',
            'os_arch' => 'os_arch',
            'purpose' => 'purpose',
            'ip' => 'ip',
        ];
        $offset = ((int)$params['offset']) ?: 0;
        $limit = ((int)$params['limit']) ?: 10;
        $search = $params['search'];
        $sort = $sortFields[$params['sort']] ?? 'create_date';
        $order = $params['order'] ?: 'desc';
        $sql = "select distinct uuid,name,create_date,expire_date,status,os_type,purpose,ip,detail,os_arch from bd_image_info";
        $sqlParams = array();
        $sqlCountParams = array();
        // 按名字搜索
        if ($this->checkEmpty($search)) {
            $sql .= ' where  name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }
        $sqlCount = $sql;
        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql, $sqlParams);
        $countData = $this->dbSelect($sqlCount, $sqlCountParams);
        $count = count($countData);
        $rows = array_map(function ($row) {
            $tmpPath = xphp_get_config('app', 'MIRROR_DOWNLOAD_DIR');
            return [
                'uuid' => $row['uuid'],
                'name' => $row['name'],
                'create_date' => $row['create_date'],
                'expire_date' => $row['expire_date'],
                'status' => $row['status'],
                'os_type' => $row['os_type'],
                'os_arch' => $row['os_arch'],
                'purpose' => $row['purpose'],
                'ip' => $row['ip'],
                'path' => $tmpPath,
                'detail' => $row['detail'] ?: '',
            ];
        }, $data);

        return array(
            'total' => $count,
            'rows' => $rows
        );
    }

    /**
     * 判断空间是否够用
     * @param array $params 数组
     * @return string
     */
    public function sizeMirror($params)
    {

        $opcodeName = 'TOOL_ISO_OP_GET_ISO_DIR_AVAILABLE_SIZE';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);

        $mbResult = $this->service()->sizeMirrorService($params);
        $result = $mbResult['result'];
        if (!$result) {
            $this->muOpResult(false, $operate, $mbResult['msg'], 0, $mbResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_ISO_OP_CREATE_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_ISO_OP_CREATE_SUCCESS'), true, 200);
    }
    /**
     * 添加镜像管理
     * @param array $params 数组
     * @return array
     */
    public function addMirror(array $params): array
    {

        $opcodeName = 'TOOL_ISO_OP_CREATE_ISO';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);

        $mbResult = $this->service()->addMirrorService($params);
        $result = $mbResult['result'];
        if (!$result) {
            $this->muOpResult(false, $operate, $mbResult['msg'], 0, $mbResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_ISO_OP_CREATE_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_ISO_OP_CREATE_SUCCESS'), true, 200);
    }

    /**
     * 批量删除镜像管理
     * @param array $mrriorUuidList
     * @return string
     */
    public function deleteMrriorList(array $mrriorUuidList): array
    {
        $opcodeName = 'TOOL_ISO_OP_DELETE_ISO';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);
        $mbResult = $this->service()->deleteMirrorService($mrriorUuidList);
        $result = $mbResult['result'];
        if (!$result) {
            $this->muOpResult(false, $operate, $mbResult['msg'], 0, $mbResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_ISO_OP_DELETE_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_ISO_OP_DELETE_SUCCESS'), true, 200);
    }

    /**
     * 批量清理镜像管理
     * @param array $mrriorUuidList
     * @return string
     */
    public function clearMrriorList(array $mrriorUuidList): array
    {
        $opcodeName = 'NODE_AGENT_OP_DEL';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);
        //组合消息
        $mbResult = $this->service()->deleteMirrorService($mrriorUuidList);

        $mbResult = $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
        $result = $mbResult['result'];
        if ($result) {
            $this->muOpResult(false, $operate, $mbResult['msg'], 0, $mbResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_ISO_OP_CLEAR_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_ISO_OP_CLEAR_SUCCESS'), true, 200);
    }
}