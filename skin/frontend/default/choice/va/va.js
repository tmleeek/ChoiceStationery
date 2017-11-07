// VideoAdd v1.9.5
// 17.07.2010
// Copyright mywebpresenters.com

var mydiv; var mydiv2; var mycontent;
var vwidth ; var vheight; var zztop;
swfobject.registerObject("swfContainer", "9.0.0");
var vaxml;
var vadebug = 0;
var vawait = 5000;
var vaplay = 1; //if 1, movie will play automatically on start - if 0, it will not

function setCookie( name, value, expires, path, domain, secure )
{
  var today = new Date();
  today.setTime( today.getTime() );
  if ( expires )
    expires = expires * 1000 * 60 * 60 * 24;
  var expires_date = new Date( today.getTime() + (expires) );
  document.cookie = name+"="+escape( value ) +
    ( ( expires ) ? ";expires="+expires_date.toGMTString() : "" ) +
    ( ( path ) ? ";path=" + path : "" ) +
    ( ( domain ) ? ";domain=" + domain : "" ) +
    ( ( secure ) ? ";secure" : "" );
}
 
function getCookie( name )
{
  var start = document.cookie.indexOf( name + "=" );
  var len = start + name.length + 1;
  if ( ( !start ) && ( name != document.cookie.substring( 0, name.length ) ) )
    return null;
  if ( start == -1 )
    return null;
  var end = document.cookie.indexOf( ";", len );
  if ( end == -1 )
    end = document.cookie.length;
  return unescape( document.cookie.substring( len, end ) );
}
 
function deleteCookie( name, path, domain )
{
  var today = new Date();
  today.setTime( today.getTime() );
  var expires_date = new Date( today.getTime() - 100 );
  setCookie(name,"",expires_date,path,domain,'');
}

var xmlDoc;

var file; var frequency;
var where; var xpos; var ypos; var vwidth; var vheight;
var url; var target;var color1; var color2;

function loadxml()
{
// for IE 
if (window.ActiveXObject)
{
  xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
	xmlDoc.async=false;
	xmlDoc.load(vaxml);
	getsettings();
		
}
// for Mozilla, Firefox, Opera, 
else if (document.implementation && document.implementation.createDocument)
{	
	var xmlhttp = new window.XMLHttpRequest();
	xmlhttp.open("GET",vaxml,false);
	xmlhttp.send(null);
  xmlDoc = xmlhttp.responseXML.documentElement;
	getsettings();
}
else
{
	alert('Error: va.js, row 77 : Your browser does not support AJAX.');
}
}

function getAttribute(tagname, attributename){
	t = xmlDoc.getElementsByTagName(tagname);
	a = t[0].getAttribute(attributename);
	return a;

}

function getsettings()
{
	video = xmlDoc.getElementsByTagName('video');
	file = video[0].getAttribute('file');
	frequency = video[0].getAttribute('frequency');
	color1 = video[0].getAttribute('color1');
	color2 = video[0].getAttribute('color2');

	position = xmlDoc.getElementsByTagName('position');
	where = position[0].getAttribute('where');
	xpos = position[0].getAttribute('xpos')+"px";
	ypos = position[0].getAttribute('ypos')+"px";
	vwidth = position[0].getAttribute('width');
	vheight = position[0].getAttribute('height');
	
	link = xmlDoc.getElementsByTagName('link');
	url = link[0].getAttribute('url');
	target = link[0].getAttribute('target');

}

function VerifyDate(visitdate,frequencyhours)
{
 	if (visitdate!=null)
	{	
	today = new Date( );          // set today's date
	frequencymilliseconds = frequencyhours * 60 * 60 * 1000;
  diff = today.getTime() - visitdate;
 
  if (frequencymilliseconds<diff)
  {
		return true;
  }else{
		return false;
  }
 
  }else{
    return true;
  }
 
}


