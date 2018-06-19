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
        if (\count($this->hand) && $this->stuff < 3) {
            $this->hardUse(1);
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
                    $this->playSomeCards($playedCards);
                    return $playedCards;
                }
            }
        }

        return [];
    }

    public function chooseSkill($availableSkills)
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
}
