<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

abstract class AbstractHandCard extends AbstractCard
{
    public $fight;

    public $trick;

    public $magic;

    public function __construct(int $fight, int $trick, int $magic)
    {
        $this->fight = $fight;
        $this->trick = $trick;
        $this->magic = $magic;
    }
}
