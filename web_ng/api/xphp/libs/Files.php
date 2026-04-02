<?php
// phpcs:ignoreFile -- 框架类
/*********************************************************************************
 *  扩展类库-文件上传类
 ***********************************************************************************/

namespace xphp;
use org\Upload;


class Files
{
    /**
     * 上传
     */
    public function upload() {
        /* 返回标准数据 */
        $return = array (
            'code' => 0,
            'info' => 'upload success',
        );

        $setting = config ( 'download_upload' );
        $setting ['callback'] = array (
            $this,
            'isFile'
        );
        $setting ['removeTrash'] = array (
            $this,
            'removeTrash'
        );
        mkdirs ( $setting ['rootPath'] );
        include_once API_PATH . 'extend/org/Upload.php';
        $uploader = new Upload ( $setting );
        $info = $uploader->upload ();
        if (! $info) {
            $return ['code'] = 1;
            $return ['info'] = $uploader->getError ();
        } else {
            foreach ( $info as $k => &$v ) {
                if(isset($v['id']) && is_numeric($v['id'])){
                    continue;
                }
                $v ['mime'] = $v ['type'];
                $v ['fullpath'] = './web_ng/api/data/uploadfile/file/' . $v ['savepath'] . $v ['savename'];;
            }
            if($setting['watermark']){
                // 这里加水印 后续
                //$v ['fullpath'] = get_water($v['fullpath'], $setting['watermark']);
            }
            $return ['info'] = $v['fullpath'];
        }

        return $return;
    }

    /**
     * 检测当前上传的文件是否已经存在
     *
     * @param array $file
     *        	文件上传数组
     * @return boolean 文件信息， false - 不存在该文件
     */
    public function isFile($file) {
        return false;
    }
    /**
     * 清除数据库存在但本地不存在的数据
     *
     * @param
     *        	$data
     */
    public function removeTrash($data) {

    }

}
