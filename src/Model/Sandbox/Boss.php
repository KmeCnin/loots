<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Boss extends Region
{
    public function explore(Game $game)
    {
        $game->gm->draw(3 + $game->gm->cardsToDraw);
        $this->cards = $game->gm->hardUse(3);
    }
}
