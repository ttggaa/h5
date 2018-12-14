<?php

class ApiController extends Yaf_Controller_Abstract
{
    private $_ck_ui_name = 'u_auth';

    //游戏直充
    public function payAction()
    {
        $req = $this->getRequest();
        $user_id = $req->get('user_id', 0);
        $sign = $req->get('sign', '');
        if ($user_id < 1 || strlen($sign) != 32) {
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
        if (isset($_GET['server_id']) || isset($_POST['server_id'])) {
            $srv_id_i = $req->get('server_id', 0);
            $srv_id_s = $req->get('server_id', '');
            if (strval($srv_id_i) === $srv_id_s) {
                $arr['server_id'] = $srv_id_i;
            }
        }
        $time = time();
        if (($arr['time'] + 300) < $time) {
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
        $md5 = md5(implode('', $arr) . $sign_key);
        if (strcmp($sign, $md5) != 0) {
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
        $game_id = $req->get('game_id', 0);
        if ($game_id) {
            $ip = $this->getIp();
            $host = Yaf_Registry::get('config')->redis->host;
            $port = Yaf_Registry::get('config')->redis->port;
            $conf = array('host' => $host, 'port' => $port);
            $redis = F_Helper_Redis::getInstance($conf);
            $back_url = $redis->get('global_url' . $ip);
            $cp_return = "http://" . $back_url . "/game/play.html?game_id={$req->get('game_id')}";
        } else {
            $cp_return = $req->get('cp_return', '');
        }
        $arr['cp_return'] = $cp_return;
        $user = new UsersModel();
        $user_info = $user->fetch(['user_id' => $user_id], 'tg_channel,player_channel');
        $arr['tg_channel'] = $user_info['tg_channel'];
        $arr['player_channel'] = $user_info['player_channel'];
        $rs = $m_pay->insert($arr, false);
        if (!$rs) {
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
        $pay['pay_id'] = $arr['pay_id'];
        $pay['to_user'] = $arr['username'];
        $pay['user_id'] = $arr['user_id'];
        $pay['money'] = $arr['money'];
        $pay['cp_order'] = $arr['cp_order'];
        $pay['cp_return'] = $arr['cp_return'];
        $pay['extra'] = $arr['extra'];
        $pay['game_name'] = $arr['game_name'];
        $pay['server_name'] = $arr['server_name'];
        $pay['subject'] = $subject ? $subject : $arr['game_name'] . '游戏充值';
        $pay['body'] = $body ? $body : '元宝';
        $this->getView()->assign('pay', $pay);
        $conf = Yaf_Application::app()->getConfig();
        $this->getView()->assign(array('parities' => $conf->application->parities));

//          $this->redirect("/pay/numstype.html?server_id={$arr['server_id']}&game_id={$arr['game_id']}&money={$arr['money']}&cp_order={$arr['cp_order']}&cp_return={$arr['cp_return']}");
    }

    /**
     * 通过平台币充值
     */
    public function payByDepositAction()
    {
        Yaf_Dispatcher::getInstance()->disableView();
        $params = $_REQUEST;
        $user_id = $params['user_id'];
        $pay_id = $params['jinzhue'];
        //查询订单
        $conds = "pay_id='{$pay_id}'";
        $m_pay = new PayModel();
        $pay = $m_pay->fetch($conds);
        if ((int)$pay['finish_time'] > 0) {
            echo json_encode(['code' => 1, 'message' => '请勿重复提交']);
            return false;
        }
        $arr = array(
            'money' => $pay['money'],
            'username' => $pay['username'],
            'game_id' => $pay['game_id'],
            'server_id' => $pay['server_id'],
            'cp_return' => $pay['cp_return'] ? $pay['cp_return'] : '1'
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
        if ($user && $user['money'] >= $arr['money']) {
            $return = $arr['cp_return'];
            if ($return == '1') {
                $return = Game_Login::redirect($user_id, $arr['username'], $arr['game_id'], $arr['server_id'], $login_url, $sign_key);
            }
//            'pay_id' => $_REQUEST['jinzhue'],
//            'trade_no' => $_REQUEST['OrderID'],
//            'pay_type' => $_REQUEST['jinzhuc'],
            //减扣平台币
            $m_user = new UsersModel();
            $now_money = (int)($user['money'] - $arr['money']);
            $rs1 = $m_user->update(array('money' => $now_money), "user_id='{$user_id}'");
//            $this->forward('notify', 'pigpay',$params);
            if ($rs1) {
                $trade_no = date('YmdHis') . rand(1, 9999);
                $url = 'http://' . $_SERVER['SERVER_NAME'] . "/notify/pigpay?jinzhue={$params['jinzhue']}&jinzhuc={$params['jinzhuc']}&OrderID={$trade_no}";
                $curl = new F_Helper_Curl();
                $rs = $curl->request($url);
                if ($rs == 'success' || $rs == 'ok') {
                    echo json_encode(['code' => 0, 'message' => $return]);
                } else {
                    echo json_encode(['code' => 1, 'message' => '游戏发货失败,请联系客服处理']);
                }
            } else {
                echo json_encode(['code' => 1, 'message' => '发货失败']);
            }
            return false;
        } else {
            echo json_encode(['code' => 1, 'message' => '平台币不足']);
            die;
            return false;
        }
    }

    //充值到平台
    public function depositAction()
    {
//        Yaf_Dispatcher::getInstance()->disableView();
        $params = $_GET;
        $open_id = $params['open_id'];
        $user_id = $open_id;
        $m_user = new UsersModel();
        $user = $m_user->fetch(['user_id' => $user_id]);
        if (empty($user)) {
            //缓存渠道id
            $assign['status'] = 'fail';
            $assign['msg'] = '账号异常,联系管理员';
            exit(json_encode($assign));
        }
        //查询订单
        $set_arr = array_merge(array('to_uid' => $user['user_id'], 'to_user' => $user_id), array('game_id' => 0, 'game_name' => '', 'server_id' => 0, 'server_name' => ''));
        $pay = $this->initPayInfo($user);
        $pay = array_merge($pay, $set_arr);
        $conf = Yaf_Application::app()->getConfig();
        $this->getView()->assign(array('parities' => $conf->application->parities, 'pay' => $pay));
    }

    private function initPayInfo($userinfo)
    {
        $this->unsetPayInfo();
        $m_pay = new PayModel();
        $pay = array(
            'pay_id' => $m_pay->createPayId(),
            'user_id' => $userinfo['user_id'],
            'username' => $userinfo['username'],
            'to_uid' => $userinfo['user_id'],
            'to_user' => $userinfo['username'],
            'game_id' => 0,
            'game_name' => '',
            'server_id' => 0,
            'server_name' => '',
            'money' => 0,
            'deposit' => 0,
            'type' => '',
            'cp_order' => '',
            'tg_channel' => $userinfo['tg_channel'],
            'player_channel' => $userinfo['player_channel'],
        );
        $sess = Yaf_Session::getInstance();
        $sess->set('pay_info', $pay);
        return $pay;
    }

    //跳转到第三方支付网站
    public function checkoutAction()
    {
        Yaf_Dispatcher::getInstance()->disableView();
        $sess = Yaf_Session::getInstance();
        $pay = $sess->get('pay_info');
        $req = $this->getRequest();
        $money = $req->get('money', 0);
        $type = $req->get('type', '');
        $m_pay = new PayModel();
        $ck_arr = $m_pay->_types;
        $ck_arr['deposit'] = '平台币';
        if (($type && !array_key_exists($type, $ck_arr)) || ($money < 1 && $pay['money'] < 1)) {
            echo json_encode(['code' => 1, 'message' => '订单生成失败，请重试！', 'info' => false], true);
            return false;
        }

        $set = null;
        if ($type) {
            $set['type'] = $type;
        }
        if ($money > 0) {
            if ($money > 60000) {
                $money = 60000;
            }
            $set['money'] = $money;
        } elseif ($pay['money'] > 60000) {
            $set['money'] = 60000;
        }
        if ($set) {
            $pay = array_merge($pay, $set);
        }
        $time = time();
        $pay['trade_no'] = '';
        $pay['add_time'] = $time;
        $rs = $m_pay->insert($pay, false);
        if (!$rs) {
            $this->unsetPayInfo();
            echo json_encode(['code' => 1, 'message' => '订单生成失败，请重试！', 'info' => false], true);
        } else {
            $pay_id = $pay['pay_id'];
            echo json_encode(['code' => 0, 'message' => '订单生成功！', 'info' => $pay_id]);
        }
        $this->unsetPayInfo();
    }

    private function unsetPayInfo()
    {
        $sess = Yaf_Session::getInstance();
        $sess->set('pay_info', null);
    }

    //跳转到顶层框架
    public function gotopAction()
    {
        $req = $this->getRequest();
        $arr = $req->getParams();
        if (count($arr) != 4 || empty($arr['pay']) || empty($arr['deposit']) || empty($arr['return'])) {
            exit;
        }
        $this->getView()->assign($arr);
    }

    //选择支付方式
    public function typeAction()
    {
        $req = $this->getRequest();
        if (!$req->isPost()) {
            exit;
        }
        $arr['pay'] = $req->getPost('pay', array());
        $arr['subject'] = $req->getPost('subject', '');
        $arr['deposit'] = $req->getPost('deposit', 0);
        $arr['return'] = $req->getPost('return', '');
        if (empty($arr['pay']) || empty($arr['deposit']) || empty($arr['return'])) {
            exit;
        }

        $conf = Yaf_Application::app()->getConfig();
        $arr['parities'] = $conf->application->parities;

        $this->getView()->assign($arr);
    }

    /**
     * 获取游戏列表
     * game_type 游戏类型 h5\手游
     */
    public function gameListAction()
    {
        $request = $_POST;
        $this->checkParams($request, ['game_type']);
        $game_type = $request['game_type'];
        $m_game = new GameModel();
        $assign['top_games'] = $m_game->getTopByType($pn = 1, $limit = 4, $type = '', $game_type);//分类
        $assign['new_games'] = $m_game->getListByAttr('new', 1, 3, $game_type);
        $assign['hot_games'] = $m_game->getListByAttr('hot', 1, 3, $game_type);
//        $assign['article_list'] = $m_game->getListByAttr('hot', 1, 5, $game_type);
        $m_article = new ArticleModel();
        $list = $m_article->fetchAll("visible=1 and type='活动' or type='公告'", 1, 3, 'article_id,cover,title,up_time', 'weight ASC,article_id DESC');
        foreach ($list as &$row) {
            $row['up_time'] = $m_article->formatTime($row['up_time']);
        }
        $assign['info'] = $list;
        echo json_encode($assign, true);
        die;
    }
    /**
     * 获取游戏列表v2
     * game_type 游戏类型 h5\手游
     */
    public function gameListV2Action()
    {
        $request = $_POST;
        $this->checkParams($request, ['game_type']);
        $game_type = $request['game_type'];
        $m_game = new GameModel();
//        $assign['top_games'] = $m_game->getTopByType($pn = 1, $limit = 4, $type = '', $game_type);//分类
        $assign['recommend_games'] = $m_game->getListByAttr('recommend', 1, 4, $game_type);
        $assign['new_games'] = $m_game->getListByAttr('new', 1, 4, $game_type);
        $assign['hot_games'] = $m_game->getListByAttr('hot', 1, 4, $game_type);
//        $assign['article_list'] = $m_game->getListByAttr('hot', 1, 5, $game_type);
        $m_article = new ArticleModel();
        $list = $m_article->fetchAll("visible=1 and type='活动' or type='公告'", 1, 3, 'article_id,cover,title,up_time', 'weight ASC,article_id DESC');
        foreach ($list as &$row) {
            $row['up_time'] = $m_article->formatTime($row['up_time']);
        }
        $assign['info'] = $list;
        echo json_encode($assign, true);
        die;
    }

    /**
     * 获取游戏列表
     * game_type 游戏类型 h5\手游
     */
    public function gameListByTypeAction()
    {
        $request = $_POST;
        $this->checkParams($request, ['game_type', 'tc', 'pn']);
        $game_type = $request['game_type'];
        $tc = $request['tc'];
        $tc = preg_replace('/[\%\*\'\"\\\]+/', '', $tc);
        $pn = $request['pn'] ?? 1;
        $limit = 9;
        $order = 'weight ASC';
        $selects = 'game_id,name,logo,corner,label,giftbag,support,grade,in_short,play_times,game_type,package_name,package_size';
        $m_game = new GameModel();
        $games = array();
        if ($tc == '全部') {
            $games = $m_game->fetchAll("visible=1 and game_type='{$game_type}'", $pn, $limit, $selects, $order);
        } elseif (in_array($tc, $m_game->_classic)) {
            $games = $m_game->fetchAll("visible=1 AND  classic='{$tc}' and game_type='{$game_type}'", $pn, $limit, $selects, $order);
        }
        foreach ($games as &$row) {
            $row['grade'] = $m_game->gradeHtml($row['grade']);
            $row['support'] = $m_game->supportFormat($row['support'] + $row['play_times']);
        }
        echo json_encode($games, true);
        die;
    }

    function getArticleListAction()
    {
        $req = $this->getRequest();
        $type = $req->getPost('type', '');
        $type = preg_replace('/[\'\"\\\]+/', '', $type);
        $pn = $req->getPost('pn', 1);
        $limit = 13;
        $m_article = new ArticleModel();
        if ($type == '综合') {
            $conds = 'visible=1';
        } else if (in_array($type, $m_article->_types)) {
            $conds = "type='{$type}' AND visible=1";
        } else {
            exit;
        }
        $conds .= " and type!='代理公告'";//过滤公告
        $list = $m_article->fetchAll($conds, $pn, $limit, 'article_id,cover,title,up_time', 'weight ASC,article_id DESC');
        foreach ($list as &$row) {
            $row['up_time'] = $m_article->formatTime($row['up_time']);
        }
        $assign['list'] = $list;
        $assign['type'] = $type;
        echo json_encode($assign, true);
        die;
    }

    /**
     * 获取游戏
     */
    function getGamesBySortAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['game_type', 'pn', 'type']);
        $game_type = $request['game_type'];
        $type = $request['type'];
        $m_game = new GameModel();
        if ($type == 'new') {
            $res = $m_game->getListByAttr('new', $request['pn'], 10, $game_type);
//            $assign=$res['new_games'];
        } elseif ($type == 'hot') {
            $res = $m_game->getListByAttr('hot', $request['pn'], 10, $game_type);
//            $assign=$res['hot_games'];
        } elseif($type == 'recommend'){
           $res = $m_game->getListByAttr('recommend', $request['pn'], 10, $game_type);
        }elseif($type == 'bt'){
            $res = $m_game->getListByAttr('bt', $request['pn'], 10, $game_type);
        }elseif($type == 'mv'){
            $res = $m_game->getListByAttr('mv', $request['pn'], 10, $game_type);
        }elseif($type == 'gm'){
            $res = $m_game->getListByAttr('gm', $request['pn'], 10, $game_type);
        }elseif($type == 'gf'){
            $res = $m_game->getListByAttr('gf', $request['pn'], 10, $game_type);
        }
        echo json_encode($res, true);
        die;
    }
    /**
     * 获取游戏
     */
    function getGamesBySortV2Action()
    {
        $request = $_GET;
        $this->checkParams($request, ['index', 'type', 'pn']);
        if($request['index']==1){
            $game_type='手游';
        }else{
            $game_type='h5';
        }
        $type = $request['type'];
        $m_game = new GameModel();
        if ($type == 'new') {
            $res[$game_type] = $m_game->getListByAttr('new', $request['pn'], 10, $game_type);
//            $assign=$res['new_games'];
        } elseif ($type == 'hot') {
            $res[$game_type] = $m_game->getListByAttr('hot', $request['pn'], 10, $game_type);
//            $assign=$res['hot_games'];
        } elseif($type == 'recommend'){
            $res[$game_type] = $m_game->getListByAttr('recommend', $request['pn'], 10, $game_type);
        }elseif($type == 'bt'){
            $res[$game_type] = $m_game->getListByAttr('bt', $request['pn'], 10, $game_type);
        }elseif($type == 'mv'){
            $res[$game_type] = $m_game->getListByAttr('mv', $request['pn'], 10, $game_type);
        }elseif($type == 'gm'){
            $res[$game_type] = $m_game->getListByAttr('gm', $request['pn'], 10, $game_type);
        }elseif($type == 'gf'){
            $res[$game_type] = $m_game->getListByAttr('gf', $request['pn'], 10, $game_type);
        }
        echo json_encode($res, true);
        die;
    }

    /**
     * 获取文章详情
     */
    function getArticleDeatilAction()
    {
        $request = $_GET;
        $article_id = $request['article_id'];
        $m_article = new ArticleModel();
        $assign['info'] = $m_article->fetch("article_id='{$article_id}' AND visible=1");
        //正则匹配标签，加上绝对路径<img src="/
        $assign['info'] = str_replace("<img src=\"", "<img src=\"http://h5.zyttx.com", $assign['info']);
        echo json_encode($assign, true);
        die;
    }

    /**
     * 获取查询列表
     */
    public function gameSearchListAction()
    {
        $request = $_POST;
        $this->checkParams($request, ['game_type', 'name', 'pn']);
        $game_type = $request['game_type'];
        $pn = $request['pn'] ?? 1;
        $limit = 20;
        $order = 'game_id DESC';
        $selects = 'game_id,name,logo,corner,label,giftbag,support,grade,in_short,play_times,game_type,package_name,package_size';
        $m_game = new GameModel();
        $m_gift = new GiftbagModel();
        $games = $m_game->fetchAll("visible=1 and game_type='{$game_type}' and name like '%{$request['name']}%'", $pn, $limit, $selects, $order);
        $gifts = $m_gift->fetchAllBySql("select h5.giftbag.name,game_name,nums,used,content,gift_id,h5.game.logo from h5.giftbag  inner join h5.`game`  on h5.giftbag.game_id = h5.`game`.game_id where h5.`game`.game_type =  '{$game_type}' and h5.giftbag.game_name like '%{$request['name']}%'");
        foreach ($gifts as &$value) {
            $value['content'] = unserialize($value['content']);
            if ($request['user_id'] ?? 0) {
                $rs = $m_gift->fetchBySql("select cdkey from h5.user_cdkey where user_id = {$request['user_id']} and gift_id = {$value['gift_id']}");
                $value['cdkey'] = $rs['cdkey'];
            }
        }
        $assign['game'] = $games;
        $assign['gift'] = $gifts;
        echo json_encode($assign, true);
        die;
    }

    /**
     * 获取查询列表
     */
    public function giftListAction()
    {
        $request = $_POST;
        $this->checkParams($request, ['game_type', 'pn']);
        $game_type = $request['game_type'];
        $pn = $request['pn'];
        $limit = 11;
        $offset = ($pn - 1) * $limit;
        $order = 'h5.giftbag.game_id DESC';
        $m_gift = new GiftbagModel();
        $gifts = $m_gift->fetchAllBySql("select h5.giftbag.name,howget,game_name,nums,used,content,gift_id,h5.game.logo from h5.giftbag  inner join h5.`game`  on h5.giftbag.game_id = h5.`game`.game_id where h5.`game`.game_type =  '{$game_type}' order by {$order}  LIMIT {$offset},{$limit} ");
//        $log="select h5.giftbag.name,game_name,nums,used,content,h5.giftbag.gift_id,h5.game.logo from h5.giftbag  inner join h5.`game`  on h5.giftbag.game_id = h5.`game`.game_id where h5.`game`.game_type =  '{$game_type}' order by {$order}  LIMIT {$offset},{$limit}";
        foreach ($gifts as &$value) {
            $value['content'] = unserialize($value['content']);
            if ($request['user_id'] ?? 0) {
                $rs = $m_gift->fetchBySql("select cdkey from h5.user_cdkey where user_id = {$request['user_id']} and gift_id = {$value['gift_id']}");
                $value['cdkey'] = $rs['cdkey'];
            }
        }
        $assign['gift'] = $gifts;
        echo json_encode($assign, true);
        die;
    }

    /**
     * 获取区服列表
     */
    public function serverListAction()
    {
        $request = $_POST;
        $this->checkParams($request, ['index', 'game_type', 'pn']);
        $pn = $request['pn'];
        $limit = 9;
        $offset = ($pn - 1) * $limit;
        $order = 'start_time asc';
        $m_server = new ServerModel();
        $servers = array();
        $now_time = (string)date('Y-m-d H:i:s');
        $three_day_befor = (string)date("Y-m-d H:i:s", strtotime("-3 day"));
        $three_day_after = (string)date("Y-m-d H:i:s", strtotime("+3 day"));
        $condition = "start_time between '{$three_day_befor}' and '{$three_day_after}' and game_type='{$request['game_type']}'";
        if ($request['index'] == 0) {
            $condition .= " and start_time< '{$now_time}'";//已开新服,时间大于当前,前三天
            $order = 'start_time desc';
            $servers_list = $m_server->fetchAllBySql("select h5.game.*,h5.server.*,h5.server.name as server_name,h5.server.add_time as server_add_time from h5.server left join h5.game on h5.game.game_id=h5.server.game_id  where {$condition}  order by {$order}  LIMIT {$offset},{$limit}");
            $servers['start'] = $servers_list;
        }
        if ($request['index'] == 1) {
            $condition .= " and start_time> '{$now_time}'";//新服预告
            $servers_list = $m_server->fetchAllBySql("select h5.game.*,h5.server.*,h5.server.name as server_name,h5.server.add_time as server_add_time from h5.server left join h5.game on h5.game.game_id=h5.server.game_id  where {$condition}  order by {$order}  LIMIT {$offset},{$limit}");
            $servers['will_start'] = $servers_list;
        }
        echo json_encode($servers, true);
        die;
    }

    /**
     * 获取广告位列表
     * game_type
     */
    public function gameAdposAction()
    {
        $m_adpos = new AdposModel();
        $banner = $m_adpos->getByCode('game_index_banner', 4);
//        foreach ($banner as &$value){
//            $url=parse_url($value['url']);
//            $param=$url['query'];
//            $value['game_id']=substr($param,8);
//        }
        echo json_encode($banner['ads'], true);
        die;
    }

    public function loginAction()
    {
        $json = array('msg' => 'success', 'xcode' => 'false', 'fwd' => '');
        $request = $_POST;
        $this->checkParams($request, ['username', 'password']);
        $m_user = new UsersModel();
        $username = $request['username'];
        $password = $request['password'];
        $username = preg_replace('#[\'\"\%\#\*\?\\\]+#', '', substr($username, 0, 32));
        if (empty($username) || empty($password)) {
            $json['msg'] = '请输入用户名及密码！';
            exit(json_encode($json));
        }
        $remember = 1;
        $error = $m_user->login($username, $password, $remember);
        if ($error) {
            $json['msg'] = $error;
            exit(json_encode($json));
        } else {
            //生成唯一open_id 缓存
            $user_info = $m_user->fetch(['username' => $username]);
            $json['info'] = $user_info;
        }
        exit(json_encode($json));
    }

    public function loginoutAction()
    {
        $m_user = new UsersModel();
        $m_user->logout();
        exit(json_encode(array('status' => 'success')));
    }

    //领取礼包
    function getGiftAction()
    {
        $request = $_POST;
        $this->checkParams($request, ['user_id', 'gift_id']);
        $gift_id = $request['gift_id'];
        if ($gift_id < 1) {
            exit;
        }
        $cdkey = '';
        $m_gift = new GiftbagModel();
        $error = $m_gift->sendout($request['user_id'], $gift_id, $cdkey);
        if ($error == '') {
            exit(json_encode(array('msg' => '领取成功!', 'cdkey' => $cdkey)));
        } else {
            exit(json_encode(array('msg' => $error, 'cdkey' => '')));
        }
    }

    //游戏详情
    function getGameDeatilAction()
    {
        $request = $_POST;
        $this->checkParams($request, ['game_id']);
        $m_game = new GameModel();
        $game = $m_game->fetch("game_id='{$request['game_id']}'");
        if (empty($game)) {
            exit(json_encode(array('msg' => '没有查到游戏详情!')));
        }
        $game['grade'] = $m_game->gradeHtml($game['grade']);
        $game['support'] = $m_game->supportFormat($game['support'] + $game['play_times']);

        $game['screenshots'] = empty($game['screenshots']) ? array() : unserialize($game['screenshots']);
        $assign['game'] = $game;
        //礼包详情
        $order = 'h5.giftbag.game_id DESC';
        $m_gift = new GiftbagModel();
        $game_id = $request['game_id'];
        $gifts = $m_gift->fetchAllBySql("select h5.giftbag.name,game_name,nums,used,howget,content,gift_id,h5.game.logo from h5.giftbag  inner join h5.`game`  on h5.giftbag.game_id = h5.`game`.game_id where h5.`game`.game_id =  '{$game_id}' order by {$order}");
//        $log="select h5.giftbag.name,game_name,nums,used,content,h5.giftbag.gift_id,h5.game.logo from h5.giftbag  inner join h5.`game`  on h5.giftbag.game_id = h5.`game`.game_id where h5.`game`.game_type =  '{$game_type}' order by {$order}  LIMIT {$offset},{$limit}";
        foreach ($gifts as &$value) {
            $value['content'] = unserialize($value['content']);
            if ($request['user_id'] ?? 0) {
                $rs = $m_gift->fetchBySql("select cdkey from h5.user_cdkey where user_id = {$request['user_id']} and gift_id = {$value['gift_id']}");
                $value['cdkey'] = $rs['cdkey'];
            }
        }
        $assign['gift'] = $gifts;
        //开服列表
        $m_server = new ServerModel();
//        $server_list=$m_server->fetchAll(['game_id'=>$request['game_id'],'start_time'=>],1,30,'*','start_time asc');
        $now_date = date('Y-m-d');
        $server_list = $m_server->fetchAllBySql("select * from server where game_id={$request['game_id']} and start_time>'{$now_date}' order by start_time asc");
        $assign['server'] = $server_list;
        //资讯列表
        $m_article = new ArticleModel();
        $list = $m_article->fetchAll("game_id={$request['game_id']} and visible=1 and type!='代理公告'", 1, 10, 'article_id,cover,title,up_time', 'up_time DESC,weight ASC,article_id DESC');
        foreach ($list as &$row) {
            $row['up_time'] = $m_article->formatTime($row['up_time']);
        }
        $assign['info'] = $list;
//        //评论列表
//        $m_comment=new CommentModel();
//        $comment_list=$m_comment->fetchAll(['game_id']);
        exit(json_encode($assign));
    }

    function getCommentAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['game_id', 'pn']);
        $pn = $request['pn'];
        $limit = 20;
        if ($pn < 1 || $limit < 1) {
            exit;
        }
        $m_comment = new CommentModel();
        $comment_list = $m_comment->getComment($request['game_id'], $pn, $limit);
        exit(json_encode($comment_list));
    }

    function addCommentAction()
    {
        $request = $_POST;
        $this->checkParams($request, ['parent_id', 'game_id', 'comm_cont', 'user_id']);
        $data = $request;
        $data['comm_time'] = time();
        $data['like_num'] = 0;
        $m_comment = new CommentModel();
        if ($m_comment->insert($data)) {
            exit(json_encode(array('status' => 'success')));
        } else {
            exit(json_encode(array('status' => 'fail')));
        }
    }

    function addcommentlikeAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['comm_id', 'user_id']);
        $m_commtlike = new CommentlikeModel();
        $rs = $m_commtlike->fetch(['comm_id' => $request['comm_id'], 'user_id' => $request['user_id']]);
        if ($rs) {
            exit(json_encode(array('status' => 'fail', 'info' => '你已点过赞了!')));
        } else {
            $m_commtlike->insert($request);
            $m_commtlike->fetchBySql("update comment set like_num = like_num+1 where comm_id={$request['comm_id']}");
            exit(json_encode(array('status' => 'success')));
        }
    }

    /**
     * 我的礼包
     */
    function myGiftBagAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['user_id', 'pn', 'limit']);
        //礼包详情
        $m_user = new UsersModel();
        $m_game = new GameModel();
        $pn = $request['pn'];
        $limit = $request['limit'];
        if ($pn < 1 || $limit < 1) {
            exit;
        }
        $m_gift = new GiftbagModel();
        $logs = $m_user->giftLogs($request['user_id'], $pn, $limit);
        foreach ($logs as $key=>&$value) {
            $gift = $m_gift->fetch(['gift_id' => $value['gift_id']]);
            $game = $m_game->fetch(['game_id' => $gift['game_id']], 'logo');
            if($game['logo']) {
                $value['content'] = unserialize($gift['content']);
                $value['game_name'] = $gift['game_name'];
                $value['name'] = $gift['name'];
                $value['nums'] = $gift['nums'];
                $value['used'] = $gift['used'];
                $value['logo'] = $game['logo'];
                $value['howget'] = $gift['howget'];
            }else{
                unset($logs[$key]);
            }
        }
        exit(json_encode($logs));
    }

    /**
     * 充值记录
     */
    function mypaylogAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['user_id', 'pn', 'limit']);
        //礼包详情
        $pn = $request['pn'];
        $limit = $request['limit'];
        if ($pn < 1 || $limit < 1) {
            exit;
        }
        $m_pay = new PayModel();
        $logs = $m_pay->fetchAll("user_id='{$request['user_id']}' and pay_time > 0", $pn, $limit, 'pay_id,to_user,game_id,game_name,money,add_time', 'add_time DESC');
        exit(json_encode($logs));
    }

    /**
     * 最近在玩记录
     */
    function gamesLogsAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['user_id']);
        $m_user = new UsersModel();
        $games = $m_user->getPlayGames($request['user_id'], 1, 12);
        exit(json_encode($games));
    }

    /**
     * 收藏记录
     */
    function favoritesAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['user_id']);
        $m_user = new UsersModel();
        $games = $m_user->getFavorites($request['user_id'], 1, 12);
        exit(json_encode($games));
    }

    function isloginAction()
    {
        $m_user = new UsersModel();
        if ($m_user->getLogin()) {
            exit(json_encode(array('status' => 'success')));
        } else {
            exit(json_encode(array('status' => 'fail')));
        }
    }

    /**
     * 玩家推广统计
     */
    function playerChannelAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['user_id']);
        $user_id = $request['user_id'];
        $m_user = new UsersModel();
        $count = $m_user->fetchBySql("select COUNT(*) AS number ,SUM(player_channel_get) as money  from h5.user where player_channel={$user_id}");
        $assign['money'] = $count['money'];
        $assign['number'] = $count['number'];
        exit(json_encode($assign));
    }
    function playerChannel2Action()
    {
        $request = $_GET;
        $this->checkParams($request, ['pn']);
        $pn = $request['pn'];
        $limit = 20;
        $m_user = new UsersModel();
        if( $pn < 1 || $limit < 1 ) {
            exit;
        }
        $assign['list']=$m_user->fetchAll(['player_channel'=>$_SESSION['user_id']],$pn,$limit,'username,reg_time,player_channel_get','reg_time desc');
        exit(json_encode($assign));
    }

    function getGameDownloadUrlAction()
    {
        //获取下载资源链接
        $request = $_GET;
        $this->checkParams($request, ['game_id', 'channel_id']);
        $game_id = $request['game_id'];
        $m_game=new GameModel();
        $download_name=$m_game->fetch(['game_id'=>$game_id],'download_name');
        if(!$download_name){
            die('游戏下载名字不存在,请联系客服!');
        }
        $download_name=$download_name['download_name'];
        $channel_id = $request['channel_id'];
        //判断是否有包,没有则分包后下载
        $admin_id = $channel_id;
        if (file_exists("/www2/wwwroot/code/h5/tg/dev/public/game/apk/{$game_id}/{$download_name}{$admin_id}.apk")) {
            $this->redirect("http://yun.zyttx.com/game/apk/{$game_id}/{$download_name}{$admin_id}.apk");
        } else {
            $zip = new ZipArchive();
            $filename = "/www2/wwwroot/code/h5/open/dev/public/game/apk/{$game_id}.apk";//母包位置
            //复制一份到当前
            //判断游戏目录是否存在
            $path = "/www2/wwwroot/code/h5/tg/dev/public/game/apk/{$game_id}";
            if (!is_dir($path)) {
                mkdir($path);
            }
            shell_exec(" 
        PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin;~/bin;
        export PATH;
        cp {$filename}  /www2/wwwroot/code/h5/tg/dev/public/game/apk/{$game_id}/{$download_name}{$admin_id}.apk;
        > /dev/null 2>&1 &");
            sleep(5);
            $now_path = $path . "/{$download_name}{$admin_id}.apk";
            if ($zip->open($now_path, ZIPARCHIVE::CREATE) !== TRUE) {
                exit("cannot open <$filename> ");
            }
            $zip->addFromString("META-INF/jiule_channelid", "{$admin_id}");
            $zip->addFromString("META-INF/jiule_gameid", "{$game_id}");
            $zip->close();
            $this->redirect("http://yun.zyttx.com/game/apk/{$game_id}/{$download_name}{$admin_id}.apk");
        }
        Yaf_Dispatcher::getInstance()->disableView();
    }

    public function checkAndroidUpdateAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['now_version', 'channel']);
        $now_version = $request['now_version'];
        $config = Yaf_Registry::get('config')->android;
        $server_version = $config['version'];
        $server_version_msg = $config['version_msg'];
        if ($now_version == $server_version) {
            $assign['status'] = '100';
        } else {
            $channel = $request['channel'];
            $assign['status'] = '200';
            $assign['msg'] = "当前版本:{$now_version}.\n,最新版本:{$server_version}.\n,更新内容:{$server_version_msg}";
            $assign['download_url'] = "http://yun.zyttx.com/index/apkgame3?tg_channel={$channel}";
        }
        exit(json_encode($assign));
    }

    public function playGameAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['game_id', 'open_id']);
        $game_id = $request['game_id'];
