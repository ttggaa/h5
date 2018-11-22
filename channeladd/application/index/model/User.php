<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/20/020
 * Time: 10:00
 */

namespace app\index\model;
use think\Session;

class User extends \think\Model
{
    protected $table='admin';
    protected $pk='admin_id';
    protected $field = true;
    protected $auto = [];
    protected $insert = [ 'status' => 'super','add_by'=>'self'];
    protected $update = [];
    protected function setPasswordAttr($value)
    {
        return md5($value);
    }
    protected function setAddIpAttr()
    {
        return request()->ip();
    }
    protected function setAddTimeAttr()
    {
        return date('Y-m-d H:i:s', time());

    }
    protected function setLastLoginTimeAttr()
    {
        return date('Y-m-d H:i:s', time());
    }
    protected function setLastLoginIpAttr()
    {
                return request()->ip();
    }

    protected function setCpsTypeAttr($value, $data)
    {
        if ($data['parent_id'] > 1) {
            return 3;
        } elseif ($data['parent_id'] == 1) {
            return 2;
        }
    }

    protected function setDivideIntoAttr($value, $data)
    {
        if ($data['parent_id'] > 1) {
            return 50;
        } elseif ($data['parent_id'] == 1) {
            return 0;
        }
    }
     /**
     * 注册
     */
    function register($data)
    {
        $result = $this->validate()->save($data);
        if(false === $result){
            // 验证失败 输出错误信息
            return ['code'=>0,'mgs'=>$this->getError()];
        }else{
            return ['code'=>1,'mgs'=>'注册成功'];
        }
    }
    /**
     * 登录
     */
    function login($data)
    {

        $result = $this->where(['username'=>$data['username'],'password'=>md5($data['password'])])->find();
        if(!$result){
            // 验证失败 输出错误信息
            return ['code'=>0,'mgs'=>'账号或密码错误'];
        }else{
            $user=$result;
            if($user['admin_id']==1){
                //超级管理员
                $channel_ids = $this->limit(2000)->column('admin_id');
            }else {
                $channel_ids = $this->where(['parent_id'=> $user['admin_id']])->limit(2000)->column( 'admin_id');
            }

//            foreach ($channel_ids as $k => $v) {
//                $channel_ids[$k] = (int)$channel_ids[$k]['admin_id'];
//            }
            array_push($channel_ids,$user['admin_id']);
            $channel_ids=array_unique($channel_ids);
            Session::set('admin_id',$user['admin_id']);
            Session::set('admin_name',$user['username']);
            Session::set('admin_status',$user['status']);
//            Session::set('admin_group',$user['group_id']);
            Session::set('channel_ids',$channel_ids);
            Session::set('channel_ids_condition','('.implode(',',$channel_ids).')');
            Session::set('cps_type',(int)$user['cps_type']);
            Session::set('boxname',$user['boxname']);
            return ['code'=>1,'mgs'=>'登录成功','info'=>$result];
        }
    }
}