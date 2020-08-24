<?php 
/**
 * 
 * Copyright  (c) 2017, Till Wehowski
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgement:
 *    This product includes software developed by the frdl/webfan.
 * 4. Neither the name of frdl/webfan nor the
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
 /**
 * EventEmitter javascript like event based final state object : 
 *    https://github.com/frdl/-Flow/blob/master/api-d/4/js-api/library.js/core/plugin.core.js#L4501
 */
namespace frdl\Flow;



class EventEmitter extends Element{
	
	
	public function hasListeners($event){
	   return (isset($this->events[$event]) && 0 < count($this->events[$event])) ? true : false;
	}


	public function required($eventsArrayIn, $callback, $multiple = false){
				$that = &$this;
							
				$eventArray = $eventsArrayIn;
									  	$eventData  = array();
									  	$updateData = array();
									  	$called = false;
									  	$listen = function (&$obj, $multiple){
									  		if(true===$multiple)
									  		{
									  		 //	return $events->on || $events->addListener;
									  		   return array($obj, 'on');
									  		}
									  		else
									  		{
										  	   return array($obj, 'once');
									  		}
									  	};
									  	$listen = $listen($that, $multiple);
									  	$silence= array($that, 'off');
									  	$isOn   = $listen === array($that, 'on');
									  	$clear  = function () use ( &$eventArray, &$updateData, $silence, &$that){
									  		
									  	//	foreach($eventArray as $event){
									  		foreach($that->Iterator('Array', $eventArray) as $event){
												call_user_func_array($silence, array($event, $updateData[array_search($event, $eventArray)]));
											}
									  		$eventData = array();
									  	}
									  	;
									  	
	
									  	$stateCheck = function () use ( &$eventArray, &$eventData, &$called, &$multiple, &$isOn, $clear, &$that, &$callback)
									  	{
									  		
									  		$waiting = false;
									  		$ready = false;
									  		foreach($that->Iterator('Array', $eventArray) as $event){
									  			  $k = array_search($event, $eventArray);
									  		//	  if(false===$k || null===$eventData[$k]){
												if(false===$k || !isset($eventData[$k])){
												  	 $waiting = true;
												  	 break;
												  }

											}									  		 
									  		
									  		
									  		$ready = (false === $waiting) ? true : false;
									  		if(true===$ready && true!==$called)
									  		{
									  			call_user_func_array($callback, array($eventData));
									  			if(true!==$multiple)
									  			{
									  				$called = true;
									  				if(true===$isOn)
									  				{
									  					$clear();
									  				}
									  			}
									  		}
									  	}
									  	;								  	
									  	
									
		                                                   
		$updateState = function ($eventName) use ( &$eventArray, &$eventData, &$stateCheck){
									  		$index = array_search($eventName, $eventArray);
									  		return function ($data = null) use ( &$eventData, &$index, &$stateCheck){
									  			if(null===$data)
									  			{
									  			   $data = true;
									  			}
									  			$eventData[$index] = $data;
									  			call_user_func_array($stateCheck, array());
									  		 //   $stateCheck();
									  		}
									  		;
									  	}
									  	;
									
									
									  	$stateReady = function ($s) use ( &$eventData, &$eventArray)
									  	{
									  		 $k = array_search($s, $eventArray);
									  		 return (false===$k || !isset($eventData[$k])) ? false : true;
									  	}
									  	;
									  	
									  	
									  	$stateGet =	function ($s) use ( &$eventData, &$eventArray)
									  	{
									  		return $eventData[array_search($s, $eventArray)];
									  	}
									  	;
									  	
									  	
									  	
									  	
									  										 
		                                     
		$addState =	function () use ( &$eventArray, &$updateData, $updateState, $listen, &$that)
									  	{
									  		$events = func_get_args();
									  		
									  		foreach($that->Iterator('Array', $events) as $event){
                                              
												if(is_array($event)){                                                	
												
												  foreach($event as $ev){													
												
												  	$index = array_search($ev, $eventArray);
									  				if($index === false)
									  				{
									  					array_push($eventArray, $ev);
									  					$index = count($eventArray) - 1;
									  				}
									  				$updateData[$index] = $updateState($ev);

									  				 call_user_func_array($listen, array($ev,$updateData[$index])); 													
													
												   }
												}else{
													$index = array_search($event, $eventArray);
									  				if($index === false)
									  				{
									  					array_push($eventArray, $event);
									  					$index = count($eventArray) - 1;
									  				}
									  				$updateData[$index] = $updateState($event);

									  				call_user_func_array($listen, array($event,$updateData[$index])); 
												}

											}											  		
									  	};
									  	
									  	
			foreach($that->Iterator('Array', $eventArray) as $event){                
				$addState($event);
			}										  	


       /* $finStateObj = new \O; */
      // $fo = new \O;
       $fo = new \stdclass;		
       $fo->cancel = $clear;
       $fo->add = $addState;
       $fo->addState = $addState;
       $fo->events = $eventArray;
       $fo->status = $eventData;
       $fo->stateReady = $stateReady;
       $fo->stateGet = $stateGet;
       
        
				
	   return $fo;
	}
									  
	

}
