<?php
namespace webfan\hps;

use Proxy\Proxy as Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter as GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter as RemoveEncodingFilter;
use Zend\Diactoros\ServerRequestFactory as ServerRequestFactory;
use GuzzleHttp\Client as Client;

use webfan\hps\patch\Uri as Uri;
use webfan\hps\patch\Request as Request;

class MyRequestFactory extends \Zend\Diactoros\ServerRequestFactory
{
	public function createServerRequest(
		    $host = null,
		    $url = null,//marshalUriFromSapi($server, $headers),
		    $method = null, //marshalMethodFromSapi($server),
            $query=  null,//null	
		
            $headers= null, //null
            $cookies= null,//null	
		
		    $files = null,		
		    $server = null,                  
        
            $body = null, //'php://input',

           // marshalProtocolVersionFromSapi($server)
		    $protocol = '1.1',
	        $parsedBody =null){
		
		
		
		
		
		$server = $server?:$_SERVER;
		$files = $files?:$_FILES;
		$cookies = $cookies?:$_COOKIE;
		$query = $query?:$_GET;
		$method = $method?:$server['REQUEST_METHOD'];
		$headers= $headers?: \frdl\webfan\App::God(false)->{'parseHeaders'}($server);
		//if (null === $cookies && null!==$headers && array_key_exists('cookie', $headers)) {
         //   $cookies = parseCookieHeader($headers['cookie']);
      //  }
		$body = $body?:'php://input';
		
		$p = parse_url($url);	
        parse_str($p['query'], $queryParams);
		
	 	$p['path'] = '/'.ltrim($p['path'], '/');
		
		
		$queryParams = array_merge($query, $queryParams);
		
		$query = http_build_query($queryParams);
		
		if(null === $host ){
		  $host = $p['host'];	
		}		
		
		
		$uri = (is_string($url)) ? new Uri($url) :new Uri( \frdl\webfan\App::God(false)->unparse_url($p) ) ;
	
		
		$uri->withQuery($query);
		$uri->withPath($p['path']);	
      //  $uri->withHost($host);	
		$uri->withHost($p['host']);	
        $uri->withPort($p['port']);		
        $uri->withScheme($p['scheme']);	
		
        $forIp = ((isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
		//Request($uri = null, string $method = null, $body = 'php://temp', array $headers = [])
		
		$input = file_get_contents('php://input');
		
	//	print_r($method);
		 parse_str($input, $parsedBody);	
		 $json = json_decode($input);
	//	 print_r($parsedBody);	
		
	  $stream = new \Zend\Diactoros\Stream('php://memory', 'wb+');		
		
   if('POST' === $method || 'PUT' === $method){
	  
	   if( is_array((array)$json) ){			    
		  $stream->write($input);
	 }elseif( is_array($parsedBody) ){		    
		 // $stream->write(http_build_query($_POST));
	     $stream->write($input);
	 }else{
		     $stream->write($input);
	   }
  }			
		
		
		 $REQUEST = (new Request(
			 $uri,
			 $method,
		     $stream,
			 $headers
		 ))//->withHeader('Host', $host)
		//	->withHeader('X-Frdlweb-Proxy', '"{void}.webfan3.de"')
			 ->withHeader('X-Frdlweb-Proxy-For-Host', $host)
			 ->withHeader('X-Forwarded-For', $forIp)
		//	->withoutHeader('x-accel-internal') 
		//	->withoutHeader('x-real-ip') 
		//    ->withoutHeader('x-accel-internal') 
		//    ->withoutHeader('x-accel-internal') 	
			  ->withMethod($method)	
			  ->withBody($stream)
			 ;

		foreach($headers as $k => $v){
			 $REQUEST = $REQUEST ->withHeader($k, $v);
		}
		
		

		
		//multipart/form-data
	
		if('POST' === $method || 'PUT' === $method){
			 if( 0<count($_FILES) ){
				$REQUEST = $REQUEST ->withHeader('Content-type', 'multipart/form-data');
			 }elseif( null!== $json && (is_array($json) ||is_object($json))){			    
		        $REQUEST = $REQUEST ->withHeader('Content-type', 'application/json');
	         }elseif( is_array($parsedBody) ){
				$REQUEST = $REQUEST ->withHeader('Content-type', 'application/x-www-form-urlencoded');
			 }else{
				 $REQUEST = $REQUEST ->withHeader('Content-type', 'application/x-www-form-urlencoded');
			 }
		}
		/*	
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' ===  $_SERVER['HTTP_X_REQUESTED_WITH']){
				$REQUEST = $REQUEST ->withHeader('X-Requested-With', 'XMLHttpRequest');
			    $REQUEST = $REQUEST ->withHeader('Orign',$_SERVER['HTTP_ORIGIN']);
		}
		*/
	//if('POST' === $method || 'PUT' === $method){	
	//	$REQUEST = $REQUEST ->withHeader('Content-type', $headers['Content-Type']);
	//}
	//	die($headers['Content-Type']);
		
		return $REQUEST;
	}
	
	public function createProxy(        
										 $reverse_host = null,
		                                 $reverse_protocol = null,
										 $reverse_uri = null,
										 $host = null,
										 $method = null,
		                                 array $config = ['http_errors' => true],
							             $serverVars = null,
							             $ClassResponse = null){
		
		if(null===$reverse_host){
	      $reverse_host = '0.webfan.de';	
			// 	  $reverse_host = 'webfan3.de';	
		}
				
		if(null===$serverVars){
		  $serverVars = $_SERVER;	
		}			
		if(null===$ClassResponse){
		  $ClassResponse ='\\'.trim(__NAMESPACE__, '\\ ').'\\'.'MyResponse';	
		}			
		if(null===$reverse_protocol){
		  $reverse_protocol = 'https';	
		}
		
		if(null===$reverse_uri){
		  $reverse_uri = $serverVars['REQUEST_URI'];	
		}		
		
		
		
		if(null===$host){
		  $host = (!isset($serverVars['SERVER_NAME']) || $serverVars['SERVER_NAME'] !==$serverVars['HTTP_HOST']) ? $serverVars['HTTP_HOST'] : $reverse_host;	
		}
		if(null===$method){
		  $method = $serverVars['REQUEST_METHOD'];
		}	
		
		

		
		 $url = rtrim($reverse_protocol, ':// ').'://'.$reverse_host.''.$reverse_uri;	
	/*	
		// die($url);
	 	// $url = $reverse_protocol.'://'.$reverse_host.''.$reverse_uri;	
		 $headers= \frdl\webfan\App::God(false)->{'parseHeaders'}($serverVars);
		
		 $input = file_get_contents('php://input');
		 parse_str($input, $parsedBody);
		
		 print_r($headers);
		 print_r($input);
		 print_r($parsedBody);
		
		 
         $stream = new \Zend\Diactoros\Stream('php://memory', 'wb+');
		
		
		 if('POST' === $method || 'PUT' === $method){
              $stream->write(http_build_query($_POST));
	  	 }else{
			  $stream->write('');
		 }
		*/
		
		 $request =	$this-> createServerRequest( $host, $url, $method, $_GET/*$query$Params*/,  \frdl\webfan\App::God(false)->{'parseHeaders'}($serverVars)/* $headers*/,
												$_COOKIE, $_FILES, $serverVars//,
												//'php://input'
												//$_POST
												//stream_get_contents('php://input')
												//rawurlencode($_POST)
											   );
		
         $guzzle = new Client(array_merge(['http_errors' => true], $config));
         $adapter = new GuzzleAdapter($guzzle);	
         $proxy = new \webfan\hps\Client\Proxy($adapter, $request->getUri());

		
		
     return $proxy
		 	 ->forward($request)
    	 ->filter(new RemoveEncodingFilter())
		 
	    ->filter(function ($request, $response, $next) use($host, $ClassResponse, $method) {
	         $request = $request->withHeader('X-Frdlweb-Proxy-For-Host', $host);
			 $request = $request->withHeader('X-Frdlweb-Proxy', '"'.$host.'.{void}.webfan3.de"');
			
			// if(isset($_SERVER['HTTP_CONTENT_TYPE'])){
			//	$request = $request->withHeader('Content-Type', $_SERVER['HTTP_CONTENT_TYPE']); 
		//	 }
		//		 $contentType = $request->getHeader('content-type');
			//if('domainundhomepagespeicher.webfan.de'===$host){
		//	$location = $request->getHeader('Location');
		  //  print_r($location);
		//		die();
		//	}
				  //  if('Location' === $n){	
					//	 $MyResponse = $MyResponse->withHeader( $n,  $v  );
					//	 header('Location: '.$v);
					//	 die($v);
					//}
			
			
			 $forIp = ((isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
			 $request = $request->withHeader('X-Forwarded-For', $forIp);
		//	 $request = $request->withMethod($method);
		//	die($method);
             $response = $next($request, $response);
			
		//	if('domainundhomepagespeicher.webfan.de'===$host){
		//	$location = $response->getHeader('location');
			 //   print_r($location);
			//		die();
		//	}
			
		     $MyResponse = new $ClassResponse($response);
 foreach($MyResponse->getHeaders() as $n => $v){

	// if('Location'===$n){
	    //        print_r($location);
		//		die();

	// }
	 
	 
				   break;
	
		 if('Set-Cookie' === $n){					
					//   print_r($hv);  
			         $MyResponse = $MyResponse->withHeader( $n,  $v, true  );
			 
			 
			 
					 $RequestCookies = [];
					 
					 if(!is_array($v)){
						 $v = [$v];
					 }
				     foreach( $v as $i => $hv){ 
					   $CookieObject = ( function_exists('\http_parse_cookie')) 
					    ? \http_parse_cookie( trim($hv), 0 /* ['comment'] */) 
					    : new \webfan\hps\Parse\Cookie(trim($hv), 0 /* ['comment'] */);
					     $cookies = $CookieObject->cookies;
					
				      	foreach($cookies as $cookieName => $cookieValue){
						   $RequestCookies[$cookieName] = [
							'expires' => $CookieObject->expires,
							'value' => $cookieValue,
							'domain' => $CookieObject->domain,
							'path' => $CookieObject->path,
							'secure' => (bool)$CookieObject->secure,
						 ];
						
						
					  }					   
				    }	 
					 
						  foreach($RequestCookies as $name => $Cookie){
							 //  print_r($name);  
							  $time = time() + 2 * 60 * 60;
							  extract($Cookie);
							  $cs = "$name=$value; expires=$expires; domain=$domain; path=$path; $secure";
							  $MyResponse = $MyResponse->withHeader('Set-Cookie', $cs, false );
							  						
							  setcookie($name, urldecode($value), 
									  max(intval(strtotime($expires)), $time), 
									  $path,
									  $domain,
									  $secure
							  ); 
						  }
			
   }elseif(is_array($v)){
				   foreach( $v as $i => $hv){
				     $MyResponse = $MyResponse->withHeader( $n,  $hv, false);
				   }
  }elseif(is_string($v)){
				 //  if('Content-Type' ===$n)print_r($n.': '.print_r($v,true).'<br />');
				   $MyResponse = $MyResponse->withHeader( $n,  $v, true  );
			    //  header( $n.': '.  $v );			
	}

 }//foreach    		
		 return $MyResponse;
 })
		
	
	  ;		
	}
}
	
