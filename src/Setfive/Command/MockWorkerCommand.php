<?php

namespace Setfive\Command;

use Setfive\Gearman\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Counter {
  public static $total = 0;
}

class MockWorkerCommand extends Command {

    protected function configure() {

        $this->setName('setfive:mock-worker')
             ->setDescription('Registers a Gearman worker that does nothing, demonstrates weird variable behavior.')            
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
    
      $worker = new \GearmanWorker();
      $worker->addServer( "127.0.0.1" );
      
      $total = 0;
      
      $worker->addFunction("processUrl", function (\GearmanJob $job) use ($total) {
      
          Logger::getLogger()->addInfo("Processed: [" . $total . " | " . Counter::$total . "]");
                    
          $total += 1;
          Counter::$total += 1;
      });
            
      while( $worker->work() ){ 
        $this->exitOnZeroJobs();
      }    
    
    }

    private function exitOnZeroJobs(){
      exec("/usr/bin/gearadmin --status", $gearStatus);
      
      $jobsLeft = 0;
      foreach( $gearStatus as $line ){
      
        $statusParts = explode("\t", $line);    
        if( count($statusParts) < 4 ){
          continue;
        }
        
        $jobsLeft += intval($statusParts[1]);    
      }    
      
      if( $jobsLeft == 0 ){
        \Setfive\Gearman\Logger::getLogger()->addInfo("Zero jobs left. Existing");
        die();
      }
      
    }
    
}