<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class GM extends AbstractPlayer
{
    public $level;

    public function __construct(Deck $deck, int $cardsToDrawAtStart, int $cardsToDraw)
    {
        parent::__construct($deck, $cardsToDrawAtStart, $cardsToDraw);

        $this->level = 1;
    }

    public function preparePlaceForAdventurers(AbstractPlace $place, array $adventurers): void
    {
        $slotsForTests = $place->slotsForTest($this);
        $this->draw($slotsForTests + $this->cardsToDraw);

        if ($slotsForTests > 0) {
            $place->addCards($this->playTests($slotsForTests, $adventurers));
        }
    }

    public function levelUp(): void
    {
        $this->level++;
    }

    public function debuff(string $skill, int $score): array
    {
        $cardsInHand = \count($this->hand);
        if (!$cardsInHand || !Game::scoreIsEnough($score)) {
            return [];
        }

        $playedCards = [];
        $availableLose = 0;
        foreach ($this->hand as $card) {
            if ($malus = $card->bonusSkill($skill) > 0) {
                $playedCards[] = $card;
                $availableLose += $card->bonusSkill($skill);
                if (!Game::scoreIsEnough($score - $availableLose)) {
                    $this->playCards($playedCards);
                    return $playedCards;
                }
            }
        }

        return [];
    }

    private function playTests(int $slots, array $adventurers): array
    {
        $bestSkills = [
            Skill::FIGHT => 0,
            Skill::TRICK => 0,
            Skill::MAGIC => 0,
        ];
        foreach ($adventurers as $adventurer) {
            /** @var Adventurer $adventurer */
            $bestSkills[$adventurer->bestSkill()]++;
        }

        $bestSkill = \array_search(\max($bestSkills), $bestSkills);

        $tests = [];
        while (\count($tests) < $slots) {
            foreach ($this->hand as $test) {
                /** @var Test $test */
                if ($test->{$bestSkill} > 0) {
                    $tests[] = $test;
                    break;
                }
                $tests[] = $test;
            }
        }

        $this->playCards($tests);

        return $tests;
    }
}
