<?php
namespace reading\db;

use PDO;
use reading\App;
use reading\Cache;
use reading\Config;
use reading\Database;
use reading\Model;

class Query
{
    // 数据库Connection对象实例
    protected $connection;
    // 当前模型类名称
    protected $model;
    // 当前数据表名称
    protected $table;
    // 当前数据表主键
    protected $pk;
    // 数据表信息
    protected static $info = [];

    /**
     * 是否自动开启缓存 
     *
     * @var mixed
     */
    protected $autocache = true;

    /**
     * 表结构缓存key格式 
     */
    const STRUCTRUE_CACHE_KEY_FORMAT = 'tb_structure_%s';

    /**
     * 数据缓存key格式 
     */
    const DATA_CACHE_KEY_FORMAT  = 'data_%s';
    /**
     * 构造函数
     * @access public
     * @param Driver $connection 数据库对象实例
     * @param string     $model      模型名
     */
    public function __construct($connection = null, $model = '')
    {
        $this->connection = $connection ? : Database::connect([]);
        $this->model      = $model;
        App::$debug && $this->autocache = false; 
    }

    /**
     * 获取当前的数据库Connection对象
     * @access public
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 切换当前的数据库连接
     * @access public
     * @param mixed $config
     * @return $this
     */
    public function connect($config)
    {
        $this->connection = Db::connect($config);
        return $this;
    }

    /**
     * 获取当前的模型对象名
     * @access public
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * 指定默认数据表名（含前缀）
     * @access public
     * @param string $table 表名
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * 得到当前数据表
     * @access public
     * @param string $name
     * @return string
    */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param string      $sql    sql指令
     * @return mixed
     * @throws PDOException
     */
    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    /**
     * 执行语句
     * @access public
     * @param string $sql  sql指令
     * @return int
     * @throws PDOException
     */
    public function execute($sql)
    {
        return $this->connection->execute($sql);
    }

    /**
     * 获取最近插入的ID
     * @access public
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->connection->getLastInsertId();
    }

    /**
     * 获取最近一次查询的sql语句
     * @access public
     * @return string
     */
    public function getLastSql()
    {
        return $this->connection->getLastSql();
    }

