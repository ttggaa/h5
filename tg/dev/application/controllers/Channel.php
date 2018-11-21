<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/2/002
 * Time: 10:02
 */

class ChannelController extends Yaf_Controller_Abstract
{
    public function indexAction()
    {
        $channel=$_GET['channel']??1;//channel_id
        //获取渠道信息
        $admin=new AdminModel();
        $channel_info=$admin->fetch(['admin_id'=>$channel]);
        $channel_info['boxname']=$channel_info['boxname']?$channel_info['boxname']:'久乐盒子';
        $channel_info['download_url']="http://yun.zyttx.com/index/apkgame3?tg_channel={$channel_info['admin_id']}";
        $channel_info['download_url2']="http://yun.zyttx.com/index/apkgame4?tg_channel={$channel_info['admin_id']}";
        $channel_info['play_url']="http://{$channel_info['admin_id']}.h5.zyttx.com";
        $assign['info']=$channel_info;
        $this->getView()->assign($assign);
    }
    public function addChannelAction(){
        $this->redirect("/channel_add/public");
    }

}