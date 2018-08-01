<?php

class ApiController extends Yaf_Controller_Abstract
{
    //游戏直充
    public function payAction()
    {
        $req = $this->getRequest();
        $user_id = $req->get('user_id', 0);
        $sign = $req->get('sign', '');
        if( $user_id < 1 || strlen($sign) != 32 ) {
            exit('Params error.');
        }
        $sign = preg_replace('/[^0-9a-f]+/', '', $sign);
        
        $arr = array(
            'user_id' => $user_id,
            'username' => $req->get('username', ''),
            'game_id' => $req->get('game_id', 0),
            'money' => $req->get('money', 0),
            'subject' => $req->get('subject', ''),
            'body' => $req->get('body', ''),
            'cp_order' => $req->get('cp_order', ''),
            'cp_return' => $req->get('cp_return', ''),
            'time' => $req->get('time', 0),
            'extra' => $req->get('extra', ''),
        );
        if( isset($_GET['server_id']) || isset($_POST['server_id']) ) {
            $srv_id_i = $req->get('server_id', 0);
            $srv_id_s = $req->get('server_id', '');
            if( strval($srv_id_i) === $srv_id_s ) {
                $arr['server_id'] = $srv_id_i;
            }
        }
        $time = time();
        if( ($arr['time'] + 300) < $time ) {
            exit('Time error.');
        }
        
//        if( $arr['server_id'] ) {
//            $m_server = new ServerModel();
//            $server = $m_server->fetch("server_id='{$arr['server_id']}'", 'name,game_name,login_url,sign_key');
//            $server_name = $server['name'];
//            $game_name = $server['game_name'];
//            $login_url = $server['login_url'];
//            $sign_key = $server['sign_key'];
//        } else {
            $m_game = new GameModel();
            $game = $m_game->fetch("game_id='{$arr['game_id']}'", 'name,login_url,sign_key');
            $server_name = '';
            $game_name = $game['name'];
            $login_url = $game['login_url'];
            $sign_key = $game['sign_key'];
//        }
        
        ksort($arr);
        $md5 = md5(implode('', $arr).$sign_key);
        if( strcmp($sign, $md5) != 0 ) {
            exit('Sign error.');
        }
        $subject = $arr['subject'];
        $body = $arr['body'];
        $subject = $arr['subject'];
        unset($arr['subject'], $arr['body'], $arr['time']);
        
        $m_pay = new PayModel();
        $arr['pay_id'] = $m_pay->createPayId();
        $arr['to_uid'] = $arr['user_id'];
        $arr['username'] = urldecode($arr['username']); //解决有些商户并没有urldecode的兼容性问题
        $arr['to_user'] = $arr['username'];
        $arr['add_time'] = $time;
        $arr['game_name'] = $game_name;
        $arr['server_name'] = $server_name;
        $arr['type'] = 'pigpay';
        $arr['cp_return'] = $arr['cp_return'] ? $arr['cp_return'] : '1';
        $rs = $m_pay->insert($arr, false);
        if( ! $rs ) {
            exit('Save payment failed.');
        }
        
        //判断用户是否有足够的平台币
//        $m_user = new UsersModel();
//        $user = $m_user->fetch("user_id='{$user_id}'", 'money');
//        if( $user && $user['money'] >= $arr['money'] ) {
//            $return = $arr['cp_return'];
//            if( $return == '1' ) {
//                $return = Game_Login::redirect($user_id, $arr['username'], $arr['game_id'], $arr['server_id'], $login_url, $sign_key);
//            }
//            $this->forward('api', 'gotop', array('pay'=>$arr, 'subject'=>$subject, 'deposit'=>$user['money'], 'return'=>$return));
//            return false;
//        }
        
//        if( empty($subject) ) {
//            $conf = Yaf_Application::app()->getConfig();
//            if( $arr['server_id'] ) {
//                $subject = "充值到《{$arr['game_name']}，{$arr['server_name']}》 - {$conf['application']['sitename']}";
//            } else {
//                $subject = "充值到《{$arr['game_name']}》 - {$conf['application']['sitename']}";
//            }
//        }
        
//        $pig_pay = new Pay_Pigpay_Mobile();
//        $url = $pig_pay->redirect($arr['pay_id'], $arr['money'], $subject, $body, $arr['username']);
//        $pay_pigpay_mobile=
//        var_dump($arr);
//        die;
        $pay['pay_id']=$arr['pay_id'];
        $pay['to_user']=$arr['username'];
        $pay['user_id']=$arr['user_id'];
        $pay['money']=$arr['money'];
        $pay['cp_order']=$arr['cp_order'];
        $pay['cp_return']=$arr['cp_return'];
        $pay['extra']=$arr['extra'];
        $pay['game_name']=$arr['game_name'];
        $pay['server_name']=$arr['server_name'];
        $pay['subject']=$subject?$subject:$arr['game_name'].'游戏充值';
        $pay['body']=$body?$body:'元宝';
        $this->getView()->assign('pay', $pay);
        $conf = Yaf_Application::app()->getConfig();
        $this->getView()->assign(array('parities'=>$conf->application->parities));

//          $this->redirect("/pay/numstype.html?server_id={$arr['server_id']}&game_id={$arr['game_id']}&money={$arr['money']}&cp_order={$arr['cp_order']}&cp_return={$arr['cp_return']}");
    }

