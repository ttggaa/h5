<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/20/020
 * Time: 10:00
 */

namespace app\index\model;
class User extends \think\Model
{
    protected $table='admin';
    protected $pk='admin_id';
    protected $field = true;
    protected $auto = ['password'];
    protected $insert = ['add_ip', 'status' => 'super', 'cps_type', 'divide_into','add_by'=>'self','add_time',];
    protected $update = ['last_login_ip','last_login_time'];
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
            return ['code'=>0,'mgs'=>$this->getError()];
        }else{
            return ['code'=>1,'mgs'=>'登录成功','info'=>$result];
        }
    }
}