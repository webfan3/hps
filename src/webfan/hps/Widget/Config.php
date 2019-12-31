<?php
declare(strict_types=1);
namespace webfan\hps\Widget;
/*!
MIT License

Copyright (c) 2019 Webfan Homepagesystem


https://github.com/webfan3/hps/tree/master/src/webfan/hps/Widget/Config.php
*/
use webfan\hps\Format\DataUri;

class Config
{
	protected $configUrl = null;
	protected $zipUrl = null;
	protected $xml = null;
	protected $contents = null;
	
	protected $favicon = null;
	
	
    public function __construct(string $configUrl = null, string $zipUrl=null){
		$this->configUrl = $configUrl;
		$this->zipUrl = $zipUrl;
		$this->loadConfig() ;
	}
	
	protected function loadConfig(){
		if(null===$this->xml && is_string($this->configUrl)){
			$this->xml = new \DOMDocument();
			$this->xml ->load($this->configUrl);
		}		
	}
	
	public function saveXML(){
		return $this->xml->saveXML();
	}
	public function saveHTML(){
		return $this->xml->saveHTML();
	}	
	public function setPreference($k, $v, $getRequestVars = true, $onlyEditables = true){
   //$root=$this->xml->documentElement;
	 $root=$this->xml;
		
	$preferences=$root->getElementsByTagName('preference');

	
	$p = [];
	foreach($preferences as $i=> $preference){
		$continue = false;
		foreach($preference->attributes as $j => $a){
		    if(true===$onlyEditables && $a->name==='readonly' && (true===$a->value || 'true'===$a->value) ){
				$continue =true;
				break;	
			}
			if($a->name==='name'){
			   $name = $a->value;	
			}
			
			
			if($a->name==='value'){
			   $value = $a->value;	
			}	
		}
		
		if(false!==$continue){
		  continue;	
		}
		
		if($name === $k || (true===$getRequestVars && $name === str_replace('.', '_', $k))){
			$preference->setAttribute('value', $v);
			break;
		}
	  }	
		

	}
	
	
	protected function path($path){
		
		if('http' === substr($path, 0, strlen('http') ) ){
		  return $path;	
		}elseif('http' === substr($this->configUrl, 0, strlen('http') ) ){
			$p = str_replace(basename($this->configUrl), '', $this->configUrl);
		  return $p.$path;	
		}else{
		   return dirname($this->configUrl) . \DIRECTORY_SEPARATOR . $path;
		}
	}
	
	public function getIcon(){
		if(null !== $this->favicon){
		  return $this->favicon;	
		}		
		
	  $root=$this->xml;		
	  $favs=$root->getElementsByTagName('icon');			
	  foreach($favs as $i=> $fav){
		    foreach($fav->attributes as $j => $a){
		         if($a->name==='src'){
					 //data:
					 $c = file_get_contents($this->path($a->value));
					 if('data:' === substr($c, 0, strlen('data:') ) ){
						 $this->favicon = $c;
					 }else{
			             $icon = new DataUri('image/x-icon',base64_encode($c), DataUri::ENCODING_BASE64);  
					     $this->favicon = ''.$icon;
				     }
					break; 
				 }
			}
		  break;
	  }
	
	  return $this->favicon;
	}
	
	public function getContents(){
		
		if(null !== $this->contents){
		  return $this->contents;	
		}
		
		$this->contents = 
		[
	          'css' => [],
		      'html' => [],
		      'javascript' => [],
		
		];
		
	  $root=$this->xml;		
	  $contents=$root->getElementsByTagName('content');		
	  foreach($contents as $i=> $content){	
		   $c = '';
		   $type = 'html'; 
		  foreach($content->attributes as $j => $a){
		   if($a->name==='src'){
			   if(file_exists($this->path($a->value)) || 'http' === substr($this->path($a->value), 0, strlen('http'))){				   				   
				   $c.= file_get_contents($this->path($a->value));					   
			   }
		   }elseif($a->name==='type'){
			   $t = explode('/', $a->value);
			   if(2 !== count($t) || 'text' !== $t[0] || !isset($this->contents[$t[1]]))continue;
			   $type =$t[1];
		   }				  
		  }
         foreach($content->childNodes as $child) {
           if ($child->nodeType == \XML_CDATA_SECTION_NODE) {
			 $c.= str_replace(array('<![CDATA[',']]>'), '',  $child->ownerDocument->saveXML( $child ));  
           }
         }																			
	   	$this->contents[$type][] = $c;																		
	  }
		
		return $this->contents;
	}
	
	
	public function getPreferences($onlyEditables = true){
    //$root=$this->xml->documentElement;
	$root=$this->xml;
		
	$preferences=$root->getElementsByTagName('preference');

	
	$p = [];
	foreach($preferences as $i=> $preference){
		$continue = false;
		foreach($preference->attributes as $j => $a){
		    if(true===$onlyEditables && $a->name==='readonly' && (true===$a->value || 'true'===$a->value) ){
				$continue =true;
				break;	
			}
			if($a->name==='name'){
			   $name = $a->value;	
			}
			
			
			if($a->name==='value'){
			   $value = $a->value;	
			}	
		}
		
		if(false!==$continue){
		  continue;	
		}
		$p[$name]=$value;		
	  }	
		
		return $p;
	}
	
}
