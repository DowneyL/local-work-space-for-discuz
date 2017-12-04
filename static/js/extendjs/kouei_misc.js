window.onload = function(){
	var oActiveBtn = document.getElementById('hactive_search_btn');
	var oActiveInput = document.getElementById('hactive_search').getElementsByTagName('input');
	//17.7.21 update
	var ptnTime = /^\d{4}-(0?[1-9]|1[0-2])-((0?[1-9])|((1|2)[0-9])|30|31)$/; 
	// var ptnTime = /^\d{4}-(0?[1-9]|1[0-2])-((0?[1-9])|((1|2)[0-9])|30|31)\s(0?[0-9]|1[0-9]|2[0-4]):(0?[0-9]|[1-5][0-9]):(0?[0-9]|[1-5][0-9])$/;
	var dataTime = Array();
	oActiveBtn.onclick = function(){
		for(var i = 0; i < oActiveInput.length; i++){
			if(oActiveInput[i].value != '' && ptnTime.test(oActiveInput[i].value)){
			    dataTime[i] = oActiveInput[i].value;
			}else{
				alert("Please input the right time!");
				return;
			}
		}
		if(dataTime[0] != undefined && dataTime[1] != undefined){
			window.location.href = "/misc.php?mod=stat&op=actmem"+"&starttime="+dataTime[0]+"&finishtime="+dataTime[1];
		}
	}
	var uurl = window.location;
	 	uurl = uurl.toString();
	function GetNoPageUrl(url)
	{
	     var reg = new RegExp("(^|&)"+ "page" +"=([^&]*)(&|$)");
         var r = url.substr(1).match(reg);
         if(r!=null)return url.replace(r[0],''); return url; 
	}
	var myURL = GetNoPageUrl(uurl);



	function GetQueryString(name)
	{
	     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
	     var r = window.location.search.substr(1).match(reg);
	     if(r!=null)return  unescape(r[2]); return null;
	}
	var page = GetQueryString('page')?GetQueryString('page'):1;
		page = parseInt(page);

	var oPageA = document.getElementById('page_change').getElementsByTagName('a');
	var oFirstPage = document.getElementById('firstpage');
	var oPrevPage = document.getElementById('prevpage');
	var oNextPage = document.getElementById('nextpage');
	var oInputPage = document.getElementById('input_page');

	oInputPage.onblur = function(){
		page = parseInt(this.value);
		window.location.href = myURL+'&page='+page;
	}

	oFirstPage.onclick = function(){
		page = 1;
		window.location.href = myURL+'&page='+page;
	}

	oPrevPage.onclick = function(){
		if(page == 1){
			return;
		}
		page = page - 1;
		window.location.href = myURL+'&page='+page;
	}

	oNextPage.onclick = function(){
		page = page + 1;
		window.location.href = myURL+'&page='+page;
	}

	for(var i = 2; i < oPageA.length-1; i++)
	{	
		var page_inner = oPageA[i].innerHTML;
		if(page != null)
		{
			if(page_inner == page){
				for(var p in oPageA) oPageA[p].className = '';
				oPageA[i].className = 'select_page_style';
			}
		}
		oPageA[i].onclick = function()
		{
			for(var p in oPageA) oPageA[p].className = '';
			this.className = "select_page_style";
			page = parseInt(this.innerHTML);
			// alert(myURL+'&page='+page);
			window.location.href = myURL+'&page='+page ;
		}
		oPageA[2].innerHTML = parseInt(page);
		oPageA[3].innerHTML = parseInt(page)+1;
		oPageA[4].innerHTML = parseInt(page)+2;
	}
}

