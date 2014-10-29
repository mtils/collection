<?php namespace Collection;

class ClassNamer{

    public static function baseName($phpClass){
        return basename(str_replace('\\', '/', self::getClass($phpClass)));
    }

    public static function cssClass($phpClass){
        $baseName = self::baseName($phpClass);
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '-$1', $baseName));
    }

    protected static function getClass($classOrObject){

        if(is_object($classOrObject)){
            return get_class($classOrObject);
        }

        return $classOrObject;

    }

}