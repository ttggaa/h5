<?php

class IndexController extends Yaf_Controller_Abstract
{
    public function indexAction()
    {
//        $info['channel_id']=2;
//        $this->getView()->assign($info);
        $url=new F_Helper_Url();
        $channel_id=$url->getUrlSign();
        if($channel_id){
            $info['channel_id']=$channel_id;
            $this->getView()->assign($info);
        }else{
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/admin/index/index';
            $this->redirect($url);
        }
    }
//    public function channelAction(){
//        $url=new F_Helper_Url();
//        $channel_id=$url->getUrlSign();
//        $info['channel_id']=$channel_id;
//        $this->getView()->assign($info);
//    }
    public function getVerifyAction(){
        $id=$_GET['id'];
        $ic = new F_Helper_ImgCode();
        $ic->sess_name=$id;
        $ic->create();
        return false;
    }
    public function ajaxloginAction()
    {
        $m_admin=new AdminModel();
        $req = $this->getRequest();
        $u = substr($req->getPost('username', ''), 0, 16);
        $p = substr($req->getPost('password', ''), 0, 31);
        $xcode = substr($req->getPost('captcha', ''), 0, 4);
        //判断验证码
        $imgcode = new F_Helper_ImgCode();
        $imgcode->sess_name='login';
        if( ! $imgcode->check($xcode) ) {
            $json['msg'] = '验证码错误！';
            $json['code'] = 0;
            exit(json_encode($json));
        }
        $error = $m_admin->login($u, $p, 0);
        if( $error != '' ) {
            $json['msg'] = $error;
            $json['code'] = 0;
            exit(json_encode($json));
        } else {
            $json['msg'] = '/admin/index/main';
            $json['code'] = 1;
            exit(json_encode($json));
        }
        return false;
    }

