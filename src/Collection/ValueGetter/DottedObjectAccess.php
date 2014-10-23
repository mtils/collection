<?php namespace Collection\ValueGetter;

use Collection\Column;

class DottedObjectAccess{

    public $dot = '.';

    public function __invoke(Column $column, $src, $accessor){

        if(strpos($accessor,$this->dot) === FALSE){
            return $src->$accessor;
        }

        $parts = explode($this->dot, $accessor);

        $node = $src;
        foreach($parts as $part){
            if($node = $node->$part){
                if(!is_object($node)){
                    return $node;
                }
            }
        }

        return $accessor;
    }

}