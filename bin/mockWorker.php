<?php

require_once dirname(__FILE__) . "/../vendor/autoload.php";

class Counter {
  public static $total = 0;
}

$worker = new \GearmanWorker();
$worker->addServer( "127.0.0.1" );

$total = 0;
$worker->addFunction("processUrl", function (\GearmanJob $job) use ($total) {

    \Setfive\Gearman\Logger::getLogger()->addInfo("Processed: [" . $total . " | " . Counter::$total . "]");
    
    $total += 1;
    Counter::$total += 1;
});

while( $worker->work() ){ 
  exitOnZeroJobs();
}

function exitOnZeroJobs(){
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