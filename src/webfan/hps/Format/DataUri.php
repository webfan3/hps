<?php
namespace webfan\hps\Format;

use webfan\hps\Format\Validate as Validate;

/**
							\webfan\hps\Filesystem\MountingFixture $fs = null,
							\webfan\hps\EventModule $AssetsLinkRoutingRewriteEmitter = null		
**/
use webfan\hps\Filesystem\MountingFixture as FilesystemManager;
use webfan\hps\EventModule as AssetsLinkRoutingRewriteEmitter;

class DataUri implements \Serializable
{
	   


 /** @var Regular expression used for decomposition of data URI scheme */
    protected static $REGEX_URI = '/^data:(.+?){0,1}(?:(?:;(base64)\,){1}|\,)(.+){0,1}$/';
    
    const DEFAULT_TYPE = 'text/plain;charset=US-ASCII';
    
    const ENCODING_URL_ENCODED_OCTETS = 0;
    const ENCODING_BASE64 = 1;
    
    /** @var Keyword used in the data URI to signify base64 encoding */
    const BASE64_KEYWORD = 'base64';
    
    protected $mediaType;
    protected $encoding = null;
    protected $encodedData = null;
    
    /**
     * Instantiates an instance of the DataURI class, initialised with the 
     * default values defined in RFC 2397. That is the media-type of 
     * text/plain;charset=US-ASCII and encoding type of URL encoded octets.
     * 
     * @param string $mediaType
     * @param string $data  Unencoded data
     * @param integer $encoding Class constant of either
     * {@link DataUri::ENCODING_URL_ENCODED_OCTETS} or
     * {@link DataUri::ENCODING_BASE64}
     * 
     * @throws InvalidArgumentException
     */
    public function __construct($mediaType = self::DEFAULT_TYPE,
            $data = '',
            $encoding = null
            ) {
        call_user_func_array([$this, 'ObjectConstructor'], func_get_args());
    }
	
	protected function ObjectConstructor($mediaType = self::DEFAULT_TYPE,
            $data = '',
            $encoding = null
            ){
        try {
			if(null===$encoding){
			  $this->encoding = intval(true===Validate::isbase64($data));	
			}else{
				$this->encoding = $encoding;
			}
			
			
            $this->setMediaType($mediaType);
            $this->setData($data, $this->encoding);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }		
	}
 
	public static function createFromLink($link, $type, //= 
						   //'image'
						   // 'text', 
							$schemeRequired = true,	
							&$mime = null,
							FilesystemManager $fs = null,
							AssetsLinkRoutingRewriteEmitter $AssetsLinkRoutingRewriteEmitter = null,
							$m = null
						  ) {
	                     $u = parse_url($link);
		                 if(false !== $schemeRequired
							 && (!isset($u['scheme']) || empty($u['scheme']) 
							|| 'file' === $u['scheme'] 
							|| 'localhost' === $u['scheme'] 
							|| 'modules' === $u['scheme']
							|| (/*null !== $fs && */ null === $fs || !$fs->isMounted($u['scheme'])))) {
								
								throw new \Exception('Invalid protocol: '.htmlentities($u['scheme']).' '.__METHOD__);
								
						}
								 
								 
				  if(null===$m)$m = \wURI::parse_uri($u['scheme'], $u['host'], $u['path'])->file_ext;
				  if($m === 'jpg')$m='jpeg';				 
								 
				  $mime  = $type.'/'.$m;		
					
								 
			if(null !== $AssetsLinkRoutingRewriteEmitter){					 
				 $eventName = 'AssetsLinkRoutingRewrite::'.basename(__METHOD__).'::'.basename(__CLASS__);	
				 $Event = new \webfan\hps\Event($eventName);
			  	 $Event->setArgument('url', \frdl\webfan\App::God(false)->{'unparse_url'}($u)  );	
			  	 $Event->setArgument('MagicMimeGuess', $mime  );				 	
			  	 $Event->setArgument('protocol', $u['scheme']  );				 
						 
	             $Event->setResult($Event->getArgument('url'));
				 $AssetsLinkRoutingRewriteEmitter->emit($eventName, $Event);			
				
				$link=$Event->getResult();
			}
								 
								 
           
		return self::create($mime, file_get_contents($link), self::ENCODING_BASE64);
     }	
	
