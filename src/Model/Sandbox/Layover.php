<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Layover extends AbstractPlace
{
    public function explore(Game $game)
    {
    }

    public function settle(Game $game): void
    {
        foreach ($game->aliveAdventurers() as $adventurer) {
            $this->settleFor($adventurer);
        }
    }

    private function settleFor(Adventurer $adventurer): void
    {
        // Self healing.
//        if ($adventurer->wounds > 0) {
//            $adventurer->healWound();
//        }
        while ($adventurer->wounds > 0 && \count($adventurer->hand) > 0) {
            $adventurer->discard(1);
            $adventurer->healWound();
        }
    }
}
