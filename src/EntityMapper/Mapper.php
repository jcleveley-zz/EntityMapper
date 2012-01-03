<?php

/**
 * Class to transform array of data from nested arrays (json_decode) to PHP custom objects
 * based on a map describing the data structure. See unit test for usgae.
 *
 * @todo Im passing variables round like a crazy person but can't think of a better way to do it :(
 * @todo Performance? Can we use reflection? use public prop / set methods instead?
 * @todo Add flag in mapping for when we inject data into constructor e.g. DateTime curretly pretty dumb
 * @todo Max recursion_count ?
 */

namespace EntityMapper;

use ReflectionClass;
use Exception;

class Mapper
{
    /**
     * @var Array Map describing class meta
     */
    protected $map;

    /**
     *  @var Bool
     *  Whether a property with the same name as the data key should be auto set
     *  true: properties will be mapped automaically if they have the same name
     *  false: you have to explicitly add properties to the map
     */
    protected $allowAutoPropertySetting;

    /**
     * @param Array $map Map describing class meta
     */
    public function __construct(Array $map = array(), $allowAutoPropertySetting = false)
    {
        $this->map = $map;
        $this->allowAutoPropertySetting = $allowAutoPropertySetting;
    }

    /**
     * Recursively hydrates (makes objects) from an array of data
     *
     * @param $data Raw data
     * @param String $className class name of object which be used to hydrate
     * @param Int $depth (used during recursion) depth of target class within arrays
     */
    public function hydrate($data, $className = null, $depth = 0, $lastStringKey = null)
    {
        // Maps to a PHP object - properties will be mapped including nested obj
        if (is_array($data) && $className && $depth == 0) {
            $entity = $this->createEntity($data, $className, $lastStringKey);
            $output = $this->updateEntity($data, $entity, $lastStringKey);
        // Maps to an Array - classname and depth are carried forward for nested obj
        } elseif (is_array($data)) {
            $output = $this->updateArray($data, array(), $className, $depth, $lastStringKey);
        // Maps to a PHP object - data will be injected into constructor
        } elseif (!is_array($data) && $className) {
            $output = $this->createInjectedEntity($data, $className);
        // Maps to normal variable
        } else {
            $output = $data;
        }

        return $output;
    }

    /**
     * Creates an object based on mapping
     * Uses _new closure if present to customise
     *
     * @param Mixed $data Subset of data
     * @param String $className
     */
    protected function createEntity($data, $className, $lastStringKey)
    {
        if (isset($this->map[$className]['_new']) && is_callable($this->map[$className]['_new'])) {
            return $this->map[$className]['_new']($data, $lastStringKey);
        } else {
            return new $className;
        }
    }

    /**
     * Updates an object based on mapping
     *
     * @param Mixed $data Subset of data
     * @param Obj $entity Object to be updated
     */
    protected function updateEntity($data, $entity, $lastStringKey)
    {
        $className = get_class($entity);
        $reflClass = new ReflectionClass($className);

        foreach ($data as $key => $value) {
            $field = $this->mapField($className, $key);

            $value = $this->hydrate(
                $value,
                $this->getChildClass($field),
                $this->getDepth($field),
                $this->getStringKey($key, $lastStringKey)
            );
            $property = $this->getProperty($field);
            if ($property && $reflClass->hasProperty($property)) {
                $reflProp = $reflClass->getProperty($property);
                $reflProp->setAccessible(true);
                $reflProp->setValue($entity, $value);
            }
        }
        return $entity;
    }

    /**
     * Maps data to an Array, every value goes through recursion
     * Depth gets reduced on every array level created
     *
     * @param Mixed $data Raw data
     * @param Array $newArray
     * @param String $className Class anme of nested object(s)
     * @param Int $depth Number of levels until we can expect an object
     */
    protected function updateArray($data, Array $newArray, $className, $depth, $lastStringKey)
    {
        $depth--;
        foreach ($data as $key => $value) {
            $newArray[$key] = $this->hydrate($value, $className, $depth, $this->getStringKey($key, $lastStringKey));
        }
        return $newArray;
    }

    /**
     * Creates an injected object based on mapping
     * Ignores exceptions such as DateTime('notaddate');
     *
     * @param String $className
     * @param Mixed $data Subset of data
     */
    protected function createInjectedEntity($data, $className)
    {
        try {
            return new $className($data);
        } catch (Exception $e) {
            return null;
        }
    }

    protected function getStringKey($key, $lastStringKey)
    {
        return is_string($key) ? $key : $lastStringKey;
    }

    protected function mapField($className, $key)
    {
        $fallback = ($this->allowAutoPropertySetting) ? array('name' => $key) : null;

        return isset($this->map[$className][$key]) ? $this->map[$className][$key] : $fallback;
    }

    protected function getDepth($field)
    {
        return isset($field['depth']) ? $field['depth'] : 0;
    }

    protected function getChildClass($field)
    {
        return isset($field['class']) ? $field['class'] : null;
    }

    protected function getProperty($field)
    {
        return isset($field['name']) ? $field['name'] : null;
    }

}