======================================================
rtorrent for iPhone beta2 (March 12th 2011)

Kramerica Industries (www.kramerican.dk)
info@kramerican.dk

This software is free, open source, and drizzled with liberal amounts of ranch dressing. This means it comes with no warranty or guarantee whatsoever. If your server explodes and deadly shrapnel tears your new poodle to shreds - well, tough luck buddy!
Portions of this software is shamelessly stolen from rTWi (see http://rtwi.jmk.hu/wiki - It's pretty cool! Although personally I use rutorrent. But hey, potato tomatoe)
======================================================

======================================================
Quick installation guide:
======================================================

 1) Copy the rtorrent for iPhone files to a web accessible directory of choice (hereinafter riPhone - yeah, I couldn't come up with anything better)
 2) Edit the config.inc.php file with your settings
 3) Set the appropriate rights for the elevaterights executable by issuing the commands:

  chown root:root elevaterights
  chmod 6755 elevaterights

 4) Open up the riPhone web-page on your iPhone - Congrats, you can now monitor rtorrent downloads and add torrents!

In addition you may want to consider htaccess password protecting the riPhone directory - you will need to dig through your iPhone settings to make it remember
login information however, for it to be comfortable to use.

To add a torrent to the rtorrent active downloads simply tap/hold a link to a torrent file in safari on your iPhone and select "copy" from the menu that pops up. Next
switch to riPhone and paste the URL in the edit field (you can't miss it) and tap the "add torrent" button. If after a brief delay it says "Torrent added!" - well then stuff
works. It will then refresh the page - if your torrent doesn't show up immediatly, don't worry - sometimes it takes a few seconds for it to show up.
Most links should work this way. I have had good success with most eztv and bt-chat links.

IMPORTANT: The link you paste has to be a direct link to a .torrent - Not a webpage containing a link to the torrent! To make sure you have a good link, you can tap it on your
iPhone and then safari will complain it cannot download the file. This means you can be pretty sure you are dealing with a direct .torrent link.

======================================================
More detailed explanation of what the hell is going on:
======================================================
Most of the settings are covered in fair detail in config.inc.php and should make sense to most people - let me clarify a few things however:

 * The elevaterights executable
 - 	What this does is simply allow apache to run a php script with root privileges on your server. This is required in order for it to invoke wget 
	(which downloads a .torrent and puts it in your watch folder) without any permission problems. In order for it to do this, the elevaterights executable needs to be
	chowned root and have its SETUID bit set by issuing a chmod 6755 (the 6 in front of 755 sets user and group to root for the executable)

 Is this a security risk? Maybe, I have no clue. All I know is that it works. I do use the escapeshellarg function to make the URL input safe - AFAIK that should neuter any
 security concerns regarding this functionality. (See http://dk2.php.net/manual/en/function.escapeshellarg.php )

In any case, in the config you need to supply fully qualified paths to both the elevaterights executable and then the php script it runs as root.

======================================================
Adding riPhone to your home screen:
======================================================
You can do this by tapping the + sign in the bottom menu of safari - this enables you to place an icon on your home screen (complete with a pretentiously fancy loading screen)
However, as riPhone requires you to switch back and forth between riPhone and a webpage in Safari (when you are copying in .torrent links) - then this does not make things very 
user-friendly as the iPhone will reload the riPhone interface every time you switch back to it. If you can live with the delay, then that's fine - I would recommend you just 
bookmark it and open it up as a regular page in Safari when adding torrents, however.


======================================================
Files:
======================================================

 config.inc.php		- contains all the configuration options
 index.php		- renders the UI for the iPhone
 ajax.rtorrent.php	- Ajax functions
 elevaterights		- c executable which has the job of running something with root privileges
 el_add_torrent.php	- the script which does the actual wget of a .torrent and puts it in your watch folder

jqtouch, jqtouch themes and a couple of rTWi includes are part of this package as well

======================================================
Troubleshooting / Support:
======================================================
I can be contacted at info@kramerican.dk or zabersoft@gmail.com 
I will not provide any support - but if you have found a bug or want to contribute with some functionality/code then I'm all ears.


If you get something about SCGI couldn't connect in the torrents overview - then doublecheck that rtorrent is running and listening on a port (usually 5000)
Doing a "/etc/init.d/rtorrent restart" usually fixes the problem - and you may want to take a look at your .rtorrent.rc config file to see how it's configured
 


Thanks for trying out rtorrent for iPhone!