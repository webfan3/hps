<?php
namespace webfan\hps\patch;
/*!
MIT License

Copyright (c) 2019 Webfan Homepagesystem


https://github.com/webfan3/hps/tree/master/src/webfan/hps/patch/ngScope.php
*/

class ngScope extends \ArrayObject
{
	 static $debugLevel = 1;
	
	 protected $___class = null;
	
	
    function __construct($input=[]){
        parent::__construct($input,\ArrayObject::STD_PROP_LIST|\ArrayObject::ARRAY_AS_PROPS);
    }
	

    public function importObj($class,  $array = []){
        $this->___class = $class;
        if(count($array) > 0){
            $this->import($array);
        }
        return $this;
    }

    public function import($input)
    {
        $this->exchangeArray($input);
        return $this;
    }

    public function export()
    {
        return $this->objectToArray($this->getArrayCopy());
    }

    public function objectToArray ($object) {
        $o = [];
        foreach ($object as $key => $value) {
           $o[$key] = is_object($value) ? (array) $value: $value;
        }
        return $o;
    }

	
	
	
    public function __call($func, $argv)
    {
        if(is_callable($func) && substr($func, 0, 6) === 'array_'){ 
			return call_user_func_array($func, array_merge(array($this->getArrayCopy()), $argv));
        }
      
        if(is_object($this->___class) && is_callable([$this->___class, $key])){
            return call_user_func_array([$this->___class, $key],$args);
        }
        $result = is_callable($c = $this->__get($key)) ? call_user_func_array($c, $args) : new \BadMethodCallException(__CLASS__.'->'.$func);
		
		if($result instanceof \Exception){
			throw $result;
		}
		
	  return $result;
    }	
	
	
  public function &__get($key)
    {
        return $this[$key];
    }

    public function __set($key, $value)
    {
        $this[$key] =  $value;
    }

    public function __isset($name)
    {
        return isset($this[$name]);
    }
	
	
  

        static public function sdprintf() {
                if (static::$debugLevel > 1) {
                        call_user_func_array("printf", func_get_args());
                }
        }

        public function offsetGet($name) {
                self::sdprintf("%s(%s)\n", __FUNCTION__, implode(",", func_get_args()));
                return call_user_func_array(array(parent, __FUNCTION__), func_get_args());
        }
        public function offsetSet($name, $value) {
                self::sdprintf("%s(%s)\n", __FUNCTION__, implode(",", func_get_args()));
                return call_user_func_array(array(parent, __FUNCTION__), func_get_args());
        }
        public function offsetExists($name) {
                self::sdprintf("%s(%s)\n", __FUNCTION__, implode(",", func_get_args()));
                return call_user_func_array(array(parent, __FUNCTION__), func_get_args());
        }
        public function offsetUnset($name) {
                self::sdprintf("%s(%s)\n", __FUNCTION__, implode(",", func_get_args()));
                return call_user_func_array(array(parent, __FUNCTION__), func_get_args());
        } 	
	
	
}
	 
	 
	 
