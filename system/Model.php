<?php
namespace reading;

/**
 * Class: Model
 *
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-11-21
 */
abstract class Model
{
    /**
     * 数据库对象池
     *
     * @var mixed
     */
    protected static $links = [];

    /**
     * 当前类名称
     *
     * @var mixed
     */
    protected $class;

    /**
     * 数据表名称
     *
     * @var mixed
     */
    protected $table;

    /**
     * 数据表主键 复合主键使用数组定义 不设置则自动获取
     *
     * @var mixed
     */
    protected $pk;

    /**
     * 数据信息
     *
     * @var mixed
     */
    protected $data = [];

    /**
     * 初始化过的模型.
     *
     * @var array
     */
    protected static $initialized = [];

    /**
     * 构造方法
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $data
     * @return void
     */
    public function __construct($data = [])
    {
        if (is_object($data)) {
            $this->data = get_object_vars($data);
        } else {
            $this->data = $data;
        }
        // 当前类名
        $this->class = get_class($this);

        if (empty($this->name)) {
            // 当前模型名
            $name       = str_replace('\\', '/', $this->class);
            $this->name = basename($name);
            if (Config::get('class_suffix')) {
                $suffix     = basename(dirname($name));
                $this->name = substr($this->name, 0, -strlen($suffix));
            }
        }

        // 执行初始化操作
        $this->initialize();
    }

    /**
     * 获取当前模型的数据库查询对象
     * @access public
     * @return Query
     */
    public function db($name = DB_MASTER)
    {
        $model = $this->class;
        if (!isset(self::$links[$model])) {
            // 设置当前模型 确保查询返回模型对象
            $query = Database::connect([], $name)->getQuery($model);

            $query->setTable($this->table);

            if (!empty($this->pk)) {
                $query->pk($this->pk);
            }

            self::$links[$model] = $query;
        }

        // 返回当前模型的数据库查询对象
        return self::$links[$model];
    }
    /**
     *  初始化模型
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        $class = get_class($this);
        if (!isset(static::$initialized[$class])) {
            static::$initialized[$class] = true;
            static::init();
        }
    }

    /**
     * 初始化处理
     * @access protected
     * @return void
     */
    protected static function init()
    {
    }

    /**
     * 设置数据对象值
     * @access public
     * @param mixed $data  数据或者属性名
     * @param mixed $value 值
     * @return $this
     */
    public function data($data, $value = null)
    {
        if (is_string($data)) {
            $this->data[$data] = $value;
        } else {
            // 清空数据
            $this->data = [];
            if (is_object($data)) {
                $data = get_object_vars($data);
            }
            if (true === $value) {
                // 数据对象赋值
                foreach ($data as $key => $value) {
                    $this->data[$key] = $value;
                }
            } else {
                $this->data = $data;
            }
        }
        return $this;
    }

    /**
     * 获取对象原始数据 如果不存在指定字段返回false
     * @access public
     * @param string $name 字段名 留空获取全部
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getData($name = null)
    {
        if (is_null($name)) {
            return $this->data;
        } elseif (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            throw new InvalidArgumentException('property not exists:' . $this->class . '->' . $name);
        }
    }

    /**
     * 获取模型对象的主键
     * @access public
     * @param string $name 模型名
     * @return mixed
     */
    public function getPk($name = '')
    {
        if (!empty($name)) {
            $table = $this->db()->getTable($name);
            return $this->db()->getPk($table);
        } elseif (empty($this->pk)) {
            $this->pk = $this->db()->getPk();
        }
        return $this->pk;
    }

    /**
     * 判断一个字段名是否为主键字段
     * @access public
     * @param string $key 名称
     * @return bool
     */
    protected function isPk($key)
    {
        $pk = $this->getPk();
        if (is_string($pk) && $pk == $key) {
            return true;
        } elseif (is_array($pk) && in_array($key, $pk)) {
            return true;
        }
        return false;
    }

    /**
     * 返回模型的错误信息
     * @access public
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * __call
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $method
     * @param mixed $args
     * @return void
    */
    public function __call($method, $args)
    {
        if (isset(static::$db)) {
            $query      = static::$db;
            static::$db = null;
        } else {
            $query = $this->db();
        }

        return call_user_func_array([$query, $method], $args);
    }
}
