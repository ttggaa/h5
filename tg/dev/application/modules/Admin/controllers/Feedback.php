<?php

class FeedbackController extends F_Controller_Backend
{
    public function init()
    {
        parent::init();
        $this->_view->assign('types', $this->_model->_types);
    }

    protected function beforeList()
    {
        $params = parent::beforeList();
        $params['orderby'] = 'status asc,create_time DESC';
        $search = $this->getRequest()->getQuery('search', array());
        if($_SESSION['admin_id']==1){
            $conds = "";
            $cmm = '';
        }else{
            $conds = "admin_id={$_SESSION['admin_id']}";
            $cmm = ' AND';
        }
        if( $search ) {
            foreach ($search as $k=>$v)
            {
                if( empty($v) ) {
                    continue;
                }
                if( $k == 'title' ) {
                    $conds .= "{$cmm}{$k} LIKE '%{$v}%'";
                    continue;
                }
                $conds .= "{$cmm}{$k}='{$v}'";
                $cmm = ' AND ';
            }
        }
        $params['op'] = F_Helper_Html::Op_Null;
        $params['conditions']=$conds;
        return $params;
    }
    protected function beforeEdit(&$info)
    {

    }
    protected function beforeUpdate($id, &$info)
    {
        if($id){
            //编辑

        }else{
            $info['create_time']=time();
            $info['status']='未处理';
            $info['admin_id']=$_SESSION['admin_id'];
            //注册
        }
        $info['content']=trim($info['content']);
    }
    protected function afterUpdate($id, &$info)
    {
        if( $id ) {
            $this->redirect("/admin/{$this->_ctl}/list?{$this->_query}");
        } else {
            $this->forward('admin', 'message', 'error', array('msg'=>'添加失败，请重试！'));
        }
    }

    /**
     * 详情
     */
    public function detailsAction(){
        $feed_id=$_GET['feed_id'];
        $feedback=new FeedbackModel();
//        $rs = $m_gift->fetchBySql("select cdkey from h5.user_cdkey where user_id = {$request['user_id']} and gift_id = {$value['gift_id']}");
        $data['feedback']=$feedback->fetchBySql("select * from cps.feed_back as a where a.feed_id={$feed_id}");
        $data['feedbackreply']=$feedback->fetchAllBySql("select * from cps.feed_back_reply as a where a.feed_id={$feed_id} order by create_time asc");
//        var_dump($result);die;
//        return $result;
        $this->getView()->assign('data',$data);
    }

    /**
     * 回复
     */
    public function ajaxReplyAction(){
        $data=$_POST;
        $m_feedbackreply=new FeedbackreplyModel();
        $now_time=time();
        $rs=$m_feedbackreply->insert(['feed_id'=>$data['feed_id'],'content'=>$data['content'],'create_time'=>$now_time,'reply_name'=>$_SESSION['admin_id']]);
        if($rs){
        die(json_encode(['code'=>1,'msg'=>'回复成功']));
        }else{
            die(json_encode(['code'=>0,'msg'=>'回复失败']));
        }
    }

    /**
     * 切换状态
     */
    public  function changeStatusAction(){
        $data=$_POST;
        $m_feedback=new FeedbackModel();
        switch ($data['status']){
            case "true":
                $data['status']='已处理';
                break;
            case "false":
                $data['status']='未处理';
                break;
        }
        $rs=$m_feedback->update(['status'=>$data['status']],['feed_id'=>$data['feed_id']]);
        if($rs){
            die(json_encode(['code'=>1,'msg'=>'修改成功']));
        }else{
            die(json_encode(['code'=>0,'msg'=>'修改失败']));
        }
    }
    public function uploadReplyImgAction(){
        $path='feedback';
        $this->uploadImg($path);
    }
}
