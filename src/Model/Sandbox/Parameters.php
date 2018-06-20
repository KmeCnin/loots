<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Parameters
{
    public $iterations;

    public $race;

    public $numberOfAdventurers;

    public $gmDrawAtStart;

    public $gmCardsToDraw;

    public $adventurersDrawAtStart;

    public $adventurersCardsToDraw;

    public function __construct()
    {
        $this->iterations = 10000;
        $this->numberOfAdventurers = 1;
        $this->race = 'human';
        $this->gmDrawAtStart = 0;
        $this->gmCardsToDraw = 1;
        $this->adventurersDrawAtStart = 3;
        $this->adventurersCardsToDraw = 1;
    }
}
