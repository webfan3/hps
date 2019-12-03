<?php
/**
*
*
*          Validate extends/based on....:
*
*
*  class valFormats
*  by T. Wehowski http://www.webfan.de
*  License: Do What The Fuck You Want To Public License, some funcs
*           by users from stackoverflow or php.net
*
*  Version: 5.2.4
* 
*  Change Log:
*    - fixed isint
*    - static methods changed to non-static, fixed
*    - enables to call all methods as static (Backward compatibillity)
*    - add and overwrite validation rules dynamically
*    - saved file as utf-8
*  
*    - added is($in, 'mime', true, &$FormattedOutputArray)
* 
* Test / Example :
*<?php
*
*require 'valFormats.php';
*
*webdof\valFormats::create() 
*      -> test()
* ;	  
*
* 
* 
*  This class is a collection of the following methods:
* 
*  @Input - string [,options -boolean]
*  
*::class-Methods.
*  - create @returns new instance of self
* 
*::validation-Methods:  => @returns MIXED :(or) FALSE
*  - isFilename   ? ^[a-zA-Z0-9\.-_ ]+$ =>  true : false
*  - isint ? integer => true : false
*  - isurl ? url => ARRAY : false
*  - ismail ? email => true : false
*  - ismd5 ? md5 => true : false
*  - issha1 ? sha1 => true : false
*  - isUUID ? UUID => true : false
*  - isUUIDVersion ? UUID => UUID version : false
*  - isCSSPositionLength ? CSS positioning => true : false
*  - isCSSPositionLengthColor ? CSS positioning or color => true : false
*  - isCSSColor ? CSS color value => true : false
*  - isCSSTextAlign ? CSS text-align value ( left|center|right|justify )  => true : false
*  - isCSSVerticalAlign ? CSS vertical-align value ( top|middle|bottom|baseline|sub|super|text-top|text-bottom )  => true : false
*  - isCSSPosition ? CSS position value ( static|relative|absolute|fixed )  => true : false
*  - isOID ? OID (like "1.3.6.1.4.1.37553.8") => true : false
*  - isbase64 ? base64 encoded => true : false
*  - valAdress ?    Straßenname 123
*                || Straßenname 1a
*                || Straßenname 2-b
*                || Müster-/GÄsse 123/b
*                || 2.nd Street 99d        => true : false
*  - germanNameTitle  ? /^[\w\sÄÖÜäöüß,\)\(\.\-]+$/      => true : false
*  - deppenS ? name ends with "s"   =>   returns "s"  : ""
*  - valVersion ? IP => false : ip version
* 
*::formatting-Methods
*  - ip2long  =>  IP to INT/LONGINT
*                ip6 : http://stackoverflow.com/questions/18276757/php-convert-ipv6-to-number
*                ip4 : http://php.net/manual/de/function.ip2long.php
*
*                list(, $ip) = unpack('l',pack('l',ip2long('200.200.200.200')));
*
*               $ip  = 'fe80:0:0:0:202:b3ff:fe1e:8329';
*               $dec = valFormats::ip2long_v6($ip);
*               $ip2 = valFormats::long2ip_v6($dec);
*
*              // $ip  = fe80:0:0:0:202:b3ff:fe1e:8329
*              // $dec = 338288524927261089654163772891438416681
*              // $ip2 = fe80::202:b3ff:fe1e:8329
*  - long2ip_v6 => LONGINT  TO IPv6
*  - fromCamelCaseToWhiteSpace => http://stackoverflow.com/questions/4519739/split-camelcase-word-into-words-with-php-preg-match-regular-expression
*
* 
 *   Bugs/ToDo:
 *    - long2ip_v6
 * 
*   @requires php >=5.3
*   @license  1.3.6.1.4.1.37553.8.1.8.4.4 http://look-up.webfan.de/webfan-do-what-the-fuck-you-want-to-public-license
*   @source   http://interface.api.webfan.de/v1/public/software/class/frdl/webdof.valFormats/source.php
*/
//namespace webdof;
namespace webfan\hps\Format;

class Validate
{

 const MODE_VALIDATE = 'validate';
 const MODE_FORMAT = 'format';

 protected $in;
 protected $valid;
 protected $out;
 
 protected $rules = [];
 
 protected $deprecated;
 protected static $creators = ['c','g','create'];

 protected $mode = null;

 function __construct($defaults = true, $defaultAddons = true){
    $this->clear();
	
 	$this->deprecated = array(
        'valAdress' => '_isaddress',
        'germanNameTitle' => '_isname',        
        'valVersion' => '_isip',          
	//	'ip2long' => '_ip2long',      
	//	'ip2long_v4' => '_ip2long_v4',              
	//	'ip2long_v6' => '_ip2long_v6',         
	//	'long2ip_v6' => '_long2ip_v6',
        
        
		'fromCamelCaseToWhiteSpace' => '_camelcase2whitespace'
    );
	
	
	 if(true === $defaults)$this->defaults();	
	 if(true === $defaultAddons)$this->defaultAddons();	 	 
 } 

