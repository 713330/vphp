<?php
use reading\Cache;
use reading\Config;
use reading\Cookie;
use reading\Database;
use reading\Debug;
use reading\exception\HttpException;
use reading\exception\HttpResponseException;
use reading\Loader;
use reading\Log;
use reading\Model;
use reading\Request;
use reading\Response;
use reading\Session;

if (!function_exists('exception')) {
    /**
     * 抛出异常处理
     *
     * @param string    $msg  异常消息
     * @param integer   $code 异常代码 默认为0
     * @param string    $exception 异常类
     *
     * @throws Exception
     */
    function exception($msg, $code = 0, $exception = '')
    {
        $e = $exception ?: '\reading\Exception';
        throw new $e($msg, $code);
    }
}

if (!function_exists('debug')) {
    /**
     * 记录时间（微秒）和内存使用情况
     * @param string            $start 开始标签
     * @param string            $end 结束标签
     * @param integer|string    $dec 小数位 如果是m 表示统计内存占用
     * @return mixed
     */
    function debug($start, $end = '', $dec = 6)
    {
        if ('' == $end) {
            Debug::remark($start);
        } else {
            return 'm' == $dec ? Debug::getRangeMem($start, $end) : Debug::getRangeTime($start, $end, $dec);
        }
    }
}

if (!function_exists('config')) {
    /**
     * 获取和设置配置参数
     * @param string|array  $name 参数名
     * @param mixed         $value 参数值
     * @return mixed
     */
    function config($name = '', $value = null)
    {
        if (is_null($value) && is_string($name)) {
            return Config::get($name);
        } else {
            return Config::set($name, $value);
        }
    }
}

if (!function_exists('input')) {
    /**
     * 获取输入数据 支持默认值和过滤
     * @param string    $key 获取的变量名
     * @param mixed     $default 默认值
     * @param string    $filter 过滤方法
     * @return mixed
     */
    function input($key = '', $default = null, $filter = '')
    {
        if (0 === strpos($key, '?')) {
            $key = substr($key, 1);
            $has = true;
        }
        if ($pos = strpos($key, '.')) {
            // 指定参数来源
            list($method, $key) = explode('.', $key, 2);
            if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'param', 'request', 'session', 'cookie', 'server', 'env', 'path', 'file'])) {
                $key    = $method . '.' . $key;
                $method = 'param';
            }
        } else {
            // 默认为自动判断
            $method = 'param';
        }
        if (isset($has)) {
            return request()->has($key, $method, $default);
        } else {
            return request()->$method($key, $default, $filter);
        }
    }
}

if (!function_exists('model')) {
    /**
     * 实例化Model
     * @param string    $name Model名称
     * @param string    $layer 业务层名称
     * @param bool      $appendSuffix 是否添加类名后缀
     * @return \reading\Model
     */
    function model($name = '', $layer = 'model', $appendSuffix = false)
    {
        return Loader::model($name, $layer, $appendSuffix);
    }
}

if (!function_exists('validate')) {
    /**
     * 实例化验证器
     * @param string    $name 验证器名称
     * @param string    $layer 业务层名称
     * @param bool      $appendSuffix 是否添加类名后缀
     * @return \reading\Validate
     */
    function validate($name = '', $layer = 'validate', $appendSuffix = false)
    {
        return Loader::validate($name, $layer, $appendSuffix);
    }
}

if (!function_exists('db')) {
    /**
     * 实例化数据库类
     * @param array|string  $config 数据库配置参数
     * @param string        $name 操作的数据表名称（不含前缀）
     * @return \reading\db\Query
     */
    function db($config = [], $name = '')
    {
        return Database::connect($config)->name($name);
    }
}

if (!function_exists('controller')) {
    /**
     * 实例化控制器 格式：[模块/]控制器
     * @param string    $name 资源地址
     * @param string    $layer 控制层名称
     * @param bool      $appendSuffix 是否添加类名后缀
     * @return \reading\Controller
     */
    function controller($name, $layer = 'controller', $appendSuffix = false)
    {
        return Loader::controller($name, $layer, $appendSuffix);
    }
}

if (!function_exists('action')) {
    /**
     * 调用模块的操作方法 参数格式 [模块/控制器/]操作
     * @param string        $url 调用地址
     * @param string|array  $vars 调用参数 支持字符串和数组
     * @param string        $layer 要调用的控制层名称
     * @param bool          $appendSuffix 是否添加类名后缀
     * @return mixed
     */
    function action($url, $vars = [], $layer = 'controller', $appendSuffix = false)
    {
        return Loader::action($url, $vars, $layer, $appendSuffix);
    }
}

if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed     $var 变量
     * @param boolean   $echo 是否输出 默认为true 如果为false 则返回输出字符串
     * @param string    $label 标签 默认为空
     * @return void|string
     */
    function dump($var, $echo = true, $label = null)
    {
        return Debug::dump($var, $echo, $label);
    }
}

if (!function_exists('session')) {
    /**
     * Session管理
     * @param string|array  $name session名称，如果为数组表示进行session设置
     * @param mixed         $value session值
     * @param string        $prefix 前缀
     * @return mixed
     */
    function session($name, $value = '', $prefix = null)
    {
        if (is_array($name)) {
            // 初始化
            Session::init($name);
        } elseif (is_null($name)) {
            // 清除
            Session::clear('' === $value ? null : $value);
        } elseif ('' === $value) {
            // 判断或获取
            return 0 === strpos($name, '?') ? Session::has(substr($name, 1), $prefix) : Session::get($name, $prefix);
        } elseif (is_null($value)) {
            // 删除
            return Session::delete($name, $prefix);
        } else {
            // 设置
            return Session::set($name, $value, $prefix);
        }
    }
}

