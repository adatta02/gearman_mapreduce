<?php

namespace Setfive\Gearman;

interface WorkerInterface{
    public function work();
    public function getAvailableJobs();
}