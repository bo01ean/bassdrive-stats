<?php
/*
## spider.libb.php - a http sockets spider class by Nathan Trujillo ##
## Copyright 2003. Usage of this software is prohibited without     ##
## expressed written consent from the original author.              ##

TODO:
- zlib decomp support ( HTTP 1.1 )
- window.open + javascript parsing support
- MSN specific functionality ( probably create a pattern based for site type API/IDE)
- log file facilities A MUST
- database communication + multiple bots running perl/php bots
GOAL:
- to create a spider using socket based http communication
- fully extensible IDE

STATUS:
debug/creation

USAGE:
include("spider.libb.php");
$spider = new spider;
$spider->sget("http://slashdot.org", 80);
$arr = $spider->striplinks();


UNPACKSTUFF for GZIP and HTTP1.1:
print "\x1f\x8b\x08\x00\x00\x00\x00\x00"; 
$Size = strlen($Contents); 
$Crc = crc32($Contents); 
$Contents = gzcompress($Contents,$level); 	
$Contents = substr($Contents, 0, strlen($Contents) - 4); 
print $Contents; 
print pack('V',$Crc); 
print pack('V',$Size); 
exit; 
*/

class spider
  {
  var $dump;
  var $url;
  var $spitDir;
  //socket variables
  var $sFp;
  var $port;
  var $error;
  var $errorno;
  var $timed_out;
  var $read_timeout;
  //debug variables
  var $stat;
  var $debug;
  //filesystem variables
  var $fp;
  var $cwd;
  var $data;
  //HTTP variables
  var $doc; //document to request
  var $req;
  var $header;
  var $post;
  var $validhtml;
  var $htmllen;
  var $cookie;
  var $length;
  var $headertype = array();
  var $headerdata = array();
  var $domain;
  var $path;
  var $protocol;
  
  // user headers variables
  var $userHeadersOnly = false;
  var $userHeaders = Array();
  
  //statistical variables
  var $match      = array();
  var $links      = array();
  var $chars      = array('|','|','/','/','-','-','\\','\\','|','|','/','/','-','-','\\','\\');
  var $got;
  var $tmp;
  var $percentage;
  var $tmparr     = array();
  var $chunks;
  var $cli        = FALSE;
  var $graph      = array('','','','','','','','','','');
  var $func_pt    = 0;
  //internal variables
  var $rd;
  var $skip;
  var $sizelimit;
  var $domains    = array();
  var $fulllinks  = array();
  var $timer      = 0;
  var $newline    = "\n";

    function spider(){
    /////////
	 if( isset($_SERVER['REMOTE_ADDR']) ){
	   $this->newline = "<br>\n";
	 }else{
       $this->cli = TRUE;
	   $this->newline = "\n";
	 }
     /////////          
	}
    
    //are we in debugging mode ?
    function setdebug($mode){$this->debug = $mode;}
	
	//sets the http referrer
    function setReferer($referrer){$this->referrer = $referrer;}

    //sets our user agent, defaults to IE6
    function setAgent($agent){$this->agent = $agent;}

    //set a cookie if any
    function setCookie($data){$this->cookie = $data;}

    function mrtime(){ 
        list($usec, $sec) = explode(" ",microtime());
          return ((float)$usec + (float)$sec); 
                     }

    function checktimeout($fp){
      if ($this->read_timeout > 0) {
        $fp_status = stream_get_meta_data($fp);
          if ($fp_status["timed_out"]) {
            $this->timed_out = true;
       return true;
                                       }
                                    }
       return false;
                              }

    //open a socket connection
    function open($url,$port='80'){
      $this->url  = $url;
      $this->port = $port;
	  $this->skip=0;
      $this->sFp= fsockopen($this->url,$this->port,$this->error,$this->errorno);
        if((!$this->sFp)&&($this->debug ==TRUE)){
          print "$this->errstr ($this->errno)!!!!!\n";
          $this->skip=1;
		die;
		}else{
		  stream_set_timeout ($this->sFp,3,33);
		  $this->skip=0;
          return $this->sFp;
        }
                                 }//end func def open

    function headerscan($what){
      for($cnt=0;$tmp<count($this->headerblock);$tmp++){
        if(eregi("($what)",$this->headerblock[$tmp][0])){
           return $this->headerblock[$tmp][1]; 
		 }else{
         }
      }//end for     
    }//end func def

    function cwd(){
      $full = $this->url . "/" . $this->what;
        if(ereg("/",$this->what)){
          $chunks = explode("/",$this->what);
          $test = $chunks[count($chunks)-1];
        }else{
          $test = $this->what;
        }

      $this->bugout("checking $full\n");

      if(ereg("((.+\.).+$)",$test)){
        $this->bugout("$test is a page\n"); 
        $this->cwd = $chunks[count($chunks)-2];
        }else{
          $this->bugout("$test is a directory\n"); 
          $this->cwd = $test;}
     return $test;
  }

  function convert_fqdn($full){
        $this->bugout("normal:\n\$full =$full\n");
        $this->chunks    = parse_url($full);
        $this->protocol  = $this->chunks[scheme];
        $this->domain    = $this->chunks[host];
        $this->query     = $this->chunks[query];
        $this->doc = ($this->chunks[path]) ? $this->chunks[path] : "/";
        $this->path = $this->chunks[path];
        // add query string to end of get string
        $this->doc.= ($this->query) ? "?" . $this->query : ''; 
  }
##############
## sget ( full url, port) a friendly interface to the get function, the 's' stands for socket
##############
    function sget($full,$port=80){
      // default to standard HTTP port
      $this->port = isset($port) ? $port : 80 ;

      if(ereg("http://",$full)){
        $this->convert_fqdn($full);        
		// make http connection to server 
        $this->open($this->domain,$this->port);
	    if($this->skip==0){// $this->skip only occurs in HTTP response codes of 301 or 302
		  $this->get($this->doc,"socket");
        }

	  }else{ 
	    $this->bugout("IMPROPER DATA: expects a FQDN like 'http://host/document'");
      }
    }
## end funcdef sget ##

##############
## scheck ( full url, port) a friendly interface to the get function, the 's' stands for socket
##############
    function scheck($full,$port=80){
      // default to standard HTTP port
      $this->port = isset($port) ? $port : 80 ;

      if(ereg("http://",$full)){
        $this->convert_fqdn($full);        
		// make http connection to server 
        $this->open($this->domain,$this->port);
	    if($this->skip==0){// $this->skip only occurs in HTTP response codes of 301 or 302
		  $this->get($this->doc,"check");
        }

	  }else{ 
	    $this->bugout("IMPROPER DATA: expects a FQDN like 'http://host/document'");
      }
    }
## end funcdef sget ##



######################
## download - dump a webpage to disk
######################
    function download($full,$port=80){
      // default to standard HTTP port
      $this->port = isset($port) ? $port : 80 ;

      if(ereg("http://",$full)){
        $this->convert_fqdn($full);        
		// make http connection to server 
        $this->open($this->domain,$this->port);
	    if($this->skip==0){// $this->skip only occurs in HTTP response codes of 301 or 302
		  $this->get($this->doc,"dump","GET",$this->doc);
        }

	  }else{ 
	    $this->bugout("IMPROPER DATA: expects a FQDN like 'http://host/document'");
      }
    }

######################

######################
## get_http_header - store http header from server in object
######################
	function get_http_header(){
	 $l = fgets($this->sFp);
           //grab all data but checking for http '\r\n' split into content
           $this->header      = "";
           $this->headerblock = "";
           $this->req         = "";
           $this->data        = "";
             while($l != "\r\n"){
                // parse : delimited data and put into array 'headerblock', cleaning out newlines \r\n
                $clean = ereg_replace("\r\n","",$l);
                $this->headerblock[] = preg_split("/:\s/",$clean,"2");
                // write : header into raw object string 'header'
                $this->header .= $l;
                $l = fgets($this->sFp);
             }//end header grabbing block
                $this->length = $this->headerscan("length");
	
			$this->bugout(  $this->header  );	
				
				
	}
## end funcdef get_http_header ##


##############
##
##  addHeader()
##
##############

	function addUserHeader( $key, $val )
	{
	  $this->userHeaders[$key] = $val;
	  $this->bugout("Adding HTTP header:");
	  $this->bugout($this->userHeaders);

	}

## end funcdef addHeader ##

    function getUserHeaders()
	{
	  $headerStr = "";
	  foreach( $this->userHeaders as $k => $v )
	  {

	    $headerStr .= $k . ": " . $v . "\r\n"; 
	  }
	  
	    $this->bugout( "headerStr: $headerStr ");
	  
	  return $headerStr;
	}


##############
## get( server document , method , type   ) - get data from a socket
## server   - fully qualified domain name/ URI
## methods:  1.fopen - a generic fopen, which doesn't use http ( slow )
##           2. socket - makes http 1.0 request
##           3. socketdump - makes a http 1.0 request and dumps all output to a file( useful for when retrieving archives or large files.
## type(s)   1. http request method ( GET, POST , etc... )
##############
    function get($what,$method,$type='GET',$file=0){
      $this->what = $what;
      $this->req = "$type $this->what HTTP/1.0\r\n";

	  
	  if ( $this->userHeadersOnly)
	  {
	    // user headers
	    $this->req.= $this->getUserHeaders();
	  }else{
//  $this->req.= "Accept: */*\r\n";
      $this->req.= (isset($this->referrer)) ? ("Referer: $this->referrer\r\n") : ("") ;
      $this->req.="Accept-Language: en-us\r\n";
//  $this->req.="Accept-Encoding: gzip, deflate\r\n"; // HTTP 1.1
      $this->req.="User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n";
      $this->req.="Host: $this->url\r\n";
      $this->req.="Connection: Keep-Alive\r\n";
      $this->req.= (isset($this->cookie))   ? ("Cookie: $this->cookie\r\n")    : ( "" ) ;
      $this->req.= (isset($this->post))     ? ("$this->post\r\n")              : ( "" ) ;
	  }
	  
      $this->req.="\r\n";
      $this->bugout("Request Header:\n$this->req\n\n");

     switch($method){
       case 'fopen':
         switch($this->port){
           case '80':
           $this->proto = "http://";
           break;
         }//implement further later
       $what = $this->proto . $this->url . "/" . $this->what;
       $this->rFp = fopen($what,"rb");
         while($buf =fgets($this->rFp)){
           $this->data .= $buf;
         }
        
		 fclose($this->rFp);
       break;
	   case 'check':
         if(! fputs( $this->sFp,  $this->req)  )
	       { die("Errors speaking with remote host " . $this->url );}
           //NEW METHOD (fast)
           //get header from socket
		   $this->get_http_header();
		   if(eregi('ok',$this->headerblock[0][0])){
			   $this->bugout("File OK\n");
               print "FILE " . $this->domain . $this->doc . " FOUND !!!!!<br>\n";
		   }else{
               print "ERROR: http://" . $this->domain . $this->doc . " NOT FOUND<br>\n";
		   }

       break;
       case 'socket':
       //write request to open socket
         if( !fputs( $this->sFp,  $this->req)  )
	       { die("Errors speaking with remote host " . $this->url );}
           //NEW METHOD (fast)
           //get header from socket
		   $this->get_http_header();

           $this->bugout("SERVER RESPONSE:\n" . $this->header);

           //determine length of content from conten-length header field
		   $redirect = $this->headerscan("location");
			
             if($cookie = $this->headerscan("cookie"))//write cookies dynamically
			   $this->setCookie($cookie);
               //follow redirects 
			
			   if($this->headerscan("location")){//check for redirect 301/2s    
                 if($this->rd==1){
                   $this->bugout("302 found redirecting");
                   $this->sget($redirect);
                 }else{
                   $this->bugout("302 found not redirecting");
				   break;
				 }
               }


               if(($this->length > 0)&&($this->length > $this->sizelimit)){
                 $this->bugout("0  % of " . $this->length . " in " . $this->url . $this->what);
                 $funk =0;
                 $start = $this->mrtime();
                   while($l = fread($this->sFp,$this->length)){
                     $funk = ( $funk < count($this->chars) - 1) ? $funk++ : 0;
                     $this->data .= $l;
                     $this->got = strlen($this->data);
                     $this->percentage = round( ($this->got / $this->length) * 100 );

                     // add logic to output status information in CLI mode only.
                     $this->cliout("\r" . $this->percentage .  $this->chars[$funk] . "\r");
                     
                     if ($this->got>=$this->length){
                       ####print $this->percentage . "%\n";
                       $time = ($this->mrtime() - $start);
                       $this->cliout("GOT $this->length kb in $time ");
                     break;
                     }
                   }  
               }else{
                 //$this->cliout("No 'content-length' in header, using S L O W fgets!!\n");
                 $start = $this->mrtime();
                 //$this->cliout("START $start\n");
                 $tmpcnt = 0;
                   while($l = fgets($this->sFp)){ 
                     $this->data .= $l;
                     $time = ($this->mrtime() - $start);
                     $this->tmparr[] = ($time - $old) . " $tmpcnt " . ereg_replace("\n","",$l);
                     $old = $time;

                     $funk = ($funk<count($this->chars) - 1) ? $funk + 1 : 0;
                     $this->cliout("         " . strlen($this->data) . $this->chars[$funk] . "\r");

                     $tmpcnt++;
                   }
                 $timer .= "                    Length: " . strlen($this->data) . "\n";
                 $timer .= "                    Ending: " .($this->mrtime() - $start) . "\n";
                 $timer .= " First time measure in get: " . $this->tmparr[0] . "\n";  
		         $timer .= "    Total time to get page: " . array_sum($this->tmparr) . "\n";
		         $timer .= "Average get time (by line): " . array_sum($this->tmparr) / count($this->tmparr) . "\n";
            	 array_multisort($this->tmparr);
                 $timer .= "                  shortest: " . $this->tmparr[0] . "\n";
                 $timer .= "                  longest : " . $this->tmparr[(count($this->tmparr)-1)]. "\n";
                 //$this->cliout($timer);
               }
       break;
       case 'socketdump':
         fputs($this->sFp,$this->req);

           $uniquefile = $_SERVER[PHP_SELF] . ".dat";
           $newptr=fopen($uniquefile,"w+");
           $this->get_http_header();
              while($new=fgets($this->sFp)){
                fwrite($newptr,$new);
               (isset($this->debug))?(print $l):(print "");
              }//end data while
           fclose($newptr);
       break;
	   case 'dump':
         fputs($this->sFp,$this->req);

           $uniquefile = "/usr/www/tmp/tmp/uploads/" . $this->domain . $file;         
           $dirs = explode("/",$uniquefile);		   
		   array_pop($dirs);
           $dir_to_create = join("/",$dirs);
		   $this->makedir($dir_to_create);

           if(file_exists($uniquefile)){
			   $this->cliout("file '$uniquefile' exists.. moving on\n");
			   break;
		     $this->timer=1;
		   }else{
			$this->timer=0;
		   }
             if($newptr=fopen($uniquefile,"w+")){
               $this->get_http_header();              

		         if(($this->length > 0)&&($this->length > $this->sizelimit)){
			      while($new = fread($this->sFp,$this->length)){
                    fwrite($newptr,$new);
                    $this->output_graph($this->length , ftell($newptr) , $file) ;
                  }

			 }else{
             				  
               
                while($new=fgets($this->sFp)){
                  fwrite($newptr,$new);
                }//end data while            
             }
             fclose($newptr);
             }
			 $this->cliout("\n");
       break;
       default:
         $this->bugout("please choose method for function get() in class spider");
         die("terminating ...");
       break;
     }//end switch

  if(isset($this->what)&&!isset($this->cwd)){
    $this->cwd = eregi_replace("/(.*\..*)$","/",$this->what);
    $this->tmp = $this->cwd;
    $dir = explode("/", $this->what);
    //print_r($dir);

    //DETERMINE DEPTH IN SERVER REDO !!!
    if((count($dir)=="1")){
      $this->cwd = $dir[0] . "/";
	}else{
      array_pop($dir);
         for($i=0;$i<count($dir);$i++)
	      {$this->cwd.=$dir[$i] . "/";}
           //print "cwd=" . $this->cwd . "/";
     }//end for
  }//end if                
}
## end funcdef get ##

function r_mkdir($directory){
	//print $directory . "\n";
           $dirs = explode("/",$directory);		   
		   array_pop($dirs);//get last one of beacause it is probably a file

           for($i=0;$i <count($dirs);$i++){
			   if ($dirs[$i] == "") {continue;}
	        $tmp_dirs[] = $dirs[$i];
	        $tmp_dir_str = "/" .  join("/",$tmp_dirs);
	        if(file_exists($tmp_dir_str)){
               
		    }else{ 
				$this->cliout( "making directory '$tmp_dir_str'\n" );
		       //mkdir($tmp_dir_str ,777);
            } 
	      }

}
## DEPRECATED SEE parseheader()
  function splitHeader(){
    $this->validhtml = substr( $this->data, strpos($this->data, "\r\n\r\n") );
    $this->htmllen = strlen($this->validhtml);
    $cut = ($this->validhtml - $this->htmllen);
    //print $cut;
    $this->header = substr( $this->validhtml, 0,strlen($cut));
    //print $this->header;
    $this->data = $this->validhtml;
  }

  function pause($amount){
    $this->bugout("taking a $amount second break....\n");
    sleep($amount);
  }//end funcdef pause

  function makedir($dir){
    if(file_exists($dir)){
//      $this->bugout("directory $dir exists, moving on");
    return 0;
    }else{
//      $this->bugout("$dir doesn't exist, creating");
      `mkdir -p $dir`;
      return 1;
    }
  }// end funcdef makedir
################
## spit ( mode , unique , directory ) output document data to a file
## mode(s) : file , writes to file specified by uniqe and directory arguments.
##           out - dumps all data to the browser / console
##           links - parses document for all links, and returns array of valid links
##           source - displays page's/document's source to the browser/console in raw format
##           default - displays source in html format using htmlspecialchars()
################
  function spit($mode,$unique,$dir=""){
    $this->bugout("VARS IN spit:$mode,$unique,$dir=");
    switch($mode){
      case 'file':
	    if($dir !=""){
		  $this->makedir($dir);
	      $unique = $dir .  "/" . $unique;
		  $this->makedir(dirname($unique));
		  $this->bugout("UNIQUE is: $unique\n\n\n");
		}else{      
		  $this->bugout("no output directory specified, BUT ignoring ..");
		  die();
		}

        $this->fp=fopen($unique,"w+");
          if((strlen($this->data)>0)&&(strlen($this->data) > $this->sizelimit)){
            fwrite($this->fp,$this->data); }
            else{
              $this->bugout($this->stat);
              $this->bugout("no data to write or file too small!!!\n\n");
              }
              //print $this->data;
              fclose($this->fp);
              $this->data ="";
      break;
      case 'out':
        header ("Content-type: $unique");
        print $this->data;
      break;
      case 'links':
        if($unique){
         //print "searching for $unique in $this->url : $this->what\n";
         $skipt="0";
         $search=$this->striplinks($this->data);
           for($i=0;$i<count($search);$i++){
             $findit = $search[$i]; 
             if(ereg("($unique)",$findit) ){
               //print "found in:<br><b>" . htmlspecialchars($findit) . "</b><br>\n"; 
                                           }else{
                                  $skipt++;     }    
                                           }
                   }else{
                return $this->striplinks($this->data);
                         }
         //print "string '$unique' was not found in $skipt lines<br>\n";
       break;
       case "source":
         $this->match = $this->stripimgs($this->data);
         return $this->match;
       break;
       default:
         header ("Content-type: text/html");
         //$this->splitHeader();
         print "<pre>" . htmlspecialchars($this->data) . "</pre>";
       break;
            }// end switch
    }
## end funcdef spit##

##############
## debugOut - prints objects caught debug information
##############
    function debugOut(){
      print $this->stat;
      print $this->req;
    }
## end funcdef debugOut ##

##############
## getSocketStatus - tells all information on a current socket
##############
    function getSocketStatus(){
      if($this->sFp){
        print_r(stream_get_meta_data($this->sFp));
	  }
    }//end funcdef
## end fundef getSocketStatus ##

##############
## bugout - display debug information if $this->debug is set
##############
    function bugout($var){
      $ending = ($_ENV["USER"]) ? "\n" : "<br>";
        if(!isset($this->debug)){
          return;               
        }else{
          if(is_array($var)){
            print "DEBUG : \n";
            print_r($var);
          }else{
            $var = ereg_replace("(\n|\r){2,}",$ending,$var);
            print "DEBUG :\n$var\n";
          }
        }
    }
## end funcdef bugout ##
########################
## function cliout
########################
	function cliout($data){
      print ($this->cli) ? $data : "";
	}
########################

##############
## strip ( data , regex) - remove elements from a http page
## based on a regular expression
##############
    function strip ($in , $pattern ){
		print $in ."\n";
      $document = (isset($in)) ? $in : $this->data;
      $proto="http://";
        if(preg_match_all($pattern, $document, $links)){      
		  $dirty=$links[1];
          for($i=0;$i<count($dirty);$i++){
            $tmp=$dirty[$i];
            $tmp = ereg_replace("(^/|/$)","",$tmp);
            $clean[]= $tmp;
            $clean = array_unique($clean);
              if($clean[$i]!=""){
                $newclean[] = $clean[$i];
              }
		  }
          return $newclean;
       }       
    }
## end funcdef strip ##

###############################################
###############################################
## 'strip*' type functions receive a full http page
## as input and return the desired elements in an array
###############################################
###############################################

##############
## stripimgs - remove images from a webpage
##############
    function stripimgs($in){
	  return $this->strip($in,'!img\s*src=\s*[\"\']([^\"\']*)[\"\']!isx');
    }
## end funcdef stripimgs ##

##############
## stripimgs - remove background images from a webpage
##############
    function stripbgs($in){    
      return $this->strip($in,'!background\s*=\s*[\"\']([^\"\']*)[\'\"]!isx');
    }
## end funcdef stripbgs ##

##############
## stripallimgs - returns background images and inline images from a webpage
##############
    function stripallimgs(){
      if($this->data){
        $tmp = @$this->stripimgs();
        $b   = @$this->stripbgs();
          for($i=0;$i<count($b);$i++){
            $tmp[] .= $b[$i];
          } 
        return $tmp;
      }
    }
## end funcdef stripallfimgs ##

 function striplinks( $in = "" ){    
    $document = ( $in ) ? $in : $this->data;
    $proto="http://";
    if(preg_match_all('!href=\s*[\'\"]([^\"\']*)[\"\'][^>]*>([^<]*)</a!isx', $document, $links)){
    $this->fulllinks = $links;
    $dirty=$links[1];
      for ( $i =0 ; $i < count($links); $i++){
        for ( $j = 0;$j<count($links[$i]); $j++){
          $links[$i][$j] = preg_replace('!\s{1,}!',' ',trim($links[$i][$j] ));
        }
     }
      $dirty = array_unique($dirty);
        for($i=0;$i<count($dirty);$i++){
          $tmp=$dirty[$i];
            if(ereg("^mailto:",$tmp)){ continue; }//no email addys please !!
              $tmp = ereg_replace("(^/|/$)","",$tmp);
              $clean[] = $tmp;
              $clean = array_unique($clean);
                if($clean[$i]!=""){
                  $newclean[] = $clean[$i];
                }
            }
         return $newclean;
        }
  }
/*## end funcdef striplinks## */


##############
## striplinks - parse out links from a webpage
##############
    function _striplinks($in=0){ 
      $document = ( $in!=0) ? $in : $this->data;
      $proto="http://";      //'<\s*a\s+.*href\s*=\s*([\"\'])?(?(1)(.*?)\\1|([^\s\>]+))'isx
      if(preg_match_all('!href=\s*[\'\"]([^\"\']*)[\"\'][^>]*>([^<]*)</a!isx', $document, $links)){
        $this->fulllinks = $links;
        $dirty=$links[1];
          for ( $i =0 ; $i < count($links); $i++){
            for ( $j = 0;$j<count($links[$i]); $j++){
              $links[$i][$j] = preg_replace('!\s{1,}!',' ',trim($links[$i][$j] ));
            }
          }
        
		$dirty = array_unique($dirty);
          
		  for($i=0;$i<count($dirty);$i++){
            $tmp=$dirty[$i];
              if(ereg("^mailto:",$tmp)){ continue; }//no email addys please !!
              $tmp = ereg_replace("(^/|/$)","",$tmp);
              $clean[] = $tmp;
              $clean = array_unique($clean);
                if($clean[$i]!=""){
                  $newclean[] = $clean[$i];
                }
          }//end for
       return $newclean;
      }
    }
## end funcdef striplinks ##

##############
## getmsn - grab links from javascript fields on msn pages
##############
    function getmsn(){
      $data = strip_tags($this->data, '<a>');
      $clean = ereg_replace("/s{1,}"," ",$data);
      $chunks = explode(" ",$clean);
        for($i=0;$i<count($chunks);$i++){
          if(ereg("[t][n][_]",$chunks[$i]))
            $tmp .= ereg_replace('href="javascript:'," ",$chunks[$i]);
          }
          preg_match_all("![0-9]{5}!",$tmp,$found);
      return $found;
    }
## end funcdef getmsn##

##############
## close - closes an object's open socket
##############
    function close(){
      @fclose($this->sFp);
    }
## end funcdef ##
  
  ## end 'spider' class definition

    function output_graph($total , $current, $file){
	  $this->funk_pt = ( $this->funk_pt < count($this->chars) - 1) ? $this->funk_pt++ : 0;
      $segment =   floor($total / 20);//size of each segment
      $segment_cnt = ceil(10 *($current / $total));
	  $this->cliout("|");
	  for($i=0;$i<count($this->graph);$i++){
        if($i < $segment_cnt){
			$this->graph[$i] = "-";
			$this->graph[$i + 1] = $this->chars[$funk];
		}else{
            $this->graph[$i] = " ";
		}
		$this->cliout($this->graph[$i]); 
      }
  	  $this->cliout("|  " .    ($current / $total)*100      . "% " . $this->chars[$this->funk_pt] . " $current/$total $file\r");
	  //$this->cliout("\n\$current = $current \$segment= $segment : \$segment_cnt = $segment_cnt\r");
	}
 };
?>