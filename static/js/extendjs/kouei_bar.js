var sidebar_div = document.getElementById('sidebar').getElementsByTagName('div');
onFocusTime = function(id1,id2){
	if($(id1)){
		$(id1).onmouseover = function(){
			if(id1 == "kouei_weixin"){
				$('kouei_qr').style.display ='block'; 
			}

			$(id2).src = IMGDIR+"/"+id2+"01.png"
			this.style.background ="#fff";
		}
	}
	if($(id1)){
		$(id1).onmouseout = function(){
			if(id1 == "kouei_weixin"){
				$('kouei_qr').style.display ='none'; 
			}

			$(id2).src = IMGDIR+"/"+id2+ "02.png"
			this.style.background ="#5CACEE";
		}
	}
}
for(var i=0; i<sidebar_div.length; i++ ){
	var divId = sidebar_div[i].id;
	var imgId = sidebar_div[i].id + "_img";
	onFocusTime(divId,imgId);
}


onSpanTime = function(id){
	if($(id)){
		$(id).onmouseover = function(){
			$(id).style.background = "#fff";
		}
	}
	if($(id)){
		$(id).onmouseout = function(){
			$(id).style.background = "#5CACEE";
		}
	}
}
for(var j=0; j<3; j++){
	var spanId = "span_" + j;
	onSpanTime(spanId);
}