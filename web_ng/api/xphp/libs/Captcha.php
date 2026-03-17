<?php
/**
 * 扩展类库-验证码(增强版)
 *
 * 功能特点：
 *     1. 生成并输出验证码图片，同时返回验证码，其存储方式以及校验由应用程序自行决定；
 *     2. 可自定义验证码图片大小，长度，字体及字体大小；
 *     3. 新增扭曲变形、字符粘连、色块干扰、增强噪点等安全效果
 */
namespace xphp;
class Captcha {

    /** @var string $font 字体 */
    private $font = '';

    /** @var int $size 字体大小 */
    private $size = 18;

    /** @var int $width 宽度 */
    private $width = 120;

    /** @var int $height 高度 */
    private $height = 40;

    /** @var int $length 验证码长度 */
    private $length = 6;

    /**
     * 获取验证码
     *
     * @return string
     */
    public function get($config = array()) {
        // 初始化
        if (!$this->init($config)) {
            return false;
        }
        unset($config);

        // 生成验证码（已包含大小写混合）
        $captcha = $this->generate_captcha($this->length);
        if (false === $captcha) {
            return false;
        }

        // 创建画布并设置透明背景
        $img = imagecreatetruecolor($this->width, $this->height);
        imagesavealpha($img, true);
        $bgColor = imagecolorallocatealpha($img, 250, 250, 250, 0);
        imagefill($img, 0, 0, $bgColor);

        // 绘制色块干扰
        $this->drawColorBlocks($img);

        // 增强干扰线（随机粗细、透明度、样式）
        $this->drawEnhancedLines($img);

        // 绘制噪点（密集噪点+雪花噪点）
        $this->drawNoise($img);

        // 绘制扭曲粘连的验证码字符
        $this->drawDistortedText($img, $captcha);

        // 整体画布扭曲效果
        $img = $this->distortCanvas($img);

        // 输出
        header('Content-type: image/png');
        imagepng($img);

        // 销毁
        imagedestroy($img);

        unset($img, $color, $i, $j);

        // 返回（保持大小写混合的原始验证码）
        return $captcha;
    }

    /**
     * 绘制色块干扰
     * @param resource $img 画布资源
     */
    private function drawColorBlocks($img) {
        // 绘制5-8个半透明色块
        for ($i = 0; $i < mt_rand(5, 8); $i++) {
            $color = imagecolorallocatealpha($img,
                mt_rand(180, 240),
                mt_rand(180, 240),
                mt_rand(180, 240),
                mt_rand(80, 120) // 透明度
            );
            $x1 = mt_rand(0, $this->width / 2);
            $y1 = mt_rand(0, $this->height / 2);
            $x2 = $x1 + mt_rand(20, 50);
            $y2 = $y1 + mt_rand(10, 30);
            imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);
        }

