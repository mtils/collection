<?php

namespace Collection;

class StringList extends OrderedList{

    public string $delimiter = ' ';
    public string $prefix = '';
    public string $suffix = '';

    public function __construct(?array $src=null, string $delimiter=' '){
        parent::__construct($src);
        $this->delimiter = $delimiter;
    }

    public function __toString() : string
    {
        return $this->prefix . implode($this->delimiter, $this->_array) .
               $this->suffix;
    }

    /**
     * Creates a Stringlist from a string.
     *
     * @param string $string
     * @param string $separator
     * @return StringList
     */
    public static function fromString(string $string, string $separator=' ') : static
    {

        if($separator === ''){
            return new StringList(str_split($string),'');
        }

        $prefix = '';
        $suffix = '';
        if($string[0] == $separator){
            $prefix = $separator;
        }
        if($string[strlen($string)-1] == $separator){
            $suffix = $separator;
        }
        $strList = new StringList(explode($separator,trim($string,$separator)),
                                          $separator);
        $strList->prefix = $prefix;
        $strList->suffix = $suffix;
        return $strList;
    }

    /**
     * Returns a copy
     *
     * @return StringList
     * @see OrderedList::copy()
     */
    public function copy() : static
    {
        $copy = parent::copy();
        $copy->delimiter = $this->delimiter;
        $copy->prefix = $this->prefix;
        $copy->suffix = $this->suffix;
        return $copy;
    }
}