	public static function create($mime, $data, $encoding = null) {
		if(null===$encoding){
		   $encoding = 	(true===Validate::isbase64($data)) ? self::ENCODING_BASE64 : self::ENCODING_URL_ENCODED_OCTETS;
		}
		return new self($mime,
						$data,
						//(true===Validate::isbase64($data)) ? base64_decode($data) : $data,
						$encoding);
     }	
		

	
    /**
     * Returns the data URI's media-type. If none was provided then in 
     * accordance to RFC 2397 it will default to text/plain;charset=US-ASCII
     * 
     * @return string Media-type
     */
    public function getMediaType() {
        return empty($this->mediaType) === false
            ? $this->mediaType
            : self::DEFAULT_TYPE;
    }
    
    /**
     * Sets the media-type.
     * 
     * @param string $mediaType Media-type
     */
    protected function setMediaType($mediaType) {
        $this->mediaType = $mediaType;
    }
    
    /**
     * Returns the method of encoding used for the data.
     * 
     * @return int Class constant of either 
     * {@link DataUri::ENCODING_URL_ENCODED_OCTETS} or
     * {@link DataUri::ENCODING_BASE64}
     */
    public function getEncoding($data = null) {
		
		if(null === $data){
		  $data = $this->getEncodedData();	
		}
		
		if(//null === $this->encoding && null!==$this->encodedData && 
		   Validate::isbase64($this->getEncodedData()) ){
		        $this->encoding = self::ENCODING_BASE64;
		}else{
			 $this->encoding = self::ENCODING_URL_ENCODED_OCTETS;
		}
		
        return $this->encoding;
    }
    
    /**
     * Returns the data in its encoded form.
     * 
     * @return string Encoded data
     */
    public function getEncodedData() {
        return $this->encodedData;
    }
    
    /**
     * Sets the encoded data and the encoding scheme used to encode/decode it.
     * Be aware that the data is not validated, so ensure that the correct
     * encoding scheme is provided otherwise the method 
     * {@link DataUri::tryDecodeData($decodedData)} will fail.
     * @param int $encoding Class constant of either 
     * {@link DataUri::ENCODING_URL_ENCODED_OCTETS} or
     * {@link DataUri::ENCODING_BASE64}
     * @param string $data Data encoded with the encoding scheme provided
     * @throws InvalidArgumentException
     */
    protected function setEncodedData($encoding, $data) {
		
        if(($encoding === self::ENCODING_URL_ENCODED_OCTETS) ||
            ($encoding === self::ENCODING_BASE64)) {
              //  throw new \InvalidArgumentException('Unsupported encoding scheme');
			if($encoding=== self::ENCODING_BASE64 && true!==Validate::isbase64($data)) {
				$data = base64_encode($data);
			}else{
				//  $data = rawurlencode($data);
			}
        }
        
        $this->encoding = $encoding;
		
        $this->encodedData = $data;
    }
    
    
    /**
     * Sets the data for the data URI, which it stores in encoded form using
     * the encoding scheme provided.
     * 
     * @param string $data Data to encode then store
     * @param int $encoding Class constant of either 
     * {@link DataUri::ENCODING_URL_ENCODED_OCTETS} or
     * {@link DataUri::ENCODING_BASE64}
     * @throws InvalidArgumentException
     */
    protected function setData($data, $encoding = null) {
		
		
		if(null===$encoding){
		   $encoding = 	$this->getEncoding($data);
		}		
			
			
        switch($encoding) {
            case self::ENCODING_URL_ENCODED_OCTETS:
		        $this->encoding = self::ENCODING_URL_ENCODED_OCTETS;
                $this->setEncodedData($this->encoding, $data);
                break;
            case self::ENCODING_BASE64:
	          	$this->encoding = self::ENCODING_BASE64;
                $this->setEncodedData($this->encoding, $data);
                break;
            default:
                throw new \InvalidArgumentException('Unsupported encoding scheme');
                break;
        }
    }
    
    /**
     * Tries to decode the URI's data using the encoding scheme set.
     * 
     * @param null $decodedData Stores the decoded data
     * @return boolean <code>true</code> if data was output, 
     * else <code>false</code>
     */
    protected function tryDecodeData(&$decodedData) {
        $hasOutput = false;
        
        switch($this->getEncoding()) {
            case self::ENCODING_URL_ENCODED_OCTETS:
                $decodedData = rawurldecode($this->getEncodedData());
                $hasOutput = true;
                break;
            case self::ENCODING_BASE64:
                $b64Decoded = base64_decode($this->getEncodedData(), true);
                if($b64Decoded !== false) {
                    $decodedData = $b64Decoded;
                    $hasOutput = true;
                }
                break;
            default:
                // NOP
                break;
        }
        return $hasOutput;
    }
	
