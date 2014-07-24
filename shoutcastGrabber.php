<html><head></head>
<body>
<?php

  error_reporting(E_ERROR);  
  require('config.php');
  require('libs/spider.libc.php');
   
  $data = Array();  
  $spider = new spider();
  // spider options
  //$spider->addUserHeader( "Icy-MetaData","1" );
  //$spider->debug = 1;
  //$spider->userHeadersOnly = true;
  
  // AAC
  $infoArr[] = "AAC";
  $plsArr[] = "http://bassdrive.com/v2/streams/BassDrive3.pls";
  // WMA this duplicates 128, WTF
  // 56k MP3
  $infoArr[] = "56";
  $plsArr[] = "http://bassdrive.com/v2/streams/BassDrive6.pls";
  // 128k MP3
  $infoArr[] = "128";
  $plsArr[] = "http://bassdrive.com/v2/streams/BassDrive.pls";
  
  
  $pullId = getPullId( $mysqli );
  
  print "Starting Pull #" . $pullId . "\n";
  
  foreach( $plsArr as $k => $pls)
  {
  
    $servers = Array();
	print $infoArr[$k] . " " . $pls . "\n";
    $serversDataFromPls = file_get_contents( $pls );
	$pattern = "/(http\:.+\:[0-9]{1,5})/";
	preg_match_all($pattern, $serversDataFromPls, $servers );
  
	  foreach( $servers[0] as $server )
	  {
	    
		$p = parse_url( $server );
		
		print " -> Grabbing {$p['scheme']}://{$p['host']} on port {$p['port']}\n";
		
		$spider->sget( $p['scheme'] . "://" . $p['host'] . "/7.html", $p['port']);
		
		preg_match_all("/(<([\w]+)[^>]*>)(.*?)(<\/\\2>)/", $spider->data, $matches, PREG_SET_ORDER);
		
		$row = preg_split("!,!", $matches[0][3], 7 );
        
		
		list($numListeners, $streamStatus, $peakListeners, $maxNumber, $uniqueListeners, $bitrate, $title ) = $row;
		
		
		if ( $streamStatus != 1 ) {
		  continue;
		}
		// add server info
		$row[] = $spider->url . ":" . $spider->port;
		$row[] = $pullId;
		insertRecord( $row, $mysqli );
		
	  }
  }

  
  
  
  
  
  
  
function getPullId( $res )
{
	$result = $res->query("INSERT INTO `bassdrive`.`pulls` (`id` ,`creationTime`) VALUES (NULL , CURRENT_TIMESTAMP );");
	return $res->insert_id;
}
 
function insertRecord( $data, $res )
{
   
   list($numListeners, $streamStatus, $peakListeners, $maxNumber, $uniqueListeners, $bitrate, $title, $url, $pullId ) = $data;
   $showId =  getIdFromShowName( $title, $res );
   $urlId = getIdFromURL( $url, $res);
   
$sql = "
	INSERT INTO `bassdrive`.`stats` (
	`id` ,
	`numListeners` ,
	`streamStatus` ,
	`peakListeners` ,
	`maxListeners` ,
	`uniqueListeners` ,
	`bitrate` ,
	`showId` , `urlId`,`pullId`,
	`creationTime`
	) VALUES (
	NULL , '$numListeners', '$streamStatus', '$peakListeners', '$maxNumber', '$uniqueListeners', '$bitrate', '$showId','$urlId','$pullId',
	CURRENT_TIMESTAMP
	);
";
  $result = $res->query( $sql );
 }
 
function getIdFromURL($url, $res )
{
    
   $result = $res->query("select id from urls where `url` like '" . $url . "'" );
   if ( $result->num_rows > 0 )
   {
     $arr = $result->fetch_assoc(); 
	 return $arr['id'];
   }else{
	 $result = $res->query("	 
			INSERT INTO `bassdrive`.`urls` (
			`id` ,
			`url` ,
			`creationTime`
			)VALUES (
			NULL , '" . addSlashes( $url ) . "',
			CURRENT_TIMESTAMP
			);
							");		
	    return $res->insert_id;
   }
 }
 
 
 function getIDFromShowName( $show, $res )
 {
	$result = $res->query("select id from shows where `name` like '" . $show . "'" );
	// just get the ID
	if ( $result->num_rows > 0 )
	{
	 $arr = $result->fetch_assoc(); 
	 return $arr['id'];
	}else{// make an insert
	 $res->query("	 
			INSERT INTO `shows` (
			  `id` ,
			  `name` ,
			  `creationTime`
			)VALUES (
			  NULL , '" . addslashes( $show ) . "',
			  CURRENT_TIMESTAMP
			);	 
				");
	 return $res->insert_id;
	} 
}

?>
</body>
</html>
