<?php

/*
 *  @class Command\CreateDryRun - php commander create:dryrun
 *
 * Author: Nino Labrador (nino.labrador@codingavenue.com)
 */

namespace WetWalk\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use WetWalk\MakeDry;

class CreateDryRun extends Command
{

    /**
     * Method that configures the parameters needed for the console command
     */
    protected function configure()
    {
        $this
            ->setName('create:dryrun')
            ->addArgument('target_path', InputArgument::REQUIRED, 'Target Directory or File Path to be parsed and create new copy dryrun files.')
            ->addOption(
                'other-methods',
                null,
                InputOption::VALUE_OPTIONAL,
                'Other custom created methods not in PHP built-in methods. Comma separated values.'
            )
            ->addOption(
                'skip-methods',
                null,
                InputOption::VALUE_OPTIONAL,
                'Method calls to skip. Comma separated values.'
            )
        ;
    }

    /**
     * Method that executes create:dryrun command
     * Create dryrun version of a PHP file
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = explode(",", $input->getOptions()['other-methods']);
        $skip    = explode(",", $input->getOptions()['skip-methods']);

        $makedry = new MakeDry($input->getArgument('target_path'), $options, $skip);
        $makedry->convert();
    }
}
