# Introduction #

Proposed roadmap going forward with rtorrent for iPhone/smartphones - Any and all feedback much appreciated


# Proposed Features #

  * Investigate further the possibility of not using the download to watchfolder approach (Neuter any security concerns regarding using wget to fetch arbitrary URL's)

  * Add the option of having a smartphone-optimized login-screen

  * This project uses code "borrowed" from rTWi (see http://rtwi.jmk.hu/wiki) - Clarify the status on whether that is cool or not, and maybe look into cleaning up that codebase and making it more specific for our purpose. Also, it has been brought up that it isn't compatible with all versions of rtorrent - figure out the differences and make this interface more stable/compatible.

  * Rename the project so it isn't so platform specific? After all, it should work well with all capable smartphones

  * Switch to jquerymobile when that thing finally gets released in a stable version

  * Filtering options. Show all active torrents. Show all torrents with the TV label

  * Manipulation: pause, resume, delete, etc.

  * Verify URL's added and warn if it doesn't look like a .torrent  (Curl - check headers/extension)

  * Fix form submit so that you can use the iPhone keyboard to submit

  * Hunt down JS error when hitting refresh

  * Statistics (possibly using the FLOT jquery plugin)

  * Add ability to execute external sh script when torrent completes download (for example to encode video for iPhone)