<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;

require_once dirname(__FILE__).'/../../../mysql-master/src/Connection.php';

class Events
{
    public static $db   =   null;

    public static function onConnect($client_id)
    {

        self::$db   =   new Workerman\MySQL\Connection('localhost', '3306', 'root', 'root', 'admin');

    }

   public static function onMessage($client_id, $message)
   {
       $send    =   json_decode($message);
	   
	   if($send->uid)
	   {
		   $data    =   self::$db->select('*')->from('ap_user')->where('id='.$send->uid)->row();
		  if(!$data)
		  {
			    $arr 	=	['code'=>'3','msg'=>'1'];
				
			   echo Gateway::sendToClient($client_id,json_encode($arr));
			   Gateway::closeClient($client_id);
			   return ;

		  }
		  
		  
		  
       $data    =   self::$db->select('*')->from('ap_login_code')->where('uid='.$send->uid)->row();
       if($data===false)
       {
           self::$db->insert('ap_login_code')->cols([
               'uid'    =>  $send->uid,
               'code'   =>  $send->code,
               'client_id'=>$client_id])->query();
       }else{
           if(base64_decode($data['code'])<base64_decode($send->code))
           {
               self::$db->update('ap_login_code')->cols(array('code'=>$send->code,'client_id'=>$client_id))->where('uid='.$send->uid)->query();
           }
       }
       Timer::add(10, function()use($client_id,$send){
           $data    =   self::$db->select('count(*) as num')->from('ap_login_code')->where('uid='.$send->uid.' and code="'.$send->code.'"')->row();
           $time    =   self::$db->select('lasttime,sign,type')->from('ap_user')->where('id='.$send->uid)->row();
           if($data['num']=='0')
           {
			   $arr 	=	['code'=>'0','sign'=>$time['sign'],'lasttime'=>$time['lasttime']];
			   
			    echo  Gateway::sendToClient($client_id,json_encode($arr));
				 Gateway::closeClient($client_id);
			  return ;
         
           }else{

            
                   if($time['type']=='1')
                   {
                       $arr 	=	['code'=>'1','sign'=>$time['sign'],'lasttime'=>'-1'];
                   }else{
                       if($time['lasttime']>time())
                       {
                           $arr 	=	['code'=>'1','sign'=>$time['sign'],'lasttime'=>$time['lasttime']];

                       }else{
                           $arr 	=	['code'=>'2','sign'=>$time['sign'],'lasttime'=>$time['lasttime']];

                       }
                   }
               


              
           }
		   return Gateway::sendToClient($client_id,json_encode($arr));
       });

	   }else{
		    Gateway::closeClient($client_id);
	   }
	      


   }
   public static function onClose($client_id)
   {
       self::$db->delete('ap_login_code')->where('client_id="'.$client_id.'"')->query();
   }
}
