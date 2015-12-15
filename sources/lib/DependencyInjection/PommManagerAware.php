<?php

namespace Naoned\PommBundle\DependencyInjection;

use Naoned\PommBundle\Manager\PommManager;
use Psr\Log\LoggerInterface;

/**
 * Class PommManagerAware
 * @package Naoned\PommBundle\DependencyInjection
 */
abstract class PommManagerAware implements PommManagerAwareInterface
{
    /**
     * @var PommManager
     */
    protected $pommManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param PommManager|null $pommManager
     */
    public function setPommManager(PommManager $pommManager = null)
    {
        $this->pommManager = $pommManager;
        $this->logger      = $pommManager->getLogger();
    }
}