 public function defaultAddons(){
 	/**
	 * some addons...
	 *  - private enterprise number
	 *  - WEID enabled OID
	 *  - german method alias...
	 */

	 $this->addRule('oid.pen', function($in){
	 	   $tok = '1.3.6.1.4.1';
		   $tl = strlen($tok);
		   $l = strlen($in);
	 	   $r = self::is($in,'oid'); 
	 	   return (false !== $r && $tok === substr($in,0,$tl) && $l > $tl) ? true : false;
	 });
	 
	 $this->addRule('oid.weid', function($in) {
	 	   $tok = '1.3.6.1.4.1.37553.8';
	 	   $r = self::is($in,'oid'); 
	 	   return (false !== $r && $tok === substr($in,0,strlen($tok))) ? true : false;
	 });	
	 
	 $this->addRule('impolite', function($in){
	 	 return (preg_match("/porn|fucker|sex|asshole/i", $in)) ? true : false;
	 });	
	 
	 
	 /**
	  * german aliasis
	  */
	 $this->addRule('ungerade', function($in) {
	 	   return self::create()->is($in, 'odd');
	 });	 	 

	 $this->addRule('gerade', function($in){
	 	   return self::create()->is($in, 'even');
	 });
	 

	 $this->addRule('primzahl', function($in) {
	 	   return self::create()->is($in, 'prime');
	 });	
	 
	 return $this;		 		 	
 }
 
 
 
 public function defaults(){
	
	 $this->addRule('mime', function($in){
	 	 preg_match("/^(?<mime>(?<type>[a-z][a-z0-9\-]+)\/(?<subtype>[a-z][a-z0-9\-]+|vnd)(\.(?<vendor>[a-z][a-z0-9\-]+))?(\.(?<typegroup>[a-z][a-z0-9\-]+))?(\.(?<complextype>[a-z][a-z0-9\-]+))?(\+(?<format>[a-z][a-z0-9\-]+))?(;([\s]+)?(?<params>[A-Za-z0-9\=\-\.\,\;\s]+)+)?)$/", 
             $in, $mimeType, 0);
        if(isset($mimeType['params'])){
	      $mimeType['params']=preg_replace("/\s/", "", $mimeType['params']);
	      $mimeType['params']=str_replace(array(",", ';'), array('&',"&"), $mimeType['params']);
	      parse_str($mimeType['params'], $mimeType['params']);
        }	
        foreach($mimeType as $key => $val){
	       if(is_numeric($key))unset($mimeType[$key]);
         }
         
         return (is_array($mimeType) && 0 < count($mimeType) ) ? $mimeType : false;
	 });		
	
	 
	  $this->addRule('true', function($val, $return_null=false){
               $boolval = ( is_string($val) ? \filter_var($val, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE) : (bool) $val );
               return ( $boolval===null && !$return_null ? false : $boolval );
      });		 
	 
	
	 
	 
	 
	 $this->addRule('float', function($in){
	 	  return (preg_match("/^[0-9]{1,}\.[0-9]{1,}$/", $in)) ? true : false;
	 });		
	 
	 $this->addRule('uuid.timebased', function($in){
	 	   return (intval(self::is($in,'uuidversion')) === 1) ? true : false;
	 });		
	 $this->addRule('uuid.random', function($in){
	 	   return (intval(self::is($in,'uuidversion')) === 4) ? true : false;
	 });		
	 $this->addRule('uuid.namebased.md5', function($in){
	 	   return (intval(self::is($in,'uuidversion')) === 3) ? true : false;
	 });		
	 $this->addRule('uuid.namebased.sha1', function($in){
	 	   return (intval(self::is($in,'uuidversion')) === 5) ? true : false;
	 });		   
	 $this->addRule('uuid.DCE', function($in){
	 	   return (intval(self::is($in,'uuidversion')) === 2) ? true : false;
	 });		   
	 
	 
	 
	 
	 $this->addRule('integer.int8_t', function($in){
	 	   if(!self::is($in,'int')) return false; 
		   if($in < -128 || $in > 127)return false;
	 	   return true;
	 });	
	 $this->addRule('byte', function($in){
	 	   return self::create()->is($in, 'integer.int8_t');
	 });	
	 
	 $this->addRule('integer.uint8_t', function($in){
	 	   if(!self::is($in,'int')) return false; 
		   if($in < 0 || $in > 255)return false;
	 	   return true;
	 });	
	 $this->addRule('byte.unsigned', function($in){
	 	   return self::create()->is($in, 'integer.uint8_t');
	 });	
	 
		  
		  
	 
	 
	 $this->addRule('integer.int16_t', function($in){
	 	   if(!self::is($in,'int') )return false; 
		   if($in < -32768 || $in > 32767)return false;
	 	   return true;
	 });	
	 $this->addRule('word', function($in){
	 	   return self::create()->is($in, 'integer.int16_t');
	 });	
	 
	 $this->addRule('integer.uint16_t', function($in){
	 	   if(!self::is($in,'int')) return false; 
		   if($in < 0 || $in > 65535)return false;
	 	   return true;
	 });	
	 $this->addRule('word.unsigned', function($in){
	 	   return self::create()->is($in, 'integer.uint16_t');
	 });	
	 		  
		  
		 
	 
	 $this->addRule('integer.int32_t', function($in){
	 	   if(!self::is($in,'int') )return false; 
		   if($in < -2147483648 || $in > 2147483647)return false;
	 	   return true;
	 });	
	 $this->addRule('double', function($in){
	 	   return self::create()->is($in, 'integer.int32_t');
	 });	
	 
	 $this->addRule('integer.uint32_t', function($in){
	 	   if(!self::is($in,'int')) return false; 
		   if($in < 0 || $in > 4294967295)return false;
	 	   return true;
	 });	
	 $this->addRule('double.unsigned', function($in){
	 	   return self::create()->is($in, 'integer.uint32_t');
	 });	
	 		  
		 
	 
	 $this->addRule('integer.int64_t', function($in){
	 	   if(!self::is($in,'int') )return false; 
		   if($in < -9223372036854775808 || $in > 9223372036854775807)return false;
	 	   return true;
	 });	
	 $this->addRule('long', function($in){
	 	   return self::create()->is($in, 'integer.int64_t');
	 });	
	 
	 $this->addRule('integer.uint64_t', function($in){
	 	   if(!self::is($in,'int')) return false; 
		   if($in < 0 || $in > 18446744073709551615)return false;
	 	   return true;
	 });	
	 $this->addRule('long.unsigned', function($in){
	 	   return self::create()->is($in, 'integer.uint64_t');
	 });	
	 		  
			  
			  
	 $this->addRule('odd', function($in){
	 	   return (false !== self::is($in,'int') && $in % 2 !== 0) ? true : false;
	 });		
	 $this->addRule('even', function($in){
	 	  return (false !==self::is($in,'int') && $in % 2 === 0) ? true : false;
	 });			 
			  
			  
	 $this->addRule('prime', function($in){
	  if(!self::is($in,'int')) return false; 
	  $in = intval($in);
      if($in <= 1)return false;
      if($in === 2)return true;
      if($in % 2 === 0)return false;
       for($i = 3; $i <= ceil(sqrt($in)); $i = $i + 2) {
           if($in % $i === 0)return false;
        }
      return true;
	 });
	 
	 	 			  	
	return $this;
  }

