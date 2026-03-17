<?php


namespace app\v2\system\v0\controller;

use app\v2\common\controller\Base;

/**
 * note          menu desc
 * @author       wanggongxi@vinchin.com
 * @date         2026/1/28 18:55
 * @version      1.0.0
 * @copyright    Copyright 2026 vinchin.com
 */
class Menu extends Base
{

    /**
    * 获取菜单列表
     * @return string
     */
    public function getMenuList()
    {
        $return = $this->logic()->getMenuList($this->param);
        return $this->success('', $return);
    }

    /**
     * 获取系统首页url
     * @return string
     */
    public function getHomePage()
    {
        $return = $this->logic()->getHomePage($this->param);
        return $this->success('', $return);
    }

    /**
     * export菜单列表
     * @return string
     */
    public function downMenuList()
    {
        return $this->logic()->downMenuList();
    }

}