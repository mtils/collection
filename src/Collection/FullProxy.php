<?php namespace Collection;

use ArrayAccess;
use ReturnTypeWillChange;
use TypeError;

use function get_class;
use function is_callable;

class FullProxy implements ArrayAccess
{

    protected object $src;

    /**
     * @var array<string,string>
     */
    protected static array $staticClassNames = [];

    public function __construct(?object $src=null){
        $this->setSrc($src);
    }

    public function getSrc() : object
    {
        return $this->src;
    }

    public function setSrc(object $src) : static
    {
        $this->src = $src;
        self::$staticClassNames[static::class] = get_class($src);
        return $this;
    }

    public function __get(string $name) : mixed
    {
        if($name == 'src'){
           return $this->src;
        }
        return $this->src->{$name};
    }

    public function __set(string $name, mixed $value) : void
    {
        $this->src->{$name} = $value;
    }

    public function __isset(string $name) : bool
    {
        return isset($this->src->{$name});
    }

    public function __unset(string $name) : void
    {
        unset($this->src->{$name});
    }

    public function __call(string $method, array $args) : mixed
    {
        return call_user_func_array(
            array($this->src, $method),
            $args
        );
    }

    /**
     * @deprecated  This is not possible...
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        return call_user_func_array(
            array(static::$staticClassNames[static::class], $method),
            $args
        );
    }

    /**
     * @param mixed ...$args
     * @return mixed
     */
    public function __invoke(...$args) : mixed
    {
        $src = $this->src;
        if (!is_callable($src)) {
            throw new TypeError('The source is not callable');
        }
        return $src(...$args);
    }

    public function __toString() : string
    {
        return (string)$this->src;
    }

    #[ReturnTypeWillChange]
    public function offsetExists(mixed $offset) : bool
    {
        return isset($this->src[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset) : mixed
    {
        return $this->src[$offset];
    }

    #[ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $this->src[$offset] = $value;
    }

    #[ReturnTypeWillChange]
    public function offsetUnset(mixed $offset) : void
    {
        unset($this->src[$offset]);
    }

}