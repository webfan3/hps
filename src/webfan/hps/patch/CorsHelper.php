<?php
namespace webfan\hps\patch;


class CorsHelper
{

 public static function sendCorsHeaders(array $allowedOrigins = null, $fallbackOrigin = 'web+fail://%%invalid%%.dns.frdl.de') {

  if(null === $allowedOrigins){
     $allowedOrigins = [
       "*", 
       $_SERVER['SERVER_NAME'], 
       $_SERVER['HTTP_ORIGIN'],
       $_SERVER['HTTP_HOST']
     ]; 
  }
   
   
  $originRequested = strip_tags(((isset($_SERVER['HTTP_ORIGIN'])) ? $_SERVER['HTTP_ORIGIN'] : "*"));
  $origin = (in_array($originRequested, $allowedOrigins)) ?  $originRequested : $fallbackOrigin;
   
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Allow-Origin: ".$origin);

	header("Access-Control-Allow-Headers: If-None-Match, X-Requested-With, Origin, X-Frdlweb-Bugs, Etag, X-Forgery-Protection-Token, X-CSRF-Token");

	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header('X-Frame-Options: ALLOW-FROM '.$origin);
	} else {
		header_remove("X-Frame-Options");
	}
   
	$expose = array('Etag', 'X-CSRF-Token');
	foreach (headers_list() as $num => $header) {
		$h = explode(':', $header);
		$expose[] = trim($h[0]);
	}
	header("Access-Control-Expose-Headers: ".implode(',',$expose));

	header("Vary: Origin");
 }
  
}
