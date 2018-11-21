<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/20/020
 * Time: 10:08
 */
namespace app\index\validate;
use think\Validate;

class User extends Validate
{
    protected $rule = [
        'username|账户' => 'require|max:25',
        'password|密码' => 'require|max:25',
        'nickname|真实姓名' => 'require',
        'pay_number|支付宝账号' => 'require',
        'boxname|游戏盒子' => 'require|max:12',
        'qq1|客服QQ' => 'require',
        'qq2|QQ群' => 'require',
    ];
    protected $message = [
        'username.require' => '账号必须',
        'username.max' => '账号不能超过25个字符',
        'boxname.require' => '盒子名称不能超过12个字符',
        'qq1.require' => 'QQ客服必须',
        'qq2.require' => 'QQ群必须',
    ];
    //场景验证
    protected $scene = [
        'edit' => ['email'],
    ];
}