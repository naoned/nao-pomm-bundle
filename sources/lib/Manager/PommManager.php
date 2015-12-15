<?php

namespace Naoned\PommBundle\Manager;

use PommProject\Foundation\Pomm;
use PommProject\Foundation\Session\Session as PommSession;
use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\ModelLayer\ModelLayer;
use Psr\Log\LoggerInterface;

/**
 * Class PommManager
 * @package Naoned\PommBundle\Manager
 */
class PommManager
{
    /**
     * @var Pomm
     */
    private $pomm;
    private $logger;

    /**
     * @param Pomm $pomm
     * @throws \PommProject\Foundation\Exception\FoundationException
     */
    public function setPomm(Pomm $pomm)
    {
        $this->pomm   = $pomm;
        $this->logger = $this->pomm->getDefaultSession()->getLogger();
    }

    /**
     * @return PommSession
     */
    public function getPommSession($name = null)
    {
        if ($name) {
            return $this->pomm->getSession($name);
        }
        return $this->pomm->getDefaultSession();
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string      $modelClassName
     * @param null|string $sessionName
     * @return Model
     */
    public function getModel($modelClassName, $sessionName = null)
    {
        return $this->getPommSession($sessionName)->getModel($modelClassName);
    }

    /**
     * @param string      $modelLayerClassName
     * @param null|string $sessionName
     * @return ModelLayer
     */
    public function getModelLayer($modelLayerClassName, $sessionName = null)
    {
        return $this->getPommSession($sessionName)->getModelLayer($modelLayerClassName);
    }
}
