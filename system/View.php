<?php
namespace reading;

class View
{
    // 视图实例
    protected static $instance;
    // 模板引擎实例
    public $engine;

    /**
     * 构造函数
     * @access public
     * @param array $engine  模板引擎参数
     */
    public function __construct($engine = [])
    {
        // 初始化模板引擎
        $this->engine((array) $engine);
        // 基础替换字符串
        $request = Request::instance();
    }

    /**
     * 初始化视图
     * @access public
     * @param array $engine  模板引擎参数
     * @param array $replace  字符串替换参数
     * @return object
     */
    public static function instance($engine = [], $replace = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($engine, $replace);
        }
        return self::$instance;
    }

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name  变量名
     * @param mixed $value 变量值
     * @return $this
     */
    public function assign($name, $value = '')
    {
        $this->engine->assign($name,  $value);
        return $this;
    }

    /**
     * 设置当前模板解析的引擎
     * @access public
     * @param array|string $options 引擎参数
     * @return $this
     */
    public function engine($options = [])
    {
        $this->engine = new \Smarty();
        if ($options) {
            foreach ($options as $key => $val) {
                $this->engine->{$key} = $val;
            }
        }
        return $this;
    }

    /**
     * 解析和获取模板内容 用于输出
     * @param string    $template 模板文件名或者内容
     * @return string
     * @throws Exception
     */
    public function fetch($template)
    {
        return $this->engine->fetch($template);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $template 内容
     * @return mixed
     */
    public function display($template)
    {
        return $this->engine->display($template);
    }
}
