<?php
namespace app\common\model;

use reading\Model;

class User extends Model
{
    protected $table = 'user';

    public function hotAuthor ($where='', $order='', $start=0, $limit=4)
    {
        $sql = 'SELECT t1.id, t1.nickname, t1.name, t1.sign, t1.avatar, t2.follower, t2.fans, t2.reward, t2.remain_reward';
        $sql .= ' FROM ' . $this->table . ' AS t1';
        $sql .= ' LEFT JOIN ';
        $sql .= ' user_cnt AS t2';
        $sql .= ' ON t1.id = t2.uid';
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