 /**
  * Mock method, comment out when  tested !?
  */
 public function test(){
 	 $cli = ('cli' === strtolower(\PHP_SAPI)) ? true : false;
 	if(true !== $cli)echo '<h1>Testing '.__CLASS__.'</h1>';
     $ref = new \ReflectionClass(\get_class($this));
     $methods = $ref->getMethods();	
	 ksort($methods);
	 
  $highlight_num = function($file)
   {
   	if(file_exists($file)){
      $lines = implode(range(1, count(file($file))), '<br />');
      $content = highlight_file($file, true);
	}else{
	  $file = ltrim($file, '<?php ');
	  $file = "<?php\r\n".$file;	
      $lines = implode(range(1, count(preg_split("/[\r\n]+/",$file))), '<br />');
      $content = highlight_string($file, true);		
	}
	
  echo '
    <style type="text/css">
        .num {
        float: left;
        color: gray;
        font-size: 13px;   
        font-family: monospace;
        text-align: right;
        margin-right: 6pt;
        padding-right: 6pt;
        border-right: 1px solid gray;}

        body {margin: 0px; margin-left: 5px;}
        td {vertical-align: top;}
        code {white-space: nowrap;}
    </style>';
   
   
   
    echo "<table><tr><td class=\"num\">\n$lines\n</td><td>\n$content\n</td></tr></table>"; 
   };

	
 	if(true !== $cli)echo '<pre>';
	echo "The following example code\n- adds rules dynamically (NOTE: The added rule overwrites the built in method if exists),\r\n- and validate some tests:";	
	//http://interface.api.webfan.de/v1/public/software/class/frdl/webdof.valFormats/source.php
$code = <<<EO
\$TEST =  valFormats::create(true,true)
 ->addRule('url.API-D', '(http|https)\:\/\/interface\.api\.webfan\.de\/v([0-9]+)\/(public|i[0-9]+)\/software\/class\/frdl\/([\w\.]+)\/source\.php')
 ->addRule('me', "Jon Doe", true)
 ->addRule('me.mention', "/Jon Doe/i", false)
 ->addRule('url.www.phpclasses.org', function(\$in){
         	 \$r = \valFormats::is(\$in,'url'); return (false !== \$r && isset(\$r['host']) && \$r['host'] === 'www.phpclasses.org') ? true : false;
    });	

echo print_r(\$TEST->is('A string with JON doe' , 'me'), true)."\\r\\n";             //false
echo print_r(\$TEST->is('A string with JON doe' , 'me.mention'), true)."\\r\\n";     //true
echo print_r(\$TEST->is('Jon Doe' , 'me'), true);                                   //true
EO;

    echo $highlight_num($code);
	echo "Outputs:\r\n";	
	eval($code);

    echo "\r\n\r\n";

    $TEST = &$this;
    		
	echo "VALIDATE METHODS (summary):\r\n";	
    echo print_r($TEST->formats(),true);
	
		
    echo "\r\n\r\n";
    echo "\r\n\r\n";
	echo "FORMATTING METHODS:\r\n";		
	echo __CLASS__."::from2to\t\tFrom\t\t\t\tTo\t\t\t\tMethod\r\n";
		foreach($methods as $index => $m){
		    $f = explode('2',$m->name, 2);
			if('is' !== substr($m->name,1,2) && 2 === count($f)  ){
         		$method = $m->name;	
				if(strlen($f[0] <= 7))$f[0] .= "\t\t\t\t";	
				if(strlen($f[1] <= 7))$f[1] .= "\t\t\t\t";	
			    echo "\t\t\t\t".ltrim($f[0],'_ ')."\t".$f[1]."\t".$m->name."\r\n";		
			}
       }	
	echo "\r\n\r\n";		
    echo "\r\n\r\n";			
	echo "TESTING SOME STRINGS:\r\n";	
	echo "\r\n\r\n";			
 	$str = array(
 	  'application/vnd.frdl.webfan.project+json; charset=UTF-8, version=0.9',
 	  'Any U&$tring% with $ome noise: *+~#\'"hello world"',
	   'http://www.phpclasses.org/package/8412-PHP-Validate-string-values-in-different-formats.html',
	   'http://interface.api.webfan.de/v1/public/software/class/frdl/webdof.valFormats/source.php',
	   'php.support@webfan.de',
	   'Till Wehowski',
	   'Herr Dr. Otto Mueller (MdB)',
	   'Dr. Otto Mueller, jr.',
	   'Frau Emma Meyer',
	   'Wattenscheiderstraße 59',
	   '1.3.6.1.4.1.0',
	   '1.3.6.1.4.1.37553.8.1.8.8',
	   '8e1441db-9bbd-4d29-ba06-3d797b63b5b6',
	   'ffffff9a-1e7d-5547-8ede-5aee3c939a37',
	   'ffffff9a-1e7d-9547-8ede-5aee3c939a37',
	   'd41d8cd98f00b204e9800998ecf8427e',
	   'da39a3ee5e6b4b0d3255bfef95601890afd80709',
	   '16px',
	   '9em',
	   'blue',
	   'center',
	   'middle',
	   'fixed',
	   '127.0.0.1',
	   '93.184.216.34',
	   '2001:0db8:0000:08d3:0000:8a2e:0070:7344',
	   base64_encode(mt_rand(10000,999999).$_SERVER['SERVER_NAME'].'test1230x00'),
	   base64_encode('My String 1234567890'),
      '~test1-123.zip' ,
	   97,
	   3.5,
	   65535,
	   -2147483648,
	   -129,
	   -128,
	   '10000000097',
	   10000000098,   	   
	  'A sexy sentence by a porn spammer asshole...',
		   
	   'Otto Müller',	   //valid
	   'dölfkltgß5   ö4ü359',  //INVALID
	   'Müller, Otto',                      //valid
	   'z435 j4894  rk ftz',   //INVALID
	   '1234 Müller',          //INVALID
	   
   );
    foreach($str as $num => $s){
	    echo 'Test '.((true !== $cli) ? '<strong>STRICT</strong>' : 'STRICT').":\t\t\$TEST->is('".((true !== $cli) ? '<strong>'.$s.'</strong>' : $s)."');\r\n";
	    $r = $TEST->is($s);
	     foreach($r as $format => $result){
	    	   if(false === $result || empty($result)){
	    	   	   unset($r[$format]);
			   }else{
			   	  if(true===$result)$result =  (true !== $cli) ? '<strong style="color:green;">OK</strong>' : 'OK';
			   	  if(is_array($result))$result = (true !== $cli) ? '<strong style="color:green;">'.print_r($result,true).'</strong>' : print_r($result,true); 
			   	  $r[$format] = $result;
			   }
		 }
	    if(is_array($r) && 0 === count($r)){
	   	  $r = false;
	    }	   

   	   $nvstr = (true !== $cli) ? '<strong style="color:red;"> - not validated successfully</strong>' : '- not validated successfully';		   	
   	   echo ((is_array($r)) ? print_r($r,true) : $nvstr);
        echo "\r\n";
		
	 if(false === $r){	
	    echo 'Try '.((true !== $cli) ? '<strong>NONE-STRICT</strong>' : 'NONE-STRICT').":\t\t\$TEST->is('".((true !== $cli) ? '<strong>'.$s.'</strong>' : $s)."', null, false);\r\n";
	    $r = $TEST->is($s, null, false);
	     foreach($r as $format => $result){
	    	   if(false === $result || empty($result)){
	    	    	$r[$format] = false;
	    	   	   unset($r[$format]);
			   }else{
			   	  if(true===$result)$result =  (true !== $cli) ? '<strong style="color:green;">OK</strong>' : 'OK';
			   	  if(is_array($result))$result = (true !== $cli) ? '<strong style="color:green;">'.print_r($result,true).'</strong>' : print_r($result,true); 
			   	  $r[$format] = $result;
			   }
		 }
	    if(is_array($r) && 0 === count($r)){
	   	  $r = false;
	    }	   

   	   $nvstr = (true !== $cli) ? '<strong style="color:red;"> - not validated successfully</strong>' : '- not validated successfully';		   	
   	   echo ((is_array($r)) ? print_r($r,true) : $nvstr);
        echo "\r\n\r\n";		
      }
      }

 	echo "\r\n\r\n";	  
   	echo "TESTING MISC.:\r\n";	
	
$code = <<<EO
 echo print_r(\$TEST->is('8e1441db-9bbd-4d29-ba06-3d797b63b5b6','uuidversion',true), true)."\r\n";
 echo print_r(\$TEST->is('8e1441db-9bbd-zd29-ba06-3d797b63b5b6','uuidversion',true), true)."\r\n";
 echo print_r(\$TEST->is('8e1441db-9bbd-zd29-ba06-3d797b63b5b6','uuidversion',false), true)."\r\n";
EO;
    echo $highlight_num($code);
	echo "Outputs:\r\n";	
	eval($code);		
	
	
$code = <<<EO
 echo print_r(\$TEST->ip2long('fe80:0:0:0:202:b3ff:fe1e:8329'), true);
 echo "\r\n";	 
 echo print_r(\$TEST->ip2long('93.184.216.34'), true);
EO;

    echo $highlight_num($code);
	echo "Outputs:\r\n";	
	eval($code);	
	
	
$code = <<<EO
 echo print_r(\$TEST->long2ip_v6('338288524927261089654163772891438416681'), true);
EO;
    echo $highlight_num($code);
	echo "Outputs:\r\n";	
	eval($code);	
	
	
$code = <<<EO
 \$teststring = 'MyCamelCasedString';
 echo \$TEST->camelcase2whitespace(\$teststring);
 echo "\r\n";	 
 echo \$TEST->camelcase2whitespace(\$teststring, "_");
EO;
    echo $highlight_num($code);
	echo "Outputs:\r\n";	
	eval($code);	
	
	
$code = <<<EO
 \$teststring = 'My Camel-Cased Test_string';
 echo \$TEST->str2CamelCase(\$teststring)."\r\n";
 echo \$TEST->string2CamelCase('font-size');
EO;
    echo $highlight_num($code);
	echo "Outputs:\r\n";	
	eval($code);	
			
		
	echo "\r\n\r\n";		
    echo "\r\n\r\n";			
	if(true !== $cli)echo "<h1>SOURCECODE</h1>\r\n";   
    if(true !== $cli)echo '</pre>';
   
    if(true !== $cli)$highlight_num(__FILE__);
   
	return $this;
 }


