<?php

namespace app\admin\controller;

use app\common\controller\Admin;
use reading\Cache;
use reading\Response\Json;
use util\Page;

class Index extends Admin
{
    /**
     * _initialize
     * @author Kong
     * @version 2017-12-05
     *
     * @return void
     */
    protected function _initialize() 
    {
        parent::_initialize();
    }


    /**
     * 页面
     * 
     * @author Kong
     * @version 2018-01-23
     *
     * @param 
     * @return void
     */
    public function show()
    {
        return $this->fetch('admin/view/index.html');
    }

}