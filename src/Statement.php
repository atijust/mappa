<?php
namespace Mappa;

class Statement extends \PDOStatement
{
    /** @var \Mappa\Hydrator */
    private $hydrator;

    /**
     * @param \Mappa\Hydrator $hydrator
     */
    protected function __construct(Hydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @param string|string[] $classes
     * @return array|bool
     */
    public function hydrate($classes = [])
    {
        return $this->hydrator->hydrate($this, $classes);
    }

    /**
     * @param string|string[] $classes
     * @return array
     */
    public function hydrateAll($classes = [])
    {
        return $this->hydrator->hydrateAll($this, $classes);
    }
}