function LoadVideo()
{
	today = new Date( );
	loadxml();

	if ((VerifyDate(getCookie('lastvisit'+file),frequency)) || (vadebug==1))
	{
	mycontent="<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+vwidth+"\" height=\""+vheight+"\" id=\"swfContainer\"><param name=\"movie\" value=\"va/player.swf\" /><param name=\"wmode\" value=\"transparent\" /><param name=\"allowscriptaccess\" value=\"always\" /><param name=\"flashvars\" value=\"flvpath="+file+"&amp;golink="+url+"&amp;color1="+color1+"&amp;color2="+color2+"&amp;p="+vaplay+"\" /><!--[if !IE]>--><object type=\"application/x-shockwave-flash\" data=\"va/player.swf\" width=\""+vwidth+"\" height=\""+vheight+"\"><param name=\"wmode\" value=\"transparent\" /><param name=\"allowscriptaccess\" value=\"always\" /><param name=\"flashvars\" value=\"flvpath="+file+"&amp;golink="+url+"&amp;color1="+color1+"&amp;color2="+color2+"&amp;p="+vaplay+"\" /><!--<![endif]--><a href=\"http://www.adobe.com/go/getflashplayer\"><img src=\"http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif\" alt=\"Get Adobe Flash player\" /></a><!--[if !IE]>--></object><!--<![endif]--></object>";


		switch(where)
		{
		case "top-left":
  		mydiv = document.getElementById("vaheader");
			mydiv2 = document.getElementById("vaheaderwrap");
			mydiv.style.float="left";
  		mydiv.style.textAlign="left";
  		if (xpos.search("-")<0){
 					 mydiv2.style.left=xpos;
			}else{
					 mydiv2.style.left="0px";
			}
			if (ypos.search("-")>=0){
						ypos = ypos.replace("-","");
						mydiv2.style.top=ypos;
			}else{
						mydiv2.style.top="0px";
			}		
			break;
		case "top-center":
  		    mydiv = document.getElementById("vaheader");
			mydiv2 = document.getElementById("vaheaderwrap");

  			mydiv.style.float="center";
  		    mydiv.style.textAlign="center";
  		    var right = (getWindowWidth()-vwidth)/2 - parseInt(xpos);
  		    var top = 0-parseInt(ypos);
  		    
  		    mydiv2.style.right = right.toString() + "px";
  		    mydiv2.style.top = top.toString() + "px";
  		    
  		    break;
		case "top-right":
  		mydiv = document.getElementById("vaheader");
			mydiv2 = document.getElementById("vaheaderwrap");
  		mydiv.style.float="right";
  		mydiv.style.textAlign="right";
  		if (xpos.search("-")>=0){
 					 xpos = xpos.replace("-","");
					 mydiv2.style.right=xpos;
			}else{
					 mydiv2.style.right="0px";
			}
			if (ypos.search("-")>=0){
						ypos = ypos.replace("-","");
						mydiv2.style.top=ypos;
			}else{
						mydiv2.style.top="0px";
			}		
			break;
		case "bottom-left":
  		mydiv = document.getElementById("vafooter");
			mydiv2 = document.getElementById("vafooterwrap");
  		mydiv.style.float="left";
  		mydiv.style.textAlign="left";
			if (xpos.search("-")<0){
 					 mydiv2.style.left=xpos;
			}else{
					 mydiv2.style.left="0px";
			}
			if (ypos.search("-")<0){
						mydiv2.style.bottom=ypos;
			}else{
						mydiv2.style.bottom="0px";
			}		
	 		break;
		case "bottom-center":
 			mydiv = document.getElementById("vafooter");
			mydiv2 = document.getElementById("vafooterwrap");

  			
			mydiv.style.float="center";
  		    mydiv.style.textAlign="center";
   		    var right = (getWindowWidth()-vwidth)/2 - parseInt(xpos);
			mydiv2.style.right = right.toString() + "px";
  		    mydiv2.style.bottom=ypos;
  		break;
		case "bottom-right":
  		mydiv = document.getElementById("vafooter");
 			mydiv2 = document.getElementById("vafooterwrap");
  		mydiv.style.float="right";
  		mydiv.style.textAlign="right";  
			if (xpos.search("-")>=0){
 					 xpos = xpos.replace("-","");
					 mydiv2.style.right=xpos;
			}else{
					 mydiv2.style.right="0px";
			}
				if (ypos.search("-")<0){
						ypos = ypos.replace("-","");
						mydiv2.style.bottom=ypos;
			}else{
						mydiv2.style.top="0px";
			}		
			break;	
		default:
  		mydiv = document.getElementById("vafooter");
  		mydiv.style.float="left";
  		mydiv.style.textAlign="left";
  		break;
	}

  mydiv.innerHTML = mycontent;
	mydiv.style.height = vheight+"px";
	mydiv2.style.height = vheight+"px";
	mydiv2.style.width = vwidth+"px";
	   
	setCookie('lastvisit'+file,today.getTime(),1);
}

}
	
//swf functions


function closeadd()
{	
	setTimeout('closeadd2()', vawait);
}

function closeadd2()
{	
	if (window.ActiveXObject)
	{
		mydiv.style.top = "-2600px";
		mydiv2.style.top = "-2600px";
	}else{
		mydiv.style.display = "none";
		mydiv2.style.display = "none";
	}
}


function callFlash() {
			window.scrollBy(0,1);
			window.scrollBy(0,-1);
			if (window.ActiveXObject)
			{
				mydiv2.style.top = "";
			}else{
  				mydiv.style.display = "block";
			}
			
			mydiv2.style.top = "";
			var swf = swfobject.getObjectById("swfContainer");
			if (swf){
				swf.reloadFLV();
			}else{
				mydiv.style.display = "block";
				mydiv2.style.display = "block";

			}
}
	
function getWindowWidth(){
 if (parseInt(navigator.appVersion)>3) {
 if (navigator.appName=="Netscape") {
  winW = window.innerWidth-16;
  winH = window.innerHeight-16;
 }
 if (navigator.appName.indexOf("Microsoft")!=-1) {
  winW = document.body.offsetWidth-20;
  winH = document.body.offsetHeight-20;
 }
 }
 return winW; 
}  
 