//        $open_id=$request['open_id'];
//        $user_id=F_Helper_Mcrypt::authcode($open_id, 'DECODE');
//        $server_id = $request['server_id'];
        $user_id = $request['open_id'];
        $m_user = new UsersModel();
        $user = $m_user->fetch(['user_id' => $user_id], 'username,user_id');
//        var_dump($user);
        if (empty($user)) {
            //缓存渠道id
            $assign['status'] = 'fail';
            $assign['msg'] = '账号异常,联系管理员';
            exit(json_encode($assign));
        }
        //选择区服 todo
        if ($game_id < 1) {
            $assign['status'] = 'fail';
            $assign['msg'] = '游戏id异常1';
            exit(json_encode($assign));
        }
        $m_game = new GameModel();
        $game = $m_game->fetch("game_id='{$game_id}'", 'game_id,name,logo,login_url,sign_key,channel,load_type');
        if (empty($game)) {
            $assign['status'] = 'fail';
            $assign['msg'] = '找不到对应游戏';
            exit(json_encode($assign));
        }
        if ($game['login_url'] && $game['sign_key']) {
            $m_user->addPlayGame($user['user_id'], $game_id);
            $url = Game_Login::redirect($user['user_id'], $user['username'], $game_id, 0, $game['login_url'], $game['sign_key']);
            $this->forward('api', 'entry', array('game_name' => $game['name'], 'url' => $url, 'load_type' => $game['load_type']));
            return false;
        }