if (!function_exists('cookie')) {
    /**
     * Cookie管理
     * @param string|array  $name cookie名称，如果为数组表示进行cookie设置
     * @param mixed         $value cookie值
     * @param mixed         $option 参数
     * @return mixed
     */
    function cookie($name, $value = '', $option = null)
    {
        if (is_array($name)) {
            // 初始化
            Cookie::init($name);
        } elseif (is_null($name)) {
            // 清除
            Cookie::clear($value);
        } elseif ('' === $value) {
            // 获取
            return 0 === strpos($name, '?') ? Cookie::has(substr($name, 1), $option) : Cookie::get($name, $option);
        } elseif (is_null($value)) {
            // 删除
            return Cookie::delete($name);
        } else {
            // 设置
            return Cookie::set($name, $value, $option);
        }
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存管理
     * @param mixed     $name 缓存名称，如果为数组表示进行缓存设置
     * @param mixed     $value 缓存值
     * @param mixed     $options 缓存参数
     * @return mixed
     */
    function cache($name, $value = '', $options = null)
    {
        if (is_array($options)) {
            // 缓存操作的同时初始化
            Cache::connect($options);
        } elseif (is_array($name)) {
            // 缓存初始化
            return Cache::connect($name);
        }
        if (is_null($name)) {
            return Cache::clear($value);
        } elseif ('' === $value) {
            // 获取缓存
            return 0 === strpos($name, '?') ? Cache::has(substr($name, 1)) : Cache::get($name);
        } elseif (is_null($value)) {
            // 删除缓存
            return Cache::rm($name);
        } elseif (0 === strpos($name, '?') && '' !== $value) {
            $expire = is_numeric($options) ? $options : null;
            return Cache::remember(substr($name, 1), $value, $expire);
        } else {
            // 缓存数据
            if (is_array($options)) {
                $expire = isset($options['expire']) ? $options['expire'] : null; //修复查询缓存无法设置过期时间
            } else {
                $expire = is_numeric($options) ? $options : null; //默认快捷缓存设置过期时间
            }
            return Cache::set($name, $value, $expire);
        }
    }
}

if (!function_exists('request')) {
    /**
     * 获取当前Request对象实例
     * @return Request
     */
    function request()
    {
        return Request::instance();
    }
}

if (!function_exists('echo_json')) {
    /**
     * 获取\reading\response\Json对象实例
     * @param mixed   $data 返回的数据
     * @param int     $errcode 错误码
     * @param string  $errmsg 错误信息
     * @return \reading\response\Json
     */
    function echo_json($data = [], $errcode = 0, $errmsg = '')
    {
        $data = [
            'errcode' => $errcode,
            'errmsg'  => $errmsg,
            'data'    => $data
        ];
        return Response::create($data, 'json');
    }
}

if (!function_exists('echo_jsonp')) {
    /**
     * 获取\reading\response\Jsonp对象实例
     * @param mixed   $data    返回的数据
     * @param integer $code    状态码
     * @param array   $header 头部
     * @param array   $options 参数
     * @return \reading\response\Jsonp
     */
    function echo_jsonp($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'jsonp', $code, $header, $options);
    }
}

if (!function_exists('echo_xml')) {
    /**
     * 获取\reading\response\Xml对象实例
     * @param mixed   $data    返回的数据
     * @param integer $code    状态码
     * @param array   $header  头部
     * @param array   $options 参数
     * @return \reading\response\Xml
     */
    function xml($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'xml', $code, $header, $options);
    }
}

if (!function_exists('redirect')) {
    /**
     * 获取\reading\response\Redirect对象实例
     * @param mixed         $url 重定向地址 支持Url::build方法的地址
     * @param array|integer $params 额外参数
     * @param integer       $code 状态码
     * @param array         $with 隐式传参
     * @return \reading\response\Redirect
     */
    function redirect($url = [], $params = [], $code = 302)
    {
        if (is_integer($params)) {
            $code   = $params;
            $params = [];
        }
        return Response::create($url, 'redirect', $code)->params($params);
    }
}

if (!function_exists('abort')) {
    /**
     * 抛出HTTP异常
     * @param integer|Response      $code 状态码 或者 Response对象实例
     * @param string                $message 错误信息
     * @param array                 $header 参数
     */
    function abort($code, $message = null, $header = [])
    {
        if ($code instanceof Response) {
            throw new HttpResponseException($code);
        } else {
            throw new HttpException($code, $message, null, $header);
        }
    }
}

if (!function_exists('halt')) {
    /**
     * 调试变量并且中断输出
     * @param mixed      $var 调试变量或者信息
     */
    function halt($var)
    {
        dump($var);
        throw new HttpResponseException(new Response);
    }
}

if (!function_exists('token')) {
    /**
     * 生成表单令牌
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    function token($name = '__token__', $type = 'md5')
    {
        $token = Request::instance()->token($name, $type);
        return '<input type="hidden" name="' . $name . '" value="' . $token . '" />';
    }
}

if (!function_exists('sha1_uuid')) {
    /**
     * 用户唯一uuid
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    function sha1_uuid($value) 
    {
        $unique_salt = unique_salt();
        $hash = sha1($unique_salt . $password);
        return $hash;
    }
}

if (!function_exists('redis_connect')) {
    /**
     * 用户登录
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    function redis_connect() 
    {
        //redis
        ini_set('memory_limit', -1);
        set_time_limit(0);
        try{
            $redis = new \Redis();
            $redis->connect('127.0.0.1', '6379');
        } catch (RedisException $e) {
            exit('Redis has going away....');
        }

        return $redis;
    }
}
