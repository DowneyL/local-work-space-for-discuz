/**
 * Created by Administrator on 2017/6/12.
 */
var oCal_2 = new Calendar({
    id: "#J_Cal_2",
    isSelect: !0
});

jQuery('#apply-info input[name="barthdate"]').click(function(ev){
    ev.stopPropagation();
    jQuery('#J_Cal_2').css('display' , 'block');

});
oCal_2.on("dateClick", function(obj) {
    jQuery('#apply-info input[name="barthdate"]').eq(0).val(obj["data-date"]);
    jQuery('#apply-info input[name="barthdate"]').eq(0).blur();
    jQuery('#J_Cal_2').css('display' , 'none');
});
//��֤
var input = jQuery('#apply-info input');
// �绰��֤
jQuery('#phone').blur(function(){
    var istrue = checkit.checkPhone(jQuery(this));
    checkit.addError(jQuery(this),istrue);
});
//������֤
jQuery('#ema').blur(function(){
    var istrue = checkit.checkEmail(jQuery(this));
    checkit.addError(jQuery(this),istrue);
});
//qq��֤
jQuery('#qq').blur(function(){
    var istrue = checkit.checkQQ(jQuery(this));
    checkit.addError(jQuery(this),istrue);
});
// ���鵽��ʱ��
jQuery('#apply-info input[name="dgsj"]').keyup(function(){
    if(isNaN(jQuery(this).val())){
        jQuery(this).val('');
        
    }
});
jQuery('#apply-info input[name="dgsj"]').blur(function(){
    if(jQuery.trim(jQuery(this).val())==""){
        checkit.addError(jQuery(this),'����');
    }else{   
        var istrue = checkit.checknum(jQuery(this));
        checkit.addError(jQuery(this),istrue);
    }
});
//Ѱ������
function getIndex(obj,current){
    var i=0;
    for(i in obj){
        //�ҵ���ǰ��ʾ��div������
        if(obj.eq(i).hasClass(current)){
            index=i;
            return i;
        }
    }
}
//���·�ҳ
jQuery('#apply-info .prev-btn').click(function(){//��һҳ
//				alert(index+'  jxhj');
    jQuery(this).next().css('display','');
    var divs = jQuery('#apply-info>form>div');
    var index = getIndex(divs,'open')
    if(index == 1){
        jQuery(this).css('display','none');
    }
    divs.eq(index).attr('class','apply-content close');
    divs.eq(--index).attr('class','apply-content open');
});
jQuery('#apply-info .next-btn').click(function(){//��һҳ
    var divs = jQuery('#apply-info>form>div');
    var index = getIndex(divs,'open');
    //�����Ƿ�Ϊ��
    var flag1 = checkNull();
    var flag2 = true;
    var inputs = jQuery('#apply-info div.open input');
    for(var i=0;i<inputs.length;i++){
        if(inputs.eq(i).next().length>0){
            if(inputs.eq(i).next().hasClass('error')){//���ĳ��input������������Ͳ���������һҳ
                flag2=false;
            }

        }
    }
    if(flag1 && flag2){
        jQuery(this).prev().css('display','');
        if(index == divs.length-2){
            jQuery(this).css('display','none');
            var textareas = jQuery('#apply-info textarea').eq(0);
            /*
            textareas.keyup(function(){
                var isfalse;
        if(!jQuery(this).val()){
            isfalse = '�������';
        }else{
            isfalse = jQuery(this).val().length<20 ?'����С��20���ַ�':true;
            if(isfalse==true){
                isfalse = checkit.checkMingan(jQuery(this));
            }
            
        }
            });
        checkit.addError(jQuery(this),isfalse);
        */
        }else{
            jQuery(this).css('display','');
        }
        divs.eq(index).attr('class','apply-content close');
        divs.eq(++index).attr('class','apply-content open');
    }
});
//��������
jQuery('#apply-info .apply-place>h3>span').click(function(){
    jQuery(this).parent().parent().next().css('display', 'block');
    addtr();
});
//��ӹ�������
function addtr(){
    var summ = jQuery('#apply-info tbody>tr').length;
    summ++;
    var clsname = ['compname','zhiwei','workingtime','salary'];
    jQuery('#apply-info tbody').append("<tr><td class='compname'><i class='line'></i><input type='text' name='infos["+summ+"][compname]' id='' value=''></td><td class='zhiwei'><input type='text' name='infos["+summ+"][zhiwei]' id='' value=''> </td><td class='workingtime'> <b class='item' style='display:inline-block'><input type='text' name='infos["+summ+"][start]' value='' /> </b> <i>��</i> <b class='item' style='display:inline-block'><input type='text' name='infos["+summ+"][end]' value=''/></b></td><td class='salary'><input type='text' class='small-100' name='infos["+summ+"][salary]' id='' value='' ></td></tr>");
    /*if(summ==1){
        jQuery('tbody>tr:first-of-type i.line').css('background','none');
    }*/
}
jQuery('#apply-info tfoot').delegate('.addt','click',function(){
    addtr();
})
jQuery('#apply-info #tbodys').delegate('.salary input','keyup',function(){
    var istrue = checkit.checknum(jQuery(this));
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
                jQuery(this).next().addClass('okc').removeClass('error');
            }

        }
})
// �Ƴ���������
jQuery('#apply-info #tbodys').delegate('i.line','click',function(ev){
    if(jQuery('#tbodys>tr').length<=1){
        jQuery('#apply-info .workjl').css('display','none');
    }
        jQuery(this).parent().parent().remove();
});
// ��֤����
jQuery('#apply-info input[name="username"]').blur(function(){
    var istrue;
    if(jQuery(this).val()==''){
        istrue='����';
    }else if(jQuery(this).val().length>6){
        istrue = '����6���ַ�';
    }else{
        istrue=true;
    }
    checkit.addError(jQuery(this),istrue)
});
// ��֤���������е�����
jQuery('#apply-info #tbodys').delegate('input','keyup',function(){
    // 1:��˾���Ʋ��ܶ���40�ַ�
    var istrue;
    if(jQuery(this).val()==''){
        istrue='����';
        if(jQuery(this).next().length<1){
                jQuery(this).parent().append('<span class="error">'+istrue+'</span>');
            }else{
                jQuery(this).next().html(istrue);
                jQuery(this).next().attr('class','error');
            }
    }else if(jQuery(this).val().length>40){
            jQuery(this).val('');
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
                jQuery(this).next().addClass('okc').removeClass('error');
            }
    }
});
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