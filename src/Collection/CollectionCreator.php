<?php namespace Collection;

interface CollectionCreator{
    public function toArray() : array;
    public function toList() : OrderedList;
    public function toDictionary() : Dictionary;
    public function toMap(array|callable|null $extractor=null) : Map;
}