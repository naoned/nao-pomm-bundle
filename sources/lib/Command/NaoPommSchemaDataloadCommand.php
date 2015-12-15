<?php

namespace Naoned\PommBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpnaoSchemaDataloadCommand
 * @package Naoned\PommBundle\Command
 */
class NaoPommSchemaDataloadCommand extends ContainerAwareCommand
{
    const CMD_OUTPUT_SIZE = 100;

    private $fixtures;

    protected function configure()
    {
        $this
            ->setName('naopomm:schema:dataload')
            ->setDescription('Load data fixtures to your database.')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures instead of deleting all data from the database first.')
            ->setHelp(<<<EOT
This command load data into your database using Pomm
EOT
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Recherche des fixtures Pomm
        $paths = [];
        foreach ($this->getApplication()->getKernel()->getBundles() as $bundle) {
            $paths[] = $bundle->getPath().'/DataFixtures/Pomm';
        }
        // Recherche des classes à charger
        $fixtures = [];
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $fixtures = $this->loadFromDirectory($path);
            }
        }
        // Exécution des fixtures
        $style = new OutputFormatterStyle('red', null);
        $output->getFormatter()->setStyle('delete', $style);

        //Erase
        if (!$input->getOption('append')) {
            $output->writeln('<fg=white;bg=red>'. str_repeat(' ', self::CMD_OUTPUT_SIZE) .'</>');
            $output->writeln('<fg=white;bg=red>'. sprintf('    %-'. (self::CMD_OUTPUT_SIZE - 4) .'s', 'Delete existing data') .'</>');
            $output->writeln('<fg=white;bg=red>'. str_repeat(' ', self::CMD_OUTPUT_SIZE) .'</>');

            $i = 0;
            foreach ($fixtures as $fixture) {
                $output->write(
                    '<fg=red>'.
                    sprintf('%2s', ++$i) .
                    sprintf("%'.-". (self::CMD_OUTPUT_SIZE - 5) .'s', ' - ' . get_class($fixture)) .
                    '</>'
                );
                $fixture->erase();
                $output->writeln('<fg=red;options=bold> OK</>');
            }
            $output->writeln('');
        }

        //Load fixures
        $output->writeln('<bg=green>'. str_repeat(' ', self::CMD_OUTPUT_SIZE) .'</>');
        $output->writeln('<fg=white;bg=green>'. sprintf('    %-'. (self::CMD_OUTPUT_SIZE - 4) .'s', 'Load data fixtures') .'</>');
        $output->writeln('<bg=green>'. str_repeat(' ', self::CMD_OUTPUT_SIZE) .'</>');

        $references = [];

        $i = 0;
        foreach ($fixtures as $fixture) {
            $output->write(
                '<fg=green>'.
                sprintf('%2s', ++$i) .
                sprintf("%'.-". (self::CMD_OUTPUT_SIZE - 5) .'s', ' - ' . get_class($fixture)) .
                '</>'
            );
            $fixture->setReferences($references);
            $fixture->load();
            $references = $fixture->getReferences();
            $output->writeln('<fg=green;options=bold> OK</>');
        }
    }

    protected function loadFromDirectory($path)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $includedFiles = [];
        foreach ($iterator as $file) {
            if ($file->getBasename('.php') == $file->getBasename()) {
                continue;
            }
            $sourceFile = realpath($file->getPathName());
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }

        $this->fixtures = [];
        $declared       = get_declared_classes();
        foreach ($declared as $className) {
            $reflClass  = new \ReflectionClass($className);
            $sourceFile = $reflClass->getFileName();

            if (in_array($sourceFile, $includedFiles) && ! $this->isTransient($className)) {
                $fixture          = new $className($this->getContainer());
                $this->fixtures[] = $fixture;
            }
        }

        usort($this->fixtures, ['self', 'fixturesOrdering']);

        return $this->fixtures;
    }

    /**
     * @param $orderA
     * @param $orderB
     * @return int
     */
    private static function fixturesOrdering($orderA, $orderB)
    {
        if (method_exists($orderA, 'getOrder') && method_exists($orderA, 'getOrder')) {
            return $orderA->getOrder() - $orderB->getOrder();
        } elseif (method_exists($orderB, 'getOrder')) {
            return -1;
        } else {
            return 1;
        }
    }

    /**
     * @param string $className
     * @return bool
     */
    private function isTransient($className)
    {
        $refClass = new \ReflectionClass($className);

        return ($refClass->isAbstract()) ? true : false;
    }
}
