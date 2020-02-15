<?php
namespace webfan\hps\patch;

use Webfan\Support\LoggingHandlerInterface;


/*
* https://stackoverflow.com/questions/5724677/php-type-hinting-to-primitive-values
*/
class Typehint
{
    const TYPEHINT_PCRE = '/^Argument (\d)+ passed to (?:(\w+)::)?(\w+)\(\) must be an instance of (\w+), (\w+) given/';
    protected static $Typehints = array(
        'boolean'   => 'is_bool',
        'integer'   => 'is_int',
        'float'     => 'is_float',
        'string'    => 'is_string',
        'resource'  => 'is_resource'
    );

    private function __construct() {}

    public static function register(LoggingHandlerInterface $LoggingHandler = null)
    {
       if(null === $LoggingHandler){
           set_error_handler(__CLASS__.'::handleTypehint');
        }else{
           $previous = $LoggingHandler->get_error_handler();
           $LoggingHandler->set_error_handler(__CLASS__.'::handleTypehint');
           $LoggingHandler->set_error_handler($previous);
           $LoggingHandler->register();
        }

        return TRUE;
    }

    protected static function getTypehintedArgument($ThBackTrace, $ThFunction, $ThArgIndex, &$ThArgValue)
    {

        foreach ($ThBackTrace as $ThTrace)
        {

            // Match the function; Note we could do more defensive error checking.
            if (isset($ThTrace['function']) && $ThTrace['function'] == $ThFunction)
            {

                $ThArgValue = $ThTrace['args'][$ThArgIndex - 1];

                return true;
            }
        }

        return false;
    }

    public static function handleTypehint($ErrLevel, $ErrMessage)
    {

        if ($ErrLevel == \E_RECOVERABLE_ERROR)
        {

            if (preg_match(self::TYPEHINT_PCRE, $ErrMessage, $ErrMatches))
            {

                list($ErrMatch, $ThArgIndex, $ThClass, $ThFunction, $ThHint, $ThType) = $ErrMatches;

                if (isset(self::$Typehints[$ThHint]))
                {

                    $ThBacktrace = debug_backtrace();
                    $ThArgValue  = NULL;

                    if (self::getTypehintedArgument($ThBacktrace, $ThFunction, $ThArgIndex, $ThArgValue))
                    {

                        if (call_user_func(self::$Typehints[$ThHint], $ThArgValue))
                        {

                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
