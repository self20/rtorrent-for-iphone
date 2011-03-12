<?php 
/*
 Main UI HTML for rtorrent iphone interface
 
 (c) 2010 Kramerican Industries (www.kramerican.dk) AHJ
*/


 include("config.inc.php");

?>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>rtorrent iphone</title>
        <style type="text/css" media="screen">@import "jqtouch/jqtouch.min.css";</style>
        <style type="text/css" media="screen">@import "themes/jqt/theme.css";</style>
        <script src="jqtouch/jquery.1.3.2.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="jqtouch/jqtouch.min.js" type="application/x-javascript" charset="utf-8"></script>
        <script type="text/javascript" charset="utf-8">
            var jQT = new $.jQTouch({
                icon: 'themes/rt_iphone_icon.png',
                addGlossToIcon: false,
                startupScreen: 'themes/rt_iphone_startup.png',
                statusBar: 'black',
                preloadImages: [
                    ]
            });
			
			
function encode_utf8( s ){  return unescape( encodeURIComponent( s ) );}

function URLEncode(c){var o='';var x=0;c=c.toString();var r=/(^[a-zA-Z0-9_.]*)/;
  c = encode_utf8(c);
  //return c;
  //alert(c);
  while(x<c.length){var m=r.exec(c.substr(x));
    if(m!=null && m.length>1 && m[1]!=''){o+=m[1];x+=m[1].length;
    }else{if(c[x]==' ')o+='+';else{var d=c.charCodeAt(x);var h=d.toString(16);
    o+='%'+(h.length<2?'0':'')+h.toUpperCase();}x++;}}return o;}
	
function URLDecode(s){var o=s;var binVal,t;var r=/(%[^%]{2})/;
  while((m=r.exec(o))!=null && m.length>1 && m[1]!=''){b=parseInt(m[1].substr(1),16);
  t=String.fromCharCode(b);o=o.replace(m[1],t);}return o;}			


function refreshpage() {
 location.reload(true);
}  
  
function addtorrent() {
//Maybe we should use jQuery form plugin instead of all this madness
	var torrent_url = URLEncode($('#torrent_url').val()); 
	
	//Now do the ajax call to our torrent add function
	$('#addtorrentdiv').html("<center>Adding torrent please wait...</center>");
	  var qurl = "ajax.rtorrent.php?action=add_torrent&torrent_url="+torrent_url+"&rand="+Math.floor(Math.random()*1001); //Cache busting needed here??
	   
	  $.get(qurl, function(data) {
	  //make sure we are getting data - TODO: Much better return checking needed. I propose returning a JSON object with status codes
	  var re = new RegExp('ERROR'); //Check for error code
	  var m = re.exec(data); //run regexp against data
	  if (m == null) { //no match found  - we are good
	   alert("Torrent added!");
	   refreshpage();
	    } else {
	  	   alert("riPhone encountered an error:\n "+data);
		   refreshpage();
	  } //end else 
	  }); 
} //end function addtorrent

	
function refreshtorrents() {
 $('#torrentlist').html("<li><center>Refreshing torrents list...</center></li>");
 var qurl = "rtorrent_interface.php?rand="+Math.floor(Math.random()*1001);
 $('#torrentlist').load(qurl, function() {
  //alert('Load was performed.');
});
} //end function refreshtorrents
			

var userAgent = navigator.userAgent.toLowerCase();
var isiPhone = (userAgent.indexOf('iphone') != -1) ? true : false;
if(userAgent.indexOf('ipod') != -1) isiPhone = false; // turn off taps for iPod Touches
clickEvent = isiPhone ? 'tap' : 'click';
	
//on document load stuff	
$(function(){	

 $('#addtorrentlink').bind(clickEvent, function() {
    addtorrent();
 });
	
 $('#refreshlink').bind(clickEvent, function() {
    refreshtorrents();
  });	

<?php
 if ($config['autorefresh'] == true) {	
 ?>
  var t=setInterval("refreshtorrents()",<?php echo $config['refreshinterval'];?>);
 <?php
 } //end if autorefresh
?>
			
}); //end document load
			
			
        </script>
		
</head>
<body>

<div id="main">
    <div class="toolbar">
        <h1>rtorrent</h1>
		<a class="button slideup" href="#" id="refreshlink">Refresh</a>
    </div>
	

    <h4>Add a torrent</h4>
            <form>
                <ul class="edit rounded">
                <li><input type="text" name="name"  id="torrent_url" placeholder="Paste torrent url here" /></li>
				<li ><div id="addtorrentdiv"><center><a href="#" id="addtorrentlink">Add torrent</a></center></div></li>	
			   </ul>
			</form>
	
    <h4>Active torrents</h4>
    <ul class="metal" id="torrentlist">
    <?php include("rtorrent_interface.php");  ?>
    </ul>
</div>


</body>
</html>		