 public function formats(){
 	 $formats = array();
     $ref = new \ReflectionClass(get_class($this));
     $methods = $ref->getMethods();	 	
		foreach($methods as $index => $m){
			if('is' === substr($m->name,1,2) && 'is' !== $m->name  && '_is' !== $m->name  ){
				$format = substr($m->name,3,strlen($m->name));
			    $formats[$format] = 'Built in';
				$formats[$format] = ('cli' !== strtolower(\PHP_SAPI)) ? '<i>built in</i>' : 'built in';		
			}
        }		
        foreach($this->rules as $format => $regex){
	         $formats[$format] = ('cli' !== strtolower(\PHP_SAPI)) ? '<strong>Dynamically added</strong>' : 'dynamically added';		
	    }		 
	
	ksort($formats);
	return $formats;	 
 }
 
 	
 
 protected function _is($in, $format = null){
 	if(is_callable($this->rules[$format])){
 		return call_user_func($this->rules[$format], $in);
 	}else{
      return (preg_match($this->rules[$format], $in)) ? true : false;
   }
 }
 

 public function is($in, $format = null, $strict = true){
   $r = false;
   
   $Obj = (is_object($this)) ? $this :  self::create();
   
   try{
  	if(is_string($format)){
 		if(isset($Obj->rules[$format])){
  			$r = $Obj->_is($in, $format);
 		}else{
 	    	$method = '_is'.strtolower($format);
		    $r = $Obj->{$method}($in, $strict);
		}
 	}elseif(is_array($format)){
 		$r = array();
		foreach($format as $pos => $f){
			$method = '_is'.strtolower($f);
			$r[$f] = $Obj->{$method}($in, $strict);
		}
    }elseif(null === $format){
    	$ref = new \ReflectionClass(get_class($Obj));
		$methods = $ref->getMethods();
 		$r = array();
		
		foreach($Obj->rules as $format => $regex){
			$r[$format] = $Obj->_is($in, $format);	
		}
		
		foreach($methods as $index => $m){
			if('is' === substr($m->name,1,2) && 'is' !== $m->name  && '_is' !== $m->name ){
         		$method = '_is'.substr($m->name,3,strlen($m->name));
			    $r[substr($m->name,3,strlen($m->name))] = $Obj->{$method}($in, $strict);				
			}
       }
    }
	
	
   if(is_array($r) && 0 === count($r))$r = false;	
	
   }catch(\Exception $e){
   	 throw new \Exception($e->getMessage());
   }
  

  
  return $r;
 }

 
 public function addRule($format, $regex, $addLimiters = true){
 	if(!is_callable($regex) && true === $addLimiters){
 	  $regex = ltrim($regex, '/^ ');
 	  $regex = rtrim($regex, '$/ ');	
	  $regex = '/^'.$regex.'$/';
	}
 	$this->rules[$format] = $regex;
	return $this;
 }
 