//        Yaf_Dispatcher::getInstance()->disableView();
    }
    //三方进入游戏
    public function playGameOAuthAction()
    {
        $request = $_GET;
        $this->checkParams($request, ['game_id', 'account','password','username','sign','timestamp']);
        //时效验证
        if(time()-$request['timestamp']>60){
            $assign['status'] = 'fail';
            $assign['msg'] = 'time error';
            exit(json_encode($assign));
        }

        $accounts=[['account'=>'moyou','password'=>'123456','key'=>'jdashdgkjsahdjkhasd']];
        $search_id= array_search( $_GET['account'],array_column($accounts, 'account'));
        if($search_id!==false){
            $arr=$accounts[$search_id];
            if($_GET['password']!=$arr['password']){
                $assign['status'] = 'fail';
                $assign['msg'] = '账号或密码错误';
                exit(json_encode($assign));
            }
            //验证签名
            $arr2['game_id']=$request['game_id'];
            $arr2['account']=$request['account'];
            $arr2['password']=$request['password'];
            $arr2['username']=$request['username'];
            $arr2['timestamp']=$request['timestamp'];
            ksort($arr2);
            $sign_key=$arr['key'];
            $md5 = md5(implode('', $arr2) . $sign_key);
            $sign=$request['sign'];
            if (strcmp($sign, $md5) != 0) {
                exit('Sign error.');
            }
        }else{
            $assign['status'] = 'fail';
            $assign['msg'] = '账号或密码错误';
            exit(json_encode($assign));
        }
        $game_id = $request['game_id'];
        $username = $request['username'];
        $m_user = new UsersModel();
        $open_id=md5($username.$search_id);
        $user = $m_user->fetch(['openid' =>$open_id], 'user_id,username');
        $url=new F_Helper_Url();
        $channel_id = $url->getUrlSign();
        if(!$user){
            //创建
            $user_id = $m_user->insert(array(
                'username' => $arr['account'].'_'.$username,
                'app' => $arr['account'],
                'openid' => $open_id,
                'password' => md5($arr['account'].'_'.$username),
                'reg_time' => time(),
                'check_status' => 0,
                'tg_channel' => $channel_id,
            ));
            if($user_id){
                $user['user_id']=$user_id;
                $user['username']=$arr['account'].'_'.$username;
            }else{
                $assign['status'] = 'fail';
                $assign['msg'] = '账号创建失败';
                exit(json_encode($assign));
            }
        }
//        var_dump($user);
        if (empty($user)) {
            //缓存渠道id
            $assign['status'] = 'fail';
            $assign['msg'] = '账号异常,联系管理员';
            exit(json_encode($assign));
        }
        //选择区服 todo
        if ($game_id < 1) {
            $assign['status'] = 'fail';
            $assign['msg'] = '游戏id异常1';
            exit(json_encode($assign));
        }
        $m_game = new GameModel();
        $game = $m_game->fetch("game_id='{$game_id}'", 'game_id,name,logo,login_url,sign_key,channel,load_type');
        if (empty($game)) {
            $assign['status'] = 'fail';
            $assign['msg'] = '找不到对应游戏';
            exit(json_encode($assign));
        }
        if ($game['login_url'] && $game['sign_key']) {
            //登录账号到平台,防止支付跳转后无法返回
            $remember = 1;
            $error = $m_user->login($user['username'], $arr['account'].'_'.$username, $remember);
            if ($error) {
                $json['msg'] = $error;
                exit(json_encode($json));
            }
            $m_user->addPlayGame($user['user_id'], $game_id);
            $url = Game_Login::redirect($user['user_id'], $user['username'], $game_id, 0, $game['login_url'], $game['sign_key']);
            $this->forward('api', 'entry', array('game_name' => $game['name'], 'url' => $url, 'load_type' => $game['load_type']));
            return false;
        }
//        Yaf_Dispatcher::getInstance()->disableView();
    }

    /**
     * 获取游戏信息
     */
    public function getGameInfoByIdAction(){
        $game_id=$_GET['game_id'];
        $m_game=new GameModel();
        $game_info=$m_game->fetch(['game_id'=>$game_id],'game_id,name,logo,package_name,package_size');
        exit(json_encode($game_info));
    }
    public function entryAction()
    {
        $req = $this->getRequest();
        $params = $req->getParams();
        if (count($params) != 3 || empty($params['game_name']) || empty($params['url']) || empty($params['load_type'])) {
            $this->redirect('/game/index.html');
            return false;
        }
        if ($params['load_type'] == 'redirect') {
            $this->redirect($params['url']);
            return false;
        }
        $this->getView()->assign($params);
    }

    //不同环境下获取真实的IP
    function getIp()
    {
        global $ip;
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }

    /**
     * 检查参数
     * @Author   liuqi
     * @DateTime 2018-08-22T11:01:38+0800
     * @param    [type]
     * @param    array
     * @return   [type]
     */
    private function checkParams(array $request, array $check)
    {
        foreach ($check as $key => $value) {
            if (!array_key_exists($value, $request)) {
                $rs['status'] = 1001;
                $rs['msg'] = $value . "参数必须!";
                echo json_encode($rs);
                die;
            }
            if ($request[$value] === '') {
                $rs['status'] = 1002;
                $rs['msg'] = $value . "参数值必须!";
                echo json_encode($rs);
                die;
            }
        }
    }
}
