<?php

namespace Naoned\PommBundle\DataFixtures;

abstract class PommFixture
{
    /**
     * Store les références
     * @var array
     */
    protected $references;

    public function __construct()
    {
        $this->references = [];
    }

    public function getReferences()
    {
        return $this->references;
    }

    public function setReferences($references)
    {
        return $this->references = $references;
    }

    public function addReference($name, $value)
    {
        $this->references[$name] = $value;
    }

    public function getReference($name)
    {
        if (!isset($this->references[$name])) {
            return '';
        }

        return $this->references[$name];
    }
}