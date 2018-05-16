<?php
namespace app\api\controller;

use app\common\controller\Api;

class Index extends Api
{
    /**
     * [index 热门推荐]
     * @return [type] [description]
     */
    public function index()
    {

        $model = model('book');
        $book_list = $model->hotBook('book_cnt');
        foreach ($book_list as $key => $value) {
            $list[] = array(
                    'id'=>$value['id'],
                    'src'=>$value['cover'],
                    'tag'=>'http://ttt.h5.iddi-inc.com/sht/yiyu2/dis_c2.png',
                    'book'=>$value['title'],
                    'desc'=>$value['summary'],
                    'uv'=>$value['read_num'],
                );
        }

        return echo_json($list, 0, '热门文章');
    }

    // 活动专题
    public function subject()
    {

    }
}
