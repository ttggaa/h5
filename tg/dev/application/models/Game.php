<?php

class GameModel extends F_Model_Pdo
{
	protected $_table = 'game';
	protected $_primary = 'game_id';
	
	//类型
	public $_types = array('推荐','独家','BT版','满V版','GM版');
    public $_game_types = array('h5','手游');
    //经典分类
	public $_classic = array(
	    '角色扮演', '动作过关', '休闲娱乐', '街机童年', '竞速狂飙', '卡牌回合', '体育竞技', '解密探险', '棋牌竞技', '音乐舞蹈',
	);
	//角标
	public $_corner = array(
	    'normal' => '-',
	    'promotion' => '优惠',
	    'discount' => '折扣',
	    'rebate' => '返利',
	    'activity' => '活动',
	);
	//后标
	public $_labels = array(
	    'normal' => '-',
	    'hot' => '人气',
	    'new' => '上新',
	);
	//渠道
	public $_channels = array(
	    'self' => '-',
	    'egret' => '白鹭',
	);
	public $_load_types = array(
	    'iframe' => 'iframe标签',
	    'object' => 'object标签',
	    'redirect' => '跳转（域名会变，慎用）',
	);
	public $_screens = array(
	    'auto' => '自适应',
	    'vertical' => '竖屏',
	    'horizontal' => '横屏',
	);
	
	//特殊分类
	public $_special_all = array('all' => '全部');
	public $_special = array(
	    'new' => '最新',
	    'support' => '人气',
	    'grade' => '星级',
	);
	
	public function __construct()
	{
	    parent::__construct('h5');
	}
	
	public function getTableLabel()
	{
		return '游戏';
	}
	
	public function getFieldsLabel()
	{
		return array(
			'game_id' => '游戏ID',
			'name' => '名称',
		    'game_type'=>'游戏类型',
		    'type' => '分类',
		    'classic' => '经典分类',
		    'divide_into' => function(&$row){
                if( empty($row) ) return '分成比例';
                if($_SESSION['cps_type']==3){
                    $admin=new AdminModel();
                    $divide_into=$admin->fetch(['admin_id'=>$_SESSION['admin_id']],'divide_into');
                    return $divide_into['divide_into'];
                }
                return $row['divide_into'];
            },
//		    'corner' => function(&$row){
//		        if( empty($row) ) return '角标';
//		        return $this->_corner[$row['corner']];
//		    },
//		    'label' => function(&$row){
//		        if( empty($row) ) return '后标';
//		        return $this->_labels[$row['label']];
//		    },
//		    'giftbag' => function(&$row){
//		        if( empty($row) ) return '礼包ID';
//		        return $row['giftbag'] ? "<a href=\"/admin/giftbag/list?search[gift_id]={$row['giftbag']}\">{$row['giftbag']}</a>" : '-';
//		    },
		    'logo' => function(&$row){
		        if( empty($row) ) return '图标';
		        return $row['logo'] ? "<a class=\"lightbox\" href=\"{$row['logo']}\"><img style=\"max-width:32px;\" src=\"{$row['logo']}\"></a>" : '';
		    },
		    'support' => function(&$row){
                if( empty($row) ) return '推广链接';
                if($row['game_type']=='手游'){
                    $channel_id=$_SESSION['admin_id'];
                    return '<a href="http://yun.zyttx.com/index/apkgame?game_id='.$row['game_id'].'&tg_channel='.$channel_id.'">http://yun.zyttx.com/index/apkgame?game_id='.$row['game_id'].'&tg_channel='.$channel_id.' </a>';
                }else{
                    return "http://".$_SESSION["admin_id"].".h5.zyttx.com/game/play.html?game_id={$row['game_id']}";
                }
            },
            'visible' => function(&$row){
                if( empty($row) ) return '推广的网站和APP是否显示';
                switch ($row['visible']){
                    case 0:
                        return '否';
                    case 1:
                        return '全显示';
                    case 2:
                        return '未显示';
                }
//		        return $row['visible'] ? '是' : '-';
            },
            'material_url' =>
                function(&$row){
                    if( empty($row) ) return '素材下载';
                    return "<a href=\"{$row['material_url']}\">{$row['material_url']}</a>";
                }
            ,
//            'apk_url' =>
//                function(&$row){
//                    if( empty($row) ) return 'apk包';
//                    $channel_id=$_SESSION['admin_id'];
//                    if(file_exists("game/apk/{$row['game_id']}/".$channel_id.'.apk')){
//                            $a='<a href="/game/apk/'.$row['game_id'].'/'.$channel_id.'.apk">地址</a>';
//                    }else{
//                            $a='<a href="/admin/admin/akpgame?game_id='.$row['game_id'].'">获取</a>';
//                    }
//                    return $a;
//                }
//            ,
//		    'grade' => '评级',
//		    'weight' => '排序',
//		    'version' => '当前版本',
//		    'trade_money' => '总流水',
//			'play_times' => '游戏次数',
		    'add_time' => function(&$row){
		        if( empty($row) ) return '上架时间';
		        return substr($row['add_time'], 0, 10);
		    },
//		    'visible' => function(&$row){
//		        if( empty($row) ) return '是否可见';
//		        return $row['visible'] ? '是' : '-';
//		    },
//		    'channel' => function(&$row){
//		        if( empty($row) ) return '合作渠道';
//		        return $this->_channels[$row['channel']];
//		    },
		);
	}
	
	public function getFieldsPadding()
	{
	    return array(
	        function(&$row){
	            if( empty($row) ) return '区/服列表';
	            return "<a href=\"/admin/server/list?search[game_id]={$row['game_id']}\">查看</a>";
	        }
	    );
	}
	
	public function getFieldsSearch()
	{
	    return array(
	        'type' => array('分类', 'select', $this->_types, null),
	        'game_type' => array('游戏类型', 'select', $this->_game_types, null),
	        'name' => array('游戏名字', 'input', null, ''),
	        'add_begin' => array('开始日期', 'datepicker', null, ''),
	        'add_end' => array('结束日期', 'datepicker', null, ''),
	    );
	}
	
	//上线
	public function online($game_id)
	{
	    $conds = "game_id='{$game_id}'";
	    $this->update(array('visible'=>1), $conds);
	}
	//下线
	public function offline($game_id)
	{
	    $conds = "game_id='{$game_id}'";
	    $this->update(array('visible'=>0), $conds);
	}
}
