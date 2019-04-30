<?php
declare(strict_types=1);
namespace webfan\hps\Widget;
/*!
MIT License

Copyright (c) 2019 Webfan Homepagesystem


https://github.com/webfan3/hps/tree/master/src/webfan/hps/Widget/Config.php
*/
class Config
{
	protected $configUrl = null;
	protected $zipUrl = null;
	protected $xml = null;
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
