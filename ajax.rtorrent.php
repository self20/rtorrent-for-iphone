<?php 
/*
 rtorrent  "interface" for iphone front-end
 This script contains the functions needed to do the following:
 
 1) Fetch a .torrent from a URL and put it in your rtorrent watch folder
 2) Return rtorrent status information on active torrents 
 
 Kramerican Industries (www.kramerican.dk) AHJ
*/
foreach($_GET AS $key => $value) {
    ${$key} = addslashes($value);
} 

include("config.inc.php");

if (!$action) { die("ERROR_001: No Action specified"); }

if (function_exists($action)) {
 $action();
} else {
 die("ERROR_002: Method Not Found: ".$action);
}

/*
Fetch a .torrent and put it in the watch folder
*/
function add_torrent() {
 foreach($_GET AS $key => $value) {
	global ${$key};
 }
 global $config;
 
 if ($config['check_for_watchfolder'])
 if (!is_dir($config['watchfolder'])) {
  echo "ERROR - could not find watch folder";
  exit;
 } 
 
 $cmd = $config['elevaterights'] . " runcommand " . $config['addscript'] . " " . escapeshellarg($torrent_url);
  echo $cmd."\n";
  echo exec($cmd);
 
} //end function fetchtorrent



?>