    /**
     * 通过平台币充值
     */
    public function payByDepositAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $params=$_REQUEST;
        $user_id=$params['user_id'];
        $pay_id=$params['jinzhue'];
        //查询订单
        $conds = "pay_id='{$pay_id}'";
        $m_pay = new PayModel();
        $pay = $m_pay->fetch($conds);
        if( (int)$pay['finish_time'] > 0 ) {
            echo json_encode(['code'=>1,'message'=>'请勿重复提交']);
            return false;
        }
        $arr=array(
            'money'=>$pay['money'],
            'username'=>$pay['username'],
            'game_id'=>$pay['game_id'],
            'server_id'=>$pay['server_id'],
            'cp_return'=> $pay['cp_return'] ?$pay['cp_return']: '1'
        );
        //游戏信息
        $m_game = new GameModel();
        $game = $m_game->fetch("game_id='{$arr['game_id']}'", 'name,login_url,sign_key');
        $server_name = '';
        $game_name = $game['name'];
        $login_url = $game['login_url'];
        $sign_key = $game['sign_key'];
        $m_user = new UsersModel();
        $user = $m_user->fetch("user_id='{$user_id}'", 'money');
        if( $user && $user['money'] >= $arr['money'] ) {
            $return = $arr['cp_return'];
            if( $return == '1' ) {
                $return = Game_Login::redirect($user_id, $arr['username'], $arr['game_id'], $arr['server_id'], $login_url, $sign_key);
            }
//            'pay_id' => $_REQUEST['jinzhue'],
//            'trade_no' => $_REQUEST['OrderID'],
//            'pay_type' => $_REQUEST['jinzhuc'],
            //减扣平台币
            $m_user = new UsersModel();
            $now_money=(int)($user['money']-$arr['money']);
            $m_user->update(array('money'=>$now_money),"user_id='{$user_id}'");
//            $this->forward('notify', 'pigpay',$params);
            $trade_no= date('YmdHis').rand(1,9999);
            $url = 'http://'.$_SERVER['SERVER_NAME']."/notify/pigpay?jinzhue={$params['jinzhue']}&jinzhuc={$params['jinzhuc']}&OrderID={$trade_no}";
            $curl = new F_Helper_Curl();
            $rs = $curl->request($url);
            if($rs=='success'){
                echo json_encode(['code'=>0,'message'=>$return]);
            }else{
                echo json_encode(['code'=>1,'message'=>'发货失败']);
            }
            return false;
        }else{
            echo json_encode(['code'=>1,'message'=>'平台币不足']);
            return false;
        }
    }
    
    //跳转到顶层框架
    public function gotopAction()
    {
        $req = $this->getRequest();
        $arr = $req->getParams();
        if( count($arr) != 4 || empty($arr['pay']) || empty($arr['deposit']) || empty($arr['return']) ) {
            exit;
        }
        $this->getView()->assign($arr);
    }
    //选择支付方式
    public function typeAction()
    {
        $req = $this->getRequest();
        if( ! $req->isPost() ) {
            exit;
        }
        $arr['pay'] = $req->getPost('pay', array());
        $arr['subject'] = $req->getPost('subject', '');
        $arr['deposit'] = $req->getPost('deposit', 0);
        $arr['return'] = $req->getPost('return', '');
        if( empty($arr['pay']) || empty($arr['deposit']) || empty($arr['return']) ) {
            exit;
        }
        
        $conf = Yaf_Application::app()->getConfig();
        $arr['parities'] = $conf->application->parities;
        
        $this->getView()->assign($arr);
    }
}
