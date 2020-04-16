<?php
namespace webfan\hps\Parse;
/*
https://gist.github.com/pokeb/10590

Ben's quick and dirty HTTP cookie header parser

HTTP cookie headers are actually quite tricky to parse, because the expiry date often contains a comma, and commas are supposed to be the delimiter between individual cookies.

This parser cheats by splitting the cookie header based on the equals sign, and reconstructing the key pairs in a loop.

This sample code doesn't support all cookie properties, but it ought to be trivial to add more. It hasn't been tested a great deal, so use at your own risk.

I originally wrote this in Objective-C for ASIHTTPRequest (http://allseeing-i.com/asi-http-request) before I discovered Apple's function that does this for you (DOH!), but I'd guess this PHP port is likely to be more useful to more people. Even if you hate PHP with a passion, you ought to be able to read it well enough to port it to whatever language you like.

More random stuff at:

http://www.allseeing-i.com

*/





class Cookie
{
	
    public $name = "";
	public $value = "";
	public $expires = "";
	public $domain = "";
	public $path = "";
	public $secure = false;
	
	public $cookies = [];
	
  /**
  *  @arguments: 
  *       -  [$cookie, int $flags [, array $allowed_extras ]]
  */ 
  public function __construct($cookie = null, $flags = \HTTP_COOKIE_PARSE_RAW,  array $allowed_extras = null){
	  $this->cookies = [];	
	  
	  if(is_string($cookie) ){
		  $this->http_parse_cookie($cookie, $flags, $allowed_extras);
	  }
  }
	
	
  protected function http_parse_cookie($header, $flags = \HTTP_COOKIE_PARSE_RAW,  array $allowed_extras = null) {
	 $class = get_class($this);
	  
	 $cookie = $this;
	 //$cookie->cookies = [];	  
	
	
	 $parts = explode("=",$header);
	 for ($i=0; $i< count($parts); $i++) {
		$part = $parts[$i];
		if ($i===0) {
			$key = $part;
			continue;
		} elseif ($i=== count($parts)-1) {
			$cookie->set_value($key,$part);
			$cookie->cookies[$cookie->name] = $cookie->value;
			continue;
		}
		$comps = explode(" ",$part);
		$new_key = $comps[count($comps)-1];
		$value = substr($part,0,strlen($part)-strlen($new_key)-1);
		$terminator = substr($value,-1);
		$value = substr($value,0,strlen($value)-1);
		$cookie->set_value($key,$value);
		if ($terminator === ",") {
			$cookie->cookies[$cookie->name] = $cookie->value;
			$cookie = new $class(null, $flags, $allowed_extras);
		}
		
		$key = $new_key;
	  }
	 return $cookies;
   }
	
	
   public function set_value($key,$value) {
		switch (strtolower($key)) {
			case "expires":
				$this->expires = $value;
				return;
			case "domain":
				$this->domain = $value;
				return;
			case "path":
				$this->path = $value;
				return;
			case "secure":
				$this->secure = ($value == true);
				return;
		}
		if ($this->name === "" && $this->value === "") {
			$this->name = $key;
			$this->value = $value;
		}
    }	
	
}

