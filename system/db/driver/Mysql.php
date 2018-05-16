<?php
namespace reading\db\driver;

use PDO;
use reading\db\Driver;

/**
 * PDO driver class
  *
 * @package lib.db
 * @version 1.0
 */
class Mysql extends Driver
{	
    /**
     * parseDsn
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-27
     *
     * @param mixed $config
     * @return void
     */
    protected function parseDsn($config) 
    {
        $dsn = 'mysql:dbname=' . $config['database'] . ';host=' . $config['host'];
        if (!empty($config['port'])) {
            $dsn .= ';port=' . $config['port'];
        } elseif (!empty($config['socket'])) {
            $dsn .= ';unix_socket=' . $config['socket'];
        }
        if (!empty($config['charset'])) {
            $dsn .= ';charset=' . $config['charset'];
        }
        return $dsn;
	}

    /**
     * 取得数据表的字段信息
     * @access public
     * @param string $tableName
     * @return array
     */
    public function getFields($tableName)
    {
        $this->connect();
        $sql = 'SHOW COLUMNS FROM ' . $tableName;
        // 调试开始
        $this->debug(true);
        $pdo = $this->conn->query($sql);
        // 调试结束
        $this->debug(false, $sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];
        if ($result) {
            foreach ($result as $key => $val) {
                $val                 = array_change_key_case($val);
                $info[$val['field']] = [
                    'name'    => $val['field'],
                    'type'    => $val['type'],
                    'notnull' => (bool) ('' === $val['null']), // not null is empty, null is yes
                    'default' => $val['default'],
                    'primary' => (strtolower($val['key']) == 'pri'),
                    'autoinc' => (strtolower($val['extra']) == 'auto_increment'),
                ];
            }
        }
        return $this->fieldCase($info);
    }
}

?>
