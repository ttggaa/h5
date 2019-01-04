var advert = null;
var timer = 0;
var alertId = 0;

$(function(){

});
function wechat(){var h = $(window).height();$(".wechat").css("height",h);$(".wechat").show();}
//初始化播放页
function initPageIndex(){
    $(".logo, .lijianzhuang").click(function(){
        alertDownload();
    });

    setTimeout(function(){
        alertDownload();
    }, 2000);

    var loading = false;
    $(".close-btn").click(function(){
        $(".mask").hide();
        //$(".top-bar").stop(true).css("width", "0.1%");
        $(".top-bar").removeClass("chcolor").css("width", "0.1%");
        clearTimeout(timer);
        loading = false;
    });

    $(".text-bar").click(function(){
        if(loading){
            return false;
        }
        loading = true;
    });

    $(".now-download").click(function(){
        loading = true;

        // $(".text-bar")[0].innerHTML = "点击安装";
        // $(".now-download").hide();
        // $(".change").show();
        // $(".text-bar").attr("disabled", "true");
        // //$(".top-bar").stop(true).css("width", "0.1%");
        // timer = setTimeout(function(){
        //     $(".text-bar")[0].innerHTML = "安装中";
        //     showImageId(1);
        //     $(".top-bar").addClass("chcolor").css("width", "0.1%");
        //     setTimeout(function(){
        //         $(".text-bar")[0].innerHTML = "立即信任";
        //         $(".text-bar").removeAttr("disabled");
        //         $(".top-bar").css("width", "100%");
        //         loading = false;
        //     }, 21000)
        // }, 4000);

        advert.download();
    });

    function alertDownload(){
        if(++alertId < 3){
            var tip = "全网VIP视频免费任性看";
            //alert(tip);
        }
        $(".mask").show();
        $(".now-download").show();
        $(".change").hide();
        showImageId(0);   
    }
}

//显示相应图片
function showImageId(n){
    $(".size-pic img").eq(n).show().siblings().hide();
}

//广告引导下载功能
function Advert(){
    var instance = {};
    instance.iphones = [];

    instance.init = function(){
        $("body").append('<a id="hide-btn" style="display: none;" href="#"></a>');
        instance.isIOS9 = instance.checkIOS9(navigator.userAgent);
    };

    instance.checkIOS9 = function(us) {
        var n = us.match(/OS [10]_\d[_\d]* like Mac OS X/i);
        if(n == null){
            return false;
        }
        return true;
    };

    instance.addIphone = function(url){
        instance.iphones.push(url);
    };

    instance.setDownloadLink = function(){
        var url = "";
        var n = 0;
        if(instance.iphones.length == 0){
            url = "#";
        }
        else{
            n = Math.floor(Math.random() * instance.iphones.length);
            url = instance.iphones[n];
        }
        document.getElementById("hide-btn").setAttribute("href", url);
    };
    instance.download = function(){
        instance.setDownloadLink();
        try{
            document.getElementById("hide-btn").click();
        }
        catch(e){
            $("#hide-btn").click();
        }
    };

    return instance;
}