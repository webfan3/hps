<?php 
/**
 * Copyright  (c) 2015, Till Wehowski
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of frdl/webfan nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY frdl/webfan ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL frdl/webfan BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 */
namespace frdl\Flow;
/**
*   
*   @provides the public methods:
*   ::create(mixed $context)
*   ->__invoke(mixed $context)            > apply $context and returns $this method chain
* 
*  ->Iterator($type = 'Array')           >not chainable returns Iterator
*  ->iterate(Array,callback,&result,&resultLog)
* 
*  ->on($event, $listener, $obj = null)
*  ->trigger($event, &$data = array() )
*  ->addEventListener                    >alias for on
*  ->dispatchEvent                       >alias for trigger
*  ->removeEventListener($event, $listener)
* 
*  ->context(mixed $context = undefined) >if getter not chainable returns context or $this
*  ->walk($Array)                        >IteratorGenerator 
* 
* Example 
* frdl\Flow\TestElement in __vendor__/frdl/Flow/Flow/TestElement.php
* <?php
* use frdl\Flow;
* 
* log('Starting testCase: '.__FILE__);
* function lnbr(){
* 	echo "\n";
* }
* function log($str){
*   echo microtime().':'.$str;
* *   lnbr();
*   ob_end_flush();	
* } 
* function highlight_num($file)
* {
*   $lines = implode(range(1, count(file($file))), '<br />');
*   $content = highlight_file($file, true);
* 
*  
*   $out = '
*     <style type="text/css">
*         .num {
*         float: left;
*         color: gray;
*         font-size: 13px;   
*         font-family: monospace;
*         text-align: right;
*         margin-right: 6pt;
*         padding-right: 6pt;
*         border-right: 1px solid gray;}
* 
*         body {margin: 0px; margin-left: 5px;}
*         td {vertical-align: top;}
*         code {white-space: nowrap;}
*     </style>';
*    
*    
*    
*     $out.= "<table><tr><td class=\"num\">\n$lines\n</td><td>\n$content\n</td></tr></table>";
*      
*     return $out;
* } 
*  
* log('Creating inherited Element class and Testclasses'.lnbr().'Bind Events on Testclasses listeners'.lnbr().'Bind a TestDebugger to the testEvent and trigegr it');
* class TestElement extends Element{
*   protected $reflection;
*   	protected $initTime=null;
* 	public function __construct(){
* 	    parent::create( func_get_args());
* 		$this->refelection = ReflectionClass::export('Apple', true);
* 	}
* 	function __destuct(){
*          register_shutdown_function(function ($className) {
* 		log('shutdown_function.invocation by destructor of '.$className);              
*          }, get_class($this));		
* 	} 
*    
*    public function test($event, &$target, &$eventData){
*    	  log('Triggering listener of "'.$event.'" Event in listener '.__METHOD__);
*    	  log(
*    	     '<pre>'
*    	     .'Eventdata: '.lnbr()
*    	     .print_r($eventData,true)
*    	     .lnbr()
*    	     .__CLASS__.':'
*    	     .lnbr()
*    	     .$this->refelection.lnbr()
*    	     .'</pre>'.lnbr()
*    	     .highlight_num(__FILE__).lnbr()
*    	  );
*    }
* }
* class MyElementSubClass extends Element{
* 	protected function __construct(){
* 		$args = func_get_args();
* 		parent::__construct($args);
* 		$this->name=$args[0];
* 		$this->data=$args[1];
* 		log('Creating Instance of '.__CLASS__.' inherrited from '.get_class(parent) );
* 	}
* 	public static function create($name, $data){
* 	   return parent::create($name, $data);
* 	}	
* }
* 
* function myEventListenerGlobalFunction($event, &$target, &$data) {
* 	// return false;  // cancel/ stopPropagation 
*   log("Hello from triggered function myEventListenerGlobalFunction() on the $event Event");
* }
* 
* class Foo {
*   public function hello($event, &$target, &$eventData) {
*     log("Hello from triggered ".__CLASS__."($event, ".print_r($target,true).", ".print_r($eventData,true).")");
*   }
* }
* 
* class Bar {
*    public static function listen() {
*     log("Hello from Bar::hello()");
*   }
* }
*  $foo = new Foo();
*  $Context = new \stdclass;
*  
*  $myElement = MyElementSubClass::create($Context)
*   // bind a global function to the 'test' event
*   ->on("test", "myEventListenerGlobalFunction")   
* 
*   // bind an anonymous function
*   ->on("test", function($event, &$target, &$eventData) { 
*      log("Hello from anonymous function triggered by Event:".$event);
*   })  
* 
* 
*    ->on("test", "hello", $foo)  // bind an class function on an instance
* 
* 
*   ->on("test", "Bar::listen")  // bind a static class function
* 
* 
* 
*  ;
* $testData=array(
*   'data' => array('someTestData', 1, 2, 3, 5, 8, 13, 21, new \stdclass),
*   'Author' => '(c) Till Wehowski, http://frdl.webfan.de',
*   '__FILE__' => __FILE__,
* );
* $myElement()
*    ->on("test", "test", new TestElement)  
*     
*   // dispatch the "test event"  
*    ->trigger("test", $testData)
*     
*    ;
*/
abstract class Element {
	protected static $tagName;
	protected $name; //id/selector
	protected $_context = null;
	
