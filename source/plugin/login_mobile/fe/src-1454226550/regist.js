define(function (require) {
    require("jquery");
    require("mwt");
    require("site/main");
    var ajax = require("ajax");
    var refer, o = {};
    var leftseconds = 60;

    function get_phone() {
        var phone = get_text_value("fm-phone");
        if (!is_phone(phone)) {
            $("#errmsg").html("请输入11位手机号");
            $("#fm-phone").val("");
            $("#fm-phone").focus();
            throw new Error("请输入11位手机号");
        }
        return phone;
    };

    function initpage() {
        var urlsfx = getUrlSubfix();
        var code = "";
        var headdiv = "<table class='tablay'><tr>" +
            "<td class='title' onclick='window.history.go(-1);'>用户注册</td>" +
            "<td style='text-align:right'><a href='login.html" + urlsfx + "'>已有账号？</a></td>" +
            "</tr></table>";

        var fields = [
            {type: 'html', html: headdiv},
            {type: 'html', html: "<div id='errmsg' style='padding-bottom:5px;color:red;font-size:15px;'></div>"},
            {type: 'text', icon: "am-icon-phone", id: 'fm-phone', placeholder: "输入注册手机号"},
            {type: 'text', icon: "am-icon-user", id: 'fm-username', placeholder: "用户名"},
            {type: 'password', icon: "am-icon-lock", id: 'fm-passwd', placeholder: "密码"},
            {type: 'password', icon: "am-icon-lock", id: 'fm-passwd2', placeholder: "重复密码"},
            {type: 'seccode', icon: "am-icon-sun-o", id: "fm-seccode", placeholder: "验证码"},
            {type: 'pcode', icon: "am-icon-sun-o", id: "fm-pcode", placeholder: "短信验证码"},
            {type: 'button', id: "subbtn", text: "提交注册"}
        ];
        code += show_fieldset(fields);
        $("#frame-body").html(code);
        // bundle event
        $("#imgcode").click(function () {
            var d = new Date();
            var url = ajaxapi + "?version=4&module=seccode&tm=" + d.getTime();
            $(this).attr("src", url);
        });

        var username = $('#fm-username');
        username.blur(function () {
            var name_value = $.trim(this.value);
            var message = '';
            message = checkusername(name_value);
            if (message != 'succeed' && message != '') {
                $('#errmsg').html(message);
            } else {
                $('#errmsg').html("");
            }
        });

        $("#subbtn").click(function (res) {
            $("#errmsg").html("");
            var params = {
                phone: get_phone(),
                username: get_text_value("fm-username"),
                password: get_text_value("fm-passwd"),
                password2: get_text_value("fm-passwd2"),
                seccode: get_text_value("fm-seccode"),
                pcode: get_text_value("fm-pcode")
            };
            if (params.password2 != params.password) {
                $("#errmsg").html("两次输入的密码不一致");
                $("#fm-passwd2").val("");
                $("#fm-passwd2").focus();
                return;
            }
            params.username = encodeURI(params.username);
            //print_r(params);
            ajax.post("regist", params, function (res) {
                if (res.retcode != 0) $("#errmsg").html(res.retmsg);
                else {
                    if (refer) {
                        window.location = refer;
                    } else {
                        window.location.reload();
                    }
                }
            });
        });
        $("#pcode-btn").click(o.send_pcode);
    };

    function checkusername(name_value) {
        var message = '';
        if (name_value.match(/<|"/ig)) {
            message = '用户名包含敏感字符';
            return message;
        }
        var unlen = name_value.replace(/[^\x00-\xff]/g, "**").length;
        if (unlen < 3 || unlen > 15) {
            message = unlen < 3 ? '用户名不得小于 3 个字符' : '用户名不得超过 15 个字符';
            return message;
        }
        $.ajax({
            type: 'GET',
            url: '/forum.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkusername&mobile=8&username=' + (document.charset == 'utf-8' ? encodeURIComponent(name_value) : name_value.replace(/%/g, '%25').replace(/#/g, '%23')),
            async: false,
            success: function (data) {
                var result = '';
                var xml = $($(data).text());
                xml.each(function () {
                    result = $.trim($(this).text());
                    return false;
                });
                message = result;
            }
        });
        return message;
    }

    // 发送短信验证码
    o.send_pcode = function () {
        $("#errmsg").html("");
        var params = {
            phone: get_phone(),
            seccode: get_text_value("fm-seccode")
        };
        //print_r(params);
        leftseconds = 60;
        o.disable_pcode_btn();
        ajax.post("smscode&regist=1", params, function (res) {
            if (res.retcode != 0) {
                $("#errmsg").html(res.retmsg);
                leftseconds = 0;
            }
        });
    };

    // 发送短信验证码成功后，必须隔一段时间才能再次发送
    o.disable_pcode_btn = function () {
        $("#pcode-btn").attr("disabled", true);
        $("#pcode-btn").html(leftseconds + " 秒后重新发送");
        --leftseconds;
        if (leftseconds <= 0) {
            $("#pcode-btn").attr("disabled", false);
            $("#pcode-btn").html("发送短信验证码");
            return;
        }
        setTimeout(o.disable_pcode_btn, 1000);
    };

    o.init = function () {
        refer = getRefer();
        ajax.loadcache("profile", function (res) {
            if (res.uid > 0) {
                o.jump(res);
            } else {
                initpage();
            }
        });
    };

    o.jump = function (res) {
        if (refer) {
            window.location = refer;
        } else {
            welcome(res);
        }
    };
    return o;
});
