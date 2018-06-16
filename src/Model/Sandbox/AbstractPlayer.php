<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

abstract class AbstractPlayer
{
    public $cardsToDraw;

    public $hand;

    public function __construct(int $cardsToDrawAtStart, int $cardsToDraw)
    {
        $this->cardsToDraw = $cardsToDraw;
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

    public function softUse(int $number): array
    {
        $cards = [];
        for ($i = 0; $i < $number; $i++) {
            $cards[] = $this->softUseOne();
        }

        return $cards;
    }

    public static function roll(int $bonus = 0): int
    {
        return \mt_rand(1, 6) + $bonus;
    }

    abstract protected function drawOne();

    abstract protected function hardUseOne();

    abstract protected function softUseOne();
}
