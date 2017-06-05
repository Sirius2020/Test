<?php

namespace Format_Checker;

if (class_exists('Format_Checker\Autoload', false) === false){
    class Autoload
    {

        public static function load($class){
            $ds = DIRECTORY_SEPARATOR;
            if (substr($class, 0, strlen(__NAMESPACE__)+1) === __NAMESPACE__.'\\') {
                $src_path = __DIR__.$ds.'src\\'.substr(str_replace('\\', $ds, $class), 15).'.php';
                require_once $src_path;
            }
        }
    }

    spl_autoload_register(__NAMESPACE__.'\Autoload::load', true, true);
}

?>