<?php
namespace Mappa;

use Doctrine\Common\Inflector\Inflector as DoctrineInflector;

class Inflector
{
    /**
     * @param string $class
     * @return string
     */
    public static function basename($class)
    {
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * @param string $s
     * @return string
     */
    public static function pluralize($s)
    {
        return DoctrineInflector::pluralize($s);
    }

    /**
     * @param string $s
     * @return string
     */
    public static function snakecase($s)
    {
        return strtolower(preg_replace('/(.)(?=[A-Z])/', '$1_', $s));
    }
}