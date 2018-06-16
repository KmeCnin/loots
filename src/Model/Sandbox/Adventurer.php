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

    public function __construct(int $fight, int $trick, int $magic, int $cardsToDrawAtStart, int $cardsToDraw)
    {
        parent::__construct($cardsToDrawAtStart, $cardsToDraw);

        $this->fight = $fight;
        $this->trick = $trick;
        $this->magic = $magic;
        $this->alive = true;
        $this->stuff = 0;
        $this->wounds = 0;
    }

    public function rollBestSkill(array $availableSkills): array
    {
        $skill = $this->chooseSkill($availableSkills);
        return [$skill, $this->roll($this->{$skill} - $availableSkills[$skill])];
    }

    public function receiveWound(): void
    {
        $this->fight--;
        $this->trick--;
        $this->magic--;
        $this->wounds++;
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

    public function die(): void
    {
        $this->alive = false;
    }

    public function equip()
    {
        if (\count($this->hand) && $this->stuff < 3) {
            $this->hardUse(1);
        }
    }

    private function chooseSkill($availableSkills)
    {
        $decisionMap = [];
        foreach ($availableSkills as $skill => $difficulty) {
            $decisionMap[$skill] = $difficulty - $this->{$skill};
        }
        return array_search(min($decisionMap), $decisionMap);
    }

    protected function drawOne()
    {
        $set = [
            [0, 0, 1],
            [1, 0, 0],
            [0, 1, 0],
        ];

        $card = $set[\mt_rand(0, \count($set)-1)];

        $this->hand[] = new Loot($card[0], $card[1], $card[2]);
    }

    protected function hardUseOne(): AbstractHandCard
    {
        /** @var Loot $cardPlayed */
        $cardPlayed = \array_pop($this->hand);
        $this->fight += $cardPlayed->fight;
        $this->trick += $cardPlayed->trick;
        $this->magic += $cardPlayed->magic;
        $this->stuff++;

        return $cardPlayed;
    }

    protected function softUseOne(): AbstractHandCard
    {
        return \array_pop($this->hand);
    }
}
