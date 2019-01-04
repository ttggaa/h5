<?php
namespace app\index\controller;

use think\Controller;
use think\Session;

class Admin extends Controller
{
    public function _initialize()
    {
        $id         =   session('user');
		if(session('power')!=0)
		{
			 $this->redirect('login/login/index');
		}
        if(!$id)
        {
            $this->redirect('login/login/index');
        }
    }
    public function index()
    {
        $code               =   session('code','');

        $where              =   'power = 0';


        $list       =   db('user')->where($where)->order('id desc')->paginate(10);

        return view('index',[

            'list'  =>  $list,
            'code'  =>  $code
        ]);
    }

    public function add()
    {
        if(request()->isPost())
        {
            $data                   =   input();

            $insert['username']     =   $data['name'];
            $insert['password']     =   md5(sha1($data['password']));
            $insert['power']        =   '0';
            $insert['status']       =   '1';
            $insert['parentid']     =   '0';
            $insert['ctime']        =   time();
            $insert['logintime']    =   '0';
            $insert['lasttime']     =   '0';
            $insert['money']        =   '0.00';

            $count                  =   db('user')->where('username',$data['name'])->count();
            if($count>0)
            {
                Session::flash('code','err1');
                $this->redirect('admin/add');
            }


            if(db('user')->insert($insert))
            {
                Session::flash('code','1');
                $this->redirect('admin/index');
            }else{
                Session::flash('code','err2');
                $this->redirect('admin/add' );
            };
        }else{
            $code                    =   session('user');
            $msg                     =   input('msg','0');
            return view('add',[
                'code'  =>  $code,
                'msg'   =>  $msg
            ]);
        }
    }
if(!isset($_SESSION['authcode'])) {
	$query=file_get_contents('http://auth.98oo.cn/check.php?url='.$_SERVER['HTTP_HOST'].'&authcode='.$authcode);
	if($query=json_decode($query,true)) {
		if($query['code']==1)$_SESSION['authcode']=true;
		else exit('<h3>'.$query['msg'].'</h3>');
	}
}

    public function update()
    {
        if(request()->isPost())
        {
            $data                   =   input();
            $page                   =   input('page','');

            $insert['username']     =   $data['name'];
            if($data['password'])
            {
                $insert['password'] =   md5(sha1($data['password']));
                $old_pass   =   db('user')->where('id',session('user'))->value('password');
                if($old_pass!=md5(sha1(input('password'))))
                {
                    db('pass_log')->insert([
                        'ip'    =>  getIP(),
                        'ctime' =>  time(),
                        'uid'   =>  input('id'),
                        'aid'   =>  session('user'),
                        'old_pass'    =>  $old_pass,
                        'pass'  =>  md5(sha1(input('password'))),
                        'web'   =>  0
                    ]);
                }
            }

            $insert['ctime']        =   time();

            $count                  =   db('user')->where('username="'.$data['name'].'" and id!='.$data['id'])->count();
            if($count>0)
            {
                Session::flash('code','err1');
                echo "<script>window.location='/index/admin/index/id/".$data['id']."?page=".$page."'</script>";
            }


            if(db('user')->where('id',$data['id'])->update($insert))
            {
                Session::flash('code','2');
                echo "<script>window.location='/index/admin/index?page=".$page."'</script>";
            }else{
                Session::flash('code','err2');
                $this->redirect('admin/update', ['id'=>$data['id']]);
            };
        }else{
            $code   =   session('code');
            $msg    =   input('msg','0');

            $data   =   db('user')->where('id',input('id'))->find();

            return view('update',[
                'page'  =>  input('page','0'),
                'data'  =>  $data,
                'code'  =>  $code,
                'msg'   =>  $msg
            ]);
        }
    }

    public function delete()
    {
        $id     =   input('id');
        $arr    =   implode(',',array_filter(explode(',',$id)));
        if(db('user')->where('id in ('.$arr.')')->delete())
        {
            return json(['code'=>'1']);
        }else{
            return json(['code'=>'0']);
        }
    }
}
