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

class GearmanCommand extends Command {

    protected function configure(){

        $this
            ->setName('setfive:gearman')
            ->setDescription('Runs the gearman worker or master')
            ->addArgument('type', InputArgument::REQUIRED, 'Pass "node" to run the node or master to run a master.')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $type = $input->getArgument('type');

        if( $type == "master" ){
            $worker = new Master();
        }else{
            $worker = new Node();
        }

        $worker->work();
    }

}