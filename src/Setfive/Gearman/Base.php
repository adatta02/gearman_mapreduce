<?php

namespace Setfive\Gearman;

class Base {

    const SERVER_IP = "127.0.0.1";

    private $gearmanClient = null;
    private $gearmanWorker = null;
    protected $startedAt = null;
    protected $exitOnZero = false;
    
    public function work(){

        $gearman = $this->getGearmanWorker();
        $that = $this;

        foreach( $this->getAvailableJobs() as $functionName ) {

            $gearman->addFunction($functionName, function (\GearmanJob $job) use ($that, $functionName) {
                $payload = json_decode($job->workload(), true);
                call_user_func(array($that, $functionName), $payload);
            });

            Logger::getLogger()->addInfo("Registering " . get_class($this) . "::" . $functionName);
        }
                
        while( $gearman->work() ){ 
          
          if( $this->exitOnZero ){
            $this->exitOnZeroJobs();
          }
          
        }
    }

    public function getGearmanClient(){

        if( $this->gearmanClient ){
            return $this->gearmanClient;
        }

        $server = self::SERVER_IP;
        $this->gearmanClient = new \GearmanClient();
        $this->gearmanClient->addServer($server);

        return $this->gearmanClient;
    }

    public function getGearmanWorker(){

        if( $this->gearmanWorker ){
            return $this->gearmanWorker;
        }

        $server = self::SERVER_IP;
        $this->gearmanWorker = new \GearmanWorker();
        $this->gearmanWorker->addServer( self::SERVER_IP );

        return $this->gearmanWorker;
    }

    public function getAvailableJobs(){
        return [ ];
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
      
      Logger::getLogger()->addInfo("Remaining jobs: " . $jobsLeft);
      
      if( $jobsLeft == 0 ){
        $totalTime = time() - $this->startedAt;
        Logger::getLogger()->addInfo("Zero jobs left. Total time: " . $totalTime);
        die();
      }
      
    }    
}