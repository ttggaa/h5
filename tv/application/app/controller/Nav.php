<?php
namespace app\app\controller;
use app\XDeode;
use think\Controller;
class Nav extends Controller
{

	   public function index()
    {
        
        $cid       =   input('cid');
        //$name      =   input('name');
        //$sort      =  input('sort');
        $list['vip']      =   db('banner')->where('cid',2)->order('sort asc')->paginate(12);
        $list['tj']      =   db('banner')->where('cid',4)->order('sort asc')->paginate(4);
        $list['banner']      =   db('banner')->where('cid',1)->order('sort asc')->paginate(4);
         $list['lr']      =   db('banner')->where('cid',3)->find();
         $list['wz']      =   db('banner')->where('cid',12)->order('sort asc')->paginate(2);
         $list['hb']      =   db('banner')->where('cid',8)->find();
        //var_dump($list);exit();
        // return view('index',[
        //     'msg'   =>  $msg,
        //     'list'  =>  $list,
        //     'code'  =>  $code
        // ]);
        $data=json_encode($list,JSON_UNESCAPED_UNICODE);
        $data = str_replace("picurl\":\"", "picurl\":\"".$_SERVER['HTTP_HOST'], $data);
        echo $data;
    }
       public function fl()
    {
        
        $cid       =   input('cid');
        //$name      =   input('name');
        //$sort      =  input('sort');
        $list['tu']      =   db('banner')->where('cid',6)->order('sort asc')->paginate(8);
        $list['banner']     =   db('banner')->where('cid',5)->order('sort asc')->paginate(4);
        $list['guanggao']     =   db('banner')->where('cid',7)->order('sort asc')->paginate(4);
        //添加绝对域名
        $data=json_encode($list,JSON_UNESCAPED_UNICODE);
        $data = str_replace("picurl\":\"", "picurl\":\"".$_SERVER['HTTP_HOST'], $data);

        //var_dump($list);exit();
        // return view('index',[
        //     'msg'   =>  $msg,
        //     'list'  =>  $list,
        //     'code'  =>  $code
        // ]);
        echo $data;
    }
       public function on()
    {
   
        $list  =   db('advert')->select();
        


        //var_dump($list);exit();
        // return view('index',[
        //     'msg'   =>  $msg,
        //     'list'  =>  $list,
        //     'code'  =>  $code
        // ]);
        $data=json_encode($list,JSON_UNESCAPED_UNICODE);
        $data = str_replace("picurl\":\"", "picurl\":\"".$_SERVER['HTTP_HOST'], $data);
        echo $data;
    }

  



}
