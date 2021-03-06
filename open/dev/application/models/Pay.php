<?php

class PayModel extends F_Model_Pdo
{
	protected $_table = 'pay';
	protected $_primary='pay_id';
	
	public $_types = array(
	    'alipay' => '支付宝',
	    'wxpay' => '微信',
	    'iapppay' => '爱贝',
	    'deposit' => '平台币',
	);
	
	public function getTableLabel()
	{
		return '支付记录';
	}
	
	public function getFieldsLabel()
	{
		return array(
			'pay_id' => function(&$row){
		        if( empty($row) ) return '支付ID';
		        return sprintf('%16.0f', $row['pay_id']);
		    },
		    'username' => '支付账号',
		    'to_user' => '充入账号',
		    'game_name' => '游戏名称',
			'server_name' => '区/服名称',
		    'money' => function(&$row){
		        if( empty($row) ) return '充值金额';
		        return number_format($row['money']).'￥';
		    },
		    'deposit' => function(&$row){
		        if( empty($row) ) return '平台币存量';
		        return $row['game_name'] ? '-' : number_format($row['deposit']).'￥';
		    },
		    'type' => function(&$row){
		        if( empty($row) ) return '支付渠道';
		        return $this->_types[$row['type']];
		    },
		    'pay_type' => '支付方式',
			'trade_no' => '第三方流水号',
		    'add_time' => function(&$row){
		        if( empty($row) ) return '下单时间';
		        return date('Y-m-d H:i:s', $row['add_time']);
		    },
		    'pay_time' => function(&$row){
		        if( empty($row) ) return '付款时间';
		        return $row['pay_time'] ? date('Y-m-d H:i:s', $row['pay_time']) : '-';
		    },
		    'finish_time' => function(&$row){
		        if( empty($row) ) return '到账时间';
		        return $row['finish_time'] ? date('Y-m-d H:i:s', $row['finish_time']) : '-';
		    },
		);
	}
	
	public function getFieldsSearch()
	{
	    return array(
	        'type' => array('付款方式', 'select', $this->_types, ''),
	        'pay_id' => array('支付ID', 'input', null, ''),
	        'username' => array('用户名', 'input', null, ''),
	        'game_id' => array('游戏ID', 'input', null, ''),
	        'server_id' => array('区/服ID', 'input', null, ''),
	        'trade_no' => array('流水号', 'input', null, ''),
	    );
	}
	
	/**
	 * 生成16位订单ID，在数据库中是bigint型
	 * 
	 * @return string
	 */
	public function createPayId()
	{
	    list($usec, $sec) = explode(' ', microtime());
	    $usec = str_replace('0.', '', $usec);
	    $pay_id = substr($sec, 1);
	    $len = strlen($usec);
	    $pad = 100;
	    if( $len < 4 ) {
	        $pad *= pow(10, 4 - $len);
	    } else {
	        $len = 4;
	    }
	    $pay_id .= substr($usec, 0, $len);
	    $pay_id .= mt_rand($pad, $pad*10-1);
	    return $pay_id;
	}
}