    public function ajaxRegisterAction()
    {
        $req = $this->getRequest();
        $u = substr($req->getPost('username', ''), 0, 16);
        $p = substr($req->getPost('password', ''), 0, 31);
        $parent_id = $req->getPost('parent_id', '');
        $xcode = substr($req->getPost('captcha', ''), 0, 4);
        //判断验证码
        $imgcode = new F_Helper_ImgCode();
        $imgcode->sess_name='register';
        if( ! $imgcode->check($xcode) ) {
            $json['msg'] = '验证码错误！';
            $json['code'] = 0;
            exit(json_encode($json));
        }
        $m_admin=new AdminModel();
        //判断用户名是否被占用
        if($m_admin->fetch(['username'=>$u],'admin_id')){
            $json['msg'] = '用户名已被占用';
            $json['code'] = 0;
            exit(json_encode($json));
        }
        $data['username']=$u;
        $data['password']=md5($p);
        $data['add_by']='self';
        $data['add_ip']=$_SERVER['REMOTE_ADDR'];
        $data['status']='super';
        $data['parent_id']=$parent_id;
        $data['cps_type']=3;
        $data['divide_into']=50;
        $id=$m_admin->insert($data);
        if( !$id ) {
            $json['msg'] = '注册失败';
            $json['code'] = 0;
            exit(json_encode($json));
        } else {
            $json['msg'] = '注册成功';
            $json['code'] = 1;
            exit(json_encode($json));
        }
        return false;
    }
    /**
     * 游戏下载
     */
    public function apkgameAction()
    {
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            //提示
        }else {
            $game_id = $_GET['game_id'] ?? die('游戏id必须');
            $channe_id = $_GET['tg_channel'] ?? 1;
            $admin_id = $channe_id;
            //获取游戏名字
            $m_game = new GameModel();
            $download_name = $m_game->fetch(['game_id' => $game_id], 'download_name');
            if (!$download_name) {
                die('游戏下载名字不存在,请联系客服!');
            }
            $download_name = $download_name['download_name'];
            if (file_exists("/www2/wwwroot/code/h5/tg/dev/public/game/apk/{$game_id}/{$download_name}{$admin_id}.apk")) {
                $this->redirect("http://yun.zyttx.com/game/apk/{$game_id}/{$download_name}{$admin_id}.apk");
            } else {
                $zip = new ZipArchive();
                $filename = "/www2/wwwroot/code/h5/open/dev/public/game/apk/{$game_id}.apk";//母包位置
                //复制一份到当前
                //判断游戏目录是否存在
                $path = APPLICATION_PATH . "/public/game/apk/{$game_id}";
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
    }

    /**
     * 盒子下载
     */
    public function akpgame2Action()
    {
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            //提示
        }else {
            $admin_id = $_REQUEST['tg_channel'];
            $admin = new AdminModel();
            //1.修改文件
            $file_dir = "/www2/wwwroot/tool/1/assets/apps/default/www/manifest.json";
            $json_string = file_get_contents($file_dir);
            $data = json_decode($json_string, true);
            $launch_path = "http://{$admin_id}.h5.zyttx.com";
            $developer_url = "http://{$admin_id}.h5.zyttx.com";
            $boxname = $admin->fetch(['admin_id' => $admin_id], 'boxname');
            if ($boxname == '') {
                $boxname = '游戏盒子';
            } else {
                $boxname = $boxname['boxname'];
            }
            // 把JSON字符串转成PHP数组
            $data['name'] = $boxname;
            $data['launch_path'] = $launch_path;
            $data['developer']['url'] = $developer_url;
            $json_strings = json_encode($data);
            file_put_contents($file_dir, $json_strings);//写入
            //修改APP名字
            $file_dir2 = "/www2/wwwroot/tool/1/res/values/strings.xml";
            $doc = new DOMDocument();
            $doc->load($file_dir2);
            $strings = $doc->getElementsByTagName("string");
            //遍历
            foreach ($strings as $string) {
                //将id=3的title设置为33333
                if ($string->getAttribute('name') == 'app_name') {
                    $string->nodeValue = $boxname;
                }
            }
            //对文件做修改后，一定要记得重新sava一下，才能修改掉原文件
            $doc->save($file_dir2);
            //2. 编译app
            shell_exec("
        PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin:/home/java/jdk1.8.0_181:/home/java/jdk1.8.0_181/lib/:/home/java/jdk1.8.0_181/bin;export PATH;
        export JAVA_HOME CLASSPATH PATH;
        cd /www2/wwwroot/tool;
        apktool b 1;
        cp /www2/wwwroot/tool/1/dist/1.apk  /www2/wwwroot/tool/;
        cd /www2/wwwroot/tool;
        java -jar signapk.jar  testkey.x509.pem testkey.pk8  1.apk {$admin_id}.apk; 
        mv -f /www2/wwwroot/tool/{$admin_id}.apk  /www2/wwwroot/xgame.zyttx.com/apk/;
        rm -rf /www2/wwwroot/tool/1.apk;
         > /dev/null 2>&1 &");
            sleep(5);
            $this->redirect("http://xgame.zyttx.com/apk/new{$admin_id}.apk");
            Yaf_Dispatcher::getInstance()->disableView();
        }
    }

    /**
     * 新版盒子下载
     */
    public function apkgame3Action()
    {
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            //提示
        }else {
            $admin_id = $_REQUEST['tg_channel'] ?? 1;
            if (file_exists("/www2/wwwroot/xgame.zyttx.com/apk/youxihezi{$admin_id}.apk")) {
                $this->redirect("http://xgame.zyttx.com/apk/youxihezi{$admin_id}.apk");
            } else {
                $channe_id = $admin_id ?? 1;
                $zip = new ZipArchive();
                $filename = "/www2/wwwroot/xgame.zyttx.com/apk/base.apk";//母包位置
                //复制一份到当前
                //判断游戏目录是否存在
                $path = "/www2/wwwroot/xgame.zyttx.com/apk/";
                if (!is_dir($path)) {
                    mkdir($path);
                }
                shell_exec(" 
        PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin;~/bin;
        export PATH;
        cp {$filename}  /www2/wwwroot/xgame.zyttx.com/apk/youxihezi{$channe_id}.apk;
        > /dev/null 2>&1 &");
                sleep(5);
                $now_path = $path . "/youxihezi{$channe_id}.apk";
                if ($zip->open($now_path, ZIPARCHIVE::CREATE) !== TRUE) {
                    exit("cannot open <$filename> ");
                }
                $zip->addFromString("META-INF/jiule_channelid", "{$channe_id}");
                $zip->close();
                $this->redirect("http://xgame.zyttx.com/apk/youxihezi{$channe_id}.apk");
            }
            Yaf_Dispatcher::getInstance()->disableView();
        }
    }

    /**
     * ios盒子下载
     */
    public function apkgame4Action()
    {
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            //提示
        }else {
            $admin_id = $_REQUEST['tg_channel'] ?? null;
            if (!$admin_id) {
                die('tg_channel必须');
            }
            if (file_exists("/www2/wwwroot/xgame.zyttx.com/ios/ipa/{$admin_id}.ipa")) {
                $this->redirect("https://ipa.zyttx.com/index.php?channel={$admin_id}");
            } else {
                $zip = new ZipArchive();
                $filename = "/www2/wwwroot/xgame.zyttx.com/ios/ipa/base.ipa";//母包位置
                //复制一份到当前
                //判断游戏目录是否存在
                $path = "/www2/wwwroot/xgame.zyttx.com/ios/ipa/";
                if (!is_dir($path)) {
                    mkdir($path);
                }
                shell_exec(" 
        PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin;~/bin;
        export PATH;
        cp {$filename}  /www2/wwwroot/xgame.zyttx.com/ios/ipa/{$admin_id}.ipa;
        cp /www2/wwwroot/xgame.zyttx.com/ios/plist/base.plist  /www2/wwwroot/xgame.zyttx.com/ios/plist/{$admin_id}.plist;
        > /dev/null 2>&1 &");
                //复制plist文件
                sleep(5);
                //添加下载文件
                $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!DOCTYPE plist PUBLIC \"-//Apple//DTD PLIST 1.0//EN\" \"http://www.apple.com/DTDs/PropertyList-1.0.dtd\">
<plist version=\"1.0\">
<dict>
    <key>items</key>
    <array>
        <dict>
            <key>assets</key>
            <array>
                <dict>
                    <key>kind</key>
                    <string>software-package</string>
                    <key>url</key>
                    <string>https://ipa.zyttx.com/ipa/{$admin_id}.ipa</string>
                </dict>
                <dict>
                    <key>kind</key>
                    <string>full-size-image</string>
                    <key>needs-shine</key>
                    <true/>
                    <key>url</key>
                    <string>https://ipa.zyttx.com/logoFull.png</string>
                </dict>
                <dict>
                    <key>kind</key>
                    <string>display-image</string>
                    <key>needs-shine</key>
                    <true/>
                    <key>url</key>
                    <string>https://ipa.zyttx.com/logo.png</string>
                </dict>
            </array>
            <key>metadata</key>
            <dict>
                <key>bundle-identifier</key>
                <string>ipa.zyttx.com</string>
                <key>bundle-version</key>
                <string>0.0.5</string>
                <key>kind</key>
                <string>software</string>
                <key>subtitle</key>
                <string>install app</string>
                <key>title</key>
                <string>游戏盒子</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>";
                file_put_contents("/www2/wwwroot/xgame.zyttx.com/ios/plist/{$admin_id}.plist", $content);
                $now_path = $path . "/{$admin_id}.ipa";
                if ($zip->open($now_path, ZIPARCHIVE::CREATE) !== TRUE) {
                    exit("cannot open <$filename> ");
                }
                $zip->addFromString("Payload/UZApp.app/_CodeSignature/jiule_channelid", "{$admin_id}");
                $zip->close();
                $this->redirect("https://ipa.zyttx.com/index.php?channel={$admin_id}");
            }
            Yaf_Dispatcher::getInstance()->disableView();
        }
    }

    private function downFile($file_name, $file_dir)
    {
        //检查文件是否存在
        if (!file_exists($file_dir . $file_name)) {
            header('HTTP/1.1 404 NOT FOUND');
        } else {
            //以只读和二进制模式打开文件
            $file = fopen($file_dir . $file_name, "rb");
            //告诉浏览器这是一个文件流格式的文件
            Header("Content-type: application/octet-stream");
            //请求范围的度量单位
            Header("Accept-Ranges: bytes");
            //Content-Length是指定包含于请求或响应中数据的字节长度
            Header("Accept-Length: " . filesize($file_dir . $file_name));
            //用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
            Header("Content-Disposition: attachment; filename=" . $file_name);
            //读取文件内容并直接输出到浏览器
            echo fread($file, filesize($file_dir . $file_name));
            fclose($file);
            exit ();
        }
    }
}
