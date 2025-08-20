<?php

namespace Infra\Database;

class SeederRunner
{
    protected $seeds = [];

    public function addSeed($seed)
    {
        $this->seeds[] = $seed;
    }

    public function run()
    {
        foreach ($this->seeds as $seed) {
            if (method_exists($seed, 'run')) {
                $seed->run();
            }
        }
    }
}