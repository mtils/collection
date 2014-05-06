<?php namespace Collection;

interface CollectionCreator{
    public function toArray();
    public function toList();
    public function toDictionary();
    public function toMap($extractor=NULL);
}