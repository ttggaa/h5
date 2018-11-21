;(function ($) {
    $.fn.lubo = function (options) {
// var defaults={
// }
// //通过覆盖来传参数
// var options=$.extend(defaults,options);
        return this.each(function () {
            var _lubo = jQuery('.lubo');
            var _box = jQuery('.lubo_box');
            var _this = jQuery(this); //
            var luboHei = _box.height();
            var Over = 'mouseover';
            var Out = 'mouseout';
            var Click = 'click';
            var Li = "li";
            var _cirBox = '.cir_box';
            var cirOn = 'cir_on';
            var _cirOn = '.cir_on';
            var cirlen = _box.children(Li).length; //圆点的数量  图片的数量
            var luboTime = 2000; //轮播时间
            var switchTime = 400;//图片切换时间
            cir();
            Btn();

//根据图片的数量来生成圆点
            function cir() {
                _lubo.append('<ul class="cir_box"></ul>');
                var cir_box = jQuery('.cir_box');
                for (var i = 0; i < cirlen; i++) {
                    cir_box.append('<li style="" value="' + i + '"></li>');
                }
                var cir_dss = cir_box.width();
                cir_box.css({
                    left: '50%',
                    marginLeft: -cir_dss / 2,
                    bottom: '5%'
                });
                cir_box.children(Li).eq(0).addClass(cirOn);
            }

//生成左右按钮
            function Btn() {
                _lubo.append('<div class="lubo_btn"></div>');
                var _btn = jQuery('.lubo_btn');
                _btn.append('<div class="left_btn l">&lt;</div><div class="right_btn r">&gt;</div>');
                var leftBtn = jQuery('.left_btn');
                var rightBtn = jQuery('.right_btn');
//点击左面按钮
                leftBtn.bind(Click, function () {
                    var cir_box = jQuery(_cirBox);
                    var onLen = jQuery(_cirOn).val();
                    _box.children(Li).eq(onLen).stop(false, false).animate({
                        opacity: 0
                    }, switchTime);
                    if (onLen == 0) {
                        onLen = cirlen;
                    }
                    _box.children(Li).eq(onLen - 1).stop(false, false).animate({
                        opacity: 1
                    }, switchTime);
                    cir_box.children(Li).eq(onLen - 1).addClass(cirOn).siblings().removeClass(cirOn);
                })
//点击右面按钮
                rightBtn.bind(Click, function () {
                    var cir_box = jQuery(_cirBox);
                    var onLen = jQuery(_cirOn).val();
                    _box.children(Li).eq(onLen).stop(false, false).animate({
                        opacity: 0
                    }, switchTime);
                    if (onLen == cirlen - 1) {
                        onLen = -1;
                    }
                    _box.children(Li).eq(onLen + 1).stop(false, false).animate({
                        opacity: 1
                    }, switchTime);
                    cir_box.children(Li).eq(onLen + 1).addClass(cirOn).siblings().removeClass(cirOn);
                })
            }

//定时器
            int = setInterval(clock, luboTime);

            function clock() {
                var cir_box = jQuery(_cirBox);
                var onLen = jQuery(_cirOn).val();
                _box.children(Li).eq(onLen).stop(false, false).animate({
                    opacity: 0
                }, switchTime);
                if (onLen == cirlen - 1) {
                    onLen = -1;
                }
                _box.children(Li).eq(onLen + 1).stop(false, false).animate({
                    opacity: 1
                }, switchTime);
                cir_box.children(Li).eq(onLen + 1).addClass(cirOn).siblings().removeClass(cirOn);
            }

// 鼠标在图片上 关闭定时器
            _lubo.bind(Over, function () {
                clearTimeout(int);
            });
            _lubo.bind(Out, function () {
                int = setInterval(clock, luboTime);
            });
//鼠标划过圆点 切换图片
            jQuery(_cirBox).children(Li).bind(Over, function () {
                var inde = jQuery(this).index();
                jQuery(this).addClass(cirOn).siblings().removeClass(cirOn);
                _box.children(Li).stop(false, false).animate({
                    opacity: 0
                }, switchTime);
                _box.children(Li).eq(inde).stop(false, false).animate({
                    opacity: 1
                }, switchTime);
            });
        });
    }
})(jQuery);

$(function () {
    $(".lubo").lubo({});
})


/*alert弹出层*/
setSize();
addEventListener('resize', setSize);

function setSize() {
    document.documentElement.style.fontSize = document.documentElement.clientWidth / 0 * 0 + 'px';
}

