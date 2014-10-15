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

class NoGearmanCommand extends Command {

    protected function configure() {

        $this->setName('setfive:no-gearman-scraper')
             ->setDescription('Starts the scraper by initializing Gearman jobs.')
             ->addArgument('filename', InputArgument::REQUIRED, 'Which filename to queue up.')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $filename = $input->getArgument("filename");
        $targetFile = dirname(__FILE__) . "/../../../bin/site_lists/" . $filename;

        if( !file_exists($targetFile) ){
            Logger::getLogger()->addInfo("Sorry! " . $filename . " does not seem to exist in the bin/site_lists directory.\n");
            return;
        }

        $master = new Master();
        $node = new Node();
        
        $start = time();
        $handle = fopen( $targetFile, "r+" );        
        
        while( ($line = fgets($handle)) ){
        
            if( strlen(trim($url)) == 0 ){
              continue;
            }
        
            Logger::getLogger()->addInfo("Fetching " . $url );
            
            $url = "http://" . trim($line);            
            $keywords = $node->getKeywordsForUrl( $url );
            $master->countKeywords( ["keywords" => $keywords], "nogearman_keyword_results.json" );            
        }

        $totalTime = time() - $start;
        Logger::getLogger()->addInfo("Total time: " . $totalTime);
    }

}