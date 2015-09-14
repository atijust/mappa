<?php
namespace Mappa;

class Hydrator
{
    /** @var callable */
    private $convertClassToTableCallback;

    /**
     * @param callable $convertClassToTableCallback
     */
    public function __construct(callable $convertClassToTableCallback = null)
    {
        $this->convertClassToTableCallback = $convertClassToTableCallback ?: function ($class) {
            return Inflector::snakecase(Inflector::pluralize(Inflector::basename($class)));
        };
    }

    /**
     * @param \PDOStatement $stmt
     * @param string|string[] $classes
     * @return array|bool
     */
    public function hydrate(\PDOStatement $stmt, $classes = [])
    {
        $classMap = $this->createClassMap(is_array($classes) ? $classes : [$classes]);

        $row = $stmt->fetch(\PDO::FETCH_NUM);
        if (!$row) {
            return false;
        }

        $fields = $this->getFields($stmt);

        return $this->doHydrate($row, $fields, $classMap);
    }

    /**
     * @param \PDOStatement $stmt
     * @param string|string[] $classes
     * @return array
     */
    public function hydrateAll(\PDOStatement $stmt, $classes = [])
    {
        $classMap = $this->createClassMap(is_array($classes) ? $classes : [$classes]);

        $rows = [];
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $fields = $this->getFields($stmt);
            $rows[] = $this->doHydrate($row, $fields, $classMap);
        }

        return $rows;
    }

    /**
     * @param array $row
     * @param array $fields
     * @param array $classMap
     * @return object[]
     */
    private function doHydrate(array $row, array $fields, array $classMap)
    {
        $objects = [];
        foreach ($classMap as $table => $class) {
            $objects[$table] = null;
        }

        foreach ($row as $n => $v) {
            $field = $fields[$n];

            if (isset($field['table']) && array_key_exists($field['table'], $objects)) {
                $table = $field['table'];
            } else {
                $table = '';
            }


            if (is_null($objects[$table])) {
                $objects[$table] = new $classMap[$table]();
            }

            $objects[$table]->{$field['name']} = $v;
        }

        return $objects;
    }

    /**
     * @param \PDOStatement $stmt
     * @return array
     */
    private function getFields(\PDOStatement $stmt)
    {
        $fields = [];

        $columnCount = $stmt->columnCount();

        for ($i = 0; $i < $columnCount; $i++) {
            $fields[$i] = $stmt->getColumnMeta($i);
        }

        return $fields;
    }

    /**
     * @param array $classes
     * @return string[]
     */
    private function createClassMap(array $classes)
    {
        $classMap = [];

        foreach ($classes as $table => $class) {
            $classMap[is_string($table) ? $table : $this->convertClassToTable($class)] = $class;
        }

        if (!isset($classMap[''])) {
            $classMap[''] = \StdClass::class;
        }

        return $classMap;
    }

    /**
     * @param string $class
     * @return string
     */
    private function convertClassToTable($class)
    {
        return call_user_func($this->convertClassToTableCallback, $class);
    }
}