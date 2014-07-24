<?php

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

error_reporting('E_ALL');  

  include("config.php");
  
  if ($mysqli->connect_errno) {
	die( "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error );
  }


  $dateStr = ( isset($_GET['week']) ) 
  		? "\t\t\tAND		DATE_FORMAT( `pulls`.`creationTime`, '%U' ) ='" . intval($_GET['week']) . "'" 
		 : "";
  
  $dateStr .= ( isset($_GET['year']) ) 
  		? "\t\t\tAND		DATE_FORMAT( `pulls`.`creationTime`, '%Y' ) ='" . intval($_GET['year']) . "'" 
		 : "";

  $sql = "
	SELECT
	#`shows`.`name` ,
	sum( `stats`.`numListeners` ) AS max,
	DATE_FORMAT( `pulls`.`creationTime`, '%j' ) AS day,
	DATE_FORMAT( `pulls`.`creationTime`, '%U' ) AS week,
	DATE_FORMAT( `pulls`.`creationTime`, '%H' ) AS hour,
	DATE_FORMAT( `pulls`.`creationTime`, '%a' ) AS dow,
	DATE_FORMAT( `pulls`.`creationTime`, '%Y' ) AS year,
	DATE_FORMAT( `pulls`.`creationTime`, '%a %m/%e/%y' ) AS stamp,
	`pulls`.`creationTime` as pullTime,
	`pulls`.`id` as id
	
	  FROM `stats` , `pulls` , `shows`
	    WHERE
		  `stats`.`pullId` = `pulls`.`id`
		  {$dateStr}

	      AND `stats`.`showId` = `shows`.`id`
	    GROUP BY 
	    `pulls`.`id`, `hour`
	      ORDER BY pullTime ASC
  ";
  
  $totals = Array();

    $result = $mysqli->query( $sql );
  
    while ($arr = $result->fetch_assoc()) {
		$totals[$arr['stamp']][$arr['hour']] += $arr['max'] / 12;
	}

	print json_encode( $totals );
	 	
?>