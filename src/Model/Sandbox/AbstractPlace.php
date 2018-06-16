<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

abstract class AbstractPlace extends AbstractCard
{
    /** @var AbstractHandCard[] */
    public $cards;

    abstract public function explore(Game $game);

    abstract public function settle(Game $game): void;
}
