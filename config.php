<?php

  //chdir("/usr/home/intuit/bassdrive-stats/");

  $mysqli = new mysqli("localhost", "root", "", "bassdrive");
  
  if ($mysqli->connect_errno)
  {
	die( "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error );
  }

?>
