<?php
namespace reading;

/**
* DB class
* @description  Use to get Database Conneciton  and destroy Connection
* @createDate 2010.12.17
*/
class Database
{
    //  数据库连接实例
    private static $instance = [];

    /**
     * 数据库初始化 并取得数据库类实例
     * @static
     * @access public
     * @param mixed         $config 连接配置
     * @param string   $name 连接标识 
     * @return Connection
     * @throws Exception
     */
    public static function connect($config = [], $name = DB_MASTER)
    {
        if (empty($name)) {
            $name = md5(json_encode($config));
        }

        if (!isset(self::$instance[$name])) {
            // 解析连接参数 支持数组和字符串
            $options = self::parseConfig($config);
            $options = $options[$name];
            if (empty($options['type'])) {
                throw new \InvalidArgumentException('Underfined db type');
            }
            $class = false !== strpos($options['type'], '\\') ? $options['type'] : '\\reading\\db\\driver\\' . ucwords($options['type']);
            // 记录初始化信息
            if (App::$debug) {
                Log::record('[ DB ] INIT ' . $options['type'], 'info');
            }
            self::$instance[$name] = new $class($options);
        }

        return self::$instance[$name];
    }

    /**
     * 数据库连接参数解析
     * @static
     * @access private
     * @param mixed $config
     * @return array
     */
    private static function parseConfig($config)
    {
        if (empty($config)) {
            $config = Config::get('database');
        }
        return $config;
    }

    // 调用驱动类的方法
    public static function __callStatic($method, $params)
    {
        // 自动初始化数据库
        return call_user_func_array([self::connect(), $method], $params);
    }
}
?>