    /**
    * 启动事务
     * @access public
     * @return void
     */
    public function startTrans()
    {
        $this->connection->startTrans();
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return void
     * @throws PDOException
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * 事务回滚
     * @access public
     * @return void
     * @throws PDOException
     */
    public function rollback()
    {
        $this->connection->rollback();
    }

    /**
     * 获取数据库的配置参数
     * @access public
     * @param string $name 参数名称
     * @return boolean
     */
    public function getConfig($name = '')
    {
        return $this->connection->getConfig($name);
    }

    /**
     * 转义字符 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $value
     * @return void
     */
    protected function quote($value)
    {
        return $this->connection->quote($value);
    }

    /**
     * 指定数据表主键
     * @access public
     * @param string $pk 主键
     * @return $this
     */
    public function pk($pk)
    {
        $this->pk = $pk;
    }

    /**
     * 获取数据表信息
     * @access public
     * @param mixed  $tableName 数据表名 留空自动获取
     * @param string $fetch     获取信息类型 包括 fields type pk
     * @return mixed
     */
    public function getTableInfo($tableName = '', $fetch = '')
    {
        if (!$tableName) {
            $tableName = $this->getTable();
        }

        $db = $this->getConfig('database');
        if (!isset(self::$info[$db . '.' . $tableName])) {
            if (!strpos($tableName, '.')) {
                $schema = $db . '.' . $tableName;
            } else {
                $schema = $tableName;
            }
            $cacheKey = sprintf(self::STRUCTRUE_CACHE_KEY_FORMAT, $schema);
            if ($this->autocache && ($info = Cache::get($cacheKey))) {
                $info = json_decode($info, true);
            } else {
                $info = $this->connection->getFields($tableName);
                $this->autocache && Cache::set($cacheKey, json_encode($info));
            }
            $fields = array_keys($info);
            $bind   = $type   = [];
            foreach ($info as $key => $val) {
                // 记录字段类型
                $type[$key] = $val['type'];
                if (!empty($val['primary'])) {
                    $pk[] = $key;
                }
            }
            if (isset($pk)) {
                // 设置主键
                $pk = count($pk) > 1 ? $pk : $pk[0];
            } else {
                $pk = null;
            }
            self::$info[$db . '.' . $tableName] = ['fields' => $fields, 'type' => $type, 'pk' => $pk];
        }
        return $fetch ? self::$info[$db . '.' . $tableName][$fetch] : self::$info[$db . '.' . $tableName];
    }

    /**
     * 获取当前数据表的主键
     * @access public
     * @return string|array
     */
    public function getPk($tablename = '')
    {
        if (!empty($this->pk)) {
            $pk = $this->pk;
        } else {
            $pk = $this->getTableInfo($tablename, 'pk');
        }
        return $pk;
    }

    /**
     * 获取当前数据表字段信息
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-30
     *
     * @param mixed $tablename
     * @return void
     */
    public function getTableFields($tablename = '')
    {
        return $this->getTableInfo($tablename, 'fields');
    }

    /**
     * 获取当前数据表字段类型
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-30
     *
     * @param string $tablename
     * @return void
     */
    public function getFieldsType($tablename = '')
    {
        return $this->getTableInfo($tablename, 'type');
    }

    /**
     * 生成缓存标识
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-30
     *
     * @return void
     */
    protected function getCacheKey($sql)
    {
        $database = $this->getConfig('database');
        if ($pos = stripos($sql, 'where')) {
            if ($this->pk && is_string($this->pk)) {
                $key = sha1($database . '_' . $this->table . "_" . $this->pk);
            } else {
                $key = trim(substr($sql, $pos + 5));
                $key = explode(' ', $key);
                sort($key, SORT_STRING);
                $key = sha1($database . '_' . $this->table . strtolower(implode($key)));
            }
            $key = sprintf(self::DATA_CACHE_KEY_FORMAT, $key);
        } else {
            $key = '';
        }
        return $key;
    }

    /**
     * 插入单条记录
     *
     * @param object $q
     * @param string $tablename
     * @return boolean
     */
    public function insert($data, $tablename = '') {
        if (!$tablename) {
            $tablename = $this->getTable();
        }
        $tableFields = $this->getTableFields($tablename);
        if (empty($data) || empty($tableFields)) {
            return false;
        }
        //link sql
        $sql = "INSERT INTO `$tablename`(";
        $fields = $values = '';
        foreach ($tableFields as $field) {
            if (isset($data->$field)) {
                $fields .= "`$field`,";
                $values .= $this->quote($data->$field) . ",";
            }
        }

        $sql .= rtrim($fields, ',') . ')';
        $sql .= ' VALUES (';
        $sql .= rtrim($values, ',') . ')';
        return $this->execute($sql);
    }

    /**
     * 批量插入记录
     *
     * @param object $q
     * @param string $tablename
     * @return boolean
     */
    public function insertAll($datas, $tablename = '') {
        if (!$tablename) {
            $tablename = $this->getTable();
        }
        $tableFields = $this->getTableFields($tablename);
        if (empty($datas) || empty($tableFields)) {
            return false;
        }
        //link sql
        $sql = "INSERT INTO `$tablename`(";
        $fields = $values = "";
        foreach ($datas as $idx => $data) {
            $values .= "(";
            foreach ($tableFields as $field) {
                if ($idx == 0) {
                    $fields .= "`$field`,";
                }
                if (isset($data->$field)) {
                    $values .= $this->quote($data->$field) . ",";
                }
            }
            $values = rtrim($values, ',') . '), ';
        }

        $sql .= rtrim($fields, ',') . ')';
        $sql .= ' VALUES ';
        $sql .= rtrim($values, ',');
        return $this->execute($sql);
    }

    /**
     * 更新数据信息 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $q
     * @param string $tablename
     * @param mixed $con
     * @return void
     */
    public function update($data, $where = null, $tablename = '')
    {
        if (!$tablename) {
            $tablename = $this->getTable();
        }
        $tableFields = $this->getTableFields($tablename);
        if (empty($data) || empty($tableFields)) {
            return false;
        }
        //link sql
        $sql = "UPDATE `$tablename` set ";
        foreach ($tableFields as $field) {
            if (isset($data->$field)) {
                $sql .= "`$field` = " . $this->quote($data->$field) . ",";
            }
        }
        $sql = rtrim($sql,',');
        if ($where) {
            $sql .= $this->where($where, $tablename);
        }
        $cacheKey = $this->getCacheKey($sql);
        $this->autocache && Cache::rm($cacheKey);
        return $this->execute($sql);
    }

    /**
     * 获取单条数据 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $where
     * @param string $tablename
     * @return void
     */
    public function find($where, $tablename = '')
    {
        if (!$tablename) {
            $tablename = $this->getTable();
        }
        //link $sql
        if (isset ($where->fields)) {
            $sql = "SELECT " . $where->fields . " FROM `$tablename` ";
            unset($where->fields);
        } else {
            $sql = "SELECT * FROM `$tablename` ";
        }
        $where->limit = 1;
        $sql .= $this->where($where, $tablename);
        //缓存检查
        $cacheKey = $this->getCacheKey($sql);
        if ($this->autocache && ($result = Cache::get($cacheKey))) {
            $result = json_decode($result, true);
            return $result;
        } else {
            $result = $this->query($sql);
            if ($result) {
                $result = array_pop($result);
                $this->autocache && Cache::set($cacheKey, json_encode($result));
                return $result;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取多条数据 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $where
     * @param string $tablename
     * @return void
     */
    public function findAll($where = null, $tablename = '')
    {
        if (!$tablename) {
            $tablename = $this->getTable();
        }
        //link $sql
        if (isset($where->fields)) {
            $sql = "SELECT " . $where->fields . " FROM `$tablename` ";
            unset($where->fields);
        } else {
            $sql = "SELECT * FROM `$tablename` ";
        }

        if ($where) {
            $sql .= $this->where($where, $tablename);
        }
        return $this->query($sql);
    }

    /**
     * 删除记录 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $where
     * @param string $tablename
     * @return void
     */
    public function delete($where, $tablename = '')
    {
        if (!$tablename) {
            $tablename = $this->getTable();
        }
        //link $sql
        $sql = "DELETE FROM `$tablename` ";
        $sql .= $this->where($where, $tablename);

        return $this->execute($sql);
    }

    /**
     * 统计数据条数 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $where
     * @param string $tablename
     * @return void
     */
    public function count($where, $tablename = '')
    {
        if (!$tablename) {
            $tablename = $this->getTable();
        }
        //link $sql
        $sql = "SELECT COUNT(*) AS `total` FROM `$tablename` ";

        $sql .= $this->where($where, $tablename);

        $result = $this->query($sql);
        if ($result) {
            return $result[0]['total'];
        } else {
            return 0;
        }
    }

    /**
     * 字段加一  
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $field
     * @param mixed $where
     * @param int $step
     * @param string $tablename
     * @return void
     */
    public function inc($field, $where, $step = 1, $tablename = '')
    {
        if (!$tablename) {
            $tablename = $this->getTable();
        }

        $sql = "UPDATE `$tablename`  set `$field` = $field + $step ";

        $sql .= $this->where($where, $tablename);

        return $this->execute($sql);
    }

    /**
     * 字段减一 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @return void
     */
    public function dec($field, $where, $step = 1, $tablename = '')
    {
        if (!$tablename) {
            $tablename = $this->getTable();
        }

        $sql = "UPDATE `$tablename` set `$field` = $field - $step ";

        $sql .= $this->where($where, $tablename);

        return $this->execute($sql);
    }

    /**
     * 拼接查询条件 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-25
     *
     * @param mixed $where
     * @param mixed $tablename
     * @return void
     */
    protected function where($conditions, $tablename)
    {
        $where = 'WHERE 1 = 1 ';

        $tableFields = $this->getTableInfo($tablename, 'fields');

        foreach ($tableFields as $field) {
            if (isset($conditions->$field)) {
                if (is_array($conditions->$field)) {
                    $detail = $conditions->$field;
                    switch (strtolower($detail['op'])) {
                        case 'between':
                            $where .= "  AND `$field` BETWEEN " . $this->quote($detail['start']) . " AND " . $this->quote($detail['end']) . " ";
                            break;
                        case 'like':
                            if (!is_array($detail['val'])) {
                                $detail['val'] = [$detail['val']];
                            }
                            foreach ($detail['val'] as $val) {

                                if (isset($detail['link'])) {
                                    $where .= "  " . $detail['link'] . " `$field` LIKE '%" . $val . "%'";
                                } else {
                                    $where .= "  AND `$field` LIKE '%" . $val . "%'";
                                }
                            }
                            break;
                        case 'likeor':
                            if (!is_array($detail['val'])) {
                                $detail['val'] = [$detail['val']];
                            }
                            foreach ($detail['val'] as $val) {
                                $where.= " AND concat( ". $this->quote($detail['object']) .") LIKE '%" . $this->quote($val) . "%'";
                            }
                            break;
                        case 'inor':
                            if (is_array($detail['val'])) {
                                $detail['val'] = implode(',', $detail['val']);
                            }
                            $where .= " OR `$field` IN (" .$detail['val'] . ")";
                            break;
                        case 'not':
                            if (is_array($detail['val'])) {
                                $detail['val'] = implode(',', $detail['val']);
                            }
                            $where .= " AND  `$field` NOT IN (" .$detail['val'] . ") ";
                            break;
                        case 'in':
                            if (is_array($detail['val'])) {
                                $detail['val'] = implode(',', $detail['val']);
                            }
                            $where .= " AND `$field` IN (" . $detail['val'] . ")";
                            break;
                        default:
                            if (isset($detail['link'])) {
                                $where .= $detail['link'] . " `$field` " . $detail['op'] .  $this->quote($detail['val']) . " ";
                            } else {
                                $where .= " AND  `$field` " . $detail['op'] . $this->quote($detail['val']) . " ";
                            }
                            break;
                    }
                } else {
                    if (strpos($conditions->$field, ',')) {
                        $where .= " AND  `$field` IN (" . $conditions->$field . ")";
                    } else {
                        $where .= " AND  `$field`=" . $this->quote($conditions->$field) . " ";
                    }
                }
            }
        }
        //trim last and
        $sql = trim($where);
        if (substr($where, -3) == 'AND') {
            $where = substr($where, 0, strripos($where, 'AND'));
        }
        //group by field
        if (isset($conditions->group)) {
            $where .= " GROUP BY " . $conditions->group;
        }
        //order by fields
        if (!empty($conditions->order)) {
            $where .= " ORDER BY " . $conditions->order;
        }
        //limit records
        if (!empty($conditions->limit)) {
            $where .= isset($conditions->start) ? " LIMIT " . $conditions->start . ", " . $conditions->limit : " LIMIT " . $conditions->limit;
        }
        //add end
        return $where;
    }
}
