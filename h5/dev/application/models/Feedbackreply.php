<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/29/029
 * Time: 10:03
 */

class FeedbackreplyModel extends F_Model_Pdo
{
    public function __construct()
    {
        parent::__construct('cps');
    }
    protected $_table = 'feed_back_reply';
    protected $_primary = 'reply_id';
}