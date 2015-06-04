# rtorrent for iPhone #

rtorrent for iPhone is a touch-compatible web-application for smartphones (Android is equally compatible). As of the current version it can:

1) Display a list of all active torrents along with upload/download speed, estimated remaining download time and percentage complete

2) Add a torrent by copy/pasting a .torrent download URL - which then gets passed to rtorrent

3) It can connect to rtorrent using a unix socket, scgi\_port or an SCGI gateway


# Quick installation guide: #


  1. Copy the rtorrent for iPhone files to a web accessible directory of choice (hereinafter riPhone - yeah, I couldn't come up with anything better)

> 2) Edit the config.inc.php file with your settings

> 3) Set the appropriate rights for the elevaterights executable by issuing the commands:

> chown root:root elevaterights

> chmod 6755 elevaterights

> 4) Open up the riPhone web-page on your iPhone - Congrats, you can now monitor rtorrent downloads and add torrents!

In addition you may want to consider htaccess password protecting the riPhone directory - you will need to dig through your iPhone settings to make it remember
login information however, for it to be comfortable to use.

To add a torrent to the rtorrent active downloads simply tap/hold a link to a torrent file in safari on your iPhone and select "copy" from the menu that pops up. Next
switch to riPhone and paste the URL in the edit field (you can't miss it) and tap the "add torrent" button. If after a brief delay it says "Torrent added!" - well then stuff
works. It will then refresh the page - if your torrent doesn't show up immediatly, don't worry - sometimes it takes a few seconds for it to show up.
Most links should work this way. I have had good success with most eztv and bt-chat links.

IMPORTANT: The link you paste has to be a direct link to a .torrent - Not a webpage containing a link to the torrent! To make sure you have a good link, you can tap it on your
iPhone and then safari will complain it cannot download the file. This means you can be pretty sure you are dealing with a direct .torrent link.