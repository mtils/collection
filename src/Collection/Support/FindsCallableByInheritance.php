<?php


namespace Collection\Support;

use ReflectionClass;

/**
 * This trait allows to assign callables as factories, providers or something
 * similar. This trait finds callables which are nearer (by class hierarchy)
 * assigned. So for example if you have:
 *
 * class Model{}
 * class User extends Model{}
 * class CmsUser extends User{}
 *
 * If you assign a callable via addCallable('Model', $callable);
 * this on will be used as long as you do not assign: addCallable(User, $callable)
 * The inheritance will be searched from top down and return the first matching
 * provider
 * @deprecated Seems to be not used anymore
 **/
trait FindsCallableByInheritance
{

    /**
     * @var array
     **/
    protected array $_callables = [
        'default' => []
    ];

    /**
     * Cache
     *
     * @var array
     **/
    protected array $_class2Callable = [];


    /**
     * Add a callable for class $class. The third parameter allows to have
     * multiple stores for callables
     *
     * @param string $class
     * @param callable $provider
     * @param string $type (optional)
     * @return self
     **/
    public function addCallable(string $class, callable $provider, string $type='default') : static
    {
        if (!isset($this->_callables[$type])) {
            $this->_callables[$type] = [];
        }
        $this->_callables[$type][$class] = $provider;
        return $this;
    }

    /**
     * This method finds the best matching callable for class $class.
     * It decorates findNearestForClass to cache the result
     *
     * @param string $findClass
     * @param string $type (optional)
     * @return callable|null
     **/
    protected function nearestForClass(string $findClass, string $type='default') : ?callable
    {

        $cacheId = "$type|$findClass";

        if (isset($this->_class2Callable[$cacheId])) {
            return $this->_class2Callable[$cacheId];
        }

        if (!isset($this->_callables[$type])) {
            return null;
        }

        if (isset($this->_callables[$type][$findClass])) {
            $this->_class2Callable[$type][$findClass] = $this->_callables[$type][$findClass];
            return $this->_callables[$type][$findClass];
        }

        $providers = &$this->_callables[$type];

        if (!$nearest = $this->findNearestForClass($providers, $findClass)) {
            return null;
        }

        $this->_class2Callable[$type][$findClass] = $nearest;

        return $nearest;

    }

    /**
     * This method does the actual work to find the best matching callable for
     * class $class
     *
     * @param array $providers
     * @param string $findClass
     * @return callable|null
     **/
    protected function findNearestForClass(array $providers, string $findClass) : ?callable
    {
        $all = $this->findAllForClass($providers, $findClass);

        if (!count($all)) {
            return null;
        }

        if (count($all) == 1) {
            return array_values($all)[0];
        }

        foreach (static::classInheritance($findClass) as $parentClass) {
            if (isset($all[$parentClass]) ) {
                return $all[$parentClass];
            }
        }

        return null;

    }

    /**
     * Returns all providers which are assigned for $findClass or one of its
     * parent classes
     *
     * @param array $providers
     * @param string $findClass
     * @return array
     **/
    protected function findAllForClass(array $providers, string $findClass) : array
    {

        $all = [];

        foreach ($providers as $class=>$provider) {
            if (is_subclass_of($findClass, $class) || $findClass == $class) {
                $all[$class] = $provider;
            }
        }

        return $all;

    }

    /**
     * Returns an array of the classname and all parent class names of
     * the passed class or object
     *
     * @param object|string $object
     * @return array
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function classInheritance(object|string $object) : array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $class = new ReflectionClass($object);
        $classNames = [$class->getName()];

        while($class = $class->getParentClass()){
            $classNames[] = $class->getName();
        }

        return $classNames;

    }

}