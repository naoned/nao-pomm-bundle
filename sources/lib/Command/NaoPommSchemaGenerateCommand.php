<?php

namespace Naoned\PommBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class NaoPommSchemaGenerate
 * @package Naoned\PommBundle\Command
 */
class NaoPommSchemaGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('naopomm:schema:generate')
            ->setDescription('Generate database schema')
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info> command update schema for upnao.
EOT
            )
            ->addOption('hostname', 'host', InputOption::VALUE_OPTIONAL, 'Database server hostname')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Database server port')
            ->addOption('database', 'd', InputOption::VALUE_OPTIONAL, 'Database name')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Database username')
            ->addOption('password', 'pwd', InputOption::VALUE_OPTIONAL, 'Database user password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = sprintf('%1$s/schema/schema.sql',  $this->getContainer()->get('kernel')->getRootDir());

        $db_hostname = $input->getOption('hostname') ?
                $input->getOption('hostname') : $this->getContainer()->getParameter('database_host');
        $db_port     = $input->getOption('port') ?
            $input->getOption('port') : $this->getContainer()->getParameter('database_port');
        $db_name     = $input->getOption('database') ?
            $input->getOption('database') : $this->getContainer()->getParameter('database_name');
        $db_user     = $input->getOption('username') ?
            $input->getOption('username') : $this->getContainer()->getParameter('database_user');
        $db_password = $input->getOption('password') ?
            $input->getOption('password') : $this->getContainer()->getParameter('database_password');


        $cmd  = sprintf(
            'PGPASSWORD="%6$s" pg_dump -h %2$s -p %3$d -d %4$s -U %5$s -s -O -x > %1$s',
            $file,
            $db_hostname,
            $db_port,
            $db_name,
            $db_user,
            $db_password
        );
        $dump = new Process($cmd);
        $dump->setTimeout(3600);
        $dump->run();
        if (!$dump->isSuccessful()) {
            $output->writeln($cmd);
            throw new \RuntimeException($dump->getErrorOutput());
        }
        $output->writeln(sprintf(
            '<info>Schema generation OK in file: %s</info>',
            $file
        ));
    }
}