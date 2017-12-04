/*
 author：yyt
 content:招聘页面相关js验证
 date:2016-2-22 10:00
 */
var checkObj = function(){};
checkObj.prototype ={
    checkNull:function(elem){
        if(jQuery.trim(elem.val())==''){
            return '必填';
        }else{
            return true;
        }

    },

    checkMingan:function(obj){
        if(obj.val().indexOf("'")>-1||obj.val().indexOf('"')>-1 || obj.val().indexOf('<')>-1 || obj.val().indexOf('>')>-1){
            return '有敏感词汇';
        }else{
            return true;
        }
    },
    checkPhone:function(elem){
        var value = jQuery.trim(elem.val()) ;
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
    addError:function(elem,istrue,tag){
        if(istrue!=true){
            if(elem.next().length<1){
                elem.parent().append('<'+tag+' class="error">'+istrue+'</'+tag+'>');
            }else{
                elem.next().html(istrue);
                elem.next().attr('class','error');
            }
        }else{
            if(elem.next().length<1){
                elem.parent().append('<'+tag+' class="ok"></'+tag+'>');
            }else{
                elem.next().html('');
                elem.next().addClass('ok').removeClass('error');
            }

        }
    }
}
var checkit = new checkObj();
jQuery('#compphone').blur(function(){
    var istrue = checkit.checkPhone(jQuery(this));
    checkit.addError(jQuery(this),istrue,'span');
});
jQuery('#public-job-table').delegate( 'textarea','focus',function(){
    jQuery(this).addClass('edit');
});
jQuery('#public-job-table').delegate( '.text-say','blur',function(){
    var flag,flag2;
    jQuery(this).removeClass('edit');
    flag = checkit.checkNull(jQuery(this));
    if(flag==true){
        flag2 = jQuery(this).val().length<10?'不能少于10个字符':checkit.checkMingan(jQuery(this));
        if(flag2!=true){
            checkit.addError(jQuery(this),flag2,'p');
            jQuery(this).parent().css('height','105px');
        }else{
            jQuery(this).next().remove();
            jQuery(this).parent().css('height','71px');
        }
    }else{
        checkit.addError(jQuery(this),flag,'p');
        jQuery(this).parent().css('height','105px');
    }
});
jQuery('#public-job-table').delegate( '.welfare','blur',function(){
    var flag,flag2;
    jQuery(this).removeClass('edit');
    flag = checkit.checkNull(jQuery(this));
    if(flag==true){
        jQuery(this).next().remove();
        jQuery(this).parent().css('height','71px');
    }else{
        checkit.addError(jQuery(this),flag,'p');
        jQuery(this).parent().css('height','105px');
    }
});
jQuery('#public-job-table').delegate( 'input','blur',function(){
    var flag,flag2;
    flag = checkit.checkNull(jQuery(this));
    if(flag==true){
        flag2 = checkit.checkMingan(jQuery(this));
        if(flag2!=true){
            checkit.addError(jQuery(this),flag2,'p');
            jQuery(this).parent().css('height','105px');
        }else{
            jQuery(this).next().remove();
            jQuery(this).parent().css('height','71px');
        }
    }else{
        checkit.addError(jQuery(this),flag,'p');
        jQuery(this).parent().css('height','105px');
    }
});
function addjob(){
    var summ = jQuery('#public-job-table tbody>tr').length;
    summ++;
    var clsname = ['compname','zhiwei','workingtime','salary'];
    jQuery('#public-job-table tbody').append('<tr><td class="job-name"><i class="line"></i><input type="text" class="input-130" name="infos['+summ+'][job]" id=""></td><td class="text-edit"><textarea name="infos['+summ+'][request]"></textarea></td><td class="text-edit "><textarea name="infos['+summ+'][welfare]"></textarea></td></tr>');
    if(summ==1){
        jQuery('#public-job-table tbody>tr:first-of-type i.line').css('opacity','0');
    }else{
        jQuery('#public-job-table tbody>tr:first-of-type i.line').css('opacity','1');
    }
}
jQuery('tfoot').delegate('.addBtn','click',function(){
    addjob();
})

jQuery('#tbod').delegate('i.line','click',function(ev){
    if(jQuery('#tbod>tr').length==2){
        jQuery('tbody>tr i.line').css('opacity','0');
        jQuery(this).parent().parent().remove();
    }else if(jQuery('#tbod>tr').length<=1){
        return;
    }
    else{
        jQuery(this).parent().parent().remove();
    }
});


var isIE = /msie/i.test(navigator.userAgent) && !window.opera;
function filefujianChange(target){
    var fileSize = 0;
    if (isIE && !target.files) {
        var filePath = target.value;
        var fileSystem = new ActiveXObject("Scripting.FileSystemObject");
        var file = fileSystem.GetFile (filePath);
        fileSize = file.Size;
    } else {
        fileSize = target.files[0].size;
    }
    var size = fileSize / 1024;
    if(size>2000){
        jQuery(target).prev().css('color','red').html('图片不能超过2M')
        target.value="";
        return false;
    }
    var name=target.value;
    if(!name){
        return false;
    }else{
        var fileName = name.substring(name.lastIndexOf(".")+1).toLowerCase();
        if(fileName !="jpg" && fileName !="jpeg" && fileName !="png" ){
            jQuery(target).next().css({'color':'#f00'});
            target.value="";
            jQuery(target).prev().html('非法文件').css('color','red').addClass('error');
        }else{
            jQuery(target).prev().css({'color':'#48cb5e'}).removeClass('errpr').html(target.value+' 上传成功！');
            jQuery(target).next().remove();
            return true;
        }
    }

}

jQuery('#textpj').blur(function(){
    var flag,flag2;
    jQuery(this).removeClass('edit');
    flag = checkit.checkNull(jQuery(this));
    if(flag==true){
        flag2 = jQuery(this).val().length<20?'不能少于20个字符':checkit.checkMingan(jQuery(this));
        checkit.addError(jQuery(this),flag2,'span');
    }else{
        checkit.addError(jQuery(this),flag,'span');
    }
});
jQuery('#apply-info .selfpj input[name="compplace"]').blur(function(){
    var flag,flag2;
    flag = checkit.checkNull(jQuery(this));
    if(flag==true){
        flag2 = checkit.checkMingan(jQuery(this));
        checkit.addError(jQuery(this),flag2,'span');
    }else{
        checkit.addError(jQuery(this),flag,'span');
    }
});

/******************
 * Author: Ara
 * Date: 2017.8.25
 * 新增name为contact的表单验证。
 */
jQuery('#apply-info .selfpj input[name=contact]').blur(function(){
    var flag,flag2;
    flag = checkit.checkNull(jQuery(this));
    if(flag==true){
        flag2 = checkit.checkMingan(jQuery(this));
        checkit.addError(jQuery(this).next(),flag2,'span');
    }else{
        checkit.addError(jQuery(this).next(),flag,'span');
    }
});

function publicjob(){
    var flags=true;
    var textarea=jQuery('#apply-info textarea');
    for(var i=0;i<textarea.length;i++){
        if(textarea.eq(i).val()==''){
            flags=false;
            var flag = '必填';
            if(textarea.eq(i).parent().hasClass('text-edit')){
                checkit.addError(jQuery(textarea.eq(i)),flag,'p');
                textarea.eq(i).parent().css('height','105px');
            }else{
                checkit.addError(jQuery(textarea.eq(i)),flag,'span');
            }
        }else if(textarea.eq(i).next().hasClass('error')){
            flags=false
        }
    }
    var input = jQuery('#apply-info input');
    for(var i=0;i<input.length;i++){
        if(input.eq(i).val()==''){
            flags=false;
            var flag = '必填';
            if(input.eq(i).parent().hasClass('job-name')){
                checkit.addError(jQuery(input.eq(i)),flag,'p');
                input.eq(i).parent().css('height','105px');
            }else{
                /******************
                 * Author: Ara
                 * Date: 2017.8.25
                 * 设置判断，当表单名为contact的时候，在获取元素的子元素后面添加提示，否则直接在获取元素后面添加代码提示。
                 */
                if(input.eq(i).attr("name") != "contact"){
                    checkit.addError(jQuery(input.eq(i)),flag,'span');
                }else{
                    checkit.addError(jQuery(input.eq(i).next()),flag,'span');
                }
            }
        }else if(input.eq(i).next().hasClass('error')){
            flags=false
        }
    }
    return flags;
}
jQuery('#apply-info input[name="compname"]').blur(function(){
    if(jQuery.trim(jQuery(this).val())!=""){
        var flag=true;
        checkit.addError(jQuery(this),flag,'span');
    }else{
        var flag  = '必填';
        checkit.addError(jQuery(this),flag,'span');
    }
})

// 重置按钮功能实现
function clear_all(){
    jQuery('#apply-info input').each(function(){
        if(jQuery(this).attr('type')=='text'){
            jQuery(this).val('')
        }
    });
    jQuery('#apply-info textarea').val('');
}
jQuery('#apply-info .reset-btn').click(function(){
    jQuery('#alert-div').show();
    jQuery('#alert-div #imsure').click(function(){
        clear_all();
        jQuery('#alert-div').hide();

    });
    jQuery('#alert-div #no-cancel').click(function(){
        jQuery('#alert-div').hide();
    });
    jQuery('#alert-box h2 span').click(function(){
        jQuery('#alert-div').hide();
    })

});