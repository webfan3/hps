<?php
declare(strict_types=1);

namespace webfan\hps\patch;

class Process
{

  protected $pid = null;

  public function __construct(int $pid = null){
     if(null === $pid){
       $pid = getmypid();
     }
     $this->pid = $pid;
  }


  public static function __callStatic($name, $args){
         if(is_int($args[0])){
          $pid = $args[0];
        } else{
          $pid = null;
        }
     $Helper = new self($pid);
     return call_user_func_array([$Helper, $name], $args);    
  }
  
  public function __call($name, $args){
     $method = '_'.$name;
     
     if(!is_callable([$this, $method])){
         throw new \Exception(get_class($this).'->'.$name.' is not callable in '.__METHOD__);
     }else{
         return call_user_func_array([$this, $method], $args);    
     }
  }
  

	
  protected function _user(string $key = null){
       $userinfo = posix_getpwnam(\get_current_user());  
       if(null === $key){
	 return $userinfo;       
       }
	  
    return (isset($userinfo[$key])) ? $userinfo[$key] : null;	  
  }
 	
 	
	
	
  protected function _owner(string $file = null, string $key = null){
       if(null === $file){
	 $file = $this->user('dir');       
       }
	
         $uid=\fileowner($file);
	  
       if('uid' === $key){
	 return $uid;       
       }	  
	  
         $userinfo=posix_getpwuid($uid); 
	  
       if(null === $key){
	 return $userinfo;       
       }	
	  
    return (isset($userinfo[$key])) ? $userinfo[$key] : null;	  
  }
	
	
	
  protected function _is(int $pid = null){   
     if(null === $pid){
       $pid = $this->pid;
     }   
  
		try{       
			$result = shell_exec(sprintf("ps %d", $pid));        
			if(count(preg_split("/\n/", $result)) > 2){        
				return true;       
			}    
		}catch(\Exception $e){}   
		return false;		
	}


  //kill(123,0);
  protected function _kill(int $pid = null,$signal = 0, $recursive = true/* kill childprocesses */) {
    if(null === $pid){
       $pid = $this->pid;
     }   
  
        if(!is_int($pid)){
          $pid = intval($pid);
        } 
  
     if(true===$recursive){
        exec("ps -ef| awk '\$3 == '$pid' { print  \$2 }'", $output, $ret);
        if($ret) return 'you need ps, grep, and awk';
        while(list(,$t) = each($output)) {
            if ( $t != $pid && getmypid() != $t) {
                $this->kill($t,$signal);
            }
        }
     }

     if(getmypid() !== $pid){
       \posix_kill($pid, 9);
		    if(posix_get_last_error()==1) {		     	
          throw new \Exception(print_r(\posix_get_last_error(), true));
		    }
      }else{
        throw new \Exception('Cannot kill process (#'.$pid.') by its own process in '.__METHOD__);
      }
   } 


}


