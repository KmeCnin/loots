<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Region extends AbstractPlace
{
    public $cards;

    public function explore(Game $game)
    {
        $game->gm->draw($game->gm->slots() + $game->gm->cardsToDraw);
        $this->cards = $game->gm->hardUse($game->gm->slots());
    }

    public function settle(Game $game): void
    {
        foreach ($game->aliveAdventurers() as $adventurer) {
            $this->settleFor($adventurer, $game->gm);
        }
    }

    protected function test(string $skill, GM $gm): int
    {
        $test = 0;
        foreach ($this->cards as $card) {
            $test += $card->{$skill};
        }

        return $test;
    }

    private function settleFor(Adventurer $adventurer, GM $gm): void
    {
        $availableSkills = [
            'fight' => $this->test('fight', $gm),
            'trick' => $this->test('trick', $gm),
            'magic' => $this->test('magic', $gm),
        ];
//        var_dump($availableSkills);
        while (true) {
            [$skillUsed, $score] = $adventurer->rollBestSkill($availableSkills);
            unset($availableSkills[$skillUsed]);

            $score = $this->versus($score, $adventurer, $gm);

//            var_dump(\count($this->cards));
//            var_dump($score);

            if ($score < 0) {
                if (empty($availableSkills)) {
                    // Death.
                    $adventurer->die();
                    $gm->draw(1);
                    return;
                }

                // Wound.
                $adventurer->receiveWound();
//                var_dump('FAIL!'); echo '<br>';
            } else {
//                var_dump('OK!'); echo '<br>';
                return;
            }
        }
    }

    private function versus(int $score, Adventurer $adventurer, GM $gm): int
    {
        $adventurerHandCount = \count($adventurer->hand);
        if ($adventurerHandCount && $score < 0 && $score > -2 * $adventurerHandCount) {
            $adventurer->softUse(1);
            $score += 2;

            return $this->versus($score, $adventurer, $gm);
        }

        $gmHandCount = \count($gm->hand);
        if ($gmHandCount && $score >= 0 && $score < 2 * $gmHandCount) {
            $gm->softUse(1);
            $score -= 2;

            return $this->versus($score, $adventurer, $gm);
        }

        return $score;
    }
}
