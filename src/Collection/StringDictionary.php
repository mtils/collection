<?php namespace Collection;

class StringDictionary extends Dictionary{

    public $rowDelimiter = "\n";
    public $keyValueDelimiter = '=>';
    public $prefix = '';
    public $suffix = '';

    public function __toString(){
        $rows = array();
        foreach($this as $key=>$value){
            $rows[] = "{$key}{$this->keyValueDelimiter}{$value}";
        }
        return $this->prefix . implode($this->rowDelimiter, $rows) . $this->suffix;
    }
}