<?php
namespace reading;

use reading\exception\HttpException;
use reading\exception\HttpResponseException;
use reading\exception\RouteNotFoundException;

/**
 * App 应用管理
 *
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-12
 */
class App
{
    /**
     * @var bool 是否初始化过
     */
    protected static $init = false;

    /**
     * @var string 当前模块路径
     */
    public static $modulePath;

    /**
     * @var bool 应用调试模式
     */
    public static $debug = true;

    /**
     * @var string 应用类库命名空间
     */
    public static $namespace = 'app';

    /**
     * @var bool 应用类库后缀
     */
    public static $suffix = false;

    protected static $dispatch;
    protected static $file = [];

    /**
     * 执行应用程序
     * @access public
     * @param Request $request Request对象
     * @return Response
     * @throws Exception
     */
    public static function run(Request $request = null)
    {
        is_null($request) && $request = Request::instance();

        try {
            $config = self::initCommon();

            $request->filter($config['default_filter']);

            // 获取应用调度信息
            $dispatch = self::$dispatch;
            if (empty($dispatch)) {
                // 进行URL路由检测
                $dispatch = self::routeCheck($request, $config);
            }
            // 记录当前调度信息
            $request->dispatch($dispatch);

            // 记录路由和请求信息
            if (self::$debug) {
                Log::record('[ ROUTE ] ' . var_export($dispatch, true), 'info');
                Log::record('[ HEADER ] ' . var_export($request->header(), true), 'info');
                Log::record('[ PARAM ] ' . var_export($request->param(), true), 'info');
            }

            switch ($dispatch['type']) {
                case 'redirect':
                    // 执行重定向跳转
                    $data = Response::create($dispatch['url'], 'redirect')->code($dispatch['status']);
                    break;
                case 'module':
                    // 模块/控制器/操作
                    $data = self::module($dispatch['module'], $config);
                    break;
                case 'controller':
                    // 执行控制器操作
                    $vars = array_merge(Request::instance()->param(), $dispatch['var']);
                    $data = Loader::action($dispatch['controller'], $vars, $config['url_controller_layer'], $config['controller_suffix']);
                    break;
                case 'method':
                    // 执行回调方法
                    $vars = array_merge(Request::instance()->param(), $dispatch['var']);
                    $data = self::invokeMethod($dispatch['method'], $vars);
                    break;
                case 'response':
                    $data = $dispatch['response'];
                    break;
                default:
                    throw new \InvalidArgumentException('dispatch type not support');
            }
        } catch (HttpResponseException $exception) {
            $data = $exception->getResponse();
        }
        
        // 清空类的实例化
        Loader::clearInstance();

        // 输出数据到客户端
        if ($data instanceof Response) {
            $response = $data;
        } elseif (!is_null($data)) {
            // 默认自动识别响应输出类型
            $isAjax   = $request->isAjax();
            $type     = $isAjax ? Config::get('default_ajax_return') : Config::get('default_return_type');
            $response = Response::create($data, $type);
        } else {
            $response = Response::create();
        }

        return $response;
    }

