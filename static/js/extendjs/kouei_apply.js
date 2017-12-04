function $(id) {
    return !id ? null : document.getElementById(id);
}

function $C(classname, ele, tag) {
    var returns = [];
    ele = ele || document;
    tag = tag || '*';
    if(ele.getElementsByClassName) {
        var eles = ele.getElementsByClassName(classname);
        if(tag != '*') {
            for (var i = 0, L = eles.length; i < L; i++) {
                if (eles[i].tagName.toLowerCase() == tag.toLowerCase()) {
                        returns.push(eles[i]);
                }
            }
        } else {
            returns = eles;
        }
    }else {
        eles = ele.getElementsByTagName(tag);
        var pattern = new RegExp("(^|\\s)"+classname+"(\\s|$)");
        for (i = 0, L = eles.length; i < L; i++) {
                if (pattern.test(eles[i].className)) {
                        returns.push(eles[i]);
                }
        }
    }
    return returns;
}

var oWin = document.getElementById("kouei_win");
var oLay = document.getElementById("kouei_overlay");    
var oBtn = document.getElementById("kouei_button");
var oClose = document.getElementById("kouei_close");
var oBody = document.body;
var oNv = document.getElementById('nv');
var oBodyWidth = oBody.clientWidth + 'px';
if(oBtn){
    oBtn.onclick = function ()
    {
        var oWinTop = document.body.scrollTop;
        var oLayTop = document.body.scrollTop+'px';
        oLay.style.top = oLayTop;
        oWin.style['margin-top'] = (oWinTop - 404) + 'px';
        oBody.style['height'] = '100%';
        oBody.style['width'] = oBodyWidth;
        oBody.style['overflow'] = 'hidden';
        oNv.style['z-index'] = '';
        oLay.style.display = "block";
        oWin.style.display = "block";   
    };
}

if(oClose){
    oClose.onclick = function ()
    {
        oBody.style['height'] = '';
        oBody.style['overflow'] = '';
        oNv.style['z-index'] = '199';
        oLay.style.display = "none";
        oWin.style.display = "none";
    }
}


/*表单只能输入数字*/
function applyKeyUp(elem, renum)     
{
    elem.onkeyup = function()
    {
        this.value = this.value.replace(renum,"");
    }
}

/*验证QQ号、电话号是否合法*/
function applyCheckNum(elem, myre, sucstr, errstr, otherstr){
    elem.onblur = function()
    {
        var noticeTd = this.parentNode.nextElementSibling;
        var noticeTdStyle = noticeTd.style;
        if(this.value && myre.test(parseInt(this.value))){
            noticeTd.innerHTML = sucstr;
            noticeTdStyle.color = '#4caf50';
        }else if(!this.value){
            noticeTd.innerHTML = otherstr;
            noticeTdStyle.color = '#cf4646';
        }else{
            noticeTd.innerHTML = errstr;
            noticeTdStyle.color = '#cf4646';
        }
    }
}

/*其他表单验证*/
function applyCheckStr(elem){
    elem.onblur = function()
    {
        var oNotice =  this.parentNode.nextElementSibling;
        var oNoticeStyle = oNotice.style;
        if(this.value){
            oNotice.innerHTML = oNoticeStr[1];
            oNoticeStyle.color = '#4caf50'; 
        }else{
            oNotice.innerHTML = oNoticeStr[2];
            oNoticeStyle.color = '#cf4646'; 
        }
    }
}

/*所有表单一起验证*/
// function applyCheckInput(elem){
//  for(var i = 0; i < oInput.length; i++){
//      if(!elem[i].value){
//          elem[i].parentNode.nextElementSibling.innerHTML = oNoticeStr[2];
//          elem[i].parentNode.nextElementSibling.style.color = '#cf4646'; 
//      }
//  }
// }

/*提示信息整合*/
var oNoticeStr = [
    '填写有误，请检查您的填写内容！',
    '填写成功！',
    '您没有填写任何内容！'
];

/*获取表单所有input元素并过滤敏感字符*/
var oInput = document.getElementById('kouei_win').getElementsByTagName('input');
var reText = /[<|>|'|"|\(|\)]+/;
for(var i = 1; i < oInput.length; i++)
{   
    applyKeyUp(oInput[i], reText);

}

/*获取所有textarea元素并过滤敏感字符*/
var oTextArea = document.getElementById('kouei_win').getElementsByTagName('textarea');
for(var x = 0; x < oTextArea.length; x++)
{
    applyKeyUp(oTextArea[x], reText);
}

/*信息表单验证*/

// update 
var oInputStr = $C('input_text_str', $('kouei_win'));
// var oInputStr = document.getElementById('kouei_win').getElementsByClassName('input_text_str');
for(var j = 0; j < oInputStr.length; j++){
    applyCheckStr(oInputStr[j]);
}

/*单独给年龄添加验证*/
var reNum = /[^(\d)]+/;
var oApplyAge = document.getElementById('kouei_apply_age');
applyKeyUp(oApplyAge,reNum);
oApplyAge.onblur = function ()
{
    var oAgeNotice = this.parentNode.nextElementSibling;
    var ageNoticeStyle = oAgeNotice.style;
    // alert(typeof parseInt(this.value));
    if(this.value && parseInt(this.value) >= 1 && parseInt(this.value) <= 100){
        oAgeNotice.innerHTML = oNoticeStr[1];
        ageNoticeStyle.color = '#4caf50';
    }else if(!this.value){
        oAgeNotice.innerHTML = oNoticeStr[2];
        ageNoticeStyle.color = '#cf4646';
    }else{
        oAgeNotice.innerHTML = oNoticeStr[0];
        ageNoticeStyle.color = '#cf4646';
    }
}

/*电话号验证*/
var oApplyPhoneNum = document.getElementById('kouei_apply_phonenum');
var rePhone = /^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57]|17[0-9])[0-9]{8}$/;
applyKeyUp(oApplyPhoneNum, reNum);
applyCheckNum(oApplyPhoneNum, rePhone, oNoticeStr[1], oNoticeStr[0], oNoticeStr[2]);

/*QQ号验证*/
var oApplyQQNum = document.getElementById('kouei_apply_QQ');
var reQQ = /^[1-9][0-9]{4,9}$/;
applyKeyUp(oApplyQQNum, reNum);
applyCheckNum(oApplyQQNum, reQQ, oNoticeStr[1], oNoticeStr[0], oNoticeStr[2]);


// update
var oNoticeTd = $C('input_notice');
// var oNoticeTd = document.getElementsByClassName('input_notice');
function checkApplySubmit()
{
    for(var x = 0; x < oInput.length; x++){
        if(!oInput[x].value){
            oInput[x].parentNode.nextElementSibling.innerHTML = oNoticeStr[2];
            oInput[x].parentNode.nextElementSibling.style.color = '#cf4646'; 
        }
    }
    for(var y = 0; y < oTextArea.length; y++){
        if(!oTextArea[y].value){
            oTextArea[y].parentNode.nextElementSibling.innerHTML = oNoticeStr[2];
            oTextArea[y].parentNode.nextElementSibling.style.color = '#cf4646'; 
        }
    }
    var ifTrue=0;
    for(var i = 2; i < oNoticeTd.length; i++)
    {
        if(oNoticeTd[i].style.color == 'rgb(76, 175, 80)'){
            ifTrue=1;
        }else{
            return false;
        }
    }
    if(ifTrue==1){
        return true;
    }
}

