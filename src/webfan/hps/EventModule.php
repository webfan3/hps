<?php
/* 
 Copyright (c) 2019 Webfan Homepagesystem MIT License
 https://raw.githubusercontent.com/webfan3/hps/master/LICENSE
*/
namespace webfan\hps;
use League\Flysystem\AdapterInterface;
class EventModule
{
	const VERSION = '2.0.2';
	
	const FM = 'frdl%ev%hps'; //filemarker
	const FM_SPLIT = "/[\%]/";
	const MODEL = '\Webfan\Homepagesystem\EventFlow\StateVM';
    protected $action;
	protected static $emitters = [];
	protected $emitter = null;
	protected $mark;
	
	protected $dirCompiled;
	
	public function fs(){
		return \Webfan::i()->Filesystem;
	}
	
	public function genMark($tok, $split ="/[\%]/"){
	      $toks = preg_split($split, $tok);
          $p=[];
          foreach($toks as $pos => $tok){
             $tok=ord($tok);
	         $p[]= pack('h*', $tok); 
         };
         return implode('', $p);	
	}
		
	public function __construct(\string $action, $reload = false){
        
		
		$pathes = explode('\\', $action);
		$ft = str_replace('\\', '_', substr($action, 0,1));
		if(strlen($action) > 4){
			$ft.= '/'. substr($action, 0,5);
		} 
		$ft.= '/';
		$path = $ft . implode('\\', $pathes).'/';
		
		$this->dirCompiled = rtrim(\Webfan::i()->Config->get('runtime.paths.compiled.events'), '/ ')
			 .'/'
			 .getenv('APP_ENV') 
			 .'/'
			 .self::VERSION  
			 .'/'
			
			
			 .$path
			;
		
		$this->mark = $this->genMark(self::FM, self::FM_SPLIT);
			
		
		if(empty($action)){
		    throw new \Exception('No action/EventModule given in '.__METHOD__.' '.__LINE__);	
		}
		
		$this->action=$action;
		
		if(!isset(self::$emitters[$action]) || true ===$reload){
		   $this->_loadEmitter($this->emitter);	
		   self::$emitters[$action] = &$this->emitter;
		}else{
			$this->emitter = &self::$emitters[$action];
		}
		
		
		
	}
	
	
	public function __call($name, $params){
		    if(null!==$this->emitter){
			   return call_user_func_array([$this->emitter, $name], $params);	
			}	
	}
	
	
	protected function _loadEmitter(&$emitter = null){
		if($this->fs()->has($this->filepath() ) ){
		     $cont = $this->fs()->read($this->filepath() );
			 if($this->mark===substr($cont, 0, strlen($this->mark))){
				 $cont = substr($cont, strlen($this->mark), strlen($cont));
				 $emitter = unserialize($cont);
			 }else{
				  throw new \Exception('Invalid FileMarker in '.__METHOD__.' '.__LINE__);	
			 }
		}else{
			$classname = self::MODEL;
			$emitter =new $classname();
		}
	}
	
	public function filepath($action = null){
		if(null===$action)$action=$this->action;
		$a = preg_replace("/[^A-Za-z0-9\_\-]/", '_', $action).'.'.strlen($action).'.'.sha1($action);
		return $this->dirCompiled.''.basename(__CLASS__).'.'.$a.'.'.'event.compiled.dat';
	}	
	
	
	public function wrap($listener, $obj = null){
		
				if(null !== $obj && is_object($obj) && is_string($listener)  ){
				  $callback = [$obj, $listener];	
				}else{
				   $callback = $listener;	
				}	
		
		return function($eventName, $Emitter, $event) use ($callback){
			
			 $args = func_get_args();
	         $event = array_pop($args);					
			  if(is_object($event) && true === $event instanceof \webfan\hps\Event){
				   if($event->isPropagationStopped() || $event->isDefaultPrevented() ){
					 		    	
					   
					    return;   
				   }
				  
				
			  }
			$args[]=$event;
			try{
		          call_user_func_array($callback, $args);
			}catch(\Exception $e){
			    throw $e;	
			}
		};
	}
	
	
	public static function register($action, $eventName, $listener, $obj = null, $once = false){
		$E = new self($action, true);
		self::unregister($action, $eventName, $callback, $obj);
		$method = (true===$once) ? 'once' : 'on';				
		$E->{$method}($eventName, $E->wrap($listener, $obj), $obj);
		$E->save();			
		return $E;
	}
	
	public static function unregister($action, $eventName = null, $listener = null, $obj = null){
		$E = new self($action, true);
		$method = 'removeEventListener';
		
		if(null !== $eventName){
		     $E->{$method}($eventName, $E->wrap($listener, $obj), $obj);
		     $E->save();
		}
		
		if(null === $eventName || 0 === count($E->getEvents() ) ){
			if($E->fs()->has( $E->filepath() ) ){
				$E->fs()->delete( $E->filepath() ) ;
			}
		}
		
		
		return $E;
	}	
	
	
	
	public function save(){
		if(!is_dir($this->dirCompiled)){
		   $this->fs()->createDir($this->dirCompiled, 0775, true);	
		}
		  chmod($this->dirCompiled, 0775);
		  
		  $cont = $this->mark . serialize($this->emitter);
		
		
		
		if(!is_dir(dirname($this->filepath()))){
		   $this->fs()->createDir(dirname($this->filepath()), 0775, true);	
		}
		  chmod(dirname($this->filepath()), 0775);	
		
		  $this->fs()->put($this->filepath(),  $cont);
		
		  chmod($this->filepath(), 0775);	
	}
}
