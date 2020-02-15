<?php

use Psr\Container\ContainerInterface;
use function DI\decorate;


return  [
	
		
	'state.emitter' => decorate(function($emitter, ContainerInterface $c){
	     $emitter->once('bootstrap', function($eventName, $emitter, \webfan\hps\Event $Event){
			         $logger = $Event->getArgument('container')->get('logger.php');
               if (version_compare(\PHP_VERSION, '7.0.0', '<')) {
                  \webfan\hps\patch\Typehint::register($logger);
               }
               $logger->register();
	     });
		return $emitter;
	}),
	
	

		
];
