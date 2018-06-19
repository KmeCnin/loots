<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Layover extends AbstractPlace
{
    public function explore(Game $game)
    {
        Game::log('Adventurers rest at Layover.');
    }

    public function settle(Game $game): void
    {
        foreach ($game->aliveAdventurers() as $adventurer) {
            $this->settleFor($adventurer, $game->deadAdventurers() );
        }
    }

    private function settleFor(Adventurer $adventurer, array $deadAllies): void
    {
        // Self healing.
        while ($adventurer->wounds > 0 && \count($adventurer->hand) > 0) {
            $adventurer->discard(1);
            $adventurer->healWound();
            Game::log('Adventurer healed a wound.');
        }

        // Resurrect ally.
        foreach ($deadAllies as $ally) {
            /** @var Adventurer $ally */
            $adventurer->discard(1);
            $ally->resurrect();
            Game::log('Adventurer resurrect an ally.');
        }
    }
}
