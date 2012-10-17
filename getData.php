<?php

//http://snippets.dzone.com/posts/show/5882
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

  error_reporting('E_NONE');  

  include("config.php");
  
  if ($mysqli->connect_errno) {
	die( "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error );
  }

  $dateStr = ( isset($_GET['week']) ) ? "		DATE_FORMAT( `pulls`.`creationTime`, '%U' ) ='" . $_GET['week'] . "'
		  AND" : "";
  
  $sql = "
	SELECT
	#`shows`.`name` ,
	sum( `stats`.`numListeners` ) AS max,
	DATE_FORMAT( `pulls`.`creationTime`, '%j' ) AS day,
	DATE_FORMAT( `pulls`.`creationTime`, '%U' ) AS week,
	DATE_FORMAT( `pulls`.`creationTime`, '%H' ) AS hour,
	DATE_FORMAT( `pulls`.`creationTime`, '%a' ) AS dow,
	DATE_FORMAT( `pulls`.`creationTime`, '%a %m/%e/%y' ) AS stamp,
	`pulls`.`creationTime` as pullTime,
	`pulls`.`id` as id
	
	  FROM `stats` , `pulls` , `shows`
	    WHERE
		  $dateStr
		  `stats`.`pullId` = `pulls`.`id`
	      AND `stats`.`showId` = `shows`.`id`
	    GROUP BY 
	    `pulls`.`id`, `hour`
	      ORDER BY pullTime ASC
	     # LIMIT 4608
	  
  ";
  
  $totals = Array();

    $result = $mysqli->query( $sql );
  
    while ($arr = $result->fetch_assoc()) {
		$totals[$arr['stamp']][$arr['hour']] += $arr['max'] / 12;
	}

	print json_encode( $totals );
	
} 	
?>