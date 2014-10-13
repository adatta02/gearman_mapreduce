<?php

require_once dirname(__FILE__) . "/../vendor/autoload.php";

$worker = new \GearmanWorker();
$worker->addServer( "127.0.0.1" );

$total = 0;
$worker->addFunction("processUrl", function (\GearmanJob $job) use ($total) {
    \Setfive\Gearman\Logger::getLogger()->addInfo("Processed: " . $total . " jobs!");
    $total += 1;
});

while( $worker->work() ){ }