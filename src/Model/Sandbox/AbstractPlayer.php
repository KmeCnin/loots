<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

abstract class AbstractPlayer
{
    public $cardsToDraw;

    public $deck;

    /** @var AbstractHandCard[] */
    public $hand;

    public function __construct(Deck $deck, int $cardsToDrawAtStart, int $cardsToDraw)
    {
        $this->cardsToDraw = $cardsToDraw;
        $this->deck = $deck;
        $this->hand = [];

        $this->draw($cardsToDrawAtStart);
    }

    public function draw(int $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $this->drawOne();
        }
    }

    public function discard(int $number)
    {
        for ($i = 0; $i < $number; $i++) {
            \array_pop($this->hand);
        }
    }

    public function hardUse(int $number): array
    {
        $cards = [];
        for ($i = 0; $i < $number; $i++) {
            $cards[] = $this->hardUseOne();
        }

        return $cards;
    }

    public function playSomeCards(array $cards): void
    {
        foreach ($cards as $card) {
            $this->playACard($card);
        }
    }

    public static function roll(int $bonus = 0): int
    {
        return \mt_rand(1, 6) + $bonus;
    }

    protected function drawOne()
    {
        $this->hand[] = $this->deck->draw();
    }

    protected function playACard(AbstractHandCard $card): void
    {
        $key = array_search($card, $this->hand);
        unset($this->hand[$key]);
        array_values($this->hand);
    }

    abstract protected function hardUseOne();
}
