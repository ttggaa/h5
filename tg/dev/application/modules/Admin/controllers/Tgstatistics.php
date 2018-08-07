<?php

class TgstatisticsController extends F_Controller_Backend
{
    protected function beforeList()
    {
        //生成统计报表
        $m_stat = new TgstatisticsModel();
        $s = Yaf_Session::getInstance();
        $channel_ids=$s->get('channel_ids_condition');
        $m_user = new UsersModel();
        $stat = $m_user->fetchAll("tg_channel in {$channel_ids} GROUP BY tg_channel",
            1, 200000, 'tg_channel AS channel, COUNT(user_id) AS reg_people');
        foreach ($stat as $row)
        {
            if(!$m_stat->fetch(['channel'=>$row['channel']])) {
                $stat_id = $m_stat->insert($row);
            }
        }
        //生成统计报表
        $m_pay = new PayModel();
        $stat = $m_pay->fetchAll("pay_time>0 AND type<>'deposit' AND tg_channel in {$channel_ids} GROUP BY tg_channel",
            1, 200000, 'tg_channel AS channel, COUNT(pay_id) AS pay_times, COUNT(DISTINCT user_id) AS pay_people, SUM(money) AS pay_money');
        foreach ($stat as $row)
        {
                $m_stat->update($row, "channel='{$row['channel']}'");
        }

        $params['op'] = F_Helper_Html::Op_Null;
        $conds = '';
        $search = $this->getRequest()->getQuery('search', array());
        $s = Yaf_Session::getInstance();
        $admin_id=$s->get('admin_id');
        if( $search ) {
            $cmm = '';
            foreach ($search as $k=>$v)
            {
                if( empty($v) ) {
                    continue;
                }
                $conds .= "{$cmm}{$k}='{$v}'";
                $cmm = ' AND ';
            }
            $conds.="AND channel in {$channel_ids}";
        }else{
            $conds.="channel in {$channel_ids}";
        }
        $params['conditions']=$conds;
        return $params;
    }
}