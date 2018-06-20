<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Adventurer extends AbstractPlayer
{
    public $fight;

    public $trick;

    public $magic;

    public $alive;

    public $stuff;

    public $wounds;

    public function __construct(int $fight, int $trick, int $magic, Deck $deck, int $cardsToDrawAtStart, int $cardsToDraw)
    {
        parent::__construct($deck, $cardsToDrawAtStart, $cardsToDraw);

        $this->fight = $fight;
        $this->trick = $trick;
        $this->magic = $magic;
        $this->alive = true;
        $this->stuff = 0;
        $this->wounds = 0;
    }

    public function receiveWound(): void
    {
        $this->fight--;
        $this->trick--;
        $this->magic--;
        $this->wounds++;
    }

    public function healAllWounds(): void
    {
        for ($i = 0; $i < $this->wounds; $i++) {
            $this->healWound();
        }
    }

    public function healWound(): void
    {
        if ($this->wounds < 1) {
            throw new \Exception('No wound to heal.');
        }

        $this->fight++;
        $this->trick++;
        $this->magic++;
        $this->wounds--;
    }

    public function resurrect()
    {
        if ($this->alive) {
            throw new \Exception('Not dead (yet).');
        }

        while ($this->wounds) {
            $this->healWound();
        }
        $this->hand = [];
        $this->alive = true;
    }

    public function die(): void
    {
        $this->alive = false;
    }

    public function equip()
    {
        if ($this->hand->count() && $this->stuff < 3) {
            $this->equipBestLoot();
        }
    }

    public function buff(string $skill, int $score): array
    {
        $cardsInHand = \count($this->hand);
        if (!$cardsInHand || Game::scoreIsEnough($score)) {
            return [];
        }

        $playedCards = [];
        $availableGain = 0;
        foreach ($this->hand as $card) {
            if ($bonus = $card->bonusSkill($skill) > 0) {
                $playedCards[] = $card;
                $availableGain += $card->bonusSkill($skill);
                if (Game::scoreIsEnough($score + $availableGain)) {
                    $this->playCards($playedCards);
                    return $playedCards;
                }
            }
        }

        return [];
    }

    public function chooseSkill(iterable $availableSkills)
    {
        $decisionMap = [];
        foreach ($availableSkills as $skill => $difficulty) {
            $decisionMap[$skill] = $difficulty - $this->{$skill};
        }
        return array_search(min($decisionMap), $decisionMap);
    }

    public function rollSkill(string $skill): int
    {
        return $this->roll($this->{$skill});
    }

    public function bestSkill(): string
    {
        $skills = [
            Skill::FIGHT => $this->fight,
            Skill::TRICK => $this->trick,
            Skill::MAGIC => $this->magic,
        ];
        return \array_search(\max($skills), $skills);
    }

    private function equipBestLoot(): void
    {
        $bestSkill = $this->bestSkill();

        foreach ($this->hand as $loot) {
            /** @var Loot $loot */
            if ($loot->{$bestSkill} > 0) {
                $this->equipCard($loot);
                return;
            }
        }

        $this->equipCard($this->hand->first());
        return;
    }

    private function equipCard(Loot $loot): void
    {
        $this->playCard($loot);

        $this->fight += $loot->fight;
        $this->trick += $loot->trick;
        $this->magic += $loot->magic;
        $this->stuff++;
    }
}
