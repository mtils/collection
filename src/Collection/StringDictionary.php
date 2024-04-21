<?php namespace Collection;

class StringDictionary extends Dictionary
{

    public string $rowDelimiter = "\n";
    public string $keyValueDelimiter = '=>';
    public string $prefix = '';
    public string $suffix = '';

    public function __toString() : string
    {
        $rows = [];
        foreach($this as $key=>$value){
            $rows[] = "{$key}{$this->keyValueDelimiter}{$value}";
        }
        return $this->prefix . implode($this->rowDelimiter, $rows) . $this->suffix;
    }
}