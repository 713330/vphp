<?php
namespace app\common\model;

use reading\Model;

class UserReadStatus extends Model
{
    protected $table = 'user_read_status';

    /**
     * 我阅读过的书的阅读量
     * @param  string  $where [description]
     * @param  string  $order [description]
     * @param  integer $start [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function userReadInfo ($where='', $order='', $start=0, $limit=4)
    {
        $sql = 'SELECT t1.book_id, t1.uid, t2.read_num';
        $sql .= ' FROM ' . $this->table . ' AS t1';
        $sql .= ' LEFT JOIN ';
        $sql .= ' book_cnt AS t2';
        $sql .= ' ON t1.book_id = t2.book_id';
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        if (!empty($order)) {
            $sql .= ' ORDER BY ' . $order;
        }
        $sql .= ' LIMIT ' . $start . ', ' . $limit;
		return $this->db()->query($sql);
    }
}
