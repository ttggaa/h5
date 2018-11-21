<?php

namespace app\index\controller;

use app\index\model\User as UserModel;
use think\captcha\Captcha;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    //登录验证码1 ,注册验证码2
    public function getVerify($id = '')
    {
        $config = [
            // 验证码字体大小
            'fontSize' => 30,
            // 验证码位数
            'length' => 4,
            // 关闭验证码杂点
            'useNoise' => false,
        ];
        $captcha = new Captcha($config);
        return $captcha->entry($id);
    }

    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串，$id多个验证码标识
    protected function check_verify($code, $id = '')
    {
        $captcha = new Captcha();
        return $captcha->check($code, $id);
    }
    //注册逻辑
    public function ajaxRegister(){
        $data=input('post.');
        if($this->check_verify($data['captcha'],'register')){
            $user = new UserModel();
            $rs = $user->register($data);
            return $rs;
        }else{
            return ['code'=>0,'mgs'=>'验证码错误'];
        }

    }
    //登录
    public function ajaxLogin(){
        $data=input('post.');
        if($this->check_verify($data['captcha'],'login')){
            $user = new UserModel();
            $rs = $user->login($data);
            return $rs;
        }else{
            return ['code'=>0,'mgs'=>'验证码错误'];
        }
    }
}
