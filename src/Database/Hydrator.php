<?php

namespace Fram\Database;

/**
 * Allows to hydrate the object with respect to the data.
 */
class Hydrator
{
    /**
     * Hydrate the object with the data.
     *
     * @param array $data The data, coming from the database.
     * @param string|mixed $object The object to hydrate (can be a string or
     *     an already existing object).
     * @return mixed The object hydrated.
     */
    public static function hydrate(array $data, $object)
    {
        if (is_string($object)) {
            $instance = new $object();
        } else {
            $instance = $object;
        }
        foreach ($data as $key => $value) {
            $method = self::getSetter($key);
            if (method_exists($instance, $method)) {
                $instance->$method($value);
            } else {
                $property = lcfirst(self::getProperty($key));
                $instance->$property = $value;
            }
        }
        return $instance;
    }

    /**
     * Generates the setter from the field name.
     * (e.g. 'field' becomes 'setField').
     *
     * @param string $fieldName
     * @return string
     */
    private static function getSetter(string $fieldName): string
    {
        return 'set' . self::getProperty($fieldName);
    }

    /**
     * Convert the field name to camel case.
     *
     * @param string $fieldName
     * @return string
     */
    private static function getProperty(string $fieldName): string
    {
        return join('', array_map('ucfirst', explode('_', $fieldName)));
    }
}
