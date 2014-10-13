<?php

namespace Setfive\Command;

use Setfive\Gearman\Logger;
use Setfive\Gearman\Master;
use Setfive\Gearman\Node;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartScrapeCommand extends Command {

    protected function configure() {

        $this->setName('setfive:start-scraper')
             ->setDescription('Starts the scraper by initializing Gearman jobs.')
            ->addArgument('filename', InputArgument::REQUIRED, 'Which filename to queue up.')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $filename = $input->getArgument("filename");
        $targetFile = dirname(__FILE__) . "/../../../bin/" . $filename;

        if( !file_exists($targetFile) ){
            $output->write("Sorry! " . $filename . " does not seem to exist in the bin directory.");
            return;
        }

        $master = new Master();
        $handle = fopen( $targetFile, "r+" );

        while( ($line = fgets($handle)) ){
            $url = "http://" . trim($line);
            $master->queueUrlForKeywords( $url );
        }

    }

}