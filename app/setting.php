<?php
namespace app;

/**
 * 系统设置 
 *
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-12-15
 */
class SystemSetting
{
    const MODULE = [
        'type' => 'module',
        'name' => '模块',
    ];

    const AD = [
        'type' => 'ad',
        'name' => '广告'
    ];
}

/**
 * 积分设置 
 *
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-12-15
 */
class PointSetting
{
    const REGISTER = [
        'name'  => '用户注册',
        'value' => 1,
        'score' => 50,
        'type'  => 'earn'
    ];

    const FULLINFO = [
        'name'  => '完善信息',
        'value' => 2,
        'score' => 100,
        'type'  => 'earn'
    ];

    const SHARE = [
        'name'  => '分享',
        'value' => 3,
        'score' => 5,
        'type'  => 'earn',
        'max'   => 50,
    ];

    const INVITE = [
        'name'  => '邀请',
        'value' => 4,
        'score' => 50,
        'type'  => 'earn',
        'max'   => 500,
    ];

    const OTHER = [
        'name'  => '其他',
        'value' => 0,
    ];

    const EXCHANGE = [
        'name'  => '兑换',
        'value' => 100,
        'type'  => 'cost',
    ];
}