 public function removeRule($format){
 	if(isset($this->rules[$format]))unset($this->rules[$format]);
	return $this;
 }
 
 public function __get($name){
 	return (property_exists($this, $name)) ? $this->{$name} : null;
 }

 public function clear(){
 	$this->in = null;
	$this->format = null;
	$this->valid = false;
	$this->out = '';
	$this->mode = null;
	$this->from = null;
	$this->to = null;
	return $this;
 }

 public static function __callStatic($name, $arguments)
 {
     if(in_array($name,self::$creators))return new self();
	 
 	 try{
    	  return call_user_func_array(array(new self,$name),$arguments);
		}catch(\Exeption $e){
			 $trace = debug_backtrace();
		     trigger_error($e->getMesage().' '.$trace[0]['file'].' '.$trace[0]['line'], \E_USER_ERROR);
			 return false;
		}
 }
	


 public function __call($name, $arguments)
 {
    if(in_array($name,self::$creators))return new self();
	
    $func = $name;
	$this->valid = false;
		
	//fixed old versions and deprecated method names (backwards compatibillity)
   if(isset($this->deprecated[$func])){
		trigger_error('Deprecated method call '.get_class($this).'::'.$func.', instead use: '.get_class($this).'::'.$this->deprecated[$func],  \E_USER_DEPRECATED);
	    $name = $this->deprecated[$func];	
	}
    	
	if(substr($name,0,1) !== '_'){		
        $name = '_'.$name;
	}
	$name = strtolower($name);
	
	$this->in = $arguments[0];
	$this->out = '';

    $f = explode('2',$func, 2);
	$this->mode = ('is' === substr($name,1,2)) ? self::MODE_VALIDATE : ((2 === count($f)) ? self::MODE_FORMAT : null);
	if(self::MODE_FORMAT === $this->mode){
		$this->from = ltrim($f[0],'_ ');
		$this->from = ('' === $this->from || 'str' === $this->from) ? 'string' : $this->from;
	    $this->to = $f[1];
		$this->format = $this->from;
		$name = '_'.$this->from.'2'.$this->to;
	}elseif(self::MODE_VALIDATE === $this->mode){
		$this->format = substr($name,3,strlen($name));
	}
	
	$call = array($this,$name);
	$args = $arguments;
    if(isset($this->rules[$this->format])){
	   $call = array($this,'_is');
	   $args = array($this->in, $this->format);		
	}


	if(!is_callable($call) ){
	    $trace = debug_backtrace();
		trigger_error('Unsupported method call '.get_class($this).'::'.$name.' in '.$trace[0]['file'].' '.$trace[0]['line'], \E_USER_ERROR);
		return false;
	}
	
    try{
    	 $result = call_user_func_array($call,$args);
		}catch(\Exeption $e){
			 $trace = debug_backtrace();
		     trigger_error($e->getMesage().' called in '.$trace[0]['file'].' '.$trace[0]['line'], \E_USER_ERROR);
			 return false;
		}
 	
	 $this->out = ($this->mode === self::MODE_FORMAT && !is_bool($result) ) ? $result 
	                      : (($this->mode === self::MODE_VALIDATE && false !== $result) ? $this->in : '');	
	 $this->valid = ( false !== $result) ? true :false;
	 $this->format = (self::MODE_FORMAT === $this->mode && true === $this->valid) ? $this->to : $this->format;				  
	 	
	return $result;	
 }

