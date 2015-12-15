<?php

namespace Naoned\PommBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class NaoPommSchemaCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('naopomm:schema:create')
            ->setDescription('Creates PostgreSQL schemas')
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info>command creates schemas for upNao.

EOT
            )
            //@todo: remplacer par no-interactive function interact()
            ->addOption('test', null, InputOption::VALUE_NONE, 'For tests (no prompt)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaFile = sprintf('%s/schema/schema.sql', $this->getContainer()->get('kernel')->getRootDir());
        if (!file_exists($schemaFile)) {
            $output->writeln(sprintf("<error>File [%s] is not found</error>", $schemaFile));
            die();
        }
        $this->getPommSession()->getConnection()->executeAnonymousQuery(
            file_get_contents($schemaFile)
        );
        $output->writeln("<info>Creation of schemas ok</info>");
        $output->writeln("");
    }

    protected function getPommSession()
    {
        return $this->getContainer()->get('pomm')->getDefaultSession();
    }

}