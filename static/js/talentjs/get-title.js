var oTitle = document.getElementById('subject');
var oKSelect = document.getElementById('typeid');
var oKOption = oKSelect.getElementsByTagName('option')[0];
var oKA = document.getElementById('typeid_ctrl');
if(document.getElementById('compname')){
	var oTitleStr = document.getElementById('compname').value;
	oTitle.value = oTitleStr + '’–∆∏∆Ù ¬';
	oKSelect.attributes["selecti"].value = 1;
	oKOption.value = 15;
	oKA.innerHTML = '’–∆∏';
}
if(document.getElementById('username') && document.getElementById('qwposition')){
	var oTitleStr = document.getElementById('username').value + "    " + document.getElementById('qwposition').value;
	oTitle.value = oTitleStr;
	oKSelect.attributes["selecti"].value = 2;
	oKOption.value = 16;
	oKA.innerHTML = '«Û÷∞';
}												