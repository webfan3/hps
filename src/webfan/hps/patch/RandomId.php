<?php
namespace webfan\hps\patch;

use Hidehalo\Nanoid\Client as IDGenerator;
use Hidehalo\Nanoid\Generator as IDGeneratorGenerator;	

class RandomId
{
   const DEFAULT_SIZE = 40;
   const DEFAULT_CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_-abcdefghijklmnopqrstuvwxyz';

   public static function generate(int $size = null, string $chars = null){
		$newId = (new IDGenerator())
     ->formattedId((is_string($chars)) ? $chars : self::DEFAULT_CHARS, 
     (is_int($size)) ? $size : self::DEFAULT_SIZE, 
     new IDGeneratorGenerator()
     );  
     
     return (string)$newId;
   }
}
