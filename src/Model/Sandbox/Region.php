<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Region extends AbstractPlace
{
    public $name;

    public $cards;

    public $fight;

    public $trick;

    public $magic;

    public function __construct()
    {
        $this->name = uniqid();
        $this->cards = [];
        $this->fight = 0;
        $this->trick = 0;
        $this->magic = 0;
    }

    public function explore(Game $game)
    {
        $game->gm->draw($game->gm->slots() + $game->gm->cardsToDraw);
        $this->cards = $game->gm->hardUse($game->gm->slots());

        Game::log(sprintf(
            'GM adds %d tests to the Region %s [%d, %d, %d].',
            $game->gm->slots(),
            $this->name,
            $this->test(Skill::FIGHT),
            $this->test(Skill::TRICK),
            $this->test(Skill::MAGIC)
        ));
    }

    public function settle(Game $game): void
    {
        Game::log(sprintf(
            '%d adventurers are fighting',
            \count($game->aliveAdventurers())
        ));

        foreach ($game->aliveAdventurers() as $adventurer) {
            $this->settleFor($adventurer, $game->gm);
        }
    }

    protected function test(string $skill): int
    {
        $test = 0;
        foreach ($this->cards as $card) {
            $test += $card->{$skill};
        }

        $test += $this->{$skill};

        return $test;
    }

    private function settleFor(Adventurer $adv, GM $gm): void
    {
        $availableSkills = [
            Skill::FIGHT => $this->test(Skill::FIGHT),
            Skill::TRICK => $this->test(Skill::TRICK),
            Skill::MAGIC => $this->test(Skill::MAGIC),
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
                    $gm->draw(1);

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
        return $rawScore - $this->test($skill);
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