 public function deppenS($name)
    {
      if( strtolower(substr($name, -1, 1)) != 's')
        {
          return 's';
        }else{
              return '';
             }
    }

 protected function _isfullname(&$name, $strict = true /* allow html entities */, $entityConvert = true)
   {
    	
   	 $converted =\html_entity_decode( 
                   \htmlspecialchars_decode(
                       \htmlentities(
                             $name,
                             \ENT_QUOTES | \ENT_HTML5,
                             \mb_detect_encoding($name, 'auto'),
                             false
                          )
                          ,\ENT_QUOTES | \ENT_HTML5), 
                \ENT_QUOTES | \ENT_HTML5);
   	 
   	 if(false === $strict && true === $entityConvert)
   	   $name = $converted;
   	 
     if(preg_match("/^[A-ZÄÖÜ]([\wÄÖÜaöüßéè]+)(\.)?(\,\s|\s)[A-ZÄÖÜ]([\wÄÖÜaöüßéè\,\)(\s[\wÄÖÜaöüßéè\,\)s(\.\-]+){1,1}?$/", 
     (true === $strict)
        ? $name
        : $converted
                
     ))
    {
     return true;
    }else{
          return false;
       }  	
   }
   
	

 protected function _isname($name)
   {
   	   	 $converted =\html_entity_decode( 
                   \htmlspecialchars_decode(
                       \htmlentities(
                             $name,
                             \ENT_QUOTES | \ENT_HTML5,
                             \mb_detect_encoding($name, 'auto'),
                             false
                          )
                          ,\ENT_QUOTES | \ENT_HTML5), 
                \ENT_QUOTES | \ENT_HTML5);
   	 
   	 if(false === $strict && true === $entityConvert)
   	   $name = $converted;
   	 
     if(preg_match("/^[A-ZÄÖÜ][\A-Za-zÄÖÜaöüßéè\,\)\s(\.\-]+$/", 
     (true === $strict)
        ? $name
        : $converted
                
     ))
    {
     return true;
    }else{
          return false;
       }  	
       
   }


