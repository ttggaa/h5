<?php

class UsergamesController extends F_Controller_Backend
{
    protected function beforeList()
    {
        $params = parent::beforeList();
        $params['op'] = F_Helper_Html::Op_Null;
        if( isset($params['conditions']) ) {
            if( preg_match('#last_play=\'([^\']+)\'#', $params['conditions'], $match) ) {
                $begin = strtotime($match[1].' 00:00:00');
                $end = strtotime($match[1].' 23:59:59');
                $params['conditions'] = preg_replace('#last_play=\'([^\']+)\'#', "last_play BETWEEN {$begin} AND {$end}", $params['conditions']);
            }
            if( preg_match('#register_time=\'([^\']+)\'#', $params['conditions'], $match) ) {
                $begin = "'".$match[1].' 00:00:00'."'";
//                $begin = strtotime($match[1].' 00:00:00');
                $end =  "'".$match[1].' 23:59:59'. "'";
//                $end = strtotime($match[1].' 23:59:59');
                $params['conditions'] = preg_replace('#register_time=\'([^\']+)\'#', "register_time BETWEEN {$begin} AND {$end}", $params['conditions']);
            }
        }
        //按时间最新的在前面
        $params['orderby']='last_play desc';
        return $params;
    }
    
    public function editAction()
    {
        exit;
    }
    
    public function updateAction()
    {
        exit;
    }
    
    public function deleteAction()
    {
        exit;
    }
}
