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

    public function levelUp()
    {
        $this->level++;
    }

    public function slots(): int
    {
        switch (true) {
            case $this->level > 6:
                return 3;
            case $this->level > 3:
                return 2;
            default:
                return 1;
        }
    }

    protected function hardUseOne(): AbstractHandCard
    {
        return \array_pop($this->hand);
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
                    $this->playSomeCards($playedCards);
                    return $playedCards;
                }
            }
        }

        return [];
    }
}
