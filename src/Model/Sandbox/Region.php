<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Region extends AbstractPlace
{
    public $fight;

    public $trick;

    public $magic;

    public function __construct()
    {
        parent::__construct();

        $this->fight = 0;
        $this->trick = 0;
        $this->magic = 0;
    }

    public function slotsForTest(Gm $gm): int
    {
        switch (true) {
            case $gm->level > 6:
                return 3;
            case $gm->level > 3:
                return 2;
            default:
                return 1;
        }
    }

    public function settle(Gm $gm, array $aliveAdventurers): void
    {
        Game::log(sprintf(
            '%d adventurers are fighting',
            \count($aliveAdventurers)
        ));

        foreach ($aliveAdventurers as $adventurer) {
            $this->settleFor($adventurer, $gm);
        }
    }

    public function reward(Gm $gm, array $aliveAdventurers): void
    {
        foreach ($aliveAdventurers as $adventurer) {
            $adventurer->draw($adventurer->cardsToDraw);
        }

        $gm->levelUp();
    }

    protected function testDifficulty(string $skill): int
    {
        $test = $this->{$skill};
        foreach ($this->cards->toArray() as $card) {
            $test += $card->{$skill};
        }

        return $test;
    }

    private function settleFor(Adventurer $adv, GM $gm): void
    {
        $availableSkills = [
            Skill::FIGHT => $this->testDifficulty(Skill::FIGHT),
            Skill::TRICK => $this->testDifficulty(Skill::TRICK),
            Skill::MAGIC => $this->testDifficulty(Skill::MAGIC),
        ];
        while (true) {
            $skillUsed = $adv->chooseSkill($availableSkills);
            $rawScore = $adv->rollSkill($skillUsed);
            unset($availableSkills[$skillUsed]);

            Game::log(sprintf(
                'Adventurer rolled %d at %s and scored %d with bonus.',
                $rawScore,
                $skillUsed,
                $this->scoreWithBonus($skillUsed, $rawScore)
            ));

            $this->versus($skillUsed, $rawScore, $adv, $gm);

            Game::log(sprintf(
                'After versus the score is now %d.',
                $this->scoreWithBonus($skillUsed, $rawScore)
            ));

            if (!Game::scoreIsEnough(
                $this->scoreWithBonus($skillUsed, $rawScore))
            ) {
                if (empty($availableSkills)) {
                    // Death.
                    $adv->die();

                    Game::log('Adventurer died.');

                    return;
                }

                // Wound.
                $adv->receiveWound();

                Game::log('Adventurer get a wound.');
            } else {
                return;
            }
        }
    }

    private function versus(
        string $skill,
        int $rawScore,
        Adventurer $adv,
        GM $gm
    ): void {
        $loots = $adv->buff($skill, $this->scoreWithBonus($skill, $rawScore));
        $this->useLootsAsBuff($loots);

        $tests = $gm->debuff($skill, $this->scoreWithBonus($skill, $rawScore));
        $this->useTestsAsDebuff($tests);

        if (!empty($loots) || !empty($tests)) {
            $this->versus($skill, $rawScore, $adv, $gm);
        }
    }

    private function scoreWithBonus(string $skill, int $rawScore): int
    {
        return $rawScore - $this->testDifficulty($skill);
    }

    private function useLootsAsBuff(array $cards): void
    {
        foreach ($cards as $card) {
            /** @var AbstractHandCard $card */
            foreach ($card->bonusSkill as $skill => $bonus) {
                $this->{$skill} -= $bonus;
            }

            Game::log('Adventurer uses a loot to improve its score.');
        }
    }

    private function useTestsAsDebuff(array $cards): void
    {
        foreach ($cards as $card) {
            /** @var AbstractHandCard $card */
            foreach ($card->bonusSkill as $skill => $bonus) {
                $this->{$skill} += $bonus;
            }

            Game::log('GM uses a test to decrease the score.');
        }
    }
}
