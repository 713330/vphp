<?php
namespace reading\db;

use PDO;
use PDOStatement;
use reading\exception\PDOException;
use reading\Log;
use reading\db\Query;

/**
 * DB class
 * @version 1.0
 */
abstract class Driver
{
    /**
     * PDOStatement PDO操作实例
     *
     * @var mixed
     */
    protected $PDOStatement;

    /**
     * 当前SQL指令 
     *
     * @var string
     */
    protected $queryStr = '';

    /**
     * 返回或者影响记录数 
     *
     * @var int
     */
    protected $numRows = 0;

	/**
	 * 数据库连接 
	 *
	 * @var mixed
	 */
	protected $conn = null;

	/**
	 * 是否事务状态 
	 *
	 * @var mixed
	 */
	protected $trans = false;

    /**
     *  查询结果类型
     *
     * @var mixed
     */
    protected $fetchType = PDO::FETCH_ASSOC;

    /**
     * 字段属性大小写
     *
     * @var mixed
     */
    protected $attrCase = PDO::CASE_LOWER;

    /**
     * 查询对象
     *
     * @var mixed
     */
    protected $query = [];

    /**
     *  数据库连接参数配置
     *
     * @var array 
     */
    protected $config = [
        // 数据库类型
        'type'            => '',
        // 服务器地址
        'host'        => '',
        // 数据库名
        'database'        => '',
        // 用户名
        'username'        => '',
        // 密码
        'password'        => '',
        // 端口
        'port'        => '',
        // 连接dsn
        'dsn'             => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => '',
    ];

    /**
     * PDO连接参数
     *
     * @var mixed
     */
    protected $params = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    /**
     * __construct
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-22
     *
     * @param mixed $hostname
     * @param mixed $name
     * @return void
     */
    public function __construct($config = []) 
    {
		if (empty($config)) {
            throw new \InvalidArgumentException('Underfined db type');
		}
        $this->config = array_merge($this->config, $config);
	}

    /**
     * 创建指定模型的查询对象
     * @access public
     * @param string $model 模型类名称
     * @param string $queryClass 查询对象类名
     * @return Query
     */
    public function getQuery($model = 'db', $queryClass = '')
    {
        if (!isset($this->query[$model])) {
            $this->query[$model] = new Query($this, 'db' == $model ? '' : $model);
        }
        return $this->query[$model];
    }

    /**
     * 调用Query类的查询方法
     * @access public
     * @param string    $method 方法名称
     * @param array     $args 调用参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->getQuery(), $method], $args);
    }

    /**
    /**
     * 解析pdo连接的dsn信息
     * @access protected
     * @param array $config 连接信息
     * @return string
     */
    abstract protected function parseDsn($config);

    /**
     * 取得数据表的字段信息
     * @access public
     * @param string $tableName
     * @return array
     */
    abstract public function getFields($tableName);

    /**
     * 对返数据表字段信息进行大小写转换出来
     * @access public
     * @param array $info 字段信息
     * @return array
     */
    public function fieldCase($info)
    {
        // 字段大小写转换
        switch ($this->attrCase) {
            case PDO::CASE_LOWER:
                $info = array_change_key_case($info);
                break;
            case PDO::CASE_UPPER:
                $info = array_change_key_case($info, CASE_UPPER);
                break;
            case PDO::CASE_NATURAL:
            default:
                // 不做转换
        }
        return $info;
    }

    /**
     * 获取数据库的配置参数
     * @access public
     * @param string $config 配置名称
     * @return mixed
     */
    public function getConfig($config = '')
    {
        return $config ? $this->config[$config] : $this->config;
    }

