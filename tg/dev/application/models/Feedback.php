<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/29/029
 * Time: 10:03
 */

class FeedbackModel extends F_Model_Pdo
{
    protected $_table = 'feed_back';
    protected $_primary = 'feed_id';
//'游戏问题','代理问题','申请返利','其他'
    public $_types = array('游戏问题','代理问题','申请返利','其他');
    public $_status = array('未处理','已处理');
    public function getFieldsLabel()
    {
        return array(
            'feed_id' => '问题ID',
            'title' => function(&$row){
                if( empty($row) ) return '标题';
                return "<a href=\"/admin/feedback/details?feed_id={$row['feed_id']}\">{$row['title']}</a>";
            },
            'type' => '问题类型',
            'status' => '问题状态',
            'create_time' => function(&$row){
                if( empty($row) ) return '创建时间';
                return date('Y-m-d H:i:s',$row['create_time']);
            },
            'admin_id' => function(&$row){
                if( empty($row) ) return '渠道id';
                return $row['admin_id'];
            }
        );
    }
    public function getFieldsSearch()
    {
        return array(
            'status' => array('状态', 'select', $this->_status, null),
            'type' => array('问题类型', 'select', $this->_types, null),
            'title' => array('关键字', 'input', null, ''),
        );
    }
//    public function getFieldsPadding()
//    {
////        return array(
////            function(&$row){
////                if( empty($row) ) return '详情';
////                return "<a href=\"/admin/feedback/details?feed_id={$row['feed_id']}\">详情</a>";
////            }
////        );
//    }
}