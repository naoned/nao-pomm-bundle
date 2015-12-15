<?php

namespace Naoned\PommBundle\DependencyInjection;

use Naoned\PommBundle\Manager\PommManager;

/**
 * Interface PommManagerAwareInterface
 * @package Naoned\PommBundle\DependencyInjection
 */
interface PommManagerAwareInterface
{
    /**
     * @param PommManager|null $pommManager
     */
    public function setPommManager(PommManager $pommManager = null);
}
