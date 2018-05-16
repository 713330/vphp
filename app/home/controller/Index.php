<?php
    namespace app\home\controller;

    use app\common\controller\Home;
    use reading\Loader;
    use reading\Response;
    use reading\Cache;

    class Index extends Home 
    {
        public function index()
        {
            // $this->assign('title', '这是标题');
            // $this->assign('content', '这是正文');
            return $this->fetch('home/view/index.html');
        }

        public function json()
        {
	    print_r('ssss');exit;
            $user = Loader::model('user');
            $where = new \stdClass;
            $where->id = 1;
            $list = $user->find($where);
            var_dump($list);
            return Response::create('这是json格式返回', 'json');  
        }

        public function jsonp()
        {
            return Response::create('这是jsonp格式返回', 'jsonp');  
        }

        public function redirect()
        {
            return Response::create('http://www.baidu.com', 'redirect');
        }

        public function cache()
        {
            Cache::set('test', '123'); 
        }
    }
?>
