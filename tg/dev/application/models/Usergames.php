<?php

class UsergamesModel extends F_Model_Pdo
{
	protected $_table = 'user_games';
	protected $_primary = '';
    public function __construct()
    {
        parent::__construct('h5');
    }
	public function getTableLabel()
	{
	    return '注册日志';
	}
	
	public function getFieldsLabel()
	{
	    return array(
	        'user_id' => '用户ID',
	        'game_id' => '游戏ID',
	        'last_play' => function(&$row){
                if(empty($row)) return '最后登录时间';
                return date('Y-m-d H:i:s',$row['last_play']);
            },
	        'register_time' =>'注册时间',
	    );
	}
	
	public function getFieldsSearch()
	{
	    return array(
	        'user_id' => array('用户ID', 'input', null, ''),
	        'game_id' => array('游戏ID', 'input', null, ''),
	        'last_play' => array('登录日期', 'datepicker', '{dateFmt:\'yyyy-MM-dd\'}', ''),
	        'register_time' => array('注册日期', 'datepicker', '{dateFmt:\'yyyy-MM-dd\'}', ''),
	    );
	}
}
