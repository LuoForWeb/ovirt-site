<?php

namespace app\v1\filecopy\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          文件同步 -- 回收站
 * @author       wuxian@vinchin.com
 * @date         2024/7/17 16:15
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class FileCopyRecycleBin extends AuthBase
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取回收站展示数据
     * @param $params 参数
     * @return object 回收站展示数据的任务、agent、时间
     */
    public function getCollectionTask($params = [])
    {
        $result = $this->logic()->getCollectionTask($this->param);
        return $this->success(xphp_get_lang('UI_FILE_COPY_COLLECTION_TASK'), $result);
    }

     /**
     * 获取回收站展示数据的文件目录
     * @param $params 参数
     * @return object 回收站展示数据的文件目录
     */
    public function getCollectionPath($params = [])
    {
        $result = $this->logic()->getCollectionPath($this->param);
        return $this->success(xphp_get_lang('UI_FILE_COPY_COLLECTION_DIR'), $result);
    }

    /**
     * 删除回收站数据(清空回收站也用此接口)
     * @param $params 参数
     * @return object 删除的结果
     */
    public function deleteCollectionData($params = [])
    {
        $result = $this->logic()->deleteCollectionData($this->param);
        return $this->success(xphp_get_lang('UI_FILE_COPY_COLLECTION_DELETE'), $result);
    }

    /**
     * 还原回收站数据
     * @param $params 参数
     * @return object 还原的结果
     */
    public function restoreCollectionData($params = [])
    {
        $result = $this->logic()->restoreCollectionData($this->param);
        if ($result['result']) {
            return $this->success(xphp_get_lang('UI_FILE_COPY_COLLECTION_RESTORE'), $result);
        } else {
            return $this->error(xphp_get_lang('UI_FILE_COPY_COLLECTION_RESTORE'), $result);
        }
    }

}
