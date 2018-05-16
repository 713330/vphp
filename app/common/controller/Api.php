<?php
namespace app\common\controller;

use reading\Controller;

class Api extends Controller
{
    /**
     * _initialize
     * @author Kong
     * @version 2017-02-05
     *
     * @return void
     */
    protected function _initialize() 
    {
        
    }

    /**
     * 支付参数
     * @author Kong
     * @version 2018-02-05
     *
     * @return void
     */
    protected function payConfig()
    {
        //微信支付参数配置(appid,商户号,支付秘钥)
        $config = array(
            'appid'      => 'wx7e96d9b14ad0548d',
            'mch_id'  => '1487822602',
            'pay_api_key' => '46c0ce9e81ac2f10acde3f3196d5ae10',
        );
        return $config;
    }
}
