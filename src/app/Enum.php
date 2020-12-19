<?php

namespace App;

abstract class Enum
{
    protected $value;

    public function __construct($value)
    {
        $class = new \ReflectionObject($this);
        $constants = $class->getConstants();
        if (in_array($value, $constants, true) === false) {
            throw new \InvalidArgumentException($value . ' is not a valid enum case');
        }

        $this->value = $value;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::dynamicallyMake($name);
    }

    private static function dynamicallyMake($label)
    {
        $keys = self::keys();
        if (in_array($label, $keys, true) === false) {
            throw new \InvalidArgumentException;
        }

        $class = get_called_class();
        $const = constant("$class::$label");
        /** @phpstan-ignore-next-line */
        return new $class($const);
    }

    public function valueOf()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string)$this->value;
    }


    public static function constants() : array
    {
        $class = new \ReflectionClass(get_called_class());
        return $class->getConstants() ?: [];
    }

    public static function keys() : array
    {
        return array_keys(self::constants()) ?: [];
    }

    public static function values() : array
    {
        return array_values(self::constants()) ?: [];
    }

    public static function fromRawValue(string $rawValue)
    {
        $constants = self::constants();
        foreach ($constants as $key => $value) {
            if ($value === $rawValue) {
                return self::dynamicallyMake($key);
            }
        }
        throw new \InvalidArgumentException($rawValue . ' is not a valid raw enum value');
    }
}
