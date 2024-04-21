<?php namespace Collection;

class ClassNamer
{

    public static function baseName(object|string $phpClass) : string
    {
        return basename(str_replace('\\', '/', self::getClass($phpClass)));
    }

    public static function cssClass(object|string $phpClass) : string
    {
        $baseName = self::baseName($phpClass);
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '-$1', $baseName));
    }

    protected static function getClass(object|string $classOrObject) : string
    {

        if(is_object($classOrObject)){
            return get_class($classOrObject);
        }

        return $classOrObject;

    }

}