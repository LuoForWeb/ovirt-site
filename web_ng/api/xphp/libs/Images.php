<?php

// phpcs:ignoreFile -- 框架类
/*********************************************************************************
 *  扩展类库-图片处理类
 ***********************************************************************************/

namespace xphp;
use org\Image;

class Images
{

    private $class;
    /**
    * 初始化
     */
    public function __construct($type = 'Gd')
    {
        include_once API_PATH . 'extend/org/Image.php';
        $this->class = (new Image())->init($type);
    }

    /**
    * 添加水印
     * @param string $url 图片所在位置（绝对路径）
     * @param string $source 水印（图片或者文字）
     * @param string $new_url 文件保存路径（绝对路径）
     * @param integer $type 水印类型 1文字 2图片
     * @param int $size 字体大小（文字水印）
     * @param string $color 颜色
     * @param integer $offset 偏移量
     * @param integer $angle 旋转度
     * @param string $font 字体
     * @param string $position 位置
            define('THINKIMAGE_WATER_NORTHWEST', 1); //常量，标识左上角水印
            define('THINKIMAGE_WATER_NORTH', 2); //常量，标识上居中水印
            define('THINKIMAGE_WATER_NORTHEAST', 3); //常量，标识右上角水印
            define('THINKIMAGE_WATER_WEST', 4); //常量，标识左居中水印
            define('THINKIMAGE_WATER_CENTER', 5); //常量，标识居中水印
            define('THINKIMAGE_WATER_EAST', 6); //常量，标识右居中水印
            define('THINKIMAGE_WATER_SOUTHWEST', 7); //常量，标识左下角水印
            define('THINKIMAGE_WATER_SOUTH', 8); //常量，标识下居中水印
            define('THINKIMAGE_WATER_SOUTHEAST', 9); //常量，标识右下角水印
     * @return string
     */
    public function water
    (
        string $url,
        string $source,
        string $new_url,
        $type = 1,
        $size = 16,
        $color = '#00000000',
        $offset = 0,
        $angle = 45,
        $font = API_PATH . 'extend/org/image/fonts/MSYH.TTC',
        $position = THINKIMAGE_WATER_CENTER
    ){
        if ($type == 1) {
            // 文字水印
           return $this->class
               ->open($url)
               ->text($source, $font, $size, $color, $position, $offset, $angle)
               ->save($new_url);
        }
        // 图片水印
        return $this->class
            ->open($url)
            ->water($source, $position)
            ->save($new_url);
    }

    /**
     * 生成缩略图
     * @param string $url 图片所在位置（绝对路径）
     * @param string $new_url 文件保存路径（绝对路径）
     * @param int $width 宽度
     * @param int $height 高度
     * @param int $type 类型
        define('THINKIMAGE_THUMB_SCALING', 1); //常量，标识缩略图等比例缩放类型
        define('THINKIMAGE_THUMB_FILLED', 2); //常量，标识缩略图缩放后填充类型
        define('THINKIMAGE_THUMB_CENTER', 3); //常量，标识缩略图居中裁剪类型
        define('THINKIMAGE_THUMB_NORTHWEST', 4); //常量，标识缩略图左上角裁剪类型
        define('THINKIMAGE_THUMB_SOUTHEAST', 5); //常量，标识缩略图右下角裁剪类型
        define('THINKIMAGE_THUMB_FIXED', 6); //常量，标识缩略图固定尺寸缩放类型
     * @return mixed
     */
    public function thumb(string $url, string $new_url, int $width, int $height, $type = THINKIMAGE_THUMB_SCALING)
    {
        return $this->class
            ->open($url)
            ->thumb($width, $height, $type)
            ->save($new_url);
    }

    /**
     * 裁剪图片
     * @param string $url 图片所在位置（绝对路径）
     * @param string $new_url 文件保存路径（绝对路径）
     * @param int $x 裁剪区域x坐标
     * @param int $y 裁剪区域y坐标
     * @param null $width 图像保存宽度
     * @param null $height 图像保存高度
     * @return mixed
     */
    public function crop(string $url, string $new_url, $x = 0, $y = 0, $width = null, $height = null)
    {
        $soucre = $this->class->open($url);

        return $soucre->crop($soucre->width(), $soucre->height(), $x, $y, $width, $height)->save($new_url);
    }

}
