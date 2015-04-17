<?php namespace Collection;


use ArrayAccess;
use Countable;
use RuntimeException;
use IteratorAggregate;
use Collection\Iterator\ArrayIterator;

/**
 * A NestedArray builds a nested array from a flat one with paths.
 *
 * e.g.:
 *
 * $array = [
 *      'id'            => 13,
 *      'name'          => 'Michael',
 *      'surname'       => 'Tils',
 *      'address.id'    => 578,
 *      'address.street'=> 'Elmstreet 13',
 *      'category.parent.id'    => 27,
 *      'category.parent.name'  => 'worker',
 *      'age'                   => 86,
 * ];
 *
 * Will be converted to:
 *
 * $array = [
 *          'id'            => 13,
 *          'name'          => 'Michael',
 *          'surname'       => 'Tils',
 *          'address' => [
 *              'id'    => 578,
 *               'street'=> 'Elmstreet 13',
 *           ],
 *          'category' => [
 *              'parent' => [
 *                  'id'    => 27,
 *                  'name'  => 'worker'
 *               ],
 *           ],
 *          'age'           => 86,
 * ];
 *
 * The "path" separator can be what you want and the query separator can be
 * a different. So you could query a file hierarchy with dots
 **/
class NestedArray implements ArrayAccess, Countable, IteratorAggregate
{

    /**
     * @var array
     */
    protected $array;

    /**
     * @var array
     **/
    protected $rootCache;

    /**
     * @var array
     **/
    protected $nestedCache;

    /**
     * @var string
     **/
    protected $querySeparator = '.';

    /**
     * @var string
     **/
    protected $separator = '.';

    public function __construct(array $array, $separator='.',
                                $querySeparator=null)
    {
        $this->setSrc($array);
        $this->separator = $separator;
        $this->querySeparator = $querySeparator ?: $separator;
    }

    /**
     * Checks if index $index exists in the array. Returns true if a direct
     * key matches or the first level of the hierarchy
     *
     * @param string $offset
     * @return bool
     **/
    public function offsetExists($offset)
    {

        if ($offset == $this->querySeparator) {
            return true;
        }

        if (isset($this->array[$offset])) {
            return true;
        }

        $nested = $this->nested();

        return isset($nested[$offset]);
    }

    /**
     * Returns the value of key $offset. Goes one level deep and returns the
     * value. If it is a array it will be returned.
     *
     * @param mixed $offset
     * @return mixed
     **/
    public function offsetGet($offset)
    {

        if ($offset == $this->querySeparator) {
            return $this->root();
        }

        if (isset($this->array[$offset])) {
            return $this->array[$offset];
        }

        return $this->group($offset);
    }

    /**
     * Setting values is not supported.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws \RuntimeException
     **/
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Setting values is not supported');
    }

    /**
     * Unsetting values is not supported.
     *
     * @param mixed $offset
     * @return void
     * @throws \RuntimeException
     **/
    public function offsetUnset($offset)
    {
        throw new RuntimeException('Currently only reading is supported');
    }

    /**
     * Returns the count of the root
     *
     * @return int
     **/
    public function count()
    {
        return count($this->root());
    }

    /**
     * Iterates over the object
     *
     * @return \Collection\Iterator\ArrayIterator
     **/
    public function getIterator()
    {
        return new ArrayIterator($this->nested());
    }

    /**
     * Returns subgroup with name $offset. Same as offsetGet
     *
     * @param mixed $offset
     * @return array (should, but dont have to)
     **/
    public function group($offset)
    {
        return static::get(
            $this->nested(), $offset, $this->querySeparator
        );
    }

    /**
     * Returns a filtered version which contains only unnested keys
     * This is handy for request hierarchies where you have a form which
     * contains some direct properties of your model and some nested for
     * relations. root() would than return only the root values
     *
     * @return array
     **/
    public function root()
    {

        if ($this->rootCache !== null) {
            return $this->rootCache;
        }

        $this->rootCache = $this->withoutNested(
            $this->array,
            $this->separator
        );

        return $this->rootCache;

    }

    /**
     * Returns the complete nested version of the source array
     *
     * @return array
     **/
    public function nested()
    {

        if( $this->nestedCache === null) {
            $this->nestedCache = static::toNested($this->array, $this->separator);
        }

        return $this->nestedCache;

    }

    /**
     * Returns a new NestedArray from subkey $offset
     *
     * @param mixed $offset
     * @return static
     **/
    public function sub($offset)
    {
        $array = $this->group($offset);
        if (is_array($array)) {
            return new static($this->group($offset));
        }
        return new static([]);
    }

    /**
     * Returns the source (flat) array
     *
     * @return array
     **/
    public function getSrc()
    {
        return $this->array;
    }

    /**
     * Sets a new (flat) source array
     *
     * @param array $src
     * @return static
     **/
    public function setSrc(array $src)
    {
        $this->array = $src;
        $this->reset();
        return $this;
    }

    /**
     * Direct access to the "array-nester". Put a flat array in this method
     * and it will return a recursivly nested version
     *
     * @param array $flat
     * @param string $delimiter
     * @return array
     **/
    public static function toNested(array $flat, $delimiter = '.')
    {

        $tree = [];

        foreach ($flat as $key => $val) {

            // Get parent parts and the current leaf
            $parts = explode($delimiter, $key);
            $leafPart = array_pop($parts);

            // Build parent structure
            $parent = &$tree;

            foreach ($parts as $part) {

                if (!isset($parent[$part])) {
                    $parent[$part] = [];
                } elseif (!is_array($parent[$part])) {
                    $parent[$part] = [];
                }

                $parent = &$parent[$part];
            }

            // Add the final part to the structure
            if (empty($parent[$leafPart])) {
                $parent[$leafPart] = $val;
            }
        }

        return $tree;
    }

    /**
     * Get a key from a nested array. Query a deeply nested array with
     * property.child.name
     *
     * @param array $nested
     * @param string $key
     * @param string $delimiter
     **/
    public static function get(array $nested, $key, $delimiter='.')
    {

        if (is_null($key)) return $nested;

        if (isset($nested[$key])) return $nested[$key];

        foreach (explode($delimiter, $key) as $segment) {
            if ( ! is_array($nested) || ! array_key_exists($segment, $nested))
            {
                return;
            }

            $nested = $nested[$segment];
        }

        return $nested;
    }

    /**
     * Removes all "nested" arrays from a flat array
     *
     * @param array $flat
     * @param string $separator
     * @return array
     **/
    public static function withoutNested(array $flat, $separator='.')
    {
        $root = [];

        foreach ($flat as $key=>$value) {
            if (strpos($key, $separator) === false) {
                $root[$key] = $value;
            }
        }

        return $root;
    }

    /**
     * Resets the cache
     *
     * @return void
     **/
    protected function reset()
    {
        $this->rootCache = null;
        $this->nestedCache = null;
    }
}