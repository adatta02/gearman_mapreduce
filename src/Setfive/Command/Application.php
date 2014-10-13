<?php

namespace Setfive\Command;

use Setfive\Command\GearmanCommand;
use Setfive\Command\StartScrapeCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication {

    public function __construct(){
        parent::__construct();

        $this->add( new GearmanCommand() );
        $this->add( new StartScrapeCommand() );
    }

}