    protected $events = array();
	function __construct(){
		$this->_context=func_get_args();
		self::$tagName = get_class($this);
	}
	public static function create(){
	   $_call='\\'.get_class(self).'::__construct';	
	   return call_user_func_array($_call, func_get_args());
	}	
	function __destruct(){
		
	}
    final public function __invoke(/* mixed */)
    {
        $this->_context=func_get_args();
		return $this;
    }	
    
    final public function context(){
       $args=func_get_args();
      if(0===count($args)){
	     return  $this->_context;		
	  } 	
	  return $this($args);
	}
	
  /*
    Iterator "Trait"
  */	
   public function Iterator($type = 'Array', $Traversable){
   	 if('Array'===$type) 
   	     return $this->_ArraIterator($Traversable);
   	  
   	  
      return function($Traversable){
            return $Traversable;
      };   	  
   }
   
   public function iterate(Array $Collection, $callback/* 
           function($item) use(&$result){
		   	  // ... process item
		   	  return $result;
		   }   
   */, &$result=null, &$resultLog=null){
   	$resultLog=array();
     foreach($this->Iterator('Array', $Collection) as $item) {
        $resultLog[] = call_user_func_array($callback, array($item)) ;
     }
     return $this;	
   }
 
	
	
	
  /*
    Event "Trait"
  */	
  public function removeEventListener($event, $listener){
     if (!isset($this->events[$event])) return $this;
    
    $indexOf = 0;
    foreach ($this->Iterator('Array', $this->events[$event]) as $EventListener) {
      // if($EventListener === $listener)	{
	   if(spl_object_id((object)$EventListener) === spl_object_id((object)$listener))	{
         array_splice($this->events[$event], $indexOf, 1);
         $indexOf--;
		 
	   }
         $indexOf++;
    }
    return $this; 	
  }
  public function removeListener(){
  	return call_user_func_array(array($this,'removeEventListener'), func_get_args());
  }  
  
   
  public function off(){
  	return call_user_func_array(array($this,'removeEventListener'), func_get_args());
  }  
   
   
   
   
  public function addEventListener(){
  	return call_user_func_array(array($this,'on'), func_get_args());
  }
    
  public function on($event, $callback, $obj = null) {
    if (!isset($this->events[$event])) {
      $this->events[$event] = array();
    }
   
    $this->events[$event][] = ($obj === null)  ? $callback : array($obj, $callback);
    return $this;
  }
  
  
  
   public function once($event, $callback, $obj = null) {
	    
   	  $THAT =$this; 
   	  $obj = $obj;
   	  $callback= $callback;
      $listener = (function() use($event, &$callback, &$THAT, &$obj, &$listener){
   	    	$THAT->removeEventListener($event, $listener);
   	  	     call_user_func_array(($obj === null)  ? $callback : array($obj, $callback), func_get_args());
   	  });
   	  $this->on($event, $listener);
   	  
    return $this;
  }
  
  /*
		EventEmitter.prototype.once = function (event, listener)
		{
			this.on(event, function g ()
				{
					this.removeListener(event, g);
					listener.apply(this, arguments);
				});
			return this;
		};  
  */
  
  public function emit(){
  	return call_user_func_array(array($this,'trigger'), func_get_args());
  }  
  public function dispatchEvent(){
  	return call_user_func_array(array($this,'trigger'), func_get_args());
  }
  public function trigger($event, $data = array()) {
    if (!$this->events[$event]) return $this;
   
  
    $indexOf=0;
    foreach ($this->Iterator('Array', $this->events[$event]) as $callback) {
      	$payload = array();
      	$ev = &$event;
      	$target = &$this;
      	$evData = &$data;
   	    array_push($payload, $event);
   	    array_push($payload, $target);  
     	array_push($payload, $data);   
     	if(!is_callable($callback)){
			trigger_error('Cannot trigger Event '.$event.' on Listener #'.$indexOf, E_USER_WARNING);
			continue;
		} 	
	//  if(frdl\run($callback, $payload) === false) break;
	  if(false === call_user_func_array($callback, $payload))break;
      $indexOf++;
    }
    return $this;
 }	
  	
  	
 	
 	
 	
 	
/*
private...
*/
   public function walk($list){
      foreach ($list as $value) {
         yield ($value);
      }  	
   }
 	
   public function _ArraIterator($arr){
     if(true===version_compare(PHP_VERSION, '5.5', '>=')) {
       $iterator=new LazyIterator($this->walk($arr));  
       return $iterator->generator();  
     }else{
     	/*
         return function(Array $arr){
            return $arr;
         };
         */
         return new arrayIterator($arr); 	
       }		
  }
 	
}
