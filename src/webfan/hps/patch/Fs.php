<?php



namespace webfan\hps\patch;
class Fs
{
	  
	
	
 public static function compress($buffer) {
        /* remove comments */
        $buffer = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $buffer);
        /* remove tabs, spaces, newlines, etc. */
        $buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);
        /* remove other spaces before/after ) */
        $buffer = preg_replace(array('(( )+\))','(\)( )+)'), ')', $buffer);
        return $buffer;
  }
	
	
 public static function filePrune($filename,$maxfilesize = 4096, $pruneStart = true){
	 
	 if(filesize($filename) < $maxfilesize){
		return; 
	 }
	 
	 $maxfilesize = min($maxfilesize, filesize($filename));
     $maxfilesize = max($maxfilesize, 0);
	 
	 if(true!==$pruneStart){
		 $fp = fopen($filename, "r+");
         ftruncate($fp, $maxfilesize);
         fclose($fp);
		 return;
	 }
	 
        $size=filesize($filename);
        if ($size<$maxfilesize*1.0) return;
        $maxfilesize=$maxfilesize*0.5; //we don't want to do it too often...
        $fh=fopen($filename,"r+");
        $start=ftell($fh);
        fseek($fh,-$maxfilesize,SEEK_END);
        $drop=fgets($fh);
        $offset=ftell($fh);
        for ($x=0;$x<$maxfilesize;$x++){
            fseek($fh,$x+$offset);
            $c=fgetc($fh);
            fseek($fh,$x);
            fwrite($fh,$c);
        }
        ftruncate($fh,$maxfilesize-strlen($drop));
        fclose($fh);
 }
	
	
public static function getRootDir($path = null){
	if(null===$path){
		$path = $_SERVER['DOCUMENT_ROOT'];
	}

		
 if(''!==dirname($path) && '/'!==dirname($path) //&& @chmod(dirname($path), 0755) 
    &&  true===@is_writable(dirname($path))
    ){
 	return self::getRootDir(dirname($path));
 }else{
 	return $path;
 }

}
	
public static function getPathUrl($dir = null){
	if(null===$dir){
	//	$dir = dirname($_SERVER['PHP_SELF']);
		$dir = getcwd();
	}elseif(is_file($dir)){
	  $dir = dirname($dir);	
	}

    $root = "";
    $dir = str_replace('\\', '/', realpath($dir));

    //HTTPS or HTTP
    $root .= !empty($_SERVER['HTTPS']) ? 'https' : 'http';

    //HOST
    $root .= '://' . $_SERVER['HTTP_HOST'];

    //ALIAS
    if(!empty($_SERVER['CONTEXT_PREFIX'])) {
        $root .= $_SERVER['CONTEXT_PREFIX'];
        $root .= substr($dir, strlen($_SERVER[ 'CONTEXT_DOCUMENT_ROOT' ]));
    } else {
        $root .= substr($dir, strlen($_SERVER[ 'DOCUMENT_ROOT' ]));
    }

    $root .= '/';

    return $root;
}
	
	
public static function getRelativePath($from, $to){
    // some compatibility fixes for Windows paths
    $from = is_dir($from) ? rtrim($from, \DIRECTORY_SEPARATOR) .  \DIRECTORY_SEPARATOR : $from;
    $to   = is_dir($to)   ? rtrim($to,  \DIRECTORY_SEPARATOR) .  \DIRECTORY_SEPARATOR   : $to;
    $from = str_replace('\\',  \DIRECTORY_SEPARATOR, $from);
    $to   = str_replace('\\',  \DIRECTORY_SEPARATOR, $to);

    $from     = explode( \DIRECTORY_SEPARATOR, $from);
    $to       = explode( \DIRECTORY_SEPARATOR, $to);
    $relPath  = $to;

    foreach($from as $depth => $dir) {
        // find first non-matching dir
        if($dir === $to[$depth]) {
            // ignore this directory
            array_shift($relPath);
        } else {
            // get number of remaining dirs to $from
            $remaining = count($from) - $depth;
            if($remaining > 1) {
                // add traversals up to first matching dir
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else {
                $relPath[0] = '.'. \DIRECTORY_SEPARATOR . $relPath[0];
            }
        }
    }
    return implode( \DIRECTORY_SEPARATOR, $relPath);
}
	
	
public static function pruneDir($dir, $limit, $skipDotFiles = true, $remove = false){
 $iterator = new \DirectoryIterator($dir);
 $c = 0;
 $all = 0;	
 foreach ($iterator as $fileinfo) {
    if ($fileinfo->isFile()) {
		$c++;
		if(true===$skipDotFiles && '.'===substr($fileinfo->getFilename(),0,1))continue;
        if($fileinfo->getMTime() < time() - $limit){
			if(file_exists($fileinfo->getPathname()) && is_file($fileinfo->getPathname())
			    && strlen(realpath($fileinfo->getPathname())) > strlen(realpath($dir))
			  ){
				//  echo $fileinfo->getPathname();
			//  @chmod(dirname($fileinfo->getPathname()), 0775);	
			//  @chmod($fileinfo->getPathname(), 0775);
			    unlink($fileinfo->getPathname());
				$c=$c-1;
			}	
		}
    }elseif ($fileinfo->isDir()){
    	     $firstToken = substr(basename($fileinfo->getPathname()),0,1);
		 //    if('~'!==$firstToken)continue;
		       if('.'===$firstToken)continue;
         //    if('.'===substr($fileinfo->getFilename(),0,1))continue;
            $subdir = rtrim($fileinfo->getPathname(),'/ ') . DIRECTORY_SEPARATOR;
		 //   echo realpath($subdir);
		    $all += self::pruneDir($subdir, $limit, $skipDotFiles, true);
		 //  if(file_exists( $subdir . '.htaccess' ))continue;
	 //  	   pruneDir($subdir, $limit);
		 
	 //  	 if($fileinfo->getMTime() < time() - $limit){
	 //  	   register_shutdown_function(function($sd){
	 //  	   	  rmdir($sd);
	 //  	   }, $subdir);
	 //  	}   
		   
		
	}
 }//foreach ($iterator as $fileinfo) 
	
	if(true === $remove && 0 === max($c, $all)){
		 @rmdir($dir);
	}
	
	return $c;
}	
	
	
  public static function rglob($pattern, $flags = 0, $traversePostOrder = false) {
    // Keep away the hassles of the rest if we don't use the wildcard anyway
    if (strpos($pattern, '/**/') === false) {
        return glob($pattern, $flags);
    }

    $patternParts = explode('/**/', $pattern);

    // Get sub dirs
    $dirs = glob(array_shift($patternParts) . '/*', \GLOB_ONLYDIR | \GLOB_NOSORT);

    // Get files for current dir
    $files = glob($pattern, $flags);

    foreach ($dirs as $dir) {
        $subDirContent = self::rglob($dir . '/**/' . implode('/**/', $patternParts), $flags, $traversePostOrder);

        if (!$traversePostOrder) {
            $files = array_merge($files, $subDirContent);
        } else {
            $files = array_merge($subDirContent, $files);
        }
    }

    return $files;
 }
}
