<?php
namespace app\common\controller;

use reading\Loader;
use reading\Controller;
use reading\Response;

class Ad extends Controller
{
    /**
     * _initialize
     * @author Kong
     * @version 2018-01-25
     *
     * @return void
     */
    protected function _initialize()
    {
        $this->permit();
    }

    /**
     * 权限
     * @author Kong
     * @version 2018-01-25
     *
     * @return void
     */
    protected function permit()
    {
        $currentUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        // print_r($currentUrl);
        $url_arr = explode('/', $currentUrl);
        // print_r($url_arr);exit;  // Array ( [0] => [1] => admin [2] => account [3] => add)

        // 角色id
        @$role_id = $_COOKIE['role_id'];  // admin 角色 1

        // 如果退出登录(-1)，并且当前控制器不是login时，跳转
        // 如果当前是login控制器，则不控制，防止死循环（重定向次数过多）
        // if ($role_id == '-1' && $url_arr[2] != 'login') {
        //     $url = "http://" . $_SERVER['SERVER_NAME'] ."/admin/books/show";
        //     header("Location:" . $url);exit;
        // }

        // 超管不控制
        if ($role_id != 1) {

            $allow_menu = array('menu');
            if(!in_array($url_arr[3], $allow_menu)){

                $q = new \stdClass;
                $q->status = array('op'=>'>', 'val'=>-1);
                $q->url = array('op'=>'like', 'val'=> $url_arr[2]);
                $q->order = 'pid, weight';
                $menu = model('menu')->find($q);
                // print_r($menu);die;
                if(!empty($menu)){

                    $q = new \stdClass;
                    $q->menu_id = $menu['id'];
                    $q->role_id = $role_id;
                    $role_menu = model('role_menu')->find($q);

                    if (!empty($role_menu)) {
                        $purviews = json_decode($role_menu['purviews'], true);
                        // print_r($purviews);die;
                        // $purview = $menu['id'];
                        // $purview = $purviews[$menu['id']];
                        if (!in_array($url_arr[3], $purviews['data'])) {
                            echo '没有权限1';exit;
                        }
                    }else{
                        echo '没有权限2';exit;
                        
                    }
                }
            } 
        }

    }
}