    /**
     * 设置数据库的配置参数
     * @access public
     * @param string|array      $config 配置名称
     * @param mixed             $value 配置值
     * @return void
     */
    public function setConfig($config, $value = '')
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        } else {
            $this->config[$config] = $value;
        }
    }

    /**
     * 连接数据库方法
     * @access public
     * @param array         $config 连接参数
     * @return PDO
     * @throws Exception
     */
    public function connect($config = [])
    {
        if (!$config) {
            $config = $this->config;
        } else {
            $config = array_merge($this->config, $config);
        }
        // 连接参数
        if (isset($config['params']) && is_array($config['params'])) {
            $params = $config['params'] + $this->params;
        } else {
            $params = $this->params;
        }
        // 记录当前字段属性大小写设置
        $this->attrCase = $params[PDO::ATTR_CASE];
        try {
            if (empty($config['dsn'])) {
                $config['dsn'] = $this->parseDsn($config);
            }
            $this->conn = new PDO($config['dsn'], $config['username'], $config['password'], $params);
        } catch (\PDOException $e) {
            throw $e;
        }
        return $this->conn;
    }

    /**
     * 释放查询结果
     * @access public
     */
    public function free()
    {
        $this->PDOStatement = null;
    }

    /**
     * 获取PDO对象
     * @access public
     * @return \PDO|false
     */
    public function getPdo()
    {
        if (!$this->conn) {
            return false;
        } else {
            return $this->conn;
        }
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param string    $sql sql指令
     * @param bool      $class 是否返回PDO对象
     * @return mixed
     * @throws PDOException
     */
    public function query($sql)
    {
		if (empty($this->conn)) {
            $this->connect();
		}
        $this->queryStr = $sql;
        // 释放前次的查询结果
        if (!empty($this->PDOStatement)) {
            $this->free();
        }
        try {
            // 调试开始
            $this->debug(true);
            if (empty($this->PDOStatement)) {
                $this->PDOStatement = $this->conn->prepare($sql);
            }
            // 执行查询
            $this->PDOStatement->execute();
            // 调试结束
            $this->debug(false);
            // 返回结果集
            return $this->getResult();
        } catch (\PDOException $e) {
            throw new PDOException($e, $this->config, $this->getLastsql());
        }
    }

    /**
     * 执行语句
     * @access public
     * @param string        $sql sql指令
     * @return int
     * @throws PDOException
     */
    public function execute($sql)
    {
        if (empty($this->conn)) {
            $this->connect();
        }

        // 记录SQL语句
        $this->queryStr = $sql;

        //释放前次的查询结果
        if (!empty($this->PDOStatement) && $this->PDOStatement->queryString != $sql) {
            $this->free();
        }

        try {
            // 调试开始
            $this->debug(true);
            if (empty($this->PDOStatement)) {
                $this->PDOStatement = $this->conn->prepare($sql);
            }
            // 执行语句
            $this->PDOStatement->execute();
            // 调试结束
            $this->debug(false);

            $this->numRows = $this->PDOStatement->rowCount();
            return $this->numRows;
        } catch (\PDOException $e) {
            throw new PDOException($e, $this->config, $this->getLastsql());
        }
    }

    /**
     * 获得数据集数组
     * @access protected
     * @param bool   $pdo 是否返回PDOStatement
     * @return array
     */
    protected function getResult()
    {
        $result        = $this->PDOStatement->fetchAll($this->fetchType);
        $this->numRows = count($result);
        return $result;
    }

    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans()
    {
        if (empty($this->conn)) {
            $this->connect();
        }
        $this->trans = true;
        $this->conn->beginTransaction();
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return void
     * @throws PDOException
     */
    public function commit()
    {
        if (empty($this->conn)) {
            $this->connect();
        }

        if ($this->trans == false) {
            return false;
        }

        $this->trans = false;

        return $this->conn->commit();
    }

    /**
     * 事务回滚
     * @access public
     * @return void
     * @throws PDOException
     */
    public function rollback()
    {
        if (empty($this->conn)) {
            $this->connect();
        }

        if ($this->trans == false) {
            return false;
        }

        $this->trans = false;

        return $this->conn->rollBack();
    }

    /**
     * 关闭数据库（或者重新连接）
     * @access public
     * @return $this
     */
    public function close()
    {
        $this->conn    = null;
    }

    /**
     * 获取最近一次查询的sql语句
     * @access public
     * @return string
     */
    public function getLastSql()
    {
        return $this->queryStr;
    }

    /**
     * 获取最近插入的ID
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-22
     *
     * @return string 
     */
    public function getLastInsertId()
    {
        return $this->conn->lastInsertId();
    }

    /**
     * 获取返回或者影响的记录数
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-22
     *
     * @return void
     */
    public function getEffectedRows()
    {
        return $this->numRows;
    }

    /**
     * 获取最近的错误信息
     * @access public
     * @return string
     */
    public function getError()
    {
        if ($this->PDOStatement) {
            $error = $this->PDOStatement->errorInfo();
            $error = $error[1] . ':' . $error[2];
        } else {
            $error = '';
        }
        if ('' != $this->queryStr) {
            $error .= "\n [ SQL语句 ] : " . $this->getLastsql();
        }
        return $error;
    }

    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str SQL字符串
     * @return string
     */
    public function quote($str)
    {
        return $this->conn ? $this->conn->quote($str) : $str;
    }

    /**
     * 数据库调试 记录当前SQL及分析性能
     * @access protected
     * @param boolean $start 调试开始标记 true 开始 false 结束
     * @param string  $sql 执行的SQL语句 留空自动获取
     * @return void
     */
    protected function debug($start, $sql = '')
    {
        if (!empty($this->config['debug'])) {
            // 开启数据库调试模式
            if ($start) {
                Debug::remark('queryStartTime', 'time');
            } else {
                // 记录操作结束时间
                Debug::remark('queryEndTime', 'time');
                $runtime = Debug::getRangeTime('queryStartTime', 'queryEndTime');
                $sql     = $sql ?: $this->getLastsql();
                $log     = $sql . ' [ RunTime:' . $runtime . 's ]';
                // SQL监听
                Log::record('[ SQL ] ' . $sql . ' [ RunTime:' . $runtime . 's ]', 'sql');
            }
        }
    }

    /**
     * 析构方法
     * @access public
     */
    public function __destruct()
    {
        // 释放查询
        if ($this->PDOStatement) {
            $this->free();
        }
        // 关闭连接
        $this->close();
    }
}

?>
