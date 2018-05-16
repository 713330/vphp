<?php
namespace app\common\model;

use reading\Model;

class Book extends Model
{
    protected $table = 'book';

    public function hotBook ($where='', $order='', $start=0, $limit=4)
    {
        $sql = 'SELECT t1.id, t1.uid, t1.title, t1.cover, t1.small_cover, t1.summary,t1.category_id, t1.utime, t2.read_num';
        $sql .= ' FROM ' . $this->table . ' AS t1';
        $sql .= ' LEFT JOIN ';
        $sql .= ' book_cnt AS t2';
        $sql .= ' ON t1.id = t2.book_id';
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
