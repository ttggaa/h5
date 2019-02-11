<?php

class StatbygameController extends F_Controller_Backend
{
    protected function beforeList()
    {
        $params['orderby'] = 'ymd DESC,game_id DESC';
        $params['op'] = F_Helper_Html::Op_Null;
        
        $search = $this->getRequest()->getQuery('search', array());
        $conds = '';
        $comma = '';
        if( !empty($search['ymd_begin']) || !empty($search['ymd_end']) ) {
            $search['ymd_begin'] = !empty($search['ymd_begin']) ? str_replace('-', '', $search['ymd_begin']) : '19700101';
            $search['ymd_end'] = !empty($search['ymd_end']) ? str_replace('-', '', $search['ymd_end']) : date('Ymd');
            $conds = "ymd BETWEEN {$search['ymd_begin']} AND {$search['ymd_end']}";
            $comma = ' AND ';
        }
        if( !empty($search['game_id']) ) {
            $conds .= "{$comma}game_id='{$search['game_id']}'";
        }
        $params['conditions'] = $conds;
        
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
