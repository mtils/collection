<?php namespace Collection\Iterator;

use \Iterator;

interface IteratorInterface extends Iterator{
    public function first();
    public function last();
}