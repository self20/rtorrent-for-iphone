<?php 
/*
 Config include for rtorrent iPhone interface.
 Kramerica Industries (www.kramerican.dk)
  
*/
global $config;

/*
Server interface settings. This is where you configure how rtorrent for iPhone communicates with rtorrent
These settings are the same as for rTWi - here is the doc on that as (shamelessly stolen) from the rTWi site (http://rtwi.jmk.hu/wiki/InstallationGuide)
(This does not mean you have to install rTWi - it just means I've grabbed some of their code for this project)
==================
1) Set the address rTWi can connect to the user's rTorrent (there are 3 ways to do it) 
 2) if you're using a unix socket enter address = "unix://~/torrent/.socket/rpc.socket" 
 3) if you're using scgi_port enter address = "123.123.123.123:12345" (address = ":12345" works for localhost) 
 4) if you're using an SCGI gateway enter address = "http://example.com/RPC2" (also works when Basic HTTP Authentication is on for the URL) 
 5) enter pass = "1234567890abcdef1234567890abcdef12345678" where the right side is the sha1 hash of the choosen password for the user (you can generate the sha1 hash  online) 
==================
I have only tested the scgi_port option, and that doesn't require a username or password. I have no clue whether my bastardized code will work with the other connection types.
AFAIK then server_user should be set to your *nix username

Feedback on these other connection types would be much appreciated
*/
$config['server_interface'] = "127.0.0.1:5000"; //This was default for my rtorrent installation on my Debian Lenny box
$config['server_user'] = "";
$config['server_pass'] = "";

/*
Here you configure where your rtorrent watch folder is located. When you add a torrent through the iPhone interface, it downloads the .torrent file and places it in this folder.
In this way rtorrent picks up on the torrent and starts downloading it.
*/
$config['watchfolder'] = "/mnt/usbdrive1/downloads/watch";

/*
Do you want the system to make sure the watch folder exists before it tries to download a .torrent to it?
This can come in handy if your watch folder is on a temporary location such as an external drive. 
The downside is however, that you will need to add the watch folder to your open_basedir paths in your apache config if you have open_basedir restrictions on your server.
*/
$config['check_for_watchfolder'] = false;

/*
 Where is the elevaterights executable located?
 (more on what this is in the readme/installation guide)
*/
$config['elevaterights'] = "/var/www/xxx/web/xxx/elevaterights";

/*
 Where is the add torrent to queue PHP script located?
(more on what this is in the readme/installation guide) 
*/
$config['addscript'] = "/var/www/xxx/web/xxx/el_add_torrent.php";

/* Automatically refresh the torrents overview? */
$config['autorefresh'] = true;
/* If so, how often? (in milliseconds, i.e. 10000 = 10 seconds) */
$config['refreshinterval'] = "10000";


?>