function jqalert(param) {
    var title = param.title,
        content = param.content,
        yestext = param.yestext,
        notext = param.notext,
        yesfn = param.yesfn,
        nofn = param.nofn,
        nolink = param.nolink,
        yeslink = param.yeslink,
        prompt = param.prompt,
        click_bg = param.click_bg;

    if (click_bg === undefined) {
        click_bg = true;
    }
    if (yestext === undefined) {
        yestext = '';
    }
    if (!nolink) {
        nolink = 'javascript:void(0);';
    }
    if (!yeslink) {
        yeslink = 'javascript:void(0);';
    }

    var htm = '';
    htm += '<div class="AlertTc" id="AlertTc"><div class="Alert">';
    if (title) htm += '<h2 class="title">' + title + '</h2>';
    if (prompt) {
    } else {
        htm += '<div class="content">' + content + '</div>';
    }
    if (!notext) {
        htm += '<div class="fd-btn"><a href="' + yeslink + '" class="confirm" id="yes_btn">' + yestext + '</a></div>'
        htm += '</div>';
    } else {
        htm += '<div class="fd-btn">' +
            '<a href="' + nolink + '"  data-role="cancel" class="cancel">' + notext + '</a>' +
            '</div>';
        htm += '</div>';
    }
    $('body').append(htm);
    var al = $('#AlertTc');
    if (click_bg === true) {
        $(document).delegate('#AlertTc', 'click', function () {
            setTimeout(function () {
                al.remove();
            }, 300);
            yesfn = '';
            nofn = '';
            param = {};
        });
    }

}

/*弹窗内容*/
$(function () {
    $('.demo1').click(function () {
        jqalert({
            title: 'VV游戏推广平台更新公告<em></em>',
            content: ' <p class="font">11月11日版本更新公告</p>   																																							<p>11月11日 7:00-9:00，更新期间用户可正常注册、登陆、充值，或出现后台短时登出，或无法登入，给您带来不便敬请谅解。</p>      																							<p class="font">一、更新内容：</p>																																						   <p>1、推广员业绩汇总页面，添加搜索条件推广员名称，默认显示全部推广员，下拉可显示单个推广员姓名</p>																																								<p>2、新增改绑功能，公会可对渠道下的玩家进行改绑</p>																																										<p>3、老板后台能够跨小组进行操作，跨小组所下的扶持扣除该小组的元宝池余额</p>																																						<p>4、公会后台新增页面</p>																																						                <p class="font">二、财务结算单：</p>																																						   <p>1、游戏收入对账表</p>          																																						   <p>2、补款扣款记录</p>',
            notext: ''
        })
    })
});


/*密码*/
(function ($) {
    $.fn.togglePassword = function (options) {
        var s = $.extend($.fn.togglePassword.defaults, options),
            input = $(this);

        $(s.el).bind(s.ev, function () {
            "password" == $(input).attr("type") ?
                $(input).attr("type", "text") : $(input).attr("type", "password");
        });
    };
    $.fn.togglePassword.defaults = {
        ev: "click"
    };
}(jQuery));


$(function () {
    $('.password').togglePassword({
        el: '.togglePassword'
    });
});


/*登录弹窗*/
$(function () {
//alert($(window).height());
        //登录
        $('.Login-Pop').click(function () {
            $('.code2').hide();
            $('.code').center();
            $('.goodcover').show();
            $('.code').fadeIn();
        });
        $('.closebt').click(function () {
            $('.code').hide();
            $('.goodcover').hide();
        });
        $('.goodcover').click(function () {
            $('.code').hide();
            $('.code2').hide();
            $('.goodcover').hide();
        });
        //注册
        $('.Register-Pop').click(function () {
            $('.code').hide();
            $('.code2').center();
            $('.goodcover').show();
            $('.code2').fadeIn();
        });
        $('.closebt2').click(function () {
            $('.code2').hide();
            $('.goodcover').hide();
        });
        jQuery.fn.center = function (loaded) {
            jQuery.fn.center = function (loaded) {
                var obj = this;
                body_width = parseInt($(window).width());
                body_height = parseInt($(window).height());
                block_width = parseInt(obj.width());
                block_height = parseInt(obj.height());

                left_position = parseInt((body_width / 2) - (block_width / 2) + $(window).scrollLeft());
                if (body_width < block_width) {
                    left_position = 0 + $(window).scrollLeft();
                }
                ;

                top_position = parseInt((body_height / 2) - (block_height / 2) + $(window).scrollTop());
                if (body_height < block_height) {
                    top_position = 0 + $(window).scrollTop();
                }
                ;

                if (!loaded) {

                    obj.css({
                        'position': 'fixed'
                    });
                    obj.css({
                        // 'top': ($(window).height() - $('.code').height()) * 0.4,
                        'top':($(window).height())*0.1,
                        'left': left_position
                    });
                    $(window).bind('resize', function () {
                        obj.center(!loaded);
                    });
                    $(window).bind('scroll', function () {
                        obj.center(!loaded);
                    });

                } else {
                    obj.stop();
                    obj.css({
                        'position': 'absolute'
                    });
                    obj.animate({
                        'top': top_position
                    }, 200, 'linear');
                }
            }
        }
    }
)

