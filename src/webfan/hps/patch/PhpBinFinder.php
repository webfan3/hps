<?php
/*
  (new \webfan\hps\patch\PhpBinFinder)->find()
*/

namespace webfan\hps\patch;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\PhpProcess;


class PhpBinFinder
{
	public function find(){
		$binFile = (new PhpExecutableFinder)->find();
		if(empty($binFile)){
			$binFile = dirname(dirname(dirname(ini_get('extension_dir')))). \DIRECTORY_SEPARATOR .'bin'. \DIRECTORY_SEPARATOR .'php';	
		}

		$tmpfname = tempnam(\sys_get_temp_dir(), 'phpcheck');
		file_put_contents($tmpfname, "<?php echo 'php\n'; echo \PHP_VERSION.'\n';");
		exec(sprintf('cd %s && %s %s 2>&1 ',dirname($tmpfname), $binFile, $tmpfname), $out, $status); 
		unlink($tmpfname);

		if(isset($out[0]) && 'php' === $out[0]){
 
		}else{ 
			exec('which php 2>&1 ', $out, $status); 
			$binFile = (isset($out[0])) ? $out[0] : '/usr/bin/php';
		}		
	
		return $binFile;
	}	
}
