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
	        'user_id' =>'用户id',
	        'game_id' =>'游戏id',
	        'user_name' =>'用户名',
	        'game_name' => '游戏名',
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
	        'user_name' => array('用户名', 'input', null, ''),
	        'user_id' => array('用户ID', 'input', null, ''),
	        'game_id' => array('游戏ID', 'input', null, ''),
	        'game_name' => array('游戏名', 'input', null, ''),
	        'last_play' => array('登录日期', 'datepicker', '{dateFmt:\'yyyy-MM-dd\'}', ''),
	        'register_time' => array('注册日期', 'datepicker', '{dateFmt:\'yyyy-MM-dd\'}', ''),
//	        'tg_channel' => array('代理渠道', 'hidden', "{$_SESSION['admin_id']}", ''),
	    );
	}
}
