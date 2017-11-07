var categories = [];
categories["startList"] = [];
var nLists = 3;

function fillSelect(currCat,currList) {
 var step = Number(currList.name.replace(/\D/g,""));
 for (i=step; i<nLists+1; i++) {
  document.forms['tripleplay']['List'+i].length = 1;
  document.forms['tripleplay']['List'+i].selectedIndex = 0;
 }
 var nCat = categories[currCat];
 for (each in nCat) {
  var nOption = document.createElement('option'); 
  var nData = document.createTextNode(nCat[each]); 
  nOption.setAttribute('value',nCat[each]); 
  nOption.appendChild(nData); 
  currList.appendChild(nOption); 
 } 
}

function getValue(L3, L2, L1) {
 alert("Your selection was:- \n" + L1 + "\n" + L2 + "\n" + L3);
}

function init() {
 fillSelect('startList',document.forms['tripleplay']['List1'])
}

navigator.appName == "Microsoft Internet Explorer" ? attachEvent('onload', init, false) : addEventListener('load', init, false);