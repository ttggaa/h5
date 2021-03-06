<?php

class CommentModel extends F_Model_Pdo
{
    protected $_table = 'comment';
    protected $_primary = 'comm_id';

    public function __construct()
    {
        parent::__construct('h5');
    }

    public function getFieldsLabel()
    {
        return array(
            'comm_id' => 'ID',
            'user_id' => '用户id',
//		    'parent_id' => '上级id',
            'game_id' => '游戏id',
            'comm_cont' => '评论',
            'comm_time' => function (&$row) {
                if (empty($row)) return '评论时间';
                return date('Y-m-d H:i:s', $row['comm_time']);
            },
            'like_num' => '赞',
        );
    }

    /**
     * 获取玩家的礼包记录
     *
     * @param int $uid
     * @param int $pn
     * @param int $limit
     * @return array
     */
    public function getComment($game_id, $pn = 1, $limit = 6)
    {
        $offset = ($pn - 1) * $limit;
        $pdo = $this->getPdo();
        $stm = $pdo->query("SELECT comment.*,user.username,user.avatar FROM comment LEFT join user on user.user_id=comment.user_id WHERE game_id='{$game_id}' and parent_id = 0 and  is_check =1 ORDER BY comm_time desc LIMIT {$offset},{$limit} ");
        $logs = array();
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        while ($row) {
            $logs[] = $row;
            $row = $stm->fetch(PDO::FETCH_ASSOC);
        }
        //递归

        foreach ($logs as $key => &$value) {
            $value['children'] = $this->fetchAllBySql("SELECT comment.*,user.username,user.avatar FROM comment LEFT join user on user.user_id=comment.user_id where parent_id={$value['comm_id']} and is_check =1 ORDER BY comm_time desc");
            //回复人的id
        }
        return $logs;
    }
}