/*输入框提示*/
$(document).ready(function () {
    $(function () {
        var animationLibrary = 'animate';
        $.easing.easeOutQuart = function (x, t, b, c, d) {
            return -c * ((t = t / d - 1) * t * t * t - 1) + b;
        };
        $('[ripple]:not([disabled],.disabled)').on('mousedown', function (e) {
            var button = $(this);
            var touch = $('<touch><touch/>');
            var size = button.outerWidth() * 1.8;
            var complete = false;
            $(document).on('mouseup', function () {
                var a = {'opacity': '0'};
                if (complete === true) {
                    size = size * 1.33;
                    $.extend(a, {
                        'height': size + 'px',
                        'width': size + 'px',
                        'margin-top': -size / 2 + 'px',
                        'margin-left': -size / 2 + 'px'
                    });
                }
                touch[animationLibrary](a, {
                    duration: 500,
                    complete: function () {
                        touch.remove();
                    },
                    easing: 'swing'
                });
            });
            touch.addClass('touch').css({
                'position': 'absolute',
                'top': e.pageY - button.offset().top + 'px',
                'left': e.pageX - button.offset().left + 'px',
                'width': '0',
                'height': '0'
            });
            button.get(0).appendChild(touch.get(0));
            touch[animationLibrary]({
                'height': size + 'px',
                'width': size + 'px',
                'margin-top': -size / 2 + 'px',
                'margin-left': -size / 2 + 'px'
            }, {
                queue: false,
                duration: 500,
                'easing': 'easeOutQuart',
                'complete': function () {
                    complete = true;
                }
            });
        });
    });
    //注册
    $("#RegisterForm").validate({
        errorPlacement: function (error, element) {
            $(element)
            error.appendTo(element.parent());
        },
        errorElement: "erroru",
        errorClass:'my_error',
        rules: {
            username: {
                required: true,
                minlength: 6,
                maxlength: 20
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 20
            },
            repassword:{
                required: true,
                minlength: 6,
                maxlength: 20,
                equalTo: ".register_password"
            },
            nickname: {
                required: true,
            },
            pay_number: {
                required: true,
            },
            boxname: {
                required: true,
            },
            qq1: "required",
            qq2: "required",
            captcha: "required",
        },
        messages: {
            username: {
                required: "请输入用户名",
                minlength: "用户名至少6个字符",
                maxlength: "用户名最多20个字符",
            },
            password: {
                required: "请输入密码",
                minlength: "密码至少6个字符",
                maxlength: "密码最多20个字符",
            },
            repassword: {
                required: "请输入验证密码",
                minlength: "验证密码至少6个字符",
                maxlength: "验证密码最多20个字符",
                equalTo:'两次密码不一致',
            },
            nickname: {
                required: "真实姓名必须",
            },
            pay_number: "支付宝账户必须",
            boxname: "盒子名称必须",
            qq1: "qq客服必须",
            qq2: "qq群必须",
            captcha: "验证码必须"
        },
        submitHandler: function (form) {
            var data = $(form).serializeArray();
            $.ajax({
                type: "POST", //提交方式
                url: "index/index/ajaxRegister",//路径
                data: data,
                success: function (result) {//返回数据根据结果进行相应的处理
                    if (result.code) {
                        alert(result.mgs);
                        $('.Login-Pop').trigger('click');
                    } else {
                        alert(result.mgs);
                        //刷新验证码
                        $('.register_captcha').trigger('click');
                        // $("#tipMsg").text("删除数据失败");
                    }
                }
            });
        },
    });
    //登录
    $("#LoginForm").validate({
        errorPlacement: function (error, element) {
            $(element)
            error.appendTo(element.parent());
        },
        errorElement: "erroru",
        errorClass:'my_error',
        rules: {
            username: {
                required: true,
                minlength: 6,
                maxlength: 20
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 20
            },
            captcha: "required",
        },
        messages: {
            username: {
                required: "请输入用户名",
                minlength: "用户名至少6个字符",
                maxlength: "用户名最多20个字符",
            },
            password: {
                required: "请输入密码",
                minlength: "密码至少6个字符",
                maxlength: "密码最多20个字符",
            },
            captcha: "验证码必须"
        },
        submitHandler: function (form) {
            var data = $(form).serializeArray();
            $.ajax({
                type: "POST", //提交方式
                url: "index/index/ajaxlogin",//路径
                data: data,
                success: function (result) {//返回数据根据结果进行相应的处理
                    if (result.code) {
                        alert(result.mgs);
                        $('.Login-Pop').trigger('click');
                    } else {
                        alert(result.mgs);
                        //刷新验证码
                        $('.login_captcha').trigger('click');
                        // $("#tipMsg").text("删除数据失败");
                    }
                }
            });
        },
    });
});
$('#RegisterBtn').click(function(){
   $('#RegisterForm').submit();
});
$('#loginBtn').click(function () {
    $('#LoginForm').submit();
})
//刷新验证码
function refreshVerify(obj, id) {
    var img = obj;
    img.src = "/index/index/getVerify?id=" + id;

}
//弹窗
$('.Login-Pop').click(function() {
    $('.code').center();
    $('.goodcover').show();
    $('.code').fadeIn();
});
$('.closebt').click(function() {
    $('.code').hide();
    $('.goodcover').hide();
    $('.Login')[0].reset();
});
$('.goodcover').click(function() {
    $('.code').hide();
    $('.goodcover').hide();
    $('.Login')[0].reset();
});