 protected function _isendingwiths($name)
    {
      if( strtolower(substr($name, -1, 1)) !== 's')
        {
          return false;
        }else{
              return true;
             }
    }



  //"^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{4})$"
  protected function _isbase64($str)
   {
     if (preg_match("/^(?:[A-Za-z0-9+]{4})*(?:[A-Za-z0-9+]{2}==|[A-Za-z0-9+]{3}=|[A-Za-z0-9+]{4})$/", trim($str))) {
        return TRUE;
     }
     return FALSE;
   }



  protected function _isint($str)
   {
   	 return (is_numeric($str) && preg_match("/^((\+|\-)?([0-9]{1,}))$/", $str)) ? true : false;
   }



  protected function _isurl($str)
   {
     $c = parse_url($str);
     if(is_array($c) && isset($c['scheme']) && !is_numeric($c['scheme'])){return $c;}else{return FALSE;}
   }

  protected function _ismail($str)
   {
      if (preg_match("/^([a-zA-Z0-9-])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", trim($str))) {
        return TRUE;
      }
     return FALSE;
   }

 protected function _ismd5($str)
   {
      return !empty($str) && preg_match('/^[a-f0-9]{32}$/', $str);
   }


 protected function _issha1($str)
   {
      return !empty($str) && preg_match('/^[a-f0-9]{40}$/', $str);
   }


 protected function _isuuid($in){
 	 return (!$this->_isuuidversion($in, true))?false:true;	 	
 }
 
 /**
 * returns UUID version or FALSE if no UUID format
 */
 protected function _isuuidversion($in, $strict = true)
   {

     if(false !== $strict)
      {
      	  $in = strtolower($in );
      	  $alphanums = "a-f";	
      }else{
             $alphanums = "a-z";	
           }
	 
	 if(!preg_match("/^[0-9$alphanums]{8}-[0-9$alphanums]{4}-(?<version>[0-9$alphanums]{1})[0-9$alphanums]{3}-[0-9$alphanums]{4}-[0-9$alphanums]{12}$/i",$in, $matches) )return false;

     $version = $matches['version'];
     if(false !== $strict && (empty($version) || !$this->_isint($version) || intval($version)<1 ||  intval($version)> 6))return false;
	
	return $version;
   }



  protected function _iscsspositionlength($str)
   {
      return !empty($str) && preg_match('/^auto$|^[+-]?[0-9]+\\.?([0-9]+)?(px|em|ex|%|in|cm|mm|pt|pc)$/', $str);
   }


  protected function _iscsspositionlengthcolor($str)
   {
      return !empty($str) && preg_match('/^auto|aqua|black|blue|fuchsia|gray|green|lime|maroon|navy|olive|orange|purple|red|silver|teal|white|yellow$|^[+-]?\#[A-Fa-f0-9]+\\.?([0-9]+)?(px|em|ex|%|in|cm|mm|pt|pc)$/', $str);
   }


  protected function _iscsscolor($str)
   {
      return !empty($str) && preg_match('/^auto|aqua|black|blue|fuchsia|gray|green|lime|maroon|navy|olive|orange|purple|red|silver|teal|white|yellow$|^\#[A-Fa-f0-9]{6}$/', $str);
   }

 protected function _iscsstextalign($str)
   {
      return !empty($str) && preg_match('/^left|center|right|justify$/', $str);
   }

 protected function _iscssverticalalign($str)
   {
      return !empty($str) && preg_match('/^top|middle|bottom|baseline|sub|super|text-top|text-bottom$/', $str);
   }
   
 protected function _iscssposition($str)
   {
      return !empty($str) && preg_match('/^static|relative|absolute|fixed$/', $str);
   }



