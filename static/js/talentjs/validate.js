//�жϺ���
var checkObj = function(){};
checkObj.prototype = {
    //�д����ƣ����Դ�����ص����дʻ�
    checkMingan:function(obj){
        //���д���֤
        if(obj.val().indexOf("'")>-1||obj.val().indexOf('"')>-1 || obj.val().indexOf('<')>-1 || obj.val().indexOf('>')>-1){
            return '�����дʻ�';
        }else{
            return true;
        }
    },
    checkPhone:function(objPhone){
        // �绰������֤
        var value = objPhone.val();
        var regTel1 = /^1[34578]\d{9}$/.test(value);//�����ŵĹ̶��绰
        var regTel2 = /^(\d{3,4}-)?\d{6,8}(-\d{3})?/.test(value);//�������ŵĹ̶��绰(\d{3,4}-)?\d{6,8}(-\d{3})?
        if (value != ""){
            if (!regTel1 && !regTel2){ //&& !regTel2
                return "�绰������������";
            }else{
                return true;
            }
        }else {
            return "������绰���룡";
        }
    },
    checkEmail:function(obj){
        // ������֤
        var myemail = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
        if(!myemail.test(obj.val())){
            return '��������Ч�����䣡';
        }else{
            return true;
        }
    },
    checkQQ:function(objqq){
        //QQ��֤
        var myqq = (/^[1-9][0-9]{4,9}$/);
        if(!myqq.test(objqq.val())){
            return '��������ȷ��QQ��';
        }else{
            return true;
        }
    },
    checknum:function(Objnum,max,min){//����ֵ�����ֵ�Լ���Сֵ
        //�Ƿ���������֤
        if(isNaN(Objnum.val())){
            Objnum.val('');
            return '����';
        }else{//��������֣���֤���Ĵ�С
            if(Objnum.val()>max){
                var msg = "����ֵ��С�ڣ�"+max;
                return msg;
            }else if(Objnum.val()<min){
                var msg = '����ֵ����ڣ�'+min;

            }else{
                return true;
            }
            
        }
    },
    setInput:function(){
        //�����µ�input��ǩ
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
//�ж������Ƿ��п�
function isnull (){
    var inputs = jQuery('#apply-info input[type="text"]');
    for(var i=0;i<inputs.length;i++){
        inputs.eq(i).blur(function(){
            //alert(!jQuery.trim(jQuery(this).val()))
            var ismin;
            if(!jQuery.trim(jQuery(this).val())){//���Ϊ��
                ismin = '����';
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
            if(!jQuery.trim(jQuery(this).val())){//���Ϊ��
                istrue = '����';
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
jQuery('#apply-info #tbodys').delegate('input','click',function(){//�������������֤
    isnull2();
});


//�ύʱ�ж��Ƿ��б�Ϊ��
function checkNull(){
    var flags = true;
    var objs = jQuery('#apply-info div.open input[type="text"]');
    //��������textera����Ϊ�ղ��ж�
    /*
    var textarea = jQuery('#apply-info div.open textarea').length>0?jQuery('div.open textarea'):null;
    //filefujianChange();
    if(textarea &&(!textarea.val() || textarea.next().hasClass('error')) ){
         //alert(textarea);
         var isfalse;
         if(!textarea.val()){
            isfalse = '�������';
         }
        if(textarea.next().length<1){
            checkit.addError(textarea,isfalse);
        }
        flags = false;
    }
    */
    if(jQuery('#apply-info input[type="file"]').prev('span').hasClass('error')){flags=false;}
    for(var i=0;i<objs.length; i++){//for-in�������null����undefined���ʱ�򣬻��׳�����
        var nextsb = objs.eq(i).next().length>0?objs.eq(i).next():null;
        if(!jQuery.trim(objs.eq(i).val())){//������������Ϊ��
            flags = false;
            if(!nextsb){
                flags=false;
                var isfalse = '����';
                checkit.addError(objs.eq(i),isfalse);
            }else{
                nextsb.html('����');
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
//textarea�������֤
/*
var textarea = jQuery('#apply-info textarea');
textarea.keyup(
    function(){
        var isfalse;
        if(!jQuery(this).val()){
            isfalse = '�������';
        }else{
            isfalse = jQuery(this).val().length<20 ?'����С��20���ַ�':true;
            if(isfalse==true){
                isfalse = checkit.checkMingan(jQuery(this));
            }
            
        }
        checkit.addError(jQuery(this),isfalse);
    }
)
*/