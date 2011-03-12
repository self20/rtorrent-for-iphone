#!/usr/bin/php
<?php 

 include("config.inc.php");

$cmd = "cd ".$config['watchfolder']."; wget --content-disposition ".$argv[1];
 echo "Executing command \n".$cmd;
  echo exec($cmd);
  
  ?>