  protected function _isaddress(&$adress, $strict = true /* allow html entities */, $entityConvert = true)
   {
    	 $converted =\html_entity_decode( 
                   \htmlspecialchars_decode(
                       \htmlentities(
                             $adress,
                             \ENT_QUOTES | \ENT_HTML5,
                             \mb_detect_encoding($adress, 'auto'),
                             false
                          )
                          ,\ENT_QUOTES | \ENT_HTML5), 
                \ENT_QUOTES | \ENT_HTML5);
   	 
   	 if(false === $strict && true === $entityConvert)
   	   $adress = $converted;
   	 
     if(preg_match("/^[a-zA-Z0-9äöüÄÖÜß\/\-\. ]+[\s]{1,}[0-9]+(|[a-z\/\-\.])+$/", 
     (true === $strict)
        ? $adress
        : $converted
                
     ))
    {
     return TRUE;
    }else{
          return FALSE;
       }
   }



  protected function _isoid($oid)
   {
    if(!preg_match("/^[0-9\.]+$/s",$oid))
      {
       return FALSE;
      }else{
           return TRUE;
           }
   }



 protected function _isfilename($str, $allowSpace = false){
 	if(true === $allowSpace)$str = preg_replace("/\s/", '~', $str);
 	return ((preg_match("/^[A-Za-z0-9\.\-\_\~]+$/", $str)  && preg_match("/[\.]/", $str) && preg_match("/[a-z]/", strtolower($str))) ? TRUE : FALSE); 
 } 
 
 
 protected function _isip($ip)
  {
     if($ip === \filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4))return 'ipv4';
	 if($ip === \filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6))return 'ipv6';
     return FALSE;
  }
  
  
  
  
 /**
  * END VALIDATE METHODS, BEGIN FORMATTING METHODS ...
  */
 

 
 /**
  * http://stackoverflow.com/questions/4519739/split-camelcase-word-into-words-with-php-preg-match-regular-expression
  * Converts camelCase string to have spaces between each.
  * @param $camelCaseString
  * @return string
  */
  protected function _camelcase2whitespace($camelCaseString, $space = " "){
  	    $re = '/(?<=[a-z])(?=[A-Z])/x';
        $a = preg_split($re, $camelCaseString);
        return join($a, $space);
  }
 
  protected function _string2camelcase($str, $split = "/[\s\-\.\_]/"){
  	 $a = preg_replace($split, " ", $str);
	 $a = ucwords($a);
  	  if (ctype_lower($str[0])) {
  	  	$a[0] = strtolower($a[0]);
  	  }
	 $a = preg_split($split, $a);
     return join($a,  "");
  }
 
 /**
  * IP Addresses...
  *  - php.net
  */
  protected function _ip2long($ip, $getVersion = TRUE)
  {
   $version = $this->_isip($ip);
   if($getVersion === FALSE && $version === FALSE)return FALSE;
   if($getVersion === FALSE && $version === 'ipv4')return $this->_ip2long_v4($ip);
   if($getVersion === FALSE && $version === 'ipv6')return $this->_ip2long_v6($ip);

   if($getVersion === TRUE && $version === FALSE)return array('version' => FALSE, 'int' => FALSE);
   if($getVersion === TRUE && $version === 'ipv4')return array('version' => $version, 'int' => $this->_ip2long_v4($ip));
   if($getVersion === TRUE && $version === 'ipv6')return array('version' => $version, 'int' => $this->_ip2long_v6($ip));

    return trigger_error('inalid argument getVersion in ipFormat::ip2long()!', \E_USER_ERROR);
  }




 protected function _ip2long_v4($ip)
  {
    list(, $result) = unpack('l',pack('l',ip2long($ip) )  );
    return $result;
  }



 protected function _ip2long_v6($ip) {
    $ip_n = inet_pton($ip);
    $bin = '';
    for ($bit = strlen($ip_n) - 1; $bit >= 0; $bit--) {
        $bin = sprintf('%08b', ord($ip_n[$bit])) . $bin;
    }

    if (function_exists('gmp_init')) {
        return gmp_strval(gmp_init($bin, 2), 10);
    } elseif (function_exists('bcadd')) {
        $dec = '0';
        for ($i = 0; $i < strlen($bin); $i++) {
            $dec = bcmul($dec, '2', 0);
            $dec = bcadd($dec, $bin[$i], 0);
        }
        return $dec;
    } else {
        trigger_error('GMP or BCMATH extension not installed!', \E_USER_ERROR);
    }
 }



 protected function _long2ip_v6($dec) {
 	$dec = intval($dec);
    if (function_exists('gmp_init')) {
        $bin = gmp_strval(gmp_init($dec, 10), 2);
    } elseif (function_exists('bcadd')) {
        $bin = '';
        do {
            $bin = bcmod($dec, '2') . $bin;
            $dec = bcdiv($dec, '2', 0);
        } while (bccomp($dec, '0'));
    } else {
        trigger_error('GMP or BCMATH extension not installed!', \E_USER_ERROR);
    }

    $bin = str_pad($bin, 128, '0', \STR_PAD_LEFT);
    $ip = array();
    for ($bit = 0; $bit <= 7; $bit++) {
        $bin_part = substr($bin, $bit * 16, 16);
        $ip[] = dechex(bindec($bin_part));
    }
    $ip = implode(':', $ip);
    return inet_ntop(inet_pton($ip));
 }
 
 
}
