<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Layover extends AbstractPlace
{
    public function slotsForTest(Gm $gm): int
    {
        return 0;
    }

    public function settle(Gm $gm, array $aliveAdventurers): void
    {
        Game::log(sprintf(
            '%d adventurers are resting',
            \count($aliveAdventurers)
        ));

        foreach ($aliveAdventurers as $adventurer) {
            $this->settleFor($adventurer, $aliveAdventurers);
        }
    }

    public function reward(Gm $gm, array $aliveAdventurers): void
    {
        foreach ($aliveAdventurers as $adventurer) {
            $adventurer->draw($adventurer->cardsToDraw);
        }

        $gm->levelUp();
    }

    private function settleFor(Adventurer $adventurer, array $allies): void
    {
        foreach ($allies as $ally) {
            /** @var Adventurer $ally */
            if ($ally->wounds > 0) {
//                $adventurer->discard(1);
                $ally->healAllWounds();

                Game::log('An adventurer get healed.');
            }
            if (!$ally->alive) {
//                $adventurer->discard(1);
                $ally->resurrect();

                Game::log('Adventurer resurrect an ally.');
            }
        }
    }
}
