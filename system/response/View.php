<?php
// +----------------------------------------------------------------------
// | iddiPHP [ WE CAN DO IT JUST iddi ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://iddiphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace iddi\response;

use iddi\Config;
use iddi\Response;
use iddi\View as ViewTemplate;

class View extends Response
{
    // 输出参数
    protected $contentType = 'text/html';

    /**
     * 处理数据
     * @access protected
     * @param mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($data)
    {
        // 渲染模板输出
        return ViewTemplate::instance(Config::get('template'))->fetch($data);
    }
}
