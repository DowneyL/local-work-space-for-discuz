$('#orderDetail .arrow').click(function (event) {
    if ($('#orderDetail').hasClass('detail-open')) {
        $('#orderDetail .detail-ct').slideUp(500, function () {
            $('#orderDetail').removeClass('detail-open');
        });
    } else {
        $('#orderDetail .detail-ct').slideDown(500, function () {
            $('#orderDetail').addClass('detail-open');
        });
    }
});
var myTimer;
function timer(intDiff) {
    var i = 0;
    myTimer = window.setInterval(function () {
        i++;
        var day = 0,
            hour = 0,
            minute = 0,
            second = 0;//时间默认值
        if (intDiff > 0) {
            day = Math.floor(intDiff / (60 * 60 * 24));
            hour = Math.floor(intDiff / (60 * 60)) - (day * 24);
            minute = Math.floor(intDiff / 60) - (day * 24 * 60) - (hour * 60);
            second = Math.floor(intDiff) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
        }
        if (minute <= 9) minute = '0' + minute;
        if (second <= 9) second = '0' + second;
        $('#hour_show').html('<s id="h"></s>' + hour + '时');
        $('#minute_show').html('<s></s>' + minute + '分');
        $('#second_show').html('<s></s>' + second + '秒');
        if (hour <= 0 && minute <= 0 && second <= 0) {
            qrcode_timeout()
            clearInterval(myTimer);

        }
        intDiff--;
    }, 1000);
}


try {
    document.ontouchstart = function () {
       $('#use').hide();
    }
} catch (e) {

}
function getNowFormatDate() {
    var date = new Date();
    var seperator1 = "-";
    var seperator2 = ":";
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
        + " " + date.getHours() + seperator2 + date.getMinutes()
        + seperator2 + date.getSeconds();
    return currentdate;
}
myDate = function (s, t, g) {
    if (t == "null") {
        return "?"
    }
    s = "-" + s;
    s = s.toLocaleLowerCase();
    a = s.indexOf("y");
    b = s.indexOf("-m");
    c = s.indexOf("d");
    d = s.indexOf("h");
    e = s.indexOf(":m");
    f = s.indexOf("s");
    t ? date = new Date(t * 1000) : date = new Date();
    b < 0 ? as = "" : as = "-";
    c < 0 ? bs = "" : bs = "-";
    d < 0 ? cs = "" : cs = " ";
    e < 0 ? ds = "" : ds = ":";
    f < 0 ? es = "" : es = ":";
    g ? g1 = ":00" : g1 = ""; //设置显示时分秒则显示否则不显示
    g ? g2 = ":00" : g2 = "";

    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    a >= 0 ? a = date.getFullYear() + as : a = "";
    b >= 0 ? b = month + bs : b = "";
    c >= 0 ? c = strDate + cs : c = "";
    d >= 0 ? d = date.getHours() + ds : d = g1;
    e >= 0 ? e = date.getMinutes() + es : e = g1;
    f >= 0 ? f = date.getSeconds() : f = g2;
    var currentdate = a + b + c + d + e + f;
    return currentdate;
}
createLinkstring = function (para) {
    var arg = "";
    for (i in para) {
        if((!para[i]&&typeof para[i]!='number')|| i == 'password' || (i+'').toLowerCase == 'creattime')continue; //跳过传递此类
        arg += i + "=" + encodeURIComponent(para[i]) + "&";
    }
    //去掉最后一个&字符
    arg = arg.substring(0, arg.length - 1)
    return arg;
}
callback = function (data) {
    var status = parseInt(data.status);
    if (!isNaN(status) && status >= 0) {
        data.id = user_data.codePay_id; //用户标识
        startNotiry(data); //启用支付成功通知服务
        show_Qrcode(data); //显示二维码
        log(data);
    } else {
        alert("订单创建失败 请更换其他支付方式或反馈管理员错误信息：" + data.msg);
    }
}
qrcode_timeout = function () { //二维码超时则停止显示二维码
    if (notifyServer && notifyServer.started)notifyServer.disconnect();
    $("#show_qrcode").attr("src", '');
    $("#show_qrcode").attr("alt", '二维码失效');

    $("#msg h1").html("支付超时 请重新提交订单"); //过期提醒信息
}
show_Qrcode = function (data) {
    if (!data)return;
    if (data.qrcode)$("#show_qrcode").attr("src", data.qrcode); //二维码更新
    if (data.money) $("#money").html('￥' + data.money); //金额改变 云端匹配最佳金额
    if (parseInt(data.type) == 1 && data.tag) { //支付宝显示备注
        $("#msg h1").html("备注：" + data.tag); //过期提醒信息
    } else if (data.message) {
        $("#msg h1").html(data.message); //过期消息
    }
    show_desc(data);
}
function getDescMode(key, value) {
    var reslut = value ? '<dt>' + key + '</dt><dd>' + value + '</dd>' : '';
    return reslut;
}
show_desc = function (data) { //商品描述
    var html = '';
    html += getDescMode('账号', data.pay_id);
    html += getDescMode('金额', "￥" + data.money);
    html += getDescMode('云端单号', data.order_id);
    html += getDescMode('创建时间', getNowFormatDate());
    html += getDescMode('过期时间', myDate("y-m-d h:m:s", data.endTime));
    $("#desc").html(html);
}
getApiHost = function (data) {
    if (data && data.https) {
        return 'https://codepay.fateqq.com:51888'
    } else {
        return 'http://codepay.fateqq.com:52888'
    }
}
log = function (s) {
    try {
        console.log(s);
    } catch (e) {

    }
}
var notifyServer
function startNotiry(data) {
    data.notiry_host = data.notiry_host || getApiHost(data);
    notifyServer = notify = io.connect(data.notiry_host);
    notify.emit('notify', data);
    notify.on('notify', function (o) { //同步通知服务返回
        log(o);
        notifyServer.started = true;
        if (!o)return;
        if (user_data && user_data.qrcode_url) {
            o.qrcode = user_data.qrcode_url + '?money=' + o.money + '&type=' + o["type"] + '&tag=' + o.tag;
        }
        if (o.qrcode && $("#show_qrcode").src != o.qrcode)$("#show_qrcode").attr("src", o.qrcode); //二维码
        if (o.money) $("#money").html('￥' + o.money); //金额改变
        if (o.msg)$("#msg h1").html(o.msg); //过期
        if (o.outTime) {
            try {
                clearInterval(myTimer);
            } catch (e) {
            }
            timer(parseInt(o.outTime));
        }

    });
    notify.on('sussecc', function (o) { //成功后跳转
        log(o);
        var status = parseInt(o.status);
        $("#show_qrcode").attr("src", '');
        $("#show_qrcode").attr("alt", '支付成功');
        if (!isNaN(status) && status > 1)$("#msg h1").html('支付成功 请查看是否到账');
        if (user_data.return_url) {
            location.href = user_data.return_url + "?" + createLinkstring(o);
        } else {
            alert('付款成功 请核对是否到账')
        }
    });
    notify.on('disconnect', function (o) {//服务器断开 无法通知
        log(o);
    });
    notify.on('warning', function (o) {//系统通知
        if (o.msg)$("#msg h1").html(o.msg);
    });
}

$(function () {
    timer(user_data.outTime||360);
});
