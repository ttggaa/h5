var dtime = '_deadtime';
var api_url = 'http://h5.zyttx.com';
//领取礼包
$(document).on('click', '.gift_btn', function(event) {
    event.stopPropagation(); //阻止事件冒泡即可
    var obj = $(this);
    var status = $(obj).find('span').html();
    if (status == '复制') {
        var data_key = $(obj).attr('data-key');
        var clipBoard = api.require('clipBoard');
        clipBoard.set({
            value: data_key
        }, function(ret2, err2) {
            if (ret2) {
                api.alert({
                    title: '礼包码已复制',
                    msg: data_key,
                }, function(ret, err) {
                    //重新获取礼包列表
                });
            } else {
                alert(JSON.stringify(err));
            }
        })
    } else if (status == '领取') {
        var gift_id = $(obj).attr('data-id');
        var user_info = get('user_info', '');
        if (user_info == '') {
            api.openWin({
                name: 'login',
                url: './login.html',
            });
        } else {
            user_info = JSON.parse(user_info);
            var user_id = user_info.user_id;
            // api.showProgress({
            //     title: '努力加载中...',
            //     text: '先喝杯茶...',
            //     // animationType:'',
            //     modal: true
            // });
            fnOpenkeyFrame();
            api.ajax({
                    url: 'http://h5.zyttx.com/api/getGift',
                    method: 'post',
                    data: {
                        values: {
                            user_id: user_id,
                            gift_id: gift_id
                        }
                    }
                },
                function(ret, err) {
                    if (ret) {
                        var clipBoard = api.require('clipBoard');
                        clipBoard.set({
                            value: ret.cdkey
                        }, function(ret2, err2) {
                            if (ret2) {
                                api.alert({
                                    title: '礼包码已复制',
                                    msg: ret.cdkey,
                                }, function(ret3, err3) {
                                    //刷新当前项
                                    $(obj).find('span').html('复制');
                                    $(obj).attr('data-key', ret.cdkey);
                                    $(obj).parents('li').find('.title').after("<span class=\"aui-margin-l-5\">cdkey:" + ret.cdkey + "</span>");
                                });
                            } else {
                                alert(JSON.stringify(err));
                            }
                        })
                        // json_encode(array('msg'=>'领取成功!','cdkey'=>$cdkey))
                    } else {
                        api.alert({
                            msg: err.msg
                        });
                    }
                    // api.hideProgress();
                    fncloseKeyFrame();
                })
        }
    }
})
