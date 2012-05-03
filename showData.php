<?php
//http://snippets.dzone.com/posts/show/5882
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

error_reporting('E_NONE');  

  require("config.php");  
  
  $sql = "
	SELECT `shows`.`name` , sum( `stats`.`numListeners` ) AS max,

	DATE_FORMAT( `pulls`.`creationTime`, '%j' ) AS day,
	DATE_FORMAT( `pulls`.`creationTime`, '%U' ) AS week,
	DATE_FORMAT( `pulls`.`creationTime`, '%H' ) AS hour,
	DATE_FORMAT( `pulls`.`creationTime`, '%a' ) AS dow,	
	DATE_FORMAT( `pulls`.`creationTime`, '%a %m/%e/%y' ) AS stamp,	
	`pulls`.`creationTime` as pullTime,
	`pulls`.`id` as id
	
	  FROM `stats` , `pulls` , `shows`
	  #  WHERE DATE_FORMAT( `pulls`.`creationTime`, '%U' ) ='{$_GET['week']}'
	WHERE	   `stats`.`pullId` = `pulls`.`id`
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
	

	print array2json( $totals );
		
// http://www.bin-co.com/php/scripts/array2json/	
function array2json($arr) {
    if(function_exists('json_encode')) return json_encode($arr); //Lastest versions of PHP already has this functionality.
    $parts = array();
    $is_list = false;

    //Find out if the given array is a numerical array
    $keys = array_keys($arr);
    $max_length = count($arr)-1;
    if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
        $is_list = true;
        for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
            if($i != $keys[$i]) { //A key fails at position check.
                $is_list = false; //It is an associative array.
                break;
            }
        }
    }

    foreach($arr as $key=>$value) {
        if(is_array($value)) { //Custom handling for arrays
            if($is_list) $parts[] = array2json($value); /* :RECURSION: */
            else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
        } else {
            $str = '';
            if(!$is_list) $str = '"' . $key . '":';

            //Custom handling for multiple data types
            if(is_numeric($value)) $str .= $value; //Numbers
            elseif($value === false) $str .= 'false'; //The booleans
            elseif($value === true) $str .= 'true';
            else $str .= '"' . addslashes($value) . '"'; //All other things
            // :TODO: Is there any more datatype we should be in the lookout for? (Object?)

            $parts[] = $str;
        }
    }
    $json = implode(',',$parts);
    
    if($is_list) return '[' . $json . ']';//Return numerical JSON
    return '{' . $json . '}';//Return associative JSON
} 	
?>
