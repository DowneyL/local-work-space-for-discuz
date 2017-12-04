//判断函数
var checkObj = function(){};
checkObj.prototype = {
    //有待完善，可以传入相关的敏感词汇
    checkMingan:function(obj){
        //敏感词验证
        if(obj.val().indexOf("'")>-1||obj.val().indexOf('"')>-1 || obj.val().indexOf('<')>-1 || obj.val().indexOf('>')>-1){
            return '有敏感词汇';
        }else{
            return true;
        }
    },
    checkPhone:function(objPhone){
        // 电话号码验证
        var value = objPhone.val();
        var regTel1 = /^1[34578]\d{9}$/.test(value);//带区号的固定电话
        var regTel2 = /^(\d{3,4}-)?\d{6,8}(-\d{3})?/.test(value);//不带区号的固定电话(\d{3,4}-)?\d{6,8}(-\d{3})?
        if (value != ""){
            if (!regTel1 && !regTel2){ //&& !regTel2
                return "电话号码输入有误！";
            }else{
                return true;
            }
        }else {
            return "请输入电话号码！";
        }
    },
    checkEmail:function(obj){
        // 邮箱验证
        var myemail = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
        if(!myemail.test(obj.val())){
            return '请输入有效的邮箱！';
        }else{
            return true;
        }
    },
    checkQQ:function(objqq){
        //QQ验证
        var myqq = (/^[1-9][0-9]{4,9}$/);
        if(!myqq.test(objqq.val())){
            return '请输入正确的QQ号';
        }else{
            return true;
        }
    },
    checknum:function(Objnum,max,min){//传入值和最大值以及最小值
        //是否是数字验证
        if(isNaN(Objnum.val())){
            Objnum.val('');
            return '数字';
        }else{//如果是数字，验证他的大小
            if(Objnum.val()>max){
                var msg = "输入值请小于："+max;
                return msg;
            }else if(Objnum.val()<min){
                var msg = '输入值请大于：'+min;

            }else{
                return true;
            }
            
        }
    },
    setInput:function(){
        //创建新的input标签
    },
    addError:function(obj,istrue){
        if(istrue!=true){
            if(obj.next().length<1){
                obj.parent().append('<span class="error">'+istrue+'</span>');
            }else{
                obj.next().html(istrue);
                obj.next().attr('class','error');
            }
        }else{
            if(obj.next().length<1){
                obj.parent().append('<span class="ok"></span>');
            }else{
                obj.next().html('');
                obj.next().addClass('ok').removeClass('error');
            }

        }
    }
}
var checkit = new checkObj();
//判断输入是否有空
function isnull (){
    var inputs = jQuery('#apply-info input[type="text"]');
    for(var i=0;i<inputs.length;i++){
        inputs.eq(i).blur(function(){
            //alert(!jQuery.trim(jQuery(this).val()))
            var ismin;
            if(!jQuery.trim(jQuery(this).val())){//如果为空
                ismin = '必填';
            }else{
                ismin = checkit.checkMingan(jQuery(this));
            }
            checkit.addError(jQuery(this),ismin);
        });
    }
}
isnull();
function isnull2(){
var inputs = jQuery('#apply-info #tbodys input[type="text"]');
    for(var i=0;i<inputs.length;i++){
        inputs.eq(i).blur(function(){
            //alert(!jQuery.trim(jQuery(this).val()))
            var istrue;
            if(!jQuery.trim(jQuery(this).val())){//如果为空
                istrue = '必填';
            }else{
                istrue = checkit.checkMingan(jQuery(this));
            }
             if(istrue!=true){
            if(jQuery(this).next().length<1){
                jQuery(this).parent().append('<span class="error">'+istrue+'</span>');
            }else{
                jQuery(this).next().html(istrue);
                jQuery(this).next().attr('class','error');
            }
        }else{
            if(jQuery(this).next().length<1){
                jQuery(this).parent().append('<span class="okc"></span>');
            }else{
                jQuery(this).next().html('');
                jQuery(this).next().addClass('okc').removeClass('error').removeClass('ok');
            }

        }
        });
    }
}
jQuery('#apply-info #tbodys').delegate('input','click',function(){//工作经历表格验证
    isnull2();
});


//提交时判断是否有表单为空
function checkNull(){
    var flags = true;
    var objs = jQuery('#apply-info div.open input[type="text"]');
    //对象中有textera并且为空才判断
    /*
    var textarea = jQuery('#apply-info div.open textarea').length>0?jQuery('div.open textarea'):null;
    //filefujianChange();
    if(textarea &&(!textarea.val() || textarea.next().hasClass('error')) ){
         //alert(textarea);
         var isfalse;
         if(!textarea.val()){
            isfalse = '此项必填';
         }
        if(textarea.next().length<1){
            checkit.addError(textarea,isfalse);
        }
        flags = false;
    }
    */
    if(jQuery('#apply-info input[type="file"]').prev('span').hasClass('error')){flags=false;}
    for(var i=0;i<objs.length; i++){//for-in语句遇到null或者undefined语句时候，会抛出错误
        var nextsb = objs.eq(i).next().length>0?objs.eq(i).next():null;
        if(!jQuery.trim(objs.eq(i).val())){//如果输入框里面为空
            flags = false;
            if(!nextsb){
                flags=false;
                var isfalse = '必填';
                checkit.addError(objs.eq(i),isfalse);
            }else{
                nextsb.html('必填');
                nextsb.attr('class','error');
            }
        }else{
            if(nextsb && nextsb.hasClass('error')){
                flags = false;
            }
        }

    }
    return flags;
}
//textarea输入框验证
/*
var textarea = jQuery('#apply-info textarea');
textarea.keyup(
    function(){
        var isfalse;
        if(!jQuery(this).val()){
            isfalse = '此项必填';
        }else{
            isfalse = jQuery(this).val().length<20 ?'不能小于20个字符':true;
            if(isfalse==true){
                isfalse = checkit.checkMingan(jQuery(this));
            }
            
        }
        checkit.addError(jQuery(this),isfalse);
    }
)
*/