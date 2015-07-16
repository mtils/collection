<?php


namespace Collection;

use Countable;
use IteratorAggregate;
use ArrayAccess;

class CallableSet implements Countable, IteratorAggregate, ArrayAccess
{

    use CallableSetTrait;

}