        // 绘制圆形色块
        for ($i = 0; $i < 3; $i++) {
            $color = imagecolorallocatealpha($img,
                mt_rand(200, 255),
                mt_rand(200, 255),
                mt_rand(200, 255),
                mt_rand(70, 100)
            );
            $x = mt_rand(10, $this->width - 10);
            $y = mt_rand(10, $this->height - 10);
            imagefilledellipse($img, $x, $y, mt_rand(15, 30), mt_rand(10, 20), $color);
        }
    }

    /**
     * 绘制增强干扰线
     * @param resource $img 画布资源
     */
    private function drawEnhancedLines($img) {
        // 普通干扰线
        for ($i = 0; $i < 15; ++$i) {
            $color = imagecolorallocatealpha($img,
                mt_rand(0, 156),
                mt_rand(0, 156),
                mt_rand(0, 156),
                mt_rand(20, 50)
            );
            // 随机粗细
            $thickness = mt_rand(1, 3);
            for ($t = 0; $t < $thickness; $t++) {
                imageline(
                    $img,
                    mt_rand(-$this->width/2, $this->width*1.5),
                    mt_rand(-$this->height/2, $this->height*1.5),
                    mt_rand(-$this->width/2, $this->width*1.5),
                    mt_rand(-$this->height/2, $this->height*1.5),
                    $color
                );
            }
        }

        // 虚线干扰
        $style = array(
            imagecolorallocate($img, mt_rand(100, 180), mt_rand(100, 180), mt_rand(100, 180)),
            IMG_COLOR_TRANSPARENT,
            imagecolorallocate($img, mt_rand(100, 180), mt_rand(100, 180), mt_rand(100, 180)),
            IMG_COLOR_TRANSPARENT
        );
        imagesetstyle($img, $style);
        for ($i = 0; $i < 3; $i++) {
            imageline(
                $img,
                mt_rand(0, $this->width), mt_rand(0, $this->height),
                mt_rand(0, $this->width), mt_rand(0, $this->height),
                IMG_COLOR_STYLED
            );
        }
    }

    /**
     * 绘制密集噪点
     * @param resource $img 画布资源
     */
    private function drawNoise($img) {
        // 密集像素噪点
        for ($i = 0; $i < 200; $i++) {
            $pointColor = imagecolorallocate($img,
                mt_rand(50, 200),
                mt_rand(50, 200),
                mt_rand(50, 200)
            );
            imagesetpixel($img, mt_rand(0, $this->width), mt_rand(0, $this->height), $pointColor);
        }

        // 雪花噪点
        for ($i = 0; $i < 80; ++$i) {
            $color = imagecolorallocate($img,
                mt_rand(180, 255),
                mt_rand(180, 255),
                mt_rand(180, 255)
            );
            $chars = array('*', '.', '+', 'x', 'o');
            imagestring($img, mt_rand(1, 3),
                mt_rand(0, $this->width),
                mt_rand(0, $this->height),
                $chars[mt_rand(0, 4)],
                $color
            );
        }
    }

    /**
     * 绘制扭曲粘连的验证码字符
     * @param resource $img 画布资源
     * @param string $captcha 验证码字符串
     */
    private function drawDistortedText($img, $captcha) {
        $charWidth = $this->width / $this->length;
        $startX = $charWidth * 0.1;

        // 1. 增强字符间距随机性（更大范围，更容易粘连/重叠）
        $spacing = array();
        $totalSpacing = 0;
        for ($i = 0; $i < $this->length; $i++) {
            // 间距范围缩小：字符宽度的40% ~ 80%（更容易重叠）
            $spacing[] = mt_rand((int)($charWidth * 0.4), (int)($charWidth * 0.8));
            $totalSpacing += $spacing[$i];
        }

        $currentX = $startX;
        for ($i = 0; $i < $this->length; ++$i) {
            $char = $captcha[$i];
            // 2. 颜色更随机（增加灰度干扰，不局限于深色）
            $color = imagecolorallocate($img,
                mt_rand(20, 120),  // 扩大RGB范围，增加颜色干扰
                mt_rand(20, 120),
                mt_rand(20, 120)
            );

            // 3. 字体大小波动更大（增加变形）
            $fontSize = $this->size + mt_rand(-3, 5); // 波动范围从-2/3扩大到-3/5
            $fontSize = max(8, $fontSize); // 防止字体过小导致消失

            // 4. 倾斜角度大幅增加（扭曲更明显）
            $angle = mt_rand(-50, 50); // 从±35°扩大到±50°

            // 5. 增强正弦扭曲幅度 + 增加二次扭曲（双重波动）
            $baseOffset = sin(($currentX / $this->width) * M_PI * 3) * mt_rand(5, 12); // 周期增加到3个，幅度5-12
            $secondOffset = cos(($currentX / $this->width) * M_PI * 5) * mt_rand(2, 6); // 叠加余弦波动
            $yOffset = $baseOffset + $secondOffset; // 双重扭曲叠加
            $yPos = (int)($this->height / 1.4 + $yOffset);

            // 6. X/Y坐标随机偏移增强（更不规则）
            $randomX = mt_rand(-4, 4); // 从±2扩大到±4
            $randomY = mt_rand(-3, 3); // 新增Y轴随机偏移

            // 绘制字符（核心）
            imagettftext(
                $img,
                $fontSize,
                $angle,
                $currentX + $randomX,
                $yPos + $randomY,
                $color,
                $this->font,
                $char
            );

            // 7. 给每个字符叠加轻微的模糊/毛刺干扰（绘制同字符但偏移1px，半透明）
            $shadowColor = imagecolorallocatealpha($img,
                mt_rand(40, 140),
                mt_rand(40, 140),
                mt_rand(40, 140),
                80 // 透明度（0-127，越大越透明）
            );
            imagettftext(
                $img,
                $fontSize,
                $angle + mt_rand(-2, 2), // 阴影轻微偏移角度
                $currentX + $randomX + 1,
                $yPos + $randomY + 1,
                $shadowColor,
                $this->font,
                $char
            );

            $currentX += $spacing[$i];
        }

        // 8. 全局添加扭曲干扰线（进一步混淆字符）
        for ($j = 0; $j < 3; $j++) { // 绘制3条干扰曲线
            $lineColor = imagecolorallocate($img,
                mt_rand(50, 150),
                mt_rand(50, 150),
                mt_rand(50, 150)
            );
            $prevX = 0;
            $prevY = mt_rand(0, $this->height);
            for ($x = 0; $x < $this->width; $x += 2) {
                // 干扰线也用正弦曲线扭曲，贴合字符扭曲风格
                $y = $prevY + sin($x / $this->width * M_PI * 4) * mt_rand(3, 8);
                $y = max(0, min($y, $this->height)); // 限制在画布内
                imageline($img, $prevX, $prevY, $x, $y, $lineColor);
                $prevX = $x;
                $prevY = $y;
            }
        }

        // 9. 添加随机噪点（颗粒感，干扰OCR识别）
        $noiseCount = $this->width * $this->height / 30; // 按画布大小计算噪点数量
        for ($k = 0; $k < $noiseCount; $k++) {
            $noiseColor = imagecolorallocate($img,
                mt_rand(0, 200),
                mt_rand(0, 200),
                mt_rand(0, 200)
            );
            imagesetpixel($img,
                mt_rand(0, $this->width),
                mt_rand(0, $this->height),
                $noiseColor
            );
        }
    }

    /**
     * 整体画布扭曲
     * @param resource $img 原始画布
     * @return resource 扭曲后的画布
     */
    private function distortCanvas($img) {
        $dstImg = imagecreatetruecolor($this->width, $this->height);
        imagesavealpha($dstImg, true);
        $bgColor = imagecolorallocatealpha($dstImg, 250, 250, 250, 0);
        imagefill($dstImg, 0, 0, $bgColor);

        // 波浪扭曲参数
        $amplitude = mt_rand(2, 4);
        $frequency = mt_rand(5, 8) / 100;

        // 像素级扭曲
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                // 水平和垂直双向扭曲
                $offsetX = sin($y * $frequency) * $amplitude;
                $offsetY = cos($x * $frequency) * $amplitude;

                $srcX = $x + $offsetX;
                $srcY = $y + $offsetY;

                // 确保坐标有效
                if ($srcX >= 0 && $srcX < $this->width && $srcY >= 0 && $srcY < $this->height) {
                    $color = imagecolorat($img, $srcX, $srcY);
                    imagesetpixel($dstImg, $x, $y, $color);
                }
            }
        }

        imagedestroy($img);
        return $dstImg;
    }

    /**
     * 初始化
     *
     * @param array $config
     *
     * @return bool
     */
    private function init($config) {
        if (is_array($config)) {
            if (isset($config['font']) && is_file($config['font']) && is_readable($config['font'])) {
                $this->font = $config['font'];
            } else {
                return false;
            }

            if (isset($config['size']) && ($config['size'] = (int) $config['size']) && 0 < $config['size']) {
                $this->size = $config['size'];
            }

            if (isset($config['width']) && ($config['width'] = (int) $config['width']) && 0 < $config['width']) {
                $this->width = $config['width'];
            }

            if (isset($config['height']) && ($config['height'] = (int) $config['height']) && 0 < $config['height']) {
                $this->height = $config['height'];
            }

            if (isset($config['length']) && ($config['length'] = (int) $config['length']) && 0 < $config['length']) {
                $this->length = $config['length'];
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * 生成验证码（大小写混合）
     *
     * @return string
     */
    private function generate_captcha($length = 6) {
        $length = intval($length);
        if (1 > $length || 50 < $length) {
            return false;
        }

        $chars = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'k', 'm',
            'n', 'p', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K', 'M',
            'N', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            '2', '3', '4', '5', '6', '7', '8', '9'
        );

        $keys = array_rand($chars, $length);

        $captcha = '';

        foreach ($keys as $key) {
            $captcha .= $chars[$key];
        }

        unset($length, $chars, $keys, $key);

        return $captcha;
    }

}