    public function raw(){
		if(!$this->tryDecodeData($decodedData) ){
			throw new \Exception('Cannot decode data in '.__METHOD__.' '.__LINE__);
		}
		
		return $decodedData;
	}
 
	public function unserialize($serialized /*, &$out*/) {
        $hasOutput = false;
        $dataUriString = $serialized;
		
        if(self::isParsable($dataUriString)) {
            $matches = null;
            if(preg_match_all(self::$REGEX_URI,
                $dataUriString,
                $matches,
                \PREG_SET_ORDER) !== false) {
                $mediatype = isset($matches[0][1])
                    ? $matches[0][1]
                    : self::DEFAULT_TYPE;
                $matchedEncoding = isset($matches[0][2]) ? $matches[0][2] : '';
                $data = isset($matches[0][3]) 
                    ? $matches[0][3] 
                    : '';				
                $encoding = (strtolower($matchedEncoding) === self::BASE64_KEYWORD &&  true===Validate::isbase64($data) )
                    ? self::ENCODING_BASE64
                    : self::ENCODING_URL_ENCODED_OCTETS;

				
				/*
                $dataUri = new self();
                $dataUri->setMediaType($mediatype);
                $dataUri->setEncodedData($encoding, $data);
                $out = $dataUri;
				
                $this->setMediaType($mediatype);
                $this->setEncodedData($encoding, $data);				
				*/
			     $this->setMediaType($mediatype);
				 $this->setEncodedData($encoding, $data);
				
				
				//call_user_func_array([$this, 'ObjectConstructor'], [$mediatype, $data, $encoding]);
				
                $hasOutput = true;
            }
        }
        
      //  return $hasOutput;
    }	
	
	
    /**
     * Determines whether a string is data URI with the components necessary for
     * it to be parsed by the {@link DataUri::tryParse($s, &$out)} method.
     * 
     * @param string $string Data URI
     * @return boolean <code>true</code> if possible to parse,
     * else <code>false</code>
     */
    public static function isParsable ($dataUriString) {
        return (preg_match(self::$REGEX_URI, $dataUriString) === 1);
    }
	
	
    /**
     * Generates a data URI string representation of the object.
     * 
     * @return string
     */
    public function toString() {
        $output = 'data:';
        
     //   if(($this->getMediaType() != self::DEFAULT_TYPE) || ($this->getEncoding($this->getEncodedData()) != self::ENCODING_URL_ENCODED_OCTETS)) {
            $output .= $this->getMediaType();
			
			if($this->encoding === self::ENCODING_BASE64) {
                $output .= ';'.self::BASE64_KEYWORD;           
		    }
			
    //    }
          
	
		
        $output .= ','.$this->getEncodedData();
        return $output; 
    }
    
    public function __toString(){
        return $this->toString();
    }
 	
	 public function serialize(){
		return $this->toString(); 
	 }   

    
    /**
     * Parses a string data URI into an instance of a DataUri object.
     * 
     * @param string $dataUriString Data URI to be parsed
     * @param DataUri $out Output DataUri of the method
     * @return boolean <code>true</code> if successful, else <code>false</code>
    
    public static function tryParse($dataUriString, &$out) {
        $hasOutput = false;
        
        if(self::isParsable($dataUriString)) {
            $matches = null;
            if(preg_match_all(self::$REGEX_URI,
                $dataUriString,
                $matches,
                \PREG_SET_ORDER) !== false) {
                $mediatype = isset($matches[0][1])
                    ? $matches[0][1]
                    : self::DEFAULT_TYPE;
                $matchedEncoding = isset($matches[0][2]) ? $matches[0][2] : '';
                $encoding = (strtolower($matchedEncoding) === self::BASE64_KEYWORD)
                    ? self::ENCODING_BASE64
                    : self::ENCODING_URL_ENCODED_OCTETS;
                $data = isset($matches[0][3]) 
                    ? $matches[0][3] 
                    : '';
                $dataUri = new self();
                $dataUri->setMediaType($mediatype);
                $dataUri->setEncodedData($encoding, $data);
                $out = $dataUri;
                $hasOutput = true;
            }
        }
        
        return $hasOutput;
    }
	
	 */
}
