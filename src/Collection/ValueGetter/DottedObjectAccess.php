<?php namespace Collection\ValueGetter;

use DateTime;
use Collection\Column;

class DottedObjectAccess
{

    public string $dot = '.';

    public function __invoke(Column $column, $src, $accessor) : string
    {

        if(!str_contains($accessor, $this->dot)){
            return $src->$accessor;
        }

        $parts = explode($this->dot, $accessor);

        $node = $src;

        foreach ($parts as $part) {

            $node = $node->$part;

            if(!is_object($node) || $node instanceof DateTime){
                return $node;
            }

        }

        return '';
    }

}