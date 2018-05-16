<?php
namespace reading;
/**
 * SESSION操作
 *
 * @abstract
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-11-25
 */
abstract class Session
{
    /**
     * init
     *
     * @var mixed
     */
    protected static $init   = null;

    /**
     * session初始化
     * @param array $config
     * @return void
     * @throws \think\Exception
     */
    public static function init()
    {
        // 记录初始化信息
        App::$debug && Log::record('[ SESSION ] INIT ', 'info');

        if (PHP_SESSION_ACTIVE != session_status()) {
            session_start();
        }
        self::$init = true;
    }

    /**
     * session设置
     * @param string        $name session名称
     * @param mixed         $value session值
     * @return void
     */
	public static function set($name, $value)
	{
        empty(self::$init) && self::init();
        $_SESSION[$name] = $value;
	}
	
    /**
     * 删除session数据
     * @param string $name session名称
     * @return void
     */
	public static function del($name)
	{
		unset($_SESSION[$key]);
		return true;
	}
	
    /**
     * session获取
     * @param string        $name session名称
     * @return mixed
     */
	public static function get($name)
	{
        empty(self::$init) && self::init();
		return isset($_SESSION[$name]) ? $_SESSION[$name] : '';
	}
	
    /**
     * 判断session数据
     * @param string        $name session名称
     * @return bool
     */
	public static function has($name)
	{
        empty(self::$init) && self::init();
		return isset($_SESSION[$name]);
	}
	
    /**
     * 启动session
     * @return void
     */
    public static function start()
    {
        session_start();
        self::$init = true;
    }

    /**
     * 清空session数据
     * @return void
     */
    public static function clear()
    {
        empty(self::$init) && self::init();
        $_SESSION = [];
    }
    /**
     * 销毁session
     * @return void
     */
	public function destroy()
    {
        if (!empty($_SESSION)) {
            $_SESSION = [];
        }
        session_unset();
        session_destroy();
        self::$init = null;
    }
}
?>
