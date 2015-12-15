<?php

namespace Naoned\PommBundle\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Parser;


class PopulatePommModel extends PommFixture
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    private $entities;

    /**
     * @var \Naoned\PommBundle\Manager\PommManager
     */
    private $pommManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pommManager = $this->container->get('naoned.pomm.pomm_manager');
        $this->entities = $this->getPopulateData();
    }

    private function getPopulateData()
    {
        return $this->getPopulateDataFromFile('populate.yml');
    }

    public function getPopulateDataFromFile($file)
    {
        $appDir = $this->container->get('kernel')->getRootDir();
        $populateFilePath = $appDir . '/schema/populate/' . $file;

        if (!file_exists($populateFilePath)) {
            throw new FileNotFoundException('No found populate file.');
        }

        $yaml = new Parser();
        return $yaml->parse(file_get_contents($populateFilePath));
    }


    public function get($entityName)
    {
        return isset($this->entities[$entityName]) ? $this->entities[$entityName] : [];
    }

    public function populate($modelClassName, $entityName, $otherData = [])
    {
        $model = $this->pommManager->getModel($modelClassName);
        $data = $this->entities[$entityName];

        if (count($data) == count($data, COUNT_RECURSIVE)) {
            $data = array_merge($data, $otherData);
            return $model->createAndSave($data);
        } else {
            $objects = [];
            foreach ($data as $oneData) {
                $oneData = array_merge($oneData, $otherData);
                $objects[] = $model->createAndSave($oneData);
            }
            return $objects;
        }
    }

    public function getPomm()
    {
        return $this->pommManager;
    }

    public function executePommQuery($query)
    {
        return $this->pommManager->getPommSession()->getConnection()->executeAnonymousQuery($query);
    }
}