    /**
     * 设置当前请求的调度信息
     * @access public
     * @param array|string  $dispatch 调度信息
     * @param string        $type 调度类型
     * @return void
     */
    public static function dispatch($dispatch, $type = 'module')
    {
        self::$dispatch = ['type' => $type, $type => $dispatch];
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     * @access public
     * @param string|array|\Closure $function 函数或者闭包
     * @param array                 $vars     变量
     * @return mixed
     */
    public static function invokeFunction($function, $vars = [])
    {
        $reflect = new \ReflectionFunction($function);
        $args    = self::bindParams($reflect, $vars);
        // 记录执行信息
        self::$debug && Log::record('[ RUN ] ' . $reflect->__toString(), 'info');
        return $reflect->invokeArgs($args);
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param string|array $method 方法
     * @param array        $vars   变量
     * @return mixed
     */
    public static function invokeMethod($method, $vars = [])
    {
        if (is_array($method)) {
            $class   = is_object($method[0]) ? $method[0] : self::invokeClass($method[0]);
            $reflect = new \ReflectionMethod($class, $method[1]);
        } else {
            // 静态方法
            $reflect = new \ReflectionMethod($method);
        }
        $args = self::bindParams($reflect, $vars);

        self::$debug && Log::record('[ RUN ] ' . $reflect->class . '->' . $reflect->name . '[ ' . $reflect->getFileName() . ' ]', 'info');
        return $reflect->invokeArgs(isset($class) ? $class : null, $args);
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     * @access public
     * @param string    $class 类名
     * @param array     $vars  变量
     * @return mixed
     */
    public static function invokeClass($class, $vars = [])
    {
        $reflect     = new \ReflectionClass($class);
        $constructor = $reflect->getConstructor();
        if ($constructor) {
            $args = self::bindParams($constructor, $vars);
        } else {
            $args = [];
        }
        return $reflect->newInstanceArgs($args);
    }

    /**
     * 绑定参数
     * @access public
     * @param \ReflectionMethod|\ReflectionFunction $reflect 反射类
     * @param array                                 $vars    变量
     * @return array
     */
    private static function bindParams($reflect, $vars = [])
    {
        if (empty($vars)) {
            $vars = Request::instance()->param();
        }
        $args = [];
        // 判断数组类型 数字数组时按顺序绑定参数
        reset($vars);
        $type = key($vars) === 0 ? 1 : 0;
        if ($reflect->getNumberOfParameters() > 0) {
            $params = $reflect->getParameters();
            foreach ($params as $param) {
                $name  = $param->getName();
                $class = $param->getClass();
                if ($class) {
                    $className = $class->getName();
                    $bind      = Request::instance()->$name;
                    if ($bind instanceof $className) {
                        $args[] = $bind;
                    } else {
                        if (method_exists($className, 'invoke')) {
                            $method = new \ReflectionMethod($className, 'invoke');
                            if ($method->isPublic() && $method->isStatic()) {
                                $args[] = $className::invoke(Request::instance());
                                continue;
                            }
                        }
                        $args[] = method_exists($className, 'instance') ? $className::instance() : new $className;
                    }
                } elseif (1 == $type && !empty($vars)) {
                    $args[] = array_shift($vars);
                } elseif (0 == $type && isset($vars[$name])) {
                    $args[] = $vars[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    throw new \InvalidArgumentException('method param miss:' . $name);
                }
            }
        }
        return $args;
    }

    /**
     * 执行模块
     * @access public
     * @param array $result 模块/控制器/操作
     * @param array $config 配置参数
     * @param bool  $convert 是否自动转换控制器和操作名
     * @return mixed
     */
    public static function module($result, $config)
    {
        if (is_string($result)) {
            $result = explode('/', $result);
        }
        $request = Request::instance();
        if ($config['app_multi_module']) {
            // 多模块部署
            $module    = strip_tags(strtolower($result[0] ?: $config['default_module']));
            $available = false;
            if (!in_array($module, $config['deny_module_list']) && is_dir(APP_ROOT . $module)) {
                $available = true;
            }

            // 模块初始化
            if ($module && $available) {
                // 初始化模块
                $request->module($module);
                $config = self::init($module);
            } else {
                throw new HttpException(404, 'module not exists:' . $module);
            }
        } else {
            // 单一模块部署
            $module = '';
            $request->module($module);
        }
        // 当前模块路径
        App::$modulePath = APP_ROOT . ($module ? $module . DS : '');

        // 获取控制器名
        $controller = strip_tags($result[1] ?: $config['default_controller']);
        $controller = strtolower($controller);

        // 获取操作名
        $actionName = strip_tags($result[2] ?: $config['default_action']);
        $actionName = strtolower($actionName);

        // 设置当前请求的控制器、操作
        $request->controller(ucfirst($controller))->action($actionName);

        $instance = Loader::controller($controller, $config['url_controller_layer'], $config['controller_suffix'], $config['empty_controller']);
        if (is_null($instance)) {
            throw new HttpException(404, 'controller not exists:' . ucfirst($controller));
        }
        // 获取当前操作名
        $action = $actionName . $config['action_suffix'];

        $vars = [];
        if (is_callable([$instance, $action])) {
            // 执行操作方法
            $call = [$instance, $action];
        } elseif (is_callable([$instance, '_empty'])) {
            // 空操作
            $call = [$instance, '_empty'];
            $vars = [$actionName];
        } else {
            // 操作不存在
            throw new HttpException(404, 'method not exists:' . get_class($instance) . '->' . $action . '()');
        }

        return self::invokeMethod($call, $vars);
    }

    /**
     * 初始化应用
     */
    public static function initCommon()
    {
        if (empty(self::$init)) {
            // 初始化应用
            $config       = self::init();
            self::$suffix = $config['class_suffix'];

            // 应用调试模式
            self::$debug = defined('APP_DEBUG') ? APP_DEBUG : Config::get('app_debug');
            if (!self::$debug) {
                ini_set('display_errors', 'Off');
            } elseif (!IS_CLI) {
                //重新申请一块比较大的buffer
                if (ob_get_level() > 0) {
                    $output = ob_get_clean();
                }
                ob_start();
                if (!empty($output)) {
                    echo $output;
                }
            }

            // 注册应用命名空间
            self::$namespace = $config['app_namespace'];
            Loader::addNamespace($config['app_namespace'], APP_ROOT);

            // 加载额外文件
            if (!empty($config['extra_file_list'])) {
                foreach ($config['extra_file_list'] as $file) {
                    $file = strpos($file, '.') ? $file : APP_ROOT . $file . EXT;
                    if (is_file($file) && !isset(self::$file[$file])) {
                        include $file;
                        self::$file[$file] = true;
                    }
                }
            }

            // 设置系统时区
            date_default_timezone_set($config['default_timezone']);

            self::$init = true;
        }
        return Config::all();
    }

    /**
     * 初始化应用或模块
     * @access public
     * @param string $module 模块名
     * @return array
     */
    private static function init($module = '')
    {
        // 定位模块目录
        $module = $module ? $module . DS : '';

        // 加载初始化文件
        if (is_file(APP_ROOT . $module . 'init' . EXT)) {
            include APP_ROOT . $module . 'init' . EXT;
        } else {
            $path = APP_ROOT . $module;
            // 加载模块配置
            $config = Config::load(CONF_ROOT . $module . 'config' . CONF_EXT);
            // 读取数据库配置文件
            $filename = CONF_ROOT . $module . 'database' . CONF_EXT;
            Config::load($filename, 'database');
            // 读取扩展配置文件
            if (is_dir(CONF_ROOT . $module . 'extra')) {
                $dir   = CONF_ROOT . $module . 'extra';
                $files = scandir($dir);
                foreach ($files as $file) {
                    if (strpos($file, CONF_EXT)) {
                        $filename = $dir . DS . $file;
                        Config::load($filename);
                    }
                }
            }

            // 加载公共文件
            if (is_file($path . 'common' . EXT)) {
                include $path . 'common' . EXT;
            }

        }
        return Config::all();
    }

    /**
     * URL路由检测（根据PATH_INFO)
     * @access public
     * @param  \think\Request $request
     * @param  array          $config
     * @return array
     * @throws \think\Exception
     */
    public static function routeCheck($request, array $config)
    {
        $path   = $request->path();
        $depr   = $config['pathinfo_depr'];
        // 路由无效 解析模块/控制器/操作/参数... 
        $result = self::parseUrl($path, $depr);
        return $result;
    }

    /**
     * 解析模块的URL地址 [模块/控制器/操作?]参数1=值1&参数2=值2...
     * @access public
     * @param string    $url URL地址
     * @param string    $depr URL分隔符
     * @return array
     */
    public static function parseUrl($url, $depr = '/')
    {
        $url              = str_replace($depr, '|', $url);
        list($path, $var) = self::parseUrlPath($url);
        $route            = [null, null, null];
        if (isset($path)) {
            // 解析模块
            $module = Config::get('app_multi_module') ? array_shift($path) : null;
            // 解析控制器
            $controller = !empty($path) ? array_shift($path) : null;
            // 解析操作
            $action = !empty($path) ? array_shift($path) : null;
            // 解析额外参数
            self::parseUrlParams(empty($path) ? '' : implode('|', $path));
            // 封装路由
            $route = [$module, $controller, $action];
        }
        return ['type' => 'module', 'module' => $route];
    }

    /**
     * 解析URL的pathinfo参数和变量
     * @access private
     * @param string    $url URL地址
     * @return array
     */
    private static function parseUrlPath($url)
    {
        // 分隔符替换 确保路由定义使用统一的分隔符
        $url = str_replace('|', '/', $url);
        $url = trim($url, '/');
        $var = [];
        if (false !== strpos($url, '?')) {
            // [模块/控制器/操作?]参数1=值1&参数2=值2...
            $info = parse_url($url);
            $path = explode('/', $info['path']);
            parse_str($info['query'], $var);
        } elseif (strpos($url, '/')) {
            // [模块/控制器/操作]
            $path = explode('/', $url);
        } else {
            $path = [$url];
        }
        return [$path, $var];
    }

    /**
     * 解析URL地址中的参数Request对象
     * @access private
     * @param string    $url 请求地址
     * @param array     $var 变量
     * @return void
     */
    private static function parseUrlParams($url, &$var = [])
    {
        if ($url) {
            preg_replace_callback('/(\w+)\|([^\|]+)/', function ($match) use (&$var) {
                $var[$match[1]] = strip_tags($match[2]);
            }, $url);
        }
        // 设置当前请求的参数
        Request::instance()->route($var);
    }
}
